<?php
/**
 * 后台 - 域名商品列表
 * 迁移自 templates/default/admin/list.php 的 $set=='ym' 分支
 * AJAX gn：p_domain_listym / p_domain_ymscxz / p_domain_xgym / p_domain_ymsc
 */
if (!defined('IN_CRONLITE')) exit;
mnbt_admin_include('head');
global $DB;
$providers = $DB->get_all_prepare("SELECT * FROM plg_dns_provider WHERE qk='true' order by id asc") ?: [];
$providerMap = [];
foreach ($providers as $p) $providerMap[(int)$p['id']] = $p;
?>
<script type="text/javascript" src="<?=mnbt_asset_url('js/bootstrap-table/bootstrap-table.min.js')?>"></script>
<script type="text/javascript" src="<?=mnbt_asset_url('js/bootstrap-table/locale/bootstrap-table-zh-CN.min.js')?>"></script>
<div class="container-fluid p-t-15">

  <!-- 编辑弹窗 -->
  <div class="modal fade" id="tanchuang" tabindex="-1" role="dialog" aria-labelledby="tanchuang" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h6 class="modal-title">编辑域名商品</h6>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </div>
        <div class="modal-body">
          <form>
            <input type="hidden" name="idr" id="idr">
            <div class="form-group">
              <label class="control-label">域名介绍：</label>
              <input type="text" class="form-control" id="recipientname">
            </div>
            <div class="form-group">
              <label class="control-label">解析价格：</label>
              <input type="number" class="form-control" id="messagetext">
            </div>
            <div class="form-group">
              <label class="control-label">解析通道：</label>
              <select class="form-control" id="edt_channel" onchange="toggleEditChannel()">
                <option value="pan">泛解析</option>
                <option value="dnsapi" <?= empty($providers) ? 'disabled' : '' ?>>DNS API</option>
              </select>
            </div>
            <div class="form-group" id="edt_provider_group" style="display:none;">
              <label class="control-label">DNS 服务商：</label>
              <select class="form-control" id="edt_provider_id">
                <option value="0">请选择服务商</option>
                <?php foreach ($providers as $p): ?>
                  <option value="<?= (int)$p['id'] ?>"><?= htmlspecialchars($p['name']) ?>（<?= htmlspecialchars($p['slug']) ?>）</option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-group">
              <label class="btn-block">是否上架</label>
              <div class="col-xs-6">
                <div class="custom-control custom-switch custom-info">
                  <input type="checkbox" class="custom-control-input" name="ymkg" id="ymkg" checked>
                  <label class="custom-control-label" for="ymkg"></label>
                </div>
              </div>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
          <button type="button" class="btn btn-primary" onclick="bj_bc()">确认保存</button>
        </div>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-lg-12">
      <div class="card">
        <header class="card-header"><div class="card-title">域名商品列表</div></header>
        <div class="card-body">
          <div class="callout callout-info">
            <p class="small">
              <strong>操作图标详解</strong><br/>
              <a href="#!" class="btn btn-xs btn-default" title="编辑" data-toggle="tooltip"><i class="mdi mdi-pencil"></i></a>：编辑纪录
              <a href="#!" class="btn btn-xs btn-default" title="删除" data-toggle="tooltip"><i class="mdi mdi-window-close"></i></a>：删除纪录
            </p>
          </div>
          <div id="toolbar" class="toolbar-btn-action">
            <button id="btn_add" type="button" class="btn btn-primary m-r-5 js-create-tab" data-title="添加域名商品" data-url="plugin.php?p=domain_shop&page=product_add">
              <span class="mdi mdi-plus"></span>新增域名
            </button>
            <button id="btn_delete" type="button" class="btn btn-danger" onclick="xzdelbt()">
              <span class="mdi mdi-window-close"></span>删除选中
            </button>
          </div>
          <table id="tb_departments"></table>
        </div>
      </div>
    </div>
  </div>
</div>

<script type="text/javascript">
function hqxzh() {
  var selRows = $("#tb_departments").bootstrapTable("getSelections");
  if (selRows.length == 0) { msalert(3, "请至少选择一行", 4000); return false; }
  var arr = [];
  $.each(selRows, function (i) { arr.push(this.id); });
  return arr;
}

function xzdelbt() {
  if (!confirm('删除后不可恢复\n是否确认删除这些域名？')) return;
  msloading('正在删除中，请稍后...');
  var arr = hqxzh();
  if (arr == false) { msloadingde(); return; }
  $.post('ajax.php', { gn: 'p_domain_ymscxz', idsz: arr }, function (date) {
    var jsoe = JSON.parse(date);
    var qk = jsoe.codr, qke = jsoe.code;
    msloadingde();
    msalert(1, '删除成功 ' + qke + ' 条' + (qk > 0 ? '，失败 ' + qk + ' 条' : ''), 5000);
    $("#tb_departments").bootstrapTable('refreshOptions', { pageNumber: 1 });
  });
}

function toggleEditChannel() {
  var ch = edt_channel.value;
  edt_provider_group.style.display = (ch === 'dnsapi') ? '' : 'none';
}

