<?php
if (!defined('IN_CRONLITE')) { exit; }
$page_title = $page_title ?? '余额充值';
$methods = $methods ?? [];
ob_start();
?>
<div class="layui-card">
  <div class="layui-card-body">
    <div class="ly-msg" id="msg"></div>

    <form class="layui-form" id="rechargeForm">
      <h1 style="font-size:20px;color:#222;margin:0 0 4px;">余额充值</h1>
      <p style="color:#999;font-size:14px;margin:0 0 20px;">选择支付方式并输入充值金额</p>

      <?php if (empty($methods)): ?>
        <p style="text-align:center;padding:30px;color:#999;">暂无可用的支付方式，请联系管理员启用支付插件。</p>
      <?php else: ?>
        <div class="layui-form-item">
          <label class="layui-form-label">支付方式</label>
          <div class="layui-input-block"><div class="ly-pay-methods">
            <?php foreach ($methods as $m): ?>
              <label><input type="radio" name="type" value="<?= htmlspecialchars($m['plugin'].'__'.$m['method']) ?>" required> <?= htmlspecialchars($m['display_name'] ?: ($m['plugin'].' / '.$m['method'])) ?></label>
            <?php endforeach; ?>
          </div></div>
        </div>

        <div class="layui-form-item">
          <label class="layui-form-label">充值金额</label>
          <div class="layui-input-block"><input type="number" id="amount" name="amount" step="0.01" min="1" max="50000" required placeholder="最低 1 元" class="layui-input" style="max-width:260px;"></div>
        </div>

        <div class="layui-form-item">
          <div class="layui-input-block"><div class="ly-quick-btns">
            <button type="button" data-v="10">10 元</button>
            <button type="button" data-v="50">50 元</button>
            <button type="button" data-v="100">100 元</button>
            <button type="button" data-v="500">500 元</button>
          </div></div>
        </div>

        <div class="layui-form-item">
          <div class="layui-input-block">
            <button type="submit" class="layui-btn layui-btn-lg" id="submitBtn">立即充值</button>
            <a href="<?= balance_url('balance') ?>" class="layui-btn layui-btn-primary layui-btn-lg" style="margin-left:10px;">返回余额页</a>
          </div>
        </div>
      <?php endif; ?>
    </form>
  </div>
</div>

<script>
(function () {
  var form = document.getElementById('rechargeForm');
  if (!form) return;
  var msg = document.getElementById('msg');
  var btn = document.getElementById('submitBtn');
  function showMsg(text, type) { msg.textContent = text; msg.className = 'ly-msg show ' + (type === 'success' ? 'ly-msg-success' : 'ly-msg-error'); }
  document.querySelectorAll('.ly-quick-btns button').forEach(function(b){b.addEventListener('click',function(){document.getElementById('amount').value=b.getAttribute('data-v');});});
  form.addEventListener('submit', function(e){
    e.preventDefault(); btn.disabled = true; btn.textContent = '正在创建订单...'; msg.className = 'ly-msg';
    var body = new URLSearchParams();
    body.append('amount', document.getElementById('amount').value);
    var checked = form.querySelector('input[name="type"]:checked');
    body.append('type', checked ? checked.value : '');
    fetch('<?= balance_url('balance/api/create_recharge') ?>', { method: 'POST', headers: {'Content-Type':'application/x-www-form-urlencoded'}, body: body.toString() })
      .then(function(r){return r.json();})
      .then(function(res){
        if (res.html) { document.open(); document.write(res.html); document.close(); }
        else { showMsg(res.code || '创建订单失败', 'error'); btn.disabled = false; btn.textContent = '立即充值'; }
      })
      .catch(function(){showMsg('网络错误，请重试','error');btn.disabled=false;btn.textContent='立即充值';});
  });
})();
</script>
<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
