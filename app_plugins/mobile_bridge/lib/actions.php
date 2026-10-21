<?php
/**
 * mobile_bridge - action 注册表与分发（PRD §5 / 交接 §3 全表）
 *
 * 约定：
 * - 'proxy'  = 参数转换后 include 核心 handler（复用其校验/保护/审计），输出经包络归一化；
 * - 'custom' = bridge 自实现（核心无对应入口或形状不合），直接调 bt_api / DB；
 * - params() 返回值即改写后的 $_POST（不含 gn）；reshape 收 (success,code,msg,data) 返回调整后 [success,code,msg,data]。
 */
if (!defined('IN_CRONLITE')) exit;

require_once __DIR__ . '/adapter.php';
require_once __DIR__ . '/token.php';
require_once __DIR__ . '/pair.php';
require_once __DIR__ . '/throttle.php';
require_once __DIR__ . '/actions_file.php';
require_once __DIR__ . '/actions_site.php';
require_once __DIR__ . '/actions_domain_ssl.php';
require_once __DIR__ . '/actions_db.php';

if (!function_exists('mnbt_app_p')) {
	/** 读 APP 上行参数（原样字符串） */
	function mnbt_app_p($key, $default = '')
	{
		$v = $_POST[$key] ?? null;
		if ($v === null || $v === '') return $default;
		return is_array($v) ? $v : (string)$v;
	}
}

if (!function_exists('mnbt_app_split_path')) {
	/** 站点相对全路径 -> [dir, name]；'/a/b.txt' -> ['/a','b.txt']，'/b.txt' -> ['/','b.txt'] */
	function mnbt_app_split_path($full)
	{
		$full = (string)$full;
		if ($full === '' || $full[0] !== '/') $full = '/' . $full;
		$full = rtrim($full, '/');
		if ($full === '') return ['/', ''];
		$pos = strrpos($full, '/');
		if ($pos === false) return ['/', $full];
		return [$pos === 0 ? '/' : substr($full, 0, $pos), substr($full, $pos + 1)];
	}
}

if (!function_exists('mnbt_app_quota_pair')) {
	/**
	 * MN_zj 的配额 JSON（{dq,max}）-> APP 的 Quota{used:Double,max:Double,unit:String}。
	 * 注意单位口径照搬面板：网页/数据库空间 dq 由 jk.php 按 size/(1024*1000) 写入 = MB；
	 * 流量 llmax.dq 是日志累加的**字节数**，而 max 是 GB（jk.php:178 用 max*1024^3 比较），
	 * 所以流量必须把 used 折算成 GB，否则 APP 会把几百 GB 显示成几百 B。
	 */
	function mnbt_app_quota_pair($json_str, $unit = 'MB', $divide = 1.0)
	{
		$arr = json_decode((string)$json_str, true);
		if (!is_array($arr)) $arr = [];
		$divide = (float)$divide > 0 ? (float)$divide : 1.0;
		return [
			'used' => round((float)($arr['dq'] ?? 0) / $divide, 2),
			'max' => round((float)($arr['max'] ?? 0) / $divide, 2),
			'unit' => $unit,
		];
	}
}

if (!function_exists('mnbt_app_host_row')) {
	/** 当前主机的公共档案块（pair / host.profile / device.info 复用，字段对齐 HostBrief+HostProfile） */
	function mnbt_app_host_row()
	{
		global $yhc, $DB;
		$expire_ts = ($yhc['datae'] === '0000-00-00' || $yhc['datae'] === '') ? 0 : (int)strtotime($yhc['datae']);
		// ymbds：0/空 = 不限（domain.add 的拦截口径 same），APP 侧 HostProfile.ymbds 是 Int?
		return [
			'user' => (string)$yhc['user'],
			'domain' => (string)$yhc['sqldz'],
			'site_name' => (string)$yhc['sqldz'],
			'status' => $yhc['qk'] === 'false' ? 'banned' : 'running',
			'expire' => (string)$yhc['datae'],
			'expire_ts' => $expire_ts,
			'node' => (string)$yhc['ssbt'],
			'ymbds' => (int)$yhc['ymbds'],
			'created_at' => (string)$yhc['data'],
			'web_quota' => mnbt_app_quota_pair($yhc['hxa'] ?? '', 'MB'),
			'sql_quota' => mnbt_app_quota_pair($yhc['hxb'] ?? '', 'MB'),
			'traffic_quota' => mnbt_app_quota_pair($yhc['llmax'] ?? '', 'GB', 1024 * 1024 * 1024),
		];
	}
}

