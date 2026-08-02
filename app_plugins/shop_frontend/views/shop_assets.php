<?php
if (!defined('IN_CRONLITE')) { exit; }
$page_title = $page_title ?? '我的主机';
$assets = $assets ?? [];
$status_labels = ['active' => '正常', 'expired' => '已到期', 'cancelled' => '已取消'];
$status_cls = ['active' => 'success', 'expired' => 'warning', 'cancelled' => 'danger'];
ob_start();
?>
<div class="sf-section">
  <div>
    <h1>我的主机</h1>
    <p>已开通的虚拟主机资产</p>
  </div>
  <a href="<?= shop_frontend_url('shop') ?>" class="sf-btn sf-btn-primary">购买新主机</a>
</div>

<div class="sf-card">
  <div class="sf-card-body-flush">
    <?php if (empty($assets)): ?>
      <div class="sf-empty">您还没有开通的主机，<a href="<?= shop_frontend_url('shop') ?>">去购买</a></div>
    <?php else: ?>
      <div class="sf-table-wrap">
        <table class="sf-table sf-asset-table">
          <thead><tr><th>套餐</th><th>主机账号</th><th>控制面板密码</th><th>节点</th><th>到期时间</th><th>状态</th><th>操作</th></tr></thead>
          <tbody>
            <?php foreach ($assets as $a): ?>
              <tr>
                <td><?= htmlspecialchars($a['plan_name']) ?></td>
                <td class="sf-mono"><span class="sf-copy-text" data-copy="<?= htmlspecialchars($a['host_user'] ?? '', ENT_QUOTES) ?>"><?= htmlspecialchars($a['host_user'] ?? '-') ?></span></td>
                <td class="sf-mono">
                  <?php if (!empty($a['host_pass'])): ?>
                    <span class="sf-pass-mask" data-pass="<?= htmlspecialchars($a['host_pass'], ENT_QUOTES) ?>">********</span>
                    <button type="button" class="sf-icon-btn sf-toggle-pass" title="显示/隐藏密码">&#128065;</button>
                    <button type="button" class="sf-icon-btn sf-copy-btn" data-copy="<?= htmlspecialchars($a['host_pass'], ENT_QUOTES) ?>" title="复制密码">&#128203;</button>
                  <?php else: ?>
                    -
                  <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($a['ssbt'] ?? '-') ?></td>
                <td><?= htmlspecialchars($a['expire_at']) ?></td>
                <td><span class="sf-badge sf-badge-<?= $status_cls[$a['status']] ?? 'default' ?>"><?= $status_labels[$a['status']] ?? $a['status'] ?></span></td>
                <td>
                  <?php if (!empty($a['host_user']) && !empty($a['host_pass'])): ?>
                    <form method="POST" action="<?= htmlspecialchars(shop_frontend_core_url('user/idcdl.php?gn=logine'), ENT_QUOTES) ?>" target="_blank" style="display:inline;">
                      <input type="hidden" name="username" value="<?= htmlspecialchars($a['host_user'], ENT_QUOTES) ?>">
                      <input type="hidden" name="password" value="<?= htmlspecialchars($a['host_pass'], ENT_QUOTES) ?>">
                      <button type="submit" class="sf-btn sf-btn-primary sf-btn-xs">一键登录</button>
                    </form>
                  <?php else: ?>
                    <span style="color:var(--sf-text-3);font-size:12px;">无登录信息</span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>

<script>
(function () {
  document.querySelectorAll('.sf-toggle-pass').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var span = this.parentNode.querySelector('.sf-pass-mask');
      if (!span) return;
      if (span.textContent === '********') {
        span.textContent = span.getAttribute('data-pass');
        span.classList.add('sf-pass-revealed');
      } else {
        span.textContent = '********';
        span.classList.remove('sf-pass-revealed');
      }
    });
  });
  document.querySelectorAll('.sf-copy-btn, .sf-copy-text').forEach(function (el) {
    el.addEventListener('click', function () {
      var text = el.getAttribute('data-copy');
      if (!text) return;
      if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(text).then(function () { alert('已复制：' + text); })
          .catch(function () { fallbackCopy(text); });
      } else {
        fallbackCopy(text);
      }
    });
  });
  function fallbackCopy(text) {
    var ta = document.createElement('textarea');
    ta.value = text;
    ta.style.position = 'fixed';
    ta.style.opacity = '0';
    document.body.appendChild(ta);
    ta.select();
    try { document.execCommand('copy'); alert('已复制：' + text); }
    catch (err) { alert('复制失败，请手动复制'); }
    document.body.removeChild(ta);
  }
})();
</script>
<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
