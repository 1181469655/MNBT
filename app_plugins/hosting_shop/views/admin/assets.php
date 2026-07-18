<?php
/**
 * 管理员端 - 资产管理
 */
if (!defined('IN_CRONLITE')) {
	exit;
}

$page = max(1, (int)($_GET['page_num'] ?? 1));
$assets = hosting_asset_list_all($page, 30);

$status_labels = [
	'active' => '正常',
	'expired' => '已到期',
	'cancelled' => '已取消',
];
$status_classes = [
	'active' => 'badge-success',
	'expired' => 'badge-warning',
	'cancelled' => 'badge-secondary',
];

$title = $title ?? '资产管理';
mnbt_admin_include('head');
?>
<div class="container-fluid p-t-15">
	<div class="card">
		<div class="card-header">
			<h4 style="display:inline-block">资产管理</h4>
		</div>
		<div class="card-body">
			<div class="table-responsive">
				<table class="table table-bordered table-hover">
					<thead>
						<tr>
							<th>ID</th>
							<th>用户ID</th>
							<th>套餐</th>
							<th>主机ID</th>
							<th>主机账号</th>
							<th>控制面板密码</th>
							<th>节点</th>
							<th>宝塔站点ID</th>
							<th>开通时间</th>
							<th>到期时间</th>
							<th>状态</th>
							<th>操作</th>
						</tr>
					</thead>
					<tbody>
						<?php if (empty($assets['list'])): ?>
							<tr><td colspan="12" class="text-center text-muted">暂无资产</td></tr>
						<?php else: ?>
							<?php foreach ($assets['list'] as $a): ?>
								<?php
								$panelUrl = '';
								if (!empty($a['btip']) && !empty($a['btdk'])) {
									$panelUrl = (($a['ptl'] ?? '') === 'true' ? 'https' : 'http') . '://' . $a['btip'] . ':' . $a['btdk'];
								}
								?>
								<tr>
									<td><?= (int)$a['id'] ?></td>
									<td><?= (int)$a['user_id'] ?></td>
									<td><?= htmlspecialchars($a['plan_name'], ENT_QUOTES) ?></td>
									<td><?= (int)$a['host_id'] > 0 ? (int)$a['host_id'] : '-' ?></td>
									<td class="small"><?= htmlspecialchars($a['host_user'] ?? '-', ENT_QUOTES) ?></td>
									<td class="small">
										<?php if (!empty($a['host_pass'])): ?>
											<span class="hs-pass-mask" data-pass="<?= htmlspecialchars($a['host_pass'], ENT_QUOTES) ?>">********</span>
											<button type="button" class="btn btn-xs btn-link hs-toggle-pass" title="显示/隐藏密码">👁</button>
											<button type="button" class="btn btn-xs btn-link hs-copy-btn" data-copy="<?= htmlspecialchars($a['host_pass'], ENT_QUOTES) ?>" title="复制密码">📋</button>
										<?php else: ?>
											-
										<?php endif; ?>
									</td>
									<td><?= htmlspecialchars($a['ssbt'] ?? '-', ENT_QUOTES) ?></td>
									<td class="small text-muted"><?= htmlspecialchars($a['btid'] ?? '-', ENT_QUOTES) ?></td>
									<td class="small"><?= htmlspecialchars($a['data'] ?? $a['created_at']) ?></td>
									<td class="small"><?= htmlspecialchars($a['expire_at']) ?></td>
									<td>
										<span class="badge <?= htmlspecialchars($status_classes[$a['status']] ?? 'badge-secondary', ENT_QUOTES) ?>">
											<?= htmlspecialchars($status_labels[$a['status']] ?? $a['status']) ?>
										</span>
									</td>
									<td>
										<?php if ($panelUrl): ?>
											<a href="<?= htmlspecialchars($panelUrl, ENT_QUOTES) ?>" target="_blank" class="btn btn-xs btn-outline-primary" rel="noopener noreferrer">打开面板</a>
										<?php else: ?>
											<span class="text-muted small">无节点信息</span>
										<?php endif; ?>
									</td>
								</tr>
							<?php endforeach; ?>
						<?php endif; ?>
					</tbody>
				</table>
			</div>

			<?php
			$total_pages = max(1, (int)ceil($assets['total'] / $assets['per_page']));
			$current_page = (int)$assets['page'];
			if ($total_pages > 1):
			?>
				<nav>
					<ul class="pagination pagination-sm">
						<?php if ($current_page > 1): ?>
							<li class="page-item"><a class="page-link" href="plugin.php?p=hosting_shop&page=assets&page_num=<?= $current_page - 1 ?>">上一页</a></li>
						<?php endif; ?>
						<li class="page-item disabled"><span class="page-link">第 <?= $current_page ?> / <?= $total_pages ?> 页（共 <?= (int)$assets['total'] ?> 条）</span></li>
						<?php if ($current_page < $total_pages): ?>
							<li class="page-item"><a class="page-link" href="plugin.php?p=hosting_shop&page=assets&page_num=<?= $current_page + 1 ?>">下一页</a></li>
						<?php endif; ?>
					</ul>
				</nav>
			<?php endif; ?>
		</div>
	</div>
</div>
<style>
.hs-pass-mask{display:inline-block;min-width:64px;user-select:all;font-family:Consolas,Monaco,monospace;}
.hs-pass-mask.revealed{background:#e6f4ff;padding:2px 6px;border-radius:4px;color:#1e9fff;}
.hs-toggle-pass,.hs-copy-btn{padding:0 2px;font-size:14px;line-height:1;}
</style>
<script>
(function(){
  document.querySelectorAll('.hs-toggle-pass').forEach(function(btn){
    btn.addEventListener('click', function(){
      var span = this.parentNode.querySelector('.hs-pass-mask');
      if (!span) return;
      if (span.textContent === '********') {
        span.textContent = span.getAttribute('data-pass');
        span.classList.add('revealed');
      } else {
        span.textContent = '********';
        span.classList.remove('revealed');
      }
    });
  });
  document.querySelectorAll('.hs-copy-btn').forEach(function(btn){
    btn.addEventListener('click', function(){
      var text = this.getAttribute('data-copy');
      if (!text) return;
      if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(text).then(function(){ alert('已复制：' + text); }).catch(function(){ fallbackCopy(text); });
      } else {
        fallbackCopy(text);
      }
    });
  });
  function fallbackCopy(text){
    var ta = document.createElement('textarea');
    ta.value = text; ta.style.position = 'fixed'; ta.style.opacity = '0';
    document.body.appendChild(ta); ta.select();
    try { document.execCommand('copy'); alert('已复制：' + text); }
    catch (err) { alert('复制失败，请手动复制'); }
    document.body.removeChild(ta);
  }
})();
</script>
