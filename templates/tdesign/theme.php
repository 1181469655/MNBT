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
