<?php
/**
 * mobile_bridge - 数据库组 action（PRD §5.4 / 交接 §3.5）
 *
 * 面板端的真实能力边界（对账结论，逐条落到 1010 或新增 action）：
 * - 一台主机 = 一个数据库 + 一个账号（MN_zj 的 sqluser/sqlpass/hxd），bt_api 里根本没有建库/删库原语
 *   （只有 Databaseadd=发起备份、DatabaseDelete=删备份文件、Databaserestore、databascx、sqlkjhq、访问权限）。
 *   APP 的「新建数据库/删除数据库」不能拿备份动作冒充：db.create、db.delete 一律 1010，
 *   真正的备份增删走本文件新增的 db.backup.create / db.backup.delete。
 * - APP 的 DbBackup DTO 只有 {name,size,time} 没有 id，而核心删备份要宝塔的数字 id：
 *   所以删除必须按文件名反查（backup 列表在 bridge 内读一次）。
 * - db.backup.download 回的是节点直连的 token 型 URL（宝塔 /down/<token>），APP 用 rawClient GET。
 * - db.sql.import 两种形态：① APP 现在的单次 multipart（file 字段）——bridge 把文件落到站点根目录
 *   再交核心 sqldr 导入，导入完删掉自己落的临时文件；② 超大 SQL 的正规姿势：先 file.upload 分片传进
 *   站点目录，再只传 path/filename（免 413）。两种都靠核心做 .sql 校验与数据库空间配额。
 * - db.delete_all 代理核心 Delalldatabase（localhost mysqli 与面板同进程、同可达性），
 *   bridge 只额外做一次只读 SHOW TABLES 计数喂 APP 的文案，连不上库就回 0 不影响清空本身。
 * - 日志分工：核心 database.php 的 add/del/download/restore 与 site.php 的 sqldr 自带 logjl，bridge 不重复记；
 *   Delalldatabase 的 logjl 排在 json_exit 之后永远跑不到、databaseaq1 核心压根没记 —— 这两条由 bridge 补。
 */
if (!defined('IN_CRONLITE')) exit;

if (!function_exists('mnbt_app_backup_time')) {
	/** 宝塔备份列表时间字段 -> APP 的 DbBackup.time（String）：数字按秒转，字符串原样 */
	function mnbt_app_backup_time($v)
	{
		if (is_numeric($v)) return date('Y-m-d H:i:s', (int)$v);
		$s = trim((string)$v);
		if ($s === '') return '';
		$ts = strtotime($s);
		return $ts === false ? $s : date('Y-m-d H:i:s', $ts);
	}
}

if (!function_exists('mnbt_app_backup_name')) {
	/** 宝塔备份项 -> 文件名（只取 basename，节点可能回带路径） */
	function mnbt_app_backup_name($item)
	{
		if (!is_array($item)) return '';
		$raw = mnbt_app_text($item['filename'] ?? ($item['name'] ?? ($item['url'] ?? '')), '');
		$raw = str_replace('\\', '/', trim($raw));
		return $raw === '' ? '' : basename($raw);
	}
}

if (!function_exists('mnbt_app_backup_rows')) {
	/** 宝塔备份列表 -> APP 的 DbBackup 列表（size 必须是整数：kotlinx 的 Long 收到小数直接解码失败） */
	function mnbt_app_backup_rows($list)
	{
		if (!is_array($list)) return [];
		$rows = [];
		foreach ($list as $item) {
			if (!is_array($item)) continue;
			$name = mnbt_app_backup_name($item);
			if ($name === '') continue;
			$rows[] = [
				'id' => is_scalar($item['id'] ?? null) ? (string)$item['id'] : '',
				'name' => $name,
				'size' => mnbt_app_bytes($item['size'] ?? ($item['dx'] ?? 0)),
				'time' => mnbt_app_backup_time($item['addtime'] ?? ($item['time'] ?? '')),
			];
		}
		return $rows;
	}
}

if (!function_exists('mnbt_app_backup_list_raw')) {
	/** 备份原始列表（bridge 代理前就要拿到 id/文件名，只能自己读一次 bt_api） */
	function mnbt_app_backup_list_raw()
	{
		global $api, $yhc;
		if (!is_object($api) || !method_exists($api, 'Databasebackuplist')) return [];
		$r = @$api->Databasebackuplist($yhc['hxd'] ?? '');
		if (!is_array($r)) return [];
		$data = $r['data'] ?? [];
		return is_array($data) ? $data : [];
	}
}

