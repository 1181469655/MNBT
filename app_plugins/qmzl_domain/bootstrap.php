<?php
/**
 * qmzl_domain 插件入口
 *
 * 对接启明智联平台域名注册 API（https://cloud.qimingidc.cn/console/v1）：
 * - 前置依赖 user_info 插件，用户维度 = user_info 用户（MN_plugin_user）
 *
 * 两种运营模式（后台设置切换）：
 *  1. client 客户自注册：每个客户绑定自己的启明智联账号，查询/下单/支付均在客户账号内完成；
 *     用户端路径：/qmzl
 *  2. agent  代理商模式：管理员配置代理商账号，客户下单走 MNBT 内置支付系统
 *     （MN_dd + 支付插件），支付成功后插件自动用代理商账号余额代注册；
 *     用户端路径：/qmzl_domain
 */
if (!defined('IN_CRONLITE')) exit;

require_once __DIR__ . '/lib/api.php';
require_once __DIR__ . '/lib/helper.php';

// 表结构自动升级（兼容已安装站点）
qmzl_schema_upgrade();

mnbt_plugin_register('qmzl_domain', ['name' => '启明智联域名注册']);

/* ============================================================
 * 1. 后台菜单
 * ============================================================ */
mnbt_register_menu('admin', [
	'title'    => '启明智联域名',
	'icon'     => 'mdi-web',
	'order'    => 35,
	'children' => [
		[
			'title'     => '插件设置',
			'page'      => 'settings',
			'icon'      => 'mdi-web',
			'order'     => 10,
			'multitabs' => true,
		],
		[
			'title'     => '订单记录',
			'page'      => 'orders',
			'icon'      => 'mdi-receipt',
			'order'     => 20,
			'multitabs' => true,
		],
		[
			'title'     => '用户云账号',
			'page'      => 'accounts',
			'icon'      => 'mdi-account-key',
			'order'     => 30,
			'multitabs' => true,
		],
	],
]);

mnbt_register_page('admin', 'settings', 'admin/settings.php', '启明智联域名设置');
mnbt_register_page('admin', 'orders',   'admin/orders.php',   '域名订单记录');
mnbt_register_page('admin', 'accounts', 'admin/accounts.php', '用户云账号');

// 插件管理页快捷入口
mnbt_register_settings_tab([
	'title' => '启明智联域名',
	'page'  => 'settings',
	'order' => 30,
]);

/* ============================================================
 * 2. 后台 AJAX
 * ============================================================ */

/** 保存插件设置（开关 + 模式 + 后缀溢价） */
mnbt_register_ajax('admin', 'p_qmzl_setting_save', function () {
	mnbt_plugin_require_admin();
	$enabled = isset($_POST['enabled']) ? (bool)$_POST['enabled'] : true;
	$mode = $_POST['mode'] ?? 'client';
	$mode = ($mode === 'agent') ? 'agent' : 'client';
	qmzl_setting_set('enabled', $enabled ? 'true' : 'false');
	qmzl_setting_set('mode', $mode);

	// 后缀溢价表 {"suffix": "溢价金额"}
	$markup = [];
	$raw = (string)($_POST['markup'] ?? '');
	$arr = json_decode($raw, true);
	if (is_array($arr)) {
		foreach ($arr as $suffix => $val) {
			$suffix = trim((string)$suffix);
			$f = round((float)$val, 2);
			if ($suffix !== '' && $f > 0) {
				$markup[$suffix] = (string)$f;
			}
		}
	}
	qmzl_setting_set('agent_markup', json_encode($markup, JSON_UNESCAPED_SLASHES));
	json_exit_success('已保存');
});

/** 保存并验证代理商开放接口凭证（平台账号 + API 密钥） */
mnbt_register_ajax('admin', 'p_qmzl_agent_test', function () {
	mnbt_plugin_require_admin();
	$username = trim((string)($_POST['username'] ?? ''));
	$apiToken = trim((string)($_POST['api_token'] ?? ''));
	if ($username === '' || $apiToken === '') {
		json_exit_error('请填写平台账号和 API 密钥');
	}
	$login = qmzl_agent_save($username, $apiToken);
	if (!$login['ok']) {
		json_exit_error($login['msg']);
	}
	json_exit_success('验证成功，代理商凭证已保存', ['exp' => $login['data']['exp']]);
});

