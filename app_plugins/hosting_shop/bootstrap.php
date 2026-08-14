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

// 自动升级表结构（追加新周期价格字段等）
hosting_upgrade_schema();

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

	hosting_render('shop', [
		'page_title' => '主机套餐',
		'plans' => $plans,
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
	// 获取已启用的支付方式
	$methods = function_exists('mnbt_get_enabled_payment_methods') ? mnbt_get_enabled_payment_methods() : [];

	hosting_render('order', [
		'page_title' => '购买：' . $plan['name'],
		'plan' => $plan,
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

// 上架套餐列表（SPA 数据接口）
mnbt_register_route('GET', '/shop/api/plans', function ($params, $ctx) {
	if (!function_exists('hosting_plan_list_active')) {
		hosting_json('ok', ['plans' => []]);
		return;
	}
	$rows = hosting_plan_list_active();
	$plans = [];
	foreach ($rows as $p) {
		$plans[] = [
			'id'          => (int)$p['id'],
			'name'        => (string)$p['name'],
			'description' => (string)($p['description'] ?? ''),
			'category'    => (string)($p['category'] ?? ''),
			'node'        => (string)($p['node'] ?? ''),
			'spec_web'    => (int)($p['spec_web'] ?? 0),
			'spec_sql'    => (int)($p['spec_sql'] ?? 0),
			'spec_flow'   => (int)($p['spec_flow'] ?? 0),
			'spec_domain' => (int)($p['spec_domain'] ?? 0),
			'periods'     => hosting_plan_enabled_periods($p),
			'prices'      => [
				'month'      => (int)($p['price_month_cents'] ?? 0),
				'quarter'    => (int)($p['price_quarter_cents'] ?? 0),
				'half_year'  => (int)($p['price_half_year_cents'] ?? 0),
				'year'       => (int)($p['price_year_cents'] ?? 0),
				'two_year'   => (int)($p['price_two_year_cents'] ?? 0),
				'three_year' => (int)($p['price_three_year_cents'] ?? 0),
			],
		];
	}
	hosting_json('ok', ['plans' => $plans]);
});

// 套餐详情（SPA 下单页数据接口）
mnbt_register_route('GET', '/shop/api/plan/{plan_id}', function ($params, $ctx) {
	$plan_id = (int)($params['plan_id'] ?? 0);
	$plan = hosting_plan_get($plan_id);
	if (!$plan || $plan['status'] !== 'active') {
		hosting_json('套餐不存在或已下架');
		return;
	}
	$methods = function_exists('mnbt_get_enabled_payment_methods') ? mnbt_get_enabled_payment_methods() : [];
	hosting_json('ok', [
		'plan' => [
			'id'          => (int)$plan['id'],
			'name'        => (string)$plan['name'],
			'description' => (string)($plan['description'] ?? ''),
			'node'        => (string)($plan['node'] ?? ''),
			'spec_web'    => (int)($plan['spec_web'] ?? 0),
			'spec_sql'    => (int)($plan['spec_sql'] ?? 0),
			'spec_flow'   => (int)($plan['spec_flow'] ?? 0),
			'spec_domain' => (int)($plan['spec_domain'] ?? 0),
			'periods'     => hosting_plan_enabled_periods($plan),
			'prices'      => [
				'month'      => (int)($plan['price_month_cents'] ?? 0),
				'quarter'    => (int)($plan['price_quarter_cents'] ?? 0),
				'half_year'  => (int)($plan['price_half_year_cents'] ?? 0),
				'year'       => (int)($plan['price_year_cents'] ?? 0),
				'two_year'   => (int)($plan['price_two_year_cents'] ?? 0),
				'three_year' => (int)($plan['price_three_year_cents'] ?? 0),
			],
		],
		'methods' => $methods,
	]);
});

// 我的资产（SPA 数据接口）
mnbt_register_route('GET', '/shop/api/assets', function ($params, $ctx) {
	$user = function_exists('user_info_auth_current') ? user_info_auth_current() : null;
	if (!$user) {
		hosting_json('not_login', ['logged_in' => false]);
		return;
	}
	$assets = hosting_asset_list_by_user((int)$user['id']);
	hosting_json('ok', ['logged_in' => true, 'assets' => $assets]);
});

// 我的订单（SPA 数据接口，分页）
mnbt_register_route('GET', '/shop/api/orders', function ($params, $ctx) {
	$user = function_exists('user_info_auth_current') ? user_info_auth_current() : null;
	if (!$user) {
		hosting_json('not_login', ['logged_in' => false]);
		return;
	}
	$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
	$per = isset($_GET['per_page']) ? max(1, min(100, (int)$_GET['per_page'])) : 15;
	$orders = hosting_order_list_by_user((int)$user['id'], $page, $per);
	hosting_json('ok', ['logged_in' => true, 'orders' => $orders]);
});

// 可用支付方式（SPA 下单页数据接口）
mnbt_register_route('GET', '/shop/api/methods', function ($params, $ctx) {
	$methods = function_exists('mnbt_get_enabled_payment_methods') ? mnbt_get_enabled_payment_methods() : [];
	hosting_json('ok', ['methods' => $methods]);
});

// 创建购买订单 → 调用支付插件
mnbt_register_route('POST', '/shop/api/create_order', function ($params, $ctx) {
	global $DB, $date, $siteurl;

	$user = hosting_require_user();
	$user_id = (int)$user['id'];

	$plan_id = (int)($_POST['plan_id'] ?? 0);
	$period = isset($_POST['period']) ? trim($_POST['period']) : 'month';
	$type = isset($_POST['type']) ? trim($_POST['type']) : '';

	// 校验套餐
	$plan = hosting_plan_get($plan_id);
	if (!$plan || $plan['status'] !== 'active') {
		hosting_json('套餐不存在或已下架');
	}
	$periods = hosting_periods();
	if (!isset($periods[$period])) {
		hosting_json('请选择有效的购买周期');
	}
	$enabled = hosting_plan_enabled_periods($plan);
	if (!in_array($period, $enabled, true)) {
		hosting_json('该套餐不支持此购买周期');
	}
	$price_field = hosting_period_price_field($period);
	$amount_cents = $price_field ? (int)($plan[$price_field] ?? 0) : 0;
	if ($amount_cents < 0) {
		hosting_json('该套餐此周期价格异常');
	}
	// 节点已由套餐固定配置，无需前端传入
	$node = trim((string)($plan['node'] ?? ''));
	if ($node === '' || !hosting_node_get($node)) {
		hosting_json('套餐未配置有效开通节点，请联系管理员');
	}
	// 非 0 元订单校验支付方式；0 元订单无需选择支付方式
	if ($amount_cents > 0) {
		if ($type === '' || !function_exists('mnbt_pay_parse_type') || !mnbt_pay_parse_type($type)) {
			hosting_json('请选择有效的支付方式');
		}
	}

	// 创建 hosting 订单（节点从套餐读取）
	$create = hosting_order_create($user, $plan, $period);
	if (empty($create['ok'])) {
		hosting_json($create['msg'] ?? '创建订单失败');
	}
	$order_no = $create['order_no'];
	$hosting_order_id = (int)$create['order_id'];

	// 0 元免费套餐：直接标记 paid 并开通，无需创建 MN_dd 和调支付网关
	if ($amount_cents === 0) {
		hosting_order_set_status($hosting_order_id, 'paid', '免费套餐直接开通');
		$open = hosting_open_host($hosting_order_id);
		if (!$open['ok']) {
			hosting_json('开通失败：' . ($open['msg'] ?? '未知错误'));
		}
		hosting_json('开通成功', ['redirect' => hosting_url('shop/assets')]);
	}

	// 创建 MN_dd 记录（支付系统订单）
	$amount_yuan = (string)round($amount_cents / 100, 2);
	$cs = json_encode([
		'user_id' => $user_id,
		'plan_id' => $plan_id,
		'period' => $period,
		'node' => $node,
		'amount' => $amount_cents,
		'username' => $user['username'],
		'order_id' => $hosting_order_id,
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
		hosting_order_set_status($hosting_order_id, 'cancelled', '支付订单创建失败');
		hosting_json('支付订单创建失败，请稍后重试');
	}

	// 分发到支付插件
	$period_label = $periods[$period]['label'];
	$order_context = [
		'out_trade_no' => $order_no,
		'name' => '购买主机：' . $plan['name'] . '（' . $period_label . '）',
		'money' => $amount_yuan,
		'type' => $type,
		'siteurl' => $siteurl,
		'pay_lx' => 'hosting',
	];

	$html = mnbt_pay_dispatch_gateway($type, $order_context);
	if ($html === false) {
		hosting_order_set_status($hosting_order_id, 'cancelled', '支付方式不可用');
		hosting_json('支付方式不可用，请检查支付插件是否已启用');
	}

	hosting_json('ok', ['html' => $html, 'order_no' => $order_no]);
});

/* ============================================================
 *  管理员端页面注册
 * ============================================================ */

mnbt_register_page('admin', 'plans', 'views/admin/plans.php', '套餐管理');
mnbt_register_page('admin', 'plan_edit', 'views/admin/plan_edit.php', '套餐编辑');
mnbt_register_page('admin', 'orders', 'views/admin/orders.php', '订单管理');
mnbt_register_page('admin', 'assets', 'views/admin/assets.php', '资产管理');

// 侧边栏菜单（三级结构）
mnbt_register_menu('admin', [
	'title' => '主机售卖',
	'icon'  => 'mdi-cart',
	'order' => 60,
	'children' => [
		['title' => '套餐管理', 'page' => 'plans', 'icon' => 'mdi-package-variant', 'multitabs' => true],
		['title' => '订单管理', 'page' => 'orders', 'icon' => 'mdi-receipt', 'multitabs' => true],
		['title' => '资产管理', 'page' => 'assets', 'icon' => 'mdi-server', 'multitabs' => true],
	],
]);

/* ============================================================
 *  节点 PHP 版本管理已移至系统底层
 *  请访问管理后台 → 节点与宝塔 → 节点PHP版本
 * ============================================================ */
