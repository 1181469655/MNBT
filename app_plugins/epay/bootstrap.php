<?php
/**
 * 易支付插件主入口
 *
 * V1.81 P3：从核心支付代码迁移而来，提供彩虹易支付协议的支付宝/微信/QQ 三个子付款方式。
 *
 * 配置项（保存在 MN_plugin_option 表）：
 *   - apiurl  易支付接口地址（如 https://pay.example.com/）
 *   - pid     商户 ID
 *   - key     商户密钥
 *
 * 自动迁移：首次启动时若检测到 MN_config.hxe/hxr/hxt 旧值且本插件未配置，则自动迁移。
 */

if (!defined('IN_CRONLITE')) {
	exit;
}

require_once __DIR__ . '/lib/Epay_Core.php';

mnbt_plugin_register('epay', [
	'name'        => '易支付',
	'description' => '彩虹易支付协议，支持支付宝/微信/QQ 钱包',
	'icon'        => 'mdi-credit-card-multiple',
]);

// ============================================================
//  自动迁移旧配置（一次性）
// ============================================================
function epay_maybe_migrate_legacy()
{
	$migrated = mnbt_plugin_option_get('epay', '_migrated', '0');
	if ($migrated === '1' || $migrated === 1) {
		return;
	}
	global $DB, $siteid;
	$siteid = isset($siteid) ? $siteid : 1;
	$row = $DB->get_row_prepare("SELECT hxe,hxr,hxt FROM MN_config WHERE id=? LIMIT 1", [$siteid]);
	if (!$row) {
		mnbt_plugin_option_set('epay', '_migrated', '1');
		return;
	}
	$apiurl = trim((string)($row['hxe'] ?? ''));
	$pid    = trim((string)($row['hxr'] ?? ''));
	$key    = trim((string)($row['hxt'] ?? ''));
	if ($apiurl !== '' || $pid !== '' || $key !== '') {
		if ($apiurl !== '') mnbt_plugin_option_set('epay', 'apiurl', $apiurl);
		if ($pid !== '')    mnbt_plugin_option_set('epay', 'pid', $pid);
		if ($key !== '')    mnbt_plugin_option_set('epay', 'key', $key);
		error_log('[MNBT epay] 已自动迁移旧易支付配置到插件选项');
	}
	mnbt_plugin_option_set('epay', '_migrated', '1');
}
epay_maybe_migrate_legacy();

// ============================================================
//  读取本插件配置的辅助函数
// ============================================================
function epay_get_config()
{
	return [
		'apiurl' => trim((string)mnbt_plugin_option_get('epay', 'apiurl', '')),
		'pid'    => trim((string)mnbt_plugin_option_get('epay', 'pid', '')),
		'key'    => trim((string)mnbt_plugin_option_get('epay', 'key', '')),
	];
}

function epay_is_configured()
{
	$c = epay_get_config();
	return $c['apiurl'] !== '' && $c['pid'] !== '' && $c['key'] !== '';
}

// ============================================================
//  注册支付插件
// ============================================================
mnbt_register_payment('epay', [
	'name'        => '易支付',
	'description' => '彩虹易支付协议（pid/key/apiurl）',
	'icon'        => 'mdi-credit-card-multiple',
	'methods'     => [
		'alipay' => ['name' => '支付宝',   'icon' => 'mdi-alpha-a-circle'],
		'wxpay'  => ['name' => '微信支付', 'icon' => 'mdi-wechat'],
		'qqpay'  => ['name' => 'QQ 钱包',  'icon' => 'mdi-qqchat'],
	],
	'build' => function ($method, $order, $plugin_config) {
		if (!epay_is_configured()) {
			error_log('[MNBT epay] 未配置 apiurl/pid/key，无法发起支付');
			return false;
		}
		$c = epay_get_config();
		$siteurl = isset($order['siteurl']) ? $order['siteurl'] : '';
		// 站点根 URL 末尾保证带斜杠
		$siteurl = rtrim((string)$siteurl, '/') . '/';
		// 回调地址统一用 index.php?_r= 兼容路由：不依赖服务器伪静态重写。
		// 部分环境（未配置 rewrite 规则）下 /pay/epay/notify 会直接返回 nginx 404，
		// 导致易支付网关异步通知收不到、订单无法结算（支付成功但余额不入账）。
		$notifyUrl = $siteurl . 'index.php?_r=/pay/epay/notify';
		$returnUrl = $siteurl . 'index.php?_r=/pay/epay/return';

		$params = [
			'type'          => $method,           // alipay / wxpay / qqpay
			'out_trade_no'  => $order['out_trade_no'],
			'notify_url'    => $notifyUrl,
			'return_url'    => $returnUrl,
			'name'          => $order['name'],
			'money'         => $order['money'],
		];
		return Epay_Core::buildForm($c['apiurl'], $c['pid'], $c['key'], $params);
	},
]);

