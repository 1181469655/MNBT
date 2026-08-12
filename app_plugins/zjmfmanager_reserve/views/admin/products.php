<?php
/**
 * 管理员端 - 商品管理
 *
 * 展示上游同步的商品列表（按供应商），支持：
 *   - 「同步商品」弹窗：选择供应商 → 拉取上游商品列表 → 勾选 → 同步
 *   - 「手动添加」表单：供应商 + 上游商品 ID + 名称 + 描述
 *   - 单品加价/上架/排序配置
 * 同步仅刷新上游名称/价格，不覆盖管理员已配置的加价与上架状态。
 */
if (!defined('IN_CRONLITE')) {
	exit;
}
mnbt_admin_include('head');

$products = zjmf_product_list_all();
$suppliers = zjmf_supplier_list_all();

function zjmf_admin_cycle_summary($product)
{
	$cycles = zjmf_product_cycles($product);
	$parts = [];
	foreach ($cycles as $cycle => $cfg) {
		$parts[] = $cfg['name'] . ' ¥' . zjmf_format_cents($cfg['price_cents']);
	}
	return implode('  ', $parts);
}

function zjmf_admin_markup_label($product)
{
	$type = (int)$product['markup_type'];
	$value = (int)$product['markup_value'];
	if ($type === 0 && $value === 0) {
		return '使用供应商规则';
	}
	return $type === 1
		? '固定 +' . zjmf_format_cents($value) . ' 元'
		: '比例 +' . ($value / 10) . '%';
}
?>
<div class="container-fluid p-t-15">
  <div class="card">
    <div class="card-header">
      <h4 style="display:inline-block">商品管理</h4>
      <button type="button" class="btn btn-primary btn-sm float-right"
              id="zjf-add">手动添加</button>
      <button type="button" class="btn btn-primary btn-sm float-right mr-2"
              id="zjf-sync">同步商品</button>
    </div>
    <div class="card-body">
      <p class="text-muted">
        同步从上游拉取商品与代理价，并按各周期试算本地售价；
        同步不会覆盖已配置的加价、上架状态与排序。
      </p>

      <!-- 同步商品面板 -->
      <div id="zjf-sync-wrap" class="mb-3 border rounded p-3" style="display:none;">
        <h6 class="mb-2">同步商品</h6>
        <div class="form-row mb-2">
          <div class="col-md-4">
            <select id="zjf-sync-supplier" class="form-control form-control-sm">
              <?php if (empty($suppliers)): ?>
                <option value="">请先在供应商管理中新增供应商</option>
              <?php else: ?>
                <?php foreach ($suppliers as $s): ?>
                  <option value="<?= (int)$s['id'] ?>"><?=
                    htmlspecialchars($s['name'], ENT_QUOTES)
                  ?></option>
                <?php endforeach; ?>
              <?php endif; ?>
            </select>
          </div>
          <div class="col-md-3">
            <button type="button" class="btn btn-sm btn-outline-primary"
                    id="zjf-sync-load">加载商品列表</button>
          </div>
          <div class="col-md-3">
            <input type="text" id="zjf-sync-filter" class="form-control form-control-sm"
                   placeholder="按名称过滤">
          </div>
        </div>
        <div id="zjf-sync-tip" class="small text-muted mb-2"></div>
        <div class="table-responsive">
          <table class="table table-bordered table-sm">
            <thead>
              <tr>
                <th style="width:40px;">
                  <input type="checkbox" id="zjf-sync-all"> 全选
                </th>
                <th>上游ID</th>
                <th>名称</th>
                <th>价格</th>
                <th>模块</th>
                <th>状态</th>
              </tr>
            </thead>
            <tbody id="zjf-sync-list">
              <tr><td colspan="6" class="text-center text-muted">
                请先选择供应商并加载商品列表
              </td></tr>
            </tbody>
          </table>
        </div>
        <button type="button" class="btn btn-sm btn-primary" id="zjf-sync-do">开始同步</button>
        <button type="button" class="btn btn-sm btn-secondary" id="zjf-sync-cancel">关闭</button>
      </div>

      <!-- 手动添加商品面板 -->
      <div id="zjf-add-wrap" class="mb-3 border rounded p-3" style="display:none;">
        <h6 class="mb-2">手动添加商品</h6>
        <div class="form-row">
          <div class="col-md-3 mb-2">
            <label class="small text-muted">所属供应商 *</label>
            <select id="zjf-add-supplier" class="form-control form-control-sm">
              <?php foreach ($suppliers as $s): ?>
                <option value="<?= (int)$s['id'] ?>"><?=
                  htmlspecialchars($s['name'], ENT_QUOTES)
                ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-2 mb-2">
            <label class="small text-muted">上游商品 ID *</label>
            <input type="number" id="zjf-add-upid" class="form-control form-control-sm" min="1">
          </div>
          <div class="col-md-3 mb-2">
            <label class="small text-muted">商品名称 *</label>
            <input type="text" id="zjf-add-name" class="form-control form-control-sm" maxlength="100">
          </div>
          <div class="col-md-4 mb-2">
            <label class="small text-muted">描述（支持 HTML）</label>
            <textarea id="zjf-add-desc" class="form-control form-control-sm" rows="2"
                      placeholder="支持 HTML 标签，如 &lt;b&gt;加粗&lt;/b&gt;"></textarea>
          </div>
        </div>
        <div class="small text-muted mb-2">
          保存后将立即拉取该商品上游代理价与各周期价格；失败则该商品标记为「待同步」。
        </div>
        <button type="button" class="btn btn-sm btn-primary" id="zjf-add-save">添加</button>
        <button type="button" class="btn btn-sm btn-secondary" id="zjf-add-cancel">取消</button>
      </div>

      <!-- 编辑商品面板 -->
      <div id="zjf-edit-wrap" class="mb-3 border rounded p-3" style="display:none;">
        <h6 class="mb-2">编辑商品</h6>
        <input type="hidden" id="zjf-edit-id" value="0">
        <div class="form-row">
          <div class="col-md-4 mb-2">
            <label class="small text-muted">商品名 *</label>
            <input type="text" id="zjf-edit-name" class="form-control form-control-sm"
                   maxlength="100">
          </div>
          <div class="col-md-2 mb-2">
            <label class="small text-muted">加价方式</label>
            <select id="zjf-edit-type" class="form-control form-control-sm">
              <option value="0">按比例</option>
              <option value="1">固定加价（分）</option>
            </select>
          </div>
          <div class="col-md-2 mb-2">
            <label class="small text-muted">加价数值</label>
            <input type="number" id="zjf-edit-value" class="form-control form-control-sm"
                   min="0" value="0">
          </div>
          <div class="col-md-2 mb-2">
            <label class="small text-muted">排序</label>
            <input type="number" id="zjf-edit-sort" class="form-control form-control-sm"
                   min="0" value="50">
          </div>
          <div class="col-md-2 mb-2">
            <label class="small text-muted">状态</label>
            <select id="zjf-edit-status" class="form-control form-control-sm">
              <option value="1">上架</option>
              <option value="0">下架</option>
            </select>
          </div>
          <div class="col-md-12 mb-2">
            <label class="small text-muted">商品简介（支持 HTML）</label>
            <textarea id="zjf-edit-desc" class="form-control form-control-sm" rows="4"
                      placeholder="支持 HTML 标签，如 &lt;b&gt;加粗&lt;/b&gt;、&lt;br&gt; 换行。同步商品不会覆盖此处内容。"></textarea>
          </div>
        </div>
        <div class="small text-muted mb-2">
          留空加价数值表示使用所属供应商的加价规则；重新同步上游仅更新价格，不覆盖本地名称与简介。
        </div>
        <button type="button" class="btn btn-sm btn-primary" id="zjf-edit-save">保存</button>
        <button type="button" class="btn btn-sm btn-secondary" id="zjf-edit-cancel">取消</button>
      </div>

      <div class="table-responsive">
        <table class="table table-bordered table-hover">
          <thead>
            <tr>
              <th>ID</th>
              <th>供应商</th>
              <th>商品名</th>
              <th>上游ID</th>
              <th>上游代理价</th>
              <th>周期售价</th>
              <th>加价</th>
              <th>状态</th>
              <th>排序</th>
              <th>更新时间</th>
              <th>操作</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($products)): ?>
              <tr><td colspan="11" class="text-center text-muted">
                暂无商品，请先点击右上角「同步商品」或「手动添加」
              </td></tr>
            <?php else: ?>
              <?php foreach ($products as $p): ?>
                <tr>
                  <td><?= (int)$p['id'] ?></td>
                  <td><?= htmlspecialchars($p['supplier_name'] ?: '-', ENT_QUOTES) ?></td>
                  <td><?= htmlspecialchars($p['name'], ENT_QUOTES) ?></td>
                  <td><?= (int)$p['up_product_id'] ?></td>
                  <td>¥<?= zjmf_format_cents($p['agent_price_cents']) ?></td>
                  <td class="small"><?=
                    htmlspecialchars(zjmf_admin_cycle_summary($p), ENT_QUOTES)
                  ?></td>
                  <td class="small"><?=
                    htmlspecialchars(zjmf_admin_markup_label($p), ENT_QUOTES)
                  ?></td>
                  <td>
                    <span class="badge <?= $p['status'] == 1 ? 'badge-success' : 'badge-secondary' ?>">
                      <?= $p['status'] == 1 ? '上架' : '下架' ?>
                    </span>
                  </td>
                  <td><?= (int)$p['sort'] ?></td>
                  <td class="small"><?= htmlspecialchars($p['updated_at'] ?: '-') ?></td>
                  <td class="text-nowrap">
                    <button type="button" class="btn btn-sm btn-outline-primary zjf-edit"
                            data-id="<?= (int)$p['id'] ?>"
                            data-name="<?= htmlspecialchars($p['name'], ENT_QUOTES) ?>"
                            data-desc="<?= htmlspecialchars($p['description'] ?? '', ENT_QUOTES) ?>"
                            data-type="<?= (int)$p['markup_type'] ?>"
                            data-value="<?= (int)$p['markup_value'] ?>"
                            data-sort="<?= (int)$p['sort'] ?>"
                            data-status="<?= (int)$p['status'] ?>">编辑</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary zjf-toggle"
                            data-id="<?= (int)$p['id'] ?>">
                      <?= $p['status'] == 1 ? '下架' : '上架' ?>
                    </button>
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
  function esc(s) {
    return String(s == null ? '' : s)
      .replace(/&/g, '&amp;').replace(/</g, '&lt;')
      .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
  }
  function post(data, reload) {
    $.post('ajax.php', data, function (r) { notify(res(r), reload); });
  }

  var syncWrap = document.getElementById('zjf-sync-wrap');
  var addWrap = document.getElementById('zjf-add-wrap');
  var editWrap = document.getElementById('zjf-edit-wrap');
  var syncList = document.getElementById('zjf-sync-list');
  var syncTip = document.getElementById('zjf-sync-tip');

  /* ---------------- 同步商品 ---------------- */
  document.getElementById('zjf-sync').addEventListener('click', function () {
    addWrap.style.display = 'none';
    editWrap.style.display = 'none';
    syncWrap.style.display = 'block';
    syncWrap.scrollIntoView({behavior: 'smooth', block: 'start'});
  });

  document.getElementById('zjf-sync-load').addEventListener('click', function () {
    var supplierId = document.getElementById('zjf-sync-supplier').value;
    if (!supplierId) { alert('请先选择供应商'); return; }
    var btn = this;
    btn.disabled = true;
    btn.textContent = '加载中...';
    syncTip.textContent = '正在拉取上游商品列表...';
    syncList.innerHTML = '<tr><td colspan="6" class="text-center text-muted">加载中...</td></tr>';
    $.post('ajax.php', {gn: 'p_zjmf_admin_upstream_products', id: supplierId},
      function (r) {
        var d = res(r);
        btn.disabled = false;
        btn.textContent = '加载商品列表';
        if (d.qk != 1 && !d.success) {
          syncTip.textContent = '';
          syncList.innerHTML = '<tr><td colspan="6" class="text-center text-muted">'
            + (d.msg || d.code || '拉取失败') + '</td></tr>';
          return;
        }
        var list = d.list || (d.data && d.data.list) || [];
        if (!list.length) {
          syncList.innerHTML = '<tr><td colspan="6" class="text-center text-muted">'
            + '该供应商暂无商品</td></tr>';
          return;
        }
        syncTip.textContent = '共 ' + list.length + ' 个商品，勾选要同步的项目后点击「开始同步」'
          + '（已同步过的将更新价格，不覆盖本地名称/简介与加价配置）';
        var html = '';
        for (var i = 0; i < list.length; i++) {
          var it = list[i];
          html += '<tr class="zjf-up-row" data-name="' + esc((it.name || '').toLowerCase()) + '">'
            + '<td><input type="checkbox" class="zjf-up-check" value="' + it.id + '"'
            + (it.synced ? ' checked' : '') + '></td>'
            + '<td>' + it.id + '</td>'
            + '<td>' + esc(it.name || '-') + '</td>'
            + '<td>' + (it.price ? '¥' + (it.price / 100).toFixed(2) : '-') + '</td>'
            + '<td>' + (it.module ? '<span class="badge badge-info">' + esc(it.module) + '</span>' : '-') + '</td>'
            + '<td>' + (it.synced ? '<span class="badge badge-success">已同步</span>'
                : '<span class="badge badge-secondary">未同步</span>') + '</td>'
            + '</tr>';
        }
        syncList.innerHTML = html;
        renderSyncFilter();
      });
  });

  function renderSyncFilter() {
    var kw = (document.getElementById('zjf-sync-filter').value || '').toLowerCase();
    document.querySelectorAll('.zjf-up-row').forEach(function (row) {
      row.style.display = (kw === '' || (row.getAttribute('data-name') || '').indexOf(kw) >= 0)
        ? '' : 'none';
    });
  }
  document.getElementById('zjf-sync-filter').addEventListener('input', renderSyncFilter);

  document.getElementById('zjf-sync-all').addEventListener('change', function () {
    var checked = this.checked;
    document.querySelectorAll('.zjf-up-row').forEach(function (row) {
      if (row.style.display !== 'none') {
        row.querySelector('.zjf-up-check').checked = checked;
      }
    });
  });

  document.getElementById('zjf-sync-do').addEventListener('click', function () {
    var supplierId = document.getElementById('zjf-sync-supplier').value;
    if (!supplierId) { alert('请先选择供应商'); return; }
    var ids = [];
    document.querySelectorAll('.zjf-up-check:checked').forEach(function (c) {
      ids.push(parseInt(c.value, 10));
    });
    if (!ids.length) { alert('请至少勾选一个商品'); return; }
    var btn = this;
    btn.disabled = true;
    btn.textContent = '同步中...';
    $.post('ajax.php', {
      gn: 'p_zjmf_admin_sync_products',
      supplier_id: supplierId,
      up_ids: ids
    }, function (r) {
      var d = res(r);
      notify(d, d.qk == 1 || d.success);
      btn.disabled = false;
      btn.textContent = '开始同步';
    });
  });

  document.getElementById('zjf-sync-cancel').addEventListener('click', function () {
    syncWrap.style.display = 'none';
  });

  /* ---------------- 手动添加 ---------------- */
  document.getElementById('zjf-add').addEventListener('click', function () {
    syncWrap.style.display = 'none';
    editWrap.style.display = 'none';
    addWrap.style.display = 'block';
    addWrap.scrollIntoView({behavior: 'smooth', block: 'start'});
  });

  document.getElementById('zjf-add-cancel').addEventListener('click', function () {
    addWrap.style.display = 'none';
  });

  document.getElementById('zjf-add-save').addEventListener('click', function () {
    var supplierId = document.getElementById('zjf-add-supplier').value;
    var upId = document.getElementById('zjf-add-upid').value;
    var name = document.getElementById('zjf-add-name').value;
    var desc = document.getElementById('zjf-add-desc').value;
    if (!supplierId || !upId || !name) {
      alert('请填写供应商、上游商品 ID 与名称');
      return;
    }
    post({
      gn: 'p_zjmf_admin_add_product',
      supplier_id: supplierId,
      up_product_id: upId,
      name: name,
      description: desc
    }, true);
  });

  /* ---------------- 编辑商品 ---------------- */
  var editId = document.getElementById('zjf-edit-id');
  var editName = document.getElementById('zjf-edit-name');
  var editDesc = document.getElementById('zjf-edit-desc');
  var editType = document.getElementById('zjf-edit-type');
  var editValue = document.getElementById('zjf-edit-value');
  var editSort = document.getElementById('zjf-edit-sort');
  var editStatus = document.getElementById('zjf-edit-status');

  document.querySelectorAll('.zjf-edit').forEach(function (btn) {
    btn.addEventListener('click', function () {
      syncWrap.style.display = 'none';
      addWrap.style.display = 'none';
      editId.value = btn.getAttribute('data-id');
      editName.value = btn.getAttribute('data-name');
      editDesc.value = btn.getAttribute('data-desc') || '';
      editType.value = btn.getAttribute('data-type');
      editValue.value = btn.getAttribute('data-value');
      editSort.value = btn.getAttribute('data-sort');
      editStatus.value = btn.getAttribute('data-status');
      editWrap.style.display = 'block';
      editWrap.scrollIntoView({behavior: 'smooth', block: 'start'});
    });
  });

  document.getElementById('zjf-edit-cancel').addEventListener('click', function () {
    editWrap.style.display = 'none';
  });

  document.getElementById('zjf-edit-save').addEventListener('click', function () {
    var id = parseInt(editId.value, 10);
    if (!id) return;
    if (!editName.value) { alert('请填写商品名称'); return; }
    post({
      gn: 'p_zjmf_admin_save_product',
      id: id,
      name: editName.value,
      description: editDesc.value,
      markup_type: editType.value,
      markup_value: editValue.value,
      sort: editSort.value,
      status: editStatus.value
    }, true);
  });

  document.querySelectorAll('.zjf-toggle').forEach(function (btn) {
    btn.addEventListener('click', function () {
      post({gn: 'p_zjmf_admin_toggle_product', id: btn.getAttribute('data-id')}, true);
    });
  });
})();
</script>
