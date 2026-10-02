<?php mnbt_admin_include('head'); ?>
<?php
require_once ROOT . 'MPHX/node.function.php';
mnbt_node_ensure_tables($DB);
$bt_list=$DB->get_all_prepare("SELECT id,btdh,btip FROM MN_bt order by id desc limit 500");
if(!is_array($bt_list)) $bt_list=[];
$default_url=mnbt_node_default_base_url();

// 获取当前标签页
$default_tab = isset($_GET['tab']) ? $_GET['tab'] : 'overview';
?>

<script type="text/javascript" src="<?=mnbt_asset_url('js/jquery-confirm/jquery-confirm.min.js')?>"></script>
<script type="text/javascript" src="<?=mnbt_asset_url('js/bootstrap-table/bootstrap-table.min.js')?>"></script>
<script type="text/javascript" src="<?=mnbt_asset_url('js/bootstrap-table/locale/bootstrap-table-zh-CN.min.js')?>"></script>

<style>
.node-toolbar {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    align-items: center;
}
.node-toolbar .form-control {
    width: auto;
    min-width: 160px;
}
.node-code-box {
    font-family: Consolas, Monaco, monospace;
    min-height: 260px;
    resize: vertical;
}
.node-text-clip {
    display: inline-block;
    max-width: 360px;
    overflow: hidden;
    text-overflow: ellipsis;
    vertical-align: bottom;
    white-space: nowrap;
}
.node-mini-text {
    max-width: 220px;
}
.node-section-title {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
}
.node-section-title small {
    color: #6c757d;
    font-weight: normal;
}
/* Tabs 样式优化 */
.nav-tabs .nav-link {
    border: none;
    color: #6c757d;
    padding: 12px 20px;
    font-weight: 500;
}
.nav-tabs .nav-link.active {
    color: #007bff;
    border-bottom: 3px solid #007bff;
    background: transparent;
}
.nav-tabs .nav-link:hover:not(.active) {
    color: #007bff;
}
.tab-content {
    padding-top: 20px;
}
/* 扫描表格优化 */
.quick-action-btn {
    padding: 4px 8px;
    font-size: 11px;
}
@media (max-width: 767px) {
    .node-toolbar .form-control,
    .node-toolbar .btn {
        width: 100%;
    }
    .node-text-clip {
        max-width: 180px;
    }
}
</style>

