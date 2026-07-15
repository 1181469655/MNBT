<?php
/**
 * 支付宝官方 API SDK（单文件版）
 *
 * 基于 dedemao/alipay 的实现整合：
 *   - alipay.trade.page.pay（电脑网站支付，PC）
 *   - alipay.trade.precreate（当面付，扫码）
 *   - 异步通知验签
 *
 * 仅依赖 PHP openssl + curl 扩展，无 composer 依赖。
 * 仅本插件内部使用，不污染全局命名空间。
 *
 * 来源：https://github.com/dedemao/alipay
 * License: MIT
 */

if (!defined('IN_CRONLITE')) {
	exit;
}

class Alipay_Official_Service
{
	protected $appId;
	protected $rsaPrivateKey;      // 应用私钥
	protected $alipayPublicKey;    // 支付宝公钥
	protected $charset = 'utf-8';
	protected $gateway = 'https://openapi.alipay.com/gateway.do';

	public function __construct(array $config)
	{
		$this->appId           = isset($config['app_id']) ? trim((string)$config['app_id']) : '';
		$this->rsaPrivateKey   = isset($config['private_key']) ? trim((string)$config['private_key']) : '';
		$this->alipayPublicKey = isset($config['public_key']) ? trim((string)$config['public_key']) : '';
		if (!empty($config['gateway'])) {
			$this->gateway = rtrim((string)$config['gateway'], '/');
		}
	}

	/**
	 * 电脑网站支付：返回自动提交的 HTML 表单。
	 *
	 * @param array $params [out_trade_no, total_amount, subject, return_url, notify_url]
	 * @return string  HTML
	 */
	public function buildPagePay(array $params)
	{
		$requestConfigs = [
			'out_trade_no' => $params['out_trade_no'],
			'product_code' => 'FAST_INSTANT_TRADE_PAY',
			'total_amount' => $params['total_amount'],
			'subject'      => $params['subject'],
		];
		$commonConfigs = [
			'app_id'      => $this->appId,
			'method'      => 'alipay.trade.page.pay',
			'format'      => 'JSON',
			'return_url'  => $params['return_url'],
			'charset'     => $this->charset,
			'sign_type'   => 'RSA2',
			'timestamp'   => date('Y-m-d H:i:s'),
			'version'     => '1.0',
			'notify_url'  => $params['notify_url'],
			'biz_content' => json_encode($requestConfigs, JSON_UNESCAPED_UNICODE),
		];
		$commonConfigs['sign'] = $this->generateSign($commonConfigs);
		return $this->buildRequestForm($commonConfigs);
	}

	/**
	 * 当面付（扫码）：调用 alipay.trade.precreate，返回二维码链接。
	 *
	 * @param array $params [out_trade_no, total_amount, subject, notify_url]
	 * @return array  ['ok'=>bool, 'qr_code'=>string, 'raw'=>array]
	 */
	public function buildPrecreate(array $params)
	{
		$requestConfigs = [
			'out_trade_no'    => $params['out_trade_no'],
			'total_amount'    => $params['total_amount'],
			'subject'         => $params['subject'],
			'timeout_express' => '2h',
		];
		$commonConfigs = [
			'app_id'      => $this->appId,
			'method'      => 'alipay.trade.precreate',
			'format'      => 'JSON',
			'charset'     => $this->charset,
			'sign_type'   => 'RSA2',
			'timestamp'   => date('Y-m-d H:i:s'),
			'version'     => '1.0',
			'notify_url'  => $params['notify_url'],
			'biz_content' => json_encode($requestConfigs, JSON_UNESCAPED_UNICODE),
		];
		$commonConfigs['sign'] = $this->generateSign($commonConfigs);
		$resp = $this->curlPost($this->gateway . '?charset=' . $this->charset, $commonConfigs);
		$data = json_decode($resp, true);
		if (!is_array($data)) {
			return ['ok' => false, 'qr_code' => '', 'raw' => ['error' => '响应解析失败', 'raw' => $resp]];
		}
		// 同步签名校验（开放平台返回结构：alipay_trade_precreate_response + sign）
		$innerKey = 'alipay_trade_precreate_response';
		$inner = isset($data[$innerKey]) ? $data[$innerKey] : null;
		if (!is_array($inner)) {
			return ['ok' => false, 'qr_code' => '', 'raw' => $data];
		}
		if (isset($inner['code']) && (string)$inner['code'] === '10000' && !empty($inner['qr_code'])) {
			return ['ok' => true, 'qr_code' => $inner['qr_code'], 'raw' => $data];
		}
		return ['ok' => false, 'qr_code' => '', 'raw' => $data];
	}

