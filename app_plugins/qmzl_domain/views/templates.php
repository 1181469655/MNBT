<?php
/**
 * qmzl_domain - 用户端信息模板管理页
 *
 * 信息模板用于域名注册时提交所有者实名信息（上游实名认证）。
 * 支持：列表 / 创建 / 编辑 / 删除 / 提交实名认证（证件照片上传）。
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
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>信息模板 - 域名服务</title>
<link rel="stylesheet" href="<?= htmlspecialchars($qzMdi, ENT_QUOTES, 'UTF-8') ?>">
<link rel="stylesheet" href="<?= htmlspecialchars($qzCss, ENT_QUOTES, 'UTF-8') ?>">
<style>
body { margin: 0; background: var(--qz-bg); color: var(--qz-text); font: 14px/1.6 -apple-system, BlinkMacSystemFont, "Segoe UI", "PingFang SC", "Hiragino Sans GB", "Microsoft YaHei", sans-serif; -webkit-font-smoothing: antialiased; padding: 24px 16px 48px; }
.file-box { display: flex; align-items: center; gap: 10px; min-width: 0; }
.file-name { font-size: 12px; color: var(--qz-text-3); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; min-width: 0; }
.qz-nav { display: flex; gap: 6px; flex-wrap: wrap; margin-bottom: 16px; }
.qz-nav a { display: inline-block; padding: 6px 14px; border-radius: 20px; font-size: 13px; background: #fff; border: 1px solid var(--qz-border); color: var(--qz-text-2); text-decoration: none; transition: all .15s; }
.qz-nav a:hover { border-color: var(--qz-brand); color: var(--qz-brand); }

/* 模板表单弹窗：加宽 + flex 行布局（避免 grid 重叠） */
#qz-mask-form .qz-modal { max-width: 780px; }
#qz-mask-form .qz-modal-body { padding: 20px 24px; }
.f-rows { display: flex; flex-direction: column; gap: 14px; }
.f-row { display: flex; gap: 20px; }
.f-cell { flex: 1; min-width: 0; display: flex; flex-direction: column; gap: 6px; }
.f-cell--full { flex-basis: 100%; }
@media (max-width: 640px) { .f-row { flex-direction: column; gap: 14px; } }
</style>
</head>
<body>
<div class="qz-wrap">

  <div class="qz-head">
    <h1>信息模板</h1>
    <p>域名注册时需提交所有者实名信息，请先创建并完成实名认证。</p>
    <div class="qz-nav" style="margin-top:12px;">
      <a href="<?= htmlspecialchars($qzIdxUrl, ENT_QUOTES, 'UTF-8') ?>">域名注册</a>
      <?php if ($mode === 'client'): ?>
        <a href="<?= htmlspecialchars($qzAccUrl, ENT_QUOTES, 'UTF-8') ?>">云账号</a>
      <?php endif; ?>
    </div>
  </div>

  <?php if ($mode === 'client'): ?>
  <div id="qz-tip-unbound" class="qz-tip qz-tip--warn" style="display:none;">
    尚未绑定启明智联账号。<a href="<?= htmlspecialchars($qzAccUrl, ENT_QUOTES, 'UTF-8') ?>" style="color:var(--qz-brand);font-weight:600;">立即绑定</a>
  </div>
  <?php endif; ?>

  <div class="qz-card">
    <div class="qz-card-head">
      <h3>模板列表</h3>
      <button type="button" class="qz-btn qz-btn--sm" id="qz-btn-add"><span class="mdi mdi-plus"></span> 新建模板</button>
    </div>
    <div class="qz-card-body" id="qz-tpl-list">
      <div class="qz-loading">加载中...</div>
    </div>
  </div>

</div>

