<?php
/**
 * official_site 插件 - 核心函数库
 *
 * 提供：产品/新闻/留言的 CRUD 与前台数据查询，供 bootstrap.php 与 admin 视图使用。
 */

if (!defined('IN_CRONLITE')) {
	exit;
}

/* ============================================================
 *  辅助函数
 * ============================================================ */

/** 管理员端插件页面 URL。 */
function site_admin_url($page, $extra = '')
{
	$base = 'plugin.php?p=official_site&page=' . rawurlencode($page);
	if ($extra !== '') {
		$base .= '&' . ltrim($extra, '&');
	}
	return $base;
}

/** 输出 JSON 并退出。 */
function site_json($code, $extra = [])
{
	@header('Content-Type: application/json; charset=UTF-8');
	$payload = ['code' => $code];
	if (is_array($extra)) {
		$payload = array_merge($payload, $extra);
	}
	echo json_encode($payload, JSON_UNESCAPED_UNICODE);
	exit;
}

/** 产品分类定义（MNBT 业务语境）。 */
function site_product_categories()
{
	return [
		'ai'       => 'AI 智能',
		'cloud'    => '云服务器',
		'hosting'  => '虚拟主机',
		'domain'   => '域名服务',
		'security' => '安全防护',
		'service'  => '增值服务',
	];
}

/* ============================================================
 *  产品
 * ============================================================ */

/** 解析产品特性（JSON 字符串 ↔ 数组）。 */
function site_product_features_parse($row)
{
	$features = [];
	$raw = trim((string)($row['features'] ?? ''));
	if ($raw !== '') {
		$decoded = json_decode($raw, true);
		if (is_array($decoded)) {
			$features = array_values(array_filter(array_map('trim', $decoded), function ($v) {
				return $v !== '';
			}));
		}
	}
	return $features;
}

/**
 * 产品列表。
 * @param string $status   ''=全部，'active'/'inactive'
 * @param string $category ''=全部，或具体分类
 */
function site_product_list($status = '', $category = '')
{
	global $DB;
	$sql = "SELECT * FROM MN_plugin_site_product";
	$where = [];
	$args = [];
	if ($status !== '') {
		$where[] = "status = ?";
		$args[] = $status;
	}
	if ($category !== '' && $category !== 'all') {
		$where[] = "category = ?";
		$args[] = $category;
	}
	if (!empty($where)) {
		$sql .= " WHERE " . implode(' AND ', $where);
	}
	$sql .= " ORDER BY sort ASC, id ASC";
	$rows = $DB->get_all_prepare($sql, $args) ?: [];
	$list = [];
	foreach ($rows as $r) {
		$r['features_list'] = site_product_features_parse($r);
		$list[] = $r;
	}
	return $list;
}

/** 产品详情（含特性数组）。 */
function site_product_get($id)
{
	global $DB;
	$row = $DB->get_row_prepare("SELECT * FROM MN_plugin_site_product WHERE id=? LIMIT 1", [(int)$id]) ?: null;
	if ($row) {
		$row['features_list'] = site_product_features_parse($row);
	}
	return $row;
}

