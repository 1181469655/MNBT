import { apiGet, apiPost } from './http'

/**
 * balance + hosting_shop + docker_shop 插件路由 API 封装
 *
 * 插件通过 P2 通用路由（routeBase）暴露 JSON API：
 *   balance:      /balance/api/info、/balance/api/methods、/balance/api/create_recharge
 *   hosting_shop: /shop/api/plans、/shop/api/assets、/shop/api/orders、/shop/api/create_order
 *   docker_shop:  /docker-shop/api/plans、/docker-shop/api/assets、/docker-shop/api/orders、
 *                 /docker-shop/api/create_order、/docker-shop/api/reset_password、/docker-shop/api/sync_status
 *
 * 路由是否可用由插件是否启用决定，前端依据 boot.plugins 标志按需展示。
 */

/** 插件能力检测：boot.plugins.balance / boot.plugins.hosting_shop / boot.plugins.docker_shop */
export function pluginEnabled(name) {
  const boot = window.__TD_BOOT__ || {}
  return !!(boot.plugins && boot.plugins[name])
}

/** 当前余额（分）格式化 → 元（保留 2 位小数） */
export function centsToYuan(cents) {
  const n = Number(cents || 0)
  return (n / 100).toFixed(2)
}

/**
 * 处理支付跳转响应（create_recharge / create_order 通用）
 * @returns {boolean} 是否已触发跳转（html 弹窗 / redirect 跳转）
 */
export function goPay(res) {
  const data = res?.data || {}
  if (data.html) {
    const win = window.open('', '_blank')
    if (win) {
      win.document.open()
      win.document.write(data.html)
      win.document.close()
    }
    return true
  }
  if (data.redirect) {
    window.location.href = data.redirect
    return true
  }
  return false
}

/* ============================================================
 *  balance 插件
 * ============================================================ */

/** 余额信息 + 流水分页：{ balance_cents, balance_yuan, logs } */
export function getBalanceInfo(page = 1, perPage = 15) {
  return apiGet('/balance/api/info', { page, per_page: perPage })
}

/** 可用支付方式（排除余额自身） */
export function getBalanceMethods() {
  return apiGet('/balance/api/methods')
}

/** 创建充值订单：amount(元) + type(如 epay__alipay) → 返回支付 HTML */
export function createRecharge(amount, type) {
  return apiPost('/balance/api/create_recharge', { amount, type })
}

/* ============================================================
 *  hosting_shop 插件
 * ============================================================ */

/** 上架套餐列表 */
export function getShopPlans() {
  return apiGet('/shop/api/plans')
}

/** 我的主机资产 */
export function getShopAssets() {
  return apiGet('/shop/api/assets')
}

/** 我的订单（分页） */
export function getShopOrders(page = 1, perPage = 15) {
  return apiGet('/shop/api/orders', { page, per_page: perPage })
}

/** 可用支付方式（hosting_shop 下单用，含余额支付等全部方式） */
export function getShopMethods() {
  return apiGet('/shop/api/methods')
}

/** 创建购买订单：plan_id + period + type → 返回支付 HTML / 0 元直接 redirect */
export function createShopOrder(planId, period, type) {
  return apiPost('/shop/api/create_order', { plan_id: planId, period, type })
}

/* ============================================================
 *  docker_shop 插件
 * ============================================================ */

/** 上架 Docker 售卖套餐（含基准配额、节点、价格） */
export function getDockerShopPlans() {
  return apiGet('/docker-shop/api/plans')
}

/** 我的 Docker 资产（含容器状态、磁盘用量冗余） */
export function getDockerShopAssets() {
  return apiGet('/docker-shop/api/assets')
}

/** 我的 Docker 订单（分页） */
export function getDockerShopOrders(page = 1, perPage = 15) {
  return apiGet('/docker-shop/api/orders', { page, per_page: perPage })
}

/** 可用支付方式（docker_shop 下单用，含余额支付等全部方式） */
export function getDockerShopMethods() {
  return apiGet('/docker-shop/api/methods')
}

/** 创建购买订单：plan_id + period + type → 返回支付 HTML / 0 元直接 redirect */
export function createDockerShopOrder(planId, period, type) {
  return apiPost('/docker-shop/api/create_order', { plan_id: planId, period, type })
}

/** 重置 Docker 账号密码：asset_id → { password } */
export function resetDockerPassword(assetId) {
  return apiPost('/docker-shop/api/reset_password', { asset_id: assetId })
}

/** 刷新容器状态：asset_id → { container_status, container_id, disk_usage } */
export function syncDockerStatus(assetId) {
  return apiPost('/docker-shop/api/sync_status', { asset_id: assetId })
}

/** Docker 控制台入口（boot.dockerUrl，如 {base}/docker/） */
export function dockerConsoleUrl() {
  const boot = window.__TD_BOOT__ || {}
  return boot.dockerUrl || ''
}
