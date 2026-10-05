<?php
if(!defined('IN_CRONLITE'))exit();

$my=isset($_GET['my'])?$_GET['my']:null;

$clientip=$_SERVER['REMOTE_ADDR'];

if(isset($_COOKIE["admin_token"]))
{
	$token=authcode(daddslashes($_COOKIE['admin_token']), 'DECODE', SYS_KEY);
	list($user, $sid) = explode("\t", $token);
	$session=md5($conf['user'].$conf['pwd'].$password_hash);
	if($session==$sid) {
		$islogin=1;
	}
}

// 面板关闭/主机到期/封禁提示只应阻断用户面板页面本身。
// 登录页、验证码、登录与注销请求以及管理端请求不能被旧的用户登录态阻断，
// 否则主机到期后用户无法注销或切换账号，同浏览器下管理员也会被一并挡住。
$td_script_path = str_replace('\\', '/', strtolower($_SERVER['SCRIPT_NAME'] ?? ''));
$td_auth_bypass = strpos($td_script_path, '/admin/') !== false
	|| in_array(basename($td_script_path), ['login.php', 'code.php'], true)
	|| (($_POST['gn'] ?? '') === 'login')
	|| isset($_POST['logout']);

if(isset($_COOKIE["user_token"]) && !$td_auth_bypass)
{
if($conf['kzmbqk']=='false'){sysmsg('控制面板已经被关闭详细请联系站长QQ'.$conf['qqh']);}
	$token=authcode(daddslashes($_COOKIE['user_token']), 'DECODE', SYS_KEY);
	$td_token_parts = explode("\t", (string)$token);
	$user = $td_token_parts[0] ?? '';
	$sid = $td_token_parts[1] ?? '';
	$yhc = $DB->get_row_prepare("SELECT * FROM MN_zj WHERE user=? limit 1", [$user]);
	$session=is_array($yhc) ? md5($yhc['user'].$yhc['pass'].$password_hash) : '';
	if(is_array($yhc) && $session==$sid) {
		$islogins=1;
		$yhid=$yhc['id'];
		$zjid=$yhc['btid'];
		$ssbt=$yhc['ssbt'];
		if($conf['kzmbqk']=='false'){sysmsg('控制面板已经关闭！详细请联系站长QQ'.$conf['qqh']);}
		// 到期不再清除登录态（不强退），改为展示专门的到期提示页：保留 cookie，续费后刷新即可恢复使用
		if(strtotime($date)-strtotime($yhc['datae'])>0 && $yhc['datae']!='0000-00-00'){sysmsg('您的主机已到期，相关功能已暂停使用！<br/>请联系站长续费（QQ：'.$conf['qqh'].'），续费成功后刷新本页即可继续使用。');}
		if($yhc['qk']=='false'){
			sysmsg('您已被禁止登陆！详细请联系站长QQ'.$conf['qqh']);
		}
	}
}
?>