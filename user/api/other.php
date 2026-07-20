<?php
if($egn == "fdlkg")
{
    include("../class.php");
    $id = $zjid;
    $name = $yhc['sqldz'];
    $fix = $_POST['fix'] ?? '';
    $domains = $_POST['domains'] ?? '';
    $return_rule = $_POST['return_rule'] ?? '';
    $httpsta = $_POST['http_status'] ?? '';
    $status = $_POST['status'] ?? '';
	$bt_api = new bt_api($btipe,$btkeye);
	$r_data = $bt_api->Setfdlkg($id,$name,$fix,$domains,$status,$return_rule,$httpsta) ?: [];
	json_exit($r_data['msg']??'');
	return;
}
if($egn == "getfdl")
{
    include("../class.php");
	$bt_api = new bt_api($btipe,$btkeye);
	$r_data = $bt_api->getfdlkg($zjid,$yhc['sqldz']) ?: [];
	exit(json_encode($r_data));
	return;
}
if($egn == "mailbd")
{
    $mailuser = daddslashes($_POST['mail']);
    $user = $yhc['user'];
    if($DB->query_prepare("UPDATE `MN_zj` SET `mailuser` = ? WHERE `user` = ?", [$mailuser, $user]))
    {
        json_exit('绑定成功');
    }
    else
    {
        json_exit('绑定失败,请联系开发者查询失败原因');
    }
    return;
}
if($egn == "fzjh")
{
    include("../class.php");
    $bt_api = new bt_api($btipe,$btkeye);
	$r_data = $bt_api->Getnginx($yhc['sqldz']) ?: [];
	json_exit($r_data['msg']??'');
	return;
}
if($egn=='indexconf'){
    $webkj=json_decode($yhc['hxa'] ?? '', true) ?: [];
    $sqlkj=json_decode($yhc['hxb'] ?? '', true) ?: [];
    $llskj=json_decode($yhc['llmax'] ?? '', true) ?: [];
    include("../class.php");
    $apist = new bt_api_set($btipe,$btkeye);
	$r_data = $apist->btapi_listphp();
	if(is_array($r_data)) {
		unset($r_data[0]);
		unset($r_data[1]);
	} else { $r_data = []; }
	$r_datc = $apist->btapi_phpnowz($yhc['sqldz'] ?? '');
	$sitexx = $apist->sitemsg($yhc['sqldz'] ?? '');
    $site_msg = $sitexx['msg'] ?? [];
    $arr=[];
    $arr['qk'] = is_array($site_msg) ? ($site_msg['status'] ?? null) : null;
    $arr['gg']=$conf['gg'] ?? '';
    $arr['type']='2';
    $arr['web']=$webkj;
    $arr['sql']=$sqlkj;
    $arr['lls']=$llskj;
    $arr['config']['url']=$yhc['ymbds'] ?? '';
    $ftpdz = $cert['ftpdz'] ?? false;
    $arr['config']['ftp']['host']=$ftpdz==false ? ($cert['btip'] ?? '') : $ftpdz;
    $arr['config']['ftp']['user']=$yhc['user'] ?? '';
    $arr['config']['ftp']['pass']=$yhc['pass'] ?? '';
    $arr['config']['sql']['user']=$yhc['sqluser'] ?? '';
    $arr['config']['sql']['pass']=$yhc['sqlpass'] ?? '';
    $arr['php']=['dq'=>$r_datc['phpversion'] ?? '', 'list'=>$r_data];
    exit(json_encode(["qk"=>1,'code'=>"获取成功！",'msg'=>$arr],256));
    return;
}
