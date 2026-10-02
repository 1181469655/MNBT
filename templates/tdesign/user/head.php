<?php
@header('Content-Type: text/html; charset=UTF-8');
if (defined('ROOT')) {
	include ROOT . 'cf_up.php';
} else {
	include dirname(__DIR__, 3) . '/cf_up.php';
}
if (!empty($mn_conf['xf']['qk'])) {
	exit('由于更新后必须进行一次系统修复，暂时无法使用本系统！请联系管理员前往后台使用修复功能！');
}
?>
<!DOCTYPE html>
<html lang="zh">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" />
<title><?=$title ?? 'MNBT'?></title>
<link rel="icon" href="<?=mnbt_asset_url('upload_logo/logo.head.png')?>?<?=$conf['auther'] ?? ''?>" type="image/ico">
<meta name="author" content="yinqi">
<link href="<?=mnbt_asset_url('css/bootstrap.min.css')?>" rel="stylesheet">
<link href="<?=mnbt_asset_url('css/materialdesignicons.min.css')?>" rel="stylesheet">
<link rel="stylesheet" href="<?=mnbt_asset_url('js/bootstrap-multitabs/multitabs.min.css')?>">
<link href="<?=mnbt_asset_url('css/animate.min.css')?>" rel="stylesheet">
<link href="<?=mnbt_asset_url('css/style.min.css')?>" rel="stylesheet">
<script type="text/javascript" src="<?=mnbt_asset_url('js/jquery.min.js')?>"></script>
<script type="text/javascript" src="<?=mnbt_asset_url('js/popper.min.js')?>"></script>
<script type="text/javascript" src="<?=mnbt_asset_url('js/bootstrap.min.js')?>"></script>
<script type="text/javascript" src="<?=mnbt_asset_url('js/lyear-loading.js')?>"></script>
<script src="<?=mnbt_asset_url('js/bootstrap-notify.min.js')?>"></script>
<script type="text/javascript" src="<?=mnbt_asset_url('js/main.min.js')?>"></script>
<script type="text/javascript" src="<?=mnbt_asset_url('js/fn-hs.js')?>?1.74"></script>
<link href="<?=mnbt_asset_url('js/bootstrap-table/bootstrap-table.min.css')?>" rel="stylesheet">
<link href="<?=mnbt_asset_url('js/jquery-confirm/jquery-confirm.min.css')?>" rel="stylesheet">
<link href="<?=mnbt_theme_asset('user-common.css')?>" rel="stylesheet">
</head>
<body>
