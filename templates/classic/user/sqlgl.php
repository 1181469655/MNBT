<?php mnbt_theme_include('head'); ?>
<div class="container-fluid p-t-15">
  
  <div class="row">
    <div class="col-lg-12">
      <div class="card">
        <div class="card-toolbar d-flex flex-column flex-md-row">
          <div class="toolbar-btn-action">
            <i class="mdi mdi-plus btn btn-primary m-r-5" onclick="databaseadd(id='<?php echo $hxd; ?>')">备份数据库</i> 
            <i class="mdi mdi-block-helper btn btn-warning m-r-5" onclick="Delalldatabase()">删除数据库所有数据</i>
          </div>
        </div>
        <div class="card-body">
          
          <div class="table-responsive">
            <table class="table table-bordered">
              <thead>
                <tr>
                  <th>
                    <div class="custom-control custom-checkbox">
                      <input type="checkbox" class="custom-control-input" id="check-all">
                      <label class="custom-control-label" for="check-all"></label>
                    </div>
                  </th>
                  <th>备份名称</th>
                  <th>备份时间</th>
                  <th>备份大小</th>
                  <th>操作对象</th>
                  <th>操作</th>
                </tr>
              </thead>
              <tbody>
                <?php
                foreach ($bf_data as $item) {
                    $backupFilenameJson = htmlspecialchars(json_encode((string)$item['filename'], JSON_UNESCAPED_UNICODE | JSON_HEX_APOS | JSON_HEX_QUOT), ENT_QUOTES, 'UTF-8');
                    // 在这里处理每个数组元素
                    //echo "ID: " . $item['id'] . "<br>";
                    //echo "备份名称: " . $item['name'] . "<br>";
                    //echo "备份时间：" .$item['addtime']."<br>";
                    //echo "备份大小：" .$item['size'] / (1024 * 1024)."MB<br>";
                    //echo "备份大小：" .$item['ps'] ."<br>";
                ?>
                <tr>
                  <td>
                    <div class="custom-control custom-checkbox">
                      <input type="checkbox" class="custom-control-input ids" name="ids[]" value="1" id="ids-1">
                      <label class="custom-control-label" for="ids-1"></label>
                    </div>
                  </td>
                  <td><?php echo $item['name']; ?></td>
                  <td><?php echo $item['addtime']; ?></td>
                  <td><?php echo $item['size'] / (1024 * 1024); ?>MB</td>
                  
                  <td><?php echo $item['ps']; ?></td>
                  <td>
                    <div class="btn-group">
                        <a class="btn btn-xs btn-default" title="恢复数据" data-toggle="tooltip" onclick="databaserestore(user='<?php echo $user; ?>',filename='<?php echo $item['filename']; ?>')" data-original-title="恢复数据"><i class="mdi mdi-cloud-check"></i></a>
                      <a class="btn btn-xs btn-default" title="下载备份" data-toggle="tooltip" onclick="databasedownload(<?php echo $backupFilenameJson; ?>)" data-original-title="下载备份"><i class="mdi mdi-cloud-download-outline"></i></a>
                      <a class="btn btn-xs btn-default ajax-get confirm" title="点我就删除了" onclick="databasedel(id='<?php echo $item['id']; ?>')" data-toggle="tooltip" data-original-title="删除"><i class="mdi mdi-window-close"></i></a>
                    </div>
                  </td>
                </tr>
                <?php
                }
                ?>
              </tbody>
            </table>
          </div>
          <ul class="pagination">
            <li class="page-item disabled"><span class="text-success">共<?php echo $count; ?>条数据</span></li>
            <li class="page-item disabled"><span class="text-success">用户总备份次数</span></li>
            <li class="page-item disabled"><span class="text-success">用户使用备份次数</span></li>
            <li class="page-item disabled"><span class="text-success">用户剩余备份次数 </span></li>
          </ul>
 
        </div>
      </div>
    </div>
</div>
<script type="text/javascript">
function databasedel(id)
{
    msloading('正在处理中，请稍后...');  // 加载显示
    let data = {};
    data["gn"] = "databasedel";
    data["id"] = id;
    $.post('./ajax.php', data, function (date) {
    var jsoe= JSON.parse(date);    
    var qk= jsoe.code
    if(qk == "删除成功")
    {
        msalert(1,'删除成功！将在两秒后刷新页面！',2000);
    setTimeout(function()
    {
        window.location.href="./sqlgl.php";
    },2000);
    }
    else
    {
        msalert(4, qk,2000);
            setTimeout(function()
    {
        window.location.href="./sqlgl.php";
    },2000);
    
    }
        
    })
}
function databaseadd(id)
{
    msloading('正在处理中，请稍后...');  // 加载显示
    let data = {};
    data["gn"] = "databaseadd";
    data["id"] = id;
    $.post('./ajax.php', data, function (date) {
    var jsoe= JSON.parse(date);    
    var qk= jsoe.code
    if(qk == "备份成功!")
    {
        msalert(1,'备份成功！将在两秒后刷新页面！',2000);
    setTimeout(function()
    {
        window.location.href="./sqlgl.php";
    },2000);
    }
    else
    {
        msalert(4, qk,2000);
            setTimeout(function()
    {
        window.location.href="./sqlgl.php";
    },2000);
    
    }
        
    })
}
function databasedownload(filename)
{
    msloading('正在获取下载链接，请稍候...');
    let data = {};
    data["gn"] = "databasedownload";
    data["filename"] = filename;
    $.post('./ajax.php', data, function (date) {
        var jsoe;
        try {
            jsoe = JSON.parse(date);
        } catch (e) {
            if (typeof msloadingde === 'function') msloadingde();
            msalert(4, '获取下载链接失败', 2000);
            return;
        }
        if (typeof msloadingde === 'function') msloadingde();
        if (jsoe.url) {
            window.open(jsoe.url, '_blank');
        } else {
            msalert(4, jsoe.msg || jsoe.code || '获取下载链接失败', 2000);
        }
    })
}
function databaserestore(user,filename)
{
    msloading('正在处理中，请稍后...');  // 加载显示
    let data = {};
    data["gn"] = "databaserestore";
    data["user"] = user;
    data['filename'] = filename;
    $.post('./ajax.php', data, function (date) {
    var jsoe= JSON.parse(date);    
    var qk= jsoe.code
    if(qk == "导入数据库成功!")
    {
        msalert(1,'导入数据库成功！将在两秒后刷新页面！',2000);
    setTimeout(function()
    {
        window.location.href="./sqlgl.php";
    },2000);
    }
    else
    {
        msalert(4, qk,2000);
            setTimeout(function()
    {
        window.location.href="./sqlgl.php";
    },2000);
    
    }
        
    })
}
function Delalldatabase()
{
    msloading('正在处理中，请稍后...');  // 加载显示
    let data = {};
    data["gn"] = "Delalldatabase";
    $.post('./ajax.php', data, function (date) {
    var jsoe= JSON.parse(date);    
    var qk= jsoe.code
    if(qk == "删除成功")
    {
        msalert(1,'全部数据库都删除成功！将在两秒后刷新页面！',2000);
    setTimeout(function()
    {
        window.location.href="./sqlgl.php";
    },2000);
    }
    else
    {
        msalert(4, qk,2000);
            setTimeout(function()
    {
        window.location.href="./sqlgl.php";
    },2000);
    
    }
        
    })
}
</script>
