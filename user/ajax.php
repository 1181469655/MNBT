<?php
/*
 *本文件为控制面板功能性操作文件
 *©梦奈
*/
include("../MPHX/common.php");
@header('Content-Type: text/html; charset=UTF-8');
$egn=$_POST['gn'] ?? '';
if($islogins==1 || $egn=='login') {
} else exit('{"code":"请登陆"}');
	if($islogins==1) {
	$cert=$DB->get_row_prepare("SELECT * FROM MN_bt WHERE btdh=? limit 1", [$ssbt]);
	if(!$cert)exit('{"code":"宝塔服务器配置错误，请联系管理员"}');
	$btipe=($cert['ptl']=='true'?'https':'http').'://'.$cert['btip'].':'.$cert['btdk'];
	$btkeye=$cert['btmy'];
	if($cert['btos']=='1') {
		$os_xt=$conf['hxi'].'/';
		$l_ler_a='/etc/hosts';
	} else {
		$os_xt=$conf['hxo'].'/';
		$l_ler_a='C:\Windows\System32\drivers\etc\hosts';
	}
}
if (($islogins==1 || $egn=='login') && function_exists('mnbt_plugin_dispatch_ajax') && mnbt_plugin_dispatch_ajax('user', $egn)) {
	return;
}
include("api/login.php");
if($yhc['hxc']=='1') {
	include("api/cdn.php");
	exit('{"code":"CDN产品无法进行此操作"}');
}
include("api/domain.php");
include("api/file.php");
include("api/cache.php");
include("api/site.php");
include("api/ssl.php");
include("api/monitor.php");
include("api/deploy.php");
include("api/database.php");
include("api/site_stats.php");
include("api/other.php");
include("api/panel.php");
exit('{"code":"请求错误！"}');
