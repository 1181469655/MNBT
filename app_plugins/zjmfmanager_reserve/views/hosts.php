<?php
/**
 * 用户端 - 我的主机列表
 */
if (!defined('IN_CRONLITE')) {
	exit;
}
$page_title = $page_title ?? '我的主机';
$hosts = $hosts ?? [];
ob_start();
?>
<div class="zj-section">
  <h1>我的主机</h1>
  <p>查看已开通的主机，点击进入详情可执行状态/流量查询与操作</p>
</div>

<?php if (empty($hosts)): ?>
  <div class="layui-card">
    <div class="layui-card-body" style="text-align:center;padding:40px;color:#999;">
      暂无主机，<a href="<?= zjmf_url('reserve/shop') ?>">去选购商品</a>
    </div>
  </div>
<?php else: ?>
  <div class="layui-card">
    <div class="layui-card-body" style="padding:0;">
      <table class="zj-table">
        <thead>
          <tr>
            <th>主机</th>
            <th>供应商</th>
            <th>状态</th>
            <th>用户名</th>
            <th>周期</th>
            <th>到期时间</th>
            <th>操作</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($hosts as $host): ?>
            <tr>
              <td>
                <b><?= htmlspecialchars($host['name']) ?></b>
                <div class="zj-muted">ID <?= (int)$host['id'] ?>
                  / 上游 <?= (int)$host['up_host_id'] ?></div>
              </td>
              <td><?= htmlspecialchars($host['supplier_name'] ?: '-') ?></td>
              <td>
                <span class="zj-status zj-status-<?= htmlspecialchars($host['status']) ?>">
                  <?= htmlspecialchars(zjmf_host_status_label($host['status'])) ?>
                </span>
              </td>
              <td class="zj-mono"><?= htmlspecialchars(zjmf_mask_account($host['username'])) ?></td>
              <td><?= htmlspecialchars($host['cycle'] ?: '-') ?></td>
              <td><?= htmlspecialchars($host['renew_date'] ?: '-') ?></td>
              <td>
                <a class="layui-btn layui-btn-xs" href="<?= zjmf_url('reserve/hosts/' . (int)$host['id']) ?>">详情</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
<?php endif; ?>

<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