<div class="container-fluid p-t-15">
  <div class="card">
    <header class="card-header">
      <div class="node-section-title">
        <div class="card-title">
          <i class="mdi mdi-server-network"></i> 节点管理
        </div>
        <div id="nodeToolbar" class="node-toolbar">
          <button type="button" class="btn btn-primary btn-sm" onclick="openAddNode()">
            <i class="mdi mdi-plus"></i> 新增节点
          </button>
          <button type="button" class="btn btn-default btn-sm" onclick="refreshAllData()">
            <i class="mdi mdi-refresh"></i> 刷新全部
          </button>
          <input type="text" id="nodeKeyword" class="form-control form-control-sm" placeholder="搜索节点名称/ID/IP">
          <select id="nodeStatus" class="form-control form-control-sm">
            <option value="">全部状态</option>
            <option value="enabled">已启用</option>
            <option value="disabled">已停用</option>
          </select>
        </div>
      </div>
    </header>

    <!-- Tabs 导航 -->
    <ul class="nav nav-tabs" id="nodeTabs">
      <li class="nav-item">
        <a class="nav-link <?= $default_tab == 'nodes' ? 'active' : '' ?>" data-toggle="tab" href="#tab-nodes">
          <i class="mdi mdi-server"></i> 节点列表
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link <?= $default_tab == 'tasks' ? 'active' : '' ?>" data-toggle="tab" href="#tab-tasks">
          <i class="mdi mdi-format-list-bulleted"></i> 任务列表
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link <?= $default_tab == 'scan' ? 'active' : '' ?>" data-toggle="tab" href="#tab-scan">
          <i class="mdi mdi-shield-search"></i> 违禁词扫描
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link <?= $default_tab == 'config' ? 'active' : '' ?>" data-toggle="tab" href="#tab-config">
          <i class="mdi mdi-cog"></i> 扫描配置
        </a>
      </li>
    </ul>

    <!-- Tabs 内容 -->
    <div class="card-body">
      <div class="tab-content">
        <!-- 节点列表标签 -->
        <div class="tab-pane fade <?= $default_tab == 'nodes' ? 'show active' : '' ?>" id="tab-nodes">
          <div class="node-toolbar mb-3">
            <button type="button" class="btn btn-primary btn-sm" onclick="batchTriggerScan()">
              <i class="mdi mdi-play"></i> 批量扫描
            </button>
            <button type="button" class="btn btn-default btn-sm" onclick="refreshNodes()">
              <i class="mdi mdi-refresh"></i> 刷新节点
            </button>
            <small class="text-muted">请先勾选节点列表左侧复选框，再点击批量扫描</small>
          </div>
          <table id="tb_nodes"></table>
        </div>

        <!-- 任务列表标签 -->
        <div class="tab-pane fade <?= $default_tab == 'tasks' ? 'show active' : '' ?>" id="tab-tasks">
          <div class="node-toolbar mb-3">
            <select id="taskNodeFilter" class="form-control form-control-sm">
              <option value="">全部节点</option>
            </select>
            <select id="taskStatusFilter" class="form-control form-control-sm">
              <option value="">全部状态</option>
              <option value="pending">等待中</option>
              <option value="running">执行中</option>
              <option value="success">成功</option>
              <option value="failed">失败</option>
            </select>
            <select id="taskTypeFilter" class="form-control form-control-sm">
              <option value="">全部类型</option>
              <option value="ping">Ping</option>
              <option value="forbidden_scan">违禁词扫描</option>
            </select>
          </div>
          <table id="tb_tasks"></table>
        </div>

        <!-- 违禁词扫描标签 -->
        <div class="tab-pane fade <?= $default_tab == 'scan' ? 'show active' : '' ?>" id="tab-scan">
          <div class="node-toolbar mb-3">
            <select id="scanNodeFilter" class="form-control form-control-sm">
              <option value="">全部节点</option>
            </select>
            <select id="scanTimeFilter" class="form-control form-control-sm">
              <option value="">全部时间</option>
              <option value="today">今天</option>
              <option value="yesterday">昨天</option>
              <option value="week">最近7天</option>
              <option value="month">最近30天</option>
            </select>
            <select id="scanStatusFilter" class="form-control form-control-sm">
              <option value="">全部状态</option>
              <option value="success">成功</option>
              <option value="failed">失败</option>
              <option value="has_matches">有命中</option>
            </select>
            <button type="button" class="btn btn-primary btn-sm" onclick="batchTriggerScan()">
              <i class="mdi mdi-play"></i> 批量扫描
            </button>
            <button type="button" class="btn btn-danger btn-sm" onclick="clearOldScans()">
              <i class="mdi mdi-delete-sweep"></i> 清理旧记录
            </button>
          </div>
          <table id="tb_scans"></table>
        </div>

        <!-- 扫描配置标签 -->
        <div class="tab-pane fade <?= $default_tab == 'config' ? 'show active' : '' ?>" id="tab-config">
          <div class="row">
            <div class="col-lg-10 col-xl-8">
              <div class="card">
              
                <div class="card-body">
                  <div class="form-group">
                    <label class="btn-block">违禁词扫描开关</label>
                    <div class="col-xs-6">
                      <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="wjscskg" <?php if($conf['wjsckg']=='true')echo 'checked';?> >
                        <label class="custom-control-label" for="wjscskg"></label>
                      </div>
                    </div>
                  </div>
                  <div class="form-group">
                    <label>违禁词内容</label>
                    <textarea id="wjscsnr" rows="10" class="form-control" placeholder="每行一个违禁词"><?php echo htmlspecialchars($conf['wjsccnr'] ?? ''); ?></textarea>
                    <small class="text-muted">每行一个关键词，支持普通匹配（暂不支持正则）</small>
                  </div>
                  <div class="form-group">
                    <label class="btn-block">只扫描变更文件</label>
                    <div class="col-xs-6">
                      <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="wjscskgbfx" <?php if(($conf['wjsckgqbfx'] ?? 'true')=='true')echo 'checked';?> >
                        <label class="custom-control-label" for="wjscskgbfx"></label>
                      </div>
                    </div>
                    <small class="text-muted">开启后仅扫描新增或修改的文件，大幅提升性能</small>
                  </div>
                  <div class="row">
                    <div class="col-md-6">
                      <div class="form-group">
                        <label>扫描目录</label>
                        <input type="text" id="wjscsml" value="<?php echo $conf['wjscml'] ?? '/www/wwwroot'; ?>" class="form-control" placeholder="默认 /www/wwwroot"/>
                        <small class="text-muted">Linux 宝塔面板建站目录</small>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-group">
                        <label>单文件最大大小（MB）</label>
                        <input type="number" id="wjscsdzmax" value="<?php echo ($conf['wjscdzmax'] ?? 5242880) / 1024 / 1024; ?>" class="form-control" min="1" max="50" placeholder="默认 5MB"/>
                      </div>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-md-6">
                      <div class="form-group">
                        <label>跳过目录</label>
                        <input type="text" id="wjscstqml" value="<?php echo $conf['wjstqml'] ?? '.git,node_modules,vendor,runtime,cache,logs'; ?>" class="form-control" placeholder="逗号分隔"/>
                        <small class="text-muted">默认：.git,node_modules,vendor,runtime,cache,logs</small>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-group">
                        <label>跳过后缀</label>
                        <input type="text" id="wjscstqhz" value="<?php echo $conf['wjstqhz'] ?? '.jpg,.png,.gif,.webp,.mp4,.zip,.rar,.7z,.pdf,.woff,.ttf'; ?>" class="form-control" placeholder="逗号分隔"/>
                        <small class="text-muted">默认：.jpg,.png,.gif,.webp,.mp4,.zip,.rar,.7z,.pdf,.woff,.ttf</small>
                      </div>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-md-6">
                      <div class="form-group">
                        <label>单次扫描最大命中数</label>
                        <input type="number" id="wjscsdhmax" value="<?php echo $conf['wjscdhmax'] ?? 1000; ?>" class="form-control" min="1" max="5000" placeholder="默认 1000"/>
                        <small class="text-muted">达到此数量后停止扫描</small>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-group">
                        <label>定时全量复扫开关</label>
                        <div class="custom-control custom-switch">
                          <input type="checkbox" class="custom-control-input" id="wjscsqzcskg" <?php if(($conf['wjscqzcskg'] ?? 'true')=='true')echo 'checked';?> >
                          <label class="custom-control-label" for="wjscsqzcskg"></label>
                        </div>
                        <small class="text-muted">每天凌晨进行一次全量扫描</small>
                      </div>
                    </div>
                  </div>
                  <div class="form-group">
                    <label>定时全量复扫时间（Cron表达式）</label>
                    <input type="text" id="wjscsqzcs" value="<?php echo $conf['wjscqzcs'] ?? '0 3 * * *'; ?>" class="form-control" placeholder="格式：分 时 日 月 周，默认 0 3 * * *"/>
                    <small class="text-muted">格式说明：分钟 小时 日期 月份 星期</small>
                  </div>
                  <hr>
                  <div class="alert alert-info">
                    <i class="mdi mdi-information"></i>
                    违禁词扫描功能需要配合宝塔节点插件使用。开启后插件会自动在本地建立文件指纹索引，仅上报命中结果。
                  </div>
                  <button class="btn btn-primary" type="button" onclick="saveScanConfig()">
                    <i class="mdi mdi-checkbox-marked-circle-outline"></i> 保存配置
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- 新增节点弹窗 -->
<div class="modal fade" id="addNodeModal" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h6 class="modal-title"><i class="mdi mdi-plus-circle"></i> 新增节点</h6>
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <label><i class="mdi mdi-server"></i> 绑定宝塔</label>
          <select id="addBtId" class="form-control">
            <option value="">请选择宝塔服务器</option>
            <?php foreach($bt_list as $bt){ ?>
            <option value="<?=htmlspecialchars($bt['id'], ENT_QUOTES, 'UTF-8')?>"><?=htmlspecialchars($bt['btdh'].' - '.$bt['btip'], ENT_QUOTES, 'UTF-8')?></option>
            <?php } ?>
          </select>
        </div>
        <div class="form-group">
          <label><i class="mdi mdi-tag"></i> 节点名称</label>
          <input type="text" id="addNodeName" class="form-control" placeholder="例如：国内节点 / 香港节点">
        </div>
        <div class="form-group">
          <label><i class="mdi mdi-identifier"></i> 节点ID</label>
          <input type="text" id="addNodeId" class="form-control" placeholder="留空自动生成">
        </div>
        <div class="form-group">
          <label><i class="mdi mdi-link"></i> MNBT访问地址</label>
          <input type="text" id="addMnbtUrl" class="form-control" value="<?=htmlspecialchars($default_url, ENT_QUOTES, 'UTF-8')?>">
        </div>
        <div class="form-group">
          <label><i class="mdi mdi-clock"></i> 插件轮询间隔（秒）</label>
          <input type="number" id="addIntervalSeconds" class="form-control" value="10" min="5" step="1">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
        <button type="button" class="btn btn-primary" onclick="saveAddNode()">
          <i class="mdi mdi-check"></i> 确认新增
        </button>
      </div>
    </div>
  </div>
