<?php
/**
 * realname 插件 - 核心函数库
 *
 * 功能：
 * - 认证记录 CRUD（plg_realname_auth）
 * - 身份证号 AES-256-CBC 加密存储 / 解密
 * - 照片上传落盘（runtime/realname/{user_id}/，随机文件名 + .htaccess 防直接访问）
 * - 自动审核（本地算法，无外部 API）
 * - 支付拦截 filter（pay.dispatch.before）
 * - 用户端视图渲染 / JSON 输出
 * - 管理端 AJAX handler
 */

if (!defined('IN_CRONLITE')) {
	exit;
}

require_once __DIR__ . '/idcard.php';

define('REALNAME_ROOT', dirname(__DIR__)); // .../app_plugins/realname

/* ============================================================
 *  基础工具
 * ============================================================ */

/** 生成带站点 base path 前缀的 URL（查询参数路由，无需 rewrite）。 */
function realname_url($path = '')
{
	$scriptName = isset($_SERVER['SCRIPT_NAME']) ? str_replace('\\', '/', $_SERVER['SCRIPT_NAME']) : '';
	$basePath = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');
	if ($basePath === '.' || $basePath === '/') {
		$basePath = '';
	}
	$p = ltrim($path, '/');
	$qpos = strpos($p, '?');
	if ($qpos !== false) {
		$route = substr($p, 0, $qpos);
		$query = substr($p, $qpos + 1);
		return $basePath . '/index.php?_r=/' . $route . '&' . $query;
	}
	return $basePath . '/index.php?_r=/' . $p;
}

/** 插件静态资源 URL。 */
function realname_asset_url($path = '')
{
	return mnbt_plugin_url('realname', 'assets/' . ltrim($path, '/'));
}

/** 输出 JSON 并退出。 */
function realname_json($code, $extra = [])
{
	@header('Content-Type: application/json; charset=UTF-8');
	$payload = ['code' => $code];
	if (is_array($extra)) {
		$payload = array_merge($payload, $extra);
	}
	echo json_encode($payload, JSON_UNESCAPED_UNICODE);
	exit;
}

/** 当前时间字符串（兼容核心 $date）。 */
function realname_now()
{
	global $date;
	return $date ?: date('Y-m-d H:i:s');
}

/** 插件配置读取。 */
function realname_opt($key, $default = null)
{
	return function_exists('mnbt_plugin_option_get') ? mnbt_plugin_option_get('realname', $key, $default) : $default;
}

/* ============================================================
 *  加密 / 解密（身份证号敏感信息）
 * ============================================================ */

function realname_aes_key()
{
	$key = mnbt_plugin_option_get('realname', 'aes_key', '');
	if ($key === '') {
		$key = bin2hex(random_bytes(32));
		mnbt_plugin_option_set('realname', 'aes_key', $key);
	}
	return $key;
}

/** AES-256-CBC 加密（返回 base64）。 */
function realname_encrypt($plain)
{
	if ($plain === '') {
		return '';
	}
	$key = substr(hash('sha256', realname_aes_key(), true), 0, 32);
	$iv  = random_bytes(16);
	$cipher = openssl_encrypt($plain, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);
	if ($cipher === false) {
		return '';
	}
	return base64_encode($iv . $cipher);
}

/** AES-256-CBC 解密（base64 → 明文），失败返回 ''。 */
function realname_decrypt($encoded)
{
	if ($encoded === '') {
		return '';
	}
	$raw = base64_decode((string)$encoded, true);
	if ($raw === false || strlen($raw) <= 16) {
		return '';
	}
	$iv = substr($raw, 0, 16);
	$cipher = substr($raw, 16);
	$key = substr(hash('sha256', realname_aes_key(), true), 0, 32);
	$plain = openssl_decrypt($cipher, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);
	return $plain === false ? '' : $plain;
}

/* ============================================================
 *  照片存储（runtime/realname/{user_id}/）
 * ============================================================ */

/** 项目根目录绝对路径。 */
function realname_root_dir()
{
	return dirname(dirname(REALNAME_ROOT));
}

