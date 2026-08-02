<?php
/**
 * 售卖前端 - 统一商店前端皮肤
 *
 * 1) 接管站点首页 /，渲染品牌落地页（原功能）
 * 2) 通过"低优先级通用路由"接管 user_info / balance / hosting_shop 的用户端 GET 页面，
 *    提供统一的现代化前端布局。业务逻辑（登录鉴权、下单、充值、支付回调）仍由原插件处理。
 *
 * 说明：
 *  - 页面接管路由 priority = 5（低于原插件默认 10），保证先于原插件匹配并渲染；
 *  - 仅在目标插件启用时注册对应路由；
 *  - POST API（/account/api/*、/shop/api/*、/balance/api/*）与支付回调 /pay/* 不接管。
 */

if (!defined('IN_CRONLITE')) {
	exit;
}

require_once __DIR__ . '/lib/shop_frontend.php';

mnbt_plugin_register('shop_frontend', [
	'name' => '售卖前端',
	'description' => '统一商店前端：接管首页落地页与 user_info / balance / hosting_shop 的全部用户端页面，提供一致的现代化皮肤',
]);

/* ============================================================
 *  1) 首页接管
 * ============================================================ */
mnbt_register_home(function ($ctx) {
	shop_frontend_render_homepage();
	return true;
}, 100);

function shop_frontend_render_homepage()
{
	$title   = shop_frontend_option('site_title', 'MNBT 主机售卖');
	$logo    = shop_frontend_option('site_logo', '');
	$primary = shop_frontend_option('site_primary', '#4f46e5');
	$accent  = shop_frontend_option('site_accent', '#FF5722');
	$hero    = shop_frontend_option('site_hero', '高性能虚拟主机，即买即用');
	$footer  = shop_frontend_option('site_footer', '© ' . date('Y') . ' MNBT. All rights reserved.');
	$favicon = shop_frontend_favicon();

	$plans = shop_frontend_get_plans();
	$user = shop_frontend_get_current_user();

	// 共用顶栏导航所需的变量
	$brand = $title;
	$brand_logo = $logo;
	$current_user = $user;
	$active = 'home';
	$has_balance = function_exists('balance_get');
	$has_hosting = function_exists('hosting_plan_list_active');

	// URL 构建 — 与 user_info / balance / hosting_shop 插件完全一致的格式
	$url = function (string $path) {
		return shop_frontend_url($path);
	};

	$features = [
		['icon' => 'mdi-shield-check',  'title' => '99.9% 在线率', 'desc' => '企业级硬件架构，稳定可靠，SLA 保障'],
		['icon' => 'mdi-flash',         'title' => '极速部署',     'desc' => '支付成功后自动开通，即买即用，无需等待'],
		['icon' => 'mdi-headset',       'title' => '专业支持',     'desc' => '技术团队 7×24 小时响应，工单优先处理'],
		['icon' => 'mdi-server',        'title' => '高性能节点',   'desc' => '全 SSD 存储，BGP 多线接入，低延迟体验'],
		['icon' => 'mdi-backup-restore','title' => '自动备份',     'desc' => '每日自动备份数据，灾备无忧'],
		['icon' => 'mdi-lock',          'title' => '安全防护',     'desc' => 'DDoS 清洗 + WAF 规则，网站安全有保障'],
	];

	$planCards = [];
	foreach ($plans as $p) {
		$minPrice = 0;
		if ($p['price_month_cents'] > 0) {
			$minPrice = $p['price_month_cents'] / 100;
		}
		if ($p['price_year_cents'] > 0) {
			$yearPrice = $p['price_year_cents'] / 100;
			if ($minPrice == 0 || $yearPrice / 12 < $minPrice) {
				$minPrice = $yearPrice / 12;
			}
		}
		$priceStr = $minPrice > 0 ? '¥' . number_format($minPrice, 2) . ' 起/月' : '免费';
		$feats = [];
		if (!empty($p['spec_web']))    $feats[] = "网页空间 " . $p['spec_web'] . " MB";
		if (!empty($p['spec_sql']))    $feats[] = "数据库 " . $p['spec_sql'] . " MB";
		if (!empty($p['spec_flow']))   $feats[] = "月流量 " . $p['spec_flow'] . " GB";
		if (!empty($p['spec_domain'])) $feats[] = "可绑定 " . $p['spec_domain'] . " 个域名";
		$planCards[] = [
			'id'    => $p['id'],
			'name'  => $p['name'],
			'desc'  => $p['description'] ?? '',
			'price' => $priceStr,
			'feats' => $feats,
		];
	}

	include __DIR__ . '/views/homepage.php';
	exit;
}

