<?php
if (!defined('IN_CRONLITE')) { exit; }
$page_title = $page_title ?? '购买 Docker';
$plan = $plan ?? null; $methods = $methods ?? [];
$base_plan = $plan && (int)$plan['base_plan_id'] > 0 ? docker_shop_base_plan_get((int)$plan['base_plan_id']) : null;
$node = $plan && (int)$plan['node'] > 0 ? docker_shop_node_get((int)$plan['node']) : null;
ob_start();
?>
<div class="layui-card">
  <div class="layui-card-body" style="padding:28px;">
    <div class="ly-msg" id="msg"></div>

    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;">
      <h1 style="font-size:20px;color:#222;margin:0;">购买：<?= htmlspecialchars($plan['name']) ?></h1>
      <a class="layui-btn layui-btn-xs layui-btn-primary" href="<?= htmlspecialchars(docker_shop_url('docker-shop'), ENT_QUOTES) ?>">返回套餐列表</a>
    </div>

    <?php if ($base_plan): ?>
    <ul class="hs-plan-spec" style="margin-bottom:20px;">
      <li><span>CPU 核</span><b><?= htmlspecialchars($base_plan['cpu_max']) ?></b></li>
      <li><span>内存</span><b><?= htmlspecialchars($base_plan['mem_max']) ?> MB</b></li>
      <li><span>磁盘</span><b><?= (int)$base_plan['disk_max'] > 0 ? htmlspecialchars($base_plan['disk_max']) . ' MB' : '不限制' ?></b></li>
      <li><span>开通节点</span><b><?= $node ? htmlspecialchars($node['name']) : '—' ?></b></li>
    </ul>
    <?php endif; ?>

    <form class="hs-order-form" id="orderForm">
      <div class="layui-form-item">
        <label class="layui-form-label">购买周期</label>
        <div class="layui-input-block hs-form-choices" style="padding-top:8px;">
          <?php
            $enabled = docker_shop_plan_enabled_periods($plan);
            foreach ($enabled as $p):
              $cfg = docker_shop_periods()[$p];
              $field = docker_shop_period_price_field($p);
              $price = (int)($plan[$field] ?? 0);
          ?>
            <label class="hs-choice"><input type="radio" name="period" value="<?= htmlspecialchars($p, ENT_QUOTES) ?>"> <?= htmlspecialchars($cfg['label']) ?> ¥<?= docker_shop_format_cents($price) ?></label>
          <?php endforeach; ?>
          <?php if ($enabled === []): ?>
            <span style="color:#999;">该套餐未设置购买周期</span>
          <?php endif; ?>
        </div>
      </div>

      <?php
        $isFreePlan = true;
        foreach ($enabled as $p) {
          $field = docker_shop_period_price_field($p);
          if ((int)($plan[$field] ?? 0) > 0) {
            $isFreePlan = false;
            break;
          }
        }
      ?>
      <?php if (!$isFreePlan): ?>
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

      <?php if ($isFreePlan || !empty($methods)): ?>
        <div class="layui-form-item">
          <div class="layui-input-block">
            <button type="submit" class="layui-btn layui-btn-lg" id="submitBtn">确认购买</button>
          </div>
        </div>
      <?php endif; ?>
    </form>
  </div>
</div>

<script>
(function(){
  var periodRadios = document.querySelectorAll('input[name="period"]');
  if (periodRadios.length && !document.querySelector('input[name="period"]:checked')) {
    periodRadios[0].checked = true;
  }
  var typeRadios = document.querySelectorAll('input[name="type"]');
  if (typeRadios.length && !document.querySelector('input[name="type"]:checked')) {
    typeRadios[0].checked = true;
  }

  var form=document.getElementById('orderForm');if(!form)return;
  var msg=document.getElementById('msg'),btn=document.getElementById('submitBtn');
  function showMsg(text,type){msg.textContent=text;msg.className='ly-msg show '+(type==='success'?'ly-msg-success':'ly-msg-error');}
  function updateChoiceStyles(){
    form.querySelectorAll('.hs-choice').forEach(function(l){l.classList.remove('active');});
    form.querySelectorAll('input[type="radio"]:checked').forEach(function(r){
      var p=r.closest('.hs-choice');if(p)p.classList.add('active');
    });
  }
  form.addEventListener('change',updateChoiceStyles);
  updateChoiceStyles();

  form.addEventListener('submit',function(e){
    e.preventDefault();if(!btn)return;btn.disabled=true;btn.textContent='正在创建订单...';msg.className='ly-msg';
    var body=new URLSearchParams();
    body.append('plan_id','<?=(int)$plan['id']?>');
    var pc=form.querySelector('input[name="period"]:checked');
    body.append('period',pc?pc.value:'');
    var tc=form.querySelector('input[name="type"]:checked');
    body.append('type',tc?tc.value:'');
    fetch('<?= htmlspecialchars(docker_shop_url('docker-shop/api/create_order'), ENT_QUOTES) ?>',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:body.toString()})
      .then(function(r){return r.json();})
      .then(function(res){
        if(res.html){document.open();document.write(res.html);document.close();}
        else if(res.redirect){window.location.href=res.redirect;}
        else{showMsg(res.code||'创建订单失败','error');btn.disabled=false;btn.textContent='确认购买';}
      })
      .catch(function(){showMsg('网络错误，请重试','error');btn.disabled=false;btn.textContent='确认购买';});
  });
})();
</script>
<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
