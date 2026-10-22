<?php
// 首页(仪表盘):服务器端准备 $sy 系统信息,注入 boot.sy
// 字段组装统一走 MPHX/function.php 的 mnbt_sys_info()，与 admin/sy.php 控制器、
// AJAX 的 system_info 同源，不再各写一份
$td_entry = 'dashboard';
$td_hash  = '#/dashboard';
$td_inject = [];

if (!isset($sy) || !is_array($sy)) {
	$sy = mnbt_sys_info();
}

// php2shell（CVE-2026-109618 / CVE-2026-109619）运行期风险提示：只提示，不拦截后台
// 分支表、EOL 判定与文案的唯一来源是 MPHX/BL.php 的 mnbt_php_security_status()（与安装向导同源）。
// $sy 也可能由控制器预先传入（不含 php_security），所以这里无条件按同源结果覆盖一次。
@include_once __DIR__ . '/../../../MPHX/BL.php';
$phpsec = mnbt_php_security_status();
$sy['php_security'] = ['status' => $phpsec['status'], 'text' => $phpsec['text']];

$td_inject['sy'] = $sy;
include __DIR__ . '/_spa_boot.php';
