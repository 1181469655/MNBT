import { createRouter, createWebHashHistory } from 'vue-router'
import authState, { initAuth } from '@/home/store/auth'

const HomeLayout = () => import('@/home/layouts/HomeLayout.vue')
const LandingView = () => import('@/home/views/LandingView.vue')
const LoginView = () => import('@/home/views/auth/LoginView.vue')
const RegisterView = () => import('@/home/views/auth/RegisterView.vue')
const ShopView = () => import('@/home/views/shop/ShopView.vue')
// Docker 售卖（docker_shop 插件）
const DockerShopView = () => import('@/home/views/docker/DockerShopView.vue')
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
      { path: 'shop', name: 'home-shop', component: ShopView, meta: { title: '主机套餐', cap: 'shop' } },
      // Docker 售卖（docker_shop 插件）
      { path: 'docker-shop', name: 'home-docker-shop', component: DockerShopView, meta: { title: 'Docker 套餐', cap: 'docker' } },
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
  if (cap === 'docker' && boot.hasDocker === false) {
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
