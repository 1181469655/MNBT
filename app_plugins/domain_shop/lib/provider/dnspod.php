<?php
/**
 * domain_shop 插件 - DNSPod（腾讯云 API 3.0）适配器
 * 文档：https://cloud.tencent.com/document/api/1427
 *
 * 旧版 DNSPod API（dnsapi.cn + login_token）已停止维护，此处迁移至腾讯云 API 3.0：
 * - 接口域名：dnspod.tencentcloudapi.com，Version=2021-03-23
 * - 鉴权：腾讯云 API 密钥（SecretId + SecretKey），TC3-HMAC-SHA256 签名
 * - 请求：POST + application/json
 */
if (!defined('IN_CRONLITE')) exit;

class DomainShop_DNSPod
{
	private $apiId;       // 腾讯云 SecretId
	private $apiSecret;   // 腾讯云 SecretKey
	private $endpoint = 'https://dnspod.tencentcloudapi.com';
	private $service = 'dnspod';
	private $version = '2021-03-23';
	private $lastErr = '';

	public function __construct($apiId, $apiSecret)
	{
		$this->apiId = (string)$apiId;
		$this->apiSecret = (string)$apiSecret;
	}

	public function lastError()
	{
		return $this->lastErr;
	}

	/**
	 * 取域名列表
	 * @return array [['name'=>..., 'id'=>...], ...]
	 */
	public function listDomains()
	{
		$resp = $this->call('DescribeDomainList', ['Type' => 'ALL', 'Limit' => 1000]);
		if ($resp === false) return [];
		$out = [];
		foreach (($resp['DomainList'] ?? []) as $d) {
			$out[] = ['id' => (string)($d['DomainId'] ?? ''), 'name' => $d['Name'] ?? ''];
		}
		return $out;
	}

	/**
	 * 取某域名下记录列表（字段映射为小写风格，与旧适配器输出保持一致）
	 */
	public function listRecords($domain)
	{
		$resp = $this->call('DescribeRecordList', [
			'Domain' => $domain,
			'Offset' => 0,
			'Limit' => 1000,
			'ErrorOnEmpty' => 'no',
		]);
		if ($resp === false) return [];
		$out = [];
		foreach (($resp['RecordList'] ?? []) as $r) {
			$out[] = [
				'id' => (string)($r['RecordId'] ?? ''),
				'name' => $r['Name'] ?? '',
				'type' => $r['Type'] ?? '',
				'value' => $r['Value'] ?? '',
				'line' => $r['Line'] ?? '',
				'ttl' => $r['TTL'] ?? 0,
				'mx' => $r['MX'] ?? 0,
				'status' => $r['Status'] ?? '',
			];
		}
		return $out;
	}

	/**
	 * 创建记录
	 * @return string|false 远程记录 ID，失败返回 false
	 */
	public function createRecord($domain, $name, $type, $value, $ttl = 600, $mx = 0)
	{
		$params = [
			'Domain' => $domain,
			'SubDomain' => $name,
			'RecordType' => strtoupper($type),
			'RecordLine' => '默认',
			'Value' => $value,
			'TTL' => (int)$ttl,
		];
		if (strtoupper($type) === 'MX') $params['MX'] = (int)$mx;

		$resp = $this->call('CreateRecord', $params);
		if ($resp === false) return false;
		return (string)($resp['RecordId'] ?? '');
	}

	/**
	 * 更新记录
	 */
	public function updateRecord($domain, $recordId, $name, $type, $value, $ttl = 600, $mx = 0)
	{
		$params = [
			'Domain' => $domain,
			'RecordId' => (int)$recordId,
			'SubDomain' => $name,
			'RecordType' => strtoupper($type),
			'RecordLine' => '默认',
			'Value' => $value,
			'TTL' => (int)$ttl,
		];
		if (strtoupper($type) === 'MX') $params['MX'] = (int)$mx;

		$resp = $this->call('ModifyRecord', $params);
		return $resp !== false;
	}

	/**
	 * 删除记录
	 */
	public function deleteRecord($domain, $recordId)
	{
		$resp = $this->call('DeleteRecord', ['Domain' => $domain, 'RecordId' => (int)$recordId]);
		return $resp !== false;
	}

