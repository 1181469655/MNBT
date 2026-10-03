<?php
if (!defined('IN_CRONLITE')) { exit; }
$page_title = $page_title ?? '续费主机';
$asset = $asset ?? null; $plan = $plan ?? null; $methods = $methods ?? [];
ob_start();
?>
<div class="layui-card">
  <div class="layui-card-body" style="padding:28px;">
    <div class="ly-msg" id="msg"></div>

    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;">
      <h1 style="font-size:20px;color:#222;margin:0;">续费：<?= htmlspecialchars($asset['plan_name'] ?: '虚拟主机') ?></h1>
      <a class="layui-btn layui-btn-xs layui-btn-primary" href="<?= hosting_url('shop/assets') ?>">返回我的主机</a>
    </div>

    <?php if (!$plan): ?>
      <p style="color:#999;">原套餐已不存在，无法在线续费，请联系管理员处理。</p>
    <?php else: ?>
      <ul class="hs-plan-spec" style="margin-bottom:20px;">
        <li><span>主机账号</span><b><?= htmlspecialchars($asset['host_user'] ?? '—') ?></b></li>
        <li><span>当前到期</span><b><?= htmlspecialchars($asset['expire_at'] ?: $asset['host_datae'] ?: '—') ?></b></li>
        <li><span>开通节点</span><b><?= htmlspecialchars($asset['ssbt'] ?: '—') ?></b></li>
      </ul>

      <form class="hs-order-form" id="renewForm">
        <div class="layui-form-item">
          <label class="layui-form-label">续费周期</label>
          <div class="layui-input-block hs-form-choices" style="padding-top:8px;">
            <?php
              $enabled = hosting_plan_enabled_periods($plan);
              foreach ($enabled as $p):
                $cfg = hosting_periods()[$p];
                $field = hosting_period_price_field($p);
                $price = (int)($plan[$field] ?? 0);
            ?>
              <label class="hs-choice"><input type="radio" name="period" value="<?= htmlspecialchars($p, ENT_QUOTES) ?>"> <?= htmlspecialchars($cfg['label']) ?> ¥<?= hosting_format_cents($price) ?></label>
            <?php endforeach; ?>
            <?php if ($enabled === []): ?>
              <span style="color:#999;">该套餐未设置可续费周期</span>
            <?php endif; ?>
          </div>
        </div>

        <?php
          $isFreePeriod = false;
          if (isset($enabled[0])) {
            $f = hosting_period_price_field($enabled[0]);
            $isFreePeriod = (int)($plan[$f] ?? 0) === 0;
          }
        ?>
        <?php if (!$isFreePeriod): ?>
          <?php if (!empty($methods)): ?>
            <div class="layui-form-item">
              <label class="layui-form-label">支付方式</label>
              <div class="layui-input-block hs-form-choices" style="padding-top:6px;">
                <?php foreach ($methods as $m): ?>
                  <label class="hs-choice"><input type="radio" name="type" value="<?= htmlspecialchars($m['plugin'].'__'.$m['method']) ?>" required> <?= htmlspecialchars($m['display_name'] ?: ($m['plugin'].' / '.$m['method'])) ?></label>
                <?php endforeach; ?>
              </div>
            </div>
          <?php else: ?>
            <div class="layui-form-item"><div class="layui-input-block" style="color:#999;">暂无可用的支付方式</div></div>
          <?php endif; ?>
        <?php endif; ?>

        <?php if ($isFreePeriod || !empty($methods)): ?>
          <div class="layui-form-item">
            <div class="layui-input-block">
              <button type="submit" class="layui-btn layui-btn-lg" id="submitBtn">确认续费</button>
            </div>
          </div>
        <?php endif; ?>
      </form>
    <?php endif; ?>
  </div>
</div>

<script>
(function(){
  var form=document.getElementById('renewForm');if(!form)return;
  var msg=document.getElementById('msg'),btn=document.getElementById('submitBtn');
  function showMsg(text,type){msg.textContent=text;msg.className='ly-msg show '+(type==='success'?'ly-msg-success':'ly-msg-error');}
  // 单选框选中态样式（兼容不支持 :has() 的浏览器）
  function updateChoiceStyles(){
    form.querySelectorAll('.hs-choice').forEach(function(l){l.classList.remove('active');});
    form.querySelectorAll('input[type="radio"]:checked').forEach(function(r){
      var p=r.closest('.hs-choice');if(p)p.classList.add('active');
    });
  }
  form.addEventListener('change',updateChoiceStyles);
  updateChoiceStyles();

  form.addEventListener('submit',function(e){
    e.preventDefault();if(!btn)return;btn.disabled=true;btn.textContent='正在创建续费订单...';msg.className='ly-msg';
    var body=new URLSearchParams();
    body.append('asset_id','<?=(int)$asset['id']?>');
    var pc=form.querySelector('input[name="period"]:checked');
    body.append('period',pc?pc.value:'');
    var tc=form.querySelector('input[name="type"]:checked');
    body.append('type',tc?tc.value:'');
    fetch('<?=hosting_url('shop/api/renew')?>',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:body.toString()})
      .then(function(r){return r.json();})
      .then(function(res){
        if(res.html){document.open();document.write(res.html);document.close();}
        else if(res.redirect){window.location.href=res.redirect;}
        else{showMsg(res.code||'创建续费订单失败','error');btn.disabled=false;btn.textContent='确认续费';}
      })
      .catch(function(){showMsg('网络错误，请重试','error');btn.disabled=false;btn.textContent='确认续费';});
  });
})();
</script>
<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
