<?php
/**
 * user_info 插件 - 主入口
 *
 * 功能：注册、登录、个人信息、修改密码
 * 架构：独立用户表 MN_plugin_user，独立认证 cookie account_token
 *       通过 P2 通用路由注册 /account/* 路径，不依赖核心 user/plugin.php
 */

if (!defined('IN_CRONLITE')) {
	exit;
}

require_once __DIR__ . '/lib/auth.php';

mnbt_plugin_register('user_info', [
	'name' => '用户信息',
	'description' => '独立用户系统：注册、登录、个人信息、修改密码',
]);

/* ============================================================
 *  页面路由
 * ============================================================ */

// 登录页
mnbt_register_route('GET', '/account/login', function ($params, $ctx) {
	$user = user_info_auth_current();
	if ($user) {
		header('Location: ' . user_info_url('account/profile'));
		exit;
	}
	user_info_render('login', ['page_title' => '登录']);
});

// 注册页
mnbt_register_route('GET', '/account/register', function ($params, $ctx) {
	$user = user_info_auth_current();
	if ($user) {
		header('Location: ' . user_info_url('account/profile'));
		exit;
	}
	user_info_render('register', ['page_title' => '注册']);
});

// 退出
mnbt_register_route('GET', '/account/logout', function ($params, $ctx) {
	user_info_auth_logout();
	header('Location: ' . user_info_url('account/login'));
	exit;
});

// 个人信息页（要求登录）
mnbt_register_route('GET', '/account/profile', function ($params, $ctx) {
	user_info_auth_require();
	user_info_render('profile', ['page_title' => '个人信息']);
});

// 修改密码页（要求登录）
mnbt_register_route('GET', '/account/password', function ($params, $ctx) {
	user_info_auth_require();
	user_info_render('password', ['page_title' => '修改密码']);
});

/* ============================================================
 *  API 路由
 * ============================================================ */

// 登录 API
mnbt_register_route('POST', '/account/api/login', function ($params, $ctx) {
	global $DB;
	$username = trim($_POST['username'] ?? '');
	$password = $_POST['password'] ?? '';

	if ($username === '' || $password === '') {
		user_info_json('用户名和密码不能为空');
	}
	if (preg_match('/["\'\/\\\\]/', $username)) {
		user_info_json('用户名包含非法字符');
	}

	$user = $DB->get_row_prepare("SELECT * FROM MN_plugin_user WHERE username=? LIMIT 1", [$username]);
	if (!$user) {
		user_info_json('用户不存在或密码错误');
	}
	if ((int)$user['status'] !== 1) {
		user_info_json('账号已被禁用');
	}
	if (!password_verify($password, $user['password_hash'])) {
		user_info_json('用户不存在或密码错误');
	}

	user_info_auth_login($user['id'], $user['password_hash']);
	user_info_json('登录成功', ['redirect' => user_info_url('account/profile')]);
});

// 注册 API
mnbt_register_route('POST', '/account/api/register', function ($params, $ctx) {
	global $DB, $date;
	$username = trim($_POST['username'] ?? '');
	$password = $_POST['password'] ?? '';
	$password2 = $_POST['password2'] ?? '';
	$email = trim($_POST['email'] ?? '');
	$qq = trim($_POST['qq'] ?? '');

	// 用户名：3~32 字符，字母数字下划线
	if (!preg_match('/^[a-zA-Z0-9_]{3,32}$/', $username)) {
		user_info_json('用户名为 3~32 位字母、数字或下划线');
	}
	// 密码：至少 6 字符
	if (strlen($password) < 6) {
		user_info_json('密码至少 6 个字符');
	}
	if ($password !== $password2) {
		user_info_json('两次输入的密码不一致');
	}
	// 邮箱（可选）
	if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
		user_info_json('邮箱格式不正确');
	}
	if ($email !== '' && strlen($email) > 128) {
		user_info_json('邮箱过长');
	}
	// QQ（可选）
	if ($qq !== '' && !preg_match('/^[0-9]{5,12}$/', $qq)) {
		user_info_json('QQ 号格式不正确');
	}

	// 检查用户名唯一
	$exists = $DB->get_row_prepare("SELECT id FROM MN_plugin_user WHERE username=? LIMIT 1", [$username]);
	if ($exists) {
		user_info_json('用户名已被占用');
	}

	$hash = password_hash($password, PASSWORD_BCRYPT);
	$now = $date ?: date('Y-m-d H:i:s');
	$ok = $DB->query_prepare(
		"INSERT INTO MN_plugin_user (username, password_hash, email, qq, status, created_at, updated_at) VALUES (?,?,?,?,1,?,?)",
		[$username, $hash, $email, $qq, $now, $now]
	);
	if (!$ok) {
		user_info_json('注册失败，请稍后重试');
	}

	// 取自增 ID（兼容 MySQLi / SQLite）
	$new_row = $DB->get_row_prepare("SELECT id FROM MN_plugin_user WHERE username=? LIMIT 1", [$username]);
	$new_id = $new_row ? (int)$new_row['id'] : 0;
	user_info_auth_login($new_id, $hash);
	user_info_json('注册成功', ['redirect' => user_info_url('account/profile')]);
});

