<?php
if (!defined('IN_CRONLITE')) exit;
mnbt_theme_include('head');
global $yhc, $DB;
$user = $yhc['user'] ?? '';

// 服务端直接获取当前用户主机（系统设计：一个用户对应一个主机）
$myHost = function_exists('auto_deploy_get_my_host') ? auto_deploy_get_my_host() : null;
$myNodeId = 0;
if ($myHost) {
	$myNodeId = (int)($myHost['node_id'] ?? 0);
}
?>
<style>
.ad-loading { display: none; text-align: center; padding: 30px; }
.ad-deploy-result { display: none; }
.ad-pkg-table td { vertical-align: middle; }
</style>

<div class="container-fluid p-t-15">
  <div class="card">
    <header class="card-header">
      <div class="card-title"><i class="mdi mdi-monitor-dashboard"></i> 软件商店 - 一键部署</div>
    </header>
    <div class="card-body">

      <!-- 当前主机信息（服务端渲染） -->
      <div class="mb-3">
        <?php if (!$myHost): ?>
          <div class="alert alert-warning mb-0">
            <i class="mdi mdi-alert-circle-outline"></i> 您还没有可用的主机，请先开通主机服务后再使用一键部署。
          </div>
        <?php else: ?>
          <div class="alert alert-info mb-0">
            <i class="mdi mdi-server"></i> 当前主机：<strong><?= htmlspecialchars((string)$myHost['sqldz']) ?></strong>
            <span class="text-muted">（节点：<?= htmlspecialchars((string)$myHost['btdh']) ?> · <?= htmlspecialchars((string)$myHost['btip']) ?>:<?= htmlspecialchars((string)$myHost['btdk']) ?>）</span>
            <br><small class="text-muted">部署将覆盖该主机网站目录中的现有内容，请谨慎操作。</small>
          </div>
        <?php endif; ?>
      </div>

      <hr>

      <!-- 软件包列表（表格 + 分页） -->
      <div class="row mb-3">
        <div class="col-md-4">
          <button type="button" class="btn btn-primary btn-block" id="btn-load-pkgs" <?= $myHost ? '' : 'disabled' ?>>
            <i class="mdi mdi-refresh"></i> 加载软件列表
          </button>
        </div>
        <div class="col-md-5">
          <input type="text" class="form-control" id="ad-pkg-search" placeholder="搜索软件包..." disabled>
        </div>
      </div>
      <div id="ad-packages-tip" class="text-muted mb-2" <?= $myHost ? 'style="display:none;"' : '' ?>>点击「加载软件列表」按钮获取可用软件。</div>
      <div id="ad-packages-loading" class="ad-loading"><div class="spinner-border text-primary"></div><p>加载软件包中...</p></div>
      <div id="ad-pkg-area" style="display:none;">
        <div class="table-responsive">
          <table class="table table-bordered table-hover table-striped ad-pkg-table">
            <thead class="thead-light">
              <tr>
                <th style="width:50px;">#</th>
                <th>软件名称</th>
                <th style="width:90px;">版本</th>
                <th style="width:120px;">作者</th>
                <th style="width:90px;">价格</th>
                <th style="width:130px;">操作</th>
              </tr>
            </thead>
            <tbody id="ad-pkg-tbody"></tbody>
          </table>
        </div>
        <div class="d-flex justify-content-between align-items-center" id="ad-pkg-pager"></div>
      </div>

      <!-- 部署结果 -->
      <div class="ad-deploy-result mt-3 card border-success" id="ad-result-box">
        <div class="card-header bg-success text-white">部署结果</div>
        <div class="card-body" id="ad-result-body"></div>
      </div>

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
function msg(msg, type, time) {
  if (typeof msalert === 'function') { msalert(type || 1, msg, time || 2500); }
  else { alert(msg); }
}

