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
//  菜单注册（独立分组，含多个子入口）
// ===================================================================

mnbt_register_menu('admin', [
	'title'    => '自动部署',
	'icon'     => 'mdi-rocket-launch',
	'order'    => 25,
	'children' => [
		['title' => '部署中心', 'page' => 'index', 'icon' => 'mdi-monitor-dashboard', 'multitabs' => true],
		['title' => '部署历史', 'page' => 'history', 'icon' => 'mdi-history', 'multitabs' => true],
	],
]);

mnbt_register_page('admin', 'index', 'admin/index.php', '部署中心');
mnbt_register_page('admin', 'history', 'admin/history.php', '部署历史');

mnbt_register_settings_tab([
	'title' => '自动部署',
	'page' => 'index',
	'order' => 25,
]);

// ===================================================================
//  辅助函数：实例化 bt_api
// ===================================================================

/**
 * 根据节点 ID 创建 bt_api 实例
 * @param int $nodeId MN_bt 表的主键
 * @return array{api: bt_api|null, node: array|null, error: string}
 */
function auto_deploy_init_api($nodeId)
{
	global $DB;

	$node = $DB->get_row_prepare("SELECT * FROM MN_bt WHERE id=? AND qk=1 LIMIT 1", [(int)$nodeId]);
	if (!$node) {
		return ['api' => null, 'node' => null, 'error' => '节点不存在或已禁用'];
	}

	$ptl = ($node['ptl'] == 'true') ? 'https' : 'http';
	$btipe = $ptl . '://' . $node['btip'] . ':' . $node['btdk'];

	if (!class_exists('bt_api')) {
		require_once MPHX_PATH . 'bt_api.php';
	}

	$api = new bt_api($btipe, $node['btmy']);
	return ['api' => $api, 'node' => $node, 'error' => ''];
}

/**
 * 设备列表（返回所有启用的 BT 节点）
 * @return array
 */
function auto_deploy_get_nodes()
{
	global $DB;
	return $DB->get_all_prepare("SELECT id, btdh, btip, btdk, btos, ptl FROM MN_bt WHERE qk=1 ORDER BY id ASC") ?: [];
}

/**
 * 记录部署日志到本地数据库
 */
function auto_deploy_log($nodeId, $nodeName, $dname, $siteName, $projectType, $result, $adminUser, $adminPwd = '', $successUrl = '')
{
	global $DB;
	$DB->query_prepare(
		"INSERT INTO plg_auto_deploy_log (node_id, node_name, dname, site_name, project_type, result, admin_username, admin_password, success_url, admin_user, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
		[(int)$nodeId, $nodeName, $dname, $siteName, $projectType, $result, $adminUser, $adminPwd, $successUrl, $adminUser, date('Y-m-d H:i:s')]
	);
}

// ===================================================================
//  AJAX 端点
// ===================================================================

// --- 获取节点列表 ---
mnbt_register_ajax('admin', 'p_auto_deploy_nodes', function () {
	mnbt_plugin_require_admin();
	$nodes = auto_deploy_get_nodes();
	json_exit_success('ok', ['nodes' => $nodes]);
});

