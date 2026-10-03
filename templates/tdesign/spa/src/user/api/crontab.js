import { apiGn } from '@/shared/api/http'

// ============ 计划任务（虚拟主机） ============
/** 任务列表 (gn=crontab_list) */
export function listCronTasks() {
  return apiGn('crontab_list', {}, { silent: true })
}
/** 新建任务 (gn=crontab_add) */
export function addCronTask(data) {
  return apiGn('crontab_add', data)
}
/** 编辑任务：仅名称与周期，类型/目标服务端锁定 (gn=crontab_edit) */
export function editCronTask(data) {
  return apiGn('crontab_edit', data)
}
/** 删除任务 (gn=crontab_del, id=任务ID) */
export function deleteCronTask(id) {
  return apiGn('crontab_del', { id })
}
/** 启用/暂停 (gn=crontab_toggle, id, status: '1'|'0') */
export function toggleCronTask(id, status) {
  return apiGn('crontab_toggle', { id, status })
}
/** 立即执行一次 (gn=crontab_exec, id=任务ID) */
export function execCronTask(id) {
  return apiGn('crontab_exec', { id })
}
/** 执行日志 (gn=crontab_logs, id=任务ID) */
export function cronTaskLogs(id) {
  return apiGn('crontab_logs', { id }, { silent: true })
}
