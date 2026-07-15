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
                'timeout' => 8 // 超时时间（单位:s）
            )
        );
        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        return json_decode($result, true);
    } catch (Exception $e) {
        return false;
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
        }else if(file_put_contents('../config.php',$config) && file_put_contents('../MPHX/SQ.php',$mnAuthcode))
            echo Res(1, '数据库信息保存成功',['in_table'=>!!DB::query("select * from MN_config where 1")]);
        else echo Res(0, '数据库信息保存失败，请检查系统是否有站点目录的读写权限');
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
        require './db.class.php';
        $cn = DB::connect($dbconfig['host'], $dbconfig['user'], $dbconfig['pwd'], $dbconfig['dbname'], $dbconfig['port']);
        if (!$cn) {
            exit(Res(0, '数据库错误：' . DB::connect_error(), null, 1));
        }
        DB::query("set sql_mode = ''");
        DB::query("set names utf8");

        $skip_sql = isset($_POST['is_install']) && (string)$_POST['is_install'] === 'false';
        $t = 0;
        $e = 0;
        $error = '';

        if (!$skip_sql) {
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
            $exists = DB::query("select * from MN_config where 1");
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