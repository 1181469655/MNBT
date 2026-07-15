<?php
// iframe 内嵌业务页仍走 default 公共头，保证样式与脚本完整
$path = MNBT_THEME_ROOT . 'default/user/head.php';
if (is_file($path)) {
	extract($GLOBALS, EXTR_SKIP);
	include $path;
	return;
}
?>
<!DOCTYPE html>
<html lang="zh"><head><meta charset="utf-8"><title><?= htmlspecialchars($title ?? 'MNBT', ENT_QUOTES, 'UTF-8') ?></title></head><body>