/** 保存产品（新增/更新），返回 true 或错误消息。 */
function site_product_save($data)
{
	global $DB;
	$now = date('Y-m-d H:i:s');
	$features = isset($data['features']) && is_array($data['features']) ? $data['features'] : [];
	$features = array_values(array_filter(array_map('trim', $features), function ($v) {
		return $v !== '';
	}));
	$fields = [
		'name'        => trim((string)($data['name'] ?? '')),
		'category'    => trim((string)($data['category'] ?? '')),
		'description' => trim((string)($data['description'] ?? '')),
		'features'    => json_encode($features, JSON_UNESCAPED_UNICODE),
		'image'       => trim((string)($data['image'] ?? '')),
		'status'      => ($data['status'] ?? 'active') === 'inactive' ? 'inactive' : 'active',
		'sort'        => max(0, (int)($data['sort'] ?? 50)),
	];
	if ($fields['name'] === '') {
		return '产品名称不能为空';
	}
	if ($fields['category'] === '') {
		return '请选择产品分类';
	}

	$id = (int)($data['id'] ?? 0);
	if ($id > 0) {
		$ok = $DB->query_prepare(
			"UPDATE MN_plugin_site_product SET name=?, category=?, description=?, features=?, image=?, status=?, sort=? WHERE id=?",
			[$fields['name'], $fields['category'], $fields['description'], $fields['features'], $fields['image'], $fields['status'], $fields['sort'], $id]
		);
		return $ok ? true : '更新失败';
	}
	$ok = $DB->query_prepare(
		"INSERT INTO MN_plugin_site_product (name, category, description, features, image, status, sort, created_at) VALUES (?,?,?,?,?,?,?,?)",
		[$fields['name'], $fields['category'], $fields['description'], $fields['features'], $fields['image'], $fields['status'], $fields['sort'], $now]
	);
	return $ok ? true : '新增失败';
}

/** 删除产品。 */
function site_product_delete($id)
{
	global $DB;
	return (bool)$DB->query_prepare("DELETE FROM MN_plugin_site_product WHERE id=? LIMIT 1", [(int)$id]);
}

/* ============================================================
 *  新闻
 * ============================================================ */

/** 新闻分类统计（前台侧边栏）。 */
function site_news_categories()
{
	global $DB;
	return $DB->get_all_prepare(
		"SELECT category, COUNT(*) AS cnt FROM MN_plugin_site_news WHERE status='active' AND category != '' GROUP BY category ORDER BY cnt DESC, category ASC"
	) ?: [];
}

/**
 * 新闻分页列表。
 * @param string $status   'active'=仅上架（默认，前台）；''=全部（管理端）
 */
function site_news_list($page = 1, $per = 6, $category = '', $status = 'active')
{
	global $DB;
	$page = max(1, (int)$page);
	$per = max(1, min(100, (int)$per));
	$where = [];
	$args = [];
	if ($status !== '') {
		$where[] = "status = ?";
		$args[] = $status;
	}
	if ($category !== '' && $category !== 'all') {
		$where[] = "category = ?";
		$args[] = $category;
	}
	$whereSql = $where === [] ? '' : (' WHERE ' . implode(' AND ', $where));
	$total = (int)($DB->get_row_prepare("SELECT COUNT(*) AS c FROM MN_plugin_site_news" . $whereSql, $args)['c'] ?? 0);
	$offset = ($page - 1) * $per;
	$rows = $DB->get_all_prepare(
		"SELECT * FROM MN_plugin_site_news" . $whereSql . " ORDER BY sort ASC, id DESC LIMIT {$offset},{$per}",
		$args
	) ?: [];
	return ['total' => $total, 'list' => $rows];
}

/** 新闻详情（$incViews 为 true 时累计浏览量）。 */
function site_news_get($id, $incViews = false)
{
	global $DB;
	$id = (int)$id;
	$row = $DB->get_row_prepare("SELECT * FROM MN_plugin_site_news WHERE id=? LIMIT 1", [$id]) ?: null;
	if ($row && $incViews) {
		$DB->query_prepare("UPDATE MN_plugin_site_news SET views = views + 1 WHERE id=?", [$id]);
		$row['views'] = (int)$row['views'] + 1;
	}
	return $row;
}

/** 热门新闻（按浏览量）。 */
function site_news_popular($limit = 4)
{
	global $DB;
	$limit = max(1, min(20, (int)$limit));
	return $DB->get_all_prepare(
		"SELECT id, title, views, created_at FROM MN_plugin_site_news WHERE status='active' ORDER BY views DESC, id DESC LIMIT " . $limit
	) ?: [];
}

