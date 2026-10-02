<?php mnbt_theme_include('head'); ?>
<script type="text/javascript" src="<?=mnbt_asset_url('js/Chart.min.js')?>"></script>
<style>
.st-page-card>.card-header{align-items:center;flex-wrap:wrap;gap:10px}
.st-page-title{display:flex;align-items:center;gap:8px}
.st-page-title .badge{font-weight:400}
.st-stat-card .card-body{min-height:112px}
.st-stat-card .fs-22{letter-spacing:.2px}
.st-pager-wrap{display:flex;align-items:center;justify-content:center;padding:10px 0 14px;gap:4px}
.st-pager-wrap .pagination{margin-bottom:0}
.log-box-p{border:1px solid #e5e7eb;border-radius:4px;background:#fafafa;font-family:Consolas,monospace;font-size:12px;padding:14px;max-height:420px;overflow-y:auto;line-height:1.7;white-space:pre-wrap;word-break:break-all;margin:0}
.st-coverage-bar{display:flex;align-items:center;gap:10px;padding:8px 14px;border-radius:4px;font-size:12px;margin-bottom:12px}
.st-coverage-bar.st-cov-full{background:#e6f7ed;color:#16a34a}
.st-coverage-bar.st-cov-partial{background:#fff7e6;color:#d97706}
.st-coverage-bar.st-cov-none{background:#fee2e2;color:#dc2626}
.st-coverage-bar .st-cov-badge{padding:2px 8px;border-radius:10px;font-weight:500;background:rgba(255,255,255,0.5)}
.st-source-tag{display:inline-block;padding:2px 6px;border-radius:3px;font-size:11px;font-weight:500;margin-left:6px}
.st-source-sqlite{background:#dbeafe;color:#1d4ed8}
.st-source-recent{background:#fef3c7;color:#b45309}
</style>

<div class="container-fluid py-3">
  <div class="card st-page-card">
    <header class="card-header">
      <div class="card-title st-page-title mb-0">
        <i class="mdi mdi-chart-bar text-primary"></i>
        <span>站点统计</span>
        <span class="badge badge-primary-light"><?=htmlspecialchars($yhc['sqldz'])?></span>
      </div>
      <ul class="card-actions">
        <li>
          <div class="btn-group btn-group-sm" id="stRangeGroup">
            <button class="btn btn-outline-primary active" data-range="today">今日</button>
            <button class="btn btn-outline-primary" data-range="yesterday">昨日</button>
            <button class="btn btn-outline-primary" data-range="7d">近7天</button>
            <button class="btn btn-outline-primary" data-range="30d">近30天</button>
          </div>
        </li>
        <li><button class="btn btn-primary btn-sm" id="stRefresh"><i class="mdi mdi-refresh"></i> 刷新</button></li>
      </ul>
    </header>
    <div class="card-body">
      <ul class="nav nav-tabs nav-fill" id="stTabNav">
        <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#tab-overview">概览</a></li>
        <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab-spider">蜘蛛</a></li>
        <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab-client">客户端</a></li>
        <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab-method">请求方式</a></li>
        <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab-iprank">IP 排行</a></li>
        <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab-urirank">URI 排行</a></li>
        <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab-errors">错误日志</a></li>
        <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab-recent">网站日志</a></li>
      </ul>

      <div class="tab-content pt-3" id="stTabContent">
    <!-- 概览 -->
    <div class="tab-pane fade show active" id="tab-overview">
      <div id="stOverviewCov"></div>
      <div class="row">
        <div class="col-6 col-md-3 mb-3"><div class="card bg-primary text-white st-stat-card"><div class="card-body">
          <div class="text-white-50 fs-12"><i class="mdi mdi-eye mr-1"></i>浏览量 PV</div>
          <div class="fs-22 font-weight-bold lh-1 my-2" id="stPv">-</div>
          <div class="fs-12 text-white-50" id="stPvComp"></div>
        </div></div></div>
        <div class="col-6 col-md-3 mb-3"><div class="card bg-success text-white st-stat-card"><div class="card-body">
          <div class="text-white-50 fs-12"><i class="mdi mdi-account-multiple mr-1"></i>访客数 UV</div>
          <div class="fs-22 font-weight-bold lh-1 my-2" id="stUv">-</div>
          <div class="fs-12 text-white-50" id="stUvComp"></div>
        </div></div></div>
        <div class="col-6 col-md-3 mb-3"><div class="card bg-info text-white st-stat-card"><div class="card-body">
          <div class="text-white-50 fs-12"><i class="mdi mdi-swap-vertical-bold mr-1"></i>流量</div>
          <div class="fs-22 font-weight-bold lh-1 my-2" id="stTraffic">-</div>
          <div class="fs-12 text-white-50" id="stTrafficComp"></div>
        </div></div></div>
        <div class="col-6 col-md-3 mb-3"><div class="card bg-danger text-white st-stat-card"><div class="card-body">
          <div class="text-white-50 fs-12"><i class="mdi mdi-alert-circle-outline mr-1"></i>错误数</div>
          <div class="fs-22 font-weight-bold lh-1 my-2" id="stErrors">-</div>
          <div class="fs-12 text-white-50" id="stErrorsComp"></div>
        </div></div></div>
      </div>
      <div class="card">
        <header class="card-header"><div class="card-title">数据指标</div>
          <ul class="card-actions"><li><div class="btn-group btn-group-sm" id="stMetricSwitch"><button class="btn btn-outline-secondary active" data-metric="pv">PV</button><button class="btn btn-outline-secondary" data-metric="uv">UV</button><button class="btn btn-outline-secondary" data-metric="total_bytes">流量</button></div></li></ul>
        </header>
        <div class="card-body"><canvas id="stTrendChart" style="height:200px;width:100%"></canvas></div>
      </div>
    </div>

    <!-- 蜘蛛 -->
    <div class="tab-pane fade" id="tab-spider">
      <div id="stSpiderCov"></div>
      <div class="callout callout-info mb-3">爬虫请求统计数据，基于 User-Agent 识别。</div>
      <div class="table-responsive"><table class="table table-vcenter table-hover table-striped"><thead><tr><th>蜘蛛名称</th><th class="text-center" style="width:14%">请求数</th><th class="text-center" style="width:18%">占比</th></tr></thead><tbody id="stSpiderBody"><tr><td colspan="3" class="text-center text-muted py-5">暂无数据</td></tr></tbody></table></div>
      <div class="st-pager-wrap" id="stSpiderPager"></div>
    </div>

    <!-- 客户端 -->
    <div class="tab-pane fade" id="tab-client">
      <div id="stClientCov"></div>
      <div class="callout callout-primary mb-3">浏览器及设备类型分布统计。</div>
      <div class="table-responsive"><table class="table table-vcenter table-hover table-striped"><thead><tr><th>客户端类型</th><th class="text-center" style="width:14%">请求数</th><th class="text-center" style="width:18%">占比</th></tr></thead><tbody id="stClientBody"><tr><td colspan="3" class="text-center text-muted py-5">暂无数据</td></tr></tbody></table></div>
      <div class="st-pager-wrap" id="stClientPager"></div>
    </div>

    <!-- 请求方式 -->
    <div class="tab-pane fade" id="tab-method">
      <div id="stMethodCov"></div>
      <div class="callout callout-info mb-3">HTTP 请求方式统计（GET / POST / HEAD 等）。</div>
      <div class="table-responsive"><table class="table table-vcenter table-hover table-striped"><thead><tr><th>请求方式</th><th class="text-center" style="width:14%">请求数</th><th class="text-center" style="width:18%">占比</th></tr></thead><tbody id="stMethodBody"><tr><td colspan="3" class="text-center text-muted py-5">暂无数据</td></tr></tbody></table></div>
      <div class="st-pager-wrap" id="stMethodPager"></div>
    </div>

    <!-- IP 排行 -->
    <div class="tab-pane fade" id="tab-iprank">
      <div id="stIpRankCov"></div>
      <div class="callout callout-primary mb-3">客户端 IP 访问排行。</div>
      <div class="table-responsive"><table class="table table-vcenter table-hover table-striped"><thead><tr><th>IP</th><th class="text-center" style="width:14%">请求数</th><th class="text-center" style="width:14%">流量</th><th class="text-center" style="width:18%">占比</th></tr></thead><tbody id="stIpRankBody"><tr><td colspan="4" class="text-center text-muted py-5">暂无数据</td></tr></tbody></table></div>
      <div class="st-pager-wrap" id="stIpPager"></div>
    </div>

    <!-- URI 排行 -->
    <div class="tab-pane fade" id="tab-urirank">
      <div id="stUriRankCov"></div>
      <div class="callout callout-success mb-3">URI 访问频率排行。</div>
      <div class="table-responsive"><table class="table table-vcenter table-hover table-striped"><thead><tr><th>URI</th><th class="text-center" style="width:14%">请求数</th><th class="text-center" style="width:14%">流量</th><th class="text-center" style="width:18%">占比</th></tr></thead><tbody id="stUriRankBody"><tr><td colspan="4" class="text-center text-muted py-5">暂无数据</td></tr></tbody></table></div>
      <div class="st-pager-wrap" id="stUriPager"></div>
    </div>

    <!-- 错误日志 -->
    <div class="tab-pane fade" id="tab-errors">
      <div id="stErrorsCov"></div>
      <div class="callout callout-danger mb-3">状态码 >= 400 的错误请求日志。</div>
      <div class="table-responsive"><table class="table table-vcenter table-hover table-striped"><thead><tr><th>时间</th><th>IP</th><th>方法</th><th>URI</th><th class="text-center" style="width:9%">状态码</th><th class="text-center" style="width:9%">字节</th></tr></thead><tbody id="stErrorsBody"><tr><td colspan="6" class="text-center text-muted py-5">暂无数据</td></tr></tbody></table></div>
      <div class="st-pager-wrap" id="stErrorsPager"></div>
    </div>

    <!-- 网站日志 -->
    <div class="tab-pane fade" id="tab-recent">
      <div id="stRecentCov"></div>
      <div class="callout callout-info mb-3">实时 Nginx 访问日志（反向读取）。</div>
      <pre class="log-box-p" id="stRecentLog">暂无数据</pre>
      <div class="st-pager-wrap" id="stRecentPager"></div>
    </div>
      </div>
    </div>
  </div>
</div>

<script>
var stChart = null, stState = {range:'today', tab:'overview'};

function stFmt(n){
  if(!n||n==='0'||n===0)return'0 B';var v=typeof n==='string'?parseFloat(n):n;if(isNaN(v)||v<0)return'0 B';var u=['B','KB','MB','GB','TB'],i=0;while(v>=1024&&i<u.length-1){v/=1024;i++}return v.toFixed(i>0?1:0)+' '+u[i];
}
function stNum(n){n=Number(n);return isNaN(n)?'0':n.toString().replace(/\B(?=(\d{3})+(?!\d))/g,',');}
function stPct(v,t){return t?(v/t*100).toFixed(1)+'%':'0%';}

function stPost(act,data,cb){
  data=data||{};data.gn='site_stats';data.act=act;
  $.post('./ajax.php',data,function(r){
    try{var o=typeof r==='object'?r:JSON.parse(r);cb(o)}catch(e){cb(null)}
  });
}

function stRenderCoverage(cov,source){
  if(!cov)return '';
  var cls='st-cov-partial',badge='部分数据',icon='mdi-alert-circle-outline';
  if(cov.has_data===true&&cov.rate>=1){cls='st-cov-full';badge='数据完整';icon='mdi-check-circle-outline';}
  else if(!cov.has_data||cov.data_days===0){cls='st-cov-none';badge='无聚合数据';icon='mdi-alert';}
  var srcTag='';
  if(source==='sqlite')srcTag='<span class="st-source-tag st-source-sqlite">聚合数据库</span>';
  else if(source==='recent_log')srcTag='<span class="st-source-tag st-source-recent">实时日志</span>';
  var days=cov.data_days?cov.data_days+' 天':'';
  var rate=cov.rate!==undefined?Math.round(cov.rate*100)+'%':'';
  var range='';
  if(cov.min_date&&cov.max_date)range=cov.min_date+' ~ '+cov.max_date;
  return '<div class="st-coverage-bar '+cls+'"><i class="mdi '+icon+'"></i><span class="st-cov-badge">'+badge+'</span><span>'+days+(rate?' · 覆盖率 '+rate:'')+(range?' · '+range:'')+'</span>'+srcTag+'</div>';
}

function stLoadActive(){
  var tab=stState.tab;
  if(tab==='overview'){
    stLoadOverview();
    setTimeout(stLoadTrend,150);
  }
  else if(tab==='spider')stLoadTable('spider')
  else if(tab==='client')stLoadTable('client')
  else if(tab==='method')stLoadTable('method')
  else if(tab==='iprank')stLoadTable('iprank')
  else if(tab==='urirank')stLoadTable('urirank')
  else if(tab==='errors')stLoadTable('errors')
  else if(tab==='recent')stLoadRecent();
}

/* ---- Overview ---- */
function stLoadOverview(){
  stPost('overview',{range:stState.range},function(r){
    if(!r||!r.status){
      $('#stPv,#stUv,#stTraffic,#stErrors').text('-');
      $('#stPvComp,#stUvComp,#stTrafficComp,#stErrorsComp').text('');
      $('#stOverviewCov').html(stRenderCoverage({has_data:false,data_days:0,rate:0},'none'));
      return;
    }
    var d=r.data||{},c=r.comparison||{};
    $('#stPv').text(stNum(d.pv));$('#stUv').text(stNum(d.uv));
    $('#stTraffic').text(stFmt(d.total_bytes));$('#stErrors').text(stNum(d.error_count));
    $('#stOverviewCov').html(stRenderCoverage(r.coverage,r.source));
    if(c.prev){
      var comps={pv:'#stPvComp',uv:'#stUvComp',total_bytes:'#stTrafficComp',error_count:'#stErrorsComp'};
      for(var k in comps){
        var v=parseFloat(d[k])||0,pv=parseFloat(c.prev[k])||0;
        if(pv){var pct=((v-pv)/pv*100).toFixed(1);$(comps[k]).html('环比 <span class="badge badge-light">'+(pct>=0?'+':'')+pct+'%</span>');}else $(comps[k]).text('');
      }
    }
  });
}

function stLoadTrend(){
  stPost('trend',{range:stState.range},function(r){
    if(!r||!r.status||!r.data)return;
    var metric=$('#stMetricSwitch .btn.active').data('metric');
    var labels=[],vals=[];
    (r.data||[]).forEach(function(item){
      labels.push(item.time||item.hour||item.date||'');
      vals.push(parseFloat(item[metric]||0));
    });
    if(stChart){stChart.destroy();stChart=null;}
    var ctx=document.getElementById('stTrendChart').getContext('2d');
    stChart=new Chart(ctx,{
      type:'line',
      data:{labels:labels,datasets:[{label:metric==='total_bytes'?'流量':metric.toUpperCase(),data:vals,borderColor:'#2d7bf4',backgroundColor:'rgba(45,123,244,0.08)',borderWidth:2,pointRadius:2.5,pointBackgroundColor:'#2d7bf4',fill:true,tension:0.3}]},
      options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}},scales:{x:{ticks:{maxTicksLimit:10,font:{size:11}},grid:{display:false}},y:{ticks:{font:{size:11}},beginAtZero:true}}}
    });
  });
}

/* ---- Table pages ---- */
var stTableCfgs={
  spider:{url:'spider',bodyId:'stSpiderBody',pagerId:'stSpiderPager',covId:'stSpiderCov',
    rowFn:function(x,i,t){return'<tr><td>'+$('<span>').text(x.spider||x.spider_name||'-').html()+'</td><td class="text-center">'+stNum(x.count||x.request_count||0)+'</td><td class="text-center"><div class="progress progress-sm"><div class="progress-bar bg-success" style="width:'+stPct(x.count||x.request_count||0,t)+'"></div></div></td></tr>';}},
  client:{url:'client',bodyId:'stClientBody',pagerId:'stClientPager',covId:'stClientCov',
    rowFn:function(x,i,t){return'<tr><td>'+$('<span>').text(x.client||x.client_type||x.client_name||x.os||'-').html()+'</td><td class="text-center">'+stNum(x.count||x.request_count||0)+'</td><td class="text-center"><div class="progress progress-sm"><div class="progress-bar" style="width:'+stPct(x.count||x.request_count||0,t)+'"></div></div></td></tr>';}},
  method:{url:'method',bodyId:'stMethodBody',pagerId:'stMethodPager',covId:'stMethodCov',
    rowFn:function(x,i,t){return'<tr><td>'+$('<span>').text(x.method||'-').html()+'</td><td class="text-center">'+stNum(x.count||x.request_count||0)+'</td><td class="text-center"><div class="progress progress-sm"><div class="progress-bar bg-info" style="width:'+stPct(x.count||x.request_count||0,t)+'"></div></div></td></tr>';}},
  iprank:{url:'ip_rank',bodyId:'stIpRankBody',pagerId:'stIpPager',covId:'stIpRankCov',
    rowFn:function(x,i,t){return'<tr><td class="text-monospace">'+$('<span>').text(x.ip||'-').html()+'</td><td class="text-center">'+stNum(x.count||0)+'</td><td class="text-center">'+stFmt(x.bytes||0)+'</td><td class="text-center"><div class="progress progress-sm"><div class="progress-bar" style="width:'+stPct(x.count||0,t)+'"></div></div></td></tr>';}},
  urirank:{url:'uri_rank',bodyId:'stUriRankBody',pagerId:'stUriPager',covId:'stUriRankCov',
    rowFn:function(x,i,t){return'<tr><td class="text-monospace" style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">'+$('<span>').text(x.uri||'-').html()+'</td><td class="text-center">'+stNum(x.count||0)+'</td><td class="text-center">'+stFmt(x.bytes||0)+'</td><td class="text-center"><div class="progress progress-sm"><div class="progress-bar bg-success" style="width:'+stPct(x.count||0,t)+'"></div></div></td></tr>';}},
  errors:{url:'errors',bodyId:'stErrorsBody',pagerId:'stErrorsPager',covId:'stErrorsCov',
    rowFn:function(x,i,t){var tm=x.time||x.time_local||'';if(tm.length>19)tm=tm.substring(0,19);return'<tr><td class="fs-12">'+$('<span>').text(tm).html()+'</td><td class="text-monospace fs-12">'+$('<span>').text(x.ip||'-').html()+'</td><td>'+$('<span>').text(x.method||'-').html()+'</td><td class="text-monospace" style="max-width:150px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:12px">'+$('<span>').text(x.uri||'-').html()+'</td><td class="text-center"><span class="badge badge-'+(x.status>=500?'danger':'warning')+'">'+(x.status||'-')+'</span></td><td class="text-center">'+stFmt(x.bytes||0)+'</td></tr>';}}
};

function stLoadTable(key,pg){
  var cfg=stTableCfgs[key];if(!cfg)return;
  var page=pg||1;
  var colspan=$('#'+cfg.bodyId).closest('table').find('thead tr:first th').length;
  stPost(cfg.url,{range:stState.range,page:page,page_size:10},function(r){
    var tbody=$('#'+cfg.bodyId);
    if(cfg.covId){
      $('#'+cfg.covId).html(stRenderCoverage(r?r.coverage:null,r?r.source:null));
    }
    if(!r||!r.status||!r.data||!r.data.length){
      var msg=(r&&r.msg)?r.msg:'暂无数据';
      tbody.html('<tr><td colspan="'+colspan+'" class="text-center text-muted py-5">'+$('<span>').text(msg).html()+'</td></tr>');
      $('#'+cfg.pagerId).empty();return;
    }
    var totalFrom=parseFloat(r.sum_count)||r.data.reduce(function(s,x){return s+(x.count||x.request_count||0);},0);
    var html='';
    if(r.fallback&&r.msg){html+='<tr><td colspan="'+colspan+'" class="text-center text-info fs-12">'+$('<span>').text(r.msg).html()+'</td></tr>';}
    r.data.forEach(function(x,i){html+=cfg.rowFn(x,i,totalFrom);});
    tbody.html(html);
    stRenderPager(cfg.pagerId,r.total||r.data.length,page,10);
  });
}

function stRenderPager(id,total,page,ps){
  var el=$('#'+id);el.empty();if(total<=1)return;
  var tp=Math.ceil(total/ps);
  var h='<ul class="pagination pagination-sm no-border mb-0">';
  h+='<li class="page-item'+(page<=1?' disabled':'')+'"><a class="page-link st-pg" data-pg="'+(page-1)+'">‹</a></li>';
  var s=Math.max(1,page-3),e=Math.min(tp,page+3);
  if(s>1){h+='<li class="page-item'+(1===page?' active':'')+'"><a class="page-link st-pg" data-pg="1">1</a></li>';if(s>2)h+='<li class="page-item disabled"><a class="page-link">…</a></li>';}
  for(var i=s;i<=e;i++)h+='<li class="page-item'+(i===page?' active':'')+'"><a class="page-link st-pg" data-pg="'+i+'">'+i+'</a></li>';
  if(e<tp){if(e<tp-1)h+='<li class="page-item disabled"><a class="page-link">…</a></li>';h+='<li class="page-item'+(tp===page?' active':'')+'"><a class="page-link st-pg" data-pg="'+tp+'">'+tp+'</a></li>';}
  h+='<li class="page-item'+(page>=tp?' disabled':'')+'"><a class="page-link st-pg" data-pg="'+(page+1)+'">›</a></li>';
  h+='</ul><span class="text-muted ml-2">共 '+stNum(total)+' 条</span>';
  el.html(h);
}

/* ---- Recent ---- */
function stLoadRecent(pg){
  var page=pg||1;
  stPost('recent',{range:stState.range,page:page,page_size:10},function(r){
    var el=document.getElementById('stRecentLog');
    if(!r||!r.status||!r.data||!r.data.length){
      el.textContent='暂无日志';$('#stRecentPager').empty();return;
    }
    el.textContent=r.data.map(function(x){return x.raw||('['+(x.time||'')+'] '+(x.ip||'')+' '+(x.method||'')+' '+(x.uri||'')+' '+(x.status||'')+' '+stFmt(x.bytes||0)+' "'+(x.ua||'')+'"');}).join('\n');
    stRenderPager('stRecentPager',r.total||r.data.length,page,10);
  });
}

/* ---- Pagination ---- */
$(document).on('click','.st-pg',function(){
  var li=$(this).closest('.page-item');if(li.hasClass('active')||li.hasClass('disabled'))return;
  var pg=parseInt($(this).data('pg'));if(!pg||pg<1)return;
  if(stState.tab==='recent')stLoadRecent(pg);else stLoadTable(stState.tab,pg);
});

/* ---- Init ---- */
$(function(){
  $('a[data-toggle="tab"]').on('shown.bs.tab',function(e){
    stState.tab=$(this).attr('href').replace('#tab-','');
    stLoadActive();
  });
  $('#stRangeGroup .btn').on('click',function(){
    stState.range=$(this).data('range');
    $(this).addClass('active').siblings().removeClass('active');
    stLoadActive();
  });
  $('#stMetricSwitch .btn').on('click',function(){
    $(this).addClass('active').siblings().removeClass('active');
    stLoadTrend();
  });
  $('#stRefresh').on('click',function(){stLoadActive();});
  stLoadOverview();stLoadTrend();
});
</script>
</body>
</html>