/** 照片数据根目录（项目根/runtime/realname）。 */
function realname_data_root()
{
	$dir = realname_root_dir() . '/runtime/realname';
	if (!is_dir($dir)) {
		@mkdir($dir, 0755, true);
	}
	// 禁止 Web 直接访问
	$ht = $dir . '/.htaccess';
	if (!is_file($ht)) {
		@file_put_contents($ht, "Deny from all\n");
	}
	return $dir;
}

/** 某用户的照片目录，不存在则创建。 */
function realname_user_data_dir($user_id)
{
	$dir = realname_data_root() . '/' . (int)$user_id;
	if (!is_dir($dir)) {
		@mkdir($dir, 0755, true);
	}
	return $dir;
}

/**
 * 处理单张上传照片，返回随机文件名（失败返回 ''）。
 * 校验：真实图片（getimagesize）、jpg/png、≤8MB。
 */
function realname_save_photo($user_id, $file_key)
{
	$file = $_FILES[$file_key] ?? null;
	if (!$file || !is_array($file) || empty($file['tmp_name'])) {
		return '';
	}
	if ((int)$file['error'] !== UPLOAD_ERR_OK) {
		return '';
	}
	if ((int)$file['size'] > 8 * 1024 * 1024) {
		return '';
	}
	$info = @getimagesize($file['tmp_name']);
	if ($info === false) {
		return '';
	}
	if (!in_array($info[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG], true)) {
		return '';
	}
	$ext = $info[2] === IMAGETYPE_PNG ? 'png' : 'jpg';
	$name = bin2hex(random_bytes(16)) . '.' . $ext;
	$dir = realname_user_data_dir($user_id);
	if (!move_uploaded_file($file['tmp_name'], $dir . '/' . $name)) {
		return '';
	}
	return $name;
}

/**
 * 处理 base64 编码的照片（前端 canvas 压缩后传来），解码后落盘。
 * @param int    $user_id
 * @param string $prefix  文件名前缀（front/back/hand）
 * @param string $b64     完整的 data URL（data:image/jpeg;base64,...）
 * @return string  随机文件名，失败返回 ''
 */
function realname_save_photo_base64($user_id, $prefix, $b64)
{
	if ($b64 === '' || strlen($b64) > 2 * 1024 * 1024) {
		return ''; // base64 字符串超过 2MB 说明原图太大
	}
	// 解析 data:image/xxx;base64,...
	if (!preg_match('#^data:image/(jpe?g|png);base64,(.+)$#i', $b64, $m)) {
		return '';
	}
	$mimeType = strtolower($m[1]) === 'png' ? 'image/png' : 'image/jpeg';
	$raw = base64_decode($m[2], true);
	if ($raw === false || strlen($raw) > 8 * 1024 * 1024) {
		return '';
	}
	// 验证解码后是有效图片
	$tmp = tempnam(sys_get_temp_dir(), 'rn');
	if ($tmp === false || file_put_contents($tmp, $raw) === false) {
		return '';
	}
	$info = @getimagesize($tmp);
	if ($info === false) {
		@unlink($tmp);
		return '';
	}
	if (!in_array($info[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG], true)) {
		@unlink($tmp);
		return '';
	}
	$ext = $info[2] === IMAGETYPE_PNG ? 'png' : 'jpg';
	$name = $prefix . '_' . bin2hex(random_bytes(12)) . '.' . $ext;
	$dir = realname_user_data_dir($user_id);
	if (!rename($tmp, $dir . '/' . $name)) {
		@unlink($tmp);
		return '';
	}
	return $name;
}

/** 根据相对文件名返回照片绝对路径。 */
function realname_photo_path($user_id, $filename)
{
	if ($filename === '') {
		return '';
	}
	$filename = basename((string)$filename); // 防路径穿越
	return realname_user_data_dir($user_id) . '/' . $filename;
}

/* ============================================================
 *  认证记录 CRUD
 * ============================================================ */

function realname_get_by_user($user_id)
{
	global $DB;
	$row = $DB->get_row_prepare("SELECT * FROM plg_realname_auth WHERE user_id=? LIMIT 1", [(int)$user_id]);
	return $row ?: null;
}

function realname_get_by_id($id)
{
	global $DB;
	$row = $DB->get_row_prepare("SELECT * FROM plg_realname_auth WHERE id=? LIMIT 1", [(int)$id]);
	return $row ?: null;
}

