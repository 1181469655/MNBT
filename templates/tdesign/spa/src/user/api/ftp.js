/**
 * 在线文件管理 API（gn → user/api/file.php）
 * 后端契约：成功 {qk:1, code, ...payload 平铺}；失败 {success:false, qk:4, code}
 * 路径一律站点相对路径（/ 开头，站点根为 /），详见 docs/api/user.md §3.3
 */
import { postGn } from '@/shared/api/http'
import http from '@/shared/api/http'

function ajaxUrl() {
  const boot = window.__TD_BOOT__ || {}
  return boot.ajaxBase || './ajax.php'
}

async function call(gn, data = {}) {
  try {
    const raw = await postGn(gn, data)
    if (raw && (raw.qk === 1 || raw.qk === '1' || raw.success === true)) {
      return { ok: true, code: raw.code || raw.msg || '操作成功', raw }
    }
    return { ok: false, code: (raw && (raw.code || raw.msg)) || '操作失败', raw }
  } catch (e) {
    let msg = (e && e.message) || '网络错误'
    const body = e && e.response && e.response.data
    if (typeof body === 'string' && body.trim()) msg = body.replace(/<[^>]+>/g, ' ').trim().slice(0, 160) || msg
    else if (body && typeof body === 'object') msg = body.code || body.msg || msg
    return { ok: false, code: msg, raw: null }
  }
}

export const listDir = (path, page, limit, sort, sortOrder) =>
  call('file_list', { path, page, limit, sort, sortOrder })

export const readFile = (path) => call('file_read', { path })

export const saveFile = (path, content) => call('file_save', { path, content })

export const createEntry = (path, name, type) => call('file_create', { path, name, type })

export const deleteOne = (path, name, type) => call('file_delete', { path, name, type })

export const deleteBatch = (path, names) => call('file_delete_batch', { path, names })

export const rename = (path, oldname, newname) => call('file_rename', { path, oldname, newname })

export const copyPaste = (ypath, xpath, names, type) => call('file_copy', { ypath, xpath, names, type })

export const compress = (path, names, type, dest) => call('file_compress', { path, names, type, dest })

export const unzip = (path, name, dest, password, coding) =>
  call('file_unzip', { path, name, dest, password, coding })

export const dirSize = (path) => call('file_size', { path })

export const fileDownload = (path, name) => call('file_download', { path, name })

export const getAccess = (path) => call('file_access', { path })

export const setAccess = (path, access) => call('file_access_set', { path, access })

/** 从文件管理器导入 SQL 文件（site.php 的 sqldr） */
export const importSql = (path, filename) => call('sqldr', { path, filename })

/** 断点续传：查询节点已接收字节数（返回 {ok, size}) */
export async function uploadPrepare(path, name, size) {
  const r = await call('file_upload_prepare', { path, name, size: String(size) })
  return { ok: r.ok, code: r.code, size: r.ok ? Number(r.raw.size) || 0 : 0 }
}

/**
 * 分片上传（原始响应：{qk:1, size:下一偏移, done:false} / {qk:1, done:true} / {qk:4, code}）
 * signal: AbortSignal，用于取消上传
 */
export function uploadChunk(path, name, start, size, blob, signal) {
  const fd = new FormData()
  fd.append('gn', 'file_upload')
  fd.append('path', path)
  fd.append('name', name)
  fd.append('start', String(start))
  fd.append('size', String(size))
  fd.append('file', blob)
  return http.post(ajaxUrl(), fd, {
    headers: { 'Content-Type': 'multipart/form-data' },
    signal,
  }).then((res) => res.data)
}

export default http
