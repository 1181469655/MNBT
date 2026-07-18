<?php
/*
 * 这是远程宝塔文件管理系统
 * 由小泉独立完成
 * 未经允许禁止修改
 * 小泉QQ3108007898
 * 全套系统由小泉以及梦奈完成
 * 版权©归梦奈所有
 */
mnbt_theme_include('head');
set_time_limit(0);
ignore_user_abort();
ini_set('memory_limit', '-1');
$siot=$_GET['wj'] ?? '';
if($conf['hxw']=='amftp' || $conf['hxw']==''){
    $cert=$DB->get_row_prepare("SELECT * FROM MN_bt WHERE btdh=? limit 1", [$ssbt]);
    $ftp_server=$cert['btip'];
    $ftp_user_name=$yhc['user'];
    $ftp_user_pass=$yhc['pass'];
    echo "<form style='display:none;' id='form1' name='form1' method='post' action='./amftp/index.php?c=index&a=amftp_login'> 
              <input name='ftp_port' type='text' value='21' />
              <input name='ftp_pasv' type='text' value='0'/>
              <input name='ftp_ip' type='text' value='".$ftp_server."'/>
              <input name='ftp_user' type='text' value='".$ftp_user_name."'/>
              <input name='ftp_pass' type='text' value='".$ftp_user_pass."'/>
              <input name='sbpostdl' type='text'  id='myform' value='登录'/>
            </form>
            <script type='text/javascript'>function load_submit(){document.form1.submit()}load_submit();</script>";
    exit;
}
?>
<!--对话框-->
<script type="text/javascript" src="<?=mnbt_asset_url('js/jquery-confirm/jquery-confirm.min.js')?>"></script>
<script type="text/javascript" src="<?=mnbt_asset_url('js/bootstrap-table/bootstrap-table.min.js')?>"></script>
<script type="text/javascript" src="<?=mnbt_asset_url('js/bootstrap-table/locale/bootstrap-table-zh-CN.min.js')?>"></script>
<!--代码编辑插件-->
<link href="<?=mnbt_asset_url('codemirror/lib/codemirror.css')?>" rel="stylesheet" type="text/css">
<link id="thme-filecod" href="<?=mnbt_asset_url('codemirror/theme/3024-night.css')?>" rel="stylesheet" type="text/css">
<link href="<?=mnbt_asset_url('codemirror/addon/display/fullscreen.css')?>" rel="stylesheet" type="text/css">
<link href="<?=mnbt_asset_url('codemirror/addon/dialog/dialog.css')?>" rel="stylesheet" type="text/css">        <!--文件内容搜索框美化-->
<link rel="stylesheet" href="<?=mnbt_asset_url('codemirror/addon/hint/show-hint.css')?>"><!--代码提示-->

