<?php
/**
 * 管理员端 - 资源池列表
 * URL: admin/plugin.php?p=resource_pool&page=list
 */
if (!defined('IN_CRONLITE')) {
	exit;
}

rp_ensure_schema();

$page_num = max(1, (int)($_GET['page_num'] ?? 1));
$kw       = trim((string)($_GET['kw'] ?? ''));
$status_f = trim((string)($_GET['status'] ?? ''));

$result      = rp_list($page_num, 20, $kw, $status_f);
$list        = $result['list'];
$total       = $result['total'];
$total_pages = $result['per_page'] > 0 ? (int)ceil($total / $result['per_page']) : 1;
$statuses    = rp_statuses();
$all_nodes   = rp_all_nodes();

// 一次性统计本页所有资源池的用量，避免逐行查询
$usage_map = rp_usage_batch(array_map(function ($p) {
	return (int)$p['id'];
}, $list));

/** 配额单元格：已用/总额，超额标红；总额 0 表示不限 */
$quota_cell = function ($used, $total, $unit) {
	$total = (int)$total;
	if ($total <= 0) {
		return '<span class="text-success">' . (int)$used . $unit . ' / 不限</span>';
	}
	$cls = ((int)$used > $total) ? 'text-danger' : 'text-success';
	return '<span class="' . $cls . '">' . (int)$used . ' / ' . $total . $unit . '</span>';
};

$title = '资源池列表';
mnbt_admin_include('head');

/** 拼接分页链接 */
$page_url = function ($n) use ($kw, $status_f) {
	return 'plugin.php?p=resource_pool&page=list&page_num=' . (int)$n
		. '&kw=' . urlencode($kw) . '&status=' . urlencode($status_f);
};
?>
<link rel="stylesheet" href="<?= mnbt_asset_url('js/jquery-confirm/jquery-confirm.min.css') ?>">
<link href="<?= mnbt_asset_url('js/bootstrap-datepicker/bootstrap-datepicker3.min.css') ?>" rel="stylesheet">
<script src="<?= mnbt_asset_url('js/jquery-confirm/jquery-confirm.min.js') ?>"></script>