var allPackages = [];   // 全部软件包
var filteredPackages = []; // 过滤后的软件包
var pageSize = 10;      // 每页条数
var currentPage = 1;
var myNodeId = <?= (int)$myNodeId ?>; // 当前用户主机所在节点 ID（服务端注入）
var myHostReady = <?= $myHost ? 'true' : 'false' ?>;

// ======================== 加载软件包列表 ========================
function loadPackages() {
  if (!myHostReady) { msg('您还没有可用的主机，请先开通主机服务', 4); return; }
  if (!myNodeId) { msg('主机节点信息异常', 4); return; }

  $('#ad-packages-tip').hide();
  $('#ad-pkg-area').hide();
  $('#ad-packages-loading').show();
  $('#ad-pkg-search').prop('disabled', true);

  $.ajax({
    url: 'ajax.php',
    type: 'POST',
    data: {gn: 'p_auto_deploy_packages', node_id: myNodeId},
    timeout: 35000,
    success: function (res) {
      $('#ad-packages-loading').hide();
      var d = parseRes(res);
      if (d.qk != 1 && !d.success) {
        $('#ad-packages-tip').text(d.msg || '加载失败').show();
        return;
      }
      allPackages = d.packages || [];
      if (!allPackages.length) {
        $('#ad-packages-tip').text('该节点暂无可用软件包').show();
        return;
      }
      $('#ad-pkg-search').val('').prop('disabled', false);
      applyFilter();
      $('#ad-pkg-area').show();
    },
    error: function (xhr, status) {
      $('#ad-packages-loading').hide();
      $('#ad-packages-tip').text('软件列表加载失败（' + (status === 'timeout' ? '请求超时' : xhr.status) + '），请检查节点面板是否可访问。').show();
    }
  });
}
$('#btn-load-pkgs').on('click', loadPackages);

// ======================== 过滤 + 分页渲染 ========================
function applyFilter() {
  var kw = $('#ad-pkg-search').val().trim().toLowerCase();
  if (kw) {
    filteredPackages = allPackages.filter(function (p) {
      return (String(p.title || '') + ' ' + String(p.name || '')).toLowerCase().indexOf(kw) !== -1;
    });
  } else {
    filteredPackages = allPackages.slice();
  }
  currentPage = 1;
  renderPkgPage();
}

function renderPkgPage() {
  var total = filteredPackages.length;
  var pages = Math.max(1, Math.ceil(total / pageSize));
  if (currentPage > pages) currentPage = pages;
  var start = (currentPage - 1) * pageSize;
  var slice = filteredPackages.slice(start, start + pageSize);

  var html = '';
  if (!slice.length) {
    html = '<tr><td colspan="6" class="text-center text-muted">未找到匹配的软件包</td></tr>';
  } else {
    $.each(slice, function (i, p) {
      var price = p.price > 0 ? '<span class="text-warning">&yen;' + escHtml(p.price) + '</span>' : '<span class="text-success">免费</span>';
      html += '<tr>';
      html += '<td>' + (start + i + 1) + '</td>';
      html += '<td><strong>' + escHtml(p.title || p.name) + '</strong></td>';
      html += '<td>v' + escHtml(p.version || '') + '</td>';
      html += '<td>' + escHtml(p.author || '') + '</td>';
      html += '<td>' + price + '</td>';
      html += '<td><button type="button" class="btn btn-sm btn-primary ad-deploy-btn" data-name="' + escHtml(p.name || '') + '" data-title="' + escHtml(p.title || p.name) + '">';
      html += '<i class="mdi mdi-open-in-new"></i> 部署</button></td>';
      html += '</tr>';
    });
  }
  $('#ad-pkg-tbody').html(html);

  // 分页控件
  var pager = '';
  if (pages > 1) {
    pager += '<nav><ul class="pagination pagination-sm mb-0">';
    pager += '<li class="page-item' + (currentPage <= 1 ? ' disabled' : '') + '"><a class="page-link ad-page" href="#" data-page="' + (currentPage - 1) + '">上一页</a></li>';
    var showStart = Math.max(1, currentPage - 2);
    var showEnd = Math.min(pages, showStart + 4);
    showStart = Math.max(1, showEnd - 4);
    for (var i = showStart; i <= showEnd; i++) {
      pager += '<li class="page-item' + (i === currentPage ? ' active' : '') + '"><a class="page-link ad-page" href="#" data-page="' + i + '">' + i + '</a></li>';
    }
    pager += '<li class="page-item' + (currentPage >= pages ? ' disabled' : '') + '"><a class="page-link ad-page" href="#" data-page="' + (currentPage + 1) + '">下一页</a></li>';
    pager += '</ul></nav>';
  }
  pager += '<small class="text-muted">共 ' + total + ' 个软件，第 ' + currentPage + '/' + pages + ' 页</small>';
  $('#ad-pkg-pager').html(pager);
}

