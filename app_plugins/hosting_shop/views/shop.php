<?php
if (!defined('IN_CRONLITE')) { exit; }
$page_title = $page_title ?? '主机套餐';
$plans = $plans ?? [];
$nodes = $nodes ?? [];
ob_start();
?>
<div class="hs-section">
  <h1>主机套餐</h1>
  <p>选择合适的套餐购买，支付完成后自动开通主机</p>
</div>

<?php if (empty($plans)): ?>
  <div class="layui-card"><div class="layui-card-body" style="text-align:center;padding:40px;color:#999;">暂无可购买的套餐，请稍后再来。</div></div>
<?php else: ?>
  <div class="hs-plan-grid">
    <?php foreach ($plans as $plan): ?>
      <div class="hs-plan-card">
        <div class="hs-plan-head">
          <h2><?= htmlspecialchars($plan['name']) ?></h2>
        </div>
        <div class="hs-plan-desc"><?= nl2br(htmlspecialchars($plan['description'])) ?></div>
        <ul class="hs-plan-spec">
          <li><span>网页空间</span><b><?= (int)$plan['spec_web'] ?> MB</b></li>
          <li><span>数据库</span><b><?= (int)$plan['spec_sql'] ?> MB</b></li>
          <li><span>流量</span><b><?= (int)$plan['spec_flow'] > 0 ? ((int)$plan['spec_flow'].' GB') : '不限' ?></b></li>
          <li><span>域名绑定</span><b><?= (int)$plan['spec_domain'] ?> 个</b></li>
        </ul>
        <div class="hs-plan-price">
          <?php
            $enabled = hosting_plan_enabled_periods($plan);
            foreach ($enabled as $p):
              $cfg = hosting_periods()[$p];
              $field = hosting_period_price_field($p);
              $price = (int)($plan[$field] ?? 0);
          ?>
            <div class="hs-price-item"><span class="hs-price-label"><?= htmlspecialchars($cfg['label']) ?></span><span class="hs-price-value">¥<?= hosting_format_cents($price) ?></span></div>
          <?php endforeach; ?>
          <?php if ($enabled === []): ?>
            <span style="color:#999;font-size:12px;">暂无可购买周期</span>
          <?php endif; ?>
        </div>
        <div class="hs-plan-buy"><a class="layui-btn" href="<?= hosting_url('shop/order/'.(int)$plan['id']) ?>">立即购买</a></div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php if (!empty($nodes)): ?>
  <div class="layui-card">
    <div class="layui-card-header" style="font-weight:600;">开通节点</div>
    <div class="layui-card-body" style="padding:0 16px;">
      <ul class="hs-node-list">
        <?php foreach ($nodes as $n): ?>
          <li><span class="hs-node-name"><?= htmlspecialchars($n['btdh']) ?></span></li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>
<?php endif; ?>
<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
