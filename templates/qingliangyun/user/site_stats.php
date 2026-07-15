<?php
// 嵌入模式：被 SPA iframe 打开时回退 default 完整页
if (isset($_GET['_ql']) && $_GET['_ql'] === '1') {
	// 强制 default，避免递归加载 SPA
	$path = MNBT_THEME_ROOT . 'default/user/site_stats.php';
	if (is_file($path)) {
		extract($GLOBALS, EXTR_SKIP);
		include $path;
		return;
	}
}
$ql_entry = 'stats';
$ql_hash = '#/stats';
include __DIR__ . '/_spa_boot.php';
