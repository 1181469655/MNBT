-- hosting_shop 插件安装 SQL
-- 套餐表：管理员配置的虚拟主机套餐与价格
DROP TABLE IF EXISTS `MN_plugin_hosting_plan`;
CREATE TABLE `MN_plugin_hosting_plan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(120) NOT NULL COMMENT '套餐名称',
  `description` text NOT NULL COMMENT '套餐介绍',
  `spec_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '产品类型 0=主机 1=CDN',
  `spec_web` int(11) NOT NULL DEFAULT '1024' COMMENT '网页空间 MB',
  `spec_sql` int(11) NOT NULL DEFAULT '256' COMMENT '数据库空间 MB',
  `spec_flow` int(11) NOT NULL DEFAULT '0' COMMENT '流量 GB（0=不限）',
  `spec_domain` int(11) NOT NULL DEFAULT '5' COMMENT '域名最大绑定数',
  `price_month_cents` int(11) NOT NULL DEFAULT '0' COMMENT '月付价格（分）',
  `price_year_cents` int(11) NOT NULL DEFAULT '0' COMMENT '年付价格（分）',
  `status` varchar(20) NOT NULL DEFAULT 'active' COMMENT 'active/inactive',
  `sort` int(11) NOT NULL DEFAULT '50' COMMENT '排序（小到大）',
  `created_at` varchar(50) NOT NULL DEFAULT '',
  `updated_at` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- 订单表：用户的主机购买订单（关联 MN_dd.ddh）
DROP TABLE IF EXISTS `MN_plugin_hosting_order`;
CREATE TABLE `MN_plugin_hosting_order` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL COMMENT '购买者 user_info 用户 ID',
  `plan_id` int(11) NOT NULL COMMENT '套餐 ID',
  `plan_name` varchar(120) NOT NULL DEFAULT '' COMMENT '下单时套餐名称快照',
  `period` varchar(10) NOT NULL DEFAULT 'month' COMMENT 'month/year',
  `amount_cents` int(11) NOT NULL DEFAULT '0' COMMENT '订单金额（分）',
  `order_no` varchar(64) NOT NULL DEFAULT '' COMMENT '关联 MN_dd.ddh',
  `host_id` int(11) NOT NULL DEFAULT '0' COMMENT '开通后回填 MN_zj.id',
  `node` varchar(250) NOT NULL DEFAULT '' COMMENT '开通节点 MN_bt.btdh',
  `status` varchar(20) NOT NULL DEFAULT 'pending' COMMENT 'pending/paid/opened/failed/cancelled',
  `remark` text NOT NULL COMMENT '备注/失败原因',
  `created_at` varchar(50) NOT NULL DEFAULT '',
  `paid_at` varchar(50) NOT NULL DEFAULT '',
  `opened_at` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_order_no` (`order_no`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- 资产表：用户已开通的主机（关联 MN_zj.id）
DROP TABLE IF EXISTS `MN_plugin_hosting_asset`;
CREATE TABLE `MN_plugin_hosting_asset` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL COMMENT '所属 user_info 用户 ID',
  `order_id` int(11) NOT NULL DEFAULT '0' COMMENT '开通订单 ID',
  `host_id` int(11) NOT NULL DEFAULT '0' COMMENT 'MN_zj.id',
  `plan_id` int(11) NOT NULL DEFAULT '0' COMMENT '套餐 ID',
  `plan_name` varchar(120) NOT NULL DEFAULT '' COMMENT '套餐名称快照',
  `expire_at` varchar(50) NOT NULL DEFAULT '' COMMENT '到期时间',
  `status` varchar(20) NOT NULL DEFAULT 'active' COMMENT 'active/expired/cancelled',
  `created_at` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_host` (`host_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
