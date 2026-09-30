<?php
if (!defined('IN_CRONLITE')) exit();
/**
 * 系统更新：从 GitHub Release 拉包覆盖更新（V1.85 起替代自建更新服务器 check.php）
 *
 * 为什么这样写：更新包来自站外，配置文件 cf_up.php 里又能改仓库与镜像地址，
 * 所以这里内置域名白名单 + 解析后 IP 校验，防止配置被人改成内网地址形成 SSRF。
 * 只定义函数，不产生任何输出。
 */

/** 站点根目录（带尾斜杠） */
function mnbt_updater_root()
{
	return defined('ROOT') ? ROOT : rtrim(str_replace('\\', '/', dirname(__DIR__)), '/') . '/';
}

/** 默认配置：仓库与镜像 */
function mnbt_updater_defaults()
{
	return ['repo' => '1181469655/MNBT', 'mirrors' => ['https://gh-proxy.com/'], 'github_token' => ''];
}

/** 读 cf_up.php 的 $mn_conf（页面已 include 时直接复用，避免重复读文件） */
function mnbt_updater_read_conf()
{
	if (isset($GLOBALS['mn_conf']) && is_array($GLOBALS['mn_conf'])) {
		return $GLOBALS['mn_conf'];
	}
	$mn_conf = [];
	$cf = mnbt_updater_root() . 'cf_up.php';
	if (is_file($cf)) {
		include $cf;
	}
	return is_array($mn_conf) ? $mn_conf : [];
}

/** owner/repo 规范化，非法返回空串 */
function mnbt_updater_norm_repo($repo)
{
	$repo = trim((string)$repo);
	$repo = preg_replace('#^https?://github\.com/#i', '', $repo);
	$repo = trim($repo, " \t\n\r/");
	if (preg_match('#^([A-Za-z0-9._\-]{1,60})/([A-Za-z0-9._\-]{1,80})$#', $repo, $m)) {
		// 仓库名带 .git 后缀时剥掉，GitHub API 不接受
		return $m[1] . '/' . preg_replace('#\.git$#', '', $m[2]);
	}
	return '';
}

/** 镜像地址规范化：支持只填域名，统一补协议与尾斜杠；非法返回空串 */
function mnbt_updater_norm_mirror($mirror)
{
	$mirror = trim((string)$mirror);
	if ($mirror === '') return '';
	if (!preg_match('#^https?://#i', $mirror)) {
		if (!preg_match('#^[A-Za-z0-9.\-]{3,120}$#', $mirror)) return '';
		$mirror = 'https://' . $mirror;
	}
	$mirror = rtrim($mirror, '/') . '/';
	// 双引号写回 cf_up.php，出现引号/反斜杠/美元符会破坏配置文件
	if (!preg_match('#^https?://[A-Za-z0-9.\-:/_]+$#', $mirror)) return '';
	$host = strtolower((string)parse_url($mirror, PHP_URL_HOST));
	if ($host === '' || strpos($host, '.') === false) return '';
	return $mirror;
}

/** 更新配置：仓库、镜像列表、Token；缺省值兜底 */
function mnbt_updater_config()
{
	$mn_conf = mnbt_updater_read_conf();
	$def = mnbt_updater_defaults();
	$up = isset($mn_conf['update']) && is_array($mn_conf['update']) ? $mn_conf['update'] : [];

	$repo = isset($up['repo']) ? mnbt_updater_norm_repo($up['repo']) : '';
	if ($repo === '') $repo = $def['repo'];

	$mirrors = [];
	$mirrors_set = false;
	if (isset($up['mirrors'])) {
		// 显式配置过就按配置走，包括被清空（只走 github 直连）
		$mirrors_set = true;
		$raw = is_array($up['mirrors']) ? $up['mirrors'] : preg_split('/\r\n|\r|\n/', (string)$up['mirrors']);
		foreach ($raw as $m) {
			$n = mnbt_updater_norm_mirror($m);
			if ($n !== '' && !in_array($n, $mirrors, true)) $mirrors[] = $n;
		}
	}
	if (!$mirrors_set && !$mirrors) $mirrors = $def['mirrors'];

	$token = trim((string)($up['github_token'] ?? ''));
	if ($token !== '' && !preg_match('#^[A-Za-z0-9._\-]{1,128}$#', $token)) $token = '';

	return ['repo' => $repo, 'mirrors' => $mirrors, 'github_token' => $token];
}

/**
 * 回写 cf_up.php 的 update 段，其余键原样保留（参照 admin/api/repair.php 的 ary_asd 写法）
 * @param string|null $token null 表示保持原值不变
 */
