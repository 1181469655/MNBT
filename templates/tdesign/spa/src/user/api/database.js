import { apiGn } from '@/shared/api/http'

/** 备份列表 (gn=backup_list) */
export function listSqlBackup() {
  return apiGn('backup_list', {}, { silent: true })
}
/** 创建备份 (gn=databaseadd, id=数据库ID, 即 backup_list 返回的 db_id) */
export function createBackup(id) {
  return apiGn('databaseadd', { id })
}
/** 下载备份 (gn=databasedownload, filename=备份文件名, 返回 url 字段为下载链接) */
export function downloadBackup(filename) {
  return apiGn('databasedownload', { filename })
}
/** 恢复备份 (gn=databaserestore, user=数据库用户名, filename=备份文件名) */
export function restoreBackup(user, filename) {
  return apiGn('databaserestore', { user, filename })
}
/** 删除备份 (gn=databasedel, id=备份ID) */
export function deleteBackup(id) {
  return apiGn('databasedel', { id })
}
