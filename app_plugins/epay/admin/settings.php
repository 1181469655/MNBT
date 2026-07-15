<?php
if (!defined('IN_CRONLITE')) {
	exit;
}
mnbt_admin_include('head');
?>
<link rel="stylesheet" href="<?=mnbt_theme_asset('set-page.css', 'admin')?>">
<link rel="stylesheet" href="<?=mnbt_theme_asset('pay-settings.css', 'admin')?>">
<link rel="stylesheet" href="<?=mnbt_asset_url('js/bootstrap-table/bootstrap-table.min.css')?>">

<div class="mn-set-page">
<?php
$cfg = [
	'apiurl' => (string)mnbt_plugin_option_get('epay', 'apiurl', ''),
	'pid'    => (string)mnbt_plugin_option_get('epay', 'pid', ''),
	'key'    => (string)mnbt_plugin_option_get('epay', 'key', ''),
];
$configured = $cfg['apiurl'] !== '' && $cfg['pid'] !== '' && $cfg['key'] !== '';

// 计算站点根 URL（admin/plugin.php → 去掉 /admin）
$scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
$siteRoot = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https://' : 'http://')
          . ($_SERVER['HTTP_HOST'] ?? 'localhost')
          . substr($scriptName, 0, strrpos($scriptName, '/')); // 去掉 /plugin.php
// 再去掉末尾的 /admin（如果存在）
if (substr($siteRoot, -6) === '/admin') {
	$siteRoot = substr($siteRoot, 0, -6);
}
$notifyUrl = $siteRoot . '/pay/epay/notify';
$returnUrl = $siteRoot . '/pay/epay/return';
?>

<div class="mn-set-card">
  <div class="mn-set-card-hd">
    <div class="mn-set-icon"><i class="mdi mdi-credit-card-multiple"></i></div>
    <div>
      <h4>易支付设置</h4>
      <p>配置彩虹易支付协议的接口地址、商户 ID 与密钥</p>
    </div>
  </div>
  <div class="mn-set-card-bd">
    <div class="mn-pay-note">
      <b>说明：</b>本页仅配置易支付的 API 凭证。是否在客户端显示该插件的付款方式，请前往 <a href="pay_settings.php" class="alert-link">支付设置</a> 启用。
    </div>
  </div>
</div>

<div class="mn-set-card">
  <div class="mn-set-card-bd">
    <div class="mn-set-field">
      <label for="epay_apiurl">易支付接口地址</label>
      <input type="text" id="epay_apiurl" class="form-control" value="<?=htmlspecialchars($cfg['apiurl'], ENT_QUOTES)?>" placeholder="https://pay.example.com/" required>
      <small>易支付站点根 URL，需以 <code>/</code> 结尾</small>
    </div>
    <div class="mn-set-field">
      <label for="epay_pid">商户 ID（PID）</label>
      <input type="text" id="epay_pid" class="form-control" value="<?=htmlspecialchars($cfg['pid'], ENT_QUOTES)?>" placeholder="例如 1001" required>
    </div>
    <div class="mn-set-field">
      <label for="epay_key">商户密钥（KEY）</label>
      <input type="text" id="epay_key" class="form-control" value="<?=htmlspecialchars($cfg['key'], ENT_QUOTES)?>" placeholder="在易支付商户后台获取" required>
    </div>
    <div class="mn-set-actions">
      <button type="button" class="btn btn-primary btn-block" onclick="epaySave()"><i class="mdi mdi-content-save-outline"></i> 保存配置</button>
    </div>
    <?php if (!$configured): ?>
    <div class="mn-set-note" style="background:#fef2f2;border-color:#fecaca;color:#991b1b">
      <b>未配置：</b>请填写完整的三项参数，否则易支付相关付款方式将不可用。
    </div>
    <?php else: ?>
    <div class="mn-set-note" style="background:#f0fdf4;border-color:#bbf7d0;color:#166534">
      <b>已配置：</b>易支付凭证已就绪。可在 <a href="pay_settings.php" class="alert-link" style="color:#166534">支付设置</a> 启用支付宝/微信/QQ 钱包。
    </div>
    <?php endif; ?>
  </div>
</div>

<div class="mn-set-card">
  <div class="mn-set-card-hd">
    <div class="mn-set-icon"><i class="mdi mdi-link-variant"></i></div>
    <div>
      <h4>回调地址</h4>
      <p>在易支付商户后台无需手动配置，本插件自动注册</p>
    </div>
  </div>
  <div class="mn-set-card-bd">
    <div class="table-responsive">
      <table class="table mn-set-table">
        <thead>
          <tr><th style="width:140px">类型</th><th>URL</th></tr>
        </thead>
        <tbody>
          <tr>
            <td>异步通知（notify_url）</td>
            <td><code id="epay_notify"><?=htmlspecialchars($notifyUrl, ENT_QUOTES)?></code></td>
          </tr>
          <tr>
            <td>同步返回（return_url）</td>
            <td><code id="epay_return"><?=htmlspecialchars($returnUrl, ENT_QUOTES)?></code></td>
          </tr>
        </tbody>
      </table>
    </div>
    <div class="mn-set-note">
      回调路径由系统通过通用路由自动注册，无需在 Web 服务器上单独配置。若使用 nginx，请确保未命中的请求被转发到 <code>index.php</code>（详见插件开发文档）。
    </div>
  </div>
</div>
</div>

<script type="text/javascript">
function epaySave() {
	var apiurl = $.trim($('#epay_apiurl').val());
	var pid    = $.trim($('#epay_pid').val());
	var key    = $.trim($('#epay_key').val());
	if (apiurl === '' || pid === '' || key === '') {
		msalert(3, '请填写完整的接口地址、商户ID和密钥', 2500);
		return;
	}
	msloading('正在保存...', 'text-info', 'text-info');
	$.post('ajax.php', { gn: 'epay_save', apiurl: apiurl, pid: pid, key: key }, function (resp) {
		var j = JSON.parse(resp);
		var qk = j.code;
		if (qk === '保存成功') {
			msalert(1, '保存成功！', 1800);
			msloadingde();
		} else {
			msalert(4, qk || '保存失败', 2500);
			msloadingde();
		}
	}).fail(function () {
		msalert(4, '网络错误', 2500);
		msloadingde();
	});
}
</script>
