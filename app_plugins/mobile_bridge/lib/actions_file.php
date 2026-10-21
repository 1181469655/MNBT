<?php
/**
 * mobile_bridge - 文件管理组 action（PRD §5.3 / 交接 §3.4）
 *
 * - read / delete_batch / copy / upload_prepare / upload 在插件内自实现并直调 bt_api：
 *   核心对应分支以 json_exit 收尾会截断 reshape，且 GBK 文件内容撞上 json 编码失败；
 *   这些分支只 include 核心 file.php 取路径校验函数（不动 $_POST），校验规则照抄核心。
 * - 其余分支直通核心 file.php，由 adapter 归一化包络。
 * - 核心上传是宝塔分片协议（start=累计字节偏移、size=文件总字节数且全程不可变），APP 发的是
 *   index/total 分片序号：bridge 按 主机|目录|文件名 记偏移量做序号->偏移翻译，index<=1 视为重头开始。
 *   宝塔按 name.<size>.upload.tmp 落分片，所以 file.upload 必须带 size（总字节数），缺失直接 1007。
 * - file.download 直接返回节点外链（宝塔 token 型 URL，24h），APP 用 rawClient GET。
 */
if (!defined('IN_CRONLITE')) exit;

if (!function_exists('mnbt_app_upload_offset_file')) {
	function mnbt_app_upload_offset_file($key)
	{
		$dir = dirname(__DIR__) . '/runtime/upload_state';
		if (!is_dir($dir)) @mkdir($dir, 0755, true);
		return $dir . '/' . hash('sha256', $key) . '.json';
	}
}

if (!function_exists('mnbt_app_upload_offset_get')) {
	function mnbt_app_upload_offset_get($key)
	{
		$file = mnbt_app_upload_offset_file($key);
		if (!is_file($file)) return 0.0;
		$data = json_decode((string)@file_get_contents($file), true);
		if (!is_array($data) || (time() - (int)($data['ts'] ?? 0)) > 86400) return 0.0;
		return (float)($data['offset'] ?? 0);
	}
}

if (!function_exists('mnbt_app_upload_offset_set')) {
	function mnbt_app_upload_offset_set($key, $offset)
	{
		@file_put_contents(mnbt_app_upload_offset_file($key), json_encode(['offset' => $offset, 'ts' => time()]), LOCK_EX);
	}
}

if (!function_exists('mnbt_app_bytes')) {
	/** 宝塔大小字段 -> 整数字节（APP 的 FileEntry.size 是 Long，浮点/字符串都会让 kotlinx 解码失败） */
	function mnbt_app_bytes($v)
	{
		if (is_numeric($v)) return (int)round((float)$v);
		$v = trim((string)$v);
		if ($v === '') return 0;
		if (preg_match('/^([\d.]+)\s*([KMGT]?)B?$/i', $v, $m)) {
			$mul = ['' => 1, 'K' => 1024, 'M' => 1048576, 'G' => 1073741824, 'T' => 1099511627776];
			$unit = strtoupper($m[2]);
			return (int)round((float)$m[1] * ($mul[$unit] ?? 1));
		}
		return 0;
	}
}

if (!function_exists('mnbt_app_ts')) {
	/** 宝塔时间字段 -> unix 秒（数字直接用，'2024-01-01 10:00:00' 走 strtotime） */
	function mnbt_app_ts($v)
	{
		if (is_numeric($v)) return (int)$v;
		$v = trim((string)$v);
		if ($v === '') return 0;
		$ts = strtotime($v);
		return $ts === false ? 0 : (int)$ts;
	}
}

