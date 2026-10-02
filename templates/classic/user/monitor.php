<?php mnbt_theme_include('head'); ?>
<script type="text/javascript" src="<?=mnbt_asset_url('js/jquery-confirm/jquery-confirm.min.js')?>"></script>
<div class="container-fluid p-t-15">
  <div class="row">
    <div class="col-lg-12">
      <div class="card">
        <div class="card-header"><h4>监控任务</h4></div>
        <div class="card-body">
          <button class="btn btn-primary m-b-10" onclick="openMonitor()"><i class="mdi mdi-plus"></i> 添加监控</button>
          <span class="text-muted m-l-10">已添加 <?=$task_count?> / 5 个监控任务</span>
          <div class="table-responsive">
            <table class="table table-bordered table-hover">
              <thead><tr><th>ID</th><th>名称</th><th>类型</th><th>监控对象</th><th>间隔</th><th>规则</th><th>状态</th><th>最近结果</th><th>操作</th></tr></thead>
              <tbody>
              <?php if(empty($tasks)): ?>
              <tr><td colspan="9" class="text-center text-muted">暂无监控任务</td></tr>
              <?php else: foreach($tasks as $t): ?>
              <tr>
                <td><?=$t['id']?></td>
                <td><?=htmlspecialchars($t['name'])?></td>
                <td><?=$t['task_type']=='resource'?'资源监控':'URL监控'?></td>
                <td style="max-width:260px;word-break:break-all"><?=$t['task_type']=='resource'?monitor_resource_name($t['resource_type']).' > '.$t['resource_threshold'].'%':htmlspecialchars($t['url'])?></td>
                <td><?=$t['task_type']=='resource'?'3分钟':$t['interval_seconds'].'秒'?></td>
                <td><?=$t['task_type']=='resource'?'超过阈值告警':'状态码 '.$t['status_rule'].' '.htmlspecialchars($t['status_value']).'<br>内容 '.$t['content_rule'].' '.htmlspecialchars($t['content_value'])?></td>
                <td><?=$t['enabled']=='true'?'<span class="text-success">启用</span>':'<span class="text-muted">停用</span>'?></td>
                <td><?=$t['last_status']=='ok'?'<span class="text-success">正常</span>':'<span class="text-danger">异常</span>'?> / <?=$t['last_code'] ?: '-'?></td>
                <td>
                  <button class="btn btn-xs btn-primary" onclick='openMonitor(<?=json_encode($t, JSON_UNESCAPED_UNICODE)?>)'>修改</button>
                  <button class="btn btn-xs btn-warning" onclick="toggleMonitor(<?=$t['id']?>,'<?=$t['enabled']=='true'?'false':'true'?>')"><?=$t['enabled']=='true'?'停用':'启用'?></button>
                  <button class="btn btn-xs btn-danger" onclick="delMonitor(<?=$t['id']?>)">删除</button>
                  <a class="btn btn-xs btn-default" href="monitor_log.php?id=<?=$t['id']?>">日志</a>
                </td>
              </tr>
              <?php endforeach; endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="monitorModal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document"><div class="modal-content">
    <div class="modal-header"><h6 class="modal-title">监控任务</h6><button type="button" class="close" data-dismiss="modal"><span>&times;</span></button></div>
    <div class="modal-body">
      <input type="hidden" id="mid" value="0">
      <div class="form-group"><label>任务名称</label><input class="form-control" id="mname"></div>
      <div class="form-group"><label>任务类型</label><select class="form-control" id="mtasktype" onchange="changeMonitorType()"><option value="url">URL监控</option><option value="resource">资源监控</option></select></div>
      <div id="url-box" class="form-group"><label>URL</label><input class="form-control" id="murl" placeholder="https://example.com/"></div>
      <div id="resource-box" style="display:none">
        <div class="form-row">
          <div class="form-group col-md-6"><label>资源类型</label><select class="form-control" id="mresource"><option value="web">网页空间</option><option value="sql">数据库空间</option><option value="traffic">本月流量</option></select></div>
          <div class="form-group col-md-6"><label>超过百分比告警</label><input type="number" min="1" max="100" class="form-control" id="mthreshold" value="80"></div>
        </div>
      </div>
      <div class="form-row url-setting-box">
        <div class="form-group col-md-3 url-rule-box"><label>方法</label><select class="form-control" id="mmethod"><option>GET</option><option>POST</option><option>HEAD</option></select></div>
        <div class="form-group col-md-3"><label>间隔秒数（最低15秒）</label><input type="number" min="15" class="form-control" id="minterval" value="60"></div>
        <div class="form-group col-md-3"><label>超时秒数</label><input type="number" min="1" max="30" class="form-control" id="mtimeout" value="10"></div>
        <div class="form-group col-md-3"><label>失败几次告警</label><input type="number" min="1" class="form-control" id="mfail" value="1"></div>
      </div>
      <div class="form-row url-rule-box">
        <div class="form-group col-md-4"><label>状态码规则</label><select class="form-control" id="mstatusrule"><option value="eq">等于</option><option value="neq">不等于</option><option value="in">包含</option><option value="not_in">不包含</option><option value="range">范围</option><option value="gte">大于等于</option><option value="lte">小于等于</option></select></div>
        <div class="form-group col-md-8"><label>状态码值</label><input class="form-control" id="mstatusvalue" value="200" placeholder="200 或 200,301 或 200-399"></div>
      </div>
      <div class="form-row url-rule-box">
        <div class="form-group col-md-4"><label>内容规则</label><select class="form-control" id="mcontentrule"><option value="none">不检测</option><option value="contains">包含</option><option value="not_contains">不包含</option></select></div>
        <div class="form-group col-md-8"><label>内容关键词</label><input class="form-control" id="mcontentvalue"></div>
      </div>
      <div class="form-row">
        <div class="form-group col-md-6"><label>邮件通知</label><select class="form-control" id="mnotify"><option value="true">开启</option><option value="false">关闭</option></select></div>
        <div class="form-group col-md-6"><label>任务状态</label><select class="form-control" id="menabled"><option value="true">启用</option><option value="false">停用</option></select></div>
      </div>
    </div>
    <div class="modal-footer"><button class="btn btn-default" data-dismiss="modal">关闭</button><button class="btn btn-primary" onclick="saveMonitor()">保存</button></div>
  </div></div>
