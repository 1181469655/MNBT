<?php
/**
 * zjmfmanager_reserve 插件 - 辅助函数库
 *
 * 提供：URL/渲染/认证/金额辅助、商品、订单、主机、日志的数据库操作，
 * 以及主机开通编排（支付成功后调用上游开通，失败自动退款）。
 */

if (!defined('IN_CRONLITE')) {
	exit;
}

/* ============================================================
 *  常量
 * ============================================================ */

/** 业务类型标识（MN_dd.lx），用于 order.paid 钩子过滤 */
define('ZJMF_LX', 'zjmf');

/** 本地订单号前缀 */
define('ZJMF_ORDER_PREFIX', 'ZJM');

/* ============================================================
 *  URL / 渲染辅助
 * ============================================================ */

/** 生成带站点 base path 前缀的 URL。 */
function zjmf_url($path = '')
{
	$scriptName = isset($_SERVER['SCRIPT_NAME'])
		? str_replace('\\', '/', $_SERVER['SCRIPT_NAME']) : '';
	$basePath = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');
	if ($basePath === '.' || $basePath === '/') {
		$basePath = '';
	}
	// 使用查询参数路由（index.php?_r=/path），避免依赖 Web 服务器 rewrite
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
function zjmf_asset_url($path = '')
{
	return mnbt_plugin_url('zjmfmanager_reserve', 'assets/' . ltrim($path, '/'));
}

/** 管理员端插件页面 URL（admin/plugin.php?p=zjmfmanager_reserve&page=xxx）。 */
function zjmf_admin_url($page, $extra = '')
{
	$base = 'plugin.php?p=zjmfmanager_reserve&page=' . rawurlencode($page);
	if ($extra !== '') {
		$base .= '&' . ltrim($extra, '&');
	}
	return $base;
}

/** 金额（分）→ 元（保留 2 位小数）。 */
function zjmf_format_cents($cents)
{
	return number_format((int)$cents / 100, 2, '.', '');
}

/** 生成本地订单号。 */
function zjmf_order_no()
{
	return ZJMF_ORDER_PREFIX . date('YmdHis') . mt_rand(1000, 9999);
}

/** 获取当前登录的 user_info 用户，未登录跳转登录页。 */
function zjmf_require_user()
{
	if (!function_exists('user_info_auth_current')) {
		http_response_code(500);
		echo '需要先启用 user_info 插件';
		exit;
	}
	$user = user_info_auth_current();
	if (!$user) {
		header('Location: ' . zjmf_url('account/login'));
		exit;
	}
	return $user;
}

/** 渲染用户端视图。 */
function zjmf_render($view, $vars = [])
{
	$vars['current_user'] = $vars['current_user']
		?? (function_exists('user_info_auth_current') ? user_info_auth_current() : null);
	extract($vars, EXTR_SKIP);
	$viewFile = mnbt_plugin_path('zjmfmanager_reserve') . 'views/' . $view . '.php';
	if (!is_file($viewFile)) {
		http_response_code(500);
		echo 'View not found: ' . htmlspecialchars($view);
		return;
	}
	include $viewFile;
}

/** 渲染管理员端视图。 */
function zjmf_render_admin($view, $vars = [])
{
	extract($vars, EXTR_SKIP);
	$viewFile = mnbt_plugin_path('zjmfmanager_reserve') . 'views/admin/' . $view . '.php';
	if (!is_file($viewFile)) {
		http_response_code(500);
		echo 'Admin view not found: ' . htmlspecialchars($view);
		return;
	}
	include $viewFile;
}

/** 输出 JSON 并退出。 */
function zjmf_json($code, $extra = [])
{
	@header('Content-Type: application/json; charset=UTF-8');
	$payload = ['code' => $code];
	if (is_array($extra)) {
		$payload = array_merge($payload, $extra);
	}
	echo json_encode($payload, JSON_UNESCAPED_UNICODE);
	exit;
}

/** 明文加密（authcode，用于上游主机密码入库）。 */
function zjmf_encrypt($plain)
{
	return authcode((string)$plain, 'ENCODE', SYS_KEY);
}

/** 密文解密。 */
function zjmf_decrypt($cipher)
{
	return authcode((string)$cipher, 'DECODE', SYS_KEY);
}

/** 账号脱敏展示（保留前 3 后 2）。 */
function zjmf_mask_account($username)
{
	$s = (string)$username;
	$len = strlen($s);
	if ($len <= 5) {
		return substr($s, 0, 1) . '***';
	}
	return substr($s, 0, 3) . '***' . substr($s, -2);
}

/** 写操作日志。 */
function zjmf_log($user_id, $order_no, $action, $result, $content, $supplier_id = 0)
{
	global $DB, $date;
	$now = $date ?: date('Y-m-d H:i:s');
	$DB->query_prepare(
		"INSERT INTO MN_plugin_zjmf_log
		 (user_id, supplier_id, order_no, action, result, content, created_at)
		 VALUES (?,?,?,?,?,?,?)",
		[(int)$user_id, (int)$supplier_id, (string)$order_no, (string)$action,
		 (string)$result, (string)$content, $now]
	);
}

/** 日志列表（管理员，分页）。 */
function zjmf_log_list_all($page = 1, $per_page = 30)
{
	global $DB;
	$page = max(1, (int)$page);
	$per_page = max(1, min(200, (int)$per_page));
	$offset = ($page - 1) * $per_page;
	$count_row = $DB->get_row_prepare("SELECT COUNT(*) AS cnt FROM MN_plugin_zjmf_log");
	$total = $count_row ? (int)$count_row['cnt'] : 0;
	$list = $DB->get_all_prepare(
		"SELECT l.*, u.username AS user_name, s.name AS supplier_name
		 FROM MN_plugin_zjmf_log l
		 LEFT JOIN MN_plugin_user u ON u.id = l.user_id
		 LEFT JOIN MN_plugin_zjmf_supplier s ON s.id = l.supplier_id
		 ORDER BY l.id DESC LIMIT {$offset},{$per_page}"
	) ?: [];
	return ['list' => $list, 'total' => $total, 'page' => $page, 'per_page' => $per_page];
}

/* ============================================================
 *  计费周期
 * ============================================================ */

/** 可用计费周期（key 与上游 billingcycle 一致，WHMCS 风格）。 */
function zjmf_cycles()
{
	return [
		'Monthly'      => ['name' => '月付'],
		'Quarterly'    => ['name' => '季付'],
		'SemiAnnually' => ['name' => '半年付'],
		'Annually'     => ['name' => '年付'],
		'Biennially'   => ['name' => '两年付'],
		'Triennially'  => ['name' => '三年付'],
	];
}

/* ============================================================
 *  供应商管理
 * ============================================================ */

/** 获取单个供应商（行数组）。 */
function zjmf_supplier_get($supplier_id)
{
	global $DB;
	return $DB->get_row_prepare(
		"SELECT * FROM MN_plugin_zjmf_supplier WHERE id=? LIMIT 1",
		[(int)$supplier_id]
	) ?: null;
}

/** 全部供应商列表（管理员，按 sort 升序）。 */
function zjmf_supplier_list_all()
{
	global $DB;
	return $DB->get_all_prepare(
		"SELECT * FROM MN_plugin_zjmf_supplier ORDER BY sort ASC, id ASC"
	) ?: [];
}

/** 供应商加价标签（用于列表展示）。 */
function zjmf_supplier_markup_label($supplier)
{
	$type = (int)($supplier['markup_type'] ?? 0);
	$value = (int)($supplier['markup_value'] ?? 0);
	return $type === 1
		? '固定 +' . zjmf_format_cents($value) . ' 元'
		: '比例 +' . ($value / 10) . '%';
}

/** 供应商是否可销售（存在且启用）。 */
function zjmf_supplier_usable($supplier_id)
{
	$supplier = zjmf_supplier_get($supplier_id);
	return $supplier && (int)$supplier['status'] === 1;
}

/**
 * 删除供应商（有商品/订单/主机时拒绝）。
 *
 * @return array ['ok'=>bool, 'msg'=>string]
 */
function zjmf_supplier_delete($supplier_id)
{
	global $DB;
	$supplier_id = (int)$supplier_id;
	$tables = [
		'MN_plugin_zjmf_product' => '商品',
		'MN_plugin_zjmf_order'   => '订单',
		'MN_plugin_zjmf_host'    => '主机',
	];
	foreach ($tables as $table => $label) {
		$row = $DB->get_row_prepare(
			"SELECT COUNT(*) AS cnt FROM {$table} WHERE supplier_id=? LIMIT 1",
			[$supplier_id]
		);
		if ($row && (int)$row['cnt'] > 0) {
			return ['ok' => false, 'msg' => '该供应商下存在' . $label . '数据，无法删除'];
		}
	}
	$ok = $DB->query_prepare(
		"DELETE FROM MN_plugin_zjmf_supplier WHERE id=?",
		[$supplier_id]
	);
	return $ok ? ['ok' => true, 'msg' => '已删除'] : ['ok' => false, 'msg' => '删除失败'];
}

/* ============================================================
 *  商品管理
 * ============================================================ */

/** 获取单个商品。 */
function zjmf_product_get($product_id)
{
	global $DB;
	return $DB->get_row_prepare(
		"SELECT * FROM MN_plugin_zjmf_product WHERE id=? LIMIT 1",
		[(int)$product_id]
	) ?: null;
}

/** 按供应商 + 上游商品 ID 获取商品。 */
function zjmf_product_get_by_up($supplier_id, $up_product_id)
{
	global $DB;
	return $DB->get_row_prepare(
		"SELECT * FROM MN_plugin_zjmf_product
		 WHERE supplier_id=? AND up_product_id=? LIMIT 1",
		[(int)$supplier_id, (int)$up_product_id]
	) ?: null;
}

/** 上架商品列表（用户端，仅所属供应商启用时可见）。 */
function zjmf_product_list_active()
{
	global $DB;
	return $DB->get_all_prepare(
		"SELECT p.*, s.name AS supplier_name
		 FROM MN_plugin_zjmf_product p
		 LEFT JOIN MN_plugin_zjmf_supplier s ON s.id = p.supplier_id
		 WHERE p.status=1 AND s.status=1
		 ORDER BY s.sort ASC, p.sort ASC, p.id ASC"
	) ?: [];
}

/** 全部商品列表（管理员，含供应商名）。 */
function zjmf_product_list_all()
{
	global $DB;
	return $DB->get_all_prepare(
		"SELECT p.*, s.name AS supplier_name
		 FROM MN_plugin_zjmf_product p
		 LEFT JOIN MN_plugin_zjmf_supplier s ON s.id = p.supplier_id
		 ORDER BY s.sort ASC, p.sort ASC, p.id ASC"
	) ?: [];
}

/** 解析商品周期 JSON，返回 ['cycle' => ['name'=>, 'price_cents'=>]]。 */
function zjmf_product_cycles($product)
{
	$raw = isset($product['cycles']) ? json_decode($product['cycles'], true) : null;
	if (!is_array($raw)) {
		return [];
	}
	$map = [];
	foreach ($raw as $item) {
		$cycle = (string)($item['cycle'] ?? '');
		if ($cycle === '') {
			continue;
		}
		$map[$cycle] = [
			'name'        => (string)($item['name'] ?? $cycle),
			'price_cents' => (int)($item['price_cents'] ?? 0),
		];
	}
	return $map;
}

/**
 * 计算本地售价（分）。
 *
 * @param int $agentCents   上游代理价（分）
 * @param int $markupType   0=比例 1=固定（分）
 * @param int $markupValue  比例（千分比）或固定加价（分）
 * @return int
 */
function zjmf_calc_price($agentCents, $markupType, $markupValue)
{
	$agentCents = max(0, (int)$agentCents);
	if ($markupType === 1) {
		return max(0, $agentCents + max(0, (int)$markupValue));
	}
	$rate = max(0, (int)$markupValue);
	return max(0, (int)round($agentCents * (1000 + $rate) / 1000));
}

/**
 * 重算商品各周期本地售价并写回 cycles 字段。
 * 加价规则：单品有配置则用单品，否则用所属供应商配置。
 *
 * @param int $product_id
 * @return void
 */
function zjmf_product_recalc_price($product_id)
{
	global $DB, $date;
	$product = zjmf_product_get($product_id);
	if (!$product) {
		return;
	}
	$supplier = zjmf_supplier_get((int)$product['supplier_id']);
	// 单品已配置加价（比例 type=0 或固定 type=1 且 value>0）时用单品规则，否则用供应商
	$hasOwn = (int)($product['markup_type'] ?? 0) !== 0
		|| (int)($product['markup_value'] ?? 0) > 0;
	$markupType = $hasOwn
		? (int)$product['markup_type'] : (int)($supplier['markup_type'] ?? 0);
	$markupValue = $hasOwn
		? (int)$product['markup_value'] : (int)($supplier['markup_value'] ?? 0);

	$cycles = zjmf_product_cycles($product);
	if ($cycles === []) {
		return;
	}
	foreach ($cycles as $cycle => &$cfg) {
		// agent_price 保存的是该周期对应的上游价（分）
		$cfg['price_cents'] = zjmf_calc_price(
			$cfg['agent_price_cents'] ?? 0,
			$markupType,
			$markupValue
		);
	}
	unset($cfg);
	$now = $date ?: date('Y-m-d H:i:s');
	$DB->query_prepare(
		"UPDATE MN_plugin_zjmf_product SET cycles=?, updated_at=? WHERE id=?",
		[json_encode(array_values($cycles), JSON_UNESCAPED_UNICODE), $now, (int)$product_id]
	);
}

/* ============================================================
 *  订单管理
 * ============================================================ */

/** 按 ID 查询订单。 */
function zjmf_order_get($order_id)
{
	global $DB;
	return $DB->get_row_prepare(
		"SELECT * FROM MN_plugin_zjmf_order WHERE id=? LIMIT 1",
		[(int)$order_id]
	) ?: null;
}

/** 按订单号查询订单。 */
function zjmf_order_get_by_no($order_no)
{
	global $DB;
	return $DB->get_row_prepare(
		"SELECT * FROM MN_plugin_zjmf_order WHERE order_no=? LIMIT 1",
		[$order_no]
	) ?: null;
}

/** 用户订单列表（分页）。 */
function zjmf_order_list_by_user($user_id, $page = 1, $per_page = 20)
{
	global $DB;
	$user_id = (int)$user_id;
	$page = max(1, (int)$page);
	$per_page = max(1, min(100, (int)$per_page));
	$offset = ($page - 1) * $per_page;
	$count_row = $DB->get_row_prepare(
		"SELECT COUNT(*) AS cnt FROM MN_plugin_zjmf_order WHERE user_id=?",
		[$user_id]
	);
	$total = $count_row ? (int)$count_row['cnt'] : 0;
	$list = $DB->get_all_prepare(
		"SELECT o.*, s.name AS supplier_name
		 FROM MN_plugin_zjmf_order o
		 LEFT JOIN MN_plugin_zjmf_supplier s ON s.id = o.supplier_id
		 WHERE o.user_id=? ORDER BY o.id DESC LIMIT {$offset},{$per_page}",
		[$user_id]
	) ?: [];
	return ['list' => $list, 'total' => $total, 'page' => $page, 'per_page' => $per_page];
}

/** 全部订单列表（管理员，分页 + 简单筛选）。 */
function zjmf_order_list_all($page = 1, $per_page = 30, $filters = [])
{
	global $DB;
	$page = max(1, (int)$page);
	$per_page = max(1, min(200, (int)$per_page));
	$offset = ($page - 1) * $per_page;

	$where = '1';
	$params = [];
	if (!empty($filters['status'])) {
		$where .= ' AND status=?';
		$params[] = $filters['status'];
	}
	if (!empty($filters['order_no'])) {
		$where .= ' AND order_no LIKE ?';
		$params[] = '%' . $filters['order_no'] . '%';
	}
	if (!empty($filters['user_id'])) {
		$where .= ' AND user_id=?';
		$params[] = (int)$filters['user_id'];
	}
	if (!empty($filters['supplier_id'])) {
		$where .= ' AND supplier_id=?';
		$params[] = (int)$filters['supplier_id'];
	}

	$count_row = $DB->get_row_prepare(
		"SELECT COUNT(*) AS cnt FROM MN_plugin_zjmf_order WHERE {$where}",
		$params
	);
	$total = $count_row ? (int)$count_row['cnt'] : 0;
	$list = $DB->get_all_prepare(
		"SELECT o.*, s.name AS supplier_name
		 FROM MN_plugin_zjmf_order o
		 LEFT JOIN MN_plugin_zjmf_supplier s ON s.id = o.supplier_id
		 WHERE {$where} ORDER BY o.id DESC LIMIT {$offset},{$per_page}",
		$params
	) ?: [];
	return ['list' => $list, 'total' => $total, 'page' => $page, 'per_page' => $per_page];
}

/**
 * 创建本地订单（未支付）。
 *
 * @param array  $user       user_info 当前用户
 * @param array  $product    本地商品行
 * @param string $cycle      计费周期
 * @param array  $cycleCfg   周期配置（name/price_cents）
 * @param string $action     buy/upgrade_config/upgrade_product
 * @param array  $extra      附加字段（up_host_id/host_id/order_params/cost_cents）
 * @return array ['ok'=>bool, 'order_no'=>string, 'order_id'=>int, 'msg'=>string]
 */
function zjmf_order_create($user, $product, $cycle, $cycleCfg, $action = 'buy', $extra = [])
{
	global $DB, $date;
	$now = $date ?: date('Y-m-d H:i:s');
	$order_no = zjmf_order_no();
	$cycleName = (string)($cycleCfg['name'] ?? $cycle);

	$ok = $DB->query_prepare(
		"INSERT INTO MN_plugin_zjmf_order
		 (order_no, action, supplier_id, user_id, product_id, up_product_id,
		  product_name, cycle, cycle_name, amount_cents, cost_cents, order_params,
		  up_order_id, up_host_id, host_id, username, status,
		  pay_time, opened_at, remark, created_at)
		 VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)",
		[
			$order_no,
			$action,
			(int)($product['supplier_id'] ?? 0),
			(int)$user['id'],
			(int)($product['id'] ?? 0),
			(int)($product['up_product_id'] ?? 0),
			(string)($product['name'] ?? ''),
			$cycle,
			$cycleName,
			(int)($cycleCfg['price_cents'] ?? 0),
			(int)($extra['cost_cents'] ?? 0),
			(string)($extra['order_params'] ?? ''),
			(int)($extra['up_order_id'] ?? 0),
			(int)($extra['up_host_id'] ?? 0),
			(int)($extra['host_id'] ?? 0),
			(string)($extra['username'] ?? ''),
			'pending',
			'',
			'',
			'',
			$now,
		]
	);
	if (!$ok) {
		return ['ok' => false, 'order_no' => '', 'order_id' => 0, 'msg' => '订单写入失败'];
	}
	$row = $DB->get_row_prepare(
		"SELECT id FROM MN_plugin_zjmf_order WHERE order_no=? LIMIT 1",
		[$order_no]
	);
	return [
		'ok'       => true,
		'order_no' => $order_no,
		'order_id' => $row ? (int)$row['id'] : 0,
		'msg'      => '',
	];
}

