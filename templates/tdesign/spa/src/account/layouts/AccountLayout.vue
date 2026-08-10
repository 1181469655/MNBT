<template>
  <div class="td-layout" :class="{ collapsed: sidebarCollapsed, 'mobile-open': mobileOpen }">
    <!-- 侧栏 -->
    <aside class="td-sidebar">
      <!-- 品牌区 -->
      <div class="td-side-brand">
        <div v-show="!sidebarCollapsed || isMobile" class="brand-text">
          <strong>{{ siteName }}</strong>
          <span>用户中心</span>
        </div>
        <button v-show="sidebarCollapsed && !isMobile" class="brand-collapsed-btn" @click="toggleSidebar" title="展开侧栏">
          <i class="mdi mdi-menu"></i>
        </button>
      </div>

      <!-- 导航区 -->
      <div class="td-side-scroll">
        <!-- 概览 -->
        <div class="td-side-group" v-show="!sidebarCollapsed || isMobile">
          <div class="td-side-group-label">
            <i class="mdi mdi-view-dashboard-outline"></i>
            <span>概览</span>
          </div>
        </div>
        <ul class="td-side-menu">
          <li class="td-side-item">
            <router-link to="/dashboard" custom v-slot="{ navigate, isActive }">
              <a href="javascript:;" :class="{ active: isActive }" @click="navigate">
                <i class="mdi mdi-home"></i><span>控制面板</span>
              </a>
            </router-link>
          </li>
        </ul>

        <!-- 账户 -->
        <div class="td-side-group" v-show="!sidebarCollapsed || isMobile">
          <div class="td-side-group-label">
            <i class="mdi mdi-account-circle-outline"></i>
            <span>账户</span>
          </div>
        </div>
        <ul class="td-side-menu">
          <li class="td-side-item">
            <router-link to="/profile" custom v-slot="{ navigate, isActive }">
              <a href="javascript:;" :class="{ active: isActive }" @click="navigate">
                <i class="mdi mdi-account-details"></i><span>个人信息</span>
              </a>
            </router-link>
          </li>
          <li class="td-side-item">
            <router-link to="/password" custom v-slot="{ navigate, isActive }">
              <a href="javascript:;" :class="{ active: isActive }" @click="navigate">
                <i class="mdi mdi-lock-outline"></i><span>修改密码</span>
              </a>
            </router-link>
          </li>
        </ul>

        <!-- 余额 -->
        <template v-if="hasBalance">
          <div class="td-side-group" v-show="!sidebarCollapsed || isMobile">
            <div class="td-side-group-label">
              <i class="mdi mdi-wallet-outline"></i>
              <span>余额</span>
            </div>
          </div>
          <ul class="td-side-menu">
            <li class="td-side-item">
              <router-link to="/balance" custom v-slot="{ navigate, isActive }">
                <a href="javascript:;" :class="{ active: isActive }" @click="navigate">
                  <i class="mdi mdi-wallet"></i><span>我的余额</span>
                </a>
              </router-link>
            </li>
            <li class="td-side-item">
              <router-link to="/balance/recharge" custom v-slot="{ navigate, isActive }">
                <a href="javascript:;" :class="{ active: isActive }" @click="navigate">
                  <i class="mdi mdi-cash"></i><span>余额充值</span>
                </a>
              </router-link>
            </li>
          </ul>
        </template>

        <!-- 商城 -->
        <template v-if="hasShop">
          <div class="td-side-group" v-show="!sidebarCollapsed || isMobile">
            <div class="td-side-group-label">
              <i class="mdi mdi-cart-outline"></i>
              <span>商城</span>
            </div>
          </div>
          <ul class="td-side-menu">
            <li class="td-side-item">
              <router-link to="/shop" custom v-slot="{ navigate, isActive }">
                <a href="javascript:;" :class="{ active: isActive }" @click="navigate">
                  <i class="mdi mdi-cart"></i><span>主机商城</span>
                </a>
              </router-link>
            </li>
            <li class="td-side-item">
              <router-link to="/hosting" custom v-slot="{ navigate, isActive }">
                <a href="javascript:;" :class="{ active: isActive }" @click="navigate">
                  <i class="mdi mdi-server"></i><span>我的主机</span>
                </a>
              </router-link>
            </li>
            <li class="td-side-item">
              <router-link to="/orders" custom v-slot="{ navigate, isActive }">
                <a href="javascript:;" :class="{ active: isActive }" @click="navigate">
                  <i class="mdi mdi-receipt"></i><span>我的订单</span>
                </a>
              </router-link>
            </li>
          </ul>
        </template>

        <!-- Docker -->
        <template v-if="hasDocker">
          <div class="td-side-group" v-show="!sidebarCollapsed || isMobile">
            <div class="td-side-group-label">
              <i class="mdi mdi-docker"></i>
              <span>Docker</span>
            </div>
          </div>
          <ul class="td-side-menu">
            <li class="td-side-item">
              <router-link to="/docker-shop" custom v-slot="{ navigate, isActive }">
                <a href="javascript:;" :class="{ active: isActive }" @click="navigate">
                  <i class="mdi mdi-cart"></i><span>Docker 商城</span>
                </a>
              </router-link>
            </li>
            <li class="td-side-item">
              <router-link to="/docker-assets" custom v-slot="{ navigate, isActive }">
                <a href="javascript:;" :class="{ active: isActive }" @click="navigate">
                  <i class="mdi mdi-docker"></i><span>我的 Docker</span>
                </a>
              </router-link>
            </li>
            <li class="td-side-item">
              <router-link to="/docker-orders" custom v-slot="{ navigate, isActive }">
                <a href="javascript:;" :class="{ active: isActive }" @click="navigate">
                  <i class="mdi mdi-receipt"></i><span>Docker 订单</span>
                </a>
              </router-link>
            </li>
          </ul>
        </template>
      </div>

      <!-- 底部用户卡 -->
      <div v-show="!sidebarCollapsed || isMobile" class="td-side-user">
        <div class="td-side-user-avatar">
          <i class="mdi mdi-account-circle"></i>
        </div>
        <div class="td-side-user-info">
          <strong>{{ userName }}</strong>
          <span>已登录</span>
        </div>
        <button class="td-side-user-logout" title="退出登录" @click="onLogout">
          <i class="mdi mdi-logout-variant"></i>
        </button>
      </div>
      <a v-show="!sidebarCollapsed || isMobile" :href="panelUrl || 'javascript:;'" class="td-side-panel-link">
        <i class="mdi mdi-server"></i><span>进入主机管理面板</span>
      </a>
      <a v-if="hasDocker && dockerUrl" v-show="!sidebarCollapsed || isMobile" :href="dockerUrl" class="td-side-panel-link" target="_blank" rel="noopener">
        <i class="mdi mdi-docker"></i><span>进入 Docker 控制台</span>
      </a>
    </aside>

    <!-- 主区 -->
    <div class="td-main">
      <header class="td-header">
        <div class="td-header-left">
          <button class="td-icon-btn" @click="toggleSidebar" :title="sidebarCollapsed ? '展开侧栏' : '收起侧栏'">
            <i class="mdi" :class="sidebarCollapsed ? 'mdi-menu' : 'mdi-backburger'"></i>
          </button>
          <button class="td-icon-btn" @click="refreshCurrent" title="刷新当前页">
            <i class="mdi mdi-refresh"></i>
          </button>
          <div class="td-header-title">
            <span class="td-header-crumb">{{ pageTitle }}</span>
          </div>
        </div>
        <div class="td-header-right">
          <a class="td-header-home" :href="homeUrl || 'javascript:;'" title="返回官网首页">
            <i class="mdi mdi-home-outline"></i>
          </a>
          <t-tag theme="success" variant="light" shape="round" size="small" class="user-tag">
            <i class="mdi mdi-account-circle"></i>
            {{ userName }}
          </t-tag>
          <t-dropdown :options="userMenuOptions" @click="onUserMenuClick">
            <button class="td-icon-btn">
              <i class="mdi mdi-dots-vertical"></i>
            </button>
          </t-dropdown>
        </div>
      </header>

      <main class="td-content">
        <router-view v-slot="{ Component, route }">
          <keep-alive :include="cachedViews">
            <component :is="Component" :key="route.fullPath" />
          </keep-alive>
        </router-view>
      </main>
    </div>

    <!-- 移动端遮罩 -->
    <div v-if="mobileOpen" class="td-mask" @click="mobileOpen = false"></div>
  </div>
