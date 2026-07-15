<?php
if (!defined('IN_CRONLITE')) {
	exit;
}
$page_title = $page_title ?? '我的余额';
$balance_cents = $balance_cents ?? 0;
$logs = $logs ?? ['list' => [], 'total' => 0, 'page' => 1, 'per_page' => 15];

$type_labels = [
	'recharge' => '充值',
	'consume' => '消费',
	'refund' => '退款',
	'adjust' => '调整',
];

ob_start();
?>
<div class="bal-card">
	<div class="bal-balance-box">
		<div class="bal-balance-label">当前余额</div>
		<div class="bal-balance-amount">¥<?= htmlspecialchars(balance_format($balance_cents)) ?></div>
		<a class="bal-btn bal-btn-primary" href="<?= balance_url('balance/recharge') ?>">充值</a>
	</div>
</div>

<div class="bal-card">
	<h2 class="bal-section-title">交易记录</h2>
	<?php if (empty($logs['list'])): ?>
		<p class="bal-empty">暂无交易记录</p>
	<?php else: ?>
		<table class="bal-table">
			<thead>
				<tr>
					<th>时间</th>
					<th>类型</th>
					<th>金额</th>
					<th>备注</th>
					<th>订单号</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($logs['list'] as $log): ?>
					<tr>
						<td><?= htmlspecialchars($log['created_at']) ?></td>
						<td><?= htmlspecialchars($type_labels[$log['type']] ?? $log['type']) ?></td>
						<td class="<?= (int)$log['amount'] >= 0 ? 'bal-income' : 'bal-expense' ?>">
							<?= (int)$log['amount'] >= 0 ? '+' : '' ?>¥<?= htmlspecialchars(balance_format(abs((int)$log['amount']))) ?>
						</td>
						<td><?= htmlspecialchars($log['remark'] ?: '-') ?></td>
						<td class="bal-mono"><?= $log['order_no'] ? htmlspecialchars($log['order_no']) : '-' ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>

		<?php
		$total_pages = max(1, (int)ceil($logs['total'] / $logs['per_page']));
		$current_page = (int)$logs['page'];
		if ($total_pages > 1):
		?>
			<div class="bal-pagination">
				<?php if ($current_page > 1): ?>
					<a href="<?= balance_url('balance?page=' . ($current_page - 1)) ?>">上一页</a>
				<?php endif; ?>
				<span class="bal-page-info">第 <?= $current_page ?> / <?= $total_pages ?> 页</span>
				<?php if ($current_page < $total_pages): ?>
					<a href="<?= balance_url('balance?page=' . ($current_page + 1)) ?>">下一页</a>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	<?php endif; ?>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
