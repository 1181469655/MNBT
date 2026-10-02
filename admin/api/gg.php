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
	include_once("../MPHX/migrations.php");
	include("../cf_up.php");

	mnbt_updater_guard_start();
	mnbt_updater_progress_reset();
	$up_cfg = mnbt_updater_config();
	// 用真实路径做文件操作，ROOT 万一是软链或大小写不同也不会把文件写到别处
	$root_real = str_replace('\\', '/', realpath(rtrim(mnbt_updater_root(),'/')) ?: rtrim(mnbt_updater_root(),'/'));
	$root_sl = $root_real.'/';					// 站点根，带尾斜杠
	$root = $root_real;						// 站点根，不带尾斜杠
	mnbt_updater_guard('root',$root_sl);

	// 1. 检查最新版本（只走 GitHub Release，不再校验 authcode）；执行更新前必须拿最新的下载地址
	mnbt_updater_progress(['step'=>'check','detail'=>'向 api.github.com 询问最新 Release','pct'=>null]);
	$chk = mnbt_updater_check(true);
	if(!$chk['ok']) {
		mnbt_updater_progress_finish(false, '检查更新失败：'.$chk['error']);
		mnbt_updater_guard_done();
		json_exit_error('检查更新失败：'.$chk['error']);
	}
	if(!$chk['can_update']) {
		mnbt_updater_progress_finish(true, '当前已是最新版本（'.$chk['current'].'）');
		mnbt_updater_guard_done();
		json_exit_error('当前已是最新版本（'.$chk['current'].'），无需更新');
	}

	// 2. 临时目录放站点根的 runtime/temp 下（已在 .gitignore 内，且后台目录改名后依然可访问）
	$tmp_dir = $root.'/runtime/temp/update_tmp';
	if(!is_dir($tmp_dir) && !mnbt_updater_mkdir($tmp_dir)) {
		mnbt_updater_progress_finish(false, '临时目录创建失败');
		mnbt_updater_guard_done();
		json_exit_error('临时目录创建失败，请检查 runtime 目录权限');
	}
	$zip_file = $tmp_dir.'/gxwj-'.mt_rand(100000,999999).'.zip';
	mnbt_updater_guard('zip',$zip_file);

	// 3. 下载：Release 附件优先，回落自动源码包；每个来源按 github 直连、配置镜像顺序依次尝试
	mnbt_updater_progress(['step'=>'download','detail'=>'准备下载','pct'=>0]);
	$used = null;
	$tried = [];
	$cand_total = count($chk['candidates']);
	foreach($chk['candidates'] as $ci => $c) {
		$e = '';
		$label = ($c['via']==='mirror' ? $c['mirror'] : 'github 直连');
		$prefix = '候选 '.($ci+1).'/'.$cand_total.'（'.$label.'）';
		mnbt_updater_progress(['detail'=>$prefix.'：正在建立连接','pct'=>null]);
		if(mnbt_updater_download($c['url'],$zip_file,$up_cfg,$e,$prefix)) {
			$used = $c;
			break;
		}
		$tried[] = $label.'：'.$e;
	}
	if($used === null) {
		mnbt_updater_progress_finish(false, '更新包下载失败');
		mnbt_updater_guard_rollback();
		mnbt_updater_guard_done();
		json_exit_error('更新包下载失败（已尝试 '.count($tried).' 个来源）：'.implode(' / ',$tried));
	}

	// 4. 解压前校验：拒绝危险路径，识别并准备剥掉顶层目录
	mnbt_updater_progress(['step'=>'verify','detail'=>'扫描压缩包条目、检查路径合法性','pct'=>null]);
	$za = mnbt_updater_zip_analyze($zip_file,$zerr);
	if(!$za) {
		mnbt_updater_progress_finish(false, '更新包校验失败：'.$zerr);
		mnbt_updater_guard_rollback();
		mnbt_updater_guard_done();
		json_exit_error('更新包校验失败：'.$zerr);
	}

	// 5. 备份本地文件：config.php、cf_up.php、MPHX/SQ.php、install/install.lock、api/cookie/
	mnbt_updater_progress(['step'=>'backup','detail'=>'备份 config.php、cf_up.php 等本地文件','pct'=>null]);
	$bak_dir = $tmp_dir.'/bak_'.mt_rand(100000,999999);
	$backup = mnbt_updater_backup_local($root_sl,$bak_dir,$bk_err);
	mnbt_updater_guard('bak',$bak_dir);
	mnbt_updater_guard('backup',$backup);
	if($bk_err!=='') {
		mnbt_updater_progress_finish(false, '本地配置备份失败：'.$bk_err);
		mnbt_updater_guard_rollback();
		mnbt_updater_guard_done();
		json_exit_error('本地配置备份失败：'.$bk_err);
	}

	// 6. 当前后台目录改名成标准的 admin，新包里的 admin 才能落在正确位置
	mnbt_updater_progress(['step'=>'extract','detail'=>'准备覆盖站点文件','pct'=>0]);
	$admin_now = str_replace('\\','/',dirname(__DIR__));
	$admin_std = $root.'/admin';
	mnbt_updater_guard('admin_now',$admin_std);
	mnbt_updater_guard('admin_orig',$admin_now);
	if(basename($admin_now)!=='admin' || strcasecmp(dirname($admin_now),$root)!==0) {
		if(is_dir($admin_std)) {
			mnbt_updater_progress_finish(false, '后台目录名冲突');
			mnbt_updater_guard_rollback();
			mnbt_updater_guard_done();
			json_exit_error('站点根目录已存在一个 admin 目录，与当前后台目录名冲突，已中止更新');
		}
		if(!@rename($admin_now,$admin_std)) {
			mnbt_updater_progress_finish(false, '后台目录改名失败');
			mnbt_updater_guard_rollback();
			mnbt_updater_guard_done();
			json_exit_error('后台目录改名失败，已中止更新');
		}
		mnbt_updater_guard('moved',1);
	}

	// 7. 解压覆盖到站点根（附件包 zip 根即站点根；源码包剥掉顶层目录后同样如此）
	if(!mnbt_updater_extract($zip_file,$root_sl,$za['strip'],$ex_err)) {
		mnbt_updater_progress_finish(false, '解压覆盖失败：'.$ex_err);
		mnbt_updater_guard_rollback();
		mnbt_updater_guard_done();
		json_exit_error('解压覆盖失败：'.$ex_err);
	}

	// 8. 升级 SQL：把包里 update/ 的版本化迁移文件按游标顺序跑一遍（支持跨版本一步跳）
	$sql_msg = '';
	$upd_dir = $root.'/update/';
	$applied_files = array();
	if(is_dir($upd_dir)) {
		mnbt_updater_progress(['step'=>'migrate','detail'=>'扫描迁移文件、连接数据库','pct'=>0]);
		$mig_total = 0;
		$mig_cb = function ($ev) use (&$mig_total) {
			if ($ev['type'] === 'start') {
				$mig_total = (int)$ev['total'];
				mnbt_updater_progress([
					'detail' => $mig_total === 0 ? '无需迁移（游标已达目标版本）' : ('计划应用 '.$mig_total.' 个迁移文件'),
					'pct' => $mig_total === 0 ? 100 : 0,
				]);
			} elseif ($ev['type'] === 'file') {
				$t = max(1, $mig_total);
				mnbt_updater_progress([
					'detail' => '正在应用 '.($ev['index']+1).'/'.$t.'：'.$ev['name'],
					'pct' => (int)floor($ev['index'] * 100 / $t),
				]);
			} elseif ($ev['type'] === 'done') {
				mnbt_updater_progress(['detail'=>'已应用 '.$mig_total.' 个迁移','pct'=>100]);
			}
		};
		$applied_files = mnbt_migrations_run($dbconfig,$upd_dir,(int)$chk['version'],$mig_err,$mig_cb);
		if($mig_err!=='') $sql_msg = $mig_err;
		// 遗留单文件 update.sql（旧式发布）继续兼容：链成功后一次性执行并删除
		if($sql_msg==='' && is_file($upd_dir.'update.sql')) {
			mnbt_updater_progress(['detail'=>'执行旧式 update/update.sql','pct'=>null]);
			$sql_msg = mnbt_updater_run_sql($upd_dir.'update.sql',$dbconfig);
			if($sql_msg==='') {
				@unlink($upd_dir.'update.sql');
				@rmdir($upd_dir);
			}
		} elseif($sql_msg==='') {
			// 版本链全部成功：删掉已应用的迁移文件，保持 update/ 目录整洁（游标表已记账，重跑无副作用）
			foreach($applied_files as $fn) @unlink($upd_dir.$fn);
			@rmdir($upd_dir);
		}
		// 失败：保留 update/ 目录所有文件，游标停在最后成功版本，修复后可再次点击继续
	}

	// 9. 还原本地配置与安装锁、把后台目录改回原名、清临时文件
	mnbt_updater_progress(['step'=>'finalize','detail'=>'还原本地配置、恢复后台目录名、清理临时包','pct'=>null]);
	mnbt_updater_guard_rollback();
	mnbt_updater_guard_done();

	$via_label = $used['via']==='mirror' ? $used['mirror'] : 'github 直连';
	if(function_exists('logjl')) {
		logjl($user ?? '', '系统更新', '更新到 '.$chk['latest'].'（'.$used['label'].'，来源 '.$via_label.'）', $sql_msg===''?'更新成功':'更新成功但升级SQL有误', $DB);
	}
	if($sql_msg!=='') {
		mnbt_updater_progress_finish(false, '文件已更新，但升级 SQL 执行失败：'.$sql_msg);
		json_exit_error('文件已更新，但升级 SQL 执行失败：'.$sql_msg.'（update/ 迁移文件已保留，游标停在最后成功的版本，可修复后重跑或手工执行）', [
			'ver' => $chk['latest'], 'tag' => $chk['tag'],
			'source' => $used['label'], 'via' => $via_label, 'strip' => $za['strip'], 'files' => $za['files'],
		]);
	}
	mnbt_updater_progress_finish(true, '更新成功～请手动刷新页面');
	json_exit('更新成功～请手动刷新页面', [
		'qk' => 1, 'ver' => $chk['latest'], 'tag' => $chk['tag'],
		'source' => $used['label'], 'via' => $via_label, 'strip' => $za['strip'], 'files' => $za['files'],
	]);
	return;
}
return;