/** 更新订单状态。 */
function zjmf_order_set_status($order_id, $status, $remark = '')
{
	global $DB, $date;
	$now = $date ?: date('Y-m-d H:i:s');
	$extra = '';
	$params = [$status];
	if ($status === 'paid') {
		$extra = ', pay_time=?';
		$params[] = $now;
	} elseif ($status === 'opened') {
		$extra = ', opened_at=?';
		$params[] = $now;
	}
	if ($remark !== '') {
		$extra .= ', remark=?';
		$params[] = $remark;
	}
	$params[] = (int)$order_id;
	return (bool)$DB->query_prepare(
		"UPDATE MN_plugin_zjmf_order SET status=?{$extra} WHERE id=?",
		$params
	);
}

/** 回填订单开通信息。 */
function zjmf_order_fill_opened($order_id, $upOrderId, $upHostId, $username)
{
	global $DB;
	return (bool)$DB->query_prepare(
		"UPDATE MN_plugin_zjmf_order
		 SET up_order_id=?, up_host_id=?, username=?
		 WHERE id=?",
		[(int)$upOrderId, (int)$upHostId, (string)$username, (int)$order_id]
	);
}

/**
 * 创建升级订单（扣款前）。
 *
 * @param array  $user         user_info 当前用户
 * @param array  $host         本地主机映射行
 * @param string $action       upgrade_config / upgrade_product
 * @param int    $amountCents  升级差额（分）
 * @param string $orderParams  升级参数 JSON（selection）
 * @return array ['ok'=>bool, 'order_no'=>string, 'order_id'=>int, 'msg'=>string]
 */
