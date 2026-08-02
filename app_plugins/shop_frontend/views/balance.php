<?php
if (!defined('IN_CRONLITE')) { exit; }
$page_title = $page_title ?? '我的余额';
$balance_cents = $balance_cents ?? 0;
$logs = $logs ?? ['list' => [], 'total' => 0, 'page' => 1, 'per_page' => 15];
$type_labels = ['recharge' => '充值', 'consume' => '消费', 'refund' => '退款', 'adjust' => '调整'];
$tp = max(1, (int)ceil($logs['total'] / max(1, (int)$logs['per_page'])));
$cp = (int)$logs['page'];
ob_start();
?>
<div class="sf-page-head">
  <h1>我的余额</h1>
  <p>查看余额与交易记录</p>
</div>

<div class="sf-balance-hero">
  <div>
    <div class="sf-balance-label">当前余额</div>
    <div class="sf-balance-amount">¥<?= htmlspecialchars(balance_format($balance_cents)) ?></div>
  </div>
  <div class="sf-balance-actions">
    <a href="<?= shop_frontend_url('balance/recharge') ?>" class="sf-btn sf-btn-primary sf-btn-lg">立即充值</a>
  </div>
</div>

<div class="sf-card" style="margin-top:22px;">
  <div class="sf-card-head">交易记录</div>
  <div class="sf-card-body-flush">
    <?php if (empty($logs['list'])): ?>
      <div class="sf-empty">暂无交易记录</div>
    <?php else: ?>
      <div class="sf-table-wrap">
        <table class="sf-table">
          <thead><tr><th>时间</th><th>类型</th><th>金额</th><th>备注</th><th>订单号</th></tr></thead>
          <tbody>
            <?php foreach ($logs['list'] as $log): ?>
              <tr>
                <td><?= htmlspecialchars($log['created_at']) ?></td>
                <td><span class="sf-badge sf-badge-default"><?= htmlspecialchars($type_labels[$log['type']] ?? $log['type']) ?></span></td>
                <td class="<?= (int)$log['amount'] >= 0 ? 'income' : 'expense' ?>"><?= (int)$log['amount'] >= 0 ? '+' : '' ?>¥<?= htmlspecialchars(balance_format(abs((int)$log['amount']))) ?></td>
                <td><?= htmlspecialchars($log['remark'] ?: '-') ?></td>
                <td class="sf-mono"><?= $log['order_no'] ? htmlspecialchars($log['order_no']) : '-' ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php if ($tp > 1): ?>
        <div class="sf-pager">
          <?php if ($cp > 1): ?><a href="<?= shop_frontend_url('balance?page=' . ($cp - 1)) ?>">上一页</a><?php endif; ?>
          <span>第 <?= $cp ?> / <?= $tp ?> 页</span>
          <?php if ($cp < $tp): ?><a href="<?= shop_frontend_url('balance?page=' . ($cp + 1)) ?>">下一页</a><?php endif; ?>
        </div>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
