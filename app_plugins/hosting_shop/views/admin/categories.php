<?php
/**
 * 管理员端 - 套餐分类管理
 */
if (!defined('IN_CRONLITE')) {
	exit;
}

$act = $_POST['act'] ?? '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	if ($act === 'add') {
		$r = hosting_category_add($_POST['name'] ?? '');
		if ($r === true) {
			$msg = '分类已添加';
			$msg_type = 'success';
		} else {
			$msg = $r;
			$msg_type = 'danger';
		}
	} elseif ($act === 'delete') {
		$r = hosting_category_delete($_POST['name'] ?? '');
		if ($r === true) {
			$msg = '分类已删除';
			$msg_type = 'success';
		} else {
			$msg = $r;
			$msg_type = 'danger';
		}
	} elseif ($act === 'rename') {
		$r = hosting_category_rename($_POST['old_name'] ?? '', $_POST['new_name'] ?? '');
		if ($r === true) {
			$msg = '分类已重命名';
			$msg_type = 'success';
		} else {
			$msg = $r;
			$msg_type = 'danger';
		}
	}
}

$categories = hosting_category_list();
$counts = hosting_category_counts();
$title = $title ?? '套餐分类';
mnbt_admin_include('head');
?>
<div class="container-fluid p-t-15">
	<div class="card">
		<div class="card-header">
			<h4 style="display:inline-block">套餐管理</h4>
			<button type="button" class="btn btn-primary btn-sm float-right" data-toggle="modal" data-target="#addModal">
				<i class="mdi mdi-plus"></i> 添加分类
			</button>
		</div>
		<div class="card-header" style="padding-top:0;border-top:0;">
			<ul class="nav nav-tabs card-header-tabs">
				<li class="nav-item">
					<a class="nav-link" href="<?= htmlspecialchars(hosting_admin_url('plans'), ENT_QUOTES) ?>">套餐列表</a>
				</li>
				<li class="nav-item">
					<a class="nav-link active" href="<?= htmlspecialchars(hosting_admin_url('categories'), ENT_QUOTES) ?>">套餐分类</a>
				</li>
			</ul>
		</div>
		<div class="card-body">
			<?php if (!empty($msg)): ?>
				<div class="alert alert-<?= htmlspecialchars($msg_type ?? 'danger', ENT_QUOTES) ?>"><?= htmlspecialchars($msg) ?></div>
			<?php endif; ?>

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
											<input type="hidden" name="act" value="delete">
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

<!-- 添加分类模态框 -->
<div class="modal fade" id="addModal" tabindex="-1" role="dialog" aria-labelledby="addModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<form method="post" onsubmit="var b=this.querySelector('button[type=submit]');b.disabled=true;b.textContent='添加中...';">
			<input type="hidden" name="act" value="add">
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

<!-- 重命名模态框 -->
<div class="modal fade" id="renameModal" tabindex="-1" role="dialog" aria-labelledby="renameModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<form method="post" onsubmit="var b=this.querySelector('button[type=submit]');b.disabled=true;b.textContent='保存中...';">
			<input type="hidden" name="act" value="rename">
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
// 重命名弹窗：打开时填入当前分类名
$('#renameModal').on('show.bs.modal', function (event) {
	var button = $(event.relatedTarget);
	var name = button.data('name');
	var modal = $(this);
	modal.find('#renameOld').val(name);
	modal.find('#renameNew').val(name);
});
// 弹窗关闭后清空输入框
$('#renameModal').on('hidden.bs.modal', function () {
	$(this).find('#renameNew').val('');
});
$('#addModal').on('hidden.bs.modal', function () {
	$(this).find('input[name="name"]').val('');
});
</script>