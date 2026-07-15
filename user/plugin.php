<?php
include("../MPHX/common.php");
mnbt_user_require_login();
$p = isset($_GET['p']) ? preg_replace('/[^a-zA-Z0-9_-]/', '', $_GET['p']) : '';
$page = isset($_GET['page']) ? preg_replace('/[^a-zA-Z0-9_-]/', '', $_GET['page']) : 'index';
if ($p === '' || !mnbt_plugin_enabled($p)) {
	exit('插件不存在或未启用');
}
if (!mnbt_plugin_find_page('user', $p, $page)) {
	exit('插件页面不存在');
}
mnbt_plugin_render_page('user', $p, $page);
