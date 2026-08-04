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
<script>
// ===== Docker 控制台公共 JS（提前到 head，确保视图 script 可用）=====
function dkToast(msg, type){
	type = type || 'info';
	var wrap = document.getElementById('dkToastWrap');
	var el = document.createElement('div');
	el.className = 'dk-toast ' + type;
	el.textContent = msg;
	wrap.appendChild(el);
	setTimeout(function(){ el.style.opacity='0'; el.style.transition='opacity .3s'; setTimeout(function(){ el.remove(); }, 300); }, 2600);
}
function dkAjax(gn, data, opts){
	opts = opts || {};
	data = data || {};
	if (typeof data === 'object' && !(data instanceof FormData)) {
		var fd = new FormData();
		for (var k in data) fd.append(k, data[k]);
		data = fd;
	}
	data.append('_csrf', window.MNBT_CSRF);
	return $.ajax({
		url: 'ajax.php?gn=' + encodeURIComponent(gn),
		type: 'POST', data: data, processData: false, contentType: false, dataType: 'json',
		timeout: opts.timeout || 60000
	}).then(function(r){
		if (r && r.success) return r;
		var msg = (r && r.msg) ? r.msg : '请求失败';
		if (!opts.silent) dkToast(msg, 'error');
		return $.Deferred().reject(r);
	}, function(xhr){
		if (!opts.silent) dkToast('网络错误：' + (xhr.statusText || ''), 'error');
		return $.Deferred().reject(xhr);
	});
}
function dockerLogout(){
	dkAjax('logout', {}).then(function(){ window.location.href = 'login.php'; });
}
function dkModal(html, title){
	$('#dkModalMask').html(
		'<div class="dk-modal"><div class="dk-modal-head"><h3>'+ (title||'') +'</h3><button class="dk-modal-close" onclick="dkCloseModal()">&times;</button></div><div class="dk-modal-body">'+ html +'</div></div>'
	).addClass('show');
}
function dkCloseModal(){ $('#dkModalMask').removeClass('show').empty(); }
$(document).on('click', '.dk-modal-mask', function(e){ if (e.target === this) dkCloseModal(); });
</script>
</head>
<body class="dk-body">
<div class="dk-app">
	<aside class="dk-sidebar">
		<div class="dk-sidebar-brand">
			<img src="<?= mnbt_theme_asset('assets/docker.svg', 'docker') ?>" alt="Docker 控制台" class="dk-logo">
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
