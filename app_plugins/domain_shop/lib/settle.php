<?php
/**
 * domain_shop 插件 - ymgm 订单结算
 * 迁移自 MPHX/lib/pay.function.php 的 $ddxx['lx']=='ymgm' 分支
 *
 * 由 order.paid 钩子触发，处理：
 * 1. 调宝塔 API 绑定域名到用户主机（所有通道都做）
 * 2. 共享主机（type==1）改 hosts 文件 + 反向代理（所有通道都做）
 * 3. 写 plg_domain_product.json 购买者列表（所有通道都做）
 * 4. 根据 channel 分支：
 *    - pan（泛解析）：不调 DNS API，依赖域名整体泛 A 记录到节点 IP
 *    - dnsapi：通过商品的 provider_id 调对应 DNS 服务商 API 建 A 记录指向节点 IP
 */
if (!defined('IN_CRONLITE')) exit;

function domain_shop_settle_ymgm($order, $ctx = [])
{
	global $DB, $date, $conf;

	if (!is_array($order)) return;
	$out_trade_no = $order['ddh'] ?? '';
	$ddxx_cs = json_decode($order['cs'] ?? '{}', true);
	if (!is_array($ddxx_cs)) $ddxx_cs = [];

	$ddxx_url = $ddxx_cs['url_zd'] ?? '';   // 一级域名
	$user = $ddxx_cs['user'] ?? '';
	$yhc = $DB->get_row_prepare("SELECT * FROM MN_zj WHERE user=? limit 1", [$user]);
	if (!$yhc) {
		if (function_exists('mnbt_pay_log')) {
			mnbt_pay_log('域名购买主机不存在 用户' . $user, '处理失败', $out_trade_no);
		}
		return;
	}
	$zjid = $yhc['btid'];
	$ssbt = $yhc['ssbt'];

	// 查商品（plg_domain_product）
	$bscx = domain_product_get_by_url($ddxx_url);
	if (!$bscx) {
		if (function_exists('mnbt_pay_log')) {
			mnbt_pay_log('域名商品不存在 ' . $ddxx_url, '处理失败', $out_trade_no);
		}
		return;
	}

	include_once __DIR__ . "/../../user/class.php";
	$cert = $DB->get_row_prepare("SELECT * FROM MN_bt WHERE btdh=? limit 1", [$ssbt]);
	if (!$cert) {
		if (function_exists('mnbt_pay_log')) {
			mnbt_pay_log('宝塔节点不存在 ' . $ssbt, '处理失败', $out_trade_no);
		}
		return;
	}
	$btipe = ($cert['ptl'] == 'true' ? 'https' : 'http') . '://' . $cert['btip'] . ':' . $cert['btdk'];
	$btkeye = $cert['btmy'];
	$os_xt = $cert['btos'] == '1' ? $conf['hxi'] . '/' : $conf['hxo'] . '/';

	$ul_url_ym = $ddxx_cs['url_zy'] ?? '';
	$path = $ddxx_cs['path'] ?? '/';
	$apie = new bt_api_set($btipe, $btkeye);
	$api  = new bt_api($btipe, $btkeye);

	// 调宝塔 API 绑定域名
	if (strpos($path, '/') !== false) {
		$r_data = $apie->btapi_addym($zjid, $yhc['sqldz'], $ul_url_ym);
	} else {
		$r_data = $api->addzml($zjid, $ul_url_ym, $path, $os_xt . $yhc['sqldz']);
	}
	$are = $r_data['status'] ?? false;

	// 失败重试：随机化前缀（与原核心逻辑一致）
	if ($are != 'true' && $are !== true) {
		$yr_c = true;
		for ($yr_a = 1; $yr_c && $yr_a <= 20; $yr_a++) {
			$hskr = mt_rand(4, 10);
			$rqsj = md5($date . $user . $yr_a . mt_rand(100, 999));
			$wjler = substr($rqsj, $hskr, 5);
			$ul_url_ym = $wjler . '.' . $ddxx_cs['url_zd'];
			if (strpos($path, '/') !== false) {
				$r_data = $apie->btapi_addym($zjid, $yhc['sqldz'], $ul_url_ym);
			} else {
				$r_data = $api->addzml($zjid, $ul_url_ym, $path, $os_xt . $yhc['sqldz']);
			}
			$yr_c = (($r_data['status'] ?? false) == 'true' || ($r_data['status'] ?? false) === true) ? false : true;
		}
		if ($yr_c) {
			if (function_exists('mnbt_pay_log')) {
				mnbt_pay_log('域名绑定失败 ' . $ul_url_ym, '处理失败', $out_trade_no);
			}
			return;
		}
	}

	// 共享主机特殊处理（原核心逻辑：改 hosts 文件 + 反向代理）
	if (($ddxx_cs['type'] ?? '') == '1') {
		$hhf = "\n";
		$apic = new bt_api($btipe, $btkeye);
		$get_host_hq = $apic->GetLogswt($ddxx_cs['hostly']);
		$host_wjnr = ($get_host_hq['data'] ?? '') . $hhf . $ddxx_cs['yz_ip'] . ' ' . $ul_url_ym;
		$apic->GetLogswh($host_wjnr, $ddxx_cs['hostly']);
		$apic->fxdl_add($ul_url_ym, $yhc['sqldz']);
	}

	// 写 plg_domain_product.json 购买者列表
	$bs_tj = json_decode($bscx['json'], true);
	if (!is_array($bs_tj)) $bs_tj = [];
	if (!in_array($user, $bs_tj)) array_push($bs_tj, $user);
	$tj_jg = json_encode($bs_tj, 256);
	if (!$DB->query_prepare("update `plg_domain_product` set `json`=? where `url`=?", [$tj_jg, $ddxx_url])) {
		if (function_exists('mnbt_pay_log')) {
			mnbt_pay_log('域名购买记录写入失败 用户' . $user, '处理失败', $out_trade_no);
		}
		return;
	}

	// 【通道分支】根据商品的 channel 决定是否调 DNS API 建 A 记录
	// - pan（泛解析）：不调 DNS API，依赖域名整体泛解析到节点 IP
	// - dnsapi：通过商品的 provider_id 调对应 DNS 服务商 API 建 A 记录
	$channel = $bscx['channel'] ?? 'pan';
	$providerId = (int)($bscx['provider_id'] ?? 0);

	if ($channel === 'dnsapi' && $providerId > 0) {
		// 主机记录：购买时使用的前缀（url_qz），如果随机化了则用实际前缀
		$prefix = explode('.', $ul_url_ym)[0];
		$recordName = ($prefix === $ddxx_cs['url_qz']) ? $ddxx_cs['url_qz'] : $prefix;
		$ip = $cert['btip'];
		if ($ip) {
			$r = dns_record_create($user, $providerId, $ddxx_url, $recordName, 'A', $ip, 600, 1);
			if (function_exists('mnbt_pay_log') && empty($r['ok'])) {
				mnbt_pay_log('DNS A 记录自动创建失败：' . ($r['msg'] ?? ''), '处理警告', $out_trade_no);
			}
		}
	}

	if (function_exists('mnbt_pay_log')) {
		mnbt_pay_log('域名购买处理成功 用户' . $user . ' 域名' . $ul_url_ym . '（通道：' . $channel . '）', '处理成功', $out_trade_no);
	}
}
