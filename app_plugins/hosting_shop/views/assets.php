<?php
if (!defined('IN_CRONLITE')) { exit; }
$page_title = $page_title ?? '我的主机';
$assets = $assets ?? [];
$status_labels = ['active'=>'正常','expired'=>'已到期','cancelled'=>'已取消'];
ob_start();
?>
<div class="hs-section"><h1>我的主机</h1><p>已开通的虚拟主机资产</p></div>

<div class="layui-card">
  <div class="layui-card-body" style="padding:0;">
    <?php if (empty($assets)): ?>
      <p style="text-align:center;padding:40px;color:#999;">您还没有开通的主机，<a href="<?= hosting_url('shop') ?>" style="color:#1e9fff;">去购买</a></p>
    <?php else: ?>
      <table class="ly-table hs-asset-table">
        <thead><tr><th>套餐</th><th>主机账号</th><th>控制面板密码</th><th>节点</th><th>到期时间</th><th>状态</th><th>操作</th></tr></thead>
        <tbody>
          <?php foreach ($assets as $a): ?>
            <?php
              $panelUrl = '';
              if (!empty($a['btip']) && !empty($a['btdk'])) {
                $panelUrl = (($a['ptl'] ?? '') === 'true' ? 'https' : 'http') . '://' . $a['btip'] . ':' . $a['btdk'];
              }
            ?>
            <tr>
              <td><?= htmlspecialchars($a['plan_name']) ?></td>
              <td class="ly-mono"><span class="hs-copy-text" data-copy="<?= htmlspecialchars($a['host_user'] ?? '', ENT_QUOTES) ?>"><?= htmlspecialchars($a['host_user'] ?? '-') ?></span></td>
              <td class="ly-mono">
                <?php if (!empty($a['host_pass'])): ?>
                  <span class="hs-pass-mask" data-pass="<?= htmlspecialchars($a['host_pass'], ENT_QUOTES) ?>">********</span>
                  <button type="button" class="hs-icon-btn hs-toggle-pass" title="显示/隐藏密码">👁</button>
                  <button type="button" class="hs-icon-btn hs-copy-btn" data-copy="<?= htmlspecialchars($a['host_pass'], ENT_QUOTES) ?>" title="复制密码">📋</button>
                <?php else: ?>
                  -
                <?php endif; ?>
              </td>
              <td><?= htmlspecialchars($a['ssbt'] ?? '-') ?></td>
              <td><?= htmlspecialchars($a['expire_at']) ?></td>
              <td><span class="ly-status ly-status-<?= $a['status'] ?>"><?= $status_labels[$a['status']] ?? $a['status'] ?></span></td>
              <td>
                <?php if ($panelUrl): ?>
                  <a href="<?= htmlspecialchars($panelUrl, ENT_QUOTES) ?>" target="_blank" class="layui-btn layui-btn-xs" rel="noopener noreferrer">打开面板</a>
                <?php else: ?>
                  <span style="color:#999;font-size:12px;">无节点信息</span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>
</div>

<script>
(function(){
  document.querySelectorAll('.hs-toggle-pass').forEach(function(btn){
    btn.addEventListener('click', function(){
      var span = this.parentNode.querySelector('.hs-pass-mask');
      if (!span) return;
      if (span.textContent === '********') {
        span.textContent = span.getAttribute('data-pass');
        span.classList.add('revealed');
      } else {
        span.textContent = '********';
        span.classList.remove('revealed');
      }
    });
  });
  document.querySelectorAll('.hs-copy-btn, .hs-copy-text').forEach(function(el){
    el.addEventListener('click', function(){
      var text = this.getAttribute('data-copy');
      if (!text) return;
      if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(text).then(function(){
          alert('已复制：' + text);
        }).catch(function(){
          fallbackCopy(text);
        });
      } else {
        fallbackCopy(text);
      }
    });
  });
  function fallbackCopy(text){
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
