import { apiGn } from '@/shared/api/http'

/**
 * 获取用户端首页配置（公告、空间、PHP版本、FTP/SQL信息等）
 * gn=indexconf, 返回 {qk:1, code:'获取成功', msg:{gg, web, sql, lls, php, config, qk}}
 */
export function getIndexConf() {
  return apiGn('indexconf', {}, { silent: true })
}

/**
 * 刷新空间用量（网页/数据库/流量）
 * gn=sxsyxx
 */
export function refreshSpace() {
  return apiGn('sxsyxx', {}, { silent: true })
}
