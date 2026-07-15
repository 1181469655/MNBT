<?php
$path = MNBT_THEME_ROOT . 'default/user/monitor_log.php';
if (is_file($path)) {
	extract($GLOBALS, EXTR_SKIP);
	include $path;
	return;
}
echo 'monitor_log view missing';
