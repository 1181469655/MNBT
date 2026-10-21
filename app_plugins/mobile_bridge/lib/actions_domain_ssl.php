<?php
/**
 * mobile_bridge - 域名与 SSL 组 action（PRD §5.5 / 交接 §3.6）
 *
 * 域名的判定式逐条照抄已上线的 domain_shop（user/api/bind.php），避免 APP 与网页面板给出两种结果：
 * - 计数口径 = GetLogsy(站点域名表) + urlzmlls(子目录绑定) 的条数，拦截条件是 count >= ymbds+1，
 *   ymbds 为 0/空 视为不限；
 * - 目录绑定的分流看 path：path 含 '/' 是普通域名（btapi_addym/delym），不含 '/' 是子目录绑定
 *   （addzml/delzml）——所以 bridge 把 APP 传的 dir 归一：空或 '/' 走普通绑定，去掉斜杠后非空走目录绑定；
 * - webname 恒取 $yhc['sqldz']（主域），绝不采信 APP 传的同名参数（APP 为兼容历史把 domain 双写进了 webname）；
 * - 禁添本站 IP、禁删主机主域、售卖二级域名（plg_domain_product.jg>0）三条保护一并保留，
 *   其中售卖表只在表存在时校验（未装 domain_shop 的客户不受影响）。
 *
 * domain.list 因此不再代理核心 listurl（那个分支丢了 port 和 path），改为直接合并两个 BT 读接口；
 * ssl 位取站点级证书状态（本面板证书按站点签发，同站域名共享），节点不可达时回 false。
 * 每行还带 dir（绑定目录，'/'=整站绑定），对应网页面板表格的「目录」列。
 *
 * 改前缀 = domain.prefix.set，照抄 domain_shop 的 p_domain_seturl：面板没有重命名原语，
 * 实现是「删旧 + 按新前缀加新」两步，第二步失败时旧域名已经删掉了，错误文案里会明说。
 *
 * 日志：域名增删由 bridge 记（domain_shop 的 AJAX 不在这次调用链上）；
 * SSL 的申请/续签/设置/关闭/强制 HTTPS 核心 ssl.php 已各自 logjl，bridge 一条都不重复记。
 */
if (!defined('IN_CRONLITE')) exit;

if (!function_exists('mnbt_app_domain_rows')) {
	/**
	 * 站点全量域名行（普通域名 + 子目录绑定），形状与 domain_shop 的 urllist 一致：
	 * [{name, port, addtime, path}]，path='/' 表示普通域名绑定。
	 */
	function mnbt_app_domain_rows()
	{
		global $api, $zjid;
		$rows = [];
		$normal = is_object($api) && method_exists($api, 'GetLogsy') ? ($api->GetLogsy($zjid) ?: []) : [];
		if (!is_array($normal)) $normal = [];
		foreach ($normal as $val) {
			if (!is_array($val)) continue;
			$rows[] = [
				'name' => (string)($val['name'] ?? ($val['domain'] ?? '')),
				'port' => (string)($val['port'] ?? ''),
				'addtime' => (string)($val['addtime'] ?? ''),
				'path' => '/',
			];
		}
		$bind = is_object($api) && method_exists($api, 'urlzmlls') ? ($api->urlzmlls($zjid) ?: []) : [];
		foreach ((is_array($bind) ? ($bind['binding'] ?? []) : []) as $val) {
			if (!is_array($val)) continue;
			$rows[] = [
				'name' => (string)($val['domain'] ?? ''),
				'port' => (string)($val['port'] ?? ''),
				'addtime' => (string)($val['addtime'] ?? ''),
				'path' => (string)($val['path'] ?? ''),
			];
		}
		return $rows;
	}
}

if (!function_exists('mnbt_app_domain_names')) {
	/** 当前站点全部域名（含子目录绑定），返回字符串数组 */
	function mnbt_app_domain_names()
	{
		$names = [];
		foreach (mnbt_app_domain_rows() as $row) {
			if ($row['name'] !== '') $names[] = $row['name'];
		}
		return array_values(array_unique($names));
	}
}

