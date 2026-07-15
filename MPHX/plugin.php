<?php
/**
 * MNBT PHP 插件引擎（P0 + P1）
 * 目录：app_plugins/{slug}/plugin.json + bootstrap.php
 */
if (!defined('IN_CRONLITE')) {
	exit;
}

define('MNBT_PLUGIN_ROOT', ROOT . 'app_plugins/');

$GLOBALS['mnbt_plugin_actions'] = [];
$GLOBALS['mnbt_plugin_filters'] = [];
$GLOBALS['mnbt_plugin_ajax'] = ['user' => [], 'admin' => []];
$GLOBALS['mnbt_plugin_pages'] = ['user' => [], 'admin' => []];
$GLOBALS['mnbt_plugin_menus'] = ['user' => [], 'admin' => []];
$GLOBALS['mnbt_plugin_widgets'] = ['user' => [], 'admin' => []];
$GLOBALS['mnbt_plugin_settings_tabs'] = [];
$GLOBALS['mnbt_plugin_meta'] = [];
$GLOBALS['mnbt_plugin_current'] = null;
$GLOBALS['mnbt_plugin_booted'] = false;
// 首页接管与通用路由（V1.81 P2）
$GLOBALS['mnbt_plugin_home_handlers'] = [];
$GLOBALS['mnbt_plugin_routes'] = [];
// 支付插件注册表（V1.81 P3）
$GLOBALS['mnbt_plugin_payments'] = [];

function mnbt_plugin_ensure_tables()
{
	global $DB;
	static $done = false;
	if ($done || !isset($DB) || !is_object($DB)) {
		return;
	}
	$done = true;
	@$DB->query("CREATE TABLE IF NOT EXISTS `MN_plugin` (
		`id` int(11) NOT NULL AUTO_INCREMENT,
		`slug` varchar(64) NOT NULL,
		`name` varchar(120) NOT NULL DEFAULT '',
		`version` varchar(32) NOT NULL DEFAULT '',
		`enabled` varchar(10) NOT NULL DEFAULT 'false',
		`config_json` mediumtext,
		`installed_at` varchar(50) NOT NULL DEFAULT '',
		`updated_at` varchar(50) NOT NULL DEFAULT '',
		PRIMARY KEY (`id`),
		UNIQUE KEY `uk_slug` (`slug`)
	) ENGINE=MyISAM DEFAULT CHARSET=utf8");
	@$DB->query("CREATE TABLE IF NOT EXISTS `MN_plugin_option` (
		`id` int(11) NOT NULL AUTO_INCREMENT,
		`plugin_slug` varchar(64) NOT NULL,
		`k` varchar(120) NOT NULL,
		`v` mediumtext,
		PRIMARY KEY (`id`),
		UNIQUE KEY `uk_plugin_k` (`plugin_slug`,`k`)
	) ENGINE=MyISAM DEFAULT CHARSET=utf8");
}

function mnbt_plugin_slug_valid($slug)
{
	return is_string($slug) && preg_match('/^[a-zA-Z0-9][a-zA-Z0-9_-]{0,62}$/', $slug);
}

function mnbt_plugin_path($slug = null)
{
	if ($slug === null) {
		$slug = $GLOBALS['mnbt_plugin_current'];
	}
	if (!mnbt_plugin_slug_valid($slug)) {
		return null;
	}
	return MNBT_PLUGIN_ROOT . $slug . '/';
}

function mnbt_plugin_url($slug = null, $rel = '')
{
	if ($slug === null) {
		$slug = $GLOBALS['mnbt_plugin_current'];
	}
	if (!mnbt_plugin_slug_valid($slug)) {
		return '';
	}
	$base = '/app_plugins/' . rawurlencode($slug) . '/';
	$rel = ltrim(str_replace('\\', '/', (string)$rel), '/');
	if ($rel === '' || strpos($rel, '..') !== false) {
		return $base;
	}
	return $base . $rel;
}

function mnbt_plugin_read_json($slug)
{
	$dir = mnbt_plugin_path($slug);
	if ($dir === null || !is_file($dir . 'plugin.json')) {
		return null;
	}
	$raw = @file_get_contents($dir . 'plugin.json');
	if ($raw === false || $raw === '') {
		return null;
	}
	$data = json_decode($raw, true);
	if (!is_array($data)) {
		return null;
	}
	$id = isset($data['id']) ? (string)$data['id'] : $slug;
	if ($id !== $slug) {
		$data['id'] = $slug;
	}
	$data['id'] = $slug;
	$data['name'] = isset($data['name']) ? (string)$data['name'] : $slug;
	$data['version'] = isset($data['version']) ? (string)$data['version'] : '0.0.0';
	$data['description'] = isset($data['description']) ? (string)$data['description'] : '';
	$data['author'] = isset($data['author']) ? (string)$data['author'] : '';
	return $data;
}

function mnbt_plugin_scan_disk()
{
	$list = [];
	if (!is_dir(MNBT_PLUGIN_ROOT)) {
		return $list;
	}
	$dirs = @scandir(MNBT_PLUGIN_ROOT) ?: [];
	foreach ($dirs as $dir) {
		if ($dir === '.' || $dir === '..' || !mnbt_plugin_slug_valid($dir)) {
			continue;
		}
		$base = MNBT_PLUGIN_ROOT . $dir;
		if (!is_dir($base) || !is_file($base . '/plugin.json') || !is_file($base . '/bootstrap.php')) {
			continue;
		}
		$meta = mnbt_plugin_read_json($dir);
		if ($meta) {
			$list[$dir] = $meta;
		}
	}
	return $list;
}

function mnbt_plugin_db_row($slug)
{
	global $DB;
	if (!mnbt_plugin_slug_valid($slug)) {
		return null;
	}
	mnbt_plugin_ensure_tables();
	return $DB->get_row_prepare("SELECT * FROM MN_plugin WHERE slug=? LIMIT 1", [$slug]) ?: null;
}

