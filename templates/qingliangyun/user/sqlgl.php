<?php
$path = MNBT_THEME_ROOT . 'default/user/sqlgl.php';
if (is_file($path)) {
	extract($GLOBALS, EXTR_SKIP);
	include $path;
	return;
}
echo 'sqlgl view missing';
