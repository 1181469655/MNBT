<?php
/**
 * 用户端 - 我的 DNS 记录
 * AJAX gn：p_dns_record_list / p_dns_record_add / p_dns_record_delete
 *
 * 用户可在此页面：
 * - 查看自己已创建的 DNS 记录（含系统自动创建的）
 * - 为已购二级域名添加新的解析记录
 * - 删除自己的记录
 */
if (!defined('IN_CRONLITE')) exit;
mnbt_theme_include('head');
global $yhc, $DB;

$user = $yhc['user'] ?? '';
$providers = dns_provider_list_enabled();
$providerMap = [];
foreach ($providers as $p) $providerMap[$p['id']] = $p;

// 用户已购的二级域名（仅 DNS API 通道，泛解析通道无需用户管理 DNS 记录）
$ownedDomains = [];
$domainProviderMap = [];  // url -> provider_id
if ($user) {
	$products = $DB->get_all_prepare("SELECT * FROM plg_domain_product WHERE channel='dnsapi' order by id asc") ?: [];
	foreach ($products as $prod) {
		$buyers = json_decode($prod['json'] ?? '[]', true);
		if (is_array($buyers) && in_array($user, $buyers, true)) {
			$ownedDomains[] = $prod['url'];
			$domainProviderMap[$prod['url']] = (int)$prod['provider_id'];
		}
	}
}
?>
<style>
.ds-dns-empty { padding: 40px 0; text-align: center; color: #999; }
.ds-dns-table th { background: #f8f9fa; }
.ds-dns-type-A { background: #1e9fff; color: #fff; }
.ds-dns-type-CNAME { background: #5fb878; color: #fff; }
.ds-dns-type-TXT { background: #ffb800; color: #fff; }
.ds-dns-type-MX { background: #ff5722; color: #fff; }
.ds-dns-type-AAAA { background: #a020f0; color: #fff; }
.ds-dns-auto-tag { background: #eee; color: #888; font-size: 11px; padding: 2px 6px; border-radius: 3px; }
</style>

<div class="container-fluid p-t-15">
  <div class="row">
    <div class="col-lg-12">
      <div class="card">
        <header class="card-header">
          <div class="card-title">我的 DNS 记录</div>
        </header>
        <div class="card-body">
          <?php if (empty($providers)): ?>
            <div class="ds-dns-empty">
              <i class="mdi mdi-dns-off" style="font-size:48px;"></i>
              <p>管理员尚未配置 DNS 服务商，无法添加解析记录</p>
            </div>
          <?php else: ?>
            <!-- 添加记录表单 -->
            <div class="card border-light mb-3">
              <div class="card-header bg-light">添加 DNS 记录</div>
              <div class="card-body">
                <?php if (empty($ownedDomains)): ?>
                  <div class="text-muted" style="padding:15px 0;">
                    <i class="mdi mdi-information-outline"></i>
                    您尚未购买任何 <b>DNS API 通道</b>的二级域名。<br/>
                    泛解析通道的域名通过整体泛 A 记录访问，无需单独管理 DNS 记录。
                  </div>
                <?php else: ?>
                <form id="dnsAddForm" onsubmit="return false;">
                  <div class="row">
                    <div class="col-md-3">
                      <label>主域名</label>
                      <select class="form-control" id="domain" required>
                        <option value="">选择已购域名</option>
                        <?php foreach ($ownedDomains as $d): ?>
                          <option value="<?= htmlspecialchars($d) ?>" data-provider="<?= (int)($domainProviderMap[$d] ?? 0) ?>"><?= htmlspecialchars($d) ?></option>
                        <?php endforeach; ?>
                      </select>
                      <small>DNS API 通道已购域名</small>
                    </div>
                    <div class="col-md-2">
                      <label>主机记录</label>
                      <input type="text" class="form-control" id="name" placeholder="如 www / @" required>
                      <small>@ 表示根域名</small>
                    </div>
                    <div class="col-md-2">
                      <label>记录类型</label>
                      <select class="form-control" id="type">
                        <option value="A">A（IPv4）</option>
                        <option value="CNAME">CNAME</option>
                        <option value="TXT">TXT</option>
                        <option value="MX">MX</option>
                        <option value="AAAA">AAAA（IPv6）</option>
                      </select>
                    </div>
                    <div class="col-md-3">
                      <label>记录值</label>
                      <input type="text" class="form-control" id="value" placeholder="IP 或域名" required>
                    </div>
                    <div class="col-md-1">
                      <label>TTL</label>
                      <input type="number" class="form-control" id="ttl" value="600" min="60">
                    </div>
                    <div class="col-md-1" style="display:flex;align-items:flex-end;">
                      <button type="button" class="btn btn-primary btn-block" onclick="addRecord()">添加</button>
                    </div>
                  </div>
                </form>
                <?php endif; ?>
              </div>
            </div>

            <!-- 记录列表 -->
            <table class="table table-bordered table-hover table-striped ds-dns-table" id="dnsTable">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>主域名</th>
                  <th>主机记录</th>
                  <th>类型</th>
                  <th>记录值</th>
                  <th>TTL</th>
                  <th>来源</th>
                  <th>创建时间</th>
                  <th>操作</th>
                </tr>
              </thead>
              <tbody id="dnsTbody">
                <tr><td colspan="9" class="text-center text-muted">加载中...</td></tr>
              </tbody>
            </table>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<script type="text/javascript">
function loadRecords() {
  $.post('ajax.php', { gn: 'p_dns_record_list' }, function (date) {
    var r = typeof date === 'string' ? JSON.parse(date) : date;
    var rows = (r.extra && r.extra.rows) || [];
    if (!rows.length) {
      $('#dnsTbody').html('<tr><td colspan="9" class="text-center text-muted">暂无记录</td></tr>');
      return;
    }
    var html = '';
    for (var i = 0; i < rows.length; i++) {
      var x = rows[i];
      var typeCls = 'ds-dns-type-' + x.type;
      var sourceTag = x.auto == 1 ? '<span class="ds-dns-auto-tag">系统自动</span>' : '<span class="ds-dns-auto-tag" style="background:#1e9fff;color:#fff;">手动</span>';
      html += '<tr>'
        + '<td>' + x.id + '</td>'
        + '<td>' + escapeHtml(x.domain) + '</td>'
        + '<td>' + escapeHtml(x.name) + '</td>'
        + '<td><span class="badge ' + typeCls + '">' + escapeHtml(x.type) + '</span></td>'
        + '<td>' + escapeHtml(x.value) + '</td>'
        + '<td>' + x.ttl + '</td>'
        + '<td>' + sourceTag + '</td>'
        + '<td>' + escapeHtml(x.created_at) + '</td>'
        + '<td><button class="btn btn-xs btn-default" onclick="delRecord(' + x.id + ')"><i class="mdi mdi-window-close"></i></button></td>'
        + '</tr>';
    }
    $('#dnsTbody').html(html);
  });
}

function addRecord() {
  var selOpt = domain.options[domain.selectedIndex];
  var pid = selOpt ? parseInt(selOpt.getAttribute('data-provider') || '0', 10) : 0;
  var data = {
    gn: 'p_dns_record_add',
    provider_id: pid,
    domain: domain.value,
    name: name.value,
    type: type.value,
    value: value.value,
    ttl: ttl.value
  };
  if (!data.domain || !data.name || !data.value) {
    msalert(3, '请填写完整', 2000); return;
  }
  msloading('创建中...');
  $.post('ajax.php', data, function (date) {
    msloadingde();
    var r = typeof date === 'string' ? JSON.parse(date) : date;
    if (r.qk == 1) {
      msalert(1, r.msg || '创建成功', 2000);
      name.value = ''; value.value = '';
      loadRecords();
    } else {
      msalert(4, r.msg || '创建失败', 3000);
    }
  });
}

function delRecord(id) {
  if (!confirm('删除后本地与远程记录都会被清除\n是否继续？')) return;
  msloading('删除中...');
  $.post('ajax.php', { gn: 'p_dns_record_delete', id: id }, function (date) {
    msloadingde();
    var r = typeof date === 'string' ? JSON.parse(date) : date;
    if (r.qk == 1) {
      msalert(1, r.msg || '删除成功', 2000);
      loadRecords();
    } else {
      msalert(4, r.msg || '删除失败', 3000);
    }
  });
}

function escapeHtml(s) {
  return String(s).replace(/[&<>"']/g, function (c) {
    return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
  });
}

$(function () { loadRecords(); });
</script>
