<?php
/**
 * user_info 插件 —— 用户中心（account）TDesign SPA 公共启动片段
 *
 * 用户主题为 tdesign 时，由 lib/auth.php 的 user_info_render() include 本目录下的
 * 入口页（dashboard/login/register/profile/password 设置 $td_entry / $td_hash 后
 * include 本文件）。SPA 源码在本插件 assets/spa/；共享运行时（td-boot.js）与
 * 共享静态资源（spa/src/shared/）仍由 tdesign 主题提供，经 srcAliases 别名引用。
 */
if (!defined('IN_CRONLITE')) {
	exit('Access Denied');
}

if (!function_exists('user_info_auth_current')) {
	echo 'user_info 插件未加载';
	exit;
}

$td_base = function_exists('mnbt_home_base') ? mnbt_home_base() : '';
$td_theme = function_exists('mnbt_theme_name') ? mnbt_theme_name('user') : 'tdesign';
$td_theme_base = $td_base . '/templates/' . $td_theme . '/';

$td_boot_file = MNBT_THEME_ROOT . $td_theme . '/assets/td-boot.js';
$td_ver = is_file($td_boot_file) ? (string)@filemtime($td_boot_file) : (string)time();

$td_user = user_info_auth_current();

$boot = [
	'siteName'    => $conf['name'] ?? 'MNBT',
	'footer'      => $conf['hxp'] ?? '',
	'loggedIn'    => $td_user ? true : false,
	'accountUser' => $td_user ? [
		'id'         => (int)$td_user['id'],
		'username'   => (string)$td_user['username'],
		'email'      => (string)($td_user['email'] ?? ''),
		'qq'         => (string)($td_user['qq'] ?? ''),
		'status'     => (int)($td_user['status'] ?? 1),
		'created_at' => (string)($td_user['created_at'] ?? ''),
	] : null,
	// 路由 API 入口（user_info 等插件通过 P2 通用路由暴露 /account/api/*）
	'routeBase'   => $td_base . '/index.php?_r=',
	// SPA 源码根：插件自带（account 作用域），@/ → 本插件 assets/spa/
	'srcBase'     => $td_base . '/app_plugins/user_info/assets/spa/',
	// @/shared/ 仍指向主题共享源码（登录背景图、共享样式等）
	'srcAliases'  => ['@/shared/' => $td_theme_base . 'spa/src/shared/'],
	'realnameOcrBase' => 'https://cdn.jsdelivr.net/npm/tesseract.js@v5.1.1/dist/',
	// 插件能力标志（account SPA 依据此决定是否展示余额/商城功能）
	'plugins'     => [
		'balance'      => function_exists('mnbt_plugin_enabled') ? mnbt_plugin_enabled('balance') : false,
		'hosting_shop' => function_exists('mnbt_plugin_enabled') ? mnbt_plugin_enabled('hosting_shop') : false,
		'docker_shop'  => function_exists('mnbt_plugin_enabled') ? mnbt_plugin_enabled('docker_shop') : false,
		'realname'     => function_exists('mnbt_plugin_enabled') ? mnbt_plugin_enabled('realname') : false,
		'zjmf'         => function_exists('mnbt_plugin_enabled') ? mnbt_plugin_enabled('zjmfmanager_reserve') : false,
	],
	// 主机管理面板入口（核心 user scope）
	'panelUrl'    => $td_base . '/user/',
	// Docker 控制台入口（核心 docker scope）
	'dockerUrl'   => $td_base . '/docker/',
	// 官网首页入口
	'homeUrl'     => $td_base . '/',
	'theme'       => $td_theme,
	'version'      => '0.4.0',
	'scope'       => 'account',
	'themeBase'   => $td_theme_base,
	'vendorBase'  => $td_base . '/imsetes/vendor/',
	'entry'       => $td_entry ?? 'dashboard',
	'hash'        => $td_hash ?? '',
];
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
<title><?= htmlspecialchars(($title ?? '用户中心') . ' · ' . ($conf['name'] ?? 'MNBT'), ENT_QUOTES, 'UTF-8') ?></title>
<link rel="icon" href="<?= htmlspecialchars(mnbt_asset_url('images/logo-ico.png'), ENT_QUOTES, 'UTF-8') ?>" type="image/ico" />
<link rel="stylesheet" href="<?= htmlspecialchars(mnbt_asset_url('css/materialdesignicons.min.css'), ENT_QUOTES, 'UTF-8') ?>" />
<link rel="stylesheet" href="<?= htmlspecialchars($td_base . '/imsetes/vendor/tdesign/tdesign.min.css', ENT_QUOTES, 'UTF-8') ?>" />
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
    <p>正在加载用户中心…</p>
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
<script src="<?= htmlspecialchars($td_base . '/imsetes/vendor/vue/vue.global.prod.js', ENT_QUOTES, 'UTF-8') ?>"></script>
<script src="<?= htmlspecialchars($td_base . '/imsetes/vendor/vue-router/vue-router.global.prod.js', ENT_QUOTES, 'UTF-8') ?>"></script>
<script src="<?= htmlspecialchars($td_base . '/imsetes/vendor/axios/axios.min.js', ENT_QUOTES, 'UTF-8') ?>"></script>
<script src="<?= htmlspecialchars($td_base . '/imsetes/vendor/tdesign/tdesign.min.js', ENT_QUOTES, 'UTF-8') ?>"></script>
<script src="<?= htmlspecialchars($td_base . '/imsetes/vendor/vue3-sfc-loader/vue3-sfc-loader.js', ENT_QUOTES, 'UTF-8') ?>"></script>
<script src="<?= htmlspecialchars($td_theme_base . 'assets/td-boot.js', ENT_QUOTES, 'UTF-8') ?>?v=<?= $td_ver ?>"></script>
</body>
</html>
