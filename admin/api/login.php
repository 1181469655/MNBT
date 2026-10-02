<?php
if($egn=='login') {
	if(isset($_POST['user']) && isset($_POST['pass'])) {
		$user=trim((string)($_POST['user'] ?? ''));
		$pass=(string)($_POST['pass'] ?? '');
		$code=(string)($_POST['code'] ?? '');
		if ($conf['yzm']=='true' && $code != $_SESSION['authcode']) {
			unset($_SESSION['authcode']);
			@header('Content-Type: text/html; charset=UTF-8');
			json_exit_error('验证码错误');
		} elseif($user==$conf['user'] && mnbt_admin_password_verify($pass, $conf['pwd'])) {
			unset($_SESSION['authcode']);
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