function zjmf_upgrade_order_create($user, $host, $action, $amountCents, $orderParams)
{
	global $DB, $date;
	$now = $date ?: date('Y-m-d H:i:s');
	$order_no = zjmf_order_no();

	$ok = $DB->query_prepare(
		"INSERT INTO MN_plugin_zjmf_order
		 (order_no, action, supplier_id, user_id, product_id, up_product_id,
		  product_name, cycle, cycle_name, amount_cents, cost_cents, order_params,
		  up_order_id, up_host_id, host_id, username, status,
		  pay_time, opened_at, remark, created_at)
		 VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)",
		[
			$order_no,
			$action,
			(int)($host['supplier_id'] ?? 0),
			(int)$user['id'],
			0,
			(int)$host['up_product_id'],
			'升级：' . $host['name'],
			(string)($host['cycle'] ?? ''),
			(string)($host['cycle'] ?? ''),
			(int)$amountCents,
			0,
			(string)$orderParams,
			0,
			(int)$host['up_host_id'],
			(int)$host['id'],
			'',
			'pending',
			'',
			'',
			'',
			$now,
		]
	);
	if (!$ok) {
		return ['ok' => false, 'order_no' => '', 'order_id' => 0, 'msg' => '订单写入失败'];
	}
	$row = $DB->get_row_prepare(
		"SELECT id FROM MN_plugin_zjmf_order WHERE order_no=? LIMIT 1",
		[$order_no]
	);
	return [
		'ok'       => true,
		'order_no' => $order_no,
		'order_id' => $row ? (int)$row['id'] : 0,
		'msg'      => '',
	];
}