</div>

<!-- 配置弹窗 -->
<div class="modal fade" id="configModal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h6 class="modal-title"><i class="mdi mdi-code-json"></i> 插件配置</h6>
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="configNodeId">
        <div class="row">
          <div class="col-md-8">
            <div class="form-group">
              <label>MNBT访问地址</label>
              <input type="text" id="configMnbtUrl" class="form-control" value="<?=htmlspecialchars($default_url, ENT_QUOTES, 'UTF-8')?>">
            </div>
          </div>
          <div class="col-md-4">
            <div class="form-group">
              <label>插件轮询间隔（秒）</label>
              <input type="number" id="configIntervalSeconds" class="form-control" value="10" min="5" step="1">
            </div>
          </div>
        </div>
        <div class="form-group">
          <label>配置 JSON</label>
          <textarea id="configJson" class="form-control node-code-box" readonly style="height:300px;"></textarea>
        </div>
        <div class="alert alert-info">
          <i class="mdi mdi-information"></i>
          将此配置复制到宝塔面板插件目录下的 config.json 文件中，然后启动插件即可。
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" onclick="loadNodeConfig()">
          <i class="mdi mdi-refresh"></i> 刷新
        </button>
        <button type="button" class="btn btn-primary" onclick="copyNodeConfig()">
          <i class="mdi mdi-content-copy"></i> 复制配置
        </button>
      </div>
    </div>
  </div>
</div>

<!-- 扫描弹窗 -->
<div class="modal fade" id="scanModal" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h6 class="modal-title" id="scanModalTitle"><i class="mdi mdi-shield-search"></i> 下发违禁词扫描</h6>
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="scanNodePk">
        <div class="form-group">
          <label><i class="mdi mdi-web"></i> 站点标识</label>
          <input type="text" id="scanSite" class="form-control" placeholder="例如：example.com">
        </div>
        <div class="form-group">
          <label><i class="mdi mdi-folder-open"></i> 扫描目录</label>
          <input type="text" id="scanRoot" class="form-control" value="/www/wwwroot">
        </div>
        <div class="form-group">
          <label><i class="mdi mdi-alert"></i> 违禁词</label>
          <textarea id="scanKeywords" rows="6" class="form-control" placeholder="每行一个关键词"></textarea>
        </div>
        <div class="row">
          <div class="col-md-6">
            <div class="form-group">
              <label><i class="mdi mdi-file-size"></i> 单文件最大MB</label>
              <input type="number" id="scanMaxFileSize" class="form-control" value="5" min="1" max="50">
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <label><i class="mdi mdi-counter"></i> 最多命中数</label>
              <input type="number" id="scanMaxMatches" class="form-control" value="1000" min="1" max="5000">
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
        <button type="button" class="btn btn-primary" onclick="submitForbiddenScan()">
          <i class="mdi mdi-play"></i> 下发任务
        </button>
      </div>
    </div>
  </div>
</div>

<!-- 命中详情弹窗 -->
<div class="modal fade" id="matchModal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-xl" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h6 class="modal-title" id="matchModalTitle"><i class="mdi mdi-file-search-outline"></i> 命中详情</h6>
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="matchTaskId">
        <table id="tb_matches"></table>
      </div>
    </div>
  </div>
</div>

<!-- 批量扫描弹窗 -->
<div class="modal fade" id="batchScanModal" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h6 class="modal-title"><i class="mdi mdi-play-multiple"></i> 批量扫描</h6>
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
      </div>
      <div class="modal-body">
        <div class="alert alert-info">
          <i class="mdi mdi-information"></i>
          将向所有选中的在线节点下发扫描任务
        </div>
        <div class="form-group">
          <label><i class="mdi mdi-folder-open"></i> 扫描目录</label>
          <input type="text" id="batchScanRoot" class="form-control" value="/www/wwwroot">
        </div>
        <div class="form-group">
          <label><i class="mdi mdi-alert"></i> 违禁词</label>
          <textarea id="batchScanKeywords" rows="4" class="form-control" placeholder="留空则使用全局配置的违禁词"></textarea>
        </div>
        <div class="row">
          <div class="col-md-6">
            <div class="form-group">
              <label>单文件最大MB</label>
              <input type="number" id="batchScanMaxSize" class="form-control" value="5" min="1" max="50">
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <label>最多命中数</label>
              <input type="number" id="batchScanMaxMatches" class="form-control" value="1000" min="1" max="5000">
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
        <button type="button" class="btn btn-primary" onclick="submitBatchScan()">
          <i class="mdi mdi-play"></i> 开始扫描
        </button>
      </div>
    </div>
  </div>
