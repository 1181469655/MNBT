<?php
/**
 * 虚拟主机计划任务（V1.88）
 *
 * 租户隔离设计（用户只能看到/操作自己主机上的任务）：
 *  1. 会话层：ajax.php 已校验 $islogins 并解析出 $yhc（用户自己的 MN_zj 行）、
 *     $btipe/$btkeye（所属节点宝塔地址与密钥），到期/禁用用户在 member.php 即被拦截；
 *  2. 归属登记：宝塔 crontab 是面板级接口（GetCrontab 返回节点全部任务、任务 id
 *     可被伪造遍历），每个 MNBT 创建的任务都在 MN_cron_task 登记 zj_id；
 *     列表按登记过滤，写操作（编辑/删除/启停/执行/日志）先查登记再下发宝塔；
 *  3. 类型白名单：仅 site/database/logs/url，不含 toShell/toPython（任意命令执行）；
 *  4. 目标锁定：sName/sBody 由服务端从用户自己的主机数据派生（站点名/库名/自己
 *     域名的 URL），编辑时从登记原样重放——用户无法把备份任务改成 Shell 任务；
 *  5. URL 任务仅允许 http(s) 且主机名属于本站点已绑定域名（防内网 SSRF）。
 */
if (!defined('IN_CRONLITE')) exit();

include_once("../MPHX/crontab.function.php");
crontab_ensure_tables($DB);

if (!in_array($egn, ['crontab_list', 'crontab_add', 'crontab_edit', 'crontab_del', 'crontab_toggle', 'crontab_exec', 'crontab_logs'], true)) {
    return;
}

$zj_id = intval($yhc['id']);          // 归属主机（MN_zj.id）
$bt_site_id = $yhc['btid'];           // 宝塔内站点 id
$site_name = $yhc['sqldz'];           // 站点名
$CRON_LIMIT = 10;                     // 每台主机最多任务数

$api = new bt_api($btipe, $btkeye);

/* ============================================================
 *  任务列表
 * ============================================================ */
if ($egn == 'crontab_list') {
    $rows = crontab_load_owned($DB, $api, $zj_id);
    exit(json_encode(['qk' => 1, 'code' => '获取成功', 'msg' => ['rows' => $rows]], JSON_UNESCAPED_UNICODE));
    return;
}

/* ============================================================
 *  新建任务
 * ============================================================ */
