-- official_site 插件安装 SQL
-- 企业官网内容管理：产品展示、新闻资讯、联系留言

-- 产品表
DROP TABLE IF EXISTS `MN_plugin_site_product`;
CREATE TABLE `MN_plugin_site_product` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(120) NOT NULL DEFAULT '' COMMENT '产品名称',
  `category` varchar(50) NOT NULL DEFAULT '' COMMENT '分类 ai/cloud/hosting/domain/security/service',
  `description` text NOT NULL COMMENT '产品简介',
  `features` text NOT NULL COMMENT '特性列表 JSON 数组',
  `image` varchar(500) NOT NULL DEFAULT '' COMMENT '展示图片 URL',
  `status` varchar(20) NOT NULL DEFAULT 'active' COMMENT 'active/inactive',
  `sort` int(11) NOT NULL DEFAULT '50' COMMENT '排序（小到大）',
  `created_at` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `idx_category` (`category`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- 新闻表
DROP TABLE IF EXISTS `MN_plugin_site_news`;
CREATE TABLE `MN_plugin_site_news` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(200) NOT NULL DEFAULT '' COMMENT '标题',
  `category` varchar(50) NOT NULL DEFAULT '' COMMENT '分类 产品发布/优惠活动/行业动态/平台公告',
  `content` mediumtext NOT NULL COMMENT '正文',
  `views` int(11) NOT NULL DEFAULT '0' COMMENT '浏览量',
  `status` varchar(20) NOT NULL DEFAULT 'active' COMMENT 'active/inactive',
  `sort` int(11) NOT NULL DEFAULT '50' COMMENT '排序（小到大）',
  `created_at` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `idx_category` (`category`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- 留言表
DROP TABLE IF EXISTS `MN_plugin_site_message`;
CREATE TABLE `MN_plugin_site_message` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(60) NOT NULL DEFAULT '' COMMENT '姓名',
  `email` varchar(120) NOT NULL DEFAULT '' COMMENT '邮箱',
  `phone` varchar(30) NOT NULL DEFAULT '' COMMENT '电话',
  `message` text NOT NULL COMMENT '留言内容',
  `is_read` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否已读',
  `created_at` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `idx_read` (`is_read`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ============================================================
-- 初始示例数据（适配 MNBT 业务语境）
-- ============================================================

INSERT INTO `MN_plugin_site_product` (`name`, `category`, `description`, `features`, `image`, `status`, `sort`, `created_at`) VALUES
('AI 智能分析平台', 'ai', '基于深度学习的业务数据智能分析与预测平台，帮助企业洞察趋势、辅助决策。', '["多源数据接入与自动清洗","可视化报表与预测模型","异常检测与实时告警","支持私有化部署"]', '', 'active', 10, '2026-08-10 00:00:00'),
('高性能云服务器', 'cloud', '弹性计算、秒级开通的企业级云服务器，按需选择配置，随时升级。', '["全 SSD 存储集群","BGP 多线接入低延迟","弹性升配秒级生效","快照与自动备份"]', '', 'active', 20, '2026-08-10 00:00:00'),
('虚拟主机托管', 'hosting', '即开即用的一键托管虚拟主机，支付后自动开通，分钟级上线，含宝塔面板。', '["一键部署宝塔面板","网页/数据库/流量灵活配置","自动开通分钟级上线","每日自动备份数据"]', '', 'active', 30, '2026-08-10 00:00:00'),
('域名注册', 'domain', '全球主流域名后缀注册与管理，免费智能解析，一键绑定主机。', '["主流后缀全覆盖","免费智能 DNS 解析","一键解析到主机","域名隐私保护"]', '', 'active', 40, '2026-08-10 00:00:00'),
('SSL 证书', 'security', 'DV / OV 企业级 HTTPS 证书，全站加密，提升信任与安全。', '["免费 DV 证书快速签发","企业 OV 证书验证","到期自动续期","全站 HTTPS 加密"]', '', 'active', 50, '2026-08-10 00:00:00'),
('网站安全防护', 'security', 'DDoS 流量清洗与 WAF 网站防火墙，为业务提供全方位安全防护。', '["DDoS 流量清洗","WAF 规则实时拦截","CC 攻击防护","安全监控与报表"]', '', 'active', 60, '2026-08-10 00:00:00');

INSERT INTO `MN_plugin_site_news` (`title`, `category`, `content`, `views`, `status`, `sort`, `created_at`) VALUES
('虚拟主机套餐全面升级：全系标配 SSD 与自动备份', '产品发布', '为进一步提升用户体验，我们完成了虚拟主机套餐的全线升级：\n\n全系更换 SSD 存储，磁盘读写性能大幅提升；所有套餐默认开启每日自动备份，数据安全更有保障；控制面板同步升级，操作更加流畅。\n\n已有用户无需任何操作，自动享受升级后的性能与容量。感谢大家的支持！', 128, 'active', 10, '2026-08-08 10:00:00'),
('AI 智能分析平台正式上线', '产品发布', '我们很高兴地宣布，AI 智能分析平台正式上线！\n\n平台基于深度学习技术，提供多源数据接入、可视化报表、异常检测与预测分析能力，支持私有化部署，帮助企业低成本构建数据洞察能力。\n\n即日起开放预约试用，欢迎通过页面底部联系方式与我们取得联系。', 96, 'active', 20, '2026-07-30 14:00:00'),
('周年庆全场 8 折，新老用户同享', '优惠活动', '周年庆活动正式开启！\n\n活动期间，全场虚拟主机、云服务器套餐均享 8 折优惠，新老用户同享，数量有限先到先得。\n\n参与方式：前往主机套餐页面选择心仪套餐下单即可，活动价格自动生效。', 220, 'active', 30, '2026-07-20 09:00:00'),
('新用户注册即送余额礼包', '优惠活动', '欢迎新用户！\n\n即日起注册平台的用户，将自动获得余额礼包，可用于购买任意套餐或续费，无使用门槛。\n\n快来注册体验吧，开启您的建站之旅！', 310, 'active', 40, '2026-07-10 11:00:00'),
('数据中心华东节点完成扩容', '行业动态', '为应对快速增长的业务需求，我们完成了华东地区数据中心的扩容升级。\n\n本次扩容新增多组高可用节点，网络带宽进一步提升，华东地区用户访问延迟显著降低。\n\n后续我们将持续优化全国节点布局，为用户提供更稳定、更快速的访问体验。', 75, 'active', 50, '2026-06-28 16:00:00'),
('平台通过国家等级保护三级认证', '行业动态', '经过权威测评机构全面评估，平台正式通过国家信息安全等级保护三级认证。\n\n这标志着平台在信息安全技术与管理体系上达到业界先进水平，能够为用户业务提供更可靠的保障。', 60, 'active', 60, '2026-06-15 10:00:00'),
('关于 ICP 备案信息更新的公告', '平台公告', '根据国家相关规定，平台官网 ICP 备案信息已完成更新，新版备案信息已同步至网站页脚。\n\n如您在访问过程中有任何疑问，欢迎通过联系我们页面反馈，我们将及时处理。', 45, 'active', 70, '2026-06-01 10:00:00'),
('用户服务协议与隐私政策更新', '平台公告', '为更好地保障用户权益，我们对用户服务协议与隐私政策进行了修订更新。\n\n新版协议自发布之日起生效，涉及账号管理、服务条款、数据处理与隐私保护等内容，请广大用户知悉。\n\n如有疑问，可通过联系我们页面与我们沟通。', 52, 'active', 80, '2026-05-20 10:00:00');
