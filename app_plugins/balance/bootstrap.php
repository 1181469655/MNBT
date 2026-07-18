<?php
/**
 * balance 插件 - 主入口
 *
 * 功能：余额查询、充值（调用支付插件 API）、流水记录
 * 依赖：user_info 插件（认证）、支付插件（epay/alipay_official）
 * 架构：通过 P2 路由注册 /balance/* 路径；通过 order.paid 钩子处理充值结算
 */

if (!defined('IN_CRONLITE')) {
	exit;
}

require_once __DIR__ . '/lib/balance.php';

mnbt_plugin_register('balance', [
	'name' => '余额管理',
	'description' => '用户余额、充值、流水',
]);

/* ============================================================
 *  order.paid 钩子：处理充值订单结算
 * ============================================================
 *  支付插件回调验签后调 mnbt_pay_settle_order()，核心标记订单完成
 *  并触发 order.paid。此处检查 lx=recharge，增加用户余额。
 */
mnbt_add_action('order.paid', function ($order_row, $ctx = []) {
	if (!is_array($order_row)) {
		return;
	}
	if (($order_row['lx'] ?? '') !== 'recharge') {
		return;
	}
	$cs = json_decode($order_row['cs'] ?? '', true);
	if (!is_array($cs)) {
		return;
	}
	$user_id = (int)($cs['user_id'] ?? 0);
	$amount_cents = (int)($cs['amount'] ?? 0);
	if ($user_id <= 0 || $amount_cents <= 0) {
		return;
	}
	// 防重复：检查该订单是否已入账
	$exists = $GLOBALS['DB']->get_row_prepare(
		"SELECT id FROM MN_plugin_balance_log WHERE user_id=? AND order_no=? AND type='recharge' LIMIT 1",
		[$user_id, $order_row['ddh']]
	);
	if ($exists) {
		return;
	}
	balance_add($user_id, $amount_cents, 'recharge', $order_row['ddh'], '余额充值');
}, 10);

/* ============================================================
 *  页面路由
 * ============================================================ */

// 余额首页（显示余额 + 流水）
mnbt_register_route('GET', '/balance', function ($params, $ctx) {
	$user = balance_require_user();
	$user_id = (int)$user['id'];
	$balance = balance_get($user_id);

	$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
	$logs = balance_logs($user_id, $page, 15);

	balance_render('balance', [
		'page_title' => '我的余额',
		'balance_cents' => $balance,
		'logs' => $logs,
	]);
});

// 充值页面
mnbt_register_route('GET', '/balance/recharge', function ($params, $ctx) {
	$user = balance_require_user();

	// 获取已启用的支付方式，排除余额支付自身（充值不能用余额付，循环）
	$methods = [];
	if (function_exists('mnbt_get_enabled_payment_methods')) {
		$all = mnbt_get_enabled_payment_methods();
		foreach ($all as $m) {
			if (($m['plugin'] ?? '') === 'balance') {
				continue;
			}
			$methods[] = $m;
		}
	}

	balance_render('recharge', [
		'page_title' => '余额充值',
		'methods' => $methods,
	]);
});

/* ============================================================
 *  API 路由
 * ============================================================ */

// 创建充值订单 → 调用支付插件
mnbt_register_route('POST', '/balance/api/create_recharge', function ($params, $ctx) {
	global $DB, $date, $siteurl;

	$user = balance_require_user();
	$user_id = (int)$user['id'];

	$amount_yuan = isset($_POST['amount']) ? (float)$_POST['amount'] : 0;
	$type = isset($_POST['type']) ? trim($_POST['type']) : '';

	// 验证金额（最低 1 元，最高 50000 元）
	if ($amount_yuan < 1) {
		balance_json('充值金额至少 1 元');
	}
	if ($amount_yuan > 50000) {
		balance_json('单次充值金额不能超过 50000 元');
	}
	$amount_cents = (int)round($amount_yuan * 100);

	// 验证支付方式
	if ($type === '' || !function_exists('mnbt_pay_parse_type') || !mnbt_pay_parse_type($type)) {
		balance_json('请选择有效的支付方式');
	}
	// 充值订单禁止使用余额支付（循环）
	$parsed_type = mnbt_pay_parse_type($type);
	if ($parsed_type && $parsed_type['plugin'] === 'balance') {
		balance_json('充值订单不能使用余额支付');
	}

	// 创建订单（MN_dd 表）
	$out_trade_no = date("YmdHis") . mt_rand(100, 999);
	$cs = json_encode([
		'user_id' => $user_id,
		'amount' => $amount_cents,
		'username' => $user['username'],
	], 256);
	$ip = $_SERVER["REMOTE_ADDR"] ?? '127.0.0.1';

	$row1 = $DB->get_row_prepare("SELECT * FROM MN_dd WHERE 1 order by id desc limit 1");
	$id = $row1 ? ((int)$row1['id'] + 1) : 1;
	$ok = $DB->query_prepare(
		"INSERT INTO MN_dd (id, cs, date, zffs, je, ddh, lx, qk, ip) VALUES (?,?,?,?,?,?,?,?,?)",
		[$id, $cs, $date, $type, $amount_yuan, $out_trade_no, 'recharge', 'false', $ip]
	);
	if (!$ok) {
		balance_json('创建订单失败，请稍后重试');
	}

	// 分发到支付插件
	$order_context = [
		'out_trade_no' => $out_trade_no,
		'name' => '余额充值',
		'money' => (string)$amount_yuan,
		'type' => $type,
		'siteurl' => $siteurl,
		'pay_lx' => 'recharge',
	];

	$html = mnbt_pay_dispatch_gateway($type, $order_context);
	if ($html === false) {
		balance_json('支付方式不可用，请检查支付插件是否已启用');
	}

	// 返回支付 HTML，前端用 document.write 输出跳转
	balance_json('正在跳转到支付页面', ['html' => $html]);
});