<div class="container-fluid p-t-15">

    <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModal" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title" id="exampleModalChangeTitle">上传文件</h6>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"> <span aria-hidden="true">&times;</span> </button>
                </div>
                <div class="card-body">
                    <p>支持断点续传！</p>
                    <p>当上传的文件存在时将覆盖！</p>
                    <p>如果上传的文件与现有的文件名称一样则将覆盖现有的文件！</p>
                    <div class="custom-file">
                        <input type="file" name="myfile" id="myfile" class="custom-file-input" required>
                        <label class="custom-file-label" for="validatedCustomFile">选择文件...</label>
                    </div><br/>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
                        <button type="submit" class="btn btn-primary" onclick="zxwjsc(paths)">确认上传</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="xjwj" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title" id="exampleModalChangeTitle">新建文件</h6>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"> <span aria-hidden="true">&times;</span> </button>
                </div>
                <div class="modal-body">
                    <form>
                        <p>文件名不能包含各种特殊符号比如：空格 {}：“|《》？【】；’、，。{}:"|<>?[];',!@#$%^&*()_-+=等；</p>
                        <div class="form-group">
                            <label for="message-text" class="control-label">存放路径</label>
                            <input type="text" name="xjlja" id="xjlja" class="form-control" placeholder="新建的文件的存放路径" readonly="true"/>
                        </div>
                        <br/>
                        <div class="form-group">
                            <label for="message-text" class="control-label">文件名称</label>
                            <input type="text" name="namea" id="namea" class="form-control" placeholder="请在此输入文件的名称" />
                        </div>
                        <br/>
                    </form>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
                        <button type="submit" class="btn btn-primary" onclick="wjwjcg()">确认新建</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="xjwjj" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title" id="exampleModalChangeTitle">新建文件夹</h6>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"> <span aria-hidden="true">&times;</span> </button>
                </div>
                <div class="card-body">
                    <p>文件夹名不能包含各种特殊符号比如：空格 {}：“|《》？【】；’、，。{}:"|<>?[];',!@#$%^&*()_-+=等；</p>
                    <div class="form-group"> <span for="message-text" class="control-label">存放路径</span>
                        <input type="text" name="xjljb" id="xjljb" class="form-control" placeholder="新建的文件夹的存放路径" readonly="true"/>
                    </div>
                    <br/>
                    <div class="form-group"> <span for="message-text" class="control-label">文件夹名称</span>
                        <input type="text" name="namec" id="namec" class="form-control" placeholder="请在此输入文件夹的名称" />
                    </div>
                    <br/>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
                        <button type="submit" class="btn btn-primary" onclick="wjwjjg()">确认新建</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="setwj" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title" id="setfiletitle">修改文件内容</h6>
                    <div class="col-md-2 mb-3">
                        <select id="setfilezt" class="custom-select form-control-sm">
                            <option value="dqs">切换编辑器主题[当前3024-night]</option><option>default</option><option>3024-day</option><option>3024-night</option><option>abbott</option><option>abcdef</option><option>ambiance</option><option>ayu-dark</option><option>ayu-mirage</option><option>base16-dark</option><option>base16-light</option><option>bespin</option><option>blackboard</option><option>cobalt</option><option>colorforth</option><option>darcula</option><option>dracula</option><option>duotone-dark</option><option>duotone-light</option><option>eclipse</option><option>elegant</option><option>erlang-dark</option><option>gruvbox-dark</option><option>hopscotch</option><option>icecoder</option><option>idea</option><option>isotope</option><option>juejin</option><option>lesser-dark</option><option>liquibyte</option><option>lucario</option><option>material</option><option>material-darker</option><option>material-palenight</option><option>material-ocean</option><option>mbo</option><option>mdn-like</option><option>midnight</option><option>monokai</option><option>moxer</option><option>neat</option><option>neo</option><option>night</option><option>nord</option><option>oceanic-next</option><option>panda-syntax</option><option>paraiso-dark</option><option>paraiso-light</option><option>pastel-on-dark</option><option>railscasts</option><option>rubyblue</option><option>seti</option><option>shadowfox</option><option>solarized dark</option><option>solarized light</option><option>the-matrix</option><option>tomorrow-night-bright</option><option>tomorrow-night-eighties</option><option>ttcn</option><option>twilight</option><option>vibrant-ink</option><option>xq-dark</option><option>xq-light</option><option>yeti</option><option>yonce</option><option>zenburn</option>
                        </select>
                    </div>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"> <span aria-hidden="true">&times;</span> </button>
                    <button type="button" class="modal-fullscreen-btn"><i class="mdi"></i></button>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <textarea type="text" name="wjnr" id="wjnr" cols="30" rows="10"></textarea>
                    </div><br/>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
                        <button type="submit" class="btn btn-primary" onclick="setwj()">确认保存</button>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div class="modal fade" id="zipjys" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title" id="exampleModalChangeTitle">解压文件</h6>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"> <span aria-hidden="true">&times;</span> </button>
                </div>
                <div class="card-body">
                    <p>目前仅支持解压zip格式的压缩包！</p>
                    <p>如果解压完成后没有文件请稍等3秒左右后手动刷新，如果还没有文件就是压缩包出问题了！</p>
                    <div class="form-group">
                        <span for="message-text" class="control-label">需解压的文件及路径</span>
                        <input type="text" name="jywjyd" id="jywjyd" class="form-control" placeholder="需要解压的文件以及它的路径" readonly="true"/>
                    </div><br/>
                    <div class="form-group">
                        <span for="message-text" class="control-label">解压到</span>
                        <input type="text" name="jyd" id="jyd" class="form-control" placeholder="请在此输入解压到的路径" />
                    </div><br/>
                    <div class="form-group">
                        <span for="message-text" class="control-label">解压密码</span>
                        <input type="text" name="jymm" id="jymm" class="form-control" placeholder="如果没有密码请留空" />
                    </div><br/>
                    <div class="form-group">
                        <span for="message-text" class="control-label">编码</span>
                        <select class="form-control" id="bm" name="bm" size="1">
                            <option value="UTF-8">UTF-8</option>
                            <option value="GBK">GBK</option>
                        </select>
                    </div><br/>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
                        <button type="button" class="btn btn-primary" onclick="jysfile();">确认解压</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="imageyul" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title" id="tupyltitle">图片预览</h6>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"> <span aria-hidden="true">&times;</span> </button>
                </div>
                <div class="card-body">
                    <div class="scrollspy-example-2 border border-info" id="imgsh">
                        <img src="" id="imgsrc"/>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-info" id="yulantext" onclick="yulanset(this);">全图预览</button>
                        <button type="button" class="btn btn-primary" data-dismiss="modal">关闭</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <header class="card-header">
                    <div class="card-title">主机文件列表</div>
                </header>
                <div class="card-body">
                    <div class="callout callout-info">
                        <p class="small"><b>操作图标详解</b></p>
                        <div class="ftp-icon-legend">
                            <div class="wqbr"><a href="#!" class="btn btn-xs btn-default" title="下载文件" data-toggle="tooltip"><i class="mdi mdi-cloud-download-outline"></i></a>：下载文件</div>
                            <div class="wqbr"><a href="#!" class="btn btn-xs btn-default" title="编辑文件内容" data-toggle="tooltip"><i class="mdi mdi-pencil"></i></a>：编辑文件内容</div>
                            <div class="wqbr"><a href="#!" class="btn btn-xs btn-default" title="重命名文件" data-toggle="tooltip"><i class="mdi mdi-format-italic"></i></a>：重命名文件</div>
                            <div class="wqbr"><a href="#!" class="btn btn-xs btn-default" title="解压文件" data-toggle="tooltip"><i class="mdi mdi-arrow-up-bold-box"></i></a>：解压文件</div>
                            <div class="wqbr"><a href="#!" class="btn btn-xs btn-default" title="复制文件" data-toggle="tooltip"><i class="mdi mdi-content-copy"></i></a>：复制文件</div>
                            <div class="wqbr"><a href="#!" class="btn btn-xs btn-default" title="剪切文件" data-toggle="tooltip"><i class="mdi mdi-content-cut"></i></a>：剪切文件</div>
                            <div class="wqbr"><a href="#!" class="btn btn-xs btn-default" title="删除" data-toggle="tooltip"><i class="mdi mdi-window-close"></i></a>：删除文件</div>
                            <div class="wqbr"><a href="#!" class="btn btn-xs btn-default" title="导入SQL文件" data-toggle="tooltip"><i class="mdi mdi-import mdi-rotate-90"></i></a>：导入到数据库</div>
                            <div class="wqbr"><a href="#!" class="btn btn-xs btn-default" title="导入SQL文件" data-toggle="tooltip"><i class="mdi mdi-image-search-outline"></i></a>：图片预览</div>
                        </div>
                        <small><b>注意：只有部分文件类型才会显示特定按钮</b></small>
                    </div>
                    <div id="toolbar" class="toolbar-btn-action">
                        <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"> 文件操作 <span class="caret"></span> </button>
                        <ul class="dropdown-menu">
                            <li><a href="#!" class="dropdown-item" data-target="#xjwj" data-whatever="@mdo" data-toggle="modal" onclick="document.getElementById('xjlja').value=paths"><i class="mdi mdi-file-plus-outline"></i>新建文件</a></li>
                            <li><a href="#!" class="dropdown-item" data-target="#xjwjj" data-whatever="@mdo" data-toggle="modal" onclick="document.getElementById('xjljb').value=paths"><i class="mdi mdi-folder-plus-outline"></i>新建文件夹</a></li>
                        </ul>
                        <button data-toggle="modal" data-target="#exampleModal" data-whatever="@mdo" type="button" class="btn btn-primary m-r-5"> <span class="mdi mdi-cloud-upload" aria-hidden="true"></span>上传文件 </button>
                        <div class="btn-group show">
                            <button type="button" id="btn_gengduoset" style="display:none;" class="btn btn-info dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"> 更多操作 <span class="caret"></span> </button>
                            <div id="gengduoset">
                                <ul class="dropdown-menu">
                                    <li><a href="#!" class="dropdown-item" onclick="copyfile(false,2);"><i class="mdi mdi-content-copy"></i>复制选中文件</a></li>
                                    <li><a href="#!" class="dropdown-item" onclick="copyfile(false,2,2);"><i class="mdi mdi-content-cut"></i>剪切选中文件</a></li>
                                    <li><a href="#!" class="dropdown-item" onclick="ysfile();"><i class="mdi mdi-zip-box-outline"></i>压缩选中文件</a></li>
                                    <li><a href="#!" class="dropdown-item" onclick="xzdelfile();"><i class="mdi mdi-window-close"></i>删除选中文件</a></li>
                                </ul>
                            </div>
                        </div>
                        <button style="display:none;" id="btn_filepst" type="button" class="btn btn btn-success" onclick="pastefile();"> <span class="mdi mdi-content-paste" aria-hidden="true"></span>粘贴文件 </button>

                        <div class="table-responsive" id="mus"> /<a href="#!" class="text-success" onclick="mulus('/',2)">根目录</a> </div>
                    </div>
                    <table id="tb_departments">
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- 引入CodeMirror核心文件 -->
<script type="text/javascript" src="<?=mnbt_asset_url('codemirror/lib/codemirror.js')?>"></script>
<script src="<?=mnbt_asset_url('codemirror/mode/clike/clike.js')?>"></script>
<!-- CodeMirror支持不同语言，根据需要引入JS文件 -->
<!-- 因为HTML混合语言依赖Javascript、XML、CSS语言支持，所以都要引入 -->
<script type="text/javascript" src="<?=mnbt_asset_url('codemirror/mode/javascript/javascript.js')?>"></script>
<script type="text/javascript" src="<?=mnbt_asset_url('codemirror/mode/xml/xml.js')?>"></script>
<script type="text/javascript" src="<?=mnbt_asset_url('codemirror/mode/css/css.js')?>"></script>
<script type="text/javascript" src="<?=mnbt_asset_url('codemirror/mode/htmlmixed/htmlmixed.js')?>"></script>
<script type="text/javascript" src="<?=mnbt_asset_url('codemirror/mode/sql/sql.js')?>"></script>
<script type="text/javascript" src="<?=mnbt_asset_url('codemirror/mode/php/php.js')?>"></script>

