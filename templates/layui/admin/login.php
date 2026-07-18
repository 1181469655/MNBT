<?php mnbt_admin_include('head'); ?>
<style>
html, body { height: 100%; }
body.ly-login-page {
  margin: 0; min-height: 100vh;
  display: flex; align-items: center; justify-content: center;
  padding: 24px 16px; background: #f4f6f8;
  font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", "PingFang SC", "Microsoft YaHei", sans-serif;
}
.ly-login-wrap { width: 100%; max-width: 400px; }
.ly-login-card {
  background: #fff; border-radius: 8px; padding: 40px 32px 28px;
  box-shadow: 0 2px 12px rgba(15, 23, 42, 0.06); border: 1px solid #eef1f4;
}
.ly-login-brand { text-align: center; margin-bottom: 28px; }
.ly-login-brand img { max-height: 48px; max-width: 200px; object-fit: contain; }
.ly-login-brand .title {
  margin: 14px 0 0; font-size: 18px; font-weight: 600;
  color: #1e293b; letter-spacing: -0.01em;
}
.ly-login-brand .sub { margin: 6px 0 0; font-size: 13px; color: #94a3b8; }
.ly-login-form .ly-field { margin-bottom: 16px; }
.ly-login-form .ly-input-wrap { position: relative; display: flex; align-items: center; }
.ly-login-form .ly-input-wrap .ly-input-icon {
  position: absolute; left: 12px; color: #94a3b8; font-size: 16px;
  pointer-events: none; z-index: 2;
}
.ly-login-form .ly-input {
  height: 44px; border-radius: 4px; border: 1px solid #e2e8f0;
  background: #f8fafc; color: #1e293b; padding: 0 14px 0 38px;
  font-size: 14px; width: 100%; box-sizing: border-box;
  transition: border-color .15s ease, background .15s ease, box-shadow .15s ease;
}
.ly-login-form .ly-input:focus {
  background: #fff; border-color: #1e9fff; outline: none;
  box-shadow: 0 0 0 3px rgba(30, 159, 255, 0.12);
}
.ly-login-form .ly-input::placeholder { color: #94a3b8; }
.ly-login-form .ly-captcha-row { display: flex; gap: 10px; align-items: stretch; }
.ly-login-form .ly-captcha-row .ly-captcha-input { flex: 1; min-width: 0; }
.ly-login-form .ly-captcha-img {
  height: 44px; border-radius: 4px; border: 1px solid #e2e8f0;
  cursor: pointer; flex-shrink: 0;
}
.ly-login-form .ly-btn-login {
  height: 44px; border-radius: 4px; border: none;
  background: #1e9fff; color: #fff; font-size: 15px; font-weight: 600;
  width: 100%; margin-top: 4px; cursor: pointer;
  transition: background .15s ease, transform .1s ease;
}
.ly-login-form .ly-btn-login:hover,
.ly-login-form .ly-btn-login:focus { background: #1890e8; color: #fff; }
.ly-login-form .ly-btn-login:active { transform: scale(0.99); }
@media (max-width: 420px) { .ly-login-card { padding: 32px 20px 24px; } }
</style>
</head>
<body class="ly-login-page">
<div class="ly-login-wrap">
  <div class="ly-login-card">
    <div class="ly-login-brand">
      <a href="./">
        <img alt="MNBT admin" src="<?=mnbt_asset_url('admin_logo/logo.login.png')?>?1">
      </a>
      <p class="title">管理后台</p>
      <p class="sub">管理员登录</p>
    </div>
    <form class="ly-login-form" onsubmit="return false;">
      <div class="ly-field">
        <div class="ly-input-wrap">
          <i class="mdi mdi-account ly-input-icon" aria-hidden="true"></i>
          <input type="text" class="ly-input" id="username" placeholder="用户名" autocomplete="username">
        </div>
      </div>
      <div class="ly-field">
        <div class="ly-input-wrap">
          <i class="mdi mdi-lock ly-input-icon" aria-hidden="true"></i>
          <input type="password" class="ly-input" id="password" placeholder="密码" autocomplete="current-password">
        </div>
      </div>
<?php if ($conf['yzm'] == 'true') { ?>
      <div class="ly-field">
        <div class="ly-captcha-row">
          <div class="ly-captcha-input ly-input-wrap">
            <i class="mdi mdi-check-all ly-input-icon" aria-hidden="true"></i>
            <input type="text" name="captcha" id="csyzmiq" class="ly-input" placeholder="验证码" autocomplete="off">
          </div>
          <img src="./code.php?r=<?php echo time(); ?>" class="ly-captcha-img" id="captcha" onclick="this.src='./code.php?r='+Math.random();" title="点击刷新" alt="验证码">
        </div>
      </div>
<?php } ?>
      <div class="ly-field" style="margin-bottom:0">
        <button class="ly-btn-login" type="button" id="example-three" onclick="chkre()">登 录</button>
      </div>
    </form>
  </div>
</div>
<script type="text/javascript">
function chkre() {
  var userq = username.value;
  var passq = password.value;
<?php if ($conf['yzm'] == 'true') { ?>
  var codeq = csyzmiq.value;
  if (userq == "" || passq == "" || codeq == "") {
    msalert(4, '请将表单填写完整！', 2000);
    return;
  }
<?php } else { ?>
  if (userq == "" || passq == "") {
    msalert(4, '请将表单填写完整！', 2000);
    return;
  }
  var codeq = '0000';
<?php } ?>
  msloading('正在登录中，请稍后...', 'text-info', 'text-info');
  var data = {};
  data["gn"] = "login";
  data["user"] = userq;
  data["pass"] = passq;
<?php if ($conf['yzm'] == 'true') { ?>
  data["code"] = codeq;
<?php } ?>
  $.post('./ajax.php', data, function (date) {
    var jsoe = JSON.parse(date);
    var qk = jsoe.code;
    if (qk == '登陆成功') {
      msalert(1, '登录成功，正在跳转…', 2000);
      window.location.href = "./index.php";
      msloadingde();
<?php if ($conf['yzm'] == 'true') { ?>
      captcha.src = './code.php?r=' + Math.random();
<?php } ?>
    } else {
      msalert(4, qk, 2000);
      msloadingde();
<?php if ($conf['yzm'] == 'true') { ?>
      captcha.src = './code.php?r=' + Math.random();
<?php } ?>
    }
  });
}
document.addEventListener('keydown', function (e) {
  if (e.key === 'Enter') chkre();
});
</script>
</body>
</html>
