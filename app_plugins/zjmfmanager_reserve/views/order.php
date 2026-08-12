<?php
/**
 * 用户端 - 下单页（选择周期 + 支付方式）
 */
if (!defined('IN_CRONLITE')) {
	exit;
}
$page_title = $page_title ?? '购买商品';
$product = $product ?? null;
$methods = $methods ?? [];
ob_start();
?>
<div class="layui-card">
  <div class="layui-card-body" style="padding:28px;">
    <div class="zj-msg" id="zjf-msg"></div>

    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;">
      <h1 style="font-size:20px;color:#222;margin:0;">购买：<?= htmlspecialchars($product['name']) ?></h1>
      <a class="layui-btn layui-btn-xs layui-btn-primary" href="<?= zjmf_url('reserve/shop') ?>">返回商品列表</a>
    </div>

    <div class="zj-desc"><?= zjmf_render_description($product['description']) ?></div>

    <form class="zj-order-form" id="zjf-order-form">
      <div class="layui-form-item">
        <label class="layui-form-label">购买周期</label>
        <div class="layui-input-block zj-choices" style="padding-top:8px;">
          <?php
            $cycles = zjmf_product_cycles($product);
            foreach ($cycles as $cycle => $cfg):
          ?>
            <label class="zj-choice">
              <input type="radio" name="cycle" value="<?= htmlspecialchars($cycle, ENT_QUOTES) ?>">
              <?= htmlspecialchars($cfg['name']) ?> ¥<?= zjmf_format_cents($cfg['price_cents']) ?>
            </label>
          <?php endforeach; ?>
          <?php if ($cycles === []): ?>
            <span style="color:#999;">该商品未设置可购买周期</span>
          <?php endif; ?>
        </div>
      </div>

      <?php if (!empty($methods)): ?>
        <div class="layui-form-item">
          <label class="layui-form-label">支付方式</label>
          <div class="layui-input-block zj-choices" style="padding-top:6px;">
            <?php foreach ($methods as $m): ?>
              <label class="zj-choice">
                <input type="radio" name="type"
                       value="<?= htmlspecialchars($m['plugin'] . '__' . $m['method'], ENT_QUOTES) ?>" required>
                <?= htmlspecialchars($m['display_name'] ?: ($m['plugin'] . ' / ' . $m['method'])) ?>
              </label>
            <?php endforeach; ?>
          </div>
        </div>
      <?php else: ?>
        <div class="layui-form-item">
          <div class="layui-input-block" style="color:#999;">暂无可用的支付方式</div>
        </div>
      <?php endif; ?>

      <?php if (!empty($methods) && $cycles !== []): ?>
        <div class="layui-form-item">
          <div class="layui-input-block">
            <button type="submit" class="layui-btn layui-btn-lg" id="zjf-submit">确认购买</button>
          </div>
        </div>
      <?php endif; ?>
    </form>
  </div>
</div>

<script>
(function () {
  var form = document.getElementById('zjf-order-form');
  if (!form) return;
  var msg = document.getElementById('zjf-msg');
  var btn = document.getElementById('zjf-submit');

  function showMsg(text, success) {
    msg.textContent = text;
    msg.className = 'zj-msg ' + (success ? 'zj-msg-success' : 'zj-msg-error');
  }
  function checkFirst(name) {
    var list = form.querySelectorAll('input[name="' + name + '"]');
    if (list.length && !form.querySelector('input[name="' + name + '"]:checked')) {
      list[0].checked = true;
    }
  }
  function updateChoices() {
    form.querySelectorAll('.zj-choice').forEach(function (l) { l.classList.remove('active'); });
    form.querySelectorAll('input[type="radio"]:checked').forEach(function (r) {
      var p = r.closest('.zj-choice');
      if (p) p.classList.add('active');
    });
  }
  checkFirst('cycle');
  checkFirst('type');
  form.addEventListener('change', updateChoices);
  updateChoices();

  form.addEventListener('submit', function (e) {
    e.preventDefault();
    if (!btn) return;
    btn.disabled = true;
    btn.textContent = '正在创建订单...';
    msg.className = 'zj-msg';

    var body = new URLSearchParams();
    body.append('product_id', '<?= (int)$product['id'] ?>');
    var c = form.querySelector('input[name="cycle"]:checked');
    body.append('cycle', c ? c.value : '');
    var t = form.querySelector('input[name="type"]:checked');
    body.append('type', t ? t.value : '');

    fetch('<?= zjmf_url('reserve/api/create_order') ?>', {
      method: 'POST',
      headers: {'Content-Type': 'application/x-www-form-urlencoded'},
      body: body.toString()
    }).then(function (r) { return r.json(); }).then(function (res) {
      if (res.html) {
        document.open();
        document.write(res.html);
        document.close();
      } else {
        showMsg(res.code || '创建订单失败', false);
        btn.disabled = false;
        btn.textContent = '确认购买';
      }
    }).catch(function () {
      showMsg('网络错误，请重试', false);
      btn.disabled = false;
      btn.textContent = '确认购买';
    });
  });
})();
</script>
<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
