czdhx=1;       //操作第几步的数字记录
updhx=1;       //表单第几个的数字记录
bl_arr=[]       //存放变量的数组

function add_cz(czlx){      //添加操作
     msloading('添加中，请稍后...');  // 加载圈显示
     // 安全：白名单验证 czlx 字段，只允许预定义的操作类型
     var allowedCzlx = ['xjwj','xjwjj','delwj','delwjj','setwj','drsql','gettj','setyxml','setwjt'];
     if (allowedCzlx.indexOf(czlx) === -1) {
         console.error('非法的操作类型:', czlx);
         msloadingde();
         return;
     }
     var funcName = 'gl_' + czlx;
     if (typeof window[funcName] !== 'function') {
         console.error('未找到操作处理函数:', funcName);
         msloadingde();
         return;
     }
     var ret = window[funcName]();
     var tmp = document.createElement("div");
     tmp.className="form-group nbvbk border border-cyan";
     tmp.innerHTML= '<label for="web_site_icp">'+'第'+czdhx+'步操作：'+ret[0]+'</label><a href="#!" class="mdi mdi-delete float-right text-dark"></a>'+ret[1];
     document.getElementById("tjdcznr").appendChild(tmp);
     czdhx++;        //步骤数字+1
     msloadingde();  // 隐藏加载圈
}

function add_pt(czlx){      //添加表单(得有预览功能)
     msloading('添加中，请稍后...');  // 加载圈显示
     // 安全：白名单验证 czlx 字段，只允许预定义的表单类型
     var allowedCzlx = ['input','dxk','dxks','urlxz','textarea','file','select','checkbox','radio'];
     if (allowedCzlx.indexOf(czlx) === -1) {
         console.error('非法的表单类型:', czlx);
         msloadingde();
         return;
     }
     var funcName = 'gs_' + czlx;
     if (typeof window[funcName] !== 'function') {
         console.error('未找到表单处理函数:', funcName);
         msloadingde();
         return;
     }
     var ret = window[funcName]();
     var tmp = document.createElement("div");
     tmp.className="nbvbks border border-purple";
     tmp.innerHTML= '<label for="web_site_icp">表单：'+ret[0]+'</label><a href="#!" class="mdi mdi-delete float-right text-dark"></a>'+ret[1];
     document.getElementById("inputs").appendChild(tmp);
     updhx++;        //步骤数字+1
     qblup()        //刷新全局可选择变量
     msloadingde();  // 隐藏加载圈
}



function gl_xjwj(){
     var lx='xjwj';                 //操作类型：新建文件
     var czmc='新建文件';            //这步操作的名称（用于显示）
     var clname1='czf['+czdhx+'][cz]';    //cz是操作类型的意思
     var clname2='czf['+czdhx+'][name]';    //name是文件名称
     var clname3='czf['+czdhx+'][ml]';    //ml是文件存放目录
     var retus='<input type="text" style="visibility:hidden" name="'+clname1+'" value="'+lx+'"/><input type="text" name="'+clname2+'" class="form-control" placeholder="新建文件的名称" required/><br/><input type="text" name="'+clname3+'" class="form-control" placeholder="新建文件的存放目录" required/>';
     return [czmc,retus];
}

function gl_xjwjj(){
     var lx='xjwjj';                 //操作类型：新建文件夹
     var czmc='新建文件夹';            //这步操作的名称（用于显示）
     var clname1='czf['+czdhx+'][cz]';    //cz是操作类型的意思
     var clname2='czf['+czdhx+'][name]';    //name是文件名称
     var clname3='czf['+czdhx+'][ml]';    //ml是文件存放目录
     var retus='<input type="text" style="visibility:hidden" name="'+clname1+'" value="'+lx+'"/><input type="text" name="'+clname2+'" class="form-control" placeholder="新建文件夹的名称" required/><br/><input type="text" name="'+clname3+'" class="form-control" placeholder="新建文件夹的存放目录" required/>';
     return [czmc,retus];
}

function gl_delwj(){
     var lx='delwj';                 //操作类型：删除文件
     var czmc='删除文件';            //这步操作的名称（用于显示）
     var clname1='czf['+czdhx+'][cz]';    //cz是操作类型的意思
     var clname2='czf['+czdhx+'][name]';    //naem该文件的名称
     var clname3='czf['+czdhx+'][ml]';    //ml该文件的所在目录
     var retus='<input type="text" style="visibility:hidden" name="'+clname1+'" value="'+lx+'"/><input type="text" name="'+clname2+'" class="form-control" placeholder="该文件的名称" required/><br/><input type="text" name="'+clname3+'" class="form-control" placeholder="该文件的所在目录" required/>';
     return [czmc,retus];
}

