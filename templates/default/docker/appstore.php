<?php
/** 应用商店 */
$active = 'appstore';
$cpu_max = $plan ? $plan['cpu_max'] : '1';
$mem_max = $plan ? $plan['mem_max'] : '512';
include __DIR__ . '/head.php';
?>
<?php if (!empty($me['container_id']) || !empty($me['service_name'])): ?>
	<div class="dk-alert dk-alert-warn">您已创建容器，单容器模型下无法再次创建。如需更换应用，请联系管理员先删除现有容器。</div>
<?php endif; ?>

<div class="dk-card">
	<div class="dk-card-head">
		<h3>应用商店</h3>
		<input class="dk-input" id="dkAppSearch" placeholder="搜索应用名称…" style="width:260px;padding:8px 14px">
	</div>
	<div class="dk-card-body">
		<div class="dk-spinner-overlay" id="dkAppLoading"><span class="dk-spin"></span> 正在加载应用列表…</div>
		<div class="dk-grid" id="dkAppGrid" style="display:none"></div>
	</div>
</div>

<script>
(function(){
	var APPS = [];
	var CPU_MAX = <?= json_encode((float)$cpu_max) ?>;
	var MEM_MAX = <?= json_encode((float)$mem_max) ?>;
	var HAS_CONTAINER = <?php echo (!empty($me['container_id']) || !empty($me['service_name'])) ? 'true' : 'false'; ?>;

	function esc(s){ return $('<div>').text(s == null ? '' : String(s)).html(); }
	function appIconChar(name){
		return (name || '?').charAt(0).toUpperCase();
	}
	function typeLabel(t){
		var map = { BuildWebsite:'建站', Database:'数据库', Storage:'存储', DevTool:'开发工具', Media:'媒体', Other:'其他' };
		return map[t] || t || '应用';
	}

	function renderGrid(list){
		var grid = $('#dkAppGrid');
		if (!list.length) { grid.html('<div class="dk-empty" style="grid-column:1/-1"><div class="dk-empty-ico">空的</div><p>没有匹配的应用</p></div>'); return; }
		var html = '';
		list.forEach(function(app){
			var versions = (app.appversion || []).map(function(v){ return v.m_version; }).join(' / ') || '-';
			html += '<div class="dk-app-card">' +
				'<div class="dk-app-head"><div class="dk-app-icon">'+ esc(appIconChar(app.appname)) +'</div>' +
				'<div><h4>'+ esc(app.apptitle || app.appname) +'</h4><div class="dk-app-type">'+ esc(typeLabel(app.apptype)) +' · v'+ esc(versions) +'</div></div></div>' +
				'<div class="dk-app-desc">'+ esc(app.desc || app.description || ('应用标识：'+ app.appname)) +'</div>' +
				'<div class="dk-app-foot"><span class="dk-muted dk-mono" style="font-size:12px">'+ esc(app.appname) +'</span>' +
				'<button class="dk-btn dk-btn-sm" onclick="dkOpenApp(\''+ esc(app.appname) +'\')">安装</button></div>' +
			'</div>';
		});
		grid.html(html);
	}

	function loadApps(){
		dkAjax('app_list', {}, {silent:true, timeout:90000}).then(function(r){
			APPS = r.data && r.data.data ? r.data.data : (r.data || []);
			$('#dkAppLoading').hide();
			$('#dkAppGrid').show();
			renderGrid(APPS);
		}).fail(function(){
			$('#dkAppLoading').html('<div class="dk-empty"><div class="dk-empty-ico">⚠</div><h4>加载失败</h4><p>无法获取应用列表，请确认节点已安装 Docker 并初始化应用商店。</p></div>');
		});
	}

	$('#dkAppSearch').on('input', function(){
		var q = $(this).val().toLowerCase();
		renderGrid(APPS.filter(function(a){
			var t = ((a.appname||'')+' '+(a.apptitle||'')+' '+(a.apptype||'')).toLowerCase();
			return t.indexOf(q) !== -1;
		}));
	});

	window.dkOpenApp = function(appname){
		if (HAS_CONTAINER) { dkToast('您已创建容器，无法再次创建', 'error'); return; }
		var app = null;
		for (var i=0;i<APPS.length;i++){ if (APPS[i].appname === appname){ app = APPS[i]; break; } }
		if (!app) { dkToast('应用不存在', 'error'); return; }
		// 检查依赖
		dkOpenInstallModal(app);
	};

	function dkRandomStr(len){
		var chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789';
		var s = '';
		for (var i=0; i<(len||12); i++) s += chars.charAt(Math.floor(Math.random()*chars.length));
		return s;
	}
	function dkRandomUser(len){
		var chars = 'abcdefghijkmnpqrstuvwxyz23456789';
		var s = '';
		for (var i=0; i<(len||10); i++) s += chars.charAt(Math.floor(Math.random()*chars.length));
		return s;
	}
	function dkRandomPort(){
		return Math.floor(Math.random() * 50000) + 10000;
	}
	function fieldInput(key, def, desc, type){
		var t = type || 'string';
		// 标签：优先用 desc（宝塔 field.name 会作为 desc 传入），其次用 key 去下划线
		var label = desc ? esc(desc) : esc(key).replace(/_/g, ' ');
		var isPwdField = (t === 'password' || t === 'secret' || /password|secret|passwd|pwd/i.test(key));
		var isUserField = (/user|username|admin/i.test(key) && !/host|ip|email|domain/i.test(key));
		var isPortField = (t === 'port' || /port/i.test(key));
		if (def === 'random') def = dkRandomStr(12);
		else if (isPwdField && (def === '' || def == null)) def = dkRandomStr(12);
		else if (isUserField && (def === '' || def == null)) def = dkRandomUser(10);
		else if (isPortField && (def === '' || def == null || def === '0')) def = dkRandomPort();
		if (t === 'checkbox') {
			var checked = def === true || def === 'true' || def === '1' ? 'checked' : '';
			return '<div class="dk-field dk-field-full"><label>'+ label +'</label><label><input type="checkbox" name="'+ esc(key) +'" value="1" '+ checked +'> 启用</label></div>';
		}
		if (t === 'textarea') {
			return '<div class="dk-field dk-field-full"><label>'+ label +'</label><textarea class="dk-input" name="'+ esc(key) +'" rows="2">'+ esc(def || '') +'</textarea></div>';
		}
		var inputType = (t === 'port' || t === 'number') ? 'number' : (isPwdField ? 'password' : 'text');
		return '<div class="dk-field"><label>'+ label +'</label><input class="dk-input" type="'+ inputType +'" name="'+ esc(key) +'" value="'+ esc(def || '') +'"></div>';
	}

	function dkOpenInstallModal(app){
		var versions = app.appversion || [];
		// 构建版本下拉选项（version 字段专用）
		var verOpts = '';
		versions.forEach(function(v){
			var sub = (v.s_version || []);
			if (sub && sub.length) {
				sub.forEach(function(sv){ verOpts += '<option value="'+ esc(v.m_version) +'|'+ esc(sv) +'">'+ esc(v.m_version) +'.'+ esc(sv) +'</option>'; });
			} else {
				// 无子版本时 s_version 留空：宝塔后端会用 m_version 作为镜像 tag
				// 之前 fallback 成 '0' 会导致 frps 这类应用拼出 latest.0 无效 tag
				verOpts += '<option value="'+ esc(v.m_version) +'|">'+ esc(v.m_version) +'</option>';
			}
		});
		// version 字段渲染为下拉框，而非普通输入
		function versionFieldHtml(){
			return '<div class="dk-field"><label>版本选择</label><select class="dk-select" name="dk_version">'+ verOpts +'</select></div>';
		}

		// 字段标签：优先用 field.name（中文），其次用 desc，最后用 key 去掉下划线
		function fieldLabel(key, name, desc){
			if (name) return esc(name);
			if (desc) return esc(desc);
			return esc(key).replace(/_/g, ' ');
		}

		var envHtml = '';
		// 系统字段 + 宝塔内部管理字段（用户无需填写）
		var seenKeys = {'cpus':1, 'memory_limit':1, 'allow_access':1, 'dk_version':1, 'version':1, 'app_path':1, 'host_ip':1};
		(app.env || []).forEach(function(e){
			var k = e.key; if (!k || seenKeys[k]) return; seenKeys[k] = 1;
			envHtml += fieldInput(k, e.default || '', e.desc, e.type);
		});
		(app.field || []).forEach(function(f){
			var k = f.attr; if (!k || seenKeys[k]) return; seenKeys[k] = 1;
			envHtml += fieldInput(k, f.default || '', f.name, f.type);
		});

		var depHtml = '';
		if (app.depend && app.depend.length) {
			depHtml = '<div class="dk-alert dk-alert-warn">此应用依赖：'+ app.depend.map(function(d){ return esc((d.appname||[]).join(',')); }).join('、') +'，请确保依赖应用已安装。</div>';
		}

		var body =
			'<div class="dk-app-head" style="margin-bottom:14px"><div class="dk-app-icon">'+ esc(appIconChar(app.appname)) +'</div>' +
			'<div><h4>'+ esc(app.apptitle||app.appname) +'</h4><div class="dk-app-type">'+ esc(app.desc||'') +'</div></div></div>' +
			depHtml +
			'<form id="dkInstallForm">' +
				// version 下拉（替换原来的空白输入框）
				versionFieldHtml() +
				'<div class="dk-form-grid">' +
					'<div class="dk-field"><label>CPU 核数（0=不限制，上限 '+ CPU_MAX +'，整数）</label><input class="dk-input" type="number" step="1" min="0" max="'+ CPU_MAX +'" name="cpus" value="0"></div>' +
					'<div class="dk-field"><label>内存 MB（0=不限制，上限 '+ MEM_MAX +'）</label><input class="dk-input" type="number" step="32" min="0" max="'+ MEM_MAX +'" name="memory_limit" value="0"></div>' +
					'<div class="dk-field dk-field-full"><label>允许外网访问</label><label><input type="checkbox" name="allow_access" value="1" checked> 允许（通过主机IP+端口访问，设了域名可不勾）</label></div>' +
					envHtml +
				'</div>' +
			'</form>';

		dkModal(body, '安装应用：'+ (app.apptitle || app.appname));
		$('#dkModalMask .dk-modal').append('<div class="dk-modal-foot"><button class="dk-btn dk-btn-ghost" onclick="dkCloseModal()">取消</button><button class="dk-btn" id="dkInstallBtn">确认安装</button></div>');
		$('#dkInstallBtn').on('click', function(){ doInstall(app); });
	}

	function doInstall(app){
		var btn = $('#dkInstallBtn');
		var ver = $('#dkInstallForm [name=dk_version]').val().split('|');
		var fd = new FormData($('#dkInstallForm')[0]);
		fd.append('app_name', app.appname);
		fd.append('m_version', ver[0] || '');
		// s_version 留空（无子版本的应用）：宝塔后端会只用 m_version 作为镜像 tag
		fd.append('s_version', ver[1] || '');
		// 强制配额上限 + cpus 取整（宝塔后端用 int() 转换）
		var cpus = parseInt(parseFloat(fd.get('cpus')) || 0, 10);
		if (cpus > CPU_MAX) cpus = Math.floor(CPU_MAX);
		fd.set('cpus', String(cpus));
		if (parseFloat(fd.get('memory_limit')) > MEM_MAX) fd.set('memory_limit', String(MEM_MAX));
		btn.prop('disabled', true).html('<span class="dk-spin"></span> 提交中…');
		dkAjax('app_create', fd, {timeout:120000}).then(function(r){
			dkCloseModal();
			dkToast('创建请求已提交，正在初始化…', 'success');
			setTimeout(function(){ window.location.href = 'console.php'; }, 1000);
		}).fail(function(){
			btn.prop('disabled', false).text('确认安装');
		});
	}

	loadApps();
})();
</script>
<?php include __DIR__ . '/foot.php'; ?>
