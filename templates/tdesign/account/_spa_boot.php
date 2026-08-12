<?php
/**
 * TDesign 用户中心（user_info 插件）SPA 公共启动片段
 *
 * 由 app_plugins/user_info 的 user_info_render() 在检测到当前用户主题为
 * tdesign 时 include。入口页面（index/login/register/profile/password）
 * 设置 $td_entry / $td_hash 后 include 本文件。
 */
if (!defined('IN_CRONLITE')) {
	exit('Access Denied');
}

if (!function_exists('user_info_auth_current')) {
	echo 'user_info 插件未加载';
	exit;
}

$td_dist = __DIR__ . '/dist';
$td_js   = $td_dist . '/assets/index.js';
$td_css  = $td_dist . '/assets/index.css';
$td_ver  = is_file($td_js) ? (string)@filemtime($td_js) : (string)time();

$td_user = user_info_auth_current();

$td_base = function_exists('mnbt_home_base') ? mnbt_home_base() : '';
$td_theme = function_exists('mnbt_theme_name') ? mnbt_theme_name('user') : 'tdesign';
$td_dist_url = $td_base . '/templates/' . $td_theme . '/account/dist/';

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
	// 路由 API 入口（user_info 插件通过 P2 通用路由暴露 /account/api/*）
	'routeBase'   => $td_base . '/index.php?_r=',
	'realnameOcrBase' => $td_base . '/app_plugins/realname/assets/ocr/',
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
	'version'     => '0.3.0',
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
<?php if (is_file($td_css)): ?>
<link rel="stylesheet" href="<?= htmlspecialchars($td_dist_url . 'assets/index.css', ENT_QUOTES, 'UTF-8') ?>?v=<?= $td_ver ?>" />
<?php endif; ?>
<style>
  html, body, #app { margin: 0; padding: 0; height: 100%; background: #f2f3f5; }
  .td-boot-missing { max-width: 540px; margin: 12vh auto; padding: 32px; border-radius: 12px; background: #fff; border: 1px solid #e7e7e7; font-family: system-ui, -apple-system, "Segoe UI", "PingFang SC", "Microsoft YaHei", sans-serif; color: #1a2e28; }
  .td-boot-missing h2 { margin: 0 0 12px; font-size: 18px; color: #d54941; }
  .td-boot-missing code { background: #f3f3f3; padding: 2px 8px; border-radius: 4px; font-size: 13px; }
  .td-boot-missing p { margin: 10px 0; line-height: 1.7; font-size: 14px; }
</style>
</head>
<body>
<div id="app">
<?php if (!is_file($td_js)): ?>
  <div class="td-boot-missing">
    <h2>用户中心尚未构建</h2>
    <p>请在服务器或本机执行:</p>
    <p><code>cd templates/tdesign/spa &amp;&amp; npm install &amp;&amp; npm run build:account</code></p>
    <p>构建产物应位于 <code>templates/tdesign/account/dist/</code></p>
  </div>
<?php endif; ?>
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
<?php if (is_file($td_js)): ?>
<script type="module" src="<?= htmlspecialchars($td_dist_url . 'assets/index.js', ENT_QUOTES, 'UTF-8') ?>?v=<?= $td_ver ?>"></script>
<?php endif; ?>
</body>
</html>
