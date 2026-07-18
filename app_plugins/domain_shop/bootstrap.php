<?php
/**
 * domain_shop 插件入口
 * - 二级域名商品 CRUD（接管原 admin/api/ym.php）
 * - DNSPod DNS 解析自动创建与管理
 * - ymgm 支付结算（接管原 MPHX/lib/pay.function.php 的 ymgm 分支）
 * - host.created 钩子：自动为新主机创建 A 记录
 */
if (!defined('IN_CRONLITE')) exit;

// 引入 lib
require_once __DIR__ . '/lib/domain.php';
require_once __DIR__ . '/lib/dns.php';

// 已有站点自动升级表结构（补齐 channel / provider_id 字段）
domain_product_schema_upgrade();

mnbt_plugin_register('domain_shop', ['name' => '域名商品与 DNS 解析']);

/* ============================================================
 * 1. 后台菜单
 * ============================================================ */
mnbt_register_menu('admin', [
	'title' => '域名商品',
	'page'  => 'products',
	'icon'  => 'mdi-domain',
	'order' => 30,
	'multitabs' => true,
]);
mnbt_register_menu('admin', [
	'title' => 'DNS 服务商',
	'page'  => 'dns_provider',
	'icon'  => 'mdi-dns',
	'order' => 31,
	'multitabs' => true,
]);
mnbt_register_menu('admin', [
	'title' => 'DNS 记录',
	'page'  => 'dns_records',
	'icon'  => 'mdi-format-list-bulleted',
	'order' => 32,
	'multitabs' => true,
]);

mnbt_register_page('admin', 'products',      'admin/products.php',      '域名商品列表');
mnbt_register_page('admin', 'product_add',   'admin/product_add.php',   '添加域名商品');
mnbt_register_page('admin', 'dns_provider',  'admin/dns_provider.php',  'DNS 服务商配置');
mnbt_register_page('admin', 'dns_records',   'admin/dns_records.php',   'DNS 记录查看');

/* ============================================================
 * 2. 用户端菜单（default 主题 sidebar 自动渲染）
 * ============================================================ */
mnbt_register_menu('user', [
	'title' => '我的 DNS 记录',
	'page'  => 'dns_records',
	'icon'  => 'mdi-dns',
	'order' => 40,
	'multitabs' => true,
]);
mnbt_register_menu('user', [
	'title' => '域名绑定',
	'page'  => 'bind',
	'icon'  => 'mdi-domain',
	'order' => 41,
	'multitabs' => true,
]);
mnbt_register_page('user', 'dns_records', 'user/dns_records.php', '我的 DNS 记录');
mnbt_register_page('user', 'bind',        'user/bind.php',        '域名绑定');

/* ============================================================
 * 3. 后台 AJAX：域名商品 CRUD
 * ============================================================ */
mnbt_register_ajax('admin', 'p_domain_addym', function () {
	mnbt_plugin_require_admin();
	$url     = $_POST['url'] ?? '';
	$bt      = $_POST['bt'] ?? '';
	$jg      = $_POST['jg'] ?? '';
	$js      = $_POST['ymjs'] ?? '';
	$kg      = isset($_POST['kg']) ? (bool)$_POST['kg'] : true;
	$channel = $_POST['channel'] ?? 'pan';
	$channel = ($channel === 'dnsapi') ? 'dnsapi' : 'pan';
	$providerId = (int)($_POST['provider_id'] ?? 0);
	$r = domain_product_add($url, $bt, $jg, $js, $kg, $channel, $providerId);
	json_exit($r['ok'] ? $r['msg'] : $r['msg']);
});

mnbt_register_ajax('admin', 'p_domain_xgym', function () {
	mnbt_plugin_require_admin();
	$id = (int)($_POST['id'] ?? 0);
	$js = $_POST['js'] ?? '';
	$jg = $_POST['jg'] ?? '';
	$kg = isset($_POST['kg']) ? (bool)$_POST['kg'] : true;
	$channel = $_POST['channel'] ?? null;
	if ($channel !== null) {
		$channel = ($channel === 'dnsapi') ? 'dnsapi' : 'pan';
	}
	$providerId = isset($_POST['provider_id']) ? (int)$_POST['provider_id'] : null;
	$r = domain_product_update($id, $js, $jg, $kg, $channel, $providerId);
	json_exit($r['ok'] ? $r['msg'] : $r['msg']);
});

mnbt_register_ajax('admin', 'p_domain_ymsc', function () {
	mnbt_plugin_require_admin();
	$id = (int)($_POST['id'] ?? 0);
	$r = domain_product_delete($id);
	json_exit($r['ok'] ? $r['msg'] : $r['msg']);
});

