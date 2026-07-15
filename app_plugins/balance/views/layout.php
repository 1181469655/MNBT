<?php
/**
 * balance 插件 - 公共布局
 */
if (!defined('IN_CRONLITE')) {
	exit;
}
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
	<link rel="stylesheet" href="<?= balance_asset_url('style.css') ?>">
</head>

<body>
	<nav class="bal-nav">
		<div class="bal-nav-inner">
			<a class="bal-nav-logo" href="<?= balance_url('balance') ?>">余额管理</a>
			<div class="bal-nav-links">
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
	</nav>

	<main class="bal-main">
		<?= $content ?>
	</main>
</body>

</html>
