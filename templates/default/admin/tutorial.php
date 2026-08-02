<?php mnbt_admin_include('head'); ?>
<style>
    /* 所有样式均限制在 .staridc-mnbt-guide 作用域内 */
    .staridc-mnbt-guide {
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        line-height: 1.6;
        color: #333;
        max-width: 800px;
        margin: 0 auto;
        padding: 20px;
    }
    .staridc-mnbt-guide .sm-guide-title {
        margin-bottom: 15px;
        color: #2c3e50;
    }
    .staridc-mnbt-guide .sm-steps-list {
        padding-left: 20px;
    }
    .staridc-mnbt-guide .sm-steps-list li {
        margin-bottom: 10px;
    }
    .staridc-mnbt-guide .sm-params-table {
        width: 100%;
        border-collapse: collapse;
        margin: 15px 0;
        background-color: #fff;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    .staridc-mnbt-guide .sm-params-table th,
    .staridc-mnbt-guide .sm-params-table td {
        border: 1px solid #e0e0e0;
        padding: 12px 15px;
        text-align: left;
    }
    .staridc-mnbt-guide .sm-params-table th {
        background-color: #f8f9fa;
        font-weight: 600;
        color: #495057;
    }
    .staridc-mnbt-guide .sm-params-table tr:hover {
        background-color: #f1f1f1;
    }
    .staridc-mnbt-guide .sm-code {
        background-color: #f4f4f4;
        padding: 2px 5px;
        border-radius: 3px;
        font-family: Consolas, Monaco, monospace;
        font-size: 0.9em;
        color: #d63384;
    }
    .staridc-mnbt-guide .sm-notice {
        background-color: #fff3cd;
        border-left: 4px solid #ffc107;
        color: #856404;
        padding: 15px 20px;
        margin-top: 20px;
        border-radius: 0 4px 4px 0;
    }
    .staridc-mnbt-guide .sm-notice-title {
        display: block;
        margin-bottom: 5px;
    }
    code.mnbt-tmp-btdh:not(.mnbt-tmp-active):not(.mnbt-tmp-permanent-active),
    code.mnbt-tmp-ktmy:not(.mnbt-tmp-active):not(.mnbt-tmp-permanent-active),
    code.mnbt-tmp-dymy:not(.mnbt-tmp-active):not(.mnbt-tmp-permanent-active),
    .staridc-mnbt-guide code.sm-code.mnbt-tmp-btdh:not(.mnbt-tmp-active):not(.mnbt-tmp-permanent-active),
    .staridc-mnbt-guide code.sm-code.mnbt-tmp-ktmy:not(.mnbt-tmp-active):not(.mnbt-tmp-permanent-active),
    .staridc-mnbt-guide code.sm-code.mnbt-tmp-dymy:not(.mnbt-tmp-active):not(.mnbt-tmp-permanent-active) {
        color: #212529;
    }
</style>
<body>

<?php
$url=($_SERVER['SERVER_PORT'] == '443' ? 'https://' : 'http://').$_SERVER['HTTP_HOST'];
if($_GET['gn']=='' || !isset($_GET['gn'])){?>
<div class="container-fluid p-t-15">

<div class="row">
    <div class="col-lg-6">
      <div class="card">
        <header class="card-header"><div class="card-title">使用教程及监控</div></header>
        <div class="card-body">

          <div class="form-group">
            <label for="tutorialBtSelect">宝塔</label>
            <select class="form-control" id="tutorialBtSelect" onchange="onTutorialBtChange(this)">
              <option value="">选择宝塔</option>
            </select>
          </div>

          <ul class="nav nav-tabs nav-fill">
            <li class="nav-item">
              <a class="nav-link active" data-toggle="tab" href="#jinyong-fill" aria-selected="true">监控教程</a>
            </li>
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#!" aria-haspopup="true" aria-expanded="false">使用教程</a>
              <div class="dropdown-menu">
                <a class="dropdown-item" href="#gulong-fill" data-toggle="tab">添加宝塔教程</a>
                <a class="dropdown-item" href="#liangyusheng-fill" data-toggle="tab">添加主机教程</a>
              </div>
            </li>
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#!" aria-haspopup="true" aria-expanded="false">对接教程</a>
              <div class="dropdown-menu">
                <a class="dropdown-item" href="#djs-fill" data-toggle="tab">SWAPIDC对接教程</a>
                  <a class="dropdown-item" href="#djm-fill" data-toggle="tab">魔方对接教程</a>
                  <a class="dropdown-item" href="#staridc-fill" data-toggle="tab">Staridc对接教程</a>
              </div>
            </li>
          </ul>
          <div class="tab-content">
            <div class="tab-pane fade show active" id="jinyong-fill" >
              <p>
			  <span>  网站监控(并非摄像头)它是一个链接,用来定时执行而达到一定功能。您可以在宝塔设置定时任务来执行或者百度网页监控等执行。它很重要请您务必设置</span><br/>
			 <?php 
			   if($conf['api']==''){
			  echo '<span><code>请您在系统设置->APi设置里面把api密钥生成并且保存后再来此处设置监控！</code></span><br>';
			  }else{
			  echo '
			  <span>在您把系统设置->APi密钥修改后这里的链接也会重置！需要您重新设置定时任务(监控)中的链接！后才能正常运行！</span><br>
              <code class="wbcchh">'.$url.'/jk.php?my='.$conf['api'].'&gn=web</code><br/>
              <span>此为计算所有主机网页空间使用情况的链接，推荐设置为10分钟执行一次</span><br/>
              <code class="wbcchh">'.$url.'/'.'jk.php?my='.$conf['api'].'&gn=sql</code><br/>
              <span>此为计算所有主机数据库空间使用情况的链接，推荐设置为10分钟执行一次</span><br/>
              <code class="wbcchh">'.$url.'/'.'jk.php?my='.$conf['api'].'&gn=fh</code><br/>
              <span>此为计算所有主机流量使用情况的链接，推荐设置为10分钟执行一次</span><br/>
              <code class="wbcchh">'.$url.'/'.'jk.php?my='.$conf['api'].'&gn=fhq</code><br/>
              <span>此为清除所有主机流量使用情况的链接，推荐设置为每月1日执行一次</span><br/>
              <code class="wbcchh">'.$url.'/'.'jk.php?my='.$conf['api'].'&gn=ywjkdel</code><br/>
              <span>此为清除不使用的主机，推荐设置为每天执行一次</span><br/>
              <code class="wbcchh">'.$url.'/'.'jk_monitor.php?my='.$conf['api'].'</code><br/>
              <span>此为用户端URL监控、POST/GET/HEAD检测、资源阈值监控和通知任务，推荐设置为每15秒执行一次；如果监控工具不支持秒级执行，至少每分钟执行一次，但15秒任务会延迟检测</span><br/>
              ';
              }
              ?>
              </p>
            </div>
            <div class="tab-pane fade" id="gulong-fill" >
              <p>
                  1.登录服务器宝塔后台->面板设置<br/>
                  2.上方点击开启API接口<br/>
                  3.添加搭建本系统服务器的IP地址和127.0.0.1<br/>
                  4.记录下安装该宝塔服务器的IP地址和接口密钥<br/>
                  5.返回本系统后台->宝塔管理—>添加宝塔<br/>
                  6.宝塔IP填写刚才记录的IP地址<br/>
                  7.宝塔端口填写你宝塔面板登录的端口<br/>
                  8.宝塔密钥填写刚才记录的接口密钥<br/>
                  9.没特殊要求那域名解析说明可由系统默认<br/>
                  10.宝塔代号使用随机生成或者自己填写<br/>
                  11.如果面板打开了[面板SSL]则需要把安全访问打开<br/>
                  12.操作系统选择您搭建宝塔的服务器系统(不是Windows就是Linux)<br/>
                  13.点击下方的确认添加即可完成添加<br/>
              </p>
            </div>
            <div class="tab-pane fade" id="liangyusheng-fill" >
              <p>
                  1.选择好宝塔<br/>
                  2.填入主机的控制面板登录账号密码<br/>
                  3.填入网页空间和数据库空间及每月流量<br/>
                  4.填入域名最大绑定数<br/>
                  5.选择到期时间(不选择则为永久)<br/>
                  6.点击确认添加即可<br/>
                  7.<code>提示：请务必设置好监控！！！</code><br/>
              </p>
            </div>
            <div class="tab-pane fade" id="djs-fill" >
              <p>
              
                  1.下载对接文件<br/>
                  2.上传到搭建IDC网站的目录<code>/swap_mac/swap_lib/servers</code><br/>
                  3.然后解压刚才的文件然后删除压缩包即可<br/>
                  4.进入idc后台添加服务器<br/>
                  5.服务器插件选择MNBT<br/>
                  6.服务器主机名填写<code><?=$_SERVER['HTTP_HOST']?></code><br/>
                  7.用户名填写<code class="mnbt-tmp-btdh">宝塔编号</code> 密码填写<code class="mnbt-tmp-dymy mnbt-tmp-permanent-active"><?=htmlspecialchars($conf['api'] ?: '网站API密钥', ENT_QUOTES, 'UTF-8')?></code><br/>
                  8.底下的哈希密码填写<code class="mnbt-tmp-ktmy">调用密钥</code>(宝塔列表内将宝塔表滑到最后面即可)<br/>
                  9.安全访问/SSL访问之类的开关：<?=($_SERVER['SERVER_PORT'] == '443' ? '打开' : '关闭')?><br/>
                  10.填写需要的消息在宝塔列表里面(可以往后面滑动),保存即可<br/>
                  11.然后添加产品选择MNBT服务器插件然后填写消息即可<br/>
              </p>
              <p class="small">对接文件下载：<a href="./wjxz.php?ne=sw"/>点我前去下载</a></p>
            </div>
            
        <div class="tab-pane fade" id="djm-fill" >
              <p>
					1.下载对接插件<br/>
					2.上传到搭建IDC网站目录：<code>/public/plugins/servers</code><br/>
					3.然后解压刚才上传的文件然后删除压缩包即可<br/>
					4.进入后台填写服务器信息<br/>
					5.IP地址：<code class="mnbt-tmp-btdh">宝塔编号</code><br/>
					6.服务器模块：梦奈宝塔对接模块<br/>
					7.主机名：<code><?=$_SERVER['HTTP_HOST']?></code><br/>
					8.用户名：<code class="mnbt-tmp-ktmy">宝塔调用密钥</code><br/>
					9.密码：<code class="mnbt-tmp-dymy mnbt-tmp-permanent-active"><?=htmlspecialchars($conf['api'] ?: 'API密钥', ENT_QUOTES, 'UTF-8')?></code><br/>
                    10.安全访问/SSL访问之类的开关：<?=($_SERVER['SERVER_PORT'] == '443' ? '打开' : '关闭')?><br/>
					11.然后您就能添加产品进行测试了<br/>
             </p>
              <p class="small">对接文件下载：<a href="./wjxz.php?ne=mr"/>点我前去下载</a></p>
            </div>


              <div class="tab-pane fade" id="staridc-fill" >
                  <!-- StarIDC 配置对接 MNBT 指南 -->
                  <div class="staridc-mnbt-guide">
                      <h4 class="sm-guide-title">StarIDC 配置对接 MNBT</h4>

                      <ol class="sm-steps-list">
                          <li>在宝塔面板中安装 <strong>MNBT 系统</strong>插件。</li>
                          <li>
                              登录 MNBT 后台，获取以下三项参数：
                              <table class="sm-params-table">
                                  <thead>
                                  <tr>
                                      <th>参数</th>
                                      <th>获取路径</th>
                                  </tr>
                                  </thead>
                                  <tbody>
                                  <tr>
                                      <td>API 地址</td>
                                      <td><code class="sm-code"><?=$url?>/api/api.php</code></td>
                                  </tr>
                                  <tr>
                                      <td>宝塔编号</td>
                                      <td><code class="sm-code mnbt-tmp-btdh">MNBT 后台 → 宝塔列表 → 宝塔编号</code></td>
                                  </tr>
                                  <tr>
                                      <td>宝塔调用密钥</td>
                                      <td><code class="sm-code mnbt-tmp-ktmy">同上位置：宝塔调用密钥（注意：是<strong>宝塔调用密钥</strong>，不是宝塔密钥）</code></td>
                                  </tr>
                                  <tr>
                                      <td>API 密钥</td>
                                      <td><code class="sm-code mnbt-tmp-permanent-active mnbt-tmp-dymy"><?=htmlspecialchars($conf['api'] ?: 'API密钥', ENT_QUOTES, 'UTF-8')?></code></td>
                                  </tr>
                                  </tbody>
                              </table>
                          </li>
                          <li>进入 StarIDC 管理后台 → 系统配置 → MNBT 对接，填入以上三项（此处为默认服务器）。</li>
                          <li>如需对接多台服务器，进入 <strong>服务器管理</strong> 页面，按同样方式添加其他 MNBT 面板即可。</li>
                      </ol>

                      <div class="sm-notice">
                          <strong class="sm-notice-title">⚠️ 注意：</strong>
                          API 地址需确保能被服务器直接访问，不要使用经过 Cloudflare 代理的域名。
                      </div>
                  </div>
                  </p>
              </div>
            
          </div>
          
        </div>
      </div>
    </div>

          
        </div>
      </div>

<div class="container-fluid p-t-15">
  <?php }elseif($_GET['gn']=='sw'){
  	$id=$_GET['sz']; $cres=$DB->get_row_prepare("SELECT * FROM MN_bt WHERE id=? limit 1", [$id]);
	$eritvt=$cres['ktmy'].$cres['qmk'];
	$eritvf=md5($eritvt);
	?>
  <div class="container-fluid p-t-15">
            <div class="col-md-4">
              <code>[对接]SWAPIDC对接教程</code>
              <p class="small">
                  1.下载对接文件<br/>
                  2.上传到搭建IDC网站的目录<code>/swap_mac/swap_lib/servers</code><br/>
                  3.然后解压上传的压缩包然后删除压缩包即可<br/>
                  4.进入idc后台添加服务器<br/>
                  5.服务器插件选择MNBT<br/>
                  6.服务器主机名填写<code><?=$_SERVER['HTTP_HOST']?></code><br/>
                  7.用户名填写<code><?=$cres['btdh']?></code><br/>
                  8.密码填写<code><?=$conf['api']?></code><br/>
                  9.哈希密码填写<code><?=$eritvf?></code><br/>
                  10.安全访问/SSL访问之类的开关：<?=($_SERVER['SERVER_PORT'] == '443' ? '打开' : '关闭')?><br/>
                  11.然后保存更改即可<br/>
                  12.添加产品选择MNBT服务器插件然后填写消息即可<br/>
              </p>
              <p class="small">对接文件下载：<a href="./wjxz.php?ne=sw"/>点我前去下载</a></p>
            </div>
          </div>
  </div>
  
  
  
  <?php }else{
	$id=$_GET['sz']; $cres=$DB->get_row_prepare("SELECT * FROM MN_bt WHERE id=? limit 1", [$id]);
	$eritvt=$cres['ktmy'].$cres['qmk'];
	$eritvf=md5($eritvt);
	?>
  <div class="container-fluid p-t-15">
            <div class="col-md-4">
              <code>[对接]魔方对接教程</code>
              <p class="small">
					1.下载对接插件<br/>
					2.上传到搭建IDC网站目录：<code>/public/plugins/servers</code><br/>
					3.然后解压刚才上传的文件然后删除压缩包即可<br/>
					4.进入后台填写服务器信息<br/>
					5.IP地址填写：<code><?=$cres['btdh']?></code><br/>
					6.服务器模块：<code>梦奈宝塔对接模块</code><br/>
					7.主机名：<code><?=$_SERVER['HTTP_HOST']?></code><br/>
					8.用户名：<code><?=$eritvf?></code><br/>
					9.密码：<code><?=$conf['api']?></code><br/>
                    10.安全访问/SSL访问之类的开关：<?=($_SERVER['SERVER_PORT'] == '443' ? '打开' : '关闭')?><br/>
					11.然后您就能添加产品进行测试了<br/>
              </p>
              <p class="small">对接文件下载：<a href="./wjxz.php?ne=mr"/>点我前去下载</a></p>
            </div>
          </div>
  </div>
  
  
  
  
  
  
  
<?php }?>
<script type="text/javascript" src="<?=mnbt_asset_url('js/md5.js')?>"></script>
<script>
var btHostData = {};
var mnbtDefaultApi = <?=json_encode($conf['api'] ?? '', JSON_UNESCAPED_UNICODE)?>;

function initTutorialTmpDefaults() {
    $('.mnbt-tmp-btdh, .mnbt-tmp-ktmy, .mnbt-tmp-dymy').each(function() {
        $(this).data('default', $(this).html());
    });
}

function loadDefault() {
    $('.mnbt-tmp-btdh, .mnbt-tmp-ktmy, .mnbt-tmp-dymy').each(function() {
        var def = $(this).data('default');
        if (def !== undefined) {
            $(this).html(def);
        }
    }).removeClass('mnbt-tmp-active');
}

function applyTutorialBtData(data) {
    var esc = function(v) {
        return $('<div>').text(v == null ? '' : v).html();
    };
    $('.mnbt-tmp-btdh').html(esc(data.btdh));
    $('.mnbt-tmp-ktmy').html(esc(data.dymy));
    $('.mnbt-tmp-dymy').html(esc(mnbtDefaultApi));
    $('.mnbt-tmp-btdh, .mnbt-tmp-ktmy, .mnbt-tmp-dymy').addClass('mnbt-tmp-active');
}

function onTutorialBtChange(el) {
    var val = el.value;
    var data = btHostData[val];
    if (!val || !data) {
        loadDefault();
        return;
    }
    applyTutorialBtData(data);
}

$(function() {
    initTutorialTmpDefaults();

    $.post('./ajax.php', {
        gn: 'listbt',
        page: 1,
        limit: 100,
        sort: 'id',
        sortOrder: 'desc'
    }, function(res) {
        if (!res || !res.rows || !res.rows.length) return;
        var $sel = $('#tutorialBtSelect');
        $.each(res.rows, function(i, row) {
            $sel.append($('<option>', {
                value: row.id,
                text: row.btdh + (row.btip ? ' (' + row.btip + ')' : '')
            }));
            row.dymy = md5(row.ktmy + row.qmk);
            btHostData[row.id] = row;
        });
    }, 'json');
});
</script>
</body>
</html>
