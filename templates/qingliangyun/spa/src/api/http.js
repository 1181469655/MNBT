import axios from 'axios'
import { ElMessage } from 'element-plus'

/**
 * MNBT 用户端 AJAX 统一走 ./ajax.php（相对当前 /user/ 路径）
 * PHP 注入 window.__QL_BOOT__.ajaxBase 可覆盖
 */
function ajaxUrl() {
  const boot = window.__QL_BOOT__ || {}
  return boot.ajaxBase || './ajax.php'
}

const http = axios.create({
  timeout: 60000,
  headers: {
    'X-Requested-With': 'XMLHttpRequest',
  },
})

/**
 * POST 表单到 ajax.php
 * @param {string} gn 业务动作
 * @param {Record<string, any>} data 额外字段
 */
export async function postGn(gn, data = {}) {
  const body = new URLSearchParams()
  body.append('gn', gn)
  Object.keys(data).forEach((k) => {
    const v = data[k]
    if (v === undefined || v === null) return
    body.append(k, typeof v === 'object' ? JSON.stringify(v) : String(v))
  })

  const res = await http.post(ajaxUrl(), body, {
    headers: { 'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8' },
  })
  return res.data
}

/**
 * 解析 MNBT 常见 JSON 响应
 * - 成功: { qk:1, code:'...', msg:... } 或 { code:'登陆成功' }
 * - 失败: { code:'错误信息' }
 */
export function parseResult(data) {
  if (data == null) {
    return { ok: false, message: '无响应', raw: data }
  }
  if (typeof data === 'string') {
    try {
      data = JSON.parse(data)
    } catch {
      return { ok: false, message: data, raw: data }
    }
  }
  if (typeof data !== 'object') {
    return { ok: false, message: String(data), raw: data }
  }
  if (data.qk === 1 || data.qk === '1') {
    return { ok: true, message: data.code || data.msg || 'ok', data: data.msg ?? data.data ?? data, raw: data }
  }
  if (data.success === true || data.success === 1 || data.success === '1') {
    return { ok: true, message: data.code || data.msg || 'ok', data: data.msg ?? data.data ?? data, raw: data }
  }
  const code = data.code
  if (code === '登陆成功' || code === '绑定成功' || code === '获取成功！') {
    return { ok: true, message: code, data: data.msg ?? data.data ?? data, raw: data }
  }
  if (typeof code === 'string' && (code.includes('成功') || code.includes('完成'))) {
    return { ok: true, message: code, data: data.msg ?? data.data ?? data, raw: data }
  }
  return {
    ok: false,
    message: typeof code === 'string' ? code : (data.msg || data.message || '操作失败'),
    raw: data,
  }
}

export async function apiGn(gn, data = {}, { silent = false } = {}) {
  try {
    const raw = await postGn(gn, data)
    const result = parseResult(raw)
    if (!result.ok && !silent) {
      ElMessage.error(result.message || '请求失败')
    }
    return result
  } catch (e) {
    if (!silent) ElMessage.error(e.message || '网络错误')
    return { ok: false, message: e.message || '网络错误', raw: null }
  }
}

export default http
