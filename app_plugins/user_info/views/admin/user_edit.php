<?php
/**
 * 管理员端 - 用户编辑
 */
if (!defined('IN_CRONLITE')) {
	exit;
}

global $DB, $date;

$user_id = (int)($_GET['id'] ?? 0);
if ($user_id <= 0) {
	echo '参数错误';
	exit;
}

$row = $DB->get_row_prepare("SELECT * FROM MN_plugin_user WHERE id=? LIMIT 1", [$user_id]);
if (!$row) {
	echo '用户不存在';
	exit;
}

// POST 保存
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$email = trim((string)($_POST['email'] ?? ''));
	$qq = trim((string)($_POST['qq'] ?? ''));
	$status = isset($_POST['status']) ? ((int)$_POST['status'] === 1 ? 1 : 0) : (int)$row['status'];

	if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
		echo '<div class="alert alert-danger m-3">邮箱格式不正确</div>';
		exit;
	}
	if ($email !== '' && strlen($email) > 128) {
		echo '<div class="alert alert-danger m-3">邮箱过长</div>';
		exit;
	}
	if ($qq !== '' && !preg_match('/^[0-9]{5,12}$/', $qq)) {
		echo '<div class="alert alert-danger m-3">QQ 号格式不正确</div>';
		exit;
	}

	$now = $date ?: date('Y-m-d H:i:s');
	$ok = $DB->query_prepare(
		"UPDATE MN_plugin_user SET email=?, qq=?, status=?, updated_at=? WHERE id=?",
		[$email, $qq, $status, $now, $user_id]
	);
	if ($ok) {
		header('Location: plugin.php?p=user_info&page=users');
		exit;
	}
	echo '<div class="alert alert-danger m-3">保存失败</div>';
	exit;
}

$title = $title ?? '用户编辑';
mnbt_admin_include('head');
?>

<div class="container-fluid p-t-15">
	<div class="card">
		<div class="card-header">
			<h4 style="display:inline-block">编辑用户</h4>
			<a href="plugin.php?p=user_info&page=users" class="btn btn-sm btn-outline-secondary float-right multitabs">返回列表</a>
		</div>
		<div class="card-body">
			<form method="post">
				<table class="table table-bordered">
					<tbody>
						<tr>
							<th style="width:140px">用户 ID</th>
							<td><?= (int)$row['id'] ?></td>
						</tr>
						<tr>
							<th>用户名</th>
							<td><code><?= htmlspecialchars($row['username'], ENT_QUOTES) ?></code><small class="text-muted ml-2">（用户名不可修改）</small></td>
						</tr>
						<tr>
							<th>邮箱</th>
							<td><input type="text" name="email" class="form-control" value="<?= htmlspecialchars($row['email'], ENT_QUOTES) ?>" placeholder="选填"></td>
						</tr>
						<tr>
							<th>QQ</th>
							<td><input type="text" name="qq" class="form-control" value="<?= htmlspecialchars($row['qq'], ENT_QUOTES) ?>" placeholder="选填"></td>
						</tr>
						<tr>
							<th>状态</th>
							<td>
								<select name="status" class="form-control">
									<option value="1" <?= (int)$row['status'] === 1 ? 'selected' : '' ?>>正常</option>
									<option value="0" <?= (int)$row['status'] === 0 ? 'selected' : '' ?>>禁用</option>
								</select>
							</td>
						</tr>
						<tr>
							<th>注册时间</th>
							<td class="text-muted"><?= htmlspecialchars($row['created_at'], ENT_QUOTES) ?></td>
						</tr>
						<tr>
							<th>更新时间</th>
							<td class="text-muted"><?= htmlspecialchars($row['updated_at'], ENT_QUOTES) ?></td>
						</tr>
					</tbody>
				</table>

				<div class="text-center mt-3">
					<button type="submit" class="btn btn-primary"><i class="mdi mdi-content-save-outline"></i> 保存修改</button>
					<a href="plugin.php?p=user_info&page=users" class="btn btn-outline-secondary ml-2 multitabs">取消</a>
				</div>
			</form>
		</div>
	</div>
</div>
