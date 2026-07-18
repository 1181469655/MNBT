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
<!-- 公共资源（保留 default 栈） -->
<link rel="stylesheet" type="text/css" href="<?=mnbt_asset_url('css/materialdesignicons.min.css')?>">
<link rel="stylesheet" type="text/css" href="<?=mnbt_asset_url('css/bootstrap.min.css')?>">
<link rel="stylesheet" type="text/css" href="<?=mnbt_asset_url('js/bootstrap-multitabs/multitabs.min.css')?>">
<link rel="stylesheet" type="text/css" href="<?=mnbt_asset_url('css/animate.min.css')?>">
<link rel="stylesheet" type="text/css" href="<?=mnbt_asset_url('css/style.min.css')?>">
<script type="text/javascript" src="<?=mnbt_asset_url('js/fn-hs.js')?>"></script>
<!-- Layui -->
<link rel="stylesheet" href="https://unpkg.com/layui@2.9.8/dist/css/layui.css">
<link href="<?=mnbt_theme_asset('theme.css', 'admin')?>" rel="stylesheet">
<style>
.textmntti { margin: 2px 10px; }
@keyframes ly-rotate { 100% { -webkit-transform: rotate(360deg); } }
#iframe_shuax { cursor: pointer; }
.modal-content { box-shadow: 0 0 10px -1px; }
</style>
</head>

<body class="layui-layout-body ly-app">
<div class="layui-layout layui-layout-admin ly-layout">

  <!-- 左侧导航 -->
  <div class="layui-side ly-side">
    <div class="ly-side-logo">
      <a href="index.php"><img src="<?=mnbt_asset_url('admin_logo/logo.index.png')?>" title="MN_logo" alt="MN_logo" /></a>
    </div>
    <div class="layui-side-scroll ly-side-scroll">
      <ul class="layui-nav layui-nav-tree ly-nav" lay-filter="ly-nav">
        <li class="layui-nav-item layui-this">
          <a href="sy.php" class="multitabs"><i class="mdi mdi-home"></i><cite>后台首页</cite></a>
        </li>
        <li class="layui-nav-item">
          <a href="javascript:;"><i class="mdi mdi-console"></i><cite>系统管理</cite></a>
          <dl class="layui-nav-child">
            <dd><a class="multitabs" href="set.php?gn=wz">网站设置</a></dd>
            <dd><a class="multitabs" href="set.php?gn=gl">管理设置</a></dd>
            <dd><a class="multitabs" href="set.php?gn=api">API设置</a></dd>
            <dd><a class="multitabs" href="pay_settings.php">支付设置</a></dd>
            <dd><a class="multitabs" href="set.php?gn=mail">邮箱设置</a></dd>
            <dd><a class="multitabs" href="set.php?gn=jk">监控主机删除设置</a></dd>
            <dd><a class="multitabs" href="set.php?gn=kzmb">控制面板管理</a></dd>
            <dd><a class="multitabs" href="set.php?gn=theme">前端模板</a></dd>
            <dd><a class="multitabs" href="plugin.php">插件管理</a></dd>
            <dd><a class="multitabs" href="tutorial.php">教程及监控</a></dd>
            <dd><a class="multitabs" href="update.php">系统更新</a></dd>
            <dd><a class="multitabs" href="list.php?gn=log">操作日志</a></dd>
          </dl>
        </li>