/**
 * 保存（插入或覆盖）认证记录。
 * @param array $user  当前插件用户数组
 * @param array $data  real_name/phone/id_card/front_img/back_img/hand_img/ocr_name/ocr_id_card
 * @return array ['ok'=>bool, 'id'=>int, 'status'=>string, 'note'=>string]
 */
function realname_save_auth($user, $data)
{
	global $DB;
	$user_id = (int)$user['id'];
	$now = realname_now();

	// 确保表存在
	static $tableReady = false;
	if (!$tableReady) {
		$tableReady = true;
		$ok = $DB->query(
			"CREATE TABLE IF NOT EXISTS `plg_realname_auth` ("
			. "`id` int(11) NOT NULL AUTO_INCREMENT,"
			. "`user_id` int(11) NOT NULL,"
			. "`username` varchar(64) NOT NULL DEFAULT '',"
			. "`real_name` varchar(64) NOT NULL DEFAULT '',"
			. "`phone` varchar(20) NOT NULL DEFAULT '',"
			. "`id_card` varchar(255) NOT NULL DEFAULT '',"
			. "`front_img` varchar(255) NOT NULL DEFAULT '',"
			. "`back_img` varchar(255) NOT NULL DEFAULT '',"
			. "`hand_img` varchar(255) NOT NULL DEFAULT '',"
			. "`ocr_name` varchar(64) NOT NULL DEFAULT '',"
			. "`ocr_id_card` varchar(255) NOT NULL DEFAULT '',"
			. "`status` varchar(16) NOT NULL DEFAULT 'pending',"
			. "`audit_note` varchar(255) NOT NULL DEFAULT '',"
			. "`created_at` varchar(50) NOT NULL DEFAULT '',"
			. "`updated_at` varchar(50) NOT NULL DEFAULT '',"
			. "`audited_at` varchar(50) NOT NULL DEFAULT '',"
			. "PRIMARY KEY (`id`),"
			. "UNIQUE KEY `uk_user` (`user_id`),"
			. "KEY `idx_status` (`status`)"
			. ") ENGINE=MyISAM DEFAULT CHARSET=utf8"
		);
		if (!$ok) {
			$msg = method_exists($DB, 'error') ? $DB->error() : 'unknown';
			return ['ok' => false, 'id' => 0, 'status' => 'error', 'note' => '建表失败: ' . $msg];
		}
	}

	$existing = realname_get_by_user($user_id);
	$id_card_enc = realname_encrypt($data['id_card']);
	$ocr_id_enc  = realname_encrypt($data['ocr_id_card']);

	// 自动审核
	$audit = realname_auto_audit($data);
	$status = $audit['status'];
	$note = $audit['note'];

	$e = function ($v) use ($DB) { return "'" . $DB->escape((string)$v) . "'"; };
	if ($existing) {
		$audited_at = ($status !== 'pending' ? $now : $existing['audited_at']);
		$sql = "UPDATE plg_realname_auth SET "
			. "real_name=" . $e($data['real_name'])
			. ", phone=" . $e($data['phone'])
			. ", id_card=" . $e($id_card_enc)
			. ", front_img=" . $e($data['front_img'])
			. ", back_img=" . $e($data['back_img'])
			. ", hand_img=" . $e($data['hand_img'])
			. ", ocr_name=" . $e($data['ocr_name'])
			. ", ocr_id_card=" . $e($ocr_id_enc)
			. ", status=" . $e($status)
			. ", audit_note=" . $e($note)
			. ", updated_at=" . $e($now)
			. ", audited_at=" . $e($audited_at)
			. " WHERE user_id=" . (int)$user_id;
		$ok = (bool)$DB->query($sql);
		$id = (int)$existing['id'];
	} else {
		$audited_at = ($status !== 'pending' ? $now : '');
		$sql = "INSERT INTO plg_realname_auth "
			. "(user_id, username, real_name, phone, id_card, front_img, back_img, hand_img, ocr_name, ocr_id_card, status, audit_note, created_at, updated_at, audited_at) VALUES ("
			. (int)$user_id . ", " . $e($user['username']) . ", " . $e($data['real_name']) . ", " . $e($data['phone']) . ", "
			. $e($id_card_enc) . ", " . $e($data['front_img']) . ", " . $e($data['back_img']) . ", " . $e($data['hand_img']) . ", "
			. $e($data['ocr_name']) . ", " . $e($ocr_id_enc) . ", " . $e($status) . ", " . $e($note) . ", "
			. $e($now) . ", " . $e($now) . ", " . $e($audited_at) . ")";
		$ok = (bool)$DB->query($sql);
		if ($ok) {
			$row = $DB->get_row("SELECT id FROM plg_realname_auth WHERE user_id=" . (int)$user_id . " LIMIT 1");
			$id = $row ? (int)$row['id'] : 0;
		} else {
			$id = 0;
		}
	}

	if (!$ok) {
		$err = method_exists($DB, 'error') ? $DB->error() : 'unknown';
		$short = strlen($sql) > 300 ? (substr($sql, 0, 300) . '...') : $sql;
		return ['ok' => false, 'id' => 0, 'status' => 'error', 'note' => '写入失败: ' . $err . ' | SQL前段: ' . $short];
	}
	return ['ok' => true, 'id' => $id, 'status' => $status, 'note' => $note];
}

