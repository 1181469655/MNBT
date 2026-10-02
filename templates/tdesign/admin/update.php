<?php
// 系统更新:服务器端检查更新(admin/update.php 已检查并注入 $mnbt_update / $mnbt_upcfg),输出注入 boot
$td_entry = 'update';
$td_hash  = '#/update';
$td_inject = [];

if (!isset($mnbt_update) || !is_array($mnbt_update)) {
	// 直接访问本文件时的兜底(正常入口都会带上来)
	if (!function_exists('mnbt_updater_check')) {
		include_once dirname(__DIR__, 3) . '/MPHX/github_updater.php';
	}
	$mnbt_update = mnbt_updater_check(!empty($_GET['recheck']), 60);
}
if (!isset($mnbt_upcfg) || !is_array($mnbt_upcfg)) {
	$mnbt_upcfg = mnbt_updater_config();
}

$td_inject['currentVersion'] = $mnbt_update['current'];

// code/ver/msg/uplog 是给已编译的 dist 用的老字段，保持原语义不能改
$td_info = [
	'code'  => $mnbt_update['ok'] != 1 ? '-1' : ($mnbt_update['can_update'] == 1 ? '1' : '0'),
	'ver'   => $mnbt_update['ok'] == 1 ? $mnbt_update['latest'] : '-',
	'msg'   => $mnbt_update['ok'] != 1
		? ('暂时无法检查更新：' . $mnbt_update['error'])
		: ($mnbt_update['can_update'] == 1 ? '发现新版本，可执行在线覆盖更新' : '当前版本已是最新，无需更新'),
	'uplog' => (string)$mnbt_update['body'],
	// 新增字段：下载来源与配置（老 dist 会忽略）
	'repo'         => $mnbt_update['repo'],
	'tag'          => $mnbt_update['tag'],
	'name'         => $mnbt_update['name'],
	'source'       => $mnbt_update['source'],
	'sourceLabel'  => $mnbt_update['source_label'],
	'assetName'    => $mnbt_update['asset_name'],
	'assetUrl'     => $mnbt_update['asset_url'],
	'assetSize'    => $mnbt_update['asset_size'],
	'publishedAt'  => $mnbt_update['published_at'],
	'canUpdate'    => (int)$mnbt_update['can_update'],
	'error'        => $mnbt_update['error'],
	'fallback'     => (int)$mnbt_update['fallback'],
	'currentVer'   => $mnbt_update['current'],
	'giteeRepo'    => $mnbt_update['gitee_repo'],
	'sourcePolicy' => $mnbt_update['source_policy'],
	'channel'      => $mnbt_update['channel'],
	'channelLabel' => $mnbt_update['channel_label'],
];
$td_inject['updateInfo'] = $td_info;

// 更新设置：Token 只回显是否已设置，绝不回显明文
$td_inject['updaterConfig'] = [
	'repo'         => $mnbt_upcfg['repo'],
	'giteeRepo'    => $mnbt_upcfg['gitee_repo'],
	'sourcePolicy' => $mnbt_upcfg['source_policy'],
	'hasToken'     => $mnbt_upcfg['github_token'] !== '' ? 1 : 0,
];

include __DIR__ . '/_spa_boot.php';
