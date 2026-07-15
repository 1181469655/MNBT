<?php
if (!defined('IN_CRONLITE')) {
	exit;
}
$page_title = $page_title ?? '注册';
ob_start();
?>
<div class="account-msg" id="msg"></div>

<form class="account-form" id="registerForm" autocomplete="on">
	<h1>注册</h1>
	<p class="account-form-sub">创建您的账户</p>

	<div class="account-field">
		<label for="username">用户名 <span style="color:#999;">（3~32 位字母/数字/下划线）</span></label>
		<input type="text" id="username" name="username" required maxlength="32" autocomplete="username" placeholder="设置用户名">
	</div>

	<div class="account-field">
		<label for="password">密码 <span style="color:#999;">（至少 6 个字符）</span></label>
		<input type="password" id="password" name="password" required autocomplete="new-password" placeholder="设置密码">
	</div>

	<div class="account-field">
		<label for="password2">确认密码</label>
		<input type="password" id="password2" name="password2" required autocomplete="new-password" placeholder="再次输入密码">
	</div>

	<div class="account-field">
		<label for="email">邮箱 <span style="color:#999;">（选填）</span></label>
		<input type="email" id="email" name="email" maxlength="128" autocomplete="email" placeholder="用于找回密码">
	</div>

	<div class="account-field">
		<label for="qq">QQ <span style="color:#999;">（选填）</span></label>
		<input type="text" id="qq" name="qq" maxlength="12" placeholder="QQ 号">
	</div>

	<button type="submit" class="account-btn" id="submitBtn">注册</button>

	<div class="account-form-footer">
		已有账号？<a href="<?= user_info_url('account/login') ?>">立即登录</a>
	</div>
</form>

<script>
(function () {
	var form = document.getElementById('registerForm');
	var msg = document.getElementById('msg');
	var btn = document.getElementById('submitBtn');

	function showMsg(text, type) {
		msg.textContent = text;
		msg.className = 'account-msg show ' + (type === 'success' ? 'account-msg-success' : 'account-msg-error');
	}

	form.addEventListener('submit', function (e) {
		e.preventDefault();
		btn.disabled = true;
		btn.textContent = '注册中...';
		msg.className = 'account-msg';

		var body = new URLSearchParams();
		body.append('username', document.getElementById('username').value);
		body.append('password', document.getElementById('password').value);
		body.append('password2', document.getElementById('password2').value);
		body.append('email', document.getElementById('email').value);
		body.append('qq', document.getElementById('qq').value);

		fetch('<?= user_info_url('account/api/register') ?>', {
			method: 'POST',
			headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
			body: body.toString()
		})
			.then(function (r) { return r.json(); })
			.then(function (res) {
				if (res.redirect) {
					showMsg(res.code || '注册成功', 'success');
					setTimeout(function () { window.location.href = res.redirect; }, 300);
				} else {
					showMsg(res.code || '注册失败', 'error');
					btn.disabled = false;
					btn.textContent = '注册';
				}
			})
			.catch(function () {
				showMsg('网络错误，请重试', 'error');
				btn.disabled = false;
				btn.textContent = '注册';
			});
	});
})();
</script>
<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
