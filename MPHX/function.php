<?php
function mnbt_ip_in_cidr($ip, $cidr)
{
	if (strpos($cidr, '/') === false) return hash_equals($cidr, $ip);
	list($network, $prefix) = explode('/', $cidr, 2);
	$ipBin = @inet_pton($ip);
	$networkBin = @inet_pton($network);
	if ($ipBin === false || $networkBin === false || strlen($ipBin) !== strlen($networkBin)) return false;
	$prefix = (int)$prefix;
	$max = strlen($ipBin) * 8;
	if ($prefix < 0 || $prefix > $max) return false;
	$bytes = intdiv($prefix, 8);
	$bits = $prefix % 8;
	if ($bytes > 0 && substr($ipBin, 0, $bytes) !== substr($networkBin, 0, $bytes)) return false;
	if ($bits === 0) return true;
	$mask = (0xFF << (8 - $bits)) & 0xFF;
	return (ord($ipBin[$bytes]) & $mask) === (ord($networkBin[$bytes]) & $mask);
}
function mnbt_request_from_trusted_proxy()
{
	$remote = isset($_SERVER['REMOTE_ADDR']) ? trim((string)$_SERVER['REMOTE_ADDR']) : '';
	$trusted = getenv('MNBT_TRUSTED_PROXIES');
	if ($remote === '' || $trusted === false || trim($trusted) === '') return false;
	foreach (explode(',', $trusted) as $proxy) {
		$proxy = trim($proxy);
		if ($proxy !== '' && mnbt_ip_in_cidr($remote, $proxy)) return true;
	}
	return false;
}
function mnbt_request_is_secure()
{
	if (!empty($_SERVER['HTTPS']) && strtolower((string)$_SERVER['HTTPS']) !== 'off') return true;
	if (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443) return true;
	if (!mnbt_request_from_trusted_proxy()) return false;
	$proto = isset($_SERVER['HTTP_X_FORWARDED_PROTO']) ? strtolower(trim(explode(',', (string)$_SERVER['HTTP_X_FORWARDED_PROTO'])[0])) : '';
	return $proto === 'https';
}
function mnbt_set_cookie($name, $value, $expires = 0, $httpOnly = true, $sameSite = 'Lax', $path = '/')
{
	$options = ['expires' => (int)$expires, 'path' => $path, 'secure' => mnbt_request_is_secure(), 'httponly' => (bool)$httpOnly, 'samesite' => $sameSite];
	if (PHP_VERSION_ID >= 70300) return setcookie($name, $value, $options);
	return setcookie($name, $value, (int)$expires, $path . '; samesite=' . $sameSite, '', $options['secure'], $options['httponly']);
}
function mnbt_set_auth_cookie($name, $value, $expires)
{
	$deleteAt = time() - 604800;
	foreach (['/admin', '/user', '/app_plugins/user_info', '/app_plugins/user_info/lib'] as $legacyPath) mnbt_set_cookie($name, '', $deleteAt, true, 'Lax', $legacyPath);
	return mnbt_set_cookie($name, $value, $expires, true, 'Lax', '/');
}
function mnbt_rotate_login_session()
{
	if (session_status() === PHP_SESSION_ACTIVE) session_regenerate_id(true);
	mnbt_csrf_token(true);
}
function mnbt_csrf_token($rotate = false)
{
	if ($rotate || empty($_SESSION['mnbt_csrf_token']) || !is_string($_SESSION['mnbt_csrf_token'])) {
		try { $_SESSION['mnbt_csrf_token'] = bin2hex(random_bytes(32)); } catch (Throwable $e) { $_SESSION['mnbt_csrf_token'] = hash('sha256', uniqid((string)mt_rand(), true) . session_id()); }
	}
	$token = $_SESSION['mnbt_csrf_token'];
	if (!headers_sent() && (!isset($_COOKIE['MNBT_CSRF_TOKEN']) || !hash_equals((string)$_COOKIE['MNBT_CSRF_TOKEN'], $token))) {
		mnbt_set_cookie('MNBT_CSRF_TOKEN', $token, 0, false, 'Lax');
		$_COOKIE['MNBT_CSRF_TOKEN'] = $token;
	}
	return $token;
}
function mnbt_csrf_request_token()
{
	foreach (['HTTP_X_CSRF_TOKEN', 'HTTP_X_XSRF_TOKEN'] as $header) if (!empty($_SERVER[$header])) return (string)$_SERVER[$header];
	foreach (['_csrf', 'csrf_token', '_token'] as $key) if (isset($_POST[$key]) && is_string($_POST[$key])) return $_POST[$key];
	return '';
}
function mnbt_csrf_verify($token = null)
{
	$expected = isset($_SESSION['mnbt_csrf_token']) && is_string($_SESSION['mnbt_csrf_token']) ? $_SESSION['mnbt_csrf_token'] : '';
	$actual = $token === null ? mnbt_csrf_request_token() : (string)$token;
	return $expected !== '' && $actual !== '' && hash_equals($expected, $actual);
}
function mnbt_csrf_is_safe_method($method = null)
{
	$method = strtoupper($method === null ? ($_SERVER['REQUEST_METHOD'] ?? 'GET') : (string)$method);
	return in_array($method, ['GET', 'HEAD', 'OPTIONS'], true);
}
function mnbt_csrf_fail()
{
	http_response_code(419);
	@header('Content-Type: application/json; charset=UTF-8');
	if (function_exists('json_exit')) json_exit('CSRF 验证失败，请刷新页面后重试');
	exit('{"code":"CSRF 验证失败，请刷新页面后重试"}');
}
function mnbt_csrf_validate_request($exempt = false)
{
	if ($exempt || mnbt_csrf_is_safe_method()) return true;
	if (!mnbt_csrf_verify()) mnbt_csrf_fail();
	return true;
}
function mnbt_csrf_field()
{
	return '<input type="hidden" name="_csrf" value="' . htmlspecialchars(mnbt_csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
}
function mnbt_csrf_head()
{
	$token = htmlspecialchars(mnbt_csrf_token(), ENT_QUOTES, 'UTF-8');
	return '<meta name="csrf-token" content="' . $token . '"><script>(function(){var m=document.querySelector(\'meta[name="csrf-token"]\');if(!m)return;var t=m.content,s=function(u){try{return new URL(u,location.href).origin===location.origin}catch(e){return false}},v=function(x){return !/^(GET|HEAD|OPTIONS)$/i.test(x||"GET")};document.addEventListener("submit",function(e){var f=e.target;if(f&&v(f.method)&&s(f.action||location.href)&&!f.querySelector(\'input[name="_csrf"]\')){var i=document.createElement("input");i.type="hidden";i.name="_csrf";i.value=t;f.appendChild(i)}},true);var o=XMLHttpRequest.prototype.open,a=XMLHttpRequest.prototype.send;XMLHttpRequest.prototype.open=function(method,url){this.__mnbtCsrf=v(method)&&s(url);return o.apply(this,arguments)};XMLHttpRequest.prototype.send=function(){if(this.__mnbtCsrf)this.setRequestHeader("X-CSRF-Token",t);return a.apply(this,arguments)};if(window.fetch){var f=window.fetch;window.fetch=function(input,init){init=init||{};var u=typeof input==="string"?input:input.url,method=init.method||(typeof input!=="string"&&input.method)||"GET";if(v(method)&&s(u)){init.headers=new Headers(init.headers||(typeof input!=="string"?input.headers:void 0));init.headers.set("X-CSRF-Token",t)}return f.call(this,input,init)}}if(window.jQuery)jQuery.ajaxPrefilter(function(options,original,xhr){if(v(options.type)&&s(options.url))xhr.setRequestHeader("X-CSRF-Token",t)})})();</script>';
}
function mnbt_csrf_inject_html($html)
{
	if (!is_string($html) || $html === '') return $html;
	if (!preg_match('/\A\s*(?:<!doctype\s+html\b[^>]*>\s*)?<html\b[^>]*>/i', $html)) return $html;
	if (!preg_match('/<head\b[^>]*>[\s\S]*<\/head\s*>/i', $html)) return $html;
	if (preg_match('/<meta\b[^>]*\bname\s*=\s*(["\'])csrf-token\1/i', $html)) return $html;
	return preg_replace('/<\/head\s*>/i', mnbt_csrf_head() . '</head>', $html, 1);
}
function curl_get($url)
{
$ch=curl_init($url);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Linux; U; Android 4.4.1; zh-cn; R815T Build/JOP40D) AppleWebKit/533.1 (KHTML, like Gecko)Version/4.0 MQQBrowser/4.5 Mobile Safari/533.1');
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$content=curl_exec($ch);
curl_close($ch);
return($content);
}
function daddslashes($string, $force = 0, $strip = FALSE) {
	!defined('MAGIC_QUOTES_GPC') && define('MAGIC_QUOTES_GPC', false);
	if(!MAGIC_QUOTES_GPC || $force) {
		if(is_array($string)) {
			foreach($string as $key => $val) {
				$string[$key] = daddslashes($val, $force, $strip);
			}
		} else {
			$string = addslashes($strip ? stripslashes($string) : $string);
		}
	}
	return $string;
}
function authcode($string, $operation = 'DECODE', $key = '', $expiry = 0) {
	$ckey_length = 4;
	$key = md5($key ? $key : ENCRYPT_KEY);
	$keya = md5(substr($key, 0, 16));
	$keyb = md5(substr($key, 16, 16));
	$keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length): substr(md5(microtime()), -$ckey_length)) : '';
	$cryptkey = $keya.md5($keya.$keyc);
	$key_length = strlen($cryptkey);
	$string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;
	$string_length = strlen($string);
	$result = '';
	$box = range(0, 255);
	$rndkey = array();
	for($i = 0; $i <= 255; $i++) {
		$rndkey[$i] = ord($cryptkey[$i % $key_length]);
	}
	for($j = $i = 0; $i < 256; $i++) {
		$j = ($j + $box[$i] + $rndkey[$i]) % 256;
		$tmp = $box[$i];
		$box[$i] = $box[$j];
		$box[$j] = $tmp;
	}
	for($a = $j = $i = 0; $i < $string_length; $i++) {
		$a = ($a + 1) % 256;
		$j = ($j + $box[$a]) % 256;
		$tmp = $box[$a];
		$box[$a] = $box[$j];
		$box[$j] = $tmp;
		$result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
	}
	if($operation == 'DECODE') {
		if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {
			return substr($result, 26);
		} else {
			return '';
		}
	} else {
		return $keyc.str_replace('=', '', base64_encode($result));
	}
}
function showmsg($content = '未知的异常',$type = 4,$back = false)
{
switch($type)
{
case 1:
	$panel="success";
break;
case 2:
	$panel="info";
break;
case 3:
	$panel="warning";
break;
case 4:
	$panel="danger";
break;
}

echo '<div class="panel panel-'.$panel.'">
      <div class="panel-heading">
        <h3 class="panel-title">提示信息</h3>
        </div>
        <div class="panel-body">';
echo $content;

if ($back) {
	echo '<hr/><a href="'.$back.'"><< 返回上一页</a>';
}
else
    echo '<hr/><a href="javascript:history.back(-1)"><< 返回上一页</a>';

echo '</div>
    </div>';
}
function sysmsg($msg = '未知的异常',$die = true) {
    ?>  
    <!DOCTYPE html>
    <html xmlns="http://www.w3.org/1999/xhtml" lang="zh-CN">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>站点提示信息</title>
        <style type="text/css">
html{background:#eee}body{background:#fff;color:#333;font-family:"微软雅黑","Microsoft YaHei",sans-serif;margin:2em auto;padding:1em 2em;max-width:700px;-webkit-box-shadow:10px 10px 10px rgba(0,0,0,.13);box-shadow:10px 10px 10px rgba(0,0,0,.13);opacity:.8}h1{border-bottom:1px solid #dadada;clear:both;color:#666;font:24px "微软雅黑","Microsoft YaHei",,sans-serif;margin:30px 0 0 0;padding:0;padding-bottom:7px}#error-page{margin-top:50px}h3{text-align:center}#error-page p{font-size:9px;line-height:1.5;margin:25px 0 20px}#error-page code{font-family:Consolas,Monaco,monospace}ul li{margin-bottom:10px;font-size:9px}a{color:#21759B;text-decoration:none;margin-top:-10px}a:hover{color:#D54E21}.button{background:#f7f7f7;border:1px solid #ccc;color:#555;display:inline-block;text-decoration:none;font-size:9px;line-height:26px;height:28px;margin:0;padding:0 10px 1px;cursor:pointer;-webkit-border-radius:3px;-webkit-appearance:none;border-radius:3px;white-space:nowrap;-webkit-box-sizing:border-box;-moz-box-sizing:border-box;box-sizing:border-box;-webkit-box-shadow:inset 0 1px 0 #fff,0 1px 0 rgba(0,0,0,.08);box-shadow:inset 0 1px 0 #fff,0 1px 0 rgba(0,0,0,.08);vertical-align:top}.button.button-large{height:29px;line-height:28px;padding:0 12px}.button:focus,.button:hover{background:#fafafa;border-color:#999;color:#222}.button:focus{-webkit-box-shadow:1px 1px 1px rgba(0,0,0,.2);box-shadow:1px 1px 1px rgba(0,0,0,.2)}.button:active{background:#eee;border-color:#999;color:#333;-webkit-box-shadow:inset 0 2px 5px -3px rgba(0,0,0,.5);box-shadow:inset 0 2px 5px -3px rgba(0,0,0,.5)}table{table-layout:auto;border:1px solid #333;empty-cells:show;border-collapse:collapse}th{padding:4px;border:1px solid #333;overflow:hidden;color:#333;background:#eee}td{padding:4px;border:1px solid #333;overflow:hidden;color:#333}
        </style>
    </head>
    <body id="error-page">
        <?php echo '<h3>站点提示信息</h3>';
        echo $msg; ?>
    </body>
    </html>
    <?php
    if ($die == true) {
        exit;
    }
}

function logjl($czuser_hsk_v = '操作用户',$lx_hsk_v = '操作类型',$lr_hsk_v = '内容',$qk_hsk_v = '操作情况',$DBZHER = 'NULL') {
	if (!is_object($DBZHER) || !method_exists($DBZHER, 'query_prepare')) {
		error_log('[MNBT业务日志失败] 数据库对象无效：'.$lx_hsk_v.' '.$lr_hsk_v);
		return '0';
	}
	$ip_hxer_ipsz_envc = $_SERVER["REMOTE_ADDR"] ?? 'CLI';
	$data_time_rq_sjv = date("Y-m-d H:i:s");
	$czuser_hsk_v = mb_substr((string)$czuser_hsk_v, 0, 250, 'UTF-8');
	$lx_hsk_v = mb_substr((string)$lx_hsk_v, 0, 250, 'UTF-8');
	$lr_hsk_v = mb_substr((string)$lr_hsk_v, 0, 50, 'UTF-8');
	$qk_hsk_v = (string)$qk_hsk_v;
	try {
		if($DBZHER->query_prepare("INSERT INTO `MN_log` (`czuser`, `date`, `lx`, `lr`, `ip`, `qk`) VALUES (?, ?, ?, ?, ?, ?)", [$czuser_hsk_v, $data_time_rq_sjv, $lx_hsk_v, $lr_hsk_v, $ip_hxer_ipsz_envc, $qk_hsk_v])) return '1';
		$error = method_exists($DBZHER, 'error') ? $DBZHER->error() : '未知数据库错误';
		error_log('[MNBT业务日志失败] '.$error.' | '.$lx_hsk_v.' '.$lr_hsk_v);
		return '0'.$error;
	} catch (Throwable $e) {
		error_log('[MNBT业务日志异常] '.$e->getMessage().' | '.$lx_hsk_v.' '.$lr_hsk_v);
		return '0'.$e->getMessage();
	}
}

function mnbt_log($user = '系统', $type = '系统日志', $content = '内容', $status = '记录', $db = null) {
	global $DB;
	if ($db === null && isset($DB)) $db = $DB;
	return logjl($user, $type, $content, $status, $db);
}

function send_post($url, $post_data) {
  if(!is_array($post_data)) $post_data=[];
  $postdata = http_build_query($post_data);
  $options = array(
    'http' => array(
      'method' => 'POST',
      'header' => 'Content-type:application/x-www-form-urlencoded',
      'content' => $postdata,
      'timeout' => 4// 超时时间（单位:s）
    )
  );
  $context = stream_context_create($options);
  $result = file_get_contents($url, false, $context);
  return $result;
}

function ary_asd($mn_conf){
	$fh_ry='"';
	foreach($mn_conf as $sne=>$via){
		$fh_ry_r='"';
		if(is_array($via)){
			$via='array('.ary_asd($via).')';
			$fh_ry_r='';
		}
		if(is_numeric($via)){
			$fh_ry_r='';
		}
		if($kr_sxy==""){
			$kr_sxy.=$fh_ry.$sne.$fh_ry.'=>'.$fh_ry_r.$via.$fh_ry_r;
		} else{
			$kr_sxy.=','.$fh_ry.$sne.$fh_ry.'=>'.$fh_ry_r.$via.$fh_ry_r;
		}
	}
	return $kr_sxy;
}

    function deldir($dir) {             //删除目录下的文件
        //先删除目录下的文件：
        $dh = opendir($dir);
        while ($file = readdir($dh)) {
            if ($file != "." && $file != "..") {
                $fullpath = $dir.
                "/".$file;
                if (!is_dir($fullpath)) {
                    unlink($fullpath);
                } else {
                    deldir($fullpath);
                }
            }
        }
        closedir($dh);
        //删除当前文件夹：
        if (rmdir($dir)) {
            return true;
        } else {
            return false;
        }
    }

	function dirfiles($data, $type) {       //控制面板返回的文件数据处理$filename如果不为false则为获取指定文件名数据
	$userinisf=0;
	$printarry = [];
	    foreach($data as $val) {
	        $arr = []; //为本次循环新建一个数组存放本条次数据
	        $valarr = explode(";", $val);
	        if($valarr[0]=='.user.ini' && $type=='file'){$userinisf=1; continue;}        //防跨站配置文件不用显示给用户
	        $arr['name'] = $valarr[0]; //文件名
	        $arr['type'] = $type; //文件类型
	        $arr['mtime'] = $valarr[2]; //修改时间戳
	        $arr['size'] = $valarr[1]; //文件大小
	        $arr['download']=$valarr[6];         //是否外链分享
	        $printarry[] = $arr; //数组存储
	        unset($arr);
	    }
	    return ["file"=>$printarry,"couts"=>$userinisf];
	}
	
	function delval_array($arr,$value,$path=''){         //删除一维数组中指定的值，同时取消名称不规范的目录/文件，并且判断执行完成后是否存在文件可删
	    foreach ($arr as $k=>$v){
	        if($path.$v==$value)unset($arr[$k]);      //删除指定的值的参数
		    if(strpos($v,'/'))unset($arr[$k]);      //删除值不规范的参数
	        $arr = array_merge($arr);
	    }
	    if(empty($arr)){            //判断是否为空数组
	        return false;
	    }else{
	        return $arr;
	    }
	}
	
function zipfile($path,$zipth,$paths='/',$filetext=false) {
	//压缩目录(被压缩的目录，此函数上一次执行的位置，压缩包存放处，程序配置文件内容)
	$zip = new \ZipArchive;
	$pathname=substr($path,strripos($path,'/')+1);
	//获取目录名
	if($zip->open($zipth, \ZIPARCHIVE::CREATE)===true) {
		//新建一个压缩包
		if($paths=='/') {
			$zip->addEmptyDir($pathname);
			//新建一个目录
		} else {
			$zip->addEmptyDir($pathname.$paths);
			//新建一个目录
		}
		if($filetext) {
			$zip->addFromString($pathname.$paths.'mnbt_file_conf.json', $filetext);
		}
		$list=scandir($path.$paths);
		foreach ($list as $val) {
			if($val=='.' || $val=='..')continue;
			if(is_dir($path.$paths.$val)) {
				//如果是目录则执行函数
				$zip->close();
				//关闭压缩包
				if($paths=='/') {
					zipfile($path,$zipth,$paths.$val.'/',false);
				} else {
					zipfile($path,$zipth,$paths.'/'.$val.'/',false);
				}
			} else {
				//文件
				$zip->addFile($path.$paths.$val,$pathname.$paths.$val);
			}
		}
	} else return ["code"=>4,"msg"=>"错误！创建压缩包失败！"];
	$zip->close();
	//关闭压缩包
	return ["code"=>1,"msg"=>"程序打包完成"];
}

function mnbt_json_encode($code, $extra = [], $success = null)
{
    $msg = is_scalar($code) ? (string)$code : '返回信息';
    if ($success === null) {
        $success = !isset($extra['qk']) || (string)$extra['qk'] !== '4';
    }
    if (class_exists('Response')) {
        $result = Response::build($code, $msg, empty($extra) ? null : $extra, null, $success);
    } else {
        $result = [
            'success' => (bool)$success,
            'code' => $code,
            'msg' => $msg,
            'redirect' => null,
        ];
        if (!empty($extra)) $result['data'] = $extra;
    }
    foreach ($extra as $k => $v) {
        $result[$k] = $v;
    }
    $json = json_encode($result, JSON_UNESCAPED_UNICODE);
    if ($json === false) $json = '{"success":false,"code":"' . addslashes((string)$code) . '","msg":"JSON编码失败"}';
    return $json;
}

function json_exit($code, $extra = [])
{
    exit(mnbt_json_encode($code, $extra));
}

function json_exit_success($msg, $extra = [])
{
    $extra['qk'] = 1;
    exit(mnbt_json_encode($msg, $extra, true));
}

function json_exit_error($msg, $extra = [])
{
    $extra['qk'] = 4;
    exit(mnbt_json_encode($msg, $extra, false));
}

function json_echo($code, $extra = [])
{
    echo mnbt_json_encode($code, $extra);
}

function json_return($code, $extra = [])
{
    return mnbt_json_encode($code, $extra);
}
?>