mnbt_register_ajax('admin', 'p_domain_ymscxz', function () {
	mnbt_plugin_require_admin();
	$idsz = $_POST['idsz'] ?? [];
	if (!is_array($idsz)) $idsz = [];
	$r = domain_product_delete_batch($idsz);
	json_exit((string)$r['ok'], ['codr' => $r['fail']]);
});

mnbt_register_ajax('admin', 'p_domain_listym', function () {
	mnbt_plugin_require_admin();
	$sorting = strtoupper($_POST['sortOrder'] ?? '') === 'DESC' ? 'DESC' : 'ASC';
	$paixu   = preg_replace('/[^a-zA-Z0-9_]/', '', $_POST['sort'] ?? 'id') ?: 'id';
	$pagesize = intval($_POST['limit'] ?? 10);
	$page     = intval($_POST['page'] ?? 1);
	$data = domain_product_list($page, $pagesize, $paixu, $sorting);
	exit(json_encode(['total' => $data['total'], 'rows' => $data['rows']], 256));
});

/* ============================================================
 * 4. 后台 AJAX：DNS 服务商凭证 CRUD
 * ============================================================ */
mnbt_register_ajax('admin', 'p_dns_provider_save', function () {
	mnbt_plugin_require_admin();
	global $DB, $date;
	$id   = (int)($_POST['id'] ?? 0);
	$slug = $_POST['slug'] ?? 'dnspod';
	$name = trim($_POST['name'] ?? '');
	$apiId = trim($_POST['api_id'] ?? '');
	$apiSecret = trim($_POST['api_secret'] ?? '');
	$kg = isset($_POST['kg']) ? (bool)$_POST['kg'] : true;

	if (!in_array($slug, ['dnspod'], true)) {
		json_exit_error('暂不支持该服务商');
	}
	if ($name === '' || $apiId === '' || $apiSecret === '') {
		json_exit_error('参数不能为空');
	}

	if ($id > 0) {
		$ok = $DB->query_prepare(
			"update `plg_dns_provider` set `slug`=?, `name`=?, `api_id`=?, `api_secret`=?, `qk`=? where `id`=?",
			[$slug, $name, $apiId, $apiSecret, $kg ? 'true' : 'false', $id]
		);
	} else {
		$ok = $DB->query_prepare(
			"INSERT INTO `plg_dns_provider` (`slug`, `name`, `api_id`, `api_secret`, `extra`, `qk`, `created_at`) VALUES (?,?,?,?,?,?,?)",
			[$slug, $name, $apiId, $apiSecret, '{}', $kg ? 'true' : 'false', $date]
		);
	}
	if (!$ok) json_exit_error('保存失败：' . $DB->error());
	json_exit_success('保存成功');
});

mnbt_register_ajax('admin', 'p_dns_provider_delete', function () {
	mnbt_plugin_require_admin();
	global $DB;
	$id = (int)($_POST['id'] ?? 0);
	// 先删关联的本地 DNS 记录（远程记录已无法删除，标记为失效）
	$DB->query_prepare("DELETE FROM plg_dns_record WHERE provider_id=?", [$id]);
	$ok = $DB->query_prepare("DELETE FROM plg_dns_provider WHERE id=? limit 1", [$id]);
	if (!$ok) json_exit_error('删除失败：' . $DB->error());
	json_exit_success('删除成功');
});

mnbt_register_ajax('admin', 'p_dns_provider_test', function () {
	mnbt_plugin_require_admin();
	$id = (int)($_POST['id'] ?? 0);
	$provider = dns_provider_get($id);
	if (!$provider) json_exit_error('服务商不存在');
	$adapter = dns_provider_adapter($provider);
	if (!$adapter) json_exit_error('不支持的服务商');
	$domains = $adapter->listDomains();
	if (empty($domains)) {
		json_exit_error('测试失败：' . $adapter->lastError());
	}
	json_exit_success('测试成功，共 ' . count($domains) . ' 个域名', ['count' => count($domains)]);
});

mnbt_register_ajax('admin', 'p_dns_record_delete', function () {
	mnbt_plugin_require_admin();
	$id = (int)($_POST['id'] ?? 0);
	$r = dns_record_delete($id);
	if (!$r['ok']) json_exit_error($r['msg']);
	json_exit_success($r['msg']);
});

/* ============================================================
 * 5. 用户端 AJAX：DNS 记录管理
 * ============================================================ */
mnbt_register_ajax('user', 'p_dns_record_list', function () {
	mnbt_plugin_require_user();
	global $yhc;
	$user = $yhc['user'] ?? '';
	$records = dns_record_list_by_user($user);
	json_exit_success('ok', ['rows' => $records]);
});

