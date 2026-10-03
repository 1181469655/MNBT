<?php
include("./MPHX/common.php");
@header('Content-Type: text/html; charset=UTF-8');

// 1) 通用路由分发：让插件可接管任意路径（如 /landing、/promo/{id}）
if (function_exists('mnbt_plugin_dispatch_route') && mnbt_plugin_dispatch_route()) {
	exit;
}

// 2) 首页接管：当请求路径为 / 时，让插件有机会渲染自定义首页或重定向
//    （V1.88 起默认主页由 official_site 插件承接，无插件接管时跳转用户面板）
if (function_exists('mnbt_plugin_dispatch_home') && mnbt_plugin_dispatch_home()) {
	exit;
}

// 3) 默认行为：跳转到用户面板
header("Location:user");
exit;
?>