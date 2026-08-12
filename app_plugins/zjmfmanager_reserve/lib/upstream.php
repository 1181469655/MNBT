<?php
/**
 * zjmfmanager_reserve 插件 - 上游服务层
 *
 * 封装对魔方财务（cube_finance）的所有上游调用，供订单开通、主机操作、
 * 商品同步、升级使用。所有方法均按供应商行（$supplier）路由，
 * 每个供应商独立客户端实例与独立 JWT 缓存（runtime/cache/s{id}）。
 * 上游响应字段因版本/定制站存在差异，解析均为防御式，
 * 联调时以实际站点返回为准（见 PRD §3.3 / §12 Q1、Q3）。
 */

if (!defined('IN_CRONLITE')) {
	exit;
}

require_once __DIR__ . '/CubeFinanceClient.php';

/** 上游下单端点（方案 A，Q1 联调确认；如上游为 provision 直通则改此处） */
define('ZJMF_CHECKOUT_PATH', 'cart/checkout');

class ZjmfUpstream
{
	/**
	 * 创建上游客户端（按供应商行）；连接信息不完整返回 null。
	 * JWT 缓存目录按供应商 ID 隔离，避免多供应商凭证串扰。
	 *
	 * @param array|null $supplier MN_plugin_zjmf_supplier 行
	 * @return CubeFinanceClient|null
	 */
	public static function client($supplier)
	{
		if (!is_array($supplier)) {
			return null;
		}
		$apiUrl = (string)($supplier['api_url'] ?? '');
		$username = (string)($supplier['api_username'] ?? '');
		$password = (string)($supplier['api_password'] ?? '');
		if ($apiUrl === '' || $username === '' || $password === '') {
			return null;
		}
		$supplierId = (int)($supplier['id'] ?? 0);
		$cacheDir = mnbt_plugin_path('zjmfmanager_reserve')
			. 'runtime/cache/s' . $supplierId;
		return new CubeFinanceClient([
			'url'        => $apiUrl,
			'username'   => $username,
			'password'   => $password,
			'timeout'    => (int)($supplier['api_timeout'] ?? 30) > 0
				? (int)($supplier['api_timeout'] ?? 30) : 30,
			'cache_dir'  => $cacheDir,
			'verify_ssl' => false,
		]);
	}

	/** 连通性测试（登录 + 商品列表），按供应商。 */
	public static function testConnection($supplier)
	{
		$client = self::client($supplier);
		if (!$client) {
			return ['ok' => false, 'msg' => '供应商连接信息不完整'];
		}
		return $client->testConnection();
	}

	/* ============================================================
	 *  商品同步
	 * ============================================================ */

	/** 上游商品列表（同步弹窗拉取，不做入库）。 */
	public static function upstreamProducts($supplier)
	{
		$client = self::client($supplier);
		if (!$client) {
			return ['ok' => false, 'msg' => '供应商连接信息不完整'];
		}
		try {
			$res = $client->productList();
			if (($res['status'] ?? 0) != 200) {
				return ['ok' => false, 'msg' => (string)($res['msg'] ?? '上游返回异常')];
			}
			return ['ok' => true, 'data' => $res['data'] ?? []];
		} catch (CubeFinanceException $e) {
			return ['ok' => false, 'msg' => $e->getMessage()];
		}
	}

	/**
	 * 按供应商同步单个商品（手动添加后拉取代理价与各周期价格）。
	 * 幂等：按 supplier_id + up_product_id upsert。
	 *
	 * @param array $supplier     供应商行
	 * @param int   $upProductId  上游商品 ID
	 * @return array ['ok'=>bool, 'msg'=>string]
	 */
	public static function syncOneProductBySupplier($supplier, $upProductId)
	{
		$client = self::client($supplier);
		if (!$client) {
			return ['ok' => false, 'msg' => '供应商连接信息不完整'];
		}
		try {
			$upId = (int)$upProductId;
			$detail = $client->productDetail($upId, 'agent');
			$prod = $detail['data']['product'] ?? ($detail['data'] ?? []);
			$item = [
				'id'          => $upId,
				'name'        => (string)($prod['name'] ?? ''),
				'description' => (string)($prod['description'] ?? ''),
			];
			$currency = (string)($detail['data']['currency_code'] ?? '');
			$ok = self::syncOneProduct(
				$client, (int)($supplier['id'] ?? 0), $upId, $item, $currency
			);
			return $ok
				? ['ok' => true, 'msg' => '商品价格已同步']
				: ['ok' => false, 'msg' => '商品写入失败'];
		} catch (CubeFinanceException $e) {
			return ['ok' => false, 'msg' => $e->getMessage()];
		}
	}

