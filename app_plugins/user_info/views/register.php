<?php
if (!defined('IN_CRONLITE')) { exit; }
$page_title = $page_title ?? '注册';
ob_start();
?>
<div class="ly-msg" id="msg"></div>

<form class="layui-form ly-form" id="registerForm" autocomplete="on">
  <h1>注册</h1>
  <p style="color:#999;font-size:14px;margin:0 0 20px;">创建您的账户</p>

  <div class="layui-form-item">
    <label class="layui-form-label">用户名</label>
    <div class="layui-input-block"><input type="text" id="username" name="username" required maxlength="32" autocomplete="username" placeholder="3~32 位字母/数字/下划线" class="layui-input"></div>
  </div>
  <div class="layui-form-item">
    <label class="layui-form-label">密码</label>
    <div class="layui-input-block"><input type="password" id="password" name="password" required autocomplete="new-password" placeholder="至少 6 个字符" class="layui-input"></div>
  </div>
  <div class="layui-form-item">
    <label class="layui-form-label">确认密码</label>
    <div class="layui-input-block"><input type="password" id="password2" name="password2" required autocomplete="new-password" placeholder="再次输入密码" class="layui-input"></div>
  </div>
  <div class="layui-form-item">
    <label class="layui-form-label">邮箱</label>
    <div class="layui-input-block"><input type="email" id="email" name="email" maxlength="128" autocomplete="email" placeholder="选填，用于找回密码" class="layui-input"></div>
  </div>
  <div class="layui-form-item">
    <label class="layui-form-label">QQ</label>
    <div class="layui-input-block"><input type="text" id="qq" name="qq" maxlength="12" placeholder="选填" class="layui-input"></div>
  </div>
  <div class="layui-form-item">
    <div class="layui-input-block">
      <button type="submit" class="layui-btn layui-btn-fluid" id="submitBtn">注册</button>
    </div>
  </div>
  <div style="text-align:center;font-size:13px;color:#888;">
    已有账号？<a href="<?= user_info_url('account/login') ?>" style="color:#009688;">立即登录</a>
  </div>
</form>

<script>
(function () {
  var form = document.getElementById('registerForm');
  var msg = document.getElementById('msg');
  var btn = document.getElementById('submitBtn');
  function showMsg(text, type) { msg.textContent = text; msg.className = 'ly-msg show ' + (type === 'success' ? 'ly-msg-success' : 'ly-msg-error'); }
  form.addEventListener('submit', function (e) {
    e.preventDefault(); btn.disabled = true; btn.textContent = '注册中...'; msg.className = 'ly-msg';
    var body = new URLSearchParams();
    body.append('username', document.getElementById('username').value);
    body.append('password', document.getElementById('password').value);
    body.append('password2', document.getElementById('password2').value);
    body.append('email', document.getElementById('email').value);
    body.append('qq', document.getElementById('qq').value);
    fetch('<?= user_info_url('account/api/register') ?>', { method: 'POST', headers: {'Content-Type':'application/x-www-form-urlencoded'}, body: body.toString() })
      .then(function (r) { return r.json(); })
      .then(function (res) {
        if (res.redirect) { showMsg(res.code || '注册成功', 'success'); setTimeout(function(){window.location.href=res.redirect;},300); }
        else { showMsg(res.code || '注册失败', 'error'); btn.disabled = false; btn.textContent = '注册'; }
      })
      .catch(function () { showMsg('网络错误，请重试', 'error'); btn.disabled = false; btn.textContent = '注册'; });
  });
})();
</script>
<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