/* ============================================================
 *  自动审核（本地算法）
 * ============================================================ */

/**
 * 自动审核：格式合法性 + 交叉一致性（本地三要素验证）。
 * @param array $data real_name/phone/id_card/ocr_name/ocr_id_card
 * @return array ['status'=>'approved'|'rejected'|'pending', 'note'=>string]
 */
function realname_auto_audit($data)
{
	$real_name   = trim((string)($data['real_name'] ?? ''));
	$phone       = trim((string)($data['phone'] ?? ''));
	$id_card     = strtoupper(trim((string)($data['id_card'] ?? '')));
	$ocr_name    = trim((string)($data['ocr_name'] ?? ''));
	$ocr_id_card = strtoupper(preg_replace('/\s+/', '', (string)($data['ocr_id_card'] ?? '')));

	$min_age = (int)realname_opt('min_age', 18);
	$allow_name_diff = (bool)realname_opt('allow_ocr_name_diff', false);

	// 1. 姓名
	$nameChk = realname_name_validate($real_name);
	if (!$nameChk['ok']) {
		return ['status' => 'rejected', 'note' => $nameChk['msg']];
	}
	// 2. 手机号
	$phoneChk = realname_phone_validate($phone);
	if (!$phoneChk['ok']) {
		return ['status' => 'rejected', 'note' => $phoneChk['msg']];
	}
	// 3. 身份证
	$idChk = realname_idcard_validate($id_card);
	if (!$idChk['ok']) {
		return ['status' => 'rejected', 'note' => $idChk['msg']];
	}
	// 4. 年龄
	if ($min_age > 0 && $idChk['age'] < $min_age) {
		return ['status' => 'rejected', 'note' => '未满 ' . $min_age .  ' 周岁，暂不支持认证'];
	}
	// 5. OCR 一致性：身份证号
	if ($ocr_id_card !== '' && $ocr_id_card !== $id_card) {
		return ['status' => 'pending', 'note' => 'OCR 身份证号与填写不一致，已转人工复核'];
	}
	// 6. OCR 一致性：姓名
	if ($allow_name_diff) {
		if ($ocr_id_card === '') {
			return ['status' => 'pending', 'note' => 'OCR 身份证号识别失败，转人工复核'];
		}
		return ['status' => 'approved', 'note' => '自动审核通过'];
	}
	if ($ocr_name === '') {
		return ['status' => 'pending', 'note' => 'OCR 姓名识别失败，转人工复核'];
	}
	if ($ocr_name !== $real_name) {
		return ['status' => 'pending', 'note' => 'OCR 姓名与填写不一致，已转人工复核'];
	}
	if ($ocr_id_card === '') {
		return ['status' => 'pending', 'note' => 'OCR 身份证号识别失败，转人工复核'];
	}
	return ['status' => 'approved', 'note' => '自动审核通过'];
}

/* ============================================================
 *  支付拦截（pay.dispatch.before filter）
 * ============================================================ */

