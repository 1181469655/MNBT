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
<link rel="stylesheet" type="text/css" href="<?=mnbt_asset_url('css/bootstrap.min.css')?>">
<link rel="stylesheet" type="text/css" href="<?=mnbt_asset_url('css/materialdesignicons.min.css')?>">
<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/smoothness/jquery-ui.css">
<link rel="stylesheet" type="text/css" href="<?=mnbt_asset_url('js/bootstrap-multitabs/multitabs.min.css')?>">
<link rel="stylesheet" type="text/css" href="<?=mnbt_asset_url('css/animate.min.css')?>">
<link rel="stylesheet" type="text/css" href="<?=mnbt_asset_url('js/jquery-confirm/jquery-confirm.min.css')?>">
<link href="<?=mnbt_theme_asset('theme.css')?>" rel="stylesheet">
<script type="text/javascript" src="<?=mnbt_asset_url('js/fn-hs.js')?>"></script>
<style>
@keyframes rotate {
    100%{-webkit-transform:rotate(360deg);}
}
#iframe_shuax{
    cursor:pointer;
}
</style>
</head>
<body>

<div class="jqui-layout-web">
<div class="jqui-layout-container">

<!-- 左侧导航 -->
<aside class="jqui-layout-sidebar">
  <div class="sidebar-header">
    <a href="index.php" class="sidebar-header-text">
      <?= htmlspecialchars($conf['name'] ?? 'MNBT', ENT_QUOTES, 'UTF-8') ?>
    </a>
  </div>
  <div class="sidebar-scroll">

    <!-- jQueryUI accordion 主导航 -->
    <div id="jqui-sidebar-accordion">
<?php if($yhc['hxc']=='1'){ ?>
      <h3><i class="mdi mdi-home"></i> 控制面板</h3>
      <div>
        <ul class="jqui-subnav">
          <li><a href="sy.php" class="multitabs">控制面板</a></li>
        </ul>
      </div>
      <h3><i class="mdi mdi-console"></i> 基本配置</h3>
      <div>
        <ul class="jqui-subnav">
          <li><a class="multitabs" href="set.php?gn=CDN_url">域名修改</a></li>
        </ul>
      </div>
<?php }else{ ?>
      <h3><i class="mdi mdi-home"></i> 控制面板</h3>
      <div>
        <ul class="jqui-subnav">
          <li><a href="sy.php" class="multitabs">控制面板</a></li>
        </ul>
      </div>
      <h3><i class="mdi mdi-console"></i> 基本配置</h3>
      <div>
        <ul class="jqui-subnav">
          <li><a class="multitabs" href="set.php?gn=php">PHP版本切换</a></li>
          <li><a class="multitabs" href="set.php?gn=url">域名修改</a></li>
          <li><a class="multitabs" href="set.php?gn=pass">设置密码访问</a></li>
          <li><a class="multitabs" href="set.php?gn=mrwd">修改默认文档</a></li>
          <li><a class="multitabs" href="set.php?gn=yxml">设置运行目录</a></li>
          <li><a class="multitabs" href="set.php?gn=wjt">设置伪静态</a></li>
          <li><a class="multitabs" href="set.php?gn=ssl">SSL配置</a></li>
          <li><a class="multitabs" href="set.php?gn=fdl">防盗链</a></li>
          <li><a class="multitabs" href="set.php?gn=gzip">Gzip配置</a></li>
          <li><a class="multitabs" href="set.php?gn=cache">缓存配置</a></li>
          <li><a class="multitabs" href="set.php?gn=xgpass">修改密码</a></li>
        </ul>
      </div>
      <h3><i class="mdi mdi-format-align-justify"></i> 数据管理</h3>
      <div>
        <ul class="jqui-subnav">
          <li><a class="multitabs" href="ftp.php">在线文件管理</a></li>
          <li><a target="_blank" href="mysql.php">SQL管理面板</a></li>
          <li><a class="multitabs" href="sqlgl.php">SQL数据备份</a></li>
          <li><a class="multitabs" href="set.php?gn=mysqlcz">SQL权限设置</a></li>
        </ul>
      </div>
      <h3><i class="mdi mdi-sitemap"></i> 网站管理</h3>
      <div>
        <ul class="jqui-subnav">
          <li><a class="multitabs" href="webgl.php?gn=yjbs">一键部署</a></li>
          <li><a class="multitabs" href="monitor.php">监控任务</a></li>
          <li><a class="multitabs" href="notice.php">通知日志</a></li>
        </ul>
      </div>
<?php }?>
<?php
if (function_exists('mnbt_plugin_render_menu_user_html')) {
  echo mnbt_plugin_render_menu_user_html();
}
?>
    </div>

    <div class="sidebar-footer">
      <p class="copyright"><?=$conf['hxp']?></p>
    </div>
  </div>
</aside>
<!-- End 左侧导航 -->

