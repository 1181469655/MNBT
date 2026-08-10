<?php
if (!defined('IN_CRONLITE')) { exit; }
$page_title = $page_title ?? 'Docker 商城';
$plans = $plans ?? [];
ob_start();
?>
<div class="hs-section">
  <h1>Docker 商城</h1>
  <p>选择 Docker 容器套餐购买，支付完成后自动开通 Docker 账号，凭账号登录控制台创建容器</p>
</div>

<?php if (empty($plans)): ?>
  <div class="layui-card"><div class="layui-card-body" style="text-align:center;padding:40px;color:#999;">暂无可购买的套餐，请稍后再来。</div></div>
<?php else: ?>
  <div class="hs-plan-grid">
    <?php foreach ($plans as $plan):
      $base_plan = $plan['base_plan_id'] > 0 ? docker_shop_base_plan_get($plan['base_plan_id']) : null;
      $node = $plan['node'] > 0 ? docker_shop_node_get($plan['node']) : null;
    ?>
      <div class="hs-plan-card">
        <div class="hs-plan-head">
          <h2><?= htmlspecialchars($plan['name']) ?></h2>
          <?php if (!empty($plan['category'])): ?><span class="hs-plan-tag"><?= htmlspecialchars($plan['category']) ?></span><?php endif; ?>
        </div>
        <div class="hs-plan-desc"><?= nl2br(htmlspecialchars($plan['description'])) ?></div>
        <?php if ($base_plan): ?>
        <ul class="hs-plan-spec">
          <li><span>CPU 核</span><b><?= htmlspecialchars($base_plan['cpu_max']) ?></b></li>
          <li><span>内存</span><b><?= htmlspecialchars($base_plan['mem_max']) ?> MB</b></li>
          <li><span>磁盘</span><b><?= (int)$base_plan['disk_max'] > 0 ? htmlspecialchars($base_plan['disk_max']) . ' MB' : '不限制' ?></b></li>
          <li><span>节点</span><b><?= $node ? htmlspecialchars($node['name']) : '—' ?></b></li>
        </ul>
        <?php endif; ?>
        <div class="hs-plan-price">
          <?php
            $enabled = docker_shop_plan_enabled_periods($plan);
            foreach ($enabled as $p):
              $cfg = docker_shop_periods()[$p];
              $field = docker_shop_period_price_field($p);
              $price = (int)($plan[$field] ?? 0);
          ?>
            <div class="hs-price-item"><span class="hs-price-label"><?= htmlspecialchars($cfg['label']) ?></span><span class="hs-price-value">¥<?= docker_shop_format_cents($price) ?></span></div>
          <?php endforeach; ?>
          <?php if ($enabled === []): ?>
            <span style="color:#999;font-size:12px;">暂无可购买周期</span>
          <?php endif; ?>
        </div>
        <div class="hs-plan-buy"><a class="layui-btn" href="<?= htmlspecialchars(docker_shop_url('docker-shop/order/'.(int)$plan['id']), ENT_QUOTES) ?>">立即购买</a></div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