function gl_delwjj(){
     var lx='delwjj';                 //操作类型：删除文件夹
     var czmc='删除文件夹';            //这步操作的名称（用于显示）
     var clname1='czf['+czdhx+'][cz]';    //cz是操作类型的意思
     var clname2='czf['+czdhx+'][name]';    //naem该文件夹的名称
     var clname3='czf['+czdhx+'][ml]';    //ml该文件夹的所在目录
     var retus='<input type="text" style="visibility:hidden" name="'+clname1+'" value="'+lx+'"/><input type="text" name="'+clname2+'" class="form-control" placeholder="该文件夹的名称" required/><br/><input type="text" name="'+clname3+'" class="form-control" placeholder="该文件夹的所在目录" required/>';
     return [czmc,retus];
}

function gl_setwj(){
     var lx='setwj';                 //操作类型：修改文件内容
     var czmc='修改文件内容';            //这步操作的名称（用于显示）
     var clname1='czf['+czdhx+'][cz]';    //cz是操作类型的意思
     var clname2='czf['+czdhx+'][name]';    //naem该文件的名称
     var clname3='czf['+czdhx+'][ml]';    //ml该文件夹的所在目录
     var clname4='czf['+czdhx+'][nr]';    //nr该文件修改后的内容
     var retus='<input type="text" style="visibility:hidden" name="'+clname1+'" value="'+lx+'"/><input type="text" name="'+clname2+'" class="form-control" placeholder="该文件的名称" required/><br/><input type="text" name="'+clname3+'" class="form-control" placeholder="该文件的所在目录" required/><br/><textarea type="text" name="'+clname4+'" class="form-control" placeholder="修改后的内容" rows="15"></textarea>';
     return [czmc,retus];
}

function gl_drsql(){
     var lx='drsql';                 //操作类型：导入数据库
     var czmc='导入数据库文件';            //这步操作的名称（用于显示）
     var clname1='czf['+czdhx+'][cz]';    //cz是操作类型的意思
     var clname2='czf['+czdhx+'][name]';    //naem该数据库文件的名称
     var clname3='czf['+czdhx+'][ml]';    //ml该文件的的所在目录
     var retus='<input type="text" style="visibility:hidden" name="'+clname1+'" value="'+lx+'"/><input type="text" name="'+clname2+'" class="form-control" placeholder="该数据库文件的名称" required/><br/><input type="text" name="'+clname3+'" class="form-control" placeholder="该数据库文件的所在目录" required/>';
     return [czmc,retus];
}

function gl_gettj(){
     var lx='gettj';                 //操作类型：访问URL
     var czmc='GET提交';            //这步操作的名称（用于显示）
     var clname1='czf['+czdhx+'][cz]';    //cz是操作类型的意思
     var clname2='czf['+czdhx+'][url]';    //url是要访问的域名
     var clname3='czf['+czdhx+'][get]';    //get是要提交的内容
     var retus='<input type="text" style="visibility:hidden" name="'+clname1+'" value="'+lx+'"/><input type="text" name="'+clname2+'" class="form-control" placeholder="要访问的域名(写法请看下方介绍)" required/><br/><input type="text" name="'+clname3+'" class="form-control" placeholder="要提交的内容(写法请看下方介绍)" required/>';
     return [czmc,retus];
}


function gl_setyxml(){
     var lx='setyxml';                 //操作类型：设置运行目录
     var czmc='设置运行目录';            //这步操作的名称（用于显示）
     var clname1='czf['+czdhx+'][cz]';    //cz是操作类型的意思
     var clname2='czf['+czdhx+'][lj]';    //lj是路径
     var retus='<input type="text" style="visibility:hidden" name="'+clname1+'" value="'+lx+'"/><input type="text" name="'+clname2+'" class="form-control" placeholder="请输入文件夹名称（仅限根目录下的一级文件夹）" required/>';
     return [czmc,retus];
}