// --- 获取软件包列表 ---
mnbt_register_ajax('admin', 'p_auto_deploy_packages', function () {
	mnbt_plugin_require_admin();
	$nodeId = isset($_POST['node_id']) ? (int)$_POST['node_id'] : 0;
	if ($nodeId <= 0) {
		json_exit_error('请选择节点');
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

// --- 获取网站列表 ---
mnbt_register_ajax('admin', 'p_auto_deploy_sites', function () {
	mnbt_plugin_require_admin();
	$nodeId = isset($_POST['node_id']) ? (int)$_POST['node_id'] : 0;
	if ($nodeId <= 0) {
		json_exit_error('请选择节点');
	}
	$r = auto_deploy_init_api($nodeId);
	if ($r['error'] !== '') {
		json_exit_error($r['error']);
	}
	$result = $r['api']->deployment_get_site_list();
	if (!$result || empty($result['list'])) {
		json_exit_success('ok', ['sites' => []]);
		return;
	}
	json_exit_success('ok', ['sites' => $result['list']]);
});

// --- 一键部署 ---
mnbt_register_ajax('admin', 'p_auto_deploy_setup', function () {
	mnbt_plugin_require_admin();
	global $islogin;

	$nodeId = isset($_POST['node_id']) ? (int)$_POST['node_id'] : 0;
	$dname = isset($_POST['dname']) ? trim((string)$_POST['dname']) : '';
	$siteName = isset($_POST['site_name']) ? trim((string)$_POST['site_name']) : '';
	$projectType = isset($_POST['project_type']) ? trim((string)$_POST['project_type']) : 'php';

	if ($nodeId <= 0) json_exit_error('请选择节点');
	if ($dname === '') json_exit_error('请选择软件包');
	if ($siteName === '') json_exit_error('请选择目标网站');

	$r = auto_deploy_init_api($nodeId);
	if ($r['error'] !== '') json_exit_error($r['error']);

	$result = $r['api']->deployment_setup_package($dname, $siteName, $projectType);

	if (!$result || empty($result['status'])) {
		$errMsg = is_array($result) ? ($result['msg'] ?? '部署失败') : 'API 响应异常';
		auto_deploy_log($nodeId, $r['node']['btdh'] ?? '', $dname, $siteName, $projectType, 'fail', $islogin);
		json_exit_error($errMsg);
	}

	$msg = $result['msg'] ?? [];
	$adminUser = is_array($msg) ? ($msg['admin_username'] ?? '') : '';
	$adminPwd = is_array($msg) ? ($msg['admin_password'] ?? '') : '';
	$successUrl = is_array($msg) ? ($msg['success_url'] ?? '') : '';

	auto_deploy_log($nodeId, $r['node']['btdh'] ?? '', $dname, $siteName, $projectType, 'success', $islogin, $adminUser, $adminPwd, $successUrl);

	json_exit_success('部署成功', [
		'admin_username' => $adminUser,
		'admin_password' => $adminPwd,
		'success_url' => $successUrl,
	]);
});

// --- 环境检测 ---
mnbt_register_ajax('admin', 'p_auto_deploy_env', function () {
	mnbt_plugin_require_admin();
	$nodeId = isset($_POST['node_id']) ? (int)$_POST['node_id'] : 0;
	if ($nodeId <= 0) {
		json_exit_error('请选择节点');
	}
	$r = auto_deploy_init_api($nodeId);
	if ($r['error'] !== '') {
		json_exit_error($r['error']);
	}
	$result = $r['api']->deployment_check_project_env();
	if (!$result) {
		json_exit_error('环境检测失败');
	}
	json_exit_success('ok', ['env' => $result]);
});

// --- 部署进度 ---
mnbt_register_ajax('admin', 'p_auto_deploy_speed', function () {
	mnbt_plugin_require_admin();
	$nodeId = isset($_POST['node_id']) ? (int)$_POST['node_id'] : 0;
	if ($nodeId <= 0) {
		json_exit_error('请选择节点');
	}
	$r = auto_deploy_init_api($nodeId);
	if ($r['error'] !== '') {
		json_exit_error($r['error']);
	}
	$result = $r['api']->deployment_get_speed();
	json_exit_success('ok', ['speed' => $result]);
});

// --- 部署日志（BT 面板侧） ---
mnbt_register_ajax('admin', 'p_auto_deploy_btlog', function () {
	mnbt_plugin_require_admin();
	$nodeId = isset($_POST['node_id']) ? (int)$_POST['node_id'] : 0;
	if ($nodeId <= 0) {
		json_exit_error('请选择节点');
	}
	$r = auto_deploy_init_api($nodeId);
	if ($r['error'] !== '') {
		json_exit_error($r['error']);
	}
	$result = $r['api']->deployment_get_in_log();
	json_exit_success('ok', ['log' => $result['msg'] ?? '']);
});

// --- 获取自定义包列表 ---
mnbt_register_ajax('admin', 'p_auto_deploy_custom_pkgs', function () {
	mnbt_plugin_require_admin();
	$nodeId = isset($_POST['node_id']) ? (int)$_POST['node_id'] : 0;
	if ($nodeId <= 0) {
		json_exit_error('请选择节点');
	}
	$r = auto_deploy_init_api($nodeId);
	if ($r['error'] !== '') {
		json_exit_error($r['error']);
	}

	// 先获取全部包，再获取自定义包详情
	$allList = $r['api']->deployment_get_list();
	$allPkgs = $allList['list'] ?? [];
	$customNames = [];

	// 遍历包列表，对存在 name 字段的记录单独查询详情
	foreach ($allPkgs as $pkg) {
		$pname = $pkg['name'] ?? '';
		if ($pname !== '') {
			$detail = $r['api']->deployment_get_package_other($pname);
			if ($detail && !isset($detail['status'])) {
				// GetPackageOther 返回的是包详情对象（无 status 字段）
				$customNames[] = $detail;
			}
		}
	}
	json_exit_success('ok', ['custom_packages' => $customNames]);
});

// --- 添加自定义包 ---
mnbt_register_ajax('admin', 'p_auto_deploy_add_pkg', function () {
	mnbt_plugin_require_admin();
	$nodeId = isset($_POST['node_id']) ? (int)$_POST['node_id'] : 0;
	$name = isset($_POST['name']) ? trim((string)$_POST['name']) : '';
	$title = isset($_POST['title']) ? trim((string)$_POST['title']) : '';
	$version = isset($_POST['version']) ? trim((string)$_POST['version']) : '';
	$projectType = isset($_POST['project_type']) ? trim((string)$_POST['project_type']) : 'php';
	$php = isset($_POST['php']) ? trim((string)$_POST['php']) : '';
	$enableFunctions = isset($_POST['enable_functions']) ? trim((string)$_POST['enable_functions']) : '';
	$javaVersion = isset($_POST['java_version']) ? trim((string)$_POST['java_version']) : '';
	$mysqlVersion = isset($_POST['mysql_version']) ? trim((string)$_POST['mysql_version']) : '';

	if ($nodeId <= 0) json_exit_error('请选择节点');
	if ($name === '') json_exit_error('软件包英文名称不能为空');
	if ($title === '') json_exit_error('软件包标题不能为空');
	if ($version === '') json_exit_error('版本号不能为空');
	if (!preg_match('/^[a-zA-Z0-9_\-.]+$/', $name)) json_exit_error('英文名称仅允许字母、数字、下划线、横线、点');

	$r = auto_deploy_init_api($nodeId);
	if ($r['error'] !== '') json_exit_error($r['error']);

	$result = $r['api']->deployment_add_package($name, $title, $version, $php, $enableFunctions, $projectType, $javaVersion, $mysqlVersion);

	if (!$result || empty($result['status'])) {
		$errMsg = is_array($result) ? ($result['msg'] ?? '添加失败') : 'API 响应异常';
		json_exit_error($errMsg);
	}
	json_exit_success('添加成功');
});

// --- 获取自定义包详情 ---
mnbt_register_ajax('admin', 'p_auto_deploy_get_pkg', function () {
	mnbt_plugin_require_admin();
	$nodeId = isset($_POST['node_id']) ? (int)$_POST['node_id'] : 0;
	$pName = isset($_POST['p_name']) ? trim((string)$_POST['p_name']) : '';
	if ($nodeId <= 0) json_exit_error('请选择节点');
	if ($pName === '') json_exit_error('请提供包名称');

	$r = auto_deploy_init_api($nodeId);
	if ($r['error'] !== '') json_exit_error($r['error']);

	$result = $r['api']->deployment_get_package_other($pName);
	if (!$result || isset($result['status'])) {
		json_exit_error('未找到该自定义包');
	}
	json_exit_success('ok', ['package' => $result]);
});

// --- 删除已部署项目 ---
mnbt_register_ajax('admin', 'p_auto_deploy_del', function () {
	mnbt_plugin_require_admin();
	$nodeId = isset($_POST['node_id']) ? (int)$_POST['node_id'] : 0;
	$dname = isset($_POST['dname']) ? trim((string)$_POST['dname']) : '';
	$siteName = isset($_POST['site_name']) ? trim((string)$_POST['site_name']) : '';
	if ($nodeId <= 0) json_exit_error('请选择节点');
	if ($dname === '') json_exit_error('请提供部署名称');
	if ($siteName === '') json_exit_error('请提供网站名称');

	$r = auto_deploy_init_api($nodeId);
	if ($r['error'] !== '') json_exit_error($r['error']);

	$result = $r['api']->deployment_del_package($dname, $siteName);

	if (!$result || empty($result['status'])) {
		$errMsg = is_array($result) ? ($result['msg'] ?? '删除失败') : 'API 响应异常';
		json_exit_error($errMsg);
	}
	json_exit_success('删除成功');
});

// --- 本地部署历史 ---
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

// --- 仪表盘小部件 ---
mnbt_register_widget('admin', [
	'title' => '自动部署概览',
	'order' => 20,
	'class' => 'col-sm-6',
	'callback' => function () {
		global $DB;
		$totalDeploy = $DB->get_row_prepare("SELECT COUNT(*) AS cnt FROM plg_auto_deploy_log")['cnt'] ?? 0;
		$successDeploy = $DB->get_row_prepare("SELECT COUNT(*) AS cnt FROM plg_auto_deploy_log WHERE result='success'")['cnt'] ?? 0;
		$recentNodes = $DB->get_row_prepare("SELECT COUNT(*) AS cnt FROM plg_auto_deploy_log WHERE created_at >= ?", [date('Y-m-d H:i:s', strtotime('-7 days'))])['cnt'] ?? 0;
		echo '<p class="mb-1">总部署次数：<strong>' . (int)$totalDeploy . '</strong></p>';
		echo '<p class="mb-1">成功部署：<strong class="text-success">' . (int)$successDeploy . '</strong></p>';
		echo '<p class="mb-1">近 7 天部署：<strong>' . (int)$recentNodes . '</strong></p>';
		echo '<p class="mt-2 mb-0"><a class="btn btn-sm btn-outline-primary multitabs" href="plugin.php?p=auto_deploy&page=index">进入部署中心</a></p>';
	},
]);
