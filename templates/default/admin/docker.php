<?php
/** Docker 管理视图（节点 / 用户 / 套餐） */
mnbt_admin_include('head');
$set = isset($_GET['gn']) ? $_GET['gn'] : 'node';
?>
<script type="text/javascript" src="<?= mnbt_asset_url('js/jquery-confirm/jquery-confirm.min.js') ?>"></script>
<script type="text/javascript" src="<?= mnbt_asset_url('js/bootstrap-table/bootstrap-table.min.js') ?>"></script>
<script type="text/javascript" src="<?= mnbt_asset_url('js/bootstrap-table/locale/bootstrap-table-zh-CN.min.js') ?>"></script>
<style>
.dk-admin-card { background:#fff; border-radius:6px; box-shadow:0 1px 4px rgba(0,0,0,.06); margin:15px; }
.dk-admin-card .card-tool { padding:12px 16px; border-bottom:1px solid #eee; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px; }
.dk-admin-card .card-body { padding:0; }
.dk-tag-sm { display:inline-block; padding:2px 8px; border-radius:10px; font-size:12px; }
.dk-tag-active { background:#e6f7ee; color:#2bb673; }
.dk-tag-paused { background:#fff6e6; color:#e6a23c; }
.dk-tag-expired { background:#fdeeee; color:#e0584b; }
.dk-tag-pruned { background:#eee; color:#888; }
.dk-tag-none { background:#eef0f4; color:#888; }
</style>

<?php if ($set === 'node'): ?>
<!-- ============ Docker 节点管理 ============ -->
<div class="dk-admin-card">
	<div class="card-tool">
		<h5 style="margin:0">Docker 节点（独立宝塔 Docker 面板实例）</h5>
		<div style="display:flex;gap:8px">
			<button type="button" class="btn btn-primary btn-sm js-create-tab" data-title="添加 Docker 节点" data-url="add.php?gn=dknode"><i class="mdi mdi-plus"></i> 添加节点</button>
			<button class="btn btn-default btn-sm" onclick="dkNodeReload()">刷新</button>
		</div>
	</div>
	<div class="card-body">
		<table id="dkNodeTable"></table>
	</div>
</div>

<!-- 节点容器查询区 -->
<div class="dk-admin-card">
	<div class="card-tool">
		<h5 style="margin:0">节点容器查询</h5>
		<div style="display:flex;gap:8px;align-items:center">
			<select id="dkNodeSel" class="form-control" style="width:auto;min-width:220px"><option value="">请选择节点</option></select>
			<button class="btn btn-primary btn-sm" onclick="dkNodeLoad()">查询</button>
		</div>
	</div>
	<div class="card-body" style="padding:16px">
		<div id="dkNodeInfo"></div>
		<table class="table table-bordered" id="dkContainerTable" style="margin-top:14px">
			<thead><tr><th>容器名</th><th>镜像</th><th>状态</th><th>端口</th><th>创建时间</th></tr></thead>
			<tbody><tr><td colspan="5" class="text-center text-muted">请选择节点后查询</td></tr></tbody>
		</table>
	</div>
</div>
<script>
$(function(){
	$('#dkNodeTable').bootstrapTable({
		url:'./ajax.php', method:'post', contentType:'application/x-www-form-urlencoded', pagination:false, search:true,
		queryParams: function(){ return {gn:'docker_node_list'}; },
		responseHandler: function(res){ return res.data || []; },
		columns: [
			{field:'id', title:'ID', width:50},
			{field:'name', title:'节点名称'},
			{field:'btip', title:'面板地址', formatter:function(v,row){ return v+':'+row.btdk+' '+(row.ptl==='true'?'(HTTPS)':'(HTTP)'); }},
			{field:'btmy', title:'接口密钥', formatter:function(v){ return v?'<span class="text-muted">已设置</span>':'<span class="text-danger">未设置</span>'; }},
			{field:'ktmy', title:'调用密钥', formatter:function(v){ return v?'<span class="text-muted">已设置</span>':'-'; }},
			{field:'qk', title:'状态', formatter:function(v){ return v==='true'?'<span class="dk-tag-sm dk-tag-active">启用</span>':'<span class="dk-tag-sm dk-tag-none">禁用</span>'; }},
			{field:'date', title:'添加时间'},
			{field:'id', title:'操作', formatter:function(v){
				return '<div class="btn-group btn-group-sm">'+
					'<button class="btn btn-default" onclick="dkNodeEdit('+v+')">编辑</button>'+
					'<button class="btn btn-danger" onclick="dkNodeDel('+v+')">删除</button>'+
				'</div>';
			}}
		]
	});
	dkLoadNodeSel();
});
function dkNodeReload(){ $('#dkNodeTable').bootstrapTable('refresh'); dkLoadNodeSel(); }
function dkLoadNodeSel(){
	$.post('./ajax.php', {gn:'docker_options'}, function(html){
		var opt = JSON.parse(html);
		var opts = '<option value="">请选择节点</option>';
		(opt.nodes||[]).forEach(function(n){ opts += '<option value="'+n.id+'">'+n.name+' ('+n.btip+')</option>'; });
		$('#dkNodeSel').html(opts);
	});
}
function dkNodeEdit(id){
	$.confirm({
		title: id?'编辑节点':'添加节点', columnClass:'m', content:
		'<form class="form-horizontal">'+
			'<input type="hidden" id="dk_nid" value="'+id+'">'+
			'<div class="form-group"><label>节点名称 *</label><input class="form-control" id="dk_name" placeholder="如：北京节点A"></div>'+
			'<div class="form-group"><label>宝塔面板地址 *</label><input class="form-control" id="dk_btip" placeholder="IP 或域名"></div>'+
			'<div class="form-group"><label>端口</label><input class="form-control" id="dk_btdk" value="8888"></div>'+
			'<div class="form-group"><label>HTTPS</label><select class="form-control" id="dk_ptl"><option value="false">否</option><option value="true">是</option></select></div>'+
			'<div class="form-group"><label>宝塔接口密钥 *</label><textarea class="form-control" id="dk_btmy" rows="2" placeholder="宝塔面板 API 密钥"></textarea></div>'+
			'<div class="form-group"><label>调用密钥（外部API鉴权）</label><input class="form-control" id="dk_ktmy" placeholder="留空则不校验调用密钥"></div>'+
			'<div class="form-group"><label>二级验证密钥</label><input class="form-control" id="dk_qmk" placeholder="与调用密钥组合 md5 校验"></div>'+
			'<div class="form-group"><label>启用</label><select class="form-control" id="dk_qk"><option value="true">启用</option><option value="false">禁用</option></select></div>'+
		'</form>',
		buttons:{ ok:{text:'保存',btnClass:'btn-primary',action:function(){
			var d={gn:id?'docker_node_edit':'docker_node_add', id:id, name:$('#dk_name').val(), btip:$('#dk_btip').val(), btdk:$('#dk_btdk').val(), ptl:$('#dk_ptl').val(), btmy:$('#dk_btmy').val(), ktmy:$('#dk_ktmy').val(), qmk:$('#dk_qmk').val(), qk:$('#dk_qk').val()};
			if(!d.name||!d.btip||!d.btmy){ $.alert('节点名、面板地址、接口密钥必填'); return false; }
			$.post('./ajax.php', d, function(r){ var j=JSON.parse(r); $.alert(j.msg||j.code); if((j.msg||'').indexOf('成功')>=0) dkNodeReload(); });
			return true;
		}}, cancel:{text:'取消'} }
	});
}
function dkNodeDel(id){ $.confirm({ title:'确认删除', content:'删除该 Docker 节点？节点下有用户时无法删除。', buttons:{ ok:{text:'删除',btnClass:'btn-danger',action:function(){ $.post('./ajax.php',{gn:'docker_node_del',id:id},function(r){var j=JSON.parse(r);$.alert(j.msg||j.code); if((j.msg||'').indexOf('成功')>=0) dkNodeReload();}); }}, cancel:{text:'取消'} } }); }
function dkNodeLoad(){
	var nid = $('#dkNodeSel').val();
	if(!nid){ $.alert('请选择节点'); return; }
	$('#dkNodeInfo').html('<i class="mdi mdi-loading mdi-spin"></i> 加载配置…');
	$.post('./ajax.php', {gn:'docker_node_config', node_id:nid}, function(r){
		var c = JSON.parse(r); var d = (c.data&&c.data.data)?c.data.data:c.data;
		var installed = d && (d.docker_installed || (d.service_status&&d.service_status.docker));
		$('#dkNodeInfo').html('<div class="alert '+(installed?'alert-success':'alert-warning')+'">Docker '+ (installed?'已安装':'未安装或异常') + (d&&d.service_status?(' · Compose: '+(d.service_status.docker_compose?'已安装':'未安装')):'') + '</div>');
	});
	$('#dkContainerTable tbody').html('<tr><td colspan="5" class="text-center"><i class="mdi mdi-loading mdi-spin"></i> 加载中…</td></tr>');
	$.post('./ajax.php', {gn:'docker_node_containers', node_id:nid}, function(r){
		var c = JSON.parse(r); var list = (c.data&&c.data.data)?c.data.data:(c.data||[]);
		list = Array.isArray(list)?list:[];
		if(!list.length){ $('#dkContainerTable tbody').html('<tr><td colspan="5" class="text-center text-muted">该节点暂无容器</td></tr>'); return; }
		var rows = '';
		list.forEach(function(x){
			var name = (x.name||(x.Names&&x.Names[0])||''); if(name.charAt(0)==='/') name=name.slice(1);
			rows += '<tr><td class="dk-mono">'+name+'</td><td class="dk-mono">'+(x.image||x.Image||'')+'</td><td>'+(x.status||x.State||'')+'</td><td class="dk-mono">'+(x.ports||x.Ports||'')+'</td><td class="dk-mono">'+(x.time||x.Created||'')+'</td></tr>';
		});
		$('#dkContainerTable tbody').html(rows);
	});
}
</script>

<?php elseif ($set === 'user'): ?>
<!-- ============ Docker 用户管理 ============ -->
<div class="dk-admin-card">
	<div class="card-tool">
		<h5 style="margin:0">Docker 用户</h5>
		<button class="btn btn-primary btn-sm" onclick="dkUserEdit(0)"><i class="mdi mdi-plus"></i> 添加用户</button>
	</div>
	<div class="card-body">
		<table id="dkUserTable"></table>
	</div>
</div>
<script>
function dkStatusTag(qk){
	var map = {active:['正常','dk-tag-active'],paused:['已暂停','dk-tag-paused'],expired:['已到期','dk-tag-expired'],pruned:['已清理','dk-tag-pruned']};
	var m = map[qk] || ['未知','dk-tag-none'];
	return '<span class="dk-tag-sm '+m[1]+'">'+m[0]+'</span>';
}
$(function(){
	$('#dkUserTable').bootstrapTable({
		url: './ajax.php', method:'post', contentType:'application/x-www-form-urlencoded',
		pagination:true, pageSize:15, pageList:[15,30,50], search:true, showRefresh:true, sidePagination:'client',
		queryParams: function(){ return {gn:'docker_user_list'}; },
		responseHandler: function(res){ return res.data || []; },
		columns: [
			{field:'id', title:'ID', width:60},
			{field:'username', title:'账号'},
			{field:'email', title:'邮箱'},
			{field:'node_name', title:'节点'},
			{field:'plan_name', title:'套餐'},
			{field:'app_name', title:'应用'},
			{field:'container_status', title:'容器状态', formatter:function(v){ var m={none:'未创建',creating:'创建中',running:'运行中',stopped:'已停止',failed:'失败'}; return '<span class="dk-tag-sm dk-tag-'+(v==='running'?'active':(v==='none'?'none':(v==='creating'?'paused':'expired')))+'">'+(m[v]||v)+'</span>'; }},
			{field:'data', title:'开通'},
			{field:'datae', title:'到期', formatter:function(v){ return v==='0000-00-00'?'永久':v; }},
			{field:'qk', title:'状态', formatter:dkStatusTag},
			{field:'id', title:'操作', formatter:function(v,row){
				return '<div class="btn-group btn-group-sm">'+
					'<button class="btn btn-default" onclick="dkUserEdit('+v+')">编辑</button>'+
					'<button class="btn btn-default" onclick="dkUserReset('+v+')">改密</button>'+
					(row.qk==='active'?'<button class="btn btn-warning" onclick="dkUserPause('+v+')">暂停</button>':'<button class="btn btn-success" onclick="dkUserResume('+v+')">恢复</button>')+
					'<button class="btn btn-danger" onclick="dkUserDel('+v+')">删除</button>'+
				'</div>';
			}}
		]
	});
});
function dkReload(){ $('#dkUserTable').bootstrapTable('refresh'); }
function dkUserEdit(id){
	$.post('./ajax.php', {gn:'docker_options'}, function(html){
		var opt = JSON.parse(html);
		var nodeOpts = (opt.nodes||[]).map(function(n){ return '<option value="'+n.id+'">'+n.name+' ('+n.btip+')</option>'; }).join('');
		var planOpts = '<option value="0">无套餐</option>' + (opt.plans||[]).map(function(p){ return '<option value="'+p.id+'">'+p.name+' ('+p.cpu_max+'核/'+p.mem_max+'MB/¥'+p.jg+')</option>'; }).join('');
		$.confirm({
			title: id?'编辑用户':'添加用户', columnClass:'m', content:
			'<form class="form-horizontal">'+
				'<input type="hidden" id="dk_uid" value="'+id+'">'+
				(id?'':'<div class="form-group"><label>账号</label><input class="form-control" id="dk_username"></div>')+
				(id?'':'<div class="form-group"><label>密码</label><input class="form-control" id="dk_password" type="password"></div>')+
				'<div class="form-group"><label>邮箱</label><input class="form-control" id="dk_email"></div>'+
				'<div class="form-group"><label>Docker 节点 *</label><select class="form-control" id="dk_ssbt">'+nodeOpts+'</select></div>'+
				'<div class="form-group"><label>套餐</label><select class="form-control" id="dk_plan_id">'+planOpts+'</select></div>'+
				'<div class="form-group"><label>到期(0000-00-00=永久)</label><input class="form-control" id="dk_datae" value="0000-00-00"></div>'+
				(id?'<div class="form-group"><label>状态</label><select class="form-control" id="dk_qk"><option value="active">正常</option><option value="paused">暂停</option><option value="expired">到期</option></select></div>':'')+
			'</form>',
			buttons: {
				ok: { text:'保存', btnClass:'btn-primary', action:function(){
					var d = {gn:id?'docker_user_edit':'docker_user_add', id:id};
					d.username=$('#dk_username').val(); d.password=$('#dk_password').val(); d.email=$('#dk_email').val();
					d.ssbt=$('#dk_ssbt').val(); d.plan_id=$('#dk_plan_id').val(); d.datae=$('#dk_datae').val();
					if(id) d.qk=$('#dk_qk').val();
					if(!id && (!d.username || !d.password)){ $.alert('账号密码必填'); return false; }
					$.post('./ajax.php', d, function(r){ var j=JSON.parse(r); $.alert(j.msg||j.code); if((j.msg||'').indexOf('成功')>=0) dkReload(); });
					return true;
				}},
				cancel: { text:'取消' }
			}
		});
	});
}
function dkUserReset(id){
	$.confirm({ title:'重置密码', content:'<input class="form-control" id="dk_newpwd" placeholder="新密码">', buttons:{ ok:{text:'重置',btnClass:'btn-primary',action:function(){ var p=$('#dk_newpwd').val(); if(!p){$.alert('密码不能为空');return false;} $.post('./ajax.php',{gn:'docker_user_reset',id:id,password:p},function(r){var j=JSON.parse(r);$.alert(j.msg||j.code);}); return true; }}, cancel:{text:'取消'} } });
}
function dkUserDel(id){ $.confirm({ title:'确认删除', content:'删除用户不会自动删除其容器，确认删除？', buttons:{ ok:{text:'删除',btnClass:'btn-danger',action:function(){ $.post('./ajax.php',{gn:'docker_user_del',id:id},function(r){var j=JSON.parse(r);$.alert(j.msg||j.code); if((j.msg||'').indexOf('成功')>=0) dkReload();}); }}, cancel:{text:'取消'} } }); }
function dkUserPause(id){ $.post('./ajax.php',{gn:'docker_user_pause',id:id},function(r){var j=JSON.parse(r);$.alert(j.msg||j.code); dkReload();}); }
function dkUserResume(id){ $.post('./ajax.php',{gn:'docker_user_resume',id:id},function(r){var j=JSON.parse(r);$.alert(j.msg||j.code); dkReload();}); }
</script>

<?php elseif ($set === 'plan'): ?>
<!-- ============ 套餐管理 ============ -->
<div class="dk-admin-card">
	<div class="card-tool">
		<h5 style="margin:0">Docker 套餐</h5>
		<button class="btn btn-primary btn-sm" onclick="dkPlanEdit(0)"><i class="mdi mdi-plus"></i> 添加套餐</button>
	</div>
	<div class="card-body">
		<table id="dkPlanTable"></table>
	</div>
</div>
<script>
$(function(){
	$('#dkPlanTable').bootstrapTable({
		url:'./ajax.php', method:'post', contentType:'application/x-www-form-urlencoded', pagination:false, search:true,
		queryParams: function(){ return {gn:'docker_plan_list'}; },
		responseHandler: function(res){ return res.data || []; },
		columns: [
			{field:'id', title:'ID', width:60},
			{field:'name', title:'套餐名'},
			{field:'jc', title:'介绍', formatter:function(v){ return v||'-'; }},
			{field:'cpu_max', title:'CPU核'},
			{field:'mem_max', title:'内存MB'},
			{field:'jg', title:'价格'},
			{field:'qk', title:'上架', formatter:function(v){ return v==='true'?'<span class="dk-tag-sm dk-tag-active">上架</span>':'<span class="dk-tag-sm dk-tag-none">下架</span>'; }},
			{field:'id', title:'操作', formatter:function(v){ return '<div class="btn-group btn-group-sm"><button class="btn btn-default" onclick="dkPlanEdit('+v+')">编辑</button><button class="btn btn-danger" onclick="dkPlanDel('+v+')">删除</button></div>'; }}
		]
	});
});
function dkPlanReload(){ $('#dkPlanTable').bootstrapTable('refresh'); }
function dkPlanEdit(id){
	$.confirm({
		title: id?'编辑套餐':'添加套餐', columnClass:'m', content:
		'<form class="form-horizontal">'+
			'<input type="hidden" id="dk_pid" value="'+id+'">'+
			'<div class="form-group"><label>套餐名</label><input class="form-control" id="dk_name"></div>'+
			'<div class="form-group"><label>介绍</label><textarea class="form-control" id="dk_jc" rows="2"></textarea></div>'+
			'<div class="form-group"><label>CPU 核上限</label><input class="form-control" id="dk_cpu" value="1" type="number" step="0.1"></div>'+
			'<div class="form-group"><label>内存 MB 上限</label><input class="form-control" id="dk_mem" value="512" type="number" step="32"></div>'+
			'<div class="form-group"><label>价格</label><input class="form-control" id="dk_jg" value="0"></div>'+
			'<div class="form-group"><label>上架</label><select class="form-control" id="dk_qk"><option value="true">上架</option><option value="false">下架</option></select></div>'+
		'</form>',
		buttons:{ ok:{text:'保存',btnClass:'btn-primary',action:function(){
			var d={gn:id?'docker_plan_edit':'docker_plan_add', id:id, name:$('#dk_name').val(), jc:$('#dk_jc').val(), cpu_max:$('#dk_cpu').val(), mem_max:$('#dk_mem').val(), jg:$('#dk_jg').val(), qk:$('#dk_qk').val()};
			if(!d.name){ $.alert('套餐名必填'); return false; }
			$.post('./ajax.php', d, function(r){ var j=JSON.parse(r); $.alert(j.msg||j.code); if((j.msg||'').indexOf('成功')>=0) dkPlanReload(); });
			return true;
		}}, cancel:{text:'取消'} }
	});
}
function dkPlanDel(id){ $.confirm({ title:'确认删除', content:'删除套餐？', buttons:{ ok:{text:'删除',btnClass:'btn-danger',action:function(){ $.post('./ajax.php',{gn:'docker_plan_del',id:id},function(r){var j=JSON.parse(r);$.alert(j.msg||j.code); if((j.msg||'').indexOf('成功')>=0) dkPlanReload();}); }}, cancel:{text:'取消'} } }); }
</script>

<?php endif; ?>
