import { apiGn } from '@/shared/api/http'

export function userLogin(user, pass, captchaVerification = '') {
  return apiGn('login', { user, pass, code: '', captchaVerification })
}

export function userLogout() {
  return apiGn('login', { logout: 'tclogin' }, { silent: true })
}
