<?php mnbt_admin_include('head'); ?>
<style>
/* ---- 管理首页样式 ---- */
.adm-dash { padding: 18px 20px; background: #f5f6fa; min-height: 100%; }

/* 统计卡片 */
.adm-stat-row { display: flex; flex-wrap: wrap; gap: 14px; margin-bottom: 18px; }
.adm-stat-card {
  flex: 1; min-width: 160px; background: #fff; border-radius: 10px;
  padding: 20px 18px; display: flex; align-items: center; gap: 14px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.06); transition: box-shadow .2s;
}
.adm-stat-card:hover { box-shadow: 0 4px 14px rgba(0,0,0,0.1); }
.adm-stat-icon {
  width: 48px; height: 48px; border-radius: 10px; display: flex;
  align-items: center; justify-content: center; font-size: 22px; color: #fff; flex-shrink: 0;
}
.adm-stat-body .adm-stat-num { font-size: 22px; font-weight: 700; color: #1e293b; line-height: 1.1; }
.adm-stat-body .adm-stat-label { font-size: 12px; color: #94a3b8; margin-top: 3px; }

/* 分段标题 */
.adm-section-title {
  font-size: 14px; font-weight: 700; color: #334155; margin: 0 0 14px 0;
  padding-bottom: 10px; border-bottom: 2px solid #e2e8f0;
}

/* 信息面板 */
.adm-panel {
  background: #fff; border-radius: 10px; padding: 20px;
  margin-bottom: 18px; box-shadow: 0 1px 3px rgba(0,0,0,0.06);
}
.adm-panel-header {
  display: flex; align-items: center; justify-content: space-between;
  margin-bottom: 16px;
}
.adm-panel-header h5 { margin: 0; font-size: 14px; font-weight: 700; color: #1e293b; }

/* 性能进度 */
.adm-metric-row { display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 14px; }
.adm-metric { padding: 0; }
.adm-metric-label { display: flex; justify-content: space-between; font-size: 13px; color: #64748b; margin-bottom: 6px; }
.adm-metric .progress {
  height: 8px; border-radius: 4px; background: #e2e8f0;
}
.adm-metric .progress-bar { border-radius: 4px; }

/* 信息表格 */
.adm-info-tbl { width: 100%; }
.adm-info-tbl td {
  padding: 7px 0; font-size: 13px; border-bottom: 1px solid #f1f5f9;
}
.adm-info-tbl td:first-child { color: #64748b; white-space: nowrap; width: 120px; }
.adm-info-tbl td:last-child { color: #1e293b; font-weight: 500; }

/* CPU 负载条 */
.adm-load-bars { display: flex; gap: 12px; }
.adm-load-item { text-align: center; flex: 1; }
.adm-load-item .progress {
  width: 100%; height: 6px; border-radius: 3px; background: #e2e8f0; margin-bottom: 4px;
}
.adm-load-item .progress-bar { border-radius: 3px; }
.adm-load-label { font-size: 11px; color: #94a3b8; }

/* 响应式 */
@media (max-width: 768px) {
  .adm-stat-card { min-width: 130px; padding: 14px 12px; }
  .adm-stat-icon { width: 38px; height: 38px; font-size: 18px; }
  .adm-stat-body .adm-stat-num { font-size: 18px; }
  .adm-metric-row { grid-template-columns: 1fr; }
}
</style>

<div class="adm-dash">

<!-- ====== 统计概览 ====== -->
<div class="adm-stat-row">
  <div class="adm-stat-card">
    <div class="adm-stat-icon" style="background:linear-gradient(135deg,#4facfe,#00f2fe);">
      <i class="mdi mdi-laptop"></i>
    </div>
    <div class="adm-stat-body">
      <div class="adm-stat-num"><?=number_format($sy['hosts'])?></div>
      <div class="adm-stat-label">主机数量</div>
    </div>
  </div>
  <div class="adm-stat-card">
    <div class="adm-stat-icon" style="background:linear-gradient(135deg,#43e97b,#38f9d7);">
      <i class="mdi mdi-server"></i>
    </div>
    <div class="adm-stat-body">
      <div class="adm-stat-num"><?=number_format($sy['bt_panels'])?></div>
      <div class="adm-stat-label">宝塔面板</div>
    </div>
  </div>
  <div class="adm-stat-card">
    <div class="adm-stat-icon" style="background:linear-gradient(135deg,#f093fb,#f5576c);">
      <i class="mdi mdi-router-wireless"></i>
    </div>
    <div class="adm-stat-body">
      <div class="adm-stat-num"><?=number_format($sy['nodes'])?></div>
      <div class="adm-stat-label">节点数量</div>
    </div>
  </div>
  <div class="adm-stat-card">
    <div class="adm-stat-icon" style="background:linear-gradient(135deg,#fa709a,#fee140);">
      <i class="mdi mdi-cart"></i>
    </div>
    <div class="adm-stat-body">
      <div class="adm-stat-num"><?=number_format($sy['orders'])?></div>
      <div class="adm-stat-label">订单总数</div>
    </div>
  </div>
</div>

<!-- ====== 服务器性能 ====== -->
<h6 class="adm-section-title"><i class="mdi mdi-chart-areaspline"></i> 服务器性能</h6>
<div class="row">
  <!-- 磁盘 -->
  <div class="col-lg-4">
    <div class="adm-panel">
      <?php if ($sy['disk_ok']): ?>
      <div class="adm-metric-label">
        <span><i class="mdi mdi-harddisk"></i> 磁盘使用</span>
        <span><?=_sy_fmt_bytes($sy['disk_used'])?> / <?=_sy_fmt_bytes($sy['disk_total'])?></span>
      </div>
      <div class="progress">
        <div class="progress-bar <?=$sy['disk_pct']>80?'bg-danger':($sy['disk_pct']>60?'bg-warning':'bg-info')?>"
             style="width:<?=$sy['disk_pct']?>%"><?=$sy['disk_pct']?>%</div>
      </div>
      <div style="margin-top:8px;font-size:11px;color:#94a3b8;">
        可用 <?=_sy_fmt_bytes($sy['disk_free'])?>
      </div>
      <?php else: ?>
      <div class="adm-metric-label">
        <span><i class="mdi mdi-harddisk"></i> 磁盘使用</span>
        <span>不可用</span>
      </div>
      <div style="text-align:center;padding:8px 0;color:#94a3b8;font-size:12px;">当前环境无法获取磁盘信息</div>
      <?php endif; ?>
    </div>
  </div>
  <!-- 内存 -->
  <div class="col-lg-4">
    <div class="adm-panel">
      <div class="adm-metric-label">
        <span><i class="mdi mdi-memory"></i> PHP 进程内存</span>
        <span><?=_sy_fmt_bytes($sy['mem_current'])?> / <?=htmlspecialchars($sy['memory_limit'])?></span>
      </div>
      <div class="progress">
        <div class="progress-bar bg-success" style="width:<?=min(100,round($sy['mem_current']/max(1,$sy['mem_peak'])*100))?>%">
          <?=_sy_fmt_bytes($sy['mem_current'])?>
        </div>
      </div>
      <div style="margin-top:8px;font-size:11px;color:#94a3b8;">
        峰值 <?=_sy_fmt_bytes($sy['mem_peak'])?> | 限制 <?=htmlspecialchars($sy['memory_limit'])?>
      </div>
    </div>
  </div>
  <!-- CPU 负载 -->
  <div class="col-lg-4">
    <div class="adm-panel">
      <div class="adm-metric-label">
        <span><i class="mdi mdi-cpu-64-bit"></i> CPU 负载</span>
        <span>
          <?php if ($sy['load_avg']): ?>
            <?=number_format($sy['load_avg'][0],2)?> / <?=number_format($sy['load_avg'][1],2)?> / <?=number_format($sy['load_avg'][2],2)?>
          <?php else: ?>
            不可用（仅 Linux）
          <?php endif; ?>
        </span>
      </div>
      <?php if ($sy['load_avg']): ?>
      <div class="adm-load-bars">
        <?php foreach ([1,5,15] as $i => $label): ?>
        <div class="adm-load-item">
          <div class="progress" style="transform:rotate(180deg);">
            <?php $pct = min(100, round($sy['load_avg'][$i] * 100)); ?>
            <div class="progress-bar <?=$pct>70?'bg-danger':($pct>40?'bg-warning':'bg-info')?>"
                 style="width:<?=$pct?>%"></div>
          </div>
          <div class="adm-load-label"><?=$label?>min</div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php else: ?>
      <div style="text-align:center;padding:12px 0;color:#94a3b8;font-size:13px;">
        <i class="mdi mdi-information"></i> sys_getloadavg 在当前系统不可用
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- ====== 系统信息 + PHP信息 ====== -->
<div class="row">
  <!-- 系统信息 -->
  <div class="col-lg-6">
    <div class="adm-panel">
      <div class="adm-panel-header">
        <h5><i class="mdi mdi-monitor"></i> 系统信息</h5>
        <span class="badge badge-info">运行中</span>
      </div>
      <table class="adm-info-tbl">
        <tr><td>操作系统</td><td><?=htmlspecialchars($sy['os'])?></td></tr>
        <tr><td>主机名</td><td><?=htmlspecialchars($sy['hostname'])?></td></tr>
        <tr><td>Web 服务</td><td><?=htmlspecialchars($sy['server_soft'])?></td></tr>
        <tr><td>IP : 端口</td><td><?=htmlspecialchars($sy['server_ip'])?> : <?=$sy['server_port']?></td></tr>
        <tr><td>服务器时间</td><td><?=$sy['server_time']?></td></tr>
        <tr><td>时区</td><td><?=htmlspecialchars($sy['timezone'])?></td></tr>
        <tr><td>数据库版本</td><td><?=htmlspecialchars($sy['db_version'])?></td></tr>
        <tr><td>Web版本 / SQL版本</td><td><?=$sy['web_version']?> / <?=$sy['sql_version']?></td></tr>
      </table>
    </div>
  </div>
  <!-- PHP 信息 -->
  <div class="col-lg-6">
    <div class="adm-panel">
      <div class="adm-panel-header">
        <h5><i class="mdi mdi-language-php"></i> PHP 信息</h5>
        <span class="badge badge-secondary">PHP <?=htmlspecialchars($sy['php_version'])?></span>
      </div>
      <table class="adm-info-tbl">
        <tr><td>PHP 版本</td><td><?=htmlspecialchars($sy['php_version'])?></td></tr>
        <tr><td>运行模式</td><td><?=htmlspecialchars($sy['php_sapi'])?></td></tr>
        <tr><td>内存限制</td><td><?=htmlspecialchars($sy['memory_limit'])?></td></tr>
        <tr><td>最大执行时间</td><td><?=htmlspecialchars($sy['max_exec_time'])?>s</td></tr>
        <tr><td>上传限制</td><td><?=htmlspecialchars($sy['upload_max'])?></td></tr>
        <tr><td>POST 限制</td><td><?=htmlspecialchars($sy['post_max'])?></td></tr>
        <tr><td>已加载扩展</td><td><?=$sy['ext_count']?> 个</td></tr>
        <tr><td>php.ini 路径</td><td style="font-size:12px;word-break:break-all;"><?php $ini = php_ini_loaded_file(); echo htmlspecialchars($ini ?: '未加载')?></td></tr>
      </table>
    </div>
  </div>
</div>

<!-- ====== 公告 + 广告 ====== -->
<div class="row">
  <div class="col-md-6">
    <div class="adm-panel">
      <div class="adm-panel-header">
        <h5><i class="mdi mdi-bullhorn"></i> 官网公告</h5>
        <button type="button" class="btn btn-sm btn-outline-info" id="butos" data-toggle="popover" data-placement="top" data-content="版本更新提示" title="">
          <i id="tbcls" class="mdi mdi-information"></i>
        </button>
      </div>
      <div id="mngf" style="font-size:13px;color:#334155;line-height:1.7;"></div>
    </div>
  </div>
  <div class="col-md-6">
    <div class="adm-panel">
      <div class="adm-panel-header">
        <h5><i class="mdi mdi-newspaper-variant"></i> 广告列表</h5>
        <span style="font-size:11px;color:#94a3b8;">广告均由第三方提供</span>
      </div>
      <div id="gglt" style="font-size:13px;color:#334155;">
        <code style="font-size:12px;"><b>广告均由第三方提供！其内容与本系统无关！</b></code>
      </div>
    </div>
  </div>
</div>

<!-- ====== 插件组件 ====== -->
<?php
if (function_exists('mnbt_plugin_render_widgets_html')) {
    echo '<div style="margin-top:4px;">' . mnbt_plugin_render_widgets_html('admin') . '</div>';
}
?>

</div><!-- /adm-dash -->

<script>
// 公告加载
msloading('正在获取中，请稍后...','text-info','text-default','#mngf');
msloading('正在获取中，请稍后...','text-info','text-default','#gglt');

let datar = {};
datar["gn"]="mnbt";
$.post('./ajax.php', datar, function (date) {
    var jsoe= JSON.parse(date);
    document.getElementById("mngf").innerHTML=jsoe.gg;
    document.getElementById("tbcls").className='mdi '+jsoe.cl;
    document.getElementById("tbcls").innerHTML=jsoe.vs;
    document.getElementById("butos").setAttribute("data-content", jsoe.gx);
    msloadingde("#mngf");
});

let data = {};
data["gn"]="gglist";
$.post('./ajax.php', data, function (date) {
    var jsoe= JSON.parse(date);
    for(var i in jsoe){
        var tmp = document.createElement("div");
        tmp.innerHTML= '<span class="list-group-item list-group-item-success" style="font-size:12px;margin:2px 0;border-radius:6px;">'+jsoe[i].nr+'<a href="http://'+jsoe[i].url+'/" target="_blank" style="margin-left:8px;">'+jsoe[i].name+'</a></span>';
        document.getElementById("gglt").appendChild(tmp);
    }
    msloadingde("#gglt");
});
</script>
</body>
</html>
