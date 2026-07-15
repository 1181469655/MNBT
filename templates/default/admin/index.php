<!DOCTYPE html>
<html lang="zh">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
<meta name="keywords" content="MNBT后台">
<meta name="description" content="MNBT后台">
<meta name="author" content="yinq">
<title>MN宝塔主机系统-后台管理</title>
<link rel="icon" href="<?=mnbt_asset_url('images/logo-ico.png')?>" type="image/ico">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-touch-fullscreen" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="default">
<link rel="stylesheet" type="text/css" href="<?=mnbt_asset_url('css/materialdesignicons.min.css')?>">
<link rel="stylesheet" type="text/css" href="<?=mnbt_asset_url('css/bootstrap.min.css')?>">
<link rel="stylesheet" type="text/css" href="<?=mnbt_asset_url('js/bootstrap-multitabs/multitabs.min.css')?>">
<link rel="stylesheet" type="text/css" href="<?=mnbt_asset_url('css/animate.min.css')?>">
<link rel="stylesheet" type="text/css" href="<?=mnbt_asset_url('css/style.min.css')?>">
<script type="text/javascript" src="<?=mnbt_asset_url('js/fn-hs.js')?>"></script>
<style>
.textmntti {
    margin: 2px 10px;
}
@keyframes rotate {
    100%{-webkit-transform:rotate(360deg);}
}

#iframe_shuax{
    cursor:pointer;
}

.modal-content{
    box-shadow: 0 0 10px -1px;
}
</style>
</head>

