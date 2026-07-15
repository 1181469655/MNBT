<?php
/**
 * hosting_shop 插件用户端 - 公共布局
 */
if (!defined('IN_CRONLITE')) {
	exit;
}
$current_user = $current_user ?? null;
$page_title = $page_title ?? '主机售卖';
$content = $content ?? '';
?>
<!DOCTYPE html>
<html lang="zh-CN">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?= htmlspecialchars($page_title) ?> - 主机售卖</title>
	<link rel="stylesheet" href="<?= hosting_asset_url('style.css') ?>">
</head>

<body>
	<nav class="hs-nav">
		<div class="hs-nav-inner">
			<a class="hs-nav-logo" href="<?= hosting_url('shop') ?>">主机售卖</a>
			<div class="hs-nav-links">
				<?php if ($current_user): ?>
					<a href="<?= hosting_url('shop') ?>">套餐</a>
					<a href="<?= hosting_url('shop/assets') ?>">我的主机</a>
					<a href="<?= hosting_url('shop/orders') ?>">订单</a>
					<a href="<?= hosting_url('balance') ?>">余额</a>
					<a href="<?= hosting_url('account/profile') ?>">个人信息</a>
					<a href="<?= hosting_url('account/logout') ?>">退出</a>
				<?php else: ?>
					<a href="<?= hosting_url('account/login') ?>">登录</a>
					<a href="<?= hosting_url('account/register') ?>">注册</a>
				<?php endif; ?>
			</div>
		</div>
	</nav>

	<main class="hs-main">
		<?= $content ?>
	</main>
</body>

</html>
