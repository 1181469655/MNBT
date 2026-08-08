<?php
/**
 * qmzl_domain - 用户端云账号绑定页（仅 client 模式）
 *
 * 上游启明智联登录需人机验证（QmCaptcha 点选），参照 qiming-web AuthView.vue 实现：
 * 先获取验证码图片 → 点击图中指定图标 → 校验 → 再以 AES 加密密码登录换取 JWT。
 */
if (!defined('IN_CRONLITE')) exit;
$qzCss   = qmzl_asset_url('qmzl.css');
$qzJs    = qmzl_asset_url('qmzl.js');
$qzMdi   = mnbt_asset_url('css/materialdesignicons.min.css');
$qzJq    = mnbt_asset_url('js/jquery.min.js');
$qzApi   = qmzl_url(qmzl_route_prefix() . '/api');
$qzIdxUrl = qmzl_url(qmzl_route_prefix());
$qzTplUrl = qmzl_url(qmzl_route_prefix() . '/templates');
$qzDomUrl = qmzl_url(qmzl_route_prefix() . '/domains');
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>云账号 - 域名服务</title>
<link rel="stylesheet" href="<?= htmlspecialchars($qzMdi, ENT_QUOTES, 'UTF-8') ?>">
<link rel="stylesheet" href="<?= htmlspecialchars($qzCss, ENT_QUOTES, 'UTF-8') ?>">
<style>
body { margin: 0; background: var(--qz-bg); color: var(--qz-text); font: 14px/1.6 -apple-system, BlinkMacSystemFont, "Segoe UI", "PingFang SC", "Hiragino Sans GB", "Microsoft YaHei", sans-serif; -webkit-font-smoothing: antialiased; padding: 24px 16px 48px; }
.qz-nav { display: flex; gap: 6px; flex-wrap: wrap; margin-bottom: 16px; }
.qz-nav a { display: inline-block; padding: 6px 14px; border-radius: 20px; font-size: 13px; background: #fff; border: 1px solid var(--qz-border); color: var(--qz-text-2); text-decoration: none; transition: all .15s; }
.qz-nav a:hover { border-color: var(--qz-brand); color: var(--qz-brand); }

/* 绑定卡片居中 */
.qz-bind-card { max-width: 640px; margin: 0 auto; }

/* 步骤条 */
.qz-steps { display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 20px; }
.qz-steps .step { display: inline-flex; align-items: center; gap: 6px; font-size: 13px; color: var(--qz-text-3); }
.qz-steps .step i { display: inline-flex; width: 20px; height: 20px; border-radius: 50%; background: #f1f3f6; color: var(--qz-text-3); font-style: normal; align-items: center; justify-content: center; font-size: 11px; flex-shrink: 0; }
.qz-steps .step.on { color: var(--qz-brand); }
.qz-steps .step.on i { background: var(--qz-brand); color: #fff; }

/* 人机验证区 */
.qz-captcha-box { margin: 18px 0; padding: 14px 16px; border: 1px dashed var(--qz-border); border-radius: 10px; background: #fafbfd; display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }
.qz-captcha-box .cap-label { display: flex; align-items: center; gap: 8px; font-size: 13px; color: var(--qz-text-2); }

/* 全宽按钮 */
.qz-btn--block { display: flex; width: 100%; justify-content: center; align-items: center; padding: 11px; font-size: 15px; }

/* 账号密码纵向排列 */
.qz-form-stack { display: flex; flex-direction: column; gap: 14px; }

/* 已绑定摘要 */
.qz-bound-summary { display: flex; align-items: center; gap: 14px; padding: 4px 0 16px; margin-bottom: 6px; border-bottom: 1px solid #f0f1f6; }
.qz-bound-summary .avatar { width: 48px; height: 48px; border-radius: 50%; background: var(--qz-brand-soft); color: var(--qz-brand); display: flex; align-items: center; justify-content: center; font-size: 24px; flex-shrink: 0; }
.qz-bound-summary .acc { font-size: 16px; font-weight: 600; font-family: ui-monospace, SFMono-Regular, Consolas, monospace; }
.qz-bound-summary .meta { font-size: 12px; color: var(--qz-text-3); margin-top: 2px; }

/* 使用说明网格 */
.qz-feat-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
.qz-feat { display: flex; gap: 12px; padding: 14px; border: 1px solid var(--qz-border); border-radius: 10px; background: #fff; }
.qz-feat .ic { width: 36px; height: 36px; border-radius: 9px; background: var(--qz-brand-soft); color: var(--qz-brand); display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0; }
.qz-feat .t { font-size: 13px; color: var(--qz-text); font-weight: 600; }
.qz-feat .d { font-size: 12px; color: var(--qz-text-3); margin-top: 3px; line-height: 1.55; }

/* 验证码弹窗 */
.cap-wrap { position: relative; width: 100%; max-width: 340px; margin: 0 auto; border-radius: 10px; overflow: hidden; background: #f5f5f5; }
.cap-wrap img { width: 100%; display: block; cursor: pointer; }
.cap-dot { position: absolute; width: 28px; height: 28px; border-radius: 50%; background: rgba(22,163,74,.8); color: #fff; text-align: center; line-height: 28px; font-size: 13px; pointer-events: none; z-index: 3; }
.cap-loading { position: absolute; inset: 0; background: rgba(255,255,255,.85); display: flex; align-items: center; justify-content: center; z-index: 5; }
.cap-spinner { width: 30px; height: 30px; border: 3px solid #e5e7eb; border-top-color: var(--qz-brand); border-radius: 50%; animation: qzSpin .7s linear infinite; }
@keyframes qzSpin { to { transform: rotate(360deg); } }

@media (max-width: 640px) {
  .qz-feat-grid { grid-template-columns: 1fr; }
}
</style>
</head>
<body>
<div class="qz-wrap">

  <div class="qz-head">
    <h1>启明智联账号</h1>
    <p>绑定你的启明智联平台账号后即可注册域名，支付在启明智联平台完成。</p>
    <div class="qz-nav" style="margin-top:12px;">
      <a href="<?= htmlspecialchars($qzIdxUrl, ENT_QUOTES, 'UTF-8') ?>">域名注册</a>
      <a href="<?= htmlspecialchars($qzTplUrl, ENT_QUOTES, 'UTF-8') ?>">信息模板</a>
      <a href="<?= htmlspecialchars($qzDomUrl, ENT_QUOTES, 'UTF-8') ?>">我的域名</a>
    </div>
  </div>

  <!-- 未绑定：绑定表单 -->
  <div id="qz-unbound" style="display:none;">
    <div class="qz-card qz-bind-card">
      <div class="qz-card-head"><h3>绑定启明智联账号</h3></div>
      <div class="qz-card-body">
        <div class="qz-tip">请填写你在 <a href="https://cloud.qimingidc.cn" target="_blank" rel="noopener" style="color:var(--qz-brand)">cloud.qimingidc.cn</a> 注册的账号与登录密码，并完成人机验证。账号密码仅用于向平台换取登录凭证，加密存储于本站。</div>

        <div class="qz-steps">
          <span class="step on"><i>1</i> 填写账号密码</span>
          <span class="step"><i>2</i> 人机验证</span>
          <span class="step"><i>3</i> 完成绑定</span>
        </div>

        <div class="qz-form-stack">
          <div class="qz-form-item">
            <label>平台账号 <span class="req">*</span></label>
            <input type="text" class="qz-input" id="qz-account" placeholder="手机号或邮箱" maxlength="200" autocomplete="username">
          </div>
          <div class="qz-form-item">
            <label>平台密码 <span class="req">*</span></label>
            <input type="password" class="qz-input" id="qz-password" placeholder="登录密码" maxlength="100" autocomplete="current-password">
          </div>
        </div>

        <div class="qz-captcha-box">
          <span class="cap-label"><span class="mdi mdi-shield-check-outline" style="color:var(--qz-brand);font-size:20px;"></span> 人机验证 <span class="req">*</span></span>
          <button type="button" class="qz-btn qz-btn--ghost" id="qz-btn-captcha" style="padding:7px 16px;">
            <span id="qz-cap-status">请完成人机验证</span>
          </button>
        </div>

        <button type="button" class="qz-btn qz-btn--block" id="qz-btn-save"><span class="mdi mdi-link-variant"></span> 绑定并验证</button>
      </div>
    </div>
  </div>

  <!-- 已绑定：当前绑定信息 -->
  <div id="qz-bound" style="display:none;">
    <div class="qz-card qz-bind-card">
      <div class="qz-card-head"><h3>当前绑定</h3></div>
      <div class="qz-card-body">
        <div class="qz-bound-summary">
          <div class="avatar"><span class="mdi mdi-account"></span></div>
          <div class="qz-grow">
            <div class="acc" id="qz-bound-account">-</div>
            <div class="meta" id="qz-bound-updated">-</div>
          </div>
          <span id="qz-bound-status"></span>
        </div>
        <div class="qz-tip" id="qz-bound-status-tip"></div>
        <div class="qz-actions" style="margin-top:16px;">
          <button type="button" class="qz-btn" id="qz-btn-edit"><span class="mdi mdi-pencil-outline"></span> 更换账号</button>
          <button type="button" class="qz-btn qz-btn--danger" id="qz-btn-unbind"><span class="mdi mdi-link-variant-off"></span> 解绑</button>
        </div>
      </div>
    </div>
  </div>

  <!-- 使用说明 -->
  <div class="qz-card qz-bind-card">
    <div class="qz-card-head"><h3>使用说明</h3></div>
    <div class="qz-card-body">
      <div class="qz-feat-grid">
        <div class="qz-feat">
          <div class="ic"><span class="mdi mdi-account-search-outline"></span></div>
          <div><div class="t">账号数据为准</div><div class="d">域名查询、价格、信息模板均以你的平台账号数据为准。</div></div>
        </div>
        <div class="qz-feat">
          <div class="ic"><span class="mdi mdi-shield-key-outline"></span></div>
          <div><div class="t">凭证过期重绑</div><div class="d">登录凭证过期后需重新完成人机验证并绑定。</div></div>
        </div>
        <div class="qz-feat">
          <div class="ic"><span class="mdi mdi-id-card"></span></div>
          <div><div class="t">模板实名认证</div><div class="d">信息模板需先完成实名认证（上传证件照片）后才能注册域名。</div></div>
        </div>
        <div class="qz-feat">
          <div class="ic"><span class="mdi mdi-cash-multiple"></span></div>
          <div><div class="t">支付在上游</div><div class="d">下单后由你自行在平台完成支付，支付成功域名即注册生效。</div></div>
        </div>
      </div>
    </div>
  </div>

</div>

<!-- 人机验证弹窗（点选验证） -->
<div class="qz-mask" id="qz-mask-captcha">
  <div class="qz-modal" style="max-width:420px;">
    <div class="qz-modal-head">
      <h3>人机验证</h3>
      <button type="button" class="qz-modal-close" data-close>&times;</button>
    </div>
    <div class="qz-modal-body" style="text-align:center;">
      <div class="cap-wrap" id="cap-wrap">
        <img id="cap-img" alt="验证码" style="opacity:0;">
        <div class="cap-loading" id="cap-loading"><div class="cap-spinner"></div></div>
      </div>
      <div style="margin-top:12px;font-size:14px;">请依次点击图中的：<span id="cap-icons" style="color:var(--qz-brand);font-weight:600;">...</span></div>
      <div id="cap-msg" class="qz-tip qz-tip--danger" style="display:none;margin-top:10px;"></div>
    </div>
    <div class="qz-modal-foot">
      <button type="button" class="qz-btn qz-btn--ghost" id="qz-cap-refresh"><span class="mdi mdi-refresh"></span> 刷新图片</button>
    </div>
  </div>
</div>

<script src="<?= htmlspecialchars($qzJq, ENT_QUOTES, 'UTF-8') ?>"></script>
<script src="<?= htmlspecialchars($qzJs, ENT_QUOTES, 'UTF-8') ?>"></script>
<script>
$(function () {
  var QZ_API = <?= json_encode($qzApi, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;
  var bound = null;
  var ICON_MAP = { star: '★', tri: '▲', square: '■', round: '●' };
  var captcha = { base64: '', icon: '', sign: '', dots: [] };
  var verifiedCaptcha = '';
  var verifiedToken = '';
  var capLoading = false;

  function maskAccount(acc) {
    if (!acc) return '-';
    return acc.length > 4 ? acc.slice(0, 2) + '***' + acc.slice(-2) : '***';
  }

  function iconSymbols(str) {
    if (!str) return '...';
    return String(str).split(',').map(function (n) {
      var t = n.trim();
      return ICON_MAP[t] || t;
    }).join(' ');
  }

  function render() {
    if (bound) {
      $('#qz-bound').show();
      $('#qz-unbound').hide();
      $('#qz-bound-account').text(maskAccount(bound.account));
      $('#qz-bound-updated').text('最近更新：' + (bound.updated_at || '-'));
      if (bound.status === 'error') {
        $('#qz-bound-status').html('<span class="qz-badge qz-badge--red">异常</span>');
        $('#qz-bound-status-tip').addClass('qz-tip--danger').removeClass('qz-tip--success')
          .html('上次登录验证失败：' + QZ.esc(bound.last_msg || '未知原因') + '，可更换账号重新绑定。').show();
      } else {
        $('#qz-bound-status').html('<span class="qz-badge qz-badge--green">正常</span>');
        $('#qz-bound-status-tip').addClass('qz-tip--success').removeClass('qz-tip--danger')
          .html('账号绑定正常，可正常使用域名注册服务。').show();
      }
    } else {
      $('#qz-bound').hide();
      $('#qz-unbound').show();
    }
  }

  function loadInfo() {
    QZ.post(QZ_API + '/account_info', {}, function (res) {
      if (res.qk === 1) {
        bound = res.data && res.data.bound ? res.data : null;
      } else {
        bound = null;
      }
      render();
    }, function () { bound = null; render(); });
  }

  /* ---------- 人机验证 ---------- */
  function clearCapLoad() {
    capLoading = false;
    $('#cap-loading').hide();
  }

  function refreshCaptcha() {
    capLoading = true;
    $('#cap-loading').show();
    $('#cap-msg').hide();
    captcha = { base64: '', icon: '', sign: '', dots: [] };
    renderDots();

    var $img = $('#cap-img');
    $img.css('opacity', 0).off('load').off('error');
    // 先绑定事件再设置 src，避免图片缓存导致 load 先于事件挂载而永远不显示
    $img.on('load', function () {
      $(this).css('opacity', 1);
      clearCapLoad();
    });
    $img.on('error', function () {
      clearCapLoad();
      $('#cap-msg').text('验证码图片加载失败，请点击刷新重试').show();
    });

    QZ.post(QZ_API + '/captcha_refresh', {}, function (res) {
      var d = res.data || {};
      if (!d.base64 || !d.sign) {
        clearCapLoad();
        QZ.toast('获取验证码失败', 'error');
        return;
      }
      captcha.base64 = d.base64;
      captcha.icon = d.captcha_icon || '';
      captcha.sign = d.sign;
      var src = d.base64.indexOf('data:') === 0 ? d.base64 : 'data:image/png;base64,' + d.base64;
      $img.attr('src', src);
      $('#cap-icons').text(iconSymbols(d.captcha_icon));
    }, function (msg) {
      clearCapLoad();
      QZ.toast(msg || '获取验证码失败', 'error');
    });
  }

  function renderDots() {
    $('#cap-wrap .cap-dot').remove();
    captcha.dots.forEach(function (d, i) {
      $('<div class="cap-dot" style="left:' + d.x + 'px;top:' + d.y + 'px;">' + (i + 1) + '</div>').appendTo('#cap-wrap');
    });
  }

  function onImgClick(e) {
    if (capLoading || captcha.dots.length >= 3) return;
    var img = document.getElementById('cap-img');
    if (!img || !img.naturalWidth) return;
    var rect = img.getBoundingClientRect();
    if (rect.width === 0 || rect.height === 0) return;
    var displayX = e.clientX - rect.left;
    var displayY = e.clientY - rect.top;
    var scaleX = img.naturalWidth / rect.width;
    var scaleY = img.naturalHeight / rect.height;
    captcha.dots.push({
      x: displayX - 14,
      y: displayY - 14,
      imgX: displayX * scaleX,
      imgY: displayY * scaleY
    });
    renderDots();
    if (captcha.dots.length === 3) verifyCaptcha();
  }

  function verifyCaptcha() {
    if (capLoading) return;
    var poi = captcha.dots.map(function (d) {
      return Math.round(d.imgX) + '-' + Math.round(d.imgY);
    }).join(',');
    var value = '28||' + poi;
    capLoading = true;
    $('#cap-loading').show();
    QZ.post(QZ_API + '/captcha_verify', { captcha: value, token: captcha.sign }, function (res) {
      verifiedCaptcha = value;
      verifiedToken = captcha.sign;
      clearCapLoad();
      $('#qz-mask-captcha').removeClass('show');
      $('#qz-cap-status').text('已通过人机验证');
      $('#qz-btn-captcha').css({ 'border-color': 'var(--qz-success)', 'color': 'var(--qz-success)', 'background': '#ecfdf5' });
    }, function (msg) {
      clearCapLoad();
      $('#cap-msg').text(msg || '验证失败，请重试').show();
      setTimeout(refreshCaptcha, 1000);
    });
  }

  $('#qz-btn-captcha').on('click', function () {
    $('#qz-mask-captcha').addClass('show');
    refreshCaptcha();
  });
  $('#qz-cap-refresh').on('click', refreshCaptcha);
  $('#cap-wrap').on('click', function (e) {
    if (e.target === this || e.target.id === 'cap-loading') return;
    onImgClick(e);
  });

  /* ---------- 绑定 ---------- */
  $('#qz-btn-save').on('click', function () {
    var account = $.trim($('#qz-account').val());
    var password = $('#qz-password').val();
    if (!account || !password) { QZ.toast('请输入账号和密码', 'error'); return; }
    if (!verifiedCaptcha || !verifiedToken) {
      QZ.toast('请先完成人机验证', 'error');
      $('#qz-mask-captcha').addClass('show');
      refreshCaptcha();
      return;
    }
    $(this).prop('disabled', true);
    QZ.post(QZ_API + '/save_account', {
      account: account,
      password: password,
      captcha: verifiedCaptcha,
      captcha_token: verifiedToken
    }, function (res) {
      if (res.qk === 1) {
        QZ.toast('绑定成功', 'success');
        $('#qz-account').val('');
        $('#qz-password').val('');
        $('#qz-btn-captcha').css({});
        $('#qz-cap-status').text('请完成人机验证');
        verifiedCaptcha = '';
        verifiedToken = '';
        loadInfo();
      } else {
        QZ.toast(res.msg || '绑定失败', 'error');
      }
      $('#qz-btn-save').prop('disabled', false);
    }, function (msg) {
      QZ.toast(msg, 'error');
      $('#qz-btn-save').prop('disabled', false);
    });
  });

  $('#qz-btn-edit').on('click', function () {
    $('#qz-bound').hide();
    $('#qz-unbound').show();
    $('#qz-account').val(bound ? bound.account : '');
    $('#qz-password').val('').focus();
  });

  $('#qz-btn-unbind').on('click', function () {
    if (!confirm('确定解绑当前启明智联账号？')) return;
    QZ.post(QZ_API + '/unbind', {}, function (res) {
      if (res.qk === 1) { QZ.toast('已解绑', 'success'); loadInfo(); }
      else QZ.toast(res.msg || '解绑失败', 'error');
    });
  });

  $('[data-close]').on('click', function () { $(this).closest('.qz-mask').removeClass('show'); });

  loadInfo();
});
</script>
</body>
</html>
