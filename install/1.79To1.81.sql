-- MNBT V1.79 → V1.81 升级迁移脚本
-- 保留所有已有数据，仅添加 V1.81 新增的表和字段

-- V1.81: MN_bt 新增 mrbts_php 字段（节点默认 PHP 版本）
ALTER TABLE `MN_bt`
    ADD COLUMN `mrbts_php` varchar(10) NOT NULL DEFAULT '' COMMENT '节点默认 PHP 版本';

-- V1.81 P3: MN_config 新增 pay_methods 字段（已启用的付款方式配置 JSON）
ALTER TABLE `MN_config`
    ADD COLUMN `pay_methods` text NOT NULL COMMENT '已启用的付款方式配置（JSON）';


DROP TABLE IF EXISTS `MN_monitor_task`; -- 用户端URL监控任务
CREATE TABLE `MN_monitor_task`
(
    `id`                 int(11)       NOT NULL AUTO_INCREMENT,
    `user`               varchar(250)  NOT NULL,
    `name`               varchar(250)  NOT NULL,
    `task_type`          varchar(30)   NOT NULL DEFAULT 'url',
    `url`                varchar(1000) NOT NULL,
    `resource_type`      varchar(30)   NOT NULL DEFAULT '',
    `resource_threshold` int(11)       NOT NULL DEFAULT 80,
    `method`             varchar(10)   NOT NULL DEFAULT 'GET',
    `interval_seconds`   int(11)       NOT NULL DEFAULT 60,
    `timeout_seconds`    int(11)       NOT NULL DEFAULT 10,
    `status_rule`        varchar(30)   NOT NULL DEFAULT 'eq',
    `status_value`       varchar(100)  NOT NULL DEFAULT '200',
    `content_rule`       varchar(30)   NOT NULL DEFAULT 'none',
    `content_value`      text,
    `fail_threshold`     int(11)       NOT NULL DEFAULT 1,
    `notify_email`       varchar(10)   NOT NULL DEFAULT 'true',
    `enabled`            varchar(10)   NOT NULL DEFAULT 'true',
    `last_run`           varchar(50)            DEFAULT NULL,
    `next_run`           varchar(50)            DEFAULT NULL,
    `last_status`        varchar(20)            DEFAULT NULL,
    `last_code`          int(11)                DEFAULT NULL,
    `last_error`         text,
    `fail_count`         int(11)       NOT NULL DEFAULT 0,
    `created_at`         varchar(50)   NOT NULL,
    `updated_at`         varchar(50)   NOT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_user` (`user`),
    KEY `idx_next_run` (`enabled`, `next_run`)
) ENGINE = MyISAM
  DEFAULT CHARSET = utf8;

DROP TABLE IF EXISTS `MN_monitor_log`; -- 用户端URL监控检测日志
CREATE TABLE `MN_monitor_log`
(
    `id`               int(11)       NOT NULL AUTO_INCREMENT,
    `task_id`          int(11)       NOT NULL,
    `user`             varchar(250)  NOT NULL,
    `url`              varchar(1000) NOT NULL,
    `http_code`        int(11)                DEFAULT NULL,
    `response_time`    int(11)                DEFAULT NULL,
    `check_status`     varchar(20)   NOT NULL,
    `error_message`    text,
    `response_excerpt` text,
    `notified`         varchar(10)   NOT NULL DEFAULT 'false',
    `created_at`       varchar(50)   NOT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_task` (`task_id`),
    KEY `idx_user` (`user`)
) ENGINE = MyISAM
  DEFAULT CHARSET = utf8;

