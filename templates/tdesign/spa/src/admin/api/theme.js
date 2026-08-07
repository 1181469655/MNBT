/**
 * 主题列表查询接口
 * 系统未提供独立 API,但 set.php?gn=theme 页面在 default 主题里直接读取 mnbt_theme_list()
 * 这里我们通过 fetch 同页 HTML 取主题列表不现实,因此提供一个静态的「主题信息」获取器:
 * 让后端在 _spa_boot.php 注入了 active_admin_theme / active_user_theme
 * 主题列表本身通过 ajax 调用 set.php?gn=theme 的 JSON 模式获取(若未来实现)。
 *
 * 当前实现:从 boot 中读 active_user_theme / active_admin_theme,
 * 主题列表请通过 set.php?gn=theme 页面 default 视图获取。
 */
import { apiGn } from '@/shared/api/http'

const S = { silent: true }

/** 占位:列出主题(若系统未来提供 listtheme API) */
export function listThemes() {
  return apiGn('listthemes', {}, S)
}
