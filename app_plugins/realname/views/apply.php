<?php
/**
 * 用户端 - 实名认证申请页
 */
if (!defined('IN_CRONLITE')) { exit; }
$page_title = '实名认证';
$auth = $auth ?? null;
ob_start();
$submitUrl = realname_url('realname/api/submit');
$ocrUrl = realname_asset_url('ocr/tesseract.min.js');
?>

<div class="rn-card rn-apply-card">
  <div class="rn-card-head">
    <h2>实名认证</h2>
    <p>根据平台要求，购买产品前需完成实名认证。信息全程本地识别，不经过第三方接口。</p>
  </div>

  <?php if ($auth && $auth['status'] === 'rejected'): ?>
    <div class="rn-alert rn-alert-danger">
      上次认证未通过：<?= htmlspecialchars((string)$auth['audit_note']) ?>，请核对后重新提交。
    </div>
  <?php endif; ?>

  <?php if ($auth && $auth['status'] === 'pending'): ?>
    <div class="rn-alert rn-alert-warn">
      当前认证正在人工复核中，请耐心等待。可到「认证状态」页查看进度。
    </div>
  <?php endif; ?>

  <form id="rnForm" class="rn-form" autocomplete="off">
    <?= function_exists('mnbt_csrf_field') ? mnbt_csrf_field() : '' ?>
    <div class="rn-field">
      <label>姓名 <span class="rn-required">*</span></label>
      <input type="text" id="rnRealName" name="real_name" maxlength="20" placeholder="与身份证一致的真实姓名" required>
      <div class="rn-hint">将用于 OCR 自动比对（识别失败时转人工审核）</div>
    </div>

    <div class="rn-field">
      <label>手机号 <span class="rn-required">*</span></label>
      <input type="text" id="rnPhone" name="phone" maxlength="11" placeholder="请输入 11 位手机号" required>
    </div>

    <div class="rn-field">
      <label>身份证号 <span class="rn-required">*</span></label>
      <input type="text" id="rnIdCard" name="id_card" maxlength="18" placeholder="18 位身份证号（上传正面照片后将自动识别填入）" required>
    </div>

    <div class="rn-uploads">
      <div class="rn-upload-item">
        <div class="rn-upload-box" id="rnFrontBox">
          <input type="file" id="rnFront" accept="image/jpeg,image/png" data-type="front">
          <div class="rn-upload-inner">
            <span class="rn-upload-ico">&#128196;</span>
            <span>身份证正面</span>
            <small>点击上传 · 识别姓名与身份证号</small>
          </div>
          <img class="rn-upload-preview" alt="">
          <button type="button" class="rn-upload-clear">&times;</button>
        </div>
        <div class="rn-ocr-state" id="rnOcrState" style="display:none">
          <span class="rn-spinner"></span> 正在本地识别身份证正面…
        </div>
      </div>
      <div class="rn-upload-item">
        <div class="rn-upload-box" id="rnBackBox">
          <input type="file" id="rnBack" accept="image/jpeg,image/png" data-type="back">
          <div class="rn-upload-inner">
            <span class="rn-upload-ico">&#128196;</span>
            <span>身份证反面</span>
            <small>点击上传</small>
          </div>
          <img class="rn-upload-preview" alt="">
          <button type="button" class="rn-upload-clear">&times;</button>
        </div>
      </div>
      <div class="rn-upload-item">
        <div class="rn-upload-box" id="rnHandBox">
          <input type="file" id="rnHand" accept="image/jpeg,image/png" data-type="hand">
          <div class="rn-upload-inner">
            <span class="rn-upload-ico">&#128247;</span>
            <span>手持身份证</span>
            <small>面部与证件清晰可见</small>
          </div>
          <img class="rn-upload-preview" alt="">
          <button type="button" class="rn-upload-clear">&times;</button>
        </div>
      </div>
    </div>

    <div class="rn-ocr-result" id="rnOcrResult" style="display:none">
      <div class="rn-ocr-result-title">&#10003; 已自动识别（请核对，可手动修改）</div>
      <div class="rn-ocr-result-row"><span>识别姓名</span><b id="rnOcrName"></b></div>
      <div class="rn-ocr-result-row"><span>识别身份证号</span><b id="rnOcrIdCard"></b></div>
    </div>

    <div class="rn-form-tip">
      上传即表示本人已阅读并同意《实名认证服务协议》，保证所提交身份信息真实、合法、有效。
    </div>

    <button type="submit" class="rn-btn rn-btn-block" id="rnSubmit">提交认证</button>
  </form>
</div>

<input type="hidden" id="rnOcrNameVal" value="">
<input type="hidden" id="rnOcrIdCardVal" value="">

<script>
window.RN_OCR_URL = <?= json_encode($ocrUrl) ?>;
window.RN_OCR_LANG_URL = <?= json_encode(realname_asset_url('ocr/lang/')) ?>;
window.RN_OCR_WORKER_URL = <?= json_encode(realname_asset_url('ocr/worker.min.js')) ?>;
window.RN_OCR_CORE_URL = <?= json_encode(realname_asset_url('ocr/tesseract-core.wasm.js')) ?>;
window.RN_SUBMIT_URL = <?= json_encode($submitUrl) ?>;
window.RN_STATUS_URL = <?= json_encode(realname_url('realname/status')) ?>;
</script>
<script src="<?= realname_asset_url('realname.js') ?>"></script>

<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
