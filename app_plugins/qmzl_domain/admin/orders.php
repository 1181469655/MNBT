<?php
/**
 * qmzl_domain - 后台订单记录
 * 全部域名订单（client 模式上游订单 / agent 模式本地订单），支持失败重试。
 */
if (!defined('IN_CRONLITE')) exit;
mnbt_admin_include('head');
$mode = qmzl_mode();
?>
<div class="container-fluid p-t-15">
  <div class="card">
    <header class="card-header"><div class="card-title">启明智联域名 - 订单记录</div></header>
    <div class="card-body">
      <div class="callout callout-info">
        <p class="small">
          当前模式：<strong><?= $mode === 'agent' ? '代理商模式' : '客户自注册' ?></strong>。
          <?= $mode === 'agent' ? '代理商模式下，支付成功且上游余额支付成功的订单显示「已支付」；余额不足的订单显示「失败」，可在补足余额后点击「重试注册」。' : '客户自注册模式下，订单支付发生在启明智联平台，本表记录下单与支付状态。' ?>
        </p>
      </div>
      <div id="toolbar" class="toolbar-btn-action">
        <div class="form-inline">
          <select class="form-control" id="f_status" style="width:120px;margin-right:8px;">
            <option value="">全部状态</option>
            <option value="Pending">待支付</option>
            <option value="Paid">已支付</option>
            <option value="Failed">失败</option>
            <option value="Cancelled">已取消</option>
          </select>
          <input type="text" class="form-control" id="f_keyword" placeholder="域名 / 用户 / 订单号" style="width:220px;margin-right:8px;">
          <button type="button" class="btn btn-primary" onclick="loadList(1)">
            <span class="mdi mdi-magnify"></span> 查询
          </button>
        </div>
      </div>
      <table id="tb_orders" data-page-size="20"></table>
    </div>
  </div>
</div>

<script type="text/javascript" src="<?=mnbt_asset_url('js/bootstrap-table/bootstrap-table.min.js')?>"></script>
<script type="text/javascript" src="<?=mnbt_asset_url('js/bootstrap-table/locale/bootstrap-table-zh-CN.min.js')?>"></script>

<script type="text/javascript">
var $table = $("#tb_orders");
var QZ_MODE = '<?= $mode ?>';

function loadList(page) {
  msloading('加载中...');
  $.post('ajax.php', {
    gn: 'p_qmzl_admin_orders',
    page: page || 1,
    limit: 20,
    status: $('#f_status').val() || '',
    keyword: $('#f_keyword').val() || ''
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

function retryOrder(id) {
  if (!confirm('确定重新为该订单执行上游注册？（将再次加入购物车、结算并用代理商余额支付）')) return;
  msloading('处理中...');
  $.post('ajax.php', { gn: 'p_qmzl_admin_order_retry', id: id }, function (date) {
    msloadingde();
    var jsoe = typeof date === 'string' ? JSON.parse(date) : date;
    if (jsoe.qk === 1) { msalert(1, jsoe.msg || '重试成功', 4000); loadList(1); }
    else { msalert(3, jsoe.msg || '重试失败', 4000); }
  }).fail(function () { msloadingde(); msalert(3, '网络错误', 4000); });
}

$(function () {
  $table.bootstrapTable({
    columns: [
      { field: 'id', title: 'ID', width: 60, align: 'center' },
      { field: 'username', title: '用户', width: 100 },
      { field: 'domain', title: '域名' },
      { field: 'year', title: '年限', width: 70, align: 'center', formatter: function (v) { return v + ' 年'; } },
      { field: 'amount', title: '金额', width: 90, align: 'center', formatter: function (v) { return '¥' + (v || '0.00'); } },
      { field: 'gateway', title: '支付方式', width: 130, formatter: function (v) { return v || '-'; } },
      {
        field: 'status', title: '状态', width: 90, align: 'center',
        formatter: function (v) {
          var map = { Pending: ['待支付', 'warning'], Paid: ['已支付', 'success'], Failed: ['失败', 'danger'], Cancelled: ['已取消', 'default'] };
          var m = map[v] || [v || '未知', 'default'];
          return '<span class="label label-' + m[1] + '">' + m[0] + '</span>';
        }
      },
      { field: 'remark', title: '备注', formatter: function (v) { return v || '-'; } },
      { field: 'ddh', title: '本站订单号', formatter: function (v) { return v || '-'; } },
      { field: 'cloud_order_id', title: '上游订单号', formatter: function (v) { return v || '-'; } },
      { field: 'created_at', title: '下单时间', width: 150 },
      {
        field: 'op', title: '操作', width: 110, align: 'center', formatter: function (v, r) {
          if (QZ_MODE === 'agent' && r.status === 'Failed') {
            return '<button type="button" class="btn btn-xs btn-danger" onclick="retryOrder(' + (parseInt(r.id, 10) || 0) + ')">重试注册</button>';
          }
          return '<span class="text-muted">-</span>';
        }
      }
    ],
    pagination: true,
    sidePagination: 'client',
    pageSize: 20,
    pageList: [20, 50, 100],
    showRefresh: false
  });
  loadList(1);
});
</script>
</body>
</html>
