<?php
/**
 * 售卖前端 - 统一布局
 * 仅被 shop_frontend 的视图引用；$content 由各视图通过 ob_start() 填充。
 */
if (!defined('IN_CRONLITE')) { exit; }
$current_user = $current_user ?? null;
$page_title = $page_title ?? '用户中心';
$active = $active ?? '';
$brand = $brand ?? 'MNBT 云服务';
$brand_logo = $brand_logo ?? '';
$brand_primary = $brand_primary ?? '#4f46e5';
$brand_footer = $brand_footer ?? '';
$brand_favicon = $brand_favicon ?? '';
$has_balance = $has_balance ?? false;
$has_hosting = $has_hosting ?? false;
$content = $content ?? '';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<?php if (!empty($brand_favicon)): ?><link rel="icon" href="<?= htmlspecialchars($brand_favicon) ?>"><?php endif; ?>
<title><?= htmlspecialchars($page_title) ?> - <?= htmlspecialchars($brand) ?></title>
<link rel="stylesheet" href="<?= htmlspecialchars($asset_url . 'style.css') ?>">
<style>:root{--brand: <?= htmlspecialchars($brand_primary) ?>;}</style>
</head>
<body class="sf-body">

<?php include __DIR__ . '/partials/navbar.php'; ?>

<main class="sf-container sf-main"><?= $content ?></main>

<footer class="sf-footer">
  <div class="sf-container"><?= htmlspecialchars($brand_footer ?: ($brand . ' · 用户中心')) ?></div>
</footer>

<script>
(function () {
  window.sfMsg = function (id, text, type) {
    var m = document.getElementById(id);
    if (!m) return;
    m.textContent = text || '';
    m.className = 'sf-msg show ' + (type === 'success' ? 'sf-msg-success' : 'sf-msg-error');
  };
  window.sfPost = function (url, data, headers) {
    var body = new URLSearchParams();
    Object.keys(data || {}).forEach(function (k) {
      var v = data[k];
      body.append(k, v === undefined || v === null ? '' : v);
    });
    return fetch(url, {
      method: 'POST',
      headers: Object.assign({ 'Content-Type': 'application/x-www-form-urlencoded' }, headers || {}),
      body: body.toString()
    }).then(function (r) { return r.json(); });
  };
  window.sfChoice = function (form) {
    form.querySelectorAll('.sf-choice').forEach(function (l) { l.classList.remove('active'); });
    form.querySelectorAll('input[type="radio"]:checked').forEach(function (r) {
      var p = r.closest('.sf-choice');
      if (p) p.classList.add('active');
    });
  };
})();
</script>

</body>
</html>
