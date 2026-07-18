<?php mnbt_theme_include('head'); ?>
<style>
html, body {
  height: 100%;
}
</style>
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
      <div class="field-group">
        <input type="text" class="ui-input" id="username" placeholder="用户名 / 账号" autocomplete="username">
      </div>
      <div class="field-group">
        <input type="password" class="ui-input" id="password" placeholder="密码" autocomplete="current-password">
      </div>
<?php if ($conf['yzme'] == 'true') { ?>
      <div class="field-group">
        <div class="captcha-row">
          <div class="captcha-input">
            <input type="text" name="captcha" id="csyzmiq" class="ui-input" placeholder="验证码" autocomplete="off">
          </div>
          <img id="captcha" src="./code.php?r=<?php echo time(); ?>" class="captcha-img" onclick="this.src='./code.php?r='+Math.random();" title="点击更换验证码" alt="验证码">
        </div>
      </div>
<?php } ?>
      <div class="field-group">
        <button class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only" type="button" onclick="chkre()">
          <span class="ui-button-text">登 录</span>
        </button>
      </div>
    </form>
    <p class="login-footer"><?= htmlspecialchars($conf['hxp'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
  </div>
</div>
<script type="text/javascript">
$(function() {
  $('.ui-button').button();
});
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