/* ============================================================
 *  余额支付（作为支付插件注册）
 * ============================================================
 *  把"余额扣款"做成标准支付方式（type = balance__balance），
 *  供 hosting_shop 等业务插件在下单页直接选用。
 *
 *  流程：
 *    1. 业务插件创建 MN_dd 订单 → mnbt_pay_dispatch_gateway('balance__balance', ...)
 *    2. 本插件 build 回调预检（登录、余额、非充值单）后返回自动提交表单
 *    3. 表单 POST 到 /pay/balance/pay → 校验订单归属 → 原子扣款 → mnbt_pay_settle_order()
 *    4. 结算成功后 order.paid 钩子触发业务插件处理（如 hosting_shop 开通主机）
 *    5. 展示支付结果页
 */
mnbt_register_payment('balance', [
	'name'        => '余额支付',
	'description' => '使用账户余额直接付款（需先充值）',
	'icon'        => 'mdi-wallet',
	'methods'     => [
		'balance' => ['name' => '余额支付', 'icon' => 'mdi-cash'],
	],
	'build' => function ($method, $order, $plugin_config) {
		// 1. 充值订单禁止使用余额支付（循环）
		if (($order['pay_lx'] ?? '') === 'recharge') {
			return balance_pay_render_error('充值订单不能使用余额支付');
		}
		// 2. 依赖 user_info 插件
		if (!function_exists('user_info_auth_current')) {
			return balance_pay_render_error('需要先启用 user_info 插件');
		}
		$user = user_info_auth_current();
		if (!$user) {
			// 未登录 → 跳转登录页
			$loginUrl = balance_url('account/login');
			return '<!DOCTYPE html><html><head><meta charset="UTF-8"></head><body><script>window.location.href="' . $loginUrl . '";</script></body></html>';
		}
		// 3. 余额预检
		$amount_cents = (int)round((float)($order['money'] ?? 0) * 100);
		$balance = balance_get($user['id']);
		if ($amount_cents <= 0) {
			return balance_pay_render_error('订单金额异常');
		}
		if ($balance < $amount_cents) {
			return balance_pay_render_error(
				'余额不足，当前余额 ¥' . balance_format($balance) . '，本次需支付 ¥' . balance_format($amount_cents),
				balance_url('balance/recharge')
			);
		}
		// 4. 构造自动提交表单 → /pay/balance/pay
		$outTradeNo = htmlspecialchars($order['out_trade_no'], ENT_QUOTES, 'UTF-8');
		$payUrl = balance_url('pay/balance/pay');
		return <<<HTML
<!DOCTYPE html>
<html lang="zh-CN"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>余额支付</title></head>
<body style="font-family:sans-serif;text-align:center;padding:60px;background:#f5f7fa;">
<p style="color:#475569;">正在使用余额支付，请稍候...</p>
<form id="payform" method="POST" action="{$payUrl}">
<input type="hidden" name="out_trade_no" value="{$outTradeNo}">
<noscript><button type="submit">点击继续</button></noscript>
</form>
<script>document.getElementById('payform').submit();</script>
</body></html>
HTML;
	},
]);

