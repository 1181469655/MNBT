<?php
if($egn=='gglist') {
	include("../cf_up.php");
	$result = send_post($mn_conf['aet'].'://'.$mn_conf['url'].':'.$mn_conf['port'].'/'.$mn_conf['install_wj'].'/guanggao.php',[]);
	if($result===false) $result='';
	exit($result);
}
if($egn=='update') {
	// 更新包体积大、解压慢，先放宽执行时间；用户中途关页面也要把流程走完
	@set_time_limit(600);
	@ignore_user_abort(true);
	include("../MPHX/BL.php");
	include_once("../MPHX/github_updater.php");
	include("../cf_up.php");

	mnbt_updater_guard_start();
	$up_cfg = mnbt_updater_config();
	// 用真实路径做文件操作，ROOT 万一是软链或大小写不同也不会把文件写到别处
	$root_real = str_replace('\\', '/', realpath(rtrim(mnbt_updater_root(),'/')) ?: rtrim(mnbt_updater_root(),'/'));
	$root_sl = $root_real.'/';					// 站点根，带尾斜杠
	$root = $root_real;						// 站点根，不带尾斜杠
	mnbt_updater_guard('root',$root_sl);

	// 1. 检查最新版本（只走 GitHub Release，不再校验 authcode）；执行更新前必须拿最新的下载地址
	$chk = mnbt_updater_check(true);
	if(!$chk['ok']) {
		mnbt_updater_guard_done();
		json_exit_error('检查更新失败：'.$chk['error']);
	}
	if(!$chk['can_update']) {
		mnbt_updater_guard_done();
		json_exit_error('当前已是最新版本（'.$chk['current'].'），无需更新');
	}

	// 2. 临时目录放站点根的 runtime/temp 下（已在 .gitignore 内，且后台目录改名后依然可访问）
	$tmp_dir = $root.'/runtime/temp/update_tmp';
	if(!is_dir($tmp_dir) && !mnbt_updater_mkdir($tmp_dir)) {
		mnbt_updater_guard_done();
		json_exit_error('临时目录创建失败，请检查 runtime 目录权限');
	}
	$zip_file = $tmp_dir.'/gxwj-'.mt_rand(100000,999999).'.zip';
	mnbt_updater_guard('zip',$zip_file);

	// 3. 下载：Release 附件优先，回落自动源码包；每个来源按 github 直连、配置镜像顺序依次尝试
	$used = null;
	$tried = [];
	foreach($chk['candidates'] as $c) {
		$e = '';
		if(mnbt_updater_download($c['url'],$zip_file,$up_cfg,$e)) {
			$used = $c;
			break;
		}
		$tried[] = ($c['via']==='mirror' ? $c['mirror'] : 'github 直连').'：'.$e;
	}
	if($used === null) {
		mnbt_updater_guard_rollback();
		mnbt_updater_guard_done();
		json_exit_error('更新包下载失败（已尝试 '.count($tried).' 个来源）：'.implode(' / ',$tried));
	}

	// 4. 解压前校验：拒绝危险路径，识别并准备剥掉顶层目录
	$za = mnbt_updater_zip_analyze($zip_file,$zerr);
	if(!$za) {
		mnbt_updater_guard_rollback();
		mnbt_updater_guard_done();
		json_exit_error('更新包校验失败：'.$zerr);
	}

	// 5. 备份本地文件：config.php、cf_up.php、MPHX/SQ.php、install/install.lock、api/cookie/
	$bak_dir = $tmp_dir.'/bak_'.mt_rand(100000,999999);
	$backup = mnbt_updater_backup_local($root_sl,$bak_dir,$bk_err);
	mnbt_updater_guard('bak',$bak_dir);
	mnbt_updater_guard('backup',$backup);
	if($bk_err!=='') {
		mnbt_updater_guard_rollback();
		mnbt_updater_guard_done();
		json_exit_error('本地配置备份失败：'.$bk_err);
	}

	// 6. 当前后台目录改名成标准的 admin，新包里的 admin 才能落在正确位置
	$admin_now = str_replace('\\','/',dirname(__DIR__));
	$admin_std = $root.'/admin';
	mnbt_updater_guard('admin_now',$admin_std);
	mnbt_updater_guard('admin_orig',$admin_now);
	if(basename($admin_now)!=='admin' || strcasecmp(dirname($admin_now),$root)!==0) {
		if(is_dir($admin_std)) {
			mnbt_updater_guard_rollback();
			mnbt_updater_guard_done();
			json_exit_error('站点根目录已存在一个 admin 目录，与当前后台目录名冲突，已中止更新');
		}
		if(!@rename($admin_now,$admin_std)) {
			mnbt_updater_guard_rollback();
			mnbt_updater_guard_done();
			json_exit_error('后台目录改名失败，已中止更新');
		}
		mnbt_updater_guard('moved',1);
	}

	// 7. 解压覆盖到站点根（附件包 zip 根即站点根；源码包剥掉顶层目录后同样如此）
	if(!mnbt_updater_extract($zip_file,$root_sl,$za['strip'],$ex_err)) {
		mnbt_updater_guard_rollback();
		mnbt_updater_guard_done();
		json_exit_error('解压覆盖失败：'.$ex_err);
	}

	// 8. 升级 SQL：只执行包里带的 update/update.sql，位置在剥顶层目录后不变
	$sql_msg = '';
	$sql_file = $root.'/update/update.sql';
	if(is_file($sql_file)) {
		$sql_msg = mnbt_updater_run_sql($sql_file,$dbconfig);
		if($sql_msg==='') {
			// 执行成功才删，出错时保留文件让管理员可以手工再跑一次
			@unlink($sql_file);
			@rmdir($root.'/update/');
		}
	}

	// 9. 还原本地配置与安装锁、把后台目录改回原名、清临时文件
	mnbt_updater_guard_rollback();
	mnbt_updater_guard_done();

	$via_label = $used['via']==='mirror' ? $used['mirror'] : 'github 直连';
	if(function_exists('logjl')) {
		logjl($user ?? '', '系统更新', '更新到 '.$chk['latest'].'（'.$used['label'].'，来源 '.$via_label.'）', $sql_msg===''?'更新成功':'更新成功但升级SQL有误', $DB);
	}
	if($sql_msg!=='') {
		json_exit_error('文件已更新，但升级 SQL 执行失败：'.$sql_msg.'（update/update.sql 已保留，可手工执行）', [
			'ver' => $chk['latest'], 'tag' => $chk['tag'],
			'source' => $used['label'], 'via' => $via_label, 'strip' => $za['strip'], 'files' => $za['files'],
		]);
	}
	json_exit('更新成功～请手动刷新页面', [
		'qk' => 1, 'ver' => $chk['latest'], 'tag' => $chk['tag'],
		'source' => $used['label'], 'via' => $via_label, 'strip' => $za['strip'], 'files' => $za['files'],
	]);
	return;
}
return;
