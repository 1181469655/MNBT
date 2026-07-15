<?php
/**
 * balance 插件 - 主入口
 *
 * 功能：余额查询、充值（调用支付插件 API）、流水记录
 * 依赖：user_info 插件（认证）、支付插件（epay/alipay_official）
 * 架构：通过 P2 路由注册 /balance/* 路径；通过 order.paid 钩子处理充值结算
 */

if (!defined('IN_CRONLITE')) {
	exit;
}

require_once __DIR__ . '/lib/balance.php';

mnbt_plugin_register('balance', [
	'name' => '余额管理',
	'description' => '用户余额、充值、流水',
]);

/* ============================================================
 *  order.paid 钩子：处理充值订单结算
 * ============================================================
 *  支付插件回调验签后调 mnbt_pay_settle_order()，核心标记订单完成
 *  并触发 order.paid。此处检查 lx=recharge，增加用户余额。
 */
mnbt_add_action('order.paid', function ($order_row, $ctx = []) {
	if (!is_array($order_row)) {
		return;
	}
	if (($order_row['lx'] ?? '') !== 'recharge') {
		return;
	}
	$cs = json_decode($order_row['cs'] ?? '', true);
	if (!is_array($cs)) {
		return;
	}
	$user_id = (int)($cs['user_id'] ?? 0);
	$amount_cents = (int)($cs['amount'] ?? 0);
	if ($user_id <= 0 || $amount_cents <= 0) {
		return;
	}
	// 防重复：检查该订单是否已入账
	$exists = $GLOBALS['DB']->get_row_prepare(
		"SELECT id FROM MN_plugin_balance_log WHERE user_id=? AND order_no=? AND type='recharge' LIMIT 1",
		[$user_id, $order_row['ddh']]
	);
	if ($exists) {
		return;
	}
	balance_add($user_id, $amount_cents, 'recharge', $order_row['ddh'], '余额充值');
}, 10);

/* ============================================================
 *  页面路由
 * ============================================================ */

// 余额首页（显示余额 + 流水）
mnbt_register_route('GET', '/balance', function ($params, $ctx) {
	$user = balance_require_user();
	$user_id = (int)$user['id'];
	$balance = balance_get($user_id);

	$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
	$logs = balance_logs($user_id, $page, 15);

	balance_render('balance', [
		'page_title' => '我的余额',
		'balance_cents' => $balance,
		'logs' => $logs,
	]);
});

// 充值页面
mnbt_register_route('GET', '/balance/recharge', function ($params, $ctx) {
	$user = balance_require_user();

	// 获取已启用的支付方式
	$methods = [];
	if (function_exists('mnbt_get_enabled_payment_methods')) {
		$methods = mnbt_get_enabled_payment_methods();
	}

	balance_render('recharge', [
		'page_title' => '余额充值',
		'methods' => $methods,
	]);
});

/* ============================================================
 *  API 路由
 * ============================================================ */

// 创建充值订单 → 调用支付插件
mnbt_register_route('POST', '/balance/api/create_recharge', function ($params, $ctx) {
	global $DB, $date, $siteurl;

	$user = balance_require_user();
	$user_id = (int)$user['id'];

	$amount_yuan = isset($_POST['amount']) ? (float)$_POST['amount'] : 0;
	$type = isset($_POST['type']) ? trim($_POST['type']) : '';

	// 验证金额（最低 1 元，最高 50000 元）
	if ($amount_yuan < 1) {
		balance_json('充值金额至少 1 元');
	}
	if ($amount_yuan > 50000) {
		balance_json('单次充值金额不能超过 50000 元');
	}
	$amount_cents = (int)round($amount_yuan * 100);

	// 验证支付方式
	if ($type === '' || !function_exists('mnbt_pay_parse_type') || !mnbt_pay_parse_type($type)) {
		balance_json('请选择有效的支付方式');
	}

	// 创建订单（MN_dd 表）
	$out_trade_no = date("YmdHis") . mt_rand(100, 999);
	$cs = json_encode([
		'user_id' => $user_id,
		'amount' => $amount_cents,
		'username' => $user['username'],
	], 256);
	$ip = $_SERVER["REMOTE_ADDR"] ?? '127.0.0.1';

	$row1 = $DB->get_row_prepare("SELECT * FROM MN_dd WHERE 1 order by id desc limit 1");
	$id = $row1 ? ((int)$row1['id'] + 1) : 1;
	$ok = $DB->query_prepare(
		"INSERT INTO MN_dd (id, cs, date, zffs, je, ddh, lx, qk, ip) VALUES (?,?,?,?,?,?,?,?,?)",
		[$id, $cs, $date, $type, $amount_yuan, $out_trade_no, 'recharge', 'false', $ip]
	);
	if (!$ok) {
		balance_json('创建订单失败，请稍后重试');
	}

	// 分发到支付插件
	$order_context = [
		'out_trade_no' => $out_trade_no,
		'name' => '余额充值',
		'money' => (string)$amount_yuan,
		'type' => $type,
		'siteurl' => $siteurl,
		'pay_lx' => 'recharge',
	];

	$html = mnbt_pay_dispatch_gateway($type, $order_context);
	if ($html === false) {
		balance_json('支付方式不可用，请检查支付插件是否已启用');
	}

	// 返回支付 HTML，前端用 document.write 输出跳转
	balance_json('正在跳转到支付页面', ['html' => $html]);
});

/* ============================================================
 *  管理员端页面注册
 * ============================================================ */

mnbt_register_page('admin', 'balances', 'views/admin/balances.php', '余额管理');
mnbt_register_page('admin', 'balance_logs', 'views/admin/logs.php', '余额流水');

mnbt_register_menu('admin', [
	'title' => '余额管理 - 用户余额',
	'page'  => 'balances',
	'icon'  => 'mdi-cash-multiple',
	'order' => 71,
	'multitabs' => true,
]);

mnbt_register_menu('admin', [
	'title' => '余额管理 - 流水记录',
	'page'  => 'balance_logs',
	'icon'  => 'mdi-history',
	'order' => 72,
	'multitabs' => true,
]);

// 管理员端 AJAX：调整用户余额
mnbt_register_ajax('admin', 'balance_admin_adjust', function () {
	mnbt_plugin_require_admin();
	$user_id = (int)($_POST['user_id'] ?? 0);
	$amount_yuan = (float)($_POST['amount'] ?? 0);
	$direction = $_POST['direction'] ?? '';   // add / deduct
	$remark = trim((string)($_POST['remark'] ?? ''));

	if ($user_id <= 0) {
		json_exit('参数错误');
	}
	if ($amount_yuan <= 0) {
		json_exit('金额必须大于 0');
	}
	if (!in_array($direction, ['add', 'deduct'], true)) {
		json_exit('操作类型错误');
	}
	$amount_cents = (int)round($amount_yuan * 100);
	if ($amount_cents <= 0) {
		json_exit('金额必须大于 0');
	}
	$remark = $remark === '' ? '管理员调整' : $remark;

	if ($direction === 'add') {
		$ok = balance_add($user_id, $amount_cents, 'adjust', '', '管理员加款：' . $remark);
	} else {
		$ok = balance_deduct($user_id, $amount_cents, 'adjust', '', '管理员扣款：' . $remark);
	}
	if (!$ok) {
		json_exit($direction === 'deduct' ? '扣款失败（余额不足）' : '加款失败');
	}
	json_exit('调整成功');
});
