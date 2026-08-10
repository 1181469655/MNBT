import { apiGet, apiPost } from './http'

/**
 * hosting_shop 插件 API（主机售卖）
 * 依赖插件启用：hosting_shop
 */

/** 上架套餐列表 */
export function getPlans() {
  return apiGet('/shop/api/plans')
}

/** 套餐详情（含支付方式） */
export function getPlan(planId) {
  return apiGet(`/shop/api/plan/${planId}`)
}

/** 我的主机资产 */
export function getAssets() {
  return apiGet('/shop/api/assets')
}

/** 我的订单（分页） */
export function getOrders(page = 1, perPage = 15) {
  return apiGet('/shop/api/orders', { page, per_page: perPage })
}

/** 可用支付方式 */
export function getShopMethods() {
  return apiGet('/shop/api/methods')
}

/** 创建购买订单 → 返回支付 HTML / redirect */
export function createOrder(planId, period, type) {
  return apiPost('/shop/api/create_order', { plan_id: planId, period, type })
}
