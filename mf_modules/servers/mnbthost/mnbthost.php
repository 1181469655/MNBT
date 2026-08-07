<?php
/**
 * 梦奈宝塔虚拟主机 — 魔方财务（idcsmart）server module
 *
 * 通过 MNBT 外部 API（api/api.php）对接宝塔虚拟主机分销。
 * 开通即建站（FTP + MySQL + 配额），配额随产品配置选项落库。
 *
 * 关联：api/api.php（cfif/kt/zt/jc/tz/xf/czmm/zjmode/start/stop + ztcx）
 */

// ========================================================================
//  MetaData
// ========================================================================

function mnbthost_MetaData()
{
    return [
        'DisplayName' => '梦奈宝塔虚拟主机',
        'APIVersion'  => '1.1',
        'HelpDoc'     => 'https://github.com/MNBT/API.md', // 替换为实际帮助文档地址
    ];
}

// ========================================================================
//  ConfigOptions（产品级配额配置）
// ========================================================================

function mnbthost_ConfigOptions()
{
    return [
        [
            'type'        => 'text',
            'name'        => '网站空间',
            'description' => 'MB（开通时 webdx 参数）',
            'default'     => '500',
            'key'         => 'webdx',
        ],
        [
            'type'        => 'text',
            'name'        => '数据库空间',
            'description' => 'MB（开通时 sqldx 参数）',
            'default'     => '100',
            'key'         => 'sqldx',
        ],
        [
            'type'        => 'text',
            'name'        => '流量',
            'description' => 'MB（0=不限制，开通时 sizemax 参数）',
            'default'     => '0',
            'key'         => 'sizemax',
        ],
        [
            'type'        => 'text',
            'name'        => '域名绑定数',
            'description' => '个（开通时 ymbds 参数）',
            'default'     => '1',
            'key'         => 'ymbds',
        ],
        [
            'type'        => 'text',
            'name'        => '控制台地址',
            'description' => '虚拟主机用户控制台，如 https://mnbt.example.com/user/login.php',
            'default'     => '',
            'key'         => 'console_url',
        ],
    ];
}

// ========================================================================
//  内部工具：参数解析 & API 调用
// ========================================================================

/**
 * 解析参数（连接信息全部来自服务器设置）
 * 服务器字段映射：
 *   server_ip / server_host  → MNBT 主机
 *   port / secure            → 端口 / HTTPS
 *   server_username          → 节点编号（MN_bt.btdh 宝塔开通代号）
 *   server_password          → 调用密钥 md5(ktmy.qmk)，空则默认 md5('')
 *   accesshash               → 系统 API 密钥（$conf['api']）
 *
 * @return array [api_url, api_key, node_id, call_key, plan_id, console_url]
 */
function _mnbthost_resolve_params($params)
{
    $scheme = ($params['secure'] ?? '') == '1' ? 'https' : 'http';
    $host   = $params['server_ip'] ?: ($params['server_host'] ?? '');
    $port   = $params['port'] ?? '';
    $api_url = '';
    if (!empty($host)) {
        $api_url = $scheme . '://' . $host;
        if (!empty($port) && $port != '80' && $port != '443') {
            $api_url .= ':' . $port;
        }
        $api_url .= '/api/api.php';
    }

    $api_key  = trim((string)($params['accesshash'] ?? ''));
    $node_id  = trim((string)($params['server_username'] ?? ''));
    $call_key = trim((string)($params['server_password'] ?? ''));

    // 魔方可能对服务器密码做了 AES 加密存储，模块收到的是密文，
    // 参考 noKVM 的 aesPasswordDecode 处理；解密成功且为 32 位 hex（md5）才采用
    if (!empty($call_key) && function_exists('aesPasswordDecode')) {
        try {
            $decoded = aesPasswordDecode($call_key);
            if (is_string($decoded) && preg_match('/^[a-f0-9]{32}$/i', trim($decoded))) {
                $call_key = trim($decoded);
            }
        } catch (\Exception $e) {
            // 解密失败保持原值
        }
    }

    // 控制台地址：优先 configoption5，兼容旧配置
    $console_url = $params['configoption5'] ?? '';
    if (empty($console_url) && !empty($host)) {
        $console_url = $scheme . '://' . $host;
        if (!empty($port) && $port != '80' && $port != '443') {
            $console_url .= ':' . $port;
        }
        $console_url .= '/user/login.php';
    }

    return [$api_url, $api_key, $node_id, $call_key, $console_url];
}

