<?php
// set.php 的 gn 参数映射到 SPA 路由
$gn = $_GET['gn'] ?? 'php';
$route_map = [
	'php'      => '#/settings/php',
	'pass'     => '#/settings/pass',
	'mrwd'     => '#/settings/default-doc',
	'yxml'     => '#/settings/run-dir',
	'wjt'      => '#/settings/rewrite',
	'ssl'      => '#/settings/ssl',
	'fdl'      => '#/settings/hotlink',
	'gzip'     => '#/settings/gzip',
	'cache'    => '#/settings/cache',
	'xgpass'   => '#/settings/password',
	'mysqlcz'  => '#/settings/sql-auth',
];
$td_entry = 'settings';
$td_hash = $route_map[$gn] ?? '#/settings/php';
include __DIR__ . '/_spa_boot.php';