function mnbt_plugin_enabled($slug)
{
	$row = mnbt_plugin_db_row($slug);
	return $row && (($row['enabled'] ?? '') === 'true' || ($row['enabled'] ?? '') === '1');
}

function mnbt_plugin_list()
{
	global $DB;
	mnbt_plugin_ensure_tables();
	$disk = mnbt_plugin_scan_disk();
	$dbRows = $DB->get_all_prepare("SELECT * FROM MN_plugin WHERE 1") ?: [];
	$dbMap = [];
	foreach ($dbRows as $r) {
		$dbMap[$r['slug']] = $r;
	}
	$out = [];
	foreach ($disk as $slug => $meta) {
		$row = $dbMap[$slug] ?? null;
		$out[] = [
			'slug' => $slug,
			'name' => $meta['name'],
			'version' => $meta['version'],
			'description' => $meta['description'],
			'author' => $meta['author'],
			'meta' => $meta,
			'installed' => $row ? true : false,
			'enabled' => $row && (($row['enabled'] ?? '') === 'true' || ($row['enabled'] ?? '') === '1'),
			'db_version' => $row['version'] ?? '',
			'updated_at' => $row['updated_at'] ?? '',
		];
	}
	usort($out, function ($a, $b) {
		return strcmp($a['slug'], $b['slug']);
	});
	return $out;
}

function mnbt_plugin_run_sql_file($file)
{
	global $DB;
	if (!is_file($file)) {
		return true;
	}
	$sql = @file_get_contents($file);
	if ($sql === false || trim($sql) === '') {
		return true;
	}
	$parts = preg_split('/;\s*[\r\n]+/', $sql);
	foreach ($parts as $stmt) {
		$stmt = trim($stmt);
		if ($stmt === '' || strpos($stmt, '--') === 0) {
			continue;
		}
		@$DB->query($stmt);
	}
	return true;
}

function mnbt_plugin_install($slug)
{
	global $DB, $date;
	if (!mnbt_plugin_slug_valid($slug)) {
		return '插件标识无效';
	}
	$meta = mnbt_plugin_read_json($slug);
	if (!$meta) {
		return '未找到 plugin.json';
	}
	if (!is_file(mnbt_plugin_path($slug) . 'bootstrap.php')) {
		return '缺少 bootstrap.php';
	}
	mnbt_plugin_ensure_tables();
	$dir = mnbt_plugin_path($slug);
	mnbt_plugin_run_sql_file($dir . 'install.sql');
	$row = mnbt_plugin_db_row($slug);
	$now = isset($date) ? $date : date('Y-m-d H:i:s');
	if ($row) {
		$DB->query_prepare(
			"UPDATE MN_plugin SET name=?, version=?, updated_at=? WHERE slug=?",
			[$meta['name'], $meta['version'], $now, $slug]
		);
	} else {
		$DB->query_prepare(
			"INSERT INTO MN_plugin (slug, name, version, enabled, config_json, installed_at, updated_at) VALUES (?,?,?,?,?,?,?)",
			[$slug, $meta['name'], $meta['version'], 'false', '', $now, $now]
		);
	}
	return true;
}

function mnbt_plugin_set_enabled($slug, $enabled)
{
	global $DB, $date;
	if (!mnbt_plugin_slug_valid($slug)) {
		return '插件标识无效';
	}
	$meta = mnbt_plugin_read_json($slug);
	if (!$meta) {
		return '插件不存在';
	}
	mnbt_plugin_ensure_tables();
	$row = mnbt_plugin_db_row($slug);
	if (!$row) {
		$r = mnbt_plugin_install($slug);
		if ($r !== true) {
			return $r;
		}
		$row = mnbt_plugin_db_row($slug);
	}
	$flag = $enabled ? 'true' : 'false';
	$now = isset($date) ? $date : date('Y-m-d H:i:s');
	$DB->query_prepare(
		"UPDATE MN_plugin SET enabled=?, name=?, version=?, updated_at=? WHERE slug=?",
		[$flag, $meta['name'], $meta['version'], $now, $slug]
	);
	return true;
}

function mnbt_plugin_uninstall($slug)
{
	global $DB;
	if (!mnbt_plugin_slug_valid($slug)) {
		return '插件标识无效';
	}
	$dir = mnbt_plugin_path($slug);
	if ($dir) {
		mnbt_plugin_run_sql_file($dir . 'uninstall.sql');
	}
	mnbt_plugin_ensure_tables();
	$DB->query_prepare("DELETE FROM MN_plugin_option WHERE plugin_slug=?", [$slug]);
	$DB->query_prepare("DELETE FROM MN_plugin WHERE slug=?", [$slug]);
	return true;
}

function mnbt_add_action($hook, $callback, $priority = 10)
{
	$hook = (string)$hook;
	$priority = (int)$priority;
	if (!isset($GLOBALS['mnbt_plugin_actions'][$hook])) {
		$GLOBALS['mnbt_plugin_actions'][$hook] = [];
	}
	if (!isset($GLOBALS['mnbt_plugin_actions'][$hook][$priority])) {
		$GLOBALS['mnbt_plugin_actions'][$hook][$priority] = [];
	}
	$GLOBALS['mnbt_plugin_actions'][$hook][$priority][] = [
		'cb' => $callback,
		'plugin' => $GLOBALS['mnbt_plugin_current'],
	];
}

function mnbt_add_filter($hook, $callback, $priority = 10)
{
	$hook = (string)$hook;
	$priority = (int)$priority;
	if (!isset($GLOBALS['mnbt_plugin_filters'][$hook])) {
		$GLOBALS['mnbt_plugin_filters'][$hook] = [];
	}
	if (!isset($GLOBALS['mnbt_plugin_filters'][$hook][$priority])) {
		$GLOBALS['mnbt_plugin_filters'][$hook][$priority] = [];
	}
	$GLOBALS['mnbt_plugin_filters'][$hook][$priority][] = [
		'cb' => $callback,
		'plugin' => $GLOBALS['mnbt_plugin_current'],
	];
}

