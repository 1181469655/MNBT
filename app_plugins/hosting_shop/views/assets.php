<?php
if (!defined('IN_CRONLITE')) {
	exit;
}
$page_title = $page_title ?? '我的主机';
$assets = $assets ?? [];

$status_labels = [
	'active' => '正常',
	'expired' => '已到期',
	'cancelled' => '已取消',
];

ob_start();
?>
<div class="hs-section-header">
	<h1>我的主机</h1>
	<p class="hs-sub">已开通的虚拟主机资产</p>
</div>

<div class="hs-card">
	<?php if (empty($assets)): ?>
		<p class="hs-empty">您还没有开通的主机，<a href="<?= hosting_url('shop') ?>">去购买</a></p>
	<?php else: ?>
		<table class="hs-table">
			<thead>
				<tr>
					<th>套餐</th>
					<th>主机账号</th>
					<th>节点</th>
					<th>开通时间</th>
					<th>到期时间</th>
					<th>状态</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($assets as $a): ?>
					<tr>
						<td><?= htmlspecialchars($a['plan_name']) ?></td>
						<td class="hs-mono"><?= htmlspecialchars($a['host_user'] ?? '-') ?></td>
						<td><?= htmlspecialchars($a['ssbt'] ?? '-') ?></td>
						<td><?= htmlspecialchars($a['created_at']) ?></td>
						<td><?= htmlspecialchars($a['expire_at']) ?></td>
						<td>
							<span class="hs-status hs-status-<?= htmlspecialchars($a['status']) ?>">
								<?= htmlspecialchars($status_labels[$a['status']] ?? $a['status']) ?>
							</span>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	<?php endif; ?>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
