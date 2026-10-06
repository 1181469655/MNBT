import { apiGn } from '@/shared/api/http'

export function login(user, pass, captchaVerification = '') {
  return apiGn('login', { user, pass, code: '', captchaVerification })
}

export function logout() {
  return apiGn('login', { logout: 'tclogin' }, { silent: true })
}
