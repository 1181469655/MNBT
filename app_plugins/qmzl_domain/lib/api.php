<?php
/**
 * qmzl_domain 插件 - 启明智联平台 API 客户端
 *
 * 上游：https://cloud.qimingidc.cn/console/v1（地址写死）
 * 认证：Authorization: Bearer {jwt}（用户各自绑定账号，密码登录获取）
 *
 * 覆盖接口（参考 qmzl_domain/域名注册API对接文档.md 与 idcsmart-api.md）：
 *   POST /login                                登录（密码方式）
 *   GET  /idcsmart_domain/config               域名配置
 *   GET  /idcsmart_domain/domain_suffix        后缀列表
 *   GET  /idcsmart_domain/check_domain         可用性
 *   GET  /idcsmart_domain/get_price            价格
 *   GET  /idcsmart_domain/info_template        模板列表
 *   POST/PUT /idcsmart_domain/info_template    创建/更新模板（multipart）
 *   DELETE /idcsmart_domain/info_template/{id} 删除模板
 *   POST /idcsmart_domain/info_template/{id}/certifications 提交实名认证
 *   GET  /product/group/first | /product/group/second | /product  域名产品 ID
 *   GET  /gateway                              支付网关
 *   POST /cart                                 加入购物车
 *   POST /cart/settle                          结算创建订单
 *   POST /pay                                  发起支付（返回三方 HTML）
 *   GET  /pay/{id}/status                      支付状态
 *   GET  /host                                 已购产品（域名）
 */
if (!defined('IN_CRONLITE')) exit;

/** 上游 API 地址（写死） */
define('QMZL_API_BASE', 'https://cloud.qimingidc.cn/console/v1');
/** 上游站点根（支付页跳转兜底用） */
define('QMZL_SITE', 'https://cloud.qimingidc.cn');

/* ============================================================
 * 1. HTTP 基础
 * ============================================================ */

/**
 * 统一解析上游响应
 * @param array $res mnbt_http_* / curl 的结果数组
 * @return array {ok,status,msg,data}
 */
function qmzl_parse_response($res)
{
	$out = ['ok' => false, 'status' => 0, 'msg' => '', 'data' => null, 'code' => null];
	$body = trim((string)($res['body'] ?? ''));
	$j = null;
	if ($body !== '') {
		$j = json_decode($body, true);
	}
	if (empty($res['ok'])) {
		if (is_array($j) && isset($j['status'])) {
			$out['status'] = (int)$j['status'];
			$out['msg'] = (string)($j['msg'] ?? '');
			$out['data'] = $j['data'] ?? null;
			$out['code'] = $j['code'] ?? null;
			$out['ok'] = $out['status'] === 200;
			return $out;
		}
		$out['msg'] = $res['error'] ?: ('HTTP ' . $res['code']);
		return $out;
	}
	if ($body === '') {
		$out['msg'] = '上游返回空响应';
		return $out;
	}
	if (!is_array($j)) {
		$out['msg'] = '上游返回非 JSON 内容';
		return $out;
	}
	$out['status'] = isset($j['status']) ? (int)$j['status'] : 0;
	$out['msg'] = (string)($j['msg'] ?? '');
	$out['data'] = $j['data'] ?? null;
	$out['code'] = $j['code'] ?? null;
	$out['ok'] = $out['status'] === 200;
	return $out;
}

/**
 * JSON 请求（GET/POST/PUT/DELETE）
 */
function qmzl_http_json($method, $path, $token = '', $body = null, $params = [])
{
	$url = QMZL_API_BASE . '/' . ltrim($path, '/');
	if ($params) {
		$url .= (strpos($url, '?') === false ? '?' : '&') . http_build_query($params);
	}
	$headers = [];
	if ($token !== '') {
		$headers[] = 'Authorization: Bearer ' . $token;
	}
	$method = strtoupper($method ?: 'GET');
	if ($method === 'GET') {
		$res = mnbt_http_get($url, ['timeout' => 25, 'headers' => $headers]);
	} elseif ($method === 'POST') {
		$res = mnbt_http_post($url, $body, ['timeout' => 25, 'headers' => $headers]);
	} else {
		// PUT / DELETE：手动 curl
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
		curl_setopt($ch, CURLOPT_TIMEOUT, 25);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($ch, CURLOPT_USERAGENT, 'MNBT-Plugin/1.83');
		if ($body !== null) {
			$headers[] = 'Content-Type: application/json; charset=utf-8';
			curl_setopt($ch, CURLOPT_POSTFIELDS, is_array($body) ? json_encode($body, JSON_UNESCAPED_UNICODE) : $body);
		}
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		$resp = curl_exec($ch);
		$code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$err = curl_error($ch);
		curl_close($ch);
		$res = ['ok' => $code >= 200 && $code < 300, 'code' => $code, 'body' => (string)$resp, 'error' => $err];
	}
	return qmzl_parse_response($res);
}

