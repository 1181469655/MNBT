<?php
if (!defined('IN_CRONLITE')) { exit; }
$page_title = $page_title ?? '我的订单';
$orders = $orders ?? ['list' => [], 'total' => 0, 'page' => 1, 'per_page' => 15];
$status_labels = ['pending'=>'待支付','paid'=>'已支付','opened'=>'已开通','failed'=>'失败','cancelled'=>'已取消'];
ob_start();
?>
<div class="hs-section"><h1>我的订单</h1><p>主机购买订单记录</p></div>

<div class="layui-card">
  <div class="layui-card-body" style="padding:0;">
    <?php if (empty($orders['list'])): ?>
      <p style="text-align:center;padding:40px;color:#999;">暂无订单，<a href="<?= hosting_url('shop') ?>" style="color:#1e9fff;">去购买主机</a></p>
    <?php else: ?>
      <table class="ly-table">
        <thead><tr><th>订单号</th><th>套餐</th><th>周期</th><th>金额</th><th>状态</th><th>下单时间</th><th>备注</th></tr></thead>
        <tbody>
          <?php foreach ($orders['list'] as $o): ?>
            <tr>
              <td class="ly-mono"><?= htmlspecialchars($o['order_no']) ?></td>
              <td><?= htmlspecialchars($o['plan_name']) ?></td>
              <td><?= $o['period']==='year'?'年付':'月付' ?></td>
              <td>¥<?= hosting_format_cents($o['amount_cents']) ?></td>
              <td><span class="ly-status ly-status-<?= $o['status'] ?>"><?= $status_labels[$o['status']] ?? $o['status'] ?></span></td>
              <td><?= htmlspecialchars($o['created_at']) ?></td>
              <td><?= htmlspecialchars($o['remark']?:'-') ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <?php $tp=max(1,(int)ceil($orders['total']/$orders['per_page']));$cp=(int)$orders['page'];
      if($tp>1): ?>
        <div class="ly-pager">
          <?php if($cp>1):?><a href="<?=hosting_url('shop/orders?page='.($cp-1))?>">上一页</a><?php endif;?>
          <span class="ly-pager-info">第<?=$cp?>/<?=$tp?>页</span>
          <?php if($cp<$tp):?><a href="<?=hosting_url('shop/orders?page='.($cp+1))?>">下一页</a><?php endif;?>
        </div>
      <?php endif;?>
    <?php endif; ?>
  </div>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
