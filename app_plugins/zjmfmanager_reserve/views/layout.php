<?php
/**
 * zjmfmanager_reserve 用户端 - 公共布局
 */
if (!defined('IN_CRONLITE')) {
	exit;
}
$current_user = $current_user ?? null;
$page_title = $page_title ?? '魔方财务分销';
$content = $content ?? '';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($page_title) ?> - 魔方财务分销</title>
<link rel="stylesheet" href="https://unpkg.com/layui@2.9.8/dist/css/layui.css">
<link rel="stylesheet" href="<?= zjmf_asset_url('style.css') ?>">
</head>
<body>

<div class="zj-nav">
  <div class="zj-nav-inner">
    <a class="zj-nav-brand" href="<?= zjmf_url('reserve/shop') ?>">魔方财务分销</a>
    <div class="zj-nav-links">
      <?php if ($current_user): ?>
        <a href="<?= zjmf_url('reserve/shop') ?>">商品</a>
        <a href="<?= zjmf_url('reserve/hosts') ?>">我的主机</a>
        <a href="<?= zjmf_url('reserve/orders') ?>">订单</a>
        <a href="<?= zjmf_url('balance') ?>">余额</a>
        <a href="<?= zjmf_url('account/profile') ?>">个人信息</a>
        <a href="<?= zjmf_url('account/logout') ?>">退出</a>
      <?php else: ?>
        <a href="<?= zjmf_url('account/login') ?>">登录</a>
        <a href="<?= zjmf_url('account/register') ?>">注册</a>
      <?php endif; ?>
    </div>
  </div>
</div>

<div class="zj-page"><?= $content ?></div>

<script src="https://unpkg.com/layui@2.9.8/dist/layui.js"></script>
</body>
</html>