if (!function_exists('mnbt_app_pair_page')) {
	/** GET /app/v1/pair_page：非本 APP 扫码器的降级说明页（PRD §4.2） */
	function mnbt_app_pair_page($params, $ctx)
	{
		$code = strtoupper((string)($_GET['c'] ?? ''));
		$valid = preg_match('/^[A-Z2-8]{8}$/', $code);
		header('Content-Type: text/html; charset=UTF-8');
		echo '<!DOCTYPE html><html lang="zh-CN"><head><meta charset="utf-8">'
			. '<meta name="viewport" content="width=device-width,initial-scale=1"><title>MNBT 主机管理 APP</title></head>'
			. '<body style="font-family:system-ui,sans-serif;display:grid;place-items:center;min-height:90vh;margin:0;background:#f2f3f5">'
			. '<div style="max-width:420px;padding:28px 32px;background:#fff;border-radius:8px;box-shadow:0 2px 12px rgba(0,0,0,.06)">'
			. '<h2 style="font-size:17px;margin:0 0 10px">请使用 MNBT 主机管理 APP 扫码</h2>'
			. '<p style="font-size:14px;line-height:1.7;color:#555;margin:6px 0">本页面是设备配对二维码的落地页。'
			. '请在 MNBT 主机管理 APP 中使用「扫码配对」功能扫描本二维码。</p>';
		if ($valid) {
			echo '<p style="font-size:13px;color:#888">配对码：<code style="background:#f2f3f5;padding:2px 6px;border-radius:4px">' . htmlspecialchars($code, ENT_QUOTES, 'UTF-8') . '</code></p>';
		}
		echo '</div></body></html>';
		return true;
	}
}

if (!function_exists('mnbt_app_action_pair')) {
	/** POST /app/v1/pair（无令牌）：校验配对码 -> 换发令牌 -> 返回 site+host（交接 §2.4） */
	function mnbt_app_action_pair($params, $ctx)
	{
		global $DB;
		$ip = mnbt_app_client_ip();
		mnbt_app_throttle_check('app_pair', $ip, 5, 600);

		$code = strtoupper(trim((string)mnbt_app_p('code')));
		$device_id = mb_substr((string)mnbt_app_p('device_id'), 0, 64);
		if ($device_id === '') mnbt_app_err(1007, '缺少 device_id');

		list($ok, $zj_id, $reason) = mnbt_app_pair_consume($code);
		if (!$ok) {
			mnbt_app_throttle_hit('app_pair', $ip, 600);
			$messages = [
				'bad_format' => '配对码格式错误',
				'not_found' => '配对码不存在或已失效',
				'used' => '配对码已被使用，请重新生成',
				'expired' => '配对码已过期，请重新生成',
			];
			mnbt_app_err(1002, $messages[$reason] ?? '配对失败');
		}

		$yhc = $DB->get_row_prepare("SELECT * FROM `MN_zj` WHERE `id`=? LIMIT 1", [$zj_id]);
		if (!is_array($yhc)) mnbt_app_err(1005, '主机已被删除，无法配对');
		if ($yhc['qk'] === 'false') mnbt_app_err(1004, '主机已被封禁，无法配对');
		if (strtotime($GLOBALS['date']) - strtotime($yhc['datae']) > 0 && $yhc['datae'] !== '0000-00-00') {
			mnbt_app_err(1003, '主机已到期，无法配对');
		}

		$token = mnbt_app_device_issue(
			$zj_id, $device_id,
			mb_substr((string)mnbt_app_p('device_name', 'Android 设备'), 0, 120),
			mb_substr((string)mnbt_app_p('device_model', ''), 0, 120),
			mb_substr((string)mnbt_app_p('app_version', ''), 0, 20),
			mb_substr((string)mnbt_app_p('android_version', ''), 0, 20)
		);
		mnbt_app_throttle_clear('app_pair', $ip);
		logjl($yhc['user'], 'APP配对', '设备' . $device_id . '完成APP配对', '成功', $DB);

		// site.base 仅在与本请求同源时被 APP 采纳（防串站）；这里返回本请求实际命中的根
		$GLOBALS['yhc'] = $yhc;
		mnbt_app_ok('配对成功', [
			'token' => $token,
			'site' => [
				'base' => mnbt_app_site_base(),
				'name' => (string)($GLOBALS['conf']['name'] ?? 'MNBT'),
				'mnbt_version' => function_exists('mnbt_version_num') ? mnbt_version_num() : '',
			],
			'host' => mnbt_app_host_row(),
		]);
	}
}

