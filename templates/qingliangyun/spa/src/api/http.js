import axios from 'axios'
import { ElMessage } from 'element-plus'

/**
 * MNBT 用户端 AJAX 统一走 ./ajax.php
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
 * 判断是否为「纯数据载荷」（无统一 code/qk 包装）
 * 如 urllist / getssl / getfdl / listurl
 */
function isDataPayload(obj) {
  if (!obj || typeof obj !== 'object' || Array.isArray(obj)) return false
  // 有明确失败标记则不是
  if (obj.qk === 4 || obj.qk === '4') return false
  if (obj.success === false) return false
  // 统一成功包装
  if (obj.qk === 1 || obj.qk === '1') return false
  if (obj.success === true) return false
  // 带 code 的多为消息型响应
  if (typeof obj.code === 'string' && obj.code !== '') return false
  // 常见数据字段
  const keys = Object.keys(obj)
  if (!keys.length) return true
  const dataHints = [
    'url', 'dir', 'dirs', 'domains', 'key', 'csr', 'pem',
    'fix', 'return_rule', 'http_status', 'cert_data',
    'httpTohttps', 'list', 'data', 'msg', 'tasks', 'logs',
    'binding', 'runPath', 'gzip', 'templates',
  ]
  return keys.some((k) => dataHints.includes(k))
}

/**
 * 解析 MNBT 响应
 *
 * 格式 A: { qk:1|4, code, msg }  — panel / 部分业务
 * 格式 B: { success, code, msg } — json_exit
 * 格式 C: { code: 'xxx成功' }    — 旧接口
 * 格式 D: 纯数据 { url, dir } / { key, csr } / getfdl 字段
 * 格式 E: 纯文本（伪静态规则）
 */
export function parseResult(data) {
  if (data == null || data === '') {
    return { ok: false, message: '无响应', data: null, raw: data }
  }

  // 纯文本：伪静态 hqjt 等
  if (typeof data === 'string') {
    const trimmed = data.trim()
    // 尝试 JSON
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
    // false 字符串
    if (trimmed === 'false' || trimmed === 'FALSE') {
      return { ok: true, message: 'ok', data: false, raw: data }
    }
    // 明显错误文案
    if (/失败|错误|请登陆|禁止|无法/.test(trimmed) && trimmed.length < 80) {
      return { ok: false, message: trimmed, data: null, raw: data }
    }
    // 当作成功正文（规则文件内容）
    return { ok: true, message: 'ok', data: data, raw: data }
  }

  if (typeof data !== 'object') {
    return { ok: true, message: 'ok', data, raw: data }
  }

  // 明确失败
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

  // 明确成功 qk=1
  if (data.qk === 1 || data.qk === '1') {
    return {
      ok: true,
      message: data.code || data.msg || 'ok',
      data: data.msg !== undefined ? data.msg : data.data !== undefined ? data.data : data,
      raw: data,
    }
  }

  // success: true（json_exit）
  if (data.success === true || data.success === 1 || data.success === '1') {
    return {
      ok: true,
      message: data.code || data.msg || 'ok',
      data: data.msg !== undefined ? data.msg : data.data !== undefined ? data.data : data,
      raw: data,
    }
  }

  // code 消息型
  if (typeof data.code === 'string' && data.code !== '') {
    const code = data.code
    // 登录拦截
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
    // 其它 code 视为业务错误
    return { ok: false, message: code, data: null, raw: data }
  }

  // 纯数据载荷（urllist / getssl / getfdl / listurl）
  if (isDataPayload(data) || Array.isArray(data)) {
    return { ok: true, message: 'ok', data, raw: data }
  }

  // 兜底：有字段的对象当数据
  if (Object.keys(data).length > 0) {
    return { ok: true, message: 'ok', data, raw: data }
  }

  return { ok: false, message: '操作失败', data: null, raw: data }
}

/**
 * @param {string} gn
 * @param {Record<string, any>} data
 * @param {{ silent?: boolean }} options silent=true 时失败不弹窗（用于页面初始化）
 */
export async function apiGn(gn, data = {}, { silent = false } = {}) {
  try {
    const raw = await postGn(gn, data)
    const result = parseResult(raw)
    if (!result.ok && !silent) {
      ElMessage.error(result.message || '请求失败')
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
    // 网络层错误才默认提示；可用 silent 关闭
    if (!silent) ElMessage.error(msg)
    return { ok: false, message: msg, data: null, raw: body ?? null }
  }
}

export default http
