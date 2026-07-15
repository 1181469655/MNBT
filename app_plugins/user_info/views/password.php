<?php
if (!defined('IN_CRONLITE')) {
	exit;
}
$page_title = $page_title ?? '修改密码';
ob_start();
?>
<div class="account-msg" id="msg"></div>

<form class="account-form" id="passwordForm" autocomplete="on">
	<h1>修改密码</h1>
	<p class="account-form-sub">修改后需使用新密码登录</p>

	<div class="account-field">
		<label for="old_password">原密码</label>
		<input type="password" id="old_password" name="old_password" required autocomplete="current-password" placeholder="输入当前密码">
	</div>

	<div class="account-field">
		<label for="new_password">新密码 <span style="color:#999;">（至少 6 个字符）</span></label>
		<input type="password" id="new_password" name="new_password" required autocomplete="new-password" placeholder="设置新密码">
	</div>

	<div class="account-field">
		<label for="new_password2">确认新密码</label>
		<input type="password" id="new_password2" name="new_password2" required autocomplete="new-password" placeholder="再次输入新密码">
	</div>

	<div class="account-form-actions">
		<button type="submit" class="account-btn" id="submitBtn">确认修改</button>
		<button type="button" class="account-btn account-btn-secondary" onclick="window.location.href='<?= user_info_url('account/profile') ?>'">返回</button>
	</div>
</form>

<script>
(function () {
	var form = document.getElementById('passwordForm');
	var msg = document.getElementById('msg');
	var btn = document.getElementById('submitBtn');

	function showMsg(text, type) {
		msg.textContent = text;
		msg.className = 'account-msg show ' + (type === 'success' ? 'account-msg-success' : 'account-msg-error');
	}

	form.addEventListener('submit', function (e) {
		e.preventDefault();
		btn.disabled = true;
		btn.textContent = '修改中...';
		msg.className = 'account-msg';

		var body = new URLSearchParams();
		body.append('old_password', document.getElementById('old_password').value);
		body.append('new_password', document.getElementById('new_password').value);
		body.append('new_password2', document.getElementById('new_password2').value);

		fetch('<?= user_info_url('account/api/change_password') ?>', {
			method: 'POST',
			headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
			body: body.toString()
		})
			.then(function (r) { return r.json(); })
			.then(function (res) {
				var ok = (res.code === '修改成功');
				showMsg(res.code || '操作失败', ok ? 'success' : 'error');
				if (ok) {
					form.reset();
				}
				btn.disabled = false;
				btn.textContent = '确认修改';
			})
			.catch(function () {
				showMsg('网络错误，请重试', 'error');
				btn.disabled = false;
				btn.textContent = '确认修改';
			});
	});
})();
</script>
<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