if (!function_exists('mnbt_app_action_device_info')) {
	function mnbt_app_action_device_info($params, $ctx)
	{
		$list = array_keys(mnbt_app_actions());
		mnbt_app_ok('ok', [
			'bridge_version' => MOBILE_BRIDGE_VERSION,
			'mnbt_version' => function_exists('mnbt_version_num') ? mnbt_version_num() : '',
			'actions' => $list,
			'capabilities' => ['file_bytes_transparent', 'chunked_upload', 'signed_download'],
		]);
	}
}

if (!function_exists('mnbt_app_action_host_overview')) {
	/** 先跑核心 sxsyxx 刷新用量（输出静默吞掉），再从刷新后的 MN_zj 组包 */
	function mnbt_app_action_host_overview($params, $ctx)
	{
		global $DB, $yhc;
		$level = ob_get_level();
		ob_start();
		try {
			mnbt_app_core_include('site.php', 'sxsyxx', []);
		} catch (Throwable $e) {
			error_log('[mobile_bridge] sxsyxx refresh failed: ' . $e->getMessage());
		}
		while (ob_get_level() > $level) ob_end_clean();

		$fresh = $DB->get_row_prepare("SELECT * FROM `MN_zj` WHERE `id`=? LIMIT 1", [(int)$yhc['id']]);
		if (is_array($fresh)) $yhc = $fresh;

		$row = mnbt_app_host_row();
		mnbt_app_ok('ok', [
			// HostOverview 读的是 web_quota/sql_quota/traffic_quota（不是 web/sql/traffic）
			'web_quota' => $row['web_quota'],
			'sql_quota' => $row['sql_quota'],
			'traffic_quota' => $row['traffic_quota'],
			// 面板没有按主机采集 CPU/内存/inode 的入口（jk.php 只有空间与流量），一律回 null/0
			'cpu' => null,
			'mem' => null,
			'inode_used' => 0,
			'inode_total' => 0,
		]);
	}
}

if (!function_exists('mnbt_app_action_host_profile')) {
	function mnbt_app_action_host_profile($params, $ctx)
	{
		global $yhc;
		require_once SYSTEM_ROOT . 'bt_api.php';
		$apist = new bt_api_set($GLOBALS['btipe'], $GLOBALS['btkeye']);
		$cur = $apist->btapi_phpnowz($yhc['sqldz'] ?? '');
		$row = mnbt_app_host_row();
		$row['php'] = (string)($cur['phpversion'] ?? '');
		// 不下发 FTP/SQL 明文口令：APP 冻结的 HostProfile 没有凭证字段，面板侧也没有对应页面，
		// 多一次全量 profile 拉取就多一次明文泄露面。后续要做凭证页再单独开 action。
		mnbt_app_ok('ok', $row);
	}
}

if (!function_exists('mnbt_app_monitor_limit')) {
	/** APP 侧 OpsMonitorLimit 的口径：与 PRD §5.6 一致（核心 monitor.php 的 URL 下限其实是 15s，面板 UI 用 60s） */
	function mnbt_app_monitor_limit()
	{
		return ['max_tasks' => 5, 'url_min_cycle' => 60, 'resource_cycle' => 180];
	}
}

if (!function_exists('mnbt_app_monitor_resource_type')) {
	/** APP 的监控表单没有「资源类型」选择器，用 target 位置回传；非法值回落旧值或 web */
	function mnbt_app_monitor_resource_type($raw, $fallback = 'web')
	{
		$v = strtolower(trim((string)$raw));
		if (in_array($v, ['web', 'sql', 'traffic'], true)) return $v;
		$f = strtolower(trim((string)$fallback));
		return in_array($f, ['web', 'sql', 'traffic'], true) ? $f : 'web';
	}
}

