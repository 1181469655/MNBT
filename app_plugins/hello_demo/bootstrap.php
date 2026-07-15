<?php
if (!defined('IN_CRONLITE')) {
	exit;
}

mnbt_plugin_register('hello_demo', [
	'name' => '示例插件 Hello',
]);

mnbt_register_menu('admin', [
	'title' => 'Hello 示例',
	'page' => 'index',
	'icon' => 'mdi-hand-okay',
	'order' => 10,
	'multitabs' => true,
]);

mnbt_register_page('admin', 'index', 'admin/index.php', 'Hello 示例');

mnbt_register_ajax('admin', 'p_hello_demo_ping', function () {
	mnbt_plugin_require_admin();
	$msg = mnbt_plugin_option_get('hello_demo', 'welcome', '你好，MNBT 插件！');
	$count = (int)mnbt_plugin_option_get('hello_demo', 'ping_count', 0);
	$count++;
	mnbt_plugin_option_set('hello_demo', 'ping_count', (string)$count);
	json_exit_success($msg, ['ping_count' => $count]);
});

mnbt_register_ajax('admin', 'p_hello_demo_save', function () {
	mnbt_plugin_require_admin();
	$welcome = isset($_POST['welcome']) ? trim((string)$_POST['welcome']) : '';
	if ($welcome === '') {
		json_exit_error('欢迎语不能为空');
	}
	if (mb_strlen($welcome) > 200) {
		json_exit_error('欢迎语过长');
	}
	mnbt_plugin_option_set('hello_demo', 'welcome', $welcome);
	json_exit_success('已保存');
});

mnbt_add_action('host.created', function ($host, $ctx = []) {
	$u = is_array($host) ? ($host['user'] ?? '') : '';
	$src = is_array($ctx) ? ($ctx['source'] ?? '') : '';
	$line = date('Y-m-d H:i:s') . " created user={$u} source={$src}";
	$log = mnbt_plugin_option_get('hello_demo', 'host_events', []);
	if (!is_array($log)) {
		$log = [];
	}
	array_unshift($log, $line);
	$log = array_slice($log, 0, 50);
	mnbt_plugin_option_set('hello_demo', 'host_events', $log);
});

mnbt_add_action('host.deleted', function ($host, $ctx = []) {
	$u = is_array($host) ? ($host['user'] ?? '') : '';
	$src = is_array($ctx) ? ($ctx['source'] ?? '') : '';
	$line = date('Y-m-d H:i:s') . " deleted user={$u} source={$src}";
	$log = mnbt_plugin_option_get('hello_demo', 'host_events', []);
	if (!is_array($log)) {
		$log = [];
	}
	array_unshift($log, $line);
	$log = array_slice($log, 0, 50);
	mnbt_plugin_option_set('hello_demo', 'host_events', $log);
});

mnbt_add_action('host.paused', function ($host, $ctx = []) {
	$u = is_array($host) ? ($host['user'] ?? '') : '';
	$line = date('Y-m-d H:i:s') . " paused user={$u}";
	$log = mnbt_plugin_option_get('hello_demo', 'host_events', []);
	if (!is_array($log)) {
		$log = [];
	}
	array_unshift($log, $line);
	$log = array_slice($log, 0, 50);
	mnbt_plugin_option_set('hello_demo', 'host_events', $log);
});

mnbt_add_action('host.renewed', function ($host, $ctx = []) {
	$u = is_array($host) ? ($host['user'] ?? '') : '';
	$old = is_array($ctx) ? ($ctx['old_date'] ?? '') : '';
	$new = is_array($ctx) ? ($ctx['new_date'] ?? '') : '';
	$line = date('Y-m-d H:i:s') . " renewed user={$u} {$old}=>{$new}";
	$log = mnbt_plugin_option_get('hello_demo', 'host_events', []);
	if (!is_array($log)) {
		$log = [];
	}
	array_unshift($log, $line);
	$log = array_slice($log, 0, 50);
	mnbt_plugin_option_set('hello_demo', 'host_events', $log);
});

mnbt_add_action('order.paid', function ($order, $ctx = []) {
	$ddh = is_array($order) ? ($order['ddh'] ?? '') : '';
	$je = is_array($order) ? ($order['je'] ?? '') : '';
	$line = date('Y-m-d H:i:s') . " order.paid ddh={$ddh} je={$je}";
	$log = mnbt_plugin_option_get('hello_demo', 'host_events', []);
	if (!is_array($log)) {
		$log = [];
	}
	array_unshift($log, $line);
	$log = array_slice($log, 0, 50);
	mnbt_plugin_option_set('hello_demo', 'host_events', $log);
});