</template>

<script setup>
import { computed, ref, onMounted, onUnmounted, nextTick, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { DialogPlugin } from 'tdesign-vue-next'
import { logoutUrl } from '@/account/api/auth'
import { pluginEnabled } from '@/account/api/plugins'

const route = useRoute()
const router = useRouter()
const boot = window.__TD_BOOT__ || {}

const siteName = boot.siteName || 'MNBT'
const panelUrl = boot.panelUrl || ''
const dockerUrl = boot.dockerUrl || ''
const homeUrl = boot.homeUrl || ''
const userName = boot.accountUser?.username || boot.user || 'user'

const hasBalance = pluginEnabled('balance')
const hasShop = pluginEnabled('hosting_shop')
const hasDocker = pluginEnabled('docker_shop')

const sidebarCollapsed = ref(false)
const mobileOpen = ref(false)
const isMobile = ref(false)

const cachedViews = ref([])

const pageTitle = computed(() => route.meta.title || '用户中心')

const userMenuOptions = computed(() => {
  const list = [
    { content: '控制面板', value: 'dashboard' },
    { content: '个人信息', value: 'profile' },
    { content: '修改密码', value: 'password' },
  ]
  if (hasRealname) list.push({ content: '实名认证', value: 'realname' })
  if (hasBalance) list.push({ content: '余额中心', value: 'balance' })
  if (hasShop) list.push({ content: '主机商城', value: 'shop' })
  if (hasDocker) list.push({ content: 'Docker 商城', value: 'docker-shop' })
  list.push({ content: '退出登录', value: 'logout', theme: 'error' })
  return list
})

function checkMobile() {
  isMobile.value = window.innerWidth < 992
  if (!isMobile.value) mobileOpen.value = false
}

function toggleSidebar() {
  if (isMobile.value) {
    mobileOpen.value = !mobileOpen.value
  } else {
    sidebarCollapsed.value = !sidebarCollapsed.value
  }
}

function refreshCurrent() {
  const key = cachedViews.value.length
  cachedViews.value = [`__refresh_${key}_${Date.now()}`]
  nextTick(() => {
    cachedViews.value = []
  })
}

function onLogout() {
  const dialog = DialogPlugin.confirm({
    header: '退出登录',
    body: '确定要退出当前账号吗?',
    confirmBtn: { content: '退出', theme: 'danger' },
    onConfirm: async () => {
      dialog.destroy()
      window.location.href = logoutUrl()
    },
    onClose: () => dialog.destroy(),
  })
}

function onUserMenuClick(item) {
  const v = item?.value
  if (v === 'logout') {
    onLogout()
    return
  }
  const targets = {
    dashboard: '/dashboard',
    profile: '/profile',
    password: '/password',
    realname: '/realname',
    balance: '/balance',
    shop: '/shop',
    'docker-shop': '/docker-shop',
  }
  if (targets[v]) router.push(targets[v])
}

watch(
  () => route.path,
  () => {
    if (isMobile.value) mobileOpen.value = false
  },
  { immediate: true },
)

onMounted(() => {
  checkMobile()
  window.addEventListener('resize', checkMobile)
})
onUnmounted(() => {
  window.removeEventListener('resize', checkMobile)
})
</script>

<style scoped>
.td-layout {
  display: flex;
  min-height: 100vh;
  background: var(--td-bg);
}

/* ============== 侧栏 ============== */
.td-sidebar {
  width: var(--td-sidebar-width);
  flex-shrink: 0;
  background: var(--td-sidebar-bg);
  color: var(--td-sidebar-text);
  display: flex;
  flex-direction: column;
  border-right: 1px solid var(--td-sidebar-border);
  transition: width var(--td-dur-lg) var(--td-ease-out);
  z-index: 30;
  position: relative;
}
.td-layout.collapsed .td-sidebar {
  width: var(--td-sidebar-collapsed);
}

/* 品牌区 */
.td-side-brand {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 0 18px;
  height: var(--td-header-height);
  box-sizing: border-box;
  border-bottom: 1px solid var(--td-sidebar-border);
}
.brand-text {
  display: flex;
  flex-direction: column;
  min-width: 0;
  overflow: hidden;
}
.brand-text strong {
  font-size: 15px;
  color: var(--td-sidebar-text);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  font-weight: 700;
  letter-spacing: 0.3px;
}
.brand-text span {
  font-size: 11px;
  color: var(--td-sidebar-text-3);
  margin-top: 1px;
}
.brand-collapsed-btn {
  width: 34px;
  height: 34px;
  border: none;
  background: transparent;
  border-radius: 8px;
  cursor: pointer;
  color: var(--td-sidebar-text-2);
  display: grid;
  place-items: center;
  font-size: 20px;
  margin: 0 auto;
  transition: background var(--td-dur) var(--td-ease),
              color var(--td-dur) var(--td-ease);
}
.brand-collapsed-btn:hover {
  background: var(--td-sidebar-hover);
  color: var(--td-brand);
}

/* 滚动区 */
.td-side-scroll {
  flex: 1;
  overflow-y: auto;
  overflow-x: hidden;
  padding: 10px 10px 16px;
}

/* 分组标签 */
.td-side-group {
  padding: 14px 12px 6px;
}
.td-side-group-label {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 12px;
  font-weight: 500;
  color: var(--td-sidebar-group-label);
  user-select: none;
}
.td-side-group-label i {
  font-size: 13px;
  color: var(--td-sidebar-text-3);
}

/* 菜单 */
.td-side-menu {
  list-style: none;
  margin: 0;
  padding: 0;
}
.td-side-menu li {
  margin: 0;
}
.td-side-menu a {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 9px 12px;
  color: var(--td-sidebar-text-2);
  font-size: 13px;
  border-radius: 8px;
  text-decoration: none;
  cursor: pointer;
  position: relative;
  transition: background var(--td-dur) var(--td-ease),
              color var(--td-dur) var(--td-ease);
  margin-bottom: 2px;
}
.td-side-menu a > i:first-child {
  font-size: 17px;
  width: 20px;
  text-align: center;
  flex-shrink: 0;
  color: var(--td-sidebar-text-3);
  transition: color var(--td-dur) var(--td-ease);
}
.td-side-menu a > span {
  flex: 1;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.td-side-menu a:hover {
  background: var(--td-sidebar-hover);
  color: var(--td-sidebar-text);
  text-decoration: none;
}
.td-side-menu a:hover > i:first-child {
  color: var(--td-sidebar-text-2);
}
.td-side-menu a.active {
  background: var(--td-sidebar-active-bg);
  color: var(--td-sidebar-active-text);
  font-weight: 500;
}
.td-side-menu a.active > i:first-child {
  color: var(--td-sidebar-active-text);
}
.td-side-menu a.active::before {
  content: '';
  position: absolute;
  left: -10px;
  top: 50%;
  transform: translateY(-50%);
  width: 3px;
  height: 18px;
  background: var(--td-brand);
  border-radius: 0 3px 3px 0;
}

/* 底部用户卡 */
.td-side-user {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 12px 14px;
  border-top: 1px solid var(--td-sidebar-border);
  background: var(--td-sidebar-surface);
}
.td-side-user-avatar {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: linear-gradient(135deg, var(--td-brand) 0%, var(--td-brand-dark) 100%);
  display: grid;
  place-items: center;
  color: #fff;
  font-size: 18px;
  flex-shrink: 0;
}
.td-side-user-info {
  flex: 1;
  min-width: 0;
  display: flex;
  flex-direction: column;
}
.td-side-user-info strong {
  font-size: 13px;
  color: var(--td-sidebar-text);
  font-weight: 600;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.td-side-user-info span {
  font-size: 11px;
  color: var(--td-sidebar-text-3);
  margin-top: 1px;
}
.td-side-user-logout {
  width: 30px;
  height: 30px;
  border: none;
  background: transparent;
  border-radius: 6px;
  cursor: pointer;
  color: var(--td-sidebar-text-3);
  font-size: 16px;
  display: grid;
  place-items: center;
  transition: background var(--td-dur) var(--td-ease),
              color var(--td-dur) var(--td-ease);
}
.td-side-user-logout:hover {
  background: var(--td-sidebar-hover);
  color: var(--td-error);
}

/* 主机面板入口 */
.td-side-panel-link {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px 12px;
  margin: 0 10px 12px;
  border-radius: 8px;
  background: var(--td-sidebar-hover);
  color: var(--td-sidebar-text-2);
  font-size: 13px;
  text-decoration: none;
  transition: background var(--td-dur) var(--td-ease),
              color var(--td-dur) var(--td-ease);
}
.td-side-panel-link:hover {
  background: var(--td-sidebar-active-bg);
  color: var(--td-sidebar-active-text);
  text-decoration: none;
}
.td-side-panel-link i {
  font-size: 17px;
}

/* 折叠态 */
.td-layout.collapsed .td-side-menu a > span,
.td-layout.collapsed .brand-text,
.td-layout.collapsed .td-side-group,
.td-layout.collapsed .td-side-user,
.td-layout.collapsed .td-side-panel-link span {
  display: none;
}
.td-layout.collapsed .td-side-menu a {
  justify-content: center;
  padding: 10px 0;
}
.td-layout.collapsed .td-side-menu a.active::before {
  left: 0;
}
.td-layout.collapsed .td-side-panel-link {
  justify-content: center;
  padding: 10px 0;
}

/* ============== 主区 ============== */
.td-main {
  flex: 1;
  min-width: 0;
  display: flex;
  flex-direction: column;
}
.td-header {
  height: var(--td-header-height);
  background: var(--td-surface);
  border-bottom: 1px solid var(--td-border);
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 20px;
  position: sticky;
  top: 0;
  z-index: 20;
  gap: 12px;
}
.td-header-left,
.td-header-right {
  display: flex;
  align-items: center;
  gap: 8px;
}
.td-icon-btn {
  width: 34px;
  height: 34px;
  border: none;
  background: transparent;
  border-radius: 8px;
  cursor: pointer;
  color: var(--td-text-secondary);
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 18px;
  transition: background var(--td-dur) var(--td-ease),
              color var(--td-dur) var(--td-ease);
}
.td-icon-btn:hover {
  background: var(--td-bg);
  color: var(--td-brand);
}
.td-header-home {
  width: 34px;
  height: 34px;
  border-radius: 8px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 18px;
  color: var(--td-text-secondary);
  text-decoration: none;
  transition: background var(--td-dur) var(--td-ease),
              color var(--td-dur) var(--td-ease);
}
.td-header-home:hover {
  background: var(--td-bg);
  color: var(--td-brand);
  text-decoration: none;
}
.td-header-title {
  display: flex;
  align-items: center;
  gap: 6px;
  margin-left: 6px;
}
.td-header-crumb {
  font-size: 15px;
  font-weight: 600;
  color: var(--td-text);
  letter-spacing: 0.2px;
}
.user-tag {
  max-width: 160px;
  overflow: hidden;
  text-overflow: ellipsis;
}
.td-content {
  flex: 1;
  min-height: 0;
}
.td-mask {
  position: fixed;
  inset: 0;
  background: rgba(15, 23, 42, 0.4);
  z-index: 25;
  animation: td-fade-in var(--td-dur) var(--td-ease);
}

@media (max-width: 991px) {
  .td-sidebar {
    position: fixed;
    left: 0;
    top: 0;
    bottom: 0;
    transform: translateX(-100%);
    width: min(260px, 86vw) !important;
    transition: transform var(--td-dur-lg) var(--td-ease-out);
    box-shadow: var(--td-shadow-lg);
  }
  .td-layout.mobile-open .td-sidebar {
    transform: translateX(0);
  }
  .td-layout.collapsed .td-sidebar {
    width: min(260px, 86vw) !important;
  }
  .td-layout.collapsed .td-side-menu a > span,
  .td-layout.collapsed .brand-text,
  .td-layout.collapsed .td-side-group,
  .td-layout.collapsed .td-side-user,
  .td-layout.collapsed .td-side-panel-link span {
    display: revert;
  }
  .td-layout.collapsed .td-side-menu a,
  .td-layout.collapsed .td-side-panel-link {
    justify-content: revert;
    padding: 9px 12px;
  }
}
</style>