function gl_setwjt(){
     var lx='setwjt';                 //操作类型：设置伪静态
     var czmc='设置伪静态';            //这步操作的名称（用于显示）
     var clname1='czf['+czdhx+'][cz]';    //cz是操作类型的意思
     var clname2='czf['+czdhx+'][nr]';    //nr是内容
     var retus='<input type="text" style="visibility:hidden" name="'+clname1+'" value="'+lx+'"/><textarea type="text" name="'+clname2+'" class="form-control" placeholder="伪静态内容" rows="15"></textarea>';
     return [czmc,retus];
}





function gs_input(){
     var lx='input';                            //操作类型：表单
     var czmc='一个表单';            //这步操作的名称（用于显示）
     var clname1='bdf['+updhx+'][cz]';      //cz是操作类型的意思
     var clname2='bdf['+updhx+'][ltaler]';    //ltaler是左上角文字提示
     var clname3='bdf['+updhx+'][blx]';    //blx是选择的变量
     var clname4='bdf['+updhx+'][isnr]';    //isnr是输入框内部显示的文字
     var clname5='bdf['+updhx+'][bt]';    //bt是是否必填
     var clname6='bdf['+updhx+'][cdxz]';    //cdxz是长度限制
     var clname7='bdf['+updhx+'][srlx]';    //srlx是输入类型
     var tmp = document.createElement("div"); 
     var retus='<input type="text" style="visibility:hidden" name="'+clname1+'" value="'+lx+'"/><div class="form-row dspj"><div class="col-12 col-md-6 dspj"><input type="text" class="form-control" placeholder="左上角显示的提示文字" name="'+clname2+'"></div><div class="col-12 col-md-6 dspj"><select class="custom-select" id="blxz" name="'+clname3+'"><option>变量选择</option><option>新建一个变量</option></select></div></div><div class="form-row dspj"><div class="col-12 col-md-6 dspj"><input type="text" class="form-control" placeholder="输入框内部显示的提示文字" name="'+clname4+'"></div><div class="col-12 col-md-6 dspj"><select class="custom-select" name="'+clname5+'"><option>是否必填</option><option>是</option><option>否</option></select></div></div><div class="form-row dspj"><div class="col-12 col-md-6 dspj"><input type="number" class="form-control" placeholder="长度限制 最多可以输入多少个字符" name="'+clname6+'"></div><div class="col-12 col-md-6 dspj"><select class="custom-select" name="'+clname7+'"><option>输入类型</option><option>文本</option><option>数字</option></select></div></div>';
     return [czmc,retus];
}

function gs_dxk(){
     var lx='dxk';                            //操作类型：单选框
     var czmc='单选框';            //这步操作的名称（用于显示）
     var clname1='bdf['+updhx+'][cz]';      //cz是操作类型的意思
     var clname2='bdf['+updhx+'][ltaler]';    //ltaler是左上角文字提示
     var clname3='bdf['+updhx+'][blx]';    //blx是选择的变量
     var clname4='bdf['+updhx+'][xknr]';    //xknr是选框内容
     var tmp = document.createElement("div"); 
     var retus='<input type="text" style="visibility:hidden" name="'+clname1+'" value="'+lx+'"/><div class="form-row dspj"><div class="col-12 col-md-6 dspj"><input type="text" class="form-control" placeholder="左上角显示的提示文字" name="'+clname2+'"></div><div class="col-12 col-md-6 dspj"><select class="custom-select" id="blxz" name="'+clname3+'"><option>变量选择</option><option>新建一个变量</option></select></div><div class="col-12 col-md-12 dspj"><textarea type="text" style="display:none" value="" name="'+clname4+'"></textarea><select class="custom-select" id="dxknradd"><option>单选框内容</option><option>添加一个选项</option><option>↓↓以下选项会展示给用户↓↓</option></select></div></div></div>';
     return [czmc,retus];
}

function gs_dxks(){
     var lx='dxks';                            //操作类型：多选框
     var czmc='多选框';            //这步操作的名称（用于显示）
     var clname1='bdf['+updhx+'][cz]';      //cz是操作类型的意思
     var clname2='bdf['+updhx+'][ltaler]';    //ltaler是左上角文字提示
     var clname3='bdf['+updhx+'][blx]';    //blx是选择的变量
     var clname4='bdf['+updhx+'][xknr]';    //xknr是选框内容
     var tmp = document.createElement("div"); 
     var retus='<input type="text" style="visibility:hidden" name="'+clname1+'" value="'+lx+'"/><div class="form-row dspj"><div class="col-12 col-md-6 dspj"><input type="text" class="form-control" placeholder="左上角显示的提示文字" name="'+clname2+'"></div><div class="col-12 col-md-6 dspj"><select class="custom-select" id="blxz" name="'+clname3+'"><option>变量选择</option><option>新建一个变量</option></select></div><div class="col-12 col-md-12 dspj"><textarea type="text" style="display:none" value="" name="'+clname4+'"></textarea><select multiple class="custom-select" id="dxknradd"><option>多选框内容</option><option>添加一个选项</option><option>↓↓以下选项会展示给用户↓↓</option></select></div></div></div>';
     return [czmc,retus];
}

