<?php mnbt_theme_include('head'); ?>
<script type="text/javascript" src="<?=mnbt_asset_url('js/Chart.min.js')?>"></script>
<script type="text/javascript" src="<?=mnbt_asset_url('js/jquery-confirm/jquery-confirm.min.js')?>"></script>
<div class="west-page">
  <div class="west-alert"><i class="west-status-dot"></i> 主机数据由系统实时读取，修改配置后如未立即生效，可刷新用量或稍后重新进入当前页面。</div>
  <div class="west-banner"><span>MNBT 虚拟主机</span><strong>自助管理中心</strong></div>
  <section class="west-panel west-host-panel">
    <div class="west-panel-title">主机信息</div>
    <div class="west-host-body">
      <div class="west-host-status"><span class="west-status-light running"></span><strong id="siteqk">运行中</strong><span>主机有效期</span><em><?= htmlspecialchars($yhc['endtime'] ?? '以系统数据为准', ENT_QUOTES, 'UTF-8') ?></em></div>
      <div class="west-host-info">
        <dl><dt>主机账号：</dt><dd><?= htmlspecialchars($user ?? '', ENT_QUOTES, 'UTF-8') ?></dd></dl>
        <dl><dt>空间使用：</dt><dd><span id="web-used">获取中</span> / <span id="web-max">获取中</span>，<a href="#!" onclick="sxxx()">刷新空间</a></dd></dl>
        <dl><dt>当前状态：</dt><dd><span id="siteqkText">正在获取中</span></dd></dl>
        <dl><dt>所在机房：</dt><dd>自动分配节点 <a class="phpvs-set" href="#!">切换PHP</a></dd></dl>
        <dl><dt>绑定域名数：</dt><dd><span id="urlnum">获取中</span></dd></dl>
        <dl><dt>SSL配置状态：</dt><dd><a class="js-create-tab" data-title="SSL配置" data-url="set.php?gn=ssl" href="#!">配置SSL</a></dd></dl>
      </div>
      <div class="west-host-info">
        <dl><dt>FTP账号：</dt><dd><span id="ftpuser">获取中</span> <a href="#!" onclick="copypwd('ftp')">复制FTP登录</a></dd></dl>
        <dl><dt>FTP地址：</dt><dd><span id="ftphost">获取中</span></dd></dl>
        <dl><dt>数据库账号：</dt><dd><span id="sqluser">获取中</span></dd></dl>
        <dl><dt>数据库地址：</dt><dd>localhost <a href="mysql.php" target="_blank">上传数据库</a></dd></dl>
        <dl><dt>业务备案：</dt><dd><a class="js-create-tab" data-title="通知日志" data-url="notice.php" href="#!">点击查看</a></dd></dl>
        <dl><dt>上月流量：</dt><dd><span id="lls-used">获取中</span> / <span id="lls-max">获取中</span></dd></dl>
      </div>
    </div>
  </section>
  <section class="west-panel west-chart-panel">
    <div class="west-panel-title">资源使用情况</div>
    <div class="west-resource-layout">
      <div class="west-resource-meters">
        <div class="west-meter-row"><span>网页空间</span><div id="web-progressbar"><div class="progress-label">1%</div></div></div>
        <div class="west-meter-row"><span>数据库空间</span><div id="sql-progressbar"><div class="progress-label">1%</div></div></div>
        <div class="west-meter-row"><span>本月流量</span><div id="lls-progressbar"><div class="progress-label">1%</div></div></div>
      </div>
      <div class="west-resource-chart"><canvas id="trafficChart"></canvas></div>
    </div>
  </section>
  <section class="west-panel">
    <div class="west-panel-title">网站基本配置</div>
    <div class="west-func-grid">
      <a href="#!" class="func-item js-create-tab" data-title="域名绑定" data-url="set.php?gn=url"><i class="mdi mdi-web"></i><span>域名绑定</span></a>
      <a href="#!" class="func-item js-create-tab" data-title="修改密码" data-url="set.php?gn=xgpass"><i class="mdi mdi-key"></i><span>修改密码</span></a>
      <a href="#!" class="func-item js-create-tab" data-title="默认首页" data-url="set.php?gn=mrwd"><i class="mdi mdi-home"></i><span>默认文档</span></a>
      <a href="#!" class="func-item phpvs-set"><i class="mdi mdi-xml"></i><span>PHP版本</span></a>
      <a href="#!" class="func-item js-create-tab" data-title="运行目录" data-url="set.php?gn=yxml"><i class="mdi mdi-television-guide"></i><span>运行目录</span></a>
      <a href="#!" class="func-item js-create-tab" data-title="伪静态" data-url="set.php?gn=wjt"><i class="mdi mdi-link-variant"></i><span>伪静态</span></a>
      <a href="#!" class="func-item js-create-tab" data-title="SSL配置" data-url="set.php?gn=ssl"><i class="mdi mdi-key"></i><span>SSL配置</span></a>
      <a href="#!" class="func-item js-create-tab" data-title="防盗链" data-url="set.php?gn=fdl"><i class="mdi mdi-access-point-network"></i><span>防盗链</span></a>
      <a href="#!" class="func-item js-create-tab" data-title="Gzip配置" data-url="set.php?gn=gzip"><i class="mdi mdi-zip-box-outline"></i><span>Gzip配置</span></a>
      <a href="#!" class="func-item js-create-tab" data-title="缓存配置" data-url="set.php?gn=cache"><i class="mdi mdi-cached"></i><span>缓存配置</span></a>
      <a href="#!" class="func-item js-create-tab" data-title="密码访问" data-url="set.php?gn=pass"><i class="mdi mdi-guy-fawkes-mask"></i><span>密码访问</span></a>
    </div>
  </section>
  <section class="west-panel">
    <div class="west-panel-title">数据管理</div>
    <div class="west-func-grid west-func-grid-sm">
      <a href="#!" class="func-item js-create-tab" data-title="在线文件管理" data-url="ftp.php"><i class="mdi mdi-folder-open"></i><span>在线文件管理</span></a>
      <a href="mysql.php" target="_blank" class="func-item"><i class="mdi mdi-database"></i><span>数据库管理面板</span></a>
      <a href="#!" class="func-item js-create-tab" data-title="数据库备份管理" data-url="sqlgl.php"><i class="mdi mdi-database-plus"></i><span>数据库备份管理</span></a>
      <a href="#!" class="func-item js-create-tab" data-title="数据库权限修改" data-url="set.php?gn=mysqlcz"><i class="mdi mdi-database-lock"></i><span>数据库权限修改</span></a>
    </div>
  </section>
  <section class="west-panel">
    <div class="west-panel-title">监控与服务</div>
    <div class="west-func-grid west-func-grid-sm">
      <a href="#!" class="func-item js-create-tab" data-title="一键部署" data-url="webgl.php?gn=yjbs"><i class="mdi mdi-webpack"></i><span>一键部署</span></a>
      <a href="#!" class="func-item js-create-tab" data-title="监控任务" data-url="monitor.php"><i class="mdi mdi-monitor-dashboard"></i><span>监控任务</span></a>
      <a href="#!" class="func-item js-create-tab" data-title="通知日志" data-url="notice.php"><i class="mdi mdi-bell-ring"></i><span>通知日志</span></a>
      <a href="#!" class="func-item js-create-tab" data-title="站点统计" data-url="site_stats.php"><i class="mdi mdi-server-minus"></i><span>站点统计</span></a>
      <a href="#!" class="func-item js-create-tab" data-title="监控日志" data-url="monitor_log.php"><i class="func-icon">志</i><span>监控日志</span></a>
      <a href="#!" class="func-item js-create-tab" data-title="流量趋势" data-url="sy.php"><i class="mdi mdi-signal"></i><span>流量分析</span></a>
    </div>
  </section>
