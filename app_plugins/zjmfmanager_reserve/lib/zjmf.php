<?php
/**
 * zjmfmanager_reserve 插件 - 辅助函数库
 *
 * 提供：URL/渲染/认证/金额辅助、商品、订单、主机、日志的数据库操作，
 * 以及主机开通编排（支付成功后调用上游开通，失败自动退款）。
 */

if (!defined('IN_CRONLITE')) {
	exit;
}

/* ============================================================
 *  常量
 * ============================================================ */

/** 业务类型标识（MN_dd.lx），用于 order.paid 钩子过滤 */
define('ZJMF_LX', 'zjmf');

/** 本地订单号前缀 */
define('ZJMF_ORDER_PREFIX', 'ZJM');

/* ============================================================
 *  URL / 渲染辅助
 * ============================================================ */

/** 生成带站点 base path 前缀的 URL。 */
function zjmf_url($path = '')
{
	$scriptName = isset($_SERVER['SCRIPT_NAME'])
		? str_replace('\\', '/', $_SERVER['SCRIPT_NAME']) : '';
	$basePath = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');
	if ($basePath === '.' || $basePath === '/') {
		$basePath = '';
	}
	// 使用查询参数路由（index.php?_r=/path），避免依赖 Web 服务器 rewrite
	$p = ltrim($path, '/');
	$qpos = strpos($p, '?');
	if ($qpos !== false) {
		$route = substr($p, 0, $qpos);
		$query = substr($p, $qpos + 1);
		return $basePath . '/index.php?_r=/' . $route . '&' . $query;
	}
	return $basePath . '/index.php?_r=/' . $p;
}

/** 插件静态资源 URL。 */
function zjmf_asset_url($path = '')
{
	return mnbt_plugin_url('zjmfmanager_reserve', 'assets/' . ltrim($path, '/'));
}

/** 管理员端插件页面 URL（admin/plugin.php?p=zjmfmanager_reserve&page=xxx）。 */
function zjmf_admin_url($page, $extra = '')
{
	$base = 'plugin.php?p=zjmfmanager_reserve&page=' . rawurlencode($page);
	if ($extra !== '') {
		$base .= '&' . ltrim($extra, '&');
	}
	return $base;
}

/** 金额（分）→ 元（保留 2 位小数）。 */
function zjmf_format_cents($cents)
{
	return number_format((int)$cents / 100, 2, '.', '');
}

/** 生成本地订单号。 */
function zjmf_order_no()
{
	return ZJMF_ORDER_PREFIX . date('YmdHis') . mt_rand(1000, 9999);
}

/** 获取当前登录的 user_info 用户，未登录跳转登录页。 */
function zjmf_require_user()
{
	if (!function_exists('user_info_auth_current')) {
		http_response_code(500);
		echo '需要先启用 user_info 插件';
		exit;
	}
	$user = user_info_auth_current();
	if (!$user) {
		header('Location: ' . zjmf_url('account/login'));
		exit;
	}
	return $user;
}

/** 渲染用户端视图。 */
function zjmf_render($view, $vars = [])
{
	$vars['current_user'] = $vars['current_user']
		?? (function_exists('user_info_auth_current') ? user_info_auth_current() : null);
	extract($vars, EXTR_SKIP);
	$viewFile = mnbt_plugin_path('zjmfmanager_reserve') . 'views/' . $view . '.php';
	if (!is_file($viewFile)) {
		http_response_code(500);
		echo 'View not found: ' . htmlspecialchars($view);
		return;
	}
	include $viewFile;
}

/** 渲染管理员端视图。 */
function zjmf_render_admin($view, $vars = [])
{
	extract($vars, EXTR_SKIP);
	$viewFile = mnbt_plugin_path('zjmfmanager_reserve') . 'views/admin/' . $view . '.php';
	if (!is_file($viewFile)) {
		http_response_code(500);
		echo 'Admin view not found: ' . htmlspecialchars($view);
		return;
	}
	include $viewFile;
}

/** 输出 JSON 并退出。 */
function zjmf_json($code, $extra = [])
{
	@header('Content-Type: application/json; charset=UTF-8');
	$payload = ['code' => $code];
	if (is_array($extra)) {
		$payload = array_merge($payload, $extra);
	}
	echo json_encode($payload, JSON_UNESCAPED_UNICODE);
	exit;
}

/**
 * 获取 AES-256 加密密钥（32 字节原始密钥，不可用时返回 null）。
 * 来源优先级：
 *   1. 环境变量 / 常量 MNBT_SECRET_KEY（任意字符串，sha256 派生 32 字节）
 *   2. 站点根 runtime/zjmf/zjmf_secret.key 密钥文件（插件目录之外，
 *      runtime/ 为仓库既有数据目录惯例并带 .htaccess 禁止 Web 访问；
 *      不存在时自动生成 64 hex 字符随机密钥并尝试 chmod 600）
 * 两者均不可用时返回 null（调用方退回旧 authcode 并记日志）。
 */
function zjmf_secret_key_raw()
{
	static $cached = null;
	if ($cached !== null) {
		return $cached['key'] ?? null;
	}
	$cached = ['key' => null];
	// 1. 环境变量 / 常量
	$secret = getenv('MNBT_SECRET_KEY');
	if ($secret === '' || $secret === false) {
		$secret = defined('MNBT_SECRET_KEY') ? (string)constant('MNBT_SECRET_KEY') : '';
	}
	if ($secret !== '') {
		$cached['key'] = hash('sha256', (string)$secret, true);
		return $cached['key'];
	}
	// 2. 密钥文件（站点根 runtime/zjmf/，插件 data 目录之外）
	$base = defined('ROOT') ? ROOT : dirname(dirname(dirname(__DIR__))) . '/';
	$dir = $base . 'runtime/zjmf';
	$file = $dir . '/zjmf_secret.key';
	$secret = '';
	if (is_file($file)) {
		$secret = trim((string)@file_get_contents($file));
	}
	if ($secret === '') {
		// 自动生成 64 hex 字符随机密钥并落盘
		if (!is_dir($dir) && !@mkdir($dir, 0755, true) && !is_dir($dir)) {
			@error_log('[zjmfmanager_reserve] 加密密钥目录创建失败：' . $dir);
			return null;
		}
		// 目录防 Web 直接访问（.htaccess 拒绝 + 空 index.html 防目录列举）
		$ht = $dir . '/.htaccess';
		if (!is_file($ht)) {
			@file_put_contents($ht, "Deny from all\n");
		}
		$ix = $dir . '/index.html';
		if (!is_file($ix)) {
			@file_put_contents($ix, '');
		}
		$secret = bin2hex(random_bytes(32));
		if (@file_put_contents($file, $secret) === false) {
			@error_log('[zjmfmanager_reserve] 加密密钥文件写入失败：' . $file
				. '，退回旧 authcode 加密');
			return null;
		}
		@chmod($file, 0600);
	}
	$cached['key'] = hash('sha256', $secret, true);
	return $cached['key'];
}

