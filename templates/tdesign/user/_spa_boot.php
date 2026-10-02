<?php
/**
 * TDesign 用户端 SPA 公共启动片段
 * 注入 window.__TD_BOOT__ 并经 vue3-sfc-loader 免构建挂载（V1.87，见 ../assets/td-boot.js）
 */
if (!defined('IN_CRONLITE')) {
	exit('Access Denied');
}

// V1.87：SPA 由 vue3-sfc-loader 直接加载 spa/src 源码，不再使用 dist 构建产物
$td_boot_file = __DIR__ . '/../assets/td-boot.js';
$td_ver = is_file($td_boot_file) ? (string)@filemtime($td_boot_file) : (string)time();

$boot = [
	'siteName'     => $conf['name'] ?? 'MNBT',
	'footer'       => $conf['hxp'] ?? '',
	'loggedIn'     => isset($islogins) && (int)$islogins === 1,
	'needCaptcha'  => isset($conf['yzm']) && $conf['yzm'] === 'true',
	'ajaxBase'     => './ajax.php',
	'codeUrl'      => './code.php',
	'logo'         => mnbt_asset_url('upload_logo/logo.login.png'),
	'logoHead'     => mnbt_asset_url('upload_logo/logo.head.png'),
	'logoIndex'    => mnbt_asset_url('upload_logo/logo.index.png'),
	'auther'       => $conf['auther'] ?? '',
	'theme'        => 'tdesign',
	'version'      => '0.4.0',
	'scope'        => 'user',
	'themeBase'    => '../templates/tdesign/',
	'vendorBase'   => mnbt_asset_url('vendor/'),
	'assetBase'    => mnbt_asset_url(''),
	'entry'        => $td_entry ?? 'dashboard',
	'hash'         => $td_hash ?? '',
	'pluginMenuHtml' => '',
	'conf'         => $conf ?? [],
	'user'         => $user ?? '',
	'zjid'         => $zjid ?? 0,
	'ssbt'         => $ssbt ?? '',
	'yhc'          => $yhc ?? [],
	'serverHost'   => $_SERVER['HTTP_HOST'] ?? '',
	'serverProto'  => (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($_SERVER['SERVER_PORT'] ?? '') == '443' ? 'https://' : 'http://',
	'pluginSettingsTabs' => function_exists('mnbt_plugin_settings_tabs') ? array_map(function ($tab) {
		if (empty($tab['icon']) && !empty($tab['plugin'])) {
			$meta = $GLOBALS['mnbt_plugin_meta'][$tab['plugin']] ?? [];
			$tab['icon'] = !empty($meta['icon']) ? $meta['icon'] : 'mdi-cog-outline';
		}
		return $tab;
	}, mnbt_plugin_settings_tabs()) : [],
];

// 视图可在 include 前设置 $td_inject(数组),把页面级数据注入 boot
if (isset($td_inject) && is_array($td_inject)) {
	foreach ($td_inject as $k => $v) {
		$boot[$k] = $v;
	}
}

// 插件菜单 HTML（独立"扩展与工具"分组,所有插件项通过 SPA 路由在 layout 内打开)
$boot['pluginMenuHtml'] = _tdboot_render_plugin_menu_html(function_exists('mnbt_plugin_menus') ? mnbt_plugin_menus('user') : []);

/**
 * 渲染插件菜单区 HTML
 * 输出形如:
 *   <li class="td-side-group"><div class="td-side-group-label">...扩展与工具...</div></li>
 *   <li class="td-side-submenu">分组项...<ul>叶子项(带 data-td-route)</ul></li>
 *   <li class="td-side-leaf">独立叶子项(带 data-td-route)</li>
 */
function _tdboot_render_plugin_menu_html($items)
{
	if (empty($items)) {
		return '';
	}

	$groups = [];
	$leafs = [];
	$html = '';
	foreach ($items as $it) {
		if (!empty($it['children'])) {
			$groups[] = $it;
		} else {
			$leafs[] = $it;
		}
	}

	foreach ($groups as $g) {
		$html .= _tdboot_render_plugin_group($g);
	}

	foreach ($leafs as $lf) {
		// 独立叶子项直接作为顶层菜单项,与其它业务项视觉一致
		$html .= _tdboot_render_plugin_leaf_item($lf);
	}

	return $html;
}

function _tdboot_render_plugin_group($it)
{
	$title = htmlspecialchars($it['title'] ?? '', ENT_QUOTES, 'UTF-8');
	$icon  = !empty($it['icon']) ? htmlspecialchars($it['icon'], ENT_QUOTES, 'UTF-8') : 'mdi-puzzle';

	$childrenHtml = '';
	foreach ($it['children'] as $child) {
		$childrenHtml .= _tdboot_render_plugin_menu_child($child);
	}

	return '<li class="td-side-submenu">'
		. '<a href="javascript:;" onclick="this.parentElement.classList.toggle(\'open\')">'
		. '<i class="mdi ' . $icon . '"></i><span>' . $title . '</span>'
		. '<i class="td-arrow mdi mdi-chevron-down"></i></a>'
		. '<ul class="td-side-subnav">' . $childrenHtml . '</ul></li>';
}

function _tdboot_render_plugin_menu_child($it)
{
	$title = htmlspecialchars($it['title'] ?? '', ENT_QUOTES, 'UTF-8');
	$icon  = !empty($it['icon']) ? htmlspecialchars($it['icon'], ENT_QUOTES, 'UTF-8') : 'mdi-puzzle';

	if (!empty($it['children'])) {
		$childrenHtml = '';
		foreach ($it['children'] as $child) {
			$childrenHtml .= _tdboot_render_plugin_menu_child($child);
		}
		return '<li class="td-side-submenu">'
			. '<a href="javascript:;" onclick="this.parentElement.classList.toggle(\'open\')">'
			. '<i class="mdi ' . $icon . '"></i><span>' . $title . '</span>'
			. '<i class="td-arrow mdi mdi-chevron-down"></i></a>'
			. '<ul class="td-side-subnav">' . $childrenHtml . '</ul></li>';
	}

	return _tdboot_render_plugin_leaf_item($it);
}

/**
 * 渲染插件叶子菜单项
 * 为 plugin.php 链接生成 data-td-route 属性,由 SPA 拦截并在 layout 内通过 iframe 加载
 */
function _tdboot_render_plugin_leaf_item($it)
{
	$title = htmlspecialchars($it['title'] ?? '', ENT_QUOTES, 'UTF-8');
	$icon  = !empty($it['icon']) ? htmlspecialchars($it['icon'], ENT_QUOTES, 'UTF-8') : 'mdi-puzzle';
	$url   = $it['url'] ?? 'javascript:void(0)';
	$urlH  = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');

	$routeAttr = '';
	if (strpos($url, 'plugin.php') !== false) {
		// 解析 query,生成 SPA 路由路径
		$query = parse_url($url, PHP_URL_QUERY) ?? '';
		$routeAttr = ' data-td-route="/plugin?' . $query . '"';
	}

	return '<li class="td-side-leaf">'
		. '<a href="' . $urlH . '"' . $routeAttr . '>'
		. '<i class="mdi ' . $icon . '"></i><span>' . $title . '</span></a></li>';
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
<title><?= htmlspecialchars(($title ?? '控制面板') . ' · ' . ($conf['name'] ?? 'MNBT'), ENT_QUOTES, 'UTF-8') ?></title>
<link rel="icon" href="<?= htmlspecialchars(mnbt_asset_url('images/logo-ico.png'), ENT_QUOTES, 'UTF-8') ?>" type="image/ico" />
<link rel="stylesheet" href="<?= htmlspecialchars(mnbt_asset_url('css/materialdesignicons.min.css'), ENT_QUOTES, 'UTF-8') ?>" />
<link rel="stylesheet" href="<?= htmlspecialchars(mnbt_asset_url('vendor/tdesign/tdesign.min.css'), ENT_QUOTES, 'UTF-8') ?>" />
<style>
  html, body, #app { margin: 0; padding: 0; height: 100%; background: #f2f3f5; }
  .td-boot-msg { max-width: 540px; margin: 12vh auto; padding: 32px; border-radius: 12px; background: #fff; border: 1px solid #e7e7e7; font-family: system-ui, -apple-system, "Segoe UI", "PingFang SC", "Microsoft YaHei", sans-serif; color: #1a2e28; text-align: center; }
  .td-boot-msg h2 { margin: 0 0 12px; font-size: 18px; color: #d54941; }
  .td-boot-msg pre { text-align: left; white-space: pre-wrap; word-break: break-all; background: #f3f3f3; padding: 12px; border-radius: 6px; font-size: 12px; max-height: 320px; overflow: auto; }
  .td-boot-msg p { margin: 10px 0; line-height: 1.7; font-size: 14px; color: #4b5b5b; }
  .td-boot-spinner { width: 36px; height: 36px; margin: 8px auto 12px; border: 3px solid #dcdcdc; border-top-color: #0052d9; border-radius: 50%; animation: td-boot-spin 0.8s linear infinite; }
  @keyframes td-boot-spin { to { transform: rotate(360deg); } }
</style>
</head>
<body>
<div id="app">
  <div id="td-boot-status" class="td-boot-msg td-boot-loading">
    <div class="td-boot-spinner"></div>
    <p>正在加载控制面板…</p>
  </div>
</div>
<script>
window.__TD_BOOT__ = <?= json_encode($boot, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
<?php if (!empty($td_hash)): ?>
if (window.__TD_BOOT__.hash) {
  if (!location.hash || location.hash === '#' || location.hash === '#/') {
    location.hash = window.__TD_BOOT__.hash;
  }
}
<?php endif; ?>
</script>
<script src="<?= htmlspecialchars(mnbt_asset_url('vendor/vue/vue.global.prod.js'), ENT_QUOTES, 'UTF-8') ?>"></script>
<script src="<?= htmlspecialchars(mnbt_asset_url('vendor/vue-router/vue-router.global.prod.js'), ENT_QUOTES, 'UTF-8') ?>"></script>
<script src="<?= htmlspecialchars(mnbt_asset_url('vendor/axios/axios.min.js'), ENT_QUOTES, 'UTF-8') ?>"></script>
<script src="<?= htmlspecialchars(mnbt_asset_url('vendor/tdesign/tdesign.min.js'), ENT_QUOTES, 'UTF-8') ?>"></script>
<script src="<?= htmlspecialchars(mnbt_asset_url('vendor/echarts/echarts.min.js'), ENT_QUOTES, 'UTF-8') ?>"></script>
<script src="<?= htmlspecialchars(mnbt_asset_url('vendor/vue3-sfc-loader/vue3-sfc-loader.js'), ENT_QUOTES, 'UTF-8') ?>"></script>
<script src="<?= htmlspecialchars(mnbt_theme_url('assets/td-boot.js', 'user'), ENT_QUOTES, 'UTF-8') ?>?v=<?= $td_ver ?>"></script>
</body>
</html>
