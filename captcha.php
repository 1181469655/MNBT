<?php
/**
 * 行为验证码统一端点（fastknife/ajcaptcha，官方前端组件协议）
 *
 *  action=get   获取验证码：底图 base64 + token + secretKey（缺口坐标仅存服务端缓存）
 *  action=check 一次验证：token + pointJson（AES 加密坐标）→ 返回 captchaVerification
 *
 * captchaVerification 由业务登录接口（docker/admin/user）用
 * mnbt_captcha_verify() 做二次校验，验证后立即销毁，防重放。
 * 本端点为登录前置公开接口，无登录态要求，按 IP 限频。
 */
declare(strict_types=1);

include __DIR__ . '/MPHX/common.php';

@header('Content-Type: application/json; charset=UTF-8');

$action = $_GET['action'] ?? $_POST['action'] ?? '';
$captchaType = $_REQUEST['captchaType'] ?? 'blockPuzzle';
if (!in_array($captchaType, ['blockPuzzle', 'clickWord'], true)) {
	$captchaType = 'blockPuzzle';
}

function mnbt_captcha_reply(bool $ok, $data, ?string $msg): void
{
	echo json_encode([
		'error' => !$ok,
		'repCode' => $ok ? '0000' : '6111',
		'repData' => $data,
		'repMsg' => $msg,
		'success' => $ok,
	], JSON_UNESCAPED_UNICODE);
	exit;
}

$ip = mnbt_client_ip();

// 插件验证码接管时，内置端点退位（防止插件模式下内置取码/校验流程仍可探测）
if (mnbt_captcha_provider()) {
	mnbt_captcha_reply(false, null, '验证码已由插件接管');
}

if ($action === 'get') {
	// 取码限频：正常用户几十次/分钟用不完，防脚本批量取码
	if (mnbt_throttle_exceeded('captcha_get', $ip, 30, 60)) {
		mnbt_captcha_reply(false, null, '请求过于频繁，请稍后再试');
	}
	mnbt_throttle_hit('captcha_get', $ip, 60);
	try {
		$data = mnbt_captcha_service($captchaType)->get();
		mnbt_captcha_reply(true, $data, null);
	} catch (\Throwable $e) {
		mnbt_captcha_reply(false, null, '获取验证码失败，请重试');
	}
}

if ($action === 'check') {
	if (mnbt_throttle_exceeded('captcha_check', $ip, 60, 60)) {
		mnbt_captcha_reply(false, null, '请求过于频繁，请稍后再试');
	}
	mnbt_throttle_hit('captcha_check', $ip, 60);
	$token = (string)($_REQUEST['token'] ?? '');
	$pointJson = (string)($_REQUEST['pointJson'] ?? '');
	if ($token === '' || $pointJson === '') {
		mnbt_captcha_reply(false, null, '参数错误');
	}
	try {
		$verification = mnbt_captcha_service($captchaType)->check($token, $pointJson);
		mnbt_captcha_reply(true, ['captchaVerification' => $verification], null);
	} catch (\Throwable $e) {
		mnbt_captcha_reply(false, null, '验证失败，请重试');
	}
}

mnbt_captcha_reply(false, null, '未知操作');
