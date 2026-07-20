<?php
/**
 * 管理员端 - 套餐编辑/新增
 */
if (!defined('IN_CRONLITE')) {
	exit;
}

$id = (int)($_GET['id'] ?? 0);
$plan = $id > 0 ? hosting_plan_get($id) : null;

// 处理 POST 保存
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['act'] ?? '') === 'save') {
	$data = [
		'id' => $id,
		'name' => $_POST['name'] ?? '',
		'description' => $_POST['description'] ?? '',
		'spec_web' => (int)($_POST['spec_web'] ?? 0),
		'spec_sql' => (int)($_POST['spec_sql'] ?? 0),
		'spec_flow' => (int)($_POST['spec_flow'] ?? 0),
		'spec_domain' => (int)($_POST['spec_domain'] ?? 0),
		'enabled_periods' => isset($_POST['enabled_periods']) && is_array($_POST['enabled_periods']) ? $_POST['enabled_periods'] : [],
		'status' => $_POST['status'] ?? 'active',
		'sort' => (int)($_POST['sort'] ?? 50),
	];
	foreach (hosting_periods() as $p => $cfg) {
		$field = hosting_period_price_field($p);
		$data[$field] = (int)round((float)($_POST['price'][$p] ?? 0) * 100);
	}
	$r = hosting_plan_save($data);
	if ($r === true) {
		header('Location: ' . hosting_admin_url('plans'));
		exit;
	}
	$msg = $r;
	$msg_type = 'danger';
	// 保留用户输入
	$plan = $data;
}

$title = $title ?? ($plan && $id > 0 ? '编辑套餐' : '新增套餐');
mnbt_admin_include('head');
?>
<div class="container-fluid p-t-15">
	<div class="card">
		<div class="card-header">
			<h4 style="display:inline-block"><?= $id > 0 ? '编辑套餐' : '新增套餐' ?></h4>
			<a class="btn btn-secondary btn-sm float-right" href="<?= htmlspecialchars(hosting_admin_url('plans'), ENT_QUOTES) ?>">返回列表</a>
		</div>
		<div class="card-body">
			<?php if (!empty($msg)): ?>
				<div class="alert alert-<?= htmlspecialchars($msg_type ?? 'danger', ENT_QUOTES) ?>"><?= htmlspecialchars($msg) ?></div>
			<?php endif; ?>

			<form method="post">
				<input type="hidden" name="act" value="save">
				<div class="form-group row">
					<label class="col-sm-3 col-form-label">套餐名称</label>
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
				<div class="form-group col-md-3">
					<label>网页空间 (MB)</label>
					<input type="number" name="spec_web" class="form-control" required min="0" value="<?= (int)($plan['spec_web'] ?? 1024) ?>">
				</div>
				<div class="form-group col-md-3">
					<label>数据库空间 (MB)</label>
					<input type="number" name="spec_sql" class="form-control" required min="0" value="<?= (int)($plan['spec_sql'] ?? 256) ?>">
				</div>
				<div class="form-group col-md-3">
					<label>流量 (GB，0=不限)</label>
					<input type="number" name="spec_flow" class="form-control" required min="0" value="<?= (int)($plan['spec_flow'] ?? 0) ?>">
				</div>
				<div class="form-group col-md-3">
					<label>域名绑定数</label>
					<input type="number" name="spec_domain" class="form-control" required min="0" value="<?= (int)($plan['spec_domain'] ?? 5) ?>">
				</div>
			</div>
			<div class="form-group row">
				<label class="col-sm-3 col-form-label">购买周期与价格 (元)</label>
				<div class="col-sm-9">
					<div class="form-row">
						<?php
							$enabledPeriods = hosting_plan_enabled_periods($plan ?: []);
							foreach (hosting_periods() as $p => $cfg):
								$field = hosting_period_price_field($p);
								$checked = in_array($p, $enabledPeriods, true) ? 'checked' : '';
								$price = isset($plan[$field]) ? hosting_format_cents((int)$plan[$field]) : '0.00';
						?>
							<div class="form-group col-md-4">
								<div class="input-group">
									<div class="input-group-prepend">
										<div class="input-group-text">
											<input type="checkbox" name="enabled_periods[]" value="<?= htmlspecialchars($p, ENT_QUOTES) ?>" <?= $checked ?>>
										</div>
									</div>
									<span class="input-group-text" style="min-width:60px;justify-content:center;"><?= htmlspecialchars($cfg['label']) ?></span>
									<input type="number" name="price[<?= htmlspecialchars($p, ENT_QUOTES) ?>]" class="form-control" step="0.01" min="0" value="<?= htmlspecialchars($price, ENT_QUOTES) ?>">
								</div>
							</div>
						<?php endforeach; ?>
					</div>
					<small class="form-text text-muted">勾选并填写价格即启用该周期；价格填 0 表示免费。</small>
				</div>
			</div>
				<div class="form-row">
					<div class="form-group col-md-6">
						<label>排序</label>
						<input type="number" name="sort" class="form-control" min="0" value="<?= (int)($plan['sort'] ?? 50) ?>">
					</div>
					<div class="form-group col-md-6">
						<label>状态</label>
						<select name="status" class="form-control">
							<option value="active" <?= (($plan['status'] ?? 'active') === 'active') ? 'selected' : '' ?>>上架</option>
							<option value="inactive" <?= (($plan['status'] ?? '') === 'inactive') ? 'selected' : '' ?>>下架</option>
						</select>
					</div>
				</div>
				<div class="form-group row">
					<div class="col-sm-9 offset-sm-3">
						<button type="submit" class="btn btn-primary">保存</button>
						<a class="btn btn-secondary" href="<?= htmlspecialchars(hosting_admin_url('plans'), ENT_QUOTES) ?>">取消</a>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>
