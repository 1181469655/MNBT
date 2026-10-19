<?php
/**
 * 保存控制面板页上传的三张 Logo：只收 PNG/JPG，超大文件或伪造图片一律拒绝。
 * 先全部校验再落盘，避免出现「前一张已覆盖、后一张报错」的半更新状态。
 * 返回 ['saved' => 已保存文件名(逗号分隔), 'error' => 失败原因]。
 */
function setkzmb_save_logos($maxMb)
{
	$map = ['loa' => 'logo.login.png', 'lob' => 'logo.index.png', 'loc' => 'logo.head.png'];
	$ready = [];
	foreach ($map as $key => $filename) {
		$file = isset($_FILES[$key]) ? $_FILES[$key] : null;
		if (!is_array($file)) continue;
		$err = isset($file['error']) ? (int)$file['error'] : UPLOAD_ERR_NO_FILE;
		if ($err === UPLOAD_ERR_NO_FILE) continue;
		if ($err !== UPLOAD_ERR_OK) {
			return ['error' => $filename.' 上传失败（错误码 '.$err.'），请确认文件不超过 PHP 的 upload_max_filesize'];
		}
		if (empty($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
			return ['error' => $filename.' 上传无效，请重新选择文件'];
		}
		if ((int)$file['size'] > $maxMb * 1024 * 1024) {
			return ['error' => $filename.' 超过 '.$maxMb.'MB 限制'];
		}
		$info = @getimagesize($file['tmp_name']);
		if ($info === false || !in_array($info[2], [IMAGETYPE_PNG, IMAGETYPE_JPEG], true)) {
			return ['error' => $filename.' 不是有效的 PNG 或 JPG 图片'];
		}
		$ready[$key] = $file['tmp_name'];
	}
	$saved = [];
	foreach ($ready as $key => $tmp) {
		if (!move_uploaded_file($tmp, '../imsetes/upload_logo/'.$map[$key])) {
			return ['error' => $map[$key].' 保存失败，请检查 imsetes/upload_logo 目录写入权限'];
		}
		$saved[] = $map[$key];
	}
	return ['saved' => implode(',', $saved)];
}
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
	$sql="update `MN_config` set `api` =?, `apiqk` =?, `hxi` =?, `hxo` =? where `id`=?";
	if(!$DB->query_prepare($sql,[$apikey,$apiqk,$lin,$win,$siteid]))json_exit_error('修改失败'.$DB->error());
	// 协议模式随保存一并提交；未携带该字段的旧页面不改列，避免把兼容模式误切回严格模式
	if(isset($_POST['api_compat'])) {
		$apicompat = ($_POST['api_compat'] === '1') ? '1' : '0';
		$sql="update `MN_config` set `api_compat` =? where `id`=?";
		if(!$DB->query_prepare($sql,[$apicompat,$siteid]))json_exit_error('修改失败'.$DB->error());
		logjl($user,'API设置','对外API协议模式切换为'.($apicompat==='1'?'兼容1.81':'新版1.83+'),'修改成功',$DB);
	}
	logjl($user,'API设置','对网站的API设置进行了修改','修改成功',$DB);
	json_exit('修改成功');
	return;
}
if($egn=='setkzmb') {
	$name=daddslashes($_POST['name']);
	$yzm=daddslashes($_POST['yzm']);
	$kg=daddslashes($_POST['kg']);
	$bq=daddslashes($_POST['bq']);
	// 上传体积超过 PHP post_max_size 时 $_POST 会整体为空，缺此校验会把面板名称洗成空串
	if($name==='') json_exit_error('控制面板名称不能为空');
	$logoFiles = setkzmb_save_logos(mnbt_panel_logo_max_mb());
	if(isset($logoFiles['error'])) json_exit_error($logoFiles['error']);
	$auther=md5($date);
	logjl($user,'控制面板设置','对主机的控制面板进行了修改'.($logoFiles['saved'] ? '(已更新 Logo: '.$logoFiles['saved'].')' : ''),'修改成功',$DB);
	$sql="update `MN_config` set `name` =?, `yzme` =?, `kzmbqk` =?, `hxp` =?, `auther` =? where `id`=?";
	if($DB->query_prepare($sql,[$name,$yzm,$kg,$bq,$auther,$siteid]))json_exit('修改成功'); else json_exit('修改失败'.$DB->error());
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
	$yuser=trim((string)($_POST['yuser'] ?? ''));
	$ypass=(string)($_POST['ypass'] ?? '');
	$xuser=trim((string)($_POST['xuser'] ?? ''));
	$xpass=(string)($_POST['xpass'] ?? '');
	if(mb_strlen($xuser)<4 && mb_strlen($xuser)!=0 || mb_strlen($xpass)<6 && mb_strlen($xpass)!=0 )json_exit('错误！新的账号必须大于或等于4位！新的密码必须大于或等于6位！');
	if(empty($xuser) && empty($xpass))json_exit('新的账号或密码不能都为空！');
	if($yuser!=$conf['user'] || !mnbt_admin_password_verify($ypass, $conf['pwd'])) {
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
		$gpwd=mnbt_admin_password_hash($xpass);
	}
	$sql="update `MN_config` set `user` =?, `pwd` =? where `id`=?";
	logjl($user,'管理修改','修改前账号'.$yuser.'修改前密码******','操作成功',$DB);
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
if($egn == 'settheme')
{
	$usertheme = mnbt_theme_sanitize($_POST['usertheme'] ?? '');
	$admintheme = mnbt_theme_sanitize($_POST['admintheme'] ?? '');
	$dockertheme = mnbt_theme_sanitize($_POST['dockertheme'] ?? '');
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
	logjl($user, '主题设置', '用户端=' . $usertheme . ' 管理端=' . $admintheme . ' Docker端=' . ($dockertheme ?: '默认'), '修改成功', $DB);
	json_exit('修改成功');
	return;
}

return;
