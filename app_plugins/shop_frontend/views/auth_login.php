<?php
if (!defined('IN_CRONLITE')) { exit; }
$page_title = $page_title ?? '登录';
ob_start();
?>
<div class="sf-auth">
  <div class="sf-card sf-auth-card">
    <h1 class="sf-auth-title">欢迎回来</h1>
    <p class="sf-auth-sub">登录到您的账户继续</p>

    <div class="sf-msg" id="msg"></div>

    <form class="sf-form" id="loginForm" autocomplete="on">
      <div class="sf-field">
        <label for="username">用户名</label>
        <input type="text" id="username" name="username" required maxlength="32" autocomplete="username" placeholder="请输入用户名">
      </div>
      <div class="sf-field">
        <label for="password">密码</label>
        <input type="password" id="password" name="password" required autocomplete="current-password" placeholder="请输入密码">
      </div>
      <button type="submit" class="sf-btn sf-btn-primary sf-btn-lg sf-btn-block" id="submitBtn">登 录</button>
    </form>

    <p class="sf-auth-foot">还没有账号？<a href="<?= shop_frontend_url('account/register') ?>">立即注册</a></p>
  </div>
</div>

<script>
(function () {
  var form = document.getElementById('loginForm');
  if (!form) return;
  var btn = document.getElementById('submitBtn');
  form.addEventListener('submit', function (e) {
    e.preventDefault();
    btn.disabled = true; btn.textContent = '登录中...'; sfMsg('msg', '', 'error');
    sfPost('<?= shop_frontend_url('account/api/login') ?>', {
      username: document.getElementById('username').value,
      password: document.getElementById('password').value
    }).then(function (res) {
      if (res.redirect) {
        sfMsg('msg', res.code || '登录成功', 'success');
        setTimeout(function () { window.location.href = res.redirect; }, 300);
      } else {
        sfMsg('msg', res.code || '登录失败', 'error');
        btn.disabled = false; btn.textContent = '登 录';
      }
    }).catch(function () {
      sfMsg('msg', '网络错误，请重试', 'error');
      btn.disabled = false; btn.textContent = '登 录';
    });
  });
})();
</script>
<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
