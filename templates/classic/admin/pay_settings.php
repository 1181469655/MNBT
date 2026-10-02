<?php mnbt_admin_include('head'); ?>
<?php header("Cache-Control: no-cache, must-revalidate"); ?>
<link rel="stylesheet" href="<?=mnbt_theme_asset('set-page.css', 'admin')?>">
<link rel="stylesheet" href="<?=mnbt_theme_asset('pay-settings.css', 'admin')?>">

<div class="mn-set-page">
<?php
// 已注册的支付插件
$payment_plugins = function_exists('mnbt_get_payment_plugins') ? mnbt_get_payment_plugins() : [];
// 当前已启用的付款方式
$enabled_methods = function_exists('mnbt_get_enabled_payment_methods') ? mnbt_get_enabled_payment_methods() : [];
// 所有插件设置页签（用于检测插件是否提供设置页）
$settings_tabs = function_exists('mnbt_plugin_settings_tabs') ? mnbt_plugin_settings_tabs() : [];

// 把已启用方式按 plugin__method 做成查找表
$enabled_map = [];
foreach ($enabled_methods as $row) {
    $key = $row['plugin'] . '__' . $row['method'];
    $enabled_map[$key] = $row;
}

// 检测某插件是否有设置页
$plugin_has_settings = function ($plugin_id) use ($settings_tabs) {
    foreach ($settings_tabs as $tab) {
        if (($tab['plugin'] ?? '') === $plugin_id && !empty($tab['url'])) {
            return $tab['url'];
        }
    }
    return '';
};
?>

<div class="mn-set-card">
  <div class="mn-set-card-hd">
    <div class="mn-set-icon"><i class="mdi mdi-credit-card-outline"></i></div>
    <div>
      <h4>支付方式设置</h4>
      <p>启用支付插件提供的付款方式，并设置客户端显示名</p>
    </div>
  </div>
  <div class="mn-set-card-bd">
    <div class="mn-pay-note">
      <b>使用说明：</b>
      <ol class="mb-0 mt-1">
        <li>在 <a href="plugin.php" class="alert-link">插件管理</a> 中安装并启用所需支付插件（如易支付、支付宝官方等）。</li>
        <li>支付插件的 API 凭证（商户号、密钥等）请在<b>插件自身的设置页</b>中配置，本页面仅负责启用与显示。</li>
        <li>勾选要启用的子付款方式，设置客户端显示名、图标与排序（数字越小越靠前），保存后立即生效。</li>
      </ol>
    </div>
  </div>
</div>

<?php if (empty($payment_plugins)): ?>
<div class="mn-set-card">
  <div class="mn-set-card-bd text-center text-muted py-5">
    <i class="mdi mdi-package-variant-closed" style="font-size:48px;color:#cbd5e1"></i>
    <h5 class="mt-3 mb-1">暂无可用支付插件</h5>
    <p class="text-muted mb-3">请先安装并启用至少一个支付插件，然后返回本页面配置付款方式。</p>
    <a href="plugin.php" class="btn btn-outline-primary"><i class="mdi mdi-puzzle"></i> 前往插件管理</a>
  </div>
</div>
<?php else: ?>

<form id="payMethodsForm" autocomplete="off">
<?php foreach ($payment_plugins as $pid => $pinfo): ?>
<?php
    $plugin_name = isset($pinfo['name']) ? $pinfo['name'] : $pid;
    $plugin_desc = isset($pinfo['description']) ? $pinfo['description'] : '';
    $methods = isset($pinfo['methods']) && is_array($pinfo['methods']) ? $pinfo['methods'] : [];
    $settings_url = $plugin_has_settings($pid);
