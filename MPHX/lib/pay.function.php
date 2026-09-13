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

		// 原子翻转订单状态：并发回调下只有 affected_rows>0 的一方获得处理权，防止重复触发 order.paid
		// （query_prepare 返回值仅表示执行成功与否，受影响行数需经 $DB->affected() 获取）
		$DB->query_prepare("update `MN_dd` set `qk` = 'true' where `ddh` = ? and `qk` <> 'true'", [$out_trade_no]);
		if ((int)$DB->affected() === 0) {
			// 翻转失败说明订单已被其他并发请求处理，直接返回成功让网关停止重发，不触发任何业务钩子
			mnbt_pay_log('订单已被其他请求处理，跳过', '重复回调', $out_trade_no);
			return ['ok' => true, 'msg' => '该订单已被系统处理'];
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
		// ymgm（域名购买）业务已迁移至 domain_shop 插件，由 order.paid 钩子处理
		} else {
			// 其他业务类型（如余额充值）：核心不处理具体业务，仅标记订单完成并触发 order.paid 钩子，
			// 由对应插件（如 balance 插件）在 order.paid 回调中处理。
			mnbt_pay_log('扩展业务类型 ' . $ddxx['lx'] . '，交由 order.paid 钩子处理', '处理成功', $out_trade_no);
		}

		// 订单状态已在业务处理前原子翻转为 'true'，此处无需再更新
		mnbt_pay_log('订单处理完成 类型' . $ddxx['lx'] . ' 金额' . $money, '处理成功', $out_trade_no);
		$order_row = $DB->get_row_prepare("SELECT * FROM MN_dd WHERE ddh=? limit 1", [$out_trade_no]);
		if (function_exists('mnbt_do_action')) {
			mnbt_do_action('order.paid', $order_row ?: $ddxx, ['money' => $money, 'source' => 'pay_plugin']);
		}
		return ['ok' => true, 'msg' => '支付成功'];
	}
}
