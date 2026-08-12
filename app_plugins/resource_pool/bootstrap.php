<?php
/**
 * resource_pool 插件 - 主入口
 *
 * 管理员后台一级菜单「资源池管理」，二级菜单：
 *   - 添加资源池
 *   - 资源池列表
 *
 * 数据表：MN_plugin_respool（插件自有）
 * 主机表：MN_zj 新增 pool_id 字段（默认 NULL，资源池开通的主机记录归属）
 */

if (!defined('IN_CRONLITE')) {
	exit;
}

require_once __DIR__ . '/lib/pool.php';

mnbt_plugin_register('resource_pool', [
	'name'        => '资源池管理',
	'description' => '资源池账号、可用节点、配额与到期管理',
]);

/* ============================================================
 *  菜单与页面
 * ============================================================ */

mnbt_register_page('admin', 'add',   'admin/add.php',   '添加资源池');
mnbt_register_page('admin', 'list',  'admin/list.php',  '资源池列表');
mnbt_register_page('admin', 'hosts', 'admin/hosts.php', '资源主机管理');

mnbt_register_menu('admin', [
	'title'    => '资源池管理',
	'icon'     => 'mdi-database',
	'order'    => 60,
	'children' => [
		['title' => '添加资源池',   'page' => 'add',   'icon' => 'mdi-plus-box',             'order' => 10, 'multitabs' => true],
		['title' => '资源池列表',   'page' => 'list',  'icon' => 'mdi-format-list-bulleted', 'order' => 20, 'multitabs' => true],
		['title' => '资源主机管理', 'page' => 'hosts', 'icon' => 'mdi-server',               'order' => 30, 'multitabs' => true],
	],
]);

mnbt_register_settings_tab([
	'title' => '资源池管理',
	'page'  => 'list',
	'order' => 60,
]);

/* ============================================================
 *  主机列表注入「资源池」列
 * ============================================================
 *  不改核心文件：在管理端 head 之后注入一段脚本，包装
 *  $.fn.bootstrapTable，在核心 list.php 初始化主机表时插入一列。
 *  仅在 admin/list.php?gn=zj 生效；异常时静默降级，不影响原页面。
 */
mnbt_register_partial_override('admin', 'head', function ($vars) {
	if (($_GET['gn'] ?? '') !== 'zj') {
		return null;
	}
	$script = (string)($_SERVER['SCRIPT_NAME'] ?? '');
	if (basename($script) !== 'list.php') {
		return null;
	}
	// 主机账号 => 资源池名（归属存在资源池表，不依赖主机表字段）
	// JSON_FORCE_OBJECT：防止纯数字主机账号让 json_encode 输出数组而非对象
	$map  = rp_host_user_name_map();
	$json = json_encode($map, JSON_UNESCAPED_UNICODE | JSON_FORCE_OBJECT) ?: '{}';

	$js = <<<HTML
<script type="text/javascript">
/* resource_pool 插件：向主机列表注入「资源池」列（按主机账号匹配） */
(function () {
	var POOL_BY_USER = {$json};
	function formatter(value, row) {
		var u = row ? row.user : null;
		if (u === null || u === undefined || u === '') {
			return '<span class="text-muted">-</span>';
		}
		var name = POOL_BY_USER[String(u)];
		if (name) {
			return '<span class="badge badge-info">' + \$('<div>').text(name).html() + '</span>';
		}
		return '<span class="text-muted">-</span>';
	}
	function inject(columns) {
		if (!columns || !columns.length) return false;
		for (var i = 0; i < columns.length; i++) {
			if (columns[i] && columns[i].field === 'rp_pool') return false;
		}
		var at = columns.length;
		for (var j = 0; j < columns.length; j++) {
			if (columns[j] && columns[j].field === 'operate') { at = j; break; }
		}
		columns.splice(at, 0, { field: 'rp_pool', title: '资源池', formatter: formatter });
		return true;
	}
	function wrap(real) {
		var wrapped = function (option) {
			try {
				if (option && typeof option === 'object' && option.columns) {
					if (Array.isArray(option.columns[0])) {
						inject(option.columns[0]);
					} else {
						inject(option.columns);
					}
				}
			} catch (e) {}
			return real.apply(this, arguments);
		};
		return wrapped;
	}
	function install() {
		if (!window.jQuery) return false;
		var \$ = window.jQuery;
		if (\$.fn.bootstrapTable) {
			if (!\$.fn.bootstrapTable.__rpWrapped) {
				var real = \$.fn.bootstrapTable;
				var w = wrap(real);
				for (var k in real) { if (Object.prototype.hasOwnProperty.call(real, k)) w[k] = real[k]; }
				w.__rpWrapped = true;
				\$.fn.bootstrapTable = w;
			}
			return true;
		}
		// bootstrapTable 尚未加载：拦截其赋值
		try {
			var stored;
			Object.defineProperty(\$.fn, 'bootstrapTable', {
				configurable: true,
				enumerable: true,
				get: function () { return stored; },
				set: function (v) {
					if (typeof v === 'function' && !v.__rpWrapped) {
						var w2 = wrap(v);
						for (var k2 in v) { if (Object.prototype.hasOwnProperty.call(v, k2)) w2[k2] = v[k2]; }
						w2.__rpWrapped = true;
						stored = w2;
					} else {
						stored = v;
					}
				}
			});
			return true;
		} catch (e) { return false; }
	}
	try { install(); } catch (e) {}
})();
</script>
HTML;
	return ['after' => $js];
}, 10);

