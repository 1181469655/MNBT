<?php
if($egn=='phpxg') {
	$php=daddslashes($_POST['php'] ?? '');
	include("../class.php");
	$apie = new bt_api_set($btipe,$btkeye);
	$r_data = $apie->btapi_setphp($yhc['sqldz'],$php);
	$ok = isset($r_data['status']) && ($r_data['status'] === true || $r_data['status'] === 'true');
	logjl($yhc['user'],'PHP版本','修改PHP版本为'.$php, $ok?'修改成功':'修改失败：'.($r_data['msg']??'未知错误'), $DB);
	if($ok) {
		exit(json_encode(['code'=>'修改成功', 'phpversion'=>$php], JSON_UNESCAPED_UNICODE));
	} else {
		exit(json_encode(['code'=>'修改失败：'.($r_data['msg']??'未知错误')], JSON_UNESCAPED_UNICODE));
	}
}
if($egn=='sqldr') {
	//导入SQL文件
	$ml=daddslashes($_POST['path'] ?? '');
	$name=daddslashes($_POST['filename'] ?? '');
	if(substr($ml,0,1)!='/')exit('{"code":"目录格式错误！"}');
	if(strpos($name,'/')!==false)exit('{"code":"文件名格式错误！"}');
	if(substr(strtolower($name),'-4' , 4)!='.sql')exit('{"code":"错误！您导入的文件不是SQL文件！"}');
	$path = $os_xt.$yhc['sqldz'].$ml;
	include("../class.php");
	$api = new bt_api($btipe,$btkeye);
	$list=dirfiles(($api->GetLogshqwjlo($path) ?: [])['FILES'] ?? [],'file')['file'];
	$file=false;
	foreach($list as $val) {
		if($val['name']==$name) {
			$file=$val;
			break;
		}
	}
	if(!$file)exit('{"code":"错误！文件不存在！"}');
	$sqlsize=json_decode($yhc['hxb'],true);
	$mbsize=round($file['size']/1048576);
	if($mbsize>$sqlsize['max'])exit('{"code":"错误！导入的文件大于您的最大可用数据库空间！"}');
	if($sqlsize['max']<=$sqlsize['dq'])exit('{"code":"错误！您的数据库空间已满！"}');
	if($mbsize>$sqlsize['max']-$sqlsize['dq'])exit('{"code":"错误！导入的文件大于您现在可用的数据库空间大小！请清除数据库空间至剩余'.$mbsize.'MB为止！"}');
	$r_datr = $api->drsql(array($path.$name,$yhc['sqluser']));
	$r_datr = $r_datr ?: [];
	logjl($yhc['user'],'导入SQL','导入SQL文件'.$name,'导入成功',$DB);
	json_exit($r_datr['msg']??'');
	return;
}
if($egn=='scmmfw') {
	//删除密码访问
	$setname=$_POST['mb'] ?? '';
	include("../class.php");
	$api = new bt_api($btipe,$btkeye);
	$r_data = $api->GetLogsr($zjid,$setname) ?: [];
	logjl($yhc['user'],'密码访问','删除了密码访问目录'.$setname,'删除成功',$DB);
	json_exit($r_data['msg']??'');
	return;
}
if($egn=='tjmmfw') {
	//添加密码访问
	$name=$_POST['name'] ?? '';
	$ml=$_POST['mbml'] ?? '';
	$zh=$_POST['user'] ?? '';
	$mm=$_POST['pass'] ?? '';
	if(substr($ml,0,1)!='/')exit('{"code":"目录格式错误！"}');
	include("../class.php");
	$api = new bt_api($btipe,$btkeye);
	$r_data = $api->GetLogst($zjid,$name,$ml,$zh,$mm) ?: [];
	logjl($yhc['user'],'密码访问','添加了密码访问目录'.$ml,'添加成功',$DB);
	json_exit($r_data['msg']??'');
	return;
}
if($egn=='xgmrwd') {
	//修改默认文档
	$index=$_POST['ml'] ?? '';
	include("../class.php");
	$api = new bt_api($btipe,$btkeye);
	$r_data = $api->GetLogsea($zjid,$index) ?: [];
	logjl($yhc['user'],'默认文档','修改默认文档为'.$index,'修改成功',$DB);
	json_exit($r_data['msg']??'');
	return;
}
if($egn=='hqjt') {
	//获取伪静态
	$tdxz=($_POST['xz']??'')!='0.当前'?'rewrite/nginx/'.($_POST['xz']??''):'vhost/rewrite/'.$yhc['sqldz'];
	$jt='/www/server/panel/'.$tdxz.'.conf';
	if($cert['btos']=='1') {
		$jt='/www/server/panel/'.$tdxz.'.conf';
	} else {
		$jt='D:/BtSoft/panel/'.$tdxz.'.conf';
	}
	include("../class.php");
	$api = new bt_api($btipe,$btkeye);
	$r_data = $api->GetLogswt($jt) ?: [];
	exit($r_data['data']??'');
	return;
}
if($egn=='setwjt') {
	//设置伪静态
	include("../class.php");
	if($cert['btos']=='1') {
		//$jt='/www/server/panel/'.$tdxz.'.conf';
		$api = new bt_api($btipe,$btkeye);
		$r_data = $api->setwjt([$_POST['wb']??'','/www/server/panel/vhost/rewrite/'.$yhc['sqldz'].'.conf']) ?: [];
		json_exit($r_data['msg']??'');
	} else {
		//$jt='/www/server/panel/'.$tdxz.'.conf';
		$api = new win_bt_api($btipe,$btkeye);
		$r_data = $api->setwjt([$yhc['sqldz'],$_POST['wb']??'']) ?: [];
		json_exit($r_data['msg']??'');
	}
	return;
}
if($egn=='ftpjy') {
	//解压文件
	$ywj=$_POST['jywj'] ?? '';
	$jyd=$_POST['jyd'] ?? '';
	$jypass=$_POST['jymm'] ?? '';
	$jybm=$_POST['wjbm'] ?? '';
	if(substr($jyd,0,1)!='/')exit('{"code":"解压到的目录格式错误！"}');
	include("../class.php");
	$api = new bt_api($btipe,$btkeye);
	$r_data = $api->GetLogsjywj($os_xt.$yhc['sqldz'].$ywj,$os_xt.$yhc['sqldz'].$jyd,$jybm,$jypass);
	json_exit('解压成功');
	return;
}
if($egn=='xgpass') {
	//修改密码
	$ftpmm=daddslashes($_POST['ftp'] ?? '');
	$sqlmm=daddslashes($_POST['sql'] ?? '');
	if(mb_strlen($ftpmm)<6 && mb_strlen($ftpmm)!=0 || mb_strlen($sqlmm)<6 && mb_strlen($sqlmm)!=0 )exit('{"code":"错误！FTP密码和数据库密码都不能小于6位！"}');
	$user=$yhc['user'];
	if(empty($ftpmm) && empty($sqlmm))exit('{"code":"错误！FTP密码和SQL密码不能全为空！"}');
	include("../class.php");
	$api = new bt_api($btipe,$btkeye);
	if(empty($ftpmm)) {
		$pass=$yhc['pass'];
	} else {
		$api->GetLogsftp($yhc['ftpid'],$yhc['user'],$ftpmm);
		$pass=$ftpmm;
	}
	if(empty($sqlmm)) {
		$gpwd=$yhc['sqlpass'];
	} else {
		$api->GetLogsworld($yhc['hxd'],$yhc['sqluser'],$sqlmm);
		$gpwd=$sqlmm;
	}
	logjl($yhc['user'],'密码修改','修改了FTP和数据库密码','修改成功',$DB);
	if($DB->query_prepare("update `MN_zj` set `sqlpass` =?, `pass` =? where `user`=?", [$gpwd, $pass, $user])) json_exit('修改成功'); else json_exit('修改失败');
	return;
}
if($egn=='setyxml') {
	//设置运行目录
	$szh=daddslashes($_POST['wb'] ?? '');
	if(substr($szh,0,1)!='/')exit('{"code":"目录格式错误！"}');
	include("../class.php");
	$api = new bt_api($btipe,$btkeye);
	$abc=$api->setyxml([$yhc['btid'],$szh,$os_xt.$yhc['sqldz']]);
	$abc = $abc ?: [];
	logjl($yhc['user'],'运行目录','设置运行目录为'.$szh,'设置成功',$DB);
	json_exit($abc['msg']??'');
	return;
}
if($egn=='sxsyxx') {
	//刷新网页空间，数据库空间，流量使用情况
	// 与其它接口一致用 ../class.php（旧 ../../class.php 在 CWD=user 时会越界失败 → 500）
	include("../class.php");
	$sql_kjr = json_decode($yhc['hxb'] ?? '', true) ?: [];
	$web_kjr = json_decode($yhc['hxa'] ?? '', true) ?: [];
	$ll_kjr = json_decode($yhc['llmax'] ?? '', true) ?: [];
	$r_js_web = $web_kjr;
	$r_js_sql = $sql_kjr;
	$api = new bt_api($btipe, $btkeye);
$t_id = $yhc['id'] ?? 0;

$r_data = $api->webkjjs($os_xt . $yhc['sqldz']) ?: [];
$webkj = ($r_data['size'] ?? 0) / (1024 * 1000);
$r_js_web = $web_kjr;
$r_js_web['dq'] = sprintf('%.2f', $webkj);
$r_sy = json_encode($r_js_web, 256);
$DB->query_prepare('update `MN_zj` set `hxa` =? where `id`=?', [$r_sy, $t_id]);

$r_datb = $api->sqlkjhq($yhc['sqluser'] ?? '') ?: [];
$r_datb_data_size = (string)($r_datb['data_size'] ?? '0');
if (substr($r_datb_data_size, -2) == 'kb' || substr($r_datb_data_size, -2) == 'KB' || substr($r_datb_data_size, -2) == 'kB' || substr($r_datb_data_size, -2) == 'Kb') {
	$sqlkj = str_ireplace(substr($r_datb_data_size, -2), '', $r_datb_data_size);
} elseif (substr($r_datb_data_size, -2) == 'MB' || substr($r_datb_data_size, -2) == 'mb' || substr($r_datb_data_size, -2) == 'Mb' || substr($r_datb_data_size, -2) == 'mB') {
	$sqlkj = str_ireplace(substr($r_datb_data_size, -2), '', $r_datb_data_size) * 1000;
} elseif (substr($r_datb_data_size, -1) == 'b' || substr($r_datb_data_size, -1) == 'B') {
	$sqlkj = (float)preg_replace('/[^0-9.]/', '', $r_datb_data_size) / 1000;
} else {
	$sqlkj = '0';
}
$adft = ((float)$sqlkj) / 1024;
$r_js_sql = $sql_kjr;
$r_js_sql['dq'] = sprintf('%.2f', $adft);
$r_sy = json_encode($r_js_sql, 256);
$DB->query_prepare('update `MN_zj` set `hxb` =? where `id`=?', [$r_sy, $t_id]);

	$s_data = $api->getlog($yhc['sqldz'] ?? '') ?: [];
	$g_size = 0;
	if (($s_data['status'] ?? false) && ($s_data['msg'] ?? '') != '') {
		$sfyr = explode(' - - ', $s_data['msg']);
		unset($sfyr[0]);
		$latest_ts = '';
		foreach ($sfyr as $vfm) {
			preg_match('/\[(.*?)\]/', $vfm, $tm);
			if (!($tm[1] ?? '')) continue;
			if (isset($ll_kjr['statistics']) && $ll_kjr['statistics'] !== '' && $tm[1] <= $ll_kjr['statistics']) continue;
			$e_size = explode(' ', $vfm);
			if (!isset($e_size[6]) || !is_numeric($e_size[6])) continue;
			$g_size += $e_size[6];
			if ($tm[1] > $latest_ts) $latest_ts = $tm[1];
		}
		if ($latest_ts !== '') $ll_kjr['statistics'] = $latest_ts;
	}
	$ll_kjr['dq'] = ($ll_kjr['dq'] ?? 0) + $g_size;
	$r_sy = json_encode($ll_kjr, 256);
	$DB->query_prepare('update `MN_zj` set `llmax` =? where `id`=?', [$r_sy, $t_id]);

	// 未定义 max/dq 时按 0，避免 PHP8 Warning→Exception 导致 500
	$ll_ok = (float)($ll_kjr['dq'] ?? 0) <= (float)($ll_kjr['max'] ?? 0) * 1024 * 1024 * 1024;
	$web_ok = (float)($r_js_web['dq'] ?? 0) <= (float)($r_js_web['max'] ?? 0);
	$sql_ok = (float)($r_js_sql['dq'] ?? 0) <= (float)($r_js_sql['max'] ?? 0);
	if ($ll_ok && $web_ok && $sql_ok) {
		$api->qdweb($yhc['btid'] ?? '', $yhc['sqldz'] ?? '');
		$api->ftpxg($yhc['ftpid'] ?? '', $yhc['user'] ?? '', '1');
	} else {
		$api->ztweb($yhc['btid'] ?? '', $yhc['sqldz'] ?? '');
		$api->ftpxg($yhc['ftpid'] ?? '', $yhc['user'] ?? '', '0');
	}
	json_exit('刷新成功！');
	return;
}

