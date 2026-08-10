<?php
if (!defined('IN_CRONLITE')) { exit; }
$page_title = $page_title ?? '我的 Docker';
$assets = $assets ?? [];
$base = function_exists('mnbt_home_base') ? mnbt_home_base() : '';
$console_url = $base . '/docker/login.php';
ob_start();
?>
<div class="hs-section">
  <h1>我的 Docker</h1>
  <p>查看已开通的 Docker 账号，使用账号密码登录控制台创建容器</p>
</div>

<?php if (empty($assets)): ?>
  <div class="layui-card"><div class="layui-card-body" style="text-align:center;padding:40px;color:#999;">
    暂无 Docker 资产，<a href="<?= htmlspecialchars(docker_shop_url('docker-shop'), ENT_QUOTES) ?>">前往商城选购</a>
  </div></div>
<?php else: ?>
  <div class="hs-plan-grid">
    <?php foreach ($assets as $a): ?>
      <div class="hs-plan-card">
        <div class="hs-plan-head">
          <h2><?= htmlspecialchars($a['plan_name']) ?></h2>
          <span class="hs-plan-tag"><?= $a['status'] === 'active' ? '有效' : htmlspecialchars($a['status']) ?></span>
        </div>
        <ul class="hs-plan-spec">
          <li><span>Docker 账号</span><b><?= htmlspecialchars($a['docker_username']) ?></b></li>
          <li><span>登录密码</span><b><?= htmlspecialchars($a['docker_password']) ?></b></li>
          <li><span>容器状态</span><b><?= htmlspecialchars($a['container_status'] ?? 'none') ?></b></li>
          <li><span>到期时间</span><b><?= htmlspecialchars($a['expire_at'] ?: '永久') ?></b></li>
        </ul>
        <div class="hs-plan-buy">
          <a class="layui-btn layui-btn-primary" href="<?= htmlspecialchars($console_url, ENT_QUOTES) ?>" target="_blank">前往控制台</a>
          <button class="layui-btn layui-btn-primary" type="button" onclick="window.open('<?= htmlspecialchars($console_url, ENT_QUOTES) ?>','_blank')">登录 Docker</button>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
