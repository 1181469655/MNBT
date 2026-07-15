<?php
if ($egn == 'plugin_list') {
	mnbt_plugin_require_admin();
	$list = mnbt_plugin_list();
	exit(json_encode(['total' => count($list), 'rows' => $list], JSON_UNESCAPED_UNICODE));
}
if ($egn == 'plugin_enable') {
	mnbt_plugin_require_admin();
	$slug = daddslashes($_POST['slug'] ?? '');
	$on = ($_POST['enabled'] ?? 'true') === 'true' || ($_POST['enabled'] ?? '') === '1';
	$r = mnbt_plugin_set_enabled($slug, $on);
	if ($r === true) {
		if (function_exists('mnbt_log')) {
			mnbt_log($user ?? 'admin', '插件管理', ($on ? '启用' : '禁用') . $slug, '成功', $DB);
		}
		json_exit_success($on ? '已启用' : '已禁用');
	}
	json_exit_error(is_string($r) ? $r : '操作失败');
}
if ($egn == 'plugin_install') {
	mnbt_plugin_require_admin();
	$slug = daddslashes($_POST['slug'] ?? '');
	$r = mnbt_plugin_install($slug);
	if ($r === true) {
		if (function_exists('mnbt_log')) {
			mnbt_log($user ?? 'admin', '插件管理', '安装' . $slug, '成功', $DB);
		}
		json_exit_success('安装成功');
	}
	json_exit_error(is_string($r) ? $r : '安装失败');
}
if ($egn == 'plugin_uninstall') {
	mnbt_plugin_require_admin();
	$slug = daddslashes($_POST['slug'] ?? '');
	$r = mnbt_plugin_uninstall($slug);
	if ($r === true) {
		if (function_exists('mnbt_log')) {
			mnbt_log($user ?? 'admin', '插件管理', '卸载' . $slug, '成功', $DB);
		}
		json_exit_success('已卸载（文件仍保留，可重新安装）');
	}
	json_exit_error(is_string($r) ? $r : '卸载失败');
}
