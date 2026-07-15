<?php
if (isset($_GET['_ql']) && $_GET['_ql'] === '1') {
	$path = MNBT_THEME_ROOT . 'default/user/notice.php';
	if (is_file($path)) {
		extract($GLOBALS, EXTR_SKIP);
		include $path;
		return;
	}
}
$ql_entry = 'notice';
$ql_hash = '#/notice';
include __DIR__ . '/_spa_boot.php';