function gs_urlxz(){
     var lx='urlxz';                            //操作类型：域名选择
     var czmc='域名选择';            //这步操作的名称（用于显示）
     var clname1='bdf['+updhx+'][cz]';      //cz是操作类型的意思
     var clname2='bdf['+updhx+'][ltaler]';    //ltaler是左上角文字提示
     var clname3='bdf['+updhx+'][blx]';    //blx是选择的变量
     var clname4='bdf['+updhx+'][urlxml]';    //urlxml是选框内容
     var clname5='bdf['+updhx+'][bt]';    //bt是是否必填
     var tmp = document.createElement("div"); 
     var retus='<input type="text" style="visibility:hidden" name="'+clname1+'" value="'+lx+'"/><div class="form-row dspj"><div class="col-12 col-md-6 dspj"><input type="text" class="form-control" placeholder="左上角显示的提示文字" name="'+clname2+'"></div><div class="col-12 col-md-6 dspj"><select class="custom-select" id="blxz" name="'+clname3+'"><option>变量选择</option><option>新建一个变量</option></select></div></div><div class="form-row dspj"><div class="col-12 col-md-6 dspj"><select class="custom-select" name="'+clname4+'"><option>能选择的域名</option><option>无限制</option><option>绑定了子目录的</option><option>未绑定子目录的</option></select></div><div class="col-12 col-md-6 dspj"><select class="custom-select" name="'+clname5+'"><option>是否必选</option><option>是</option><option>否</option></select></div></div><small>单选，将在用户填写表单时让用户选择主机已经绑定的域名</small></div>';
     return [czmc,retus];
}



function qblup(){       //全局变量更新
var ysyblarr=[];
var ysyblarr2={};
$("select[id='blxz']").each(function(){     //获取已经使用的变量
if($(this).val()=='新建一个变量'){
ysyblarr.push('变量选择');
}else{
ysyblarr.push($(this).val());       //判断用
ysyblarr2[$(this).val()]=this;           //获取用
}
});
var bls='<option>变量选择</option>'
$.each(bl_arr,function(i,va){       //循环当前所有变量
if($.inArray(va,ysyblarr)=='-1'){     //没有被使用的变量
//未被使用的变量
bls+='<option>'+this+'</option>'
}else{
//已经被使用的变量
}
})
bls+='<option>新建一个变量</option>'
$("select[id='blxz']").html(bls);

$.each(ysyblarr2,function(is,vai){       //循环当前所有变量
if(is!='变量选择' && is!='新建一个变量'){
$(vai).append('<option>'+is+'</option>');
}
})

//给予值
var adts=0;
$("select[id='blxz']").each(function(){
$(this).find("option:contains('"+ysyblarr[adts]+"')").attr("selected",true);
adts++;
});
}

