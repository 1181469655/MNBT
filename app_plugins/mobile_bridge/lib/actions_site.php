<?php
/**
 * mobile_bridge - 站点设置组 action（PRD §5.2）
 *
 * APP 参数名 -> 核心参数名的转换全部集中在这里（交接 §3.3 的 ⚠️ 项逐条落地）：
 *   php_version->php / rundir->wb / index->ml / rewrite->wb / gzip->action
 *   user_dir->name+mbml / pass(新密码)->ftp+sql
 *   缓存 url->suffix、seconds->time_out(<n>s)、id->按当前列表序号反查 suffix
 *
 * 两条刻意的取舍：
 * - 「索引配置」「反向代理」已按产品决定从 APP 下线，对应 action 不再注册。
 *   核心侧本来也做不了：indexconf 分支（other.php:47）是首页只读汇总（还带 FTP/SQL 明文密码），
 *   全仓没有目录索引（autoindex）的写入入口，绝不能拿站点开关 siteqt 冒充；
 *   宝塔反代原语把 location 写死 '/'，子路径规则无从创建。
 *   site.init 的 url 分支只保留核心的 btip/als，不再拿 Getnginx 原文抠规则冒充列表。
 * - 核心分支自带 logjl 的写操作（php/默认文档/运行目录/密码/防盗链外的 gzip/cache/passdir），
 *   bridge 不再重复记日志，避免面板操作日志一次动作两条记录。
 */
if (!defined('IN_CRONLITE')) exit;

if (!function_exists('mnbt_app_time_out')) {
	/** 秒 -> 宝塔缓存时长格式（60s/12h/30d/12M/1y），非法值回落 30d */
	function mnbt_app_time_out($seconds)
	{
		$s = (int)$seconds;
		if ($s <= 0) return '30d';
		if ($s % 31536000 === 0) return ($s / 31536000) . 'y';
		if ($s % 2592000 === 0) return ($s / 2592000) . 'M';
		if ($s % 86400 === 0) return ($s / 86400) . 'd';
		if ($s % 3600 === 0) return ($s / 3600) . 'h';
		if ($s % 60 === 0) return ($s / 60) . 'm';
		return $s . 's';
	}
}

if (!function_exists('mnbt_app_time_out_seconds')) {
	/** 宝塔缓存时长格式 -> 秒（无法识别回 0） */
	function mnbt_app_time_out_seconds($time_out)
	{
		$v = trim((string)$time_out);
		if ($v === '') return 0;
		if (preg_match('/^(\d+)([smhdMy])$/', $v, $m)) {
			$unit = ['s' => 1, 'm' => 60, 'h' => 3600, 'd' => 86400, 'M' => 2592000, 'y' => 31536000];
			return (int)$m[1] * $unit[$m[2]];
		}
		return is_numeric($v) ? (int)$v : 0;
	}
}

if (!function_exists('mnbt_app_is_on')) {
	/** 与网页面板 isOn() 同规则：true/'true'/1/'1'/'on' 记为开启 */
	function mnbt_app_is_on($v)
	{
		if ($v === true) return true;
		if ($v === false || $v === null) return false;
		$s = strtolower(trim((string)$v));
		return in_array($s, ['1', 'true', 'on', 'yes', 'y'], true);
	}
}

if (!function_exists('mnbt_app_text')) {
	/** 宝塔回包字段松取 -> 字符串（数组按逗号拼接，布尔/对象丢弃） */
	function mnbt_app_text($v, $default = '')
	{
		if (is_scalar($v)) return (string)$v;
		if (is_array($v)) {
			$parts = [];
			foreach ($v as $item) {
				if (is_scalar($item)) $parts[] = (string)$item;
			}
			return $parts === [] ? $default : implode(',', $parts);
		}
		return $default;
	}
}

