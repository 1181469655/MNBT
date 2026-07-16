<html lang="zh">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
<meta name="keywords" content="MNBT控制面板,<?=$conf['name']?>-控制面板">
<meta name="description" content="MNBT控制面板,<?=$conf['name']?>-控制面板">
<meta name="author" content="MNBT">
<title><?=$conf['name']?>-控制面板</title>
<link rel="icon" href="<?=mnbt_asset_url('upload_logo/logo.head.png')?>?<?=$conf['auther']?>" type="image/ico">
<link rel="stylesheet" type="text/css" href="<?=mnbt_asset_url('css/bootstrap.min.css')?>">
<link rel="stylesheet" type="text/css" href="<?=mnbt_asset_url('css/materialdesignicons.min.css')?>">
<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/smoothness/jquery-ui.css">
<link rel="stylesheet" type="text/css" href="<?=mnbt_asset_url('js/bootstrap-multitabs/multitabs.min.css')?>">
<link rel="stylesheet" type="text/css" href="<?=mnbt_asset_url('css/animate.min.css')?>">
<link rel="stylesheet" type="text/css" href="<?=mnbt_asset_url('js/jquery-confirm/jquery-confirm.min.css')?>">
<link href="<?=mnbt_theme_asset('theme.css')?>" rel="stylesheet">
<script type="text/javascript" src="<?=mnbt_asset_url('js/fn-hs.js')?>"></script>
</head>
<body class="west-frame">
<div class="west-topbar">
  <div class="west-topbar-inner">
    <span>MNBT 主机面板</span>
    <span>当前账号：<?= htmlspecialchars($user ?? '', ENT_QUOTES, 'UTF-8') ?></span>
    <span>主机状态以面板实时检测为准</span>
    <span>遇到异常请先刷新用量与配置</span>
    <button type="button" onclick="chteci();">退出登录</button>
  </div>
</div>
<div class="west-mainnav">
  <a href="index.php" class="west-brand"><img alt="MNBT" src="<?=mnbt_asset_url('upload_logo/logo.login.png')?>?<?=$conf['auther']?>"><strong><?= htmlspecialchars($conf['name'] ?? 'MNBT', ENT_QUOTES, 'UTF-8') ?></strong></a>
  <div class="west-mainnav-links">
    <a href="index.php">首页</a>
    <a class="multitabs" href="sy.php">主机概览</a>
    <a class="multitabs" href="ftp.php">文件管理</a>
    <a class="multitabs" href="set.php?gn=url">域名管理</a>
    <a class="multitabs" href="set.php?gn=ssl">证书安全</a>
    <a class="multitabs" href="monitor.php">监控任务</a>
    <a class="multitabs" href="notice.php">通知日志</a>
    <a href="#!" onclick="chteci();">退出</a>
  </div>
</div>
<div class="west-shell">
  <aside class="west-sidebar">
    <div class="west-sidebar-title">主机控制面板</div>
    <div class="west-sidebar-scroll">
      <ul class="west-sidebar-menu">