/**
 * multipart/form-data 请求（模板创建/更新、提交实名认证需传证件照片）
 */
function qmzl_http_multipart($method, $path, $token, $fields = [], $files = [])
{
	$url = QMZL_API_BASE . '/' . ltrim($path, '/');
	$post = $fields;
	foreach ($files as $k => $f) {
		if (!empty($f['tmp_name']) && is_file($f['tmp_name'])) {
			$post[$k] = new CURLFile(
				$f['tmp_name'],
				($f['type'] && $f['type'] !== 'application/octet-stream') ? $f['type'] : 'application/octet-stream',
				$f['name']
			);
		}
	}
	$headers = [];
	if ($token !== '') {
		$headers[] = 'Authorization: Bearer ' . $token;
	}
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method ?: 'POST'));
	curl_setopt($ch, CURLOPT_TIMEOUT, 90);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
	curl_setopt($ch, CURLOPT_USERAGENT, 'MNBT-Plugin/1.83');
	curl_setopt($ch, CURLOPT_POSTFIELDS, $post); // 数组+文件 → 自动 multipart
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	$resp = curl_exec($ch);
	$code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
	$err = curl_error($ch);
	curl_close($ch);
	return qmzl_parse_response(['ok' => $code >= 200 && $code < 300, 'code' => $code, 'body' => (string)$resp, 'error' => $err]);
}

/* ============================================================
 * 2. 账号与 Token
 * ============================================================ */

/** 密码加密（与系统 authcode 保持一致，SYS_KEY 做密钥） */
function qmzl_encrypt_pwd($pwd)
{
	if ($pwd === '') return '';
	return authcode($pwd, 'ENCODE', SYS_KEY);
}

function qmzl_decrypt_pwd($enc)
{
	if ($enc === '') return '';
	return authcode($enc, 'DECODE', SYS_KEY);
}

/**
 * 上游登录密码加密（AES-128-CBC，与 qiming-web AuthView.vue 一致）
 * key=idcsmart.finance  iv=9311019310287172  输出 base64
 */
function qmzl_encrypt_upstream_password($password)
{
	if (!function_exists('openssl_encrypt')) {
		return '';
	}
	$enc = openssl_encrypt((string)$password, 'aes-128-cbc', 'idcsmart.finance', OPENSSL_RAW_DATA, '9311019310287172');
	return $enc === false ? '' : base64_encode($enc);
}

/** 解析 JWT 的 exp */
function qmzl_jwt_exp($jwt)
{
	if (!is_string($jwt) || $jwt === '') return 0;
	$parts = explode('.', $jwt);
	if (count($parts) < 2) return 0;
	$payload = strtr($parts[1], '-_', '+/');
	$j = json_decode((string)base64_decode($payload), true);
	if (is_array($j) && isset($j['exp'])) return (int)$j['exp'];
	return 0;
}

/**
 * 密码方式登录（上游登录需要人机验证时传入 $captcha/$captchaToken）
 * @return array {ok,status,msg,data:{jwt,exp,client_id}}
 */
function qmzl_login($account, $password, $captcha = '', $captchaToken = '')
{
	$enc = qmzl_encrypt_upstream_password($password);
	if ($enc === '') {
		return ['ok' => false, 'status' => 0, 'msg' => '服务器不支持密码加密（缺少 openssl 扩展）', 'data' => null];
	}
	$body = [
		'type'              => 'password',
		'account'           => (string)$account,
		'password'          => $enc,
		'remember_password' => '1',
	];
	if ($captcha !== '' && $captchaToken !== '') {
		$body['captcha'] = (string)$captcha;
		$body['token']   = (string)$captchaToken;
	}
	$res = qmzl_http_json('POST', 'login', '', $body);
	if (!$res['ok']) {
		return ['ok' => false, 'status' => $res['status'], 'msg' => $res['msg'] ?: '登录失败', 'data' => null];
	}
	$jwt = is_array($res['data']) ? (string)($res['data']['jwt'] ?? '') : '';
	if ($jwt === '') {
		return ['ok' => false, 'status' => 0, 'msg' => '登录成功但未返回 token', 'data' => null];
	}
	$exp = qmzl_jwt_exp($jwt);
	return [
		'ok'     => true,
		'status' => 200,
		'msg'    => '登录成功',
		'data'   => [
			'jwt'       => $jwt,
			'exp'       => $exp ?: (time() + 7200),
			'client_id' => (int)(is_array($res['data']) ? ($res['data']['client_id'] ?? 0) : 0),
		],
	];
}

