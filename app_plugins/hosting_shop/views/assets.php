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
      <table class="ly-table">
        <thead><tr><th>套餐</th><th>主机账号</th><th>节点</th><th>开通时间</th><th>到期时间</th><th>状态</th></tr></thead>
        <tbody>
          <?php foreach ($assets as $a): ?>
            <tr>
              <td><?= htmlspecialchars($a['plan_name']) ?></td>
              <td class="ly-mono"><?= htmlspecialchars($a['host_user'] ?? '-') ?></td>
              <td><?= htmlspecialchars($a['ssbt'] ?? '-') ?></td>
              <td><?= htmlspecialchars($a['created_at']) ?></td>
              <td><?= htmlspecialchars($a['expire_at']) ?></td>
              <td><span class="ly-status ly-status-<?= $a['status'] ?>"><?= $status_labels[$a['status']] ?? $a['status'] ?></span></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
