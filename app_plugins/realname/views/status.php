<?php
/**
 * 用户端 - 认证状态页
 */
if (!defined('IN_CRONLITE')) { exit; }
$page_title = '认证状态';
$auth = $auth ?? null;
ob_start();
$applyUrl = realname_url('realname/apply');
$imgUrl = realname_url('realname/api/img');
?>

<div class="rn-card rn-status-card">
  <div class="rn-card-head">
    <h2>认证状态</h2>
  </div>

  <?php if (!$auth): ?>
    <div class="rn-status-empty">
      <div class="rn-status-ico">&#128100;</div>
      <h3>尚未实名认证</h3>
      <p>完成实名认证后即可购买产品。</p>
      <a class="rn-btn" href="<?= htmlspecialchars($applyUrl) ?>">立即认证</a>
    </div>
  <?php elseif ($auth['status'] === 'approved'): ?>
    <div class="rn-status-approved">
      <div class="rn-status-ico rn-ico-ok">&#10004;</div>
      <h3>认证已通过</h3>
      <p>您已通过实名认证，可以正常购买产品。</p>
    </div>
    <div class="rn-info-table">
      <div class="rn-info-row"><span>姓名</span><b><?= htmlspecialchars(realname_mask_name($auth['real_name'])) ?></b></div>
      <div class="rn-info-row"><span>手机号</span><b><?= htmlspecialchars(realname_mask_phone($auth['phone'])) ?></b></div>
      <div class="rn-info-row"><span>身份证号</span><b><?= htmlspecialchars(realname_mask_idcard(realname_decrypt($auth['id_card']))) ?></b></div>
      <div class="rn-info-row"><span>认证时间</span><b><?= htmlspecialchars((string)$auth['audited_at']) ?></b></div>
    </div>
  <?php elseif ($auth['status'] === 'rejected'): ?>
    <div class="rn-status-rejected">
      <div class="rn-status-ico rn-ico-no">&#10007;</div>
      <h3>认证未通过</h3>
      <p>原因：<?= htmlspecialchars((string)$auth['audit_note']) ?></p>
      <a class="rn-btn" href="<?= htmlspecialchars($applyUrl) ?>">重新认证</a>
    </div>
  <?php else: ?>
    <div class="rn-status-pending">
      <div class="rn-status-ico rn-ico-wait">
        <span class="rn-spinner rn-spinner-lg"></span>
      </div>
      <h3>认证审核中</h3>
      <p>您的认证材料已提交，正在审核中，请耐心等待。</p>
      <p class="rn-sub">提交时间：<?= htmlspecialchars((string)$auth['created_at']) ?></p>
    </div>
  <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