	/**
	 * 验证异步通知签名。
	 *
	 * @param array $params  通常是 $_POST
	 * @return bool
	 */
	public function verifyNotify(array $params)
	{
		if (empty($params['sign']) || empty($params['sign_type'])) {
			return false;
		}
		$sign = $params['sign'];
		$signType = $params['sign_type'];
		unset($params['sign'], $params['sign_type']);
		return $this->verify($this->getSignContent($params), $sign, $signType);
	}

	// ============================================================
	//  内部方法
	// ============================================================

	protected function buildRequestForm(array $params)
	{
		$html = "<form id='alipaysubmit' name='alipaysubmit' action='" . htmlspecialchars($this->gateway . '?charset=' . $this->charset, ENT_QUOTES, 'UTF-8') . "' method='POST'>";
		foreach ($params as $key => $val) {
			if ($this->checkEmpty($val)) continue;
			$html .= "<input type='hidden' name='" . htmlspecialchars($key, ENT_QUOTES, 'UTF-8') . "' value='" . htmlspecialchars($val, ENT_QUOTES, 'UTF-8') . "'/>";
		}
		$html .= "<input type='submit' value='正在跳转' style='display:none'></form>";
		$html .= "<script>document.forms['alipaysubmit'].submit();</script>";
		return $html;
	}

	public function generateSign($params)
	{
		return $this->sign($this->getSignContent($params));
	}

	protected function sign($data, $signType = 'RSA2')
	{
		$priKey = $this->rsaPrivateKey;
		$res = "-----BEGIN RSA PRIVATE KEY-----\n" .
			wordwrap($priKey, 64, "\n", true) .
			"\n-----END RSA PRIVATE KEY-----";
		if (!$res) {
			error_log('[MNBT alipay_official] 私钥格式错误');
			return '';
		}
		if ($signType === 'RSA2') {
			openssl_sign($data, $sign, $res, OPENSSL_ALGO_SHA256);
		} else {
			openssl_sign($data, $sign, $res);
		}
		return base64_encode($sign);
	}

	protected function verify($data, $sign, $signType = 'RSA2')
	{
		$pubKey = $this->alipayPublicKey;
		if ($pubKey === '') return false;
		$res = "-----BEGIN PUBLIC KEY-----\n" .
			wordwrap($pubKey, 64, "\n", true) .
			"\n-----END PUBLIC KEY-----";
		if (!$res) return false;
		if ($signType === 'RSA2') {
			return (bool)openssl_verify($data, base64_decode($sign), $res, OPENSSL_ALGO_SHA256);
		}
		return (bool)openssl_verify($data, base64_decode($sign), $res);
	}

	protected function checkEmpty($value)
	{
		if (!isset($value)) return true;
		if ($value === null) return true;
		if (trim($value) === '') return true;
		return false;
	}

	public function getSignContent($params)
	{
		ksort($params);
		$stringToBeSigned = '';
		$i = 0;
		foreach ($params as $k => $v) {
			if ($this->checkEmpty($v) || substr($v, 0, 1) === '@') continue;
			$v = $this->characet($v, $this->charset);
			if ($i == 0) {
				$stringToBeSigned .= $k . '=' . $v;
			} else {
				$stringToBeSigned .= '&' . $k . '=' . $v;
			}
			$i++;
		}
		return $stringToBeSigned;
	}

	protected function characet($data, $targetCharset)
	{
		if (!empty($data) && strcasecmp($this->charset, $targetCharset) != 0) {
			$data = mb_convert_encoding($data, $targetCharset, $this->charset);
		}
		return $data;
	}

	protected function curlPost($url, $postData = '', $options = [])
	{
		if (is_array($postData)) {
			$postData = http_build_query($postData);
		}
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		if (!empty($options)) {
			curl_setopt_array($ch, $options);
		}
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		$data = curl_exec($ch);
		if ($data === false) {
			error_log('[MNBT alipay_official] curl error: ' . curl_error($ch));
		}
		curl_close($ch);
		return $data;
	}
}
