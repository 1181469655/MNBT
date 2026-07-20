<?php
/**
 * 管理员端 - 套餐管理（列表 + 分类，Tab 切换）
 */
if (!defined('IN_CRONLITE')) {
	exit;
}

// —— 处理 POST（成功后 redirect 到带提示参数的地址，避免刷新重复提交）——
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$act = $_POST['act'] ?? '';

	// 套餐相关
	if ($act === 'save') {
		$data = [
			'id' => 0,
			'name' => $_POST['name'] ?? '',
			'description' => $_POST['description'] ?? '',
			'category' => $_POST['category'] ?? '',
			'node' => $_POST['node'] ?? '',
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
			header('Location: ' . hosting_admin_url('plans', 'saved=1'));
			exit;
		}
		$msg = $r;
		$msg_type = 'danger';
		$plan_form = $data;
		$show_modal = true;
	} elseif ($act === 'delete_plan') {
		$plan_id = (int)($_POST['plan_id'] ?? 0);
		$ok = $plan_id > 0 && hosting_plan_delete($plan_id);
		header('Location: ' . hosting_admin_url('plans', ($ok ? 'saved=1&msg=' . urlencode('套餐已删除') : 'msg=' . urlencode('删除失败'))));
		exit;
	}

	// 分类相关
	if ($act === 'cat_add') {
		$r = hosting_category_add($_POST['name'] ?? '');
		$ok = ($r === true);
		header('Location: ' . hosting_admin_url('plans', 'tab=categories&' . ($ok ? 'saved=1&msg=' . urlencode('分类已添加') : 'msg=' . urlencode($r))));
		exit;
	} elseif ($act === 'cat_delete') {
		$r = hosting_category_delete($_POST['name'] ?? '');
		$ok = ($r === true);
		header('Location: ' . hosting_admin_url('plans', 'tab=categories&' . ($ok ? 'saved=1&msg=' . urlencode('分类已删除') : 'msg=' . urlencode($r))));
		exit;
	} elseif ($act === 'cat_rename') {
		$r = hosting_category_rename($_POST['old_name'] ?? '', $_POST['new_name'] ?? '');
		$ok = ($r === true);
		header('Location: ' . hosting_admin_url('plans', 'tab=categories&' . ($ok ? 'saved=1&msg=' . urlencode('分类已重命名') : 'msg=' . urlencode($r))));
		exit;
	}
}

// —— GET 参数处理（redirect 后的提示）——
$saved = isset($_GET['saved']);
$msg = $_GET['msg'] ?? ($msg ?? '');
$msg_type = $saved ? 'success' : ($msg_type ?? 'danger');
if ($saved && $msg === '') {
	$msg = '保存成功';
}
$active_tab = $_GET['tab'] ?? ($active_tab ?? 'plans');

