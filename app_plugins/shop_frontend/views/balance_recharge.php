<?php
if (!defined('IN_CRONLITE')) { exit; }
$page_title = $page_title ?? '余额充值';
$methods = $methods ?? [];
ob_start();
?>
<div class="sf-page-head">
  <h1>余额充值</h1>
  <p>选择支付方式并输入充值金额</p>
</div>

<div class="sf-card" style="max-width:620px;">
  <div class="sf-card-body">
    <div class="sf-msg" id="msg"></div>

    <?php if (empty($methods)): ?>
      <div class="sf-empty">暂无可用的支付方式，请联系管理员启用支付插件。</div>
    <?php else: ?>
      <form class="sf-form" id="rechargeForm">
        <div class="sf-field">
          <label>支付方式</label>
          <div class="sf-choices" id="methodChoices">
            <?php foreach ($methods as $i => $m): ?>
              <?php $v = htmlspecialchars($m['plugin'] . '__' . $m['method'], ENT_QUOTES); ?>
              <label class="sf-choice">
                <input type="radio" name="type" value="<?= $v ?>"<?= $i === 0 ? ' checked' : '' ?>>
                <?= htmlspecialchars($m['display_name'] ?: ($m['plugin'] . ' / ' . $m['method'])) ?>
              </label>
            <?php endforeach; ?>
          </div>
        </div>

        <div class="sf-field">
          <label for="amount">充值金额</label>
          <input type="number" id="amount" name="amount" step="0.01" min="1" max="50000" required placeholder="最低 1 元">
        </div>

        <div class="sf-field">
          <label>快捷金额</label>
          <div class="sf-quick-btns">
            <button type="button" data-v="10">10 元</button>
            <button type="button" data-v="50">50 元</button>
            <button type="button" data-v="100">100 元</button>
            <button type="button" data-v="500">500 元</button>
          </div>
        </div>

        <div class="sf-form-actions">
          <button type="submit" class="sf-btn sf-btn-primary sf-btn-lg" id="submitBtn">立即充值</button>
          <a href="<?= shop_frontend_url('balance') ?>" class="sf-btn sf-btn-ghost sf-btn-lg">返回余额页</a>
        </div>
      </form>
    <?php endif; ?>
  </div>
</div>

<script>
(function () {
  var form = document.getElementById('rechargeForm');
  if (!form) return;
  var btn = document.getElementById('submitBtn');
  var quick = document.querySelectorAll('.sf-quick-btns button');
  var amountInput = document.getElementById('amount');
  quick.forEach(function (b) {
    b.addEventListener('click', function () { amountInput.value = b.getAttribute('data-v'); });
  });
  form.addEventListener('change', function () { sfChoice(form); });
  sfChoice(form);

  form.addEventListener('submit', function (e) {
    e.preventDefault();
    btn.disabled = true; btn.textContent = '正在创建订单...'; sfMsg('msg', '', 'error');
    var checked = form.querySelector('input[name="type"]:checked');
    sfPost('<?= shop_frontend_url('balance/api/create_recharge') ?>', {
      amount: amountInput.value,
      type: checked ? checked.value : ''
    }).then(function (res) {
      if (res.html) { document.open(); document.write(res.html); document.close(); }
      else {
        sfMsg('msg', res.code || '创建订单失败', 'error');
        btn.disabled = false; btn.textContent = '立即充值';
      }
    }).catch(function () {
      sfMsg('msg', '网络错误，请重试', 'error');
      btn.disabled = false; btn.textContent = '立即充值';
    });
  });
})();
</script>
<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
