<?php
/**
 * docker_shop 插件 - 主入口
 *
 * 功能：Docker 售卖套餐、购买下单、自动开通 Docker 账号、资产/订单管理
 * 依赖：user_info 插件（认证）、balance 插件（余额）、支付插件（epay/alipay_official）
 * 架构：
 *   - 用户端：通过 P2 路由注册 /docker-shop/* 路径
 *   - 管理员端：通过 mnbt_register_page('admin', ...) 注册到 admin/plugin.php
 *   - 开通：通过 order.paid 钩子处理 lx=docker 订单，写入 MN_docker_user + 资产表
 */

if (!defined('IN_CRONLITE')) {
	exit;
}

require_once __DIR__ . '/lib/docker_shop.php';

// 自动升级表结构
docker_shop_upgrade_schema();

mnbt_plugin_register('docker_shop', [
	'name' => 'Docker 售卖',
	'description' => 'Docker 容器套餐售卖、自动开通 Docker 账号、资产订单管理',
]);

/* ============================================================
 *  order.paid 钩子：处理 Docker 购买订单
 * ============================================================
 *  支付成功后由 mnbt_pay_settle_order() 触发。
 *  1. 检查订单是否已处理（防重复）
 *  2. 标记订单为 paid
 *  3. 调用 docker_shop_open_account() 开通 Docker 账号
 */
mnbt_add_action('order.paid', function ($order_row, $ctx = []) {
	if (!is_array($order_row)) {
		return;
	}
	if (($order_row['lx'] ?? '') !== 'docker') {
		return;
	}
	$order_no = (string)($order_row['ddh'] ?? '');
	if ($order_no === '') {
		return;
	}

	// 防重复：检查该订单号对应的 docker 订单是否已存在
	$docker_order = docker_shop_order_get_by_no($order_no);
	if (!$docker_order) {
		// 可能是直接走支付完成的订单，但没有 docker 订单记录，跳过
		return;
	}
	// 已开通/已失败，跳过
	if (in_array($docker_order['status'], ['opened', 'failed', 'paid'], true)) {
		return;
	}

	// 标记为 paid，然后开通
	docker_shop_order_set_status((int)$docker_order['id'], 'paid', '支付完成');
	$result = docker_shop_open_account((int)$docker_order['id']);
	if (!$result['ok']) {
		@error_log('[docker_shop] open failed order=' . $order_no . ' : ' . ($result['msg'] ?? ''));
	}
}, 20);

/* ============================================================
 *  用户端页面路由
 * ============================================================ */

// 售卖页（套餐列表）
mnbt_register_route('GET', '/docker-shop', function ($params, $ctx) {
	docker_shop_require_user();
	$plans = docker_shop_plan_list_active();

	docker_shop_render('shop', [
		'page_title' => 'Docker 商城',
		'plans' => $plans,
	]);
});

// 下单页
mnbt_register_route('GET', '/docker-shop/order/{plan_id}', function ($params, $ctx) {
	docker_shop_require_user();
	$plan_id = (int)($params['plan_id'] ?? 0);
	$plan = docker_shop_plan_get($plan_id);
	if (!$plan || $plan['status'] !== 'active') {
		http_response_code(404);
		echo '套餐不存在或已下架';
		return;
	}
	$methods = function_exists('mnbt_get_enabled_payment_methods') ? mnbt_get_enabled_payment_methods() : [];

	docker_shop_render('order', [
		'page_title' => '购买：' . $plan['name'],
		'plan' => $plan,
		'methods' => $methods,
	]);
});

// 我的 Docker 资产
mnbt_register_route('GET', '/docker-shop/assets', function ($params, $ctx) {
	$user = docker_shop_require_user();
	$assets = docker_shop_asset_list_by_user((int)$user['id']);

	docker_shop_render('assets', [
		'page_title' => '我的 Docker',
		'assets' => $assets,
	]);
});

