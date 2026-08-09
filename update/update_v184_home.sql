-- MNBT v1.84 独立主页系统增量升级脚本
-- 为 MN_config 追加 home_* 配置字段（内置主页：标题 / Hero / 主色 / Logo / Favicon / 版权 / 区块开关）
-- 执行方式：在 MNBT 管理后台或数据库管理中导入本文件
-- 全新安装请使用 install/install.sql（已包含本字段）
--
-- 说明：MySQL 8.0 之前的版本不支持 "ADD COLUMN IF NOT EXISTS"，
-- 此处改用 information_schema 判断列是否存在 + 动态 SQL，兼容 MySQL 5.5+/MariaDB。

SET @mnbt_db = DATABASE();

-- home_theme
SET @s = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE table_schema = @mnbt_db AND table_name = 'MN_config' AND column_name = 'home_theme') = 0,
  'ALTER TABLE `MN_config` ADD COLUMN `home_theme` varchar(50) NOT NULL DEFAULT '''''' ,
  'SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

-- home_enable
SET @s = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE table_schema = @mnbt_db AND table_name = 'MN_config' AND column_name = 'home_enable') = 0,
  'ALTER TABLE `MN_config` ADD COLUMN `home_enable` varchar(10) NOT NULL DEFAULT ''true''',
  'SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

-- home_title
SET @s = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE table_schema = @mnbt_db AND table_name = 'MN_config' AND column_name = 'home_title') = 0,
  'ALTER TABLE `MN_config` ADD COLUMN `home_title` text NOT NULL',
  'SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

-- home_hero
SET @s = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE table_schema = @mnbt_db AND table_name = 'MN_config' AND column_name = 'home_hero') = 0,
  'ALTER TABLE `MN_config` ADD COLUMN `home_hero` text NOT NULL',
  'SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

-- home_primary
SET @s = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE table_schema = @mnbt_db AND table_name = 'MN_config' AND column_name = 'home_primary') = 0,
  'ALTER TABLE `MN_config` ADD COLUMN `home_primary` varchar(10) NOT NULL DEFAULT ''#4f46e5''',
  'SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

-- home_logo
SET @s = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE table_schema = @mnbt_db AND table_name = 'MN_config' AND column_name = 'home_logo') = 0,
  'ALTER TABLE `MN_config` ADD COLUMN `home_logo` text NOT NULL',
  'SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

-- home_favicon
SET @s = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE table_schema = @mnbt_db AND table_name = 'MN_config' AND column_name = 'home_favicon') = 0,
  'ALTER TABLE `MN_config` ADD COLUMN `home_favicon` text NOT NULL',
  'SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

-- home_footer
SET @s = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE table_schema = @mnbt_db AND table_name = 'MN_config' AND column_name = 'home_footer') = 0,
  'ALTER TABLE `MN_config` ADD COLUMN `home_footer` text NOT NULL',
  'SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

-- home_show_notice
SET @s = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE table_schema = @mnbt_db AND table_name = 'MN_config' AND column_name = 'home_show_notice') = 0,
  'ALTER TABLE `MN_config` ADD COLUMN `home_show_notice` varchar(10) NOT NULL DEFAULT ''true''',
  'SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

-- home_show_plans
SET @s = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE table_schema = @mnbt_db AND table_name = 'MN_config' AND column_name = 'home_show_plans') = 0,
  'ALTER TABLE `MN_config` ADD COLUMN `home_show_plans` varchar(10) NOT NULL DEFAULT ''true''',
  'SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