<!-- 模板表单弹窗 -->
<div class="qz-mask" id="qz-mask-form">
  <div class="qz-modal">
    <div class="qz-modal-head">
      <h3 id="qz-form-title">新建模板</h3>
      <button type="button" class="qz-modal-close" data-close>&times;</button>
    </div>
    <div class="qz-modal-body">
      <input type="hidden" id="f-id" value="0">
      <div class="f-rows">
        <div class="f-row">
          <div class="f-cell">
            <label>模板类型 <span class="req">*</span></label>
            <select class="qz-select" id="f-type">
              <option value="personal">个人</option>
              <option value="enterprise">企业</option>
            </select>
          </div>
          <div class="f-cell">
            <label>证件类型 <span class="req">*</span></label>
            <select class="qz-select" id="f-idtype"></select>
          </div>
        </div>
        <div class="f-row">
          <div class="f-cell">
            <label>所有者（中文）<span class="req">*</span></label>
            <input type="text" class="qz-input" id="f-zh_owner" maxlength="120">
            <span class="qz-hint">个人填姓名，企业填公司名</span>
          </div>
          <div class="f-cell">
            <label>联系人全名（中文）<span class="req">*</span></label>
            <input type="text" class="qz-input" id="f-zh_all_name" maxlength="120">
          </div>
        </div>
        <div class="f-row">
          <div class="f-cell">
            <label>所有者（英文/拼音大写）<span class="req">*</span></label>
            <input type="text" class="qz-input" id="f-en_owner" maxlength="120" placeholder="ZHANG SAN">
          </div>
          <div class="f-cell">
            <label>联系人姓氏（英文）<span class="req">*</span></label>
            <input type="text" class="qz-input" id="f-en_last_name" maxlength="60" placeholder="ZHANG">
          </div>
        </div>
        <div class="f-row">
          <div class="f-cell">
            <label>联系人名字（英文）<span class="req">*</span></label>
            <input type="text" class="qz-input" id="f-en_first_name" maxlength="60" placeholder="SAN">
          </div>
          <div class="f-cell">
            <label>电子邮箱 <span class="req">*</span></label>
            <input type="email" class="qz-input" id="f-email" maxlength="120">
          </div>
        </div>
        <div class="f-row">
          <div class="f-cell">
            <label>手机号码 <span class="req">*</span></label>
            <input type="text" class="qz-input" id="f-phone" maxlength="30">
          </div>
          <div class="f-cell">
            <label>证件号码 <span class="req">*</span></label>
            <input type="text" class="qz-input" id="f-idnum" maxlength="60">
          </div>
        </div>
        <div class="f-row">
          <div class="f-cell">
            <label>邮政编码</label>
            <input type="text" class="qz-input" id="f-postal_code" maxlength="20" value="">
          </div>
          <div class="f-cell">
            <label>国家代码</label>
            <input type="text" class="qz-input" id="f-country" maxlength="10" value="CN">
          </div>
        </div>
        <div class="f-row">
          <div class="f-cell f-cell--full">
            <label>中文地址 <span class="req">*</span></label>
            <input type="text" class="qz-input" id="f-zh_address" maxlength="255" placeholder="省市区 + 详细地址">
          </div>
        </div>
        <div class="f-row">
          <div class="f-cell f-cell--full">
            <label>英文地址（拼音大写）<span class="req">*</span></label>
            <input type="text" class="qz-input" id="f-en_address" maxlength="255" placeholder="XXX ROAD, XXX DISTRICT, BEIJING, CHINA">
          </div>
        </div>
        <div class="f-row">
          <div class="f-cell f-cell--full">
            <label>证件正面照片 <span class="req">*</span></label>
            <div class="file-box">
              <label class="qz-btn qz-btn--ghost qz-btn--sm" style="cursor:pointer;flex-shrink:0;">选择文件<input type="file" id="f-img_front" accept="image/*" style="display:none;"></label>
              <span class="file-name" id="f-img_front-name">未选择</span>
            </div>
          </div>
        </div>
        <div class="f-row">
          <div class="f-cell f-cell--full">
            <label>证件反面照片（可选）</label>
            <div class="file-box">
              <label class="qz-btn qz-btn--ghost qz-btn--sm" style="cursor:pointer;flex-shrink:0;">选择文件<input type="file" id="f-img_back" accept="image/*" style="display:none;"></label>
              <span class="file-name" id="f-img_back-name">未选择</span>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="qz-modal-foot">
      <button type="button" class="qz-btn qz-btn--ghost" data-close>取消</button>
      <button type="button" class="qz-btn" id="qz-btn-save"><span class="mdi mdi-content-save"></span> 保存</button>
    </div>
  </div>
