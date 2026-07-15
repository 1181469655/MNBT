<?php
@header('Content-Type: text/html; charset=UTF-8');
if (defined('ROOT')) {
	include ROOT . 'cf_up.php';
} else {
	include dirname(__DIR__, 3) . '/cf_up.php';
}
if (!empty($mn_conf['xf']['qk']) && (!isset($islogin) || (int)$islogin !== 0)) {
	exit('由于更新后必须进行一次系统修复，暂时无法使用这功能！修复方法：进入管理后台->点击右上角系统管理员->点击系统修复->选择要修复的功能->点击确认修复即可！');
}
?>
<!DOCTYPE html>
<html lang="zh">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" />
<title><?=$title?></title>
<link rel="icon" href="../imsetes/images/logo-ico.png" type="image/ico">
<meta name="author" content="yinqi">
<link href="../imsetes/css/bootstrap.min.css" rel="stylesheet">
<link href="../imsetes/css/materialdesignicons.min.css" rel="stylesheet">
<link rel="stylesheet" href="../imsetes/js/bootstrap-multitabs/multitabs.min.css">
<link href="../imsetes/css/animate.min.css" rel="stylesheet">
<link href="../imsetes/css/style.min.css" rel="stylesheet">
<script type="text/javascript" src="../imsetes/js/jquery.min.js"></script>

<script type="text/javascript" src="../imsetes/js/popper.min.js"></script>
<script type="text/javascript" src="../imsetes/js/bootstrap.min.js"></script>
<script type="text/javascript" src="../imsetes/js/lyear-loading.js"></script>

<!--消息提示-->
<script src="../imsetes/js/bootstrap-notify.min.js"></script>
<script type="text/javascript" src="../imsetes/js/main.min.js"></script>
<script type="text/javascript" src="../imsetes/js/fn-hs.js"></script>

<!--表格样式-->
<link href="../imsetes/js/bootstrap-table/bootstrap-table.min.css" rel="stylesheet">
<link href="../imsetes/js/jquery-confirm/jquery-confirm.min.css" rel="stylesheet">
<link href="<?=mnbt_theme_url('assets/admin-common.css', 'admin')?>" rel="stylesheet">
</head>
<body>

