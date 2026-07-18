<?php
/**
 * 后台 - 添加域名商品
 * 迁移自 templates/default/admin/add.php 的 $set=='ym' 分支
 * AJAX gn：p_domain_addym
 *
 * 通道（channel）说明：
 * - pan（泛解析）：域名已通过 *.example.com 泛解析到节点 IP，购买时仅调宝塔绑定 + 共享主机 hosts/反代
 * - dnsapi（DNS API）：域名已托管在 DNS 服务商（如 DNSPod），购买时除宝塔绑定外还通过 API 建 A 记录
 */
if (!defined('IN_CRONLITE')) exit;
mnbt_admin_include('head');
global $DB;
$bt_list = $DB->get_all_prepare("SELECT * FROM MN_bt order by id desc limit 100") ?: [];
$providers = $DB->get_all_prepare("SELECT * FROM plg_dns_provider WHERE qk='true' order by id asc") ?: [];
?>
<div class="container-fluid p-t-15">
  <div class="row">
    <div class="col-lg-12">
      <div class="card">
        <header class="card-header"><div class="card-title">添加售卖域名</div></header>
        <div class="card-body">
          <div class="form-group">
            <label>域名</label>
            <input type="text" id="ym" class="form-control" placeholder="请填写要出售二级的域名" required/>
            <small>不能带 http:// 和 /</small>
          </div><br/>

          <div class="form-group">
            <label>将此域名绑定到</label>
            <select class="form-control" id="btdh">
              <option value="-00-">点我选择宝塔</option>
              <?php foreach ($bt_list as $res): ?>
                <option value="<?= htmlspecialchars($res['btdh']) ?>"><?= htmlspecialchars($res['btdh']) ?></option>
              <?php endforeach; ?>
            </select>
            <small>购买时域名会绑定到该宝塔节点的主机</small>
          </div><br/>

          <div class="form-group">
            <label>解析通道</label>
            <select class="form-control" id="channel" onchange="toggleChannel()">
              <option value="pan">泛解析（域名已 *.example.com 泛解析到节点 IP）</option>
              <option value="dnsapi" <?= empty($providers) ? 'disabled' : '' ?>>DNS API（域名已托管在 DNS 服务商）</option>
            </select>
            <small id="channel-tip">泛解析通道：依赖域名整体的泛 A 记录，购买时仅调宝塔绑定，不调 DNS API</small>
          </div><br/>

          <div class="form-group" id="provider-group" style="display:none;">
            <label>DNS 服务商</label>
            <select class="form-control" id="provider_id">
              <option value="0">请选择服务商</option>
              <?php foreach ($providers as $p): ?>
                <option value="<?= (int)$p['id'] ?>"><?= htmlspecialchars($p['name']) ?>（<?= htmlspecialchars($p['slug']) ?>）</option>
              <?php endforeach; ?>
            </select>
            <small>DNS API 通道必填；如列表为空请先到「DNS 服务商」页配置</small>
          </div><br/>

          <div class="form-group">
            <label>解析一次的价格</label>
            <input type="number" id="jg" class="form-control" placeholder="请填写对该域名解析一次的价格" required/>
            <small>填写 0 即为免费</small>
          </div><br/>

          <div class="form-group">
            <label>域名介绍</label>
            <input type="text" id="js" class="form-control" placeholder="如：该域名是国内备案域名" required/>
          </div><br/>

          <div class="form-group">
            <label class="btn-block">是否上架</label>
            <div class="col-xs-6">
              <div class="custom-control custom-switch custom-info">
                <input type="checkbox" class="custom-control-input" id="ymsxj" checked>
                <label class="custom-control-label" for="ymsxj"></label>
              </div>
            </div>
          </div>

          <button class="btn btn-primary form-control" type="button" onclick="tjym()">
            <i class="mdi mdi-checkbox-marked-circle-outline"></i> 确认添加
          </button>

          <div class="panel-footer" style="margin-top:15px;">
            <span class="glyphicon glyphicon-info-sign"></span>
            <b>泛解析通道</b>：请将域名 A 记录到宝塔 IP，主机记录为 <code>*</code><br/>
            <b>DNS API 通道</b>：请确保该域名已添加到所选 DNS 服务商的域名列表中
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script type="text/javascript">
function toggleChannel() {
  var ch = channel.value;
  if (ch === 'dnsapi') {
    provider_group.style.display = '';
    channel_tip.innerHTML = 'DNS API 通道：购买时除宝塔绑定外，还通过所选服务商 API 自动建 A 记录指向节点 IP';
  } else {
    provider_group.style.display = 'none';
    channel_tip.innerHTML = '泛解析通道：依赖域名整体的泛 A 记录，购买时仅调宝塔绑定，不调 DNS API';
  }
}

function tjym() {
  var url = ym.value, bt = btdh.value, je = jg.value, ymjs = js.value, kg = ymsxj.checked;
  var ch = channel.value, pid = provider_id.value;
  if (url == "" || bt == "-00-" || je == "" || ymjs == "") {
    msalert(3, '表单不能为空！', 2000); return;
  }
  if (ch === 'dnsapi' && (pid === '0' || pid === '')) {
    msalert(3, 'DNS API 通道必须选择 DNS 服务商！', 2000); return;
  }
  msloading('正在加载中');
  $.post('ajax.php', {
    gn: 'p_domain_addym', url: url, bt: bt, jg: je, ymjs: ymjs, kg: kg,
    channel: ch, provider_id: pid
  }, function (date) {
    var jsoe = JSON.parse(date);
    var qk = jsoe.code;
    msloadingde();
    if (qk == '添加成功') {
      msalert(1, '添加成功！', 2000);
      window.location.href = "plugin.php?p=domain_shop&page=products";
    } else {
      msalert(4, qk, 2000);
    }
  });
}
</script>
