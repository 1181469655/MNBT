<?php
if (!defined('IN_CRONLITE')) { exit; }
$page_title = $page_title ?? '我的余额';
$balance_cents = $balance_cents ?? 0;
$logs = $logs ?? ['list' => [], 'total' => 0, 'page' => 1, 'per_page' => 15];
$type_labels = ['recharge' => '充值', 'consume' => '消费', 'refund' => '退款', 'adjust' => '调整'];
ob_start();
?>
<div class="layui-card">
  <div class="layui-card-body bal-box">
    <div class="bal-label">当前余额</div>
    <div class="bal-amount">¥<?= htmlspecialchars(balance_format($balance_cents)) ?></div>
    <a class="layui-btn layui-btn-lg" href="<?= balance_url('balance/recharge') ?>">充值</a>
  </div>
</div>

<div class="layui-card">
  <div class="layui-card-header" style="font-weight:600;">交易记录</div>
  <div class="layui-card-body" style="padding:0;">
    <?php if (empty($logs['list'])): ?>
      <p style="text-align:center;padding:30px;color:#999;">暂无交易记录</p>
    <?php else: ?>
      <table class="ly-table">
        <thead><tr><th>时间</th><th>类型</th><th>金额</th><th>备注</th><th>订单号</th></tr></thead>
        <tbody>
          <?php foreach ($logs['list'] as $log): ?>
            <tr>
              <td><?= htmlspecialchars($log['created_at']) ?></td>
              <td><?= htmlspecialchars($type_labels[$log['type']] ?? $log['type']) ?></td>
              <td class="<?= (int)$log['amount'] >= 0 ? 'income' : 'expense' ?>"><?= (int)$log['amount'] >= 0 ? '+' : '' ?>¥<?= htmlspecialchars(balance_format(abs((int)$log['amount']))) ?></td>
              <td><?= htmlspecialchars($log['remark'] ?: '-') ?></td>
              <td class="ly-mono"><?= $log['order_no'] ? htmlspecialchars($log['order_no']) : '-' ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <?php $tp = max(1, (int)ceil($logs['total'] / $logs['per_page'])); $cp = (int)$logs['page'];
      if ($tp > 1): ?>
        <div class="ly-pager">
          <?php if ($cp > 1): ?><a href="<?= balance_url('balance?page=' . ($cp-1)) ?>">上一页</a><?php endif; ?>
          <span class="ly-pager-info">第 <?= $cp ?> / <?= $tp ?> 页</span>
          <?php if ($cp < $tp): ?><a href="<?= balance_url('balance?page=' . ($cp+1)) ?>">下一页</a><?php endif; ?>
        </div>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