/* ============================================================
 *  主机映射管理
 * ============================================================ */

/** 按 ID 查询主机映射。 */
function zjmf_host_get($host_id)
{
	global $DB;
	return $DB->get_row_prepare(
		"SELECT * FROM MN_plugin_zjmf_host WHERE id=? LIMIT 1",
		[(int)$host_id]
	) ?: null;
}

/** 校验主机归属当前用户后返回。 */
function zjmf_host_get_by_user($user_id, $host_id)
{
	global $DB;
	return $DB->get_row_prepare(
		"SELECT h.*, s.name AS supplier_name
		 FROM MN_plugin_zjmf_host h
		 LEFT JOIN MN_plugin_zjmf_supplier s ON s.id = h.supplier_id
		 WHERE h.id=? AND h.user_id=? LIMIT 1",
		[(int)$host_id, (int)$user_id]
	) ?: null;
}

/** 按上游主机 ID 查询映射。 */
function zjmf_host_get_by_up($up_host_id)
{
	global $DB;
	return $DB->get_row_prepare(
		"SELECT * FROM MN_plugin_zjmf_host WHERE up_host_id=? LIMIT 1",
		[(int)$up_host_id]
	) ?: null;
}

/** 用户主机列表。 */
function zjmf_host_list_by_user($user_id)
{
	global $DB;
	return $DB->get_all_prepare(
		"SELECT h.*, s.name AS supplier_name
		 FROM MN_plugin_zjmf_host h
		 LEFT JOIN MN_plugin_zjmf_supplier s ON s.id = h.supplier_id
		 WHERE h.user_id=? ORDER BY h.id DESC",
		[(int)$user_id]
	) ?: [];
}

