import { apiGn } from '@/shared/api/http'

// ============ 监控任务 ============
/** 监控任务列表 (gn=monitor_list) */
export function listMonitorTasks() {
  return apiGn('monitor_list', {}, { silent: true })
}
/** 保存监控任务 (gn=monitor_save) */
export function saveMonitorTask(data) {
  return apiGn('monitor_save', data)
}
/** 删除监控任务 (gn=monitor_del, id=任务ID) */
export function deleteMonitorTask(id) {
  return apiGn('monitor_del', { id })
}
/** 切换监控任务开关 (gn=monitor_toggle, id=任务ID) */
export function toggleMonitorTask(id) {
  return apiGn('monitor_toggle', { id })
}

// ============ 监控日志 ============
/** 监控日志列表 (gn=monitor_log_list, id=任务ID, page, page_size) */
export function listMonitorLog(id = 0, page = 1, page_size = 20) {
  return apiGn('monitor_log_list', { id, page, page_size }, { silent: true })
}

// ============ 通知 ============
/** 通知列表 (gn=notice_list, page, page_size) */
export function listNoticeLog(page = 1, page_size = 15) {
  return apiGn('notice_list', { page, page_size }, { silent: true })
}
/** 标记通知已读 (gn=notice_read, id=通知ID) */
export function markNoticeRead(id) {
  return apiGn('notice_read', { id })
}
