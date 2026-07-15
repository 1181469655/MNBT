import { apiGn, postGn, parseResult } from './http'

export function login(user, pass, code = '0000') {
  return apiGn('login', { user, pass, code })
}

export function logout() {
  return apiGn('login', { logout: 'tclogin' }, { silent: true })
}

export function fetchIndexConf() {
  return apiGn('indexconf')
}

export function refreshUsage() {
  return apiGn('sxsyxx')
}

export function changePhp(php) {
  return apiGn('phpxg', { php })
}

export function bindMail(mail) {
  return apiGn('mailbd', { mail })
}

export { postGn, parseResult }
