<?php
// docker.php?gn=node|user|plan → SPA Docker 管理对应路由
$gn = isset($_GET['gn']) ? preg_replace('/[^a-zA-Z0-9_]/', '', (string)$_GET['gn']) : 'node';
$map = [
	'node' => '#/docker/node',
	'user' => '#/docker/user',
	'plan' => '#/docker/plan',
];
$td_entry = 'docker';
$td_hash  = $map[$gn] ?? '#/docker/node';
include __DIR__ . '/_spa_boot.php';
