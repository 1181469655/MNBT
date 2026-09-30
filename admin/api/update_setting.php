<?php
/**
 * 系统更新设置（GitHub Release）
 * setting.php 归外部 API 兼容改造占用，更新相关的配置单独走这个文件
 */
if($egn=='upset') {
	include_once("../MPHX/BL.php");
	include_once("../MPHX/github_updater.php");
	include("../cf_up.php");

	$repo = trim((string)($_POST['repo'] ?? ''));
	if($repo==='') json_exit_error('请填写更新仓库，格式为 owner/repo');

	$mirrors_raw = $_POST['mirrors'] ?? '';
	if(is_array($mirrors_raw)) {
		$mirrors = $mirrors_raw;
	} else {
		$mirrors = preg_split('/\r\n|\r|\n|,/', (string)$mirrors_raw);
	}
	$mirrors = array_values(array_filter(array_map('trim', (array)$mirrors), function($v){ return $v!==''; }));

	// Token 留空表示保持原值，勾选清除才写空；任何场合都不回显明文
	$token = null;
	if(!empty($_POST['clear_token'])) {
		$token = '';
	} elseif(trim((string)($_POST['github_token'] ?? ''))!=='') {
		$token = trim((string)$_POST['github_token']);
	}

	$res = mnbt_updater_save_config($repo, $mirrors, $token);
	if(!$res['ok']) json_exit_error($res['error']);

	$cfg = mnbt_updater_config();
	logjl($user ?? '', '系统更新', '更新仓库设为 '.$cfg['repo'].'，镜像 '.count($cfg['mirrors']).' 个', '保存成功', $DB);
	json_exit('保存成功', [
		'qk' => 1,
		'repo' => $cfg['repo'],
		'mirrors' => $cfg['mirrors'],
		'has_token' => $cfg['github_token']!=='' ? 1 : 0,
	]);
	return;
}
if($egn=='upcheck') {
	include_once("../MPHX/BL.php");
	include_once("../MPHX/github_updater.php");
	include("../cf_up.php");
	// 纯数据载荷，前端直接取字段（与 egn=mnbt 的风格一致）
	exit(json_encode(mnbt_updater_check(!empty($_POST['force']), 60), JSON_UNESCAPED_UNICODE));
	return;
}
return;
