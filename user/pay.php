<?php
@header('Content-Type: text/html; charset=UTF-8');
include("../MPHX/common.php");
if($islogins==1){}else exit("<script language='javascript'>window.location.href='./login.php';</script>");
mnbt_csrf_validate_request();

// V1.81 P3: 支付方式 type 格式为 {plugin}__{method}，如 epay__alipay
$type = isset($_POST['type']) ? $_POST['type'] : '';
if ($type === '' || !function_exists('mnbt_pay_parse_type') || !mnbt_pay_parse_type($type)) {
	exit("<script language='javascript'>alert('请选择有效的支付方式');history.go(-1);</script>");
}

$pay_lx = isset($_POST['pay_lx']) ? $_POST['pay_lx'] : '';
if($pay_lx=='yjbs'){
$bs_id=daddslashes($_POST['id']);
$bs_cx = $DB->get_row_prepare("SELECT * FROM `MN_bs` WHERE `id` = ? limit 1", [$bs_id]);
}else{
// ymgm（域名购买）已迁移至 domain_shop 插件，由 index.php?_r=/domain/buy 路由处理
exit('404 NOT！');
}
$out_trade_no = date("YmdHis").mt_rand(100,999);
$name = 'MNBT订单支付支付';
$money = $bs_cx['jg'];
$dada = date("Y-m-d H-i-s");
$ip = $_SERVER["REMOTE_ADDR"] ?? '127.0.0.1';

if($pay_lx=='yjbs'){
//一键部署
if($bs_cx['qk']==false)exit("<script language='javascript'>alert('该程序已下架！');history.go(-1);</script>");
if(in_array($yhc['user'],json_decode($bs_cx['tj'],true)))exit("<script language='javascript'>alert('您已购买该程序！');history.go(-1);</script>");
$cs=json_encode(array('user'=>$yhc['user'],'gmid'=>$_POST['id']),256);               //user：购买该程序的主机账号；gmid：部署程序的id
$row1=$DB->get_row_prepare("SELECT * FROM MN_dd WHERE 1 order by id desc limit 1");
$id = $row1['id']+1;
$DB->query_prepare("INSERT INTO `MN_dd` (`id`, `cs`, `date`, `zffs`, `je`, `ddh`, `lx`, `qk`, `ip`) VALUES (?,?,?,?,?,?,?,?,?)", [$id, $cs, $date, $type, $money, $out_trade_no, 'yjbs', 'false', $ip]);

}else{exit('404 NOT！');}

// V1.81 P3: 分发到支付插件
$order_context = array(
	'out_trade_no' => $out_trade_no,
	'name'         => $name,
	'money'        => $money,
	'type'         => $type,
	'siteurl'      => $siteurl,
		'pay_lx'       => $pay_lx,
);

$html = mnbt_pay_dispatch_gateway($type, $order_context);
if ($html === false) {
	exit("<script language='javascript'>alert('支付方式不可用，请检查支付插件是否已启用');history.go(-1);</script>");
}
echo $html;
?>