/**
 * 调用 MNBT 虚拟主机 API
 *
 * @param array  $params  魔方传入的 $params
 * @param string $gn      动作（cfif/kt/zt/jc/tz/xf/czmm/zjmode/start/stop/ztcx）
 * @param array  $extra   额外 POST 字段（业务参数）
 * @param int    $timeout cURL 超时秒数
 * @return array          [success, code, msg, data?, _debug?]
 */
function _mnbthost_api_call($params, $gn, $extra = [], $timeout = 30)
{
    list($api_url, $api_key, $node_id, $call_key) = _mnbthost_resolve_params($params);

    if (empty($api_url))    return ['success' => false, 'code' => 0, 'msg' => '[mnbthost] 未配置服务器 IP/域名'];
    if (empty($api_key))    return ['success' => false, 'code' => 0, 'msg' => '[mnbthost] 未配置 Access Hash（系统 API 密钥）'];
    if (empty($node_id))    return ['success' => false, 'code' => 0, 'msg' => '[mnbthost] 未配置用户名（节点 btdh）'];
    // 调用密钥为空时默认 md5('')，适配节点未设置 ktmy/qmk 的场景
    if (empty($call_key))   $call_key = md5('');

    // 账号名优先用 domain（开通时就是用它），username 可能是 root 等主机名
    $username = $params['domain'] ?? ($params['username'] ?? '');

    $post = array_merge([
        'mn_bh'    => $node_id,
        'mn_key'   => $api_key,
        'mn_keye'  => $call_key,
        'mn_vs'    => 15,
        'username' => $username,
    ], $extra);

    // DEBUG：打印请求参数摘要（定位鉴权问题用，确认后可删除）
    $dbg = "gn={$gn} | mn_bh=[{$post['mn_bh']}] | mn_key_len=" . strlen($post['mn_key'])
        . " | mn_keye=[" . substr($post['mn_keye'], 0, 6) . '***' . substr($post['mn_keye'], -4) . '](len=' . strlen($post['mn_keye']) . ')'
        . " | username=[{$post['username']}]";

    $url = $api_url . '?gn=' . urlencode($gn);

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => $url,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => http_build_query($post),
        CURLOPT_TIMEOUT        => $timeout,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_HTTPHEADER     => ['Content-Type: application/x-www-form-urlencoded; charset=UTF-8'],
    ]);
    $resp = curl_exec($ch);
    $errno = curl_errno($ch);
    $error = curl_error($ch);
    curl_close($ch);

    if ($errno) {
        return ['success' => false, 'code' => 0, 'msg' => '[mnbthost] cURL 错误(' . $errno . ')：' . $error];
    }

    $decoded = json_decode($resp, true);
    if ($decoded === null) {
        return ['success' => false, 'code' => 0, 'msg' => '[mnbthost] 响应解析失败：' . substr($resp, 0, 200)];
    }

    // 附加调试摘要到响应
    $decoded['_debug'] = $dbg;
    return $decoded;
}

/**
 * 将 MNBT API 响应转为魔方模块返回值
 */
function _mnbthost_return($api_result)
{
    if (($api_result['success'] ?? false) && ($api_result['code'] ?? 0) == 200) {
        return 'success';
    }
    return $api_result['msg'] ?? '未知错误';
}

// ========================================================================
//  测试连接
// ========================================================================