/* ============================================================
 *  AJAX（管理端，gn 前缀 p_respool_）
 * ============================================================ */

/** 新增 / 编辑资源池 */
mnbt_register_ajax('admin', 'p_respool_save', function () {
	global $DB, $user;
	mnbt_plugin_require_admin();
	rp_ensure_schema();

	$id = (int)($_POST['id'] ?? 0);
	$in = [
		'name'        => $_POST['name'] ?? '',
		'username'    => $_POST['username'] ?? '',
		'password'    => $_POST['password'] ?? '',
		'nodes'       => isset($_POST['nodes']) && is_array($_POST['nodes']) ? $_POST['nodes'] : [],
		'web_space'   => $_POST['web_space'] ?? 0,
		'sql_space'   => $_POST['sql_space'] ?? 0,
		'flow'        => $_POST['flow'] ?? 0,
		'expire_date' => $_POST['expire_date'] ?? '',
		'status'      => $_POST['status'] ?? 'enabled',
		'remark'      => $_POST['remark'] ?? '',
	];

	if ($id > 0) {
		$r = rp_update($id, $in);
		if (!$r['ok']) {
			json_exit_error($r['msg']);
		}
		mnbt_log($user ?? '系统', '插件-资源池', '编辑资源池 ID' . $id, '成功', $DB);
		json_exit_success('保存成功', ['id' => $id]);
	}

	$r = rp_create($in);
	if (!$r['ok']) {
		json_exit_error($r['msg']);
	}
	mnbt_log($user ?? '系统', '插件-资源池', '添加资源池 ' . $in['name'], '成功', $DB);
	json_exit_success('添加成功', ['id' => $r['id']]);
});

/** 删除资源池 */
mnbt_register_ajax('admin', 'p_respool_delete', function () {
	global $DB, $user;
	mnbt_plugin_require_admin();
	$id = (int)($_POST['id'] ?? 0);
	$r  = rp_delete($id);
	if (!$r['ok']) {
		json_exit_error($r['msg']);
	}
	mnbt_log($user ?? '系统', '插件-资源池', '删除资源池 ID' . $id, '成功', $DB);
	json_exit_success('删除成功');
});

/** 启用 / 禁用资源池 */
mnbt_register_ajax('admin', 'p_respool_status', function () {
	global $DB, $user;
	mnbt_plugin_require_admin();
	$id     = (int)($_POST['id'] ?? 0);
	$status = (string)($_POST['status'] ?? '');
	$r      = rp_set_status($id, $status);
	if (!$r['ok']) {
		json_exit_error($r['msg']);
	}
	mnbt_log($user ?? '系统', '插件-资源池', '资源池 ID' . $id . ' 状态改为 ' . $status, '成功', $DB);
	json_exit_success('操作成功');
});

