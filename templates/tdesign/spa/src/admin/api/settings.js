import { apiGn } from '@/shared/api/http'

/** 网站设置: gg / qq / yzm / zjyxbd */
export function setWebsite(data) {
  return apiGn('setwz', data)
}

/** API 设置: apikey / apiqk / linux / windows */
export function setApi(data) {
  return apiGn('setapi', data)
}

/** 控制面板: name / ftp / yzm / kg / bq + 文件上传 loa/lob/loc */
export function setPanel(data) {
  return apiGn('setkzmb', data)
}

/** 管理账号: yuser / ypass / xuser / xpass */
export function setAdmin(data) {
  return apiGn('gl', data)
}

/** 邮箱: host / user / password / port */
export function setMail(data) {
  return apiGn('mailmode', data)
}

/** 监控设置: ymkg / ymyjkg / ymtsyz / wjkg / wjyjkg / wjtsyz / option */
export function setMonitor(data) {
  return apiGn('jkscsz', data)
}

/** 主题切换: usertheme / admintheme / dockertheme */
export function setTheme(usertheme, admintheme, dockertheme = '') {
  return apiGn('settheme', { usertheme, admintheme, dockertheme })
}

/** 支付方式列表(走 panel API,如启用支付插件) */
export function listPaymentMethods() {
  return apiGn('list_payment_methods', {}, { silent: true })
}

/** 保存支付方式 */
export function savePaymentMethods(methods) {
  return apiGn('setpaymethods', { methods })
}
