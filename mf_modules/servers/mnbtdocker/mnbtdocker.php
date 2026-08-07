<?php
/**
 * 梦奈宝塔Docker对接插件 — 魔方财务（idcsmart）server module
 *
 * 通过 MNBT 外部 API 对接 Docker 容器分销。
 * 单容器模型：CreateAccount 仅开通账号，容器由用户登录 MNBT 控制台后在应用商店创建。
 *
 * 关联：api/docker.php（M1 已实现 gn=kt/zt/jc/tj/xf/bg/czmm/ztcx/sy/start/stop/restart）
 */

// ========================================================================
//  MetaData
// ========================================================================

function mnbtdocker_MetaData()
{
    return [
        'DisplayName' => '梦奈宝塔Docker对接插件',
        'APIVersion'  => '1.1',
        'HelpDoc'     => 'https://github.com/MNBT/api-docker-guide', // 替换为实际帮助文档地址
    ];
}

// ========================================================================
//  ConfigOptions
// ========================================================================

function mnbtdocker_ConfigOptions()
{
    return [
        [
            'type'        => 'text',
            'name'        => '默认套餐 ID',
            'placeholder' => 'MN_docker_plan.id',
            'description' => '开通时绑定的默认套餐；不填则不绑定套餐',
            'default'     => '',
            'key'         => 'plan_id',
        ],
        [
            'type'        => 'text',
            'name'        => '控制台地址',
            'placeholder' => 'https://mnbt.example.com/docker/login.php',
            'description' => 'Docker 控制台入口（用户需用 Docker 账号登录）',
            'default'     => '',
            'key'         => 'console_url',
        ],
    ];
}

// ========================================================================
//  内部工具：参数解析 & API 调用
// ========================================================================

/**
 * 解析参数（连接信息全部来自服务器设置，配置选项仅产品级别）
 * 服务器字段映射（参照 noKVM 模式）：
 *   server_ip / server_host  → MNBT 主机
 *   port                      → 端口
 *   secure                    → HTTPS
 *   server_username           → 节点编号（MN_docker_node.id）
 *   server_password           → 调用密钥 md5(ktmy.qmk)，空则默认 md5('')
 *   accesshash                → 系统 API 密钥（$conf['api']）
 *
 * @return array [api_url, api_key, node_id, call_key, plan_id, console_url]
 */
function _mnbtdocker_resolve_params($params)
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
        $api_url .= '/api/docker.php';
    }

    $api_key     = $params['accesshash'] ?? '';
    $node_id     = $params['server_username'] ?? '';
    $call_key    = $params['server_password'] ?? '';
    // plan_id: 优先 configoption1，兼容旧位置 configoption5
    $plan_id     = $params['configoption1'] ?? ($params['configoption5'] ?? '');
    // console_url: 优先 configoption2，兼容旧位置 configoption6，兜底 server_ip
    $console_url = $params['configoption2'] ?? ($params['configoption6'] ?? '');
    if (empty($console_url) && !empty($host)) {
        $console_url = $scheme . '://' . $host;
        if (!empty($port) && $port != '80' && $port != '443') {
            $console_url .= ':' . $port;
        }
        $console_url .= '/docker/login.php';
    }

    return [$api_url, $api_key, $node_id, $call_key, $plan_id, $console_url];
}

/**
 * 调用梦奈宝塔Docker对接插件 API
 *
 * @param array  $params      魔方传入的 $params
 * @param string $gn          动作（kt/zt/jc/tj/xf/bg/czmm/ztcx/sy/start/stop/restart）
 * @param array  $extra       额外 POST 字段（业务参数）
 * @param int    $timeout     cURL 超时秒数
 * @return array             [success, code, msg, data?]
 */
function _mnbtdocker_api_call($params, $gn, $extra = [], $timeout = 30)
{
    list($api_url, $api_key, $node_id, $call_key) = _mnbtdocker_resolve_params($params);

    if (empty($api_url))    return ['success' => false, 'code' => 0, 'msg' => '[mnbtdocker] 未配置 API 地址'];
    if (empty($api_key))    return ['success' => false, 'code' => 0, 'msg' => '[mnbtdocker] 未配置系统 API 密钥'];
    if (empty($node_id))    return ['success' => false, 'code' => 0, 'msg' => '[mnbtdocker] 未配置节点编号'];
    // 调用密钥为空时默认 md5('')，适配节点未设置 ktmy/qmk 的场景
    if (empty($call_key))   $call_key = md5('');

    // Docker 账号名优先用 domain（开通时就是用它），因为 username 可能是 "root" 等主机名
    $username = $params['domain'] ?? ($params['username'] ?? '');

    $post = array_merge([
        'mn_bh'   => $node_id,
        'mn_key'  => $api_key,
        'mn_keye' => $call_key,
        'mn_vs'   => 15,
        'username' => $username,
    ], $extra);

    // DEBUG：打印请求参数（部署确认问题后可删除此段）
    $debug_info = "gn={$gn}, mn_bh=[{$post['mn_bh']}], mn_key=[len=" . strlen($post['mn_key']) . "], mn_keye=[len=" . strlen($post['mn_keye']) . "], mn_vs=[{$post['mn_vs']}], username=[{$post['username']}]";

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
        return ['success' => false, 'code' => 0, 'msg' => '[mnbtdocker] cURL 错误(' . $errno . ')：' . $error];
    }

    $decoded = json_decode($resp, true);
    if ($decoded === null) {
        return ['success' => false, 'code' => 0, 'msg' => '[mnbtdocker] 响应解析失败：' . substr($resp, 0, 200)];
    }

    // 附加调试信息到响应中
    $decoded['_debug'] = $debug_info;
    return $decoded;
}

