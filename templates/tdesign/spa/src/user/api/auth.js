import { apiGn } from '@/shared/api/http'

export function userLogin(user, pass, code = '0000') {
  return apiGn('login', { user, pass, code })
}

export function userLogout() {
  return apiGn('login', { logout: 'tclogin' }, { silent: true })
}
