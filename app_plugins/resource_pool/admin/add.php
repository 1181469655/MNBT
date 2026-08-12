<?php
/**
 * 管理员端 - 添加 / 编辑资源池
 * URL: admin/plugin.php?p=resource_pool&page=add[&id=N]
 */
if (!defined('IN_CRONLITE')) {
	exit;
}

rp_ensure_schema();

$edit_id = (int)($_GET['id'] ?? 0);
$pool    = $edit_id > 0 ? rp_get($edit_id) : null;
$is_edit = $pool !== null;

$nodes         = rp_all_nodes();
$checked_nodes = $is_edit ? rp_decode_nodes($pool['nodes'] ?? '') : [];
$statuses      = rp_statuses();

$title = $is_edit ? '编辑资源池' : '添加资源池';
mnbt_admin_include('head');
?>
<link href="<?= mnbt_asset_url('js/bootstrap-datepicker/bootstrap-datepicker3.min.css') ?>" rel="stylesheet">

<div class="container-fluid p-t-15">
  <div class="row">
    <div class="col-lg-12">
      <div class="card">
        <header class="card-header">
          <div class="card-title"><?= $is_edit ? '编辑资源池 #' . (int)$pool['id'] : '添加资源池' ?></div>
        </header>
        <div class="card-body">

          <?php if ($edit_id > 0 && !$is_edit): ?>
            <div class="alert alert-danger">资源池不存在（ID <?= $edit_id ?>），请返回<a href="plugin.php?p=resource_pool&page=list">资源池列表</a>。</div>
          <?php else: ?>

          <input type="hidden" id="rp_id" value="<?= $is_edit ? (int)$pool['id'] : 0 ?>">

          <div class="form-group">
            <label><b>资源池名</b></label>
            <input type="text" class="form-control" id="rp_name" maxlength="120" placeholder="例如：华东代理A"
                   value="<?= $is_edit ? htmlspecialchars($pool['name'], ENT_QUOTES, 'UTF-8') : '' ?>">
            <small class="text-muted">用于识别该资源池的名称</small>
          </div><br/>

          <div class="form-group">
            <label><b>用户名</b></label>
            <input type="text" class="form-control" id="rp_username" maxlength="120" placeholder="字母/数字/下划线/横线，4-120 位"
                   value="<?= $is_edit ? htmlspecialchars($pool['username'], ENT_QUOTES, 'UTF-8') : '' ?>">
            <small class="text-muted">资源池登录账号，全局唯一</small>
          </div><br/>

          <div class="form-group">
            <label><b>密码</b></label>
            <div class="input-group">
              <input type="text" class="form-control" id="rp_password" maxlength="255"
                     placeholder="<?= $is_edit ? '留空表示不修改密码' : '不少于 6 位' ?>">
              <div class="input-group-btn">
                <button class="btn btn-default" type="button" onclick="rpGenPass()">随机生成</button>
              </div>
            </div>
            <small class="text-muted"><?= $is_edit ? '留空表示保持原密码不变' : '不少于 6 位' ?></small>
          </div><br/>

          <div class="form-group">
            <label><b>可用节点</b></label>
            <?php if (empty($nodes)): ?>
              <div class="alert alert-warning mb-0">还没有任何宝塔节点，请先到「宝塔管理 → 添加宝塔」添加节点。</div>
            <?php else: ?>
              <div class="row">
                <?php foreach ($nodes as $n): ?>
                  <div class="col-md-4">
                    <label class="lyear-checkbox checkbox-primary">
                      <input type="checkbox" class="rp-node" value="<?= htmlspecialchars($n['btdh'], ENT_QUOTES, 'UTF-8') ?>"
                        <?= in_array((string)$n['btdh'], $checked_nodes, true) ? 'checked' : '' ?>>
                      <span><?= htmlspecialchars($n['btdh'], ENT_QUOTES, 'UTF-8') ?></span>
                      <small class="text-muted">(<?= htmlspecialchars($n['btip'], ENT_QUOTES, 'UTF-8') ?> / <?= ((string)$n['btos'] === '1' ? 'Linux' : 'Windows') ?>)</small>
                    </label>
                  </div>
                <?php endforeach; ?>
              </div>
              <small class="text-muted">全部不勾选表示不限制节点（该资源池可用所有节点）</small>
            <?php endif; ?>
          </div><br/>

          <div class="form-group">
            <label><b>网页空间(MB)</b></label>
            <input type="number" min="0" class="form-control" id="rp_web" placeholder="该资源池可分配的网页空间总量，0 表示不限"
                   value="<?= $is_edit ? (int)$pool['web_space'] : 0 ?>">
          </div><br/>

          <div class="form-group">
            <label><b>数据库空间(MB)</b></label>
            <input type="number" min="0" class="form-control" id="rp_sql" placeholder="该资源池可分配的数据库空间总量，0 表示不限"
                   value="<?= $is_edit ? (int)$pool['sql_space'] : 0 ?>">
          </div><br/>

          <div class="form-group">
            <label><b>流量(G/月)</b></label>
            <input type="number" min="0" class="form-control" id="rp_flow" placeholder="该资源池可分配的月流量总量，0 表示不限"
                   value="<?= $is_edit ? (int)$pool['flow'] : 0 ?>">
          </div><br/>

          <div class="form-group">
            <label><b>到期日期</b>（点击即可选择）</label>
            <input class="form-control js-datepicker m-b-10" type="text" id="rp_expire" placeholder="yyyy-mm-dd"
                   data-provide="datepicker" readonly="true" data-date-format="yyyy-mm-dd" style="background-color:#FFFFFF;"
                   value="<?= $is_edit ? htmlspecialchars($pool['expire_date'], ENT_QUOTES, 'UTF-8') : '' ?>">
            <small class="text-muted">留空表示永不到期。到期后该资源池不能再开通主机（已开通主机不受影响）</small>
            <div><button type="button" class="btn btn-xs btn-outline-secondary mt-1" onclick="document.getElementById('rp_expire').value='';">清空日期</button></div>
          </div><br/>

          <div class="form-group">
            <label><b>资源池状态</b></label>
            <select class="form-control" id="rp_status">
              <?php foreach ($statuses as $k => $v): ?>
                <option value="<?= htmlspecialchars($k, ENT_QUOTES, 'UTF-8') ?>"
                  <?= ($is_edit && $pool['status'] === $k) ? 'selected' : '' ?>><?= htmlspecialchars($v, ENT_QUOTES, 'UTF-8') ?></option>
              <?php endforeach; ?>
            </select>
            <small class="text-muted">禁用后该资源池不能开通主机</small>
          </div><br/>

          <div class="form-group">
            <label><b>备注</b>（可选）</label>
            <input type="text" class="form-control" id="rp_remark" maxlength="500"
                   value="<?= $is_edit ? htmlspecialchars($pool['remark'], ENT_QUOTES, 'UTF-8') : '' ?>">
          </div><br/>

          <button class="btn btn-primary form-control" type="button" onclick="rpSave()">
            <i class="mdi mdi-checkbox-marked-circle-outline"></i> <?= $is_edit ? '保存修改' : '确认添加' ?>
          </button>

          <div class="panel-footer mt-3">
            <span class="mdi mdi-information-outline"></span> 说明：网页空间 / 数据库空间 / 流量是该资源池的<b>总配额</b>，
            从本资源池开通主机时会累计校验，超出即拒绝开通。填 0 表示该项不限。
          </div>

          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="<?= mnbt_asset_url('js/bootstrap-datepicker/bootstrap-datepicker.min.js') ?>"></script>
