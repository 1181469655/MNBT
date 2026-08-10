<?php
/**
 * MNBT 默认主页落地页（内置，不依赖插件）
 * 数据由 MPHX/frontend.php 的 mnbt_home_data() 注入：
 *   $site_title / $site_logo / $site_primary / $site_hero / $site_footer / $favicon
 *   $notice / $show_notice / $show_plans / $logged_in / $has_shop / $has_user
 *   $plans（套餐卡）/ $blocks（插件扩展区块）
 *   $url($path) 路由 URL / $coreUrl($path) 核心文件 URL
 * 主题可提供 templates/{theme}/home/index.php 覆盖本文件。
 */
if (!defined('IN_CRONLITE')) { exit('Access Denied'); }
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($site_title) ?></title>
<?php if ($favicon): ?><link rel="icon" href="<?= htmlspecialchars($favicon) ?>"><?php endif; ?>
<style>
:root {
  --brand: <?= htmlspecialchars($site_primary ?: '#4f46e5') ?>;
  --brand-soft: color-mix(in srgb, var(--brand) 7%, #fff);
  --brand-light: color-mix(in srgb, var(--brand) 92%, #fff);
  --text: #111827;
  --text-2: #4b5563;
  --text-3: #9ca3af;
  --border: #e5e7eb;
  --bg: #f9fafb;
  --white: #fff;
  --r: 16px;
  --rs: 10px;
}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
html{scroll-behavior:smooth}
body{
  font-family:-apple-system,BlinkMacSystemFont,"Segoe UI","PingFang SC","Hiragino Sans GB","Microsoft YaHei",sans-serif;
  color:var(--text);background:var(--white);line-height:1.6;
  -webkit-font-smoothing:antialiased;font-size:15px;
}
a{text-decoration:none;color:inherit}
img{max-width:100%;display:block}
.w{width:min(1120px,calc(100% - 48px));margin:0 auto}

/* ── 顶栏 ── */
.nav{
  position:sticky;top:0;z-index:50;
  background:rgba(255,255,255,.88);backdrop-filter:blur(8px);
  border-bottom:1px solid var(--border);
}
.nav .w{display:flex;align-items:center;justify-content:space-between;height:64px}
.nav .brand{display:flex;align-items:center;gap:10px;font-weight:700;font-size:17px;letter-spacing:-.01em}
.nav .brand img{width:28px;height:28px;object-fit:contain}
.nav .actions{display:flex;gap:10px;align-items:center}

/* ── 按钮 ── */
.btn{
  display:inline-flex;align-items:center;justify-content:center;gap:7px;
  padding:9px 18px;border-radius:var(--rs);font-weight:600;font-size:14px;
  border:1px solid transparent;transition:.15s;cursor:pointer;white-space:nowrap;
}
.btn-fill{background:var(--brand);color:#fff}
.btn-fill:hover{background:var(--brand-light)}
.btn-outline{border:1px solid var(--border);color:var(--text);background:var(--white)}
.btn-outline:hover{background:var(--bg)}
.btn-lg{padding:13px 26px;font-size:15px;border-radius:12px}

/* ── Hero ── */
.hero{text-align:center;padding:92px 0 84px}
.hero .badge{
  display:inline-flex;align-items:center;gap:6px;margin-bottom:20px;
  padding:6px 14px;border-radius:999px;border:1px solid var(--border);
  background:var(--brand-soft);color:var(--brand);font-size:13px;font-weight:600;
}
.hero h1{
  font-size:clamp(2.2rem, 5.5vw, 3.8rem);
  font-weight:800;letter-spacing:-.04em;line-height:1.12;
  max-width:18ch;margin:0 auto 16px;
}
.hero h1 span{color:var(--brand)}
.hero .sub{
  font-size:1.08rem;color:var(--text-2);max-width:52ch;margin:0 auto 32px;line-height:1.75;
}
.hero .btns{display:flex;gap:10px;justify-content:center;flex-wrap:wrap;margin-bottom:40px}
.hero .stats-row{
  display:inline-flex;gap:36px;flex-wrap:wrap;justify-content:center;
  padding:14px 26px;border:1px solid var(--border);border-radius:var(--r);background:var(--bg);
}
.hero .stat{text-align:center}
.hero .stat .num{display:block;font-size:1.2rem;font-weight:700}
.hero .stat .label{font-size:12px;color:var(--text-3);margin-top:2px}

/* ── 区块 ── */
.sec{padding:72px 0}
.sec.dim{background:var(--bg);border-top:1px solid var(--border);border-bottom:1px solid var(--border)}
.sec-head{text-align:center;max-width:560px;margin:0 auto 44px}
.sec-head .tag{
  display:inline-block;margin-bottom:10px;padding:5px 12px;border-radius:999px;
  background:var(--brand-soft);color:var(--brand);font-size:12px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;
}
.sec-head h2{font-size:1.8rem;font-weight:800;letter-spacing:-.02em;line-height:1.25;margin-bottom:10px}
.sec-head p{color:var(--text-3);font-size:15px}

/* ── 公告 ── */
.notice-box{
  display:flex;gap:12px;align-items:flex-start;
  padding:16px 20px;border:1px solid var(--border);border-radius:var(--r);background:var(--white);
}
.notice-box .mdi{
  font-size:20px;line-height:1.5;flex-shrink:0;
}
.notice-box .txt{color:var(--text-2);font-size:14px;white-space:pre-line;word-break:break-word}

/* ── 套餐卡 ── */
.g-plan{display:grid;grid-template-columns:repeat(3,1fr);gap:18px}
.g-plan .card{
  background:var(--white);border:1px solid var(--border);border-radius:var(--r);
  display:flex;flex-direction:column;transition:.18s;
}
.g-plan .card:hover{border-color:#cbd5e1;box-shadow:0 6px 24px rgba(17,24,39,.05)}
.g-plan .card.pop{border-color:var(--brand);box-shadow:0 0 0 1px var(--brand)}
.g-plan .card-top{padding:26px 24px;border-bottom:1px solid var(--border)}
.g-plan .card-top .chip{
  display:inline-block;padding:4px 10px;border-radius:999px;
  background:var(--brand-soft);color:var(--brand);font-size:12px;font-weight:700;margin-bottom:10px;
}
.g-plan .card-top h3{font-size:1.15rem;letter-spacing:-.02em;margin-bottom:4px}
.g-plan .card-top .desc{color:var(--text-3);font-size:14px;min-height:40px}
.g-plan .card-body{padding:22px 24px;display:flex;flex-direction:column;flex:1}
.g-plan .price{margin-bottom:18px}
.g-plan .price .num{font-size:1.9rem;font-weight:800;letter-spacing:-.03em}
.g-plan .price .sub{color:var(--text-3);font-size:13px}
.g-plan ul{list-style:none;display:grid;gap:9px;margin-bottom:22px;flex:1}
.g-plan ul li{display:flex;gap:9px;font-size:14px;color:var(--text-2)}
.g-plan ul li .ok{color:var(--brand);flex-shrink:0}
.g-plan .card .btn{width:100%}
.empty{
  text-align:center;padding:56px 20px;border:1px dashed var(--border);border-radius:var(--r);color:var(--text-3);
}

/* ── 页脚 ── */
.footer{
  padding:32px 0;border-top:1px solid var(--border);
  text-align:center;color:var(--text-3);font-size:13px;
}

@media(max-width:860px){
  .g-plan{grid-template-columns:1fr}
  .hero{padding:64px 0 56px}
}
@media(max-width:540px){
  .hero .btns{flex-direction:column;align-items:center}
  .nav .brand .site-name{display:none}
}
</style>
</head>
<body>

<nav class="nav">
  <div class="w">
    <a class="brand" href="<?= htmlspecialchars($coreUrl('')) ?>">
      <?php if ($site_logo): ?><img src="<?= htmlspecialchars($site_logo) ?>" alt="logo"><?php endif; ?>
      <span class="site-name"><?= htmlspecialchars($site_title) ?></span>
    </a>
    <div class="actions">
      <?php if ($has_shop): ?>
        <a class="btn btn-outline" href="<?= htmlspecialchars($url('/shop')) ?>">主机套餐</a>
      <?php endif; ?>
      <?php if ($logged_in): ?>
        <a class="btn btn-outline" href="<?= htmlspecialchars($has_user ? $url('/account/profile') : $coreUrl('user/index.php')) ?>">控制台</a>
      <?php else: ?>
        <a class="btn btn-outline" href="<?= htmlspecialchars($has_user ? $url('/account/login') : $coreUrl('user/login.php')) ?>">登录</a>
        <?php if ($has_user): ?>
          <a class="btn btn-fill" href="<?= htmlspecialchars($url('/account/register')) ?>">免费注册</a>
        <?php endif; ?>
      <?php endif; ?>
    </div>
  </div>
</nav>

<section class="hero">
  <div class="w">
    <div class="badge">高性能 · 即开即用 · 自动部署</div>
    <h1><?= htmlspecialchars($site_hero ?: $site_title) ?></h1>
    <p class="sub">全 SSD 存储、BGP 多线接入——支付完成后自动开通，分钟级上线，为企业和开发者打造的虚拟主机平台。</p>
    <div class="btns">
      <?php if ($has_shop): ?>
        <a class="btn btn-fill btn-lg" href="<?= htmlspecialchars($url('/shop')) ?>">查看全部套餐</a>
      <?php endif; ?>
      <a class="btn btn-outline btn-lg" href="<?= htmlspecialchars($logged_in ? ($has_shop ? $url('/shop/assets') : $coreUrl('user/index.php')) : ($has_user ? $url('/account/register') : $coreUrl('user/login.php'))) ?>">
        <?= $logged_in ? '我的主机' : ($has_user ? '免费注册' : '用户登录') ?>
      </a>
    </div>
    <div class="stats-row">
      <div class="stat"><span class="num">99.9%</span><span class="label">服务可用性</span></div>
      <div class="stat"><span class="num">&lt; 1 min</span><span class="label">平均开通时间</span></div>
      <div class="stat"><span class="num">7×24</span><span class="label">技术支持</span></div>
    </div>
  </div>
</section>

<?php if ($show_notice && $notice !== ''): ?>
<section class="sec" style="padding:0 0 72px">
  <div class="w">
    <div class="notice-box">
      <span class="mdi mdi-bullhorn" style="color:<?= htmlspecialchars($site_primary) ?>"></span>
      <div class="txt"><?= htmlspecialchars($notice) ?></div>
    </div>
  </div>
</section>
<?php endif; ?>

<?php if ($show_plans): ?>
<section class="sec dim">
  <div class="w">
    <div class="sec-head">
      <div class="tag">Pricing</div>
      <h2>选择适合的套餐</h2>
      <p>按需选择，随时升级。价格透明，开通简单。</p>
    </div>
    <?php if (empty($plans)): ?>
      <div class="empty">暂无可购买套餐，请联系管理员</div>
    <?php else: ?>
      <div class="g-plan">
        <?php foreach (array_slice($plans, 0, 3) as $i => $plan): ?>
        <div class="card<?= $i === 1 ? ' pop' : '' ?>">
          <div class="card-top">
            <?php if ($i === 1): ?><span class="chip">推荐</span><?php endif; ?>
            <h3><?= htmlspecialchars($plan['name']) ?></h3>
            <div class="desc"><?= htmlspecialchars($plan['desc'] ?: '适合中小站点快速上线') ?></div>
          </div>
          <div class="card-body">
            <div class="price">
              <div class="num"><?= htmlspecialchars($plan['price']) ?></div>
              <div class="sub">含基础资源与自动开通</div>
            </div>
            <ul>
              <?php foreach ($plan['feats'] as $feat): ?>
                <li><span class="ok">✓</span><?= htmlspecialchars($feat) ?></li>
              <?php endforeach; ?>
              <?php if (empty($plan['feats'])): ?>
                <li><span class="ok">✓</span>高性能节点资源</li>
                <li><span class="ok">✓</span>一键开通部署</li>
              <?php endif; ?>
            </ul>
            <a class="btn btn-fill" href="<?= htmlspecialchars($url('/shop/order/' . $plan['id'])) ?>">立即购买</a>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>
<?php endif; ?>

<?php foreach ($blocks as $block): ?>
<section class="sec<?= !empty($block['order']) && ($block['order'] % 2) === 0 ? ' dim' : '' ?>">
  <div class="w">
    <?php if (!empty($block['title'])): ?>
      <div class="sec-head">
        <div class="tag">Extras</div>
        <h2><?= htmlspecialchars($block['title']) ?></h2>
      </div>
    <?php endif; ?>
    <?= $block['html'] ?>
  </div>
</section>
<?php endforeach; ?>

<footer class="footer">
  <div class="w"><?= htmlspecialchars($site_footer ?: ('© ' . date('Y') . ' ' . $site_title . '. All rights reserved.')) ?></div>
</footer>

</body>
</html>