/**
 * 获取上游人机验证码图片（QmCaptcha 点选验证，路径在站点根，不在 /console/v1 下）。
 * 使用浏览器头规避反爬，返回字段宽松解析（部分环境不返回 status）。
 * @return array {ok,msg,data:{base64,captcha_icon,sign}}
 */
function qmzl_captcha_refresh()
{
	$url = rtrim(QMZL_SITE, '/') . '/captcha/qm_captcha/index/refresh';
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_TIMEOUT, 25);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');
	curl_setopt($ch, CURLOPT_HTTPHEADER, [
		'Accept: application/json',
		'Referer: ' . rtrim(QMZL_SITE, '/') . '/',
	]);
	$resp = curl_exec($ch);
	$err  = curl_error($ch);
	curl_close($ch);

	$j = json_decode(trim((string)$resp), true);
	if (!is_array($j)) {
		return ['ok' => false, 'msg' => $err ?: '获取验证码失败（接口返回异常）', 'data' => null];
	}
	$base64 = (string)($j['base64'] ?? '');
	$sign   = (string)($j['sign'] ?? '');
	if ($base64 === '' || $sign === '') {
		return ['ok' => false, 'msg' => (string)($j['msg'] ?? '获取验证码失败'), 'data' => null];
	}
	return ['ok' => true, 'msg' => '', 'data' => [
		'base64'       => $base64,
		'captcha_icon' => (string)($j['captcha_icon'] ?? ''),
		'sign'         => $sign,
	]];
}

/** 校验人机验证码（点选坐标） */
function qmzl_captcha_verify($captcha, $token)
{
	$url = rtrim(QMZL_SITE, '/') . '/captcha/qm_captcha/index/verify';
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_TIMEOUT, 25);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');
	curl_setopt($ch, CURLOPT_HTTPHEADER, [
		'Accept: application/json',
		'Referer: ' . rtrim(QMZL_SITE, '/') . '/',
	]);
	curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
		'captcha' => (string)$captcha,
		'token'   => (string)$token,
	]));
	$resp = curl_exec($ch);
	$code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
	$err  = curl_error($ch);
	curl_close($ch);
	$j = json_decode(trim((string)$resp), true);
	if (!is_array($j) || !isset($j['status'])) {
		return ['ok' => false, 'msg' => $err ?: '验证失败', 'data' => null];
	}
	if ((int)$j['status'] !== 200) {
		return ['ok' => false, 'msg' => (string)($j['msg'] ?? '验证失败'), 'data' => null];
	}
	return ['ok' => true, 'msg' => '验证通过', 'data' => $j];
}

/**
 * 开放接口鉴权登录（管理员/代理商用，换取 JWT，无需人机验证）
 * POST {site}/api/v1/auth
 * 请求：{username: 注册邮箱或手机号, password: API密钥 token}
 * 返回：data.jwt
 * @return array {ok,status,msg,data:{jwt,exp}}
 */
function qmzl_openapi_auth($username, $apiToken)
{
	$url = rtrim(QMZL_SITE, '/') . '/api/v1/auth';
	$res = mnbt_http_post($url, [
		'username' => (string)$username,
		'password' => (string)$apiToken,
	], ['timeout' => 25]);
	$j = json_decode(trim((string)($res['body'] ?? '')), true);
	$jwt = is_array($j) && isset($j['data']) && is_array($j['data']) ? (string)($j['data']['jwt'] ?? '') : '';
	if ($jwt === '') {
		return ['ok' => false, 'status' => (int)($j['status'] ?? 0), 'msg' => (string)($j['msg'] ?? '鉴权失败'), 'data' => null];
	}
	$exp = qmzl_jwt_exp($jwt);
	return ['ok' => true, 'status' => 200, 'msg' => '鉴权成功', 'data' => ['jwt' => $jwt, 'exp' => $exp ?: (time() + 7200)]];
}

