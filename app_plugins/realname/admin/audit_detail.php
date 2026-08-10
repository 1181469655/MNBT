<?php
/**
 * 管理员端 - 实名认证详情
 */
if (!defined('IN_CRONLITE')) {
	exit;
}
$id = (int)($_GET['id'] ?? 0);
$auth = $id > 0 ? realname_get_by_id($id) : null;
$title = '实名认证详情';
mnbt_admin_include('head');

$statusMap = [
	'pending'  => ['待审核', 'warning'],
	'approved' => ['已通过', 'success'],
	'rejected' => ['已驳回', 'danger'],
];
$imgBase = '../index.php?_r=/realname/api/img';
$typeMap = [
	'front' => '身份证正面',
	'back'  => '身份证反面',
	'hand'  => '手持身份证',
];
?>
<div class="container-fluid p-t-15">
<?php if (!$auth): ?>
	<div class="card"><div class="card-body text-center" style="color:#999;padding:40px">记录不存在或已删除。<br><br><a class="btn btn-primary btn-sm" href="plugin.php?p=realname&page=audits">返回列表</a></div></div>
<?php else: ?>
	<div class="row">
		<div class="col-md-8">
			<div class="card">
				<div class="card-header">
					<h4 style="display:inline-block">认证详情 #<?= (int)$auth['id'] ?></h4>
					<?php $st = $statusMap[$auth['status']] ?? [$auth['status'], 'secondary']; ?>
					<span class="badge badge-<?= $st[1] ?> ml-2"><?= $st[0] ?></span>
					<span class="float-right"><a class="btn btn-default btn-sm" href="plugin.php?p=realname&page=audits">返回列表</a></span>
				</div>
				<div class="card-body">
					<table class="table table-bordered">
						<tr><th style="width:160px">用户名</th><td><?= htmlspecialchars($auth['username']) ?></td></tr>
						<tr><th>用户 ID</th><td><?= (int)$auth['user_id'] ?></td></tr>
						<tr><th>姓名</th><td><?= htmlspecialchars(realname_mask_name($auth['real_name'])) ?></td></tr>
						<tr><th>手机号</th><td><?= htmlspecialchars(realname_mask_phone($auth['phone'])) ?></td></tr>
						<tr><th>身份证号</th><td><?= htmlspecialchars(realname_mask_idcard(realname_decrypt($auth['id_card']))) ?>
							<button class="btn btn-warning btn-xs ml-2" onclick="$('#decryptModal').modal('show')"><i class="mdi mdi-eye"></i> 解密查看</button>
						</td></tr>
						<tr><th>OCR 姓名</th><td><?= htmlspecialchars($auth['ocr_name'] !== '' ? $auth['ocr_name'] : '（未识别/未回填）') ?></td></tr>
						<tr><th>OCR 身份证号</th><td><?= $auth['ocr_id_card'] !== '' ? htmlspecialchars(realname_mask_idcard(realname_decrypt($auth['ocr_id_card']))) : '（未识别/未回填）' ?></td></tr>
						<tr><th>审核状态</th><td><span class="badge badge-<?= $st[1] ?>"><?= $st[0] ?></span></td></tr>
						<tr><th>审核备注</th><td><?= htmlspecialchars((string)$auth['audit_note']) ?: '—' ?></td></tr>
						<tr><th>提交时间</th><td><?= htmlspecialchars((string)$auth['created_at']) ?></td></tr>
						<tr><th>审核时间</th><td><?= htmlspecialchars((string)$auth['audited_at']) ?: '—' ?></td></tr>
					</table>
					<?php if ($auth['status'] === 'pending'): ?>
					<div class="mt-3">
						<button class="btn btn-success" id="btnApprove"><i class="mdi mdi-check"></i> 审核通过</button>
						<button class="btn btn-danger" id="btnReject" data-toggle="modal" data-target="#rejectModal"><i class="mdi mdi-close"></i> 驳回</button>
					</div>
					<?php elseif ($auth['status'] === 'rejected'): ?>
					<div class="mt-3">
						<button class="btn btn-success" id="btnApprove2"><i class="mdi mdi-check"></i> 强制通过</button>
					</div>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<div class="col-md-4">
			<div class="card">
				<div class="card-header"><h4>证件照片</h4></div>
				<div class="card-body">
					<?php foreach ($typeMap as $key => $label): ?>
						<div class="form-group text-center">
							<label><?= $label ?></label>
							<a href="<?= $imgBase ?>&id=<?= (int)$auth['id'] ?>&type=<?= $key ?>" target="_blank">
								<img src="<?= $imgBase ?>&id=<?= (int)$auth['id'] ?>&type=<?= $key ?>" alt="<?= $label ?>"
									style="width:100%;max-height:180px;object-fit:contain;border:1px solid #eee;border-radius:6px;background:#fafbfc;">
							</a>
						</div>
					<?php endforeach; ?>
					<p class="text-muted" style="font-size:12px">点击图片可查看原图。照片仅管理员可见。</p>
				</div>
			</div>
		</div>
	</div>

	<!-- 解密查看 Modal -->
	<div class="modal fade" id="decryptModal" tabindex="-1">
		<div class="modal-dialog modal-sm">
			<div class="modal-content">
				<div class="modal-header"><h5 class="modal-title">解密查看身份证号</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
				<div class="modal-body">
					<div class="form-group">
						<label>后台管理密码</label>
						<input type="password" class="form-control" id="decryptPass" placeholder="输入后台密码确认身份">
					</div>
					<div id="decryptResult" style="display:none;background:#f8f9fa;padding:10px;border-radius:6px;margin-top:8px;"></div>
				</div>
				<div class="modal-footer">
					<button class="btn btn-secondary" data-dismiss="modal">取消</button>
					<button class="btn btn-warning" id="btnDecryptOk">确认查看</button>
				</div>
			</div>
		</div>
	</div>

	<!-- 驳回原因 Modal -->
	<div class="modal fade" id="rejectModal" tabindex="-1">
		<div class="modal-dialog modal-sm">
			<div class="modal-content">
				<div class="modal-header"><h5 class="modal-title">驳回认证</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
				<div class="modal-body">
					<textarea class="form-control" id="rejectNote" rows="3" placeholder="请填写驳回原因，将展示给用户"></textarea>
				</div>
				<div class="modal-footer">
					<button class="btn btn-secondary" data-dismiss="modal">取消</button>
					<button class="btn btn-danger" id="btnRejectOk">确认驳回</button>
				</div>
			</div>
		</div>
	</div>