/**
 * filter 回调：未实名的插件用户禁止发起支付。
 * @return string|null 返回 HTML 提示则拦截并终止；null 放行
 */
function realname_pay_guard($value, $type, $order_context)
{
	if (!function_exists('user_info_auth_current')) {
		return null;
	}
	$user = user_info_auth_current();
	if (!$user) {
		return null; // 非插件用户（核心用户）→ 放行
	}
	$auth = realname_get_by_user((int)$user['id']);
	if ($auth && $auth['status'] === 'approved') {
		return null; // 已实名 → 放行
	}

	$isTdesign = function_exists('mnbt_theme_name') && mnbt_theme_name('user') === 'tdesign';
	if ($isTdesign && function_exists('user_info_url')) {
		$applyUrl = user_info_url('account') . '#/realname';
		$statusUrl = $applyUrl;
	} else {
		$applyUrl = realname_url('realname/apply');
		$statusUrl = realname_url('realname/status');
	}
	$tip = '尚未完成实名认证';
	if ($auth) {
		if ($auth['status'] === 'pending') {
			$tip = '实名认证正在审核中';
		} elseif ($auth['status'] === 'rejected') {
			$tip = '实名认证未通过，请重新提交';
		}
	}
	return '<!DOCTYPE html><html lang="zh-CN"><head><meta charset="UTF-8">'
		. '<meta name="viewport" content="width=device-width, initial-scale=1.0">'
		. '<title>实名认证</title><style>'
		. 'body{font-family:-apple-system,"PingFang SC","Microsoft YaHei",sans-serif;background:#f4f6fa;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0;}'
		. '.rn-card{background:#fff;border-radius:16px;box-shadow:0 8px 30px rgba(0,0,0,.08);padding:44px 52px;max-width:420px;width:88%;text-align:center;}'
		. '.rn-ico{width:64px;height:64px;border-radius:50%;background:#fef0ef;color:#e34d59;display:flex;align-items:center;justify-content:center;font-size:32px;margin:0 auto 20px;}'
		. 'h2{font-size:20px;color:#1c2438;margin:0 0 10px;}'
		. 'p{font-size:14px;color:#6b7a99;line-height:1.7;margin:0 0 26px;}'
		. 'a.rn-btn{display:inline-block;background:#0052d9;color:#fff;text-decoration:none;padding:10px 30px;border-radius:8px;font-size:14px;transition:opacity .2s;}'
		. 'a.rn-btn:hover{opacity:.85;}'
		. '</style></head><body>'
		. '<div class="rn-card"><div class="rn-ico">&#128100;</div>'
		. '<h2>' . htmlspecialchars($tip) . '</h2>'
		. '<p>根据平台要求，购买产品前需先完成实名认证（姓名 / 手机号 / 身份证号 + 证件照片，全程本地识别）。</p>'
		. '<a class="rn-btn" href="' . htmlspecialchars($applyUrl) . '">前往实名认证</a>'
		. '&nbsp;&nbsp;<a class="rn-btn" style="background:#6b7a99" href="' . htmlspecialchars($statusUrl) . '">查看状态</a>'
		. '</div></body></html>';
}

/* ============================================================
 *  视图渲染
 * ============================================================ */

/** 渲染用户端视图（带插件自带布局，不依赖外部 CDN）。 */
function realname_render($view, $vars = [])
{
	$vars['current_user'] = $vars['current_user'] ?? (function_exists('user_info_auth_current') ? user_info_auth_current() : null);
	$vars['asset_url'] = realname_asset_url();
	$vars['url'] = 'realname_url';
	extract($vars, EXTR_SKIP);

	$viewFile = REALNAME_ROOT . '/views/' . $view . '.php';
	if (!is_file($viewFile)) {
		http_response_code(500);
		echo 'View not found: ' . htmlspecialchars($view);
		return;
	}
	include $viewFile;
}

/* ============================================================
 *  管理端 AJAX handler（由 bootstrap.php 注册）
 * ============================================================ */

