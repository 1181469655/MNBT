<?php
/**
 * 首页接管示例 - /landing 活动落地页模板
 *
 * 此文件由 bootstrap.php 中注册的 mnbt_register_route 回调 include。
 * 可用变量：
 *   $info  — 路由上下文 ['path'=>'/landing', 'method'=>'GET', 'base'=>'', 'plugin'=>'home_demo', 'route'=>'/landing']
 */
if (!defined('IN_CRONLITE')) {
	exit;
}
$base = isset($info['base']) ? $info['base'] : '';
header('Content-Type: text/html; charset=UTF-8');
?>
<!doctype html>
<html lang="zh-CN">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>活动落地页 - MNBT 首页接管示例</title>
<link rel="icon" href="<?php echo htmlspecialchars(mnbt_asset_url('upload_logo/logo.head.png'), ENT_QUOTES, 'UTF-8'); ?>" type="image/x-icon">
<style>
  *{box-sizing:border-box;margin:0;padding:0}
  body{
    font-family:-apple-system,BlinkMacSystemFont,"Segoe UI","PingFang SC","Microsoft YaHei",sans-serif;
    background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);
    min-height:100vh;
    display:flex;align-items:center;justify-content:center;
    padding:24px;
    color:#1f2937;
  }
  .card{
    background:#fff;
    border-radius:16px;
    box-shadow:0 20px 40px rgba(0,0,0,.15);
    max-width:560px;width:100%;
    padding:48px 40px;
    text-align:center;
  }
  .tag{
    display:inline-block;
    background:#fef3c7;color:#92400e;
    font-size:12px;font-weight:600;
    padding:4px 12px;border-radius:999px;
    margin-bottom:20px;
    letter-spacing:.5px;
  }
  h1{
    font-size:32px;
    font-weight:700;
    color:#111827;
    margin-bottom:12px;
    line-height:1.25;
  }
  .subtitle{
    font-size:16px;color:#6b7280;
    margin-bottom:32px;
    line-height:1.6;
  }
  .features{
    display:grid;grid-template-columns:repeat(2,1fr);
    gap:16px;margin-bottom:32px;text-align:left;
  }
  .feature{
    display:flex;align-items:flex-start;gap:10px;
    font-size:14px;color:#374151;
  }
  .feature .dot{
    width:8px;height:8px;border-radius:50%;
    background:#10b981;margin-top:6px;flex-shrink:0;
  }
  .cta{
    display:inline-block;
    background:#2563eb;color:#fff;
    padding:14px 32px;border-radius:10px;
    font-size:15px;font-weight:600;
    text-decoration:none;
    transition:background .15s;
  }
  .cta:hover{background:#1d4ed8}
  .back{
    display:block;margin-top:20px;
    font-size:13px;color:#6b7280;text-decoration:none;
  }
  .back:hover{color:#374151}
  @media (max-width:480px){
    .card{padding:32px 24px}
    h1{font-size:24px}
    .features{grid-template-columns:1fr}
  }
</style>
</head>
<body>
  <div class="card">
    <span class="tag">活动落地页演示</span>
    <h1>限时优惠，立即体验</h1>
    <p class="subtitle">这是通过 <code>mnbt_register_route('GET', '/landing', ...)</code> 注册的活动落地页。插件可以完全自定义页面内容与样式。</p>

    <div class="features">
      <div class="feature"><span class="dot"></span><span>独立 URL，不依赖主题系统</span></div>
      <div class="feature"><span class="dot"></span><span>支持命名参数路由</span></div>
      <div class="feature"><span class="dot"></span><span>可自定义 HTTP 方法</span></div>
      <div class="feature"><span class="dot"></span><span>子目录部署自动适配</span></div>
    </div>

    <a class="cta" href="<?php echo htmlspecialchars($base . '/user', ENT_QUOTES, 'UTF-8'); ?>">立即开通</a>
    <a class="back" href="<?php echo htmlspecialchars($base . '/', ENT_QUOTES, 'UTF-8'); ?>">&larr; 返回首页</a>
  </div>
</body>
</html>
