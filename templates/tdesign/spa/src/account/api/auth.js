import { apiGet, apiPost } from './http'

/** 获取当前登录用户信息（未登录返回 ok=false + code=not_login） */
export function getMe() {
  return apiGet('/account/api/me', {}, { silent: true })
}

/** 登录：username + password */
export function accountLogin(username, password) {
  return apiPost('/account/api/login', { username, password })
}

/** 注册：username + password + password2 + email(可选) + qq(可选) */
export function accountRegister(data) {
  return apiPost('/account/api/register', {
    username: data.username,
    password: data.password,
    password2: data.password2,
    email: data.email || '',
    qq: data.qq || '',
  })
}

/** 更新个人信息：email + qq */
export function updateProfile(data) {
  return apiPost('/account/api/update_profile', {
    email: data.email || '',
    qq: data.qq || '',
  })
}

/** 修改密码：old_password + new_password + new_password2 */
export function changePassword(data) {
  return apiPost('/account/api/change_password', {
    old_password: data.old_password,
    new_password: data.new_password,
    new_password2: data.new_password2,
  })
}

/** 退出登录地址（服务端清 cookie 后回登录页） */
export function logoutUrl() {
  const boot = window.__TD_BOOT__ || {}
  const base = boot.routeBase || '/index.php?_r='
  return base + 'account/logout'
}
