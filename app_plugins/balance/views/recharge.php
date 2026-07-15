<?php
if (!defined('IN_CRONLITE')) {
	exit;
}
$page_title = $page_title ?? '余额充值';
$methods = $methods ?? [];
ob_start();
?>
<div class="bal-card">
	<div class="account-msg" id="msg"></div>

	<form class="bal-form" id="rechargeForm">
		<h1>余额充值</h1>
		<p class="bal-form-sub">选择支付方式并输入充值金额</p>

		<?php if (empty($methods)): ?>
			<p class="bal-empty">暂无可用的支付方式，请联系管理员启用支付插件。</p>
		<?php else: ?>
			<div class="bal-field">
				<label>支付方式</label>
				<div class="bal-pay-methods">
					<?php foreach ($methods as $m): ?>
						<label class="bal-pay-method">
							<input type="radio" name="type" value="<?= htmlspecialchars($m['plugin'] . '__' . $m['method']) ?>" required>
							<span><?= htmlspecialchars($m['display_name'] ?: ($m['plugin'] . ' / ' . $m['method'])) ?></span>
						</label>
					<?php endforeach; ?>
				</div>
			</div>

			<div class="bal-field">
				<label for="amount">充值金额（元）</label>
				<input type="number" id="amount" name="amount" step="0.01" min="1" max="50000" required placeholder="最低 1 元">
			</div>

			<div class="bal-quick-amounts">
				<button type="button" data-v="10">10 元</button>
				<button type="button" data-v="50">50 元</button>
				<button type="button" data-v="100">100 元</button>
				<button type="button" data-v="500">500 元</button>
			</div>

			<button type="submit" class="bal-btn bal-btn-primary" id="submitBtn">立即充值</button>
		<?php endif; ?>

		<div class="bal-form-footer">
			<a href="<?= balance_url('balance') ?>">返回余额页</a>
		</div>
	</form>
</div>

<script>
(function () {
	var form = document.getElementById('rechargeForm');
	if (!form) return;
	var msg = document.getElementById('msg');
	var btn = document.getElementById('submitBtn');

	function showMsg(text, type) {
		msg.textContent = text;
		msg.className = 'account-msg show ' + (type === 'success' ? 'account-msg-success' : 'account-msg-error');
	}

	// 快捷金额
	document.querySelectorAll('.bal-quick-amounts button').forEach(function (b) {
		b.addEventListener('click', function () {
			document.getElementById('amount').value = b.getAttribute('data-v');
		});
	});

	form.addEventListener('submit', function (e) {
		e.preventDefault();
		btn.disabled = true;
		btn.textContent = '正在创建订单...';
		msg.className = 'account-msg';

		var body = new URLSearchParams();
		body.append('amount', document.getElementById('amount').value);
		var checked = form.querySelector('input[name="type"]:checked');
		body.append('type', checked ? checked.value : '');

		fetch('<?= balance_url('balance/api/create_recharge') ?>', {
			method: 'POST',
			headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
			body: body.toString()
		})
			.then(function (r) { return r.json(); })
			.then(function (res) {
				if (res.html) {
					// 支付插件返回的 HTML（自动提交表单），用 document.write 输出跳转
					document.open();
					document.write(res.html);
					document.close();
				} else {
					showMsg(res.code || '创建订单失败', 'error');
					btn.disabled = false;
					btn.textContent = '立即充值';
				}
			})
			.catch(function () {
				showMsg('网络错误，请重试', 'error');
				btn.disabled = false;
				btn.textContent = '立即充值';
			});
	});
})();
</script>
<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
