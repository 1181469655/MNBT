import { apiGn } from '@/shared/api/http'

/** 系统修复 */
export function repair(xx, xe) {
  return apiGn('xtxf', { xx, xe })
}
