import { createRouter, createWebHashHistory } from 'vue-router'

const UserLoginView = () => import('@/user/views/LoginView.vue')
const UserLayout = () => import('@/user/layouts/UserLayout.vue')
const UserDashboardView = () => import('@/user/views/dashboard/DashboardView.vue')
const UserSiteStatsView = () => import('@/user/views/stats/SiteStatsView.vue')
const UserSettingsView = () => import('@/user/views/settings/SettingsView.vue')
const UserMonitorView = () => import('@/user/views/monitor/MonitorView.vue')
const UserMonitorLogView = () => import('@/user/views/monitor/MonitorLogView.vue')
const UserNoticeView = () => import('@/user/views/NoticeView.vue')
const UserDeployView = () => import('@/user/views/deploy/DeployView.vue')
const UserSqlBackupView = () => import('@/user/views/database/SqlBackupView.vue')
const UserFtpView = () => import('@/user/views/ftp/FtpView.vue')
const UserPluginView = () => import('@/user/views/PluginView.vue')

const routes = [
  { path: '/login', name: 'user-login', component: UserLoginView, meta: { guest: true, title: '登录' } },
  {
    path: '/',
    component: UserLayout,
    meta: { auth: true },
    children: [
      { path: '', redirect: '/dashboard' },
      { path: 'dashboard', name: 'user-dashboard', component: UserDashboardView, meta: { title: '控制面板' } },
      { path: 'stats', name: 'user-stats', component: UserSiteStatsView, meta: { title: '站点统计' } },
      { path: 'settings/:tab', name: 'user-settings', component: UserSettingsView, meta: { title: '站点设置' } },
      { path: 'monitor', name: 'user-monitor', component: UserMonitorView, meta: { title: '监控任务' } },
      { path: 'monitor-log', name: 'user-monitor-log', component: UserMonitorLogView, meta: { title: '监控日志' } },
      { path: 'notice', name: 'user-notice', component: UserNoticeView, meta: { title: '通知日志' } },
      { path: 'deploy', name: 'user-deploy', component: UserDeployView, meta: { title: '一键部署' } },
      { path: 'sql-backup', name: 'user-sql-backup', component: UserSqlBackupView, meta: { title: 'SQL数据备份' } },
      { path: 'ftp', name: 'user-ftp', component: UserFtpView, meta: { title: '在线文件管理' } },
      { path: 'plugin', name: 'user-plugin', component: UserPluginView, meta: { title: '插件页面' } },
    ],
  },
  { path: '/:pathMatch(.*)*', redirect: '/dashboard' },
]

const router = createRouter({
  history: createWebHashHistory(),
  routes,
  scrollBehavior() {
    return { top: 0 }
  },
})

router.beforeEach((to, _from, next) => {
  const boot = window.__TD_BOOT__ || {}
  const loggedIn = !!boot.loggedIn
  document.title = `${to.meta.title || '控制面板'} · ${boot.siteName || 'MNBT'}`

  if (to.meta.guest && loggedIn) {
    next('/dashboard')
    return
  }
  if (to.meta.auth && !loggedIn && to.path !== '/login') {
    next('/login')
    return
  }
  next()
})

export default router
