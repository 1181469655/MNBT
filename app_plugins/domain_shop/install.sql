-- domain_shop 插件安装脚本
-- 3 张表：域名商品 / DNS 服务商凭证 / DNS 记录

-- 域名商品（取代核心 MN_ym 表，结构兼容便于数据迁移）
CREATE TABLE IF NOT EXISTS `plg_domain_product` (
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='域名商品表';

-- DNS 服务商凭证（管理员配置，共享给所有用户）
CREATE TABLE IF NOT EXISTS `plg_dns_provider` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `slug` varchar(32) NOT NULL COMMENT 'dnspod / cloudflare / aliyun',
  `name` varchar(64) NOT NULL COMMENT '显示名',
  `api_id` varchar(128) NOT NULL COMMENT 'API ID / Token ID',
  `api_secret` varchar(255) NOT NULL COMMENT 'API Secret / Token',
  `extra` text NOT NULL COMMENT '其他配置 JSON',
  `qk` varchar(20) NOT NULL DEFAULT 'true' COMMENT '启用状态',
  `created_at` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='DNS 服务商凭证';

-- DNS 记录（用户创建的解析记录）
CREATE TABLE IF NOT EXISTS `plg_dns_record` (
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='DNS 解析记录';
