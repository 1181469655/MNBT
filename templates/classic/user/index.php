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
<link rel="stylesheet" type="text/css" href="<?=mnbt_asset_url('css/materialdesignicons.min.css')?>">
<link rel="stylesheet" type="text/css" href="<?=mnbt_asset_url('css/bootstrap.min.css')?>">
<link rel="stylesheet" type="text/css" href="<?=mnbt_asset_url('js/bootstrap-multitabs/multitabs.min.css')?>">
<link rel="stylesheet" type="text/css" href="<?=mnbt_asset_url('css/animate.min.css')?>">
<link rel="stylesheet" type="text/css" href="<?=mnbt_asset_url('css/style.min.css')?>">

<link rel="stylesheet" type="text/css" href="<?=mnbt_asset_url('js/jquery-confirm/jquery-confirm.min.css')?>">
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
<div class="lyear-layout-web">
<div class="lyear-layout-container">
<!--左侧导航-->
<aside class="lyear-layout-sidebar">

<!-- logo -->
<div id="logo" class="sidebar-header"> <a href="index.php"> <img src="<?=mnbt_asset_url('upload_logo/logo.index.png')?>?<?=$conf['auther']?>" title="MN_logo" alt="MN_logo" /> </a> </div>
<div class="lyear-layout-sidebar-info lyear-scroll">
<nav class="sidebar-main">
<ul class="nav-drawer">
<li class="nav-item active"> <a href="sy.php" class="multitabs"> <i class="mdi mdi-home"></i> <span>控制面板</span> </a> </li>
<li class="nav-item"> <a href="site_stats.php" class="multitabs"> <i class="mdi mdi-chart-bar"></i> <span>站点统计</span> </a> </li>
<li class="nav-item nav-item-has-subnav"> <a href="javascript:void(0)"> <i class="mdi mdi-console"></i> <span>基本配置</span> </a>
  <ul class="nav nav-subnav">
      <li> <a class="multitabs" href="set.php?gn=php">PHP版本切换</a> </li>
  <!--  <li> <a class="multitabs" href="set.php?gn=rzfx">日志分析</a> </li> -->
    <li> <a class="multitabs" href="set.php?gn=url">域名修改</a> </li>
    <li> <a class="multitabs" href="set.php?gn=pass">设置密码访问</a> </li>
    <li> <a class="multitabs" href="set.php?gn=mrwd">修改默认文档</a> </li>
   <!-- <li> <a class="multitabs" href="set.php?gn=nginx">nginx配置文件</a> </li>-->
    <li> <a class="multitabs" href="set.php?gn=yxml">设置运行目录</a> </li>
    <li> <a class="multitabs" href="set.php?gn=wjt">设置伪静态</a> </li>
    <li> <a class="multitabs" href="set.php?gn=ssl">SSL配置</a> </li>
    <li> <a class="multitabs" href="set.php?gn=fdl">防盗链</a> </li>
   <!-- <li> <a class="multitabs" href="set.php?gn=fzjh">负载均衡配置(开发中)</a> </li> -->
    <li> <a class="multitabs" href="set.php?gn=gzip">Gzip配置</a> </li>
    <li> <a class="multitabs" href="set.php?gn=cache">缓存配置</a> </li>
    <li> <a class="multitabs" href="set.php?gn=xgpass">修改密码</a> </li>
  </ul>
</li>
<li class="nav-item nav-item-has-subnav"> <a href="javascript:void(0)"> <i class="mdi mdi-format-align-justify"></i> <span>数据管理</span> </a>
  <ul class="nav nav-subnav">
    <li> <a class="multitabs" href="ftp.php">在线文件管理</a> </li>
    <li> <a target="_blank" href="mysql.php">SQL管理面板</a> </li>
    <li> <a class="multitabs" href="sqlgl.php">SQL数据备份</a> </li>
    <li> <a class="multitabs" href="set.php?gn=mysqlcz">SQL权限设置</a> </li>
  </ul>
