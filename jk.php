<?php
/*
 * 网页空间及数据库空间和流量使用情况监控文件
 * 会自动判断是否超出大小以及暂停超出的站点
 * 建议10分钟执行一次（不建议低于1分钟）
 * 访问地址：http://搭建此系统的网站域名/jk.php?my=后台设置的api密钥&gn=需要监控的功能(wq为数据库空间和网页，fh为流量监控)
 * 2022©梦奈
 */
include("./MPHX/common.php");
include("./cf_up.php");
if($mn_conf['xf']['qk'])
{
    exit('由于更新后必须进行一次系统修复，暂时无法使用这功能！');
}
if($_GET['my']!=$conf['api'])
{
    exit('密钥错误');
}

?>
<?php
include("./class.php");

// ========== 辅助函数 ==========
function jk_get_bt_config($ssbt, &$bt_cache) {
    if (!isset($bt_cache[$ssbt])) return null;
    return $bt_cache[$ssbt];
}

function jk_build_bt_url($cert) {
    return ($cert['ptl']=='true'?'https':'http').'://'.$cert['btip'].':'.$cert['btdk'];
}

function jk_get_os_prefix($cert) {
    global $conf;
    return $cert['btos']=='1' ? $conf['hxi'].'/' : $conf['hxo'].'/';
}

function jk_is_expired($yhc, $date) {
    if (strtotime($date)-strtotime($yhc['datae'])>0 && $yhc['datae']!='0000-00-00') return true;
    if ($yhc['qk']==false) return true;
    return false;
}

function jk_parse_size($raw) {
    $suffix = substr($raw, -2);
    $suffix_lower = strtolower($suffix);
    if (in_array($suffix_lower, ['kb', 'mb'])) {
        $val = str_ireplace($suffix,'',$raw);
        return $suffix_lower === 'kb' ? $val : $val * 1000;
    } elseif (in_array($suffix_lower, ['b '])) {
        return str_ireplace($suffix,'',$raw) / 1000;
    }
    return '0';
}

// 检查是否需要暂停或恢复站点
function jk_toggle_site($over_limit, $was_under, $api, $yhc, &$ztzj, &$ztyh_arr, $other_ok=true) {
    if ($over_limit) {
        if ($was_under) {
            $api->ztweb($yhc['btid'], $yhc['sqldz']);
            $api->ftpxg($yhc['ftpid'], $yhc['user'], '0');
        }
        $ztzj++;
        $ztyh_arr[] = $yhc['user'];
    } else {
        if (!$was_under && $other_ok) {
            $api->qdweb($yhc['btid'], $yhc['sqldz']);
            $api->ftpxg($yhc['ftpid'], $yhc['user'], '1');
        }
    }
}

// 预先加载所有 BT 配置，按 btdh 索引
$bt_cache = [];
$bt_rows = $DB->get_all_prepare("SELECT * FROM MN_bt");
if ($bt_rows) {
    foreach ($bt_rows as $bt) {
        $bt_cache[$bt['btdh']] = $bt;
    }
}