</div>

<script type="text/javascript">
var defaultMnbtUrl = <?=json_encode($default_url, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)?>;
var currentNodePk = 0;
var currentNodeLabel = '';
var autoRefreshTimer = null;

// 工具函数
function nodeEscape(value) {
    if (value === null || value === undefined) return '';
    return String(value).replace(/[&<>"']/g, function (ch) {
        return {'&':'&amp;', '<':'&lt;', '>':'&gt;', '"':'&quot;', "'":'&#39;'}[ch];
    });
}

function nodeBadge(text, type) {
    return '<span class="badge badge-' + type + '">' + nodeEscape(text) + '</span>';
}

function nodeStatusFormatter(value, row) {
    if (row.display_status === 'online') return '<i class="mdi mdi-circle text-success"></i> 在线';
    if (row.display_status === 'disabled') return '<i class="mdi mdi-circle text-secondary"></i> 停用';
    return '<i class="mdi mdi-circle text-danger"></i> 离线';
}

function nodeEnabledFormatter(value, row) {
    if (row.display_status === 'disabled') return nodeBadge('停用', 'secondary');
    return value === 'true' ? nodeBadge('启用', 'success') : nodeBadge('停用', 'secondary');
}

function taskStatusFormatter(value) {
    if (value === 'success') return nodeBadge('成功', 'success');
    if (value === 'failed') return nodeBadge('失败', 'danger');
    if (value === 'running') return '<span class="badge badge-info"><i class="mdi mdi-loading mdi-spin"></i> 执行中</span>';
    return nodeBadge('等待中', 'warning');
}

function textClipFormatter(value) {
    return '<span class="node-text-clip" title="' + nodeEscape(value) + '">' + nodeEscape(value || '-') + '</span>';
}

function miniTextClipFormatter(value) {
    return '<span class="node-text-clip node-mini-text" title="' + nodeEscape(value) + '">' + nodeEscape(value || '-') + '</span>';
}

function secretFormatter(value) {
    return '<span class="node-text-clip node-mini-text"><span class="node-secret-text">**********</span> ' +
        '<a href="#!" title="显示密钥" data-secret="' + nodeEscape(value || '') + '" onclick="toggleSecret(this);return false;">' +
        '<i class="mdi mdi-eye"></i></a></span>';
}

function toggleSecret(el) {
    var wrap = $(el).closest('span');
    var text = wrap.find('.node-secret-text');
    var icon = $(el).find('i');
    if (text.text() === '**********') {
        text.text($(el).data('secret') || '');
        icon.removeClass('mdi-eye').addClass('mdi-eye-off');
    } else {
        text.text('**********');
        icon.removeClass('mdi-eye-off').addClass('mdi-eye');
    }
}

// 刷新所有数据
function refreshAllData() {
    msloading('刷新中...');
    $('#tb_nodes').bootstrapTable('refresh', {silent: true});
    $('#tb_tasks').bootstrapTable('refresh', {silent: true});
    $('#tb_scans').bootstrapTable('refresh', {silent: true});
    loadStatistics();
    setTimeout(function() {
        msloadingde();
        msalert(1, '刷新完成', 1200);
    }, 800);
}

// 加载统计数据
function loadStatistics() {
    $.post('./ajax.php', {gn: 'nodestats'}, function(res) {
        if(res.success) {
            $('#statOnline').text(res.data.online || 0);
            $('#statOffline').text(res.data.offline || 0);
            $('#statTodayMatches').text(res.data.today_matches || 0);
            $('#statPendingTasks').text(res.data.pending_tasks || 0);
        }
    }, 'json');
}

// 刷新节点列表
function refreshNodes() {
    $('#tb_nodes').bootstrapTable('refreshOptions', {pageNumber: 1});
}

// 重置筛选
function resetNodeFilter() {
    $('#nodeKeyword').val('');
    $('#nodeStatus').val('');
    refreshNodes();
}

// 显示全部节点记录
function showAllNodeRecords() {
    currentNodePk = 0;
    currentNodeLabel = '';
    $('#taskNodeFilter').val('');
    $('#scanNodeFilter').val('');
    $('#tb_tasks').bootstrapTable('refreshOptions', {pageNumber: 1});
    $('#tb_scans').bootstrapTable('refreshOptions', {pageNumber: 1});
}

// 使用指定节点记录
function useNodeRecords(row) {
    currentNodePk = row.id;
    currentNodeLabel = row.node_name || row.node_id;
    $('#taskNodeFilter').val(row.id);
    $('#scanNodeFilter').val(row.id);
    $('#tb_tasks').bootstrapTable('refreshOptions', {pageNumber: 1});
    $('#tb_scans').bootstrapTable('refreshOptions', {pageNumber: 1});
    // 切换到任务标签
    $('a[href="#tab-tasks"]').tab('show');
}

// AJAX 请求封装
function ajaxNode(data, done, silent) {
    if (!silent) msloading('正在处理...');
    $.post('./ajax.php', data, function (res) {
        if (typeof res === 'string') {
            try { res = JSON.parse(res); } catch (e) { res = {success:false, msg:res}; }
        }
        if (res.success) {
            if (done) done(res);
        } else {
            msalert(4, res.msg || res.code || '操作失败', 3000);
        }
    }, 'json').fail(function () {
        msalert(4, '请求失败，请检查后台日志', 3000);
    }).always(function () {
        if (!silent) msloadingde();
    });
}

// 打开新增节点弹窗
function openAddNode() {
    $('#addBtId').val('');
    $('#addNodeName').val('');
    $('#addNodeId').val('');
    $('#addMnbtUrl').val(defaultMnbtUrl);
    $('#addIntervalSeconds').val(10);
    $('#addNodeModal').modal('show');
}