if ($egn == 'crontab_add') {
    $stype = trim($_POST['stype'] ?? '');
    $name = trim((string)($_POST['name'] ?? ''));
    if (!in_array($stype, crontab_allowed_stypes(), true)) json_exit('不支持的任务类型');
    if ($name === '' || mb_strlen($name) > 50) json_exit('任务名称需为 1~50 个字符');
    $cycle = crontab_normalize_cycle(
        trim($_POST['cycle_type'] ?? 'day'),
        $_POST['cycle_value'] ?? '',
        $_POST['cycle_hour'] ?? 0,
        $_POST['cycle_minute'] ?? 0
    );
    if (!$cycle) json_exit('执行周期不合法');

    // 目标派生：sName/sBody 只来自用户自己的主机数据，不采信用户输入
    $sname = '';
    $sbody = '';
    if ($stype === 'site' || $stype === 'logs') {
        $sname = $site_name;
        $sbody = $site_name;
    } elseif ($stype === 'database') {
        // 库名以宝塔侧登记为准（MN_zj.hxd=SQLID），取不到时回退数据库账号名
        $sname = '';
        $list = $api->cronDataList('databases');
        foreach ((array)($list['data'] ?? []) as $db) {
            if (isset($db['id']) && strval($db['id']) === strval($yhc['hxd'])) {
                $sname = (string)$db['name'];
                break;
            }
        }
        if ($sname === '') {
            $sname = (string)$yhc['sqluser'];
        }
        $sbody = $sname;
    } else { // url：仅允许本站点已绑定域名（防内网 SSRF）
        $url = trim((string)($_POST['sbody'] ?? ''));
        if ($url === '' || mb_strlen($url) > 500) json_exit('请填写要访问的 URL');
        $dm = $api->get_site_domains($bt_site_id);
        $domains = [];
        foreach (($dm['domains'] ?? []) as $d) {
            $domains[] = $d['name'] ?? '';
        }
        $domains[] = $site_name;
        if (!crontab_validate_own_url($url, $domains)) {
            json_exit('仅允许访问本站点已绑定的域名（http/https）');
        }
        $sbody = $url;
        $sname = $name;
    }

    // 备份保留份数（仅备份类任务）
    $save_count = 0;
    if ($stype === 'site' || $stype === 'database') {
        $save_count = max(1, min(30, intval($_POST['save_count'] ?? 3)));
    }

    $count = $DB->count_prepare("SELECT count(*) FROM MN_cron_task WHERE zj_id=?", [$zj_id]);
    if ($count >= $CRON_LIMIT) json_exit('每台主机最多 ' . $CRON_LIMIT . ' 个计划任务');

    list($type, $where1, $hour, $minute) = $cycle;
    $payload = [
        'name'         => $name,
        'type'         => $type,
        'where1'       => $where1 !== '' ? $where1 : '0',
        'where_hour'   => $hour >= 0 ? strval($hour) : '0',
        'where_minute' => $minute >= 0 ? strval($minute) : '0',
        'sType'        => $stype,
        'sName'        => $sname,
        'sBody'        => $sbody,
    ];
    if ($save_count > 0) {
        $payload['save'] = strval($save_count);
    }
    $r = $api->cronAdd($payload);
    if (empty($r['status'])) {
        json_exit('添加失败：' . crontab_bt_msg($r));
    }

    // 宝塔任务 id：优先取响应，取不到时按 目标+名称 特征回查
    $bt_cron_id = '';
    if (!empty($r['id'])) {
        $bt_cron_id = strval($r['id']);
    } elseif (!empty($r['data']['id'])) {
        $bt_cron_id = strval($r['data']['id']);
    } else {
        foreach ((array)($api->cronList() ?: []) as $t) {
            if (isset($t['id'], $t['sType'], $t['sBody'], $t['name'])
                && strval($t['sType']) === $stype && strval($t['sBody']) === $sbody
                && strval($t['name']) === $name) {
                $bt_cron_id = strval($t['id']);
            }
        }
    }
    if ($bt_cron_id === '') {
        json_exit('任务已创建但未能登记归属，请在列表刷新后重试或联系管理员');
    }

    $cycle_value = crontab_build_cycle_value($type, $where1, $hour, $minute);
    $DB->query_prepare(
        "INSERT INTO MN_cron_task (zj_id,bt_cron_id,name,stype,sname,sbody,cycle_type,cycle_value,save_count,created_at) VALUES (?,?,?,?,?,?,?,?,?,?)",
        [$zj_id, $bt_cron_id, $name, $stype, $sname, $sbody, $type, $cycle_value, $save_count, date('Y-m-d H:i:s')]
    );
    logjl($yhc['user'], '计划任务', '添加了' . crontab_stype_text($stype) . '计划任务「' . $name . '」', '添加成功', $DB);
    json_exit('添加成功');
    return;
}

/* ============================================================
 *  编辑任务（仅名称与周期；类型与目标锁定）
 * ============================================================ */
