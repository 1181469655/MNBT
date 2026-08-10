<?php
/**
 * docker_shop 插件 - 核心函数库
 *
 * 提供：售卖套餐 CRUD、Docker 节点/配额套餐查询、订单管理、资产查询、Docker 账号开通。
 * 依赖 user_info 插件（认证）、balance 插件（余额扣款）、核心 Docker 表（MN_docker_user/plan/node）。
 */

if (!defined('IN_CRONLITE')) {
	exit;
}

/* ============================================================
 *  周期 / schema 配置
 * ============================================================ */

/** 可用购买周期定义（months 用于计算到期时间）。 */
function docker_shop_periods()
{
	return [
		'month'       => ['label' => '月付', 'months' => 1],
		'quarter'     => ['label' => '季付', 'months' => 3],
		'half_year'   => ['label' => '半年付', 'months' => 6],
		'year'        => ['label' => '年付', 'months' => 12],
		'two_year'    => ['label' => '两年付', 'months' => 24],
		'three_year'  => ['label' => '三年付', 'months' => 36],
	];
}

/** 周期到数据库价格字段的映射。 */
function docker_shop_period_price_field($period)
{
	$map = [
		'month'      => 'price_month_cents',
		'quarter'    => 'price_quarter_cents',
		'half_year'  => 'price_half_year_cents',
		'year'       => 'price_year_cents',
		'two_year'   => 'price_two_year_cents',
		'three_year' => 'price_three_year_cents',
	];
	return $map[$period] ?? '';
}

/** 获取售卖套餐启用的购买周期列表。 */
function docker_shop_plan_enabled_periods($plan)
{
	$periods = docker_shop_periods();
	$enabled = [];
	$raw = isset($plan['enabled_periods']) ? trim((string)$plan['enabled_periods']) : '';
	if ($raw === '') {
		// 兼容旧数据：只要对应价格 > 0 就认为启用
		foreach (array_keys($periods) as $p) {
			$field = docker_shop_period_price_field($p);
			if ($field && (int)($plan[$field] ?? 0) > 0) {
				$enabled[] = $p;
			}
		}
		return $enabled;
	}
	foreach (explode(',', $raw) as $p) {
		$p = trim($p);
		if (isset($periods[$p])) {
			$enabled[] = $p;
		}
	}
	return $enabled;
}

/** 自动升级售卖套餐表结构（对已经安装的插件追加新字段）。 */
function docker_shop_upgrade_schema()
{
	global $DB;
	if (!isset($DB)) {
		return;
	}
	$cols = $DB->get_all_prepare("SHOW COLUMNS FROM MN_plugin_docker_plan") ?: [];
	$existing = [];
	foreach ($cols as $c) {
		$existing[] = $c['Field'];
	}
	$toAdd = [];
	if (!in_array('category', $existing, true)) {
		$toAdd[] = "ADD COLUMN `category` varchar(50) NOT NULL DEFAULT '' COMMENT '分类'";
	}
	if (!empty($toAdd)) {
		$DB->query("ALTER TABLE MN_plugin_docker_plan " . implode(', ', $toAdd));
	}
}

/* ============================================================
 *  URL / 渲染辅助
 * ============================================================ */