/** 全部主机列表（管理员，分页，含供应商名）。 */
function zjmf_host_list_all($page = 1, $per_page = 30)
{
	global $DB;
	$page = max(1, (int)$page);
	$per_page = max(1, min(200, (int)$per_page));
	$offset = ($page - 1) * $per_page;
	$count_row = $DB->get_row_prepare("SELECT COUNT(*) AS cnt FROM MN_plugin_zjmf_host");
	$total = $count_row ? (int)$count_row['cnt'] : 0;
	$list = $DB->get_all_prepare(
		"SELECT h.*, u.username AS user_name, s.name AS supplier_name
		 FROM MN_plugin_zjmf_host h
		 LEFT JOIN MN_plugin_user u ON u.id = h.user_id
		 LEFT JOIN MN_plugin_zjmf_supplier s ON s.id = h.supplier_id
		 ORDER BY h.id DESC LIMIT {$offset},{$per_page}"
	) ?: [];
	return ['list' => $list, 'total' => $total, 'page' => $page, 'per_page' => $per_page];
}

/** 新增主机映射。 */
function zjmf_host_create($data)
{
	global $DB, $date;
	$now = $date ?: date('Y-m-d H:i:s');
	$ok = $DB->query_prepare(
		"INSERT INTO MN_plugin_zjmf_host
		 (supplier_id, user_id, order_id, up_host_id, up_product_id, name, username,
		  password, cycle, status, renew_date, created_at, updated_at)
		 VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)",
		[
			(int)($data['supplier_id'] ?? 0),
			(int)($data['user_id'] ?? 0),
			(int)($data['order_id'] ?? 0),
			(int)($data['up_host_id'] ?? 0),
			(int)($data['up_product_id'] ?? 0),
			(string)($data['name'] ?? ''),
			(string)($data['username'] ?? ''),
			(string)($data['password'] ?? ''),
			(string)($data['cycle'] ?? ''),
			(string)($data['status'] ?? 'active'),
			(string)($data['renew_date'] ?? ''),
			$now,
			$now,
		]
	);
	if (!$ok) {
		return 0;
	}
	$row = $DB->get_row_prepare(
		"SELECT id FROM MN_plugin_zjmf_host
		 WHERE order_id=? ORDER BY id DESC LIMIT 1",
		[(int)($data['order_id'] ?? 0)]
	);
	return $row ? (int)$row['id'] : 0;
}

