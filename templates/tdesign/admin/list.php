<?php
// list.php?gn=xxx → SPA 列表页对应路由
$gn = isset($_GET['gn']) ? preg_replace('/[^a-zA-Z0-9_]/', '', (string)$_GET['gn']) : 'zj';
$map = [
	'zj'  => '#/host',
	'bt'  => '#/baota',
	'dd'  => '#/order',
	'cx'  => '#/program',
	'log' => '#/log',
];
$td_entry = 'list';
$td_hash  = $map[$gn] ?? '#/host';
include __DIR__ . '/_spa_boot.php';
