<?php
/**
 * balance 插件 - 余额操作函数库
 *
 * 余额以「分」为单位整数存储，避免浮点误差。
 * 依赖 user_info 插件提供认证（account_token cookie / user_info_auth_current）。
 */

if (!defined('IN_CRONLITE')) {
	exit;
}

/**
 * 生成带站点 base path 前缀的 URL。
 */
function balance_url($path = '')
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

/**
 * 插件静态资源 URL。
 */
function balance_asset_url($path = '')
{
	return mnbt_plugin_url('balance', ltrim($path, '/'));
}

/**
 * 获取当前登录的 user_info 用户。
 * 依赖 user_info 插件已启用。未登录跳转登录页。
 */
function balance_require_user()
{
	if (!function_exists('user_info_auth_current')) {
		http_response_code(500);
		echo '需要先启用 user_info 插件';
		exit;
	}
	$user = user_info_auth_current();
	if (!$user) {
		header('Location: ' . balance_url('account/login'));
		exit;
	}
	return $user;
}

/**
 * 获取用户余额（分）。无记录返回 0。
 */
function balance_get($user_id)
{
	global $DB;
	$row = $DB->get_row_prepare("SELECT balance FROM MN_plugin_balance WHERE user_id=? LIMIT 1", [(int)$user_id]);
	return $row ? (int)$row['balance'] : 0;
}

/**
 * 确保用户有余额记录行。
 */
function balance_ensure_row($user_id)
{
	global $DB, $date;
	$user_id = (int)$user_id;
	$row = $DB->get_row_prepare("SELECT id FROM MN_plugin_balance WHERE user_id=? LIMIT 1", [$user_id]);
	if (!$row) {
		$now = $date ?: date('Y-m-d H:i:s');
		$DB->query_prepare(
			"INSERT INTO MN_plugin_balance (user_id, balance, updated_at) VALUES (?,?,?)",
			[$user_id, 0, $now]
		);
	}
}

/**
 * 增加余额（充值/退款/调整）。
 *
 * @param int    $user_id
 * @param int    $amount    金额（分，正数）
 * @param string $type      recharge / refund / adjust
 * @param string $order_no  关联订单号
 * @param string $remark    备注
 * @return bool
 */
function balance_add($user_id, $amount, $type, $order_no = '', $remark = '')
{
	global $DB, $date;
	$user_id = (int)$user_id;
	$amount = (int)$amount;
	if ($amount <= 0) {
		return false;
	}
	balance_ensure_row($user_id);
	$now = $date ?: date('Y-m-d H:i:s');
	$ok = $DB->query_prepare(
		"UPDATE MN_plugin_balance SET balance = balance + ?, updated_at = ? WHERE user_id = ?",
		[$amount, $now, $user_id]
	);
	if (!$ok) {
		return false;
	}
	$balance_after = balance_get($user_id);
	$DB->query_prepare(
		"INSERT INTO MN_plugin_balance_log (user_id, amount, balance_after, type, order_no, remark, created_at) VALUES (?,?,?,?,?,?,?)",
		[$user_id, $amount, $balance_after, $type, $order_no, $remark, $now]
	);
	return true;
}

/**
 * 扣减余额（消费）。余额不足返回 false。
 *
 * @param int    $user_id
 * @param int    $amount    金额（分，正数）
 * @param string $type      consume
 * @param string $order_no  关联订单号
 * @param string $remark    备注
 * @return bool
 */
function balance_deduct($user_id, $amount, $type = 'consume', $order_no = '', $remark = '')
{
	global $DB, $date;
	$user_id = (int)$user_id;
	$amount = (int)$amount;
	if ($amount <= 0) {
		return false;
	}
	balance_ensure_row($user_id);
	$now = $date ?: date('Y-m-d H:i:s');
	// 原子扣减：余额必须充足
	$ok = $DB->query_prepare(
		"UPDATE MN_plugin_balance SET balance = balance - ?, updated_at = ? WHERE user_id = ? AND balance >= ?",
		[$amount, $now, $user_id, $amount]
	);
	// affected() 返回受影响行数，为 0 说明余额不足
	if (!$ok || !$DB->affected()) {
		return false;
	}
	$balance_after = balance_get($user_id);
	$DB->query_prepare(
		"INSERT INTO MN_plugin_balance_log (user_id, amount, balance_after, type, order_no, remark, created_at) VALUES (?,?,?,?,?,?,?)",
		[$user_id, -$amount, $balance_after, $type, $order_no, $remark, $now]
	);
	return true;
}

/**
 * 获取用户流水记录。
 *
 * @param int $user_id
 * @param int $page      页码（从 1 开始）
 * @param int $per_page  每页条数
 * @return array ['list'=>[], 'total'=>int, 'page'=>int, 'per_page'=>int]
 */