function mnbt_updater_save_config($repo, $mirrors, $token = null)
{
	$repo = mnbt_updater_norm_repo($repo);
	if ($repo === '') return ['ok' => 0, 'error' => '仓库格式不正确，应为 owner/repo'];

	$clean = [];
	foreach ((array)$mirrors as $m) {
		$n = mnbt_updater_norm_mirror($m);
		if ($n !== '' && !in_array($n, $clean, true)) $clean[] = $n;
	}
	$token_new = null;
	if ($token !== null) {
		$token_new = trim((string)$token);
		if ($token_new !== '' && !preg_match('#^[A-Za-z0-9._\-]{1,128}$#', $token_new)) {
			return ['ok' => 0, 'error' => 'Token 含非法字符，只允许字母、数字与 . _ -'];
		}
	}

	$mn_conf = mnbt_updater_read_conf();
	$old = isset($mn_conf['update']) && is_array($mn_conf['update']) ? $mn_conf['update'] : [];
	$mn_conf['update'] = [
		'repo' => $repo,
		'mirrors' => array_values($clean),
		'github_token' => $token_new === null ? (string)($old['github_token'] ?? '') : $token_new,
	];

	$kr_sxy = ary_asd($mn_conf);
	if ($kr_sxy === '') return ['ok' => 0, 'error' => '配置数据为空，已取消写入'];
	$cf = mnbt_updater_root() . 'cf_up.php';
	if (is_file($cf) && !is_writable($cf)) return ['ok' => 0, 'error' => 'cf_up.php 不可写，请检查文件权限'];
	$wr = @file_put_contents($cf, '<?php $mn_conf=array(' . $kr_sxy . ');?>');
	if ($wr === false) return ['ok' => 0, 'error' => '配置文件写入失败'];
	$GLOBALS['mn_conf'] = $mn_conf;
	return ['ok' => 1, 'error' => ''];
}

/** 下载/接口地址的 host 是否授权：GitHub 官方域 + 配置里的镜像域 */
function mnbt_updater_host_allowed($host, $cfg)
{
	$host = strtolower(trim((string)$host));
	if ($host === '') return false;
	// github.com / api.github.com / codeload.github.com / *.githubusercontent.com（附件与源码包的跳转目标）
	if (preg_match('#^([a-z0-9\-]+\.)*github(usercontent)?\.com$#', $host)) return true;
	foreach ((array)$cfg['mirrors'] as $m) {
		$mh = strtolower((string)parse_url($m, PHP_URL_HOST));
		if ($mh !== '' && $mh === $host) return true;
	}
	return false;
}

/** IP 是否为公网可路由地址 */
function mnbt_updater_ip_public($ip)
{
	return (bool)filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
}

/** 域名解析结果必须全是公网地址，否则视为内网打洞 */
function mnbt_updater_host_public($host, &$err)
{
	static $memo = [];
	$host = strtolower((string)$host);
	if (isset($memo[$host])) {
		// 同一次请求里会反复校验同一批地址，避免重复解析
		if ($memo[$host] === true) return true;
		$err = $memo[$host];
		return false;
	}
	$ips = @gethostbynamel($host);
	if (!is_array($ips)) $ips = [];
	if (function_exists('dns_get_record')) {
		$v6 = @dns_get_record($host, DNS_AAAA);
		if (is_array($v6)) {
			foreach ($v6 as $r) {
				if (!empty($r['ipv6'])) $ips[] = $r['ipv6'];
			}
		}
	}
	if (!$ips) {
		$err = '无法解析域名 ' . $host;
		$memo[$host] = $err;
		return false;
	}
	foreach ($ips as $ip) {
		if (!mnbt_updater_ip_public($ip)) {
			$err = '域名 ' . $host . ' 解析到内网或保留地址，已拒绝';
			$memo[$host] = $err;
			return false;
		}
	}
	$memo[$host] = true;
	return true;
}

/** 完整地址安全检查：协议 + 白名单 + 解析 IP */
function mnbt_updater_url_safe($url, $cfg, &$err)
{
	$url = trim((string)$url);
	if (!preg_match('#^https?://#i', $url)) {
		$err = '仅允许 http/https 地址';
		return false;
	}
	$host = (string)parse_url($url, PHP_URL_HOST);
	if ($host === '') {
		$err = '地址无效';
		return false;
	}
	if (!mnbt_updater_host_allowed($host, $cfg)) {
		$err = '未授权的下载地址：' . $host;
		return false;
	}
	return mnbt_updater_host_public($host, $err);
}

