<?php
/**
 * 易支付 SDK（彩虹易支付协议，MD5 签名）
 *
 * V1.81 P3：从原 MPHX/lib/submit.class.php、notify.class.php、
 * core.function.php、md5.function.php 合并而来，仅保留易支付所需的最小集合。
 *
 * 仅本插件内部使用，不污染全局命名空间（类名加 Epay 前缀）。
 */

if (!defined('IN_CRONLITE')) {
	exit;
}

class Epay_Core
{
	/** 生成签名：把参数按 key 升序拼接成 a=1&b=2，末尾拼上 key，做 md5 */
	public static function buildSign(array $params, $key)
	{
		// 去掉 sign / sign_type / 空值
		$filtered = [];
		foreach ($params as $k => $v) {
			if ($k === 'sign' || $k === 'sign_type' || $v === '') {
				continue;
			}
			$filtered[$k] = $v;
		}
		ksort($filtered);
		reset($filtered);
		$prestr = '';
		foreach ($filtered as $k => $v) {
			$prestr .= $k . '=' . $v . '&';
		}
		$prestr = substr($prestr, 0, -1);
		return md5($prestr . $key);
	}

	/** 验证签名 */
	public static function verifySign(array $params, $key, $sign)
	{
		$expected = self::buildSign($params, $key);
		return $expected !== '' && $expected === $sign;
	}

	/**
	 * 构造提交表单 HTML。
	 *
	 * @param string $apiurl  易支付接口地址（如 https://pay.example.com/）
	 * @param string $pid     商户 ID
	 * @param string $key     商户密钥
	 * @param array  $params  业务参数（type/out_trade_no/notify_url/return_url/name/money 等）
	 * @return string  自动提交的 HTML 表单
	 */
	public static function buildForm($apiurl, $pid, $key, array $params)
	{
		$params['pid'] = $pid;
		$sign = self::buildSign($params, $key);
		$params['sign'] = $sign;
		$params['sign_type'] = 'MD5';

		$apiurl = rtrim((string)$apiurl, '/') . '/';
		$gateway = $apiurl . 'submit.php?';

		$html = "<form id='epaysubmit' name='epaysubmit' action='" . htmlspecialchars($gateway, ENT_QUOTES, 'UTF-8') . "' method='POST'>";
		foreach ($params as $k => $v) {
			$html .= "<input type='hidden' name='" . htmlspecialchars($k, ENT_QUOTES, 'UTF-8') . "' value='" . htmlspecialchars($v, ENT_QUOTES, 'UTF-8') . "'/>";
		}
		$html .= "<input type='submit' value='正在跳转'></form>";
		$html .= "<script>document.forms['epaysubmit'].submit();</script>";
		return $html;
	}
}
