import { apiGn, parseResult, default as http } from '@/shared/api/http'
import { MessagePlugin } from 'tdesign-vue-next'

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

/** 主题切换: usertheme / admintheme / dockertheme / hometheme */
export function setTheme(usertheme, admintheme, dockertheme = '', hometheme = '') {
  return apiGn('settheme', { usertheme, admintheme, dockertheme, hometheme })
}

/** 支付方式列表(走 panel API,如启用支付插件) */
export function listPaymentMethods() {
  return apiGn('list_payment_methods', {}, { silent: true })
}

/** 保存支付方式 */
export function savePaymentMethods(methods) {
  return apiGn('setpaymethods', { methods })
}

/** 主页设置: home_enable / home_title / home_hero / home_primary / home_logo / home_favicon / home_footer / home_show_notice / home_show_plans */
export function setHome(data) {
  return apiGn('save_home_settings', data)
}

/** 上传主页 Logo/Favicon: target=logo|favicon */
export async function uploadHomeIcon(target, file) {
  const boot = window.__TD_BOOT__ || {}
  const url = boot.ajaxBase || './ajax.php'
  const fd = new FormData()
  fd.append('gn', 'home_upload_icon')
  fd.append('target', target)
  fd.append('icon', file)
  const res = await http.post(url, fd)
  const r = parseResult(res.data)
  if (!r.ok) MessagePlugin.error(r.message || '上传失败')
  return r
}
