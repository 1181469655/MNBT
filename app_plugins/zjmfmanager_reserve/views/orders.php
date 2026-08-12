<?php
/**
 * 用户端 - 我的订单
 */
if (!defined('IN_CRONLITE')) {
	exit;
}
$page_title = $page_title ?? '我的订单';
$orders = $orders ?? ['list' => [], 'total' => 0, 'page' => 1, 'per_page' => 15];

$action_labels = [
	'buy'             => '购买',
	'upgrade_config'  => '配置升级',
	'upgrade_product' => '产品升级',
];
$status_labels = [
	'pending'   => '待处理',
	'paid'      => '已支付',
	'opened'    => '已完成',
	'failed'    => '失败',
	'cancelled' => '已取消',
];
ob_start();
?>
<div class="zj-section">
  <h1>我的订单</h1>
  <p>购买与升级订单记录</p>
</div>

<?php if (empty($orders['list'])): ?>
  <div class="layui-card">
    <div class="layui-card-body" style="text-align:center;padding:40px;color:#999;">
      暂无订单，<a href="<?= zjmf_url('reserve/shop') ?>">去选购商品</a>
    </div>
  </div>
<?php else: ?>
  <div class="layui-card">
    <div class="layui-card-body" style="padding:0;">
      <table class="zj-table">
        <thead>
          <tr>
            <th>订单号</th>
            <th>类型</th>
            <th>供应商</th>
            <th>商品</th>
            <th>周期</th>
            <th>金额</th>
            <th>状态</th>
            <th>下单时间</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($orders['list'] as $o): ?>
            <tr>
              <td class="zj-mono"><?= htmlspecialchars($o['order_no']) ?></td>
              <td><?= htmlspecialchars($action_labels[$o['action']] ?? $o['action']) ?></td>
              <td><?= htmlspecialchars($o['supplier_name'] ?: '-') ?></td>
              <td><?= htmlspecialchars($o['product_name']) ?></td>
              <td><?= htmlspecialchars($o['cycle_name'] ?: '-') ?></td>
              <td>¥<?= zjmf_format_cents($o['amount_cents']) ?></td>
              <td>
                <span class="zj-status zj-status-<?= htmlspecialchars($o['status']) ?>">
                  <?= htmlspecialchars($status_labels[$o['status']] ?? $o['status']) ?>
                </span>
              </td>
              <td class="small"><?= htmlspecialchars($o['created_at']) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <?php
  $total_pages = max(1, (int)ceil($orders['total'] / $orders['per_page']));
  $current_page = (int)$orders['page'];
  if ($total_pages > 1):
  ?>
    <div class="zj-pager">
      <?php if ($current_page > 1): ?>
        <a href="<?= zjmf_url('reserve/orders?page=' . ($current_page - 1)) ?>">上一页</a>
      <?php endif; ?>
      <span class="zj-pager-info">第 <?= $current_page ?> / <?= $total_pages ?> 页</span>
      <?php if ($current_page < $total_pages): ?>
        <a href="<?= zjmf_url('reserve/orders?page=' . ($current_page + 1)) ?>">下一页</a>
      <?php endif; ?>
    </div>
  <?php endif; ?>
<?php endif; ?>

<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
