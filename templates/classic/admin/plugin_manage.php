<?php mnbt_admin_include('head'); ?>
<script type="text/javascript" src="<?=mnbt_asset_url('js/jquery-confirm/jquery-confirm.min.js')?>"></script>
<link href="<?=mnbt_asset_url('js/jquery-confirm/jquery-confirm.min.css')?>" rel="stylesheet">

<div class="container-fluid p-t-15">
<?php if (!empty($plugin_error)): ?>
  <div class="alert alert-warning"><?=htmlspecialchars($plugin_error, ENT_QUOTES, 'UTF-8')?></div>
<?php endif; ?>
<?php
$settingsTabs = function_exists('mnbt_plugin_settings_tabs') ? mnbt_plugin_settings_tabs() : [];
if ($settingsTabs):
?>
  <div class="card mb-3">
    <div class="card-header"><h4>已启用插件设置</h4></div>
    <div class="card-body">
      <?php foreach ($settingsTabs as $tab): ?>
        <a class="btn btn-outline-primary btn-sm m-1 multitabs" href="<?=htmlspecialchars($tab['url'] ?? '#', ENT_QUOTES, 'UTF-8')?>">
          <?=htmlspecialchars($tab['title'] ?? '', ENT_QUOTES, 'UTF-8')?>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
<?php endif; ?>
  <div class="card">
    <div class="card-header">
      <h4>插件管理</h4>
      <small class="text-muted">插件目录：app_plugins/{标识}/ ，需包含 plugin.json 与 bootstrap.php</small>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-bordered table-hover" id="plugin-table">
          <thead>
            <tr>
              <th>标识</th>
              <th>名称</th>
              <th>版本</th>
              <th>作者</th>
              <th>状态</th>
              <th>说明</th>
              <th style="width:220px">操作</th>
            </tr>
          </thead>
          <tbody id="plugin-tbody">
            <tr><td colspan="7" class="text-center text-muted">加载中…</td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<script>
function loadPlugins() {
  $.post('ajax.php', {gn: 'plugin_list'}, function (res) {
    var data;
    try { data = typeof res === 'string' ? JSON.parse(res) : res; } catch (e) { data = null; }
    var rows = (data && data.rows) ? data.rows : [];
    var html = '';
    if (!rows.length) {
      html = '<tr><td colspan="7" class="text-center text-muted">未发现插件。请将插件放到 app_plugins/ 目录。</td></tr>';
    } else {
      rows.forEach(function (r) {
        var st = r.enabled ? '<span class="badge badge-success">已启用</span>' : (r.installed ? '<span class="badge badge-secondary">已安装</span>' : '<span class="badge badge-light">未安装</span>');
        html += '<tr>';
        html += '<td><code>' + $('<div>').text(r.slug).html() + '</code></td>';
        html += '<td>' + $('<div>').text(r.name || '').html() + '</td>';
        html += '<td>' + $('<div>').text(r.version || '').html() + '</td>';
        html += '<td>' + $('<div>').text(r.author || '-').html() + '</td>';
        html += '<td>' + st + '</td>';
        html += '<td class="small">' + $('<div>').text(r.description || '').html() + '</td>';
        html += '<td>';
        if (!r.installed) {
          html += '<button type="button" class="btn btn-sm btn-primary m-1" data-act="install" data-slug="' + r.slug + '">安装</button>';
        }
        if (r.enabled) {
          html += '<button type="button" class="btn btn-sm btn-warning m-1" data-act="disable" data-slug="' + r.slug + '">禁用</button>';
        } else {
          html += '<button type="button" class="btn btn-sm btn-success m-1" data-act="enable" data-slug="' + r.slug + '">启用</button>';
        }
        if (r.installed) {
          html += '<button type="button" class="btn btn-sm btn-outline-danger m-1" data-act="uninstall" data-slug="' + r.slug + '">卸载</button>';
        }
        html += '</td></tr>';
      });
    }
    $('#plugin-tbody').html(html);
  }).fail(function () {
    $('#plugin-tbody').html('<tr><td colspan="7" class="text-danger text-center">加载失败</td></tr>');
  });
}

function pluginAct(act, slug) {
  var gn = 'plugin_enable';
  var post = {slug: slug};
  if (act === 'install') {
    gn = 'plugin_install';
  } else if (act === 'uninstall') {
    gn = 'plugin_uninstall';
  } else if (act === 'enable') {
    post.enabled = 'true';
  } else if (act === 'disable') {
    post.enabled = 'false';
  }
  post.gn = gn;
  $.post('ajax.php', post, function (res) {
    var data;
    try { data = typeof res === 'string' ? JSON.parse(res) : res; } catch (e) { data = {code: res}; }
    var ok = data && (data.qk == 1 || data.success === true || (data.code && String(data.code).indexOf('成功') >= 0) || (data.code && String(data.code).indexOf('启用') >= 0) || (data.code && String(data.code).indexOf('禁用') >= 0) || (data.code && String(data.code).indexOf('安装') >= 0) || (data.code && String(data.code).indexOf('卸载') >= 0));
    if (typeof $.notify === 'function') {
      $.notify({message: data.msg || data.code || '完成'}, {type: ok ? 'success' : 'danger'});
    } else {
      alert(data.msg || data.code || '完成');
    }
    loadPlugins();
  });
}

$(function () {
  loadPlugins();
  $('#plugin-tbody').on('click', 'button[data-act]', function () {
    var act = $(this).data('act');
    var slug = $(this).data('slug');
    if (act === 'uninstall') {
      if (!confirm('确定卸载插件 ' + slug + ' ？将删除数据库记录与插件配置，文件仍保留。')) return;
    }
    pluginAct(act, slug);
  });
});
</script>
