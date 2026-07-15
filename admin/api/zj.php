<?php
if($egn=='zjsc') {
	//删除主机
	$id=$_POST['id'];
	$cres=$DB->get_row_prepare("SELECT * FROM MN_zj WHERE id=? limit 1", [$id]);
	$ftr=$cres['btid'];
	$sza=$cres['sqldz'];
	$yrez=$cres['ssbt'];
	$cert=$DB->get_row_prepare("SELECT * FROM MN_bt WHERE btdh=? limit 1", [$yrez]);
	$btipe=($cert['ptl']=='true'?'https':'http').'://'.$cert['btip'].':'.$cert['btdk'];
	$btkeye=$cert['btmy'];
	if($cert['btos']=='1') {
		$l_ler_a='/etc/hosts';
	} else {
		$l_ler_a='C:\Windows\System32\drivers\etc\hosts';
	}
	include("./class.php");
	//实例化对象
	$api = new bt_api($btipe,$btkeye);
	//获取面板日志
	if($cres['hxc']=='1') {
		$r_datad = $api->get_domain_list($ftr);
		foreach(($r_datad ?? []) as $are) {
			if($are!='' && $are['name']!=$cres['sqldz']) {
				$get_host_hq = $api->hqwjnr($l_ler_a);
				$kh='
';
				//换行符
				$arysz=explode($kh,$get_host_hq['data']);
				foreach($arysz as $val) {
					if(!strpos($val,' '.$are['name']) && $val!='') {
						$ayrt.=$val.$kh;
					}
				}
				$get_host_xg = $api->setwj(array($ayrt,$l_ler_a));
				unset($ayrt);
				unset($val);
				unset($arysz);
				unset($get_host_hq);
			}
		}
	}
	$r_data = $api->delsite($ftr,$sza);
	if($r_data['status']=='1' || $r_data['status']=='true' || $r_data['msg']=='指定站点不存在!') {
		mnbt_log($user,'删除主机','删除ID'.$id.'宝塔成功','删除成功',$DB);
		if($DB->query_prepare("DELETE FROM MN_zj WHERE id=? limit 1", [$id])){
			if (function_exists('mnbt_do_action')) {
				mnbt_do_action('host.deleted', $cres, ['source'=>'admin']);
			}
			json_exit('删除成功');
		} else { mnbt_log($user,'删除主机','删除ID'.$id.'数据库失败','删除失败',$DB); json_exit('删除失败'.$DB->error()); }
	} else {
        mnbt_log($user,'删除主机','删除ID'.$id.'宝塔失败','删除失败：'.($r_data['msg'] ?? '未知错误'),$DB);
	        exit(json_encode(['code'=>"删除失败！宝塔返回信息：{$r_data['msg']}"],256));
	}
	return;
}
if($egn=='zjscxz') {
	//删除多个主机
	include("./class.php");
	$idsz=$_POST['idsz'];
	$hst_ary=array();
	$scqkr=0;
	$scqke=0;
	foreach($idsz as $id) {
		$cres=$DB->get_row_prepare("SELECT * FROM MN_zj WHERE id=? limit 1", [$id]);
		$ftr=$cres['btid'];
		$sza=$cres['sqldz'];
		$yrez=$cres['ssbt'];
		$cert=$DB->get_row_prepare("SELECT * FROM MN_bt WHERE btdh=? limit 1", [$yrez]);
		$btipe=($cert['ptl']=='true'?'https':'http').'://'.$cert['btip'].':'.$cert['btdk'];
		$btkeye=$cert['btmy'];
		//实例化对象
		$api = new bt_api($btipe,$btkeye);
		//获取面板日志
		if($cres['hxc']=='1') {
			$r_datad = $api->get_domain_list($ftr);
			foreach(($r_datad ?? []) as $are) {
				if($are!='' && $are['name']!=$sza) {
					$hst_ary[]=$are['name'];
				}
			}
		}
		$r_data = $api->delsite($ftr,$sza);
		if($r_data['status']=='1' || $r_data['status']=='true') {
			mnbt_log($user,'删除主机','删除ID'.$id.'宝塔成功','删除成功',$DB);
			if($DB->query_prepare("DELETE FROM MN_zj WHERE id=? limit 1", [$id])) $scqke++; else $scqkr++;
		} else {
			$scqkr++;
		}
	}
	if(isset($hst_ary)) {
		$get_host_hq = $api->hqwjnr('/etc/hosts');
		$kh='
';
		//换行符
		function in_aray($xcz,$arrayr) {
			$fh=0;
			foreach($arrayr as $vav) {
				if(strpos($xcz,' '.$vav)) {
					$fh++;
				}
			}
			if($fh>0) {
				return true;
			} else {
				return false;
			}
		}
		$arysz=explode($kh,$get_host_hq['data']);
		foreach($arysz as $val) {
			if(!in_aray($val,$hst_ary) && $val!='') {
				$ayrt.=$val.$kh;
			}
		}
		$get_host_xg = $api->setwj(array($ayrt,'/etc/hosts'));
		unset($ayrt);
		unset($val);
		unset($arysz);
		unset($get_host_hq);
	}
	mnbt_log($user,'删除主机','批量删除成功'.$scqke.'失败'.$scqkr,'删除完成',$DB);
		json_exit($scqke, ['codr' => $scqkr]);
	return;
}
if($egn=='zjxgjl') {
	//修改主机
	$id=daddslashes($_POST['id']);
	$user=daddslashes($_POST['user']);
	$pass=daddslashes($_POST['pass']);
	$sqluser=daddslashes($_POST['sqluser']);
	$sqlpass=daddslashes($_POST['sqlpass']);
	$ymbds=daddslashes($_POST['ymbds']);
	$datar=daddslashes($_POST['datar']);
	$webkj=daddslashes($_POST['webkj']);
	$sqlkj=daddslashes($_POST['sqlkj']);
	$lldx=daddslashes($_POST['llmax']);
	$kg=daddslashes($_POST['kg']);
	$cres=$DB->get_row_prepare("SELECT * FROM MN_zj WHERE id=? limit 1", [$id]);
	$btbh=$cres['ssbt'];
	$cxbt=$DB->get_row_prepare("SELECT * FROM MN_bt WHERE btdh=? limit 1", [$btbh]);
	$btipe=($cxbt['ptl']=='true'?'https':'http').'://'.$cxbt['btip'].':'.$cxbt['btdk'];
	if(is_numeric($ymbds)) {
	} else {
		$ymbds=0;
	}
	include("./class.php");
	//实例化对象
	$api = new bt_api($btipe,$cxbt['btmy']);
	//获取面板日志
	$r_data = $api->setdqsj($cres['btid'],$datar);
	$de=$r_data['status'];
	if($de=='1' || $de=='true') {
		if($kg=='true') {
			$styi='1';
		} else {
			$styi='0';
		}
		$api->setftpzt($cres['ftpid'],$user,$styi);
		$api->setmysqlpass($cres['hxd'],$sqluser,$sqlpass);
		$api->setftppass($cres['ftpid'],$user,$pass);
		if($kg!=$cres['qk']) {
			if($kg=='true') {
				$api->siteqt($cres['btid'],$cres['sqldz'],true);
				$api->setftpzt($cres['ftpid'],$cres['user'],'1');
				if (function_exists('mnbt_do_action')) {
					mnbt_do_action('host.unpaused', $cres, ['source'=>'admin']);
				}
			} else {
				$api->siteqt($cres['btid'],$cres['sqldz'],false);
				$api->setftpzt($cres['ftpid'],$cres['user'],'0');
				if (function_exists('mnbt_do_action')) {
					mnbt_do_action('host.paused', $cres, ['source'=>'admin']);
				}
			}
		}
		if($kg=='true' && strtotime($date)-strtotime($datar)<0 && $datar!='0000-00-00') {
			$api->siteqt($cres['btid'],$cres['sqldz'],true);
			$api->setftpzt($cres['ftpid'],$cres['user'],'1');
		}
		$llyl=json_decode($cres['llmax'],true);
		$llyl['max']=$lldx;
		$llde=json_encode($llyl);
		$s_weba=json_decode($cres['hxa'],true);
		$s_weba['max']=$webkj;
		$s_web=json_encode($s_weba);
		$s_sqla=json_decode($cres['hxb'],true);
		$s_sqla['max']=$sqlkj;
		$s_sql=json_encode($s_sqla);
		mnbt_log($user,'修改主机','修改ID'.$id.'配置','修改成功',$DB);
			if($cres['datae'] != $datar) mnbt_log($user,'主机续费','ID'.$id.'到期'.$cres['datae'].'=>'.$datar,'修改成功',$DB);
		@header('Content-Type: text/html; charset=UTF-8');
		if($DB->query_prepare("update `MN_zj` set `datae` =?, `ymbds` =?, `hxa` =?, `hxb` =?, `sqlpass` =?, `pass` =?, `qk` =?, `llmax` =? where `id`=?", [$datar, $ymbds, $s_web, $s_sql, $sqlpass, $pass, $kg, $llde, $id])){
			if (function_exists('mnbt_do_action') && $cres['datae'] != $datar) {
				$host_row = $DB->get_row_prepare("SELECT * FROM MN_zj WHERE id=? limit 1", [$id]);
				mnbt_do_action('host.renewed', $host_row ?: array_merge($cres, ['datae'=>$datar]), ['source'=>'admin','old_date'=>$cres['datae'],'new_date'=>$datar]);
			}
			json_exit('修改成功');
		} else json_exit('修改失败');
	} else {
		json_exit('修改失败');
	}
	return;
}
if($egn=='addzj') {
	//添加主机
	$btdh=daddslashes($_POST['btdh']);
	$user=daddslashes($_POST['user']);
	$pass=daddslashes($_POST['pass']);
	$datae=$_POST['datae']=='' ? '0000-00-00' : daddslashes($_POST['datae']);
	$webdx=json_encode(array('max'=>daddslashes($_POST['webkj']),'dq'=>0));
	$sqldx=json_encode(array('max'=>daddslashes($_POST['sqlkj']),'dq'=>0));
	;
	$cptype=daddslashes($_POST['cplx']);
	$flowratemax=json_encode(array('max'=>daddslashes($_POST['ll']),'dq'=>0,'statistics'=>false));
	$ymbds=$cptype=='1' ? '1' : daddslashes($_POST['ymbds']);
	$kg=daddslashes($_POST['kg']);
	if($cptype=='1') {
		$cp_eh_lx='CDN';
		$cp_eh_ftp='false';
		$cp_eh_sql='false';
	} else {
		$cp_eh_lx='主机';
		$cp_eh_ftp='true';
		$cp_eh_sql='true';
	}
	$cxbt=$DB->get_row_prepare("SELECT * FROM MN_bt WHERE btdh=? limit 1", [$btdh]);
	$btipe=($cxbt['ptl']=='true'?'https':'http').'://'.$cxbt['btip'].':'.$cxbt['btdk'];
	$btkeye=$cxbt['btmy'];
	$rowe=$DB->get_row_prepare("SELECT * FROM MN_zj WHERE 1 order by id desc limit 1");
	$id=$rowe['id']+1;
	//以下是计算创建网站的名称(防止重复创建失败)
	$hskr=mt_rand(4,10);
	$rqsj=md5($date.$user);
	$wjler=substr($rqsj, $hskr , 3);
	$btserw='mnbt.'.$id.mt_rand(1,999).$wjler;
	include("./class.php");
	//实例化对象
	$api = new bt_api($btipe,$btkeye);
	//目录设置
	$mrwww=$cxbt['btos']=='1' ? $conf['hxi'].'/'.$btserw : $conf['hxo'].'/'.$btserw;
	if($cptype!='1') {
		if(mb_strlen($user)<6 || mb_strlen($pass)<6)json_exit('错误！账号和密码位数均不能小于6位！');
		if($DB->get_row_prepare("SELECT id FROM MN_zj WHERE user=? limit 1", [$user]))json_exit('错误！该账号已存在！请更换账号！');
		// 本地查重，BT面板的数据库/FTP重复由webkt接口自行处理并返回错误
	}
	//开通网站
	$r_data = $api->webkt($user,$pass,$btserw,$cp_eh_lx,$cp_eh_ftp,$cp_eh_sql,$conf['hxu'],$mrwww);
	$cjqk=$r_data['siteStatus'];
	//创建情况
	$zdide=$r_data['siteId'];
	//站点ID
	//$api->mysqlqx($user,'%');
	//新版本不再需要将MySQL设置为外部访问这种危险行为。
	if($cjqk) {
		//设置到期时间
		$r_datan = $api->setdqsj($zdide,$datae);
		$de=$r_datan['status'];
		if($de=='1' || $de=='true') {
			//获取FTP/数据库列表
			$r_datn = $api->sjlist('ftps');
			$r_datp = $api->sjlist('databases');
			$aedfs = '0'; $sqlfs = '0';
			if(isset($r_datn['data']) && is_array($r_datn['data'])) {
				foreach($r_datn['data'] as $val) {
					if($val['name']===$user) {
						$aedfs=$val['id'];
						break;
					}
				}
			}
			if(isset($r_datp['data']) && is_array($r_datp['data'])) {
				foreach($r_datp['data'] as $val) {
					if($val['name']===$user) {
						$sqlfs=$val['id'];
						break;
					}
				}
			}
			$rowe=$DB->get_row_prepare("SELECT * FROM MN_zj WHERE 1 order by id desc limit 1");
			$id=$rowe['id']+1;
			mnbt_log($user,'添加主机','添加ID'.$id.'宝塔成功','添加成功',$DB);
			if($DB->query_prepare("INSERT INTO `MN_zj` (`id`, `ssbt`, `user`, `pass`, `sqluser`, `sqlpass`, `data`, `datae`, `qk`, `btid`, `sqldz`, `ftpid`, `ymbds`, `hxa`, `hxb`, `hxc`, `hxd`, `llmax`) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)", [$id, $btdh, $user, $pass, $user, $pass, $date, $datae, $kg, $zdide, $btserw, $aedfs, $ymbds, $webdx, $sqldx, $cptype, $sqlfs, $flowratemax])){
				$host_row = $DB->get_row_prepare("SELECT * FROM MN_zj WHERE id=? limit 1", [$id]);
				if (function_exists('mnbt_do_action')) {
					mnbt_do_action('host.created', $host_row ?: ['id'=>$id,'user'=>$user,'ssbt'=>$btdh,'btid'=>$zdide,'sqldz'=>$btserw], ['source'=>'admin']);
				}
				json_exit('添加成功');
			} else { mnbt_log($user,'添加主机','添加ID'.$id.'数据库失败','添加失败',$DB); json_exit('添加失败'.$DB->error()); }
		} else {
			mnbt_log($user,'添加主机','添加'.$user.'到期设置失败','添加失败',$DB);
				json_exit('添加失败');
		}
	} else {
		mnbt_log($user,'添加主机','添加'.$user.'宝塔创建失败','添加失败：'.($r_data['msg'] ?? '未知错误'),$DB);
			json_exit('网站创建失败！'.$r_data['msg']);
	}
	return;
}
if($egn=='listzj') {
	//主机列表
	$sorting=strtoupper($_POST['sortOrder']??'')==='DESC'?'DESC':'ASC';
	$paixu=preg_replace('/[^a-zA-Z0-9_]/','',$_POST['sort']??'id')?:'id';
	$pagesize=intval($_POST['limit']);
	$pageu=(intval($_POST['page']-1)) * $pagesize;
	$where=json_decode($_POST['where'],true);
    $pswhere='';
    $param_arr=[];
    if($where && $where['name']!=false && $where['type']!=false && $where['value']!=false){
        if($where['type']!='1' && $where['type']!='2')exit(json_encode(['code'=>4,'msg'=>'搜索方式错误！只能为模糊或者精确搜索！']));
        $zdm=['ssbt','sqldz','user'];
        if(!in_array($where['name'],$zdm))exit(json_encode(['code'=>4,'msg'=>'错误！不存在的搜索字段！']));
        $val=$where['value'];
        $pswhere='and '.$where['name'].($where['type']=='1'?"=?":" LIKE ?");
        $param_arr[]=$where['type']=='1'?$val:'%'.$val.'%';
    }
	$countdata=$DB->count_prepare("SELECT count(*) from MN_zj WHERE 1 {$pswhere}", $param_arr);
	$data=["total"=>$countdata];
	$data["rows"]=$DB->get_all_prepare("SELECT * FROM MN_zj where 1 {$pswhere} order by $paixu $sorting limit $pageu,$pagesize", $param_arr);
	exit(json_encode($data));
	return;
}
return;
