-- MNBT v1.84 独立主页系统增量升级 SQL
-- 为 MN_config 追加 home_* 配置字段
-- 使用存储过程实现列存在检查，兼容 MySQL 5.5+ / MariaDB
-- 可重复执行，已有列会跳过

DELIMITER $$

DROP PROCEDURE IF EXISTS mnbt_add_col$$
CREATE PROCEDURE mnbt_add_col(IN t VARCHAR(64), IN c VARCHAR(64), IN d VARCHAR(255))
BEGIN
  IF (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE table_schema = DATABASE() AND table_name = t AND column_name = c) = 0 THEN
    SET @s = CONCAT('ALTER TABLE `', t, '` ADD COLUMN `', c, '` ', d);
    PREPARE st FROM @s;
    EXECUTE st;
    DEALLOCATE PREPARE st;
  END IF;
END$$

DELIMITER ;

CALL mnbt_add_col('MN_config', 'home_theme',       "varchar(50) NOT NULL DEFAULT ''");
CALL mnbt_add_col('MN_config', 'home_enable',      "varchar(10) NOT NULL DEFAULT 'true'");
CALL mnbt_add_col('MN_config', 'home_title',       'text NOT NULL');
CALL mnbt_add_col('MN_config', 'home_hero',        'text NOT NULL');
CALL mnbt_add_col('MN_config', 'home_primary',     "varchar(10) NOT NULL DEFAULT '#4f46e5'");
CALL mnbt_add_col('MN_config', 'home_logo',        'text NOT NULL');
CALL mnbt_add_col('MN_config', 'home_favicon',     'text NOT NULL');
CALL mnbt_add_col('MN_config', 'home_footer',      'text NOT NULL');
CALL mnbt_add_col('MN_config', 'home_show_notice', "varchar(10) NOT NULL DEFAULT 'true'");
CALL mnbt_add_col('MN_config', 'home_show_plans',  "varchar(10) NOT NULL DEFAULT 'true'");

DROP PROCEDURE IF EXISTS mnbt_add_col;
