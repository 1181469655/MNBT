-- MNBT V1.89 密文列宽度增量 SQL
-- MN_zj.pass / MN_config.mailpassword 原为 varchar(50)，改成 enc1: 密文后必然超长，
-- 而本项目连接层 set sql_mode=''，截断不报错，直接把密文写残（永久解不开）。
-- 注意：执行器按分号逐句跑且忽略单句报错，所以用裸 ALTER；
-- 列本来就是 VARCHAR(500) 或表不存在时报错会被安全跳过。
ALTER TABLE `MN_zj` MODIFY `pass` VARCHAR(500) NOT NULL;
ALTER TABLE `MN_config` MODIFY `mailpassword` VARCHAR(500) NULL;
