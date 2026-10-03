/** 日期格式化（2026-08-10 → 2026年8月10日） */
export function formatDate(dateString, { showYear = true } = {}) {
  if (!dateString) return ''
  const d = new Date(String(dateString).replace(/-/g, '/'))
  if (isNaN(d.getTime())) return String(dateString)
  const opts = { month: 'long', day: 'numeric' }
  if (showYear) opts.year = 'numeric'
  return d.toLocaleDateString('zh-CN', opts)
}
