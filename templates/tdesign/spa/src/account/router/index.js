import { createRouter, createWebHashHistory } from 'vue-router'
import AccountLayout from '@/account/layouts/AccountLayout.vue'

const routes = [
  {
    path: '/login',
    name: 'login',
    component: () => import('@/account/views/LoginView.vue'),
    meta: { title: '登录' },
  },
  {
    path: '/register',
    name: 'register',
    component: () => import('@/account/views/RegisterView.vue'),
    meta: { title: '注册' },
  },
  {
    path: '/',
    component: AccountLayout,
    meta: { requiresAuth: true },
    children: [
      {
        path: '',
        redirect: '/dashboard',
      },
      {
        path: 'dashboard',
        name: 'dashboard',
        component: () => import('@/account/views/DashboardView.vue'),
        meta: { title: '控制面板', requiresAuth: true },
      },
      {
        path: 'profile',
        name: 'profile',
        component: () => import('@/account/views/ProfileView.vue'),
        meta: { title: '个人信息', requiresAuth: true },
      },
      {
        path: 'password',
        name: 'password',
        component: () => import('@/account/views/PasswordView.vue'),
        meta: { title: '修改密码', requiresAuth: true },
      },
      {
        path: 'balance',
        name: 'balance',
        component: () => import('@/account/views/BalanceView.vue'),
        meta: { title: '我的余额', requiresAuth: true },
      },
      {
        path: 'balance/recharge',
        name: 'balance-recharge',
        component: () => import('@/account/views/RechargeView.vue'),
        meta: { title: '余额充值', requiresAuth: true },
      },
      {
        path: 'shop',
        name: 'shop',
        component: () => import('@/account/views/ShopView.vue'),
        meta: { title: '主机商城', requiresAuth: true },
      },
      {
        path: 'hosting',
        name: 'hosting',
        component: () => import('@/account/views/HostingView.vue'),
        meta: { title: '我的主机', requiresAuth: true },
      },
      {
        path: 'orders',
        name: 'orders',
        component: () => import('@/account/views/OrdersView.vue'),
        meta: { title: '我的订单', requiresAuth: true },
      },
      {
        path: 'docker-shop',
        name: 'docker-shop',
        component: () => import('@/account/views/DockerShopView.vue'),
        meta: { title: 'Docker 商城', requiresAuth: true },
      },
      {
        path: 'docker-assets',
        name: 'docker-assets',
        component: () => import('@/account/views/DockerAssetsView.vue'),
        meta: { title: '我的 Docker', requiresAuth: true },
      },
      {
        path: 'docker-orders',
        name: 'docker-orders',
        component: () => import('@/account/views/DockerOrdersView.vue'),
        meta: { title: 'Docker 订单', requiresAuth: true },
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

function isLoggedIn() {
  const boot = window.__TD_BOOT__ || {}
  return !!(boot.loggedIn && boot.accountUser)
}

// 全局前置守卫：未登录只允许访问 /login /register
router.beforeEach((to) => {
  const loggedIn = isLoggedIn()
  if (to.meta.requiresAuth && !loggedIn) {
    return { name: 'login', query: { redirect: to.fullPath } }
  }
  if ((to.name === 'login' || to.name === 'register') && loggedIn) {
    return { name: 'dashboard' }
  }
  return true
})

export default router
