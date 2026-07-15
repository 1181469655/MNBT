<?php
// 文件管理体积大，直接使用 default 视图（仍在清凉云主题激活时被外壳 iframe 引用）
$path = MNBT_THEME_ROOT . 'default/user/ftp.php';
if (is_file($path)) {
	extract($GLOBALS, EXTR_SKIP);
	include $path;
	return;
}
echo 'ftp view missing';
