<?php
/**
 * 管理员端 - 售卖套餐编辑/新增
 */
if (!defined('IN_CRONLITE')) {
	exit;
}

$id = (int)($_GET['id'] ?? 0);
$plan = $id > 0 ? docker_shop_plan_get($id) : null;

// 处理 POST 保存
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['act'] ?? '') === 'save') {
	$data = [
		'id' => $id,
		'name' => $_POST['name'] ?? '',
		'description' => $_POST['description'] ?? '',
		'category' => $_POST['category'] ?? '',
		'node' => (int)($_POST['node'] ?? 0),
		'base_plan_id' => (int)($_POST['base_plan_id'] ?? 0),
		'enabled_periods' => isset($_POST['enabled_periods']) && is_array($_POST['enabled_periods']) ? $_POST['enabled_periods'] : [],
		'status' => $_POST['status'] ?? 'active',
		'sort' => (int)($_POST['sort'] ?? 50),
	];
	foreach (docker_shop_periods() as $p => $cfg) {
		$field = docker_shop_period_price_field($p);
		$data[$field] = (int)round((float)($_POST['price'][$p] ?? 0) * 100);
	}
	$r = docker_shop_plan_save($data);
	if ($r === true) {
		header('Location: ' . docker_shop_admin_url('plans', 'saved=1'));
		exit;
	}
	$msg = $r;
	$msg_type = 'danger';
	// 保留用户输入
	$plan = $data;
}

$nodes = docker_shop_node_list_all();
$basePlans = docker_shop_base_plan_list();

