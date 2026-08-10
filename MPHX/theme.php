<?php
/**
 * MNBT 主题系统（用户端 + 管理端 + Docker 端）
 * 目录: templates/{theme}/user/ 、templates/{theme}/admin/ 、templates/{theme}/docker/
 * 缺页自动回退到 templates/default/{scope}/
 */

if (!defined('IN_CRONLITE')) {
	exit('Access Denied');
}

define('MNBT_THEME_ROOT', ROOT . 'templates/');
define('MNBT_THEME_DEFAULT', 'default');

// 主题可注册的菜单渲染器：scope => callback(array $items): string
$GLOBALS['mnbt_theme_menu_renderers'] = ['user' => null, 'admin' => null, 'docker' => null];

/**
 * 规范化主题名
 */
function mnbt_theme_sanitize($name)
{
	return preg_replace('/[^a-zA-Z0-9_-]/', '', (string)$name);
}

/**
 * 规范化 scope：user | admin | docker | home（非法值回退 user）
 */
function mnbt_theme_normalize_scope($scope)
{
	if ($scope === 'admin') return 'admin';
	if ($scope === 'docker') return 'docker';
	if ($scope === 'home') return 'home';
	return 'user';
}

/**
 * 当前主题名
 * @param string $scope user|admin|docker|home
 */
function mnbt_theme_name($scope = 'user')
{
	global $conf;
	$scope = mnbt_theme_normalize_scope($scope);
	$confKeyMap = ['user' => 'usertheme', 'admin' => 'admintheme', 'docker' => 'docker_theme', 'home' => 'home_theme'];
	$fileKeyMap = ['user' => 'active_user_theme', 'admin' => 'active_admin_theme', 'docker' => 'active_docker_theme', 'home' => 'active_home_theme'];
	$confKey = $confKeyMap[$scope];
	$fileKey = $fileKeyMap[$scope];

	$name = '';
	if (is_array($conf) && !empty($conf[$confKey])) {
		$name = (string)$conf[$confKey];
	}
	if ($name === '' && is_file(MNBT_THEME_ROOT . $fileKey)) {
		$name = trim((string)@file_get_contents(MNBT_THEME_ROOT . $fileKey));
	}
	$name = mnbt_theme_sanitize($name);
	if ($name === '' || !is_dir(MNBT_THEME_ROOT . $name . '/' . $scope)) {
		return MNBT_THEME_DEFAULT;
	}
	return $name;
}

/**
 * 解析视图路径（带 default 回退）
 * @param string $view 如 login / head，或 user/login / admin/login / docker/login
 * @param string $scope user|admin|docker
 */
function mnbt_theme_resolve($view, $scope = 'user')
{
	$view = str_replace('\\', '/', (string)$view);
	$view = ltrim($view, '/');
	$scope = mnbt_theme_normalize_scope($scope);

	if (strpos($view, 'user/') === 0) {
		$scope = 'user';
		$view = substr($view, 5);
	} elseif (strpos($view, 'admin/') === 0) {
		$scope = 'admin';
		$view = substr($view, 6);
	} elseif (strpos($view, 'docker/') === 0) {
		$scope = 'docker';
		$view = substr($view, 7);
	}

	$view = preg_replace('/\.php$/i', '', $view);
	if ($view === '' || strpos($view, '..') !== false) {
		return null;
	}

	mnbt_theme_ensure_loaded($scope);
	$theme = mnbt_theme_name($scope);
	$candidates = [
		MNBT_THEME_ROOT . $theme . '/' . $scope . '/' . $view . '.php',
	];
	if ($theme !== MNBT_THEME_DEFAULT) {
		$candidates[] = MNBT_THEME_ROOT . MNBT_THEME_DEFAULT . '/' . $scope . '/' . $view . '.php';
	}
	foreach ($candidates as $path) {
		if (is_file($path)) {
			return $path;
		}
	}
	return null;
}

/**
 * 主题静态资源 URL（相对 user/ 或 admin/ 页面）
 * 查找顺序：当前主题 → default 主题（缺文件自动回退）
 * @param string $path 相对 scope 根目录，如 assets/login.css
 * @param string $scope user|admin
 */
function mnbt_theme_url($path = '', $scope = 'user')
{
	$scope = mnbt_theme_normalize_scope($scope);
	$path = ltrim(str_replace('\\', '/', (string)$path), '/');
	if ($path !== '' && (strpos($path, '..') !== false || $path[0] === '/')) {
		$path = '';
	}
	$theme = mnbt_theme_name($scope);
	if ($path === '') {
		return '../templates/' . $theme . '/' . $scope . '/';
	}
	$candidates = [$theme];
	if ($theme !== MNBT_THEME_DEFAULT) {
		$candidates[] = MNBT_THEME_DEFAULT;
	}
	foreach ($candidates as $t) {
		if (is_file(MNBT_THEME_ROOT . $t . '/' . $scope . '/' . $path)) {
			return '../templates/' . $t . '/' . $scope . '/' . $path;
		}
	}
	return '../templates/' . $theme . '/' . $scope . '/' . $path;
}

