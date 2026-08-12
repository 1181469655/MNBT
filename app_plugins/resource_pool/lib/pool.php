<?php
/**
 * resource_pool 插件 - 公共函数库
 *
 * 归属关系存在插件自有表 MN_plugin_respool.host_users（MN_zj.user 的 JSON 数组），
 * 不改核心主机表结构、不修改任何核心文件。
 */

if (!defined('IN_CRONLITE')) {
	exit;
}

define('RP_TABLE', 'MN_plugin_respool');

/* ============================================================
 *  建表保障（幂等）
 * ============================================================
 *  install.sql 已建表；此处兜底处理"手动放目录未走安装"或
 *  "旧版本升级"的情况。只在后台页面/AJAX 内调用，避免拖慢用户端。
 */

function rp_ensure_schema($force = false)
{
	global $DB;
	static $done = false;
	if (!isset($DB) || !is_object($DB)) {
		return;
	}
	if ($done && !$force) {
		return;
	}
	$done = true;

	@$DB->query("CREATE TABLE IF NOT EXISTS `" . RP_TABLE . "` (
		`id` int(11) NOT NULL AUTO_INCREMENT,
		`name` varchar(120) NOT NULL DEFAULT '',
		`username` varchar(120) NOT NULL DEFAULT '',
		`password` varchar(255) NOT NULL DEFAULT '',
		`nodes` text,
		`host_users` text,
		`web_space` int(11) NOT NULL DEFAULT '0',
		`sql_space` int(11) NOT NULL DEFAULT '0',
		`flow` int(11) NOT NULL DEFAULT '0',
		`expire_date` varchar(50) NOT NULL DEFAULT '',
		`status` varchar(20) NOT NULL DEFAULT 'enabled',
		`remark` varchar(500) NOT NULL DEFAULT '',
		`created_at` varchar(50) NOT NULL DEFAULT '',
		`updated_at` varchar(50) NOT NULL DEFAULT '',
		PRIMARY KEY (`id`),
		UNIQUE KEY `uk_respool_username` (`username`)
	) ENGINE=MyISAM DEFAULT CHARSET=utf8");

	// host_users：老版本（1.0.0）建的表没有这一列，补上
	if (@$DB->get_row_prepare("SELECT `host_users` FROM " . RP_TABLE . " WHERE 1 LIMIT 1") === false) {
		@$DB->query("ALTER TABLE `" . RP_TABLE . "` ADD COLUMN `host_users` text");
	}

	rp_migrate_from_pool_id();
}

/**
 * 从 1.0.0 版的 MN_zj.pool_id 迁移到 host_users。
 *
 * 1.0.0 曾给 MN_zj 加过 pool_id 字段；现在归属关系改存在资源池表里。
 * 若检测到该字段且仍有数据，则把归属搬到 host_users。
 * 不删除 MN_zj.pool_id（不动核心表结构），迁移后该字段闲置不影响原功能。
 */
function rp_migrate_from_pool_id()
{
	global $DB;
	// pool_id 不存在（正常的新装）→ 无需迁移
	if (@$DB->get_row_prepare("SELECT `pool_id` FROM MN_zj WHERE 1 LIMIT 1") === false) {
		return;
	}
	$rows = @$DB->get_all_prepare("SELECT user, pool_id FROM MN_zj WHERE pool_id IS NOT NULL AND pool_id>0");
	if (!$rows) {
		return;
	}
	$byPool = [];
	foreach ($rows as $r) {
		$pid = (int)$r['pool_id'];
		$u   = trim((string)$r['user']);
		if ($pid > 0 && $u !== '') {
			$byPool[$pid][] = $u;
		}
	}
	foreach ($byPool as $pid => $users) {
		$pool = @$DB->get_row_prepare("SELECT id, host_users FROM " . RP_TABLE . " WHERE id=? LIMIT 1", [$pid]);
		if (!$pool) {
			continue;
		}
		$merged = rp_decode_host_users($pool['host_users'] ?? '');
		foreach ($users as $u) {
			if (!in_array($u, $merged, true)) {
				$merged[] = $u;
			}
		}
		@$DB->query_prepare(
			"UPDATE " . RP_TABLE . " SET host_users=? WHERE id=?",
			[json_encode(array_values($merged), JSON_UNESCAPED_UNICODE), $pid]
		);
	}
	// 迁移完成，清空 pool_id 避免下次重复迁移
	@$DB->query("UPDATE MN_zj SET pool_id=NULL WHERE pool_id IS NOT NULL");
}

/* ============================================================
 *  工具
 * ============================================================ */

/** 当前时间字符串 */
function rp_now()
{
	global $date;
	return $date ?: date('Y-m-d H:i:s');
}

/** 资源池状态可选值 */
function rp_statuses()
{
	return [
		'enabled'  => '启用',
		'disabled' => '禁用',
	];
}

/** nodes 字段解码为节点代号数组 */
function rp_decode_nodes($raw)
{
	if (is_array($raw)) {
		return array_values(array_filter(array_map('strval', $raw), 'strlen'));
	}
	$raw = trim((string)$raw);
	if ($raw === '') {
		return [];
	}
	$arr = json_decode($raw, true);
	if (!is_array($arr)) {
		return [];
	}
	$out = [];
	foreach ($arr as $v) {
		$v = trim((string)$v);
		if ($v !== '' && !in_array($v, $out, true)) {
			$out[] = $v;
		}
	}
	return $out;
}