if($_GET['gn']=='web'){		//WEB监控
$all_zj=$DB->get_all_prepare("SELECT * FROM MN_zj");
$ztzj='0';
$ztyh_arr=[];
foreach ($all_zj as $yhc)
{
if(jk_is_expired($yhc, $date)) continue;

$cert=jk_get_bt_config($yhc['ssbt'], $bt_cache);
if(!$cert) continue;
$api = new bt_api(jk_build_bt_url($cert), $cert['btmy']);
$r_data = $api->webkjjs(jk_get_os_prefix($cert).$yhc['sqldz']);
$webkj=$r_data['size']/(1024*1000);

$web_kjr=json_decode($yhc['hxa'],true);
$r_js=$web_kjr;
$r_js['dq']=$webkj;
$DB->query_prepare("update `MN_zj` set `hxa` =? where `id`=?", [json_encode($r_js,256), $yhc['id']]);

$k_qa=json_decode($yhc['llmax'],true);
$k_qe=json_decode($yhc['hxb'],true);
$other_ok = $k_qa['dq']<=$k_qa['max'] && $k_qe['dq']<=$k_qe['max'];
jk_toggle_site($webkj>$web_kjr['max'], $web_kjr['dq']<$web_kjr['max'], $api, $yhc, $ztzj, $ztyh_arr, $other_ok);
}
echo '执行完成，有'.$ztzj.'个主机由于网页空间超过被暂停他们分别是：'.implode('，', $ztyh_arr);

}elseif($_GET['gn']=='sql'){		//数据库空间监控

$all_zj=$DB->get_all_prepare("SELECT * FROM MN_zj");
$ztzj='0';
$ztyh_arr=[];
foreach ($all_zj as $yhc)
{
if(jk_is_expired($yhc, $date)) continue;

$cert=jk_get_bt_config($yhc['ssbt'], $bt_cache);
if(!$cert) continue;
$api = new bt_api(jk_build_bt_url($cert), $cert['btmy']);
$r_datb = $api->sqlkjhq($yhc['sqluser']);
$sqlkj=jk_parse_size($r_datb['data_size']);
$adft=$sqlkj/1024;

$sql_kjr=json_decode($yhc['hxb'],true);
$r_js=$sql_kjr;
$r_js['dq']=$adft;
$DB->query_prepare("update `MN_zj` set `hxb` =? where `id`=?", [json_encode($r_js,256), $yhc['id']]);

$k_qa=json_decode($yhc['llmax'],true);
$k_qe=json_decode($yhc['hxa'],true);
$other_ok = $k_qa['dq']<=$k_qa['max'] && $k_qe['dq']<=$k_qe['max'];
jk_toggle_site($adft>$sql_kjr['max'], $sql_kjr['dq']<$sql_kjr['max'], $api, $yhc, $ztzj, $ztyh_arr, $other_ok);
}
echo '执行完成，有'.$ztzj.'个主机由于数据库空间超过被暂停他们分别是：'.implode('，', $ztyh_arr);

}elseif($_GET['gn']=='fh'){		//流量监控
$all_zj=$DB->get_all_prepare("SELECT * FROM MN_zj");
$ztzj='0';
$ztyh_arr=[];
foreach ($all_zj as $yhc)
{
if(jk_is_expired($yhc, $date)) continue;

$cert=jk_get_bt_config($yhc['ssbt'], $bt_cache);
if(!$cert) continue;
$api = new bt_api(jk_build_bt_url($cert), $cert['btmy']);
$r_js=json_decode($yhc['llmax'],true);
$k_qa=json_decode($yhc['hxa'],true);
$k_qe=json_decode($yhc['hxb'],true);
$s_data=$api->getlog($yhc['sqldz']);
if($s_data['status'] && $s_data['msg']!=''){
$sfyr=explode(' - - ',$s_data['msg']);
unset($sfyr[0]);
$g_size=0;
$latest_ts='';
foreach($sfyr as $vfm){
preg_match('/\[(.*?)\]/', $vfm, $tm);
if(!($tm[1]??''))continue;
if($tm[1]<=$r_js['statistics'])continue;
$e_size=explode(' ',$vfm);
if(!is_numeric($e_size[6]))continue;
$g_size+=$e_size[6];
if($tm[1]>$latest_ts)$latest_ts=$tm[1];
}
$r_jy=$r_js;
$r_js['dq']+=$g_size;
$r_js['statistics']=$latest_ts;
$DB->query_prepare("update `MN_zj` set `llmax` =? where `id`=?", [json_encode($r_js,256), $yhc['id']]);

$max_bytes=$r_js['max']*1024*1024*1024;
$other_ok = $k_qa['dq']<=$k_qa['max'] && $k_qe['dq']<=$k_qe['max'];
jk_toggle_site($r_js['dq']>=$max_bytes, $r_jy['dq']<$max_bytes, $api, $yhc, $ztzj, $ztyh_arr, $other_ok);
}
}
echo '执行完成，有'.$ztzj.'个主机由于流量超过被暂停他们分别是：'.implode('，', $ztyh_arr);

}elseif($_GET['gn']=='fhq'){		//清除统计的流量使用量（推荐每个月执行一次）
$all_zj=$DB->get_all_prepare("SELECT * FROM MN_zj");
foreach ($all_zj as $yhc)
{
if(jk_is_expired($yhc, $date)) continue;
$r_js=json_decode($yhc['llmax'],true);
if ($r_js['dq'] > 0) {
    $month = date('Y-m');
    if (!isset($r_js['history'])) $r_js['history'] = [];
    $r_js['history'][$month] = $r_js['dq'];
    $history = $r_js['history'];
    ksort($history);
    if (count($history) > 12) $history = array_slice($history, -12, null, true);
    $r_js['history'] = $history;
}
$r_js['dq']=0;
$DB->query_prepare("update `MN_zj` set `llmax` =? where `id`=?", [json_encode($r_js,256), $yhc['id']]);
}
echo '执行完成，所有主机的月使用流量清零完毕！';

}
elseif($_GET['gn'] == "ywjkdel")
{
    include_once("./mail.php");
    $all_zj=$DB->get_all_prepare("SELECT * FROM MN_zj");
    foreach ($all_zj as $yhc){
        $cert=jk_get_bt_config($yhc['ssbt'], $bt_cache);
        if(!$cert) continue;
        $api = new bt_api(jk_build_bt_url($cert), $cert['btmy']);
        
        if($conf['ymjkkg'] == "true" || $conf['mtyxfskg'])
        {
            $r_dataaa = $api->Getymlist($yhc['btid']);
            $ymlistcount = count($r_dataaa);
            
            if($ymlistcount == 1 && $r_dataaa[0]['name'] == $yhc["sqldz"])
            {
                echo $yhc['user'] . "没有绑定域名";
                echo '<br>';
                $create_time = new DateTime($yhc['data']);
                $dq_time = new DateTime();
                $xc_time = $dq_time->diff($create_time);
                $xxx = $xc_time->days;
                if($conf['ymjkkg']=="true")
                {
                    if($yhc['mailuser'] != null || $yhc['mailuser'] != "")
                    {
                        if($xc_time->days > $conf['ymjktsyz'])
                        {
                            
                            if($conf['optionzc'] == "stop")
                            {
                                $r_dataa = $api->stopjq($yhc['btid'],$yhc['sqldz']);
                                echo($yhc['user'].$r_dataa['msg']);
                                $message = "检测到".$yhc['user']. "的主机".$xxx."天内未使用超过了预定的天数已经暂停机器";
                            }
                            elseif($conf['optionzc'] == "del")
                            {
                                $r_dataa = $api->delsite($yhc['btid'],$yhc['sqldz']);
                                echo($yhc['sqldz'].$r_dataa['msg']);
                                $message = "检测到".$yhc['user']. "的主机".$xxx."天内未使用超过了预定的天数已经删除机器";
                            }
                            else
                            {
                                echo("数据库配置不对,没有读取到正常的配置");
                            }
                            if(sendEmail($yhc['mailuser'],"MN系统",$message))
                            {
                                echo "邮箱发送成功";
                            }
                            else{
                                echo "邮箱发送失败";
                            }
                            
                        } 
                        else
                        {
                            $message = "检测到".$yhc['user']. "的主机".$xxx."天内未使用,超过".$conf['ymjktsyz']."天将会暂停或删除";
                            sendEmail($yhc['mailuser'],"MN系统",$message);
                        }
                    }
                    else
                    {
                        echo "邮箱为空";
                    }

                }
                else
                {
                    if($conf['mtyxfskg'] == "true")
                    {
                        $message = "检测到".$yhc['user']. "的主机天".$xxx."未使用请尽快使用";
                        sendEmail($yhc['mailuser'],"MN系统",$message);
                    }
                }
                
            }
        }
    }
}
else{
exit('该功能不存在！');
}
?>
