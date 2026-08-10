-- realname 插件：实名认证记录表
-- 身份证号/OCR 身份证号使用 AES-256-CBC 加密存储（密钥在 MN_plugin_option）
-- 照片存放于 runtime/realname/{user_id}/，表中仅存相对文件名（随机名）

CREATE TABLE IF NOT EXISTS `plg_realname_auth` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,                   -- MN_plugin_user.id
  `username` varchar(64) NOT NULL DEFAULT '',   -- 冗余用户名，便于后台展示
  `real_name` varchar(64) NOT NULL DEFAULT '',  -- 姓名（明文）
  `phone` varchar(20) NOT NULL DEFAULT '',      -- 手机号（明文）
  `id_card` varchar(255) NOT NULL DEFAULT '',   -- 身份证号（AES 加密）
  `front_img` varchar(255) NOT NULL DEFAULT '', -- 身份证正面文件名（随机名，位于 runtime/realname/{user_id}/）
  `back_img` varchar(255) NOT NULL DEFAULT '',  -- 身份证反面文件名
  `hand_img` varchar(255) NOT NULL DEFAULT '',  -- 手持身份证文件名
  `ocr_name` varchar(64) NOT NULL DEFAULT '',   -- OCR 识别的姓名
  `ocr_id_card` varchar(255) NOT NULL DEFAULT '', -- OCR 识别的身份证号（AES 加密）
  `status` varchar(16) NOT NULL DEFAULT 'pending', -- pending/approved/rejected
  `audit_note` varchar(255) NOT NULL DEFAULT '',   -- 审核备注（自动失败原因/管理员驳回原因）
  `created_at` varchar(50) NOT NULL DEFAULT '',
  `updated_at` varchar(50) NOT NULL DEFAULT '',
  `audited_at` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_user` (`user_id`),
  KEY `idx_status` (`status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
