<?php mnbt_admin_include('head'); ?>
<style>
html, body {
  height: 100%;
}
body.login-page {
  margin: 0;
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 24px 16px;
  background: #f4f6f8;
  font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", "PingFang SC", "Microsoft YaHei", sans-serif;
}
.login-wrap {
  width: 100%;
  max-width: 400px;
}
.login-card {
  background: #fff;
  border-radius: 16px;
  padding: 40px 32px 28px;
  box-shadow: 0 4px 24px rgba(15, 23, 42, 0.06);
  border: 1px solid #eef1f4;
}
.login-brand {
  text-align: center;
  margin-bottom: 28px;
}
.login-brand img {
  max-height: 48px;
  max-width: 200px;
  object-fit: contain;
}
.login-brand .title {
  margin: 14px 0 0;
  font-size: 18px;
  font-weight: 600;
  color: #1e293b;
  letter-spacing: -0.01em;
}
.login-brand .sub {
  margin: 6px 0 0;
  font-size: 13px;
  color: #94a3b8;
}
.login-form .form-group {
  margin-bottom: 16px;
}
.login-form .form-control {
  height: 44px;
  border-radius: 10px;
  border: 1px solid #e2e8f0;
  background: #f8fafc;
  color: #1e293b;
  padding: 0 14px 0 40px;
  font-size: 14px;
  box-shadow: none;
  transition: border-color .15s ease, background .15s ease, box-shadow .15s ease;
}
.login-form .form-control:focus {
  background: #fff;
  border-color: #33cabb;
  box-shadow: 0 0 0 3px rgba(51, 202, 187, 0.15);
  outline: none;
}
.login-form .form-control::placeholder {
  color: #94a3b8;
}
.login-form .has-feedback {
  position: relative;
}
.login-form .has-feedback .mdi {
  position: absolute;
  left: 0;
  top: 0;
  width: 40px;
  height: 44px;
  line-height: 44px;
  text-align: center;
  color: #94a3b8;
  z-index: 2;
  pointer-events: none;
}
.login-form .has-feedback.row .mdi {
  left: 15px;
}
.login-form .captcha-row {
  display: flex;
  gap: 10px;
  align-items: stretch;
}
.login-form .captcha-row .captcha-input {
  flex: 1;
  min-width: 0;
}
.login-form .captcha-img {
  height: 44px;
  border-radius: 10px;
  border: 1px solid #e2e8f0;
  cursor: pointer;
  flex-shrink: 0;
}
.login-form .btn-login {
  height: 44px;
  border-radius: 10px;
  border: none;
  background: #33cabb;
  color: #fff;
  font-size: 15px;
  font-weight: 600;
  width: 100%;
  margin-top: 4px;
  transition: background .15s ease, transform .1s ease;
}
.login-form .btn-login:hover,
.login-form .btn-login:focus {
  background: #2bb3a5;
  color: #fff;
}
.login-form .btn-login:active {
  transform: scale(0.99);
}
@media (max-width: 420px) {
  .login-card {
    padding: 32px 20px 24px;
  }
}
</style>
</head>
<body class="login-page">
<div class="login-wrap">
  <div class="login-card">
    <div class="login-brand">
      <a href="./">
        <img alt="MNBT admin" src="<?=mnbt_asset_url('admin_logo/logo.login.png')?>?1">
      </a>
      <p class="title">管理后台</p>
      <p class="sub">管理员登录</p>
    </div>
    <form class="login-form" onsubmit="return false;">
      <div class="form-group has-feedback">
        <span class="mdi mdi-account" aria-hidden="true"></span>
        <input type="text" class="form-control" id="username" placeholder="用户名" autocomplete="username">
      </div>
      <div class="form-group has-feedback">
        <span class="mdi mdi-lock" aria-hidden="true"></span>
        <input type="password" class="form-control" id="password" placeholder="密码" autocomplete="current-password">
      </div>
<?php if ($conf['yzm'] == 'true') { ?>
      <div class="form-group">
        <div class="captcha-row">
          <div class="captcha-input has-feedback">
            <span class="mdi mdi-check-all" aria-hidden="true"></span>
            <input type="text" name="captcha" id="csyzmiq" class="form-control" placeholder="验证码" autocomplete="off">
          </div>
          <img src="./code.php?r=<?php echo time(); ?>" class="captcha-img" id="captcha" style="cursor:pointer;" onclick="this.src='./code.php?r='+Math.random();" title="点击刷新" alt="验证码">
        </div>
      </div>
<?php } ?>
      <div class="form-group mb-0">
        <button class="btn btn-login" type="button" id="example-three" onclick="chkre()">登 录</button>
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
