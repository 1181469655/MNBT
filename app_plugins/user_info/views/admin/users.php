<?php
/**
 * 管理员端 - 用户列表管理
 */
if (!defined('IN_CRONLITE')) {
	exit;
}

global $DB, $date;

// POST 操作：启用/禁用/删除用户
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$action = $_POST['action'] ?? '';
	$user_id = (int)($_POST['user_id'] ?? 0);
	if ($user_id > 0) {
		if ($action === 'toggle_status') {
			$row = $DB->get_row_prepare("SELECT status FROM MN_plugin_user WHERE id=? LIMIT 1", [$user_id]);
			if ($row) {
				$new_status = (int)$row['status'] === 1 ? 0 : 1;
				$now = $date ?: date('Y-m-d H:i:s');
				$DB->query_prepare("UPDATE MN_plugin_user SET status=?, updated_at=? WHERE id=?", [$new_status, $now, $user_id]);
			}
		}
	}
	header('Location: plugin.php?p=user_info&page=users&' . http_build_query([
		'page_num' => $_GET['page_num'] ?? 1,
		'kw' => $_GET['kw'] ?? '',
	]));
	exit;
}

// 筛选与分页
$page_num = max(1, (int)($_GET['page_num'] ?? 1));
$per_page = 30;
$offset = ($page_num - 1) * $per_page;
$kw = trim((string)($_GET['kw'] ?? ''));

$where = '';
$params = [];
if ($kw !== '') {
	$where = " WHERE username LIKE ? OR email LIKE ? OR qq LIKE ?";
	$like = '%' . $kw . '%';
	$params = [$like, $like, $like];
}

$count_row = $DB->get_row_prepare("SELECT COUNT(*) AS cnt FROM MN_plugin_user{$where}", $params);
$total = $count_row ? (int)$count_row['cnt'] : 0;

$list = $DB->get_all_prepare(
	"SELECT * FROM MN_plugin_user{$where} ORDER BY id DESC LIMIT {$offset},{$per_page}",
	$params
) ?: [];

$total_pages = $per_page > 0 ? (int)ceil($total / $per_page) : 1;

$title = $title ?? '用户管理';
mnbt_admin_include('head');
?>
<link rel="stylesheet" href="<?= mnbt_asset_url('js/jquery-confirm/jquery-confirm.min.css') ?>">
<script src="<?= mnbt_asset_url('js/jquery-confirm/jquery-confirm.min.js') ?>"></script>

<div class="container-fluid p-t-15">
	<div class="card">
		<div class="card-header">
			<h4 style="display:inline-block">用户管理</h4>
			<small class="text-muted ml-2">共 <?= $total ?> 个用户</small>
		</div>
		<div class="card-body">
			<form method="get" class="form-inline mb-3">
				<input type="hidden" name="p" value="user_info">
				<input type="hidden" name="page" value="users">
				<input type="text" name="kw" class="form-control form-control-sm mr-2" placeholder="用户名 / 邮箱 / QQ" value="<?= htmlspecialchars($kw, ENT_QUOTES) ?>">
				<button type="submit" class="btn btn-sm btn-primary">搜索</button>
				<a href="plugin.php?p=user_info&page=users" class="btn btn-sm btn-outline-secondary ml-2">重置</a>
			</form>

			<div class="table-responsive">
				<table class="table table-bordered table-hover">
					<thead>
						<tr>
							<th style="width:60px">ID</th>
							<th>用户名</th>
							<th>邮箱</th>
							<th>QQ</th>
							<th>状态</th>
							<th>注册时间</th>
							<th style="width:200px">操作</th>
						</tr>
					</thead>
					<tbody>
						<?php if (empty($list)): ?>
							<tr><td colspan="7" class="text-center text-muted">暂无用户</td></tr>
						<?php else: ?>
							<?php foreach ($list as $u): ?>
								<tr>
									<td><?= (int)$u['id'] ?></td>
									<td><code><?= htmlspecialchars($u['username'], ENT_QUOTES) ?></code></td>
									<td><?= htmlspecialchars($u['email'] ?: '-', ENT_QUOTES) ?></td>
									<td><?= htmlspecialchars($u['qq'] ?: '-', ENT_QUOTES) ?></td>
									<td>
										<?php if ((int)$u['status'] === 1): ?>
											<span class="badge badge-success">正常</span>
										<?php else: ?>
											<span class="badge badge-danger">禁用</span>
										<?php endif; ?>
									</td>
									<td class="small text-muted"><?= htmlspecialchars($u['created_at'], ENT_QUOTES) ?></td>
									<td>
										<a href="plugin.php?p=user_info&page=user_edit&id=<?= (int)$u['id'] ?>" class="btn btn-sm btn-outline-primary multitabs">编辑</a>
										<button type="button" class="btn btn-sm btn-outline-warning" onclick="resetPass(<?= (int)$u['id'] ?>, '<?= htmlspecialchars($u['username'], ENT_QUOTES) ?>')">重置密码</button>
										<form method="post" class="d-inline" onsubmit="return confirm('确定<?= (int)$u['status'] === 1 ? '禁用' : '启用' ?>该用户？')">
											<input type="hidden" name="action" value="toggle_status">
											<input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
											<button type="submit" class="btn btn-sm <?= (int)$u['status'] === 1 ? 'btn-outline-danger' : 'btn-outline-success' ?>">
												<?= (int)$u['status'] === 1 ? '禁用' : '启用' ?>
											</button>
										</form>
									</td>
								</tr>
							<?php endforeach; ?>
						<?php endif; ?>
					</tbody>
				</table>
			</div>

			<?php if ($total_pages > 1): ?>
				<nav>
					<ul class="pagination justify-content-center">
						<?php for ($i = 1; $i <= $total_pages; $i++): ?>
							<li class="page-item <?= $i === $page_num ? 'active' : '' ?>">
								<a class="page-link" href="plugin.php?p=user_info&page=users&page_num=<?= $i ?>&kw=<?= urlencode($kw) ?>"><?= $i ?></a>
							</li>
						<?php endfor; ?>
					</ul>
				</nav>
			<?php endif; ?>
		</div>
	</div>
</div>

<script>
function resetPass(uid, uname) {
	$.confirm({
		title: '重置密码 - ' + uname,
		content: '<input type="password" class="form-control" id="reset-pwd" placeholder="新密码（至少6位）">',
		buttons: {
			confirm: {
				text: '确定重置',
				btnClass: 'btn-primary',
				action: function () {
					var pwd = $.trim($('#reset-pwd').val());
					if (pwd.length < 6) {
						$.alert('新密码至少 6 个字符');
						return false;
					}
					$.post('ajax.php', {
						gn: 'user_info_admin_reset_password',
						user_id: uid,
						new_password: pwd
					}, function (resp) {
						var j;
						try { j = typeof resp === 'string' ? JSON.parse(resp) : resp; } catch (e) { j = { code: resp }; }
						var code = j.code || j.msg || '';
						if (code === '重置成功') {
							$.alert({ title: '成功', content: '密码已重置', type: 'green' });
						} else {
							$.alert({ title: '失败', content: code || '操作失败', type: 'red' });
						}
					}).fail(function () {
						$.alert({ title: '错误', content: '网络错误', type: 'red' });
					});
					return true;
				}
			},
			cancel: { text: '取消' }
		}
	});
}
</script>
