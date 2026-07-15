<?php mnbt_theme_include('head'); ?>
  <script type="text/javascript" src="<?=mnbt_asset_url('js/md5.js')?>"></script>
   <div class="container" style="padding-top:5%;">

<?php
$set=isset($_GET['gn'])?$_GET["gn"]:NULL;
if($set=='CDN_url' && $yhc['hxc']=='1'){ //CDN的URL控制
$cert=$DB->get_row_prepare("SELECT * FROM MN_bt WHERE btdh=? limit 1", [$ssbt]);
?>

<div class="col-sm-6">
      <div class="card">
        <div class="card-header">
          <h4>域名修改</br>
          <span class="h6"><?php if($cert['als']=='false'){echo '请将域名A记录到 '.$cert['btip'];}else{echo $cert['als'];}?></span>
</h4>
          <ul class="card-actions">
          </ul>
        </div>
        <div class="card-body">
        
          <div class="table-responsive">
            <table class="table table-bordered">
              <thead>
                <tr>
                  <th>域名</th>
                  <th>端口</th>
                  <th>操作</th>
                </tr>
              </thead>
              <tbody>
        <?php 
	include("./class.php");
	$ap_sz=0;
	$btipe=($cert['ptl']=='true'?'https':'http').'://'.$cert['btip'].':'.$cert['btdk'];
	$btkeye=$cert['btmy'];
	$api = new bt_api_set($btipe,$btkeye);
	$r_data = $api->btapi_ym($zjid);
	
	foreach($r_data as $are){
	if($are!='' && $are['name']!=$yhc['sqldz']){
	$ap_sz++;
	?>
                <tr>
                <td><div class="row col-xs-7" style="display:none" id="ydk<?=$ap_sz?>"><input type="text" class="form-control input-sm" id="ynk<?=$ap_sz?>" onblur="intay(<?=$ap_sz?>)"/></div>
    			<a href="http://<?=$are['name'].':'.$are['port']?>" class="text-success" id="ymk<?=$ap_sz?>"><?=$are['name']?></a></td>
                  <td><?=$are['port']?></td>
                  <td>
                    <div class="btn-group">
                    <button type="button" class="btn btn-xs btn-default" title="修改前缀" id="sgjl<?=$ap_sz?>" data-toggle="tooltip" onclick="xgym('<?=$are['name']?>','<?=$ap_sz?>','<?=$are['port']?>')"><i class="mdi mdi-pencil" id="tbfh<?=$ap_sz?>"></i></button>
                    <button type="button" class="btn btn-xs btn-default" title="删除记录" id="dejl<?=$ap_sz?>" data-toggle="tooltip" onclick="scym('<?=$are['name']?>','<?=$are['port']?>')"><i class="mdi mdi-window-close"></i></button>
                    </div>
                  </td>
                </tr>
     <?php }}?>
              </tbody>
            </table>
          </div>
          
          <div class="example-box">
            <label class="lyear-radio radio-inline radio-primary">
              <input type="radio" name="e" checked="" onclick="qh(1)"><span>自定义添加</span>
            </label>
            <label class="lyear-radio radio-inline radio-primary">
              <input type="radio" name="e" onclick="qh(2)"><span>本站二级域名</span>
            </label>
          </div>
          
          
          <div class="row m-b-10">
              <div class="col-lg-6">
                <div class="form-group">
                <input class="form-control" type="text" name="yz_ip" id="yz_ip" placeholder="源站IP">
                </div>
                <div class="input-group">
                  <input type="text" class="form-control" name="url" id="url" value="" placeholder="请在此输入域名"/>
                  <div class="input-group-btn">
                    <button type="button" class="btn btn-default dropdown-toggle btn-xs" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" id="ymqhq" style="display:none">请选择域名<span class="caret"></span></button>
                    <ul class="dropdown-menu">
                    <?php
                    $qbza=$cert['btdh'];
                    	$bym_list=$DB->query_prepare("SELECT * FROM MN_ym WHERE btdh=? and qk='true' order by id desc limit 9999", [$qbza]);
                    while($res = $DB->fetch($bym_list))
				{
				echo '<li><a href="#!" class="dropdown-item" onclick="ymqh('."'".$res['url']."'".','.$res['jg'].')">'.$res['url'].'<br/>价格：'.$res['jg'].'元<br/>简介：'.$res['js'].'</a></li>';
				}
                    ?>
                    </ul>
                    </div>
                    <div class="input-group-btn">
                    <button type="button" class="btn btn-default btn-primary" style="display:none" id="tjana" data-toggle="modal" data-target="#exampleModal" data-whatever="@mdo" onclick="zfs()">添加</button>
                    <button type="button" class="btn btn-default btn-primary" id="tjanb" onclick="tjym()">添加</button>
                  </div>
                  </div>
                </div>
              </div>
            </div>
            
            
            
          <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel">
            <div class="modal-dialog" role="document">
              <div class="modal-content">
                <div class="modal-header">
                  <h6 class="modal-title" id="exampleModalChangeTitle">购置二级域名</h6>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                  </button>
                </div>
                <div class="modal-body">
                    <h5 id="ts"></h5><br/>
                    <h5 id="hf"></h5><br/>
                    <h5>支付完成后将自动添加该域名！</h5><br/>
                    <h5>如果该前缀已经被其他主机绑定后将会为您随机化一个前缀</h5><br/>
                    <h5><b>您可以随时修改您域名的前缀！</b></h5><br/>
                    <h4 align="center">是否确认支付？</h4>
               <form action="pay.php" method="post" target="_blank" role="form">
        	     <input type="hidden" name="urla" id="urla"/>
        	     <input type="hidden" name="urlb" id="urlb"/>
        	     <input type="hidden" name="yzdip" id="yzdip"/>
        	     <input type="hidden" name="pay_lx" value="ymgm"/>
        	     <input type="hidden" name="urlzml" value="/"/>
        	     
	  <label for="web_site_logo">请选择支付方式</label>
          <div class="example-box">
              <div class="row">
            <?php $__pay_methods = function_exists('mnbt_get_enabled_payment_methods') ? mnbt_get_enabled_payment_methods() : []; ?>
            <?php foreach ($__pay_methods as $__idx => $__m): ?>
            <?php $__type = $__m['plugin'] . '__' . $__m['method']; ?>
            <label class="lyear-radio radio-inline radio-primary col">
              <input type="radio" name="type" value="<?php echo htmlspecialchars($__type); ?>" <?php echo $__idx === 0 ? 'checked' : ''; ?>><i class="mdi <?php echo htmlspecialchars($__m['icon'] ?? 'mdi-payment'); ?>"></i><span><?php echo htmlspecialchars($__m['display_name']); ?></span>
            </label>
            <?php endforeach; ?>
            <?php if (empty($__pay_methods)): ?>
            <label class="col text-muted">暂无可用支付方式，请联系管理员</label>
            <?php endif; ?>
            </div>
          </div>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
                  <button type="submit" class="btn btn-primary" id="zfgn" onclick="">确认支付</button>
            </form>
                </div>
              </div>
            </div>
          </div>
     
          
      </div>
<script type="text/javascript">
tjdymh=1;
inp_qk=false;

function intay(urlidsz){
var yip=yz_ip.value;
if(!inp_qk){msalert(3,'你都没编辑哪来的对焦？',2000); return;}
var inek=document.getElementById("ynk"+urlidsz).value;
if(inp_ysj==inek){
document.getElementById("ydk"+urlidsz).style.display="none";
document.getElementById("ymk"+urlidsz).innerHTML = inp_ysj+inp_qs;
inp_qk=false;
document.getElementById("tbfh"+urlidsz).className="mdi mdi-pencil";
return;
}else{
if(inp_qs.substr(0,1)=='.'){
var it_url=inp_qs.slice(1);
}else{
var it_url=inp_qs;
}
var sym=inp_ysj+inp_qs;
var ym=inek+inp_qs;
var port=pordk;
msloading('正在处理中，请稍后...');  // 加载显示
let data = {};
data["gn"]="seturl";
data["zym"]=it_url;
data["port"]=port;
data["jqz"]=inp_ysj;
data["xqz"]=inek;
data["yz_ip"]=yip;
$.post('./ajax.php', data, function (date) {
//alert(date);
var jsoe= JSON.parse(date);    
var qk= jsoe.code

if(qk=='添加成功'){
msalert(1,'修改成功！',2000);
document.getElementById("ydk"+urlidsz).style.display="none";
document.getElementById("ymk"+urlidsz).innerHTML = ym;
document.getElementById("ymk"+urlidsz).href = 'http://'+ym;
document.getElementById("tbfh"+urlidsz).className="mdi mdi-pencil";
window.location.href="./set.php?gn=CDN_url"
}else{
msalert(4,qk,2000);
document.getElementById("ydk"+urlidsz).style.display="none";
document.getElementById("ymk"+urlidsz).innerHTML = sym;
msloadingde();  // 隐藏
document.getElementById("tbfh"+urlidsz).className="mdi mdi-pencil";
}
inp_qk=false;                        
})
}
}

function xgym(url,urlidsz,port){
if(inp_qk){
msalert(3,'请完成当前编辑后再试！',2000);
return;
}
var wz=url.indexOf('.');
var qz=url.substring(0,wz);
var qzh=url.substring(wz);
document.getElementById("ydk"+urlidsz).style.display="block";
document.getElementById("ynk"+urlidsz).value = qz;
document.getElementById("ymk"+urlidsz).innerHTML = qzh;
document.getElementById("tbfh"+urlidsz).className="mdi mdi-checkbox-marked-circle-outline";
//alert(urlidsz);
$('input[id=ynk'+urlidsz+']').focus();
inp_ysj=qz;
inp_qs=qzh;
pordk=port;
inp_qk=true;
}

function zfs(){
var p = /^[0-9a-zA-Z]{1,24}$/;
if(!p.test(url.value)){msalert(3,'只能输入数字和英文！',2000); document.getElementById("zfgn").type='button'; return;}
msloading('正在处理中，请稍后...');  // 加载显示
var yer=ymqhq.innerHTML;
var ym=url.value+yer;
if(yer.indexOf('请选择域名')!='-1' || url.value==""){
msalert(3,'请选择域名和输入前缀！',2000);
document.getElementById("zfgn").onclick='';
document.getElementById("zfgn").type='button';
msloadingde();
return;
}
if(bl_jg>'0'){
//set pay
document.getElementById("zfgn").type='submit';
document.getElementById("ts").innerHTML = '您正在添加域名：<b>'+ym+'</b>';
document.getElementById("hf").innerHTML = '购置此二级域名将会花费<b>'+bl_jg+'</b>元';
document.getElementById("urla").value = bl_zym;
document.getElementById("urlb").value = url.value;
document.getElementById("yzdip").value = yz_ip.value;
msloadingde();  // 隐藏
}else{
document.getElementById("zfgn").type='button';
var input = document.getElementById("zfgn");  
document.getElementById("zfgn").onclick = tjym();
}
}

function qh(abd){
if(abd=="1"){
tjdymh=1;
document.getElementById("ymqhq").style.display="none";
document.getElementById("url").placeholder='请在此输入域名';
document.getElementById("tjana").style.display="none";
document.getElementById("tjanb").style.display="block";
}else{
tjdymh=2;
document.getElementById("ymqhq").style.display="block";
document.getElementById("url").placeholder='前缀';
document.getElementById("tjana").style.display="block";
document.getElementById("tjanb").style.display="none";
}
}

function ymqh(url,je) {
bl_zym=url;
bl_jg=je;
document.getElementById("ymqhq").innerHTML='.'+url;
}

function scym(url,port) {
msloading('正在处理中，请稍后...');  // 加载显示
let data = {};
data["gn"]="scurl";
data["url"]=url;
data["port"]=port;
$.post('./ajax.php', data, function (date) {
var jsoe= JSON.parse(date);    
var qk= jsoe.code

if(qk=='删除成功'){
msalert(1,'删除成功！',2000);
msloadingde();  // 隐藏
window.location.href="./set.php?gn=CDN_url"
}else{
msalert(4,qk,2000);
msloadingde();  // 隐藏
}                        
})
}
function tjym() {
var ms=tjdymh;
var yip=yz_ip.value;
if(ms==2){
var yer=ymqhq.innerHTML;
var ym=url.value+yer;
if(yer.indexOf('请选择域名')!='-1'){msalert(3,'请选择域名和输入前缀！',2000);return;}
}else{
var ym=url.value;
}

if(ym=="" || url.value=="" || yip==""){
msalert(3,'请填写域名和源站IP！',2000);
msloadingde();  // 隐藏
}
else
{
msloading('正在处理中，请稍后...');  // 加载显示
let data = {};
data["gn"]="tjurl";
data["url"]=ym;
data["yz_ip"]=yip;
$.post('./ajax.php', data, function (date) {
var jsoe= JSON.parse(date);    
var qk= jsoe.code

if(qk=='添加成功'){
msalert(1,'添加成功！',2000);
//msloadingde();  // 隐藏
window.location.href="./set.php?gn=CDN_url"
}else{
msalert(4,qk,2000);
msloadingde();  // 隐藏
}                        
})
}}
</script>


<?php
}
elseif($set == "nginxrz")
{
    
}
elseif($set=='url' && $yhc['hxc']!='1'){
$cert=$DB->get_row_prepare("SELECT * FROM MN_bt WHERE btdh=? limit 1", [$ssbt]);
?>
<!--对话框-->
<script type="text/javascript" src="<?=mnbt_asset_url('js/jquery-confirm/jquery-confirm.min.js')?>"></script>
<script type="text/javascript" src="<?=mnbt_asset_url('js/bootstrap-table/bootstrap-table.min.js')?>"></script>
<script type="text/javascript" src="<?=mnbt_asset_url('js/bootstrap-table/locale/bootstrap-table-zh-CN.min.js')?>"></script>
<div class="col-lg-5">
      <div class="card">
        <div class="card-header">
          <h4>域名修改</br>
          <span class="h6"><?php if($cert['als']=='false'){echo '请将域名A记录到 '.$cert['btip'];}else{echo $cert['als'];}?></span>
</h4>
          <ul class="card-actions">
          </ul>
        </div>
        <div class="card-body">
        <table class="table table-bordered">
            <thead>
              <tr>
                <th>域名</th>
                <th>端口</th>
                <th>目录</th>
                <th>操作</th>
              </tr>
            </thead>
            <tbody id="urllist">
                <tr>
                <td><div class="row col-xs-7" style="display:none" id="ydk"><input type="text" class="form-control input-sm" id="ynk"/></div>
    			<a href="#!" class="text-success" id="ymk">正在获取域名中，请稍后...</a></td>
                  <td>80</td>
                  <td>
                    <div class="btn-group">
                    <button type="button" class="btn btn-xs btn-default use-url" title="修改前缀" data-toggle="tooltip"><i class="mdi mdi-pencil"></i></i></button>
                    <button type="button" class="btn btn-xs btn-default del-url" title="删除记录" data-toggle="tooltip"><i class="mdi mdi-window-close"></i></button>
                    </div>
                  </td>
                </tr>
            </tbody>
          </table>
          <!--style="display:none"-->
        <div class="custom-control custom-radio custom-control-inline">
	        <input type="radio" id="urladdms" name="urladdms" class="custom-control-input" checked>
	        <label class="custom-control-label" for="urladdms">自定义添加</label>
	      </div>
	      <div class="custom-control custom-radio custom-control-inline">
	        <input type="radio" id="urladdms2" name="urladdms" class="custom-control-input">
	        <label class="custom-control-label" for="urladdms2">本站二级域名</label>
	      </div>
	      
          <div class="input-group mb-3">
            <input type="text" class="form-control" name="url" id="url" placeholder="请在此输入域名">
            <div class="input-group-append">
              <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" id="ymqhq" style="display:none">请选择域名<span class="caret"></span></button>
            <ul class="dropdown-menu">
			<li><a href="#!" class="dropdown-item" onclick="">a.a<br/>价格：元<br/>简介：</a></li>
            </ul>
              <button type="button" class="btn btn-default btn-primary" id="tjurl">添加</button>
            </div>
          </div>
          
            <div class="form-group">
              <select class="custom-select form-control-sm">
                <option>/</option>
              </select>
              <small><b>域名子目录，如果无特殊需求则推荐默认</b><br/>会自动显示主机文件中的目录<br/>如果设置了运行目录则会显示运行目录中的目录</small>
            </div>
          
                  
                </div>
              </div>
            
            
            
          <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel">
            <div class="modal-dialog" role="document">
              <div class="modal-content">
                <div class="modal-header">
                  <h6 class="modal-title" id="exampleModalChangeTitle">购置二级域名</h6>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                  </button>
                </div>
                <div class="modal-body">
                    <h5 id="ts"></h5><br/>
                    <h5 id="hf"></h5><br/>
                    <h5>支付完成后将自动添加该域名！</h5><br/>
                    <h5>如果该前缀已经被其他主机绑定那我们将会为您随机化一个前缀</h5><br/>
                    <h5><b>您可以随时修改您域名的前缀！</b></h5><br/>
                    <h4 align="center">是否确认支付？</h4>
               <form action="pay.php" method="post" target="_blank" role="form">
        	     <input type="hidden" name="urla" id="urla"/>
        	     <input type="hidden" name="urlb" id="urlb"/>
        	     <input type="hidden" name="urlzml" id="urlzml" value="/"/>
        	     <input type="hidden" name="pay_lx" value="ymgm"/>
        	     
	  <label for="web_site_logo">请选择支付方式</label>
          <div class="example-box">
              <div class="row">
            <?php $__pay_methods = function_exists('mnbt_get_enabled_payment_methods') ? mnbt_get_enabled_payment_methods() : []; ?>
            <?php foreach ($__pay_methods as $__idx => $__m): ?>
            <?php $__type = $__m['plugin'] . '__' . $__m['method']; ?>
            <label class="lyear-radio radio-inline radio-primary col">
              <input type="radio" name="type" value="<?php echo htmlspecialchars($__type); ?>" <?php echo $__idx === 0 ? 'checked' : ''; ?>><i class="mdi <?php echo htmlspecialchars($__m['icon'] ?? 'mdi-payment'); ?>"></i><span><?php echo htmlspecialchars($__m['display_name']); ?></span>
            </label>
            <?php endforeach; ?>
            <?php if (empty($__pay_methods)): ?>
            <label class="col text-muted">暂无可用支付方式，请联系管理员</label>
            <?php endif; ?>
          </div>
            </div>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
                  <button type="submit" class="btn btn-primary" id="zfgn" onclick="">确认支付</button>
            </form>
                </div>
              </div>
            </div>
          </div>
     
          
          
     
        </footer>
      </div>
<script type="text/javascript">
urllist();
smurl();

$("#urllist").on("click",".use-url",function(){     //修改域名
var setlis=$(this.parentNode.parentNode.parentNode).children()
var url=setlis[0].innerText       //获取域名
var port=setlis[1].innerText       //获取端口
var paths=setlis[2].innerText       //获取域名子目录
var wz=url.indexOf('.');
var qz=url.substring(0,wz);
var qzh=url.substring(wz);
if(qzh.substr(0,1)=='.'){
var it_url=qzh.slice(1);
}else{
var it_url=qzh;
}
var dirlist='';
let data={};
data["gn"]="urllist";
$.post('./ajax.php', data,function (date) {
var arr= JSON.parse(date);
$.each(arr.dir,function(){
if(this==paths){
dirlist+='<option selected>'+this+'</option>';
}else{
dirlist+='<option>'+this+'</option>';
}
});
    $.confirm({
        title: '修改域名',
        content: '<div class="form-group p-1 mb-0">' + 
                 '  <label class="control-label">域名前缀</label>' +
                 '  <input autofocus="" type="text" id="input-name" value='+qz+' placeholder="请输入您域名的新前缀" class="form-control">' +
              '<select class="custom-select form-control-sm" id="input-sel">'
                +dirlist+
              '</select>'+
                 '</div>',
        buttons: {
            sayMyName: {
                text: '确定修改',
                btnClass: 'btn-primary',
                action: function() {
                    var input = this.$content.find('input#input-name');
                    var sel = this.$content.find('select#input-sel');
                    if (!$.trim(input.val())) {
                        $.alert({
                            content: "前缀不能为空！",
                            type: 'red'
                        });
                        return false;
                    } else {
                        msloading('正在处理中，请稍后...');  // 加载显示
                        let data = {};
                        data["gn"]="seturl";
                        data["zym"]=it_url;
                        data["port"]=port;
                        data["jqz"]=qz;
                        data["xqz"]=input.val();
                        data["path"]=sel.val();
                        $.post('./ajax.php', data, function (date) {
                        var jsoe= JSON.parse(date);    
                        var qk= jsoe.code

                        if(qk=='添加成功'){
                        msalert(1,'修改成功！',2000);
                        urllist();          //刷新页面列表
                        msloadingde();  // 隐藏
                        }else{
                        msalert(4,qk,2000);
                        msloadingde();  // 隐藏
                        }
                    })
                }
            }
            },
            '取消': function() {
            }
        }
    });
});
});

function urllist(){
//获取域名列表
msloading('正在加载中，请稍后... ♪（^∇^*）');
let data = {};
data["gn"]="urllist";
$.post('./ajax.php', data,function (date) {
var arr= JSON.parse(date);
var urllist='';
var dirlist='';
$.each(arr.url,function(){
urllist+='<tr><td>'+
'<a target="_blank" href="http://'+this.name+':'+this.port+'/" class="text-success">'+this.name+'</a></td>'+
'<td>'+this.port+'</td>'+
'<td>'+this.path+'</td>'+
'<td><div class="btn-group">'+
'<button type="button" class="btn btn-xs btn-default use-url" title="修改前缀" data-toggle="tooltip"><i class="mdi mdi-pencil"></i></i></button>'+
'<button type="button" class="btn btn-xs btn-default del-url" title="删除记录" data-toggle="tooltip"><i class="mdi mdi-window-close"></i></button>'+
'</div>'+
'</td>'+
'</tr>';
});
$.each(arr.dir,function(){
dirlist+='<option>'+this+'</option>';
});
$("#urllist").html(urllist);
$(".custom-select").html(dirlist);

$(function () {     //初始化所有的tooltip提示
  $('[data-toggle="tooltip"]').tooltip()
})

msloadingde();
});
}

function smurl(){
//获取站点售卖级域名列表
msloading('正在加载中，请稍后... ♪（^∇^*）','#ymqhq');
let data = {};
data["gn"]="erurl";
$.post('./ajax.php', data,function (date) {
var arr= JSON.parse(date);
var urllist='';
$.each(arr,function(){
urllist+='<li><a href="#!" class="dropdown-item" onclick="$(`#ymqhq`).html(`'+this.url+'`);bl_jg='+this.jg+';">'+this.url+'<br/>价格：'+this.jg+'元<br/>简介：'+this.jj+'</a></li>';
});
$(".dropdown-menu").html(urllist);
msloadingde('#ymqhq');
});
}

$("#tjurl").on("click",function(){      //添加域名
var ms=$("#urladdms").prop("checked");
if(!ms){
var yer=$("#ymqhq").html();
var ym=$("#url").val()+'.'+yer;
if(yer.indexOf('请选择域名')!='-1'){msalert(3,'请选择域名和输入前缀！',2000);return;}
if(bl_jg>0){
var p = /^[0-9a-zA-Z]{1,24}$/;
if(!p.test($("#url").val())){msalert(3,'只能输入数字和英文！',2000);return;}
$('#exampleModal').modal();		//弹出弹窗
zfs();

return;
}
}else{
var ym=$("#url").val();
}
if(ym==""){
msalert(3,'请填写域名！',2000);
return;
}
var urldir=$(".custom-select").val();
msloading('正在处理中，请稍后...');  // 加载显示
let data = {};
data["gn"]="tjurl";
data["url"]=ym;
data["dirs"]=urldir;
$.post('./ajax.php', data, function (date) {
var jsoe= JSON.parse(date);    
var qk= jsoe.code

if(qk=='添加成功'){
msalert(1,'添加成功！',2000);
urllist();
msloadingde();  // 隐藏
}else{
msalert(4,qk,2000);
msloadingde();  // 隐藏
}                        
})
})

$("#urllist").on("click",".del-url",function(){     //删除域名
var setlis=$(this.parentNode.parentNode.parentNode).children()
var url=setlis[0].innerText       //获取域名
var port=setlis[1].innerText       //获取端口
var dir=setlis[2].innerText       //获取目录
msloading('正在删除中，请稍后...');  // 加载显示
let data = {};
data["gn"]="scurl";
data["url"]=url;
data["port"]=port;
data["dir"]=dir;
$.post('./ajax.php', data, function (date) {
var jsoe= JSON.parse(date);    
var qk= jsoe.code

if(qk=='删除成功'){
msalert(1,'删除成功！',2000);
urllist();
msloadingde();  // 隐藏
}else{
msalert(4,qk,2000);
urllist();
msloadingde();  // 隐藏
}
})
})

$(".custom-control-input").on("change",function(){      //域名添加模式切换
if(this.id=='urladdms'){
document.getElementById("ymqhq").style.display="none";
document.getElementById("url").placeholder='请在此输入域名';
}else{
document.getElementById("ymqhq").style.display="block";
document.getElementById("url").placeholder='前缀';
}
})

function zfs(){
var yer=$("#ymqhq").html();
var ym=$("#url").val()+'.'+yer;
var p = /^[0-9a-zA-Z]{1,24}$/;
if(!p.test($("#url").val())){msalert(3,'只能输入数字和英文！',2000);setTimeout(function(){$('#exampleModal').modal('hide');},500); document.getElementById("zfgn").type='button';return;}
msloading('正在处理中，请稍后...');  // 加载显示
if(yer.indexOf('请选择域名')!='-1' || $("#url").val()==false){
msalert(3,'请选择域名和输入前缀！',2000);
setTimeout(function(){$('#exampleModal').modal('hide');},500);
document.getElementById("zfgn").type='button';
msloadingde();
return;
}
//set pay
document.getElementById("zfgn").type='submit';
document.getElementById("ts").innerHTML = '您正在添加域名：<b>'+ym+'</b>';
document.getElementById("hf").innerHTML = '购置此二级域名将会花费<b>'+bl_jg+'</b>元';
document.getElementById("urla").value = yer;
document.getElementById("urlb").value = $("#url").val();
document.getElementById("urlzml").value = $(".custom-select").val();
msloadingde();  // 隐藏
}

</script>
<?php }elseif($set=='pass'){
$cert=$DB->get_row_prepare("SELECT * FROM MN_bt WHERE btdh=? limit 1", [$ssbt]);
?>
<div class="col-sm-6">
      <div class="card">
        <div class="card-header">
          加密访问设置</br>
目录设置加密访问后，访问时需要输入账号密码才能访问</br>
例如我设置了加密访问 /test/ ,那我访问 http://aaa.com/test/ 是就要输入账号密码才能访问
          <ul class="card-actions">
          </ul>
        </div>
        <div class="card-body">
        
          <div class="table-responsive">
            <table class="table table-bordered">
              <thead>
                <tr>
                  <th>名称</th>
                  <th>目标目录</th>
                  <th>操作</th>
                </tr>
              </thead>
              <tbody>
        <?php 
	include("./class.php");
	$btipe=($cert['ptl']=='true'?'https':'http').'://'.$cert['btip'].':'.$cert['btdk'];
	$btkeye=$cert['btmy'];
	$api = new bt_api($btipe,$btkeye);
	$r_data = $api->GetLogs($zjid);
	if($cert['btos']=='2'){
	$idsz=$r_data;
	}else{
	$idsz=$r_data[$yhc['sqldz']] ?? [];
	}
	foreach($idsz as $are){?>

                <tr>
                  <td><?=$are['name']?></td>
                  <td><?=$are['site_dir']?></td>
                  <td>
                    <div class="btn-group">
                    <button type="button" class="btn btn-xs btn-default" onclick="scym(del='<?=$are['name']?>')"><i class="mdi mdi-window-close"></i></button>
                    </div>
                  </td>
                </tr>
<?php
}
?>
              </tbody>
            </table>
          </div>
          
                    <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel">
            <div class="modal-dialog" role="document">
              <div class="modal-content">
                <div class="modal-header">
                  <h6 class="modal-title" id="exampleModalChangeTitle">添加密码访问</h6>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                  </button>
                </div>
                <div class="modal-body">
                  <form>
                    <div class="form-group">
                      <label for="recipient-name" class="control-label">名称：</label>
                      <input type="text" class="form-control" name="names" id="names" placeholder="请随意填写 不可重复！">
                    </div>
                    <div class="form-group">
                      <label for="recipient-name" class="control-label">目标目录：</label>
                      <input type="text" class="form-control" name="tkol" id="tkol" placeholder="输入要加密访问的目录如：/text/">
                    </div>
                    <div class="form-group">
                      <label for="message-text" class="control-label">账号：</label>
                      <input type="text" class="form-control" name="zh" id="zh" placeholder="请输入访问页面时要输入的账号"></input>
                    </div>
                    <div class="form-group">
                      <label for="message-text" class="control-label">密码：</label>
                      <input type="text" class="form-control" name="mm" id="mm" placeholder="请输入访问页面时要输入的密码"></input>
                    </div>
              </div>
                  </form>
                <div class="modal-footer">
                  <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
                  <button type="button" class="btn btn-primary" onclick="tj()">确认添加</button>
                </div>
              </div>
            </div>
          </div>
                  <div class="input-group-btn"><button class="btn btn-default btn-xs btn btn-primary form-control"  type="button" data-toggle="modal" data-target="#exampleModal" data-whatever="@mdo"><i class="mdi mdi-checkbox-marked-circle-outline"></i>添加</button></div>
                </div>
              </div>
        </footer>
      </div>
<script type="text/javascript">
function scym(url) {
msloading('正在删除中，请稍后...');  // 加载显示
let data = {};
data["gn"]="scmmfw";
data["mb"]=url;
$.post('./ajax.php', data, function (date) {
var jsoe= JSON.parse(date);    
var qk= jsoe.code

if(qk=='删除成功'){
msalert(1,'删除成功！',2000);
msloadingde();  // 隐藏
window.location.href="./set.php?gn=pass"
}else{
msalert(4,qk,2000);
msloadingde();  // 隐藏
}                        
})
}
function tj() {
var mz=names.value;
var ml=tkol.value;
var user=zh.value;
var pass=mm.value;
if(mz=="" || ml=="" || user=="" || pass==""){
msalert(3,'不能留空！',2000);
}
else
{
msloading('正在添加中，请稍后...');  // 加载显示
let data = {};
data["gn"]="tjmmfw";
data["name"]=mz;
data["mbml"]=ml;
data["user"]=user;
data["pass"]=pass;
$.post('./ajax.php', data, function (date) {
var jsoe= JSON.parse(date);    
var qk= jsoe.code
if(qk=='创建成功'){
msalert(1,'添加成功！',2000,'#exampleModal');
msloadingde();  // 隐藏
window.location.href="./set.php?gn=pass"
}else{
msalert(4,qk,2000,'#exampleModal');
msloadingde();  // 隐藏
}                        
})
}}
</script>
<?php }elseif($set=='mrwd'){
$cert=$DB->get_row_prepare("SELECT * FROM MN_bt WHERE btdh=? limit 1", [$ssbt]);
?>
<div class="col-sm-6">
      <div class="card">
        <div class="card-header">
          <h4>默认文档修改</br>
</h4>
          <ul class="card-actions">
          </ul>
        </div>
        <div class="card-body">
        
    <?php 
	include("./class.php");
	$btipe=($cert['ptl']=='true'?'https':'http').'://'.$cert['btip'].':'.$cert['btdk'];
	$btkeye=$cert['btmy'];
	$api = new bt_api($btipe,$btkeye);
	$r_data = $api->GetLogseb($zjid);
	?>

                <div class="form-group">
                  <textarea  type="text" class="form-control" rows="5" name="url" id="url" placeholder="请在此输入文档"><?=$r_data?></textarea>
                  <small class="help-block"><strong>每个文档请使用 , 隔开！不得出现空格等情况！先后顺序代表文档的优先级</strong></small>
                  </div>
                  <div class="input-group-btn"><button class="btn btn-default btn btn-primary form-control" type="button" onclick="xg()"><label><i class="mdi mdi-checkbox-marked-circle-outline"></i></label>确定修改</button></div>
                </div>
              </div>
      </div>
<script type="text/javascript">
function xg() {
var ym=url.value;
if(ym==""){
msalert(3,'不能为空！',2000);
}
else
{
msloading('正在处理中，请稍后...');  // 加载显示
let data = {};
data["gn"]="xgmrwd";
data["ml"]=ym;
$.post('./ajax.php', data, function (date) {
var jsoe= JSON.parse(date);    
var qk= jsoe.code

if(qk=='设置成功'){
msalert(1,'修改成功！',2000);
msloadingde();  // 隐藏
}else{
msalert(4,qk,2000);
msloadingde();  // 隐藏
}                        
})
}}
</script>

<?php }
elseif($set=="nginx")
{
    $cert=$DB->get_row_prepare("SELECT * FROM MN_bt WHERE btdh=? limit 1", [$ssbt]);
?>
<div class="col-sm-6">
      <div class="card">
        <div class="card-header">
          <h4>nginx配置文件</br>
</h4>
          <ul class="card-actions">
          </ul>
        </div>
        <div class="card-body">
        
    <?php 
	include("./class.php");
	$btipe=($cert['ptl']=='true'?'https':'http').'://'.$cert['btip'].':'.$cert['btdk'];
	$btkeye=$cert['btmy'];
	$api = new bt_api($btipe,$btkeye);
	$name = $yhc['sqldz'];
	$r_data = $api->Getnginx($name);
	?>

                <div class="form-group">
                  <textarea  type="text" class="form-control" rows="20" name="url" id="url" placeholder="请在此输入文档"><?php echo ($r_data['data']) ;?></textarea>
              </div>
      </div>
<?php
}