/**
 * 主题 assets/ 快捷 URL（自动加 assets/ 前缀，带 default 回退）
 * 例：mnbt_theme_asset('login.css') → .../user/assets/login.css
 */
function mnbt_theme_asset($path = '', $scope = 'user')
{
	$path = ltrim(str_replace('\\', '/', (string)$path), '/');
	if ($path === '') {
		return mnbt_theme_url('assets/', $scope);
	}
	if (strpos($path, 'assets/') !== 0) {
		$path = 'assets/' . $path;
	}
	return mnbt_theme_url($path, $scope);
}

/**
 * 公共静态资源（imsetes）URL — 全站共享，不随主题切换
 * Bootstrap / jQuery / CodeMirror / 上传 logo 等放这里
 */
function mnbt_asset_url($path = '')
{
	$path = ltrim(str_replace('\\', '/', (string)$path), '/');
	if ($path !== '' && strpos($path, '..') !== false) {
		$path = '';
	}
	return $path === '' ? '../imsetes/' : '../imsetes/' . $path;
}

/**
 * 注册主题菜单渲染器。
 *
 * 每个主题在初始化时调用此函数，告诉引擎如何把自己作用域下的插件菜单树
 * （由 mnbt_register_menu 注册）渲染成该主题侧边栏所需的 HTML。
 *
 * @param string   $scope    'user' 或 'admin'
 * @param callable $callback 签名: function(array $items): string
 *                           $items 是按 order 排好序的插件菜单树，每项含：
 *                           - title, icon, url, order, multitabs
 *                           - children: 子菜单数组（可选）
 * @return bool
 *
 * 示例（layui 主题）：
 *   mnbt_register_theme_menu_renderer('user', function ($items) {
 *       $html = '';
 *       foreach ($items as $it) {
 *           if (!empty($it['children'])) { ... 分组 ... }
 *           else { ... 叶子 ... }
 *       }
 *       return $html;
 *   });
 */
function mnbt_register_theme_menu_renderer($scope, $callback)
{
	if (!is_callable($callback)) {
		return false;
	}
	$scope = mnbt_theme_normalize_scope($scope);
	$GLOBALS['mnbt_theme_menu_renderers'][$scope] = $callback;
	return true;
}

/**
 * 确保当前主题的 theme.php 初始化文件已被加载。
 *
 * 引擎会在首次解析主题视图时自动调用本函数。主题开发者只需在
 * templates/{theme}/theme.php 中注册渲染器，无需手动在 index.php 中引入。
 */
function mnbt_theme_ensure_loaded($scope = 'user')
{
	static $loaded = [];
	$scope = mnbt_theme_normalize_scope($scope);
	if (!empty($loaded[$scope])) {
		return;
	}
	$loaded[$scope] = true;
	$theme = mnbt_theme_name($scope);
	$initFile = MNBT_THEME_ROOT . $theme . '/theme.php';
	if (is_file($initFile)) {
		include_once $initFile;
	}
}

/**
 * 内部辅助：按 priority 遍历 override filter，第一个返回非 null 的值即短路。
 *
 * 与 mnbt_apply_filters 不同，本函数不串联 filter 输出 —— 任何一个 filter 返回非 null
 * 即停止后续 filter 调用。这样插件可以用低 priority "抢注" 接管，避免被高 priority 覆盖。
 *
 * @param string $hook filter 名
 * @param array  $vars 传给回调的变量
 * @return mixed|null 第一个非 null 返回值；全部返回 null 时为 null
 */
function _mnbt_theme_first_override($hook, array $vars)
{
	if (empty($GLOBALS['mnbt_plugin_filters'][$hook])) {
		return null;
	}
	$buckets = $GLOBALS['mnbt_plugin_filters'][$hook];
	ksort($buckets, SORT_NUMERIC);
	foreach ($buckets as $list) {
		foreach ($list as $item) {
			$prev = $GLOBALS['mnbt_plugin_current'];
			$GLOBALS['mnbt_plugin_current'] = $item['plugin'];
			try {
				$result = call_user_func($item['cb'], $vars);
			} catch (Throwable $e) {
				error_log('[MNBT theme] override ' . $hook . ' @' . $item['plugin'] . ': ' . $e->getMessage());
				$result = null;
			}
			$GLOBALS['mnbt_plugin_current'] = $prev;
			if ($result !== null) {
				return $result;
			}
		}
	}
	return null;
}

