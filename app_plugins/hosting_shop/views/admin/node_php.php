<?php
/**
 * 管理员端 - 节点 PHP 版本管理
 */
if (!defined('IN_CRONLITE')) {
	exit;
}

$nodes = hosting_node_list_all();
$title = $title ?? '节点PHP管理';
mnbt_admin_include('head');
?>
<div class="container-fluid p-t-15">
	<div class="card">
		<div class="card-header">
			<h4 style="display:inline-block">节点 PHP 版本管理</h4>
		</div>
		<div class="card-body">
			<div class="alert alert-info">
				<i class="mdi mdi-information"></i> 每个宝塔节点可独立设置默认 PHP 版本。节点未设置时，系统会自动从宝塔 API 检测最新版本。
			</div>

			<?php if (empty($nodes)): ?>
				<div class="alert alert-warning">尚未添加任何宝塔节点。</div>
			<?php else: ?>
				<div class="table-responsive">
					<table class="table table-bordered table-hover">
						<thead class="thead-light">
							<tr>
								<th style="width:60px">#</th>
								<th>节点代号</th>
								<th style="width:130px">当前默认 PHP</th>
								<th style="width:130px">操作</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($nodes as $i => $n): ?>
							<?php $btdh = $n['btdh']; $cur = $n['mrbts_php'] ?? ''; ?>
							<tr>
								<td><?= $i + 1 ?></td>
								<td>
									<strong><?= htmlspecialchars($btdh) ?></strong>
									<small class="text-muted d-block"><?= htmlspecialchars($n['btip']) ?>:<?= htmlspecialchars($n['btdk']) ?></small>
								</td>
								<td>
									<span id="php_label_<?= htmlspecialchars($btdh) ?>">
										<?php if ($cur !== ''): ?>
											<span class="badge badge-success"><?= htmlspecialchars($cur) ?></span>
										<?php else: ?>
											<span class="badge badge-secondary">未设置</span>
										<?php endif; ?>
									</span>
								</td>
								<td>
									<button class="btn btn-info btn-sm" onclick="fetchVersions('<?= htmlspecialchars($btdh, ENT_QUOTES) ?>')" id="btn_fetch_<?= htmlspecialchars($btdh) ?>">
										<i class="mdi mdi-cloud-download"></i> 获取版本
									</button>
									<button class="btn btn-success btn-sm" onclick="autoDetect('<?= htmlspecialchars($btdh, ENT_QUOTES) ?>')" id="btn_detect_<?= htmlspecialchars($btdh) ?>">
										<i class="mdi mdi-magic"></i> 自动设置最新
									</button>
								</td>
							</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			<?php endif; ?>
		</div>
	</div>

	<!-- 版本选择弹窗 -->
	<div class="modal fade" id="versionModal" tabindex="-1">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title">选择 PHP 版本 - <span id="vNodeLabel"></span></h5>
					<button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
				</div>
				<div class="modal-body" id="versionList" style="max-height:400px;overflow-y:auto;">
					<div class="text-center text-muted p-4">加载中...</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">关闭</button>
				</div>
			</div>
		</div>
	</div>
</div>

<script>
function ajax(gn, data, cb) {
	$.post('./ajax.php', $.extend({gn: gn}, data), function(res) {
		try { res = typeof res === 'string' ? JSON.parse(res) : res; } catch(e) {}
		cb(res);
	});
}

function fetchVersions(btdh) {
	var btn = $('#btn_fetch_' + btdh);
	btn.prop('disabled', true).html('<i class="mdi mdi-loading mdi-spin"></i> 获取中...');
	$('#vNodeLabel').text(btdh);
	$('#versionList').html('<div class="text-center text-muted p-4">加载中...</div>');
	$('#versionModal').modal('show');

	ajax('p_hosting_node_php_list', {btdh: btdh}, function(res) {
		btn.prop('disabled', false).html('<i class="mdi mdi-cloud-download"></i> 获取版本');
		if (res.qk !== 1) {
			$('#versionList').html('<div class="alert alert-danger">' + (res.msg || res.code || '获取失败') + '</div>');
			return;
		}
		var versions = res.versions || [];
		var current = res.current_default || '';
		var html = '';
		if (versions.length === 0) {
			html = '<div class="alert alert-warning">该节点未安装任何 PHP 版本</div>';
		} else {
			html += '<div class="list-group list-group-flush">';
			versions.forEach(function(v) {
				var active = v.version === current ? ' active' : '';
				var badge = v.version === current ? ' <span class="badge badge-success float-right">当前默认</span>' : '';
				html += '<a href="javascript:;" class="list-group-item list-group-item-action' + active + '" onclick="setVersion(\'' + btdh + '\', \'' + v.version + '\')">' +
					'<strong>' + v.version + '</strong> - ' + v.name + badge + '</a>';
			});
			html += '</div>';
		}
		$('#versionList').html(html);
	});
}

function autoDetect(btdh) {
	var btn = $('#btn_detect_' + btdh);
	btn.prop('disabled', true).html('<i class="mdi mdi-loading mdi-spin"></i> 检测中...');
	ajax('p_hosting_node_php_auto_detect', {btdh: btdh}, function(res) {
		btn.prop('disabled', false).html('<i class="mdi mdi-magic"></i> 自动设置最新');
		if (res.qk === 1) {
			$('#php_label_' + btdh).html('<span class="badge badge-success">' + (res.version || '') + '</span>');
			alert('设置成功：默认 PHP 版本已设为 ' + (res.version || ''));
		} else {
			alert(res.msg || res.code || '操作失败');
		}
	});
}

function setVersion(btdh, version) {
	ajax('p_hosting_node_php_set', {btdh: btdh, version: version}, function(res) {
		if (res.qk === 1) {
			$('#php_label_' + btdh).html('<span class="badge badge-success">' + version + '</span>');
			$('#versionModal').modal('hide');
			alert('已设置节点 ' + btdh + ' 的默认 PHP 版本为 ' + version);
		} else {
			alert(res.msg || res.code || '操作失败');
		}
	});
}
</script>