// 我的 Docker 订单
mnbt_register_route('GET', '/docker-shop/orders', function ($params, $ctx) {
	$user = docker_shop_require_user();
	$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
	$orders = docker_shop_order_list_by_user((int)$user['id'], $page, 15);

	docker_shop_render('orders', [
		'page_title' => 'Docker 订单',
		'orders' => $orders,
	]);
});

/* ============================================================
 *  用户端 API 路由（SPA 数据接口）
 * ============================================================ */

// 售卖套餐列表
mnbt_register_route('GET', '/docker-shop/api/plans', function ($params, $ctx) {
	$rows = docker_shop_plan_list_active();
	$plans = [];
	foreach ($rows as $p) {
		$plans[] = docker_shop_plan_to_api($p);
	}
	docker_shop_json('ok', ['plans' => $plans]);
});

// 售卖套餐详情 + 支付方式
mnbt_register_route('GET', '/docker-shop/api/plan/{plan_id}', function ($params, $ctx) {
	$plan_id = (int)($params['plan_id'] ?? 0);
	$plan = docker_shop_plan_get($plan_id);
	if (!$plan || $plan['status'] !== 'active') {
		docker_shop_json('套餐不存在或已下架');
		return;
	}
	$methods = function_exists('mnbt_get_enabled_payment_methods') ? mnbt_get_enabled_payment_methods() : [];
	docker_shop_json('ok', [
		'plan' => docker_shop_plan_to_api($plan),
		'methods' => $methods,
	]);
});

// 我的 Docker 资产
mnbt_register_route('GET', '/docker-shop/api/assets', function ($params, $ctx) {
	$user = function_exists('user_info_auth_current') ? user_info_auth_current() : null;
	if (!$user) {
		docker_shop_json('not_login', ['logged_in' => false]);
		return;
	}
	$assets = docker_shop_asset_list_by_user((int)$user['id']);
	docker_shop_json('ok', ['logged_in' => true, 'assets' => $assets]);
});

// 我的 Docker 订单（分页）
mnbt_register_route('GET', '/docker-shop/api/orders', function ($params, $ctx) {
	$user = function_exists('user_info_auth_current') ? user_info_auth_current() : null;
	if (!$user) {
		docker_shop_json('not_login', ['logged_in' => false]);
		return;
	}
	$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
	$per = isset($_GET['per_page']) ? max(1, min(100, (int)$_GET['per_page'])) : 15;
	$orders = docker_shop_order_list_by_user((int)$user['id'], $page, $per);
	docker_shop_json('ok', ['logged_in' => true, 'orders' => $orders]);
});

// 可用支付方式（下单页数据接口）
mnbt_register_route('GET', '/docker-shop/api/methods', function ($params, $ctx) {
	$methods = function_exists('mnbt_get_enabled_payment_methods') ? mnbt_get_enabled_payment_methods() : [];
	docker_shop_json('ok', ['methods' => $methods]);
});

