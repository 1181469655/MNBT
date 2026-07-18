<?php
/**
 * domain_shop 插件 - DNS 记录管理函数
 * 操作 plg_dns_record 表，配合 provider 适配器调用服务商 API
 */
if (!defined('IN_CRONLITE')) exit;

require_once __DIR__ . '/provider/dnspod.php';

/**
 * 取已启用的 DNS 服务商
 */
function dns_provider_list_enabled()
{
	global $DB;
	return $DB->get_all_prepare("SELECT * FROM plg_dns_provider WHERE qk='true' order by id asc") ?: [];
}

function dns_provider_get($id)
{
	global $DB;
	return $DB->get_row_prepare("SELECT * FROM plg_dns_provider WHERE id=? limit 1", [(int)$id]);
}

/**
 * 根据 slug 取服务商（如 dnspod）
 */
function dns_provider_get_by_slug($slug)
{
	global $DB;
	return $DB->get_row_prepare("SELECT * FROM plg_dns_provider WHERE slug=? and qk='true' limit 1", [$slug]);
}

/**
 * 实例化服务商适配器
 * @return object|null 适配器实例，失败返回 null
 */
function dns_provider_adapter($providerRow)
{
	if (!$providerRow) return null;
	$slug = $providerRow['slug'] ?? '';
	switch ($slug) {
		case 'dnspod':
			return new DomainShop_DNSPod($providerRow['api_id'], $providerRow['api_secret']);
		// 后续扩展：cloudflare / aliyun
		default:
			return null;
	}
}

/**
 * 创建 DNS 记录（本地 + 远程）
 * @param string $user 用户名
 * @param int $providerId 服务商 ID
 * @param string $domain 主域名（如 example.com）
 * @param string $name 主机记录（如 www / @）
 * @param string $type A / CNAME / TXT / MX / AAAA
 * @param string $value 记录值
 * @param int $ttl
 * @param int $auto 是否系统自动创建
 * @return array ['ok'=>bool, 'msg'=>string, 'id'=>int]
 */
function dns_record_create($user, $providerId, $domain, $name, $type, $value, $ttl = 600, $auto = 0)
{
	global $DB, $date;
	$provider = dns_provider_get($providerId);
	if (!$provider) return ['ok' => false, 'msg' => 'DNS 服务商不存在'];

	$adapter = dns_provider_adapter($provider);
	if (!$adapter) return ['ok' => false, 'msg' => '不支持的 DNS 服务商'];

	$domain = strtolower(trim($domain));
	$name = trim($name);
	$type = strtoupper(trim($type));
	$value = trim($value);
	$ttl = (int)$ttl;

	if ($domain === '' || $name === '' || $type === '' || $value === '') {
		return ['ok' => false, 'msg' => '参数不能为空'];
	}

	// 先在远程创建
	$remoteId = $adapter->createRecord($domain, $name, $type, $value, $ttl);
	if (!$remoteId) {
		return ['ok' => false, 'msg' => '远程创建失败：' . $adapter->lastError()];
	}

	// 写本地表
	$ok = $DB->query_prepare(
		"INSERT INTO `plg_dns_record` (`user`, `provider_id`, `domain`, `name`, `type`, `value`, `ttl`, `remote_id`, `auto`, `qk`, `created_at`) VALUES (?,?,?,?,?,?,?,?,?,?,?)",
		[$user, $providerId, $domain, $name, $type, $value, $ttl, $remoteId, $auto ? 1 : 0, 'true', $date]
	);
	if (!$ok) {
		// 本地写入失败，回滚远程
		$adapter->deleteRecord($domain, $remoteId);
		return ['ok' => false, 'msg' => '本地记录写入失败：' . $DB->error()];
	}

	$rid = (int)$DB->insert_id();
	return ['ok' => true, 'msg' => '创建成功', 'id' => $rid];
}

/**
 * 删除 DNS 记录（本地 + 远程）
 */
