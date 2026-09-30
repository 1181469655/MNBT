<?php
$WEBQB='1850';
$SQLQB='1310';

// 版本号唯一数据源是上面的 $WEBQB（如 1850 => V1.85）。
// 全项目对外显示版本一律走下面两个函数，发版只改 $WEBQB 这一处。
// BL.php 会被多处 include（非 once），所以用 function_exists 兜住重复定义。
if (!function_exists('mnbt_version_num')) {
	// 纯数字串：1850 => "1.85"
	function mnbt_version_num()
	{
		return sprintf('%.2f', (int)($GLOBALS['WEBQB'] ?? 0) / 1000);
	}
}
if (!function_exists('mnbt_version')) {
	// 带前缀显示：1850 => "V1.85"
	function mnbt_version($prefix = 'V')
	{
		return $prefix . mnbt_version_num();
	}
}
?>