<?php
/**
 * TDesign Docker 控制台 SPA 公共启动片段
 * 注入 window.__TD_BOOT__ 并经 vue3-sfc-loader 免构建挂载（V1.87，见 ../assets/td-boot.js）
 *
 * 由 tdesign/docker/ 下的入口视图（login/console/appstore/...）include
 * 依赖：IN_CRONLITE 已定义、docker.member.php 已加载
 */
if (!defined('IN_CRONLITE')) {
	exit('Access Denied');
}

$td_boot_file = __DIR__ . '/../assets/td-boot.js';
$td_ver = is_file($td_boot_file) ? (string)@filemtime($td_boot_file) : (string)time();

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
	'version'      => '0.4.0',
	'scope'     => 'docker',
	'themeBase' => '../templates/tdesign/',
	'vendorBase'=> mnbt_asset_url('vendor/'),
	'entry'     => $td_entry ?? 'console',
	'hash'      => $td_hash ?? '',
	'captchaUrl' => '../captcha.php',
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
    <p>正在加载 Docker 控制台…</p>
  </div>
</div>
<script>
<?php $boot = mnbt_apply_filters('spa.boot', $boot); // 插件可注入 boot.captcha（provider/adapter）等前端配置 ?>
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
<script src="<?= htmlspecialchars(mnbt_asset_url('vendor/crypto-js/crypto-js.min.js'), ENT_QUOTES, 'UTF-8') ?>"></script>
<script src="<?= htmlspecialchars(mnbt_asset_url('vendor/tdesign/tdesign.min.js'), ENT_QUOTES, 'UTF-8') ?>"></script>
<script src="<?= htmlspecialchars(mnbt_asset_url('vendor/vue3-sfc-loader/vue3-sfc-loader.js'), ENT_QUOTES, 'UTF-8') ?>"></script>
<script src="../templates/tdesign/assets/td-boot.js?v=<?= $td_ver ?>"></script>
</body>
</html>
