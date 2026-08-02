<?php
if (!defined('IN_CRONLITE')) { exit; }
$page_title = $page_title ?? '主机套餐';
$plans = $plans ?? [];
ob_start();
?>
<div class="sf-section">
  <div>
    <h1>主机套餐</h1>
    <p>选择合适的套餐购买，支付完成后自动开通主机</p>
  </div>
</div>

<?php if (empty($plans)): ?>
  <div class="sf-card"><div class="sf-empty">暂无可购买的套餐，请稍后再来。</div></div>
<?php else: ?>
  <div class="sf-plan-grid">
    <?php foreach ($plans as $plan): ?>
      <?php $enabled = hosting_plan_enabled_periods($plan); ?>
      <div class="sf-plan-card">
        <div class="sf-plan-head">
          <h2><?= htmlspecialchars($plan['name']) ?></h2>
          <?php if (!empty($plan['category'])): ?><span class="sf-plan-tag"><?= htmlspecialchars($plan['category']) ?></span><?php endif; ?>
        </div>
        <div class="sf-plan-desc"><?= nl2br(htmlspecialchars($plan['description'])) ?></div>
        <ul class="sf-plan-spec">
          <li><span>网页空间</span><b><?= (int)$plan['spec_web'] ?> MB</b></li>
          <li><span>数据库</span><b><?= (int)$plan['spec_sql'] ?> MB</b></li>
          <li><span>流量</span><b><?= (int)$plan['spec_flow'] > 0 ? ((int)$plan['spec_flow'] . ' GB') : '不限' ?></b></li>
          <li><span>域名绑定</span><b><?= (int)$plan['spec_domain'] ?> 个</b></li>
        </ul>
        <div class="sf-plan-price">
          <?php foreach ($enabled as $p): ?>
            <?php
              $cfg = hosting_periods()[$p];
              $field = hosting_period_price_field($p);
              $price = (int)($plan[$field] ?? 0);
            ?>
            <div class="sf-price-item"><span class="sf-price-label"><?= htmlspecialchars($cfg['label']) ?></span><span class="sf-price-value">¥<?= hosting_format_cents($price) ?></span></div>
          <?php endforeach; ?>
          <?php if ($enabled === []): ?>
            <span style="color:var(--sf-text-3);font-size:13px;">暂无可购买周期</span>
          <?php endif; ?>
        </div>
        <div class="sf-plan-buy">
          <?php if ($enabled !== []): ?>
            <a href="<?= shop_frontend_url('shop/order/' . (int)$plan['id']) ?>" class="sf-btn sf-btn-primary">立即购买</a>
          <?php endif; ?>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
