<?php
if (!defined('IN_CRONLITE')) exit();

/**
 * MNBT Docker 用户独立认证
 *
 * 独立于 member.php（admin_token / user_token），使用独立 cookie：docker_token。
 * - 加密：authcode($user_id . "\t" . $session_hash, 'ENCODE', SYS_KEY)
 * - session_hash = md5($user_id . $password_hash . SYS_KEY)（改密后旧 cookie 自动失效）
 * - 密码：password_hash / password_verify（bcrypt）
 *
 * 不修改 MPHX/member.php，由 docker/ 控制器自行引入本文件。
 */

define('MNBT_DOCKER_COOKIE', 'docker_token');
define('MNBT_DOCKER_COOKIE_TTL', 7 * 86400); // Docker 控制台 cookie 有效期 7 天

/**
 * 密码哈希（bcrypt）
 */
function docker_auth_password_hash($plain)
{
	return password_hash((string)$plain, PASSWORD_BCRYPT);
}

/**
 * 校验密码
 */
function docker_auth_password_verify($plain, $hash)
{
	return password_verify((string)$plain, (string)$hash);
}

/**
 * 计算 session_hash
 */
function docker_auth_session_hash($user_id, $password_hash)
{
	return md5((string)$user_id . (string)$password_hash . SYS_KEY);
}

/**
 * 写入登录 cookie
 * @param int    $user_id       MN_docker_user.id
 * @param string $password_hash MN_docker_user.password_hash（bcrypt）
 */
function docker_auth_login($user_id, $password_hash)
{
	$session_hash = docker_auth_session_hash($user_id, $password_hash);
	$payload = $user_id . "\t" . $session_hash;
	$cookie_value = authcode($payload, 'ENCODE', SYS_KEY);
	mnbt_set_auth_cookie(MNBT_DOCKER_COOKIE, $cookie_value, time() + MNBT_DOCKER_COOKIE_TTL);
}

/**
 * 登出（清除 docker_token）
 */
function docker_auth_logout()
{
	mnbt_set_auth_cookie(MNBT_DOCKER_COOKIE, '', time() - 604800);
}

/**
 * 获取当前登录的 Docker 用户（含状态/到期校验）
 * @return array|null MN_docker_user 行；未登录或异常返回 null
 */
function docker_auth_current()
{
	global $DB;
	if (empty($_COOKIE[MNBT_DOCKER_COOKIE])) {
		return null;
	}
	$token = authcode(daddslashes($_COOKIE[MNBT_DOCKER_COOKIE]), 'DECODE', SYS_KEY);
	if ($token === false || $token === '') {
		return null;
	}
	$parts = explode("\t", $token);
	if (count($parts) !== 2) {
		return null;
	}
	$user_id = (int)$parts[0];
	$sid = $parts[1];
	if ($user_id <= 0) {
		return null;
	}
	$row = $DB->get_row_prepare("SELECT * FROM MN_docker_user WHERE id=? limit 1", [$user_id]);
	if (!$row) {
		return null;
	}
	if (!hash_equals(docker_auth_session_hash($user_id, $row['password_hash']), $sid)) {
		return null;
	}
	return $row;
}

/**
 * 要求已登录，否则跳转登录页
 * @return array 当前 Docker 用户行
 */
function docker_auth_require()
{
	$row = docker_auth_current();
	if (!$row) {
		docker_auth_logout();
		exit("<script language='javascript'>window.location.href='./login.php';</script>");
	}
	// 状态校验
	if ($row['qk'] === 'paused') {
		docker_auth_logout();
		sysmsg('该 Docker 账户已被暂停，请联系管理员');
	}
	if ($row['qk'] === 'expired' || $row['qk'] === 'pruned') {
		docker_auth_logout();
		sysmsg('该 Docker 账户已到期，请联系管理员续费');
	}
	// 到期校验（非永久账号）
	global $date;
	if ($row['datae'] !== '0000-00-00' && strtotime($date) - strtotime($row['datae']) > 0) {
		docker_auth_logout();
		sysmsg('您的 Docker 账户已到期，已经帮您自动退出登录！刷新即可重新登录');
	}
	return $row;
}

/**
 * 仅允许未登录访问（如登录页）
 */
function docker_auth_guest_only()
{
	$row = docker_auth_current();
	if ($row) {
		exit("<script language='javascript'>window.location.href='./console.php';</script>");
	}
}
