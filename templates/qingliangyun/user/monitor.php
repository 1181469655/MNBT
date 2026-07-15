<?php
if (isset($_GET['_ql']) && $_GET['_ql'] === '1') {
	$path = MNBT_THEME_ROOT . 'default/user/monitor.php';
	if (is_file($path)) {
		extract($GLOBALS, EXTR_SKIP);
		include $path;
		return;
	}
}
$ql_entry = 'monitor';
$ql_hash = '#/monitor';
include __DIR__ . '/_spa_boot.php';
