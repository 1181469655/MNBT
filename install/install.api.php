<?php
error_reporting(0);
@header('Content-Type: text/html; charset=UTF-8');
define('IN_CRONLITE', true);
include("../cf_up.php");
include("../MPHX/BL.php");
include_once("../MPHX/Response.php");
$action = $_GET['action'] ?? 'index';
$vs = sprintf("%.2f ", $WEBQB / 1000);

function Res(int $code, string $msg = '返回信息', ?array $data = null, ?int $redirect = null)
{
    return Response::json($code, $msg, $data, $redirect);
}

function table_exists_case($tableName) {
    $result = DB::query("SHOW TABLES");
    if ($result) {
        while ($row = DB::fetch($result)) {
            if (strcasecmp(reset($row), $tableName) === 0) return true;
        }
    }
    return false;
}

// 返回规范表名 → 实际表名的映射（兼容大小写迁移场景）
function table_case_map($canonical_list) {
    $map = array();
    $result = DB::query("SHOW TABLES");
    if ($result) {
        $existing = array();
        while ($row = DB::fetch($result)) {
            $existing[] = reset($row);
        }
        foreach ($canonical_list as $canon) {
            foreach ($existing as $real) {
                if (strcasecmp($real, $canon) === 0) {
                    $map[$canon] = $real;
                    break;
                }
            }
        }
    }
    return $map;
}

if (file_exists('install.lock')) exit(Res(1, '已安装', ['vs' => $vs,'is_install'=>true], 1));

function send_post()
{
    //如果需要进行离线安装请将$ins_tall=true;改为$ins_tall=false;
    //注意：离线安装不支持进行在线更新！
    //如果无法安装可尝试进行离线安装！
    try {
        $ins_tall = true;
        if (!$ins_tall) return array('code' => 1, 'authcode' => '您安装时使用的为离线安装！');
        global $mn_conf;
        if (empty($mn_conf['url']) || empty($mn_conf['aet']) || empty($mn_conf['install_wj'])) {
            return array('code' => 1, 'authcode' => '');
        }
        include("../MPHX/BL.php");
        $url = $mn_conf['aet'] . "://" . $mn_conf['url'] . ":" . $mn_conf['port'] . "/" . $mn_conf['install_wj'] . "/coder.php";
        $post_data = array(
            'url' => $_SERVER['HTTP_HOST'],
            'bb' => $WEBQB,
        );
        $postdata = http_build_query($post_data);
        $options = array(
            'http' => array(
                'method' => 'POST',
                'header' => 'Content-type:application/x-www-form-urlencoded',
                'content' => $postdata,
                'timeout' => 5 // 缩短超时
            )
        );
        $context = stream_context_create($options);
        $result = @file_get_contents($url, false, $context);
        if ($result === false) return array('code' => 1, 'authcode' => '');
        $decoded = json_decode($result, true);
        if (!is_array($decoded)) return array('code' => 1, 'authcode' => '');
        return $decoded;
    } catch (Exception $e) {
        return array('code' => 1, 'authcode' => '');
    }
}

