<?php
mnbt_theme_include('head');
if (!function_exists('notice_build_url')) {
function notice_build_url($page, $overrides = []) {
    $query = array_merge($_GET, $overrides, ['page' => $page]);
    foreach($query as $key => $value) {
        if($value === '' || $value === null) unset($query[$key]);
    }
    return 'notice.php?'.http_build_query($query);
}
}
?>
<div class="container-fluid p-t-15"><div class="row"><div class="col-lg-12"><div class="card">
<div class="card-header"><h4>通知日志</h4><ul class="card-actions"><li><button class="btn btn-xs btn-primary" onclick="readAll()">全部已读</button></li></ul></div>
<div class="card-body">
<form class="form-inline m-b-15" method="get" action="notice.php">
  <select class="form-control m-r-5 m-b-5" name="type">
    <option value="">全部类型</option>
    <?php foreach(['monitor'=>'监控','expire'=>'到期','traffic'=>'流量'] as $k=>$v): ?>
    <option value="<?=$k?>" <?=$type===$k?'selected':''?>><?=$v?></option>
    <?php endforeach; ?>
  </select>
  <select class="form-control m-r-5 m-b-5" name="level">
    <option value="">全部等级</option>
    <?php foreach(['info'=>'信息','success'=>'成功','warning'=>'警告','danger'=>'危险'] as $k=>$v): ?>
    <option value="<?=$k?>" <?=$level===$k?'selected':''?>><?=$v?></option>
    <?php endforeach; ?>
  </select>
  <select class="form-control m-r-5 m-b-5" name="read">
    <option value="">全部状态</option>
    <option value="false" <?=$read==='false'?'selected':''?>>未读</option>
    <option value="true" <?=$read==='true'?'selected':''?>>已读</option>
  </select>
  <select class="form-control m-r-5 m-b-5" name="page_size">
    <?php foreach([10,15,25,50,100] as $size): ?>
    <option value="<?=$size?>" <?=$page_size===$size?'selected':''?>>每页<?=$size?>条</option>
    <?php endforeach; ?>
  </select>
  <input class="form-control m-r-5 m-b-5" name="keyword" value="<?=htmlspecialchars($keyword)?>" placeholder="标题/内容关键词">
  <button class="btn btn-primary m-r-5 m-b-5" type="submit">筛选</button>
  <a class="btn btn-default m-b-5" href="notice.php">重置</a>
</form>
<div class="table-responsive">
<table class="table table-bordered table-hover"><thead><tr><th>时间</th><th>类型</th><th>等级</th><th>标题</th><th>内容</th><th>状态</th><th>操作</th></tr></thead><tbody>
<?php if(empty($logs)): ?><tr><td colspan="7" class="text-center text-muted">暂无通知</td></tr><?php else: foreach($logs as $l): ?>
<?php $is_read = ($l['is_read'] ?? 'false') === 'true'; ?>
<tr><td><?=htmlspecialchars($l['created_at'] ?? '')?></td><td><?=htmlspecialchars($l['type'] ?? '')?></td><td><?=htmlspecialchars($l['level'] ?? 'info')?></td><td><?=htmlspecialchars($l['title'] ?? '')?></td><td><?=htmlspecialchars($l['content'] ?? '')?></td><td><?=$is_read?'已读':'<span class="text-danger">未读</span>'?></td><td><?=$is_read?'-':'<button class="btn btn-xs btn-primary" onclick="readNotice('.intval($l['id'] ?? 0).')">已读</button>'?></td></tr>
<?php endforeach; endif; ?>
</tbody></table></div>
<div class="d-flex justify-content-between align-items-center">
  <div class="text-muted">共 <?=$total?> 条，第 <?=$page?> / <?=$total_pages?> 页</div>
  <ul class="pagination m-0">
    <li class="page-item <?=$page<=1?'disabled':''?>"><a class="page-link" href="<?=notice_build_url(max(1,$page-1))?>">上一页</a></li>
    <?php
    $start = max(1, $page - 2);
    $end = min($total_pages, $page + 2);
    for($p=$start; $p<=$end; $p++):
    ?>
    <li class="page-item <?=$p===$page?'active':''?>"><a class="page-link" href="<?=notice_build_url($p)?>"><?=$p?></a></li>
    <?php endfor; ?>
    <li class="page-item <?=$page>=$total_pages?'disabled':''?>"><a class="page-link" href="<?=notice_build_url(min($total_pages,$page+1))?>">下一页</a></li>
  </ul>
</div>
</div></div></div></div></div>
<script>
function readNotice(id){$.post('./ajax.php',{gn:'notice_read',id:id},function(r){var j=JSON.parse(r);msalert(j.code=='操作成功'?1:4,j.code,1500);setTimeout(function(){location.reload()},1500);});}
function readAll(){readNotice(0);}
</script>
