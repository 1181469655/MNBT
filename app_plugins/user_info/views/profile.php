<?php
if (!defined('IN_CRONLITE')) {
	exit;
}
$page_title = $page_title ?? '个人信息';
$u = $current_user;
ob_start();
?>
<div class="account-msg" id="msg"></div>

<form class="account-form" id="profileForm">
	<h1>个人信息</h1>
	<p class="account-form-sub">查看和更新您的账户信息</p>

	<div class="account-info-row">
		<span class="account-info-label">用户名</span>
		<span class="account-info-value"><?= htmlspecialchars($u['username']) ?></span>
	</div>

	<div class="account-info-row">
		<span class="account-info-label">注册时间</span>
		<span class="account-info-value"><?= htmlspecialchars($u['created_at']) ?></span>
	</div>

	<div class="account-field" style="margin-top:20px;">
		<label for="email">邮箱</label>
		<input type="email" id="email" name="email" maxlength="128" value="<?= htmlspecialchars($u['email']) ?>" placeholder="选填，用于找回密码">
	</div>

	<div class="account-field">
		<label for="qq">QQ</label>
		<input type="text" id="qq" name="qq" maxlength="12" value="<?= htmlspecialchars($u['qq']) ?>" placeholder="选填">
	</div>

	<div class="account-form-actions">
		<button type="submit" class="account-btn" id="submitBtn">保存</button>
		<button type="button" class="account-btn account-btn-secondary" onclick="window.location.href='<?= user_info_url('account/password') ?>'">修改密码</button>
	</div>
</form>

<script>
(function () {
	var form = document.getElementById('profileForm');
	var msg = document.getElementById('msg');
	var btn = document.getElementById('submitBtn');

	function showMsg(text, type) {
		msg.textContent = text;
		msg.className = 'account-msg show ' + (type === 'success' ? 'account-msg-success' : 'account-msg-error');
	}

	form.addEventListener('submit', function (e) {
		e.preventDefault();
		btn.disabled = true;
		btn.textContent = '保存中...';
		msg.className = 'account-msg';

		var body = new URLSearchParams();
		body.append('email', document.getElementById('email').value);
		body.append('qq', document.getElementById('qq').value);

		fetch('<?= user_info_url('account/api/update_profile') ?>', {
			method: 'POST',
			headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
			body: body.toString()
		})
			.then(function (r) { return r.json(); })
			.then(function (res) {
				var ok = (res.code === '保存成功');
				showMsg(res.code || '操作失败', ok ? 'success' : 'error');
				btn.disabled = false;
				btn.textContent = '保存';
			})
			.catch(function () {
				showMsg('网络错误，请重试', 'error');
				btn.disabled = false;
				btn.textContent = '保存';
			});
	});
})();
</script>
<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
