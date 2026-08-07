import { createRouter, createWebHashHistory } from 'vue-router'

const DockerLayout = () => import('@/docker/layouts/DockerLayout.vue')
const LoginView = () => import('@/docker/views/LoginView.vue')
const ConsoleView = () => import('@/docker/views/ConsoleView.vue')
const AppStoreView = () => import('@/docker/views/AppStoreView.vue')
const ImageView = () => import('@/docker/views/ImageView.vue')
const VolumeView = () => import('@/docker/views/VolumeView.vue')
const ComposeView = () => import('@/docker/views/ComposeView.vue')
const ProxyView = () => import('@/docker/views/ProxyView.vue')

const routes = [
  { path: '/login', name: 'login', component: LoginView, meta: { title: '登录 Docker 控制台' } },
  {
    path: '/',
    component: DockerLayout,
    redirect: '/console',
    children: [
      { path: 'console', name: 'console', component: ConsoleView, meta: { title: '我的容器', icon: 'mdi-view-dashboard-outline' } },
      { path: 'appstore', name: 'appstore', component: AppStoreView, meta: { title: '应用商店', icon: 'mdi-store' } },
      { path: 'proxy', name: 'proxy', component: ProxyView, meta: { title: '反向代理', icon: 'mdi-swap-horizontal' } },
      { path: 'image', name: 'image', component: ImageView, meta: { title: '镜像管理', icon: 'mdi-package-variant' } },
      { path: 'volume', name: 'volume', component: VolumeView, meta: { title: '数据卷', icon: 'mdi-database' } },
      { path: 'compose', name: 'compose', component: ComposeView, meta: { title: 'Compose', icon: 'mdi-file-document' } },
    ],
  },
]

const router = createRouter({
  history: createWebHashHistory(),
  routes,
})

router.afterEach((to) => {
  const t = to.meta?.title
  if (t) {
    const boot = window.__TD_BOOT__ || {}
    document.title = `${t} · ${boot.siteName || 'Docker'}`
  }
})

export default router
