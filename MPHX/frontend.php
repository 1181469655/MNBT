<?php
/**
 * MNBT 独立主页系统（V1.84）
 *
 * 主页（站点根路径 /）作为核心一等公民，不依赖插件引擎：
 *   - index.php 在插件首页接管（mnbt_plugin_dispatch_home）之后调用 mnbt_home_dispatch()
 *   - 仅当后台开启 home_enable 且无插件接管时，渲染内置默认主页
 *   - 默认主页模板：templates/{当前用户主题}/home/index.php，缺页回退 templates/default/home/index.php
 *   - 插件仍可通过 mnbt_register_home 优先覆盖主页；可通过 home.blocks 过滤器注入扩展区块
 *
 * 关联文档：docs/HOME_PRD.md
 */
if (!defined('IN_CRONLITE')) {
	exit('Access Denied');
}

/** 站点 base path（子目录部署前缀） */
function mnbt_home_base(): string {
	if (function_exists('mnbt_plugin_request_info')) {
		$info = mnbt_plugin_request_info();
		return $info['base'] ?? '';
	}
	$scriptName = isset($_SERVER['SCRIPT_NAME']) ? str_replace('\\', '/', $_SERVER['SCRIPT_NAME']) : '';
	$base = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');
	return ($base === '.' || $base === '/') ? '' : $base;
}

/** 读取主页配置（MN_config home_* 字段，空值回退默认） */
function mnbt_home_option($key, $default = null)
{
	global $conf;
	if (is_array($conf) && isset($conf[$key]) && $conf[$key] !== '') {
		return $conf[$key];
	}
	return $default;
}

/** 是否启用内置主页 */
function mnbt_home_enabled(): bool
{
	return mnbt_home_option('home_enable', 'true') === 'true';
}

/** 生成带 base 前缀的路由 URL（index.php?_r=/path） */
function mnbt_home_url(string $path = ''): string
{
	$base = mnbt_home_base();
	$p = ltrim($path, '/');
	$qpos = strpos($p, '?');
	if ($qpos !== false) {
		$route = substr($p, 0, $qpos);
		$query = substr($p, $qpos + 1);
		return $base . '/index.php?_r=/' . $route . '&' . $query;
	}
	return $base . '/index.php?_r=/' . $p;
}

/** 生成带 base 前缀的核心物理文件 URL（如 user/login.php） */
function mnbt_home_core_url(string $path = ''): string
{
	return mnbt_home_base() . '/' . ltrim($path, '/');
}

/** 资源 URL：绝对地址（http(s):// 或 / 开头）原样返回，相对路径加 base 前缀 */
function mnbt_home_asset($path): string
{
	$path = (string)$path;
	if ($path === '') {
		return '';
	}
	if (preg_match('#^https?://#i', $path) || strpos($path, '/') === 0) {
		return $path;
	}
	return mnbt_home_base() . '/' . ltrim($path, '/');
}

/** 解析主页模板路径：当前主页主题（templates/{theme}/home/）→ default 主题回退 */
function mnbt_home_resolve(): ?string
{
	$theme = function_exists('mnbt_theme_name') ? mnbt_theme_name('home') : 'default';
	$root = defined('MNBT_THEME_ROOT') ? MNBT_THEME_ROOT : (ROOT . 'templates/');
	$candidates = [$root . $theme . '/home/index.php'];
	if ($theme !== 'default') {
		$candidates[] = $root . 'default/home/index.php';
	}
	foreach ($candidates as $p) {
		if (is_file($p)) {
			return $p;
		}
	}
	return null;
}