// 保存新增节点
function saveAddNode() {
    ajaxNode({
        gn: 'addnode',
        bt_id: $('#addBtId').val(),
        node_name: $('#addNodeName').val(),
        node_id: $('#addNodeId').val(),
        mnbt_url: $('#addMnbtUrl').val(),
        interval_seconds: $('#addIntervalSeconds').val()
    }, function (res) {
        $('#addNodeModal').modal('hide');
        $('#configNodeId').val(res.id || '');
        $('#configMnbtUrl').val($('#addMnbtUrl').val());
        $('#configIntervalSeconds').val($('#addIntervalSeconds').val());
        loadNodeConfig();
        $('#configModal').modal('show');
        refreshNodes();
        loadStatistics();
        msalert(1, '新增成功，请复制配置到插件目录', 3000);
    });
}

// 显示节点配置
function showNodeConfig(row) {
    $('#configNodeId').val(row.id);
    $('#configMnbtUrl').val(defaultMnbtUrl);
    $('#configIntervalSeconds').val('10');
    $('#configJson').val('');
    $('#configModal').modal('show');
    loadNodeConfig();
}

// 加载节点配置
function loadNodeConfig() {
    var nodePk = $('#configNodeId').val();
    if (!nodePk) return;
    ajaxNode({
        gn: 'nodeconfig',
        id: nodePk,
        mnbt_url: $('#configMnbtUrl').val(),
        interval_seconds: $('#configIntervalSeconds').val()
    }, function (res) {
        $('#configJson').val(res.config_json || '');
    }, true);
}

// 复制节点配置
function copyNodeConfig() {
    var target = document.getElementById('configJson');
    target.focus();
    target.select();
    document.execCommand('copy');
    msalert(1, '配置已复制', 2000, '#configModal');
}

// Ping 节点
function pingNode(row) {
    ajaxNode({gn: 'nodeping', id: row.id}, function (res) {
        msalert(1, res.msg || '任务已下发', 2000);
        refreshNodes();
    });
}

// 切换节点开关
function toggleNode(row) {
    var nextEnabled = row.enabled === 'true' ? 'false' : 'true';
    ajaxNode({gn: 'setnodestatus', id: row.id, enabled: nextEnabled}, function (res) {
        msalert(1, res.msg || '操作成功', 2000);
        refreshNodes();
        loadStatistics();
    });
}

// 删除节点
function deleteNode(row) {
    $.confirm({
        title: '删除节点',
        content: '确认删除节点 ' + nodeEscape(row.node_name || row.node_id) + ' 及其任务和扫描记录吗？',
        icon: 'mdi mdi-alert',
        type: 'red',
        buttons: {
            ok: {
                text: '确认删除',
                btnClass: 'btn-red',
                action: function () {
                    ajaxNode({gn: 'delnode', id: row.id}, function (res) {
                        msalert(1, res.msg || '删除成功', 2000);
                        refreshNodes();
                        loadStatistics();
                        $('#tb_tasks').bootstrapTable('refresh');
                        $('#tb_scans').bootstrapTable('refresh');
                    });
                }
            },
            cancel: { text: '取消' }
        }
    });
}

// 打开扫描弹窗
function openForbiddenScan(row) {
    $('#scanNodePk').val(row.id);
    $('#scanSite').val('');
    $('#scanRoot').val('/www/wwwroot');
    $('#scanKeywords').val('');
    $('#scanMaxFileSize').val('5');
    $('#scanMaxMatches').val('1000');
    $('#scanModalTitle').text('下发违禁词扫描 - ' + (row.node_name || row.node_id));
    $('#scanModal').modal('show');
}

// 提交扫描任务
function submitForbiddenScan() {
    ajaxNode({
        gn: 'nodeforbiddenscan',
        id: $('#scanNodePk').val(),
        site: $('#scanSite').val(),
        root: $('#scanRoot').val(),
        keywords: $('#scanKeywords').val(),
        max_file_size_mb: $('#scanMaxFileSize').val(),
        max_matches: $('#scanMaxMatches').val()
    }, function (res) {
        $('#scanModal').modal('hide');
        msalert(1, res.msg || '扫描任务已下发', 2000);
        $('#tb_tasks').bootstrapTable('refreshOptions', {pageNumber: 1});
        $('#tb_scans').bootstrapTable('refreshOptions', {pageNumber: 1});
        loadStatistics();
    });
}

// 显示命中详情
function showMatches(row) {
    $('#matchTaskId').val(row.task_id);
    $('#matchModalTitle').text('命中详情 - ' + row.task_id);
    $('#matchModal').modal('show');
    $('#tb_matches').bootstrapTable('refreshOptions', {pageNumber: 1});
}

// 批量扫描
function batchTriggerScan() {
    // 检查当前是否在节点列表标签页
    var currentTab = $('#nodeTabs .nav-link.active').attr('href');
    if (currentTab !== '#tab-nodes') {
        $('a[href="#tab-nodes"]').tab('show');
        // 等待标签切换完成后再检查选中
        setTimeout(function() {
            checkAndOpenBatchScan();
        }, 300);
        return;
    }

    checkAndOpenBatchScan();
}

