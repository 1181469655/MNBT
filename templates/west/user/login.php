<?php
@header('Content-Type: text/html; charset=UTF-8');
if (defined('ROOT')) {
	include ROOT . 'cf_up.php';
} else {
	include dirname(__DIR__, 3) . '/cf_up.php';
}
if (!empty($mn_conf['xf']['qk'])) {
	exit('由于更新后必须进行一次系统修复，暂时无法使用本系统！请联系管理员前往后台使用修复功能！');
}
?>
<!DOCTYPE html>
<html lang="zh">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" />
<title><?=$title ?? '用户登录'?></title>
<link rel="icon" href="<?=mnbt_asset_url('upload_logo/logo.head.png')?>?<?=$conf['auther'] ?? ''?>" type="image/ico">
<link href="<?=mnbt_asset_url('css/bootstrap.min.css')?>" rel="stylesheet">
<link href="<?=mnbt_asset_url('css/materialdesignicons.min.css')?>" rel="stylesheet">
<script type="text/javascript" src="<?=mnbt_asset_url('js/jquery.min.js')?>"></script>
<script type="text/javascript" src="<?=mnbt_asset_url('js/bootstrap.min.js')?>"></script>
<script type="text/javascript" src="<?=mnbt_asset_url('js/lyear-loading.js')?>"></script>
<script src="<?=mnbt_asset_url('js/bootstrap-notify.min.js')?>"></script>
<script type="text/javascript" src="<?=mnbt_asset_url('js/main.min.js')?>"></script>
<script type="text/javascript" src="<?=mnbt_asset_url('js/fn-hs.js')?>?1.74"></script>
<link href="<?=mnbt_asset_url('js/jquery-confirm/jquery-confirm.min.css')?>" rel="stylesheet">
<link href="<?=mnbt_theme_asset('theme.css')?>" rel="stylesheet">
</head>
<body class="west-login-page">
<div class="west-login-shell">
  <div class="west-login-top">
    <a href="login.php"><img alt="MNBT" src="<?=mnbt_asset_url('upload_logo/logo.login.png')?>?<?=$conf['auther']?>"></a>
    <span><?= htmlspecialchars($conf['name'] ?? '控制面板', ENT_QUOTES, 'UTF-8') ?></span>
  </div>
  <div class="west-login-card">
    <div class="west-login-title">用户控制面板登录</div>
    <form action="#!" method="post" class="west-login-form" onsubmit="return false;">
      <label>主机账号</label>
      <input type="text" class="west-input" id="username" placeholder="请输入账号" autocomplete="username">
      <label>登录密码</label>
      <input type="password" class="west-input" id="password" placeholder="请输入密码" autocomplete="current-password">
<?php if ($conf['yzme'] == 'true') { ?>
      <label>验证码</label>
      <div class="west-captcha-row">
        <input type="text" name="captcha" id="csyzmiq" class="west-input" placeholder="验证码" autocomplete="off">
        <img id="captcha" src="./code.php?r=<?php echo time(); ?>" class="west-captcha-img" onclick="this.src='./code.php?r='+Math.random();" title="点击更换验证码" alt="验证码">
      </div>
<?php } ?>
      <button class="west-login-button" type="button" onclick="chkre()">登录控制面板</button>
    </form>
    <p class="west-login-footer"><?= htmlspecialchars($conf['hxp'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
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