</li>
<li class="nav-item nav-item-has-subnav"> <a href="javascript:void(0)"> <i class="mdi mdi-sitemap"></i> <span>网站管理</span> </a>
  <ul class="nav nav-subnav">
    <li> <a class="multitabs" href="webgl.php?gn=yjbs">一键部署</a> </li>
    <li> <a class="multitabs" href="monitor.php">监控任务</a> </li>
    <li> <a class="multitabs" href="notice.php">通知日志</a> </li>
  </ul>
</li>
<?php
if (function_exists('mnbt_plugin_render_menu_user_html')) {
  echo mnbt_plugin_render_menu_user_html();
}
?>
</ul>
<div class="sidebar-footer">
  <p class="copyright">
    <?=$conf['hxp']?>
  </p>
</div>
</div>
</aside>
<!--End 左侧导航--> 

<!--头部信息-->
<header class="lyear-layout-header">
  <nav class="navbar">
    <div class="navbar-left">
      <div class="lyear-aside-toggler float-left"> <span class="lyear-toggler-bar"></span> <span class="lyear-toggler-bar"></span> <span class="lyear-toggler-bar"></span> </div>
      <i class="ml-2 mdi mdi-refresh mdi-18px" id="iframe_shuax"></i>
    </div>
    <ul class="navbar-right d-flex align-items-center">
      <!--切换主题配色-->
      <li class="dropdown dropdown-skin"> <span data-toggle="dropdown" class="icon-item"> <i class="mdi mdi-palette"></i> </span>
        <ul class="dropdown-menu dropdown-menu-right" data-stopPropagation="true">
          <li class="drop-title">
            <p>LOGO</p>
          </li>
          <li class="drop-skin-li clearfix"> <span class="inverse">
            <input type="radio" name="logo_bg" value="default" id="logo_bg_1" checked>
            <label for="logo_bg_1"></label>
            </span> <span>
            <input type="radio" name="logo_bg" value="color_2" id="logo_bg_2">
            <label for="logo_bg_2"></label>
            </span> <span>
            <input type="radio" name="logo_bg" value="color_3" id="logo_bg_3">
            <label for="logo_bg_3"></label>
            </span> <span>
            <input type="radio" name="logo_bg" value="color_4" id="logo_bg_4">
            <label for="logo_bg_4"></label>
            </span> <span>
            <input type="radio" name="logo_bg" value="color_5" id="logo_bg_5">
            <label for="logo_bg_5"></label>
            </span> <span>
            <input type="radio" name="logo_bg" value="color_6" id="logo_bg_6">
            <label for="logo_bg_6"></label>
            </span> <span>
            <input type="radio" name="logo_bg" value="color_7" id="logo_bg_7">
            <label for="logo_bg_7"></label>
            </span> <span>
            <input type="radio" name="logo_bg" value="color_8" id="logo_bg_8">
            <label for="logo_bg_8"></label>
            </span> </li>
          <li class="drop-title">
            <p>头部</p>
          </li>
          <li class="drop-skin-li clearfix"> <span class="inverse">
            <input type="radio" name="header_bg" value="default" id="header_bg_1" checked>
            <label for="header_bg_1"></label>
            </span> <span>
            <input type="radio" name="header_bg" value="color_2" id="header_bg_2">
            <label for="header_bg_2"></label>
            </span> <span>
            <input type="radio" name="header_bg" value="color_3" id="header_bg_3">
            <label for="header_bg_3"></label>
            </span> <span>
            <input type="radio" name="header_bg" value="color_4" id="header_bg_4">
            <label for="header_bg_4"></label>
            </span> <span>
            <input type="radio" name="header_bg" value="color_5" id="header_bg_5">
            <label for="header_bg_5"></label>
            </span> <span>
            <input type="radio" name="header_bg" value="color_6" id="header_bg_6">
            <label for="header_bg_6"></label>
            </span> <span>
            <input type="radio" name="header_bg" value="color_7" id="header_bg_7">
            <label for="header_bg_7"></label>
            </span> <span>
            <input type="radio" name="header_bg" value="color_8" id="header_bg_8">
            <label for="header_bg_8"></label>
            </span> </li>
          <li class="drop-title">
            <p>侧边栏</p>
          </li>
          <li class="drop-skin-li clearfix"> <span class="inverse">
            <input type="radio" name="sidebar_bg" value="default" id="sidebar_bg_1" checked>
            <label for="sidebar_bg_1"></label>
            </span> <span>
            <input type="radio" name="sidebar_bg" value="color_2" id="sidebar_bg_2">
            <label for="sidebar_bg_2"></label>
            </span> <span>
            <input type="radio" name="sidebar_bg" value="color_3" id="sidebar_bg_3">
            <label for="sidebar_bg_3"></label>
            </span> <span>
            <input type="radio" name="sidebar_bg" value="color_4" id="sidebar_bg_4">
            <label for="sidebar_bg_4"></label>
            </span> <span>
            <input type="radio" name="sidebar_bg" value="color_5" id="sidebar_bg_5">
            <label for="sidebar_bg_5"></label>
            </span> <span>
            <input type="radio" name="sidebar_bg" value="color_6" id="sidebar_bg_6">
            <label for="sidebar_bg_6"></label>
            </span> <span>
            <input type="radio" name="sidebar_bg" value="color_7" id="sidebar_bg_7">
            <label for="sidebar_bg_7"></label>
            </span> <span>
            <input type="radio" name="sidebar_bg" value="color_8" id="sidebar_bg_8">
            <label for="sidebar_bg_8"></label>
            </span> </li>
        </ul>
      </li>
      <!--切换主题配色-->
      <li class="dropdown dropdown-profile"> <a href="javascript:void(0)" data-toggle="dropdown" class="dropdown-toggle"> <img class="img-avatar img-avatar-48 m-r-10" src="<?=mnbt_asset_url('upload_logo/logo.head.png')?>?<?=$conf['auther']?>" alt="<?=$user?>" /> <span>
        <?=$user?>
        </span> </a>
        <ul class="dropdown-menu dropdown-menu-right">
          <li> <a class="dropdown-item"> <i class="mdi mdi-delete"> 清空缓存</i> </a> </li>
          <li class="dropdown-divider"></li>
          <li> <a class="dropdown-item" onclick="chteci();"> <i class="mdi mdi-logout-variant"> 退出登录</i> </a> </li>
        </ul>
      </li>
    </ul>
  </nav>