function checkAndOpenBatchScan() {
    var selectedRows = $('#tb_nodes').bootstrapTable('getSelections');
    if (selectedRows.length === 0) {
        msalert(3, '请先勾选要扫描的节点（在表格左侧的复选框）', 3000);
        return;
    }

    var onlineNodes = selectedRows.filter(function(row) {
        return row.display_status === 'online' && row.enabled === 'true';
    });
    var offlineNodes = selectedRows.filter(function(row) {
        return row.display_status !== 'online';
    });
    var disabledNodes = selectedRows.filter(function(row) {
        return row.display_status === 'online' && row.enabled !== 'true';
    });

    if (onlineNodes.length === 0) {
        var errorMsg = '没有可扫描的节点！';
        if (offlineNodes.length > 0) {
            errorMsg += '\n离线节点：' + offlineNodes.map(function(r) { return r.node_name || r.node_id; }).join(', ');
        }
        if (disabledNodes.length > 0) {
            errorMsg += '\n停用节点：' + disabledNodes.map(function(r) { return r.node_name || r.node_id; }).join(', ');
        }
        msalert(3, errorMsg, 5000);
        return;
    }

    // 如果有部分节点不可用，提示用户
    if (onlineNodes.length < selectedRows.length) {
        var skippedCount = selectedRows.length - onlineNodes.length;
        if (skippedCount > 0) {
            msalert(1, '已过滤 ' + skippedCount + ' 个离线或停用节点，将对 ' + onlineNodes.length + ' 个节点下发扫描', 2000);
        }
    }

    $('#batchScanRoot').val('/www/wwwroot');
    $('#batchScanKeywords').val('');
    $('#batchScanMaxSize').val('5');
    $('#batchScanMaxMatches').val('1000');
    $('#batchScanModal').modal('show');
}

// 提交批量扫描
function submitBatchScan() {
    var selectedRows = $('#tb_nodes').bootstrapTable('getSelections');
    var onlineNodes = selectedRows.filter(function(row) {
        return row.display_status === 'online' && row.enabled === 'true';
    });

    var keywords = $('#batchScanKeywords').val();
    if (!keywords) {
        // 使用全局配置的违禁词
        $.post('./ajax.php', {gn: 'get_global_keywords'}, function(res) {
            if (res.success && res.data) {
                submitBatchScanWithNodes(onlineNodes, res.data);
            } else {
                msalert(3, '请填写违禁词或先在「违禁词扫描设置」中配置', 3000);
            }
        }, 'json');
        return;
    }

    submitBatchScanWithNodes(onlineNodes, keywords);
}

function submitBatchScanWithNodes(nodes, keywords) {
    var count = 0;
    var root = $('#batchScanRoot').val();
    var maxSize = $('#batchScanMaxSize').val() * 1024 * 1024;
    var maxMatches = $('#batchScanMaxMatches').val();

    nodes.forEach(function(node) {
        ajaxNode({
            gn: 'nodeforbiddenscan',
            id: node.id,
            site: '',
            root: root,
            keywords: keywords,
            max_file_size_mb: $('#batchScanMaxSize').val(),
            max_matches: maxMatches
        }, function() {
            count++;
            if (count === nodes.length) {
                $('#batchScanModal').modal('hide');
                msalert(1, '已向 ' + count + ' 个节点下发扫描任务', 2000);
                $('#tb_tasks').bootstrapTable('refreshOptions', {pageNumber: 1});
                $('#tb_scans').bootstrapTable('refreshOptions', {pageNumber: 1});
                loadStatistics();
            }
        }, true);
    });
}

// 清理旧扫描记录
function clearOldScans() {
    $.confirm({
        title: '清理旧记录',
        content: '' +
            '<div class="form-group">' +
            '<label>清理多少天前的扫描记录？</label>' +
            '<input type="number" id="clearScanDays" class="form-control" value="7" min="1" max="365">' +
            '<small class="text-muted">会同时清理扫描记录、命中详情、对应的扫描任务日志</small>' +
            '</div>',
        icon: 'mdi mdi-delete-sweep',
        type: 'orange',
        buttons: {
            ok: {
                text: '确认清理',
                btnClass: 'btn-orange',
                action: function () {
                    var days = $('#clearScanDays').val() || 7;
                    ajaxNode({gn: 'clearoldscans', days: days}, function(res) {
                        msalert(1, res.msg || '清理成功', 3000);
                        $('#tb_scans').bootstrapTable('refreshOptions', {pageNumber: 1});
                        $('#tb_tasks').bootstrapTable('refreshOptions', {pageNumber: 1});
                        loadStatistics();
                    });
                }
            },
            cancel: { text: '取消' }
        }
    });
}

// 操作按钮格式化
function nodeOperateFormatter(value, row) {
    var statusClass = row.display_status === 'online' ? 'text-success' : 'text-secondary';
    var toggleIcon = row.enabled === 'true' ? 'mdi-pause-circle-outline' : 'mdi-play-circle-outline';
    var toggleTitle = row.enabled === 'true' ? '停用' : '启用';

    return '' +
        '<div class="btn-group btn-group-sm">' +
        '<button class="btn btn-default quick-action-btn task-btn" title="最近任务" onclick="useNodeRecords({\'id\':\'' + row.id + '\',\'node_name\':\'' + nodeEscape(row.node_name || row.node_id) + '\'});"><i class="mdi mdi-format-list-bulleted"></i></button>' +
        '<button class="btn btn-default quick-action-btn config-btn" title="配置" onclick="showNodeConfig({\'id\':\'' + row.id + '\',\'node_name\':\'' + nodeEscape(row.node_name || row.node_id) + '\'});"><i class="mdi mdi-code-json"></i></button>' +
        '<button class="btn btn-default quick-action-btn ping-btn" title="Ping测试" onclick="pingNode({\'id\':\'' + row.id + '\',\'node_name\':\'' + nodeEscape(row.node_name || row.node_id) + '\'});"><i class="mdi mdi-access-point"></i></button>' +
        '<button class="btn btn-default quick-action-btn scan-btn" title="违禁词扫描" onclick="openForbiddenScan({\'id\':\'' + row.id + '\',\'node_name\':\'' + nodeEscape(row.node_name || row.node_id) + '\'});"><i class="mdi mdi-shield-search"></i></button>' +
        '<button class="btn btn-default quick-action-btn toggle-btn" title="' + toggleTitle + '" onclick="toggleNode({\'id\':\'' + row.id + '\',\'node_name\':\'' + nodeEscape(row.node_name || row.node_id) + '\',\'enabled\':\'' + row.enabled + '\'});"><i class="mdi ' + toggleIcon + '"></i></button>' +
        '<button class="btn btn-danger quick-action-btn delete-btn" title="删除" onclick="deleteNode({\'id\':\'' + row.id + '\',\'node_name\':\'' + nodeEscape(row.node_name || row.node_id) + '\'});"><i class="mdi mdi-window-close"></i></button>' +
        '</div>';
}

