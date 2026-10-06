<?php
if($egn=='login') {
	if(isset($_POST['user']) && isset($_POST['pass'])) {
		$user=daddslashes($_POST['user']);
		$pass=daddslashes($_POST['pass']);
		$code=daddslashes($_POST['code'] ?? '');
		$captchaVerification=(string)($_POST['captchaVerification'] ?? '');
		$ip=mnbt_client_ip();
		if(strpos($user,'"') || strpos($user,"'") || strpos($user,',') || strpos($user,'/') || strpos($user,"\\"))exit('{"code":"账号不能包含危险字符！"}');
		if ($conf['yzme']!='false') {
			// 登录防爆破：IP 维度 10 分钟窗口内最多 10 次失败
			if (mnbt_throttle_exceeded('login_user', $ip, 10, 600)) {
				@header('Content-Type: text/html; charset=UTF-8');
				exit('{"code":"失败次数过多，请 10 分钟后再试！"}');
			}
			if ($captchaVerification !== '') {
				// 行为验证码（官方组件协议）：二次校验后销毁，防重放
				$captchaRs=mnbt_captcha_verify($captchaVerification);
				if ($captchaRs !== true) {
					@header('Content-Type: text/html; charset=UTF-8');
					exit('{"code":"'.$captchaRs.'"}');
				}
			} else {
				// classic 模板兼容：旧文字验证码。
				// 严格比较修复历史绕过：不加载 code.php 时 authcode 为 null，
				// 旧代码 '' != null 为 false 可直接跳过验证码
				$sessCode=isset($_SESSION['authcode']) && is_string($_SESSION['authcode']) ? $_SESSION['authcode'] : '';
				if ($sessCode==='' || $code==='' || !hash_equals($sessCode, $code)) {
					unset($_SESSION['authcode']);
					@header('Content-Type: text/html; charset=UTF-8');
					exit('{"code":"验证码错误！"}');
				}
			}
		}
		$wedsv=$DB->get_row_prepare("SELECT * FROM MN_zj WHERE user=? limit 1", [$user]);
		if($user==$wedsv['user'] && $pass==$wedsv['pass']) {
			unset($_SESSION['authcode']);
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