if (!function_exists('mnbt_app_site_has_cert')) {
	/** 站点级证书开关（核心 sqssl 也是用 getsslpem 的 status 判「SSL 已开启」） */
	function mnbt_app_site_has_cert()
	{
		static $cached = null;
		if ($cached !== null) return $cached;
		global $api, $yhc;
		$data = is_object($api) && method_exists($api, 'getsslpem') ? (@$api->getsslpem($yhc['sqldz']) ?: []) : [];
		$cached = is_array($data) && mnbt_app_is_on($data['status'] ?? false);
		return $cached;
	}
}

if (!function_exists('mnbt_app_check_ymbds_limit')) {
	/** 域名上限校验：数据源与判定式都照抄 domain_shop 的 bind.php:71-75 */
	function mnbt_app_check_ymbds_limit($rows = null)
	{
		global $yhc;
		$max = (int)($yhc['ymbds'] ?? 0);
		if ($max <= 0) return;
		if (!is_array($rows)) $rows = mnbt_app_domain_rows();
		$cur = count($rows);
		if ($cur >= $max + 1) {
			mnbt_app_err(1007, '添加失败！域名已达到最大绑定数！（当前 ' . $cur . '/上限 ' . $max . '，如需继续请先删除闲置域名）');
		}
	}
}

if (!function_exists('mnbt_app_domain_product_guard')) {
	/** 未装/无表时直接放行；有售卖域名且标价>0 时禁止自定义添加（照抄 bind.php:79-91） */
	function mnbt_app_domain_product_guard($domain)
	{
		global $DB;
		if (!is_object($DB)) return;
		$tbl = @$DB->get_row_prepare("SHOW TABLES LIKE 'plg_domain_product'");
		if (!is_array($tbl)) return;
		$list = @$DB->get_all_prepare("SELECT * FROM `plg_domain_product` ORDER BY `id` DESC LIMIT 9999") ?: [];
		foreach ($list as $res) {
			if ($domain === '' || strpos($domain, (string)($res['url'] ?? '')) === false) continue;
			if ((float)($res['jg'] ?? 0) > 0) {
				mnbt_app_err(1007, '禁止使用自定义添加本站的售卖二级域名');
			}
		}
	}
}

if (!function_exists('mnbt_app_domain_dir_mode')) {
	/** APP 的 dir -> 面板 dirs 语义：返回 ['subdir'=>bool,'dir'=>string] */
	function mnbt_app_domain_dir_mode($raw)
	{
		$dir = trim((string)$raw);
		if ($dir === '' || $dir === '/') return ['subdir' => false, 'dir' => ''];
		$dir = trim($dir, '/');
		if ($dir === '') return ['subdir' => false, 'dir' => ''];
		if (!preg_match('/^[A-Za-z0-9_\-.]+$/', $dir)) mnbt_app_err(1007, '子目录名不合法：' . $dir);
		return ['subdir' => true, 'dir' => $dir];
	}
}

if (!function_exists('mnbt_app_ssl_cert_info')) {
	/**
	 * PEM 证书 -> APP 的 SslCertDto（expire/issuer/domains/status）。
	 * expire 用 'Y-m-d H:i:s'，APP 的 sslDaysRemaining 取前 10 位按 ISO 日期解析；
	 * domains 优先 SAN，回落 CN，解析不出来回空列表（APP 的 List<String> 收到对象会直接解码失败）。
	 */
	function mnbt_app_ssl_cert_info($pem, $status_raw)
	{
		$status = mnbt_app_is_on($status_raw) ? 'ok' : ((string)$status_raw === '' ? 'none' : (string)$status_raw);
		$out = ['expire' => '', 'issuer' => '', 'domains' => [], 'status' => $status];
		$pem = (string)$pem;
		if ($pem === '' || strpos($pem, 'BEGIN') === false) return $out;
		if (!function_exists('openssl_x509_parse')) {
			$out['status'] = $status === 'none' ? 'none' : 'ok';
			return $out;
		}
		$info = @openssl_x509_parse($pem);
		if (is_array($info)) {
			$valid_to = (int)($info['validTo_time_t'] ?? 0);
			if ($valid_to > 0) $out['expire'] = date('Y-m-d H:i:s', $valid_to);
			$out['issuer'] = (string)($info['issuer']['commonName'] ?? ($info['issuer']['organization'] ?? ''));
			$names = [];
			$cn = (string)($info['subject']['commonName'] ?? '');
			if ($cn !== '') $names[] = $cn;
			$san = $info['extensions']['subjectAltName'] ?? '';
			if (is_string($san) && $san !== '') {
				foreach (explode(',', $san) as $item) {
					$item = trim(str_replace('DNS:', '', trim($item)));
					if ($item !== '' && !filter_var($item, FILTER_VALIDATE_IP)) $names[] = $item;
				}
			}
			$out['domains'] = array_values(array_unique($names));
			if ($out['status'] === 'none') $out['status'] = 'ok';
		}
		return $out;
	}
}

