<?php mnbt_theme_include('head'); ?>
<style>html, body { height: 100%; }</style>
</head>
<body class="login-page">
<div class="login-wrap">
  <div class="login-card">
    <div class="login-brand">
      <a href="login.php">
        <img alt="MNBT" src="<?=mnbt_asset_url('upload_logo/logo.login.png')?>?<?=$conf['auther']?>">
      </a>
      <p class="title"><?= htmlspecialchars($conf['name'] ?? '控制面板', ENT_QUOTES, 'UTF-8') ?></p>
      <p class="sub">用户登录</p>
    </div>
    <form action="#!" method="post" class="login-form" onsubmit="return false;">
      <div class="form-group position-relative">
        <i class="mdi mdi-account input-icon"></i>
        <input type="text" class="form-control pl-5" id="username" placeholder="用户名 / 账号" autocomplete="username">
      </div>
      <div class="form-group position-relative">
        <i class="mdi mdi-lock input-icon"></i>
        <input type="password" class="form-control pl-5" id="password" placeholder="密码" autocomplete="current-password">
      </div>
<?php if ($conf['yzme'] == 'true') { ?>
      <div class="form-group">
        <div class="captcha-row">
          <div class="captcha-input position-relative">
            <i class="mdi mdi-check-all input-icon"></i>
            <input type="text" name="captcha" id="csyzmiq" class="form-control pl-5" placeholder="验证码" autocomplete="off">
          </div>
          <img id="captcha" src="./code.php?r=<?=time()?>" class="captcha-img" onclick="this.src='./code.php?r='+Math.random();" title="点击更换验证码" alt="验证码">
        </div>
      </div>
<?php } ?>
      <div class="form-group mb-0">
        <button class="btn btn-login" type="button" onclick="chkre()">登 录</button>
      </div>
    </form>
    <p class="login-footer"><?= htmlspecialchars($conf['hxp'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
  </div>
</div>
<script type="text/javascript">
function chkre() {
  var userq = username.value;
  var passq = password.value;
  var codeq = '0000';
<?php if ($conf['yzme'] == 'true') { echo "  codeq = csyzmiq.value;\n"; } ?>
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
<?php if ($conf['yzme'] == 'true') { echo "        captcha.src='./code.php?r='+Math.random();\n"; } ?>
      } else {
        msalert(4, qk, 4000);
        msloadingde();
<?php if ($conf['yzme'] == 'true') { echo "        captcha.src='./code.php?r='+Math.random();\n"; } ?>
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
