<?php
if (!defined('IN_CRONLITE')) { exit; }
mnbt_admin_include('head');
$fields = ['site_title','site_logo','site_primary','site_accent','site_hero','site_footer','site_favicon'];
$values = [];
foreach ($fields as $f) {
    $values[$f] = shop_frontend_option($f, '');
}
?>
<div class="container-fluid" style="padding:20px;">
<div class="card">
  <div class="card-header bg-info"><h4>售卖前端设置</h4></div>
  <div class="card-body">
    <div class="alert alert-info">配置 Vue SPA 售卖网站的显示内容。保存后需刷新前台页面。</div>
    <form id="settingsForm">
      <div class="form-group">
        <label>站点标题</label>
        <input class="form-control" name="site_title" value="<?=htmlspecialchars($values['site_title'])?>" placeholder="MNBT 主机售卖">
      </div>
      <div class="form-group">
        <label>站点 Logo URL</label>
        <input class="form-control" name="site_logo" value="<?=htmlspecialchars($values['site_logo'])?>" placeholder="https://... 留空则不显示">
      </div>
      <div class="row">
        <div class="col-md-6">
          <div class="form-group">
            <label>主色调</label>
            <input class="form-control" name="site_primary" value="<?=htmlspecialchars($values['site_primary'])?>" placeholder="#1867C0">
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group">
            <label>强调色</label>
            <input class="form-control" name="site_accent" value="<?=htmlspecialchars($values['site_accent'])?>" placeholder="#FF5722">
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
      <div class="form-group">
        <label>Favicon URL</label>
        <input class="form-control" name="site_favicon" value="<?=htmlspecialchars($values['site_favicon'])?>" placeholder="https://...">
      </div>
      <button type="button" class="btn btn-primary" onclick="saveSettings()">保存设置</button>
    </form>
  </div>
</div>
</div>

<script>
function saveSettings() {
    var data = {};
    $('#settingsForm input').each(function(){ data[$(this).attr('name')] = $(this).val(); });
    data.gn = 'shop_frontend_save_settings';
    $.post('./ajax.php', data, function(r){
        var j = JSON.parse(r);
        msalert(j.code === '保存成功' ? 1 : 4, j.code || '保存失败', 2000);
    });
}
</script>
</body>
</html>