if (!function_exists('mnbt_app_monitor_task_row')) {
	/**
	 * MN_monitor_task 原始行 -> APP 的 OpsMonitorTask / MonitorTask。
	 * PDO 常把 int 列回成数字串，kotlinx 对 Int 字段的串是硬解码失败，逐个显式转型。
	 */
	function mnbt_app_monitor_task_row($t)
	{
		if (!is_array($t)) return null;
		$type = ((string)($t['task_type'] ?? 'url') === 'resource') ? 'resource' : 'url';
		$code = null;
		if (isset($t['last_code']) && $t['last_code'] !== '') $code = (int)$t['last_code'];
		$enabled = strtolower((string)($t['enabled'] ?? 'true')) !== 'false';
		return [
			'id' => (int)($t['id'] ?? 0),
			'name' => (string)($t['name'] ?? ''),
			'type' => $type,
			// 资源任务没有 URL：target 回资源类型，APP 编辑时原样回传即可复原 resource_type
			'target' => $type === 'resource' ? (string)($t['resource_type'] ?? 'web') : (string)($t['url'] ?? ''),
			'cycle' => (int)($t['interval_seconds'] ?? 0),
			'status' => $enabled ? 'on' : 'off',
			'last_code' => $code,
			'last_time' => (string)($t['last_run'] ?? ''),
			'resource_threshold' => (int)($t['resource_threshold'] ?? 80),
			'last_status' => (string)($t['last_status'] ?? ''),
			'last_error' => (string)($t['last_error'] ?? ''),
			'fail_count' => (int)($t['fail_count'] ?? 0),
		];
	}
}

if (!function_exists('mnbt_app_action_monitor_list')) {
	function mnbt_app_action_monitor_list($params, $ctx)
	{
		mnbt_app_proxy_run('panel.php', 'monitor_list', [], function ($s, $c, $m, $d) {
			if (!$s) return [$s, $c, $m, []];
			$rows = [];
			$alert = 0;
			foreach (($d['tasks'] ?? []) as $t) {
				$row = mnbt_app_monitor_task_row($t);
				if ($row === null) continue;
				if ($row['last_code'] !== null && $row['last_code'] >= 400) $alert++;
				$rows[] = $row;
			}
			return [true, 0, 'ok', [
				'list' => $rows,
				'limit' => mnbt_app_monitor_limit(),
				'tasks' => $rows,
				'task_count' => count($rows),
				'max' => 5,
				'alert_count' => $alert,
			]];
		});
	}
}

if (!function_exists('mnbt_app_action_monitor_save')) {
	/**
	 * monitor.save：APP 只提交 id/name/type/target/cycle（mock 表单口径），
	 * 其余核心必填项按「新任务取默认、编辑沿用旧行」补齐，避免编辑一次就把
	 * method/通知/阈值重置成默认值。旧行必须在代理之前读——代理会重写 $_POST。
	 */
	function mnbt_app_action_monitor_save($params, $ctx)
	{
		global $DB, $yhc;
		$id = (int)mnbt_app_p('id', '0');
		$type = ((string)mnbt_app_p('task_type', mnbt_app_p('type', 'url')) === 'resource') ? 'resource' : 'url';
		$target = trim((string)mnbt_app_p('target', mnbt_app_p('url', '')));
		$cycle = (int)mnbt_app_p('cycle', mnbt_app_p('interval_seconds', '0'));
		$name = trim((string)mnbt_app_p('name'));

		$old = [];
		if ($id > 0) {
			$hit = $DB->get_row_prepare("SELECT * FROM MN_monitor_task WHERE id=? AND user=? LIMIT 1", [$id, $yhc['user']]);
			if (is_array($hit)) $old = $hit;
		}
		if ($name === '') $name = (string)($old['name'] ?? '');

		$core = [
			'id' => (string)$id,
			'name' => $name,
			'task_type' => $type,
			'url' => $type === 'url' ? $target : (string)($old['url'] ?? ''),
			'resource_type' => $type === 'resource'
				? mnbt_app_monitor_resource_type(mnbt_app_p('resource_type', $target), $old['resource_type'] ?? '')
				: (string)($old['resource_type'] ?? ''),
			'resource_threshold' => (string)(int)mnbt_app_p('resource_threshold', (string)($old['resource_threshold'] ?? 80)),
			'method' => (string)mnbt_app_p('method', (string)($old['method'] ?? 'GET')),
			'interval_seconds' => (string)($cycle > 0 ? $cycle : (int)($old['interval_seconds'] ?? 60)),
			'timeout_seconds' => (string)(int)mnbt_app_p('timeout_seconds', (string)($old['timeout_seconds'] ?? 10)),
			'status_rule' => (string)mnbt_app_p('status_rule', (string)($old['status_rule'] ?? 'eq')),
			'status_value' => (string)mnbt_app_p('status_value', (string)($old['status_value'] ?? '200')),
			'content_rule' => (string)mnbt_app_p('content_rule', (string)($old['content_rule'] ?? 'none')),
			'content_value' => (string)mnbt_app_p('content_value', (string)($old['content_value'] ?? '')),
			'fail_threshold' => (string)(int)mnbt_app_p('fail_threshold', (string)($old['fail_threshold'] ?? 1)),
			'notify_email' => mnbt_app_p('notify_email', $old['notify_email'] ?? 'true') === 'false' ? 'false' : 'true',
			'enabled' => mnbt_app_p('enabled', $old['enabled'] ?? 'true') === 'false' ? 'false' : 'true',
		];

		mnbt_app_proxy_run('monitor.php', 'monitor_save', $core, function ($s, $c, $m, $d) use ($id, $name, $type, $DB, $yhc) {
			$final_id = $id;
			if ($s && $final_id <= 0) {
				// 核心不回自增 id，按「本人同名最新一条」回查（列表本来就是 name+id DESC 展示）
				$hit = $DB->get_row_prepare("SELECT id FROM MN_monitor_task WHERE user=? AND name=? ORDER BY id DESC LIMIT 1", [$yhc['user'], $name]);
				$final_id = is_array($hit) ? (int)$hit['id'] : 0;
			}
			return [$s, $s ? 0 : $c, $m, [
				'id' => $final_id,
				'name' => $name,
				'type' => $type,
				'saved' => (bool)$s,
			]];
		});
	}
}

