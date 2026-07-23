-- MNBT V1.79 → V1.81 升级迁移脚本
-- 保留所有已有数据，仅添加 V1.81 新增的表和字段

-- V1.81: MN_bt 新增 mrbts_php 字段（节点默认 PHP 版本）
ALTER TABLE `MN_bt` ADD COLUMN `mrbts_php` varchar(10) NOT NULL DEFAULT '' COMMENT '节点默认 PHP 版本';

-- V1.81 P3: MN_config 新增 pay_methods 字段（已启用的付款方式配置 JSON）
ALTER TABLE `MN_config` ADD COLUMN `pay_methods` text NOT NULL DEFAULT '' COMMENT '已启用的付款方式配置（JSON）';

-- V1.81: 插件系统
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
