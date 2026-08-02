<?php

function formcl($cxform,$yhform){      //用户填写的表单处理
    $arr=[];
    if (!is_array($yhform)) $yhform = [];
    foreach (json_decode($cxform,true) ?: [] as $va=>$val){
    $userfmdata=(isset($yhform[$va]) && is_array($yhform[$va])) ? ($yhform[$va][$val['cz']] ?? null) : null;       //获取用户提交的数据
    if($val['bt']=='是' && $userfmdata==null)return ["code"=>0,"msg"=>"错误，表单未填写完整"];
    if($val['srlx']=='数字' && !is_numeric($userfmdata))return ["code"=>0,"msg"=>"错误，表单未按照要求填写！"];
    if(is_numeric($val['cdxz']) && mb_strlen($userfmdata)>$val['cdxz'])return ["code"=>0,"msg"=>"错误，表单内容长度已超过预设值".$val['cdxz']."个字符！"];
    $arr[$val['blx']]=$userfmdata;
    }
    return $arr;
}

function tihs($data,$arr=false,$yhc){            //替换数据
    //以下为替换系统提供的操作选项
    $data=str_ireplace('[cn_host]','localhost',$data);
    $data=str_ireplace('[cn_port]','3306',$data);
    $data=str_ireplace('[cn_user]',$yhc['sqluser'],$data);
    $data=str_ireplace('[cn_pass]',$yhc['sqlpass'],$data);
    $data=str_ireplace('[cn_name]',$yhc['sqluser'],$data);
    $data=str_ireplace('[cn_date]',date("Y-m-d H:i:s"),$data);
    if($arr){
    foreach ($arr as $name=>$val){          //以下为替换为表单数据
    $data=str_ireplace('[sf_'.$name.']',$val,$data);
    }
    }
    return $data;
}


function xjwj($val,$yhc,$os_xt,$userbd){               //新建文件
    $mly=$val['ml'];
    if($val['ml']!='/'){
    $ml = substr($mly, 0, 1 );
    $mlh = substr($mly, -1);
    if($ml!='/'){$mly='/'.$mly;}
    if($mlh!='/'){$mly=$mly.'/';}
    }
    $sc=$os_xt.$yhc['sqldz'].$mly.$val['name'];
    return($sc);
}

function xjwjj($val,$yhc,$os_xt,$userbd){               //新建文件夹
    $mly=$val['ml'];
    if($val['ml']!='/'){
    $ml = substr($mly, 0, 1 );
    $mlh = substr($mly, -1);
    if($ml!='/'){$mly='/'.$mly;}
    if($mlh!='/'){$mly=$mly.'/';}
    }
    $sc=$os_xt.$yhc['sqldz'].$mly.$val['name'];
    return($sc);
}

function delwj($val,$yhc,$os_xt,$userbd){               //删除文件
    $mly=$val['ml'];
    if($val['ml']!='/'){
    $ml = substr($mly, 0, 1 );
    $mlh = substr($mly, -1);
    if($ml!='/'){$mly='/'.$mly;}
    if($mlh!='/'){$mly=$mly.'/';}
    }
    $sc=$os_xt.$yhc['sqldz'].$mly.$val['name'];
    return($sc);
}

function delwjj($val,$yhc,$os_xt,$userbd){               //删除文件夹
    $mly=$val['ml'];
    if($val['ml']!='/'){
    $ml = substr($mly, 0, 1 );
    $mlh = substr($mly, -1);
    if($ml!='/'){$mly='/'.$mly;}
    if($mlh!='/'){$mly=$mly.'/';}
    }
    $sc=$os_xt.$yhc['sqldz'].$mly.$val['name'];
    return($sc);
}

function setwj($val,$yhc,$os_xt,$userbd){               //修改文件内容
    $mly=$val['ml'];
    if($val['ml']!='/'){
    $ml = substr($mly, 0, 1 );
    $mlh = substr($mly, -1);
    if($ml!='/'){$mly='/'.$mly;}
    if($mlh!='/'){$mly=$mly.'/';}
    }
    $nr = file_get_contents($val['nr']);
    $nr=tihs($nr,$userbd,$yhc);
    $sc=array($nr,$os_xt.$yhc['sqldz'].$mly.$val['name']);
    return($sc);
}

function drsql($val,$yhc,$os_xt,$userbd){               //导入数据库
    $mly=$val['ml'];
    if($val['ml']!='/'){
    $ml = substr($mly, 0, 1 );
    $mlh = substr($mly, -1);
    if($ml!='/'){$mly='/'.$mly;}
    if($mlh!='/'){$mly=$mly.'/';}
    }
    $sc=array($os_xt.$yhc['sqldz'].$mly.$val['name'],$yhc['sqluser']);
    return($sc);
}

function gettj($val,$yhc,$os_xt,$userbd){               //GET提交
    $nr = $val['get'];
    $nr=tihs($nr,$userbd,$yhc);
    $sc=file_get_contents('http://'.$val['url'].'?'.$nr);
    return($sc);
}

function cxname($val,$yhc,$os_xt,$userbd){               //文件重命名
    $mly=$val['lj'];
    if($val['lj']!='/'){
    $ml = substr($mly, 0, 1 );
    $mlh = substr($mly, -1);
    if($ml!='/'){$mly='/'.$mly;}
    if($mlh!='/'){$mly=$mly.'/';}
    }
    $sc=array($os_xt.$yhc['sqldz'],$mly,$val['jne'],$val['xne']);
    return($sc);
}

function setyxml($val,$yhc,$os_xt,$userbd){               //设置运行目录
    $mly=$val['lj'];
    if($val['lj']!='/'){
    $ml = substr($mly, 0, 1 );
    if($ml!='/'){$mly='/'.$mly;}
    }
    $sc=array($yhc['btid'],$mly,$os_xt.$yhc['sqldz']);
    return($sc);
}

function setwjt($val,$yhc,$os_xt,$userbd){               //设置伪静态
    $mly=$val['lj'];
    if($val['lj']!='/'){
    $ml = substr($mly, 0, 1 );
    $mlh = substr($mly, -1);
    if($ml!='/'){$mly='/'.$mly;}
    if($mlh!='/'){$mly=$mly.'/';}
    }
    $nr = file_get_contents($val['nr']);
    $nr=tihs($nr,$userbd,$yhc);
    $sc=array($nr,'/www/server/panel/vhost/rewrite/'.$yhc['sqldz'].'.conf');
    return($sc);
}

function setphp($val,$yhc,$os_xt,$userbd){               //设置PHP版本
    $php=tihs($val['php'],$userbd,$yhc);
    $php=preg_replace('/[^0-9]/','',$php);
    if($php==='')return array('','');
    $sc=array($yhc['sqldz'],$php);
    return($sc);
}
?>