/**
 * 获取当前 user_info 用户的有效 token，过期自动重新登录
 * @return array {ok,status,msg,data:{jwt}}
 */
function qmzl_get_token($user_id)
{
	$row = qmzl_account_get($user_id);
	if (!$row) {
		return ['ok' => false, 'status' => 0, 'msg' => '请先绑定启明智联账号', 'data' => null];
	}
	$jwt = (string)($row['jwt'] ?? '');
	$exp = (int)($row['jwt_expire'] ?? 0);
	if ($jwt !== '' && $exp > time() + 300) {
		return ['ok' => true, 'status' => 200, 'msg' => '', 'data' => ['jwt' => $jwt]];
	}
	// token 失效，用存储的密码重新登录
	$password = qmzl_decrypt_pwd((string)$row['password']);
	if ($password === '') {
		return ['ok' => false, 'status' => 0, 'msg' => '账号密码缺失，请重新绑定', 'data' => null];
	}
	$login = qmzl_login($row['account'], $password);
	if (!$login['ok']) {
		// 上游登录需人机验证时自动刷新失败，提示重新绑定
		$msg = $login['msg'];
		if (mb_strpos($msg, '验证码') !== false || mb_strpos($msg, 'captcha') !== false || mb_strpos($msg, '人机') !== false) {
			$msg = '登录需要人机验证，请到「云账号」页重新验证绑定';
		}
		qmzl_account_update_status($user_id, 'error', $msg);
		$login['msg'] = $msg;
		return $login;
	}
	qmzl_account_set_token($user_id, $login['data']['jwt'], $login['data']['exp']);
	qmzl_account_update_status($user_id, 'ok', '');
	return ['ok' => true, 'status' => 200, 'msg' => '', 'data' => ['jwt' => $login['data']['jwt']]];
}

/* ============================================================
 * 3. 域名接口
 * ============================================================ */

/** 获取域名配置（协议链接 / 可搜索后缀 / 默认后缀） */
function qmzl_config($token)
{
	return qmzl_http_json('GET', 'idcsmart_domain/config', $token);
}

/** 获取后缀列表 */
function qmzl_suffixes($token)
{
	return qmzl_http_json('GET', 'idcsmart_domain/domain_suffix', $token);
}

/** 查询域名可用性，$suffix 形如 .com，可传数组 */
function qmzl_check($token, $domain, $suffix = '.com')
{
	$suffix = is_array($suffix) ? $suffix[0] : $suffix;
	return qmzl_http_json('GET', 'idcsmart_domain/check_domain', $token, null, [
		'domain'  => $domain,
		'suffix'  => $suffix,
		'host_id' => 0,
	]);
}

/** 查询域名价格（各年限） */
function qmzl_price($token, $name)
{
	return qmzl_http_json('GET', 'idcsmart_domain/get_price', $token, null, [
		'name'    => $name,
		'host_id' => 0,
	]);
}

/** 信息模板列表 */
function qmzl_templates($token)
{
	return qmzl_http_json('GET', 'idcsmart_domain/info_template', $token);
}

/** 创建模板（$fields 表单字段，$files 证件照片） */
function qmzl_template_create($token, $fields, $files)
{
	return qmzl_http_multipart('POST', 'idcsmart_domain/info_template', $token, $fields, $files);
}

/** 更新模板 */
function qmzl_template_update($token, $id, $fields, $files)
{
	return qmzl_http_multipart('PUT', 'idcsmart_domain/info_template/' . (int)$id, $token, $fields, $files);
}

/** 删除模板 */
function qmzl_template_delete($token, $id)
{
	return qmzl_http_json('DELETE', 'idcsmart_domain/info_template/' . (int)$id, $token);
}

/** 提交实名认证 */
function qmzl_template_certify($token, $id, $files)
{
	return qmzl_http_multipart('POST', 'idcsmart_domain/info_template/' . (int)$id . '/certifications', $token, [], $files);
}

