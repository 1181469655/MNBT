<?php
/**
 * 管理员端 - 主机指派
 *
 * 拉取当前上游账户已开通的主机列表（GET host/list），
 * 勾选具体某台机器并指定目标用户后「批量指派」：
 * 在本地为该用户绑定该上游主机（不调上游购买/开通），
 * 主机随即出现在该用户的「我的主机」中。
 */
if (!defined('IN_CRONLITE')) {
	exit;
}
mnbt_admin_include('head');

$suppliers = zjmf_supplier_list_all();
?>
<div class="container-fluid p-t-15">
  <div class="card">
    <div class="card-header">
      <h4 style="display:inline-block">主机指派（上游已开通机器）</h4>
    </div>
    <div class="card-body">
      <p class="text-muted">
        选择供应商并加载其已开通的主机列表，勾选要指派的具体机器并指定目标用户，
        点击「批量指派所选主机」。指派为本地绑定操作，不会在上游重复开通；
        被指派的主机将出现在该用户的「我的主机」中。
      </p>

      <!-- 供应商 / 用户 -->
      <div class="form-row">
        <div class="col-md-3 mb-2">
          <label class="small text-muted">供应商 *</label>
          <select id="zjf-assign-supplier" class="form-control form-control-sm">
            <?php if (empty($suppliers)): ?>
              <option value="">请先在供应商管理中新增供应商</option>
            <?php else: ?>
              <?php foreach ($suppliers as $s): ?>
                <option value="<?= (int)$s['id'] ?>"><?=
                  htmlspecialchars($s['name'], ENT_QUOTES)
                ?><?= (int)$s['status'] !== 1 ? '（已停用）' : '' ?></option>
              <?php endforeach; ?>
            <?php endif; ?>
          </select>
        </div>
        <div class="col-md-4 mb-2">
          <label class="small text-muted">目标用户 *（输入用户名/邮箱/ID 搜索）</label>
          <div class="input-group input-group-sm">
            <input type="text" id="zjf-assign-user-kw" class="form-control"
                   placeholder="用户名 / 邮箱 / ID" maxlength="64">
            <div class="input-group-append">
              <button type="button" class="btn btn-outline-primary" id="zjf-assign-user-search">搜索</button>
            </div>
          </div>
          <div id="zjf-assign-user-list" class="mt-1"></div>
          <input type="hidden" id="zjf-assign-user-id" value="0">
        </div>
        <div class="col-md-3 mb-2">
          <label class="small text-muted">&nbsp;</label>
          <div>
            <button type="button" class="btn btn-primary btn-sm" id="zjf-assign-load">加载已开通的主机</button>
          </div>
        </div>
      </div>

      <div id="zjf-assign-tip" class="small text-muted mb-2"></div>

      <!-- 上游已开通主机列表 -->
      <div class="table-responsive">
        <table class="table table-bordered table-sm">
          <thead>
            <tr>
              <th style="width:40px;">
                <input type="checkbox" id="zjf-assign-all"> 全选
              </th>
              <th>上游主机ID</th>
              <th>域名</th>
              <th>产品名</th>
              <th>状态</th>
              <th>IP</th>
              <th>周期</th>
              <th>到期日</th>
            </tr>
          </thead>
          <tbody id="zjf-assign-list">
            <tr><td colspan="8" class="text-center text-muted">
              请先选择供应商并加载主机列表
            </td></tr>
          </tbody>
        </table>
      </div>

      <button type="button" class="btn btn-primary" id="zjf-assign-do">批量指派所选主机</button>
      <button type="button" class="btn btn-secondary" id="zjf-assign-reset">重置列表</button>

      <!-- 指派结果 -->
      <div id="zjf-assign-result" class="mt-3" style="display:none;"></div>
    </div>
  </div>