/** GitHub API 单次请求（返回体量很小，可以进内存） */
function mnbt_updater_api_once($url, $cfg, $send_token)
{
	$out = ['ok' => 0, 'json' => null, 'http' => 0, 'error' => ''];
	$err = '';
	if (!mnbt_updater_url_safe($url, $cfg, $err)) {
		$out['error'] = $err;
		return $out;
	}
	$headers = [
		'User-Agent: MNBT-Updater/1.85',
		'Accept: application/vnd.github+json',
	];
	// Token 只用于提速率，发给镜像就等于把凭据交给第三方，所以只有直连才带
	if ($send_token && $cfg['github_token'] !== '') {
		$headers[] = 'Authorization: Bearer ' . $cfg['github_token'];
	}
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HEADER, false);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
	curl_setopt($ch, CURLOPT_TIMEOUT, 20);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	$body = curl_exec($ch);
	$cerr = curl_error($ch);
	$out['http'] = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
	curl_close($ch);
	if ($body === false) {
		$out['error'] = $cerr !== '' ? $cerr : '接口请求失败';
		return $out;
	}
	if ($out['http'] < 200 || $out['http'] >= 300) {
		$js = json_decode((string)$body, true);
		$msg = is_array($js) && isset($js['message']) ? (string)$js['message'] : '';
		$out['error'] = 'HTTP ' . $out['http'] . ($msg !== '' ? '（' . $msg . '）' : '');
		return $out;
	}
	$js = json_decode((string)$body, true);
	if (!is_array($js)) {
		$out['error'] = '接口返回内容无法解析';
		return $out;
	}
	$out['ok'] = 1;
	$out['json'] = $js;
	return $out;
}

/**
 * 调用 GitHub API：先直连 api.github.com，失败再按镜像顺序尝试
 * 国内环境直连 api.github.com 常常不通，但镜像可以代理它，所以这里也要有镜像兜底
 */
function mnbt_updater_api_get($path, $cfg)
{
	$out = ['ok' => 0, 'json' => null, 'http' => 0, 'error' => ''];
	if (!function_exists('curl_init')) {
		$out['error'] = '服务器未启用 curl 扩展';
		return $out;
	}
	$direct = 'https://api.github.com' . $path;
	$urls = [['url' => $direct, 'token' => true]];
	foreach ($cfg['mirrors'] as $m) {
		$urls[] = ['url' => $m . $direct, 'token' => false];
	}
	$errs = [];
	foreach ($urls as $u) {
		$r = mnbt_updater_api_once($u['url'], $cfg, $u['token']);
		if ($r['ok']) return $r;
		$errs[] = ($u['token'] ? '直连' : $u['url']) . '：' . $r['error'];
		$out['http'] = $r['http'];
	}
	$out['error'] = '接口请求失败（' . implode(' / ', $errs) . '）';
	return $out;
}

/**
 * tag 规范化成项目版本号：V1.84 => 1840
 * 与 $WEBQB 的规则一致：主版本占千位、次版本占百十位、补丁占个位（补丁最多记 9）
 */
function mnbt_updater_tag_to_version($tag)
{
	$tag = (string)$tag;
	if (!preg_match('/(\d+)(?:[.\-](\d+))?(?:[.\-](\d+))?/', $tag, $m)) return 0;
	$major = (int)$m[1];
	$minor = isset($m[2]) && $m[2] !== '' ? (int)$m[2] : 0;
	$patch = isset($m[3]) && $m[3] !== '' ? (int)$m[3] : 0;
	return $major * 1000 + $minor * 10 + min(9, $patch);
}

/** tag 显示名（GitHub 上的 tag 已带 V 前缀，这里只做兜底） */
function mnbt_updater_tag_display($tag)
{
	$tag = trim((string)$tag);
	if ($tag === '') return '';
	return (preg_match('/^v/i', $tag) ? '' : 'V') . $tag;
}

/** 从 release 的 assets 里挑自定义 zip 附件 */
function mnbt_updater_pick_asset($assets)
{
	if (!is_array($assets)) return ['url' => '', 'name' => '', 'size' => 0];
	foreach ($assets as $a) {
		if (!is_array($a)) continue;
		$url = isset($a['browser_download_url']) ? (string)$a['browser_download_url'] : '';
		$name = isset($a['name']) ? (string)$a['name'] : '';
		if ($url === '') continue;
		if (!preg_match('#\.zip$#i', $name !== '' ? $name : $url)) continue;
		return ['url' => $url, 'name' => $name, 'size' => (int)($a['size'] ?? 0)];
	}
	return ['url' => '', 'name' => '', 'size' => 0];
}

/**
 * 下载候选地址：包来源优先级为 release 自定义 zip 附件 > GitHub 自动源码包；
 * 每个来源再按 github.com 直连、配置镜像顺序依次尝试
 */
function mnbt_updater_candidates($cfg, $tag, $asset_url)
{
	$srcs = [];
	if ($asset_url !== '') {
		$srcs[] = ['type' => 'asset', 'label' => 'Release 附件', 'url' => $asset_url];
	}
	// archive/refs/tags 会由 GitHub 跳到 codeload，legacy.zip 是 codeload 的直链形式，两种都留作兜底
	$srcs[] = [
		'type' => 'source',
		'label' => 'GitHub 源码包',
		'url' => 'https://github.com/' . $cfg['repo'] . '/archive/refs/tags/' . rawurlencode($tag) . '.zip',
	];
	$srcs[] = [
		'type' => 'source',
		'label' => 'GitHub 源码包（直连 codeload）',
		'url' => 'https://codeload.github.com/' . $cfg['repo'] . '/legacy.zip/refs/tags/' . rawurlencode($tag),
	];

	$out = [];
	foreach ($srcs as $s) {
		$out[] = ['type' => $s['type'], 'label' => $s['label'], 'url' => $s['url'], 'via' => 'github', 'mirror' => ''];
		foreach ($cfg['mirrors'] as $m) {
			$out[] = ['type' => $s['type'], 'label' => $s['label'], 'url' => $m . $s['url'], 'via' => 'mirror', 'mirror' => $m];
		}
	}
	return $out;
}