/**
 * 引入主题局部模板
 *
 * 插件可通过 mnbt_register_partial_override() 注册 override（filter 名: include.{scope}.{view}）
 * 介入 partial 渲染流程。回调签名: function(array $vars): mixed
 *
 * 回调返回值的三种模式：
 *   - null                                    → 不接管，继续加载原 partial（默认行为）
 *   - string                                  → 完全接管，直接输出该字符串，跳过原 partial
 *   - ['before' => string, 'after' => string] → 包裹模式，在原 partial 输出前后插入内容
 *
 * @param string $view  视图名
 * @param array  $vars  传入模板的变量
 * @param string $scope 'user' 或 'admin'
 * @return bool 是否成功加载
 */
function mnbt_theme_include($view, array $vars = [], $scope = 'user')
{
	$filterName = 'include.' . $scope . '.' . $view;
	$override   = _mnbt_theme_first_override($filterName, $vars);

	// 模式 1：完全接管
	if (is_string($override)) {
		echo mnbt_csrf_inject_html($override);
		return true;
	}

	// 解析包裹模式
	$before = '';
	$after  = '';
	if (is_array($override)) {
		$before = (string)($override['before'] ?? '');
		$after  = (string)($override['after']  ?? '');
	}

	$path = mnbt_theme_resolve($view, $scope);
	if ($path === null) {
		if ($before !== '' || $after !== '') {
			echo $before . $after;
			return true;
		}
		trigger_error('Theme partial not found: ' . $scope . '/' . $view, E_USER_WARNING);
		return false;
	}

	// 模式 2：包裹模式 - 输出 before
	if ($before !== '') {
		echo $before;
	}

	// 加载原 partial
	extract($GLOBALS, EXTR_SKIP);
	if ($vars) {
		extract($vars, EXTR_OVERWRITE);
	}
	$bufferLevel = ob_get_level();
	ob_start('mnbt_csrf_inject_html');
	try {
		include $path;
	} finally {
		while (ob_get_level() > $bufferLevel) ob_end_flush();
	}

	// 模式 2：包裹模式 - 输出 after
	if ($after !== '') {
		echo $after;
	}
	return true;
}

/**
 * 渲染页面模板
 *
 * 插件可通过 mnbt_register_page_override() 注册 override（filter 名: render.{scope}.{view}）
 * 介入整页渲染流程。回调签名: function(array $vars): mixed
 *
 * 回调返回值的三种模式：
 *   - null                                    → 不接管，继续加载原主题文件（默认行为）
 *   - string                                  → 完全接管，直接输出该字符串，跳过原主题文件
 *   - ['before' => string, 'after' => string] → 包裹模式，在原主题文件输出前后插入内容
 *
 * 多个插件注册同一 view 的 override 时，按 priority 升序执行，第一个返回非 null 的值生效，
 * 后续 filter 不再调用（短路语义）。
 *
 * @param string $view  视图名
 * @param array  $vars  传入模板的变量
 * @param bool   $exit  渲染完成后是否 exit
 * @param string $scope 'user' 或 'admin'
 * @return bool
 */
function mnbt_render($view, array $vars = [], $exit = true, $scope = 'user')
{
	$filterName = 'render.' . $scope . '.' . $view;
	$override   = _mnbt_theme_first_override($filterName, $vars);

	// 模式 1：完全接管
	if (is_string($override)) {
		echo mnbt_csrf_inject_html($override);
		if ($exit) {
			exit;
		}
		return true;
	}

	// 解析包裹模式
	$before = '';
	$after  = '';
	if (is_array($override)) {
		$before = (string)($override['before'] ?? '');
		$after  = (string)($override['after']  ?? '');
	}

	$path = mnbt_theme_resolve($view, $scope);
	if ($path === null) {
		http_response_code(500);
		echo 'Theme view not found: ' . htmlspecialchars($scope . '/' . (string)$view);
		if ($exit) {
			exit;
		}
		return false;
	}

	// 模式 2：before
	if ($before !== '') {
		echo $before;
	}

	// 加载原主题文件
	extract($GLOBALS, EXTR_SKIP);
	if ($vars) {
		extract($vars, EXTR_OVERWRITE);
	}
	$bufferLevel = ob_get_level();
	ob_start('mnbt_csrf_inject_html');
	try {
		include $path;
	} finally {
		while (ob_get_level() > $bufferLevel) ob_end_flush();
	}

	// 模式 2：after
	if ($after !== '') {
		echo $after;
	}

	if ($exit) {
		exit;
	}
	return true;
}

/** 管理端快捷渲染 */
function mnbt_admin_render($view, array $vars = [], $exit = true)
{
	return mnbt_render($view, $vars, $exit, 'admin');
}

