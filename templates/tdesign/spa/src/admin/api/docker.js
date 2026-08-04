import { apiGn } from '@/shared/api/http'

const S = { silent: true }

/**
 * Docker 管理（admin/ajax.php 分发 docker_* 指令）
 * 返回格式：{ code:0, msg, count, data }，data 为列表或节点/套餐选项
 */

// ===== 节点 =====

/** 节点列表 */
export function listDockerNode() {
  return apiGn('docker_node_list', {}, S)
}

/** 添加节点 */
export function addDockerNode(data) {
  return apiGn('docker_node_add', data)
}

/** 编辑节点 */
export function editDockerNode(data) {
  return apiGn('docker_node_edit', data)
}

/** 删除节点 */
export function delDockerNode(id) {
  return apiGn('docker_node_del', { id })
}

/** 节点 Docker 配置（检测安装状态） */
export function dockerNodeConfig(nodeId) {
  return apiGn('docker_node_config', { node_id: nodeId }, S)
}

/** 节点容器列表 */
export function dockerNodeContainers(nodeId) {
  return apiGn('docker_node_containers', { node_id: nodeId }, S)
}

// ===== 用户 =====

/** 用户列表（JOIN 节点名/套餐名） */
export function listDockerUser() {
  return apiGn('docker_user_list', {}, S)
}

/** 添加用户 */
export function addDockerUser(data) {
  return apiGn('docker_user_add', data)
}

/** 编辑用户 */
export function editDockerUser(data) {
  return apiGn('docker_user_edit', data)
}

/** 删除用户 */
export function delDockerUser(id) {
  return apiGn('docker_user_del', { id })
}

/** 重置密码 */
export function resetDockerUser(id, password) {
  return apiGn('docker_user_reset', { id, password })
}

/** 暂停 / 恢复 */
export function setDockerUserStatus(id, paused) {
  return apiGn(paused ? 'docker_user_pause' : 'docker_user_resume', { id })
}

// ===== 套餐 =====

/** 套餐列表 */
export function listDockerPlan() {
  return apiGn('docker_plan_list', {}, S)
}

/** 添加套餐 */
export function addDockerPlan(data) {
  return apiGn('docker_plan_add', data)
}

/** 编辑套餐 */
export function editDockerPlan(data) {
  return apiGn('docker_plan_edit', data)
}

/** 删除套餐 */
export function delDockerPlan(id) {
  return apiGn('docker_plan_del', { id })
}

// ===== 选项（节点/套餐下拉，密钥字段不回传） =====
export function dockerOptions() {
  return apiGn('docker_options', {}, S)
}
