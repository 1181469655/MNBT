/*
 * MN宝塔系统
 * 整套系统的所有设置页面的js全在这了
 * 版权归梦奈所有
 * 2022 © 梦奈
*/

function setwz() {
var gg=wzgg.value;
var qqh=qq.value;
var yzm=yzmkg.checked;
var zjyx = zjyxbd.checked;
msloading('正在修改中！请稍后...','text-info','text-info');
let data = {};
data["gn"]="setwz";
data["gg"]=gg;
data["qq"]=qqh;
data["yzm"]=yzm;
data['zjyx'] = zjyx;
$.post('./ajax.php', data, function (date) {
var jsoe= JSON.parse(date);    
var qk= jsoe.code

if(qk=='修改成功'){
msalert(1,'修改成功',2000);
msloadingde();
}else{
msalert(4,qk,2000);
msloadingde();
}                        
})
}

function apisc() {
msloading('正在生成中！请稍后...','text-info','text-info');  // 加载显示
var date = new Date();
var sj=Math.ceil(Math.random()*1000);
var str='hello'+sj+'mw';
var sjs=md5(str);
document.getElementById("apimy").value = sjs;
msalert(1,'生成成功',100);
msloadingde();  // 隐藏
}
function setapi() {
var apikey=apimy.value;
var php=mrphp.value;
var lml=linuxml.value;
var wml=winml.value;
var apiqk=apikg.checked;
if(apikey=="" || lml=="" || wml==""){
msalert(3,'请将表单填写完整',2000);
}
else
{
msloading('正在修改中！请稍后...','text-info','text-info');  // 加载显示
let data = {};
data["gn"]="setapi";
data["apikey"]=apikey;
data["php"]=php;
data["linux"]=lml;
data["windows"]=wml;
data["apiqk"]=apiqk;
$.post('./ajax.php', data, function (date) {
var jsoe= JSON.parse(date);    
var qk= jsoe.code

if(qk=='修改成功'){
msalert(1,'修改成功！',2000);
msloadingde();  // 隐藏
}else{
msalert(4,qk,2000);
msloadingde();
}                        
})
}}

function setkzmb() {
var name=kzmbname.value;
var ftpxt=ftp.value;
var yzm=yzmkzmb.checked;
var kg=kzmbkg.checked;
var bqr=bq.value;
var la= document.getElementById("logoa");
var lb = document.getElementById("logob");
var lc = document.getElementById("logoc");
if(name=="" || bqr==""){
msalert(3,'请填写控制面板名和版权',2000);
}
else
{
msloading('正在修改中！请稍后...','text-info','text-info');  // 加载显示

var formdata = new FormData(); 
formdata.append("gn","setkzmb");
formdata.append("loa",la.files[0]);
formdata.append("lob",lb.files[0]);
formdata.append("loc",lc.files[0]);
formdata.append("bq",bqr); 
formdata.append("name",name); 
formdata.append("ftp",ftpxt);
formdata.append("yzm",yzm);
formdata.append("kg",kg);


 var xhr = new XMLHttpRequest()
 xhr.open('POST', './ajax.php',true);
 xhr.send(formdata)
 xhr.onload=function(data){
 date=data.target.responseText;
var jsoe= JSON.parse(date);    
var qk= jsoe.code
if(qk=='修改成功'){
msalert(1,'修改成功！',2000);
msloadingde();  // 隐藏
}else{
msalert(4,qk,2000);
msloadingde();  // 隐藏
}
}

}}

function setgl() {
var yuser=ysuser.value;
var ypass=yspass.value;
var xuser=huser.value;
var xpass=hpass.value;
if(yuser=="" || ypass==""){
msalert(3,'原账号密码不能为空！',2000);
}else{
msloading('正在修改中！请稍后...','text-info','text-info');  // 加载显示
let data = {};
data["gn"]="gl";
data["yuser"]=yuser;
data["ypass"]=ypass;
data["xuser"]=xuser;
data["xpass"]=xpass;
$.post('./ajax.php', data, function (date) {
var jsoe= JSON.parse(date);    
var qk= jsoe.code

if(qk=='修改成功'){
msalert(1,'修改成功！',2000);
msloadingde();  // 隐藏
}else{
msalert(4,qk,2000);
msloadingde();  // 隐藏
}                        
})
}}

function dellog(del) {
msloading('正在删除中！请稍后...','text-info','text-info');  // 加载显示
let data = {};
data["gn"]="logsc";
data["id"]=del;
$.post('./ajax.php', data, function (date) {
var jsoe= JSON.parse(date);    
var qk= jsoe.code

if(qk=='删除成功'){
msalert(1,'删除成功！',2000);
window.location.href="./log.php"
lightyear.loading('hide');  // 隐藏
}else{
msalert(4,qk,2000);
lightyear.loading('hide');  // 隐藏
}                        
})
}