$title = $title ?? ($plan && $id > 0 ? '编辑售卖套餐' : '新增售卖套餐');
mnbt_admin_include('head');
?>
<div class="container-fluid p-t-15">
	<div class="card">
		<div class="card-header">
			<h4 style="display:inline-block"><?= $id > 0 ? '编辑售卖套餐' : '新增售卖套餐' ?></h4>
			<a class="btn btn-secondary btn-sm float-right" href="<?= htmlspecialchars(docker_shop_admin_url('plans'), ENT_QUOTES) ?>">返回列表</a>
		</div>
		<div class="card-body">
			<?php if (!empty($msg)): ?>
				<div class="alert alert-<?= htmlspecialchars($msg_type ?? 'danger', ENT_QUOTES) ?>"><?= htmlspecialchars($msg) ?></div>
			<?php endif; ?>

			<?php if (empty($nodes)): ?>
				<div class="alert alert-warning">还没有可用 Docker 节点，请先到「Docker → 节点管理」添加并启用节点。</div>
			<?php endif; ?>
			<?php if (empty($basePlans)): ?>
				<div class="alert alert-warning">还没有配额套餐，请先到「Docker → 套餐」创建配额套餐（CPU/内存/磁盘限制）。</div>
			<?php endif; ?>

			<form method="post" onsubmit="var b=this.querySelector('button[type=submit]');b.disabled=true;b.textContent='保存中...';">
				<input type="hidden" name="act" value="save">
				<div class="form-group row">
					<label class="col-sm-3 col-form-label">套餐名称 <span class="text-danger">*</span></label>
					<div class="col-sm-9">
						<input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($plan['name'] ?? '', ENT_QUOTES) ?>">
					</div>
				</div>
				<div class="form-group row">
					<label class="col-sm-3 col-form-label">套餐介绍</label>
					<div class="col-sm-9">
						<textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($plan['description'] ?? '', ENT_QUOTES) ?></textarea>
					</div>
				</div>
				<div class="form-row">
					<div class="form-group col-md-4">
						<label>分类</label>
						<input type="text" name="category" class="form-control" value="<?= htmlspecialchars($plan['category'] ?? '', ENT_QUOTES) ?>" placeholder="如：入门 / 进阶">
					</div>
					<div class="form-group col-md-4">
						<label>排序</label>
						<input type="number" name="sort" class="form-control" min="0" value="<?= (int)($plan['sort'] ?? 50) ?>">
					</div>
					<div class="form-group col-md-4">
						<label>上架</label>
						<select name="status" class="form-control">
							<option value="active" <?= (($plan['status'] ?? 'active') === 'active') ? 'selected' : '' ?>>上架</option>
							<option value="inactive" <?= (($plan['status'] ?? '') === 'inactive') ? 'selected' : '' ?>>下架</option>
						</select>
					</div>
				</div>
				<div class="form-row">
					<div class="form-group col-md-6">
						<label>绑定配额套餐 <span class="text-danger">*</span></label>
						<select name="base_plan_id" class="form-control" required>
							<option value="">请选择配额套餐</option>
							<?php foreach ($basePlans as $bp): ?>
								<option value="<?= (int)$bp['id'] ?>" <?= ((int)($plan['base_plan_id'] ?? 0) === (int)$bp['id']) ? 'selected' : '' ?>>
									<?= htmlspecialchars($bp['name'], ENT_QUOTES) ?> (<?= htmlspecialchars($bp['cpu_max'], ENT_QUOTES) ?>核/<?= htmlspecialchars($bp['mem_max'], ENT_QUOTES) ?>MB<?= (int)$bp['disk_max'] > 0 ? '/' . htmlspecialchars($bp['disk_max'], ENT_QUOTES) . 'MB' : '' ?>)
								</option>
							<?php endforeach; ?>
						</select>
						<small class="form-text text-muted">用户在 Docker 控制台创建的容器将受该套餐 CPU/内存/磁盘配额限制。</small>
					</div>
					<div class="form-group col-md-6">
						<label>固定开通节点 <span class="text-danger">*</span></label>
						<select name="node" class="form-control" required>
							<option value="">请选择 Docker 节点</option>
							<?php foreach ($nodes as $n): ?>
								<option value="<?= (int)$n['id'] ?>" <?= ((int)($plan['node'] ?? 0) === (int)$n['id']) ? 'selected' : '' ?>><?= htmlspecialchars($n['name'], ENT_QUOTES) ?> (ID:<?= (int)$n['id'] ?>)</option>
							<?php endforeach; ?>
						</select>
						<small class="form-text text-muted">购买后 Docker 账号开通在该节点上。</small>
					</div>
				</div>
				<div class="form-group row">
					<label class="col-sm-3 col-form-label">购买周期与价格 (元)</label>
					<div class="col-sm-9">
						<div class="form-row">
							<?php
								$enabledPeriods = docker_shop_plan_enabled_periods($plan ?: []);
								foreach (docker_shop_periods() as $p => $cfg):
									$field = docker_shop_period_price_field($p);
									$checked = in_array($p, $enabledPeriods, true) ? 'checked' : '';
									$price = isset($plan[$field]) ? docker_shop_format_cents((int)$plan[$field]) : '0.00';
							?>
							<div class="form-group col-md-4">
								<label class="form-check">
									<input type="checkbox" name="enabled_periods[]" value="<?= htmlspecialchars($p, ENT_QUOTES) ?>" class="form-check-input" <?= $checked ?>>
									<span class="form-check-label"><?= htmlspecialchars($cfg['label'], ENT_QUOTES) ?></span>
								</label>
								<div class="input-group">
									<div class="input-group-prepend"><span class="input-group-text">¥</span></div>
									<input type="number" name="price[<?= htmlspecialchars($p, ENT_QUOTES) ?>]" class="form-control" step="0.01" min="0" value="<?= htmlspecialchars($price, ENT_QUOTES) ?>">
								</div>
							</div>
							<?php endforeach; ?>
						</div>
						<small class="form-text text-muted">勾选即启用该周期；价格为 0 表示该周期免费（下单后立即开通）。</small>
					</div>
				</div>
				<div class="form-group row">
					<div class="col-sm-9 offset-sm-3">
						<button type="submit" class="btn btn-primary">保存</button>
						<a class="btn btn-secondary" href="<?= htmlspecialchars(docker_shop_admin_url('plans'), ENT_QUOTES) ?>">取消</a>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>
<?php mnbt_admin_include('foot'); ?>
