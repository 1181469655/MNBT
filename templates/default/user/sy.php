<?php mnbt_theme_include('head'); ?>
<style>
.byys {
    overflow: hidden;
}

</style>

<?php
if($yhc['mailuser'] == "" || $yhc['mailuser'] == null)
{
    echo("<script language='javascript'>'mail();'</script>");
}

?>

<!--图形插件-->
<script type="text/javascript" src="<?=mnbt_asset_url('js/Chart.min.js')?>"></script>
<!--对话框-->
<script type="text/javascript" src="<?=mnbt_asset_url('js/jquery-confirm/jquery-confirm.min.js')?>"></script>
<script type="text/javascript" src="<?=mnbt_asset_url('js/bootstrap-table/bootstrap-table.min.js')?>"></script>
<script type="text/javascript" src="<?=mnbt_asset_url('js/bootstrap-table/locale/bootstrap-table-zh-CN.min.js')?>"></script>

<div class="container-fluid col-15">
<div class="row">
<div class="col-lg-9">
<div class="row">
    
<div class="col-md-4">
  <div class="card byys">
    <div class="card-header bg-info">
      <h4>网页空间</h4>
      <ul class="card-actions">
        <li> <span class="mdi mdi-server-minus mdi-24px" onclick="sxxx()"></span> </li>
      </ul>
    </div>
    <div class="card-body" id="web">
        <div style="display: inline-block;">获取中</div>
        <div class="float-right" style="display: inline-block;">获取中</div>
      <div class="progress">
        <div class="progress-bar progress-bar-cyan progress-bar-striped" role="progressbar" style="width: 1%;" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"><span style="color:#000000;text-align:right;">
          1
          </span></div>
      </div>
    </div>
  </div>
</div>
<div class="col-md-4">
  <div class="card byys">
    <div class="card-header bg-info">
      <h4>数据库空间</h4>
      <ul class="card-actions">
        <li> <span class="mdi mdi-database mdi-24px" onclick="sxxx()"></span> </li>
      </ul>
    </div>
    <div class="card-body" id="sql">
        <div style="display: inline-block;">获取中</div>
        <div class="float-right" style="display: inline-block;">获取中</div>
      <div class="progress">
        <div class="progress-bar progress-bar-cyan progress-bar-striped" role="progressbar" style="width: 1%;" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"><span style="color:#000000;text-align:right;">
          获取中
          </span></div>
      </div>
    </div>
  </div>
</div>
<div class="col-md-4">
  <div class="card byys">
    <div class="card-header bg-info">
      <h4>本月流量</h4>
      <ul class="card-actions">
        <li> <span class="mdi mdi-signal mdi-24px" onclick="sxxx()"></span> </li>
      </ul>
    </div>
    <div class="card-body" id="lls">
        <div style="display: inline-block;">获取中</div>
        <div class="float-right" style="display: inline-block;">获取中</div>
      <div class="progress">
        <div class="progress-bar progress-bar-cyan progress-bar-striped" role="progressbar" style="width: 1%;" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"><span style="color:#000000;text-align:right;">
          获取中
          </span></div>
      </div>
    </div>
  </div>
</div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <header class="card-header">
                <div class="card-title">月度流量趋势</div>
                <ul class="card-actions">
                    <li><span id="trendIndicator" class="text-muted" style="font-size:14px"></span></li>
                </ul>
            </header>
            <div class="card-body">
                <canvas id="trafficChart" style="height:250px;width:100%"></canvas>
            </div>
        </div>
    </div>
</div>

<?php
if (function_exists('mnbt_plugin_render_widgets_html')) {
	echo mnbt_plugin_render_widgets_html('user');
}
?>

