<?php
if (!defined('IN_CRONLITE')) {
	exit;
}

// ===================================================================
//  插件注册
// ===================================================================

mnbt_plugin_register('auto_deploy', [
	'name' => '高级自动部署',
]);

// ===================================================================
//  用户端菜单（插件核心：用户自主部署网站）
// ===================================================================

mnbt_register_menu('user', [
	'title'    => '一键部署',
	'icon'     => 'mdi-package-variant',
	'order'    => 30,
	'children' => [
		['title' => '软件商店', 'page' => 'index', 'icon' => 'mdi-monitor-dashboard', 'multitabs' => true],
		['title' => '部署记录', 'page' => 'history', 'icon' => 'mdi-history', 'multitabs' => true],
	],
]);

mnbt_register_page('user', 'index', 'user/index.php', '软件商店');
mnbt_register_page('user', 'history', 'user/history.php', '部署记录');

// ===================================================================
//  管理端菜单（仅只读查看所有用户部署记录，不提供部署操作）
// ===================================================================

mnbt_register_menu('admin', [
	'title'    => '自动部署',
	'icon'     => 'mdi-package-variant',
	'order'    => 25,
	'children' => [
		['title' => '部署记录', 'page' => 'history', 'icon' => 'mdi-history', 'multitabs' => true],
	],
]);

mnbt_register_page('admin', 'history', 'admin/history.php', '部署记录');

// ===================================================================
//  辅助函数
// ===================================================================

/**
 * 根据 MN_bt.id 创建 bt_api 实例
 */
function auto_deploy_init_api($nodeId)
{
	global $DB;

	$node = $DB->get_row_prepare("SELECT * FROM MN_bt WHERE id=? AND qk<>'false' LIMIT 1", [(int)$nodeId]);
	if (!$node) {
		return ['api' => null, 'node' => null, 'error' => '节点不存在或已禁用'];
	}

	$ptl = ($node['ptl'] == 'true') ? 'https' : 'http';
	$btipe = $ptl . '://' . $node['btip'] . ':' . $node['btdk'];

	if (!class_exists('bt_api')) {
		require_once SYSTEM_ROOT . 'bt_api.php';
	}

	$api = new bt_api($btipe, $node['btmy']);
	return ['api' => $api, 'node' => $node, 'error' => ''];
}

/**
 * 获取当前用户绑定的主机（系统设计：一个用户对应一个主机，取第一条）
 * 主机 = MN_zj 中的一条记录，对应宝塔面板上的一个网站（sqldz 为网站名）
 * 附带节点信息与密钥，返回 null 表示无可用主机
 */
function auto_deploy_get_my_host()
{
	global $DB, $yhc;

	if (empty($yhc['user'])) {
		return null;
	}

	return $DB->get_row_prepare(
		"SELECT z.id, z.ssbt, z.sqldz, z.ymbds, z.btid, z.datae,
		        b.id AS node_id, b.btdh, b.btip, b.btdk, b.btos, b.ptl, b.btmy
		 FROM MN_zj z INNER JOIN MN_bt b ON b.btdh = z.ssbt
		 WHERE z.user=? AND z.qk<>'false' AND b.qk<>'false'
		 ORDER BY z.id ASC LIMIT 1",
		[$yhc['user']]
	) ?: null;
}

/**
 * 记录部署日志
 */
function auto_deploy_log($nodeId, $nodeName, $dname, $siteName, $projectType, $result, $operator, $adminPwd = '', $successUrl = '')
{
	global $DB;
	$DB->query_prepare(
		"INSERT INTO plg_auto_deploy_log (node_id, node_name, dname, site_name, project_type, result, admin_username, admin_password, success_url, admin_user, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
		[(int)$nodeId, $nodeName, $dname, $siteName, $projectType, $result, $operator, $adminPwd, $successUrl, $operator, date('Y-m-d H:i:s')]
	);
}

// ===================================================================
//  用户端 AJAX
// ===================================================================

// --- 我的主机信息（含节点） ---
mnbt_register_ajax('user', 'p_auto_deploy_hosts', function () {
	mnbt_plugin_require_user();
	$host = auto_deploy_get_my_host();
	json_exit_success('ok', ['host' => $host]);
});