// 分页点击
$(document).on('click', '.ad-page', function (e) {
  e.preventDefault();
  if ($(this).parent().hasClass('disabled')) return;
  currentPage = parseInt($(this).data('page')) || 1;
  renderPkgPage();
});

// 搜索过滤
$('#ad-pkg-search').on('keyup', function () {
  applyFilter();
});

// ======================== 执行部署（每行按钮） ========================
$(document).on('click', '.ad-deploy-btn', function () {
  if (!myHostReady) { msg('主机信息尚未加载完成，请稍候', 4); return; }
  var dname = $(this).data('name') || '';
  var title = $(this).data('title') || dname;
  if (!dname) { msg('软件包信息异常', 4); return; }
  if (!confirm('将部署「' + title + '」到您的主机，\n该主机网站目录现有内容可能被覆盖，是否继续？')) return;

  var btn = this;
  $(btn).prop('disabled', true).html('<i class="mdi mdi-cached mdi-spin"></i> 部署中...');
  if (typeof msloading === 'function') msloading('部署中...');

  function restoreBtn() {
    if (typeof msloadingde === 'function') msloadingde();
    $(btn).prop('disabled', false).html('<i class="mdi mdi-open-in-new"></i> 部署');
  }

  $.ajax({
    url: 'ajax.php',
    type: 'POST',
    data: {
      gn: 'p_auto_deploy_setup',
      dname: dname,
      project_type: 'php'
    },
    timeout: 40000,
    success: function (res) {
      restoreBtn();
      var d = parseRes(res);
      if (d.qk == 1 || d.success) {
        var html = '<p class="text-success"><strong>部署成功！</strong></p>';
        if (d.admin_username) html += '<p>管理员账号：<code>' + escHtml(d.admin_username) + '</code></p>';
        if (d.admin_password) html += '<p>管理员密码：<code>' + escHtml(d.admin_password) + '</code></p>';
        if (d.success_url) {
          html += '<p>访问入口：<a href="' + escHtml('http://' + d.success_url) + '" target="_blank">' + escHtml(d.success_url) + '</a></p>';
        } else {
          html += '<p class="text-warning small">该 CMS 软件需访问网站域名进入 Web 安装向导完成配置。</p>';
        }
        $('#ad-result-body').html(html);
        $('#ad-result-box').removeClass('border-danger').addClass('border-success').show();
        msg(d.msg || '部署成功', 1);
      } else {
        $('#ad-result-body').html('<p class="text-danger">' + escHtml(d.msg || '部署失败') + '</p>');
        $('#ad-result-box').removeClass('border-success').addClass('border-danger').show();
        msg(d.msg || '部署失败', 4);
      }
    },
    error: function (xhr, status) {
      restoreBtn();
      var tip = (status === 'timeout' ? '部署请求超时' : ('部署请求失败（HTTP ' + xhr.status + '）')) + '，请到「部署记录」查看是否已提交。';
      $('#ad-result-body').html('<p class="text-danger">' + escHtml(tip) + '</p>');
      $('#ad-result-box').removeClass('border-success').addClass('border-danger').show();
      msg(tip, 4, 4000);
    }
  });
});
</script>