/** 保存新闻（新增/更新），返回 true 或错误消息。 */
function site_news_save($data)
{
	global $DB;
	$now = date('Y-m-d H:i:s');
	$fields = [
		'title'    => trim((string)($data['title'] ?? '')),
		'category' => trim((string)($data['category'] ?? '')),
		'content'  => (string)($data['content'] ?? ''),
		'status'   => ($data['status'] ?? 'active') === 'inactive' ? 'inactive' : 'active',
		'sort'     => max(0, (int)($data['sort'] ?? 50)),
	];
	if ($fields['title'] === '') {
		return '新闻标题不能为空';
	}
	if ($fields['content'] === '') {
		return '新闻内容不能为空';
	}

	$id = (int)($data['id'] ?? 0);
	if ($id > 0) {
		$ok = $DB->query_prepare(
			"UPDATE MN_plugin_site_news SET title=?, category=?, content=?, status=?, sort=? WHERE id=?",
			[$fields['title'], $fields['category'], $fields['content'], $fields['status'], $fields['sort'], $id]
		);
		return $ok ? true : '更新失败';
	}
	$ok = $DB->query_prepare(
		"INSERT INTO MN_plugin_site_news (title, category, content, views, status, sort, created_at) VALUES (?,?,?,0,?,?,?)",
		[$fields['title'], $fields['category'], $fields['content'], $fields['status'], $fields['sort'], $now]
	);
	return $ok ? true : '新增失败';
}

/** 删除新闻。 */
function site_news_delete($id)
{
	global $DB;
	return (bool)$DB->query_prepare("DELETE FROM MN_plugin_site_news WHERE id=? LIMIT 1", [(int)$id]);
}

/* ============================================================
 *  留言
 * ============================================================ */

/** 新增留言（前台提交），返回 true 或错误消息。 */
function site_message_add($data)
{
	global $DB;
	$now = date('Y-m-d H:i:s');
	$fields = [
		'name'    => trim((string)($data['name'] ?? '')),
		'email'   => trim((string)($data['email'] ?? '')),
		'phone'   => trim((string)($data['phone'] ?? '')),
		'message' => trim((string)($data['message'] ?? '')),
	];
	if ($fields['name'] === '') {
		return '请输入姓名';
	}
	if ($fields['email'] === '' || !filter_var($fields['email'], FILTER_VALIDATE_EMAIL)) {
		return '请输入有效的邮箱地址';
	}
	if ($fields['message'] === '') {
		return '请输入留言内容';
	}
	$ok = $DB->query_prepare(
		"INSERT INTO MN_plugin_site_message (name, email, phone, message, is_read, created_at) VALUES (?,?,?,?,0,?)",
		[$fields['name'], $fields['email'], $fields['phone'], $fields['message'], $now]
	);
	return $ok ? true : '留言提交失败，请稍后重试';
}

/** 留言分页列表（管理端）。 */
function site_message_list($page = 1, $per = 15)
{
	global $DB;
	$page = max(1, (int)$page);
	$per = max(1, min(100, (int)$per));
	$total = (int)($DB->get_row_prepare("SELECT COUNT(*) AS c FROM MN_plugin_site_message")['c'] ?? 0);
	$offset = ($page - 1) * $per;
	$rows = $DB->get_all_prepare(
		"SELECT * FROM MN_plugin_site_message ORDER BY id DESC LIMIT {$offset},{$per}"
	) ?: [];
	return ['total' => $total, 'list' => $rows];
}

/** 删除留言。 */
function site_message_delete($id)
{
	global $DB;
	return (bool)$DB->query_prepare("DELETE FROM MN_plugin_site_message WHERE id=? LIMIT 1", [(int)$id]);
}

/** 标记留言已读/未读。 */
function site_message_set_read($id, $read)
{
	global $DB;
	return (bool)$DB->query_prepare("UPDATE MN_plugin_site_message SET is_read=? WHERE id=?", [$read ? 1 : 0, (int)$id]);
}