	/** 上游商品详情（代理价），供手动添加 / 单品刷新。 */
	public static function productDetail($supplier, $upProductId)
	{
		$client = self::client($supplier);
		if (!$client) {
			return ['ok' => false, 'msg' => '供应商连接信息不完整'];
		}
		try {
			$res = $client->productDetail((int)$upProductId, 'agent');
			if (($res['status'] ?? 0) != 200) {
				return ['ok' => false, 'msg' => (string)($res['msg'] ?? '获取商品详情失败')];
			}
			return ['ok' => true, 'data' => $res['data'] ?? []];
		} catch (CubeFinanceException $e) {
			return ['ok' => false, 'msg' => $e->getMessage()];
		}
	}

	/**
	 * 同步商品（按供应商，可选勾选商品 ID 列表）。
	 * 列表 + 代理价详情 + 各周期试算价。
	 * 幂等：按 supplier_id + up_product_id upsert，
	 * 不覆盖管理员加价/上架/排序。$upIds 为空表示同步全部。
	 *
	 * @param array $supplier 供应商行
	 * @param array $upIds    勾选的上游商品 ID 列表
	 * @return array ['ok'=>bool, 'msg'=>string]
	 */
	public static function syncProducts($supplier, array $upIds = [])
	{
		$client = self::client($supplier);
		if (!$client) {
			return ['ok' => false, 'msg' => '供应商连接信息不完整'];
		}
		$supplierId = (int)($supplier['id'] ?? 0);
		try {
			$res = $client->productList();
			if (($res['status'] ?? 0) != 200 || empty($res['data']['list'])) {
				return ['ok' => false, 'msg' => (string)($res['msg'] ?? '上游返回异常')];
			}
			$currency = (string)($res['data']['currency_code'] ?? '');
			$count = 0;
			$fail = 0;
			$skip = 0;
			foreach ($res['data']['list'] as $item) {
				$upId = (int)($item['id'] ?? 0);
				if ($upId <= 0) {
					continue;
				}
				if ($upIds && !in_array($upId, $upIds, true)) {
					$skip++;
					continue;
				}
				if (self::syncOneProduct($client, $supplierId, $upId, $item, $currency)) {
					$count++;
				} else {
					$fail++;
				}
			}
			$msg = "同步完成：更新 {$count} 个，失败 {$fail} 个";
			if ($skip > 0) {
				$msg .= "，跳过 {$skip} 个";
			}
			return ['ok' => true, 'msg' => $msg];
		} catch (CubeFinanceException $e) {
			return ['ok' => false, 'msg' => $e->getMessage()];
		}
	}

