<?php
/**
 * domain_shop 插件 - 域名商品 CRUD 函数
 * 迁移自原 admin/api/ym.php，操作 plg_domain_product 表
 *
 * 通道（channel）字段说明：
 * - pan：泛解析通道。域名通过 *.example.com 泛解析到节点 IP，
 *   购买时调宝塔绑定 + 共享主机改 hosts/反代，不调 DNS API。
 * - dnsapi：DNS API 通道。域名需已托管在对应 DNS 服务商（如 DNSPod），
 *   购买时除调宝塔绑定外，还通过 DNS API 创建 A 记录指向节点 IP。
 */
if (!defined('IN_CRONLITE')) exit;

/**
 * 表结构自动升级：
 * - 若 plg_domain_product / plg_dns_provider / plg_dns_record 表不存在，则按 install.sql 中最新结构创建
 * - 若 plg_domain_product 表已存在但缺少 channel / provider_id 字段，则 ALTER 补齐
 * 在 bootstrap.php 引入本文件后立即调用一次。
 */
function domain_product_schema_upgrade()
{
	global $DB;
	if (!isset($DB) || !is_object($DB)) return;

	// 1. plg_domain_product 表
	$tbl = $DB->get_row_prepare("SHOW TABLES LIKE 'plg_domain_product'");
	if (!$tbl) {
		// 表不存在，按最新结构直接创建（含 channel / provider_id 字段）
		@$DB->query("CREATE TABLE IF NOT EXISTS `plg_domain_product` (
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`url` varchar(128) NOT NULL COMMENT '一级域名',
			`btdh` varchar(250) NOT NULL COMMENT '所属宝塔节点代号',
			`jg` varchar(250) NOT NULL COMMENT '解析价格',
			`date` varchar(50) NOT NULL COMMENT '添加时间',
			`js` varchar(50) NOT NULL COMMENT '域名介绍',
			`json` text NOT NULL COMMENT '已购用户列表 JSON',
			`qk` varchar(50) NOT NULL DEFAULT 'true' COMMENT '上架状态 true/false',
			`channel` varchar(32) NOT NULL DEFAULT 'pan' COMMENT '通道: pan=泛解析 / dnsapi=DNS API',
			`provider_id` int(11) NOT NULL DEFAULT 0 COMMENT 'DNS 服务商 ID（channel=dnsapi 时关联 plg_dns_provider.id）',
			PRIMARY KEY (`id`),
			KEY `url` (`url`),
			KEY `btdh` (`btdh`),
			KEY `channel` (`channel`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='域名商品表'");
	} else {
		// 表已存在，检测并补齐字段
		$cols = $DB->get_all_prepare("SHOW COLUMNS FROM `plg_domain_product` LIKE 'channel'") ?: [];
		if (empty($cols)) {
			@$DB->query("ALTER TABLE `plg_domain_product` ADD COLUMN `channel` varchar(32) NOT NULL DEFAULT 'pan' COMMENT '通道: pan=泛解析 / dnsapi=DNS API' AFTER `qk`");
		}
		$cols = $DB->get_all_prepare("SHOW COLUMNS FROM `plg_domain_product` LIKE 'provider_id'") ?: [];
		if (empty($cols)) {
			@$DB->query("ALTER TABLE `plg_domain_product` ADD COLUMN `provider_id` int(11) NOT NULL DEFAULT 0 COMMENT 'DNS 服务商 ID（channel=dnsapi 时关联 plg_dns_provider.id）' AFTER `channel`");
		}
	}

	// 2. plg_dns_provider 表（若不存在则创建）
	$tbl = $DB->get_row_prepare("SHOW TABLES LIKE 'plg_dns_provider'");
	if (!$tbl) {
		@$DB->query("CREATE TABLE IF NOT EXISTS `plg_dns_provider` (
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`slug` varchar(32) NOT NULL COMMENT 'dnspod / cloudflare / aliyun',
			`name` varchar(64) NOT NULL COMMENT '显示名',
			`api_id` varchar(128) NOT NULL COMMENT 'API ID / Token ID',
			`api_secret` varchar(255) NOT NULL COMMENT 'API Secret / Token',
			`extra` text NOT NULL COMMENT '其他配置 JSON',
			`qk` varchar(20) NOT NULL DEFAULT 'true' COMMENT '启用状态',
			`created_at` varchar(50) NOT NULL,
			PRIMARY KEY (`id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='DNS 服务商凭证'");
	}

	// 3. plg_dns_record 表（若不存在则创建）
	$tbl = $DB->get_row_prepare("SHOW TABLES LIKE 'plg_dns_record'");
	if (!$tbl) {
		@$DB->query("CREATE TABLE IF NOT EXISTS `plg_dns_record` (
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`user` varchar(64) NOT NULL COMMENT '所属用户（MN_zj.user）',
			`provider_id` int(11) NOT NULL COMMENT '关联 plg_dns_provider.id',
			`domain` varchar(128) NOT NULL COMMENT '主域名',
			`name` varchar(128) NOT NULL COMMENT '主机记录（如 www / @）',
			`type` varchar(20) NOT NULL COMMENT 'A / CNAME / TXT / MX / AAAA',
			`value` varchar(255) NOT NULL COMMENT '记录值',
			`ttl` int(11) NOT NULL DEFAULT 600,
			`remote_id` varchar(64) NOT NULL COMMENT '服务商返回的记录 ID',
			`auto` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否系统自动创建（1=是 0=用户手动）',
			`qk` varchar(20) NOT NULL DEFAULT 'true' COMMENT '启用状态',
			`created_at` varchar(50) NOT NULL,
			PRIMARY KEY (`id`),
			KEY `user` (`user`),
			KEY `domain` (`domain`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='DNS 解析记录'");
	}
}

/**
 * 添加域名商品
 * @param string $url 一级域名
 * @param string $btdh 所属宝塔节点代号
 * @param string $jg 价格（元，0=免费）
 * @param string $js 介绍
 * @param bool $qk 是否上架
 * @param string $channel 通道：pan / dnsapi
 * @param int $providerId DNS 服务商 ID（channel=dnsapi 时必填）
 * @return array ['ok'=>bool, 'msg'=>string]
 */
function domain_product_add($url, $btdh, $jg, $js, $qk = 'true', $channel = 'pan', $providerId = 0)
{
	global $DB, $date, $user;
	$url = trim($url);
	$btdh = trim($btdh);
	$jg = (string)$jg;
	$js = trim($js);
	$qk = $qk ? 'true' : 'false';
	$channel = ($channel === 'dnsapi') ? 'dnsapi' : 'pan';
	$providerId = (int)$providerId;

	if ($url === '' || $btdh === '' || $btdh === '-00-' || $js === '') {
		return ['ok' => false, 'msg' => '表单不能为空或未选择宝塔'];
	}
	if (!preg_match('/^[0-9a-zA-Z\.\-]{1,128}$/', $url)) {
		return ['ok' => false, 'msg' => '域名格式不合法'];
	}
	// DNS API 通道必须选服务商
	if ($channel === 'dnsapi' && $providerId <= 0) {
		return ['ok' => false, 'msg' => 'DNS API 通道必须选择 DNS 服务商'];
	}

	$exists = $DB->get_row_prepare("SELECT id FROM plg_domain_product WHERE url=? limit 1", [$url]);
	if ($exists) {
		return ['ok' => false, 'msg' => '该域名已存在'];
	}

	$ok = $DB->query_prepare(
		"INSERT INTO `plg_domain_product` (`url`, `btdh`, `jg`, `date`, `js`, `json`, `qk`, `channel`, `provider_id`) VALUES (?,?,?,?,?,?,?,?,?)",
		[$url, $btdh, $jg, $date, $js, '[]', $qk, $channel, $providerId]
	);
	if (!$ok) return ['ok' => false, 'msg' => '添加失败：' . $DB->error()];

	if (function_exists('logjl')) {
		logjl($user, '添加域名商品', '添加了 ' . $url . '（通道：' . $channel . '）', '添加成功', $DB);
	}
	return ['ok' => true, 'msg' => '添加成功'];
}

/**
 * 修改域名商品（可改介绍/价格/上架状态/通道/服务商）
 */
function domain_product_update($id, $js, $jg, $qk, $channel = null, $providerId = null)
{
	global $DB, $user;
	$id = (int)$id;
	$qk = $qk ? 'true' : 'false';

	$sets = ['`js`=?', '`jg`=?', '`qk`=?'];
	$params = [$js, $jg, $qk];

	if ($channel !== null) {
		$channel = ($channel === 'dnsapi') ? 'dnsapi' : 'pan';
		$sets[] = '`channel`=?';
		$params[] = $channel;
		// 切回 pan 通道时清零 provider_id
		if ($channel === 'pan') {
			$sets[] = '`provider_id`=0';
		}
	}
	if ($providerId !== null) {
		$providerId = (int)$providerId;
		$sets[] = '`provider_id`=?';
		$params[] = $providerId;
	}

	$params[] = $id;
	$ok = $DB->query_prepare(
		"update `plg_domain_product` set " . implode(',', $sets) . " where `id`=?",
		$params
	);
	if (!$ok) return ['ok' => false, 'msg' => '修改失败：' . $DB->error()];

	if (function_exists('logjl')) {
		logjl($user, '修改域名商品', '修改了 ID=' . $id, '修改成功', $DB);
	}
	return ['ok' => true, 'msg' => '修改成功'];
}

/**
 * 删除单个域名商品
 */
function domain_product_delete($id)
{
	global $DB, $user;
	$id = (int)$id;
	$ok = $DB->query_prepare("DELETE FROM plg_domain_product WHERE id=? limit 1", [$id]);
	if (!$ok) return ['ok' => false, 'msg' => '删除失败：' . $DB->error()];

	if (function_exists('logjl')) {
		logjl($user, '删除域名商品', '删除了 ID=' . $id, '删除成功', $DB);
	}
	return ['ok' => true, 'msg' => '删除成功'];
}

/**
 * 批量删除域名商品
 * @return array ['ok'=>int, 'fail'=>int]
 */
function domain_product_delete_batch(array $ids)
{
	global $DB, $user;
	$ok = 0; $fail = 0;
	foreach ($ids as $id) {
		$id = (int)$id;
		if ($DB->query_prepare("DELETE FROM plg_domain_product WHERE id=? limit 1", [$id])) {
			$ok++;
		} else {
			$fail++;
		}
	}
	if (function_exists('logjl')) {
		logjl($user, '批量删除域名商品', '删除了 ' . $ok . ' 条', '删除成功', $DB);
	}
	return ['ok' => $ok, 'fail' => $fail];
}

/**
 * 列表分页查询
 * @return array ['total'=>int, 'rows'=>array]
 */
function domain_product_list($page, $pagesize, $sort = 'id', $order = 'ASC')
{
	global $DB;
	$sort = preg_replace('/[^a-zA-Z0-9_]/', '', $sort) ?: 'id';
	$order = strtoupper($order) === 'DESC' ? 'DESC' : 'ASC';
	$page = max(1, (int)$page);
	$pagesize = max(1, (int)$pagesize);
	$offset = ($page - 1) * $pagesize;

	$total = (int)$DB->count_prepare("SELECT count(*) from plg_domain_product WHERE 1");
	$rows = $DB->get_all_prepare("SELECT * FROM plg_domain_product order by $sort $order limit $offset,$pagesize") ?: [];
	return ['total' => $total, 'rows' => $rows];
}

/**
 * 根据 URL 取域名商品（用于支付结算时查商品）
 */
function domain_product_get_by_url($url)
{
	global $DB;
	return $DB->get_row_prepare("SELECT * FROM plg_domain_product WHERE url=? limit 1", [$url]);
}

/**
 * 根据宝塔节点取上架域名（用户端下拉用）
 */
function domain_product_list_by_node($btdh)
{
	global $DB;
	return $DB->get_all_prepare("SELECT * FROM plg_domain_product WHERE btdh=? and qk='true' order by id desc limit 9999", [$btdh]) ?: [];
}

/**
 * 记录用户购买（写入 json 字段）
 */
function domain_product_add_buyer($productId, $user)
{
	global $DB;
	$row = $DB->get_row_prepare("SELECT * FROM plg_domain_product WHERE id=? limit 1", [$productId]);
	if (!$row) return false;
	$buyers = json_decode($row['json'], true);
	if (!is_array($buyers)) $buyers = [];
	if (!in_array($user, $buyers)) $buyers[] = $user;
	return $DB->query_prepare("update `plg_domain_product` set `json`=? where `id`=?", [json_encode($buyers, 256), $productId]);
}

/**
 * 根据 URL 更新购买者列表（ymgm 结算用）
 */
function domain_product_add_buyer_by_url($url, $user)
{
	global $DB;
	$row = $DB->get_row_prepare("SELECT * FROM plg_domain_product WHERE url=? limit 1", [$url]);
	if (!$row) return false;
	$buyers = json_decode($row['json'], true);
	if (!is_array($buyers)) $buyers = [];
	if (!in_array($user, $buyers)) $buyers[] = $user;
	return $DB->query_prepare("update `plg_domain_product` set `json`=? where `url`=?", [json_encode($buyers, 256), $url]);
}