function mnbt_do_action($hook)
{
	$args = func_get_args();
	array_shift($args);
	$hook = (string)$hook;
	if (empty($GLOBALS['mnbt_plugin_actions'][$hook])) {
		return;
	}
	$buckets = $GLOBALS['mnbt_plugin_actions'][$hook];
	ksort($buckets, SORT_NUMERIC);
	foreach ($buckets as $list) {
		foreach ($list as $item) {
			$prev = $GLOBALS['mnbt_plugin_current'];
			$GLOBALS['mnbt_plugin_current'] = $item['plugin'];
			try {
				call_user_func_array($item['cb'], $args);
			} catch (Throwable $e) {
				error_log('[MNBT plugin] action ' . $hook . ' @' . $item['plugin'] . ': ' . $e->getMessage());
			}
			$GLOBALS['mnbt_plugin_current'] = $prev;
		}
	}
}

function mnbt_apply_filters($hook, $value)
{
	$args = func_get_args();
	array_shift($args);
	$hook = (string)$hook;
	if (empty($GLOBALS['mnbt_plugin_filters'][$hook])) {
		return $value;
	}
	$buckets = $GLOBALS['mnbt_plugin_filters'][$hook];
	ksort($buckets, SORT_NUMERIC);
	foreach ($buckets as $list) {
		foreach ($list as $item) {
			$prev = $GLOBALS['mnbt_plugin_current'];
			$GLOBALS['mnbt_plugin_current'] = $item['plugin'];
			try {
				$args[0] = $value;
				$value = call_user_func_array($item['cb'], $args);
			} catch (Throwable $e) {
				error_log('[MNBT plugin] filter ' . $hook . ' @' . $item['plugin'] . ': ' . $e->getMessage());
			}
			$GLOBALS['mnbt_plugin_current'] = $prev;
		}
	}
	return $value;
}

function mnbt_register_ajax($side, $gn, $callback)
{
	$side = $side === 'admin' ? 'admin' : 'user';
	$gn = (string)$gn;
	if ($gn === '' || !is_callable($callback)) {
		return false;
	}
	if (isset($GLOBALS['mnbt_plugin_ajax'][$side][$gn])) {
		error_log('[MNBT plugin] ajax gn conflict: ' . $side . '/' . $gn);
		return false;
	}
	$GLOBALS['mnbt_plugin_ajax'][$side][$gn] = [
		'cb' => $callback,
		'plugin' => $GLOBALS['mnbt_plugin_current'],
	];
	return true;
}

function mnbt_register_page($side, $page, $file, $title = '', $perm = null)
{
	$side = $side === 'admin' ? 'admin' : 'user';
	$page = preg_replace('/[^a-zA-Z0-9_-]/', '', (string)$page);
	if ($page === '' || $file === '') {
		return false;
	}
	$slug = $GLOBALS['mnbt_plugin_current'];
	if (!mnbt_plugin_slug_valid($slug)) {
		return false;
	}
	$GLOBALS['mnbt_plugin_pages'][$side][$slug . ':' . $page] = [
		'plugin' => $slug,
		'page' => $page,
		'file' => $file,
		'title' => $title,
		'perm' => $perm,
	];
	return true;
}

function mnbt_register_menu($side, $item)
{
	$side = $side === 'admin' ? 'admin' : 'user';
	if (!is_array($item) || empty($item['title'])) {
		return false;
	}
	$slug = $GLOBALS['mnbt_plugin_current'];
	$item['plugin'] = $slug;
	$item['order'] = isset($item['order']) ? (int)$item['order'] : 50;
	if (empty($item['url']) && !empty($item['page'])) {
		$base = $side === 'admin' ? 'plugin.php' : 'plugin.php';
		$item['url'] = $base . '?p=' . rawurlencode($slug) . '&page=' . rawurlencode($item['page']);
	}
	$GLOBALS['mnbt_plugin_menus'][$side][] = $item;
	return true;
}

function mnbt_plugin_option_get($slug, $key, $default = null)
{
	global $DB;
	if (!mnbt_plugin_slug_valid($slug) || $key === '') {
		return $default;
	}
	mnbt_plugin_ensure_tables();
	$row = $DB->get_row_prepare("SELECT v FROM MN_plugin_option WHERE plugin_slug=? AND k=? LIMIT 1", [$slug, (string)$key]);
	if (!$row) {
		return $default;
	}
	$v = $row['v'];
	if (is_string($v) && $v !== '' && ($v[0] === '{' || $v[0] === '[' || $v[0] === '"')) {
		$j = json_decode($v, true);
		if (json_last_error() === JSON_ERROR_NONE) {
			return $j;
		}
	}
	return $v;
}

function mnbt_plugin_option_set($slug, $key, $value)
{
	global $DB;
	if (!mnbt_plugin_slug_valid($slug) || $key === '') {
		return false;
	}
	mnbt_plugin_ensure_tables();
	if (is_array($value) || is_object($value)) {
		$value = json_encode($value, JSON_UNESCAPED_UNICODE);
	} else {
		$value = (string)$value;
	}
	$exist = $DB->get_row_prepare("SELECT id FROM MN_plugin_option WHERE plugin_slug=? AND k=? LIMIT 1", [$slug, (string)$key]);
	if ($exist) {
		return (bool)$DB->query_prepare("UPDATE MN_plugin_option SET v=? WHERE plugin_slug=? AND k=?", [$value, $slug, (string)$key]);
	}
	return (bool)$DB->query_prepare("INSERT INTO MN_plugin_option (plugin_slug, k, v) VALUES (?,?,?)", [$slug, (string)$key, $value]);
}