	/** 同步单个商品。 */
	protected static function syncOneProduct($client, $supplierId, $upId, $item, $currency)
	{
		global $DB, $date;
		$now = $date ?: date('Y-m-d H:i:s');

		// 代理价（详情接口）
		$agentCents = 0;
		try {
			$detail = $client->productDetail($upId, 'agent');
			$prod = $detail['data']['product'] ?? ($detail['data'] ?? []);
			$agentCents = self::toCents(self::pickPrice($prod));
		} catch (CubeFinanceException $e) {
			// 详情失败不致命，仅代理价缺失
		}

		// 各周期试算价
		$cycles = [];
		foreach (zjmf_cycles() as $cycle => $cfg) {
			try {
				$trial = $client->cartSetConfig(['pid' => $upId, 'billingcycle' => $cycle]);
				$price = self::parseTrialPrice($trial);
				if ($price > 0) {
					$cycles[] = [
						'cycle'             => $cycle,
						'name'              => $cfg['name'],
						'agent_price_cents' => $price,
					];
				}
			} catch (CubeFinanceException $e) {
				continue;
			}
		}
		$cyclesJson = json_encode($cycles, JSON_UNESCAPED_UNICODE);

		$existing = zjmf_product_get_by_up($supplierId, $upId);
		if ($existing) {
			// 重复同步仅刷新价格/周期，不覆盖本地已修改的名称与简介
			$ok = $DB->query_prepare(
				"UPDATE MN_plugin_zjmf_product
				 SET currency=?, agent_price_cents=?, cycles=?, synced_at=?, updated_at=?
				 WHERE id=?",
				[
					$currency,
					$agentCents,
					$cyclesJson,
					$now,
					$now,
					(int)$existing['id'],
				]
			);
		} else {
			$ok = $DB->query_prepare(
				"INSERT INTO MN_plugin_zjmf_product
				 (supplier_id, up_product_id, name, description, currency,
				  agent_price_cents, cycles, status, sort, synced_at,
				  created_at, updated_at)
				 VALUES (?,?,?,?,?,?,?,?,?,?,?,?)",
				[
					$supplierId,
					$upId,
					(string)($item['name'] ?? ''),
					(string)($item['description'] ?? ''),
					$currency,
					$agentCents,
					$cyclesJson,
					0,
					50,
					$now,
					$now,
					$now,
				]
			);
		}
		if (!$ok) {
			return false;
		}
		// 重算本地售价（保持已有加价配置不变）
		$targetId = $existing ? (int)$existing['id'] : self::lastProductId($supplierId);
		if ($targetId > 0) {
			zjmf_product_recalc_price($targetId);
		}
		return true;
	}

	/** 取指定供应商内最近插入的商品 ID。 */
	protected static function lastProductId($supplierId)
	{
		global $DB;
		$row = $DB->get_row_prepare(
			"SELECT id FROM MN_plugin_zjmf_product
			 WHERE supplier_id=? ORDER BY id DESC LIMIT 1",
			[(int)$supplierId]
		);
		return $row ? (int)$row['id'] : 0;
	}

	/* ============================================================
	 *  开通（代理商直通）
	 * ============================================================ */

	/**
	 * 上游开通主机（按订单所属供应商路由）。
	 * 方案 A：cart/set_config 试算 → 下单 → apply_credit 余额支付 → 解析主机信息。
	 * 端点与响应字段联调确认（Q1），调整仅需改动本方法。
	 *
	 * @param array $order    MN_plugin_zjmf_order 行
	 * @param array $supplier MN_plugin_zjmf_supplier 行
	 * @return array ['ok'=>bool, 'msg'=>string, 'up_order_id'=>int, 'up_host_id'=>int,
	 *                'username'=>string, 'password'=>string, 'name'=>string,
	 *                'renew_date'=>string]
	 */
	public static function purchase($order, $supplier)
	{
		$client = self::client($supplier);
		if (!$client) {
			return ['ok' => false, 'msg' => '供应商连接信息不完整'];
		}

		$params = [
			'pid'          => (int)($order['up_product_id'] ?? 0),
			'billingcycle' => (string)($order['cycle'] ?? ''),
		];
		$extra = json_decode((string)($order['order_params'] ?? ''), true);
		if (is_array($extra)) {
			$params = array_merge($params, $extra);
		}

		try {
			// 1. 下单
			$res = $client->post(ZJMF_CHECKOUT_PATH, $params);
			if (($res['status'] ?? 0) != 200) {
				return ['ok' => false, 'msg' => (string)($res['msg'] ?? '上游下单失败')];
			}
			$data = is_array($res['data'] ?? null) ? $res['data'] : [];

			// 2. 若有上游订单，尝试余额抵扣支付（部分站点下单即开通，忽略失败）
			$upOrderId = self::findId($data);
			if ($upOrderId > 0) {
				try {
					$client->applyCredit(['order_id' => $upOrderId]);
				} catch (CubeFinanceException $e) {
					// 忽略：非致命
				}
			}

			// 3. 解析主机信息
			$hostId = self::findHostId($data);
			if ($hostId > 0) {
				$header = self::safeHostHeader($client, $hostId);
				return [
					'ok'          => true,
					'msg'         => '开通成功',
					'up_order_id' => $upOrderId,
					'up_host_id'  => $hostId,
					'username'    => $header['username'],
					'password'    => $header['password'],
					'name'        => $header['name'],
					'renew_date'  => $header['renew_date'],
				];
			}

			// 4. 未拿到 host_id：订单已生成，需人工核对（不判失败，避免误退款）
			return [
				'ok'          => true,
				'msg'         => '上游订单已创建，但未返回主机 ID，请到上游后台核对',
				'up_order_id' => $upOrderId,
				'up_host_id'  => 0,
				'username'    => '',
				'password'    => '',
				'name'        => (string)($order['product_name'] ?? ''),
				'renew_date'  => '',
			];
		} catch (CubeFinanceException $e) {
			return ['ok' => false, 'msg' => $e->getMessage()];
		}
	}

