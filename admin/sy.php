<?php
include("../MPHX/common.php");
include("../MPHX/BL.php");
include("../MPHX/SQ.php");
$title = 'MN宝塔主机首页目录';
mnbt_admin_require_login();

function _sy_fmt_bytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= (1 << (10 * $pow));
    return round($bytes, $precision) . ' ' . $units[$pow];
}

$sy = [
    'hosts'   => (int)$DB->count_prepare("SELECT count(*) FROM MN_zj WHERE 1"),
    'bt_panels' => (int)$DB->count_prepare("SELECT count(*) FROM MN_bt WHERE 1"),
    'nodes'   => (int)$DB->count_prepare("SELECT count(*) FROM MN_node WHERE 1"),
    'orders'  => (int)$DB->count_prepare("SELECT count(*) FROM MN_dd WHERE 1"),
];

$sy['os']             = php_uname('s') . ' ' . php_uname('r');
$sy['hostname']       = php_uname('n');
$sy['php_version']    = PHP_VERSION;
$sy['php_sapi']       = PHP_SAPI;
$sy['server_soft']    = $_SERVER['SERVER_SOFTWARE'] ?? 'N/A';
$sy['server_ip']      = $_SERVER['SERVER_ADDR'] ?? ($_SERVER['LOCAL_ADDR'] ?? '127.0.0.1');
$sy['server_port']    = $_SERVER['SERVER_PORT'] ?? '80';
$sy['server_time']    = date('Y-m-d H:i:s');
$sy['timezone']       = date_default_timezone_get();
$sy['memory_limit']   = ini_get('memory_limit');
$sy['max_exec_time']  = ini_get('max_execution_time');
$sy['upload_max']     = ini_get('upload_max_filesize');
$sy['post_max']       = ini_get('post_max_size');
$sy['ext_count']      = count(get_loaded_extensions());
$sy['disk_total']     = @disk_total_space(ROOT) ?: 0;
$sy['disk_free']      = @disk_free_space(ROOT) ?: 0;
$sy['disk_used']      = max(0, $sy['disk_total'] - $sy['disk_free']);
$sy['disk_pct']       = $sy['disk_total'] ? round($sy['disk_used'] / $sy['disk_total'] * 100, 1) : 0;
$sy['disk_ok']        = $sy['disk_total'] > 0;
$sy['mem_current']    = memory_get_usage(true);
$sy['mem_peak']       = memory_get_peak_usage(true);
$sy['load_avg']       = function_exists('sys_getloadavg') ? @sys_getloadavg() : null;
$row = $DB->get_row("SELECT VERSION() AS ver");
$sy['db_version'] = isset($row['ver']) ? $row['ver'] : 'N/A';
$sy['web_version']    = $WEBQB;
$sy['sql_version']    = $SQLQB;

mnbt_admin_render('sy', ['sy' => $sy]);