/** 从资源池开通主机 */
mnbt_register_ajax('admin', 'p_respool_open_host', function () {
	global $DB, $user;
	mnbt_plugin_require_admin();
	$pool_id = (int)($_POST['pool_id'] ?? 0);
	$r = rp_open_host($pool_id, [
		'node'         => $_POST['node'] ?? '',
		'user'         => $_POST['host_user'] ?? '',
		'pass'         => $_POST['host_pass'] ?? '',
		'web_space'    => $_POST['web_space'] ?? 0,
		'sql_space'    => $_POST['sql_space'] ?? 0,
		'flow'         => $_POST['flow'] ?? 0,
		'domain_count' => $_POST['domain_count'] ?? 0,
		'expire_date'  => $_POST['expire_date'] ?? '',
		'status'       => ($_POST['host_status'] ?? 'true') === 'true' ? 'true' : 'false',
	]);
	if (!$r['ok']) {
		mnbt_log($user ?? '系统', '插件-资源池', '资源池 ID' . $pool_id . ' 开通主机失败：' . $r['msg'], '失败', $DB);
		json_exit_error($r['msg']);
	}
	mnbt_log($user ?? '系统', '插件-资源池', '资源池 ID' . $pool_id . ' 开通主机 ID' . $r['host_id'], '成功', $DB);
	json_exit_success('开通成功', ['host_id' => $r['host_id']]);
});

/** 把已有主机绑定到资源池（按主机账号写入 host_users） */
mnbt_register_ajax('admin', 'p_respool_bind_host', function () {
	global $DB, $user;
	mnbt_plugin_require_admin();
	$pool_id   = (int)($_POST['pool_id'] ?? 0);
	$host_user = trim((string)($_POST['host_user'] ?? ''));
	$r = rp_bind_host_user($pool_id, $host_user);
	if (!$r['ok']) {
		json_exit_error($r['msg']);
	}
	mnbt_log($user ?? '系统', '插件-资源池', '主机 ' . $host_user . ' 绑定到资源池 ID' . $pool_id, '成功', $DB);
	json_exit_success($r['msg']);
});

/** 解除主机的资源池归属（从 host_users 移除，不删主机） */
mnbt_register_ajax('admin', 'p_respool_unbind_host', function () {
	global $DB, $user;
	mnbt_plugin_require_admin();
	$host_user = trim((string)($_POST['host_user'] ?? ''));
	$pool_id   = isset($_POST['pool_id']) && $_POST['pool_id'] !== '' ? (int)$_POST['pool_id'] : null;
	if ($host_user === '') {
		json_exit_error('参数错误');
	}
	$r = rp_unbind_host_user($host_user, $pool_id);
	if (!$r['ok']) {
		json_exit_error($r['msg']);
	}
	mnbt_log($user ?? '系统', '插件-资源池', '解除主机 ' . $host_user . ' 的资源池归属', '成功', $DB);
	json_exit_success($r['msg']);
});

/** 清理失效归属（主机已被删除但账号仍留在 host_users 里） */
mnbt_register_ajax('admin', 'p_respool_prune', function () {
	global $DB, $user;
	mnbt_plugin_require_admin();
	$pool_id = isset($_POST['pool_id']) && (int)$_POST['pool_id'] > 0 ? (int)$_POST['pool_id'] : null;
	$r = rp_prune_host_users($pool_id);
	if (!$r['ok']) {
		json_exit_error($r['msg']);
	}
	if ((int)$r['removed'] > 0) {
		mnbt_log($user ?? '系统', '插件-资源池', '清理失效归属 ' . (int)$r['removed'] . ' 条', '成功', $DB);
	}
	json_exit_success($r['msg'], ['removed' => (int)$r['removed']]);
});

/** 修复数据表（补建 MN_plugin_respool 与 host_users 列） */
mnbt_register_ajax('admin', 'p_respool_repair', function () {
	global $DB;
	mnbt_plugin_require_admin();
	rp_ensure_schema(true);
	if (@$DB->get_row_prepare("SELECT `host_users` FROM " . RP_TABLE . " WHERE 1 LIMIT 1") === false) {
		json_exit_error('资源池表的 host_users 列仍不可用，请检查数据库账号权限');
	}
	json_exit_success('数据表已就绪');
});
