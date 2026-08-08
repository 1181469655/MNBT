<?php
/**
 * qmzl_domain - 后台用户云账号管理
 * 查看 / 解绑各用户绑定的启明智联平台账号。
 */
if (!defined('IN_CRONLITE')) exit;
mnbt_admin_include('head');
?>
<div class="container-fluid p-t-15">
  <div class="card">
    <header class="card-header"><div class="card-title">启明智联域名 - 用户云账号</div></header>
    <div class="card-body">
      <div class="callout callout-info">
        <p class="small">展示所有绑定启明智联平台账号的用户。账号密码加密存储于 <code>plg_qmzl_account</code>，此处仅用于排查与支持。</p>
      </div>
      <div id="toolbar" class="toolbar-btn-action">
        <div class="form-inline">
          <input type="text" class="form-control" id="search_keyword" placeholder="用户名 / 平台账号" style="width:220px;margin-right:8px;">
          <button type="button" class="btn btn-primary" onclick="loadList(1)">
            <span class="mdi mdi-magnify"></span> 查询
          </button>
        </div>
      </div>
      <table id="tb_accounts" data-page-size="10"></table>
    </div>
  </div>
</div>

<script type="text/javascript" src="<?=mnbt_asset_url('js/bootstrap-table/bootstrap-table.min.js')?>"></script>
<script type="text/javascript" src="<?=mnbt_asset_url('js/bootstrap-table/locale/bootstrap-table-zh-CN.min.js')?>"></script>

<script type="text/javascript">
var $table = $("#tb_accounts");

function loadList(page) {
  msloading('加载中...');
  $.post('ajax.php', {
    gn: 'p_qmzl_admin_accounts',
    page: 1,
    limit: 1000,
    keyword: $('#search_keyword').val() || ''
  }, function (date) {
    msloadingde();
    var jsoe = typeof date === 'string' ? JSON.parse(date) : date;
    if (jsoe.qk !== 1) { msalert(3, jsoe.msg || '加载失败', 4000); return; }
    var data = jsoe.data || {};
    $table.bootstrapTable('load', {
      total: data.total || 0,
      rows: data.rows || []
    });
  }).fail(function () { msloadingde(); msalert(3, '网络错误', 4000); });
}

function unbindAccount(userId, username) {
  if (!confirm('确定解绑用户 ' + username + ' 的启明智联账号？')) return;
  msloading('处理中...');
  $.post('ajax.php', { gn: 'p_qmzl_admin_account_unbind', user_id: userId }, function (date) {
    msloadingde();
    var jsoe = typeof date === 'string' ? JSON.parse(date) : date;
    if (jsoe.qk === 1) { msalert(1, '已解绑', 3000); loadList(1); }
    else { msalert(3, jsoe.msg || '解绑失败', 4000); }
  }).fail(function () { msloadingde(); msalert(3, '网络错误', 4000); });
}

$(function () {
  $table.bootstrapTable({
    columns: [
      { field: 'id', title: 'ID', width: 60, align: 'center' },
      { field: 'user_id', title: '用户ID', width: 80, align: 'center' },
      { field: 'username', title: '用户名' },
      { field: 'account', title: '平台账号' },
      {
        field: 'status', title: '状态', width: 90, align: 'center',
        formatter: function (v) {
          return v === 'error'
            ? '<span class="label label-danger">异常</span>'
            : '<span class="label label-success">正常</span>';
        }
      },
      { field: 'last_msg', title: '最近信息', formatter: function (v) { return v || '-'; } },
      { field: 'created_at', title: '绑定时间' },
      { field: 'updated_at', title: '更新时间' },
      {
        field: 'op', title: '操作', width: 100, align: 'center', formatter: function (v, r) {
          return '<button type="button" class="btn btn-xs btn-danger" onclick="unbindAccount(' + (parseInt(r.user_id, 10) || 0) + ',\'' + (r.username || '').replace(/'/g, '') + '\')">解绑</button>';
        }
      }
    ],
    pagination: true,
    sidePagination: 'client',
    pageSize: 10,
    pageList: [10, 20, 50],
    showRefresh: false
  });
  loadList(1);
});
</script>
</body>
</html>
