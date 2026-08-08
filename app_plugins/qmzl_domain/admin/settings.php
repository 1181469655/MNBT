<?php
/**
 * qmzl_domain - 后台插件设置
 * 运营模式切换 + 代理商账号配置。
 */
if (!defined('IN_CRONLITE')) exit;
mnbt_admin_include('head');
$enabled  = qmzl_setting_get('enabled', 'true') === 'true';
$mode     = qmzl_mode();
$agentUsername = (string)qmzl_setting_get('agent_username', '');
$agentStatus  = (string)qmzl_setting_get('agent_status', '');
$agentMsg     = (string)qmzl_setting_get('agent_msg', '');
$agentExp     = (int)qmzl_setting_get('agent_jwt_expire', 0);
$markupMap  = qmzl_markup_map();
$agentSuffixes = [];
if ($mode === 'agent') {
	$tok = qmzl_agent_token();
	if ($tok['ok']) {
		$cfg = qmzl_config($tok['data']['jwt']);
		if ($cfg['ok'] && !empty($cfg['data']['specify_search_domain']) && is_array($cfg['data']['specify_search_domain'])) {
			$agentSuffixes = $cfg['data']['specify_search_domain'];
		}
	}
}
// 合并已配置溢价的未列出后缀，并补全默认后缀兜底
if (empty($agentSuffixes)) $agentSuffixes = ['.com', '.cn', '.net', '.com.cn', '.top', '.xyz'];
$suffixSet = [];
foreach ($agentSuffixes as $s) {
	$suffixSet[trim((string)$s)] = true;
}
foreach (array_keys($markupMap) as $s) {
	if (!isset($suffixSet[$s])) $suffixSet[$s] = true;
}
$suffixList = array_keys($suffixSet);
?>
<div class="container-fluid p-t-15">
  <div class="row">
    <div class="col-lg-8">
      <div class="card">
        <header class="card-header"><div class="card-title">启明智联域名注册 - 插件设置</div></header>
        <div class="card-body">

          <div class="callout callout-info">
            <p class="small">
              本插件对接 <strong>启明智联平台</strong> 域名注册 API（地址写死：<code>https://cloud.qimingidc.cn/console/v1</code>）。<br>
              <strong>前置依赖：</strong>需要先安装并启用 <code>user_info</code> 插件（独立用户系统）。
            </p>
          </div>

          <div class="form-group">
            <label class="btn-block">用户端功能开关</label>
            <div class="col-xs-6">
              <div class="custom-control custom-switch custom-info">
                <input type="checkbox" class="custom-control-input" name="enabled" id="enabled" <?= $enabled ? 'checked' : '' ?>>
                <label class="custom-control-label" for="enabled">启用后，用户端提供域名注册功能</label>
              </div>
            </div>
          </div>

          <div class="form-group">
            <label class="control-label">运营模式</label>
            <select class="form-control" id="mode" style="max-width:420px;" onchange="toggleMode()">
              <option value="client" <?= $mode === 'client' ? 'selected' : '' ?>>客户自注册（每个客户绑定自己的启明智联账号，支付跳转上游）</option>
              <option value="agent" <?= $mode === 'agent' ? 'selected' : '' ?>>代理商模式（用管理员账号代注册，客户走本站支付系统）</option>
            </select>
            <small class="text-muted">
              客户自注册入口：<code>index.php?_r=/qmzl</code>；
              代理商模式入口：<code>index.php?_r=/qmzl_domain</code>
            </small>
          </div>

          <div class="form-group">
            <button type="button" class="btn btn-primary" onclick="saveSetting()">
              <span class="mdi mdi-content-save"></span> 保存设置
            </button>
          </div>
        </div>
      </div>

      <!-- 代理商模式配置 -->
      <div class="card" id="agent-card" style="<?= $mode === 'agent' ? '' : 'display:none;' ?>">
        <header class="card-header"><div class="card-title">代理商鉴权（开放接口 API 密钥）</div></header>
        <div class="card-body">
          <div class="callout callout-info">
            <p class="small">
              上游登录需人机验证，无法用密码自动换取凭证。管理员请使用 <strong>开放接口鉴权</strong>：
              在 <a href="https://cloud.qimingidc.cn" target="_blank">cloud.qimingidc.cn</a> 控制台 → 「API密钥」→ 创建，得到 API 密钥 token；
              此处填写平台注册账号（邮箱/手机号）与 API 密钥，插件调用 <code>POST /api/v1/auth</code> 自动换取 JWT（过期自动续期，无需人机验证），
              并用于代注册与余额支付。
            </p>
          </div>
          <?php if ($agentUsername !== ''): ?>
            <div class="callout <?= $agentStatus === 'error' ? 'callout-danger' : 'callout-success' ?>">
              <p class="small">
                当前代理商账号：<code><?= htmlspecialchars($agentUsername, ENT_QUOTES, 'UTF-8') ?></code>
                <?php if ($agentStatus === 'error'): ?>
                  <br><span class="text-danger">最近鉴权失败：<?= htmlspecialchars($agentMsg, ENT_QUOTES, 'UTF-8') ?></span>
                <?php else: ?>
                  <br><span class="text-success">鉴权正常<?= $agentExp > 0 ? '，凭证有效期至 ' . date('Y-m-d H:i', $agentExp) : '' ?></span>
                <?php endif; ?>
              </p>
            </div>
          <?php endif; ?>
          <div class="form-group">
            <label class="control-label">平台账号（注册邮箱/手机号）</label>
            <input type="text" class="form-control" id="agent_username" placeholder="启明智联平台注册账号" style="max-width:420px;" value="<?= htmlspecialchars($agentUsername, ENT_QUOTES, 'UTF-8') ?>">
          </div>
          <div class="form-group">
            <label class="control-label">API 密钥 token</label>
            <input type="password" class="form-control" id="agent_api_token" placeholder="控制台 → API密钥 → 创建 得到的 token" style="max-width:420px;" autocomplete="off">
          </div>
          <div class="form-group">
            <button type="button" class="btn btn-info" onclick="agentTest()">
              <span class="mdi mdi-connection"></span> 验证并保存
            </button>
          </div>
        </div>
      </div>

      <!-- 后缀溢价（仅代理商模式） -->
      <div class="card" id="markup-card" style="<?= $mode === 'agent' ? '' : 'display:none;' ?>">
        <header class="card-header"><div class="card-title">后缀溢价（代理商模式）</div></header>
        <div class="card-body">
          <div class="callout callout-info">
            <p class="small">为各后缀设置<b>一次性溢价</b>（元）：客户下单时将在上游价格基础上加收该金额，并计入本站支付订单金额。留空或填 0 表示不加价。修改后点「保存设置」生效。</p>
          </div>
          <table class="table table-bordered" style="max-width:520px;">
            <thead>
              <tr><th style="width:140px;">后缀</th><th>溢价（元）</th><th style="width:60px;"></th></tr>
            </thead>
            <tbody id="markup-tbody">
              <?php foreach ($suffixList as $s): ?>
                <tr>
                  <td><input type="text" class="form-control mk-suffix" value="<?= htmlspecialchars($s, ENT_QUOTES, 'UTF-8') ?>" style="font-family:monospace;"></td>
                  <td><input type="number" class="form-control mk-amount" step="0.01" min="0" value="<?= htmlspecialchars((string)($markupMap[$s] ?? ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="0"></td>
                  <td><button type="button" class="btn btn-xs btn-danger" onclick="$(this).closest('tr').remove()">删除</button></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
          <button type="button" class="btn btn-xs btn-default" onclick="addMarkupRow()">
            <span class="mdi mdi-plus"></span> 添加后缀
          </button>
        </div>
      </div>
    </div>

    <div class="col-lg-4">
      <div class="card">
        <header class="card-header"><div class="card-title">说明</div></header>
        <div class="card-body">
          <p class="small text-muted">
            <strong>客户自注册模式：</strong>客户在「云账号」页绑定自己的启明智联账号（需完成人机验证），查询/价格/信息模板均以客户账号数据为准，下单后跳转上游支付。
          </p>
          <p class="small text-muted">
            <strong>代理商模式：</strong>管理员配置平台账号 + API 密钥（开放接口鉴权，无需人机验证，JWT 过期自动续期）。客户下单走本站支付系统（MN_dd + 已启用的支付插件），支付成功后自动代注册并扣减账户余额。余额不足时订单标记为失败，可在「订单记录」中重试。
          </p>
          <p class="small text-muted">
            <strong>信息模板：</strong>两种模式下客户都需先创建并完成实名认证的信息模板；代理商模式下模板创建在管理员账号名下。
          </p>
        </div>
      </div>
    </div>
  </div>