function mnbthost_TestLink($params)
{
    list($api_url, $api_key, $node_id) = _mnbthost_resolve_params($params);

    if (empty($api_url))    return ['status' => 200, 'data' => ['server_status' => 0, 'msg' => '未配置服务器 IP/域名']];
    if (empty($api_key))    return ['status' => 200, 'data' => ['server_status' => 0, 'msg' => '未配置 Access Hash（系统 API 密钥）']];
    if (empty($node_id))    return ['status' => 200, 'data' => ['server_status' => 0, 'msg' => '未配置用户名（节点 btdh）']];

    // cfif 需要 username 非空，测试连接时无产品账户故传占位值
    $r = _mnbthost_api_call($params, 'cfif', ['username' => 'test'], 15);
    if (($r['success'] ?? false) && ($r['code'] ?? 0) == 200) {
        return ['status' => 200, 'data' => ['server_status' => 1]];
    }
    $msg = $r['msg'] ?? '未知错误';
    if (!empty($r['_debug'])) {
        $msg .= ' [' . $r['_debug'] . ']';
    }
    return ['status' => 200, 'data' => ['server_status' => 0, 'msg' => $msg]];
}

// ========================================================================
//  生命周期方法
// ========================================================================

/**
 * 开通主机（开通即建站：FTP + 数据库 + 站点）
 */
function mnbthost_CreateAccount($params)
{
    $username = $params['domain'] ?? '';
    if (empty($username)) {
        $username = 'zh_' . $params['hostid'];
    }

    $password = $params['password'] ?? '';
    if (empty($password)) {
        $password = substr(md5(uniqid(mt_rand(), true)), 0, 12);
    }

    $dqtime = $params['nextduedate'] ?? '';
    if (empty($dqtime) || $dqtime == '0000-00-00') {
        $dqtime = '0';
    }

    $co = $params['configoptions'] ?? [];
    $extra = [
        'username' => $username,
        'password' => $password,
        'dqtime'   => $dqtime,
        'webdx'    => $co['webdx'] ?? 500,
        'sqldx'    => $co['sqldx'] ?? 100,
        'sizemax'  => $co['sizemax'] ?? 0,
        'ymbds'    => $co['ymbds'] ?? 1,
    ];

    $r = _mnbthost_api_call($params, 'kt', $extra);
    return _mnbthost_return($r);
}

/** 暂停 */
function mnbthost_SuspendAccount($params)
{
    $r = _mnbthost_api_call($params, 'zt');
    return _mnbthost_return($r);
}

/** 解除暂停 */
function mnbthost_UnsuspendAccount($params)
{
    $r = _mnbthost_api_call($params, 'jc');
    return _mnbthost_return($r);
}

/** 删除（删站点 + 删行） */
function mnbthost_TerminateAccount($params)
{
    $r = _mnbthost_api_call($params, 'tz');
    return _mnbthost_return($r);
}

/** 续费 */
function mnbthost_Renew($params)
{
    $dqtime = $params['nextduedate'] ?? '';
    if (empty($dqtime) || $dqtime == '0000-00-00') {
        $dqtime = '0';
    }
    $r = _mnbthost_api_call($params, 'xf', ['setdate' => $dqtime]);
    return _mnbthost_return($r);
}

/** 升降级（更新空间/数据库/流量配额，仅传变更项） */
function mnbthost_ChangePackage($params)
{
    $upgrade = $params['configoptions_upgrade'] ?? [];
    $co = $params['configoptions'] ?? [];

    $extra = [];
    if (isset($upgrade['webdx']))   $extra['websize'] = $co['webdx'];
    if (isset($upgrade['sqldx']))   $extra['sqlsize'] = $co['sqldx'];
    if (isset($upgrade['sizemax'])) $extra['ll'] = $co['sizemax'];

    if (empty($extra)) {
        // 无配额变更时仍调用一次确保同步（zjmode 需传全量，用现有值兜底）
        $extra = [
            'websize' => $co['webdx'] ?? 0,
            'sqlsize' => $co['sqldx'] ?? 0,
            'll'      => $co['sizemax'] ?? 0,
        ];
    }
    $r = _mnbthost_api_call($params, 'zjmode', $extra);
    return _mnbthost_return($r);
}

/** 重置密码（idcsmart 将新密码作为第二参数传入） */
function mnbthost_CrackPassword($params, $new_pass = '')
{
    if (empty($new_pass)) return '缺少新密码';
    $r = _mnbthost_api_call($params, 'czmm', ['password' => $new_pass]);
    return _mnbthost_return($r);
}