	/* ============================================================
	 *  主机查询与操作
	 * ============================================================ */

	/** 主机头信息（状态/账号/到期），按主机所属供应商。 */
	public static function hostInfo($supplier, $upHostId)
	{
		$client = self::client($supplier);
		if (!$client) {
			return ['ok' => false, 'msg' => '供应商连接信息不完整'];
		}
		try {
			$res = $client->hostHeader((int)$upHostId);
			if (($res['status'] ?? 0) != 200) {
				return ['ok' => false, 'msg' => (string)($res['msg'] ?? '查询失败')];
			}
			$data = $res['data'] ?? [];
			if (is_array($data) && isset($data['host']) && is_array($data['host'])) {
				$data = $data['host'];
			}
			return ['ok' => true, 'data' => $data, 'status' => self::mapHostStatus($data)];
		} catch (CubeFinanceException $e) {
			return ['ok' => false, 'msg' => $e->getMessage()];
		}
	}

	/** 流量使用，按主机所属供应商。 */
	public static function hostTraffic($supplier, $upHostId)
	{
		$client = self::client($supplier);
		if (!$client) {
			return ['ok' => false, 'msg' => '供应商连接信息不完整'];
		}
		try {
			$res = $client->hostTrafficUsage((int)$upHostId);
			if (($res['status'] ?? 0) != 200) {
				return ['ok' => false, 'msg' => (string)($res['msg'] ?? '查询失败')];
			}
			return ['ok' => true, 'data' => $res['data'] ?? []];
		} catch (CubeFinanceException $e) {
			return ['ok' => false, 'msg' => $e->getMessage()];
		}
	}

	/** 主机操作（provision/default，func 由调用方传入）。 */
	public static function hostAction($supplier, $upHostId, $func, $extra = [])
	{
		$client = self::client($supplier);
		if (!$client) {
			return ['ok' => false, 'msg' => '供应商连接信息不完整'];
		}
		try {
			$params = array_merge(['id' => (int)$upHostId, 'func' => $func], $extra);
			$res = $client->provisionDefault($params);
			if (($res['status'] ?? 0) == 200) {
				return ['ok' => true, 'msg' => (string)($res['msg'] ?? '操作成功')];
			}
			return ['ok' => false, 'msg' => (string)($res['msg'] ?? '操作失败')];
		} catch (CubeFinanceException $e) {
			return ['ok' => false, 'msg' => $e->getMessage()];
		}
	}

	/* ============================================================
	 *  升级（配置升级 / 产品升降级）
	 * ============================================================ */

	/** 只读获取升级选项（配置项或可升降级产品列表）。 */
	public static function upgradeOptions($supplier, $upHostId, $kind)
	{
		$client = self::client($supplier);
		if (!$client) {
			return ['ok' => false, 'msg' => '供应商连接信息不完整'];
		}
		try {
			$res = $kind === 'product'
				? $client->upgradeProduct((int)$upHostId)
				: $client->upgradeIndex((int)$upHostId);
			if (($res['status'] ?? 0) != 200) {
				return ['ok' => false, 'msg' => (string)($res['msg'] ?? '获取升级选项失败')];
			}
			return ['ok' => true, 'data' => $res['data'] ?? []];
		} catch (CubeFinanceException $e) {
			return ['ok' => false, 'msg' => $e->getMessage()];
		}
	}