<!-- 右侧区域 -->
<div style="flex:1;display:flex;flex-direction:column;overflow:hidden;min-width:0;">

  <!-- 顶部栏 -->
  <header class="jqui-layout-header">
    <div class="jqui-header-left">
      <div class="jqui-aside-toggler" id="jqui-aside-toggler">
        <span class="jqui-toggler-bar"></span>
        <span class="jqui-toggler-bar"></span>
        <span class="jqui-toggler-bar"></span>
      </div>
      <i class="mdi mdi-refresh mdi-18px" id="iframe_shuax" style="color:#7f8c8d;cursor:pointer;font-size:20px;"></i>
    </div>
    <div class="jqui-header-right">
      <div class="jqui-user-dropdown">
        <button id="userMenuBtn">
          <i class="mdi mdi-account-circle" style="font-size:20px;vertical-align:middle;margin-right:4px;"></i>
          <?=$user?>
        </button>
        <ul class="jqui-dropdown-menu" id="userDropdown">
          <li><a onclick="chteci();"><i class="mdi mdi-logout-variant"></i> 退出登录</a></li>
        </ul>
      </div>
    </div>
  </header>

  <!-- 页面主体 -->
  <main class="jqui-layout-content">
    <div id="iframe-content"></div>
  </main>

</div>
<!-- End 右侧区域 -->

</div>
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
  // jQueryUI accordion 侧栏
  $("#jqui-sidebar-accordion").accordion({
    heightStyle: "content",
    collapsible: true,
    active: false,
    icons: false
  });

  // 用户按钮
  $("#userMenuBtn").button({
    icons: { primary: null }
  }).click(function(e) {
    e.stopPropagation();
    $("#userDropdown").toggle();
  });
  $(document).click(function() {
    $("#userDropdown").hide();
  });

  // 移动端侧栏 toggle
  $("#jqui-aside-toggler").click(function() {
    $(".jqui-layout-sidebar").toggleClass("open");
  });

  // 点击侧栏链接关闭移动端菜单
  $(".jqui-subnav a, .jqui-sidebar-nav a").click(function() {
    if ($(window).width() <= 768) {
      $(".jqui-layout-sidebar").removeClass("open");
    }
  });
});

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
                    var errorText = this.$content.find('.text-danger');
                    var emailRegex = /^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/;
                    if (!emailRegex.test(input.val())||!$.trim(input.val())) {
                        $.alert({
                            content: "邮箱错误",
                            type: 'red'
                        });
                        return false;
                    }
                    msloading('正在处理中，请稍后...');
                    let data = {};
                    data["gn"] = "mailbd";
                    data['mail'] = input.val();
                    $.post('./ajax.php', data, function (date) {
                        var jsoe= JSON.parse(date);
                        var qk= jsoe.code;
                        if(qk == "绑定成功") {
                            msalert(1,'绑定成功！将在两秒后跳转登录！',2000);
                            setTimeout(function() {
                                window.location.href="./index.php";
                            },2000);
                        } else {
                            msalert(4, qk,2000);
                            setTimeout(function() {
                                window.location.href="./index.php";
                            },2000);
                        }
                    })
                }
            },
        }
    });
}

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

// 页面加载动画
$thisTabs = $('#iframe-content');
var datasl=[];
$thisTabs.bind('DOMNodeInserted',function(){
    var xzl=$(this)[0].innerText;
    var dqs=xzl.split('\n');
    if(datasl.indexOf(dqs[dqs.length-1])==-1){
        setTimeout(function(){
            var $thisTabs = parent.$('.mt-nav-bar .nav-tabs').find('a.active');
            var ifarid=$thisTabs.attr('data-id');
            $('#'+ifarid).contents().find('body').html('<link href="<?=mnbt_asset_url('css/bootstrap.min.css')?>" rel="stylesheet"><link href="<?=mnbt_asset_url('css/index.loading.css')?>" rel="stylesheet"><div class="loading_upds"><div class="ctn-preloader"><div class="round_spinner"><div class="spinner"></div><img src="<?=mnbt_asset_url('upload_logo/logo.head.png')?>?<?=$conf['auther']?>" alt=""></div</div></div>');
        },2);
    }
    datasl=dqs;
});

// 页面刷新
$("#iframe_shuax").on('click',function(){
    var $thisTabs = parent.$('.mt-nav-bar .nav-tabs').find('a.active');
    var ifarid=$thisTabs.attr('data-id');
    $(this).css({animation: "rotate 0.5s linear 1",display: "inline-block"});
    setTimeout(function(){$("#iframe_shuax").removeAttr('style');},500);
    $('#'+ifarid).contents().find('body').html('<link href="<?=mnbt_asset_url('css/bootstrap.min.css')?>" rel="stylesheet"><link href="<?=mnbt_asset_url('css/index.loading.css')?>" rel="stylesheet"><div class="loading_upds"><div class="ctn-preloader"><div class="round_spinner"><div class="spinner"></div><img src="<?=mnbt_asset_url('upload_logo/logo.head.png')?>?<?=$conf['auther']?>" alt=""></div</div></div>');
    $('#'+ifarid).attr('src', $('#'+ifarid).attr('src'));
});

</script>
</body>
</html>
<?php
if($conf['zjyxbd'] == "true") {
    if($yhc['mailuser'] == "" || $yhc['mailuser'] == null) {
        echo '<script>xiaole()</script>';
    }
}
?>
