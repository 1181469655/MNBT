<?php
/**
 * qmzl_domain - 用户端支付页
 *
 * 下单后跳转到本页（index.php?_r=/qmzl/pay&order={上游订单ID}），
 * 路由处理器已校验订单归属并传入 $order / $order_id / $order_valid。
 * 前端调用 /qmzl/api/pay 发起支付并渲染三方返回的 HTML（自动提交表单 / 二维码），
 * 同时轮询 /qmzl/api/pay_status 直到支付完成。
 */
if (!defined('IN_CRONLITE')) exit;
$order      = $order ?? null;
$orderId    = (int)($order_id ?? 0);
$orderValid = !empty($order_valid);
$qzCss   = qmzl_asset_url('qmzl.css');
$qzJs    = qmzl_asset_url('qmzl.js');
$qzMdi   = mnbt_asset_url('css/materialdesignicons.min.css');
$qzJq    = mnbt_asset_url('js/jquery.min.js');
$qzApi   = qmzl_url(qmzl_route_prefix() . '/api');
$qzDomUrl = qmzl_url(qmzl_route_prefix() . '/domains');
$qzIdxUrl = qmzl_url(qmzl_route_prefix());
$orderDomain = is_array($order) ? (string)($order['domain'] ?? '') : '';
$orderAmount = is_array($order) ? (string)($order['amount'] ?? '0.00') : '0.00';
$orderGateway = is_array($order) ? (string)($order['gateway'] ?? '') : '';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>域名支付 - 域名服务</title>
<link rel="stylesheet" href="<?= htmlspecialchars($qzMdi, ENT_QUOTES, 'UTF-8') ?>">
<link rel="stylesheet" href="<?= htmlspecialchars($qzCss, ENT_QUOTES, 'UTF-8') ?>">
<style>
body { margin: 0; background: var(--qz-bg); color: var(--qz-text); font: 14px/1.6 -apple-system, BlinkMacSystemFont, "Segoe UI", "PingFang SC", "Hiragino Sans GB", "Microsoft YaHei", sans-serif; -webkit-font-smoothing: antialiased; padding: 24px 16px 48px; }
#qz-pay-html { display: flex; justify-content: center; margin-top: 8px; }
#qz-pay-html form, #qz-pay-html .qr-wrap { width: 100%; max-width: 380px; margin: 0 auto; text-align: center; }
#qz-pay-html img.qr { width: 240px; height: 240px; border: 1px solid var(--qz-border); border-radius: 12px; }
#qz-pay-html .btn-pay-link { display: inline-block; margin-top: 16px; }
.spin { display: inline-block; animation: qzSpin 1s linear infinite; }
@keyframes qzSpin { to { transform: rotate(360deg); } }
.qz-nav { display: flex; gap: 6px; flex-wrap: wrap; margin-bottom: 16px; }
.qz-nav a { display: inline-block; padding: 6px 14px; border-radius: 20px; font-size: 13px; background: #fff; border: 1px solid var(--qz-border); color: var(--qz-text-2); text-decoration: none; transition: all .15s; }
.qz-nav a:hover { border-color: var(--qz-brand); color: var(--qz-brand); }
</style>
</head>
<body>
<div class="qz-wrap" style="max-width:720px;">

  <div class="qz-head">
    <h1>域名支付</h1>
    <p>订单 <span class="qz-monospace" id="qz-order-id"><?= (int)$orderId ?></span> · 域名 <span class="qz-monospace" id="qz-domain"><?= htmlspecialchars($orderDomain, ENT_QUOTES, 'UTF-8') ?></span></p>
    <div class="qz-nav" style="margin-top:12px;">
      <a href="<?= htmlspecialchars($qzIdxUrl, ENT_QUOTES, 'UTF-8') ?>">域名注册</a>
      <a href="<?= htmlspecialchars($qzDomUrl, ENT_QUOTES, 'UTF-8') ?>">我的域名</a>
    </div>
  </div>

  <div class="qz-card">
    <div class="qz-card-body">
      <!-- 加载中 -->
      <div id="qz-loading" style="text-align:center;padding:36px 0;color:var(--qz-text-3);">
        <span class="spin mdi mdi-loading" style="font-size:28px;"></span>
        <div style="margin-top:10px;">正在创建支付订单...</div>
      </div>

      <!-- 支付内容 -->
      <div id="qz-pay-html" style="display:none;"></div>

      <!-- 支付失败 -->
      <div id="qz-error" style="display:none;text-align:center;padding:24px 0;">
        <div style="font-size:40px;color:var(--qz-danger);"><span class="mdi mdi-alert-circle-outline"></span></div>
        <div style="margin:10px 0 18px;color:var(--qz-text-2);" id="qz-error-msg"></div>
        <button type="button" class="qz-btn" id="qz-btn-retry"><span class="mdi mdi-refresh"></span> 重试</button>
        <a class="qz-btn qz-btn--ghost" style="margin-left:8px;" href="<?= htmlspecialchars($qzDomUrl, ENT_QUOTES, 'UTF-8') ?>">我的域名</a>
      </div>

      <!-- 已支付 -->
      <div id="qz-paid" style="display:none;text-align:center;padding:24px 0;">
        <div style="font-size:44px;color:var(--qz-success);"><span class="mdi mdi-check-circle"></span></div>
        <div style="margin:10px 0 4px;font-size:17px;font-weight:600;">支付成功</div>
        <div style="color:var(--qz-text-2);margin-bottom:20px;">域名注册处理中，稍后可在“我的域名”中查看</div>
        <a class="qz-btn" href="<?= htmlspecialchars($qzDomUrl, ENT_QUOTES, 'UTF-8') ?>"><span class="mdi mdi-earth"></span> 查看我的域名</a>
      </div>
    </div>
  </div>

  <div class="qz-card">
    <div class="qz-card-head"><h3>订单信息</h3></div>
    <div class="qz-card-body">
      <table class="qz-table">
        <tr><th style="width:130px;">域名</th><td class="qz-monospace"><?= htmlspecialchars($orderDomain ?: '-', ENT_QUOTES, 'UTF-8') ?></td></tr>
        <tr><th>金额</th><td style="color:var(--qz-brand);font-weight:700;">¥<?= htmlspecialchars($orderAmount ?: '0.00', ENT_QUOTES, 'UTF-8') ?></td></tr>
        <tr><th>状态</th><td id="qz-order-status">-</td></tr>
      </table>
    </div>
  </div>

</div>

<script src="<?= htmlspecialchars($qzJq, ENT_QUOTES, 'UTF-8') ?>"></script>
<script src="<?= htmlspecialchars($qzJs, ENT_QUOTES, 'UTF-8') ?>"></script>
<script>
$(function () {
  var QZ_API = <?= json_encode($qzApi, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;
  var ORDER_ID = <?= (int)$orderId ?>;
  var GATEWAY = <?= json_encode($orderGateway, JSON_UNESCAPED_UNICODE) ?>;
  var ORDER_VALID = <?= $orderValid ? 'true' : 'false' ?>;
  var pollTimer = null;

  function setStatus(txt) { $('#qz-order-status').html(txt); }
  setStatus('<span class="qz-badge qz-badge--yellow">待支付</span>');

  function renderPaidHtml(html) {
    $('#qz-loading').hide();
    var $wrap = $('#qz-pay-html');
    $wrap.show().html(html);

    // 自动提交支付表单（跳转到三方网关）
    var $form = $wrap.find('form').first();
    if ($form.length) {
      var btn = '<div style="margin-top:14px;"><a class="qz-btn" id="qz-open-gateway" href="javascript:void(0)">若未自动跳转，点击此处进入支付</a></div>';
      $wrap.append(btn);
      setTimeout(function () {
        try { $form.submit(); } catch (e) {}
      }, 300);
      $('#qz-open-gateway').on('click', function () {
        try { $form.submit(); } catch (e) {}
      });
    }

    // 二维码 / 图片
    if ($wrap.find('img').length) {
      $wrap.find('img').addClass('qr');
      var tip = '<div class="qz-muted" style="margin-top:12px;">请使用' + (GATEWAY || '支付工具') + '扫码完成支付，支付完成后将自动确认</div>';
      $wrap.append(tip);
    }

    startPoll();
  }

  function startPoll() {
    if (pollTimer) clearInterval(pollTimer);
    pollTimer = setInterval(function () {
      QZ.post(QZ_API + '/pay_status', { order_id: ORDER_ID }, function (res) {
        if (res.data && res.data.paid) {
          clearInterval(pollTimer);
          onPaid();
        }
      }, function () {});
    }, 2500);
  }

  function onPaid() {
    $('#qz-pay-html').hide();
    $('#qz-paid').show();
    setStatus('<span class="qz-badge qz-badge--green">已支付</span>');
  }

  function initPay() {
    QZ.post(QZ_API + '/pay', { order_id: ORDER_ID, gateway: GATEWAY }, function (res) {
      if (res.data && res.data.code === 'Paid') { onPaid(); return; }
      var html = (res.data && res.data.html) || '';
      if (!html) { onPaid(); return; }
      renderPaidHtml(html);
    }, function (msg) {
      $('#qz-loading').hide();
      $('#qz-error').show();
      $('#qz-error-msg').text(msg || '发起支付失败');
    });
  }

  $('#qz-btn-retry').on('click', function () {
    $('#qz-error').hide();
    $('#qz-loading').show();
    $('#qz-pay-html').hide();
    initPay();
  });

  if (!ORDER_VALID) {
    $('#qz-loading').hide();
    $('#qz-error').show();
    $('#qz-error-msg').text('订单不存在或不属于当前账号');
    setStatus('<span class="qz-badge qz-badge--gray">无效</span>');
    return;
  }
  initPay();
});
</script>
</body>
</html>
