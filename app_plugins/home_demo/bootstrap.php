<?php
if (!defined('IN_CRONLITE')) {
	exit;
}

mnbt_plugin_register('home_demo', ['name' => '首页接管示例']);

// 后台菜单
mnbt_register_menu('admin', [
	'title' => '首页接管示例',
	'page' => 'index',
	'icon' => 'mdi-home',
	'order' => 25,
	'multitabs' => true,
]);

// 后台设置页
mnbt_register_page('admin', 'index', 'admin/index.php', '首页接管示例');

// 插件管理页快捷入口
mnbt_register_settings_tab([
	'title' => '首页接管设置',
	'page' => 'index',
	'order' => 30,
]);

// AJAX：保存配置
mnbt_register_ajax('admin', 'p_home_demo_save', function () {
	mnbt_plugin_require_admin();
	$mode = isset($_POST['mode']) ? (string)$_POST['mode'] : 'off';
	if (!in_array($mode, ['off', 'redirect', 'render'], true)) {
		json_exit_error('模式无效');
	}
	$target = isset($_POST['redirect_target']) ? trim((string)$_POST['redirect_target']) : '/user';
	if ($mode === 'redirect' && $target === '') {
		json_exit_error('重定向目标不能为空');
	}
	if (mb_strlen($target) > 500) {
		json_exit_error('重定向目标过长');
	}
	mnbt_plugin_option_set('home_demo', 'mode', $mode);
	mnbt_plugin_option_set('home_demo', 'redirect_target', $target);
	json_exit_success('已保存', ['mode' => $mode]);
});

// ============================================================
//  首页接管：根据后台配置决定 / 的响应
// ============================================================
mnbt_register_home(function ($ctx) {
	$mode = mnbt_plugin_option_get('home_demo', 'mode', 'off');

	if ($mode === 'off') {
		// 不接管，回退到默认 /user 跳转
		return false;
	}

	if ($mode === 'redirect') {
		// 返回字符串 → 引擎自动 header("Location: ...") + exit
		$target = mnbt_plugin_option_get('home_demo', 'redirect_target', '/user');
		return $target;
	}

	if ($mode === 'render') {
		// 直接渲染自定义首页
		$tpl = mnbt_plugin_path('home_demo') . 'user/home.php';
		if (is_file($tpl)) {
			// 这些变量在模板中可能用到
			$info = $ctx;
			include $tpl;
		} else {
			header('Content-Type: text/html; charset=UTF-8');
			echo '<!doctype html><meta charset="utf-8"><title>首页</title>';
			echo '<h1>首页接管示例</h1><p>模板文件缺失：user/home.php</p>';
		}
		return true;  // 已渲染，引擎 exit
	}

	return false;
});

// ============================================================
//  通用路由：/landing 活动落地页演示
// ============================================================
mnbt_register_route('GET', '/landing', function ($params, $ctx) {
	$tpl = mnbt_plugin_path('home_demo') . 'user/landing.php';
	if (is_file($tpl)) {
		$info = $ctx;
		include $tpl;
	} else {
		header('Content-Type: text/html; charset=UTF-8');
		echo '<!doctype html><meta charset="utf-8"><title>落地页</title>';
		echo '<h1>活动落地页</h1><p>模板文件缺失：user/landing.php</p>';
	}
	// 不返回 → 视为已处理，引擎 exit
});

// 带参数的路由演示：/promo/{id}
mnbt_register_route('GET', '/promo/{id}', function ($params, $ctx) {
	header('Content-Type: text/html; charset=UTF-8');
	$id = isset($params['id']) ? $params['id'] : '';
	$base = isset($ctx['base']) ? $ctx['base'] : '';
	echo '<!doctype html><meta charset="utf-8"><title>推广 ' . htmlspecialchars($id, ENT_QUOTES, 'UTF-8') . '</title>';
	echo '<body style="font-family:sans-serif;padding:2rem;line-height:1.6">';
	echo '<h1>推广页</h1>';
	echo '<p>推广 ID：<code>' . htmlspecialchars($id, ENT_QUOTES, 'UTF-8') . '</code></p>';
	echo '<p>这是 <code>mnbt_register_route(\'GET\', \'/promo/{id}\', ...)</code> 的演示。</p>';
	echo '<p><a href="' . htmlspecialchars($base . '/', ENT_QUOTES, 'UTF-8') . '">返回首页</a></p>';
	echo '</body>';
});