<?php
if (function_exists('mnbt_plugin_render_menu_admin_html')) {
  echo mnbt_plugin_render_menu_admin_html();
}
?>
        <li class="layui-nav-item">
          <a href="javascript:;"><i class="mdi mdi-domain"></i><cite>二级域名</cite></a>
          <dl class="layui-nav-child">
            <dd><a class="multitabs" href="list.php?gn=ym">域名列表</a></dd>
            <dd><a class="multitabs" href="add.php?gn=ym">添加域名</a></dd>
          </dl>
        </li>
        <li class="layui-nav-item">
          <a href="javascript:;"><i class="mdi mdi-server"></i><cite>宝塔管理</cite></a>
          <dl class="layui-nav-child">
            <dd><a class="multitabs" href="list.php?gn=bt">宝塔列表</a></dd>
            <dd><a class="multitabs" href="add.php?gn=bt">添加宝塔</a></dd>
          </dl>
        </li>
        <li class="layui-nav-item">
          <a href="javascript:;"><i class="mdi mdi-server-network"></i><cite>节点管理</cite></a>
          <dl class="layui-nav-child">
            <dd><a class="multitabs" href="node.php">节点列表</a></dd>
            <dd><a class="multitabs" href="node.php?tab=scan">违禁词扫描</a></dd>
          </dl>
        </li>
        <li class="layui-nav-item">
          <a href="javascript:;"><i class="mdi mdi-locker-multiple"></i><cite>主机管理</cite></a>
          <dl class="layui-nav-child">
            <dd><a class="multitabs" href="list.php?gn=zj">主机列表</a></dd>
            <dd><a class="multitabs" href="add.php?gn=zj">添加主机</a></dd>
          </dl>
        </li>
        <li class="layui-nav-item">
          <a href="javascript:;"><i class="mdi mdi-webpack"></i><cite>一键部署</cite></a>
          <dl class="layui-nav-child">
            <dd><a class="multitabs" href="list.php?gn=dd">订单列表</a></dd>
            <dd><a class="multitabs" href="list.php?gn=cx">程序列表</a></dd>
            <dd><a class="multitabs" href="add.php?gn=cx">添加程序</a></dd>
            <dd><a class="multitabs" href="add.php?gn=dr">导入程序</a></dd>
          </dl>
        </li>
        <li class="layui-nav-item">
          <a href="javascript:void(0)" data-toggle="modal" data-target="#exampleModal" data-whatever="@mdo" data-backdrop="false"><i class="mdi mdi-backup-restore"></i><cite>系统修复</cite></a>
        </li>
      </ul>
      <div class="ly-side-footer">Copyright &copy; 2026. <a target="_blank" href="http://mf.mengnai.top/">梦奈云</a> All rights reserved.</div>
    </div>
  </div>
  <!--End 左侧导航-->

  <!-- 头部信息 -->
  <div class="layui-header ly-header">
    <div class="ly-header-left">
      <a href="javascript:;" class="ly-aside-toggler" id="ly-aside-toggler"><span></span><span></span><span></span></a>
      <i class="ml-2 mdi mdi-refresh mdi-18px" id="iframe_shuax" title="刷新当前标签"></i>
    </div>
    <ul class="ly-header-right">
      <li class="dropdown dropdown-profile">
        <a href="javascript:void(0)" data-toggle="dropdown" class="dropdown-toggle">
          <img class="img-avatar img-avatar-48 m-r-10" src="<?=mnbt_asset_url('admin_logo/logo.head.png')?>" alt="Admin" />
          <span>超级管理员</span>
        </a>
        <ul class="dropdown-menu dropdown-menu-right">
          <li><a class="multitabs dropdown-item" data-url="./set.php?gn=wz" href="javascript:void(0)"><i class="mdi mdi-account"></i> 网站设置</a></li>
          <li><a class="multitabs dropdown-item" data-url="./set.php?gn=gl" href="javascript:void(0)"><i class="mdi mdi-lock-outline"></i> 修改密码</a></li>
          <li><a href="javascript:void(0)" class="dropdown-item" id="xfmr" data-toggle="modal" data-target="#exampleModal" data-whatever="@mdo" data-backdrop="false"><i class="mdi mdi-backup-restore"></i> 系统修复</a></li>
          <li><a class="dropdown-item" href="javascript:void(0)"><i class="mdi mdi-delete"></i> 清空缓存</a></li>
          <li class="dropdown-divider"></li>
          <li><a class="dropdown-item" onclick="chteci();"><i class="mdi mdi-logout-variant"></i> 退出登录</a></li>
        </ul>
      </li>
    </ul>
  </div>
  <!--End 头部信息-->

  <!-- 系统修复弹窗 -->
  <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h6 class="modal-title" id="exampleModalChangeTitle">系统修复</h6>
          <?php if(!$mn_conf['xf']['qk']){
            echo '<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>';
          } ?>
        </div>
        <div class="textmntti">
          <?php
          if($mn_conf['xf']['qk']){
            echo '<p><b>在本次更新后您必须进行一次修复！</b></p>
            <p><b>需要修复的功能已经默认选中！请勿更改，否则将继续进行本提示！现在您仅需要点击下方的确认修复按钮！</b></p>';
          } ?>
          <p>系统修复可以修复由于版本更新导致的数据变更和旧版本的数据不支持新版本的错误！也能删除新版本废除的旧文件！</p>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label class="col-xs-12" for="example-multiple-select">请选择要修复的(可多选)</label>
            <div class="col-xs-12">
              <select class="form-control" id="xzdcp" name="xzdcp" size="5" multiple>
                <option value="1" <?php if(strpos($mn_conf['xf']['gne'],'1')!==false && $mn_conf['xf']['qk'])echo 'selected="selected"' ; ?>>同步主机ID(迁移宝塔后必须执行！也能修复主机的数据混乱)</option>
                <option value="3" <?php if(strpos($mn_conf['xf']['gne'],'3')!==false && $mn_conf['xf']['qk'])echo 'selected="selected"' ; ?>>无用文件删除</option>
              </select>
            </div>
          </div>
          <br /><br /><br /><br />
        </div>
        <div class="modal-footer">
          <?php if(!$mn_conf['xf']['qk']){
            echo '<button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>';
          } ?>
          <button type="button" class="btn btn-primary" onclick="xt_xf()">确认修复</button>
        </div>
      </div>
    </div>
  </div>

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
<?php if($mn_conf['xf']['qk']){ echo '$("#xfmr").trigger("click");'; } ?>