if (!function_exists('mnbt_app_action_monitor_delete')) {
	function mnbt_app_action_monitor_delete($params, $ctx)
	{
		$id = (int)mnbt_app_p('id');
		mnbt_app_proxy_run('monitor.php', 'monitor_del', ['id' => (string)$id], function ($s, $c, $m, $d) use ($id) {
			return [$s, $s ? 0 : $c, $m, ['id' => $id, 'deleted' => (bool)$s]];
		});
	}
}

if (!function_exists('mnbt_app_action_monitor_toggle')) {
	/** 核心 monitor_toggle 读 enabled='true'/'false'；APP 传的是 status=on/off，必须在这层转换 */
	function mnbt_app_action_monitor_toggle($params, $ctx)
	{
		$id = (int)mnbt_app_p('id');
		$status = strtolower((string)mnbt_app_p('status', mnbt_app_p('enabled', 'on')));
		$enabled = in_array($status, ['off', 'false', '0', 'disabled', 'stop'], true) ? 'false' : 'true';
		mnbt_app_proxy_run('monitor.php', 'monitor_toggle', ['id' => (string)$id, 'enabled' => $enabled], function ($s, $c, $m, $d) use ($id, $enabled) {
			return [$s, $s ? 0 : $c, $m, ['id' => $id, 'status' => $enabled === 'true' ? 'on' : 'off']];
		});
	}
}

if (!function_exists('mnbt_app_action_monitor_log')) {
	function mnbt_app_action_monitor_log($params, $ctx)
	{
		global $DB, $yhc;
		$id = (int)mnbt_app_p('id');
		$page = max(1, (int)mnbt_app_p('page', '1'));
		$size = (int)mnbt_app_p('page_size', '20');
		if (!in_array($size, [10, 15, 20, 25, 50, 100], true)) $size = 20;

		// 资源监控的日志 url 恒为空，用任务名补位，避免 APP 日志行末尾空白
		$fallback = '';
		if ($id > 0) {
			$hit = $DB->get_row_prepare("SELECT name FROM MN_monitor_task WHERE id=? AND user=? LIMIT 1", [$id, $yhc['user']]);
			if (is_array($hit)) $fallback = (string)$hit['name'];
		}

		mnbt_app_proxy_run('panel.php', 'monitor_log_list', ['id' => (string)$id, 'page' => (string)$page, 'page_size' => (string)$size], function ($s, $c, $m, $d) use ($fallback) {
			if (!$s) return [$s, $c, $m, []];
			$rows = [];
			foreach (($d['logs'] ?? []) as $l) {
				if (!is_array($l)) continue;
				$rows[] = [
					'time' => (string)($l['created_at'] ?? ''),
					'target' => (string)($l['url'] ?? '') !== '' ? (string)$l['url'] : $fallback,
					'code' => (int)($l['http_code'] ?? 0),
					'used_ms' => (int)($l['response_time'] ?? 0),
					'status' => (string)($l['check_status'] ?? ''),
					'error' => (string)($l['error_message'] ?? ''),
				];
			}
			return [true, 0, 'ok', [
				'list' => $rows,
				'total' => (int)($d['total'] ?? count($rows)),
				'page' => (int)($d['page'] ?? 1),
				'page_size' => (int)($d['page_size'] ?? 20),
				'total_pages' => (int)($d['total_pages'] ?? 1),
			]];
		});
	}
}

