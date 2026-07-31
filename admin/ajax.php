<?php
include("../MPHX/common.php");
@header('Content-Type: text/html; charset=UTF-8');
mnbt_csrf_validate_request();
$egn=$_POST['gn'];
	if($islogin==1 || $egn=='login') {
} else json_exit('请登陆');
if (function_exists('mnbt_plugin_dispatch_ajax') && mnbt_plugin_dispatch_ajax('admin', $egn)) {
	return;
}
require_once './api/login.php';
require_once './api/repair.php';
require_once './api/setting.php';
require_once './api/log.php';
require_once './api/bt.php';
require_once './api/node.php';
require_once './api/zj.php';
require_once './api/cx.php';
require_once './api/dd.php';
// ym.php 已删除：域名管理 AJAX 已迁移至 domain_shop 插件（p_domain_*）
require_once './api/gg.php';
require_once './api/plugin.php';
json_exit('系统指令不存在！');
?>
