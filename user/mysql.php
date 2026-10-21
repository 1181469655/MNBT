<?php
@header('Content-Type: text/html; charset=UTF-8');
include("../MPHX/common.php");
if($islogins==1){}else exit("<script language='javascript'>window.location.href='./login.php';</script>");
$cert=$DB->get_row_prepare("SELECT * FROM MN_bt WHERE btdh=? limit 1", [$ssbt]);
?>
<?php
$cert=$DB->get_row_prepare("SELECT * FROM MN_bt WHERE btdh=? limit 1", [$ssbt]);
$btipe=($cert['ptl']=='true'?'https':'http').'://'.$cert['btip'].':'.$cert['btdk'];
$btkeye=$cert['btmy'];

include("./class.php");
$api = new bt_api_rj($btipe,$btkeye);
$data=$api->api_sql_cf() ?: [];
if(!($data['status'] ?? false)){$data=$api->api_sql_set('start') ?: []; $data=$api->api_sql_cf() ?: [];}

$ext_url = $data['ext']['url'] ?? '';
echo '自动登陆中...<b id=sjs>10秒后开启手动登陆</b><a id="djr" style="display:none" href="'.$ext_url.'">点击此处进行手动登陆</a>';
echo '
<script>
var spa = document.getElementById("sjs");
        let t = 10;
        setInterval(() => {
            t--;
            if(t>0){
            spa.innerText = t+"秒后开启手动登陆";
            }
            if (t == 0) {
            alert("请点击上方蓝色处进行跳转手动输入账号密码进行登陆");
			document.getElementById("sjs").innerHTML="自动登录失败！，";
			document.getElementById("djr").style.display="block";
            }
        }, 1000);
</script>
';

 echo "<form style='display:none;' id='form1' name='form1' method='post' action='".$ext_url."/index.php'> 
              <input name='pma_username' type='text' value='".$yhc['sqluser']."' />
              <input name='pma_password' type='text' value='".mnbt_secret_decrypt($yhc['sqlpass'] ?? '')."'/>
              <input name='server' type='text' value='1'/>
              <input name='target' type='text' value='index.php'/>
              <input name='token' type='text' value=''/>
              <input name='sbpostdl' type='text'  id='myform' value='登录'/>
            </form>
            <script type='text/javascript'>function load_submit(){document.form1.submit()}load_submit();</script>
";

?>