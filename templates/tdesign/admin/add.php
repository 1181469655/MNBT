<?php
// add.php?gn=xxx → SPA 添加页对应路由
$gn = isset($_GET['gn']) ? preg_replace('/[^a-zA-Z0-9_]/', '', (string)$_GET['gn']) : 'zj';
$map = [
	'zj' => '#/host/add',
	'bt' => '#/baota/add',
	'cx' => '#/program/add',
	'dr' => '#/program/import',
	'dknode' => '#/docker/node/add',
];
$td_entry = 'add';
$td_hash  = $map[$gn] ?? '#/host/add';
include __DIR__ . '/_spa_boot.php';
