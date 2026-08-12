<?php
/**
 * zjmfmanager_reserve 插件 - 主入口
 *
 * 功能：以代理商身份分销魔方财务（cube_finance）产品。
 * 依赖：user_info 插件（认证）、balance 插件（余额支付/退款）。
 * 架构：
 *   - 上游服务：lib/upstream.php（ZjmfUpstream，封装全部魔方财务 API 调用）
 *   - 辅助函数：lib/zjmf.php（URL/渲染/金额/加价计算 + 数据表操作 + 开通编排）
 *   - 用户端：通过 P2 路由注册 /reserve/* 路径
 *   - 管理员端：通过 mnbt_register_page('admin', ...) 注册到 plugin.php
 *   - 开通：通过 order.paid 钩子处理 lx=zjmf 订单，调用上游开通，失败自动退款
 */

if (!defined('IN_CRONLITE')) {
	exit;
}

require_once __DIR__ . '/lib/zjmf.php';
require_once __DIR__ . '/lib/upstream.php';

mnbt_plugin_register('zjmfmanager_reserve', [
	'name'        => '魔方财务代理分销',
	'description' => '商品同步加价、本地余额购买、代理商直通开通、主机管理与升降级',
]);

/* ============================================================
 *  order.paid 钩子：处理购买 / 升级订单
 * ============================================================
 *  支付成功后由 mnbt_pay_settle_order() 触发。
 *  1. 按 lx=zjmf 过滤（仅处理本插件创建的 MN_dd 订单）
 *  2. 按订单号找到本地业务订单
 *  3. 防重复：已支付/已开通/已失败直接跳过
 *  4. 标记 paid 后按 action 分流处理（buy→开通；升级→后续里程碑）
 */
mnbt_add_action('order.paid', function ($order_row, $ctx = []) {
	if (!is_array($order_row)) {
		return;
	}
	if (($order_row['lx'] ?? '') !== ZJMF_LX) {
		return;
	}
	$order_no = (string)($order_row['ddh'] ?? '');
	if ($order_no === '') {
		return;
	}

	// 业务订单号前缀校验，防止串单
	if (strpos($order_no, ZJMF_ORDER_PREFIX) !== 0) {
		return;
	}

	$order = zjmf_order_get_by_no($order_no);
	if (!$order) {
		return;
	}
	// 已支付 / 已开通 / 已失败，跳过（防重复处理）
	if (in_array($order['status'], ['paid', 'opened', 'failed'], true)) {
		return;
	}

	zjmf_order_set_status((int)$order['id'], 'paid', '支付完成');

	// 仅 buy 动作本期直接开通；升级订单由升级接口联动处理
	if ($order['action'] === 'buy') {
		$result = zjmf_open_host((int)$order['id']);
		if (!$result['ok']) {
			@error_log('[zjmfmanager_reserve] open failed order=' . $order_no
				. ' : ' . ($result['msg'] ?? ''));
		}
	}
}, 20);

/* ============================================================
 *  管理员端页面注册
 * ============================================================ */

mnbt_register_page('admin', 'suppliers', 'views/admin/suppliers.php', '供应商管理');
mnbt_register_page('admin', 'products', 'views/admin/products.php', '商品管理');
mnbt_register_page('admin', 'orders', 'views/admin/orders.php', '订单管理');
mnbt_register_page('admin', 'hosts', 'views/admin/hosts.php', '主机管理');
mnbt_register_page('admin', 'logs', 'views/admin/logs.php', '操作日志');

// 侧边栏菜单（三级结构）
mnbt_register_menu('admin', [
	'title'    => '魔方财务分销',
	'icon'     => 'mdi-currency-cny',
	'order'    => 60,
	'children' => [
		['title' => '供应商管理', 'page' => 'suppliers', 'icon' => 'mdi-settings', 'multitabs' => true],
		['title' => '商品管理', 'page' => 'products', 'icon' => 'mdi-package-variant', 'multitabs' => true],
		['title' => '订单管理', 'page' => 'orders', 'icon' => 'mdi-receipt', 'multitabs' => true],
		['title' => '主机管理', 'page' => 'hosts', 'icon' => 'mdi-server', 'multitabs' => true],
		['title' => '操作日志', 'page' => 'logs', 'icon' => 'mdi-clipboard-text', 'multitabs' => true],
	],
]);