/** 组装主页数据（模板注入变量） */
function mnbt_home_data(): array
{
	global $DB, $conf;

	$siteTitle = mnbt_home_option('home_title', '') ?: ($conf['name'] ?? 'MNBT');

	$data = [
		'site_title'    => $siteTitle,
		'site_logo'     => mnbt_home_asset(mnbt_home_option('home_logo', '') ?: 'imsetes/upload_logo/logo.index.png'),
		'site_primary'  => mnbt_home_option('home_primary', '#4f46e5'),
		'site_hero'     => mnbt_home_option('home_hero', '高性能虚拟主机，即买即用'),
		'site_footer'   => mnbt_home_option('home_footer', '') ?: ($conf['hxp'] ?? ''),
		'favicon'       => mnbt_home_asset(mnbt_home_option('home_favicon', '') ?: 'imsetes/images/logo-ico.png'),
		// 公告与区块开关
		'notice'        => mnbt_home_option('gg', ''),
		'show_notice'   => mnbt_home_option('home_show_notice', 'true') === 'true',
		'show_plans'    => mnbt_home_option('home_show_plans', 'true') === 'true',
		// 登录态与能力探测（不强依赖插件）
		'logged_in'     => isset($_COOKIE['user_token']) && $_COOKIE['user_token'] !== '',
		'has_shop'      => function_exists('mnbt_plugin_enabled') && mnbt_plugin_enabled('hosting_shop'),
		'has_user'      => function_exists('mnbt_plugin_enabled') && mnbt_plugin_enabled('user_info'),
		// 业务数据
		'plans'         => [],
		'blocks'        => [],
		// URL 生成回调
		'url'           => function (string $path = '') { return mnbt_home_url($path); },
		'coreUrl'       => function (string $path = '') { return mnbt_home_core_url($path); },
	];

	// 套餐区（hosting_shop 启用且有有效套餐时展示，查询失败静默降级为空）
	if ($data['has_shop'] && $data['show_plans'] && isset($DB)) {
		$rows = @$DB->get_all_prepare("SELECT * FROM MN_plugin_hosting_plan WHERE status='active' ORDER BY sort ASC, id ASC") ?: [];
		foreach ($rows as $p) {
			$minPrice = 0;
			if ((int)($p['price_month_cents'] ?? 0) > 0) {
				$minPrice = (int)$p['price_month_cents'] / 100;
			}
			if ((int)($p['price_year_cents'] ?? 0) > 0) {
				$yearPrice = (int)$p['price_year_cents'] / 100;
				if ($minPrice == 0 || $yearPrice / 12 < $minPrice) {
					$minPrice = $yearPrice / 12;
				}
			}
			$feats = [];
			if (!empty($p['spec_web']))    $feats[] = '网页空间 ' . $p['spec_web'] . ' MB';
			if (!empty($p['spec_sql']))    $feats[] = '数据库 ' . $p['spec_sql'] . ' MB';
			if (!empty($p['spec_flow']))   $feats[] = '月流量 ' . $p['spec_flow'] . ' GB';
			if (!empty($p['spec_domain'])) $feats[] = '可绑定 ' . $p['spec_domain'] . ' 个域名';
			$data['plans'][] = [
				'id'    => (int)$p['id'],
				'name'  => (string)$p['name'],
				'desc'  => (string)($p['description'] ?? ''),
				'price' => $minPrice > 0 ? '¥' . number_format($minPrice, 2) . ' 起/月' : '免费',
				'feats' => $feats,
			];
		}
	}

	// 插件扩展区块（home.blocks 过滤器，按 order 升序；仅取结构化字段）
	if (function_exists('mnbt_apply_filters')) {
		$blocks = mnbt_apply_filters('home.blocks', []);
		if (is_array($blocks)) {
			$clean = [];
			foreach ($blocks as $b) {
				if (!is_array($b) || !isset($b['html'])) {
					continue;
				}
				$clean[] = [
					'id'    => (string)($b['id'] ?? ''),
					'title' => (string)($b['title'] ?? ''),
					'html'  => (string)$b['html'],
					'order' => (int)($b['order'] ?? 50),
				];
			}
			usort($clean, function ($a, $b) {
				return $a['order'] - $b['order'];
			});
			$data['blocks'] = $clean;
		}
	}

	return $data;
}

/** 渲染内置主页（组装数据 → 加载主题模板 → 终止请求） */
function mnbt_home_render(): void
{
	$path = mnbt_home_resolve();
	if ($path === null) {
		http_response_code(500);
		echo 'Home template not found';
		exit;
	}
	if (!headers_sent()) {
		@header('Content-Type: text/html; charset=UTF-8');
	}
	$vars = mnbt_home_data();
	$bufferLevel = ob_get_level();
	ob_start('mnbt_csrf_inject_html');
	try {
		extract($vars, EXTR_SKIP);
		include $path;
	} finally {
		while (ob_get_level() > $bufferLevel) {
			ob_end_flush();
		}
	}
	exit;
}

/** 分发入口：index.php 在插件首页接管之后调用；仅路径为 / 且启用时生效 */
function mnbt_home_dispatch(): bool
{
	if (!function_exists('mnbt_plugin_request_info')) {
		return false;
	}
	$info = mnbt_plugin_request_info();
	if ($info['path'] !== '/') {
		return false;
	}
	if (!mnbt_home_enabled()) {
		return false;
	}
	mnbt_home_render();
	return true;
}
