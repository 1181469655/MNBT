<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($title) ?></title>
<?php if ($favicon): ?><link rel="icon" href="<?= htmlspecialchars($favicon) ?>"><?php endif; ?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@mdi/font@7.4.47/css/materialdesignicons.min.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<style>
:root {
  --brand: <?= htmlspecialchars($primary ?: '#2563eb') ?>;
  --brand-soft: color-mix(in srgb, var(--brand) 8%, #fff);
  --brand-light: color-mix(in srgb, var(--brand) 90%, #fff);
  --text: #0f172a;
  --text-2: #475569;
  --text-3: #94a3b8;
  --border: #e2e8f0;
  --bg: #f8fafc;
  --white: #fff;
  --r: 16px;
  --rs: 10px;
}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
html{scroll-behavior:smooth}
body{
  font-family:"Inter",-apple-system,BlinkMacSystemFont,"Segoe UI","PingFang SC","Microsoft YaHei",sans-serif;
  color:var(--text);background:var(--white);line-height:1.6;
  -webkit-font-smoothing:antialiased;font-size:15px;
}
a{text-decoration:none;color:inherit}
img{max-width:100%;display:block}
.w{width:min(1160px,calc(100% - 48px));margin:0 auto}

/* ── Navbar ── */
.navbar{
  position:sticky;top:0;z-index:100;
  background:rgba(255,255,255,.88);
  border-bottom:1px solid var(--border);
  backdrop-filter:blur(12px);
}
.navbar .w{height:68px;display:flex;align-items:center;justify-content:space-between;gap:24px}
.nav-brand{display:flex;align-items:center;gap:10px;font-weight:800;font-size:1.1rem;color:var(--text);letter-spacing:-.02em}
.nav-brand img{height:34px;width:auto;border-radius:8px}
.nav-brand .mark{
  width:36px;height:36px;border-radius:8px;background:var(--brand);color:#fff;
  display:grid;place-items:center;font-weight:800;font-size:15px;
}
.nav-links{display:flex;align-items:center;gap:4px}
.nav-links a{
  padding:8px 14px;border-radius:8px;font-size:14px;font-weight:500;color:var(--text-2);transition:.15s;
}
.nav-links a:hover{background:var(--bg);color:var(--text)}

.btn{
  display:inline-flex;align-items:center;justify-content:center;gap:7px;
  padding:10px 18px;border-radius:var(--rs);font-weight:600;font-size:14px;
  border:1px solid transparent;transition:.15s;cursor:pointer;
  white-space:nowrap;
}
.btn-fill{background:var(--brand);color:#fff;box-shadow:0 1px 3px rgba(0,0,0,.08)}
.btn-fill:hover{background:var(--brand-light)}
.btn-outline{border:1px solid var(--border);color:var(--text);background:var(--white)}
.btn-outline:hover{background:var(--bg)}
.btn-ghost{color:var(--text-2);background:transparent}
.btn-ghost:hover{background:var(--bg)}
.btn-lg{padding:14px 26px;font-size:15px;border-radius:12px}

/* ── Hero ── */
.hero{text-align:center;padding:96px 0 88px}
.hero .badge{
  display:inline-flex;align-items:center;gap:6px;margin-bottom:20px;
  padding:6px 14px;border-radius:999px;border:1px solid var(--border);
  background:var(--brand-soft);color:var(--brand);font-size:13px;font-weight:600;
}
.hero h1{
  font-size:clamp(2.6rem, 6vw, 4.2rem);
  font-weight:900;letter-spacing:-.05em;line-height:1.08;
  max-width:16ch;margin:0 auto 16px;
  color:var(--text);
}
.hero h1 span{color:var(--brand)}
.hero .sub{
  font-size:1.12rem;color:var(--text-2);max-width:48ch;margin:0 auto 32px;line-height:1.75;
}
.hero .btns{display:flex;gap:10px;justify-content:center;flex-wrap:wrap;margin-bottom:40px}
.hero .stats-row{
  display:inline-flex;gap:32px;flex-wrap:wrap;justify-content:center;
  padding:14px 24px;border:1px solid var(--border);border-radius:var(--r);
  background:var(--bg);
}
.hero .stat{text-align:center}
.hero .stat .num{display:block;font-size:1.25rem;font-weight:700;color:var(--text)}
.hero .stat .label{font-size:12px;color:var(--text-3);margin-top:2px}

/* ── Section ── */
.sec{padding:80px 0}
.sec.dim{background:var(--bg);border-top:1px solid var(--border);border-bottom:1px solid var(--border)}
.sec-head{text-align:center;max-width:520px;margin:0 auto 48px}
.sec-head .tag{
  display:inline-block;margin-bottom:10px;padding:5px 12px;border-radius:999px;
  background:var(--brand-soft);color:var(--brand);font-size:12px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;
}
.sec-head h2{font-size:2rem;font-weight:800;letter-spacing:-.03em;line-height:1.2;margin-bottom:10px}
.sec-head p{color:var(--text-3);font-size:15px}

/* ── Feature grid ── */
.g-feat{display:grid;grid-template-columns:repeat(3,1fr);gap:16px}
.g-feat .card{
  padding:28px 24px;background:var(--white);border:1px solid var(--border);
  border-radius:var(--r);transition:.2s;
}
.g-feat .card:hover{border-color:#cbd5e1;box-shadow:0 4px 16px rgba(15,23,42,.04)}
.g-feat .card .icon{
  width:44px;height:44px;border-radius:12px;display:grid;place-items:center;
  background:var(--brand-soft);color:var(--brand);font-size:22px;margin-bottom:14px;
}
.g-feat .card h3{font-size:1rem;margin-bottom:6px}
.g-feat .card p{color:var(--text-3);font-size:14px;line-height:1.6}

/* ── Plans ── */
.g-plan{display:grid;grid-template-columns:repeat(3,1fr);gap:18px}
.g-plan .card{
  background:var(--white);border:1px solid var(--border);border-radius:var(--r);
  display:flex;flex-direction:column;transition:.2s;
}
.g-plan .card:hover{border-color:#cbd5e1;box-shadow:0 6px 24px rgba(15,23,42,.05)}
.g-plan .card.pop{border-color:var(--brand);box-shadow:0 0 0 1px var(--brand)}
.g-plan .card-top{padding:28px 24px;border-bottom:1px solid var(--border)}
.g-plan .card-top .chip{
  display:inline-block;padding:4px 10px;border-radius:999px;
  background:var(--brand-soft);color:var(--brand);font-size:12px;font-weight:700;margin-bottom:10px;
}
.g-plan .card-top h3{font-size:1.2rem;letter-spacing:-.02em;margin-bottom:4px}
.g-plan .card-top .desc{color:var(--text-3);font-size:14px;min-height:36px}
.g-plan .card-body{padding:24px;display:flex;flex-direction:column;flex:1}
.g-plan .price{margin-bottom:20px}
.g-plan .price .num{font-size:2rem;font-weight:800;letter-spacing:-.03em}
.g-plan .price .sub{color:var(--text-3);font-size:13px}
.g-plan ul{list-style:none;display:grid;gap:10px;margin-bottom:24px;flex:1}
.g-plan ul li{display:flex;gap:10px;font-size:14px;color:var(--text-2)}
.g-plan ul li .mdi{color:var(--brand);font-size:17px;line-height:1.3;flex-shrink:0}
.g-plan .card .btn{width:100%}

.empty{
  text-align:center;padding:64px 20px;border:1px dashed var(--border);border-radius:var(--r);color:var(--text-3);
}
.empty .mdi{font-size:48px;margin-bottom:10px}
.more{margin-top:32px;text-align:center}

/* ── CTA banner ── */
.banner{
  background:var(--brand);color:#fff;border-radius:var(--r);
  padding:40px 48px;display:flex;align-items:center;justify-content:space-between;
  gap:24px;flex-wrap:wrap;margin:0 0 80px;
}
.banner h2{font-size:1.4rem;font-weight:700;letter-spacing:-.02em;margin-bottom:6px}
.banner p{color:rgba(255,255,255,.8);font-size:15px}
.banner .btn-fill{background:#fff;color:var(--brand)}
.banner .btn-fill:hover{background:rgba(255,255,255,.92)}

/* ── Footer ── */
.foot{border-top:1px solid var(--border);background:var(--bg);padding:28px 0;color:var(--text-3);font-size:13px;text-align:center}

@media(max-width:860px){
  .g-feat,.g-plan{grid-template-columns:1fr}
  .hero{padding:64px 0 56px}
  .banner{flex-direction:column;text-align:center;padding:32px 24px}
}
@media(max-width:540px){
  .navbar .w{height:auto;padding:12px 0;flex-wrap:wrap;gap:10px}
  .nav-links{width:100%;justify-content:flex-end}
  .hero .btns{flex-direction:column;align-items:center}
}
</style>
</head>
<body>

<header class="navbar">
  <div class="w">
    <a class="nav-brand" href="/">
      <?php if ($logo): ?>
        <img src="<?= htmlspecialchars($logo) ?>" alt="<?= htmlspecialchars($title) ?>">
      <?php else: ?>
        <span class="mark"><?= htmlspecialchars(mb_substr($title, 0, 1, 'UTF-8')) ?></span>
      <?php endif; ?>
      <?= htmlspecialchars($title) ?>
    </a>
    <nav class="nav-links">
      <a href="<?= $url('/shop') ?>">套餐</a>
      <?php if ($user): ?>
        <a href="<?= $url('/shop/orders') ?>">订单</a>
        <a href="<?= $url('/balance') ?>">余额</a>
        <a class="btn btn-ghost" style="font-weight:600" href="<?= $url('/user') ?>">
          <span class="mdi mdi-account-circle"></span> <?= htmlspecialchars($user['username']) ?>
        </a>
        <a class="btn btn-outline" href="<?= $url('/account/logout') ?>">退出</a>
      <?php else: ?>
        <a href="<?= $url('/account/login') ?>">登录</a>
        <a class="btn btn-fill" href="<?= $url('/account/register') ?>">免费注册</a>
      <?php endif; ?>
    </nav>
  </div>
</header>

<section class="hero">
  <div class="w">
    <div class="badge">
      <span class="mdi mdi-lightning-bolt"></span> 高性能 · 即开即用 · 自动部署
    </div>
    <h1><?= htmlspecialchars($hero ?: $title) ?></h1>
    <p class="sub">为企业与开发者打造的虚拟主机平台。全 SSD 存储、BGP 多线接入——支付完成后自动开通，分钟级上线。</p>
    <div class="btns">
      <a class="btn btn-fill btn-lg" href="<?= $url('/shop') ?>">
        <span class="mdi mdi-package-variant-closed"></span> 查看全部套餐
      </a>
      <a class="btn btn-outline btn-lg" href="<?= $url($user ? '/shop/assets' : '/account/register') ?>">
        <?= $user ? '<span class="mdi mdi-server"></span> 我的主机' : '免费注册' ?>
      </a>
    </div>
    <div class="stats-row">
      <?php foreach ([['99.9%','服务可用性'],['&lt; 1 min','平均开通时间'],['7×24','技术支持']] as $s): ?>
      <div class="stat"><span class="num"><?= $s[0] ?></span><span class="label"><?= $s[1] ?></span></div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="sec dim">
  <div class="w">
    <div class="sec-head">
      <div class="tag">Features</div>
      <h2>稳定交付，从开通开始</h2>
      <p>不只是卖主机。我们把开通、运维、支持做成可预期的服务标准。</p>
    </div>
    <div class="g-feat">
      <?php foreach ($features as $f): ?>
      <div class="card">
        <div class="icon"><span class="mdi <?= htmlspecialchars($f['icon']) ?>"></span></div>
        <h3><?= htmlspecialchars($f['title']) ?></h3>
        <p><?= htmlspecialchars($f['desc']) ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="sec">
  <div class="w">
    <div class="sec-head">
      <div class="tag">Pricing</div>
      <h2>选择适合的套餐</h2>
      <p>按需选择，随时升级。价格透明，开通简单。</p>
    </div>
    <?php if (empty($planCards)): ?>
      <div class="empty">
        <div class="mdi mdi-package-variant"></div>
        <p>暂无可购买套餐，请联系管理员</p>
      </div>
    <?php else: ?>
      <div class="g-plan">
        <?php foreach (array_slice($planCards, 0, 3) as $i => $plan): ?>
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
                <li><span class="mdi mdi-check-circle"></span><?= htmlspecialchars($feat) ?></li>
              <?php endforeach; ?>
              <?php if (empty($plan['feats'])): ?>
                <li><span class="mdi mdi-check-circle"></span>高性能节点资源</li>
                <li><span class="mdi mdi-check-circle"></span>一键开通部署</li>
              <?php endif; ?>
            </ul>
            <a class="btn btn-fill" href="<?= $url('/shop/order/' . $plan['id']) ?>">立即购买</a>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <div class="more">
        <a class="btn btn-outline btn-lg" href="<?= $url('/shop') ?>">查看全部套餐 <span class="mdi mdi-arrow-right"></span></a>
      </div>
    <?php endif; ?>
  </div>
</section>

<section class="sec" style="padding-top:0">
  <div class="w">
    <div class="banner">
      <div>
        <h2>准备好开始了？</h2>
        <p>注册账号，选择套餐，分钟级完成主机开通。</p>
      </div>
      <div style="display:flex;gap:10px;flex-wrap:wrap">
        <a class="btn btn-fill btn-lg" href="<?= $url('/shop') ?>">浏览套餐</a>
        <?php if (!$user): ?>
          <a class="btn btn-outline btn-lg" style="border-color:rgba(255,255,255,.3);color:#fff" href="<?= $url('/account/register') ?>">创建账号</a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>

<footer class="foot"><div class="w"><?= htmlspecialchars($footer) ?></div></footer>

</body>
</html>
