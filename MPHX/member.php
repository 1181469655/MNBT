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

if(isset($_COOKIE["user_token"]))
{
if($conf['kzmbqk']=='false'){sysmsg('控制面板已经被关闭详细请联系站长QQ'.$conf['qqh']);}
	$token=authcode(daddslashes($_COOKIE['user_token']), 'DECODE', SYS_KEY);
	list($user, $sid) = explode("\t", $token);
	$yhc = $DB->get_row_prepare("SELECT * FROM MN_zj WHERE user=? limit 1", [$user]);
	$session=md5($yhc['user'].$yhc['pass'].$password_hash);
	if($session==$sid) {
		$islogins=1;
		$yhid=$yhc['id'];
		$zjid=$yhc['btid'];
		$ssbt=$yhc['ssbt'];                     
		if($conf['kzmbqk']=='false'){sysmsg('控制面板已经关闭！详细请联系站长QQ'.$conf['qqh']);}
		if(strtotime($date)-strtotime($yhc['datae'])>0 && $yhc['datae']!='0000-00-00'){mnbt_set_auth_cookie("user_token", "", time() - 604800);sysmsg('您已到期！已经帮您自动退出登陆！刷新即可重新登录');}
		if($yhc['qk']=='false'){
			sysmsg('您已被禁止登陆！详细请联系站长QQ'.$conf['qqh']);
		}
	}
}
?>