$plans = hosting_plan_list_all();
$categories = hosting_category_list();
$counts = hosting_category_counts();
$title = $title ?? '套餐管理';
mnbt_admin_include('head');
?>
<div class="container-fluid p-t-15">
	<div class="card">
		<div class="card-header">
			<h4 style="display:inline-block">套餐管理</h4>
			<span class="float-right" id="tabBtns">
				<button type="button" class="btn btn-primary btn-sm" id="btnAddPlan" data-toggle="modal" data-target="#addPlanModal">
					<i class="mdi mdi-plus"></i> 新增套餐
				</button>
				<button type="button" class="btn btn-primary btn-sm" id="btnAddCat" data-toggle="modal" data-target="#addModal" style="display:none;">
					<i class="mdi mdi-plus"></i> 添加分类
				</button>
			</span>
		</div>
		<div class="card-header" style="padding-top:0;border-top:0;">
			<ul class="nav nav-tabs card-header-tabs" id="mainTabs">
				<li class="nav-item">
					<a class="nav-link<?= $active_tab === 'plans' ? ' active' : '' ?>" href="#" data-tab="plans">套餐列表</a>
				</li>
				<li class="nav-item">
					<a class="nav-link<?= $active_tab === 'categories' ? ' active' : '' ?>" href="#" data-tab="categories">套餐分类</a>
				</li>
			</ul>
		</div>
		<div class="card-body">
			<?php if (!empty($msg)): ?>
				<div class="alert alert-<?= htmlspecialchars($msg_type ?? 'danger', ENT_QUOTES) ?>"><?= htmlspecialchars($msg) ?></div>
			<?php endif; ?>

			<!-- ====== Tab：套餐列表 ====== -->
			<div class="tab-panel" id="tab-plans"<?= $active_tab !== 'plans' ? ' style="display:none;"' : '' ?>>
				<?php if (empty($plans)): ?>
					<p class="text-muted">还没有套餐，点击右上角"新增套餐"创建第一个套餐。</p>
				<?php else: ?>
					<div class="table-responsive">
						<table class="table table-bordered table-hover">
							<thead>
								<tr>
									<th>ID</th>
									<th>名称</th>
									<th>分类</th>
									<th>节点</th>
									<th>网页/数据库/流量</th>
									<th>域名数</th>
									<th>价格（启用周期）</th>
									<th>状态</th>
									<th>排序</th>
									<th style="width:180px">操作</th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ($plans as $p): ?>
									<?php $enabled = hosting_plan_enabled_periods($p); ?>
									<tr>
										<td><?= (int)$p['id'] ?></td>
										<td><?= htmlspecialchars($p['name'], ENT_QUOTES) ?></td>
										<td><?= htmlspecialchars($p['category'] ?: '—', ENT_QUOTES) ?></td>
										<td><?= htmlspecialchars($p['node'] ?: '—', ENT_QUOTES) ?></td>
										<td><?= (int)$p['spec_web'] ?>MB / <?= (int)$p['spec_sql'] ?>MB / <?= (int)$p['spec_flow'] > 0 ? ((int)$p['spec_flow'] . 'GB') : '不限' ?></td>
										<td><?= (int)$p['spec_domain'] ?></td>
										<td>
											<?php foreach ($enabled as $periodKey): ?>
												<?php $cfg = hosting_periods()[$periodKey]; $field = hosting_period_price_field($periodKey); $price = (int)($p[$field] ?? 0); ?>
												<span class="badge badge-light" style="margin-right:4px;"><?= htmlspecialchars($cfg['label']) ?> ¥<?= htmlspecialchars(hosting_format_cents($price)) ?></span>
											<?php endforeach; ?>
											<?php if ($enabled === []): ?>
												<span class="text-muted">未启用周期</span>
											<?php endif; ?>
										</td>
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
											<form method="post" style="display:inline-block" onsubmit="var b=this.querySelector('button');b.disabled=true;b.textContent='删除中...';return confirm('确定删除此套餐？已有订单不受影响。')">
												<input type="hidden" name="act" value="delete_plan">
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

			<!-- ====== Tab：套餐分类 ====== -->
			<div class="tab-panel" id="tab-categories"<?= $active_tab !== 'categories' ? ' style="display:none;"' : '' ?>>
				<?php if (empty($categories)): ?>
					<p class="text-muted">还没有分类，点击右上角"添加分类"创建第一个分类。</p>
				<?php else: ?>
					<div class="table-responsive">
						<table class="table table-bordered table-hover">
							<thead>
								<tr>
									<th>分类名称</th>
									<th>套餐数量</th>
									<th style="width:200px">操作</th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ($categories as $cat): ?>
									<tr>
										<td><?= htmlspecialchars($cat, ENT_QUOTES) ?></td>
										<td><?= (int)($counts[$cat] ?? 0) ?></td>
										<td>
											<button type="button" class="btn btn-sm btn-outline-primary" data-toggle="modal" data-target="#renameModal" data-name="<?= htmlspecialchars($cat, ENT_QUOTES) ?>">重命名</button>
											<form method="post" style="display:inline-block" onsubmit="var b=this.querySelector('button');b.disabled=true;b.textContent='删除中...';return confirm('确定删除此分类？关联套餐的分类将被清空。')">
												<input type="hidden" name="act" value="cat_delete">
												<input type="hidden" name="name" value="<?= htmlspecialchars($cat, ENT_QUOTES) ?>">
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
</div>

