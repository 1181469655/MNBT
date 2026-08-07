<?php
if (!defined('IN_CRONLITE')) { exit; }
$page_title = $page_title ?? '购买套餐';
$plan = $plan ?? null;
$methods = $methods ?? [];
$enabled = hosting_plan_enabled_periods($plan);
$is_free_plan = true;
foreach ($enabled as $p) {
  $field = hosting_period_price_field($p);
  if ((int)($plan[$field] ?? 0) > 0) { $is_free_plan = false; break; }
}
ob_start();
?>
<div class="sf-section">
  <div>
    <h1>购买：<?= htmlspecialchars($plan['name']) ?></h1>
    <p>确认购买周期与支付方式</p>
  </div>
  <a href="<?= shop_frontend_url('shop') ?>" class="sf-btn sf-btn-ghost">返回套餐列表</a>
</div>

<div class="sf-card" style="max-width:680px;">
  <div class="sf-card-body">
    <div class="sf-msg" id="msg"></div>

    <ul class="sf-plan-spec" style="margin:6px 0 22px;padding:0 0 10px;">
      <li><span>网页空间</span><b><?= (int)$plan['spec_web'] ?> MB</b></li>
      <li><span>数据库</span><b><?= (int)$plan['spec_sql'] ?> MB</b></li>
      <li><span>流量</span><b><?= (int)$plan['spec_flow'] > 0 ? ((int)$plan['spec_flow'] . ' GB') : '不限' ?></b></li>
      <li><span>域名绑定</span><b><?= (int)$plan['spec_domain'] ?> 个</b></li>
      <li><span>开通节点</span><b><?= htmlspecialchars($plan['node'] ?: '—') ?></b></li>
    </ul>

    <form class="sf-form" id="orderForm">
      <div class="sf-field">
        <label>购买周期</label>
        <div class="sf-choices" id="periodChoices">
          <?php foreach ($enabled as $p): ?>
            <?php
              $cfg = hosting_periods()[$p];
              $field = hosting_period_price_field($p);
              $price = (int)($plan[$field] ?? 0);
            ?>
            <label class="sf-choice">
              <input type="radio" name="period" value="<?= htmlspecialchars($p, ENT_QUOTES) ?>">
              <?= htmlspecialchars($cfg['label']) ?> ¥<?= hosting_format_cents($price) ?>
            </label>
          <?php endforeach; ?>
          <?php if ($enabled === []): ?>
            <span style="color:var(--sf-text-3);">该套餐未设置购买周期</span>
          <?php endif; ?>
        </div>
      </div>

      <?php if (!$is_free_plan): ?>
        <div class="sf-field">
          <label>支付方式</label>
          <div class="sf-choices" id="typeChoices">
            <?php foreach ($methods as $i => $m): ?>
              <label class="sf-choice">
                <input type="radio" name="type" value="<?= htmlspecialchars($m['plugin'] . '__' . $m['method'], ENT_QUOTES) ?>"<?= $i === 0 ? ' checked' : '' ?>>
                <?= htmlspecialchars($m['display_name'] ?: ($m['plugin'] . ' / ' . $m['method'])) ?>
              </label>
            <?php endforeach; ?>
            <?php if (empty($methods)): ?>
              <span style="color:var(--sf-text-3);">暂无可用的支付方式</span>
            <?php endif; ?>
          </div>
        </div>
      <?php endif; ?>

      <?php if ($is_free_plan || !empty($methods)): ?>
        <div>
          <button type="submit" class="sf-btn sf-btn-primary sf-btn-lg" id="submitBtn">确认购买</button>
        </div>
      <?php endif; ?>
    </form>
  </div>
</div>

<script>
(function () {
  var form = document.getElementById('orderForm');
  if (!form) return;
  var btn = document.getElementById('submitBtn');
  function ensureDefault(name) {
    var radios = form.querySelectorAll('input[name="' + name + '"]');
    if (radios.length && !form.querySelector('input[name="' + name + '"]:checked')) {
      radios[0].checked = true;
    }
  }
  ensureDefault('period');
  ensureDefault('type');
  form.addEventListener('change', function () { sfChoice(form); });
  sfChoice(form);

  form.addEventListener('submit', function (e) {
    e.preventDefault();
    btn.disabled = true; btn.textContent = '正在创建订单...'; sfMsg('msg', '', 'error');
    var pc = form.querySelector('input[name="period"]:checked');
    var tc = form.querySelector('input[name="type"]:checked');
    sfPost('<?= shop_frontend_url('shop/api/create_order') ?>', {
      plan_id: '<?= (int)$plan['id'] ?>',
      period: pc ? pc.value : '',
      type: tc ? tc.value : ''
    }).then(function (res) {
      if (res.html) { document.open(); document.write(res.html); document.close(); }
      else if (res.redirect) { window.location.href = res.redirect; }
      else {
        sfMsg('msg', res.code || '创建订单失败', 'error');
        btn.disabled = false; btn.textContent = '确认购买';
      }
    }).catch(function () {
      sfMsg('msg', '网络错误，请重试', 'error');
      btn.disabled = false; btn.textContent = '确认购买';
    });
  });
})();
</script>
<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
