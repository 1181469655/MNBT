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
		$now = time();
		// 同一主机只保留最新一张未用码：重新生成即让旧码立即失效
		$DB->query_prepare("DELETE FROM `MN_plugin_app_pair` WHERE `zj_id`=? AND `used`=0", [(int)$zj_id]);
		// code 是主键，极小概率碰撞时换码重试；三次都失败返回空串，由调用方报错
		for ($try = 0; $try < 3; $try++) {
			$code = '';
			for ($i = 0; $i < 8; $i++) {
				$code .= $alphabet[random_int(0, strlen($alphabet) - 1)];
			}
			$ok = $DB->query_prepare(
				"INSERT INTO `MN_plugin_app_pair` (`code`,`zj_id`,`created_at`,`expires_at`,`used`) VALUES (?,?,?,?,0)",
				[$code, (int)$zj_id, $now, $now + 120]
			);
			if ($ok) return $code;
		}
		return '';
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
	/**
	 * 站点根（含子目录部署前缀，不带尾斜杠），scheme 按实际连接判定。
	 *
	 * 不能用 dirname(SCRIPT_NAME) 单独定根：面板页在 /user/ajax.php、/user/plugin.php 下触发，
	 * 那样算出的 base 会多一段 /user，二维码里的地址直接 404。core 在 common.php 里已按
	 * DOCUMENT_ROOT 与项目物理根算出 $siteWebRoot（形如 / 或 /mnbt/），非根目录时优先采信；
	 * 它退化成 / 时再按入口脚本目录推断（伪静态下 SCRIPT_NAME 已是 /index.php，不受影响）。
	 */
	function mnbt_app_site_base()
	{
		$scheme = (function_exists('mnbt_request_is_secure') && mnbt_request_is_secure()) ? 'https' : 'http';
		$host = (string)($_SERVER['HTTP_HOST'] ?? 'localhost');
		$script = str_replace('\\', '/', (string)($_SERVER['SCRIPT_NAME'] ?? '/index.php'));
		$dir = rtrim(dirname($script), '/');
		if ($dir === '.' || $dir === '/') $dir = '';
		// 入口脚本位于核心子目录时去掉该级，避免把 /user、/admin 当成站点根
		$dir = preg_replace('#/(user|admin)$#', '', $dir);
		$prefix = $dir;
		if (isset($GLOBALS['siteWebRoot']) && is_string($GLOBALS['siteWebRoot'])) {
			$fs = rtrim(str_replace('\\', '/', $GLOBALS['siteWebRoot']), '/');
			if ($fs !== '' && $fs !== '/') $prefix = $fs;
		}
		return $scheme . '://' . $host . $prefix;
	}
}