/** 检查结果缓存文件（后台首页每次加载都要问一遍，不限流会很快打满匿名请求配额） */
function mnbt_updater_cache_file()
{
	$dir = mnbt_updater_root() . 'runtime/cache';
	if (!is_dir($dir)) mnbt_updater_mkdir($dir);
	return $dir . '/github_updater_check.json';
}

/** 检查最新版本；返回展示与下载所需的全部信息 */
function mnbt_updater_check($force = false, $ttl = 1800)
{
	$cfg = mnbt_updater_config();
	$cur = (int)($GLOBALS['WEBQB'] ?? 0);
	$out = [
		'ok' => 0, 'error' => '', 'repo' => $cfg['repo'], 'mirrors' => $cfg['mirrors'],
		'has_token' => ($cfg['github_token'] !== ''), 'current' => 'V' . sprintf('%.2f', $cur / 1000),
		'current_version' => $cur, 'tag' => '', 'latest' => '', 'version' => 0, 'name' => '', 'body' => '',
		'asset_url' => '', 'asset_name' => '', 'asset_size' => 0, 'source' => '', 'source_label' => '',
		'published_at' => '', 'fallback' => 0, 'can_update' => 0, 'candidates' => [],
	];
	if ($cfg['repo'] === '') {
		$out['error'] = '未配置更新仓库';
		return $out;
	}

	// 仓库或本地版本变了要重新问；失败结果最多缓存 5 分钟
	$cf = mnbt_updater_cache_file();
	if (!$force && is_file($cf)) {
		$c = json_decode((string)@file_get_contents($cf), true);
		if (is_array($c) && isset($c['ts'], $c['repo'], $c['ver'], $c['data'])
			&& $c['repo'] === $cfg['repo'] && (int)$c['ver'] === $cur) {
			$life = !empty($c['data']['ok']) ? min((int)($c['ttl'] ?? 1800), (int)$ttl) : min(300, (int)$ttl);
			if (time() - (int)$c['ts'] < $life) {
				return $c['data'];
			}
		}
	}

	$rel = null;
	$err1 = '';
	$api = mnbt_updater_api_get('/repos/' . $cfg['repo'] . '/releases/latest', $cfg);
	if ($api['ok']) {
		$js = $api['json'];
		// latest 接口对非数组返回也会给 200，这里靠 tag_name 判定
		if (isset($js['tag_name']) && mnbt_updater_tag_to_version($js['tag_name']) > 0) {
			$rel = $js;
		}
	} else {
		$err1 = $api['error'];
	}
	if (!is_array($rel)) {
		// latest 不可用（未来 tag 可能不再以 V 开头，或接口限流），退化成列表取版本号最高的一个
		$list = mnbt_updater_api_get('/repos/' . $cfg['repo'] . '/releases?per_page=20', $cfg);
		if (!$list['ok']) {
			$out['error'] = $list['error'] !== '' ? $list['error'] : $err1;
			mnbt_updater_cache_put($cfg, $cur, $out, $ttl);
			return $out;
		}
		$best = null;
		$bestv = 0;
		foreach ($list['json'] as $js) {
			if (!is_array($js) || empty($js['tag_name'])) continue;
			if (!empty($js['draft']) || !empty($js['prerelease'])) continue;
			$v = mnbt_updater_tag_to_version($js['tag_name']);
			if ($v > $bestv) { $bestv = $v; $best = $js; }
		}
		if (!is_array($best)) {
			$out['error'] = $err1 !== '' ? $err1 : 'GitHub 未找到可用的版本发布';
			mnbt_updater_cache_put($cfg, $cur, $out, $ttl);
			return $out;
		}
		$rel = $best;
		$out['fallback'] = 1;
	}

	$tag = (string)$rel['tag_name'];
	$asset = mnbt_updater_pick_asset($rel['assets'] ?? []);
	$ver = mnbt_updater_tag_to_version($tag);

	$out['ok'] = 1;
	$out['tag'] = $tag;
	$out['latest'] = mnbt_updater_tag_display($tag);
	$out['version'] = $ver;
	$out['name'] = (string)($rel['name'] ?? '');
	$out['body'] = (string)($rel['body'] ?? '');
	$out['published_at'] = (string)($rel['published_at'] ?? '');
	$out['asset_url'] = $asset['url'];
	$out['asset_name'] = $asset['name'];
	$out['asset_size'] = $asset['size'];
	$out['can_update'] = ($ver > $cur) ? 1 : 0;
	$out['source'] = $asset['url'] !== '' ? 'asset' : 'source';
	$out['source_label'] = $asset['url'] !== '' ? 'Release 附件 ' . $asset['name'] : 'GitHub 自动源码包（需剥顶层目录）';
	$out['candidates'] = mnbt_updater_candidates($cfg, $tag, $asset['url']);
	mnbt_updater_cache_put($cfg, $cur, $out, $ttl);
	return $out;
}