?>
<div class="mn-set-card mn-pay-plugin-card" data-plugin="<?=htmlspecialchars($pid)?>">
  <div class="mn-set-card-hd">
    <div class="mn-set-icon"><i class="mdi <?=htmlspecialchars(isset($pinfo['icon']) ? $pinfo['icon'] : 'mdi-credit-card-multiple')?>"></i></div>
    <div class="mn-pay-plugin-title">
      <h4><?=htmlspecialchars($plugin_name)?>
        <code class="mn-pay-slug"><?=htmlspecialchars($pid)?></code>
      </h4>
      <p><?=htmlspecialchars($plugin_desc)?></p>
    </div>
    <?php if ($settings_url): ?>
    <a href="<?=htmlspecialchars($settings_url)?>" class="btn btn-sm btn-outline-secondary mn-pay-settings-btn">
      <i class="mdi mdi-cog-outline"></i> 插件设置
    </a>
    <?php endif; ?>
  </div>
  <div class="mn-set-card-bd">
    <?php if (empty($methods)): ?>
    <div class="text-muted py-2">该插件未声明任何付款方式</div>
    <?php else: ?>
    <div class="table-responsive">
      <table class="table mn-set-table mn-pay-methods-table">
        <thead>
          <tr>
            <th style="width:56px">启用</th>
            <th>子付款方式</th>
            <th style="width:240px">客户端显示名</th>
            <th style="width:200px">图标 class</th>
            <th style="width:90px">排序</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($methods as $mid => $minfo): ?>
          <?php
            $type_key = $pid . '__' . $mid;
            $enabled = isset($enabled_map[$type_key]);
            $cur = $enabled ? $enabled_map[$type_key] : [];
            $default_name = isset($minfo['name']) ? $minfo['name'] : $mid;
            $default_icon = isset($minfo['icon']) ? $minfo['icon'] : 'mdi-payment';
            $display_name = $enabled && isset($cur['display_name']) && $cur['display_name'] !== '' ? $cur['display_name'] : $default_name;
            $icon = $enabled && isset($cur['icon']) && $cur['icon'] !== '' ? $cur['icon'] : $default_icon;
            $sort = $enabled && isset($cur['sort']) ? (int)$cur['sort'] : 99;
          ?>
          <tr data-type="<?=htmlspecialchars($type_key)?>">
            <td class="text-center">
              <div class="custom-control custom-switch">
                <input type="checkbox" class="custom-control-input pay-method-enable" id="pm_<?=htmlspecialchars($pid)?>_<?=htmlspecialchars($mid)?>" data-plugin="<?=htmlspecialchars($pid)?>" data-method="<?=htmlspecialchars($mid)?>" <?=$enabled ? 'checked' : ''?>>
                <label class="custom-control-label" for="pm_<?=htmlspecialchars($pid)?>_<?=htmlspecialchars($mid)?>"></label>
              </div>
            </td>
            <td>
              <i class="mdi <?=htmlspecialchars($default_icon)?> mr-1"></i>
              <strong><?=htmlspecialchars($default_name)?></strong>
              <code class="ml-1"><?=htmlspecialchars($mid)?></code>
            </td>
            <td>
              <input type="text" class="form-control form-control-sm pay-method-name" value="<?=htmlspecialchars($display_name)?>" placeholder="<?=htmlspecialchars($default_name)?>" maxlength="40">
            </td>
            <td>
              <input type="text" class="form-control form-control-sm pay-method-icon" value="<?=htmlspecialchars($icon)?>" placeholder="mdi-payment" maxlength="60">
            </td>
            <td>
              <input type="number" class="form-control form-control-sm pay-method-sort" value="<?=$sort?>" min="0" max="999" style="text-align:center">
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>
</div>
<?php endforeach; ?>

<div class="mn-set-card">
  <div class="mn-set-card-bd">
    <div class="mn-set-actions" style="border-top:0;padding-top:0;margin-top:0">
      <button class="btn btn-primary btn-block" type="button" onclick="savePayMethods()"><i class="mdi mdi-content-save-outline"></i> 保存支付方式配置</button>
    </div>
  </div>
</div>
</form>

<?php endif; ?>
</div>

<script type="text/javascript">
function savePayMethods() {
    var rows = [];
    var sortCounter = 1;
    $('#payMethodsForm .pay-method-enable').each(function () {
        if (!this.checked) return;
        var $tr = $(this).closest('tr');
        var plugin = $(this).data('plugin');
        var method = $(this).data('method');
        var name = $.trim($tr.find('.pay-method-name').val());
        var icon = $.trim($tr.find('.pay-method-icon').val());
        var sort = parseInt($tr.find('.pay-method-sort').val(), 10);
        if (isNaN(sort) || sort < 0) sort = 99;
        rows.push({
            plugin: plugin,
            method: method,
            display_name: name,
            icon: icon,
            sort: sort
        });
    });
    msloading('正在保存支付方式配置...','text-info','text-info');
    var data = {};
    data['gn'] = 'setpaymethods';
    data['methods'] = JSON.stringify(rows);
    $.post('./ajax.php', data, function (date) {
        var jsoe = JSON.parse(date);
        var qk = jsoe.code;
        if (qk == '修改成功') {
            msalert(1, '保存成功！共启用 ' + rows.length + ' 个付款方式', 2000);
            msloadingde();
        } else {
            msalert(4, qk, 3000);
            msloadingde();
        }
    }).fail(function () {
        msalert(4, '网络错误，保存失败', 3000);
        msloadingde();
    });
}
</script>