<!-- ====== 新增套餐模态框 ====== -->
<div class="modal fade" id="addPlanModal" tabindex="-1" role="dialog" aria-labelledby="addPlanModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<form method="post" onsubmit="var b=this.querySelector('button[type=submit]');b.disabled=true;b.textContent='保存中...';">
			<input type="hidden" name="act" value="save">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="addPlanModalLabel">新增套餐</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<div class="form-group row">
						<label class="col-sm-3 col-form-label">套餐名称 <span class="text-danger">*</span></label>
						<div class="col-sm-9">
							<input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($plan_form['name'] ?? '', ENT_QUOTES) ?>">
						</div>
					</div>
					<div class="form-group row">
						<label class="col-sm-3 col-form-label">套餐介绍</label>
						<div class="col-sm-9">
							<textarea name="description" class="form-control" rows="2"><?= htmlspecialchars($plan_form['description'] ?? '', ENT_QUOTES) ?></textarea>
						</div>
					</div>
					<div class="form-row">
						<div class="form-group col-md-6">
							<label>套餐分类</label>
							<select name="category" class="form-control">
								<option value="">未分类</option>
								<?php foreach (hosting_category_list() as $cat): ?>
									<option value="<?= htmlspecialchars($cat, ENT_QUOTES) ?>" <?= (($plan_form['category'] ?? '') === $cat) ? 'selected' : '' ?>><?= htmlspecialchars($cat, ENT_QUOTES) ?></option>
								<?php endforeach; ?>
							</select>
						</div>
						<div class="form-group col-md-6">
							<label>固定开通节点 <span class="text-danger">*</span></label>
							<select name="node" class="form-control" required>
								<option value="">请选择宝塔节点</option>
								<?php foreach (hosting_node_list_all() as $n): ?>
									<option value="<?= htmlspecialchars($n['btdh'], ENT_QUOTES) ?>" <?= (($plan_form['node'] ?? '') === $n['btdh']) ? 'selected' : '' ?>><?= htmlspecialchars($n['btdh'], ENT_QUOTES) ?> (<?= htmlspecialchars($n['btip'], ENT_QUOTES) ?>)</option>
								<?php endforeach; ?>
							</select>
						</div>
					</div>
					<div class="form-row">
						<div class="form-group col-md-3">
							<label>网页空间 (MB)</label>
							<input type="number" name="spec_web" class="form-control" required min="0" value="<?= (int)($plan_form['spec_web'] ?? 1024) ?>">
						</div>
						<div class="form-group col-md-3">
							<label>数据库空间 (MB)</label>
							<input type="number" name="spec_sql" class="form-control" required min="0" value="<?= (int)($plan_form['spec_sql'] ?? 256) ?>">
						</div>
						<div class="form-group col-md-3">
							<label>流量 (GB，0=不限)</label>
							<input type="number" name="spec_flow" class="form-control" required min="0" value="<?= (int)($plan_form['spec_flow'] ?? 0) ?>">
						</div>
						<div class="form-group col-md-3">
							<label>域名绑定数</label>
							<input type="number" name="spec_domain" class="form-control" required min="0" value="<?= (int)($plan_form['spec_domain'] ?? 5) ?>">
						</div>
					</div>
					<div class="form-group row">
						<label class="col-sm-3 col-form-label">购买周期与价格 (元)</label>
						<div class="col-sm-9">
							<div class="form-row">
								<?php
									$form_enabled = $plan_form['enabled_periods'] ?? [];
									foreach (hosting_periods() as $p => $cfg):
										$checked = in_array($p, $form_enabled, true) ? 'checked' : '';
										$f_price = '0.00';
										if (isset($plan_form)) {
											$pf = hosting_period_price_field($p);
											$f_price = isset($plan_form[$pf]) ? hosting_format_cents((int)$plan_form[$pf]) : '0.00';
										}
								?>
									<div class="form-group col-md-4">
										<div class="input-group">
											<div class="input-group-prepend">
												<div class="input-group-text">
													<input type="checkbox" name="enabled_periods[]" value="<?= htmlspecialchars($p, ENT_QUOTES) ?>" <?= $checked ?>>
												</div>
											</div>
											<span class="input-group-text" style="min-width:60px;justify-content:center;"><?= htmlspecialchars($cfg['label']) ?></span>
											<input type="number" name="price[<?= htmlspecialchars($p, ENT_QUOTES) ?>]" class="form-control" step="0.01" min="0" value="<?= htmlspecialchars($f_price, ENT_QUOTES) ?>">
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
							<input type="number" name="sort" class="form-control" min="0" value="<?= (int)($plan_form['sort'] ?? 50) ?>">
						</div>
						<div class="form-group col-md-6">
							<label>状态</label>
							<select name="status" class="form-control">
								<option value="active" <?= (($plan_form['status'] ?? 'active') === 'active') ? 'selected' : '' ?>>上架</option>
								<option value="inactive" <?= (($plan_form['status'] ?? '') === 'inactive') ? 'selected' : '' ?>>下架</option>
							</select>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">取消</button>
					<button type="submit" class="btn btn-primary">保存</button>
				</div>
			</div>
		</form>
	</div>
