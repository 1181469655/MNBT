import { createRouter, createWebHashHistory } from 'vue-router'

const LoginView = () => import('@/admin/views/LoginView.vue')
const LayoutView = () => import('@/admin/layouts/AdminLayout.vue')
const DashboardView = () => import('@/admin/views/DashboardView.vue')

const SettingsWebsite = () => import('@/admin/views/settings/WebsiteView.vue')
const SettingsAdmin = () => import('@/admin/views/settings/AdminView.vue')
const SettingsApi = () => import('@/admin/views/settings/ApiView.vue')
const SettingsMail = () => import('@/admin/views/settings/MailView.vue')
const SettingsPanel = () => import('@/admin/views/settings/PanelView.vue')
const SettingsMonitor = () => import('@/admin/views/settings/MonitorView.vue')
const SettingsTheme = () => import('@/admin/views/settings/ThemeView.vue')

const HostListView = () => import('@/admin/views/host/HostListView.vue')
const HostAddView = () => import('@/admin/views/host/HostAddView.vue')
const BaotaListView = () => import('@/admin/views/baota/BaotaListView.vue')
const BaotaAddView = () => import('@/admin/views/baota/BaotaAddView.vue')
const NodeListView = () => import('@/admin/views/node/NodeListView.vue')
const NodeScanView = () => import('@/admin/views/node/NodeScanView.vue')

const ProgramListView = () => import('@/admin/views/program/ProgramListView.vue')
const ProgramAddView = () => import('@/admin/views/program/ProgramAddView.vue')
const ProgramImportView = () => import('@/admin/views/program/ProgramImportView.vue')
const OrderListView = () => import('@/admin/views/order/OrderListView.vue')
const LogListView = () => import('@/admin/views/log/LogListView.vue')
const PluginListView = () => import('@/admin/views/plugin/PluginListView.vue')
const PluginPageView = () => import('@/admin/views/PluginPageView.vue')
const PaySettingsView = () => import('@/admin/views/pay/PaySettingsView.vue')
const TutorialView = () => import('@/admin/views/TutorialView.vue')
const UpdateView = () => import('@/admin/views/UpdateView.vue')
const RepairView = () => import('@/admin/views/RepairView.vue')

const DockerNodeView = () => import('@/admin/views/docker/DockerNodeView.vue')
const DockerNodeAddView = () => import('@/admin/views/docker/DockerNodeAddView.vue')
const DockerUserView = () => import('@/admin/views/docker/DockerUserView.vue')
const DockerPlanView = () => import('@/admin/views/docker/DockerPlanView.vue')

const routes = [
  { path: '/login', name: 'login', component: LoginView, meta: { guest: true, title: '登录' } },
  {
    path: '/',
    component: LayoutView,
    meta: { auth: true },
    children: [
      { path: '', redirect: '/dashboard' },
      { path: 'dashboard', name: 'dashboard', component: DashboardView, meta: { title: '仪表盘' } },

      { path: 'settings/website', name: 'settings-website', component: SettingsWebsite, meta: { title: '网站设置' } },
      { path: 'settings/admin', name: 'settings-admin', component: SettingsAdmin, meta: { title: '管理设置' } },
      { path: 'settings/api', name: 'settings-api', component: SettingsApi, meta: { title: 'API 设置' } },
      { path: 'settings/mail', name: 'settings-mail', component: SettingsMail, meta: { title: '邮箱设置' } },
      { path: 'settings/panel', name: 'settings-panel', component: SettingsPanel, meta: { title: '控制面板' } },
      { path: 'settings/monitor', name: 'settings-monitor', component: SettingsMonitor, meta: { title: '监控设置' } },
      { path: 'settings/theme', name: 'settings-theme', component: SettingsTheme, meta: { title: '前端模板' } },

      { path: 'host', name: 'host', component: HostListView, meta: { title: '主机列表' } },
      { path: 'host/add', name: 'host-add', component: HostAddView, meta: { title: '添加主机' } },
      { path: 'baota', name: 'baota', component: BaotaListView, meta: { title: '宝塔列表' } },
      { path: 'baota/add', name: 'baota-add', component: BaotaAddView, meta: { title: '添加宝塔' } },
      { path: 'node', name: 'node', component: NodeListView, meta: { title: '节点列表' } },
      { path: 'node/scan', name: 'node-scan', component: NodeScanView, meta: { title: '违禁词扫描' } },

      { path: 'docker/node', name: 'docker-node', component: DockerNodeView, meta: { title: 'Docker 节点' } },
      { path: 'docker/node/add', name: 'docker-node-add', component: DockerNodeAddView, meta: { title: '添加 Docker 节点' } },
      { path: 'docker/user', name: 'docker-user', component: DockerUserView, meta: { title: 'Docker 用户' } },
      { path: 'docker/plan', name: 'docker-plan', component: DockerPlanView, meta: { title: 'Docker 套餐' } },

      { path: 'program', name: 'program', component: ProgramListView, meta: { title: '程序列表' } },
      { path: 'program/add', name: 'program-add', component: ProgramAddView, meta: { title: '添加程序' } },
      { path: 'program/import', name: 'program-import', component: ProgramImportView, meta: { title: '导入程序' } },
      { path: 'order', name: 'order', component: OrderListView, meta: { title: '订单列表' } },
      { path: 'log', name: 'log', component: LogListView, meta: { title: '操作日志' } },
      { path: 'plugin', name: 'plugin', component: PluginListView, meta: { title: '插件管理' } },
      { path: 'plugin/page', name: 'plugin-page', component: PluginPageView, meta: { title: '插件页面' } },
      { path: 'pay', name: 'pay', component: PaySettingsView, meta: { title: '支付设置' } },
      { path: 'tutorial', name: 'tutorial', component: TutorialView, meta: { title: '教程与监控' } },
      { path: 'update', name: 'update', component: UpdateView, meta: { title: '系统更新' } },
      { path: 'repair', name: 'repair', component: RepairView, meta: { title: '系统修复' } },
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
  document.title = `${to.meta.title || '管理后台'} · ${boot.siteName || 'MNBT'}`

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
