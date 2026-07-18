<?php mnbt_theme_include('head'); ?>
<style>
.ly-dash { padding: 15px; }
.ly-stat-card .layui-card-header {
  display: flex; align-items: center; justify-content: space-between;
  padding: 12px 16px; font-size: 14px; font-weight: 500; color: #333;
}
.ly-stat-card .layui-card-header .ly-refresh {
  color: #999; cursor: pointer; font-size: 18px; transition: color .15s;
}
.ly-stat-card .layui-card-header .ly-refresh:hover { color: #1e9fff; }
.ly-stat-card .layui-card-body { padding: 16px; }

/* 用量卡片文本（保留 #web/#sql/#lls 三段式 DOM 契约：3 个直接子元素） */
.ly-stat-current { display: inline-block; font-size: 13px; color: #666; }
.ly-stat-max { display: inline-block; float: right; font-size: 13px; color: #999; }

/* 自定义进度条（与 layui 视觉一致，但无需 JS 渲染） */
.ly-progress { height: 16px; margin-top: 4px; background: #f0f2f5; border-radius: 8px; overflow: hidden; }
.ly-progress-bar {
  height: 100%; background: #1e9fff; border-radius: 8px;
  transition: width .3s ease; color: #fff; font-size: 11px;
  line-height: 16px; text-align: right; padding-right: 8px; box-sizing: border-box;
}

/* 信息表格 */
.ly-info-table { margin: 0; }
.ly-info-table tr td { padding: 9px 12px; font-size: 13px; border-bottom: 1px solid #f6f6f6; }
.ly-info-table tr:last-child td { border-bottom: 0; }
.ly-info-table td:first-child { color: #999; width: 90px; white-space: nowrap; }
.ly-info-table td:last-child { color: #333; }

/* PHP 版本下拉 */
.ly-dropdown { position: relative; display: inline-block; }
.ly-dropdown-menu {
  position: absolute; top: 100%; right: 0; min-width: 140px;
  background: #fff; border: 1px solid #e6e6e6; border-radius: 4px;
  box-shadow: 0 2px 12px rgba(0,0,0,0.08);
  padding: 5px 0; margin: 4px 0 0; list-style: none;
  display: none; z-index: 9999;
}
.ly-dropdown-menu.show { display: block; }
.ly-dropdown-menu .dropdown-item {
  display: block; padding: 6px 14px; color: #555; font-size: 13px;
  text-decoration: none; cursor: pointer; white-space: nowrap;
}
.ly-dropdown-menu .dropdown-item:hover { background: #f2f2f2; color: #1e9fff; text-decoration: none; }

/* 功能菜单网格 */
.ly-func-grid {
  display: grid; grid-template-columns: repeat(auto-fill, minmax(130px, 1fr)); gap: 12px;
}
.ly-func-item {
  display: flex; flex-direction: column; align-items: center; padding: 18px 8px;
  background: #fff; border: 1px solid #eee; border-radius: 4px;
  cursor: pointer; transition: all .15s; color: #555; text-decoration: none;
}
.ly-func-item:hover {
  border-color: #1e9fff; color: #1e9fff; text-decoration: none;
  box-shadow: 0 2px 10px rgba(30, 159, 255, 0.1);
}
.ly-func-item .mdi { font-size: 28px; margin-bottom: 6px; color: #999; transition: color .15s; }
.ly-func-item:hover .mdi { color: #1e9fff; }
.ly-func-item span { font-size: 12px; text-align: center; }

/* 卡片标题统一 */
.ly-card-title { font-size: 14px; font-weight: 500; color: #333; }
.ly-card-extra { font-size: 13px; color: #999; }

/* 多标签 iframe 中嵌入时去除外层 padding */
.layui-body .ly-dash { padding: 15px; }
</style>

<?php
if($yhc['mailuser'] == "" || $yhc['mailuser'] == null)
{
    echo("<script language='javascript'>'mail();'</script>");
}
?>

<!-- 图表 / 表格插件 -->
<script type="text/javascript" src="<?=mnbt_asset_url('js/Chart.min.js')?>"></script>
<script type="text/javascript" src="<?=mnbt_asset_url('js/jquery-confirm/jquery-confirm.min.js')?>"></script>
<script type="text/javascript" src="<?=mnbt_asset_url('js/bootstrap-table/bootstrap-table.min.js')?>"></script>
<script type="text/javascript" src="<?=mnbt_asset_url('js/bootstrap-table/locale/bootstrap-table-zh-CN.min.js')?>"></script>

<div class="layui-fluid ly-dash">
<div class="layui-row layui-col-space15">

  <!-- 左侧主区 -->
  <div class="layui-col-md8">

    <!-- 用量卡片 -->
    <div class="layui-row layui-col-space15">
      <div class="layui-col-md4">
        <div class="layui-card ly-stat-card">
          <div class="layui-card-header">
            <span>网页空间</span>
            <i class="mdi mdi-server-minus ly-refresh" onclick="sxxx()"></i>
          </div>
          <div class="layui-card-body" id="web">
            <div class="ly-stat-current">获取中</div>
            <div class="ly-stat-max">获取中</div>
            <div class="ly-progress"><div class="ly-progress-bar" style="width:1%;"><span>1</span></div></div>
          </div>
        </div>
      </div>
      <div class="layui-col-md4">
        <div class="layui-card ly-stat-card">
          <div class="layui-card-header">
            <span>数据库空间</span>
            <i class="mdi mdi-database ly-refresh" onclick="sxxx()"></i>
          </div>
          <div class="layui-card-body" id="sql">
            <div class="ly-stat-current">获取中</div>
            <div class="ly-stat-max">获取中</div>
            <div class="ly-progress"><div class="ly-progress-bar" style="width:1%;"><span>获取中</span></div></div>
          </div>
        </div>
      </div>
      <div class="layui-col-md4">
        <div class="layui-card ly-stat-card">
          <div class="layui-card-header">
            <span>本月流量</span>
            <i class="mdi mdi-signal ly-refresh" onclick="sxxx()"></i>
          </div>
          <div class="layui-card-body" id="lls">
            <div class="ly-stat-current">获取中</div>
            <div class="ly-stat-max">获取中</div>
            <div class="ly-progress"><div class="ly-progress-bar" style="width:1%;"><span>获取中</span></div></div>
          </div>
        </div>
      </div>
    </div>

    <!-- 流量趋势 -->
    <div class="layui-row layui-col-space15">
      <div class="layui-col-md12">
        <div class="layui-card">
          <div class="layui-card-header" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:6px;">
            <span class="ly-card-title">月度流量趋势</span>
            <span id="trendIndicator" class="ly-card-extra"></span>
          </div>
          <div class="layui-card-body">
            <canvas id="trafficChart" style="height:240px;width:100%"></canvas>
          </div>
        </div>
      </div>
    </div>

<?php
if (function_exists('mnbt_plugin_render_widgets_html')) {
	echo mnbt_plugin_render_widgets_html('user');
}
?>

    <!-- 功能菜单 -->
    <div class="layui-row layui-col-space15 d-sm-block">
      <div class="layui-col-md12">
        <div class="layui-card">
          <div class="layui-card-header"><span class="ly-card-title">功能菜单</span></div>
          <div class="layui-card-body">
            <div class="layui-tab layui-tab-brief" lay-filter="funcTabs">
              <ul class="layui-tab-title">
                <li class="layui-this">基本配置</li>
                <li>数据管理</li>
                <li>网站管理</li>
              </ul>
              <div class="layui-tab-content" style="padding-top:15px;">
                <div class="layui-tab-item layui-show">
                  <div class="ly-func-grid">
                    <a href="#!" class="ly-func-item js-create-tab" data-title="域名绑定" data-url="set.php?gn=url"><i class="mdi mdi-web"></i><span>域名绑定</span></a>
                    <a href="#!" class="ly-func-item phpvs-set"><i class="mdi mdi-xml"></i><span>PHP版本</span></a>
                    <a href="#!" class="ly-func-item js-create-tab" data-title="设置密码访问" data-url="set.php?gn=pass"><i class="mdi mdi-guy-fawkes-mask"></i><span>密码访问</span></a>
                    <a href="#!" class="ly-func-item js-create-tab" data-title="修改默认文档" data-url="set.php?gn=mrwd"><i class="mdi mdi-home"></i><span>默认文档</span></a>
                    <a href="#!" class="ly-func-item js-create-tab" data-title="设置运行目录" data-url="set.php?gn=yxml"><i class="mdi mdi-television-guide"></i><span>运行目录</span></a>
                    <a href="#!" class="ly-func-item js-create-tab" data-title="设置伪静态" data-url="set.php?gn=wjt"><i class="mdi mdi-link-variant"></i><span>伪静态</span></a>
                    <a href="#!" class="ly-func-item js-create-tab" data-title="SSL配置" data-url="set.php?gn=ssl"><i class="mdi mdi-key"></i><span>SSL配置</span></a>
                    <a href="#!" class="ly-func-item js-create-tab" data-title="防盗链配置" data-url="set.php?gn=fdl"><i class="mdi mdi-access-point-network"></i><span>防盗链</span></a>
                    <a href="#!" class="ly-func-item js-create-tab" data-title="Gzip配置" data-url="set.php?gn=gzip"><i class="mdi mdi-zip-box-outline"></i><span>Gzip配置</span></a>
                    <a href="#!" class="ly-func-item js-create-tab" data-title="缓存配置" data-url="set.php?gn=cache"><i class="mdi mdi-cached"></i><span>缓存配置</span></a>
                  </div>
                </div>
                <div class="layui-tab-item">
                  <div class="ly-func-grid">
                    <a href="#!" class="ly-func-item js-create-tab" data-title="在线文件管理" data-url="ftp.php"><i class="mdi mdi-folder-open"></i><span>在线文件管理</span></a>
                    <a href="mysql.php" target="_blank" class="ly-func-item"><i class="mdi mdi-database"></i><span>数据库管理面板</span></a>
                    <a href="#!" class="ly-func-item js-create-tab" data-title="数据库备份管理" data-url="sqlgl.php"><i class="mdi mdi-database-plus"></i><span>数据库备份管理</span></a>
                    <a href="#!" class="ly-func-item js-create-tab" data-title="数据库权限修改" data-url="set.php?gn=mysqlcz"><i class="mdi mdi-database-lock"></i><span>数据库权限修改</span></a>
                  </div>
                </div>
                <div class="layui-tab-item">
                  <div class="ly-func-grid">
                    <a href="#!" class="ly-func-item js-create-tab" data-title="一键部署" data-url="webgl.php?gn=yjbs"><i class="mdi mdi-webpack"></i><span>一键部署</span></a>
                    <a href="#!" class="ly-func-item js-create-tab" data-title="监控任务" data-url="monitor.php"><i class="mdi mdi-monitor-dashboard"></i><span>监控任务</span></a>
                    <a href="#!" class="ly-func-item js-create-tab" data-title="通知日志" data-url="notice.php"><i class="mdi mdi-bell-ring"></i><span>通知日志</span></a>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>

  <!-- 右侧信息区 -->
  <div class="layui-col-md4">
    <div class="layui-row layui-col-space15">

      <!-- 参数信息 -->
      <div class="layui-col-md12">
        <div class="layui-card">
          <div class="layui-card-header" style="display:flex;align-items:center;justify-content:space-between;">
            <span class="ly-card-title">参数信息</span>
            <div class="ly-dropdown">
              <button type="button" class="layui-btn layui-btn-primary layui-btn-sm phpvs" id="phpvsed" onclick="event.stopPropagation();$('#phpvsList').toggleClass('show');">PHP版本获取中</button>
              <ul class="ly-dropdown-menu phpvslist" id="phpvsList"></ul>
            </div>
          </div>
          <div class="layui-card-body" style="padding:0 10px;">
            <table class="ly-info-table">
              <tr><td>主机情况</td><td><span id="siteqk">正在获取中</span></td></tr>
              <tr><td>域名绑定数</td><td><span id="urlnum">正在获取中</span></td></tr>
              <tr><td>主机语言</td><td>PHP</td></tr>
            </table>
          </div>
        </div>
      </div>

      <!-- FTP 信息 -->
      <div class="layui-col-md12 ftpxx">
        <div class="layui-card">
          <div class="layui-card-header" style="display:flex;align-items:center;justify-content:space-between;">
            <span class="ly-card-title">FTP信息</span>
            <a class="layui-btn layui-btn-primary layui-btn-sm js-create-tab" data-title="在线文件管理" data-url="ftp.php">文件管理器</a>
          </div>
          <div class="layui-card-body" style="padding:0 10px;">
            <table class="ly-info-table">
              <tr><td>FTP地址</td><td><span id="ftphost">正在获取中</span></td></tr>
              <tr><td>FTP端口</td><td>21</td></tr>
              <tr><td>FTP账号</td><td><span id="ftpuser">正在获取中</span></td></tr>
              <tr><td>FTP密码</td><td><span>**********</span> <a href="#!" onclick="xymy(this,'ftp')" style="color:#999;"><i class="mdi mdi-eye"></i></a> <a href="#!" onclick="copypwd('ftp')" style="color:#999;"><i class="mdi mdi-content-copy"></i></a></td></tr>
            </table>
          </div>
        </div>
      </div>

      <!-- 数据库信息 -->
      <div class="layui-col-md12 basxx">
        <div class="layui-card">
          <div class="layui-card-header" style="display:flex;align-items:center;justify-content:space-between;">
            <span class="ly-card-title">数据库信息</span>
            <a href="mysql.php" target="_blank" class="layui-btn layui-btn-primary layui-btn-sm">phpMyAdmin</a>
          </div>
          <div class="layui-card-body" style="padding:0 10px;">
            <table class="ly-info-table">
              <tr><td>数据库地址</td><td>localhost</td></tr>
              <tr><td>数据库端口</td><td>3306</td></tr>
              <tr><td>数据库账号</td><td><span id="sqluser">正在获取中</span></td></tr>
              <tr><td>数据库密码</td><td><span>**********</span> <a href="#!" onclick="xymy(this,'sql')" style="color:#999;"><i class="mdi mdi-eye"></i></a> <a href="#!" onclick="copypwd('sql')" style="color:#999;"><i class="mdi mdi-content-copy"></i></a></td></tr>
            </table>
          </div>
        </div>
      </div>

    </div>
  </div>

</div>
</div>

<script>
// 渲染 layui tab
layui.use(['element'], function(){
  var element = layui.element;
  element.render('tab');
});

// 点击外部关闭 PHP 版本下拉
$(document).on('click', function(){
  $('#phpvsList').removeClass('show');
});

$(".phpvs-set").on('click',function(){
msloading('正在获取PHP版本信息');
let data = {};
data["gn"]="indexconf";
$.post('./ajax.php', data, function (date) {
var jsoe= JSON.parse(date);
var conf= jsoe.msg

var listphpvs='';
var bdfsr='';
$.each(conf['php'].list,function(e,v){
if(conf['php'].dq==v.version)bdfsr='selected';
listphpvs+='<option value="'+v.version+'" '+bdfsr+'>'+v.name+'</option>';
bdfsr='';
})
msloadingde();
    $.confirm({
        title: 'PHP版本切换',
        content: '<div class="form-group p-1 mb-0">' +
                 '  <label class="control-label">您主机的PHP版本</label>' +
                 '  <select class="form-control" id="phpvsmsg">' +listphpvs+
                 '</select></div>',
        type:'blue',
        backgroundDismiss: true,
        buttons: {
            sayMyName: {
                text: '确定切换',
                btnClass: 'btn-info',
                action: function() {
                    var input = this.$content.find('select#phpvsmsg');
                    if (!$.trim(input.val())) {
                        $.alert({
                            content: "PHP版本不能为空为空。",
                            type: 'red'
                        });
                        return false;
                    } else {
                        php_vs(input.val(),'PHP-'+input.val());
                    }
                }
            },
            '取消': function() {}
        }
    });
})
})

configup();
function configup(){
msloading('正在获取配置信息，请稍后...');
let data = {};
data["gn"]="indexconf";
$.post('./ajax.php', data, function (date) {
var jsoe= JSON.parse(date);
var conf= jsoe.msg

if(jsoe.qk==1){
/*网站公告*/
if(conf.gg!=null && conf.gg!=false){
msalertb(2,'公告',conf.gg,true,false);
}

/*用量*/
var web=$("#web").children();
var sql=$("#sql").children();
var lls=$("#lls").children();

let bfbweb=Math.round(((conf['web'].dq/conf['web'].max)*10000)/100);
if(bfbweb<1)bfbweb=1;
let bfbsql=Math.round(((conf['sql'].dq/conf['sql'].max)*10000)/100);
if(bfbsql<1)bfbsql=1;
let bfblls=Math.round(((conf['lls'].dq/(conf['lls'].max*1024*1024*1024))*10000)/100);
if(bfblls<1)bfblls=1;

var yltext='';
if(bfbweb>=100)yltext+='网页空间 ';
if(bfbsql>=100)yltext+='数据库空间 ';
if(bfblls>=100)yltext+='本月流量用量 ';
if(yltext!='')ylalert(yltext);

// #web/#sql/#lls 的 DOM 契约：3 个直接子元素
//  [0] 当前用量文本 [1] 最大值文本 [2] 进度容器（.ly-progress > .ly-progress-bar > span）
$(web[0]).html(Number(conf['web'].dq).toFixed(2)+'MB');
$(web[1]).html(conf['web'].max+'MB');
$($(web[2]).children()[0]).width(bfbweb+'%');
$($($(web[2]).children()[0]).children()[0]).html(bfbweb+'%');

$(sql[0]).html(Number(conf['sql'].dq).toFixed(2)+'MB');
$(sql[1]).html(conf['sql'].max+'MB');
$($(sql[2]).children()[0]).width(bfbsql+'%');
$($($(sql[2]).children()[0]).children()[0]).html(bfbsql+'%');

$(lls[0]).html(sizedwhs(conf['lls'].dq));
$(lls[1]).html(conf['lls'].max+'G');
$($(lls[2]).children()[0]).width(bfblls+'%');
$($($(lls[2]).children()[0]).children()[0]).html(bfblls+'%');

/*月度流量趋势图*/
try {
    var historyData = conf['lls'].history || {};
    var labels = [];
    var values = [];
    var months = Object.keys(historyData).sort();
    months.forEach(function(m) {
        labels.push(parseInt(m.split('-')[1]) + '月');
        values.push(+(historyData[m] / (1024*1024*1024)).toFixed(2));
    });
    var now = new Date();
    labels.push('本月');
    values.push(+(conf['lls'].dq / (1024*1024*1024)).toFixed(2));

    // 计算环比趋势
    var trendEl = document.getElementById('trendIndicator');
    if (trendEl && values.length >= 2) {
        var prev = values[values.length - 2];
        var curr = values[values.length - 1];
        if (prev > 0) {
            var pct = ((curr - prev) / prev * 100).toFixed(1);
            var arrow = curr >= prev ? '↑' : '↓';
            var color = curr >= prev ? '#ea5455' : '#28c76f';
            trendEl.innerHTML = '较上月 <span style="color:' + color + ';font-weight:bold">' + arrow + ' ' + Math.abs(curr - prev).toFixed(2) + 'GB (' + (pct >= 0 ? '+' : '') + pct + '%)</span>';
        } else {
            trendEl.innerHTML = '本月用量 <span style="color:#1e9fff;font-weight:bold">' + curr.toFixed(2) + ' GB</span>';
        }
    }

    var trendValues = values.slice();

    var canvas = document.getElementById('trafficChart');
    if (window.trafficChart && window.trafficChart.data && window.trafficChart.data.datasets) {
        window.trafficChart.data.labels = labels;
        window.trafficChart.data.datasets[0].data = values;
        window.trafficChart.data.datasets[1].data = trendValues;
        window.trafficChart.update();
    } else if (canvas && typeof Chart !== 'undefined') {
        var ctx = canvas.getContext('2d');
        window.trafficChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: '流量用量 (GB)',
                    data: values,
                    backgroundColor: 'rgba(30, 159, 255, 0.5)',
                    borderColor: 'rgba(30, 159, 255, 1)',
                    borderWidth: 1,
                    order: 2
                }, {
                    label: '趋势',
                    data: trendValues,
                    type: 'line',
                    borderColor: '#16b777',
                    backgroundColor: 'rgba(22, 183, 119, 0.1)',
                    borderWidth: 2,
                    pointBackgroundColor: '#16b777',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    fill: true,
                    tension: 0.4,
                    order: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: { display: true, text: 'GB' }
                    }
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                        labels: { boxWidth: 12, padding: 10 }
                    }
                }
            }
        });
    }
} catch(e) {}

/*PHP版本*/
$(".phpvs").html('PHP-'+conf['php'].dq);
var listphpvs='';
$.each(conf['php'].list,function(e,v){
listphpvs+='<li><a class="dropdown-item" href="#!" onclick="php_vs('+v.version+',`'+v.name+'`)">'+v.name+'</a></li>';
})
$(".phpvslist").html(listphpvs);

/*主机信息*/
let cons=conf['config'];
let urlbds=cons.url;
if(urlbds==0)urlbds='无限制';
let ftphost=cons.ftp['host'];
let ftpuser=cons.ftp.user;
let sqluser=cons.sql.user;
ftp_pass=cons.ftp.pass;
sql_pass=cons.sql.pass;
if(conf['qk']=='1'){var siteqk='<span class="layui-badge layui-bg-green">运行中</span>'}else if(conf['qk']=='0'){var siteqk='<span class="layui-badge">已暂停</span>'}else{var siteqk='<span class="layui-badge layui-bg-orange">未知</span>'}

$("#siteqk").html(siteqk);
$("#urlnum").html(urlbds);
$("#ftphost").html(ftphost);
$("#ftpuser").html(ftpuser);
$("#sqluser").html(sqluser);
if(conf.type=='1'){
$(".d-sm-block").removeClass('d-sm-block');
$(".ftpxx").addClass('d-none');
$(".basxx").addClass('d-none');
}
}else{
msalert(4,jsoe.code,6000);
}
msloadingde();  // 隐藏

})
}

function ylalert(data){
    $.confirm({
        title: '用量超出',
        content: '<div class="form-group p-1 mb-0">' +
                 '  <label class="control-label"><span>您的主机<b class="text-danger">'+data+
                 '</b>超出，您的主机已被系统暂停！解决方法如下：<br/><b>1.联系管理员升级配置。</b><br/>2.如果是网页用量超出则删除文件即可。<br/>3.如果是数据库空间超出则去数据库删除数据即可。<br/>4.如果是流量超出则可以等月初清零。<br/><b>在您完成以上的解决方案后请稍等最多10分钟的系统刷新时间再来查看用量，或者点击空间用量旁的图标立即重新计算用量！</b></span></label>' +
                 '</div>',
        type:'red',
        buttons: {
            sayMyName: {
                text: '我知道了',
                btnClass: 'btn-info'
            }
        }
    });
}

function copypwd(lx){
    if(lx=='ftp'){
    var text=ftp_pass;
    }else{
    var text=sql_pass;
    }
    const el = document.createElement('input')
    el.setAttribute('value', text);
    document.body.appendChild(el);
    el.select();
    document.execCommand('copy');
    document.body.removeChild(el);
    msalert(2,'复制成功！',1000);
}

function xymy(data,lx){
if(lx=='ftp'){
var value=ftp_pass;
}else{
var value=sql_pass;
}
var valdat=data.parentNode.childNodes[1];
if(valdat.innerHTML=='**********'){
$(valdat).html(value);
$(data.childNodes[1]).removeClass('mdi-eye');
$(data.childNodes[1]).addClass('mdi-eye-off');
}else{
$(valdat).html('**********');
$(data.childNodes[1]).removeClass('mdi-eye-off');
$(data.childNodes[1]).addClass('mdi-eye');
}
}

function php_vs(vio,phpbs) {
if(vio==00){
msalert(3,'请勿选择伪静态！',4000);
return;
}
msloading('正在加载中，请稍后...');  // 加载显示
let data = {};
data["gn"]="phpxg";
data["php"]=vio;
$.post('./ajax.php', data, function (date) {
var jsoe= JSON.parse(date);
var qk= jsoe.code

if(qk=='修改成功'){
msalert(1,'修改成功！',2000);
document.getElementById("phpvsed").innerHTML=phpbs;
msloadingde();  // 隐藏
}else{
msalert(3,qk,2000);
msloadingde();  // 隐藏
}
})
}

function sxxx(){
msloading('正在计算您的空间和流量用量中，请稍后...');  // 加载显示
let data = {};
data["gn"]="sxsyxx";
$.post('./ajax.php', data, function (date) {
msalert(1,'网页/数据库空间和流量用量刷新成功！',2000);
configup();
})
}

function sizedwhs(size){            //单位换算
	var units = 'B';
	if(size/1024>1){
		size = size/1024;
		units = 'KB';
	}
	if(size/1024>1){
		size = size/1024;
		units = 'MB';
	}
	if(size/1024>1){
		size = size/1024;
		units = 'GB';
	}
	return size.toFixed(2)+units;
}
</script>
</body>
</html>