</div>
<script>
$(function() { $("#web-progressbar, #sql-progressbar, #lls-progressbar").progressbar({ value: 1 }); });
var ftp_pass = '';
var sql_pass = '';
$(document).on('click', '.phpvs-set', function(){
    msloading('正在获取PHP版本信息');
    let data = {};
    data["gn"]="indexconf";
    $.post('./ajax.php', data, function (date) {
        var jsoe= JSON.parse(date);
        var conf= jsoe.msg;
        var listphpvs='';
        var bdfsr='';
        $.each(conf['php'].list,function(e,v){
            if(conf['php'].dq==v.version) bdfsr='selected';
            listphpvs+='<option value="'+v.version+'" '+bdfsr+'>'+v.name+'</option>';
            bdfsr='';
        });
        msloadingde();
        $.confirm({ title: 'PHP版本切换', content: '<div class="form-group p-1 mb-0"><label class="control-label">您主机的PHP版本</label><select class="form-control" id="phpvsmsg">' + listphpvs + '</select></div>', type:'blue', backgroundDismiss: true, buttons: { sayMyName: { text: '确定切换', btnClass: 'btn-info', action: function() { var input = this.$content.find('select#phpvsmsg'); if (!$.trim(input.val())) { $.alert({ content: "PHP版本不能为空。", type: 'red' }); return false; } else { php_vs(input.val(),'PHP-'+input.val()); } } }, '取消': function() {} } });
    });
});
configup();
function configup(){
    msloading('正在获取配置信息，请稍后...');
    let data = {};
    data["gn"]="indexconf";
    $.post('./ajax.php', data, function (date) {
        var jsoe= JSON.parse(date);
        var conf= jsoe.msg;
        if(jsoe.qk==1){
            let bfbweb=Math.round(((conf['web'].dq/conf['web'].max)*10000)/100);
            let bfbsql=Math.round(((conf['sql'].dq/conf['sql'].max)*10000)/100);
            let bfblls=Math.round(((conf['lls'].dq/(conf['lls'].max*1024*1024*1024))*10000)/100);
            if(bfbweb<1) bfbweb=1;
            if(bfbsql<1) bfbsql=1;
            if(bfblls<1) bfblls=1;
            $('#web-used').text(Number(conf['web'].dq).toFixed(2)+'M');
            $('#web-max').text(conf['web'].max+'M');
            $('#lls-used').text(sizedwhs(conf['lls'].dq));
            $('#lls-max').text(conf['lls'].max+'G');
            $('#web-progressbar').progressbar('option', 'value', bfbweb).find('.progress-label').text(bfbweb+'%');
            $('#sql-progressbar').progressbar('option', 'value', bfbsql).find('.progress-label').text(bfbsql+'%');
            $('#lls-progressbar').progressbar('option', 'value', bfblls).find('.progress-label').text(bfblls+'%');
            let cons=conf['config'];
            let urlbds=cons.url;
            if(urlbds==0) urlbds='无限制';
            ftp_pass=cons.ftp.pass;
            sql_pass=cons.sql.pass;
            $('#urlnum').text(urlbds);
            $('#ftphost').text(cons.ftp.host);
            $('#ftpuser').text(cons.ftp.user);
            $('#sqluser').text(cons.sql.user);
            if(conf['qk']=='1'){ $('#siteqk').html('(运行中)'); $('#siteqkText').html('<span class="text-success">运行中</span>'); $('.west-status-light').removeClass('paused'); }
            else if(conf['qk']=='0'){ $('#siteqk').html('(已暂停)'); $('#siteqkText').html('<span class="text-danger">已暂停</span>'); $('.west-status-light').addClass('paused'); }
            else{ $('#siteqk').html('(未知)'); $('#siteqkText').html('<span class="text-warning">未知</span>'); $('.west-status-light').removeClass('paused'); }
            renderTrafficChart(conf);
        } else { msalert(4,jsoe.code,6000); }
        msloadingde();
    });
}
function renderTrafficChart(conf) {
    var canvas = document.getElementById('trafficChart');
    if (!canvas || typeof Chart === 'undefined') return;
    var historyData = conf['lls'].history || {};
    var labels = [];
    var values = [];
    Object.keys(historyData).sort().forEach(function(m) { labels.push(parseInt(m.split('-')[1]) + '月'); values.push(+(historyData[m] / (1024*1024*1024)).toFixed(2)); });
    labels.push('本月');
    values.push(+(conf['lls'].dq / (1024*1024*1024)).toFixed(2));
    if (window.trafficChart && window.trafficChart.data && window.trafficChart.data.labels && window.trafficChart.data.datasets && window.trafficChart.data.datasets[0]) {
        window.trafficChart.data.labels = labels;
        window.trafficChart.data.datasets[0].data = values;
        window.trafficChart.update();
        return;
    }
    if (window.trafficChart && typeof window.trafficChart.destroy === 'function') {
        window.trafficChart.destroy();
    }
    window.trafficChart = new Chart(canvas.getContext('2d'), { type: 'line', data: { labels: labels, datasets: [{ label: '流量用量(GB)', data: values, borderColor: '#1f8fd5', backgroundColor: 'rgba(31,143,213,.12)', borderWidth: 2, pointRadius: 3, fill: true }] }, options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } } });
}
function copypwd(lx){
    var text = lx=='ftp' ? ftp_pass : sql_pass;
    const el = document.createElement('input');
    el.setAttribute('value', text);
    document.body.appendChild(el);
    el.select();
    document.execCommand('copy');
    document.body.removeChild(el);
    msalert(2,'复制成功！',1000);
}
function php_vs(vio,phpbs) {
    if(vio==00){ msalert(3,'请勿选择伪静态！',4000); return; }
    msloading('正在加载中，请稍后...');
    let data = {};
    data["gn"]="phpxg";
    data["php"]=vio;
    $.post('./ajax.php', data, function (date) { var jsoe= JSON.parse(date); var qk= jsoe.code; if(qk=='修改成功'){ msalert(1,'修改成功！',2000); msloadingde(); } else { msalert(3,qk,2000); msloadingde(); } });
}
function sxxx(){
    msloading('正在计算您的空间和流量用量中，请稍后...');
    let data = {};
    data["gn"]="sxsyxx";
    $.post('./ajax.php', data, function () { msalert(1,'网页/数据库空间和流量用量刷新成功！',2000); configup(); });
}
function sizedwhs(size){
    var units = 'B';
    if(size/1024>1){ size = size/1024; units = 'KB'; }
    if(size/1024>1){ size = size/1024; units = 'MB'; }
    if(size/1024>1){ size = size/1024; units = 'GB'; }
    return size.toFixed(2)+units;
}
</script>
</body>
</html>
