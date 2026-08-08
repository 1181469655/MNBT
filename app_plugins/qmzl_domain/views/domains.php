<?php
/**
 * qmzl_domain - 用户端我的域名页
 *
 * 已购域名同步自启明智联平台（GET /host，过滤 type=domain），
 * 另展示本地下单记录与支付状态。
 */
if (!defined('IN_CRONLITE')) exit;
$mode = qmzl_mode();
$qzCss   = qmzl_asset_url('qmzl.css');
$qzJs    = qmzl_asset_url('qmzl.js');
$qzMdi   = mnbt_asset_url('css/materialdesignicons.min.css');
$qzJq    = mnbt_asset_url('js/jquery.min.js');
$qzApi   = qmzl_url(qmzl_route_prefix() . '/api');
$qzAccUrl = qmzl_url(qmzl_route_prefix() . '/account');
$qzIdxUrl = qmzl_url(qmzl_route_prefix());
$qzPayBase = qmzl_url(qmzl_route_prefix() . '/pay');
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>我的域名 - 域名服务</title>
<link rel="stylesheet" href="<?= htmlspecialchars($qzMdi, ENT_QUOTES, 'UTF-8') ?>">
<link rel="stylesheet" href="<?= htmlspecialchars($qzCss, ENT_QUOTES, 'UTF-8') ?>">
<style>
body { margin: 0; background: var(--qz-bg); color: var(--qz-text); font: 14px/1.6 -apple-system, BlinkMacSystemFont, "Segoe UI", "PingFang SC", "Hiragino Sans GB", "Microsoft YaHei", sans-serif; -webkit-font-smoothing: antialiased; padding: 24px 16px 48px; }
.qz-nav { display: flex; gap: 6px; flex-wrap: wrap; margin-bottom: 16px; }
.qz-nav a { display: inline-block; padding: 6px 14px; border-radius: 20px; font-size: 13px; background: #fff; border: 1px solid var(--qz-border); color: var(--qz-text-2); text-decoration: none; transition: all .15s; }
.qz-nav a:hover { border-color: var(--qz-brand); color: var(--qz-brand); }
</style>
</head>
<body>
<div class="qz-wrap">

  <div class="qz-head">
    <h1>我的域名</h1>
    <p>已购域名数据同步自启明智联平台。</p>
    <div class="qz-nav" style="margin-top:12px;">
      <a href="<?= htmlspecialchars($qzIdxUrl, ENT_QUOTES, 'UTF-8') ?>">域名注册</a>
      <?php if ($mode === 'client'): ?>
        <a href="<?= htmlspecialchars($qzAccUrl, ENT_QUOTES, 'UTF-8') ?>">云账号</a>
      <?php endif; ?>
    </div>
  </div>

  <?php if ($mode === 'client'): ?>
  <div id="qz-tip-unbound" class="qz-tip qz-tip--warn" style="display:none;">
    尚未绑定启明智联账号，无法同步域名数据。<a href="<?= htmlspecialchars($qzAccUrl, ENT_QUOTES, 'UTF-8') ?>" style="color:var(--qz-brand);font-weight:600;">立即绑定</a>
  </div>
  <?php endif; ?>

  <div class="qz-card">
    <div class="qz-card-head">
      <h3>已购域名</h3>
      <button type="button" class="qz-btn qz-btn--ghost qz-btn--sm" id="qz-btn-refresh"><span class="mdi mdi-refresh"></span> 刷新</button>
    </div>
    <div class="qz-card-body" id="qz-domains">
      <div class="qz-loading">加载中...</div>
    </div>
  </div>

  <div class="qz-card">
    <div class="qz-card-head"><h3>购买记录</h3></div>
    <div class="qz-card-body" id="qz-orders">
      <div class="qz-loading">加载中...</div>
    </div>
  </div>

</div>

<script src="<?= htmlspecialchars($qzJq, ENT_QUOTES, 'UTF-8') ?>"></script>
<script src="<?= htmlspecialchars($qzJs, ENT_QUOTES, 'UTF-8') ?>"></script>
<script>
$(function () {
  var QZ_MODE = <?= json_encode($mode, JSON_UNESCAPED_UNICODE) ?>;
  var QZ_API = <?= json_encode($qzApi, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;
  var PAY_BASE = <?= json_encode($qzPayBase, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;
  var STATUS = {
    Active: ['正常', 'green'], Suspended: ['已暂停', 'red'], Pending: ['处理中', 'yellow'],
    Expired: ['已过期', 'red'], Deleted: ['已删除', 'gray'], Cancelled: ['已取消', 'gray'], Fraud: ['欺诈', 'red']
  };
  var ORDER_STATUS = { Pending: ['待支付', 'yellow'], Paid: ['已支付', 'green'], Cancelled: ['已取消', 'gray'], Failed: ['失败', 'red'] };

  if (QZ_MODE === 'client') {
    QZ.post(QZ_API + '/account_info', {}, function (res) {
      if (!(res.data && res.data.bound)) $('#qz-tip-unbound').show();
    }, function () { $('#qz-tip-unbound').show(); });
  }

  function fmtTime(ts) {
    if (!ts) return '-';
    var d = new Date(parseInt(ts, 10) * 1000);
    if (isNaN(d.getTime())) return '-';
    var p = function (n) { return (n < 10 ? '0' : '') + n; };
    return d.getFullYear() + '-' + p(d.getMonth() + 1) + '-' + p(d.getDate()) + ' ' + p(d.getHours()) + ':' + p(d.getMinutes());
  }

  function loadDomains() {
    $('#qz-domains').html('<div class="qz-loading">加载中...</div>');
    QZ.post(QZ_API + '/domains', {}, function (res) {
      var list = (res.data && res.data.list) || [];
      if (!list.length) {
        $('#qz-domains').html('<div class="qz-empty">暂无已购域名</div>');
        return;
      }
      var html = '<table class="qz-table"><thead><tr>' +
        '<th>域名</th><th>注册商</th><th>状态</th><th>到期时间</th><th>转移锁</th>' +
        '</tr></thead><tbody>';
      $.each(list, function (i, h) {
        var st = STATUS[h.status] || [h.status || '未知', 'gray'];
        html += '<tr>' +
          '<td class="qz-monospace">' + QZ.esc(h.name || h.domain || '-') + '</td>' +
          '<td>' + QZ.esc(h.module_name || h.registrar || '-') + '</td>' +
          '<td><span class="qz-badge qz-badge--' + st[1] + '">' + st[0] + '</span></td>' +
          '<td>' + fmtTime(h.due_time) + '</td>' +
          '<td>' + (h.lock_status === 1 ? '<span class="qz-badge qz-badge--blue">已开启</span>' : '<span class="qz-badge qz-badge--gray">未开启</span>') + '</td>' +
          '</tr>';
      });
      html += '</tbody></table>';
      $('#qz-domains').html(html);
    }, function (msg) {
      $('#qz-domains').html('<div class="qz-empty">' + QZ.esc(msg) + '</div>');
    });
  }

  function loadOrders() {
    $('#qz-orders').html('<div class="qz-loading">加载中...</div>');
    QZ.post(QZ_API + '/orders', {}, function (res) {
      var list = (res.data && res.data.rows) || [];
      if (!list.length) {
        $('#qz-orders').html('<div class="qz-empty">暂无购买记录</div>');
        return;
      }
      var html = '<table class="qz-table"><thead><tr>' +
        '<th>域名</th><th>年限</th><th>金额</th><th>状态</th><th>备注</th><th>下单时间</th><th>操作</th>' +
        '</tr></thead><tbody>';
      $.each(list, function (i, o) {
        var st = ORDER_STATUS[o.status] || [o.status || '未知', 'gray'];
        var op = '-';
        if (QZ_MODE === 'client' && o.status === 'Pending' && o.cloud_order_id) {
          op = '<a class="qz-btn qz-btn--sm" href="' + PAY_BASE + '&order=' + parseInt(o.cloud_order_id, 10) + '">去支付</a>';
        }
        html += '<tr>' +
          '<td class="qz-monospace">' + QZ.esc(o.domain) + '</td>' +
          '<td>' + parseInt(o.year, 10) + ' 年</td>' +
          '<td>¥' + QZ.esc(o.amount) + '</td>' +
          '<td><span class="qz-badge qz-badge--' + st[1] + '">' + st[0] + '</span></td>' +
          '<td class="qz-muted">' + QZ.esc(o.remark || '-') + '</td>' +
          '<td>' + QZ.esc(o.created_at) + '</td>' +
          '<td>' + op + '</td>' +
          '</tr>';
      });
      html += '</tbody></table>';
      $('#qz-orders').html(html);
    }, function (msg) {
      $('#qz-orders').html('<div class="qz-empty">' + QZ.esc(msg) + '</div>');
    });
  }

  $('#qz-btn-refresh').on('click', function () {
    loadDomains();
    loadOrders();
  });

  loadDomains();
  loadOrders();
});
</script>
</body>
</html>