	/**
	 * 试算升级差额（GET 页面端点，只读不提交）。
	 * 端点联调确认 Q1；selection 结构：
	 *   config  → ['configoption' => [option_id => value]]
	 *   product → ['newpid' => id, 'billingcycle' => cycle]
	 *
	 * @param array $supplier  供应商行
	 * @param string $kind     config|product
	 * @param int    $upHostId 上游主机 ID
	 * @param array  $selection
	 * @return array ['ok'=>bool, 'msg'=>string, 'price_cents'=>int, 'data'=>array]
	 */
	public static function upgradePreview($supplier, $kind, $upHostId, $selection)
	{
		$client = self::client($supplier);
		if (!$client) {
			return ['ok' => false, 'msg' => '供应商连接信息不完整'];
		}
		try {
			$path = $kind === 'product'
				? 'upgrade/upgrade_product_page'
				: 'upgrade/upgrade_config_page';
			$params = array_merge(
				['hid' => (int)$upHostId, 'price_basis' => 'agent'],
				$selection
			);
			$res = $client->get($path, $params);
			if (($res['status'] ?? 0) != 200) {
				return ['ok' => false, 'msg' => (string)($res['msg'] ?? '试算失败')];
			}
			return [
				'ok'          => true,
				'msg'         => (string)($res['msg'] ?? ''),
				'price_cents' => self::parseTrialPrice($res),
				'data'        => is_array($res['data'] ?? null) ? $res['data'] : [],
			];
		} catch (CubeFinanceException $e) {
			return ['ok' => false, 'msg' => $e->getMessage()];
		}
	}

	/**
	 * 提交升级（确认，POST 端点）。
	 * 调用方需先扣款；提交失败由调用方负责退款。
	 *
	 * @param array $supplier  供应商行
	 * @param string $kind     config|product
	 * @param int    $upHostId 上游主机 ID
	 * @param array  $selection
	 * @return array ['ok'=>bool, 'msg'=>string, 'price_cents'=>int,
	 *                'up_order_id'=>int, 'up_host_id'=>int]
	 */
	public static function upgradeSubmit($supplier, $kind, $upHostId, $selection)
	{
		$client = self::client($supplier);
		if (!$client) {
			return ['ok' => false, 'msg' => '供应商连接信息不完整'];
		}
		try {
			$params = array_merge(['hid' => (int)$upHostId], $selection);
			$res = $kind === 'product'
				? $client->upgradeProductPost($params)
				: $client->upgradeConfigPost($params);
			if (($res['status'] ?? 0) != 200) {
				return ['ok' => false, 'msg' => (string)($res['msg'] ?? '升级提交失败')];
			}
			$data = is_array($res['data'] ?? null) ? $res['data'] : [];
			return [
				'ok'          => true,
				'msg'         => (string)($res['msg'] ?? '提交成功'),
				'price_cents' => self::parseTrialPrice($res),
				'up_order_id' => self::findId($data),
				'up_host_id'  => (int)$upHostId,
			];
		} catch (CubeFinanceException $e) {
			return ['ok' => false, 'msg' => $e->getMessage()];
		}
	}

	/* ============================================================
	 *  响应解析辅助（防御式，字段以实际上游返回为准）
	 * ============================================================ */

	/** 从响应 data 中提取金额（分），常见字段/嵌套结构兜底。 */
	protected static function parseTrialPrice($res)
	{
		$data = $res['data'] ?? [];
		if (!is_array($data)) {
			return 0;
		}
		foreach (['price', 'money', 'total', 'amount', 'subtotal', 'renewal_price'] as $k) {
			if (isset($data[$k]) && $data[$k] !== '' && $data[$k] !== null) {
				$c = self::toCents($data[$k]);
				if ($c > 0) {
					return $c;
				}
			}
		}
		foreach (['cart', 'order', 'product', 'host'] as $k) {
			if (isset($data[$k]) && is_array($data[$k])) {
				$c = self::toCents(self::pickPrice($data[$k]));
				if ($c > 0) {
					return $c;
				}
			}
		}
		return 0;
	}

