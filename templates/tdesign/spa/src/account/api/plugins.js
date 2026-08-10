import { apiGet, apiPost } from './http'

/**
 * balance + hosting_shop 插件路由 API 封装
 *
 * 插件通过 P2 通用路由（routeBase）暴露 JSON API：
 *   balance:      /balance/api/info、/balance/api/methods、/balance/api/create_recharge
 *   hosting_shop: /shop/api/plans、/shop/api/assets、/shop/api/orders、/shop/api/create_order
 *
 * 路由是否可用由插件是否启用决定，前端依据 boot.plugins 标志按需展示。
 */

/** 插件能力检测：boot.plugins.balance / boot.plugins.hosting_shop */
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