/** 写检查结果缓存；缓存里不含 Token，只存展示与下载所需数据 */
function mnbt_updater_cache_put($cfg, $cur, $out, $ttl = 1800)
{
	$cf = mnbt_updater_cache_file();
	@file_put_contents($cf, json_encode([
		'ts' => time(),
		'ttl' => (int)$ttl,
		'repo' => $cfg['repo'],
		'ver' => (int)$cur,
		'data' => $out,
	], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
}

/**
 * 流式下载压缩包到临时文件：逐跳校验地址、校验 zip 魔数与字节数，失败即删除
 * @return bool
 */
function mnbt_updater_download($url, $dest, $cfg, &$err)
{
	$err = '';
	if (!function_exists('curl_init')) {
		$err = '服务器未启用 curl 扩展';
		return false;
	}
	if (!mnbt_updater_url_safe($url, $cfg, $err)) return false;
	$fp = @fopen($dest, 'wb');
	if (!$fp) {
		$err = '无法创建临时文件 ' . $dest;
		return false;
	}
	$cur = (string)$url;
	$ok = false;
	$heads = [];
	for ($hop = 0; $hop < 6; $hop++) {
		if (!mnbt_updater_url_safe($cur, $cfg, $err)) break;
		$heads = [];
		$ch = curl_init($cur);
		curl_setopt($ch, CURLOPT_FILE, $fp);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
		curl_setopt($ch, CURLOPT_TIMEOUT, 300);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($ch, CURLOPT_HTTPHEADER, [
			// 下载请求不带 Token，避免跳转或镜像把它带出去
			'User-Agent: MNBT-Updater/1.85',
			'Accept: application/octet-stream',
		]);
		curl_setopt($ch, CURLOPT_HEADERFUNCTION, function ($ch, $line) use (&$heads) {
			$len = strlen($line);
			$p = strpos($line, ':');
			if ($p !== false) {
				$heads[strtolower(trim(substr($line, 0, $p)))] = trim(substr($line, $p + 1));
			}
			return $len;
		});
		$res = curl_exec($ch);
		$cerr = curl_error($ch);
		$code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		if ($res === false) {
			$err = $cerr !== '' ? $cerr : '下载中断';
			break;
		}
		if ($code >= 300 && $code < 400) {
			$loc = $heads['location'] ?? '';
			if ($loc === '') {
				$err = 'HTTP ' . $code . ' 但未返回跳转地址';
				break;
			}
			$next = mnbt_updater_abs_url($cur, $loc);
			if ($next === '') {
				$err = '跳转地址无效';
				break;
			}
			$cur = $next;
			// 跳转响应可能自带一小段 HTML，会把压缩包开头弄脏，跳一次就把文件截回空
			rewind($fp);
			ftruncate($fp, 0);
			continue;
		}
		if ($code !== 200) {
			$err = 'HTTP ' . $code;
			break;
		}
		$ok = true;
		break;
	}
	fflush($fp);
	$bytes = ftell($fp);
	fclose($fp);

	if ($ok) {
		$expect = (int)($heads['content-length'] ?? 0);
		if ($expect > 0 && $expect !== $bytes) {
			$err = '文件不完整，应为 ' . $expect . ' 字节，实际 ' . $bytes . ' 字节';
			$ok = false;
		} elseif ($bytes < 1024) {
			$err = '下载内容过小（' . $bytes . ' 字节），不像有效的压缩包';
			$ok = false;
		} elseif (!mnbt_updater_is_zip($dest)) {
			$err = '下载内容不是有效的 zip 压缩包（可能是镜像返回的错误页）';
			$ok = false;
		}
	}
	if (!$ok) {
		@unlink($dest);
	}
	return $ok;
}

/** 相对跳转地址转绝对 */
function mnbt_updater_abs_url($base, $loc)
{
	$loc = trim((string)$loc);
	if ($loc === '') return '';
	if (preg_match('#^https?://#i', $loc)) return $loc;
	$p = parse_url($base);
	if (!$p || empty($p['host'])) return '';
	$scheme = isset($p['scheme']) ? $p['scheme'] : 'https';
	if (strpos($loc, '/') === 0) return $scheme . '://' . $p['host'] . $loc;
	$dir = isset($p['path']) ? substr($p['path'], 0, strrpos($p['path'], '/') + 1) : '/';
	return $scheme . '://' . $p['host'] . $dir . $loc;
}

/** zip 魔数校验 */
function mnbt_updater_is_zip($file)
{
	if (!is_file($file)) return false;
	$fp = @fopen($file, 'rb');
	if (!$fp) return false;
	$magic = fread($fp, 4);
	fclose($fp);
	return $magic === "PK\x03\x04" || $magic === "PK\x05\x06";
}

/** zip 条目名安全校验：禁止绝对路径、盘符、反斜杠与 .. 段 */
function mnbt_updater_entry_safe($name)
{
	$name = (string)$name;
	if ($name === '' || strpos($name, "\0") !== false) return false;
	if (strpos($name, '/') === 0) return false;
	if (strpos($name, '\\') !== false) return false;
	if (preg_match('#^[A-Za-z]:#', $name)) return false;
	foreach (explode('/', $name) as $seg) {
		if ($seg === '..') return false;
	}
	return true;
}

/**
 * 解压前扫描：拒绝危险条目、识别需要剥掉的顶层目录、确认剥完之后确实是站点根
 * @return array|null 成功返回 ['strip'=>..,'entries'=>..,'files'=>..]，失败返回 null 并写 $err
 */
function mnbt_updater_zip_analyze($file, &$err)
{
	$err = '';
	if (!class_exists('ZipArchive')) {
		$err = '服务器未启用 Zip 扩展，无法在线更新';
		return null;
	}
	$zip = new ZipArchive();
	$op = $zip->open($file);
	if ($op !== true) {
		$err = '压缩包无法打开（错误码 ' . $op . '），可能下载不完整';
		return null;
	}
	$n = (int)$zip->numFiles;
	if ($n <= 0) {
		$zip->close();
		$err = '压缩包是空的';
		return null;
	}
	$names = [];
	$files = 0;
	for ($i = 0; $i < $n; $i++) {
		$e = $zip->statIndex($i);
		$name = isset($e['name']) ? (string)$e['name'] : '';
		if (!mnbt_updater_entry_safe($name)) {
			$zip->close();
			$err = '压缩包含非法路径，已中止：' . $name;
			return null;
		}
		$names[] = $name;
		if (substr($name, -1) !== '/') $files++;
	}
	$strip = mnbt_updater_zip_strip($names);
	// 附件包（无顶层目录）与源码包（剥掉顶层目录后）都必须落在站点根上，否则视为包内容不对
	if (!mnbt_updater_zip_looks_like_site($names, $strip)) {
		$zip->close();
		$err = '压缩包结构不符合站点根目录，已中止';
		return null;
	}
	$zip->close();
	return ['strip' => $strip, 'entries' => $n, 'files' => $files];
}

/**
 * 顶层目录识别：GitHub 自动源码包的顶层名有两种（MNBT-1.84/ 与 1181469655-MNBT-<sha>/），
 * 所以只按「所有条目共享同一个顶层目录」来判断，不硬编码名字
 */
function mnbt_updater_zip_strip($names)
{
	$top = null;
	foreach ($names as $name) {
		$pos = strpos($name, '/');
		if ($pos === false || $pos === 0) return '';
		$seg = substr($name, 0, $pos);
		if ($top === null) {
			$top = $seg;
		} elseif ($top !== $seg) {
			return '';
		}
	}
	return $top === null ? '' : $top . '/';
}

/** 剥掉顶层目录后是否还是一个站点根 */
function mnbt_updater_zip_looks_like_site($names, $strip)
{
	$need = ['MPHX/function.php', 'admin/ajax.php', 'index.php', 'cf_up.php'];
	$len = strlen($strip);
	foreach ($names as $name) {
		if ($strip !== '' && strncmp($name, $strip, $len) !== 0) continue;
		$rel = rtrim($strip === '' ? $name : substr($name, $len), '/');
		if ($rel !== '' && in_array($rel, $need, true)) return true;
	}
	return false;
}

/** 逐级建目录 */
function mnbt_updater_mkdir($dir)
{
	if (is_dir($dir)) return true;
	return @mkdir($dir, 0755, true);
}

/**
 * 逐条目解压（需要剥顶层目录时 extractTo 做不到，所以统一走这里）
 * @return bool
 */
function mnbt_updater_extract($file, $root, $strip, &$err)
{
	$err = '';
	$zip = new ZipArchive();
	if ($zip->open($file) !== true) {
		$err = '压缩包打开失败';
		return false;
	}
	$n = (int)$zip->numFiles;
	for ($i = 0; $i < $n; $i++) {
		$e = $zip->statIndex($i);
		$name = isset($e['name']) ? (string)$e['name'] : '';
		if (!mnbt_updater_entry_safe($name)) {
			$zip->close();
			$err = '压缩包含非法路径，已中止：' . $name;
			return false;
		}
		$rel = $name;
		if ($strip !== '') {
			if (strpos($name, $strip) !== 0) continue;
			$rel = substr($name, strlen($strip));
		}
		if ($rel === '') continue;
		$target = $root . $rel;
		if (substr($name, -1) === '/') {
			if (!is_dir($target) && !mnbt_updater_mkdir($target)) {
				$zip->close();
				$err = '目录创建失败：' . $rel;
				return false;
			}
			continue;
		}
		$dir = dirname($target);
		if (!is_dir($dir) && !mnbt_updater_mkdir($dir)) {
			$zip->close();
			$err = '目录创建失败：' . $dir;
			return false;
		}
		$src = $zip->getStream($name);
		if ($src === false) {
			$zip->close();
			$err = '条目读取失败：' . $rel;
			return false;
		}
		$dst = @fopen($target, 'wb');
		if (!$dst) {
			fclose($src);
			$zip->close();
			$err = '文件写入失败：' . $rel;
			return false;
		}
		stream_copy_to_stream($src, $dst);
		fclose($dst);
		fclose($src);
	}
	$zip->close();
	return true;
}

/** 需要备份还原的本地文件：装完就有、包里有但不能被覆盖 */
function mnbt_updater_local_files()
{
	return ['config.php', 'cf_up.php', 'MPHX/SQ.php', 'install/install.lock'];
}

/** 需要备份还原的本地目录：包里可能带着作者的数据，整目录换回来 */
function mnbt_updater_local_dirs()
{
	return ['api/cookie'];
}

/** 递归删除目录（只用于更新流程自己产生的临时目录） */
function mnbt_updater_rrmdir($dir)
{
	if (!is_dir($dir)) return;
	$items = @scandir($dir);
	if (!is_array($items)) return;
	foreach ($items as $it) {
		if ($it === '.' || $it === '..') continue;
		$p = $dir . '/' . $it;
		if (is_dir($p)) {
			mnbt_updater_rrmdir($p);
		} else {
			@unlink($p);
		}
	}
	@rmdir($dir);
}

/** 递归复制目录 */
function mnbt_updater_copy_dir($src, $dst)
{
	if (!is_dir($src)) return;
	if (!is_dir($dst) && !mnbt_updater_mkdir($dst)) return;
	$items = @scandir($src);
	if (!is_array($items)) return;
	foreach ($items as $it) {
		if ($it === '.' || $it === '..') continue;
		$s = $src . '/' . $it;
		$d = $dst . '/' . $it;
		if (is_dir($s)) {
			mnbt_updater_copy_dir($s, $d);
		} else {
			@copy($s, $d);
		}
	}
}

/**
 * 备份本地文件（记录哪些原本存在，install.lock 之类要按原样决定留还是删）
 * @return array ['dir'=>备份目录,'files'=>[相对路径=>是否已备份],'dirs'=>[...],'present'=>[...]]
 */
function mnbt_updater_backup_local($root, $bak_dir, &$err)
{
	$err = '';
	$st = ['dir' => $bak_dir, 'files' => [], 'dirs' => [], 'present' => []];
	if (!mnbt_updater_mkdir($bak_dir)) {
		$err = '备份目录创建失败：' . $bak_dir;
		return $st;
	}
	foreach (mnbt_updater_local_files() as $rel) {
		$src = $root . $rel;
		$st['present'][$rel] = is_file($src);
		if (!is_file($src)) continue;
		$dst = $bak_dir . '/' . $rel;
		if (!is_dir(dirname($dst)) && !mnbt_updater_mkdir(dirname($dst))) {
			$err = '备份目录创建失败：' . dirname($dst);
			continue;
		}
		if (@copy($src, $dst)) {
			$st['files'][$rel] = 1;
		} else {
			$err = '本地文件备份失败：' . $rel;
		}
	}
	foreach (mnbt_updater_local_dirs() as $rel) {
		$src = rtrim($root, '/') . '/' . $rel;
		$st['present'][$rel] = is_dir($src);
		if (!is_dir($src)) continue;
		mnbt_updater_copy_dir($src, $bak_dir . '/' . $rel);
		$st['dirs'][$rel] = 1;
	}
	return $st;
}

/**
 * 还原本地文件；backup 为空数组时按「跳过备份」处理，只保证不会误判成未安装
 * @return bool
 */
function mnbt_updater_restore_local($root, $backup, &$err)
{
	$err = '';
	$dir = isset($backup['dir']) ? $backup['dir'] : '';
	if ($dir === '' || !is_dir($dir)) return true;
	foreach ($backup['files'] as $rel => $ok) {
		if (empty($ok)) continue;
		$bak = $dir . '/' . $rel;
		if (is_file($bak)) {
			$dst = $root . $rel;
			if (!is_dir(dirname($dst)) && !mnbt_updater_mkdir(dirname($dst))) {
				$err .= '无法还原目录：' . dirname($dst) . '；';
				continue;
			}
			if (!@copy($bak, $dst)) $err .= '本地文件还原失败：' . $rel . '；';
		}
	}
	// 新包带的安装锁不能让站点被判定为未安装，原本没有就必须删掉
	$lock = $root . 'install/install.lock';
	if (empty($backup['files']['install/install.lock']) && is_file($lock)) {
		@unlink($lock);
	}
	foreach ($backup['dirs'] as $rel => $ok) {
		if (empty($ok)) continue;
		$target = rtrim($root, '/') . '/' . $rel;
		mnbt_updater_rrmdir($target);
		mnbt_updater_copy_dir($dir . '/' . $rel, $target);
	}
	// 站点原本没有这些目录时，包里带进来的同名内容一律清掉（例如作者打包残留的 cookie）
	foreach ($backup['present'] as $rel => $existed) {
		if (!empty($existed)) continue;
		$p = rtrim($root, '/') . '/' . $rel;
		if (is_dir($p)) {
			mnbt_updater_rrmdir($p);
		}
	}
	return $err === '';
}

/** 更新流程的兜底状态：任何中断路径（超时、致命错误、用户关页面）都要收拾残局 */
function mnbt_updater_guard_start()
{
	$GLOBALS['mnbt_updater_guard'] = [
		'running' => 1, 'root' => mnbt_updater_root(), 'zip' => '', 'bak' => '',
		'admin_now' => '', 'admin_orig' => '', 'moved' => 0, 'backup' => null, 'restored' => 0,
	];
	register_shutdown_function('mnbt_updater_guard_shutdown');
}

/** 更新兜底状态的某个键 */
function mnbt_updater_guard($key, $val)
{
	if (!isset($GLOBALS['mnbt_updater_guard']) || !is_array($GLOBALS['mnbt_updater_guard'])) {
		$GLOBALS['mnbt_updater_guard'] = [];
	}
	$GLOBALS['mnbt_updater_guard'][$key] = $val;
}

/** 流程正常收尾：关掉兜底，避免残局处理再动一次文件 */
function mnbt_updater_guard_done()
{
	$GLOBALS['mnbt_updater_guard']['running'] = 0;
}

/**
 * 回滚/收尾：还原本地配置 → 把改名后的后台目录还原 → 清临时包与备份
 * 成功与失败都走这里，所以逻辑必须可重复执行
 */
function mnbt_updater_guard_rollback()
{
	$g = $GLOBALS['mnbt_updater_guard'] ?? null;
	if (!is_array($g)) return;
	$root = !empty($g['root']) ? $g['root'] : mnbt_updater_root();
	if (!empty($g['backup']) && empty($g['restored'])) {
		$re = '';
		mnbt_updater_restore_local($root, $g['backup'], $re);
		$GLOBALS['mnbt_updater_guard']['restored'] = 1;
	}
	if (!empty($g['moved']) && !empty($g['admin_orig']) && !empty($g['admin_now']) && $g['admin_now'] !== $g['admin_orig']) {
		if (!file_exists($g['admin_orig'])) {
			@rename($g['admin_now'], $g['admin_orig']);
		} elseif (is_dir($g['admin_orig']) && !is_dir($g['admin_now'] . '/api')) {
			// 新包里恰好带了同名目录：先删掉空的占位目录再改回来
			@rmdir($g['admin_orig']);
			if (!file_exists($g['admin_orig'])) @rename($g['admin_now'], $g['admin_orig']);
		}
	}
	if (!empty($g['zip']) && is_file($g['zip'])) @unlink($g['zip']);
	if (!empty($g['bak']) && is_dir($g['bak'])) mnbt_updater_rrmdir($g['bak']);
}

/** 关机兜底：流程没正常跑完时至少把站点恢复成可访问状态 */
function mnbt_updater_guard_shutdown()
{
	$g = $GLOBALS['mnbt_updater_guard'] ?? null;
	if (!is_array($g) || empty($g['running'])) return;
	mnbt_updater_guard_rollback();
}

/**
 * V1.85 迁移链（MPHX/migrations.php / MN_dbver 游标表）的旧式兼容路径：
 * 仅用于执行发布包里遗留的单文件 update/update.sql，按分号逐句跑，不做 DELIMITER 切分。
 * 新版本请改放 update/update_v<3位或4位>_<slug>.sql，走 mnbt_migrations_run。
 * 注意参数顺序是 mysqli(host, user, pwd, dbname[, port])，旧代码把 dbname 和 pwd 传反了，
 * 导致连接总是失败，这里按正确顺序传
 * @return string 空串表示成功，否则为错误说明
 */
function mnbt_updater_run_sql($file, $dbconfig)
{
	if (!is_file($file)) return '';
	$_sql = @file_get_contents($file);
	if ($_sql === false || trim($_sql) === '') return '';
	$port = !empty($dbconfig['port']) ? (int)$dbconfig['port'] : 3306;
	try {
		$_mysqli = new mysqli($dbconfig['host'], $dbconfig['user'], $dbconfig['pwd'], $dbconfig['dbname'], $port);
	} catch (Throwable $e) {
		return '连接数据库出错：' . $e->getMessage();
	}
	if (mysqli_connect_errno()) {
		return '连接数据库出错：' . mysqli_connect_error();
	}
	$_mysqli->query('set names utf8;');
	foreach (explode(';', $_sql) as $_value) {
		if (trim((string)$_value) === '') continue;
		$_mysqli->query($_value);
	}
	$_mysqli->close();
	return '';
}
