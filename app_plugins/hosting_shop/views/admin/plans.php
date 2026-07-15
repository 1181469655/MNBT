<?php
/**
 * 管理员端 - 套餐管理（列表 + 删除）
 */
if (!defined('IN_CRONLITE')) {
	exit;
}

// 处理 POST 删除
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['act'] ?? '') === 'delete') {
	$plan_id = (int)($_POST['plan_id'] ?? 0);
	if ($plan_id > 0 && hosting_plan_delete($plan_id)) {
		$msg = '套餐已删除';
		$msg_type = 'success';
	} else {
		$msg = '删除失败';
		$msg_type = 'danger';
	}
}

$plans = hosting_plan_list_all();
$title = $title ?? '套餐管理';
mnbt_admin_include('head');
?>
<div class="container-fluid p-t-15">
	<div class="card">
		<div class="card-header">
			<h4 style="display:inline-block">套餐管理</h4>
			<a class="btn btn-primary btn-sm float-right" href="<?= htmlspecialchars(hosting_admin_url('plan_edit'), ENT_QUOTES) ?>">
				<i class="mdi mdi-plus"></i> 新增套餐
			</a>
		</div>
		<div class="card-body">
			<?php if (!empty($msg)): ?>
				<div class="alert alert-<?= htmlspecialchars($msg_type, ENT_QUOTES) ?>"><?= htmlspecialchars($msg) ?></div>
			<?php endif; ?>

			<?php if (empty($plans)): ?>
				<p class="text-muted">暂无套餐，点击右上角"新增套餐"创建。</p>
			<?php else: ?>
				<div class="table-responsive">
					<table class="table table-bordered table-hover">
						<thead>
							<tr>
								<th>ID</th>
								<th>名称</th>
								<th>类型</th>
								<th>网页/数据库/流量</th>
								<th>域名数</th>
								<th>月付/年付</th>
								<th>状态</th>
								<th>排序</th>
								<th style="width:180px">操作</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($plans as $p): ?>
								<tr>
									<td><?= (int)$p['id'] ?></td>
									<td><?= htmlspecialchars($p['name'], ENT_QUOTES) ?></td>
									<td><?= (int)$p['spec_type'] === 1 ? 'CDN' : '主机' ?></td>
									<td><?= (int)$p['spec_web'] ?>MB / <?= (int)$p['spec_sql'] ?>MB / <?= (int)$p['spec_flow'] > 0 ? ((int)$p['spec_flow'] . 'GB') : '不限' ?></td>
									<td><?= (int)$p['spec_domain'] ?></td>
									<td>¥<?= htmlspecialchars(hosting_format_cents($p['price_month_cents'])) ?> / ¥<?= htmlspecialchars(hosting_format_cents($p['price_year_cents'])) ?></td>
									<td>
										<?php if ($p['status'] === 'active'): ?>
											<span class="badge badge-success">上架</span>
										<?php else: ?>
											<span class="badge badge-secondary">下架</span>
										<?php endif; ?>
									</td>
									<td><?= (int)$p['sort'] ?></td>
									<td>
										<a class="btn btn-sm btn-outline-primary" href="<?= htmlspecialchars(hosting_admin_url('plan_edit', 'id=' . (int)$p['id']), ENT_QUOTES) ?>">编辑</a>
										<form method="post" style="display:inline-block" onsubmit="return confirm('确定删除此套餐？已有订单不受影响。')">
											<input type="hidden" name="act" value="delete">
											<input type="hidden" name="plan_id" value="<?= (int)$p['id'] ?>">
											<button type="submit" class="btn btn-sm btn-outline-danger">删除</button>
										</form>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			<?php endif; ?>
		</div>
	</div>
</div>
