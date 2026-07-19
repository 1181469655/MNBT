<?php mnbt_admin_include('head'); ?>

<?php
$set=isset($_GET['gn'])?$_GET["gn"]:NULL;
if($set=='bt'){?>
<div class="container-fluid p-t-15">
  
  <div class="row">
    
    <div class="col-lg-12">
      <div class="card">
        <header class="card-header"><div class="card-title">宝塔添加</div></header>
        <div class="card-body">
  <div class="form-group">
	  <label for="web_site_icp"><b>宝塔IP</b></label>
	  <input type="text" name="btip" id="btip" class="form-control" placeholder="请在这填写宝塔的IP" required/>
	<small>您宝塔的IP(如果面板开启了域名访问则需要在此填写配置的域名)</small></div><br/>
  <div class="form-group">
	  <label for="web_site_icp"><b>宝塔端口</b></label>
	  <input type="text" name="btdk" id="btdk" class="form-control" placeholder="请在这填写宝塔的对接端口" required/>
	<small>默认为8888的那个数字，如果修改后就把修改后的填写在这</small></div><br/>
  <div class="form-group">
	  <label for="web_site_icp"><b>宝塔密钥</b></label>
	  <input type="text" name="btkey" id="btkey" class="form-control" placeholder="请在这填写宝塔的密钥(key)" required/>
	<small>填写：宝塔设置->APi接口->接口密钥</small></div><br/>
  <div class="form-group">
	  <label for="web_site_icp"><b>FTP地址</b></label>
        <input type="text" class="form-control" name="ftpdz" id="ftpdz" placeholder="请在这填写该宝塔的FTP地址"/>
	<small>显示在控制面板，通常情况下和宝塔IP一样，如果没特殊需求则不用改！</small></div><br/>
  <div class="form-group">
	  <label for="web_site_icp"><b>域名解析说明</b></label>
        <textarea type="text" class="form-control" name="urljx" id="urljx" placeholder="请在这填写该宝塔的域名解析地址"></textarea>
	<small>显示在控制面板，在用户绑定域名时查看的，如果不懂则请不要乱改！</small></div><br/>
<div class="form-group">
                <label for="web_site_logo"><b>宝塔编号(支持中文)</b></label>
                <div class="input-group">
                  <input type="text" class="form-control" name="btbh" id="btbh" placeholder="每台宝塔的编号"/>
                  <div class="input-group-btn"><button class="btn btn-default" type="button" onclick="szsc()">随机生成</button></div>
                </div>
              </div>
              </br>
<div class="form-group">
	  <label for="web_site_icp"><b>操作系统</b></label>
                <select class="form-control" id="btos" name="btos" size="1">
                  <option value="1">Linux</option>
                  <option value="2">Windows</option>
                </select>
            <small>Centos、Unbutu等都属于Linux操作系统</small>
            </div>
              </br>
	<div class="form-group">
	  <label class="btn-block" for="web_site_status"><b>安全访问(HTTPS)</b></label>
	  <div class="col-xs-6">
	  
	  <div class="custom-control custom-switch custom-info">
            <input type="checkbox" class="custom-control-input" name="xieyi" id="xieyi">
            <label class="custom-control-label" for="xieyi"></label>
          </div>
            <small>如果您的宝塔面板开启了[面板SSL](访问面板时为HTTPS请求协议)则需要把这个开关打开，如果您宝塔未开启[面板SSL]则请不要打开此开关！否则将无法与宝塔正常通信！</small>
	  
              </div>
              </div>
	<div class="form-group">
	  <label class="btn-block" for="web_site_status">宝塔接口开关</label>
	  <div class="col-xs-6">
	  
	  <div class="custom-control custom-switch custom-info">
            <input type="checkbox" class="custom-control-input" name="btkg" id="btkg" checked="true">
            <label class="custom-control-label" for="btkg"></label>
          </div>
	  
              </div>
              </div>
          <button class="btn btn-primary form-control" type="button" onclick="tjbt()"><label><i class="mdi mdi-checkbox-marked-circle-outline"></i></label>确认添加</button>

<h6 class="mt-3">注意：</h6>
<p>您所对接的宝塔必须安装至少一个 PHP 版本，系统会自动检测并使用最新版本！<br/>推荐添加的宝塔面板版本为7.9.0(包括7.9.0)以上！否则可能会出现错误！我们所使用的测试宝塔也是这个版本！
</p>
</div>

</div>
 <script type="text/javascript">

$("#btip").on("input propertychange change",function(){
    $("#urljx").val('请将域名A记录到 '+this.value);
    $("#ftpdz").val(this.value);
})

 function szsc() {
msloading('正在生成中，请稍后','text-info','text-info');  // 加载显示
var date = new Date();
var sj=Math.ceil(Math.random()*1000);
var sjs='mn'+sj+'f';
document.getElementById("btbh").value = sjs;
msalert(1,'生成成功',100)
msloadingde();  // 隐藏
}
  function tjbt() {
var ipe=btip.value;
var dke=btdk.value;
var keye=btkey.value;
var brs=btos.value;
var bhe=btbh.value;
var urlzdy=urljx.value;
var ftploc=ftpdz.value;
var xy=xieyi.checked;
var kge=btkg.checked;
if(ipe==false || dke==false || keye==false || bhe==false || urlzdy==false || ftploc==false){
msalert(3,'表单不能为空！',2000);
}else{
msloading('正在添加中，请稍后...');
let data = {};
data["gn"]="addbt";
data["ip"]=ipe;
data["dk"]=dke;
data["key"]=keye;
data["bh"]=bhe;
data["btos"]=brs;
data["urlla"]=urlzdy;
data["ftpdz"]=ftploc;
data["xieyi"]=xy;
data["kg"]=kge;
$.post('./ajax.php', data, function (date) {
var jsoe= JSON.parse(date);    
var qk= jsoe.code

if(qk=='添加成功'){
msalert(1,'添加成功！',2000);
msloadingde();
window.location.href="./list.php?gn=bt"
}else{
msalert(4,qk,2000);
msloadingde();
}                        
})
}}
  </script> 
<?php }elseif($set=='zj'){?>
<link href="<?=mnbt_asset_url('js/bootstrap-datepicker/bootstrap-datepicker3.min.css')?>" rel="stylesheet">
<link href="<?=mnbt_asset_url('js/bootstrap-datetimepicker/bootstrap-datetimepicker.min.css')?>" rel="stylesheet">
<link href="<?=mnbt_asset_url('js/bootstrap-clockpicker/bootstrap-clockpicker.min.css')?>" rel="stylesheet">

<div class="container-fluid p-t-15">
  
  <div class="row">
    
    <div class="col-lg-12">
      <div class="card">
        <header class="card-header"><div class="card-title">主机添加</div></header>
        <div class="card-body">
        
<div class="form-group">
	  <label for="web_site_icp">请选择宝塔</label>
                <select class="form-control" id="btdh" name="btdh" size="1">
                  <option value="-00-">点我选择宝塔</option>
                <?php
$bt_list=$DB->get_all_prepare("SELECT * FROM MN_bt order by id desc limit 100");
				foreach ($bt_list as $res) { ?>
                  <option value="<?=$res['btdh']?>"><?=$res['btdh']?></option>
                  <?php }?>
                </select>
            </div>
              </br>
<div class="form-group">
	  <label for="web_site_icp">产品类型</label>
                <select class="form-control" id="cplx" onchange="_sel(this.options[this.options.selectedIndex])" name="cplx" size="1">
                  <option value="2">主机</option>
                   <option value="1">CDN</option>
                </select>
            </div>
              </br>
<div class="form-group">
                <label for="web_site_logo">账号</label>
                <div class="input-group">
                  <input type="text" class="form-control" name="user" id="user" placeholder="FTP和SQL的账号"/>
 				  <div class="input-group-btn"><button class="btn btn-default" type="button" onclick="szsce(bh='user')">随机生成</button></div>
                </div>
              </div>
              </br>
<div class="form-group">
                <label for="web_site_logo">密码</label>
                <div class="input-group">
                  <input type="text" class="form-control" name="pass" id="pass" placeholder="FTP和SQL的密码"/>
 				  <div class="input-group-btn"><button class="btn btn-default" type="button" onclick="szsce(bh='pass')">随机生成</button></div>
                </div>
              </div>
              </br>
              <div id="irth">
  <div class="form-group">
	  <label for="web_site_icp">网页空间大小(MB)</label>
	  <input type="number" name="webkj" id="webkj" class="form-control" placeholder="网站储存的总文件的最大大小" required/>
	</div><br/>
  <div class="form-group">
	  <label for="web_site_icp">数据库空间大小(MB)</label>
	  <input type="number" name="sqlkj" id="sqlkj" class="form-control" placeholder="数据库储存的总数据的最大大小" required/>
	</div><br/>
	</div>
  <div class="form-group">
	  <label for="web_site_icp">最大流量(G/月)</label>
	  <input type="number" name="lls" id="lls" class="form-control" placeholder="请填写每月最大可用流量（数字）" required/>
	</div><br/>
  <div class="form-group">
	  <label for="web_site_icp">域名最大绑定数</label>
	  <input type="text" name="yms" id="yms" class="form-control" placeholder="最多能够绑定的域名数不限制请填0" required/>
	</div><br/>
	<div class="form-group">
	  <label class="btn-block" for="web_site_status">主机开关</label>
	  <div class="col-xs-6">
	  <div class="custom-control custom-switch custom-info">
            <input type="checkbox" class="custom-control-input" name="kg" id="kg" checked="true" checked>
            <label class="custom-control-label" for="kg"></label>
          </div>
              </div>
              </div><br/>
                    <div class="form-group">
                      <label for="recipient-name" class="control-label">到期时间(点击即可选择)：</label>
                    <input class="form-control js-datepicker m-b-10" type="text" name="datar" id="datar" placeholder="0000-00-00" value="" data-provide="datepicker" readonly="true" data-date-format="yyyy-mm-dd" style="background-color:#FFFFFF;"/>
                    </div>
          <button class="btn btn-primary form-control" type="button" onclick="tjzj()"><label><i class="mdi mdi-checkbox-marked-circle-outline"></i></label>确认添加</button>

<div class="panel-footer">
<span class="glyphicon glyphicon-info-sign"></span> 注意：<b>系统会自动检测并使用该节点的最新 PHP 版本，请确保宝塔面板已安装 PHP！</b><br/>CDN产品由于部分原因导致1台主机只能解析一个域名<br/>到期时间留空即为不启用到期时间（即该主机本系统不会对主机进行到期检测）<br/>最大流量：即该主机每个月最多可用多少流量！每月1日重置，<b>如果不开启流量监控则无法控制流量（开启方法：系统设置->使用教程->系统监控）</b>
</div>
</div>
</div>


</div>
<!--日期选择插件-->
<script src="<?=mnbt_asset_url('js/bootstrap-datepicker/bootstrap-datepicker.min.js')?>"></script>
<script src="<?=mnbt_asset_url('js/bootstrap-datepicker/locales/bootstrap-datepicker.zh-CN.min.js')?>"></script>
<script type="text/javascript" src="<?=mnbt_asset_url('js/main.min.js')?>"></script>
 <script type="text/javascript">
function _sel(val){
var vio=val.value;
if(vio=='1'){
document.getElementById("irth").style.display="none";
document.getElementById("yms").value="1";
document.getElementById("yms").readOnly="true";
}else{
document.getElementById("yms").value="";
document.getElementById("yms").readOnly="";
document.getElementById("irth").style.display="block";
}
}
 
function szsce(bho) {
msloading('正在生成中...');
var date = new Date();
var sj=Math.ceil(Math.random()*1000);
var so=Math.ceil(Math.random()*1000);
var sjs='f'+so+sj+'w';
document.getElementById(bho).value = sjs;
msalert(1,'生成成功！',2000);
msloadingde();
}
 function tjzj() {
var btbh=btdh.value;
var cpty=cplx.value;
var usere=user.value;
var passe=pass.value;
var datae=datar.value;
var lld=lls.value;
var webdx=webkj.value;
var sqldx=sqlkj.value;
var ymbds=yms.value;
var kge=kg.checked;
if(user=="" || pass=="" || btbh=="" || ymbds=="" || lld=="" || btbh=='-00-'){
msalert(3,'表单不能为空或您未选择宝塔！',2000);
}else{
msloading('正在添加中...');
let data = {};
data["gn"]="addzj";
data["user"]=usere;
data["pass"]=passe;
data["cplx"]=cpty;
data["btdh"]=btbh;
data["datae"]=datae;
data["webkj"]=webdx;
data["sqlkj"]=sqldx;
data["ll"]=lld;
data["ymbds"]=ymbds;
data["kg"]=kge;
$.post('./ajax.php', data, function (date) {
var jsoe= JSON.parse(date);    
var qk= jsoe.code

if(qk=='添加成功'){
msalert(1,'添加成功',2000);
msloadingde();
window.location.href="./list.php?gn=zj"
}else{
msalert(4,qk,2000);
msloadingde();
}                        
})
}}

  </script>

<?php }elseif($set=='cx'){
?>
<!--对话框-->
<script type="text/javascript" src="<?=mnbt_asset_url('js/jquery-confirm/jquery-confirm.min.js')?>"></script>
<script type="text/javascript" src="<?=mnbt_asset_url('js/bootstrap-table/bootstrap-table.min.js')?>"></script>
<script type="text/javascript" src="<?=mnbt_asset_url('js/bootstrap-table/locale/bootstrap-table-zh-CN.min.js')?>"></script>
<!--图片选择插件-->
<script src="<?=mnbt_asset_url('js/dropzone/min/basic.min.css')?>"></script>
<script src="<?=mnbt_asset_url('js/dropzone/min/dropzone.min.css')?>"></script>
<script src="<?=mnbt_asset_url('js/dropzone/min/dropzone.min.js')?>"></script>

<div class="container-fluid p-t-15">
  
  <div class="row">
    
    <div class="col-lg-12">
      <div class="card">
        <header class="card-header"><div class="card-title">程序添加</div></header>
        <div class="card-body">

          <form action="#!" method="post" id="example-from" onsubmit="return false;">
              
  <div class="form-group">
	  <label for="web_site_icp">程序名称</label>
	  <input type="text" name="cxname" id="cxname" class="form-control" placeholder="显示的此程序的名称" required/>
	</div><br/>
  <div class="form-group">
	  <label for="web_site_icp">程序介绍</label>
	  <textarea rows="5" name="cxjs" id="cxjs" class="form-control" placeholder="此程序的介绍"></textarea>
	</div><br/>
  <div class="form-group">
	  <label for="web_site_icp">程序价格</label>
	  <input type="number" name="cxrmb" id="cxrmb" class="form-control" min="0.00" step="0.01" max="1000" placeholder="此程序的价格 免费请填0" required/>
	</div><br/>
  <div class="form-group">
	  <label for="web_site_icp">所需网页空间</label>
	  <input type="number" name="cxwebkj" id="cxwebkj" class="form-control" placeholder="搭建此程序最低所需的web容量(填写数字，单位MB)" required/>
	</div><br/>
  <div class="form-group">
	  <label for="web_site_icp">所需数据库空间</label>
	  <input type="number" name="cxsqlkj" id="cxsqlkj" class="form-control" placeholder="搭建此程序最低所需的web容量(填写数字，单位MB)" required/>
	</div><br/>
  <div class="form-group">
	  <label for="web_site_icp">部署完成后的提示</label>
	  <textarea type="text" name="alerts" id="alerts" rows="6" class="form-control" placeholder="部署此程序成功后的弹窗提示(支持使用替代)"></textarea>
	  <small>部署此程序成功后给用户的弹窗提示(支持使用替代)</small>
	</div><br/>
  <div class="form-group">
	  <label for="web_site_icp">程序源码上传</label>
  <div class="custom-file">
      <input type="file" name="filecx" id="filecx" class="custom-file-input" required>
      <label class="custom-file-label" for="validatedCustomFile">选择文件...</label>
    </div></div><br/>
	
  <div class="form-group">
          
	  <label for="web_site_icp">程序展示图片上传</label>
	  
              <div id="example-uploads" class="m-b-10">
                <span class="btn btn-success fileinput-button">
                  <i class="mdi mdi-plus"></i>
                  <span>添加文件...</span>
                </span>
              </div>
              
              <!--重设上传主题-->
              <ul class="list-inline row" id="previews">
                <li id="template" class="file-row col-6 col-sm-3 col-md-2">
                  <!-- 它用作文件预览模板 -->
                  <div class="dropzone-preview">
                    <img data-dz-thumbnail />
                    <input type="hidden" class="pic_hidden_url" name="picurl[]" value="" />
                  </div>
                  <div class="dropzone-message">
                    <p class="dropzone-name" data-dz-name></p>
                    <strong class="error text-danger" data-dz-errormessage></strong>
                  </div>
                  <div class="dropzone-progress">
                    <p class="dropzone-size" data-dz-size></p>
                    <div class="progress m-b-10 active" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
                      <div class="progress-barr progress-bar-striped bg-success" style="width:0%;" data-dz-uploadprogress></div>
                    </div>
                  </div>
                  <div class="dropzone-btns">
                    <button style="display:none;" class="btn btn-primary btn-sm start">
                      <i class="mdi mdi-upload"></i>
                      <span>上传</span>
                    </button>
                    <button data-dz-remove class="btn btn-danger btn-sm delete">
                      <i class="mdi mdi-delete"></i>
                      <span>删除</span>
                    </button>
                    
                  </div>
                </li>
              </ul>
            </div>
            </form>
            
            <div class="tjnr1">
            <div class="a2">
           <h6 class="a1"> <b>需用户填写的表单</b></h6>
            </div>
           
                 <div class="btn-group a3 btn-round">
              
                <button type="button" class="btn btn-primary btn-xs dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                  添加一个表单 <span class="caret"></span>
                </button>
                  
                <ul class="dropdown-menu">
                  <li><a class="dropdown-item" href="#!" onclick="add_pt('input')">表单</a></li>
                  <li><a class="dropdown-item" href="#!" onclick="add_pt('dxk')">单选框</a></li>
                  <li><a class="dropdown-item" href="#!" onclick="add_pt('dxks')">多选框</a></li>
                  <li><a class="dropdown-item" href="#!" onclick="add_pt('urlxz')">域名选择</a></li>
                </ul>
                
              </div>
            <hr color="#8f8c8c"/>
          <form action="#!" method="post" id="siteinput">
            <div id="inputs"><!--添加内容存放div-->
            
            </div>
            </form>
           </div>
           <br/>
           <br/>
           
           
            <div class="tjnr1">
            <div class="a2">
           <h4 class="a1"> <b>安装时的操作</b></h4>
            </div>
           
                 <div class="btn-group a3 btn-round">
              
                <button type="button" class="btn btn-primary btn-xs dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                  添加一个操作 <span class="caret"></span>
                </button>
                  
                <ul class="dropdown-menu">
                  <li><a class="dropdown-item" href="#!" onclick="add_cz('xjwj')">新建文件</a></li>
                  <li><a class="dropdown-item" href="#!" onclick="add_cz('xjwjj')">新建文件夹</a></li>
                  <li><a class="dropdown-item" href="#!" onclick="add_cz('delwj')">删除文件</a></li>
                  <li><a class="dropdown-item" href="#!" onclick="add_cz('delwjj')">删除文件夹</a></li>
                  <li><a class="dropdown-item" href="#!" onclick="add_cz('setwj')">修改文件内容</a></li>
                  <li><a class="dropdown-item" href="#!" onclick="add_cz('drsql')">导入数据库文件</a></li>
                  <li><a class="dropdown-item" href="#!" onclick="add_cz('cxname')">重命名文件或文件夹</a></li>
                  <li><a class="dropdown-item" href="#!" onclick="add_cz('gettj')">GET提交</a></li>
                  <li><a class="dropdown-item" href="#!" onclick="add_cz('setyxml')">设置运行目录</a></li>
                  <li><a class="dropdown-item" href="#!" onclick="add_cz('setwjt')">配置伪静态</a></li>
                </ul>
                
              </div>
            <hr color="#8f8c8c"/>
          <form action="#!" method="post" id="siteform">
            <div id="tjdcznr"><!--添加内容存放div-->
             
            </div>
            </form>
           </div>
                
	<div class="form-group">
	  <label class="btn-block" for="web_site_status">是否上架</label>
	  <div class="custom-control custom-switch custom-info">
            <input type="checkbox" class="custom-control-input" name="kg" id="kg" checked="true" checked>
            <label class="custom-control-label" for="kg"></label>
          </div>
          <div id="example-uploads">
          <button class="btn btn-primary start form-control" type="submit" onclick=""><label><i class="mdi mdi-checkbox-marked-circle-outline"></i></label>确认添加</button>
          </div>
          
<span class="glyphicon glyphicon-info-sign"></span> 
<b>程序名称：</b>程序显示给客户时的名字<br/>
<b>程序介绍：</b>程序显示给客户的本程序介绍<br/>
<b>程序价格：</b>部署本程序需要支付的费用（单位：元）<br/>
<b>所需网页空间：</b>安装本程序最低所需要的网页空间（单位：MB）<br/>
<b>所需数据库空间：</b>安装本程序最低所需要的数据库空间（单位：MB）<br/>
<b>程序展示图片：</b>在程序列表中会以幻灯片的方式展示选择的图片<br/>
<b>需用户填写的表单：</b>在用户确定部署本程序之后系统将弹出一个弹出让用户填写您配置的表单，填写完成后将开始部署程序，系统会将[sf_变量名]替换为用户填写的内容，您可以在安装时的操作中引用这些替换符<br/>
<b>安装时的操作：</b>在用户确定部署本程序之后系统将会把程序源码上传到此用户的主机并解压然后根据您的填写的配置并根据步骤数字的从小到大依次进行操作（整个过程自动化！）<br/>
<b>新建文件：</b>在指定目录下新建一个空白文件并命名为您填写的那个<br/>
<b>新建文件夹：</b>在指定目录下新建一个空的文件夹并命名为您填写的那个<br/>
<b>删除文件：</b>在指定目录下删除一个指定的文件<br/>
<b>删除文件夹：</b>在指定目录下删除一个指定的文件夹（此操作将同时删除该文件内的文件及文件夹）<br/>
<b>导入数据库文件：</b>将指定目录下的指定文件（该文件必须为数据库文件）导入进数据库（此操作完成后并不会删除该数据库文件）<br/>
<b>修改文件内容：</b>修改指定目录下的一个指定文件的内容<br/>
<b>GET提交：</b>也就是访问url；目前只支持访问http不支持https！比如我要访问http://baidu.com/?a=1&b=2&c=[cn_user]那么要访问的域名那一栏则填写 baidu.com 提交的内容的那一栏填写a=1&b=2&c=[cn_user]<br/>


部署完成后的弹窗提示、修改文件内容、GET提交的数据均可用以下代替法：<br/><span color="#000"><b> 
[cn_host]代表用户的数据库连接地址；<br/>
[cn_port]代表用户的数据库端口；<br/>
[cn_user]代表用户的数据库账号；<br/>
[cn_pass]代表用户的数据库密码；<br/>
[cn_name]代表用户的数据库名；<br/>
[cn_date]代表安装时的时间；<br/>
[sf_变量名]代表用户填写的表单；比如变量名为a1则可以用[sf_a1]来替换为用户填写表单的内容<br/>

</b></span><br/>
<hr color="#000"/>
<span color="#FF0000"><b>注意：如果是根目录请填写 / ；<br/>文件名称和目录不得出现特殊字符比如 空格 {}：“|《》？【】；’、，。{}:"|<>?[];',.!@#$%^&*()_-+='"等；<br/>更多操作请期待后续更新</b></span>
</div>


</div>
<!--日期选择插件-->
<script src="<?=mnbt_asset_url('js/bootstrap-datepicker/bootstrap-datepicker.min.js')?>"></script>
<script src="<?=mnbt_asset_url('js/bootstrap-datepicker/locales/bootstrap-datepicker.zh-CN.min.js')?>"></script>
<script type="text/javascript" src="<?=mnbt_asset_url('js/main.min.js')?>"></script>
<script type="text/javascript" src="<?=mnbt_asset_url('js/addbs.js')?>?<?=$date?>"></script>
<script>
tpnamesz='0';
//文件选择监听
$(".custom-file-input").on("change",function(){
    if(this.files[0]==null){
        $(".custom-file-label").html('选择文件...');
    }else{
        $(".custom-file-label").html(this.files[0].name);
    }
});

$(function() {
    // 表单提交
    // 获取模板HTML并将其从文档中删除
    var previewNode = document.querySelector("#template");
    previewNode.id = "";
    var previewTemplate = previewNode.parentNode.innerHTML;
    previewNode.parentNode.removeChild(previewNode);
    
    Dropzone.autoDiscover = false;
    databeass=true;
    var myDropzone = new Dropzone(document.body, {
        url: "ajax.php",   // 文件提交地址
        method: "post",      // 也可用put
        acceptedFiles: ".png,.gif,.jpg,.jpeg", // 允许上传的文件格式
        thumbnailWidth: 165,  // 设置缩略图的缩略比
        thumbnailHeight: 110, // 就像 thumbnailWidth一样。如果为空, 将不能重置尺寸。
        parallelUploads: 24,  // 要并行处理的文件上载数量
        maxFilesize: 6,       // 最大上传文件大小(MB)
        
        autoQueue: false,                 // 确保在手动添加之前文件不会排队
        previewTemplate: previewTemplate, // 主题模板
        previewsContainer: "#previews",   // 上传图片的预览窗口
        clickable: ".fileinput-button",   // 定义应该用作单击触发器以选择文件的元素
        
        paramName: "imgfile", // 传到后台的参数名称
        uploadMultiple: true, //开启多文件上传
        maxFiles: null, //最大提交文件数量，为null时，则全部提交


        
        dictResponseError: '文件上传失败!',
        dictInvalidFileType: "文件类型只能是*.jpg,*.gif,*.png,*.jpeg",
        dictFallbackMessage: "浏览器不受支持",
        dictFileTooBig:"可添加的最大文件大小为{{maxFilesize}}Mb，当前文件大小为{{filesize}}Mb ",
        init: function () {
        var submitButton = $("#submit")
        myDropzone = this; // closure
        //为上传按钮添加点击事件
        submitButton.on("click", function (e) {
                e.preventDefault();
                e.stopPropagation();
                if (myDropzone.getAcceptedFiles().length !== 0) {
                    myDropzone.processQueue();
                }
        });
        this.on("sending", function (data, xhr, formData) {
            if(databeass){
                var t = $('#siteform').serializeArray();
                    $.each(t, function() {              //配置添加
                    formData.append(this.name,this.value);
                });
                var t = $('#siteinput').serializeArray();
                    $.each(t, function() {              //表单添加
                    formData.append(this.name,this.value);
                });
                
            var files = $('#filecx')[0].files        //获取源码
            formData.append("gn", 'cxtj');
            formData.append("cxname", $("#cxname").val());
            formData.append("cxjs", $("#cxjs").val());
            formData.append("cxrmb", $("#cxrmb").val());
            formData.append("cxwebkj", $("#cxwebkj").val());
            formData.append("cxsqlkj", $("#cxsqlkj").val());
            formData.append("alerts", $("#alerts").val());
            formData.append("filecx", files[0]);
            formData.append("kg", $("#kg").val());
        databeass=false;
            }
        });
        }

    });
    
    myDropzone.on("addedfile", function(file) {
        // 连接开始按钮
        file.previewElement.querySelector(".start").onclick = function() { myDropzone.enqueueFile(file); };
    });

    myDropzone.on("sending", function(file) {
        // 禁用“开始”按钮
        file.previewElement.querySelector(".start").setAttribute("disabled", "disabled");
    });
    
    myDropzone.on("success", function(file, response) {
        msloadingde();
        if(datamsalerts){
        var resData = JSON.parse(response);
        if (resData.code == '200') {
            msalert(1,'添加成功！',4000);
        } else {
    	myDropzone.removeFile(file);
    	msalert(4,resData.msg,4000);
    	msloadingde();
        }
        datamsalerts=false;
        }
    });
  
    myDropzone.on("error", function(file, errorMessage){
        //不接受该文件（非定义的可接受类型）或上传失败
    	myDropzone.removeFile(file);
    	msalert(4,'添加失败！',4000);
    	msloadingde();
    });

    // 设置所有传输的按钮，"add files" 按钮不需要设置，因为 `clickable` 已指定。
    $("#example-uploads .start").on('click', function() {
        var count= myDropzone.getAcceptedFiles().length;
                
        if( $("#cxname").val()=='' || $("#cxjs").val()=='' || $("#cxrmb").val()=='' || $("#cxwebkj").val()=='' || $("#cxsqlkj").val()=='' || $("#filecx").val()==null){
            msalert(3,'表单及源码不能为空！');
        }else{
            if(count>0){
                msloading('正在添加中，请稍后...');
                databeass=true;
                datamsalerts=true;
                myDropzone.enqueueFiles(myDropzone.getFilesWithStatus(Dropzone.ADDED));
            }else{
                msalert(3,'请选择展示图片！',2000);
            }
        }
    });
    
});
  </script>


<?php }elseif($set=='dr'){          //导入程序
?>
<!--对话框-->
<script type="text/javascript" src="<?=mnbt_asset_url('js/jquery-confirm/jquery-confirm.min.js')?>"></script>
<script type="text/javascript" src="<?=mnbt_asset_url('js/bootstrap-table/bootstrap-table.min.js')?>"></script>
<script type="text/javascript" src="<?=mnbt_asset_url('js/bootstrap-table/locale/bootstrap-table-zh-CN.min.js')?>"></script>

<div class="container-fluid p-t-15">
  
  <div class="row">
    
    <div class="col-lg-12">
      <div class="card">
        <header class="card-header"><div class="card-title">导入程序</div></header>
        <div class="card-body">

          <form action="#!" method="post" id="example-from" onsubmit="return false;">
              

  <div class="form-group">
	  <label for="web_site_icp">导入的程序包</label>
  <div class="custom-file">
      <input type="file" name="filecx" id="filecx" class="custom-file-input" required>
      <label class="custom-file-label" for="validatedCustomFile">选择文件...</label>
    <small>请选择打包导出后的程序包，这些程序包可以是其他同系统站点的，也可以是您自己站点的。它通常是一个zip压缩文件</small></div></div><br/>
            </form>
	<div class="form-group">
	  <label class="btn-block" for="web_site_status">是否上架这些程序</label>
	  <div class="custom-control custom-switch custom-info">
            <input type="checkbox" class="custom-control-input" name="kg" id="kg" checked="true" checked>
            <label class="custom-control-label" for="kg"></label>
          </div>
          <div id="example-uploads">
          <button class="btn btn-primary start form-control" type="submit" onclick="zxwjsc();"><label><i class="mdi mdi-checkbox-marked-circle-outline"></i></label>确认导入</button>
          </div>
          
<span class="glyphicon glyphicon-info-sign">
选择MNBT系统的一键部署程序导出文件点击[确认导入]即可！
这个文件是一个zip压缩文件
</span> 
</div>


</div>

<script>
tpnamesz='0';
//文件选择监听
$(".custom-file-input").on("change",function(){
    if(this.files[0]==null){
        $(".custom-file-label").html('选择文件...');
    }else{
        $(".custom-file-label").html(this.files[0].name);
    }
});


function pro(bolb,sizes){             //上传文件,此分片文件，上次返回的大小
var formdata = new FormData(); 
formdata.append("file",bolb);       //分片文件
formdata.append("gn","cxfiledru");
formdata.append("fesw",sizes);      //上次上传的大小
formdata.append("zsize",file.size);         //总大小
formdata.append("sxj",$("#kg").prop('checked'));         //是否上架
let data;
    $.ajax({
        type:"post",
        url:"./ajax.php", 
        data:formdata,
        cache: false,
        processData: false,
        contentType: false,
        async:true,
        success:function(res){
                uploadsfdyc++;
                var json= JSON.parse(res);
                if(json.error==1){
                    //上传完成结束
                    msalert(json.size,json.msg,4000);
                    msloadingde();
                }else{
                    //开始下一片上传
                    if(json.size>=file.size){
                        $(".custom-file-label").html('选择文件...');
                        msalert(4,'出现意外的错误！请重新导入！', 6000);
                        msloadingde();  // 隐藏
                    }else{
                        var filebolb=filefp(json.size);
                        pro(filebolb,json.size);
                    }
                }
            }
    });
}
    
function filefp(filesizey){      //文件分片(已上传大小)
    fileupload(filesizey,lengths);       //更新上传提示
    if(uploadsfdyc<=2){
    lengths=lengths;         //下次分片上传的文件大小
    }else{
    lengths=(upxcsize.toFixed(0))*1.5;         //下次分片上传的文件大小
    }
    var end=filesizey+lengths;               //获取文件下次结尾大小
    if(end>file.size){
        end=file.size;          //如果结尾大于文件大小那么下次结尾就是文件大小。
    }
    var bolb = file.slice(filesizey,end);           //文件分片
    return bolb;
}
    
function zxwjsc(){              //文件上传
var myfile = document.getElementById("filecx");
uploadsfdyc=0;       //已完成上传几次分片
file = myfile.files[0]; 
if(file==null){msalert(3,'请选择要上传的文件！',4000,'#exampleModal');return;}
//判断是否存在

lengths = 1024 * 1024 * 1;      //默认每片1MB大小
msloading('文件上传中，共'+sizedwhs(file.size)+'，已上传0.00MB，剩余'+sizedwhs(file.size)+'未上传，当前速度0.00MB/s，预计剩余时间获取中，文件上传进度0.00%');
fileuploadname=file.name;
ot = new Date().getTime();   //设置上传开始时间
var bolb=filefp(0);
pro(bolb,0);
}

function sizedwhs(size){            //文件单位换算
	var units = 'B';
	if(size/1024>1){
		size = size/1024;
		units = 'KB';
	}
	if(size/1024>1){
		size = size/1024;
		units = 'MB';
	}
	if(size/1024>1){
		size = size/1024;
		units = 'GB';
	}
	return size.toFixed(2)+units;
}

function fileupload(uploadsize,filesz){          //更新上传进度，uploadsize为已上传大小，filesz为当前分片上传大小，单位B
var nt = new Date().getTime();//获取当前时间
	var pertime = (nt-ot)/1000; //计算出上次调用该方法时到现在的时间差，单位为s
	ot = new Date().getTime(); //重新赋值时间，用于下次计算
	//上传速度计算
	var speed = filesz/pertime;
	var bspeed = speed;
	var units = 'B/s';//单位名称
	if(speed/1024>1){
		speed = speed/1024;
		units = 'KB/s';
	}
	if(speed/1024>1){
		speed = speed/1024;
		units = 'MB/s';
	}
	speed = speed.toFixed(2);
	upxcsize=bspeed;
    var resttime = ((file.size-uploadsize)/bspeed).toFixed(2);
	var bfb=Math.round(uploadsize / file.size * 10000) / 100 + "%";
	if(uploadsfdyc<1){
    var msg='文件上传中，共'+sizedwhs(file.size)+'，已上传'+sizedwhs(uploadsize)+'，剩余'+sizedwhs(file.size-uploadsize)+'未上传，当前速度获取中，预计剩余时间获取中，文件上传进度'+bfb;
	}else{
    var msg='文件上传中，共'+sizedwhs(file.size)+'，已上传'+sizedwhs(uploadsize)+'，剩余'+sizedwhs(file.size-uploadsize)+'未上传，当前速度'+speed+units+'，预计剩余时间'+resttime+'秒，文件上传进度'+bfb;
	}
    msloadingup(msg);
}


  </script>

<?php }  ?>
<!-- 域名添加已迁移至 domain_shop 插件，访问路径：plugin.php?p=domain_shop&page=product_add -->