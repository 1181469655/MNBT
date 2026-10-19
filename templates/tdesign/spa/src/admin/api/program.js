import { apiGn, postGn, parseResult } from '@/shared/api/http'

const S = { silent: true }

/** 程序列表 */
export function listProgram(params) {
  return apiGn('listbs', params, S)
}

/** 添加程序(含文件上传,需用 FormData) */
export async function addProgram(formData) {
  const res = await postGn('cxtj', formData)
  return parseResult(res)
}

/** 修改程序 */
export function updateProgram(data) {
  return apiGn('cxxgjl', data)
}

/** 删除程序 */
export function deleteProgram(id) {
  return apiGn('cxsc', { id })
}

/** 批量删除程序 */
export function deleteProgramBatch(idsz) {
  return apiGn('cxscxz', { idsz })
}

/** 导出程序 */
export function exportProgram(ids) {
  return apiGn('cxdc', { id: ids })
}

/** 导入程序(分片上传) */
export function importProgramFile(data) {
  return apiGn('cxfiledru', data, S)
}
