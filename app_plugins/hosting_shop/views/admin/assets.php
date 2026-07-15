<?php
/**
 * 管理员端 - 资产管理
 */
if (!defined('IN_CRONLITE')) {
	exit;
}

$page = max(1, (int)($_GET['page_num'] ?? 1));
$assets = hosting_asset_list_all($page, 30);

$status_labels = [
	'active' => '正常',
	'expired' => '已到期',
	'cancelled' => '已取消',
];
$status_classes = [
	'active' => 'badge-success',
	'expired' => 'badge-warning',
	'cancelled' => 'badge-secondary',
];

$title = $title ?? '资产管理';
mnbt_admin_include('head');
?>
<div class="container-fluid p-t-15">
	<div class="card">
		<div class="card-header">
			<h4 style="display:inline-block">资产管理</h4>
		</div>
		<div class="card-body">
			<div class="table-responsive">
				<table class="table table-bordered table-hover">
					<thead>
						<tr>
							<th>ID</th>
							<th>用户ID</th>
							<th>套餐</th>
							<th>主机ID</th>
							<th>主机账号</th>
							<th>节点</th>
							<th>宝塔站点ID</th>
							<th>开通时间</th>
							<th>到期时间</th>
							<th>状态</th>
						</tr>
					</thead>
					<tbody>
						<?php if (empty($assets['list'])): ?>
							<tr><td colspan="10" class="text-center text-muted">暂无资产</td></tr>
						<?php else: ?>
							<?php foreach ($assets['list'] as $a): ?>
								<tr>
									<td><?= (int)$a['id'] ?></td>
									<td><?= (int)$a['user_id'] ?></td>
									<td><?= htmlspecialchars($a['plan_name'], ENT_QUOTES) ?></td>
									<td><?= (int)$a['host_id'] > 0 ? (int)$a['host_id'] : '-' ?></td>
									<td class="small"><?= htmlspecialchars($a['host_user'] ?? '-', ENT_QUOTES) ?></td>
									<td><?= htmlspecialchars($a['ssbt'] ?? '-', ENT_QUOTES) ?></td>
									<td class="small text-muted"><?= htmlspecialchars($a['btid'] ?? '-', ENT_QUOTES) ?></td>
									<td class="small"><?= htmlspecialchars($a['data'] ?? $a['created_at']) ?></td>
									<td class="small"><?= htmlspecialchars($a['expire_at']) ?></td>
									<td>
										<span class="badge <?= htmlspecialchars($status_classes[$a['status']] ?? 'badge-secondary', ENT_QUOTES) ?>">
											<?= htmlspecialchars($status_labels[$a['status']] ?? $a['status']) ?>
										</span>
									</td>
								</tr>
							<?php endforeach; ?>
						<?php endif; ?>
					</tbody>
				</table>
			</div>

			<?php
			$total_pages = max(1, (int)ceil($assets['total'] / $assets['per_page']));
			$current_page = (int)$assets['page'];
			if ($total_pages > 1):
			?>
				<nav>
					<ul class="pagination pagination-sm">
						<?php if ($current_page > 1): ?>
							<li class="page-item"><a class="page-link" href="plugin.php?p=hosting_shop&page=assets&page_num=<?= $current_page - 1 ?>">上一页</a></li>
						<?php endif; ?>
						<li class="page-item disabled"><span class="page-link">第 <?= $current_page ?> / <?= $total_pages ?> 页（共 <?= (int)$assets['total'] ?> 条）</span></li>
						<?php if ($current_page < $total_pages): ?>
							<li class="page-item"><a class="page-link" href="plugin.php?p=hosting_shop&page=assets&page_num=<?= $current_page + 1 ?>">下一页</a></li>
						<?php endif; ?>
					</ul>
				</nav>
			<?php endif; ?>
		</div>
	</div>
</div>
