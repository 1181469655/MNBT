import { apiGet } from './http'

/**
 * zjmfmanager_reserve 插件 API（魔方财务代理分销 - 云服务器）
 * 依赖插件启用：zjmfmanager_reserve
 * 注意：购买/主机/订单等用户中心能力统一在 account SPA 或插件独立页面，
 * home 仅展示公开商品列表
 */

/** 公开商品列表（游客可访问，含最低价） */
export function getZjmfProducts() {
  return apiGet('/reserve/api/public_products')
}