<?php endif; ?>
</div>

<script>
var AUTH_ID = <?= $auth ? (int)$auth['id'] : 0 ?>;

function doApprove() {
	if (!confirm('确认通过该认证？')) return;
	$.post('ajax.php', { gn: 'realname_admin_approve', id: AUTH_ID }, function (res) {
		try { res = typeof res === 'string' ? JSON.parse(res) : res; } catch (e) {}
		alert(res && res.code === 'ok' ? '已通过' : (res && res.code ? res.code : '操作失败'));
		if (res && res.code === 'ok') location.reload();
	});
}
$('#btnApprove').on('click', doApprove);
$('#btnApprove2').on('click', doApprove);

$('#btnRejectOk').on('click', function () {
	var note = $('#rejectNote').val();
	if (!note) { alert('请填写驳回原因'); return; }
	$.post('ajax.php', { gn: 'realname_admin_reject', id: AUTH_ID, note: note }, function (res) {
		try { res = typeof res === 'string' ? JSON.parse(res) : res; } catch (e) {}
		alert(res && res.code === 'ok' ? '已驳回' : (res && res.code ? res.code : '操作失败'));
		if (res && res.code === 'ok') location.reload();
	});
});

$('#btnDecryptOk').on('click', function () {
	var pass = $('#decryptPass').val();
	if (!pass) { alert('请输入后台管理密码'); return; }
	$.post('ajax.php', { gn: 'realname_admin_decrypt', id: AUTH_ID, password: pass }, function (res) {
		try { res = typeof res === 'string' ? JSON.parse(res) : res; } catch (e) {}
		if (!res || res.code !== 'ok') { alert(res && res.code ? res.code : '操作失败'); return; }
		$('#decryptResult').html(
			'<b>姓名：</b>' + res.real_name + '<br>'
			+ '<b>手机号：</b>' + res.phone + '<br>'
			+ '<b>身份证号：</b>' + res.id_card + '<br>'
			+ (res.ocr_name ? '<b>OCR姓名：</b>' + res.ocr_name + '<br>' : '')
			+ (res.ocr_id_card ? '<b>OCR身份证号：</b>' + res.ocr_id_card : '')
		).show();
	});
});
</script>
<?php mnbt_admin_include('foot'); ?>
