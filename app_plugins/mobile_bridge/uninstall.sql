-- mobile_bridge 卸载：仅清理自有表，不动核心数据
DROP TABLE IF EXISTS `MN_plugin_app_device`;
DROP TABLE IF EXISTS `MN_plugin_app_pair`;