switch ($action) {
    case 'index':
        echo Res(1, '基础信息返回', ['vs' => $vs]);
        break;
    case 'system':
        echo Res(1, '系统基础环境监测', [
            'vs' => [
                'info' => PHP_VERSION,
                'is_vs_install' => version_compare(PHP_VERSION, '7.4.0', '>=')
            ],
            'curl_exec' => function_exists('curl_exec'),
            'mn_link' => ((int)send_post()['code'] ?? 0) === 1,
        ]);
        break;
    case 'database_info_wire':
        require_once './db.class.php';
        $db_host = isset($_POST['db_host']) ? $_POST['db_host'] : NULL;
        $db_port = isset($_POST['db_port']) ? $_POST['db_port'] : NULL;
        $db_user = isset($_POST['db_user']) ? $_POST['db_user'] : NULL;
        $db_pwd = isset($_POST['db_pwd']) ? $_POST['db_pwd'] : NULL;
        $db_name = isset($_POST['db_name']) ? $_POST['db_name'] : NULL;
        if ($db_host == null || $db_port == null || $db_user == null || $db_pwd == null || $db_name == null) {
            echo '<div class="alert alert-danger">保存错误,请确保每项都不为空<hr/><a href="javascript:history.back(-1)"><< 返回上一页</a></div>';
            exit;
        }
        $result = send_post();
        if (is_array($result) && $result['code'] == '0') {
            exit(Res(0,$result['msg']));
        } else {
            $config = "<?php
            /*数据库配置*/
            \$dbconfig=array(
                'host' => '{$db_host}', //数据库服务器
                'port' => {$db_port}, //数据库端口
                'user' => '{$db_user}', //数据库用户名
                'pwd' => '{$db_pwd}', //数据库密码
                'dbname' => '{$db_name}', //数据库名
            );
            ?>";
            $mnAuthcode="<?php 
            \$authcode='{$result["authcode"]}';
            ?>";
        }
        if(!$con=DB::connect($db_host,$db_user,$db_pwd,$db_name,$db_port)){
            $enumMsg=[
                2002=>'连接数据库失败，数据库地址填写错误！',
                1045=>'连接数据库失败，数据库用户名或密码填写错误！',
                1049=>'连接数据库失败，数据库名不存在！',
            ];
            exit(Res(0,$enumMsg[DB::connect_errno()]??'['.DB::connect_errno().']'.DB::connect_error()));
        }else{
            $cfgw = file_put_contents('../config.php', $config);
            $sqw = file_put_contents('../MPHX/SQ.php', $mnAuthcode);
            if ($cfgw !== false && $sqw !== false) {
                // 用 SHOW TABLES 获取全部表名，PHP 端忽略大小写比较
                $all_tables_result = DB::query("SHOW TABLES");
                $found = array();
                $in_table = false;
                if ($all_tables_result) {
                    while ($row = DB::fetch($all_tables_result)) {
                        $tbl = reset($row);
                        $found[] = $tbl;
                        if (strcasecmp($tbl, 'MN_config') === 0) $in_table = true;
                    }
                }
                $diag = array('in_table' => $in_table);
                if (!$in_table) {
                    $diag['db_error'] = DB::error() ?: '';
                    $diag['all_tables'] = $found;
                }
                echo Res(1, '数据库信息保存成功', $diag);
            } else {
                $err_detail = '';
                if ($cfgw === false) $err_detail .= 'config.php 写入失败；';
                if ($sqw === false) $err_detail .= 'MPHX/SQ.php 写入失败；';
                if (!is_writable('..')) $err_detail .= '上级目录不可写；';
                if (!is_writable('../MPHX')) $err_detail .= 'MPHX目录不可写；';
                if (file_exists('../config.php') && !is_writable('../config.php')) $err_detail .= 'config.php存在但不可覆盖；';
                if (file_exists('../MPHX/SQ.php') && !is_writable('../MPHX/SQ.php')) $err_detail .= 'MPHX/SQ.php存在但不可覆盖；';
                echo Res(0, '数据库信息保存失败：' . $err_detail);
            }
        }
        break;
    case 'check_upgrade':
        include_once '../config.php';
        if (!$dbconfig['user'] || !$dbconfig['pwd'] || !$dbconfig['dbname']) {
            exit(Res(0, '请先填写并保存数据库配置'));
        }
        require_once './db.class.php';
        $cn = DB::connect($dbconfig['host'], $dbconfig['user'], $dbconfig['pwd'], $dbconfig['dbname'], $dbconfig['port']);
        if (!$cn) {
            exit(Res(0, '数据库连接失败：' . DB::connect_error()));
        }
        DB::query("set sql_mode = ''");
        DB::query("set names utf8");

        $has_config = table_exists_case('MN_config');
        $result = array(
            'is_v179' => false,
            'need_upgrade' => false,
            'missing_tables' => array(),
            'missing_columns' => array(),
        );

        if ($has_config) {
            $result['is_v179'] = true;

            // 获取当前数据库中所有表（忽略大小写）
            $all_tables_result = DB::query("SHOW TABLES");
            $existing_tables = array();
            if ($all_tables_result) {
                while ($row = DB::fetch($all_tables_result)) {
                    $existing_tables[] = strtolower(reset($row));
                }
            }

            // 检查所有标准表
            $all_tables = array(
                'MN_log', 'MN_bt', 'MN_zj', 'MN_bs', 'MN_ym', 'MN_dd',
                'MN_monitor_task', 'MN_monitor_log', 'MN_notice_log',
                'MN_node', 'MN_node_task', 'MN_node_nonce',
                'MN_forbidden_scan', 'MN_forbidden_match',
                'MN_plugin', 'MN_plugin_option'
            );
            foreach ($all_tables as $tbl) {
                if (!in_array(strtolower($tbl), $existing_tables, true)) {
                    $result['need_upgrade'] = true;
                    $result['missing_tables'][] = $tbl;
                }
            }

            // 检查 V1.81 新增字段
            $col_pay = DB::get_row("SHOW COLUMNS FROM `MN_config` LIKE 'pay_methods'");
            if (!$col_pay) {
                $result['need_upgrade'] = true;
                $result['missing_columns'][] = 'MN_config.pay_methods';
            }

            $col_php = DB::get_row("SHOW COLUMNS FROM `MN_bt` LIKE 'mrbts_php'");
            if (!$col_php) {
                $result['need_upgrade'] = true;
                $result['missing_columns'][] = 'MN_bt.mrbts_php';
            }
        }

        echo Res(1, '升级检测完成', $result);
        break;

    case 'repair':
        include_once '../config.php';
        if (!$dbconfig['user'] || !$dbconfig['pwd'] || !$dbconfig['dbname']) {
            exit(Res(0, '请先填写并保存数据库配置'));
        }
        require_once './db.class.php';
        $cn = DB::connect($dbconfig['host'], $dbconfig['user'], $dbconfig['pwd'], $dbconfig['dbname'], $dbconfig['port']);
        if (!$cn) {
            exit(Res(0, '数据库连接失败：' . DB::connect_error()));
        }
        DB::query("set sql_mode = ''");
        DB::query("set names utf8");

        $r_t = 0; $r_e = 0;

        // 获取规范名→实际名映射（兼容跨系统迁移后大小写不一致）
        $all_tables = array('MN_config','MN_log','MN_bt','MN_zj','MN_bs','MN_ym','MN_dd',
            'MN_monitor_task','MN_monitor_log','MN_notice_log',
            'MN_node','MN_node_task','MN_node_nonce',
            'MN_forbidden_scan','MN_forbidden_match',
            'MN_plugin','MN_plugin_option');
        $tbl_map = table_case_map($all_tables);

        // 1. 补齐缺失的表（跳过已存在的，不管大小写）
        $sql = file_get_contents("repair_tables.sql");
        $sql = explode(';', $sql);
        for ($i = 0; $i < count($sql); $i++) {
            $q = trim($sql[$i]);
            if ($q === '') continue;
            // 提取表名，检查是否已存在
            if (preg_match('/CREATE TABLE.*?`(\w+)`/', $q, $m)) {
                if (isset($tbl_map[$m[1]])) continue; // 表已存在（任意大小写），跳过
            }
            if (DB::query($q)) {
                ++$r_t;
            } else {
                ++$r_e;
            }
        }

        // 2. 补齐字段（使用实际表名）
        $actual_config = isset($tbl_map['MN_config']) ? $tbl_map['MN_config'] : 'MN_config';
        $actual_zj     = isset($tbl_map['MN_zj']) ? $tbl_map['MN_zj'] : 'MN_zj';
        $actual_bt     = isset($tbl_map['MN_bt']) ? $tbl_map['MN_bt'] : 'MN_bt';

        $config_cols = array(
            'mailhost','mailuser','mailpassword','mailport',
            'ymjkkg','mtyxfskg','ymjktsyz','wjjkkg','mtwjfskg','wjjktsyz',
            'optionzc','zjyxbd',
            'wjsckg','wjsccnr','wjsckgqbfx','wjscml','wjstqml','wjstqhz','wjscdzmax','wjscdhmax','wjscqzcs','wjscqzcskg',
            'pay_methods',
        );

        // 先获取已有列名
        $existing_cols = array();
        $col_result = DB::query("SHOW COLUMNS FROM `{$actual_config}`");
        if ($col_result) {
            while ($row = DB::fetch($col_result)) {
                $existing_cols[] = strtolower($row['Field']);
            }
        }

        $alter_sqls = array(
            'mailhost' => "ALTER TABLE `{$actual_config}` ADD `mailhost` VARCHAR(50) NULL DEFAULT NULL",
            'mailuser' => "ALTER TABLE `{$actual_config}` ADD `mailuser` VARCHAR(50) NULL DEFAULT NULL",
            'mailpassword' => "ALTER TABLE `{$actual_config}` ADD `mailpassword` VARCHAR(50) NULL DEFAULT NULL",
            'mailport' => "ALTER TABLE `{$actual_config}` ADD `mailport` VARCHAR(20) NOT NULL DEFAULT '465'",
            'ymjkkg' => "ALTER TABLE `{$actual_config}` ADD `ymjkkg` VARCHAR(20) NOT NULL DEFAULT 'false'",
            'mtyxfskg' => "ALTER TABLE `{$actual_config}` ADD `mtyxfskg` VARCHAR(20) NOT NULL DEFAULT 'false'",
            'ymjktsyz' => "ALTER TABLE `{$actual_config}` ADD `ymjktsyz` VARCHAR(20) NOT NULL DEFAULT '7'",
            'wjjkkg' => "ALTER TABLE `{$actual_config}` ADD `wjjkkg` VARCHAR(20) NOT NULL DEFAULT 'false'",
            'mtwjfskg' => "ALTER TABLE `{$actual_config}` ADD `mtwjfskg` VARCHAR(50) NOT NULL DEFAULT 'false'",
            'wjjktsyz' => "ALTER TABLE `{$actual_config}` ADD `wjjktsyz` VARCHAR(20) NOT NULL DEFAULT '7'",
            'optionzc' => "ALTER TABLE `{$actual_config}` ADD `optionzc` VARCHAR(20) NOT NULL DEFAULT 'stop'",
            'zjyxbd' => "ALTER TABLE `{$actual_config}` ADD `zjyxbd` VARCHAR(20) NOT NULL DEFAULT 'true'",
            'wjsckg' => "ALTER TABLE `{$actual_config}` ADD `wjsckg` VARCHAR(20) NOT NULL DEFAULT 'false'",
            'wjsccnr' => "ALTER TABLE `{$actual_config}` ADD `wjsccnr` TEXT NULL DEFAULT NULL",
            'wjsckgqbfx' => "ALTER TABLE `{$actual_config}` ADD `wjsckgqbfx` VARCHAR(10) NOT NULL DEFAULT 'true'",
            'wjscml' => "ALTER TABLE `{$actual_config}` ADD `wjscml` VARCHAR(500) NOT NULL DEFAULT '/www/wwwroot'",
            'wjstqml' => "ALTER TABLE `{$actual_config}` ADD `wjstqml` TEXT NULL DEFAULT NULL",
            'wjstqhz' => "ALTER TABLE `{$actual_config}` ADD `wjstqhz` TEXT NULL DEFAULT NULL",
            'wjscdzmax' => "ALTER TABLE `{$actual_config}` ADD `wjscdzmax` INT(11) NOT NULL DEFAULT 5242880",
            'wjscdhmax' => "ALTER TABLE `{$actual_config}` ADD `wjscdhmax` INT(11) NOT NULL DEFAULT 1000",
            'wjscqzcs' => "ALTER TABLE `{$actual_config}` ADD `wjscqzcs` VARCHAR(50) NOT NULL DEFAULT '0 3 * * *'",
            'wjscqzcskg' => "ALTER TABLE `{$actual_config}` ADD `wjscqzcskg` VARCHAR(20) NOT NULL DEFAULT 'true'",
            'pay_methods' => "ALTER TABLE `{$actual_config}` ADD `pay_methods` TEXT NOT NULL DEFAULT ''",
            // MN_zj
            'backup' => "ALTER TABLE `{$actual_zj}` ADD `backup` VARCHAR(50) NOT NULL DEFAULT '{\"max\":\"3\",\"dq\":0}'",
            'mailuser_zj' => "ALTER TABLE `{$actual_zj}` ADD `mailuser` VARCHAR(50) NULL DEFAULT NULL",
            // MN_bt
            'ftpdz' => "ALTER TABLE `{$actual_bt}` ADD `ftpdz` VARCHAR(50) NOT NULL DEFAULT 'false'",
            'mrbts_php' => "ALTER TABLE `{$actual_bt}` ADD `mrbts_php` VARCHAR(10) NOT NULL DEFAULT ''",
        );

        foreach ($alter_sqls as $col_name => $alter_sql) {
            // 修复列名键（去掉 _zj 后缀）
            $check_name = $col_name;
            if ($check_name === 'mailuser_zj') $check_name = 'mailuser';

            if (!in_array(strtolower($check_name), $existing_cols, true)) {
                if (DB::query($alter_sql)) {
                    ++$r_t;
                } else {
                    ++$r_e;
                }
            }
        }

        @file_put_contents("install.lock", '安装锁');
        echo Res(1, "修复完成！成功{$r_t}项，失败{$r_e}项", array('tbl_map' => $tbl_map));
        break;

    case 'install':
        $site_name = isset($_POST['site_name']) ? trim((string)$_POST['site_name']) : '';
        $site_qq = isset($_POST['site_qq']) ? trim((string)$_POST['site_qq']) : '';
        $site_gg = isset($_POST['site_gg']) ? trim((string)$_POST['site_gg']) : '';
        $admin_user = isset($_POST['admin_user']) ? trim((string)$_POST['admin_user']) : '';
        $admin_pwd = isset($_POST['admin_pwd']) ? (string)$_POST['admin_pwd'] : '';

        if ($site_name === '' || $admin_user === '' || $admin_pwd === '') {
            exit(Res(0, '请填写站点名称、管理员账号与密码'));
        }
        if (mb_strlen($site_name) > 80) {
            exit(Res(0, '控制面板名称过长'));
        }
        if (mb_strlen($admin_user) < 3 || mb_strlen($admin_user) > 50) {
            exit(Res(0, '管理员账号长度需在 3～50 位'));
        }
        if (!preg_match('/^[a-zA-Z0-9_\x{4e00}-\x{9fa5}-]+$/u', $admin_user)) {
            exit(Res(0, '管理员账号含非法字符'));
        }
        if (strlen($admin_pwd) < 6 || strlen($admin_pwd) > 64) {
            exit(Res(0, '管理员密码长度需在 6～64 位'));
        }
        if ($site_qq !== '' && !preg_match('/^\d{5,15}$/', $site_qq)) {
            exit(Res(0, 'QQ 号格式不正确'));
        }
        if (mb_strlen($site_gg) > 2000) {
            exit(Res(0, '网站公告过长'));
        }

        include_once '../config.php';
        if (!$dbconfig['user'] || !$dbconfig['pwd'] || !$dbconfig['dbname']) {
            exit(Res(0,'请先填写好数据库并保存后再安装！',null,1));
        }
        require_once './db.class.php';
        $cn = DB::connect($dbconfig['host'], $dbconfig['user'], $dbconfig['pwd'], $dbconfig['dbname'], $dbconfig['port']);
        if (!$cn) {
            exit(Res(0, '数据库错误：' . DB::connect_error(), null, 1));
        }
        DB::query("set sql_mode = ''");
        DB::query("set names utf8");

        $install_mode = isset($_POST['install_mode']) ? (string)$_POST['install_mode'] : 'install';
        $t = 0;
        $e = 0;
        $error = '';
        $skip_sql = ($install_mode === 'skip');

        if ($install_mode === 'upgrade') {
            // 获取规范名→实际名映射
            $all_tables = array('MN_config','MN_log','MN_bt','MN_zj','MN_bs','MN_ym','MN_dd',
                'MN_monitor_task','MN_monitor_log','MN_notice_log',
                'MN_node','MN_node_task','MN_node_nonce',
                'MN_forbidden_scan','MN_forbidden_match',
                'MN_plugin','MN_plugin_option');
            $tbl_map = table_case_map($all_tables);
            $actual_config = isset($tbl_map['MN_config']) ? $tbl_map['MN_config'] : 'MN_config';
            $actual_zj     = isset($tbl_map['MN_zj']) ? $tbl_map['MN_zj'] : 'MN_zj';
            $actual_bt     = isset($tbl_map['MN_bt']) ? $tbl_map['MN_bt'] : 'MN_bt';

            // 1. 运行 V1.79→V1.81 升级脚本
            $sql = file_get_contents("upgrade_179to181.sql");
            $sql = explode(';', $sql);
            for ($i = 0; $i < count($sql); $i++) {
                $q = trim($sql[$i]);
                if ($q === '') continue;
                if (DB::query($q)) {
                    ++$t;
                } else {
                    ++$e;
                    $error .= DB::error() . '<br/>';
                }
            }
            // 2. 补齐缺失的表（跳过已存在的）
            $sql = file_get_contents("repair_tables.sql");
            $sql = explode(';', $sql);
            for ($i = 0; $i < count($sql); $i++) {
                $q = trim($sql[$i]);
                if ($q === '') continue;
                if (preg_match('/CREATE TABLE.*?`(\w+)`/', $q, $m)) {
                    if (isset($tbl_map[$m[1]])) continue;
                }
                if (DB::query($q)) {
                    ++$t;
                } else {
                    ++$e;
                    $error .= DB::error() . '<br/>';
                }
            }
            // 3. 补齐缺失字段（使用实际表名）
            $repair_cols = array(
                "ALTER TABLE `{$actual_config}` ADD `mailhost` VARCHAR(50) NULL DEFAULT NULL",
                "ALTER TABLE `{$actual_config}` ADD `mailuser` VARCHAR(50) NULL DEFAULT NULL",
                "ALTER TABLE `{$actual_config}` ADD `mailpassword` VARCHAR(50) NULL DEFAULT NULL",
                "ALTER TABLE `{$actual_config}` ADD `mailport` VARCHAR(20) NOT NULL DEFAULT '465'",
                "ALTER TABLE `{$actual_config}` ADD `ymjkkg` VARCHAR(20) NOT NULL DEFAULT 'false'",
                "ALTER TABLE `{$actual_config}` ADD `mtyxfskg` VARCHAR(20) NOT NULL DEFAULT 'false'",
                "ALTER TABLE `{$actual_config}` ADD `ymjktsyz` VARCHAR(20) NOT NULL DEFAULT '7'",
                "ALTER TABLE `{$actual_config}` ADD `wjjkkg` VARCHAR(20) NOT NULL DEFAULT 'false'",
                "ALTER TABLE `{$actual_config}` ADD `mtwjfskg` VARCHAR(50) NOT NULL DEFAULT 'false'",
                "ALTER TABLE `{$actual_config}` ADD `wjjktsyz` VARCHAR(20) NOT NULL DEFAULT '7'",
                "ALTER TABLE `{$actual_config}` ADD `optionzc` VARCHAR(20) NOT NULL DEFAULT 'stop'",
                "ALTER TABLE `{$actual_config}` ADD `zjyxbd` VARCHAR(20) NOT NULL DEFAULT 'true'",
                "ALTER TABLE `{$actual_config}` ADD `wjsckg` VARCHAR(20) NOT NULL DEFAULT 'false'",
                "ALTER TABLE `{$actual_config}` ADD `wjsccnr` TEXT NULL DEFAULT NULL",
                "ALTER TABLE `{$actual_config}` ADD `wjsckgqbfx` VARCHAR(10) NOT NULL DEFAULT 'true'",
                "ALTER TABLE `{$actual_config}` ADD `wjscml` VARCHAR(500) NOT NULL DEFAULT '/www/wwwroot'",
                "ALTER TABLE `{$actual_config}` ADD `wjstqml` TEXT NULL DEFAULT NULL",
                "ALTER TABLE `{$actual_config}` ADD `wjstqhz` TEXT NULL DEFAULT NULL",
                "ALTER TABLE `{$actual_config}` ADD `wjscdzmax` INT(11) NOT NULL DEFAULT 5242880",
                "ALTER TABLE `{$actual_config}` ADD `wjscdhmax` INT(11) NOT NULL DEFAULT 1000",
                "ALTER TABLE `{$actual_config}` ADD `wjscqzcs` VARCHAR(50) NOT NULL DEFAULT '0 3 * * *'",
                "ALTER TABLE `{$actual_config}` ADD `wjscqzcskg` VARCHAR(20) NOT NULL DEFAULT 'true'",
                "ALTER TABLE `{$actual_config}` ADD `pay_methods` TEXT NOT NULL DEFAULT ''",
                "ALTER TABLE `{$actual_zj}` ADD `backup` VARCHAR(50) NOT NULL DEFAULT '{\"max\":\"3\",\"dq\":0}'",
                "ALTER TABLE `{$actual_zj}` ADD `mailuser` VARCHAR(50) NULL DEFAULT NULL",
                "ALTER TABLE `{$actual_bt}` ADD `mrbts_php` VARCHAR(10) NOT NULL DEFAULT ''",
            );
            foreach ($repair_cols as $col_sql) {
                if (DB::query($col_sql)) {
                    ++$t;
                } else {
                    ++$e;
                }
            }
        } elseif (!$skip_sql) {
            $sql = file_get_contents("install.sql");
            $sql = explode(';', $sql);
            for ($i = 0; $i < count($sql); $i++) {
                $q = trim($sql[$i]);
                if ($q === '') continue;
                if (DB::query($q)) {
                    ++$t;
                } else {
                    ++$e;
                    $error .= DB::error() . '<br/>';
                }
            }
            if ($e != 0) {
                exit(Res(0, "安装失败！SQL成功{$t}句，失败{$e}句，请确保您的数据库版本在Mysql5.6(含)~5.7(含)之间，错误信息：" . $error));
            }
        } else {
            $exists = table_exists_case('MN_config');
            if (!$exists) {
                exit(Res(0, '未检测到已有数据表，无法跳过导入，请勾选强制重装或检查数据库'));
            }
        }

        date_default_timezone_set("PRC");
        $date = date("Y-m-d");
        $esc_user = DB::escape($admin_user);
        $esc_pwd = DB::escape($admin_pwd);
        $esc_name = DB::escape($site_name);
        $esc_qq = DB::escape($site_qq);
        $esc_gg = DB::escape($site_gg);
        $esc_date = DB::escape($date);
        $upd = DB::query("UPDATE `MN_config` SET `user`='{$esc_user}', `pwd`='{$esc_pwd}', `name`='{$esc_name}', `qqh`='{$esc_qq}', `gg`='{$esc_gg}', `date`='{$esc_date}' WHERE `id`='1'");
        if (!$upd) {
            exit(Res(0, '站点配置写入失败：' . DB::error()));
        }

        @file_put_contents("install.lock", '安装锁');
        if ($install_mode === 'upgrade') {
            exit(Res(1, '升级完成！已保留原有数据，成功更新至 V1.81'));
        }
        if ($skip_sql) {
            exit(Res(1, '安装完成（保留原表并更新站点/管理员配置）'));
        }
        exit(Res(1, '安装成功！'));
    default:
        exit(Res(0, '不存在的action'));
}
exit();


