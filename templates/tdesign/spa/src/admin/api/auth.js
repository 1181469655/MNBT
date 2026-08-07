import { apiGn } from '@/shared/api/http'

export function login(user, pass, code = '0000') {
  return apiGn('login', { user, pass, code })
}

export function logout() {
  return apiGn('login', { logout: 'tclogin' }, { silent: true })
}
