<?php
if (!defined('IN_CRONLITE')) exit;
mnbt_theme_include('head');
global $yhc, $DB;
$user = $yhc['user'] ?? '';
?>
<style>
.ad-log-box { max-height: 400px; overflow: auto; background: #1e1e1e; color: #d4d4d4; padding: 12px; border-radius: 4px; font-family: 'Courier New', monospace; font-size: .85rem; white-space: pre-wrap; word-break: break-all; }
</style>

<div class="container-fluid p-t-15">
  <div class="card">
    <header class="card-header">
      <div class="card-title"><i class="mdi mdi-history"></i> 部署记录</div>
    </header>
    <div class="card-body">
      <div class="mb-3">
        <button class="btn btn-sm btn-outline-primary" id="btn-load">加载记录</button>
        <button class="btn btn-sm btn-outline-secondary ml-2" id="btn-refresh">刷新</button>
      </div>
      <div id="ad-loading" style="display:none; text-align:center; padding:20px;">
        <div class="spinner-border text-primary"></div><p>加载中...</p>
      </div>
      <div id="ad-table"></div>
      <div id="ad-pager" class="mt-3"></div>
    </div>
  </div>
</div>

<script>
function parseRes(res) {
  try { return typeof res === 'string' ? JSON.parse(res) : res; } catch (e) { return {code: res}; }
}
function escHtml(s) {
  return String(s).replace(/[&<>"']/g, function (c) {
    return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
  });
}
var currentPage = 1;

function loadHistory(page) {
  page = page || 1;
  currentPage = page;
  $('#ad-loading').show();
  $('#ad-table').empty();
  $('#ad-pager').empty();

  $.post('ajax.php', {gn: 'p_auto_deploy_history', page: page}, function (res) {
    $('#ad-loading').hide();
    var d = parseRes(res);
    if (d.qk != 1 && !d.success) {
      $('#ad-table').html('<p class="text-danger">加载失败</p>');
      return;
    }
    var rows = d.rows || [];
    if (!rows.length) {
      $('#ad-table').html('<p class="text-muted text-center py-4">暂无部署记录</p>');
      return;
    }
    var html = '<div class="table-responsive"><table class="table table-sm table-bordered table-hover">';
    html += '<thead class="thead-light"><tr>';
    html += '<th>ID</th><th>时间</th><th>节点</th><th>软件包</th><th>目标网站</th><th>结果</th><th>详情</th>';
    html += '</tr></thead><tbody>';
    $.each(rows, function (i, r) {
      var badge = r.result === 'success'
        ? '<span class="badge badge-success">成功</span>'
        : '<span class="badge badge-danger">失败</span>';
      var detailText = '';
      if (r.admin_username) detailText += '账号: ' + r.admin_username + '\n';
      if (r.admin_password) detailText += '密码: ' + r.admin_password + '\n';
      if (r.success_url) detailText += '入口: ' + r.success_url;
      var detailBtn = detailText ? '<button class="btn btn-sm btn-outline-info ad-detail-btn" data-detail="' + escHtml(detailText) + '">查看</button>' : '-';
      html += '<tr>';
      html += '<td>' + (r.id || '') + '</td>';
      html += '<td>' + escHtml(r.created_at || '') + '</td>';
      html += '<td>' + escHtml(r.node_name || r.node_id || '') + '</td>';
      html += '<td><code>' + escHtml(r.dname || '') + '</code></td>';
      html += '<td>' + escHtml(r.site_name || '') + '</td>';
      html += '<td>' + badge + '</td>';
      html += '<td>' + detailBtn + '</td>';
      html += '</tr>';
    });
    html += '</tbody></table></div>';
    $('#ad-table').html(html);

    var pager = '';
    if (d.pages > 1) {
      pager += '<nav><ul class="pagination pagination-sm">';
      for (var i = 1; i <= d.pages; i++) {
        pager += '<li class="page-item' + (i === d.page ? ' active' : '') + '"><a class="page-link ad-page-link" href="#" data-page="' + i + '">' + i + '</a></li>';
      }
      pager += '</ul></nav>';
      pager += '<small class="text-muted">共 ' + d.total + ' 条记录</small>';
    }
    $('#ad-pager').html(pager);
  });
}

$(document).on('click', '.ad-page-link', function (e) {
  e.preventDefault();
  loadHistory(parseInt($(this).data('page')));
});
$(document).on('click', '.ad-detail-btn', function () {
  alert($(this).data('detail'));
});

$('#btn-load').on('click', function () { loadHistory(1); });
$('#btn-refresh').on('click', function () { loadHistory(currentPage); });

loadHistory(1);
</script>
