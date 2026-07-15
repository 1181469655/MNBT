<?php
if (!defined('IN_CRONLITE')) {
	exit;
}

mnbt_plugin_register('webhook_notify', ['name' => 'Webhook 通知']);

mnbt_register_menu('admin', [
	'title' => 'Webhook 通知',
	'page' => 'settings',
	'icon' => 'mdi-webhook',
	'order' => 20,
	'multitabs' => true,
]);

mnbt_register_page('admin', 'settings', 'admin/settings.php', 'Webhook 通知');
mnbt_register_settings_tab([
	'title' => 'Webhook 通知',
	'page' => 'settings',
	'order' => 20,
]);

mnbt_register_widget('admin', [
	'title' => 'Webhook 状态',
	'order' => 10,
	'class' => 'col-sm-6',
	'callback' => function () {
		$url = (string)mnbt_plugin_option_get('webhook_notify', 'url', '');
		$enabled = mnbt_plugin_option_get('webhook_notify', 'enabled', 'true') === 'true';
		$logs = mnbt_plugin_option_get('webhook_notify', 'delivery_log', []);
		$last = is_array($logs) && $logs ? $logs[0] : null;
		echo '<p class="mb-1">状态：' . ($enabled && $url !== '' ? '<span class="text-success">已配置</span>' : '<span class="text-muted">未配置</span>') . '</p>';
		echo '<p class="mb-1 small text-muted">URL：' . htmlspecialchars($url !== '' ? $url : '（空）', ENT_QUOTES, 'UTF-8') . '</p>';
		if ($last) {
			echo '<p class="mb-0 small">最近：' . htmlspecialchars((string)$last, ENT_QUOTES, 'UTF-8') . '</p>';
		}
		echo '<p class="mt-2 mb-0"><a class="btn btn-sm btn-outline-primary multitabs" href="plugin.php?p=webhook_notify&page=settings">打开设置</a></p>';
	},
]);

/**
 * @return array{url:string,secret:string,enabled:bool,events:array}
 */
function webhook_notify_config()
{
	$events = mnbt_plugin_option_get('webhook_notify', 'events', null);
	if (!is_array($events)) {
		$events = [
			'host.created' => true,
			'host.paused' => true,
			'host.unpaused' => true,
			'host.renewed' => true,
			'host.deleted' => true,
			'order.paid' => true,
		];
	}
	return [
		'url' => trim((string)mnbt_plugin_option_get('webhook_notify', 'url', '')),
		'secret' => (string)mnbt_plugin_option_get('webhook_notify', 'secret', ''),
		'enabled' => mnbt_plugin_option_get('webhook_notify', 'enabled', 'true') === 'true',
		'events' => $events,
	];
}

function webhook_notify_log_line($line)
{
	$log = mnbt_plugin_option_get('webhook_notify', 'delivery_log', []);
	if (!is_array($log)) {
		$log = [];
	}
	array_unshift($log, date('Y-m-d H:i:s') . ' ' . $line);
	$log = array_slice($log, 0, 40);
	mnbt_plugin_option_set('webhook_notify', 'delivery_log', $log);
}

function webhook_notify_sanitize_host($host)
{
	if (!is_array($host)) {
		return [];
	}
	$keep = ['id', 'user', 'ssbt', 'btid', 'sqldz', 'datae', 'qk', 'hxc', 'ymbds', 'data'];
	$out = [];
	foreach ($keep as $k) {
		if (array_key_exists($k, $host)) {
			$out[$k] = $host[$k];
		}
	}
	return $out;
}

function webhook_notify_sanitize_order($order)
{
	if (!is_array($order)) {
		return [];
	}
	$keep = ['id', 'ddh', 'je', 'lx', 'zffs', 'qk', 'date', 'cs', 'ip'];
	$out = [];
	foreach ($keep as $k) {
		if (array_key_exists($k, $order)) {
			$out[$k] = $order[$k];
		}
	}
	return $out;
}

function webhook_notify_dispatch($event, $payload)
{
	$cfg = webhook_notify_config();
	if (!$cfg['enabled'] || $cfg['url'] === '') {
		return;
	}
	$events = $cfg['events'];
	if (isset($events[$event]) && !$events[$event]) {
		return;
	}
	$body = [
		'event' => $event,
		'time' => date('c'),
		'source' => 'mnbt',
		'payload' => $payload,
	];
	$json = json_encode($body, JSON_UNESCAPED_UNICODE);
	$headers = ['Content-Type: application/json; charset=utf-8', 'X-MNBT-Event: ' . $event];
	if ($cfg['secret'] !== '') {
		$sig = hash_hmac('sha256', $json, $cfg['secret']);
		$headers[] = 'X-MNBT-Signature: sha256=' . $sig;
	}
	$res = mnbt_http_post($cfg['url'], $json, [
		'timeout' => 10,
		'headers' => $headers,
		'insecure' => mnbt_plugin_option_get('webhook_notify', 'insecure_ssl', 'false') === 'true',
	]);
	if ($res['ok']) {
		webhook_notify_log_line('OK ' . $event . ' HTTP ' . $res['code']);
	} else {
		webhook_notify_log_line('FAIL ' . $event . ' ' . ($res['error'] ?: ('HTTP ' . $res['code'])));
	}
}