</div>

<!-- ====== 添加分类模态框 ====== -->
<div class="modal fade" id="addModal" tabindex="-1" role="dialog" aria-labelledby="addModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<form method="post" onsubmit="var b=this.querySelector('button[type=submit]');b.disabled=true;b.textContent='添加中...';">
			<input type="hidden" name="act" value="cat_add">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="addModalLabel">添加分类</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<div class="form-group">
						<label>分类名称</label>
						<input type="text" name="name" class="form-control" required placeholder="如：入门型" autofocus>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">取消</button>
					<button type="submit" class="btn btn-primary">添加</button>
				</div>
			</div>
		</form>
	</div>
</div>

<!-- ====== 重命名分类模态框 ====== -->
<div class="modal fade" id="renameModal" tabindex="-1" role="dialog" aria-labelledby="renameModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<form method="post" onsubmit="var b=this.querySelector('button[type=submit]');b.disabled=true;b.textContent='保存中...';">
			<input type="hidden" name="act" value="cat_rename">
			<input type="hidden" name="old_name" id="renameOld">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="renameModalLabel">重命名分类</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<div class="form-group">
						<label>新名称</label>
						<input type="text" name="new_name" class="form-control" required id="renameNew">
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">取消</button>
					<button type="submit" class="btn btn-primary">保存</button>
				</div>
			</div>
		</form>
	</div>
</div>

<script>
// Tab 切换
(function(){
	var tabs = document.querySelectorAll('#mainTabs .nav-link');
	var tabPanels = {
		plans: document.getElementById('tab-plans'),
		categories: document.getElementById('tab-categories')
	};
	var btnAddPlan = document.getElementById('btnAddPlan');
	var btnAddCat = document.getElementById('btnAddCat');

	function switchTab(tab) {
		// 更新 nav 激活态
		tabs.forEach(function(t){ t.classList.remove('active'); });
		var target = document.querySelector('#mainTabs .nav-link[data-tab="' + tab + '"]');
		if (target) target.classList.add('active');
		// 显示/隐藏面板
		Object.keys(tabPanels).forEach(function(k){ tabPanels[k].style.display = (k === tab) ? '' : 'none'; });
		// 切换右上角按钮
		if (tab === 'plans') { btnAddPlan.style.display = ''; btnAddCat.style.display = 'none'; }
		else { btnAddPlan.style.display = 'none'; btnAddCat.style.display = ''; }
	}

	tabs.forEach(function(t){
		t.addEventListener('click', function(e){
			e.preventDefault();
			switchTab(t.getAttribute('data-tab'));
		});
	});

	// 初始化激活态
	var activeTab = '<?= $active_tab ?>';
	switchTab(activeTab);
})();

// 重命名弹窗：打开时填入当前分类名
$('#renameModal').on('show.bs.modal', function (event) {
	var button = $(event.relatedTarget);
	var name = button.data('name');
	var modal = $(this);
	modal.find('#renameOld').val(name);
	modal.find('#renameNew').val(name);
});
$('#renameModal').on('hidden.bs.modal', function () {
	$(this).find('#renameNew').val('');
});
$('#addModal').on('hidden.bs.modal', function () {
	$(this).find('input[name="name"]').val('');
});

<?php if (!empty($show_modal)): ?>
$(function(){ $('#addPlanModal').modal('show'); });
<?php endif; ?>
</script>