<!-- 下面分别为显示行数、括号匹配和全屏插件和自动刷新 -->
<script type="text/javascript" src="<?=mnbt_asset_url('codemirror/addon/selection/active-line.js')?>"></script>
<script type="text/javascript" src="<?=mnbt_asset_url('codemirror/addon/edit/matchbrackets.js')?>"></script>
<script type="text/javascript" src="<?=mnbt_asset_url('codemirror/addon/display/fullscreen.js')?>"></script>
<script type="text/javascript" src="<?=mnbt_asset_url('codemirror/addon/display/autorefresh.js')?>"></script>
<script type="text/javascript" src="<?=mnbt_asset_url('codemirror/addon/edit/closebrackets.js')?>"></script><!--自动闭合括号-->
<script type="text/javascript" src="<?=mnbt_asset_url('codemirror/addon/search/search.js')?>"></script>  <!--搜索内容-->
<script type="text/javascript" src="<?=mnbt_asset_url('codemirror/addon/search/searchcursor.js')?>"></script>  <!--查找上一个-->
<script type="text/javascript" src="<?=mnbt_asset_url('codemirror/addon/search/jump-to-line.js')?>"></script>  <!--查找下一个-->
<script type="text/javascript" src="<?=mnbt_asset_url('codemirror/addon/dialog/dialog.js')?>"></script>  <!--搜索框美化-->
<!--自动提示-->
<script src="<?=mnbt_asset_url('codemirror/addon/hint/show-hint.js')?>"></script>
<script src="<?=mnbt_asset_url('codemirror/addon/hint/sql-hint.js')?>"></script>
<script src="<?=mnbt_asset_url('codemirror/addon/hint/xml-hint.js')?>"></script>
<script src="<?=mnbt_asset_url('codemirror/addon/hint/html-hint.js')?>"></script>
<script src="<?=mnbt_asset_url('codemirror/addon/hint/javascript-hint.js')?>"></script>

