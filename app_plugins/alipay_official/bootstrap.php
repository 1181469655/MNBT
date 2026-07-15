<?php
/**
 * 支付宝官方插件主入口
 *
 * V1.81 P3：基于支付宝官方 API 直连，提供两种付款方式：
 *   - pc      电脑网站支付（alipay.trade.page.pay）
 *   - qrcode  当面付扫码（alipay.trade.precreate）
 *
 * 配置项（保存在 MN_plugin_option 表）：
 *   - app_id       应用 APPID
 *   - private_key  应用私钥（RSA2）
 *   - public_key   支付宝公钥
 *   - gateway      网关地址（默认 https://openapi.alipay.com/gateway.do，沙箱可换）
 *
 * 依赖：PHP openssl + curl 扩展
 */

if (!defined('IN_CRONLITE')) {
	exit;
}

require_once __DIR__ . '/lib/AlipayService.php';

mnbt_plugin_register('alipay_official', [
	'name'        => '支付宝官方',
	'description' => '支付宝官方 API 直连，PC + 当面付',
	'icon'        => 'mdi-alpha-a-circle',
]);

// ============================================================
//  配置读取辅助
// ============================================================
function alipay_official_get_config()
{
	return [
		'app_id'      => trim((string)mnbt_plugin_option_get('alipay_official', 'app_id', '')),
		'private_key' => trim((string)mnbt_plugin_option_get('alipay_official', 'private_key', '')),
		'public_key'  => trim((string)mnbt_plugin_option_get('alipay_official', 'public_key', '')),
		'gateway'     => trim((string)mnbt_plugin_option_get('alipay_official', 'gateway', '')),
	];
}

function alipay_official_is_configured()
{
	$c = alipay_official_get_config();
	return $c['app_id'] !== '' && $c['private_key'] !== '' && $c['public_key'] !== '';
}

function alipay_official_get_service()
{
	if (!alipay_official_is_configured()) {
		return null;
	}
	return new Alipay_Official_Service(alipay_official_get_config());
}

// ============================================================
//  注册支付插件
// ============================================================
mnbt_register_payment('alipay_official', [
	'name'        => '支付宝官方',
	'description' => '支付宝官方 API 直连（RSA2）',
	'icon'        => 'mdi-alpha-a-circle',
	'methods'     => [
		'pc'      => ['name' => '支付宝（电脑网站）', 'icon' => 'mdi-desktop-mac'],
		'qrcode'  => ['name' => '支付宝（扫码）',     'icon' => 'mdi-qrcode-scan'],
	],
	'build' => function ($method, $order, $plugin_config) {
		$svc = alipay_official_get_service();
		if (!$svc) {
			error_log('[MNBT alipay_official] 未配置 app_id/private_key/public_key，无法发起支付');
			return false;
		}
		$siteurl = isset($order['siteurl']) ? $order['siteurl'] : '';
		$siteurl = rtrim((string)$siteurl, '/') . '/';
		$notifyUrl = $siteurl . 'pay/alipay_official/notify';
		$returnUrl = $siteurl . 'pay/alipay_official/return';

		$params = [
			'out_trade_no' => $order['out_trade_no'],
			'total_amount' => $order['money'],
			'subject'      => $order['name'],
			'notify_url'   => $notifyUrl,
		];

		if ($method === 'pc') {
			$params['return_url'] = $returnUrl;
			return $svc->buildPagePay($params);
		}

		if ($method === 'qrcode') {
			$result = $svc->buildPrecreate($params);
			if (empty($result['ok']) || empty($result['qr_code'])) {
				error_log('[MNBT alipay_official] precreate 失败: ' . json_encode($result['raw'], JSON_UNESCAPED_UNICODE));
				return alipay_official_render_qr_error($result);
			}
			return alipay_official_render_qr_page($order, $result['qr_code'], $returnUrl);
		}

		return false;
	},
]);

/**
 * 渲染扫码支付页面（HTML）。
 */