/** 更新主机缓存信息（状态/到期/周期）。 */
function zjmf_host_update_cache($host_id, $data)
{
	global $DB, $date;
	$sets = [];
	$params = [];
	foreach (['status', 'renew_date', 'cycle', 'up_product_id'] as $key) {
		if (array_key_exists($key, $data)) {
			$sets[] = "`{$key}`=?";
			$params[] = (string)$data[$key];
		}
	}
	if ($sets === []) {
		return false;
	}
	$now = $date ?: date('Y-m-d H:i:s');
	$sets[] = "updated_at=?";
	$params[] = $now;
	$params[] = (int)$host_id;
	return (bool)$DB->query_prepare(
		"UPDATE MN_plugin_zjmf_host SET " . implode(',', $sets) . " WHERE id=?",
		$params
	);
}

/* ============================================================
 *  主机操作辅助
 * ============================================================ */

/** 操作标识 → 上游 func 名称（视上游模块而定，联调时按实际调整）。 */
function zjmf_action_func($action)
{
	$map = [
		'on'             => 'on',
		'off'            => 'off',
		'reboot'         => 'reboot',
		'reset_password' => 'passwd',
		'reinstall'      => 'reinstall',
	];
	return $map[$action] ?? '';
}

/** 操作成功后建议写入的缓存状态（空表示不修改）。 */
function zjmf_action_status($action)
{
	$map = [
		'on'     => 'active',
		'off'    => 'suspend',
		'reboot' => 'active',
	];
	return $map[$action] ?? '';
}

