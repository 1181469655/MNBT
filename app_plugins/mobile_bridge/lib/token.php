<?php
/**
 * mobile_bridge - 设备令牌：生成 / 校验 / 吊销 / 活跃回写
 *
 * 规则（PRD §4.4）：
 * - 明文 = bin2hex(random_bytes(32))，只在配对响应中出现一次，库存 sha256；
 * - 绝不使用 authcode()/SYS_KEY（仓库公开，可伪造）；
 * - 吊销即时生效：每次请求实时查库，无缓存。
 */
if (!defined('IN_CRONLITE')) exit;

if (!function_exists('mnbt_app_token_from_request')) {
	/** 从请求头提取 Bearer 令牌；兼容未透传 Authorization 的 FastCGI 环境 */
	function mnbt_app_token_from_request()
	{
		$hdr = '';
		if (function_exists('getallheaders')) {
			foreach (getallheaders() as $k => $v) {
				if (strcasecmp((string)$k, 'Authorization') === 0) { $hdr = (string)$v; break; }
			}
		}
		if ($hdr === '') $hdr = (string)($_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '');
		if ($hdr === '' && function_exists('apache_request_headers')) {
			foreach (apache_request_headers() as $k => $v) {
				if (strcasecmp((string)$k, 'Authorization') === 0) { $hdr = (string)$v; break; }
			}
		}
		if (preg_match('/^\s*Bearer\s+(\S+)\s*$/i', $hdr, $m)) return $m[1];
		return '';
	}
}

if (!function_exists('mnbt_app_device_find_by_token')) {
	/** 令牌明文 -> 启用中的设备行；查不到返回 null */
	function mnbt_app_device_find_by_token($token)
	{
		global $DB;
		if (!is_string($token) || strlen($token) !== 64 || !ctype_xdigit($token)) return null;
		$hash = hash('sha256', $token);
		return $DB->get_row_prepare(
			"SELECT * FROM `MN_plugin_app_device` WHERE `token_hash`=? AND `status`=1 LIMIT 1",
			[$hash]
		);
	}
}

if (!function_exists('mnbt_app_device_issue')) {
	/**
	 * 换发令牌。同一 device_id 重复配对视为换绑：旧行置 status=0，插入新行（PRD 交接 §2.4 建议）。
	 * 返回明文令牌（仅此一次）。
	 */
	function mnbt_app_device_issue($zj_id, $device_id, $device_name, $device_model, $app_version, $android_version)
	{
		global $DB, $date;
		$token = bin2hex(random_bytes(32));
		$DB->query_prepare(
			"UPDATE `MN_plugin_app_device` SET `status`=0 WHERE `zj_id`=? AND `device_id`=? AND `status`=1",
			[(int)$zj_id, (string)$device_id]
		);
		$DB->query_prepare(
			"INSERT INTO `MN_plugin_app_device`
			 (`zj_id`,`token_hash`,`device_id`,`device_name`,`device_model`,`app_version`,`android_version`,`created_at`,`last_seen_at`,`last_ip`,`status`)
			 VALUES (?,?,?,?,?,?,?,?,?,?,1)",
			[
				(int)$zj_id, hash('sha256', $token), mb_substr((string)$device_id, 0, 64),
				mb_substr((string)$device_name, 0, 120), mb_substr((string)$device_model, 0, 120),
				mb_substr((string)$app_version, 0, 20), mb_substr((string)$android_version, 0, 20),
				$date, $date, (string)($_SERVER['REMOTE_ADDR'] ?? ''),
			]
		);
		return $token;
	}
}

if (!function_exists('mnbt_app_device_revoke')) {
	/** 按设备行吊销（即时生效，无缓存） */
	function mnbt_app_device_revoke($device_row)
	{
		global $DB;
		$DB->query_prepare("UPDATE `MN_plugin_app_device` SET `status`=0 WHERE `id`=? LIMIT 1", [(int)$device_row['id']]);
	}
}

if (!function_exists('mnbt_app_device_revoke_by_id')) {
	/** 面板侧按 id 吊销；强制限定当前用户，防越权 */
	function mnbt_app_device_revoke_by_id($id, $zj_id)
	{
		global $DB;
		return $DB->query_prepare(
			"UPDATE `MN_plugin_app_device` SET `status`=0 WHERE `id`=? AND `zj_id`=? LIMIT 1",
			[(int)$id, (int)$zj_id]
		);
	}
}

if (!function_exists('mnbt_app_device_list')) {
	function mnbt_app_device_list($zj_id)
	{
		global $DB;
		return $DB->get_all_prepare(
			"SELECT `id`,`device_id`,`device_name`,`device_model`,`app_version`,`android_version`,`created_at`,`last_seen_at`,`last_ip`,`status`
			 FROM `MN_plugin_app_device` WHERE `zj_id`=? ORDER BY `status` DESC, `id` DESC",
			[(int)$zj_id]
		) ?: [];
	}
}

if (!function_exists('mnbt_app_touch_device')) {
	/** last_seen 回写节流 60s（PRD §6.3 第 6 步） */
	function mnbt_app_touch_device($device_row)
	{
		global $DB, $date;
		$last = (string)($device_row['last_seen_at'] ?? '');
		if ($last !== '' && (time() - strtotime($last)) < 60) return;
		$DB->query_prepare(
			"UPDATE `MN_plugin_app_device` SET `last_seen_at`=?, `last_ip`=? WHERE `id`=? LIMIT 1",
			[$date, (string)($_SERVER['REMOTE_ADDR'] ?? ''), (int)$device_row['id']]
		);
	}
}
