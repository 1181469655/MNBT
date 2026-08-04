import { apiGn } from '@/shared/api/http'

const S = { silent: true }

/** 宝塔列表 */
export function listBaota(params) {
  return apiGn('listbt', params, S)
}

/** 添加宝塔 */
export function addBaota(data) {
  return apiGn('addbt', data)
}

/** 修改宝塔 */
export function updateBaota(data) {
  return apiGn('xgjl', data)
}

/** 删除宝塔 */
export function deleteBaota(id) {
  return apiGn('btsc', { id })
}

/** 获取单条宝塔数据 */
export function getBaota(id) {
  return apiGn('btsj', { id }, S)
}

/** 宝塔通信检测 */
export function checkBaotaConnect(btid) {
  return apiGn('btztjc', { btid }, S)
}

/** 节点 PHP 版本列表 */
export function listNodePhp(btdh) {
  return apiGn('list_node_php', { btdh }, S)
}

/** 自动检测节点 PHP */
export function autoDetectNodePhp(btdh) {
  return apiGn('auto_detect_node_php', { btdh })
}

/** 设置节点默认 PHP */
export function setNodePhp(btdh, version) {
  return apiGn('set_node_php', { btdh, version })
}
