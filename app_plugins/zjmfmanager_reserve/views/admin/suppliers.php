<?php
/**
 * 管理员端 - 供应商管理
 *
 * 维护多个魔方财务上游供应商（独立 API 账号 / 加价规则 / 启用状态）。
 * 停用供应商后其商品不可售，主机操作与升级被拒绝。
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
      <h4 style="display:inline-block">供应商管理</h4>
      <button type="button" class="btn btn-primary btn-sm float-right"
              id="zjf-supplier-add">新增供应商</button>
    </div>
    <div class="card-body">
      <p class="text-muted">
        可添加多个魔方财务上游站点，各自独立的 API 账号与加价规则；
        停用后其商品自动下架不可售，主机操作与升级将被拒绝。
      </p>

      <div id="zjf-edit-wrap" class="mb-3 border rounded p-3" style="display:none;">
        <h6 class="mb-2" id="zjf-edit-title">编辑供应商</h6>
        <input type="hidden" id="zjf-edit-id" value="0">
        <div class="form-row">
          <div class="col-md-3 mb-2">
            <label class="small text-muted">名称 *</label>
            <input type="text" id="zjf-edit-name" class="form-control form-control-sm"
                   placeholder="如：魔方A站">
          </div>
          <div class="col-md-3 mb-2">
            <label class="small text-muted">站点 URL *</label>
            <input type="text" id="zjf-edit-url" class="form-control form-control-sm"
                   placeholder="https://upstream.example.com">
          </div>
          <div class="col-md-3 mb-2">
            <label class="small text-muted">API 用户名 *</label>
            <input type="text" id="zjf-edit-username" class="form-control form-control-sm">
          </div>
          <div class="col-md-3 mb-2">
            <label class="small text-muted">API 密钥</label>
            <input type="password" id="zjf-edit-password" class="form-control form-control-sm"
                   placeholder="编辑时留空不修改">
          </div>
        </div>
        <div class="form-row">
          <div class="col-md-2 mb-2">
            <label class="small text-muted">超时（秒）</label>
            <input type="number" id="zjf-edit-timeout" class="form-control form-control-sm"
                   min="5" max="120" value="30">
          </div>
          <div class="col-md-2 mb-2">
            <label class="small text-muted">加价方式</label>
            <select id="zjf-edit-mtype" class="form-control form-control-sm">
              <option value="0">按比例</option>
              <option value="1">固定加价（分）</option>
            </select>
          </div>
          <div class="col-md-2 mb-2">
            <label class="small text-muted">加价数值</label>
            <input type="number" id="zjf-edit-mvalue" class="form-control form-control-sm"
                   min="0" value="0">
          </div>
          <div class="col-md-2 mb-2">
            <label class="small text-muted">状态</label>
            <select id="zjf-edit-status" class="form-control form-control-sm">
              <option value="1">启用</option>
              <option value="0">停用</option>
            </select>
          </div>
          <div class="col-md-2 mb-2">
            <label class="small text-muted">排序</label>
            <input type="number" id="zjf-edit-sort" class="form-control form-control-sm"
                   min="0" value="0">
          </div>
        </div>
        <div class="form-row">
          <div class="col-md-6 mb-2">
            <label class="small text-muted">备注</label>
            <input type="text" id="zjf-edit-remark" class="form-control form-control-sm">
          </div>
        </div>
        <div class="small text-muted mb-2">
          加价数值：按比例填千分比（如 10 表示 +1%）；固定加价填金额（分，1 元=100）。
          商品未单独配置加价时使用此处规则。
        </div>
        <button type="button" class="btn btn-sm btn-primary" id="zjf-edit-save">保存</button>
        <button type="button" class="btn btn-sm btn-secondary" id="zjf-edit-cancel">取消</button>
      </div>

      <div class="table-responsive">
        <table class="table table-bordered table-hover">
          <thead>
            <tr>
              <th>ID</th>
              <th>名称</th>
              <th>站点</th>
              <th>加价</th>
              <th>超时</th>
              <th>状态</th>
              <th>排序</th>
              <th>备注</th>
              <th>更新时间</th>
              <th>操作</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($suppliers)): ?>
              <tr><td colspan="10" class="text-center text-muted">
                暂无供应商，请点击右上角「新增供应商」
              </td></tr>
            <?php else: ?>
              <?php foreach ($suppliers as $s): ?>
                <tr>
                  <td><?= (int)$s['id'] ?></td>
                  <td><?= htmlspecialchars($s['name'], ENT_QUOTES) ?></td>
                  <td class="small"><?= htmlspecialchars($s['api_url'], ENT_QUOTES) ?></td>
                  <td class="small"><?=
                    htmlspecialchars(zjmf_supplier_markup_label($s), ENT_QUOTES)
                  ?></td>
                  <td><?= (int)$s['api_timeout'] ?>s</td>
                  <td>
                    <span class="badge <?= $s['status'] == 1 ? 'badge-success' : 'badge-secondary' ?>">
                      <?= $s['status'] == 1 ? '启用' : '停用' ?>
                    </span>
                  </td>
                  <td><?= (int)$s['sort'] ?></td>
                  <td class="small"><?= htmlspecialchars($s['remark'] ?: '-') ?></td>
                  <td class="small"><?= htmlspecialchars($s['updated_at'] ?: '-') ?></td>
                  <td class="text-nowrap">
                    <button type="button" class="btn btn-sm btn-outline-primary zjf-test"
                            data-id="<?= (int)$s['id'] ?>">连通测试</button>
                    <button type="button" class="btn btn-sm btn-outline-primary zjf-edit"
                            data-id="<?= (int)$s['id'] ?>"
                            data-name="<?= htmlspecialchars($s['name'], ENT_QUOTES) ?>"
                            data-url="<?= htmlspecialchars($s['api_url'], ENT_QUOTES) ?>"
                            data-username="<?= htmlspecialchars($s['api_username'], ENT_QUOTES) ?>"
                            data-timeout="<?= (int)$s['api_timeout'] ?>"
                            data-mtype="<?= (int)$s['markup_type'] ?>"
                            data-mvalue="<?= (int)$s['markup_value'] ?>"
                            data-status="<?= (int)$s['status'] ?>"
                            data-sort="<?= (int)$s['sort'] ?>"
                            data-remark="<?= htmlspecialchars($s['remark'], ENT_QUOTES) ?>">编辑</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary zjf-toggle"
                            data-id="<?= (int)$s['id'] ?>">
                      <?= $s['status'] == 1 ? '停用' : '启用' ?>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-danger zjf-del"
                            data-id="<?= (int)$s['id'] ?>"
                            data-name="<?= htmlspecialchars($s['name'], ENT_QUOTES) ?>">删除</button>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
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
  function notify(d, reload) {
    var ok = d.qk == 1 || d.success;
    if (typeof $.notify === 'function') {
      $.notify({message: d.msg || d.code || '完成'}, {type: ok ? 'success' : 'danger'});
    } else {
      alert(d.msg || d.code || '完成');
    }
    if (ok && reload) setTimeout(function () { location.reload(); }, 600);
  }
  function post(data, reload) {
    $.post('ajax.php', data, function (r) { notify(res(r), reload); });
  }

  var wrap = document.getElementById('zjf-edit-wrap');
  var editTitle = document.getElementById('zjf-edit-title');
  var editId = document.getElementById('zjf-edit-id');

  function openPanel(reset) {
    if (reset) {
      editId.value = '0';
      document.getElementById('zjf-edit-name').value = '';
      document.getElementById('zjf-edit-url').value = '';
      document.getElementById('zjf-edit-username').value = '';
      document.getElementById('zjf-edit-password').value = '';
      document.getElementById('zjf-edit-timeout').value = '30';
      document.getElementById('zjf-edit-mtype').value = '0';
      document.getElementById('zjf-edit-mvalue').value = '0';
      document.getElementById('zjf-edit-status').value = '1';
      document.getElementById('zjf-edit-sort').value = '0';
      document.getElementById('zjf-edit-remark').value = '';
      editTitle.textContent = '新增供应商';
    }
    wrap.style.display = 'block';
    wrap.scrollIntoView({behavior: 'smooth', block: 'start'});
  }

  document.getElementById('zjf-supplier-add').addEventListener('click', function () {
    openPanel(true);
  });

  document.querySelectorAll('.zjf-edit').forEach(function (btn) {
    btn.addEventListener('click', function () {
      editId.value = btn.getAttribute('data-id');
      document.getElementById('zjf-edit-name').value = btn.getAttribute('data-name');
      document.getElementById('zjf-edit-url').value = btn.getAttribute('data-url');
      document.getElementById('zjf-edit-username').value = btn.getAttribute('data-username');
      document.getElementById('zjf-edit-password').value = '';
      document.getElementById('zjf-edit-timeout').value = btn.getAttribute('data-timeout');
      document.getElementById('zjf-edit-mtype').value = btn.getAttribute('data-mtype');
      document.getElementById('zjf-edit-mvalue').value = btn.getAttribute('data-mvalue');
      document.getElementById('zjf-edit-status').value = btn.getAttribute('data-status');
      document.getElementById('zjf-edit-sort').value = btn.getAttribute('data-sort');
      document.getElementById('zjf-edit-remark').value = btn.getAttribute('data-remark');
      editTitle.textContent = '编辑供应商';
      openPanel(false);
    });
  });

  document.getElementById('zjf-edit-cancel').addEventListener('click', function () {
    wrap.style.display = 'none';
  });

  document.getElementById('zjf-edit-save').addEventListener('click', function () {
    post({
      gn: 'p_zjmf_admin_save_supplier',
      id: editId.value,
      name: document.getElementById('zjf-edit-name').value,
      api_url: document.getElementById('zjf-edit-url').value,
      api_username: document.getElementById('zjf-edit-username').value,
      api_password: document.getElementById('zjf-edit-password').value,
      api_timeout: document.getElementById('zjf-edit-timeout').value,
      markup_type: document.getElementById('zjf-edit-mtype').value,
      markup_value: document.getElementById('zjf-edit-mvalue').value,
      status: document.getElementById('zjf-edit-status').value,
      sort: document.getElementById('zjf-edit-sort').value,
      remark: document.getElementById('zjf-edit-remark').value
    }, true);
  });

  document.querySelectorAll('.zjf-test').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var b = this;
      b.disabled = true;
      var old = b.textContent;
      b.textContent = '测试中...';
      $.post('ajax.php', {gn: 'p_zjmf_admin_test_supplier', id: btn.getAttribute('data-id')},
        function (r) {
          var d = res(r);
          notify(d, false);
          b.disabled = false;
          b.textContent = old;
        });
    });
  });

  document.querySelectorAll('.zjf-toggle').forEach(function (btn) {
    btn.addEventListener('click', function () {
      post({gn: 'p_zjmf_admin_toggle_supplier', id: btn.getAttribute('data-id')}, true);
    });
  });

  document.querySelectorAll('.zjf-del').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var name = btn.getAttribute('data-name');
      if (!confirm('确定删除供应商「' + name + '」吗？\n该供应商下存在商品/订单/主机时将无法删除。')) {
        return;
      }
      post({gn: 'p_zjmf_admin_delete_supplier', id: btn.getAttribute('data-id')}, true);
    });
  });
})();
</script>