/** 生成带站点 base path 前缀的 URL。 */
function docker_shop_url($path = '')
{
	$scriptName = isset($_SERVER['SCRIPT_NAME']) ? str_replace('\\', '/', $_SERVER['SCRIPT_NAME']) : '';
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

/** 管理员端插件页面 URL（admin/plugin.php?p=docker_shop&page=xxx）。 */
function docker_shop_admin_url($page, $extra = '')
{
	$base = 'plugin.php?p=docker_shop&page=' . rawurlencode($page);
	if ($extra !== '') {
		$base .= '&' . ltrim($extra, '&');
	}
	return $base;
}

/** 金额（分）→ 元（保留 2 位小数）。 */
function docker_shop_format_cents($cents)
{
	return number_format((int)$cents / 100, 2, '.', '');
}

/** 输出 JSON 并退出（与 user_info/balance 插件一致：{ code, ...extra }）。 */
function docker_shop_json($code, $extra = [])
{
	@header('Content-Type: application/json; charset=UTF-8');
	$payload = ['code' => $code];
	if (is_array($extra)) {
		$payload = array_merge($payload, $extra);
	}
	echo json_encode($payload, JSON_UNESCAPED_UNICODE);
	exit;
}

/** 获取当前登录的 user_info 用户，未登录跳转登录页。 */
function docker_shop_require_user()
{
	if (!function_exists('user_info_auth_current')) {
		http_response_code(500);
		echo '需要先启用 user_info 插件';
		exit;
	}
	$user = user_info_auth_current();
	if (!$user) {
		header('Location: ' . docker_shop_url('account/login'));
		exit;
	}
	return $user;
}

/** 渲染用户端视图。 */
function docker_shop_render($view, $vars = [])
{
	$vars['current_user'] = $vars['current_user'] ?? (function_exists('user_info_auth_current') ? user_info_auth_current() : null);
	extract($vars, EXTR_SKIP);
	$viewFile = mnbt_plugin_path('docker_shop') . 'views/' . $view . '.php';
	if (!is_file($viewFile)) {
		http_response_code(500);
		echo 'View not found: ' . htmlspecialchars($view);
		return;
	}
	include $viewFile;
}

/** 渲染管理员端视图。 */
function docker_shop_render_admin($view, $vars = [])
{
	extract($vars, EXTR_SKIP);
	$viewFile = mnbt_plugin_path('docker_shop') . 'views/admin/' . $view . '.php';
	if (!is_file($viewFile)) {
		http_response_code(500);
		echo 'Admin view not found: ' . htmlspecialchars($view);
		return;
	}
	include $viewFile;
}

/* ============================================================
 *  节点 / 配额套餐查询
 * ============================================================ */

/** 可用 Docker 节点列表（用户端，仅返回 id/名称，不暴露 IP/端口/密钥）。 */
function docker_shop_node_list()
{
	global $DB;
	return $DB->get_all_prepare("SELECT id, name FROM MN_docker_node WHERE qk='true' ORDER BY id ASC") ?: [];
}

/** 获取单个 Docker 节点。 */
function docker_shop_node_get($node_id)
{
	global $DB;
	return $DB->get_row_prepare("SELECT * FROM MN_docker_node WHERE id=? LIMIT 1", [(int)$node_id]) ?: null;
}

/** 全部 Docker 节点列表（管理员端，含完整信息）。 */
function docker_shop_node_list_all()
{
	global $DB;
	return $DB->get_all_prepare("SELECT * FROM MN_docker_node ORDER BY id ASC") ?: [];
}

/** 上架中的配额套餐列表（管理员下拉用）。 */
function docker_shop_base_plan_list()
{
	global $DB;
	return $DB->get_all_prepare("SELECT * FROM MN_docker_plan WHERE qk='true' ORDER BY id ASC") ?: [];
}

/** 获取单个配额套餐。 */
function docker_shop_base_plan_get($plan_id)
{
	global $DB;
	return $DB->get_row_prepare("SELECT * FROM MN_docker_plan WHERE id=? LIMIT 1", [(int)$plan_id]) ?: null;
}

/* ============================================================
 *  售卖套餐管理
 * ============================================================ */

/** 获取单个售卖套餐。 */
function docker_shop_plan_get($plan_id)
{
	global $DB;
	return $DB->get_row_prepare("SELECT * FROM MN_plugin_docker_plan WHERE id=? LIMIT 1", [(int)$plan_id]) ?: null;
}

/** 获取上架售卖套餐列表（按 sort 升序）。 */
function docker_shop_plan_list_active()
{
	global $DB;
	return $DB->get_all_prepare("SELECT * FROM MN_plugin_docker_plan WHERE status='active' ORDER BY sort ASC, id ASC") ?: [];
}

/** 获取全部售卖套餐列表（管理员）。 */
function docker_shop_plan_list_all()
{
	global $DB;
	return $DB->get_all_prepare("SELECT * FROM MN_plugin_docker_plan ORDER BY sort ASC, id ASC") ?: [];
}

/** 保存售卖套餐（新增或更新）。 */
function docker_shop_plan_save($data)
{
	global $DB, $date;
	$now = $date ?: date('Y-m-d H:i:s');

	$periods = docker_shop_periods();
	$enabled = [];
	$rawEnabled = isset($data['enabled_periods']) && is_array($data['enabled_periods']) ? $data['enabled_periods'] : [];
	foreach ($rawEnabled as $p) {
		if (isset($periods[$p])) {
			$enabled[] = $p;
		}
	}

	$fields = [
		'name' => trim((string)($data['name'] ?? '')),
		'description' => trim((string)($data['description'] ?? '')),
		'category' => trim((string)($data['category'] ?? '')),
		'node' => max(0, (int)($data['node'] ?? 0)),
		'base_plan_id' => max(0, (int)($data['base_plan_id'] ?? 0)),
		'price_month_cents' => max(0, (int)($data['price_month_cents'] ?? 0)),
		'price_quarter_cents' => max(0, (int)($data['price_quarter_cents'] ?? 0)),
		'price_half_year_cents' => max(0, (int)($data['price_half_year_cents'] ?? 0)),
		'price_year_cents' => max(0, (int)($data['price_year_cents'] ?? 0)),
		'price_two_year_cents' => max(0, (int)($data['price_two_year_cents'] ?? 0)),
		'price_three_year_cents' => max(0, (int)($data['price_three_year_cents'] ?? 0)),
		'enabled_periods' => implode(',', $enabled),
		'status' => ($data['status'] ?? 'active') === 'inactive' ? 'inactive' : 'active',
		'sort' => max(0, (int)($data['sort'] ?? 50)),
		'updated_at' => $now,
	];
	if ($fields['name'] === '') {
		return '套餐名称不能为空';
	}
	if ($fields['node'] <= 0 || !docker_shop_node_get($fields['node'])) {
		return '请选择有效的开通节点';
	}
	if ($fields['base_plan_id'] <= 0 || !docker_shop_base_plan_get($fields['base_plan_id'])) {
		return '请选择有效的配额套餐';
	}
	// 至少启用一个有效周期
	if ($enabled === []) {
		return '请至少选择一个购买周期';
	}

	$id = (int)($data['id'] ?? 0);
	if ($id > 0) {
		$ok = $DB->query_prepare(
			"UPDATE MN_plugin_docker_plan SET name=?, description=?, category=?, node=?, base_plan_id=?, price_month_cents=?, price_quarter_cents=?, price_half_year_cents=?, price_year_cents=?, price_two_year_cents=?, price_three_year_cents=?, enabled_periods=?, status=?, sort=?, updated_at=? WHERE id=?",
			[$fields['name'], $fields['description'], $fields['category'], $fields['node'], $fields['base_plan_id'], $fields['price_month_cents'], $fields['price_quarter_cents'], $fields['price_half_year_cents'], $fields['price_year_cents'], $fields['price_two_year_cents'], $fields['price_three_year_cents'], $fields['enabled_periods'], $fields['status'], $fields['sort'], $fields['updated_at'], $id]
		);
		return $ok ? true : '更新失败';
	}
	$fields['created_at'] = $now;
	$ok = $DB->query_prepare(
		"INSERT INTO MN_plugin_docker_plan (name, description, category, node, base_plan_id, price_month_cents, price_quarter_cents, price_half_year_cents, price_year_cents, price_two_year_cents, price_three_year_cents, enabled_periods, status, sort, created_at, updated_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)",
		[$fields['name'], $fields['description'], $fields['category'], $fields['node'], $fields['base_plan_id'], $fields['price_month_cents'], $fields['price_quarter_cents'], $fields['price_half_year_cents'], $fields['price_year_cents'], $fields['price_two_year_cents'], $fields['price_three_year_cents'], $fields['enabled_periods'], $fields['status'], $fields['sort'], $fields['created_at'], $fields['updated_at']]
	);
	return $ok ? true : '新增失败';
}

/** 删除售卖套餐。 */
function docker_shop_plan_delete($plan_id)
{
	global $DB;
	return (bool)$DB->query_prepare("DELETE FROM MN_plugin_docker_plan WHERE id=? LIMIT 1", [(int)$plan_id]);
}

/** 构造用户端可用的售卖套餐 JSON（含基准配额信息）。 */
function docker_shop_plan_to_api($plan)
{
	$base = $plan['base_plan_id'] > 0 ? docker_shop_base_plan_get($plan['base_plan_id']) : null;
	$node = $plan['node'] > 0 ? docker_shop_node_get($plan['node']) : null;
	return [
		'id'           => (int)$plan['id'],
		'name'         => (string)$plan['name'],
		'description'  => (string)($plan['description'] ?? ''),
		'category'     => (string)($plan['category'] ?? ''),
		'base_plan'    => $base ? [
			'id'       => (int)$base['id'],
			'name'     => (string)$base['name'],
			'cpu_max'  => (string)$base['cpu_max'],
			'mem_max'  => (string)$base['mem_max'],
			'disk_max' => (string)$base['disk_max'],
			'proxy_max'=> (string)$base['proxy_max'],
		] : null,
		'node'         => $node ? ['id' => (int)$node['id'], 'name' => (string)$node['name']] : null,
		'periods'      => docker_shop_plan_enabled_periods($plan),
		'prices'       => [
			'month'      => (int)($plan['price_month_cents'] ?? 0),
			'quarter'    => (int)($plan['price_quarter_cents'] ?? 0),
			'half_year'  => (int)($plan['price_half_year_cents'] ?? 0),
			'year'       => (int)($plan['price_year_cents'] ?? 0),
			'two_year'   => (int)($plan['price_two_year_cents'] ?? 0),
			'three_year' => (int)($plan['price_three_year_cents'] ?? 0),
		],
	];
}

/* ============================================================
 *  订单管理
 * ============================================================ */

/** 按订单号查询订单。 */
function docker_shop_order_get_by_no($order_no)
{
	global $DB;
	return $DB->get_row_prepare("SELECT * FROM MN_plugin_docker_order WHERE order_no=? LIMIT 1", [$order_no]) ?: null;
}

/** 按 ID 查询订单。 */
function docker_shop_order_get($order_id)
{
	global $DB;
	return $DB->get_row_prepare("SELECT * FROM MN_plugin_docker_order WHERE id=? LIMIT 1", [(int)$order_id]) ?: null;
}

/** 用户的订单列表（分页）。 */
function docker_shop_order_list_by_user($user_id, $page = 1, $per_page = 20)
{
	global $DB;
	$user_id = (int)$user_id;
	$page = max(1, (int)$page);
	$per_page = max(1, min(100, (int)$per_page));
	$offset = ($page - 1) * $per_page;
	$count_row = $DB->get_row_prepare("SELECT COUNT(*) AS cnt FROM MN_plugin_docker_order WHERE user_id=?", [$user_id]);
	$total = $count_row ? (int)$count_row['cnt'] : 0;
	$list = $DB->get_all_prepare(
		"SELECT * FROM MN_plugin_docker_order WHERE user_id=? ORDER BY id DESC LIMIT {$offset},{$per_page}",
		[$user_id]
	) ?: [];
	return ['list' => $list, 'total' => $total, 'page' => $page, 'per_page' => $per_page];
}

/** 全部订单列表（管理员，分页 + 简单筛选）。 */
function docker_shop_order_list_all($page = 1, $per_page = 30, $filters = [])
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
	if (!empty($filters['user_id'])) {
		$where .= ' AND user_id=?';
		$params[] = (int)$filters['user_id'];
	}
	if (!empty($filters['order_no'])) {
		$where .= ' AND order_no=?';
		$params[] = $filters['order_no'];
	}

	$count_row = $DB->get_row_prepare("SELECT COUNT(*) AS cnt FROM MN_plugin_docker_order WHERE {$where}", $params);
	$total = $count_row ? (int)$count_row['cnt'] : 0;
	$list = $DB->get_all_prepare(
		"SELECT * FROM MN_plugin_docker_order WHERE {$where} ORDER BY id DESC LIMIT {$offset},{$per_page}",
		$params
	) ?: [];
	return ['list' => $list, 'total' => $total, 'page' => $page, 'per_page' => $per_page];
}

