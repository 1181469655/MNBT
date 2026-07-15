<?php
include("../MPHX/common.php");
mnbt_admin_require_login();
$p = isset($_GET['p']) ? preg_replace('/[^a-zA-Z0-9_-]/', '', $_GET['p']) : '';
$page = isset($_GET['page']) ? preg_replace('/[^a-zA-Z0-9_-]/', '', $_GET['page']) : '';
if ($p !== '' && $page !== '') {
	if (!mnbt_plugin_enabled($p)) {
		$title = '插件未启用';
		echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>插件</title></head><body style="font-family:sans-serif;padding:2rem">';
		echo '<p>插件未启用或不存在：' . htmlspecialchars($p, ENT_QUOTES, 'UTF-8') . '</p>';
		echo '<p><a href="plugin.php">返回插件管理</a></p></body></html>';
		exit;
	}
	$title = $p;
	$info = mnbt_plugin_find_page('admin', $p, $page);
	if ($info && !empty($info['title'])) {
		$title = $info['title'];
	}
	mnbt_plugin_render_page('admin', $p, $page);
	exit;
}
$title = '插件管理';
mnbt_admin_render('plugin_manage');
