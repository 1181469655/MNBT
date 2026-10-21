<?php
/**
 * mobile_bridge - 统一响应包络
 *
 * bridge 对外只发形态 1（PRD §4.5）：
 *   {"success":bool,"code":int,"msg":string,"redirect":null,"data":{...},"meta":{...}}
 * 错误码表见 PRD §4.6。业务字段放 data，同时平铺顶层一份（APP 两端都读）。
 */
if (!defined('IN_CRONLITE')) exit;

if (!defined('MOBILE_BRIDGE_VERSION')) {
	define('MOBILE_BRIDGE_VERSION', '1.0.0');
}

// action -> 所需最低插件版本；能力协商用，低于站点插件版本时返回 1010（PRD §5.9）
if (!function_exists('mnbt_app_required_bridge_version')) {
	function mnbt_app_required_bridge_version($action)
	{
		return '1.0.0';
	}
}

if (!function_exists('mnbt_app_bridge_meta')) {
	function mnbt_app_bridge_meta($action = '')
	{
		return [
			'action' => (string)$action,
			'gn' => (string)($GLOBALS['mnbt_app_resolved_gn'] ?? ''),
			'api' => 'app/v1',
			'bridge_version' => MOBILE_BRIDGE_VERSION,
			'mnbt_version' => function_exists('mnbt_version_num') ? mnbt_version_num() : '',
			'ts' => time(),
		];
	}
}

if (!function_exists('mnbt_app_send')) {
	/** 输出包络并终止请求；$data 的键同时平铺到顶层以兼容 APP 的顶层取键逻辑 */
	function mnbt_app_send($success, $code, $msg, $data = [], $http = 200)
	{
		if (!headers_sent()) {
			@header('Content-Type: application/json; charset=UTF-8');
			http_response_code($http);
		}
		$data = is_array($data) ? $data : ['value' => $data];
		$out = [
			'success' => (bool)$success,
			'code' => (int)$code,
			'msg' => (string)$msg,
			'redirect' => null,
			'data' => $data,
			'meta' => mnbt_app_bridge_meta($GLOBALS['mnbt_app_action'] ?? ''),
		];
		foreach ($data as $k => $v) {
			if (!isset($out[$k])) $out[$k] = $v;
		}
		$json = json_encode($out, JSON_UNESCAPED_UNICODE);
		if ($json === false) {
			$fallback = ['action' => $GLOBALS['mnbt_app_action'] ?? '', 'bridge_version' => MOBILE_BRIDGE_VERSION];
			$out['data'] = ['value' => is_scalar($data) ? (string)$data : '', 'encoding_note' => 'payload_not_utf8_json'];
			$out['msg'] = mb_strcut((string)$out['msg'], 0, 200);
			$out['meta'] = $fallback;
			$json = json_encode($out, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
			if ($json === false) $json = '{"success":false,"code":5000,"msg":"JSON encode failed","data":{}}';
		}
		if (function_exists('mnbt_app_mark_replied')) mnbt_app_mark_replied();
		echo $json;
		exit;
	}
}

if (!function_exists('mnbt_app_ok')) {
	function mnbt_app_ok($msg, $data = [])
	{
		mnbt_app_send(true, 0, $msg, $data);
	}
}

if (!function_exists('mnbt_app_err')) {
	/** 业务错误：状态类错误（1003/1004/1005/1006/1010）也必须走 JSON，验收 A7 */
	function mnbt_app_err($code, $msg, $data = [], $http = 200)
	{
		mnbt_app_send(false, $code, $msg, $data, $http);
	}
}

if (!function_exists('mnbt_app_err_1008')) {
	/** 限流：必须带 retry_after（秒），APP 用它做倒计时 */
	function mnbt_app_err_1008($msg, $retry_after)
	{
		mnbt_app_send(false, 1008, $msg, ['retry_after' => max(1, (int)$retry_after)]);
	}
}

if (!function_exists('mnbt_app_err_5000')) {
	/** 服务端异常：附 trace_id，绝不透传 HTML/堆栈 */
	function mnbt_app_err_5000($reason = '')
	{
		$trace = bin2hex(random_bytes(8));
		error_log('[mobile_bridge] 5000 trace=' . $trace . ' action=' . ($GLOBALS['mnbt_app_action'] ?? '?') . ' reason=' . $reason);
		mnbt_app_send(false, 5000, '服务端异常，请重试并联系管理员', ['trace_id' => $trace]);
	}
}
