import { apiGet, apiPost } from './http'

/**
 * balance 插件 API（余额系统）
 * 依赖插件启用：balance
 */

/** 余额信息 + 流水分页 */
export function getBalanceInfo(page = 1, perPage = 15) {
  return apiGet('/balance/api/info', { page, per_page: perPage })
}

/** 可用支付方式（充值页，排除余额自身） */
export function getBalanceMethods() {
  return apiGet('/balance/api/methods')
}

/** 创建充值订单 → 返回支付 HTML */
export function createRecharge(amount, type) {
  return apiPost('/balance/api/create_recharge', { amount, type })
}