/* ============================================================
 *  管理员端 AJAX
 * ============================================================ */

// 保存供应商（新增/编辑，密码留空不修改）
mnbt_register_ajax('admin', 'p_zjmf_admin_save_supplier', function () {
	mnbt_plugin_require_admin();
	global $DB, $date;

	$id = (int)($_POST['id'] ?? 0);
	$name = trim((string)($_POST['name'] ?? ''));
	$url = trim((string)($_POST['api_url'] ?? ''));
	$username = trim((string)($_POST['api_username'] ?? ''));
	$password = (string)($_POST['api_password'] ?? '');
	$timeout = max(5, min(120, (int)($_POST['api_timeout'] ?? 30)));
	$markupType = in_array((string)($_POST['markup_type'] ?? ''), ['0', '1'], true)
		? (int)$_POST['markup_type'] : 0;
	$markupValue = max(0, (int)($_POST['markup_value'] ?? 0));
	$status = in_array((string)($_POST['status'] ?? ''), ['0', '1'], true)
		? (int)$_POST['status'] : 1;
	$sort = max(0, (int)($_POST['sort'] ?? 0));
	$remark = trim((string)($_POST['remark'] ?? ''));

	if ($name === '') {
		json_exit_error('请填写供应商名称');
	}
	if (mb_strlen($name) > 50) {
		json_exit_error('供应商名称过长');
	}
	if ($url !== '' && !preg_match('#^https?://#i', $url)) {
		json_exit_error('站点 URL 必须以 http:// 或 https:// 开头');
	}
	if (mb_strlen($url) > 255) {
		json_exit_error('站点 URL 过长');
	}
	if ($url !== '' && $username === '') {
		json_exit_error('请填写 API 用户名');
	}
	if ($password !== '' && mb_strlen($password) > 255) {
		json_exit_error('API 密钥过长');
	}

	$now = $date ?: date('Y-m-d H:i:s');
	if ($id > 0) {
		$existing = zjmf_supplier_get($id);
		if (!$existing) {
			json_exit_error('供应商不存在');
		}
		$sql = "UPDATE MN_plugin_zjmf_supplier
		        SET name=?, api_url=?, api_username=?, api_timeout=?,
		            markup_type=?, markup_value=?, status=?, sort=?,
		            remark=?, updated_at=?";
		$args = [$name, $url, $username, $timeout, $markupType, $markupValue,
		         $status, $sort, $remark, $now];
		if ($password !== '') {
			$sql .= ", api_password=?";
			$args[] = $password;
		}
		$sql .= " WHERE id=?";
		$args[] = $id;
		$ok = $DB->query_prepare($sql, $args);
		if (!$ok) {
			json_exit_error('保存失败');
		}
	} else {
		$ok = $DB->query_prepare(
			"INSERT INTO MN_plugin_zjmf_supplier
			 (name, api_url, api_username, api_password, api_timeout,
			  markup_type, markup_value, status, sort, remark, created_at, updated_at)
			 VALUES (?,?,?,?,?,?,?,?,?,?,?,?)",
			[$name, $url, $username, $password, $timeout, $markupType,
			 $markupValue, $status, $sort, $remark, $now, $now]
		);
		if (!$ok) {
			json_exit_error('保存失败');
		}
	}
	json_exit_success('已保存');
});

// 启用/停用供应商
mnbt_register_ajax('admin', 'p_zjmf_admin_toggle_supplier', function () {
	mnbt_plugin_require_admin();
	global $DB, $date;

	$id = (int)($_POST['id'] ?? 0);
	$supplier = zjmf_supplier_get($id);
	if (!$supplier) {
		json_exit_error('供应商不存在');
	}
	$newStatus = (int)$supplier['status'] === 1 ? 0 : 1;
	$now = $date ?: date('Y-m-d H:i:s');
	$DB->query_prepare(
		"UPDATE MN_plugin_zjmf_supplier SET status=?, updated_at=? WHERE id=?",
		[$newStatus, $now, $id]
	);
	json_exit_success($newStatus === 1 ? '已启用' : '已停用');
});

