<?php
include("../MPHX/common.php");
mnbt_set_auth_cookie("user_token", "", time() - 604800);
@header('Content-Type: text/html; charset=UTF-8');
$egn=$_REQUEST['gn'];
if($conf['yzme']=='true')exit("<script language='javascript'>alert('后台已经开启控制面板验证码登陆无法进行一键登录！');window.location.href='./login.php';</script>");
?>
<?php
if($egn=='logine'){
if(isset($_REQUEST['username']) && isset($_REQUEST['password'])){
	$user=daddslashes($_REQUEST['username']);
	$pass=daddslashes($_REQUEST['password']);
	if(strpos($user,'"') || strpos($user,"'") || strpos($user,',') || strpos($user,'/') || strpos($user,"\\"))exit('{"code":"账号不能包含危险字符！"}');
	$wedsv=$DB->get_row_prepare("SELECT * FROM MN_zj WHERE user=? limit 1", [$user]);
	if($user==$wedsv['user'] && $pass==$wedsv['pass']) {
		unset($_SESSION['authcode']);
		$session=md5($user.$pass.$password_hash);
		$token=authcode("{$user}\t{$session}", 'ENCODE', SYS_KEY);
		mnbt_rotate_login_session();
		mnbt_set_auth_cookie("user_token", $token, time() + 604800);
		@header('Content-Type: text/html; charset=UTF-8');
        header("Location:index.php");
}else{
		@header('Content-Type: text/html; charset=UTF-8');
		exit('用户名或密码错误！');
	    }
}
}elseif($egn='xz'){
header("Location:login.php");
}else{
exit('错误代码-404');}
?>