$(function(){

$("#inputs").on("change","select[id='dxknradd']",function(){
if($(this).val() instanceof Array){
if($(this).val().length>1){
return;
}
}
var dqxxk=this;
if($(this).val()=='添加一个选项'){
    $.confirm({
        title: '新增一个单选框选项',
        content: '<div class="form-group p-1 mb-0">' + 
                 '  <label class="control-label">选项名</label>' +
                 '  <input autofocus="" type="text" id="input-bls" placeholder="请输入选项名" class="form-control">' +
                 '<br/>'+
                 '</div>',
        buttons: {
            sayMyName: {
                text: '确定',
                btnClass: 'btn-orange',
                action: function() {
                    var input = this.$content.find('input#input-bls');
                    if (!$.trim(input.val())) {
                        $.alert({
                            title: '提示',
                            content: "选项名字段不能为空为空。",
                            type: 'red'
                        });
                        return false;
                    } else {
                        $(dqxxk).append('<option>'+input.val()+'</option>');
                        var inputsr=$(dqxxk).parent().children()[0];
                        var ynr=inputsr.value;
                        $(inputsr).val(ynr+'<option>'+input.val()+'</option>');            //添加到隐藏表单中
                        $(dqxxk).val("单选框内容");
                    }
                }
            },
            '取消': function() {}
        }
    });
}else if($(this).val()!='单选框内容' && $(this).val()!='多选框内容' && $(this).val()!='↓↓以下选项会展示给用户↓↓'){
        $.confirm({
        title: '提示',
        content: '确认删除选项 ['+$(dqxxk).val()+'] 吗？',
        buttons: {
            sayMyName: {
                text: '确定',
                btnClass: 'btn-orange',
                action: function() {
                    var valiue=$(dqxxk).val();
                    $(dqxxk).find("option:contains('"+$(dqxxk).val()+"')").remove();
                        var inputsr=$(dqxxk).parent().children()[0];
                        var ynr=inputsr.value;
                        var thhr = ynr.replace('<option>'+valiue+'</option>', "");
                        $(inputsr).val(thhr);
                    //替换隐藏input中的op
                }
            },
            '取消': function() {}
        }
    });
}
})



//变量选择
$("#inputs").on("change",'select[id="blxz"]',function(){
var xznr=$(this).val();
if(xznr=='新建一个变量'){
    $.confirm({
        title: '新建一个变量',
        content: '<div class="form-group p-1 mb-0">' + 
                 '  <label class="control-label">变量名</label>' +
                 '  <input autofocus="" type="text" id="input-bls" placeholder="请输入新变量的变量名" class="form-control">' +
                 '<br/><small>变量名可随意输入，变量是在替换数据时使用的，用户在部署程序时系统会弹出一个对话窗让用户填写您添加的表单(未添加任何表单则不会弹出对话窗)，用户填写完成后您可以在添加操作时(仅限修改文件内容和GET提交)输入特定文本( [sf_变量名] )即可在系统执行部署操作时将这些特定文本替换为用户输入的数据！</small>'+
                 '</div>',
        buttons: {
            sayMyName: {
                text: '确定',
                btnClass: 'btn-orange',
                action: function() {
                    var input = this.$content.find('input#input-bls');
                    if (!$.trim(input.val())) {
                        $.alert({
                            title: '提示',
                            content: "新的变量字段不能为空为空。",
                            type: 'red'
                        });
                        return false;
                    } else {
                        if($.inArray(input.val(),bl_arr)!='-1'){
                        $.alert({
                            title: '提示',
                            content: "变量已存在！",
                            type: 'red'
                        });
                        return false;
                        }
                        bl_arr.push(input.val());
                        //更新全局变量下拉
                        qblup();
                    }
                }
            },
            '取消': function() {qblup();}
        }
    });
}else{
//已选择变量，更新全局变量下拉
qblup();
}
})

//表单操作-表单删除

$("#inputs").on("mouseover mouseout",'a.mdi-delete',function () {          //鼠标移动上去修改样式名
$(this).closest('.text-dark').toggleClass("text-danger");
$(this).closest('.mdi-delete').toggleClass("mdi-delete-empty");
})

$("#inputs").on("click","a.mdi-delete",function(){         //删除一行操作
$(this).parent().addClass("animated bounce fadeOutRight");
var bq=$(this).parent();
setTimeout(function() {
bq.remove();       //删除一个操作
var sybq=$("#inputs").children();      //获取所有子标签
if(sybq.length==0){
updhx=1;            //无操作标签，重置操作步骤
}
}, 400);
})


//安装时的操作-步骤删除

$("#tjdcznr").on("mouseover mouseout",'a.mdi-delete',function () {          //鼠标移动上去修改样式名
$(this).closest('.text-dark').toggleClass("text-danger");
$(this).closest('.mdi-delete').toggleClass("mdi-delete-empty");
})

$("#tjdcznr").on("click","a.mdi-delete",function(){         //删除一行操作
$(this).parent().addClass("animated bounce fadeOutRight");
var bq=$(this).parent();
setTimeout(function() {
bq.remove();       //删除一个操作
var sybq=$("#tjdcznr").children();      //获取所有子标签
if(sybq.length!=0){
var xhcs=1;
$.each(sybq,function(){     //循环
var csd=$($(this).children()[0])[0].innerText;          //获取子标签(label)的内容
var dre=csd.replace(/[\d]+/,xhcs)               //替换数字
$($(this).children()[0]).html(dre);             //显示的操作步骤修改
xhcs++;
})
}else{
czdhx=1;            //无操作标签，重置操作步骤
}
}, 400);
})

})