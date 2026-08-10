<?php
/**
 * realname 插件用户端 - 公共布局（自包含样式，不依赖外部 CDN）
 */
if (!defined('IN_CRONLITE')) { exit; }
$current_user = $current_user ?? null;
$page_title = $page_title ?? '实名认证';
$content = $content ?? '';
$csrf_field = function_exists('mnbt_csrf_field') ? mnbt_csrf_field() : '';
$csrf_token = function_exists('mnbt_csrf_token') ? mnbt_csrf_token() : '';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($page_title) ?> - 实名认证</title>
<link rel="stylesheet" href="<?= realname_asset_url('realname.css') ?>">
</head>
<body>
<div class="rn-nav">
  <div class="rn-nav-inner">
    <a class="rn-brand" href="<?= realname_url('realname/apply') ?>">实名认证</a>
    <div class="rn-nav-links">
      <?php if ($current_user): ?>
        <a href="<?= realname_url('realname/apply') ?>">实名认证</a>
        <a href="<?= realname_url('realname/status') ?>">认证状态</a>
        <a href="<?= (function_exists('user_info_url') ? user_info_url('account') : '#') ?>">控制面板</a>
        <a href="<?= (function_exists('user_info_url') ? user_info_url('account/logout') : '#') ?>">退出</a>
      <?php else: ?>
        <a href="<?= (function_exists('user_info_url') ? user_info_url('account/login') : '#') ?>">登录</a>
        <a href="<?= (function_exists('user_info_url') ? user_info_url('account/register') : '#') ?>">注册</a>
      <?php endif; ?>
    </div>
  </div>
</div>

<div class="rn-page">
  <input type="hidden" id="rnCsrf" value="<?= htmlspecialchars($csrf_token) ?>">
  <?= $content ?>
</div>

</body>
</html>