/**
 * 加密上游主机密码等敏感信息。
 * 密钥可用时 AES-256-GCM，密文格式 'v2:' + base64(nonce.tag.ciphertext)；
 * 密钥不可用时退回旧 authcode（记日志）。
 */
function zjmf_encrypt($plain)
{
	$plain = (string)$plain;
	if ($plain === '') {
		return '';
	}
	$key = zjmf_secret_key_raw();
	if ($key !== null) {
		$nonce = random_bytes(12);
		$tag = '';
		$cipher = openssl_encrypt($plain, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $nonce, $tag);
		if ($cipher !== false) {
			return 'v2:' . base64_encode($nonce . $tag . $cipher);
		}
		@error_log('[zjmfmanager_reserve] AES-256-GCM 加密失败，退回旧 authcode');
	}
	return authcode($plain, 'ENCODE', SYS_KEY);
}

/**
 * 密文解密（防御式）。
 * 'v2:' 前缀走 AES-256-GCM（与 zjmf_encrypt 对称）；无前缀走旧 authcode
 * 解密以兼容存量数据。authcode 解密分支在 PHP 8 下对乱码密文会抛
 * TypeError，这里对空/过短密文直接返回，异常兜底为空串。
 */
function zjmf_decrypt($cipher)
{
	$cipher = (string)$cipher;
	if ($cipher === '') {
		return '';
	}
	if (strpos($cipher, 'v2:') === 0) {
		$key = zjmf_secret_key_raw();
		if ($key === null) {
			return ''; // 密钥不可用（如密钥文件被删），无法解密
		}
		$raw = base64_decode(substr($cipher, 3), true);
		if ($raw === false || strlen($raw) <= 12 + 16) {
			return '';
		}
		$nonce = substr($raw, 0, 12);
		$tag = substr($raw, 12, 16);
		$ct = substr($raw, 28);
		$plain = openssl_decrypt($ct, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $nonce, $tag);
		return $plain === false ? '' : $plain;
	}
	if (strlen($cipher) <= 4) {
		return ''; // 未设置密码或非 authcode 密文
	}
	try {
		$out = authcode($cipher, 'DECODE', SYS_KEY);
		return is_string($out) ? $out : '';
	} catch (Throwable $e) {
		return '';
	}
}

/** 写操作日志。 */
function zjmf_log($user_id, $order_no, $action, $result, $content, $supplier_id = 0)
{
	global $DB, $date;
	$now = $date ?: date('Y-m-d H:i:s');
	$DB->query_prepare(
		"INSERT INTO MN_plugin_zjmf_log
		 (user_id, supplier_id, order_no, action, result, content, created_at)
		 VALUES (?,?,?,?,?,?,?)",
		[(int)$user_id, (int)$supplier_id, (string)$order_no, (string)$action,
		 (string)$result, (string)$content, $now]
	);
}

/** 日志列表（管理员，分页）。 */
function zjmf_log_list_all($page = 1, $per_page = 30)
{
	global $DB;
	$page = max(1, (int)$page);
	$per_page = max(1, min(200, (int)$per_page));
	$offset = ($page - 1) * $per_page;
	$count_row = $DB->get_row_prepare("SELECT COUNT(*) AS cnt FROM MN_plugin_zjmf_log");
	$total = $count_row ? (int)$count_row['cnt'] : 0;
	$list = $DB->get_all_prepare(
		"SELECT l.*, u.username AS user_name, s.name AS supplier_name
		 FROM MN_plugin_zjmf_log l
		 LEFT JOIN MN_plugin_user u ON u.id = l.user_id
		 LEFT JOIN MN_plugin_zjmf_supplier s ON s.id = l.supplier_id
		 ORDER BY l.id DESC LIMIT {$offset},{$per_page}"
	) ?: [];
	return ['list' => $list, 'total' => $total, 'page' => $page, 'per_page' => $per_page];
}

/* ============================================================
 *  计费周期
 * ============================================================ */

/** 可用计费周期（key 与上游 billingcycle 一致，WHMCS 风格）。 */
function zjmf_cycles()
{
	return [
		'Monthly'      => ['name' => '月付'],
		'Quarterly'    => ['name' => '季付'],
		'SemiAnnually' => ['name' => '半年付'],
		'Annually'     => ['name' => '年付'],
		'Biennially'   => ['name' => '两年付'],
		'Triennially'  => ['name' => '三年付'],
	];
}

/**
 * 渲染商品简介为规范的展示 HTML。
 * 上游常见 `&lt;li&gt;CPU：4核&lt;/li&gt; &lt;li&gt;内存：4G&lt;/li&gt;...` 格式：
 *   解码实体 → 白名单过滤标签 → 压缩标签间空白 → 外层包裹 <ul> 渲染成列表。
 * 白名单：p/br/b/strong/em/i/ul/ol/li/a[href|target]/span，
 * a 的 href 仅允许 http/https 协议，其余标签的全部属性（含 style/on*、
 * script/iframe 等危险标签）一律剥除，防上游描述注入 XSS。
 */
