-- MNBT 数据库修复脚本
-- 使用 CREATE TABLE IF NOT EXISTS 补齐缺失的表，不覆盖已有数据

CREATE TABLE IF NOT EXISTS `MN_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `czuser` varchar(250) NOT NULL,
  `date` varchar(250) NOT NULL,
  `lx` varchar(250) NOT NULL,
  `lr` varchar(50) NOT NULL,
  `ip` text NOT NULL,
  `qk` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `MN_bt` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `btip` varchar(250) NOT NULL,
  `btdk` varchar(250) NOT NULL,
  `btmy` varchar(250) NOT NULL,
  `date` varchar(50) NOT NULL,
  `ktmy` text NOT NULL,
  `qmk` text NOT NULL,
  `btdh` varchar(250) NOT NULL,
  `btos` INT(10) NOT NULL DEFAULT '1',
  `als` varchar(200) NOT NULL,
  `ftpdz` varchar(50) NOT NULL,
  `ptl` varchar(50) NOT NULL,
  `qk` varchar(50) NOT NULL,
  `mrbts_php` varchar(10) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `MN_zj` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ssbt` varchar(250) NOT NULL,
  `user` varchar(250) NOT NULL,
  `pass` varchar(50) NOT NULL,
  `sqluser` text NOT NULL,
  `sqlpass` text NOT NULL,
  `sqldz` varchar(50) NOT NULL,
  `data` varchar(50) NOT NULL,
  `datae` varchar(50) NOT NULL,
  `qk` varchar(50) NOT NULL,
  `btid` varchar(50) NOT NULL,
  `ftpid` varchar(50) NOT NULL,
  `ymbds` varchar(50) NOT NULL,
  `hxa` varchar(50) NOT NULL,
  `hxb` varchar(50) NOT NULL,
  `hxc` varchar(50) NOT NULL,
  `hxd` varchar(50) NOT NULL,
  `llmax` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `MN_bs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(250) NOT NULL,
  `jc` varchar(250) NOT NULL,
  `src` text NOT NULL,
  `date` text NOT NULL,
  `cxwz` text NOT NULL,
  `sxpz` varchar(500) NOT NULL,
  `tj` text NOT NULL,
  `jg` varchar(50) NOT NULL,
  `inp` text NOT NULL,
  `pz` text NOT NULL,
  `alet` text NOT NULL,
  `qk` varchar(50) NOT NULL,
  `hxa` varchar(50) NOT NULL,
  `hxb` varchar(50) NOT NULL,
  `hxc` varchar(50) NOT NULL,
  `hxd` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `MN_ym` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `url` varchar(128) NOT NULL,
  `btdh` varchar(250) NOT NULL,
  `jg` varchar(250) NOT NULL,
  `date` varchar(50) NOT NULL,
  `js` varchar(50) NOT NULL,
  `json` text NOT NULL,
  `qk` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `MN_dd` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cs` varchar(1000) NOT NULL,
  `date` varchar(250) NOT NULL,
  `zffs` varchar(250) NOT NULL,
  `je` varchar(250) NOT NULL,
  `ddh` varchar(250) NOT NULL,
  `lx` varchar(250) NOT NULL,
  `qk` varchar(50) NOT NULL,
  `ip` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `MN_monitor_task` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user` varchar(250) NOT NULL,
  `name` varchar(250) NOT NULL,
  `task_type` varchar(30) NOT NULL DEFAULT 'url',
  `url` varchar(1000) NOT NULL,
  `resource_type` varchar(30) NOT NULL DEFAULT '',
  `resource_threshold` int(11) NOT NULL DEFAULT 80,
  `method` varchar(10) NOT NULL DEFAULT 'GET',
  `interval_seconds` int(11) NOT NULL DEFAULT 60,
  `timeout_seconds` int(11) NOT NULL DEFAULT 10,
  `status_rule` varchar(30) NOT NULL DEFAULT 'eq',
  `status_value` varchar(100) NOT NULL DEFAULT '200',
  `content_rule` varchar(30) NOT NULL DEFAULT 'none',
  `content_value` text,
  `fail_threshold` int(11) NOT NULL DEFAULT 1,
  `notify_email` varchar(10) NOT NULL DEFAULT 'true',
  `enabled` varchar(10) NOT NULL DEFAULT 'true',
  `last_run` varchar(50) DEFAULT NULL,
  `next_run` varchar(50) DEFAULT NULL,
  `last_status` varchar(20) DEFAULT NULL,
  `last_code` int(11) DEFAULT NULL,
  `last_error` text,
  `fail_count` int(11) NOT NULL DEFAULT 0,
  `created_at` varchar(50) NOT NULL,
  `updated_at` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user`),
  KEY `idx_next_run` (`enabled`,`next_run`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `MN_monitor_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `task_id` int(11) NOT NULL,
  `user` varchar(250) NOT NULL,
  `url` varchar(1000) NOT NULL,
  `http_code` int(11) DEFAULT NULL,
  `response_time` int(11) DEFAULT NULL,
  `check_status` varchar(20) NOT NULL,
  `error_message` text,
  `response_excerpt` text,
  `notified` varchar(10) NOT NULL DEFAULT 'false',
  `created_at` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_task` (`task_id`),
  KEY `idx_user` (`user`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `MN_notice_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user` varchar(250) NOT NULL,
  `type` varchar(50) NOT NULL,
  `title` varchar(250) NOT NULL,
  `content` text NOT NULL,
  `level` varchar(20) NOT NULL DEFAULT 'info',
  `is_read` varchar(10) NOT NULL DEFAULT 'false',
  `created_at` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_user_read` (`user`,`is_read`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `MN_node` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bt_id` int(11) NOT NULL DEFAULT 0,
  `node_id` varchar(64) NOT NULL,
  `node_name` varchar(100) NOT NULL DEFAULT '',
  `node_secret` varchar(128) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'offline',
  `enabled` varchar(10) NOT NULL DEFAULT 'true',
  `ip` varchar(64) NOT NULL DEFAULT '',
  `version` varchar(30) NOT NULL DEFAULT '',
  `capabilities` text,
  `last_heartbeat` varchar(50) DEFAULT NULL,
  `created_at` varchar(50) NOT NULL,
  `updated_at` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_node_id` (`node_id`),
  KEY `idx_bt_id` (`bt_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `MN_node_task` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `task_id` varchar(64) NOT NULL,
  `node_id` varchar(64) NOT NULL,
  `action` varchar(50) NOT NULL,
  `payload` mediumtext,
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `result` mediumtext,
  `error` text,
  `created_at` varchar(50) NOT NULL,
  `pulled_at` varchar(50) DEFAULT NULL,
  `finished_at` varchar(50) DEFAULT NULL,
  `updated_at` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_task_id` (`task_id`),
  KEY `idx_node_status` (`node_id`,`status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `MN_node_nonce` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `node_id` varchar(64) NOT NULL,
  `nonce` varchar(80) NOT NULL,
  `created_at` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_node_nonce` (`node_id`,`nonce`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `MN_forbidden_scan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `task_id` varchar(64) NOT NULL,
  `node_id` varchar(64) NOT NULL,
  `site` varchar(250) NOT NULL DEFAULT '',
  `status` varchar(20) NOT NULL DEFAULT 'success',
  `scanned_files` int(11) NOT NULL DEFAULT 0,
  `scanned_rows` int(11) NOT NULL DEFAULT 0,
  `matches_count` int(11) NOT NULL DEFAULT 0,
  `summary` mediumtext,
  `created_at` varchar(50) NOT NULL,
  `updated_at` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_task` (`task_id`),
  KEY `idx_node` (`node_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `MN_forbidden_match` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `task_id` varchar(64) NOT NULL,
  `node_id` varchar(64) NOT NULL,
  `site` varchar(250) NOT NULL DEFAULT '',
  `match_type` varchar(30) NOT NULL DEFAULT 'file',
  `target` varchar(1000) NOT NULL DEFAULT '',
  `line_no` int(11) NOT NULL DEFAULT 0,
  `keyword` varchar(250) NOT NULL DEFAULT '',
  `excerpt` text,
  `created_at` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_task` (`task_id`),
  KEY `idx_node` (`node_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

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