function mnbt_plugin_option_all($slug)
{
	global $DB;
	if (!mnbt_plugin_slug_valid($slug)) {
		return [];
	}
	mnbt_plugin_ensure_tables();
	$rows = $DB->get_all_prepare("SELECT k,v FROM MN_plugin_option WHERE plugin_slug=?", [$slug]) ?: [];
	$out = [];
	foreach ($rows as $r) {
		$out[$r['k']] = mnbt_plugin_option_get($slug, $r['k'], $r['v']);
	}
	return $out;
}

function mnbt_plugin_require_admin()
{
	global $islogin;
	if (!isset($islogin) || (int)$islogin !== 1) {
		if (function_exists('json_exit')) {
			json_exit('请登陆');
		}
		exit('{"code":"请登陆"}');
	}
}

function mnbt_plugin_require_user()
{
	global $islogins;
	if (!isset($islogins) || (int)$islogins !== 1) {
		if (function_exists('json_exit')) {
			json_exit('请登陆');
		}
		exit('{"code":"请登陆"}');
	}
}

function mnbt_plugin_dispatch_ajax($side, $egn)
{
	$side = $side === 'admin' ? 'admin' : 'user';
	$egn = (string)$egn;
	if ($egn === '' || empty($GLOBALS['mnbt_plugin_ajax'][$side][$egn])) {
		return false;
	}
	$item = $GLOBALS['mnbt_plugin_ajax'][$side][$egn];
	$prev = $GLOBALS['mnbt_plugin_current'];
	$GLOBALS['mnbt_plugin_current'] = $item['plugin'];
	try {
		call_user_func($item['cb'], $egn, $side);
	} catch (Throwable $e) {
		error_log('[MNBT plugin] ajax ' . $side . '/' . $egn . ': ' . $e->getMessage());
		if (function_exists('json_exit')) {
			json_exit('插件接口异常');
		}
		exit('{"code":"插件接口异常"}');
	}
	$GLOBALS['mnbt_plugin_current'] = $prev;
	return true;
}

function mnbt_plugin_menus($side)
{
	$side = $side === 'admin' ? 'admin' : 'user';
	$items = $GLOBALS['mnbt_plugin_menus'][$side] ?? [];
	$items = mnbt_apply_filters('menu.' . $side, $items);
	usort($items, function ($a, $b) {
		return ($a['order'] ?? 50) - ($b['order'] ?? 50);
	});
	return $items;
}

function mnbt_plugin_render_menu_side_html($side)
{
	$side = $side === 'admin' ? 'admin' : 'user';
	$items = mnbt_plugin_menus($side);
	if (!$items) {
		return '';
	}
	$html = '';
	foreach ($items as $it) {
		$title = htmlspecialchars($it['title'] ?? '', ENT_QUOTES, 'UTF-8');
		$url = htmlspecialchars($it['url'] ?? 'javascript:void(0)', ENT_QUOTES, 'UTF-8');
		$icon = htmlspecialchars($it['icon'] ?? 'mdi-puzzle', ENT_QUOTES, 'UTF-8');
		$mt = !empty($it['multitabs']) || strpos($url, 'plugin.php') !== false ? ' multitabs' : '';
		$html .= '<li> <a class="' . trim($mt) . '" href="' . $url . '"><i class="mdi ' . $icon . '"></i> ' . $title . '</a> </li>';
	}
	if ($html === '') {
		return '';
	}
	return '<li class="nav-item nav-item-has-subnav"> <a href="javascript:void(0)"> <i class="mdi mdi-puzzle"></i> <span>插件</span> </a><ul class="nav nav-subnav">' . $html . '</ul></li>';
}

function mnbt_plugin_render_menu_admin_html()
{
	return mnbt_plugin_render_menu_side_html('admin');
}

function mnbt_plugin_render_menu_user_html()
{
	return mnbt_plugin_render_menu_side_html('user');
}

/**
 * 注册仪表盘小部件
 * $item: title, html|callback, order, class
 */
function mnbt_register_widget($side, $item)
{
	$side = $side === 'admin' ? 'admin' : 'user';
	if (!is_array($item) || (empty($item['title']) && empty($item['html']) && empty($item['callback']))) {
		return false;
	}
	$item['plugin'] = $GLOBALS['mnbt_plugin_current'];
	$item['order'] = isset($item['order']) ? (int)$item['order'] : 50;
	$GLOBALS['mnbt_plugin_widgets'][$side][] = $item;
	return true;
}

function mnbt_plugin_widgets($side)
{
	$side = $side === 'admin' ? 'admin' : 'user';
	$items = $GLOBALS['mnbt_plugin_widgets'][$side] ?? [];
	$items = mnbt_apply_filters('dashboard.' . $side . '.widgets', $items);
	usort($items, function ($a, $b) {
		return ($a['order'] ?? 50) - ($b['order'] ?? 50);
	});
	return $items;
}

function mnbt_plugin_render_widgets_html($side)
{
	$items = mnbt_plugin_widgets($side);
	if (!$items) {
		return '';
	}
	$html = '<div class="row mt-3">';
	foreach ($items as $it) {
		$prev = $GLOBALS['mnbt_plugin_current'];
		$GLOBALS['mnbt_plugin_current'] = $it['plugin'] ?? null;
		$body = '';
		if (!empty($it['callback']) && is_callable($it['callback'])) {
			ob_start();
			try {
				call_user_func($it['callback'], $side);
			} catch (Throwable $e) {
				echo '小部件错误';
				error_log('[MNBT plugin] widget: ' . $e->getMessage());
			}
			$body = ob_get_clean();
		} else {
			$body = (string)($it['html'] ?? '');
		}
		$GLOBALS['mnbt_plugin_current'] = $prev;
		$title = htmlspecialchars($it['title'] ?? '', ENT_QUOTES, 'UTF-8');
		$col = htmlspecialchars($it['class'] ?? 'col-sm-6', ENT_QUOTES, 'UTF-8');
		$html .= '<div class="' . $col . '"><div class="card"><div class="card-header"><h4>' . $title . '</h4></div><div class="card-body">' . $body . '</div></div></div>';
	}
	$html .= '</div>';
	return $html;
}