function zjmf_render_description($raw)
{
	$html = (string)$raw;
	if ($html === '') {
		return '';
	}
	// 解码实体直到稳定（兼容单/双重编码）
	$i = 0;
	do {
		$prev = $html;
		$html = html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8');
		$i++;
	} while ($html !== $prev && $i < 3);
	// 第一步：仅保留白名单标签（script/iframe 等连同标签一并剥除）
	$html = strip_tags(
		$html,
		'<p><br><b><strong><em><i><ul><ol><li><a><span>'
	);
	// 第二步：逐标签重建，白名单外的属性全部剥除；a 仅恢复安全的 href/target
	$html = preg_replace_callback(
		'/<([a-zA-Z0-9]+)((?:\s+[^<>]*?)?)(\/?)>/u',
		function ($m) {
			$tag = strtolower($m[1]);
			$attrs = $m[2] ?? '';
			$selfClose = $m[3] ?? '';
			if ($tag === 'a') {
				$out = '<a';
				// href 仅 http/https 协议，杜绝 javascript:/data: 等协议注入
				if (preg_match('/href\s*=\s*(?:"|\')?\s*((?:https?:\/\/)[^"\'\s>]+)/iu', $attrs, $am)) {
					$out .= ' href="' . htmlspecialchars($am[1], ENT_QUOTES) . '"';
				}
				if (preg_match('/target\s*=\s*(?:"|\')?_blank(?:"|\')?/iu', $attrs)) {
					$out .= ' target="_blank" rel="noopener"';
				}
				return $out . '>';
			}
			// 其余标签剥除全部属性（<br/>、<br> 统一输出为 <br>）
			return '<' . $tag . '>';
		},
		$html
	);
	if (stripos($html, '<li') === false) {
		return $html;
	}
	$html = preg_replace('/>\s+</', '><', $html);
	if (stripos($html, '<ul') === false) {
		$html = '<ul>' . $html . '</ul>';
	}
	return $html;
}

/* ============================================================
 *  供应商管理
 * ============================================================ */

/** 获取单个供应商（行数组）。 */
function zjmf_supplier_get($supplier_id)
{
	global $DB;
	return $DB->get_row_prepare(
		"SELECT * FROM MN_plugin_zjmf_supplier WHERE id=? LIMIT 1",
		[(int)$supplier_id]
	) ?: null;
}

/** 全部供应商列表（管理员，按 sort 升序）。 */
function zjmf_supplier_list_all()
{
	global $DB;
	return $DB->get_all_prepare(
		"SELECT * FROM MN_plugin_zjmf_supplier ORDER BY sort ASC, id ASC"
	) ?: [];
}

/** 供应商加价标签（用于列表展示）。 */
function zjmf_supplier_markup_label($supplier)
{
	$type = (int)($supplier['markup_type'] ?? 0);
	$value = (int)($supplier['markup_value'] ?? 0);
	return $type === 1
		? '固定 +' . zjmf_format_cents($value) . ' 元'
		: '比例 +' . ($value / 10) . '%';
}

/** 供应商是否可销售（存在且启用）。 */
function zjmf_supplier_usable($supplier_id)
{
	$supplier = zjmf_supplier_get($supplier_id);
	return $supplier && (int)$supplier['status'] === 1;
}

/**
 * 删除供应商（有商品/订单/主机时拒绝）。
 *
 * @return array ['ok'=>bool, 'msg'=>string]
 */
function zjmf_supplier_delete($supplier_id)
{
	global $DB;
	$supplier_id = (int)$supplier_id;
	$tables = [
		'MN_plugin_zjmf_product' => '商品',
		'MN_plugin_zjmf_order'   => '订单',
		'MN_plugin_zjmf_host'    => '主机',
	];
	foreach ($tables as $table => $label) {
		$row = $DB->get_row_prepare(
			"SELECT COUNT(*) AS cnt FROM {$table} WHERE supplier_id=? LIMIT 1",
			[$supplier_id]
		);
		if ($row && (int)$row['cnt'] > 0) {
			return ['ok' => false, 'msg' => '该供应商下存在' . $label . '数据，无法删除'];
		}
	}
	$ok = $DB->query_prepare(
		"DELETE FROM MN_plugin_zjmf_supplier WHERE id=?",
		[$supplier_id]
	);
	return $ok ? ['ok' => true, 'msg' => '已删除'] : ['ok' => false, 'msg' => '删除失败'];
}

/* ============================================================
 *  商品管理
 * ============================================================ */

/** 获取单个商品。 */
function zjmf_product_get($product_id)
{
	global $DB;
	return $DB->get_row_prepare(
		"SELECT * FROM MN_plugin_zjmf_product WHERE id=? LIMIT 1",
		[(int)$product_id]
	) ?: null;
}

/** 按供应商 + 上游商品 ID 获取商品。 */
function zjmf_product_get_by_up($supplier_id, $up_product_id)
{
	global $DB;
	return $DB->get_row_prepare(
		"SELECT * FROM MN_plugin_zjmf_product
		 WHERE supplier_id=? AND up_product_id=? LIMIT 1",
		[(int)$supplier_id, (int)$up_product_id]
	) ?: null;
}

/** 上架商品列表（用户端，仅所属供应商启用时可见；带供应商名供分组展示）。 */
function zjmf_product_list_active()
{
	global $DB;
	return $DB->get_all_prepare(
		"SELECT p.*, s.name AS supplier_name
		 FROM MN_plugin_zjmf_product p
		 LEFT JOIN MN_plugin_zjmf_supplier s ON s.id = p.supplier_id
		 WHERE p.status=1 AND s.status=1
		 ORDER BY s.sort ASC, p.sort ASC, p.id ASC"
	) ?: [];
}

/** 全部商品列表（管理员，含供应商名）。 */
function zjmf_product_list_all()
{
	global $DB;
	return $DB->get_all_prepare(
		"SELECT p.*, s.name AS supplier_name
		 FROM MN_plugin_zjmf_product p
		 LEFT JOIN MN_plugin_zjmf_supplier s ON s.id = p.supplier_id
		 ORDER BY s.sort ASC, p.sort ASC, p.id ASC"
	) ?: [];
}