if ($egn == 'crontab_edit') {
    $task_id = intval($_POST['id'] ?? 0);
    $row = crontab_find_owned($DB, $zj_id, $task_id);
    if (!$row) json_exit('任务不存在');
    $name = trim((string)($_POST['name'] ?? ''));
    if ($name === '' || mb_strlen($name) > 50) json_exit('任务名称需为 1~50 个字符');
    $cycle = crontab_normalize_cycle(
        trim($_POST['cycle_type'] ?? $row['cycle_type']),
        $_POST['cycle_value'] ?? '',
        $_POST['cycle_hour'] ?? 0,
        $_POST['cycle_minute'] ?? 0
    );
    if (!$cycle) json_exit('执行周期不合法');
    list($type, $where1, $hour, $minute) = $cycle;

    $payload = array_merge(crontab_locked_target($row), [
        'id'           => intval($row['bt_cron_id']),
        'name'         => $name,
        'type'         => $type,
        'where1'       => $where1 !== '' ? $where1 : '0',
        'where_hour'   => $hour >= 0 ? strval($hour) : '0',
        'where_minute' => $minute >= 0 ? strval($minute) : '0',
    ]);
    $r = $api->cronModify($payload);
    if (empty($r['status'])) {
        json_exit('保存失败：' . crontab_bt_msg($r));
    }

    $cycle_value = crontab_build_cycle_value($type, $where1, $hour, $minute);
    $DB->query_prepare(
        "UPDATE MN_cron_task SET name=?,cycle_type=?,cycle_value=? WHERE id=? AND zj_id=?",
        [$name, $type, $cycle_value, $row['id'], $zj_id]
    );
    logjl($yhc['user'], '计划任务', '修改了计划任务「' . $name . '」', '修改成功', $DB);
    json_exit('保存成功');
    return;
}

/* ============================================================
 *  删除任务
 * ============================================================ */
if ($egn == 'crontab_del') {
    $row = crontab_find_owned($DB, $zj_id, intval($_POST['id'] ?? 0));
    if (!$row) json_exit('任务不存在');
    $r = $api->cronDelete($row['bt_cron_id']);
    if (empty($r['status'])) {
        json_exit('删除失败：' . crontab_bt_msg($r));
    }
    $DB->query_prepare("DELETE FROM MN_cron_task WHERE id=? AND zj_id=?", [$row['id'], $zj_id]);
    logjl($yhc['user'], '计划任务', '删除了计划任务「' . $row['name'] . '」', '删除成功', $DB);
    json_exit('删除成功');
    return;
}

/* ============================================================
 *  启用 / 暂停
 * ============================================================ */
if ($egn == 'crontab_toggle') {
    $row = crontab_find_owned($DB, $zj_id, intval($_POST['id'] ?? 0));
    if (!$row) json_exit('任务不存在');
    $status = ($_POST['status'] ?? '') === '1' ? '1' : '0';
    $r = $api->cronStatus($row['bt_cron_id'], $status);
    if (empty($r['status'])) {
        json_exit('操作失败：' . crontab_bt_msg($r));
    }
    json_exit('操作成功');
    return;
}

/* ============================================================
 *  立即执行一次
 * ============================================================ */
if ($egn == 'crontab_exec') {
    $row = crontab_find_owned($DB, $zj_id, intval($_POST['id'] ?? 0));
    if (!$row) json_exit('任务不存在');
    $r = $api->cronExecute($row['bt_cron_id']);
    if (empty($r['status'])) {
        json_exit('执行失败：' . crontab_bt_msg($r));
    }
    logjl($yhc['user'], '计划任务', '手动执行了计划任务「' . $row['name'] . '」', '执行成功', $DB);
    json_exit('操作成功');
    return;
}

/* ============================================================
 *  执行日志
 * ============================================================ */
if ($egn == 'crontab_logs') {
    $row = crontab_find_owned($DB, $zj_id, intval($_POST['id'] ?? 0));
    if (!$row) json_exit('任务不存在');
    $r = $api->cronLogs($row['bt_cron_id']);
    if (isset($r['status']) && !$r['status']) {
        json_exit('读取失败：' . crontab_bt_msg($r));
    }
    $log = (string)($r['msg'] ?? '');
    if (mb_strlen($log) > 65536) {
        $log = mb_substr($log, -65536);
    }
    exit(json_encode(['qk' => 1, 'code' => '获取成功', 'msg' => ['name' => $row['name'], 'log' => $log]], JSON_UNESCAPED_UNICODE));
    return;
}
