<?php
// set.php?gn=xxx → SPA 设置页对应路由
$gn = isset($_GET['gn']) ? preg_replace('/[^a-zA-Z0-9_]/', '', (string)$_GET['gn']) : 'wz';
$map = [
	'wz'   => '#/settings/website',
	'gl'   => '#/settings/admin',
	'api'  => '#/settings/api',
	'mail' => '#/settings/mail',
	'kzmb' => '#/settings/panel',
	'jk'   => '#/settings/monitor',
	'theme'=> '#/settings/theme',
	'yzf'  => '#/pay',
];
$td_entry = 'settings';
$td_hash  = $map[$gn] ?? '#/settings/website';
include __DIR__ . '/_spa_boot.php';
