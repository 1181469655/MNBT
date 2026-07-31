<?php
if($egn=='login') {
	if(isset($_POST['user']) && isset($_POST['pass'])) {
		$user=daddslashes($_POST['user']);
		$pass=daddslashes($_POST['pass']);
		$code=daddslashes($_POST['code']);
		if(strpos($user,'"') || strpos($user,"'") || strpos($user,',') || strpos($user,'/') || strpos($user,"\\"))exit('{"code":"账号不能包含危险字符！"}');
		$wedsv=$DB->get_row_prepare("SELECT * FROM MN_zj WHERE user=? limit 1", [$user]);
		if ($conf['yzme']!='false' && $code != $_SESSION['authcode']) {
			unset($_SESSION['authcode']);
			@header('Content-Type: text/html; charset=UTF-8');
			exit('{"code":"验证码错误！"}');
		} elseif($user==$wedsv['user'] && $pass==$wedsv['pass']) {
			unset($_SESSION['authcode']);
			$session=md5($user.$pass.$password_hash);
			$token=authcode("{$user}\t{$session}", 'ENCODE', SYS_KEY);
			mnbt_rotate_login_session();
			mnbt_set_auth_cookie("user_token", $token, time() + 604800);
			@header('Content-Type: text/html; charset=UTF-8');
			json_exit('登陆成功');
		} else {
			@header('Content-Type: text/html; charset=UTF-8');
			exit('{"code":"用户不存在或密码错误！"}');
		}
	} elseif(isset($_POST['logout'])) {
		mnbt_set_auth_cookie("user_token", "", time() - 604800);
		@header('Content-Type: text/html; charset=UTF-8');
		exit('{"code":"您已成功注销本次登陆！"}');
	}
	return;
}
