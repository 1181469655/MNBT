<?php
/** Docker 登录页（独立布局，无侧栏） */
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= htmlspecialchars($title ?? 'Docker 控制台登录') ?></title>
<link rel="icon" href="<?= mnbt_asset_url('images/logo-ico.png') ?>" type="image/ico">
<link href="<?= mnbt_theme_asset('assets/docker.css', 'docker') ?>" rel="stylesheet">
<script src="<?= mnbt_asset_url('js/jquery.min.js') ?>"></script>
<script>window.MNBT_CSRF = <?= json_encode(mnbt_csrf_token()) ?>;</script>
</head>
<body class="dk-body">
<div class="dk-login-wrap">
	<div class="dk-login-card">
		<div class="dk-brand">
			<img src="<?= mnbt_theme_asset('assets/docker.svg', 'docker') ?>" alt="Docker 控制台" class="dk-login-logo">
			<p class="dk-sub">登录以管理您的容器服务</p>
		</div>
		<form id="dkLoginForm" autocomplete="off">
			<div class="dk-field">
				<label>账号</label>
				<input class="dk-input" type="text" name="username" required placeholder="请输入 Docker 账号">
			</div>
			<div class="dk-field">
				<label>密码</label>
				<input class="dk-input" type="password" name="password" required placeholder="请输入密码">
			</div>
			<button type="submit" class="dk-btn dk-btn-block" id="dkLoginBtn">登 录</button>
		</form>
	</div>
</div>
<div class="dk-toast-wrap" id="dkToastWrap"></div>
<script>
function dkToast(msg, type){
	type = type || 'info';
	var wrap = document.getElementById('dkToastWrap');
	var el = document.createElement('div');
	el.className = 'dk-toast ' + type; el.textContent = msg;
	wrap.appendChild(el);
	setTimeout(function(){ el.style.transition='opacity .3s'; el.style.opacity='0'; setTimeout(function(){ el.remove(); }, 300); }, 2600);
}
$('#dkLoginForm').on('submit', function(e){
	e.preventDefault();
	var btn = $('#dkLoginBtn');
	btn.prop('disabled', true).html('<span class="dk-spin"></span> 登录中...');
	var fd = new FormData(this);
	fd.append('_csrf', window.MNBT_CSRF);
	$.ajax({ url: 'ajax.php?gn=login', type: 'POST', data: fd, processData: false, contentType: false, dataType: 'json' })
	 .then(function(r){
		if (r.success) { dkToast('登录成功', 'success'); setTimeout(function(){ window.location.href = 'console.php'; }, 500); }
		else { dkToast(r.msg || '登录失败', 'error'); btn.prop('disabled', false).text('登 录'); }
	 }, function(){ dkToast('网络错误', 'error'); btn.prop('disabled', false).text('登 录'); });
});
</script>
</body>
</html>