function checkfunc($f, $m = false)
{
    if (function_exists($f)) {
        return '<font color="green">可用</font>';
    } else {
        if ($m == false) {
            return '<font color="black">不支持</font>';
        } else {
            return '<font color="red">不支持</font>';
        }
    }
}

function checkclass($f, $m = false)
{
    if (class_exists($f)) {
        return '<font color="green">可用</font>';
    } else {
        if ($m == false) {
            return '<font color="black">不支持</font>';
        } else {
            return '<font color="red">不支持</font>';
        }
    }
}


function mnqz()
{
    global $mn_conf;
    $fh = file_get_contents($mn_conf['aet'] . "://" . $mn_conf['url'] . ":" . $mn_conf['port'] . "/" . $mn_conf['install_wj'] . "/xx.php");
    $f = json_decode($fh, true);
    if ($f['code_qk']) {
        return '<font color="green">正常</font>';
    } else {
        return '<font color="red">不支持</font>';
    }
}

?>


<html lang="zh-cn">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="viewport" content="initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0,user-scalable=no,minimal-ui">
    <title>MN宝塔主机系统</title>
    <link rel="stylesheet" href="../imsetes/css/bootstrap.min.css">
    <style>
        .panel {
            border: none;
            border-radius: 10px;
        }

        .panel {
            box-shadow: 1px 1px 5px 5px rgba(169, 169, 169, 0.35);
            -moz-box-shadow: 1px 1px 5px 5px rgba(169, 169, 169, 0.35);
        }
    </style>
