<?php
/**
 * 管理员端 - Docker 资产管理
 */
if (!defined('IN_CRONLITE')) {
	exit;
}

$page = max(1, (int)($_GET['page_num'] ?? 1));
$assets = docker_shop_asset_list_all($page, 30);

$container_labels = [
	'none' => '未创建',
	'creating' => '创建中',
	'running' => '运行中',
	'stopped' => '已停止',
	'failed' => '异常',
];
$container_classes = [
	'none' => 'badge-secondary',
	'creating' => 'badge-info',
	'running' => 'badge-success',
	'stopped' => 'badge-warning',
	'failed' => 'badge-danger',
];

$title = $title ?? '资产管理';
mnbt_admin_include('head');
?>
<div class="container-fluid p-t-15">
	<div class="card">
		<div class="card-header">
			<h4 style="display:inline-block">Docker 资产管理</h4>
		</div>
		<div class="card-body">
			<div class="table-responsive">
				<table class="table table-bordered table-hover">
					<thead>
						<tr>
							<th>ID</th>
							<th>用户ID</th>
							<th>套餐</th>
							<th>Docker账号</th>
							<th>节点</th>
							<th>容器状态</th>
							<th>磁盘用量</th>
							<th>到期时间</th>
							<th>状态</th>
							<th>开通时间</th>
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
								<td class="small"><?= htmlspecialchars($a['docker_username'] ?: '-', ENT_QUOTES) ?></td>
								<td><?= htmlspecialchars($a['node_name'] ?: '-', ENT_QUOTES) ?></td>
								<td>
									<span class="badge <?= htmlspecialchars($container_classes[$a['container_status']] ?? 'badge-secondary', ENT_QUOTES) ?>">
										<?= htmlspecialchars($container_labels[$a['container_status']] ?? $a['container_status']) ?>
									</span>
								</td>
								<td class="small"><?= (int)$a['disk_usage'] > 0 ? round((int)$a['disk_usage'] / 1048576, 1) . ' MB' : '-' ?></td>
								<td class="small"><?= htmlspecialchars($a['expire_at'] ?: '-', ENT_QUOTES) ?></td>
								<td>
									<span class="badge <?= $a['status'] === 'active' ? 'badge-success' : 'badge-warning' ?>">
										<?= $a['status'] === 'active' ? '有效' : htmlspecialchars($a['status']) ?>
									</span>
								</td>
								<td class="small"><?= htmlspecialchars($a['created_at']) ?></td>
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
							<li class="page-item"><a class="page-link" href="<?= htmlspecialchars(docker_shop_admin_url('assets', 'page_num=' . ($current_page - 1)), ENT_QUOTES) ?>">上一页</a></li>
						<?php endif; ?>
						<li class="page-item disabled"><span class="page-link">第 <?= $current_page ?> / <?= $total_pages ?> 页（共 <?= (int)$assets['total'] ?> 条）</span></li>
						<?php if ($current_page < $total_pages): ?>
							<li class="page-item"><a class="page-link" href="<?= htmlspecialchars(docker_shop_admin_url('assets', 'page_num=' . ($current_page + 1)), ENT_QUOTES) ?>">下一页</a></li>
						<?php endif; ?>
					</ul>
				</nav>
			<?php endif; ?>
		</div>
	</div>
</div>
<?php mnbt_admin_include('foot'); ?>
