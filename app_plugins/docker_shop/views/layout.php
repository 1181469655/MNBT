<?php
/**
 * docker_shop 插件用户端 - 公共布局 (Layui)
 */
if (!defined('IN_CRONLITE')) { exit; }
$current_user = $current_user ?? null;
$page_title = $page_title ?? 'Docker 商城';
$content = $content ?? '';
$base = function_exists('mnbt_home_base') ? mnbt_home_base() : '';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($page_title) ?> - Docker 售卖</title>
<link rel="stylesheet" href="https://unpkg.com/layui@2.9.8/dist/css/layui.css">
<link rel="stylesheet" href="<?= htmlspecialchars(mnbt_plugin_url('docker_shop', 'assets/style.css'), ENT_QUOTES) ?>">
</head>
<body>

<div class="ly-nav hs-navbar">
  <div class="ly-nav-inner">
    <a class="ly-nav-brand" href="<?= htmlspecialchars(docker_shop_url('docker-shop'), ENT_QUOTES) ?>">Docker 售卖</a>
    <div class="ly-nav-links">
      <?php if ($current_user): ?>
        <a href="<?= htmlspecialchars(docker_shop_url('docker-shop'), ENT_QUOTES) ?>">套餐</a>
        <a href="<?= htmlspecialchars(docker_shop_url('docker-shop/assets'), ENT_QUOTES) ?>">我的 Docker</a>
        <a href="<?= htmlspecialchars(docker_shop_url('docker-shop/orders'), ENT_QUOTES) ?>">订单</a>
        <a href="<?= htmlspecialchars(docker_shop_url('balance'), ENT_QUOTES) ?>">余额</a>
        <a href="<?= htmlspecialchars(docker_shop_url('account/profile'), ENT_QUOTES) ?>">个人信息</a>
        <a href="<?= htmlspecialchars($base . '/docker/login.php', ENT_QUOTES) ?>" target="_blank">Docker 控制台</a>
        <a href="<?= htmlspecialchars(docker_shop_url('account/logout'), ENT_QUOTES) ?>">退出</a>
      <?php else: ?>
        <a href="<?= htmlspecialchars(docker_shop_url('account/login'), ENT_QUOTES) ?>">登录</a>
        <a href="<?= htmlspecialchars(docker_shop_url('account/register'), ENT_QUOTES) ?>">注册</a>
      <?php endif; ?>
    </div>
  </div>
</div>

<div class="ly-page-wide"><?= $content ?></div>

<script src="https://unpkg.com/layui@2.9.8/dist/layui.js"></script>
</body>
</html>
