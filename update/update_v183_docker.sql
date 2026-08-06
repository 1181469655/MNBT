-- MNBT v1.83 Docker 集成增量升级脚本
-- 新增四张独立表：MN_docker_node / MN_docker_user / MN_docker_plan / MN_docker_order
-- Docker 模块独立于 MN_bt，节点信息存于 MN_docker_node
-- 执行方式：在 MNBT 管理后台或数据库管理中导入本文件
-- 全新安装请使用 install/install.sql（已包含本四表）

-- Docker 节点（独立的宝塔 Docker 面板实例，不依赖 MN_bt）
CREATE TABLE IF NOT EXISTS `MN_docker_node` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,                                  -- 节点名称（显示用）
  `btip` varchar(128) NOT NULL,                                 -- 宝塔面板地址（IP/域名）
  `btdk` varchar(10) NOT NULL DEFAULT '8888',                   -- 宝塔端口
  `ptl` varchar(10) NOT NULL DEFAULT 'false',                   -- 是否 HTTPS
  `btmy` varchar(255) NOT NULL,                                 -- 宝塔接口密钥
  `ktmy` varchar(255) NOT NULL,                                 -- 调用密钥（外部 API 鉴权用）
  `qmk` varchar(255) NOT NULL DEFAULT '',                       -- 二级验证密钥
  `qk` varchar(10) NOT NULL DEFAULT 'true',                     -- 启用/禁用
  `date` varchar(50) NOT NULL,                                  -- 添加时间
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- Docker 用户（单容器模型：每账户固定一个容器）
CREATE TABLE IF NOT EXISTS `MN_docker_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(64) NOT NULL,                              -- 登录名（唯一），同时作为容器命名前缀
  `password_hash` varchar(255) NOT NULL,                        -- password_hash/bcrypt
  `email` varchar(128) DEFAULT NULL,                            -- 邮箱
  `ssbt` int(11) NOT NULL DEFAULT '0',                          -- 所属 Docker 节点 MN_docker_node.id
  `data` varchar(50) NOT NULL,                                  -- 开通时间
  `datae` varchar(50) NOT NULL,                                 -- 到期时间（0000-00-00=永久）
  `qk` varchar(20) NOT NULL DEFAULT 'active',                   -- 状态：active/expired/paused/pruned
  `plan_id` int(11) DEFAULT NULL,                               -- 套餐 MN_docker_plan.id
  `container_id` varchar(64) DEFAULT NULL,                      -- 已创建容器 ID（单容器）
  `service_name` varchar(64) DEFAULT NULL,                      -- create_app 的 service_name（唯一）
  `app_name` varchar(64) DEFAULT NULL,                          -- 应用名（源自 get_apps）
  `container_spec` text,                                        -- 用户选择的容器规格 JSON（镜像/版本/cpus/mem/appenv）
  `container_status` varchar(20) DEFAULT 'none',                -- none/creating/running/stopped/failed
  `disk_usage` bigint(20) NOT NULL DEFAULT '0',                 -- 最近磁盘用量（字节，由 get_path_size 采集）
  `disk_usage_at` varchar(50) DEFAULT NULL,                     -- 磁盘用量采集时间
  `expired_at` varchar(50) DEFAULT NULL,                        -- 软删开始时间（到期时间）
  `prune_due` varchar(50) DEFAULT NULL,                         -- 7 天物理删除到期时间（空=未排程）
  `extra` text,                                                 -- JSON 扩展（compose_dir 等）
  `created_at` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_username` (`username`),
  KEY `idx_ssbt` (`ssbt`),
  KEY `idx_qk` (`qk`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- Docker 套餐（单容器配额）
CREATE TABLE IF NOT EXISTS `MN_docker_plan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,                                  -- 套餐名
  `jc` text,                                                    -- 介绍
  `cpu_max` varchar(20) NOT NULL DEFAULT '1',                   -- CPU 核上限（create_app.cpus）
  `mem_max` varchar(20) NOT NULL DEFAULT '512',                 -- 内存 MB 上限（create_app.memory_limit）
  `disk_max` varchar(20) NOT NULL DEFAULT '0',                  -- 磁盘配额 MB 上限（0=不限制，create_app.appenv 下发）
  `proxy_max` varchar(20) NOT NULL DEFAULT '0',                 -- 反向代理数量上限（0=不限制）
  `jg` varchar(50) NOT NULL,                                    -- 价格
  `qk` varchar(10) NOT NULL DEFAULT 'true',                     -- 上架/下架
  `date` varchar(50) NOT NULL,                                  -- 添加时间
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- Docker 订单（预留，P0 只建表不接支付）
CREATE TABLE IF NOT EXISTS `MN_docker_order` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(64) NOT NULL,                              -- MN_docker_user.username
  `plan_id` int(11) NOT NULL,                                   -- 套餐 ID
  `rmb` varchar(50) NOT NULL,                                   -- 金额
  `qk` varchar(10) NOT NULL DEFAULT 'false',                    -- 支付状态
  `date` varchar(50) NOT NULL,                                  -- 下单时间
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- v1.83.1 磁盘配额：为已有表添加 disk_max / disk_usage / disk_usage_at 字段（若不存在）
ALTER TABLE `MN_docker_plan` ADD COLUMN IF NOT EXISTS `disk_max` varchar(20) NOT NULL DEFAULT '0' COMMENT '磁盘配额 MB 上限（0=不限制）';
ALTER TABLE `MN_docker_plan` ADD COLUMN IF NOT EXISTS `proxy_max` varchar(20) NOT NULL DEFAULT '0' COMMENT '反向代理数量上限（0=不限制）';
ALTER TABLE `MN_docker_user` ADD COLUMN IF NOT EXISTS `disk_usage` bigint(20) NOT NULL DEFAULT '0' COMMENT '最近磁盘用量（字节）';
ALTER TABLE `MN_docker_user` ADD COLUMN IF NOT EXISTS `disk_usage_at` varchar(50) DEFAULT NULL COMMENT '磁盘用量采集时间';