// 删除供应商（有商品/订单/主机时拒绝）
mnbt_register_ajax('admin', 'p_zjmf_admin_delete_supplier', function () {
	mnbt_plugin_require_admin();
	$id = (int)($_POST['id'] ?? 0);
	$result = zjmf_supplier_delete($id);
	if (empty($result['ok'])) {
		json_exit_error($result['msg'] ?? '删除失败');
	}
	json_exit_success($result['msg'] ?? '已删除');
});

// 连通测试（登录 + 商品列表）
mnbt_register_ajax('admin', 'p_zjmf_admin_test_supplier', function () {
	mnbt_plugin_require_admin();
	$supplier = zjmf_supplier_get((int)($_POST['id'] ?? 0));
	if (!$supplier) {
		json_exit_error('供应商不存在');
	}
	$result = ZjmfUpstream::testConnection($supplier);
	if (empty($result['ok'])) {
		json_exit_error($result['msg'] ?? '连接失败');
	}
	json_exit_success($result['msg'] ?? '连接成功');
});

// 拉取供应商上游商品列表（供同步弹窗选择）
mnbt_register_ajax('admin', 'p_zjmf_admin_upstream_products', function () {
	mnbt_plugin_require_admin();
	$supplier = zjmf_supplier_get((int)($_POST['id'] ?? 0));
	if (!$supplier) {
		json_exit_error('供应商不存在');
	}
	$result = ZjmfUpstream::upstreamProducts($supplier);
	if (empty($result['ok'])) {
		json_exit_error($result['msg'] ?? '拉取失败');
	}
	// 标注已同步商品，便于弹窗勾选时识别
	$existing = [];
	$rows = zjmf_product_list_all();
	foreach ($rows as $p) {
		if ((int)$p['supplier_id'] === (int)$supplier['id']) {
			$existing[(int)$p['up_product_id']] = true;
		}
	}
	$list = [];
	foreach (($result['data']['list'] ?? []) as $item) {
		$list[] = [
			'id'          => (int)($item['id'] ?? 0),
			'name'        => (string)($item['name'] ?? ''),
			'description' => (string)($item['description'] ?? ''),
			'synced'      => isset($existing[(int)($item['id'] ?? 0)]),
		];
	}
	json_exit_success('拉取成功', [
		'currency' => (string)($result['data']['currency_code'] ?? ''),
		'list'     => $list,
	]);
});

// 按所选供应商 + 勾选商品 ID 列表同步
mnbt_register_ajax('admin', 'p_zjmf_admin_sync_products', function () {
	mnbt_plugin_require_admin();
	$supplier = zjmf_supplier_get((int)($_POST['supplier_id'] ?? 0));
	if (!$supplier) {
		json_exit_error('请先选择供应商');
	}
	$upIds = [];
	if (isset($_POST['up_ids']) && is_array($_POST['up_ids'])) {
		foreach ($_POST['up_ids'] as $v) {
			$v = (int)$v;
			if ($v > 0) {
				$upIds[] = $v;
			}
		}
	}
	$result = ZjmfUpstream::syncProducts($supplier, $upIds);
	if (empty($result['ok'])) {
		json_exit_error($result['msg'] ?? '同步失败');
	}
	json_exit_success($result['msg'] ?? '同步完成');
});

