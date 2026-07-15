<?php
/**
 * hosting_shop 插件 - 核心函数库
 *
 * 提供：套餐 CRUD、节点列表、订单管理、资产查询、宝塔主机开通。
 * 依赖 user_info 插件（认证）、balance 插件（余额扣款）。
 */

if (!defined('IN_CRONLITE')) {
	exit;
}

/* ============================================================
 *  URL / 渲染辅助
 * ============================================================ */

/** 生成带站点 base path 前缀的 URL。 */
function hosting_url($path = '')
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

/** 插件静态资源 URL。 */
function hosting_asset_url($path = '')
{
	return mnbt_plugin_url('hosting_shop', ltrim($path, '/'));
}

/** 管理员端插件页面 URL（admin/plugin.php?p=hosting_shop&page=xxx）。 */
function hosting_admin_url($page, $extra = '')
{
	$base = 'plugin.php?p=hosting_shop&page=' . rawurlencode($page);
	if ($extra !== '') {
		$base .= '&' . ltrim($extra, '&');
	}
	return $base;
}

/** 金额（分）→ 元（保留 2 位小数）。 */
function hosting_format_cents($cents)
{
	return number_format((int)$cents / 100, 2, '.', '');
}

/** 获取当前登录的 user_info 用户，未登录跳转登录页。 */
function hosting_require_user()
{
	if (!function_exists('user_info_auth_current')) {
		http_response_code(500);
		echo '需要先启用 user_info 插件';
		exit;
	}
	$user = user_info_auth_current();
	if (!$user) {
		header('Location: ' . hosting_url('account/login'));
		exit;
	}
	return $user;
}

/** 渲染用户端视图。 */
function hosting_render($view, $vars = [])
{
	$vars['current_user'] = $vars['current_user'] ?? (function_exists('user_info_auth_current') ? user_info_auth_current() : null);
	extract($vars, EXTR_SKIP);
	$viewFile = mnbt_plugin_path('hosting_shop') . 'views/' . $view . '.php';
	if (!is_file($viewFile)) {
		http_response_code(500);
		echo 'View not found: ' . htmlspecialchars($view);
		return;
	}
	include $viewFile;
}

/** 渲染管理员端视图。 */
function hosting_render_admin($view, $vars = [])
{
	extract($vars, EXTR_SKIP);
	$viewFile = mnbt_plugin_path('hosting_shop') . 'views/admin/' . $view . '.php';
	if (!is_file($viewFile)) {
		http_response_code(500);
		echo 'Admin view not found: ' . htmlspecialchars($view);
		return;
	}
	include $viewFile;
}

/** 输出 JSON 并退出。 */
function hosting_json($code, $extra = [])
{
	@header('Content-Type: application/json; charset=UTF-8');
	$payload = ['code' => $code];
	if (is_array($extra)) {
		$payload = array_merge($payload, $extra);
	}
	echo json_encode($payload, JSON_UNESCAPED_UNICODE);
	exit;
}

/* ============================================================
 *  套餐管理
 * ============================================================ */

/** 获取单个套餐。 */
function hosting_plan_get($plan_id)
{
	global $DB;
	return $DB->get_row_prepare("SELECT * FROM MN_plugin_hosting_plan WHERE id=? LIMIT 1", [(int)$plan_id]) ?: null;
}

/** 获取上架套餐列表（按 sort 升序）。 */
function hosting_plan_list_active()
{
	global $DB;
	return $DB->get_all_prepare("SELECT * FROM MN_plugin_hosting_plan WHERE status='active' ORDER BY sort ASC, id ASC") ?: [];
}

/** 获取全部套餐列表（管理员）。 */
function hosting_plan_list_all()
{
	global $DB;
	return $DB->get_all_prepare("SELECT * FROM MN_plugin_hosting_plan ORDER BY sort ASC, id ASC") ?: [];
}