if (!function_exists('mnbt_app_actions_file')) {
	function mnbt_app_actions_file()
	{
		return [
			// 按层返回（核心 file_list 本身就是单层；交接 §4-B11 要求 mock 的递归展平改掉即可）
			'file.list' => ['auth' => 'ctx', 'cb' => function () {
				mnbt_app_proxy_run('file.php', 'file_list', [
					'path' => mnbt_app_p('path', '/'),
					'page' => mnbt_app_p('page', '1'),
					'limit' => mnbt_app_p('limit', '500'),
					'sort' => mnbt_app_p('sort', 'name'),
					'sortOrder' => mnbt_app_p('sortOrder', 'asc'),
				], function ($s, $c, $m, $d) {
					if (!$s) return [$s, $c, $m, $d];
					$cwd = (string)($d['path'] ?? '/');
					$list = [];
					foreach (($d['rows'] ?? []) as $row) {
						if (!is_array($row)) continue;
						$name = (string)($row['name'] ?? '');
						if ($name === '') continue;
						$list[] = [
							'name' => $name,
							'path' => rtrim($cwd, '/') . '/' . $name,
							'type' => (($row['type'] ?? 'file') === 'dir') ? 'dir' : 'file',
							'size' => mnbt_app_bytes($row['size'] ?? 0),
							'mtime' => mnbt_app_ts($row['mtime'] ?? 0),
							'perms' => '',
							'owner' => '',
							'group' => '',
						];
					}
					return [true, 0, 'ok', [
						'cwd' => $cwd,
						'path' => $cwd,
						'list' => $list,
						'total' => (int)($d['total'] ?? count($list)),
					]];
				});
			}],
			'file.read' => ['auth' => 'ctx', 'cb' => function () {
				global $api;
				$path = mnbt_app_p('path', mnbt_app_p('file'));
				// 借用核心 file.php 的路径校验/读取语义，但不经其 JSON 出口：
				// GBK/二进制字节直接 base64 回传，避免核心 json_encode 对非法 UTF-8 整体失败
				$defs = mnbt_app_core_include('file.php', '__bridge_defs__', null);
				if ($defs === false || !function_exists('mnbt_file_norm_path')) mnbt_app_err_5000('missing_core_api:file.php');
				$rel = mnbt_file_norm_path((string)$path);
				if ($rel === null || $rel === '/') mnbt_app_err(1007, '路径格式错误');
				$r = $api->hqwjnr(mnbt_file_abs($rel)) ?: [];
				if (!is_array($r)) $r = [];
				$content = $r['DATA'] ?? $r['data'] ?? null;
				if ($content !== null && !is_scalar($content)) $content = null;
				if ($content === null) {
					if (!empty($r['status'])) {
						$content = '';
					} else {
						mnbt_app_err(1009, (string)($r['msg'] ?? '文件读取失败'));
					}
				}
				$content = (string)$content;
				$encoding = '';
				foreach (['ENCODING', 'encoding'] as $k) {
					if (!empty($r[$k]) && is_string($r[$k])) { $encoding = $r[$k]; break; }
				}
				mnbt_app_ok('ok', [
					'path' => $rel,
					'name' => basename($rel),
					'encoding' => $encoding,
					'size' => strlen($content),
					'content_base64' => base64_encode($content),
					// 非法 UTF-8（GBK 等）时 content 仅 lossy 兜底，APP 首选 content_base64
					'content' => mb_check_encoding($content, 'UTF-8') ? $content : mb_convert_encoding($content, 'UTF-8', 'UTF-8'),
				]);
			}],
			'file.save' => ['auth' => 'ctx', 'cb' => function () {
				$path = mnbt_app_p('path', mnbt_app_p('file'));
				// content 保持原字节，禁止任何转义/再编码
				$content = (string)($_POST['content'] ?? '');
				mnbt_app_proxy_run('file.php', 'file_save', [
					'path' => $path,
					'content' => $content,
				], function ($s, $c, $m, $d) use ($path, $content) {
					return [$s, $c, $m, ['path' => $path, 'saved' => (bool)$s, 'size' => strlen($content)]];
				});
			}],
			'file.create' => ['auth' => 'ctx', 'cb' => function () {
				$full = mnbt_app_p('path');
				[$dir, $name] = mnbt_app_split_path($full);
				mnbt_app_proxy_run('file.php', 'file_create', [
					'path' => $dir, 'name' => $name, 'type' => mnbt_app_p('type', 'file'),
				], function ($s, $c, $m, $d) use ($full) {
					return [$s, $c, $m, ['path' => $full]];
				});
			}],
			'file.delete' => ['auth' => 'ctx', 'cb' => function () {
				$full = mnbt_app_p('path');
				[$dir, $name] = mnbt_app_split_path($full);
				mnbt_app_proxy_run('file.php', 'file_delete', [
					'path' => $dir, 'name' => $name, 'type' => mnbt_app_p('type', 'file'),
				], function ($s, $c, $m, $d) use ($full) {
					return [$s, $c, $m, ['path' => $full]];
				});
			}],
			'file.delete_batch' => ['auth' => 'ctx', 'cb' => function () {
				// 数组协议正式化：paths[] 为全路径数组，按目录分组调宝塔 xzdelwj（含运行目录/绑定子目录保护）。
				// 不复用核心 file_delete_batch 分支：它以 json_exit 结束请求，多目录分组时第一批后即终止。
				global $api, $yhc;
				mnbt_app_core_include('file.php', '__bridge_defs__', null);
				if (!function_exists('mnbt_file_check_name')) mnbt_app_err_5000('missing_core_api:file.php');
				$paths = $_POST['paths'] ?? [];
				if (is_string($paths)) $paths = json_decode($paths, true);
				if (!is_array($paths) || $paths === []) mnbt_app_err(1007, '缺少 paths[] 数组');
				$groups = [];
				foreach ($paths as $p) {
					[$dir, $name] = mnbt_app_split_path((string)$p);
					if ($name === '') continue;
					$nd = mnbt_file_norm_path($dir);
					if ($nd === null || !mnbt_file_check_name($name)) { $groups = []; break; }
					if ($name === '.user.ini') { $groups = []; break; }
					$groups[$nd][] = $name;
				}
				if ($groups === []) mnbt_app_err(1007, '路径或文件名格式错误');
				$deleted = 0;
				$failed = [];
				foreach ($groups as $dir => $names) {
					$r = $api->xzdelwj($dir, json_encode($names, JSON_UNESCAPED_UNICODE), [$yhc['btid'], mnbt_file_base()]);
					if (mnbt_file_is_ok(is_array($r) ? $r : [])) {
						$deleted += count($names);
					} else {
						$err = is_array($r) ? (string)($r['msg'] ?? '删除失败') : '删除失败';
						foreach ($names as $n) $failed[] = $dir . '/' . $n . ': ' . $err;
					}
				}
				mnbt_app_log('删除文件', '批量删除' . $deleted . '项', $failed === [] ? '成功' : '部分失败');
				mnbt_app_send($failed === [], $failed === [] ? 0 : 1009, '批量删除完成', [
					'deleted' => $deleted, 'failed' => $failed,
				]);
			}],
			'file.rename' => ['auth' => 'ctx', 'cb' => function () {
				$src = mnbt_app_p('src', mnbt_app_p('old_name'));
				$dest = mnbt_app_p('dest', mnbt_app_p('new_name'));
				[$dir, $old] = mnbt_app_split_path($src);
				[, $new] = mnbt_app_split_path($dest);
				mnbt_app_proxy_run('file.php', 'file_rename', [
					'path' => $dir, 'oldname' => $old, 'newname' => $new,
				], function ($s, $c, $m, $d) use ($src, $dest) {
					return [$s, $c, $m, ['src' => $src, 'dest' => $dest]];
				});
			}],
			'file.copy' => ['auth' => 'ctx', 'cb' => function () {
				// 自定义实现：dest 允许是目录（结尾 /）或含改名后的完整目标路径，
				// 核心 file_copy 只支持同名跨目录且同目录直接拒绝，覆盖不了 APP 的复制语义
				global $api, $yhc;
				mnbt_app_core_include('file.php', '__bridge_defs__', null);
				if (!function_exists('mnbt_file_norm_path')) mnbt_app_err_5000('missing_core_api:file.php');
				$src = (string)mnbt_app_p('src');
				$dest = (string)mnbt_app_p('dest');
				[$ypathRaw, $name] = mnbt_app_split_path($src);
				if ($name === '' || $name === '.user.ini') mnbt_app_err(1007, 'src 必须包含合法文件名');
				if (substr($dest, -1) === '/') {
					$xpathRaw = $dest;
					$tname = $name;
				} else {
					[$xpathRaw, $tname] = mnbt_app_split_path($dest);
					if ($tname === '') $tname = $name;
				}
				$ypath = mnbt_file_norm_path($ypathRaw);
				$xpath = $xpathRaw === '' ? '/' : mnbt_file_norm_path($xpathRaw);
				if ($ypath === null || $xpath === null || !mnbt_file_check_name($tname) || $tname === '.user.ini') {
					mnbt_app_err(1007, '路径或文件名格式错误');
				}
				$abs_src = mnbt_file_abs(mnbt_file_rel_child($ypath, $name));
				$abs_dst = mnbt_file_abs(mnbt_file_rel_child($xpath, $tname));
				if ($abs_src === $abs_dst) mnbt_app_err(1007, '源路径与目标路径相同');
				if ($xpath !== '/' && substr($xpath, 0, mb_strlen($ypath . $name . '/')) === $ypath . $name . '/') {
					mnbt_app_err(1007, '目标目录在源目录内，存在无限递归复制风险');
				}
				$type = (string)mnbt_app_p('type', 'copy') === 'cut' ? 'cut' : 'copy';
				$r = $api->filecopy($abs_src, $abs_dst);
				$ok = is_array($r) && mnbt_file_is_ok($r);
				if ($ok && $type === 'cut') {
					$api->xzdelwj($ypath, json_encode([$name], JSON_UNESCAPED_UNICODE), [$yhc['btid'], mnbt_file_base()]);
				}
				mnbt_app_log('文件操作', ($type === 'cut' ? '剪切' : '复制') . $src . '到' . mnbt_file_rel_child($xpath, $tname), $ok ? '成功' : '失败:' . (is_array($r) ? (string)($r['msg'] ?? '未知错误') : '未知错误'));
				if (!$ok) mnbt_app_err(1009, is_array($r) ? (string)($r['msg'] ?? '操作失败') : '操作失败');
				mnbt_app_ok($type === 'cut' ? '剪切成功' : '复制成功', [
					'src' => $src, 'dest' => mnbt_file_rel_child($xpath, $tname), 'type' => $type,
				]);
			}],
			'file.compress' => ['auth' => 'ctx', 'cb' => function () {
				$full = mnbt_app_p('files', mnbt_app_p('path'));
				$dest = mnbt_app_p('dest');
				[$dir, $name] = mnbt_app_split_path($full);
				$names = $name === '' ? [] : [$name];
				mnbt_app_proxy_run('file.php', 'file_compress', [
					'path' => $dir, 'names' => $names, 'dest' => $dest, 'type' => mnbt_app_p('type', 'zip'),
				], function ($s, $c, $m, $d) use ($dest) {
					return [$s, $c, $m, ['dest' => $dest]];
				});
			}],
			'file.unzip' => ['auth' => 'ctx', 'cb' => function () {
				$src = mnbt_app_p('src');
				$dest = mnbt_app_p('dest', $src);
				[$dir, $name] = mnbt_app_split_path($src);
				mnbt_app_proxy_run('file.php', 'file_unzip', [
					'path' => $dir, 'name' => $name, 'dest' => $dest,
					'coding' => mnbt_app_p('coding', 'UTF-8'), 'password' => mnbt_app_p('password', ''),
				], function ($s, $c, $m, $d) use ($dest) {
					return [$s, $c, $m, ['dir' => $dest] + (is_array($d) ? $d : [])];
				});
			}],
			'file.size' => ['auth' => 'ctx', 'cb' => function () {
				$target = (string)mnbt_app_p('files', mnbt_app_p('path'));
				mnbt_app_proxy_run('file.php', 'file_size', ['path' => $target], function ($s, $c, $m, $d) use ($target) {
					if (!$s) return [$s, $c, $m, $d];
					// APP 的 FileSizePayload.size 是 Long，核心回的是 float -> 必须整型化
					$bytes = mnbt_app_bytes($d['size'] ?? 0);
					return [true, 0, 'ok', ['path' => $target, 'size' => $bytes, 'unit' => 'B', 'files' => $target]];
				});
			}],
			'file.upload_prepare' => ['auth' => 'ctx', 'cb' => function () {
				// 自定义实现（核心分支 json_exit 会截断 reshape）；校验与偏移探测逻辑照抄核心
				global $api, $yhc;
				mnbt_app_core_include('file.php', '__bridge_defs__', null);
				if (!function_exists('mnbt_file_norm_path')) mnbt_app_err_5000('missing_core_api:file.php');
				$full = (string)mnbt_app_p('path');
				[$dirRaw, $name] = mnbt_app_split_path($full);
				$dir = mnbt_file_norm_path($dirRaw);
				if ($name === '' || !mnbt_file_check_name($name) || $name === '.user.ini') mnbt_app_err(1007, 'path 必须包含合法文件名');
				if ($dir === null) mnbt_app_err(1007, '目录格式错误');
				// size 是文件总字节数：宝塔的分片协议按它判定「传完没有」，缺了就拼不出完整文件
				$size = (string)mnbt_app_p('size', '0');
				if (!ctype_digit($size)) $size = '0';
				$offset = 0.0;
				if ($size !== '0') {
					$r = $api->fileupa(mnbt_file_abs($dir) . '/' . $name . '.' . $size . '.upload.tmp');
					if (is_array($r) && mnbt_file_is_ok($r)) {
						$m = $r['msg'] ?? 0;
						if (is_array($m)) $offset = is_numeric($m['size'] ?? null) ? (float)$m['size'] : 0;
						elseif (is_numeric($m)) $offset = (float)$m;
					}
				}
				$key = $yhc['id'] . '|' . $dir . '|' . $name;
				mnbt_app_upload_offset_set($key, $offset);
				$quota = json_decode((string)($yhc['hxa'] ?? ''), true) ?: [];
				$maxmb = (float)($quota['max'] ?? 0);
				mnbt_app_ok('ok', [
					'upload_id' => $dir . '|' . $name,
					'path' => mnbt_file_rel_child($dir, $name),
					'name' => $name,
					'size' => (int)$size,
					'max_size' => $maxmb > 0 ? (int)($maxmb * 1048576) : 0,
					'chunk_size' => 2 * 1048576,
					'resume_offset' => (int)$offset,
				]);
			}],
			'file.upload' => ['auth' => 'ctx', 'cb' => function () {
				// APP 协议 index/total 是 1-based 分片序号/总数，宝塔协议 start/size 是字节偏移/总字节数：
				// size 必须由客户端给出（宝塔按 name.<size>.upload.tmp 落分片，中途不能变），
				// start 由 bridge 按 主机|目录|文件名 记偏移量，index<=1 视为重头开始
				global $api, $yhc;
				mnbt_app_core_include('file.php', '__bridge_defs__', null);
				$upload_id = (string)mnbt_app_p('upload_id');
				[$dir, $fname] = array_pad(explode('|', $upload_id, 2), 2, '');
				if ($dir === '' || $fname === '') {
					$full = (string)mnbt_app_p('path');
					[$dir, $fname] = mnbt_app_split_path($full);
				}
				$fname = (string)mnbt_app_p('name', mnbt_app_p('file_name', $fname));
				if ($dir === '' || $fname === '' || $fname === '.user.ini') mnbt_app_err(1007, '缺少 upload_id/path');
				if ($fname === '.user.ini') mnbt_app_err(1007, '禁止上传配置文件(.user.ini)！');
				$size = (string)mnbt_app_p('size', '0');
				if (!ctype_digit($size) || (int)$size <= 0) {
					mnbt_app_err(1007, '缺少 size（文件总字节数）：宝塔分片协议按它判定传输完成，请随分片一起上报');
				}
				if (empty($_FILES['file']) || !is_array($_FILES['file']) || !is_uploaded_file($_FILES['file']['tmp_name'] ?? '')) {
					mnbt_app_err(1007, '缺少文件分片(multipart: file)');
				}
				$index = (int)mnbt_app_p('index', '1');
				$key = $yhc['id'] . '|' . $dir . '|' . $fname;
				$mtime = (int)@filemtime(mnbt_app_upload_offset_file($key));
				$fresh = ($index <= 1) || $mtime === 0 || (time() - $mtime) > 86400;
				$start = $fresh ? 0 : mnbt_app_upload_offset_get($key);
				// 网页空间配额校验（hxa: {max: 总量MB, dq: 已用MB}），与核心 file_upload 对齐
				$websize = json_decode((string)($yhc['hxa'] ?? ''), true);
				if (!is_array($websize)) $websize = [];
				$max = (float)($websize['max'] ?? 0);
				$used = (float)($websize['dq'] ?? 0);
				$mb = round((float)$size / 1048576);
				if ($max > 0 && $mb > $max) mnbt_app_err(1007, '错误！上传的文件大于您的最大可用网页空间，无法上传此文件！');
				if ($max > 0 && $max <= $used) mnbt_app_err(1007, '错误！网页空间已满！');
				if ($max > 0 && $mb > $max - $used) mnbt_app_err(1007, '错误！上传的文件大于当前可使用的网页空间，请清理空间后再试！');
				$r = $api->fileups(mnbt_file_abs($dir) . '/', $_FILES['file'], (string)$start, $fname, $size);
				if (is_numeric($r)) {
					mnbt_app_upload_offset_set($key, (float)$r);
					mnbt_app_ok('分片已接收', ['size' => (int)$r, 'received' => (int)$r, 'total_size' => (int)$size, 'done' => false]);
				}
				if (is_array($r) && mnbt_file_is_ok($r)) {
					mnbt_app_upload_offset_set($key, 0);
					mnbt_app_log('上传文件', '上传了' . rtrim($dir, '/') . '/' . $fname, '成功');
					mnbt_app_ok('上传成功', ['done' => true, 'path' => rtrim($dir, '/') . '/' . $fname, 'size' => (int)$size]);
				}
				mnbt_app_err(1009, is_array($r) ? (string)($r['msg'] ?? '上传失败') : '上传失败');
			}],
			'file.download' => ['auth' => 'ctx', 'cb' => function () {
				$full = mnbt_app_p('file', mnbt_app_p('path'));
				[$dir, $name] = mnbt_app_split_path($full);
				if ($name === '') mnbt_app_err(1007, 'path 必须包含文件名');
				mnbt_app_proxy_run('file.php', 'file_download', ['path' => $dir, 'name' => $name], function ($s, $c, $m, $d) use ($full, $name) {
					if (!$s) return [$s, $c, $m, $d];
					return [true, 0, 'ok', [
						'filename' => $name,
						'file' => $full,
						'url' => (string)($d['url'] ?? ''),
						'expires_hint' => '24h',
					]];
				});
			}],
			'file.access.get' => ['auth' => 'ctx', 'cb' => function () {
				$path = (string)mnbt_app_p('path');
				mnbt_app_proxy_run('file.php', 'file_access', ['path' => $path], function ($s, $c, $m, $d) use ($path) {
					if (!$s) return [$s, $c, $m, $d];
					// APP 的 FileAccessPayload.perm 要 "0644" 形式的八进制串
					$perm = str_pad(preg_replace('/[^0-7]/', '', mnbt_app_text($d['access'] ?? '', '')), 4, '0', STR_PAD_LEFT);
					return [true, 0, 'ok', [
						'path' => $path,
						'perm' => $perm,
						'owner' => mnbt_app_text($d['user'] ?? ($d['owner'] ?? ''), ''),
						'group' => mnbt_app_text($d['group'] ?? '', ''),
					]];
				});
			}],
			'file.access.set' => ['auth' => 'ctx', 'cb' => function () {
				$path = (string)mnbt_app_p('path');
				$perm = (string)mnbt_app_p('perm', mnbt_app_p('access'));
				$recurse = (string)mnbt_app_p('recurse', '0');
				mnbt_app_proxy_run('file.php', 'file_access_set', [
					'path' => $path,
					'access' => $perm,
					'recurse' => $recurse,
				], function ($s, $c, $m, $d) use ($path, $perm, $recurse) {
					return [$s, $c, $m, ['path' => $path, 'perm' => $perm, 'recurse' => $recurse]];
				});
			}],
		];
	}
}
