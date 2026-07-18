import { createRouter, createWebHistory } from 'vue-router';
import { useUserStore } from '../stores/user';

const routes = [
  { path: '/', component: () => import('../views/HomePage.vue'), meta: { public: true } },
  { path: '/shop', component: () => import('../views/ShopPage.vue'), meta: { public: true } },
  { path: '/shop/:id', component: () => import('../views/PlanDetail.vue'), meta: { public: true } },
  { path: '/login', component: () => import('../views/LoginPage.vue'), meta: { guest: true } },
  { path: '/register', component: () => import('../views/RegisterPage.vue'), meta: { guest: true } },
  { path: '/dashboard', component: () => import('../views/DashboardPage.vue') },
  { path: '/orders', component: () => import('../views/OrdersPage.vue') },
  { path: '/balance', component: () => import('../views/BalancePage.vue') },
  { path: '/profile', component: () => import('../views/ProfilePage.vue') },
];

const router = createRouter({ history: createWebHistory(), routes });

router.beforeEach(async (to, from, next) => {
  const user = useUserStore();
  if (!user.loaded) await user.fetch();
  if (to.meta.guest && user.isLogin) return next('/dashboard');
  if (!to.meta.public && !user.isLogin) return next('/login');
  next();
});

export default router;
