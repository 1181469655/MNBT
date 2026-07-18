<!DOCTYPE html>
<html lang="zh">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
<meta name="keywords" content="MNBT控制面板,<?=$conf['name']?>-控制面板">
<meta name="description" content="MNBT控制面板,<?=$conf['name']?>-控制面板">
<meta name="author" content="yinq">
<title><?=$conf['name']?>-控制面板</title>
<link rel="icon" href="<?=mnbt_asset_url('upload_logo/logo.head.png')?>?<?=$conf['auther']?>" type="image/ico">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-touch-fullscreen" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="default">
<!-- 公共资源（保留 default 栈） -->
<link rel="stylesheet" type="text/css" href="<?=mnbt_asset_url('css/materialdesignicons.min.css')?>">
<link rel="stylesheet" type="text/css" href="<?=mnbt_asset_url('css/bootstrap.min.css')?>">
<link rel="stylesheet" type="text/css" href="<?=mnbt_asset_url('js/bootstrap-multitabs/multitabs.min.css')?>">
<link rel="stylesheet" type="text/css" href="<?=mnbt_asset_url('css/animate.min.css')?>">
<link rel="stylesheet" type="text/css" href="<?=mnbt_asset_url('css/style.min.css')?>">
<link rel="stylesheet" type="text/css" href="<?=mnbt_asset_url('js/jquery-confirm/jquery-confirm.min.css')?>">
<!-- Layui -->
<link rel="stylesheet" href="https://unpkg.com/layui@2.9.8/dist/css/layui.css">
<link href="<?=mnbt_theme_asset('theme.css')?>" rel="stylesheet">
<script type="text/javascript" src="<?=mnbt_asset_url('js/fn-hs.js')?>"></script>
<style>
@keyframes ly-rotate { 100% { -webkit-transform: rotate(360deg); } }
#iframe_shuax { cursor: pointer; }
</style>
</head>

<body class="layui-layout-body ly-app">
<div class="layui-layout layui-layout-admin ly-layout">

  <!-- 左侧导航 -->
  <div class="layui-side ly-side">
    <div class="ly-side-logo">
      <a href="index.php">
        <img src="<?=mnbt_asset_url('upload_logo/logo.index.png')?>?<?=$conf['auther']?>" title="MN_logo" alt="MN_logo" />
      </a>
    </div>
    <div class="layui-side-scroll ly-side-scroll">
      <ul class="layui-nav layui-nav-tree ly-nav" lay-filter="ly-nav">
        <li class="layui-nav-item layui-this">
          <a href="sy.php" class="multitabs"><i class="mdi mdi-home"></i><cite>控制面板</cite></a>
        </li>
        <li class="layui-nav-item">
          <a href="site_stats.php" class="multitabs"><i class="mdi mdi-chart-bar"></i><cite>站点统计</cite></a>
        </li>
