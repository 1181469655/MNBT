<?php
if (!defined('IN_CRONLITE')) {
	exit;
}
mnbt_admin_include('head');
?>
<style>
.ad-loading { display: none; text-align: center; padding: 40px; }
.ad-loading .spinner-border { width: 3rem; height: 3rem; }
.ad-status-badge { font-size: .85rem; }
.ad-status-ok { color: #28a745; }
.ad-status-fail { color: #dc3545; }
.ad-log-box { max-height: 320px; overflow: auto; background: #1e1e1e; color: #d4d4d4; padding: 12px; border-radius: 4px; font-family: 'Courier New', monospace; font-size: .85rem; white-space: pre-wrap; word-break: break-all; }
.ad-deploy-result { display: none; }
.ad-pkg-card { cursor: pointer; border: 2px solid transparent; transition: border-color .2s; }
.ad-pkg-card:hover { border-color: #007bff; }
.ad-pkg-card.selected { border-color: #007bff; background-color: rgba(0,123,255,.05); }
.ad-env-table td:first-child { font-weight: 500; width: 140px; }
</style>

<div class="container-fluid p-t-15">
  <div class="card">
    <div class="card-header">
      <h4 class="mb-0"><i class="mdi mdi-rocket-launch text-primary"></i> 高级自动部署</h4>
    </div>
    <div class="card-body">
      <!-- 节点选择 -->
      <div class="row mb-3">
        <div class="col-md-4">
          <label class="font-weight-bold">目标节点</label>
          <select id="ad-node" class="form-control">
            <option value="">-- 加载中 --</option>
          </select>
        </div>
        <div class="col-md-2 d-flex align-items-end">
          <button type="button" class="btn btn-outline-secondary btn-block" id="btn-refresh-nodes">刷新节点</button>
        </div>
      </div>

      <!-- 标签页 -->
      <ul class="nav nav-tabs" id="ad-tabs" role="tablist">
        <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#tab-deploy" role="tab">一键部署</a></li>
        <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab-custom" role="tab">自定义包管理</a></li>
        <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab-env" role="tab">环境检测</a></li>
        <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab-progress" role="tab">进度与日志</a></li>
      </ul>

      <div class="tab-content p-t-15">
        <!-- ===== Tab 1：一键部署 ===== -->
        <div class="tab-pane fade show active" id="tab-deploy" role="tabpanel">
          <div class="row">
            <!-- 左：软件包列表 -->
            <div class="col-md-6">
              <h5>1. 选择软件包</h5>
              <div class="input-group mb-2">
                <input type="text" class="form-control form-control-sm" id="ad-pkg-search" placeholder="搜索软件包...">
                <div class="input-group-append"><button class="btn btn-sm btn-outline-secondary" id="btn-load-packages">加载列表</button></div>
              </div>
              <div id="ad-packages-loading" class="ad-loading"><div class="spinner-border text-primary"></div><p>加载中...</p></div>
              <div id="ad-packages-list" style="max-height:420px;overflow:auto;">
                <p class="text-muted">请先选择节点并点击"加载列表"</p>
              </div>
            </div>
            <!-- 右：目标网站 + 执行部署 -->
            <div class="col-md-6">
              <h5>2. 选择目标网站</h5>
              <div class="input-group mb-2">
                <select id="ad-site" class="form-control form-control-sm">
                  <option value="">-- 请先加载网站列表 --</option>
                </select>
                <div class="input-group-append"><button class="btn btn-sm btn-outline-secondary" id="btn-load-sites">加载网站</button></div>
              </div>
              <div id="ad-sites-loading" class="ad-loading"><div class="spinner-border text-primary"></div><p>加载中...</p></div>

              <div class="mt-3">
                <h5>3. 执行部署</h5>
                <p class="text-muted small mb-2">
                  已选软件包：<strong id="ad-selected-pkg" class="text-primary">未选择</strong><br>
                  已选网站：<strong id="ad-selected-site" class="text-info">未选择</strong>
                </p>
                <button type="button" class="btn btn-primary btn-lg btn-block" id="btn-deploy" disabled>
                  <i class="mdi mdi-rocket"></i> 立即部署
                </button>
              </div>

              <!-- 部署结果 -->
              <div class="ad-deploy-result mt-3 card border-success" id="ad-result-box">
                <div class="card-header bg-success text-white">部署结果</div>
                <div class="card-body" id="ad-result-body"></div>
              </div>
            </div>
          </div>
        </div>

        <!-- ===== Tab 2：自定义包管理 ===== -->
        <div class="tab-pane fade" id="tab-custom" role="tabpanel">
          <div class="row">
            <div class="col-md-7">
              <h5>自定义包列表</h5>
              <div class="mb-2"><button class="btn btn-sm btn-outline-primary" id="btn-load-custom">加载自定义包</button></div>
              <div id="ad-custom-loading" class="ad-loading"><div class="spinner-border text-primary"></div><p>加载中...</p></div>
              <div id="ad-custom-list"><p class="text-muted">请先选择节点并点击"加载自定义包"</p></div>
            </div>
            <div class="col-md-5">
              <h5>添加自定义包</h5>
              <div class="form-group">
                <label>项目类型</label>
                <select id="ad-cp-type" class="form-control form-control-sm">
                  <option value="php">PHP 项目</option>
                  <option value="java">Java 项目</option>
                </select>
              </div>
              <div class="form-group"><label>英文名称 *</label><input type="text" class="form-control form-control-sm" id="ad-cp-name" placeholder="如 my_cms_v2" maxlength="100"></div>
              <div class="form-group"><label>显示标题 *</label><input type="text" class="form-control form-control-sm" id="ad-cp-title" placeholder="如 我的CMS V2" maxlength="200"></div>
              <div class="form-group"><label>版本号 *</label><input type="text" class="form-control form-control-sm" id="ad-cp-version" placeholder="如 1.0.0" maxlength="50"></div>
              <div id="ad-cp-php-fields">
                <div class="form-group"><label>PHP 版本</label><input type="text" class="form-control form-control-sm" id="ad-cp-php" placeholder="如 74" maxlength="10"></div>
                <div class="form-group"><label>解禁函数（逗号分隔）</label><input type="text" class="form-control form-control-sm" id="ad-cp-funcs" placeholder="如 exec,system,popen" maxlength="500"></div>
              </div>
              <div id="ad-cp-java-fields" style="display:none;">
                <div class="form-group"><label>JDK 版本</label><input type="text" class="form-control form-control-sm" id="ad-cp-java" placeholder="如 8" maxlength="10"></div>
                <div class="form-group"><label>MySQL 版本</label><input type="text" class="form-control form-control-sm" id="ad-cp-mysql" placeholder="如 5.7" maxlength="10"></div>
              </div>
              <button class="btn btn-primary btn-sm" id="btn-add-custom">添加自定义包</button>
              <div class="mt-3">
                <h5>删除已部署项目</h5>
                <div class="form-group"><label>部署名称</label><input type="text" class="form-control form-control-sm" id="ad-del-dname" placeholder="如 ThinkPHP-5.0" maxlength="200"></div>
                <div class="form-group"><label>网站名称</label><input type="text" class="form-control form-control-sm" id="ad-del-site" placeholder="如 test.example.com" maxlength="200"></div>
                <button class="btn btn-danger btn-sm" id="btn-del-deploy">删除部署</button>
              </div>
            </div>
          </div>
        </div>

        <!-- ===== Tab 3：环境检测 ===== -->
        <div class="tab-pane fade" id="tab-env" role="tabpanel">
          <button class="btn btn-primary btn-sm mb-3" id="btn-check-env">开始检测</button>
          <div id="ad-env-loading" class="ad-loading"><div class="spinner-border text-primary"></div><p>检测中...</p></div>
          <div id="ad-env-result" style="display:none;">
            <table class="table table-bordered table-sm ad-env-table">
              <tbody id="ad-env-tbody"></tbody>
            </table>
          </div>
        </div>

        <!-- ===== Tab 4：进度与日志 ===== -->
        <div class="tab-pane fade" id="tab-progress" role="tabpanel">
          <div class="row">
            <div class="col-md-6">
              <h5>部署进度</h5>
              <button class="btn btn-outline-primary btn-sm mb-2" id="btn-check-speed">查询进度</button>
              <div id="ad-speed-result"><p class="text-muted">点击查询按钮获取进度信息</p></div>
            </div>
            <div class="col-md-6">
              <h5>面板部署日志</h5>
              <button class="btn btn-outline-info btn-sm mb-2" id="btn-check-log">查询日志</button>
              <div id="ad-btlog-result"><p class="text-muted">点击查询按钮获取面板日志</p></div>
            </div>
          </div>
        </div>
      </div><!-- /tab-content -->
    </div><!-- /card-body -->
  </div><!-- /card -->
</div>

<script>
// ======================== 工具函数 ========================
function parseRes(res) {
  try { return typeof res === 'string' ? JSON.parse(res) : res; } catch (e) { return {code: res}; }
}
function notify(msg, type) {
  type = type || ((typeof (d && d.qk) !== 'undefined' && d.qk == 1) ? 'success' : 'danger');
  if (typeof $.notify === 'function') $.notify({message: msg}, {type: type});
  else alert(msg);
}
function getNodeId() {
  var v = $('#ad-node').val();
  return v ? parseInt(v) : 0;
}
function showLoading(elId) { $('#' + elId).show(); }
function hideLoading(elId) { $('#' + elId).hide(); }

// ======================== 节点加载 ========================
function loadNodes() {
  $.post('ajax.php', {gn: 'p_auto_deploy_nodes'}, function (res) {
    var d = parseRes(res);
    var sel = $('#ad-node');
    sel.empty();
    if (d.nodes && d.nodes.length > 0) {
      $.each(d.nodes, function (i, n) {
        sel.append('<option value="' + n.id + '">[' + (n.btdh || n.id) + '] ' + n.btip + ':' + n.btdk + '</option>');
      });
    } else {
      sel.append('<option value="">-- 无可用节点 --</option>');
    }
  });
}
$('#btn-refresh-nodes').on('click', loadNodes);
loadNodes();

// ======================== Tab 1：一键部署 ========================
$('#btn-load-packages').on('click', function () {
  var nodeId = getNodeId();
  if (!nodeId) { alert('请先选择节点'); return; }
  showLoading('ad-packages-loading');
  $('#ad-packages-list').empty();
  $.post('ajax.php', {gn: 'p_auto_deploy_packages', node_id: nodeId}, function (res) {
    hideLoading('ad-packages-loading');
    var d = parseRes(res);
    if (d.qk != 1 && !d.success) { notify(d.msg || '加载失败', 'danger'); return; }
    var pkgs = d.packages || [];
    if (!pkgs.length) { $('#ad-packages-list').html('<p class="text-muted">该节点无可部署的软件包</p>'); return; }
    var html = '';
    $.each(pkgs, function (i, p) {
      html += '<div class="ad-pkg-card card mb-1 p-2" data-name="' + (p.name || '') + '" data-title="' + (p.title || '') + '">';
      html += '<div class="d-flex justify-content-between align-items-center">';
      html += '<span><strong>' + escHtml(p.title || p.name) + '</strong> <small class="text-muted">v' + escHtml(p.version || '') + '</small></span>';
      html += '<small class="text-muted">' + escHtml(p.author || '') + (p.price > 0 ? ' <span class="text-warning">&yen;' + p.price + '</span>' : ' <span class="text-success">免费</span>') + '</small>';
      html += '</div></div>';
    });
    $('#ad-packages-list').html(html);
  });
});

$(document).on('click', '.ad-pkg-card', function () {
  $('.ad-pkg-card').removeClass('selected');
  $(this).addClass('selected');
  $('#ad-selected-pkg').text($(this).data('title') || $(this).data('name'));
  updateDeployBtn();
});

$('#btn-load-sites').on('click', function () {
  var nodeId = getNodeId();
  if (!nodeId) { alert('请先选择节点'); return; }
  showLoading('ad-sites-loading');
  $.post('ajax.php', {gn: 'p_auto_deploy_sites', node_id: nodeId}, function (res) {
    hideLoading('ad-sites-loading');
    var d = parseRes(res);
    if (d.qk != 1 && !d.success) { notify(d.msg || '加载失败', 'danger'); return; }
    var sites = d.sites || [];
    var sel = $('#ad-site');
    sel.empty();
    sel.append('<option value="">-- 选择网站 --</option>');
    $.each(sites, function (i, s) {
      sel.append('<option value="' + (s.name || '') + '">' + escHtml(s.title || s.name) + ' v' + escHtml(s.version || '') + '</option>');
    });
  });
});

$('#ad-site').on('change', function () {
  $('#ad-selected-site').text($(this).val() || '未选择');
  updateDeployBtn();
});

function updateDeployBtn() {
  var pkgSel = $('.ad-pkg-card.selected');
  var siteSel = $('#ad-site').val();
  $('#btn-deploy').prop('disabled', !(pkgSel.length && siteSel));
}

$('#btn-deploy').on('click', function () {
  var nodeId = getNodeId();
  var dname = $('.ad-pkg-card.selected').data('name') || '';
  var siteName = $('#ad-site').val();
  if (!dname || !siteName) { alert('请选择软件包和目标网站'); return; }
  $(this).prop('disabled', true).html('<i class="mdi mdi-loading mdi-spin"></i> 部署中...');

  $.post('ajax.php', {
    gn: 'p_auto_deploy_setup',
    node_id: nodeId,
    dname: dname,
    site_name: siteName,
    project_type: 'php'
  }, function (res) {
    var d = parseRes(res);
    $('#btn-deploy').prop('disabled', false).html('<i class="mdi mdi-rocket"></i> 立即部署');
    updateDeployBtn();
    if (d.qk == 1 || d.success) {
      var html = '<p class="text-success"><strong>部署成功！</strong></p>';
      if (d.admin_username) html += '<p>管理员账号：<code>' + escHtml(d.admin_username) + '</code></p>';
      if (d.admin_password) html += '<p>管理员密码：<code>' + escHtml(d.admin_password) + '</code></p>';
      if (d.success_url) {
        html += '<p>' + escHtml(d.success_url) + '</p>';
      } else {
        html += '<p class="text-warning small">该 CMS 包需访问网站域名进入 Web 安装向导完成配置。</p>';
      }
      $('#ad-result-body').html(html);
      $('#ad-result-box').removeClass('border-success').addClass('border-success').show();
      notify(d.msg || '部署成功', 'success');
    } else {
      $('#ad-result-body').html('<p class="text-danger">' + escHtml(d.msg || '部署失败') + '</p>');
      $('#ad-result-box').removeClass('border-success').addClass('border-danger').show();
      notify(d.msg || '部署失败', 'danger');
    }
  });
});

// 搜索过滤
$('#ad-pkg-search').on('keyup', function () {
  var kw = $(this).val().toLowerCase();
  $('.ad-pkg-card').each(function () {
    var t = ($(this).data('title') + ' ' + $(this).data('name')).toLowerCase();
    $(this).toggle(t.indexOf(kw) !== -1);
  });
});

// ======================== Tab 2：自定义包管理 ========================
$('#ad-cp-type').on('change', function () {
  var v = $(this).val();
  $('#ad-cp-php-fields').toggle(v === 'php');
  $('#ad-cp-java-fields').toggle(v === 'java');
});

$('#btn-load-custom').on('click', function () {
  var nodeId = getNodeId();
  if (!nodeId) { alert('请先选择节点'); return; }
  showLoading('ad-custom-loading');
  $('#ad-custom-list').empty();
  $.post('ajax.php', {gn: 'p_auto_deploy_custom_pkgs', node_id: nodeId}, function (res) {
    hideLoading('ad-custom-loading');
    var d = parseRes(res);
    if (d.qk != 1 && !d.success) { notify(d.msg || '加载失败', 'danger'); return; }
    var pkgs = d.custom_packages || [];
    if (!pkgs.length) { $('#ad-custom-list').html('<p class="text-muted">该节点无自定义软件包</p>'); return; }
    var html = '<table class="table table-sm table-bordered"><thead><tr><th>名称</th><th>标题</th><th>版本</th><th>类型</th><th>PHP</th></tr></thead><tbody>';
    $.each(pkgs, function (i, p) {
      html += '<tr><td><code>' + escHtml(p.name || '') + '</code></td><td>' + escHtml(p.title || '') + '</td><td>' + escHtml(p.version || '') + '</td><td>' + escHtml(p.project_type || 'php') + '</td><td>' + escHtml(p.php || '') + '</td></tr>';
    });
    html += '</tbody></table>';
    $('#ad-custom-list').html(html);
  });
});

$('#btn-add-custom').on('click', function () {
  var nodeId = getNodeId();
  if (!nodeId) { alert('请先选择节点'); return; }
  var pType = $('#ad-cp-type').val();
  var data = {
    gn: 'p_auto_deploy_add_pkg',
    node_id: nodeId,
    name: $('#ad-cp-name').val().trim(),
    title: $('#ad-cp-title').val().trim(),
    version: $('#ad-cp-version').val().trim(),
    project_type: pType
  };
  if (!data.name || !data.title || !data.version) { alert('英文名称、标题、版本号为必填'); return; }
  if (pType === 'php') {
    data.php = $('#ad-cp-php').val().trim();
    data.enable_functions = $('#ad-cp-funcs').val().trim();
  } else {
    data.java_version = $('#ad-cp-java').val().trim();
    data.mysql_version = $('#ad-cp-mysql').val().trim();
  }
  $.post('ajax.php', data, function (res) {
    var d = parseRes(res);
    notify(d.msg || '完成', d.qk == 1 || d.success ? 'success' : 'danger');
    if (d.qk == 1 || d.success) { $('#btn-load-custom').click(); }
  });
});

$('#btn-del-deploy').on('click', function () {
  var nodeId = getNodeId();
  if (!nodeId) { alert('请先选择节点'); return; }
  var dname = $('#ad-del-dname').val().trim();
  var siteName = $('#ad-del-site').val().trim();
  if (!dname || !siteName) { alert('请填写部署名称和网站名称'); return; }
  if (!confirm('确认删除部署「' + dname + ' @ ' + siteName + '」？')) return;
  $.post('ajax.php', {gn: 'p_auto_deploy_del', node_id: nodeId, dname: dname, site_name: siteName}, function (res) {
    var d = parseRes(res);
    notify(d.msg || '完成', d.qk == 1 || d.success ? 'success' : 'danger');
  });
});

// ======================== Tab 3：环境检测 ========================
$('#btn-check-env').on('click', function () {
  var nodeId = getNodeId();
  if (!nodeId) { alert('请先选择节点'); return; }
  showLoading('ad-env-loading');
  $('#ad-env-result').hide();
  $.post('ajax.php', {gn: 'p_auto_deploy_env', node_id: nodeId}, function (res) {
    hideLoading('ad-env-loading');
    var d = parseRes(res);
    if (d.qk != 1 && !d.success) { notify(d.msg || '检测失败', 'danger'); return; }
    var env = d.env || {};
    var rows = '';
    var items = [
      {k: 'mysql_version', label: 'MySQL 版本', icon: 'mdi-database'},
      {k: 'mysql', label: 'MySQL', icon: 'mdi-database'},
      {k: 'nginx', label: 'Nginx', icon: 'mdi-server'},
      {k: 'redis', label: 'Redis', icon: 'mdi-memory'},
      {k: 'ftp', label: 'FTP', icon: 'mdi-folder-upload'},
    ];
    $.each(items, function (i, item) {
      var v = env[item.k];
      var icon, badge;
      if (typeof v === 'boolean') {
        icon = v ? '<span class="ad-status-ok"><i class="mdi mdi-check-circle"></i></span>' : '<span class="ad-status-fail"><i class="mdi mdi-close-circle"></i></span>';
        badge = v ? '<span class="badge badge-success">可用</span>' : '<span class="badge badge-secondary">不可用</span>';
        rows += '<tr><td><i class="mdi ' + item.icon + '"></i> ' + item.label + '</td><td>' + icon + ' ' + badge + '</td></tr>';
      } else if (typeof v === 'string') {
        rows += '<tr><td><i class="mdi ' + item.icon + '"></i> ' + item.label + '</td><td><code>' + escHtml(v) + '</code> <span class="badge badge-success">已安装</span></td></tr>';
      } else {
        rows += '<tr><td><i class="mdi ' + item.icon + '"></i> ' + item.label + '</td><td><span class="badge badge-secondary">未知</span></td></tr>';
      }
    });
    // 额外字段
    $.each(env, function (k, v) {
      if (!items.find(function (x) { return x.k === k; })) {
        rows += '<tr><td>' + escHtml(k) + '</td><td><code>' + escHtml(JSON.stringify(v)) + '</code></td></tr>';
      }
    });
    $('#ad-env-tbody').html(rows);
    $('#ad-env-result').show();
  });
});

// ======================== Tab 4：进度与日志 ========================
$('#btn-check-speed').on('click', function () {
  var nodeId = getNodeId();
  if (!nodeId) { alert('请先选择节点'); return; }
  $.post('ajax.php', {gn: 'p_auto_deploy_speed', node_id: nodeId}, function (res) {
    var d = parseRes(res);
    if (d.qk == 1 || d.success) {
      var speed = d.speed;
      if (speed === null || speed === undefined || (typeof speed === 'object' && $.isEmptyObject(speed))) {
        $('#ad-speed-result').html('<p class="text-muted">当前无正在执行的部署任务</p>');
      } else {
        $('#ad-speed-result').html('<pre class="ad-log-box">' + escHtml(JSON.stringify(speed, null, 2)) + '</pre>');
      }
    } else {
      $('#ad-speed-result').html('<p class="text-danger">' + escHtml(d.msg || '查询失败') + '</p>');
    }
  });
});

$('#btn-check-log').on('click', function () {
  var nodeId = getNodeId();
  if (!nodeId) { alert('请先选择节点'); return; }
  $.post('ajax.php', {gn: 'p_auto_deploy_btlog', node_id: nodeId}, function (res) {
    var d = parseRes(res);
    if (d.qk == 1 || d.success) {
      var log = d.log || '';
      if (log === '' || log === null) {
        $('#ad-btlog-result').html('<p class="text-muted">暂无日志</p>');
      } else {
        $('#ad-btlog-result').html('<pre class="ad-log-box">' + escHtml(log) + '</pre>');
      }
    } else {
      $('#ad-btlog-result').html('<p class="text-danger">' + escHtml(d.msg || '查询失败') + '</p>');
    }
  });
});

// ======================== 工具：HTML 转义 ========================
function escHtml(str) {
  if (!str && str !== 0) return '';
  var div = document.createElement('div');
  div.appendChild(document.createTextNode(String(str)));
  return div.innerHTML;
}
</script>