/**
 * 注册插件设置页签（出现在插件管理页或独立入口）
 * $item: id, title, page|url, order
 */
function mnbt_register_settings_tab($item)
{
	if (!is_array($item) || empty($item['title'])) {
		return false;
	}
	$slug = $GLOBALS['mnbt_plugin_current'];
	$item['plugin'] = $slug;
	$item['order'] = isset($item['order']) ? (int)$item['order'] : 50;
	if (empty($item['url']) && !empty($item['page'])) {
		$item['url'] = 'plugin.php?p=' . rawurlencode($slug) . '&page=' . rawurlencode($item['page']);
	}
	$GLOBALS['mnbt_plugin_settings_tabs'][] = $item;
	return true;
}

function mnbt_plugin_settings_tabs()
{
	$items = $GLOBALS['mnbt_plugin_settings_tabs'] ?? [];
	$items = mnbt_apply_filters('settings.admin.tabs', $items);
	usort($items, function ($a, $b) {
		return ($a['order'] ?? 50) - ($b['order'] ?? 50);
	});
	return $items;
}

/**
 * 安全 HTTP 请求（仅 http/https）
 * @return array{ok:bool,code:int,body:string,error:string,headers:array}
 */
function mnbt_http_request($method, $url, $body = null, $opts = [])
{
	$out = ['ok' => false, 'code' => 0, 'body' => '', 'error' => '', 'headers' => []];
	$url = trim((string)$url);
	if ($url === '' || !preg_match('#^https?://#i', $url)) {
		$out['error'] = '仅允许 http/https URL';
		return $out;
	}
	$parts = @parse_url($url);
	if (!$parts || empty($parts['host'])) {
		$out['error'] = 'URL 无效';
		return $out;
	}
	$host = strtolower($parts['host']);
	if ($host === 'localhost' || $host === '127.0.0.1' || $host === '::1' || preg_match('/^(10\.|192\.168\.|172\.(1[6-9]|2\d|3[01])\.)/', $host)) {
		if (empty($opts['allow_private'])) {
			$out['error'] = '禁止访问内网地址';
			return $out;
		}
	}
	if (!function_exists('curl_init')) {
		$out['error'] = 'curl 不可用';
		return $out;
	}
	$method = strtoupper($method ?: 'GET');
	$timeout = isset($opts['timeout']) ? max(1, (int)$opts['timeout']) : 15;
	$headers = isset($opts['headers']) && is_array($opts['headers']) ? $opts['headers'] : [];
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_MAXREDIRS, 3);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
	curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, !empty($opts['insecure']) ? false : true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, !empty($opts['insecure']) ? 0 : 2);
	curl_setopt($ch, CURLOPT_USERAGENT, $opts['user_agent'] ?? 'MNBT-Plugin/1.81');
	if ($method === 'POST') {
		curl_setopt($ch, CURLOPT_POST, true);
		if ($body !== null) {
			if (is_array($body)) {
				$body = json_encode($body, JSON_UNESCAPED_UNICODE);
				$hasCt = false;
				foreach ($headers as $h) {
					if (stripos($h, 'Content-Type:') === 0) {
						$hasCt = true;
						break;
					}
				}
				if (!$hasCt) {
					$headers[] = 'Content-Type: application/json; charset=utf-8';
				}
			}
			curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
		}
	} elseif ($method !== 'GET') {
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
		if ($body !== null) {
			curl_setopt($ch, CURLOPT_POSTFIELDS, is_array($body) ? json_encode($body, JSON_UNESCAPED_UNICODE) : $body);
		}
	}
	if ($headers) {
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	}
	$resp = curl_exec($ch);
	$err = curl_error($ch);
	$code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
	curl_close($ch);
	if ($resp === false) {
		$out['error'] = $err ?: '请求失败';
		$out['code'] = $code;
		return $out;
	}
	$out['ok'] = $code >= 200 && $code < 300;
	$out['code'] = $code;
	$out['body'] = (string)$resp;
	if (!$out['ok'] && $out['error'] === '') {
		$out['error'] = 'HTTP ' . $code;
	}
	return $out;
}

function mnbt_http_get($url, $opts = [])
{
	return mnbt_http_request('GET', $url, null, $opts);
}

function mnbt_http_post($url, $body = null, $opts = [])
{
	return mnbt_http_request('POST', $url, $body, $opts);
}

/** 当前插件 slug（bootstrap/钩子回调内有效） */
function mnbt_plugin_id()
{
	return $GLOBALS['mnbt_plugin_current'];
}

function mnbt_plugin_find_page($side, $plugin, $page)
{
	$key = $plugin . ':' . $page;
	return $GLOBALS['mnbt_plugin_pages'][$side][$key] ?? null;
}

function mnbt_plugin_render_page($side, $plugin, $page)
{
	$side = $side === 'admin' ? 'admin' : 'user';
	$info = mnbt_plugin_find_page($side, $plugin, $page);
	if (!$info) {
		http_response_code(404);
		echo '插件页面不存在';
		return false;
	}
	$file = $info['file'];
	if ($file[0] !== '/' && strpos($file, ':') === false) {
		$file = mnbt_plugin_path($plugin) . ltrim(str_replace('\\', '/', $file), '/');
	}
	$realPlugin = realpath(mnbt_plugin_path($plugin));
	$realFile = realpath($file);
	if ($realPlugin === false || $realFile === false || strpos($realFile, $realPlugin) !== 0 || !is_file($realFile)) {
		http_response_code(404);
		echo '插件页面文件无效';
		return false;
	}
	$prev = $GLOBALS['mnbt_plugin_current'];
	$GLOBALS['mnbt_plugin_current'] = $plugin;
	extract($GLOBALS, EXTR_SKIP);
	$title = $info['title'] ?: ($plugin . ' / ' . $page);
	include $realFile;
	$GLOBALS['mnbt_plugin_current'] = $prev;
	return true;
}