DROP TABLE IF EXISTS `MN_notice_log`; -- 用户端通知日志
CREATE TABLE `MN_notice_log`
(
    `id`         int(11)      NOT NULL AUTO_INCREMENT,
    `user`       varchar(250) NOT NULL,
    `type`       varchar(50)  NOT NULL,
    `title`      varchar(250) NOT NULL,
    `content`    text         NOT NULL,
    `level`      varchar(20)  NOT NULL DEFAULT 'info',
    `is_read`    varchar(10)  NOT NULL DEFAULT 'false',
    `created_at` varchar(50)  NOT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_user_read` (`user`, `is_read`)
) ENGINE = MyISAM
  DEFAULT CHARSET = utf8;

DROP TABLE IF EXISTS `MN_node`; -- MNBT节点插件
CREATE TABLE `MN_node`
(
    `id`             int(11)      NOT NULL AUTO_INCREMENT,
    `bt_id`          int(11)      NOT NULL DEFAULT 0,
    `node_id`        varchar(64)  NOT NULL,
    `node_name`      varchar(100) NOT NULL DEFAULT '',
    `node_secret`    varchar(128) NOT NULL,
    `status`         varchar(20)  NOT NULL DEFAULT 'offline',
    `enabled`        varchar(10)  NOT NULL DEFAULT 'true',
    `ip`             varchar(64)  NOT NULL DEFAULT '',
    `version`        varchar(30)  NOT NULL DEFAULT '',
    `capabilities`   text,
    `last_heartbeat` varchar(50)           DEFAULT NULL,
    `created_at`     varchar(50)  NOT NULL,
    `updated_at`     varchar(50)  NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_node_id` (`node_id`),
    KEY `idx_bt_id` (`bt_id`)
) ENGINE = MyISAM
  DEFAULT CHARSET = utf8;

DROP TABLE IF EXISTS `MN_node_task`; -- MNBT节点任务
CREATE TABLE `MN_node_task`
(
    `id`          int(11)     NOT NULL AUTO_INCREMENT,
    `task_id`     varchar(64) NOT NULL,
    `node_id`     varchar(64) NOT NULL,
    `action`      varchar(50) NOT NULL,
    `payload`     mediumtext,
    `status`      varchar(20) NOT NULL DEFAULT 'pending',
    `result`      mediumtext,
    `error`       text,
    `created_at`  varchar(50) NOT NULL,
    `pulled_at`   varchar(50)          DEFAULT NULL,
    `finished_at` varchar(50)          DEFAULT NULL,
    `updated_at`  varchar(50) NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_task_id` (`task_id`),
    KEY `idx_node_status` (`node_id`, `status`)
) ENGINE = MyISAM
  DEFAULT CHARSET = utf8;

DROP TABLE IF EXISTS `MN_node_nonce`; -- MNBT节点防重放
CREATE TABLE `MN_node_nonce`
(
    `id`         int(11)     NOT NULL AUTO_INCREMENT,
    `node_id`    varchar(64) NOT NULL,
    `nonce`      varchar(80) NOT NULL,
    `created_at` varchar(50) NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_node_nonce` (`node_id`, `nonce`)
) ENGINE = MyISAM
  DEFAULT CHARSET = utf8;

DROP TABLE IF EXISTS `MN_forbidden_scan`; -- 违禁词扫描任务
CREATE TABLE `MN_forbidden_scan`
(
    `id`            int(11)      NOT NULL AUTO_INCREMENT,
    `task_id`       varchar(64)  NOT NULL,
    `node_id`       varchar(64)  NOT NULL,
    `site`          varchar(250) NOT NULL DEFAULT '',
    `status`        varchar(20)  NOT NULL DEFAULT 'success',
    `scanned_files` int(11)      NOT NULL DEFAULT 0,
    `scanned_rows`  int(11)      NOT NULL DEFAULT 0,
    `matches_count` int(11)      NOT NULL DEFAULT 0,
    `summary`       mediumtext,
    `created_at`    varchar(50)  NOT NULL,
    `updated_at`    varchar(50)  NOT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_task` (`task_id`),
    KEY `idx_node` (`node_id`)
) ENGINE = MyISAM
  DEFAULT CHARSET = utf8;

DROP TABLE IF EXISTS `MN_forbidden_match`; -- 违禁词命中记录
CREATE TABLE `MN_forbidden_match`
(
    `id`         int(11)       NOT NULL AUTO_INCREMENT,
    `task_id`    varchar(64)   NOT NULL,
    `node_id`    varchar(64)   NOT NULL,
    `site`       varchar(250)  NOT NULL DEFAULT '',
    `match_type` varchar(30)   NOT NULL DEFAULT 'file',
    `target`     varchar(1000) NOT NULL DEFAULT '',
    `line_no`    int(11)       NOT NULL DEFAULT 0,
    `keyword`    varchar(250)  NOT NULL DEFAULT '',
    `excerpt`    text,
    `created_at` varchar(50)   NOT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_task` (`task_id`),
    KEY `idx_node` (`node_id`)
) ENGINE = MyISAM
  DEFAULT CHARSET = utf8;


