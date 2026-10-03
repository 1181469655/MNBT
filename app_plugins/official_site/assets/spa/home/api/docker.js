import { apiGet } from './http'

/**
 * docker_shop 插件 API（Docker 容器售卖）
 * 依赖插件启用：docker_shop
 * 注意：购买/资产/订单等用户中心能力统一在 account SPA，home 仅展示套餐列表
 */

/** 上架 Docker 售卖套餐（含基准配额、节点、价格） */
export function getDockerPlans() {
  return apiGet('/docker-shop/api/plans')
}
