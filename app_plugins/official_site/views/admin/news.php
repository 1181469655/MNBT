<?php
/**
 * 管理员端 - 官网新闻管理（列表分页 + 添加/编辑/删除）
 */
if (!defined('IN_CRONLITE')) {
	exit;
}

// —— 处理 POST（成功后 redirect 到带提示参数的地址，避免刷新重复提交）——
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$act = $_POST['act'] ?? '';

	if ($act === 'save') {
		$data = [
			'id'       => (int)($_POST['id'] ?? 0),
			'title'    => $_POST['title'] ?? '',
			'category' => $_POST['category'] ?? '',
			'content'  => $_POST['content'] ?? '',
			'status'   => $_POST['status'] ?? 'active',
			'sort'     => (int)($_POST['sort'] ?? 50),
		];
		$r = site_news_save($data);
		if ($r === true) {
			header('Location: ' . site_admin_url('site_news', 'saved=1'));
			exit;
		}
		$msg = $r;
		$msg_type = 'danger';
		$form = $data;
		$show_modal = true;
	} elseif ($act === 'delete') {
		$id = (int)($_POST['id'] ?? 0);
		$ok = $id > 0 && site_news_delete($id);
		header('Location: ' . site_admin_url('site_news', ($ok ? 'saved=1&msg=' . urlencode('新闻已删除') : 'msg=' . urlencode('删除失败'))));
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

$per = 15;
$page = max(1, (int)($_GET['page'] ?? 1));
$pageData = site_news_list($page, $per, '', ''); // 管理端显示全部状态
$newsList = $pageData['list'];
$total = $pageData['total'];
$pages = (int)ceil($total / $per);

// 新闻分类（默认分类 + 数据库中已有的其他分类）
$news_categories = ['产品发布', '优惠活动', '行业动态', '平台公告'];
global $DB;
$cat_rows = $DB->get_all_prepare("SELECT DISTINCT category FROM MN_plugin_site_news WHERE category != '' ORDER BY category ASC") ?: [];
foreach ($cat_rows as $cr) {
	if (!in_array($cr['category'], $news_categories, true)) {
		$news_categories[] = $cr['category'];
	}
}

$title = $title ?? '新闻管理';
mnbt_admin_include('head');
?>
<div class="container-fluid p-t-15">
	<div class="card">
		<div class="card-header">
			<h4 style="display:inline-block">新闻管理</h4>
			<span class="float-right">
				<button type="button" class="btn btn-primary btn-sm" onclick="openEdit(0)">
					<i class="mdi mdi-plus"></i> 添加新闻
				</button>
			</span>
		</div>
		<div class="card-body">
			<?php if (!empty($msg)): ?>
				<div class="alert alert-<?= htmlspecialchars($msg_type ?? 'danger', ENT_QUOTES) ?>"><?= htmlspecialchars($msg) ?></div>
			<?php endif; ?>

			<?php if (empty($newsList)): ?>
				<p class="text-muted">还没有新闻，点击右上角"添加新闻"发布第一篇。</p>
			<?php else: ?>
				<div class="table-responsive">
					<table class="table table-bordered table-hover">
						<thead>
							<tr>
								<th style="width:60px">ID</th>
								<th>标题</th>
								<th style="width:120px">分类</th>
								<th style="width:80px">浏览</th>
								<th style="width:80px">状态</th>
								<th style="width:70px">排序</th>
								<th style="width:140px">发布时间</th>
								<th style="width:170px">操作</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($newsList as $n): ?>
								<tr>
									<td><?= (int)$n['id'] ?></td>
									<td><?= htmlspecialchars(mb_substr($n['title'], 0, 40), ENT_QUOTES) ?><?= mb_strlen($n['title']) > 40 ? '…' : '' ?></td>
									<td><?= htmlspecialchars($n['category'] ?: '—', ENT_QUOTES) ?></td>
									<td><?= (int)$n['views'] ?></td>
									<td>
										<?php if ($n['status'] === 'active'): ?>
											<span class="badge badge-success">上架</span>
										<?php else: ?>
											<span class="badge badge-secondary">下架</span>
										<?php endif; ?>
									</td>
									<td><?= (int)$n['sort'] ?></td>
									<td><?= htmlspecialchars($n['created_at'], ENT_QUOTES) ?></td>
									<td>
										<button type="button" class="btn btn-sm btn-outline-primary" onclick="openEdit(<?= (int)$n['id'] ?>)">编辑</button>
										<form method="post" style="display:inline-block" onsubmit="var b=this.querySelector('button');b.disabled=true;b.textContent='删除中...';return confirm('确定删除此新闻？')">
											<input type="hidden" name="act" value="delete">
											<input type="hidden" name="id" value="<?= (int)$n['id'] ?>">
											<button type="submit" class="btn btn-sm btn-outline-danger">删除</button>
										</form>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>

				<?php if ($pages > 1): ?>
					<nav aria-label="分页">
						<ul class="pagination pagination-sm justify-content-end" style="margin-bottom:0">
							<?php for ($i = 1; $i <= $pages; $i++): ?>
								<li class="page-item<?= $i === $page ? ' active' : '' ?>">
									<a class="page-link" href="<?= htmlspecialchars(site_admin_url('site_news', 'page=' . $i), ENT_QUOTES) ?>"><?= $i ?></a>
								</li>
							<?php endfor; ?>
						</ul>
					</nav>
				<?php endif; ?>
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
					<h5 class="modal-title" id="modalTitle">添加新闻</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				</div>
				<div class="modal-body">
					<div class="form-group">
						<label>新闻标题</label>
						<input type="text" class="form-control" name="title" id="f-title" required>
					</div>
					<div class="form-row">
						<div class="col-6">
							<div class="form-group">
								<label>新闻分类</label>
								<select class="form-control" name="category" id="f-category">
									<option value="">未分类</option>
									<?php foreach ($news_categories as $cat): ?>
										<option value="<?= htmlspecialchars($cat, ENT_QUOTES) ?>"><?= htmlspecialchars($cat) ?></option>
									<?php endforeach; ?>
								</select>
							</div>
						</div>
						<div class="col-3">
							<div class="form-group">
								<label>状态</label>
								<select class="form-control" name="status" id="f-status">
									<option value="active">上架</option>
									<option value="inactive">下架</option>
								</select>
							</div>
						</div>
						<div class="col-3">
							<div class="form-group">
								<label>排序（小到大）</label>
								<input type="number" class="form-control" name="sort" id="f-sort" value="50" min="0">
							</div>
						</div>
					</div>
					<div class="form-group">
						<label>正文内容</label>
						<textarea class="form-control" name="content" id="f-content" rows="10" required></textarea>
						<small class="form-text text-muted">支持纯文本与 HTML 标签，换行使用 &lt;br&gt; 或空段落。</small>
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
var __siteNews = <?= json_encode($newsList, JSON_UNESCAPED_UNICODE) ?>;
<?php if (!empty($show_modal)): ?>
var __siteForm = <?= json_encode($form, JSON_UNESCAPED_UNICODE) ?>;
<?php endif; ?>
function openEdit(id) {
	var el = function (k) { return document.getElementById('f-' + k); };
	document.getElementById('modalTitle').textContent = id ? '编辑新闻' : '添加新闻';
	el('id').value = id;
	if (id > 0) {
		var n = __siteNews.find(function (x) { return parseInt(x.id, 10) === parseInt(id, 10); });
		if (n) {
			el('title').value = n.title || '';
			el('category').value = n.category || '';
			el('content').value = n.content || '';
			el('status').value = n.status || 'active';
			el('sort').value = n.sort || 50;
		}
	} else {
		el('title').value = '';
		el('category').value = '';
		el('content').value = '';
		el('status').value = 'active';
		el('sort').value = 50;
	}
	<?php if (!empty($show_modal)): ?>
	// 提交失败回显：用服务端返回的表单数据覆盖字段
	if (typeof __siteForm !== 'undefined') {
		el('title').value = __siteForm.title || '';
		el('category').value = __siteForm.category || '';
		el('content').value = __siteForm.content || '';
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
