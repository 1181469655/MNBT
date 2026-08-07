import { apiGn } from '@/shared/api/http'

/** 一键部署程序列表 (gn=deploy_list) */
export function listDeployPrograms() {
  return apiGn('deploy_list', {}, { silent: true })
}
/** 部署程序 (gn=yjbs, id=程序ID) */
export function deployProgram(id) {
  return apiGn('yjbs', { id })
}