/** 管理端快捷 include */
function mnbt_admin_include($view, array $vars = [])
{
	return mnbt_theme_include($view, $vars, 'admin');
}

/** Docker 端快捷渲染 */
function mnbt_docker_render($view, array $vars = [], $exit = true)
{
	return mnbt_render($view, $vars, $exit, 'docker');
}

/** Docker 端快捷 include */
function mnbt_docker_include($view, array $vars = [])
{
	return mnbt_theme_include($view, $vars, 'docker');
}

function mnbt_user_require_login()
{
	global $islogins;
	if (!isset($islogins) || (int)$islogins !== 1) {
		exit("<script language='javascript'>window.location.href='./login.php';</script>");
	}
}

function mnbt_user_guest_only()
{
	global $islogins;
	if (isset($islogins) && (int)$islogins === 1) {
		exit("<script language='javascript'>window.location.href='./index.php';</script>");
	}
}

function mnbt_admin_require_login()
{
	global $islogin;
	if (!isset($islogin) || (int)$islogin !== 1) {
		exit("<script language='javascript'>window.location.href='./login.php';</script>");
	}
}

function mnbt_admin_guest_only()
{
	global $islogin;
	if (isset($islogin) && (int)$islogin === 1) {
		exit("<script language='javascript'>window.location.href='./index.php';</script>");
	}
}

/**
 * 列出主题
 * @param string|null $scope user|admin|docker|null(全部，只要有任一侧目录)
 */
function mnbt_theme_list($scope = null)
{
	$list = [];
	if (!is_dir(MNBT_THEME_ROOT)) {
		return $list;
	}
	$dirs = @scandir(MNBT_THEME_ROOT) ?: [];
	foreach ($dirs as $dir) {
		if ($dir === '.' || $dir === '..') {
			continue;
		}
		$base = MNBT_THEME_ROOT . $dir;
		if (!is_dir($base)) {
			continue;
		}
		$hasUser = is_dir($base . '/user');
		$hasAdmin = is_dir($base . '/admin');
		$hasDocker = is_dir($base . '/docker');
		$hasHome = is_dir($base . '/home');
		if ($scope === 'user' && !$hasUser) {
			continue;
		}
		if ($scope === 'admin' && !$hasAdmin) {
			continue;
		}
		if ($scope === 'docker' && !$hasDocker) {
			continue;
		}
		if ($scope === 'home' && !$hasHome) {
			continue;
		}
		if ($scope === null && !$hasUser && !$hasAdmin && !$hasDocker && !$hasHome) {
			continue;
		}
		$meta = [
			'name' => $dir,
			'title' => $dir,
			'version' => '',
			'description' => '',
			'has_user' => $hasUser,
			'has_admin' => $hasAdmin,
			'has_docker' => $hasDocker,
			'has_home' => $hasHome,
		];
		$json = $base . '/theme.json';
		if (is_file($json)) {
			$data = json_decode((string)@file_get_contents($json), true);
			if (is_array($data)) {
				$meta['title'] = $data['title'] ?? $meta['title'];
				$meta['version'] = $data['version'] ?? '';
				$meta['description'] = $data['description'] ?? '';
			}
		}
		$list[$dir] = $meta;
	}
	return $list;
}

/**
 * 设置当前主题（写文件；若表字段存在则同步数据库）
 */
function mnbt_theme_set_active($scope, $name)
{
	global $DB, $conf, $siteid;
	$scope = mnbt_theme_normalize_scope($scope);
	$name = mnbt_theme_sanitize($name);
	if ($name === '' || !is_dir(MNBT_THEME_ROOT . $name . '/' . $scope)) {
		return [false, '主题不存在或不支持该端：' . $name];
	}

	$fileKeyMap = ['user' => 'active_user_theme', 'admin' => 'active_admin_theme', 'docker' => 'active_docker_theme', 'home' => 'active_home_theme'];
	$confKeyMap = ['user' => 'usertheme', 'admin' => 'admintheme', 'docker' => 'docker_theme', 'home' => 'home_theme'];
	$file = MNBT_THEME_ROOT . $fileKeyMap[$scope];
	if (@file_put_contents($file, $name) === false) {
		return [false, '无法写入主题配置文件，请检查 templates 目录写权限'];
	}

	$confKey = $confKeyMap[$scope];
	if (is_array($conf)) {
		$conf[$confKey] = $name;
	}

	// 可选：同步到 MN_config（字段不存在时忽略）
	if (isset($DB) && is_object($DB)) {
		$col = $confKey;
		$sid = isset($siteid) ? $siteid : 1;
		@$DB->query_prepare("UPDATE `MN_config` SET `{$col}` = ? WHERE `id` = ?", [$name, $sid]);
	}

	return [true, '设置成功'];
}
