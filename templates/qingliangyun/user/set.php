<?php
// SPA 设置页通过 iframe 加载 default 的 set 内容
if (isset($_GET['_ql']) && $_GET['_ql'] === '1') {
	$path = MNBT_THEME_ROOT . 'default/user/set.php';
	if (is_file($path)) {
		extract($GLOBALS, EXTR_SKIP);
		include $path;
		return;
	}
}
$gn = isset($_GET['gn']) ? preg_replace('/[^a-zA-Z0-9_]/', '', (string)$_GET['gn']) : 'php';
$ql_entry = 'settings';
$ql_hash = '#/settings/' . $gn;
include __DIR__ . '/_spa_boot.php';