function mnbt_plugin_register($id, $meta = [])
{
	if (!mnbt_plugin_slug_valid($id)) {
		return false;
	}
	$GLOBALS['mnbt_plugin_meta'][$id] = is_array($meta) ? $meta : [];
	$GLOBALS['mnbt_plugin_meta'][$id]['id'] = $id;
	return true;
}

function mnbt_plugins_boot()
{
	global $DB;
	if (!empty($GLOBALS['mnbt_plugin_booted'])) {
		return;
	}
	$GLOBALS['mnbt_plugin_booted'] = true;
	if (!is_dir(MNBT_PLUGIN_ROOT)) {
		@mkdir(MNBT_PLUGIN_ROOT, 0755, true);
	}
	mnbt_plugin_ensure_tables();
	$rows = @$DB->get_all_prepare("SELECT * FROM MN_plugin WHERE enabled=? OR enabled=?", ['true', '1']) ?: [];
	foreach ($rows as $row) {
		$slug = $row['slug'] ?? '';
		if (!mnbt_plugin_slug_valid($slug)) {
			continue;
		}
		$boot = mnbt_plugin_path($slug) . 'bootstrap.php';
		if (!is_file($boot)) {
			continue;
		}
		$meta = mnbt_plugin_read_json($slug);
		$GLOBALS['mnbt_plugin_current'] = $slug;
		$GLOBALS['mnbt_plugin_meta'][$slug] = $meta ?: ['id' => $slug];
		try {
			include $boot;
		} catch (Throwable $e) {
			error_log('[MNBT plugin] boot ' . $slug . ': ' . $e->getMessage());
		}
		$GLOBALS['mnbt_plugin_current'] = null;
	}
	mnbt_do_action('boot');
	global $islogin, $islogins;
	if (isset($islogin) && (int)$islogin === 1) {
		mnbt_do_action('init.admin');
	}
	if (isset($islogins) && (int)$islogins === 1) {
		mnbt_do_action('init.user');
	}
}

/**
 * ============================================================
 *  V1.81 P2：首页接管与通用路由
 * ============================================================
 */

/**
 * 计算当前请求相对于站点根目录的路径（去掉 base path 与查询串）。
 * 用于子目录部署：https://example.com/mnbt/landing?x=1 → /landing
 * @return array{path:string,method:string,base:string}
 */
function mnbt_plugin_request_info()
{
	$scriptName = isset($_SERVER['SCRIPT_NAME']) ? str_replace('\\', '/', $_SERVER['SCRIPT_NAME']) : '';
	$basePath = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');
	if ($basePath === '.' || $basePath === '/') {
		$basePath = '';
	}
	$uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
	$path = parse_url($uri, PHP_URL_PATH) ?: '';
	// 去掉 base path 前缀
	if ($basePath !== '' && strpos($path, $basePath) === 0) {
		$path = substr($path, strlen($basePath));
	}
	if ($path === '' || $path === false) {
		$path = '/';
	}
	// 规范化：确保以 / 开头
	if ($path !== '' && $path[0] !== '/') {
		$path = '/' . $path;
	}
	$method = isset($_SERVER['REQUEST_METHOD']) ? strtoupper($_SERVER['REQUEST_METHOD']) : 'GET';
	return ['path' => $path, 'method' => $method, 'base' => $basePath];
}

/**
 * 注册首页接管回调。
 *
 * 回调签名：function (array $ctx): mixed
 *   - 返回 string  → 视为重定向 URL，引擎会 header("Location: ...") + exit
 *   - 返回 true    → 视为已渲染（回调内自行 echo），引擎直接 exit
 *   - 返回 false/null → 不接管，继续下一个回调
 *
 * @param callable $callback
 * @param int      $priority  数字越小越先执行（默认 10）
 * @return bool
 */
function mnbt_register_home($callback, $priority = 10)
{
	if (!is_callable($callback)) {
		return false;
	}
	$priority = (int)$priority;
	if (!isset($GLOBALS['mnbt_plugin_home_handlers'][$priority])) {
		$GLOBALS['mnbt_plugin_home_handlers'][$priority] = [];
	}
	$GLOBALS['mnbt_plugin_home_handlers'][$priority][] = [
		'cb' => $callback,
		'plugin' => $GLOBALS['mnbt_plugin_current'],
	];
	return true;
}

/**
 * 分发首页接管。
 * 由根目录 index.php 在请求路径为 / 时调用。
 *
 * @return bool  true 表示已被插件接管（请求已终止）；false 表示无插件接管，调用方走默认逻辑
 */
