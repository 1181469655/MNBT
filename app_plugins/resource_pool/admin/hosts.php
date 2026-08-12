<?php
/**
 * 管理员端 - 资源主机管理
 *
 * 查看所有由资源池开通 / 归属资源池的主机，可按资源池筛选。
 * URL: admin/plugin.php?p=resource_pool&page=hosts[&pool_id=N][&kw=xxx]
 *
 * 归属关系来自 MN_plugin_respool.host_users（MN_zj.user 的 JSON 数组），
 * 不依赖主机表的任何新增字段。
 */
if (!defined('IN_CRONLITE')) {
	exit;
}

rp_ensure_schema();

$pool_id = (int)($_GET['pool_id'] ?? 0);
$kw      = trim((string)($_GET['kw'] ?? ''));

$pools    = rp_list(1, 200)['list'];
$cur_pool = $pool_id > 0 ? rp_get($pool_id) : null;
if ($pool_id > 0 && !$cur_pool) {
	$pool_id = 0;
}

$hosts     = rp_all_pool_hosts($pool_id, $kw);
$unbound   = rp_unbound_hosts(500);
$total_all = count(rp_host_user_map());

$title = '资源主机管理';
mnbt_admin_include('head');
?>
<link rel="stylesheet" href="<?= mnbt_asset_url('js/jquery-confirm/jquery-confirm.min.css') ?>">
<script src="<?= mnbt_asset_url('js/jquery-confirm/jquery-confirm.min.js') ?>"></script>