/** host_users 字段解码为主机账号数组 */
function rp_decode_host_users($raw)
{
	if (is_array($raw)) {
		$arr = $raw;
	} else {
		$raw = trim((string)$raw);
		if ($raw === '') {
			return [];
		}
		$arr = json_decode($raw, true);
		if (!is_array($arr)) {
			return [];
		}
	}
	$out = [];
	foreach ($arr as $v) {
		$v = trim((string)$v);
		if ($v !== '' && !in_array($v, $out, true)) {
			$out[] = $v;
		}
	}
	return $out;
}

/** 全部宝塔节点（用于可用节点勾选） */
function rp_all_nodes()
{
	global $DB;
	return $DB->get_all_prepare("SELECT id, btdh, btip, btos FROM MN_bt WHERE 1 ORDER BY id ASC") ?: [];
}

/** 日期格式校验：空串或 yyyy-mm-dd */
function rp_valid_date($d)
{
	$d = trim((string)$d);
	if ($d === '') {
		return true;
	}
	if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $d)) {
		return false;
	}
	$parts = explode('-', $d);
	return checkdate((int)$parts[1], (int)$parts[2], (int)$parts[0]);
}

/** 资源池是否已过期 */
function rp_is_expired($pool)
{
	$d = trim((string)($pool['expire_date'] ?? ''));
	if ($d === '' || $d === '0000-00-00') {
		return false;
	}
	return strtotime($d . ' 23:59:59') < time();
}

/** 资源池当前是否可用于开通（启用且未过期） */
function rp_is_usable($pool)
{
	return is_array($pool)
		&& ($pool['status'] ?? '') === 'enabled'
		&& !rp_is_expired($pool);
}

/** 配额显示：0 表示不限 */
function rp_quota_text($value, $unit)
{
	$value = (int)$value;
	return $value <= 0 ? '不限' : ($value . $unit);
}

/* ============================================================
 *  资源池 CRUD
 * ============================================================ */

/** 按 ID 取资源池 */
function rp_get($id)
{
	global $DB;
	$id = (int)$id;
	if ($id <= 0) {
		return null;
	}
	rp_ensure_schema();
	$row = $DB->get_row_prepare("SELECT * FROM " . RP_TABLE . " WHERE id=? LIMIT 1", [$id]);
	return $row ?: null;
}

/** 按用户名取资源池 */
function rp_get_by_username($username)
{
	global $DB;
	$username = trim((string)$username);
	if ($username === '') {
		return null;
	}
	rp_ensure_schema();
	$row = $DB->get_row_prepare("SELECT * FROM " . RP_TABLE . " WHERE username=? LIMIT 1", [$username]);
	return $row ?: null;
}

/**
 * 分页列出资源池。
 *
 * @param int    $page     页码（从 1 开始）
 * @param int    $per_page 每页条数
 * @param string $kw       关键词（匹配资源池名 / 用户名）
 * @param string $status   状态筛选（''=全部）
 * @return array ['list'=>array, 'total'=>int, 'per_page'=>int, 'page'=>int]
 */
function rp_list($page = 1, $per_page = 20, $kw = '', $status = '')
{
	global $DB;
	rp_ensure_schema();

	$page     = max(1, (int)$page);
	$per_page = max(1, min(200, (int)$per_page));
	$kw       = trim((string)$kw);
	$status   = trim((string)$status);

	$where  = 'WHERE 1';
	$params = [];
	if ($kw !== '') {
		$where .= ' AND (name LIKE ? OR username LIKE ?)';
		$params[] = '%' . $kw . '%';
		$params[] = '%' . $kw . '%';
	}
	if ($status !== '' && isset(rp_statuses()[$status])) {
		$where .= ' AND status=?';
		$params[] = $status;
	}

	$total  = (int)$DB->count_prepare("SELECT count(*) FROM " . RP_TABLE . " {$where}", $params);
	$offset = ($page - 1) * $per_page;
	$list   = $DB->get_all_prepare(
		"SELECT * FROM " . RP_TABLE . " {$where} ORDER BY id DESC LIMIT {$offset},{$per_page}",
		$params
	) ?: [];

	return ['list' => $list, 'total' => $total, 'per_page' => $per_page, 'page' => $page];
}

/**
 * 校验资源池表单数据。
 *
 * @param array    $in 表单输入
 * @param int|null $id 编辑时的资源池 ID（用于用户名查重排除自身）
 * @return array ['ok'=>bool, 'msg'=>string, 'data'=>array]
 */
