<?php
if (!defined('IN_CRONLITE')) { exit; }
$page_title = $page_title ?? '我的订单';
$orders = $orders ?? ['list' => [], 'total' => 0, 'page' => 1, 'per_page' => 15];
$status_labels = ['pending' => '待支付', 'paid' => '已支付', 'opened' => '已开通', 'failed' => '失败', 'cancelled' => '已取消'];
$status_cls = ['pending' => 'warning', 'paid' => 'info', 'opened' => 'success', 'failed' => 'danger', 'cancelled' => 'default'];
$period_labels = hosting_periods();
$tp = max(1, (int)ceil($orders['total'] / max(1, (int)$orders['per_page'])));
$cp = (int)$orders['page'];
ob_start();
?>
<div class="sf-section">
  <div>
    <h1>我的订单</h1>
    <p>主机购买订单记录</p>
  </div>
</div>

<div class="sf-card">
  <div class="sf-card-body-flush">
    <?php if (empty($orders['list'])): ?>
      <div class="sf-empty">暂无订单，<a href="<?= shop_frontend_url('shop') ?>">去购买主机</a></div>
    <?php else: ?>
      <div class="sf-table-wrap">
        <table class="sf-table">
          <thead><tr><th>订单号</th><th>套餐</th><th>周期</th><th>金额</th><th>状态</th><th>下单时间</th><th>备注</th></tr></thead>
          <tbody>
            <?php foreach ($orders['list'] as $o): ?>
              <tr>
                <td class="sf-mono"><?= htmlspecialchars($o['order_no']) ?></td>
                <td><?= htmlspecialchars($o['plan_name']) ?></td>
                <td><?= htmlspecialchars($period_labels[$o['period']]['label'] ?? $o['period']) ?></td>
                <td>¥<?= hosting_format_cents($o['amount_cents']) ?></td>
                <td><span class="sf-badge sf-badge-<?= $status_cls[$o['status']] ?? 'default' ?>"><?= $status_labels[$o['status']] ?? $o['status'] ?></span></td>
                <td><?= htmlspecialchars($o['created_at']) ?></td>
                <td><?= htmlspecialchars($o['remark'] ?: '-') ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php if ($tp > 1): ?>
        <div class="sf-pager">
          <?php if ($cp > 1): ?><a href="<?= shop_frontend_url('shop/orders?page=' . ($cp - 1)) ?>">上一页</a><?php endif; ?>
          <span>第 <?= $cp ?> / <?= $tp ?> 页</span>
          <?php if ($cp < $tp): ?><a href="<?= shop_frontend_url('shop/orders?page=' . ($cp + 1)) ?>">下一页</a><?php endif; ?>
        </div>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
