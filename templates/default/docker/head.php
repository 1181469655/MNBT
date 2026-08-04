<?php
/** Docker 布局头部（仅认证后页面使用） */
$active = $active ?? '';
$me = $me ?? null;
$plan = $plan ?? null;
$node = $node ?? null;
$navItems = [
	'console'  => ['我的容器', 'console.php', '▣'],
	'appstore' => ['应用商店', 'appstore.php', '▦'],
	'image'    => ['本地镜像', 'image.php', '▤'],
	'volume'   => ['存储卷', 'volume.php', '▥'],
	'compose'  => ['Compose', 'compose.php', '⊟'],
];
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= htmlspecialchars($title ?? 'Docker 控制台') ?></title>
<link rel="icon" href="<?= mnbt_asset_url('images/logo-ico.png') ?>" type="image/ico">
<link href="<?= mnbt_asset_url('css/bootstrap.min.css') ?>" rel="stylesheet">
<link href="<?= mnbt_theme_asset('assets/docker.css', 'docker') ?>" rel="stylesheet">
<script src="<?= mnbt_asset_url('js/jquery.min.js') ?>"></script>
<script>window.MNBT_CSRF = <?= json_encode(mnbt_csrf_token()) ?>;</script>
</head>
<body class="dk-body">
<div class="dk-app">
	<aside class="dk-sidebar">
		<div class="dk-sidebar-brand">
			<span class="dk-logo-sm">D</span> Docker 控制台
		</div>
		<nav class="dk-nav">
			<div class="dk-nav-section">容器服务</div>
			<?php foreach ($navItems as $key => $item): ?>
				<a href="<?= $item[1] ?>" class="<?= $active === $key ? 'active' : '' ?>"><span class="dk-ico"><?= $item[2] ?></span><?= $item[0] ?></a>
			<?php endforeach; ?>
		</nav>
		<div class="dk-sidebar-user">
			<div class="dk-u-name"><?= htmlspecialchars($me['username'] ?? '') ?></div>
			<div class="dk-u-meta">节点 <?= htmlspecialchars($node['name'] ?? ($me['ssbt'] ?? '')) ?><?php if ($plan): ?> · <?= htmlspecialchars($plan['name']) ?><?php endif; ?></div>
			<div style="margin-top:10px"><button class="dk-btn dk-btn-ghost dk-btn-sm dk-btn-block" onclick="dockerLogout()">退出登录</button></div>
		</div>
	</aside>
	<main class="dk-main">
		<div class="dk-topbar">
			<h2><?= htmlspecialchars($title ?? '') ?></h2>
			<div class="dk-badges">
				<?php if (!empty($plan)): ?>
					<span class="dk-tag dk-tag-none"><?= htmlspecialchars($plan['name']) ?> · <?= htmlspecialchars($plan['cpu_max']) ?>核 / <?= htmlspecialchars($plan['mem_max']) ?>MB</span>
				<?php endif; ?>
			</div>
		</div>
		<div class="dk-content">