/** 用户端主机状态展示标签映射。 */
function zjmf_host_status_label($status)
{
	$map = [
		'active'  => '运行中',
		'suspend' => '已暂停',
		'unknown' => '未知',
	];
	return $map[$status] ?? $status;
}

/* ============================================================
 *  主机开通（核心编排）
 * ============================================================ */

/**
 * 支付成功后开通主机：调用上游开通，落库映射，失败自动退款。
 *
 * @param int $order_id  MN_plugin_zjmf_order.id
 * @return array ['ok'=>bool, 'msg'=>string, 'host_id'=>int]
 */
function zjmf_open_host($order_id)
{
	global $DB, $date;
	$order = zjmf_order_get($order_id);
	if (!$order) {
		return ['ok' => false, 'msg' => '订单不存在'];
	}
	if ($order['status'] !== 'paid') {
		return ['ok' => false, 'msg' => '订单状态非已支付，无法开通'];
	}
	// 幂等：已开通或已有映射跳过
	$existing = $DB->get_row_prepare(
		"SELECT id FROM MN_plugin_zjmf_host WHERE order_id=? LIMIT 1",
		[(int)$order_id]
	);
	if ($existing) {
		return ['ok' => true, 'msg' => '该订单已开通', 'host_id' => (int)$existing['id']];
	}

	// 供应商校验：缺失或停用时直接失败退款
	$supplier = zjmf_supplier_get((int)$order['supplier_id']);
	if (!$supplier || (int)$supplier['status'] !== 1) {
		$msg = '供应商不存在或已停用，无法开通';
		zjmf_order_set_status($order_id, 'failed', $msg);
		zjmf_log((int)$order['user_id'], $order['order_no'], 'purchase',
			'failed', json_encode(['msg' => $msg], JSON_UNESCAPED_UNICODE),
			(int)$order['supplier_id']);
		$amount = (int)$order['amount_cents'];
		if ($amount > 0 && function_exists('balance_add')) {
			balance_add((int)$order['user_id'], $amount, 'refund',
				$order['order_no'], '开通失败自动退款');
		}
		return ['ok' => false, 'msg' => $msg];
	}

	// 调用上游开通（代理商直通，按订单供应商路由）
	$result = ZjmfUpstream::purchase($order, $supplier);
	if (empty($result['ok'])) {
		$msg = (string)($result['msg'] ?? '上游开通失败');
		zjmf_order_set_status($order_id, 'failed', $msg);
		zjmf_log((int)$order['user_id'], $order['order_no'],
			'purchase', 'failed', json_encode(['msg' => $msg], JSON_UNESCAPED_UNICODE),
			(int)$order['supplier_id']);
		// 自动原路退回余额
		$amount = (int)$order['amount_cents'];
		if ($amount > 0 && function_exists('balance_add')) {
			balance_add((int)$order['user_id'], $amount, 'refund',
				$order['order_no'], '开通失败自动退款');
		}
		return ['ok' => false, 'msg' => $msg];
	}

	$now = $date ?: date('Y-m-d H:i:s');
	$upHostId = (int)($result['up_host_id'] ?? 0);
	$username = (string)($result['username'] ?? '');
	$password = (string)($result['password'] ?? '');
	$upOrderId = (int)($result['up_order_id'] ?? 0);

	// 回填订单
	zjmf_order_fill_opened($order_id, $upOrderId, $upHostId, $username);
	zjmf_order_set_status($order_id, 'opened', '主机已开通');

	// 写主机映射
	$hostId = zjmf_host_create([
		'supplier_id'    => (int)$order['supplier_id'],
		'user_id'        => (int)$order['user_id'],
		'order_id'       => (int)$order_id,
		'up_host_id'     => $upHostId,
		'up_product_id'  => (int)$order['up_product_id'],
		'name'           => (string)($result['name'] ?? $order['product_name']),
		'username'       => $username,
		'password'       => $password !== '' ? zjmf_encrypt($password) : '',
		'cycle'          => $order['cycle'],
		'status'         => 'active',
		'renew_date'     => (string)($result['renew_date'] ?? ''),
	]);

	zjmf_log((int)$order['user_id'], $order['order_no'],
		'purchase', 'success',
		json_encode([
			'up_order_id' => $upOrderId,
			'up_host_id'  => $upHostId,
			'username'    => $username,
		], JSON_UNESCAPED_UNICODE),
		(int)$order['supplier_id']);

	return ['ok' => true, 'msg' => '开通成功', 'host_id' => $hostId];
}
