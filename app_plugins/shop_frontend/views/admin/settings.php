<?php
if (!defined('IN_CRONLITE')) { exit; }
mnbt_admin_include('head');
$fields = ['site_title','site_logo','site_primary','site_accent','site_hero','site_footer','site_favicon'];
$values = [];
foreach ($fields as $f) {
    $values[$f] = shop_frontend_option($f, '');
}
// 颜色只接受 #rrggbb；未设置时给默认值（主色默认蓝色）
$primary = preg_match('/^#[0-9a-fA-F]{6}$/', (string)$values['site_primary']) ? strtolower($values['site_primary']) : '#4f46e5';
$accent  = preg_match('/^#[0-9a-fA-F]{6}$/', (string)$values['site_accent'])  ? strtolower($values['site_accent'])  : '#ff5722';
?>
<div class="container-fluid" style="padding:20px;">
<div class="card">
  <div class="card-header bg-info"><h4>售卖前端设置</h4></div>
  <div class="card-body">
    <div class="alert alert-info">配置售卖网站首页与用户端页面的显示内容。保存后刷新前台页面即可生效。</div>
    <form id="settingsForm">
      <div class="form-group">
        <label>站点标题</label>
        <input class="form-control" name="site_title" value="<?=htmlspecialchars($values['site_title'])?>" placeholder="MNBT 主机售卖">
      </div>
      <div class="form-group">
        <label>站点 Logo（ICO，可上传）</label>
        <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
          <img id="logo_preview" src="<?=htmlspecialchars($values['site_logo'])?>" alt="logo" style="width:32px;height:32px;object-fit:contain;border:1px solid #e5e7eb;border-radius:6px;background:#fff;<?= $values['site_logo'] ? '' : 'display:none;' ?>">
          <input type="file" id="logo_file" accept=".ico,image/vnd.microsoft.icon,image/x-icon">
          <button type="button" class="btn btn-primary" id="logo_upload_btn">上传 ICO</button>
          <button type="button" class="btn btn-default" id="logo_clear_btn">移除 Logo</button>
        </div>
        <div style="margin-top:8px;">
          <input type="text" class="form-control" name="site_logo" id="logo_value" value="<?=htmlspecialchars($values['site_logo'])?>" placeholder="上传后自动填入，或手动填写 Logo URL">
        </div>
        <small class="text-muted">仅支持 .ico 文件（2MB 内）；上传后应用到首页顶栏与全部用户端页面</small>
      </div>
      <div class="form-group">
        <label>站点 Favicon（ICO，可上传）</label>
        <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
          <img id="favicon_preview" src="<?=htmlspecialchars($values['site_favicon'])?>" alt="favicon" style="width:24px;height:24px;object-fit:contain;border:1px solid #e5e7eb;border-radius:4px;background:#fff;<?= $values['site_favicon'] ? '' : 'display:none;' ?>">
          <input type="file" id="favicon_file" accept=".ico,image/vnd.microsoft.icon,image/x-icon">
          <button type="button" class="btn btn-primary" id="favicon_upload_btn">上传 ICO</button>
          <button type="button" class="btn btn-default" id="favicon_clear_btn">移除 Favicon</button>
        </div>
        <div style="margin-top:8px;">
          <input type="text" class="form-control" name="site_favicon" id="favicon_value" value="<?=htmlspecialchars($values['site_favicon'])?>" placeholder="上传后自动填入，或手动填写 Favicon URL">
        </div>
        <small class="text-muted">仅支持 .ico 文件（2MB 内）；应用到浏览器标签页图标</small>
      </div>
      <div class="row">
        <div class="col-md-6">
          <div class="form-group">
            <label>主色调 <small class="text-muted">（默认蓝色）</small></label>
            <div style="display:flex;align-items:center;gap:10px;">
              <input type="color" name="site_primary" id="site_primary" value="<?=htmlspecialchars($primary)?>" style="width:46px;height:34px;padding:2px;border:1px solid #ced4da;border-radius:4px;background:#fff;cursor:pointer;">
              <input type="text" class="form-control" id="site_primary_hex" value="<?=htmlspecialchars($primary)?>" readonly style="max-width:110px;">
            </div>
            <small class="text-muted">点击色盘选择，应用到首页与全部用户端页面</small>
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group">
            <label>强调色</label>
            <div style="display:flex;align-items:center;gap:10px;">
              <input type="color" name="site_accent" id="site_accent" value="<?=htmlspecialchars($accent)?>" style="width:46px;height:34px;padding:2px;border:1px solid #ced4da;border-radius:4px;background:#fff;cursor:pointer;">
              <input type="text" class="form-control" id="site_accent_hex" value="<?=htmlspecialchars($accent)?>" readonly style="max-width:110px;">
            </div>
            <small class="text-muted">首页强调元素使用</small>
          </div>
        </div>
      </div>
      <div class="form-group">
        <label>首页 Hero 标语</label>
        <input class="form-control" name="site_hero" value="<?=htmlspecialchars($values['site_hero'])?>" placeholder="高性能虚拟主机，即买即用">
      </div>
      <div class="form-group">
        <label>底部版权</label>
        <input class="form-control" name="site_footer" value="<?=htmlspecialchars($values['site_footer'])?>" placeholder="© 2026 MNBT. All rights reserved.">
      </div>
      <button type="button" class="btn btn-primary" onclick="saveSettings()">保存设置</button>
    </form>
  </div>
</div>
</div>

<script>
document.getElementById('site_primary').addEventListener('input', function () { document.getElementById('site_primary_hex').value = this.value; });
document.getElementById('site_accent').addEventListener('input', function () { document.getElementById('site_accent_hex').value = this.value; });

function uploadIcon(target) {
    var input = document.getElementById(target + '_file');
    if (!input.files || !input.files.length) { alert('请先选择 .ico 文件'); return; }
    var file = input.files[0];
    if (!/\.ico$/i.test(file.name)) { alert('仅支持 ICO 格式'); return; }
    var fd = new FormData();
    fd.append('gn', 'shop_frontend_upload_icon');
    fd.append('target', target);
    fd.append('icon', file);
    $.ajax({
        url: './ajax.php',
        type: 'POST',
        data: fd,
        processData: false,
        contentType: false,
        success: function (r) {
            var j;
            try { j = typeof r === 'string' ? JSON.parse(r) : r; } catch (e) { j = { code: '响应解析失败' }; }
            if (j.url) {
                $('#' + target + '_value').val(j.url);
                $('#' + target + '_preview').attr('src', j.url).show();
                msalert(1, j.code || '上传成功', 2000);
            } else {
                msalert(4, j.code || '上传失败', 2000);
            }
        },
        error: function () { msalert(4, '网络错误，请重试', 2000); }
    });
}

$('#logo_upload_btn').on('click', function () { uploadIcon('logo'); });
$('#favicon_upload_btn').on('click', function () { uploadIcon('favicon'); });
$('#logo_clear_btn').on('click', function () { $('#logo_value').val(''); $('#logo_preview').hide(); });
$('#favicon_clear_btn').on('click', function () { $('#favicon_value').val(''); $('#favicon_preview').hide(); });

function saveSettings() {
    var data = {};
    $('#settingsForm input').each(function(){ if ($(this).attr('name')) data[$(this).attr('name')] = $(this).val(); });
    data.gn = 'shop_frontend_save_settings';
    $.post('./ajax.php', data, function(r){
        var j = JSON.parse(r);
        msalert(j.code === '保存成功' ? 1 : 4, j.code || '保存失败', 2000);
    });
}
</script>
</body>
</html>