elseif($set == "mysqlcz")
{
    include("./class.php");
    $cert=$DB->get_row_prepare("SELECT * FROM MN_bt WHERE btdh=? limit 1", [$ssbt]);
	$btipe=($cert['ptl']=='true'?'https':'http').'://'.$cert['btip'].':'.$cert['btdk'];
	$btkeye=$cert['btmy'];
	$api = new bt_api($btipe,$btkeye);
	$name = $yhc['sqluser'];
    $r_data = $api->GetDatabaseAccess($name);
	?>
	<!--对话框-->
<script type="text/javascript" src="<?=mnbt_asset_url('js/jquery-confirm/jquery-confirm.min.js')?>"></script>
<script type="text/javascript" src="<?=mnbt_asset_url('js/bootstrap-table/bootstrap-table.min.js')?>"></script>
<script type="text/javascript" src="<?=mnbt_asset_url('js/bootstrap-table/locale/bootstrap-table-zh-CN.min.js')?>"></script>
<div class="col-sm-6">
      <div class="card">
        <div class="card-header">
          <h4>设置数据库权限</br>
</h4>
          <ul class="card-actions">
          </ul>
        </div>
        <div class="card-body">
<div class="form-group">
                <select class="form-control" id="databaseaq" name="databaseaq" size="1">
                    <?php 
                    var_dump($r_data);
                    if($r_data['msg'] == "127.0.0.1")
                    {
                        echo '<option value="127.0.0.1">本地服务器</option>';
                        echo '<option value="%">所有人(不安全)</option>';
                        echo '<option value="%">指定IP</option>';
                        
                    }
                    elseif($r_data['msg'] == "%")
                    {
                        echo '<option value="%">所有人(不安全)</option>';
                        echo '<option value="127.0.0.1">本地服务器</option>';
                        echo '<option value="127.0.0.1">指定IP</option>';
                        
                    }
                    else
                    {
                        echo '<option value="127.0.0.1">指定IP</option>';
                        echo '<option value="%">所有人(不安全)</option>';
                        echo '<option value="127.0.0.1">本地服务器</option>';
                        
                    }
                    
                    ?>
                    
                </select>
                </div>
                  <div class="input-group-btn"><button class="btn btn-default btn btn-primary form-control" onclick="databaseaqbc1()" type="button"><label><i class="mdi mdi-checkbox-marked-circle-outline"></i></label>保存</button></div>
                </div>
              </div>
      </div>
<script type="text/javascript">
function databaseaqbc1()
{
    msloading('正在处理中，请稍后...');  // 加载显示
    let option = databaseaq.value;
    let data = {};
    data["gn"]="databaseaq1";
    data["dataAccess"]=option;
    $.post('./ajax.php', data, function (date) {
    var jsoe= JSON.parse(date);    
    var qk= jsoe.code;
    if(qk=='设置成功')
    {
        msalert(1,'设置成功！',2000);
        msloadingde();  // 隐藏
    }
    else
    {
        msalert(4,qk,2000);
        msloadingde();  // 隐藏
        }
    })
}
</script>

<?php
}
elseif($set=="php")
{

$cert=$DB->get_row_prepare("SELECT * FROM MN_bt WHERE btdh=? limit 1", [$ssbt]);
$btipe=($cert['ptl']=='true'?'https':'http').'://'.$cert['btip'].':'.$cert['btdk'];
$btkeye=$cert['btmy'];
if($cert['btos']=='1'){
$os_xt=$conf['hxi'].'/';
}else{
$os_xt=$conf['hxo'].'/';
}
include("./class.php");
$api = new bt_api($btipe,$btkeye);
//php版本获取
$apist = new bt_api_set($btipe,$btkeye);
$r_data = $apist->btapi_listphp();
unset($r_data[0]);			//由于纯静态通过APi切换后再切换为其他PHP版本部分宝塔会报错，等待宝塔官方修复这个问题，所以暂时关闭纯静态选项
unset($r_data[1]);			//关闭自定义选项
$php_version = $apist->btapi_phpnowz($yhc['sqldz'])['phpversion'];
?>

	<!--对话框-->
<script type="text/javascript" src="<?=mnbt_asset_url('js/jquery-confirm/jquery-confirm.min.js')?>"></script>
<script type="text/javascript" src="<?=mnbt_asset_url('js/bootstrap-table/bootstrap-table.min.js')?>"></script>
<script type="text/javascript" src="<?=mnbt_asset_url('js/bootstrap-table/locale/bootstrap-table-zh-CN.min.js')?>"></script>
<div class="col-sm-6">
      <div class="card">
        <div class="card-header">
          <h4>php版本切换</br>
</h4>
          <ul class="card-actions">
          </ul>
        </div>
        <div class="card-body">
<div class="form-group">
                <select class="form-control" id="phpsave" name="phpsave" size="1">
                    <?php 
                        echo "<option value='$php_version'>PHP-"."$php_version</option>";
                        foreach (($r_data ?: []) as $value_array)
                        {
                            $name = $value_array['name'];
                            $version = $value_array['version'];
                            if($version != $php_version)
                            {
                                echo "<option value='$version'>$name</option>";
                            }
                                
                            }
                    ?>
                    
                </select>
                </div>
                  <div class="input-group-btn"><button class="btn btn-default btn btn-primary form-control" onclick="databaseaqbc1()" type="button"><label><i class="mdi mdi-checkbox-marked-circle-outline"></i></label>保存</button></div>
                </div>
              </div>
      </div>
<script type="text/javascript">
function databaseaqbc1()
{
    msloading('正在处理中，请稍后...');  // 加载显示
    let option = phpsave.value;
    let data = {};
    data["gn"]="phpxg";
    data["php"]=option;
    $.post('./ajax.php', data, function (date) {
    var jsoe= JSON.parse(date);    
    var qk= jsoe.code;
    if(qk=='修改成功')
    {
        msalert(1,'修改成功！',2000);
        msloadingde();  // 隐藏
    }
    else
    {
        msalert(4,qk,2000);
        msloadingde();  // 隐藏
        }
    })
}
</script>

<?php
}
elseif($set=='wjt'){
$cert=$DB->get_row_prepare("SELECT * FROM MN_bt WHERE btdh=? limit 1", [$ssbt]);
?>
<div class="col-sm-6">
      <div class="card">
        <div class="card-header">
          <h4>伪静态设置</br>
</h4>
          <ul class="card-actions">
          </ul>
        </div>
        <div class="card-body">
        
    <?php 
	include("./class.php");
	$btipe=($cert['ptl']=='true'?'https':'http').'://'.$cert['btip'].':'.$cert['btdk'];
	$btkeye=$cert['btmy'];
	$api = new bt_api($btipe,$btkeye);
	$r_daoe = $api->GetLogswr($yhc['sqldz']);
	if($cert['btos']=='1'){
	$r_data = $api->GetLogswt('/www/server/panel/vhost/rewrite/'.$yhc['sqldz'].'.conf');
	}else{
	$api = new win_bt_api($btipe,$btkeye);
	$r_data = $api->wjt_hqdq($yhc['sqldz']);
	}
	?>
<div class="form-group">
                <select class="form-control" id="btdh" name="btdh" size="1" onchange="_sel(this.options[this.options.selectedIndex])">
                <?php
                foreach (($r_daoe['rewrite'] ?? []) as $value) {
                  echo '
                  <option value="'.$value.'">'.$value.'</option>
                  ';
                  }?>
                </select>
                </div>
            <div class="form-group">
                  <textarea  type="text" class="form-control" rows="10" name="url" id="url" placeholder="请在此输入伪静态规则"><?=$r_data['data']?></textarea>
                  <small class="help-block"><strong><li>请选择您的应用，若设置伪静态后，网站无法正常访问，请尝试设置回default</li>
<li>您可以对伪静态规则进行修改，修改完后保存即可。</li>
<li><a href="https://www.bt.cn/Tools">Apache转Nginx工具</a></li></strong></small>
                  </div>
                  <div class="input-group-btn"><button class="btn btn-default btn btn-primary form-control" type="button" onclick="xg()"><label><i class="mdi mdi-checkbox-marked-circle-outline"></i></label>保存</button></div>
                </div>
              </div>
      </div>
<script type="text/javascript">
function _sel(val){
var vio=val.value;
if(vio==""){
msalert(3,'？？？？你是咋选出这个来的？？？？',2000);
}
else
{
msloading('正在处理中，请稍后...');  // 加载显示
let data = {};
data["gn"]="hqjt";
data["xz"]=vio;
$.post('./ajax.php', data, function (date) {

//msalert(1,'数据获取成功！',2000);
document.getElementById("url").value = date; 
msloadingde();  // 隐藏
})
}}

function xg() {
var ym=url.value;
msloading('正在处理中，请稍后...');  // 加载显示
let data = {};
data["gn"]="setwjt";
data["wb"]=ym;
$.post('./ajax.php', data, function (date) {
var jsoe= JSON.parse(date);    
var qk= jsoe.code;

if(qk=='文件已保存!'){
msalert(1,'保存成功！',2000);
msloadingde();  // 隐藏
}else{
msalert(4,qk,2000);
msloadingde();  // 隐藏
}
})
}
</script>

<?php
}
elseif($set=='fdl')
{
?>
<div class="col-6">
      <div class="card">
        <div class="card-header">
          <h4>防盗链设置</br>
</h4>
          <ul class="card-actions">
          </ul>
        </div>
        <div class="card-body">
<div class="form-group">
    <label for="message-text" class="control-label">URL后缀</label>
                <input class="form-control" id="btfdlurl" value="" placeholder="请在此输入URL后缀" name="btfdlurl" size="1">
                
                </div>
                <div class="form-group">
                    <label for="message-text" class="control-label">响应资源</label>
                <input class="form-control" id="btfdlzy" value="" name="btfdlzy" placeholder="请在此输入响应资源" size="1">
                
                </div>
            <div class="form-group">
                <label for="message-text" class="control-label">许可域名</label>
                  <textarea  type="text" class="form-control" rows="10" name="ymlist1" id="ymlist1" placeholder="请在此输入许可域名"></textarea>
                  </div>
                  	<div class="form-group">
                  	<label class="btn-block" for="web_site_status">允许空HTTP_REFERER请求开关</label>
	  <div class="col-xs-6">
          <div class="custom-control custom-switch">
            <input type="checkbox" class="custom-control-input" id="nullhttp">
            <label class="custom-control-label" for="nullhttp"></label>
          </div>
                 
	  <label class="btn-block" for="web_site_status">防盗链开关</label>
	  <div class="col-xs-6">
          <div class="custom-control custom-switch">
            <input type="checkbox" class="custom-control-input" id="fdlkg" for="fdlkg"></label>
            <label class="custom-control-label" for="fdlkg"></label>
          </div>
          
              </div>
              </div>
              
                  <div class="input-group-btn"><button class="btn btn-default btn btn-primary form-control" type="button" onclick="fdlkg()"><label><i class="mdi mdi-checkbox-marked-circle-outline"></i></label>保存</button></div>

          
              </div>
              <small class="help-block"><strong><code><li>【URL后缀】一般填写文件后缀,每个文件后缀使用","分隔,如: png,jpg</li><li>【许可域名】允许作为来路的域名，每行一个域名,如: www.bt.cn</li><li>【响应资源】可设置404/403等状态码，也可以设置一个有效资源，如：/security.png</li><li>【允许空HTTP_REFERER请求】是否允许浏览器直接访问，若您的网站访问异常，可尝试开启此功能</li>
    </code></strong></small>
                
              </div>
      </div>
<script type="text/javascript">
getfdl();


$("#fdlkg").on('change',function(){
    var jg=$(this).prop('checked');
    if(jg==false)$("#nullhttp").prop("checked",false);
})


function getfdl(){
msloading('正在获取数据中，请稍后...');  // 加载显示
let data = {};
data["gn"]="getfdl";
$.post('./ajax.php', data, function (date) {
var json= JSON.parse(date);    
var xkzym=json['domains'].split(',');
xkzym=xkzym.join('\n');

$("#btfdlurl").val(json.fix);
$("#btfdlzy").val(json.return_rule);
$("#ymlist1").val(xkzym);

if(json.http_status==true)json.http_status=true;
else json.http_status=false;
if(json.status==true){json.status=true;}
else json.status=false;
$("#nullhttp").prop("checked",json.http_status);
$("#fdlkg").prop("checked",json.status);

msloadingde();  // 隐藏
})

}



function fdlkg()
{
    msloading('正在处理中，请稍后...');  // 加载显示
    
    var fix=$("#btfdlurl").val();
    var zy=$("#btfdlzy").val();
    var doma=$.trim($("#ymlist1").val());
    doma=(doma.split('\n')).join(',');
    
    let data = {};
    data["gn"] = "fdlkg";
    data['fix'] = fix;
    data['domains'] = doma;
    data['return_rule'] = zy;
    data['http_status'] = $("#nullhttp").prop("checked");
    data['status'] = $("#fdlkg").prop("checked");
    $.post('./ajax.php', data, function (date) {
    var jsoe= JSON.parse(date);    
    var qk= jsoe.code
    if(qk == "设置成功")
    {
        msalert(1,'设置成功！',2000);
    }
    else
    {
        msalert(4, qk,2000);
    }
        msloadingde();
    })
    
}
</script>

<?php }