function xt_xf() {
  var json = "";
  var xzd = document.getElementById("xzdcp");
  for (i = 0; i < xzd.length; i++) {
    if (xzd.options[i].selected) {
      if (json == "") {
        var json = '"' + i + '":' + xzd[i].value;
        var str = xzd[i].value;
      } else {
        var json = json + ',"' + i + '":' + xzd[i].value;
        var str = str + ',' + xzd[i].value;
      }
    }
  }
  if (json != "") {
    var json = "{" + json + "}";
  } else {
    msalert(3, '请选择要修复哪些功能', 2000, "#exampleModal");
    msloadingde();
    return;
  }
  msloading('正在修复中，请稍后...', 'text-info', 'text-info');
  let data = {};
  data["gn"] = "xtxf";
  data["xx"] = json;
  data["xe"] = str;
  $.post('./ajax.php', data, function (date) {
    var jsoe = JSON.parse(date);
    var qk = jsoe.code;
    msalert(1, qk, 2000);
    window.location.href = "./";
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

// 页面刷新
$("#iframe_shuax").on('click', function () {
  var $thisTabs = parent.$('.mt-nav-bar .nav-tabs').find('a.active');
  var ifarid = $thisTabs.attr('data-id');
  $(this).css({ animation: "ly-rotate 0.5s linear 1", display: "inline-block" });
  setTimeout(function () { $("#iframe_shuax").removeAttr('style'); }, 500);
  $('#' + ifarid).contents().find('body').html('<link href="<?=mnbt_asset_url('css/index.loading.css')?>" rel="stylesheet"><div class="loading_upds"><div class="ctn-preloader"><div class="round_spinner"><div class="spinner"></div><img src="<?=mnbt_asset_url('admin_logo/logo.head.png')?>" alt=""></div></div></div>');
  $('#' + ifarid).attr('src', $('#' + ifarid).attr('src'));
});

// 导航栏处点击导航加载
$(".multitabs").on('click', function () {
  setTimeout(function () {
    var $thisTabs = parent.$('.mt-nav-bar .nav-tabs').find('a.active');
    var ifarid = $thisTabs.attr('data-id');
    var htmlfr = $('#' + ifarid).contents().find('body').html();
    if (htmlfr == '') {
      $('#' + ifarid).contents().find('body').html('<link href="<?=mnbt_asset_url('css/style.min.css')?>" rel="stylesheet"><link href="<?=mnbt_asset_url('css/bootstrap.min.css')?>" rel="stylesheet"><link href="<?=mnbt_asset_url('css/index.loading.css')?>" rel="stylesheet"><div class="loading_upds"><div class="ctn-preloader"><div class="round_spinner"><div class="spinner"></div><img src="<?=mnbt_asset_url('upload_logo/logo.head.png')?>" alt=""></div></div></div>');
    }
  }, 10);
});

setTimeout(function () {
  $('#multitabs_main_0').contents().find('body').html('<link href="<?=mnbt_asset_url('css/style.min.css')?>" rel="stylesheet"><link href="<?=mnbt_asset_url('css/bootstrap.min.css')?>" rel="stylesheet"><link href="<?=mnbt_asset_url('css/index.loading.css')?>" rel="stylesheet"><div class="loading_upds"><div class="ctn-preloader"><div class="round_spinner"><div class="spinner"></div><img src="<?=mnbt_asset_url('upload_logo/logo.head.png')?>?<?=$conf['auther']?>" alt=""></div></div></div>');
}, 1);

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
