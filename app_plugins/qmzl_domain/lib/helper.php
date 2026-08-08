<?php
/**
 * qmzl_domain 插件 - 账号与订单辅助函数
 *
 * 用户维度：user_info 插件用户（MN_plugin_user），前置依赖 user_info。
 */
if (!defined('IN_CRONLITE')) exit;

/* ============================================================
 * URL / 渲染 / 登录辅助
 * ============================================================ */

/** 生成带站点 base path 前缀的 URL（index.php?_r= 查询参数路由）。 */
function qmzl_url($path = '')
{
	$scriptName = isset($_SERVER['SCRIPT_NAME']) ? str_replace('\\', '/', $_SERVER['SCRIPT_NAME']) : '';
	$basePath = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');
	if ($basePath === '.' || $basePath === '/') {
		$basePath = '';
	}
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
function qmzl_asset_url($path = '')
{
	return mnbt_plugin_url('qmzl_domain', 'assets/' . ltrim($path, '/'));
}

/** 输出 JSON 并退出（与 balance/hosting 一致）。 */
function qmzl_json($code, $extra = [])
{
	@header('Content-Type: application/json; charset=UTF-8');
	$payload = ['code' => $code];
	if (is_array($extra)) {
		$payload = array_merge($payload, $extra);
	}
	echo json_encode($payload, JSON_UNESCAPED_UNICODE);
	exit;
}

/**
 * 获取当前登录的 user_info 用户；未登录跳转登录页。
 * $json=true 时输出 JSON 错误（供 API 路由使用）。
 * @return array|void
 */
function qmzl_require_user($json = false)
{
	if (!function_exists('user_info_auth_current')) {
		if ($json) qmzl_json('需要先启用 user_info 插件');
		http_response_code(500);
		echo '需要先启用 user_info 插件';
		exit;
	}
	$user = user_info_auth_current();
	if (!$user) {
		if ($json) {
			qmzl_json('请先登录', ['msg' => '请先登录', 'login_url' => qmzl_url('account/login')]);
		}
		header('Location: ' . qmzl_url('account/login'));
		exit;
	}
	return $user;
}

/** 渲染用户端视图（views/ 目录，自包含页面）。 */
function qmzl_render($view, $vars = [])
{
	$vars['current_user'] = $vars['current_user'] ?? (function_exists('user_info_auth_current') ? user_info_auth_current() : null);
	extract($vars, EXTR_SKIP);
	$viewFile = mnbt_plugin_path('qmzl_domain') . 'views/' . $view . '.php';
	if (!is_file($viewFile)) {
		http_response_code(500);
		echo 'View not found: ' . htmlspecialchars($view);
		return;
	}
	include $viewFile;
}

/* ============================================================
 * 表结构自动升级（bootstrap 时调用一次，兼容已安装站点）
 * ============================================================ */
function qmzl_schema_upgrade()
{
	global $DB;
	if (!isset($DB) || !is_object($DB)) return;

	$tbl = $DB->get_row_prepare("SHOW TABLES LIKE 'plg_qmzl_account'");
	if (!$tbl) {
		@$DB->query("CREATE TABLE IF NOT EXISTS `plg_qmzl_account` (
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`user_id` int(11) NOT NULL DEFAULT 0,
			`username` varchar(64) NOT NULL DEFAULT '',
			`account` varchar(255) NOT NULL DEFAULT '',
			`password` text,
			`jwt` text,
			`jwt_expire` varchar(20) NOT NULL DEFAULT '0',
			`status` varchar(20) NOT NULL DEFAULT 'ok',
			`last_msg` varchar(255) NOT NULL DEFAULT '',
			`created_at` varchar(50) NOT NULL DEFAULT '',
			`updated_at` varchar(50) NOT NULL DEFAULT '',
			PRIMARY KEY (`id`),
			UNIQUE KEY `uq_user_id` (`user_id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8");
	}

	$tbl = $DB->get_row_prepare("SHOW TABLES LIKE 'plg_qmzl_order'");
	if (!$tbl) {
		@$DB->query("CREATE TABLE IF NOT EXISTS `plg_qmzl_order` (
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`user_id` int(11) NOT NULL DEFAULT 0,
			`username` varchar(64) NOT NULL DEFAULT '',
			`ddh` varchar(64) NOT NULL DEFAULT '',
			`domain` varchar(255) NOT NULL DEFAULT '',
			`year` int(11) NOT NULL DEFAULT 1,
			`amount` varchar(20) NOT NULL DEFAULT '0',
			`template_id` int(11) NOT NULL DEFAULT 0,
			`cloud_order_id` varchar(64) NOT NULL DEFAULT '',
			`gateway` varchar(64) NOT NULL DEFAULT '',
			`status` varchar(20) NOT NULL DEFAULT 'Pending',
			`remark` varchar(255) NOT NULL DEFAULT '',
			`created_at` varchar(50) NOT NULL DEFAULT '',
			`updated_at` varchar(50) NOT NULL DEFAULT '',
			PRIMARY KEY (`id`),
			KEY `idx_user` (`user_id`),
			KEY `idx_ddh` (`ddh`),
			KEY `idx_order` (`cloud_order_id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8");
	} else {
		// 老表补齐字段
		$cols = $DB->get_all_prepare("SHOW COLUMNS FROM `plg_qmzl_order` LIKE 'ddh'") ?: [];
		if (empty($cols)) @$DB->query("ALTER TABLE `plg_qmzl_order` ADD COLUMN `ddh` varchar(64) NOT NULL DEFAULT '' AFTER `username`");
		$cols = $DB->get_all_prepare("SHOW COLUMNS FROM `plg_qmzl_order` LIKE 'template_id'") ?: [];
		if (empty($cols)) @$DB->query("ALTER TABLE `plg_qmzl_order` ADD COLUMN `template_id` int(11) NOT NULL DEFAULT 0 AFTER `amount`");
		$cols = $DB->get_all_prepare("SHOW COLUMNS FROM `plg_qmzl_order` LIKE 'remark'") ?: [];
		if (empty($cols)) @$DB->query("ALTER TABLE `plg_qmzl_order` ADD COLUMN `remark` varchar(255) NOT NULL DEFAULT '' AFTER `status`");
		$cols = $DB->get_all_prepare("SHOW COLUMNS FROM `plg_qmzl_order` LIKE 'updated_at'") ?: [];
		if (empty($cols)) @$DB->query("ALTER TABLE `plg_qmzl_order` ADD COLUMN `updated_at` varchar(50) NOT NULL DEFAULT '' AFTER `created_at`");
	}

	// 模板归属表
	$tbl = $DB->get_row_prepare("SHOW TABLES LIKE 'plg_qmzl_template'");
	if (!$tbl) {
		@$DB->query("CREATE TABLE IF NOT EXISTS `plg_qmzl_template` (
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`template_id` int(11) NOT NULL DEFAULT 0,
			`user_id` int(11) NOT NULL DEFAULT 0,
			`username` varchar(64) NOT NULL DEFAULT '',
			`created_at` varchar(50) NOT NULL DEFAULT '',
			PRIMARY KEY (`id`),
			UNIQUE KEY `uq_tpl` (`template_id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8");
	}
}

/* ============================================================
 * 账号（按 user_id）
 * ============================================================ */

/** 获取用户绑定记录 */
function qmzl_account_get($user_id)
{
	global $DB;
	$user_id = (int)$user_id;
	if ($user_id <= 0) return false;
	return $DB->get_row_prepare("SELECT * FROM plg_qmzl_account WHERE user_id=? LIMIT 1", [$user_id]);
}

/** 保存/更新绑定凭证（密码加密存储，登录后再写 token） */
function qmzl_account_save_cred($user_id, $username, $account, $password)
{
	global $DB, $date;
	$enc = qmzl_encrypt_pwd($password);
	$exist = qmzl_account_get($user_id);
	if ($exist) {
		return (bool)$DB->query_prepare(
			"UPDATE plg_qmzl_account SET username=?, account=?, password=?, updated_at=? WHERE user_id=?",
			[$username, $account, $enc, $date, (int)$user_id]
		);
	}
	return (bool)$DB->query_prepare(
		"INSERT INTO plg_qmzl_account (user_id, username, account, password, jwt, jwt_expire, status, last_msg, created_at, updated_at) VALUES (?,?,?,?,?,?,?,?,?,?)",
		[(int)$user_id, $username, $account, $enc, '', '0', 'ok', '', $date, $date]
	);
}

/** 写 token 与过期时间 */
function qmzl_account_set_token($user_id, $jwt, $expire)
{
	global $DB, $date;
	return (bool)$DB->query_prepare(
		"UPDATE plg_qmzl_account SET jwt=?, jwt_expire=?, updated_at=? WHERE user_id=?",
		[(string)$jwt, (string)$expire, $date, (int)$user_id]
	);
}

/** 更新状态（登录失败等） */
function qmzl_account_update_status($user_id, $status, $msg = '')
{
	global $DB, $date;
	$status = ($status === 'error') ? 'error' : 'ok';
	$msg = mb_substr((string)$msg, 0, 250);
	return (bool)$DB->query_prepare(
		"UPDATE plg_qmzl_account SET status=?, last_msg=?, updated_at=? WHERE user_id=?",
		[$status, $msg, $date, (int)$user_id]
	);
}

/** 解绑账号 */
function qmzl_account_delete($user_id)
{
	global $DB;
	return (bool)$DB->query_prepare("DELETE FROM plg_qmzl_account WHERE user_id=? LIMIT 1", [(int)$user_id]);
}

/** 后台：账号列表 */
function qmzl_account_list($page = 1, $limit = 200, $keyword = '')
{
	global $DB;
	$page = max(1, (int)$page);
	$limit = min(1000, max(1, (int)$limit));
	$where = '';
	$args = [];
	if ($keyword !== '') {
		$where = "WHERE username LIKE ? OR account LIKE ?";
		$kw = '%' . $keyword . '%';
		$args = [$kw, $kw];
	}
	$row = $DB->get_row_prepare("SELECT COUNT(*) AS c FROM plg_qmzl_account " . $where, $args);
	$total = (int)($row['c'] ?? 0);
	$offset = ($page - 1) * $limit;
	$rows = $DB->get_all_prepare("SELECT * FROM plg_qmzl_account " . $where . " ORDER BY id DESC LIMIT {$limit} OFFSET {$offset}", $args) ?: [];
	return ['total' => $total, 'rows' => $rows];
}

/* ============================================================
 * 模式 / 代理商（agent）
 * ============================================================ */

/** 当前模式：client（客户自注册）/ agent（代理商） */
function qmzl_mode()
{
	return qmzl_setting_get('mode', 'client') === 'agent' ? 'agent' : 'client';
}

/** 用户端路由前缀：client → qmzl，agent → qmzl_domain */
function qmzl_route_prefix()
{
	return qmzl_mode() === 'agent' ? 'qmzl_domain' : 'qmzl';
}

/**
 * 保存代理商开放接口凭证（平台账号 + API 密钥）并立即鉴权换取 JWT。
 * 上游登录需人机验证，但开放接口鉴权（/api/v1/auth）用 API 密钥，无需人机验证。
 */
function qmzl_agent_save($username, $apiToken)
{
	$username = trim((string)$username);
	$apiToken = trim((string)$apiToken);
	if ($username === '' || $apiToken === '') {
		return ['ok' => false, 'status' => 0, 'msg' => '平台账号和 API 密钥不能为空', 'data' => null];
	}
	$auth = qmzl_openapi_auth($username, $apiToken);
	if (!$auth['ok']) {
		qmzl_setting_set('agent_status', 'error');
		qmzl_setting_set('agent_msg', $auth['msg']);
		return ['ok' => false, 'status' => 0, 'msg' => '鉴权失败：' . $auth['msg'] . '（请确认平台账号与 API 密钥正确）', 'data' => null];
	}
	qmzl_setting_set('agent_username', $username);
	qmzl_setting_set('agent_api_token', qmzl_encrypt_pwd($apiToken));
	qmzl_setting_set('agent_jwt', $auth['data']['jwt']);
	qmzl_setting_set('agent_jwt_expire', (string)$auth['data']['exp']);
	qmzl_setting_set('agent_status', 'ok');
	qmzl_setting_set('agent_msg', '');
	return $auth;
}

/** 获取有效的代理商 JWT（过期自动用 API 密钥重新换取） */
function qmzl_agent_token()
{
	$jwt = (string)qmzl_setting_get('agent_jwt', '');
	$exp = (int)qmzl_setting_get('agent_jwt_expire', 0);
	if ($jwt !== '' && $exp > time() + 300) {
		return ['ok' => true, 'status' => 200, 'msg' => '', 'data' => ['jwt' => $jwt]];
	}
	$username = (string)qmzl_setting_get('agent_username', '');
	$apiToken = qmzl_decrypt_pwd((string)qmzl_setting_get('agent_api_token', ''));
	if ($username === '' || $apiToken === '') {
		return ['ok' => false, 'status' => 0, 'msg' => '请先在后台配置代理商平台账号与 API 密钥', 'data' => null];
	}
	$auth = qmzl_openapi_auth($username, $apiToken);
	if (!$auth['ok']) {
		qmzl_setting_set('agent_status', 'error');
		qmzl_setting_set('agent_msg', $auth['msg']);
		return $auth;
	}
	qmzl_setting_set('agent_jwt', $auth['data']['jwt']);
	qmzl_setting_set('agent_jwt_expire', (string)$auth['data']['exp']);
	qmzl_setting_set('agent_status', 'ok');
	qmzl_setting_set('agent_msg', '');
	return $auth;
}

/**
 * 获取上游 token（统一入口）：
 * agent 模式用管理员代理商账号，client 模式用用户绑定账号。
 * @param array $user user_info 用户数组
 */
function qmzl_require_token($user)
{
	if (qmzl_mode() === 'agent') {
		return qmzl_agent_token();
	}
	return qmzl_get_token((int)$user['id']);
}

/* ============================================================
 * 模板归属（agent 模式：客户创建的模板记录归属，隔离查看/操作）
 * ============================================================ */

/** 查询上游模板的归属用户 ID（0 = 未记录/管理员私有） */
function qmzl_template_owner_id($templateId)
{
	global $DB;
	$r = $DB->get_row_prepare("SELECT user_id FROM plg_qmzl_template WHERE template_id=? LIMIT 1", [(int)$templateId]);
	return $r ? (int)$r['user_id'] : 0;
}

/** 记录模板归属（已存在则忽略） */
function qmzl_template_record($templateId, $userId, $username)
{
	global $DB, $date;
	if ((int)$templateId <= 0 || (int)$userId <= 0) return false;
	if (qmzl_template_owner_id($templateId) > 0) return true;
	return (bool)$DB->query_prepare(
		"INSERT INTO plg_qmzl_template (template_id, user_id, username, created_at) VALUES (?,?,?,?)",
		[(int)$templateId, (int)$userId, $username, $date]
	);
}

/** 模板是否属于该用户（agent 模式权限校验用） */
function qmzl_template_owned($templateId, $userId)
{
	$owner = qmzl_template_owner_id($templateId);
	return $owner > 0 && $owner === (int)$userId;
}

/** 某用户拥有的上游模板 ID 集合 */
function qmzl_template_ids_by_user($userId)
{
	global $DB;
	$rows = $DB->get_all_prepare("SELECT template_id FROM plg_qmzl_template WHERE user_id=? ORDER BY id DESC", [(int)$userId]) ?: [];
	$ids = [];
	foreach ($rows as $r) {
		$ids[(int)$r['template_id']] = true;
	}
	return $ids;
}

/* ============================================================
 * 后缀溢价（agent 模式：管理员为各后缀设置的一次性加价，元）
 * ============================================================ */

/** 溢价配置表：{".com": "10", ...} */
function qmzl_markup_map()
{
	if (qmzl_mode() !== 'agent') return [];
	$raw = qmzl_setting_get('agent_markup', '');
	$map = json_decode((string)$raw, true);
	return is_array($map) ? $map : [];
}

/** 某后缀的溢价金额（元，无则 0） */
function qmzl_markup_for_suffix($suffix)
{
	$map = qmzl_markup_map();
	$key = strtolower((string)$suffix);
	if ($key !== '' && $key[0] !== '.') $key = '.' . ltrim($key, '.');
	$v = (string)($map[$key] ?? '0');
	$f = (float)$v;
	return $f > 0 ? $f : 0;
}

/** 某完整域名的溢价金额（取第一个点之后的后缀） */
function qmzl_markup_for_domain($domain)
{
	$pos = strpos((string)$domain, '.');
	if ($pos === false) return 0;
	return qmzl_markup_for_suffix(substr($domain, $pos));
}

/** 对价格列表应用溢价（agent 模式），返回溢价金额 */
function qmzl_apply_markup(&$list, $domain)
{
	if (qmzl_mode() !== 'agent' || !is_array($list)) return 0;
	$markup = qmzl_markup_for_domain($domain);
	if ($markup <= 0) return 0;
	foreach ($list as &$p) {
		if (is_array($p) && isset($p['buyprice'])) {
			$p['buyprice'] = (string)(round((float)$p['buyprice'] + $markup, 2));
			$p['markup'] = $markup;
		}
	}
	unset($p);
	return $markup;
}

/* ============================================================
 * 订单（按 user_id）
 * ============================================================ */

/** 新增本地订单记录 */
function qmzl_order_create($user_id, $username, $ddh, $domain, $year, $amount, $templateId, $cloudOrderId, $gateway, $remark = '')
{
	global $DB, $date;
	return (bool)$DB->query_prepare(
		"INSERT INTO plg_qmzl_order (user_id, username, ddh, domain, year, amount, template_id, cloud_order_id, gateway, status, remark, created_at, updated_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)",
		[(int)$user_id, $username, (string)$ddh, $domain, (int)$year, (string)$amount, (int)$templateId, (string)$cloudOrderId, (string)$gateway, 'Pending', $remark, $date, $date]
	);
}

/** 按本地主键查订单 */
function qmzl_order_get_by_id($id)
{
	global $DB;
	return $DB->get_row_prepare("SELECT * FROM plg_qmzl_order WHERE id=? LIMIT 1", [(int)$id]);
}

/** 按上游订单号查本地记录（client 模式） */
function qmzl_order_get($cloudOrderId)
{
	global $DB;
	return $DB->get_row_prepare("SELECT * FROM plg_qmzl_order WHERE cloud_order_id=? LIMIT 1", [(string)$cloudOrderId]);
}

/** 按 MNBT 订单号查本地记录（agent 模式） */
function qmzl_order_get_by_ddh($ddh)
{
	global $DB;
	return $DB->get_row_prepare("SELECT * FROM plg_qmzl_order WHERE ddh=? LIMIT 1", [(string)$ddh]);
}

/** 更新订单（白名单字段） */
function qmzl_order_update($id, $fields = [])
{
	global $DB, $date;
	$whitelist = ['ddh', 'domain', 'year', 'amount', 'template_id', 'cloud_order_id', 'gateway', 'status', 'remark'];
	$set = [];
	$args = [];
	foreach ($fields as $k => $v) {
		if (in_array($k, $whitelist, true)) {
			$set[] = "`{$k}`=?";
			$args[] = $v;
		}
	}
	if (!$set) return false;
	$set[] = "`updated_at`=?";
	$args[] = $date;
	$args[] = (int)$id;
	return (bool)$DB->query_prepare("UPDATE plg_qmzl_order SET " . implode(', ', $set) . " WHERE id=?", $args);
}

/** 更新订单状态（按上游订单号，client 模式） */
function qmzl_order_update_status($cloudOrderId, $status)
{
	global $DB, $date;
	return (bool)$DB->query_prepare(
		"UPDATE plg_qmzl_order SET status=?, updated_at=? WHERE cloud_order_id=?",
		[$status, $date, (string)$cloudOrderId]
	);
}

/** 用户订单列表 */
function qmzl_order_list($user_id, $page = 1, $limit = 20)
{
	global $DB;
	$page = max(1, (int)$page);
	$limit = min(100, max(1, (int)$limit));
	$row = $DB->get_row_prepare("SELECT COUNT(*) AS c FROM plg_qmzl_order WHERE user_id=?", [(int)$user_id]);
	$total = (int)($row['c'] ?? 0);
	$offset = ($page - 1) * $limit;
	$rows = $DB->get_all_prepare("SELECT * FROM plg_qmzl_order WHERE user_id=? ORDER BY id DESC LIMIT {$limit} OFFSET {$offset}", [(int)$user_id]) ?: [];
	return ['total' => $total, 'rows' => $rows];
}

/** 后台：全部订单列表 */
function qmzl_order_admin_list($page = 1, $limit = 20, $status = '', $keyword = '')
{
	global $DB;
	$page = max(1, (int)$page);
	$limit = min(200, max(1, (int)$limit));
	$where = '';
	$args = [];
	if ($status !== '' && in_array($status, ['Pending', 'Paid', 'Cancelled', 'Failed'], true)) {
		$where = "WHERE status=?";
		$args[] = $status;
	}
	if ($keyword !== '') {
		$where .= ($where === '' ? 'WHERE ' : ' AND ') . "(domain LIKE ? OR username LIKE ? OR ddh LIKE ? OR cloud_order_id LIKE ?)";
		$kw = '%' . $keyword . '%';
		array_push($args, $kw, $kw, $kw, $kw);
	}
	$row = $DB->get_row_prepare("SELECT COUNT(*) AS c FROM plg_qmzl_order " . $where, $args);
	$total = (int)($row['c'] ?? 0);
	$offset = ($page - 1) * $limit;
	$rows = $DB->get_all_prepare("SELECT * FROM plg_qmzl_order " . $where . " ORDER BY id DESC LIMIT {$limit} OFFSET {$offset}", $args) ?: [];
	return ['total' => $total, 'rows' => $rows];
}

/* ============================================================
 * 插件设置
 * ============================================================ */

function qmzl_setting_get($key, $default = '')
{
	return mnbt_plugin_option_get('qmzl_domain', $key, $default);
}

function qmzl_setting_set($key, $value)
{
	return mnbt_plugin_option_set('qmzl_domain', $key, $value);
}