<?php if($yhc['hxc']=='1'){?>
        <li class="layui-nav-item">
          <a href="javascript:;"><i class="mdi mdi-console"></i><cite>基本配置</cite></a>
          <dl class="layui-nav-child">
            <dd><a href="set.php?gn=CDN_url" class="multitabs">域名修改</a></dd>
          </dl>
        </li>
<?php }else{?>
        <li class="layui-nav-item">
          <a href="javascript:;"><i class="mdi mdi-console"></i><cite>基本配置</cite></a>
          <dl class="layui-nav-child">
            <dd><a href="set.php?gn=php" class="multitabs">PHP版本切换</a></dd>
            <dd><a href="set.php?gn=url" class="multitabs">域名修改</a></dd>
            <dd><a href="set.php?gn=pass" class="multitabs">设置密码访问</a></dd>
            <dd><a href="set.php?gn=mrwd" class="multitabs">修改默认文档</a></dd>
            <dd><a href="set.php?gn=yxml" class="multitabs">设置运行目录</a></dd>
            <dd><a href="set.php?gn=wjt" class="multitabs">设置伪静态</a></dd>
            <dd><a href="set.php?gn=ssl" class="multitabs">SSL配置</a></dd>
            <dd><a href="set.php?gn=fdl" class="multitabs">防盗链</a></dd>
            <dd><a href="set.php?gn=gzip" class="multitabs">Gzip配置</a></dd>
            <dd><a href="set.php?gn=cache" class="multitabs">缓存配置</a></dd>
            <dd><a href="set.php?gn=xgpass" class="multitabs">修改密码</a></dd>
          </dl>
        </li>
        <li class="layui-nav-item">
          <a href="javascript:;"><i class="mdi mdi-format-align-justify"></i><cite>数据管理</cite></a>
          <dl class="layui-nav-child">
            <dd><a href="ftp.php" class="multitabs">在线文件管理</a></dd>
            <dd><a href="mysql.php" target="_blank">SQL管理面板</a></dd>
            <dd><a href="sqlgl.php" class="multitabs">SQL数据备份</a></dd>
            <dd><a href="set.php?gn=mysqlcz" class="multitabs">SQL权限设置</a></dd>
          </dl>
        </li>
        <li class="layui-nav-item">
          <a href="javascript:;"><i class="mdi mdi-sitemap"></i><cite>网站管理</cite></a>
          <dl class="layui-nav-child">
            <dd><a href="webgl.php?gn=yjbs" class="multitabs">一键部署</a></dd>
            <dd><a href="monitor.php" class="multitabs">监控任务</a></dd>
            <dd><a href="notice.php" class="multitabs">通知日志</a></dd>
          </dl>
        </li>
<?php }?>
<?php
if (function_exists('mnbt_plugin_render_menu_user_html')) {
  echo mnbt_plugin_render_menu_user_html();
}
?>
      </ul>
      <div class="ly-side-footer"><?=$conf['hxp']?></div>
    </div>
  </div>
  <!--End 左侧导航-->

  <!-- 头部信息 -->
  <div class="layui-header ly-header">
    <div class="ly-header-left">
      <a href="javascript:;" class="ly-aside-toggler" id="ly-aside-toggler">
        <span></span><span></span><span></span>
      </a>
      <i class="ml-2 mdi mdi-refresh mdi-18px" id="iframe_shuax" title="刷新当前标签"></i>
    </div>
    <ul class="ly-header-right">
      <li class="dropdown dropdown-profile">
        <a href="javascript:void(0)" data-toggle="dropdown" class="dropdown-toggle">
          <img class="img-avatar img-avatar-48 m-r-10" src="<?=mnbt_asset_url('upload_logo/logo.head.png')?>?<?=$conf['auther']?>" alt="<?=$user?>" />
          <span><?=$user?></span>
        </a>
        <ul class="dropdown-menu dropdown-menu-right">
          <li><a class="dropdown-item"> <i class="mdi mdi-delete"></i> 清空缓存</a></li>
          <li class="dropdown-divider"></li>
          <li><a class="dropdown-item" onclick="chteci();"> <i class="mdi mdi-logout-variant"></i> 退出登录</a></li>
        </ul>
      </li>
    </ul>
  </div>
  <!--End 头部信息-->

  <!-- 页面主要内容 -->
  <div class="layui-body ly-body">
    <div id="iframe-content"></div>
  </div>
  <!--End 页面主要内容-->

</div>

<script type="text/javascript" src="<?=mnbt_asset_url('js/jquery.min.js')?>"></script>
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
<script src="https://unpkg.com/layui@2.9.8/dist/layui.js"></script>
<script type="text/javascript">
function xiaole() {
  $.confirm({
    title: '邮箱绑定',
    content: '<div class="form-group p-1 mb-0">' +
             '  <label class="control-label">请输入你的邮箱,必须输入邮箱</label>' +
             '  <input autofocus="" type="text" id="input-name" placeholder="请输入您的邮箱" class="form-control">' +
             '</div>',
    buttons: {
      sayMyName: {
        text: '提交',
        btnClass: 'btn-orange',
        action: function() {
          var input = this.$content.find('input#input-name');
          var emailRegex = /^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/;
          if (!emailRegex.test(input.val()) || !$.trim(input.val())) {
            $.alert({ content: "邮箱错误", type: 'red' });
            return false;
          }
          msloading('正在处理中，请稍后...');
          let data = {};
          data["gn"] = "mailbd";
          data['mail'] = input.val();
          $.post('./ajax.php', data, function (date) {
            var jsoe = JSON.parse(date);
            var qk = jsoe.code;
            if (qk == "绑定成功") {
              msalert(1, '绑定成功！将在两秒后跳转登录！', 2000);
              setTimeout(function() { window.location.href = "./index.php"; }, 2000);
            } else {
              msalert(4, qk, 2000);
              setTimeout(function() { window.location.href = "./index.php"; }, 2000);
            }
          });
        }
      }
    }
  });
}

