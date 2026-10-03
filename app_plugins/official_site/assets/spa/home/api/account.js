import { apiGet, apiPost } from './http'

/**
 * user_info 插件 API（账户系统）
 * 依赖插件启用：user_info
 */

/** 获取当前登录用户（未登录 code=not_login；静默探测，失败不弹窗） */
export function getMe() {
  return apiGet('/account/api/me', {}, { silent: true })
}

/** 登录 */
export function login(username, password) {
  return apiPost('/account/api/login', { username, password })
}

/** 注册 */
export function register(payload) {
  return apiPost('/account/api/register', payload)
}
