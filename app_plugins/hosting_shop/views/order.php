<?php
if (!defined('IN_CRONLITE')) { exit; }
$page_title = $page_title ?? '购买套餐';
$plan = $plan ?? null; $nodes = $nodes ?? []; $methods = $methods ?? [];
ob_start();
?>
<div class="layui-card">
  <div class="layui-card-body" style="padding:28px;">
    <div class="ly-msg" id="msg"></div>

    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;">
      <h1 style="font-size:20px;color:#222;margin:0;">购买：<?= htmlspecialchars($plan['name']) ?></h1>
      <a class="layui-btn layui-btn-xs layui-btn-primary" href="<?= hosting_url('shop') ?>">返回套餐列表</a>
    </div>

    <ul class="hs-plan-spec" style="margin-bottom:20px;">
      <li><span>产品类型</span><b><?= (int)$plan['spec_type'] === 1 ? 'CDN' : '虚拟主机' ?></b></li>
      <li><span>网页空间</span><b><?= (int)$plan['spec_web'] ?> MB</b></li>
      <li><span>数据库</span><b><?= (int)$plan['spec_sql'] ?> MB</b></li>
      <li><span>流量</span><b><?= (int)$plan['spec_flow'] > 0 ? ((int)$plan['spec_flow'].' GB') : '不限' ?></b></li>
      <li><span>域名绑定</span><b><?= (int)$plan['spec_domain'] ?> 个</b></li>
    </ul>

    <form class="layui-form" id="orderForm">
      <div class="layui-form-item">
        <label class="layui-form-label">购买周期</label>
        <div class="layui-input-block" style="padding-top:8px;">
          <?php if ((int)$plan['price_month_cents'] > 0): ?>
            <input type="radio" name="period" value="month" title="月付 ¥<?= hosting_format_cents($plan['price_month_cents']) ?>" <?= (int)$plan['price_year_cents']<=0?'checked':'') ?>>
          <?php endif; ?>
          <?php if ((int)$plan['price_year_cents'] > 0): ?>
            <input type="radio" name="period" value="year" title="年付 ¥<?= hosting_format_cents($plan['price_year_cents']) ?>" <?= (int)$plan['price_month_cents']<=0?'checked':'') ?>>
          <?php endif; ?>
        </div>
      </div>

      <div class="layui-form-item">
        <label class="layui-form-label">开通节点</label>
        <div class="layui-input-block">
          <?php if (empty($nodes)): ?>
            <p style="color:#999;padding-top:8px;">管理员尚未添加宝塔节点，无法购买。</p>
          <?php else: ?>
            <select name="node" required class="layui-input" style="max-width:360px;">
              <option value="">请选择节点</option>
              <?php foreach ($nodes as $n): ?>
                <option value="<?= htmlspecialchars($n['btdh']) ?>"><?= htmlspecialchars($n['btdh']) ?>（<?= htmlspecialchars($n['btip']) ?>，<?= $n['btos']=='1'?'Linux':'Windows' ?>）</option>
              <?php endforeach; ?>
            </select>
          <?php endif; ?>
        </div>
      </div>

      <?php if (!empty($methods)): ?>
        <div class="layui-form-item">
          <label class="layui-form-label">支付方式</label>
          <div class="layui-input-block" style="padding-top:6px;">
            <?php foreach ($methods as $m): ?>
              <input type="radio" name="type" value="<?= htmlspecialchars($m['plugin'].'__'.$m['method']) ?>" title="<?= htmlspecialchars($m['display_name'] ?: ($m['plugin'].' / '.$m['method'])) ?>" required>
            <?php endforeach; ?>
          </div>
        </div>
      <?php else: ?>
        <div class="layui-form-item"><div class="layui-input-block" style="color:#999;">暂无可用的支付方式</div></div>
      <?php endif; ?>

      <?php if (!empty($nodes) && !empty($methods)): ?>
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
  var form=document.getElementById('orderForm');if(!form)return;
  var msg=document.getElementById('msg'),btn=document.getElementById('submitBtn');
  function showMsg(text,type){msg.textContent=text;msg.className='ly-msg show '+(type==='success'?'ly-msg-success':'ly-msg-error');}
  form.addEventListener('submit',function(e){
    e.preventDefault();if(!btn)return;btn.disabled=true;btn.textContent='正在创建订单...';msg.className='ly-msg';
    var body=new URLSearchParams();
    body.append('plan_id','<?=(int)$plan['id']?>');
    var pc=form.querySelector('input[name="period"]:checked');
    body.append('period',pc?pc.value:'');
    body.append('node',form.querySelector('select[name="node"]').value);
    var tc=form.querySelector('input[name="type"]:checked');
    body.append('type',tc?tc.value:'');
    fetch('<?=hosting_url('shop/api/create_order')?>',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:body.toString()})
      .then(function(r){return r.json();})
      .then(function(res){
        if(res.html){document.open();document.write(res.html);document.close();}
        else{showMsg(res.code||'创建订单失败','error');btn.disabled=false;btn.textContent='确认购买';}
      })
      .catch(function(){showMsg('网络错误，请重试','error');btn.disabled=false;btn.textContent='确认购买';});
  });
})();
</script>
<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