/* ============================================================
 *  2) 管理员页面与设置
 * ============================================================ */
mnbt_register_page('admin', 'settings', 'views/admin/settings.php', '售卖前端设置');

mnbt_register_menu('admin', [
	'title' => '售卖前端',
	'icon'  => 'mdi-store',
	'order' => 59,
	'children' => [
		['title' => '前端设置', 'page' => 'settings', 'icon' => 'mdi-cog', 'multitabs' => true],
	],
]);

mnbt_register_ajax('admin', 'shop_frontend_save_settings', function () {
	mnbt_plugin_require_admin();
	$fields = ['site_title', 'site_logo', 'site_primary', 'site_accent', 'site_hero', 'site_footer', 'site_favicon'];
	foreach ($fields as $f) {
		mnbt_plugin_option_set('shop_frontend', $f, trim((string)($_POST[$f] ?? '')));
	}
	echo json_encode(['code' => '保存成功'], JSON_UNESCAPED_UNICODE);
	exit;
});

/* 站点 Logo / Favicon 上传（仅支持 ICO） */
mnbt_register_ajax('admin', 'shop_frontend_upload_icon', function () {
	mnbt_plugin_require_admin();

	$target = ($_POST['target'] ?? '') === 'favicon' ? 'favicon' : 'logo';
	$optionKey = $target === 'logo' ? 'site_logo' : 'site_favicon';

	if (empty($_FILES['icon']) || !is_array($_FILES['icon']) || (int)($_FILES['icon']['error'] ?? 1) !== 0) {
		json_exit_error('未收到文件或上传失败');
	}
	$file = $_FILES['icon'];
	$name = (string)($file['name'] ?? '');
	if (strtolower(pathinfo($name, PATHINFO_EXTENSION)) !== 'ico') {
		json_exit_error('仅支持 ICO 格式');
	}
	$size = (int)($file['size'] ?? 0);
	if ($size <= 0 || $size > 2 * 1024 * 1024) {
		json_exit_error('ICO 文件大小需在 2MB 以内');
	}
	$tmp = (string)($file['tmp_name'] ?? '');
	if ($tmp === '' || !is_uploaded_file($tmp)) {
		json_exit_error('文件上传异常');
	}
	// 校验 ICO 魔数：前 4 字节为 00 00 01 00
	$head = @file_get_contents($tmp, false, null, 0, 4);
	if ($head === false || $head !== "\x00\x00\x01\x00") {
		json_exit_error('文件不是有效的 ICO 图片');
	}

	$dir = mnbt_plugin_path('shop_frontend') . 'assets/';
	if (!is_dir($dir) && !@mkdir($dir, 0755, true)) {
		json_exit_error('插件资源目录不可写');
	}
	if (!move_uploaded_file($tmp, $dir . $target . '.ico')) {
		json_exit_error('保存文件失败，请检查插件目录权限');
	}

	$url = mnbt_plugin_url('shop_frontend', 'assets/' . $target . '.ico');
	mnbt_plugin_option_set('shop_frontend', $optionKey, $url);
	json_exit_success('上传成功', ['url' => $url]);
});

/* ============================================================
 *  3) 页面接管（priority 5 < 原插件默认 10）
 * ============================================================ */

$sf_priority = 5;

/* ---- user_info：/account/* ---- */
if (mnbt_plugin_enabled('user_info')) {

	mnbt_register_route('GET', '/account/login', function ($params, $ctx) {
		$user = shop_frontend_get_current_user();
		if ($user) {
			header('Location: ' . shop_frontend_url('account/profile'));
			exit;
		}
		shop_frontend_render('auth_login', ['page_title' => '登录', 'active' => 'login']);
	}, $sf_priority);

	mnbt_register_route('GET', '/account/register', function ($params, $ctx) {
		$user = shop_frontend_get_current_user();
		if ($user) {
			header('Location: ' . shop_frontend_url('account/profile'));
			exit;
		}
		shop_frontend_render('auth_register', ['page_title' => '注册', 'active' => 'register']);
	}, $sf_priority);

	mnbt_register_route('GET', '/account/profile', function ($params, $ctx) {
		$user = shop_frontend_get_current_user();
		if (!$user) {
			header('Location: ' . shop_frontend_url('account/login'));
			exit;
		}
		shop_frontend_render('auth_profile', ['page_title' => '个人信息', 'active' => 'profile']);
	}, $sf_priority);

	mnbt_register_route('GET', '/account/password', function ($params, $ctx) {
		$user = shop_frontend_get_current_user();
		if (!$user) {
			header('Location: ' . shop_frontend_url('account/login'));
			exit;
		}
		shop_frontend_render('auth_password', ['page_title' => '修改密码', 'active' => 'password']);
	}, $sf_priority);
}

