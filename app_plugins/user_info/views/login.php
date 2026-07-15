<?php
if (!defined('IN_CRONLITE')) {
	exit;
}
$page_title = $page_title ?? '登录';
ob_start();
?>
<div class="account-msg" id="msg"></div>

<form class="account-form" id="loginForm" autocomplete="on">
	<h1>登录</h1>
	<p class="account-form-sub">登录到您的账户</p>

	<div class="account-field">
		<label for="username">用户名</label>
		<input type="text" id="username" name="username" required maxlength="32" autocomplete="username" placeholder="字母、数字或下划线">
	</div>

	<div class="account-field">
		<label for="password">密码</label>
		<input type="password" id="password" name="password" required autocomplete="current-password" placeholder="至少 6 个字符">
	</div>

	<button type="submit" class="account-btn" id="submitBtn">登录</button>

	<div class="account-form-footer">
		还没有账号？<a href="<?= user_info_url('account/register') ?>">立即注册</a>
	</div>
</form>

<script>
(function () {
	var form = document.getElementById('loginForm');
	var msg = document.getElementById('msg');
	var btn = document.getElementById('submitBtn');

	function showMsg(text, type) {
		msg.textContent = text;
		msg.className = 'account-msg show ' + (type === 'success' ? 'account-msg-success' : 'account-msg-error');
	}

	form.addEventListener('submit', function (e) {
		e.preventDefault();
		btn.disabled = true;
		btn.textContent = '登录中...';
		msg.className = 'account-msg';

		var body = new URLSearchParams();
		body.append('username', document.getElementById('username').value);
		body.append('password', document.getElementById('password').value);

		fetch('<?= user_info_url('account/api/login') ?>', {
			method: 'POST',
			headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
			body: body.toString()
		})
			.then(function (r) { return r.json(); })
			.then(function (res) {
				if (res.redirect) {
					showMsg(res.code || '登录成功', 'success');
					setTimeout(function () { window.location.href = res.redirect; }, 300);
				} else {
					showMsg(res.code || '登录失败', 'error');
					btn.disabled = false;
					btn.textContent = '登录';
				}
			})
			.catch(function () {
				showMsg('网络错误，请重试', 'error');
				btn.disabled = false;
				btn.textContent = '登录';
			});
	});
})();
</script>
<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
