<?php
/**
 * user_info 插件 - 认证函数库
 *
 * 独立于核心 member.php 的用户认证：
 * - cookie 名：account_token（与核心 user_token 不冲突）
 * - 加密方式：authcode($user_id \t $session_hash, SYS_KEY)
 * - session_hash = md5($user_id . $password_hash . SYS_KEY)
 *   修改密码后 session_hash 变化，旧 cookie 自动失效
 * - 密码哈希：password_hash / password_verify（bcrypt）
 */

if (!defined('IN_CRONLITE')) {
	exit;
}

/**
 * 生成带站点 base path 前缀的 URL（用于页面链接）。
 * 子目录部署时自动补全前缀。
 */
function user_info_url($path = '')
{
	$scriptName = isset($_SERVER['SCRIPT_NAME']) ? str_replace('\\', '/', $_SERVER['SCRIPT_NAME']) : '';
	$basePath = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');
	if ($basePath === '.' || $basePath === '/') {
		$basePath = '';
	}
	// 使用查询参数路由（index.php?_r=/path），避免依赖 Web 服务器 rewrite
	$p = ltrim($path, '/');
	$qpos = strpos($p, '?');
	if ($qpos !== false) {
		$route = substr($p, 0, $qpos);
		$query = substr($p, $qpos + 1);
		return $basePath . '/index.php?_r=/' . $route . '&' . $query;
	}
	return $basePath . '/index.php?_r=/' . $p;
}

/**
 * 插件静态资源 URL。
 */
function user_info_asset_url($path = '')
{
	return mnbt_plugin_url('user_info', ltrim($path, '/'));
}

/**
 * 设置登录 cookie。
 */
function user_info_auth_login($user_id, $password_hash)
{
	$session_hash = md5($user_id . $password_hash . SYS_KEY);
	$token = authcode($user_id . "\t" . $session_hash, 'ENCODE', SYS_KEY);
	setcookie('account_token', $token, time() + 604800, '/'); // 7 天
}

/**
 * 清除登录 cookie。
 */
function user_info_auth_logout()
{
	setcookie('account_token', '', time() - 604800, '/');
}

/**
 * 获取当前登录用户（数组），未登录返回 null。
 */
function user_info_auth_current()
{
	global $DB;
	if (empty($_COOKIE['account_token'])) {
		return null;
	}
	$token = daddslashes($_COOKIE['account_token']);
	$decoded = authcode($token, 'DECODE', SYS_KEY);
	if ($decoded === '' || $decoded === false || $decoded === null) {
		return null;
	}
	$parts = explode("\t", $decoded);
	if (count($parts) !== 2) {
		return null;
	}
	$user_id = (int)$parts[0];
	$session_hash = $parts[1];
	if ($user_id <= 0 || $session_hash === '') {
		return null;
	}
	$user = $DB->get_row_prepare("SELECT * FROM MN_plugin_user WHERE id=? LIMIT 1", [$user_id]);
	if (!$user) {
		return null;
	}
	if ((int)$user['status'] !== 1) {
		return null;
	}
	$expected = md5($user['id'] . $user['password_hash'] . SYS_KEY);
	if ($session_hash !== $expected) {
		return null;
	}
	return $user;
}

/**
 * 要求登录，未登录跳转登录页。返回用户数组。
 */
function user_info_auth_require()
{
	$user = user_info_auth_current();
	if (!$user) {
		header('Location: ' . user_info_url('account/login'));
		exit;
	}
	return $user;
}

/**
 * 渲染视图文件（带布局）。
 *
 * @param string $view  views 目录下的文件名（不含 .php）
 * @param array  $vars  传给视图的变量
 */
function user_info_render($view, $vars = [])
{
	$vars['current_user'] = user_info_auth_current();
	$vars['asset_url'] = user_info_asset_url();
	$vars['url'] = 'user_info_url';
	extract($vars, EXTR_SKIP);
	$viewFile = mnbt_plugin_path('user_info') . 'views/' . $view . '.php';
	if (!is_file($viewFile)) {
		http_response_code(500);
		echo 'View not found: ' . htmlspecialchars($view);
		return;
	}
	include $viewFile;
}

/**
 * 输出 JSON 并退出。
 */
function user_info_json($code, $extra = [])
{
	@header('Content-Type: application/json; charset=UTF-8');
	$payload = ['code' => $code];
	if (is_array($extra)) {
		$payload = array_merge($payload, $extra);
	}
	echo json_encode($payload, JSON_UNESCAPED_UNICODE);
	exit;
}
