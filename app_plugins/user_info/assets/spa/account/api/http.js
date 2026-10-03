import axios from 'axios'
import { MessagePlugin } from 'tdesign-vue-next'

/**
 * MNBT 用户中心（user_info 插件）路由 API 封装
 *
 * user_info 插件通过 P2 通用路由暴露 JSON API：
 *   POST/GET {routeBase}/account/api/login 等
 * routeBase 由 PHP 入口注入（index.php?_r=），响应为 { code, ...extra } 结构：
 *   - code === 'ok'         → 成功，extra 为数据载荷
 *   - code === 'not_login'  → 未登录（前端跳登录页）
 *   - 其他 code             → 失败消息
 */

function getBoot() {
  return window.__TD_BOOT__ || {}
}

/** 读取 CSRF token（meta 优先，回退 cookie） */
export function getCsrfToken() {
  const meta = document.querySelector('meta[name="csrf-token"]')
  if (meta && meta.content) return meta.content
  try {
    const cookies = document.cookie.split('; ')
    for (const c of cookies) {
      const idx = c.indexOf('=')
      if (idx > -1 && c.slice(0, idx) === 'MNBT_CSRF_TOKEN') {
        return decodeURIComponent(c.slice(idx + 1))
      }
    }
  } catch { /* ignore */ }
  return ''
}

const http = axios.create({ timeout: 60000 })

/** 构造完整 API URL */
export function apiUrl(path) {
  const boot = getBoot()
  const base = boot.routeBase || '/index.php?_r='
  return base + path.replace(/^\//, '')
}

/**
 * 发送路由 API 请求
 * @param {string} method GET|POST
 * @param {string} path   路由路径（/account/api/me）
 * @param {object} data   POST 表单数据
 * @param {{silent?:boolean}} opts
 * @returns {Promise<{ok:boolean, code:string, message:string, data:object, raw:object}>}
 */
export async function routeRequest(method, path, data = {}, { silent = false } = {}) {
  try {
    let res
    if (method === 'GET') {
      res = await http.get(apiUrl(path), { params: data })
    } else {
      const body = new URLSearchParams()
      Object.keys(data || {}).forEach((k) => {
        const v = data[k]
        if (v === undefined || v === null) return
        body.append(k, String(v))
      })
      const token = getCsrfToken()
      if (token && !body.get('_csrf')) body.append('_csrf', token)
      res = await http.post(apiUrl(path), body, {
        headers: { 'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8' },
      })
    }
    const raw = res.data
    if (raw && typeof raw === 'object' && typeof raw.code === 'string') {
      if (raw.code === 'ok') {
        return { ok: true, code: 'ok', message: 'ok', data: raw, raw }
      }
      if (raw.code === 'not_login') {
        return { ok: false, code: 'not_login', message: '请先登录', data: raw, raw }
      }
      return { ok: false, code: raw.code, message: raw.code || '请求失败', data: raw, raw }
    }
    // 兼容 { success:true, ... } 风格
    if (raw && typeof raw === 'object' && raw.success === true) {
      return { ok: true, code: 'ok', message: raw.msg || 'ok', data: raw, raw }
    }
    if (raw && typeof raw === 'object' && raw.success === false) {
      const msg = raw.msg || raw.code || raw.message || '请求失败'
      return { ok: false, code: msg, message: msg, data: raw, raw }
    }
    return { ok: true, code: 'ok', message: 'ok', data: raw ?? {}, raw }
  } catch (e) {
    let msg = e.message || '网络错误'
    const body = e?.response?.data
    if (typeof body === 'string' && body.trim()) {
      const plain = body.replace(/<[^>]+>/g, ' ').replace(/\s+/g, ' ').trim()
      if (plain) msg = plain.slice(0, 160)
    } else if (body && typeof body === 'object') {
      msg = body.code || body.msg || body.message || msg
    }
    console.error(`[accountApi] ${method} ${path} 异常:`, e, body)
    if (!silent) MessagePlugin.error(msg)
    return { ok: false, code: msg, message: msg, data: null, raw: body ?? null }
  }
}

/** GET 快捷方法 */
export async function apiGet(path, params = {}, opts = {}) {
  return routeRequest('GET', path, params, opts)
}

/** POST 快捷方法 */
export async function apiPost(path, data = {}, opts = {}) {
  return routeRequest('POST', path, data, opts)
}

export default http