/**
 * 将 MNBT API 响应转为魔方模块返回值
 * - 成功：返回 ['status' => 'success'] 或 'success'
 * - 失败：返回 MNBT 的 msg（含 _debug 调试信息）
 */
function _mnbtdocker_return($api_result)
{
    if (($api_result['success'] ?? false) && ($api_result['code'] ?? 0) == 200) {
        return 'success';
    }
    $msg = $api_result['msg'] ?? '未知错误';
    if (!empty($api_result['_debug'])) {
        $msg .= ' [' . $api_result['_debug'] . ']';
    }
    return $msg;
}

// ========================================================================
//  测试连接
// ========================================================================

/**
 * 测试连接（魔方命名约定：{modulename}_TestLink）
 * 校验参数配置是否完整，然后调 gn=cfif 验证 MNBT API 可达性与鉴权
 */
function mnbtdocker_TestLink($params)
{
    list($api_url, $api_key, $node_id, $call_key) = _mnbtdocker_resolve_params($params);

    if (empty($api_url))    return ['status' => 200, 'data' => ['server_status' => 0, 'msg' => '未配置服务器 IP/域名']];
    if (empty($api_key))    return ['status' => 200, 'data' => ['server_status' => 0, 'msg' => '未配置 Access Hash（系统 API 密钥）']];
    if (empty($node_id))    return ['status' => 200, 'data' => ['server_status' => 0, 'msg' => '未配置用户名（节点编号）']];

    // call_key 可为空（_mnbtdocker_api_call 自动兜底 md5('')）

    // cfif 需要 username 非空，测试连接时无产品账户故传占位值
    $r = _mnbtdocker_api_call($params, 'cfif', ['username' => 'test'], 15);
    if (($r['success'] ?? false) && ($r['code'] ?? 0) == 200) {
        return ['status' => 200, 'data' => ['server_status' => 1]];
    }
    return ['status' => 200, 'data' => ['server_status' => 0, 'msg' => $r['msg'] ?? '未知错误']];
}

// ========================================================================
//  生命周期方法
// ========================================================================

/**
 * 开通账户
 * 只开通账号，不创建容器（容器由用户登录控制台后在应用商店创建）
 */
function mnbtdocker_CreateAccount($params)
{
    list(, , , , $plan_id) = _mnbtdocker_resolve_params($params);

    // 使用魔方 domain 参数（主机名，随机唯一字符串）
    $username = $params['domain'];

    $password = $params['password'] ?? '';
    if (empty($password)) {
        $password = substr(md5(uniqid(mt_rand(), true)), 0, 12);
    }

    $dqtime = $params['nextduedate'] ?? '';
    if (empty($dqtime) || $dqtime == '0000-00-00') {
        $dqtime = '0';
    }

    $extra = [
        'username' => $username,
        'password' => $password,
        'dqtime'   => $dqtime,
        'email'    => $params['user_info']['email'] ?? '',
    ];
    if (!empty($plan_id)) {
        $extra['plan_id'] = $plan_id;
    }

    $r = _mnbtdocker_api_call($params, 'kt', $extra);
    $result = _mnbtdocker_return($r);

    if ($result === 'success' && !empty($password)) {
        // 尝试回写密码到产品表
        // idcsmart 可能通过返回值中的 password 字段自动更新
    }

    return $result;
}

/** 暂停 */
function mnbtdocker_SuspendAccount($params)
{
    $r = _mnbtdocker_api_call($params, 'zt');
    return _mnbtdocker_return($r);
}

/** 解除暂停 */
function mnbtdocker_UnsuspendAccount($params)
{
    $r = _mnbtdocker_api_call($params, 'jc');
    return _mnbtdocker_return($r);
}

/** 删除（立即删除容器 + 账号） */
function mnbtdocker_TerminateAccount($params)
{
    $r = _mnbtdocker_api_call($params, 'tj');
    return _mnbtdocker_return($r);
}

/** 续费 */
function mnbtdocker_Renew($params)
{
    $dqtime = $params['nextduedate'] ?? '';
    if (empty($dqtime) || $dqtime == '0000-00-00') {
        $dqtime = '0';
    }
    $r = _mnbtdocker_api_call($params, 'xf', ['setdate' => $dqtime]);
    return _mnbtdocker_return($r);
}