<script type="text/javascript">
    paths='/';
    file_copy=false;

    //读取缓存获取设置的编辑器主题
    if(localStorage.getItem("themes")==null){
        var thmers="3024-night";            //默认的编辑器主题
    }else{
        var thmers=localStorage.getItem("themes");
        if(thmers!='default'){
            document.getElementById("thme-filecod").href="<?=mnbt_asset_url('codemirror/theme/')?>"+thmers+".css";
        }
        $("option[value='dqs']").html('编辑器主题[当前'+thmers+']');
    }

    //配置代码编辑器
    var myTextarea = document.getElementById('wjnr');
    var CodeMirrorEditor = CodeMirror.fromTextArea(myTextarea, {
        lineNumbers: true,     // 显示行号
        tabSize:4,              //制表符
        indentUnit: 1,         // 缩进单位为2
        styleActiveLine: true, // 当前行背景高亮
        matchBrackets: true,   // 括号匹配
        mode: "application/x-httpd-php",     // php混合html
        lineWrapping: true,    // 自动换行
        foldGutter:true,
        theme: thmers,      // 编辑器主题
        lineWiseCopyCut:false,  //关闭未选择时使用剪切或者复制则选中当前行
        autofocus:true,        //自动获取焦点
        dragDrop: false,        //是否使用拖拽
        flattenSpans:false,         //取消相邻相同类组合为span
        spellcheck:true,        //拼写检查
        autoRefresh:true,       //打开自动刷新
        autoCloseBrackets: true,        //自动闭合括号
        specialChars: /[\u0000-\u001f\u007f-\u009f\u00ad\u061c\u200b-\u200f\u2028\u2029\ufeff\ufff9-\ufffc\s]/g,        //空格显示为灰色小点
        specialCharPlaceholder: function (ch) {     //设置为小点
            let token = document.createElement("span");
            let content = "\u002e";
            token.className = "cm-invalidchar";
            if (typeof content == "string") {
                token.appendChild(document.createTextNode(content));
            }
            return token
        },
        hintOptions: { // 自定义提示选项
            completeSingle: false, // 当匹配只有一项的时候是否自动补全
        },
        extraKeys: {
            "Tab": (cm) => {
                if (cm.somethingSelected()) {      // 存在文本选择
                    cm.indentSelection('add');    // 正向缩进文本
                } else {                    // 无文本选择
                    cm.replaceSelection(Array(cm.getOption("indentUnit") + 1).join(" "), "end", "+input");  // 光标处插入 indentUnit 个空格
                }
            },
            "Ctrl-S": function(cm) {
                setwj(false);        //保存文件并且不关闭编辑弹窗
            },
            "Ctrl-H": "replace",            //替换功能
        },
    });
    CodeMirrorEditor.setSize('100%','750px');

    CodeMirrorEditor.on("keyup", function (cm, event) {
        //所有的字母和'$','{','.'在键按下之后都将触发自动完成
        if (!cm.state.completionActive &&
            ((event.keyCode >= 65 && event.keyCode <= 90 ) || event.keyCode == 52 || event.keyCode == 219 || event.keyCode == 190)) {
            CodeMirror.commands.autocomplete(cm, null, {completeSingle: false});
        }
    });


    //监听编辑器主题切换
    $("#setfilezt").on("change",function(){
        var thmers=this.options[this.selectedIndex].textContent;
        if(this.options[this.selectedIndex].value!='dqs'){
            if(thmers!='default'){
                document.getElementById("thme-filecod").href="<?=mnbt_asset_url('codemirror/theme/')?>"+thmers+".css";
            }
            CodeMirrorEditor.setOption("theme", thmers);
            //将选择的主题缓存到本地
            localStorage.setItem("themes", thmers);
            $("option[value='dqs']").html('编辑器主题[当前'+thmers+']');
            $(this).find("option").eq(0).prop("selected",true);         //永久显示第一个选项
            console.log(this);
        }
    });


    function hqxzh(msg=true) {		//获取选中行
        var selRows = $("#tb_departments").bootstrapTable("getSelections");
        if(selRows.length == 0){
            if(msg==true){
                msalert(3,"请至少选择一行",4000);
            }
            return false;
        }

        var arr = new Array();
        $.each(selRows,function(i) {
            arr.push(this.name);
        });
        return arr;
    }

    $('#tb_departments').on('uncheck.bs.table check.bs.table check-all.bs.table uncheck-all.bs.table load-success.bs.table', function (e, row) {        //监听选中与取消选中
        var xzh=hqxzh(false);
        if(xzh!=false){     //有选中行
            document.getElementById("btn_gengduoset").style.display="inline-block";
            $("#btn_gengduoset").removeClass('animated zoomOut');
            $("#btn_gengduoset").addClass('animated zoomIn');
            document.getElementById("gengduoset").style.display="inline-block";
        }else{              //无选中行
            $("#btn_gengduoset").removeClass('animated zoomIn');
            $("#btn_gengduoset").addClass('animated zoomOut');
            document.getElementById("gengduoset").style.display="none";
            setTimeout(function() {document.getElementById("btn_gengduoset").style.display="none";},500);
        }
    });

    function yulanset(data){
        if(data.innerHTML=='全图预览'){
            $('#imgsh').removeClass('scrollspy-example-2');
            data.innerHTML='滑动预览';
        }else{
            $('#imgsh').addClass('scrollspy-example-2');
            data.innerHTML='全图预览';
        }
    }

    $('#tb_departments').bootstrapTable({
        classes: 'table table-bordered table-hover table-striped',
        url: './ajax.php',
        method: 'post',
        contentType : "application/x-www-form-urlencoded",  //请求格式
        dataType : 'json',        // 返回数据格式
        uniqueId: 'id',
        idField: 'id',             // 每行的唯一标识字段
        toolbar: '#toolbar',       // 工具按钮容器
        //clickToSelect: true,     // 是否启用点击选中行
        showColumns: true,         // 是否显示所有的列
        showRefresh: true,         // 是否显示刷新按钮

        showToggle: true,        // 是否显示详细视图和列表视图的切换按钮(clickToSelect同时设置为true时点击会报错)

        pagination: true,                    // 是否显示分页
        sortOrder: "asc",                    // 排序方式
        sortName: "type",                     //默认排序字段
        queryParams: function(params) {
            var temp = {
                gn: 'listfile',         // 请求功能
                path: paths,         // 目录
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
        pageSize: 100,                        // 每页的记录行数
        pageList: [25,50,100,200,500,1000],         // 可供选择的每页的行数
        //search: true,                      // 是否显示表格搜索，此搜索是客户端搜索

        showExport: false,        // 是否显示导出按钮, 导出功能需要导出插件支持(tableexport.min.js)
        exportDataType: "basic", // 导出数据类型, 'basic':当前页, 'all':所有数据, 'selected':选中的数据
        responseHandler: function (res) {       //当前所在目录与返回数据目录误差纠正
            var fanhpath=res.path;
            if(fanhpath!=paths){
                mulus('/',2);
            }
            return res;
        },

        columns: [{
            field: 'example',
            checkbox: true    // 是否显示复选框
        }, {
            field: 'name',
            title: '文件名称',
            sortable: true,    // 是否排序
            formatter:function(value,row){
                if(row.type=='dir'){        //文件夹
                    return '<div style="width:100px;">'+
                        '<a class="text-success" style="font-size:15px;" href="#!" onclick="mulus(this.childNodes[1].innerHTML,1);">'+
                        '<i class="mdi mdi-24px mdi-folder-open" style="vertical-align:middle;"></i><span>'+value+'</span></a></div>';
                }else{      //文件
                    return '<a class="text-default">'+
                        '<i class="mdi mdi-18px '+fileico(value)+'" style="vertical-align:middle;"></i>'+value+'</a>';
                }
            }
        }, {
            field: 'size',
            title: '文件大小',
            sortable: true,    // 是否排序
            formatter:function(value,row){
                if(row.type=='dir'){
                    return '<a class="text-success" href="#!" onclick="filesize(this,'+"'"+row.name+"'"+');">计算</a>';
                }else{
                    var values=value/1024;
                    if(values>1024){
                        var values=values/1024;
                        num = values.toFixed(2)+'MB';
                    }else{
                        num = values.toFixed(2)+'KB';
                    }
                    return num;
                }
            }
        }, {
            field: 'mtime',
            title: '修改时间',
            sortable: true,    // 是否排序
            formatter:function(value,row){
                var date=getTimes(value);
                return date;
            }
        }, {
            field: 'operate',
            title: '文件操作',
            formatter: btnGroup,  // 自定义方法
            events: {
                'click .edit-btn': function (event, value, row, index) {
                    editfile(row);
                },
                'click .del-btn': function (event, value, row, index) {
                    delfile(row);
                },
                'click .fd-name': function (event, value, row, index) {
                    setname(row);
                },
                'click .file-jys': function (event, value, row, index) {
                    if(row.name==null){msalert(3,'需解压文件不存在！',4000); return;}
                    msloading('正在加载中...');  // 加载显示
                    document.getElementById("jywjyd").value = paths+row.name;
                    document.getElementById("jyd").value = paths;
                    document.getElementById("jymm").value = '';
                    msloadingde();  // 隐藏
                    $('#zipjys').modal();		//弹出弹窗
                },
                'click .file-dr': function (event, value, row, index) {
                    drsqlfile(row);
                },
                'click .cp-file': function (event, value, row, index) {
                    copyfile(row,1);
                },
                'click .jq-file': function (event, value, row, index) {
                    copyfile(row,1,2);
                },
                'click .file-down': function (event, value, row, index) {

                    get_file_download_id(row,(url)=>{
                        window.open(url, '_blank');
                    })

                },
                'click .file-img': function (event, value, row, index) {
                    //使用模态框显示图片
                    get_file_download_id(row,(url)=>{
                        $('#imgsrc').attr('src',url);
                        $('#tupyltitle').html('图片预览['+row.name+']');
                        $('#imageyul').modal();		//弹出弹窗
                    })

                    $('#imageyul').on('hidden.bs.modal', function (e) {        //监听模态框关闭事件
                        $('#imgsrc').attr('src','');
                        $("#imgsh").addClass('scrollspy-example-2');
                        $('#yulantext').html('全图预览');
                    });
                }
            }
        }],
        onLoadSuccess: function(data){
            $("[data-toggle='tooltip']").tooltip();
        }
    });

    function get_file_download_id(row,fun){
        msloading('正在获取文件下载链接...');  // 加载显示
        $.post("./wjxz.php?dowtype=dowbtfile&filepath="+paths+"&filename="+row.name, {}, function (date) {
            let json= JSON.parse(date);
            fun(json.url)
            msloadingde();  // 隐藏
        })

    }

    // 操作按钮
    function btnGroup (value,row)
    {
        if(row.type=='dir'){
            //文件夹操作
            var html =
                '<a href="#!" class="btn btn-xs btn-default fd-name" title="重命名文件夹" data-toggle="tooltip"><i class="mdi mdi-format-italic"></i></a>' +
                '<a href="#!" class="btn btn-xs btn-default cp-file" title="复制文件夹" data-toggle="tooltip"><i class="mdi mdi-content-copy"></i></a>' +
                '<a href="#!" class="btn btn-xs btn-default jq-file" title="剪切文件夹" data-toggle="tooltip"><i class="mdi mdi-content-cut"></i></a>' +
                '<a href="#!" class="btn btn-xs btn-default del-btn" title="删除" data-toggle="tooltip"><i class="mdi mdi-window-close"></i></a>';
        }else{
            //文件操作
            var eduset='';
            var ann_msg=filebtn(row.name);
            if(ann_msg[1]){
                eduset='<a href="#!" class="btn btn-xs btn-default edit-btn" title="编辑" data-toggle="tooltip"><i class="mdi mdi-pencil"></i></a>'
            }
            var html =
                eduset+
                '<a href="#!" class="btn btn-xs btn-default file-down" title="下载文件" data-toggle="tooltip"><i class="mdi mdi-cloud-download-outline"></i></a>' +
                '<a href="#!" class="btn btn-xs btn-default fd-name" title="重命名文件" data-toggle="tooltip"><i class="mdi mdi-format-italic"></i></a>' +
                '<a href="#!" class="btn btn-xs btn-default cp-file" title="复制文件" data-toggle="tooltip"><i class="mdi mdi-content-copy"></i></a>' +
                '<a href="#!" class="btn btn-xs btn-default jq-file" title="剪切文件" data-toggle="tooltip"><i class="mdi mdi-content-cut"></i></a>' +
                ann_msg[0]+
                '<a href="#!" class="btn btn-xs btn-default del-btn" title="删除" data-toggle="tooltip"><i class="mdi mdi-window-close"></i></a>';
        }
        return html;
    }
    function ysfile(){
        filenames=paths+'yuanma'+Math.floor(Math.random()*2000)+'.';
        $.confirm({
            title: '压缩选中文件',
            content: '<div class="form-group p-1 mb-0">' +
                '  <label class="control-label">压缩类型</label>' +
                '  <select id="input-type" class="custom-select" onchange="document.getElementById('+"'input-path'"+').value=filenames+this.options[this.options.selectedIndex].value">' +
                '  <option value="zip">zip(通用格式)</option><option value="tar.gz">tar.gz(推荐)</option><option value="rar">rar(WinRAR对中文兼容较好)</option><option value="7z">7z(压缩率极高的压缩格式)</option></select>' +
                '  <label class="control-label">压缩包存放路径</label>' +
                '  <input type="text" id="input-path" placeholder="请输入此的新名称" class="form-control" value="'+filenames+'zip">' +
                '</div>',
            buttons: {
                sayMyName: {
                    text: '确认压缩',
                    btnClass: 'btn-info',
                    action: function() {
                        var typel = this.$content.find('select#input-type');
                        var pathl = this.$content.find('input#input-path');
                        if (!$.trim(pathl.val())) {
                            $.alert({
                                title: "提示",
                                content: "压缩包存放路径字段不能为空！",
                                type: 'red'
                            });
                            return false;
                        } else {
                            msloading('正在压缩文件...');  // 加载显示
                            let data = {};
                            data["gn"]="fileys";
                            data["file"]=hqxzh();
                            data["dpath"]=pathl.val();
                            data["type"]=typel.val();
                            data["path"]=paths;
                            $.post('./ajax.php', data, function (date) {
                                var jsoe= JSON.parse(date);
                                var qk= jsoe.code

                                msalert(jsoe.qk,jsoe.code,2000);
                                $("#tb_departments").bootstrapTable('refreshOptions',{pageNumber:1});		//刷新表格
                                msloadingde();  // 隐藏
                            })
                        }
                    }
                },
                '取消': function() {}
            }
        });
    }

    //复制/剪切文件
    function pastefile(){
        if(file_copy==false){msalert(3,'未选择要复制的文件！',2000);return;}
        if(file_copy[0]==paths){msalert(3,'复制目录与粘贴目录禁止相同！',2000);return;}
        var file_zclj=[false];            //文件粘贴主从逻辑判断变量
        $.each(file_copy[1],function(i) {
            var psr=paths.slice(0,(file_copy[0]+this+'/').length);
            if(psr==file_copy[0]+this+'/'){
                file_zclj=[true,psr];
            }
        });
        if(file_zclj[0]){msalert(3,'主从逻辑错误！目录'+file_zclj[1]+'粘贴到'+paths+'有包含关系，存在无限循环复制风险！',4000);return;}
        var selRows = $("#tb_departments").bootstrapTable("getData",{useCurrentPage:true});
        var arrs = '';
        $.each(selRows,function(i) {
            var sfcz=file_copy[1].includes(this.name);
            if(sfcz==true){
                if(this.type=='dir'){var filetype='文件夹';}else{var filetype='文件';}
                var values=this.size/1024;
                if(values>1024){
                    values=values/1024;
                    num = values.toFixed(2)+'MB';
                }else{
                    num = values.toFixed(2)+'KB';
                }
                arrs+='<tr><th scope="row">'+filetype+'</th><td>'+this.name+'</td><td>'+num+'</td><td>'+getTimes(this.mtime)+'</td></tr>';
            }
        });

        if(arrs!=''){
            $.confirm({
                title: '即将覆盖以下文件',
                content: '该目录以下文件与即将粘贴文件的文件名相同！<br/>是否确认覆盖以下文件？<table class="table table-striped table-dark"><thead><tr><th>文件类型</th><th>文件名称</th><th>文件大小</th><th>最后一次修改时间</th></tr></thead><tbody>'+arrs+'</tbody></table><br/>',
                icon: 'mdi mdi-comment-question',
                animation: 'scale',
                closeAnimation: 'scale',
                opacity: 0.5,
                type:'orange',
                buttons: {
                    'confirm': {
                        text: '确认覆盖',
                        btnClass: 'btn-blue',
                        action: function() {
                            msloading('正在粘贴文件中，请稍后...');
                            let data = {};
                            data["gn"]="filecp";
                            data["yfile"]=file_copy[1];
                            data["ypath"]=file_copy[0];
                            data["xpath"]=paths;
                            data["type"]=file_copy[2];     //1为复制，2为剪切
                            $.post('./ajax.php', data, function (date) {
                                var jsoe= JSON.parse(date);

                                file_copy=false;
                                document.getElementById("btn_filepst").style.display="none";
                                msalert(jsoe.qk,jsoe.code,4000);
                                $("#tb_departments").bootstrapTable('refreshOptions',{pageNumber:1});		//刷新表格
                                msloadingde();  // 隐藏
                            })
                        }
                    },
                    '取消': function() {}
                }
            });
        }else{
            msloading('正在粘贴文件中，请稍后...');
            let data = {};
            data["gn"]="filecp";
            data["yfile"]=file_copy[1];
            data["ypath"]=file_copy[0];
            data["xpath"]=paths;
            data["type"]=file_copy[2];     //1为复制，2为剪切
            $.post('./ajax.php', data, function (date) {
                var jsoe= JSON.parse(date);

                file_copy=false;
                document.getElementById("btn_filepst").style.display="none";
                msalert(jsoe.qk,jsoe.code,4000);
                $("#tb_departments").bootstrapTable('refreshOptions',{pageNumber:1});		//刷新表格
                msloadingde();  // 隐藏
            })
        }
    }

    function copyfile(row,vs=1,tp=1){        //row为文件名称，vs为单/多文件，tp为复制/剪切
        if(vs==1){
            file_copy=[paths,[row.name],tp];
        }else{
            var xzdata=hqxzh();
            if(xzdata==false){return;}
            file_copy=[paths,xzdata,tp];
        }
        console.log(file_copy);
        document.getElementById("btn_filepst").style.display="inline-block";
        if(tp==1){
            msalert(2,'复制完成！请到其他目录下点击顶部粘贴按钮进行粘贴！',4000);
        }else{
            msalert(2,'剪切完成！请到其他目录下点击顶部粘贴按钮进行粘贴！',4000);
        }
    }

    //导入SQL文件到数据库中
    function drsqlfile(row) {
        if(row.name==null){
            msalert(3,'导入文件不存在！',2000);
        }
        else
        {
            $.confirm({
                title: '注意',
                content: '你确定要导入数据库文件吗？导入的数据如有相同则将会在导入后覆盖原数据库内数据！导入后数据不可恢复！！！',
                icon: 'mdi mdi-comment-question',
                animation: 'scale',
                closeAnimation: 'scale',
                opacity: 0.5,
                buttons: {
                    'confirm': {
                        text: '确认导入',
                        btnClass: 'btn-blue',
                        action: function() {
                            msloading('正在导入中，请稍后...');  // 加载显示
                            let data = {};
                            data["gn"]="sqldr";
                            data["path"]=paths;
                            data["filename"]=row.name;
                            $.post('./ajax.php', data, function (date) {
                                var jsoe= JSON.parse(date);
                                var qk= jsoe.code

                                if(qk=='导入数据库成功!'){
                                    msalert(1,qk, 4000);
                                    msloadingde();  // 隐藏
                                }else{
                                    msalert(4,qk, 4000);
                                    msloadingde();  // 隐藏
                                }
                            })
                        }
                    },
                    '取消': function() {}
                }
            });
        }
    }

    //解压缩文件
    function jysfile(){
        var xjy=jywjyd.value;
        var xjl=jyd.value;
        var xjm=jymm.value;
        var jbm=bm.value;
        if(xjy==null || xjl==null || jbm==null){
            msalert(3,'除密码外其余表单请填写或选择完整！',3000);
        }
        else
        {
            msloading('正在解压中，请稍后...');  // 加载显示
            let data = {};
            data["gn"]="ftpjy";
            data["jywj"]=xjy;
            data["jyd"]=xjl;
            data["jymm"]=xjm;
            data["wjbm"]=jbm;
            $.post('./ajax.php', data, function (date) {
                var jsoe= JSON.parse(date);
                var qk= jsoe.code

                if(qk=='解压成功'){
                    msalert(2,'解压请求已提交！正在解压中~~~',2000,'#zipjys');
                    var t = 2;      //停留在页面的时间
                    setTimeout(function() {
                            $("#tb_departments").bootstrapTable('refreshOptions',{pageNumber:1});		//刷新表格
                            msalert(1,'解压成功！',2000);
                            $('#zipjys').modal('hide');		//关闭弹窗
                            msloadingde();  // 隐藏
                        },
                        t*1000);
                }else{
                    msalert(4,'解压失败！',2500,'#zipjys');
                    msloadingde();  // 隐藏
                }
            })
        }
    }


    function setname(row){
        if(row.type=='dir'){
            var dirfile_name_dhk='文件夹';
            var dirfile_name_title='重命名文件夹-'+row.name;
        }else{
            var dirfile_name_dhk='文件';
            var dirfile_name_title='重命名文件-'+row.name;
        }
        $.confirm({
            title: dirfile_name_title,
            content: '<div class="form-group p-1 mb-0">' +
                '  <label class="control-label">此'+dirfile_name_dhk+'的新名称</label>' +
                '  <input autofocus="" type="text" id="input-name" placeholder="请输入此'+dirfile_name_dhk+'的新名称" class="form-control" value="'+row.name+'">' +
                '</div>'+
                '<script>var inputvals=$("#input-name").val();$("#input-name").val("").focus().val(inputvals);//定位光标到末尾',
            buttons: {
                sayMyName: {
                    text: '确定',
                    btnClass: 'btn-info',
                    action: function() {
                        var input = this.$content.find('input#input-name');
                        if (!$.trim(input.val())) {
                            $.alert({
                                title: "提示",
                                content: "名称字段不能为空！",
                                type: 'red'
                            });
                            return false;
                        } else {
                            msloading('正在修改文件名称...');  // 加载显示
                            let data = {};
                            data["gn"]="setname";
                            data["lj"]=paths;
                            data["wjjm"]=row.name;
                            data["wjmc"]=input.val();
                            $.post('./ajax.php', data, function (date) {
                                var jsoe= JSON.parse(date);
                                var qk= jsoe.code
                                if(qk=='重命名成功!'){
                                    msalert(1,qk,2000);
                                    $("#tb_departments").bootstrapTable('refreshOptions',{pageNumber:1});		//刷新表格
                                    msloadingde();  // 隐藏
                                }else{
                                    msalert(4,qk,4000);
                                    msloadingde();  // 隐藏
                                }
                            })
                        }
                    }
                },
                '取消': function() {}
            }
        });
    }

    function xzdelfile() {
        var arr=hqxzh();
        if(arr==false){msloadingde();return;}
        $.confirm({
            title: '删除选中的文件',
            content: '你确定要删除该您选中的文件吗？',
            autoClose: 'cancelAction|10000',
            escapeKey: 'cancelAction',
            icon: 'mdi mdi-alert',
            type: 'dark',
            buttons: {
                confirm: {
                    btnClass: 'btn-red',
                    text: '确定删除',
                    action: function() {
                        msloading('正在删除中，请稍后...');  // 加载显示
                        let data = {};
                        data["gn"]="ftpscxz";
                        data["idsz"]=arr;
                        data["path"]=paths;
                        $.post('./ajax.php', data, function (date) {
                            var jsoe= JSON.parse(date);
                            var qk= jsoe.code;

                            if(qk=='删除成功'){
                                msalert(1,'删除成功！',2000);
                                $("#tb_departments").bootstrapTable('refreshOptions',{pageNumber:1});		//刷新表格
                                msloadingde();  // 隐藏
                            }else{
                                msloadingde();  // 隐藏
                                msalert(4,qk,4000);
                            }
                        })
                    }
                },
                cancelAction: {
                    text: '取消',
                    action: function() {
                    }
                }
            }
        });
    }

    function delfile(row){
        if(row.name==null){
            msalert(3,'未选择要删除的文件！',2000);
            return;
        }
        if(row.type=='dir'){
            var dirfile_name_dhk='文件夹';
        }else{
            var dirfile_name_dhk='文件';
        }

        $.confirm({
            title: '删除'+dirfile_name_dhk+row.name,
            content: '你确定要删除该'+dirfile_name_dhk+'['+paths+row.name+']吗？',
            autoClose: 'cancelAction|10000',
            escapeKey: 'cancelAction',
            icon: 'mdi mdi-alert',
            type: 'dark',
            buttons: {
                confirm: {
                    btnClass: 'btn-red',
                    text: '确定删除',
                    action: function() {
                        msloading('正在删除中，请稍后...');  // 加载显示
                        let data = {};
                        data["gn"]="ftpsc";
                        data["lx"]=row.type;
                        data["path"]=paths;
                        data["name"]=row.name;
                        $.post('./ajax.php', data, function (date) {
                            var jsoe= JSON.parse(date);
                            var qk= jsoe.code

                            if(qk=='删除成功'){
                                msalert(1,'删除成功！',2000);
                                $("#tb_departments").bootstrapTable('refreshOptions',{pageNumber:1});		//刷新表格
                                msloadingde();  // 隐藏
                            }else{
                                msalert(4,qk,4000);
                                msloadingde();  // 隐藏
                            }
                        })
                    }
                },
                cancelAction: {
                    text: '取消',
                    action: function() {
                    }
                }
            }
        });
    }

    function setwj(modalfi=true){
        var nr=wjnr.value;
        if(path_bj_name==""){
            msalert(3,'被编辑文件以及所在目录不能为空！请刷新页面重试',4000);
        }
        else
        {
            msloading('正在修改中，请稍后...');  // 加载显示
            let data = {};
            data["gn"]="setwj";
            data["wj"]=path_bj_name;
            data["nr"]=CodeMirrorEditor.getValue();
            $.post('./ajax.php', data, function (date) {
                var jsoe= JSON.parse(date);
                var qk= jsoe.code

                if(modalfi){
                    msalert(1,qk,2000);
                    $('#setwj').modal('hide');		//关闭弹窗
                }else{
                    msalert(1,qk,1000,'#setwj');
                }
                msloadingde();  // 隐藏

            })
        }}

    // 操作方法 - 编辑
    function editfile(row)
    {
        msloading('正在获取文件内容，请稍后...');  // 加载显示
        document.getElementById("wjnr").value = '';
        let data = {};
        data["gn"]="hqwj";
        data["wj"]=paths+row.name;
        $.post('./ajax.php', data, function (date) {

            document.getElementById("setfiletitle").innerHTML="修改文件内容["+row.name+"]";
            $('#setwj').modal();		//弹出弹窗
            $('.modal-fullscreen-btn').closest('.modal').toggleClass('modal-fullscreen',true);       //最大化模态框
            msalert(2,'文件内容获取成功！',1000,'#setwj');
            CodeMirrorEditor.setValue(date);
            CodeMirrorEditor.setOption("mode", filecomode(row.name));        //切换渲染的文件类型
            setTimeout(() => {
                CodeMirrorEditor.refresh();
            },200);
            path_bj_name=paths+row.name;

            msloadingde();  // 隐藏
        })

        $('#setwj').on('hidden.bs.modal', function (e) {        //监听模态框关闭事件
            path_bj_name='';
            CodeMirrorEditor.setValue('');
            CodeMirrorEditor.refresh();
        });
    }

    function wjwjcg(){
        var ml=xjlja.value;
        var nameb=namea.value;
        if(nameb==""){
            msalert(3,'文件名不能为空',2000,'#xjwj');
        }
        else
        {
            msloading('正常新建中...');  // 加载显示
            let data = {};
            data["gn"]="xjwj";
            data["ml"]=ml;
            data["wjname"]=nameb;
            $.post('./ajax.php', data, function (date) {
                var jsoe= JSON.parse(date);
                var qk= jsoe.code

                if(qk=='文件创建成功!'){
                    msalert(1,qk,2000);
                    $("#tb_departments").bootstrapTable('refreshOptions',{pageNumber:1});		//刷新表格
                    document.getElementById("namea").value='';
                    $('#xjwj').modal('hide');		//关闭弹窗
                }else{
                    msalert(3,qk,2000,'#xjwj');
                }
                msloadingde();  // 隐藏

            })
        }}

    function wjwjjg(){
        var ml=xjljb.value;
        var nameb=namec.value;
        if(nameb==""){
            msalert(3,'文件夹名不能为空',2000,'#xjwjj');
        }
        else
        {
            msloading('正常新建中...');  // 加载显示
            let data = {};
            data["gn"]="xjwjj";
            data["ml"]=ml;
            data["wjname"]=nameb;
            $.post('./ajax.php', data, function (date) {
                var jsoe= JSON.parse(date);
                var qk= jsoe.code

                if(qk=='目录创建成功!'){
                    msalert(1,qk,2000);
                    $("#tb_departments").bootstrapTable('refreshOptions',{pageNumber:1});		//刷新表格
                    document.getElementById("namec").value='';
                    $('#xjwjj').modal('hide');		//关闭弹窗
                }else{
                    msalert(3,qk,2000,'#xjwjj');
                }
                msloadingde();  // 隐藏
            })
        }}




    function filesize(val,name){
        msloading('计算中，请稍后...');  // 加载显示
        let data = {};
        data["gn"]="hqdx";
        data["dw"]=paths+name;
        $.post('./ajax.php', data, function (date) {
            var jsoe= JSON.parse(date);
            var qk= jsoe.code/1024

            if(qk>1024){
                var jg=qk/1024;
                num = jg.toFixed(2)+'MB';
            }else{
                num = qk.toFixed(2)+'KB';
            }
            msalert(1,'计算成功',500);
            val.innerHTML=num;
            msloadingde();  // 隐藏
        })
    }

    //切换目录
    function mulus(data,vs){
        if(vs==1){
            paths+=data+'/';
            mldh();
        }else{
            paths=data;
            mldh();
        }
        $("#tb_departments").bootstrapTable('refreshOptions',{pageNumber:1});		//刷新表格
    }

    //更新目录导航
    function mldh(){
        var arr=paths.split('/');
        var ksr='';
        document.getElementById("mus").innerHTML='';
        var tmp = document.createElement("span");
        tmp.innerHTML= '/<a class="text-success" onclick="mulus('+"'"+'/'+"'"+',2);" href="#!">根目录</a>';
        document.getElementById("mus").appendChild(tmp);
        for (x in arr) {
            if(arr[x]!=''){
                ksr+=arr[x]+'/';
                var tmp = document.createElement("span");
                tmp.innerHTML= '/<a class="text-success" onclick="mulus('+"'"+'/'+ksr+"'"+',2);" href="#!">'+arr[x]+'</a>';
                document.getElementById("mus").appendChild(tmp);
            }
        }
    }

    //文件类型后缀对应图标
    function fileico(data){
        var arr=[];
        arr['zip']='mdi mdi-zip-box';
        arr['rar']='mdi mdi-zip-box';
        arr['7z']='mdi mdi-zip-box';
        arr['gz']='mdi mdi-zip-box';
        arr['js']='mdi-language-javascript';
        arr['php']='mdi mdi-language-php';
        arr['sql']='mdi mdi-database';
        arr['png']='mdi mdi-image';
        arr['jpg']='mdi mdi-image';
        arr['svg']='mdi mdi-image';
        arr['jpeg']='mdi mdi-image';
        arr['ico']='mdi mdi-image';
        arr['gif']='mdi-gif';
        arr['mp4']='mdi-file-video';
        arr['avi']='mdi-file-video';
        arr['wmv']='mdi-file-video';
        arr['mpg']='mdi-file-video';
        arr['mpeg']='mdi-file-video';
        arr['mov']='mdi-file-video';
        arr['mp3']='mdi-file-music';
        arr['wma']='mdi-file-music';
        arr['aac']='mdi-file-music';
        arr['mpc']='mdi-file-music';
        arr['css']='mdi mdi-language-css3';
        arr['htm']='mdi mdi-web';
        arr['xml']='mdi mdi-xml';
        arr['html']='mdi mdi-web';
        arr['py']='mdi-language-python';
        arr['go']='mdi-language-go';
        arr['java']='mdi-language-java';
        arr['docx']='mdi-file-word';
        arr['xls']='mdi-file-excel';

        var val=data.substr(data.lastIndexOf('.')+1).toLowerCase();
        var ds=arr[val];
        if(ds!=null){
            return ds;
        }else{
            return 'mdi-file-document';
        }
    }

    //部分类型文件显示指定按钮
    function filebtn(data){
        var arr=[];         //0为按钮，1为是否显示编辑按钮
        arr['zip']=['<a href="#!" class="btn btn-xs btn-default file-jys" title="解压缩文件" data-toggle="tooltip"><i class="mdi mdi-arrow-up-bold-box"></i></a>',false];
        arr['rar']=['<a href="#!" class="btn btn-xs btn-default file-jys" title="解压缩文件" data-toggle="tooltip"><i class="mdi mdi-arrow-up-bold-box"></i></a>',false];
        arr['7z']=['<a href="#!" class="btn btn-xs btn-default file-jys" title="解压缩文件" data-toggle="tooltip"><i class="mdi mdi-arrow-up-bold-box"></i></a>',false];
        arr['gz']=['<a href="#!" class="btn btn-xs btn-default file-jys" title="解压缩文件" data-toggle="tooltip"><i class="mdi mdi-arrow-up-bold-box"></i></a>',false];
        arr['sql']=['<a href="#!" class="btn btn-xs btn-default file-dr" title="导入SQL文件到数据库" data-toggle="tooltip"><i class="mdi mdi-import mdi-rotate-90"></i></a>',true];
        arr['png']=['<a href="#!" class="btn btn-xs btn-default file-img" title="预览图片" data-toggle="tooltip"><i class="mdi mdi-image-search-outline"></i></a>',false];      //图片类文件显示预览按钮
        arr['jpg']=['<a href="#!" class="btn btn-xs btn-default file-img" title="预览图片" data-toggle="tooltip"><i class="mdi mdi-image-search-outline"></i></a>',false];
        arr['svg']=['<a href="#!" class="btn btn-xs btn-default file-img" title="预览图片" data-toggle="tooltip"><i class="mdi mdi-image-search-outline"></i></a>',false];
        arr['jpeg']=['<a href="#!" class="btn btn-xs btn-default file-img" title="预览图片" data-toggle="tooltip"><i class="mdi mdi-image-search-outline"></i></a>',false];
        arr['gif']=['<a href="#!" class="btn btn-xs btn-default file-img" title="预览图片" data-toggle="tooltip"><i class="mdi mdi-image-search-outline"></i></a>',false];
        arr['ico']=['<a href="#!" class="btn btn-xs btn-default file-img" title="预览图片" data-toggle="tooltip"><i class="mdi mdi-image-search-outline"></i></a>',false];
        arr['mp4']=['',false];
        arr['avi']=['',false];
        arr['wmv']=['',false];
        arr['mpg']=['',false];
        arr['mpeg']=['',false];
        arr['mov']=['',false];
        arr['mp3']=['',false];
        arr['wma']=['',false];
        arr['aac']=['',false];
        arr['mpc']=['',false];

        var val=data.substr(data.lastIndexOf('.')+1).toLowerCase();
        var ds=arr[val];
        if(ds!=null){
            return ds;
        }else{
            return ['',true];      //无匹配记录时显示的
        }
    }

    //文件类型后缀对应编辑器使用的语言渲染类型
    function filecomode(data){
        var arr=[];
        arr['js']='javascript';
        arr['php']='application/x-httpd-php';
        arr['sql']='text/x-mysql';
        arr['css']='text/css';
        arr['htm']='text/html';
        arr['html']='text/html';
        arr['xml']='application/xml';

        var val=data.substr(data.lastIndexOf('.')+1).toLowerCase();
        var ds=arr[val];
        if(ds!=null){
            return ds;
        }else{
            return 'application/x-httpd-php';       //未知的文件类型使用PHP语言模式渲染
        }
    }

    function getTimes(timestamp) {
        var date = new Date(timestamp*1000);//时间戳为10位需*1000，时间戳为13位的话不需乘1000
        let Y = date.getFullYear(),
            M = (date.getMonth() + 1 < 10 ? '0' + (date.getMonth() + 1) : date.getMonth() + 1),
            D = (date.getDate() < 10 ? '0' + (date.getDate()) : date.getDate()),
            h = (date.getHours() < 10 ? '0' + (date.getHours()) : date.getHours()),
            m = (date.getMinutes() < 10 ? '0' + (date.getMinutes()) : date.getMinutes()),
            s = (date.getSeconds() < 10 ? '0' + (date.getSeconds()) : date.getSeconds());
        return Y + '-' + M + '-' + D + ' ' + h + ':' + m + ':' + s
    }

    //文件选择监听
    $(".custom-file-input").on("change",function(){
        if(this.files[0]==null){
            $(".custom-file-label").html('选择文件...');
        }else{
            $(".custom-file-label").html(this.files[0].name);
        }
    });
</script>
<!--文件上传-->
<script type="text/javascript" src="<?=mnbt_asset_url('js/upload.js')?>?1"></script>
</body>
</html>