/** 查找域名产品 ID（遍历产品分组 + 关键词兜底） */
function qmzl_product_id($token)
{
	// 1) 关键词搜索
	$res = qmzl_http_json('GET', 'product', $token, null, ['keywords' => '域名']);
	if ($res['ok'] && !empty($res['data']['list']) && is_array($res['data']['list'])) {
		foreach ($res['data']['list'] as $p) {
			$name = (string)($p['name'] ?? '');
			if (mb_strpos($name, '域名') !== false || stripos($name, 'domain') !== false) {
				return (int)$p['id'];
			}
		}
		if (isset($res['data']['list'][0]['id'])) return (int)$res['data']['list'][0]['id'];
	}
	// 2) 一级分组遍历
	$r1 = qmzl_http_json('GET', 'product/group/first', $token);
	if ($r1['ok'] && !empty($r1['data']['list']) && is_array($r1['data']['list'])) {
		foreach ($r1['data']['list'] as $g) {
			$r2 = qmzl_http_json('GET', 'product/group/second', $token, null, ['id' => (int)$g['id']]);
			if (!$r2['ok'] || empty($r2['data']['list'])) continue;
			foreach ($r2['data']['list'] as $sg) {
				$rp = qmzl_http_json('GET', 'product', $token, null, ['id' => (int)$sg['id']]);
				if (!$rp['ok'] || empty($rp['data']['list'])) continue;
				foreach ($rp['data']['list'] as $p) {
					$name = (string)($p['name'] ?? '');
					if (mb_strpos($name, '域名') !== false || stripos($name, 'domain') !== false) {
						return (int)$p['id'];
					}
				}
			}
		}
	}
	return 0;
}

/** 支付网关列表 */
function qmzl_gateways($token = '')
{
	return qmzl_http_json('GET', 'gateway', $token);
}

/** 加入购物车 */
function qmzl_cart_add($token, $productId, $domain, $year)
{
	return qmzl_http_json('POST', 'cart', $token, [
		'product_id'     => (int)$productId,
		'config_options' => [
			'domain' => (string)$domain,
			'year'   => (int)$year,
		],
		'qty'        => 1,
		'customfield' => ['is_domain' => 1],
	]);
}

/** 结算创建订单 */
function qmzl_cart_settle($token, $templateId, $autoRenew = 1, $lockStatus = 1)
{
	return qmzl_http_json('POST', 'cart/settle', $token, [
		'positions'   => [0],
		'customfield' => [
			'auto_renew'  => $autoRenew ? 1 : 0,
			'lock_status' => $lockStatus ? 1 : 0,
			'c_sysid'     => (string)$templateId,
			'host_id'     => 0,
		],
	]);
}

/** 发起支付，返回三方 HTML */
function qmzl_pay($token, $orderId, $gateway)
{
	$res = qmzl_http_json('POST', 'pay', $token, [
		'id'      => (int)$orderId,
		'gateway' => (string)$gateway,
	]);
	if (!$res['ok']) return $res;
	$data = is_array($res['data']) ? $res['data'] : [];
	$code = (string)($res['code'] ?? ($data['code'] ?? ''));
	$res['paid'] = ($code === 'Paid');
	$res['pay_html'] = (string)($data['html'] ?? '');
	return $res;
}

/** 支付状态：返回 {ok,status,msg,data,paid} */
function qmzl_pay_status($token, $orderId)
{
	$res = qmzl_http_json('GET', 'pay/' . (int)$orderId . '/status', $token);
	if (!$res['ok']) return $res;
	$code = (string)($res['code'] ?? '');
	if ($code === '' && is_array($res['data'])) {
		$code = (string)($res['data']['code'] ?? '');
	}
	$res['paid'] = ($code === 'Paid');
	return $res;
}

/** 使用账户余额支付上游订单（agent 模式自动注册用） */
function qmzl_credit_pay($token, $orderId)
{
	return qmzl_http_json('POST', 'credit', $token, [
		'id'  => (int)$orderId,
		'use' => 1,
	]);
}

/** 已购产品列表（域名） */
function qmzl_hosts($token, $page = 1, $limit = 100)
{
	return qmzl_http_json('GET', 'host', $token, null, ['page' => (int)$page, 'limit' => (int)$limit]);
}

/**
 * 获取当前用户已购域名列表。
 * client 模式：按用户自己的上游账号 /host 过滤 type=domain；
 * agent 模式：用管理员代理商账号取全部域名，再过滤该用户本地已支付订单的域名。
 * @param array $user user_info 用户数组
 * @return array {ok,msg,list}
 */
