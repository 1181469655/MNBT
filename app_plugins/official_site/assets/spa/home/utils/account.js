/**
 * 用户中心（account SPA）跳转工具
 *
 * account 入口为 {routeBase}account，内部为 hash 路由（#/profile、#/hosting、#/balance ...）。
 * 未登录时 account 路由守卫会自动转跳其登录页，登录后回跳。
 * 用途：home 前台只做展示，账户/资产管理/下单等操作统一跳转 account 承载。
 *
 * @param {string} [path] account 内部路径（不带 #），空则跳用户中心首页（/dashboard）
 * @returns {string} 完整页面 URL
 */
export function accountUrl(path = '') {
  const boot = window.__TD_BOOT__ || {}
  const base = (boot.routeBase || '/index.php?_r=') + 'account'
  return path ? base + '#/' + String(path).replace(/^\//, '') : base
}