/**
 * 创建购买订单（未支付）。
 *
 * @param array $user      user_info 当前用户
 * @param array $plan      售卖套餐行
 * @param string $period   购买周期
 * @return array ['ok'=>bool, 'order_no'=>string, 'order_id'=>int, 'amount_cents'=>int, 'msg'=>string]
 */
function docker_shop_order_create($user, $plan, $period)
{
	global $DB, $date;
	$periods = docker_shop_periods();
	if (!isset($periods[$period])) {
		return ['ok' => false, 'msg' => '无效的购买周期'];
	}
	$enabled = docker_shop_plan_enabled_periods($plan);
	if (!in_array($period, $enabled, true)) {
		return ['ok' => false, 'msg' => '该套餐不支持此购买周期'];
	}
	$field = docker_shop_period_price_field($period);
	$amount_cents = $field ? (int)($plan[$field] ?? 0) : 0;
	if ($amount_cents < 0) {
		return ['ok' => false, 'msg' => '该套餐此周期价格异常'];
	}
	$node = (int)($plan['node'] ?? 0);
	$base_plan_id = (int)($plan['base_plan_id'] ?? 0);
	if ($node <= 0 || !docker_shop_node_get($node)) {
		return ['ok' => false, 'msg' => '套餐未配置有效开通节点'];
	}
	if ($base_plan_id <= 0 || !docker_shop_base_plan_get($base_plan_id)) {
		return ['ok' => false, 'msg' => '套餐未配置有效配额套餐'];
	}
	$now = $date ?: date('Y-m-d H:i:s');
	$order_no = date("YmdHis") . mt_rand(1000, 9999);
	$ok = $DB->query_prepare(
		"INSERT INTO MN_plugin_docker_order (user_id, plan_id, plan_name, period, amount_cents, order_no, node, docker_user_id, status, remark, created_at, paid_at, opened_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)",
		[(int)$user['id'], (int)$plan['id'], $plan['name'], $period, $amount_cents, $order_no, $node, 0, 'pending', '', $now, '', '']
	);
	if (!$ok) {
		return ['ok' => false, 'msg' => '订单写入失败'];
	}
	// 取自增 ID
	$row = $DB->get_row_prepare("SELECT id FROM MN_plugin_docker_order WHERE order_no=? LIMIT 1", [$order_no]);
	$order_id = $row ? (int)$row['id'] : 0;
	return ['ok' => true, 'order_no' => $order_no, 'order_id' => $order_id, 'amount_cents' => $amount_cents];
}

