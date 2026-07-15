-- MNBT V1.81 插件系统（已有站点升级执行一次）
CREATE TABLE IF NOT EXISTS `MN_plugin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `slug` varchar(64) NOT NULL,
  `name` varchar(120) NOT NULL DEFAULT '',
  `version` varchar(32) NOT NULL DEFAULT '',
  `enabled` varchar(10) NOT NULL DEFAULT 'false',
  `config_json` mediumtext,
  `installed_at` varchar(50) NOT NULL DEFAULT '',
  `updated_at` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_slug` (`slug`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `MN_plugin_option` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `plugin_slug` varchar(64) NOT NULL,
  `k` varchar(120) NOT NULL,
  `v` mediumtext,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_plugin_k` (`plugin_slug`,`k`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
