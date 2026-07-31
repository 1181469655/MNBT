<?php
if($egn=='login') {
	if(isset($_POST['user']) && isset($_POST['pass'])) {
		$user=daddslashes($_POST['user']);
		$pass=daddslashes($_POST['pass']);
		$code=daddslashes($_POST['code']);
		if ($conf['yzm']=='true' && $code != $_SESSION['authcode']) {
			unset($_SESSION['authcode']);
			@header('Content-Type: text/html; charset=UTF-8');
			json_exit('验证码错误');
		} elseif($user==$conf['user'] && $pass==$conf['pwd']) {
			unset($_SESSION['authcode']);
			$session=md5($user.$pass.$password_hash);
			$token=authcode("{$user}\t{$session}", 'ENCODE', SYS_KEY);
			mnbt_rotate_login_session();
			mnbt_set_auth_cookie("admin_token", $token, time() + 604800);
			@header('Content-Type: text/html; charset=UTF-8');
			json_exit('登陆成功');
		} else {
			unset($_SESSION['authcode']);
			@header('Content-Type: text/html; charset=UTF-8');
			json_exit('用户名或密码错误');
		}
	} elseif(isset($_POST['logout'])) {
		mnbt_set_auth_cookie("admin_token", "", time() - 604800);
		@header('Content-Type: text/html; charset=UTF-8');
		json_exit('您已成功注销本次登陆');
	}
}
return;