// 创建购买订单 → 调用支付插件
mnbt_register_route('POST', '/docker-shop/api/create_order', function ($params, $ctx) {
	global $DB, $date, $siteurl;

	$user = docker_shop_require_user();
	$user_id = (int)$user['id'];

	$plan_id = (int)($_POST['plan_id'] ?? 0);
	$period = isset($_POST['period']) ? trim($_POST['period']) : 'month';
	$type = isset($_POST['type']) ? trim($_POST['type']) : '';

	// 校验套餐
	$plan = docker_shop_plan_get($plan_id);
	if (!$plan || $plan['status'] !== 'active') {
		docker_shop_json('套餐不存在或已下架');
	}
	$periods = docker_shop_periods();
	if (!isset($periods[$period])) {
		docker_shop_json('请选择有效的购买周期');
	}
	$enabled = docker_shop_plan_enabled_periods($plan);
	if (!in_array($period, $enabled, true)) {
		docker_shop_json('该套餐不支持此购买周期');
	}
	$price_field = docker_shop_period_price_field($period);
	$amount_cents = $price_field ? (int)($plan[$price_field] ?? 0) : 0;
	if ($amount_cents < 0) {
		docker_shop_json('该套餐此周期价格异常');
	}
	// 节点/配额套餐已由套餐固定配置，无需前端传入
	$node = (int)($plan['node'] ?? 0);
	if ($node <= 0 || !docker_shop_node_get($node)) {
		docker_shop_json('套餐未配置有效开通节点，请联系管理员');
	}
	if ((int)($plan['base_plan_id'] ?? 0) <= 0) {
		docker_shop_json('套餐未配置有效配额套餐，请联系管理员');
	}
	// 非 0 元订单校验支付方式；0 元订单无需选择支付方式
	if ($amount_cents > 0) {
		if ($type === '' || !function_exists('mnbt_pay_parse_type') || !mnbt_pay_parse_type($type)) {
			docker_shop_json('请选择有效的支付方式');
		}
	}

	// 创建 docker 订单（节点从套餐读取）
	$create = docker_shop_order_create($user, $plan, $period);
	if (empty($create['ok'])) {
		docker_shop_json($create['msg'] ?? '创建订单失败');
	}
	$order_no = $create['order_no'];
	$docker_order_id = (int)$create['order_id'];

	// 0 元免费套餐：直接标记 paid 并开通，无需创建 MN_dd 和调支付网关
	if ($amount_cents === 0) {
		docker_shop_order_set_status($docker_order_id, 'paid', '免费套餐直接开通');
		$open = docker_shop_open_account($docker_order_id);
		if (!$open['ok']) {
			docker_shop_json('开通失败：' . ($open['msg'] ?? '未知错误'));
		}
		docker_shop_json('开通成功', [
			'redirect' => docker_shop_url('docker-shop/assets'),
			'docker_username' => $open['docker_username'],
			'docker_password' => $open['docker_password'],
		]);
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
		'order_id' => $docker_order_id,
	], 256);
	$ip = $_SERVER["REMOTE_ADDR"] ?? '127.0.0.1';

	$row1 = $DB->get_row_prepare("SELECT * FROM MN_dd WHERE 1 order by id desc limit 1");
	$dd_id = $row1 ? ((int)$row1['id'] + 1) : 1;
	$ok = $DB->query_prepare(
		"INSERT INTO MN_dd (id, cs, date, zffs, je, ddh, lx, qk, ip) VALUES (?,?,?,?,?,?,?,?,?)",
		[$dd_id, $cs, $date, $type, $amount_yuan, $order_no, 'docker', 'false', $ip]
	);
	if (!$ok) {
		// 回滚 docker 订单状态
		docker_shop_order_set_status($docker_order_id, 'cancelled', '支付订单创建失败');
		docker_shop_json('支付订单创建失败，请稍后重试');
	}

	// 分发到支付插件
	$period_label = $periods[$period]['label'];
	$order_context = [
		'out_trade_no' => $order_no,
		'name' => '购买 Docker：' . $plan['name'] . '（' . $period_label . '）',
		'money' => $amount_yuan,
		'type' => $type,
		'siteurl' => $siteurl,
		'pay_lx' => 'docker',
	];

	$html = mnbt_pay_dispatch_gateway($type, $order_context);
	if ($html === false) {
		docker_shop_order_set_status($docker_order_id, 'cancelled', '支付方式不可用');
		docker_shop_json('支付方式不可用，请检查支付插件是否已启用');
	}

	docker_shop_json('正在跳转到支付页面', ['html' => $html, 'order_no' => $order_no]);
});

// 重置 Docker 账号密码
mnbt_register_route('POST', '/docker-shop/api/reset_password', function ($params, $ctx) {
	$user = function_exists('user_info_auth_current') ? user_info_auth_current() : null;
	if (!$user) {
		docker_shop_json('not_login', ['logged_in' => false]);
		return;
	}
	$asset_id = (int)($_POST['asset_id'] ?? 0);
	// 校验资产归属
	$asset = null;
	foreach (docker_shop_asset_list_by_user((int)$user['id']) as $a) {
		if ((int)$a['id'] === $asset_id) {
			$asset = $a;
			break;
		}
	}
	if (!$asset) {
		docker_shop_json('资产不存在');
	}
	$result = docker_shop_reset_password($asset_id);
	if (!$result['ok']) {
		docker_shop_json($result['msg']);
	}
	docker_shop_json('重置成功', ['password' => $result['password']]);
});

