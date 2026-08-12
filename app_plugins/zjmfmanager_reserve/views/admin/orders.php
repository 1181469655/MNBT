<?php
/**
 * 管理员端 - 订单管理
 */
if (!defined('IN_CRONLITE')) {
	exit;
}
mnbt_admin_include('head');

$page = max(1, (int)($_GET['page_num'] ?? 1));
$filters = [
	'status'      => $_GET['status'] ?? '',
	'order_no'    => $_GET['order_no'] ?? '',
	'user_id'     => $_GET['user_id'] ?? '',
	'supplier_id' => $_GET['supplier_id'] ?? '',
];
$orders = zjmf_order_list_all($page, 30, $filters);
$suppliers = zjmf_supplier_list_all();

$action_labels = [
	'buy'              => '购买',
	'upgrade_config'   => '配置升级',
	'upgrade_product'  => '产品升级',
];
$status_labels = [
	'pending'   => '待处理',
	'paid'      => '已支付',
	'opened'    => '已完成',
	'failed'    => '失败',
	'cancelled' => '已取消',
];
$status_classes = [
	'pending'   => 'badge-secondary',
	'paid'      => 'badge-info',
	'opened'    => 'badge-success',
	'failed'    => 'badge-danger',
	'cancelled' => 'badge-warning',
];
$title = $title ?? '订单管理';
?>
<div class="container-fluid p-t-15">
  <div class="card">
    <div class="card-header"><h4 style="display:inline-block">订单管理</h4></div>
    <div class="card-body">
      <form method="get" class="form-inline mb-3">
        <input type="hidden" name="p" value="zjmfmanager_reserve">
        <input type="hidden" name="page" value="orders">
        <select name="status" class="form-control form-control-sm mr-2">
          <option value="">全部状态</option>
          <?php foreach ($status_labels as $k => $v): ?>
            <option value="<?= htmlspecialchars($k, ENT_QUOTES) ?>"
              <?= $filters['status'] === $k ? 'selected' : '' ?>>
              <?= htmlspecialchars($v) ?>
            </option>
          <?php endforeach; ?>
        </select>
        <select name="supplier_id" class="form-control form-control-sm mr-2">
          <option value="">全部供应商</option>
          <?php foreach ($suppliers as $s): ?>
            <option value="<?= (int)$s['id'] ?>"
              <?= $filters['supplier_id'] == $s['id'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($s['name'], ENT_QUOTES) ?>
            </option>
          <?php endforeach; ?>
        </select>
        <input type="text" name="order_no" class="form-control form-control-sm mr-2"
               placeholder="订单号" value="<?= htmlspecialchars($filters['order_no'], ENT_QUOTES) ?>">
        <input type="number" name="user_id" class="form-control form-control-sm mr-2"
               placeholder="用户 ID" value="<?= htmlspecialchars($filters['user_id'], ENT_QUOTES) ?>">
        <button type="submit" class="btn btn-sm btn-primary">筛选</button>
      </form>

      <div class="table-responsive">
        <table class="table table-bordered table-hover">
          <thead>
            <tr>
              <th>ID</th>
              <th>订单号</th>
              <th>动作</th>
              <th>供应商</th>
              <th>用户ID</th>
              <th>商品</th>
              <th>周期</th>
              <th>金额</th>
              <th>上游订单</th>
              <th>上游主机</th>
              <th>状态</th>
              <th>下单时间</th>
              <th>完成时间</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($orders['list'])): ?>
              <tr><td colspan="13" class="text-center text-muted">暂无订单</td></tr>
            <?php else: ?>
              <?php foreach ($orders['list'] as $o): ?>
                <tr>
                  <td><?= (int)$o['id'] ?></td>
                  <td class="small text-muted"><?= htmlspecialchars($o['order_no'], ENT_QUOTES) ?></td>
                  <td><?= htmlspecialchars($action_labels[$o['action']] ?? $o['action']) ?></td>
                  <td><?= htmlspecialchars($o['supplier_name'] ?: '-') ?></td>
                  <td><?= (int)$o['user_id'] ?></td>
                  <td><?= htmlspecialchars($o['product_name'], ENT_QUOTES) ?></td>
                  <td><?= htmlspecialchars($o['cycle_name'] ?: '-') ?></td>
                  <td>¥<?= zjmf_format_cents($o['amount_cents']) ?></td>
                  <td><?= (int)$o['up_order_id'] > 0 ? (int)$o['up_order_id'] : '-' ?></td>
                  <td><?= (int)$o['up_host_id'] > 0 ? (int)$o['up_host_id'] : '-' ?></td>
                  <td>
                    <span class="badge <?= htmlspecialchars($status_classes[$o['status']] ?? 'badge-secondary') ?>">
                      <?= htmlspecialchars($status_labels[$o['status']] ?? $o['status']) ?>
                    </span>
                  </td>
                  <td class="small"><?= htmlspecialchars($o['created_at']) ?></td>
                  <td class="small"><?= htmlspecialchars($o['opened_at'] ?: '-') ?></td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

      <?php
      $total_pages = max(1, (int)ceil($orders['total'] / $orders['per_page']));
      $current_page = (int)$orders['page'];
      if ($total_pages > 1):
        $qs = http_build_query([
          'p' => 'zjmfmanager_reserve', 'page' => 'orders',
          'status' => $filters['status'], 'order_no' => $filters['order_no'],
          'user_id' => $filters['user_id'], 'supplier_id' => $filters['supplier_id'],
        ]);
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
              第 <?= $current_page ?> / <?= $total_pages ?> 页（共 <?= (int)$orders['total'] ?> 条）
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