<?php if($yhc['hxc']=='1'){ ?>
        <li><a href="sy.php" class="multitabs"><i class="mdi mdi-monitor-dashboard"></i>主机首页</a></li>
        <li><a class="multitabs" href="set.php?gn=CDN_url"><i class="func-icon func-icon-nav">设</i>域名修改</a></li>
<?php }else{ ?>
        <li><a href="sy.php" class="multitabs"><i class="mdi mdi-monitor-dashboard"></i>主机首页</a></li>
        <li><a class="multitabs" href="set.php?gn=php"><i class="func-icon func-icon-nav">P</i>PHP配置</a></li>
        <li><a class="multitabs" href="set.php?gn=url"><i class="mdi mdi-web"></i>域名修改</a></li>
        <li><a class="multitabs" href="set.php?gn=pass"><i class="mdi mdi-lock-outline"></i>密码访问</a></li>
        <li><a class="multitabs" href="set.php?gn=mrwd"><i class="mdi mdi-file-document-outline"></i>默认文档</a></li>
        <li><a class="multitabs" href="set.php?gn=yxml"><i class="mdi mdi-folder-outline"></i>运行目录</a></li>
        <li><a class="multitabs" href="set.php?gn=wjt"><i class="mdi mdi-file-code-outline"></i>伪静态</a></li>
        <li><a class="multitabs" href="set.php?gn=ssl"><i class="mdi mdi-shield-key-outline"></i>SSL配置</a></li>
        <li><a class="multitabs" href="set.php?gn=fdl"><i class="mdi mdi-link-variant-off"></i>防盗链</a></li>
        <li><a class="multitabs" href="set.php?gn=gzip"><i class="func-icon func-icon-nav">G</i>Gzip配置</a></li>
        <li><a class="multitabs" href="set.php?gn=cache"><i class="mdi mdi-cached"></i>缓存配置</a></li>
        <li><a class="multitabs" href="set.php?gn=xgpass"><i class="mdi mdi-account-key-outline"></i>修改密码</a></li>
        <li><a class="multitabs" href="ftp.php"><i class="mdi mdi-folder-multiple-outline"></i>在线文件管理</a></li>
        <li><a target="_blank" href="mysql.php"><i class="mdi mdi-database"></i>SQL管理面板</a></li>
        <li><a class="multitabs" href="webgl.php?gn=yjbs"><i class="mdi mdi-package-variant-closed"></i>一键部署</a></li>
        <li><a class="multitabs" href="monitor.php"><i class="mdi mdi-chart-line"></i>监控任务</a></li>
        <li><a class="multitabs" href="notice.php"><i class="mdi mdi-bell-outline"></i>通知日志</a></li>
<?php }?>
      </ul>
      <div class="west-usage-box"><div>空间配额使用情况：<span id="west-web-small">--</span></div><div class="west-mini-bar"><span id="west-web-small-bar"></span></div><div>空间总计：<span id="west-web-max">--</span></div><div>已经使用：<span id="west-web-used">--</span></div><a class="multitabs" href="set.php?gn=cache">升级空间</a></div>
    </div>
  </aside>
  <section class="west-content-wrap">
    <div class="west-breadcrumb">MNBT 用户首页 &gt; 用户管理中心 &gt; 虚拟主机管理首页 &gt; <?= htmlspecialchars($user ?? '', ENT_QUOTES, 'UTF-8') ?></div>
    <main class="west-layout-content"><div id="iframe-content"></div></main>
  </section>
</div>
<script type="text/javascript" src="<?=mnbt_asset_url('js/jquery.min.js')?>"></script>
<script type="text/javascript" src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
<script type="text/javascript" src="<?=mnbt_asset_url('js/popper.min.js')?>"></script>
<script type="text/javascript" src="<?=mnbt_asset_url('js/bootstrap.min.js')?>"></script>
<script type="text/javascript" src="<?=mnbt_asset_url('js/perfect-scrollbar.min.js')?>"></script>
<script type="text/javascript" src="<?=mnbt_asset_url('js/bootstrap-multitabs/multitabs.min.js')?>"></script>
<script type="text/javascript" src="<?=mnbt_asset_url('js/jquery.cookie.min.js')?>"></script>
<script type="text/javascript" src="<?=mnbt_asset_url('js/index.min.js')?>"></script>
<script type="text/javascript" src="<?=mnbt_asset_url('js/jquery-confirm/jquery-confirm.min.js')?>"></script>
<script type="text/javascript" src="<?=mnbt_asset_url('js/lyear-loading.js')?>"></script>
<script type="text/javascript" src="<?=mnbt_asset_url('js/main.min.js')?>"></script>
<script type="text/javascript" src="<?=mnbt_asset_url('js/fn-hs.js')?>"></script>
<script type="text/javascript" src="<?=mnbt_asset_url('js/bootstrap-notify.min.js')?>"></script>
<script type="text/javascript">
$(function() {
  $.post('./ajax.php', { gn: 'indexconf' }, function (date) {
    var jsoe = JSON.parse(date);
    if (jsoe.qk != 1 || !jsoe.msg || !jsoe.msg.web) return;
    var web = jsoe.msg.web;
    var percent = Math.round(((web.dq / web.max) * 10000) / 100);
    percent = Math.max(0, Math.min(100, percent || 0));
    $('#west-web-small').text(percent + '%');
    $('#west-web-small-bar').css('width', percent + '%');
    $('#west-web-max').text(web.max + 'M');
    $('#west-web-used').text(Number(web.dq).toFixed(2) + 'M');
  });
});
function chteci() {
    msloading('正在退出登录中...','text-info','text-info');
    let data = {};
    data["gn"]="login";
    data["logout"]="tclogin";
    $.post('./ajax.php', data, function (date) {
        var jsoe= JSON.parse(date);
        var qk= jsoe.code;
        msalert(1,qk,2000);
        window.location.href="./login.php";
        msloadingde();
    })
}
</script>
</body>
</html>
