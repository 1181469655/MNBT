<?php
/**
 * 用户端 - 主机详情（实时状态 / 流量 / 操作）
 */
if (!defined('IN_CRONLITE')) {
	exit;
}
$page_title = $page_title ?? '主机详情';
$host = $host ?? null;
$info = $info ?? ['ok' => false, 'msg' => ''];
$traffic = $traffic ?? ['ok' => false, 'data' => []];

$password = zjmf_decrypt((string)$host['password']);
$hasUpId = (int)$host['up_host_id'] > 0;

function zjmf_view_show_traffic($data)
{
	if (!is_array($data) || $data === []) {
		return '暂无流量数据';
	}
	// 常见标量字段直接展示
	$rows = [];
	foreach (['total', 'used', 'free', 'percent', 'current', 'monthly_used'] as $k) {
		if (isset($data[$k]) && $data[$k] !== '' && $data[$k] !== null) {
			$rows[] = $k . '：' . htmlspecialchars((string)$data[$k]);
		}
	}
	if ($rows) {
		return implode('<br>', $rows);
	}
	return htmlspecialchars(json_encode($data, JSON_UNESCAPED_UNICODE));
}
ob_start();
?>
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;">
  <h1 style="font-size:20px;color:#222;margin:0;"><?= htmlspecialchars($host['name']) ?></h1>
  <a class="layui-btn layui-btn-xs layui-btn-primary" href="<?= zjmf_url('reserve/hosts') ?>">返回主机列表</a>
</div>

<div class="zj-msg" id="zjf-msg"></div>

<div class="layui-card">
  <div class="layui-card-header">主机信息</div>
  <div class="layui-card-body" style="padding:0;">
    <table class="zj-table">
      <tbody>
        <tr><td style="width:120px;">状态</td><td>
          <span class="zj-status zj-status-<?= htmlspecialchars($host['status']) ?>">
            <?= htmlspecialchars(zjmf_host_status_label($host['status'])) ?>
          </span>
          <?php if (!empty($info['ok']) && $info['status'] !== $host['status']): ?>
            <span class="zj-muted">（实时：<?= htmlspecialchars(zjmf_host_status_label($info['status'])) ?>）</span>
          <?php endif; ?>
        </td></tr>
        <tr><td>供应商</td><td><?= htmlspecialchars($host['supplier_name'] ?: '-') ?></td></tr>
        <tr><td>用户名</td><td class="zj-mono" id="zjf-username"><?=
          htmlspecialchars(zjmf_mask_account($host['username']))
        ?></td></tr>
        <tr><td>密码</td><td class="zj-mono" id="zjf-password"><?= $password !== '' ? '••••••••' : '-' ?></td></tr>
        <tr><td>周期</td><td><?= htmlspecialchars($host['cycle'] ?: '-') ?></td></tr>
        <tr><td>到期时间</td><td><?= htmlspecialchars($host['renew_date'] ?: '-') ?></td></tr>
        <tr><td>上游主机 ID</td><td class="zj-mono"><?= (int)$host['up_host_id'] ?></td></tr>
      </tbody>
    </table>
  </div>
</div>

<div class="layui-card">
  <div class="layui-card-header">流量使用</div>
  <div class="layui-card-body" style="padding:16px;font-size:13px;color:#555;">
    <?php if (!empty($traffic['ok'])): ?>
      <?= zjmf_view_show_traffic($traffic['data']) ?>
    <?php else: ?>
      <span class="zj-muted"><?= (($traffic['msg'] ?? '') ?: '流量查询失败') ?></span>
    <?php endif; ?>
  </div>
</div>

<?php if ($hasUpId): ?>
<div class="layui-card">
  <div class="layui-card-header">主机操作</div>
  <div class="layui-card-body">
    <p class="zj-muted" style="margin-top:0;">重启 / 重装 / 重置密码为高危操作，请谨慎执行。</p>
    <button type="button" class="layui-btn layui-btn-sm" data-act="on">开机</button>
    <button type="button" class="layui-btn layui-btn-sm layui-btn-warm" data-act="off">关机</button>
    <button type="button" class="layui-btn layui-btn-sm layui-btn-warm" data-act="reboot">重启</button>
    <button type="button" class="layui-btn layui-btn-sm layui-btn-danger" data-act="reinstall">重装系统</button>
    <button type="button" class="layui-btn layui-btn-sm" data-act="reset_password">重置密码</button>

    <div id="zjf-pass-panel" style="display:none;margin-top:14px;padding:14px;
      background:#fafbfc;border:1px solid #eee;border-radius:6px;">
      <input type="password" id="zjf-pass-input" class="layui-input" style="max-width:260px;"
             placeholder="输入新密码（至少 6 位）">
      <button type="button" class="layui-btn layui-btn-sm" id="zjf-pass-confirm"
              style="margin-top:10px;">确认重置</button>
    </div>
  </div>
</div>
<?php endif; ?>

<script>
(function () {
  var msg = document.getElementById('zjf-msg');
  var passPanel = document.getElementById('zjf-pass-panel');
  var passInput = document.getElementById('zjf-pass-input');
  var pendingAction = '';

  function show(text, ok) {
    msg.textContent = text;
    msg.className = 'zj-msg zj-msg-show ' + (ok ? 'zj-msg-success' : 'zj-msg-error');
  }
  function call(action, extra) {
    var body = new URLSearchParams();
    body.append('host_id', '<?= (int)$host['id'] ?>');
    body.append('action', action);
    for (var k in (extra || {})) body.append(k, extra[k]);
    fetch('<?= zjmf_url('reserve/api/host_action') ?>', {
      method: 'POST',
      headers: {'Content-Type': 'application/x-www-form-urlencoded'},
      body: body.toString()
    }).then(function (r) { return r.json(); }).then(function (res) {
      var ok = res.code === 'ok' || res.success;
      show(res.msg || res.code || '操作成功', ok);
      if (ok) setTimeout(function () { location.reload(); }, 800);
    }).catch(function () {
      show('网络错误，请重试', false);
    });
  }

  document.querySelectorAll('[data-act]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var act = btn.getAttribute('data-act');
      if (act === 'reset_password') {
        pendingAction = act;
        passPanel.style.display = 'block';
        passInput.focus();
        return;
      }
      if (act === 'reinstall') {
        if (!confirm('确定要重装该系统吗？重装将清空数据且不可恢复！')) return;
      }
      if (act === 'reboot') {
        if (!confirm('确定要重启该主机吗？')) return;
      }
      call(act, {});
    });
  });

  document.getElementById('zjf-pass-confirm').addEventListener('click', function () {
    var pwd = passInput.value;
    if (!pwd || pwd.length < 6) {
      show('密码至少 6 位', false);
      return;
    }
    call('reset_password', {password: pwd});
  });
})();
</script>
<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