/** 解析商品周期 JSON，返回 ['cycle' => ['name'=>, 'price_cents'=>, 'override'=>]]。 */
function zjmf_product_cycles($product)
{
	$raw = isset($product['cycles']) ? json_decode($product['cycles'], true) : null;
	if (!is_array($raw)) {
		return [];
	}
	$map = [];
	// 旧版扁平格式：["Monthly","月",2500]
	if (isset($raw[0]) && !is_array($raw[0])) {
		if ($raw[0] !== '' && isset($raw[2])) {
			$cycle = (string)$raw[0];
			$map[$cycle] = [
				'cycle'             => $cycle,
				'up_cycle'          => '',
				'name'              => (string)$raw[1],
				'price_cents'       => (int)$raw[2],
				'agent_price_cents' => (int)$raw[2],
				'override'          => 0,
			];
		}
		return $map;
	}
	foreach ($raw as $item) {
		$cycle = (string)($item['cycle'] ?? '');
		if ($cycle === '') {
			continue;
		}
		$map[$cycle] = [
			'cycle'             => $cycle,
			'up_cycle'          => (string)($item['up_cycle'] ?? ''),
			'name'              => (string)($item['name'] ?? $cycle),
			'price_cents'       => (int)($item['price_cents'] ?? 0),
			'agent_price_cents' => (int)($item['agent_price_cents'] ?? 0),
			'override'          => (int)($item['override'] ?? 0),
		];
	}
	return $map;
}

/**
 * 计算本地售价（分）。
 *
 * @param int $agentCents   上游代理价（分）
 * @param int $markupType   0=比例 1=固定（分）
 * @param int $markupValue  比例（千分比）或固定加价（分）
 * @return int
 */
function zjmf_calc_price($agentCents, $markupType, $markupValue)
{
	$agentCents = max(0, (int)$agentCents);
	if ($markupType === 1) {
		return max(0, $agentCents + max(0, (int)$markupValue));
	}
	$rate = max(0, (int)$markupValue);
	return max(0, (int)round($agentCents * (1000 + $rate) / 1000));
}

/**
 * 重算商品各周期本地售价并写回 cycles 字段。
 * 加价规则：单品有配置则用单品，否则用所属供应商配置。
 *
 * @param int $product_id
 * @return void
 */
function zjmf_product_recalc_price($product_id)
{
	global $DB, $date;
	$product = zjmf_product_get($product_id);
	if (!$product) {
		return;
	}
	$supplier = zjmf_supplier_get((int)$product['supplier_id']);
	// 单品已配置加价（比例 type=0 或固定 type=1 且 value>0）时用单品规则，否则用供应商
	$hasOwn = (int)($product['markup_type'] ?? 0) !== 0
		|| (int)($product['markup_value'] ?? 0) > 0;
	$markupType = $hasOwn
		? (int)$product['markup_type'] : (int)($supplier['markup_type'] ?? 0);
	$markupValue = $hasOwn
		? (int)$product['markup_value'] : (int)($supplier['markup_value'] ?? 0);

	$cycles = zjmf_product_cycles($product);
	if ($cycles === []) {
		return;
	}
	foreach ($cycles as $cycle => &$cfg) {
		// 管理员手动设置过售价（override>0）时保持不动，否则按加价规则重算
		if ((int)($cfg['override'] ?? 0) > 0) {
			$cfg['price_cents'] = (int)$cfg['override'];
		} else {
			$cfg['price_cents'] = zjmf_calc_price(
				$cfg['agent_price_cents'] ?? 0,
				$markupType,
				$markupValue
			);
		}
	}
	unset($cfg);
	$now = $date ?: date('Y-m-d H:i:s');
	$DB->query_prepare(
		"UPDATE MN_plugin_zjmf_product SET cycles=?, updated_at=? WHERE id=?",
		[json_encode(array_values($cycles), JSON_UNESCAPED_UNICODE), $now, (int)$product_id]
	);
}

/* ============================================================
 *  订单管理
 * ============================================================ */

/** 按 ID 查询订单。 */
function zjmf_order_get($order_id)
{
	global $DB;
	return $DB->get_row_prepare(
		"SELECT * FROM MN_plugin_zjmf_order WHERE id=? LIMIT 1",
		[(int)$order_id]
	) ?: null;
}

/** 按订单号查询订单。 */
function zjmf_order_get_by_no($order_no)
{
	global $DB;
	return $DB->get_row_prepare(
		"SELECT * FROM MN_plugin_zjmf_order WHERE order_no=? LIMIT 1",
		[$order_no]
	) ?: null;
}

/** 用户订单列表（分页）。 */
function zjmf_order_list_by_user($user_id, $page = 1, $per_page = 20)
{
	global $DB;
	$user_id = (int)$user_id;
	$page = max(1, (int)$page);
	$per_page = max(1, min(100, (int)$per_page));
	$offset = ($page - 1) * $per_page;
	$count_row = $DB->get_row_prepare(
		"SELECT COUNT(*) AS cnt FROM MN_plugin_zjmf_order WHERE user_id=?",
		[$user_id]
	);
	$total = $count_row ? (int)$count_row['cnt'] : 0;
	$list = $DB->get_all_prepare(
		"SELECT o.* FROM MN_plugin_zjmf_order o
		 WHERE o.user_id=? ORDER BY o.id DESC LIMIT {$offset},{$per_page}",
		[$user_id]
	) ?: [];
	return ['list' => $list, 'total' => $total, 'page' => $page, 'per_page' => $per_page];
}

/** 全部订单列表（管理员，分页 + 简单筛选）。 */
function zjmf_order_list_all($page = 1, $per_page = 30, $filters = [])
{
	global $DB;
	$page = max(1, (int)$page);
	$per_page = max(1, min(200, (int)$per_page));
	$offset = ($page - 1) * $per_page;

	$where = '1';
	$params = [];
	if (!empty($filters['status'])) {
		$where .= ' AND status=?';
		$params[] = $filters['status'];
	}
	if (!empty($filters['order_no'])) {
		$where .= ' AND order_no LIKE ?';
		$params[] = '%' . $filters['order_no'] . '%';
	}
	if (!empty($filters['user_id'])) {
		$where .= ' AND user_id=?';
		$params[] = (int)$filters['user_id'];
	}
	if (!empty($filters['supplier_id'])) {
		$where .= ' AND supplier_id=?';
		$params[] = (int)$filters['supplier_id'];
	}

	$count_row = $DB->get_row_prepare(
		"SELECT COUNT(*) AS cnt FROM MN_plugin_zjmf_order WHERE {$where}",
		$params
	);
	$total = $count_row ? (int)$count_row['cnt'] : 0;
	$list = $DB->get_all_prepare(
		"SELECT o.*, s.name AS supplier_name
		 FROM MN_plugin_zjmf_order o
		 LEFT JOIN MN_plugin_zjmf_supplier s ON s.id = o.supplier_id
		 WHERE {$where} ORDER BY o.id DESC LIMIT {$offset},{$per_page}",
		$params
	) ?: [];
	return ['list' => $list, 'total' => $total, 'page' => $page, 'per_page' => $per_page];
}

