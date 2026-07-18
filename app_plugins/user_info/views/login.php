<?php
if (!defined('IN_CRONLITE')) { exit; }
$page_title = $page_title ?? '登录';
ob_start();
?>
<div class="ly-msg" id="msg"></div>

<form class="layui-form ly-form" id="loginForm" autocomplete="on">
  <h1>登录</h1>
  <p style="color:#999;font-size:14px;margin:0 0 20px;">登录到您的账户</p>

  <div class="layui-form-item">
    <label class="layui-form-label">用户名</label>
    <div class="layui-input-block"><input type="text" id="username" name="username" required maxlength="32" autocomplete="username" placeholder="字母、数字或下划线" class="layui-input"></div>
  </div>
  <div class="layui-form-item">
    <label class="layui-form-label">密码</label>
    <div class="layui-input-block"><input type="password" id="password" name="password" required autocomplete="current-password" placeholder="至少 6 个字符" class="layui-input"></div>
  </div>
  <div class="layui-form-item">
    <div class="layui-input-block">
      <button type="submit" class="layui-btn layui-btn-fluid" id="submitBtn">登录</button>
    </div>
  </div>
  <div style="text-align:center;font-size:13px;color:#888;">
    还没有账号？<a href="<?= user_info_url('account/register') ?>" style="color:#009688;">立即注册</a>
  </div>
</form>

<script>
(function () {
  var form = document.getElementById('loginForm');
  var msg = document.getElementById('msg');
  var btn = document.getElementById('submitBtn');
  function showMsg(text, type) { msg.textContent = text; msg.className = 'ly-msg show ' + (type === 'success' ? 'ly-msg-success' : 'ly-msg-error'); }
  form.addEventListener('submit', function (e) {
    e.preventDefault(); btn.disabled = true; btn.textContent = '登录中...'; msg.className = 'ly-msg';
    var body = new URLSearchParams();
    body.append('username', document.getElementById('username').value);
    body.append('password', document.getElementById('password').value);
    fetch('<?= user_info_url('account/api/login') ?>', { method: 'POST', headers: {'Content-Type':'application/x-www-form-urlencoded'}, body: body.toString() })
      .then(function (r) { return r.json(); })
      .then(function (res) {
        if (res.redirect) { showMsg(res.code || '登录成功', 'success'); setTimeout(function(){window.location.href=res.redirect;},300); }
        else { showMsg(res.code || '登录失败', 'error'); btn.disabled = false; btn.textContent = '登录'; }
      })
      .catch(function () { showMsg('网络错误，请重试', 'error'); btn.disabled = false; btn.textContent = '登录'; });
  });
})();
</script>
<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
