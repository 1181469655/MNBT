<?php
/**
 * mobile_bridge - 一次性配对码（PRD §4.3）
 *
 * 8 位大写去混淆字符集（无 0/O/1/I），random_int 逐位生成，TTL 120s，单次使用。
 * MyISAM 无事务：置 used=1 后回读 affected_rows 确认抢占，防并发双花。
 */
if (!defined('IN_CRONLITE')) exit;

if (!function_exists('mnbt_app_pair_generate')) {
	/** 为当前登录用户生成配对码，返回 code 字符串 */
	function mnbt_app_pair_generate($zj_id)
	{
		global $DB;
		$alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
		$code = '';
		for ($i = 0; $i < 8; $i++) {
			$code .= $alphabet[random_int(0, strlen($alphabet) - 1)];
		}
		$now = time();
		// 主键唯一，极小概率碰撞时重试一次
		$DB->query_prepare("DELETE FROM `MN_plugin_app_pair` WHERE `zj_id`=? AND `used`=0", [(int)$zj_id]);
		$ok = $DB->query_prepare(
			"INSERT INTO `MN_plugin_app_pair` (`code`,`zj_id`,`created_at`,`expires_at`,`used`) VALUES (?,?,?,?,0)",
			[$code, (int)$zj_id, $now, $now + 120]
		);
		if (!$ok) {
			$code = '';
			for ($i = 0; $i < 8; $i++) {
				$code .= $alphabet[random_int(0, strlen($alphabet) - 1)];
			}
			$DB->query_prepare(
				"INSERT INTO `MN_plugin_app_pair` (`code`,`zj_id`,`created_at`,`expires_at`,`used`) VALUES (?,?,?,?,0)",
				[$code, (int)$zj_id, $now, $now + 120]
			);
		}
		return $code;
	}
}

if (!function_exists('mnbt_app_pair_consume')) {
	/**
	 * 校验并抢占配对码。
	 * @return array [ok(bool), zj_id(int), reason(string)]
	 */
	function mnbt_app_pair_consume($code)
	{
		global $DB;
		$code = strtoupper(trim((string)$code));
		if ($code === '' || strlen($code) !== 8 || !preg_match('/^[A-Z2-8]{8}$/', $code)) {
			return [false, 0, 'bad_format'];
		}
		$row = $DB->get_row_prepare("SELECT * FROM `MN_plugin_app_pair` WHERE `code`=? LIMIT 1", [$code]);
		if (!is_array($row)) return [false, 0, 'not_found'];
		if ((int)$row['used'] === 1) return [false, 0, 'used'];
		if (time() > (int)$row['expires_at']) return [false, 0, 'expired'];
		$DB->query_prepare("UPDATE `MN_plugin_app_pair` SET `used`=1 WHERE `code`=? AND `used`=0 LIMIT 1", [$code]);
		if ($DB->affected() < 1) return [false, 0, 'used'];
		$check = $DB->get_row_prepare("SELECT `used`,`zj_id` FROM `MN_plugin_app_pair` WHERE `code`=? LIMIT 1", [$code]);
		if (!is_array($check) || (int)$check['used'] !== 1) return [false, 0, 'used'];
		return [true, (int)$check['zj_id'], ''];
	}
}

if (!function_exists('mnbt_app_pair_gc')) {
	/** 顺带清理过期码（面板生成新码时调用，无定时任务依赖） */
	function mnbt_app_pair_gc()
	{
		global $DB;
		$DB->query_prepare("DELETE FROM `MN_plugin_app_pair` WHERE `expires_at` < ? LIMIT 200", [time() - 3600]);
	}
}

if (!function_exists('mnbt_app_pair_page_url')) {
	/** 二维码内容：{站点根}/index.php?_r=/app/v1/pair_page&c={码}（PRD §4.2，APP 从此串解析 base+code） */
	function mnbt_app_pair_page_url($code)
	{
		$base = mnbt_app_site_base();
		return $base . '/index.php?_r=/app/v1/pair_page&c=' . rawurlencode($code);
	}
}

if (!function_exists('mnbt_app_site_base')) {
	/** 站点根（含子目录部署前缀，不带尾斜杠），scheme 按实际连接判定 */
	function mnbt_app_site_base()
	{
		$scheme = (function_exists('mnbt_request_is_secure') && mnbt_request_is_secure()) ? 'https' : 'http';
		$host = (string)($_SERVER['HTTP_HOST'] ?? 'localhost');
		$script = str_replace('\\', '/', (string)($_SERVER['SCRIPT_NAME'] ?? '/index.php'));
		$dir = rtrim(dirname($script), '/');
		if ($dir === '.' || $dir === '/') $dir = '';
		return $scheme . '://' . $host . $dir;
	}
}