// 手动添加商品（供应商 + 上游商品 ID + 名称 + 描述）
mnbt_register_ajax('admin', 'p_zjmf_admin_add_product', function () {
	mnbt_plugin_require_admin();
	global $DB, $date;

	$supplier = zjmf_supplier_get((int)($_POST['supplier_id'] ?? 0));
	if (!$supplier || (int)$supplier['status'] !== 1) {
		json_exit_error('供应商不存在或已停用');
	}
	$upId = (int)($_POST['up_product_id'] ?? 0);
	$name = trim((string)($_POST['name'] ?? ''));
	$description = trim((string)($_POST['description'] ?? ''));
	if ($upId <= 0) {
		json_exit_error('请填写上游商品 ID');
	}
	if ($name === '') {
		json_exit_error('请填写商品名称');
	}
	if (mb_strlen($name) > 100) {
		json_exit_error('商品名称过长');
	}
	if (zjmf_product_get_by_up((int)$supplier['id'], $upId)) {
		json_exit_error('该供应商下已存在此上游商品');
	}

	$now = $date ?: date('Y-m-d H:i:s');
	$ok = $DB->query_prepare(
		"INSERT INTO MN_plugin_zjmf_product
		 (supplier_id, up_product_id, name, description, currency,
		  agent_price_cents, cycles, status, sort, synced_at, created_at, updated_at)
		 VALUES (?,?,?,?,?,?,?,?,?,?,?,?)",
		[(int)$supplier['id'], $upId, $name, $description, '', 0,
		 '[]', 0, 50, $now, $now, $now]
	);
	if (!$ok) {
		json_exit_error('商品创建失败');
	}

	// 立即拉取代理价与各周期价格（失败不阻断，商品标记待同步）
	$result = ZjmfUpstream::syncOneProductBySupplier($supplier, $upId);
	if (empty($result['ok'])) {
		json_exit_error('商品已添加，但价格同步失败：' . ($result['msg'] ?? ''));
	}
	json_exit_success('商品已添加，价格同步完成');
});

// 保存商品加价/排序/状态
mnbt_register_ajax('admin', 'p_zjmf_admin_save_product', function () {
	mnbt_plugin_require_admin();

	$id = (int)($_POST['id'] ?? 0);
	$product = zjmf_product_get($id);
	if (!$product) {
		json_exit_error('商品不存在');
	}

	$markupType = in_array((string)($_POST['markup_type'] ?? ''), ['0', '1'], true)
		? (int)$_POST['markup_type'] : 0;
	$markupValue = max(0, (int)($_POST['markup_value'] ?? 0));
	$sort = max(0, (int)($_POST['sort'] ?? 50));
	$status = in_array((string)($_POST['status'] ?? ''), ['0', '1'], true)
		? (int)$_POST['status'] : 0;

	global $DB, $date;
	$now = $date ?: date('Y-m-d H:i:s');
	$ok = $DB->query_prepare(
		"UPDATE MN_plugin_zjmf_product
		 SET markup_type=?, markup_value=?, sort=?, status=?, updated_at=?
		 WHERE id=?",
		[$markupType, $markupValue, $sort, $status, $now, $id]
	);
	if (!$ok) {
		json_exit_error('保存失败');
	}
	// 加价变化后重算各周期本地售价
	zjmf_product_recalc_price($id);
	json_exit_success('已保存');
});

// 切换商品上架/下架
mnbt_register_ajax('admin', 'p_zjmf_admin_toggle_product', function () {
	mnbt_plugin_require_admin();

	$id = (int)($_POST['id'] ?? 0);
	$product = zjmf_product_get($id);
	if (!$product) {
		json_exit_error('商品不存在');
	}

	global $DB, $date;
	$now = $date ?: date('Y-m-d H:i:s');
	$newStatus = $product['status'] == 1 ? 0 : 1;
	$DB->query_prepare(
		"UPDATE MN_plugin_zjmf_product SET status=?, updated_at=? WHERE id=?",
		[$newStatus, $now, $id]
	);
	json_exit_success($newStatus == 1 ? '已上架' : '已下架');
});

/* ============================================================
 *  用户端页面路由
 * ============================================================ */

// 商品列表
mnbt_register_route('GET', '/reserve/shop', function ($params, $ctx) {
	zjmf_require_user();
	zjmf_render('shop', [
		'page_title' => '商品选购',
		'products'   => zjmf_product_list_active(),
	]);
});

