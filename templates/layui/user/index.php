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
<?php /* —— 回退栈：Bootstrap/jQuery/fn-hs.js（保留 multitabs 插件所需依赖）—— */ ?>
<link rel="stylesheet" type="text/css" href="<?=mnbt_asset_url('css/bootstrap.min.css')?>">
<link rel="stylesheet" type="text/css" href="<?=mnbt_asset_url('css/materialdesignicons.min.css')?>">
<link rel="stylesheet" type="text/css" href="<?=mnbt_asset_url('js/bootstrap-multitabs/multitabs.min.css')?>">
<link rel="stylesheet" type="text/css" href="<?=mnbt_asset_url('css/animate.min.css')?>">
<link rel="stylesheet" type="text/css" href="<?=mnbt_asset_url('css/style.min.css')?>">
<link rel="stylesheet" type="text/css" href="<?=mnbt_asset_url('js/jquery-confirm/jquery-confirm.min.css')?>">
<script type="text/javascript" src="<?=mnbt_asset_url('js/fn-hs.js')?>"></script>
<?php /* —— Layui —— */ ?>
<link href="https://unpkg.com/layui@2.9.8/dist/css/layui.css" rel="stylesheet">
<link href="<?=mnbt_theme_asset('theme.css')?>" rel="stylesheet">
<style>
@keyframes rotate { 100%{-webkit-transform:rotate(360deg);} }
#iframe_shuax { cursor:pointer; }
</style>
</head>

<body>
<div class="ly-app">

  <!-- 侧边栏 -->
  <aside class="ly-sidebar" id="sidebar">
    <div class="ly-sidebar-header">
      <a href="index.php" class="ly-sidebar-logo">
        <span class="ly-logo-text"><?= htmlspecialchars($conf['name'] ?? 'MNBT', ENT_QUOTES, 'UTF-8') ?></span>
      </a>
    </div>
    <div class="ly-sidebar-body">
      <ul class="ly-menu" id="lyMenu">
        <li class="ly-menu-item active">
          <a href="sy.php" class="multitabs"><i class="mdi mdi-home"></i><span>控制面板</span></a>
        </li>
        <li class="ly-menu-item">
          <a href="site_stats.php" class="multitabs"><i class="mdi mdi-chart-bar"></i><span>站点统计</span></a>
        </li>
        <li class="ly-menu-item ly-submenu">
          <a href="javascript:;"><i class="mdi mdi-console"></i><span>基本配置</span><i class="mdi mdi-chevron-right ly-arrow"></i></a>
          <ul class="ly-submenu-list">
            <li><a href="set.php?gn=php" class="multitabs">PHP版本切换</a></li>
            <li><a href="set.php?gn=url" class="multitabs">域名修改</a></li>
            <li><a href="set.php?gn=pass" class="multitabs">设置密码访问</a></li>
            <li><a href="set.php?gn=mrwd" class="multitabs">修改默认文档</a></li>
            <li><a href="set.php?gn=yxml" class="multitabs">设置运行目录</a></li>
            <li><a href="set.php?gn=wjt" class="multitabs">设置伪静态</a></li>
            <li><a href="set.php?gn=ssl" class="multitabs">SSL配置</a></li>
            <li><a href="set.php?gn=fdl" class="multitabs">防盗链</a></li>
            <li><a href="set.php?gn=gzip" class="multitabs">Gzip配置</a></li>
            <li><a href="set.php?gn=cache" class="multitabs">缓存配置</a></li>
            <li><a href="set.php?gn=xgpass" class="multitabs">修改密码</a></li>
          </ul>
        </li>
        <li class="ly-menu-item ly-submenu">
          <a href="javascript:;"><i class="mdi mdi-format-align-justify"></i><span>数据管理</span><i class="mdi mdi-chevron-right ly-arrow"></i></a>
          <ul class="ly-submenu-list">
            <li><a href="ftp.php" class="multitabs">在线文件管理</a></li>
            <li><a href="mysql.php" target="_blank">SQL管理面板</a></li>
            <li><a href="sqlgl.php" class="multitabs">SQL数据备份</a></li>
            <li><a href="set.php?gn=mysqlcz" class="multitabs">SQL权限设置</a></li>
          </ul>
        </li>
        <li class="ly-menu-item ly-submenu">
          <a href="javascript:;"><i class="mdi mdi-sitemap"></i><span>网站管理</span><i class="mdi mdi-chevron-right ly-arrow"></i></a>
          <ul class="ly-submenu-list">
            <li><a href="webgl.php?gn=yjbs" class="multitabs">一键部署</a></li>
            <li><a href="monitor.php" class="multitabs">监控任务</a></li>
            <li><a href="notice.php" class="multitabs">通知日志</a></li>
          </ul>
        </li>