</div>

<!-- 实名认证弹窗 -->
<div class="qz-mask" id="qz-mask-cert">
  <div class="qz-modal" style="max-width:440px;">
    <div class="qz-modal-head">
      <h3>提交实名认证</h3>
      <button type="button" class="qz-modal-close" data-close>&times;</button>
    </div>
    <div class="qz-modal-body">
      <input type="hidden" id="c-id" value="0">
      <div class="qz-tip">提交证件照片进行实名认证，审核通过后（状态为“已认证”）方可注册域名。</div>
      <div class="qz-form-item" style="margin-bottom:12px;">
        <label>证件正面照片 <span class="req">*</span></label>
        <div class="file-box">
          <label class="qz-btn qz-btn--ghost qz-btn--sm" style="cursor:pointer;">选择文件<input type="file" id="c-img_front" accept="image/*" style="display:none;"></label>
          <span class="file-name" id="c-img_front-name">未选择</span>
        </div>
      </div>
      <div class="qz-form-item">
        <label>证件反面照片（可选）</label>
        <div class="file-box">
          <label class="qz-btn qz-btn--ghost qz-btn--sm" style="cursor:pointer;">选择文件<input type="file" id="c-img_back" accept="image/*" style="display:none;"></label>
          <span class="file-name" id="c-img_back-name">未选择</span>
        </div>
      </div>
    </div>
    <div class="qz-modal-foot">
      <button type="button" class="qz-btn qz-btn--ghost" data-close>取消</button>
      <button type="button" class="qz-btn" id="qz-btn-cert"><span class="mdi mdi-shield-check-outline"></span> 提交认证</button>
    </div>
  </div>
</div>