function rp_validate($in, $id = null)
{
	$name     = trim((string)($in['name'] ?? ''));
	$username = trim((string)($in['username'] ?? ''));
	$password = (string)($in['password'] ?? '');
	$nodes    = rp_decode_nodes($in['nodes'] ?? []);
	$web      = (int)($in['web_space'] ?? 0);
	$sql      = (int)($in['sql_space'] ?? 0);
	$flow     = (int)($in['flow'] ?? 0);
	$expire   = trim((string)($in['expire_date'] ?? ''));
	$status   = trim((string)($in['status'] ?? 'enabled'));
	$remark   = trim((string)($in['remark'] ?? ''));

	if ($name === '' || mb_strlen($name) > 120) {
		return ['ok' => false, 'msg' => '资源池名不能为空且不超过 120 字'];
	}
	if (!preg_match('/^[a-zA-Z0-9_-]{4,120}$/', $username)) {
		return ['ok' => false, 'msg' => '用户名只能用字母/数字/下划线/横线，长度 4-120 位'];
	}
	// 新增必填密码；编辑时留空表示不修改
	if ($id === null) {
		if (mb_strlen($password) < 6) {
			return ['ok' => false, 'msg' => '密码不能少于 6 位'];
		}
	} elseif ($password !== '' && mb_strlen($password) < 6) {
		return ['ok' => false, 'msg' => '密码不能少于 6 位（留空表示不修改）'];
	}
	if (mb_strlen($password) > 255) {
		return ['ok' => false, 'msg' => '密码过长'];
	}
	if ($web < 0 || $sql < 0 || $flow < 0) {
		return ['ok' => false, 'msg' => '配额不能为负数'];
	}
	if (!rp_valid_date($expire)) {
		return ['ok' => false, 'msg' => '到期日期格式错误，应为 yyyy-mm-dd 或留空'];
	}
	if (!isset(rp_statuses()[$status])) {
		return ['ok' => false, 'msg' => '资源池状态非法'];
	}
	if (mb_strlen($remark) > 500) {
		return ['ok' => false, 'msg' => '备注不超过 500 字'];
	}
	// 可用节点必须存在于 MN_bt
	if ($nodes) {
		$valid = [];
		foreach (rp_all_nodes() as $n) {
			$valid[] = (string)$n['btdh'];
		}
		foreach ($nodes as $n) {
			if (!in_array($n, $valid, true)) {
				return ['ok' => false, 'msg' => '可用节点不存在：' . $n];
			}
		}
	}
	// 用户名唯一
	$exist = rp_get_by_username($username);
	if ($exist && (int)$exist['id'] !== (int)$id) {
		return ['ok' => false, 'msg' => '该用户名已被其他资源池占用'];
	}

	return ['ok' => true, 'msg' => '', 'data' => [
		'name'        => $name,
		'username'    => $username,
		'password'    => $password,
		'nodes'       => json_encode(array_values($nodes), JSON_UNESCAPED_UNICODE),
		'web_space'   => $web,
		'sql_space'   => $sql,
		'flow'        => $flow,
		'expire_date' => $expire,
		'status'      => $status,
		'remark'      => $remark,
	]];
}

/**
 * 新增资源池。
 * @return array ['ok'=>bool, 'msg'=>string, 'id'=>int]
 */
function rp_create($in)
{
	global $DB;
	rp_ensure_schema();
	$v = rp_validate($in, null);
	if (!$v['ok']) {
		return ['ok' => false, 'msg' => $v['msg']];
	}
	$d   = $v['data'];
	$now = rp_now();
	$ok  = $DB->query_prepare(
		"INSERT INTO " . RP_TABLE . " (name, username, password, nodes, web_space, sql_space, flow, expire_date, status, remark, created_at, updated_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)",
		[$d['name'], $d['username'], $d['password'], $d['nodes'], $d['web_space'], $d['sql_space'], $d['flow'], $d['expire_date'], $d['status'], $d['remark'], $now, $now]
	);
	if (!$ok) {
		return ['ok' => false, 'msg' => '写入数据库失败：' . $DB->error()];
	}
	$row = rp_get_by_username($d['username']);
	return ['ok' => true, 'msg' => '添加成功', 'id' => $row ? (int)$row['id'] : 0];
}

/**
 * 更新资源池。密码留空表示不修改。
 * @return array ['ok'=>bool, 'msg'=>string]
 */
function rp_update($id, $in)
{
	global $DB;
	rp_ensure_schema();
	$id   = (int)$id;
	$pool = rp_get($id);
	if (!$pool) {
		return ['ok' => false, 'msg' => '资源池不存在'];
	}
	$v = rp_validate($in, $id);
	if (!$v['ok']) {
		return ['ok' => false, 'msg' => $v['msg']];
	}
	$d = $v['data'];

	if ($d['password'] === '') {
		$ok = $DB->query_prepare(
			"UPDATE " . RP_TABLE . " SET name=?, username=?, nodes=?, web_space=?, sql_space=?, flow=?, expire_date=?, status=?, remark=?, updated_at=? WHERE id=?",
			[$d['name'], $d['username'], $d['nodes'], $d['web_space'], $d['sql_space'], $d['flow'], $d['expire_date'], $d['status'], $d['remark'], rp_now(), $id]
		);
	} else {
		$ok = $DB->query_prepare(
			"UPDATE " . RP_TABLE . " SET name=?, username=?, password=?, nodes=?, web_space=?, sql_space=?, flow=?, expire_date=?, status=?, remark=?, updated_at=? WHERE id=?",
			[$d['name'], $d['username'], $d['password'], $d['nodes'], $d['web_space'], $d['sql_space'], $d['flow'], $d['expire_date'], $d['status'], $d['remark'], rp_now(), $id]
		);
	}
	if (!$ok) {
		return ['ok' => false, 'msg' => '更新失败：' . $DB->error()];
	}
	return ['ok' => true, 'msg' => '保存成功'];
}

