-- MNBT V1.85 对外 API 协议兼容开关增量 SQL
-- 为 MN_config 追加 api_compat 字段（0=1.83+ 严格协议，1=兼容 1.81 对接模块）
-- 注意：在线更新的执行器按分号逐句跑且忽略单句报错，所以这里用裸 ALTER，
-- 不要写成 DELIMITER/存储过程（那样会被拆碎），列已存在时报错会被安全跳过。
ALTER TABLE `MN_config` ADD `api_compat` varchar(10) NOT NULL DEFAULT '0';