// ========================================================================
//  站点启停
// ========================================================================

/** 开机（启动站点） */
function mnbthost_On($params)
{
    $r = _mnbthost_api_call($params, 'start');
    return _mnbthost_return($r);
}

/** 关机（停止站点） */
function mnbthost_Off($params)
{
    $r = _mnbthost_api_call($params, 'stop');
    return _mnbthost_return($r);
}

// ========================================================================
//  状态 & 同步
// ========================================================================

/**
 * 获取主机状态（调 gn=ztcx：状态 + 配额用量）
 */
function mnbthost_Status($params)
{
    $r = _mnbthost_api_call($params, 'ztcx', [], 60);
    if (!($r['success'] ?? false) || ($r['code'] ?? 0) != 200) {
        return [
            'status' => 'error',
            'msg'    => $r['msg'] ?? '状态查询失败',
        ];
    }

    $data = $r['data'] ?? [];
    $user = $data['user'] ?? [];
    $qk = $user['qk'] ?? 'true';
    $datae = $user['datae'] ?? '0000-00-00';

    // 状态映射：qk=false → 暂停；到期 → 暂停；否则运行中
    $today = date('Y-m-d');
    if ($qk == 'false') {
        $status = 'suspend';
        $des = '已暂停';
    } elseif ($datae != '0000-00-00' && strtotime($today) > strtotime($datae)) {
        $status = 'suspend';
        $des = '已到期';
    } else {
        $status = 'on';
        $des = '运行中';
    }

    return [
        'status' => 'success',
        'data'   => [
            'status' => $status,
            'des'    => $des,
            'quota'  => $data['quota'] ?? null,
        ],
    ];
}

/** 同步 */
function mnbthost_Sync($params)
{
    return mnbthost_Status($params);
}

// ========================================================================
//  ClientArea 前台自定义输出
// ========================================================================

function mnbthost_ClientArea($params)
{
    return [
        'console' => ['name' => '主机信息'],
    ];
}

function mnbthost_ClientAreaOutput($params, $key)
{
    if ($key !== 'console') {
        return '';
    }

    // 查询最新状态
    $status_data = mnbthost_Status($params);
    $status_info = $status_data['data'] ?? ['status' => 'unknown', 'des' => '未知'];
    $status_class = $status_info['status'] ?? 'unknown';
    $quota = $status_info['quota'] ?? null;

    // 预计算配额进度百分比（0-100）
    $quota_pct = null;
    if (is_array($quota)) {
        $quota_pct = [];
        foreach (['web_size', 'sql_size', 'flow'] as $k) {
            $max = (int)($quota[$k . '_max'] ?? 0);
            $used = (int)($quota[$k . '_used'] ?? 0);
            $pct = $max > 0 ? min(100, (int)round($used / $max * 100)) : 0;
            $cls = $max > 0 ? ($pct >= 90 ? 'danger' : ($pct >= 70 ? 'warn' : 'ok')) : 'ok';
            $quota_pct[$k] = ['pct' => $pct, 'cls' => $cls];
        }
    }

    list(, , , , $console_url) = _mnbthost_resolve_params($params);

    return [
        'template' => 'templates/console.html',
        'vars'     => [
            'status_text'  => $status_info['des'],
            'status_class' => $status_class,
            'console_url'  => $console_url,
            'username'     => $params['domain'] ?? ($params['username'] ?? ''),
            'password'     => $params['password'] ?? '',
            'quota'        => $quota,
            'quota_pct'    => $quota_pct,
        ],
    ];
}

// ========================================================================
//  ClientButton & AllowFunction
// ========================================================================

function mnbthost_ClientButton($params)
{
    return [
        'console' => [
            'place' => 'console',
            'name'  => '打开主机控制台',
        ],
    ];
}

function mnbthost_AllowFunction()
{
    return [
        'client' => ['console'],
        'admin'  => [],
    ];
}

/**
 * 前台按钮：返回控制台 URL
 */
function mnbthost_console($params)
{
    list(, , , , $console_url) = _mnbthost_resolve_params($params);
    return ['status' => 'success', 'msg' => '正在跳转到主机控制台', 'url' => $console_url];
}