/**
 * 删除资源池。仅删除资源池记录本身，不删除已开通的主机；
 * 归属关系随资源池记录一并消失（host_users 存在该行内）。
 * @return array ['ok'=>bool, 'msg'=>string]
 */
function rp_delete($id)
{
	global $DB;
	rp_ensure_schema();
	$id   = (int)$id;
	$pool = rp_get($id);
	if (!$pool) {
		return ['ok' => false, 'msg' => '资源池不存在'];
	}
	if (!$DB->query_prepare("DELETE FROM " . RP_TABLE . " WHERE id=? LIMIT 1", [$id])) {
		return ['ok' => false, 'msg' => '删除失败：' . $DB->error()];
	}
	return ['ok' => true, 'msg' => '删除成功'];
}

/** 切换资源池状态 */
function rp_set_status($id, $status)
{
	global $DB;
	rp_ensure_schema();
	$id = (int)$id;
	if (!isset(rp_statuses()[$status])) {
		return ['ok' => false, 'msg' => '状态非法'];
	}
	if (!rp_get($id)) {
		return ['ok' => false, 'msg' => '资源池不存在'];
	}
	if (!$DB->query_prepare("UPDATE " . RP_TABLE . " SET status=?, updated_at=? WHERE id=?", [$status, rp_now(), $id])) {
		return ['ok' => false, 'msg' => '操作失败：' . $DB->error()];
	}
	return ['ok' => true, 'msg' => '操作成功'];
}

/* ============================================================
 *  配额统计（按已开通主机汇总）
 * ============================================================ */

/** 从 MN_zj 的 JSON 配额字段中取 max 值 */
function rp_json_max($raw)
{
	$j = json_decode((string)$raw, true);
	return is_array($j) ? (float)($j['max'] ?? 0) : 0.0;
}

/**
 * 资源池已开通的主机列表。
 *
 * 归属关系存在资源池表的 host_users（主机账号 JSON 数组），
 * 用 MN_zj.user 反查主机行；账号在 MN_zj 已不存在的（主机被删）自动忽略。
 */
function rp_pool_hosts($pool_id)
{
	global $DB;
	rp_ensure_schema();
	$pool = rp_get($pool_id);
	if (!$pool) {
		return [];
	}
	$users = rp_decode_host_users($pool['host_users'] ?? '');
	if (!$users) {
		return [];
	}
	$place = implode(',', array_fill(0, count($users), '?'));
	return $DB->get_all_prepare(
		"SELECT * FROM MN_zj WHERE user IN ({$place}) ORDER BY id DESC",
		$users
	) ?: [];
}

/**
 * 主机账号 => 资源池 的反查表。
 *
 * @return array user => ['pool_id'=>int, 'pool_name'=>string]
 */
function rp_host_user_map()
{
	global $DB;
	rp_ensure_schema();
	$rows = $DB->get_all_prepare("SELECT id, name, host_users FROM " . RP_TABLE . " WHERE 1") ?: [];
	$map  = [];
	foreach ($rows as $r) {
		foreach (rp_decode_host_users($r['host_users'] ?? '') as $u) {
			// 一个主机账号只归属一个池；先出现的优先（写入时已做互斥校验）
			if (!isset($map[$u])) {
				$map[$u] = ['pool_id' => (int)$r['id'], 'pool_name' => (string)$r['name']];
			}
		}
	}
	return $map;
}

/**
 * 查某主机账号归属的资源池。
 * @return array|null ['pool_id'=>int,'pool_name'=>string]
 */
function rp_find_pool_by_host_user($host_user)
{
	$host_user = trim((string)$host_user);
	if ($host_user === '') {
		return null;
	}
	$map = rp_host_user_map();
	return $map[$host_user] ?? null;
}

/**
 * 把主机账号加入资源池的 host_users。
 * @return array ['ok'=>bool,'msg'=>string]
 */
function rp_bind_host_user($pool_id, $host_user)
{
	global $DB;
	rp_ensure_schema();
	$host_user = trim((string)$host_user);
	$pool      = rp_get($pool_id);
	if (!$pool) {
		return ['ok' => false, 'msg' => '资源池不存在'];
	}
	if ($host_user === '') {
		return ['ok' => false, 'msg' => '主机账号不能为空'];
	}
	if (!$DB->get_row_prepare("SELECT id FROM MN_zj WHERE user=? LIMIT 1", [$host_user])) {
		return ['ok' => false, 'msg' => '主机账号不存在：' . $host_user];
	}
	// 互斥：已归属其他池则拒绝
	$exist = rp_find_pool_by_host_user($host_user);
	if ($exist && $exist['pool_id'] !== (int)$pool['id']) {
		return ['ok' => false, 'msg' => '该主机已归属资源池「' . $exist['pool_name'] . '」，请先解除归属'];
	}
	$users = rp_decode_host_users($pool['host_users'] ?? '');
	if (in_array($host_user, $users, true)) {
		return ['ok' => true, 'msg' => '该主机已在本资源池内'];
	}
	$users[] = $host_user;
	$ok = $DB->query_prepare(
		"UPDATE " . RP_TABLE . " SET host_users=?, updated_at=? WHERE id=?",
		[json_encode(array_values($users), JSON_UNESCAPED_UNICODE), rp_now(), (int)$pool['id']]
	);
	if (!$ok) {
		return ['ok' => false, 'msg' => '写入失败：' . $DB->error()];
	}
	return ['ok' => true, 'msg' => '绑定成功'];
}

