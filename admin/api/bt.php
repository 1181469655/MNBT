<?php
if($egn=='addbt') {
	$ip=daddslashes($_POST['ip']);
	$dk=daddslashes($_POST['dk']);
	$key=daddslashes($_POST['key']);
	$bh=daddslashes($_POST['bh']);
	$btos=daddslashes($_POST['btos']);
	$urlla=daddslashes($_POST['urlla']);
	$ftpdz=daddslashes($_POST['ftpdz']);
	$xieyi=daddslashes($_POST['xieyi']);
	$kg=daddslashes($_POST['kg']);
	$rowe=$DB->get_row_prepare("SELECT * FROM MN_bt WHERE 1 order by id desc limit 1");
	$id=$rowe['id']+1;
	$dati='ikj'.$date.mt_rand(100,10000).mt_rand(10,99999).'sql';
	$kiterw=mt_rand(1,100);
	$ktmy=md5($dati);
	$qmk=md5($kiterw);
	logjl($user,'添加宝塔','添加了一个编号为'.$bh.'的宝塔','添加成功',$DB);
	if($DB->query_prepare("INSERT INTO `MN_bt` (`id`, `btip`, `btdk`, `btmy`, `date`, `ktmy`, `qmk`, `btdh`, `qk`, `btos`, `als`, `ptl`, `ftpdz`) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)", [$id, $ip, $dk, $key, $date, $ktmy, $qmk, $bh, $kg, $btos, $urlla, $xieyi, $ftpdz])) {
		// 添加成功后自动检测并设置默认 PHP 版本（非阻塞）
		if (is_file(SYSTEM_ROOT . 'bt_php.function.php')) {
			require_once SYSTEM_ROOT . 'bt_php.function.php';
			mnbt_node_auto_detect_php($bh);
		}
		json_exit('添加成功');
	} else {
		json_exit('添加失败'.$DB->error());
	}
	return;
}
if($egn=='btsc') {
	$id=$_POST['id'];
	logjl($user,'删除宝塔','删除了ID为'.$id.'的宝塔','删除成功',$DB);
	$cres=$DB->get_row_prepare("SELECT * FROM MN_bt WHERE id=? limit 1", [$id]);
	$ssbt=$cres['btdh'];
	if($DB->query_prepare("DELETE FROM MN_bt WHERE id=? limit 1", [$id])) {
		$rs=$DB->get_all_prepare("SELECT * FROM MN_zj WHERE ssbt=? order by id desc limit 9999", [$ssbt]);
		if (is_array($rs)) {
			foreach($rs as $res) {
				$bjyr=$res['id'];
				$DB->query_prepare("DELETE FROM MN_zj WHERE id=? limit 1", [$bjyr]);
			}
		}
		json_exit('删除成功');
	} else {
		json_exit('删除失败'.$DB->error());
	}
	return;
}
if($egn=='btsj') {
	$id=$_POST['id'];
	$cres=$DB->get_row_prepare("SELECT * FROM MN_bt WHERE id=? limit 1", [$id]);
	exit('{"code":"1","ip":"'.$cres['btip'].'","dk":"'.$cres['btdk'].'","my":"'.$cres['btmy'].'","kg":"'.$cres['qk'].'","btos":"'.$cres['btos'].'"}');
	return;
}
if($egn=='xgjl') {
	$id=daddslashes($_POST['id']);
	$ip=daddslashes($_POST['ip']);
	$dk=daddslashes($_POST['dk']);
	$key=daddslashes($_POST['key']);
	$kg=daddslashes($_POST['kg']);
	$btos=daddslashes($_POST['btos']);
	$urlla=daddslashes($_POST['urlla']);
	$ftpdz=daddslashes($_POST['ftpdz']);
	$xieyi=daddslashes($_POST['xieyi']);
	logjl($user,'修改宝塔','对ID为'.$id.'的宝塔进行了修改','修改成功',$DB);
	if($DB->query_prepare("update `MN_bt` set `btip` =?,`btdk` =?,`btmy` =?,`qk` =?,`btos` =?,`als` =?,`ptl` =?,`ftpdz` =? where `id`=?", [$ip,$dk,$key,$kg,$btos,$urlla,$xieyi,$ftpdz,$id]))json_exit('修改成功'); else json_exit('修改失败'.$DB->error());
	return;
}
if($egn=='mnbt') {
	include("../MPHX/BL.php");
	include("../MPHX/SQ.php");
	include("../cf_up.php");
	$gxtj = array(
		'url' => $_SERVER['HTTP_HOST'],
		'authcode' => $authcode,
		'ver' => $WEBQB,
		);
	$result = send_post($mn_conf['aet'].'://'.$mn_conf['url'].':'.$mn_conf['port'].'/check.php',$gxtj);
	$content=json_decode($result, true);
	$total ='V'.sprintf( "%.2f ",$WEBQB/1000);
	if($content['code']=='0') {
		$cl='mdi-bookmark-check';
		$gx='您使用的已是最新版本！';
	} elseif($content['code']=='1') {
		$cl='mdi-arrow-up-bold-circle';
		$gx='已经有新版本推出！请前往系统管理->系统更新处进行更新';
	} elseif($content['code']=='-1') {
		$cl='mdi-account-off';
		$gx='离线模式不提供更新！！！';
	}
	exit(json_encode(['cl'=>$cl,'gx'=>$gx,'vs'=>$total,'gg'=>$content['gg']],256));
}
if($egn=='listbt') {
	//宝塔列表
	$sorting=strtoupper($_POST['sortOrder']??'')==='DESC'?'DESC':'ASC';
	$paixu=preg_replace('/[^a-zA-Z0-9_]/','',$_POST['sort']??'id')?:'id';
	$pagesize=intval($_POST['limit']);
	$pageu=(intval($_POST['page'])-1) * $pagesize;
	$countdata=$DB->count_prepare("SELECT count(*) from MN_bt WHERE 1");
	$data=["total"=>$countdata];
	$rows=$DB->get_all_prepare("SELECT * FROM MN_bt order by $paixu $sorting limit $pageu,$pagesize");
	// 计算调用密钥 dymy = md5(ktmy.qmk)，前端直接用不需要算 md5
	foreach($rows as &$r){
		$r['dymy']=md5($r['ktmy'].$r['qmk']);
	}
	unset($r);
	$data["rows"]=$rows;
	exit(json_encode($data));
	return;
}
if($egn=='btztjc') {
	$btid = intval($_POST['btid']);
	$cert = $DB->get_row_prepare("SELECT * FROM MN_bt WHERE id=? limit 1", [$btid]);
	if(!$cert) {
		exit(json_encode(['qk'=>0,'code'=>'宝塔不存在','titco'=>'text-danger']));
	}
	include_once(SYSTEM_ROOT."bt_api.php");
	$protocol = $cert['ptl']=='true'?'https':'http';
	$bt_url = $protocol.'://'.$cert['btip'].':'.$cert['btdk'];
	$api = new bt_api($bt_url, $cert['btmy']);
	try {
		$result = $api->btapi_listphp();
		if(is_array($result) && isset($result['status']) && ($result['status'] === false || $result['status'] === 0 || $result['status'] === '0')) {
			// 面板返回业务错误（连接失败/鉴权失败等），透出真实原因
			exit(json_encode(['qk'=>0,'code'=>($result['msg'] ?? '面板返回错误'),'titco'=>'text-danger'], JSON_UNESCAPED_UNICODE));
		}
		if(is_array($result)) {
			exit(json_encode(['qk'=>1,'code'=>'通信正常','titco'=>'text-success']));
		} else {
			exit(json_encode(['qk'=>0,'code'=>'无法获取面板信息：'.json_encode($result,256),'titco'=>'text-danger']));
		}
	} catch (Throwable $e) {
		exit(json_encode(['qk'=>0,'code'=>'连接失败：'.$e->getMessage(),'titco'=>'text-danger']));
	}
}

