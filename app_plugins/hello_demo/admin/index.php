<?php
if (!defined('IN_CRONLITE')) {
	exit;
}
mnbt_admin_include('head');
$welcome = mnbt_plugin_option_get('hello_demo', 'welcome', '你好，MNBT 插件！');
$pingCount = (int)mnbt_plugin_option_get('hello_demo', 'ping_count', 0);
$events = mnbt_plugin_option_get('hello_demo', 'host_events', []);
if (!is_array($events)) {
	$events = [];
}
?>
<div class="container-fluid p-t-15">
  <div class="card">
    <div class="card-header"><h4>Hello 示例插件</h4></div>
    <div class="card-body">
      <p class="text-muted">本页演示：插件配置读写、AJAX 接口、主机生命周期事件记录。</p>
      <div class="form-group">
        <label>欢迎语</label>
        <input type="text" class="form-control" id="hello-welcome" value="<?=htmlspecialchars((string)$welcome, ENT_QUOTES, 'UTF-8')?>" maxlength="200">
      </div>
      <button type="button" class="btn btn-primary" id="btn-save">保存配置</button>
      <button type="button" class="btn btn-info" id="btn-ping">测试 AJAX（Ping）</button>
      <span class="ml-2 text-muted">Ping 次数：<strong id="ping-count"><?=(int)$pingCount?></strong></span>
      <hr>
      <h5>主机事件日志（最近 50 条）</h5>
      <pre class="bg-light p-3" style="max-height:320px;overflow:auto" id="event-log"><?php
if (!$events) {
	echo "（暂无。开通/暂停/删除主机后会出现在这里）\n";
} else {
	echo htmlspecialchars(implode("\n", $events), ENT_QUOTES, 'UTF-8');
}
?></pre>
    </div>
  </div>
</div>
<script>
function parseRes(res) {
  try { return typeof res === 'string' ? JSON.parse(res) : res; } catch (e) { return {code: res}; }
}
$('#btn-save').on('click', function () {
  $.post('ajax.php', {gn: 'p_hello_demo_save', welcome: $('#hello-welcome').val()}, function (res) {
    var d = parseRes(res);
    var msg = d.msg || d.code || '完成';
    if (typeof $.notify === 'function') $.notify({message: msg}, {type: (d.qk == 1 || d.success) ? 'success' : 'danger'});
    else alert(msg);
  });
});
$('#btn-ping').on('click', function () {
  $.post('ajax.php', {gn: 'p_hello_demo_ping'}, function (res) {
    var d = parseRes(res);
    var msg = d.msg || d.code || '完成';
    if (d.ping_count != null) $('#ping-count').text(d.ping_count);
    if (typeof $.notify === 'function') $.notify({message: msg}, {type: (d.qk == 1 || d.success) ? 'success' : 'danger'});
    else alert(msg);
  });
});
</script>