function qmzl_my_domains($user)
{
	$agent = qmzl_mode() === 'agent';
	$tok = $agent ? qmzl_agent_token() : qmzl_get_token((int)$user['id']);
	if (!$tok['ok']) return ['ok' => false, 'msg' => $tok['msg'], 'list' => []];
	$res = qmzl_hosts($tok['data']['jwt'], 1, 100);
	if (!$res['ok']) return ['ok' => false, 'msg' => $res['msg'] ?: '获取失败', 'list' => []];
	$all = (is_array($res['data'] ?? null)) ? ($res['data']['list'] ?? []) : [];
	if (!is_array($all)) $all = [];

	if ($agent) {
		// 该用户本地已支付订单的域名集合
		$orders = qmzl_order_list((int)$user['id'], 1, 200);
		$mine = [];
		foreach ($orders['rows'] as $o) {
			if (($o['status'] ?? '') === 'Paid' && ($o['domain'] ?? '') !== '') {
				$mine[strtolower($o['domain'])] = true;
			}
		}
		$list = [];
		foreach ($all as $h) {
			$name = strtolower((string)($h['name'] ?? $h['domain'] ?? ''));
			if ($name !== '' && isset($mine[$name])) {
				$list[] = $h;
			}
		}
		return ['ok' => true, 'msg' => '', 'list' => $list];
	}

	$list = [];
	foreach ($all as $h) {
		if (($h['type'] ?? '') === 'domain') {
			$list[] = $h;
		}
	}
	return ['ok' => true, 'msg' => '', 'list' => $list];
}

/**
 * 代理商模式：支付成功后自动在上游注册域名（加入购物车 + 结算 + 余额支付）。
 * @param int $orderId plg_qmzl_order.id
 * @return array {ok,msg}
 */
function qmzl_agent_register_domain($orderId)
{
	$order = qmzl_order_get_by_id($orderId);
	if (!$order) return ['ok' => false, 'msg' => '本地订单不存在'];

	$tok = qmzl_agent_token();
	if (!$tok['ok']) return ['ok' => false, 'msg' => $tok['msg']];
	$jwt = $tok['data']['jwt'];

	$domain = (string)$order['domain'];
	$year   = (int)$order['year'];
	$tplId  = (int)$order['template_id'];
	if ($domain === '' || $year < 1 || $tplId <= 0) {
		qmzl_order_update($order['id'], ['status' => 'Failed', 'remark' => '订单参数不完整']);
		return ['ok' => false, 'msg' => '订单参数不完整'];
	}

	// 1) 域名产品 ID
	$productId = qmzl_product_id($jwt);
	if ($productId <= 0) {
		qmzl_order_update($order['id'], ['status' => 'Failed', 'remark' => '未找到域名产品']);
		return ['ok' => false, 'msg' => '未找到域名产品，请联系平台确认'];
	}

	// 2) 加入购物车
	$cart = qmzl_cart_add($jwt, $productId, $domain, $year);
	if (!$cart['ok']) {
		qmzl_order_update($order['id'], ['status' => 'Failed', 'remark' => '加入购物车失败：' . $cart['msg']]);
		return ['ok' => false, 'msg' => '加入购物车失败：' . $cart['msg']];
	}

	// 3) 结算创建上游订单
	$settle = qmzl_cart_settle($jwt, $tplId, 1, 1);
	if (!$settle['ok']) {
		qmzl_order_update($order['id'], ['status' => 'Failed', 'remark' => '上游下单失败：' . $settle['msg']]);
		return ['ok' => false, 'msg' => '上游下单失败：' . $settle['msg']];
	}
	$cloudOrderId = 0;
	if (isset($settle['data']['order_id'])) {
		$cloudOrderId = (int)$settle['data']['order_id'];
	} elseif (!empty($settle['data']['ids']) && is_array($settle['data']['ids'])) {
		$cloudOrderId = (int)$settle['data']['ids'][0];
	}
	if ($cloudOrderId <= 0) {
		qmzl_order_update($order['id'], ['status' => 'Failed', 'remark' => '上游未返回订单号']);
		return ['ok' => false, 'msg' => '上游未返回订单号'];
	}

	// 4) 用代理商账号余额支付
	$credit = qmzl_credit_pay($jwt, $cloudOrderId);
	if (!$credit['ok']) {
		qmzl_order_update($order['id'], [
			'cloud_order_id' => (string)$cloudOrderId,
			'status'         => 'Failed',
			'remark'         => '上游余额支付失败：' . $credit['msg'],
		]);
		return ['ok' => false, 'msg' => '上游余额支付失败：' . $credit['msg'] . '（请检查代理商账号余额）'];
	}

	qmzl_order_update($order['id'], [
		'cloud_order_id' => (string)$cloudOrderId,
		'status'         => 'Paid',
		'remark'         => '已注册（上游订单 ' . $cloudOrderId . '）',
	]);
	return ['ok' => true, 'msg' => '域名注册成功'];
}
