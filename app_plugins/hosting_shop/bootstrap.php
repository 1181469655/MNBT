<?php
/**
 * hosting_shop 插件 - 主入口
 *
 * 功能：套餐售卖、购买下单、自动开通、资产/订单管理
 * 依赖：user_info 插件（认证）、balance 插件（余额）、支付插件（epay/alipay_official）
 * 架构：
 *   - 用户端：通过 P2 路由注册 /shop/* 路径
 *   - 管理员端：通过 mnbt_register_page('admin', ...) 注册到 admin/plugin.php
 *   - 开通：通过 order.paid 钩子处理 lx=hosting 订单，调用宝塔 API 开通主机
 */

if (!defined('IN_CRONLITE')) {
	exit;
}

require_once __DIR__ . '/lib/hosting.php';

mnbt_plugin_register('hosting_shop', [
	'name' => '主机售卖',
	'description' => '虚拟主机套餐售卖、自动开通、资产订单管理',
]);

/* ============================================================
 *  order.paid 钩子：处理主机购买订单
 * ============================================================
 *  支付成功后由 mnbt_pay_settle_order() 触发。
 *  1. 检查订单是否已处理（防重复）
 *  2. 标记订单为 paid
 *  3. 调用 hosting_open_host() 开通主机
 */
mnbt_add_action('order.paid', function ($order_row, $ctx = []) {
	if (!is_array($order_row)) {
		return;
	}
	if (($order_row['lx'] ?? '') !== 'hosting') {
		return;
	}
	$order_no = (string)($order_row['ddh'] ?? '');
	if ($order_no === '') {
		return;
	}

	// 防重复：检查该订单号对应的 hosting 订单是否已存在
	$hosting_order = hosting_order_get_by_no($order_no);
	if (!$hosting_order) {
		// 可能是直接走支付完成的订单，但没有 hosting 订单记录，跳过
		return;
	}
	// 已开通/已失败，跳过
	if (in_array($hosting_order['status'], ['opened', 'failed', 'paid'], true)) {
		return;
	}

	// 标记为 paid，然后开通
	hosting_order_set_status((int)$hosting_order['id'], 'paid', '支付完成');
	$result = hosting_open_host((int)$hosting_order['id']);
	if (!$result['ok']) {
		// 开通失败已在 hosting_open_host 内标记 failed
		@error_log('[hosting_shop] open failed order=' . $order_no . ' : ' . ($result['msg'] ?? ''));
	}
}, 20);

/* ============================================================
 *  用户端页面路由
 * ============================================================ */

// 售卖页（套餐列表）
mnbt_register_route('GET', '/shop', function ($params, $ctx) {
	hosting_require_user();
	$plans = hosting_plan_list_active();
	$nodes = hosting_node_list();

	hosting_render('shop', [
		'page_title' => '主机套餐',
		'plans' => $plans,
		'nodes' => $nodes,
	]);
});

// 下单页
mnbt_register_route('GET', '/shop/order/{plan_id}', function ($params, $ctx) {
	hosting_require_user();
	$plan_id = (int)($params['plan_id'] ?? 0);
	$plan = hosting_plan_get($plan_id);
	if (!$plan || $plan['status'] !== 'active') {
		http_response_code(404);
		echo '套餐不存在或已下架';
		return;
	}
	$nodes = hosting_node_list();
	// 获取已启用的支付方式
	$methods = function_exists('mnbt_get_enabled_payment_methods') ? mnbt_get_enabled_payment_methods() : [];

	hosting_render('order', [
		'page_title' => '购买：' . $plan['name'],
		'plan' => $plan,
		'nodes' => $nodes,
		'methods' => $methods,
	]);
});

// 我的资产
mnbt_register_route('GET', '/shop/assets', function ($params, $ctx) {
	$user = hosting_require_user();
	$assets = hosting_asset_list_by_user((int)$user['id']);

	hosting_render('assets', [
		'page_title' => '我的主机',
		'assets' => $assets,
	]);
});

// 我的订单
mnbt_register_route('GET', '/shop/orders', function ($params, $ctx) {
	$user = hosting_require_user();
	$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
	$orders = hosting_order_list_by_user((int)$user['id'], $page, 15);

	hosting_render('orders', [
		'page_title' => '我的订单',
		'orders' => $orders,
	]);
});

/* ============================================================
 *  用户端 API 路由
 * ============================================================ */

