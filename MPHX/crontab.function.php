<?php
if (!defined('IN_CRONLITE')) exit();

/**
 * 虚拟主机计划任务（V1.88）
 *
 * 用户端（/user/）经宝塔 /crontab 接口管理自己站点上的计划任务。
 * 归属模型：宝塔的 crontab 是面板级接口（GetCrontab 返回本节点全部任务、
 * 任务 id 可被伪造），因此每个由 MNBT 创建的任务都在 MN_cron_task 登记一条
 * 归属记录（zj_id = MN_zj.id）；所有读操作按登记过滤，所有写操作先查登记，
 * 未登记的任务（其他租户或管理员手工建的）一律不可见、不可操作。
 * sType 白名单不含 toShell/toPython（任意代码执行），sName/sBody 由服务端
 * 从用户自己的主机数据派生并锁定，编辑时原样重放，杜绝改类型提权。
 */

function crontab_ensure_tables($DB)
{
    $DB->query("CREATE TABLE IF NOT EXISTS `MN_cron_task` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `zj_id` int(11) NOT NULL COMMENT '归属主机 MN_zj.id',
        `bt_cron_id` varchar(50) NOT NULL COMMENT '宝塔任务 id',
        `name` varchar(200) NOT NULL DEFAULT '',
        `stype` varchar(20) NOT NULL DEFAULT '',
        `sname` varchar(200) NOT NULL DEFAULT '' COMMENT '锁定的宝塔 sName',
        `sbody` text NOT NULL COMMENT '锁定的宝塔 sBody',
        `cycle_type` varchar(20) NOT NULL DEFAULT 'day',
        `cycle_value` varchar(50) NOT NULL DEFAULT '',
        `save_count` int(11) NOT NULL DEFAULT 0 COMMENT '备份保留份数',
        `created_at` varchar(50) NOT NULL DEFAULT '',
        PRIMARY KEY (`id`),
        KEY `idx_zj` (`zj_id`),
        KEY `idx_btcron` (`bt_cron_id`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8");
}

/** 允许租户创建的任务类型（绝不包含 toShell/toPython/rememory） */
function crontab_allowed_stypes()
{
    return ['site', 'database', 'logs', 'url'];
}

/** 允许的执行周期与参数范围：type => [where1 是否需要, 小时分钟是否需要, min, max] */
function crontab_cycle_spec()
{
    return [
        'minute-n' => ['where1' => true,  'hm' => false, 'min' => 1,  'max' => 720],
        'hour'     => ['where1' => false, 'hm' => false, 'min' => 0,  'max' => 59],
        'day'      => ['where1' => false, 'hm' => true,  'min' => 0,  'max' => 23],
        'day-n'    => ['where1' => true,  'hm' => true,  'min' => 1,  'max' => 31],
    ];
}

/** 规范化周期参数：返回 [type, where1, hour, minute] 或 null（非法） */
function crontab_normalize_cycle($type, $value, $hour, $minute)
{
    $spec = crontab_cycle_spec();
    if (!isset($spec[$type])) {
        return null;
    }
    $spec = $spec[$type];
    $where1 = '';
    $hour = intval($hour);
    $minute = intval($minute);
    if ($spec['where1']) {
        $where1 = strval(max($spec['min'], min($spec['max'], intval($value))));
    }
    if ($spec['hm']) {
        if ($hour < 0 || $hour > 23 || $minute < 0 || $minute > 59) {
            return null;
        }
    } else {
        if ($type === 'hour') {
            // 每小时：只取分钟（0-59）
            if ($minute < 0 || $minute > 59) return null;
            $hour = -1;
        } else {
            $hour = -1;
            $minute = -1;
        }
    }
    return [$type, $where1, $hour, $minute];
}

/**
 * 解析登记的 cycle_value 为 [value, hour, minute]（编辑表单回显用）：
 * minute-n/hour 存纯数字（hour 类型的数字是分钟），day 存 "H:M"，day-n 存 "N H:M"
 */
function crontab_parse_cycle_value($type, $cycle_value)
{
    $s = (string)$cycle_value;
    if ($type === 'day') {
        $hm = explode(':', $s);
        return [0, intval($hm[0]), count($hm) > 1 ? intval($hm[1]) : 0];
    }
    if ($type === 'day-n') {
        $p = explode(' ', $s, 2);
        $hm = isset($p[1]) ? explode(':', $p[1]) : ['0', '0'];
        return [intval($p[0]), intval($hm[0]), count($hm) > 1 ? intval($hm[1]) : 0];
    }
    return [intval($s), -1, 0];
}

/** 周期的人类可读描述（列表展示用） */
function crontab_cycle_text($type, $cycle_value)
{
    list($value, $hour, $minute) = crontab_parse_cycle_value($type, $cycle_value);
    $hm = ($hour >= 0 ? $hour : 0) . ':' . sprintf('%02d', max(0, $minute));
    switch ($type) {
        case 'minute-n': return '每 ' . $value . ' 分钟';
        case 'hour':     return '每小时第 ' . $minute . ' 分钟';
        case 'day':      return '每天 ' . $hm;
        case 'day-n':    return '每 ' . $value . ' 天 ' . $hm;
        default:         return $type;
    }
}

/** 租户可用的任务类型中文说明 */
function crontab_stype_text($stype)
{
    $map = [
        'site'     => '备份网站',
        'database' => '备份数据库',
        'logs'     => '日志切割',
        'url'      => '访问URL',
    ];
    return $map[$stype] ?? $stype;
}

/** punycode 转换（intl 扩展缺失时原样返回） */
function crontab_idn($host)
{
    return function_exists('idn_to_ascii') ? (idn_to_ascii($host) ?: $host) : $host;
}

/**
 * 校验 URL 归属：必须是 http(s) 协议、合法公网 URL，且主机名属于该站点
 * 已绑定的域名（防 SSRF：宝塔节点会真的去访问这个 URL）。
 * $domains 为该站点在宝塔绑定的域名列表（调用方经 get_site_domains 获取）。
 */
function crontab_validate_own_url($url, $domains)
{
    if (!preg_match('#^https?://#i', $url)) {
        return false;
    }
    $host = strtolower((string)parse_url($url, PHP_URL_HOST));
    if ($host === '' || filter_var($host, FILTER_VALIDATE_IP)) {
        return false; // 禁止直接用 IP 访问（含内网地址）
    }
    if (preg_match('/^(localhost|.*\.local|.*\.internal)$/i', $host)) {
        return false;
    }
    $host = crontab_idn($host);
    foreach ((array)$domains as $d) {
        $d = strtolower(trim((string)$d));
        if ($d === '' || filter_var($d, FILTER_VALIDATE_IP)) {
            continue;
        }
        if ($host === crontab_idn($d)) {
            return true;
        }
    }
    return false;
}

/** 校验任务归属：返回登记行（含宝塔任务 id），不属于当前主机返回 null */
function crontab_find_owned($DB, $zj_id, $task_id)
{
    return $DB->get_row_prepare(
        "SELECT * FROM MN_cron_task WHERE id=? AND zj_id=? LIMIT 1",
        [intval($task_id), intval($zj_id)]
    );
}

/** 读取登记表（当前主机），按宝塔实时状态合并；宝塔已不存在的任务自动清理 */
function crontab_load_owned($DB, $api, $zj_id)
{
    $rows = $DB->get_all_prepare(
        "SELECT * FROM MN_cron_task WHERE zj_id=? ORDER BY id DESC",
        [$zj_id]
    ) ?: [];
    $bt_tasks = $api->cronList();
    $live = [];
    $bt_ok = false;
    foreach ((array)($bt_tasks ?: []) as $t) {
        if (!is_array($t) || !isset($t['id'])) continue;
        $bt_ok = true;
        $live[(string)$t['id']] = $t;
    }
    $out = [];
    foreach ($rows as $row) {
        $bt = $live[(string)$row['bt_cron_id']] ?? null;
        if ($bt_ok && !$bt) {
            // 宝塔侧已被删除（管理员清理等），同步清理登记
            $DB->query_prepare("DELETE FROM MN_cron_task WHERE id=? AND zj_id=?", [$row['id'], $zj_id]);
            continue;
        }
        $row['bt_status'] = $bt ? $bt['status'] : '';
        $row['stype_text'] = crontab_stype_text($row['stype']);
        $row['cycle_text'] = crontab_cycle_text($row['cycle_type'], $row['cycle_value']);
        list($row['cycle_value_raw'], $row['cycle_hour'], $row['cycle_minute']) =
            crontab_parse_cycle_value($row['cycle_type'], $row['cycle_value']);
        $out[] = $row;
    }
    return $out;
}

/** 从登记行重建锁定的宝塔任务参数（sType/sName/sBody 一律以登记为准） */
function crontab_locked_target($row)
{
    return [
        'sType' => $row['stype'],
        'sName' => $row['sname'],
        'sBody' => $row['sbody'],
    ];
}

/** 从规范化周期结果组装登记用 cycle_value 字符串（解析见 crontab_parse_cycle_value） */
function crontab_build_cycle_value($type, $where1, $hour, $minute)
{
    if ($type === 'day-n') {
        return $where1 . ' ' . $hour . ':' . $minute;
    }
    if ($type === 'day') {
        return $hour . ':' . $minute;
    }
    return $where1 !== '' ? $where1 : strval($minute);
}

/** 宝塔响应 msg 取字符串（部分接口返回数组） */
function crontab_bt_msg($r, $fallback = '宝塔接口异常')
{
    $msg = is_array($r) ? ($r['msg'] ?? null) : null;
    return is_string($msg) && $msg !== '' ? $msg : $fallback;
}
