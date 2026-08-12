-- 魔方财务代理分销插件 - 建表
-- v1.1 多供应商：新增供应商表，product/order/host/log 增加 supplier_id

CREATE TABLE IF NOT EXISTS `MN_plugin_zjmf_supplier` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL DEFAULT '' COMMENT '供应商名称',
  `api_url` varchar(255) NOT NULL DEFAULT '' COMMENT '上游站点根地址',
  `api_username` varchar(64) NOT NULL DEFAULT '' COMMENT 'API 用户名',
  `api_password` varchar(255) NOT NULL DEFAULT '' COMMENT 'API 密钥(仅保存时写入,不回显)',
  `api_timeout` int(11) NOT NULL DEFAULT '30' COMMENT '请求超时(秒)',
  `markup_type` tinyint(4) NOT NULL DEFAULT '0' COMMENT '加价方式：0=比例 1=固定(分)',
  `markup_value` bigint(20) NOT NULL DEFAULT '0' COMMENT '加价比例(千分比)或固定加价(分)',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '1=启用 0=停用',
  `sort` int(11) NOT NULL DEFAULT '50',
  `remark` varchar(255) NOT NULL DEFAULT '',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `status` (`status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `MN_plugin_zjmf_product` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `supplier_id` int(11) NOT NULL DEFAULT '0' COMMENT '所属供应商ID',
  `up_product_id` int(11) NOT NULL DEFAULT '0' COMMENT '上游商品ID',
  `name` varchar(100) NOT NULL DEFAULT '',
  `description` text,
  `currency` varchar(10) NOT NULL DEFAULT '',
  `agent_price_cents` bigint(20) NOT NULL DEFAULT '0' COMMENT '上游代理价（分）',
  `markup_type` tinyint(4) NOT NULL DEFAULT '0' COMMENT '加价方式：0=比例 1=固定(分)',
  `markup_value` bigint(20) NOT NULL DEFAULT '0' COMMENT '加价比例(千分比)或固定加价(分)',
  `cycles` text COMMENT '周期JSON：[{cycle,name,price_cents}]',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '1=上架 0=下架',
  `sort` int(11) NOT NULL DEFAULT '50',
  `synced_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_supplier_up` (`supplier_id`,`up_product_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `MN_plugin_zjmf_order` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_no` varchar(64) NOT NULL DEFAULT '',
  `action` varchar(20) NOT NULL DEFAULT 'buy' COMMENT 'buy/upgrade_config/upgrade_product',
  `supplier_id` int(11) NOT NULL DEFAULT '0' COMMENT '所属供应商ID(开通路由依据)',
  `user_id` int(11) NOT NULL DEFAULT '0',
  `product_id` int(11) NOT NULL DEFAULT '0',
  `up_product_id` int(11) NOT NULL DEFAULT '0',
  `product_name` varchar(100) NOT NULL DEFAULT '',
  `cycle` varchar(20) NOT NULL DEFAULT '',
  `cycle_name` varchar(20) NOT NULL DEFAULT '',
  `amount_cents` bigint(20) NOT NULL DEFAULT '0',
  `cost_cents` bigint(20) NOT NULL DEFAULT '0',
  `order_params` text,
  `up_order_id` int(11) NOT NULL DEFAULT '0',
  `up_host_id` int(11) NOT NULL DEFAULT '0',
  `host_id` int(11) NOT NULL DEFAULT '0' COMMENT '本地主机映射ID',
  `username` varchar(64) NOT NULL DEFAULT '',
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `pay_time` datetime DEFAULT NULL,
  `opened_at` datetime DEFAULT NULL,
  `remark` varchar(255) NOT NULL DEFAULT '',
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_no` (`order_no`),
  KEY `user_id` (`user_id`),
  KEY `supplier_id` (`supplier_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `MN_plugin_zjmf_host` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `supplier_id` int(11) NOT NULL DEFAULT '0' COMMENT '所属供应商ID(操作/升级路由依据)',
  `user_id` int(11) NOT NULL DEFAULT '0',
  `order_id` int(11) NOT NULL DEFAULT '0',
  `up_host_id` int(11) NOT NULL DEFAULT '0',
  `up_product_id` int(11) NOT NULL DEFAULT '0',
  `name` varchar(100) NOT NULL DEFAULT '',
  `username` varchar(64) NOT NULL DEFAULT '',
  `password` varchar(255) NOT NULL DEFAULT '' COMMENT 'authcode加密存储',
  `cycle` varchar(20) NOT NULL DEFAULT '',
  `status` varchar(20) NOT NULL DEFAULT 'unknown',
  `renew_date` varchar(20) NOT NULL DEFAULT '',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `supplier_id` (`supplier_id`),
  KEY `up_host_id` (`up_host_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `MN_plugin_zjmf_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `supplier_id` int(11) NOT NULL DEFAULT '0' COMMENT '供应商ID',
  `order_no` varchar(64) NOT NULL DEFAULT '',
  `action` varchar(50) NOT NULL DEFAULT '',
  `result` varchar(20) NOT NULL DEFAULT 'success',
  `content` text,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_no` (`order_no`),
  KEY `supplier_id` (`supplier_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
