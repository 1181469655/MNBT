<?php
/**
 * geetest_captcha - 后台插件设置
 * 配置极验 4.0 的 captchaId / captcha_key。
 * 获取方式：极验后台「行为验证 4.0」→ 新增验证项目，选择 Web 使用场景。
 */
if (!defined('IN_CRONLITE')) exit;
mnbt_admin_include('head');
$captchaId  = (string)mnbt_plugin_option_get('geetest_captcha', 'captcha_id', '');
$captchaKey = (string)mnbt_plugin_option_get('geetest_captcha', 'captcha_key', '');
?>
<div class="container-fluid p-t-15">
  <div class="row">
    <div class="col-lg-8">
      <div class="card">
        <header class="card-header"><div class="card-title">极验行为验证 4.0 - 插件设置</div></header>
        <div class="card-body">

          <div class="callout callout-info">
            <p class="small">
              本插件启用并完成配置后，三端登录（管理后台 / 用户控制台 / Docker 控制台）的人机验证将自动切换为
              <strong>极验行为验证 4.0</strong>（内置滑块验证码自动退位）。<br>
              停用本插件或清空密钥后，内置滑块验证码自动复位。<br>
              <strong>密钥获取：</strong>极验后台 <a href="https://auth.geetest.com/" target="_blank">auth.geetest.com</a>
              → 行为验证 4.0 → 新增验证项目（Web 场景），即可获得 captchaId 与 captcha_key。
            </p>
          </div>

          <div class="form-group">
            <label class="btn-block">captchaId（验证 ID）</label>
            <input type="text" class="form-control" id="gt4_captcha_id" placeholder="请在极验后台获取" value="<?= htmlspecialchars($captchaId, ENT_QUOTES, 'UTF-8') ?>" autocomplete="off">
          </div>

          <div class="form-group">
            <label class="btn-block">captcha_key（验证密钥，仅本机存储，请勿泄露）</label>
            <input type="text" class="form-control" id="gt4_captcha_key" placeholder="请在极验后台获取" value="<?= htmlspecialchars($captchaKey, ENT_QUOTES, 'UTF-8') ?>" autocomplete="off">
          </div>

          <button type="button" class="btn btn-primary" onclick="saveSetting()">保存设置</button>

        </div>
      </div>
    </div>
  </div>
</div>

<script>
function saveSetting() {
  var captchaId = $.trim($('#gt4_captcha_id').val());
  var captchaKey = $.trim($('#gt4_captcha_key').val());
  if (!captchaId || !captchaKey) { msalert(3, 'captchaId 和 captcha_key 不能为空', 4000); return; }
  msloading('保存中...');
  $.post('ajax.php', {
    gn: 'p_geetest4_setting_save',
    captcha_id: captchaId,
    captcha_key: captchaKey
  }, function (date) {
    msloadingde();
    var jsoe = typeof date === 'string' ? JSON.parse(date) : date;
    if (jsoe.qk === 1) { msalert(1, '保存成功', 4000); }
    else { msalert(3, jsoe.msg || '保存失败', 4000); }
  }).fail(function () { msloadingde(); msalert(3, '网络错误', 4000); });
}
</script>
</body>
</html>
