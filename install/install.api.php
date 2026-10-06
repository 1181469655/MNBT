<?php
error_reporting(0);
@header('Content-Type: text/html; charset=UTF-8');
define('IN_CRONLITE', true);
include("../cf_up.php");
include("../MPHX/BL.php");
include_once("../MPHX/Response.php");
$action = $_GET['action'] ?? 'index';
$vs = mnbt_version_num();

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

// 读取表的全部列名（小写），表不存在时返回空数组
function mnbt_table_columns($table) {
    $cols = array();
    if (!table_exists_case($table)) return $cols;
    $result = DB::query("SHOW COLUMNS FROM `{$table}`");
    if ($result) {
        while ($row = DB::fetch($result)) {
            $cols[] = strtolower($row['Field']);
        }
    }
    return $cols;
}

// 标准表清单（含 V1.83 Docker 集成四表、V1.85 迁移游标表 MN_dbver）
function mnbt_standard_tables() {
    return array('MN_config','MN_log','MN_bt','MN_zj','MN_bs','MN_ym','MN_dd',
        'MN_monitor_task','MN_monitor_log','MN_notice_log',
        'MN_node','MN_node_task','MN_node_nonce',
        'MN_forbidden_scan','MN_forbidden_match',
        'MN_plugin','MN_plugin_option',
        'MN_docker_node','MN_docker_user','MN_docker_plan','MN_docker_order','MN_dbver');
}

// 各版本增量字段定义（随发版追加，勿在此写死当前版本号）：表 => (列名 => ALTER 语句)
// 实际表名由调用方传入（兼容大小写迁移场景）；仅添加缺失列
function mnbt_upgrade_columns($actual_config, $actual_zj, $actual_bt, $actual_dplan, $actual_duser) {
    return array(
        // V1.81 修复字段
        $actual_config => array(
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
            // V1.85 对外 API 协议模式开关（0=1.83+ 严格，1=1.81 兼容）
            'api_compat' => "ALTER TABLE `{$actual_config}` ADD `api_compat` varchar(10) NOT NULL DEFAULT '0'",
        ),
        $actual_zj => array(
            'backup' => "ALTER TABLE `{$actual_zj}` ADD `backup` VARCHAR(50) NOT NULL DEFAULT '{\"max\":\"3\",\"dq\":0}'",
            'mailuser' => "ALTER TABLE `{$actual_zj}` ADD `mailuser` VARCHAR(50) NULL DEFAULT NULL",
        ),
        $actual_bt => array(
            'ftpdz' => "ALTER TABLE `{$actual_bt}` ADD `ftpdz` VARCHAR(50) NOT NULL DEFAULT 'false'",
            'mrbts_php' => "ALTER TABLE `{$actual_bt}` ADD `mrbts_php` VARCHAR(10) NOT NULL DEFAULT ''",
        ),
        // V1.83.1 Docker 磁盘配额字段（表已在 repair_tables.sql 中补齐）
        $actual_dplan => array(
            'disk_max' => "ALTER TABLE `{$actual_dplan}` ADD `disk_max` varchar(20) NOT NULL DEFAULT '0' COMMENT '磁盘配额 MB 上限（0=不限制）'",
            'proxy_max' => "ALTER TABLE `{$actual_dplan}` ADD `proxy_max` varchar(20) NOT NULL DEFAULT '0' COMMENT '反向代理数量上限（0=不限制）'",
        ),
        $actual_duser => array(
            'disk_usage' => "ALTER TABLE `{$actual_duser}` ADD `disk_usage` bigint(20) NOT NULL DEFAULT '0' COMMENT '最近磁盘用量（字节）'",
            'disk_usage_at' => "ALTER TABLE `{$actual_duser}` ADD `disk_usage_at` varchar(50) DEFAULT NULL COMMENT '磁盘用量采集时间'",
        ),
    );
}