</div>
<script>
function openMonitor(t){
  t=t||{}; if(!t.id && <?=$task_count?> >= 5){msalert(4,'每个用户最多只能添加5个监控任务',3000);return;} $('#mid').val(t.id||0); $('#mname').val(t.name||''); $('#mtasktype').val(t.task_type||'url'); $('#murl').val(t.url||''); $('#mresource').val(t.resource_type||'web'); $('#mthreshold').val(t.resource_threshold||80); $('#mmethod').val(t.method||'GET'); $('#minterval').val(t.interval_seconds||60); $('#mtimeout').val(t.timeout_seconds||10); $('#mfail').val(t.fail_threshold||1); $('#mstatusrule').val(t.status_rule||'eq'); $('#mstatusvalue').val(t.status_value||'200'); $('#mcontentrule').val(t.content_rule||'none'); $('#mcontentvalue').val(t.content_value||''); $('#mnotify').val(t.notify_email||'true'); $('#menabled').val(t.enabled||'true'); changeMonitorType(); $('#monitorModal').modal('show');
}
function changeMonitorType(){var type=$('#mtasktype').val(); if(type=='resource'){$('#url-box').hide();$('#resource-box').show();$('.url-setting-box').hide();$('.url-rule-box').hide();}else{$('#url-box').show();$('#resource-box').hide();$('.url-setting-box').show();$('.url-rule-box').show();}}
function saveMonitor(){
  var data={gn:'monitor_save',id:$('#mid').val(),name:$('#mname').val(),task_type:$('#mtasktype').val(),url:$('#murl').val(),resource_type:$('#mresource').val(),resource_threshold:$('#mthreshold').val(),method:$('#mmethod').val(),interval_seconds:$('#minterval').val(),timeout_seconds:$('#mtimeout').val(),fail_threshold:$('#mfail').val(),status_rule:$('#mstatusrule').val(),status_value:$('#mstatusvalue').val(),content_rule:$('#mcontentrule').val(),content_value:$('#mcontentvalue').val(),notify_email:$('#mnotify').val(),enabled:$('#menabled').val()};
  msloading('正在保存...'); $.post('./ajax.php',data,function(r){var j=JSON.parse(r); if(j.code=='保存成功'||j.code=='添加成功'){msalert(1,j.code,1500);setTimeout(function(){location.reload()},1500)}else{msalert(4,j.code,3000);msloadingde();}});
}
function toggleMonitor(id,en){$.post('./ajax.php',{gn:'monitor_toggle',id:id,enabled:en},function(r){var j=JSON.parse(r);msalert(j.code=='修改成功'?1:4,j.code,1500);setTimeout(function(){location.reload()},1500);});}
function delMonitor(id){$.confirm({title:'删除监控',content:'确定删除该监控任务？',type:'red',buttons:{ok:{text:'删除',btnClass:'btn-danger',action:function(){ $.post('./ajax.php',{gn:'monitor_del',id:id},function(r){var j=JSON.parse(r);msalert(j.code=='删除成功'?1:4,j.code,1500);setTimeout(function(){location.reload()},1500);});}},cancel:{text:'取消'}}});}
</script>