/** 审核列表（分页 + 筛选）。 */
function realname_admin_list()
{
	global $DB;
	mnbt_plugin_require_admin();
	$page = max(1, (int)($_POST['page'] ?? 1));
	$per = min(50, max(10, (int)($_POST['per_page'] ?? 20)));
	$status = trim((string)($_POST['status'] ?? ''));
	$kw = trim((string)($_POST['keyword'] ?? ''));

	$where = 'WHERE 1';
	$params = [];
	if ($status !== '' && in_array($status, ['pending', 'approved', 'rejected'], true)) {
		$where .= ' AND status=?';
		$params[] = $status;
	}
	if ($kw !== '') {
		$where .= ' AND (username LIKE ? OR real_name LIKE ?)';
		$params[] = '%' . $kw . '%';
		$params[] = '%' . $kw . '%';
	}
	$total = (int)($DB->get_row_prepare("SELECT COUNT(*) c FROM plg_realname_auth {$where}", $params)['c'] ?? 0);
	$offset = ($page - 1) * $per;
	$rows = $DB->get_all_prepare("SELECT * FROM plg_realname_auth {$where} ORDER BY id DESC LIMIT {$offset},{$per}", $params) ?: [];

	$items = [];
	foreach ($rows as $r) {
		$idcard = realname_decrypt($r['id_card']);
		$items[] = [
			'id'         => (int)$r['id'],
			'user_id'    => (int)$r['user_id'],
			'username'   => (string)$r['username'],
			'real_name'  => realname_mask_name($r['real_name']),
			'phone'      => realname_mask_phone($r['phone']),
			'id_card'    => realname_mask_idcard($idcard),
			'status'     => (string)$r['status'],
			'audit_note' => (string)$r['audit_note'],
			'created_at' => (string)$r['created_at'],
			'audited_at' => (string)$r['audited_at'],
		];
	}
	realname_json('ok', ['items' => $items, 'total' => $total, 'page' => $page, 'pages' => max(1, (int)ceil($total / $per))]);
}

/** 审核通过。 */
function realname_admin_approve()
{
	global $DB;
	mnbt_plugin_require_admin();
	$id = (int)($_POST['id'] ?? 0);
	$note = trim((string)($_POST['note'] ?? ''));
	if ($id <= 0) {
		realname_json('参数错误');
	}
	$row = realname_get_by_id($id);
	if (!$row) {
		realname_json('记录不存在');
	}
	$now = realname_now();
	$final = $note === '' ? '管理员审核通过' : '管理员审核通过：' . $note;
	if (!$DB->query_prepare("UPDATE plg_realname_auth SET status='approved', audit_note=?, audited_at=? WHERE id=?", [$final, $now, $id])) {
		realname_json('操作失败');
	}
	realname_json('ok', ['message' => '已通过']);
}

/** 审核驳回。 */
function realname_admin_reject()
{
	global $DB;
	mnbt_plugin_require_admin();
	$id = (int)($_POST['id'] ?? 0);
	$note = trim((string)($_POST['note'] ?? ''));
	if ($id <= 0) {
		realname_json('参数错误');
	}
	if ($note === '') {
		realname_json('请填写驳回原因');
	}
	$row = realname_get_by_id($id);
	if (!$row) {
		realname_json('记录不存在');
	}
	$now = realname_now();
	if (!$DB->query_prepare("UPDATE plg_realname_auth SET status='rejected', audit_note=?, audited_at=? WHERE id=?", [$note, $now, $id])) {
		realname_json('操作失败');
	}
	realname_json('ok', ['message' => '已驳回']);
}

/** 解密查看身份证号（管理员二次密码验证）。 */
function realname_admin_decrypt()
{
	global $DB, $conf;
	mnbt_plugin_require_admin();
	$id = (int)($_POST['id'] ?? 0);
	$password = (string)($_POST['password'] ?? '');
	if ($id <= 0) {
		realname_json('参数错误');
	}
	if ($password === '' || $password !== ($conf['pwd'] ?? '')) {
		realname_json('管理密码错误');
	}
	$row = realname_get_by_id($id);
	if (!$row) {
		realname_json('记录不存在');
	}
	realname_json('ok', [
		'real_name' => (string)$row['real_name'],
		'phone'     => (string)$row['phone'],
		'id_card'   => realname_decrypt($row['id_card']),
		'ocr_name'  => (string)$row['ocr_name'],
		'ocr_id_card' => realname_decrypt($row['ocr_id_card']),
	]);
}
