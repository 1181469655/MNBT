<?php
include("../MPHX/common.php");
include("../MPHX/BL.php");
include_once("../MPHX/github_updater.php");
include("../cf_up.php");
$title = 'MN宝塔主机系统更新';
mnbt_admin_require_login();
// 检查一次更新，两套模板共用同一份数据；recheck=1 时跳过缓存重新问 GitHub（页面最多 60 秒问一次）
$mnbt_update = mnbt_updater_check(!empty($_GET['recheck']), 60);
$mnbt_upcfg = mnbt_updater_config();
mnbt_admin_render('update');
