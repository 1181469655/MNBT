<?php mnbt_admin_include('head'); ?>
<?php
$zjsl=$DB->count_prepare("SELECT count(*) from MN_zj WHERE 1");
$btsl=$DB->count_prepare("SELECT count(*) from MN_bt WHERE 1");
?>
<div class="container-fluid col-15">

  <div class="row">
    <div class="col-sm-20 col-md-6">
      <div class="card bg-primary text-white">
        <div class="card-body clearfix">
          <div class="flex-box">
            <span class="img-avatar img-avatar-48 bg-translucent"><i class="mdi mdi-locker-multiple fs-22"></i></span>
            <span class="fs-22 lh-22"><?php if($zjsl==''){echo 0;}else{echo $zjsl;}?></span>
          </div>
          <div class="text-right">主机数量</div>
        </div>
      </div>
    </div>

    <div class="col-sm-20 col-md-6">
      <div class="card bg-success text-white">
        <div class="card-body clearfix">
          <div class="flex-box">
            <span class="img-avatar img-avatar-48 bg-translucent"><i class="mdi mdi-server fs-22"></i></span>
            <span class="fs-22 lh-22"><?php if($btsl==''){echo 0;}else{echo $btsl;}?></span>
          </div>
          <div class="text-right">宝塔数量</div>
        </div>
      </div>
    </div>
</div>

<div class="row">
		<div class="col-sm-6">
			<div class="card">
				<div class="card-header bg-info">
					<h4>官网公告</h4>
					<ul class="card-actions">
						<li>
						    
						    <button type="button" class="btn btn-info" data-container="body" id="butos" data-toggle="popover" data-placement="top" data-content="版本更新提示" data-original-title="" title="">
						        <i id="tbcls" class="mdi <?=$cl?>">
								</i>
          </button>
						    
						</li>
					</ul>
				</div>
				<div class="card-body">
					<p>
					    <div id="mngf"></div>
					</p>
				</div>
			</div>
		</div>

		<div class="col-sm-6">
			<div class="card">
				<div class="card-header bg-info">
					<h4>广告列表</h4>
					<ul class="card-actions">
						<li>
						    <button type="button" class="btn btn-info" data-container="body" id="butos" data-toggle="popover" data-placement="top" data-content="购买广告位置请前去官方QQ群找小泉或小乐！" title="">
						        <i id="tbcls" class="mdi mdi-react">
								</i>
						</li>
					</ul>
				</div>
				<div class="card-body">
					<p>
						<div id="gglt">
							<code>
								<b>广告均由第三方提供！其内容与本系统无关！</b>
							</code>
						</div>
					</p>
				</div>
			</div>
		</div>
</div>

<?php
if (function_exists('mnbt_plugin_render_widgets_html')) {
	echo mnbt_plugin_render_widgets_html('admin');
}
?>

</div>
<script>
msloading('正在获取中，请稍后...','text-info','text-default','#gglt');  // 加载显示
msloading('正在获取中，请稍后...','text-info','text-default','#mngf');  // 加载显示
//获取公告+版本信息
	let datar = {};
			datar["gn"]="mnbt";
			$.post('./ajax.php', datar, function (date) {
			var jsoe= JSON.parse(date);    
			     document.getElementById("mngf").innerHTML=jsoe.gg;
			     document.getElementById("tbcls").className='mdi '+jsoe.cl;
			     document.getElementById("tbcls").innerHTML=jsoe.vs;
			     document.getElementById("butos").setAttribute("data-content", jsoe.gx);
		msloadingde("#mngf");
			})

//获取广告列表
	let data = {};
			data["gn"]="gglist";
			$.post('./ajax.php', data, function (date) {
			var jsoe= JSON.parse(date); 
			for(var i in jsoe){
			     var tmp = document.createElement("div");  
			     tmp.innerHTML= '<span class="list-group-item list-group-item-success">'+jsoe[i].nr+'<a href="http://'+jsoe[i].url+'/" target="_blank">'+jsoe[i].name+jsoe[i].url+'</a></span><br/>';
			     document.getElementById("gglt").appendChild(tmp);
			}
			msloadingde("#gglt");
			
			})
</script>
</body>
</html>