/** 保存套餐（新增或更新）。 */
function hosting_plan_save($data)
{
	global $DB, $date;
	$now = $date ?: date('Y-m-d H:i:s');
	$fields = [
		'name' => trim((string)($data['name'] ?? '')),
		'description' => trim((string)($data['description'] ?? '')),
		'spec_type' => (int)($data['spec_type'] ?? 0) === 1 ? 1 : 0,
		'spec_web' => max(0, (int)($data['spec_web'] ?? 0)),
		'spec_sql' => max(0, (int)($data['spec_sql'] ?? 0)),
		'spec_flow' => max(0, (int)($data['spec_flow'] ?? 0)),
		'spec_domain' => max(0, (int)($data['spec_domain'] ?? 0)),
		'price_month_cents' => max(0, (int)($data['price_month_cents'] ?? 0)),
		'price_year_cents' => max(0, (int)($data['price_year_cents'] ?? 0)),
		'status' => ($data['status'] ?? 'active') === 'inactive' ? 'inactive' : 'active',
		'sort' => max(0, (int)($data['sort'] ?? 50)),
		'updated_at' => $now,
	];
	if ($fields['name'] === '') {
		return '套餐名称不能为空';
	}
	$id = (int)($data['id'] ?? 0);
	if ($id > 0) {
		$ok = $DB->query_prepare(
			"UPDATE MN_plugin_hosting_plan SET name=?, description=?, spec_type=?, spec_web=?, spec_sql=?, spec_flow=?, spec_domain=?, price_month_cents=?, price_year_cents=?, status=?, sort=?, updated_at=? WHERE id=?",
			[$fields['name'], $fields['description'], $fields['spec_type'], $fields['spec_web'], $fields['spec_sql'], $fields['spec_flow'], $fields['spec_domain'], $fields['price_month_cents'], $fields['price_year_cents'], $fields['status'], $fields['sort'], $fields['updated_at'], $id]
		);
		return $ok ? true : '更新失败';
	}
	$fields['created_at'] = $now;
	$ok = $DB->query_prepare(
		"INSERT INTO MN_plugin_hosting_plan (name, description, spec_type, spec_web, spec_sql, spec_flow, spec_domain, price_month_cents, price_year_cents, status, sort, created_at, updated_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)",
		[$fields['name'], $fields['description'], $fields['spec_type'], $fields['spec_web'], $fields['spec_sql'], $fields['spec_flow'], $fields['spec_domain'], $fields['price_month_cents'], $fields['price_year_cents'], $fields['status'], $fields['sort'], $fields['created_at'], $fields['updated_at']]
	);
	return $ok ? true : '新增失败';
}

/** 删除套餐。 */
function hosting_plan_delete($plan_id)
{
	global $DB;
	return (bool)$DB->query_prepare("DELETE FROM MN_plugin_hosting_plan WHERE id=? LIMIT 1", [(int)$plan_id]);
}

/* ============================================================
 *  节点管理
 * ============================================================ */

/** 获取可用宝塔节点列表（MN_bt 表）。 */
function hosting_node_list()
{
	global $DB;
	return $DB->get_all_prepare("SELECT btdh, btip, btdk, btos, ptl FROM MN_bt ORDER BY id ASC") ?: [];
}

/** 获取单个节点。 */
function hosting_node_get($btdh)
{
	global $DB;
	return $DB->get_row_prepare("SELECT * FROM MN_bt WHERE btdh=? LIMIT 1", [$btdh]) ?: null;
}

/* ============================================================
 *  订单管理
 * ============================================================ */

/** 按订单号查询订单。 */
function hosting_order_get_by_no($order_no)
{
	global $DB;
	return $DB->get_row_prepare("SELECT * FROM MN_plugin_hosting_order WHERE order_no=? LIMIT 1", [$order_no]) ?: null;
}

/** 按 ID 查询订单。 */
function hosting_order_get($order_id)
{
	global $DB;
	return $DB->get_row_prepare("SELECT * FROM MN_plugin_hosting_order WHERE id=? LIMIT 1", [(int)$order_id]) ?: null;
}

/** 用户的订单列表（分页）。 */
function hosting_order_list_by_user($user_id, $page = 1, $per_page = 20)
{
	global $DB;
	$user_id = (int)$user_id;
	$page = max(1, (int)$page);
	$per_page = max(1, min(100, (int)$per_page));
	$offset = ($page - 1) * $per_page;
	$count_row = $DB->get_row_prepare("SELECT COUNT(*) AS cnt FROM MN_plugin_hosting_order WHERE user_id=?", [$user_id]);
	$total = $count_row ? (int)$count_row['cnt'] : 0;
	$list = $DB->get_all_prepare(
		"SELECT * FROM MN_plugin_hosting_order WHERE user_id=? ORDER BY id DESC LIMIT {$offset},{$per_page}",
		[$user_id]
	) ?: [];
	return ['list' => $list, 'total' => $total, 'page' => $page, 'per_page' => $per_page];
}

