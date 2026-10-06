<?php
if($egn=='login') {
	if(isset($_POST['user']) && isset($_POST['pass'])) {
		$user=trim((string)($_POST['user'] ?? ''));
		$pass=(string)($_POST['pass'] ?? '');
		$code=(string)($_POST['code'] ?? '');
		$captchaVerification=(string)($_POST['captchaVerification'] ?? '');
		$ip=mnbt_client_ip();
		if ($conf['yzm']=='true') {
			// 登录防爆破：IP 维度 10 分钟窗口内最多 10 次失败
			if (mnbt_throttle_exceeded('login_admin', $ip, 10, 600)) {
				json_exit_error('失败次数过多，请 10 分钟后再试');
			}
			if ($captchaVerification !== '') {
				// 行为验证码（官方组件协议）：二次校验后销毁，防重放
				$captchaRs=mnbt_captcha_verify($captchaVerification);
				if ($captchaRs !== true) {
					json_exit_error($captchaRs);
				}
			} else {
				// classic 模板兼容：旧文字验证码。
				// 严格比较修复历史绕过：不加载 code.php 时 authcode 为 null，
				// 旧代码 '' != null 为 false 可直接跳过验证码
				$sessCode=isset($_SESSION['authcode']) && is_string($_SESSION['authcode']) ? $_SESSION['authcode'] : '';
				if ($sessCode==='' || $code==='' || !hash_equals($sessCode, $code)) {
					unset($_SESSION['authcode']);
					@header('Content-Type: text/html; charset=UTF-8');
					json_exit_error('验证码错误');
				}
			}
		}
		if($user==$conf['user'] && mnbt_admin_password_verify($pass, $conf['pwd'])) {
			unset($_SESSION['authcode']);
			mnbt_throttle_clear('login_admin', $ip);
			// 明文历史密码在首次登录成功后升级为 bcrypt 哈希；会话令牌按存储值换算，旧登录态失效一次
			if (!mnbt_admin_password_is_hash($conf['pwd'])) {
				$conf['pwd'] = mnbt_admin_password_hash($pass);
				$DB->query_prepare("update `MN_config` set `pwd`=? where `id`=?", [$conf['pwd'], $siteid]);
				logjl($user, '安全加固', '管理员密码已升级为 bcrypt 哈希存储', '升级成功', $DB);
			}
			$session=md5($conf['user'].$conf['pwd'].$password_hash);
			$token=authcode("{$user}\t{$session}", 'ENCODE', SYS_KEY);
			mnbt_rotate_login_session();
			mnbt_set_auth_cookie("admin_token", $token, time() + 604800);
			@header('Content-Type: text/html; charset=UTF-8');
			json_exit('登陆成功');
		} else {
			unset($_SESSION['authcode']);
			mnbt_throttle_hit('login_admin', $ip, 600);
			@header('Content-Type: text/html; charset=UTF-8');
			json_exit_error('用户名或密码错误');
		}
	} elseif(isset($_POST['logout'])) {
		mnbt_set_auth_cookie("admin_token", "", time() - 604800);
		@header('Content-Type: text/html; charset=UTF-8');
		json_exit('您已成功注销本次登陆');
	}
}
return;
