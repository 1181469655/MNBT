import { postGn } from '@/shared/api/http'
import { MessagePlugin } from 'tdesign-vue-next'

/**
 * 读取 CSRF token（优先 meta 标签，回退 cookie）
 * docker/ajax.php 对所有请求校验 CSRF，虽然 mnbt_csrf_inject_html 会注入
 * XHR 补丁自动加 X-CSRF-Token 头，但这里显式兜底更稳妥。
 */
function getCsrfToken() {
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

/**
 * Docker 控制台 API 封装
 *
 * docker/ajax.php 返回格式：{ success, code, msg, ...extra }
 * 与 admin/ajax.php 格式不同（admin 用 {qk, code, msg} 或 {code:'成功'}），
 * 因此不能用 shared/api/http.js 的 apiGn（会把 msg 字符串误当数据载荷提取）。
 * 这里直接用 postGn + 自定义解析。
 *
 * @param {string} gn      ajax.php 的 gn 指令
 * @param {object} data    表单数据
 * @param {{silent?:boolean}} opts  silent=true 时失败不弹窗
 * @returns {{ok:boolean, message:string, data:object, raw:object}}
 *         data 是整个响应对象，前端按需取 data.container / data.me / data.node 等
 */
export async function dkApi(gn, data = {}, { silent = false } = {}) {
  try {
    // 显式附加 CSRF token（docker/ajax.php 校验所有请求）
    const token = getCsrfToken()
    if (token && !data._csrf) {
      data = { ...data, _csrf: token }
    }
    const raw = await postGn(gn, data)
    const ok = raw.success === true || raw.code === 200 || raw.success === 1
    const result = {
      ok,
      message: raw.msg || '',
      data: raw,
      raw,
    }
    if (!ok && !silent) {
      MessagePlugin.error(result.message || '请求失败')
    }
    return result
  } catch (e) {
    let msg = e.message || '网络错误'
    const body = e?.response?.data
    if (typeof body === 'string') {
      // 会话过期：docker_auth_require() 返回 <script> 跳转 login.php
      if (body.includes('login.php') && body.includes('<script')) {
        MessagePlugin.warning('登录已过期，正在跳转登录页…')
        setTimeout(() => { window.location.href = './login.php' }, 800)
        return { ok: false, message: '登录已过期', data: null, raw: body }
      }
      const plain = body.replace(/<[^>]+>/g, ' ').replace(/\s+/g, ' ').trim()
      if (plain) msg = plain.slice(0, 160)
    } else if (body && typeof body === 'object') {
      msg = body.msg || body.message || msg
    }
    console.error(`[dkApi] ${gn} 异常:`, e, body)
    if (!silent) MessagePlugin.error(msg)
    return { ok: false, message: msg, data: null, raw: body ?? null }
  }
}

// ===== 认证 =====

/** 登录 */
export function dockerLogin(username, password) {
  return dkApi('login', { username, password })
}

/** 登出 */
export function dockerLogout() {
  return dkApi('logout', {}, { silent: true })
}

// ===== 我的容器 =====

/** 获取我的容器 + 用户信息 + 节点信息 */
export function getMyContainer() {
  return dkApi('my_container', {}, { silent: true })
}

/** 容器启动 */
export function containerStart() {
  return dkApi('container_start')
}

/** 容器停止 */
export function containerStop() {
  return dkApi('container_stop')
}

/** 容器重启 */
export function containerRestart() {
  return dkApi('container_restart')
}

// ===== 应用商店 =====

/** 应用列表 */
export function listApp() {
  return dkApi('app_list', {}, { silent: true })
}

/** 应用详情 */
export function getAppDetail(appname) {
  return dkApi('app_detail', { appname }, { silent: true })
}

/** 应用依赖 */
export function getAppDependence(appname) {
  return dkApi('app_dependence', { appname }, { silent: true })
}

/** 创建应用（开通容器） */
export function createApp(params) {
  return dkApi('app_create', params)
}

// ===== 容器操作 =====

/** 删除容器（卸载应用） */
export function containerRemove() {
  return dkApi('container_remove')
}

/** 获取容器端口列表 */
export function getContainerPorts() {
  return dkApi('container_ports', {}, { silent: true })
}

// ===== 反向代理 =====

/** 反向代理列表 */
export function listProxy() {
  return dkApi('proxy_list', {}, { silent: true })
}

/** 创建反向代理 */
export function createProxy(params) {
  return dkApi('proxy_create', params)
}

/** 删除反向代理 */
export function deleteProxy(id, siteName) {
  return dkApi('proxy_delete', { id, site_name: siteName })
}

// ===== 镜像 =====

/** 镜像列表 */
export function listImage() {
  return dkApi('image_list', {}, { silent: true })
}

// ===== 数据卷 =====

/** 数据卷列表 */
export function listVolume() {
  return dkApi('volume_list', {}, { silent: true })
}

// ===== Compose =====

/** Compose 模板 + 项目 */
export function listCompose() {
  return dkApi('compose_list', {}, { silent: true })
}
