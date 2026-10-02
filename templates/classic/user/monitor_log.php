<?php
mnbt_theme_include('head');
if (!function_exists('mlog_build_url')) {
function mlog_build_url($page, $overrides = []) {
    $query = array_merge($_GET, $overrides, ['page' => $page]);
    foreach($query as $k => $v) { if($v === '' || $v === null) unset($query[$k]); }
    return 'monitor_log.php?'.http_build_query($query);
}
}
?>
<div class="container-fluid p-t-15"><div class="row"><div class="col-lg-12"><div class="card">
<div class="card-header"><h4>监控检测日志</h4></div><div class="card-body">
<form class="form-inline m-b-15" method="get" action="monitor_log.php">
  <input type="hidden" name="id" value="<?=$id?>">
  <select class="form-control m-r-5 m-b-5" name="page_size">
    <?php foreach([10,15,25,50,100] as $size): ?>
    <option value="<?=$size?>" <?=$page_size===$size?'selected':''?>>每页<?=$size?>条</option>
    <?php endforeach; ?>
  </select>
  <button class="btn btn-primary m-b-5" type="submit">刷新</button>
  <?php if($id): ?><a class="btn btn-default m-l-5 m-b-5" href="monitor_log.php">查看全部</a><?php endif; ?>
</form>
<div class="table-responsive">
<table class="table table-bordered table-hover"><thead><tr><th>时间</th><th>URL</th><th>状态</th><th>状态码</th><th>耗时</th><th>错误</th><th>通知</th></tr></thead><tbody>
<?php if(empty($logs)): ?><tr><td colspan="7" class="text-center text-muted">暂无日志</td></tr><?php else: foreach($logs as $l): ?>
<tr><td><?=$l['created_at']?></td><td style="max-width:320px;word-break:break-all"><?=htmlspecialchars($l['url'])?></td><td><?=$l['check_status']=='ok'?'<span class="text-success">正常</span>':'<span class="text-danger">异常</span>'?></td><td><?=$l['http_code']?></td><td><?=$l['response_time']?>ms</td><td><?=htmlspecialchars($l['error_message'])?></td><td><?=$l['notified']=='true'?'已通知':'-'?></td></tr>
<?php endforeach; endif; ?>
</tbody></table></div>
<div class="d-flex justify-content-between align-items-center">
  <div class="text-muted">共 <?=$total?> 条，第 <?=$page?> / <?=$total_pages?> 页</div>
  <ul class="pagination m-0">
    <li class="page-item <?=$page<=1?'disabled':''?>"><a class="page-link" href="<?=mlog_build_url(max(1,$page-1))?>">上一页</a></li>
    <?php
    $start = max(1, $page - 2);
    $end = min($total_pages, $page + 2);
    for($p=$start; $p<=$end; $p++):
    ?>
    <li class="page-item <?=$p===$page?'active':''?>"><a class="page-link" href="<?=mlog_build_url($p)?>"><?=$p?></a></li>
    <?php endfor; ?>
    <li class="page-item <?=$page>=$total_pages?'disabled':''?>"><a class="page-link" href="<?=mlog_build_url(min($total_pages,$page+1))?>">下一页</a></li>
  </ul>
</div>
</div></div></div></div></div>
