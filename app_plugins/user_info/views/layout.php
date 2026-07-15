<?php
/**
 * 公共布局 - 完整 HTML 骨架 + 导航 + 内容区
 *
 * 页面文件设置 $content / $page_title 后 include 本文件。
 */
if (!defined('IN_CRONLITE')) {
	exit;
}
$current_user = $current_user ?? null;
$page_title = $page_title ?? '用户中心';
$content = $content ?? '';
$asset = $asset_url ?? '';
?>
<!DOCTYPE html>
<html lang="zh-CN">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?= htmlspecialchars($page_title) ?> - 用户中心</title>
	<link rel="stylesheet" href="<?= user_info_asset_url('style.css') ?>">
</head>

<body>
	<nav class="account-nav">
		<div class="account-nav-inner">
			<a class="account-nav-logo" href="<?= user_info_url('account/profile') ?>">用户中心</a>
			<div class="account-nav-links">
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
	</nav>

	<main class="account-main">
		<div class="account-card">
			<?= $content ?>
		</div>
	</main>
</body>

</html>
