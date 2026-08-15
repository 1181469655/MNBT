<?php
/**
 * 魔方财务 (cube_finance / 智简魔方财务) API 客户端
 *
 * 改编自 app_plugins/zjmfmanager_reserve/example/sdk/CubeFinanceClient.php。
 * 认证流程与魔方云 v10 上游对接一致：
 *   POST /zjmf_api_login  →  JWT
 *   Authorization: Bearer {jwt}
 *
 * 适用于 PHP 7.2+，仅依赖 ext-curl / ext-json。
 * 返回 status=401/405 时自动强制重登并重试一次。
 */

if (!defined('IN_CRONLITE')) {
	exit;
}

if (!class_exists('CubeFinanceException', false)) {

class CubeFinanceException extends Exception
{
	/** @var array|null */
	public $response;

	public function __construct($message, $code = 0, $response = null)
	{
		parent::__construct($message, (int)$code);
		$this->response = $response;
	}
}

}

if (!class_exists('CubeFinanceClient', false)) {

class CubeFinanceClient
{
	/** @var string */
	protected $baseUrl;

	/** @var string */
	protected $username;

	/** @var string */
	protected $password;

	/** @var string|null */
	protected $jwt;

	/** @var int */
	protected $timeout = 30;

	/** @var string|null JWT 文件缓存目录，null 则仅内存缓存 */
	protected $cacheDir;

	/** @var int JWT 缓存秒数，默认 2 小时（与 v10 一致） */
	protected $jwtTtl = 7200;

	/** @var bool */
	protected $verifySsl = false;

	/**
	 * @param array $config
	 *  - url        string  魔方财务站点根地址
	 *  - username   string  API 账号（客户用户名）
	 *  - password   string  API 密钥（客户 API 密码）
	 *  - timeout    int     请求超时秒数
	 *  - cache_dir  string  JWT 缓存目录（可写）
	 *  - jwt_ttl    int     JWT 缓存时长
	 *  - verify_ssl bool    是否校验 HTTPS 证书
	 *  - jwt        string  已有 JWT（可选，跳过登录）
	 */
	public function __construct(array $config)
	{
		if (empty($config['url'])) {
			throw new InvalidArgumentException('缺少 url');
		}
		$this->baseUrl = rtrim($config['url'], '/');
		$this->username = isset($config['username']) ? (string)$config['username'] : '';
		$this->password = isset($config['password']) ? (string)$config['password'] : '';
		$this->timeout = isset($config['timeout']) ? (int)$config['timeout'] : 30;
		$this->cacheDir = isset($config['cache_dir']) ? rtrim($config['cache_dir'], '/\\') : null;
		$this->jwtTtl = isset($config['jwt_ttl']) ? (int)$config['jwt_ttl'] : 7200;
		$this->verifySsl = !empty($config['verify_ssl']);
		if (!empty($config['jwt'])) {
			$this->jwt = (string)$config['jwt'];
		}
	}

	/**
	 * 登录获取 JWT（会写入缓存）。
	 *
	 * @param bool $force 强制重新登录
	 * @return string
	 * @throws CubeFinanceException
	 */
	public function login($force = false)
	{
		if (!$force) {
			$cached = $this->getCachedJwt();
			if ($cached) {
				$this->jwt = $cached;
				return $this->jwt;
			}
		}

		if ($this->username === '' || $this->password === '') {
			throw new CubeFinanceException('缺少 username 或 password，无法登录');
		}

		$raw = $this->rawRequest('POST', 'zjmf_api_login', [
			'username' => $this->username,
			'password' => $this->password,
		], false);

		$result = $this->decodeJson($raw);
		if (!isset($result['status']) || (int)$result['status'] !== 200 || empty($result['jwt'])) {
			$msg = isset($result['msg']) ? $result['msg'] : '登录失败';
			throw new CubeFinanceException($msg, isset($result['status']) ? (int)$result['status'] : 400, $result);
		}

		$this->jwt = $result['jwt'];
		$this->setCachedJwt($this->jwt);
		return $this->jwt;
	}

	/**
	 * 当前 JWT（未登录则自动登录）。
	 *
	 * @return string
	 */
	public function getJwt()
	{
		if ($this->jwt) {
			return $this->jwt;
		}
		return $this->login();
	}

	/**
	 * 通用请求（自动附带 Bearer，401/405 时强制重登一次）。
	 *
	 * @param string $method GET|POST|PUT|DELETE
	 * @param string $path   相对路径，如 api/product/list
	 * @param array  $data
	 * @return array
	 * @throws CubeFinanceException
	 */
	public function request($method, $path, array $data = [])
	{
		$this->getJwt();
		$raw = $this->rawRequest($method, $path, $data, true);
		$result = $this->decodeJson($raw);

		if (isset($result['status']) && in_array((int)$result['status'], [401, 405], true)) {
			$this->login(true);
			$raw = $this->rawRequest($method, $path, $data, true);
			$result = $this->decodeJson($raw);
			if (isset($result['status']) && (int)$result['status'] === 401) {
				throw new CubeFinanceException(
					isset($result['msg']) ? $result['msg'] : 'API 账号或密码错误',
					401,
					$result
				);
			}
		}

		return $result;
	}

	public function get($path, array $data = [])
	{
		return $this->request('GET', $path, $data);
	}

	public function post($path, array $data = [])
	{
		return $this->request('POST', $path, $data);
	}

	public function put($path, array $data = [])
	{
		return $this->request('PUT', $path, $data);
	}

	public function delete($path, array $data = [])
	{
		return $this->request('DELETE', $path, $data);
	}

	// ==================== 业务封装 ====================

	/**
	 * 测试连通性：登录并拉取商品列表。
	 *
	 * @return array{ok:bool,msg:string,jwt?:string,product_count?:int,raw?:array}
	 */
	public function testConnection()
	{
		try {
			$jwt = $this->login(true);
			$list = $this->productList();
			$count = 0;
			if (isset($list['data']['list']) && is_array($list['data']['list'])) {
				$count = count($list['data']['list']);
			}
			return [
				'ok'            => true,
				'msg'           => '连接成功',
				'jwt'           => substr($jwt, 0, 24) . '...',
				'product_count' => $count,
				'raw'           => $list,
			];
		} catch (Exception $e) {
			return [
				'ok'  => false,
				'msg' => $e->getMessage(),
			];
		}
	}

	/**
	 * 商品列表。
	 * GET /api/product/list
	 *
	 * @return array status/msg/data.list/data.currency_code
	 */
	public function productList()
	{
		return $this->get('api/product/list');
	}

	/**
	 * 商品详情。
	 * GET /api/product/{id}?price_basis=agent
	 *
	 * @param int    $productId
	 * @param string $priceBasis agent|cost 等
	 * @return array
	 */
	public function productDetail($productId, $priceBasis = 'agent')
	{
		return $this->get('api/product/' . intval($productId), [
			'price_basis' => $priceBasis,
		]);
	}

	/**
	 * 购物车配置 / 价格试算。
	 * GET cart/set_config
	 *
	 * @param array $params 通常含 pid、billingcycle、配置项等
	 * @return array
	 */
	public function cartSetConfig(array $params)
	{
		return $this->get('cart/set_config', $params);
	}

	/**
	 * 购物车计算总价（官方价格计算接口）。
	 * POST /cart/get_total
	 *
	 * @param array $params 通常含 pid、billingcycle、qty、configoption 等
	 * @return array
	 */
	public function cartGetTotal(array $params)
	{
		return $this->post('cart/get_total', $params);
	}

	/**
	 * 购物车产品配置（含该产品可选周期/价格，用于探测真实 billingcycle 键）。
	 * GET /cart/get_product_config?pid={pid}
	 *
	 * @param int $productId
	 * @return array
	 */
	public function cartGetProductConfig($productId)
	{
		return $this->get('cart/get_product_config', [
			'pid' => (int)$productId,
		]);
	}

	/**
	 * 清空购物车。
	 * POST /cart/clear
	 *
	 * @return array
	 */
	public function cartClear()
	{
		return $this->post('cart/clear', []);
	}

	/**
	 * 购物车页面数据（含购物车产品列表，数组键/序号即位置 i）。
	 * GET /cart/get_shop_data
	 *
	 * @return array
	 */
	public function cartGetShopData()
	{
		return $this->get('cart/get_shop_data');
	}

	/**
	 * 前台首页数据（含客户余额 data.client[].credit）。
	 * GET /index
	 *
	 * @return array
	 */
	public function homeIndex()
	{
		return $this->get('index');
	}

	/**
	 * 账户信息 / 余额。
	 * GET user_info
	 *
	 * @return array
	 */
	public function userInfo()
	{
		return $this->get('user_info');
	}

	/**
	 * 主机头信息。
	 * GET host/header?host_id={id}
	 *
	 * @param int $hostId 上游主机 ID
	 * @return array
	 */
	public function hostHeader($hostId)
	{
		return $this->get('host/header', ['host_id' => (int)$hostId]);
	}

	/** 产品内页数据（GET host/product），状态/账号等字段更稳定。 */
	public function hostProduct($hostId)
	{
		return $this->get('host/product', ['host_id' => (int)$hostId]);
	}

	/**
	 * 独服/云服务器详情（含重装可选系统列表 cloud_os / cloud_os_group）。
	 * GET host/dedicatedserver?host_id={hostId}
	 *
	 * 实测 host/cloudos 无法返回系统列表，该端点是重装系统的可靠来源。
	 *
	 * @param int $hostId 上游主机 ID
	 * @return array
	 */
	public function hostDedicatedServer($hostId)
	{
		return $this->get('host/dedicatedserver', ['host_id' => (int)$hostId]);
	}

	/**
	 * 我的主机列表。
	 * GET host/list
	 *
	 * @param array $extra 如 groupid/orderby/sort/show_type
	 * @return array
	 */
	public function hostList(array $extra = [])
	{
		return $this->get('host/list', $extra);
	}

	/**
	 * 流量使用。
	 * GET host/trafficusage
	 *
	 * @param int   $hostId
	 * @param array $extra 如 start/end
	 * @return array
	 */
	public function hostTrafficUsage($hostId, array $extra = [])
	{
		$data = array_merge(['id' => intval($hostId)], $extra);
		return $this->get('host/trafficusage', $data);
	}

	/**
	 * 开通模块默认操作（开关机/重启/重装/重置密码等）。
	 * POST provision/default
	 *
	 * @param array $params 含 id(host_id)、func 等
	 * @return array
	 */
	public function provisionDefault(array $params)
	{
		return $this->post('provision/default', $params);
	}

	/* ============================================================
	 *  DCIM 管理（服务器租用 / NAT 类产品）
	 * ============================================================ */

	/**
	 * DCIM 模块操作（开机/关机/重启/重置BMC/救援/取消任务等）。
	 * POST dcim/{action}?id={hostId}
	 *
	 * @param string $action 端点名（on/off/reboot/bmc/rescue/cancel_task/reinstall/crack_pass）
	 * @param int    $hostId 上游主机 ID
	 * @param array  $extra  额外参数
	 * @return array
	 */
	public function dcimAction($action, $hostId, array $extra = [])
	{
		$data = array_merge(['id' => (int)$hostId], $extra);
		return $this->post('dcim/' . $action, $data);
	}

	/** DCIM 产品详情（交换机/端口信息）。GET dcim/detail */
	public function dcimDetail($hostId)
	{
		return $this->get('dcim/detail', ['id' => (int)$hostId]);
	}

	/** 单台电源状态。POST dcim/refresh_power_status */
	public function dcimPowerStatus($hostId)
	{
		return $this->post('dcim/refresh_power_status', ['id' => (int)$hostId]);
	}

	/** 验证是否可以重装（本周次数/上限）。POST dcim/check_reinstall */
	public function dcimCheckReinstall($hostId)
	{
		return $this->post('dcim/check_reinstall', ['id' => (int)$hostId]);
	}

	/** 重装/救援/重置密码进度。GET dcim/resintall_status */
	public function dcimReinstallStatus($hostId)
	{
		return $this->get('dcim/resintall_status', ['id' => (int)$hostId]);
	}

	/** 用量信息。GET dcim/traffic_usage */
	public function dcimTrafficUsage($hostId, array $extra = [])
	{
		$data = array_merge(['id' => (int)$hostId], $extra);
		return $this->get('dcim/traffic_usage', $data);
	}

	/**
	 * 配置升级页。
	 * GET upgrade/index/{hostId}
	 *
	 * @param int   $hostId
	 * @param array $params
	 * @return array
	 */
	public function upgradeIndex($hostId, array $params = [])
	{
		return $this->get('upgrade/index/' . intval($hostId), $params);
	}

	/**
	 * 提交配置升级。
	 * POST upgrade/upgrade_config_post
	 *
	 * @param array $params
	 * @return array
	 */
	public function upgradeConfigPost(array $params)
	{
		return $this->post('upgrade/upgrade_config_post', $params);
	}

	/**
	 * 配置升级确认页。
	 * GET upgrade/upgrade_config_page
	 *
	 * @param int    $hostId
	 * @param string $priceBasis
	 * @return array
	 */
	public function upgradeConfigPage($hostId, $priceBasis = 'agent')
	{
		return $this->get('upgrade/upgrade_config_page', [
			'hid'         => intval($hostId),
			'price_basis' => $priceBasis,
		]);
	}

	/**
	 * 产品升降级选项。
	 * GET upgrade/upgrade_product/{hostId}
	 *
	 * @param int   $hostId
	 * @param array $params
	 * @return array
	 */
	public function upgradeProduct($hostId, array $params = [])
	{
		return $this->get('upgrade/upgrade_product/' . intval($hostId), $params);
	}

	/**
	 * 提交产品升降级。
	 * POST upgrade/upgrade_product_post
	 *
	 * @param array $params
	 * @return array
	 */
	public function upgradeProductPost(array $params)
	{
		return $this->post('upgrade/upgrade_product_post', $params);
	}

	/**
	 * 任意未封装接口。
	 *
	 * @param string $method GET|POST|PUT|DELETE
	 * @param string $path   相对路径
	 * @param array  $data
	 * @return array
	 */
	public function custom($method, $path, array $data = [])
	{
		return $this->request($method, ltrim($path, '/'), $data);
	}

	// ==================== 底层 HTTP ====================

	/**
	 * @param string $method
	 * @param string $path
	 * @param array  $data
	 * @param bool   $auth
	 * @return string 原始响应体
	 * @throws CubeFinanceException
	 */
	protected function rawRequest($method, $path, array $data, $auth)
	{
		$method = strtoupper($method);
		$url = $this->baseUrl . '/' . ltrim($path, '/');
		$headers = [
			'User-Agent: CubeFinanceClient/1.0 (+php)',
			'Accept: application/json',
		];
		if ($auth) {
			$headers[] = 'Authorization: Bearer ' . $this->jwt;
		}

		$ch = curl_init();
		if ($method === 'GET') {
			$qs = http_build_query($data);
			if ($qs !== '') {
				$url .= (strpos($url, '?') === false ? '?' : '&') . $qs;
			}
			curl_setopt($ch, CURLOPT_HTTPGET, true);
		} else {
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
			if ($method === 'POST') {
				curl_setopt($ch, CURLOPT_POST, true);
			}
		}

		curl_setopt_array($ch, [
			CURLOPT_URL            => $url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_TIMEOUT        => $this->timeout,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_SSL_VERIFYPEER => $this->verifySsl,
			CURLOPT_SSL_VERIFYHOST => $this->verifySsl ? 2 : 0,
			CURLOPT_HTTPHEADER     => $headers,
			CURLOPT_HEADER         => false,
		]);

		$body = curl_exec($ch);
		$errno = curl_errno($ch);
		$error = curl_error($ch);
		$httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);

		if ($errno) {
			throw new CubeFinanceException('网络错误: ' . $error, $errno);
		}
		if ($httpCode < 200 || $httpCode >= 300) {
			$snippet = is_string($body) ? $this->truncate($body, 300) : '';
			throw new CubeFinanceException(
				"HTTP {$httpCode} {$method} {$url}" . ($snippet !== '' ? ': ' . $snippet : ''),
				$httpCode,
				['url' => $url, 'http_code' => $httpCode, 'body' => $body]
			);
		}
		if ($body === false || $body === '') {
			throw new CubeFinanceException('空响应', $httpCode);
		}

		return $body;
	}

	/**
	 * @param string $raw
	 * @return array
	 * @throws CubeFinanceException
	 */
	protected function decodeJson($raw)
	{
		$data = json_decode($raw, true);
		if (!is_array($data)) {
			throw new CubeFinanceException(
				'响应不是合法 JSON: ' . $this->truncate((string)$raw, 200)
			);
		}
		return $data;
	}

	/**
	 * 截断字符串（mb_substr 不可用时回退 substr）。
	 *
	 * @param string $str
	 * @param int    $len
	 * @return string
	 */
	protected function truncate($str, $len)
	{
		if (function_exists('mb_substr')) {
			return mb_substr($str, 0, $len);
		}
		return substr($str, 0, $len);
	}

	protected function cacheKey()
	{
		return 'jwt_' . md5($this->baseUrl . '|' . $this->username);
	}

	protected function cacheFile()
	{
		if (!$this->cacheDir) {
			return null;
		}
		if (!is_dir($this->cacheDir)) {
			@mkdir($this->cacheDir, 0755, true);
		}
		return $this->cacheDir . DIRECTORY_SEPARATOR . $this->cacheKey() . '.json';
	}

	protected function getCachedJwt()
	{
		$file = $this->cacheFile();
		if (!$file || !is_file($file)) {
			return null;
		}
		$json = @file_get_contents($file);
		$data = json_decode((string)$json, true);
		if (empty($data['jwt']) || empty($data['expire']) || time() >= (int)$data['expire']) {
			return null;
		}
		return $data['jwt'];
	}

	protected function setCachedJwt($jwt)
	{
		$file = $this->cacheFile();
		if (!$file) {
			return;
		}
		@file_put_contents($file, json_encode([
			'jwt'    => $jwt,
			'expire' => time() + $this->jwtTtl,
		]));
	}

	/**
	 * 清除 JWT 缓存。
	 */
	public function clearCache()
	{
		$this->jwt = null;
		$file = $this->cacheFile();
		if ($file && is_file($file)) {
			@unlink($file);
		}
	}

	/**
	 * 修改请求超时秒数（用户端实时查询用较短超时，避免拖垮整页加载）。
	 *
	 * @param int $seconds
	 * @return void
	 */
	public function setTimeout($seconds)
	{
		$this->timeout = max(1, (int)$seconds);
	}
}

}