<body>
<div class="lyear-layout-web">
  <div class="lyear-layout-container"> 
    <!--左侧导航-->
    <aside class="lyear-layout-sidebar"> 
      
      <!-- logo -->
      <div id="logo" class="sidebar-header"> <a href="index.php"> <img src="<?=mnbt_asset_url('admin_logo/logo.index.png')?>" title="MN_logo" alt="MN_logo" /> </a> </div>
      <div class="lyear-layout-sidebar-info lyear-scroll">
        <nav class="sidebar-main">
          <ul class="nav-drawer">
            <li class="nav-item active"> <a href="sy.php" class="multitabs"> <i class="mdi mdi-home"></i> <span>后台首页</span> </a> </li>
            <li class="nav-item nav-item-has-subnav"> <a href="javascript:void(0)"> <i class="mdi mdi-console"></i> <span>系统管理</span> </a>
              <ul class="nav nav-subnav">
                <li> <a class="multitabs" href="set.php?gn=wz">网站设置</a> </li>
                <li> <a class="multitabs" href="set.php?gn=gl">管理设置</a> </li>
                <li> <a class="multitabs" href="set.php?gn=api">API设置</a> </li>
                <li> <a class="multitabs" href="pay_settings.php">支付设置</a> </li>
                <li> <a class="multitabs" href="set.php?gn=mail">邮箱设置</a> </li>
                <li> <a class="multitabs" href="set.php?gn=jk">监控主机删除设置</a> </li>
                <li> <a class="multitabs" href="set.php?gn=kzmb">控制面板管理</a> </li>
                <li> <a class="multitabs" href="set.php?gn=theme">前端模板</a> </li>
                <li> <a class="multitabs" href="plugin.php">插件管理</a> </li>
                <li> <a class="multitabs" href="tutorial.php">教程及监控</a> </li>
                <li> <a class="multitabs" href="update.php">系统更新</a> </li>
                <li> <a class="multitabs" href="list.php?gn=log">操作日志</a> </li>
              </ul>
            </li>
            <?php
            if (function_exists('mnbt_plugin_render_menu_admin_html')) {
              echo mnbt_plugin_render_menu_admin_html();
            }
            ?>
            <li class="nav-item nav-item-has-subnav"> <a href="javascript:void(0)"> <i class="mdi mdi-domain"></i> <span>二级域名</span> </a>
              <ul class="nav nav-subnav">
                <li> <a class="multitabs" href="list.php?gn=ym">域名列表</a> </li>
                <li> <a class="multitabs" href="add.php?gn=ym">添加域名</a> </li>
              </ul>
            </li>
            <li class="nav-item nav-item-has-subnav"> <a href="javascript:void(0)"> <i class="mdi mdi-server"></i> <span>宝塔管理</span> </a>
              <ul class="nav nav-subnav">
                <li> <a class="multitabs" href="list.php?gn=bt">宝塔列表</a> </li>
                <li> <a class="multitabs" href="add.php?gn=bt">添加宝塔</a> </li>
              </ul>
            </li>
            <li class="nav-item nav-item-has-subnav"> <a href="javascript:void(0)"> <i class="mdi mdi-server-network"></i> <span>节点管理</span> </a>
              <ul class="nav nav-subnav">
                <li> <a class="multitabs" href="node.php">节点列表</a> </li>
                <li> <a class="multitabs" href="node.php?tab=scan">违禁词扫描</a> </li>
              </ul>
            </li>
            <li class="nav-item nav-item-has-subnav"> <a href="javascript:void(0)"> <i class="mdi mdi-locker-multiple"></i> <span>主机管理</span> </a>
              <ul class="nav nav-subnav">
                <li> <a class="multitabs" href="list.php?gn=zj">主机列表</a> </li>
                <li> <a class="multitabs" href="add.php?gn=zj">添加主机</a> </li>
              </ul>
            </li>
            <li class="nav-item nav-item-has-subnav">
            <li class="nav-item nav-item-has-subnav"> <a href="javascript:void(0)"> <i class="mdi mdi-webpack"></i> <span>一键部署</span> </a>
              <ul class="nav nav-subnav">
                <li> <a class="multitabs" href="list.php?gn=dd">订单列表</a> </li>
                <li> <a class="multitabs" href="list.php?gn=cx">程序列表</a> </li>
                <li> <a class="multitabs" href="add.php?gn=cx">添加程序</a> </li>
                <li> <a class="multitabs" href="add.php?gn=dr">导入程序</a> </li>
              </ul>
              <li class="nav-item"> <a href="javascript:void(0)" data-toggle="modal" data-target="#exampleModal" data-whatever="@mdo" data-backdrop="false"> <i class="mdi mdi-backup-restore"></i> <span>系统修复</span> </a> </li>
          </ul>
          </li>
          </ul>
        </nav>
        <div class="sidebar-footer">
          <p class="copyright">Copyright &copy; 2026. <a target="_blank"
								   href="http://mf.mengnai.top/">梦奈云</a> All rights reserved.</p>
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
                <input type="radio" name="header_bg" value="default" id="header_bg_1"
												   checked>
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
                <input type="radio" name="sidebar_bg" value="default" id="sidebar_bg_1"
												   checked>
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
          <li class="dropdown dropdown-profile"> <a href="javascript:void(0)" data-toggle="dropdown" class="dropdown-toggle"> <img class="img-avatar img-avatar-48 m-r-10" src="<?=mnbt_asset_url('admin_logo/logo.head.png')?>"
										 alt="Admin" /> <span>超级管理员</span> </a>
            <ul class="dropdown-menu dropdown-menu-right">
              <li> <a class="multitabs dropdown-item" data-url="./set.php?gn=wz"
										   href="javascript:void(0)"> <i class="mdi mdi-account"></i>网站设置 </a> </li>
              <li> <a class="multitabs dropdown-item" data-url="./set.php?gn=gl"
										   href="javascript:void(0)"> <i class="mdi mdi-lock-outline"></i> 修改密码 </a> </li>
              <li> <a href="javascript:void(0)" class="dropdown-item" id="xfmr" data-toggle="modal" data-target="#exampleModal" data-whatever="@mdo" data-backdrop="false"> <i class="mdi mdi-backup-restore"></i> 系统修复 </a> </li>
              <li> <a class="dropdown-item" href="javascript:void(0)"> <i
											   class="mdi mdi-delete"></i> 清空缓存 </a> </li>
              <li class="dropdown-divider"></li>
              <li> <a class="dropdown-item" onclick="chteci();"> <i
											   class="mdi mdi-logout-variant"></i> 退出登录 </a> </li>
            </ul>
          </li>
        </ul>
      </nav>
    </header>
    <!--End 头部信息-->
    
    <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h6 class="modal-title" id="exampleModalChangeTitle">系统修复</h6>
            <?php if(!$mn_conf['xf']['qk']){
                  echo '<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>';
                  }?>
          </div>
          <div class="textmntti">
            <?php
                if($mn_conf['xf']['qk']){
                echo '<p><b>在本次更新后您必须进行一次修复！</b></p>
                <p><b>需要修复的功能已经默认选中！请勿更改，否则将继续进行本提示！现在您仅需要点击下方的确认修复按钮！</b></p>
                ';
                }?>
            <p>系统修复可以修复由于版本更新导致的数据变更和旧版本的数据不支持新版本的错误！也能删除新版本废除的旧文件！</p>
          </div>
          <div class="modal-body">
            <div class="form-group">
              <label class="col-xs-12" for="example-multiple-select">请选择要修复的(可多选)</label>
              <div class="col-xs-12">
                <select class="form-control" id="xzdcp" name="xzdcp" size="5" multiple>
                  <option value="1" <?php if(strpos($mn_conf['xf']['gne'],'1')!==false && $mn_conf['xf']['qk'])echo 'selected="selected"' ; ?>>同步主机ID(迁移宝塔后必须执行！也能修复主机的数据混乱)</option>
                  <option value="3" <?php if(strpos($mn_conf['xf']['gne'],'3')!==false && $mn_conf['xf']['qk'])echo 'selected="selected"' ; ?>>无用文件删除</option>
                  <!--<option value="4">数据库检测</option>-->
                </select>
              </div>
            </div>
            <br />
            <br />
            <br />
            <br />
          </div>
          <div class="modal-footer">
            <?php if(!$mn_conf['xf']['qk']){
                  echo '<button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>';
                  }?>
            <button type="button" class="btn btn-primary" onclick="xt_xf()">确认修复</button>
          </div>
        </div>
      </div>
    </div>
    
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
<script type="text/javascript" src="<?=mnbt_asset_url('js/bootstrap.min.js')?>"></script> 
<script type="text/javascript" src="<?=mnbt_asset_url('js/lyear-loading.js')?>"></script> 
<script type="text/javascript" src="<?=mnbt_asset_url('js/main.min.js')?>"></script> 
<script type="text/javascript" src="<?=mnbt_asset_url('js/fn-hs.js')?>"></script> 
<script type="text/javascript" src="<?=mnbt_asset_url('js/bootstrap-notify.min.js')?>"></script> 
<script type="text/javascript">
			<?php if($mn_conf['xf']['qk']){
			echo '$("#xfmr").trigger("click");';
			}?>
			function xt_xf() {
			var json="";
			var xzd=document.getElementById("xzdcp");;
			    for(i=0;i<xzd.length;i++){
			        if(xzd.options[i].selected){
			            if(json==""){
			            var json='"'+i+'":'+xzd[i].value;
			            var str=xzd[i].value;
			            }else{
			            var json=json+',"'+i+'":'+xzd[i].value;
			            var str=str+','+xzd[i].value;
			            }
			        }
			    }
			    if(json!=""){
			   var json="{"+json+"}";
			   }else{
			      msalert(3,'请选择要修复哪些功能',2000,"#exampleModal");
			      msloadingde();
			   return;
			   }
			msloading('正在修复中，请稍后...','text-info','text-info');  // 加载显示
			let data = {};
			data["gn"]="xtxf";
			data["xx"]=json;
			data["xe"]=str;
			$.post('./ajax.php', data, function (date) {    
			var jsoe= JSON.parse(date);    
			var qk= jsoe.code
			msalert(1,qk,2000);
			window.location.href="./"
			})
			
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
			
			//页面刷新
			$("#iframe_shuax").on('click',function(){
			var $thisTabs = parent.$('.mt-nav-bar .nav-tabs').find('a.active');
			var ifarid=$thisTabs.attr('data-id');
			//旋转动画
			$(this).css({animation: "rotate 0.5s linear 1",display: "inline-block"});
			setTimeout(function(){$("#iframe_shuax").removeAttr('style');},500);
			//页面loading
			$('#'+ifarid).contents().find('body').html('<link href="<?=mnbt_asset_url('css/index.loading.css')?>" rel="stylesheet"><div class="loading_upds"><div class="ctn-preloader"><div class="round_spinner"><div class="spinner"></div><img src="<?=mnbt_asset_url('admin_logo/logo.head.png')?>" alt=""></div</div></div>');
			
			$('#'+ifarid).attr('src', $('#'+ifarid).attr('src'));       //刷新子页面
			})
			
			//导航栏处点击导航加载
			$(".multitabs").on('click',function(){
			setTimeout(function() {
			var $thisTabs = parent.$('.mt-nav-bar .nav-tabs').find('a.active');
			var ifarid=$thisTabs.attr('data-id');
			var htmlfr=$('#'+ifarid).contents().find('body').html();
			if(htmlfr==''){
			//页面loading
			$('#'+ifarid).contents().find('body').html('<link href="<?=mnbt_asset_url('css/style.min.css')?>" rel="stylesheet"><link href="<?=mnbt_asset_url('css/bootstrap.min.css')?>" rel="stylesheet"><link href="<?=mnbt_asset_url('css/index.loading.css')?>" rel="stylesheet"><div class="loading_upds"><div class="ctn-preloader"><div class="round_spinner"><div class="spinner"></div><img src="<?=mnbt_asset_url('upload_logo/logo.head.png')?>" alt=""></div</div></div>');
			}
			}, 10);
			});
			
			
			setTimeout(function(){
			$('#multitabs_main_0').contents().find('body').html('<link href="<?=mnbt_asset_url('css/style.min.css')?>" rel="stylesheet"><link href="<?=mnbt_asset_url('css/bootstrap.min.css')?>" rel="stylesheet"><link href="<?=mnbt_asset_url('css/index.loading.css')?>" rel="stylesheet"><div class="loading_upds"><div class="ctn-preloader"><div class="round_spinner"><div class="spinner"></div><img src="<?=mnbt_asset_url('upload_logo/logo.head.png')?>?<?=$conf['auther']?>" alt=""></div</div></div>');
			},1)
		</script>
</body>
</html>