if (!function_exists('mnbt_app_action_notice_list')) {
	function mnbt_app_action_notice_list($params, $ctx)
	{
		global $DB, $yhc;
		$unread = (int)$DB->count_prepare("SELECT count(*) FROM MN_notice_log WHERE user=? AND is_read=?", [$yhc['user'], 'false']);
		$page = max(1, (int)mnbt_app_p('page', '1'));
		$size = (int)mnbt_app_p('page_size', '15');
		if (!in_array($size, [10, 15, 25, 50, 100], true)) $size = 15;
		mnbt_app_proxy_run('panel.php', 'notice_list', [
			'page' => (string)$page, 'page_size' => (string)$size,
			'type' => (string)mnbt_app_p('type'), 'level' => (string)mnbt_app_p('level'), 'keyword' => (string)mnbt_app_p('keyword'),
		], function ($s, $c, $m, $d) use ($unread) {
			if (!$s) return [$s, $c, $m, []];
			$rows = [];
			foreach (($d['logs'] ?? []) as $n) {
				if (!is_array($n)) continue;
				$rows[] = [
					'id' => (int)($n['id'] ?? 0),
					'title' => (string)($n['title'] ?? ''),
					'content' => (string)($n['content'] ?? ''),
					'time' => (string)($n['created_at'] ?? ''),
					'read' => ((string)($n['is_read'] ?? 'false') === 'true') ? 1 : 0,
					'type' => (string)($n['type'] ?? ''),
					'level' => (string)($n['level'] ?? 'info'),
				];
			}
			return [true, 0, 'ok', [
				'list' => $rows,
				'unread' => $unread,
				'total' => (int)($d['total'] ?? count($rows)),
				'page' => (int)($d['page'] ?? 1),
				'page_size' => (int)($d['page_size'] ?? 15),
				'total_pages' => (int)($d['total_pages'] ?? 1),
			]];
		});
	}
}

if (!function_exists('mnbt_app_action_notice_read')) {
	/** id 缺省或 0 = 全部标记已读（核心 notice_read 的口径） */
	function mnbt_app_action_notice_read($params, $ctx)
	{
		$id = (int)mnbt_app_p('id');
		mnbt_app_proxy_run('monitor.php', 'notice_read', ['id' => (string)$id], function ($s, $c, $m, $d) use ($id) {
			return [$s, $s ? 0 : $c, $m, ['id' => $id, 'read' => $s ? 1 : 0]];
		});
	}
}

if (!function_exists('mnbt_app_deploy_fields')) {
	/**
	 * MN_bs.inp（管理员搭的部署表单定义）-> APP 的 OpsDeployField[]。
	 * 面板表单只有文本/数字两种 input，没有 select，故 type 恒 'input'、options 恒空。
	 * key 用核心 yjbs 读取的 $_POST 键名（val['cz']），APP 按同名平铺提交。
	 * default 必须留空：isnr 是 placeholder，填进 default 会让 APP 把提示文字当用户输入提交。
	 */
	function mnbt_app_deploy_fields($inp)
	{
		if (is_string($inp)) $inp = json_decode($inp, true);
		if (!is_array($inp)) return [];
		$fields = [];
		foreach ($inp as $val) {
			if (!is_array($val)) continue;
			$key = (string)($val['cz'] ?? '');
			if ($key === '' || strtolower($key) === 'id') continue;
			$fields[] = [
				'key' => $key,
				'label' => (string)($val['ltaler'] ?? '') !== '' ? (string)$val['ltaler'] : $key,
				'type' => 'input',
				'default' => '',
				'options' => [],
				'required' => (string)($val['bt'] ?? '') === '是',
				'placeholder' => (string)($val['isnr'] ?? ''),
				'max_length' => (int)($val['cdxz'] ?? 0),
			];
		}
		return $fields;
	}
}

