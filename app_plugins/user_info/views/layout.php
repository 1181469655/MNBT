<?php
/**
 * 公共布局 - Layui 风格
 */
if (!defined('IN_CRONLITE')) { exit; }
$current_user = $current_user ?? null;
$page_title = $page_title ?? '用户中心';
$content = $content ?? '';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($page_title) ?> - 用户中心</title>
<link rel="stylesheet" href="https://unpkg.com/layui@2.9.8/dist/css/layui.css">
<link rel="stylesheet" href="<?= user_info_asset_url('style.css') ?>">
</head>
<body>

<div class="ly-nav">
  <div class="ly-nav-inner">
    <a class="ly-nav-brand" href="<?= user_info_url('account/profile') ?>">用户中心</a>
    <div class="ly-nav-links">
      <?php if ($current_user): ?>
        <a href="<?= user_info_url('account/profile') ?>">个人信息</a>
        <a href="<?= user_info_url('account/password') ?>">修改密码</a>
        <a href="<?= user_info_url('account/logout') ?>">退出</a>
      <?php else: ?>
        <a href="<?= user_info_url('account/login') ?>">登录</a>
        <a href="<?= user_info_url('account/register') ?>">注册</a>
      <?php endif; ?>
    </div>
  </div>
</div>

<div class="ly-page">
  <div class="layui-card">
    <div class="layui-card-body" style="padding:30px;"><?= $content ?></div>
  </div>
</div>

</body>
</html>
