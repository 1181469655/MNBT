<?php
/**
 * balance 插件 - 公共布局 (Layui)
 */
if (!defined('IN_CRONLITE')) { exit; }
$current_user = $current_user ?? null;
$page_title = $page_title ?? '余额';
$content = $content ?? '';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($page_title) ?> - 余额管理</title>
<link rel="stylesheet" href="https://unpkg.com/layui@2.9.8/dist/css/layui.css">
<link rel="stylesheet" href="<?= balance_asset_url('style.css') ?>">
</head>
<body>

<div class="ly-nav">
  <div class="ly-nav-inner">
    <a class="ly-nav-brand" href="<?= balance_url('balance') ?>">余额管理</a>
    <div class="ly-nav-links">
      <?php if ($current_user): ?>
        <a href="<?= balance_url('balance') ?>">余额</a>
        <a href="<?= balance_url('balance/recharge') ?>">充值</a>
        <a href="<?= balance_url('account/profile') ?>">个人信息</a>
        <a href="<?= balance_url('account/logout') ?>">退出</a>
      <?php else: ?>
        <a href="<?= balance_url('account/login') ?>">登录</a>
        <a href="<?= balance_url('account/register') ?>">注册</a>
      <?php endif; ?>
    </div>
  </div>
</div>

<div class="ly-page-wide"><?= $content ?></div>

</body>
</html>
