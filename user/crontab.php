<?php
include("../MPHX/common.php");
@header('Content-Type: text/html; charset=UTF-8');
$title = '计划任务';
mnbt_user_require_login();
include_once("../MPHX/crontab.function.php");
crontab_ensure_tables($DB);
mnbt_render('crontab');
