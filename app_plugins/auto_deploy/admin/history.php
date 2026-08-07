<?php
if (!defined('IN_CRONLITE')) {
	exit;
}
mnbt_admin_include('head');
?>
<style>
.ad-log-box { max-height: 400px; overflow: auto; background: #1e1e1e; color: #d4d4d4; padding: 12px; border-radius: 4px; font-family: 'Courier New', monospace; font-size: .85rem; white-space: pre-wrap; word-break: break-all; }
</style>

<div class="container-fluid p-t-15">
  <div class="card">
    <div class="card-header">
      <h4 class="mb-0"><i class="mdi mdi-history text-info"></i> 部署历史</h4>
    </div>
    <div class="card-body">
      <div class="mb-3">
        <button class="btn btn-sm btn-outline-primary" id="btn-load-history">加载历史</button>
        <button class="btn btn-sm btn-outline-secondary ml-2" id="btn-refresh-history">刷新</button>
      </div>
      <div id="ad-history-loading" style="display:none; text-align:center; padding:20px;">
        <div class="spinner-border text-primary" style="width:3rem;height:3rem;"></div><p>加载中...</p>
      </div>
      <div id="ad-history-table"></div>
      <div id="ad-history-pager" class="mt-3"></div>
    </div>
  </div>
</div>

<script>
function parseRes(res) {
  try { return typeof res === 'string' ? JSON.parse(res) : res; } catch (e) { return {code: res}; }
}
function escHtml(str) {
  if (!str && str !== 0) return '';
  var div = document.createElement('div');
  div.appendChild(document.createTextNode(String(str)));
  return div.innerHTML;
}

var currentPage = 1;

function loadHistory(page) {
  page = page || 1;
  currentPage = page;
  $('#ad-history-loading').show();
  $('#ad-history-table').empty();
  $('#ad-history-pager').empty();

  $.post('ajax.php', {gn: 'p_auto_deploy_history', page: page}, function (res) {
    $('#ad-history-loading').hide();
    var d = parseRes(res);
    if (d.qk != 1 && !d.success) {
      $('#ad-history-table').html('<p class="text-danger">加载失败：' + escHtml(d.msg || '') + '</p>');
      return;
    }
    var rows = d.rows || [];
    if (!rows.length) {
      $('#ad-history-table').html('<p class="text-muted">暂无部署记录</p>');
      return;
    }
    var html = '<div class="table-responsive"><table class="table table-sm table-bordered table-hover">';
    html += '<thead class="thead-light"><tr>';
    html += '<th>ID</th><th>时间</th><th>节点</th><th>软件包</th><th>目标网站</th><th>类型</th><th>结果</th><th>管理员</th><th>详情</th>';
    html += '</tr></thead><tbody>';
    $.each(rows, function (i, r) {
      var badge = r.result === 'success'
        ? '<span class="badge badge-success">成功</span>'
        : '<span class="badge badge-danger">失败</span>';
      var details = '';
      if (r.admin_username) details += '账号: ' + escHtml(r.admin_username) + '\n';
      if (r.admin_password) details += '密码: ' + escHtml(r.admin_password) + '\n';
      if (r.success_url) details += '入口: ' + escHtml(r.success_url);
      var detailHtml = details ? '<button class="btn btn-sm btn-outline-info ad-detail-btn" data-detail="' + escHtml(details) + '">查看</button>' : '-';
      html += '<tr>';
      html += '<td>' + (r.id || '') + '</td>';
      html += '<td>' + escHtml(r.created_at || '') + '</td>';
      html += '<td>' + escHtml(r.node_name || r.node_id) + '</td>';
      html += '<td><code>' + escHtml(r.dname || '') + '</code></td>';
      html += '<td>' + escHtml(r.site_name || '') + '</td>';
      html += '<td>' + escHtml(r.project_type || 'php') + '</td>';
      html += '<td>' + badge + '</td>';
      html += '<td>' + escHtml(r.admin_user || '') + '</td>';
      html += '<td>' + detailHtml + '</td>';
      html += '</tr>';
    });
    html += '</tbody></table></div>';
    $('#ad-history-table').html(html);

    // 分页
    var pager = '';
    if (d.pages > 1) {
      pager += '<nav><ul class="pagination pagination-sm">';
      for (var i = 1; i <= d.pages; i++) {
        pager += '<li class="page-item' + (i === d.page ? ' active' : '') + '"><a class="page-link ad-page-link" href="#" data-page="' + i + '">' + i + '</a></li>';
      }
      pager += '</ul></nav>';
      pager += '<small class="text-muted">共 ' + d.total + ' 条记录</small>';
    }
    $('#ad-history-pager').html(pager);
  });
}

$(document).on('click', '.ad-page-link', function (e) {
  e.preventDefault();
  loadHistory(parseInt($(this).data('page')));
});

$(document).on('click', '.ad-detail-btn', function () {
  var detail = $(this).data('detail');
  alert(detail);
});

$('#btn-load-history').on('click', function () { loadHistory(1); });
$('#btn-refresh-history').on('click', function () { loadHistory(currentPage); });

// 页面加载时自动加载
loadHistory(1);
</script>