function alipay_official_render_qr_page($order, $qrCode, $returnUrl)
{
	$amount   = htmlspecialchars((string)$order['money'], ENT_QUOTES, 'UTF-8');
	$subject  = htmlspecialchars((string)$order['name'], ENT_QUOTES, 'UTF-8');
	$orderno  = htmlspecialchars((string)$order['out_trade_no'], ENT_QUOTES, 'UTF-8');
	$qrEscape = htmlspecialchars($qrCode, ENT_QUOTES, 'UTF-8');
	$qrImg    = 'https://api.qrserver.com/v1/create-qr-code/?size=240x240&data=' . rawurlencode($qrCode);
	$returnEsc = htmlspecialchars($returnUrl, ENT_QUOTES, 'UTF-8');

	return <<<HTML
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
<title>支付宝扫码支付</title>
<style>
*{box-sizing:border-box;margin:0;padding:0;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI","PingFang SC","Microsoft YaHei",sans-serif;}
body{background:#f5f7fa;color:#1e293b;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:16px;}
.qr-card{background:#fff;border-radius:16px;box-shadow:0 4px 24px rgba(15,23,42,.08);max-width:380px;width:100%;padding:28px 24px;text-align:center;}
.qr-card h1{font-size:18px;font-weight:600;margin-bottom:6px;color:#0f172a;}
.qr-card .sub{font-size:13px;color:#64748b;margin-bottom:20px;}
.qr-card .amount{font-size:28px;font-weight:600;color:#1677ff;margin:8px 0 16px;}
.qr-card .amount small{font-size:14px;font-weight:400;color:#64748b;}
.qr-img-wrap{background:#fafbfc;border:1px solid #eef1f5;border-radius:12px;padding:16px;margin-bottom:18px;display:flex;justify-content:center;}
.qr-img-wrap img{width:240px;height:240px;display:block;}
.qr-tip{font-size:12px;color:#94a3b8;margin-bottom:18px;}
.qr-tip b{color:#475569;}
.qr-btn{display:inline-block;background:#1677ff;color:#fff;text-decoration:none;padding:10px 24px;border-radius:8px;font-size:14px;font-weight:500;transition:background .15s;}
.qr-btn:hover{background:#0958d9;}
.orderno{font-size:11px;color:#cbd5e1;margin-top:14px;word-break:break-all;}
</style>
</head>
<body>
<div class="qr-card">
  <h1>支付宝扫码支付</h1>
  <div class="sub">{$subject}</div>
  <div class="amount">¥{$amount}</div>
  <div class="qr-img-wrap">
    <img src="{$qrImg}" alt="支付二维码">
  </div>
  <div class="qr-tip">请使用 <b>支付宝</b> 扫描上方二维码完成支付<br>支付完成后请点击下方按钮返回</div>
  <a href="{$returnEsc}" class="qr-btn">我已完成支付</a>
  <div class="orderno">订单号：{$orderno}</div>
</div>
</body>
</html>
HTML;
}

/**
 * 渲染扫码失败页面（HTML）。
 */
function alipay_official_render_qr_error($result)
{
	$raw = is_array($result['raw'] ?? null) ? $result['raw'] : [];
	$msg = $raw['msg'] ?? ($raw['error'] ?? '未知错误');
	$sub = $raw['sub_msg'] ?? '';
	$msgEsc  = htmlspecialchars((string)$msg, ENT_QUOTES, 'UTF-8');
	$subEsc  = htmlspecialchars((string)$sub, ENT_QUOTES, 'UTF-8');

	return <<<HTML
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>支付发起失败</title>
<style>
body{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI","PingFang SC","Microsoft YaHei",sans-serif;background:#f5f7fa;color:#1e293b;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:16px;}
.err-card{background:#fff;border-radius:12px;box-shadow:0 4px 24px rgba(15,23,42,.08);max-width:420px;width:100%;padding:24px;text-align:center;}
.err-card h1{font-size:18px;color:#b91c1c;margin-bottom:10px;}
.err-card p{font-size:13px;color:#64748b;margin-bottom:6px;}
.err-card .back{display:inline-block;margin-top:14px;color:#1677ff;text-decoration:none;font-size:14px;}
</style>
</head>
<body>
<div class="err-card">
  <h1>支付发起失败</h1>
  <p>{$msgEsc}</p>
  {$subEsc ? "<p>{$subEsc}</p>" : ''}
  <a href="javascript:history.back()" class="back">返回上一页</a>
</div>
</body>
</html>
HTML;
}

// ============================================================
//  回调路由：/pay/alipay_official/notify（异步通知）
// ============================================================
mnbt_register_route('*', '/pay/alipay_official/notify', function ($params, $ctx) {
	@header('Content-Type: text/plain; charset=UTF-8');
	$svc = alipay_official_get_service();
	if (!$svc) {
		echo 'fail';
		return;
	}
	$post = $_POST;
	if (empty($post)) {
		mnbt_pay_log('支付宝官方异步通知无数据', '回调异常', '');
		echo 'fail';
		return;
	}
	if (!$svc->verifyNotify($post)) {
		mnbt_pay_log('支付宝官方异步通知验签失败', '验签失败', $post['out_trade_no'] ?? '');
		echo 'fail';
		return;
	}
	$outTradeNo  = isset($post['out_trade_no']) ? (string)$post['out_trade_no'] : '';
	$tradeStatus = isset($post['trade_status']) ? (string)$post['trade_status'] : '';
	$totalAmount = isset($post['total_amount']) ? (string)$post['total_amount'] : '';
	if ($outTradeNo === '') {
		echo 'fail';
		return;
	}
	$result = mnbt_pay_settle_order($outTradeNo, $tradeStatus, $totalAmount);
	if (!empty($result['ok'])) {
		echo 'success';
	} else {
		echo 'fail';
	}
});

// ============================================================
//  回调路由：/pay/alipay_official/return（同步返回）
// ============================================================
mnbt_register_route('*', '/pay/alipay_official/return', function ($params, $ctx) {
	$base = isset($ctx['base']) ? $ctx['base'] : '';
	// 同步返回仅作展示，订单最终状态以异步通知为准
	@header('Location: ' . $base . '/user');
});

// ============================================================
//  后台设置页
// ============================================================
mnbt_register_page('admin', 'settings', 'admin/settings.php', '支付宝官方设置');

mnbt_register_settings_tab([
	'title' => '支付宝官方设置',
	'page'  => 'settings',
	'order' => 20,
]);

mnbt_register_menu('admin', [
	'title' => '支付宝官方设置',
	'page'  => 'settings',
	'icon'  => 'mdi-alpha-a-circle',
	'order' => 61,
	'multitabs' => true,
]);

// ============================================================
//  AJAX：保存配置
// ============================================================
mnbt_register_ajax('admin', 'alipay_official_save', function () {
	mnbt_plugin_require_admin();
	$appId      = isset($_POST['app_id']) ? trim((string)$_POST['app_id']) : '';
	$privateKey = isset($_POST['private_key']) ? trim((string)$_POST['private_key']) : '';
	$publicKey  = isset($_POST['public_key']) ? trim((string)$_POST['public_key']) : '';
	$gateway    = isset($_POST['gateway']) ? trim((string)$_POST['gateway']) : '';
	if (mb_strlen($appId) > 60)      json_exit('APPID 过长');
	if (mb_strlen($gateway) > 200)   json_exit('网关地址过长');
	if (mb_strlen($privateKey) > 4000) json_exit('私钥过长');
	if (mb_strlen($publicKey) > 4000)  json_exit('公钥过长');
	mnbt_plugin_option_set('alipay_official', 'app_id', $appId);
	mnbt_plugin_option_set('alipay_official', 'private_key', $privateKey);
	mnbt_plugin_option_set('alipay_official', 'public_key', $publicKey);
	mnbt_plugin_option_set('alipay_official', 'gateway', $gateway);
	json_exit('保存成功');
});
