<?php
if (defined('SYSTEM_ROOT')) return;
if (!defined('IN_CRONLITE')) define('IN_CRONLITE', true);
define('SYSTEM_ROOT', dirname(__FILE__).'/');
define('ROOT', dirname(SYSTEM_ROOT).'/');

// 生产环境：页面不显示错误，但写入本地错误日志，便于排查问题。
ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
ini_set('log_errors', '1');
$runtimeLogDir = ROOT . 'runtime/logs';
if (!is_dir($runtimeLogDir)) {
	@mkdir($runtimeLogDir, 0755, true);
}
$errorLogPath = getenv('MNBT_ERROR_LOG');
if (!$errorLogPath) {
	$errorLogPath = $runtimeLogDir . '/php-error.log';
}
ini_set('error_log', $errorLogPath);
error_reporting(E_ALL);

// 未捕获异常兜底：细节只进日志，页面/AJAX 拿到的是一句话。
// 例外是密钥类错误（MNBT_SecretException）——它的消息就是运维的恢复步骤，必须原文回显。
// 没有这层兜底时 display_errors=0 会直接吐空白 500，运维看不到任何原因。
set_exception_handler(function ($e) {
	error_log('[MNBT] 未捕获异常 ' . get_class($e) . ': ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());
	if (headers_sent()) return;
	$msg = ($e instanceof MNBT_SecretException)
		? $e->getMessage()
		: '服务器内部错误，请稍后重试；持续出现请查看 runtime/logs/php-error.log';
	$is_ajax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) || !empty($_POST['gn']) || !empty($_GET['gn']) || !empty($_REQUEST['egn']);
	http_response_code(500);
	if ($is_ajax) {
		header('Content-Type: application/json; charset=utf-8');
		echo json_encode(['success' => false, 'qk' => 4, 'code' => $msg, 'msg' => $msg], JSON_UNESCAPED_UNICODE);
	} else {
		echo '<!doctype html><meta charset="utf-8"><title>服务器错误</title><p>' . htmlspecialchars($msg) . '</p>';
	}
});

define('SYS_KEY', 'MNBT');
define('CC_Defender', 1); //防CC攻击开关(1为session模式)
date_default_timezone_set("PRC");
$date = date("Y-m-d H:i:s");
include_once(SYSTEM_ROOT."function.php");
$sessionSecure = mnbt_request_is_secure();
@ini_set('session.use_strict_mode', '1');
@ini_set('session.cookie_httponly', '1');
@ini_set('session.cookie_samesite', 'Lax');
// php2shell缓解(CVE-2026-109618/CVE-2026-109619)：关闭会话上传进度，仅缓解，仍需升级PHP
@ini_set('session.upload_progress.enabled', '0');
if (PHP_VERSION_ID >= 70300) {
	session_set_cookie_params(['lifetime' => 0, 'path' => '/', 'secure' => $sessionSecure, 'httponly' => true, 'samesite' => 'Lax']);
} else {
	session_set_cookie_params(0, '/; samesite=Lax', '', $sessionSecure, true);
}
session_start();
// 提前初始化 CSRF token 并下发双提交 Cookie，确保即使页面未注入 meta 也能通过 cookie 兜底校验
mnbt_csrf_token();
$scriptpath=str_replace('\\','/',$_SERVER['SCRIPT_NAME']);
$sitepath = substr($scriptpath, 0, strrpos($scriptpath, '/'));
$siteurl = ($_SERVER['SERVER_PORT'] == '443' ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].$sitepath.'/';
$docRoot = rtrim(str_replace('\\', '/', isset($_SERVER['DOCUMENT_ROOT']) ? realpath($_SERVER['DOCUMENT_ROOT']) : ''), '/');
$projFs = rtrim(str_replace('\\', '/', realpath(ROOT)), '/');
$siteWebRoot = ($docRoot && strpos($projFs, $docRoot) === 0) ? rtrim(substr($projFs, strlen($docRoot)), '/') . '/' : '/';
$install_url = ($_SERVER['SERVER_PORT'] == '443' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $siteWebRoot . 'install';
if(is_file(SYSTEM_ROOT.'360safe/360webscan.php')){//360网站卫士
require_once(SYSTEM_ROOT.'360safe/360webscan.php');
}
if (!is_file(ROOT.'config.php')) {
	header("Location: " . $install_url);
	exit();
}
require ROOT.'config.php';
if(!defined('SQLITE') && (!$dbconfig['user']||!$dbconfig['pwd']||!$dbconfig['dbname']))//检测安装
{
header("Location: " . $install_url);
exit();
}
//连接数据库
include_once(SYSTEM_ROOT."db.class.php");
$DB=new DB($dbconfig['host'],$dbconfig['user'],$dbconfig['pwd'],$dbconfig['dbname'],$dbconfig['port']);
if(!$DB->get_all_prepare("select * from MN_config where 1"))//检测安装2
{
header("Location: " . $install_url);
exit();
}
	
$siteid=1;
$conf=$DB->get_row_prepare("SELECT * FROM MN_config WHERE id=? limit 1", [$siteid]);//获取系统配置
$password_hash='!@#%!s!0';
include_once(SYSTEM_ROOT."secret.php");
$conf['mailpassword']=mnbt_secret_decrypt($conf['mailpassword'] ?? '');//库内邮箱授权码为密文，读入统一还原
	include_once(SYSTEM_ROOT."Response.php");
include_once(SYSTEM_ROOT."function.php");
include_once(SYSTEM_ROOT."member.php");
include_once(SYSTEM_ROOT."theme.php");
include_once(SYSTEM_ROOT."plugin.php");
require_once(SYSTEM_ROOT."lib/pay.function.php");
mnbt_plugins_boot();
?>