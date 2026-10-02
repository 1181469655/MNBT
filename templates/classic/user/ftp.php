<?php
/*
 * 在线文件管理（V1.86 重构版）
 * 通过节点宝塔面板 /files API 管理站点文件，后端见 user/api/file.php
 */
mnbt_theme_include('head');
?>
<!--文件操作按钮与选择框插件-->
<script type="text/javascript" src="<?=mnbt_asset_url('js/jquery-confirm/jquery-confirm.min.js')?>"></script>
<script type="text/javascript" src="<?=mnbt_asset_url('js/bootstrap-table/bootstrap-table.min.js')?>"></script>
<script type="text/javascript" src="<?=mnbt_asset_url('js/bootstrap-table/locale/bootstrap-table-zh-CN.min.js')?>"></script>
<!--代码编辑插件-->
<link href="<?=mnbt_asset_url('codemirror/lib/codemirror.css')?>" rel="stylesheet" type="text/css">
<link id="fm-editor-theme-css" href="<?=mnbt_asset_url('codemirror/theme/3024-night.css')?>" rel="stylesheet" type="text/css">
<link href="<?=mnbt_asset_url('codemirror/addon/display/fullscreen.css')?>" rel="stylesheet" type="text/css">
<link href="<?=mnbt_asset_url('codemirror/addon/dialog/dialog.css')?>" rel="stylesheet" type="text/css">
<link rel="stylesheet" href="<?=mnbt_asset_url('codemirror/addon/hint/show-hint.css')?>">

<div class="container-fluid p-t-15">
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <header class="card-header">
                    <div class="card-title">主机文件管理</div>
                </header>
                <div class="card-body">
                    <div class="mb-2">
                        <button type="button" class="btn btn-primary m-r-5" id="fm-btn-newfile"><i class="mdi mdi-file-plus-outline"></i> 新建文件</button>
                        <button type="button" class="btn btn-primary m-r-5" id="fm-btn-newdir"><i class="mdi mdi-folder-plus-outline"></i> 新建文件夹</button>
                        <button type="button" class="btn btn-primary m-r-5" data-toggle="modal" data-target="#fm-upload"><i class="mdi mdi-cloud-upload"></i> 上传文件</button>
                        <button type="button" class="btn btn-info m-r-5" id="fm-btn-compress" style="display:none;"><i class="mdi mdi-zip-box-outline"></i> 压缩选中</button>
                        <button type="button" class="btn btn-danger m-r-5" id="fm-btn-delete" style="display:none;"><i class="mdi mdi-window-close"></i> 删除选中</button>
                        <button type="button" class="btn btn-success m-r-5" id="fm-btn-paste" style="display:none;"><i class="mdi mdi-content-paste"></i> 粘贴</button>
                        <button type="button" class="btn btn-secondary m-r-5" data-toggle="modal" data-target="#fm-recycle"><i class="mdi mdi-delete-restore"></i> 回收站</button>
                        <button type="button" class="btn btn-default m-r-5" id="fm-btn-refresh"><i class="mdi mdi-refresh"></i> 刷新</button>
                        <span class="float-right mt-1" id="fm-breadcrumb"></span>
                    </div>
                    <table id="fm-table"></table>
                </div>
            </div>
        </div>
    </div>
</div>

<!--上传文件-->
<div class="modal fade" id="fm-upload" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title">上传文件</h6>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <p class="small text-muted">支持断点续传；同名文件上传时会提示覆盖或重命名。</p>
                <div id="fm-upload-pick" class="custom-file">
                    <input type="file" name="myfile" id="fm-upload-file" class="custom-file-input">
                    <label class="custom-file-label" for="fm-upload-file">选择文件...</label>
                </div>
                <div id="fm-upload-progress" style="display:none;">
                    <div class="progress mb-1">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" id="fm-upload-bar" style="width:0%;"></div>
                    </div>
                    <p class="small text-muted" id="fm-upload-text">准备上传...</p>
                    <button type="button" class="btn btn-sm btn-danger" id="fm-upload-cancel">取消上传</button>
                </div>
            </div>
            <div class="modal-footer" id="fm-upload-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
                <button type="button" class="btn btn-primary" id="fm-btn-upload-start">确认上传</button>
            </div>
        </div>
    </div>
</div>

<!--在线编辑-->
<div class="modal fade" id="fm-editor" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title" id="fm-editor-title">编辑文件</h6>
                <div class="col-md-3 mb-0 ml-2">
                    <select id="fm-editor-theme" class="custom-select custom-select-sm"></select>
                </div>
                <button type="button" class="modal-fullscreen-btn"><i class="mdi"></i></button>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <textarea id="fm-editor-content"></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
                <button type="button" class="btn btn-primary" id="fm-btn-save">保存（Ctrl+S）</button>
            </div>
        </div>
    </div>
</div>

<!--解压文件-->
<div class="modal fade" id="fm-unzip" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title">解压文件</h6>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <p class="small text-muted">支持 zip / tar.gz / rar（节点需安装 RAR 支持）压缩包；解压完成后若未立即显示文件，请稍候刷新。</p>
                <div class="form-group">
                    <label>需解压的文件</label>
                    <input type="text" class="form-control" id="fm-unzip-src" readonly>
                </div>
                <div class="form-group">
                    <label>解压到</label>
                    <input type="text" class="form-control" id="fm-unzip-dest" placeholder="请输入解压到的目录（站点相对路径）">
                </div>
                <div class="form-group">
                    <label>解压密码</label>
                    <input type="text" class="form-control" id="fm-unzip-pass" placeholder="没有密码请留空">
                </div>
                <div class="form-group">
                    <label>压缩包编码</label>
                    <select class="form-control" id="fm-unzip-coding">
                        <option value="UTF-8">UTF-8</option>
                        <option value="GBK">GBK</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
                <button type="button" class="btn btn-primary" id="fm-btn-unzip">确认解压</button>
            </div>
        </div>
    </div>
