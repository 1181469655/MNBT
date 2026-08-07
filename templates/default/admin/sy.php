<?php mnbt_admin_include('head'); ?>
<link rel="stylesheet" href="https://unpkg.com/layui@2.9.8/dist/css/layui.css">
<style>
/* ---- Layui 管理首页增强 ---- */
.ly-dash { padding: 15px; background: #f2f3f5; min-height: 100%; }

/* 统计卡片行 */
.ly-stat-row { display: flex; flex-wrap: wrap; gap: 12px; margin-bottom: 15px; }
.ly-stat-card {
  flex: 1; min-width: 155px; background: #fff; border-radius: 8px;
  padding: 18px 16px; display: flex; align-items: center; gap: 12px;
  box-shadow: 0 1px 2px 0 rgba(0,0,0,.05); transition: box-shadow .2s;
}
.ly-stat-card:hover { box-shadow: 0 3px 8px rgba(0,0,0,.1); }
.ly-stat-card .ly-stat-icon {
  width: 44px; height: 44px; border-radius: 8px; display: flex;
  align-items: center; justify-content: center; font-size: 20px; color: #fff; flex-shrink: 0;
}
.ly-stat-num { font-size: 22px; font-weight: 700; color: #1e293b; line-height: 1.2; }
.ly-stat-label { font-size: 12px; color: #8c8c8c; }

/* 分段标题 */
.ly-sec-title {
  font-size: 15px; font-weight: 600; color: #333; margin: 0 0 12px 0;
  padding-left: 10px; border-left: 3px solid #5fb878;
}

/* 指标标签 */
.ly-metric-label { display: flex; justify-content: space-between; font-size: 13px; color: #666; margin-bottom: 8px; }

/* 卡片内栅格微调 */
.ly-card-body { padding: 16px; }
.ly-card-body .progress { height: 8px; border-radius: 4px; background: #eee; margin-bottom: 6px; }
.ly-card-body .progress-bar { border-radius: 4px; }

/* CPU 负载条 */
.ly-load-row { display: flex; gap: 14px; margin-top: 6px; }
.ly-load-item { text-align: center; flex: 1; }
.ly-load-item .progress { height: 6px; border-radius: 3px; background: #eee; margin-bottom: 4px; }
.ly-load-item .progress-bar { border-radius: 3px; }
.ly-load-tag { font-size: 11px; color: #999; }

/* 信息表格 */
.ly-info-tbl { width: 100%; }
.ly-info-tbl td { padding: 6px 0; font-size: 13px; border-bottom: 1px solid #f6f6f6; }
.ly-info-tbl td:first-child { color: #888; white-space: nowrap; width: 115px; }
.ly-info-tbl td:last-child { color: #222; font-weight: 500; }

/* 公告/广告 */
.ly-gg-item { margin: 4px 0; font-size: 12px; border-radius: 4px; }

/* 系统通知横幅 */
.ly-gg-banner {
  display: flex; align-items: center; gap: 10px;
  background: #eaf6fe; border: 1px solid #bee5f8; border-left: 4px solid #12b7f5;
  border-radius: 8px; padding: 12px 16px; margin-bottom: 15px;
  font-size: 14px; color: #0a6a9c; box-shadow: 0 1px 2px 0 rgba(0,0,0,.05);
}
.ly-gg-banner .mdi { font-size: 22px; color: #12b7f5; }
.ly-gg-banner b { color: #12688f; font-size: 15px; }

/* 广告弹窗网格 */
.ly-ad-grid {
  display: grid; grid-template-columns: 1fr 1fr; gap: 12px;
}
.ly-ad-card {
  border: 1px solid #e8eaed; border-radius: 8px; padding: 14px 16px;
  background: #fafbfc; transition: box-shadow .2s, border-color .2s;
  overflow: hidden;
}
.ly-ad-card:hover { box-shadow: 0 2px 8px rgba(0,0,0,.08); border-color: #d0d5db; }
.ly-ad-card--full { grid-column: 1 / -1; }
.ly-ad-card .ly-ad-name {
  font-size: 15px; font-weight: 600; color: #17a2b8; text-decoration: none;
  display: block; margin-bottom: 6px;
}
.ly-ad-card .ly-ad-name:hover { color: #117a8b; }
.ly-ad-card .ly-ad-nr { font-size: 12px; color: #666; line-height: 1.7; }
.ly-ad-card .ly-ad-nr img { max-width: 100%; height: auto; }
@media (max-width: 560px) {
  .ly-ad-grid { grid-template-columns: 1fr; }
}

@media (max-width: 768px) {
  .ly-stat-card { min-width: 120px; padding: 12px 10px; }
  .ly-stat-card .ly-stat-icon { width: 36px; height: 36px; font-size: 17px; }
  .ly-stat-num { font-size: 18px; }
}
</style>

<div class="ly-dash">

<!-- ====== 系统通知横幅 ====== -->
<div class="ly-gg-banner">
  <i class="mdi mdi-qqchat"></i>
  <span>使用本系统的请进入我们的QQ群：<b>994752422</b>方便进行通知！</span>
</div>

<!-- ====== 统计概览 ====== -->
<div class="ly-stat-row">
  <div class="ly-stat-card">
    <div class="ly-stat-icon" style="background:#5fb878;">
      <i class="mdi mdi-laptop"></i>
    </div>
    <div>
      <div class="ly-stat-num"><?=number_format($sy['hosts'])?></div>
      <div class="ly-stat-label">主机数量</div>
    </div>
  </div>
  <div class="ly-stat-card">
    <div class="ly-stat-icon" style="background:#009688;">
      <i class="mdi mdi-server"></i>
    </div>
    <div>
      <div class="ly-stat-num"><?=number_format($sy['bt_panels'])?></div>
      <div class="ly-stat-label">宝塔面板</div>
    </div>
  </div>
  <div class="ly-stat-card">
    <div class="ly-stat-icon" style="background:#ff5722;">
      <i class="mdi mdi-router-wireless"></i>
    </div>
    <div>
      <div class="ly-stat-num"><?=number_format($sy['nodes'])?></div>
      <div class="ly-stat-label">节点数量</div>
    </div>
  </div>
  <div class="ly-stat-card">
    <div class="ly-stat-icon" style="background:#f78400;">
      <i class="mdi mdi-cart"></i>
    </div>
    <div>
      <div class="ly-stat-num"><?=number_format($sy['orders'])?></div>
      <div class="ly-stat-label">订单总数</div>
    </div>
  </div>
</div>

<!-- ====== 服务器性能 ====== -->
<fieldset class="layui-elem-field layui-field-title" style="margin-top:20px;">
  <legend><i class="mdi mdi-chart-areaspline"></i> 服务器性能</legend>
</fieldset>

<div class="layui-row layui-col-space15">
  <!-- 磁盘 -->
  <div class="layui-col-lg4">
    <div class="layui-card">
      <?php if ($sy['disk_ok']): ?>
      <div class="layui-card-body ly-card-body">
        <div class="ly-metric-label">
          <span><i class="mdi mdi-harddisk"></i> 磁盘使用</span>
          <span><strong><?=_sy_fmt_bytes($sy['disk_used'])?></strong> / <?=_sy_fmt_bytes($sy['disk_total'])?></span>
        </div>
        <div class="progress">
          <div class="progress-bar layui-bg-<?=$sy['disk_pct']>80?'red':($sy['disk_pct']>60?'orange':'green')?>"
               style="width:<?=$sy['disk_pct']?>%"><?=$sy['disk_pct']?>%</div>
        </div>
        <div style="font-size:11px;color:#999;">可用 <?=_sy_fmt_bytes($sy['disk_free'])?></div>
      </div>
      <?php else: ?>
      <div class="layui-card-body ly-card-body" style="text-align:center;color:#999;">
        <div class="ly-metric-label"><span><i class="mdi mdi-harddisk"></i> 磁盘使用</span><span>不可用</span></div>
        <p style="margin:10px 0 0;font-size:12px;">当前环境无法获取磁盘信息</p>
      </div>
      <?php endif; ?>
    </div>
  </div>
  <!-- 内存 -->
  <div class="layui-col-lg4">
    <div class="layui-card">
      <div class="layui-card-body ly-card-body">
        <div class="ly-metric-label">
          <span><i class="mdi mdi-memory"></i> PHP 进程内存</span>
          <span><strong><?=_sy_fmt_bytes($sy['mem_current'])?></strong> / <?=htmlspecialchars($sy['memory_limit'])?></span>
        </div>
        <div class="progress">
          <div class="progress-bar layui-bg-green" style="width:<?=min(100,round($sy['mem_current']/max(1,$sy['mem_peak'])*100))?>%">
            <?=_sy_fmt_bytes($sy['mem_current'])?>
          </div>
        </div>
        <div style="font-size:11px;color:#999;">峰值 <?=_sy_fmt_bytes($sy['mem_peak'])?> | 限制 <?=htmlspecialchars($sy['memory_limit'])?></div>
      </div>
    </div>
  </div>
  <!-- CPU 负载 -->
  <div class="layui-col-lg4">
    <div class="layui-card">
      <div class="layui-card-body ly-card-body">
        <div class="ly-metric-label">
          <span><i class="mdi mdi-cpu-64-bit"></i> CPU 负载</span>
          <?php if ($sy['load_avg']): ?>
            <span><?=number_format($sy['load_avg'][0],2)?> / <?=number_format($sy['load_avg'][1],2)?> / <?=number_format($sy['load_avg'][2],2)?></span>
          <?php else: ?>
            <span style="color:#999;">不可用（仅 Linux）</span>
          <?php endif; ?>
        </div>
        <?php if ($sy['load_avg']): ?>
        <div class="ly-load-row">
          <?php foreach ([1,5,15] as $i => $label): ?>
          <div class="ly-load-item">
            <div class="progress" style="transform:rotate(180deg);">
              <?php $pct = min(100, round($sy['load_avg'][$i] * 100)); ?>
              <div class="progress-bar layui-bg-<?=$pct>70?'red':($pct>40?'orange':'green')?>"
                   style="width:<?=$pct?>%"></div>
            </div>
            <div class="ly-load-tag"><?=$label?>min</div>
          </div>
          <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div style="text-align:center;padding:10px 0;color:#999;font-size:12px;">
          <i class="mdi mdi-information"></i> sys_getloadavg 不可用
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<!-- ====== 系统信息 + PHP信息 ====== -->
<fieldset class="layui-elem-field layui-field-title" style="margin-top:25px;">
  <legend><i class="mdi mdi-information-outline"></i> 系统 &amp; PHP 信息</legend>
</fieldset>

<div class="layui-row layui-col-space15">
  <!-- 系统信息 -->
  <div class="layui-col-lg6">
    <div class="layui-card">
      <div class="layui-card-header">
        <i class="mdi mdi-monitor"></i> 系统信息
        <span class="layui-badge layui-bg-green" style="margin-left:10px;">运行中</span>
      </div>
      <div class="layui-card-body" style="padding:0 15px;">
        <table class="ly-info-tbl">
          <tr><td>操作系统</td><td><?=htmlspecialchars($sy['os'])?></td></tr>
          <tr><td>主机名</td><td><?=htmlspecialchars($sy['hostname'])?></td></tr>
          <tr><td>Web 服务</td><td><?=htmlspecialchars($sy['server_soft'])?></td></tr>
          <tr><td>IP : 端口</td><td><?=htmlspecialchars($sy['server_ip'])?> : <?=$sy['server_port']?></td></tr>
          <tr><td>服务器时间</td><td><?=$sy['server_time']?></td></tr>
          <tr><td>时区</td><td><?=htmlspecialchars($sy['timezone'])?></td></tr>
          <tr><td>数据库版本</td><td><?=htmlspecialchars($sy['db_version'])?></td></tr>
          <tr><td>Web / SQL 版本</td><td><?=$sy['web_version']?> / <?=$sy['sql_version']?></td></tr>
        </table>
      </div>
    </div>
  </div>
  <!-- PHP 信息 -->
  <div class="layui-col-lg6">
    <div class="layui-card">
      <div class="layui-card-header">
        <i class="mdi mdi-language-php"></i> PHP 信息
        <span class="layui-badge layui-bg-blue" style="margin-left:10px;"><?=htmlspecialchars($sy['php_version'])?></span>
      </div>
      <div class="layui-card-body" style="padding:0 15px;">
        <table class="ly-info-tbl">
          <tr><td>PHP 版本</td><td><?=htmlspecialchars($sy['php_version'])?></td></tr>
          <tr><td>运行模式</td><td><?=htmlspecialchars($sy['php_sapi'])?></td></tr>
          <tr><td>内存限制</td><td><?=htmlspecialchars($sy['memory_limit'])?></td></tr>
          <tr><td>最大执行时间</td><td><?=htmlspecialchars($sy['max_exec_time'])?>s</td></tr>
          <tr><td>上传限制</td><td><?=htmlspecialchars($sy['upload_max'])?></td></tr>
          <tr><td>POST 限制</td><td><?=htmlspecialchars($sy['post_max'])?></td></tr>
          <tr><td>已加载扩展</td><td><?=$sy['ext_count']?> 个</td></tr>
          <tr><td>php.ini</td><td style="font-size:11px;word-break:break-all;"><?php $ini=php_ini_loaded_file(); echo htmlspecialchars($ini?:'未加载')?></td></tr>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- ====== 插件组件 ====== -->
<?php
if (function_exists('mnbt_plugin_render_widgets_html')) {
    echo mnbt_plugin_render_widgets_html('admin');
}
?>

</div><!-- /ly-dash -->

<!-- ====== 广告弹窗 ====== -->
<div class="modal fade" id="adModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header bg-info">
        <h5 class="modal-title"><i class="mdi mdi-bullhorn" style="margin-right:6px;"></i>广告</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" id="adModalBody" style="max-height:60vh;overflow-y:auto;"></div>
    </div>
  </div>
</div>

<script>
// 广告弹窗
let data = {};
data["gn"]="gglist";
$.post('./ajax.php', data, function (date) {
    var jsoe= JSON.parse(date);
    if(jsoe && jsoe.length>0){
        var html='<div class="ly-ad-grid">';
        for(var i in jsoe){
            if(jsoe[i].html){
                html+= '<div class="ly-ad-card ly-ad-card--full">'+jsoe[i].html+'</div>';
            }else{
                html+= '<div class="ly-ad-card">'+
                       '<a href="http://'+jsoe[i].url+'/" target="_blank" class="ly-ad-name"><i class="mdi mdi-bullhorn" style="margin-right:4px;"></i>'+jsoe[i].name+'</a>'+
                       '<div class="ly-ad-nr">'+jsoe[i].nr+'</div>'+
                       '</div>';
            }
        }
        html+='</div>';
        document.getElementById("adModalBody").innerHTML = html;
        $('#adModal').modal('show');
    }
});
</script>
</body>
</html>