</div>
<script>
(function () {
  function res(res) {
    var d;
    try { d = typeof res === 'string' ? JSON.parse(res) : res; } catch (e) { d = {code: res}; }
    return d;
  }
  function notify(d) {
    var ok = d.qk == 1 || d.success;
    if (typeof $.notify === 'function') {
      $.notify({message: d.msg || d.code || '完成'}, {type: ok ? 'success' : 'danger'});
    } else {
      alert(d.msg || d.code || '完成');
    }
  }
  function esc(s) {
    return String(s == null ? '' : s)
      .replace(/&/g, '&amp;').replace(/</g, '&lt;')
      .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
  }

  var listBox = document.getElementById('zjf-assign-list');
  var tip = document.getElementById('zjf-assign-tip');
  var userId = document.getElementById('zjf-assign-user-id');
  var userList = document.getElementById('zjf-assign-user-list');

  /* ---------------- 用户搜索 ---------------- */
  document.getElementById('zjf-assign-user-search').addEventListener('click', function () {
    var kw = document.getElementById('zjf-assign-user-kw').value.trim();
    if (!kw) { alert('请输入用户名/邮箱/ID'); return; }
    $.post('ajax.php', {gn: 'p_zjmf_admin_search_users', keyword: kw}, function (r) {
      var d = res(r);
      if (d.qk != 1 && !d.success) {
        userList.innerHTML = '<div class="text-danger small">' + esc(d.msg || '搜索失败') + '</div>';
        return;
      }
      var list = d.list || (d.data && d.data.list) || [];
      if (!list.length) {
        userList.innerHTML = '<div class="text-muted small">未找到匹配用户</div>';
        return;
      }
      var html = '';
      for (var i = 0; i < list.length; i++) {
        var u = list[i];
        html += '<label class="d-block border rounded px-2 py-1 mb-1 small">'
          + '<input type="radio" name="zjf-assign-user" class="mr-1 zjf-assign-user-radio"'
          + ' value="' + u.id + '" data-name="' + esc(u.username) + '">'
          + '<b>' + esc(u.username) + '</b>'
          + (u.email ? ' <span class="text-muted">' + esc(u.email) + '</span>' : '')
          + (u.qq ? ' <span class="text-muted">QQ:' + esc(u.qq) + '</span>' : '')
          + ' <span class="text-muted">ID:' + u.id + '</span>'
          + '</label>';
      }
      userList.innerHTML = html;
    });
  });

  userList.addEventListener('change', function (e) {
    if (e.target.classList.contains('zjf-assign-user-radio')) {
      userId.value = e.target.value;
      tip.textContent = '已选择用户：' + e.target.getAttribute('data-name');
    }
  });

  /* ---------------- 加载上游已开通主机 ---------------- */
  document.getElementById('zjf-assign-load').addEventListener('click', function () {
    var supplierId = document.getElementById('zjf-assign-supplier').value;
    if (!supplierId) { alert('请先选择供应商'); return; }
    var btn = this;
    btn.disabled = true;
    btn.textContent = '加载中...';
    tip.textContent = '正在拉取该账户已开通的主机列表...';
    listBox.innerHTML = '<tr><td colspan="8" class="text-center text-muted">加载中...</td></tr>';
    $.post('ajax.php', {gn: 'p_zjmf_admin_upstream_owned_products', id: supplierId},
      function (r) {
        var d = res(r);
        btn.disabled = false;
        btn.textContent = '加载已开通的主机';
        if (d.qk != 1 && !d.success) {
          tip.textContent = '';
          listBox.innerHTML = '<tr><td colspan="8" class="text-center text-muted">'
            + esc(d.msg || '拉取失败') + '</td></tr>';
          return;
        }
        var list = d.list || (d.data && d.data.list) || [];
        if (!list.length) {
          listBox.innerHTML = '<tr><td colspan="8" class="text-center text-muted">'
            + '该账户暂无已开通的主机</td></tr>';
          return;
        }
        var assignedCount = 0;
        for (var i = 0; i < list.length; i++) {
          if (list[i].assigned) assignedCount++;
        }
        tip.textContent = '共 ' + list.length + ' 台主机（已指派 ' + assignedCount
          + ' 台，灰显不可重复指派），勾选要指派的主机并指定目标用户后点击「批量指派所选主机」。';
        var html = '';
        for (var i = 0; i < list.length; i++) {
          var it = list[i];
          var disabled = it.assigned ? ' disabled' : '';
          html += '<tr' + (it.assigned ? ' class="text-muted"' : '') + '>'
            + '<td><input type="checkbox" class="zjf-assign-check" value="' + it.id + '"' + disabled + '></td>'
            + '<td>' + it.id + '</td>'
            + '<td>' + esc(it.domain || '-') + '</td>'
            + '<td>' + esc(it.productname || '-') + '</td>'
            + '<td>' + esc(it.status_desc || it.status || '-') + '</td>'
            + '<td>' + esc(it.dedicatedip || '-') + '</td>'
            + '<td>' + esc(it.cycle || '-') + '</td>'
            + '<td>' + esc(it.nextdue || '-')
            + (it.assigned ? ' <span class="badge badge-secondary">已指派</span>' : '')
            + '</td>'
            + '</tr>';
        }
        listBox.innerHTML = html;
      });
  });

  document.getElementById('zjf-assign-all').addEventListener('change', function () {
    var checked = this.checked;
    document.querySelectorAll('.zjf-assign-check').forEach(function (c) {
      if (!c.disabled) c.checked = checked;
    });
  });

  /* ---------------- 批量指派 ---------------- */
  document.getElementById('zjf-assign-do').addEventListener('click', function () {
    var supplierId = document.getElementById('zjf-assign-supplier').value;
    var uid = parseInt(userId.value, 10);
    if (!supplierId) { alert('请先选择供应商'); return; }
    if (!uid) { alert('请先搜索并选择目标用户'); return; }
    var ids = [];
    document.querySelectorAll('.zjf-assign-check:checked').forEach(function (c) {
      ids.push(parseInt(c.value, 10));
    });
    if (!ids.length) { alert('请至少勾选一台主机'); return; }
    if (!confirm('确认为所选用户指派 ' + ids.length + ' 台主机？'
        + '\n此操作将本地绑定上游已开通的机器，不会在上游重复开通。')) return;

    var btn = this;
    btn.disabled = true;
    btn.textContent = '指派中...';
    var resultBox = document.getElementById('zjf-assign-result');
    resultBox.style.display = 'block';
    resultBox.innerHTML = '<div class="text-muted small">正在逐个绑定，请稍候...</div>';
    $.post('ajax.php', {
      gn: 'p_zjmf_admin_assign_host',
      supplier_id: supplierId,
      user_id: uid,
      up_host_id: ids
    }, function (r) {
      var d = res(r);
      btn.disabled = false;
      btn.textContent = '批量指派所选主机';
      if (d.qk != 1 && !d.success) {
        resultBox.innerHTML = '<div class="alert alert-danger py-2 small mb-0">'
          + esc(d.msg || '操作失败') + '</div>';
        return;
      }
      var rows = d.data && d.data.results ? d.data.results : [];
      var html = '<div class="card"><div class="card-header py-2"><b>指派结果</b></div>'
        + '<div class="card-body p-0"><table class="table table-bordered table-sm mb-0">'
        + '<thead><tr><th>机器</th><th>状态</th><th>本地主机ID</th><th>说明</th></tr></thead><tbody>';
      var okCount = 0;
      for (var i = 0; i < rows.length; i++) {
        var it = rows[i];
        if (it.ok) okCount++;
        html += '<tr>'
          + '<td>' + esc(it.domain || ('上游#' + it.up_host_id)) + '</td>'
          + '<td>' + (it.ok ? '<span class="badge badge-success">指派成功</span>'
              : '<span class="badge badge-danger">失败</span>') + '</td>'
          + '<td>' + (it.host_id ? it.host_id : '-') + '</td>'
          + '<td class="small">' + esc(it.msg || '-') + '</td>'
          + '</tr>';
      }
      html += '</tbody></table></div></div>';
      if (okCount === rows.length) {
        html += '<div class="alert alert-success py-2 small mb-0 mt-2">全部指派成功</div>';
      } else {
        html += '<div class="alert alert-warning py-2 small mb-0 mt-2">成功 ' + okCount + ' / '
          + rows.length + '，失败的机器可能已指派给其他用户。</div>';
      }
      resultBox.innerHTML = html;
    });
  });

  document.getElementById('zjf-assign-reset').addEventListener('click', function () {
    listBox.innerHTML = '<tr><td colspan="8" class="text-center text-muted">'
      + '请先选择供应商并加载主机列表</td></tr>';
    tip.textContent = '';
    userId.value = 0;
    userList.innerHTML = '';
    document.getElementById('zjf-assign-user-kw').value = '';
    document.getElementById('zjf-assign-all').checked = false;
  });
})();
</script>
