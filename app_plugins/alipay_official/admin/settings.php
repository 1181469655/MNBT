<?php
if (!defined('IN_CRONLITE')) {
	exit;
}
mnbt_admin_include('head');
?>
<link rel="stylesheet" href="<?=mnbt_theme_asset('set-page.css', 'admin')?>">
<link rel="stylesheet" href="<?=mnbt_theme_asset('pay-settings.css', 'admin')?>">

<div class="mn-set-page">
<?php
$cfg = [
	'app_id'      => (string)mnbt_plugin_option_get('alipay_official', 'app_id', ''),
	'private_key' => (string)mnbt_plugin_option_get('alipay_official', 'private_key', ''),
	'public_key'  => (string)mnbt_plugin_option_get('alipay_official', 'public_key', ''),
	'gateway'     => (string)mnbt_plugin_option_get('alipay_official', 'gateway', ''),
];
$configured = $cfg['app_id'] !== '' && $cfg['private_key'] !== '' && $cfg['public_key'] !== '';
$gatewayDefault = $cfg['gateway'] === '' ? 'https://openapi.alipay.com/gateway.do' : $cfg['gateway'];

// 计算回调 URL
$scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
$siteRoot = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https://' : 'http://')
          . ($_SERVER['HTTP_HOST'] ?? 'localhost')
          . substr($scriptName, 0, strrpos($scriptName, '/'));
if (substr($siteRoot, -6) === '/admin') {
	$siteRoot = substr($siteRoot, 0, -6);
}
$notifyUrl = $siteRoot . '/pay/alipay_official/notify';
$returnUrl = $siteRoot . '/pay/alipay_official/return';
?>

<div class="mn-set-card">
  <div class="mn-set-card-hd">
    <div class="mn-set-icon"><i class="mdi mdi-alpha-a-circle"></i></div>
    <div>
      <h4>支付宝官方设置</h4>
      <p>配置支付宝开放平台应用的 RSA2 凭证</p>
    </div>
  </div>
  <div class="mn-set-card-bd">
    <div class="mn-pay-note">
      <b>说明：</b>本页仅配置支付宝官方 API 凭证。是否在客户端显示该插件的付款方式，请前往 <a href="pay_settings.php" class="alert-link">支付设置</a> 启用。
    </div>
  </div>
</div>

<div class="mn-set-card">
  <div class="mn-set-card-bd">
    <div class="mn-set-field">
      <label for="alipay_app_id">应用 APPID</label>
      <input type="text" id="alipay_app_id" class="form-control" value="<?=htmlspecialchars($cfg['app_id'], ENT_QUOTES)?>" placeholder="开放平台应用 APPID" required>
      <small>开放平台 → 应用列表 → 对应应用的 APPID</small>
    </div>
    <div class="mn-set-field">
      <label for="alipay_private_key">应用私钥</label>
      <textarea id="alipay_private_key" class="form-control" rows="5" placeholder="RSA2 应用私钥（不含 -----BEGIN/END----- 标记）" required><?=htmlspecialchars($cfg['private_key'], ENT_QUOTES)?></textarea>
      <small>开放平台 → 密钥管理 → 应用私钥（PKCS1 或 PKCS8 格式均可，仅 base64 字符串部分）</small>
    </div>
    <div class="mn-set-field">
      <label for="alipay_public_key">支付宝公钥</label>
      <textarea id="alipay_public_key" class="form-control" rows="5" placeholder="支付宝公钥（不含 -----BEGIN/END----- 标记）" required><?=htmlspecialchars($cfg['public_key'], ENT_QUOTES)?></textarea>
      <small>开放平台 → 密钥管理 → 支付宝公钥（用于验签异步通知）</small>
    </div>
    <div class="mn-set-field">
      <label for="alipay_gateway">网关地址</label>
      <input type="text" id="alipay_gateway" class="form-control" value="<?=htmlspecialchars($gatewayDefault, ENT_QUOTES)?>" placeholder="https://openapi.alipay.com/gateway.do">
      <small>默认正式环境；沙箱测试可改为 <code>https://openapi-sandbox.dl.alipaydev.com/gateway.do</code></small>
    </div>
    <div class="mn-set-actions">
      <button type="button" class="btn btn-primary btn-block" onclick="alipayOfficialSave()"><i class="mdi mdi-content-save-outline"></i> 保存配置</button>
    </div>
    <?php if (!$configured): ?>
    <div class="mn-set-note" style="background:#fef2f2;border-color:#fecaca;color:#991b1b">
      <b>未配置：</b>请填写完整的三项凭证，否则支付宝官方相关付款方式将不可用。
    </div>
    <?php else: ?>
    <div class="mn-set-note" style="background:#f0fdf4;border-color:#bbf7d0;color:#166534">
      <b>已配置：</b>凭证已就绪。可在 <a href="pay_settings.php" class="alert-link" style="color:#166534">支付设置</a> 启用电脑网站支付或扫码支付。
    </div>
    <?php endif; ?>
  </div>
</div>

<div class="mn-set-card">
  <div class="mn-set-card-hd">
    <div class="mn-set-icon"><i class="mdi mdi-link-variant"></i></div>
    <div>
      <h4>回调地址</h4>
      <p>在支付宝开放平台应用配置中需填入以下地址</p>
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
            <td><code><?=htmlspecialchars($notifyUrl, ENT_QUOTES)?></code></td>
          </tr>
          <tr>
            <td>同步返回（return_url）</td>
            <td><code><?=htmlspecialchars($returnUrl, ENT_QUOTES)?></code></td>
          </tr>
        </tbody>
      </table>
    </div>
    <div class="mn-set-note">
      回调路径由系统通过通用路由自动注册。请在支付宝开放平台对应应用的"接口加签方式"中选择 <code>公钥模式</code>，并确保服务器时间准确（RSA2 验签对时间戳敏感）。
    </div>
  </div>
</div>

<div class="mn-set-card">
  <div class="mn-set-card-hd">
    <div class="mn-set-icon"><i class="mdi mdi-alert-circle-outline"></i></div>
    <div>
      <h4>环境要求</h4>
      <p>使用本插件需服务器满足以下条件</p>
    </div>
  </div>
  <div class="mn-set-card-bd">
    <ul class="mb-0" style="font-size:13px;line-height:1.9;color:#475569">
      <li>PHP 已启用 <code>openssl</code> 扩展（RSA2 签名必需）</li>
      <li>PHP 已启用 <code>curl</code> 扩展（调用 alipay.trade.precreate 必需）</li>
      <li>已配置 nginx/apache rewrite，将未命中文件的请求转发到 <code>index.php</code>（异步通知需要）</li>
      <li>开放平台应用已开通"<b>电脑网站支付</b>"和"<b>当面付</b>"能力</li>
    </ul>
  </div>
</div>
</div>

<script type="text/javascript">
function alipayOfficialSave() {
	var appId      = $.trim($('#alipay_app_id').val());
	var privateKey = $.trim($('#alipay_private_key').val());
	var publicKey  = $.trim($('#alipay_public_key').val());
	var gateway    = $.trim($('#alipay_gateway').val());
	if (appId === '' || privateKey === '' || publicKey === '') {
		msalert(3, '请填写完整的 APPID、应用私钥和支付宝公钥', 2500);
		return;
	}
	msloading('正在保存...', 'text-info', 'text-info');
	$.post('ajax.php', {
		gn: 'alipay_official_save',
		app_id: appId,
		private_key: privateKey,
		public_key: publicKey,
		gateway: gateway
	}, function (resp) {
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
