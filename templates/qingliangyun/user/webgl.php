<?php
$path = MNBT_THEME_ROOT . 'default/user/webgl.php';
if (is_file($path)) {
	extract($GLOBALS, EXTR_SKIP);
	include $path;
	return;
}
echo 'webgl view missing';
