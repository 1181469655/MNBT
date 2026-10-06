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
	// 双提交 Cookie 兜底：页面未注入 meta 时前端可从 MNBT_CSRF_TOKEN 读取并回传
	if (isset($_COOKIE['MNBT_CSRF_TOKEN']) && is_string($_COOKIE['MNBT_CSRF_TOKEN']) && $_COOKIE['MNBT_CSRF_TOKEN'] !== '') return $_COOKIE['MNBT_CSRF_TOKEN'];
	return '';
}
function mnbt_csrf_verify($token = null)
{
	$expected = isset($_SESSION['mnbt_csrf_token']) && is_string($_SESSION['mnbt_csrf_token']) ? $_SESSION['mnbt_csrf_token'] : '';
	if ($expected === '') return false;
	$actual = $token === null ? mnbt_csrf_request_token() : (string)$token;
	if ($actual !== '' && hash_equals($expected, $actual)) return true;
	// header/post 携带旧 token 不匹配时，回退校验双提交 cookie，覆盖旧页面/轮换后的场景
	if (isset($_COOKIE['MNBT_CSRF_TOKEN']) && is_string($_COOKIE['MNBT_CSRF_TOKEN']) && $_COOKIE['MNBT_CSRF_TOKEN'] !== '') {
		return hash_equals($expected, $_COOKIE['MNBT_CSRF_TOKEN']);
	}
	return false;
}
function mnbt_csrf_is_safe_method($method = null)
{
	$method = strtoupper($method === null ? ($_SERVER['REQUEST_METHOD'] ?? 'GET') : (string)$method);
	return in_array($method, ['GET', 'HEAD', 'OPTIONS'], true);
}
function mnbt_csrf_enabled()
{
	if (defined('MNBT_CSRF_ENABLED')) return (bool)MNBT_CSRF_ENABLED;
	$env = getenv('MNBT_CSRF_ENABLED');
	if ($env !== false && trim($env) !== '') return !in_array(strtolower(trim($env)), ['0', 'false', 'off', 'no'], true);
	return true;
}
function mnbt_csrf_fail()
{
	$expected = isset($_SESSION['mnbt_csrf_token']) && is_string($_SESSION['mnbt_csrf_token']) ? $_SESSION['mnbt_csrf_token'] : '';
	$provided = mnbt_csrf_request_token();
	$source = '';
	foreach (['HTTP_X_CSRF_TOKEN', 'HTTP_X_XSRF_TOKEN'] as $h) if (!empty($_SERVER[$h])) { $source = 'header'; break; }
	if ($source === '') foreach (['_csrf', 'csrf_token', '_token'] as $k) if (isset($_POST[$k]) && is_string($_POST[$k])) { $source = 'post'; break; }
	if ($source === '' && isset($_COOKIE['MNBT_CSRF_TOKEN']) && $_COOKIE['MNBT_CSRF_TOKEN'] !== '') $source = 'cookie';
	error_log(sprintf('[MNBT CSRF] fail path=%s method=%s has_session_token=%d provided_len=%d source=%s has_cookie=%d ip=%s',
		$_SERVER['REQUEST_URI'] ?? '', $_SERVER['REQUEST_METHOD'] ?? '', $expected !== '' ? 1 : 0,
		strlen($provided), $source, isset($_COOKIE['MNBT_CSRF_TOKEN']) ? 1 : 0, $_SERVER['REMOTE_ADDR'] ?? ''));
	http_response_code(419);
	@header('Content-Type: application/json; charset=UTF-8');
	if (function_exists('json_exit_error')) json_exit_error('CSRF 验证失败，请刷新页面后重试');
	if (function_exists('json_exit')) json_exit('CSRF 验证失败，请刷新页面后重试');
	exit('{"success":false,"code":"CSRF 验证失败，请刷新页面后重试","msg":"CSRF 验证失败，请刷新页面后重试","redirect":null}');
}
function mnbt_csrf_validate_request($exempt = false)
{
	if ($exempt || !mnbt_csrf_enabled() || mnbt_csrf_is_safe_method()) return true;
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
	return '<meta name="csrf-token" content="' . $token . '"><script>(function(){var m=document.querySelector(\'meta[name="csrf-token"]\'),t=m&&m.content?m.content:(function(){try{var c=document.cookie.split(\'; \'),i;for(i=0;i<c.length;i++){var q=c[i].indexOf(\'=\');if(q>-1&&c[i].slice(0,q)===\'MNBT_CSRF_TOKEN\')return decodeURIComponent(c[i].slice(q+1))}}catch(e){}return \'\'})();if(!t)return;var s=function(u){try{return new URL(u,location.href).origin===location.origin}catch(e){return false}},v=function(x){return !/^(GET|HEAD|OPTIONS)$/i.test(x||"GET")};document.addEventListener("submit",function(e){var f=e.target;if(f&&v(f.method)&&s(f.action||location.href)&&!f.querySelector(\'input[name="_csrf"]\')){var i=document.createElement("input");i.type="hidden";i.name="_csrf";i.value=t;f.appendChild(i)}},true);var o=XMLHttpRequest.prototype.open,a=XMLHttpRequest.prototype.send;XMLHttpRequest.prototype.open=function(method,url){this.__mnbtCsrf=v(method)&&s(url);return o.apply(this,arguments)};XMLHttpRequest.prototype.send=function(){if(this.__mnbtCsrf)this.setRequestHeader("X-CSRF-Token",t);return a.apply(this,arguments)};if(window.fetch){var f=window.fetch;window.fetch=function(input,init){init=init||{};var u=typeof input==="string"?input:input.url,method=init.method||(typeof input!=="string"&&input.method)||"GET";if(v(method)&&s(u)){init.headers=new Headers(init.headers||(typeof input!=="string"?input.headers:void 0));init.headers.set("X-CSRF-Token",t)}return f.call(this,input,init)}}if(window.jQuery)jQuery.ajaxPrefilter(function(options,original,xhr){if(v(options.type)&&s(options.url))xhr.setRequestHeader("X-CSRF-Token",t)})})();</script>';
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

function mnbt_api_compat_mode()
{
	global $conf;
	// api_compat='1' 时对外主机 API 还原 1.81 老协议行为，供未升级对接模块的客户过渡使用
	return isset($conf['api_compat']) && (string)$conf['api_compat'] === '1';
}

// ---------------------------------------------------------------------------
//  监控/定时入口鉴权（jk.php / jk_monitor.php / docker_cron.php）
//  新方式：?t=<unix秒>&sign=hash_hmac('sha256', '<脚本名>|<t>', API密钥)，±300 秒有效
//  过渡期：旧 ?my=<API密钥> 仍接受，但记录弃用日志并回显 X-MNBT-Auth-Deprecated
//  背景：查询串里的密钥会落进访问日志/代理，务必引导切换
// ---------------------------------------------------------------------------
function mnbt_cron_signature($script, $time)
{
	global $conf;
	return hash_hmac('sha256', $script . '|' . (string)$time, (string)($conf['api'] ?? ''));
}

function mnbt_cron_auth_check($script)
{
	global $conf;
	$secret = (string)($conf['api'] ?? '');
	if ($secret === '') return false;

	$t = isset($_GET['t']) ? (int)$_GET['t'] : 0;
	$sign = isset($_GET['sign']) ? (string)$_GET['sign'] : '';
	if ($t > 0 && $sign !== '') {
		if (abs(time() - $t) > 300) return false;
		return hash_equals(mnbt_cron_signature($script, $t), $sign);
	}

	$legacy = isset($_GET['my']) ? (string)$_GET['my'] : '';
	if ($legacy !== '' && hash_equals($secret, $legacy)) {
		error_log('[MNBT cron] ' . $script . ' 仍在使用 ?my= 查询串鉴权（密钥已暴露在访问日志中），请尽快切换为 ?t=<unix>&sign=<hmac> 方式');
		if (!headers_sent()) @header('X-MNBT-Auth-Deprecated: legacy-query-key');
		return true;
	}
	return false;
}

// ---------------------------------------------------------------------------
//  管理员密码（惰性迁移：明文存储在首次登录成功后升级为 bcrypt 哈希）
//  MN_zj.pass 为 FTP 密码、须明文展示给用户，不参与本机制
// ---------------------------------------------------------------------------
function mnbt_admin_password_is_hash($stored)
{
	return is_string($stored) && strlen($stored) === 60 && strncmp($stored, '$2y$', 4) === 0;
}

function mnbt_admin_password_hash($plain)
{
	return password_hash((string)$plain, PASSWORD_DEFAULT);
}

/** 校验管理员密码：哈希存储走 password_verify；明文历史数据兼容原始输入与 daddslashes 转义形态 */
function mnbt_admin_password_verify($plain, $stored)
{
	$plain = (string)$plain;
	$stored = (string)$stored;
	if ($stored === '') return false;
	if (mnbt_admin_password_is_hash($stored)) return password_verify($plain, $stored);
	return hash_equals($stored, $plain) || hash_equals($stored, daddslashes($plain));
}

/** BT 面板 cookie jar 路径：runtime/bt_cookie（web 根下的 api/cookie 会话 cookie 可被直读；含旧目录一次性迁移） */
function mnbt_bt_cookie_file($panel_url)
{
	$dir = ROOT . 'runtime/bt_cookie/';
	if (!is_dir($dir)) @mkdir($dir, 0755, true);
	$file = $dir . md5((string)$panel_url) . '.cookie';
	if (!is_file($file)) {
		$legacy = ROOT . 'api/cookie/' . md5((string)$panel_url) . '.cookie';
		if (is_file($legacy)) @rename($legacy, $file);
	}
	return $file;
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
	        $valarr = explode(";", (string)$val);
	        if($valarr[0]=='.user.ini' && $type=='file'){$userinisf=1; continue;}        //防跨站配置文件不用显示给用户
	        $arr['name'] = $valarr[0]; //文件名
	        $arr['type'] = $type; //文件类型
	        $arr['mtime'] = $valarr[2] ?? 0; //修改时间戳
	        $arr['size'] = $valarr[1] ?? 0; //文件大小
	        $arr['download']=$valarr[6] ?? 0;         //是否外链分享
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

/* ============================================================
 *  站点 base path / URL 助手（原 frontend.php V1.84，V1.88 起为通用设施；
 *  与"主页"无关，供插件视图 / 各端 boot 构建子目录部署安全的 URL）
 * ============================================================ */

/** 站点 base path（子目录部署前缀） */
function mnbt_home_base(): string {
	if (function_exists('mnbt_plugin_request_info')) {
		$info = mnbt_plugin_request_info();
		return $info['base'] ?? '';
	}
	$scriptName = isset($_SERVER['SCRIPT_NAME']) ? str_replace('\\', '/', $_SERVER['SCRIPT_NAME']) : '';
	$base = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');
	return ($base === '.' || $base === '/') ? '' : $base;
}

/** 生成带 base 前缀的路由 URL（index.php?_r=/path） */
function mnbt_home_url(string $path = ''): string
{
	$base = mnbt_home_base();
	$p = ltrim($path, '/');
	$qpos = strpos($p, '?');
	if ($qpos !== false) {
		$route = substr($p, 0, $qpos);
		$query = substr($p, $qpos + 1);
		return $base . '/index.php?_r=/' . $route . '&' . $query;
	}
	return $base . '/index.php?_r=/' . $p;
}

/** 生成带 base 前缀的核心物理文件 URL（如 user/login.php） */
function mnbt_home_core_url(string $path = ''): string
{
	return mnbt_home_base() . '/' . ltrim($path, '/');
}

/** 资源 URL：绝对地址（http(s):// 或 / 开头）原样返回，相对路径加 base 前缀 */
function mnbt_home_asset($path): string
{
	$path = (string)$path;
	if ($path === '') {
		return '';
	}
	if (preg_match('#^https?://#i', $path) || strpos($path, '/') === 0) {
		return $path;
	}
	return mnbt_home_base() . '/' . ltrim($path, '/');
}

/* ============================================================
 *  行为验证码（fastknife/ajcaptcha，根 vendor 手动 vendor）
 *  协议见 captcha.php；一次验证产物 captchaVerification 由登录接口二次校验
 * ============================================================ */

/** 客户端 IP（仅信任 MNBT_TRUSTED_PROXIES 声明的代理头） */
function mnbt_client_ip(): string
{
	if (mnbt_request_from_trusted_proxy()) {
		$xff = isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? trim(explode(',', (string)$_SERVER['HTTP_X_FORWARDED_FOR'])[0]) : '';
		if ($xff !== '' && filter_var($xff, FILTER_VALIDATE_IP)) return $xff;
	}
	return isset($_SERVER['REMOTE_ADDR']) ? trim((string)$_SERVER['REMOTE_ADDR']) : '0.0.0.0';
}

/** 验证码服务实例（blockPuzzle 滑动拼图 / clickWord 文字点选） */
function mnbt_captcha_service(string $type = 'blockPuzzle')
{
	require_once dirname(__DIR__) . '/vendor/autoload.php';

	global $conf;
	$expire = 300;
	$cachePath = dirname(__DIR__) . '/runtime/captcha';
	$config = [
		'watermark' => [
			'fontsize' => 12,
			'color' => '#ffffff',
			'text' => (string)($conf['name'] ?? 'MNBT'),
		],
		'block_puzzle' => [
			'mode' => 'drawing',
			'shape_type' => 'jigsaw',
			'backgrounds' => [],
			'templates' => [],
			'offset' => 8,
			'is_cache_pixel' => true,
			'is_interfere' => true,
			'blur_num' => 3,
		],
		'click_word' => [
			'backgrounds' => [],
			'word_num' => 3,
			'distract_num' => 2,
			'icons' => [],
			'icon_mode' => 'never',
			'min_icons' => 0,
			'max_icons' => 0,
			'icon_font_size_scale' => 1.3,
		],
		'cache' => [
			'constructor' => '\\Fastknife\\Utils\\CacheUtils',
			'method' => [],
			'options' => [
				'expire' => $expire,
				'prefix' => '',
				'path' => $cachePath,
				'serialize' => [],
			],
		],
	];
	return $type === 'clickWord'
		? new \Fastknife\Service\ClickWordCaptchaService($config)
		: new \Fastknife\Service\BlockPuzzleCaptchaService($config);
}

/**
 * 二次校验前端提交的 captchaVerification
 * @return true|string true 表示通过，否则返回给用户看的失败文案
 */
function mnbt_captcha_verify(string $encryptCode, string $type = 'blockPuzzle')
{
	if ($encryptCode === '') {
		return '请先完成人机验证';
	}
	try {
		mnbt_captcha_service($type)->verificationByEncryptCode($encryptCode);
		return true;
	} catch (\Throwable $e) {
		return '人机验证失败，请重新验证';
	}
}

/**
 * 验证码 provider 扩展点：验证码插件通过它接管内置验证码（"安装即退位"）
 *
 * 插件在 bootstrap.php 中注册（引擎按 priority 取第一个返回数组的插件）：
 *   mnbt_add_filter('captcha.provider', function ($provider) {
 *       if ($provider) return $provider;   // 已有其他验证码插件生效，让位
 *       return [
 *           'id'     => 'tcaptcha',        // 提供商标识
 *           'field'  => 'captchaToken',    // 登录请求携带的验证载荷字段（前端固定提交 captchaToken）
 *           'verify' => function (string $payload) {
 *               return true;               // true=通过；非空字符串=拒绝并作为用户提示文案
 *           },
 *       ];
 *   });
 * 插件停用后 filter 消失，内置验证码自动复位。
 * 同时插件应通过 filter('spa.boot', $boot) 注入 $boot['captcha'] =
 *   ['provider'=>'tcaptcha', 'adapter'=>'<适配器JS完整URL>']，
 * 适配器契约：window.MNBT_CAPTCHA_ADAPTER = { mount(el, {onSuccess, onFail}), reset() }，
 * onSuccess 收到的字符串将作为 captchaToken 提交登录接口。
 */
function mnbt_captcha_provider()
{
	$provider = mnbt_apply_filters('captcha.provider', null);
	if (!is_array($provider) || empty($provider['id']) || empty($provider['field']) || !is_callable($provider['verify'] ?? null)) {
		return null;
	}
	return $provider;
}

/** 调用 provider 二次校验：true 通过；异常/空载荷/非 true 一律拒绝（失败语义锁死，插件无法放行） */
function mnbt_captcha_provider_verify(array $provider, string $payload)
{
	if ($payload === '') {
		return '请先完成人机验证';
	}
	try {
		$rs = call_user_func($provider['verify'], $payload);
	} catch (\Throwable $e) {
		error_log('[MNBT captcha] provider ' . (string)$provider['id'] . ' verify 异常: ' . $e->getMessage());
		return '人机验证失败，请重试';
	}
	if ($rs === true) {
		return true;
	}
	return is_string($rs) && $rs !== '' ? $rs : '人机验证失败，请重试';
}

/* ============================================================
 *  限速（文件计数，runtime/temp/throttle）：登录防爆破 / 取码限频
 * ============================================================ */

function _mnbt_throttle_file(string $scope, string $identity): string
{
	$dir = dirname(__DIR__) . '/runtime/temp/throttle';
	if (!is_dir($dir)) @mkdir($dir, 0755, true);
	return $dir . '/' . hash('sha256', $scope . '|' . $identity) . '.json';
}

/** 窗口内已计数次数（窗口自首次计数起算， $window 秒） */
function mnbt_throttle_count(string $scope, string $identity, int $window): int
{
	$file = _mnbt_throttle_file($scope, $identity);
	if (!is_file($file)) return 0;
	if (time() - (int)filemtime($file) >= $window) return 0;
	$data = json_decode((string)@file_get_contents($file), true);
	return max(0, (int)($data['c'] ?? 0));
}

/** 计一次失败/请求（新窗口自动重置） */
function mnbt_throttle_hit(string $scope, string $identity, int $window): void
{
	$file = _mnbt_throttle_file($scope, $identity);
	$now = time();
	$count = 1;
	if (is_file($file) && $now - (int)filemtime($file) < $window) {
		$data = json_decode((string)@file_get_contents($file), true);
		$count = max(1, (int)($data['c'] ?? 0)) + 1;
	}
	@file_put_contents($file, json_encode(['c' => $count, 't' => $now]), LOCK_EX);
	@touch($file, $now);
}

/** 成功后清除计数 */
function mnbt_throttle_clear(string $scope, string $identity): void
{
	@unlink(_mnbt_throttle_file($scope, $identity));
}

/** 是否已被限速（超阈值拒绝） */
function mnbt_throttle_exceeded(string $scope, string $identity, int $max, int $window): bool
{
	return mnbt_throttle_count($scope, $identity, $window) >= $max;
}
?>