<?php
if($egn=='cxtj') {
	//一键部署添加
	$cxname=daddslashes($_POST['cxname']);
	//程序名称
	$cxjs=daddslashes($_POST['cxjs']);
	//程序介绍
	$cxrmb=daddslashes($_POST['cxrmb']);
	//程序价格
	$cxwebkj=daddslashes($_POST['cxwebkj']);
	//程序所需最低网页空间
	$cxsqlkj=daddslashes($_POST['cxsqlkj']);
	//程序所需最低数据库空间
	$alerts=daddslashes($_POST['alerts']);
	//程序部署完成后的提示
	$kg=daddslashes($_POST['kg']);
	//程序是否上架
	if($kg=='on' || $kg=='true') {
		$kg='true';
	} else {
		$kg='false';
	}
	//开关所代表的意思进行修改
	if(!isset($cxname) || !isset($cxjs) || !isset($cxrmb) || !isset($cxwebkj) || !isset($cxsqlkj) || !isset($kg)   || $_FILES['filecx']=='') {
		exit('{"code":100,"msg":"禁止留空"}');
	}
	$rowse=$DB->get_row_prepare("SELECT * FROM MN_bs WHERE name=? limit 1", [$cxname]);
	if(isset($rowse)) {
		exit('{"code":100,"msg":"该程序名称已被其他程序占用"}');
	}
	$wjdhlmym=strtolower(substr(strrchr($_FILES['filecx']['name'], '.'), 1));
	//获取文件标识名并转换为小写
	if($wjdhlmym!='zip' && $wjdhlmym!='7z' && $wjdhlmym!='rar' && $wjdhlmym!='gzip' && $wjdhlmym!='jar') {
		exit('{"code":100,"msg":"源码类型错误：目前仅支持zip,7z,rar,gzip,jar类型的压缩包"}');
	}
	if(is_dir('../filecx')) {
	} else {
		mkdir('../filecx');
	}
	//判断根目录下的filecx文件夹是否存在
	for ($isx=1;$isx<200;$isx++) {
		$sf = $date.mt_rand(16,999).$cxname.$cxjs.$cxarmb;
		$rqsj=md5($sf);
		$hsks=mt_rand(0,5);
		$hskr=mt_rand(6,10);
		$wjjm=substr($rqsj, $hsks , 8);
		//算法计算文件夹名称
		if(!is_dir('../filecx/'.$wjjm)) {
			$isx=2000;
			break;
		}
		//避免目录名称重复
	}
	$sfjgf='../filecx/'.$wjjm;
	//此程序所在的文件夹
	mkdir($sfjgf);
	//新建文件夹
	//下面开始程序源码的保存
	$cxxiname=$sfjgf.'/'.'cxym.'.$wjdhlmym;
	//源码文件的路径以及名称
	copy($_FILES['filecx']['tmp_name'],$cxxiname);
	//拷贝文件
	unlink($_FILES['filecx']['tmp_name']);
	//删除原文件
	//程序的保存到此结束 程序的源码名称统一为cxym标识名取原名称
	unset($_FILES['filecx']);
	//去掉上传的程序源码的数组
	mkdir($sfjgf.'/tp');
	//在程序文件夹中新建一个文件夹用来存储图片
	$acft='0';
	//新建一个变量用来标识每张图片的名称每次循环完成后都会+1
	$szaie=array();
	//创建一个空的数组
	foreach ($_FILES['imgfile']['tmp_name'] as $aryqs=>$_value) {
		//下面开始图片的保存
		$cxxname=$sfjgf.'/tp/'.$acft.'.'.substr(strrchr($_FILES['imgfile']['name'][$aryqs], '.'), 1);
		//图片文件的路径以及名称
		copy($_value,$cxxname);
		//拷贝图片
		unlink($_value);
		//删除原文件
		//图片的保存到此结束 图片的名称为1，2，3依次排序
		$acft++;
		//变量+1
		array_push($szaie,$cxxname);
		//插入一个字符串
	}
	$fn_cz=$_POST['czf'];
	foreach ($fn_cz as $xbd=>$val) {
		if($val['cz']=='setwj' || $val['cz']=='setwjt') {
			//判断操作是否为修改文件内容
			//键名将直接作为文件名写盘，按仓库既有标识符白名单校验，非法则拒绝写入
			//用\z而非$，避免结尾换行符绕过（如 "abc\n"）
			if(!preg_match('/^[a-zA-Z0-9_\-]+\z/', (string)$xbd)) {
				exit('{"code":100,"msg":"配置项标识名非法：仅允许字母、数字、下划线与短横线"}');
			}
			$wjszwz=$sfjgf.'/setwj';
			//此操作文件的所在位置
			mkdir($wjszwz);
			//新建文件
			file_put_contents ($wjszwz.'/'.$xbd.'.setwj',$val['nr']);
			//写入文件内容
			$fn_cz[$xbd]['nr']=$wjszwz.'/'.$xbd.'.setwj';
			//修改数组
		}
	}
	$jsonzd=json_encode(array($cxwebkj,$cxsqlkj));
	//程序所需最低配置
	$jsonsjz=json_encode($fn_cz,256);
	//程序的安装配置
	$jsonipt=daddslashes(json_encode($_POST['bdf'],256));
	//安装前表单填写的配置
	$jsontp=daddslashes(json_encode($szaie,256));
	//程序的图片
	$rowe=$DB->get_row_prepare("SELECT * FROM MN_bs WHERE 1 order by id desc limit 1");
	$id=$rowe['id']+1;
	$tj=json_encode(array());
	if($DB->query_prepare("INSERT INTO `MN_bs` (`id`, `name`, `jc`, `src`, `date`, `cxwz`, `sxpz`, `tj`, `jg`, `inp`, `pz`, `alet`, `qk`) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)", [$id, $cxname, $cxjs, $jsontp, $date, $cxxiname, $jsonzd, $tj, $cxrmb, $jsonipt, $jsonsjz, $alerts, $kg])) {
		exit('{"code":200,"msg":"添加成功！"}');
	} else {
		exit('{"code":100,"msg":"添加失败'.$DB->error().'"}');
	}
	return;
}
if($egn=='cxxgjl') {
	$id=daddslashes($_POST['id']);
	$name=daddslashes($_POST['cxname']);
	$jc=daddslashes($_POST['cxjc']);
	$web=daddslashes($_POST['webkj']);
	$sql=daddslashes($_POST['sqlkj']);
	$jg=daddslashes($_POST['cxrmb']);
	$alerts=daddslashes($_POST['alerts']);
	$kg=daddslashes($_POST['cxkg']);
	$websql=json_encode(array($web,$sql));
	$cres=$DB->get_row_prepare("SELECT * FROM MN_bs WHERE id=? limit 1", [$id]);
	$fn_cz=$_POST['czf'];
	foreach ($fn_cz as $xbd=>$val) {
		if($val['cz']=='setwj' || $val['cz']=='setwjt') {
			//判断操作是否为修改文件内容
			//键名将直接用于拼接删档/写盘路径，先按仓库既有标识符白名单校验，非法则整体拒绝
			//用\z而非$，避免结尾换行符绕过（如 "abc\n"）；校验在unlink之前，合法名称的删除重建流程不受影响
			if(!preg_match('/^[a-zA-Z0-9_\-]+\z/', (string)$xbd)) {
				json_exit_error('配置项标识名非法：仅允许字母、数字、下划线与短横线');
			}
			$sfjgf='../filecx/'.explode("/",$cres['cxwz'])[2];
			$wjszwz=$sfjgf.'/setwj';
			//此操作文件的所在位置
			@unlink($wjszwz.'/'.$xbd.'.setwj');
			//删除文件
			if(is_dir($wjszwz)) {
			} else {
				mkdir($wjszwz);
			}
			//判断setwj文件夹是否存在
			file_put_contents ($wjszwz.'/'.$xbd.'.setwj',$val['nr']);
			//新建并写入文件内容
			$fn_cz[$xbd]['nr']=$wjszwz.'/'.$xbd.'.setwj';
			//修改数组
		}
	}
	$jsonsjz=json_encode($fn_cz,256);
	//程序的安装配置
	$jsonipt=json_encode($_POST['bdf'],256);
	//安装前表单填写的配置
	if($DB->query_prepare("update `MN_bs` set `name` =?, `jc` =?, `jg` =?, `inp` =?, `sxpz` =?, `qk` =?, `pz`=?, `alet`=? where `id`=?", [$name, $jc, $jg, $jsonipt, $websql, $kg, $jsonsjz, $alerts, $id]))json_exit('修改成功'); else json_exit('修改失败！');
	return;
}
if($egn=='cxsc') {
	$id=$_POST['id'];
	$val=$DB->get_row_prepare("SELECT * FROM MN_bs WHERE id=? limit 1", [$id]);
	if(explode("/",$val['cxwz'])[2]!=null) {
		deldir('../filecx/'.explode("/",$val['cxwz'])[2]);
	}
	if($DB->query_prepare("DELETE FROM MN_bs WHERE id=? limit 1", [$id]))json_exit('删除成功'); else json_exit('删除失败'.$DB->error());
	return;
}
if($egn=='cxscxz') {
	$idsz=$_POST['idsz'];
	$scqkr=0;
	$scqke=0;
	foreach($idsz as $id) {
		$val=$DB->get_row_prepare("SELECT * FROM MN_bs WHERE id=? limit 1", [$id]);
		if($DB->query_prepare("DELETE FROM MN_bs WHERE id=? limit 1", [$id])) {
			if(explode("/",$val['cxwz'])[2]!=null) {
				deldir('../filecx/'.explode("/",$val['cxwz'])[2]);
			}
			$scqke++;
		} else $scqkr++;
	}
	json_exit($scqke, ['codr' => $scqkr]);
	return;
}
if($egn=='listbs') {
	//程序列表
	$sorting=strtoupper($_POST['sortOrder']??'')==='DESC'?'DESC':'ASC';
	$paixu=preg_replace('/[^a-zA-Z0-9_]/','',$_POST['sort']??'id')?:'id';
	$pagesize=intval($_POST['limit']);
	$pageu=(intval($_POST['page'])-1) * $pagesize;
	$countdata=$DB->count_prepare("SELECT count(*) from MN_bs WHERE 1");
	$data=["total"=>$countdata];
	$data["rows"]=$DB->get_all_prepare("SELECT * FROM MN_bs order by $paixu $sorting limit $pageu,$pagesize");
	foreach ($data["rows"] as &$res) {
		$res['cxdx']=sprintf( "%.2f ",filesize($res['cxwz'])/1048576);
		$fn_cz=json_decode($res['pz'],true);
		if($fn_cz!=null) {
			foreach ($fn_cz as $xbd=>$val) {
				if($val['cz']=='setwj' || $val['cz']=='setwjt') {
					$fn_cz[$xbd]['nr']=file_get_contents($val['nr']);
				}
			}
			$res['pz']=json_encode($fn_cz,256);
		}
	}
	unset($res);
	exit(json_encode($data));
	return;
}
if($egn=='cxdc') {
	//程序导出
	include("../MPHX/BL.php");
	$ids=daddslashes($_POST['id']);
	$dccg=0;
	$dcsb=0;
	//导出成功次数和导出失败次数
	$zipfile='../filecx/export_file.zip';
	//压缩包存放目录
	@unlink($zipfile);
	//删除原来的一个压缩包
	foreach ($ids as $id) {
		$cres=$DB->get_row_prepare("SELECT * FROM MN_bs WHERE id=? limit 1", [$id]);
		if($cres==false)exit(json_encode(["code"=>4,"code"=>"错误！程序不存在！"]));
		$arr=[];
		$arr['vs']=$WEBQB;
		$arr['config']=$cres;
		$fileconfig=json_encode($arr,256);
		//配置信息，中文不转义
		$cxpath='../filecx/'.explode("/",$cres['cxwz'])[2];
		//程序所在目录
		$zipqk=zipfile($cxpath,'../filecx/export_file.zip','/',$fileconfig);
		if($zipqk['code']==1) {
			$dccg++;
		} else {
			$dcsb++;
		}
	}
	exit(json_encode(['code'=>1,'msg'=>"打包成功<b>$dccg</b>个程序，打包失败<b class='text-danger'>$dcsb</b>个程序"],256));
	return;
}
if($egn=='cxfiledru') {
	//程序导入
	include("../MPHX/BL.php");
	$ysize=daddslashes($_POST['fesw']);
	//已经上传的大小
	$zsize=daddslashes($_POST['zsize']);
	//总大小
	$file=$_FILES['file'];
	//文件
	$tmp_path='../filecx/file_import_tmp';
	//导入文件解压后的临时存放位置
	$filepath='../filecx/import_file.zip';
	//压缩包存放目录和文件名
	if(is_dir($tmp_path))deldir($tmp_path);
	//删除临时目录
	if(!is_dir($tmp_path))mkdir($tmp_path);
	//新建目录
	if($ysize==0) {
		@unlink($filepath);
		//删除上一次的导入文件
	}
	if(filesize($filepath)<$zsize) {
		$files=file_get_contents($file['tmp_name']);
		//读取
		file_put_contents($filepath, $files, FILE_APPEND | LOCK_EX);
		//写入
		@unlink($file['tmp_name']);
		//删除
	}
	$filesizes=filesize($filepath);
	if($filesizes>$zsize)exit(json_encode(['error'=>1,'size'=>4,'msg'=>'抱歉，我们遇见了一个未知的错误！请重新导入！']));
	if($filesizes<$zsize) {
		//上传未完成，通知继续上传
		exit(json_encode(['error'=>0,'size'=>$filesizes]));
	}
	//解压文件
	$zip = new \ZipArchive;
	if($zip->open($filepath)===true) {
		//打开
		//逐条解压并校验，防止 zip-slip（../ 段或绝对路径条目写到目标目录之外）
		$dest_real=realpath($tmp_path);
		//目标目录真实路径
		if($dest_real===false) {
			$zip->close();
			@unlink($filepath);
			exit(json_encode(['error'=>1,'size'=>4,'msg'=>'错误！解压目标目录不存在！']));
		}
		$total=$zip->numFiles;
		for($i=0;$i<$total;$i++) {
			$name=$zip->getNameIndex($i);
			if($name===false || $name==='')continue;
			//条目分隔符统一为 / 再判断，同时拒绝空字节等控制字符
			$entry=str_replace('\\','/',$name);
			if($entry[0]=='/' || preg_match('/^[a-zA-Z]:\//',$entry) || preg_match('/[\x00-\x1F]/',$entry)) {
				//绝对路径、盘符路径或含控制字符，跳过该条目
				continue;
			}
			$esc=false;
			foreach (explode('/',$entry) as $seg) {
				if($seg==='..') {
					$esc=true;
					break;
				}
			}
			//仅过滤字符串".."不够，需按路径段拆分判断（如 a/../b）
			if($esc)continue;
			$zip->extractTo($tmp_path,$name);
			//单条解压，保持原 extractTo 的目标路径与不覆盖语义
			//解压后再用 realpath 复核落点，防符号链接等绕过；前缀比较补分隔符，避免 dest 误配 destory
			$real=realpath($tmp_path.'/'.$entry);
			if($real===false || ($real!==$dest_real && strpos($real,$dest_real.DIRECTORY_SEPARATOR)!==0)) {
				if($real!==false) {
					if(is_file($real))@unlink($real);
					if(is_dir($real))@deldir($real);
				}
				continue;
			}
		}
		$zip->close();
		//关闭
		@unlink($filepath);
		//删除压缩包
	} else {
		@unlink($filepath);
		//删除压缩包
		exit(json_encode(['error'=>1,'size'=>4,'msg'=>'错误！压缩包打开失败！']));
	}
	//读取数据导入文件
	$imptlist=scandir($tmp_path);
	//导入的文件列表
	$filelist=scandir('../filecx');
	//原来的文件列表
	if($imptlist[0]=='.')unset($imptlist[0]);
	if($imptlist[1]=='..')unset($imptlist[1]);
	if(empty($imptlist))exit(json_encode(['error'=>1,'size'=>4,'msg'=>'错误！导入文件不存在！']));
	$drcgf=0;
	$drsbf=0;
	//成功，失败
	foreach ($imptlist as $val) {
		if(in_array($val,$filelist)) {
			$drsbf++;
			continue;
		}
		$path=$tmp_path.'/'.$val;
		//导入的文件的目录
		$config_file=$path.'/mnbt_file_conf.json';
		//配置文件
		if(!is_file($config_file)) {
			$drsbf++;
			continue;
		}
		$config=json_decode(file_get_contents($config_file),true)['config'];
		//读取配置文件
		if(!$config) {
			$drsbf++;
			continue;
		}
		@unlink($config_file);
		$columns = [];
		$placeholders = [];
		$params = [];
		foreach ($config as $k=>$v) {
			if($k=='id') {
				$rowe=$DB->get_row_prepare("SELECT * FROM MN_bs WHERE 1 order by id desc limit 1");
				$v=$rowe['id']+1;
			}
			if($k=='qk')$v=$_POST['sxj'];
			if($k=='name') {
				$rowse=$DB->get_row_prepare("SELECT * FROM MN_bs WHERE name=? limit 1", [$cxname]);
				if(isset($rowse)) {
					$columns = false;
					break;
				}
			}
			if ($columns !== false) {
				$columns[] = '`'.$k.'`';
				$placeholders[] = '?';
				$params[] = $v;
			}
		}
		if(!$columns || !$params) {
			$drsbf++;
			continue;
		}
		$sql = "INSERT INTO `MN_bs` (".implode(',', $columns).") VALUES (".implode(',', $placeholders).")";
		if(!rename($path,'../filecx/'.$val)) {
			$drsbf++;
			continue;
		}
		if($DB->query_prepare($sql, $params)) {
			$drcgf++;
		} else {
			$drsbf++;
			//失败
			if(is_dir('../filecx/'.$val))deldir('../filecx/'.$val);
		}
	}
	//删除临时目录
	if(is_dir($tmp_path))deldir($tmp_path);
	exit(json_encode(['error'=>1,'size'=>1,'msg'=>'导入成功<b>'.$drcgf.'</b>个程序！导入失败<b class="text-danger">'.$drsbf.'</b>个程序！']));
	return;
}
return;