if (!function_exists('mnbt_app_backup_id_by_name')) {
	/** 文件名 -> 宝塔备份 id（找不到回 0） */
	function mnbt_app_backup_id_by_name($name)
	{
		$name = basename(str_replace('\\', '/', trim((string)$name)));
		if ($name === '') return 0;
		foreach (mnbt_app_backup_list_raw() as $item) {
			if (!is_array($item)) continue;
			if (mnbt_app_backup_name($item) !== $name) continue;
			return is_scalar($item['id'] ?? null) ? (int)$item['id'] : 0;
		}
		return 0;
	}
}

if (!function_exists('mnbt_app_backup_quota')) {
	/** 备份次数配额（核心 add/del 会改写 MN_zj.backup，回显要给 APP 改过的最新值，所以重读一遍） */
	function mnbt_app_backup_quota()
	{
		global $DB, $yhc;
		$row = null;
		try {
			$row = $DB->get_row_prepare("SELECT `backup` FROM `MN_zj` WHERE `id`=? LIMIT 1", [(int)($yhc['id'] ?? 0)]);
		} catch (Throwable $e) {
			$row = null;
		}
		$json = is_array($row) && isset($row['backup']) ? (string)$row['backup'] : (string)($yhc['backup'] ?? '');
		$quota = json_decode($json, true) ?: [];
		return [
			'used' => (float)($quota['dq'] ?? 0),
			'max' => (float)($quota['max'] ?? 0),
			'unit' => '次',
		];
	}
}

if (!function_exists('mnbt_app_db_charset')) {
	/** 数据库字符集（bt GetInfo 的 charset 字段；节点不可达或字段缺失回空串，APP 留空显示） */
	function mnbt_app_db_charset()
	{
		global $api, $yhc;
		$name = (string)($yhc['sqluser'] ?? '');
		if ($name === '' || !is_object($api) || !method_exists($api, 'sqlkjhq')) return '';
		try {
			$r = @$api->sqlkjhq($name);
		} catch (Throwable $e) {
			return '';
		}
		if (!is_array($r)) return '';
		$rows = $r['data'] ?? $r['msg'] ?? $r;
		if (isset($rows[0]) && is_array($rows[0])) $rows = $rows[0];
		if (!is_array($rows)) return '';
		return mnbt_app_text($rows['charset'] ?? ($rows['db_charset'] ?? ''), '');
	}
}

if (!function_exists('mnbt_app_db_table_count')) {
	/** 只读数一遍表数量（供 db.delete_all 的回显计数）；连不上库回 -1，不影响清空动作本身 */
	function mnbt_app_db_table_count()
	{
		global $yhc;
		if (!function_exists('mysqli_connect')) return -1;
		$user = (string)($yhc['sqluser'] ?? '');
		if ($user === '') return -1;
		$conn = @mysqli_connect('localhost', $user, (string)mnbt_secret_decrypt($yhc['sqlpass'] ?? ''), $user);
		if (!$conn) return -1;
		$count = -1;
		$res = @mysqli_query($conn, "SHOW TABLES");
		if ($res) {
			$count = (int)@mysqli_num_rows($res);
			if (function_exists('mysqli_free_result')) @mysqli_free_result($res);
		}
		@mysqli_close($conn);
		return $count;
	}
}

if (!function_exists('mnbt_app_db_access_in')) {
	/** APP 的 host=localhost|% -> 面板的 dataAccess=127.0.0.1|%（与网页端写入口径一致，便于读回） */
	function mnbt_app_db_access_in($v)
	{
		$s = strtolower(trim((string)$v));
		if ($s === '' || $s === 'localhost' || $s === '127.0.0.1' || $s === 'local') return '127.0.0.1';
		return (string)$v;
	}
}

if (!function_exists('mnbt_app_db_access_out')) {
	/** 面板/宝塔的回读值 -> APP 词汇（APP 用 access=="localhost" 判「仅本地」，回 127.0.0.1 会被判成允许远程） */
	function mnbt_app_db_access_out($v)
	{
		$s = trim((string)$v);
		if ($s === '' || $s === '127.0.0.1' || strtolower($s) === 'local') return 'localhost';
		return $s;
	}
}