<script src="<?= htmlspecialchars($qzJq, ENT_QUOTES, 'UTF-8') ?>"></script>
<script src="<?= htmlspecialchars($qzJs, ENT_QUOTES, 'UTF-8') ?>"></script>
<script>
$(function () {
  var QZ_MODE = <?= json_encode($mode, JSON_UNESCAPED_UNICODE) ?>;
  var QZ_API = <?= json_encode($qzApi, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;
  var IDTYPE = {
    personal: [['SFZ', '身份证'], ['HZ', '护照'], ['GAJMTX', '港澳通行证'], ['TWJMTX', '台湾通行证']],
    enterprise: [['YYZZ', '营业执照'], ['ORG', '组织机构代码证'], ['TYDM', '统一社会信用代码证']]
  };
  var STATUS = { 0: ['未认证', 'red'], 1: ['已认证', 'green'], 2: ['审核中', 'yellow'], 3: ['认证失败', 'red'], 4: ['异常', 'red'] };
  var editing = null; // 当前编辑的模板对象

  if (QZ_MODE === 'client') {
    QZ.post(QZ_API + '/account_info', {}, function (res) {
      if (!(res.data && res.data.bound)) $('#qz-tip-unbound').show();
    }, function () { $('#qz-tip-unbound').show(); });
  }

  function renderIdType() {
    var type = $('#f-type').val();
    var $sel = $('#f-idtype');
    $sel.empty();
    $.each(IDTYPE[type] || IDTYPE.personal, function (i, it) {
      $sel.append('<option value="' + it[0] + '">' + it[1] + '</option>');
    });
  }

  function loadList() {
    QZ.post(QZ_API + '/templates', {}, function (res) {
      var list = (res.data && res.data.list) || [];
      var $box = $('#qz-tpl-list');
      if (!list.length) {
        $box.html('<div class="qz-empty">暂无模板，点击右上角“新建模板”创建</div>');
        return;
      }
      var html = '<table class="qz-table"><thead><tr>' +
        '<th>所有者</th><th>类型</th><th>邮箱</th><th>手机</th><th>认证状态</th><th style="width:220px;">操作</th>' +
        '</tr></thead><tbody>';
      $.each(list, function (i, t) {
        var st = STATUS[String(t.status)] || ['未知', 'gray'];
        var typeName = t.type === 'enterprise' ? '企业' : '个人';
        html += '<tr>' +
          '<td>' + QZ.esc(t.zh_owner || t.en_owner || '未命名') + '</td>' +
          '<td>' + typeName + '</td>' +
          '<td>' + QZ.esc(t.email || '-') + '</td>' +
          '<td>' + QZ.esc(t.phone || '-') + '</td>' +
          '<td><span class="qz-badge qz-badge--' + st[1] + '">' + st[0] + '</span></td>' +
          '<td class="qz-actions">' +
            '<button type="button" class="qz-btn qz-btn--ghost qz-btn--sm" data-act="edit" data-id="' + t.id + '">编辑</button>' +
            '<button type="button" class="qz-btn qz-btn--ghost qz-btn--sm" data-act="cert" data-id="' + t.id + '">实名认证</button>' +
            '<button type="button" class="qz-btn qz-btn--danger qz-btn--sm" data-act="del" data-id="' + t.id + '">删除</button>' +
          '</td></tr>';
      });
      html += '</tbody></table>';
      $box.html(html);

      $box.find('[data-act]').on('click', function () {
        var act = $(this).data('act'), id = parseInt($(this).data('id'), 10);
        var t = null;
        $.each(list, function (i, x) { if (parseInt(x.id, 10) === id) t = x; });
        if (!t) return;
        if (act === 'edit') openForm(t);
        else if (act === 'cert') openCert(id);
        else if (act === 'del') delTemplate(id);
      });
    }, function (msg) {
      $('#qz-tpl-list').html('<div class="qz-empty">' + QZ.esc(msg) + '</div>');
    });
  }

  /* ---------- 表单 ---------- */
  function openForm(t) {
    editing = t || null;
    $('#f-id').val(t ? t.id : 0);
    $('#qz-form-title').text(t ? '编辑模板' : '新建模板');
    $('#f-type').val(t ? (t.type || 'personal') : 'personal');
    renderIdType();
    $('#f-zh_owner').val(t ? (t.zh_owner || '') : '');
    $('#f-zh_all_name').val(t ? (t.zh_all_name || '') : '');
    $('#f-en_owner').val(t ? (t.en_owner || '') : '');
    $('#f-en_last_name').val(t ? (t.en_last_name || '') : '');
    $('#f-en_first_name').val(t ? (t.en_first_name || '') : '');
    $('#f-email').val(t ? (t.email || '') : '');
    $('#f-phone').val(t ? (t.phone || '') : '');
    $('#f-idtype').val(t ? (t.idtype || 'SFZ') : 'SFZ');
    $('#f-idnum').val(t ? (t.idnum || '') : '');
    $('#f-zh_address').val(t ? (t.zh_address || '') : '');
    $('#f-en_address').val(t ? (t.en_address || '') : '');
    $('#f-postal_code').val(t ? (t.postal_code || '') : '');
    $('#f-country').val(t ? (t.country || 'CN') : 'CN');
    $('#f-img_front').val(''); $('#f-img_front-name').text('未选择');
    $('#f-img_back').val(''); $('#f-img_back-name').text('未选择');
    $('#qz-mask-form').addClass('show');
  }

  function buildFormData() {
    var fd = new FormData();
    if (editing && editing.id) fd.append('id', editing.id);
    fd.append('type', $('#f-type').val());
    fd.append('zh_owner', $('#f-zh_owner').val());
    fd.append('zh_all_name', $('#f-zh_all_name').val());
    fd.append('en_owner', $('#f-en_owner').val());
    fd.append('en_last_name', $('#f-en_last_name').val());
    fd.append('en_first_name', $('#f-en_first_name').val());
    fd.append('email', $('#f-email').val());
    fd.append('phone', $('#f-phone').val());
    fd.append('idtype', $('#f-idtype').val());
    fd.append('idnum', $('#f-idnum').val());
    fd.append('zh_address', $('#f-zh_address').val());
    fd.append('en_address', $('#f-en_address').val());
    fd.append('postal_code', $('#f-postal_code').val());
    fd.append('country', $('#f-country').val());
    if ($('#f-img_front')[0].files[0]) fd.append('img_front', $('#f-img_front')[0].files[0]);
    if ($('#f-img_back')[0].files[0]) fd.append('img_back', $('#f-img_back')[0].files[0]);
    return fd;
  }

  $('#f-type').on('change', renderIdType);

  $('#f-img_front').on('change', function () {
    $('#f-img_front-name').text(this.files[0] ? this.files[0].name : '未选择');
  });
  $('#f-img_back').on('change', function () {
    $('#f-img_back-name').text(this.files[0] ? this.files[0].name : '未选择');
  });

  $('#qz-btn-save').on('click', function () {
    var $btn = $(this);
    // 基础校验
    var required = ['zh_owner', 'en_owner', 'email', 'phone', 'zh_address', 'en_address', 'idnum'];
    var labels = { zh_owner: '所有者（中文）', en_owner: '所有者（英文）', email: '邮箱', phone: '手机', zh_address: '中文地址', en_address: '英文地址', idnum: '证件号码' };
    for (var i = 0; i < required.length; i++) {
      var v = $('#f-' + required[i]).val();
      if (!$.trim(v || '')) { QZ.toast('请填写' + labels[required[i]], 'error'); return; }
    }
    $btn.prop('disabled', true);
    QZ.postForm(QZ_API + '/template_save', buildFormData(), function (res) {
      QZ.toast(res.msg || '已保存', 'success');
      $('#qz-mask-form').removeClass('show');
      $btn.prop('disabled', false);
      loadList();
    }, function (msg) {
      QZ.toast(msg, 'error');
      $btn.prop('disabled', false);
    });
  });

  /* ---------- 实名认证 ---------- */
  function openCert(id) {
    $('#c-id').val(id);
    $('#c-img_front').val(''); $('#c-img_front-name').text('未选择');
    $('#c-img_back').val(''); $('#c-img_back-name').text('未选择');
    $('#qz-mask-cert').addClass('show');
  }

  $('#c-img_front').on('change', function () {
    $('#c-img_front-name').text(this.files[0] ? this.files[0].name : '未选择');
  });
  $('#c-img_back').on('change', function () {
    $('#c-img_back-name').text(this.files[0] ? this.files[0].name : '未选择');
  });

  $('#qz-btn-cert').on('click', function () {
    var id = $('#c-id').val();
    if (!id) { QZ.toast('参数错误', 'error'); return; }
    if (!$('#c-img_front')[0].files[0]) { QZ.toast('请选择证件正面照片', 'error'); return; }
    var fd = new FormData();
    fd.append('id', id);
    fd.append('img_front', $('#c-img_front')[0].files[0]);
    if ($('#c-img_back')[0].files[0]) fd.append('img_back', $('#c-img_back')[0].files[0]);
    var $btn = $(this).prop('disabled', true);
    QZ.postForm(QZ_API + '/template_certify', fd, function (res) {
      QZ.toast(res.msg || '已提交', 'success');
      $('#qz-mask-cert').removeClass('show');
      $btn.prop('disabled', false);
      loadList();
    }, function (msg) {
      QZ.toast(msg, 'error');
      $btn.prop('disabled', false);
    });
  });

  /* ---------- 删除 ---------- */
  function delTemplate(id) {
    if (!confirm('确定删除该模板？')) return;
    QZ.post(QZ_API + '/template_delete', { id: id }, function (res) {
      QZ.toast('已删除', 'success');
      loadList();
    }, function (msg) { QZ.toast(msg, 'error'); });
  }

  /* ---------- 通用 ---------- */
  $('#qz-btn-add').on('click', function () { openForm(null); });
  $('[data-close]').on('click', function () { $(this).closest('.qz-mask').removeClass('show'); });
  $('.qz-mask').on('click', function (e) { if (e.target === this) $(this).removeClass('show'); });

  loadList();
});
</script>
</body>
</html>
