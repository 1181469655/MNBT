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

	function renderContainer(c, me){
		var name = ltrim((c.name || (c.Names && c.Names[0]) || ''), '/');
		var image = c.image || (c.Image || '') || '';
		var status = c.status || (c.State || '');
		var ports = c.ports || (c.Ports || '');
		var created = c.time || (c.Created || '') || '';
		var cs = me.container_status || 'running';
		var actions = '';
		if (cs === 'running') {
			actions = '<button class="dk-btn dk-btn-warning dk-btn-sm" onclick="dkContainerOp(\'container_stop\')">停止</button>' +
				'<button class="dk-btn dk-btn-ghost dk-btn-sm" onclick="dkContainerOp(\'container_restart\')">重启</button>';
		} else if (cs === 'stopped') {
			actions = '<button class="dk-btn dk-btn-success dk-btn-sm" onclick="dkContainerOp(\'container_start\')">启动</button>' +
				'<button class="dk-btn dk-btn-ghost dk-btn-sm" onclick="dkContainerOp(\'container_restart\')">重启</button>';
		}
		return '<div class="dk-card">' +
			'<div class="dk-card-head"><h3>容器详情</h3>'+ statusTag(cs) +'</div>' +
			'<div class="dk-card-body">' +
				'<div class="dk-metrics" style="margin-bottom:18px">' +
					'<div class="dk-metric"><div class="dk-m-label">容器名</div><div class="dk-m-value dk-mono" style="font-size:14px">'+ esc(name) +'</div></div>' +
					'<div class="dk-metric"><div class="dk-m-label">应用</div><div class="dk-m-value dk-mono" style="font-size:14px">'+ esc(me.app_name || '-') +'</div></div>' +
					'<div class="dk-metric"><div class="dk-m-label">镜像</div><div class="dk-m-value dk-mono" style="font-size:13px">'+ esc(image) +'</div></div>' +
					'<div class="dk-metric"><div class="dk-m-label">配额</div><div class="dk-m-value dk-mono" style="font-size:14px">'+ esc(specCpu(me)) +'核 / '+ esc(specMem(me)) +'MB</div></div>' +
				'</div>' +
				'<div class="dk-card-body flush" style="border:1px solid var(--dk-border);border-radius:8px">' +
					'<table class="dk-table"><tbody>' +
						'<tr><th style="width:140px">状态</th><td>'+ esc(status) +'</td></tr>' +
						'<tr><th>端口映射</th><td class="dk-mono">'+ esc(ports || '无') +'</td></tr>' +
						'<tr><th>创建时间</th><td class="dk-mono">'+ esc(created || '-') +'</td></tr>' +
						'<tr><th>服务名</th><td class="dk-mono">'+ esc(me.service_name || '-') +'</td></tr>' +
					'</tbody></table>' +
				'</div>' +
				'<div class="dk-row-actions" style="margin-top:18px">'+ actions +
					'<button class="dk-btn dk-btn-ghost dk-btn-sm" onclick="dkShowLog()">查看安装日志</button>' +
				'</div>' +
			'</div></div>';
	}

	function renderCreating(me){
		return '<div class="dk-card">' +
			'<div class="dk-card-head"><h3>容器创建中</h3>'+ statusTag('creating') +'</div>' +
			'<div class="dk-card-body">' +
				'<div class="dk-alert dk-alert-info">应用 <b>'+ esc(me.app_name || '') +'</b> 正在初始化，通常需要 1-5 分钟，请勿关闭页面。</div>' +
				'<div class="dk-log" id="dkInstallLog">正在拉取安装日志…</div>' +
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
	window.dkShowLog = function(){
		dkModal('<div class="dk-log" id="dkModalLog">加载中…</div>', '容器执行日志');
		dkAjax('install_log', {}, {silent:true}).then(function(r){
			var log = r.log;
			$('#dkModalLog').text(typeof log === 'string' ? log : JSON.stringify(log, null, 2));
		});
	};

	function load(){
		return dkAjax('my_container', {}, {silent:true}).then(function(r){
			if (pollTimer) { clearInterval(pollTimer); pollTimer = null; }
			var me = r.me || {};
			var c = r.container;
			var html;
			if (!me.service_name && !me.container_id) {
				html = renderEmpty();
			} else if (me.container_status === 'creating' || (!c && me.service_name)) {
				html = renderCreating(me);
				// 轮询安装日志 + 自动刷新状态
				pollLog();
				pollTimer = setInterval(load, 8000);
			} else if (c) {
				html = renderContainer(c, me);
			} else {
				html = renderEmpty();
			}
			$('#dkConsoleView').html(html);
		});
	}

	function pollLog(){
		dkAjax('install_log', {}, {silent:true}).then(function(r){
			var log = r.log;
			var txt = typeof log === 'string' ? log : (log && log.msg ? log.msg : JSON.stringify(log));
			var box = $('#dkInstallLog');
			if (box.length) {
				box.html(esc(txt).replace(/\n/g, '<br>'));
				box.scrollTop(box[0].scrollHeight);
			}
		});
	}

	load();
})();
</script>
<?php include __DIR__ . '/foot.php'; ?>