function balance_logs($user_id, $page = 1, $per_page = 20)
{
	global $DB;
	$user_id = (int)$user_id;
	$page = max(1, (int)$page);
	$per_page = max(1, min(100, (int)$per_page));
	$offset = ($page - 1) * $per_page;

	$count_row = $DB->get_row_prepare("SELECT COUNT(*) AS cnt FROM MN_plugin_balance_log WHERE user_id=?", [$user_id]);
	$total = $count_row ? (int)$count_row['cnt'] : 0;

	$list = $DB->get_all_prepare(
		"SELECT * FROM MN_plugin_balance_log WHERE user_id=? ORDER BY id DESC LIMIT {$offset},{$per_page}",
		[$user_id]
	) ?: [];

	return ['list' => $list, 'total' => $total, 'page' => $page, 'per_page' => $per_page];
}

/**
 * 格式化金额：分 → 元（保留 2 位小数）。
 */
function balance_format($cents)
{
	return number_format($cents / 100, 2, '.', '');
}

/**
 * 渲染视图。
 */
function balance_render($view, $vars = [])
{
	$vars['current_user'] = $vars['current_user'] ?? (function_exists('user_info_auth_current') ? user_info_auth_current() : null);
	extract($vars, EXTR_SKIP);
	$viewFile = mnbt_plugin_path('balance') . 'views/' . $view . '.php';
	if (!is_file($viewFile)) {
		http_response_code(500);
		echo 'View not found: ' . htmlspecialchars($view);
		return;
	}
	include $viewFile;
}

/**
 * 输出 JSON 并退出。
 */
function balance_json($code, $extra = [])
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
 *  管理员端辅助函数
 * ============================================================ */

/**
 * 获取所有用户余额列表（分页，关联用户名）。
 *
 * @param int   $page
 * @param int   $per_page
 * @param string $kw       用户名关键字
 * @return array ['list'=>[], 'total'=>int, 'page'=>int, 'per_page'=>int]
 */
function balance_admin_list($page = 1, $per_page = 30, $kw = '')
{
	global $DB;
	$page = max(1, (int)$page);
	$per_page = max(1, min(200, (int)$per_page));
	$offset = ($page - 1) * $per_page;

	$join = '';
	$where = '';
	$params = [];
	if ($kw !== '') {
		$join = " LEFT JOIN MN_plugin_user u ON b.user_id=u.id";
		$where = " WHERE u.username LIKE ?";
		$params = ['%' . $kw . '%'];
	}

	$count_row = $DB->get_row_prepare("SELECT COUNT(*) AS cnt FROM MN_plugin_balance b{$join}{$where}", $params);
	$total = $count_row ? (int)$count_row['cnt'] : 0;

	$list = $DB->get_all_prepare(
		"SELECT b.*, u.username, u.email, u.status
		 FROM MN_plugin_balance b
		 LEFT JOIN MN_plugin_user u ON b.user_id=u.id
		 " . ($kw !== '' ? " WHERE u.username LIKE ?" : "") . "
		 ORDER BY b.id DESC
		 LIMIT {$offset},{$per_page}",
		$params
	) ?: [];

	return ['list' => $list, 'total' => $total, 'page' => $page, 'per_page' => $per_page];
}

/**
 * 获取所有用户的流水记录（分页，关联用户名）。
 *
 * @param int   $page
 * @param int   $per_page
 * @param array $filters  ['user_id'=>int, 'type'=>string, 'order_no'=>string]
 * @return array
 */
function balance_admin_logs($page = 1, $per_page = 30, $filters = [])
{
	global $DB;
	$page = max(1, (int)$page);
	$per_page = max(1, min(200, (int)$per_page));
	$offset = ($page - 1) * $per_page;

	$where = ' WHERE 1=1';
	$params = [];
	if (!empty($filters['user_id'])) {
		$where .= ' AND l.user_id=?';
		$params[] = (int)$filters['user_id'];
	}
	if (!empty($filters['type'])) {
		$where .= ' AND l.type=?';
		$params[] = $filters['type'];
	}
	if (!empty($filters['order_no'])) {
		$where .= ' AND l.order_no LIKE ?';
		$params[] = '%' . $filters['order_no'] . '%';
	}

	$count_row = $DB->get_row_prepare("SELECT COUNT(*) AS cnt FROM MN_plugin_balance_log l{$where}", $params);
	$total = $count_row ? (int)$count_row['cnt'] : 0;

	$list = $DB->get_all_prepare(
		"SELECT l.*, u.username
		 FROM MN_plugin_balance_log l
		 LEFT JOIN MN_plugin_user u ON l.user_id=u.id
		 {$where}
		 ORDER BY l.id DESC
		 LIMIT {$offset},{$per_page}",
		$params
	) ?: [];

	return ['list' => $list, 'total' => $total, 'page' => $page, 'per_page' => $per_page];
}
