<?php
// 系统更新:服务器端检查更新(与默认主题 update.php 一致),注入 boot
$td_entry = 'update';
$td_hash  = '#/update';
$td_inject = [];

// 当前版本
$td_inject['currentVersion'] = isset($WEBQB) ? 'V' . sprintf('%.2f', $WEBQB / 1000) : 'V0.00';

// 服务器端检查更新
if (isset($mn_conf['url']) && $mn_conf['url'] && function_exists('send_post')) {
	$gxtj = array(
		'url'      => $_SERVER['HTTP_HOST'] ?? '',
		'authcode' => $authcode ?? '',
		'ver'      => $WEBQB ?? 0,
	);
	$result  = send_post($mn_conf['aet'] . '://' . $mn_conf['url'] . ':' . $mn_conf['port'] . '/check.php', $gxtj);
	$content = json_decode($result, true);
	if (is_array($content)) {
		$td_inject['updateInfo'] = $content;
	}
}

include __DIR__ . '/_spa_boot.php';