if (!function_exists('mnbt_app_str_list')) {
	/** 宝塔列表/映射 -> 字符串列表（APP 的 options/list 都是 List<String>，混进对象会解码失败） */
	function mnbt_app_str_list($v)
	{
		if (!is_array($v)) return [];
		$out = [];
		foreach ($v as $item) {
			if (is_scalar($item)) {
				$s = trim((string)$item);
				if ($s !== '') $out[] = $s;
				continue;
			}
			if (is_array($item)) {
				foreach (['name', 'title', 'dir', 'path', 'value', 'version'] as $k) {
					if (isset($item[$k]) && is_scalar($item[$k]) && (string)$item[$k] !== '') {
						$out[] = (string)$item[$k];
						break;
					}
				}
			}
		}
		return array_values(array_unique($out));
	}
}

if (!function_exists('mnbt_app_cache_type')) {
	/** 缓存后缀 -> APP 的三分类（宝塔只有后缀，没有类型位；这里是 bridge 侧的分类约定） */
	function mnbt_app_cache_type($suffix)
	{
		$s = strtolower(trim((string)$suffix));
		if ($s === '' || in_array($s, ['html', 'htm', 'php', '/'], true)) return 'page';
		$image = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'ico', 'svg', 'avif', 'tiff', 'jfif'];
		$first = strtolower(trim(explode(',', $s)[0]));
		return in_array($first, $image, true) ? 'image' : 'file';
	}
}

if (!function_exists('mnbt_app_cache_rows')) {
	/**
	 * 静态缓存列表（读 bt_api->get_static_cache，形状归一与核心 panel.php 的 cache 分支一致）。
	 * APP 的 SiteBCacheRule 要 {id:Long,url,type,seconds}：id 取列表内 1-based 序号，
	 * edit/del 再按序号反查后缀（宝塔以后缀为主键，没有稳定 id）。
	 */
	function mnbt_app_cache_rows()
	{
		global $api, $yhc;
		$r = $api->get_static_cache($yhc['sqldz'] ?? '') ?: [];
		if (!is_array($r)) return [];
		$list = $r['data'] ?? $r['msg'] ?? $r['list'] ?? $r;
		if (!is_array($list)) return [];
		if (isset($list['status']) && isset($list['data']) && is_array($list['data'])) $list = $list['data'];
		if (!is_array($list)) return [];
		if ($list && array_keys($list) !== range(0, count($list) - 1)) {
			$tmp = [];
			foreach ($list as $k => $v) {
				$tmp[] = is_array($v) ? array_merge(['suffix' => $k], $v) : ['suffix' => $k, 'time_out' => $v];
			}
			$list = $tmp;
		}
		$rows = [];
		$i = 0;
		foreach ($list as $item) {
			if (!is_array($item)) continue;
			$suffix = mnbt_app_text($item['suffix'] ?? ($item['ext'] ?? ''), '');
			$time_out = mnbt_app_text($item['time_out'] ?? ($item['time'] ?? ''), '');
			$i++;
			$rows[] = [
				'id' => $i,
				'url' => $suffix,
				'type' => mnbt_app_cache_type($suffix),
				'seconds' => mnbt_app_time_out_seconds($time_out),
				'time_out' => $time_out,
			];
		}
		return $rows;
	}
}

if (!function_exists('mnbt_app_cache_suffix_by_id')) {
	/** id 序号 -> 后缀；解析不到回空串（交给调用方判参数错误） */
	function mnbt_app_cache_suffix_by_id($id)
	{
		$id = (int)$id;
		if ($id <= 0) return '';
		foreach (mnbt_app_cache_rows() as $row) {
			if ((int)$row['id'] === $id) return (string)$row['url'];
		}
		return '';
	}
}