/**
 * 把主机账号从资源池的 host_users 移除。
 * @param int|null $pool_id 为 null 时自动查归属池
 * @return array ['ok'=>bool,'msg'=>string]
 */
function rp_unbind_host_user($host_user, $pool_id = null)
{
	global $DB;
	rp_ensure_schema();
	$host_user = trim((string)$host_user);
	if ($host_user === '') {
		return ['ok' => false, 'msg' => '主机账号不能为空'];
	}
	if ($pool_id === null) {
		$found = rp_find_pool_by_host_user($host_user);
		if (!$found) {
			return ['ok' => false, 'msg' => '该主机没有资源池归属'];
		}
		$pool_id = $found['pool_id'];
	}
	$pool = rp_get($pool_id);
	if (!$pool) {
		return ['ok' => false, 'msg' => '资源池不存在'];
	}
	$users = rp_decode_host_users($pool['host_users'] ?? '');
	$left  = array_values(array_filter($users, function ($u) use ($host_user) {
		return $u !== $host_user;
	}));
	if (count($left) === count($users)) {
		return ['ok' => false, 'msg' => '该主机不在此资源池内'];
	}
	$ok = $DB->query_prepare(
		"UPDATE " . RP_TABLE . " SET host_users=?, updated_at=? WHERE id=?",
		[json_encode($left, JSON_UNESCAPED_UNICODE), rp_now(), (int)$pool['id']]
	);
	if (!$ok) {
		return ['ok' => false, 'msg' => '写入失败：' . $DB->error()];
	}
	return ['ok' => true, 'msg' => '已解除归属'];
}

/**
 * 清理失效归属：host_users 里在 MN_zj 已不存在的主机账号。
 * @return array ['ok'=>bool,'msg'=>string,'removed'=>int]
 */
function rp_prune_host_users($pool_id = null)
{
	global $DB;
	rp_ensure_schema();

	if ($pool_id === null) {
		$pools = $DB->get_all_prepare("SELECT id, host_users FROM " . RP_TABLE . " WHERE 1") ?: [];
	} else {
		$one   = rp_get($pool_id);
		$pools = $one ? [$one] : [];
	}

	$removed = 0;
	foreach ($pools as $p) {
		$users = rp_decode_host_users($p['host_users'] ?? '');
		if (!$users) {
			continue;
		}
		$place = implode(',', array_fill(0, count($users), '?'));
		$rows  = $DB->get_all_prepare("SELECT user FROM MN_zj WHERE user IN ({$place})", $users) ?: [];
		$alive = [];
		foreach ($rows as $r) {
			$alive[] = (string)$r['user'];
		}
		$keep = array_values(array_filter($users, function ($u) use ($alive) {
			return in_array($u, $alive, true);
		}));
		if (count($keep) !== count($users)) {
			$removed += count($users) - count($keep);
			$DB->query_prepare(
				"UPDATE " . RP_TABLE . " SET host_users=?, updated_at=? WHERE id=?",
				[json_encode($keep, JSON_UNESCAPED_UNICODE), rp_now(), (int)$p['id']]
			);
		}
	}
	return ['ok' => true, 'msg' => $removed > 0 ? ('已清理 ' . $removed . ' 条失效归属') : '没有失效归属', 'removed' => $removed];
}

/**
 * 统计资源池已分配用量。
 *
 * @return array ['hosts'=>int,'web'=>int,'sql'=>int,'flow'=>int]（单位 MB/MB/GB）
 */
function rp_usage($pool_id)
{
	$out = ['hosts' => 0, 'web' => 0, 'sql' => 0, 'flow' => 0];
	foreach (rp_pool_hosts($pool_id) as $h) {
		$out['hosts']++;
		$out['web']  += (int)rp_json_max($h['hxa'] ?? '');
		$out['sql']  += (int)rp_json_max($h['hxb'] ?? '');
		$out['flow'] += (int)rp_json_max($h['llmax'] ?? '');
	}
	return $out;
}

/**
 * 批量统计多个资源池的用量（避免列表页 N+1 查询）。
 *
 * @param array $pool_ids
 * @return array pool_id => ['hosts'=>int,'web'=>int,'sql'=>int,'flow'=>int]
 */