</head>
<body background="https://ww2.sinaimg.cn/large/a15b4afegy1fpp139ax3wj200o00g073.jpg">
<nav class="navbar navbar-fixed-top navbar-default">
    <div class="container">
        <div class="navbar-header">
            <span class="navbar-brand">安装向导</span>
        </div>
    </div>
</nav>
<div class="container" style="padding-top:80px;">
    <div class="col-xs-12 col-sm-8 col-lg-6 center-block" style="float: none;">
        <?php if ($do == '0'){ ?>
        <div class="panel panel-primary">
            <div class="panel-heading" style="background: #66CCFF;">
                <h3 class="panel-title" align="center">MN宝塔主机系统</h3>
            </div>
            <div class="panel-body">
                <center>
                    <div class="alert alert-success"><a href="https://mf.mengnai.top" target="_blank"><img
                                    class="img-circle m-b-xs"
                                    style="border: 2px solid #1281FF; margin-left:3px; margin-right:3px;"
                                    src="https://q4.qlogo.cn/headimg_dl?dst_uin=3108807898&spec=100" ; width="60px"
                                    height="60px" alt="<?php echo $conf['sitename']; ?>"><br></a>欢迎使用由梦奈基于光年V3框架原创的MN宝塔主机系统(简称MNBT)！本系统免费发布于网络！</br>
                        官网：<a href="http://mf.mengnai.top/" target="_blank">mf.mengnai.top</a><br>未经作者同意严禁任何形式的二次开发及引用！<br><small>2023by:梦奈</br>
                            系统版本：V1.6</small>
                    </div>
                </center>
                <?php if ($installed) { ?>
                    <div class="alert alert-warning">您已经安装过，如需重新安装请<font
                                color=red>从官网重新下载源码</font>文件后再安装！
                    </div>
                <?php }else{ ?>
                <input type="checkbox" name="gxk" id="eei" value="Car" onclick="eey()"/>勾选则代表您同意遵守<a
                        href='../xy.html' target="_blank"/>MN系统使用协议</a>
                    <p align="center"><a class="btn btn-primary" id="abq" style="opacity: 0.2"
                                         href="javascript:return false;">开始安装</a></p>
                    <script type="text/javascript">
                        function eey() {
                            let $xz = document.getElementById("abq");
                            let vio = $xz.href;
                            if (vio === 'javascript:return false;') {
                                $xz.style = '';
                                $xz.href = 'index.php?do=1';
                            } else {
                                alert('若想安装本系统请自觉阅读并勾选《MN系统使用协议》');
                                $xz.style = 'opacity: 0.2';
                                $xz.href = 'javascript:return false;';
                            }
                        }
                    </script>
                <?php } ?>
            </div>
        </div>
    </div>
</div>
<?php } elseif ($do == '1') { ?>
    <div class="panel panel-primary">
        <div class="panel-heading" style="background: #66CCFF;">
            <h3 class="panel-title" align="center">环境检查</h3>
        </div>
        <div class="progress progress-striped">
            <div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="40" aria-valuemin="0"
                 aria-valuemax="100" style="width: 10%">
                <span class="sr-only">10%</span>
            </div>
        </div>
        <table class="table table-striped">
            <thead>
            <tr>
                <th style="width:20%">函数检测</th>
                <th style="width:15%">需求</th>
                <th style="width:15%">当前</th>
                <th style="width:50%">用途</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td>PHP 7.4 ~ 7.6</td>
                <td>必须</td>
                <td><?php $php_vs = version_compare(PHP_VERSION, '7.4.0', '>') && version_compare(PHP_VERSION, '7.7.0', '<');
                    echo $php_vs ? '<font color="green" id="server-code">' . PHP_VERSION . '</font>' : '<font color="red" id="server-code">' . PHP_VERSION . '</font>'; ?></td>
                <td>PHP版本支持</td>
            </tr>
            <tr>
                <td>curl_exec()</td>
                <td>必须</td>
                <td><?php echo checkfunc('curl_exec', true); ?></td>
                <td>抓取网页</td>
            </tr>
            <tr>
                <td>file_get_contents()</td>
                <td>必须</td>
                <td><?php echo checkfunc('file_get_contents', true); ?></td>
                <td>读取文件</td>
            </tr>
            <tr>
                <td>MN更新支持</td>
                <td>非必须</td>
                <td><?php echo mnqz(); ?></td>
                <td>获取MN更新</td>
            </tr>
            </tbody>
        </table>
        <p><span><a class="btn btn-primary" href="index.php?do=0"><<上一步</a></span>
            <span style="float:right"><a class="btn btn-primary" id="next" href="index.php?do=2"
                                         align="right">下一步>></a></span></p>
    </div>
    <script>
        let php_ves = Number(document.getElementById('server-code').innerText.substring(0, 3));
        let next = document.getElementById('next')
        if (php_ves < 7.4 || php_ves > 7.6) {
            next.style = 'opacity: 0.2';
            next.href = 'javascript:void(0)';
        }

    </script>
<?php } elseif ($do == '2') { ?>
    <div class="panel panel-primary">
        <div class="panel-heading" style="background: #66CCFF;">
            <h3 class="panel-title" align="center">数据库配置</h3>
        </div>
        <div class="progress progress-striped">
            <div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="40" aria-valuemin="0"
                 aria-valuemax="100" style="width: 30%">
                <span class="sr-only">30%</span>
            </div>
        </div>
        <div class="panel-body">
            <?php
            echo <<<HTML
		<form action="?do=3" class="form-sign" method="post">
		<label for="name">数据库地址:</label>
		<input type="text" class="form-control" name="db_host" value="localhost">
		<label for="name">数据库端口:</label>
		<input type="text" class="form-control" name="db_port" value="3306">
		<label for="name">数据库用户名:</label>
		<input type="text" class="form-control" name="db_user">
		<label for="name">数据库名:</label>
		<input type="text" class="form-control" name="db_name">
		<label for="name">数据库密码:</label>
		<input type="text" class="form-control" name="db_pwd">
		<br><input type="submit" class="btn btn-primary btn-block" name="submit" value="保存配置">
		</form>
HTML;
            ?>
        </div>
    </div>
<?php } elseif ($do == '3') {
    ?>
    <div class="panel panel-primary">
        <div class="panel-heading" style="background: #66CCFF;">
            <h3 class="panel-title" align="center">保存配置</h3>
        </div>
        <div class="progress progress-striped">
            <div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="40" aria-valuemin="0"
                 aria-valuemax="100" style="width: 50%">
                <span class="sr-only">50%</span>
            </div>
        </div>
        <div class="panel-body">
            <?php
            require './db.class.php';
            $db_host = isset($_POST['db_host']) ? $_POST['db_host'] : NULL;
            $db_port = isset($_POST['db_port']) ? $_POST['db_port'] : NULL;
            $db_user = isset($_POST['db_user']) ? $_POST['db_user'] : NULL;
            $db_pwd = isset($_POST['db_pwd']) ? $_POST['db_pwd'] : NULL;
            $db_name = isset($_POST['db_name']) ? $_POST['db_name'] : NULL;
            if ($db_host == null || $db_port == null || $db_user == null || $db_pwd == null || $db_name == null) {
                echo '<div class="alert alert-danger">保存错误,请确保每项都不为空<hr/><a href="javascript:history.back(-1)"><< 返回上一页</a></div>';
                exit;
            }
            $posj = send_post();
            if ($posj['code'] == '0') {
                echo '<div class="alert alert-danger">' . $posj['msg'] . '<hr/><a href="javascript:history.back(-1)"><< 返回上一页</a></div>';
            } else {
                $config = "<?php
/*数据库配置*/
\$dbconfig=array(
	'host' => '{$db_host}', //数据库服务器
	'port' => {$db_port}, //数据库端口
	'user' => '{$db_user}', //数据库用户名
	'pwd' => '{$db_pwd}', //数据库密码
	'dbname' => '{$db_name}', //数据库名
);
?>";
                $sqwj = "<?php 
\$authcode='{$posj["authcode"]}';
?>";
                if (!$con = DB::connect($db_host, $db_user, $db_pwd, $db_name, $db_port)) {
                    if (DB::connect_errno() == 2002)
                        echo '<div class="alert alert-warning">连接数据库失败，数据库地址填写错误！</div>';
                    elseif (DB::connect_errno() == 1045)
                        echo '<div class="alert alert-warning">连接数据库失败，数据库用户名或密码填写错误！</div>';
                    elseif (DB::connect_errno() == 1049)
                        echo '<div class="alert alert-warning">连接数据库失败，数据库名不存在！</div>';
                    else
                        echo '<div class="alert alert-warning">连接数据库失败，[' . DB::connect_errno() . ']' . DB::connect_error() . '</div>';
                } elseif (file_put_contents('../config.php', $config) && file_put_contents('../MPHX/SQ.php', $sqwj)) {
                    echo '<div class="alert alert-success">数据库配置文件保存成功！</div>';
                    if (DB::query("select * from MN_config where 1") == FALSE)
                        echo '<p align="right"><a class="btn btn-primary btn-block" href="?do=4">创建数据表>></a></p>';
                    else
                        echo '<div class="list-group-item list-group-item-info">系统检测到你已安装过MN宝塔主机系统</div>
				<div class="list-group-item">
					<a href="?do=6" class="btn btn-block btn-primary">跳过安装</a>
				</div>
				<div class="list-group-item">
					<a href="?do=4" onclick="if(!confirm(\'全新安装将会清空所有数据，是否继续？\')){return false;}" class="btn btn-block btn-warning">强制全新安装</a>
				</div>';
                } else
                    echo '<div class="alert alert-danger">保存失败，请确保网站根目录有写入权限<hr/><a href="javascript:history.back(-1)"><< 返回上一页</a></div>';
            }
            ?>
        </div>
    </div>
<?php } elseif ($do == '4') { ?>
    <div class="panel panel-primary">
        <div class="panel-heading" style="background: #66CCFF;">
            <h3 class="panel-title" align="center">创建数据表</h3>
        </div>
        <div class="progress progress-striped">
            <div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="40" aria-valuemin="0"
                 aria-valuemax="100" style="width: 70%">
                <span class="sr-only">70%</span>
            </div>
        </div>
        <div class="panel-body">
            <?php
            include_once '../config.php';
            if (!$dbconfig['user'] || !$dbconfig['pwd'] || !$dbconfig['dbname']) {
                echo '<div class="alert alert-danger">请先填写好数据库并保存后再安装！<hr/><a href="javascript:history.back(-1)"><< 返回上一页</a></div>';
            } else {
                require './db.class.php';
                $sql = file_get_contents("install.sql");
                $sql = explode(';', $sql);
                $cn = DB::connect($dbconfig['host'], $dbconfig['user'], $dbconfig['pwd'], $dbconfig['dbname'], $dbconfig['port']);
                if (!$cn) die('err:' . DB::connect_error());
                DB::query("set sql_mode = ''");
                DB::query("set names utf8");
                $t = 0;
                $e = 0;
                $error = '';
                for ($i = 0; $i < count($sql); $i++) {
                    $q = trim($sql[$i]);
                    if ($q === '') continue;
                    if (DB::query($q)) {
                        ++$t;
                    } else {
                        ++$e;
                        $error .= DB::error() . '<br/>';
                    }
                }
                date_default_timezone_set("PRC");
                $date = date("Y-m-d");
                DB::query("update `MN_config` set `date` ='$date'  where `id`='1'");
            }
            $esew = 0;
            if ($e == 0) {
                echo '<div class="alert alert-success">安装成功！<br/>SQL成功' . $t . '句/失败' . $e . '句</div><p align="right"><a class="btn btn-block btn-primary" href="index.php?do=5">下一步>></a></p>';
            } else {
                echo '<div class="alert alert-success">安装成功！<br/>SQL成功' . $t . '句/失败' . $esew . '句</div><p align="right"><a class="btn btn-block btn-primary" href="index.php?do=5">下一步>></a></p>';
            }
            ?>
        </div>
    </div>

<?php } elseif ($do == '5') { ?>
    <div class="panel panel-primary">
        <div class="panel-heading" style="background: #66CCFF;">
            <h3 class="panel-title" align="center">安装完成</h3>
        </div>
        <div class="progress progress-striped">
            <div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="40" aria-valuemin="0"
                 aria-valuemax="100" style="width: 100%">
                <span class="sr-only">100%</span>
            </div>
        </div>
        <div class="panel-body">
            <?php
            @file_put_contents("install.lock", '安装锁');
            echo '<div class="alert alert-success">安装完成！管理账号和密码是:admin/123456</font><br/><br/><a href="../user/">>>控制面板</a>｜<a href="../admin/">>>后台管理</a><hr/>更多设置选项请登录后台管理进行修改。<br/><br/><font color="#FF0033">如果你的空间不支持本地文件读写，请自行删除install文件夹！</font></div>';
            unlink('index.php');
            unlink('install.lock');
            unlink('install.sql');
            unlink('db.class.php');
            @rmdir('../install/');
            ?>
        </div>
    </div>

<?php } elseif ($do == '6') { ?>
    <div class="panel panel-primary">
        <div class="panel-heading" style="background: #66CCFF;">
            <h3 class="panel-title" align="center">安装完成</h3>
        </div>
        <div class="progress progress-striped">
            <div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="40" aria-valuemin="0"
                 aria-valuemax="100" style="width: 100%">
                <span class="sr-only">100%</span>
            </div>
        </div>
        <div class="panel-body">
            <?php
            @file_put_contents("install.lock", '安装锁');
            echo '<div class="alert alert-success">安装完成！管理账号和密码为原账号密码如果忘记请进入数据库MN_config表查看账号(user)密码(pwd)</font><br/><br/><a href="../user/">>>控制面板</a>｜<a href="../admin/">>>后台管理</a><hr/>更多设置选项请登录后台管理进行修改。<br/><br/><font color="#FF0033">如果你的空间不支持本地文件读写，请自行删除install文件夹！</font></div>';
            unlink('index.php');
            unlink('install.lock');
            unlink('install.sql');
            unlink('db.class.php');
            @rmdir('../install/');
            ?>
        </div>
    </div>

<?php } ?>

</div>
</body>
</html>