// ============================================================
//  注册回调路由：/pay/epay/notify（异步通知）
//  注意：路由方式必须为 *（GET+POST 都接收）——
//  部分易支付变体（如 pay1987）的自动通知/后台补发使用 GET 携带参数，
//  只注册 POST 会导致通知命中不了路由、订单无法结算。
// ============================================================
mnbt_register_route('*', '/pay/epay/notify', function ($params, $ctx) {
	@header('Content-Type: text/plain; charset=UTF-8');
	if (!epay_is_configured()) {
		echo 'fail';
		return;
	}
	$c = epay_get_config();
	// 兼容 POST / GET 两种通知方式
	$data = $_POST;
	if (empty($data)) {
		$data = $_GET;
		// 路由参数 _r 是我们拼接的，不参与网关签名，验签前必须剔除
		unset($data['_r']);
	}
	if (empty($data) || empty($data['sign'])) {
		mnbt_pay_log('易支付异步通知无数据或缺少 sign', '回调异常', $data['out_trade_no'] ?? '');
		echo 'fail';
		return;
	}
	$sign = (string)$data['sign'];
	if (!Epay_Core::verifySign($data, $c['key'], $sign)) {
		mnbt_pay_log('易支付异步通知验签失败', '验签失败', $data['out_trade_no'] ?? '');
		echo 'fail';
		return;
	}
	$out_trade_no = isset($data['out_trade_no']) ? (string)$data['out_trade_no'] : '';
	$trade_status = isset($data['trade_status']) ? (string)$data['trade_status'] : '';
	$money        = isset($data['money']) ? (string)$data['money'] : '';
	if ($out_trade_no === '') {
		echo 'fail';
		return;
	}
	$result = mnbt_pay_settle_order($out_trade_no, $trade_status, $money);
	if (!empty($result['ok'])) {
		echo 'success';
	} else {
		echo 'fail';
	}
}, 10, null, true);

// ============================================================
//  注册回调路由：/pay/epay/return（同步返回）
// ============================================================
mnbt_register_route('GET', '/pay/epay/return', function ($params, $ctx) {
	if (!epay_is_configured()) {
		@header('Location: ./');
		return;
	}
	$c = epay_get_config();
	$data = $_GET;
	// 剔除路由参数 _r（不参与签名）
	unset($data['_r']);
	$ok = false;
	if (!empty($data) && !empty($data['sign'])) {
		$ok = Epay_Core::verifySign($data, $c['key'], (string)$data['sign']);
	}
	if (!$ok) {
		mnbt_pay_log('易支付同步返回验签失败', '验签失败', $data['out_trade_no'] ?? '');
	}
	// 同步返回仅作展示，订单最终状态以异步通知为准
	$base = isset($ctx['base']) ? $ctx['base'] : '';
	@header('Location: ' . $base . '/user');
});

// ============================================================
//  后台设置页
// ============================================================
mnbt_register_page('admin', 'settings', 'admin/settings.php', '易支付设置');

mnbt_register_settings_tab([
	'title' => '易支付设置',
	'page'  => 'settings',
	'order' => 10,
]);

mnbt_register_menu('admin', [
	'title' => '易支付设置',
	'page'  => 'settings',
	'icon'  => 'mdi-credit-card-multiple',
	'order' => 60,
	'multitabs' => true,
]);

// ============================================================
//  AJAX：保存配置
// ============================================================
mnbt_register_ajax('admin', 'epay_save', function () {
	mnbt_plugin_require_admin();
	$apiurl = isset($_POST['apiurl']) ? trim((string)$_POST['apiurl']) : '';
	$pid    = isset($_POST['pid']) ? trim((string)$_POST['pid']) : '';
	$key    = isset($_POST['key']) ? trim((string)$_POST['key']) : '';
	if (mb_strlen($apiurl) > 500 || mb_strlen($pid) > 60 || mb_strlen($key) > 200) {
		json_exit('参数过长');
	}
	mnbt_plugin_option_set('epay', 'apiurl', $apiurl);
	mnbt_plugin_option_set('epay', 'pid', $pid);
	mnbt_plugin_option_set('epay', 'key', $key);
	json_exit('保存成功');
});