if (!function_exists('mnbt_app_action_deploy_list')) {
	function mnbt_app_action_deploy_list($params, $ctx)
	{
		mnbt_app_proxy_run('panel.php', 'deploy_list', [], function ($s, $c, $m, $d) {
			if (!$s) return [$s, $c, $m, []];
			$rows = [];
			foreach (($d['list'] ?? []) as $r) {
				if (!is_array($r)) continue;
				// 面板把图标存成 JSON 数组，APP 的 src 是单个 String：取第一个标量元素
				$src = $r['src'] ?? '';
				if (is_array($src)) {
					$first = '';
					foreach ($src as $one) {
						if (is_scalar($one)) { $first = (string)$one; break; }
					}
					$src = $first;
				}
				$tj = $r['tj'] ?? [];
				$rows[] = [
					'id' => (int)($r['id'] ?? 0),
					'name' => (string)($r['name'] ?? ''),
					// MN_bs 没有版本列，APP 的 ver 只能回空串（契约缺口，已记交接文档）
					'ver' => '',
					'src' => (string)$src,
					// 核心部署完把运行目录固定设为根目录（deploy.php:64 setyxml）
					'dir' => '/',
					'intro' => (string)($r['jc'] ?? ''),
					'price' => (string)($r['jg'] ?? ''),
					'used_by' => is_array($tj) ? count($tj) : 0,
					'on_sale' => in_array((string)($r['qk'] ?? ''), ['1', 'true', 'TRUE', 'True'], true),
				];
			}
			return [true, 0, 'ok', ['list' => $rows]];
		});
	}
}

if (!function_exists('mnbt_app_action_deploy_form')) {
	function mnbt_app_action_deploy_form($params, $ctx)
	{
		global $DB;
		$bs_id = (int)mnbt_app_p('bs_id', mnbt_app_p('id', '0'));
		$hit = $DB->get_row_prepare("SELECT * FROM MN_bs WHERE id=? LIMIT 1", [$bs_id]);
		mnbt_app_proxy_run('deploy.php', 'yjbsform', ['id' => (string)$bs_id], function ($s, $c, $m, $d) use ($bs_id, $hit) {
			if (!$s) return [$s, $c, $m, []];
			$sxpz = json_decode((string)($hit['sxpz'] ?? ''), true);
			if (!is_array($sxpz)) $sxpz = [];
			return [true, 0, $m !== '' ? $m : 'ok', [
				'id' => $bs_id,
				'name' => (string)($hit['name'] ?? ''),
				// sxpz = [所需网页空间, 所需数据库空间]，需要 SQL 空间即视为要建库
				'need_db' => (float)($sxpz[1] ?? 0) > 0,
				'create_dir' => '/',
				'fields' => mnbt_app_deploy_fields($d['form'] ?? ''),
				'intro' => (string)($hit['jc'] ?? ''),
			]];
		});
	}
}

if (!function_exists('mnbt_app_action_deploy_run')) {
	/**
	 * deploy.run 是破坏性动作：核心先把站点根目录文件全删（deploy.php:53 xzdelwj），
	 * 再上传解压源码包，每步 sleep 3s，整体可能跑到几十秒。
	 * 表单字段按核心 MN_bs.inp 的 cz 键平铺进 $_POST；id 保留给部署任务主键，
	 * 管理员若把某个字段命名为 id，核心本来就会自相冲突，这里一并跳过。
	 * 日志只记任务 id，绝不记表单值（用户在里面填的管理员密码是明文 POST）。
	 */
	function mnbt_app_action_deploy_run($params, $ctx)
	{
		$bs_id = (int)mnbt_app_p('bs_id', mnbt_app_p('id', '0'));
		$core = ['id' => (string)$bs_id];
		foreach ($_POST as $k => $v) {
			if ($k === 'gn' || $k === 'id' || is_array($v)) continue;
			$core[$k] = $v;
		}
		mnbt_app_proxy_run('deploy.php', 'yjbs', $core, function ($s, $c, $m, $d) use ($bs_id) {
			mnbt_app_log('一键部署', '执行了一键部署任务id=' . $bs_id, $s ? '成功' : '失败:' . $m);
			return [$s, $s ? 0 : $c, $m, ['task_id' => '', 'status' => $m !== '' ? $m : ($s ? '已完成' : '部署失败')]];
		});
	}
}

if (!function_exists('mnbt_app_actions')) {
	/** action 总表：分组文件各自贡献，合并后统一分发 */
	function mnbt_app_actions()
	{
		static $merged = null;
		if ($merged !== null) return $merged;
		$merged = array_merge(
			mnbt_app_actions_core(),
			mnbt_app_actions_site(),
			mnbt_app_actions_file(),
			mnbt_app_actions_db(),
			mnbt_app_actions_domain_ssl()
		);
		return $merged;
	}
}

