-- user_info 插件：独立用户表
-- 密码使用 bcrypt 哈希（password_hash / password_verify）

DROP TABLE IF EXISTS `MN_plugin_user`;
CREATE TABLE `MN_plugin_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(64) NOT NULL,                -- 用户名（字母数字下划线，3~32 字符）
  `password_hash` varchar(255) NOT NULL,          -- bcrypt 哈希
  `email` varchar(128) NOT NULL DEFAULT '',       -- 邮箱（可选）
  `qq` varchar(20) NOT NULL DEFAULT '',           -- QQ（可选）
  `status` tinyint(1) NOT NULL DEFAULT 1,         -- 1正常 0禁用
  `created_at` varchar(50) NOT NULL,              -- 注册时间
  `updated_at` varchar(50) NOT NULL,              -- 更新时间
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_username` (`username`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
