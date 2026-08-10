<?php
/**
 * official_site 插件 - 主入口
 *
 * 功能：企业官网内容管理（产品展示、新闻资讯、联系留言）
 * 架构：
 *   - 前台：通过 P2 路由注册 /site/api/* 数据接口，供 home SPA「产品/新闻/联系」页面使用
 *   - 管理员端：通过 mnbt_register_page('admin', ...) 注册到 admin/plugin.php，侧边栏「官网内容」菜单
 * 能力探测：启用后 home 引擎注入 has_site=true，自动开启「关于/产品/新闻/联系」页面与导航
 */

if (!defined('IN_CRONLITE')) {
	exit;
}

require_once __DIR__ . '/lib/site.php';

mnbt_plugin_register('official_site', [
	'name' => '官网内容',
	'description' => '企业官网内容管理：产品展示、新闻资讯、联系留言',
]);

/* ============================================================
 *  前台 API 路由（home SPA 数据接口）
 * ============================================================ */

// 产品列表（支持 ?category= 筛选，返回分类定义）
mnbt_register_route('GET', '/site/api/products', function ($params, $ctx) {
	$category = isset($_GET['category']) ? trim($_GET['category']) : '';
	$rows = site_product_list('active', $category);
	$cat = site_product_categories();
	$products = [];
	foreach ($rows as $p) {
		$products[] = [
			'id'            => (int)$p['id'],
			'name'          => (string)$p['name'],
			'category'      => (string)$p['category'],
			'category_name' => (string)($cat[$p['category']] ?? $p['category']),
			'description'   => (string)$p['description'],
			'features'      => $p['features_list'],
			'image'         => (string)($p['image'] ?? ''),
		];
	}
	$cats = [];
	foreach ($cat as $k => $v) {
		$cats[] = ['id' => $k, 'name' => $v];
	}
	site_json('ok', ['products' => $products, 'categories' => $cats]);
});

// 产品详情
mnbt_register_route('GET', '/site/api/products/{product_id}', function ($params, $ctx) {
	$id = (int)($params['product_id'] ?? 0);
	$p = site_product_get($id);
	if (!$p || $p['status'] !== 'active') {
		site_json('产品不存在');
		return;
	}
	$cat = site_product_categories();
	site_json('ok', ['product' => [
		'id'            => (int)$p['id'],
		'name'          => (string)$p['name'],
		'category'      => (string)$p['category'],
		'category_name' => (string)($cat[$p['category']] ?? $p['category']),
		'description'   => (string)$p['description'],
		'features'      => $p['features_list'],
		'image'         => (string)($p['image'] ?? ''),
		'created_at'    => (string)($p['created_at'] ?? ''),
	]]);
});

// 新闻列表（分页 + 分类，返回分类统计）
mnbt_register_route('GET', '/site/api/news', function ($params, $ctx) {
	$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
	$per = isset($_GET['per_page']) ? max(1, min(50, (int)$_GET['per_page'])) : 6;
	$category = isset($_GET['category']) ? trim($_GET['category']) : '';
	$data = site_news_list($page, $per, $category);
	$list = [];
	foreach ($data['list'] as $n) {
		$list[] = [
			'id'         => (int)$n['id'],
			'title'      => (string)$n['title'],
			'category'   => (string)$n['category'],
			'content'    => (string)$n['content'],
			'views'      => (int)$n['views'],
			'created_at' => (string)$n['created_at'],
		];
	}
	site_json('ok', [
		'news'       => $list,
		'total'      => (int)$data['total'],
		'page'       => $page,
		'per_page'   => $per,
		'categories' => site_news_categories(),
	]);
});

// 热门新闻（静态段需注册在 /news/{news_id} 之前，避免被参数路由吞掉）
mnbt_register_route('GET', '/site/api/news/popular', function ($params, $ctx) {
	$rows = site_news_popular(4);
	$list = [];
	foreach ($rows as $n) {
		$list[] = [
			'id'         => (int)$n['id'],
			'title'      => (string)$n['title'],
			'views'      => (int)$n['views'],
			'created_at' => (string)$n['created_at'],
		];
	}
	site_json('ok', ['popular' => $list]);
});

// 新闻详情（累计浏览量）
mnbt_register_route('GET', '/site/api/news/{news_id}', function ($params, $ctx) {
	$id = (int)($params['news_id'] ?? 0);
	$n = site_news_get($id, true);
	if (!$n || $n['status'] !== 'active') {
		site_json('新闻不存在');
		return;
	}
	site_json('ok', ['news' => [
		'id'         => (int)$n['id'],
		'title'      => (string)$n['title'],
		'category'   => (string)$n['category'],
		'content'    => (string)$n['content'],
		'views'      => (int)$n['views'],
		'created_at' => (string)$n['created_at'],
	]]);
});

// 提交留言
mnbt_register_route('POST', '/site/api/contact', function ($params, $ctx) {
	$r = site_message_add([
		'name'    => $_POST['name'] ?? '',
		'email'   => $_POST['email'] ?? '',
		'phone'   => $_POST['phone'] ?? '',
		'message' => $_POST['message'] ?? '',
	]);
	if ($r !== true) {
		site_json($r);
		return;
	}
	site_json('ok');
});

/* ============================================================
 *  管理员端页面注册
 * ============================================================ */

mnbt_register_page('admin', 'site_products', 'views/admin/products.php', '产品管理');
mnbt_register_page('admin', 'site_news', 'views/admin/news.php', '新闻管理');
mnbt_register_page('admin', 'site_messages', 'views/admin/messages.php', '留言管理');

// 侧边栏菜单
mnbt_register_menu('admin', [
	'title' => '官网内容',
	'icon'  => 'mdi-web',
	'order' => 55,
	'children' => [
		['title' => '产品管理', 'page' => 'site_products', 'icon' => 'mdi-package-variant', 'multitabs' => true],
		['title' => '新闻管理', 'page' => 'site_news', 'icon' => 'mdi-newspaper', 'multitabs' => true],
		['title' => '留言管理', 'page' => 'site_messages', 'icon' => 'mdi-email-open', 'multitabs' => true],
	],
]);
