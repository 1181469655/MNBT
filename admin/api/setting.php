<?php
if($egn=='setwz') {
	$copy=daddslashes($_POST['gg']);
	$sitename=daddslashes($_POST['qq']);
	$kfqq=daddslashes($_POST['yzm']);
	$zjyx = daddslashes($_POST['zjyx']);
	$sql="update `MN_config` set `gg` =?,`qqh` =?,`yzm` =?,`zjyxbd`=? where `id`=?";
	logjl($user,'网站设置','对网站的设置进行了修改','修改成功',$DB);
	if($DB->query_prepare($sql,[$copy,$sitename,$kfqq,$zjyx,$siteid]))json_exit('修改成功'); else json_exit('修改失败'.$DB->error());
	return;
}
if($egn=='setapi') {
	$apikey=daddslashes($_POST['apikey']);
	$apiqk=daddslashes($_POST['apiqk']);
	$lin=daddslashes($_POST['linux']);
	$win=daddslashes($_POST['windows']);
	logjl($user,'API设置','对网站的API设置进行了修改','修改成功',$DB);
	$sql="update `MN_config` set `api` =?, `apiqk` =?, `hxi` =?, `hxo` =? where `id`=?";
	if($DB->query_prepare($sql,[$apikey,$apiqk,$lin,$win,$siteid]))json_exit('修改成功'); else json_exit('修改失败'.$DB->error());
	return;
}
if($egn=='setkzmb') {
	$name=daddslashes($_POST['name']);
	$ftp=daddslashes($_POST['ftp']);
	$yzm=daddslashes($_POST['yzm']);
	$kg=daddslashes($_POST['kg']);
	$bq=daddslashes($_POST['bq']);
	if(isset($_FILES['loa'])) {
		move_uploaded_file($_FILES['loa']['tmp_name'],'../imsetes/upload_logo/logo.login.png');
	}
	if(isset($_FILES['lob'])) {
		move_uploaded_file($_FILES['lob']['tmp_name'],'../imsetes/upload_logo/logo.index.png');
	}
	if(isset($_FILES['loc'])) {
		move_uploaded_file($_FILES['loc']['tmp_name'],'../imsetes/upload_logo/logo.head.png');
	}
	$auther=md5($date);
	logjl($user,'控制面板设置','对主机的控制面板进行了修改','修改成功',$DB);
	$sql="update `MN_config` set `name` =?, `hxw` =?, `yzme` =?, `kzmbqk` =?, `hxp` =?, `auther` =? where `id`=?";
	if($DB->query_prepare($sql,[$name,$ftp,$yzm,$kg,$bq,$auther,$siteid]))json_exit('修改成功'); else json_exit('修改失败'.$DB->error());
	return;
}
if($egn=='setpaymethods') {
	$raw = isset($_POST['methods']) ? $_POST['methods'] : '';
	$data = json_decode($raw, true);
	if (!is_array($data)) {
		json_exit('数据格式错误');
	}
	// 校验并规范化
	$clean = [];
	$seen = [];
	foreach ($data as $row) {
		if (!is_array($row)) continue;
		$plugin = isset($row['plugin']) ? preg_replace('/[^a-zA-Z0-9_\-]/', '', (string)$row['plugin']) : '';
		$method = isset($row['method']) ? preg_replace('/[^a-zA-Z0-9_\-]/', '', (string)$row['method']) : '';
		if ($plugin === '' || $method === '') continue;
		$key = $plugin . '__' . $method;
		if (isset($seen[$key])) continue;
		$seen[$key] = true;
		$clean[] = [
			'plugin'       => $plugin,
			'method'       => $method,
			'display_name' => mb_substr(isset($row['display_name']) ? trim((string)$row['display_name']) : '', 0, 40),
			'icon'         => mb_substr(isset($row['icon']) ? trim((string)$row['icon']) : '', 0, 60),
			'sort'         => isset($row['sort']) ? max(0, min(999, (int)$row['sort'])) : 99,
		];
	}
	if (function_exists('mnbt_save_payment_methods') && mnbt_save_payment_methods($clean)) {
		logjl($user, '支付设置', '保存了 ' . count($clean) . ' 个付款方式', '修改成功', $DB);
		json_exit('修改成功');
	}
	json_exit('修改失败');
	return;
}
if($egn=='gl') {
	$yuser=daddslashes($_POST['yuser']);
	$ypass=daddslashes($_POST['ypass']);
	$xuser=daddslashes($_POST['xuser']);
	$xpass=daddslashes($_POST['xpass']);
	if(mb_strlen($xuser)<4 && mb_strlen($xuser)!=0 || mb_strlen($xpass)<6 && mb_strlen($xpass)!=0 )json_exit('错误！新的账号必须大于或等于4位！新的密码必须大于或等于6位！');
	if(empty($xuser) && empty($xpass))json_exit('新的账号或密码不能都为空！');
	if($yuser!=$conf['user'] || $ypass!=$conf['pwd']) {
		json_exit('您输入的原账号或密码错误！');
	}
	if(empty($xuser)) {
		$guser=$conf['user'];
	} else {
		$guser=$xuser;
	}
	if(empty($xpass)) {
		$gpwd=$conf['pwd'];
	} else {
		$gpwd=$xpass;
	}
	$sql="update `MN_config` set `user` =?, `pwd` =? where `id`=?";
	logjl($user,'管理修改','修改前账号'.$yuser.'修改前密码'.$ypass,'登陆成功',$DB);
	if($DB->query_prepare($sql,[$guser,$gpwd,$siteid]))json_exit('修改成功'); else json_exit('修改失败'.$DB->error());
	return;
}
if($egn == "mailmode")
{
    $host = daddslashes($_POST['host']);
    $mailuser = daddslashes($_POST['user']);
    $passwrod = daddslashes($_POST['password']);
    $port = daddslashes($_POST['port']);
    if(!$DB->query_prepare("UPDATE `MN_config` SET `mailhost` = ?, `mailuser` = ?, `mailpassword` = ?, `mailport` = ? WHERE `id` = 1", [$host, $mailuser, $passwrod, $port]))
    {
        json_exit('数据库操作失败请联系开发人员判断错误');
    }
    else
    {
        json_exit('修改成功');
    }
	return;
}
if($egn == "jkscsz")
{
    $ymkg = daddslashes($_POST['ymkg']);
    $ymyjkg = daddslashes($_POST['ymyjkg']);
    $ymtsyz = daddslashes($_POST['ymtsyz']);
    $wjkg = daddslashes($_POST['wjkg']);
    $wjyjkg = daddslashes($_POST['wjyjkg']);
    $wjtsyz = daddslashes($_POST['wjtsyz']);
    $opention = daddslashes($_POST['option']);
    if(!$DB->query_prepare("UPDATE `MN_config` SET `ymjkkg` = ?, `mtyxfskg` = ?, `ymjktsyz` = ?, `wjjkkg` = ?, `mtwjfskg` = ?, `wjjktsyz` = ?, `optionzc` = ? WHERE `id` = 1", [$ymkg, $ymyjkg, $ymtsyz, $wjkg, $wjyjkg, $wjtsyz, $opention]))
    {
        json_exit('数据库操作失败请联系开发人员判断错误');
    }
    else
    {
        json_exit('修改成功');
    }
		return;
}
if($egn == 'save_home_settings')
{
	// V1.84 独立主页系统设置：home_enable / home_title / home_hero / home_primary
	// home_logo / home_favicon / home_footer / home_show_notice / home_show_plans
	$fields = ['home_enable','home_title','home_hero','home_primary','home_logo','home_favicon','home_footer','home_show_notice','home_show_plans'];
	$parts = [];
	$params = [];
	foreach ($fields as $f) {
		$v = daddslashes(trim((string)($_POST[$f] ?? '')));
		if ($f === 'home_primary' && $v !== '' && !preg_match('/^#[0-9a-fA-F]{6}$/', $v)) {
			$v = '';
		}
		$parts[] = "`$f` = ?";
		$params[] = $v;
	}
	$params[] = $siteid;
	$sql = "UPDATE `MN_config` SET " . implode(',', $parts) . " WHERE id = ?";
	logjl($user, '主页设置', '修改了主页设置', '修改成功', $DB);
	if ($DB->query_prepare($sql, $params)) json_exit('修改成功'); else json_exit('修改失败' . $DB->error());
	return;
}
if($egn == 'home_upload_icon')
{
	// 上传主页 Logo / Favicon，保存到 imsetes/upload_logo/，并将相对路径写入 MN_config
	$target = ($_POST['target'] ?? '') === 'favicon' ? 'favicon' : 'logo';
	$field = $target === 'logo' ? 'home_logo' : 'home_favicon';
	if (empty($_FILES['icon']) || !is_array($_FILES['icon']) || (int)($_FILES['icon']['error'] ?? 1) !== 0) {
		json_exit('未收到文件或上传失败');
	}
	$file = $_FILES['icon'];
	$name = (string)($file['name'] ?? '');
	$ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
	if (!in_array($ext, ['png', 'jpg', 'jpeg', 'gif', 'ico'])) {
		json_exit('仅支持 png / jpg / gif / ico 格式');
	}
	$tmp = (string)($file['tmp_name'] ?? '');
	if ($tmp === '' || !is_uploaded_file($tmp)) {
		json_exit('文件上传异常');
	}
	$dir = ROOT . 'imsetes/upload_logo/';
	if (!is_dir($dir) && !@mkdir($dir, 0755, true)) {
		json_exit('上传目录不可写');
	}
	$filename = 'home_' . $target . '.' . ($ext === 'ico' ? 'ico' : 'png');
	if (!move_uploaded_file($tmp, $dir . $filename)) {
		json_exit('保存文件失败，请检查 imsetes/upload_logo 目录权限');
	}
	$url = 'imsetes/upload_logo/' . $filename;
	if (!$DB->query_prepare("UPDATE `MN_config` SET `$field` = ? WHERE id = ?", [$url, $siteid])) {
		json_exit('保存配置失败');
	}
	json_exit('上传成功');
	return;
}
if($egn == 'settheme')
{
	$usertheme = mnbt_theme_sanitize($_POST['usertheme'] ?? '');
	$admintheme = mnbt_theme_sanitize($_POST['admintheme'] ?? '');
	$dockertheme = mnbt_theme_sanitize($_POST['dockertheme'] ?? '');
	$hometheme = mnbt_theme_sanitize($_POST['hometheme'] ?? '');
	if ($usertheme === '' || $admintheme === '') {
		json_exit('请选择用户端和管理端主题');
	}
	list($okUser, $msgUser) = mnbt_theme_set_active('user', $usertheme);
	if (!$okUser) {
		json_exit($msgUser);
	}
	list($okAdmin, $msgAdmin) = mnbt_theme_set_active('admin', $admintheme);
	if (!$okAdmin) {
		json_exit($msgAdmin);
	}
	if ($dockertheme !== '') {
		list($okDocker, $msgDocker) = mnbt_theme_set_active('docker', $dockertheme);
		if (!$okDocker) {
			json_exit($msgDocker);
		}
	}
	if ($hometheme !== '') {
		list($okHome, $msgHome) = mnbt_theme_set_active('home', $hometheme);
		if (!$okHome) {
			json_exit($msgHome);
		}
	}
	logjl($user, '主题设置', '用户端=' . $usertheme . ' 管理端=' . $admintheme . ' Docker端=' . ($dockertheme ?: '默认') . ' 主页=' . ($hometheme ?: '默认'), '修改成功', $DB);
	json_exit('修改成功');
	return;
}

return;
