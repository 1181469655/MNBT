import { apiGn } from '@/shared/api/http'

const S = { silent: true }

/** 插件列表 */
export function listPlugin() {
  return apiGn('plugin_list', {}, S)
}

/** 启用/禁用插件 */
export function setPluginEnabled(slug, enabled) {
  return apiGn('plugin_enable', { slug, enabled: enabled ? 'true' : 'false' })
}

/** 安装插件 */
export function installPlugin(slug) {
  return apiGn('plugin_install', { slug })
}

/** 卸载插件 */
export function uninstallPlugin(slug) {
  return apiGn('plugin_uninstall', { slug })
}
