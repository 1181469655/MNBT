<?php
/**
 * Layui 主题初始化文件
 * 引擎会在首次解析本主题视图时自动加载（通过 mnbt_theme_ensure_loaded）。
 */
if (!defined('IN_CRONLITE')) {
	exit;
}

/**
 * 渲染插件菜单树为 Layui 主题的侧边栏结构。
 *
 * @param array $items 插件菜单树（已按 order 排序）
 * @return string
 */
mnbt_register_theme_menu_renderer('user', function ($items) {
	if (empty($items)) {
		return '';
	}
	$html = '';
	foreach ($items as $it) {
		$title = htmlspecialchars($it['title'] ?? '', ENT_QUOTES, 'UTF-8');
		$icon  = htmlspecialchars($it['icon'] ?? 'mdi-puzzle', ENT_QUOTES, 'UTF-8');
		if (!empty($it['children'])) {
			$childrenHtml = '';
			foreach ($it['children'] as $child) {
				$childTitle = htmlspecialchars($child['title'] ?? '', ENT_QUOTES, 'UTF-8');
				$childUrl   = htmlspecialchars($child['url'] ?? 'javascript:void(0)', ENT_QUOTES, 'UTF-8');
				$childIconHtml = '';
				if (!empty($child['icon'])) {
					$childIcon = htmlspecialchars($child['icon'], ENT_QUOTES, 'UTF-8');
					$childIconHtml = '<i class="mdi ' . $childIcon . '"></i> ';
				}
				$mt = !empty($child['multitabs']) || strpos($childUrl, 'plugin.php') !== false ? ' multitabs' : '';
				$childrenHtml .= '<li><a href="' . $childUrl . '" class="' . trim($mt) . '">' . $childIconHtml . $childTitle . '</a></li>';
			}
			$html .= '<li class="ly-menu-item ly-submenu">'
				. '<a href="javascript:;"><i class="mdi ' . $icon . '"></i><span>' . $title . '</span><i class="mdi mdi-chevron-right ly-arrow"></i></a>'
				. '<ul class="ly-submenu-list">' . $childrenHtml . '</ul></li>';
		} else {
			$url = htmlspecialchars($it['url'] ?? 'javascript:void(0)', ENT_QUOTES, 'UTF-8');
			$mt = !empty($it['multitabs']) || strpos($url, 'plugin.php') !== false ? ' multitabs' : '';
			$html .= '<li class="ly-menu-item">'
				. '<a href="' . $url . '" class="' . trim($mt) . '"><i class="mdi ' . $icon . '"></i><span>' . $title . '</span></a></li>';
		}
	}
	return $html;
});