/** 后台：用户云账号列表 */
mnbt_register_ajax('admin', 'p_qmzl_admin_accounts', function () {
	mnbt_plugin_require_admin();
	$page    = max(1, (int)($_POST['page'] ?? 1));
	$limit   = min(1000, max(1, (int)($_POST['limit'] ?? 200)));
	$keyword = trim((string)($_POST['keyword'] ?? ''));
	$data = qmzl_account_list($page, $limit, $keyword);
	json_exit_success('ok', $data);
});

/** 后台：解绑某用户云账号 */
mnbt_register_ajax('admin', 'p_qmzl_admin_account_unbind', function () {
	mnbt_plugin_require_admin();
	$user_id = (int)($_POST['user_id'] ?? 0);
	if ($user_id <= 0) json_exit_error('参数错误');
	if (!qmzl_account_delete($user_id)) json_exit_error('解绑失败');
	json_exit_success('已解绑');
});

/** 后台：订单列表 */
mnbt_register_ajax('admin', 'p_qmzl_admin_orders', function () {
	mnbt_plugin_require_admin();
	$page    = max(1, (int)($_POST['page'] ?? 1));
	$limit   = min(200, max(1, (int)($_POST['limit'] ?? 20)));
	$status  = trim((string)($_POST['status'] ?? ''));
	$keyword = trim((string)($_POST['keyword'] ?? ''));
	$data = qmzl_order_admin_list($page, $limit, $status, $keyword);
	json_exit_success('ok', $data);
});

/** 后台：重试失败的代理商注册订单 */
mnbt_register_ajax('admin', 'p_qmzl_admin_order_retry', function () {
	mnbt_plugin_require_admin();
	$id = (int)($_POST['id'] ?? 0);
	$order = qmzl_order_get_by_id($id);
	if (!$order) json_exit_error('订单不存在');
	if (qmzl_mode() !== 'agent') json_exit_error('仅代理商模式支持重试注册');
	$r = qmzl_agent_register_domain($id);
	if (!$r['ok']) json_exit_error($r['msg']);
	json_exit_success($r['msg']);
});

/* ============================================================
 * 3. 钩子：代理商模式支付成功后自动注册
 * ============================================================ */
mnbt_add_action('order.paid', function ($order_row, $ctx = []) {
	if (!is_array($order_row)) return;
	if (qmzl_mode() !== 'agent') return;
	if (($order_row['lx'] ?? '') !== 'qmzl_domain') return;
	$ddh = (string)($order_row['ddh'] ?? '');
	if ($ddh === '') return;
	$local = qmzl_order_get_by_ddh($ddh);
	if (!$local) return;
	if (($local['status'] ?? '') === 'Paid') return; // 已注册
	$r = qmzl_agent_register_domain((int)$local['id']);
	if (function_exists('mnbt_log')) {
		mnbt_log('系统', '插件-qmzl_domain', '代理商注册域名 ' . $local['domain'] . '：' . ($r['msg'] ?? ''), $r['ok'] ? '成功' : '失败', $GLOBALS['DB']);
	}
}, 10);

/* ============================================================
 * 4. 用户端（受后台「用户端功能开关」控制，依赖 user_info）
 * ============================================================ */
