-- V1.81 P3: 支付插件系统迁移
-- 为已安装的系统添加 pay_methods 字段（存储已启用的付款方式 JSON 配置）

ALTER TABLE `MN_config` ADD COLUMN `pay_methods` text NOT NULL DEFAULT '' AFTER `hxd`;