/**
 * 创建本地订单（未支付）。
 *
 * @param array  $user       user_info 当前用户
 * @param array  $product    本地商品行
 * @param string $cycle      计费周期
 * @param array  $cycleCfg   周期配置（name/price_cents）
 * @param string $action     buy/upgrade_config/upgrade_product
 * @param array  $extra      附加字段（up_host_id/host_id/order_params/cost_cents）
 * @return array ['ok'=>bool, 'order_no'=>string, 'order_id'=>int, 'msg'=>string]
 */
function zjmf_order_create($user, $product, $cycle, $cycleCfg, $action = 'buy', $extra = [])
{
	global $DB, $date;
	$now = $date ?: date('Y-m-d H:i:s');
	$order_no = zjmf_order_no();
	$cycleName = (string)($cycleCfg['name'] ?? $cycle);

	$ok = $DB->query_prepare(
		"INSERT INTO MN_plugin_zjmf_order
		 (order_no, action, supplier_id, user_id, product_id, up_product_id,
		  product_name, cycle, cycle_name, amount_cents, cost_cents, order_params,
		  up_order_id, up_host_id, host_id, username, status,
		  pay_time, opened_at, remark, created_at)
		 VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)",
		[
			$order_no,
			$action,
			(int)($product['supplier_id'] ?? 0),
			(int)$user['id'],
			(int)($product['id'] ?? 0),
			(int)($product['up_product_id'] ?? 0),
			(string)($product['name'] ?? ''),
			$cycle,
			$cycleName,
			(int)($cycleCfg['price_cents'] ?? 0),
			(int)($extra['cost_cents'] ?? 0),
			(string)($extra['order_params'] ?? ''),
			(int)($extra['up_order_id'] ?? 0),
			(int)($extra['up_host_id'] ?? 0),
			(int)($extra['host_id'] ?? 0),
			(string)($extra['username'] ?? ''),
			'pending',
			'',
			'',
			'',
			$now,
		]
	);
	if (!$ok) {
		return ['ok' => false, 'order_no' => '', 'order_id' => 0, 'msg' => '订单写入失败'];
	}
	$row = $DB->get_row_prepare(
		"SELECT id FROM MN_plugin_zjmf_order WHERE order_no=? LIMIT 1",
		[$order_no]
	);
	return [
		'ok'       => true,
		'order_no' => $order_no,
		'order_id' => $row ? (int)$row['id'] : 0,
		'msg'      => '',
	];
}

/** 更新订单状态。 */
function zjmf_order_set_status($order_id, $status, $remark = '')
{
	global $DB, $date;
	$now = $date ?: date('Y-m-d H:i:s');
	$extra = '';
	$params = [$status];
	if ($status === 'paid') {
		$extra = ', pay_time=?';
		$params[] = $now;
	} elseif ($status === 'opened') {
		$extra = ', opened_at=?';
		$params[] = $now;
	}
	if ($remark !== '') {
		$extra .= ', remark=?';
		$params[] = $remark;
	}
	$params[] = (int)$order_id;
	return (bool)$DB->query_prepare(
		"UPDATE MN_plugin_zjmf_order SET status=?{$extra} WHERE id=?",
		$params
	);
}

/** 回填订单开通信息。 */
function zjmf_order_fill_opened($order_id, $upOrderId, $upHostId, $username)
{
	global $DB;
	return (bool)$DB->query_prepare(
		"UPDATE MN_plugin_zjmf_order
		 SET up_order_id=?, up_host_id=?, username=?
		 WHERE id=?",
		[(int)$upOrderId, (int)$upHostId, (string)$username, (int)$order_id]
	);
}

/**
 * 创建升级订单（扣款前）。
 *
 * @param array  $user         user_info 当前用户
 * @param array  $host         本地主机映射行
 * @param string $action       upgrade_config / upgrade_product
 * @param int    $amountCents  升级差额（分）
 * @param string $orderParams  升级参数 JSON（selection）
 * @return array ['ok'=>bool, 'order_no'=>string, 'order_id'=>int, 'msg'=>string]
 */
function zjmf_upgrade_order_create($user, $host, $action, $amountCents, $orderParams)
{
	global $DB, $date;
	$now = $date ?: date('Y-m-d H:i:s');
	$order_no = zjmf_order_no();

	$ok = $DB->query_prepare(
		"INSERT INTO MN_plugin_zjmf_order
		 (order_no, action, supplier_id, user_id, product_id, up_product_id,
		  product_name, cycle, cycle_name, amount_cents, cost_cents, order_params,
		  up_order_id, up_host_id, host_id, username, status,
		  pay_time, opened_at, remark, created_at)
		 VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)",
		[
			$order_no,
			$action,
			(int)($host['supplier_id'] ?? 0),
			(int)$user['id'],
			0,
			(int)$host['up_product_id'],
			'升级：' . $host['name'],
			(string)($host['cycle'] ?? ''),
			(string)($host['cycle'] ?? ''),
			(int)$amountCents,
			0,
			(string)$orderParams,
			0,
			(int)$host['up_host_id'],
			(int)$host['id'],
			'',
			'pending',
			'',
			'',
			'',
			$now,
		]
	);
	if (!$ok) {
		return ['ok' => false, 'order_no' => '', 'order_id' => 0, 'msg' => '订单写入失败'];
	}
	$row = $DB->get_row_prepare(
		"SELECT id FROM MN_plugin_zjmf_order WHERE order_no=? LIMIT 1",
		[$order_no]
	);
	return [
		'ok'       => true,
		'order_no' => $order_no,
		'order_id' => $row ? (int)$row['id'] : 0,
		'msg'      => '',
	];
}

/* ============================================================
 *  主机映射管理
 * ============================================================ */

/** 按 ID 查询主机映射。 */
function zjmf_host_get($host_id)
{
	global $DB;
	return $DB->get_row_prepare(
		"SELECT * FROM MN_plugin_zjmf_host WHERE id=? LIMIT 1",
		[(int)$host_id]
	) ?: null;
}

