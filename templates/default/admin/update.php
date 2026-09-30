<?php mnbt_admin_include('head'); ?>
<?php
// $mnbt_update / $mnbt_upcfg 由 admin/update.php 服务端检查后注入
$upd_ok = !empty($mnbt_update['ok']);
$upd_can = !empty($mnbt_update['can_update']);
$upd_log = trim((string)($mnbt_update['body'] ?? ''));
$upd_mirror_txt = implode("\n", $mnbt_upcfg['mirrors']);
$upd_token_set = !empty($mnbt_update['has_token']);
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
        <tr><td>下载通道</td><td>github.com 直连<?=!empty($mnbt_update['mirrors']) ? '，失败后依次尝试：' . htmlspecialchars(implode('、', $mnbt_update['mirrors'])) : ''?></td></tr>
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
        <button class="btn btn-primary" type="button" onclick="up()"><i class="mdi mdi-update"></i> 立刻更新</button>
<?php endif; ?>
        <a class="btn btn-outline-secondary" href="update.php?recheck=1"><i class="mdi mdi-refresh"></i> 重新检查</a>
      </div>
<?php else: ?>
      <div class="mn-upd-actions">
        <a class="btn btn-outline-secondary" href="update.php?recheck=1"><i class="mdi mdi-refresh"></i> 重新检查</a>
      </div>
<?php endif; ?>
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
        <small>按顺序依次尝试：先 github.com 直连，再按上面的顺序走镜像。镜像地址填 <code>https://gh-proxy.com/</code> 这种带协议的形式，会在原地址前面加前缀。</small>
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
        <code>config.php</code>、<code>cf_up.php</code>、<code>MPHX/SQ.php</code>、<code>install/install.lock</code>、<code>api/cookie/</code>；
        包里若带 <code>update/update.sql</code> 会自动执行。请在维护时段操作，更新过程中不要关闭页面。
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
  msloading();
  $.post('./ajax.php', data, function (date) {
    msloadingde();
    var jsoe = JSON.parse(date);
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
  msloading();
  let data = {};
  data["gn"] = "update";
  $.post('./ajax.php', data, function (date) {
    msloadingde();
    var jsoe = JSON.parse(date);
    var qk = jsoe.code;
    if (jsoe.qk == 1 || qk == '更新成功～请手动刷新页面') {
      var txt = '更新成功～请手动刷新页面';
      if (jsoe.source) txt += '｜' + jsoe.source + (jsoe.via ? '（' + jsoe.via + '）' : '');
      msalert(1, txt, 6000);
    } else {
      msalert(4, qk || '更新失败', 8000);
    }
  }).fail(function () {
    msloadingde();
    msalert(4, '更新请求中断，请刷新后台确认站点是否正常；本地配置与后台目录在中断时会自动还原', 8000);
  });
}
</script>
</body>
</html>