</div>

<script type="text/javascript">
function toggleMode() {
  var agent = $('#mode').val() === 'agent';
  $('#agent-card').toggle(agent);
  $('#markup-card').toggle(agent);
}

function addMarkupRow() {
  $('#markup-tbody').append(
    '<tr>' +
      '<td><input type="text" class="form-control mk-suffix" style="font-family:monospace;" placeholder=".com"></td>' +
      '<td><input type="number" class="form-control mk-amount" step="0.01" min="0" placeholder="0"></td>' +
      '<td><button type="button" class="btn btn-xs btn-danger" onclick="$(this).closest(\'tr\').remove()">删除</button></td>' +
    '</tr>'
  );
}

function saveSetting() {
  var markup = {};
  $('#markup-tbody tr').each(function () {
    var suffix = $.trim($(this).find('.mk-suffix').val());
    var amount = parseFloat($(this).find('.mk-amount').val());
    if (suffix && !isNaN(amount) && amount > 0) markup[suffix] = amount;
  });
  msloading('保存中...');
  $.post('ajax.php', {
    gn: 'p_qmzl_setting_save',
    enabled: $('#enabled').prop('checked') ? 1 : 0,
    mode: $('#mode').val(),
    markup: JSON.stringify(markup)
  }, function (date) {
    msloadingde();
    var jsoe = typeof date === 'string' ? JSON.parse(date) : date;
    if (jsoe.qk === 1) { msalert(1, '保存成功', 4000); }
    else { msalert(3, jsoe.msg || '保存失败', 4000); }
  }).fail(function () { msloadingde(); msalert(3, '网络错误', 4000); });
}

function agentTest() {
  var username = $('#agent_username').val();
  var apiToken = $('#agent_api_token').val();
  if (!username || !apiToken) { msalert(3, '请填写平台账号和 API 密钥', 4000); return; }
  msloading('验证中...');
  $.post('ajax.php', {
    gn: 'p_qmzl_agent_test',
    username: username,
    api_token: apiToken
  }, function (date) {
    msloadingde();
    var jsoe = typeof date === 'string' ? JSON.parse(date) : date;
    if (jsoe.qk === 1) { msalert(1, '验证成功，代理商凭证已保存', 4000); }
    else { msalert(3, jsoe.msg || '验证失败', 4000); }
  }).fail(function () { msloadingde(); msalert(3, '网络错误', 4000); });
}
</script>
</body>
</html>