-- 违禁词扫描配置字段
ALTER TABLE `MN_config`
    ADD `wjsckg` VARCHAR(20) NOT NULL DEFAULT 'false' COMMENT '违禁词扫描开关';
ALTER TABLE `MN_config`
    ADD `wjsccnr` TEXT NULL DEFAULT NULL COMMENT '违禁词内容(每行一个)';
ALTER TABLE `MN_config`
    ADD `wjsckgqbfx` VARCHAR(10) NOT NULL DEFAULT 'true' COMMENT '是否只扫描变更文件';
ALTER TABLE `MN_config`
    ADD `wjscml` VARCHAR(500) NOT NULL DEFAULT '/www/wwwroot' COMMENT '扫描目录';
ALTER TABLE `MN_config`
    ADD `wjstqml` TEXT NULL DEFAULT NULL COMMENT '跳过目录(逗号分隔)';
ALTER TABLE `MN_config`
    ADD `wjstqhz` TEXT NULL DEFAULT NULL COMMENT '跳过后缀(逗号分隔)';
ALTER TABLE `MN_config`
    ADD `wjscdzmax` INT(11) NOT NULL DEFAULT 5242880 COMMENT '单文件最大大小(字节),默认5MB';
ALTER TABLE `MN_config`
    ADD `wjscdhmax` INT(11) NOT NULL DEFAULT 1000 COMMENT '单次扫描最大命中数';
ALTER TABLE `MN_config`
    ADD `wjscqzcs` VARCHAR(50) NOT NULL DEFAULT '0 3 * * *' COMMENT '定时全量复扫 cron 表达式(默认每天凌晨3点)';
ALTER TABLE `MN_config`
    ADD `wjscqzcskg` VARCHAR(20) NOT NULL DEFAULT 'true' COMMENT '定时全量复扫开关';

-- V1.81 插件系统
DROP TABLE IF EXISTS `MN_plugin`;
CREATE TABLE `MN_plugin`
(
    `id`           int(11)      NOT NULL AUTO_INCREMENT,
    `slug`         varchar(64)  NOT NULL,
    `name`         varchar(120) NOT NULL DEFAULT '',
    `version`      varchar(32)  NOT NULL DEFAULT '',
    `enabled`      varchar(10)  NOT NULL DEFAULT 'false',
    `config_json`  mediumtext,
    `installed_at` varchar(50)  NOT NULL DEFAULT '',
    `updated_at`   varchar(50)  NOT NULL DEFAULT '',
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_slug` (`slug`)
) ENGINE = MyISAM
  DEFAULT CHARSET = utf8;

DROP TABLE IF EXISTS `MN_plugin_option`;
CREATE TABLE `MN_plugin_option`
(
    `id`          int(11)      NOT NULL AUTO_INCREMENT,
    `plugin_slug` varchar(64)  NOT NULL,
    `k`           varchar(120) NOT NULL,
    `v`           mediumtext,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_plugin_k` (`plugin_slug`, `k`)
) ENGINE = MyISAM
  DEFAULT CHARSET = utf8;