if (!function_exists('mnbt_app_site_init')) {
	/** site.init 统一初始化：按 APP 冻结的 DTO 形状出参（current 一律是字符串） */
	function mnbt_app_site_init($section)
	{
		global $yhc;
		$section = (string)$section;

		if ($section === 'pass') {
			mnbt_app_proxy_run('panel.php', 'pass_list', [], function ($s, $c, $m, $d) {
				if (!$s) return [$s, $c, $m, $d];
				$list = [];
				foreach (($d['list'] ?? []) as $item) {
					if (!is_array($item)) $item = ['name' => (string)$item];
					$dir = mnbt_app_text($item['mbml'] ?? ($item['name'] ?? ($item['dir'] ?? '')), '');
					$list[] = [
						'dir' => '/' . ltrim($dir, '/'),
						'user' => mnbt_app_text($item['user'] ?? '', ''),
						'pass' => '****',
					];
				}
				return [true, 0, 'ok', ['section' => 'pass', 'list' => $list]];
			});
			return;
		}

		if ($section === 'xgpass') {
			// 核心该分支只回一句提示，用户名直接取当前上下文；密码任何形式都不回显
			mnbt_app_ok('ok', [
				'section' => 'xgpass',
				'user' => (string)($yhc['user'] ?? ''),
				'ftp_hint' => '修改后控制面板登录密码同步为 FTP 密码',
			]);
		}

		if ($section === 'cache') {
			mnbt_app_ok('ok', ['section' => 'cache', 'list' => mnbt_app_cache_rows()]);
		}

		if ($section === 'gzip') {
			mnbt_app_proxy_run('panel.php', 'set_init', ['section' => 'gzip'], function ($s, $c, $m, $d) {
				if (!$s) return [$s, $c, $m, $d];
				$g = is_array($d['gzip'] ?? null) ? $d['gzip'] : [];
				return [true, 0, 'ok', [
					'section' => 'gzip',
					'current' => mnbt_app_is_on($g['status'] ?? ($g['qk'] ?? '')) ? 'on' : 'off',
					'level' => mnbt_app_text($g['comp_level'] ?? '', '6'),
					'min_len' => mnbt_app_text($g['min_length'] ?? '', '1k'),
					'types' => mnbt_app_text($g['gzip_types'] ?? '', ''),
				]];
			});
			return;
		}

		mnbt_app_proxy_run('panel.php', 'set_init', ['section' => $section], function ($s, $c, $m, $d) use ($section) {
			if (!$s) return [$s, $c, $m, $d];
			$out = ['section' => $section];
			switch ($section) {
				case 'php':
					$out['current'] = mnbt_app_text($d['php'] ?? '', '');
					$out['options'] = mnbt_app_str_list($d['list'] ?? []);
					break;
				case 'yxml':
					$out['current'] = mnbt_app_text($d['current'] ?? '', '/');
					$out['options'] = mnbt_app_str_list($d['dirs'] ?? []);
					break;
				case 'mrwd':
					$out['current'] = mnbt_app_text($d['index'] ?? '', '');
					break;
				case 'wjt':
					// 宝塔查不到「当前生效的模板名」，current 恒空串：APP 不预选，
					// 用户点名后由 site.rewrite.get 取内容（当前规则内容用 name 省略即可）
					$out['current'] = '';
					$out['list'] = mnbt_app_str_list($d['templates'] ?? []);
					break;
				case 'url':
					$out['btip'] = mnbt_app_text($d['btip'] ?? '', '');
					$out['als'] = mnbt_app_text($d['als'] ?? '', 'false');
					break;
				case 'mysqlcz':
					$out['current'] = mnbt_app_text($d['access'] ?? '', '127.0.0.1');
					$out['access'] = $out['current'];
					break;
				default:
					$out['current'] = is_array($d) ? '' : mnbt_app_text($d, '');
			}
			mnbt_app_ok('ok', $out);
		});
	}
}

if (!function_exists('mnbt_app_hotlink_payload')) {
	/** 防盗链回包 -> APP 的 SiteAHotlinkData（open/lasts 必须是 Int） */
	function mnbt_app_hotlink_payload($d, $overrides = [])
	{
		$d = is_array($d) ? $d : [];
		return array_merge([
			'open' => mnbt_app_is_on($d['kg'] ?? ($d['status'] ?? ($d['qk'] ?? ''))) ? 1 : 0,
			'domains' => mnbt_app_text($d['domains'] ?? ($d['domain'] ?? ($d['list'] ?? '')), ''),
			'exts' => mnbt_app_text($d['exts'] ?? ($d['ext'] ?? ($d['extensions'] ?? ($d['fix'] ?? ''))), ''),
			// 宝塔防盗链没有「有效时长」概念，恒 0（APP 侧显示为不限时）
			'lasts' => 0,
		], $overrides);
	}
}