</div>

<!--修改权限-->
<div class="modal fade" id="fm-perm" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title">修改权限</h6>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>文件</label>
                    <input type="text" class="form-control" id="fm-perm-path" readonly>
                </div>
                <div class="form-group">
                    <label>权限值（八进制）</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="fm-perm-val" placeholder="如 644 / 755">
                        <div class="input-group-append">
                            <button type="button" class="btn btn-outline-secondary" data-perm="644">644</button>
                            <button type="button" class="btn btn-outline-secondary" data-perm="755">755</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
                <button type="button" class="btn btn-primary" id="fm-btn-perm">确认修改</button>
            </div>
        </div>
    </div>
</div>

<!--图片预览-->
<div class="modal fade" id="fm-image" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title" id="fm-image-title">图片预览</h6>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body text-center">
                <img src="" id="fm-image-src" class="img-fluid" alt="图片预览">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-dismiss="modal">关闭</button>
            </div>
        </div>
    </div>
</div>

<!--回收站-->
<div class="modal fade" id="fm-recycle" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title">文件回收站</h6>
                <div class="custom-control custom-switch ml-3">
                    <input type="checkbox" class="custom-control-input" id="fm-recycle-switch">
                    <label class="custom-control-label" for="fm-recycle-switch">回收站开关</label>
                </div>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <p class="small text-muted">删除的文件会先进入节点回收站；恢复将还原到原位置，删除则彻底清除、不可恢复。</p>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead><tr><th>文件名</th><th>原位置</th><th>大小</th><th>删除时间</th><th>操作</th></tr></thead>
                        <tbody id="fm-recycle-tbody"><tr><td colspan="5" class="text-center text-muted">加载中...</td></tr></tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" id="fm-btn-recycle-clear">清空回收站</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
            </div>
        </div>
    </div>
</div>

<!-- 引入CodeMirror -->
<script type="text/javascript" src="<?=mnbt_asset_url('codemirror/lib/codemirror.js')?>"></script>
<script src="<?=mnbt_asset_url('codemirror/mode/clike/clike.js')?>"></script>
<script type="text/javascript" src="<?=mnbt_asset_url('codemirror/mode/javascript/javascript.js')?>"></script>
<script type="text/javascript" src="<?=mnbt_asset_url('codemirror/mode/xml/xml.js')?>"></script>
<script type="text/javascript" src="<?=mnbt_asset_url('codemirror/mode/css/css.js')?>"></script>
<script type="text/javascript" src="<?=mnbt_asset_url('codemirror/mode/htmlmixed/htmlmixed.js')?>"></script>
<script type="text/javascript" src="<?=mnbt_asset_url('codemirror/mode/sql/sql.js')?>"></script>
<script type="text/javascript" src="<?=mnbt_asset_url('codemirror/mode/php/php.js')?>"></script>
<script type="text/javascript" src="<?=mnbt_asset_url('codemirror/addon/selection/active-line.js')?>"></script>
<script type="text/javascript" src="<?=mnbt_asset_url('codemirror/addon/edit/matchbrackets.js')?>"></script>
<script type="text/javascript" src="<?=mnbt_asset_url('codemirror/addon/display/fullscreen.js')?>"></script>
<script type="text/javascript" src="<?=mnbt_asset_url('codemirror/addon/display/autorefresh.js')?>"></script>
<script type="text/javascript" src="<?=mnbt_asset_url('codemirror/addon/edit/closebrackets.js')?>"></script>
<script type="text/javascript" src="<?=mnbt_asset_url('codemirror/addon/search/search.js')?>"></script>
<script type="text/javascript" src="<?=mnbt_asset_url('codemirror/addon/search/searchcursor.js')?>"></script>
<script type="text/javascript" src="<?=mnbt_asset_url('codemirror/addon/search/jump-to-line.js')?>"></script>
<script type="text/javascript" src="<?=mnbt_asset_url('codemirror/addon/dialog/dialog.js')?>"></script>
<script type="text/javascript" src="<?=mnbt_asset_url('codemirror/addon/hint/show-hint.js')?>"></script>

<!--分片上传器-->
<script type="text/javascript" src="<?=mnbt_asset_url('js/mnbt-uploader.js')?>"></script>

