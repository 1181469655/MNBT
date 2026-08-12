<?php
/**
 * 管理员端 - 操作日志
 */
if (!defined('IN_CRONLITE')) {
	exit;
}
mnbt_admin_include('head');

$page = max(1, (int)($_GET['page_num'] ?? 1));
$logs = zjmf_log_list_all($page, 30);
$result_labels = [
	'success' => '成功',
	'failed'  => '失败',
];
$result_classes = [
	'success' => 'badge-success',
	'failed'  => 'badge-danger',
];
$title = $title ?? '操作日志';
?>
<div class="container-fluid p-t-15">
  <div class="card">
    <div class="card-header"><h4 style="display:inline-block">操作日志</h4></div>
    <div class="card-body">
      <p class="text-muted">记录商品同步、开通、主机操作、升级等关键动作（内容已脱敏，不含密码/密钥）。</p>
      <div class="table-responsive">
        <table class="table table-bordered table-hover">
          <thead>
            <tr>
              <th>ID</th>
              <th>供应商</th>
              <th>用户</th>
              <th>订单号</th>
              <th>操作</th>
              <th>结果</th>
              <th>内容</th>
              <th>时间</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($logs['list'])): ?>
              <tr><td colspan="8" class="text-center text-muted">暂无日志</td></tr>
            <?php else: ?>
              <?php foreach ($logs['list'] as $l): ?>
                <tr>
                  <td><?= (int)$l['id'] ?></td>
                  <td><?= htmlspecialchars($l['supplier_name'] ?: '-') ?></td>
                  <td><?= htmlspecialchars($l['user_name'] ?: ('ID ' . (int)$l['user_id'])) ?></td>
                  <td class="small text-muted"><?= htmlspecialchars($l['order_no'] ?: '-') ?></td>
                  <td><?= htmlspecialchars($l['action']) ?></td>
                  <td>
                    <span class="badge <?= htmlspecialchars($result_classes[$l['result']] ?? 'badge-secondary') ?>">
                      <?= htmlspecialchars($result_labels[$l['result']] ?? $l['result']) ?>
                    </span>
                  </td>
                  <td class="small"><?= htmlspecialchars($l['content'] ?: '-') ?></td>
                  <td class="small"><?= htmlspecialchars($l['created_at']) ?></td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

      <?php
      $total_pages = max(1, (int)ceil($logs['total'] / $logs['per_page']));
      $current_page = (int)$logs['page'];
      if ($total_pages > 1):
        $qs = http_build_query(['p' => 'zjmfmanager_reserve', 'page' => 'logs']);
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
              第 <?= $current_page ?> / <?= $total_pages ?> 页（共 <?= (int)$logs['total'] ?> 条）
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