window.nodeOperateEvents = {
    // 事件已通过 onclick 内联绑定
};

// 扫描操作格式化
function scanOperateFormatter() {
    return '<button class="btn btn-xs btn-info" onclick="showMatches(row)">查看命中</button>';
}

window.scanOperateEvents = {
    'click .match-btn': function (event, value, row) { showMatches(row); }
};

// 节点列表表格
$('#tb_nodes').bootstrapTable({
    classes: 'table table-bordered table-hover table-striped',
    url: './ajax.php',
    method: 'post',
    contentType: 'application/x-www-form-urlencoded',
    dataType: 'json',
    uniqueId: 'id',
    idField: 'id',
    showRefresh: true,
    showColumns: true,
    showFullscreen: true,
    pagination: true,
    sidePagination: 'server',
    pageNumber: 1,
    pageSize: 10,
    pageList: [10, 25, 50],
    sortName: 'id',
    sortOrder: 'desc',
    queryParams: function (params) {
        return {
            gn: 'listnode',
            limit: params.limit,
            offset: params.offset,
            page: (params.offset / params.limit) + 1,
            sort: params.sort || 'id',
            sortOrder: params.order || 'desc',
            keyword: $('#nodeKeyword').val(),
            status: $('#nodeStatus').val()
        };
    },
    columns: [{
        checkbox: true
    }, {
        field: 'id',
        title: 'ID',
        sortable: true,
        width: 50
    }, {
        field: 'display_status',
        title: '状态',
        sortable: false,
        formatter: nodeStatusFormatter,
        width: 80
    }, {
        field: 'enabled',
        title: '开关',
        sortable: false,
        formatter: nodeEnabledFormatter,
        width: 80
    }, {
        field: 'node_name',
        title: '节点名称',
        formatter: textClipFormatter,
        width: 150
    }, {
        field: 'node_id',
        title: '节点ID',
        formatter: miniTextClipFormatter,
        width: 120
    }, {
        field: 'btdh',
        title: '绑定宝塔',
        formatter: function (value, row) {
            return nodeEscape(value || '-') + '<br><small class="text-muted">' + nodeEscape(row.btip || '') + '</small>';
        },
        width: 150
    }, {
        field: 'ip',
        title: '节点IP',
        formatter: miniTextClipFormatter,
        width: 120
    }, {
        field: 'version',
        title: '版本',
        formatter: miniTextClipFormatter,
        width: 80
    }, {
        field: 'last_heartbeat',
        title: '最后心跳',
        sortable: true,
        formatter: function (value) {
            if (!value) return '-';
            var now = new Date();
            var heartbeat = new Date(value);
            var diff = Math.floor((now - heartbeat) / 1000);
            if (diff < 60) return '<span class="text-success">' + value + '</span>';
            if (diff < 300) return '<span class="text-warning">' + value + '</span>';
            return '<span class="text-danger">' + value + '</span>';
        },
        width: 160
    }, {
        field: 'operate',
        title: '操作',
        formatter: nodeOperateFormatter,
        events: nodeOperateEvents,
        width: 180
    }],
    onLoadSuccess: function () {
        $('[data-toggle="tooltip"]').tooltip();
    },
    responseHandler: function(res) {
        loadStatistics();
        return res;
    }
});

// 任务列表表格
$('#tb_tasks').bootstrapTable({
    classes: 'table table-bordered table-hover table-striped',
    url: './ajax.php',
    method: 'post',
    contentType: 'application/x-www-form-urlencoded',
    dataType: 'json',
    pagination: true,
    sidePagination: 'server',
    pageNumber: 1,
    pageSize: 15,
    pageList: [15, 30, 50],
    sortName: 'id',
    sortOrder: 'desc',
    queryParams: function (params) {
        return {
            gn: 'listnodetask',
            node_pk: $('#taskNodeFilter').val(),
            status: $('#taskStatusFilter').val(),
            action: $('#taskTypeFilter').val(),
            limit: params.limit,
            offset: params.offset,
            page: (params.offset / params.limit) + 1,
            sort: params.sort || 'id',
            sortOrder: params.order || 'desc'
        };
    },
    columns: [{
        field: 'id',
        title: 'ID',
        sortable: true,
        width: 50
    }, {
        field: 'node_id',
        title: '节点ID',
        formatter: miniTextClipFormatter,
        width: 120
    }, {
        field: 'action',
        title: '任务类型',
        formatter: function (value) {
            if (value === 'ping') return '<span class="badge badge-secondary">Ping</span>';
            if (value === 'forbidden_scan') return '<span class="badge badge-warning">违禁词扫描</span>';
            return value;
        },
        width: 100
    }, {
        field: 'status',
        title: '状态',
        formatter: taskStatusFormatter,
        width: 80
    }, {
        field: 'error',
        title: '错误信息',
        formatter: miniTextClipFormatter,
        width: 200
    }, {
        field: 'created_at',
        title: '创建时间',
        sortable: true,
        formatter: miniTextClipFormatter,
        width: 160
    }, {
        field: 'finished_at',
        title: '完成时间',
        formatter: miniTextClipFormatter,
        width: 160
    }],
    onLoadSuccess: function () {
        $('[data-toggle="tooltip"]').tooltip();
    }
});

