<?php
/**
 * mobile_bridge 插件 - 主入口
 *
 * 功能：为 MNBT 主机管理 APP 提供 /app/v1/{action} JSON 桥接
 * 承载：通用路由（index.php?_r=/app/v1/...），auth=null + csrfExempt=true，
 *       鉴权由 lib/context.php 的 Bearer 设备令牌独立完成
 * 面板：用户端「APP 配对」页（views/app_bind.php）+ 配套 AJAX（cookie+CSRF 通道）
 */
if (!defined('IN_CRONLITE')) {
	exit;
}

require_once __DIR__ . '/lib/envelope.php';
require_once __DIR__ . '/lib/actions.php';

mnbt_plugin_register('mobile_bridge', [
	'name' => '移动桥接（APP）',
	'description' => '为 MNBT 主机管理 APP 提供设备配对与 /app/v1 JSON 接口',
]);

/* ============================================================
 *  建表守卫（与 install.sql 同构；引擎表缺失时自愈）
 * ============================================================ */
function mnbt_app_ensure_tables()
{
	global $DB;
	@$DB->query("CREATE TABLE IF NOT EXISTS `MN_plugin_app_device` (
		`id` int(11) NOT NULL AUTO_INCREMENT,
		`zj_id` int(11) NOT NULL,
		`token_hash` char(64) NOT NULL,
		`device_id` varchar(64) NOT NULL DEFAULT '',
		`device_name` varchar(120) NOT NULL DEFAULT '',
		`device_model` varchar(120) NOT NULL DEFAULT '',
		`app_version` varchar(20) NOT NULL DEFAULT '',
		`android_version` varchar(20) NOT NULL DEFAULT '',
		`created_at` varchar(50) NOT NULL,
		`last_seen_at` varchar(50) NOT NULL DEFAULT '',
		`last_ip` varchar(45) NOT NULL DEFAULT '',
		`status` tinyint(1) NOT NULL DEFAULT 1,
		PRIMARY KEY (`id`),
		UNIQUE KEY `uk_token` (`token_hash`),
		KEY `idx_zj` (`zj_id`),
		KEY `idx_device` (`zj_id`, `device_id`)
	) ENGINE=MyISAM DEFAULT CHARSET=utf8");
	@$DB->query("CREATE TABLE IF NOT EXISTS `MN_plugin_app_pair` (
		`code` char(8) NOT NULL,
		`zj_id` int(11) NOT NULL,
		`created_at` int(11) NOT NULL,
		`expires_at` int(11) NOT NULL,
		`used` tinyint(1) NOT NULL DEFAULT 0,
		PRIMARY KEY (`code`),
		KEY `idx_zj` (`zj_id`)
	) ENGINE=MyISAM DEFAULT CHARSET=utf8");
}
mnbt_app_ensure_tables();

/* ============================================================
 *  APP 路由（JSON 通道：无 cookie、无 CSRF，凭 Bearer 令牌）
 * ============================================================ */

// POST /app/v1/{action} —— 全部业务 action；pair 也走这里（auth 在 dispatch 内按表分级）
mnbt_register_route('POST', '/app/v1/{action}', 'mnbt_app_dispatch', 10, null, true);

// GET /app/v1/pair_page —— 配对二维码落地页（普通浏览器扫码降级说明）
mnbt_register_route('GET', '/app/v1/pair_page', 'mnbt_app_pair_page', 10, null, true);

/* ============================================================
 *  用户端页面与菜单
 * ============================================================ */
mnbt_register_page('user', 'app_bind', 'views/app_bind.php', 'APP 配对');
mnbt_register_menu('user', [
	'title' => 'APP 配对',
	'icon' => 'mdi-cellphone-link',
	'order' => 78,
	'page' => 'app_bind',
]);

/* ============================================================
 *  用户端 AJAX（cookie 会话 + CSRF，经 user/ajax.php 的插件分发）
 * ============================================================ */

// 生成一次性配对码
mnbt_register_ajax('user', 'p_mobile_bridge_gen_code', function () {
	global $DB, $yhc;
	$uid = (string)($yhc['user'] ?? '');
	if (function_exists('mnbt_throttle_exceeded') && mnbt_throttle_exceeded('app_gen_code', $uid, 10, 600)) {
		json_exit_error('生成过于频繁，请 10 分钟后再试');
	}
	mnbt_app_pair_gc();
	$code = mnbt_app_pair_generate((int)$yhc['id']);
	if ($code === '') {
		json_exit_error('配对码生成失败，请稍后重试');
	}
	if (function_exists('mnbt_throttle_hit')) mnbt_throttle_hit('app_gen_code', $uid, 600);
	logjl($uid, 'APP配对', '生成了配对码（未记录码值）', '成功', $DB);
	json_exit_success('ok', [
		'code' => $code,
		'expires_in' => 120,
		'qr_url' => mnbt_app_pair_page_url($code),
	]);
});

// 设备列表
mnbt_register_ajax('user', 'p_mobile_bridge_device_list', function () {
	global $yhc;
	$rows = mnbt_app_device_list((int)$yhc['id']);
	$list = [];
	foreach ($rows as $r) {
		$list[] = [
			'id' => (int)$r['id'],
			'device_id' => $r['device_id'],
			'device_name' => $r['device_name'],
			'device_model' => $r['device_model'],
			'app_version' => $r['app_version'],
			'android_version' => $r['android_version'],
			'created_at' => $r['created_at'],
			'last_seen_at' => $r['last_seen_at'],
			'last_ip' => $r['last_ip'],
			'status' => (int)$r['status'],
		];
	}
	json_exit_success('ok', ['list' => $list]);
});

// 吊销设备（限定当前主机，防越权）
mnbt_register_ajax('user', 'p_mobile_bridge_device_revoke', function () {
	global $DB, $yhc;
	$id = (int)($_POST['id'] ?? 0);
	if ($id <= 0) {
		json_exit_error('参数错误');
	}
	if (!mnbt_app_device_revoke_by_id($id, (int)$yhc['id'])) {
		json_exit_error('吊销失败，请刷新后重试');
	}
	logjl($yhc['user'], 'APP配对', '在面板端吊销了设备id=' . $id, '成功', $DB);
	json_exit_success('已吊销');
});
