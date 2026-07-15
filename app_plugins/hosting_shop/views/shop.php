<?php
if (!defined('IN_CRONLITE')) {
	exit;
}
$page_title = $page_title ?? '主机套餐';
$plans = $plans ?? [];
$nodes = $nodes ?? [];

ob_start();
?>
<div class="hs-section-header">
	<h1>主机套餐</h1>
	<p class="hs-sub">选择合适的套餐购买，支付完成后自动开通主机</p>
</div>

<?php if (empty($plans)): ?>
	<div class="hs-card">
		<p class="hs-empty">暂无可购买的套餐，请稍后再来。</p>
	</div>
<?php else: ?>
	<div class="hs-plan-grid">
		<?php foreach ($plans as $plan): ?>
			<div class="hs-plan-card">
				<div class="hs-plan-head">
					<h2><?= htmlspecialchars($plan['name']) ?></h2>
					<span class="hs-plan-tag"><?= (int)$plan['spec_type'] === 1 ? 'CDN' : '虚拟主机' ?></span>
				</div>
				<div class="hs-plan-desc"><?= nl2br(htmlspecialchars($plan['description'])) ?></div>

				<ul class="hs-plan-spec">
					<li><span>网页空间</span><b><?= (int)$plan['spec_web'] ?> MB</b></li>
					<li><span>数据库</span><b><?= (int)$plan['spec_sql'] ?> MB</b></li>
					<li><span>流量</span><b><?= (int)$plan['spec_flow'] > 0 ? ((int)$plan['spec_flow'] . ' GB') : '不限' ?></b></li>
					<li><span>域名绑定</span><b><?= (int)$plan['spec_domain'] ?> 个</b></li>
				</ul>

				<div class="hs-plan-price">
					<?php if ((int)$plan['price_month_cents'] > 0): ?>
						<div class="hs-price-item">
							<span class="hs-price-label">月付</span>
							<span class="hs-price-value">¥<?= htmlspecialchars(hosting_format_cents($plan['price_month_cents'])) ?></span>
						</div>
					<?php endif; ?>
					<?php if ((int)$plan['price_year_cents'] > 0): ?>
						<div class="hs-price-item">
							<span class="hs-price-label">年付</span>
							<span class="hs-price-value">¥<?= htmlspecialchars(hosting_format_cents($plan['price_year_cents'])) ?></span>
						</div>
					<?php endif; ?>
				</div>

				<a class="hs-btn hs-btn-primary hs-plan-buy" href="<?= hosting_url('shop/order/' . (int)$plan['id']) ?>">立即购买</a>
			</div>
		<?php endforeach; ?>
	</div>
<?php endif; ?>

<?php if (!empty($nodes)): ?>
	<div class="hs-card">
		<h2 class="hs-section-title">开通节点</h2>
		<ul class="hs-node-list">
			<?php foreach ($nodes as $n): ?>
				<li>
					<span class="hs-node-name"><?= htmlspecialchars($n['btdh']) ?></span>
					<span class="hs-node-info"><?= htmlspecialchars($n['btip']) ?>:<?= htmlspecialchars($n['btdk']) ?> · <?= $n['btos'] == '1' ? 'Linux' : 'Windows' ?> · <?= $n['ptl'] == 'true' ? 'HTTPS' : 'HTTP' ?></span>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
<?php endif; ?>
<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