	/** 从数组取价格字段（元），取首个非空值。 */
	protected static function pickPrice($arr)
	{
		if (!is_array($arr)) {
			return 0;
		}
		foreach (['price', 'renew_price', 'setup_fee', 'total', 'amount'] as $k) {
			if (isset($arr[$k]) && $arr[$k] !== '' && $arr[$k] !== null) {
				return $arr[$k];
			}
		}
		return 0;
	}

	/** 金额（元）→ 分。 */
	protected static function toCents($val)
	{
		return (int)round((float)$val * 100);
	}

	/** 上游列表项价格（分），支持嵌套 pricing 结构，无价格返回 0。 */
	public static function itemPrice($item)
	{
		if (!is_array($item)) {
			return 0;
		}
		$cents = self::toCents(self::pickPrice($item));
		if ($cents <= 0 && isset($item['pricing']) && is_array($item['pricing'])) {
			foreach ($item['pricing'] as $v) {
				$cents = self::toCents(is_array($v) ? self::pickPrice($v) : $v);
				if ($cents > 0) {
					break;
				}
			}
		}
		return $cents;
	}

	/** 上游列表项模块类型（魔方财务列表返回字段为 type），无则返回空串。 */
	public static function itemModule($item)
	{
		if (!is_array($item)) {
			return '';
		}
		return (string)($item['type'] ?? $item['module'] ?? $item['module_name'] ?? '');
	}

	/** 从 data 中找通用 ID（订单 ID）。 */
	protected static function findId($arr)
	{
		if (!is_array($arr)) {
			return 0;
		}
		foreach (['id', 'order_id', 'orderid'] as $k) {
			if (isset($arr[$k])) {
				return (int)$arr[$k];
			}
		}
		return 0;
	}

	/** 从 data 中找主机 ID（支持嵌套 host）。 */
	protected static function findHostId($arr)
	{
		if (!is_array($arr)) {
			return 0;
		}
		foreach (['host_id', 'hostid', 'hid', 'id'] as $k) {
			if (isset($arr[$k]) && (int)$arr[$k] > 0) {
				return (int)$arr[$k];
			}
		}
		if (isset($arr['host']) && is_array($arr['host'])) {
			return self::findHostId($arr['host']);
		}
		return 0;
	}

	/** 查询主机头信息并安全提取字段（失败给空）。 */
	protected static function safeHostHeader($client, $hostId)
	{
		try {
			$res = $client->hostHeader((int)$hostId);
			$data = $res['data'] ?? [];
			if (is_array($data) && isset($data['host']) && is_array($data['host'])) {
				$data = $data['host'];
			}
			return [
				'username'   => (string)($data['username'] ?? ''),
				'password'   => (string)($data['password'] ?? ''),
				'name'       => (string)($data['name'] ?? ''),
				'renew_date' => (string)($data['renew_date'] ?? $data['renewdate'] ?? ''),
			];
		} catch (CubeFinanceException $e) {
			return ['username' => '', 'password' => '', 'name' => '', 'renew_date' => ''];
		}
	}

	/** 上游主机状态 → 本地展示状态（active/suspend/unknown）。 */
	public static function mapHostStatus($data)
	{
		if (!is_array($data)) {
			return 'unknown';
		}
		$qk = $data['qk'] ?? null;
		if ($qk !== null && in_array((string)$qk, ['false', '0', ''], true)) {
			return 'suspend';
		}
		$st = (string)($data['status'] ?? $data['domainstatus'] ?? '');
		$st = strtolower($st);
		if (in_array($st, ['active', 'on', 'true', '运行中'], true)) {
			return 'active';
		}
		if (in_array($st, ['suspended', 'suspend', 'paused', 'off'], true)) {
			return 'suspend';
		}
		return 'unknown';
	}
}
