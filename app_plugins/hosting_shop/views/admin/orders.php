<?php
/**
 * 管理员端 - 订单管理
 */
if (!defined('IN_CRONLITE')) {
	exit;
}

$page = max(1, (int)($_GET['page_num'] ?? 1));
$filters = [
	'status' => $_GET['status'] ?? '',
	'user_id' => $_GET['user_id'] ?? '',
	'order_no' => $_GET['order_no'] ?? '',
];
$orders = hosting_order_list_all($page, 30, $filters);

$status_labels = [
	'pending' => '待支付',
	'paid' => '已支付',
	'opened' => '已开通',
	'failed' => '失败',
	'cancelled' => '已取消',
];
$status_classes = [
	'pending' => 'badge-secondary',
	'paid' => 'badge-info',
	'opened' => 'badge-success',
	'failed' => 'badge-danger',
	'cancelled' => 'badge-warning',
];

$title = $title ?? '订单管理';
mnbt_admin_include('head');
?>
<div class="container-fluid p-t-15">
	<div class="card">
		<div class="card-header">
			<h4 style="display:inline-block">订单管理</h4>
		</div>
		<div class="card-body">
			<form method="get" class="form-inline mb-3">
				<input type="hidden" name="p" value="hosting_shop">
				<input type="hidden" name="page" value="orders">
				<select name="status" class="form-control form-control-sm mr-2">
					<option value="">全部状态</option>
					<?php foreach ($status_labels as $k => $v): ?>
						<option value="<?= htmlspecialchars($k, ENT_QUOTES) ?>" <?= $filters['status'] === $k ? 'selected' : '' ?>><?= htmlspecialchars($v) ?></option>
					<?php endforeach; ?>
				</select>
				<input type="text" name="order_no" class="form-control form-control-sm mr-2" placeholder="订单号" value="<?= htmlspecialchars($filters['order_no'], ENT_QUOTES) ?>">
				<input type="number" name="user_id" class="form-control form-control-sm mr-2" placeholder="用户 ID" value="<?= htmlspecialchars($filters['user_id'], ENT_QUOTES) ?>">
				<button type="submit" class="btn btn-sm btn-primary">筛选</button>
			</form>

			<div class="table-responsive">
				<table class="table table-bordered table-hover">
					<thead>
						<tr>
							<th>ID</th>
							<th>订单号</th>
							<th>用户ID</th>
							<th>套餐</th>
							<th>周期</th>
							<th>金额</th>
							<th>节点</th>
							<th>主机ID</th>
							<th>状态</th>
							<th>下单时间</th>
							<th>开通时间</th>
							<th>备注</th>
						</tr>
					</thead>
					<tbody>
						<?php if (empty($orders['list'])): ?>
							<tr><td colspan="12" class="text-center text-muted">暂无订单</td></tr>
						<?php else: ?>
							<?php foreach ($orders['list'] as $o): ?>
								<tr>
									<td><?= (int)$o['id'] ?></td>
									<td class="small text-muted"><?= htmlspecialchars($o['order_no'], ENT_QUOTES) ?></td>
									<td><?= (int)$o['user_id'] ?></td>
									<td><?= htmlspecialchars($o['plan_name'], ENT_QUOTES) ?></td>
									<td><?= $o['period'] === 'year' ? '年付' : '月付' ?></td>
									<td>¥<?= htmlspecialchars(hosting_format_cents($o['amount_cents'])) ?></td>
									<td><?= htmlspecialchars($o['node'], ENT_QUOTES) ?></td>
									<td><?= (int)$o['host_id'] > 0 ? (int)$o['host_id'] : '-' ?></td>
									<td>
										<span class="badge <?= htmlspecialchars($status_classes[$o['status']] ?? 'badge-secondary', ENT_QUOTES) ?>">
											<?= htmlspecialchars($status_labels[$o['status']] ?? $o['status']) ?>
										</span>
									</td>
									<td class="small"><?= htmlspecialchars($o['created_at']) ?></td>
									<td class="small"><?= htmlspecialchars($o['opened_at']) ?></td>
									<td class="small"><?= htmlspecialchars($o['remark'] ?: '-', ENT_QUOTES) ?></td>
								</tr>
							<?php endforeach; ?>
						<?php endif; ?>
					</tbody>
				</table>
			</div>

			<?php
			$total_pages = max(1, (int)ceil($orders['total'] / $orders['per_page']));
			$current_page = (int)$orders['page'];
			if ($total_pages > 1):
				$qs = http_build_query(['p' => 'hosting_shop', 'page' => 'orders', 'status' => $filters['status'], 'order_no' => $filters['order_no'], 'user_id' => $filters['user_id']]);
			?>
				<nav>
					<ul class="pagination pagination-sm">
						<?php if ($current_page > 1): ?>
							<li class="page-item"><a class="page-link" href="plugin.php?<?= htmlspecialchars($qs . '&page_num=' . ($current_page - 1), ENT_QUOTES) ?>">上一页</a></li>
						<?php endif; ?>
						<li class="page-item disabled"><span class="page-link">第 <?= $current_page ?> / <?= $total_pages ?> 页（共 <?= (int)$orders['total'] ?> 条）</span></li>
						<?php if ($current_page < $total_pages): ?>
							<li class="page-item"><a class="page-link" href="plugin.php?<?= htmlspecialchars($qs . '&page_num=' . ($current_page + 1), ENT_QUOTES) ?>">下一页</a></li>
						<?php endif; ?>
					</ul>
				</nav>
			<?php endif; ?>
		</div>
	</div>
</div>