/** 校验主机归属当前用户后返回。 */
function zjmf_host_get_by_user($user_id, $host_id)
{
	global $DB;
	return $DB->get_row_prepare(
		"SELECT h.*, s.name AS supplier_name
		 FROM MN_plugin_zjmf_host h
		 LEFT JOIN MN_plugin_zjmf_supplier s ON s.id = h.supplier_id
		 WHERE h.id=? AND h.user_id=? LIMIT 1",
		[(int)$host_id, (int)$user_id]
	) ?: null;
}

/** 按上游主机 ID 查询映射。 */
function zjmf_host_get_by_up($up_host_id)
{
	global $DB;
	return $DB->get_row_prepare(
		"SELECT * FROM MN_plugin_zjmf_host WHERE up_host_id=? LIMIT 1",
		[(int)$up_host_id]
	) ?: null;
}

/** 用户主机列表（含供应商名）。 */
function zjmf_host_list_by_user($user_id)
{
	global $DB;
	return $DB->get_all_prepare(
		"SELECT h.*, s.name AS supplier_name
		 FROM MN_plugin_zjmf_host h
		 LEFT JOIN MN_plugin_zjmf_supplier s ON s.id = h.supplier_id
		 WHERE h.user_id=? ORDER BY h.id DESC",
		[(int)$user_id]
	) ?: [];
}

/** 全部主机列表（管理员，分页，含供应商名）。 */
function zjmf_host_list_all($page = 1, $per_page = 30)
{
	global $DB;
	$page = max(1, (int)$page);
	$per_page = max(1, min(200, (int)$per_page));
	$offset = ($page - 1) * $per_page;
	$count_row = $DB->get_row_prepare("SELECT COUNT(*) AS cnt FROM MN_plugin_zjmf_host");
	$total = $count_row ? (int)$count_row['cnt'] : 0;
	$list = $DB->get_all_prepare(
		"SELECT h.*, u.username AS user_name, s.name AS supplier_name
		 FROM MN_plugin_zjmf_host h
		 LEFT JOIN MN_plugin_user u ON u.id = h.user_id
		 LEFT JOIN MN_plugin_zjmf_supplier s ON s.id = h.supplier_id
		 ORDER BY h.id DESC LIMIT {$offset},{$per_page}"
	) ?: [];
	return ['list' => $list, 'total' => $total, 'page' => $page, 'per_page' => $per_page];
}

/** 新增主机映射。 */
function zjmf_host_create($data)
{
	global $DB, $date;
	$now = $date ?: date('Y-m-d H:i:s');
	$ok = $DB->query_prepare(
		"INSERT INTO MN_plugin_zjmf_host
		 (supplier_id, user_id, order_id, up_host_id, up_product_id, name, username,
		  password, cycle, status, renew_date, created_at, updated_at)
		 VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)",
		[
			(int)($data['supplier_id'] ?? 0),
			(int)($data['user_id'] ?? 0),
			(int)($data['order_id'] ?? 0),
			(int)($data['up_host_id'] ?? 0),
			(int)($data['up_product_id'] ?? 0),
			(string)($data['name'] ?? ''),
			(string)($data['username'] ?? ''),
			(string)($data['password'] ?? ''),
			(string)($data['cycle'] ?? ''),
			(string)($data['status'] ?? 'active'),
			(string)($data['renew_date'] ?? ''),
			$now,
			$now,
		]
	);
	if (!$ok) {
		return 0;
	}
	$row = $DB->get_row_prepare(
		"SELECT id FROM MN_plugin_zjmf_host
		 WHERE order_id=? ORDER BY id DESC LIMIT 1",
		[(int)($data['order_id'] ?? 0)]
	);
	return $row ? (int)$row['id'] : 0;
}

/** 更新主机缓存信息（状态/到期/周期）。 */
function zjmf_host_update_cache($host_id, $data)
{
	global $DB, $date;
	$sets = [];
	$params = [];
	foreach (['status', 'renew_date', 'cycle', 'up_product_id'] as $key) {
		if (array_key_exists($key, $data)) {
			$sets[] = "`{$key}`=?";
			$params[] = (string)$data[$key];
		}
	}
	if ($sets === []) {
		return false;
	}
	$now = $date ?: date('Y-m-d H:i:s');
	$sets[] = "updated_at=?";
	$params[] = $now;
	$params[] = (int)$host_id;
	return (bool)$DB->query_prepare(
		"UPDATE MN_plugin_zjmf_host SET " . implode(',', $sets) . " WHERE id=?",
		$params
	);
}

/**
 * 归一化上游返回的日期/时间戳为 Y-m-d H:i:s（精确到秒，与 created_at 展示一致）。
 * 魔方财务部分版本接口返回 Unix 时间戳（秒/毫秒），部分返回 Y-m-d / Y-m-d H:i:s；
 * 已含时分秒的标准串原样保留。
 *
 * @param mixed $val
 * @return string
 */
function zjmf_normalize_date($val)
{
	$s = trim((string)$val);
	if ($s === '') {
		return '';
	}
	// 已是标准日期（时间）串：截断到秒粒度并保留
	if (preg_match('/^\d{4}-\d{2}-\d{2}/', $s)) {
		$s = preg_replace(
			'/^(\d{4}-\d{2}-\d{2}(?:[ T]\d{2}:\d{2}(?::\d{2})?)?).*$/',
			'$1',
			$s
		);
		return $s;
	}
	if (preg_match('/^\d+$/', $s)) {
		$t = (int)$s;
		if ($t > 100000000000) {
			$t = (int)($t / 1000); // 毫秒时间戳
		}
		return $t > 0 ? date('Y-m-d H:i:s', $t) : '';
	}
	$t = strtotime($s);
	return $t ? date('Y-m-d H:i:s', $t) : $s;
}

/**
 * 补齐本地主机缺失的上游主机 ID（up_host_id<=0 时），确定性匹配。
 *
 * 匹配键：开通流程保存在订单 order_params.up_host 的本地生成主机标识
 * （upstream.php purchase() add_to_shop 时的 host 参数），与上游
 * host/list 返回的 domain 精确相等才绑定；已带 up_host_id 的正常路径
 * 不会进入本函数。不再做"产品名前缀 + 日期最近"的猜测匹配（曾发生
 * 误绑上游他人主机的越权风险）。
 * 匹配不到不写库（保持 up_host_id=0），仅记一条告警日志提示人工绑定。
 * 本函数只应在开通流程内或管理端调用，用户端 GET 请求不得触发写库。
 *
 * @param array $host MN_plugin_zjmf_host 行
 * @return array 回填后的主机行（未匹配则原样返回）
 */
