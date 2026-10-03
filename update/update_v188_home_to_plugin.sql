-- MNBT v1.88 官网首页迁移插件增量升级 SQL
-- 官网首页（站点根路径 /）自核心独立主页系统（V1.84）迁入 official_site 插件：
--   1) 将 MN_config.home_* 配置值拷贝到 MN_plugin_option（official_site 插件 options），
--      保证无论升级 SQL 与插件首次引导谁先执行，旧配置都不丢失
--   2) 拷贝完成后删除 MN_config.home_* 列
-- 与插件侧 official_site_home_migrate_legacy()（读列兜底）配合，全程可重复执行。
-- 使用存储过程实现列存在检查，兼容 MySQL 5.5+ / MariaDB

DELIMITER $$

DROP PROCEDURE IF EXISTS mnbt_home_migrate_col$$
CREATE PROCEDURE mnbt_home_migrate_col(IN c VARCHAR(64))
BEGIN
  IF (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE table_schema = DATABASE() AND table_name = 'MN_config' AND column_name = c) > 0 THEN
    SET @s = CONCAT(
      'INSERT IGNORE INTO `MN_plugin_option` (`plugin_slug`, `k`, `v`) ',
      'SELECT ''official_site'', ''', c, ''', `', c, '` FROM `MN_config` WHERE `', c, '` <> '''' LIMIT 1'
    );
    PREPARE st FROM @s;
    EXECUTE st;
    DEALLOCATE PREPARE st;
  END IF;
END$$

DROP PROCEDURE IF EXISTS mnbt_home_drop_col$$
CREATE PROCEDURE mnbt_home_drop_col(IN c VARCHAR(64))
BEGIN
  IF (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE table_schema = DATABASE() AND table_name = 'MN_config' AND column_name = c) > 0 THEN
    SET @s = CONCAT('ALTER TABLE `MN_config` DROP COLUMN `', c, '`');
    PREPARE st FROM @s;
    EXECUTE st;
    DEALLOCATE PREPARE st;
  END IF;
END$$

DELIMITER ;

CALL mnbt_home_migrate_col('home_enable');
CALL mnbt_home_migrate_col('home_theme_settings');
CALL mnbt_home_migrate_col('home_title');
CALL mnbt_home_migrate_col('home_hero');
CALL mnbt_home_migrate_col('home_primary');
CALL mnbt_home_migrate_col('home_logo');
CALL mnbt_home_migrate_col('home_favicon');
CALL mnbt_home_migrate_col('home_footer');
CALL mnbt_home_migrate_col('home_show_notice');
CALL mnbt_home_migrate_col('home_show_plans');
-- home_theme（主页主题选择）随主题作用域一并下线，不迁移
CALL mnbt_home_migrate_col('home_theme');

CALL mnbt_home_drop_col('home_enable');
CALL mnbt_home_drop_col('home_theme');
CALL mnbt_home_drop_col('home_theme_settings');
CALL mnbt_home_drop_col('home_title');
CALL mnbt_home_drop_col('home_hero');
CALL mnbt_home_drop_col('home_primary');
CALL mnbt_home_drop_col('home_logo');
CALL mnbt_home_drop_col('home_favicon');
CALL mnbt_home_drop_col('home_footer');
CALL mnbt_home_drop_col('home_show_notice');
CALL mnbt_home_drop_col('home_show_plans');

DROP PROCEDURE IF EXISTS mnbt_home_migrate_col;
DROP PROCEDURE IF EXISTS mnbt_home_drop_col;
