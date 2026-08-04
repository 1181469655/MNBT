<?php
/** 我的容器（单容器模型） */
$active = 'console';
include __DIR__ . '/head.php';
?>
<div id="dkConsoleView">
	<div class="dk-spinner-overlay"><span class="dk-spin"></span> 加载中…</div>
</div>

<script>
(function(){
	var pollTimer = null;

	function renderEmpty(){
		return '<div class="dk-card"><div class="dk-card-body"><div class="dk-empty">' +
			'<div class="dk-empty-ico">▢</div>' +
			'<h4>您还没有容器</h4>' +
			'<p>每个账户可创建一个容器，前往应用商店选择镜像或应用即可开通。</p>' +
			'<a class="dk-btn" href="appstore.php">前往应用商店</a>' +
			'</div></div></div>';
	}

	function statusTag(s){
		var map = { running: ['运行中','running'], stopped: ['已停止','stopped'], creating: ['创建中','creating'], none: ['未创建','none'], failed: ['失败','failed'] };
		var m = map[s] || ['未知','none'];
		return '<span class="dk-tag dk-tag-'+ m[1] +'">'+ m[0] +'</span>';
	}

	function fmtBytes(n){
		n = parseInt(n, 10) || 0;
		if (n < 1024) return n + ' B';
		if (n < 1048576) return (n/1024).toFixed(1) + ' KB';
		if (n < 1073741824) return (n/1048576).toFixed(1) + ' MB';
		return (n/1073741824).toFixed(2) + ' GB';
	}

	function appIconChar(name){
		if (!name) return '◆';
		name = String(name).trim();
		if (/^[\u4e00-\u9fa5]/.test(name)) {
			return name.charAt(0);
		}
		return name.charAt(0).toUpperCase();
	}

	function renderContainer(c, me, node){
		// get_installed_apps 返回字段：service_name/appname/apptitle/status/port[]/host_ip/server_ip/container_id/appinfo[]
		var name = c.service_name || me.service_name || '';
		var appTitle = c.apptitle || c.appname || me.app_name || '';
		var appDesc = c.appdesc || '';
		var status = c.status || '';
		var portsArr = c.port || [];
		var home = c.home || '';
		var appinfo = c.appinfo || [];
		var cs = me.container_status || 'running';
		var nodeIp = (node && node.btip) ? node.btip : (c.server_ip || c.host_ip || '');
		var actions = '';
		if (cs === 'running') {
			actions = '<button class="dk-btn dk-btn-warning dk-btn-sm" onclick="dkContainerOp(\'container_stop\')">停止</button>' +
				'<button class="dk-btn dk-btn-ghost dk-btn-sm" onclick="dkContainerOp(\'container_restart\')">重启</button>';
		} else if (cs === 'stopped') {
			actions = '<button class="dk-btn dk-btn-success dk-btn-sm" onclick="dkContainerOp(\'container_start\')">启动</button>' +
				'<button class="dk-btn dk-btn-ghost dk-btn-sm" onclick="dkContainerOp(\'container_restart\')">重启</button>';
		}
		return '<div class="dk-card">' +
			'<div class="dk-card-head">' +
				'<div class="dk-app-title">' +
					'<div class="dk-app-icon">'+ esc(appIconChar(c.appname || me.app_name)) +'</div>' +
					'<div><h3>'+ esc(appTitle || '容器详情') +'</h3>'+ (appDesc ? '<div class="dk-muted" style="font-size:12.5px;margin-top:2px">'+ esc(appDesc) +'</div>' : '') +'</div>' +
				'</div>' +
				statusTag(cs) +
			'</div>' +
			'<div class="dk-card-body">' +
				'<div class="dk-metrics" style="margin-bottom:20px">' +
					'<div class="dk-metric"><div class="dk-m-label">服务名</div><div class="dk-m-value dk-mono" style="font-size:14px">'+ esc(name || '-') +'</div></div>' +
					'<div class="dk-metric"><div class="dk-m-label">节点 IP</div><div class="dk-m-value dk-mono" style="font-size:14px">'+ esc(nodeIp || '-') +'</div></div>' +
					'<div class="dk-metric"><div class="dk-m-label">配额</div><div class="dk-m-value dk-mono" style="font-size:14px">'+ esc(specCpu(me)) +'核 / '+ esc(specMem(me)) +'MB</div></div>' +
					'<div class="dk-metric"><div class="dk-m-label">版本</div><div class="dk-m-value dk-mono" style="font-size:14px">'+ esc(c.version || (c.m_version + (c.s_version ? '.'+c.s_version : '')) || '-') +'</div></div>' +
				'</div>' +
				'<div class="dk-card-body flush" style="border:1px solid var(--dk-border);border-radius:var(--dk-radius-sm)">' +
					'<table class="dk-table"><tbody>' +
						'<tr><th style="width:140px">状态</th><td>'+ esc(status || cs) +'</td></tr>' +
						'<tr><th>节点 IP</th><td class="dk-mono">'+ esc(nodeIp || '-') +'</td></tr>' +
						'<tr><th>端口映射</th><td>'+ renderPortsFromArray(portsArr, nodeIp, appinfo) +'</td></tr>' +
						(home ? '<tr><th>应用主页</th><td><a href="'+ esc(home.trim()) +'" target="_blank" rel="noopener" class="dk-port-link">'+ esc(home.trim()) +'</a></td></tr>' : '') +
						'<tr><th>服务名</th><td class="dk-mono">'+ esc(name || '-') +'</td></tr>' +
					'</tbody></table>' +
				'</div>' +
				'<div class="dk-row-actions" style="margin-top:20px">'+ actions +
					'<button class="dk-btn dk-btn-ghost dk-btn-sm" onclick="load()">刷新状态</button>' +
				'</div>' +
			'</div></div>';
	}

	// 渲染端口列表：80/443 自动用 http/https 协议，其他端口用 host:port 链接
	// 同时从 appinfo 中匹配端口的中文说明（如 frps_http_port → HTTP 监听端口）
	function renderPortsFromArray(ports, nodeIp, appinfo){
		if (!ports || !ports.length) return '<span class="dk-muted">无端口映射</span>';
		// 从 appinfo 构造 port→title 映射（key 含 _port 且 value 是数字）
		var portTitleMap = {};
		(appinfo || []).forEach(function(info){
			var key = info.fieldKey || '';
			var val = String(info.fieldValue || '');
			if (/_port$/i.test(key) && /^\d+$/.test(val)) {
				portTitleMap[val] = info.fieldTitle || key;
			}
		});
		var html = '';
		ports.forEach(function(p){
			var port = String(p);
			var label = (nodeIp ? esc(nodeIp) : '节点') +':'+ esc(port);
			var url = '';
			if (nodeIp) {
				if (port === '80') url = 'http://'+ nodeIp;
				else if (port === '443') url = 'https://'+ nodeIp;
				else url = 'http://'+ nodeIp +':'+ port;
			}
			var link = url ? '<a href="'+ esc(url) +'" target="_blank" rel="noopener" class="dk-port-link">'+ label +'</a>' : '<span class="dk-mono">'+ label +'</span>';
			var title = portTitleMap[port];
			var titleHtml = title ? ' <span class="dk-muted dk-port-meta">('+ esc(title) +')</span>' : '';
			html += '<div class="dk-port-item">'+ link + titleHtml +'</div>';
		});
		return html;
	}

	function renderCreating(me){
		var elapsed = me.container_spec ? '' : '';
		return '<div class="dk-card">' +
			'<div class="dk-card-head"><h3>容器创建中</h3>'+ statusTag('creating') +'</div>' +
			'<div class="dk-card-body">' +
				'<div class="dk-alert dk-alert-info">应用 <b>'+ esc(me.app_name || '') +'</b> 正在初始化，通常需要 1-5 分钟，请耐心等待，页面会自动刷新。</div>' +
				'<div class="dk-step-list">' +
					'<div class="dk-step" data-s="1"><span class="dk-step-ico">1</span><div class="dk-step-txt">提交创建请求</div></div>' +
					'<div class="dk-step" data-s="2"><span class="dk-step-ico">2</span><div class="dk-step-txt">拉取镜像 / 启动容器</div></div>' +
					'<div class="dk-step" data-s="3"><span class="dk-step-ico">3</span><div class="dk-step-txt">应用初始化完成</div></div>' +
				'</div>' +
				'<div class="dk-muted" style="margin-top:14px;font-size:13px">每 8 秒自动检查容器状态…</div>' +
			'</div></div>';
	}

	function esc(s){ return $('<div>').text(s == null ? '' : String(s)).html(); }
	function ltrim(s, ch){ return s.charAt(0) === ch ? s.slice(1) : s; }
	function specCpu(me){ try { return JSON.parse(me.container_spec||'{}').cpus || '-'; } catch(e){ return '-'; } }
	function specMem(me){ try { return JSON.parse(me.container_spec||'{}').memory_limit || '-'; } catch(e){ return '-'; } }

	window.dkContainerOp = function(gn){
		dkAjax(gn, {}).then(function(r){
			dkToast(r.msg || '操作已提交', 'success');
			setTimeout(load, 1500);
		});
	};

	function load(){
		return dkAjax('my_container', {}, {silent:true}).then(function(r){
			if (pollTimer) { clearInterval(pollTimer); pollTimer = null; }
			var me = r.me || {};
			var c = r.container;
			var node = r.node || {};
			var html;
			if (!me.service_name && !me.container_id) {
				html = renderEmpty();
			} else if (me.container_status === 'creating' || (!c && me.service_name)) {
				html = renderCreating(me);
				// creating 状态：每 8 秒自动刷新检查容器是否就绪
				pollTimer = setInterval(load, 8000);
			} else if (c) {
				html = renderContainer(c, me, node);
			} else {
				html = renderEmpty();
			}
			$('#dkConsoleView').html(html);
		});
	}

	load();
})();
</script>
<?php include __DIR__ . '/foot.php'; ?>