// 扫描结果表格
$('#tb_scans').bootstrapTable({
    classes: 'table table-bordered table-hover table-striped',
    url: './ajax.php',
    method: 'post',
    contentType: 'application/x-www-form-urlencoded',
    dataType: 'json',
    pagination: true,
    sidePagination: 'server',
    pageNumber: 1,
    pageSize: 15,
    pageList: [15, 30, 50, 100],
    sortName: 'id',
    sortOrder: 'desc',
    queryParams: function (params) {
        return {
            gn: 'listforbiddenscan',
            node_pk: $('#scanNodeFilter').val(),
            time_filter: $('#scanTimeFilter').val(),
            status_filter: $('#scanStatusFilter').val(),
            limit: params.limit,
            offset: params.offset,
            page: (params.offset / params.limit) + 1,
            sort: params.sort || 'id',
            sortOrder: params.order || 'desc'
        };
    },
    columns: [{
        field: 'id',
        title: 'ID',
        sortable: true,
        width: 50
    }, {
        field: 'node_id',
        title: '节点',
        formatter: miniTextClipFormatter,
        width: 100
    }, {
        field: 'site',
        title: '站点',
        formatter: miniTextClipFormatter,
        width: 120
    }, {
        field: 'status',
        title: '状态',
        formatter: taskStatusFormatter,
        width: 80
    }, {
        field: 'scanned_files',
        title: '文件数',
        sortable: true,
        width: 80
    }, {
        field: 'matches_count',
        title: '命中',
        sortable: true,
        formatter: function (value, row) {
            var count = Number(value) || 0;
            if (count === 0) return '<span class="text-muted">0</span>';
            return '<span class="text-danger font-weight-bold">' + count + '</span>';
        },
        width: 60
    }, {
        field: 'created_at',
        title: '扫描时间',
        sortable: true,
        formatter: miniTextClipFormatter,
        width: 160
    }, {
        field: 'operate',
        title: '操作',
        formatter: function (value, row) {
            if (row.matches_count > 0) {
                return '<button class="btn btn-xs btn-info" onclick="showMatches({\'task_id\':\'' + row.task_id + '\'});">查看命中</button>';
            }
            return '<span class="text-muted">无</span>';
        },
        width: 80
    }],
    onLoadSuccess: function () {
        $('[data-toggle="tooltip"]').tooltip();
    },
    responseHandler: function(res) {
        loadStatistics();
        return res;
    }
});



// 命中详情表格
$('#tb_matches').bootstrapTable({
    classes: 'table table-bordered table-hover table-striped',
    url: './ajax.php',
    method: 'post',
    contentType: 'application/x-www-form-urlencoded',
    dataType: 'json',
    pagination: true,
    sidePagination: 'server',
    pageNumber: 1,
    pageSize: 20,
    pageList: [20, 50, 100],
    queryParams: function (params) {
        return {
            gn: 'listforbiddenmatch',
            task_id: $('#matchTaskId').val(),
            limit: params.limit,
            offset: params.offset,
            page: (params.offset / params.limit) + 1
        };
    },
    columns: [{
        field: 'id',
        title: 'ID',
        width: 50
    }, {
        field: 'target',
        title: '文件路径',
        formatter: textClipFormatter,
        width: 300
    }, {
        field: 'line_no',
        title: '行号',
        width: 60
    }, {
        field: 'keyword',
        title: '关键词',
        formatter: function (value) {
            return '<span class="text-danger font-weight-bold">' + nodeEscape(value) + '</span>';
        },
        width: 100
    }, {
        field: 'excerpt',
        title: '内容片段',
        formatter: function (value) {
            return '<code class="text-muted">' + nodeEscape(value || '') + '</code>';
        }
    }]
});

// 筛选器事件
$('#nodeKeyword').on('keydown', function (event) {
    if (event.keyCode === 13) refreshNodes();
});
$('#nodeStatus').on('change', refreshNodes);
$('#taskNodeFilter, #taskStatusFilter, #taskTypeFilter').on('change', function() {
    $('#tb_tasks').bootstrapTable('refreshOptions', {pageNumber: 1});
});
$('#scanNodeFilter, #scanTimeFilter, #scanStatusFilter').on('change', function() {
    $('#tb_scans').bootstrapTable('refreshOptions', {pageNumber: 1});
});

// 自动刷新（每30秒）
function startAutoRefresh() {
    if (autoRefreshTimer) clearInterval(autoRefreshTimer);
    autoRefreshTimer = setInterval(function() {
        var activeTab = $('#nodeTabs .nav-link.active').attr('href');
        if (activeTab === '#tab-nodes' || activeTab === '#tab-scan') {
            loadStatistics();
        }
        if (activeTab === '#tab-nodes') {
            $('#tb_nodes').bootstrapTable('refresh', {silent: true});
        }
    }, 30000);
}

// 切换标签时刷新对应数据
$('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
    var target = $(e.target).attr('href');
    if (target === '#tab-nodes') {
        $('#tb_nodes').bootstrapTable('refreshOptions', {pageNumber: 1});
    } else if (target === '#tab-tasks') {
        $('#tb_tasks').bootstrapTable('refreshOptions', {pageNumber: 1});
    } else if (target === '#tab-scan') {
        $('#tb_scans').bootstrapTable('refreshOptions', {pageNumber: 1});
    }
});

// 保存扫描配置
function saveScanConfig() {
    msloading('正在保存...');
    $.post('./ajax.php', {
        gn: 'savescancfg',
        skg: document.getElementById('wjscskg').checked,
        snr: document.getElementById('wjscsnr').value,
        sgbfx: document.getElementById('wjscskgbfx').checked,
        sml: document.getElementById('wjscsml').value,
        stqml: document.getElementById('wjscstqml').value,
        stqhz: document.getElementById('wjscstqhz').value,
        sdzmax: document.getElementById('wjscsdzmax').value,
        sdhmax: document.getElementById('wjscsdhmax').value,
        sqzcskg: document.getElementById('wjscsqzcskg').checked,
        sqzcs: document.getElementById('wjscsqzcs').value
    }, function(res) {
        try { res = JSON.parse(res); } catch(e) {}
        msloadingde();
        if (res.code === '修改成功' || res.success) {
            msalert(1, res.code || res.msg || '保存成功', 2000);
        } else {
            msalert(4, res.code || res.msg || '保存失败', 3000);
        }
    }).fail(function() {
        msloadingde();
        msalert(4, '请求失败', 3000);
    });
}

// 页面加载完成
$(function() {
    startAutoRefresh();
    loadStatistics();
});
</script>

</body>
</html>