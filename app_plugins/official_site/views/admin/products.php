<?php
/**
 * 管理员端 - 官网产品管理（列表 + 添加/编辑/删除）
 */
if (!defined('IN_CRONLITE')) {
	exit;
}

// —— 处理 POST（成功后 redirect 到带提示参数的地址，避免刷新重复提交）——
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$act = $_POST['act'] ?? '';

	if ($act === 'save') {
		$features = [];
		foreach (preg_split('/[\r\n]+/', (string)($_POST['features'] ?? '')) as $line) {
			$line = trim($line);
			if ($line !== '') {
				$features[] = $line;
			}
		}
		$data = [
			'id'          => (int)($_POST['id'] ?? 0),
			'name'        => $_POST['name'] ?? '',
			'category'    => $_POST['category'] ?? '',
			'description' => $_POST['description'] ?? '',
			'features'    => $features,
			'image'       => $_POST['image'] ?? '',
			'link'        => $_POST['link'] ?? '',
			'status'      => $_POST['status'] ?? 'active',
			'sort'        => (int)($_POST['sort'] ?? 50),
		];
		$r = site_product_save($data);
		if ($r === true) {
			header('Location: ' . site_admin_url('site_products', 'saved=1'));
			exit;
		}
		$msg = $r;
		$msg_type = 'danger';
		$form = $data;
		$show_modal = true;
	} elseif ($act === 'delete') {
		$id = (int)($_POST['id'] ?? 0);
		$ok = $id > 0 && site_product_delete($id);
		header('Location: ' . site_admin_url('site_products', ($ok ? 'saved=1&msg=' . urlencode('产品已删除') : 'msg=' . urlencode('删除失败'))));
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

$products = site_product_list();
$categories = site_product_categories();
$title = $title ?? '产品管理';
mnbt_admin_include('head');
?>
<div class="container-fluid p-t-15">
	<div class="card">
		<div class="card-header">
			<h4 style="display:inline-block">产品管理</h4>
			<span class="float-right">
				<button type="button" class="btn btn-primary btn-sm" onclick="openEdit(0)">
					<i class="mdi mdi-plus"></i> 添加产品
				</button>
			</span>
		</div>
		<div class="card-body">
			<?php if (!empty($msg)): ?>
				<div class="alert alert-<?= htmlspecialchars($msg_type ?? 'danger', ENT_QUOTES) ?>"><?= htmlspecialchars($msg) ?></div>
			<?php endif; ?>

			<?php if (empty($products)): ?>
				<p class="text-muted">还没有产品，点击右上角"添加产品"创建第一个产品。</p>
			<?php else: ?>
				<div class="table-responsive">
					<table class="table table-bordered table-hover">
						<thead>
							<tr>
								<th style="width:60px">ID</th>
								<th>产品名称</th>
								<th style="width:110px">分类</th>
								<th>简介</th>
								<th style="width:130px">跳转链接</th>
								<th style="width:80px">状态</th>
								<th style="width:170px">操作</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($products as $p): ?>
								<tr>
									<td><?= (int)$p['id'] ?></td>
									<td><?= htmlspecialchars($p['name'], ENT_QUOTES) ?></td>
									<td><?= htmlspecialchars($categories[$p['category']] ?? $p['category'], ENT_QUOTES) ?></td>
									<td><?= htmlspecialchars(mb_substr($p['description'], 0, 40), ENT_QUOTES) ?><?= mb_strlen($p['description']) > 40 ? '…' : '' ?></td>
								<td>
									<?php if (!empty($p['link'])): ?>
										<a href="<?= htmlspecialchars($p['link'], ENT_QUOTES) ?>" target="_blank" rel="noopener" class="text-primary"><?= htmlspecialchars(mb_substr($p['link'], 0, 22), ENT_QUOTES) ?><?= mb_strlen($p['link']) > 22 ? '…' : '' ?></a>
									<?php else: ?>
										<span class="text-muted">—</span>
									<?php endif; ?>
								</td>
								<td>
									<?php if ($p['status'] === 'active'): ?>
										<span class="badge badge-success">上架</span>
									<?php else: ?>
										<span class="badge badge-secondary">下架</span>
									<?php endif; ?>
								</td>
									<td>
										<button type="button" class="btn btn-sm btn-outline-primary" onclick="openEdit(<?= (int)$p['id'] ?>)">编辑</button>
										<form method="post" style="display:inline-block" onsubmit="var b=this.querySelector('button');b.disabled=true;b.textContent='删除中...';return confirm('确定删除此产品？')">
											<input type="hidden" name="act" value="delete">
											<input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
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

<!-- 添加/编辑模态框 -->
<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<form method="post">
				<input type="hidden" name="act" value="save">
				<input type="hidden" name="id" id="f-id" value="0">
				<div class="modal-header">
					<h5 class="modal-title" id="modalTitle">添加产品</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				</div>
				<div class="modal-body">
					<div class="form-group">
						<label>产品名称</label>
						<input type="text" class="form-control" name="name" id="f-name" required>
					</div>
					<div class="form-group">
						<label>产品分类</label>
						<select class="form-control" name="category" id="f-category">
							<?php foreach ($categories as $k => $v): ?>
								<option value="<?= htmlspecialchars($k, ENT_QUOTES) ?>"><?= htmlspecialchars($v) ?></option>
							<?php endforeach; ?>
						</select>
					</div>
					<div class="form-group">
						<label>产品简介</label>
						<textarea class="form-control" name="description" id="f-description" rows="3" required></textarea>
					</div>
					<div class="form-group">
						<label>产品特性（每行一条）</label>
						<textarea class="form-control" name="features" id="f-features" rows="4" placeholder="高性能架构设计，确保系统稳定运行&#10;易于集成和部署，降低实施成本"></textarea>
					</div>
					<div class="form-group">
						<label>展示图片（URL，可选）</label>
						<input type="text" class="form-control" name="image" id="f-image" placeholder="https://example.com/image.png">
					</div>
					<div class="form-group">
						<label>跳转链接（可选）</label>
						<input type="text" class="form-control" name="link" id="f-link" placeholder="/shop 或 https://example.com/product">
						<small class="form-text text-muted">配置后，产品中心卡片点击将直接跳转到该链接（站内路径或 http(s) 外链）；留空则进入产品详情页。</small>
					</div>
					<div class="form-row">
						<div class="col-6">
							<div class="form-group">
								<label>状态</label>
								<select class="form-control" name="status" id="f-status">
									<option value="active">上架</option>
									<option value="inactive">下架</option>
								</select>
							</div>
						</div>
						<div class="col-6">
							<div class="form-group">
								<label>排序（小到大）</label>
								<input type="number" class="form-control" name="sort" id="f-sort" value="50" min="0">
							</div>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">取消</button>
					<button type="submit" class="btn btn-primary">保存</button>
				</div>
			</form>
		</div>
	</div>
</div>

<script>
var __siteProducts = <?= json_encode($products, JSON_UNESCAPED_UNICODE) ?>;
<?php if (!empty($show_modal)): ?>
var __siteForm = <?= json_encode($form, JSON_UNESCAPED_UNICODE) ?>;
<?php endif; ?>
function openEdit(id) {
	var el = function (k) { return document.getElementById('f-' + k); };
	document.getElementById('modalTitle').textContent = id ? '编辑产品' : '添加产品';
	el('id').value = id;
	if (id > 0) {
		var p = __siteProducts.find(function (x) { return parseInt(x.id, 10) === parseInt(id, 10); });
		if (p) {
			el('name').value = p.name || '';
			el('category').value = p.category || 'hosting';
			el('description').value = p.description || '';
			el('features').value = (p.features_list || []).join('\n');
			el('image').value = p.image || '';
			el('link').value = p.link || '';
			el('status').value = p.status || 'active';
			el('sort').value = p.sort || 50;
		}
	} else {
		el('name').value = '';
		el('category').value = 'hosting';
		el('description').value = '';
		el('features').value = '';
		el('image').value = '';
		el('link').value = '';
		el('status').value = 'active';
		el('sort').value = 50;
	}
	<?php if (!empty($show_modal)): ?>
	// 提交失败回显：用服务端返回的表单数据覆盖字段
	if (typeof __siteForm !== 'undefined') {
		el('name').value = __siteForm.name || '';
		el('category').value = __siteForm.category || 'hosting';
		el('description').value = __siteForm.description || '';
		el('features').value = (__siteForm.features || []).join('\n');
		el('image').value = __siteForm.image || '';
		el('link').value = __siteForm.link || '';
		el('status').value = __siteForm.status || 'active';
		el('sort').value = __siteForm.sort || 50;
	}
	<?php endif; ?>
	$('#editModal').modal('show');
}
<?php if (!empty($show_modal)): ?>
	$(function () { openEdit(<?= (int)($form['id'] ?? 0) ?>); });
<?php endif; ?>
</script>
<?php mnbt_admin_include('foot'); ?>