elseif($set == "fzjh")
{
 ?>
 <div class="col-sm-6">
      <div class="card">
        <div class="card-header">
          <h4>负载均衡设置</br>
</h4>
          <ul class="card-actions">
          </ul>
        </div>
        <div class="card-body">
            <div class="form-group">
                <label for="message-text" class="control-label">负载均衡</label>
                 <textarea  type="text" class="form-control" rows="10" name="ymlist1" id="ymlist1" placeholder="请输入要负载的网址,每个网址用,隔开"><?php
	                include("./class.php");
	                    $cert=$DB->get_row_prepare("SELECT * FROM MN_bt WHERE btdh=? limit 1", [$ssbt]);
	                $btipe=($cert['ptl']=='true'?'https':'http').'://'.$cert['btip'].':'.$cert['btdk'];
	                $btkeye=$cert['btmy'];
	                $api = new bt_api($btipe,$btkeye);
	                $name = $yhc['sqldz'];
	                $r_data = $api->Getnginx($name);
	              //  echo($r_data['data']);
                    $pattern = '/#######\s*(.*?)\s*########/s';
                    if (preg_match($pattern, $r_data['data'], $matches)) 
                    {
                        $content = trim($matches[1]);
                        echo $content;
                    }
                    else
                    {
                        echo "未配置负载均衡器";
                    }
                  ?></textarea>
            </div>

          
              </div>
              </div>

          
              </div>
 <?php
}
elseif($set == "rzfx")
{
    include_once('./class.php');
    $cert=$DB->get_row_prepare("SELECT * FROM MN_bt WHERE btdh=? limit 1", [$ssbt]);
    $btipe=($cert['ptl']=='true'?'https':'http').'://'.$cert['btip'].':'.$cert['btdk'];
    $btkeye=$cert['btmy'];
    $api = new bt_api($btipe,$btkeye);
    $abc=$api->getlog($yhc['sqldz']);
// Extract and print the access logs
$data = $abc['msg'];
$logArray = explode("\n", $data);

?>
<div class="border border-primary rounded-top">
<div class="bg-primary border border-gray"><h3 class="ml-2">nginx日志</h3></div>
<div class="bg-white px-2">
    
  <div class="form-group">
	  <label for="web_site_logo">nginx访问日志📢</label>
	
	    <textarea name="wzgg" rows="20" id="wzgg" class="form-control" placeholder="nginx访问日志"><?php 
        foreach($logArray as $nginx_list)
        {
            $pattern = '/"(.*?)".*\[(.*?)\]/';

            preg_match($pattern, $nginx_list, $matches);

            // 提取匹配到的 IP 地址
            $ip = $matches[0];

           // echo $ip;  // 输出 IP 地址
            //echo "\n";
            echo $nginx_list;
            echo "\n";
        }
	  ?>
	  </textarea>
	</div>
</div>
</div>
</div>
<?php
}
elseif($set=='xgpass'){?>
<div class="card">
<div class="card-body">
<h3 class="panel-title">修改密码</h3>
<hr/>
	<div class="form-group">
	  <label for="message-text" class="control-label">新的FTP密码</label>
	  <input type="text" name="webmm" id="webmm" placeholder="不修改请留空" class="form-control" required/>
	</div><br/>
	<div class="form-group">
	  <label for="message-text" class="control-label">新的数据库密码</label>
	  <input type="text" name="sqlmm" id="sqlmm" placeholder="不修改请留空" class="form-control" required/>
	</div><br/>
          <button class="btn btn-primary form-control" type="button" onclick="xgmm()"><i class="mdi mdi-checkbox-marked-circle-outline"></i>确认修改</button>
<small class="help-block"><strong><code>注意：FTP密码也是你的控制面板登陆密码，FTP密码修改后需重新登录控制面板</code></strong></small>
</div>
</div>
<script type="text/javascript">
function xgmm() {

msloading('正在处理中，请稍后...');  // 加载显示
let data = {};
data["gn"]="xgpass";
data["ftp"]=webmm.value;
data["sql"]=sqlmm.value;
$.post('./ajax.php', data, function (date) {
var jsoe= JSON.parse(date);    
var qk= jsoe.code

if(qk=='修改成功'){
if(webmm.value!=''){
msalert(1,'修改成功！将在两秒后跳转登录！',2000);
setTimeout(function(){
window.location.href="./set.php?gn=xgpass";
},2000);
}else{
msalert(1,'修改成功！',2000);
}
msloadingde();  // 隐藏
}else{
msalert(4, qk,2000);
msloadingde();  // 隐藏
}                        
})
}
</script>

<?php }elseif($set=='yxml'){
$cert=$DB->get_row_prepare("SELECT * FROM MN_bt WHERE btdh=? limit 1", [$ssbt]);
?>
<!--对话框-->
<script type="text/javascript" src="<?=mnbt_asset_url('js/jquery-confirm/jquery-confirm.min.js')?>"></script>
<script type="text/javascript" src="<?=mnbt_asset_url('js/bootstrap-table/bootstrap-table.min.js')?>"></script>
<script type="text/javascript" src="<?=mnbt_asset_url('js/bootstrap-table/locale/bootstrap-table-zh-CN.min.js')?>"></script>
<div class="col-sm-6">
      <div class="card">
        <div class="card-header">
          <h4>设置运行目录</br>
</h4>
          <ul class="card-actions">
          </ul>
        </div>
        <div class="card-body">
        
    <?php 
	include("./class.php");
	$btipe=($cert['ptl']=='true'?'https':'http').'://'.$cert['btip'].':'.$cert['btdk'];
	$btkeye=$cert['btmy'];
	if($cert['btos']=='1'){
	$os_xt=$conf['hxi'].'/';
	}else{
	$os_xt=$conf['hxo'].'/';
	}
	$api = new bt_api($btipe,$btkeye);
	$r_data = $api->yxmlrhq($yhc['btid'],$os_xt.$yhc['sqldz']);
	?>
<div class="form-group">
                <select class="form-control" id="btdh" name="btdh" size="1">
                <?php
                foreach (($r_data['runPath']['dirs'] ?? []) as $value) {
                    $aduajvff=$value==$r_data['runPath']['runPath']?'selected':'';
                  echo '
                  <option value="'.$value.'" '.$aduajvff.'>'.$value.'</option>
                  ';
                  }?>
                </select>
                </div>
            <div class="input-group">
                  <small class="help-block"><strong><li>部分程序需要指定二级目录作为运行目录，如ThinkPHP5，Laravel</li>
<li>选择您的运行目录，点保存即可</li></strong></small>
                  </div>
                  <div class="input-group-btn"><button class="btn btn-default btn btn-primary form-control" type="button" onclick="xg()"><label><i class="mdi mdi-checkbox-marked-circle-outline"></i></label>保存</button></div>
                </div>
              </div>
      </div>
<script type="text/javascript">
function xg(ms='') {
msloading('正在处理中，请稍后...');  // 加载显示
if(ms!='1'){
var data = {};
data["gn"]="hqzmlls";
$.post('./ajax.php', data, function (date) {
msloadingde();
if(date!='false'){
msalerts(3,'提示','当前运行目录下绑定有'+date+'域名的子目录，推荐您去[域名修改]处将这些域名的子目录修改为/，如果继续切换运行目录则会删除这些域名绑定！是否确认删除！','xg("1")','',30000);
}else{
xg('1');
}
});
return;
}
var ym=btdh.value;
data = {};
data["gn"]="setyxml";
data["wb"]=ym;
$.post('./ajax.php', data, function (date) {
var jsoe= JSON.parse(date);    
var qk= jsoe.code;

if(qk=='设置成功'){
msalert(1,'设置成功！',2000);
msloadingde();  // 隐藏
}else{
msalert(4,qk,2000);
msloadingde();  // 隐藏
}
})
}
</script>

<?php }elseif($set=='ssl'){
$cert=$DB->get_row_prepare("SELECT * FROM MN_bt WHERE btdh=? limit 1", [$ssbt]);
?>

      <div class="card">
        <header class="card-header"><div class="card-title">配置SSL</div></header>
        <div class="card-body">
          
          <ul class="nav nav-tabs nav-fill">
            <li class="nav-item">
              <a class="nav-link active" data-toggle="tab" href="#dqzs" aria-selected="true">当前证书</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" data-toggle="tab" href="#lets" aria-selected="false">Let's Encrypt证书</a>
            </li>
          </ul>
          <div class="tab-content">
            <div class="tab-pane fade show active" id="dqzs" role="tabpanel">
                <div class="bg-success row">
                    <div class="col">
                        <span><b>认证域名：</b><span id="rzurl">未知</span></span><br/>
                            <span><b>强制HTTPS：</b>
                        <div class="custom-switch wqbr">
            <input type="checkbox" class="custom-control-input" id="qzhttps">
            <label class="custom-control-label" for="qzhttps"></label>
          </div></span>
                    </div>
                    <div class="col">
                        <span><b>证书品牌：</b><span id="zspp">未知</span></span><br/>
                        <span><b>到期时间：</b><span id="dadate">未知</span></span>
                        </div>
                </div>
                <br/>
             <div class="row">
              <div class="form-group col-md">
              <label for="ssl-key">密钥(KEY)</label>
              <textarea class="form-control" id="ssl-key" rows="15" placeholder="密钥(KEY)"></textarea>
            </div>
              
              <div class="form-group col-md">
              <label for="ssl-pem">证书(PEM格式)</label>
              <textarea class="form-control" id="ssl-pem" rows="15" placeholder="证书(PEM格式)"></textarea>
            </div>
            </div>
        <div class="input-group-btn row" id="anncz">
            <button class="btn btn-primary form-control" type="button" onclick="setssl()"><label><i class="mdi mdi-shield-plus"></i></label>保存并启用证书</button>
            </div>
            <small>粘贴您的*.key以及*.pem内容，然后保存即可<a href="http://www.bt.cn/bbs/thread-704-1-1.html" target="_blank">[帮助]。</a><br/>
如果浏览器提示证书链不完整,请检查是否正确拼接PEM证书<br/>
PEM格式证书 = 域名证书.crt + 根证书(root_bundle).crt</small>
            </div>
            <div class="tab-pane fade" id="lets" role="tabpanel">
              
              <div class="form-group col-md">
              <label for="ssl-pem">请选择需要申请SSL的域名</label>
              
              <div class="border rounded-lg p-2">
          <div class="custom-control custom-checkbox">
            <input type="checkbox" class="border border-white custom-control-input" id="quanx">
            <label class="custom-control-label" for="quanx">全选</label>
          </div>
          <div style="display:inline-block;" id="url">
              <h2>正在加载域名列表中，请稍后...</h2>
          </div>
          </div>
          <br/>
        <div class="input-group-btn"><button class="btn btn btn-primary form-control" type="button" onclick="sq()"><label><i class="mdi mdi-shield-plus-outline"></i></label>申请</button></div>
        <br/>
              <small><strong class="text-danger">注意：请勿将SSL证书用于非法网站</strong><br/>
Let's Encrypt 证书申请和续签限制 <a href="https://letsencrypt.org/zh-cn/docs/rate-limits/" target="_blank">点击查看</a><br/>
Let's Encrypt因更换根证书，部分老旧设备访问时可能提示不可信。<br/>
申请之前，请确保域名已解析，如未解析会导致审核失败<br/>
Let's Encrypt免费证书，有效期3个月，支持多域名。默认会自动续签<br/>
若您的站点使用了CDN或301重定向会导致续签失败</small>
            </div>
            </div>
          </div>
          
        </div>
      </div>
    </div>
<script type="text/javascript">

getssl();
geturllist();
function getssl(){
msloading('正在获取证书配置中，请稍后...');  // 加载显示
let data = {};
var anns='';
data["gn"]="getssl";
$.post('./ajax.php', data, function (date) {
get_fh_json= JSON.parse(date);
if(get_fh_json.key==false){
get_fh_json['key']='';
}
if(get_fh_json.csr==false){
get_fh_json['csr']='';
}
$("#ssl-key").val(get_fh_json.key)
$("#ssl-pem").val(get_fh_json.csr)
$("#rzurl").html(get_fh_json['cert_data'].subject)
$("#zspp").html(get_fh_json['cert_data'].issuer)
var dqdate=new Date().getTime();
if(new Date(get_fh_json['cert_data'].notAfter)<new Date(dqdate)){
$("#dadate").html('<b class="text-danger">'+get_fh_json['cert_data'].notAfter+'</b>')
}else{
$("#dadate").html(get_fh_json['cert_data'].notAfter)
}
$("#qzhttps").prop('checked',get_fh_json.httpTohttps)
if(get_fh_json.status){
anns+='<button class="btn btn-primary form-control col-12 col-md" type="button" onclick="setssl()"><label><i class="mdi mdi-shield-plus"></i></label>保存</button>'
if(get_fh_json.type==1){
anns+='<button class="btn btn-primary form-control col-12 col-md" type="button" onclick="sq(1)"><label><i class="mdi mdi-circle-edit-outline"></i></label>续签证书</button>'
}
anns+='<button class="btn btn-primary form-control col-12 col-md" type="button" onclick="clossl()"><label><i class="mdi mdi-shield-remove"></i></label>关闭SSL</button>'
}else{
anns='<button class="btn btn-primary form-control" type="button" onclick="setssl()"><label><i class="mdi mdi-shield-plus"></i></label>保存并启用证书</button>';
}
$("#anncz").html(anns)
msloadingde();  // 隐藏
})
}

$("#qzhttps").change(function(){
if(!get_fh_json.status){msalert(3,'SSL未开启！'); $("#qzhttps").prop('checked',false); return;}
msloading('正在设置强制HTTPS，请稍后...');  // 加载显示
let data = {};
data["gn"]="httpsqz";
data["qk"]=this.checked;
$.post('./ajax.php', data, function (date) {
var json= JSON.parse(date);    

msalert(json.qk,json.code,4000);
msloadingde();  // 隐藏
getssl();
})
})

function clossl(){      //关闭证书
msloading('正在关闭SSL，请稍后...');  // 加载显示
let data = {};
data["gn"]="clossl";
$.post('./ajax.php', data, function (date) {
var json= JSON.parse(date);    

msalert(json.qk,json.code,4000);
msloadingde();  // 隐藏
getssl();
})
}

function setssl(){          //配置证书
let key=$("#ssl-key").val();
let pem=$("#ssl-pem").val();
if(key==false || pem==false){msalert(3,'密钥(KEY)和证书(PEM)均不能留空！'); return;}
msloading('正在设置并启用证书，请稍后...');  // 加载显示
let data = {};
data["gn"]="setssl";
data["key"]=key;
data["pem"]=pem;
$.post('./ajax.php', data, function (date) {
var json= JSON.parse(date);    

msalert(json.qk,json.code,4000);
msloadingde();  // 隐藏
getssl();
})
}



$("#quanx").change(function () {        //全选/取消全选
    $(".custom-checkbox input[type='checkbox']").prop('checked', $(this).prop("checked"));
});

function sq(qqlx=0) {
if(qqlx!=1){
var arr=[];
var xuanz=$(".custom-control input[type='checkbox']").each(function(){
    let xzzt=$(this).prop("checked");
    if(xzzt && this.id!='quanx'){
        arr.push($("label[for='"+this.id+"']").html());
    }
});
var titles='申请';
var settype=false;
if(get_fh_json.status){msalert(3,'当前SSL已开启！继续申请将会覆盖现有证书！如需继续申请请先关闭SSL！'); return;}
}else{
var arr=get_fh_json['cert_data'].dns;
var titles='续签';
var settype=true;
if(get_fh_json.httpTohttps){
msalert(3,'续签前请先关闭强制HTTPS！'); return;
}
}
if(arr.length<=0){msalert(3,'域名未选择！'); return;}
msloading('正在'+titles+'证书中，请稍后...');  // 加载显示
let data = {};
data["gn"]="sqssl";
data["list"]=arr;
data["type"]=settype;
$.post('./ajax.php', data, function (date) {
var json= JSON.parse(date);    

msalert(json.qk,json.code,4000);
if(json.qk==1){getssl();}
msloadingde();  // 隐藏
})
}
function geturllist(){
msloading('正在加载中，请稍后...','text-info','text-info','#url');  // 加载显示
let data = {};
data["gn"]="listurl";
$.post('./ajax.php', data, function (date) {
var list= JSON.parse(date);
var urls='';
var xhcs=1;
$.each(list['domains'],function(){
    urls+='<div class="custom-control custom-checkbox"><input type="checkbox" class="border border-white custom-control-input" id="url'+xhcs+'"><label class="custom-control-label" for="url'+xhcs+'">'+this.name+'</label></div>';
    xhcs++;
});
$('#url').html(urls);
msloadingde('#url');
})
}
</script>

<?php
}
elseif($set=="gzip")
{
$cert=$DB->get_row_prepare("SELECT * FROM MN_bt WHERE btdh=? limit 1", [$ssbt]);
$btipe=($cert['ptl']=='true'?'https':'http').'://'.$cert['btip'].':'.$cert['btdk'];
$btkeye=$cert['btmy'];
include("./class.php");
$api = new bt_api($btipe,$btkeye);
$gzip_data = $api->get_gzip_status($yhc['sqldz']);
$gzip = $gzip_data['data'] ?? [];
$gzip_on = ($gzip['status'] ?? false) ? true : false;
$gzip_level = $gzip['comp_level'] ?? '6';
$gzip_min_len = $gzip['min_length'] ?? '1k';
$gzip_types = $gzip['gzip_types'] ?? 'text/plain application/javascript application/x-javascript text/javascript text/css application/xml application/json image/jpeg image/gif image/png font/ttf font/otf image/svg+xml application/xml+rss text/x-js';
?>
<div class="col-sm-6">
    <div class="card">
        <div class="card-header">
            <h4>Gzip压缩配置</h4>
            <ul class="card-actions"></ul>
        </div>
        <div class="card-body">
            <div class="form-group">
                <label>Gzip压缩</label>
                <div class="switchery switchery-demo">
                    <input type="checkbox" class="js-switch" id="gzipSwitch" <?=$gzip_on?'checked':''?> data-color="#7367f0" />
                </div>
            </div>
            <div class="form-group">
                <label>压缩级别（1-9）</label>
                <select class="form-control" id="gzipLevel">
                    <?php for ($i = 1; $i <= 9; $i++): ?>
                    <option value="<?=$i?>" <?=$i==$gzip_level?'selected':''?>><?=$i?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="form-group">
                <label>最小压缩长度</label>
                <input class="form-control" id="gzipMinLen" value="<?=$gzip_min_len?>" placeholder="例如: 1k" />
            </div>
            <div class="form-group">
                <label>压缩类型（MIME类型，空格分隔）</label>
                <textarea class="form-control" id="gzipTypes" rows="4"><?=$gzip_types?></textarea>
            </div>
            <button class="btn btn-primary" onclick="saveGzip()"><label><i class="mdi mdi-checkbox-marked-circle-outline"></i></label>保存</button>
        </div>
    </div>
</div>
<script type="text/javascript">
function saveGzip() {
    msloading('正在保存...');
    var on = document.getElementById('gzipSwitch').checked;
    var data = {};
    data["gn"] = "setgzip";
    if (!on) {
        data["action"] = "off";
    } else {
        data["action"] = "on";
        data["level"] = document.getElementById('gzipLevel').value;
        data["min_len"] = document.getElementById('gzipMinLen').value;
        data["types"] = document.getElementById('gzipTypes').value;
    }
    $.post('./ajax.php', data, function(date) {
        var jsoe = JSON.parse(date);
        if (jsoe.code == '修改成功') {
            msalert(1, '修改成功！', 2000);
        } else {
            msalert(4, jsoe.code, 4000);
        }
        msloadingde();
    });
}
</script>
<?php
}
elseif($set=="cache")
{
$cert=$DB->get_row_prepare("SELECT * FROM MN_bt WHERE btdh=? limit 1", [$ssbt]);
$btipe=($cert['ptl']=='true'?'https':'http').'://'.$cert['btip'].':'.$cert['btdk'];
$btkeye=$cert['btmy'];
include("./class.php");
$api = new bt_api($btipe,$btkeye);
$cache_data = $api->get_static_cache($yhc['sqldz']);
$cache_rules = $cache_data['data'] ?? [];
?>
<script type="text/javascript" src="<?=mnbt_asset_url('js/jquery-confirm/jquery-confirm.min.js')?>"></script>
<div class="col-sm-8">
    <div class="card">
        <div class="card-header">
            <h4>缓存配置</h4>
            <ul class="card-actions"></ul>
        </div>
        <div class="card-body">
            <h5>当前缓存规则</h5>
            <table class="table table-bordered table-hover" id="cacheTable">
                <thead><tr><th>#</th><th>文件后缀</th><th>缓存时间</th><th>操作</th></tr></thead>
                <tbody>
                <?php if (empty($cache_rules)): ?>
                    <tr><td colspan="4" class="text-center text-muted">暂无缓存规则</td></tr>
                <?php else: $idx=0; foreach ($cache_rules as $rule): ?>
                    <?php $suffix = implode(',', $rule['suffix'] ?? []); $time_out = $rule['time_out'] ?? ''; ?>
                    <tr>
                        <td><?=$idx+1?></td>
                        <td><?=htmlspecialchars($suffix)?></td>
                        <td><?=htmlspecialchars($time_out)?></td>
                        <td>
                            <button class="btn btn-primary btn-xs" onclick='editCache(this, <?=json_encode($suffix, JSON_UNESCAPED_UNICODE)?>, <?=json_encode($time_out, JSON_UNESCAPED_UNICODE)?>)'>修改</button>
                            <button class="btn btn-danger btn-xs" onclick='delCache(this, <?=json_encode($suffix, JSON_UNESCAPED_UNICODE)?>)'>删除</button>
                        </td>
                    </tr>
                <?php $idx++; endforeach; endif; ?>
                </tbody>
            </table>
            <hr>
            <h5>新增缓存规则</h5>
            <div class="form-inline">
                <div class="form-group mr-2">
                    <label class="mr-1">文件后缀</label>
                    <input class="form-control cache-ext" placeholder="如: css,js" />
                </div>
                <div class="form-group mr-2">
                    <label class="mr-1">缓存时间</label>
                    <input class="form-control cache-time-value" type="number" min="1" value="30" style="width:90px" />
                    <select class="form-control ml-1 cache-time-unit">
                        <option value="s">秒</option>
                        <option value="m">分钟</option>
                        <option value="h">小时</option>
                        <option value="d" selected>天</option>
                    </select>
                </div>
                <button class="btn btn-primary" onclick="addCache(this)">添加</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="cacheModal" tabindex="-1" role="dialog" aria-labelledby="cacheModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title" id="cacheModalLabel">修改缓存规则</h6>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <input type="hidden" class="cache-old-suffix" />
                <div class="form-group">
                    <label>文件后缀</label>
                    <input class="form-control cache-edit-ext" placeholder="如: css,js" />
                </div>
                <div class="form-group">
                    <label>缓存时间</label>
                    <div class="form-inline">
                        <input class="form-control mr-1 cache-edit-time-value" type="number" min="1" style="width:100px" />
                        <select class="form-control cache-edit-time-unit">
                            <option value="s">秒</option>
                            <option value="m">分钟</option>
                            <option value="h">小时</option>
                            <option value="d">天</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
                <button type="button" class="btn btn-primary" onclick="saveCacheEdit(this)">保存</button>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
function addCache(btn) {
    var box = btn ? $(btn).closest('.card-body') : $('.cache-ext:visible').last().closest('.card-body');
    var suffix = $.trim(box.find('.cache-ext').val());
    var timeValue = $.trim(box.find('.cache-time-value').val());
    var timeUnit = box.find('.cache-time-unit').val();
    if (!suffix) { msalert(4, '请输入文件后缀', 2000); return; }
    if (!timeValue || parseInt(timeValue) < 1) { msalert(4, '请输入正确的缓存时间', 2000); return; }
    var time = timeValue + timeUnit;
    msloading('正在添加...');
    var data = {};
    data['gn'] = 'cacheadd';
    data['suffix'] = suffix;
    data['ext'] = suffix;
    data['time_out'] = time;
    data['time'] = time;
    $.post('./ajax.php', data, function(date) {
        var jsoe = JSON.parse(date);
        if (jsoe.code == '添加成功') {
            msalert(1, '添加成功！', 2000);
            setTimeout(function(){ location.reload(); }, 1500);
        } else {
            msalert(4, jsoe.code, 4000);
            msloadingde();
        }
    });
}
function editCache(btn, suffix, time) {
    if (typeof btn === 'string') {
        time = suffix;
        suffix = btn;
        btn = null;
    }
    var timeMatch = /^([0-9]+)([smhd])$/.exec(time || '30d');
    if (!timeMatch) timeMatch = ['30d', '30', 'd'];
    var modal = btn ? $(btn).closest('.col-sm-8').next('#cacheModal') : $('#cacheModal:visible').last();
    if (!modal.length) modal = $('#cacheModal').last();
    modal.find('.cache-old-suffix').val(suffix);
    modal.find('.cache-edit-ext').val(suffix);
    modal.find('.cache-edit-time-value').val(timeMatch[1]);
    modal.find('.cache-edit-time-unit').val(timeMatch[2]);
    modal.modal('show');
}
function saveCacheEdit(btn) {
    var modal = btn ? $(btn).closest('#cacheModal') : $('#cacheModal:visible').last();
    if (!modal.length) modal = $('#cacheModal').last();
    var oldSuffix = $.trim(modal.find('.cache-old-suffix').val());
    var suffix = $.trim(modal.find('.cache-edit-ext').val());
    var timeValue = $.trim(modal.find('.cache-edit-time-value').val());
    var timeUnit = modal.find('.cache-edit-time-unit').val();
    if (!suffix) { msalert(4, '请输入文件后缀', 2000, '#cacheModal'); return; }
    if (!timeValue || parseInt(timeValue) < 1) { msalert(4, '请输入正确的缓存时间', 2000, '#cacheModal'); return; }
    var time = timeValue + timeUnit;
    msloading('正在修改...');
    var data = {};
    data['gn'] = 'cacheedit';
    data['old_suffix'] = oldSuffix;
    data['suffix'] = suffix;
    data['ext'] = suffix;
    data['time_out'] = time;
    data['time'] = time;
    $.post('./ajax.php', data, function(date) {
        var jsoe = JSON.parse(date);
        if (jsoe.code == '修改成功') {
            msalert(1, '修改成功！', 2000, '#cacheModal');
            setTimeout(function(){ location.reload(); }, 1500);
        } else {
            msalert(4, jsoe.code, 4000, '#cacheModal');
            msloadingde();
        }
    });
}
function delCache(btn, suffix) {
    if (typeof suffix === 'undefined') {
        suffix = btn;
        btn = null;
    }
    suffix = $.trim(suffix || '');
    if (!suffix) { msalert(4, '参数错误', 2000); return; }
    $.confirm({
        title: '删除缓存规则',
        content: '确定删除后缀为 <b>' + suffix + '</b> 的缓存规则？',
        type: 'red',
        backgroundDismiss: true,
        buttons: {
            ok: {
                text: '确定删除',
                btnClass: 'btn-danger',
                action: function() {
                    msloading('正在删除...');
                    var data = {};
                    data['gn'] = 'cachedel';
                    data['suffix'] = suffix;
                    data['ext'] = suffix;
                    $.post('./ajax.php', data, function(date) {
                        var jsoe = JSON.parse(date);
                        if (jsoe.code == '删除成功') {
                            msalert(1, '删除成功！', 2000);
                            setTimeout(function(){ location.reload(); }, 1500);
                        } else {
                            msalert(4, jsoe.code, 4000);
                            msloadingde();
                        }
                    });
                }
            },
            cancel: {
                text: '取消'
            }
        }
    });
}
</script>
<?php
}
?>