/** 全部订单列表（管理员，分页 + 简单筛选）。 */
function hosting_order_list_all($page = 1, $per_page = 30, $filters = [])
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

	$count_row = $DB->get_row_prepare("SELECT COUNT(*) AS cnt FROM MN_plugin_hosting_order WHERE {$where}", $params);
	$total = $count_row ? (int)$count_row['cnt'] : 0;
	$list = $DB->get_all_prepare(
		"SELECT * FROM MN_plugin_hosting_order WHERE {$where} ORDER BY id DESC LIMIT {$offset},{$per_page}",
		$params
	) ?: [];
	return ['list' => $list, 'total' => $total, 'page' => $page, 'per_page' => $per_page];
}

/**
 * 创建购买订单（未支付）。
 *
 * @param array $user      user_info 当前用户
 * @param array $plan      套餐行
 * @param string $period   month/year
 * @param string $node     MN_bt.btdh
 * @return array ['ok'=>bool, 'order_no'=>string, 'order_id'=>int, 'msg'=>string]
 */
function hosting_order_create($user, $plan, $period, $node)
{
	global $DB, $date;
	$period = $period === 'year' ? 'year' : 'month';
	$amount_cents = $period === 'year' ? (int)$plan['price_year_cents'] : (int)$plan['price_month_cents'];
	if ($amount_cents <= 0) {
		return ['ok' => false, 'msg' => '该套餐此周期不可购买（价格为 0）'];
	}
	if (!hosting_node_get($node)) {
		return ['ok' => false, 'msg' => '所选节点不存在'];
	}
	$now = $date ?: date('Y-m-d H:i:s');
	$order_no = date("YmdHis") . mt_rand(1000, 9999);
	$ok = $DB->query_prepare(
		"INSERT INTO MN_plugin_hosting_order (user_id, plan_id, plan_name, period, amount_cents, order_no, host_id, node, status, remark, created_at, paid_at, opened_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)",
		[(int)$user['id'], (int)$plan['id'], $plan['name'], $period, $amount_cents, $order_no, 0, $node, 'pending', '', $now, '', '']
	);
	if (!$ok) {
		return ['ok' => false, 'msg' => '订单写入失败'];
	}
	// 取自增 ID
	$row = $DB->get_row_prepare("SELECT id FROM MN_plugin_hosting_order WHERE order_no=? LIMIT 1", [$order_no]);
	$order_id = $row ? (int)$row['id'] : 0;
	return ['ok' => true, 'order_no' => $order_no, 'order_id' => $order_id, 'amount_cents' => $amount_cents];
}

/** 更新订单状态。 */
function hosting_order_set_status($order_id, $status, $remark = '')
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
		"UPDATE MN_plugin_hosting_order SET status=?{$extra} WHERE id=?",
		$params
	);
}

/* ============================================================
 *  资产管理
 * ============================================================ */

/** 用户资产列表。 */
function hosting_asset_list_by_user($user_id)
{
	global $DB;
	return $DB->get_all_prepare(
		"SELECT a.*, zj.user AS host_user, zj.sqldz, zj.ssbt, zj.btid, zj.qk AS host_qk, zj.data, zj.datae
		 FROM MN_plugin_hosting_asset a
		 LEFT JOIN MN_zj zj ON zj.id = a.host_id
		 WHERE a.user_id=?
		 ORDER BY a.id DESC",
		[(int)$user_id]
	) ?: [];
}

/** 全部资产列表（管理员，分页）。 */
function hosting_asset_list_all($page = 1, $per_page = 30)
{
	global $DB;
	$page = max(1, (int)$page);
	$per_page = max(1, min(200, (int)$per_page));
	$offset = ($page - 1) * $per_page;
	$count_row = $DB->get_row_prepare("SELECT COUNT(*) AS cnt FROM MN_plugin_hosting_asset WHERE 1");
	$total = $count_row ? (int)$count_row['cnt'] : 0;
	$list = $DB->get_all_prepare(
		"SELECT a.*, zj.user AS host_user, zj.sqldz, zj.ssbt, zj.btid, zj.qk AS host_qk, zj.data, zj.datae
		 FROM MN_plugin_hosting_asset a
		 LEFT JOIN MN_zj zj ON zj.id = a.host_id
		 ORDER BY a.id DESC LIMIT {$offset},{$per_page}"
	) ?: [];
	return ['list' => $list, 'total' => $total, 'page' => $page, 'per_page' => $per_page];
}