function xzdel() {
msloading('正在删除中！请稍后...','text-info','text-info');  // 加载显示
//alert('ok');
var objects = document.getElementsByTagName ("input");
var arr = ' ';
for (i=0;i<objects.length;i++){
if (objects[i].checked == true) {
var szz=objects[i].value;
if(szz!='on' && szz!=''){
var arr=arr+szz+',';
}
}
}
//alert(arr);
let data = {};
data["gn"]="logscxz";
data["idsz"]=arr;
$.post('./ajax.php', data, function (date) {
var jsoe= JSON.parse(date);    
var qk= jsoe.codr;
var qke= jsoe.code;

if(qk=='0'){
msloadingde();  // 隐藏
msalert(1,'删除成功'+qk+'句',2000);
window.location.href="./log.php"
}else{
msloadingde();  // 隐藏
msalert(1,'删除成功'+qk+'句',2000);
msalert(4,'删除失败'+qk+'句',2000);
window.location.href="./log.php"
}                        
})
}


$(function(){
    //文件选择监听
$(".custom-file-input").on("change",function(){
    if(this.files[0]==null){
        $("label[for='"+this.id+"']").html('选择文件...');
    }else{
        $("label[for='"+this.id+"']").html(this.files[0].name);
    }
});
});
function mailmode()
{
    msloading('正在处理中，请稍后...');  // 加载显示
    let host = mailhost.value;
    let user = mailuser.value;
    let password = mailpassword.value;
    let port = mailport.value;
    let data = {};
    data['gn'] = "mailmode";
    data['host'] = host;
    data['user'] = user;
    data['password'] = password;
    data['port'] = port;
    
    
    $.post('./ajax.php', data, function (date) {
    var jsoe= JSON.parse(date);    
    var qk= jsoe.code
    if(qk == "修改成功")
    {
        msalert(1,'修改成功！将在两秒后跳转登录！',2000);
    setTimeout(function()
    {
        window.location.href="./set.php?gn=mail";
    },2000);
    }
    else
    {
        msalert(4, qk,2000);
            setTimeout(function()
    {
        window.location.href="./set.php?gn=mail";
    },2000);
    
    }
        
    })
    
}
function jkscsz()
{
    //msloading('正在处理中，请稍后...');  // 加载显示
    let ymkg = ymkga.checked;
    let ymyjkg = ymyjkga.checked;
    let ymtsyz = ymtsyza.value;
    let wjkg = wjkga.checked;
    let wjyjkg = wjyjkga.checked;
    let wjtsyz = wjtsyza.value;
    let option = option1.value;
    let data = {};
    data['gn'] = "jkscsz";
    data['ymkg'] = ymkg;
    data['ymyjkg'] = ymyjkg;
    data['ymtsyz'] = ymtsyz;
    data['wjkg'] = wjkg;
    data['wjyjkg'] = wjyjkg;
    data['wjtsyz'] = wjtsyz;
    data['option'] = option;
    $.post('./ajax.php', data, function (date) {
    var jsoe= JSON.parse(date);
    var qk= jsoe.code
    if(qk == "修改成功")
    {
        msalert(1,'修改成功！将在两秒后刷新页面！',2000);
    setTimeout(function()
    {
        window.location.href="./set.php?gn=jk";
    },2000);
    }
    else
    {
        msalert(4, qk,2000);
            setTimeout(function()
    {
        window.location.href="./set.php?gn=jk";
    },2000);

    }

    })
}

function settheme() {
    var ut = document.getElementById('usertheme');
    var at = document.getElementById('admintheme');
    if (!ut || !at) {
        msalert(4, '主题选择控件未找到', 2000);
        return;
    }
    if (!ut.value || !at.value) {
        msalert(3, '请选择用户端和管理端主题', 2000);
        return;
    }
    msloading('正在保存主题设置...','text-info','text-info');
    var data = {};
    data['gn'] = 'settheme';
    data['usertheme'] = ut.value;
    data['admintheme'] = at.value;
    $.post('./ajax.php', data, function (date) {
        var jsoe = JSON.parse(date);
        var qk = jsoe.code;
        if (qk == '修改成功') {
            msalert(1, '修改成功！将在两秒后刷新页面！', 2000);
            setTimeout(function () {
                window.location.href = './set.php?gn=theme';
            }, 2000);
        } else {
            msalert(4, qk, 3000);
            msloadingde();
        }
    });
}


