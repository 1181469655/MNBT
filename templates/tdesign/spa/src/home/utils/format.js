/** 周期标签 */
export const periodLabels = {
  month: '月付',
  quarter: '季付',
  half_year: '半年付',
  year: '年付',
  two_year: '两年付',
  three_year: '三年付',
}

/** 分→元 */
export function centsToYuan(cents) {
  return (cents / 100).toFixed(2)
}

/** 日期格式化（2026-08-10 → 2026年8月10日） */
export function formatDate(dateString, { showYear = true } = {}) {
  if (!dateString) return ''
  const d = new Date(String(dateString).replace(/-/g, '/'))
  if (isNaN(d.getTime())) return String(dateString)
  const opts = { month: 'long', day: 'numeric' }
  if (showYear) opts.year = 'numeric'
  return d.toLocaleDateString('zh-CN', opts)
}

/** 订单状态标签 */
export const orderStatusMap = {
  pending: { label: '待支付', theme: 'warning' },
  paid: { label: '已支付', theme: 'primary' },
  opened: { label: '已开通', theme: 'success' },
  failed: { label: '失败', theme: 'danger' },
  cancelled: { label: '已取消', theme: 'default' },
}

/** 资产状态标签 */
export const assetStatusMap = {
  active: { label: '正常', theme: 'success' },
  expired: { label: '已过期', theme: 'danger' },
  suspended: { label: '已停用', theme: 'warning' },
}

/** 流水类型标签 */
export const logTypeMap = {
  recharge: { label: '充值', theme: 'success' },
  consume: { label: '消费', theme: 'danger' },
  refund: { label: '退款', theme: 'warning' },
  adjust: { label: '调整', theme: 'primary' },
}
