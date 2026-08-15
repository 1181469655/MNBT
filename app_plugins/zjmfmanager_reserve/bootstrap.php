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

// 确保插件数据表存在：修复历史版本安装时 install.sql 首段（注释 + CREATE TABLE）
// 被 mnbt_plugin_run_sql_file 整体跳过导致缺表（如 MN_plugin_zjmf_supplier）的问题。
// install.sql 全部为 IF NOT EXISTS 建表，幂等，可安全重复执行。
static $zjmf_tables_ready = false;
if (!$zjmf_tables_ready && function_exists('mnbt_plugin_run_sql_file')) {
	$zjmf_tables_ready = true;
	mnbt_plugin_run_sql_file(__DIR__ . '/install.sql');
}

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
mnbt_register_page('admin', 'assign', 'views/admin/assign.php', '主机指派');
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
		['title' => '主机指派', 'page' => 'assign', 'icon' => 'mdi-gift', 'multitabs' => true],
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

// 查询供应商上游余额（GET /user_info → data.credit）
mnbt_register_ajax('admin', 'p_zjmf_admin_supplier_balance', function () {
	mnbt_plugin_require_admin();
	$supplier = zjmf_supplier_get((int)($_POST['id'] ?? 0));
	if (!$supplier) {
		json_exit_error('供应商不存在');
	}
	$result = ZjmfUpstream::balance($supplier);
	if (empty($result['ok'])) {
		json_exit_error($result['msg'] ?? '获取失败');
	}
	json_exit_success('ok', [
		'credit'   => (string)($result['credit'] ?? ''),
		'currency' => (string)($result['currency'] ?? ''),
	]);
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
			'module'      => ZjmfUpstream::itemModule($item),
			'price'       => ZjmfUpstream::itemPrice($item),
			'synced'      => isset($existing[(int)($item['id'] ?? 0)]),
		];
	}
	json_exit_success('拉取成功', [
		'currency' => (string)($result['data']['currency_code'] ?? ''),
		'list'     => $list,
	]);
});

// 拉取当前账户（代理）已开通的主机列表（产品分配页用）
// 来源：GET host/list，每台主机一行；标注本地已指派状态
mnbt_register_ajax('admin', 'p_zjmf_admin_upstream_owned_products', function () {
	mnbt_plugin_require_admin();
	global $DB;
	$supplier = zjmf_supplier_get((int)($_POST['id'] ?? 0));
	if (!$supplier) {
		json_exit_error('供应商不存在');
	}

	// 分页拉全量主机列表（limit 尽量大，翻页兜底；安全上限防死循环）
	$items = [];
	$sum = 0;
	$maxPage = 1;
	$page = 1;
	$safety = 30;
	while ($page <= $maxPage && $safety-- > 0) {
		$res = ZjmfUpstream::hostList($supplier, ['page' => $page, 'limit' => 100]);
		if (empty($res['ok'])) {
			json_exit_error('拉取失败：' . (string)($res['msg'] ?? ''));
		}
		$data = $res['data'] ?? [];
		foreach (($data['list'] ?? []) as $h) {
			$items[] = $h;
		}
		$sum = (int)($data['sum'] ?? count($items));
		$maxPage = (int)($data['max_page'] ?? 0);
		if ($maxPage <= 0) {
			$maxPage = 1;
		}
		if ($sum > 0 && count($items) >= $sum) {
			break;
		}
		$page++;
	}

	// 本地已指派的主机集合（供应商 + 上游主机ID）
	$assigned = [];
	$rows = $DB->get_all_prepare(
		"SELECT up_host_id FROM MN_plugin_zjmf_host
		 WHERE supplier_id=? AND up_host_id>0",
		[(int)$supplier['id']]
	) ?: [];
	foreach ($rows as $r) {
		$assigned[(int)$r['up_host_id']] = true;
	}

	$list = [];
	foreach ($items as $h) {
		$upId = (int)($h['id'] ?? 0);
		if ($upId <= 0) {
			continue;
		}
		$list[] = [
			'id'          => $upId,
			'domain'      => (string)($h['domain'] ?? ''),
			'productname' => (string)($h['productname'] ?? ''),
			'status'      => (string)($h['domainstatus'] ?? ''),
			'status_desc' => (string)($h['domainstatus_desc'] ?? $h['domainstatus'] ?? ''),
			'dedicatedip' => (string)($h['dedicatedip'] ?? ''),
			'cycle'       => (string)($h['cycle_desc'] ?? $h['billingcycle'] ?? ''),
			'nextdue'     => zjmf_normalize_date((string)($h['nextduedate'] ?? '')),
			'assigned'    => isset($assigned[$upId]),
		];
	}
	json_exit_success('拉取成功', [
		'list' => $list,
	]);
});