	/**
	 * 调用腾讯云 DNSPod API 3.0
	 * @return array|false 解析后的 Response 数组，失败返回 false（lastErr 记录原因）
	 */
	private function call($action, array $params)
	{
		$timestamp = time();
		$payload = json_encode($params, JSON_UNESCAPED_UNICODE);
		$authorization = $this->sign($action, $payload, $timestamp);

		$headers = [
			'Content-Type: application/json; charset=utf-8',
			'X-TC-Action: ' . $action,
			'X-TC-Timestamp: ' . $timestamp,
			'X-TC-Version: ' . $this->version,
			'Authorization: ' . $authorization,
		];

		// 用插件引擎的 HTTP 出站函数（有内网/协议安全策略），失败回退到 cURL
		if (function_exists('mnbt_http_post')) {
			$res = mnbt_http_post($this->endpoint, $payload, [
				'timeout' => 15,
				'headers' => $headers,
			]);
			if (!empty($res['ok'])) {
				return $this->parseResponse($res['body'] ?? '');
			}
			$this->lastErr = $res['error'] ?? 'mnbt_http_post 失败';
		}

		if (!function_exists('curl_init')) {
			$this->lastErr = 'PHP 未启用 cURL 扩展';
			return false;
		}
		$ch = curl_init($this->endpoint);
		curl_setopt_array($ch, [
			CURLOPT_POST => true,
			CURLOPT_POSTFIELDS => $payload,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_TIMEOUT => 15,
			CURLOPT_HTTPHEADER => $headers,
		]);
		$body = curl_exec($ch);
		$err = curl_error($ch);
		curl_close($ch);
		if ($body === false) {
			$this->lastErr = 'cURL 错误：' . $err;
			return false;
		}
		return $this->parseResponse($body);
	}

	/**
	 * 解析 API 3.0 响应（统一包在 Response 中；存在 Error 视为失败）
	 */
	private function parseResponse($body)
	{
		$arr = json_decode($body, true);
		if (!is_array($arr) || !isset($arr['Response'])) {
			$this->lastErr = '响应解析失败：' . mb_substr((string)$body, 0, 200);
			return false;
		}
		$resp = $arr['Response'];
		if (isset($resp['Error'])) {
			$this->lastErr = ($resp['Error']['Message'] ?? '') ?: ($resp['Error']['Code'] ?? 'API 错误');
			return false;
		}
		return $resp;
	}

	/**
	 * TC3-HMAC-SHA256 签名（腾讯云 API 3.0 签名方法 v3）
	 */
	private function sign($action, $payload, $timestamp)
	{
		$algorithm = 'TC3-HMAC-SHA256';
		$host = 'dnspod.tencentcloudapi.com';
		$date = gmdate('Y-m-d', $timestamp);

		// 1. 拼接规范请求串
		$contentType = 'application/json; charset=utf-8';
		$canonicalHeaders = "content-type:$contentType\nhost:$host\nx-tc-action:" . strtolower($action) . "\n";
		$signedHeaders = 'content-type;host;x-tc-action';
		$hashedRequestPayload = hash('sha256', $payload);
		$canonicalRequest = "POST\n/\n\n$canonicalHeaders\n$signedHeaders\n$hashedRequestPayload";

		// 2. 拼接待签名字符串
		$credentialScope = "$date/{$this->service}/tc3_request";
		$hashedCanonicalRequest = hash('sha256', $canonicalRequest);
		$stringToSign = "$algorithm\n$timestamp\n$credentialScope\n$hashedCanonicalRequest";

		// 3. 计算派生签名密钥与签名
		$secretDate = hash_hmac('sha256', $date, 'TC3' . $this->apiSecret, true);
		$secretService = hash_hmac('sha256', $this->service, $secretDate, true);
		$secretSigning = hash_hmac('sha256', 'tc3_request', $secretService, true);
		$signature = hash_hmac('sha256', $stringToSign, $secretSigning);

		// 4. 拼接 Authorization
		return "$algorithm Credential={$this->apiId}/$credentialScope, SignedHeaders=$signedHeaders, Signature=$signature";
	}
}
