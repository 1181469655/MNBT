<?php
/**
 * 用户端 - 商品列表（按供应商分组展示）
 */
if (!defined('IN_CRONLITE')) {
	exit;
}
$page_title = $page_title ?? '商品选购';
$products = $products ?? [];

// 按供应商分组
$groups = [];
foreach ($products as $product) {
	$key = (int)$product['supplier_id'];
	if (!isset($groups[$key])) {
		$groups[$key] = [
			'name'  => (string)($product['supplier_name'] ?? '其他供应商'),
			'items' => [],
		];
	}
	$groups[$key]['items'][] = $product;
}
ob_start();
?>
<div class="zj-section">
  <h1>商品选购</h1>
  <p>选择商品与周期，余额支付后自动开通主机</p>
</div>

<?php if (empty($groups)): ?>
  <div class="layui-card">
    <div class="layui-card-body" style="text-align:center;padding:40px;color:#999;">
      暂无可购买的商品，请稍后再来。
    </div>
  </div>
<?php else: ?>
  <?php foreach ($groups as $group): ?>
    <div class="zj-group">
      <div class="zj-group-head">
        <span class="zj-group-name"><?= htmlspecialchars($group['name']) ?></span>
        <span class="zj-tag">供应商</span>
      </div>
      <div class="zj-grid">
        <?php foreach ($group['items'] as $product): ?>
          <div class="zj-card">
            <div class="zj-card-head">
              <h2><?= htmlspecialchars($product['name']) ?></h2>
              <?php if (!empty($product['currency'])): ?>
                <span class="zj-tag"><?= htmlspecialchars($product['currency']) ?></span>
              <?php endif; ?>
            </div>
            <div class="zj-card-desc"><?= $product['description'] ?></div>
            <div class="zj-price">
              <?php $cycles = zjmf_product_cycles($product); ?>
              <?php foreach ($cycles as $cycle => $cfg): ?>
                <div class="zj-price-item">
                  <span class="zj-price-label"><?= htmlspecialchars($cfg['name']) ?></span>
                  <span class="zj-price-value">¥<?= zjmf_format_cents($cfg['price_cents']) ?></span>
                </div>
              <?php endforeach; ?>
              <?php if ($cycles === []): ?>
                <span style="color:#999;font-size:12px;">暂无可购买周期</span>
              <?php endif; ?>
            </div>
            <div class="zj-buy">
              <a class="layui-btn"
                 href="<?= zjmf_url('reserve/product/' . (int)$product['id']) ?>">立即购买</a>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endforeach; ?>
<?php endif; ?>

<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