// 更新个人信息 API
mnbt_register_route('POST', '/account/api/update_profile', function ($params, $ctx) {
	global $DB, $date;
	$user = user_info_auth_require();
	$email = trim($_POST['email'] ?? '');
	$qq = trim($_POST['qq'] ?? '');

	if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
		user_info_json('邮箱格式不正确');
	}
	if ($email !== '' && strlen($email) > 128) {
		user_info_json('邮箱过长');
	}
	if ($qq !== '' && !preg_match('/^[0-9]{5,12}$/', $qq)) {
		user_info_json('QQ 号格式不正确');
	}

	$now = $date ?: date('Y-m-d H:i:s');
	$ok = $DB->query_prepare(
		"UPDATE MN_plugin_user SET email=?, qq=?, updated_at=? WHERE id=?",
		[$email, $qq, $now, $user['id']]
	);
	if (!$ok) {
		user_info_json('保存失败');
	}
	user_info_json('保存成功');
});

// 修改密码 API
mnbt_register_route('POST', '/account/api/change_password', function ($params, $ctx) {
	global $DB, $date;
	$user = user_info_auth_require();
	$old_pass = $_POST['old_password'] ?? '';
	$new_pass = $_POST['new_password'] ?? '';
	$new_pass2 = $_POST['new_password2'] ?? '';

	if (!password_verify($old_pass, $user['password_hash'])) {
		user_info_json('原密码错误');
	}
	if (strlen($new_pass) < 6) {
		user_info_json('新密码至少 6 个字符');
	}
	if ($new_pass !== $new_pass2) {
		user_info_json('两次输入的新密码不一致');
	}
	if ($new_pass === $old_pass) {
		user_info_json('新密码不能与原密码相同');
	}

	$hash = password_hash($new_pass, PASSWORD_BCRYPT);
	$now = $date ?: date('Y-m-d H:i:s');
	$ok = $DB->query_prepare(
		"UPDATE MN_plugin_user SET password_hash=?, updated_at=? WHERE id=?",
		[$hash, $now, $user['id']]
	);
	if (!$ok) {
		user_info_json('修改失败');
	}

	// 刷新 cookie（session_hash 依赖 password_hash，需重新签发）
	user_info_auth_login($user['id'], $hash);
	user_info_json('修改成功');
});

/* ============================================================
 *  管理员端页面注册
 * ============================================================ */

mnbt_register_page('admin', 'users', 'views/admin/users.php', '用户管理');
mnbt_register_page('admin', 'user_edit', 'views/admin/user_edit.php', '用户编辑');

mnbt_register_menu('admin', [
	'title' => '用户管理 - 用户列表',
	'page'  => 'users',
	'icon'  => 'mdi-account-multiple',
	'order' => 70,
	'multitabs' => true,
]);

// 管理员端 AJAX：重置用户密码
mnbt_register_ajax('admin', 'user_info_admin_reset_password', function () {
	mnbt_plugin_require_admin();
	global $DB, $date;
	$user_id = (int)($_POST['user_id'] ?? 0);
	$new_pass = trim((string)($_POST['new_password'] ?? ''));
	if ($user_id <= 0) {
		json_exit('参数错误');
	}
	if (strlen($new_pass) < 6) {
		json_exit('新密码至少 6 个字符');
	}
	$row = $DB->get_row_prepare("SELECT id FROM MN_plugin_user WHERE id=? LIMIT 1", [$user_id]);
	if (!$row) {
		json_exit('用户不存在');
	}
	$hash = password_hash($new_pass, PASSWORD_BCRYPT);
	$now = $date ?: date('Y-m-d H:i:s');
	$ok = $DB->query_prepare(
		"UPDATE MN_plugin_user SET password_hash=?, updated_at=? WHERE id=?",
		[$hash, $now, $user_id]
	);
	if (!$ok) {
		json_exit('重置失败');
	}
	json_exit('重置成功');
});
