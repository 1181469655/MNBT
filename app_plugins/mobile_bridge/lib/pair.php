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
		$updated = $DB->query_prepare("UPDATE `MN_plugin_app_pair` SET `used`=1 WHERE `code`=? AND `used`=0 LIMIT 1", [$code]);
		// 抢占成功与否以「回读是否为已用」为准，不单独依赖 $DB->affected()：
		// core 的 query_prepare 执行后立即 close 语句，PDO/SQLite 那支根本不会刷新
		// $this->result，affected() 会返回上一条查询的旧值甚至 0，把首次配对误判成「已被使用」；
		// mysqli 支虽然正常，但同样以回读兜底更稳。回读为 1 即视为本请求赢得抢占
		// （一张码只对应一台主机 zj_id，即便真并发被抢先，也是同一授权主机，不构成越权）。
		$check = $DB->get_row_prepare("SELECT `used`,`zj_id` FROM `MN_plugin_app_pair` WHERE `code`=? LIMIT 1", [$code]);
		if (is_array($check) && (int)$check['used'] === 1) {
			return [true, (int)$check['zj_id'], ''];
		}
		if ($updated === false) {
			error_log('[mobile_bridge] pair 抢占 UPDATE 执行失败（检查表引擎/权限）: code_len=' . strlen($code));
			return [false, 0, 'not_found'];
		}
		// 回读仍是未用：多为极窄并发被其它请求抢占；本请求按已用处理，引导重新生成。
		return [false, 0, 'used'];
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

if (!function_exists('mnbt_app_host_port')) {
	/**
	 * 客户端眼里的 host[:port]。
	 *
	 * HTTP_HOST 本应带端口，但 nginx 只传 $host（不是 $http_host）、或站前挂了
	 * 改写 Host 的反代/CDN 时端口会被吃掉，于是二维码里的地址少了 :8443 这一段，
	 * 手机连到默认端口上去必然失败。回退顺序：HTTP_HOST 自带端口 > 可信代理的
	 * X-Forwarded-Host 自带端口 > 按 scheme 比对非默认端口再补上。
	 * 只有配了 MNBT_TRUSTED_PROXIES 且来源命中时才采信 X-Forwarded-*。
	 */
	function mnbt_app_host_port($secure)
	{
		$trusted = function_exists('mnbt_request_from_trusted_proxy') && mnbt_request_from_trusted_proxy();
		$host = (string)($_SERVER['HTTP_HOST'] ?? '');
		if (strpos($host, ':') === false && $trusted && !empty($_SERVER['HTTP_X_FORWARDED_HOST'])) {
			$fwd = trim(explode(',', (string)$_SERVER['HTTP_X_FORWARDED_HOST'])[0]);
			if ($fwd !== '') $host = $fwd;
		}
		$host = preg_replace('/[^A-Za-z0-9.\-:_\[\]]/', '', $host);
		if ($host === '') $host = (string)($_SERVER['SERVER_NAME'] ?? 'localhost');
		if ($host === '' || strpos($host, ':') !== false) return $host;
		$port = 0;
		if ($trusted && !empty($_SERVER['HTTP_X_FORWARDED_PORT'])) $port = (int)$_SERVER['HTTP_X_FORWARDED_PORT'];
		if ($port < 1 || $port > 65535) $port = isset($_SERVER['SERVER_PORT']) ? (int)$_SERVER['SERVER_PORT'] : 0;
		if ($port < 1 || $port > 65535) return $host;
		return $port === ($secure ? 443 : 80) ? $host : $host . ':' . $port;
	}
}

if (!function_exists('mnbt_app_base_sanitize')) {
	/** 校验并规范化显式配置的对外地址；不合法返回空串（调用方回退自动探测） */
	function mnbt_app_base_sanitize($raw)
	{
		$v = trim((string)$raw);
		if ($v === '' || !preg_match('#^https?://#i', $v) || filter_var($v, FILTER_VALIDATE_URL) === false) return '';
		$p = parse_url($v);
		if (!is_array($p) || empty($p['host'])) return '';
		$scheme = (isset($p['scheme']) && strtolower($p['scheme']) === 'https') ? 'https' : 'http';
		$host = preg_replace('/[^A-Za-z0-9.\-:\[\]]/', '', (string)$p['host']);
		if ($host === '') return '';
		$port = (isset($p['port']) && $p['port'] > 0 && $p['port'] < 65536) ? ':' . (int)$p['port'] : '';
		$path = isset($p['path']) ? rtrim(str_replace('\\', '/', (string)$p['path']), '/') : '';
		if ($path !== '' && $path[0] !== '/') $path = '/' . $path;
		$path = preg_replace('#/index\.php$#i', '', $path); // 有人会把带 index.php 的整串贴进来
		return $scheme . '://' . $host . $port . $path;
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
	 *
	 * 端口映射/内网穿透（外部端口与监听端口不一致）时服务器推不出正确地址，
	 * 用 MNBT_APP_BASE_URL 显式指定，例如 https://panel.example.com:8443
	 * （nginx 用 fastcgi_param 会落到 $_SERVER，Apache SetEnv / 环境变量走 getenv）
	 */
	function mnbt_app_site_base()
	{
		$env = getenv('MNBT_APP_BASE_URL');
		if (!is_string($env) || trim($env) === '') $env = isset($_SERVER['MNBT_APP_BASE_URL']) ? (string)$_SERVER['MNBT_APP_BASE_URL'] : '';
		if (trim($env) !== '') {
			$fixed = mnbt_app_base_sanitize($env);
			if ($fixed !== '') return $fixed;
			error_log('[mobile_bridge] MNBT_APP_BASE_URL 无法解析，回退自动探测: ' . substr(trim($env), 0, 120));
		}
		$secure = function_exists('mnbt_request_is_secure') && mnbt_request_is_secure();
		$script = str_replace('\\', '/', (string)($_SERVER['SCRIPT_NAME'] ?? '/index.php'));
		$dir = rtrim(dirname($script), '/');
		if ($dir === '.' || $dir === '/') $dir = '';
		// 入口脚本位于核心子目录时去掉该级，避免把 /user、/admin 当成站点根
		$prefix = preg_replace('#/(user|admin)$#', '', $dir);
		if (isset($GLOBALS['siteWebRoot']) && is_string($GLOBALS['siteWebRoot'])) {
			$fs = rtrim(str_replace('\\', '/', $GLOBALS['siteWebRoot']), '/');
			if ($fs !== '' && $fs !== '/') $prefix = $fs;
		}
		return ($secure ? 'https' : 'http') . '://' . mnbt_app_host_port($secure) . $prefix;
	}
}
