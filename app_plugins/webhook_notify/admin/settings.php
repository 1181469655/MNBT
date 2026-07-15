<?php
if (!defined('IN_CRONLITE')) {
	exit;
}
mnbt_admin_include('head');
$cfg = function_exists('webhook_notify_config') ? webhook_notify_config() : [
	'url' => '', 'secret' => '', 'enabled' => true,
	'events' => [
		'host.created' => true, 'host.paused' => true, 'host.unpaused' => true,
		'host.renewed' => true, 'host.deleted' => true, 'order.paid' => true,
	],
];
$insecure = mnbt_plugin_option_get('webhook_notify', 'insecure_ssl', 'false') === 'true';
$logs = mnbt_plugin_option_get('webhook_notify', 'delivery_log', []);
if (!is_array($logs)) {
	$logs = [];
}
$eventLabels = [
	'host.created' => '主机开通',
	'host.paused' => '主机暂停',
	'host.unpaused' => '主机恢复',
	'host.renewed' => '主机续费',
	'host.deleted' => '主机删除',
	'order.paid' => '订单支付成功',
];
?>
<div class="container-fluid p-t-15">
  <div class="card">
    <div class="card-header"><h4>Webhook 通知</h4></div>
    <div class="card-body">
      <p class="text-muted">事件发生时向指定 URL 发送 JSON（POST）。可选 HMAC-SHA256 签名头 <code>X-MNBT-Signature</code>。</p>
      <div class="form-group">
        <label>启用</label>
        <div class="custom-control custom-switch">
          <input type="checkbox" class="custom-control-input" id="wh-enabled" <?=!empty($cfg['enabled']) ? 'checked' : ''?>>
          <label class="custom-control-label" for="wh-enabled">开启推送</label>
        </div>
      </div>
      <div class="form-group">
        <label>Webhook URL</label>
        <input type="url" class="form-control" id="wh-url" value="<?=htmlspecialchars($cfg['url'], ENT_QUOTES, 'UTF-8')?>" placeholder="https://example.com/hook">
      </div>
      <div class="form-group">
        <label>签名密钥（可选）</label>
        <input type="text" class="form-control" id="wh-secret" value="<?=htmlspecialchars($cfg['secret'], ENT_QUOTES, 'UTF-8')?>" placeholder="用于 HMAC-SHA256">
        <small class="text-muted">签名：sha256=HMAC_SHA256(body, secret)，请求头 X-MNBT-Signature</small>
      </div>
      <div class="form-group">
        <div class="custom-control custom-switch">
          <input type="checkbox" class="custom-control-input" id="wh-insecure" <?=$insecure ? 'checked' : ''?>>
          <label class="custom-control-label" for="wh-insecure">跳过 HTTPS 证书校验（不推荐）</label>
        </div>
      </div>
      <div class="form-group">
        <label>订阅事件</label>
        <div class="row">
          <?php foreach ($eventLabels as $ek => $lab): ?>
          <div class="col-md-4">
            <div class="custom-control custom-checkbox">
              <input type="checkbox" class="custom-control-input wh-event" id="ev-<?=htmlspecialchars($ek, ENT_QUOTES, 'UTF-8')?>" data-ev="<?=htmlspecialchars($ek, ENT_QUOTES, 'UTF-8')?>" <?=!empty($cfg['events'][$ek]) ? 'checked' : ''?>>
              <label class="custom-control-label" for="ev-<?=htmlspecialchars($ek, ENT_QUOTES, 'UTF-8')?>"><?=htmlspecialchars($lab, ENT_QUOTES, 'UTF-8')?> <code class="small"><?=$ek?></code></label>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
      <button type="button" class="btn btn-primary" id="wh-save">保存</button>
      <button type="button" class="btn btn-info" id="wh-test">发送测试</button>
      <hr>
      <h5>投递日志</h5>
      <pre class="bg-light p-3" style="max-height:280px;overflow:auto"><?php
if (!$logs) {
	echo "（暂无）\n";
} else {
	echo htmlspecialchars(implode("\n", $logs), ENT_QUOTES, 'UTF-8');
}
?></pre>
      <h5 class="mt-3">请求体示例</h5>
      <pre class="bg-light p-3 small">{
  "event": "host.created",
  "time": "2026-07-16T12:00:00+08:00",
  "source": "mnbt",
  "payload": {
    "host": { "user": "demo001", "ssbt": "bt1", "btid": "12", "sqldz": "mnbt.1xxx", "datae": "2027-01-01" },
    "ctx": { "source": "admin" }
  }
}</pre>
    </div>
  </div>
</div>
<script>
function parseRes(res) {
  try { return typeof res === 'string' ? JSON.parse(res) : res; } catch (e) { return {code: res}; }
}
function collectEvents() {
  var o = {};
  $('.wh-event').each(function () {
    o[$(this).data('ev')] = $(this).is(':checked');
  });
  return o;
}
$('#wh-save').on('click', function () {
  $.post('ajax.php', {
    gn: 'p_webhook_notify_save',
    url: $('#wh-url').val(),
    secret: $('#wh-secret').val(),
    enabled: $('#wh-enabled').is(':checked') ? 'true' : 'false',
    insecure_ssl: $('#wh-insecure').is(':checked') ? 'true' : 'false',
    events: JSON.stringify(collectEvents())
  }, function (res) {
    var d = parseRes(res);
    var msg = d.msg || d.code || '完成';
    if (typeof $.notify === 'function') $.notify({message: msg}, {type: (d.qk == 1 || d.success) ? 'success' : 'danger'});
    else alert(msg);
    if (d.qk == 1 || d.success) setTimeout(function () { location.reload(); }, 600);
  });
});
$('#wh-test').on('click', function () {
  $.post('ajax.php', {gn: 'p_webhook_notify_test'}, function (res) {
    var d = parseRes(res);
    var msg = d.msg || d.code || '完成';
    if (typeof $.notify === 'function') $.notify({message: msg}, {type: (d.qk == 1 || d.success) ? 'success' : 'danger'});
    else alert(msg);
    if (d.qk == 1 || d.success) setTimeout(function () { location.reload(); }, 800);
  });
});
</script>
