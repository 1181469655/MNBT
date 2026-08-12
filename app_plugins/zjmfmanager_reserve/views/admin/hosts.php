<?php
/**
 * 管理员端 - 主机管理
 */
if (!defined('IN_CRONLITE')) {
	exit;
}
mnbt_admin_include('head');

$page = max(1, (int)($_GET['page_num'] ?? 1));
$hosts = zjmf_host_list_all($page, 30);
$status_labels = [
	'active'  => '运行中',
	'suspend' => '已暂停',
	'unknown' => '未知',
];
$status_classes = [
	'active'  => 'badge-success',
	'suspend' => 'badge-danger',
	'unknown' => 'badge-secondary',
];
$title = $title ?? '主机管理';
?>
<div class="container-fluid p-t-15">
  <div class="card">
    <div class="card-header"><h4 style="display:inline-block">主机管理</h4></div>
    <div class="card-body">
      <p class="text-muted">主机映射为开通成功的上游主机。可点击「刷新状态」同步上游实时状态。</p>
      <div class="table-responsive">
        <table class="table table-bordered table-hover">
          <thead>
            <tr>
              <th>ID</th>
              <th>供应商</th>
              <th>用户</th>
              <th>主机名</th>
              <th>上游主机ID</th>
              <th>状态</th>
              <th>周期</th>
              <th>到期时间</th>
              <th>创建时间</th>
              <th>操作</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($hosts['list'])): ?>
              <tr><td colspan="10" class="text-center text-muted">暂无主机</td></tr>
            <?php else: ?>
              <?php foreach ($hosts['list'] as $h): ?>
                <tr>
                  <td><?= (int)$h['id'] ?></td>
                  <td><?= htmlspecialchars($h['supplier_name'] ?: '-') ?></td>
                  <td><?= htmlspecialchars($h['user_name'] ?: ('ID ' . (int)$h['user_id'])) ?></td>
                  <td><?= htmlspecialchars($h['name'], ENT_QUOTES) ?></td>
                  <td><?= (int)$h['up_host_id'] ?></td>
                  <td>
                    <span class="badge <?= htmlspecialchars($status_classes[$h['status']] ?? 'badge-secondary') ?>">
                      <?= htmlspecialchars($status_labels[$h['status']] ?? $h['status']) ?>
                    </span>
                  </td>
                  <td><?= htmlspecialchars($h['cycle'] ?: '-') ?></td>
                  <td><?= htmlspecialchars($h['renew_date'] ?: '-') ?></td>
                  <td class="small"><?= htmlspecialchars($h['created_at']) ?></td>
                  <td>
                    <?php if ((int)$h['up_host_id'] > 0): ?>
                      <button type="button" class="btn btn-sm btn-outline-primary zjf-refresh"
                              data-id="<?= (int)$h['id'] ?>">刷新状态</button>
                    <?php else: ?>
                      <span class="text-muted">-</span>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

      <?php
      $total_pages = max(1, (int)ceil($hosts['total'] / $hosts['per_page']));
      $current_page = (int)$hosts['page'];
      if ($total_pages > 1):
        $qs = http_build_query(['p' => 'zjmfmanager_reserve', 'page' => 'hosts']);
      ?>
        <nav>
          <ul class="pagination pagination-sm">
            <?php if ($current_page > 1): ?>
              <li class="page-item"><a class="page-link"
                href="plugin.php?<?= htmlspecialchars(
                  $qs . '&page_num=' . ($current_page - 1), ENT_QUOTES
                ) ?>">上一页</a></li>
            <?php endif; ?>
            <li class="page-item disabled"><span class="page-link">
              第 <?= $current_page ?> / <?= $total_pages ?> 页（共 <?= (int)$hosts['total'] ?> 条）
            </span></li>
            <?php if ($current_page < $total_pages): ?>
              <li class="page-item"><a class="page-link"
                href="plugin.php?<?= htmlspecialchars(
                  $qs . '&page_num=' . ($current_page + 1), ENT_QUOTES
                ) ?>">下一页</a></li>
            <?php endif; ?>
          </ul>
        </nav>
      <?php endif; ?>
    </div>
  </div>
</div>
<script>
document.querySelectorAll('.zjf-refresh').forEach(function (btn) {
  btn.addEventListener('click', function () {
    var self = this;
    self.disabled = true;
    self.textContent = '刷新中...';
    $.post('ajax.php', {gn: 'p_zjmf_admin_fetch_host', id: self.getAttribute('data-id')}, function (res) {
      var d;
      try { d = typeof res === 'string' ? JSON.parse(res) : res; } catch (e) { d = {code: res}; }
      var ok = d.qk == 1 || d.success;
      if (typeof $.notify === 'function') {
        $.notify({message: d.msg || d.code || '完成'}, {type: ok ? 'success' : 'danger'});
      } else {
        alert(d.msg || d.code || '完成');
      }
      setTimeout(function () { location.reload(); }, 600);
    });
  });
});
</script>
