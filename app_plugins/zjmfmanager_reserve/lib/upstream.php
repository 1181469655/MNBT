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
	 * @param int|null   $timeout  覆盖超时秒数（null 用供应商配置 api_timeout）
	 * @return CubeFinanceClient|null
	 */
	public static function client($supplier, $timeout = null)
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
		$t = (int)($supplier['api_timeout'] ?? 30);
		$t = $t > 0 ? $t : 30;
		if ($timeout !== null && (int)$timeout > 0) {
			$t = (int)$timeout;
		}
		$supplierId = (int)($supplier['id'] ?? 0);
		$cacheDir = mnbt_plugin_path('zjmfmanager_reserve')
			. 'runtime/cache/s' . $supplierId;
		return new CubeFinanceClient([
			'url'        => $apiUrl,
			'username'   => $username,
			'password'   => $password,
			'timeout'    => $t,
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

	/**
	 * 查询供应商上游账户余额（GET /index → data.client[].credit）。
	 * user_info 的 data.credit 是信用卡信息而非余额，故优先取首页客户数据，
	 * 失败再兜底 user_info。
	 *
	 * @param array $supplier 供应商行
	 * @return array ['ok'=>bool, 'msg'=>string, 'credit'=>string, 'currency'=>string]
	 */
	public static function balance($supplier)
	{
		$client = self::client($supplier);
		if (!$client) {
			return ['ok' => false, 'msg' => '供应商连接信息不完整'];
		}
		try {
			$res = $client->homeIndex();
			if (self::respOk($res)) {
				$clients = $res['data']['client'] ?? [];
				// 兼容单对象（非数组）形态
				if (is_array($clients) && !isset($clients[0]) && ($clients['credit'] ?? '') !== '') {
					return [
						'ok'       => true,
						'msg'      => 'ok',
						'credit'   => (string)$clients['credit'],
						'currency' => (string)($clients['currency'] ?? ''),
					];
				}
				if (is_array($clients)) {
					foreach ($clients as $c) {
						if (is_array($c) && ($c['credit'] ?? '') !== '') {
							return [
								'ok'       => true,
								'msg'      => 'ok',
								'credit'   => (string)$c['credit'],
								'currency' => (string)($c['currency'] ?? ''),
							];
						}
					}
				}
			}
			// 兜底：GET /user_info（部分版本 credit 即余额）
			$res = $client->userInfo();
			if (!self::respOk($res)) {
				return ['ok' => false, 'msg' => self::respErr('获取上游账户信息失败', $res)];
			}
			$data = $res['data'] ?? [];
			$credit = $data['credit'] ?? null;
			if ($credit === null || $credit === '') {
				$credit = $data['credit_balance'] ?? null; // 兼容字段
			}
			return [
				'ok'       => true,
				'msg'      => 'ok',
				'credit'   => (string)$credit,
				'currency' => (string)($data['currency'] ?? ''),
			];
		} catch (CubeFinanceException $e) {
			return ['ok' => false, 'msg' => $e->getMessage()];
		}
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
		@set_time_limit(0);
		$client = self::client($supplier);
		if (!$client) {
			return ['ok' => false, 'msg' => '供应商连接信息不完整'];
		}
		try {
			$upId = (int)$upProductId;
			// 优先从列表取真实条目（含 billingcycle/price 等周期信息，同 pid 可能每周期一行），失败再退回详情
			$rows = [];
			$currency = '';
			try {
				$res = $client->productList();
				if (($res['status'] ?? 0) == 200 && !empty($res['data']['list'])) {
					$currency = (string)($res['data']['currency_code'] ?? '');
					foreach ($res['data']['list'] as $row) {
						if ((int)($row['id'] ?? 0) === $upId) {
							$rows[] = $row;
						}
					}
				}
			} catch (CubeFinanceException $e) {
				// 列表失败忽略，退回详情
			}
			if ($rows === []) {
				$detail = $client->productDetail($upId, 'agent');
				$prod = $detail['data']['product'] ?? ($detail['data'] ?? []);
				$rows = [[
					'id'          => $upId,
					'name'        => (string)($prod['name'] ?? ''),
					'description' => (string)($prod['description'] ?? ''),
				]];
				$currency = (string)($detail['data']['currency_code'] ?? $currency);
			}
			$ok = self::syncOneProduct(
				$client, (int)($supplier['id'] ?? 0), $upId, $rows, $currency
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
		@set_time_limit(0);
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
			// 上游列表可能每个周期一行（同 pid 多行），按 pid 分组后合并周期，避免相互覆盖
			$grouped = [];
			foreach ($res['data']['list'] as $item) {
				$upId = (int)($item['id'] ?? 0);
				if ($upId <= 0) {
					continue;
				}
				if ($upIds && !in_array($upId, $upIds, true)) {
					$skip++;
					continue;
				}
				$grouped[$upId][] = $item;
			}
			foreach ($grouped as $upId => $rows) {
				if (self::syncOneProduct($client, $supplierId, (int)$upId, $rows, $currency)) {
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

	/** 同步单个商品（rows 为同一 pid 的列表行，可能每周期一行）。 */
	protected static function syncOneProduct($client, $supplierId, $upId, array $rows, $currency)
	{
		global $DB, $date;
		$now = $date ?: date('Y-m-d H:i:s');
		$item = $rows[0] ?? [];

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
			foreach ($rows as $row) {
				$agentCents = self::itemPrice($row);
				if ($agentCents > 0) {
					break;
				}
			}
		}

		// 各周期价：列表行 → 详情 → 购物车算价，逐级补充合并（列表常只有单周期字段）
		$cycles = [];
		foreach ($rows as $row) {
			foreach (self::parseItemCycles($row) as $k => $entry) {
				if (!isset($cycles[$k])) {
					$cycles[$k] = $entry;
				}
			}
		}
		if ($detail) {
			foreach (self::parseDetailCycles($detail) as $k => $entry) {
				if (!isset($cycles[$k])) {
					$cycles[$k] = $entry;
				}
			}
		}
		// 探测购物车配置接口：一次请求拿到全部可选周期的真实 billingcycle 键与价格。
		// 列表/详情已有周期保持不变；仅补充缺失周期，并给缺失 up_cycle 的条目补上真实键。
		$probedKeys = [];
		foreach (self::probeCycles($client, $upId, $detail) as $key => $cfg) {
			$entry = self::cycleEntry($key, (int)($cfg['price_cents'] ?? 0), (string)($cfg['name'] ?? ''));
			$k = $entry['cycle'];
			$probedKeys[$k] = (string)$key;
			if (isset($cycles[$k])) {
				if ((string)($cycles[$k]['up_cycle'] ?? '') === '') {
					$cycles[$k]['up_cycle'] = (string)$key;
				}
				continue;
			}
			if ($entry['agent_price_cents'] <= 0) {
				continue;
			}
			$cycles[$k] = $entry;
		}
		// 兜底：购物车算价补充缺失周期。先探测一个周期，失败即放弃该商品试算
		//（避免对每个缺失周期都发请求，商品多时同步被拖垮）
		$trialOn = false;
		foreach (zjmf_cycles() as $cycle => $cfg) {
			if (isset($cycles[$cycle])) {
				continue;
			}
			if (!$trialOn) {
				$probe = self::trialPrice($client, $upId, $cycle);
				if ($probe <= 0) {
					break;
				}
				$trialOn = true;
				$cycles[$cycle] = [
					'cycle'             => $cycle,
					'up_cycle'          => $probedKeys[$cycle] ?? strtolower($cycle),
					'name'              => $cfg['name'],
					'agent_price_cents' => $probe,
				];
				continue;
			}
			$price = self::trialPrice($client, $upId, $cycle);
			if ($price > 0) {
				$cycles[$cycle] = [
					'cycle'             => $cycle,
					'up_cycle'          => $probedKeys[$cycle] ?? strtolower($cycle),
					'name'              => $cfg['name'],
					'agent_price_cents' => $price,
				];
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
			// 0. 清空购物车：该版本 settle(checkout=1) 会结算整辆购物车，
			//    若残留历史测试商品会把多件一起结算开通（曾实测一次开出多台机器）。
			//    先清空保证本次结算只涉及刚添加的这一件商品。
			try {
				$client->cartClear();
			} catch (CubeFinanceException $e) {
				// 清空失败不致命，继续尝试（可能购物车本就为空）
			}

			// 1. 添加产品至购物车（官方：POST /cart/add_to_shop）→ data.i 购物车位置
			$upCycle = self::upstreamCycle((int)($order['supplier_id'] ?? 0), $upProductId, $cycle);
			$addParams = [
				'pid'          => $upProductId,
				'billingcycle' => $upCycle,
				'qty'          => 1,
				'host'         => (string)($extra['host'] ?? self::randHost()),
				'password'     => (string)($extra['password'] ?? self::randPassword()),
			];
			foreach (['configoption', 'customfield', 'serverid', 'os', 'currencyid'] as $k) {
				if (isset($extra[$k]) && $extra[$k] !== '') {
					$addParams[$k] = $extra[$k];
				}
			}
			// 缺少 currencyid / configoption 时探测上游默认配置：
			// 有配置项的商品必须传 configoption（子项ID/数量），否则计价失败报
			// "此周期未配置价格或价格错误，请重新选择周期"（实测上游前台抓包确认）
			if (!isset($addParams['currencyid']) || !isset($addParams['configoption'])) {
				$cfgDefaults = self::probeConfigDefaults($client, $upProductId);
				if (!isset($addParams['currencyid']) && !empty($cfgDefaults['currencyid'])) {
					$addParams['currencyid'] = (int)$cfgDefaults['currencyid'];
				}
				if (!isset($addParams['configoption']) && !empty($cfgDefaults['configoption'])) {
					$addParams['configoption'] = $cfgDefaults['configoption'];
				}
			}
			$res = $client->post('cart/add_to_shop', $addParams);
			if (!self::respOk($res)) {
				// 上游拒绝（多为"此周期未配置价格"）：探测商品真实周期键，命中则重试一次，
				// 并把正确键写回商品周期（后续购买直接可用）
				$avail = [];
				if (self::isCyclePriceError($res)) {
					$avail = self::probeCycles($client, $upProductId);
					$matched = self::matchUpCycle($cycle, $avail);
					if ($matched !== '' && $matched !== $upCycle) {
						$addParams['billingcycle'] = $matched;
						self::persistUpCycle(
							(int)($order['supplier_id'] ?? 0),
							$upProductId,
							$cycle,
							$matched
						);
						$upCycle = $matched;
						$res = $client->post('cart/add_to_shop', $addParams);
					}
				}
				if (!self::respOk($res)) {
					$msg = self::respErr('上游添加购物车失败（周期: ' . $upCycle . '）', $res);
					if ($avail !== []) {
						$msg .= self::formatAvailable($avail);
					}
					return ['ok' => false, 'msg' => $msg];
				}
			}
			$position = self::findPosition($res);
			if ($position < 0) {
				// 该版本 add_to_shop 不返回位置（响应仅 status/msg/is_aff）：
				// 从购物车数据（GET /cart/get_shop_data，数组键/序号即位置 i）定位刚添加的产品
				$position = self::cartPosition($client, $upProductId, $upCycle);
			}
			if ($position < 0) {
				$raw = json_encode($res['data'] ?? $res, JSON_UNESCAPED_UNICODE);
				return ['ok' => false, 'msg' => '上游添加购物车后无法定位购物车位置（响应: '
					. self::truncate((string)$raw, 300) . '）'];
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

	/** 拉取上游我的主机列表（GET host/list），按供应商。用户端读取用短超时，避免拖垮页面。 */
	public static function hostList($supplier, array $extra = [])
	{
		$client = self::client($supplier, 8);
		if (!$client) {
			return ['ok' => false, 'msg' => '供应商连接信息不完整'];
		}
		try {
			$res = $client->hostList($extra);
			if (!self::respOk($res)) {
				return ['ok' => false, 'msg' => self::respErr('获取上游主机列表失败', $res)];
			}
			return ['ok' => true, 'data' => $res['data'] ?? []];
		} catch (CubeFinanceException $e) {
			return ['ok' => false, 'msg' => $e->getMessage()];
		}
	}

	/** 主机头信息（状态/账号/到期），按主机所属供应商。用户端读取用短超时，避免拖垮页面。 */
	public static function hostInfo($supplier, $upHostId)
	{
		$client = self::client($supplier, 8);
		if (!$client) {
			return ['ok' => false, 'msg' => '供应商连接信息不完整'];
		}
		// host/product 优先（状态等字段更稳定），失败回退 host/header
		foreach (['hostProduct', 'hostHeader'] as $method) {
			try {
				$res = $client->{$method}((int)$upHostId);
				if (($res['status'] ?? 0) == 200) {
					$resData = $res['data'] ?? [];
					$data = self::pickHostData($resData);
					return [
						'ok'             => true,
						'data'           => $data,
						'config_options' => isset($resData['config_options']) && is_array($resData['config_options'])
							? $resData['config_options'] : [],
						'custom_fields'  => isset($resData['custom_field_data']) && is_array($resData['custom_field_data'])
							? $resData['custom_field_data'] : [],
						'dcim'           => isset($resData['dcim']) && is_array($resData['dcim'])
							? $resData['dcim'] : [],
						'status'         => self::mapHostStatus($data),
					];
				}
			} catch (CubeFinanceException $e) {
				// 尝试下一个端点
			}
		}
		return ['ok' => false, 'msg' => '查询失败'];
	}

	/** 流量使用，按主机所属供应商。用户端读取用短超时，避免拖垮页面。 */
	public static function hostTraffic($supplier, $upHostId)
	{
		$client = self::client($supplier, 8);
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

	/**
	 * DCIM 综合信息（交换机端口 / 电源状态 / 重装次数 / 任务进度）。
	 * 单项失败不致命，仅用于展示；非 DCIM 产品会全部为空。
	 */
	public static function hostDcimInfo($supplier, $upHostId)
	{
		// 短超时，避免叠加请求拖慢详情页
		$client = self::client($supplier, 5);
		if (!$client) {
			return ['ok' => false, 'msg' => '供应商连接信息不完整'];
		}
		$out = [
			'detail'    => [],
			'power'     => '',
			'power_msg' => '',
			'reinstall' => [],
			'task'      => [],
		];
		$tasks = [
			'detail'    => function () use ($client, $upHostId) { return $client->dcimDetail((int)$upHostId); },
			'power'     => function () use ($client, $upHostId) { return $client->dcimPowerStatus((int)$upHostId); },
			'reinstall' => function () use ($client, $upHostId) { return $client->dcimCheckReinstall((int)$upHostId); },
			'task'      => function () use ($client, $upHostId) { return $client->dcimReinstallStatus((int)$upHostId); },
		];
		foreach ($tasks as $k => $fn) {
			try {
				$res = $fn();
				if (($res['status'] ?? 0) == 200) {
					$d = $res['data'] ?? [];
					if ($k === 'power') {
						$out['power']     = (string)($d['power'] ?? '');
						$out['power_msg'] = (string)($d['msg'] ?? '');
					} else {
						$out[$k] = $d;
					}
				}
			} catch (CubeFinanceException $e) {
				// 单项失败忽略
			}
		}
		return ['ok' => true, 'data' => $out];
	}

	/** DCIM 专用操作（rescue/bmc/cancel_task/reinstall/crack_pass 等）。 */
	public static function hostDcimAction($supplier, $upHostId, $action, $extra = [])
	{
		$client = self::client($supplier);
		if (!$client) {
			return ['ok' => false, 'msg' => '供应商连接信息不完整'];
		}
		try {
			$res = $client->dcimAction($action, (int)$upHostId, $extra);
			if (($res['status'] ?? 0) == 200) {
				return ['ok' => true, 'msg' => (string)($res['msg'] ?? '操作成功')];
			}
			return ['ok' => false, 'msg' => (string)($res['msg'] ?? '操作失败')];
		} catch (CubeFinanceException $e) {
			return ['ok' => false, 'msg' => $e->getMessage()];
		}
	}

	/** 重装可选系统列表（GET host/dedicatedserver），按上游主机 ID 获取。 */
	public static function hostDedicatedOs($supplier, $upHostId)
	{
		$client = self::client($supplier, 8);
		if (!$client) {
			return ['ok' => false, 'msg' => '供应商连接信息不完整'];
		}
		try {
			$res = $client->hostDedicatedServer((int)$upHostId);
			if (($res['status'] ?? 0) != 200) {
				error_log('[zjmfmanager_reserve] hostDedicatedServer failed: '
					. 'host_id=' . (int)$upHostId . ' status=' . ($res['status'] ?? '')
					. ' msg=' . ($res['msg'] ?? ''));
				return ['ok' => false, 'msg' => (string)($res['msg'] ?? '获取系统列表失败')];
			}
			$data = $res['data'] ?? [];
			return [
				'ok'      => true,
				'os_list' => is_array($data['cloud_os'] ?? null) ? $data['cloud_os'] : [],
				'groups'  => is_array($data['cloud_os_group'] ?? null) ? $data['cloud_os_group'] : [],
			];
		} catch (CubeFinanceException $e) {
			error_log('[zjmfmanager_reserve] hostDedicatedServer exception: ' . $e->getMessage());
			return ['ok' => false, 'msg' => $e->getMessage()];
		}
	}

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

	/**
	 * 购物车算价：优先官方价格计算接口 POST /cart/get_total，
	 * 失败回退配置页 GET /cart/set_config。
	 */
	protected static function trialPrice($client, $upId, $cycle)
	{
		$bc = strtolower($cycle);
		try {
			$price = self::parseTrialPrice($client->cartGetTotal([
				'pid'          => $upId,
				'billingcycle' => $bc,
				'qty'          => 1,
			]));
			if ($price > 0) {
				return $price;
			}
		} catch (CubeFinanceException $e) {
			// 忽略，走回退
		}
		try {
			return self::parseTrialPrice($client->cartSetConfig([
				'pid'          => $upId,
				'billingcycle' => $bc,
			]));
		} catch (CubeFinanceException $e) {
			return 0;
		}
	}

	protected static function parseTrialPrice($res)
	{
		$data = $res['data'] ?? [];
		if (!is_array($data)) {
			return 0;
		}
		// 官方 get_total 响应：data.products[]（signal_price 为单个产品周期费用）
		if (isset($data['products']) && is_array($data['products'])) {
			foreach ($data['products'] as $p) {
				if (!is_array($p)) {
					continue;
				}
				foreach (['signal_price', 'product_price', 'price_total', 'total', 'amount', 'subtotal'] as $k) {
					if (isset($p[$k]) && $p[$k] !== '' && $p[$k] !== null) {
						$c = self::toCents($p[$k]);
						if ($c > 0) {
							return $c;
						}
					}
				}
			}
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
	 * 从列表行提取各周期价格（分）。
	 * 兼容三种结构（优先完整结构，避免单周期字段提前返回漏掉其余周期）：
	 *   a. 完整周期：cycle/pricing 为数组或映射
	 *   b. 并行数组：billingcycle[] + billingcycle_price[]（+ billingcycle_zh[]）
	 *   c. 单周期：billingcycle + price + billingcycle_zh（仅作最后兜底）
	 * 解析失败返回空数组。
	 */
	protected static function parseItemCycles($item)
	{
		if (!is_array($item)) {
			return [];
		}
		// a. 完整周期结构：cycle/pricing 为数组或映射
		$raw = $item['cycle'] ?? $item['pricing'] ?? null;
		if (is_string($raw)) {
			$decoded = json_decode($raw, true);
			$raw = is_array($decoded) ? $decoded : null;
		}
		if (is_array($raw) && $raw !== []) {
			$out = self::parseCycleMap($raw);
			if ($out !== []) {
				return $out;
			}
		}
		// b. 并行数组结构：billingcycle[] + billingcycle_price[]（v10 列表常见）
		$out = self::parseParallelCycles($item);
		if ($out !== []) {
			return $out;
		}
		// c. 单周期结构：billingcycle + price + billingcycle_zh
		$key = (string)($item['billingcycle'] ?? '');
		if ($key !== '') {
			$cents = self::toCents(self::pickPrice($item));
			if ($cents > 0) {
				$entry = self::cycleEntry($key, $cents, (string)($item['billingcycle_zh'] ?? ''));
				return [$entry['cycle'] => $entry];
			}
		}
		return [];
	}

	/**
	 * 并行数组结构：billingcycle[] + billingcycle_price[]（+ billingcycle_zh[]）。
	 * 若任一数组缺失或长度不一致，返回空数组。
	 */
	protected static function parseParallelCycles($item)
	{
		$keys = $item['billingcycle'] ?? null;
		$prices = $item['billingcycle_price'] ?? $item['billingcycleprices'] ?? null;
		if (!is_array($keys) || $keys === [] || !is_array($prices)) {
			return [];
		}
		$names = $item['billingcycle_zh'] ?? null;
		if (!is_array($names)) {
			$names = null;
		}
		$out = [];
		foreach ($keys as $i => $k) {
			$k = (string)$k;
			if ($k === '' || !isset($prices[$i])) {
				continue;
			}
			$cents = self::toCents($prices[$i]);
			if ($cents <= 0) {
				continue;
			}
			$name = $names ? (string)($names[$i] ?? '') : '';
			$entry = self::cycleEntry($k, $cents, $name);
			if (!isset($out[$entry['cycle']])) {
				$out[$entry['cycle']] = $entry;
			}
		}
		return $out;
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
		$out = self::parseParallelCycles($prod);
		if ($out !== []) {
			return $out;
		}
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

	/* ============================================================
	 *  周期键探测（add_to_shop 失败时定位上游真实 billingcycle 键）
	 * ============================================================ */

	/**
	 * 探测上游商品可选周期（真实 billingcycle 键 + 中文名 + 价格）。
	 * 优先 GET /cart/get_product_config（含该产品可选周期数据），
	 * 失败回退商品详情。返回 [billingcycle键 => ['name'=>, 'price_cents'=>]]；
	 * 无结果返回 []。
	 *
	 * @param CubeFinanceClient $client
	 * @param int               $upId
	 * @param array|null        $detail 已抓取的详情响应（避免重复请求）
	 */
	protected static function probeCycles($client, $upId, $detail = null)
	{
		$out = [];
		try {
			$res = $client->cartGetProductConfig((int)$upId);
			if (self::respOk($res)) {
				$out = self::parseConfigCycles($res);
			}
		} catch (CubeFinanceException $e) {
			$out = [];
		}
		if ($out !== []) {
			return $out;
		}
		if ($detail === null) {
			try {
				$detail = $client->productDetail((int)$upId, 'agent');
			} catch (CubeFinanceException $e) {
				return [];
			}
		}
		return self::parseDetailCycleList($detail);
	}

	/**
	 * 解析购物车配置接口响应中的周期列表。
	 * 兼容 data.products（数组或单产品）与 data.product 两种外壳；
	 * 周期可来自 cycle（JSON 串/数组/映射）、pricing 映射、并行数组。
	 */
	protected static function parseConfigCycles($res)
	{
		$data = $res['data'] ?? [];
		if (!is_array($data)) {
			return [];
		}
		$products = $data['products'] ?? $data['product'] ?? null;
		if (!is_array($products)) {
			return [];
		}
		if (isset($products['cycle']) || isset($products['pricing'])
			|| isset($products['billingcycle']) || isset($products['id'])) {
			$products = [$products];
		}
		$out = [];
		foreach ($products as $prod) {
			if (!is_array($prod)) {
				continue;
			}
			foreach (self::cycleSourceCandidates($prod) as $item) {
				$key = (string)($item['billingcycle'] ?? '');
				if ($key === '') {
					continue;
				}
				$cents = self::toCents(
					$item['price'] ?? $item['product_price'] ?? $item['price_total'] ?? 0
				);
				$name = (string)($item['billingcycle_zh'] ?? '');
				if (!isset($out[$key])) {
					$out[$key] = ['name' => $name, 'price_cents' => max(0, $cents)];
				} elseif ($cents > 0 && $out[$key]['price_cents'] <= 0) {
					$out[$key]['price_cents'] = max(0, $cents);
				}
			}
		}
		return $out;
	}

	/**
	 * 从单个产品数据提取周期条目数组 [{billingcycle, billingcycle_zh, price}]。
	 * 优先完整周期（cycle/pricing），其次并行数组（billingcycle[]+billingcycle_price[]）。
	 */
	protected static function cycleSourceCandidates($prod)
	{
		$items = [];
		$raw = $prod['cycle'] ?? $prod['pricing'] ?? null;
		if (is_string($raw)) {
			$decoded = json_decode($raw, true);
			$raw = is_array($decoded) ? $decoded : null;
		}
		if (is_array($raw)) {
			if (isset($raw[0]) && is_array($raw[0])) {
				// 数组形式：[{billingcycle, price, billingcycle_zh}]
				foreach ($raw as $it) {
					if (is_array($it) && (string)($it['billingcycle'] ?? '') !== '') {
						$items[] = $it;
					}
				}
			} else {
				// 映射形式：{cycle: 价格 或 {price:...}}
				foreach ($raw as $k => $v) {
					$items[] = is_array($v)
						? array_merge($v, ['billingcycle' => (string)$k])
						: ['billingcycle' => (string)$k, 'price' => $v];
				}
			}
		}
		if ($items !== []) {
			return $items;
		}
		// 并行数组形式
		$keys = $prod['billingcycle'] ?? null;
		$prices = $prod['billingcycle_price'] ?? $prod['billingcycleprices'] ?? null;
		if (is_array($keys) && is_array($prices)) {
			$names = is_array($prod['billingcycle_zh'] ?? null) ? $prod['billingcycle_zh'] : null;
			foreach ($keys as $i => $k) {
				$items[] = [
					'billingcycle'    => (string)$k,
					'billingcycle_zh' => $names ? (string)($names[$i] ?? '') : '',
					'price'           => $prices[$i] ?? 0,
				];
			}
		}
		return $items;
	}

	/** 详情响应 → [billingcycle键 => ['name'=>, 'price_cents'=>]]。 */
	protected static function parseDetailCycleList($detail)
	{
		$out = [];
		foreach (self::parseDetailCycles($detail) as $entry) {
			$key = (string)($entry['up_cycle'] ?? '');
			if ($key === '') {
				continue;
			}
			$out[$key] = [
				'name'        => (string)($entry['name'] ?? ''),
				'price_cents' => (int)($entry['agent_price_cents'] ?? 0),
			];
		}
		return $out;
	}

	/* ============================================================
	 *  配置项默认值探测（add_to_shop 需要 currencyid + configoption）
	 * ============================================================ */

	/**
	 * 探测商品默认配置（货币ID + 配置项默认选择），用于 add_to_shop 计价。
	 * 优先 GET /cart/get_product_config，失败回退商品详情。
	 * 返回 ['currencyid'=>int, 'configoption'=>[optionId=>子项ID或数量]]；
	 * 探测不到 configoption 时至少带回 currencyid。
	 *
	 * @param CubeFinanceClient $client
	 * @param int               $upId
	 * @param array|null        $detail 已抓取的详情响应（避免重复请求）
	 */
	protected static function probeConfigDefaults($client, $upId, $detail = null)
	{
		try {
			$res = $client->cartGetProductConfig((int)$upId);
			if (self::respOk($res)) {
				$out = self::parseConfigDefaults($res);
				if (!empty($out['configoption'])) {
					return $out;
				}
			}
		} catch (CubeFinanceException $e) {
			// 忽略，走详情回退
		}
		if ($detail === null) {
			try {
				$detail = $client->productDetail((int)$upId, 'agent');
			} catch (CubeFinanceException $e) {
				return ['currencyid' => 0, 'configoption' => []];
			}
		}
		return self::parseConfigDefaults($detail);
	}

	/**
	 * 从响应中解析 currencyid 与 configoption 默认选择。
	 * 兼容 data.products（数组/单产品）与 data.product 外壳；
	 * 配置项结构兼容数组组/映射组/扁平默认值/数量型多种形态。
	 */
	protected static function parseConfigDefaults($res)
	{
		$data = $res['data'] ?? [];
		if (!is_array($data)) {
			return ['currencyid' => 0, 'configoption' => []];
		}
		$out = ['currencyid' => 0, 'configoption' => []];
		if (isset($data['default_currency']) && is_numeric($data['default_currency'])) {
			$out['currencyid'] = (int)$data['default_currency'];
		}
		$currencies = $data['currencies'] ?? null;
		if (is_array($currencies)) {
			foreach ($currencies as $c) {
				if (is_array($c) && isset($c['id']) && is_numeric($c['id'])) {
					if ($out['currencyid'] <= 0) {
						$out['currencyid'] = (int)$c['id'];
					}
					break;
				}
			}
		}
		$products = $data['products'] ?? $data['product'] ?? null;
		if (!is_array($products)) {
			if ($out['currencyid'] <= 0) {
				$out['currencyid'] = 1;
			}
			return $out;
		}
		if (isset($products['id']) || isset($products['pid'])
			|| isset($products['configoption']) || isset($products['config_option'])) {
			$products = [$products];
		}
		$groups = [];
		foreach ($products as $prod) {
			if (!is_array($prod)) {
				continue;
			}
			// 产品级 currencyid（映射形式取首个键）
			if ($out['currencyid'] <= 0 && isset($prod['currencyid']) && is_array($prod['currencyid'])) {
				foreach ($prod['currencyid'] as $cid => $cfg) {
					if (is_numeric($cid) && (int)$cid > 0) {
						$out['currencyid'] = (int)$cid;
						break;
					}
				}
			}
			$co = $prod['configoption'] ?? $prod['config_option'] ?? null;
			if (is_array($co)) {
				$groups = array_merge($groups, self::normalizeConfigGroups($co));
			}
		}
		foreach ($groups as $g) {
			if (!is_array($g)) {
				continue;
			}
			$oid = (int)($g['id'] ?? $g['configoption_id'] ?? 0);
			if ($oid <= 0) {
				continue;
			}
			$val = self::pickConfigDefault($g);
			if ($val !== null && $val !== '') {
				$out['configoption'][(string)$oid] = (string)$val;
			}
		}
		if ($out['currencyid'] <= 0) {
			$out['currencyid'] = 1; // 兜底默认货币
		}
		return $out;
	}

	/** 将 configoption 数据规范化为配置组列表。 */
	protected static function normalizeConfigGroups($co)
	{
		$groups = [];
		if (isset($co[0]) && is_array($co[0])) {
			return $co; // 数组组形式
		}
		foreach ($co as $k => $v) {
			if (!is_array($v)) {
				// 扁平：optionId => 默认值
				$groups[] = ['id' => is_numeric($k) ? $k : 0, '_flat_value' => $v];
				continue;
			}
			if (isset($v['option']) || isset($v['options']) || isset($v['id']) || isset($v['configoption_id'])) {
				if (!isset($v['id']) && !isset($v['configoption_id']) && is_numeric($k)) {
					$v['id'] = (int)$k;
				}
				$groups[] = $v;
				continue;
			}
			// 映射：optionId => 子项数组
			$groups[] = ['id' => is_numeric($k) ? (int)$k : 0, 'option' => $v];
		}
		return $groups;
	}

	/**
	 * 从单个配置组取默认子项值（子项ID或数量）。
	 * 优先 checked/selected/default 标记，否则取第一个子项。
	 */
	protected static function pickConfigDefault($g)
	{
		if (array_key_exists('_flat_value', $g)) {
			return (string)$g['_flat_value'];
		}
		$type = strtolower((string)($g['type'] ?? ''));
		if (in_array($type, ['qty', 'quantity', 'number'], true)) {
			$q = $g['qty'] ?? $g['quantity'] ?? $g['value'] ?? 1;
			return (string)$q;
		}
		$subs = $g['option'] ?? $g['options'] ?? $g['sub_option']
			?? $g['suboption'] ?? $g['child'] ?? $g['values'] ?? null;
		if (!is_array($subs)) {
			return null;
		}
		$first = null;
		foreach ($subs as $s) {
			if (!is_array($s)) {
				continue;
			}
			if ($first === null) {
				$first = $s;
			}
			foreach (['checked', 'selected', 'default', 'is_check', 'is_default'] as $f) {
				if (isset($s[$f]) && ((int)$s[$f] === 1 || (string)$s[$f] === '1')) {
					$first = $s;
					break 2;
				}
			}
		}
		if ($first === null) {
			return null;
		}
		$id = $first['id'] ?? $first['suboption_id'] ?? $first['value'] ?? null;
		return $id === null ? null : (string)$id;
	}

	/** 上游失败信息是否与周期/价格相关（命中才触发探测重试）。 */
	protected static function isCyclePriceError($res)
	{
		$msg = (string)($res['msg'] ?? '');
		return $msg !== '' && (
			strpos($msg, '未配置价格') !== false
			|| strpos($msg, '价格错误') !== false
			|| strpos($msg, '周期') !== false
			|| stripos($msg, 'cycle') !== false
		);
	}

	/**
	 * 本地周期键 → 上游真实 billingcycle 键。
	 * 忽略大小写并按别名（monthly/month、quarterly/quarter 等）匹配，
	 * 也匹配中文名（月付/月/季付…）；找不到返回原键。
	 */
	protected static function matchUpCycle($localCycle, array $available)
	{
		$localCycle = (string)$localCycle;
		if ($localCycle === '' || $available === []) {
			return $localCycle;
		}
		$aliases = self::cycleAliases($localCycle);
		foreach ($available as $key => $cfg) {
			$candidate = (string)$key;
			if (in_array(self::normKey($candidate), $aliases, true)) {
				return $candidate;
			}
			$name = (string)($cfg['name'] ?? '');
			if ($name !== '' && in_array(self::normKey($name), $aliases, true)) {
				return $candidate;
			}
		}
		$normLocal = self::normKey($localCycle);
		foreach ($available as $key => $cfg) {
			if (self::normKey((string)$key) === $normLocal) {
				return (string)$key;
			}
		}
		return $localCycle;
	}

	/** 本地周期键的别名集合（归一化后，含中文名）。 */
	protected static function cycleAliases($localCycle)
	{
		$norm = self::normKey($localCycle);
		$map = [
			'monthly'      => ['monthly', 'month', 'm', '月', '月付'],
			'quarterly'    => ['quarterly', 'quarter', 'q', '季', '季付'],
			'semiannually' => ['semiannually', 'semiannual', 'halfyear', 'half_year', 'half', 'semi', '半年', '半年付'],
			'annually'     => ['annually', 'annual', 'yearly', 'year', 'y', '年', '年付'],
			'biennially'   => ['biennially', 'biennial', 'biennium', 'twoyear', 'two_year', '两年', '两年付'],
			'triennially'  => ['triennially', 'triennial', 'triennium', 'threeyear', 'three_year', '三年', '三年付'],
		];
		foreach ($map as $k => $list) {
			if ($norm === $k || in_array($norm, $list, true)) {
				return $list;
			}
		}
		return [$norm];
	}

	/** 键归一化：小写 + 去非字母数字（保留中文）。 */
	protected static function normKey($key)
	{
		return strtolower(preg_replace('/[^a-z0-9\x{4e00}-\x{9fa5}]/iu', '', (string)$key));
	}

	/** 可用周期列表渲染为可读文案（供失败信息展示）。 */
	protected static function formatAvailable(array $avail)
	{
		$parts = [];
		foreach ($avail as $key => $cfg) {
			$name = (string)($cfg['name'] ?? '');
			$parts[] = $name !== '' ? $key . '(' . $name . ')' : $key;
		}
		return $parts === [] ? '' : '；上游可用周期：' . implode('、', $parts);
	}

	/**
	 * 将本地周期对应的上游键写回商品 cycles JSON（后续购买直接使用）。
	 */
	protected static function persistUpCycle($supplierId, $upProductId, $localCycle, $upCycle)
	{
		global $DB;
		$product = zjmf_product_get_by_up((int)$supplierId, (int)$upProductId);
		if (!$product) {
			return;
		}
		$cycles = json_decode((string)($product['cycles'] ?? ''), true);
		if (!is_array($cycles)) {
			return;
		}
		$changed = false;
		foreach ($cycles as &$cfg) {
			if (is_array($cfg) && (string)($cfg['cycle'] ?? '') === (string)$localCycle) {
				if ((string)($cfg['up_cycle'] ?? '') !== (string)$upCycle) {
					$cfg['up_cycle'] = (string)$upCycle;
					$changed = true;
				}
				break;
			}
		}
		unset($cfg);
		if ($changed) {
			$DB->query_prepare(
				"UPDATE MN_plugin_zjmf_product SET cycles=?, updated_at=?
				 WHERE id=?",
				[
					json_encode($cycles, JSON_UNESCAPED_UNICODE),
					date('Y-m-d H:i:s'),
					(int)$product['id'],
				]
			);
		}
	}

	/** 构建单个周期条目（键归一化 + 名称兜底）。 */
	protected static function cycleEntry($key, $cents, $name = '')
	{
		$norm = self::normalizeCycleKey($key);
		$known = zjmf_cycles();
		return [
			'cycle'             => $norm,
			// 上游原始 billingcycle 值（如 monthly），购买时原样回传
			'up_cycle'          => (string)$key,
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

	/**
	 * 从购物车数据中定位刚添加产品的购物车位置。
	 * 部分版本 add_to_shop 不返回位置 i，需 GET /cart/get_shop_data 后
	 * 按 productid 匹配（购物车数组的键/序号即位置 i；同商品取最后一项，即刚添加的）。
	 *
	 * @param CubeFinanceClient $client
	 * @param int               $upProductId
	 * @param string            $upCycle 上游 billingcycle 键
	 * @return int 位置，找不到返回 -1
	 */
	protected static function cartPosition($client, $upProductId, $upCycle)
	{
		try {
			$res = $client->cartGetShopData();
		} catch (CubeFinanceException $e) {
			return -1;
		}
		if (!self::respOk($res)) {
			return -1;
		}
		$products = $res['data']['cart_products'] ?? null;
		if (!is_array($products)) {
			return -1;
		}
		$wantPid = (string)$upProductId;
		$wantCycle = strtolower((string)$upCycle);
		$lastPid = -1;
		$lastMatch = -1;
		foreach ($products as $i => $p) {
			if (!is_array($p)) {
				continue;
			}
			$pid = (string)($p['productid'] ?? $p['pid'] ?? $p['id'] ?? '');
			if ($pid !== $wantPid) {
				continue;
			}
			$lastPid = (int)$i;
			$cycle = strtolower((string)($p['billingcycle'] ?? ''));
			if ($cycle === $wantCycle || $cycle === '') {
				$lastMatch = (int)$i; // 周期匹配（或未知）的最后一个即刚添加项
			}
		}
		if ($lastMatch >= 0) {
			return $lastMatch;
		}
		return $lastPid; // 仅 pid 匹配的最后一个
	}

	/**
	 * 从加购响应中取购物车位置 data.i（兼容字符串 data、其他位置键、一层嵌套）。
	 *
	 * @param array $res 响应数组
	 * @return int 位置，找不到返回 -1
	 */
	protected static function findPosition($res)
	{
		$d = $res['data'] ?? null;
		if (is_string($d)) {
			if (is_numeric($d)) {
				return (int)$d;
			}
			$decoded = json_decode($d, true);
			if (is_array($decoded)) {
				$d = $decoded;
			}
		}
		if (is_numeric($d)) {
			return (int)$d;
		}
		if (!is_array($d)) {
			return -1;
		}
		foreach (['i', 'position', 'pos', 'cart_i', 'key', 'id'] as $k) {
			if (!array_key_exists($k, $d)) {
				continue;
			}
			$v = $d[$k];
			if (is_array($v)) {
				$v = reset($v);
			}
			if (is_numeric($v)) {
				return (int)$v;
			}
		}
		// 一层嵌套深搜（如 data.products[].i）
		foreach ($d as $v) {
			if (is_array($v)) {
				$sub = self::findPosition(['data' => $v]);
				if ($sub >= 0) {
					return $sub;
				}
			}
		}
		return -1;
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
	 * 插件周期键 → 上游 billingcycle 值。
	 * 优先取同步时保留的上游原始值（up_cycle），未同步/旧数据回退小写（官方周期如 monthly、day、hour）。
	 */
	protected static function upstreamCycle($supplierId, $upProductId, $cycle)
	{
		$cycle = (string)$cycle;
		if ($cycle !== '') {
			$product = zjmf_product_get_by_up((int)$supplierId, (int)$upProductId);
			if ($product) {
				$cycles = zjmf_product_cycles($product);
				$entry = $cycles[$cycle] ?? null;
				if (is_array($entry) && (string)($entry['up_cycle'] ?? '') !== '') {
					return (string)$entry['up_cycle'];
				}
			}
		}
		return strtolower($cycle);
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

	/** 从主机详情响应提取主机数据（兼容 host_data/host/info/list/data 键与单对象/数组结构）。 */
	protected static function pickHostData($data)
	{
		if (!is_array($data)) {
			return [];
		}
		foreach (['host_data', 'host', 'info', 'list', 'data'] as $k) {
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
				// 上游部分版本 nextduedate 为 Unix 时间戳，统一归一化为 Y-m-d
				'renew_date' => zjmf_normalize_date(
					(string)($host['nextduedate'] ?? $host['renew_date'] ?? $host['renewdate'] ?? '')
				),
			];
		} catch (CubeFinanceException $e) {
			return ['username' => '', 'password' => '', 'name' => '', 'renew_date' => ''];
		}
	}

	/** 上游主机状态 → 本地展示状态（active/suspend/pending/terminated/unknown）。 */
	public static function mapHostStatus($data)
	{
		if (!is_array($data)) {
			return 'unknown';
		}
		$st = strtolower(trim((string)($data['status'] ?? $data['domainstatus'] ?? '')));
		if ($st !== '') {
			if (in_array($st, ['active', 'on', 'true', 'completed', '运行中'], true)) {
				return 'active';
			}
			if (in_array($st, ['pending', 'wait', 'waiting', '待开通'], true)) {
				return 'pending';
			}
			if (in_array($st, ['suspended', 'suspend', 'paused', 'off', '已暂停'], true)) {
				return 'suspend';
			}
			if (in_array($st, ['cancelled', 'cancel', 'terminated', 'terminate', 'fraud', '已终止'], true)) {
				return 'terminated';
			}
			return 'unknown';
		}
		// 无状态字段时用 qk 兜底（false 视为不可用）
		$qk = $data['qk'] ?? null;
		if ($qk !== null && in_array((string)$qk, ['false', '0', ''], true)) {
			return 'suspend';
		}
		return 'unknown';
	}
}
