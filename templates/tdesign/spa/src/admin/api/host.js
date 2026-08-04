import { apiGn } from '@/shared/api/http'

const S = { silent: true }

/** 主机列表 */
export function listHost(params) {
  return apiGn('listzj', params, S)
}

/** 添加主机 */
export function addHost(data) {
  return apiGn('addzj', data)
}

/** 修改主机 */
export function updateHost(data) {
  return apiGn('zjxgjl', data)
}

/** 删除主机 */
export function deleteHost(id) {
  return apiGn('zjsc', { id })
}

/** 批量删除主机 */
export function deleteHostBatch(idsz) {
  return apiGn('zjscxz', { idsz })
}
