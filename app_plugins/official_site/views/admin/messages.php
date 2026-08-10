<?php
/**
 * 管理员端 - 官网留言管理（列表分页 + 标记已读/删除）
 */
if (!defined('IN_CRONLITE')) {
	exit;
}

// —— 处理 POST（成功后 redirect 到带提示参数的地址，避免刷新重复提交）——
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$act = $_POST['act'] ?? '';
	$id = (int)($_POST['id'] ?? 0);

	if ($act === 'set_read') {
		$read = (int)($_POST['read'] ?? 0) === 1;
		$ok = $id > 0 && site_message_set_read($id, $read);
		header('Location: ' . site_admin_url('site_messages', ($ok ? 'saved=1&msg=' . urlencode($read ? '已标记为已读' : '已标记为未读') : 'msg=' . urlencode('操作失败'))));
		exit;
	} elseif ($act === 'delete') {
		$ok = $id > 0 && site_message_delete($id);
		header('Location: ' . site_admin_url('site_messages', ($ok ? 'saved=1&msg=' . urlencode('留言已删除') : 'msg=' . urlencode('删除失败'))));
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
$pageData = site_message_list($page, $per);
$messages = $pageData['list'];
$total = $pageData['total'];
$pages = (int)ceil($total / $per);
global $DB;
$unread = (int)($DB->get_row_prepare("SELECT COUNT(*) AS c FROM MN_plugin_site_message WHERE is_read=0")['c'] ?? 0);

$title = $title ?? '留言管理';
mnbt_admin_include('head');
?>
<div class="container-fluid p-t-15">
	<div class="card">
		<div class="card-header">
			<h4 style="display:inline-block">留言管理</h4>
			<span class="float-right"><span class="badge badge-warning">未读 <?= (int)$unread ?> 条</span></span>
		</div>
		<div class="card-body">
			<?php if (!empty($msg)): ?>
				<div class="alert alert-<?= htmlspecialchars($msg_type ?? 'danger', ENT_QUOTES) ?>"><?= htmlspecialchars($msg) ?></div>
			<?php endif; ?>

			<?php if (empty($messages)): ?>
				<p class="text-muted">还没有收到留言。</p>
			<?php else: ?>
				<div class="table-responsive">
					<table class="table table-bordered table-hover">
						<thead>
							<tr>
								<th style="width:60px">ID</th>
								<th style="width:140px">姓名</th>
								<th style="width:200px">联系方式</th>
								<th>留言内容</th>
								<th style="width:90px">状态</th>
								<th style="width:140px">提交时间</th>
								<th style="width:200px">操作</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($messages as $m): ?>
								<tr class="<?= (int)$m['is_read'] === 0 ? 'table-warning' : '' ?>">
									<td><?= (int)$m['id'] ?></td>
									<td><?= htmlspecialchars($m['name'], ENT_QUOTES) ?></td>
									<td>
										<?php if ($m['email'] !== ''): ?><i class="mdi mdi-email-outline"></i> <?= htmlspecialchars($m['email'], ENT_QUOTES) ?><br><?php endif; ?>
										<?php if ($m['phone'] !== ''): ?><i class="mdi mdi-phone-outline"></i> <?= htmlspecialchars($m['phone'], ENT_QUOTES) ?><?php endif; ?>
									</td>
									<td><?= htmlspecialchars(mb_substr($m['message'], 0, 60), ENT_QUOTES) ?><?= mb_strlen($m['message']) > 60 ? '…' : '' ?></td>
									<td>
										<?php if ((int)$m['is_read'] === 0): ?>
											<span class="badge badge-warning">未读</span>
										<?php else: ?>
											<span class="badge badge-success">已读</span>
										<?php endif; ?>
									</td>
									<td><?= htmlspecialchars($m['created_at'], ENT_QUOTES) ?></td>
									<td>
										<button type="button" class="btn btn-sm btn-outline-primary" onclick="viewMessage(<?= (int)$m['id'] ?>)">查看</button>
										<form method="post" style="display:inline-block">
											<input type="hidden" name="act" value="set_read">
											<input type="hidden" name="id" value="<?= (int)$m['id'] ?>">
											<input type="hidden" name="read" value="<?= (int)$m['is_read'] === 0 ? 1 : 0 ?>">
											<button type="submit" class="btn btn-sm btn-outline-secondary"><?= (int)$m['is_read'] === 0 ? '标为已读' : '标为未读' ?></button>
										</form>
										<form method="post" style="display:inline-block" onsubmit="var b=this.querySelector('button');b.disabled=true;b.textContent='删除中...';return confirm('确定删除此留言？')">
											<input type="hidden" name="act" value="delete">
											<input type="hidden" name="id" value="<?= (int)$m['id'] ?>">
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
									<a class="page-link" href="<?= htmlspecialchars(site_admin_url('site_messages', 'page=' . $i), ENT_QUOTES) ?>"><?= $i ?></a>
								</li>
							<?php endfor; ?>
						</ul>
					</nav>
				<?php endif; ?>
			<?php endif; ?>
		</div>
	</div>
</div>

<!-- 留言详情模态框 -->
<div class="modal fade" id="viewModal" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">留言详情</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			</div>
			<div class="modal-body" id="viewBody"></div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">关闭</button>
			</div>
		</div>
	</div>
</div>

<script>
var __siteMessages = <?= json_encode($messages, JSON_UNESCAPED_UNICODE) ?>;
function esc(s) {
	return String(s == null ? '' : s).replace(/[&<>"]/g, function (c) {
		return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c];
	});
}
function viewMessage(id) {
	var m = __siteMessages.find(function (x) { return parseInt(x.id, 10) === parseInt(id, 10); });
	if (!m) { return; }
	var contact = [];
	if (m.email) { contact.push('邮箱：' + esc(m.email)); }
	if (m.phone) { contact.push('电话：' + esc(m.phone)); }
	document.getElementById('viewBody').innerHTML =
		'<p><strong>' + esc(m.name) + '</strong> <span class="text-muted">(' + esc(m.created_at) + ')</span></p>' +
		(contact.length ? '<p class="text-muted">' + contact.join('　') + '</p>' : '') +
		'<hr><p style="white-space:pre-wrap;word-break:break-word;">' + esc(m.message) + '</p>';
	$('#viewModal').modal('show');
}
</script>
<?php mnbt_admin_include('foot'); ?>
