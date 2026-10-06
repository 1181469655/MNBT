<?php
/**
 * geetest_captcha 插件入口 —— 极验行为验证 4.0
 *
 * 通过 MNBT 验证码 provider 扩展点接管三端登录人机验证（"安装即退位"）：
 *  - 启用并配置 captchaId / captcha_key 后，登录验证自动切换为极验 4.0；
 *  - 停用插件或未配置密钥时，内置滑块验证码自动复位；
 *  - 若已有其他验证码插件生效（priority 更早），本插件自动让位。
 *
 * 前端协议见 adapter.js；服务端二次校验协议：
 *   POST https://gcaptcha4.geetest.com/validate
 *   sign_token = hash_hmac('sha256', lot_number, captcha_key)
 *   result === 'success' 视为通过（网络异常按失败处理，fail-closed）
 */
if (!defined('IN_CRONLITE')) exit;

if (!function_exists('geetest4_cfg')) {
	/** 插件配置读取（显式 slug，不依赖插件上下文） */
	function geetest4_cfg(string $key, string $default = ''): string
	{
		return (string)mnbt_plugin_option_get('geetest_captcha', $key, $default);
	}
}

if (!function_exists('geetest4_ready')) {
	/** 是否已完成配置（配置齐全才能接管） */
	function geetest4_ready(): bool
	{
		return geetest4_cfg('captcha_id') !== '' && geetest4_cfg('captcha_key') !== '';
	}
}

if (!function_exists('geetest4_second_validate')) {
	/**
	 * 极验服务端二次校验
	 * @param string $payload 前端提交的验证结果 JSON（lot_number/captcha_output/pass_token/gen_time）
	 * @return bool|string true=通过；字符串=拒绝并作为用户提示文案
	 */
	function geetest4_second_validate(string $payload, string $captchaId, string $captchaKey)
	{
		$data = json_decode($payload, true);
		if (!is_array($data)) {
			return '人机验证失败，请重试';
		}
		$lot = (string)($data['lot_number'] ?? '');
		$captchaOutput = (string)($data['captcha_output'] ?? '');
		$passToken = (string)($data['pass_token'] ?? '');
		$genTime = (string)($data['gen_time'] ?? '');
		if ($lot === '' || $captchaOutput === '' || $passToken === '' || $genTime === '') {
			return '人机验证失败，请重试';
		}
		$body = http_build_query([
			'lot_number'     => $lot,
			'captcha_output' => $captchaOutput,
			'pass_token'     => $passToken,
			'gen_time'       => $genTime,
			'captcha_id'     => $captchaId,
			'sign_token'     => hash_hmac('sha256', $lot, $captchaKey),
		]);
		$rs = mnbt_http_request('POST', 'https://gcaptcha4.geetest.com/validate', $body, [
			'headers' => ['Content-Type: application/x-www-form-urlencoded; charset=utf-8'],
			'timeout' => 8,
		]);
		if (empty($rs['ok']) || $rs['body'] === '') {
			// fail-closed：校验接口不可达时不放行
			error_log('[geetest_captcha] 二次校验请求失败: ' . (string)($rs['error'] ?: $rs['code']));
			return '人机验证服务暂不可用，请稍后重试';
		}
		$json = json_decode($rs['body'], true);
		if (!is_array($json)) {
			return '人机验证失败，请重试';
		}
		if (($json['result'] ?? '') === 'success') {
			return true;
		}
		error_log('[geetest_captcha] 二次校验未通过: ' . (string)($json['reason'] ?? 'unknown'));
		return '人机验证失败，请重试';
	}
}

mnbt_plugin_register('geetest_captcha', ['name' => '极验行为验证 4.0']);

/* ============================================================
 * 后台菜单与设置页
 * ============================================================ */
// 单入口：不带 children，自动归入侧栏「插件管理」分组
mnbt_register_menu('admin', [
	'title'     => '极验行为验证 4.0',
	'page'      => 'settings',
	'icon'      => 'mdi-shield-check',
	'order'     => 28,
	'multitabs' => true,
]);
mnbt_register_page('admin', 'settings', 'admin/settings.php', '极验行为验证 4.0 设置');
mnbt_register_settings_tab([
	'title' => '极验行为验证 4.0',
	'page'  => 'settings',
	'order' => 28,
]);

/** 保存配置 */
mnbt_register_ajax('admin', 'p_geetest4_setting_save', function () {
	mnbt_plugin_require_admin();
	$captchaId = trim((string)($_POST['captcha_id'] ?? ''));
	$captchaKey = trim((string)($_POST['captcha_key'] ?? ''));
	if ($captchaId === '' || $captchaKey === '') {
		json_exit_error('captchaId 和 captcha_key 不能为空');
	}
	mnbt_plugin_option_set('geetest_captcha', 'captcha_id', $captchaId);
	mnbt_plugin_option_set('geetest_captcha', 'captcha_key', $captchaKey);
	json_exit_success('保存成功');
});

/* ============================================================
 * 验证码 provider 接管
 * ============================================================ */
mnbt_add_filter('captcha.provider', function ($provider) {
	if ($provider) {
		// 其他验证码插件已生效，让位
		return $provider;
	}
	if (!geetest4_ready()) {
		return null; // 未配置密钥，退回内置验证码
	}
	$captchaId = geetest4_cfg('captcha_id');
	$captchaKey = geetest4_cfg('captcha_key');
	return [
		'id'     => 'geetest4',
		'field'  => 'captchaToken',
		'verify' => function (string $payload) use ($captchaId, $captchaKey) {
			return geetest4_second_validate($payload, $captchaId, $captchaKey);
		},
	];
});

/* ============================================================
 * 前端注入：SPA boot 携带适配器与 captchaId
 * ============================================================ */
mnbt_add_filter('spa.boot', function ($boot) {
	// 仅当 provider 最终归本插件时注入 UI（避免与其他验证码插件冲突）
	$provider = mnbt_apply_filters('captcha.provider', null);
	if (!is_array($provider) || ($provider['id'] ?? '') !== 'geetest4') {
		return $boot;
	}
	$boot['captcha'] = [
		'provider'  => 'geetest4',
		'adapter'   => mnbt_plugin_url('geetest_captcha', 'adapter.js'),
		'captchaId' => geetest4_cfg('captcha_id'),
		// 内联模式：HumanVerify 用极验官方按钮替换自带的"点击进行人机验证"按钮
		'inline'    => true,
	];
	return $boot;
});