/** 更新订单状态。 */
function docker_shop_order_set_status($order_id, $status, $remark = '')
{
	global $DB, $date;
	$now = $date ?: date('Y-m-d H:i:s');
	$extra = '';
	$params = [$status];
	if ($status === 'paid') {
		$extra = ', paid_at=?';
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
		"UPDATE MN_plugin_docker_order SET status=?{$extra} WHERE id=?",
		$params
	);
}

/* ============================================================
 *  资产管理
 * ============================================================ */

/** 用户资产列表（含 MN_docker_user 状态冗余）。 */
function docker_shop_asset_list_by_user($user_id)
{
	global $DB;
	return $DB->get_all_prepare(
		"SELECT a.*, d.container_status, d.container_id, d.service_name, d.disk_usage, d.disk_usage_at, d.qk AS docker_qk, d.data AS docker_created_at,
		        n.name AS node_name
		 FROM MN_plugin_docker_asset a
		 LEFT JOIN MN_docker_user d ON d.id = a.docker_user_id
		 LEFT JOIN MN_docker_node n ON n.id = d.ssbt
		 WHERE a.user_id=?
		 ORDER BY a.id DESC",
		[(int)$user_id]
	) ?: [];
}

/** 全部资产列表（管理员，分页）。 */
function docker_shop_asset_list_all($page = 1, $per_page = 30)
{
	global $DB;
	$page = max(1, (int)$page);
	$per_page = max(1, min(200, (int)$per_page));
	$offset = ($page - 1) * $per_page;
	$count_row = $DB->get_row_prepare("SELECT COUNT(*) AS cnt FROM MN_plugin_docker_asset WHERE 1");
	$total = $count_row ? (int)$count_row['cnt'] : 0;
	$list = $DB->get_all_prepare(
		"SELECT a.*, d.container_status, d.container_id, d.service_name, d.disk_usage, d.disk_usage_at, d.qk AS docker_qk, d.data AS docker_created_at,
		        n.name AS node_name
		 FROM MN_plugin_docker_asset a
		 LEFT JOIN MN_docker_user d ON d.id = a.docker_user_id
		 LEFT JOIN MN_docker_node n ON n.id = d.ssbt
		 ORDER BY a.id DESC LIMIT {$offset},{$per_page}"
	) ?: [];
	return ['list' => $list, 'total' => $total, 'page' => $page, 'per_page' => $per_page];
}

