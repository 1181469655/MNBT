import { apiGn } from '@/shared/api/http'

const S = { silent: true }

/** 检查更新 / 版本信息 */
export function checkUpdate() {
  return apiGn('mnbt', {}, S)
}

/** 系统信息(仪表盘) */
export function systemInfo() {
  return apiGn('system_info', {}, S)
}

/** 系统更新 */
export function systemUpdate() {
  return apiGn('update', {})
}

/** 重新检查更新（跳过服务端缓存，返回 GitHub Release 检查结果原始载荷） */
export function updaterCheck(force = 1) {
  return apiGn('upcheck', { force }, { silent: true })
}

/** 保存更新设置（GitHub / Gitee 仓库 / 下载策略 / 可选 Token） */
export function saveUpdaterConfig(data) {
  return apiGn('upset', data)
}

/** 系统修复 */
export function systemRepair(xx, xe) {
  return apiGn('xtxf', { xx, xe })
}