// 创建购买订单 → 调用支付插件
mnbt_register_route('POST', '/shop/api/create_order', function ($params, $ctx) {
	global $DB, $date, $siteurl;

	$user = hosting_require_user();
	$user_id = (int)$user['id'];

	$plan_id = (int)($_POST['plan_id'] ?? 0);
	$period = isset($_POST['period']) ? trim($_POST['period']) : 'month';
	$node = isset($_POST['node']) ? trim($_POST['node']) : '';
	$type = isset($_POST['type']) ? trim($_POST['type']) : '';

	// 校验套餐
	$plan = hosting_plan_get($plan_id);
	if (!$plan || $plan['status'] !== 'active') {
		hosting_json('套餐不存在或已下架');
	}
	if (!in_array($period, ['month', 'year'], true)) {
		hosting_json('请选择有效的购买周期');
	}
	$amount_cents = $period === 'year' ? (int)$plan['price_year_cents'] : (int)$plan['price_month_cents'];
	if ($amount_cents <= 0) {
		hosting_json('该套餐此周期不可购买');
	}
	// 校验节点
	if ($node === '' || !hosting_node_get($node)) {
		hosting_json('请选择有效的开通节点');
	}
	// 校验支付方式
	if ($type === '' || !function_exists('mnbt_pay_parse_type') || !mnbt_pay_parse_type($type)) {
		hosting_json('请选择有效的支付方式');
	}

	// 创建 hosting 订单
	$create = hosting_order_create($user, $plan, $period, $node);
	if (empty($create['ok'])) {
		hosting_json($create['msg'] ?? '创建订单失败');
	}
	$order_no = $create['order_no'];

	// 创建 MN_dd 记录（支付系统订单）
	$amount_yuan = (string)round($amount_cents / 100, 2);
	$cs = json_encode([
		'user_id' => $user_id,
		'plan_id' => $plan_id,
		'period' => $period,
		'node' => $node,
		'amount' => $amount_cents,
		'username' => $user['username'],
		'order_id' => $create['order_id'],
	], 256);
	$ip = $_SERVER["REMOTE_ADDR"] ?? '127.0.0.1';

	$row1 = $DB->get_row_prepare("SELECT * FROM MN_dd WHERE 1 order by id desc limit 1");
	$dd_id = $row1 ? ((int)$row1['id'] + 1) : 1;
	$ok = $DB->query_prepare(
		"INSERT INTO MN_dd (id, cs, date, zffs, je, ddh, lx, qk, ip) VALUES (?,?,?,?,?,?,?,?,?)",
		[$dd_id, $cs, $date, $type, $amount_yuan, $order_no, 'hosting', 'false', $ip]
	);
	if (!$ok) {
		// 回滚 hosting 订单状态
		hosting_order_set_status((int)$create['order_id'], 'cancelled', '支付订单创建失败');
		hosting_json('支付订单创建失败，请稍后重试');
	}

	// 分发到支付插件
	$order_context = [
		'out_trade_no' => $order_no,
		'name' => '购买主机：' . $plan['name'] . '（' . ($period === 'year' ? '年付' : '月付') . '）',
		'money' => $amount_yuan,
		'type' => $type,
		'siteurl' => $siteurl,
		'pay_lx' => 'hosting',
	];

	$html = mnbt_pay_dispatch_gateway($type, $order_context);
	if ($html === false) {
		hosting_order_set_status((int)$create['order_id'], 'cancelled', '支付方式不可用');
		hosting_json('支付方式不可用，请检查支付插件是否已启用');
	}

	hosting_json('正在跳转到支付页面', ['html' => $html, 'order_no' => $order_no]);
});

/* ============================================================
 *  管理员端页面注册
 * ============================================================ */

mnbt_register_page('admin', 'plans', 'views/admin/plans.php', '套餐管理');
mnbt_register_page('admin', 'plan_edit', 'views/admin/plan_edit.php', '套餐编辑');
mnbt_register_page('admin', 'orders', 'views/admin/orders.php', '订单管理');
mnbt_register_page('admin', 'assets', 'views/admin/assets.php', '资产管理');

// 侧边栏菜单
mnbt_register_menu('admin', ['title' => '主机售卖 - 套餐管理', 'page' => 'plans', 'order' => 60]);
mnbt_register_menu('admin', ['title' => '主机售卖 - 订单管理', 'page' => 'orders', 'order' => 61]);
mnbt_register_menu('admin', ['title' => '主机售卖 - 资产管理', 'page' => 'assets', 'order' => 62]);
