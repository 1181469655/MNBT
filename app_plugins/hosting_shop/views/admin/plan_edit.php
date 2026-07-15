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
		'spec_type' => (int)($_POST['spec_type'] ?? 0),
		'spec_web' => (int)($_POST['spec_web'] ?? 0),
		'spec_sql' => (int)($_POST['spec_sql'] ?? 0),
		'spec_flow' => (int)($_POST['spec_flow'] ?? 0),
		'spec_domain' => (int)($_POST['spec_domain'] ?? 0),
		'price_month_cents' => (int)round((float)($_POST['price_month'] ?? 0) * 100),
		'price_year_cents' => (int)round((float)($_POST['price_year'] ?? 0) * 100),
		'status' => $_POST['status'] ?? 'active',
		'sort' => (int)($_POST['sort'] ?? 50),
	];
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
				<div class="form-group row">
					<label class="col-sm-3 col-form-label">产品类型</label>
					<div class="col-sm-9">
						<select name="spec_type" class="form-control">
							<option value="0" <?= ((int)($plan['spec_type'] ?? 0) === 0) ? 'selected' : '' ?>>虚拟主机（开通 FTP + 数据库）</option>
							<option value="1" <?= ((int)($plan['spec_type'] ?? 0) === 1) ? 'selected' : '' ?>>CDN（不开通 FTP/数据库）</option>
						</select>
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
				<div class="form-row">
					<div class="form-group col-md-4">
						<label>月付价格 (元)</label>
						<input type="number" name="price_month" class="form-control" step="0.01" min="0" value="<?= htmlspecialchars(isset($plan['price_month_cents']) ? hosting_format_cents($plan['price_month_cents']) : '0.00', ENT_QUOTES) ?>">
					</div>
					<div class="form-group col-md-4">
						<label>年付价格 (元)</label>
						<input type="number" name="price_year" class="form-control" step="0.01" min="0" value="<?= htmlspecialchars(isset($plan['price_year_cents']) ? hosting_format_cents($plan['price_year_cents']) : '0.00', ENT_QUOTES) ?>">
					</div>
					<div class="form-group col-md-2">
						<label>排序</label>
						<input type="number" name="sort" class="form-control" min="0" value="<?= (int)($plan['sort'] ?? 50) ?>">
					</div>
					<div class="form-group col-md-2">
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