function mnbt_plugin_dispatch_home()
{
	if (empty($GLOBALS['mnbt_plugin_home_handlers'])) {
		return false;
	}
	$info = mnbt_plugin_request_info();
	// 仅当路径为 / 时才视作"首页"请求
	if ($info['path'] !== '/') {
		return false;
	}
	$buckets = $GLOBALS['mnbt_plugin_home_handlers'];
	ksort($buckets, SORT_NUMERIC);
	$ctx = [
		'path' => $info['path'],
		'method' => $info['method'],
		'base' => $info['base'],
	];
	foreach ($buckets as $list) {
		foreach ($list as $item) {
			$prev = $GLOBALS['mnbt_plugin_current'];
			$GLOBALS['mnbt_plugin_current'] = $item['plugin'];
			try {
				$result = call_user_func($item['cb'], $ctx);
			} catch (Throwable $e) {
				error_log('[MNBT plugin] home @' . ($item['plugin'] ?? '?') . ': ' . $e->getMessage());
				$GLOBALS['mnbt_plugin_current'] = $prev;
				continue;
			}
			$GLOBALS['mnbt_plugin_current'] = $prev;
			// 返回字符串 → 重定向
			if (is_string($result) && $result !== '') {
				header('Location: ' . $result);
				exit;
			}
			// 返回 true → 已渲染
			if ($result === true) {
				exit;
			}
			// false / null / 其他 → 不接管，继续
		}
	}
	return false;
}

/**
 * 注册通用路由。
 *
 * 路径支持命名参数：/promo/{id}  →  匹配 /promo/123，回调收到 ['id'=>'123']
 * 路径必须以 / 开头；尾斜杠可选（自动同时匹配带/不带尾斜杠两种形式）。
 *
 * 回调签名：function (array $params, array $ctx): mixed
 *   - 回调内自行 echo 输出，返回 true 或不返回（null）→ 引擎 exit 终止
 *   - 返回 false → 不接管，继续匹配下一个路由
 *
 * @param string   $method    'GET'/'POST'/'PUT'/'DELETE'/'HEAD'/'*'（* 匹配任意）
 * @param string   $path      如 '/landing' 或 '/promo/{id}'
 * @param callable $callback
 * @param int      $priority
 * @return bool
 */
function mnbt_register_route($method, $path, $callback, $priority = 10)
{
	if (!is_callable($callback)) {
		return false;
	}
	$method = strtoupper((string)$method);
	if ($method === '') {
		$method = '*';
	}
	$path = (string)$path;
	if ($path === '' || $path[0] !== '/') {
		$path = '/' . $path;
	}
	// 编译路径为正则 + 参数名列表
	$paramNames = [];
	$regex = preg_replace_callback('/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/', function ($m) use (&$paramNames) {
		$paramNames[] = $m[1];
		return '([^/]+)';
	}, $path);
	$regex = '#^' . $regex . '/?$#';
	$priority = (int)$priority;
	if (!isset($GLOBALS['mnbt_plugin_routes'][$priority])) {
		$GLOBALS['mnbt_plugin_routes'][$priority] = [];
	}
	$GLOBALS['mnbt_plugin_routes'][$priority][] = [
		'method' => $method,
		'path' => $path,
		'regex' => $regex,
		'params' => $paramNames,
		'cb' => $callback,
		'plugin' => $GLOBALS['mnbt_plugin_current'],
	];
	return true;
}

/**
 * 分发通用路由。
 * 由 index.php 或 _router.php 在请求未命中实际文件时调用。
 *
 * @return bool  true 表示已匹配并由插件处理（请求已终止）；false 表示无匹配
 */
function mnbt_plugin_dispatch_route()
{
	if (empty($GLOBALS['mnbt_plugin_routes'])) {
		return false;
	}
	$info = mnbt_plugin_request_info();
	$buckets = $GLOBALS['mnbt_plugin_routes'];
	ksort($buckets, SORT_NUMERIC);
	foreach ($buckets as $list) {
		foreach ($list as $item) {
			// 方法匹配
			if ($item['method'] !== '*' && $item['method'] !== $info['method']) {
				continue;
			}
			// 路径匹配
			if (!preg_match($item['regex'], $info['path'], $matches)) {
				continue;
			}
			// 提取命名参数
			$params = [];
			array_shift($matches); // 去掉完整匹配
			foreach ($item['params'] as $i => $name) {
				$params[$name] = isset($matches[$i]) ? $matches[$i] : '';
			}
			$prev = $GLOBALS['mnbt_plugin_current'];
			$GLOBALS['mnbt_plugin_current'] = $item['plugin'];
			$ctx = [
				'path' => $info['path'],
				'method' => $info['method'],
				'base' => $info['base'],
				'plugin' => $item['plugin'],
				'route' => $item['path'],
			];
			try {
				$result = call_user_func($item['cb'], $params, $ctx);
			} catch (Throwable $e) {
				error_log('[MNBT plugin] route ' . $item['method'] . ' ' . $item['path'] . ' @' . ($item['plugin'] ?? '?') . ': ' . $e->getMessage());
				$GLOBALS['mnbt_plugin_current'] = $prev;
				continue;
			}
			$GLOBALS['mnbt_plugin_current'] = $prev;
			// 返回 false → 显式不接管，继续匹配
			if ($result === false) {
				continue;
			}
			// 其他情况（true / null / 字符串）→ 视为已处理
			// 若回调返回字符串且未自行输出，作为兜底 echo
			if (is_string($result) && $result !== '' && !headers_sent()) {
				header('Content-Type: text/html; charset=UTF-8');
				echo $result;
			}
			exit;
		}
	}
	return false;
}

/**
 * ============================================================
 *  V1.81 P3：支付插件系统
 * ============================================================
 *
 *  支付方式 type 格式：{plugin_id}__{method_id}
 *    例：epay__alipay、alipay_official__pc
 *
 *  支付设置存储：MN_config.pay_methods 字段（JSON）
 *    [{"plugin":"epay","method":"alipay","display_name":"支付宝","icon":"mdi-puzzle","sort":1}, ...]
 *
 *  插件 API 凭证存储：MN_plugin_option 表（通过 mnbt_plugin_option_get/set）
 */

