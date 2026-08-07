import axios from 'axios'
import { MessagePlugin } from 'tdesign-vue-next'

/**
 * MNBT 管理端 AJAX 统一走 ./ajax.php
 */
function ajaxUrl() {
  const boot = window.__TD_BOOT__ || {}
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
 */
export async function postGn(gn, data = {}) {
  const body = new URLSearchParams()
  body.append('gn', gn)
  Object.keys(data).forEach((k) => {
    const v = data[k]
    if (v === undefined || v === null) return
    if (Array.isArray(v)) {
      v.forEach((item, i) => body.append(`${k}[${i}]`, String(item)))
      return
    }
    if (typeof v === 'object') {
      body.append(k, JSON.stringify(v))
      return
    }
    body.append(k, String(v))
  })

  const res = await http.post(ajaxUrl(), body, {
    headers: { 'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8' },
  })
  return res.data
}

/**
 * 判断是否为「纯数据载荷」(无统一 code/qk 包装)
 * 如 listbt / listzj / listlog / listbs / listdd / listnode 等表格数据
 */
function isDataPayload(obj) {
  if (!obj || typeof obj !== 'object' || Array.isArray(obj)) return false
  if (obj.qk === 4 || obj.qk === '4') return false
  if (obj.success === false) return false
  if (obj.qk === 1 || obj.qk === '1') return false
  if (obj.success === true) return false
  if (typeof obj.code === 'string' && obj.code !== '') return false
  const keys = Object.keys(obj)
  if (!keys.length) return true
  const dataHints = [
    'total', 'rows', 'data', 'msg', 'list', 'items',
    'ip', 'dk', 'my', 'kg', 'btos', 'versions', 'latest',
    'current_default', 'config', 'config_json',
  ]
  return keys.some((k) => dataHints.includes(k))
}

/**
 * 解析 MNBT 响应
 *
 * 格式 A: { qk:1|4, code, msg }  — panel / 部分业务
 * 格式 B: { success, code, msg } — json_exit
 * 格式 C: { code: 'xxx成功' }    — 旧接口
 * 格式 D: 纯数据 { total, rows } / { ip, dk }
 * 格式 E: 纯文本
 */
export function parseResult(data) {
  if (data == null || data === '') {
    return { ok: false, message: '无响应', data: null, raw: data }
  }

  if (typeof data === 'string') {
    const trimmed = data.trim()
    if (
      (trimmed.startsWith('{') && trimmed.endsWith('}')) ||
      (trimmed.startsWith('[') && trimmed.endsWith(']'))
    ) {
      try {
        return parseResult(JSON.parse(trimmed))
      } catch {
        /* fallthrough as text */
      }
    }
    if (trimmed === 'false' || trimmed === 'FALSE') {
      return { ok: true, message: 'ok', data: false, raw: data }
    }
    if (/失败|错误|请登陆|禁止|无法/.test(trimmed) && trimmed.length < 80) {
      return { ok: false, message: trimmed, data: null, raw: data }
    }
    return { ok: true, message: 'ok', data: data, raw: data }
  }

  if (typeof data !== 'object') {
    return { ok: true, message: 'ok', data, raw: data }
  }

  if (data.qk === 4 || data.qk === '4') {
    return {
      ok: false,
      message: data.code || data.msg || '操作失败',
      data: null,
      raw: data,
    }
  }
  if (data.success === false) {
    return {
      ok: false,
      message: data.code || data.msg || data.message || '操作失败',
      data: null,
      raw: data,
    }
  }

  if (data.qk === 1 || data.qk === '1') {
    return {
      ok: true,
      message: data.code || data.msg || 'ok',
      data: data.msg !== undefined ? data.msg : data.data !== undefined ? data.data : data,
      raw: data,
    }
  }

  if (data.success === true || data.success === 1 || data.success === '1') {
    return {
      ok: true,
      message: data.code || data.msg || 'ok',
      data: data.msg !== undefined ? data.msg : data.data !== undefined ? data.data : data,
      raw: data,
    }
  }

  if (typeof data.code === 'string' && data.code !== '') {
    const code = data.code
    if (code === '请登陆' || code.includes('请登录')) {
      return { ok: false, message: code, data: null, raw: data }
    }
    if (
      code.includes('成功') ||
      code.includes('完成') ||
      code.includes('已保存') ||
      code === '文件已保存!'
    ) {
      return {
        ok: true,
        message: code,
        data: data.msg !== undefined ? data.msg : data.data !== undefined ? data.data : data,
        raw: data,
      }
    }
    return { ok: false, message: code, data: null, raw: data }
  }

  if (isDataPayload(data) || Array.isArray(data)) {
    return { ok: true, message: 'ok', data, raw: data }
  }

  if (Object.keys(data).length > 0) {
    return { ok: true, message: 'ok', data, raw: data }
  }

  return { ok: false, message: '操作失败', data: null, raw: data }
}

/**
 * @param {string} gn
 * @param {Record<string, any>} data
 * @param {{ silent?: boolean }} options silent=true 时失败不弹窗
 */
export async function apiGn(gn, data = {}, { silent = false } = {}) {
  try {
    const raw = await postGn(gn, data)
    const result = parseResult(raw)
    if (!result.ok) {
      console.warn(`[apiGn] ${gn} 失败:`, result.message, result.raw)
      if (!silent) {
        MessagePlugin.error(result.message || '请求失败')
      }
    }
    return result
  } catch (e) {
    let msg = e.message || '网络错误'
    const body = e?.response?.data
    if (typeof body === 'string' && body.trim()) {
      const plain = body.replace(/<[^>]+>/g, ' ').replace(/\s+/g, ' ').trim()
      if (plain) msg = plain.slice(0, 160)
    } else if (body && typeof body === 'object') {
      msg = body.code || body.msg || body.message || msg
    }
    console.error(`[apiGn] ${gn} 异常:`, e, body)
    if (!silent) MessagePlugin.error(msg)
    return { ok: false, message: msg, data: null, raw: body ?? null }
  }
}

export default http