if (file_exists('install.lock')) exit(Res(1, '已安装', ['vs' => $vs,'is_install'=>true], 1));

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
        // 数据库配置用 var_export 写入，杜绝 POST 原文拼接进 PHP 源码的注入风险
        $dbconfig_out = array(
            'host' => (string)$db_host,     //数据库服务器
            'port' => (int)$db_port,        //数据库端口
            'user' => (string)$db_user,     //数据库用户名
            'pwd'  => (string)$db_pwd,      //数据库密码
            'dbname' => (string)$db_name,   //数据库名
        );
        $config = "<?php\n" . '/*数据库配置*/' . "\n" . '$dbconfig=' . var_export($dbconfig_out, true) . ";\n";
        if(!$con=DB::connect($db_host,$db_user,$db_pwd,$db_name,$db_port)){
            $enumMsg=[
                2002=>'连接数据库失败，数据库地址填写错误！',
                1045=>'连接数据库失败，数据库用户名或密码填写错误！',
                1049=>'连接数据库失败，数据库名不存在！',
            ];
            exit(Res(0,$enumMsg[DB::connect_errno()]??'['.DB::connect_errno().']'.DB::connect_error()));
        }else{
            $cfgw = file_put_contents('../config.php', $config);
            if ($cfgw !== false) {
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
                if (!is_writable('..')) $err_detail .= '上级目录不可写；';
                if (file_exists('../config.php') && !is_writable('../config.php')) $err_detail .= 'config.php存在但不可覆盖；';
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
            $all_tables = mnbt_standard_tables();
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

            // 检查 V1.85 新增字段（对外 API 协议模式开关）
            $col_api_compat = DB::get_row("SHOW COLUMNS FROM `MN_config` LIKE 'api_compat'");
            if (!$col_api_compat) {
                $result['need_upgrade'] = true;
                $result['missing_columns'][] = 'MN_config.api_compat';
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
        $tbl_map = table_case_map(mnbt_standard_tables());

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

        // 2. 补齐字段（使用实际表名，仅添加缺失列）
        $actual_config = isset($tbl_map['MN_config']) ? $tbl_map['MN_config'] : 'MN_config';
        $actual_zj     = isset($tbl_map['MN_zj']) ? $tbl_map['MN_zj'] : 'MN_zj';
        $actual_bt     = isset($tbl_map['MN_bt']) ? $tbl_map['MN_bt'] : 'MN_bt';
        $actual_dplan  = isset($tbl_map['MN_docker_plan']) ? $tbl_map['MN_docker_plan'] : 'MN_docker_plan';
        $actual_duser  = isset($tbl_map['MN_docker_user']) ? $tbl_map['MN_docker_user'] : 'MN_docker_user';

        $alter_sqls = mnbt_upgrade_columns($actual_config, $actual_zj, $actual_bt, $actual_dplan, $actual_duser);

        foreach ($alter_sqls as $table_name => $cols) {
            $existing_cols = mnbt_table_columns($table_name);
            if (empty($existing_cols)) continue; // 表不存在，repair_tables 已尝试创建
            foreach ($cols as $col_name => $alter_sql) {
                if (!in_array(strtolower($col_name), $existing_cols, true)) {
                    if (DB::query($alter_sql)) {
                        ++$r_t;
                    } else {
                        ++$r_e;
                    }
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
            // 获取规范名→实际名映射（含 V1.83 Docker 集成四表）
            $tbl_map = table_case_map(mnbt_standard_tables());
            $actual_config = isset($tbl_map['MN_config']) ? $tbl_map['MN_config'] : 'MN_config';
            $actual_zj     = isset($tbl_map['MN_zj']) ? $tbl_map['MN_zj'] : 'MN_zj';
            $actual_bt     = isset($tbl_map['MN_bt']) ? $tbl_map['MN_bt'] : 'MN_bt';
            $actual_dplan  = isset($tbl_map['MN_docker_plan']) ? $tbl_map['MN_docker_plan'] : 'MN_docker_plan';
            $actual_duser  = isset($tbl_map['MN_docker_user']) ? $tbl_map['MN_docker_user'] : 'MN_docker_user';

            // 1. 运行 V1.79→V1.81 升级脚本（脚本已幂等化，1.81 库上重跑安全）
            $upgrade_sql_file = __DIR__ . '/1.79To1.81.sql';
            if (file_exists($upgrade_sql_file)) {
                $sql = file_get_contents($upgrade_sql_file);
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
            }
            // 2. 补齐缺失的表（含 V1.83 Docker 四表，跳过已存在的）
            $sql = file_get_contents(__DIR__ . '/repair_tables.sql');
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
            // 3. 补齐缺失字段（V1.81 修复字段 + V1.83 Docker 配额字段 + V1.84 主页字段，判重后添加）
            $alter_sqls = mnbt_upgrade_columns($actual_config, $actual_zj, $actual_bt, $actual_dplan, $actual_duser);
            foreach ($alter_sqls as $table_name => $cols) {
                $existing_cols = mnbt_table_columns($table_name);
                if (empty($existing_cols)) continue; // 表不存在，第 2 步已尝试创建
                foreach ($cols as $col_name => $alter_sql) {
                    if (!in_array(strtolower($col_name), $existing_cols, true)) {
                        if (DB::query($alter_sql)) {
                            ++$t;
                        } else {
                            ++$e;
                            $error .= DB::error() . '<br/>';
                        }
                    }
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
                exit(Res(0, "安装失败！SQL成功{$t}句，失败{$e}句，本系统支持 MySQL 5.6 ~ 8.x，请检查数据库账号是否有建表权限（SQL 报错已列出失败语句），错误信息：" . $error));
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
        // V1.87：安装即存 bcrypt 哈希，不落明文（首次登录无需再迁移）
        $esc_pwd = DB::escape(password_hash($admin_pwd, PASSWORD_DEFAULT));
        $esc_name = DB::escape($site_name);
        $esc_qq = DB::escape($site_qq);
        $esc_gg = DB::escape($site_gg);
        $esc_date = DB::escape($date);
        $upd = DB::query("UPDATE `MN_config` SET `user`='{$esc_user}', `pwd`='{$esc_pwd}', `name`='{$esc_name}', `qqh`='{$esc_qq}', `gg`='{$esc_gg}', `date`='{$esc_date}' WHERE `id`='1'");
        if (!$upd) {
            exit(Res(0, '站点配置写入失败：' . DB::error()));
        }

        // V1.85 迁移链基线播种：全新安装 / 覆盖升级已把库结构带到当前版本，
        // 据此把游标 MN_dbver 抬到 $WEBQB，之后在线更新只跑更高版本的增量、不重放历史迁移。
        // skip 模式（只改配置不动表结构）库版本未知，故不播种；播种失败也不阻断安装。
        if ($install_mode !== 'skip') {
            include_once __DIR__ . '/../MPHX/migrations.php';
            if (function_exists('mnbt_migrations_seed')) {
                $seed_err = '';
                mnbt_migrations_seed($dbconfig, (int)$WEBQB, $seed_err);
            }
        }

        @file_put_contents("install.lock", '安装锁');
        if ($install_mode === 'upgrade') {
            exit(Res(1, '升级完成！已保留原有数据，成功更新至 ' . mnbt_version()));

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