// 下单页（选周期 + 支付方式）
mnbt_register_route('GET', '/reserve/product/{product_id}', function ($params, $ctx) {
	zjmf_require_user();
	$product_id = (int)($params['product_id'] ?? 0);
	$product = zjmf_product_get($product_id);
	if (!$product || (int)$product['status'] !== 1) {
		http_response_code(404);
		echo '商品不存在或已下架';
		return;
	}
	if (!zjmf_supplier_usable((int)$product['supplier_id'])) {
		http_response_code(404);
		echo '商品所属供应商已停用';
		return;
	}
	$methods = function_exists('mnbt_get_enabled_payment_methods')
		? mnbt_get_enabled_payment_methods() : [];

	zjmf_render('order', [
		'page_title' => '购买：' . $product['name'],
		'product'    => $product,
		'methods'    => $methods,
	]);
});

/* ============================================================
 *  用户端 API 路由
 * ============================================================ */

// 创建购买订单 → 生成 MN_dd → 分发支付网关
mnbt_register_route('POST', '/reserve/api/create_order', function ($params, $ctx) {
	global $DB, $date, $siteurl;

	$user = zjmf_require_user();
	$user_id = (int)$user['id'];

	$product_id = (int)($_POST['product_id'] ?? 0);
	$cycle = isset($_POST['cycle']) ? trim($_POST['cycle']) : '';
	$type = isset($_POST['type']) ? trim($_POST['type']) : '';

	// 校验商品与周期
	$product = zjmf_product_get($product_id);
	if (!$product || (int)$product['status'] !== 1) {
		zjmf_json('商品不存在或已下架');
	}
	if (!zjmf_supplier_usable((int)$product['supplier_id'])) {
		zjmf_json('商品所属供应商已停用，无法下单');
	}
	$cycles = zjmf_product_cycles($product);
	if (!isset($cycles[$cycle])) {
		zjmf_json('请选择有效的购买周期');
	}
	$cycleCfg = $cycles[$cycle];
	$amount_cents = (int)$cycleCfg['price_cents'];
	if ($amount_cents <= 0) {
		zjmf_json('该商品此周期价格异常');
	}
	// 校验支付方式
	if (!function_exists('mnbt_pay_parse_type') || !mnbt_pay_parse_type($type)) {
		zjmf_json('请选择有效的支付方式');
	}

	// 创建本地业务订单
	$create = zjmf_order_create($user, $product, $cycle, $cycleCfg, 'buy', [
		'cost_cents' => (int)$product['agent_price_cents'],
	]);
	if (empty($create['ok'])) {
		zjmf_json($create['msg'] ?? '创建订单失败');
	}
	$order_no = $create['order_no'];
	$order_id = (int)$create['order_id'];

	// 创建 MN_dd 支付订单（lx=zjmf，qk=false 待支付）
	$amount_yuan = (string)round($amount_cents / 100, 2);
	$cs = json_encode([
		'user_id'  => $user_id,
		'order_id' => $order_id,
		'amount'   => $amount_cents,
		'username' => $user['username'],
	], 256);
	$ip = $_SERVER["REMOTE_ADDR"] ?? '127.0.0.1';

	$lastRow = $DB->get_row_prepare("SELECT id FROM MN_dd ORDER BY id DESC LIMIT 1");
	$dd_id = $lastRow ? ((int)$lastRow['id'] + 1) : 1;
	$ok = $DB->query_prepare(
		"INSERT INTO MN_dd (id, cs, date, zffs, je, ddh, lx, qk, ip)
		 VALUES (?,?,?,?,?,?,?,?,?)",
		[$dd_id, $cs, $date, $type, $amount_yuan, $order_no, ZJMF_LX, 'false', $ip]
	);
	if (!$ok) {
		zjmf_order_set_status($order_id, 'cancelled', '支付订单创建失败');
		zjmf_json('支付订单创建失败，请稍后重试');
	}

	// 分发到支付网关（余额插件 / 其他支付插件）
	$order_context = [
		'out_trade_no' => $order_no,
		'name'         => '购买商品：' . $product['name'] . '（' . $cycleCfg['name'] . '）',
		'money'        => $amount_yuan,
		'type'         => $type,
		'siteurl'      => $siteurl,
		'pay_lx'       => ZJMF_LX,
	];
	$html = mnbt_pay_dispatch_gateway($type, $order_context);
	if ($html === false) {
		zjmf_order_set_status($order_id, 'cancelled', '支付方式不可用');
		zjmf_json('支付方式不可用，请检查支付插件是否已启用');
	}

	zjmf_json('正在跳转到支付页面', [
		'html'     => $html,
		'order_no' => $order_no,
	]);
});