/* ============================================================
 *  Docker 账号开通（核心）
 * ============================================================ */

/**
 * 开通 Docker 账号：写入 MN_docker_user + 资产表，回填订单。
 *
 * @param int $order_id  MN_plugin_docker_order.id
 * @return array ['ok'=>bool, 'msg'=>string, 'docker_user_id'=>int, 'docker_username'=>string, 'docker_password'=>string]
 */
function docker_shop_open_account($order_id)
{
	global $DB, $date, $conf;
	$order = docker_shop_order_get($order_id);
	if (!$order) {
		return ['ok' => false, 'msg' => '订单不存在'];
	}
	if ($order['status'] !== 'paid') {
		return ['ok' => false, 'msg' => '订单状态非已支付，无法开通'];
	}
	if ((int)$order['docker_user_id'] > 0) {
		return ['ok' => false, 'msg' => '该订单已开通', 'docker_user_id' => (int)$order['docker_user_id']];
	}
	$plan = docker_shop_plan_get($order['plan_id']);
	if (!$plan) {
		docker_shop_order_set_status($order_id, 'failed', '售卖套餐不存在');
		return ['ok' => false, 'msg' => '售卖套餐不存在'];
	}
	$node = docker_shop_node_get($order['node']);
	if (!$node) {
		docker_shop_order_set_status($order_id, 'failed', '节点不存在或已停用');
		return ['ok' => false, 'msg' => '节点不存在或已停用'];
	}
	$base_plan = docker_shop_base_plan_get($plan['base_plan_id']);
	if (!$base_plan) {
		docker_shop_order_set_status($order_id, 'failed', '配额套餐不存在');
		return ['ok' => false, 'msg' => '配额套餐不存在'];
	}

	// 依赖核心 Docker 认证函数（bcrypt 哈希）
	if (!function_exists('docker_auth_password_hash')) {
		$memberFile = ROOT . 'MPHX/docker.member.php';
		if (is_file($memberFile)) {
			include_once $memberFile;
		}
	}
	if (!function_exists('docker_auth_password_hash')) {
		docker_shop_order_set_status($order_id, 'failed', 'Docker 认证组件缺失');
		return ['ok' => false, 'msg' => 'Docker 认证组件缺失，请联系管理员'];
	}

	// 生成 Docker 登录名：优先 user_info username；冲突则追加随机后缀
	$user_row = $DB->get_row_prepare("SELECT * FROM MN_plugin_user WHERE id=? LIMIT 1", [(int)$order['user_id']]);
	$base_username = $user_row ? (string)($user_row['username'] ?? '') : '';
	if ($base_username === '' || !docker_shop_docker_username_available($base_username)) {
		$base_username = ($base_username === '' ? 'dn' . $order['user_id'] : $base_username) . '_' . substr(md5($order['order_no'] . mt_rand(100, 999)), 0, 4);
	}
	$docker_username = $base_username;

	// 生成随机密码（12 位）
	$docker_password = docker_shop_random_password(12);

	// 计算到期时间
	$periods = docker_shop_periods();
	$periodCfg = $periods[$order['period']] ?? $periods['month'];
	$datae = date('Y-m-d', strtotime('+' . (int)$periodCfg['months'] . ' months', time()));

	$now = $date ?: date('Y-m-d H:i:s');
	$hash = docker_auth_password_hash($docker_password);

	$ok = $DB->query_prepare(
		"INSERT INTO MN_docker_user (username, password_hash, email, ssbt, data, datae, qk, plan_id, container_status, created_at) VALUES (?,?,?,?,?,?,'active',?, 'none',?)",
		[$docker_username, $hash, ($user_row['email'] ?? ''), (int)$node['id'], $now, $datae, (int)$base_plan['id'], $now]
	);
	if (!$ok) {
		docker_shop_order_set_status($order_id, 'failed', 'Docker 账号写入失败');
		return ['ok' => false, 'msg' => 'Docker 账号写入失败，请联系管理员'];
	}
	$docker_row = $DB->get_row_prepare("SELECT id FROM MN_docker_user WHERE username=? LIMIT 1", [$docker_username]);
	$docker_user_id = $docker_row ? (int)$docker_row['id'] : 0;

	// 回填订单
	docker_shop_order_set_status($order_id, 'opened', 'Docker 账号已开通：' . $docker_username);
	if ($docker_user_id > 0) {
		$DB->query_prepare("UPDATE MN_plugin_docker_order SET docker_user_id=? WHERE id=?", [$docker_user_id, $order_id]);
	}

	// 写入资产表（明文账号密码，同 MN_zj.user/pass 惯例）
	$DB->query_prepare(
		"INSERT INTO MN_plugin_docker_asset (user_id, order_id, docker_user_id, plan_id, plan_name, docker_username, docker_password, expire_at, status, created_at) VALUES (?,?,?,?,?,?,?,?,?,?)",
		[(int)$order['user_id'], $order_id, $docker_user_id, (int)$plan['id'], $plan['name'], $docker_username, $docker_password, $datae, 'active', $now]
	);

	// 触发 docker.user.created 钩子（与 api/docker.php kt 保持一致）
	if (function_exists('mnbt_do_action')) {
		mnbt_do_action('docker.user.created', [
			'username' => $docker_username,
			'ssbt'     => (int)$node['id'],
			'plan_id'  => (int)$base_plan['id'],
			'user_id'  => (int)$order['user_id'],
		], ['source' => 'docker_shop', 'order_id' => $order_id]);
	}

	return [
		'ok' => true,
		'msg' => '开通成功',
		'docker_user_id' => $docker_user_id,
		'docker_username' => $docker_username,
		'docker_password' => $docker_password,
		'expire' => $datae,
	];
}