if($egn=='setgzip' || $egn=='gzip') {
	$action = $_POST['action'] ?? ($_POST['status'] ?? '');
	include("../class.php");
	$api = new bt_api($btipe,$btkeye);
	if ($action === 'off' || $action === '0' || $action === 0 || $action === false) {
		$result = $api->remove_gzip_status($yhc['sqldz']);
	} else {
		$level = intval($_POST['level'] ?? 6);
		if ($level < 1 || $level > 9) $level = 6;
		$min_len = trim($_POST['min_len'] ?? '1k');
		if ($min_len === '') $min_len = '1k';
		$types = trim($_POST['types'] ?? 'text/plain application/javascript application/x-javascript text/javascript text/css application/xml application/json image/jpeg image/gif image/png font/ttf font/otf image/svg+xml application/xml+rss text/x-js');
		$result = $api->set_gzip($yhc['sqldz'], $types, (string)$level, $min_len);
	}
	if (isset($result['status']) && ($result['status'] === true || $result['status'] === 'true')) {
		logjl($yhc['user'], 'Gzip配置', '修改Gzip压缩为: '.$action, '修改成功', $DB);
		json_exit('修改成功');
	} else {
		logjl($yhc['user'], 'Gzip配置', '修改Gzip压缩为: '.$action, '修改失败：'.($result['msg'] ?? '未知错误'), $DB);
		json_exit('操作失败：'.($result['msg'] ?? '未知错误'));
	}
	return;
}

