<?php
/**
 * 管理员端 - 用户余额管理
 */
if (!defined('IN_CRONLITE')) {
	exit;
}

$page_num = max(1, (int)($_GET['page_num'] ?? 1));
$kw = trim((string)($_GET['kw'] ?? ''));
$result = balance_admin_list($page_num, 30, $kw);
$list = $result['list'];
$total = $result['total'];
$total_pages = $result['per_page'] > 0 ? (int)ceil($total / $result['per_page']) : 1;

$title = $title ?? '余额管理';
mnbt_admin_include('head');
?>
<link rel="stylesheet" href="<?= mnbt_asset_url('js/jquery-confirm/jquery-confirm.min.css') ?>">
<script src="<?= mnbt_asset_url('js/jquery-confirm/jquery-confirm.min.js') ?>"></script>

<div class="container-fluid p-t-15">
	<div class="card">
		<div class="card-header">
			<h4 style="display:inline-block">用户余额管理</h4>
			<small class="text-muted ml-2">共 <?= $total ?> 条记录</small>
		</div>
		<div class="card-body">
			<form method="get" class="form-inline mb-3">
				<input type="hidden" name="p" value="balance">
				<input type="hidden" name="page" value="balances">
				<input type="text" name="kw" class="form-control form-control-sm mr-2" placeholder="用户名" value="<?= htmlspecialchars($kw, ENT_QUOTES) ?>">
				<button type="submit" class="btn btn-sm btn-primary">搜索</button>
				<a href="plugin.php?p=balance&page=balances" class="btn btn-sm btn-outline-secondary ml-2">重置</a>
			</form>

			<div class="table-responsive">
				<table class="table table-bordered table-hover">
					<thead>
						<tr>
							<th style="width:60px">ID</th>
							<th>用户ID</th>
							<th>用户名</th>
							<th>邮箱</th>
							<th>余额（元）</th>
							<th>状态</th>
							<th>更新时间</th>
							<th style="width:180px">操作</th>
						</tr>
					</thead>
					<tbody>
						<?php if (empty($list)): ?>
							<tr><td colspan="8" class="text-center text-muted">暂无记录</td></tr>
						<?php else: ?>
							<?php foreach ($list as $b): ?>
								<tr>
									<td><?= (int)$b['id'] ?></td>
									<td><?= (int)$b['user_id'] ?></td>
									<td><code><?= htmlspecialchars($b['username'] ?: ('#' . $b['user_id']), ENT_QUOTES) ?></code></td>
									<td><?= htmlspecialchars($b['email'] ?: '-', ENT_QUOTES) ?></td>
									<td><b class="text-primary">¥<?= htmlspecialchars(balance_format((int)$b['balance']), ENT_QUOTES) ?></b></td>
									<td>
										<?php if ((int)($b['status'] ?? 1) === 1): ?>
											<span class="badge badge-success">正常</span>
										<?php else: ?>
											<span class="badge badge-danger">禁用</span>
										<?php endif; ?>
									</td>
									<td class="small text-muted"><?= htmlspecialchars($b['updated_at'], ENT_QUOTES) ?></td>
									<td>
										<button type="button" class="btn btn-sm btn-outline-success" onclick="adjustBalance(<?= (int)$b['user_id'] ?>, '<?= htmlspecialchars($b['username'] ?: ('#' . $b['user_id']), ENT_QUOTES) ?>', 'add')">加款</button>
										<button type="button" class="btn btn-sm btn-outline-danger" onclick="adjustBalance(<?= (int)$b['user_id'] ?>, '<?= htmlspecialchars($b['username'] ?: ('#' . $b['user_id']), ENT_QUOTES) ?>', 'deduct')">扣款</button>
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
								<a class="page-link" href="plugin.php?p=balance&page=balances&page_num=<?= $i ?>&kw=<?= urlencode($kw) ?>"><?= $i ?></a>
							</li>
						<?php endfor; ?>
					</ul>
				</nav>
			<?php endif; ?>
		</div>
	</div>
</div>

<script>
function adjustBalance(uid, uname, direction) {
	var title = direction === 'add' ? '加款' : '扣款';
	$.confirm({
		title: title + ' - ' + uname,
		content: '<div class="form-group">' +
			'<label>金额（元）</label>' +
			'<input type="number" step="0.01" min="0.01" class="form-control" id="adj-amount" placeholder="金额">' +
			'</div>' +
			'<div class="form-group">' +
			'<label>备注</label>' +
			'<input type="text" class="form-control" id="adj-remark" placeholder="备注（选填）">' +
			'</div>',
		buttons: {
			confirm: {
				text: '确定' + title,
				btnClass: direction === 'add' ? 'btn-success' : 'btn-danger',
				action: function () {
					var amount = $.trim($('#adj-amount').val());
					var remark = $.trim($('#adj-remark').val());
					if (!amount || parseFloat(amount) <= 0) {
						$.alert('金额必须大于 0');
						return false;
					}
					$.post('ajax.php', {
						gn: 'balance_admin_adjust',
						user_id: uid,
						amount: amount,
						direction: direction,
						remark: remark
					}, function (resp) {
						var j;
						try { j = typeof resp === 'string' ? JSON.parse(resp) : resp; } catch (e) { j = { code: resp }; }
						var code = j.code || j.msg || '';
						if (code === '调整成功') {
							$.alert({ title: '成功', content: '余额调整成功', type: 'green', buttons: { ok: { action: function () { location.reload(); } } } });
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
