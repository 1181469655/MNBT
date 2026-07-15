<?php
@header('Content-Type: application/json; charset=UTF-8');
include("../MPHX/common.php");
include("../MPHX/BL.php");
include("./api.class.php");

function api_json_exit($code, $msg, $extra = []) {
    $result = array_merge([
        'success' => ((int)$code === 200),
        'code' => $code,
        'msg' => $msg,
    ], $extra);
    exit(json_encode($result, JSON_UNESCAPED_UNICODE));
}

function api_lifecycle_log($type, $content, $status = '记录') {
    global $DB, $user, $bh;
    $logUser = $user ?: '外部API';
    mnbt_log($logUser, $type, 'API-'.$bh.' '.$content, $status, $DB);
}

if($conf['apiqk']=='false')api_json_exit(100, '错误！APi已关闭！开启方法：系统后台的系统设置->api密钥处开启即可');
$gn=$_GET['gn'] ?? '';
$bh=$_POST['mn_bh'] ?? '';
$key=$_POST['mn_key'] ?? '';
$keye=$_POST['mn_keye'] ?? '';
$mn_vser=$_POST['mn_vs'] ?? 0;
$user=$_POST['username'] ?? '';
if($mn_vser<15)api_json_exit(300, '您的版插件本不支持当前MNBT版本！当前MNBT版本：'.intval($WEBQB/100).'，插件所支持的版本'.$mn_vser);
if(empty($gn) || empty($bh) || empty($key) || empty($keye) || empty($user))api_json_exit(100, '错误！表单填写不完整！');
if($key!=$conf['api']){
    mnbt_log('外部API','API鉴权','API-'.$bh.' '.$user.' 系统密钥错误','鉴权失败',$DB);
    api_json_exit(100, '错误！您的密钥与网站APi密钥不匹配！请您仔细核对后再试！');
}
$cert=$DB->get_row_prepare("SELECT * FROM MN_bt WHERE btdh=? limit 1", [$bh]);
$et_zj=$DB->get_row_prepare("SELECT * FROM MN_zj WHERE user=? limit 1", [$user]);
if($cert=='' || $cert['qk']=='false')api_json_exit(100, '错误！该宝塔不存在或该宝塔已经被关闭');
$adyjm=$cert['ktmy'].$cert['qmk'];$mdjm=md5($adyjm);
if($keye!=$mdjm){
    mnbt_log('外部API','API鉴权','API-'.$bh.' '.$user.' 宝塔调用密钥错误','鉴权失败',$DB);
    api_json_exit(100, '错误！您所传输的宝塔调用密钥与该宝塔的调用密钥不匹配！');
}
$btipe=($cert['ptl']=='true'?'https':'http').'://'.$cert['btip'].':'.$cert['btdk'];
$btkeye=$cert['btmy'];