function zjmf_backfill_host_upid($host)
{
	global $DB, $date;
	if (!is_array($host) || (int)($host['id'] ?? 0) <= 0 || (int)($host['up_host_id'] ?? 0) > 0) {
		return $host;
	}
	$supplierId = (int)($host['supplier_id'] ?? 0);
	if ($supplierId <= 0) {
		return $host;
	}
	$supplier = zjmf_supplier_get($supplierId);
	if (!$supplier || (int)$supplier['status'] !== 1) {
		return $host;
	}
	// 确定性匹配键：订单参数中保存的本地生成 host 标识
	$wantDomain = '';
	$orderNo = '';
	$order = zjmf_order_get((int)($host['order_id'] ?? 0));
	if ($order) {
		$orderNo = (string)($order['order_no'] ?? '');
		$params = json_decode((string)($order['order_params'] ?? ''), true);
		if (is_array($params) && isset($params['up_host'])) {
			$wantDomain = trim((string)$params['up_host']);
		}
	}
	if ($wantDomain === '') {
		// 无匹配键（历史数据/指派单等）：不猜测，提示人工绑定
		zjmf_log((int)($host['user_id'] ?? 0), $orderNo, 'backfill', 'failed',
			'主机 #' . (int)$host['id'] . ' 缺少上游主机 ID 且无可用的确定性匹配键，'
			. '已放弃自动回填，请管理员人工绑定', $supplierId);
		return $host;
	}
	$res = ZjmfUpstream::hostList($supplier, ['orderby' => 'id', 'sort' => 'DESC']);
	$list = empty($res['ok']) ? [] : ($res['data']['list'] ?? []);
	$matched = null;
	foreach ((array)$list as $item) {
		if (is_array($item) && trim((string)($item['domain'] ?? '')) === $wantDomain) {
			$matched = $item;
			break;
		}
	}
	if (!$matched || (int)($matched['id'] ?? 0) <= 0) {
		// 上游列表中无该 domain（主机可能仍在异步创建）：不写库
		zjmf_log((int)($host['user_id'] ?? 0), $orderNo, 'backfill', 'failed',
			'主机 #' . (int)$host['id'] . ' 未能按 host=' . $wantDomain
			. ' 在上游主机列表中确定性匹配，保持 up_host_id=0，请人工核对绑定', $supplierId);
		return $host;
	}
	$upId = (int)$matched['id'];
	// 代码级防重：该上游主机已被其他本地主机绑定时不重复写库
	$dup = $DB->get_row_prepare(
		"SELECT id FROM MN_plugin_zjmf_host
		 WHERE supplier_id=? AND up_host_id=? AND id<>? LIMIT 1",
		[$supplierId, $upId, (int)$host['id']]
	);
	if ($dup) {
		zjmf_log((int)($host['user_id'] ?? 0), $orderNo, 'backfill', 'failed',
			'上游主机 #' . $upId . ' 已被本地主机 #' . (int)$dup['id'] . ' 绑定，'
			. '主机 #' . (int)$host['id'] . ' 放弃自动回填，请人工核对', $supplierId);
		return $host;
	}
	$status = function_exists('zjmf_map_upstream_status')
		? zjmf_map_upstream_status((string)($matched['domainstatus'] ?? ''))
		: (string)($host['status'] ?? '');
	// nextduedate 部分版本为 Unix 时间戳，统一归一化为 Y-m-d
	$renew = zjmf_normalize_date((string)($matched['nextduedate'] ?? $host['renew_date'] ?? ''));
	$now = $date ?: date('Y-m-d H:i:s');
	$DB->query_prepare(
		"UPDATE MN_plugin_zjmf_host
		 SET up_host_id=?, status=?, renew_date=?, updated_at=? WHERE id=?",
		[$upId, $status, $renew, $now, (int)$host['id']]
	);
	$host['up_host_id'] = $upId;
	$host['status'] = $status;
	$host['renew_date'] = $renew;
	return $host;
}

/**
 * 上游状态 → 本地展示状态（统一映射表，主实现）。
 * ZjmfUpstream::mapHostStatus 委托调用本函数；除常规 domainstatus 外
 * 补充 off/true/false/deleted/Unpaid 等分支（true/false/off 多见于
 * DCIM/云主机开关机状态，deleted/Unpaid 见于部分上游版本）。
 */
function zjmf_map_upstream_status($status)
{
	$st = strtolower(trim((string)$status));
	if (in_array($st, ['active', 'on', 'true', 'completed', '运行中'], true)) {
		return 'active';
	}
	if (in_array($st, ['pending', 'wait', 'waiting', 'unpaid', '待开通', '未付款'], true)) {
		return 'pending';
	}
	if (in_array($st, ['suspended', 'suspend', 'paused', 'off', 'false', '已暂停', '已关机'], true)) {
		return 'suspend';
	}
	if (in_array($st, ['cancelled', 'cancel', 'terminated', 'terminate', 'deleted', 'delete', 'fraud', '已终止', '已删除'], true)) {
		return 'terminated';
	}
	return 'unknown';
}

/* ============================================================
 *  主机操作辅助
 * ============================================================ */

/** 操作标识 → 上游 func 名称（视上游模块而定，联调时按实际调整）。 */
function zjmf_action_func($action)
{
	$map = [
		'on'             => 'on',
		'off'            => 'off',
		'reboot'         => 'reboot',
		'reset_password' => 'passwd',
		'reinstall'      => 'reinstall',
	];
	return $map[$action] ?? '';
}

/** 操作成功后建议写入的缓存状态（空表示不修改）。 */
function zjmf_action_status($action)
{
	$map = [
		'on'     => 'active',
		'reboot' => 'active',
		// 关机不写 suspend：本地主机表无独立电源字段，写入 suspend 会与
		// 上游真实状态（domainstatus 仍为 active）脱节，由调用方刷新上游
		// 状态回写真实状态
		'off'    => '',
	];
	return $map[$action] ?? '';
}