<div class="row d-sm-block">
    <div class="col-lg-12">
      <div class="card">
        <header class="card-header"><div class="card-title">功能菜单</div></header>
        <div class="card-body">
          
          
          <ul class="nav nav-tabs nav-fill">
            <li class="nav-item">
              <a class="nav-link active" data-toggle="tab" href="#config-sy" aria-selected="true">基本配置</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" data-toggle="tab" href="#date-sy" aria-selected="true">数据管理</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" data-toggle="tab" href="#site-sy" aria-selected="true">网站管理</a>
          </ul>
          <div class="tab-content">
            <div class="tab-pane fade show active" id="config-sy" >
                <div class="row">
                    <div class="col-6 col-md-3">
                        <a href="#!" class="card text-dark border js-create-tab" data-title="域名绑定" data-url="set.php?gn=url">
                            <i class="mdi mdi-web mdi-36px text-center"></i>
                            <span class="text-center">域名绑定</span>
                        </a>
                    </div>
                    <div class="col-6 col-md-3">
                        <a href="#!" class="card text-dark border phpvs-set">
                            <i class="mdi mdi-xml mdi-36px col text-center"></i>
                            <span class="col text-center">PHP版本</span>
                        </a>
                    </div>
                    <div class="col-6 col-md-3">
                        <a href="#!" class="card text-dark border js-create-tab" data-title="设置密码访问" data-url="set.php?gn=pass">
                            <i class="mdi mdi-guy-fawkes-mask mdi-36px col text-center"></i>
                            <span class="col text-center">密码访问</span>
                        </a>
                    </div>
                    <div class="col-6 col-md-3">
                        <a href="#!" class="card text-dark border js-create-tab" data-title="修改默认文档" data-url="set.php?gn=mrwd">
                            <i class="mdi mdi-home mdi-36px col text-center"></i>
                            <span class="col text-center">默认文档</span>
                        </a>
                    </div>
                    <div class="col-6 col-md-3">
                        <a href="#!" class="card text-dark border js-create-tab" data-title="设置运行目录" data-url="set.php?gn=yxml">
                            <i class="mdi mdi-television-guide mdi-36px col text-center"></i>
                            <span class="col text-center">运行目录</span>
                        </a>
                    </div>
                    <div class="col-6 col-md-3">
                        <a href="#!" class="card text-dark border js-create-tab" data-title="设置伪静态" data-url="set.php?gn=wjt">
                            <i class="mdi mdi-link-variant mdi-36px col text-center"></i>
                            <span class="col text-center">伪静态</span>
                        </a>
                    </div>
                    <div class="col-6 col-md-3">
                        <a href="#!" class="card text-dark border js-create-tab" data-title="SSL配置" data-url="set.php?gn=ssl">
                            <i class="mdi mdi-key mdi-36px col text-center"></i>
                            <span class="col text-center">SSL配置</span>
                        </a>
                    </div>
                    <div class="col-6 col-md-3">
                        <a href="#!" class="card text-dark border js-create-tab" data-title="防盗链配置" data-url="set.php?gn=fdl">
                            <i class="mdi mdi-access-point-network mdi-36px col text-center"></i>
                            <span class="col text-center">防盗链</span>
                        </a>
                    </div>
                    <div class="col-6 col-md-3">
                        <a href="#!" class="card text-dark border js-create-tab" data-title="Gzip配置" data-url="set.php?gn=gzip">
                            <i class="mdi mdi-zip-box-outline mdi-36px col text-center"></i>
                            <span class="col text-center">Gzip配置</span>
                        </a>
                    </div>
                    <div class="col-6 col-md-3">
                        <a href="#!" class="card text-dark border js-create-tab" data-title="缓存配置" data-url="set.php?gn=cache">
                            <i class="mdi mdi-cached mdi-36px col text-center"></i>
                            <span class="col text-center">缓存配置</span>
                        </a>
                    </div>
                </div>
            </div>
            <div class="tab-pane fade" id="date-sy" >
                <div class="row">
                    <div class="col-6 col-md-3">
                        <a href="#!" class="card text-dark border js-create-tab" data-title="在线文件管理" data-url="ftp.php">
                            <i class="mdi mdi-folder-open mdi-36px col text-center"></i>
                            <span class="col text-center">在线文件管理</span>
                        </a>
                    </div>
                    <div class="col-6 col-md-3">
                        <a href="mysql.php" target="_blank" class="card text-dark border">
                            <i class="mdi mdi-database mdi-36px col text-center"></i>
                            <span class="col text-center">数据库管理面板</span>
                        </a>
                    </div>
                    <div class="col-6 col-md-3">
                        <a href="#!" class="card text-dark border js-create-tab" data-title="数据库备份管理" data-url="sqlgl.php">
                            <i class="mdi mdi-database-plus mdi-36px col text-center"></i>
                            <span class="col text-center">数据库备份管理</span>
                        </a>
                    </div>
                    <div class="col-6 col-md-3">
                        <a href="#!" class="card text-dark border js-create-tab" data-title="数据库权限修改" data-url="set.php?gn=mysqlcz">
                            <i class="mdi mdi-database-lock mdi-36px col text-center"></i>
                            <span class="col text-center">数据库权限修改</span>
                        </a>
                    </div>
                </div>
            </div>
            <div class="tab-pane fade" id="site-sy" >
                <div class="row">
                    <div class="col-6 col-md-3">
                        <a href="#!" class="card text-dark border js-create-tab" data-title="一键部署" data-url="webgl.php?gn=yjbs">
                            <i class="mdi mdi-webpack mdi-36px col text-center"></i>
                            <span class="col text-center">一键部署</span>
                        </a>
                    </div>
                    <div class="col-6 col-md-3">
                        <a href="#!" class="card text-dark border js-create-tab" data-title="监控任务" data-url="monitor.php">
                            <i class="mdi mdi-monitor-dashboard mdi-36px col text-center"></i>
                            <span class="col text-center">监控任务</span>
                        </a>
                    </div>
                    <div class="col-6 col-md-3">
                        <a href="#!" class="card text-dark border js-create-tab" data-title="通知日志" data-url="notice.php">
                            <i class="mdi mdi-bell-ring mdi-36px col text-center"></i>
                            <span class="col text-center">通知日志</span>
                        </a>
                    </div>
                </div>
            </div>
            
          </div>
          
        </div>
      </div>
    </div>
        </div>
