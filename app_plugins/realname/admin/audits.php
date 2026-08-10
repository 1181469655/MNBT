<?php
/**
 * 管理员端 - 实名认证审核列表
 */
if (!defined('IN_CRONLITE')) {
	exit;
}
$title = '实名认证审核';
mnbt_admin_include('head');
$statusMap = [
	'pending'  => ['待审核', 'warning'],
	'approved' => ['已通过', 'success'],
	'rejected' => ['已驳回', 'danger'],
];
?>
<div class="container-fluid p-t-15">
	<div class="card">
		<div class="card-header">
			<h4 style="display:inline-block">实名认证审核</h4>
			<span class="float-right">
				<select class="form-control form-control-sm" id="fStatus" style="display:inline-block;width:120px;">
					<option value="">全部状态</option>
					<option value="pending">待审核</option>
					<option value="approved">已通过</option>
					<option value="rejected">已驳回</option>
				</select>
				<input type="text" class="form-control form-control-sm" id="fKeyword" placeholder="用户名 / 姓名" style="display:inline-block;width:160px;">
				<button class="btn btn-primary btn-sm" id="btnSearch"><i class="mdi mdi-magnify"></i> 查询</button>
			</span>
		</div>
		<div class="card-body">
			<table class="table table-hover table-striped text-center">
				<thead>
					<tr>
						<th>ID</th>
						<th>用户名</th>
						<th>姓名</th>
						<th>手机号</th>
						<th>身份证号</th>
						<th>状态</th>
						<th>备注</th>
						<th>提交时间</th>
						<th>操作</th>
					</tr>
				</thead>
				<tbody id="tbList">
					<tr><td colspan="9" style="color:#999">加载中…</td></tr>
				</tbody>
			</table>
			<div class="text-center" id="pgWrap" style="margin-top:10px;"></div>
		</div>
	</div>
</div>

<!-- 驳回原因 Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
	<div class="modal-dialog modal-sm">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">驳回认证</h5>
				<button type="button" class="close" data-dismiss="modal">&times;</button>
			</div>
			<div class="modal-body">
				<input type="hidden" id="rejectId">
				<div class="form-group">
					<label>驳回原因</label>
					<textarea class="form-control" id="rejectNote" rows="3" placeholder="请填写驳回原因，将展示给用户"></textarea>
				</div>
			</div>
			<div class="modal-footer">
				<button class="btn btn-secondary" data-dismiss="modal">取消</button>
				<button class="btn btn-danger" id="btnRejectOk">确认驳回</button>
			</div>
		</div>
	</div>
</div>

<script>
var curPage = 1;
var statusMap = {pending:['待审核','warning'], approved:['已通过','success'], rejected:['已驳回','danger']};

function loadList(page) {
	curPage = page || 1;
	$.post('ajax.php', {
		gn: 'realname_admin_list',
		page: curPage,
		per_page: 20,
		status: $('#fStatus').val(),
		keyword: $('#fKeyword').val()
	}, function (res) {
		try { res = typeof res === 'string' ? JSON.parse(res) : res; } catch (e) {}
		if (!res || res.code !== 'ok') { alert(res && res.code ? res.code : '加载失败'); return; }
		var html = '';
		if (!res.items || !res.items.length) {
			html = '<tr><td colspan="9" style="color:#999">暂无数据</td></tr>';
		} else {
			$.each(res.items, function (i, it) {
				var st = statusMap[it.status] || [it.status, 'secondary'];
				html += '<tr>'
					+ '<td>' + it.id + '</td>'
					+ '<td>' + it.username + '</td>'
					+ '<td>' + it.real_name + '</td>'
					+ '<td>' + it.phone + '</td>'
					+ '<td>' + it.id_card + '</td>'
					+ '<td><span class="badge badge-' + st[1] + '">' + st[0] + '</span></td>'
					+ '<td style="max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="' + (it.audit_note || '') + '">' + (it.audit_note || '') + '</td>'
					+ '<td>' + it.created_at + '</td>'
					+ '<td>'
					+ '<a class="btn btn-info btn-xs" href="plugin.php?p=realname&page=audit_detail&id=' + it.id + '">详情</a> ';
				if (it.status === 'pending') {
					html += '<button class="btn btn-success btn-xs" onclick="approve(' + it.id + ')">通过</button> ';
					html += '<button class="btn btn-danger btn-xs" onclick="openReject(' + it.id + ')">驳回</button>';
				}
				html += '</td></tr>';
			});
		}
		$('#tbList').html(html);
		// 分页
		var pg = '';
		if (res.pages > 1) {
			pg = '<nav><ul class="pagination pagination-sm" style="justify-content:center">';
			for (var i = 1; i <= res.pages; i++) {
				pg += '<li class="page-item ' + (i === curPage ? 'active' : '') + '"><a class="page-link" href="javascript:;" onclick="loadList(' + i + ')">' + i + '</a></li>';
			}
			pg += '</ul></nav>';
		}
		$('#pgWrap').html(pg);
	});
}

function approve(id) {
	if (!confirm('确认通过该认证？')) return;
	$.post('ajax.php', { gn: 'realname_admin_approve', id: id }, function (res) {
		try { res = typeof res === 'string' ? JSON.parse(res) : res; } catch (e) {}
		if (res && res.code === 'ok') { alert('已通过'); loadList(); } else { alert(res && res.code ? res.code : '操作失败'); }
	});
}

function openReject(id) {
	$('#rejectId').val(id);
	$('#rejectNote').val('');
	$('#rejectModal').modal('show');
}

$('#btnRejectOk').on('click', function () {
	var id = $('#rejectId').val();
	var note = $('#rejectNote').val();
	if (!note) { alert('请填写驳回原因'); return; }
	$.post('ajax.php', { gn: 'realname_admin_reject', id: id, note: note }, function (res) {
		try { res = typeof res === 'string' ? JSON.parse(res) : res; } catch (e) {}
		if (res && res.code === 'ok') { $('#rejectModal').modal('hide'); alert('已驳回'); loadList(); } else { alert(res && res.code ? res.code : '操作失败'); }
	});
});

$('#btnSearch').on('click', function () { loadList(1); });
loadList(1);
</script>
<?php mnbt_admin_include('foot'); ?>
