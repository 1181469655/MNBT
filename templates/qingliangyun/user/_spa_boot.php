<?php
/**
 * 清凉云 SPA 公共启动片段
 * 注入 window.__QL_BOOT__ 并挂载构建产物
 */
if (!defined('IN_CRONLITE')) {
	exit('Access Denied');
}

$ql_dist = __DIR__ . '/dist';
$ql_js = $ql_dist . '/assets/index.js';
$ql_css = $ql_dist . '/assets/index.css';
$ql_ver = is_file($ql_js) ? (string)@filemtime($ql_js) : (string)time();

$boot = [
	'siteName' => $conf['name'] ?? '清凉云',
	'footer' => $conf['hxp'] ?? '',
	'user' => $user ?? '',
	'loggedIn' => isset($islogins) && (int)$islogins === 1,
	'needCaptcha' => isset($conf['yzme']) && $conf['yzme'] === 'true',
	'productType' => isset($yhc['hxc']) ? (string)$yhc['hxc'] : '',
	'ajaxBase' => './ajax.php',
	'logo' => mnbt_asset_url('upload_logo/logo.login.png'),
	'auther' => $conf['auther'] ?? '',
	'theme' => 'qingliangyun',
	'version' => '0.1.0',
	'entry' => $ql_entry ?? 'dashboard',
	'hash' => $ql_hash ?? '',
];
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
<title><?= htmlspecialchars(($title ?? '控制面板') . ' · ' . ($conf['name'] ?? '清凉云'), ENT_QUOTES, 'UTF-8') ?></title>
<link rel="icon" href="<?= htmlspecialchars(mnbt_asset_url('upload_logo/logo.head.png'), ENT_QUOTES, 'UTF-8') ?>?<?= htmlspecialchars((string)($conf['auther'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" type="image/ico" />
<?php if (is_file($ql_css)): ?>
<link rel="stylesheet" href="<?= htmlspecialchars(mnbt_theme_url('dist/assets/index.css'), ENT_QUOTES, 'UTF-8') ?>?v=<?= $ql_ver ?>" />
<?php endif; ?>
<style>
  html,body,#app{margin:0;min-height:100dvh;background:#f4faf7}
  .ql-boot-missing{max-width:520px;margin:12vh auto;padding:28px;border-radius:16px;background:#fff;border:1px solid #d8ebe3;font-family:system-ui,sans-serif;color:#1a2e28}
  .ql-boot-missing code{background:#e6fcf5;padding:2px 6px;border-radius:6px}
</style>
</head>
<body>
<div id="app">
<?php if (!is_file($ql_js)): ?>
  <div class="ql-boot-missing">
    <h2>清凉云前端尚未构建</h2>
    <p>请在服务器或本机执行：</p>
    <p><code>cd templates/qingliangyun/spa && npm install && npm run build</code></p>
    <p>构建产物应位于 <code>templates/qingliangyun/user/dist/</code></p>
  </div>
<?php endif; ?>
</div>
<script>
window.__QL_BOOT__ = <?= json_encode($boot, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
<?php if (!empty($ql_hash)): ?>
if (window.__QL_BOOT__.hash) {
  if (!location.hash || location.hash === '#' || location.hash === '#/') {
    location.hash = window.__QL_BOOT__.hash;
  }
}
<?php endif; ?>
</script>
<?php if (is_file($ql_js)): ?>
<script type="module" src="<?= htmlspecialchars(mnbt_theme_url('dist/assets/index.js'), ENT_QUOTES, 'UTF-8') ?>?v=<?= $ql_ver ?>"></script>
<?php endif; ?>
</body>
</html>
