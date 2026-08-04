<?php
/** Compose 模板与项目 */
$active = 'compose';
include __DIR__ . '/head.php';
?>
<div class="dk-card">
	<div class="dk-card-head"><h3>Compose 模板</h3><button class="dk-btn dk-btn-ghost dk-btn-sm" onclick="loadCompose()">刷新</button></div>
	<div class="dk-card-body flush">
		<div class="dk-spinner-overlay" id="dkTplLoading"><span class="dk-spin"></span> 加载中…</div>
		<table class="dk-table" id="dkTplTable" style="display:none">
			<thead><tr><th>模板名称</th><th>描述</th><th>分类</th></tr></thead>
			<tbody></tbody>
		</table>
	</div>
</div>
<div class="dk-card">
	<div class="dk-card-head"><h3>Docker 项目</h3></div>
	<div class="dk-card-body flush">
		<div class="dk-spinner-overlay" id="dkProjLoading"><span class="dk-spin"></span> 加载中…</div>
		<table class="dk-table" id="dkProjTable" style="display:none">
			<thead><tr><th>项目名</th><th>状态</th><th>容器数</th><th>更新时间</th></tr></thead>
			<tbody></tbody>
		</table>
	</div>
</div>
<script>
function esc(s){ return $('<div>').text(s==null?'':String(s)).html(); }
function loadCompose(){
	$('#dkTplLoading').show(); $('#dkTplTable').hide();
	$('#dkProjLoading').show(); $('#dkProjTable').hide();
	dkAjax('compose_list', {}, {silent:true, timeout:60000}).then(function(r){
		var tpls = (r.templates && r.templates.data) ? r.templates.data : (r.templates || []);
		tpls = Array.isArray(tpls) ? tpls : [];
		var tr = '';
		if (!tpls.length) tr = '<tr><td colspan="3" class="dk-empty">暂无 Compose 模板</td></tr>';
		tpls.forEach(function(t){ tr += '<tr><td>'+ esc(t.name || t.title || '-') +'</td><td class="dk-muted">'+ esc(t.desc || t.description || '-') +'</td><td>'+ esc(t.category || t.type || '-') +'</td></tr>'; });
		$('#dkTplTable tbody').html(tr);
		$('#dkTplLoading').hide(); $('#dkTplTable').show();

		var projs = (r.projects && r.projects.data) ? r.projects.data : (r.projects || []);
		projs = Array.isArray(projs) ? projs : [];
		var pr = '';
		if (!projs.length) pr = '<tr><td colspan="4" class="dk-empty">暂无 Docker 项目</td></tr>';
		projs.forEach(function(p){ pr += '<tr><td>'+ esc(p.name || '-') +'</td><td>'+ esc(p.status || '-') +'</td><td>'+ esc(p.container_count || p.count || '-') +'</td><td class="dk-mono">'+ esc(p.updated_at || p.time || '-') +'</td></tr>'; });
		$('#dkProjTable tbody').html(pr);
		$('#dkProjLoading').hide(); $('#dkProjTable').show();
	}).fail(function(){ $('#dkTplLoading').html('<div class="dk-empty"><p>加载失败，请确认节点 Docker 可用</p></div>'); $('#dkProjLoading').html(''); });
}
loadCompose();
</script>
<?php include __DIR__ . '/foot.php'; ?>
