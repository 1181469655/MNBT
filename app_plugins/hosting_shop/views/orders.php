<?php
if (!defined('IN_CRONLITE')) {
	exit;
}
$page_title = $page_title ?? '我的订单';
$orders = $orders ?? ['list' => [], 'total' => 0, 'page' => 1, 'per_page' => 15];

$status_labels = [
	'pending' => '待支付',
	'paid' => '已支付',
	'opened' => '已开通',
	'failed' => '失败',
	'cancelled' => '已取消',
];

ob_start();
?>
<div class="hs-section-header">
	<h1>我的订单</h1>
	<p class="hs-sub">主机购买订单记录</p>
</div>

<div class="hs-card">
	<?php if (empty($orders['list'])): ?>
		<p class="hs-empty">暂无订单，<a href="<?= hosting_url('shop') ?>">去购买主机</a></p>
	<?php else: ?>
		<table class="hs-table">
			<thead>
				<tr>
					<th>订单号</th>
					<th>套餐</th>
					<th>周期</th>
					<th>金额</th>
					<th>状态</th>
					<th>下单时间</th>
					<th>备注</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($orders['list'] as $o): ?>
					<tr>
						<td class="hs-mono"><?= htmlspecialchars($o['order_no']) ?></td>
						<td><?= htmlspecialchars($o['plan_name']) ?></td>
						<td><?= $o['period'] === 'year' ? '年付' : '月付' ?></td>
						<td>¥<?= htmlspecialchars(hosting_format_cents($o['amount_cents'])) ?></td>
						<td>
							<span class="hs-status hs-status-<?= htmlspecialchars($o['status']) ?>">
								<?= htmlspecialchars($status_labels[$o['status']] ?? $o['status']) ?>
							</span>
						</td>
						<td><?= htmlspecialchars($o['created_at']) ?></td>
						<td><?= htmlspecialchars($o['remark'] ?: '-') ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>

		<?php
		$total_pages = max(1, (int)ceil($orders['total'] / $orders['per_page']));
		$current_page = (int)$orders['page'];
		if ($total_pages > 1):
		?>
			<div class="hs-pagination">
				<?php if ($current_page > 1): ?>
					<a href="<?= hosting_url('shop/orders?page=' . ($current_page - 1)) ?>">上一页</a>
				<?php endif; ?>
				<span class="hs-page-info">第 <?= $current_page ?> / <?= $total_pages ?> 页</span>
				<?php if ($current_page < $total_pages): ?>
					<a href="<?= hosting_url('shop/orders?page=' . ($current_page + 1)) ?>">下一页</a>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	<?php endif; ?>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
