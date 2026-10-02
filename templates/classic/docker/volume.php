<?php
/** 存储卷 */
$active = 'volume';
include __DIR__ . '/head.php';
?>
<div class="dk-card">
	<div class="dk-card-head"><h3>存储卷</h3><button class="dk-btn dk-btn-ghost dk-btn-sm" onclick="loadVolumes()">刷新</button></div>
	<div class="dk-card-body flush">
		<div class="dk-spinner-overlay" id="dkVolLoading"><span class="dk-spin"></span> 加载中…</div>
		<table class="dk-table" id="dkVolTable" style="display:none">
			<thead><tr><th>名称</th><th>驱动</th><th>挂载点</th><th>创建时间</th></tr></thead>
			<tbody></tbody>
		</table>
	</div>
</div>
<script>
function esc(s){ return $('<div>').text(s==null?'':String(s)).html(); }
function loadVolumes(){
	$('#dkVolLoading').show(); $('#dkVolTable').hide();
	dkAjax('volume_list', {}, {silent:true, timeout:60000}).then(function(r){
		var list = r.data && r.data.data ? r.data.data : (r.data || []);
		list = Array.isArray(list) ? list : [];
		var rows = '';
		if (!list.length) rows = '<tr><td colspan="4" class="dk-empty">暂无存储卷</td></tr>';
		list.forEach(function(v){
			rows += '<tr><td class="dk-mono">'+ esc(v.Name || v.name || '-') +'</td><td>'+ esc(v.Driver || v.driver || '-') +'</td><td class="dk-mono" style="font-size:12px">'+ esc(v.Mountpoint || v.mountpoint || '-') +'</td><td class="dk-mono">'+ esc(v.CreatedAt || v.created_at || '-') +'</td></tr>';
		});
		$('#dkVolTable tbody').html(rows);
		$('#dkVolLoading').hide(); $('#dkVolTable').show();
	}).fail(function(){ $('#dkVolLoading').html('<div class="dk-empty"><p>加载失败，请确认节点 Docker 可用</p></div>'); });
}
loadVolumes();
</script>
<?php include __DIR__ . '/foot.php'; ?>