mnbt_register_ajax('user', 'p_dns_record_add', function () {
	mnbt_plugin_require_user();
	global $yhc, $DB;
	$user = $yhc['user'] ?? '';
	$providerId = (int)($_POST['provider_id'] ?? 0);
	$domain = $_POST['domain'] ?? '';
	$name   = $_POST['name'] ?? '';
	$type   = $_POST['type'] ?? 'A';
	$value  = $_POST['value'] ?? '';
	$ttl    = (int)($_POST['ttl'] ?? 600);

	// 限制：用户只能为已购域名添加记录
	if (!domain_shop_user_owns_domain($user, $domain)) {
		json_exit_error('您未购买该域名，无权添加记录');
	}

	// 限制：仅 DNS API 通道域名允许用户管理 DNS 记录
	$prod = $DB->get_row_prepare("SELECT * FROM plg_domain_product WHERE url=? limit 1", [$domain]);
	if (!$prod) json_exit_error('域名商品不存在');
	if (($prod['channel'] ?? 'pan') !== 'dnsapi') {
		json_exit_error('该域名为泛解析通道，无需单独管理 DNS 记录');
	}

	// 服务商以商品配置为准，忽略前端传值
	$productProviderId = (int)$prod['provider_id'];
	if ($productProviderId <= 0) {
		json_exit_error('该域名未关联 DNS 服务商，请联系管理员');
	}

	$r = dns_record_create($user, $productProviderId, $domain, $name, $type, $value, $ttl, 0);
	if (!$r['ok']) json_exit_error($r['msg']);
	json_exit_success($r['msg']);
});

mnbt_register_ajax('user', 'p_dns_record_delete', function () {
	mnbt_plugin_require_user();
	global $yhc;
	$user = $yhc['user'] ?? '';
	$id = (int)($_POST['id'] ?? 0);
	$r = dns_record_delete($id, $user);
	if (!$r['ok']) json_exit_error($r['msg']);
	json_exit_success($r['msg']);
});

/* ============================================================
 * 6. 用户端 AJAX：域名绑定（迁自 user/api/domain.php）
 *    urllist/erurl 保留原 gn 名（与 cdn.php 不冲突）。
 *    tjurl/scurl/seturl 因与 CDN 产品的 cdn.php 同名冲突，
 *    改名为 p_domain_tjurl / p_domain_scurl / p_domain_seturl；
 *    CDN 产品（hxc==1）仍走核心 cdn.php 的同名处理器。
 * ============================================================ */
require_once __DIR__ . '/user/api/bind.php';

/* ============================================================
 * 7. 钩子：host.created - 自动创建 A 记录
 *    仅对 channel='dnsapi' 的域名商品生效；泛解析商品依赖域名整体
 *    泛 A 记录，无需逐主机建记录（详见 lib/dns.php）。
 * ============================================================ */
mnbt_add_action('host.created', function ($host, $ctx = []) {
	$r = dns_record_auto_create_for_host($host, $ctx);
	if (!empty($r['ok']) && function_exists('mnbt_log')) {
		mnbt_log('系统', '插件-domain_shop', '主机开通自动建 DNS：' . $r['msg'], '成功', $GLOBALS['DB']);
	}
}, 20);

/* ============================================================
 * 8. 钩子：order.paid - 处理 ymgm 结算
 *    迁自 MPHX/lib/pay.function.php 的 ymgm 分支
 * ============================================================ */
mnbt_add_action('order.paid', function ($order, $ctx = []) {
	global $DB;
	if (!is_array($order)) return;
	$lx = $order['lx'] ?? '';
	if ($lx !== 'ymgm') return;

	// ymgm 结算逻辑迁自原核心，已搬到 lib/settle.php
	require_once __DIR__ . '/lib/settle.php';
	domain_shop_settle_ymgm($order, $ctx);
}, 10);

/* ============================================================
 * 9. 辅助：判断用户是否已购某域名
 * ============================================================ */
function domain_shop_user_owns_domain($user, $domain)
{
	global $DB;
	$products = $DB->get_all_prepare("SELECT * FROM plg_domain_product WHERE url=? limit 1", [$domain]);
	if (!$products) return false;
	$row = $products[0];
	$buyers = json_decode($row['json'] ?? '[]', true);
	if (!is_array($buyers)) return false;
	return in_array($user, $buyers, true);
}

/* ============================================================
 * 10. 路由：/domain/buy - 替代 user/pay.php 的 ymgm 下单分支
 * ============================================================ */
mnbt_register_route('POST', '/domain/buy', function ($params, $ctx) {
	// 兼容 user/pay.php 的 ymgm 下单流程，迁移到此处
	require_once __DIR__ . '/lib/buy.php';
	domain_shop_handle_buy();
});