function rp_usage_batch($pool_ids)
{
	global $DB;
	rp_ensure_schema();

	$ids = [];
	foreach ((array)$pool_ids as $v) {
		$v = (int)$v;
		if ($v > 0 && !in_array($v, $ids, true)) {
			$ids[] = $v;
		}
	}
	$out = [];
	foreach ($ids as $v) {
		$out[$v] = ['hosts' => 0, 'web' => 0, 'sql' => 0, 'flow' => 0];
	}
	if (!$ids) {
		return $out;
	}

	// 取这些池的 host_users，建立 主机账号 => pool_id 映射
	$place = implode(',', array_fill(0, count($ids), '?'));
	$pools = $DB->get_all_prepare("SELECT id, host_users FROM " . RP_TABLE . " WHERE id IN ({$place})", $ids) ?: [];
	$userToPool = [];
	foreach ($pools as $p) {
		foreach (rp_decode_host_users($p['host_users'] ?? '') as $u) {
			if (!isset($userToPool[$u])) {
				$userToPool[$u] = (int)$p['id'];
			}
		}
	}
	if (!$userToPool) {
		return $out;
	}

	// 一次查回所有相关主机
	// 注意：PHP 会把纯数字字符串键转成 int，这里统一转回字符串再绑定
	$users  = array_map('strval', array_keys($userToPool));
	$place2 = implode(',', array_fill(0, count($users), '?'));
	$rows   = $DB->get_all_prepare(
		"SELECT user, hxa, hxb, llmax FROM MN_zj WHERE user IN ({$place2})",
		$users
	) ?: [];
	foreach ($rows as $r) {
		$pid = $userToPool[(string)$r['user']] ?? 0;
		if ($pid <= 0 || !isset($out[$pid])) {
			continue;
		}
		$out[$pid]['hosts']++;
		$out[$pid]['web']  += (int)rp_json_max($r['hxa'] ?? '');
		$out[$pid]['sql']  += (int)rp_json_max($r['hxb'] ?? '');
		$out[$pid]['flow'] += (int)rp_json_max($r['llmax'] ?? '');
	}
	return $out;
}

/** 资源池剩余配额；总额为 0（不限）时返回 null 表示不限 */
function rp_remaining($pool, $usage = null)
{
	if ($usage === null) {
		$usage = rp_usage($pool['id'] ?? 0);
	}
	$calc = function ($total, $used) {
		$total = (int)$total;
		return $total <= 0 ? null : ($total - (int)$used);
	};
	return [
		'web'  => $calc($pool['web_space'] ?? 0, $usage['web']),
		'sql'  => $calc($pool['sql_space'] ?? 0, $usage['sql']),
		'flow' => $calc($pool['flow'] ?? 0, $usage['flow']),
	];
}

/* ============================================================
 *  从资源池开通主机
 * ============================================================
 *  复用核心 addzj 的宝塔调用流程（MPHX/bt_api.php），
 *  额外做：可用节点白名单校验 + 配额校验，并把主机账号写入本池 host_users。
 */

/**
 * @param int   $pool_id
 * @param array $args node/user/pass/web_space/sql_space/flow/domain_count/expire_date/status
 * @return array ['ok'=>bool,'msg'=>string,'host_id'=>int]
 */
