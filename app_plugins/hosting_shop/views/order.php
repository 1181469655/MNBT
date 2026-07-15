<?php
if (!defined('IN_CRONLITE')) {
	exit;
}
$page_title = $page_title ?? '购买套餐';
$plan = $plan ?? null;
$nodes = $nodes ?? [];
$methods = $methods ?? [];

ob_start();
?>
<div class="hs-card">
	<div class="account-msg" id="msg"></div>

	<div class="hs-order-head">
		<h1>购买：<?= htmlspecialchars($plan['name']) ?></h1>
		<a class="hs-btn hs-btn-link" href="<?= hosting_url('shop') ?>">返回套餐列表</a>
	</div>

	<ul class="hs-plan-spec">
		<li><span>产品类型</span><b><?= (int)$plan['spec_type'] === 1 ? 'CDN' : '虚拟主机' ?></b></li>
		<li><span>网页空间</span><b><?= (int)$plan['spec_web'] ?> MB</b></li>
		<li><span>数据库</span><b><?= (int)$plan['spec_sql'] ?> MB</b></li>
		<li><span>流量</span><b><?= (int)$plan['spec_flow'] > 0 ? ((int)$plan['spec_flow'] . ' GB') : '不限' ?></b></li>
		<li><span>域名绑定</span><b><?= (int)$plan['spec_domain'] ?> 个</b></li>
	</ul>

	<form class="hs-form" id="orderForm">
		<div class="hs-field">
			<label>购买周期</label>
			<div class="hs-period-options">
				<?php if ((int)$plan['price_month_cents'] > 0): ?>
					<label class="hs-period">
						<input type="radio" name="period" value="month" required <?= (int)$plan['price_year_cents'] <= 0 ? 'checked' : '' ?>>
						<span>月付 ¥<?= htmlspecialchars(hosting_format_cents($plan['price_month_cents'])) ?></span>
					</label>
				<?php endif; ?>
				<?php if ((int)$plan['price_year_cents'] > 0): ?>
					<label class="hs-period">
						<input type="radio" name="period" value="year" required <?= (int)$plan['price_month_cents'] <= 0 ? 'checked' : '' ?>>
						<span>年付 ¥<?= htmlspecialchars(hosting_format_cents($plan['price_year_cents'])) ?></span>
					</label>
				<?php endif; ?>
			</div>
		</div>

		<div class="hs-field">
			<label>开通节点</label>
			<?php if (empty($nodes)): ?>
				<p class="hs-empty">管理员尚未添加宝塔节点，无法购买。请联系管理员。</p>
			<?php else: ?>
				<select name="node" class="hs-select" required>
					<option value="">请选择节点</option>
					<?php foreach ($nodes as $n): ?>
						<option value="<?= htmlspecialchars($n['btdh']) ?>">
							<?= htmlspecialchars($n['btdh']) ?>（<?= htmlspecialchars($n['btip']) ?>，<?= $n['btos'] == '1' ? 'Linux' : 'Windows' ?>）
						</option>
					<?php endforeach; ?>
				</select>
			<?php endif; ?>
		</div>

		<?php if (!empty($methods)): ?>
			<div class="hs-field">
				<label>支付方式</label>
				<div class="hs-pay-methods">
					<?php foreach ($methods as $m): ?>
						<label class="hs-pay-method">
							<input type="radio" name="type" value="<?= htmlspecialchars($m['plugin'] . '__' . $m['method']) ?>" required>
							<span><?= htmlspecialchars($m['display_name'] ?: ($m['plugin'] . ' / ' . $m['method'])) ?></span>
						</label>
					<?php endforeach; ?>
				</div>
			</div>
		<?php else: ?>
			<p class="hs-empty">暂无可用的支付方式，请联系管理员启用支付插件。</p>
		<?php endif; ?>

		<?php if (!empty($nodes) && !empty($methods)): ?>
			<button type="submit" class="hs-btn hs-btn-primary" id="submitBtn">确认购买</button>
		<?php endif; ?>
	</form>
</div>

<script>
(function () {
	var form = document.getElementById('orderForm');
	if (!form) return;
	var msg = document.getElementById('msg');
	var btn = document.getElementById('submitBtn');

	function showMsg(text, type) {
		msg.textContent = text;
		msg.className = 'account-msg show ' + (type === 'success' ? 'account-msg-success' : 'account-msg-error');
	}

	form.addEventListener('submit', function (e) {
		e.preventDefault();
		btn.disabled = true;
		btn.textContent = '正在创建订单...';
		msg.className = 'account-msg';

		var body = new URLSearchParams();
		body.append('plan_id', '<?= (int)$plan['id'] ?>');
		var periodChecked = form.querySelector('input[name="period"]:checked');
		body.append('period', periodChecked ? periodChecked.value : '');
		body.append('node', form.querySelector('select[name="node"]').value);
		var typeChecked = form.querySelector('input[name="type"]:checked');
		body.append('type', typeChecked ? typeChecked.value : '');

		fetch('<?= hosting_url('shop/api/create_order') ?>', {
			method: 'POST',
			headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
			body: body.toString()
		})
			.then(function (r) { return r.json(); })
			.then(function (res) {
				if (res.html) {
					document.open();
					document.write(res.html);
					document.close();
				} else {
					showMsg(res.code || '创建订单失败', 'error');
					btn.disabled = false;
					btn.textContent = '确认购买';
				}
			})
			.catch(function () {
				showMsg('网络错误，请重试', 'error');
				btn.disabled = false;
				btn.textContent = '确认购买';
			});
	});
})();
</script>
<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
