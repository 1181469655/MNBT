-- docker_shop 插件安装 SQL
-- 售卖套餐表：管理员配置的 Docker 售卖套餐（绑定配额套餐 + 固定节点 + 周期价格）
DROP TABLE IF EXISTS `MN_plugin_docker_plan`;
CREATE TABLE `MN_plugin_docker_plan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(120) NOT NULL COMMENT '售卖套餐名称',
  `description` text NOT NULL COMMENT '售卖介绍',
  `category` varchar(50) NOT NULL DEFAULT '' COMMENT '分类',
  `node` int(11) NOT NULL DEFAULT '0' COMMENT '固定开通节点 MN_docker_node.id',
  `base_plan_id` int(11) NOT NULL DEFAULT '0' COMMENT '绑定配额套餐 MN_docker_plan.id',
  `price_month_cents` int(11) NOT NULL DEFAULT '0' COMMENT '月付价格（分）',
  `price_quarter_cents` int(11) NOT NULL DEFAULT '0' COMMENT '季付价格（分）',
  `price_half_year_cents` int(11) NOT NULL DEFAULT '0' COMMENT '半年付价格（分）',
  `price_year_cents` int(11) NOT NULL DEFAULT '0' COMMENT '年付价格（分）',
  `price_two_year_cents` int(11) NOT NULL DEFAULT '0' COMMENT '两年付价格（分）',
  `price_three_year_cents` int(11) NOT NULL DEFAULT '0' COMMENT '三年付价格（分）',
  `enabled_periods` varchar(255) NOT NULL DEFAULT '' COMMENT '允许周期，逗号分隔',
  `status` varchar(20) NOT NULL DEFAULT 'active' COMMENT 'active/inactive',
  `sort` int(11) NOT NULL DEFAULT '50' COMMENT '排序（小到大）',
  `created_at` varchar(50) NOT NULL DEFAULT '',
  `updated_at` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `idx_base_plan` (`base_plan_id`),
  KEY `idx_node` (`node`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- 订单表：用户的 Docker 购买订单（关联 MN_dd.ddh）
DROP TABLE IF EXISTS `MN_plugin_docker_order`;
CREATE TABLE `MN_plugin_docker_order` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL COMMENT '购买者 user_info 用户 ID',
  `plan_id` int(11) NOT NULL COMMENT '售卖套餐 ID',
  `plan_name` varchar(120) NOT NULL DEFAULT '' COMMENT '下单时套餐名称快照',
  `period` varchar(10) NOT NULL DEFAULT 'month' COMMENT '购买周期',
  `amount_cents` int(11) NOT NULL DEFAULT '0' COMMENT '订单金额（分）',
  `order_no` varchar(64) NOT NULL DEFAULT '' COMMENT '关联 MN_dd.ddh',
  `node` int(11) NOT NULL DEFAULT '0' COMMENT '开通节点 MN_docker_node.id',
  `docker_user_id` int(11) NOT NULL DEFAULT '0' COMMENT '开通后回填 MN_docker_user.id',
  `status` varchar(20) NOT NULL DEFAULT 'pending' COMMENT 'pending/paid/opened/failed/cancelled',
  `remark` text NOT NULL COMMENT '备注/失败原因',
  `created_at` varchar(50) NOT NULL DEFAULT '',
  `paid_at` varchar(50) NOT NULL DEFAULT '',
  `opened_at` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_order_no` (`order_no`),
  KEY `idx_status` (`status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- 资产表：用户的 Docker 账号资产
DROP TABLE IF EXISTS `MN_plugin_docker_asset`;
CREATE TABLE `MN_plugin_docker_asset` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL COMMENT '所属 user_info 用户 ID',
  `order_id` int(11) NOT NULL DEFAULT '0' COMMENT '开通订单 ID',
  `docker_user_id` int(11) NOT NULL DEFAULT '0' COMMENT 'MN_docker_user.id',
  `plan_id` int(11) NOT NULL DEFAULT '0' COMMENT '售卖套餐 ID',
  `plan_name` varchar(120) NOT NULL DEFAULT '' COMMENT '套餐名称快照',
  `docker_username` varchar(64) NOT NULL DEFAULT '' COMMENT 'Docker 控制台登录名',
  `docker_password` varchar(64) NOT NULL DEFAULT '' COMMENT 'Docker 控制台登录密码',
  `expire_at` varchar(50) NOT NULL DEFAULT '' COMMENT '到期时间（0000-00-00=永久）',
  `status` varchar(20) NOT NULL DEFAULT 'active' COMMENT 'active/expired/cancelled',
  `created_at` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_docker_user` (`docker_user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
