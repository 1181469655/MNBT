import { apiGn } from '@/shared/api/http'

export function userLogin(user, pass, captcha = {}) {
  return apiGn('login', { user, pass, code: '', ...captcha })
}

export function userLogout() {
  return apiGn('login', { logout: 'tclogin' }, { silent: true })
}
