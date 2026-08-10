<?php
/**
 * 管理员端 - 售卖套餐管理
 */
if (!defined('IN_CRONLITE')) {
	exit;
}

// —— 处理 POST（成功后 redirect，避免刷新重复提交）——
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$act = $_POST['act'] ?? '';
	if ($act === 'delete') {
		$plan_id = (int)($_POST['plan_id'] ?? 0);
		$ok = $plan_id > 0 && docker_shop_plan_delete($plan_id);
		header('Location: ' . docker_shop_admin_url('plans', ($ok ? 'saved=1&msg=' . urlencode('套餐已删除') : 'msg=' . urlencode('删除失败'))));
		exit;
	}
}

$saved = isset($_GET['saved']);
$msg = $_GET['msg'] ?? ($msg ?? '');
$msg_type = $saved ? 'success' : ($msg_type ?? 'danger');
if ($saved && $msg === '') {
	$msg = '保存成功';
}

$plans = docker_shop_plan_list_all();
// 关联显示：节点名 / 配额套餐名 / 配额
$nodeMap = [];
foreach (docker_shop_node_list_all() as $n) {
	$nodeMap[(int)$n['id']] = $n;
}
$baseMap = [];
foreach ($DB->get_all_prepare("SELECT id, name, cpu_max, mem_max, disk_max, proxy_max FROM MN_docker_plan ORDER BY id ASC") ?: [] as $bp) {
	$baseMap[(int)$bp['id']] = $bp;
}

$title = $title ?? '售卖套餐管理';
mnbt_admin_include('head');
?>
<div class="container-fluid p-t-15">
	<div class="card">
		<div class="card-header">
			<h4 style="display:inline-block">售卖套餐管理</h4>
			<a class="btn btn-primary btn-sm float-right" href="<?= htmlspecialchars(docker_shop_admin_url('plan_edit'), ENT_QUOTES) ?>">
				<i class="mdi mdi-plus"></i> 新增售卖套餐
			</a>
		</div>
		<div class="card-body">
			<?php if (!empty($msg)): ?>
				<div class="alert alert-<?= htmlspecialchars($msg_type ?? 'danger', ENT_QUOTES) ?>"><?= htmlspecialchars($msg) ?></div>
			<?php endif; ?>

			<?php if (empty($plans)): ?>
				<p class="text-muted">还没有售卖套餐。Docker 售卖套餐需要先到「Docker → 套餐」创建配额套餐，再点击右上角"新增售卖套餐"绑定配额与节点、设置价格。</p>
			<?php else: ?>
				<div class="table-responsive">
					<table class="table table-bordered table-hover">
						<thead>
							<tr>
								<th>ID</th>
								<th>名称</th>
								<th>分类</th>
								<th>配额套餐</th>
								<th>配额</th>
								<th>节点</th>
								<th>价格（启用周期）</th>
								<th>状态</th>
								<th>排序</th>
								<th style="width:140px">操作</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($plans as $p):
								$bp = $baseMap[(int)$p['base_plan_id']] ?? null;
								$nd = $nodeMap[(int)$p['node']] ?? null;
								$periods = docker_shop_plan_enabled_periods($p);
								$prices = [];
								foreach ($periods as $pp) {
									$field = docker_shop_period_price_field($pp);
									$prices[] = docker_shop_periods()[$pp]['label'] . ' ¥' . docker_shop_format_cents((int)($p[$field] ?? 0));
								}
							?>
							<tr>
								<td><?= (int)$p['id'] ?></td>
								<td><?= htmlspecialchars($p['name'], ENT_QUOTES) ?></td>
								<td><?= htmlspecialchars($p['category'] ?: '—', ENT_QUOTES) ?></td>
								<td><?= $bp ? htmlspecialchars($bp['name'], ENT_QUOTES) : '<span class="text-danger">已失效</span>' ?></td>
								<td><?= $bp ? htmlspecialchars($bp['cpu_max'], ENT_QUOTES) . '核 / ' . htmlspecialchars($bp['mem_max'], ENT_QUOTES) . 'MB' . ((int)$bp['disk_max'] > 0 ? ' / ' . htmlspecialchars($bp['disk_max'], ENT_QUOTES) . 'MB' : '') : '—' ?></td>
								<td><?= $nd ? htmlspecialchars($nd['name'], ENT_QUOTES) : '<span class="text-danger">已失效</span>' ?></td>
								<td><?= $prices ? implode('<br>', array_map('htmlspecialchars', $prices)) : '<span class="text-muted">未设置</span>' ?></td>
								<td>
									<span class="badge <?= $p['status'] === 'active' ? 'badge-success' : 'badge-secondary' ?>"><?= $p['status'] === 'active' ? '上架' : '下架' ?></span>
								</td>
								<td><?= (int)$p['sort'] ?></td>
								<td>
									<a class="btn btn-primary btn-sm" href="<?= htmlspecialchars(docker_shop_admin_url('plan_edit', 'id=' . (int)$p['id']), ENT_QUOTES) ?>">编辑</a>
									<form method="post" style="display:inline-block" onsubmit="return confirm('确定删除该售卖套餐吗？');">
										<input type="hidden" name="act" value="delete">
										<input type="hidden" name="plan_id" value="<?= (int)$p['id'] ?>">
										<button type="submit" class="btn btn-danger btn-sm">删除</button>
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
<?php mnbt_admin_include('foot'); ?>