<div class="container-fluid p-t-15">
  <div class="card">
    <header class="card-header">
      <div class="card-title" style="display:inline-block">
        资源主机管理<?= $cur_pool ? ' - ' . htmlspecialchars($cur_pool['name'], ENT_QUOTES, 'UTF-8') : '' ?>
      </div>
      <small class="text-muted ml-2">
        当前 <?= count($hosts) ?> 台<?= $pool_id > 0 ? '（全部资源池共 ' . (int)$total_all . ' 台）' : '' ?>
      </small>
    </header>
    <div class="card-body">

      <div class="callout callout-info">
        <p class="small">
          本页只列出<b>归属资源池</b>的主机。主机的开通 / 暂停 / 删除仍在「主机管理 → 主机列表」操作；
          这里管理归属关系与查看各资源池的主机分布。
        </p>
      </div>

      <form method="get" class="form-inline mb-3">
        <input type="hidden" name="p" value="resource_pool">
        <input type="hidden" name="page" value="hosts">
        <label class="mr-2">资源池</label>
        <select name="pool_id" class="form-control form-control-sm mr-2">
          <option value="0">全部资源池</option>
          <?php foreach ($pools as $p): ?>
            <option value="<?= (int)$p['id'] ?>" <?= $pool_id === (int)$p['id'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($p['name'], ENT_QUOTES, 'UTF-8') ?>
            </option>
          <?php endforeach; ?>
        </select>
        <input type="text" name="kw" class="form-control form-control-sm mr-2" placeholder="主机账号 / 网站名 / 宝塔"
               value="<?= htmlspecialchars($kw, ENT_QUOTES, 'UTF-8') ?>">
        <button type="submit" class="btn btn-sm btn-primary">筛选</button>
        <a href="plugin.php?p=resource_pool&page=hosts" class="btn btn-sm btn-outline-secondary ml-2">重置</a>
        <button type="button" class="btn btn-sm btn-success ml-2" onclick="rpBindDialog()">
          <i class="mdi mdi-link-variant"></i> 绑定已有主机
        </button>
        <button type="button" class="btn btn-sm btn-outline-warning ml-2" onclick="rpPrune()">
          清理失效归属
        </button>
        <a href="plugin.php?p=resource_pool&page=list" class="btn btn-sm btn-outline-secondary ml-2">资源池列表</a>
      </form>

      <div class="table-responsive">
        <table class="table table-bordered table-hover table-striped">
          <thead>
            <tr>
              <th style="width:60px">ID</th>
              <th>资源池</th>
              <th>所属宝塔</th>
              <th>网站名</th>
              <th>主机账号</th>
              <th>网页空间</th>
              <th>数据库空间</th>
              <th>流量</th>
              <th>创建时间</th>
              <th>到期时间</th>
              <th>状态</th>
              <th style="width:100px">操作</th>
            </tr>
          </thead>
          <tbody>
          <?php if (empty($hosts)): ?>
            <tr><td colspan="12" class="text-center text-muted">
              <?php if ($kw !== ''): ?>
                没有匹配「<?= htmlspecialchars($kw, ENT_QUOTES, 'UTF-8') ?>」的资源池主机
              <?php elseif ($pool_id > 0): ?>
                该资源池还没有主机，可在「资源池列表」点「开通主机」，或用上方「绑定已有主机」
              <?php else: ?>
                还没有任何归属资源池的主机
              <?php endif; ?>
            </td></tr>
          <?php else: ?>
            <?php foreach ($hosts as $h):
              $hu   = (string)$h['user'];
              $hpid = (int)($h['pool_id'] ?? 0);
            ?>
              <tr>
                <td><?= (int)$h['id'] ?></td>
                <td>
                  <?php if ($hpid > 0): ?>
                    <a href="plugin.php?p=resource_pool&page=hosts&pool_id=<?= $hpid ?>">
                      <span class="badge badge-info"><?= htmlspecialchars((string)$h['pool_name'], ENT_QUOTES, 'UTF-8') ?></span>
                    </a>
                  <?php else: ?>
                    <span class="text-muted">-</span>
                  <?php endif; ?>
                </td>
                <td><?= htmlspecialchars((string)$h['ssbt'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars((string)$h['sqldz'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($hu, ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= (int)rp_json_max($h['hxa'] ?? '') ?>MB</td>
                <td><?= (int)rp_json_max($h['hxb'] ?? '') ?>MB</td>
                <td><?= (int)rp_json_max($h['llmax'] ?? '') ?>G</td>
                <td class="small text-muted"><?= htmlspecialchars((string)$h['data'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars((string)$h['datae'], ENT_QUOTES, 'UTF-8') ?></td>
                <td>
                  <?php if ((string)$h['qk'] === 'true'): ?>
                    <span class="badge badge-success">正常</span>
                  <?php else: ?>
                    <span class="badge badge-danger">暂停</span>
                  <?php endif; ?>
                </td>
                <td>
                  <button type="button" class="btn btn-xs btn-outline-danger"
                          onclick="rpUnbind('<?= htmlspecialchars($hu, ENT_QUOTES, 'UTF-8') ?>')"
                          title="解除归属，不删除主机">解除归属</button>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
          </tbody>
        </table>
      </div>

      <div class="panel-footer">
        <span class="mdi mdi-information-outline"></span>
        归属关系存在资源池表的 <code>host_users</code> 字段（按主机账号匹配），未改动主机表结构。
        主机在「主机列表」被删除后，归属会自动失效不再统计，可点「清理失效归属」清掉残留记录。
      </div>
    </div>
  </div>
</div>

<script type="text/javascript" src="<?= mnbt_asset_url('js/main.min.js') ?>"></script>
<script type="text/javascript">
var RP_POOLS = <?= json_encode(array_map(function ($p) {
	return ['id' => (int)$p['id'], 'name' => (string)$p['name'], 'status' => (string)$p['status']];
}, $pools), JSON_UNESCAPED_UNICODE) ?: '[]' ?>;

var RP_UNBOUND = <?= json_encode(array_map(function ($h) {
	return ['user' => (string)$h['user'], 'sqldz' => (string)$h['sqldz'], 'ssbt' => (string)$h['ssbt']];
}, $unbound), JSON_UNESCAPED_UNICODE) ?: '[]' ?>;

function rpEsc(s) { return $('<div>').text(s == null ? '' : s).html(); }
function rpResp(resp) {
	try { return typeof resp === 'string' ? JSON.parse(resp) : resp; } catch (e) { return {}; }
}
function rpOk(j) { return j && (j.qk === 1 || j.qk === '1'); }
function rpMsg(j) { return (j && (j.msg || j.code)) || '未知返回'; }

function rpUnbind(hostUser) {
	$.confirm({
		title: '解除资源池归属',
		content: '确认解除主机「' + rpEsc(hostUser) + '」的资源池归属？<br/>主机<b>不会</b>被删除，其占用的配额会从资源池释放。',
		type: 'orange',
		buttons: {
			confirm: {
				text: '确认解除', btnClass: 'btn-warning',
				action: function () {
					msloading('正在处理...');
					$.post('ajax.php', { gn: 'p_respool_unbind_host', host_user: hostUser }, function (resp) {
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

function rpBindDialog() {
	if (!RP_POOLS.length) {
		$.alert({ title: '没有资源池', content: '请先到「添加资源池」创建资源池。', type: 'red' });
		return;
	}
	if (!RP_UNBOUND.length) {
		$.alert({ title: '没有可绑定的主机', content: '所有主机都已归属资源池，或系统内还没有主机。', type: 'orange' });
		return;
	}
	var pOpts = '';
	for (var i = 0; i < RP_POOLS.length; i++) {
		var dis = RP_POOLS[i].status === 'enabled' ? '' : '（已禁用）';
		pOpts += '<option value="' + RP_POOLS[i].id + '">' + rpEsc(RP_POOLS[i].name) + dis + '</option>';
	}
	var hOpts = '';
	for (var k = 0; k < RP_UNBOUND.length; k++) {
		var h = RP_UNBOUND[k];
		hOpts += '<option value="' + rpEsc(h.user) + '">' + rpEsc(h.user) + ' — ' + rpEsc(h.sqldz) + ' (' + rpEsc(h.ssbt) + ')</option>';
	}
	$.confirm({
		title: '绑定已有主机到资源池',
		columnClass: 'col-md-8 col-md-offset-2',
		content: '<div class="form-group"><label>目标资源池</label>' +
			'<select class="form-control" id="rp-bd-pool">' + pOpts + '</select></div>' +
			'<div class="form-group"><label>选择主机（仅列出未归属任何资源池的主机）</label>' +
			'<select class="form-control" id="rp-bd-host">' + hOpts + '</select></div>' +
			'<p class="small text-muted">绑定后该主机的空间 / 流量会计入所选资源池的已用配额。</p>',
		buttons: {
			confirm: {
				text: '确认绑定', btnClass: 'btn-success',
				action: function () {
					msloading('正在绑定...');
					$.post('ajax.php', {
						gn: 'p_respool_bind_host',
						pool_id: $('#rp-bd-pool').val(),
						host_user: $('#rp-bd-host').val()
					}, function (resp) {
						var j = rpResp(resp);
						msloadingde();
						if (rpOk(j)) {
							msalert(1, rpMsg(j), 1500);
							setTimeout(function () { location.reload(); }, 600);
						} else {
							$.alert({ title: '绑定失败', content: rpEsc(rpMsg(j)), type: 'red' });
						}
					}).fail(function () { msloadingde(); msalert(4, '网络错误', 3000); });
					return true;
				}
			},
			cancel: { text: '取消' }
		}
	});
}

function rpPrune() {
	$.confirm({
		title: '清理失效归属',
		content: '把已删除主机的账号从资源池的归属列表里移除。<br/>不会影响任何现存主机。',
		buttons: {
			confirm: {
				text: '开始清理', btnClass: 'btn-warning',
				action: function () {
					msloading('正在清理...');
					$.post('ajax.php', { gn: 'p_respool_prune' }, function (resp) {
						var j = rpResp(resp);
						msloadingde();
						if (rpOk(j)) { msalert(1, rpMsg(j), 2000); setTimeout(function () { location.reload(); }, 900); }
						else { msalert(4, rpMsg(j), 4000); }
					}).fail(function () { msloadingde(); msalert(4, '网络错误', 3000); });
				}
			},
			cancel: { text: '取消' }
		}
	});
}
</script>