if($gn=='cfif'){
    api_json_exit(200, '连接验证成功！');
}elseif($gn=='kt'){
    $pass=daddslashes($_POST['password'] ?? '');
    $cptype=daddslashes($_POST['type'] ?? '2');
    $flowratemax=json_encode(array('max'=>daddslashes($_POST['sizemax'] ?? 0),'dq'=>0,'statistics'=>false));
    $datae=($_POST['dqtime'] ?? '0')=='0' ? '0000-00-00' : daddslashes($_POST['dqtime']);
    $webdx=json_encode(array('max'=>daddslashes($_POST['webdx'] ?? 0),'dq'=>0));
    $sqldx=json_encode(array('max'=>daddslashes($_POST['sqldx'] ?? 0),'dq'=>0));
    $ymbds=$cptype=='1' ? '1' : daddslashes($_POST['ymbds'] ?? 0);
    if($et_zj!='' || $et_zj!=false){
        api_lifecycle_log('API开通主机','开通'.$user.'失败：主机已存在','开通失败');
        api_json_exit(100, '错误！该主机已经存在，请重新开通！');
    }
    if($cptype=='1'){$cplxch='CDN'; $cp_eh_ftp='false'; $cp_eh_sql='false';}else{$cplxch='主机'; $cp_eh_ftp='true'; $cp_eh_sql='true';}
    $api = new bt_api($btipe,$btkeye);
    if($cptype!='1'){
        if(mb_strlen($user)<6 || mb_strlen($pass)<6){
            api_lifecycle_log('API开通主机','开通'.$user.'失败：账号或密码过短','开通失败');
            api_json_exit(100, '错误！账号和密码位数均不能小于6位！');
        }
        if($DB->get_row_prepare("SELECT id FROM MN_zj WHERE user=? limit 1", [$user])){
            api_lifecycle_log('API开通主机','开通'.$user.'失败：账号重复','开通失败');
            api_json_exit(100, '错误！该账号已存在！请更换账号！');
        }
    }
    $rowe=$DB->get_row_prepare("SELECT * FROM MN_zj WHERE 1 order by id desc limit 1");
    $id=$rowe['id']+1;
    $hskr=mt_rand(4,10);
    $rqsj=md5($date.$user);
    $wjler=substr($rqsj, $hskr , 3);
    $btserw='mnbt.'.$id.mt_rand(1,999).$wjler;
    $mrwww=$cert['btos']=='1' ? $conf['hxi'].'/'.$btserw : $conf['hxo'].'/'.$btserw;
    $r_data = $api->webkt($user,$pass,$btserw,$cplxch,$cp_eh_ftp,$cp_eh_sql,$conf['hxu'],$mrwww);
    $cjqk=$r_data['siteStatus'] ?? false;
    $zdide=$r_data['siteId'] ?? 0;
    if($cjqk=='1' || $cjqk=='true' || $cjqk===true){
        if(($_POST['dqtime'] ?? '0')!='0'){
            $r_datan = $api->setdqsj($zdide,$datae);
            $de=$r_datan['status'] ?? false;
            if(!($de=='1' || $de=='true' || $de===true)){
                api_lifecycle_log('API开通主机','开通'.$user.'失败：到期时间设置失败','开通失败');
                api_json_exit(100, '错误！网站创建成功但到期时间设置失败！');
            }
        }
        $r_datn = $api->sjlist('ftps');
        $r_datp = $api->sjlist('databases');
        $aedfs = '0'; $sqlfs = '0';
        foreach(($r_datn['data'] ?? []) as $val){ if($val['name']===$user){ $aedfs=$val['id']; break; } }
        foreach(($r_datp['data'] ?? []) as $val){ if($val['name']===$user){ $sqlfs=$val['id']; break; } }
        if($DB->query_prepare("INSERT INTO `MN_zj` (`id`, `ssbt`, `user`, `pass`, `sqluser`, `sqlpass`, `data`, `datae`, `qk`, `btid`, `sqldz`, `ftpid`, `ymbds`, `hxa`, `hxb`, `hxc`, `hxd`, `llmax`) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)", [$id, $bh, $user, $pass, $user, $pass, $date, $datae, 'true', $zdide, $btserw, $aedfs, $ymbds, $webdx, $sqldx, $cptype, $sqlfs, $flowratemax])){
            api_lifecycle_log('API开通主机','开通'.$user.'成功，站点'.$btserw,'开通成功');
            $host_row = $DB->get_row_prepare("SELECT * FROM MN_zj WHERE id=? limit 1", [$id]);
            if (function_exists('mnbt_do_action')) {
                mnbt_do_action('host.created', $host_row ?: ['id'=>$id,'user'=>$user,'ssbt'=>$bh,'btid'=>$zdide,'sqldz'=>$btserw], ['source'=>'api']);
            }
            api_json_exit(200, '主机开通成功！');
        } else {
            $api->delsite($zdide,$btserw);
            api_lifecycle_log('API开通主机','开通'.$user.'失败：数据库写入失败','开通失败');
            api_json_exit(100, '错误！网站添加失败(数据写入数据库失败)！请尝试重试或加官Q群询问！');
        }
    }else{
        api_lifecycle_log('API开通主机','开通'.$user.'失败：'.($r_data['msg'] ?? '宝塔创建失败'),'开通失败');
        api_json_exit(100, '错误！网站创建失败！宝塔返回信息：'.($r_data['msg'] ?? '未知错误'));
    }
}elseif($gn=='zt'){
    $api = new bt_api($btipe,$btkeye);
    $api->siteqt($et_zj['btid'],$et_zj['sqldz'],false);
    $api->setftpzt($et_zj['ftpid'],$et_zj['user'],'0');
    $DB->query_prepare("update `MN_zj` set `qk` =? where `user`=?", ['false', $user]);
    if (function_exists('mnbt_do_action')) {
        mnbt_do_action('host.paused', $et_zj, ['source'=>'api']);
    }
    api_json_exit(200, '主机暂停成功！');
}elseif($gn=='xf'){
    $x_dq_date=($_POST['setdate'] ?? '0')=='0' ? '0000-00-00' : $_POST['setdate'];
    $old_date=$et_zj['datae'] ?? '';
    $api = new bt_api($btipe,$btkeye);
    $r_data = $api->setdqsj($et_zj['btid'],$x_dq_date);
    if(strtotime($date)-strtotime($x_dq_date)<0 && $x_dq_date!='0000-00-00' && $et_zj['qk']){
        $api->siteqt($et_zj['btid'],$et_zj['sqldz'],true);
        $api->setftpzt($et_zj['ftpid'],$et_zj['user'],'1');
    }
    if($DB->query_prepare("update `MN_zj` set `datae` =? where `user`=?", [$x_dq_date, $user])){
        api_lifecycle_log('API续费主机','续费'.$user.' '.$old_date.'=>'.$x_dq_date,'续费成功');
        $host_row = $DB->get_row_prepare("SELECT * FROM MN_zj WHERE user=? limit 1", [$user]);
        if (function_exists('mnbt_do_action')) {
            mnbt_do_action('host.renewed', $host_row ?: array_merge($et_zj, ['datae'=>$x_dq_date]), ['source'=>'api','old_date'=>$old_date,'new_date'=>$x_dq_date]);
        }
        api_json_exit(200, '主机续费成功！');
    }
    api_lifecycle_log('API续费主机','续费'.$user.'数据库写入失败','续费失败');
    api_json_exit(100, '主机续费失败，数据库写入失败！');
}elseif($gn=='jc'){
    $api = new bt_api($btipe,$btkeye);
    $api->siteqt($et_zj['btid'],$et_zj['sqldz'],true);
    $api->setftpzt($et_zj['ftpid'],$et_zj['user'],'1');
    if($DB->query_prepare("update `MN_zj` set `qk` =? where `user`=?", ['true', $user])){
        if (function_exists('mnbt_do_action')) {
            mnbt_do_action('host.unpaused', $et_zj, ['source'=>'api']);
        }
        api_json_exit(200, '主机暂停解除成功！');
    }
    else api_json_exit(100, '主机暂停解除成功！但是写入数据库时出现错误！请站长排查！');
}elseif($gn=='tz'){
    $l_ler_a=$cert['btos']=='1' ? '/etc/hosts' : 'C:\Windows\System32\drivers\etc\hosts';
    $api = new bt_api($btipe,$btkeye);
    if($et_zj['hxc']=='1'){
        $r_datad = $api->get_domain_list($et_zj['btid']);
        foreach(($r_datad ?? []) as $are){
            if($are!='' && $are['name']!=$et_zj['sqldz']){
                $get_host_hq = $api->hqwjnr($l_ler_a);
                $kh="\n";
                $arysz=explode($kh,$get_host_hq['data']);
                foreach($arysz as $val){ if(!strpos($val,' '.$are['name']) && $val!=''){ $ayrt.=$val.$kh; } }
                $api->setwj(array($ayrt,$l_ler_a));
                unset($ayrt,$val,$arysz,$get_host_hq);
            }
        }
    }
    $r_data = $api->delsite($et_zj['btid'],$et_zj['sqldz']);
    if($r_data['status']){
        if($DB->query_prepare("DELETE FROM MN_zj WHERE user=? limit 1", [$user])){
            api_lifecycle_log('API删除主机','删除'.$user.'成功','删除成功');
            if (function_exists('mnbt_do_action')) {
                mnbt_do_action('host.deleted', $et_zj, ['source'=>'api']);
            }
            api_json_exit(200, '主机删除成功！');
        }
        api_lifecycle_log('API删除主机','删除'.$user.'数据库写入失败','删除失败');
        api_json_exit(100, '主机删除成功，但是写入数据库失败，请站长排查！');
    }else{
        api_lifecycle_log('API删除主机','删除'.$user.'失败：'.($r_data['msg'] ?? '未知错误'),'删除失败');
        api_json_exit(100, '主机删除失败！因为'.($r_data['msg'] ?? '未知错误'));
    }
}elseif($gn=='czmm'){
    $x_up_pass=$_POST['password'];
    $api = new bt_api($btipe,$btkeye);
    $api->setftppass($et_zj['ftpid'],$user,$x_up_pass);
    if($DB->query_prepare("update `MN_zj` set `pass` =? where `user`=?", [$x_up_pass, $user]))api_json_exit(200, '主机FTP及控制面板登陆密码重置成功！');
    else api_json_exit(100, '主机FTP密码重置成功！但是控制面板登陆密码重置失败，因为数据写入数据库失败！请站长排查');
}elseif($gn == 'zjmode'){
    $zjdata=$DB->get_row_prepare("SELECT * FROM MN_zj WHERE user=?", [$user]);
    if($zjdata == null) api_json_exit(100, '不存在主机用户名');
    $hxa_array = json_decode($zjdata['hxa'],true);
    $hxb_array = json_decode($zjdata['hxb'],true);
    $llmax_array = json_decode($zjdata['llmax'],true);
    $hxa_array['max'] = $_POST['websize'];
    $hxb_array['max'] = $_POST['sqlsize'];
    $llmax_array['max'] = $_POST['ll'];
    if($DB->query_prepare("UPDATE `MN_zj` SET `hxa` = ?, `hxb` = ?, `llmax` = ? WHERE `user` = ?", [json_encode($hxa_array), json_encode($hxb_array), json_encode($llmax_array), $user])) api_json_exit(200, '主机修改成功');
    api_json_exit(100, '主机修改失败我也不知道什么问题，请联系开发者');
}else{
    api_json_exit(100, '此功能不存在！请仔细核对开发文档！');
}
?>
