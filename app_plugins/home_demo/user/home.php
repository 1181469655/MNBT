<?php
/**
 * 首页接管示例 - 自定义首页模板
 *
 * 此文件由 bootstrap.php 中注册的 mnbt_register_home 回调在「渲染」模式下 include。
 * 可用变量：
 *   $conf  — 站点配置数组（MN_config 表行），含 name/auther/hxp 等
 *   $info  — 请求上下文 ['path'=>'/', 'method'=>'GET', 'base'=>'']
 *   $DB    — 数据库实例
 *
 * 模板设计原则（遵循用户偏好）：
 *   - 简洁自然，无 AI 光效
 *   - 响应式，移动端友好
 *   - 不依赖外部 CSS 框架，纯内联样式，避免路径问题
 */
if (!defined('IN_CRONLITE')) {
	exit;
}
$siteName = isset($conf['name']) ? htmlspecialchars((string)$conf['name'], ENT_QUOTES, 'UTF-8') : 'MNBT';
$footer = isset($conf['hxp']) ? htmlspecialchars((string)$conf['hxp'], ENT_QUOTES, 'UTF-8') : '';
$base = isset($info['base']) ? $info['base'] : '';
$logoUrl = mnbt_asset_url('upload_logo/logo.index.png');
$userUrl = $base . '/user';
$adminUrl = $base . '/admin';
header('Content-Type: text/html; charset=UTF-8');
?>
<!doctype html>
<html lang="zh-CN">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title><?php echo $siteName; ?></title>
<link rel="icon" href="<?php echo htmlspecialchars(mnbt_asset_url('upload_logo/logo.head.png'), ENT_QUOTES, 'UTF-8'); ?>" type="image/x-icon">
<style>
  :root{
    --bg:#ffffff;
    --fg:#0f172a;
    --muted:#64748b;
    --border:#e2e8f0;
    --primary:#2563eb;
    --primary-hover:#1d4ed8;
    --primary-soft:#eff6ff;
    --card:#f8fafc;
    --radius:12px;
    --shadow:0 1px 3px rgba(15,23,42,.06),0 1px 2px rgba(15,23,42,.04);
  }
  *{box-sizing:border-box;margin:0;padding:0}
  html,body{height:100%}
  body{
    font-family:-apple-system,BlinkMacSystemFont,"Segoe UI","PingFang SC","Hiragino Sans GB","Microsoft YaHei",sans-serif;
    background:var(--bg);
    color:var(--fg);
    line-height:1.6;
    -webkit-font-smoothing:antialiased;
    display:flex;
    flex-direction:column;
    min-height:100vh;
  }
  a{color:var(--primary);text-decoration:none}
  a:hover{color:var(--primary-hover)}
  .container{width:100%;max-width:1120px;margin:0 auto;padding:0 24px}

  /* 顶栏 */
  .navbar{
    border-bottom:1px solid var(--border);
    background:rgba(255,255,255,.9);
    backdrop-filter:saturate(180%) blur(8px);
    position:sticky;top:0;z-index:10;
  }
  .navbar-inner{display:flex;align-items:center;justify-content:space-between;height:64px}
  .brand{display:flex;align-items:center;gap:10px;font-weight:600;font-size:18px;color:var(--fg)}
  .brand img{height:32px;width:auto;display:block}
  .nav-actions{display:flex;gap:8px}
  .btn{
    display:inline-flex;align-items:center;gap:6px;
    padding:8px 16px;border-radius:8px;
    font-size:14px;font-weight:500;
    border:1px solid transparent;
    transition:background .15s,border-color .15s,color .15s;
    cursor:pointer;
  }
  .btn-ghost{color:var(--muted);border-color:transparent}
  .btn-ghost:hover{color:var(--fg);background:var(--card)}
  .btn-primary{background:var(--primary);color:#fff}
  .btn-primary:hover{background:var(--primary-hover);color:#fff}
  .btn-outline{color:var(--primary);border-color:var(--primary)}
  .btn-outline:hover{background:var(--primary-soft)}

  /* Hero */
  .hero{padding:72px 0 48px;text-align:center}
  .hero h1{
    font-size:clamp(32px,5vw,48px);
    line-height:1.2;
    font-weight:700;
    letter-spacing:-.02em;
    margin-bottom:16px;
  }
  .hero p{
    font-size:clamp(15px,2.2vw,18px);
    color:var(--muted);
    max-width:640px;margin:0 auto 32px;
  }
  .hero-actions{display:flex;gap:12px;justify-content:center;flex-wrap:wrap}
  .hero-actions .btn{padding:12px 24px;font-size:15px}

  /* 特性卡片 */
  .features{padding:32px 0 64px}
  .features-grid{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(240px,1fr));
    gap:20px;
  }
  .feature-card{
    background:var(--card);
    border:1px solid var(--border);
    border-radius:var(--radius);
    padding:24px;
    transition:transform .15s,box-shadow .15s;
  }
  .feature-card:hover{
    transform:translateY(-2px);
    box-shadow:var(--shadow);
  }
  .feature-icon{
    width:40px;height:40px;
    border-radius:8px;
    background:var(--primary-soft);
    color:var(--primary);
    display:flex;align-items:center;justify-content:center;
    margin-bottom:14px;
  }
  .feature-icon svg{width:20px;height:20px}
  .feature-card h3{font-size:16px;font-weight:600;margin-bottom:6px}
  .feature-card p{font-size:14px;color:var(--muted)}

  /* 页脚 */
  .footer{
    margin-top:auto;
    border-top:1px solid var(--border);
    padding:24px 0;
    color:var(--muted);
    font-size:13px;
  }
  .footer-inner{display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px}
  .footer a{color:var(--muted)}
  .footer a:hover{color:var(--fg)}

  @media (max-width:640px){
    .hero{padding:48px 0 32px}
    .navbar-inner{height:56px}
    .brand{font-size:16px}
    .btn{padding:6px 12px;font-size:13px}
  }
</style>
</head>
<body>

<nav class="navbar">
  <div class="container navbar-inner">
    <a class="brand" href="<?php echo htmlspecialchars($userUrl, ENT_QUOTES, 'UTF-8'); ?>">
      <img src="<?php echo htmlspecialchars($logoUrl, ENT_QUOTES, 'UTF-8'); ?>" alt="logo">
      <span><?php echo $siteName; ?></span>
    </a>
    <div class="nav-actions">
      <a class="btn btn-ghost" href="<?php echo htmlspecialchars($userUrl, ENT_QUOTES, 'UTF-8'); ?>">用户面板</a>
      <a class="btn btn-outline" href="<?php echo htmlspecialchars($adminUrl, ENT_QUOTES, 'UTF-8'); ?>">管理后台</a>
    </div>
  </div>
</nav>

<section class="hero">
  <div class="container">
    <h1>欢迎来到 <?php echo $siteName; ?></h1>
    <p>稳定可靠的宝塔主机控制系统，提供站点管理、数据库、文件管理、监控告警等一站式能力。</p>
    <div class="hero-actions">
      <a class="btn btn-primary" href="<?php echo htmlspecialchars($userUrl, ENT_QUOTES, 'UTF-8'); ?>">登录控制面板</a>
      <a class="btn btn-outline" href="<?php echo htmlspecialchars($base . '/landing', ENT_QUOTES, 'UTF-8'); ?>">查看活动</a>
    </div>
  </div>
</section>

<section class="features">
  <div class="container">
    <div class="features-grid">
      <div class="feature-card">
        <div class="feature-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect><line x1="8" y1="21" x2="16" y2="21"></line><line x1="12" y1="17" x2="12" y2="21"></line></svg>
        </div>
        <h3>站点管理</h3>
        <p>一键开通/绑定域名，支持 Nginx/Apache 配置自动生成。</p>
      </div>
      <div class="feature-card">
        <div class="feature-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><ellipse cx="12" cy="5" rx="9" ry="3"></ellipse><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"></path><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"></path></svg>
        </div>
        <h3>数据库</h3>
        <p>可视化数据库管理，支持备份、导入、SQL 在线执行。</p>
      </div>
      <div class="feature-card">
        <div class="feature-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path></svg>
        </div>
        <h3>文件管理</h3>
        <p>在线文件管理器，支持上传、下载、压缩、编辑代码。</p>
      </div>
      <div class="feature-card">
        <div class="feature-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 12h-4l-3 9L9 3l-3 9H2"></path></svg>
        </div>
        <h3>监控告警</h3>
        <p>实时监控主机状态，异常自动告警，保障业务稳定。</p>
      </div>
    </div>
  </div>
</section>

<footer class="footer">
  <div class="container footer-inner">
    <span>&copy; <?php echo date('Y'); ?> <?php echo $siteName; ?></span>
    <?php if ($footer !== ''): ?>
      <span><?php echo $footer; ?></span>
    <?php else: ?>
      <span>Powered by MNBT</span>
    <?php endif; ?>
  </div>
</footer>

</body>
</html>