/* ---- balance：/balance/* ---- */
if (mnbt_plugin_enabled('balance')) {

	mnbt_register_route('GET', '/balance', function ($params, $ctx) {
		if (!function_exists('balance_require_user') || !function_exists('balance_get') || !function_exists('balance_logs')) {
			return false;
		}
		$user = balance_require_user();
		$balance = balance_get((int)$user['id']);
		$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
		$logs = balance_logs((int)$user['id'], $page, 15);
		shop_frontend_render('balance', [
			'page_title' => '我的余额',
			'active' => 'balance',
			'balance_cents' => $balance,
			'logs' => $logs,
		]);
	}, $sf_priority);

	mnbt_register_route('GET', '/balance/recharge', function ($params, $ctx) {
		if (!function_exists('balance_require_user')) return false;
		$user = balance_require_user();
		$methods = [];
		if (function_exists('mnbt_get_enabled_payment_methods')) {
			foreach (mnbt_get_enabled_payment_methods() as $m) {
				if (($m['plugin'] ?? '') === 'balance') continue; // 充值不能用余额支付
				$methods[] = $m;
			}
		}
		shop_frontend_render('balance_recharge', [
			'page_title' => '余额充值',
			'active' => 'recharge',
			'methods' => $methods,
		]);
	}, $sf_priority);
}

/* ---- hosting_shop：/shop/* ---- */
if (mnbt_plugin_enabled('hosting_shop')) {

	mnbt_register_route('GET', '/shop', function ($params, $ctx) {
		if (!function_exists('hosting_require_user') || !function_exists('hosting_plan_list_active')) {
			return false;
		}
		hosting_require_user();
		$plans = hosting_plan_list_active();
		shop_frontend_render('shop', ['page_title' => '主机套餐', 'active' => 'shop', 'plans' => $plans]);
	}, $sf_priority);

	mnbt_register_route('GET', '/shop/order/{plan_id}', function ($params, $ctx) {
		if (!function_exists('hosting_require_user') || !function_exists('hosting_plan_get')) {
			return false;
		}
		hosting_require_user();
		$plan_id = (int)($params['plan_id'] ?? 0);
		$plan = hosting_plan_get($plan_id);
		if (!$plan || $plan['status'] !== 'active') {
			http_response_code(404);
			echo '套餐不存在或已下架';
			return;
		}
		$methods = function_exists('mnbt_get_enabled_payment_methods') ? mnbt_get_enabled_payment_methods() : [];
		shop_frontend_render('shop_order', [
			'page_title' => '购买：' . $plan['name'],
			'active' => 'shop',
			'plan' => $plan,
			'methods' => $methods,
		]);
	}, $sf_priority);

	mnbt_register_route('GET', '/shop/assets', function ($params, $ctx) {
		if (!function_exists('hosting_require_user') || !function_exists('hosting_asset_list_by_user')) {
			return false;
		}
		$user = hosting_require_user();
		$assets = hosting_asset_list_by_user((int)$user['id']);
		shop_frontend_render('shop_assets', ['page_title' => '我的主机', 'active' => 'assets', 'assets' => $assets]);
	}, $sf_priority);

	mnbt_register_route('GET', '/shop/orders', function ($params, $ctx) {
		if (!function_exists('hosting_require_user') || !function_exists('hosting_order_list_by_user')) {
			return false;
		}
		$user = hosting_require_user();
		$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
		$orders = hosting_order_list_by_user((int)$user['id'], $page, 15);
		shop_frontend_render('shop_orders', ['page_title' => '我的订单', 'active' => 'orders', 'orders' => $orders]);
	}, $sf_priority);
}

/* ============================================================
 *  4) 支付同步返回接管
 *     epay / alipay_official 的同步返回默认跳 $base.'/user'（核心登录），
 *     用户走 user_info 账户体系时会被踢到核心「主机登录页」；
 *     且若返回路径无任何路由匹配，index.php 也会兜底跳到 /user。
 *     这里用低优先级通用路由接管所有 /pay/{plugin}/return，
 *     支付后回到统一前端（按订单类型跳订单列表或余额页）。
 *     订单最终状态仍由异步通知 /pay/{slug}/notify 决定。
 * ============================================================ */
mnbt_register_route('GET', '/pay/{plugin}/return', function ($params, $ctx) {
	@header('Location: ' . shop_frontend_pay_return_url());
	exit;
}, $sf_priority);