</div>

<div class="col-lg-3">
<div class="row">

<div class="col-lg-12 col-md-4">
  <div class="card">
    <div class="card-header">
      <h4>参数信息</h4>
      <ul class="card-actions">
      </ul>
        <button type="button" class="btn btn-cyan btn-xs dropdown-toggle phpvs" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" id="phpvsed">
        PHP版本获取中<span class="caret"></span>
        </button>
        <ul class="dropdown-menu phpvslist">
        </ul>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-bordered">
          <tr>
            <td>主机情况：
              <span id="siteqk">正在获取中</span></td>
          </tr>
            <td>域名绑定数：
              <span id="urlnum">正在获取中</span></td>
          </tr>
          <tr>
            <td>主机语言：
              PHP</td>
          </tr>
        </table>
      </div>
    </div>
    </div>
  </div>

<div class="col-lg-12 col-md-4 ftpxx">
  <div class="card">
    <div class="card-header">
      <h4>FTP信息</h4>
      <ul class="card-actions">
      </ul>
    <a class="btn btn-purple btn-xs js-create-tab" data-title="在线文件管理" data-url="ftp.php">在线文件管理器</a>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-bordered">
          <tr>
            <td>FTP地址：
              <span id="ftphost">正在获取中</span></td>
          </tr>
          <tr>
            <td>FTP端口：
              21</td>
          </tr>
          <tr>
            <td>FTP账号：
              <span id="ftpuser">正在获取中</span></td>
          </tr>
          <tr>
            <td id="ftppass">FTP密码：
              <span>**********</span>
              <a href="#!" onclick="xymy(this,'ftp')"> <i class="mdi mdi-eye h6"></i></a>
              <a href="#!" class="text-purple" onclick="copypwd('ftp')"> <i class="mdi mdi-content-copy h6"></i></a>
              </td>
          </tr>
        </table>
      </div>
    </div>
  </div>
</div>
<div class="col-lg-12 col-md-4 basxx">
<div class="card">
  <div class="card-header">
    <h4>数据库信息</h4>
    <ul class="card-actions">
    </ul>
    <a href="mysql.php" target="_blank" class="btn btn-info btn-xs">phpMyAdmin</a>
  </div>
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-bordered">
        <tr>
          <td>数据库地址：localhost</td>
        </tr>
        <tr>
          <td>数据库端口：3306</td>
        </tr>
        <tr>
          <td>数据库账号：
            <span id="sqluser">正在获取中</span></td>
        </tr>
        <tr>
          <td>数据库密码：
              <span>**********</span>
              <a href="#!" onclick="xymy(this,'sql')"> <i class="mdi mdi-eye h6"></i></a>
              <a href="#!" class="text-purple" onclick="copypwd('sql')"> <i class="mdi mdi-content-copy h6"></i></a>
              </td>
        </tr>
      </table>
    </div>
  </div>
        </div>
</div>
</div>
</div>
</div>
<script>

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
var web=$("#web").children();       //获取网页空间子标签
var sql=$("#sql").children();       //获取数据库空间子标签
var lls=$("#lls").children();       //获取流量使用子标签

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
            trendEl.innerHTML = '本月用量 <span style="color:#7367f0;font-weight:bold">' + curr.toFixed(2) + ' GB</span>';
        }
    }
    
    // 计算趋势线数据（简单移动平均或直接用原值作为折线）
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
                    backgroundColor: 'rgba(23, 162, 184, 0.6)',
                    borderColor: 'rgba(23, 162, 184, 1)',
                    borderWidth: 1,
                    order: 2
                }, {
                    label: '趋势',
                    data: trendValues,
                    type: 'line',
                    borderColor: '#7367f0',
                    backgroundColor: 'rgba(115, 103, 240, 0.1)',
                    borderWidth: 2,
                    pointBackgroundColor: '#7367f0',
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
if(conf['qk']=='1'){var siteqk='<span class="badge badge-success"><b>运行中</b></span>'}else if(conf['qk']=='0'){var siteqk='<span class="badge badge-danger"><b>已暂停</b></span>'}else{var siteqk='<span class="badge badge-warning"><b>未知</b></span>'}

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