<div class="container-fluid p-t-15">
  <div class="card">
    <header class="card-header">
      <div class="card-title" style="display:inline-block">资源池列表</div>
      <small class="text-muted ml-2">共 <?= (int)$total ?> 个资源池</small>
    </header>
    <div class="card-body">

      <form method="get" class="form-inline mb-3">
        <input type="hidden" name="p" value="resource_pool">
        <input type="hidden" name="page" value="list">
        <input type="text" name="kw" class="form-control form-control-sm mr-2" placeholder="资源池名 / 用户名"
               value="<?= htmlspecialchars($kw, ENT_QUOTES, 'UTF-8') ?>">
        <select name="status" class="form-control form-control-sm mr-2">
          <option value="">全部状态</option>
          <?php foreach ($statuses as $k => $v): ?>
            <option value="<?= htmlspecialchars($k, ENT_QUOTES, 'UTF-8') ?>" <?= $status_f === $k ? 'selected' : '' ?>>
              <?= htmlspecialchars($v, ENT_QUOTES, 'UTF-8') ?>
            </option>
          <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-sm btn-primary">搜索</button>
        <a href="plugin.php?p=resource_pool&page=list" class="btn btn-sm btn-outline-secondary ml-2">重置</a>
        <a href="plugin.php?p=resource_pool&page=add" class="btn btn-sm btn-success ml-2">
          <i class="mdi mdi-plus"></i> 添加资源池
        </a>
        <a href="plugin.php?p=resource_pool&page=hosts" class="btn btn-sm btn-outline-primary ml-2">
          <i class="mdi mdi-server"></i> 资源主机管理
        </a>
        <button type="button" class="btn btn-sm btn-outline-secondary ml-2" onclick="rpRepair()" title="补建插件数据表与 host_users 列">
          修复数据表
        </button>
      </form>

      <div class="table-responsive">
        <table class="table table-bordered table-hover table-striped">
          <thead>
            <tr>
              <th style="width:60px">ID</th>
              <th>资源池名</th>
              <th>用户名</th>
              <th>密码</th>
              <th>可用节点</th>
              <th>网页空间<br/><small class="text-muted">已用/总额</small></th>
              <th>数据库空间<br/><small class="text-muted">已用/总额</small></th>
              <th>流量<br/><small class="text-muted">已用/总额</small></th>
              <th>主机数</th>
              <th>到期日期</th>
              <th>状态</th>
              <th style="width:210px">操作</th>
            </tr>
          </thead>
          <tbody>
          <?php if (empty($list)): ?>
            <tr><td colspan="12" class="text-center text-muted">暂无资源池，点击右上「添加资源池」创建</td></tr>
          <?php else: ?>
            <?php foreach ($list as $p):
              $pid     = (int)$p['id'];
              $usage   = $usage_map[$pid] ?? ['hosts' => 0, 'web' => 0, 'sql' => 0, 'flow' => 0];
              $nodes   = rp_decode_nodes($p['nodes'] ?? '');
              $expired = rp_is_expired($p);
              $pname   = htmlspecialchars($p['name'], ENT_QUOTES, 'UTF-8');
            ?>
              <tr>
                <td><?= $pid ?></td>
                <td><b><?= $pname ?></b>
                  <?php if (!empty($p['remark'])): ?>
                    <br/><small class="text-muted"><?= htmlspecialchars($p['remark'], ENT_QUOTES, 'UTF-8') ?></small>
                  <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($p['username'], ENT_QUOTES, 'UTF-8') ?></td>
                <td>
                  <span class="rp-pass" data-pass="<?= htmlspecialchars($p['password'], ENT_QUOTES, 'UTF-8') ?>">••••••</span>
                  <a href="#!" class="ml-1 small" onclick="rpTogglePass(this);return false;">显示</a>
                </td>
                <td>
                  <?php if (empty($nodes)): ?>
                    <span class="badge badge-secondary">不限</span>
                  <?php else: ?>
                    <?php foreach ($nodes as $n): ?>
                      <span class="badge badge-light"><?= htmlspecialchars($n, ENT_QUOTES, 'UTF-8') ?></span>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </td>
                <td><?= $quota_cell($usage['web'], $p['web_space'], 'MB') ?></td>
                <td><?= $quota_cell($usage['sql'], $p['sql_space'], 'MB') ?></td>
                <td><?= $quota_cell($usage['flow'], $p['flow'], 'G') ?></td>
                <td>
                  <a href="plugin.php?p=resource_pool&page=hosts&pool_id=<?= $pid ?>"><?= (int)$usage['hosts'] ?> 台</a>
                </td>
                <td>
                  <?php if (trim((string)$p['expire_date']) === '' || $p['expire_date'] === '0000-00-00'): ?>
                    <span class="text-success">永不到期</span>
                  <?php else: ?>
                    <span class="<?= $expired ? 'text-danger' : 'text-success' ?>">
                      <?= htmlspecialchars($p['expire_date'], ENT_QUOTES, 'UTF-8') ?>
                    </span>
                    <?php if ($expired): ?><br/><span class="badge badge-danger">已到期</span><?php endif; ?>
                  <?php endif; ?>
                </td>
                <td>
                  <?php if (($p['status'] ?? '') === 'enabled'): ?>
                    <span class="badge badge-success">启用</span>
                  <?php else: ?>
                    <span class="badge badge-danger">禁用</span>
                  <?php endif; ?>
                </td>
                <td>
                  <button type="button" class="btn btn-xs btn-primary" title="从该资源池开通主机"
                          onclick="rpOpenHost(<?= $pid ?>, '<?= $pname ?>')">开通主机</button>
                  <a class="btn btn-xs btn-default" href="plugin.php?p=resource_pool&page=add&id=<?= $pid ?>" title="编辑">
                    <i class="mdi mdi-pencil"></i>
                  </a>
                  <?php if (($p['status'] ?? '') === 'enabled'): ?>
                    <button type="button" class="btn btn-xs btn-warning" onclick="rpStatus(<?= $pid ?>, 'disabled')" title="禁用">禁用</button>
                  <?php else: ?>
                    <button type="button" class="btn btn-xs btn-success" onclick="rpStatus(<?= $pid ?>, 'enabled')" title="启用">启用</button>
                  <?php endif; ?>
                  <button type="button" class="btn btn-xs btn-danger" onclick="rpDelete(<?= $pid ?>, '<?= $pname ?>')" title="删除">
                    <i class="mdi mdi-window-close"></i>
                  </button>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
          </tbody>
        </table>
      </div>

      <?php if ($total_pages > 1): ?>
        <nav>
          <ul class="pagination justify-content-center">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
              <li class="page-item <?= $i === $page_num ? 'active' : '' ?>">
                <a class="page-link" href="<?= htmlspecialchars($page_url($i), ENT_QUOTES, 'UTF-8') ?>"><?= $i ?></a>
              </li>
            <?php endfor; ?>
          </ul>
        </nav>
      <?php endif; ?>

      <div class="panel-footer">
        <span class="mdi mdi-information-outline"></span>
        「开通主机」会调用所选节点的宝塔 API 创建站点，并把新主机的账号记入本资源池的
        <code>host_users</code>（未改动主机表结构）；删除资源池不会删除已开通的主机，只会解除归属关系。
        主机分布与归属管理见「资源主机管理」。
      </div>
    </div>
  </div>
