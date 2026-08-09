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

/* ============================================================
 *  V1.84 主题注册自定义设置（结构化字段声明）
 * ============================================================
 *
 * 主页主题可在 templates/{theme}/theme.php 中声明自定义设置字段：
 *
 *   mnbt_register_home_setting([
 *       'key'         => 'bg_color',
 *       'label'       => '背景颜色',
 *       'type'        => 'color',          // text | color | select | switch | textarea | number
 *       'default'     => '#f0f4ff',
 *       'placeholder' => '#f0f4ff',
 *       'hint'        => '可选提示文本',
 *       'options'     => [['value'=>'a','label'=>'A'], ...],  // select 专用
 *   ]);
 *
 * 字段统一持久化到 MN_config.home_theme_settings（JSON），模板端通过
 * mnbt_home_theme_setting($key, $default) 读取当前值。
 *
 * 渲染由当前 Admin 主题负责（default 用原生 HTML，tdesign 用 TDesign 组件），
 * 主题只需声明字段结构，不需要关心样式。
 */

$GLOBALS['mnbt_home_settings_fields'] = [];

/** 注册主页自定义设置字段（由主题 theme.php 调用） */
function mnbt_register_home_setting(array $config): bool
{
	$key = trim((string)($config['key'] ?? ''));
	if ($key === '' || !preg_match('/^[a-zA-Z_][a-zA-Z0-9_]{0,63}$/', $key)) {
		return false;
	}
	$type = (string)($config['type'] ?? 'text');
	if (!in_array($type, ['text', 'color', 'select', 'switch', 'textarea', 'number'], true)) {
		$type = 'text';
	}
	if (isset($GLOBALS['mnbt_home_settings_fields'][$key])) {
		return false; // 重复 key
	}
	$GLOBALS['mnbt_home_settings_fields'][$key] = [
		'key'         => $key,
		'label'       => (string)($config['label'] ?? $key),
		'type'        => $type,
		'default'     => $config['default'] ?? ($type === 'switch' ? false : ''),
		'placeholder' => (string)($config['placeholder'] ?? ''),
		'hint'        => (string)($config['hint'] ?? ''),
		'options'     => ($type === 'select' && isset($config['options']) && is_array($config['options'])) ? $config['options'] : [],
	];
	return true;
}

/** 读取当前主页主题所有已保存的自定义设置值 */
function mnbt_home_theme_settings_all(): array
{
	global $conf;
	if (is_array($conf) && !empty($conf['home_theme_settings'])) {
		$v = $conf['home_theme_settings'];
		if (is_string($v) && $v !== '') {
			$decoded = json_decode($v, true);
			if (is_array($decoded)) return $decoded;
		}
	}
	return [];
}

/** 读取单个主题自定义设置值（供模板使用） */
function mnbt_home_theme_setting(string $key, $default = null)
{
	$all = mnbt_home_theme_settings_all();
	return $all[$key] ?? $default;
}

/** 获取已注册字段列表（确保 theme.php 已加载） */
function mnbt_home_get_settings_fields(): array
{
	mnbt_home_ensure_theme_loaded();
	return $GLOBALS['mnbt_home_settings_fields'] ?? [];
}

/** 合并字段定义 + 已存值 → 渲染用数据 */
function mnbt_home_settings_view_data(): array
{
	$fields = mnbt_home_get_settings_fields();
	$values = mnbt_home_theme_settings_all();
	$result = [];
	foreach ($fields as $f) {
		$key = $f['key'];
		$item = $f;
		$item['value'] = $values[$key] ?? $f['default'];
		$result[] = $item;
	}
	return $result;
}

/** 加载当前主页主题的 theme.php（若存在），确保字段已注册 */
function mnbt_home_ensure_theme_loaded(): void
{
	static $done = false;
	if ($done) return;
	$done = true;
	if (function_exists('mnbt_theme_ensure_loaded')) {
		mnbt_theme_ensure_loaded('home');
	}
}

/** 保存主题自定义设置（从 POST 收集注册字段的值，合并 JSON 写入 MN_config） */
function mnbt_home_settings_save(): void
{
	mnbt_home_ensure_theme_loaded();
	$fields = $GLOBALS['mnbt_home_settings_fields'] ?? [];
	if (empty($fields)) return;
	global $DB, $siteid;
	$existing = mnbt_home_theme_settings_all();
	foreach ($fields as $f) {
		$k = $f['key'];
		if ($f['type'] === 'switch') {
			$existing[$k] = (isset($_POST['home_ts_' . $k]) && $_POST['home_ts_' . $k] === 'true');
		} else {
			$existing[$k] = trim((string)($_POST['home_ts_' . $k] ?? ''));
		}
	}
	$json = json_encode($existing, JSON_UNESCAPED_UNICODE);
	if ($json === false) $json = '{}';
	$sid = isset($siteid) ? $siteid : 1;
	@$DB->query_prepare("UPDATE `MN_config` SET `home_theme_settings` = ? WHERE `id` = ?", [$json, $sid]);
}