<script type="text/javascript">
(function () {
    'use strict';

    // ------------------------------------------------------------------
    //  状态
    // ------------------------------------------------------------------
    var FM = {
        path: '/',              // 当前目录（站点相对路径）
        clipboard: null,        // {path, names, type: copy|cut}
        editorPath: '',         // 正在编辑的文件
        editorName: '',
        unzipName: '',          // 正在解压的文件名
        permPath: '',           // 正在改权限的文件
        uploader: null,         // 当前上传器
        codeMirror: null
    };

    var EDITOR_THEMES = ['default', '3024-day', '3024-night', 'abbott', 'abcdef', 'ambiance', 'ayu-dark',
        'ayu-mirage', 'base16-dark', 'base16-light', 'bespin', 'blackboard', 'cobalt', 'colorforth',
        'darcula', 'dracula', 'duotone-dark', 'duotone-light', 'eclipse', 'erlang-dark', 'gruvbox-dark',
        'hopscotch', 'icecoder', 'idea', 'isotope', 'material', 'material-darker', 'material-ocean',
        'mbo', 'midnight', 'monokai', 'neat', 'neo', 'night', 'nord', 'oceanic-next', 'panda-syntax',
        'paraiso-dark', 'paraiso-light', 'pastel-on-dark', 'railscasts', 'rubyblue', 'seti', 'solarized',
        'the-matrix', 'tomorrow-night-bright', 'tomorrow-night-eighties', 'twilight', 'vibrant-ink',
        'xq-dark', 'xq-light', 'yeti', 'zenburn'];

    // ------------------------------------------------------------------
    //  工具
    // ------------------------------------------------------------------
    function esc(s) {
        return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }

    function fmtTime(ts) {
        var d = new Date(Number(ts) * 1000);
        if (isNaN(d.getTime())) return '-';
        function p(n) { return n < 10 ? '0' + n : n; }
        return d.getFullYear() + '-' + p(d.getMonth() + 1) + '-' + p(d.getDate()) +
            ' ' + p(d.getHours()) + ':' + p(d.getMinutes()) + ':' + p(d.getSeconds());
    }

    function ext(name) {
        var i = String(name).lastIndexOf('.');
        return i > -1 ? String(name).slice(i + 1).toLowerCase() : '';
    }

    function joinPath(name) {
        return FM.path === '/' ? '/' + name : FM.path + '/' + name;
    }

    function post(data, done, fail) {
        $.post('./ajax.php', data, function (res) {
            if (res && (res.qk === 1 || res.success === true)) {
                if (done) done(res);
            } else if (fail) {
                fail(res || {});
            } else {
                msalert(4, (res && res.code) || '操作失败', 4000);
            }
        }, 'json').fail(function () {
            if (fail) fail({});
            else msalert(4, '网络错误，请稍后重试', 4000);
        });
    }

    function nameList(rows) {
        return rows.map(function (r) { return r.name; });
    }

    // ------------------------------------------------------------------
    //  文件类型
    // ------------------------------------------------------------------
    var ICONS = {
        zip: 'mdi-zip-box', rar: 'mdi-zip-box', '7z': 'mdi-zip-box', gz: 'mdi-zip-box', tgz: 'mdi-zip-box',
        js: 'mdi-language-javascript', php: 'mdi-language-php', sql: 'mdi-database',
        png: 'mdi-image', jpg: 'mdi-image', jpeg: 'mdi-image', svg: 'mdi-image', ico: 'mdi-image',
        gif: 'mdi-image', webp: 'mdi-image', bmp: 'mdi-image',
        mp4: 'mdi-file-video', avi: 'mdi-file-video', wmv: 'mdi-file-video', mpg: 'mdi-file-video',
        mpeg: 'mdi-file-video', mov: 'mdi-file-video', mkv: 'mdi-file-video',
        mp3: 'mdi-file-music', wma: 'mdi-file-music', aac: 'mdi-file-music', flac: 'mdi-file-music',
        css: 'mdi-language-css3', htm: 'mdi-web', html: 'mdi-web', xml: 'mdi-xml', json: 'mdi-code-json',
        py: 'mdi-language-python', go: 'mdi-language-go', java: 'mdi-language-java',
        docx: 'mdi-file-word', doc: 'mdi-file-word', xls: 'mdi-file-excel', xlsx: 'mdi-file-excel',
        pdf: 'mdi-file-pdf', md: 'mdi-language-markdown', txt: 'mdi-file-document-outline', log: 'mdi-file-document-outline'
    };
    var ARCHIVES = ['zip', 'rar', 'gz', 'tgz'];
    var IMAGES = ['png', 'jpg', 'jpeg', 'gif', 'ico', 'svg', 'webp', 'bmp'];
    var MEDIA = ['mp4', 'avi', 'wmv', 'mpg', 'mpeg', 'mov', 'mkv', 'mp3', 'wma', 'aac', 'flac'];
    var CODE_MODES = {
        js: 'javascript', json: 'javascript', php: 'application/x-httpd-php',
        sql: 'text/x-mysql', css: 'text/css', htm: 'text/html', html: 'text/html',
        xml: 'application/xml', md: 'text/html'
    };

    function isArchive(e) { return ARCHIVES.indexOf(e) > -1; }
    function isImage(e) { return IMAGES.indexOf(e) > -1; }

    // ------------------------------------------------------------------
    //  目录导航
    // ------------------------------------------------------------------
    function renderBreadcrumb() {
        var html = '<a href="#!" class="text-success" data-path="/">根目录</a>';
        if (FM.path !== '/') {
            var acc = '';
            FM.path.split('/').forEach(function (seg) {
                if (!seg) return;
                acc += '/' + seg;
                html += ' / <a href="#!" class="text-success" data-path="' + esc(acc) + '">' + esc(seg) + '</a>';
            });
        }
        $('#fm-breadcrumb').html(html);
    }

    function go(path) {
        FM.path = path;
        renderBreadcrumb();
        $('#fm-table').bootstrapTable('refreshOptions', { pageNumber: 1 });
    }

    function refresh() {
        $('#fm-table').bootstrapTable('refresh');
    }

    $(document).on('click', '#fm-breadcrumb a', function () {
        go($(this).data('path') || '/');
    });

    // ------------------------------------------------------------------
    //  文件表格
    // ------------------------------------------------------------------
    function fmtSizeCell(v, row) {
        if (row.type === 'dir') {
            return '<a href="#!" class="text-success fm-sizecalc" data-name="' + esc(row.name) + '">计算</a>';
        }
        return window.mnbt_fmt_size(v);
    }

    function actionButtons(v, row) {
        function btn(cls, icon, title) {
            return '<a href="#!" class="btn btn-xs btn-default ' + cls + '" title="' + title + '" data-toggle="tooltip"><i class="mdi ' + icon + '"></i></a>';
        }
        var e = ext(row.name);
        var html = '';
        if (row.type === 'dir') {
            html += btn('act-rename', 'mdi-format-italic', '重命名文件夹');
            html += btn('act-copy', 'mdi-content-copy', '复制文件夹');
            html += btn('act-cut', 'mdi-content-cut', '剪切文件夹');
            html += btn('act-chmod', 'mdi-lock-open-outline', '修改权限');
            html += btn('act-del', 'mdi-window-close', '删除');
        } else {
            if (!isArchive(e) && !isImage(e) && MEDIA.indexOf(e) === -1) {
                html += btn('act-edit', 'mdi-pencil', '编辑文件');
            }
            html += btn('act-down', 'mdi-cloud-download-outline', '下载文件');
            html += btn('act-rename', 'mdi-format-italic', '重命名文件');
            html += btn('act-copy', 'mdi-content-copy', '复制文件');
            html += btn('act-cut', 'mdi-content-cut', '剪切文件');
            if (isArchive(e)) html += btn('act-unzip', 'mdi-arrow-up-bold-box', '解压文件');
            else if (e === 'sql') html += btn('act-sql', 'mdi-import mdi-rotate-90', '导入到数据库');
            else if (isImage(e)) html += btn('act-img', 'mdi-image-search-outline', '预览图片');
            html += btn('act-chmod', 'mdi-lock-open-outline', '修改权限');
            html += btn('act-del', 'mdi-window-close', '删除');
        }
        return html;
    }

    $('#fm-table').bootstrapTable({
        classes: 'table table-bordered table-hover table-striped',
        url: './ajax.php',
        method: 'post',
        contentType: 'application/x-www-form-urlencoded',
        dataType: 'json',
        showColumns: true,
        showRefresh: true,
        pagination: true,
        sidePagination: 'server',
        pageNumber: 1,
        pageSize: 100,
        pageList: [20, 50, 100, 200, 500, 1000],
        sortName: 'name',
        sortOrder: 'asc',
        queryParams: function (params) {
            return {
                gn: 'file_list',
                path: FM.path,
                limit: params.limit,
                page: (params.offset / params.limit) + 1,
                sort: params.sort || 'name',
                sortOrder: params.order || 'asc'
            };
        },
        responseHandler: function (res) {
            if (res && res.path && res.path !== FM.path) {
                // 服务端强制回落（站点目录异常时回到根目录），同步本地状态
                FM.path = res.path;
                renderBreadcrumb();
            }
            return res;
        },
        columns: [{
            field: 'state',
            checkbox: true
        }, {
            field: 'name',
            title: '文件名称',
            sortable: true,
            formatter: function (v, row) {
                if (row.type === 'dir') {
                    return '<a href="#!" class="text-success fm-enter" style="font-size:15px;">' +
                        '<i class="mdi mdi-24px mdi-folder-open" style="vertical-align:middle;"></i>' +
                        '<span>' + esc(v) + '</span></a>';
                }
                var icon = ICONS[ext(v)] || 'mdi-file-document';
                return '<a class="text-default"><i class="mdi mdi-18px ' + icon + '" style="vertical-align:middle;"></i>' + esc(v) + '</a>';
            }
        }, {
            field: 'size',
            title: '文件大小',
            sortable: true,
            formatter: fmtSizeCell
        }, {
            field: 'mtime',
            title: '修改时间',
            sortable: true,
            formatter: function (v) { return fmtTime(v); }
        }, {
            field: 'operate',
            title: '文件操作',
            formatter: actionButtons,
            events: {
                'click .fm-enter': function (e, v, row) { go(joinPath(row.name)); },
                'click .fm-sizecalc': function (e, v, row) { calcSize(e.currentTarget, row); },
                'click .act-edit': function (e, v, row) { openEditor(row); },
                'click .act-down': function (e, v, row) { downloadFile(row, function (url) { window.open(url, '_blank'); }); },
                'click .act-rename': function (e, v, row) { renameEntry(row); },
                'click .act-copy': function (e, v, row) { clip(row, 'copy'); },
                'click .act-cut': function (e, v, row) { clip(row, 'cut'); },
                'click .act-unzip': function (e, v, row) { openUnzip(row); },
                'click .act-sql': function (e, v, row) { importSql(row); },
                'click .act-img': function (e, v, row) { previewImage(row); },
                'click .act-chmod': function (e, v, row) { openPerm(row); },
                'click .act-del': function (e, v, row) { removeEntries([row]); }
            }
        }],
        onLoadSuccess: function () {
            $("[data-toggle='tooltip']").tooltip();
        }
    });

    // 选中行 → 显示批量按钮
    $('#fm-table').on('check.bs.table uncheck.bs.table check-all.bs.table uncheck-all.bs.table', function () {
        var has = $('#fm-table').bootstrapTable('getSelections').length > 0;
        $('#fm-btn-compress, #fm-btn-delete').toggle(has);
    });

    function calcSize(el, row) {
        msloading('正在计算大小...');
        post({ gn: 'file_size', path: joinPath(row.name) }, function (res) {
            msloadingde();
            el.innerHTML = window.mnbt_fmt_size(res.size || 0);
        }, function (res) {
            msloadingde();
            msalert(4, (res && res.code) || '计算失败', 4000);
        });
    }

    // ------------------------------------------------------------------
    //  新建 / 重命名 / 删除
    // ------------------------------------------------------------------
    function createEntry(type) {
        var label = type === 'dir' ? '文件夹' : '文件';
        $.confirm({
            title: '新建' + label,
            content: '<div class="form-group p-1 mb-0">' +
                '<label class="control-label">' + label + '名称（存放于 ' + esc(FM.path) + '）</label>' +
                '<input autofocus type="text" id="fm-input-name" placeholder="请输入' + label + '的名称" class="form-control">' +
                '</div>',
            buttons: {
                ok: {
                    text: '确认创建',
                    btnClass: 'btn-info',
                    action: function () {
                        var name = $.trim(this.$content.find('#fm-input-name').val());
                        if (!name) { msalert(3, '名称不能为空！', 3000); return false; }
                        msloading('正在创建' + label + '...');
                        post({ gn: 'file_create', path: FM.path, name: name, type: type }, function () {
                            msloadingde();
                            msalert(1, '创建成功！', 2000);
                            refresh();
                        }, function (res) {
                            msloadingde();
                            msalert(4, (res && res.code) || '创建失败', 4000);
                        });
                    }
                },
                cancel: { text: '取消' }
            }
        });
    }

    $('#fm-btn-newfile').on('click', function () { createEntry('file'); });
    $('#fm-btn-newdir').on('click', function () { createEntry('dir'); });

    function renameEntry(row) {
        var label = row.type === 'dir' ? '文件夹' : '文件';
        $.confirm({
            title: '重命名' + label + ' - ' + esc(row.name),
            content: '<div class="form-group p-1 mb-0">' +
                '<label class="control-label">' + label + '的新名称</label>' +
                '<input autofocus type="text" id="fm-input-rename" placeholder="请输入新名称" class="form-control" value="' + esc(row.name) + '">' +
                '</div>',
            buttons: {
                ok: {
                    text: '确定',
                    btnClass: 'btn-info',
                    action: function () {
                        var name = $.trim(this.$content.find('#fm-input-rename').val());
                        if (!name) { msalert(3, '名称不能为空！', 3000); return false; }
                        if (name === row.name) { msalert(3, '新名称与原名称相同！', 3000); return false; }
                        msloading('正在重命名...');
                        post({ gn: 'file_rename', path: FM.path, oldname: row.name, newname: name }, function () {
                            msloadingde();
                            msalert(1, '重命名成功！', 2000);
                            refresh();
                        }, function (res) {
                            msloadingde();
                            msalert(4, (res && res.code) || '重命名失败', 4000);
                        });
                    }
                },
                cancel: { text: '取消' }
            }
        });
    }

    function removeEntries(rows) {
        if (!rows || !rows.length) { msalert(3, '请至少选择一项！', 3000); return; }
        var names = nameList(rows);
        $.confirm({
            title: '删除确认',
            content: '确定要删除选中的 ' + names.length + ' 项吗？文件将进入节点回收站。',
            autoClose: 'cancel|10000',
            escapeKey: 'cancel',
            icon: 'mdi mdi-alert',
            type: 'dark',
            buttons: {
                ok: {
                    btnClass: 'btn-red',
                    text: '确定删除',
                    action: function () {
                        msloading('正在删除中...');
                        if (names.length === 1) {
                            post({
                                gn: 'file_delete',
                                path: FM.path,
                                name: names[0],
                                type: rows[0].type === 'dir' ? 'dir' : 'file'
                            }, afterRemove, removeFailed);
                        } else {
                            post({ gn: 'file_delete_batch', path: FM.path, names: names }, afterRemove, removeFailed);
                        }
                    }
                },
                cancel: { text: '取消' }
            }
        });
        function afterRemove() {
            msloadingde();
            msalert(1, '删除成功！', 2000);
            refresh();
        }
        function removeFailed(res) {
            msloadingde();
            msalert(4, (res && res.code) || '删除失败', 4000);
        }
    }

    $('#fm-btn-delete').on('click', function () {
        removeEntries($('#fm-table').bootstrapTable('getSelections'));
    });

    // ------------------------------------------------------------------
    //  复制 / 剪切 / 粘贴
    // ------------------------------------------------------------------
    function clip(rows, type) {
        var names = nameList(Array.isArray(rows) ? rows : [rows]);
        if (!names.length) { msalert(3, '请至少选择一项！', 3000); return; }
        FM.clipboard = { path: FM.path, names: names, type: type };
        $('#fm-btn-paste').show();
        msalert(2, (type === 'cut' ? '剪切' : '复制') + '完成！请到目标目录点击"粘贴"按钮。', 4000);
    }

    $('#fm-btn-paste').on('click', function () {
        if (!FM.clipboard) { msalert(3, '未选择要粘贴的文件！', 3000); return; }
        if (FM.clipboard.path === FM.path) { msalert(3, '原目录与粘贴目录不能相同！', 3000); return; }
        var selfLoop = false;
        FM.clipboard.names.forEach(function (n) {
            if (FM.path.slice(0, (FM.clipboard.path + n + '/').length) === FM.clipboard.path + n + '/') selfLoop = true;
        });
        if (selfLoop) { msalert(3, '粘贴目录与源目录存在包含关系，禁止粘贴！', 4000); return; }
        // 与当前目录同名文件时提示覆盖
        var conflicts = [];
        $.each($('#fm-table').bootstrapTable('getData', { useCurrentPage: true }), function () {
            if (FM.clipboard.names.indexOf(this.name) > -1) conflicts.push(this);
        });
        var doPaste = function () {
            msloading('正在粘贴文件...');
            post({
                gn: 'file_copy',
                ypath: FM.clipboard.path,
                xpath: FM.path,
                names: FM.clipboard.names,
                type: FM.clipboard.type
            }, function (res) {
                msloadingde();
                FM.clipboard = null;
                $('#fm-btn-paste').hide();
                msalert(1, (res && res.code) || '粘贴成功！', 4000);
                refresh();
            }, function (res) {
                msloadingde();
                if (FM.clipboard && (res && res.qk === 4 && /成功/.test(res.code || ''))) {
                    // 部分失败
                    FM.clipboard = null;
                    $('#fm-btn-paste').hide();
                }
                msalert(4, (res && res.code) || '粘贴失败', 5000);
            });
        };
        if (conflicts.length) {
            var list = conflicts.map(function (c) {
                return '<tr><td>' + (c.type === 'dir' ? '文件夹' : '文件') + '</td><td>' + esc(c.name) +
                    '</td><td>' + window.mnbt_fmt_size(c.size) + '</td><td>' + fmtTime(c.mtime) + '</td></tr>';
            }).join('');
            $.confirm({
                title: '即将覆盖以下文件',
                content: '该目录存在同名文件，是否确认覆盖？' +
                    '<table class="table table-striped"><thead><tr><th>类型</th><th>名称</th><th>大小</th><th>修改时间</th></tr></thead><tbody>' + list + '</tbody></table>',
                icon: 'mdi mdi-comment-question',
                type: 'orange',
                buttons: {
                    ok: { text: '确认覆盖', btnClass: 'btn-blue', action: doPaste },
                    cancel: { text: '取消' }
                }
            });
        } else {
            doPaste();
        }
    });

    // ------------------------------------------------------------------
    //  压缩 / 解压
    // ------------------------------------------------------------------
    $('#fm-btn-compress').on('click', function () {
        var names = nameList($('#fm-table').bootstrapTable('getSelections'));
        if (!names.length) { msalert(3, '请至少选择一行', 4000); return; }
        var rand = 'yuanma' + Math.floor(Math.random() * 2000);
        $.confirm({
            title: '压缩选中文件',
            content: '<div class="form-group p-1 mb-0">' +
                '<label class="control-label">压缩类型</label>' +
                '<select id="fm-input-type" class="custom-select">' +
                '<option value="zip">zip（通用格式）</option><option value="tar.gz">tar.gz（推荐）</option>' +
                '<option value="rar">rar（WinRAR 对中文兼容较好）</option><option value="7z">7z（压缩率高）</option>' +
                '</select>' +
                '<label class="control-label">压缩包存放路径</label>' +
                '<input type="text" id="fm-input-dest" class="form-control" value="' + esc(rand) + '.zip">' +
                '</div>',
            buttons: {
                ok: {
                    text: '确认压缩',
                    btnClass: 'btn-info',
                    action: function () {
                        var type = this.$content.find('#fm-input-type').val();
                        var dest = $.trim(this.$content.find('#fm-input-dest').val());
                        if (!dest) { msalert(3, '压缩包存放路径不能为空！', 3000); return false; }
                        msloading('正在压缩文件...');
                        post({ gn: 'file_compress', path: FM.path, names: names, type: type, dest: dest }, function () {
                            msloadingde();
                            msalert(1, '压缩成功！', 2000);
                            refresh();
                        }, function (res) {
                            msloadingde();
                            msalert(4, (res && res.code) || '压缩失败', 5000);
                        });
                    }
                },
                cancel: { text: '取消' }
            }
        });
    });

    function openUnzip(row) {
        FM.unzipName = row.name;
        $('#fm-unzip-src').val(joinPath(row.name));
        $('#fm-unzip-dest').val(FM.path);
        $('#fm-unzip-pass').val('');
        $('#fm-unzip-coding').val('UTF-8');
        $('#fm-unzip').modal();
    }

    $('#fm-btn-unzip').on('click', function () {
        var dest = $.trim($('#fm-unzip-dest').val());
        if (!dest) { msalert(3, '解压到的目录不能为空！', 3000, '#fm-unzip'); return; }
        msloading('正在解压中...');
        post({
            gn: 'file_unzip',
            path: FM.path,
            name: FM.unzipName,
            dest: dest,
            password: $('#fm-unzip-pass').val(),
            coding: $('#fm-unzip-coding').val()
        }, function () {
            msloadingde();
            msalert(2, '解压请求已提交！正在解压中…', 2000);
            $('#fm-unzip').modal('hide');
            setTimeout(refresh, 2000);
        }, function (res) {
            msloadingde();
            msalert(4, (res && res.code) || '解压失败', 4000, '#fm-unzip');
        });
    });

    // ------------------------------------------------------------------
    //  上传
    // ------------------------------------------------------------------
    $(".custom-file-input").on("change", function () {
        var label = this.files && this.files[0] ? this.files[0].name : '选择文件...';
        $(this).next('.custom-file-label').html(label);
    });

    function setUploadUI(uploading) {
        $('#fm-upload-pick, #fm-upload-footer').toggle(!uploading);
        $('#fm-upload-progress').toggle(uploading);
        if (!uploading) {
            $('#fm-upload-bar').css('width', '0%');
            $('#fm-upload-text').text('准备上传...');
            var input = $('#fm-upload-file');
            input.val('');
            input.next('.custom-file-label').html('选择文件...');
        }
    }

    $('#fm-btn-upload-start').on('click', function () {
        var input = document.getElementById('fm-upload-file');
        var file = input.files && input.files[0];
        if (!file) { msalert(3, '请选择要上传的文件！', 3000, '#fm-upload'); return; }
        var startUpload = function (name) {
            // 断点续传的分片临时文件与目标名一致，上传期间使用目标名
            var real = new File([file], name, { type: file.type });
            setUploadUI(true);
            FM.uploader = new MnbtUploader(real, FM.path, {
                onProgress: function (p, speed, done, rest) {
                    $('#fm-upload-bar').css('width', p + '%').text(p + '%');
                    $('#fm-upload-text').text('已上传 ' + done + '，速度 ' + speed + '，剩余 ' + rest);
                },
                onDone: function () {
                    setUploadUI(false);
                    $('#fm-upload').modal('hide');
                    msalert(1, '上传成功！', 3000);
                    refresh();
                },
                onError: function (msg) {
                    setUploadUI(false);
                    msalert(4, msg, 5000, '#fm-upload');
                }
            });
            FM.uploader.start();
        };
        // 同名文件冲突检测
        var exists = null;
        $.each($('#fm-table').bootstrapTable('getData', { useCurrentPage: true }), function () {
            if (this.name === file.name && this.type === 'file') exists = this;
        });
        if (exists) {
            $.confirm({
                title: '文件名冲突',
                content: '目录中已存在同名文件[' + esc(file.name) + ']（' + window.mnbt_fmt_size(exists.size) + '），请选择操作：' +
                    '<div class="mt-1"><label class="mr-3"><input type="radio" name="fm-up-mode" value="overwrite" checked> 覆盖文件</label>' +
                    '<label><input type="radio" name="fm-up-mode" value="rename"> 重命名上传</label></div>' +
                    '<input type="text" id="fm-input-upname" class="form-control mt-1" value="' + esc(file.name.replace(/(\.[^.]+)?$/, '-副本$1')) + '">',
                icon: 'mdi mdi-comment-question',
                type: 'orange',
                buttons: {
                    ok: {
                        text: '开始上传',
                        btnClass: 'btn-blue',
                        action: function () {
                            var mode = this.$content.find('input[name="fm-up-mode"]:checked').val();
                            if (mode === 'rename') {
                                var nn = $.trim(this.$content.find('#fm-input-upname').val());
                                if (!nn || nn.indexOf('/') !== -1) { msalert(3, '文件名不合法！', 3000); return false; }
                                startUpload(nn);
                            } else {
                                startUpload(file.name);
                            }
                        }
                    },
                    cancel: { text: '取消' }
                }
            });
        } else {
            startUpload(file.name);
        }
    });

    $('#fm-upload-cancel').on('click', function () {
        if (FM.uploader) FM.uploader.stop();
        setUploadUI(false);
        msalert(2, '已取消上传', 2000);
    });

    // ------------------------------------------------------------------
    //  下载 / 图片预览 / SQL导入
    // ------------------------------------------------------------------
    function downloadFile(row, cb) {
        msloading('正在获取文件下载链接...');
        post({ gn: 'file_download', path: FM.path, name: row.name }, function (res) {
            msloadingde();
            cb(res.url);
        }, function (res) {
            msloadingde();
            msalert(4, (res && res.code) || '获取下载链接失败', 4000);
        });
    }

    function previewImage(row) {
        downloadFile(row, function (url) {
            $('#fm-image-src').attr('src', url);
            $('#fm-image-title').text('图片预览[' + row.name + ']');
            $('#fm-image').modal();
        });
    }

    $('#fm-image').on('hidden.bs.modal', function () {
        $('#fm-image-src').attr('src', '');
    });

    function importSql(row) {
        $.confirm({
            title: '导入确认',
            content: '确定要将 [' + esc(row.name) + '] 导入数据库吗？如有相同数据将在导入后覆盖，导入后不可恢复！',
            icon: 'mdi mdi-comment-question',
            type: 'orange',
            buttons: {
                ok: {
                    text: '确认导入',
                    btnClass: 'btn-blue',
                    action: function () {
                        msloading('正在导入中...');
                        post({ gn: 'sqldr', path: FM.path, filename: row.name }, function (res) {
                            msloadingde();
                            msalert(1, (res && res.code) || '导入成功', 4000);
                        }, function (res) {
                            msloadingde();
                            msalert(4, (res && res.code) || '导入失败', 5000);
                        });
                    }
                },
                cancel: { text: '取消' }
            }
        });
    }

    // ------------------------------------------------------------------
    //  权限
    // ------------------------------------------------------------------
    function openPerm(row) {
        FM.permPath = joinPath(row.name);
        $('#fm-perm-path').val(FM.permPath);
        $('#fm-perm-val').val('');
        $('#fm-perm').modal();
        msloading('正在获取当前权限...');
        post({ gn: 'file_access', path: FM.permPath }, function (res) {
            msloadingde();
            $('#fm-perm-val').val(res.access || '');
        }, function (res) {
            msloadingde();
            msalert(3, (res && res.code) || '未能获取当前权限，可手动输入', 4000, '#fm-perm');
        });
    }

    $('#fm-perm [data-perm]').on('click', function () {
        $('#fm-perm-val').val($(this).data('perm'));
    });

    $('#fm-btn-perm').on('click', function () {
        var val = $.trim($('#fm-perm-val').val());
        if (!/^[0-7]{3,4}$/.test(val)) { msalert(3, '请输入如 644 / 755 的八进制权限值！', 3000, '#fm-perm'); return; }
        msloading('正在修改权限...');
        post({ gn: 'file_access_set', path: FM.permPath, access: val }, function () {
            msloadingde();
            msalert(1, '修改成功', 2000);
            $('#fm-perm').modal('hide');
        }, function (res) {
            msloadingde();
            msalert(4, (res && res.code) || '修改失败', 4000, '#fm-perm');
        });
    });

    // ------------------------------------------------------------------
    //  回收站
    // ------------------------------------------------------------------
    function recycleRow(item) {
        var rname = item.rname || String(item.path || '').split('/').pop();
        var name = item.filename || rname;
        var dir = item.dname || '';
        var size = isFinite(Number(item.size)) ? Number(item.size) : 0;
        var mtime = item.mtime ? fmtTime(item.mtime) : '-';
        return '<tr>' +
            '<td>' + esc(name) + '</td>' +
            '<td class="text-muted small">' + esc(dir) + '</td>' +
            '<td>' + window.mnbt_fmt_size(size) + '</td>' +
            '<td>' + mtime + '</td>' +
            '<td><button type="button" class="btn btn-xs btn-success mr-1" data-act="restore" data-rname="' + esc(rname) + '">恢复</button>' +
            '<button type="button" class="btn btn-xs btn-danger" data-act="delete" data-rname="' + esc(rname) + '">删除</button></td>' +
            '</tr>';
    }

    function loadRecycle() {
        post({ gn: 'recycle_list' }, function (res) {
            $('#fm-recycle-switch').prop('checked', !!res.status);
            var rows = (res.list || []).map(recycleRow);
            $('#fm-recycle-tbody').html(rows.length ? rows.join('') :
                '<tr><td colspan="5" class="text-center text-muted">回收站为空</td></tr>');
        }, function (res) {
            $('#fm-recycle-tbody').html('<tr><td colspan="5" class="text-center text-danger">' +
                esc((res && res.code) || '加载失败') + '</td></tr>');
        });
    }

    $('#fm-recycle').on('show.bs.modal', loadRecycle);

    $('#fm-recycle-switch').on('change', function () {
        msloading('正在切换回收站...');
        post({ gn: 'recycle_switch' }, function (res) {
            msloadingde();
            msalert(1, (res && res.code) || '设置成功', 3000);
            loadRecycle();
        }, function (res) {
            msloadingde();
            msalert(4, (res && res.code) || '操作失败', 4000);
            loadRecycle();
        });
    });

    $('#fm-recycle-tbody').on('click', '[data-act]', function () {
        var act = $(this).data('act');
        var rname = String($(this).data('rname') || '');
        if (!rname) return;
        if (act === 'delete') {
            $.confirm({
                title: '彻底删除',
                content: '确定要彻底删除 [' + esc(rname) + '] 吗？删除后不可恢复！',
                type: 'dark',
                buttons: {
                    ok: { btnClass: 'btn-red', text: '确定删除', action: function () { recycleOp('recycle_delete', rname); } },
                    cancel: { text: '取消' }
                }
            });
        } else {
            recycleOp('recycle_restore', rname);
        }
    });

    function recycleOp(gn, rname) {
        msloading('正在操作...');
        post({ gn: gn, rname: rname }, function () {
            msloadingde();
            msalert(1, '操作成功', 2000);
            loadRecycle();
        }, function (res) {
            msloadingde();
            msalert(4, (res && res.code) || '操作失败', 4000);
        });
    }

    $('#fm-btn-recycle-clear').on('click', function () {
        $.confirm({
            title: '清空回收站',
            content: '确定要清空节点回收站吗？所有回收站文件将被永久删除、不可恢复！',
            type: 'dark',
            icon: 'mdi mdi-alert',
            buttons: {
                ok: {
                    btnClass: 'btn-red',
                    text: '确定清空',
                    action: function () {
                        msloading('正在清空回收站...');
                        post({ gn: 'recycle_clear' }, function () {
                            msloadingde();
                            msalert(1, '回收站已清空', 2000);
                            loadRecycle();
                        }, function (res) {
                            msloadingde();
                            msalert(4, (res && res.code) || '操作失败', 4000);
                        });
                    }
                },
                cancel: { text: '取消' }
            }
        });
    });

    // ------------------------------------------------------------------
    //  在线编辑器
    // ------------------------------------------------------------------
    var themeSelect = document.getElementById('fm-editor-theme');
    var themeOpts = ['<option value="mnbt-current">切换编辑器主题</option>'];
    EDITOR_THEMES.forEach(function (t) { themeOpts.push('<option value="' + t + '">' + t + '</option>'); });
    themeSelect.innerHTML = themeOpts.join('');

    var savedTheme = null;
    try { savedTheme = localStorage.getItem('mnbt_editor_theme'); } catch (e) {}
    if (savedTheme && EDITOR_THEMES.indexOf(savedTheme) > -1 && savedTheme !== 'default') {
        document.getElementById('fm-editor-theme-css').href = "<?=mnbt_asset_url('codemirror/theme/')?>" + savedTheme + ".css";
    }

    $(themeSelect).on('change', function () {
        var t = this.value;
        if (t === 'mnbt-current') return;
        if (t !== 'default') {
            document.getElementById('fm-editor-theme-css').href = "<?=mnbt_asset_url('codemirror/theme/')?>" + t + ".css";
        }
        if (FM.codeMirror) FM.codeMirror.setOption('theme', t);
        try { localStorage.setItem('mnbt_editor_theme', t); } catch (e) {}
        this.options[0].selected = true;
        this.options[0].text = '当前主题：' + t;
    });

    function initCodeMirror() {
        var theme = (savedTheme && EDITOR_THEMES.indexOf(savedTheme) > -1) ? savedTheme : '3024-night';
        FM.codeMirror = CodeMirror.fromTextArea(document.getElementById('fm-editor-content'), {
            lineNumbers: true,
            tabSize: 4,
            indentUnit: 4,
            styleActiveLine: true,
            matchBrackets: true,
            mode: 'application/x-httpd-php',
            lineWrapping: true,
            theme: theme,
            autoRefresh: true,
            autoCloseBrackets: true,
            extraKeys: {
                'Tab': function (cm) {
                    if (cm.somethingSelected()) cm.indentSelection('add');
                    else cm.replaceSelection(Array(cm.getOption('indentUnit') + 1).join(' '), 'end', '+input');
                },
                'Ctrl-S': function () { saveFile(false); },
                'Ctrl-H': 'replace'
            }
        });
        FM.codeMirror.setSize('100%', '640px');
    }

    function openEditor(row) {
        FM.editorPath = joinPath(row.name);
        FM.editorName = row.name;
        msloading('正在获取文件内容...');
        post({ gn: 'file_read', path: FM.editorPath }, function (res) {
            msloadingde();
            $('#fm-editor-title').text('编辑文件 [' + row.name + ']');
            if (!FM.codeMirror) initCodeMirror();
            FM.codeMirror.setValue(res.content || '');
            FM.codeMirror.setOption('mode', CODE_MODES[ext(row.name)] || 'application/x-httpd-php');
            $('#fm-editor').modal();
            setTimeout(function () { FM.codeMirror.refresh(); }, 200);
        }, function (res) {
            msloadingde();
            msalert(4, (res && res.code) || '文件内容获取失败', 4000);
        });
    }

    function saveFile(close) {
        if (!FM.editorPath || !FM.codeMirror) return;
        msloading('正在保存文件...');
        post({ gn: 'file_save', path: FM.editorPath, content: FM.codeMirror.getValue() }, function () {
            msloadingde();
            msalert(1, '保存成功', 2000, '#fm-editor');
            if (close) $('#fm-editor').modal('hide');
        }, function (res) {
            msloadingde();
            msalert(4, (res && res.code) || '保存失败', 4000, '#fm-editor');
        });
    }

    $('#fm-btn-save').on('click', function () { saveFile(true); });

    $('#fm-editor').on('hidden.bs.modal', function () {
        FM.editorPath = '';
        FM.editorName = '';
        if (FM.codeMirror) {
            FM.codeMirror.setValue('');
            FM.codeMirror.refresh();
        }
    });

    $(document).on('click', '.modal-fullscreen-btn', function () {
        $(this).closest('.modal').toggleClass('modal-fullscreen');
        if (FM.codeMirror) setTimeout(function () { FM.codeMirror.refresh(); }, 300);
    });

    // ------------------------------------------------------------------
    //  其它
    // ------------------------------------------------------------------
    $('#fm-btn-refresh').on('click', refresh);
})();
</script>
</body>
</html>