if (!function_exists('mnbt_app_dispatch')) {
	/** 路由总入口：/app/v1/{action} */
	function mnbt_app_dispatch($params, $ctx)
	{
		$action = isset($params['action']) ? (string)$params['action'] : '';
		$action = preg_replace('/[^a-z0-9_.]/', '', strtolower($action));
		if ($action === '') mnbt_app_err(1010, '未知接口');
		$GLOBALS['mnbt_app_action'] = $action;
		$GLOBALS['mnbt_app_dispatched'] = true;
		mnbt_app_register_shutdown();

		$defs = mnbt_app_actions();
		if (!isset($defs[$action])) mnbt_app_err(1010, '未知接口：' . $action);

		$need = mnbt_app_required_bridge_version($action);
		if ($need !== '' && version_compare(MOBILE_BRIDGE_VERSION, $need, '<')) {
			mnbt_app_err(1010, '需升级服务端插件（本接口要求 bridge ' . $need . '，当前 ' . MOBILE_BRIDGE_VERSION . '）');
		}

		$def = $defs[$action];
		if ($def['auth'] !== 'none') {
			mnbt_app_build_context();
		}
		$cb = $def['cb'];
		$cb($params, $ctx);
		mnbt_app_err_5000('handler_returned_without_reply:' . $action);
	}
}

if (!function_exists('mnbt_app_actions_core')) {
	function mnbt_app_actions_core()
	{
		return [
			'pair' => ['auth' => 'none', 'cb' => 'mnbt_app_action_pair'],
			'device.ping' => ['auth' => 'ctx', 'cb' => function () { mnbt_app_ok('ok', ['server_time' => date('Y-m-d H:i:s')]); }],
			'device.info' => ['auth' => 'ctx', 'cb' => 'mnbt_app_action_device_info'],
			'device.revoke' => ['auth' => 'ctx', 'cb' => function () {
				global $device;
				mnbt_app_device_revoke($device);
				mnbt_app_log('APP设备', '吊销了设备' . (string)$device['device_id'], '成功');
				mnbt_app_ok('已吊销', ['revoked' => true]);
			}],
			'host.overview' => ['auth' => 'ctx', 'cb' => 'mnbt_app_action_host_overview'],
			'host.profile' => ['auth' => 'ctx', 'cb' => 'mnbt_app_action_host_profile'],

			// 设备列表（面板数据同源，供 APP 侧查看）
			'device.list' => ['auth' => 'ctx', 'cb' => function () {
				global $yhc;
				$rows = mnbt_app_device_list($yhc['id']);
				$list = [];
				foreach ($rows as $r) {
					if ((int)$r['status'] !== 1) continue;
					$list[] = [
						'id' => (int)$r['id'],
						'device_id' => $r['device_id'],
						'device_name' => $r['device_name'],
						'device_model' => $r['device_model'],
						'app_version' => $r['app_version'],
						'created_at' => $r['created_at'],
						'last_seen_at' => $r['last_seen_at'],
						'last_ip' => $r['last_ip'],
					];
				}
				mnbt_app_ok('ok', ['list' => $list]);
			}],

			// 站点设置中的直通项（转换细节在 actions_site.php）
			'site.init' => ['auth' => 'ctx', 'cb' => function () {
				mnbt_app_site_init((string)mnbt_app_p('section', 'php'));
			}],
			'monitor.list' => ['auth' => 'ctx', 'cb' => 'mnbt_app_action_monitor_list'],
			'monitor.save' => ['auth' => 'ctx', 'cb' => 'mnbt_app_action_monitor_save'],
			'monitor.delete' => ['auth' => 'ctx', 'cb' => 'mnbt_app_action_monitor_delete'],
			'monitor.toggle' => ['auth' => 'ctx', 'cb' => 'mnbt_app_action_monitor_toggle'],
			'monitor.log' => ['auth' => 'ctx', 'cb' => 'mnbt_app_action_monitor_log'],
			'notice.list' => ['auth' => 'ctx', 'cb' => 'mnbt_app_action_notice_list'],
			'notice.read' => ['auth' => 'ctx', 'cb' => 'mnbt_app_action_notice_read'],
			'deploy.list' => ['auth' => 'ctx', 'cb' => 'mnbt_app_action_deploy_list'],
			'deploy.form' => ['auth' => 'ctx', 'cb' => 'mnbt_app_action_deploy_form'],
			'deploy.run' => ['auth' => 'ctx', 'cb' => 'mnbt_app_action_deploy_run'],
		];
	}
}