/** 校验 Docker 登录名是否可用（全局唯一）。 */
function docker_shop_docker_username_available($username)
{
	global $DB;
	return !(bool)$DB->get_row_prepare("SELECT id FROM MN_docker_user WHERE username=? LIMIT 1", [$username]);
}

/** 生成随机密码。 */
function docker_shop_random_password($len = 12)
{
	$chars = 'abcdefghijkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789';
	$str = '';
	$max = strlen($chars) - 1;
	for ($i = 0; $i < $len; $i++) {
		$str .= $chars[mt_rand(0, $max)];
	}
	return $str;
}

/**
 * 重置 Docker 账号密码（更新 MN_docker_user.password_hash，旧 docker_token 自动失效）。
 *
 * @param int $asset_id  MN_plugin_docker_asset.id
 * @return array ['ok'=>bool, 'msg'=>string, 'password'=>string]
 */
function docker_shop_reset_password($asset_id)
{
	global $DB;
	$asset = $DB->get_row_prepare("SELECT * FROM MN_plugin_docker_asset WHERE id=? LIMIT 1", [(int)$asset_id]);
	if (!$asset) {
		return ['ok' => false, 'msg' => '资产不存在'];
	}
	if ((int)$asset['docker_user_id'] <= 0) {
		return ['ok' => false, 'msg' => 'Docker 账号未开通'];
	}
	if (!function_exists('docker_auth_password_hash')) {
		$memberFile = ROOT . 'MPHX/docker.member.php';
		if (is_file($memberFile)) {
			include_once $memberFile;
		}
	}
	if (!function_exists('docker_auth_password_hash')) {
		return ['ok' => false, 'msg' => 'Docker 认证组件缺失'];
	}
	$password = docker_shop_random_password(12);
	$hash = docker_auth_password_hash($password);
	$ok = $DB->query_prepare("UPDATE MN_docker_user SET password_hash=? WHERE id=?", [$hash, (int)$asset['docker_user_id']]);
	if (!$ok) {
		return ['ok' => false, 'msg' => '密码重置失败'];
	}
	// 同步资产表明文
	$DB->query_prepare("UPDATE MN_plugin_docker_asset SET docker_password=? WHERE id=?", [$password, (int)$asset_id]);
	// 记录日志
	if (function_exists('mnbt_log')) {
		mnbt_log($asset['docker_username'] ?: 'Docker', 'Docker账号', '用户自助重置密码', '重置成功', $DB);
	}
	return ['ok' => true, 'msg' => '重置成功', 'password' => $password];
}