/** 用户端主机状态展示标签映射。 */
function zjmf_host_status_label($status)
{
	$map = [
		'active'     => '运行中',
		'suspend'    => '已暂停',
		'pending'    => '待开通',
		'terminated' => '已终止',
		'unknown'    => '未知',
	];
	return $map[$status] ?? $status;
}

/* ============================================================
 *  主机开通（核心编排）
 * ============================================================ */

/**
 * 支付成功后开通主机：调用上游开通，落库映射，失败自动退款。
 *
 * @param int $order_id  MN_plugin_zjmf_order.id
 * @return array ['ok'=>bool, 'msg'=>string, 'host_id'=>int]
 */
function zjmf_open_host($order_id)
{
	global $DB, $date;
	$order = zjmf_order_get($order_id);
	if (!$order) {
		return ['ok' => false, 'msg' => '订单不存在'];
	}
	if ($order['status'] !== 'paid') {
		return ['ok' => false, 'msg' => '订单状态非已支付，无法开通'];
	}
	// 幂等：已开通或已有映射跳过
	$existing = $DB->get_row_prepare(
		"SELECT id FROM MN_plugin_zjmf_host WHERE order_id=? LIMIT 1",
		[(int)$order_id]
	);
	if ($existing) {
		return ['ok' => true, 'msg' => '该订单已开通', 'host_id' => (int)$existing['id']];
	}

	// 供应商校验：缺失或停用时直接失败退款
	$supplier = zjmf_supplier_get((int)$order['supplier_id']);
	if (!$supplier || (int)$supplier['status'] !== 1) {
		$msg = '供应商不存在或已停用，无法开通';
		zjmf_order_set_status($order_id, 'failed', $msg);
		zjmf_log((int)$order['user_id'], $order['order_no'], 'purchase',
			'failed', json_encode(['msg' => $msg], JSON_UNESCAPED_UNICODE),
			(int)$order['supplier_id']);
		$amount = (int)$order['amount_cents'];
		if ($amount > 0 && function_exists('balance_add')) {
			balance_add((int)$order['user_id'], $amount, 'refund',
				$order['order_no'], '开通失败自动退款');
		}
		return ['ok' => false, 'msg' => $msg];
	}

	// 调用上游开通（代理商直通，按订单供应商路由）
	$result = ZjmfUpstream::purchase($order, $supplier);
	if (empty($result['ok'])) {
		$msg = (string)($result['msg'] ?? '上游开通失败');
		zjmf_order_set_status($order_id, 'failed', $msg);
		zjmf_log((int)$order['user_id'], $order['order_no'],
			'purchase', 'failed', json_encode(['msg' => $msg], JSON_UNESCAPED_UNICODE),
			(int)$order['supplier_id']);
		// 自动原路退回余额
		$amount = (int)$order['amount_cents'];
		if ($amount > 0 && function_exists('balance_add')) {
			balance_add((int)$order['user_id'], $amount, 'refund',
				$order['order_no'], '开通失败自动退款');
		}
		return ['ok' => false, 'msg' => $msg];
	}

	$now = $date ?: date('Y-m-d H:i:s');
	$upHostId = (int)($result['up_host_id'] ?? 0);
	$username = (string)($result['username'] ?? '');
	$password = (string)($result['password'] ?? '');
	$upOrderId = (int)($result['up_order_id'] ?? 0);

	// 保存本次开通使用的本地生成主机标识（add_to_shop 的 host 参数），
	// 供开通响应未带主机 ID 时的确定性回填匹配（S2，避免猜测匹配越权）
	$genHost = trim((string)($result['host'] ?? ''));
	if ($genHost !== '') {
		$params = json_decode((string)($order['order_params'] ?? ''), true);
		if (!is_array($params)) {
			$params = [];
		}
		if ((string)($params['up_host'] ?? '') !== $genHost) {
			$params['up_host'] = $genHost;
			$DB->query_prepare(
				"UPDATE MN_plugin_zjmf_order SET order_params=? WHERE id=?",
				[json_encode($params, JSON_UNESCAPED_UNICODE), (int)$order_id]
			);
		}
	}

	// 写主机映射（先建主机，成功后再标记订单 opened，避免中间态）：
	// 主机映射写入失败时订单保持 paid 可人工重试，不得标 opened
	$hostId = zjmf_host_create([
		'supplier_id'    => (int)$order['supplier_id'],
		'user_id'        => (int)$order['user_id'],
		'order_id'       => (int)$order_id,
		'up_host_id'     => $upHostId,
		'up_product_id'  => (int)$order['up_product_id'],
		'name'           => (string)($result['name'] ?? $order['product_name']),
		'username'       => $username,
		'password'       => $password !== '' ? zjmf_encrypt($password) : '',
		'cycle'          => $order['cycle'],
		'status'         => 'active',
		'renew_date'     => (string)($result['renew_date'] ?? ''),
	]);
	if ($hostId <= 0) {
		@error_log('[zjmfmanager_reserve] host create failed, order stays paid: order_id='
			. (int)$order_id . ' up_host_id=' . $upHostId);
		zjmf_log((int)$order['user_id'], $order['order_no'], 'purchase', 'failed',
			'上游开通成功但本地主机映射写入失败，订单保持已支付待人工处理'
			. '（up_host_id=' . $upHostId . '）', (int)$order['supplier_id']);
		return ['ok' => false, 'msg' => '上游开通成功但本地主机映射写入失败，请人工处理', 'host_id' => 0];
	}

	// 回填订单并标记 opened
	zjmf_order_fill_opened($order_id, $upOrderId, $upHostId, $username);
	zjmf_order_set_status($order_id, 'opened', '主机已开通');

	// 开通响应未带主机 ID 时，在开通流程内同步做一次确定性回填（失败不阻断）
	if ($upHostId <= 0) {
		$hostRow = zjmf_host_get($hostId);
		if ($hostRow) {
			zjmf_backfill_host_upid($hostRow);
		}
	}

	zjmf_log((int)$order['user_id'], $order['order_no'],
		'purchase', 'success',
		json_encode([
			'up_order_id' => $upOrderId,
			'up_host_id'  => $upHostId,
			'username'    => $username,
		], JSON_UNESCAPED_UNICODE),
		(int)$order['supplier_id']);

	return ['ok' => true, 'msg' => '开通成功', 'host_id' => $hostId];
}