/* 余额支付处理路由：原子扣款 + 统一结算 */
mnbt_register_route('POST', '/pay/balance/pay', function ($params, $ctx) {
	global $DB;

	// 1. 登录校验
	if (!function_exists('user_info_auth_current')) {
		balance_pay_show_result('error', '需要先启用 user_info 插件');
		return;
	}
	$user = user_info_auth_current();
	if (!$user) {
		balance_pay_show_result('error', '请先登录', ['redirect' => balance_url('account/login'), 'redirect_text' => '去登录']);
		return;
	}

	// 2. 取订单号并查单
	$out_trade_no = isset($_POST['out_trade_no']) ? trim((string)$_POST['out_trade_no']) : '';
	if ($out_trade_no === '') {
		balance_pay_show_result('error', '订单号缺失');
		return;
	}
	$order = $DB->get_row_prepare("SELECT * FROM MN_dd WHERE ddh=? LIMIT 1", [$out_trade_no]);
	if (!$order) {
		balance_pay_show_result('error', '订单不存在');
		return;
	}

	// 3. 订单状态校验
	if ((string)$order['qk'] === 'true') {
		balance_pay_show_result('error', '订单已支付，无需重复付款');
		return;
	}

	// 4. 支付方式校验：必须为 balance__balance，防止伪造请求用余额去结其他支付方式的单
	$parsed = function_exists('mnbt_pay_parse_type') ? mnbt_pay_parse_type($order['zffs']) : false;
	if (!$parsed || $parsed['plugin'] !== 'balance') {
		balance_pay_show_result('error', '订单支付方式非余额支付');
		return;
	}

	// 5. 订单归属校验：cs.user_id 必须等于当前用户 id
	$cs = json_decode($order['cs'] ?? '', true);
	if (!is_array($cs) || (int)($cs['user_id'] ?? 0) !== (int)$user['id']) {
		balance_pay_show_result('error', '订单不属于当前用户');
		return;
	}

	// 6. 充值单拒绝（双保险，build 已挡过一次）
	if (($order['lx'] ?? '') === 'recharge') {
		balance_pay_show_result('error', '充值订单不能使用余额支付');
		return;
	}

	// 7. 原子扣款（WHERE balance >= ? 保证余额不足时失败）
	$amount_cents = (int)round((float)$order['je'] * 100);
	if ($amount_cents <= 0) {
		balance_pay_show_result('error', '订单金额异常');
		return;
	}
	$ok = balance_deduct(
		$user['id'],
		$amount_cents,
		'consume',
		$out_trade_no,
		'余额支付：' . ($order['lx'] ?? '')
	);
	if (!$ok) {
		balance_pay_show_result(
			'error',
			'余额不足，扣款失败',
			['redirect' => balance_url('balance/recharge'), 'redirect_text' => '去充值']
		);
		return;
	}

	// 8. 统一结算：标记订单完成 + 触发 order.paid 钩子（由业务插件处理后续业务）
	$result = mnbt_pay_settle_order($out_trade_no, 'TRADE_SUCCESS', (string)$order['je']);
	if (empty($result['ok'])) {
		// 扣款成功但结算异常 → 记录日志并提示联系管理员（余额已扣，需人工处理）
		@error_log('[balance] settle failed but deducted: order=' . $out_trade_no . ' user=' . $user['id'] . ' amount=' . $amount_cents . ' msg=' . ($result['msg'] ?? ''));
		balance_pay_show_result('error', '扣款成功但订单结算异常，请联系管理员。订单号：' . $out_trade_no);
		return;
	}

	// 9. 成功
	balance_pay_show_result('success', '支付成功', [
		'order_no' => $out_trade_no,
		'amount'   => (string)$order['je'],
	]);
});

/* ============================================================
 *  管理员端页面注册
 * ============================================================ */

mnbt_register_page('admin', 'balances', 'views/admin/balances.php', '余额管理');
mnbt_register_page('admin', 'balance_logs', 'views/admin/logs.php', '余额流水');

mnbt_register_menu('admin', [
	'title' => '余额管理',
	'icon'  => 'mdi-wallet',
	'order' => 71,
	'children' => [
		['title' => '用户余额', 'page' => 'balances', 'icon' => 'mdi-cash-multiple', 'multitabs' => true],
		['title' => '流水记录', 'page' => 'balance_logs', 'icon' => 'mdi-history', 'multitabs' => true],
	],
]);

// 管理员端 AJAX：调整用户余额
mnbt_register_ajax('admin', 'balance_admin_adjust', function () {
	mnbt_plugin_require_admin();
	$user_id = (int)($_POST['user_id'] ?? 0);
	$amount_yuan = (float)($_POST['amount'] ?? 0);
	$direction = $_POST['direction'] ?? '';   // add / deduct
	$remark = trim((string)($_POST['remark'] ?? ''));

	if ($user_id <= 0) {
		json_exit('参数错误');
	}
	if ($amount_yuan <= 0) {
		json_exit('金额必须大于 0');
	}
	if (!in_array($direction, ['add', 'deduct'], true)) {
		json_exit('操作类型错误');
	}
	$amount_cents = (int)round($amount_yuan * 100);
	if ($amount_cents <= 0) {
		json_exit('金额必须大于 0');
	}
	$remark = $remark === '' ? '管理员调整' : $remark;

	if ($direction === 'add') {
		$ok = balance_add($user_id, $amount_cents, 'adjust', '', '管理员加款：' . $remark);
	} else {
		$ok = balance_deduct($user_id, $amount_cents, 'adjust', '', '管理员扣款：' . $remark);
	}
	if (!$ok) {
		json_exit($direction === 'deduct' ? '扣款失败（余额不足）' : '加款失败');
	}
	json_exit('调整成功');
});