/* ============================================================
 *  主机开通（核心）
 * ============================================================ */

/**
 * 开通主机：调用宝塔 API 创建站点，写入 MN_zj，回填订单和资产。
 *
 * @param int $order_id  MN_plugin_hosting_order.id
 * @return array ['ok'=>bool, 'msg'=>string, 'host_id'=>int]
 */
function hosting_open_host($order_id)
{
	global $DB, $date, $conf;
	$order = hosting_order_get($order_id);
	if (!$order) {
		return ['ok' => false, 'msg' => '订单不存在'];
	}
	if ($order['status'] !== 'paid') {
		return ['ok' => false, 'msg' => '订单状态非已支付，无法开通'];
	}
	if ((int)$order['host_id'] > 0) {
		return ['ok' => false, 'msg' => '该订单已开通', 'host_id' => (int)$order['host_id']];
	}
	$plan = hosting_plan_get($order['plan_id']);
	if (!$plan) {
		hosting_order_set_status($order_id, 'failed', '套餐不存在');
		return ['ok' => false, 'msg' => '套餐不存在'];
	}
	$node = hosting_node_get($order['node']);
	if (!$node) {
		hosting_order_set_status($order_id, 'failed', '节点不存在');
		return ['ok' => false, 'msg' => '节点不存在'];
	}

	// 加载宝塔 API 类
	$bt_api_file = ROOT . 'MPHX/bt_api.php';
	if (!is_file($bt_api_file)) {
		hosting_order_set_status($order_id, 'failed', 'bt_api 类文件缺失');
		return ['ok' => false, 'msg' => 'bt_api 类文件缺失'];
	}
	require_once $bt_api_file;

	$btipe = ($node['ptl'] == 'true' ? 'https' : 'http') . '://' . $node['btip'] . ':' . $node['btdk'];
	$btkeye = $node['btmy'];
	$api = new bt_api($btipe, $btkeye);

	// 生成宝塔面板账号（保证 >=6 字符且全局唯一）
	$user_info_id = (int)$order['user_id'];
	$base_user = 'mb' . $user_info_id;
	$bt_user = $base_user . substr(md5($date . $order['order_no'] . mt_rand(100, 999)), 0, 6);
	$bt_pass = substr(md5($date . SYS_KEY . $order['order_no'] . mt_rand(1000, 9999)), 0, 12);

	// 生成站点目录名（防止重名）
	$hskr = mt_rand(4, 10);
	$rqsj = md5($date . $bt_user . mt_rand(100, 999));
	$wjler = substr($rqsj, $hskr, 3);
	$btserw = 'mnbt.' . $bt_user . $wjler;

	// 产品类型映射
	$cptype = (int)$plan['spec_type'] === 1 ? '1' : '0';
	if ($cptype === '1') {
		$cp_eh_lx = 'CDN';
		$cp_eh_ftp = 'false';
		$cp_eh_sql = 'false';
	} else {
		$cp_eh_lx = '主机';
		$cp_eh_ftp = 'true';
		$cp_eh_sql = 'true';
	}
	$mrwww = $node['btos'] == '1' ? $conf['hxu'] : $conf['hxu'];
	$mrml = ($node['btos'] == '1' ? $conf['hxi'] : $conf['hxo']) . '/' . $btserw;

	// 计算到期时间
	$now_ts = time();
	$period = $order['period'] === 'year' ? 'year' : 'month';
	$expire_ts = strtotime($period === 'year' ? '+1 year' : '+1 month', $now_ts);
	$datae = date('Y-m-d', $expire_ts);

	// 调用宝塔 API 开通
	$r_data = $api->webkt($bt_user, $bt_pass, $btserw, $cp_eh_lx, $cp_eh_ftp, $cp_eh_sql, $conf['hxu'], $mrml);
	$cjqk = $r_data['siteStatus'] ?? false;
	$zdide = $r_data['siteId'] ?? 0;

	if (!$cjqk) {
		$err = $r_data['msg'] ?? '宝塔未返回站点ID';
		hosting_order_set_status($order_id, 'failed', '宝塔创建失败：' . $err);
		return ['ok' => false, 'msg' => '宝塔创建失败：' . $err];
	}

	// 设置到期时间
	$r_datan = $api->setdqsj($zdide, $datae);
	if (!($r_datan['status'] == '1' || $r_datan['status'] == 'true')) {
		// 设置到期失败不致命，继续流程，但记录备注
		@error_log('[hosting_shop] setdqsj failed for order ' . $order['order_no']);
	}

	// 获取 FTP/数据库 ID（与 admin/api/zj.php 流程一致）
	$r_datn = $api->sjlist('ftps');
	$r_datp = $api->sjlist('databases');
	$aedfs = '0';
	$sqlfs = '0';
	if (isset($r_datn['data']) && is_array($r_datn['data'])) {
		foreach ($r_datn['data'] as $val) {
			if ($val['name'] === $bt_user) {
				$aedfs = $val['id'];
				break;
			}
		}
	}
	if (isset($r_datp['data']) && is_array($r_datp['data'])) {
		foreach ($r_datp['data'] as $val) {
			if ($val['name'] === $bt_user) {
				$sqlfs = $val['id'];
				break;
			}
		}
	}

	// 写入 MN_zj 表
	$webdx = json_encode(['max' => (int)$plan['spec_web'], 'dq' => 0]);
	$sqldx = json_encode(['max' => (int)$plan['spec_sql'], 'dq' => 0]);
	$flowratemax = json_encode(['max' => (int)$plan['spec_flow'], 'dq' => 0, 'statistics' => false]);
	$ymbds = $cptype === '1' ? '1' : (string)(int)$plan['spec_domain'];
	$now = $date ?: date('Y-m-d H:i:s');
	$kg = 'true';

	$rowe = $DB->get_row_prepare("SELECT * FROM MN_zj WHERE 1 order by id desc limit 1");
	$new_zj_id = $rowe ? ((int)$rowe['id'] + 1) : 1;

	$ok = $DB->query_prepare(
		"INSERT INTO `MN_zj` (`id`, `ssbt`, `user`, `pass`, `sqluser`, `sqlpass`, `data`, `datae`, `qk`, `btid`, `sqldz`, `ftpid`, `ymbds`, `hxa`, `hxb`, `hxc`, `hxd`, `llmax`) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)",
		[$new_zj_id, $order['node'], $bt_user, $bt_pass, $bt_user, $bt_pass, $now, $datae, $kg, $zdide, $btserw, $aedfs, $ymbds, $webdx, $sqldx, $cptype, $sqlfs, $flowratemax]
	);
	if (!$ok) {
		// 宝塔站点已开通但本地数据库写入失败，标记 failed 但保留 siteId 信息
		hosting_order_set_status($order_id, 'failed', '宝塔已开通但本地数据库写入失败 siteId=' . $zdide);
		return ['ok' => false, 'msg' => '本地数据库写入失败，请联系管理员'];
	}

	// 回填订单
	hosting_order_set_status($order_id, 'opened', '主机已开通，账号：' . $bt_user);
	$DB->query_prepare(
		"UPDATE MN_plugin_hosting_order SET host_id=? WHERE id=?",
		[$new_zj_id, $order_id]
	);

	// 写入资产表
	$DB->query_prepare(
		"INSERT INTO MN_plugin_hosting_asset (user_id, order_id, host_id, plan_id, plan_name, expire_at, status, created_at) VALUES (?,?,?,?,?,?,?,?)",
		[$user_info_id, $order_id, $new_zj_id, (int)$plan['id'], $plan['name'], $datae, 'active', $now]
	);

	// 触发 host.created 钩子（与 admin 开通逻辑保持一致）
	if (function_exists('mnbt_do_action')) {
		$host_row = $DB->get_row_prepare("SELECT * FROM MN_zj WHERE id=? LIMIT 1", [$new_zj_id]);
		mnbt_do_action('host.created', $host_row ?: ['id' => $new_zj_id, 'user' => $bt_user, 'ssbt' => $order['node']], ['source' => 'hosting_shop', 'order_id' => $order_id, 'user_id' => $user_info_id]);
	}

	return ['ok' => true, 'msg' => '开通成功', 'host_id' => $new_zj_id, 'bt_user' => $bt_user, 'bt_pass' => $bt_pass, 'expire' => $datae];
}
