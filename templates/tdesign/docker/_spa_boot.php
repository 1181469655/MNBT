<?php
/**
 * TDesign Docker 控制台 SPA 公共启动片段
 * 注入 window.__TD_BOOT__ 并挂载构建产物
 *
 * 由 tdesign/docker/ 下的入口视图（login/console/appstore/...）include
 * 依赖：IN_CRONLITE 已定义、docker.member.php 已加载
 */
if (!defined('IN_CRONLITE')) {
	exit('Access Denied');
}

$td_dist = __DIR__ . '/dist';
$td_js   = $td_dist . '/assets/index.js';
$td_css  = $td_dist . '/assets/index.css';
$td_ver  = is_file($td_js) ? (string)@filemtime($td_js) : (string)time();

// 当前 Docker 用户（login 入口为 null）
$dkUser = isset($me) && is_array($me) ? $me : (function_exists('docker_auth_current') ? docker_auth_current() : null);
$dkPlan = null;
if ($dkUser) {
	$dkPlan = isset($plan) && is_array($plan) ? $plan : (function_exists('docker_user_plan') ? docker_user_plan($dkUser) : null);
}

$boot = [
	'siteName'  => $conf['name'] ?? 'MNBT',
	'footer'    => $conf['hxp'] ?? '',
	'ajaxBase'  => './ajax.php',
	'theme'     => 'tdesign',
	'version'   => '0.3.0',
	'entry'     => $td_entry ?? 'console',
	'hash'      => $td_hash ?? '',
	'captchaBase' => mnbt_theme_url('dist/captcha-images/', 'docker'),
	'dockerUser' => $dkUser ? array_merge($dkUser, [
		'password_hash' => null,
		'plan_name'     => $dkPlan['name'] ?? '',
		'cpu_max'       => $dkPlan['cpu_max'] ?? 1,
		'mem_max'       => $dkPlan['mem_max'] ?? 512,
		'disk_max'      => $dkPlan['disk_max'] ?? '0',
		'proxy_max'     => $dkPlan['proxy_max'] ?? '0',
	]) : null,
];

// 视图可在 include 前设置 $td_inject(数组),把页面级数据注入 boot
if (isset($td_inject) && is_array($td_inject)) {
	foreach ($td_inject as $k => $v) {
		$boot[$k] = $v;
	}
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
<title><?= htmlspecialchars(($title ?? 'Docker 控制台') . ' · ' . ($conf['name'] ?? 'MNBT'), ENT_QUOTES, 'UTF-8') ?></title>
<link rel="icon" href="<?= htmlspecialchars(mnbt_asset_url('images/logo-ico.png'), ENT_QUOTES, 'UTF-8') ?>" type="image/ico" />
<link rel="stylesheet" href="<?= htmlspecialchars(mnbt_asset_url('css/materialdesignicons.min.css'), ENT_QUOTES, 'UTF-8') ?>" />
<?php if (is_file($td_css)): ?>
<link rel="stylesheet" href="<?= htmlspecialchars(mnbt_theme_url('dist/assets/index.css', 'docker'), ENT_QUOTES, 'UTF-8') ?>?v=<?= $td_ver ?>" />
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
    <h2>TDesign Docker 控制台尚未构建</h2>
    <p>请在服务器或本机执行:</p>
    <p><code>cd templates/tdesign/spa &amp;&amp; npm install &amp;&amp; npm run build:docker</code></p>
    <p>构建产物应位于 <code>templates/tdesign/docker/dist/</code></p>
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
<script type="module" src="<?= htmlspecialchars(mnbt_theme_url('dist/assets/index.js', 'docker'), ENT_QUOTES, 'UTF-8') ?>?v=<?= $td_ver ?>"></script>
<?php endif; ?>
</body>
</html>
