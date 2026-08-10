<?php
/**
 * realname 插件 - 主入口
 *
 * 功能：三要素实名认证（姓名/手机号/身份证号 + 身份证正反面与手持照片），
 *       前端 tesseract.js 本地 OCR 识别身份证正面，服务端自动审核；
 *       未实名插件用户无法发起支付（pay.dispatch.before filter 统一拦截）。
 * 依赖：user_info 插件（MN_plugin_user 用户体系）
 */

if (!defined('IN_CRONLITE')) {
	exit;
}

require_once __DIR__ . '/lib/auth.php';

mnbt_plugin_register('realname', [
	'name' => '实名认证',
	'description' => '三要素实名认证 + 身份证本地 OCR + 自动审核，未实名无法发起支付',
]);

/* ============================================================
 *  用户端页面路由
 * ============================================================ */

$realname_user_auth = function () {
	return (bool)(function_exists('user_info_auth_current') ? user_info_auth_current() : null);
};

// 申请页（未认证/被驳回可提交；已通过跳转状态页）
mnbt_register_route('GET', '/realname/apply', function ($params, $ctx) {
	$user = function_exists('user_info_auth_current') ? user_info_auth_current() : null;
	if (!$user) {
		header('Location: ' . (function_exists('user_info_url') ? user_info_url('account/login') : realname_url('realname/apply')));
		exit;
	}
	$auth = realname_get_by_user((int)$user['id']);
	if ($auth && $auth['status'] === 'approved') {
		header('Location: ' . realname_url('realname/status'));
		exit;
	}
	realname_render('apply', [
		'page_title' => '实名认证',
		'auth' => $auth,
	]);
}, 10, $realname_user_auth);

// 状态页
mnbt_register_route('GET', '/realname/status', function ($params, $ctx) {
	$user = function_exists('user_info_auth_current') ? user_info_auth_current() : null;
	if (!$user) {
		header('Location: ' . (function_exists('user_info_url') ? user_info_url('account/login') : realname_url('realname/apply')));
		exit;
	}
	$auth = realname_get_by_user((int)$user['id']);
	realname_render('status', [
		'page_title' => '认证状态',
		'auth' => $auth,
	]);
}, 10, $realname_user_auth);

/* ============================================================
 *  用户端 API 路由
 * ============================================================ */

// 当前认证状态
mnbt_register_route('GET', '/realname/api/me', function ($params, $ctx) {
	$user = function_exists('user_info_auth_current') ? user_info_auth_current() : null;
	if (!$user) {
		realname_json('not_login', ['logged_in' => false]);
		return;
	}
	$auth = realname_get_by_user((int)$user['id']);
	$data = ['logged_in' => true];
	if ($auth) {
		$idcard = realname_decrypt($auth['id_card']);
		$data['auth'] = [
			'id'         => (int)$auth['id'],
			'status'     => (string)$auth['status'],
			'real_name'  => realname_mask_name($auth['real_name']),
			'phone'      => realname_mask_phone($auth['phone']),
			'id_card'    => realname_mask_idcard($idcard),
			'audit_note' => (string)$auth['audit_note'],
			'created_at' => (string)$auth['created_at'],
			'audited_at' => (string)$auth['audited_at'],
		];
	} else {
		$data['auth'] = null;
	}
	realname_json('ok', $data);
}, 10, $realname_user_auth);