/**
 * 同步 Docker 容器状态到资产（构造 bt_docker 查询已安装应用，逻辑同 docker/ajax.php my_container）。
 * 失败不阻断，返回数据库中的状态作为兜底。
 *
 * @param array $asset MN_plugin_docker_asset 行
 * @return array 资产行（含 container_status / container_id / service_name / disk_usage）
 */
function docker_shop_sync_container_status($asset)
{
	global $DB, $date;
	$docker_user = $DB->get_row_prepare("SELECT * FROM MN_docker_user WHERE id=? LIMIT 1", [(int)$asset['docker_user_id']]);
	if (!$docker_user) {
		return $asset;
	}
	$node = docker_shop_node_get((int)$docker_user['ssbt']);
	if (!$node || $node['qk'] !== 'true') {
		return $asset;
	}
	// 复用 Docker 控制台的查找辅助函数（docker/head.php）
	if (!function_exists('docker_find_my_installed_app')) {
		$headFile = ROOT . 'docker/head.php';
		if (is_file($headFile)) {
			include_once $headFile;
		}
	}
	if (!function_exists('docker_find_my_installed_app')) {
		return $asset;
	}
	$url = ($node['ptl'] === 'true' ? 'https' : 'http') . '://' . $node['btip'] . ':' . $node['btdk'];
	$btFile = ROOT . 'MPHX/bt_docker.php';
	if (!is_file($btFile)) {
		return $asset;
	}
	require_once $btFile;
	try {
		$bt = new bt_docker($url, $node['btmy']);
		$apps = $bt->installed_apps();
		$container = docker_find_my_installed_app($docker_user, $apps);
		if (!$container) {
			$DB->query_prepare("UPDATE MN_docker_user SET container_id=NULL, container_status='none', service_name=NULL WHERE id=?", [$docker_user['id']]);
			$asset['container_status'] = 'none';
			return $asset;
		}
		$status = strtolower((string)($container['status'] ?? ''));
		$mapped = 'running';
		if (strpos($status, 'exit') !== false || strpos($status, 'stop') !== false) $mapped = 'stopped';
		elseif (strpos($status, 'creat') !== false) $mapped = 'creating';
		elseif (strpos($status, 'run') !== false) $mapped = 'running';
		$cid = substr((string)($container['container_id'] ?? ''), 0, 64);
		$sn = substr((string)($container['service_name'] ?? ''), 0, 64);
		$DB->query_prepare("UPDATE MN_docker_user SET container_id=?, container_status=?, service_name=? WHERE id=?", [$cid, $mapped, $sn, $docker_user['id']]);
		// 磁盘用量采集
		$containerPath = (string)($container['path'] ?? '');
		if ($containerPath !== '') {
			$sizeResult = $bt->get_path_size($containerPath);
			$diskSize = (int)($sizeResult['size'] ?? 0);
			if ($diskSize > 0) {
				$DB->query_prepare("UPDATE MN_docker_user SET disk_usage=?, disk_usage_at=? WHERE id=?", [$diskSize, $date, $docker_user['id']]);
				$asset['disk_usage'] = $diskSize;
			}
		}
		$asset['container_id'] = $cid;
		$asset['container_status'] = $mapped;
		$asset['service_name'] = $sn;
	} catch (Throwable $e) {
		@error_log('[docker_shop] sync_container failed: ' . $e->getMessage());
	}
	return $asset;
}
