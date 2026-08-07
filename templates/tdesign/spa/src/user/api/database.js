import { apiGn } from '@/shared/api/http'

/** 备份列表 (gn=backup_list) */
export function listSqlBackup() {
  return apiGn('backup_list', {}, { silent: true })
}
/** 创建备份 (gn=databaseadd) */
export function createBackup() {
  return apiGn('databaseadd')
}
/** 下载备份 (gn=databasedownload, id=备份ID) */
export function downloadBackup(id) {
  return apiGn('databasedownload', { id })
}
/** 恢复备份 (gn=databaserestore, id=备份ID) */
export function restoreBackup(id) {
  return apiGn('databaserestore', { id })
}
/** 删除备份 (gn=databasedel, id=备份ID) */
export function deleteBackup(id) {
  return apiGn('databasedel', { id })
}