// 提交认证（multipart：三要素 + 三张照片 + OCR 结果）
mnbt_register_route('POST', '/realname/api/submit', function ($params, $ctx) {
	$user = function_exists('user_info_auth_current') ? user_info_auth_current() : null;
	if (!$user) {
		realname_json('not_login', ['logged_in' => false]);
		return;
	}

	$real_name   = trim((string)($_POST['real_name'] ?? ''));
	$phone       = trim((string)($_POST['phone'] ?? ''));
	$id_card     = strtoupper(trim((string)($_POST['id_card'] ?? '')));
	$ocr_name    = trim((string)($_POST['ocr_name'] ?? ''));
	$ocr_id_card = strtoupper(preg_replace('/\s+/', '', (string)($_POST['ocr_id_card'] ?? '')));

	// 三要素服务端预校验
	$nameChk = realname_name_validate($real_name);
	if (!$nameChk['ok']) realname_json($nameChk['msg']);
	$phoneChk = realname_phone_validate($phone);
	if (!$phoneChk['ok']) realname_json($phoneChk['msg']);
	$idChk = realname_idcard_validate($id_card);
	if (!$idChk['ok']) realname_json($idChk['msg']);

	// 照片上传（base64 格式，前端 canvas 压缩后传来）
	$require_hand = (bool)realname_opt('require_hand_photo', true);
	$front = realname_save_photo_base64((int)$user['id'], 'front', $_POST['front_img'] ?? '');
	$back  = realname_save_photo_base64((int)$user['id'], 'back',  $_POST['back_img'] ?? '');
	$hand  = realname_save_photo_base64((int)$user['id'], 'hand',  $_POST['hand_img'] ?? '');
	if ($front === '') realname_json('身份证正面照片上传失败（仅支持 jpg/png，≤8MB）');
	if ($back === '') realname_json('身份证反面照片上传失败（仅支持 jpg/png，≤8MB）');
	if ($require_hand && $hand === '') realname_json('手持身份证照片上传失败（仅支持 jpg/png，≤8MB）');

	$result = realname_save_auth($user, [
		'real_name'   => $real_name,
		'phone'       => $phone,
		'id_card'     => $id_card,
		'front_img'   => $front,
		'back_img'    => $back,
		'hand_img'    => $hand,
		'ocr_name'    => $ocr_name,
		'ocr_id_card' => $ocr_id_card,
	]);
	if (!$result['ok']) {
		realname_json($result['note']);
	}

	$tips = [
		'approved' => '实名认证已通过',
		'rejected' => '认证未通过：' . $result['note'],
		'pending'  => '已提交，等待人工复核：' . $result['note'],
	];
	realname_json('ok', [
		'message' => $tips[$result['status']] ?? '已提交',
		'status'  => $result['status'],
		'note'    => $result['note'],
	]);
}, 10, $realname_user_auth);

// 照片访问（鉴权：本人或管理员）
mnbt_register_route('GET', '/realname/api/img', function ($params, $ctx) {
	$id = (int)($_GET['id'] ?? 0);
	$type = (string)($_GET['type'] ?? 'front');
	if ($id <= 0 || !in_array($type, ['front', 'back', 'hand'], true)) {
		realname_json('参数错误');
	}
	$auth = realname_get_by_id($id);
	if (!$auth) {
		realname_json('记录不存在');
	}
	// 权限：管理员（$islogin，由 member.php 解析 admin_token），或本人
	$isAdmin = isset($GLOBALS['islogin']) && (int)$GLOBALS['islogin'] === 1;
	if (!$isAdmin) {
		$user = function_exists('user_info_auth_current') ? user_info_auth_current() : null;
		if (!$user || (int)$user['id'] !== (int)$auth['user_id']) {
			realname_json('无权访问');
		}
	}
	$field = $type . '_img';
	$path = realname_photo_path((int)$auth['user_id'], $auth[$field]);
	if ($path === '' || !is_file($path)) {
		realname_json('照片不存在');
	}
	$mime = (strtolower(pathinfo($path, PATHINFO_EXTENSION)) === 'png') ? 'image/png' : 'image/jpeg';
	@header('Content-Type: ' . $mime);
	@header('X-Content-Type-Options: nosniff');
	@header('Cache-Control: private, max-age=300');
	readfile($path);
	exit;
});

// 支付拦截：未实名插件用户禁止发起支付
mnbt_add_filter('pay.dispatch.before', 'realname_pay_guard', 10);

/* ============================================================
 *  用户端菜单
 * ============================================================ */

mnbt_register_menu('user', [
	'title'    => '实名认证',
	'icon'     => 'mdi-account-check',
	'order'    => 90,
	'children' => [
		['title' => '实名认证', 'page' => 'apply', 'icon' => 'mdi-card-account-details', 'multitabs' => true],
		['title' => '认证状态', 'page' => 'status', 'icon' => 'mdi-shield-check', 'multitabs' => true],
	],
]);

/* ============================================================
 *  管理员端页面与菜单
 * ============================================================ */

mnbt_register_page('admin', 'audits', 'admin/audits.php', '实名认证审核');
mnbt_register_page('admin', 'audit_detail', 'admin/audit_detail.php', '实名认证详情');

mnbt_register_menu('admin', [
	'title'    => '实名认证',
	'icon'     => 'mdi-account-check',
	'order'    => 80,
	'children' => [
		['title' => '审核列表', 'page' => 'audits', 'icon' => 'mdi-format-list-bulleted', 'multitabs' => true],
	],
]);

// 管理员端 AJAX
mnbt_register_ajax('admin', 'realname_admin_list', 'realname_admin_list');
mnbt_register_ajax('admin', 'realname_admin_approve', 'realname_admin_approve');
mnbt_register_ajax('admin', 'realname_admin_reject', 'realname_admin_reject');
mnbt_register_ajax('admin', 'realname_admin_decrypt', 'realname_admin_decrypt');