<?php
if (function_exists('mnbt_plugin_render_menu_user_html')) {
  echo mnbt_plugin_render_menu_user_html();
}
?>
      </ul>
    </div>
    <div class="ly-sidebar-footer"><?=$conf['hxp']?></div>
  </aside>

  <!-- 主区域 -->
  <div class="ly-main">

    <!-- 顶部栏 -->
    <header class="ly-topbar">
      <div class="ly-topbar-left">
        <button class="ly-toggle" id="sidebarToggle" title="折叠/展开侧栏">
          <i class="mdi mdi-menu" style="font-size:22px;"></i>
        </button>
        <span class="ly-refresh" id="iframe_shuax" title="刷新当前页">
          <i class="mdi mdi-refresh" style="font-size:20px;"></i>
        </span>
      </div>
      <div class="ly-topbar-right">
        <div class="ly-usermenu" id="lyUserMenu">
          <a href="javascript:;" class="ly-user-toggle">
            <img src="<?=mnbt_asset_url('upload_logo/logo.head.png')?>?<?=$conf['auther']?>" alt="<?=$user?>">
            <span><?=$user?></span>
            <i class="mdi mdi-chevron-down"></i>
          </a>
          <ul class="ly-user-dropdown">
            <li><a href="javascript:;" onclick="chteci();"><i class="mdi mdi-logout-variant"></i> 退出登录</a></li>
          </ul>
        </div>
      </div>
    </header>

    <!-- 内容 -->
    <main class="ly-content">
      <div id="iframe-content"></div>
    </main>

  </div>

</div>

<?php /* —— multitabs 插件依赖（必须保留）—— */ ?>
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
<?php /* —— Layui —— */ ?>
<script src="https://unpkg.com/layui@2.9.8/dist/layui.js"></script>

<script type="text/javascript">
// 侧栏折叠
$("#sidebarToggle").on('click', function(){
  $("#sidebar").toggleClass('collapsed');
  $(".ly-app").toggleClass('sidebar-collapsed');
  $(this).find('.mdi').toggleClass('mdi-menu mdi-backburger');
});

// 子菜单展开/折叠
$(document).on('click', '.ly-submenu > a', function(e){
  e.preventDefault();
  var $item = $(this).parent();
  var $sub = $item.children('.ly-submenu-list');
  var isOpen = $item.hasClass('open');
  // 手风琴：同级只展开一个
  $item.siblings('.ly-submenu.open').removeClass('open').children('.ly-submenu-list').slideUp(180);
  $item.toggleClass('open', !isOpen);
  $sub.stop(true, true).slideToggle(180);
});

// 点击子菜单后高亮
$(document).on('click', '.ly-menu a', function(){
  if ($(this).attr('href') === 'javascript:;') return;
  $('.ly-menu-item').removeClass('active');
  $(this).closest('.ly-menu-item').addClass('active');
});

// 用户下拉
$("#lyUserMenu").on('click', function(){
  $(this).toggleClass('open');
});
$(document).on('click', function(e){
  if (!$(e.target).closest('#lyUserMenu').length) {
    $("#lyUserMenu").removeClass('open');
  }
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
                    var emailRegex = /^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/;
                    if (!emailRegex.test(input.val())||!$.trim(input.val()))
                    {
                        $.alert({ content: "邮箱错误", type: 'red' });
                        return false;
                    }
                    msloading('正在处理中，请稍后...');
                    let data = {};
                    data["gn"] = "mailbd";
                    data['mail'] = input.val();
                    $.post('./ajax.php', data, function (date) {
                        var jsoe = JSON.parse(date); var qk = jsoe.code;
                        if(qk == "绑定成功") {
                            msalert(1,'绑定成功！将在两秒后跳转登录！',2000);
                            setTimeout(function(){ window.location.href="./index.php"; },2000);
                        } else {
                            msalert(4, qk,2000);
                            setTimeout(function(){ window.location.href="./index.php"; },2000);
                        }
                    });
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
    });
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
            $('#'+ifarid).contents().find('body').html('<link href="<?=mnbt_asset_url('css/style.min.css')?>" rel="stylesheet"><link href="<?=mnbt_asset_url('css/bootstrap.min.css')?>" rel="stylesheet"><link href="<?=mnbt_asset_url('css/index.loading.css')?>" rel="stylesheet"><div class="loading_upds"><div class="ctn-preloader"><div class="round_spinner"><div class="spinner"></div><img src="<?=mnbt_asset_url('upload_logo/logo.head.png')?>?<?=$conf['auther']?>" alt=""></div</div></div>');
        },2);
    }
    datasl=dqs;
});

// 刷新
$("#iframe_shuax").on('click',function(){
    var $thisTabs = parent.$('.mt-nav-bar .nav-tabs').find('a.active');
    var ifarid=$thisTabs.attr('data-id');
    $(this).css({animation: "rotate 0.5s linear 1",display: "inline-block"});
    setTimeout(function(){$("#iframe_shuax").removeAttr('style');},500);
    $('#'+ifarid).contents().find('body').html('<link href="<?=mnbt_asset_url('css/style.min.css')?>" rel="stylesheet"><link href="<?=mnbt_asset_url('css/bootstrap.min.css')?>" rel="stylesheet"><link href="<?=mnbt_asset_url('css/index.loading.css')?>" rel="stylesheet"><div class="loading_upds"><div class="ctn-preloader"><div class="round_spinner"><div class="spinner"></div><img src="<?=mnbt_asset_url('upload_logo/logo.head.png')?>?<?=$conf['auther']?>" alt=""></div</div></div>');
    $('#'+ifarid).attr('src', $('#'+ifarid).attr('src'));
});
</script>
</body>
</html>
<?php
if($conf['zjyxbd'] == "true")
{
if($yhc['mailuser'] == "" || $yhc['mailuser'] == null)
{
    echo '<script>xiaole()</script>';
}
}
?>
