<?php
if (!defined('IN_CRONLITE')) { exit; }
$page_title = $page_title ?? '修改密码';
ob_start();
?>
<div class="sf-page-head">
  <h1>修改密码</h1>
  <p>修改后需使用新密码登录</p>
</div>

<div class="sf-card" style="max-width:560px;">
  <div class="sf-card-body">
    <div class="sf-msg" id="msg"></div>

    <form class="sf-form" id="passwordForm" autocomplete="on">
      <div class="sf-field">
        <label for="old_password">原密码</label>
        <input type="password" id="old_password" name="old_password" required autocomplete="current-password" placeholder="输入当前密码">
      </div>
      <div class="sf-field">
        <label for="new_password">新密码</label>
        <input type="password" id="new_password" name="new_password" required minlength="6" autocomplete="new-password" placeholder="至少 6 个字符">
      </div>
      <div class="sf-field">
        <label for="new_password2">确认新密码</label>
        <input type="password" id="new_password2" name="new_password2" required minlength="6" autocomplete="new-password" placeholder="再次输入新密码">
      </div>
      <div class="sf-form-actions">
        <button type="submit" class="sf-btn sf-btn-primary" id="submitBtn">确认修改</button>
        <a href="<?= shop_frontend_url('account/profile') ?>" class="sf-btn sf-btn-ghost">返回</a>
      </div>
    </form>
  </div>
</div>

<script>
(function () {
  var form = document.getElementById('passwordForm');
  if (!form) return;
  var btn = document.getElementById('submitBtn');
  form.addEventListener('submit', function (e) {
    e.preventDefault();
    var n1 = document.getElementById('new_password').value;
    var n2 = document.getElementById('new_password2').value;
    if (n1 !== n2) { sfMsg('msg', '两次输入的新密码不一致', 'error'); return; }
    btn.disabled = true; btn.textContent = '修改中...'; sfMsg('msg', '', 'error');
    sfPost('<?= shop_frontend_url('account/api/change_password') ?>', {
      old_password: document.getElementById('old_password').value,
      new_password: n1,
      new_password2: n2
    }).then(function (res) {
      sfMsg('msg', res.code || '操作失败', res.code === '修改成功' ? 'success' : 'error');
      if (res.code === '修改成功') { form.reset(); btn.disabled = false; btn.textContent = '确认修改'; }
      else { btn.disabled = false; btn.textContent = '确认修改'; }
    }).catch(function () {
      sfMsg('msg', '网络错误，请重试', 'error');
      btn.disabled = false; btn.textContent = '确认修改';
    });
  });
})();
</script>
<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
