-- balance 插件：用户余额表 + 流水表
-- 余额以「分」为单位整数存储，避免浮点误差

DROP TABLE IF EXISTS `MN_plugin_balance`;
CREATE TABLE `MN_plugin_balance` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,                        -- MN_plugin_user.id
  `balance` int(11) NOT NULL DEFAULT 0,             -- 余额（分）
  `updated_at` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_user` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `MN_plugin_balance_log`;
CREATE TABLE `MN_plugin_balance_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,                        -- MN_plugin_user.id
  `amount` int(11) NOT NULL,                         -- 变动金额（分，正=收入，负=支出）
  `balance_after` int(11) NOT NULL,                  -- 变动后余额（分）
  `type` varchar(20) NOT NULL,                       -- recharge / consume / refund / adjust
  `order_no` varchar(64) NOT NULL DEFAULT '',        -- 关联订单号（充值时填）
  `remark` varchar(255) NOT NULL DEFAULT '',         -- 备注
  `created_at` varchar(50) NOT NULL,                 -- 时间
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
