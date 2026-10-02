<?php
/** 本地镜像 */
$active = 'image';
include __DIR__ . '/head.php';
?>
<div class="dk-card">
	<div class="dk-card-head"><h3>本地镜像</h3><button class="dk-btn dk-btn-ghost dk-btn-sm" onclick="loadImages()">刷新</button></div>
	<div class="dk-card-body flush">
		<div class="dk-spinner-overlay" id="dkImgLoading"><span class="dk-spin"></span> 加载中…</div>
		<table class="dk-table" id="dkImgTable" style="display:none">
			<thead><tr><th>镜像</th><th>标签</th><th>大小</th><th>创建时间</th><th>ID</th></tr></thead>
			<tbody></tbody>
		</table>
	</div>
</div>
<script>
function esc(s){ return $('<div>').text(s==null?'':String(s)).html(); }
function fmtSize(s){
	var n = parseInt(s,10)||0;
	if (n < 1048576) return (n/1024).toFixed(1)+' KB';
	if (n < 1073741824) return (n/1048576).toFixed(1)+' MB';
	return (n/1073741824).toFixed(2)+' GB';
}
function loadImages(){
	$('#dkImgLoading').show(); $('#dkImgTable').hide();
	dkAjax('image_list', {}, {silent:true, timeout:60000}).then(function(r){
		var list = r.data && r.data.data ? r.data.data : (r.data || []);
		list = Array.isArray(list) ? list : [];
		var rows = '';
		if (!list.length) rows = '<tr><td colspan="5" class="dk-empty">暂无本地镜像</td></tr>';
		list.forEach(function(img){
			var tags = img.RepoTags || img.repo_tags || img.tags || [];
			if (Array.isArray(tags)) tags = tags.join(', ');
			var size = img.Size || img.size || 0;
			var created = img.Created || img.created || '';
			if (created) { try { created = new Date(created*1000).toLocaleString(); } catch(e){} }
			var id = (img.Id || img.id || '').toString().slice(0,19);
			rows += '<tr><td class="dk-mono">'+ esc((tags||'').split(':')[0] || '-') +'</td><td class="dk-mono">'+ esc((tags||'').split(':')[1] || '-') +'</td><td>'+ esc(fmtSize(size)) +'</td><td class="dk-mono">'+ esc(created || '-') +'</td><td class="dk-mono">'+ esc(id) +'</td></tr>';
		});
		$('#dkImgTable tbody').html(rows);
		$('#dkImgLoading').hide(); $('#dkImgTable').show();
	}).fail(function(){ $('#dkImgLoading').html('<div class="dk-empty"><p>加载失败，请确认节点 Docker 可用</p></div>'); });
}
loadImages();
</script>
<?php include __DIR__ . '/foot.php'; ?>
