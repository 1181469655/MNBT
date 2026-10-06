/**
 * 验证码接口：对接根目录 captcha.php（官方组件协议 repCode/repData）
 * 表单编码提交，token/pointJson 为 base64 字符串，由 URLSearchParams 负责转义
 */

function endpoint(action) {
	const boot = window.__TD_BOOT__ || {}
	const base = boot.captchaUrl || '../captcha.php'
	return base + '?action=' + action
}

function captchaPost(action, data) {
	return fetch(endpoint(action), {
		method: 'POST',
		headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
		body: new URLSearchParams(data).toString(),
	})
		.then((res) => res.json())
		.catch(() => ({ repCode: '6111', repMsg: '网络异常，请重试' }))
}

/** 获取验证码（底图 base64 + token + secretKey） */
export function reqGet(data) {
	return captchaPost('get', data)
}

/** 一次验证，返回 repData.captchaVerification 供业务登录接口二次校验 */
export function reqCheck(data) {
	return captchaPost('check', data)
}
