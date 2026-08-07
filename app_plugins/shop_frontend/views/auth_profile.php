<?php
if (!defined('IN_CRONLITE')) { exit; }
$page_title = $page_title ?? '个人信息';
$u = $current_user;
ob_start();
?>
<div class="sf-page-head">
  <h1>个人信息</h1>
  <p>查看和更新您的账户信息</p>
</div>

<div class="sf-card" style="max-width:560px;">
  <div class="sf-card-body">
    <div class="sf-msg" id="msg"></div>

    <div style="display:flex;flex-direction:column;gap:10px;margin:6px 0 20px;padding:16px;background:#fafbfe;border:1px solid var(--sf-border);border-radius:10px;">
      <div style="display:flex;justify-content:space-between;"><span style="color:var(--sf-text-2);">用户名</span><b><?= htmlspecialchars($u['username']) ?></b></div>
      <div style="display:flex;justify-content:space-between;"><span style="color:var(--sf-text-2);">注册时间</span><span><?= htmlspecialchars($u['created_at']) ?></span></div>
    </div>

    <form class="sf-form" id="profileForm">
      <div class="sf-field">
        <label for="email">邮箱</label>
        <input type="email" id="email" name="email" maxlength="128" value="<?= htmlspecialchars($u['email']) ?>" placeholder="选填，用于找回密码">
      </div>
      <div class="sf-field">
        <label for="qq">QQ</label>
        <input type="text" id="qq" name="qq" maxlength="12" value="<?= htmlspecialchars($u['qq']) ?>" placeholder="选填">
      </div>
      <div class="sf-form-actions">
        <button type="submit" class="sf-btn sf-btn-primary" id="submitBtn">保存</button>
        <a href="<?= shop_frontend_url('account/password') ?>" class="sf-btn sf-btn-ghost">修改密码</a>
      </div>
    </form>
  </div>
</div>

<script>
(function () {
  var form = document.getElementById('profileForm');
  if (!form) return;
  var btn = document.getElementById('submitBtn');
  form.addEventListener('submit', function (e) {
    e.preventDefault();
    btn.disabled = true; btn.textContent = '保存中...'; sfMsg('msg', '', 'error');
    sfPost('<?= shop_frontend_url('account/api/update_profile') ?>', {
      email: document.getElementById('email').value,
      qq: document.getElementById('qq').value
    }).then(function (res) {
      sfMsg('msg', res.code || '操作失败', res.code === '保存成功' ? 'success' : 'error');
      btn.disabled = false; btn.textContent = '保存';
    }).catch(function () {
      sfMsg('msg', '网络错误，请重试', 'error');
      btn.disabled = false; btn.textContent = '保存';
    });
  });
})();
</script>
<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