if (!function_exists('mnbt_app_actions_db')) {
	function mnbt_app_actions_db()
	{
		return [
			'db.list' => ['auth' => 'ctx', 'cb' => function () {
				global $yhc;
				mnbt_app_proxy_run('panel.php', 'backup_list', [], function ($s, $c, $m, $d) use ($yhc) {
					if (!$s) return [$s, $c, $m, $d];
					$row = mnbt_app_host_row();
					$user = (string)($yhc['sqluser'] ?? '');
					return [true, 0, 'ok', [
						'list' => [[
							'name' => $user,
							'username' => $user,
							'charset' => mnbt_app_db_charset(),
							'state' => $user === '' ? '' : 'RUNNING',
							'addtime' => (string)($yhc['data'] ?? ''),
							'backups' => mnbt_app_backup_rows($d['list'] ?? []),
						]],
						'db_id' => $d['db_id'] ?? $yhc['hxd'] ?? '',
						'backup_count' => (int)($d['count'] ?? 0),
						'quota' => mnbt_app_backup_quota(),
						'sql_space' => $row['sql_quota'],
					]];
				});
			}],

			// 面板没有建库原语（一主机一库一账号，随主机开通产生），不拿备份冒充
			'db.create' => ['auth' => 'ctx', 'cb' => function () {
				mnbt_app_err(1010, '本面板一台主机只有一个数据库，建库随主机开通完成，没有「新建数据库」接口；需要立刻备份请调 db.backup.create');
			}],
			'db.delete' => ['auth' => 'ctx', 'cb' => function () {
				mnbt_app_err(1010, '本面板不支持删除主机数据库（删库会连带主机账号失效）；面板的删除动作只针对备份文件，请调 db.backup.delete 并传备份文件名');
			}],

			'db.backup.create' => ['auth' => 'ctx', 'cb' => function () {
				global $yhc;
				$user = (string)($yhc['sqluser'] ?? '');
				mnbt_app_proxy_run('database.php', 'databaseadd', ['id' => $yhc['hxd'] ?? ''], function ($s, $c, $m, $d) use ($user) {
					return [$s, $c, $m, [
						'action' => 'backup_create',
						'name' => $user,
						'username' => $user,
						'quota' => mnbt_app_backup_quota(),
					]];
				});
			}],
			'db.backup.delete' => ['auth' => 'ctx', 'cb' => function () {
				// APP 的备份行只有文件名，核心删备份要数字 id：先按文件名反查
				$id = mnbt_app_p('id');
				$name = mnbt_app_p('name', mnbt_app_p('file', mnbt_app_p('filename')));
				if (!is_numeric($id) || (int)$id <= 0) {
					if ($name === '') mnbt_app_err(1007, '缺少备份文件名（name）或备份 id');
					$id = (string)mnbt_app_backup_id_by_name($name);
					if ((int)$id <= 0) mnbt_app_err(1007, '备份文件不存在：' . $name);
				}
				$file = $name !== '' ? basename(str_replace('\\', '/', $name)) : ('id=' . (int)$id);
				mnbt_app_proxy_run('database.php', 'databasedel', ['id' => (string)(int)$id], function ($s, $c, $m, $d) use ($file) {
					return [$s, $c, $m, [
						'action' => 'backup_delete',
						'name' => $file,
						'deleted' => (bool)$s,
						'quota' => mnbt_app_backup_quota(),
					]];
				});
			}],

			'db.delete_all' => ['auth' => 'ctx', 'cb' => function () {
				// 核心分支的 logjl 在 json_exit 之后，永远跑不到：日志与计数由 bridge 补
				$before = mnbt_app_db_table_count();
				mnbt_app_proxy_run('database.php', 'Delalldatabase', [], function ($s, $c, $m, $d) use ($before) {
					mnbt_app_log('清空数据库', '在APP清空了所有数据库表', $s ? '成功' : '失败:' . $m);
					if (!$s) return [$s, $c, $m, ['deleted' => 0, 'remaining' => max(0, $before)]];
					return [true, 0, $m, [
						'deleted' => $before > 0 ? $before : 0,
						'remaining' => 0,
					]];
				});
			}],

			'db.backup.download' => ['auth' => 'ctx', 'cb' => function () {
				$file = mnbt_app_p('file', mnbt_app_p('filename', mnbt_app_p('name')));
				if ($file === '') mnbt_app_err(1007, '缺少备份文件名(file)');
				$name = basename(str_replace('\\', '/', $file));
				mnbt_app_proxy_run('database.php', 'databasedownload', ['filename' => $name], function ($s, $c, $m, $d) use ($name) {
					if (!$s) return [$s, $c, $m, $d];
					return [true, 0, $m, [
						'file' => $name,
						'url' => mnbt_app_text($d['url'] ?? '', ''),
						'size' => 0,
					]];
				});
			}],

			'db.backup.restore' => ['auth' => 'ctx', 'cb' => function () {
				global $yhc;
				$file = mnbt_app_p('name', mnbt_app_p('filename', mnbt_app_p('file')));
				if ($file === '') mnbt_app_err(1007, '缺少备份文件名(name)');
				$name = basename(str_replace('\\', '/', $file));
				// 本主机只有一个库：目标库名一律取 sqluser，APP 的 data_account 只作校验不参与拼接
				$user = (string)($yhc['sqluser'] ?? '');
				$target = mnbt_app_p('data_account', mnbt_app_p('db'));
				if ($target !== '' && $user !== '' && $target !== $user) {
					mnbt_app_err(1007, '本主机只有一个数据库 ' . $user . '，无法恢复到 ' . $target);
				}
				mnbt_app_proxy_run('database.php', 'databaserestore', ['filename' => $name, 'user' => $user], function ($s, $c, $m, $d) use ($name, $user) {
					// 宝塔恢复成功的历史文案是「备份成功!」，失败含「失败/错误」由 adapter 翻成 success=false
					return [$s, $c, $m, ['name' => $name, 'db' => $user, 'restored' => (bool)$s]];
				});
			}],

			'db.access.set' => ['auth' => 'ctx', 'cb' => function () {
				global $yhc;
				$access = mnbt_app_db_access_in(mnbt_app_p('host', mnbt_app_p('access')));
				$port = (int)mnbt_app_p('port', '0');
				$user = (string)($yhc['sqluser'] ?? '');
				mnbt_app_proxy_run('database.php', 'databaseaq1', ['dataAccess' => $access], function ($s, $c, $m, $d) use ($access, $port, $user) {
					// 核心 databaseaq1 不记日志，由 bridge 记（权限值不含口令，可入日志）
					mnbt_app_log('数据库安全', '修改了数据库访问权限为' . $access, $s ? '成功' : '失败:' . $m);
					return [$s, $c, $m, [
						'db' => $user,
						'access' => mnbt_app_db_access_out($access),
						'port' => $port,
						'applied' => (bool)$s,
					]];
				});
			}],
			'db.access.get' => ['auth' => 'ctx', 'cb' => function () {
				global $yhc;
				mnbt_app_proxy_run('panel.php', 'set_init', ['section' => 'mysqlcz'], function ($s, $c, $m, $d) use ($yhc) {
					if (!$s) return [$s, $c, $m, $d];
					return [true, 0, 'ok', [
						'db' => (string)($yhc['sqluser'] ?? ''),
						'access' => mnbt_app_db_access_out($d['access'] ?? ''),
						'port' => 0,
						'applied' => false,
					]];
				});
			}],

			// 大文件导入的两种形态见文件头：multipart 直投 / 先 file.upload 再传 path
			'db.sql.import' => ['auth' => 'ctx', 'cb' => function () {
				global $yhc;
				mnbt_app_core_include('file.php', '__bridge_defs__', null);
				if (!function_exists('mnbt_file_abs')) mnbt_app_err_5000('missing_core_api:file.php');

				$user = (string)($yhc['sqluser'] ?? '');
				$target = mnbt_app_p('data_account', mnbt_app_p('db'));
				if ($target !== '' && $user !== '' && $target !== $user) {
					mnbt_app_err(1007, '本主机只有一个数据库 ' . $user . '，无法导入到 ' . $target);
				}

				$dir = '/';
				$name = '';
				$tmp_abs = '';
				$upload = $_FILES['file'] ?? null;
				if (is_array($upload) && is_uploaded_file((string)($upload['tmp_name'] ?? ''))) {
					if (!empty($upload['error'])) mnbt_app_err(1007, '上传的 SQL 文件有误，请重试');
					// 客户端文件名不可信：只保留时间戳+摘要生成的安全名，原名仅用于回显
					$raw = basename(str_replace('\\', '/', (string)($upload['name'] ?? 'dump.sql')));
					$name = 'app_' . date('YmdHis') . '_' . substr(md5($raw . microtime()), 0, 6) . '.sql';
					$tmp_abs = rtrim(mnbt_file_abs('/'), '/') . '/' . $name;
					if (!@move_uploaded_file($upload['tmp_name'], $tmp_abs)) {
						mnbt_app_err(1007, 'SQL 文件落盘失败，请改用分片上传（file.upload）后再导入');
					}
				} else {
					$full = mnbt_app_p('file', mnbt_app_p('path'));
					[$dir, $name] = mnbt_app_split_path($full);
					if ($name === '') $name = (string)mnbt_app_p('filename');
					if ($name === '') mnbt_app_err(1007, '缺少 SQL 文件：multipart 直接上传 file 字段，或先用 file.upload 把 .sql 传到站点目录再传 path');
				}

				$display = basename(str_replace('\\', '/', (string)($upload['name'] ?? $name)));
				mnbt_app_proxy_run('site.php', 'sqldr', ['path' => $dir, 'filename' => $name], function ($s, $c, $m, $d) use ($tmp_abs, $display, $user) {
					// 核心 site.php:41 已记导入日志；这里只清掉 bridge 自己落的临时文件
					if ($tmp_abs !== '' && is_file($tmp_abs)) @unlink($tmp_abs);
					return [$s, $c, $m, ['file' => $display, 'db' => $user, 'imported' => (bool)$s]];
				});
			}],
		];
	}
}