if (!function_exists('mnbt_app_actions_domain_ssl')) {
	function mnbt_app_actions_domain_ssl()
	{
		return [
			'domain.list' => ['auth' => 'ctx', 'cb' => function () {
				global $yhc;
				$main = (string)$yhc['sqldz'];
				$rows = mnbt_app_domain_rows();
				$ssl = mnbt_app_site_has_cert();
				$list = [];
				// 主域永远排第一：APP 用 type=main 判定「主机主域名」标签
				$list[] = ['domain' => $main, 'port' => 80, 'ssl' => $ssl, 'type' => 'main', 'status' => 'ok', 'dir' => '/'];
				foreach ($rows as $row) {
					$name = (string)$row['name'];
					if ($name === '' || $name === $main) continue;
					$path = (string)$row['path'];
					$list[] = [
						'domain' => $name,
						'port' => (int)($row['port'] !== '' ? $row['port'] : 80),
						'ssl' => $ssl,
						'type' => $path === '/' ? 'top' : 'subdir',
						'status' => 'ok',
						// 目录列：网页面板表格里那列 path，'/' 表示整站绑定
						'dir' => $path === '' ? '/' : $path,
					];
				}
				mnbt_app_ok('ok', ['list' => $list, 'total' => count($list), 'limit' => (int)($yhc['ymbds'] ?? 0)]);
			}],
			'domain.subdir.list' => ['auth' => 'ctx', 'cb' => function () {
				global $api, $zjid;
				// APP 的 SubdirItemDto 只能表达「已存在的域名→目录」，弹窗按 ${domain} → ${dir} 渲染并回传 dir；
				// 网页面板给的是「运行目录下的可选目录名」，两种口径都在这一个 action 里返回：
				// list 供现在的 APP 用，dirs 是补的扩展字段，等 APP 改版直接把候选换成 dirs。
				$list = [];
				foreach (mnbt_app_domain_rows() as $row) {
					if ($row['path'] === '/' || $row['path'] === '' || $row['name'] === '') continue;
					$list[] = ['domain' => $row['name'], 'dir' => $row['path']];
				}
				$dirs = [];
				if (is_object($api) && method_exists($api, 'urlzmlls')) {
					$bind = @$api->urlzmlls($zjid);
					foreach ((is_array($bind) ? ($bind['dirs'] ?? []) : []) as $d) {
						if (is_scalar($d) && (string)$d !== '') $dirs[] = (string)$d;
					}
				}
				mnbt_app_ok('ok', ['list' => $list, 'dirs' => $dirs]);
			}],
			'domain.add' => ['auth' => 'ctx', 'cb' => function () {
				global $api, $yhc, $zjid, $os_xt, $cert;
				$domain = strtolower(str_replace([' ', "\t"], '', (string)mnbt_app_p('domain')));
				$domain = preg_replace('#^https?://#', '', $domain);
				$domain = rtrim($domain, '/');
				if ($domain === '' || !preg_match('/^[a-z0-9][a-z0-9.\-]*$/', $domain)) mnbt_app_err(1007, '域名格式错误');
				if (strpos($domain, "\n") !== false) mnbt_app_err(1007, '不能使用换行符！');
				preg_match('/\d+\.\d+\.\d+\.\d+/', $domain, $ure);
				$host_part = (strpos($domain, ':') === false) ? $domain : mb_substr($domain, 0, strpos($domain, ':'));
				$btip = (string)(is_array($cert) ? ($cert['btip'] ?? '') : '');
				if ($btip !== '' && ($host_part === $btip || ($ure[0] ?? '') === $btip)) mnbt_app_err(1007, '禁止添加本站IP！');

				$mode = mnbt_app_domain_dir_mode(mnbt_app_p('dir', mnbt_app_p('dirs')));
				$rows = mnbt_app_domain_rows();
				foreach ($rows as $row) {
					if ($row['name'] === $domain) mnbt_app_err(1007, '该域名已绑定：' . $domain);
				}
				mnbt_app_check_ymbds_limit($rows);
				mnbt_app_domain_product_guard($domain);

				$bound = (string)($yhc['sqldz']);
				if ($mode['subdir']) {
					$r = $api->addzml($zjid, $domain, $mode['dir'], $os_xt . $yhc['sqldz']) ?: [];
				} else {
					$r = $api->btapi_addym($zjid, $bound, $domain) ?: [];
				}
				$ok = is_array($r) && mnbt_app_is_on($r['status'] ?? false);
				mnbt_app_log('域名添加', '在APP添加了域名' . $domain . ($mode['subdir'] ? '（目录' . $mode['dir'] . '）' : ''), $ok ? '添加成功' : '添加失败:' . mnbt_app_text($r['msg'] ?? '', '未知错误'));
				if (!$ok) mnbt_app_err(1009, '添加失败' . mnbt_app_text($r['msg'] ?? '', '未知错误'));
				mnbt_app_ok('添加成功', [
					'domain' => $domain,
					'port' => 80,
					'added' => true,
					'bound' => count($rows) + 1,
					'limit' => (int)($yhc['ymbds'] ?? 0),
				]);
			}],
			'domain.del' => ['auth' => 'ctx', 'cb' => function () {
				global $api, $yhc, $zjid, $os_xt;
				$domain = strtolower(str_replace([' ', "\t"], '', (string)mnbt_app_p('domain')));
				if ($domain === '') mnbt_app_err(1007, '缺少 domain');
				if ($domain === (string)$yhc['sqldz']) mnbt_app_err(1007, '禁止删除主机名称');

				// 目录绑定还是普通域名：APP 传了 dir 就用，没传就查域名表（APP 的 DomainItemDto 不带 dir）
				$target = null;
				foreach (mnbt_app_domain_rows() as $row) {
					if ($row['name'] === $domain) { $target = $row; break; }
				}
				$dir = trim((string)mnbt_app_p('dir'));
				$path = $dir !== '' ? $dir : (string)($target['path'] ?? '/');
				$port = (string)mnbt_app_p('port', (string)($target['port'] !== '' ? $target['port'] : '80'));
				if ($port === '') $port = '80';

				if ($path === '/' || $path === '') {
					$r = $api->btapi_delym($zjid, (string)$yhc['sqldz'], $domain, $port) ?: [];
				} else {
					$r = $api->delzml($zjid, $domain, $os_xt . $yhc['sqldz']) ?: [];
				}
				$ok = is_array($r) && mnbt_app_is_on($r['status'] ?? false);
				mnbt_app_log('域名删除', '在APP删除了域名' . $domain, $ok ? '删除成功' : '删除失败:' . mnbt_app_text($r['msg'] ?? '', '未知错误'));
				if (!$ok) mnbt_app_err(1009, '删除失败' . mnbt_app_text($r['msg'] ?? '', '未知错误'));
				mnbt_app_ok('删除成功', ['domain' => $domain, 'port' => (int)$port, 'deleted' => true]);
			}],
			'domain.prefix.set' => ['auth' => 'ctx', 'cb' => function () {
				global $api, $yhc, $zjid, $os_xt, $cert;
				// 改前缀 = 网页面板的 p_domain_seturl（domain_shop/user/api/bind.php:147-204）：
				// 面板没有「重命名」原语，实现就是拿旧域名删一条、按新前缀加一条，
				// 两步各自的成功与否分别回错；这里逐条照抄，包括它的两档上限判定（前置 >、后置 >=）。
				$old = strtolower(str_replace([' ', "\t"], '', (string)mnbt_app_p('domain', mnbt_app_p('old_domain'))));
				$new_prefix = trim((string)mnbt_app_p('prefix', mnbt_app_p('xqz')));
				if ($old === '') mnbt_app_err(1007, '缺少 domain');
				if ($old === (string)$yhc['sqldz']) mnbt_app_err(1007, '禁止删除主机名称');

				$target = null;
				foreach (mnbt_app_domain_rows() as $row) {
					if ($row['name'] === $old) { $target = $row; break; }
				}
				if ($target === null) mnbt_app_err(1007, '未找到该域名：' . $old);

				// 前缀/主域按第一个点拆，与网页面板客户端的 indexOf('.') 一致
				$dot = strpos($old, '.');
				$old_prefix = $dot === false ? '' : substr($old, 0, $dot);
				$zone = $dot === false ? '' : substr($old, $dot + 1);
				if ($zone === '') $zone = (string)$yhc['sqldz'];
				if (!preg_match('/^[0-9a-zA-Z]{1,24}$/', $new_prefix) || !preg_match('/^[0-9a-zA-Z]{1,24}$/', $old_prefix)) {
					mnbt_app_err(1007, '域名前缀不合法！');
				}
				$new_prefix = strtolower($new_prefix);
				$xurl = $new_prefix . '.' . $zone;
				if ($xurl === $old) mnbt_app_err(1007, '新前缀与原名相同');

				$dir = trim((string)mnbt_app_p('dir', mnbt_app_p('path')));
				$path = $dir !== '' ? $dir : (string)($target['path'] ?? '/');
				if ($path === '') $path = '/';
				$port = (string)mnbt_app_p('port', (string)($target['port'] !== '' ? $target['port'] : '80'));
				if ($port === '') $port = '80';

				$rows = mnbt_app_domain_rows();
				$max = (int)($yhc['ymbds'] ?? 0);
				$cur = count($rows);
				if ($max > 0 && $cur > $max + 1) {
					mnbt_app_err(1007, '添加失败！域名已达到最大绑定数！如想继续添加则请删除多余闲置域名！（当前 ' . $cur . '/上限 ' . $max . '）');
				}

				if ($path === '/') {
					$r = $api->btapi_delym($zjid, (string)$yhc['sqldz'], $old, $port) ?: [];
				} else {
					$r = $api->delzml($zjid, $old, $os_xt . $yhc['sqldz']) ?: [];
				}
				$ok = is_array($r) && mnbt_app_is_on($r['status'] ?? false);
				if (!$ok) {
					mnbt_app_log('域名修改', '将域名' . $old . '修改为' . $xurl, '删除失败:' . mnbt_app_text($r['msg'] ?? '', '未知错误'));
					mnbt_app_err(1009, '删除失败' . mnbt_app_text($r['msg'] ?? '', '未知错误'));
				}

				// 删完再判一次：与网页面板同口径（add 用的是 >=），也顺带挡住「新名撞 IP」
				preg_match('/\d+\.\d+\.\d+\.\d+/', $xurl, $ure);
				$btip = (string)(is_array($cert) ? ($cert['btip'] ?? '') : '');
				if ($btip !== '' && ($xurl === $btip || ($ure[0] ?? '') === $btip)) {
					mnbt_app_err(1007, '禁止添加本站IP！');
				}
				$after = count(mnbt_app_domain_rows());
				if ($max > 0 && $after >= $max + 1) {
					mnbt_app_err(1007, '添加失败！域名已达到最大绑定数！（当前 ' . $after . '/上限 ' . $max . '）');
				}
				if (strpos($path, '/') !== false) {
					$r2 = $api->btapi_addym($zjid, (string)$yhc['sqldz'], $xurl . ':' . $port) ?: [];
				} else {
					$r2 = $api->addzml($zjid, $xurl . ':' . $port, $path, $os_xt . $yhc['sqldz']) ?: [];
				}
				$ok2 = is_array($r2) && mnbt_app_is_on($r2['status'] ?? false);
				mnbt_app_log('域名修改', '将域名' . $old . '修改为' . $xurl, $ok2 ? '修改成功' : '修改失败:' . mnbt_app_text($r2['msg'] ?? '', '未知错误'));
				if (!$ok2) mnbt_app_err(1009, '添加失败' . mnbt_app_text($r2['msg'] ?? '', '未知错误') . '（原域名 ' . $old . ' 已删除，请重新添加）');
				mnbt_app_ok('修改成功', [
					'domain' => $xurl,
					'old_domain' => $old,
					'port' => (int)$port,
					'dir' => $path,
					'added' => true,
					'bound' => $after + 1,
					'limit' => $max,
				]);
			}],
			'ssl.get' => ['auth' => 'ctx', 'cb' => function () {
				// 核心 getssl 返回裸 JSON（无 qk/success），且其中 status 字段是「有没有证书」
				// 的业务状态：无证书时回 false，会被 adapter 形态3 误判成请求失败，
				// 导致「没有证书」这个正常态在 APP 上变成整页报错。故不按 $s 短路。
				mnbt_app_proxy_run('ssl.php', 'getssl', [], function ($s, $c, $m, $d) {
					$d = is_array($d) ? $d : [];
					$pem = mnbt_app_text($d['cert_data'] ?? '', '');
					$cert = mnbt_app_ssl_cert_info($pem, $d['status'] ?? '');
					return [true, 0, 'ok', [
						'ssl' => $cert,
						'force_https' => mnbt_app_is_on($d['httpTohttps'] ?? false),
						'pem' => $pem,
						'key' => mnbt_app_text($d['key'] ?? '', ''),
						'type' => mnbt_app_text($d['type'] ?? '', ''),
						'csr' => mnbt_app_text($d['csr'] ?? '', ''),
					]];
				});
			}],
			'ssl.apply' => ['auth' => 'ctx', 'cb' => function () {
				mnbt_app_ssl_run('false');
			}],
			'ssl.renew' => ['auth' => 'ctx', 'cb' => function () {
				mnbt_app_ssl_run('true');
			}],
			'ssl.custom' => ['auth' => 'ctx', 'cb' => function () {
				$key = (string)mnbt_app_p('key');
				$pem = (string)mnbt_app_p('pem');
				if ($key === '' || $pem === '') mnbt_app_err(1007, '错误！禁止留空！');
				mnbt_app_proxy_run('ssl.php', 'setssl', ['key' => $key, 'pem' => $pem], function ($s, $c, $m, $d) use ($key, $pem) {
					// 证书内容不进日志，只记有无
					return [$s, $c, $m, ['saved' => (bool)$s, 'has_key' => $key !== '', 'has_pem' => $pem !== '']];
				});
			}],
			'ssl.close' => ['auth' => 'ctx', 'cb' => function () {
				mnbt_app_proxy_run('ssl.php', 'clossl', [], function ($s, $c, $m, $d) {
					return [$s, $c, $m, ['closed' => (bool)$s]];
				});
			}],
			'ssl.force_https' => ['auth' => 'ctx', 'cb' => function () {
				$qk = (string)mnbt_app_p('qk', '1');
				$on = $qk !== '0' && $qk !== 'false';
				$kg = $on ? 'true' : 'false';
				mnbt_app_proxy_run('ssl.php', 'httpsqz', ['qk' => $kg], function ($s, $c, $m, $d) use ($on) {
					return [$s, $c, $m, ['force_https' => $s ? $on : false]];
				});
			}],
		];
	}
}