// --- 软件包列表（按节点，节点须属于当前用户主机） ---
mnbt_register_ajax('user', 'p_auto_deploy_packages', function () {
	mnbt_plugin_require_user();
	$nodeId = isset($_POST['node_id']) ? (int)$_POST['node_id'] : 0;
	$host = auto_deploy_get_my_host();
	if (!$host || (int)$host['node_id'] !== $nodeId) {
		json_exit_error('参数错误');
	}
	$r = auto_deploy_init_api($nodeId);
	if ($r['error'] !== '') {
		json_exit_error($r['error']);
	}
	$result = $r['api']->deployment_get_list();
	if (!$result || empty($result['list'])) {
		json_exit_success('ok', ['packages' => []]);
		return;
	}
	json_exit_success('ok', ['packages' => $result['list']]);
});

// --- 一键部署到我的主机 ---
mnbt_register_ajax('user', 'p_auto_deploy_setup', function () {
	mnbt_plugin_require_user();
	global $yhc;

	$dname = isset($_POST['dname']) ? trim((string)$_POST['dname']) : '';
	$projectType = isset($_POST['project_type']) ? trim((string)$_POST['project_type']) : 'php';

	if ($dname === '') json_exit_error('请选择软件包');

	$host = auto_deploy_get_my_host();
	if (!$host) json_exit_error('未找到可用的主机');

	$siteName = $host['sqldz'];
	if ($siteName === '') json_exit_error('主机未绑定网站');

	$ptl = ($host['ptl'] == 'true') ? 'https' : 'http';
	$btipe = $ptl . '://' . $host['btip'] . ':' . $host['btdk'];
	if (!class_exists('bt_api')) {
		require_once SYSTEM_ROOT . 'bt_api.php';
	}
	$api = new bt_api($btipe, $host['btmy']);

	$result = $api->deployment_setup_package($dname, $siteName, $projectType);

	if (!$result || empty($result['status'])) {
		$errMsg = is_array($result) ? ($result['msg'] ?? '部署失败') : '部署失败：面板返回内容无法解析，请检查节点面板的「一键部署」功能是否可用';
		auto_deploy_log((int)$host['node_id'], $host['btdh'] ?? '', $dname, $siteName, $projectType, 'fail', $yhc['user']);
		json_exit_error($errMsg);
	}

	$msg = $result['msg'] ?? [];
	$adminUser = is_array($msg) ? ($msg['admin_username'] ?? '') : '';
	$adminPwd = is_array($msg) ? ($msg['admin_password'] ?? '') : '';
	$successUrl = is_array($msg) ? ($msg['success_url'] ?? '') : '';

	auto_deploy_log((int)$host['node_id'], $host['btdh'] ?? '', $dname, $siteName, $projectType, 'success', $yhc['user'], $adminPwd, $successUrl);

	json_exit_success('部署成功', [
		'admin_username' => $adminUser,
		'admin_password' => $adminPwd,
		'success_url' => $successUrl,
	]);
});

// --- 我的部署记录 ---
mnbt_register_ajax('user', 'p_auto_deploy_history', function () {
	mnbt_plugin_require_user();
	global $DB, $yhc;
	$page = isset($_POST['page']) ? max(1, (int)$_POST['page']) : 1;
	$limit = 20;
	$offset = ($page - 1) * $limit;

	$total = $DB->get_row_prepare("SELECT COUNT(*) AS cnt FROM plg_auto_deploy_log WHERE admin_user=?", [$yhc['user']])['cnt'] ?? 0;
	$rows = $DB->get_all_prepare("SELECT * FROM plg_auto_deploy_log WHERE admin_user=? ORDER BY id DESC LIMIT ?, ?", [$yhc['user'], $offset, $limit]) ?: [];

	json_exit_success('ok', [
		'rows' => $rows,
		'total' => (int)$total,
		'page' => $page,
		'pages' => max(1, ceil($total / $limit)),
	]);
});

// ===================================================================
//  管理端 AJAX
// ===================================================================

// --- 全部用户的部署记录（只读） ---
mnbt_register_ajax('admin', 'p_auto_deploy_history', function () {
	mnbt_plugin_require_admin();
	global $DB;
	$page = isset($_POST['page']) ? max(1, (int)$_POST['page']) : 1;
	$limit = 20;
	$offset = ($page - 1) * $limit;

	$total = $DB->get_row_prepare("SELECT COUNT(*) AS cnt FROM plg_auto_deploy_log")['cnt'] ?? 0;
	$rows = $DB->get_all_prepare("SELECT * FROM plg_auto_deploy_log ORDER BY id DESC LIMIT ?, ?", [$offset, $limit]) ?: [];

	json_exit_success('ok', [
		'rows' => $rows,
		'total' => (int)$total,
		'page' => $page,
		'pages' => max(1, ceil($total / $limit)),
	]);
});
