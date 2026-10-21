<?php
/**
 * mobile_bridge - 用户端「APP 配对」页（自包含页面，不依赖主题外壳）
 *
 * 功能：
 *   - 生成一次性配对码（8 位 / 120s / 单次），本地渲染二维码（TDesign QRCode，仓库内置资源，零站外请求）
 *   - 已绑定设备列表与吊销
 *   - 手动配对降级：无摄像头时直接在 APP 输入配对码
 *
 * AJAX 端点（bootstrap.php 注册，POST user/ajax.php，走 cookie 会话 + CSRF）：
 *   p_mobile_bridge_gen_code / p_mobile_bridge_device_list / p_mobile_bridge_device_revoke
 */
if (!defined('IN_CRONLITE')) exit;

global $yhc;
// 页面从 /user/plugin.php 输出，相对路径需回退两级取内置资源
$vendor = '../../imsetes/vendor';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>APP 配对</title>
<link rel="stylesheet" href="../../imsetes/css/materialdesignicons.min.css">
<link rel="stylesheet" href="<?= $vendor ?>/tdesign/tdesign.min.css">
<style>
:root { --brand:#0052d9; --text:#1a2e28; --text-2:#5a6672; --bg:#f2f3f5; --card:#fff; --border:#e4e7ed; --danger:#d54941; }
* { box-sizing:border-box; }
body { margin:0; background:var(--bg); color:var(--text); font-family:system-ui,-apple-system,"PingFang SC","Microsoft YaHei",sans-serif; font-size:14px; }
.wrap { max-width:860px; margin:0 auto; padding:24px 16px 48px; }
.card { background:var(--card); border:1px solid var(--border); border-radius:6px; padding:20px 24px; margin-bottom:16px; }
.card h2 { font-size:16px; margin:0 0 6px; }
.card .sub { color:var(--text-2); font-size:13px; line-height:1.7; margin:0 0 16px; }
.btn { display:inline-flex; align-items:center; gap:6px; border:1px solid var(--brand); background:var(--brand); color:#fff; border-radius:6px; padding:8px 18px; font-size:14px; cursor:pointer; }
.btn:hover { opacity:.9; }
.btn[disabled] { opacity:.5; cursor:not-allowed; }
.btn-plain { background:#fff; color:var(--brand); }
.pair-area { display:flex; gap:28px; align-items:flex-start; flex-wrap:wrap; }
.qr-box { min-width:236px; min-height:236px; display:grid; place-items:center; border:1px dashed var(--border); border-radius:6px; padding:8px; }
.code-info { flex:1; min-width:240px; }
.code-str { font-family:ui-monospace,Consolas,monospace; font-size:28px; letter-spacing:6px; color:var(--brand); margin:4px 0 10px; }
.count { color:var(--text-2); font-size:13px; margin-bottom:14px; }
.base { color:var(--text-2); font-size:12px; line-height:1.6; margin:0 0 12px; word-break:break-all; }
.hint { color:var(--text-2); font-size:13px; line-height:1.8; background:#f7f9fc; border:1px solid var(--border); border-radius:6px; padding:12px 14px; margin-top:16px; }
.hint b { color:var(--text); }
table { width:100%; border-collapse:collapse; }
th, td { text-align:left; padding:10px 12px; border-bottom:1px solid var(--border); font-size:13px; }
th { color:var(--text-2); font-weight:500; background:#fafbfc; }
td .act { color:var(--danger); cursor:pointer; background:none; border:none; font-size:13px; padding:0; }
td .act:hover { text-decoration:underline; }
.tag { display:inline-block; padding:2px 8px; border-radius:6px; font-size:12px; }
.tag-on { background:#e8f2ff; color:var(--brand); }
.tag-off { background:#f2f3f5; color:var(--text-2); }
.empty { color:var(--text-2); text-align:center; padding:28px 0; font-size:13px; }
.toast { position:fixed; top:18px; left:50%; transform:translateX(-50%); background:#28343f; color:#fff; padding:9px 18px; border-radius:6px; font-size:13px; opacity:0; pointer-events:none; transition:opacity .2s; z-index:9; }
.toast.show { opacity:1; }
.toast.err { background:var(--danger); }
@media (max-width:640px){ .pair-area{flex-direction:column;align-items:center;} .code-str{text-align:center;} }
</style>
</head>
<body>
<div class="wrap">
	<div class="card">
		<h2>扫码绑定主机</h2>
		<p class="sub">在 MNBT 主机管理 APP 中使用「扫码配对」扫描下方二维码。配对码 2 分钟内有效、仅可使用一次；换绑同名将自动顶替旧设备。</p>
		<div id="qr-root">
			<button class="btn" id="btn-gen" onclick="genCode()">
				<i class="mdi mdi-qrcode-scan"></i>生成配对码
			</button>
			<div class="pair-area" id="pair-area" style="display:none;margin-top:18px">
				<div class="qr-box"><div id="qr-mount"></div></div>
				<div class="code-info">
					<div style="color:var(--text-2);font-size:13px">配对码</div>
					<div class="code-str" id="code-str">--------</div>
					<div class="count" id="count-line">有效期 120 秒</div>
					<div class="base" id="base-line"></div>
					<button class="btn btn-plain" onclick="genCode()">
						<i class="mdi mdi-refresh"></i>重新生成
					</button>
				</div>
			</div>
		</div>
		<div class="hint">
			<b>手动配对：</b>手机无法扫码时，在 APP 的「手动输入」中直接键入上方 8 位配对码即可。<br>
			<b>安全说明：</b>二维码与配对码仅在本页面有效，服务器只保存令牌摘要；随时可在下方吊销设备，吊销立即生效。
		</div>
	</div>

	<div class="card">
		<h2>已绑定设备</h2>
		<p class="sub">同一主机可绑定多台设备；如怀疑设备丢失，请及时吊销。</p>
		<div style="overflow:auto;max-height:420px">
			<table>
				<thead><tr><th>设备名称</th><th>型号</th><th>系统</th><th>最近活跃</th><th>最近 IP</th><th>状态</th><th>操作</th></tr></thead>
				<tbody id="dev-body"><tr><td colspan="7" class="empty">加载中…</td></tr></tbody>
			</table>
		</div>
	</div>
</div>
<div class="toast" id="toast"></div>

<script src="<?= $vendor ?>/vue/vue.global.prod.js"></script>
<script src="<?= $vendor ?>/tdesign/tdesign.min.js"></script>
<script>
(function () {
	var CSRF = (document.cookie.match(/(?:^|;\s*)MNBT_CSRF_TOKEN=([^;]+)/) || [])[1] || '';

	function post(data, cb) {
		var body = new URLSearchParams();
		Object.keys(data).forEach(function (k) { body.append(k, data[k]); });
		fetch('ajax.php', {
			method: 'POST',
			headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-CSRF-TOKEN': CSRF },
			body: body.toString()
		}).then(function (r) { return r.json().catch(function () { return { qk: 4, code: '响应异常' }; }); }).then(cb);
	}

	var toastTimer = null;
	function toast(msg, err) {
		var el = document.getElementById('toast');
		el.textContent = msg;
		el.className = 'toast show' + (err ? ' err' : '');
		clearTimeout(toastTimer);
		toastTimer = setTimeout(function () { el.className = 'toast'; }, 2600);
	}

	function esc(s) {
		return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
			return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
		});
	}

	/* ---------- 配对码 + 二维码 ---------- */
	var qrState = null;
	function renderQR(url) {
		var mount = document.getElementById('qr-mount');
		if (!window.Vue || !window.TDesign) {
			mount.innerHTML = '';
			mount.textContent = '二维码组件不可用，请使用手动配对';
			return;
		}
		if (!qrState) {
			// 用外部 reactive 驱动重绘，避免「重新生成」时改不动已挂载组件
			qrState = Vue.reactive({ url: '' });
			var app = Vue.createApp({
				setup: function () { return { qrState: qrState }; },
				template: '<t-qrcode :value="qrState.url" :size="220" level="M"></t-qrcode>'
			});
			app.use(TDesign);
			app.mount(mount);
		}
		qrState.url = url;
	}

	var timer = null;
	window.genCode = function () {
		var btn = document.getElementById('btn-gen');
		btn.disabled = true;
		post({ gn: 'p_mobile_bridge_gen_code' }, function (res) {
			btn.disabled = false;
			if (!res || res.qk !== 1 || !res.code) { toast((res && res.code) || '生成失败', true); return; }
			document.getElementById('pair-area').style.display = 'flex';
			document.getElementById('code-str').textContent = res.code;
			var qr = String(res.qr_url || '');
			document.getElementById('base-line').textContent = qr ? ('APP 将连接的地址：' + qr.split('/index.php')[0]) : '';
			renderQR(qr);
			var left = res.expires_in || 120;
			var line = document.getElementById('count-line');
			clearInterval(timer);
			line.textContent = '有效期 ' + left + ' 秒';
			timer = setInterval(function () {
				left--;
				line.textContent = left > 0 ? ('有效期 ' + left + ' 秒') : '已过期，请点击重新生成';
				if (left <= 0) clearInterval(timer);
			}, 1000);
		});
	};

	/* ---------- 设备列表 ---------- */
	function loadDevices() {
		post({ gn: 'p_mobile_bridge_device_list' }, function (res) {
			var tb = document.getElementById('dev-body');
			if (!res || res.qk !== 1 || !Array.isArray(res.list) || res.list.length === 0) {
				tb.innerHTML = '<tr><td colspan="7" class="empty">暂无绑定设备</td></tr>';
				return;
			}
			tb.innerHTML = res.list.map(function (d) {
				return '<tr>'
					+ '<td>' + esc(d.device_name || d.device_id) + '</td>'
					+ '<td>' + esc(d.device_model || '-') + '</td>'
					+ '<td>' + esc(d.android_version || '-') + '</td>'
					+ '<td>' + esc(d.last_seen_at || '-') + '</td>'
					+ '<td>' + esc(d.last_ip || '-') + '</td>'
					+ '<td>' + (d.status === 1 ? '<span class="tag tag-on">活跃</span>' : '<span class="tag tag-off">已吊销</span>') + '</td>'
					+ '<td>' + (d.status === 1 ? '<button class="act" onclick="revokeDevice(' + d.id + ')">吊销</button>' : '-') + '</td>'
					+ '</tr>';
			}).join('');
		});
	}

	window.revokeDevice = function (id) {
		if (!confirm('确定吊销该设备？APP 端将立即失效。')) return;
		post({ gn: 'p_mobile_bridge_device_revoke', id: id }, function (res) {
			toast(res && res.qk === 1 ? '已吊销' : ((res && res.code) || '操作失败'), res && res.qk !== 1);
			loadDevices();
		});
	};

	loadDevices();
})();
</script>
</body>
</html>
