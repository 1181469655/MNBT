CREATE TABLE IF NOT EXISTS `MN_plugin_respool` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(120) NOT NULL DEFAULT '' COMMENT '资源池名',
  `username` varchar(120) NOT NULL DEFAULT '' COMMENT '资源池用户名',
  `password` varchar(255) NOT NULL DEFAULT '' COMMENT '资源池密码',
  `nodes` text COMMENT '可用节点（MN_bt.btdh 的 JSON 数组，空数组=不限）',
  `host_users` text COMMENT '归属本池的主机账号（MN_zj.user 的 JSON 数组）',
  `web_space` int(11) NOT NULL DEFAULT '0' COMMENT '网页空间总配额 MB（0=不限）',
  `sql_space` int(11) NOT NULL DEFAULT '0' COMMENT '数据库空间总配额 MB（0=不限）',
  `flow` int(11) NOT NULL DEFAULT '0' COMMENT '流量总配额 GB/月（0=不限）',
  `expire_date` varchar(50) NOT NULL DEFAULT '' COMMENT '到期日期 yyyy-mm-dd（空=永不到期）',
  `status` varchar(20) NOT NULL DEFAULT 'enabled' COMMENT '资源池状态 enabled/disabled',
  `remark` varchar(500) NOT NULL DEFAULT '' COMMENT '备注',
  `created_at` varchar(50) NOT NULL DEFAULT '',
  `updated_at` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_respool_username` (`username`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