</header>
<!--End 头部信息--> 

<!--页面主要内容-->
<main class="lyear-layout-content">
  <div id="iframe-content"></div>
</main>
<!--End 页面主要内容-->
</div>
</div>
<script type="text/javascript" src="<?=mnbt_asset_url('js/jquery.min.js')?>"></script> 
<script type="text/javascript" src="<?=mnbt_asset_url('js/popper.min.js')?>"></script> 
<script type="text/javascript" src="<?=mnbt_asset_url('js/bootstrap.min.js')?>"></script> 
<script type="text/javascript" src="<?=mnbt_asset_url('js/perfect-scrollbar.min.js')?>"></script> 
<script type="text/javascript" src="<?=mnbt_asset_url('js/bootstrap-multitabs/multitabs.min.js')?>"></script> 
<script type="text/javascript" src="<?=mnbt_asset_url('js/jquery.cookie.min.js')?>"></script> 
<script type="text/javascript" src="<?=mnbt_asset_url('js/index.min.js')?>"></script> 

<!--消息提示--> 
<script type="text/javascript" src="<?=mnbt_asset_url('js/jquery-confirm/jquery-confirm.min.js')?>"></script>

<script type="text/javascript" src="<?=mnbt_asset_url('js/lyear-loading.js')?>"></script> 
<script type="text/javascript" src="<?=mnbt_asset_url('js/main.min.js')?>"></script> 
<script type="text/javascript" src="<?=mnbt_asset_url('js/fn-hs.js')?>"></script> 
<script type="text/javascript" src="<?=mnbt_asset_url('js/bootstrap-notify.min.js')?>"></script> 
<script type="text/javascript">
function xiaole()
{
    $.confirm({
        title: '邮箱绑定',
        //content: 'url:form.html',  // 也可以采用url形式
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
                    if (!emailRegex.test(input.val())||!$.trim(input.val())) 
                    {
                        $.alert({
                            content: "邮箱错误",
                            type: 'red'
                        });
                        return false;
                    } 
                    msloading('正在处理中，请稍后...');  // 加载显示
                    let data = {};
                    data["gn"] = "mailbd";
                    data['mail'] = input.val();
                    $.post('./ajax.php', data, function (date) {
                    var jsoe= JSON.parse(date);    
                    var qk= jsoe.code
                    if(qk == "绑定成功")
                    {
                        msalert(1,'绑定成功！将在两秒后跳转登录！',2000);
                        setTimeout(function()
                    {
                        window.location.href="./index.php";
                        },2000);
                    }
                    else
                    {
                        msalert(4, qk,2000);
                        setTimeout(function()
                    {
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
			msloading('正在退出登录中...','text-info','text-info');  // 加载显示
			let data = {};
			data["gn"]="login";
			data["logout"]="tclogin";
			$.post('./ajax.php', data, function (date) {    
			var jsoe= JSON.parse(date);    
			var qk= jsoe.code
			msalert(1,qk,2000);
			window.location.href="./login.php"
			msloadingde();
			})
			}
		

			//页面加载动画
			$thisTabs = $('#iframe-content')
			var datasl=[];
			$thisTabs.bind('DOMNodeInserted',function(){
			var xzl=$(this)[0].innerText;
			var dqs=xzl.split('\n');
			if(datasl.indexOf(dqs[dqs.length-1])==-1){
			//页面loading
			setTimeout(function(){
			var $thisTabs = parent.$('.mt-nav-bar .nav-tabs').find('a.active');
			var ifarid=$thisTabs.attr('data-id');
			$('#'+ifarid).contents().find('body').html('<link href="<?=mnbt_asset_url('css/style.min.css')?>" rel="stylesheet"><link href="<?=mnbt_asset_url('css/bootstrap.min.css')?>" rel="stylesheet"><link href="<?=mnbt_asset_url('css/index.loading.css')?>" rel="stylesheet"><div class="loading_upds"><div class="ctn-preloader"><div class="round_spinner"><div class="spinner"></div><img src="<?=mnbt_asset_url('upload_logo/logo.head.png')?>?<?=$conf['auther']?>" alt=""></div</div></div>');
			},2)
			}
			datasl=dqs;
			})
			
			//页面刷新
			$("#iframe_shuax").on('click',function(){
			var $thisTabs = parent.$('.mt-nav-bar .nav-tabs').find('a.active');
			var ifarid=$thisTabs.attr('data-id');
			//旋转动画
			$(this).css({animation: "rotate 0.5s linear 1",display: "inline-block"});
			setTimeout(function(){$("#iframe_shuax").removeAttr('style');},500);
			
			//页面loading
			$('#'+ifarid).contents().find('body').html('<link href="<?=mnbt_asset_url('css/style.min.css')?>" rel="stylesheet"><link href="<?=mnbt_asset_url('css/bootstrap.min.css')?>" rel="stylesheet"><link href="<?=mnbt_asset_url('css/index.loading.css')?>" rel="stylesheet"><div class="loading_upds"><div class="ctn-preloader"><div class="round_spinner"><div class="spinner"></div><img src="<?=mnbt_asset_url('upload_logo/logo.head.png')?>?<?=$conf['auther']?>" alt=""></div</div></div>');
			
			$('#'+ifarid).attr('src', $('#'+ifarid).attr('src'));       //刷新子页面
			})
			

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
