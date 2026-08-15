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

/**
 * 开通流程（按魔方财务官方 API 文档，前台会员中心接口，无 v1 前缀）：
 *   1. POST /cart/add_to_shop 添加产品至购物车（pid/billingcycle/qty/host/password）→ data.i 购物车位置
 *   2. POST /cart/settle      结算购物车（pos[]=[i]、checkout=1 直接结算）→ data.invoiceid
 *   3. POST /apply_credit     使用余额支付账单（invoiceid、use_credit=1、enough=1）
 *   4. GET  /invoices/{id}    账单详情，取 host[].num 得到主机 ID（产品 ID）
 *   5. GET  /host/header      查询主机信息（username/password/productname/nextduedate）
 * 接口与响应字段以官方文档为准（docs/zjmf-api-doc/zjmf-api.md）；
 * 上游版本/定制站存在差异时，失败信息会带出上游返回详情便于联调适配（见 PRD §3.3 / §12 Q1）。
 */

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
			// 优先从列表取真实条目（含 billingcycle/price 等周期信息），失败再退回详情
			$item = null;
			$currency = '';
			try {
				$res = $client->productList();
				if (($res['status'] ?? 0) == 200 && !empty($res['data']['list'])) {
					$currency = (string)($res['data']['currency_code'] ?? '');
					foreach ($res['data']['list'] as $row) {
						if ((int)($row['id'] ?? 0) === $upId) {
							$item = $row;
							break;
						}
					}
				}
			} catch (CubeFinanceException $e) {
				// 列表失败忽略，退回详情
			}
			if (!$item) {
				$detail = $client->productDetail($upId, 'agent');
				$prod = $detail['data']['product'] ?? ($detail['data'] ?? []);
				$item = [
					'id'          => $upId,
					'name'        => (string)($prod['name'] ?? ''),
					'description' => (string)($prod['description'] ?? ''),
				];
				$currency = (string)($detail['data']['currency_code'] ?? $currency);
			}
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

		// 代理价（详情接口，失败回退列表项 price）
		$agentCents = 0;
		$detail = null;
		try {
			$detail = $client->productDetail($upId, 'agent');
			$prod = $detail['data']['product'] ?? ($detail['data'] ?? []);
			$agentCents = self::toCents(self::pickPrice($prod));
		} catch (CubeFinanceException $e) {
			// 详情失败不致命，仅代理价缺失
		}
		if ($agentCents <= 0) {
			$agentCents = self::itemPrice($item);
		}

		// 各周期价：优先读上游返回的 cycle 字段（列表项 → 详情），均无则回退购物车试算
		$cycles = self::parseItemCycles($item);
		if ($cycles === []) {
			$cycles = $detail ? self::parseDetailCycles($detail) : [];
		}
		if ($cycles === []) {
			foreach (zjmf_cycles() as $cycle => $cfg) {
				try {
					$trial = $client->cartSetConfig(['pid' => $upId, 'billingcycle' => $cycle]);
					$price = self::parseTrialPrice($trial);
					if ($price > 0) {
						$cycles[$cycle] = [
							'cycle'             => $cycle,
							'name'              => $cfg['name'],
							'agent_price_cents' => $price,
						];
					}
				} catch (CubeFinanceException $e) {
					continue;
				}
			}
		}
		$cyclesJson = json_encode(array_values($cycles), JSON_UNESCAPED_UNICODE);

		$existing = zjmf_product_get_by_up($supplierId, $upId);
		if ($existing) {
			// 同步不覆盖管理员手动设置的售价（override）
			$oldCycles = zjmf_product_cycles($existing);
			$changed = false;
			foreach ($cycles as $k => &$cfg) {
				if (isset($oldCycles[$k]['override']) && (int)$oldCycles[$k]['override'] > 0) {
					$cfg['override'] = (int)$oldCycles[$k]['override'];
					$cfg['price_cents'] = (int)$oldCycles[$k]['override'];
					$changed = true;
				}
			}
			unset($cfg);
			if ($changed) {
				$cyclesJson = json_encode(array_values($cycles), JSON_UNESCAPED_UNICODE);
			}
		}
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
					zjmf_render_description((string)($item['description'] ?? '')),
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
	 * 流程：add_to_shop 加购 → settle 结算 → apply_credit 余额支付 → 账单详情取主机 ID → host/header 查信息。
	 * 端点与响应字段以官方文档为准（Q1），调整仅需改动本方法。
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

		$upProductId = (int)($order['up_product_id'] ?? 0);
		$cycle = (string)($order['cycle'] ?? '');
		$extra = json_decode((string)($order['order_params'] ?? ''), true);
		if (!is_array($extra)) {
			$extra = [];
		}

		try {
			// 1. 添加产品至购物车（官方：POST /cart/add_to_shop）→ data.i 购物车位置
			$addParams = [
				'pid'          => $upProductId,
				'billingcycle' => $cycle,
				'qty'          => 1,
				'host'         => (string)($extra['host'] ?? self::randHost()),
				'password'     => (string)($extra['password'] ?? self::randPassword()),
			];
			foreach (['configoption', 'customfield', 'serverid', 'os'] as $k) {
				if (isset($extra[$k])) {
					$addParams[$k] = $extra[$k];
				}
			}
			$res = $client->post('cart/add_to_shop', $addParams);
			if (!self::respOk($res)) {
				return ['ok' => false, 'msg' => self::respErr('上游添加购物车失败', $res)];
			}
			$position = -1;
			if (is_array($res['data'] ?? null)) {
				$position = (int)($res['data']['i'] ?? -1);
			} elseif (is_numeric($res['data'] ?? null)) {
				$position = (int)$res['data'];
			}
			if ($position < 0) {
				return ['ok' => false, 'msg' => '上游添加购物车未返回位置，无法结算'];
			}

			// 2. 结算购物车（官方：POST /cart/settle，checkout=1 直接结算）→ data.invoiceid
			$checkout = $client->post('cart/settle', [
				'pos[]'    => [$position],
				'checkout' => 1,
			]);
			if (!self::respOk($checkout)) {
				return ['ok' => false, 'msg' => self::respErr('上游结算失败', $checkout)];
			}
			$checkoutData = is_array($checkout['data'] ?? null) ? $checkout['data'] : [];
			$invoiceId = self::findId($checkoutData);
			$hostId = self::findHostId($checkoutData);

			// 3. 使用余额支付账单（官方：POST /apply_credit）
			if ($invoiceId > 0 && $hostId <= 0) {
				$credit = $client->post('apply_credit', [
					'invoiceid' => $invoiceId,
					'use_credit' => 1,
					'enough'    => 1,
				]);
				if (!self::respOk($credit)) {
					// 账单可能已被自动扣款，确认已支付后再继续
					$info = self::invoiceInfo($client, $invoiceId, 1);
					if (!$info || !self::isPaidStatus($info['status'])) {
						return ['ok' => false, 'msg' => self::respErr('上游余额支付失败', $credit)];
					}
				}
			}

			// 4. 账单已支付，轮询账单详情取主机 ID（主机创建可能异步）
			if ($hostId <= 0 && $invoiceId > 0) {
				$info = self::invoiceInfo($client, $invoiceId);
				if ($info) {
					$hostId = (int)$info['host_id'];
				}
			}

			// 5. 拿到主机 ID → 查询主机信息（官方：GET /host/header）
			if ($hostId > 0) {
				$header = self::safeHostHeader($client, $hostId);
				return [
					'ok'          => true,
					'msg'         => '开通成功',
					'up_order_id' => $invoiceId,
					'up_host_id'  => $hostId,
					'username'    => $header['username'],
					'password'    => $header['password'],
					'name'        => $header['name'],
					'renew_date'  => $header['renew_date'],
				];
			}

			// 6. 订单已创建/支付但未拿到主机：人工核对（不判失败，避免误退款）
			return [
				'ok'          => true,
				'msg'         => '上游订单已创建，但未返回主机 ID，请到上游后台核对',
				'up_order_id' => $invoiceId,
				'up_host_id'  => 0,
				'username'    => '',
				'password'    => '',
				'name'        => (string)($order['product_name'] ?? ''),
				'renew_date'  => '',
			];
		} catch (CubeFinanceException $e) {
			$detail = '';
			if (is_array($e->response) && !empty($e->response['body'])) {
				$detail = '（响应: ' . self::truncate((string)$e->response['body'], 300) . '）';
			}
			return ['ok' => false, 'msg' => $e->getMessage() . $detail];
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
			$data = self::pickHostData($data);
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
			$params = array_merge([
				'id'      => (int)$upHostId,
				'func'    => $func,
				'is_api'  => 1,
			], $extra);
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

	/**
	 * 从列表项提取各周期价格（分）。
	 * 魔方财务列表项为「单周期」结构：billingcycle + price + billingcycle_zh（cycle 只是标签字符串）。
	 * 兼容完整周期结构（cycle/pricing 为数组或映射），解析失败返回空数组。
	 */
	protected static function parseItemCycles($item)
	{
		if (!is_array($item)) {
			return [];
		}
		// 单周期结构：billingcycle + price + billingcycle_zh
		$key = (string)($item['billingcycle'] ?? '');
		if ($key !== '') {
			$cents = self::toCents(self::pickPrice($item));
			if ($cents > 0) {
				$entry = self::cycleEntry($key, $cents, (string)($item['billingcycle_zh'] ?? ''));
				return [$entry['cycle'] => $entry];
			}
		}
		// 兼容完整周期结构：cycle/pricing 为数组或映射
		$raw = $item['cycle'] ?? $item['pricing'] ?? null;
		if (is_string($raw)) {
			$decoded = json_decode($raw, true);
			$raw = is_array($decoded) ? $decoded : null;
		}
		if (is_array($raw) && $raw !== []) {
			return self::parseCycleMap($raw);
		}
		return [];
	}

	/**
	 * 从商品详情响应提取各周期价格（分）。
	 * 魔方财务详情接口将各周期价格放在 data.product.cycle（结构与列表项一致）。
	 */
	protected static function parseDetailCycles($detail)
	{
		$data = $detail['data'] ?? [];
		if (!is_array($data)) {
			return [];
		}
		$prod = is_array($data['product'] ?? null) ? $data['product'] : $data;
		$raw = $prod['cycle'] ?? $data['cycle'] ?? null;
		if (is_string($raw)) {
			$decoded = json_decode($raw, true);
			$raw = is_array($decoded) ? $decoded : null;
		}
		if (!is_array($raw) || $raw === []) {
			return [];
		}
		return self::parseCycleMap($raw);
	}

	/** 构建单个周期条目（键归一化 + 名称兜底）。 */
	protected static function cycleEntry($key, $cents, $name = '')
	{
		$norm = self::normalizeCycleKey($key);
		$known = zjmf_cycles();
		return [
			'cycle'             => $norm,
			'name'              => $name !== '' ? $name : ($known[$norm]['name'] ?? $norm),
			'agent_price_cents' => max(0, (int)$cents),
		];
	}

	/** 周期键归一化：忽略大小写匹配内置周期（monthly → Monthly）。 */
	protected static function normalizeCycleKey($key)
	{
		$key = (string)$key;
		if ($key === '') {
			return '';
		}
		foreach (array_keys(zjmf_cycles()) as $k) {
			if (strcasecmp($k, $key) === 0) {
				return $k;
			}
		}
		return $key;
	}

	/** 解析周期映射/数组，返回 ['Monthly' => ['cycle','name','agent_price_cents']]。 */
	protected static function parseCycleMap($raw)
	{
		$out = [];
		// 数组形式：[{billingcycle: 'monthly', billingcycle_zh: '月', price: '25.00'}, ...]
		if (isset($raw[0]) && is_array($raw[0])) {
			foreach ($raw as $item) {
				if (!is_array($item)) {
					continue;
				}
				$key = (string)($item['billingcycle'] ?? $item['cycle'] ?? '');
				if ($key === '') {
					continue;
				}
				$cents = self::toCents(self::pickPrice($item));
				if ($cents <= 0) {
					continue;
				}
				$name = (string)($item['name'] ?? $item['billingcycle_zh'] ?? '');
				$out[self::normalizeCycleKey($key)] = self::cycleEntry($key, $cents, $name);
			}
		} else {
			// 映射形式：{'monthly': '25.00' 或 {price: '25.00'}}
			foreach ($raw as $key => $v) {
				$key = (string)$key;
				if ($key === '') {
					continue;
				}
				$cents = self::toCents(is_array($v) ? self::pickPrice($v) : $v);
				if ($cents <= 0) {
					continue;
				}
				$name = is_array($v) ? (string)($v['name'] ?? $v['billingcycle_zh'] ?? '') : '';
				$out[self::normalizeCycleKey($key)] = self::cycleEntry($key, $cents, $name);
			}
		}
		return $out;
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

	/** 上游返回是否成功（200 常规成功；1001 购买成功/支付完成，无需继续支付）。 */
	protected static function respOk($res)
	{
		$st = (int)($res['status'] ?? 0);
		return in_array($st, [200, 1001], true);
	}

	/** 组装上游失败详情（msg + data 截断），避免日志里只有泛化文案。 */
	protected static function respErr($prefix, $res)
	{
		$msg = (string)($res['msg'] ?? '');
		$data = $res['data'] ?? null;
		$detail = '';
		if (is_array($data) || is_scalar($data)) {
			$json = json_encode($data, JSON_UNESCAPED_UNICODE);
			if (is_string($json)) {
				$detail = ' data=' . self::truncate($json, 300);
			}
		}
		$out = trim($prefix . '：' . $msg . $detail, '：');
		return $out !== '' ? $out : $prefix;
	}

	/** 截断字符串（mb_substr 不可用时回退 substr）。 */
	protected static function truncate($str, $len)
	{
		if (function_exists('mb_substr')) {
			return mb_substr($str, 0, $len);
		}
		return substr($str, 0, $len);
	}

	/** 生成上游主机名（官方示例：ser + 12 位随机串）。 */
	protected static function randHost()
	{
		$chars = 'abcdefghijklmnopqrstuvwxyz0123456789';
		return 'ser' . substr(str_shuffle($chars), 0, 12);
	}

	/** 生成随机密码（12 位字母数字）。 */
	protected static function randPassword()
	{
		$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
		return substr(str_shuffle($chars), 0, 12);
	}

	protected static function findId($arr)
	{
		if (!is_array($arr)) {
			return 0;
		}
		foreach (['id', 'order_id', 'orderid', 'invoiceid', 'invoice_id'] as $k) {
			if (isset($arr[$k])) {
				$v = $arr[$k];
				if (is_array($v)) {
					$v = reset($v);
				}
				return (int)$v;
			}
		}
		return 0;
	}

	/** 从 data 中找主机 ID（支持嵌套 host 与 hostid 数组）。 */
	protected static function findHostId($arr)
	{
		if (!is_array($arr)) {
			return 0;
		}
		foreach (['host_id', 'hostid', 'hid', 'id'] as $k) {
			if (isset($arr[$k])) {
				$v = $arr[$k];
				if (is_array($v)) {
					$v = reset($v);
				}
				if ((int)$v > 0) {
					return (int)$v;
				}
			}
		}
		if (isset($arr['host']) && is_array($arr['host'])) {
			return self::findHostId($arr['host']);
		}
		return 0;
	}

	/**
	 * 查询账单详情并轮询主机 ID（主机创建可能异步）。
	 * 官方：GET /invoices/{id} → data.invoices[].status（支付状态）+
	 *      data.host[].num（账单项目关联的产品 ID，兼容标量/数组）。
	 *
	 * @param CubeFinanceClient $client
	 * @param int               $invoiceId
	 * @param int               $maxTries 轮询次数（每次间隔 2 秒）
	 * @return array|null ['status'=>string, 'host_id'=>int]，未取到主机 ID 时轮询至超时
	 */
	protected static function invoiceInfo($client, $invoiceId, $maxTries = 4)
	{
		for ($i = 0; $i < $maxTries; $i++) {
			try {
				$res = $client->get('invoices/' . (int)$invoiceId);
				if (self::respOk($res)) {
					$data = is_array($res['data'] ?? null) ? $res['data'] : [];
					$info = ['status' => '', 'host_id' => 0];
					$inv = $data['invoices'] ?? null;
					if (is_array($inv)) {
						$first = (isset($inv[0]) && is_array($inv[0])) ? $inv[0] : $inv;
						$info['status'] = strtolower((string)($first['status'] ?? ''));
					}
					$items = $data['host'] ?? null;
					if (is_array($items)) {
						foreach ($items as $item) {
							if (!is_array($item)) {
								continue;
							}
							$num = $item['num'] ?? null;
							if (is_array($num)) {
								foreach ($num as $n) {
									if ((int)$n > 0) {
										$info['host_id'] = (int)$n;
										break 2;
									}
								}
							} elseif ((int)$num > 0) {
								$info['host_id'] = (int)$num;
								break;
							}
						}
					}
					if ($info['host_id'] > 0) {
						return $info;
					}
				}
			} catch (CubeFinanceException $e) {
				// 查询失败忽略，稍后重试
			}
			if ($i < $maxTries - 1) {
				sleep(2);
			}
		}
		return null;
	}

	/** 账单支付状态是否已支付（兼容英文/中文）。 */
	protected static function isPaidStatus($status)
	{
		$st = strtolower(trim((string)$status));
		return in_array($st, ['paid', '已支付', 'completed', 'complete', 'success', 'payment'], true);
	}

	/** 从主机详情响应提取主机数据（兼容 host_data/host 键与单对象/数组结构）。 */
	protected static function pickHostData($data)
	{
		if (!is_array($data)) {
			return [];
		}
		foreach (['host_data', 'host', 'info'] as $k) {
			if (isset($data[$k]) && is_array($data[$k])) {
				$v = $data[$k];
				if (isset($v[0]) && is_array($v[0])) {
					return $v[0];
				}
				return $v;
			}
		}
		return $data;
	}

	/** 查询主机头信息并安全提取字段（失败给空）。 */
	protected static function safeHostHeader($client, $hostId)
	{
		try {
			$res = $client->hostHeader((int)$hostId);
			$host = self::pickHostData($res['data'] ?? []);
			return [
				'username'   => (string)($host['username'] ?? ''),
				'password'   => (string)($host['password'] ?? ''),
				'name'       => (string)($host['productname'] ?? $host['name'] ?? ''),
				'renew_date' => (string)($host['nextduedate'] ?? $host['renew_date'] ?? $host['renewdate'] ?? ''),
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
