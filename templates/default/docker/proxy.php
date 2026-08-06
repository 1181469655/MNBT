<?php
/** 反向代理管理 */
$active = 'proxy';
$proxy_max = $plan ? (int)$plan['proxy_max'] : 0;
include __DIR__ . '/head.php';
?>
<?php if (empty($me['container_id']) && empty($me['service_name'])): ?>
	<div class="dk-alert dk-alert-warn">您还没有容器，请先在应用商店创建容器后再配置反向代理。</div>
<?php endif; ?>

<div class="dk-card">
	<div class="dk-card-head">
		<h3>反向代理</h3>
		<button class="dk-btn dk-btn-sm" onclick="dkProxyAdd()" id="dkProxyAddBtn">+ 添加规则</button>
	</div>
	<div class="dk-card-body">
		<div class="dk-spinner-overlay" id="dkProxyLoading"><span class="dk-spin"></span> 加载中…</div>
		<div id="dkProxyList" style="display:none"></div>
	</div>
</div>

<script>
(function(){
	var PROXY_MAX = <?= json_encode($proxy_max) ?>;
	var HAS_CONTAINER = <?php echo (!empty($me['container_id']) || !empty($me['service_name'])) ? 'true' : 'false'; ?>;
	var CONTAINER_IP = <?= json_encode($node['btip'] ?? '') ?>;

	function esc(s){ return $('<div>').text(s == null ? '' : String(s)).html(); }

	function loadList(){
		$('#dkProxyLoading').show(); $('#dkProxyList').hide();
		dkAjax('proxy_list', {}, {silent:true}).then(function(r){
			$('#dkProxyLoading').hide(); $('#dkProxyList').show();
			var list = r.data || [];
			if (!list.length) {
				$('#dkProxyList').html('<div class="dk-empty"><div class="dk-empty-ico">⇄</div><h4>暂无反向代理规则</h4><p>点击上方按钮添加，将域名指向容器端口。</p></div>');
				return;
			}
			var html = '<table class="dk-table"><thead><tr><th>站点名</th><th>代理目标</th><th>路径</th><th>状态</th><th>备注</th><th>操作</th></tr></thead><tbody>';
			list.forEach(function(p){
				var statusTag = p.status === '1' ? '<span class="dk-tag dk-tag-running">运行中</span>' : '<span class="dk-tag dk-tag-stopped">已停止</span>';
				html += '<tr>' +
					'<td class="dk-mono">'+ esc(p.name) +'</td>' +
					'<td class="dk-mono">'+ esc(p.proxy_pass || '') +'</td>' +
					'<td>'+ esc(p.path || '/') +'</td>' +
					'<td>'+ statusTag +'</td>' +
					'<td>'+ esc(p.ps || '-') +'</td>' +
					'<td><button class="dk-btn dk-btn-danger dk-btn-sm" onclick="dkProxyDel('+ p.id +', \''+ esc(p.name) +'\')">删除</button></td>' +
				'</tr>';
			});
			html += '</tbody></table>';
			$('#dkProxyList').html(html);
		});
	}

	window.dkProxyAdd = function(){
		if (!HAS_CONTAINER) { dkToast('请先创建容器', 'error'); return; }
		if (PROXY_MAX > 0) {
			dkAjax('proxy_list', {}, {silent:true}).then(function(r){
				var count = (r.data || []).length;
				if (count >= PROXY_MAX) { dkToast('反向代理数量已达上限（'+PROXY_MAX+'个）', 'error'); return; }
				showAddForm();
			});
		} else {
			showAddForm();
		}
	};

	function showAddForm(){
		var body =
			'<form id="dkProxyForm">' +
				'<div class="dk-field"><label>域名 *</label><input class="dk-input" name="domains" placeholder="多个域名用换行分隔，如 example.com"></div>' +
				'<div class="dk-field"><label>代理目标 *</label><input class="dk-input" name="proxy_pass" placeholder="http://'+ esc(CONTAINER_IP) +':端口"></div>' +
				'<div class="dk-field"><label>代理路径</label><input class="dk-input" name="proxy_path" value="/" placeholder="/"></div>' +
				'<div class="dk-field"><label>备注</label><input class="dk-input" name="remark" placeholder="可选"></div>' +
			'</form>';
		dkModal(body, '添加反向代理');
		$('#dkModalMask .dk-modal').append('<div class="dk-modal-foot"><button class="dk-btn dk-btn-ghost" onclick="dkCloseModal()">取消</button><button class="dk-btn" id="dkProxySubmit">确认添加</button></div>');
		$('#dkProxySubmit').on('click', function(){
			var btn = $(this);
			var fd = new FormData($('#dkProxyForm')[0]);
			var domains = (fd.get('domains') || '').trim();
			var proxy_pass = (fd.get('proxy_pass') || '').trim();
			if (!domains || !proxy_pass) { dkToast('域名和代理目标不能为空', 'error'); return; }
			btn.prop('disabled', true).html('<span class="dk-spin"></span> 提交中…');
			dkAjax('proxy_create', fd, {timeout:30000}).then(function(r){
				dkCloseModal();
				dkToast('反向代理创建成功', 'success');
				loadList();
			}).fail(function(){
				btn.prop('disabled', false).text('确认添加');
			});
		});
	}

	window.dkProxyDel = function(id, name){
		if (!confirm('确定删除反向代理 ' + name + ' 吗？')) return;
		dkAjax('proxy_delete', {id: id, site_name: name}).then(function(r){
			dkToast('已删除', 'success');
			loadList();
		});
	};

	loadList();
})();
</script>
<?php include __DIR__ . '/foot.php'; ?>