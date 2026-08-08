<?php
/**
 * qmzl_domain - 用户端域名注册页
 *
 * 流程：查询可用性 → 查看各年限价格 → 选择信息模板/支付方式 → 下单。
 * client 模式：跳转上游支付；agent 模式：走本站支付系统（MN_dd + 支付插件）。
 */
if (!defined('IN_CRONLITE')) exit;
$mode = qmzl_mode();
$qzCss   = qmzl_asset_url('qmzl.css');
$qzJs    = qmzl_asset_url('qmzl.js');
$qzMdi   = mnbt_asset_url('css/materialdesignicons.min.css');
$qzJq    = mnbt_asset_url('js/jquery.min.js');
$qzApi   = qmzl_url(qmzl_route_prefix() . '/api');
$qzAccUrl  = qmzl_url(qmzl_route_prefix() . '/account');
$qzTplUrl  = qmzl_url(qmzl_route_prefix() . '/templates');
$qzDomUrl  = qmzl_url(qmzl_route_prefix() . '/domains');
$qzPayBase = qmzl_url(qmzl_route_prefix() . '/pay');
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>域名注册 - 域名服务</title>
<link rel="stylesheet" href="<?= htmlspecialchars($qzMdi, ENT_QUOTES, 'UTF-8') ?>">
<link rel="stylesheet" href="<?= htmlspecialchars($qzCss, ENT_QUOTES, 'UTF-8') ?>">
<style>
body { margin: 0; background: var(--qz-bg); color: var(--qz-text); font: 14px/1.6 -apple-system, BlinkMacSystemFont, "Segoe UI", "PingFang SC", "Hiragino Sans GB", "Microsoft YaHei", sans-serif; -webkit-font-smoothing: antialiased; padding: 24px 16px 48px; }
.qz-result { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 12px 16px; border: 1px solid var(--qz-border); border-radius: 10px; margin-bottom: 10px; transition: border-color .15s, background .15s; }
.qz-result:hover { border-color: #c7cbdd; }
.qz-result.avail { border-color: #c6f6d5; background: #f7fef9; }
.qz-result.avail:hover { border-color: #86efac; }
.qz-result .r-domain { font-weight: 600; font-family: ui-monospace, SFMono-Regular, Consolas, monospace; }
.qz-year { display: flex; gap: 8px; flex-wrap: wrap; }
.qz-year button { padding: 8px 14px; border: 1px solid var(--qz-border); border-radius: 8px; background: #fff; cursor: pointer; font-size: 14px; transition: all .15s; }
.qz-year button.on { border-color: var(--qz-brand); background: var(--qz-brand-soft); color: var(--qz-brand); font-weight: 600; }
.qz-opt { display: flex; align-items: flex-start; gap: 8px; padding: 10px 0; cursor: pointer; }
.qz-opt input { margin-top: 3px; accent-color: var(--qz-brand); }
.qz-tpl-item { display: flex; align-items: center; gap: 10px; padding: 10px 12px; border: 1px solid var(--qz-border); border-radius: 10px; margin-bottom: 8px; cursor: pointer; transition: all .15s; }
.qz-tpl-item.on { border-color: var(--qz-brand); background: var(--qz-brand-soft); }
.qz-tpl-item input { accent-color: var(--qz-brand); }
.qz-agree { display: flex; align-items: center; gap: 8px; padding: 6px 0; cursor: pointer; }
.qz-agree input { accent-color: var(--qz-brand); }
.qz-summary { display: flex; align-items: center; justify-content: space-between; gap: 14px; flex-wrap: wrap; padding-top: 16px; border-top: 1px solid #f0f1f6; }
.qz-total { font-size: 22px; font-weight: 700; color: var(--qz-brand); }
.qz-total small { font-size: 13px; color: var(--qz-text-2); font-weight: 400; }
.qz-nav { display: flex; gap: 6px; flex-wrap: wrap; margin-bottom: 16px; }
.qz-nav a { display: inline-block; padding: 6px 14px; border-radius: 20px; font-size: 13px; background: #fff; border: 1px solid var(--qz-border); color: var(--qz-text-2); text-decoration: none; transition: all .15s; }
.qz-nav a:hover { border-color: var(--qz-brand); color: var(--qz-brand); }
</style>
</head>
<body>
<div class="qz-wrap">

  <div class="qz-head">
    <h1>域名注册</h1>
    <p>查询域名可用性，选择年限与信息模板完成注册。</p>
    <div class="qz-nav" style="margin-top:12px;">
      <?php if ($mode === 'client'): ?>
        <a href="<?= htmlspecialchars($qzAccUrl, ENT_QUOTES, 'UTF-8') ?>">云账号</a>
      <?php endif; ?>
      <a href="<?= htmlspecialchars($qzTplUrl, ENT_QUOTES, 'UTF-8') ?>">信息模板</a>
      <a href="<?= htmlspecialchars($qzDomUrl, ENT_QUOTES, 'UTF-8') ?>">我的域名</a>
    </div>
  </div>

  <?php if ($mode === 'client'): ?>
  <div id="qz-tip-unbound" class="qz-tip qz-tip--warn" style="display:none;">
    尚未绑定启明智联账号，无法使用域名注册服务。<a href="<?= htmlspecialchars($qzAccUrl, ENT_QUOTES, 'UTF-8') ?>" style="color:var(--qz-brand);font-weight:600;">立即绑定</a>
  </div>
  <?php endif; ?>

  <?php if ($mode === 'agent'): ?>
  <div class="qz-tip">代理商模式：下单后通过本站支付系统完成支付，支付成功后域名将自动注册。</div>
  <?php endif; ?>

  <!-- 搜索 -->
  <div class="qz-card">
    <div class="qz-card-body">
      <div class="qz-search-row">
        <input type="text" class="qz-input qz-monospace" id="qz-domain" placeholder="输入想要注册的域名前缀，如 mydomain" maxlength="63" autocomplete="off">
        <select class="qz-select" id="qz-suffix"></select>
        <button type="button" class="qz-btn" id="qz-btn-check"><span class="mdi mdi-magnify"></span> 查询</button>
      </div>
      <div style="margin-top:12px;">
        <span class="qz-muted">热门后缀：</span>
        <span id="qz-hot-tlds"></span>
      </div>
    </div>
  </div>

  <!-- 查询结果 -->
  <div class="qz-card" id="qz-results-card" style="display:none;">
    <div class="qz-card-head">
      <h3>查询结果</h3>
      <span class="qz-muted" id="qz-results-count"></span>
    </div>
    <div class="qz-card-body" id="qz-results"></div>
  </div>

  <!-- 注册面板 -->
  <div class="qz-card" id="qz-register-card" style="display:none;">
    <div class="qz-card-head">
      <h3>注册域名 <span class="qz-monospace" id="qz-reg-domain" style="color:var(--qz-brand);"></span></h3>
      <button type="button" class="qz-btn qz-btn--ghost qz-btn--sm" id="qz-btn-cancel"><span class="mdi mdi-close"></span> 取消</button>
    </div>
    <div class="qz-card-body">

      <!-- 年限 -->
      <div class="qz-form-item" style="margin-bottom:18px;">
        <label>注册年限</label>
        <div class="qz-year" id="qz-years"></div>
      </div>

      <!-- 选项 -->
      <div class="qz-form-item" style="margin-bottom:8px;">
        <label>域名选项</label>
        <label class="qz-opt"><input type="checkbox" id="qz-opt-renew" checked> <span>到期自动续费 <span class="qz-muted">（默认开启，可在平台关闭）</span></span></label>
        <label class="qz-opt"><input type="checkbox" id="qz-opt-lock" checked> <span>开启转移锁 <span class="qz-muted">（防止域名被非法转移）</span></span></label>
      </div>

      <!-- 信息模板 -->
      <div class="qz-form-item" style="margin-bottom:12px;">
        <label>信息模板 <span class="req">*</span>
          <a href="<?= htmlspecialchars($qzTplUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" style="float:right;font-size:12px;color:var(--qz-brand);">管理模板</a>
        </label>
        <div id="qz-templates"></div>
      </div>

      <!-- 协议 -->
      <div class="qz-form-item" style="margin-bottom:16px;">
        <label class="qz-agree"><input type="checkbox" id="qz-agree1"> <span>我已阅读并同意 <a href="javascript:void(0)" id="qz-agr1" target="_blank" style="color:var(--qz-brand)">《域名注册协议》</a></span></label>
        <label class="qz-agree"><input type="checkbox" id="qz-agree2"> <span>我已阅读并同意 <a href="javascript:void(0)" id="qz-agr2" target="_blank" style="color:var(--qz-brand)">《域名信息服务协议》</a></span></label>
      </div>

      <!-- 汇总 -->
      <div class="qz-summary">
        <div>
          <div style="font-size:13px;color:var(--qz-text-2);">支付方式 <span class="req">*</span></div>
          <select class="qz-select" id="qz-gateway" style="width:200px;margin-top:4px;"></select>
        </div>
        <div style="text-align:right;">
          <div class="qz-total"><small>合计：</small>¥<span id="qz-total">0.00</span></div>
          <div class="qz-muted" id="qz-markup-note" style="display:none;"></div>
          <div class="qz-muted"><?= $mode === 'agent' ? '使用本站支付系统' : '支付将在启明智联平台完成' ?></div>
        </div>
        <button type="button" class="qz-btn" id="qz-btn-order" style="font-size:16px;padding:10px 32px;"><span class="mdi mdi-arrow-right"></span> 去支付</button>
      </div>

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
  var TPL_URL = <?= json_encode($qzTplUrl, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;

  var state = {
    suffixes: [{ suffix: '.com' }, { suffix: '.cn' }, { suffix: '.net' }],
    defaultSuffix: '.com',
    agreements: { register: '#', service: '#' },
    results: [],
    selected: null,          // {name, prefix, suffix}
    prices: [],              // [{buyyear, buyprice}]
    templates: [],
    templateId: 0,
    gateways: [],
    gateway: '',
    bound: false
  };

  var $domain = $('#qz-domain'), $suffix = $('#qz-suffix'), $results = $('#qz-results'),
      $registerCard = $('#qz-register-card'), $resultsCard = $('#qz-results-card');

  /* ---------- 初始化 ---------- */
  if (QZ_MODE === 'agent') {
    state.bound = true;
  } else {
    QZ.post(QZ_API + '/account_info', {}, function (res) {
      state.bound = !!(res.data && res.data.bound);
      if (!state.bound) $('#qz-tip-unbound').show();
    }, function () { $('#qz-tip-unbound').show(); });
  }

  QZ.post(QZ_API + '/config', {}, function (res) {
    var d = res.data || {};
    if (d.suffixes && d.suffixes.length) state.suffixes = d.suffixes;
    if (d.config) {
      if (d.config.domain_register_agreement_url) state.agreements.register = d.config.domain_register_agreement_url;
      if (d.config.domain_information_service_agreement_url) state.agreements.service = d.config.domain_information_service_agreement_url;
      if (d.config.default_search_domain) state.defaultSuffix = d.config.default_search_domain;
    }
    buildSuffixes();
  }, function (msg) { buildSuffixes(); });

  QZ.post(QZ_API + '/gateways', {}, function (res) {
    state.gateways = (res.data && res.data.list) || [];
    var $g = $('#qz-gateway');
    $g.empty();
    if (!state.gateways.length) {
      $g.append('<option value="">无可用支付方式</option>');
      return;
    }
    $.each(state.gateways, function (i, gw) {
      $g.append('<option value="' + QZ.esc(gw.name) + '">' + QZ.esc(gw.title || gw.name) + '</option>');
    });
    state.gateway = state.gateways[0].name;
  });

  QZ.post(QZ_API + '/templates', {}, function (res) {
    state.templates = (res.data && res.data.list) || [];
  }, function () {});

  function buildSuffixes() {
    var $s = $('#qz-suffix');
    $s.empty();
    var defaultFound = false;
    $.each(state.suffixes, function (i, it) {
      var sfx = it.suffix;
      $s.append('<option value="' + QZ.esc(sfx) + '">' + QZ.esc(sfx) + '</option>');
      if (sfx === state.defaultSuffix) defaultFound = true;
    });
    $s.val(defaultFound ? state.defaultSuffix : (state.suffixes[0] ? state.suffixes[0].suffix : '.com'));
    // 热门后缀标签
    var hot = state.suffixes.slice(0, 6);
    var html = hot.map(function (it) {
      return '<span class="qz-tag" data-sfx="' + QZ.esc(it.suffix) + '">' + QZ.esc(it.suffix) + '</span>';
    }).join('');
    $('#qz-hot-tlds').html(html);
    $('#qz-hot-tlds').off('click').on('click', '.qz-tag', function () {
      $s.val($(this).data('sfx'));
      $s.trigger('change');
    });
  }

  $('#qz-suffix').on('change', function () {
    $('#qz-domain').focus();
  });

  /* ---------- 查询 ---------- */
  function doCheck() {
    var domain = $.trim($domain.val());
    if (!domain) { QZ.toast('请输入域名前缀', 'error'); return; }
    if (!/^[a-z0-9]([a-z0-9\-]{0,62}[a-z0-9])?$/i.test(domain)) { QZ.toast('域名前缀格式不正确', 'error'); return; }
    if (!state.bound) { QZ.toast('请先绑定启明智联账号', 'error'); return; }

    $('#qz-btn-check').prop('disabled', true).html('<span class="mdi mdi-loading mdi-spin"></span> 查询中');
    QZ.post(QZ_API + '/check_domain', { domain: domain, suffix: $suffix.val() }, function (res) {
      state.results = (res.data && res.data.list) || [];
      renderResults();
      $('#qz-btn-check').prop('disabled', false).html('<span class="mdi mdi-magnify"></span> 查询');
    }, function (msg) {
      $('#qz-btn-check').prop('disabled', false).html('<span class="mdi mdi-magnify"></span> 查询');
      QZ.toast(msg, 'error');
    });
  }

  $('#qz-btn-check').on('click', doCheck);
  $domain.on('keydown', function (e) { if (e.which === 13) doCheck(); });

  function renderResults() {
    if (!state.results.length) {
      $resultsCard.hide();
      return;
    }
    $resultsCard.show();
    $('#qz-results-count').text('共 ' + state.results.length + ' 个结果');
    var html = state.results.map(function (r) {
      var avail = r.avail === 1;
      var parts = r.name.split('.');
      var prefix = parts[0], tld = '.' + parts.slice(1).join('.');
      return '<div class="qz-result ' + (avail ? 'avail' : '') + '">' +
        '<div class="qz-flex" style="gap:12px;">' +
          (avail
            ? '<span class="qz-badge qz-badge--green">可注册</span>'
            : '<span class="qz-badge qz-badge--gray">已注册</span>') +
          '<span class="r-domain">' + QZ.esc(r.name) + '</span>' +
        '</div>' +
        (avail
          ? '<button type="button" class="qz-btn qz-btn--sm" data-name="' + QZ.esc(r.name) + '" data-prefix="' + QZ.esc(prefix) + '" data-tld="' + QZ.esc(tld) + '">立即注册</button>'
          : '') +
      '</div>';
    }).join('');
    $results.html(html);
    $results.find('[data-name]').on('click', function () {
      openRegister($(this).data('prefix'), $(this).data('name'));
    });
  }

  /* ---------- 注册面板 ---------- */
  function openRegister(prefix, name) {
    if (!state.bound) { QZ.toast('请先绑定启明智联账号', 'error'); return; }
    state.selected = { prefix: prefix, name: name };
    state.prices = [];
    state.markup = 0;
    state.templateId = 0;
    $('#qz-reg-domain').text(name);
    $registerCard.show();
    $('#qz-years').empty().append('<span class="qz-muted">加载价格中...</span>');
    $('#qz-total').text('0.00');
    $('#qz-markup-note').hide();
    // 自动滚动到下方信息确认区
    $('html, body').animate({ scrollTop: $registerCard.offset().top - 14 }, 400);

    QZ.post(QZ_API + '/price', { name: name }, function (res) {
      state.prices = (res.data && res.data.list) || [];
      state.markup = parseFloat((res.data && res.data.markup) || 0) || 0;
      renderYears();
    }, function (msg) {
      QZ.toast(msg, 'error');
      $('#qz-years').empty().append('<span class="qz-muted">获取价格失败</span>');
    });
    renderTemplates();
  }

  function renderYears() {
    var $y = $('#qz-years');
    $y.empty();
    if (!state.prices.length) {
      $y.append('<span class="qz-muted">暂无价格数据</span>');
      return;
    }
    $.each(state.prices, function (i, p) {
      var avg = (Number(p.buyprice) / Number(p.buyyear)).toFixed(2);
      $y.append('<button type="button" data-year="' + p.buyyear + '" data-price="' + p.buyprice + '">' +
        p.buyyear + ' 年 <span class="qz-muted">¥' + avg + '/年</span></button>');
    });
    $y.children().first().addClass('on');
    $('#qz-total').text(Number(state.prices[0].buyprice).toFixed(2));
    if (state.markup > 0) {
      $('#qz-markup-note').show().text('已包含后缀溢价 ¥' + state.markup.toFixed(2));
    } else {
      $('#qz-markup-note').hide();
    }
    $y.off('click').on('click', 'button', function () {
      $y.children().removeClass('on');
      $(this).addClass('on');
      $('#qz-total').text(Number($(this).data('price')).toFixed(2));
    });
  }

  var TPL_STATUS = { 0: ['未认证', 'red'], 1: ['已认证', 'green'], 2: ['审核中', 'yellow'], 3: ['认证失败', 'red'], 4: ['异常', 'red'] };
  function renderTemplates() {
    var $t = $('#qz-templates');
    $t.empty();
    if (!state.templates.length) {
      $t.append('<div class="qz-empty">暂无信息模板，<a href="' + TPL_URL + '" style="color:var(--qz-brand);">去创建</a></div>');
      return;
    }
    $.each(state.templates, function (i, t) {
      var st = TPL_STATUS[String(t.status)] || ['未知', 'gray'];
      var typeName = t.type === 'enterprise' ? '企业' : '个人';
      $t.append('<label class="qz-tpl-item" data-id="' + t.id + '">' +
        '<input type="radio" name="qz-tpl" value="' + t.id + '">' +
        '<div class="qz-grow">' +
          '<div><strong>' + QZ.esc(t.zh_owner || t.en_owner || '未命名') + '</strong> <span class="qz-muted">' + typeName + '</span></div>' +
          '<div class="qz-muted">' + QZ.esc(t.email || '') + ' · ' + QZ.esc(t.phone || '') + '</div>' +
        '</div>' +
        '<span class="qz-badge qz-badge--' + st[1] + '">' + st[0] + '</span>' +
      '</label>');
    });
    // 默认选中第一个已认证模板
    var pick = null;
    $.each(state.templates, function (i, t) { if (t.status === 1 && !pick) pick = t.id; });
    if (!pick && state.templates.length) pick = state.templates[0].id;
    $t.find('input[value="' + pick + '"]').prop('checked', true);
    state.templateId = pick;
    $t.off('click').on('click', 'label', function () {
      $(this).find('input').prop('checked', true);
      state.templateId = parseInt($(this).data('id'), 10);
    });
  }

  // 协议链接
  $('#qz-agr1').on('click', function (e) {
    if (state.agreements.register && state.agreements.register !== '#') { window.open(state.agreements.register); e.preventDefault(); }
  });
  $('#qz-agr2').on('click', function (e) {
    if (state.agreements.service && state.agreements.service !== '#') { window.open(state.agreements.service); e.preventDefault(); }
  });

  $('#qz-gateway').on('change', function () { state.gateway = $(this).val(); });

  $('#qz-btn-cancel').on('click', function () {
    $registerCard.hide();
    state.selected = null;
    state.prices = [];
  });

  /* ---------- 下单 ---------- */
  $('#qz-btn-order').on('click', function () {
    if (!state.selected) { QZ.toast('请先选择域名', 'error'); return; }
    if (!state.templateId) { QZ.toast('请选择信息模板', 'error'); return; }
    if (!$('#qz-agree1').prop('checked') || !$('#qz-agree2').prop('checked')) { QZ.toast('请先阅读并同意注册协议', 'error'); return; }
    if (!state.gateway) { QZ.toast('请选择支付方式', 'error'); return; }

    var year = parseInt($('#qz-years .on').data('year'), 10) || 1;
    var $btn = $('#qz-btn-order');
    $btn.prop('disabled', true).html('<span class="mdi mdi-loading mdi-spin"></span> 下单中...');
    QZ.post(QZ_API + '/place_order', {
      domain: state.selected.name,
      year: year,
      template_id: state.templateId,
      gateway: state.gateway,
      auto_renew: $('#qz-opt-renew').prop('checked') ? 1 : 0,
      lock_status: $('#qz-opt-lock').prop('checked') ? 1 : 0
    }, function (res) {
      var d = res.data || {};
      if (d.mode === 'agent') {
        // 代理商模式：渲染支付插件返回的 HTML（自动提交表单 / 二维码）
        if (d.html) {
          document.open();
          document.write(d.html);
          document.close();
        } else {
          $btn.prop('disabled', false).html('<span class="mdi mdi-arrow-right"></span> 去支付');
          QZ.toast('支付跳转失败，请重试', 'error');
        }
        return;
      }
      var orderId = d.order_id;
      if (orderId) {
        location.href = PAY_BASE + '&order=' + orderId;
      } else {
        $btn.prop('disabled', false).html('<span class="mdi mdi-arrow-right"></span> 去支付');
        QZ.toast(res.msg || '下单失败', 'error');
      }
    }, function (msg) {
      $btn.prop('disabled', false).html('<span class="mdi mdi-arrow-right"></span> 去支付');
      QZ.toast(msg, 'error');
    });
  });
});
</script>
</body>
</html>
