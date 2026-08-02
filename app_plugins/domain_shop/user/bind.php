<?php
/**
 * domain_shop - 用户端域名绑定（自包含页面，不依赖主题外壳）
 *
 * 功能：
 *   - 查看已绑定域名列表（含端口/目录）
 *   - 添加自定义域名 / 本站二级域名（免费直接添加，付费弹窗购买）
 *   - 修改域名前缀、删除域名
 *
 * AJAX 端点（由 user/api/bind.php 注册，POST 到 ajax.php）：
 *   urllist / erurl / p_domain_tjurl / p_domain_seturl / p_domain_scurl
 * 购买下单：POST 到 /domain/buy 路由（mnbt_register_route）
 */
if (!defined('IN_CRONLITE')) exit;

global $yhc, $ssbt, $DB;
$cert = $DB->get_row_prepare("SELECT * FROM MN_bt WHERE btdh=? limit 1", [$ssbt]);
if (!is_array($cert)) $cert = [];

// 可购二级域名价格来自 erurl（前端拉取）；支付方式在后端渲染
$pay_methods = function_exists('mnbt_get_enabled_payment_methods') ? mnbt_get_enabled_payment_methods() : [];

// 站点根 index.php（购买表单 action，避免相对路径落到 /user/index.php）
$script = isset($_SERVER['SCRIPT_NAME']) ? str_replace('\\', '/', $_SERVER['SCRIPT_NAME']) : '';
$buyRoot = rtrim(dirname(dirname($script)), '/');
if ($buyRoot === '.' || $buyRoot === '' || $buyRoot === '/') $buyRoot = '';
$buyUrl = $buyRoot . '/index.php?_r=/domain/buy';