// 我的订单
mnbt_register_route('GET', '/reserve/orders', function ($params, $ctx) {
	$user = zjmf_require_user();
	$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
	$orders = zjmf_order_list_by_user((int)$user['id'], $page, 15);

	zjmf_render('orders', [
		'page_title' => '我的订单',
		'orders'     => $orders,
	]);
});

// 我的主机列表
mnbt_register_route('GET', '/reserve/hosts', function ($params, $ctx) {
	$user = zjmf_require_user();
	zjmf_render('hosts', [
		'page_title' => '我的主机',
		'hosts'      => zjmf_host_list_by_user((int)$user['id']),
	]);
});

// 主机详情（实时状态 + 流量 + 操作）
mnbt_register_route('GET', '/reserve/hosts/{host_id}', function ($params, $ctx) {
	$user = zjmf_require_user();
	$host_id = (int)($params['host_id'] ?? 0);
	$host = zjmf_host_get_by_user((int)$user['id'], $host_id);
	if (!$host) {
		http_response_code(404);
		echo '主机不存在';
		return;
	}

	// 实时信息（失败不致命，仅展示缓存）
	$info = ['ok' => false, 'msg' => ''];
	$traffic = ['ok' => false, 'data' => []];
	$supplier = zjmf_supplier_get((int)$host['supplier_id']);
	if ((int)$host['up_host_id'] > 0 && $supplier) {
		$info = ZjmfUpstream::hostInfo($supplier, (int)$host['up_host_id']);
		$traffic = ZjmfUpstream::hostTraffic($supplier, (int)$host['up_host_id']);
		// 拉取成功后同步缓存状态
		if (!empty($info['ok']) && $info['status'] !== $host['status']) {
			zjmf_host_update_cache((int)$host['id'], ['status' => $info['status']]);
			$host['status'] = $info['status'];
		}
	}

	zjmf_render('host', [
		'page_title' => '主机详情：' . $host['name'],
		'host'       => $host,
		'info'       => $info,
		'traffic'    => $traffic,
	]);
});

// 主机操作（开机/关机/重启/重置密码/重装）
mnbt_register_route('POST', '/reserve/api/host_action', function ($params, $ctx) {
	$user = zjmf_require_user();

	$host_id = (int)($_POST['host_id'] ?? 0);
	$action = isset($_POST['action']) ? trim($_POST['action']) : '';

	$host = zjmf_host_get_by_user((int)$user['id'], $host_id);
	if (!$host) {
		zjmf_json('主机不存在');
	}
	if ((int)$host['up_host_id'] <= 0) {
		zjmf_json('该主机缺少上游主机 ID，无法执行操作');
	}
	if (!zjmf_supplier_usable((int)$host['supplier_id'])) {
		zjmf_json('供应商已停用，无法执行操作');
	}
	$supplier = zjmf_supplier_get((int)$host['supplier_id']);

	$func = zjmf_action_func($action);
	if ($func === '') {
		zjmf_json('不支持的操作');
	}

	// 重置密码需要新密码
	$extra = [];
	if ($action === 'reset_password') {
		$password = trim((string)($_POST['password'] ?? ''));
		if ($password === '') {
			zjmf_json('请输入新密码');
		}
		$extra['password'] = $password;
	}

	$result = ZjmfUpstream::hostAction($supplier, (int)$host['up_host_id'], $func, $extra);
	$hostOrder = zjmf_order_get((int)$host['order_id']);
	$orderNo = $hostOrder ? $hostOrder['order_no'] : '';
	zjmf_log((int)$user['id'], $orderNo, 'host_action:' . $action,
		empty($result['ok']) ? 'failed' : 'success', $result['msg'] ?? '',
		(int)$host['supplier_id']);

	if (empty($result['ok'])) {
		zjmf_json($result['msg'] ?? '操作失败');
	}

	// 操作成功：更新缓存状态与密码
	$status = zjmf_action_status($action);
	if ($status !== '') {
		zjmf_host_update_cache((int)$host['id'], ['status' => $status]);
	}
	if ($action === 'reset_password' && $extra['password'] !== '') {
		global $DB, $date;
		$now = $date ?: date('Y-m-d H:i:s');
		$DB->query_prepare(
			"UPDATE MN_plugin_zjmf_host SET password=?, updated_at=? WHERE id=?",
			[zjmf_encrypt($extra['password']), $now, (int)$host['id']]
		);
	}

	zjmf_json('ok', ['msg' => '操作成功']);
});

