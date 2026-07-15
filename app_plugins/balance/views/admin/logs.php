<?php
/**
 * 管理员端 - 余额流水查询
 */
if (!defined('IN_CRONLITE')) {
	exit;
}

$page_num = max(1, (int)($_GET['page_num'] ?? 1));
$filters = [
	'user_id'  => trim((string)($_GET['user_id'] ?? '')),
	'type'     => trim((string)($_GET['type'] ?? '')),
	'order_no' => trim((string)($_GET['order_no'] ?? '')),
];
$result = balance_admin_logs($page_num, 30, $filters);
$list = $result['list'];
$total = $result['total'];
$total_pages = $result['per_page'] > 0 ? (int)ceil($total / $result['per_page']) : 1;

$type_labels = [
	'recharge' => '充值',
	'consume'  => '消费',
	'refund'   => '退款',
	'adjust'   => '调整',
];
$type_classes = [
	'recharge' => 'badge-success',
	'consume'  => 'badge-danger',
	'refund'   => 'badge-info',
	'adjust'   => 'badge-warning',
];

$title = $title ?? '余额流水';
mnbt_admin_include('head');
?>

<div class="container-fluid p-t-15">
	<div class="card">
		<div class="card-header">
			<h4 style="display:inline-block">余额流水记录</h4>
			<small class="text-muted ml-2">共 <?= $total ?> 条记录</small>
		</div>
		<div class="card-body">
			<form method="get" class="form-inline mb-3">
				<input type="hidden" name="p" value="balance">
				<input type="hidden" name="page" value="balance_logs">
				<input type="number" name="user_id" class="form-control form-control-sm mr-2" placeholder="用户 ID" value="<?= htmlspecialchars($filters['user_id'], ENT_QUOTES) ?>">
				<select name="type" class="form-control form-control-sm mr-2">
					<option value="">全部类型</option>
					<?php foreach ($type_labels as $k => $v): ?>
						<option value="<?= htmlspecialchars($k, ENT_QUOTES) ?>" <?= $filters['type'] === $k ? 'selected' : '' ?>><?= htmlspecialchars($v) ?></option>
					<?php endforeach; ?>
				</select>
				<input type="text" name="order_no" class="form-control form-control-sm mr-2" placeholder="订单号" value="<?= htmlspecialchars($filters['order_no'], ENT_QUOTES) ?>">
				<button type="submit" class="btn btn-sm btn-primary">筛选</button>
				<a href="plugin.php?p=balance&page=balance_logs" class="btn btn-sm btn-outline-secondary ml-2">重置</a>
			</form>

			<div class="table-responsive">
				<table class="table table-bordered table-hover">
					<thead>
						<tr>
							<th style="width:60px">ID</th>
							<th>用户ID</th>
							<th>用户名</th>
							<th>金额（元）</th>
							<th>变动后余额（元）</th>
							<th>类型</th>
							<th>订单号</th>
							<th>备注</th>
							<th>时间</th>
						</tr>
					</thead>
					<tbody>
						<?php if (empty($list)): ?>
							<tr><td colspan="9" class="text-center text-muted">暂无记录</td></tr>
						<?php else: ?>
							<?php foreach ($list as $log):
								$amt = (int)$log['amount'];
								$is_income = $amt >= 0;
							?>
								<tr>
									<td><?= (int)$log['id'] ?></td>
									<td><?= (int)$log['user_id'] ?></td>
									<td><code><?= htmlspecialchars($log['username'] ?: ('#' . $log['user_id']), ENT_QUOTES) ?></code></td>
									<td>
										<b class="<?= $is_income ? 'text-success' : 'text-danger' ?>">
											<?= $is_income ? '+' : '' ?>¥<?= htmlspecialchars(balance_format(abs($amt)), ENT_QUOTES) ?>
										</b>
									</td>
									<td>¥<?= htmlspecialchars(balance_format((int)$log['balance_after']), ENT_QUOTES) ?></td>
									<td>
										<span class="badge <?= htmlspecialchars($type_classes[$log['type']] ?? 'badge-secondary', ENT_QUOTES) ?>">
											<?= htmlspecialchars($type_labels[$log['type']] ?? $log['type']) ?>
										</span>
									</td>
									<td class="small text-muted"><?= htmlspecialchars($log['order_no'] ?: '-', ENT_QUOTES) ?></td>
									<td class="small"><?= htmlspecialchars($log['remark'] ?: '-', ENT_QUOTES) ?></td>
									<td class="small text-muted"><?= htmlspecialchars($log['created_at'], ENT_QUOTES) ?></td>
								</tr>
							<?php endforeach; ?>
						<?php endif; ?>
					</tbody>
				</table>
			</div>

			<?php if ($total_pages > 1):
				$qs = http_build_query([
					'p'        => 'balance',
					'page'     => 'balance_logs',
					'user_id'  => $filters['user_id'],
					'type'     => $filters['type'],
					'order_no' => $filters['order_no'],
				]);
			?>
				<nav>
					<ul class="pagination justify-content-center">
						<?php for ($i = 1; $i <= $total_pages; $i++): ?>
							<li class="page-item <?= $i === $page_num ? 'active' : '' ?>">
								<a class="page-link" href="plugin.php?<?= htmlspecialchars($qs . '&page_num=' . $i, ENT_QUOTES) ?>"><?= $i ?></a>
							</li>
						<?php endfor; ?>
					</ul>
				</nav>
			<?php endif; ?>
		</div>
	</div>
</div>
