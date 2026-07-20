<?php mnbt_admin_include('head'); ?>

<script type="text/javascript" src="<?=mnbt_asset_url('js/jquery-confirm/jquery-confirm.min.js')?>"></script>
<script type="text/javascript" src="<?=mnbt_asset_url('js/bootstrap-table/bootstrap-table.min.js')?>"></script>
<script type="text/javascript" src="<?=mnbt_asset_url('js/bootstrap-table/locale/bootstrap-table-zh-CN.min.js')?>"></script>

<script type="text/javascript" src="<?=mnbt_asset_url('js/md5.js')?>"></script> 

<?php
$set=isset($_GET['gn'])?$_GET["gn"]:NULL;
if($set=='bt'){
?>
<!--对话框-->
<script type="text/javascript" src="<?=mnbt_asset_url('js/jquery-confirm/jquery-confirm.min.js')?>"></script>
<script type="text/javascript" src="<?=mnbt_asset_url('js/bootstrap-table/bootstrap-table.min.js')?>"></script>
<script type="text/javascript" src="<?=mnbt_asset_url('js/bootstrap-table/locale/bootstrap-table-zh-CN.min.js')?>"></script>

<div class="container-fluid p-t-15">

<div class="modal fade" id="tanchuang" tabindex="-1" role="dialog" aria-labelledby="tanchuang" aria-hidden="true">
            <div class="modal-dialog" role="document">
              <div class="modal-content">
                <div class="modal-header">
                  <h6 class="modal-title" id="exampleModalChangeTitle">编辑宝塔</h6>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                  </button>
                </div>
                <div class="modal-body">
                  <form>
                   <input type="hidden" name="idr" id="idr">
                    <div class="form-group">
                      <label for="recipient-name" class="control-label">宝塔IP：</label>
                      <input type="text" class="form-control" name="recipientname" id="recipientname"/>
                    <small>宝塔IP修改后记得把下方的<b>[FTP地址]</b>和<b>[域名解析说明]</b>修改一下</small></div>
                    <div class="form-group">
                      <label for="recipient-name" class="control-label">宝塔端口：</label>
                      <input type="text" class="form-control" name="messagetext" id="messagetext"/>
                    </div>
                    <div class="form-group">
                      <label for="message-text" class="control-label">宝塔密钥：</label>
                      <textarea type="text" class="form-control" name="messagekey" id="messagekey"></textarea>
                    </div>
                    <div class="form-group">
                      <label for="message-text" class="control-label">FTP地址：</label>
                      <input type="text" class="form-control" name="ftpdz" id="ftpdz" placeholder="请在这填写该宝塔的FTP地址"/>
                    <small>显示在控制面板，通常情况下和宝塔IP一样，如果没特殊需求则不用改！</small></div>
                    <div class="form-group">
                      <label for="message-text" class="control-label">域名解析说明：</label>
                      <textarea type="text" class="form-control" name="urljx" id="urljx" placeholder="请在这填写该宝塔的域名解析地址"></textarea>
                    <small>显示在控制面板，在用户绑定域名时查看的，如果不懂则请不要乱改！</small></div>
                <div class="form-group">
	                <label for="web_site_icp">操作系统</label>
                <select class="form-control" id="btos" name="btos" size="1">
                  <option value="1">Linux</option>
                  <option value="2">Windows</option>
                </select>
            </div>
              </br>
	<div class="form-group">
	  <label class="btn-block" for="web_site_status">安全访问(HTTPS)</label>
	  <div class="col-xs-6">
	  
	  <div class="custom-control custom-switch custom-info">
            <input type="checkbox" class="custom-control-input" name="xieyi" id="xieyi">
            <label class="custom-control-label" for="xieyi"></label>
          </div>
              </div>
              </div>
	<div class="form-group">
	  <label class="btn-block" for="web_site_status">宝塔接口开关</label>
	  <div class="col-xs-6">
	  
	  <div class="custom-control custom-switch custom-info">
            <input type="checkbox" class="custom-control-input" name="btkg" id="btkg" checked="true" checked>
			<label class="custom-control-label" for="btkg"></label>
          </div>
          
              </div>
              </div>
                  </form>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
                  <button type="button" class="btn btn-primary" onclick="bj_bc()">确认保存</button>
                </div>
              </div>
            </div>
          </div>
          
  <div class="row">
    
    <div class="col-lg-12">
      <div class="card">
        <header class="card-header"><div class="card-title">宝塔列表</div></header>
        <div class="card-body">
          
          <div class="callout callout-info">
          <p class="small">
          <strong>操作图标详解</strong><br/>
        <a href="#!" class="btn btn-xs btn-default" title="编辑" data-toggle="tooltip"><i class="mdi mdi-pencil"></i></a>：编辑纪录
        <a href="#!" class="btn btn-xs btn-default" title="swapidc对接文档" data-toggle="tooltip"><i class="mdi mdi-buffer"></i></a>：SWAPIDC对接文档<br/>
        <a href="#!" class="btn btn-xs btn-default" title="删除" data-toggle="tooltip"><i class="mdi mdi-window-close"></i></a>：删除纪录
        <a href="#!" class="btn btn-xs btn-default" title="魔方对接文档" data-toggle="tooltip"><i class="mdi mdi-cube-outline"></i></a>：魔方对接文档
          </p></div>
          <div id="toolbar" class="toolbar-btn-action">
            <button id="btn_add" type="button" class="btn btn-primary m-r-5 js-create-tab" aria-hidden="true" data-title="添加宝塔" data-url="add.php?gn=bt">
              <span class="mdi mdi-plus"></span>新增宝塔
            </button>
            <button id="btn_delete" type="button" class="btn btn-danger" onclick="xzdelbt()">
              <span class="mdi mdi-window-close" aria-hidden="true"></span>删除选中
            </button>
          </div>
          <table id="tb_departments"></table>
          
        </div>
      </div>
    </div>
    
  </div>
  
</div>
<script type="text/javascript">

function hqxzh() {		//获取选中行
    var selRows = $("#tb_departments").bootstrapTable("getSelections");
    if(selRows.length == 0){
        msalert(3,"请至少选择一行",4000);
        return false;
    }

    var arr = new Array();
    $.each(selRows,function(i) {
    arr.push(this.id);
    });
    return arr;
}

function xzdelbt() {
if(!confirm('删除后不可恢复\n是否确认删除这些宝塔？\n删除的同时也会删除这些宝塔在本站开通的主机记录！并不会删除这些主机在您宝塔的数据，如需删除数据请前去主机列表进行删除！！！')){return;}
msloading('正在删除中，请稍后...')  // 加载显示
var arr=hqxzh();
if(arr==false){msloadingde();return;}
let data = {};
data["gn"]="btscxz";
data["idsz"]=arr;
$.post('./ajax.php', data, function (date) {
var jsoe= JSON.parse(date);    
var qk= jsoe.codr;
var qke= jsoe.code;

if(qk=='0'){
msloadingde();
msalert(1,'删除成功'+qke+'句',5000);
$("#tb_departments").bootstrapTable('refreshOptions',{pageNumber:1});		//刷新表格
}else{
msloadingde();
msalert(1,'删除成功'+qke+'句',5000);
msalert(4,'删除失败'+qk+'句',5000);
$("#tb_departments").bootstrapTable('refreshOptions',{pageNumber:1});		//刷新表格
}                        
})
}

function bj_bc() {
var ipe=recipientname.value;
var dke=messagetext.value;
var keye=messagekey.value;
var bs=btos.value;
var id=idr.value;
var urldz=urljx.value;
var ftploc=ftpdz.value;
var xy=xieyi.checked;
var kge=btkg.checked;
if(ipe=="" || dke=="" || keye==""){
msalert(3,'表单不能为空！',3000,'#tanchuang');
}else{
msloading('正在修改中...');
let data = {};
data["gn"]="xgjl";
data["id"]=id;
data["ip"]=ipe;
data["dk"]=dke;
data["key"]=keye;
data["btos"]=bs;
data["urlla"]=urldz;
data["ftpdz"]=ftploc;
data["xieyi"]=xy;
data["kg"]=kge;
$.post('./ajax.php', data, function (date) {
var jsoe= JSON.parse(date);    
var qk= jsoe.code

if(qk=='修改成功'){
$("#tb_departments").bootstrapTable('refreshOptions',{pageNumber:1});		//刷新表格
$('#tanchuang').modal('hide');		//关闭弹窗
msalert(1,'修改成功！',3000);
msloadingde();		//关闭加载条
}else{
msalert(4,qk,3000,'#tanchuang');
msloadingde();		//关闭加载条
}    
})
}}

$('#tb_departments').bootstrapTable({
    classes: 'table table-bordered table-hover table-striped',
    url: './ajax.php',
    method: 'post',		
    contentType : "application/x-www-form-urlencoded",  //请求格式
    dataType : 'json',        // 因为本示例中是跨域的调用,所以涉及到ajax都采用jsonp,
    uniqueId: 'id',
    idField: 'id',             // 每行的唯一标识字段
    toolbar: '#toolbar',       // 工具按钮容器
    //clickToSelect: true,     // 是否启用点击选中行
    showColumns: true,         // 是否显示所有的列
    showRefresh: true,         // 是否显示刷新按钮
    
    showToggle: true,        // 是否显示详细视图和列表视图的切换按钮(clickToSelect同时设置为true时点击会报错)
   
    pagination: true,                    // 是否显示分页
    sortOrder: "asc",                    // 排序方式
    sortName: "id",                     //默认排序字段
    queryParams: function(params) {
        var temp = {
            gn: 'listbt',         // 请求功能
            limit: params.limit,         // 每页数据量
            offset: params.offset,       // sql语句起始索引
            page: (params.offset / params.limit) + 1,
            sort: params.sort,           // 排序的列名
            sortOrder: params.order      // 排序方式'asc' 'desc'
        };
        return temp;
    },                                   // 传递参数
    sidePagination: "server",            // 分页方式：client客户端分页，server服务端分页
    pageNumber: 1,                       // 初始化加载第一页，默认第一页
    pageSize: 10,                        // 每页的记录行数
    pageList: [10, 25, 50, 100],         // 可供选择的每页的行数
    //search: true,                      // 是否显示表格搜索，此搜索是客户端搜索
    
    showExport: true,        // 是否显示导出按钮, 导出功能需要导出插件支持(tableexport.min.js)
    exportDataType: "basic", // 导出数据类型, 'basic':当前页, 'all':所有数据, 'selected':选中的数据
  
    columns: [{
        field: 'example',
        checkbox: true    // 是否显示复选框
    }, {
        field: 'id',
        title: 'ID',
        sortable: true    // 是否排序
    }, {
        field: 'btls',
        title: '宝塔状态',
        formatter:function(value,row){ 
			return '<a href="#!" class="text-info" title="检测宝塔通信状态" data-toggle="tooltip" onclick="ljztjc(this,'+row.id+');"><i class="mdi mdi-help-circle-outline"></i>点我检测</a>';
		}
    }, {
        field: 'btdh',
        title: '宝塔编号'
    }, {
        field: 'btip',
        title: '宝塔IP',
    }, {
        field: 'ktmy',
        title: '调用密钥',
        formatter:function(value,row){ 
			let dymy=md5(value+row.qmk);
			return '<div style="display: inline-block;"><span>**********</span><a href="#!" onclick="xymy(this,`'+dymy+'`)"><i class="mdi mdi-eye h6"></i></a></div>';
		}
    }, {
        field: 'date',
        title: '添加时间'
    }, {
        field: 'btdk',
        title: '宝塔端口'
    }, {
        field: 'btmy',
        title: '宝塔密钥',
        formatter:function(value){ 
			return '<div style="display: inline-block;"><span>**********</span><a href="#!" onclick="xymy(this,`'+value+'`)"><i class="mdi mdi-eye h6"></i></a></div>';
		}
    }, {
        field: 'qk',
        title: '宝塔情况',
        formatter:function(value){ 
			if (value == 'false') {
				value = '<span class="badge badge-danger"><b>关闭</b></span>';
			} else if(value == 'true') {
				value = '<span class="badge badge-success"><b>开启</b</span>';
			}else {
				value = row.pType ;
			}
			return value;
		}
    }, {
        field: 'operate',
        title: '宝塔操作',
        formatter: btnGroup,  // 自定义方法
        events: {
            'click .edit-btn': function (event, value, row, index) {
                editUser(row);
            },
            'click .del-btn': function (event, value, row, index) {
                delUser(row);
            },
            'click .dj-sw': function (event, value, row, index) {
                window.location.href='tutorial.php?gn=sw&sz='+row.id;
            },
            'click .dj-mf': function (event, value, row, index) {
                window.location.href='tutorial.php?gn=mr&sz='+row.id;
            }
        }
    }],
    onLoadSuccess: function(data){
        $("[data-toggle='tooltip']").tooltip();
    }
});
function xymy(data,value){
var valdat=data.parentNode.childNodes[0];
if(valdat.innerHTML=='**********'){
$(valdat).html(value);
$(data.childNodes[0]).removeClass('mdi-eye');
$(data.childNodes[0]).addClass('mdi-eye-off');
}else{
$(valdat).html('**********');
$(data.childNodes[0]).removeClass('mdi-eye-off');
$(data.childNodes[0]).addClass('mdi-eye');
}
}
// 操作按钮
function btnGroup ()
{
    let html =
        '<a href="#!" class="btn btn-xs btn-default edit-btn" title="编辑" data-toggle="tooltip"><i class="mdi mdi-pencil"></i></a>' +
        '<a href="#!" class="btn btn-xs btn-default dj-sw" title="swapidc对接文档" data-toggle="tooltip"><i class="mdi mdi-buffer"></i></a>' +
        '<a href="#!" class="btn btn-xs btn-default dj-mf" title="魔方对接文档" data-toggle="tooltip"><i class="mdi mdi-cube-outline"></i></a>'+
        '<a href="#!" class="btn btn-xs btn-default del-btn" title="删除" data-toggle="tooltip"><i class="mdi mdi-window-close"></i></a>';
    return html;
}

function ljztjc(lis,btid){          //宝塔连接状态检测
msloading('正在检测中...');
let data = {};
data["gn"]="btztjc";
data["btid"]=btid;        //宝塔ID
$.post('./ajax.php', data, function (date) {
var json= JSON.parse(date);
if(json.qk!=1){
$.alert({
        title: '提示',
        content: json.code,
        icon: 'mdi mdi-alert-decagram',
        animation: 'scale',
        closeAnimation: 'scale',
        type:json.titco,
        buttons: {
            okay: {
                text: '我知道了',
                btnClass: 'btn-blue'
            }
        }
    });
$(lis).html('<span class="mdi mdi-close-circle text-danger">通信失败</span>');
}else{
$(lis).html('<span class="mdi mdi-check-circle text-success">通信正常</span>');
}
msloadingde();
})
}

// 操作方法 - 编辑
function editUser(row)
{
document.getElementById("recipientname").value = row.btip;
document.getElementById("messagetext").value = row.btdk;
document.getElementById("idr").value = row.id;
$("#btos").val(row.btos);
document.getElementById("messagekey").innerHTML = row.btmy; 
if(row.als=='false'){$("#urljx").val('请将域名A记录到 '+row.btip);}else{$("#urljx").val(row.als);}
if(row.ftpdz=='false'){$("#ftpdz").val(row.btip);}else{$("#ftpdz").val(row.ftpdz);}
if(row.ptl=='false'){$("#xieyi").attr("checked",false);}else{$("#xieyi").attr("checked",true);}
if(row.qk=='false'){
document.getElementById("btkg").checked = "";
}else{
document.getElementById("btkg").checked = "false";
}
$('#tanchuang').modal();		//弹出弹窗
}
// 操作方法 - 删除
function delUser(row,s=true)
{
if(!confirm('删除后不可恢复\n是否确认删除该宝塔？\n删除的同时也会删除该宝塔在本站开通的主机记录！并不会删除这些主机在您宝塔的数据，如需删除数据请前去主机列表进行删除！！！')){return;}
let data = {};
data["gn"]="btsc";
data["id"]=row.id;
$.post('./ajax.php', data, function (date) {
var jsoe= JSON.parse(date);    
var qk= jsoe.code

if(qk=='删除成功'){
$("#tb_departments").bootstrapTable('refreshOptions',{pageNumber:1});		//刷新表格
msalert(1,'删除成功！',2000);
msloadingde();
}else{
msalert(4,qk,2000);
msloadingde();
}                        
})
}

</script>
</body>
</html>

<?php }elseif($set=='zj'){?>
<link href="<?=mnbt_asset_url('js/bootstrap-datepicker/bootstrap-datepicker3.min.css')?>" rel="stylesheet">
<link href="<?=mnbt_asset_url('js/bootstrap-datetimepicker/bootstrap-datetimepicker.min.css')?>" rel="stylesheet">
<link href="<?=mnbt_asset_url('js/bootstrap-clockpicker/bootstrap-clockpicker.min.css')?>" rel="stylesheet">
<div class="container-fluid p-t-15">

<div class="modal fade" id="tanchuang" tabindex="-1" role="dialog" aria-labelledby="tanchuang" aria-hidden="true">
            <div class="modal-dialog" role="document">
              <div class="modal-content">
                <div class="modal-header">
                  <h6 class="modal-title" id="exampleModalChangeTitle">编辑主机</h6>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                  </button>
                </div>
                <div class="modal-body">
                  <form>
        	     <input type="hidden" name="idr" id="idr">
        	     
                    <div class="form-group">
                      <label for="recipient-name" class="control-label">登陆账号(目前版本暂时无法修改)：</label>
                      <input type="text" class="form-control" name="zjuser" id="zjuser" readonly="true">
                    </div>
                    <div class="form-group">
                      <label for="recipient-name" class="control-label">SQL账号(目前版本暂时无法修改)：</label>
                      <input type="text" class="form-control" name="sqluser" id="sqluser" readonly="true">
                    </div>
                    
                    <div class="form-group">
                      <label for="recipient-name" class="control-label">登陆密码(也是FTP密码)：</label>
                      <input type="text" class="form-control" name="zjpass" id="zjpass">
                    </div>
                    <div class="form-group">
                      <label for="recipient-name" class="control-label">SQL密码：</label>
                      <input type="text" class="form-control" name="sqlpass" id="sqlpass">
                    </div>
              <div id="irth">
                    <div class="form-group">
                      <label for="recipient-name" class="control-label">网页空间(单位MB)：</label>
                      <input type="number" class="form-control" name="webkjt" id="webkjt">
                    </div>
                    <div class="form-group">
                      <label for="recipient-name" class="control-label">数据库空间(单位MB)：</label>
                      <input type="number" class="form-control" name="sqlkjt" id="sqlkjt">
                    </div>
                    </div>
                    <div class="form-group">
                      <label for="recipient-name" class="control-label">最大流量(G/月)：</label>
                      <input type="number" class="form-control" name="llmax" id="llmax">
                    </div>
                    <div class="form-group">
                      <label for="recipient-name" class="control-label">域名最大绑定数：</label>
                      <input type="text" class="form-control" name="urlbd" id="urlbd" placeholder="最多能够绑定的域名数不限制请填0">
                    </div>
                    <div class="form-group">
                      <label for="recipient-name" class="control-label">到期时间(点击即可编辑)：</label>
                    <input class="form-control js-datepicker m-b-10" type="text" name="datar" id="datar" placeholder="yyyy-mm-dd" value="" data-provide="datepicker" readonly="true" data-date-format="yyyy-mm-dd" style="background-color:#FFFFFF;"/>
                    </div>
	<div class="form-group">
	  <label class="btn-block" for="web_site_status">主机开关</label>
	  <div class="col-xs-6">
	  <div class="custom-control custom-switch custom-info">
            <input type="checkbox" class="custom-control-input" name="zjkg" id="zjkg" checked="true" checked>
			<label class="custom-control-label" for="zjkg"></label>
          </div>
          
              </div>
              </div>
                  </form>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
                  <button type="button" class="btn btn-primary" onclick="bj_bc()">确认保存</button>
                </div>
              </div>
            </div>
          </div>
          
  <div class="row">
    
    <div class="col-lg-12">
      <div class="card">
        <header class="card-header"><div class="card-title">主机列表</div></header>
        <div class="card-body">
          
          <div class="callout callout-info">
          <p class="small">
          <strong>操作图标详解</strong><br/>
        <a href="#!" class="btn btn-xs btn-default" title="编辑" data-toggle="tooltip"><i class="mdi mdi-pencil"></i></a>：编辑纪录
        <a href="#!" class="btn btn-xs btn-default" title="删除" data-toggle="tooltip"><i class="mdi mdi-window-close"></i></a>：删除主机<br/>
        <a href="#!" class="btn btn-xs btn-default" title="登陆控制面板" data-toggle="tooltip"><i class="mdi mdi-security-network"></i></a>：登陆控制面板
          </p></div>
          <div id="toolbar" class="toolbar-btn-action input-group">

              <div class="input-group-prepend">
                  <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="where_name" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" val="">选择搜索字段</button>
                  <div class="dropdown-menu">
                      <a class="dropdown-item" val="ssbt" href="#!">所属宝塔</a>
                      <a class="dropdown-item" val="sqldz" href="#!">网站名</a>
                      <a class="dropdown-item" val="user" href="#!">账号</a>
                  </div>

                  <div class="input-group-prepend">
                      <button class="btn btn-outline-secondary dropdown-toggle rounded-0" type="button" id="where_type" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" val="2">模糊搜索</button>
                      <div class="dropdown-menu">
                          <a class="dropdown-item" val="2" href="#!">模糊搜索</a>
                          <a class="dropdown-item" val="1" href="#!">精确搜索</a>
                      </div>
                  </div>

              </div>

              <input type="text" class="form-control" placeholder="请在此输入搜索内容" autocomplete="off" id="where_value">
              <div class="input-group-append">
                  <button class="btn btn-outline-secondary rounded-right qrssat" type="button"><i class="mdi mdi-cloud-search-outline"></i>搜索</button>
                  <button class="btn btn-outline-secondary rounded-right qrss" type="button"><i class="mdi mdi-backup-restore"></i>重置</button>
              </div>

            <button id="btn_add" type="button" class="ml-2 btn btn-primary m-r-5 js-create-tab" aria-hidden="true" data-title="添加主机" data-url="add.php?gn=zj">
              <span class="mdi mdi-plus"></span>新增主机
            </button>
            <button id="btn_delete" type="button" class="btn btn-danger" onclick="xzdelbt()">
              <span class="mdi mdi-window-close" aria-hidden="true"></span>删除选中
            </button>
          </div>
          <table id="tb_departments"></table>
          
        </div>
      </div>
    </div>
    
  </div>
  
</div>
<script src="<?=mnbt_asset_url('js/bootstrap-datepicker/bootstrap-datepicker.min.js')?>"></script>
<script src="<?=mnbt_asset_url('js/bootstrap-datepicker/locales/bootstrap-datepicker.zh-CN.min.js')?>"></script>
<script type="text/javascript" src="<?=mnbt_asset_url('js/main.min.js')?>"></script>
<script type="text/javascript">
    var where={"name":null,"type":null,"value":null};


    $(".dropdown-item").on("click",function(k,v){
        const $dr=$($(this).parent().parent()).children(":first")
        $dr.html($(this).html())
        $dr.attr('val',$(this).attr('val'));
    })

    $(".qrssat").on("click",function(){
        where.name=$("#where_name").attr('val');
        where.type=$("#where_type").attr('val');
        where.value=$("#where_value").val().trim();
        $("#tb_departments").bootstrapTable('refreshOptions',{pageNumber:1});		//刷新表格
    })

function hqxzh() {		//获取选中行
    var selRows = $("#tb_departments").bootstrapTable("getSelections");
    if(selRows.length == 0){
        return 'NOT_NONE';
    }

    var arr = new Array();
    $.each(selRows,function(i) {
    arr.push(this.id);
    });
    return arr;
}

function xzdelbt() {
if(!confirm('删除后不可恢复\n是否确认删除这些主机？\n如果这些主机所属宝塔无法正常访问将会删除失败！\n如果需要删除宝塔下的所有主机请前去宝塔列表删除宝塔即可！')){return;}
msloading('正在删除中，请稍后...')  // 加载显示
var arr=hqxzh();
if(arr=='NOT_NONE'){msalert(3,'请至少选择一行',3000);msloadingde();return;}
let data = {};
data["gn"]="zjscxz";
data["idsz"]=arr;
$.post('./ajax.php', data, function (date) {
var jsoe= JSON.parse(date);    
var qk= jsoe.codr;
var qke= jsoe.code;

if(qk=='0'){
msloadingde();
msalert(1,'删除成功'+qke+'句',5000);
$("#tb_departments").bootstrapTable('refreshOptions',{pageNumber:1});		//刷新表格
}else{
msloadingde();
msalert(1,'删除成功'+qke+'句',5000);
msalert(4,'删除失败'+qk+'句',5000);
$("#tb_departments").bootstrapTable('refreshOptions',{pageNumber:1});		//刷新表格
}                        
})
}

function bj_bc() {
var user=zjuser.value;
var pass=zjpass.value;
var sqlusere=sqluser.value;
var sqlpasse=sqlpass.value;
var datare=datar.value;
var id=idr.value;
var urlz=urlbd.value;
var webkjr=webkjt.value;
var sqlkjr=sqlkjt.value;
var lldr=llmax.value;
var kg=zjkg.checked;
if(user=="" || pass=="" || sqluser=="" || sqlpass=="" || datar==""){
msalert(3,'表单不能为空！',3000,'#tanchuang');
}else{
msloading('正在修改中...');
let data = {};
data["gn"]="zjxgjl";
data["id"]=id;
data["user"]=user;
data["pass"]=pass;
data["sqluser"]=sqlusere;
data["sqlpass"]=sqlpasse;
data["datar"]=datare;
data["ymbds"]=urlz;
data["webkj"]=webkjr;
data["sqlkj"]=sqlkjr;
data["llmax"]=lldr;
data["kg"]=kg;
$.post('./ajax.php', data, function (date) {
var jsoe= JSON.parse(date);    
var qk= jsoe.code

if(qk=='修改成功'){
$("#tb_departments").bootstrapTable('refreshOptions',{pageNumber:1});		//刷新表格
$('#tanchuang').modal('hide');		//关闭弹窗
msalert(1,'修改成功！',3000);
msloadingde();		//关闭加载条
}else{
msalert(4,qk,3000,'#tanchuang');
msloadingde();		//关闭加载条

}    
})
}}

$('#tb_departments').bootstrapTable({
    classes: 'table table-bordered table-hover table-striped',
    url: './ajax.php',
    method: 'post',		
    contentType : "application/x-www-form-urlencoded",  //请求格式
    dataType : 'json',        // 因为本示例中是跨域的调用,所以涉及到ajax都采用jsonp,
    uniqueId: 'id',
    idField: 'id',             // 每行的唯一标识字段
    toolbar: '#toolbar',       // 工具按钮容器
    //clickToSelect: true,     // 是否启用点击选中行
    showColumns: true,         // 是否显示所有的列
    showRefresh: true,         // 是否显示刷新按钮
    
    showToggle: true,        // 是否显示详细视图和列表视图的切换按钮(clickToSelect同时设置为true时点击会报错)
   
    pagination: true,                    // 是否显示分页
    sortOrder: "desc",                    // 排序方式
    sortName: "id",                     //默认排序字段
    queryParams: function(params) {
        var temp = {
            gn: 'listzj',         // 请求功能
            where: JSON.stringify(where),
            limit: params.limit,         // 每页数据量
            offset: params.offset,       // sql语句起始索引
            page: (params.offset / params.limit) + 1,
            sort: params.sort,           // 排序的列名
            sortOrder: params.order      // 排序方式'asc' 'desc'
        };
        return temp;
    },                                   // 传递参数
    sidePagination: "server",            // 分页方式：client客户端分页，server服务端分页
    pageNumber: 1,                       // 初始化加载第一页，默认第一页
    pageSize: 10,                        // 每页的记录行数
    pageList: [10, 25, 50, 100],         // 可供选择的每页的行数
    //search: true,                      // 是否显示表格搜索，此搜索是客户端搜索
    
    showExport: true,        // 是否显示导出按钮, 导出功能需要导出插件支持(tableexport.min.js)
    exportDataType: "basic", // 导出数据类型, 'basic':当前页, 'all':所有数据, 'selected':选中的数据
  
    columns: [{
        field: 'example',
        checkbox: true    // 是否显示复选框
    }, {
        field: 'id',
        title: 'ID',
        sortable: true    // 是否排序
    }, {
        field: 'ssbt',
        title: '所属宝塔',
        sortable: true    // 是否排序
    }, {
        field: 'sqldz',
        title: '网站名'
    }, {
        field: 'userpass',
        title: '账号/密码',
        formatter:function(value,row){
			return row.user+'<br/>'+row.pass;
		}
    }, {
        field: 'sqluserpass',
        title: 'SQL账号/密码',
        formatter:function(value,row){
			return row.sqluser+'<br/>'+row.sqlpass;
		}
    }, {
        field: 'webkj',
        title: '网页空间',
        formatter:function(value,row){
        fanhuiqk='';
        var jsoe= JSON.parse(row.hxa);
        var dq=Number(jsoe.dq).toFixed(2);      //当前用量四舍五入保留两位小数
        if(dq>Number(jsoe.max)){
        var textcolor='text-danger';
        fanhuiqk+='<span class="badge badge-danger"><b>网页超出</b></span><div class="w-100" style="height:1px;"></div>';
        }else{
        var textcolor='text-success';
        }
			return '<span class="'+textcolor+'">'+dq+'/'+jsoe.max+'MB'+'</span>';
		}
    }, {
        field: 'sqlkj',
        title: '数据库空间',
        formatter:function(value,row){
        var jsoe= JSON.parse(row.hxb);
        var dq=Number(jsoe.dq).toFixed(2);      //当前用量四舍五入保留两位小数
        if(dq>Number(jsoe.max)){
        var textcolor='text-danger';
        fanhuiqk+='<span class="badge badge-danger"><b>数据库超出</b></span><div class="w-100" style="height:1px;"></div>';
        }else{
        var textcolor='text-success';
        }
			return '<span class="'+textcolor+'">'+dq+'/'+jsoe.max+'MB'+'</span>';;
		}
    }, {
        field: 'lls',
        title: '已使用/总流量',
        formatter:function(value,row){ 
        var jsoe= JSON.parse(row.llmax);
        var wdq=Number(jsoe.dq);      //当前用量
        var rdq=wdq / (1024*1024*1024);     //单位转换：B转GB
        var dq=rdq.toFixed(2);       //四舍五入保留两位小数
        if(dq>Number(jsoe.max)){
        var textcolor='text-danger';
        fanhuiqk+='<span class="badge badge-danger"><b>流量超出</b></span><div class="w-100" style="height:1px;"></div>';
        }else{
        var textcolor='text-success';
        }
			return '<span class="'+textcolor+'">'+dq+'/'+jsoe.max+'G'+'</span>';;
		}
    }, {
        field: 'data',
        title: '创建时间'
    }, {
        field: 'datae',
        title: '到期时间',
        formatter:function(value,row){ 
        var date=row.data;
        var dqdate=row.datae;
        if(dqdate!='0000-00-00'){
        
        var customTimesr = dqdate.replace("-", "/");
        var customTimed = new Date(Date.parse(customTimesr));
        var currentTime = new Date();

        if(currentTime>customTimed){
        textcolor='text-danger';
        fanhuiqk+='<span class="badge badge-danger"><b>主机到期</b></span><div class="w-100" style="height:1px;"></div>';
        }else{
        textcolor='text-success';
        }
        }else{
        textcolor='text-success';
        }
			return '<span class="'+textcolor+'">'+row.datae+'</span>';
		}
    }, {
        field: 'qk',
        title: '状态',
        formatter:function(value,row){
				if(value=='false'){
                    fanhuiqk+='<span class="badge badge-danger"><b>被关闭</b></span>';
				}
				
				if(fanhuiqk==''){
				    var sc='<span class="badge badge-success"><b>正常</b></span>';
				}else{
				    var sc=fanhuiqk;
				}
				fanhuiqk='';
        return sc;
		}
		
    }, {
        field: 'operate',
        title: '主机操作',
        formatter: btnGroup,  // 自定义方法
        events: {
            'click .edit-btn': function (event, value, row, index) {
                editUser(row);
            },
            'click .del-btn': function (event, value, row, index) {
                delUser(row);
            },
            'click .login-kzmb': function (event, value, row, index) {
            msloading('正在登陆中...');  // 加载显示
            let data = {};
            data["username"]=row.user;
            data["password"]=row.pass;
            //alert(codeq);
            $.post('../user/idcdl.php?gn=logine', data, function (date) {

            msalert(1,'登陆成功！页面即将自动跳转~',2000);
            setTimeout(function(){
            window.open("../user/");
            },1000)
            msloadingde();  // 隐藏
            })
            }
        }
    }],
    onLoadSuccess: function(data){
        $("[data-toggle='tooltip']").tooltip();
        try {
            if (Number(data.code) === 4) {
                msalert(4, data.msg);
                $('#tb_departments').bootstrapTable("removeAll");
                return;
            }
            if (Number(data.total) === 0) {
                $('#tb_departments').bootstrapTable("removeAll");
            }
        } catch (e) {
        }
    }
});

// 操作按钮
function btnGroup ()
{
    let html =
        '<a href="#!" class="btn btn-xs btn-default edit-btn" title="编辑" data-toggle="tooltip"><i class="mdi mdi-pencil"></i></a>' +
        '<a href="#!" class="btn btn-xs btn-default login-kzmb" title="登陆控制面板" data-toggle="tooltip"><i class="mdi mdi-security-network"></i></a>' +
        '<a href="#!" class="btn btn-xs btn-default del-btn" title="删除" data-toggle="tooltip"><i class="mdi mdi-window-close"></i></a>';
    return html;
}

// 操作方法 - 编辑
function editUser(row)
{

document.getElementById("zjuser").value = row.user;
document.getElementById("zjpass").value = row.pass;
document.getElementById("sqluser").value = row.sqluser;
document.getElementById("sqlpass").value = row.sqlpass;
document.getElementById("webkjt").value = JSON.parse(row.hxa).max;
document.getElementById("sqlkjt").value = JSON.parse(row.hxb).max;
document.getElementById("llmax").value = JSON.parse(row.llmax).max;
if(row.ymbds=='0' || row.ymbds==''){ var urlbde='无限制'; }else{ var urlbde=row.ymbds; }
document.getElementById("urlbd").value = urlbde;
document.getElementById("datar").value = row.datae;
document.getElementById("idr").value = row.id;
if(row.qk!='true'){
document.getElementById("zjkg").checked = "";
}else{
document.getElementById("zjkg").checked = "true";
}
document.getElementById("urlbd").readOnly="";
document.getElementById("irth").style.display="block";

$('#tanchuang').modal();		//弹出弹窗
}
// 操作方法 - 删除
function delUser(row)
{
if(!confirm('删除后不可恢复\n是否确认删除该主机？\n如果该主机所属宝塔无法正常访问将会删除失败！\n如果需要删除该宝塔的所有主机请前去宝塔列表删除宝塔即可！')){return;}
msloading('正在删除中，请稍后...');
let data = {};
data["gn"]="zjsc";
data["id"]=row.id;
$.post('./ajax.php', data, function (date) {
var jsoe= JSON.parse(date);    
var qk= jsoe.code

if(qk=='删除成功'){
$("#tb_departments").bootstrapTable('refreshOptions',{pageNumber:1});		//刷新表格
msalert(1,'删除成功！',2000);
msloadingde();
}else{
msalert(4,qk,2000);
msloadingde();
}                        
})
}

</script>
</body>
</html>


<?php }elseif($set=='cx'){?>
<script type="text/javascript" src="<?=mnbt_asset_url('js/addbs.js')?>?<?=$date?>"></script>
<div class="container-fluid p-t-15">

<div class="modal fade" id="tanchuang" tabindex="-1" role="dialog" aria-labelledby="tanchuang" aria-hidden="true">
            <div class="modal-dialog" role="document">
              <div class="modal-content">
                <div class="modal-header">
                  <h6 class="modal-title" id="exampleModalChangeTitle">编辑纪录</h6>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                  </button>
                </div>
                <div class="modal-body">
                <li>网页空间和数据库空间的单位都是MB，程序价格的单位是元</li>
               <form action="ajax.php" method="post" role="form">
        	     <input type="hidden" name="id" id="idr"/>
        	     <input type="hidden" name="gn" value="cxxgjl"/>
                    <div class="form-group">
                      <label for="recipient-name" class="control-label">程序名称：</label>
                      <input type="text" class="form-control" name="cxname" id="cxname">
                    </div>
                    <div class="form-group">
                      <label for="recipient-name" class="control-label">程序介绍：</label>
	                    <textarea rows="5" name="cxjc" id="cxjc" class="form-control" placeholder="此程序的介绍"></textarea>
                    </div>
                    <div class="form-group">
                      <label for="recipient-name" class="control-label">最低所需网页空间：</label>
                      <input type="number" class="form-control" name="webkj" id="webkj">
                    </div>
                    <div class="form-group">
                      <label for="recipient-name" class="control-label">最低所需数据库空间：</label>
                      <input type="number" class="form-control" name="sqlkj" id="sqlkj">
                    </div>
                    <div class="form-group">
                      <label for="recipient-name" class="control-label">部署完成后的提示：</label>
                      <textarea type="text" class="form-control" name="alerts" id="alerts" rows="6" placeholder="部署此程序成功后的弹窗提示(支持使用替代)"></textarea>
                    </div>
                    <div class="form-group">
                      <label for="recipient-name" class="control-label">程序价格：</label>
	                   <input type="number" name="cxrmb" id="cxrmb" class="form-control" min="0.00" step="0.01" max="1000" placeholder="此程序的价格 免费请填0" required/>
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
	  <label class="btn-block" for="web_site_status">上/下架</label>
	  <div class="col-xs-6">
	  <div class="custom-control custom-switch custom-info">
            <input type="checkbox" class="custom-control-input" name="cxkg" id="cxkg" checked="true" checked>
            <label class="custom-control-label" for="cxkg"></label>
          </div>
              </div>
              </div>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
                  <button type="button" class="btn btn-primary" onclick="bj_bc()">确认保存</button>
                </div>
              </div>
            </div>
          </div>
          
  <div class="row">
    
    <div class="col-lg-12">
      <div class="card">
        <header class="card-header"><div class="card-title">宝塔列表</div></header>
        <div class="card-body">
          
          <div class="callout callout-info">
          <p class="small">
          <strong>操作图标详解</strong><br/>
        <a href="#!" class="btn btn-xs btn-default" title="编辑" data-toggle="tooltip"><i class="mdi mdi-pencil"></i></a>：编辑纪录
        <a href="#!" class="btn btn-xs btn-default" title="导出" data-toggle="tooltip"><i class="mdi mdi-package-variant-closed"></i></a>：程序导出<br/>
        <a href="#!" class="btn btn-xs btn-default" title="删除" data-toggle="tooltip"><i class="mdi mdi-window-close"></i></a>：删除纪录
        <a href="#!" class="btn btn-xs btn-default" title="下载程序源码" data-toggle="tooltip"><i class="mdi mdi-arrow-down"></i></a>：下载程序源码
          </p></div>
          <div id="toolbar" class="toolbar-btn-action">
            <button id="btn_add" type="button" class="btn btn-primary m-r-5 js-create-tab" aria-hidden="true" data-title="添加程序" data-url="add.php?gn=cx">
              <span class="mdi mdi-plus"></span>新增程序
            </button>
            <button id="btn_add" type="button" class="btn btn-primary m-r-5" onclick="daochu('xzcx')">
              <span class="mdi mdi-package-variant-closed"></span>导出选中
            </button>
            <button id="btn_delete" type="button" class="btn btn-danger" onclick="xzdelbt()">
              <span class="mdi mdi-window-close" aria-hidden="true"></span>删除选中
            </button>
          </div>
          <table id="tb_departments"></table>
          
        </div>
      </div>
    </div>
    
  </div>
  
</div>
<script type="text/javascript">

function hqxzh() {		//获取选中行
    var selRows = $("#tb_departments").bootstrapTable("getSelections");
    if(selRows.length == 0){
        msalert(3,"请至少选择一行",4000);
        return false;
    }

    var arr = new Array();
    $.each(selRows,function(i) {
    arr.push(this.id);
    });
    return arr;
}

function xzdelbt() {
if(!confirm('删除后不可恢复\n是否确认删除这些程序？')){return;}
msloading('正在删除中，请稍后...')  // 加载显示
var arr=hqxzh();
if(arr==false){msloadingde();return;}
let data = {};
data["gn"]="cxscxz";
data["idsz"]=arr;
$.post('./ajax.php', data, function (date) {
var jsoe= JSON.parse(date);    
var qk= jsoe.codr;
var qke= jsoe.code;

if(qk=='0'){
msloadingde();
msalert(1,'删除成功'+qke+'句',5000);
$("#tb_departments").bootstrapTable('refreshOptions',{pageNumber:1});		//刷新表格
}else{
msloadingde();
msalert(1,'删除成功'+qke+'句',5000);
msalert(4,'删除失败'+qk+'句',5000);
$("#tb_departments").bootstrapTable('refreshOptions',{pageNumber:1});		//刷新表格
}                        
})
}

function bj_bc() {
var t = $('#siteform').serializeArray();
var b = $('#siteinput').serializeArray();

var id=idr.value;
var cxnames=cxname.value;
var cxjcs=cxjc.value;
var webkjs=webkj.value;
var sqlkjs=sqlkj.value;
var cxrmbs=cxrmb.value;
var kge=cxkg.checked;
if(cxnames=="" || cxjcs=="" || webkjs=="" || sqlkjs=="" || cxrmbs==""){
msalert(3,'表单不能为空！',3000,'#tanchuang');
}else{
msloading('正在修改中...');
let data = {};
data["gn"]="cxxgjl";
data["id"]=id;
data["cxname"]=cxnames;
data["cxjc"]=cxjcs;
data["webkj"]=webkjs;
data["sqlkj"]=sqlkjs;
data["cxrmb"]=cxrmbs;
data["alerts"]=$("#alerts").val();
data["cxkg"]=kge;
$.each(t, function() {      //操作
data[this.name]=this.value;
});
$.each(b, function() {      //操作
data[this.name]=this.value;
});
$.post('./ajax.php', data, function (date) {
var jsoe= JSON.parse(date);    
var qk= jsoe.code

if(qk=='修改成功'){
$("#tb_departments").bootstrapTable('refreshOptions',{pageNumber:1});		//刷新表格
$('#tanchuang').modal('hide');		//关闭弹窗
msalert(1,'修改成功！',3000);
msloadingde();		//关闭加载条
}else{
msalert(4,qk,3000,'#tanchuang');
msloadingde();		//关闭加载条
}    
})
}}

$('#tb_departments').bootstrapTable({
    classes: 'table table-bordered table-hover table-striped',
    url: './ajax.php',
    method: 'post',		
    contentType : "application/x-www-form-urlencoded",  //请求格式
    dataType : 'json',        // 因为本示例中是跨域的调用,所以涉及到ajax都采用jsonp,
    uniqueId: 'id',
    idField: 'id',             // 每行的唯一标识字段
    toolbar: '#toolbar',       // 工具按钮容器
    //clickToSelect: true,     // 是否启用点击选中行
    showColumns: true,         // 是否显示所有的列
    showRefresh: true,         // 是否显示刷新按钮
    
    showToggle: true,        // 是否显示详细视图和列表视图的切换按钮(clickToSelect同时设置为true时点击会报错)
   
    pagination: true,                    // 是否显示分页
    sortOrder: "asc",                    // 排序方式
    sortName: "id",                     //默认排序字段
    queryParams: function(params) {
        var temp = {
            gn: 'listbs',         // 请求功能
            limit: params.limit,         // 每页数据量
            offset: params.offset,       // sql语句起始索引
            page: (params.offset / params.limit) + 1,
            sort: params.sort,           // 排序的列名
            sortOrder: params.order      // 排序方式'asc' 'desc'
        };
        return temp;
    },                                   // 传递参数
    sidePagination: "server",            // 分页方式：client客户端分页，server服务端分页
    pageNumber: 1,                       // 初始化加载第一页，默认第一页
    pageSize: 10,                        // 每页的记录行数
    pageList: [5, 10, 25, 50, 100],         // 可供选择的每页的行数
    //search: true,                      // 是否显示表格搜索，此搜索是客户端搜索
    
    showExport: false,        // 是否显示导出按钮, 导出功能需要导出插件支持(tableexport.min.js)
    exportDataType: "basic", // 导出数据类型, 'basic':当前页, 'all':所有数据, 'selected':选中的数据
  
    columns: [{
        field: 'example',
        checkbox: true    // 是否显示复选框
    }, {
        field: 'id',
        title: 'ID',
        sortable: true    // 是否排序
    }, {
        field: 'name',
        title: '程序名称'
    }, {
        field: 'jc',
        title: '程序介绍',
        formatter:function(value,row){ 
			return '<div style="width:150px;">'+value+'</div>';
		}
    }, {
        field: 'date',
        title: '添加时间',
        sortable: true    // 是否排序
    }, {
        field: 'cxdx',
        title: '程序大小',
        sortable: true,    // 是否排序
        formatter:function(value,row){ 
			return value+'MB';
		}
    }, {
        field: 'web',
        title: '最低网页空间',
        formatter:function(value,row){ 
			return JSON.parse(row.sxpz)[0]+'MB';
		}
    }, {
        field: 'sql',
        title: '最低数据库空间',
        formatter:function(value,row){ 
			return JSON.parse(row.sxpz)[1]+'MB';
		}
    }, {
        field: 'cs',
        title: '被部署次数',
        sortable: true,    // 是否排序
        formatter:function(value,row){
        var obj=JSON.parse(row.tj);
        var size = 0, key;  
        for (key in obj) {
        if (obj.hasOwnProperty(key)) size++;  
        }
        return size+'次';
		}
    }, {
        field: 'jg',
        title: '程序价格',
        sortable: true,    // 是否排序
        formatter:function(value,row){ 
			return value+'元';
		}
    },{
        field: 'src',
        title: '展示的图片',
        formatter:function(value,row){
        var jsoe=JSON.parse(value);
        trvimg='';
        jsoe.forEach(function(item){
            trvimg+='<img src="'+item+'" />';
        })
			return '<div class="listscr">'+trvimg+'</div>';
		}
		
    }, {
        field: 'qk',
        title: '情况',
        sortable: true,    // 是否排序
        formatter:function(value){ 
			if (value == 'false') {
				value = '<span class="badge badge-danger"><b>下架</b></span>';
			} else if(value == 'true') {
				value = '<span class="badge badge-success"><b>上架</b></span>';
			}else {
				value = row.pType ;
			}
			return value;
		}
    }, {
        field: 'operate',
        title: '数据操作',
        formatter: btnGroup,  // 自定义方法
        events: {
            'click .edit-btn': function (event, value, row, index) {
                editUser(row);
            },
            'click .daochu-btn': function (event, value, row, index) {
                daochu(row);        //程序导出
            },
            'click .del-btn': function (event, value, row, index) {
                delUser(row);
            },
            'click .ym-down': function (event, value, row, index) {
                window.location.href="./wjxz.php?idh="+row.id;
            }
        }
    }],
    onLoadSuccess: function(data){
        $("[data-toggle='tooltip']").tooltip();
    }
});

// 操作按钮
function btnGroup ()
{
    let html =
        '<a href="#!" class="btn btn-xs btn-default edit-btn" title="编辑" data-toggle="tooltip"><i class="mdi mdi-pencil"></i></a>' +
        '<a href="#!" class="btn btn-xs btn-default daochu-btn" title="导出" data-toggle="tooltip"><i class="mdi mdi-package-variant-closed"></i></a>' +
        '<a href="#!" class="btn btn-xs btn-default ym-down" title="下载源码" data-toggle="tooltip"><i class="mdi mdi-arrow-down"></i></a>'+
        '<a href="#!" class="btn btn-xs btn-default del-btn" title="删除" data-toggle="tooltip"><i class="mdi mdi-window-close"></i></a>';
    return html;
}

function daochu(row){      //程序导出
var cxid=row.id;
if(row=='xzcx'){
var arr=hqxzh();
if(arr==false){msloadingde();return;}
}else{
var arr=[row.id];
}
msloading('正在导出中，请稍后...');
let data = {};
data["gn"]="cxdc";
data["id"]=arr;
$.post('./ajax.php', data, function (date) {
var json= JSON.parse(date);
msalert(json.code,json.msg,5000);
if(json.code=1){
window.location.href="./wjxz.php?gn=cxfile";
}
msloadingde();
})
}


// 操作方法 - 编辑
function editUser(row)
{
document.getElementById("tjdcznr").innerHTML="";
document.getElementById("inputs").innerHTML="";
document.getElementById("cxname").value = row.name;
document.getElementById("cxjc").value = row.jc;
document.getElementById("webkj").value = JSON.parse(row.sxpz)[0];
document.getElementById("sqlkj").value = JSON.parse(row.sxpz)[1];
document.getElementById("cxrmb").value = row.jg;
document.getElementById("idr").value = row.id;
document.getElementById("alerts").value = row.alet;
if(row.qk=='false'){
document.getElementById("cxkg").checked = "";
}else{
document.getElementById("cxkg").checked = "false";
}

$('#tanchuang').modal();		//弹出弹窗
//程序添加并且内容修改
var rows=JSON.parse(row.pz);
if(rows!=null){
czdhx=1;
var xhcs=1;
$.each(rows,function(){
// 安全：直接调用 add_cz 函数，移除 eval 拼接字符串
add_cz(this.cz);

let arr = this;
for(var key in arr){
    document.getElementsByName("czf["+xhcs+"]["+key+"]")[0].value=arr[key];
}
xhcs++;
})
}
//表单添加并且内容修改
var rows=JSON.parse(row.inp);
if(rows!=null){
updhx=1;
bl_arr=[];          //初始化全局变量
var xhcs=1;
$.each(rows,function(){
// 安全：直接调用 add_pt 函数，移除 eval 拼接字符串
add_pt(this.cz);

let arr = this;
for(var key in arr){
    if($("select[name='bdf["+xhcs+"]["+key+"]']").length==0){
        //input标签和单/多选框
        if(key!='xknr'){        //input标签
    document.getElementsByName("bdf["+xhcs+"]["+key+"]")[0].value=arr[key];
        }else{      //单/多选框
    document.getElementsByName("bdf["+xhcs+"]["+key+"]")[0].value=arr[key];       //给隐藏输入框写入内容
            //给选框添加内容
            $($("textarea[name='bdf["+xhcs+"]["+key+"]']").parent().children()[1]).append(arr[key]);
        }
    }else{
        //select选项标签
        if(key=='blx'){
            //变量操作
            bl_arr.push(arr[key]);          //添加一个全局变量
            $("select[name='bdf["+xhcs+"]["+key+"]']").append('<option>'+arr[key]+'</option>');
            $("select[name='bdf["+xhcs+"]["+key+"]']").val(arr[key]);
        }else{      //非变量选框只用选择，不用添加
            $("select[name='bdf["+xhcs+"]["+key+"]']").val(arr[key]);
        }
    }
}
xhcs++;
})
qblup();    //更新全局变量
}
}
// 操作方法 - 删除
function delUser(row)
{
if(!confirm('删除后不可恢复\n是否确认删除该程序？')){return;}
msloading('正在删除中，请稍后...');
let data = {};
data["gn"]="cxsc";
data["id"]=row.id;
$.post('./ajax.php', data, function (date) {
var jsoe= JSON.parse(date);    
var qk= jsoe.code

if(qk=='删除成功'){
$("#tb_departments").bootstrapTable('refreshOptions',{pageNumber:1});		//刷新表格
msalert(1,'删除成功！',2000);
msloadingde();
}else{
msalert(4,qk,2000);
msloadingde();
}
})
}
</script>
</body>
</html>

<?php
}elseif($set=='dd'){
?>
          
  <div class="row">
    
    <div class="col-lg-12">
      <div class="card">
        <header class="card-header"><div class="card-title">订单列表</div></header>
        <div class="card-body">
          
          <div class="callout callout-info">
          <p class="small">
          <strong>操作图标详解</strong><br/>
        <a href="#!" class="btn btn-xs btn-default" title="删除" data-toggle="tooltip"><i class="mdi mdi-window-close"></i></a>：删除纪录
          </p></div>
          <div id="toolbar" class="toolbar-btn-action">
            <button id="btn_delete" type="button" class="btn btn-danger" onclick="xzdelbt()">
              <span class="mdi mdi-window-close" aria-hidden="true"></span>删除选中
            </button>
          </div>
          <table id="tb_departments"></table>
          
        </div>
      </div>
    </div>
    
  </div>
  
</div>
<script type="text/javascript">

function hqxzh() {		//获取选中行
    var selRows = $("#tb_departments").bootstrapTable("getSelections");
    if(selRows.length == 0){
        msalert(3,"请至少选择一行",4000);
        return false;
    }

    var arr = new Array();
    $.each(selRows,function(i) {
    arr.push(this.id);
    });
    return arr;
}

function xzdelbt() {
if(!confirm('删除后不可恢复\n是否确认删除这些订单记录？')){return;}
msloading('正在删除中，请稍后...')  // 加载显示
var arr=hqxzh();
if(arr==false){msloadingde();return;}
let data = {};
data["gn"]="ddscxz";
data["idsz"]=arr;
$.post('./ajax.php', data, function (date) {
var jsoe= JSON.parse(date);    
var qk= jsoe.codr;
var qke= jsoe.code;

if(qk=='0'){
msloadingde();
msalert(1,'删除成功'+qke+'句',5000);
$("#tb_departments").bootstrapTable('refreshOptions',{pageNumber:1});		//刷新表格
}else{
msloadingde();
msalert(1,'删除成功'+qke+'句',5000);
msalert(4,'删除失败'+qk+'句',5000);
$("#tb_departments").bootstrapTable('refreshOptions',{pageNumber:1});		//刷新表格
}                        
})
}

$('#tb_departments').bootstrapTable({
    classes: 'table table-bordered table-hover table-striped',
    url: './ajax.php',
    method: 'post',		
    contentType : "application/x-www-form-urlencoded",  //请求格式
    dataType : 'json',        // 因为本示例中是跨域的调用,所以涉及到ajax都采用jsonp,
    uniqueId: 'id',
    idField: 'id',             // 每行的唯一标识字段
    toolbar: '#toolbar',       // 工具按钮容器
    //clickToSelect: true,     // 是否启用点击选中行
    showColumns: true,         // 是否显示所有的列
    showRefresh: true,         // 是否显示刷新按钮
    
    showToggle: true,        // 是否显示详细视图和列表视图的切换按钮(clickToSelect同时设置为true时点击会报错)
   
    pagination: true,                    // 是否显示分页
    sortOrder: "desc",                    // 排序方式
    sortName: "id",                     //默认排序字段
    queryParams: function(params) {
        var temp = {
            gn: 'listdd',         // 请求功能
            limit: params.limit,         // 每页数据量
            offset: params.offset,       // sql语句起始索引
            page: (params.offset / params.limit) + 1,
            sort: params.sort,           // 排序的列名
            sortOrder: params.order      // 排序方式'asc' 'desc'
        };
        return temp;
    },                                   // 传递参数
    sidePagination: "server",            // 分页方式：client客户端分页，server服务端分页
    pageNumber: 1,                       // 初始化加载第一页，默认第一页
    pageSize: 10,                        // 每页的记录行数
    pageList: [10, 25, 50, 100],         // 可供选择的每页的行数
    //search: true,                      // 是否显示表格搜索，此搜索是客户端搜索
    
    showExport: true,        // 是否显示导出按钮, 导出功能需要导出插件支持(tableexport.min.js)
    exportDataType: "basic", // 导出数据类型, 'basic':当前页, 'all':所有数据, 'selected':选中的数据
  
    columns: [{
        field: 'example',
        checkbox: true    // 是否显示复选框
    }, {
        field: 'id',
        title: 'ID',
        sortable: true    // 是否排序
    }, {
        field: 'url',
        title: '发起者账号',
        formatter:function(value,row){
        var obj=JSON.parse(row.cs);
        return obj.user;
		},
        sortable: true    // 是否排序
    }, {
        field: 'spname',
        title: '支付商品',
        sortable: true    // 是否排序
    }, {
        field: 'ddh',
        title: '订单号',
        sortable: true    // 是否排序
    }, {
        field: 'je',
        title: '支付金额',
        sortable: true    // 是否排序
    }, {
        field: 'zffs',
        title: '支付方式',
        sortable: true    // 是否排序
    }, {
        field: 'date',
        title: '发起时间',
        sortable: true    // 是否排序
    }, {
        field: 'ip',
        title: '发起者IP'
    }, {
        field: 'qk',
        title: '支付情况',
        formatter:function(value){ 
			if (value == 'false') {
				value = '<span class="badge badge-danger"><b>未支付</b></span>';
			} else if(value == 'true') {
				value = '<span class="badge badge-success"><b>支付成功</b></span>';
			}else {
				value = '<span class="badge badge-danger"><b>出现了意料之外错误，请刷新页面，如果还是提示本句就请联系开发者解决</b></span>' ;
			}
			return value;
		}
    }, {
        field: 'operate',
        title: '操作',
        formatter: btnGroup,  // 自定义方法
        events: {
            'click .del-btn': function (event, value, row, index) {
                delUser(row);
            }
        }
    }],
    onLoadSuccess: function(data){
        $("[data-toggle='tooltip']").tooltip();
    }
});

// 操作按钮
function btnGroup ()
{
    let html =
        '<a href="#!" class="btn btn-xs btn-default del-btn" title="删除" data-toggle="tooltip"><i class="mdi mdi-window-close"></i></a>';
    return html;
}
// 操作方法 - 删除
function delUser(row)
{
if(!confirm('删除后不可恢复\n是否确认删除此条订单记录？')){return;}
msloading('正在删除中，请稍后...');
let data = {};
data["gn"]="ddsc";
data["id"]=row.id;
$.post('./ajax.php', data, function (date) {
var jsoe= JSON.parse(date);    
var qk= jsoe.code

if(qk=='删除成功'){
$("#tb_departments").bootstrapTable('refreshOptions',{pageNumber:1});		//刷新表格
msalert(1,'删除成功！',2000);
msloadingde();
}else{
msalert(4,qk,2000);
msloadingde();
}                        
})
}

</script>
</body>
</html>

<?php 
}elseif($set=='log'){?>
<!-- 域名管理已迁移至 domain_shop 插件，由 mnbt_plugin_render_menu_admin_html() 渲染 -->
<div class="container-fluid p-t-15">
  <div class="row">
    <div class="col-lg-12">
      <div class="card">
        <header class="card-header"><div class="card-title">操作日志</div></header>
        <div class="card-body">
          <div id="toolbar" class="toolbar-btn-action input-group">
            <div class="input-group-prepend">
              <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="where_name" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" val="">选择搜索字段</button>
              <div class="dropdown-menu">
                <a class="dropdown-item" val="czuser" href="#!">操作用户</a>
                <a class="dropdown-item" val="lx" href="#!">操作类型</a>
                <a class="dropdown-item" val="lr" href="#!">操作内容</a>
                <a class="dropdown-item" val="qk" href="#!">操作情况</a>
              </div>
              <div class="input-group-prepend">
                <button class="btn btn-outline-secondary dropdown-toggle rounded-0" type="button" id="where_type" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" val="2">模糊搜索</button>
                <div class="dropdown-menu">
                  <a class="dropdown-item" val="2" href="#!">模糊搜索</a>
                  <a class="dropdown-item" val="1" href="#!">精确搜索</a>
                </div>
              </div>
            </div>
            <input type="text" class="form-control" placeholder="请输入搜索内容" autocomplete="off" id="where_value">
            <div class="input-group-append">
              <button class="btn btn-outline-secondary rounded-right qrssat" type="button"><i class="mdi mdi-cloud-search-outline"></i>搜索</button>
              <button class="btn btn-outline-secondary rounded-right qrss" type="button"><i class="mdi mdi-backup-restore"></i>重置</button>
            </div>
            <button id="btn_clear" type="button" class="ml-2 btn btn-danger" onclick="clearLog()">
              <span class="mdi mdi-delete"></span>清空日志
            </button>
          </div>
          <table id="tb_log"></table>
        </div>
      </div>
    </div>
  </div>
</div>
<script type="text/javascript">
var where={"name":null,"type":null,"value":null};

$(".dropdown-item").on("click",function(k,v){
    const $dr=$($(this).parent().parent()).children(":first")
    $dr.html($(this).html())
    $dr.attr('val',$(this).attr('val'));
})

$(".qrssat").on("click",function(){
    where.name=$("#where_name").attr('val');
    where.type=$("#where_type").attr('val');
    where.value=$("#where_value").val().trim();
    $("#tb_log").bootstrapTable('refreshOptions',{pageNumber:1});
})

function clearLog() {
    if(!confirm('确定要清空所有操作日志吗？\n此操作不可恢复！')){return;}
    msloading('正在清空日志...');
    let data = {};
    data["gn"]="logclear";
    $.post('./ajax.php', data, function (date) {
        var jsoe= JSON.parse(date);
        if(jsoe.code=='清空成功'){
            msalert(1,'日志已清空！',2000);
            $("#tb_log").bootstrapTable('refreshOptions',{pageNumber:1});
        } else {
            msalert(4,'清空失败！',2000);
        }
        msloadingde();
    })
}

$('#tb_log').bootstrapTable({
    classes: 'table table-bordered table-hover table-striped',
    url: './ajax.php',
    method: 'post',
    contentType : "application/x-www-form-urlencoded",
    dataType : 'json',
    uniqueId: 'id',
    idField: 'id',
    toolbar: '#toolbar',
    showColumns: true,
    showRefresh: true,
    showToggle: true,
    pagination: true,
    sortOrder: "desc",
    sortName: "id",
    queryParams: function(params) {
        var temp = {
            gn: 'listlog',
            where: JSON.stringify(where),
            limit: params.limit,
            offset: params.offset,
            page: (params.offset / params.limit) + 1,
            sort: params.sort,
            sortOrder: params.order
        };
        return temp;
    },
    sidePagination: "server",
    pageNumber: 1,
    pageSize: 15,
    pageList: [10, 15, 25, 50, 100],
    columns: [{
        field: 'id',
        title: 'ID',
        sortable: true
    }, {
        field: 'czuser',
        title: '操作用户',
        sortable: true
    }, {
        field: 'lx',
        title: '操作类型',
        sortable: true
    }, {
        field: 'lr',
        title: '操作内容',
        formatter:function(value){
            return '<div style="max-width:400px;word-break:break-all;">'+value+'</div>';
        }
    }, {
        field: 'qk',
        title: '操作情况',
        sortable: true
    }, {
        field: 'ip',
        title: 'IP地址'
    }, {
        field: 'date',
        title: '操作时间',
        sortable: true,
        sortName: 'id'
    }],
    onLoadSuccess: function(data){
        try {
            if (Number(data.code) === 4) {
                msalert(4, data.msg);
                $('#tb_log').bootstrapTable("removeAll");
                return;
            }
            if (Number(data.total) === 0) {
                $('#tb_log').bootstrapTable("removeAll");
            }
        } catch (e) {}
    }
});
</script>
<?php }?>