// ====== 节点 PHP 版本管理（系统底层，独立于插件） ======

if ($egn === 'list_node_php') {
	$btdh = trim((string)($_POST['btdh'] ?? ''));
	if ($btdh === '') {
		exit(json_encode(['qk' => 0, 'msg' => '请指定节点']));
	}
	require_once SYSTEM_ROOT . 'bt_php.function.php';
	$result = mnbt_node_php_list($btdh);
	if (!$result['ok']) {
		exit(json_encode(['qk' => 0, 'msg' => $result['msg'], 'raw' => $result['raw'] ?? null], JSON_UNESCAPED_UNICODE));
	}
	$currentDefault = mnbt_node_get_php($btdh);
	exit(json_encode(['qk' => 1, 'versions' => $result['versions'], 'latest' => $result['latest'], 'current_default' => $currentDefault]));
}

if ($egn === 'auto_detect_node_php') {
	$btdh = trim((string)($_POST['btdh'] ?? ''));
	if ($btdh === '') {
		exit(json_encode(['qk' => 0, 'msg' => '请指定节点']));
	}
	require_once SYSTEM_ROOT . 'bt_php.function.php';
	$result = mnbt_node_auto_detect_php($btdh);
	if (!$result['ok']) {
		exit(json_encode(['qk' => 0, 'msg' => $result['msg']]));
	}
	exit(json_encode(['qk' => 1, 'version' => $result['version'], 'msg' => $result['msg']]));
}

if ($egn === 'set_node_php') {
	$btdh = trim((string)($_POST['btdh'] ?? ''));
	$version = trim((string)($_POST['version'] ?? ''));
	if ($btdh === '' || $version === '') {
		exit(json_encode(['qk' => 0, 'msg' => '参数不完整']));
	}
	require_once SYSTEM_ROOT . 'bt_php.function.php';
	if (!mnbt_node_set_php($btdh, $version)) {
		exit(json_encode(['qk' => 0, 'msg' => '保存失败']));
	}
	exit(json_encode(['qk' => 1, 'msg' => '已设置节点 ' . htmlspecialchars($btdh) . ' 的默认 PHP 版本为 ' . $version]));
}

return;
