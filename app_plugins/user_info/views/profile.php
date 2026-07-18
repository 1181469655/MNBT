<?php
if (!defined('IN_CRONLITE')) { exit; }
$page_title = $page_title ?? '个人信息';
$u = $current_user;
ob_start();
?>
<div class="ly-msg" id="msg"></div>

<form class="layui-form ly-form" id="profileForm">
  <h1>个人信息</h1>
  <p style="color:#999;font-size:14px;margin:0 0 20px;">查看和更新您的账户信息</p>

  <div class="ly-info-row"><span class="ly-info-label">用户名</span><span class="ly-info-value"><?= htmlspecialchars($u['username']) ?></span></div>
  <div class="ly-info-row"><span class="ly-info-label">注册时间</span><span class="ly-info-value"><?= htmlspecialchars($u['created_at']) ?></span></div>

  <div class="layui-form-item" style="margin-top:20px;">
    <label class="layui-form-label">邮箱</label>
    <div class="layui-input-block"><input type="email" id="email" name="email" maxlength="128" value="<?= htmlspecialchars($u['email']) ?>" placeholder="选填，用于找回密码" class="layui-input"></div>
  </div>
  <div class="layui-form-item">
    <label class="layui-form-label">QQ</label>
    <div class="layui-input-block"><input type="text" id="qq" name="qq" maxlength="12" value="<?= htmlspecialchars($u['qq']) ?>" placeholder="选填" class="layui-input"></div>
  </div>
  <div class="layui-form-item">
    <div class="layui-input-block">
      <div class="ly-form-actions">
        <button type="submit" class="layui-btn" id="submitBtn">保存</button>
        <button type="button" class="layui-btn layui-btn-primary" onclick="window.location.href='<?= user_info_url('account/password') ?>'">修改密码</button>
      </div>
    </div>
  </div>
</form>

<script>
(function () {
  var form = document.getElementById('profileForm');
  var msg = document.getElementById('msg');
  var btn = document.getElementById('submitBtn');
  function showMsg(text, type) { msg.textContent = text; msg.className = 'ly-msg show ' + (type === 'success' ? 'ly-msg-success' : 'ly-msg-error'); }
  form.addEventListener('submit', function (e) {
    e.preventDefault(); btn.disabled = true; btn.textContent = '保存中...'; msg.className = 'ly-msg';
    var body = new URLSearchParams();
    body.append('email', document.getElementById('email').value);
    body.append('qq', document.getElementById('qq').value);
    fetch('<?= user_info_url('account/api/update_profile') ?>', { method: 'POST', headers: {'Content-Type':'application/x-www-form-urlencoded'}, body: body.toString() })
      .then(function (r) { return r.json(); })
      .then(function (res) {
        showMsg(res.code || '操作失败', res.code === '保存成功' ? 'success' : 'error');
        btn.disabled = false; btn.textContent = '保存';
      })
      .catch(function () { showMsg('网络错误，请重试', 'error'); btn.disabled = false; btn.textContent = '保存'; });
  });
})();
</script>
<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
