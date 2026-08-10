import { reactive } from 'vue'
import { getMe } from '@/home/api/account'

/**
 * 共享登录态 store
 * user_info 插件认证（account_token cookie）
 */
export const authState = reactive({
  loading: false,
  loggedIn: false,
  user: null,
  initialized: false,
})

/** 探测当前登录态（不弹窗） */
export async function initAuth() {
  if (authState.loading) return authState
  authState.loading = true
  try {
    const res = await getMe()
    if (res.ok && res.data?.logged_in) {
      authState.loggedIn = true
      authState.user = res.data.user || null
    } else {
      authState.loggedIn = false
      authState.user = null
    }
  } catch {
    authState.loggedIn = false
    authState.user = null
  } finally {
    authState.loading = false
    authState.initialized = true
  }
  return authState
}

/** 清除本地登录态（退出/失效时调用） */
export function resetAuth() {
  authState.loggedIn = false
  authState.user = null
  authState.initialized = true
}

export default authState