/* ============================================================
 *  默认 Admin 主题渲染器
 * ============================================================ */

/** 在 default 主题后台渲染主页自定义设置字段 HTML */
function mnbt_home_render_settings_fields_default(): string
{
	$items = mnbt_home_settings_view_data();
	if (empty($items)) return '';
	$html = '';
	foreach ($items as $f) {
		$label = htmlspecialchars($f['label']);
		$key   = 'home_ts_' . $f['key'];
		$hint  = $f['hint'] !== '' ? '<small>' . htmlspecialchars($f['hint']) . '</small>' : '';
		$val   = htmlspecialchars((string)$f['value']);
		$ph    = htmlspecialchars($f['placeholder']);
		switch ($f['type']) {
			case 'color':
				$html .= '<div class="mn-set-field"><label>' . $label . '</label>'
					. '<div style="display:flex;align-items:center;gap:10px;">'
					. '<input type="color" value="' . $val . '" style="width:46px;height:34px;padding:2px;border:1px solid #ced4da;border-radius:4px;background:#fff;cursor:pointer;"'
					. ' oninput="document.getElementById(\'hts_' . $f['key'] . '_hex\').value=this.value">'
					. '<input type="text" class="form-control" id="hts_' . $f['key'] . '_hex" name="' . $key . '" value="' . $val . '" style="max-width:140px;" placeholder="' . $ph . '"/>'
					. '</div>' . $hint . '</div>';
				break;
			case 'switch':
				$checked = $f['value'] ? 'checked' : '';
				$html .= '<div class="mn-set-field"><div class="mn-set-switch">'
					. '<div class="mn-set-switch-txt"><strong>' . $label . '</strong>'
					. ($f['hint'] !== '' ? '<span>' . htmlspecialchars($f['hint']) . '</span>' : '')
					. '</div>'
					. '<div class="custom-control custom-switch">'
					. '<input type="checkbox" class="custom-control-input" id="hts_' . $f['key'] . '" ' . $checked . '>'
					. '<label class="custom-control-label" for="hts_' . $f['key'] . '"></label>'
					. '<input type="hidden" name="' . $key . '" value="false" data-switch="hts_' . $f['key'] . '">'
					. '</div></div></div>';
				break;
			case 'select':
				$html .= '<div class="mn-set-field"><label>' . $label . '</label>'
					. '<select class="form-control" name="' . $key . '">';
				if (!empty($f['options'])) {
					foreach ($f['options'] as $opt) {
						$ov = htmlspecialchars((string)($opt['value'] ?? ''));
						$ol = htmlspecialchars((string)($opt['label'] ?? $ov));
						$sel = ($ov === (string)$f['value']) ? ' selected' : '';
						$html .= '<option value="' . $ov . '"' . $sel . '>' . $ol . '</option>';
					}
				}
				$html .= '</select>' . $hint . '</div>';
				break;
			case 'textarea':
				$html .= '<div class="mn-set-field"><label>' . $label . '</label>'
					. '<textarea class="form-control" name="' . $key . '" rows="4" placeholder="' . $ph . '">' . $val . '</textarea>'
					. $hint . '</div>';
				break;
			case 'number':
				$html .= '<div class="mn-set-field"><label>' . $label . '</label>'
					. '<input type="number" class="form-control" name="' . $key . '" value="' . $val . '" placeholder="' . $ph . '"/>'
					. $hint . '</div>';
				break;
			default: // text
				$html .= '<div class="mn-set-field"><label>' . $label . '</label>'
					. '<input type="text" class="form-control" name="' . $key . '" value="' . $val . '" placeholder="' . $ph . '"/>'
					. $hint . '</div>';
				break;
		}
	}
	// switch 值同步脚本
	$html .= '<script>(function(){'
		. 'document.querySelectorAll(\'input[data-switch]\').forEach(function(el){'
		. 'var cbId=el.getAttribute(\'data-switch\');'
		. 'var cb=document.getElementById(cbId);'
		. 'if(!cb)return;'
		. 'el.value=cb.checked?"true":"false";'
		. 'cb.addEventListener("change",function(){el.value=cb.checked?"true":"false"})'
		. '})})()</script>';
	return $html;
}
