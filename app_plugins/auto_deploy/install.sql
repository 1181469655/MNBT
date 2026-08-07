CREATE TABLE IF NOT EXISTS `plg_auto_deploy_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `node_id` int(11) NOT NULL DEFAULT 0,
  `node_name` varchar(100) NOT NULL DEFAULT '',
  `dname` varchar(200) NOT NULL DEFAULT '',
  `site_name` varchar(200) NOT NULL DEFAULT '',
  `project_type` varchar(20) NOT NULL DEFAULT 'php',
  `result` varchar(20) NOT NULL DEFAULT 'success',
  `admin_username` varchar(200) NOT NULL DEFAULT '',
  `admin_password` varchar(200) NOT NULL DEFAULT '',
  `success_url` varchar(500) NOT NULL DEFAULT '',
  `admin_user` varchar(100) NOT NULL DEFAULT '',
  `created_at` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `idx_node` (`node_id`),
  KEY `idx_date` (`created_at`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
