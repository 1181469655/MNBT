-- mobile_bridge：APP 设备令牌表 + 一次性配对码表
-- 引擎与字符集沿用 MNBT 现有约定（MyISAM / utf8）

CREATE TABLE IF NOT EXISTS `MN_plugin_app_device` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `zj_id` int(11) NOT NULL,                 -- 绑定的 MN_zj.id
  `token_hash` char(64) NOT NULL,           -- sha256(token) hex，明文只下发一次
  `device_id` varchar(64) NOT NULL DEFAULT '',
  `device_name` varchar(120) NOT NULL DEFAULT '',
  `device_model` varchar(120) NOT NULL DEFAULT '',
  `app_version` varchar(20) NOT NULL DEFAULT '',
  `android_version` varchar(20) NOT NULL DEFAULT '',
  `created_at` varchar(50) NOT NULL,
  `last_seen_at` varchar(50) NOT NULL DEFAULT '',
  `last_ip` varchar(45) NOT NULL DEFAULT '',
  `status` tinyint(1) NOT NULL DEFAULT 1,   -- 1 启用 / 0 已吊销
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_token` (`token_hash`),
  KEY `idx_zj` (`zj_id`),
  KEY `idx_device` (`zj_id`, `device_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `MN_plugin_app_pair` (
  `code` char(8) NOT NULL,
  `zj_id` int(11) NOT NULL,
  `created_at` int(11) NOT NULL,
  `expires_at` int(11) NOT NULL,
  `used` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`code`),
  KEY `idx_zj` (`zj_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