mnbt_add_action('host.created', function ($host, $ctx = []) {
	webhook_notify_dispatch('host.created', [
		'host' => webhook_notify_sanitize_host($host),
		'ctx' => is_array($ctx) ? $ctx : [],
	]);
});

mnbt_add_action('host.paused', function ($host, $ctx = []) {
	webhook_notify_dispatch('host.paused', [
		'host' => webhook_notify_sanitize_host($host),
		'ctx' => is_array($ctx) ? $ctx : [],
	]);
});

mnbt_add_action('host.unpaused', function ($host, $ctx = []) {
	webhook_notify_dispatch('host.unpaused', [
		'host' => webhook_notify_sanitize_host($host),
		'ctx' => is_array($ctx) ? $ctx : [],
	]);
});

mnbt_add_action('host.renewed', function ($host, $ctx = []) {
	webhook_notify_dispatch('host.renewed', [
		'host' => webhook_notify_sanitize_host($host),
		'ctx' => is_array($ctx) ? $ctx : [],
	]);
});

mnbt_add_action('host.deleted', function ($host, $ctx = []) {
	webhook_notify_dispatch('host.deleted', [
		'host' => webhook_notify_sanitize_host($host),
		'ctx' => is_array($ctx) ? $ctx : [],
	]);
});

mnbt_add_action('order.paid', function ($order, $ctx = []) {
	webhook_notify_dispatch('order.paid', [
		'order' => webhook_notify_sanitize_order($order),
		'ctx' => is_array($ctx) ? $ctx : [],
	]);
});

mnbt_register_ajax('admin', 'p_webhook_notify_save', function () {
	mnbt_plugin_require_admin();
	$url = trim((string)($_POST['url'] ?? ''));
	$secret = (string)($_POST['secret'] ?? '');
	$enabled = (($_POST['enabled'] ?? '') === 'true' || ($_POST['enabled'] ?? '') === '1') ? 'true' : 'false';
	$insecure = (($_POST['insecure_ssl'] ?? '') === 'true' || ($_POST['insecure_ssl'] ?? '') === '1') ? 'true' : 'false';
	if ($url !== '' && !preg_match('#^https?://#i', $url)) {
		json_exit_error('Webhook URL 必须以 http:// 或 https:// 开头');
	}
	if (mb_strlen($url) > 500) {
		json_exit_error('URL 过长');
	}
	$eventKeys = ['host.created', 'host.paused', 'host.unpaused', 'host.renewed', 'host.deleted', 'order.paid'];
	$events = [];
	$raw = $_POST['events'] ?? '';
	if (is_string($raw) && $raw !== '') {
		$decoded = json_decode($raw, true);
		if (is_array($decoded)) {
			foreach ($eventKeys as $k) {
				$events[$k] = !empty($decoded[$k]);
			}
		}
	}
	if (!$events) {
		foreach ($eventKeys as $k) {
			$events[$k] = true;
		}
	}
	mnbt_plugin_option_set('webhook_notify', 'url', $url);
	mnbt_plugin_option_set('webhook_notify', 'secret', $secret);
	mnbt_plugin_option_set('webhook_notify', 'enabled', $enabled);
	mnbt_plugin_option_set('webhook_notify', 'insecure_ssl', $insecure);
	mnbt_plugin_option_set('webhook_notify', 'events', $events);
	json_exit_success('已保存');
});

mnbt_register_ajax('admin', 'p_webhook_notify_test', function () {
	mnbt_plugin_require_admin();
	$cfg = webhook_notify_config();
	if ($cfg['url'] === '') {
		json_exit_error('请先填写 Webhook URL 并保存');
	}
	$body = [
		'event' => 'webhook.test',
		'time' => date('c'),
		'source' => 'mnbt',
		'payload' => ['message' => 'MNBT Webhook 测试', 'plugin' => 'webhook_notify'],
	];
	$json = json_encode($body, JSON_UNESCAPED_UNICODE);
	$headers = ['Content-Type: application/json; charset=utf-8', 'X-MNBT-Event: webhook.test'];
	if ($cfg['secret'] !== '') {
		$headers[] = 'X-MNBT-Signature: sha256=' . hash_hmac('sha256', $json, $cfg['secret']);
	}
	$res = mnbt_http_post($cfg['url'], $json, [
		'timeout' => 10,
		'headers' => $headers,
		'insecure' => mnbt_plugin_option_get('webhook_notify', 'insecure_ssl', 'false') === 'true',
	]);
	if ($res['ok']) {
		webhook_notify_log_line('OK webhook.test HTTP ' . $res['code']);
		json_exit_success('测试成功 HTTP ' . $res['code'], ['body' => mb_substr($res['body'], 0, 200)]);
	}
	webhook_notify_log_line('FAIL webhook.test ' . ($res['error'] ?: ('HTTP ' . $res['code'])));
	json_exit_error('测试失败：' . ($res['error'] ?: ('HTTP ' . $res['code'])));
});