</div>

<script src="<?= mnbt_asset_url('js/bootstrap-datepicker/bootstrap-datepicker.min.js') ?>"></script>
<script src="<?= mnbt_asset_url('js/bootstrap-datepicker/locales/bootstrap-datepicker.zh-CN.min.js') ?>"></script>
<script type="text/javascript" src="<?= mnbt_asset_url('js/main.min.js') ?>"></script>
<script type="text/javascript">
var RP_NODES = <?= json_encode(array_map(function ($n) {
	return ['btdh' => (string)$n['btdh'], 'btip' => (string)$n['btip']];
}, $all_nodes), JSON_UNESCAPED_UNICODE) ?: '[]' ?>;

var RP_POOL_NODES = <?= json_encode(array_reduce($list, function ($carry, $p) {
	$carry[(string)(int)$p['id']] = rp_decode_nodes($p['nodes'] ?? '');
	return $carry;
}, []), JSON_UNESCAPED_UNICODE) ?: '{}' ?>;

function rpEsc(s) { return $('<div>').text(s == null ? '' : s).html(); }

function rpTogglePass(a) {
	var span = $(a).prev('.rp-pass');
	if ($(a).text() === '显示') {
		span.text(span.data('pass'));
		$(a).text('隐藏');
	} else {
		span.text('••••••');
		$(a).text('显示');
	}
}

function rpResp(resp) {
	try { return typeof resp === 'string' ? JSON.parse(resp) : resp; } catch (e) { return {}; }
}
function rpOk(j) { return j && (j.qk === 1 || j.qk === '1'); }
function rpMsg(j) { return (j && (j.msg || j.code)) || '未知返回'; }

function rpRepair() {
	msloading('正在修复...');
	$.post('ajax.php', { gn: 'p_respool_repair' }, function (resp) {
		var j = rpResp(resp);
		msloadingde();
		if (rpOk(j)) { msalert(1, rpMsg(j), 2000); setTimeout(function () { location.reload(); }, 800); }
		else { msalert(4, rpMsg(j), 4000); }
	}).fail(function () { msloadingde(); msalert(4, '网络错误', 3000); });
}

function rpStatus(id, status) {
	msloading('正在处理...');
	$.post('ajax.php', { gn: 'p_respool_status', id: id, status: status }, function (resp) {
		var j = rpResp(resp);
		msloadingde();
		if (rpOk(j)) { msalert(1, rpMsg(j), 1500); setTimeout(function () { location.reload(); }, 600); }
		else { msalert(4, rpMsg(j), 4000); }
	}).fail(function () { msloadingde(); msalert(4, '网络错误', 3000); });
}

function rpDelete(id, name) {
	$.confirm({
		title: '删除资源池',
		content: '确认删除资源池「' + rpEsc(name) + '」？<br/>已开通的主机<b>不会</b>被删除，只会解除归属关系。',
		type: 'red',
		buttons: {
			confirm: {
				text: '确认删除', btnClass: 'btn-danger',
				action: function () {
					msloading('正在删除...');
					$.post('ajax.php', { gn: 'p_respool_delete', id: id }, function (resp) {
						var j = rpResp(resp);
						msloadingde();
						if (rpOk(j)) { msalert(1, rpMsg(j), 1500); setTimeout(function () { location.reload(); }, 600); }
						else { msalert(4, rpMsg(j), 4000); }
					}).fail(function () { msloadingde(); msalert(4, '网络错误', 3000); });
				}
			},
			cancel: { text: '取消' }
		}
	});
}

