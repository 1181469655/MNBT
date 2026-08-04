import { apiGn } from '@/shared/api/http'

const S = { silent: true }

/** 订单列表 */
export function listOrder(params) {
  return apiGn('listdd', params, S)
}

/** 删除订单 */
export function deleteOrder(id) {
  return apiGn('ddsc', { id })
}

/** 批量删除订单 */
export function deleteOrderBatch(idsz) {
  return apiGn('ddscxz', { idsz })
}