if (qmzl_setting_get('enabled', 'true') === 'true') {

$qzRoute = qmzl_route_prefix(); // client → qmzl / agent → qmzl_domain

/* ---------- 4.1 页面路由 ---------- */

// 域名注册（查询/价格/下单）
mnbt_register_route('GET', '/' . $qzRoute, function ($params, $ctx) {
	$user = qmzl_require_user();
	qmzl_render('index', ['current_user' => $user, 'mode' => qmzl_mode()]);
});

// 信息模板
mnbt_register_route('GET', '/' . $qzRoute . '/templates', function ($params, $ctx) {
	$user = qmzl_require_user();
	qmzl_render('templates', ['current_user' => $user, 'mode' => qmzl_mode()]);
});

// 我的域名
mnbt_register_route('GET', '/' . $qzRoute . '/domains', function ($params, $ctx) {
	$user = qmzl_require_user();
	qmzl_render('domains', ['current_user' => $user, 'mode' => qmzl_mode()]);
});

// 云账号绑定（仅 client 模式）
if (qmzl_mode() === 'client') {
	mnbt_register_route('GET', '/' . $qzRoute . '/account', function ($params, $ctx) {
		$user = qmzl_require_user();
		qmzl_render('account', ['current_user' => $user, 'mode' => 'client']);
	});

	// 支付页（?order=上游订单号，仅 client 模式）
	mnbt_register_route('GET', '/' . $qzRoute . '/pay', function ($params, $ctx) {
		$user = qmzl_require_user();
		$orderId = isset($_GET['order']) ? (int)$_GET['order'] : 0;
		$order = $orderId > 0 ? qmzl_order_get($orderId) : false;
		$valid = $order && (int)$order['user_id'] === (int)$user['id'];
		qmzl_render('pay', [
			'current_user' => $user,
			'order'        => $order ?: null,
			'order_id'     => $orderId,
			'order_valid'  => $valid,
			'mode'         => 'client',
		]);
	});
}

/* ---------- 4.2 用户端 API 路由 ---------- */

/** 当前绑定信息 / 模式 */
mnbt_register_route('POST', '/' . $qzRoute . '/api/account_info', function ($params, $ctx) {
	$user = qmzl_require_user(true);
	if (qmzl_mode() === 'agent') {
		json_exit_success('ok', ['bound' => true, 'mode' => 'agent']);
	}
	$row = qmzl_account_get((int)$user['id']);
	if (!$row) {
		json_exit_success('未绑定', ['bound' => false, 'mode' => 'client']);
	}
	$account = (string)$row['account'];
	$masked = strlen($account) > 4 ? mb_substr($account, 0, 2) . '***' . mb_substr($account, -2) : '***';
	json_exit_success('ok', [
		'bound'   => true,
		'mode'    => 'client',
		'account' => $account,
		'masked'  => $masked,
		'status'  => $row['status'],
		'last_msg' => (string)$row['last_msg'],
		'updated_at' => (string)$row['updated_at'],
	]);
});

/** 获取人机验证码图片（代理上游 QmCaptcha） */
mnbt_register_route('POST', '/' . $qzRoute . '/api/captcha_refresh', function ($params, $ctx) {
	qmzl_require_user(true);
	$res = qmzl_captcha_refresh();
	if (!$res['ok']) json_exit_error($res['msg'] ?: '获取验证码失败');
	json_exit_success('ok', $res['data']);
});

/** 校验人机验证码（代理上游） */
mnbt_register_route('POST', '/' . $qzRoute . '/api/captcha_verify', function ($params, $ctx) {
	qmzl_require_user(true);
	$captcha = trim((string)($_POST['captcha'] ?? ''));
	$token   = trim((string)($_POST['token'] ?? ''));
	if ($captcha === '' || $token === '') json_exit_error('参数错误');
	$res = qmzl_captcha_verify($captcha, $token);
	if (!$res['ok']) json_exit_error($res['msg'] ?: '验证失败');
	json_exit_success('验证通过');
});

/** 绑定/更新云账号并测试登录（仅 client 模式，需先过人机验证） */
mnbt_register_route('POST', '/' . $qzRoute . '/api/save_account', function ($params, $ctx) {
	$user = qmzl_require_user(true);
	$userId = (int)$user['id'];
	if (qmzl_mode() !== 'client') json_exit_error('代理商模式下无需绑定账号');
	$account  = trim((string)($_POST['account'] ?? ''));
	$password = (string)($_POST['password'] ?? '');
	$captcha  = trim((string)($_POST['captcha'] ?? ''));
	$captchaToken = trim((string)($_POST['captcha_token'] ?? ''));

	if ($account === '' || $password === '') {
		json_exit_error('账号和密码不能为空');
	}
	if (mb_strlen($account) > 200) {
		json_exit_error('账号过长');
	}
	if (strlen($password) > 100) {
		json_exit_error('密码过长');
	}
	if ($captcha === '' || $captchaToken === '') {
		json_exit_error('请先完成人机验证');
	}

	// 先登录测试（密码经 AES 加密，携带人机验证结果）
	$login = qmzl_login($account, $password, $captcha, $captchaToken);
	if (!$login['ok']) {
		json_exit_error('登录失败：' . $login['msg']);
	}

	if (!qmzl_account_save_cred($userId, (string)$user['username'], $account, $password)) {
		json_exit_error('保存失败，请重试');
	}
	qmzl_account_set_token($userId, $login['data']['jwt'], $login['data']['exp']);
	qmzl_account_update_status($userId, 'ok', '');
	json_exit_success('绑定成功', ['exp' => $login['data']['exp']]);
});

/** 解绑（仅 client 模式） */
mnbt_register_route('POST', '/' . $qzRoute . '/api/unbind', function ($params, $ctx) {
	$user = qmzl_require_user(true);
	if (qmzl_mode() !== 'client') json_exit_error('代理商模式下无需绑定账号');
	if (!qmzl_account_delete((int)$user['id'])) json_exit_error('解绑失败');
	json_exit_success('已解绑');
});

/** 域名配置（后缀/协议） */
mnbt_register_route('POST', '/' . $qzRoute . '/api/config', function ($params, $ctx) {
	$user = qmzl_require_user(true);
	$tok = qmzl_require_token($user);
	if (!$tok['ok']) json_exit_error($tok['msg']);

	$config = qmzl_config($tok['data']['jwt']);
	if (!$config['ok']) json_exit_error($config['msg'] ?: '获取配置失败');

	// 兜底后缀列表
	$suffixes = [];
	$d = $config['data'] ?: [];
	if (!empty($d['specify_search_domain']) && is_array($d['specify_search_domain'])) {
		foreach ($d['specify_search_domain'] as $s) {
			$suffixes[] = ['suffix' => (string)$s];
		}
	}
	if (empty($suffixes)) {
		$sp = qmzl_suffixes($tok['data']['jwt']);
		if ($sp['ok'] && !empty($sp['data']) && is_array($sp['data'])) {
			$suffixes = $sp['data'];
		}
	}
	if (empty($suffixes)) {
		$suffixes = [['suffix' => '.com'], ['suffix' => '.cn'], ['suffix' => '.net']];
	}
	json_exit_success('ok', [
		'config'   => $d,
		'suffixes' => $suffixes,
	]);
});

/** 域名可用性查询 */
mnbt_register_route('POST', '/' . $qzRoute . '/api/check_domain', function ($params, $ctx) {
	$user = qmzl_require_user(true);
	$domain = trim((string)($_POST['domain'] ?? ''));
	$suffix = trim((string)($_POST['suffix'] ?? '.com'));

	if (!preg_match('/^[a-z0-9]([a-z0-9\-]{0,62}[a-z0-9])?$/i', $domain)) {
		json_exit_error('域名前缀格式不正确');
	}
	if (!preg_match('/^\.[a-z0-9\-.]+$/i', $suffix)) {
		json_exit_error('后缀格式不正确');
	}

	$tok = qmzl_require_token($user);
	if (!$tok['ok']) json_exit_error($tok['msg']);

	$res = qmzl_check($tok['data']['jwt'], strtolower($domain), strtolower($suffix));
	if (!$res['ok']) json_exit_error($res['msg'] ?: '查询失败');
	$list = $res['data'];
	if (isset($list['list'])) $list = $list['list'];
	if (!is_array($list)) $list = [];
	json_exit_success('ok', ['list' => $list]);
});

/** 域名价格（agent 模式自动加上该后缀溢价） */
mnbt_register_route('POST', '/' . $qzRoute . '/api/price', function ($params, $ctx) {
	$user = qmzl_require_user(true);
	$name = trim((string)($_POST['name'] ?? ''));
	if (!preg_match('/^[a-z0-9]([a-z0-9\-]*[a-z0-9])?\.[a-z0-9\-.]+$/i', $name)) {
		json_exit_error('域名格式不正确');
	}
	$tok = qmzl_require_token($user);
	if (!$tok['ok']) json_exit_error($tok['msg']);
	$res = qmzl_price($tok['data']['jwt'], strtolower($name));
	if (!$res['ok']) json_exit_error($res['msg'] ?: '获取价格失败');
	$list = $res['data'];
	if (isset($list['list'])) $list = $list['list'];
	if (!is_array($list)) $list = [];
	$markup = qmzl_apply_markup($list, strtolower($name));
	json_exit_success('ok', ['list' => $list, 'markup' => $markup]);
});

/** 支付方式：agent 模式用 MNBT 内置支付，client 模式用上游网关 */
mnbt_register_route('POST', '/' . $qzRoute . '/api/gateways', function ($params, $ctx) {
	$user = qmzl_require_user(true);
	if (qmzl_mode() === 'agent') {
		$methods = function_exists('mnbt_get_enabled_payment_methods') ? mnbt_get_enabled_payment_methods() : [];
		$list = [];
		foreach ($methods as $m) {
			$list[] = [
				'name'  => (string)($m['plugin'] ?? '') . '__' . (string)($m['method'] ?? ''),
				'title' => (string)($m['display_name'] ?? ($m['method'] ?? '')),
			];
		}
		json_exit_success('ok', ['list' => $list]);
	}
	$res = qmzl_gateways();
	if (!$res['ok']) json_exit_error($res['msg'] ?: '获取支付方式失败');
	$list = is_array($res['data']['list'] ?? null) ? $res['data']['list'] : [];
	json_exit_success('ok', ['list' => $list]);
});

/** 信息模板列表（agent 模式仅显示当前用户创建的模板） */
mnbt_register_route('POST', '/' . $qzRoute . '/api/templates', function ($params, $ctx) {
	$user = qmzl_require_user(true);
	$tok = qmzl_require_token($user);
	if (!$tok['ok']) json_exit_error($tok['msg']);
	$res = qmzl_templates($tok['data']['jwt']);
	if (!$res['ok']) json_exit_error($res['msg'] ?: '获取模板失败');
	$list = [];
	if (isset($res['data']['list'])) {
		$list = $res['data']['list'];
	} elseif (is_array($res['data'])) {
		$list = $res['data'];
	}
	if (!is_array($list)) $list = [];
	if (qmzl_mode() === 'agent') {
		$mine = qmzl_template_ids_by_user((int)$user['id']);
		$list = array_values(array_filter($list, function ($t) use ($mine) {
			return isset($mine[(int)($t['id'] ?? 0)]);
		}));
	}
	json_exit_success('ok', ['list' => $list]);
});

/** 创建/更新模板（含证件照片上传） */
mnbt_register_route('POST', '/' . $qzRoute . '/api/template_save', function ($params, $ctx) {
	$user = qmzl_require_user(true);
	$id = (int)($_POST['id'] ?? 0);
	$newId = 0;

	$fields = [];
	foreach ([
		'type', 'zh_owner', 'zh_all_name', 'zh_last_name', 'zh_first_name',
		'en_owner', 'en_all_name', 'en_last_name', 'en_first_name',
		'email', 'phone', 'zh_address', 'en_address', 'postal_code',
		'country', 'idtype', 'idnum',
	] as $k) {
		if (isset($_POST[$k])) {
			$fields[$k] = trim((string)$_POST[$k]);
		}
	}
	if (empty($fields['type']) || !in_array($fields['type'], ['personal', 'enterprise'], true)) {
		json_exit_error('请选择模板类型');
	}
	foreach (['zh_owner', 'en_owner', 'email', 'phone', 'zh_address', 'en_address', 'idtype', 'idnum'] as $k) {
		if (($fields[$k] ?? '') === '') {
			json_exit_error('必填项缺失：' . $k);
		}
	}
	if (empty($fields['country'])) $fields['country'] = 'CN';

	$tok = qmzl_require_token($user);
	if (!$tok['ok']) json_exit_error($tok['msg']);

	// agent 模式：仅允许操作本人创建的模板
	if (qmzl_mode() === 'agent' && $id > 0 && !qmzl_template_owned($id, (int)$user['id'])) {
		json_exit_error('无权操作该模板');
	}

	$files = ['img_front' => $_FILES['img_front'] ?? null, 'img_back' => $_FILES['img_back'] ?? null];
	foreach ($files as $k => $f) {
		if (!empty($f['tmp_name']) && $f['size'] > 5 * 1024 * 1024) {
			json_exit_error('证件照片不能超过 5MB');
		}
	}
	if ($id > 0) {
		$res = qmzl_template_update($tok['data']['jwt'], $id, $fields, $files);
	} else {
		if (empty($files['img_front']['tmp_name'])) {
			json_exit_error('证件正面照片为必传项');
		}
		$res = qmzl_template_create($tok['data']['jwt'], $fields, $files);
	}
	if (!$res['ok']) json_exit_error($res['msg'] ?: '保存失败');
	// agent 模式：记录新模板归属
	if (qmzl_mode() === 'agent' && $id <= 0) {
		$newId = 0;
		$d = $res['data'];
		if (is_array($d)) {
			if (!empty($d['id'])) {
				$newId = (int)$d['id'];
			} elseif (!empty($d['template_id'])) {
				$newId = (int)$d['template_id'];
			} elseif (is_array($d['info_template'] ?? null) && !empty($d['info_template']['id'])) {
				$newId = (int)$d['info_template']['id'];
			}
		}
		if ($newId > 0) {
			qmzl_template_record($newId, (int)$user['id'], (string)$user['username']);
		}
	}
	json_exit_success($id > 0 ? '模板已更新' : '模板已创建', ['id' => $id > 0 ? $id : $newId]);
});

/** 删除模板 */
mnbt_register_route('POST', '/' . $qzRoute . '/api/template_delete', function ($params, $ctx) {
	$user = qmzl_require_user(true);
	$id = (int)($_POST['id'] ?? 0);
	if ($id <= 0) json_exit_error('参数错误');
	if (qmzl_mode() === 'agent' && !qmzl_template_owned($id, (int)$user['id'])) {
		json_exit_error('无权操作该模板');
	}
	$tok = qmzl_require_token($user);
	if (!$tok['ok']) json_exit_error($tok['msg']);
	$res = qmzl_template_delete($tok['data']['jwt'], $id);
	if (!$res['ok']) json_exit_error($res['msg'] ?: '删除失败');
	json_exit_success('已删除');
});

/** 提交实名认证 */
mnbt_register_route('POST', '/' . $qzRoute . '/api/template_certify', function ($params, $ctx) {
	$user = qmzl_require_user(true);
	$id = (int)($_POST['id'] ?? 0);
	if ($id <= 0) json_exit_error('参数错误');
	if (qmzl_mode() === 'agent' && !qmzl_template_owned($id, (int)$user['id'])) {
		json_exit_error('无权操作该模板');
	}
	$files = ['img_front' => $_FILES['img_front'] ?? null, 'img_back' => $_FILES['img_back'] ?? null];
	if (empty($files['img_front']['tmp_name'])) {
		json_exit_error('请上传证件正面照片');
	}
	foreach ($files as $k => $f) {
		if (!empty($f['tmp_name']) && $f['size'] > 5 * 1024 * 1024) {
			json_exit_error('证件照片不能超过 5MB');
		}
	}
	$tok = qmzl_require_token($user);
	if (!$tok['ok']) json_exit_error($tok['msg']);
	$res = qmzl_template_certify($tok['data']['jwt'], $id, $files);
	if (!$res['ok']) json_exit_error($res['msg'] ?: '提交失败');
	json_exit_success('已提交实名认证，请等待审核');
});

/** 下单：agent 模式走 MNBT 支付，client 模式走上游购物车结算 */
mnbt_register_route('POST', '/' . $qzRoute . '/api/place_order', function ($params, $ctx) {
	global $DB, $date, $siteurl;
	$user = qmzl_require_user(true);
	$userId = (int)$user['id'];
	$domain     = strtolower(trim((string)($_POST['domain'] ?? '')));
	$year       = (int)($_POST['year'] ?? 1);
	$templateId = (int)($_POST['template_id'] ?? 0);
	$gateway    = trim((string)($_POST['gateway'] ?? ''));
	$autoRenew  = isset($_POST['auto_renew']) ? (int)$_POST['auto_renew'] : 1;
	$lockStatus = isset($_POST['lock_status']) ? (int)$_POST['lock_status'] : 1;

	if (!preg_match('/^[a-z0-9]([a-z0-9\-]*[a-z0-9])?\.[a-z0-9\-.]+$/i', $domain)) {
		json_exit_error('域名格式不正确');
	}
	if ($year < 1 || $year > 10) {
		json_exit_error('购买年限不正确');
	}
	if ($templateId <= 0) {
		json_exit_error('请选择信息模板');
	}
	if (qmzl_mode() === 'agent' && !qmzl_template_owned($templateId, $userId)) {
		json_exit_error('信息模板不存在或不属于你，请先创建并完成实名认证');
	}
	if ($gateway === '') {
		json_exit_error('请选择支付方式');
	}

	$tok = qmzl_require_token($user);
	if (!$tok['ok']) json_exit_error($tok['msg']);
	$jwt = $tok['data']['jwt'];

	// 1) 取价格（以当次查询为准）
	$priceRes = qmzl_price($jwt, $domain);
	if (!$priceRes['ok']) json_exit_error($priceRes['msg'] ?: '获取价格失败');
	$priceList = $priceRes['data'];
	if (isset($priceList['list'])) $priceList = $priceList['list'];
	if (!is_array($priceList)) $priceList = [];
	$amount = '';
	foreach ($priceList as $p) {
		if ((int)($p['buyyear'] ?? 0) === $year) {
			$amount = (string)($p['buyprice'] ?? '');
			break;
		}
	}
	if ($amount === '') {
		json_exit_error('未获取到该域名 ' . $year . ' 年价格');
	}
	// agent 模式：加上该后缀溢价
	if (qmzl_mode() === 'agent') {
		$markup = qmzl_apply_markup($priceList, $domain);
		if ($markup > 0) {
			$amount = (string)round((float)$amount + $markup, 2);
		}
	}

	/* ---- agent 模式：创建 MN_dd 订单走系统支付 ---- */
	if (qmzl_mode() === 'agent') {
		$out_trade_no = date("YmdHis") . mt_rand(100, 999);
		$cs = json_encode([
			'user_id'     => $userId,
			'username'    => (string)$user['username'],
			'domain'      => $domain,
			'year'        => $year,
			'template_id' => $templateId,
			'auto_renew'  => $autoRenew,
			'lock_status' => $lockStatus,
			'amount'      => $amount,
			'gateway'     => $gateway,
		], 256);
		$ip = $_SERVER["REMOTE_ADDR"] ?? '127.0.0.1';
		$row1 = $DB->get_row_prepare("SELECT * FROM MN_dd WHERE 1 order by id desc limit 1");
		$dd_id = $row1 ? ((int)$row1['id'] + 1) : 1;
		$ok = $DB->query_prepare(
			"INSERT INTO MN_dd (id, cs, date, zffs, je, ddh, lx, qk, ip) VALUES (?,?,?,?,?,?,?,?,?)",
			[$dd_id, $cs, $date, $gateway, $amount, $out_trade_no, 'qmzl_domain', 'false', $ip]
		);
		if (!$ok) json_exit_error('创建订单失败，请稍后重试');

		// 本地订单记录（ddh 关联，待支付）
		qmzl_order_create($userId, (string)$user['username'], $out_trade_no, $domain, $year, $amount, $templateId, '', $gateway, '等待支付');

		// 分发到 MNBT 支付插件
		$order_context = [
			'out_trade_no' => $out_trade_no,
			'name'         => '域名注册：' . $domain,
			'money'        => $amount,
			'type'         => $gateway,
			'siteurl'      => $siteurl,
			'pay_lx'       => 'qmzl_domain',
		];
		$html = function_exists('mnbt_pay_dispatch_gateway') ? mnbt_pay_dispatch_gateway($gateway, $order_context) : false;
		if ($html === false) {
			json_exit_error('支付方式不可用，请检查支付插件是否已启用');
		}
		json_exit_success('下单成功，请完成支付', ['mode' => 'agent', 'html' => $html, 'order_no' => $out_trade_no]);
	}

	/* ---- client 模式：上游购物车 + 结算 ---- */
	$productId = qmzl_product_id($jwt);
	if ($productId <= 0) {
		json_exit_error('未找到域名产品，请联系平台确认');
	}
	$cart = qmzl_cart_add($jwt, $productId, $domain, $year);
	if (!$cart['ok']) json_exit_error('加入购物车失败：' . $cart['msg']);

	$settle = qmzl_cart_settle($jwt, $templateId, $autoRenew, $lockStatus);
	if (!$settle['ok']) json_exit_error('下单失败：' . $settle['msg']);
	$orderId = 0;
	if (isset($settle['data']['order_id'])) {
		$orderId = (int)$settle['data']['order_id'];
	} elseif (!empty($settle['data']['ids']) && is_array($settle['data']['ids'])) {
		$orderId = (int)$settle['data']['ids'][0];
	}
	if ($orderId <= 0) json_exit_error('下单失败：未返回订单号');

	qmzl_order_create($userId, (string)$user['username'], '', $domain, $year, $amount, $templateId, $orderId, $gateway, '等待支付');

	json_exit_success('下单成功，请前往支付', [
		'mode'          => 'client',
		'order_id'      => $orderId,
		'amount'        => $amount,
		'gateway'       => $gateway,
		'cloud_pay_url' => QMZL_SITE . '/console/payment?orderId=' . $orderId . '&amount=' . rawurlencode($amount) . '&gateway=' . rawurlencode($gateway),
	]);
});

/** 发起支付（仅 client 模式，返回三方 HTML） */
mnbt_register_route('POST', '/' . $qzRoute . '/api/pay', function ($params, $ctx) {
	$user = qmzl_require_user(true);
	if (qmzl_mode() !== 'client') json_exit_error('代理商模式无需此操作');
	$userId = (int)$user['id'];
	$orderId = (int)($_POST['order_id'] ?? 0);
	$gateway = trim((string)($_POST['gateway'] ?? ''));
	if ($orderId <= 0 || $gateway === '') json_exit_error('参数错误');
	$rec = qmzl_order_get($orderId);
	if ($rec && (int)$rec['user_id'] !== $userId) json_exit_error('订单不存在');
	$tok = qmzl_require_token($user);
	if (!$tok['ok']) json_exit_error($tok['msg']);
	$res = qmzl_pay($tok['data']['jwt'], $orderId, $gateway);
	if (!$res['ok']) json_exit_error($res['msg'] ?: '发起支付失败');
	if (!empty($res['paid'])) {
		qmzl_order_update_status($orderId, 'Paid');
		json_exit_success('该订单已支付', ['code' => 'Paid']);
	}
	json_exit_success('ok', ['code' => '', 'html' => $res['pay_html']]);
});

/** 支付状态轮询（仅 client 模式） */
mnbt_register_route('POST', '/' . $qzRoute . '/api/pay_status', function ($params, $ctx) {
	$user = qmzl_require_user(true);
	if (qmzl_mode() !== 'client') json_exit_error('代理商模式无需此操作');
	$userId = (int)$user['id'];
	$orderId = (int)($_POST['order_id'] ?? 0);
	if ($orderId <= 0) json_exit_error('参数错误');
	$rec = qmzl_order_get($orderId);
	if ($rec && (int)$rec['user_id'] !== $userId) json_exit_error('订单不存在');
	$tok = qmzl_require_token($user);
	if (!$tok['ok']) json_exit_error($tok['msg']);
	$res = qmzl_pay_status($tok['data']['jwt'], $orderId);
	if (!$res['ok']) json_exit_error($res['msg'] ?: '查询失败');
	if (!empty($res['paid'])) {
		qmzl_order_update_status($orderId, 'Paid');
	}
	json_exit_success('ok', ['paid' => !empty($res['paid'])]);
});

/** 我的域名 */
mnbt_register_route('POST', '/' . $qzRoute . '/api/domains', function ($params, $ctx) {
	$user = qmzl_require_user(true);
	$data = qmzl_my_domains($user);
	if (!$data['ok']) json_exit_error($data['msg']);
	json_exit_success('ok', ['list' => $data['list']]);
});

/** 本地订单记录 */
mnbt_register_route('POST', '/' . $qzRoute . '/api/orders', function ($params, $ctx) {
	$user = qmzl_require_user(true);
	$page = max(1, (int)($_POST['page'] ?? 1));
	$data = qmzl_order_list((int)$user['id'], $page, 20);
	json_exit_success('ok', $data);
});

} // end 用户端开关