// 刷新容器状态
mnbt_register_route('POST', '/docker-shop/api/sync_status', function ($params, $ctx) {
	$user = function_exists('user_info_auth_current') ? user_info_auth_current() : null;
	if (!$user) {
		docker_shop_json('not_login', ['logged_in' => false]);
		return;
	}
	$asset_id = (int)($_POST['asset_id'] ?? 0);
	$asset = null;
	foreach (docker_shop_asset_list_by_user((int)$user['id']) as $a) {
		if ((int)$a['id'] === $asset_id) {
			$asset = $a;
			break;
		}
	}
	if (!$asset) {
		docker_shop_json('资产不存在');
	}
	$synced = docker_shop_sync_container_status($asset);
	docker_shop_json('ok', [
		'container_status' => $synced['container_status'] ?? 'none',
		'container_id' => $synced['container_id'] ?? '',
		'disk_usage' => (int)($synced['disk_usage'] ?? 0),
	]);
});

/* ============================================================
 *  管理员端页面注册
 * ============================================================ */

mnbt_register_page('admin', 'plans', 'views/admin/plans.php', '售卖套餐管理');
mnbt_register_page('admin', 'plan_edit', 'views/admin/plan_edit.php', '售卖套餐编辑');
mnbt_register_page('admin', 'orders', 'views/admin/orders.php', '订单管理');
mnbt_register_page('admin', 'assets', 'views/admin/assets.php', '资产管理');

// 侧边栏菜单（三级结构）
mnbt_register_menu('admin', [
	'title' => 'Docker 售卖',
	'icon'  => 'mdi-docker',
	'order' => 61,
	'children' => [
		['title' => '售卖套餐', 'page' => 'plans', 'icon' => 'mdi-package-variant', 'multitabs' => true],
		['title' => '订单管理', 'page' => 'orders', 'icon' => 'mdi-receipt', 'multitabs' => true],
		['title' => '资产管理', 'page' => 'assets', 'icon' => 'mdi-server', 'multitabs' => true],
	],
]);

/* ============================================================
 *  管理员端 AJAX
 * ============================================================ */

// 保存售卖套餐
mnbt_register_ajax('admin', 'p_docker_shop_plan_save', function ($egn, $side) {
	mnbt_plugin_require_admin();
	$data = [
		'id' => (int)($_POST['id'] ?? 0),
		'name' => $_POST['name'] ?? '',
		'description' => $_POST['description'] ?? '',
		'category' => $_POST['category'] ?? '',
		'node' => (int)($_POST['node'] ?? 0),
		'base_plan_id' => (int)($_POST['base_plan_id'] ?? 0),
		'enabled_periods' => isset($_POST['enabled_periods']) && is_array($_POST['enabled_periods']) ? $_POST['enabled_periods'] : [],
		'status' => $_POST['status'] ?? 'active',
		'sort' => (int)($_POST['sort'] ?? 50),
	];
	foreach (docker_shop_periods() as $p => $cfg) {
		$field = docker_shop_period_price_field($p);
		$data[$field] = (int)round((float)($_POST['price'][$p] ?? 0) * 100);
	}
	$r = docker_shop_plan_save($data);
	if ($r === true) {
		json_exit_success('保存成功');
	}
	json_exit_error($r);
});

// 删除售卖套餐
mnbt_register_ajax('admin', 'p_docker_shop_plan_delete', function ($egn, $side) {
	mnbt_plugin_require_admin();
	$plan_id = (int)($_POST['plan_id'] ?? 0);
	if ($plan_id <= 0 || !docker_shop_plan_delete($plan_id)) {
		json_exit_error('删除失败');
	}
	json_exit_success('删除成功');
});
