-- qmzl_domain 插件数据表
-- 用户维度 = user_info 插件用户（MN_plugin_user.id / username）
-- 模式：client（客户自注册上游账号）/ agent（代理商模式，管理员代注册）
-- 1) 用户绑定的启明智联账号（仅 client 模式使用，含加密密码与 JWT）
CREATE TABLE IF NOT EXISTS `plg_qmzl_account` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT 0 COMMENT 'user_info 用户 ID',
  `username` varchar(64) NOT NULL DEFAULT '' COMMENT 'user_info 用户名',
  `account` varchar(255) NOT NULL DEFAULT '' COMMENT '启明智联账号（手机/邮箱）',
  `password` text COMMENT '启明智联密码（authcode 加密）',
  `jwt` text COMMENT '登录 token',
  `jwt_expire` varchar(20) NOT NULL DEFAULT '0' COMMENT 'token 过期时间戳',
  `status` varchar(20) NOT NULL DEFAULT 'ok' COMMENT 'ok / error',
  `last_msg` varchar(255) NOT NULL DEFAULT '' COMMENT '最近一次错误信息',
  `created_at` varchar(50) NOT NULL DEFAULT '',
  `updated_at` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='启明智联域名-用户账号';

-- 2) 本地订单记录（下单即记，支付状态可轮询）
CREATE TABLE IF NOT EXISTS `plg_qmzl_order` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT 0 COMMENT 'user_info 用户 ID',
  `username` varchar(64) NOT NULL DEFAULT '' COMMENT 'user_info 用户名',
  `ddh` varchar(64) NOT NULL DEFAULT '' COMMENT 'MNBT 订单号（agent 模式）',
  `domain` varchar(255) NOT NULL DEFAULT '' COMMENT '完整域名',
  `year` int(11) NOT NULL DEFAULT 1 COMMENT '购买年限',
  `amount` varchar(20) NOT NULL DEFAULT '0' COMMENT '金额（元）',
  `template_id` int(11) NOT NULL DEFAULT 0 COMMENT '信息模板 ID',
  `cloud_order_id` varchar(64) NOT NULL DEFAULT '' COMMENT '上游订单 ID',
  `gateway` varchar(64) NOT NULL DEFAULT '' COMMENT '支付网关标识',
  `status` varchar(20) NOT NULL DEFAULT 'Pending' COMMENT 'Pending/Paid/Cancelled/Failed',
  `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '状态备注',
  `created_at` varchar(50) NOT NULL DEFAULT '',
  `updated_at` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_ddh` (`ddh`),
  KEY `idx_order` (`cloud_order_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='启明智联域名-订单记录';

-- 3) 模板归属记录（agent 模式：客户创建的模板记录归属，用于隔离查看）
CREATE TABLE IF NOT EXISTS `plg_qmzl_template` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `template_id` int(11) NOT NULL DEFAULT 0 COMMENT '上游模板 ID',
  `user_id` int(11) NOT NULL DEFAULT 0 COMMENT 'user_info 用户 ID',
  `username` varchar(64) NOT NULL DEFAULT '' COMMENT 'user_info 用户名',
  `created_at` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_tpl` (`template_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='启明智联域名-模板归属';