if (!function_exists('mnbt_app_ssl_list')) {
	/** APP 不传域名列表时自动取站点全部域名（含 www 主域）做申请/续签 */
	function mnbt_app_ssl_list()
	{
		$list = $_POST['domains'] ?? null;
		if (is_string($list) && $list !== '') {
			$decoded = json_decode($list, true);
			$list = is_array($decoded) ? $decoded : preg_split('/[\s,;]+/', $list);
		}
		if (!is_array($list) || $list === []) $list = mnbt_app_domain_names();
		$out = [];
		foreach ($list as $item) {
			if (!is_scalar($item)) continue;
			$s = trim((string)$item);
			if ($s !== '') $out[] = $s;
		}
		return array_values(array_unique($out));
	}
}

if (!function_exists('mnbt_app_ssl_run')) {
	/**
	 * 申请(false)/续签(true) 共用 gn=sqssl；核心 ssl.php:19 已 logjl，这里不再记。
	 * type=true 时不校验「SSL 已开启」（核心的分支逻辑），所以续签要提前确认已有证书：
	 * 没证书就 1007，否则 APP 上点「续签」会拿到宝塔的一句莫名失败。
	 */
	function mnbt_app_ssl_run($type)
	{
		$list = mnbt_app_ssl_list();
		if ($list === []) mnbt_app_err(1007, '未获取到可申请的域名，请先绑定域名');
		if ($type === 'true' && !mnbt_app_site_has_cert()) mnbt_app_err(1007, '当前站点没有生效的 SSL 证书，无法续签，请改用「申请证书」');
		mnbt_app_proxy_run('ssl.php', 'sqssl', ['list' => $list, 'type' => $type], function ($s, $c, $m, $d) use ($list) {
			return [$s, $c, $m, [
				'order_id' => '',
				'domains' => $list,
				'result' => (string)$m,
				'expire' => '',
			]];
		});
	}
}
