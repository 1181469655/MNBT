import { apiGn } from '@/shared/api/http'

const S = { silent: true }

/** 操作日志列表 */
export function listLog(params) {
  return apiGn('listlog', params, S)
}

/** 删除日志 */
export function deleteLog(id) {
  return apiGn('logsc', { id })
}

/** 批量删除日志(注意:接口接受逗号分隔字符串) */
export function deleteLogBatch(idsz) {
  const ids = Array.isArray(idsz) ? idsz.join(',') : String(idsz)
  return apiGn('logscxz', { idsz: ids })
}

/** 清空日志 */
export function clearLog() {
  return apiGn('logclear', {})
}