<script src="<?= mnbt_asset_url('js/bootstrap-datepicker/locales/bootstrap-datepicker.zh-CN.min.js') ?>"></script>
<script type="text/javascript" src="<?= mnbt_asset_url('js/main.min.js') ?>"></script>
<script type="text/javascript">
function rpGenPass() {
	var s = 'abcdefghijkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789';
	var out = '';
	for (var i = 0; i < 12; i++) { out += s.charAt(Math.floor(Math.random() * s.length)); }
	document.getElementById('rp_password').value = out;
	msalert(1, '已生成随机密码', 2000);
}

function rpSave() {
	var id = $('#rp_id').val();
	var nodes = [];
	$('.rp-node:checked').each(function () { nodes.push($(this).val()); });

	var name = $.trim($('#rp_name').val());
	var username = $.trim($('#rp_username').val());
	var password = $('#rp_password').val();

	if (name === '') { msalert(3, '请填写资源池名', 2000); return; }
	if (username === '') { msalert(3, '请填写用户名', 2000); return; }
	if (id === '0' && password.length < 6) { msalert(3, '密码不能少于 6 位', 2000); return; }

	msloading('正在保存...');
	$.post('ajax.php', {
		gn: 'p_respool_save',
		id: id,
		name: name,
		username: username,
		password: password,
		'nodes[]': nodes,
		web_space: $('#rp_web').val() || 0,
		sql_space: $('#rp_sql').val() || 0,
		flow: $('#rp_flow').val() || 0,
		expire_date: $.trim($('#rp_expire').val()),
		status: $('#rp_status').val(),
		remark: $.trim($('#rp_remark').val())
	}, function (resp) {
		var j;
		try { j = typeof resp === 'string' ? JSON.parse(resp) : resp; } catch (e) { j = {}; }
		msloadingde();
		var msg = j.msg || j.code || '未知返回';
		if (j.qk === 1 || j.qk === '1') {
			msalert(1, msg, 2000);
			setTimeout(function () { window.location.href = 'plugin.php?p=resource_pool&page=list'; }, 800);
		} else {
			msalert(4, msg, 4000);
		}
	}).fail(function () {
		msloadingde();
		msalert(4, '网络错误', 3000);
	});
}
</script>
