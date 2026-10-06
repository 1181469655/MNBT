<?php
if($egn=='login') {
	if(isset($_POST['user']) && isset($_POST['pass'])) {
		$user=daddslashes($_POST['user']);
		$pass=daddslashes($_POST['pass']);
		$captchaVerification=(string)($_POST['captchaVerification'] ?? '');
		$ip=mnbt_client_ip();
		if(strpos($user,'"') || strpos($user,"'") || strpos($user,',') || strpos($user,'/') || strpos($user,"\\"))exit('{"code":"账号不能包含危险字符！"}');
		if ($conf['yzme']!='false') {
			// 登录防爆破：IP 维度 10 分钟窗口内最多 10 次失败
			if (mnbt_throttle_exceeded('login_user', $ip, 10, 600)) {
				@header('Content-Type: text/html; charset=UTF-8');
				exit('{"code":"失败次数过多，请 10 分钟后再试！"}');
			}
			if (mnbt_captcha_provider()) {
				// 插件验证码接管：载荷固定为 captchaToken
				$captchaRs=mnbt_captcha_provider_verify(mnbt_captcha_provider(), (string)($_POST['captchaToken'] ?? ''));
			} else {
				// 内置行为验证码（官方组件协议）：二次校验后销毁，防重放
				$captchaRs=$captchaVerification === ''
					? '请先完成人机验证'
					: mnbt_captcha_verify($captchaVerification);
			}
			if ($captchaRs !== true) {
				@header('Content-Type: text/html; charset=UTF-8');
				exit('{"code":"'.$captchaRs.'"}');
			}
		}
		$wedsv=$DB->get_row_prepare("SELECT * FROM MN_zj WHERE user=? limit 1", [$user]);
		if($user==$wedsv['user'] && $pass==$wedsv['pass']) {
			mnbt_throttle_clear('login_user', $ip);
			$session=md5($user.$pass.$password_hash);
			$token=authcode("{$user}\t{$session}", 'ENCODE', SYS_KEY);
			mnbt_rotate_login_session();
			mnbt_set_auth_cookie("user_token", $token, time() + 604800);
			@header('Content-Type: text/html; charset=UTF-8');
			json_exit('登陆成功');
		} else {
			mnbt_throttle_hit('login_user', $ip, 600);
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