/* ============================================================
 *  升级（配置升级 / 产品升降级）
 * ============================================================ */

// 升级页（kind=config|product）
mnbt_register_route('GET', '/reserve/hosts/{host_id}/upgrade', function ($params, $ctx) {
	$user = zjmf_require_user();
	$host_id = (int)($params['host_id'] ?? 0);
	$kind = ($_GET['kind'] ?? 'config') === 'product' ? 'product' : 'config';

	$host = zjmf_host_get_by_user((int)$user['id'], $host_id);
	if (!$host) {
		http_response_code(404);
		echo '主机不存在';
		return;
	}
	if ((int)$host['up_host_id'] <= 0) {
		echo '该主机缺少上游主机 ID，无法升级';
		return;
	}
	if (!zjmf_supplier_usable((int)$host['supplier_id'])) {
		echo '供应商已停用，无法升级';
		return;
	}
	$supplier = zjmf_supplier_get((int)$host['supplier_id']);

	$options = ZjmfUpstream::upgradeOptions($supplier, (int)$host['up_host_id'], $kind);

	zjmf_render('upgrade', [
		'page_title' => '升级：' . $host['name'],
		'host'       => $host,
		'kind'       => $kind,
		'options'    => $options,
	]);
});

// 升级接口（preview=1 试算差额；preview=0 确认扣款并提交）
mnbt_register_route('POST', '/reserve/api/upgrade', function ($params, $ctx) {
	$user = zjmf_require_user();

	$host_id = (int)($_POST['host_id'] ?? 0);
	$kind = ($_POST['kind'] ?? 'config') === 'product' ? 'product' : 'config';
	$isPreview = (($_POST['preview'] ?? '') === '1');

	$host = zjmf_host_get_by_user((int)$user['id'], $host_id);
	if (!$host) {
		zjmf_json('主机不存在');
	}
	if ((int)$host['up_host_id'] <= 0) {
		zjmf_json('该主机缺少上游主机 ID，无法升级');
	}
	if (!zjmf_supplier_usable((int)$host['supplier_id'])) {
		zjmf_json('供应商已停用，无法升级');
	}
	$supplier = zjmf_supplier_get((int)$host['supplier_id']);

	// 构造升级选择参数
	if ($kind === 'config') {
		$raw = (string)($_POST['config_json'] ?? '');
		$decoded = json_decode($raw, true);
		if (!is_array($decoded) || $decoded === []) {
			zjmf_json('请选择配置项');
		}
		$selection = ['configoption' => $decoded];
	} else {
		$newpid = (int)($_POST['newpid'] ?? 0);
		$billingcycle = trim((string)($_POST['billingcycle'] ?? ''));
		if ($newpid <= 0 || $billingcycle === '') {
			zjmf_json('请选择目标产品与周期');
		}
		$selection = ['newpid' => $newpid, 'billingcycle' => $billingcycle];
	}

	// 试算差额
	$preview = ZjmfUpstream::upgradePreview($supplier, $kind, (int)$host['up_host_id'], $selection);
	if (empty($preview['ok'])) {
		zjmf_json($preview['msg'] ?? '试算失败');
	}
	$amount_cents = (int)($preview['price_cents'] ?? 0);
	if ($isPreview) {
		zjmf_json('ok', [
			'price_cents' => $amount_cents,
			'price'       => zjmf_format_cents($amount_cents),
		]);
	}

	// 防重复：同一主机存在未完成升级单则拦截
	global $DB;
	$dup = $DB->get_row_prepare(
		"SELECT id FROM MN_plugin_zjmf_order
		 WHERE host_id=? AND action=? AND status IN ('pending','paid')
		 LIMIT 1",
		[(int)$host['id'], 'upgrade_' . $kind]
	);
	if ($dup) {
		zjmf_json('该主机已有未完成的升级订单');
	}

	$action = 'upgrade_' . $kind;
	$orderParams = json_encode($selection, JSON_UNESCAPED_UNICODE);

	// 创建升级订单
	$create = zjmf_upgrade_order_create($user, $host, $action, $amount_cents, $orderParams);
	if (empty($create['ok'])) {
		zjmf_json($create['msg'] ?? '创建升级订单失败');
	}
	$order_id = (int)$create['order_id'];
	$order_no = $create['order_no'];

	// 扣余额（差额为 0 时跳过）
	if ($amount_cents > 0) {
		$deducted = function_exists('balance_deduct')
			&& balance_deduct((int)$user['id'], $amount_cents, 'consume',
				$order_no, '升级扣款');
		if (!$deducted) {
			zjmf_order_set_status($order_id, 'failed', '余额不足');
			zjmf_json('余额不足，无法完成升级');
		}
	}

	// 提交上游
	$submit = ZjmfUpstream::upgradeSubmit($supplier, $kind, (int)$host['up_host_id'], $selection);
	if (empty($submit['ok'])) {
		$msg = (string)($submit['msg'] ?? '升级提交失败');
		zjmf_order_set_status($order_id, 'failed', $msg);
		// 原路退回余额
		if ($amount_cents > 0 && function_exists('balance_add')) {
			balance_add((int)$user['id'], $amount_cents, 'refund',
				$order_no, '升级失败自动退款');
		}
		zjmf_log((int)$user['id'], $order_no, $action, 'failed',
			json_encode(['msg' => $msg], JSON_UNESCAPED_UNICODE),
			(int)$host['supplier_id']);
		zjmf_json($msg);
	}

	// 升级成功：回填订单 + 更新主机缓存
	zjmf_order_fill_opened($order_id, (int)($submit['up_order_id'] ?? 0),
		(int)$host['up_host_id'], (string)$host['username']);
	zjmf_order_set_status($order_id, 'opened', '升级完成');

	$cache = [];
	if ($kind === 'product') {
		$cache['up_product_id'] = (int)($_POST['newpid'] ?? 0);
	}
	$cache['cycle'] = (string)($_POST['billingcycle'] ?? $host['cycle']);
	$cache['renew_date'] = (string)($preview['data']['renew_date'] ?? '');
	zjmf_host_update_cache((int)$host['id'], $cache);

	zjmf_log((int)$user['id'], $order_no, $action, 'success',
		json_encode(['amount_cents' => $amount_cents, 'selection' => $selection],
			JSON_UNESCAPED_UNICODE),
		(int)$host['supplier_id']);

	zjmf_json('ok', ['msg' => '升级成功']);
});

