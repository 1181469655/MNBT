<?php mnbt_theme_include('head'); ?>
<script type="text/javascript" src="<?=mnbt_asset_url('js/Chart.min.js')?>"></script>
<script type="text/javascript" src="<?=mnbt_asset_url('js/jquery-confirm/jquery-confirm.min.js')?>"></script>
<div class="west-page">
  <div class="west-alert"><i class="mdi mdi-alert"></i> 您的联系资料不完整，请与真实的电话号码和邮箱，这将对您的主机管理非常重要。</div>
  <div class="west-banner"><span>独享云虚拟主机</span><strong>新品上线</strong><i class="mdi mdi-server-network"></i></div>
  <section class="west-panel west-host-panel">
    <div class="west-panel-title"><i class="mdi mdi-database"></i> 主机信息</div>
    <div class="west-host-body">
      <div class="west-host-status"><i class="mdi mdi-database-check"></i><strong id="siteqk">运行中</strong><span>主机有效期</span><em><?= htmlspecialchars($yhc['endtime'] ?? '以系统数据为准', ENT_QUOTES, 'UTF-8') ?></em></div>
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
  <section class="west-panel">
    <div class="west-panel-title">网站基本功能</div>
    <div class="west-func-grid">
      <a href="#!" class="func-item js-create-tab" data-title="域名绑定" data-url="set.php?gn=url"><i class="mdi mdi-server"></i><span>主机域名绑定</span></a>
      <a href="#!" class="func-item phpvs-set"><i class="mdi mdi-lock-reset"></i><span>修改密码</span></a>
      <a href="#!" class="func-item js-create-tab" data-title="默认首页" data-url="set.php?gn=mrwd"><i class="mdi mdi-monitor"></i><span>设置首页</span></a>
      <a href="#!" class="func-item js-create-tab" data-title="伪静态" data-url="set.php?gn=wjt"><i class="mdi mdi-cog"></i><span>主机环境设置</span></a>
      <a href="#!" class="func-item phpvs-set"><i class="mdi mdi-language-php"></i><span>PHP版本</span></a>
      <a href="#!" class="func-item js-create-tab" data-title="运行目录" data-url="set.php?gn=yxml"><i class="mdi mdi-folder-home"></i><span>虚拟目录</span></a>
      <a href="#!" class="func-item js-create-tab" data-title="防盗链" data-url="set.php?gn=fdl"><i class="mdi mdi-file-document-edit"></i><span>伪静态设置</span></a>
      <a href="#!" class="func-item js-create-tab" data-title="独立IP" data-url="set.php?gn=url"><i class="mdi mdi-alpha-p-box"></i><span>独立IP</span></a>
      <a href="#!" class="func-item js-create-tab" data-title="缓存配置" data-url="set.php?gn=cache"><i class="mdi mdi-plus-circle"></i><span>主机诊断</span></a>
      <a href="#!" class="func-item js-create-tab" data-title="MIME类型" data-url="set.php?gn=gzip"><i class="mdi mdi-sitemap"></i><span>MIME类型</span></a>
      <a href="#!" class="func-item js-create-tab" data-title="一键部署" data-url="webgl.php?gn=yjbs"><i class="mdi mdi-rocket-launch"></i><span>301转向</span></a>
      <a href="#!" class="func-item js-create-tab" data-title="SSL配置" data-url="set.php?gn=ssl"><i class="mdi mdi-certificate"></i><span>ASP.NET版本</span></a>
    </div>
  </section>
  <section class="west-panel west-chart-panel">
    <div class="west-panel-title">资源使用情况</div>
    <div class="west-meter-row"><span>网页空间</span><div id="web-progressbar"><div class="progress-label">1%</div></div></div>
    <div class="west-meter-row"><span>数据库空间</span><div id="sql-progressbar"><div class="progress-label">1%</div></div></div>
    <div class="west-meter-row"><span>本月流量</span><div id="lls-progressbar"><div class="progress-label">1%</div></div></div>
    <canvas id="trafficChart"></canvas>
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
            if(conf['qk']=='1'){ $('#siteqk').html('(运行中)'); $('#siteqkText').html('<span class="text-success">运行中</span>'); }
            else if(conf['qk']=='0'){ $('#siteqk').html('(已暂停)'); $('#siteqkText').html('<span class="text-danger">已暂停</span>'); }
            else{ $('#siteqk').html('(未知)'); $('#siteqkText').html('<span class="text-warning">未知</span>'); }
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
    if (window.trafficChart) { window.trafficChart.data.labels = labels; window.trafficChart.data.datasets[0].data = values; window.trafficChart.update(); return; }
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