function rpOpenHost(poolId, poolName) {
	var allow = RP_POOL_NODES[String(poolId)] || [];
	var opts = '';
	for (var i = 0; i < RP_NODES.length; i++) {
		var nd = RP_NODES[i];
		if (allow.length && allow.indexOf(nd.btdh) === -1) continue;
		opts += '<option value="' + rpEsc(nd.btdh) + '">' + rpEsc(nd.btdh) + ' (' + rpEsc(nd.btip) + ')</option>';
	}
	if (opts === '') {
		$.alert({ title: '无可用节点', content: '该资源池没有可用节点，请先编辑资源池勾选节点，或在「宝塔管理」添加节点。', type: 'red' });
		return;
	}

	$.confirm({
		title: '从资源池开通主机 - ' + rpEsc(poolName),
		columnClass: 'col-md-8 col-md-offset-2',
		content: '<div class="form-group"><label>开通节点</label><select class="form-control" id="rp-oh-node">' + opts + '</select></div>' +
			'<div class="form-group"><label>主机账号（FTP/SQL 账号，≥6 位，字母数字下划线）</label>' +
			'<input type="text" class="form-control" id="rp-oh-user" placeholder="例如 user123456"></div>' +
			'<div class="form-group"><label>主机密码（≥6 位）</label>' +
			'<input type="text" class="form-control" id="rp-oh-pass" placeholder="例如 Abc123456"></div>' +
			'<div class="form-group"><label>网页空间(MB)</label><input type="number" min="0" class="form-control" id="rp-oh-web" value="1024"></div>' +
			'<div class="form-group"><label>数据库空间(MB)</label><input type="number" min="0" class="form-control" id="rp-oh-sql" value="256"></div>' +
			'<div class="form-group"><label>流量(G/月，0=不限)</label><input type="number" min="0" class="form-control" id="rp-oh-flow" value="0"></div>' +
			'<div class="form-group"><label>域名最大绑定数（0=不限）</label><input type="number" min="0" class="form-control" id="rp-oh-ym" value="5"></div>' +
			'<div class="form-group"><label>主机到期时间（留空=不启用到期检测）</label>' +
			'<input type="text" class="form-control" id="rp-oh-expire" placeholder="yyyy-mm-dd"></div>',
		buttons: {
			confirm: {
				text: '确认开通', btnClass: 'btn-primary',
				action: function () {
					var hu = $.trim($('#rp-oh-user').val());
					var hp = $('#rp-oh-pass').val();
					if (hu.length < 6 || hp.length < 6) {
						$.alert('主机账号和密码均不能少于 6 位');
						return false;
					}
					msloading('正在调用宝塔开通，请稍候...');
					$.post('ajax.php', {
						gn: 'p_respool_open_host',
						pool_id: poolId,
						node: $('#rp-oh-node').val(),
						host_user: hu,
						host_pass: hp,
						web_space: $('#rp-oh-web').val() || 0,
						sql_space: $('#rp-oh-sql').val() || 0,
						flow: $('#rp-oh-flow').val() || 0,
						domain_count: $('#rp-oh-ym').val() || 0,
						expire_date: $.trim($('#rp-oh-expire').val()),
						host_status: 'true'
					}, function (resp) {
						var j = rpResp(resp);
						msloadingde();
						if (rpOk(j)) {
							$.alert({
								title: '开通成功', type: 'green',
								content: '主机 ID：' + rpEsc(j.host_id),
								buttons: { ok: { action: function () { location.reload(); } } }
							});
						} else {
							$.alert({ title: '开通失败', content: rpEsc(rpMsg(j)), type: 'red' });
						}
					}).fail(function () {
						msloadingde();
						$.alert({ title: '错误', content: '网络错误', type: 'red' });
					});
					return true;
				}
			},
			cancel: { text: '取消' }
		},
		onContentReady: function () {
			try {
				$('#rp-oh-expire').datepicker({ format: 'yyyy-mm-dd', language: 'zh-CN', autoclose: true });
			} catch (e) {}
		}
	});
}
</script>
