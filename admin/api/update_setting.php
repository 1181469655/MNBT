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
	if($repo==='') json_exit_error('请填写 GitHub 仓库，格式为 owner/repo');

	// Gitee 仓库允许留空（= 只走 GitHub）；格式校验交给 save_config
	$gitee_repo = trim((string)($_POST['gitee_repo'] ?? ''));

	// Token 留空表示保持原值，勾选清除才写空；任何场合都不回显明文
	$token = null;
	if(!empty($_POST['clear_token'])) {
		$token = '';
	} elseif(trim((string)($_POST['github_token'] ?? ''))!=='') {
		$token = trim((string)$_POST['github_token']);
	}

	// 下载策略：前端提交才改，否则保持原值
	$policy = isset($_POST['source_policy']) && $_POST['source_policy'] !== ''
		? (string)$_POST['source_policy']
		: null;

	$res = mnbt_updater_save_config($repo, $gitee_repo, $token, $policy);
	if(!$res['ok']) json_exit_error($res['error']);

	$cfg = mnbt_updater_config();
	logjl($user ?? '', '系统更新', 'GitHub '.$cfg['repo'].' / Gitee '.($cfg['gitee_repo']===''?'未设置':$cfg['gitee_repo']).'，策略 '.$cfg['source_policy'], '保存成功', $DB);
	json_exit('保存成功', [
		'qk' => 1,
		'repo' => $cfg['repo'],
		'gitee_repo' => $cfg['gitee_repo'],
		'has_token' => $cfg['github_token']!=='' ? 1 : 0,
		'source_policy' => $cfg['source_policy'],
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
if($egn=='upprogress') {
	include_once("../MPHX/BL.php");
	include_once("../MPHX/github_updater.php");
	// 只读进度文件；文件不存在说明当前没有更新在跑，也不启动流程
	$p = mnbt_updater_progress_read();
	if (!is_array($p)) {
		exit(json_encode(['ok'=>1,'running'=>0,'has'=>0], JSON_UNESCAPED_UNICODE));
	}
	// V1.87：ok 字段是进度文件的真实终态（null=进行中/1=成功/0=失败），不能被端点覆盖
	$p['has'] = 1;
	exit(json_encode($p, JSON_UNESCAPED_UNICODE));
	return;
}
return;
