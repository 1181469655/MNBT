import { apiGet, apiPost } from './http'

/**
 * user_info 插件 API（账户系统）
 * 依赖插件启用：user_info
 */

/** 获取当前登录用户（未登录 code=not_login） */
export function getMe() {
  return apiGet('/account/api/me')
}

/** 登录 */
export function login(username, password) {
  return apiPost('/account/api/login', { username, password })
}

/** 注册 */
export function register(payload) {
  return apiPost('/account/api/register', payload)
}

/** 更新个人信息 */
export function updateProfile(email, qq) {
  return apiPost('/account/api/update_profile', { email, qq })
}

/** 修改密码 */
export function changePassword(payload) {
  return apiPost('/account/api/change_password', payload)
}
