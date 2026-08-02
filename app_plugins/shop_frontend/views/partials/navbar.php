<?php
/**
 * 共用顶栏导航（所有前端页面共用：首页落地页 + user_info/balance/hosting_shop 各功能页）。
 * 依赖 shop_frontend_url() / shop_frontend_brand_mark()（lib/shop_frontend.php）。
 * 样式来自 assets/style.css（.sf-topbar 系列），页面需先引入该样式表。
 */
if (!defined('IN_CRONLITE')) { exit; }
$current_user = $current_user ?? null;
$active = $active ?? '';
$brand = $brand ?? 'MNBT 云服务';
$brand_logo = $brand_logo ?? '';
$has_balance = $has_balance ?? false;
$has_hosting = $has_hosting ?? false;
?>
<header class="sf-topbar">
  <div class="sf-container sf-topbar-inner">
    <a class="sf-brand" href="<?= shop_frontend_url('') ?>">
      <?php if ($brand_logo): ?>
        <img class="sf-brand-img" src="<?= htmlspecialchars($brand_logo) ?>" alt="<?= htmlspecialchars($brand) ?>">
      <?php else: ?>
        <span class="sf-brand-mark"><?= htmlspecialchars(shop_frontend_brand_mark($brand)) ?></span>
      <?php endif; ?>
      <span class="sf-brand-text"><?= htmlspecialchars($brand) ?></span>
    </a>
    <nav class="sf-nav">
      <a href="<?= shop_frontend_url('') ?>" class="<?= $active === 'home' ? 'active' : '' ?>">首页</a>
      <?php if ($has_hosting): ?>
        <a href="<?= shop_frontend_url('shop') ?>" class="<?= $active === 'shop' ? 'active' : '' ?>">主机套餐</a>
        <?php if ($current_user): ?>
          <a href="<?= shop_frontend_url('shop/assets') ?>" class="<?= $active === 'assets' ? 'active' : '' ?>">我的主机</a>
          <a href="<?= shop_frontend_url('shop/orders') ?>" class="<?= $active === 'orders' ? 'active' : '' ?>">我的订单</a>
        <?php endif; ?>
      <?php endif; ?>
      <?php if ($has_balance && $current_user): ?>
        <a href="<?= shop_frontend_url('balance') ?>" class="<?= $active === 'balance' ? 'active' : '' ?>">我的余额</a>
      <?php endif; ?>
    </nav>
    <div class="sf-nav-right">
      <?php if ($current_user): ?>
        <a href="<?= shop_frontend_url('account/profile') ?>" class="<?= $active === 'profile' ? 'active' : '' ?>"><?= htmlspecialchars($current_user['username']) ?></a>
        <a href="<?= shop_frontend_url('account/logout') ?>" class="sf-btn sf-btn-ghost sf-btn-sm">退出</a>
      <?php else: ?>
        <a href="<?= shop_frontend_url('account/login') ?>" class="<?= $active === 'login' ? 'active' : '' ?>">登录</a>
        <a href="<?= shop_frontend_url('account/register') ?>" class="sf-btn sf-btn-primary sf-btn-sm">注册</a>
      <?php endif; ?>
    </div>
  </div>
</header>
