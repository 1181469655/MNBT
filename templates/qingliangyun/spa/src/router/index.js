import { createRouter, createWebHashHistory } from 'vue-router'
import LoginView from '../views/LoginView.vue'
import LayoutView from '../views/LayoutView.vue'
import DashboardView from '../views/DashboardView.vue'
import StatsView from '../views/StatsView.vue'
import MonitorView from '../views/MonitorView.vue'
import NoticeView from '../views/NoticeView.vue'
import SettingsView from '../views/SettingsView.vue'
import LegacyFrame from '../views/LegacyFrame.vue'

// 静态 import：单文件构建，避免 PHP 嵌入时动态 chunk 路径错误
const routes = [
  {
    path: '/login',
    name: 'login',
    component: LoginView,
    meta: { guest: true, title: '登录' },
  },
  {
    path: '/',
    component: LayoutView,
    meta: { auth: true },
    children: [
      { path: '', redirect: '/dashboard' },
      {
        path: 'dashboard',
        name: 'dashboard',
        component: DashboardView,
        meta: { title: '控制面板' },
      },
      {
        path: 'stats',
        name: 'stats',
        component: StatsView,
        meta: { title: '站点统计' },
      },
      {
        path: 'monitor',
        name: 'monitor',
        component: MonitorView,
        meta: { title: '监控任务' },
      },
      {
        path: 'notice',
        name: 'notice',
        component: NoticeView,
        meta: { title: '通知日志' },
      },
      {
        path: 'settings/:gn?',
        name: 'settings',
        component: SettingsView,
        meta: { title: '站点设置' },
      },
      {
        path: 'legacy',
        name: 'legacy',
        component: LegacyFrame,
        meta: { title: '功能' },
      },
    ],
  },
  {
    path: '/:pathMatch(.*)*',
    redirect: '/dashboard',
  },
]

const router = createRouter({
  history: createWebHashHistory(),
  routes,
})

router.beforeEach((to, _from, next) => {
  const boot = window.__QL_BOOT__ || {}
  const loggedIn = !!boot.loggedIn
  document.title = `${to.meta.title || '控制面板'} · ${boot.siteName || '清凉云'}`

  if (to.meta.guest && loggedIn) {
    next('/dashboard')
    return
  }
  if (to.meta.auth && !loggedIn && to.path !== '/login') {
    // 未登录时仍允许进入 login；壳路由跳转登录
    if (to.name === 'login') {
      next()
      return
    }
    next('/login')
    return
  }
  next()
})

export default router