/* ============================================================
 *  管理员端 - 主机操作 AJAX
 * ============================================================ */

// 刷新主机状态（管理员）
mnbt_register_ajax('admin', 'p_zjmf_admin_fetch_host', function () {
	mnbt_plugin_require_admin();

	$id = (int)($_POST['id'] ?? 0);
	$host = zjmf_host_get($id);
	if (!$host) {
		json_exit_error('主机不存在');
	}
	if ((int)$host['up_host_id'] <= 0) {
		json_exit_error('该主机缺少上游主机 ID');
	}
	$supplier = zjmf_supplier_get((int)$host['supplier_id']);
	if (!$supplier) {
		json_exit_error('主机所属供应商不存在');
	}
	$info = ZjmfUpstream::hostInfo($supplier, (int)$host['up_host_id']);
	if (empty($info['ok'])) {
		json_exit_error($info['msg'] ?? '查询失败');
	}
	$cache = ['status' => $info['status']];
	if (isset($info['data']['renewdate']) || isset($info['data']['renew_date'])) {
		$cache['renew_date'] = (string)($info['data']['renew_date']
			?? $info['data']['renewdate'] ?? '');
	}
	zjmf_host_update_cache($id, $cache);
	json_exit_success('已刷新', ['status' => $info['status']]);
});
