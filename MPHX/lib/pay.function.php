<?php
/**
 * 支付公共函数（V1.81 P3）
 *
 * 订单结算逻辑（原 user/notify_url.php / return_url.php 中的处理），
 * 供支付插件回调验签后统一调用。
 *
 * 依赖：$DB, $conf, $date（由调用方作用域提供，或通过 global 引入）
 */

if (!function_exists('mnbt_pay_log')) {
	/**
	 * 记录支付日志。
	 * @param string $content
	 * @param string $status
	 * @param string $orderNo
	 * @return mixed
	 */
	function mnbt_pay_log($content, $status = '记录', $orderNo = '')
	{
		global $DB;
		$suffix = $orderNo ? ' 订单' . $orderNo : '';
		return mnbt_log('支付回调', '支付回调', $content . $suffix, $status, $DB);
	}
}

if (!function_exists('mnbt_pay_settle_order')) {
	/**
	 * 处理支付成功的订单。
	 *
	 * 由支付插件在回调验签通过后调用，执行订单的业务逻辑（一键部署/域名购买），
	 * 标记订单完成，并触发 order.paid action。
	 *
	 * @param string $out_trade_no  商户订单号
	 * @param string $trade_status  交易状态（支付宝: TRADE_SUCCESS）
	 * @param string $money         实付金额
	 * @return array ['ok'=>bool, 'msg'=>string]
	 */
	function mnbt_pay_settle_order($out_trade_no, $trade_status, $money)
	{
		global $DB, $conf, $date;

		if ($trade_status != 'TRADE_SUCCESS') {
			mnbt_pay_log('支付状态非成功：' . $trade_status, '回调失败', $out_trade_no);
			return ['ok' => false, 'msg' => 'trade_status=' . $trade_status];
		}

		$ddxx = $DB->get_row_prepare("SELECT * FROM `MN_dd` WHERE `ddh` = ? limit 1", [$out_trade_no]);
		if (!$ddxx) {
			mnbt_pay_log('订单不存在', '回调异常', $out_trade_no);
			return ['ok' => false, 'msg' => '订单不存在'];
		}

		if ((string)$ddxx['qk'] === 'true') {
			mnbt_pay_log('订单重复回调，已处理', '重复回调', $out_trade_no);
			return ['ok' => true, 'msg' => '该订单已被系统处理'];
		}

		if (isset($ddxx['je']) && (string)$ddxx['je'] !== '' && (string)$money !== '' && (float)$ddxx['je'] != (float)$money) {
			mnbt_pay_log('订单金额不一致，应付' . $ddxx['je'] . '实付' . $money, '回调异常', $out_trade_no);
			return ['ok' => false, 'msg' => '订单金额不一致'];
		}

		$ddxx_cs = json_decode($ddxx['cs'], true);
		if (!is_array($ddxx_cs)) {
			mnbt_pay_log('订单参数解析失败', '回调异常', $out_trade_no);
			return ['ok' => false, 'msg' => '订单参数解析失败'];
		}

		if ($ddxx['lx'] == 'yjbs') {
			// 一键部署
			$ddxx_xid = $ddxx_cs['gmid'] ?? 0;
			$bscx = $DB->get_row_prepare("SELECT * FROM `MN_bs` WHERE `id` = ? limit 1", [$ddxx_xid]);
			if (!$bscx) {
				mnbt_pay_log('一键部署程序不存在ID' . $ddxx_xid, '处理失败', $out_trade_no);
				return ['ok' => false, 'msg' => '程序不存在'];
			}
			$bs_tj = json_decode($bscx['tj'], true);
			if (!is_array($bs_tj)) $bs_tj = [];
			if (!in_array($ddxx_cs['user'], $bs_tj)) array_push($bs_tj, $ddxx_cs['user']);
			$tj_jg = json_encode($bs_tj, 256);
			if (!$DB->query_prepare("update `MN_bs` set `tj` =? where `id`=?", [$tj_jg, $ddxx_xid])) {
				mnbt_pay_log('一键部署购买写入失败 用户' . ($ddxx_cs['user'] ?? ''), '处理失败', $out_trade_no);
				return ['ok' => false, 'msg' => '程序购买写入失败'];
			}
			mnbt_pay_log('一键部署购买处理成功 用户' . ($ddxx_cs['user'] ?? ''), '处理成功', $out_trade_no);
		} elseif ($ddxx['lx'] == 'ymgm') {
			// 域名购买
			$ddxx_url = $ddxx_cs['url_zd'] ?? '';
			$user = $ddxx_cs['user'] ?? '';
			$yhc = $DB->get_row_prepare("SELECT * FROM MN_zj WHERE user=? limit 1", [$user]);
			if (!$yhc) {
				mnbt_pay_log('域名购买主机不存在 用户' . $user, '处理失败', $out_trade_no);
				return ['ok' => false, 'msg' => '主机不存在'];
			}
			$zjid = $yhc['btid'];
			$ssbt = $yhc['ssbt'];
			$bscx = $DB->get_row_prepare("SELECT * FROM `MN_ym` WHERE `url` = ? limit 1", [$ddxx_url]);
			if (!$bscx) {
				mnbt_pay_log('域名商品不存在 ' . $ddxx_url, '处理失败', $out_trade_no);
				return ['ok' => false, 'msg' => '域名商品不存在'];
			}
			include_once(__DIR__ . "/../../user/class.php");
			$cert = $DB->get_row_prepare("SELECT * FROM MN_bt WHERE btdh=? limit 1", [$ssbt]);
			if (!$cert) {
				mnbt_pay_log('宝塔节点不存在 ' . $ssbt, '处理失败', $out_trade_no);
				return ['ok' => false, 'msg' => '宝塔节点不存在'];
			}
			$btipe = ($cert['ptl'] == 'true' ? 'https' : 'http') . '://' . $cert['btip'] . ':' . $cert['btdk'];
			$btkeye = $cert['btmy'];
			$os_xt = $cert['btos'] == '1' ? $conf['hxi'] . '/' : $conf['hxo'] . '/';
			$ul_url_ym = $ddxx_cs['url_zy'] ?? '';
			$path = $ddxx_cs['path'] ?? '/';
			$apie = new bt_api_set($btipe, $btkeye);
			$api = new bt_api($btipe, $btkeye);
			if (strpos($path, '/') !== false) {
				$r_data = $apie->btapi_addym($zjid, $yhc['sqldz'], $ul_url_ym);
			} else {
				$r_data = $api->addzml($zjid, $ul_url_ym, $path, $os_xt . $yhc['sqldz']);
			}
			$are = $r_data['status'] ?? false;
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
					$yr_c = ($r_data['status'] ?? false) == 'true' || ($r_data['status'] ?? false) === true ? false : true;
				}
				if ($yr_c) {
					mnbt_pay_log('域名绑定失败 ' . $ul_url_ym, '处理失败', $out_trade_no);
					return ['ok' => false, 'msg' => '域名绑定失败'];
				}
			}

			if (($ddxx_cs['type'] ?? '') == '1') {
				$hhf = "\n";
				$apic = new bt_api($btipe, $btkeye);
				$get_host_hq = $apic->GetLogswt($ddxx_cs['hostly']);
				$host_wjnr = ($get_host_hq['data'] ?? '') . $hhf . $ddxx_cs['yz_ip'] . ' ' . $ul_url_ym;
				$apic->GetLogswh($host_wjnr, $ddxx_cs['hostly']);
				$apic->fxdl_add($ul_url_ym, $yhc['sqldz']);
			}
			$bs_tj = json_decode($bscx['json'], true);
			if (!is_array($bs_tj)) $bs_tj = [];
			if (!in_array($user, $bs_tj)) array_push($bs_tj, $user);
			$tj_jg = json_encode($bs_tj, 256);
			if (!$DB->query_prepare("update `MN_ym` set `json` =? where `url`=?", [$tj_jg, $ddxx_url])) {
				mnbt_pay_log('域名购买记录写入失败 用户' . $user, '处理失败', $out_trade_no);
				return ['ok' => false, 'msg' => '域名购买记录写入失败'];
			}
			mnbt_pay_log('域名购买处理成功 用户' . $user . ' 域名' . $ul_url_ym, '处理成功', $out_trade_no);
		} else {
			// 其他业务类型（如余额充值）：核心不处理具体业务，仅标记订单完成并触发 order.paid 钩子，
			// 由对应插件（如 balance 插件）在 order.paid 回调中处理。
			mnbt_pay_log('扩展业务类型 ' . $ddxx['lx'] . '，交由 order.paid 钩子处理', '处理成功', $out_trade_no);
		}

		if (!$DB->query_prepare("update `MN_dd` set `qk` =? where `ddh`=?", ['true', $out_trade_no])) {
			mnbt_pay_log('订单状态更新失败', '处理失败', $out_trade_no);
			return ['ok' => false, 'msg' => '订单状态更新失败'];
		}
		mnbt_pay_log('订单处理完成 类型' . $ddxx['lx'] . ' 金额' . $money, '处理成功', $out_trade_no);
		$order_row = $DB->get_row_prepare("SELECT * FROM MN_dd WHERE ddh=? limit 1", [$out_trade_no]);
		if (function_exists('mnbt_do_action')) {
			mnbt_do_action('order.paid', $order_row ?: $ddxx, ['money' => $money, 'source' => 'pay_plugin']);
		}
		return ['ok' => true, 'msg' => '支付成功'];
	}
}
