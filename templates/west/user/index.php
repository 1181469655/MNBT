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
    <span><i class="mdi mdi-cart"></i> 购物车 0</span>
    <span>最新公告</span>
    <span>云虚拟主机</span>
    <span>帮助中心</span>
    <span>提交工单</span>
    <span>备案</span>
    <span>身份识别码：<?= htmlspecialchars($user ?? '', ENT_QUOTES, 'UTF-8') ?></span>
    <button type="button" onclick="chteci();"><i class="mdi mdi-account"></i> 管理中心</button>
  </div>
</div>
<div class="west-mainnav">
  <a href="index.php" class="west-brand"><img alt="MNBT" src="<?=mnbt_asset_url('upload_logo/logo.login.png')?>?<?=$conf['auther']?>"><strong><?= htmlspecialchars($conf['name'] ?? 'MNBT', ENT_QUOTES, 'UTF-8') ?></strong></a>
  <div class="west-mainnav-links">
    <a href="index.php">首页</a>
    <a class="multitabs" href="set.php?gn=url">域名绑定</a>
    <a class="multitabs" href="set.php?gn=ssl">安全</a>
    <a class="multitabs" href="webgl.php?gn=yjbs">云建站</a>
    <a class="multitabs" href="notice.php">消息</a>
    <a href="#!" onclick="chteci();">退出</a>
  </div>
</div>
<div class="west-shell">
  <aside class="west-sidebar">
    <div class="west-sidebar-title">主机控制面板</div>
    <div class="west-sidebar-scroll">
      <div id="jqui-sidebar-accordion">
<?php if($yhc['hxc']=='1'){ ?>
        <h3><i class="mdi mdi-monitor-dashboard"></i> 主机管理</h3>
        <div><ul class="jqui-subnav"><li><a href="sy.php" class="multitabs">主机首页</a></li></ul></div>
        <h3><i class="mdi mdi-earth"></i> 网站设置</h3>
        <div><ul class="jqui-subnav"><li><a class="multitabs" href="set.php?gn=CDN_url">域名修改</a></li></ul></div>
<?php }else{ ?>
        <h3><i class="mdi mdi-monitor-dashboard"></i> 主机管理</h3>
        <div><ul class="jqui-subnav"><li><a href="sy.php" class="multitabs">主机首页</a></li></ul></div>
        <h3><i class="mdi mdi-tune"></i> 网站设置</h3>
        <div><ul class="jqui-subnav"><li><a class="multitabs" href="set.php?gn=php">PHP版本切换</a></li><li><a class="multitabs" href="set.php?gn=url">域名修改</a></li><li><a class="multitabs" href="set.php?gn=pass">密码访问</a></li><li><a class="multitabs" href="set.php?gn=mrwd">默认文档</a></li><li><a class="multitabs" href="set.php?gn=yxml">运行目录</a></li><li><a class="multitabs" href="set.php?gn=wjt">伪静态</a></li><li><a class="multitabs" href="set.php?gn=ssl">SSL配置</a></li><li><a class="multitabs" href="set.php?gn=fdl">防盗链</a></li><li><a class="multitabs" href="set.php?gn=gzip">Gzip配置</a></li><li><a class="multitabs" href="set.php?gn=cache">缓存配置</a></li><li><a class="multitabs" href="set.php?gn=xgpass">修改密码</a></li></ul></div>
        <h3><i class="mdi mdi-database"></i> 数据管理</h3>
        <div><ul class="jqui-subnav"><li><a class="multitabs" href="ftp.php">在线文件管理</a></li><li><a target="_blank" href="mysql.php">SQL管理面板</a></li><li><a class="multitabs" href="sqlgl.php">SQL数据备份</a></li><li><a class="multitabs" href="set.php?gn=mysqlcz">SQL权限设置</a></li></ul></div>
        <h3><i class="mdi mdi-web"></i> 网站服务</h3>
        <div><ul class="jqui-subnav"><li><a class="multitabs" href="webgl.php?gn=yjbs">一键部署</a></li><li><a class="multitabs" href="monitor.php">监控任务</a></li><li><a class="multitabs" href="notice.php">通知日志</a></li></ul></div>
<?php }?>
<?php if (function_exists('mnbt_plugin_render_menu_user_html')) { echo mnbt_plugin_render_menu_user_html(); } ?>
      </div>
      <div class="west-usage-box"><div>空间使用统计图：<span id="west-web-small">--</span></div><div class="west-mini-bar"><span></span></div><a class="multitabs" href="set.php?gn=cache">速度优化</a></div>
    </div>
  </aside>
  <section class="west-content-wrap">
    <div class="west-breadcrumb">西部风格用户首页 &gt; 用户管理中心 &gt; 虚拟主机管理首页 &gt; <?= htmlspecialchars($user ?? '', ENT_QUOTES, 'UTF-8') ?></div>
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
  $("#jqui-sidebar-accordion").accordion({ heightStyle: "content", collapsible: true, active: 0, icons: false });
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
