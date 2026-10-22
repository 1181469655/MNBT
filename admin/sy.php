<?php
include("../MPHX/common.php");
include("../MPHX/BL.php");
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

// 系统信息字段与仪表盘启动注入、AJAX system_info 同源（MPHX/function.php），
// 其中 php_security（php2shell 运行期风险提示）也来自同一处，不再各写一份
$sy = mnbt_sys_info();

mnbt_admin_render('sy', ['sy' => $sy]);
