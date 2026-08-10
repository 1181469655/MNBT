import { createRouter, createWebHashHistory } from 'vue-router'
import authState, { initAuth } from '@/home/store/auth'

const HomeLayout = () => import('@/home/layouts/HomeLayout.vue')
const LandingView = () => import('@/home/views/LandingView.vue')
const LoginView = () => import('@/home/views/auth/LoginView.vue')
const RegisterView = () => import('@/home/views/auth/RegisterView.vue')
const ProfileView = () => import('@/home/views/auth/ProfileView.vue')
const PasswordView = () => import('@/home/views/auth/PasswordView.vue')
const ShopView = () => import('@/home/views/shop/ShopView.vue')
const OrderView = () => import('@/home/views/shop/OrderView.vue')
const AssetsView = () => import('@/home/views/shop/AssetsView.vue')
const OrdersView = () => import('@/home/views/shop/OrdersView.vue')
const BalanceView = () => import('@/home/views/balance/BalanceView.vue')
const RechargeView = () => import('@/home/views/balance/RechargeView.vue')
// 官网内容（official_site 插件）
const AboutView = () => import('@/home/views/site/AboutView.vue')
const SiteProductsView = () => import('@/home/views/site/products/ProductsView.vue')
const SiteProductDetailView = () => import('@/home/views/site/products/ProductDetailView.vue')
const SiteNewsView = () => import('@/home/views/site/news/NewsView.vue')
const SiteNewsDetailView = () => import('@/home/views/site/news/NewsDetailView.vue')
const ContactView = () => import('@/home/views/site/ContactView.vue')

const routes = [
  {
    path: '/',
    component: HomeLayout,
    children: [
      { path: '', name: 'home-landing', component: LandingView, meta: { title: '' } },
      { path: 'login', name: 'home-login', component: LoginView, meta: { title: '登录', guest: true, cap: 'user' } },
      { path: 'register', name: 'home-register', component: RegisterView, meta: { title: '注册', guest: true, cap: 'user' } },
      { path: 'profile', name: 'home-profile', component: ProfileView, meta: { title: '个人信息', auth: true, cap: 'user' } },
      { path: 'password', name: 'home-password', component: PasswordView, meta: { title: '修改密码', auth: true, cap: 'user' } },
      { path: 'shop', name: 'home-shop', component: ShopView, meta: { title: '主机套餐', cap: 'shop' } },
      { path: 'shop/order/:planId', name: 'home-order', component: OrderView, meta: { title: '购买套餐', auth: true, cap: 'shop' } },
      { path: 'shop/assets', name: 'home-assets', component: AssetsView, meta: { title: '我的主机', auth: true, cap: 'shop' } },
      { path: 'shop/orders', name: 'home-orders', component: OrdersView, meta: { title: '我的订单', auth: true, cap: 'shop' } },
      { path: 'balance', name: 'home-balance', component: BalanceView, meta: { title: '我的余额', auth: true, cap: 'balance' } },
      { path: 'balance/recharge', name: 'home-recharge', component: RechargeView, meta: { title: '余额充值', auth: true, cap: 'balance' } },
      // 官网内容（official_site 插件）
      { path: 'about', name: 'home-about', component: AboutView, meta: { title: '关于我们', cap: 'site' } },
      { path: 'site/products', name: 'home-site-products', component: SiteProductsView, meta: { title: '产品中心', cap: 'site' } },
      { path: 'site/products/:id', name: 'home-site-product-detail', component: SiteProductDetailView, meta: { title: '产品详情', cap: 'site' } },
      { path: 'site/news', name: 'home-site-news', component: SiteNewsView, meta: { title: '新闻资讯', cap: 'site' } },
      { path: 'site/news/:id', name: 'home-site-news-detail', component: SiteNewsDetailView, meta: { title: '新闻详情', cap: 'site' } },
      { path: 'site/contact', name: 'home-site-contact', component: ContactView, meta: { title: '联系我们', cap: 'site' } },
    ],
  },
  { path: '/:pathMatch(.*)*', redirect: '/' },
]

const router = createRouter({
  history: createWebHashHistory(),
  routes,
  scrollBehavior() {
    return { top: 0 }
  },
})

router.beforeEach(async (to, _from, next) => {
  const boot = window.__TD_BOOT__ || {}
  document.title = to.meta.title ? `${to.meta.title} · ${boot.siteTitle || 'MNBT'}` : boot.siteTitle || 'MNBT'

  if (!authState.initialized) {
    await initAuth()
  }
  if (to.meta.guest && authState.loggedIn) {
    next('/')
    return
  }
  if (to.meta.auth && !authState.loggedIn) {
    next('/login')
    return
  }
  // 插件能力校验：对应插件未启用时自动禁用（hasXxx 仅在明确 false 时拦截,兼容旧注入缺字段）
  const cap = to.meta.cap
  if (cap === 'shop' && boot.hasShop === false) {
    next('/')
    return
  }
  if (cap === 'balance' && boot.hasBalance === false) {
    next('/')
    return
  }
  if (cap === 'user' && boot.hasUser === false) {
    next('/')
    return
  }
  if (cap === 'site' && boot.hasSite === false) {
    next('/')
    return
  }
  next()
})

export default router