// 产品分配：把上游已开通的机器直接指派给指定用户（本地绑定，不调上游购买）
mnbt_register_ajax('admin', 'p_zjmf_admin_assign_host', function () {
	mnbt_plugin_require_admin();
	global $DB;

	$supplier = zjmf_supplier_get((int)($_POST['supplier_id'] ?? 0));
	if (!$supplier || (int)$supplier['status'] !== 1) {
		json_exit_error('供应商不存在或已停用');
	}
	$user_id = (int)($_POST['user_id'] ?? 0);
	$user = $DB->get_row_prepare(
		"SELECT id, username, email FROM MN_plugin_user
		 WHERE id=? AND status=1 LIMIT 1",
		[$user_id]
	);
	if (!$user) {
		json_exit_error('目标用户不存在或已禁用');
	}

	$upHostIds = [];
	if (isset($_POST['up_host_id']) && is_array($_POST['up_host_id'])) {
		foreach ($_POST['up_host_id'] as $v) {
			$v = (int)$v;
			if ($v > 0) {
				$upHostIds[] = $v;
			}
		}
	}
	if ($upHostIds === []) {
		json_exit_error('请至少勾选一台主机');
	}

	// 一次拉取上游主机列表，匹配勾选机器的详情（产品名/周期/状态/到期）
	$upMap = [];
	$res = ZjmfUpstream::hostList($supplier, ['page' => 1, 'limit' => 100]);
	if (!empty($res['ok'])) {
		foreach (($res['data']['list'] ?? []) as $h) {
			$upMap[(int)($h['id'] ?? 0)] = $h;
		}
	}

	$results = [];
	foreach ($upHostIds as $upHostId) {
		// 幂等：该上游主机已指派则跳过
		$exist = $DB->get_row_prepare(
			"SELECT id FROM MN_plugin_zjmf_host
			 WHERE supplier_id=? AND up_host_id=? LIMIT 1",
			[(int)$supplier['id'], $upHostId]
		);
		if ($exist) {
			$results[] = [
				'up_host_id' => $upHostId,
				'domain'     => (string)($upMap[$upHostId]['domain'] ?? ''),
				'ok'         => false,
				'msg'        => '该机器已指派（本地主机 #' . (int)$exist['id'] . '）',
				'host_id'    => (int)$exist['id'],
			];
			continue;
		}

		$upRow = $upMap[$upHostId] ?? [];
		$name = (string)($upRow['productname'] ?? '');
		$cycle = (string)($upRow['cycle_desc'] ?? $upRow['billingcycle'] ?? '');
		$status = (string)($upRow['domainstatus'] ?? '');
		$renew = (string)($upRow['nextduedate'] ?? '');
		$account = '';
		$password = '';
		// 拉取上游主机头信息（账号/密码），失败不阻断
		// 注意：host/header 返回字段为 domainstatus/nextduedate/billingcycle/productname
		$info = ZjmfUpstream::hostInfo($supplier, $upHostId);
		if (!empty($info['ok']) && is_array($info['data'])) {
			$d = $info['data'];
			$account = (string)($d['username'] ?? '');
			$password = (string)($d['password'] ?? '');
			if ($name === '') {
				$name = (string)($d['productname'] ?? $d['name'] ?? '');
			}
			if ($cycle === '') {
				$cycle = (string)($d['billingcycle_desc'] ?? $d['billingcycle'] ?? $d['cycle_desc'] ?? '');
			}
			if ($status === '') {
				$status = (string)($d['domainstatus'] ?? $d['status'] ?? '');
			}
			if ($renew === '') {
				$renew = (string)($d['nextduedate'] ?? $d['renew_date'] ?? '');
			}
		}
		if ($name === '') {
			$name = '上游主机#' . $upHostId;
		}

		// 创建指派订单（记录用，金额 0，直接标记已开通）
		$product = [
			'id'            => 0,
			'supplier_id'   => (int)$supplier['id'],
			'up_product_id' => (int)($upRow['pid'] ?? 0),
			'name'          => $name,
		];
		$cycleCfg = ['name' => $cycle !== '' ? $cycle : 'Monthly', 'price_cents' => 0];
		$order = zjmf_order_create($user, $product, $cycle, $cycleCfg, 'assign', [
			'cost_cents'   => 0,
			'up_host_id'   => $upHostId,
			'order_params' => json_encode([
				'assign_by'   => 'admin',
				'assign_type' => 'existing_host',
			], JSON_UNESCAPED_UNICODE),
		]);
		if (empty($order['ok'])) {
			$results[] = [
				'up_host_id' => $upHostId,
				'domain'     => (string)($upRow['domain'] ?? ''),
				'ok'         => false,
				'msg'        => (string)($order['msg'] ?? '订单创建失败'),
				'host_id'    => 0,
			];
			continue;
		}
		$order_id = (int)$order['order_id'];
		zjmf_order_fill_opened($order_id, 0, $upHostId, $account);
		zjmf_order_set_status($order_id, 'opened', '管理员指派');

		// 写本地主机映射（绑定该上游机器）
		$hostId = zjmf_host_create([
			'supplier_id'   => (int)$supplier['id'],
			'user_id'       => (int)$user['id'],
			'order_id'      => $order_id,
			'up_host_id'    => $upHostId,
			'up_product_id' => (int)($upRow['pid'] ?? 0),
			'name'          => $name,
			'username'      => $account,
			'password'      => $password !== '' ? zjmf_encrypt($password) : '',
			'cycle'         => $cycle,
			'status'        => zjmf_map_upstream_status($status),
			'renew_date'    => zjmf_normalize_date($renew),
		]);
		if ($hostId <= 0) {
			$results[] = [
				'up_host_id' => $upHostId,
				'domain'     => (string)($upRow['domain'] ?? ''),
				'ok'         => false,
				'msg'        => '本地主机映射写入失败',
				'host_id'    => 0,
			];
			continue;
		}

		zjmf_log((int)$user['id'], (string)($order['order_no'] ?? ''), 'assign', 'success',
			json_encode(['up_host_id' => $upHostId, 'assign_by' => 'admin'], JSON_UNESCAPED_UNICODE),
			(int)$supplier['id']);

		$results[] = [
			'up_host_id' => $upHostId,
			'domain'     => (string)($upRow['domain'] ?? ''),
			'ok'         => true,
			'msg'        => '指派成功',
			'host_id'    => $hostId,
		];
	}
	json_exit_success('处理完成', ['results' => $results]);
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

// 产品分配：按关键词搜索可分配用户（user_info 独立用户表）
mnbt_register_ajax('admin', 'p_zjmf_admin_search_users', function () {
	mnbt_plugin_require_admin();
	global $DB;
	$kw = trim((string)($_POST['keyword'] ?? ''));
	$list = [];
	if ($kw !== '') {
		$like = '%' . $kw . '%';
		$list = $DB->get_all_prepare(
			"SELECT id, username, email, qq, created_at
			 FROM MN_plugin_user
			 WHERE status=1 AND (username LIKE ? OR email LIKE ? OR id=?)
			 ORDER BY id DESC LIMIT 20",
			[$like, $like, (int)$kw]
		) ?: [];
	}
	json_exit_success('ok', ['list' => $list]);
});

// 产品分配：同步勾选的上游商品并直接给指定用户开通（管理员代开，金额 0）
mnbt_register_ajax('admin', 'p_zjmf_admin_assign_open', function () {
	mnbt_plugin_require_admin();
	global $DB;

	$supplier = zjmf_supplier_get((int)($_POST['supplier_id'] ?? 0));
	if (!$supplier || (int)$supplier['status'] !== 1) {
		json_exit_error('供应商不存在或已停用');
	}
	$user_id = (int)($_POST['user_id'] ?? 0);
	$user = $DB->get_row_prepare(
		"SELECT id, username, email FROM MN_plugin_user
		 WHERE id=? AND status=1 LIMIT 1",
		[$user_id]
	);
	if (!$user) {
		json_exit_error('目标用户不存在或已禁用');
	}

	$upIds = [];
	if (isset($_POST['up_product_id']) && is_array($_POST['up_product_id'])) {
		foreach ($_POST['up_product_id'] as $v) {
			$v = (int)$v;
			if ($v > 0) {
				$upIds[] = $v;
			}
		}
	}
	if ($upIds === []) {
		json_exit_error('请至少勾选一个产品');
	}
	// 周期可选：指定周期不存在时自动回退商品第一个可用周期
	$cycle = trim((string)($_POST['cycle'] ?? ''));
	if ($cycle !== '' && !isset(zjmf_cycles()[$cycle])) {
		$cycle = '';
	}

	$results = [];
	foreach ($upIds as $upId) {
		$product = zjmf_product_get_by_up((int)$supplier['id'], $upId);
		if (!$product) {
			$sync = ZjmfUpstream::syncOneProductBySupplier($supplier, $upId);
			if (empty($sync['ok'])) {
				$results[] = [
					'up_product_id' => $upId,
					'name'          => '上游#' . $upId,
					'ok'            => false,
					'msg'           => '商品同步失败：' . (string)($sync['msg'] ?? ''),
					'up_host_id'    => 0,
				];
				continue;
			}
			$product = zjmf_product_get_by_up((int)$supplier['id'], $upId);
		}
		if (!$product) {
			$results[] = [
				'up_product_id' => $upId,
				'name'          => '上游#' . $upId,
				'ok'            => false,
				'msg'           => '商品入库后仍未找到',
				'up_host_id'    => 0,
			];
			continue;
		}

		// 确定周期：管理员指定 > 商品第一个有价格的周期 > Monthly > 任意
		$cycles = zjmf_product_cycles($product);
		$pick = ($cycle !== '' && isset($cycles[$cycle])) ? $cycle : '';
		if ($pick === '') {
			foreach ($cycles as $k => $cfg) {
				if ((int)($cfg['agent_price_cents'] ?? 0) > 0
					|| (int)($cfg['price_cents'] ?? 0) > 0) {
					$pick = $k;
					break;
				}
			}
		}
		if ($pick === '') {
			if (isset($cycles['Monthly'])) {
				$pick = 'Monthly';
			} else {
				foreach ($cycles as $k => $v) {
					$pick = $k;
					break;
				}
			}
		}
		if ($pick === '') {
			$results[] = [
				'up_product_id' => $upId,
				'name'          => (string)$product['name'],
				'ok'            => false,
				'msg'           => '商品无可用周期',
				'up_host_id'    => 0,
			];
			continue;
		}

		// 管理员代开订单：金额 0，标记后直接开通（不扣用户本地余额）
		$cycleCfg = [
			'name'        => (string)($cycles[$pick]['name'] ?? $pick),
			'price_cents' => 0,
		];
		$order = zjmf_order_create($user, $product, $pick, $cycleCfg, 'buy', [
			'cost_cents'   => 0,
			'order_params' => json_encode(['assign_by' => 'admin'], JSON_UNESCAPED_UNICODE),
		]);
		if (empty($order['ok'])) {
			$results[] = [
				'up_product_id' => $upId,
				'name'          => (string)$product['name'],
				'ok'            => false,
				'msg'           => (string)($order['msg'] ?? '订单创建失败'),
				'up_host_id'    => 0,
			];
			continue;
		}
		$order_id = (int)$order['order_id'];
		zjmf_order_set_status($order_id, 'paid', '管理员代开');
		$open = zjmf_open_host($order_id);

		$results[] = [
			'up_product_id' => $upId,
			'name'          => (string)$product['name'],
			'ok'            => !empty($open['ok']),
			'msg'           => (string)($open['msg'] ?? '开通失败'),
			'up_host_id'    => (int)($open['host_id'] ?? 0),
		];
	}
	json_exit_success('处理完成', ['results' => $results]);
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

// 保存商品名称/简介/加价/排序/状态
mnbt_register_ajax('admin', 'p_zjmf_admin_save_product', function () {
	mnbt_plugin_require_admin();

	$id = (int)($_POST['id'] ?? 0);
	$product = zjmf_product_get($id);
	if (!$product) {
		json_exit_error('商品不存在');
	}

	$name = trim((string)($_POST['name'] ?? ''));
	$description = (string)($_POST['description'] ?? '');
	if ($name === '') {
		json_exit_error('请填写商品名称');
	}
	if (mb_strlen($name) > 100) {
		json_exit_error('商品名称过长');
	}
	if (mb_strlen($description) > 65535) {
		json_exit_error('商品简介过长');
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
		 SET name=?, description=?, markup_type=?, markup_value=?, sort=?, status=?, updated_at=?
		 WHERE id=?",
		[$name, $description, $markupType, $markupValue, $sort, $status, $now, $id]
	);
	if (!$ok) {
		json_exit_error('保存失败');
	}
	// 加价变化后重算各周期本地售价
	zjmf_product_recalc_price($id);
	json_exit_success('已保存');
});

// 保存商品各周期售价（管理员直接定价，覆盖加价规则）
mnbt_register_ajax('admin', 'p_zjmf_admin_save_cycles', function () {
	mnbt_plugin_require_admin();
	global $DB, $date;

	$id = (int)($_POST['id'] ?? 0);
	$product = zjmf_product_get($id);
	if (!$product) {
		json_exit_error('商品不存在');
	}

	$overrides = (isset($_POST['overrides']) && is_array($_POST['overrides']))
		? $_POST['overrides'] : [];
	if ($overrides === []) {
		json_exit_error('请至少填写一个周期的售价');
	}

	$now = $date ?: date('Y-m-d H:i:s');
	$known = zjmf_cycles();
	$agentCents = max(0, (int)$product['agent_price_cents']);
	$old = zjmf_product_cycles($product);
	$save = [];
	foreach ($overrides as $cycle => $val) {
		$cycle = trim((string)$cycle);
		if ($cycle === '' || !is_numeric($val)) {
			continue;
		}
		$override = max(0, (int)round((float)$val * 100));
		$entry = $old[$cycle] ?? [];
		$save[$cycle] = [
			'cycle'             => $cycle,
			'name'              => (string)($entry['name'] ?? $known[$cycle]['name'] ?? $cycle),
			'agent_price_cents' => (int)($entry['agent_price_cents'] ?? $agentCents),
			'price_cents'       => $override,
			'override'          => $override,
		];
	}
	if ($save === []) {
		json_exit_error('周期售价参数无效');
	}
	$ok = $DB->query_prepare(
		"UPDATE MN_plugin_zjmf_product SET cycles=?, updated_at=? WHERE id=?",
		[json_encode(array_values($save), JSON_UNESCAPED_UNICODE), $now, $id]
	);
	if (!$ok) {
		json_exit_error('保存失败');
	}
	json_exit_success('周期售价已保存');
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

// 商品列表（对游客开放，无需登录）
mnbt_register_route('GET', '/reserve/shop', function ($params, $ctx) {
	zjmf_render('shop', [
		'page_title' => '商品选购',
		'products'   => zjmf_product_list_active(),
	]);
});

// 公开商品列表 API（供官网首页/游客展示，无需登录）
mnbt_register_route('GET', '/reserve/api/public_products', function ($params, $ctx) {
	$list = [];
	foreach (zjmf_product_list_active() as $p) {
		$min = 0;
		foreach (zjmf_product_cycles($p) as $cfg) {
			$c = (int)$cfg['price_cents'];
			if ($c > 0 && ($min === 0 || $c < $min)) {
				$min = $c;
			}
		}
		$list[] = [
			'id'             => (int)$p['id'],
			'name'           => (string)$p['name'],
			'description'    => zjmf_render_description((string)($p['description'] ?? '')),
			'currency'       => (string)($p['currency'] ?? ''),
			'min_price_cents'=> $min,
		];
	}
	zjmf_json('ok', ['list' => $list]);
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
		echo '该商品已暂停销售';
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
		zjmf_json('该商品已暂停销售，无法下单');
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

	zjmf_json('ok', [
		'html'     => $html,
		'order_no' => $order_no,
		'msg'      => '正在跳转到支付页面',
	]);
});

/* ============================================================
 *  用户端 API 路由（SPA 数据接口）
 * ============================================================ */

// 上架商品列表（含各周期价格，游客可访问，供 account SPA 商城页）
mnbt_register_route('GET', '/reserve/api/products', function ($params, $ctx) {
	$list = [];
	foreach (zjmf_product_list_active() as $p) {
		$cycles = [];
		foreach (zjmf_product_cycles($p) as $key => $cfg) {
			$cycles[] = [
				'key'         => $key,
				'name'        => $cfg['name'],
				'price_cents' => $cfg['price_cents'],
			];
		}
		$list[] = [
			'id'          => (int)$p['id'],
			'name'        => (string)$p['name'],
			'description' => zjmf_render_description((string)($p['description'] ?? '')),
			'currency'    => (string)($p['currency'] ?? ''),
			'cycles'      => $cycles,
		];
	}
	zjmf_json('ok', ['list' => $list]);
});

// 可用支付方式（account SPA 下单页用）
mnbt_register_route('GET', '/reserve/api/methods', function ($params, $ctx) {
	$methods = function_exists('mnbt_get_enabled_payment_methods')
		? mnbt_get_enabled_payment_methods() : [];
	zjmf_json('ok', ['methods' => $methods]);
});

// 我的主机（account SPA，脱敏后返回）
mnbt_register_route('GET', '/reserve/api/hosts', function ($params, $ctx) {
	$user = function_exists('user_info_auth_current') ? user_info_auth_current() : null;
	if (!$user) {
		zjmf_json('not_login', ['logged_in' => false]);
		return;
	}
	$hosts = [];
	foreach (zjmf_host_list_by_user((int)$user['id']) as $h) {
		// 缺失上游主机 ID 时尝试从上游主机列表补齐（同一次请求只拉一次上游列表）
		$h = zjmf_backfill_host_upid($h);
		$supplier = zjmf_supplier_get((int)$h['supplier_id']);
		$hosts[] = [
			'id'            => (int)$h['id'],
			'up_host_id'    => (int)$h['up_host_id'],
			'up_product_id' => (int)$h['up_product_id'],
			'name'          => (string)$h['name'],
			'username'      => (string)$h['username'],
			'cycle'         => (string)$h['cycle'],
			'status'        => (string)$h['status'],
			// 历史数据可能存了上游时间戳，统一归一化为 Y-m-d
			'renew_date'    => zjmf_normalize_date((string)$h['renew_date']),
			'created_at'    => (string)$h['created_at'],
			'updated_at'    => (string)$h['updated_at'],
			'supplier_name' => (string)($supplier['name'] ?? ''),
		];
	}
	zjmf_json('ok', ['logged_in' => true, 'hosts' => $hosts]);
});

// 我的订单（account SPA，分页）
mnbt_register_route('GET', '/reserve/api/orders', function ($params, $ctx) {
	$user = function_exists('user_info_auth_current') ? user_info_auth_current() : null;
	if (!$user) {
		zjmf_json('not_login', ['logged_in' => false]);
		return;
	}
	$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
	$per  = isset($_GET['per_page']) ? max(1, min(100, (int)$_GET['per_page'])) : 15;
	$orders = zjmf_order_list_by_user((int)$user['id'], $page, $per);
	zjmf_json('ok', ['logged_in' => true, 'orders' => $orders]);
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
	$hosts = zjmf_host_list_by_user((int)$user['id']);
	// 列表页顺带补齐缺失的上游主机 ID（同一次请求只拉一次上游列表）
	foreach ($hosts as $i => $h) {
		$hosts[$i] = zjmf_backfill_host_upid($h);
		// 历史数据可能存了上游时间戳，统一归一化为 Y-m-d
		$hosts[$i]['renew_date'] = zjmf_normalize_date((string)$hosts[$i]['renew_date']);
	}
	zjmf_render('hosts', [
		'page_title' => '我的主机',
		'hosts'      => $hosts,
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

	// 缺失上游主机 ID 时尝试补齐（开通结算未解析出 ID 的历史数据）
	$host = zjmf_backfill_host_upid($host);
	// 历史数据可能存了上游时间戳，统一归一化为 Y-m-d
	$host['renew_date'] = zjmf_normalize_date((string)$host['renew_date']);

	// 实时信息（失败不致命，仅展示缓存）
	$info = ['ok' => false, 'msg' => ''];
	$traffic = ['ok' => false, 'data' => []];
	$dcim = ['ok' => false, 'data' => []];
	$osList = [];
	$osGroups = [];
	$osError = '';
	$supplier = zjmf_supplier_get((int)$host['supplier_id']);
	if ((int)$host['up_host_id'] > 0 && $supplier) {
		try {
			$info = ZjmfUpstream::hostInfo($supplier, (int)$host['up_host_id']);
			$traffic = ZjmfUpstream::hostTraffic($supplier, (int)$host['up_host_id']);
			// DCIM 信息（交换机端口/电源状态/重装次数/任务进度），非 DCIM 产品为空，失败不致命
			$dcim = ZjmfUpstream::hostDcimInfo($supplier, (int)$host['up_host_id']);
			// 重装系统列表（GET host/dedicatedserver?host_id=，实测可返回 cloud_os），
			// 失败回退 host/product 的 dcim.os
			$osList = is_array($info['dcim']['os'] ?? null) ? $info['dcim']['os'] : [];
			$osGroups = [];
			$osError = '';
			$co = ZjmfUpstream::hostDedicatedOs($supplier, (int)$host['up_host_id']);
			if (!empty($co['ok']) && $co['os_list'] !== []) {
				$osList = $co['os_list'];
				$osGroups = $co['groups'];
			} else {
				$osError = (string)($co['msg'] ?? '获取系统列表失败');
			}
			// 详情端点取不到状态时，从 host/list（含 domainstatus）按 ID 匹配兜底
			if (($info['status'] ?? '') === 'unknown') {
				$lr = ZjmfUpstream::hostList($supplier, ['limit' => 100]);
				if (!empty($lr['ok'])) {
					foreach (($lr['data']['list'] ?? []) as $it) {
						if ((int)($it['id'] ?? 0) === (int)$host['up_host_id']) {
							$mapped = zjmf_map_upstream_status((string)($it['domainstatus'] ?? ''));
							if ($mapped !== 'unknown') {
								$info['status'] = $mapped;
								$info['data'] = is_array($info['data']) ? $info['data'] : [];
								$info['data'] = array_merge($info['data'], $it);
							}
							break;
						}
					}
				}
			}
			// 拉取成功后同步缓存状态（unknown 不上写，避免误覆盖）
			if (!empty($info['ok']) && $info['status'] !== 'unknown' && $info['status'] !== $host['status']) {
				zjmf_host_update_cache((int)$host['id'], ['status' => $info['status']]);
				$host['status'] = $info['status'];
			}
		} catch (Throwable $e) {
			error_log('[zjmfmanager_reserve] host detail upstream: ' . $e->getMessage());
		}
	}

	zjmf_render('host', [
		'page_title'    => '主机详情：' . $host['name'],
		'host'          => $host,
		'info'          => $info,
		'traffic'       => $traffic,
		'dcim'          => $dcim,
		'config_options'=> is_array($info['config_options'] ?? null) ? $info['config_options'] : [],
		'custom_fields' => is_array($info['custom_fields'] ?? null) ? $info['custom_fields'] : [],
		'os_list'       => $osList,
		'os_groups'     => $osGroups,
		'os_error'      => $osError,
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
		zjmf_json('服务已停用，无法执行操作');
	}
	$supplier = zjmf_supplier_get((int)$host['supplier_id']);

	// DCIM 专用操作（救援系统/重置BMC/取消任务）
	$dcimActions = ['rescue', 'bmc', 'cancel_task'];
	if (in_array($action, $dcimActions, true)) {
		$extra = [];
		$apiAction = $action;
		if ($action === 'rescue') {
			$system = (int)($_POST['system'] ?? 0);
			if ($system !== 1 && $system !== 2) {
				zjmf_json('请选择救援系统（1 Linux / 2 Windows）');
			}
			$extra['system'] = $system;
		}
		$result = ZjmfUpstream::hostDcimAction($supplier, (int)$host['up_host_id'], $apiAction, $extra);
		$hostOrder = zjmf_order_get((int)$host['order_id']);
		$orderNo = $hostOrder ? $hostOrder['order_no'] : '';
		zjmf_log((int)$user['id'], $orderNo, 'host_action:' . $action,
			empty($result['ok']) ? 'failed' : 'success', $result['msg'] ?? '',
			(int)$host['supplier_id']);
		if (empty($result['ok'])) {
			zjmf_json($result['msg'] ?? '操作失败');
		}
		zjmf_json('ok', ['msg' => '操作成功']);
	}

	// 重装系统：POST /provision/default (func=reinstall)，只需 os 与 os_group，无需密码/端口
	if ($action === 'dcim_reinstall') {
		$os = (int)($_POST['os'] ?? 0);
		if ($os <= 0) {
			zjmf_json('请选择操作系统');
		}
		$extra = ['os' => $os, 'code' => 0];
		$osGroup = trim((string)($_POST['os_group'] ?? ''));
		if ($osGroup !== '') {
			$extra['os_group'] = $osGroup;
		}
		$result = ZjmfUpstream::hostAction($supplier, (int)$host['up_host_id'], 'reinstall', $extra);
		$hostOrder = zjmf_order_get((int)$host['order_id']);
		$orderNo = $hostOrder ? $hostOrder['order_no'] : '';
		zjmf_log((int)$user['id'], $orderNo, 'host_action:dcim_reinstall',
			empty($result['ok']) ? 'failed' : 'success', $result['msg'] ?? '',
			(int)$host['supplier_id']);
		if (empty($result['ok'])) {
			zjmf_json($result['msg'] ?? '操作失败');
		}
		zjmf_json('ok', ['msg' => '操作成功']);
	}

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
		echo '服务已停用，无法升级';
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
		zjmf_json('服务已停用，无法升级');
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
	$cache['renew_date'] = zjmf_normalize_date(
		(string)($preview['data']['renew_date'] ?? '')
	);
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
	$cache = [];
	if ($info['status'] !== 'unknown') {
		$cache['status'] = $info['status'];
	}
	if (isset($info['data']['nextduedate']) || isset($info['data']['renewdate']) || isset($info['data']['renew_date'])) {
		$cache['renew_date'] = zjmf_normalize_date(
			(string)($info['data']['nextduedate'] ?? $info['data']['renew_date'] ?? $info['data']['renewdate'] ?? '')
		);
	}
	if ($cache) {
		zjmf_host_update_cache($id, $cache);
	}
	json_exit_success('已刷新', ['status' => $info['status']]);
});

// 全部刷新主机状态（管理员）：拉取 host/list 全量按 ID 匹配批量更新，
// 修复存量 unknown/过期缓存（比逐台 hostInfo 快且可靠）
mnbt_register_ajax('admin', 'p_zjmf_admin_fetch_all_hosts', function () {
	mnbt_plugin_require_admin();

	// 拉取本地全部主机
	$hosts = [];
	$page = 1;
	$per = 200;
	while (true) {
		$res = zjmf_host_list_all($page, $per);
		$list = $res['list'] ?? [];
		if ($list === []) {
			break;
		}
		foreach ($list as $h) {
			$hosts[] = $h;
		}
		if ($page * $per >= (int)($res['total'] ?? 0)) {
			break;
		}
		$page++;
	}

	// 按供应商拉取上游主机列表（分页全量）
	$supplierIds = [];
	foreach ($hosts as $h) {
		$sid = (int)$h['supplier_id'];
		if ($sid > 0) {
			$supplierIds[$sid] = true;
		}
	}
	$upMap = [];
	foreach (array_keys($supplierIds) as $sid) {
		$supplier = zjmf_supplier_get($sid);
		if (!$supplier) {
			continue;
		}
		$items = [];
		$sum = 0;
		$maxPage = 1;
		$p = 1;
		$safety = 30;
		while ($p <= $maxPage && $safety-- > 0) {
			$r = ZjmfUpstream::hostList($supplier, ['page' => $p, 'limit' => 100]);
			if (empty($r['ok'])) {
				break;
			}
			$d = $r['data'] ?? [];
			foreach (($d['list'] ?? []) as $it) {
				$items[(int)($it['id'] ?? 0)] = $it;
			}
			$sum = (int)($d['sum'] ?? count($items));
			$maxPage = (int)($d['max_page'] ?? 1);
			if ($maxPage <= 0) {
				$maxPage = 1;
			}
			if ($sum > 0 && count($items) >= $sum) {
				break;
			}
			$p++;
		}
		$upMap[$sid] = $items;
	}

	$stat = ['total' => count($hosts), 'ok' => 0, 'fail' => 0, 'no_up' => 0,
		'missing' => 0, 'unknown' => 0, 'changed' => 0];
	foreach ($hosts as $host) {
		$upId = (int)$host['up_host_id'];
		if ($upId <= 0) {
			$stat['no_up']++;
			continue;
		}
		$item = $upMap[(int)$host['supplier_id']][$upId] ?? null;
		if (!$item) {
			$stat['missing']++;
			continue;
		}
		$status = zjmf_map_upstream_status((string)($item['domainstatus'] ?? ''));
		$renew = zjmf_normalize_date((string)($item['nextduedate'] ?? ''));
		$cache = [];
		if ($status !== 'unknown' && $status !== (string)$host['status']) {
			$cache['status'] = $status;
			$stat['changed']++;
		}
		if ($renew !== '' && $renew !== (string)$host['renew_date']) {
			$cache['renew_date'] = $renew;
		}
		if ($cache) {
			zjmf_host_update_cache((int)$host['id'], $cache);
		}
		if ($status === 'unknown') {
			$stat['unknown']++;
		}
		$stat['ok']++;
	}
	json_exit_success('刷新完成', $stat);
});