function rp_open_host($pool_id, $args)
{
	global $DB, $conf, $date;

	rp_ensure_schema();

	$pool = rp_get($pool_id);
	if (!$pool) {
		return ['ok' => false, 'msg' => '资源池不存在'];
	}
	if (($pool['status'] ?? '') !== 'enabled') {
		return ['ok' => false, 'msg' => '资源池已禁用，无法开通'];
	}
	if (rp_is_expired($pool)) {
		return ['ok' => false, 'msg' => '资源池已于 ' . $pool['expire_date'] . ' 到期，无法开通'];
	}

	$node_dh  = trim((string)($args['node'] ?? ''));
	$hostuser = trim((string)($args['user'] ?? ''));
	$hostpass = (string)($args['pass'] ?? '');
	$web      = (int)($args['web_space'] ?? 0);
	$sqlspace = (int)($args['sql_space'] ?? 0);
	$flow     = (int)($args['flow'] ?? 0);
	$ymbds    = (int)($args['domain_count'] ?? 0);
	$expire   = trim((string)($args['expire_date'] ?? ''));
	$switch   = ($args['status'] ?? 'true') === 'true' ? 'true' : 'false';

	if (mb_strlen($hostuser) < 6 || mb_strlen($hostpass) < 6) {
		return ['ok' => false, 'msg' => '主机账号和密码均不能少于 6 位'];
	}
	if (!preg_match('/^[a-zA-Z0-9_]{6,60}$/', $hostuser)) {
		return ['ok' => false, 'msg' => '主机账号只能用字母/数字/下划线，长度 6-60 位'];
	}
	if ($web < 0 || $sqlspace < 0 || $flow < 0 || $ymbds < 0) {
		return ['ok' => false, 'msg' => '配额不能为负数'];
	}
	if (!rp_valid_date($expire)) {
		return ['ok' => false, 'msg' => '到期日期格式错误，应为 yyyy-mm-dd 或留空'];
	}

	// 可用节点白名单
	$allow = rp_decode_nodes($pool['nodes'] ?? '');
	if ($allow && !in_array($node_dh, $allow, true)) {
		return ['ok' => false, 'msg' => '该节点不在本资源池的可用节点内'];
	}
	$node = $DB->get_row_prepare("SELECT * FROM MN_bt WHERE btdh=? LIMIT 1", [$node_dh]);
	if (!$node) {
		return ['ok' => false, 'msg' => '节点不存在：' . $node_dh];
	}

	// 配额校验
	$usage = rp_usage($pool_id);
	$checks = [
		['total' => (int)$pool['web_space'], 'used' => $usage['web'],  'need' => $web,      'label' => '网页空间',   'unit' => 'MB'],
		['total' => (int)$pool['sql_space'], 'used' => $usage['sql'],  'need' => $sqlspace, 'label' => '数据库空间', 'unit' => 'MB'],
		['total' => (int)$pool['flow'],      'used' => $usage['flow'], 'need' => $flow,     'label' => '流量',       'unit' => 'GB'],
	];
	foreach ($checks as $c) {
		if ($c['total'] > 0 && ($c['used'] + $c['need']) > $c['total']) {
			return ['ok' => false, 'msg' => sprintf(
				'%s配额不足：总额 %d%s，已分配 %d%s，本次需 %d%s',
				$c['label'], $c['total'], $c['unit'], $c['used'], $c['unit'], $c['need'], $c['unit']
			)];
		}
	}

	// 账号查重（本地）
	if ($DB->get_row_prepare("SELECT id FROM MN_zj WHERE user=? LIMIT 1", [$hostuser])) {
		return ['ok' => false, 'msg' => '该主机账号已存在，请更换'];
	}

	// 宝塔 API
	$bt_api_file = ROOT . 'MPHX/bt_api.php';
	if (!is_file($bt_api_file)) {
		return ['ok' => false, 'msg' => 'bt_api 类文件缺失'];
	}
	require_once $bt_api_file;

	$btipe = ($node['ptl'] == 'true' ? 'https' : 'http') . '://' . $node['btip'] . ':' . $node['btdk'];
	$api   = new bt_api($btipe, $node['btmy']);

	// PHP 版本：优先节点已保存的，否则自动探测最新
	$phpVersion = $node['mrbts_php'] ?? '';
	if ($phpVersion === '' || $phpVersion === '00') {
		$phpList = $api->btapi_listphp();
		if (is_array($phpList)) {
			$versions = [];
			foreach ($phpList as $v) {
				if (($v['status'] ?? false) && ($v['version'] ?? '') !== '00') {
					$versions[] = $v['version'];
				}
			}
			if ($versions) {
				usort($versions, function ($a, $b) {
					return strcmp($b, $a);
				});
				$phpVersion = $versions[0];
				$DB->query_prepare("UPDATE MN_bt SET mrbts_php=? WHERE btdh=? LIMIT 1", [$phpVersion, $node_dh]);
			}
		}
	}
	if ($phpVersion === '' || $phpVersion === '00') {
		return ['ok' => false, 'msg' => '无法获取该节点的 PHP 版本，请先在宝塔面板安装 PHP 或在节点管理中设置默认 PHP 版本'];
	}

	// 站点目录名（防重名，与核心 addzj 同风格）
	$now     = $date ?: date('Y-m-d H:i:s');
	$wjler   = substr(md5($now . $hostuser . mt_rand(100, 999)), mt_rand(4, 10), 6);
	$btserw  = 'mnbt.' . mt_rand(1, 999) . $wjler;
	$mrml    = ($node['btos'] == '1' ? $conf['hxi'] : $conf['hxo']) . '/' . $btserw;
	$datae   = $expire === '' ? '0000-00-00' : $expire;

	$r_data = $api->webkt($hostuser, $hostpass, $btserw, '主机', 'true', 'true', $phpVersion, $mrml);
	if (empty($r_data['siteStatus'])) {
		return ['ok' => false, 'msg' => '宝塔创建站点失败：' . ($r_data['msg'] ?? '未知错误')];
	}
	$siteId = $r_data['siteId'] ?? 0;

	$r_datan = $api->setdqsj($siteId, $datae);
	if (!(($r_datan['status'] ?? '') == '1' || ($r_datan['status'] ?? '') == 'true')) {
		@error_log('[resource_pool] setdqsj failed for host ' . $hostuser);
	}

	// FTP / 数据库 ID
	$ftpid = '0';
	$sqlid = '0';
	$r_ftp = $api->sjlist('ftps');
	$r_sql = $api->sjlist('databases');
	if (isset($r_ftp['data']) && is_array($r_ftp['data'])) {
		foreach ($r_ftp['data'] as $v) {
			if (($v['name'] ?? '') === $hostuser) {
				$ftpid = $v['id'];
				break;
			}
		}
	}
	if (isset($r_sql['data']) && is_array($r_sql['data'])) {
		foreach ($r_sql['data'] as $v) {
			if (($v['name'] ?? '') === $hostuser) {
				$sqlid = $v['id'];
				break;
			}
		}
	}

	$webdx = json_encode(['max' => $web, 'dq' => 0]);
	$sqldx = json_encode(['max' => $sqlspace, 'dq' => 0]);
	$lldx  = json_encode(['max' => $flow, 'dq' => 0, 'statistics' => false]);

	// 不显式指定 id，交给 AUTO_INCREMENT，避免并发开通时 id 撞车；
	// 随后用唯一的 user 字段回查真实 id。写入的是核心原有字段，未改主机表结构。
	$ok = $DB->query_prepare(
		"INSERT INTO `MN_zj` (`ssbt`, `user`, `pass`, `sqluser`, `sqlpass`, `data`, `datae`, `qk`, `btid`, `sqldz`, `ftpid`, `ymbds`, `hxa`, `hxb`, `hxc`, `hxd`, `llmax`) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)",
		[$node_dh, $hostuser, $hostpass, $hostuser, $hostpass, $now, $datae, $switch, $siteId, $btserw, $ftpid, (string)$ymbds, $webdx, $sqldx, '2', $sqlid, $lldx]
	);
	if (!$ok) {
		return ['ok' => false, 'msg' => '宝塔已开通但本地写库失败，请联系管理员（siteId=' . $siteId . '）：' . $DB->error()];
	}

	$newRow = $DB->get_row_prepare("SELECT id FROM MN_zj WHERE user=? ORDER BY id DESC LIMIT 1", [$hostuser]);
	$newId  = $newRow ? (int)$newRow['id'] : 0;

	// 记录归属：写进资源池表的 host_users
	$bind = rp_bind_host_user($pool_id, $hostuser);
	if (!$bind['ok']) {
		@error_log('[resource_pool] bind host_user failed: pool=' . $pool_id . ' user=' . $hostuser . ' msg=' . $bind['msg']);
	}

	// 开关为关：宝塔侧同步停站 + 停 FTP（webkt 创建出来默认是运行状态）
	if ($switch === 'false') {
		@$api->siteqt($siteId, $btserw, false);
		@$api->setftpzt($ftpid, $hostuser, '0');
	}

	if (function_exists('mnbt_do_action')) {
		$host_row = $DB->get_row_prepare("SELECT * FROM MN_zj WHERE id=? LIMIT 1", [$newId]);
		mnbt_do_action(
			'host.created',
			$host_row ?: ['id' => $newId, 'user' => $hostuser, 'ssbt' => $node_dh],
			['source' => 'resource_pool', 'pool_id' => (int)$pool_id]
		);
	}

	return ['ok' => true, 'msg' => '开通成功', 'host_id' => $newId, 'site' => $btserw];
}