function dns_record_delete($recordId, $user = null)
{
	global $DB;
	$recordId = (int)$recordId;
	$row = $DB->get_row_prepare("SELECT * FROM plg_dns_record WHERE id=? limit 1", [$recordId]);
	if (!$row) return ['ok' => false, 'msg' => '记录不存在'];

	if ($user !== null && $row['user'] !== $user) {
		return ['ok' => false, 'msg' => '无权操作'];
	}

	$provider = dns_provider_get($row['provider_id']);
	$adapter = $provider ? dns_provider_adapter($provider) : null;
	if ($adapter && $row['remote_id']) {
		$adapter->deleteRecord($row['domain'], $row['remote_id']);
	}

	$ok = $DB->query_prepare("DELETE FROM plg_dns_record WHERE id=? limit 1", [$recordId]);
	if (!$ok) return ['ok' => false, 'msg' => '本地删除失败：' . $DB->error()];
	return ['ok' => true, 'msg' => '删除成功'];
}

/**
 * 列出用户的 DNS 记录
 */
function dns_record_list_by_user($user)
{
	global $DB;
	return $DB->get_all_prepare("SELECT * FROM plg_dns_record WHERE user=? order by id desc", [$user]) ?: [];
}

/**
 * 列出全部 DNS 记录（管理端用）
 */
function dns_record_list_all()
{
	global $DB;
	return $DB->get_all_prepare("SELECT * FROM plg_dns_record order by id desc limit 500") ?: [];
}

/**
 * 为主机自动创建 A 记录
 * 在 host.created 钩子触发时调用：为用户在每个 DNS API 通道域名商品下
 * 创建一条 A 记录指向节点 IP。
 *
 * 仅处理 channel='dnsapi' 的商品；泛解析（pan）商品依赖域名整体泛 A 记录，
 * 无需逐主机建记录。
 *
 * @param array $host 主机行（含 user/ssbt/sqldz 等）
 * @param array $ctx 钩子上下文
 * @return array ['ok'=>bool, 'msg'=>string]
 */
function dns_record_auto_create_for_host($host, $ctx = [])
{
	global $DB;
	if (!is_array($host)) return ['ok' => false, 'msg' => 'host 不是数组'];
	$user = $host['user'] ?? '';
	$ssbt = $host['ssbt'] ?? '';
	if (!$user || !$ssbt) return ['ok' => false, 'msg' => '缺少 user 或 ssbt'];

	// 取节点 IP
	$bt = $DB->get_row_prepare("SELECT * FROM MN_bt WHERE btdh=? limit 1", [$ssbt]);
	if (!$bt) return ['ok' => false, 'msg' => '宝塔节点不存在'];
	$ip = $bt['btip'];
	if (!$ip) return ['ok' => false, 'msg' => '节点 IP 为空'];

	// 取该节点下所有上架域名商品
	$products = domain_product_list_by_node($ssbt);
	if (!$products) return ['ok' => false, 'msg' => '该节点无上架域名商品'];

	$created = 0; $failed = 0; $skipped = 0;
	foreach ($products as $prod) {
		// 仅 DNS API 通道商品需要自动建记录
		if (($prod['channel'] ?? 'pan') !== 'dnsapi') {
			$skipped++;
			continue;
		}
		$providerId = (int)($prod['provider_id'] ?? 0);
		if ($providerId <= 0) {
			$failed++;
			continue;
		}

		$domain = $prod['url'];
		$name = $user;  // 用用户名作为主机记录

		// 避免重复创建
		$exists = $DB->get_row_prepare(
			"SELECT id FROM plg_dns_record WHERE user=? and domain=? and name=? and type='A' limit 1",
			[$user, $domain, $name]
		);
		if ($exists) continue;

		$r = dns_record_create($user, $providerId, $domain, $name, 'A', $ip, 600, 1);
		if (!empty($r['ok'])) $created++; else $failed++;
	}

	return ['ok' => true, 'msg' => "自动创建 {$created} 条，失败 {$failed} 条，跳过泛解析 {$skipped} 条"];
}