/** 升降级套餐 */
function mnbtdocker_ChangePackage($params)
{
    // 从可配置选项中获取新的 plan_id
    // 魔方升降级时 old_configoptions / configoptions 会有新旧值
    $new_plan_id = $params['configoptions']['plan_id'] ?? $params['config_options']['plan_id'] ?? 0;
    if (empty($new_plan_id)) {
        // 兼容：某些魔方版本 key 在 configoptionX 直接覆盖
        list(, , , , $new_plan_id) = _mnbtdocker_resolve_params($params);
    }
    if (empty($new_plan_id)) {
        return '未找到新套餐 ID，请确认产品可配置选项中已设置 plan_id 字段';
    }
    $r = _mnbtdocker_api_call($params, 'bg', ['plan_id' => $new_plan_id]);
    return _mnbtdocker_return($r);
}

/** 重置密码（参照 noKVM：idcsmart 将新密码作为第二个参数传入） */
function mnbtdocker_CrackPassword($params, $new_pass = '')
{
    if (empty($new_pass)) return '缺少新密码';
    $r = _mnbtdocker_api_call($params, 'czmm', ['password' => $new_pass]);
    return _mnbtdocker_return($r);
}

// ========================================================================
//  状态 & 同步
// ========================================================================

/**
 * 获取机器状态
 */
function mnbtdocker_Status($params)
{
    $r = _mnbtdocker_api_call($params, 'ztcx', [], 60);
    if (!($r['success'] ?? false) || ($r['code'] ?? 0) != 200) {
        return [
            'status' => 'error',
            'msg'    => $r['msg'] ?? '状态查询失败',
        ];
    }

    $data = $r['data'] ?? [];
    $user = $data['user'] ?? [];
    $container = $data['container'] ?? null;

    $qk = $user['qk'] ?? 'active';
    $cs  = $user['container_status'] ?? 'none';

    // 状态映射（见 PRD §5.1）
    if ($qk === 'paused' || $qk === 'expired' || $qk === 'pruned') {
        $status = 'suspend';
        $des = $qk === 'paused' ? '已暂停' : ($qk === 'pruned' ? '容器已清理' : '已到期');
    } elseif ($cs === 'none' || $cs === 'creating') {
        $status = 'waiting';
        $des = $cs === 'none' ? '未创建容器（请在控制台创建）' : '容器创建中';
    } elseif ($cs === 'running') {
        $status = 'on';
        $des = '运行中';
    } elseif ($cs === 'stopped') {
        $status = 'off';
        $des = '已停止';
    } else {
        $status = 'unknown';
        $des = '未知状态';
    }

    // 附加容器信息
    if ($container && !empty($container['port'])) {
        $des .= ' | 端口：' . implode(', ', $container['port']);
    }

    return [
        'status' => 'success',
        'data'   => [
            'status' => $status,
            'des'    => $des,
        ],
    ];
}

/** 同步 */
function mnbtdocker_Sync($params)
{
    return mnbtdocker_Status($params);
}

// ========================================================================
//  容器启停
// ========================================================================

/** 开机 */
function mnbtdocker_On($params)
{
    $r = _mnbtdocker_api_call($params, 'start');
    return _mnbtdocker_return($r);
}

/** 关机 */
function mnbtdocker_Off($params)
{
    $r = _mnbtdocker_api_call($params, 'stop');
    return _mnbtdocker_return($r);
}

/** 重启 */
function mnbtdocker_Reboot($params)
{
    $r = _mnbtdocker_api_call($params, 'restart');
    return _mnbtdocker_return($r);
}

// ========================================================================
//  ClientArea 前台自定义输出
// ========================================================================

/**
 * 前台选项卡
 */
function mnbtdocker_ClientArea($params)
{
    return [
        'console' => ['name' => '容器控制台'],
    ];
}

/**
 * 前台选项卡内容
 */
function mnbtdocker_ClientAreaOutput($params, $key)
{
    if ($key !== 'console') {
        return '';
    }

    // 查询最新状态
    $status_data = mnbtdocker_Status($params);
    $status_info  = $status_data['data'] ?? ['status' => 'unknown', 'des' => '未知'];
    $status_class = $status_info['status'] ?? 'unknown';

    list(, , , , , $console_url) = _mnbtdocker_resolve_params($params);

    return [
        'template' => 'templates/console.html',
        'vars'     => [
            'status_text'  => $status_info['des'],
            'status_class' => $status_class,
            'console_url'  => $console_url,
            'username'     => $params['domain'] ?? ($params['username'] ?? ''),
            'password'     => $params['password'] ?? '',
        ],
    ];
}

// ========================================================================
//  ClientButton & AllowFunction
// ========================================================================

/**
 * 前台自定义按钮
 */
function mnbtdocker_ClientButton($params)
{
    return [
        'console' => [
            'place' => 'console',
            'name'  => '打开容器控制台',
        ],
    ];
}

/**
 * 允许前台调用的自定义方法
 */
function mnbtdocker_AllowFunction()
{
    return [
        'client' => ['console'],
        'admin'  => [],
    ];
}

/**
 * 前台按钮调用的自定义方法：返回控制台 URL 供魔方弹窗或跳转
 */
function mnbtdocker_console($params)
{
    list(, , , , , $console_url) = _mnbtdocker_resolve_params($params);
    return ['status' => 'success', 'msg' => '正在跳转到容器控制台', 'url' => $console_url];
}