if (!function_exists('mnbt_app_actions_site')) {
	function mnbt_app_actions_site()
	{
		return [
			'site.php.set' => ['auth' => 'ctx', 'cb' => function () {
				$pv = (string)mnbt_app_p('php_version');
				mnbt_app_proxy_run('site.php', 'phpxg', ['php' => $pv], function ($s, $c, $m, $d) use ($pv) {
					$echo = mnbt_app_text($d['phpversion'] ?? '', $pv);
					return [$s, $c, $m, ['php_version' => $echo, 'phpversion' => $echo]];
				});
			}],
			'site.rundir.set' => ['auth' => 'ctx', 'cb' => function () {
				$rd = mnbt_app_p('rundir');
				mnbt_app_proxy_run('site.php', 'setyxml', ['wb' => $rd], function ($s, $c, $m, $d) use ($rd) {
					return [$s, $c, $m, ['rundir' => (string)$rd]];
				});
			}],
			'site.indexdoc.set' => ['auth' => 'ctx', 'cb' => function () {
				$idx = mnbt_app_p('index');
				mnbt_app_proxy_run('site.php', 'xgmrwd', ['ml' => $idx], function ($s, $c, $m, $d) use ($idx) {
					return [$s, $c, $m, ['indexdoc' => (string)$idx]];
				});
			}],
			// 伪静态：name/rewrite 空或 'current' -> 读站点当前规则文件（交接 §4-B9）
			'site.rewrite.get' => ['auth' => 'ctx', 'cb' => function () {
				$xz = (string)mnbt_app_p('name', mnbt_app_p('rewrite', ''));
				$is_current = ($xz === '' || $xz === 'current');
				mnbt_app_proxy_run('site.php', 'hqjt', ['xz' => $is_current ? '0.当前' : $xz], function ($s, $c, $m, $d) use ($xz, $is_current) {
					global $api, $yhc;
					if (!$s) return [$s, $c, $m, $d];
					$content = (string)($d['content'] ?? '');
					$cur = $api->GetLogswr($yhc['sqldz'] ?? '') ?: [];
					$list = $cur['rewrite'] ?? ($cur['list'] ?? $cur);
					return [true, 0, 'ok', [
						// current 恒空：宝塔查不到当前生效的模板名，回内容会污染 APP 的选中态
						'current' => '',
						'rewrite' => $content,
						'list' => mnbt_app_str_list($list),
						'name' => $is_current ? '' : $xz,
						'content' => $content,
					]];
				}, true);
			}],
			'site.rewrite.set' => ['auth' => 'ctx', 'cb' => function () {
				$wb = (string)mnbt_app_p('rewrite', mnbt_app_p('wb'));
				// 核心 setwjt 是「整文件覆盖写」：wb 会原样落进 vhost/rewrite/{站点}.conf 并被
				// 站点 nginx 配置 include。历史上 APP 传过模板名，一行裸词就让 nginx -t 全站失败，
				// 所以这里挡住「单个词、既无换行也无 ; { }」这种必然写坏配置的形状。
				// 清空规则（deploy.php:66 的合法用法）仍可通过：空串不含裸词，核心自己处理。
				if ($wb !== '' && strpbrk($wb, "\r\n;{}") === false && preg_match('/^\s*[A-Za-z0-9._-]+\s*$/', $wb)) {
					mnbt_app_err(1007, '伪静态需提交规则正文，而不是模板名');
				}
				mnbt_app_proxy_run('site.php', 'setwjt', ['wb' => $wb], function ($s, $c, $m, $d) use ($wb) {
					// 核心 setwjt 分支不记日志，这里补
					mnbt_app_log('伪静态', '设置伪静态规则', $s ? '成功' : '失败:' . $m);
					return [$s, $c, $m, ['rewrite' => $wb]];
				});
			}],
			'site.gzip.set' => ['auth' => 'ctx', 'cb' => function () {
				$raw = strtolower((string)mnbt_app_p('gzip', mnbt_app_p('status', 'on')));
				$on = !in_array($raw, ['0', 'off', 'false', 'no'], true);
				$params = ['action' => $on ? 'on' : 'off'];
				// 只在 APP 明确带上时才覆盖，缺省交给核心默认值（level 6 / 1k / 默认类型串）
				foreach (['level', 'min_len', 'types'] as $k) {
					$v = mnbt_app_p($k);
					if ($v !== '') $params[$k] = $v;
				}
				mnbt_app_proxy_run('site.php', 'setgzip', $params, function ($s, $c, $m, $d) use ($on) {
					return [$s, $c, $m, ['gzip' => $on ? 'on' : 'off']];
				});
			}],
			// 密码访问目录：user_dir -> 核心 name + mbml（GetLogs 系列以目录名为键）
			'site.passdir.add' => ['auth' => 'ctx', 'cb' => function () {
				$dir = (string)mnbt_app_p('user_dir', mnbt_app_p('dir'));
				$user = (string)mnbt_app_p('user');
				[$dd, $dn] = mnbt_app_split_path($dir);
				mnbt_app_proxy_run('site.php', 'tjmmfw', [
					'name' => (string)mnbt_app_p('name', $dn),
					'mbml' => $dir,
					'user' => $user,
					'pass' => (string)mnbt_app_p('pass'),
				], function ($s, $c, $m, $d) use ($dir, $user) {
					return [$s, $c, $m, ['action' => 'add', 'added' => $s, 'dir' => $dir, 'user' => $user]];
				});
			}],
			'site.passdir.del' => ['auth' => 'ctx', 'cb' => function () {
				$dir = (string)mnbt_app_p('user_dir', mnbt_app_p('dir'));
				[, $dn] = mnbt_app_split_path($dir);
				mnbt_app_proxy_run('site.php', 'scmmfw', ['mb' => $dn], function ($s, $c, $m, $d) use ($dir) {
					return [$s, $c, $m, ['action' => 'del', 'deleted' => $s, 'dir' => $dir]];
				});
			}],
			'site.passdir.list' => ['auth' => 'ctx', 'cb' => function () {
				mnbt_app_site_init('pass');
			}],
			// 改密：明文仅透传核心，日志与回包都不落密码（交接 §4-B4）
			'site.password.set' => ['auth' => 'ctx', 'cb' => function () {
				$pass = (string)mnbt_app_p('pass', mnbt_app_p('newpass'));
				$ftp = (string)mnbt_app_p('ftp', $pass);
				$sql = (string)mnbt_app_p('sql', $pass);
				if ($ftp === '' && $sql === '') mnbt_app_err(1007, '请至少填写一项密码');
				mnbt_app_proxy_run('site.php', 'xgpass', ['ftp' => $ftp, 'sql' => $sql], function ($s, $c, $m, $d) {
					return [$s, $c, $m, ['action' => 'password', 'user' => (string)($GLOBALS['yhc']['user'] ?? '')]];
				});
			}],
			'site.hotlink.get' => ['auth' => 'ctx', 'cb' => function () {
				// getfdl 是裸 exit(json_encode) 的「文档型」回包，其中 status:false 表示
				// 「防盗链未开启」这一正常状态，不是请求失败；而 adapter 形态3 会把裸 false
				// 判成失败。所以这里不按 $s 短路，直接把载荷交给 payload 组装。
				mnbt_app_proxy_run('other.php', 'getfdl', [], function ($s, $c, $m, $d) {
					return [true, 0, 'ok', mnbt_app_hotlink_payload($d)];
				});
			}],
			'site.hotlink.set' => ['auth' => 'ctx', 'cb' => function () {
				$on = mnbt_app_is_on(mnbt_app_p('open', mnbt_app_p('status', '1')));
				$exts = (string)mnbt_app_p('exts', mnbt_app_p('fix'));
				$domains = (string)mnbt_app_p('domains');
				// status 用宝塔/面板一致的 'true'/'false'；lasts 无对应字段，不透传
				mnbt_app_proxy_run('other.php', 'fdlkg', [
					'status' => $on ? 'true' : 'false',
					'domains' => $domains,
					'fix' => $exts,
					'return_rule' => (string)mnbt_app_p('return_rule'),
					'http_status' => (string)mnbt_app_p('http_status'),
				], function ($s, $c, $m, $d) use ($on, $exts, $domains) {
					return [$s, $c, $m, mnbt_app_hotlink_payload($d, [
						'open' => $on ? 1 : 0,
						'domains' => $domains,
						'exts' => $exts,
					])];
				});
			}],
			// 静态缓存：真实契约是 后缀+时长（交接 §4-B5 的 type/seconds 猜测由 id/url/type/seconds 适配层吸收）
			'site.cache.list' => ['auth' => 'ctx', 'cb' => function () {
				mnbt_app_site_init('cache');
			}],
			'site.cache.add' => ['auth' => 'ctx', 'cb' => function () {
				$suffix = (string)mnbt_app_p('url', mnbt_app_p('suffix', mnbt_app_p('type')));
				$seconds = (string)mnbt_app_p('seconds', '0');
				if ($suffix === '') mnbt_app_err(1007, '缺少缓存后缀(url)');
				mnbt_app_proxy_run('cache.php', 'cacheadd', [
					'suffix' => $suffix,
					'time_out' => (string)mnbt_app_p('time_out', mnbt_app_time_out($seconds)),
				], function ($s, $c, $m, $d) use ($suffix, $seconds) {
					$rows = mnbt_app_cache_rows();
					$id = 0;
					foreach ($rows as $row) {
						if ((string)$row['url'] === $suffix) { $id = (int)$row['id']; break; }
					}
					return [$s, $c, $m, ['action' => 'add', 'added' => $s, 'url' => $suffix, 'id' => $id, 'type' => mnbt_app_cache_type($suffix), 'seconds' => (int)$seconds]];
				});
			}],
			'site.cache.edit' => ['auth' => 'ctx', 'cb' => function () {
				$suffix = (string)mnbt_app_p('url', mnbt_app_p('suffix'));
				$id = (int)mnbt_app_p('id', '0');
				$old = (string)mnbt_app_p('old_suffix', mnbt_app_cache_suffix_by_id($id));
				if ($old === '') mnbt_app_err(1007, '缓存规则不存在（id 已失效，请刷新列表）');
				if ($suffix === '') $suffix = $old;
				mnbt_app_proxy_run('cache.php', 'cacheedit', [
					'suffix' => $suffix,
					'old_suffix' => $old,
					'time_out' => (string)mnbt_app_p('time_out', mnbt_app_time_out(mnbt_app_p('seconds', '0'))),
				], function ($s, $c, $m, $d) use ($suffix, $old, $id) {
					return [$s, $c, $m, ['action' => 'edit', 'url' => $suffix, 'old_suffix' => $old, 'id' => $id]];
				});
			}],
			'site.cache.del' => ['auth' => 'ctx', 'cb' => function () {
				$id = (int)mnbt_app_p('id', '0');
				$suffix = (string)mnbt_app_p('suffix', mnbt_app_cache_suffix_by_id($id));
				if ($suffix === '') mnbt_app_err(1007, '缓存规则不存在（id 已失效，请刷新列表）');
				mnbt_app_proxy_run('cache.php', 'cachedel', ['suffix' => $suffix], function ($s, $c, $m, $d) use ($suffix, $id) {
					return [$s, $c, $m, ['action' => 'del', 'deleted' => $s, 'url' => $suffix, 'id' => $id]];
				});
			}],
		];
	}
}
