<?php
if (!defined('IN_CRONLITE')) { exit; }
$page_title = $page_title ?? '注册';
ob_start();
?>
<div class="sf-auth">
  <div class="sf-card sf-auth-card">
    <h1 class="sf-auth-title">创建账户</h1>
    <p class="sf-auth-sub">注册后即可购买与管理您的云产品</p>

    <div class="sf-msg" id="msg"></div>

    <form class="sf-form" id="registerForm" autocomplete="on">
      <div class="sf-field">
        <label for="username">用户名</label>
        <input type="text" id="username" name="username" required maxlength="32" autocomplete="username" placeholder="3~32 位字母/数字/下划线">
      </div>
      <div class="sf-field">
        <label for="password">密码</label>
        <input type="password" id="password" name="password" required minlength="6" autocomplete="new-password" placeholder="至少 6 个字符">
      </div>
      <div class="sf-field">
        <label for="password2">确认密码</label>
        <input type="password" id="password2" name="password2" required minlength="6" autocomplete="new-password" placeholder="再次输入密码">
      </div>
      <div class="sf-field">
        <label for="email">邮箱（选填）</label>
        <input type="email" id="email" name="email" maxlength="128" autocomplete="email" placeholder="用于找回密码">
      </div>
      <div class="sf-field">
        <label for="qq">QQ（选填）</label>
        <input type="text" id="qq" name="qq" maxlength="12" placeholder="选填">
      </div>
      <button type="submit" class="sf-btn sf-btn-primary sf-btn-lg sf-btn-block" id="submitBtn">注 册</button>
    </form>

    <p class="sf-auth-foot">已有账号？<a href="<?= shop_frontend_url('account/login') ?>">立即登录</a></p>
  </div>
</div>

<script>
(function () {
  var form = document.getElementById('registerForm');
  if (!form) return;
  var btn = document.getElementById('submitBtn');
  form.addEventListener('submit', function (e) {
    e.preventDefault();
    var p1 = document.getElementById('password').value;
    var p2 = document.getElementById('password2').value;
    if (p1 !== p2) { sfMsg('msg', '两次输入的密码不一致', 'error'); return; }
    btn.disabled = true; btn.textContent = '注册中...'; sfMsg('msg', '', 'error');
    sfPost('<?= shop_frontend_url('account/api/register') ?>', {
      username: document.getElementById('username').value,
      password: p1,
      password2: p2,
      email: document.getElementById('email').value,
      qq: document.getElementById('qq').value
    }).then(function (res) {
      if (res.redirect) {
        sfMsg('msg', res.code || '注册成功', 'success');
        setTimeout(function () { window.location.href = res.redirect; }, 300);
      } else {
        sfMsg('msg', res.code || '注册失败', 'error');
        btn.disabled = false; btn.textContent = '注 册';
      }
    }).catch(function () {
      sfMsg('msg', '网络错误，请重试', 'error');
      btn.disabled = false; btn.textContent = '注 册';
    });
  });
})();
</script>
<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
