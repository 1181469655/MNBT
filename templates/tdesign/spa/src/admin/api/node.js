import { apiGn } from '@/shared/api/http'

const S = { silent: true }

/** 节点列表 */
export function listNode(params) {
  return apiGn('listnode', params, S)
}

/** 新增节点 */
export function addNode(data) {
  return apiGn('addnode', data)
}

/** 删除节点 */
export function deleteNode(id) {
  return apiGn('delnode', { id })
}

/** 启用/停用节点 */
export function setNodeStatus(id, enabled) {
  return apiGn('setnodestatus', { id, enabled: enabled ? 'true' : 'false' })
}

/** 获取节点配置 */
export function nodeConfig(id) {
  return apiGn('nodeconfig', { id }, S)
}

/** 下发 Ping 任务 */
export function nodePing(id) {
  return apiGn('nodeping', { id })
}

/** 下发违禁词扫描 */
export function nodeForbiddenScan(data) {
  return apiGn('nodeforbiddenscan', data)
}

/** 节点任务列表 */
export function listNodeTask(params) {
  return apiGn('listnodetask', params, S)
}

/** 扫描记录列表 */
export function listForbiddenScan(params) {
  return apiGn('listforbiddenscan', params, S)
}

/** 命中记录列表 */
export function listForbiddenMatch(params) {
  return apiGn('listforbiddenmatch', params, S)
}

/** 节点统计 */
export function nodeStats() {
  return apiGn('nodestats', {}, S)
}

/** 全局违禁词 */
export function getGlobalKeywords() {
  return apiGn('get_global_keywords', {}, S)
}

/** 清理旧扫描 */
export function clearOldScans(days) {
  return apiGn('clearoldscans', { days })
}

/** 保存扫描配置 */
export function saveScanCfg(data) {
  return apiGn('savescancfg', data)
}

/** 节点日志列表 */
export function nodeListLog(nodeId) {
  return apiGn('nodeloglist', { node_id: nodeId }, S)
}

/** 节点日志内容 */
export function nodeLogContent(params) {
  return apiGn('nodelogcontent', params, S)
}
