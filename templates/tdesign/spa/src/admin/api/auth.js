import { apiGn } from '@/shared/api/http'

export function login(user, pass, captcha = {}) {
  return apiGn('login', { user, pass, code: '', ...captcha })
}

export function logout() {
  return apiGn('login', { logout: 'tclogin' }, { silent: true })
}
