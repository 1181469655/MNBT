<?php
include("../MPHX/common.php");
@header('Content-Type: text/html; charset=UTF-8');
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
require_once './api/ym.php';
require_once './api/gg.php';
require_once './api/plugin.php';
json_exit('系统指令不存在！');
?>
