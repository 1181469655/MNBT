<?php mnbt_theme_include('head'); ?>
<style>
html, body { height: 100%; }
body.login-page {
  margin: 0;
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 24px 16px;
  background: linear-gradient(135deg, #1e9fff 0%, #16b777 100%);
  font-family: -apple-system, BlinkMacSystemFont, "Helvetica Neue", "PingFang SC", "Microsoft YaHei", sans-serif;
}
.ly-login-wrap { width: 100%; max-width: 410px; }
.ly-login-card {
  background: #fff;
  border-radius: 8px;
  padding: 40px 36px 28px;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.12);
}
.ly-login-brand { text-align: center; margin-bottom: 28px; }
.ly-login-brand img { max-height: 48px; max-width: 200px; object-fit: contain; }
.ly-login-brand .title {
  margin: 14px 0 0;
  font-size: 18px;
  font-weight: 600;
  color: #1e9fff;
  letter-spacing: -0.01em;
}
.ly-login-brand .sub { margin: 6px 0 0; font-size: 13px; color: #999; }
.ly-login-form .layui-input-wrap { margin-bottom: 18px; }
.ly-login-form .layui-input {
  height: 44px;
  border-radius: 4px;
  border: 1px solid #eee;
  background: #f7f8fa;
  padding-left: 40px;
  font-size: 14px;
  transition: border-color .15s, background .15s, box-shadow .15s;
}
.ly-login-form .layui-input:focus {
  border-color: #1e9fff;
  background: #fff;
  box-shadow: 0 0 0 3px rgba(30, 159, 255, 0.12);
}
.ly-login-form .layui-input-prefix {
  width: 40px;
  color: #999;
  font-size: 18px;
  text-align: center;
}
.ly-login-captcha { display: flex; gap: 10px; align-items: stretch; }
.ly-login-captcha .layui-input-wrap { flex: 1; min-width: 0; margin-bottom: 0; }
.ly-login-captcha .captcha-img {
  height: 44px;
  width: 110px;
  border-radius: 4px;
  border: 1px solid #eee;
  cursor: pointer;
  flex-shrink: 0;
}
.ly-login-btn {
  height: 44px;
  border-radius: 4px;
  font-size: 15px;
  font-weight: 500;
  width: 100%;
  margin-top: 6px;
  letter-spacing: 4px;
}
.ly-login-footer {
  margin: 20px 0 0;
  text-align: center;
  font-size: 12px;
  color: #bbb;
  line-height: 1.5;
}
@media (max-width: 420px) {
  .ly-login-card { padding: 32px 22px 24px; }
}
</style>
</head>
<body class="login-page">
<div class="ly-login-wrap">
  <div class="ly-login-card">
    <div class="ly-login-brand">
      <a href="login.php">
        <img alt="MNBT" src="<?=mnbt_asset_url('upload_logo/logo.login.png')?>?<?=$conf['auther']?>">
      </a>
      <p class="title"><?= htmlspecialchars($conf['name'] ?? '控制面板', ENT_QUOTES, 'UTF-8') ?></p>
      <p class="sub">用户登录</p>
    </div>
    <form action="#!" method="post" class="ly-login-form layui-form" onsubmit="return false;">
      <div class="layui-form-item">
        <div class="layui-input-wrap">
          <i class="layui-icon layui-icon-username layui-input-prefix"></i>
          <input type="text" class="layui-input" id="username" placeholder="用户名 / 账号" autocomplete="username">
        </div>
      </div>
      <div class="layui-form-item">
        <div class="layui-input-wrap">
          <i class="layui-icon layui-icon-password layui-input-prefix"></i>
          <input type="password" class="layui-input" id="password" placeholder="密码" autocomplete="current-password">
        </div>
      </div>
<?php if ($conf['yzme'] == 'true') { ?>
      <div class="layui-form-item">
        <div class="ly-login-captcha">
          <div class="layui-input-wrap">
            <i class="layui-icon layui-icon-vercode layui-input-prefix"></i>
            <input type="text" name="captcha" id="csyzmiq" class="layui-input" placeholder="验证码" autocomplete="off">
          </div>
          <img id="captcha" src="./code.php?r=<?=time()?>" class="captcha-img" onclick="this.src='./code.php?r='+Math.random();" title="点击更换验证码" alt="验证码">
        </div>
      </div>
<?php } ?>
      <div class="layui-form-item">
        <button class="layui-btn ly-login-btn" type="button" onclick="chkre()">登 录</button>
      </div>
    </form>
    <p class="ly-login-footer"><?= htmlspecialchars($conf['hxp'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
  </div>
</div>
<script type="text/javascript">
function chkre() {
  var userq = username.value;
  var passq = password.value;
  var codeq = '0000';
<?php if ($conf['yzme'] == 'true') {
  echo "  codeq = csyzmiq.value;\n";
} ?>
  if (userq == "" || passq == "" || codeq == "") {
    msalert(3, "请将表单填写完整", 2000);
  } else {
    msloading('正在登录，请稍后...');
    var data = {};
    data["gn"] = "login";
    data["user"] = userq;
    data["pass"] = passq;
    data["code"] = codeq;
    $.post('./ajax.php', data, function (date) {
      var jsoe = JSON.parse(date);
      var qk = jsoe.code;
      if (qk == '登陆成功') {
        msalert(1, "登录成功，正在跳转…", 2000);
        window.location.href = "./index.php";
        msloadingde();
<?php if ($conf['yzme'] == 'true') {
  echo "        captcha.src='./code.php?r='+Math.random();\n";
} ?>
      } else {
        msalert(4, qk, 4000);
        msloadingde();
<?php if ($conf['yzme'] == 'true') {
  echo "        captcha.src='./code.php?r='+Math.random();\n";
} ?>
      }
    });
  }
}
document.addEventListener('keydown', function (e) {
  if (e.key === 'Enter') chkre();
});
</script>
</body>
</html>
