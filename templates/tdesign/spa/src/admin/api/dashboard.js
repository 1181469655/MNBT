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

/** 系统修复 */
export function systemRepair(xx, xe) {
  return apiGn('xtxf', { xx, xe })
}