$hint = '';
if (!empty($cert)) {
    $hint = ($cert['als'] ?? 'false') === 'false'
        ? '请将域名 A 记录指向：' . htmlspecialchars((string)($cert['btip'] ?? ''), ENT_QUOTES, 'UTF-8')
        : htmlspecialchars((string)($cert['als'] ?? ''), ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>域名绑定 - 域名服务</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@mdi/font@7.4.47/css/materialdesignicons.min.css">
<style>
:root {
  --brand: #4f46e5;
  --brand-dark: #4338ca;
  --brand-soft: #eef2ff;
  --bg: #f4f6fb;
  --card: #fff;
  --border: #e6e8f0;
  --text: #1f2937;
  --text-2: #6b7280;
  --text-3: #9ca3af;
  --danger: #dc2626;
  --success: #16a34a;
  --radius: 12px;
}
* { box-sizing: border-box; }
body {
  margin: 0;
  background: var(--bg);
  color: var(--text);
  font: 14px/1.6 -apple-system, BlinkMacSystemFont, "Segoe UI", "PingFang SC", "Hiragino Sans GB", "Microsoft YaHei", sans-serif;
  -webkit-font-smoothing: antialiased;
  padding: 24px 16px 48px;
}
.ds-wrap { width: 100%; max-width: 920px; margin: 0 auto; }
.ds-head { margin-bottom: 20px; }
.ds-head h1 { margin: 0; font-size: 22px; }
.ds-head p { margin: 6px 0 0; color: var(--text-2); }

.ds-card {
  background: var(--card);
  border: 1px solid var(--border);
  border-radius: var(--radius);
  box-shadow: 0 1px 2px rgba(16,24,40,.04), 0 8px 24px -12px rgba(16,24,40,.12);
  margin-bottom: 20px;
}
.ds-card-head {
  padding: 14px 20px;
  border-bottom: 1px solid var(--border);
  font-weight: 600;
  font-size: 15px;
  display: flex; align-items: center; justify-content: space-between; gap: 10px; flex-wrap: wrap;
}
.ds-card-head small { color: var(--text-3); font-weight: 400; }
.ds-card-body { padding: 20px; }

/* 提示 */
.ds-alert {
  padding: 10px 14px;
  border-radius: 10px;
  font-size: 13px;
  background: var(--brand-soft);
  color: var(--brand);
  border: 1px solid #dbe1ff;
  margin-bottom: 16px;
}

/* 表格 */
.ds-table-wrap { overflow-x: auto; }
table.ds-table { width: 100%; border-collapse: collapse; }
.ds-table th, .ds-table td { padding: 11px 14px; text-align: left; white-space: nowrap; }
.ds-table thead th {
  background: #fafbfe;
  color: var(--text-2);
  font-size: 12px;
  font-weight: 600;
  border-bottom: 1px solid var(--border);
}
.ds-table tbody td { border-bottom: 1px solid #f1f2f7; font-size: 13.5px; }
.ds-table tbody tr:hover { background: #fafbff; }
.ds-table tbody tr:last-child td { border-bottom: none; }
.ds-empty { text-align: center; padding: 40px 16px; color: var(--text-3); }
.ds-mono { font-family: ui-monospace, SFMono-Regular, Consolas, monospace; font-size: 12.5px; }
.ds-domain a { color: var(--brand); font-weight: 600; }

/* 按钮 */
.ds-btn {
  display: inline-flex; align-items: center; justify-content: center; gap: 6px;
  padding: 9px 18px; border: 1px solid transparent; border-radius: 10px;
  font-size: 14px; font-weight: 600; cursor: pointer; transition: all .15s;
  background: var(--brand-soft); color: var(--brand); text-decoration: none;
}
.ds-btn:hover { background: #e0e7ff; }
.ds-btn-primary { background: var(--brand); color: #fff; box-shadow: 0 4px 12px -3px rgba(79,70,229,.55); }
.ds-btn-primary:hover { background: var(--brand-dark); color: #fff; }
.ds-btn-ghost { background: #fff; border-color: var(--border); color: var(--text-2); }
.ds-btn-ghost:hover { border-color: var(--brand); color: var(--brand); }
.ds-btn-sm { padding: 5px 12px; font-size: 12.5px; border-radius: 8px; }
.ds-btn-block { width: 100%; }
.ds-btn:disabled { opacity: .6; cursor: not-allowed; }

/* 表单 */
.ds-field { margin-bottom: 14px; }
.ds-field label { display: block; margin-bottom: 6px; font-size: 13px; color: var(--text-2); font-weight: 500; }
.ds-field input[type="text"],
.ds-field input[type="password"],
.ds-field select {
  width: 100%; padding: 10px 14px; border: 1px solid var(--border); border-radius: 10px;
  font-size: 14px; color: var(--text); background: #fafbfe; outline: none; transition: all .15s;
}
.ds-field input:focus, .ds-field select:focus { border-color: var(--brand); background: #fff; box-shadow: 0 0 0 3px rgba(79,70,229,.12); }
.ds-hint { font-size: 12.5px; color: var(--text-3); margin-top: 6px; }
.ds-price { color: var(--brand); font-weight: 700; }

/* 分段选择 */
.ds-tabs { display: flex; gap: 8px; margin-bottom: 16px; }
.ds-tab {
  padding: 8px 16px; border: 1px solid var(--border); border-radius: 9px;
  background: #fff; color: var(--text-2); font-size: 13px; font-weight: 500; cursor: pointer; transition: all .15s;
}
.ds-tab:hover { border-color: var(--brand); color: var(--brand); }
.ds-tab.active { border-color: var(--brand); background: var(--brand-soft); color: var(--brand); font-weight: 600; }

/* 弹窗 */
.ds-mask {
  position: fixed; inset: 0; z-index: 50;
  background: rgba(15,23,42,.45);
  display: none; align-items: center; justify-content: center; padding: 16px;
}
.ds-mask.show { display: flex; }
.ds-modal {
  width: 100%; max-width: 460px; max-height: 90vh; overflow-y: auto;
  background: #fff; border-radius: 14px; box-shadow: 0 24px 60px -20px rgba(15,23,42,.4);
}
.ds-modal-head {
  padding: 16px 20px; border-bottom: 1px solid var(--border);
  font-weight: 600; font-size: 16px;
  display: flex; align-items: center; justify-content: space-between;
}
.ds-modal-body { padding: 20px; }
.ds-close { border: none; background: none; font-size: 22px; line-height: 1; cursor: pointer; color: var(--text-3); padding: 0 4px; }
.ds-close:hover { color: var(--text); }
.ds-buy-domain { margin: 0 0 6px; font-size: 18px; font-weight: 700; }
.ds-buy-price { margin: 0 0 12px; color: var(--brand); font-weight: 700; font-size: 16px; }

/* 支付方式 */
.ds-choices { display: flex; flex-wrap: wrap; gap: 8px; }
.ds-choice {
  display: inline-flex; align-items: center; gap: 6px;
  padding: 8px 14px; border: 1px solid var(--border); border-radius: 9px; background: #fff; cursor: pointer; font-size: 13.5px;
}
.ds-choice input { accent-color: var(--brand); }
.ds-choice:has(input:checked), .ds-choice.active { border-color: var(--brand); background: var(--brand-soft); color: var(--brand); font-weight: 600; }

/* 轻提示 */
.ds-toast {
  position: fixed; left: 50%; bottom: 32px; transform: translateX(-50%);
  z-index: 100; padding: 10px 18px; border-radius: 10px; font-size: 13.5px; color: #fff;
  background: #1f2937; box-shadow: 0 8px 24px rgba(0,0,0,.2); opacity: 0; pointer-events: none; transition: opacity .2s;
}
.ds-toast.show { opacity: 1; }
.ds-toast.ok { background: var(--success); }
.ds-toast.err { background: var(--danger); }
</style>
</head>
<body>
<div class="ds-wrap">

  <div class="ds-head">
    <h1>域名绑定</h1>
    <p>管理绑定到您主机的域名；如需购买本站二级域名，请选择「本站二级域名」模式。</p>
  </div>

  <?php if ($hint !== ''): ?>
    <div class="ds-alert"><?= $hint ?></div>
  <?php endif; ?>

  <!-- 已绑定域名 -->
  <div class="ds-card">
    <div class="ds-card-head">
      <span>已绑定域名</span>
      <small>修改前缀 / 删除后立即生效</small>
    </div>
    <div class="ds-card-body ds-table-wrap">
      <table class="ds-table">
        <thead><tr><th>域名</th><th>端口</th><th>目录</th><th>操作</th></tr></thead>
        <tbody id="ds-list"></tbody>
      </table>
      <div id="ds-empty" class="ds-empty">暂无绑定域名</div>
    </div>
  </div>

  <!-- 添加域名 -->
  <div class="ds-card">
    <div class="ds-card-head">
      <span>添加域名</span>
      <small id="ds-add-desc">请输入要绑定的完整域名</small>
    </div>
    <div class="ds-card-body">
      <div class="ds-tabs">
        <button type="button" class="ds-tab active" data-mode="custom" id="ds-tab-custom">自定义域名</button>
        <button type="button" class="ds-tab" data-mode="sub" id="ds-tab-sub">本站二级域名</button>
      </div>

      <form id="ds-add-form">
        <div class="ds-field">
          <label for="ds-add-input" id="ds-add-label">域名</label>
          <input type="text" id="ds-add-input" autocomplete="off" placeholder="例如 www.example.com">
        </div>

        <div class="ds-field" id="ds-sub-wrap" style="display:none">
          <label for="ds-sub-domain">选择二级域名</label>
          <select id="ds-sub-domain"></select>
          <div class="ds-hint" id="ds-sub-price"></div>
        </div>

        <div class="ds-field">
          <label for="ds-dir">子目录</label>
          <select id="ds-dir"></select>
          <div class="ds-hint">如果无特殊需求则推荐默认；会自动显示主机目录</div>
        </div>

        <button type="submit" class="ds-btn ds-btn-primary" id="ds-add-btn">添加</button>
      </form>
    </div>
  </div>
</div>

<!-- 修改前缀弹窗 -->
<div class="ds-mask" id="ds-edit-mask">
  <div class="ds-modal">
    <div class="ds-modal-head">修改域名前缀 <button type="button" class="ds-close" data-close="ds-edit-mask">&times;</button></div>
    <div class="ds-modal-body">
      <div class="ds-field">
        <label>新前缀（数字和字母，1~24 位）</label>
        <input type="text" id="ds-edit-prefix" maxlength="24" autocomplete="off">
      </div>
      <div class="ds-field">
        <label>子目录</label>
        <select id="ds-edit-dir"></select>
      </div>
      <button type="button" class="ds-btn ds-btn-primary" id="ds-edit-confirm">确定修改</button>
    </div>
  </div>
</div>

<!-- 购买弹窗 -->
<div class="ds-mask" id="ds-buy-mask">
  <div class="ds-modal">
    <div class="ds-modal-head">购置二级域名 <button type="button" class="ds-close" data-close="ds-buy-mask">&times;</button></div>
    <div class="ds-modal-body">
      <p class="ds-buy-domain" id="ds-buy-domain"></p>
      <p class="ds-buy-price" id="ds-buy-price"></p>
      <p class="ds-hint">支付完成后将自动添加该域名；若前缀已被占用，系统会随机化一个新前缀，您可以随时再次修改。</p>

      <form id="ds-buy-form" method="post" action="<?= htmlspecialchars($buyUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank">
        <input type="hidden" name="urla" id="ds-buy-urla">
        <input type="hidden" name="urlb" id="ds-buy-urlb">
        <input type="hidden" name="urlzml" id="ds-buy-urlzml" value="/">
        <input type="hidden" name="pay_lx" value="ymgm">
        <div class="ds-field">
          <label>选择支付方式</label>
          <?php if (!empty($pay_methods)): ?>
            <div class="ds-choices">
              <?php foreach ($pay_methods as $i => $m): ?>
                <?php $type = $m['plugin'] . '__' . $m['method']; ?>
                <label class="ds-choice">
                  <input type="radio" name="type" value="<?= htmlspecialchars($type, ENT_QUOTES, 'UTF-8') ?>"<?= $i === 0 ? ' checked' : '' ?>>
                  <i class="mdi <?= htmlspecialchars($m['icon'] ?? 'mdi-payment', ENT_QUOTES, 'UTF-8') ?>"></i>
                  <?= htmlspecialchars($m['display_name'] ?? ($m['plugin'] . ' / ' . $m['method']), ENT_QUOTES, 'UTF-8') ?>
                </label>
              <?php endforeach; ?>
            </div>
          <?php else: ?>
            <p class="ds-hint">暂无可用的支付方式，请联系管理员。</p>
          <?php endif; ?>
        </div>
        <button type="submit" class="ds-btn ds-btn-primary ds-btn-block"<?= empty($pay_methods) ? ' disabled' : '' ?>>确认支付</button>
      </form>
    </div>
  </div>
</div>

<div class="ds-toast" id="ds-toast"></div>

<script>
(function () {
  'use strict';

  var listEl = document.getElementById('ds-list');
  var emptyEl = document.getElementById('ds-empty');
  var dirSelect = document.getElementById('ds-dir');
  var addForm = document.getElementById('ds-add-form');
  var addInput = document.getElementById('ds-add-input');
  var addLabel = document.getElementById('ds-add-label');
  var addDesc = document.getElementById('ds-add-desc');
  var addBtn = document.getElementById('ds-add-btn');
  var subWrap = document.getElementById('ds-sub-wrap');
  var subDomainSelect = document.getElementById('ds-sub-domain');
  var subPrice = document.getElementById('ds-sub-price');
  var tabCustom = document.getElementById('ds-tab-custom');
  var tabSub = document.getElementById('ds-tab-sub');

  var dirs = ['/'];
  var subDomains = [];      // erurl: [{url, jg, jj}]
  var selectedSub = null;   // 当前选中的二级域名
  var currentMode = 'custom';
  var editing = null;       // 正在修改的域名信息 {name, port, path, zym, prefix}

  function esc(s) {
    return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
    });
  }

  var toastTimer = null;
  function toast(msg, type) {
    var el = document.getElementById('ds-toast');
    el.textContent = msg;
    el.className = 'ds-toast show' + (type === 'ok' ? ' ok' : type === 'err' ? ' err' : '');
    clearTimeout(toastTimer);
    toastTimer = setTimeout(function () { el.className = 'ds-toast'; }, 2600);
  }

  function post(data) {
    var body = new URLSearchParams();
    Object.keys(data).forEach(function (k) { body.append(k, data[k]); });
    return fetch('ajax.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: body.toString()
    }).then(function (r) { return r.json().catch(function () { return { code: '响应异常' }; }); });
  }

  function parseCode(res) {
    if (res && typeof res === 'object' && typeof res.code === 'string') return res.code;
    return '';
  }

  /* ---------- 列表 ---------- */
  function loadList() {
    return post({ gn: 'urllist' }).then(function (res) {
      if (!res || typeof res !== 'object' || !Array.isArray(res.url)) {
        renderList([]);
        return;
      }
      dirs = Array.isArray(res.dir) ? res.dir : ['/'];
      if (dirs.indexOf('/') === -1) dirs.unshift('/');
      fillDirs(dirSelect, dirs);
      renderList(res.url);
    }).catch(function () { renderList([]); });
  }

  function renderList(urls) {
    emptyEl.style.display = urls.length ? 'none' : 'block';
    var html = '';
    urls.forEach(function (u) {
      html += '<tr>' +
        '<td class="ds-domain"><a target="_blank" rel="noopener" href="http://' + esc(u.name) + ':' + esc(u.port) + '/">' + esc(u.name) + '</a></td>' +
        '<td>' + esc(u.port) + '</td>' +
        '<td>' + esc(u.path) + '</td>' +
        '<td>' +
          '<button type="button" class="ds-btn ds-btn-ghost ds-btn-sm" data-edit-name="' + esc(u.name) + '" data-edit-port="' + esc(u.port) + '" data-edit-path="' + esc(u.path) + '">修改前缀</button> ' +
          '<button type="button" class="ds-btn ds-btn-ghost ds-btn-sm" data-del-name="' + esc(u.name) + '" data-del-port="' + esc(u.port) + '" data-del-path="' + esc(u.path) + '">删除</button>' +
        '</td>' +
      '</tr>';
    });
    listEl.innerHTML = html;
  }

  function fillDirs(select, list) {
    var html = '';
    list.forEach(function (d) {
      html += '<option value="' + esc(d) + '">' + esc(d) + '</option>';
    });
    select.innerHTML = html || '<option value="/">/</option>';
  }

  /* ---------- 可购二级域名 ---------- */
  function loadSubDomains() {
    return post({ gn: 'erurl' }).then(function (res) {
      subDomains = Array.isArray(res) ? res : [];
      renderSubSelect();
    }).catch(function () { subDomains = []; renderSubSelect(); });
  }

  function renderSubSelect() {
    if (!subDomains.length) {
      subDomainSelect.innerHTML = '<option value="">暂无可购二级域名</option>';
      subPrice.textContent = '';
      return;
    }
    var html = '<option value="">请选择</option>';
    subDomains.forEach(function (s, i) {
      html += '<option value="' + esc(s.url) + '" data-price="' + esc(s.jg) + '" data-desc="' + esc(s.jj) + '">' + esc(s.url) + '</option>';
    });
    subDomainSelect.innerHTML = html;
    onSubChange();
  }

  function onSubChange() {
    var opt = subDomainSelect.options[subDomainSelect.selectedIndex];
    if (!opt || !opt.value) { selectedSub = null; subPrice.textContent = ''; return; }
    var price = parseInt(opt.getAttribute('data-price') || '0', 10);
    var desc = opt.getAttribute('data-desc') || '';
    selectedSub = { url: opt.value, jg: price, jj: desc };
    subPrice.innerHTML = '价格：<span class="ds-price">' + (price > 0 ? '¥' + price : '免费') + '</span>' + (desc ? '　' + esc(desc) : '');
  }

  /* ---------- 模式切换 ---------- */
  function setMode(mode) {
    currentMode = mode;
    tabCustom.classList.toggle('active', mode === 'custom');
    tabSub.classList.toggle('active', mode === 'sub');
    var isSub = mode === 'sub';
    subWrap.style.display = isSub ? '' : 'none';
    addLabel.textContent = isSub ? '前缀' : '域名';
    addInput.placeholder = isSub ? '例如：www（数字和字母，1~24 位）' : '例如：www.example.com';
    addDesc.textContent = isSub ? '选择本站提供的二级域名，付费域名将弹窗购买' : '请输入要绑定的完整域名';
    addInput.value = '';
  }

  /* ---------- 添加 ---------- */
  function addDomain(fullUrl) {
    if (!fullUrl) { toast('请填写域名', 'err'); return; }
    var dir = dirSelect.value || '/';
    addBtn.disabled = true;
    post({ gn: 'p_domain_tjurl', url: fullUrl, dirs: dir }).then(function (res) {
      var code = parseCode(res);
      if (code === '添加成功') { toast('添加成功', 'ok'); addInput.value = ''; loadList(); }
      else toast(code || '添加失败', 'err');
    }).catch(function () { toast('网络错误，请重试', 'err'); })
      .then(function () { addBtn.disabled = false; });
  }

  addForm.addEventListener('submit', function (e) {
    e.preventDefault();
    if (currentMode === 'sub') {
      if (!selectedSub) { toast('请选择二级域名', 'err'); return; }
      var prefix = addInput.value.trim();
      if (!/^[0-9a-zA-Z]{1,24}$/.test(prefix)) { toast('前缀只能为数字和字母（1~24 位）', 'err'); return; }
      if (selectedSub.jg > 0) {
        openBuy(selectedSub, prefix);
        return;
      }
      addDomain(prefix + '.' + selectedSub.url);
    } else {
      addDomain(addInput.value.trim());
    }
  });

  /* ---------- 删除 ---------- */
  listEl.addEventListener('click', function (e) {
    var del = e.target.closest('[data-del-name]');
    if (del) {
      var name = del.getAttribute('data-del-name');
      if (!window.confirm('确定删除域名 ' + name + ' 吗？')) return;
      post({
        gn: 'p_domain_scurl',
        url: name,
        port: del.getAttribute('data-del-port'),
        dir: del.getAttribute('data-del-path')
      }).then(function (res) {
        var code = parseCode(res);
        if (code === '删除成功') { toast('删除成功', 'ok'); loadList(); }
        else toast(code || '删除失败', 'err');
      }).catch(function () { toast('网络错误，请重试', 'err'); });
      return;
    }

    var edit = e.target.closest('[data-edit-name]');
    if (edit) {
      var name = edit.getAttribute('data-edit-name');
      var dot = name.indexOf('.');
      var prefix = dot > 0 ? name.substring(0, dot) : '';
      var zym = dot > 0 ? name.substring(dot + 1) : name;
      editing = {
        name: name,
        port: edit.getAttribute('data-edit-port'),
        path: edit.getAttribute('data-edit-path'),
        zym: zym,
        prefix: prefix
      };
      openEdit(editing);
    }
  });

  /* ---------- 修改前缀 ---------- */
  function openEdit(item) {
    document.getElementById('ds-edit-prefix').value = item.prefix;
    fillDirs(document.getElementById('ds-edit-dir'), dirs);
    document.getElementById('ds-edit-dir').value = item.path;
    showMask('ds-edit-mask');
    document.getElementById('ds-edit-prefix').focus();
  }

  document.getElementById('ds-edit-confirm').addEventListener('click', function () {
    if (!editing) return;
    var xqz = document.getElementById('ds-edit-prefix').value.trim();
    if (!/^[0-9a-zA-Z]{1,24}$/.test(xqz)) { toast('前缀只能为数字和字母（1~24 位）', 'err'); return; }
    var btn = this;
    btn.disabled = true;
    post({
      gn: 'p_domain_seturl',
      zym: editing.zym,
      port: editing.port,
      jqz: editing.prefix,
      xqz: xqz,
      path: document.getElementById('ds-edit-dir').value
    }).then(function (res) {
      var code = parseCode(res);
      if (code === '添加成功') { toast('修改成功', 'ok'); hideMask('ds-edit-mask'); loadList(); }
      else toast(code || '修改失败', 'err');
    }).catch(function () { toast('网络错误，请重试', 'err'); })
      .then(function () { btn.disabled = false; });
  });

  /* ---------- 购买 ---------- */
  function openBuy(sub, prefix) {
    document.getElementById('ds-buy-domain').textContent = prefix + '.' + sub.url;
    document.getElementById('ds-buy-price').textContent = '价格：¥' + sub.jg;
    document.getElementById('ds-buy-urla').value = sub.url;
    document.getElementById('ds-buy-urlb').value = prefix;
    document.getElementById('ds-buy-urlzml').value = dirSelect.value || '/';
    showMask('ds-buy-mask');
  }

  document.getElementById('ds-buy-form').addEventListener('submit', function () {
    hideMask('ds-buy-mask');
    toast('已在新窗口打开支付，请完成付款', 'ok');
  });
  document.getElementById('ds-buy-form').addEventListener('change', function () {
    var form = this;
    form.querySelectorAll('.ds-choice').forEach(function (l) { l.classList.remove('active'); });
    var checked = form.querySelector('input[name="type"]:checked');
    if (checked) {
      var p = checked.closest('.ds-choice');
      if (p) p.classList.add('active');
    }
  });

  /* ---------- 弹窗 ---------- */
  function showMask(id) { document.getElementById(id).classList.add('show'); }
  function hideMask(id) { document.getElementById(id).classList.remove('show'); }
  document.querySelectorAll('[data-close]').forEach(function (btn) {
    btn.addEventListener('click', function () { hideMask(btn.getAttribute('data-close')); });
  });
  document.querySelectorAll('.ds-mask').forEach(function (mask) {
    mask.addEventListener('click', function (e) { if (e.target === mask) mask.classList.remove('show'); });
  });
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') document.querySelectorAll('.ds-mask.show').forEach(function (m) { m.classList.remove('show'); });
  });

  /* ---------- 初始化 ---------- */
  tabCustom.addEventListener('click', function () { setMode('custom'); });
  tabSub.addEventListener('click', function () { setMode('sub'); });
  subDomainSelect.addEventListener('change', onSubChange);
  setMode('custom');
  loadList();
  loadSubDomains();
})();
</script>
</body>
</html>
