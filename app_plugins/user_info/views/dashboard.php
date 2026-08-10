<?php
if (!defined('IN_CRONLITE')) { exit; }
$page_title = $page_title ?? '控制面板';
$u = $current_user;
ob_start();
?>
<div class="ly-msg" id="msg"></div>

<div style="padding:6px 0 20px;">
  <h1 style="font-size:22px;margin:0 0 6px;">你好,<?= htmlspecialchars($u['username']) ?>!</h1>
  <p style="color:#999;font-size:14px;margin:0;">欢迎回到用户中心,在这里管理你的个人信息与账号安全。</p>
</div>

<div class="ly-info-row"><span class="ly-info-label">用户名</span><span class="ly-info-value"><?= htmlspecialchars($u['username']) ?></span></div>
<div class="ly-info-row"><span class="ly-info-label">邮箱</span><span class="ly-info-value"><?= htmlspecialchars($u['email']) ?: '未绑定' ?></span></div>
<div class="ly-info-row"><span class="ly-info-label">QQ</span><span class="ly-info-value"><?= htmlspecialchars($u['qq']) ?: '未绑定' ?></span></div>
<div class="ly-info-row"><span class="ly-info-label">注册时间</span><span class="ly-info-value"><?= htmlspecialchars($u['created_at']) ?></span></div>

<div style="margin-top:24px;display:flex;gap:10px;flex-wrap:wrap;">
  <a href="<?= user_info_url('account/profile') ?>" class="layui-btn">个人信息</a>
  <a href="<?= user_info_url('account/password') ?>" class="layui-btn layui-btn-primary">修改密码</a>
  <a href="<?= user_info_url('account/logout') ?>" class="layui-btn layui-btn-danger">退出登录</a>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