function bj_bc() {
  var id = idr.value, ipe = recipientname.value, dke = messagetext.value, kge = ymkg.checked;
  var ch = edt_channel.value, pid = edt_provider_id.value;
  if (ipe == "" || dke == "") { msalert(3, '表单不能为空！', 2000, '#tanchuang'); return; }
  if (ch === 'dnsapi' && (pid === '0' || pid === '')) {
    msalert(3, 'DNS API 通道必须选择 DNS 服务商！', 2000, '#tanchuang'); return;
  }
  msloading('正在加载中');
  $.post('ajax.php', {
    gn: 'p_domain_xgym', id: id, js: ipe, jg: dke, kg: kge,
    channel: ch, provider_id: pid
  }, function (date) {
    var jsoe = JSON.parse(date);
    var qk = jsoe.code;
    if (qk == '修改成功') {
      $("#tb_departments").bootstrapTable('refreshOptions', { pageNumber: 1 });
      $('#tanchuang').modal('hide');
      msalert(1, '修改成功！', 3000);
    } else {
      msalert(4, qk, 2000, '#tanchuang');
    }
    msloadingde();
  });
}

$('#tb_departments').bootstrapTable({
  classes: 'table table-bordered table-hover table-striped',
  url: 'ajax.php',
  method: 'post',
  contentType: "application/x-www-form-urlencoded",
  dataType: 'json',
  uniqueId: 'id',
  idField: 'id',
  toolbar: '#toolbar',
  showColumns: true,
  showRefresh: true,
  showToggle: true,
  pagination: true,
  sortOrder: "asc",
  sortName: "id",
  queryParams: function (params) {
    return {
      gn: 'p_domain_listym',
      limit: params.limit,
      offset: params.offset,
      page: (params.offset / params.limit) + 1,
      sort: params.sort,
      sortOrder: params.order
    };
  },
  sidePagination: "server",
  pageNumber: 1,
  pageSize: 10,
  pageList: [10, 25, 50, 100],
  showExport: true,
  exportDataType: "basic",
  columns: [{
    field: 'example', checkbox: true
  }, {
    field: 'id', title: 'ID', sortable: true
  }, {
    field: 'url', title: '域名'
  }, {
    field: 'btdh', title: '绑定的宝塔', sortable: true
  }, {
    field: 'channel', title: '通道', sortable: true,
    formatter: function (value, row) {
      if (value === 'dnsapi') {
        var pName = row.provider_id > 0 && window._providerNames && window._providerNames[row.provider_id]
          ? window._providerNames[row.provider_id] : ('#' + row.provider_id);
        return '<span class="badge badge-info"><b>DNS API</b></span> <small>' + escapeHtml(pName) + '</small>';
      }
      return '<span class="badge badge-secondary"><b>泛解析</b></span>';
    }
  }, {
    field: 'jg', title: '价格', sortable: true
  }, {
    field: 'date', title: '添加时间', sortable: true
  }, {
    field: 'gzcs', title: '购置次数', sortable: true,
    formatter: function (value, row) {
      var obj = JSON.parse(row.json);
      var size = 0, key;
      for (key in obj) { if (obj.hasOwnProperty(key)) size++; }
      return size;
    }
  }, {
    field: 'js', title: '域名介绍'
  }, {
    field: 'qk', title: '情况',
    formatter: function (value) {
      if (value == 'false') return '<span class="badge badge-danger"><b>下架</b></span>';
      if (value == 'true') return '<span class="badge badge-success"><b>上架</b></span>';
      return '<span class="badge badge-danger">未知状态</span>';
    }
  }, {
    field: 'operate', title: '操作', formatter: btnGroup,
    events: {
      'click .edit-btn': function (e, v, row) { editUser(row); },
      'click .del-btn': function (e, v, row) { delUser(row); }
    }
  }],
  onLoadSuccess: function () { $("[data-toggle='tooltip']").tooltip(); }
});

function btnGroup() {
  return '<a href="#!" class="btn btn-xs btn-default edit-btn" title="编辑" data-toggle="tooltip"><i class="mdi mdi-pencil"></i></a>' +
         '<a href="#!" class="btn btn-xs btn-default del-btn" title="删除" data-toggle="tooltip"><i class="mdi mdi-window-close"></i></a>';
}

function editUser(row) {
  document.getElementById("recipientname").value = row.js;
  document.getElementById("messagetext").value = row.jg;
  document.getElementById("idr").value = row.id;
  document.getElementById("ymkg").checked = (row.qk != 'false');
  var ch = row.channel === 'dnsapi' ? 'dnsapi' : 'pan';
  document.getElementById("edt_channel").value = ch;
  document.getElementById("edt_provider_id").value = row.provider_id > 0 ? row.provider_id : '0';
  toggleEditChannel();
  $('#tanchuang').modal();
}

function delUser(row) {
  if (!confirm('删除后不可恢复\n是否确认删除该域名？')) return;
  msloading('正在删除中，请稍后...');
  $.post('ajax.php', { gn: 'p_domain_ymsc', id: row.id }, function (date) {
    var jsoe = JSON.parse(date);
    msloadingde();
    msalert(jsoe.code == '删除成功' ? 1 : 4, jsoe.code, 3000);
    $("#tb_departments").bootstrapTable('refreshOptions', { pageNumber: 1 });
  });
}

// 服务商 ID -> 显示名映射，供列表通道列展示
window._providerNames = {
<?php foreach ($providerMap as $id => $p): ?>
  <?= (int)$id ?>: <?= json_encode(htmlspecialchars($p['name'] . '（' . $p['slug'] . ')')) ?>,
<?php endforeach; ?>
};

function escapeHtml(s) {
  return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
    return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
  });
}
</script>