/**
 * 注册支付插件。
 *
 * @param string $plugin_id  插件标识（即 slug）
 * @param array  $config     [
 *     'name'        => '易支付',
 *     'description' => '彩虹易支付协议',
 *     'methods' => [
 *         'alipay' => ['label'=>'支付宝', 'icon'=>'mdi-puzzle'],
 *         'wxpay'  => ['label'=>'微信支付', 'icon'=>'mdi-wechat'],
 *     ],
 *     'build' => function ($method, $order, $plugin_config) {
 *         // $method: 'alipay'
 *         // $order: ['out_trade_no'=>..., 'name'=>..., 'money'=>..., 'notify_url'=>..., 'return_url'=>..., 'order_row'=>...]
 *         // $plugin_config: 该插件的所有选项（来自 MN_plugin_option）
 *         // 返回 HTML 表单字符串，或返回 false 表示不接管
 *     },
 * ]
 * @return bool
 */
function mnbt_register_payment($plugin_id, $config)
{
	$plugin_id = (string)$plugin_id;
	if ($plugin_id === '' || !is_array($config)) {
		return false;
	}
	// 校验 build 回调
	if (!isset($config['build']) || !is_callable($config['build'])) {
		return false;
	}
	if (!isset($config['methods']) || !is_array($config['methods'])) {
		$config['methods'] = [];
	}
	$config['plugin_id'] = $plugin_id;
	$GLOBALS['mnbt_plugin_payments'][$plugin_id] = $config;
	return true;
}

/**
 * 获取所有已注册的支付插件。
 * @return array  ['epay' => ['name'=>..., 'methods'=>[...]], ...]
 */
function mnbt_get_payment_plugins()
{
	return isset($GLOBALS['mnbt_plugin_payments']) ? $GLOBALS['mnbt_plugin_payments'] : [];
}

/**
 * 构造支付方式的 type 标识。
 * @param string $plugin_id
 * @param string $method_id
 * @return string  如 "epay__alipay"
 */
function mnbt_pay_type($plugin_id, $method_id)
{
	return $plugin_id . '__' . $method_id;
}

/**
 * 从 type 解析出 plugin_id 和 method_id。
 * @param string $type
 * @return array|false  ['plugin'=>'epay', 'method'=>'alipay'] 或 false
 */
function mnbt_pay_parse_type($type)
{
	$type = (string)$type;
	if (strpos($type, '__') === false) {
		return false;
	}
	$parts = explode('__', $type, 2);
	if (count($parts) !== 2 || $parts[0] === '' || $parts[1] === '') {
		return false;
	}
	return ['plugin' => $parts[0], 'method' => $parts[1]];
}

/**
 * 获取已启用的付款方式列表（从 MN_config.pay_methods JSON 解析）。
 * @return array  [['plugin'=>..., 'method'=>..., 'display_name'=>..., 'icon'=>..., 'sort'=>...], ...] 按 sort 排序
 */
function mnbt_get_enabled_payment_methods()
{
	global $DB, $siteid;
	if (!isset($DB)) {
		return [];
	}
	$siteid = isset($siteid) ? $siteid : 1;
	$row = $DB->get_row_prepare("SELECT pay_methods FROM MN_config WHERE id=? LIMIT 1", [$siteid]);
	if (!$row || empty($row['pay_methods'])) {
		return [];
	}
	$list = json_decode($row['pay_methods'], true);
	if (!is_array($list)) {
		return [];
	}
	// 按 sort 排序
	usort($list, function ($a, $b) {
		$sa = isset($a['sort']) ? (int)$a['sort'] : 99;
		$sb = isset($b['sort']) ? (int)$b['sort'] : 99;
		return $sa - $sb;
	});
	return $list;
}

/**
 * 保存付款方式配置到 MN_config.pay_methods。
 * @param array $methods  [['plugin'=>..., 'method'=>..., 'display_name'=>..., 'icon'=>..., 'sort'=>...], ...]
 * @return bool
 */
function mnbt_save_payment_methods($methods)
{
	global $DB, $siteid;
	if (!isset($DB)) {
		return false;
	}
	$siteid = isset($siteid) ? $siteid : 1;
	$json = json_encode($methods, JSON_UNESCAPED_UNICODE);
	return (bool)$DB->query_prepare("UPDATE MN_config SET pay_methods=? WHERE id=?", [$json, $siteid]);
}

/**
 * 分发支付网关：根据 type 找到对应插件的 build 回调并调用。
 *
 * @param string $type           支付方式 type，如 epay__alipay
 * @param array  $order_context  订单上下文 ['out_trade_no'=>..., 'name'=>..., 'money'=>..., ...]
 * @return string|false  HTML 表单字符串，或 false 表示无插件接管
 */
function mnbt_pay_dispatch_gateway($type, $order_context)
{
	$parsed = mnbt_pay_parse_type($type);
	if (!$parsed) {
		return false;
	}
	$plugin_id = $parsed['plugin'];
	$method_id = $parsed['method'];
	if (!isset($GLOBALS['mnbt_plugin_payments'][$plugin_id])) {
		return false;
	}
	$payment = $GLOBALS['mnbt_plugin_payments'][$plugin_id];
	if (!isset($payment['methods'][$method_id])) {
		return false;
	}
	$cb = $payment['build'];
	if (!is_callable($cb)) {
		return false;
	}
	// 加载插件选项作为配置
	$plugin_config = function_exists('mnbt_plugin_option_all') ? mnbt_plugin_option_all($plugin_id) : [];
	$prev = isset($GLOBALS['mnbt_plugin_current']) ? $GLOBALS['mnbt_plugin_current'] : null;
	$GLOBALS['mnbt_plugin_current'] = $plugin_id;
	try {
		$result = call_user_func($cb, $method_id, $order_context, $plugin_config);
	} catch (Throwable $e) {
		error_log('[MNBT plugin] payment build @' . $plugin_id . '::' . $method_id . ': ' . $e->getMessage());
		$GLOBALS['mnbt_plugin_current'] = $prev;
		return false;
	}
	$GLOBALS['mnbt_plugin_current'] = $prev;
	return $result;
}
