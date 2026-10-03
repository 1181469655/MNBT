import { apiGet } from './http'

/**
 * hosting_shop 插件 API（主机售卖）
 * 依赖插件启用：hosting_shop
 * 注意：购买/资产/订单等用户中心能力统一在 account SPA，home 仅展示套餐列表
 */

/** 上架套餐列表 */
export function getPlans() {
  return apiGet('/shop/api/plans')
}
