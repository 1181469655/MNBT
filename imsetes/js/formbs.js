

function add_inp(czlx,bdbz,rows){      //添加表单
     msloading('添加中，请稍后...');  // 加载圈显示
     // 安全：白名单验证 czlx 字段，只允许预定义的表单类型
     var allowedTypes = ['input','dxk','dxks','urlxz','textarea','file','select','checkbox','radio','hidden','password','email','date','time','datetime'];
     if (allowedTypes.indexOf(czlx) === -1) {
         console.error('非法的表单类型:', czlx);
         msloadingde();
         return;
     }
     var funcName = 'gp_' + czlx;
     if (typeof window[funcName] !== 'function') {
         console.error('未找到表单处理函数:', funcName);
         msloadingde();
         return;
     }
     var ret = window[funcName](bdbz, rows);
     var tmp = document.createElement("div");
     tmp.className="form-group";
     tmp.innerHTML= ret+'<br/>';
     document.getElementById("form-txx").appendChild(tmp);
     if(czlx=='input'){
     $("input[name='bds["+bdbz+"]["+rows.cz+"]']").maxlength({
     warningClass: "badge badge-info",
     limitReachedClass: "badge badge-warning"
    });
     }
     msloadingde();  // 隐藏加载圈
}


function gp_input(num,row){            //表单
     var inpst='bds['+num+']['+row.cz+']';    //表单名
     if(row.bt=='是'){bt='*';}else{bt='';}
     if(row.srlx=='数字'){tp='number';}else{tp='text';}
     var tmp = document.createElement("div"); 
     var retus='<label class="control-label"><span class="text-danger">'+bt+'</span>'+row.ltaler+'</label><input type="'+tp+'" maxlength="'+row.cdxz+'" name="'+inpst+'" placeholder="'+row.isnr+'" class="form-control" required/>';
     return retus;
}

function gp_dxk(num,row){          //单选框
     var inpst='bds['+num+']['+row.cz+']';    //表单名
     var tmp = document.createElement("div"); 
     var retus='<label class="control-label"><span class="text-danger">*</span>'+row.ltaler+'</label><select class="form-control" name="'+inpst+'">'+row.xknr+'</select>';
     return retus;
}

function gp_dxks(num,row){     //多选框
     var inpst='bds['+num+']['+row.cz+']';    //表单名
     var tmp = document.createElement("div"); 
     var retus='<label class="control-label"><span class="text-danger">*</span>'+row.ltaler+'</label><select multiple class="form-control" name="'+inpst+'">'+row.xknr+'</select>';
     return retus;
}

function gp_urlxz(num,row){     //域名选择
     var inpst='bds['+num+']['+row.cz+']';    //表单名
     if(row.bt=='是'){bt='*';}else{bt='';}
     if(row.urlxml=='绑定了子目录的'){var types=1;}else if(row.urlxml=='未绑定子目录的'){var types=2;}else{var types=3;}
     let data={}; 
     data["gn"]="urllist";
     data["type"]=types;
     var dspe='';
     $.ajax({ type:'POST', url:'./ajax.php', data: data, async:false, success:function(date){         //获取域名数据
     var json= JSON.parse(date)['url'];
     $.each(json,function(xb,val){
     dspe+='<option>'+val.name+'</option>';
     })
     }})
     var tmp = document.createElement("div"); 
     var retus='<label class="control-label"><span class="text-danger">'+bt+'</span>'+row.ltaler+'</label><select class="form-control" name="'+inpst+'">'+dspe+'</select>';
     return retus;
}
