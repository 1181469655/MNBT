<?php
// 仪表盘:把控制器传入的 $sy 与 $conf 注入 boot,SPA 直接消费
$td_entry = 'dashboard';
$td_hash  = '#/dashboard';
$td_inject = [];
if (isset($sy) && is_array($sy)) {
	$td_inject['sy'] = $sy;
}
include __DIR__ . '/_spa_boot.php';
