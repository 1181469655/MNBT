<?php
/**
 * TDesign 管理后台 SPA 公共启动片段
 * 注入 window.__TD_BOOT__ 并经 vue3-sfc-loader 免构建挂载（V1.87，见 ../assets/td-boot.js）
 */
if (!defined('IN_CRONLITE')) {
	exit('Access Denied');
}

$td_boot_file = __DIR__ . '/../assets/td-boot.js';
$td_ver = is_file($td_boot_file) ? (string)@filemtime($td_boot_file) : (string)time();

$boot = [
	'siteName'     => $conf['name'] ?? 'MNBT',
	'footer'       => $conf['hxp'] ?? '',
	'adminUser'    => $conf['user'] ?? 'admin',
	'loggedIn'     => isset($islogin) && (int)$islogin === 1,
	'needCaptcha'  => isset($conf['yzm']) && $conf['yzm'] === 'true',
	'ajaxBase'     => './ajax.php',
	'codeUrl'      => './code.php',
	'logo'         => mnbt_asset_url('admin_logo/logo.login.png'),
	'logoHead'     => mnbt_asset_url('admin_logo/logo.head.png'),
	'logoIndex'    => mnbt_asset_url('admin_logo/logo.index.png'),
	'auther'       => $conf['auther'] ?? '',
	'theme'        => 'tdesign',
	'version'      => '0.4.0',
	'scope'        => 'admin',
	'themeBase'    => '../templates/tdesign/',
	'vendorBase'   => mnbt_asset_url('vendor/'),
	'entry'        => $td_entry ?? 'dashboard',
	'hash'         => $td_hash ?? '',
	'pluginMenuHtml' => '',
	'conf'         => $conf ?? [],
	'serverHost'   => $_SERVER['HTTP_HOST'] ?? '',
	'serverProto'  => (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($_SERVER['SERVER_PORT'] ?? '') == '443' ? 'https://' : 'http://',
	// 主题列表(给设置页主题切换使用)
	// mnbt_theme_list 返回关联数组,json_encode 后变成对象;SPA 需要数组,所以用 array_values 重建索引
	'themeList'    => function_exists('mnbt_theme_list') ? array_values(mnbt_theme_list(null)) : [],
	'curUserTheme' => function_exists('mnbt_theme_name') ? mnbt_theme_name('user') : '',
	'curAdminTheme'=> function_exists('mnbt_theme_name') ? mnbt_theme_name('admin') : '',
	'curDockerTheme'=> function_exists('mnbt_theme_name') ? mnbt_theme_name('docker') : '',
	// 支付插件信息(给支付设置页使用)
	// mnbt_get_payment_plugins 返回关联数组,json_encode 后变成对象;SPA 需要数组,并补充 plugin_id 字段
	'paymentPlugins'  => function_exists('mnbt_get_payment_plugins') ? array_values(array_map(function ($config, $pluginId) {
		$config['plugin_id'] = $pluginId;
		return $config;
	}, mnbt_get_payment_plugins(), array_keys(mnbt_get_payment_plugins()))) : [],
	'enabledPayments' => function_exists('mnbt_get_enabled_payment_methods') ? mnbt_get_enabled_payment_methods() : [],
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

// 插件菜单 HTML 直接在 boot 中构造,避免依赖服务器上可能未更新的 theme.php 渲染器
$boot['pluginMenuHtml'] = _tdboot_render_plugin_menu_html(function_exists('mnbt_plugin_menus') ? mnbt_plugin_menus('admin') : []);

/**
 * 渲染插件菜单区 HTML
 * 结构与默认主题一致:
 *   - 插件列表(独立项)
 *   - 有 children 的插件分组(独立 submenu)
 *   - 无 children 的插件菜单归入"插件管理"分组
 */
function _tdboot_render_plugin_menu_html($items)
{
	$html = '<li class="td-side-item">'
		. '<a href="javascript:;" data-td-route="/plugin">'
		. '<i class="mdi mdi-puzzle-outline"></i><span>插件列表</span></a></li>';

	if (empty($items)) {
		return $html;
	}

	$groups = [];
	$leafs = [];
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

	if (!empty($leafs)) {
		$html .= '<li class="td-side-submenu">'
			. '<a href="javascript:;" onclick="this.parentElement.classList.toggle(\'open\')">'
			. '<i class="mdi mdi-puzzle"></i><span>插件管理</span>'
			. '<i class="td-arrow mdi mdi-chevron-down"></i></a>'
			. '<ul class="td-side-subnav">' . _tdboot_render_plugin_leafs($leafs) . '</ul></li>';
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

	$url = htmlspecialchars($it['url'] ?? 'javascript:void(0)', ENT_QUOTES, 'UTF-8');
	return '<li class="td-side-leaf">'
		. '<a href="' . $url . '">'
		. '<i class="mdi ' . $icon . '"></i><span>' . $title . '</span></a></li>';
}

function _tdboot_render_plugin_leafs($leafs)
{
	$html = '';
	foreach ($leafs as $it) {
		$html .= _tdboot_render_plugin_menu_child($it);
	}
	return $html;
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
<title><?= htmlspecialchars(($title ?? '管理后台') . ' · ' . ($conf['name'] ?? 'MNBT'), ENT_QUOTES, 'UTF-8') ?></title>
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
    <p>正在加载管理后台…</p>
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
<script src="../templates/tdesign/assets/td-boot.js?v=<?= $td_ver ?>"></script>
</body>
</html>
