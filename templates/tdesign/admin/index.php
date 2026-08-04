<?php
// 首页(仪表盘):服务器端准备 $sy 系统信息,注入 boot.sy
// 参考 admin/sy.php 控制器的数据准备逻辑
$td_entry = 'dashboard';
$td_hash  = '#/dashboard';
$td_inject = [];

if (!isset($sy) || !is_array($sy)) {
	// 引入版本号常量
	if (!isset($WEBQB)) {
		@include_once __DIR__ . '/../../../MPHX/BL.php';
	}
	if (!isset($SQLQB)) {
		@include_once __DIR__ . '/../../../MPHX/SQ.php';
	}

	$sy = [
		'hosts'     => (int)$DB->count_prepare("SELECT count(*) FROM MN_zj WHERE 1"),
		'bt_panels' => (int)$DB->count_prepare("SELECT count(*) FROM MN_bt WHERE 1"),
		'nodes'     => (int)$DB->count_prepare("SELECT count(*) FROM MN_node WHERE 1"),
		'orders'    => (int)$DB->count_prepare("SELECT count(*) FROM MN_dd WHERE 1"),
	];
	$sy['os']            = php_uname('s') . ' ' . php_uname('r');
	$sy['hostname']      = php_uname('n');
	$sy['php_version']   = PHP_VERSION;
	$sy['php_sapi']      = PHP_SAPI;
	$sy['server_soft']   = $_SERVER['SERVER_SOFTWARE'] ?? 'N/A';
	$sy['server_ip']     = $_SERVER['SERVER_ADDR'] ?? ($_SERVER['LOCAL_ADDR'] ?? '127.0.0.1');
	$sy['server_port']   = $_SERVER['SERVER_PORT'] ?? '80';
	$sy['server_time']   = date('Y-m-d H:i:s');
	$sy['timezone']      = date_default_timezone_get();
	$sy['memory_limit']  = ini_get('memory_limit');
	$sy['max_exec_time'] = ini_get('max_execution_time');
	$sy['upload_max']    = ini_get('upload_max_filesize');
	$sy['post_max']      = ini_get('post_max_size');
	$sy['ext_count']     = count(get_loaded_extensions());
	$sy['disk_total']    = @disk_total_space(ROOT) ?: 0;
	$sy['disk_free']     = @disk_free_space(ROOT) ?: 0;
	$sy['disk_used']     = max(0, $sy['disk_total'] - $sy['disk_free']);
	$sy['disk_pct']      = $sy['disk_total'] ? round($sy['disk_used'] / $sy['disk_total'] * 100, 1) : 0;
	$sy['disk_ok']       = $sy['disk_total'] > 0;
	$sy['mem_current']   = memory_get_usage(true);
	$sy['mem_peak']      = memory_get_peak_usage(true);
	$sy['load_avg']      = function_exists('sys_getloadavg') ? @sys_getloadavg() : null;
	$_row = $DB->get_row("SELECT VERSION() AS ver");
	$sy['db_version']    = isset($_row['ver']) ? $_row['ver'] : 'N/A';
	$sy['web_version']   = $WEBQB ?? 0;
	$sy['sql_version']   = $SQLQB ?? 0;
}

$td_inject['sy'] = $sy;
include __DIR__ . '/_spa_boot.php';