function chteci() {
  msloading('正在退出登录中...', 'text-info', 'text-info');
  let data = {};
  data["gn"] = "login";
  data["logout"] = "tclogin";
  $.post('./ajax.php', data, function (date) {
    var jsoe = JSON.parse(date);
    var qk = jsoe.code;
    msalert(1, qk, 2000);
    window.location.href = "./login.php";
    msloadingde();
  });
}

// 页面加载动画
$thisTabs = $('#iframe-content');
var datasl = [];
$thisTabs.bind('DOMNodeInserted', function () {
  var xzl = $(this)[0].innerText;
  var dqs = xzl.split('\n');
  if (datasl.indexOf(dqs[dqs.length - 1]) == -1) {
    setTimeout(function () {
      var $thisTabs = parent.$('.mt-nav-bar .nav-tabs').find('a.active');
      var ifarid = $thisTabs.attr('data-id');
      $('#' + ifarid).contents().find('body').html('<link href="<?=mnbt_asset_url('css/style.min.css')?>" rel="stylesheet"><link href="<?=mnbt_asset_url('css/bootstrap.min.css')?>" rel="stylesheet"><link href="<?=mnbt_asset_url('css/index.loading.css')?>" rel="stylesheet"><div class="loading_upds"><div class="ctn-preloader"><div class="round_spinner"><div class="spinner"></div><img src="<?=mnbt_asset_url('upload_logo/logo.head.png')?>?<?=$conf['auther']?>" alt=""></div></div></div>');
    }, 2);
  }
  datasl = dqs;
});

// 页面刷新
$("#iframe_shuax").on('click', function () {
  var $thisTabs = parent.$('.mt-nav-bar .nav-tabs').find('a.active');
  var ifarid = $thisTabs.attr('data-id');
  $(this).css({ animation: "ly-rotate 0.5s linear 1", display: "inline-block" });
  setTimeout(function () { $("#iframe_shuax").removeAttr('style'); }, 500);
  $('#' + ifarid).contents().find('body').html('<link href="<?=mnbt_asset_url('css/style.min.css')?>" rel="stylesheet"><link href="<?=mnbt_asset_url('css/bootstrap.min.css')?>" rel="stylesheet"><link href="<?=mnbt_asset_url('css/index.loading.css')?>" rel="stylesheet"><div class="loading_upds"><div class="ctn-preloader"><div class="round_spinner"><div class="spinner"></div><img src="<?=mnbt_asset_url('upload_logo/logo.head.png')?>?<?=$conf['auther']?>" alt=""></div></div></div>');
  $('#' + ifarid).attr('src', $('#' + ifarid).attr('src'));
});

// 侧栏折叠
$("#ly-aside-toggler").on('click', function () {
  var $body = $('body');
  if ($body.hasClass('ly-side-collapse')) {
    $body.removeClass('ly-side-collapse');
  } else {
    $body.addClass('ly-side-collapse');
  }
});

// Layui 导航折叠
if (window.layui && layui.element) {
  layui.use(['element'], function () {
    layui.element.render('nav', 'ly-nav');
  });
}
</script>
</body>
</html>
<?php
if ($conf['zjyxbd'] == "true") {
  if ($yhc['mailuser'] == "" || $yhc['mailuser'] == null) {
    echo '<script>xiaole()</script>';
  }
}
?>