/**
 * 资源池名称映射：pool_id => name
 */
function rp_name_map()
{
	global $DB;
	rp_ensure_schema();
	$rows = $DB->get_all_prepare("SELECT id, name FROM " . RP_TABLE . " WHERE 1") ?: [];
	$map  = [];
	foreach ($rows as $r) {
		$map[(int)$r['id']] = (string)$r['name'];
	}
	return $map;
}

/**
 * 主机账号 => 资源池名（供主机列表注入列展示）
 *
 * 键统一转成字符串：纯数字的主机账号（如 123456）会被 PHP 转成 int 键，
 * 若恰好构成连续下标，json_encode 会输出 JSON 数组而不是对象，导致前端查不到。
 * 调用方 json_encode 时请带 JSON_FORCE_OBJECT。
 */
function rp_host_user_name_map()
{
	$out = [];
	foreach (rp_host_user_map() as $u => $info) {
		$out[(string)$u] = $info['pool_name'];
	}
	return $out;
}

/**
 * 所有资源池开通的主机（跨池），可按资源池筛选。
 *
 * @param int    $pool_id 0 = 全部资源池
 * @param string $kw      关键词（主机账号 / 网站名 / 所属宝塔）
 * @return array 每行为 MN_zj 行 + pool_id / pool_name
 */
function rp_all_pool_hosts($pool_id = 0, $kw = '')
{
	global $DB;
	rp_ensure_schema();

	$pool_id = (int)$pool_id;
	$kw      = trim((string)$kw);
	$map     = rp_host_user_map();

	// 按资源池过滤出目标主机账号（键可能被 PHP 转成 int，统一转回字符串）
	$users = [];
	foreach ($map as $u => $info) {
		if ($pool_id > 0 && $info['pool_id'] !== $pool_id) {
			continue;
		}
		$users[] = (string)$u;
	}
	if (!$users) {
		return [];
	}

	$place  = implode(',', array_fill(0, count($users), '?'));
	$params = $users;
	$sql    = "SELECT * FROM MN_zj WHERE user IN ({$place})";
	if ($kw !== '') {
		$sql .= " AND (user LIKE ? OR sqldz LIKE ? OR ssbt LIKE ?)";
		$like = '%' . $kw . '%';
		$params[] = $like;
		$params[] = $like;
		$params[] = $like;
	}
	$sql .= " ORDER BY id DESC";

	$rows = $DB->get_all_prepare($sql, $params) ?: [];
	foreach ($rows as $i => $r) {
		$u = (string)$r['user'];
		$rows[$i]['pool_id']   = $map[$u]['pool_id'] ?? 0;
		$rows[$i]['pool_name'] = $map[$u]['pool_name'] ?? '';
	}
	return $rows;
}

/**
 * 未归属任何资源池的主机（供「绑定到资源池」选择）。
 */
function rp_unbound_hosts($limit = 500)
{
	global $DB;
	rp_ensure_schema();
	$limit = max(1, min(2000, (int)$limit));
	$rows  = $DB->get_all_prepare("SELECT id, user, sqldz, ssbt FROM MN_zj WHERE 1 ORDER BY id DESC LIMIT {$limit}") ?: [];
	$map   = rp_host_user_map();
	$out   = [];
	foreach ($rows as $r) {
		if (!isset($map[(string)$r['user']])) {
			$out[] = $r;
		}
	}
	return $out;
}
