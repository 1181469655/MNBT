<?php
/**
 * TDesign 主题 theme.php
 * 注册 user 和 admin 两个 scope 的菜单渲染器
 */
if (!defined('IN_CRONLITE')) exit;

// 菜单渲染由 _spa_boot.php 中的 _tdboot_render_plugin_menu_html 函数处理
// theme.php 仅需注册渲染器占位,实际 HTML 生成在 _spa_boot.php 中完成

if (function_exists('mnbt_register_theme_menu_renderer')) {
	mnbt_register_theme_menu_renderer('user', function ($items) {
		// 渲染由 _spa_boot.php 处理,此处返回空字符串
		return '';
	});

	mnbt_register_theme_menu_renderer('admin', function ($items) {
		// 渲染由 _spa_boot.php 处理,此处返回空字符串
		return '';
	});
}

// 主页自定义设置字段（后台「前端模板 → 主页内容」自动渲染）
// 底部备案信息：显示在主页页脚,自动链接到工信部/公安备案查询
if (function_exists('mnbt_register_home_setting')) {
	mnbt_register_home_setting([
		'key'         => 'beian_info',
		'label'       => 'ICP 备案信息',
		'type'        => 'text',
		'default'     => '',
		'placeholder' => '如：京ICP备12345678号',
		'hint'        => '显示在主页页脚,自动添加工信部备案查询链接',
	]);

	mnbt_register_home_setting([
		'key'         => 'ps_beian',
		'label'       => '公安备案信息',
		'type'        => 'text',
		'default'     => '',
		'placeholder' => '如：京公网安备11010802020266号',
		'hint'        => '显示在主页页脚（可选）,自动添加全国公安网站备案查询链接',
	]);
}
