<?php
/**
 * 后台 - DNS 服务商凭证配置
 * AJAX gn：p_dns_provider_save / p_dns_provider_delete / p_dns_provider_test
 */
if (!defined('IN_CRONLITE')) exit;
mnbt_admin_include('head');
global $DB;
$providers = $DB->get_all_prepare("SELECT * FROM plg_dns_provider order by id asc") ?: [];
?>
<div class="container-fluid p-t-15">
  <div class="row">
    <div class="col-lg-6">
      <div class="card">
        <header class="card-header"><div class="card-title">添加 / 编辑 DNS 服务商</div></header>
        <div class="card-body">
          <form id="providerForm">
            <input type="hidden" id="pid" value="0">
            <div class="form-group">
              <label>服务商类型</label>
              <select class="form-control" id="slug">
                <option value="dnspod">DNSPod（腾讯云）</option>
              </select>
              <small>首期仅支持 DNSPod，后续扩展 Cloudflare / 阿里云 DNS</small>
            </div>
            <div class="form-group">
              <label>显示名</label>
              <input type="text" class="form-control" id="name" placeholder="如：我的 DNSPod 账号">
            </div>
            <div class="form-group">
              <label>SecretId（腾讯云 API 密钥 ID）</label>
              <input type="text" class="form-control" id="api_id" placeholder="形如 AKIDxxxxxxxxxxxxxxxx">
              <small>腾讯云控制台 → 访问管理 → API 密钥管理 中创建，形如 AKID 开头</small>
            </div>
            <div class="form-group">
              <label>SecretKey（腾讯云 API 密钥 Key）</label>
              <input type="text" class="form-control" id="api_secret" placeholder="腾讯云 SecretKey">
              <small>DNSPod 已停用旧版 API Token，请改用腾讯云 API 3.0 密钥（SecretId/SecretKey）</small>
            </div>
            <div class="form-group">
              <label class="btn-block">是否启用</label>
              <div class="col-xs-6">
                <div class="custom-control custom-switch custom-info">
                  <input type="checkbox" class="custom-control-input" id="kg" checked>
                  <label class="custom-control-label" for="kg"></label>
                </div>
              </div>
            </div>
            <button type="button" class="btn btn-primary" onclick="saveProvider()">保存</button>
            <button type="button" class="btn btn-default" onclick="resetForm()">重置</button>
          </form>
        </div>
      </div>
    </div>

    <div class="col-lg-6">
      <div class="card">
        <header class="card-header"><div class="card-title">已配置的服务商</div></header>
        <div class="card-body">
          <?php if (empty($providers)): ?>
            <p class="text-muted">暂无配置，请在左侧添加</p>
          <?php else: ?>
            <table class="table table-bordered table-striped">
              <thead>
                <tr>
                  <th>ID</th><th>类型</th><th>名称</th><th>SecretId</th>
                  <th>状态</th><th>添加时间</th><th>操作</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($providers as $p): ?>
                  <tr>
                    <td><?= (int)$p['id'] ?></td>
                    <td><?= htmlspecialchars($p['slug']) ?></td>
                    <td><?= htmlspecialchars($p['name']) ?></td>
                    <td><?= htmlspecialchars(substr($p['api_id'], 0, 8)) ?>...</td>
                    <td>
                      <?php if ($p['qk'] === 'true'): ?>
                        <span class="badge badge-success">启用</span>
                      <?php else: ?>
                        <span class="badge badge-danger">禁用</span>
                      <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($p['created_at']) ?></td>
                    <td>
                      <button class="btn btn-xs btn-default" title="编辑" onclick="editProvider(<?= htmlspecialchars(json_encode($p, 256)) ?>)">
                        <i class="mdi mdi-pencil"></i>
                      </button>
                      <button class="btn btn-xs btn-default" title="测试连接" onclick="testProvider(<?= (int)$p['id'] ?>)">
                        <i class="mdi mdi-connection"></i>
                      </button>
                      <button class="btn btn-xs btn-default" title="删除" onclick="delProvider(<?= (int)$p['id'] ?>)">
                        <i class="mdi mdi-window-close"></i>
                      </button>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<script type="text/javascript">
function saveProvider() {
  var data = {
    gn: 'p_dns_provider_save',
    id: pid.value,
    slug: slug.value,
    name: name.value,
    api_id: api_id.value,
    api_secret: api_secret.value,
    kg: kg.checked
  };
  if (!data.name || !data.api_id || !data.api_secret) {
    msalert(3, '参数不能为空', 2000); return;
  }
  msloading('保存中...');
  $.post('ajax.php', data, function (date) {
    msloadingde();
    var r = JSON.parse(date);
    if (r.qk == 1) {
      msalert(1, r.msg || '保存成功', 2000);
      setTimeout(function () { location.reload(); }, 800);
    } else {
      msalert(4, r.msg || '保存失败', 3000);
    }
  });
}

function resetForm() {
  pid.value = 0; slug.value = 'dnspod';
  name.value = ''; api_id.value = ''; api_secret.value = '';
  kg.checked = true;
}

function editProvider(row) {
  pid.value = row.id;
  slug.value = row.slug;
  name.value = row.name;
  api_id.value = row.api_id;
  api_secret.value = row.api_secret;
  kg.checked = (row.qk == 'true');
  $('html,body').animate({ scrollTop: 0 }, 200);
}

function testProvider(id) {
  msloading('测试连接中...');
  $.post('ajax.php', { gn: 'p_dns_provider_test', id: id }, function (date) {
    msloadingde();
    var r = JSON.parse(date);
    if (r.qk == 1) msalert(1, r.msg || '测试成功', 3000);
    else msalert(4, r.msg || '测试失败', 4000);
  });
}

function delProvider(id) {
  if (!confirm('删除后该服务商下所有本地 DNS 记录也会被删除（远程记录无法批量清理）\n是否继续？')) return;
  $.post('ajax.php', { gn: 'p_dns_provider_delete', id: id }, function (date) {
    var r = JSON.parse(date);
    if (r.qk == 1) {
      msalert(1, r.msg || '删除成功', 2000);
      setTimeout(function () { location.reload(); }, 800);
    } else {
      msalert(4, r.msg || '删除失败', 3000);
    }
  });
}
</script>
