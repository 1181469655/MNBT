<?php mnbt_admin_include('head'); ?>
<?php
// $mnbt_update / $mnbt_upcfg 由 admin/update.php 服务端检查后注入
$upd_ok = !empty($mnbt_update['ok']);
$upd_can = !empty($mnbt_update['can_update']);
$upd_log = trim((string)($mnbt_update['body'] ?? ''));
$upd_mirror_txt = implode("\n", $mnbt_upcfg['mirrors']);
$upd_token_set = !empty($mnbt_update['has_token']);
$upd_policy = isset($mnbt_upcfg['source_policy']) ? $mnbt_upcfg['source_policy'] : 'mirror_first';
$upd_policy_labels = [
	'mirror_first' => '镜像优先（推荐国内服务器）',
	'github_first' => 'GitHub 优先',
	'mirror_only'  => '仅用镜像',
	'github_only'  => '仅用 GitHub 直连',
];
$upd_policy_desc = isset($upd_policy_labels[$upd_policy]) ? $upd_policy_labels[$upd_policy] : $upd_policy;
?>
<style>
/* 系统更新页：直角蓝白，不用蓝底色块与装饰竖条 */
.mn-upd-page { max-width: 900px; margin: 0 auto; padding: 16px 14px 40px; }
.mn-upd-card { background: #fff; border: 1px solid #e8ecf1; border-radius: 0; margin-bottom: 14px; }
.mn-upd-hd { padding: 14px 18px; border-bottom: 1px solid #eef1f5; font-size: 15px; font-weight: 600; color: #0f172a; }
.mn-upd-hd .mdi { color: #2563eb; margin-right: 6px; }
.mn-upd-bd { padding: 18px; }
.mn-upd-ver { display: flex; flex-wrap: wrap; gap: 12px; margin-bottom: 16px; }
.mn-upd-ver-item { flex: 1; min-width: 180px; border: 1px solid #e8ecf1; border-radius: 0; padding: 14px 16px; }
.mn-upd-ver-num { font-size: 22px; font-weight: 700; color: #1e293b; line-height: 1.2; }
.mn-upd-ver-num.is-new { color: #2563eb; }
.mn-upd-ver-label { font-size: 12px; color: #94a3b8; margin-top: 4px; }
.mn-upd-tip { font-size: 13px; color: #475569; line-height: 1.8; margin-bottom: 14px; }
.mn-upd-tip.bad { color: #b42318; }
.mn-upd-kv { width: 100%; border-collapse: collapse; margin-bottom: 6px; }
.mn-upd-kv td { font-size: 13px; padding: 7px 0; border-bottom: 1px solid #f1f5f9; vertical-align: top; }
.mn-upd-kv td:first-child { color: #94a3b8; width: 110px; white-space: nowrap; }
.mn-upd-kv td:last-child { color: #1e293b; word-break: break-all; }
.mn-upd-log { background: #f8fafc; border: 1px solid #eef1f5; padding: 12px 14px; font-size: 12px; line-height: 1.8; color: #334155; white-space: pre-wrap; word-break: break-word; max-height: 300px; overflow-y: auto; margin: 0; border-radius: 0; }
.mn-upd-log-title { font-size: 13px; font-weight: 600; color: #334155; margin: 0 0 8px; }
.mn-upd-actions { margin-top: 16px; display: flex; gap: 10px; }
.mn-upd-actions .btn { border-radius: 0; font-weight: 500; padding: 9px 18px; }
.mn-upd-field { margin-bottom: 16px; }
.mn-upd-field > label { display: block; font-size: 13px; font-weight: 500; color: #334155; margin-bottom: 6px; }
.mn-upd-field .form-control { border-radius: 0; border-color: #e2e8f0; font-size: 13px; }
.mn-upd-field .form-control:focus { border-color: #93c5fd; box-shadow: 0 0 0 3px rgba(37, 99, 235, .1); }
.mn-upd-field small { display: block; margin-top: 6px; font-size: 12px; color: #94a3b8; line-height: 1.7; }
.mn-upd-row { display: flex; gap: 8px; margin-bottom: 8px; }
.mn-upd-row .form-control { flex: 1; }
.mn-upd-row .btn { border-radius: 0; flex-shrink: 0; }
.mn-upd-note { background: #f8fafc; border: 1px solid #eef1f5; padding: 12px 14px; font-size: 12px; color: #64748b; line-height: 1.8; }
.mn-upd-note code { background: #fff; padding: 1px 4px; border: 1px solid #e8ecf1; }
.mn-upd-policy { display: flex; flex-wrap: wrap; gap: 8px; }
.mn-upd-policy-item { display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; border: 1px solid #e2e8f0; background: #fff; font-size: 13px; color: #334155; font-weight: normal; cursor: pointer; margin: 0; }
.mn-upd-policy-item input { margin: 0; accent-color: #2563eb; }
.mn-upd-policy-item:has(input:checked) { border-color: #2563eb; background: #eff6ff; color: #1d4ed8; }
/* 更新进度面板：直角、细边框、蓝白，不加装饰竖条 */
.mn-upd-prog { margin-top: 16px; padding: 14px 16px; border: 1px solid #e8ecf1; background: #fff; }
.mn-upd-prog-hd { display: flex; align-items: baseline; justify-content: space-between; margin-bottom: 10px; }
.mn-upd-prog-title { font-size: 14px; font-weight: 600; color: #1e293b; }
.mn-upd-prog-elapsed { font-size: 12px; color: #94a3b8; font-variant-numeric: tabular-nums; }
.mn-upd-prog-bar { height: 3px; background: #f1f5f9; overflow: hidden; margin-bottom: 8px; }
.mn-upd-prog-bar > i { display: block; height: 100%; width: 0; background: #2563eb; transition: width .3s ease; }
.mn-upd-prog-bar.is-indet > i { width: 30% !important; animation: mn-upd-indet 1.4s linear infinite; }
@keyframes mn-upd-indet { 0% { transform: translateX(-100%); } 100% { transform: translateX(340%); } }
.mn-upd-prog-detail { font-size: 12px; color: #475569; min-height: 20px; line-height: 1.6; word-break: break-all; }
.mn-upd-prog-steps { list-style: none; padding: 0; margin: 12px 0 0; font-size: 13px; color: #475569; }
.mn-upd-prog-steps li { display: flex; align-items: flex-start; gap: 8px; padding: 4px 0; }
.mn-upd-prog-steps li .mn-upd-step-icon { width: 16px; flex-shrink: 0; text-align: center; color: #cbd5e1; font-size: 14px; line-height: 20px; }
.mn-upd-prog-steps li .mn-upd-step-label { flex: 1; }
.mn-upd-prog-steps li .mn-upd-step-note { font-size: 12px; color: #94a3b8; }
.mn-upd-prog-steps li.is-done .mn-upd-step-icon { color: #2ba471; }
.mn-upd-prog-steps li.is-done .mn-upd-step-label { color: #64748b; }
.mn-upd-prog-steps li.is-active { color: #0f172a; }
.mn-upd-prog-steps li.is-active .mn-upd-step-icon { color: #2563eb; }
.mn-upd-prog-steps li.is-active .mn-upd-step-label { font-weight: 600; }
.mn-upd-prog-steps li.is-failed .mn-upd-step-icon { color: #d54941; }
.mn-upd-prog-steps li.is-failed .mn-upd-step-label { color: #b42318; }
.mn-upd-prog-steps li .mn-upd-step-spin { display: inline-block; animation: mn-upd-spin 1s linear infinite; }
@keyframes mn-upd-spin { from { transform: rotate(0); } to { transform: rotate(360deg); } }
.mn-upd-prog-notice { margin-top: 10px; padding: 8px 10px; background: #fff7ed; border: 1px solid #fed7aa; color: #9a3412; font-size: 12px; line-height: 1.6; }
.mn-upd-prog-notice.is-ok { background: #ecfdf5; border-color: #a7f3d0; color: #065f46; }
</style>

<div class="mn-upd-page">

  <div class="mn-upd-card">
    <div class="mn-upd-hd"><i class="mdi mdi-cloud-download"></i>检查更新</div>
    <div class="mn-upd-bd">
      <div class="mn-upd-ver">
        <div class="mn-upd-ver-item">
          <div class="mn-upd-ver-num"><?=htmlspecialchars($mnbt_update['current'])?></div>
          <div class="mn-upd-ver-label">当前版本</div>
        </div>
        <div class="mn-upd-ver-item">
          <div class="mn-upd-ver-num <?=($upd_ok && $upd_can) ? 'is-new' : ''?>"><?=htmlspecialchars($upd_ok ? $mnbt_update['latest'] : '—')?></div>
          <div class="mn-upd-ver-label">最新可用版本<?=($upd_ok && !empty($mnbt_update['published_at'])) ? '（' . htmlspecialchars(substr((string)$mnbt_update['published_at'], 0, 10)) . '）' : ''?></div>
        </div>
      </div>

<?php if (!$upd_ok): ?>
      <div class="mn-upd-tip bad"><i class="mdi mdi-alert-circle-outline"></i> 检查更新失败：<?=htmlspecialchars($mnbt_update['error'])?>（仓库 <?=htmlspecialchars($mnbt_update['repo'])?>）</div>
<?php elseif ($upd_can): ?>
      <div class="mn-upd-tip"><i class="mdi mdi-arrow-up-bold-circle"></i> 发现新版本，可执行在线覆盖更新。</div>
<?php else: ?>
      <div class="mn-upd-tip"><i class="mdi mdi-bookmark-check"></i> 当前已是最新版本，无需更新。</div>
<?php endif; ?>

      <table class="mn-upd-kv">
        <tr><td>更新仓库</td><td><?=htmlspecialchars($mnbt_update['repo'])?></td></tr>
        <tr><td>包来源</td><td><?=htmlspecialchars($upd_ok ? $mnbt_update['source_label'] : '—')?><?=($upd_ok && !empty($mnbt_update['asset_size'])) ? '（' . number_format($mnbt_update['asset_size'] / 1048576, 1) . ' MB）' : ''?></td></tr>
        <tr><td>下载通道</td><td><?=htmlspecialchars($upd_policy_desc)?><?php
			if ($upd_policy !== 'github_only'):
				if (!empty($mnbt_update['mirrors'])) echo ' · 镜像：' . htmlspecialchars(implode('、', $mnbt_update['mirrors']));
			endif;
			if ($upd_policy === 'github_first' || $upd_policy === 'mirror_first') echo ' · 失败自动切换到另一种来源';
		?></td></tr>
<?php if ($upd_ok && !empty($mnbt_update['asset_url'])): ?>
        <tr><td>附件地址</td><td><?=htmlspecialchars($mnbt_update['asset_url'])?></td></tr>
<?php endif; ?>
<?php if (!empty($mnbt_update['fallback'])): ?>
        <tr><td>检查方式</td><td>latest 接口不可用，已改用发布列表取最高版本</td></tr>
<?php endif; ?>
      </table>

<?php if ($upd_ok): ?>
      <div class="mn-upd-actions">
<?php if ($upd_can): ?>
        <button class="btn btn-primary" type="button" id="upd_run_btn" onclick="up()"><i class="mdi mdi-update"></i> 立刻更新</button>
<?php endif; ?>
        <a class="btn btn-outline-secondary" href="update.php?recheck=1"><i class="mdi mdi-refresh"></i> 重新检查</a>
      </div>
<?php else: ?>
      <div class="mn-upd-actions">
        <a class="btn btn-outline-secondary" href="update.php?recheck=1"><i class="mdi mdi-refresh"></i> 重新检查</a>
      </div>
<?php endif; ?>

      <!-- 更新进度面板：默认隐藏，点击立刻更新后展开 -->
      <div id="upd_progress" class="mn-upd-prog" style="display:none;">
        <div class="mn-upd-prog-hd">
          <span id="upd_prog_title" class="mn-upd-prog-title">准备开始…</span>
          <span id="upd_prog_elapsed" class="mn-upd-prog-elapsed"></span>
        </div>
        <div class="mn-upd-prog-bar"><i id="upd_prog_fill"></i></div>
        <div id="upd_prog_detail" class="mn-upd-prog-detail"></div>
        <ol id="upd_prog_steps" class="mn-upd-prog-steps"></ol>
        <div id="upd_prog_notice" class="mn-upd-prog-notice" style="display:none;"></div>
      </div>
    </div>
  </div>

  <div class="mn-upd-card">
    <div class="mn-upd-hd"><i class="mdi mdi-text-box-outline"></i>更新日志</div>
    <div class="mn-upd-bd">
<?php if ($upd_log !== ''): ?>
      <pre class="mn-upd-log"><?=htmlspecialchars($upd_log)?></pre>
<?php else: ?>
      <p class="mn-upd-tip">该版本发布没有填写更新说明。</p>
<?php endif; ?>
    </div>
  </div>

  <div class="mn-upd-card">
    <div class="mn-upd-hd"><i class="mdi mdi-cog-outline"></i>更新设置</div>
    <div class="mn-upd-bd">
      <div class="mn-upd-field">
        <label for="upd_repo">GitHub 仓库</label>
        <input type="text" class="form-control" id="upd_repo" value="<?=htmlspecialchars($mnbt_upcfg['repo'])?>" placeholder="owner/repo" maxlength="140">
        <small>格式 <code>owner/repo</code>，更新包只能从这个仓库的 Release 里取。</small>
      </div>

      <div class="mn-upd-field">
        <label>下载镜像</label>
        <div id="upd_mirrors">
<?php foreach ($mnbt_upcfg['mirrors'] as $m): ?>
          <div class="mn-upd-row">
            <input type="text" class="form-control upd-mirror-input" value="<?=htmlspecialchars($m)?>" placeholder="https://gh-proxy.com/" maxlength="160">
            <button class="btn btn-outline-danger" type="button" onclick="updDelMirror(this)"><i class="mdi mdi-close"></i></button>
          </div>
<?php endforeach; ?>
        </div>
        <button class="btn btn-outline-secondary" type="button" onclick="updAddMirror()"><i class="mdi mdi-plus"></i> 添加镜像</button>
        <small>镜像地址填 <code>https://gh-proxy.com/</code> 这种带协议的形式，会在原地址前面加前缀。具体是否使用镜像、顺序如何，看下面"下载策略"。</small>
      </div>

      <div class="mn-upd-field">
        <label>下载策略</label>
        <div class="mn-upd-policy">
          <?php $pol_map = ['mirror_first'=>'镜像优先（推荐国内服务器）','github_first'=>'GitHub 优先','mirror_only'=>'仅用镜像','github_only'=>'仅用 GitHub 直连']; ?>
          <?php foreach ($pol_map as $pk => $pv): ?>
          <label class="mn-upd-policy-item">
            <input type="radio" name="upd_source_policy" value="<?=htmlspecialchars($pk)?>" <?=($upd_policy === $pk ? 'checked' : '')?>>
            <span><?=htmlspecialchars($pv)?></span>
          </label>
          <?php endforeach; ?>
        </div>
        <small>国内服务器直连 GitHub 大概率超时，建议保持"镜像优先"；"仅用镜像"能彻底避免 GitHub 超时拖时间；"仅用 GitHub"适合海外或有代理的服务器。</small>
      </div>

      <div class="mn-upd-field">
        <label for="upd_token">GitHub Token（可选）</label>
        <input type="password" class="form-control" id="upd_token" placeholder="<?=($upd_token_set ? '已设置，留空表示不修改' : '仅用于提高 API 速率限制，可留空')?>" autocomplete="new-password" maxlength="128">
<?php if ($upd_token_set): ?>
        <div class="mn-upd-row" style="margin-top:8px">
          <label style="font-size:12px;color:#64748b;font-weight:normal;margin:0;padding-top:8px;">
            <input type="checkbox" id="upd_clear_token"> 清除已保存的 Token
          </label>
        </div>
<?php endif; ?>
        <small>Token 只用于调用 GitHub API，不会跟着下载请求发给镜像；页面不会回显明文。</small>
      </div>

      <div class="mn-upd-actions">
        <button class="btn btn-primary" type="button" onclick="saveUpdSetting()"><i class="mdi mdi-content-save-outline"></i> 保存更新设置</button>
      </div>

      <div class="mn-upd-note" style="margin-top:16px;">
        <b>更新说明：</b>更新会直接用 GitHub Release 的包覆盖站点文件，覆盖前会备份并还原
        <code>config.php</code>、<code>cf_up.php</code>、<code>install/install.lock</code>、<code>runtime/bt_cookie/</code>；
        包里 <code>update/update_v*_*.sql</code> 的版本化迁移会按版本号依次执行（仅跑游标 <code>MN_dbver</code> 之后的增量），旧式单文件 <code>update/update.sql</code> 仍兼容。请在维护时段操作，更新过程中不要关闭页面。
      </div>
    </div>
  </div>

</div>
<script type="text/javascript">
function updAddMirror() {
  var box = document.getElementById('upd_mirrors');
  var div = document.createElement('div');
  div.className = 'mn-upd-row';
  div.innerHTML = '<input type="text" class="form-control upd-mirror-input" placeholder="https://gh-proxy.com/" maxlength="160">'
                + '<button class="btn btn-outline-danger" type="button" onclick="updDelMirror(this)"><i class="mdi mdi-close"></i></button>';
  box.appendChild(div);
  div.querySelector('input').focus();
}
function updDelMirror(btn) {
  var row = btn.parentNode;
  var box = document.getElementById('upd_mirrors');
  if (box.children.length <= 1) {
    row.querySelector('input').value = '';
    return;
  }
  box.removeChild(row);
}
function saveUpdSetting() {
  var mirrors = [];
  var list = document.querySelectorAll('#upd_mirrors .upd-mirror-input');
  for (var i = 0; i < list.length; i++) {
    var v = $.trim(list[i].value);
    if (v !== '' && mirrors.indexOf(v) === -1) mirrors.push(v);
  }
  var data = {};
  data['gn'] = 'upset';
  data['repo'] = $.trim(document.getElementById('upd_repo').value);
  data['mirrors'] = mirrors.join('\n');
  var tk = document.getElementById('upd_token');
  if (tk && tk.value !== '') data['github_token'] = $.trim(tk.value);
  var cl = document.getElementById('upd_clear_token');
  if (cl && cl.checked) data['clear_token'] = '1';
  var polEl = document.querySelector('input[name="upd_source_policy"]:checked');
  if (polEl) data['source_policy'] = polEl.value;
  msloading();
  $.post('./ajax.php', data, function (date) {
    msloadingde();
    var jsoe = typeof date === 'string' ? JSON.parse(date) : date;
    if (jsoe.qk == 1 || jsoe.code == '保存成功') {
      msalert(1, '保存成功', 2500);
      setTimeout(function () { window.location.href = 'update.php'; }, 1200);
    } else {
      msalert(4, jsoe.code || '保存失败', 4000);
    }
  }).fail(function () {
    msloadingde();
    msalert(4, '网络错误，保存失败', 3000);
  });
}
function up() {
  if (!window.confirm('将用 GitHub Release 的包覆盖当前站点文件，过程可能需要几分钟，期间请勿关闭页面。确认开始更新？')) return;
  var btn = document.getElementById('upd_run_btn');
  if (btn) { btn.disabled = true; btn.style.opacity = .55; }
  var box = document.getElementById('upd_progress');
  if (box) box.style.display = 'block';
  updProg.state = {
    startedAt: Math.floor(Date.now() / 1000), steps: [], stepIndex: -1, stepLabel: '准备',
    detail: '发起更新请求…', pct: null, running: true, ok: null, message: '',
    lastUpdatedAt: Math.floor(Date.now() / 1000), terminal: false, httpNote: ''
  };
  updProg.render();
  updProg.elapsedTimer = setInterval(function () { updProg.renderElapsed(); }, 1000);
  updProg.pollTimer = setInterval(function () { updProg.poll(); }, 1500);
  setTimeout(function () { updProg.poll(); }, 1000);
  updProg.stopAll = function () {
    if (updProg.pollTimer) { clearInterval(updProg.pollTimer); updProg.pollTimer = null; }
    if (updProg.elapsedTimer) { clearInterval(updProg.elapsedTimer); updProg.elapsedTimer = null; }
  };
  updProg.finalize = function (ok, message) {
    if (updProg.state.terminal) return;
    updProg.state.terminal = true;
    updProg.state.running = false;
    updProg.state.ok = ok ? 1 : 0;
    updProg.state.message = message;
    updProg.stopAll();
    updProg.render();
    if (btn) { btn.disabled = false; btn.style.opacity = ''; }
    if (ok) msalert(1, message, 6000); else msalert(4, message, 8000);
  };
  let data = {};
  data["gn"] = "update";
  $.post('./ajax.php', data, function (date) {
    var jsoe;
    try { jsoe = typeof date === 'string' ? JSON.parse(date) : date; } catch (e) { jsoe = { _parse_fail: 1, code: '返回内容解析失败：' + String(date).slice(0, 160) }; }
    // 异步模式：HTTP 只回 202/started，终态完全靠 progress.json
    if (jsoe && Number(jsoe.async) === 1) {
      updProg.state.detail = '后端已启动，等待进度反馈…';
      updProg.render();
      return;
    }
    // 同步模式（没有 fastcgi_finish_request）：HTTP 响应即终态
    var okk = (jsoe.qk == 1 || jsoe.code === '更新成功～请手动刷新页面');
    var msg = okk ? '更新成功～请手动刷新页面' : (jsoe.code || '更新失败');
    if (okk && jsoe.source) msg += '｜' + jsoe.source + (jsoe.via ? '（' + jsoe.via + '）' : '');
    updProg.finalize(okk, msg);
  }).fail(function (xhr) {
    // HTTP 中断不代表后端死了：FPM 环境下后端仍在跑，非 FPM 环境靠 ignore_user_abort 也可能跑完；
    // 前端继续按进度轮询，最多再等 8 分钟或直到 stall 判定，才归为失败
    updProg.state.httpNote = 'HTTP ' + ((xhr && xhr.status) || '?') + '（连接已断，若后端仍在跑会自动继续，进度以页面为准）';
    updProg.render();
  });
}

// 更新进度面板的状态与渲染：从 egn=upprogress 拉后端写好的 progress.json 再画步骤条
var updProg = {
  state: null, pollTimer: null, elapsedTimer: null, stopAll: null, finalize: null, pollBusy: false,
  // 卡死判定：progress.json 里 updated_at 5 分钟不动 = 后端死了
  // 总时长兜底：30 分钟仍未终态 = 前端不再等，但只提示不判失败（后端可能仍在跑）
  STALL_MS: 300 * 1000, MAX_WAIT_MS: 30 * 60 * 1000,
  poll: function () {
    if (updProg.pollBusy || !updProg.state || updProg.state.terminal) return;
    updProg.pollBusy = true;
    $.post('./ajax.php', { gn: 'upprogress' }, function (raw) {
      var p;
      try { p = typeof raw === 'string' ? JSON.parse(raw) : raw; } catch (e) { return; }
      if (!p || Number(p.has) !== 1) return;
      // 后端 progress.json 是权威状态：running=0 才算终态
      updProg.state.steps = p.steps || updProg.state.steps;
      updProg.state.stepIndex = typeof p.step_index === 'number' ? p.step_index : updProg.state.stepIndex;
      updProg.state.stepLabel = p.step_label || updProg.state.stepLabel;
      updProg.state.detail = p.detail || '';
      updProg.state.pct = (typeof p.pct === 'number' || p.pct === null) ? p.pct : updProg.state.pct;
      if (p.started_at) updProg.state.startedAt = Number(p.started_at);
      var beUpdated = Number(p.updated_at) || 0;
      if (beUpdated > (updProg.state.lastUpdatedAt || 0)) updProg.state.lastUpdatedAt = beUpdated;
      if (Number(p.running) === 0) {
        var ok = Number(p.ok) === 1;
        updProg.finalize(ok, p.message || (ok ? '更新完成' : '更新失败'));
        return;
      }
      // 卡死判定：后端 progress.json 里 updated_at 5 分钟没动
      var nowSec = Math.floor(Date.now() / 1000);
      if (updProg.state.lastUpdatedAt && (nowSec - updProg.state.lastUpdatedAt) * 1000 > updProg.STALL_MS) {
        updProg.finalize(false, '进度已 5 分钟未更新，后端可能崩溃或超时；本地配置与后台目录在关闭时会自动还原，请刷新后台确认站点状态。');
        return;
      }
      // 总等待兜底：30 分钟仍未终态，前端放弃等待；后端可能仍在跑，靠 progress.json 自锁防并发
      if (updProg.state.startedAt && (nowSec - updProg.state.startedAt) * 1000 > updProg.MAX_WAIT_MS) {
        updProg.finalize(false, '已等满 30 分钟仍无终态，前端停止轮询。请稍后刷新后台查看版本号是否已升；如仍未升级，可去服务器看 runtime/temp/update_tmp/progress.json 确认后端状态。');
        return;
      }
      updProg.render();
    }).always(function () { updProg.pollBusy = false; });
  },
  render: function () {
    var s = updProg.state;
    if (!s) return;
    var title = document.getElementById('upd_prog_title');
    var fill = document.getElementById('upd_prog_fill');
    var bar = fill ? fill.parentNode : null;
    var detail = document.getElementById('upd_prog_detail');
    var list = document.getElementById('upd_prog_steps');
    var notice = document.getElementById('upd_prog_notice');
    if (title) {
      if (!s.running) {
        title.textContent = s.ok ? '更新完成' : '更新失败';
      } else {
        title.textContent = (s.stepLabel || '进行中') + (s.stepIndex >= 0 && s.steps && s.steps.length ? '（' + (s.stepIndex + 1) + '/' + s.steps.length + '）' : '');
      }
    }
    if (bar) {
      if (s.pct === null || typeof s.pct === 'undefined') bar.classList.add('is-indet');
      else bar.classList.remove('is-indet');
    }
    if (fill && typeof s.pct === 'number') {
      var pct = Math.max(0, Math.min(100, s.pct));
      if (!s.running) pct = s.ok ? 100 : pct;
      fill.style.width = pct + '%';
    }
    if (detail) {
      var txt = s.detail || '';
      if (typeof s.pct === 'number' && s.running) txt += '　' + Math.max(0, Math.min(100, s.pct)) + '%';
      if (s.httpNote && s.running) txt += (txt ? '　·　' : '') + s.httpNote;
      detail.textContent = txt;
    }
    if (list && s.steps && s.steps.length) {
      var html = '';
      for (var i = 0; i < s.steps.length; i++) {
        var st = s.steps[i];
        var cls = '';
        var icon = '<i class="mdi mdi-circle-outline"></i>';
        if (i < s.stepIndex) { cls = 'is-done'; icon = '<i class="mdi mdi-check-circle"></i>'; }
        else if (i === s.stepIndex) {
          if (!s.running && s.ok) { cls = 'is-done'; icon = '<i class="mdi mdi-check-circle"></i>'; }
          else if (!s.running) { cls = 'is-failed'; icon = '<i class="mdi mdi-close-circle"></i>'; }
          else { cls = 'is-active'; icon = '<i class="mdi mdi-loading mn-upd-step-spin"></i>'; }
        }
        var note = '';
        if (i === s.stepIndex && s.running) note = s.detail ? ' <span class="mn-upd-step-note">· ' + updProg.esc(s.detail) + '</span>' : '';
        html += '<li class="' + cls + '"><span class="mn-upd-step-icon">' + icon + '</span><span class="mn-upd-step-label">' + updProg.esc(st.label) + note + '</span></li>';
      }
      list.innerHTML = html;
    }
    if (notice) {
      if (!s.running && s.message) {
        notice.style.display = 'block';
        notice.textContent = s.message + (s.ok ? '（页面已刷新过请忽略，否则请手动刷新后台）' : '');
        if (s.ok) notice.classList.add('is-ok'); else notice.classList.remove('is-ok');
      } else {
        notice.style.display = 'none';
      }
    }
    updProg.renderElapsed();
  },
  renderElapsed: function () {
    var s = updProg.state;
    var el = document.getElementById('upd_prog_elapsed');
    if (!s || !el) return;
    var secs = Math.max(0, Math.floor(Date.now() / 1000) - (s.startedAt || Math.floor(Date.now() / 1000)));
    var m = Math.floor(secs / 60), r = secs % 60;
    el.textContent = '已用 ' + (m > 0 ? (m + '分') : '') + r + '秒';
  },
  esc: function (v) {
    return String(v == null ? '' : v).replace(/[&<>"']/g, function (c) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
    });
  }
};
</script>
</body>
</html>
