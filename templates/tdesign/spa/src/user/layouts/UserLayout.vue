<template>
  <div class="td-layout" :class="{ collapsed: sidebarCollapsed, 'mobile-open': mobileOpen }">
    <!-- 侧栏 -->
    <aside class="td-sidebar">
      <!-- 品牌区 -->
      <div class="td-side-brand">
        <div v-show="!sidebarCollapsed || isMobile" class="brand-text">
          <strong>{{ siteName }}</strong>
          <span>控制面板</span>
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
          <li class="td-side-item">
            <router-link to="/stats" custom v-slot="{ navigate, isActive }">
              <a href="javascript:;" :class="{ active: isActive }" @click="navigate">
                <i class="mdi mdi-chart-bar"></i><span>站点统计</span>
              </a>
            </router-link>
          </li>
        </ul>

        <!-- 基本配置 -->
        <div class="td-side-group" v-show="!sidebarCollapsed || isMobile">
          <div class="td-side-group-label">
            <i class="mdi mdi-console"></i>
            <span>基本配置</span>
          </div>
        </div>
        <ul class="td-side-menu">
          <li class="td-side-submenu" :class="{ open: openGroups.basic }">
            <a href="javascript:;" @click="toggleGroup('basic')">
              <i class="mdi mdi-tune-vertical"></i><span>站点设置</span>
              <i class="td-arrow mdi" :class="openGroups.basic ? 'mdi-chevron-down' : 'mdi-chevron-right'"></i>
            </a>
            <ul class="td-side-subnav">
              <li><router-link to="/settings/php" custom v-slot="{ navigate, isActive }"><a href="javascript:;" :class="{ active: isActive }" @click="navigate">PHP版本切换</a></router-link></li>
              <li><router-link to="/settings/pass" custom v-slot="{ navigate, isActive }"><a href="javascript:;" :class="{ active: isActive }" @click="navigate">密码访问</a></router-link></li>
              <li><router-link to="/settings/default-doc" custom v-slot="{ navigate, isActive }"><a href="javascript:;" :class="{ active: isActive }" @click="navigate">默认文档</a></router-link></li>
              <li><router-link to="/settings/run-dir" custom v-slot="{ navigate, isActive }"><a href="javascript:;" :class="{ active: isActive }" @click="navigate">运行目录</a></router-link></li>
              <li><router-link to="/settings/rewrite" custom v-slot="{ navigate, isActive }"><a href="javascript:;" :class="{ active: isActive }" @click="navigate">伪静态</a></router-link></li>
              <li><router-link to="/settings/ssl" custom v-slot="{ navigate, isActive }"><a href="javascript:;" :class="{ active: isActive }" @click="navigate">SSL配置</a></router-link></li>
              <li><router-link to="/settings/hotlink" custom v-slot="{ navigate, isActive }"><a href="javascript:;" :class="{ active: isActive }" @click="navigate">防盗链</a></router-link></li>
              <li><router-link to="/settings/gzip" custom v-slot="{ navigate, isActive }"><a href="javascript:;" :class="{ active: isActive }" @click="navigate">Gzip配置</a></router-link></li>
              <li><router-link to="/settings/cache" custom v-slot="{ navigate, isActive }"><a href="javascript:;" :class="{ active: isActive }" @click="navigate">缓存配置</a></router-link></li>
              <li><router-link to="/settings/password" custom v-slot="{ navigate, isActive }"><a href="javascript:;" :class="{ active: isActive }" @click="navigate">修改密码</a></router-link></li>
            </ul>
          </li>
        </ul>

        <!-- 数据管理 -->
        <div class="td-side-group" v-show="!sidebarCollapsed || isMobile">
          <div class="td-side-group-label">
            <i class="mdi mdi-database"></i>
            <span>数据管理</span>
          </div>
        </div>
        <ul class="td-side-menu">
          <li class="td-side-item">
            <router-link to="/ftp" custom v-slot="{ navigate, isActive }">
              <a href="javascript:;" :class="{ active: isActive }" @click="navigate">
                <i class="mdi mdi-folder"></i><span>在线文件管理</span>
              </a>
            </router-link>
          </li>
          <li class="td-side-item">
            <a href="mysql.php" target="_blank">
              <i class="mdi mdi-database-search"></i><span>SQL管理面板</span>
                </a>
          </li>
          <li class="td-side-item">
            <router-link to="/sql-backup" custom v-slot="{ navigate, isActive }">
              <a href="javascript:;" :class="{ active: isActive }" @click="navigate">
                <i class="mdi mdi-database-edit"></i><span>SQL数据备份</span>
              </a>
            </router-link>
          </li>
          <li class="td-side-item">
            <router-link to="/settings/sql-auth" custom v-slot="{ navigate, isActive }">
              <a href="javascript:;" :class="{ active: isActive }" @click="navigate">
                <i class="mdi mdi-shield-key"></i><span>SQL权限设置</span>
              </a>
            </router-link>
          </li>
        </ul>

        <!-- 网站管理 -->
        <div class="td-side-group" v-show="!sidebarCollapsed || isMobile">
          <div class="td-side-group-label">
            <i class="mdi mdi-sitemap"></i>
            <span>网站管理</span>
          </div>
        </div>
        <ul class="td-side-menu">
          <li class="td-side-item">
            <router-link to="/deploy" custom v-slot="{ navigate, isActive }">
              <a href="javascript:;" :class="{ active: isActive }" @click="navigate">
                <i class="mdi mdi-webpack"></i><span>一键部署</span>
              </a>
            </router-link>
          </li>
          <li class="td-side-item">
            <router-link to="/monitor" custom v-slot="{ navigate, isActive }">
              <a href="javascript:;" :class="{ active: isActive }" @click="navigate">
                <i class="mdi mdi-radar"></i><span>监控任务</span>
              </a>
            </router-link>
          </li>
          <li class="td-side-item">
            <router-link to="/notice" custom v-slot="{ navigate, isActive }">
              <a href="javascript:;" :class="{ active: isActive }" @click="navigate">
                <i class="mdi mdi-bell-outline"></i><span>通知日志</span>
              </a>
            </router-link>
          </li>
        </ul>

        <!-- 扩展与工具 -->
        <div class="td-side-group" v-show="!sidebarCollapsed || isMobile">
          <div class="td-side-group-label">
            <i class="mdi mdi-puzzle-outline"></i>
            <span>扩展与工具</span>
          </div>
        </div>
        <ul class="td-side-menu td-plugin-menu" ref="pluginMenuRef" @click="onPluginMenuClick" v-html="pluginMenuHtml"></ul>
      </div>

      <!-- 底部用户卡 -->
      <div v-show="!sidebarCollapsed || isMobile" class="td-side-user">
        <div class="td-side-user-avatar">
          <i class="mdi mdi-account-circle"></i>
        </div>
        <div class="td-side-user-info">
          <strong>{{ userName }}</strong>
          <span>v{{ boot.version || '0.3.0' }}</span>
        </div>
        <button class="td-side-user-logout" title="退出登录" @click="onLogout">
          <i class="mdi mdi-logout-variant"></i>
        </button>
      </div>
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
import { computed, reactive, ref, onMounted, onUnmounted, watch, nextTick } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { DialogPlugin } from 'tdesign-vue-next'
import { userLogout } from '@/user/api/auth'

const route = useRoute()
const router = useRouter()
const boot = window.__TD_BOOT__ || {}

const siteName = boot.siteName || 'MNBT'
const footer = boot.footer || ''
const userName = boot.user || 'user'
const pluginMenuHtml = boot.pluginMenuHtml || ''

const sidebarCollapsed = ref(false)
const mobileOpen = ref(false)
const isMobile = ref(false)
const pluginMenuRef = ref(null)

const openGroups = reactive({
  basic: false,
})

const cachedViews = ref([])

const pageTitle = computed(() => {
  // 插件页面支持通过 query.title 传入动态标题
  if (route.path === '/plugin' && route.query.title) {
    return String(route.query.title)
  }
  return route.meta.title || '控制面板'
})

const userMenuOptions = [
  { content: '控制面板', value: 'dashboard' },
  { content: '修改密码', value: 'password' },
  { content: '退出登录', value: 'logout', theme: 'error' },
]

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

function toggleGroup(name) {
  openGroups[name] = !openGroups[name]
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
      await userLogout()
      dialog.destroy()
      window.location.href = './login.php'
    },
    onClose: () => dialog.destroy(),
  })
}

async function onUserMenuClick(item) {
  const v = item?.value
  if (v === 'logout') {
    onLogout()
    return
  }
  if (v === 'dashboard') router.push('/dashboard')
  else if (v === 'password') router.push('/settings/password')
}

function onPluginMenuClick(e) {
  const link = e.target.closest('a[href],a[data-td-route]')
  if (!link) return

  // 优先识别 data-td-route 属性,通过 SPA 路由在 layout 内加载
  const routePath = link.getAttribute('data-td-route')
  if (routePath) {
    e.preventDefault()
    e.stopPropagation()
    // 提取菜单项文本作为标题
    const textNode = link.querySelector('span')
    const title = textNode ? textNode.textContent.trim() : ''
    const fullPath = title
      ? routePath + (routePath.includes('?') ? '&' : '?') + 'title=' + encodeURIComponent(title)
      : routePath
    router.push(fullPath)
    return
  }

  const href = link.getAttribute('href') || ''
  // 仍兼容无 data-td-route 的 plugin.php 链接,在新窗口打开作为兜底
  if (href.includes('plugin.php')) {
    e.preventDefault()
    e.stopPropagation()
    window.open(href)
  }
}

watch(
  () => [route.path, route.query],
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

/* 箭头 */
.td-arrow {
  font-size: 14px;
  opacity: 0.6;
  transition: transform var(--td-dur) var(--td-ease);
}
.td-side-submenu.open .td-arrow {
  transform: rotate(0deg);
  opacity: 0.9;
}

/* 子菜单 */
.td-side-submenu .td-side-subnav {
  list-style: none;
  margin: 0;
  padding: 2px 0 4px 0;
  max-height: 0;
  overflow: hidden;
  opacity: 0;
  transition: max-height var(--td-dur-lg) var(--td-ease-out),
              opacity var(--td-dur) var(--td-ease);
}
.td-side-submenu.open .td-side-subnav {
  max-height: 600px;
  opacity: 1;
}
.td-side-subnav a {
  padding-left: 42px;
  font-size: 12.5px;
  padding-top: 7px;
  padding-bottom: 7px;
  color: var(--td-sidebar-text-2);
}
.td-side-subnav a::before {
  display: none;
}
.td-side-subnav a.active {
  background: var(--td-sidebar-active-bg);
  color: var(--td-sidebar-active-text);
}

/* ============== 插件菜单区 ============== */
.td-plugin-menu {
  list-style: none;
  margin: 0;
  padding: 0;
}
.td-plugin-menu :deep(li) {
  list-style: none;
  margin: 0;
}
.td-plugin-menu :deep(li > a) {
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
  margin-bottom: 2px;
  transition: background var(--td-dur) var(--td-ease),
              color var(--td-dur) var(--td-ease);
}
.td-plugin-menu :deep(li > a:hover) {
  background: var(--td-sidebar-hover);
  color: var(--td-sidebar-text);
  text-decoration: none;
}
.td-plugin-menu :deep(li > a > i:first-child) {
  font-size: 17px;
  width: 20px;
  text-align: center;
  flex-shrink: 0;
  color: var(--td-sidebar-text-3);
  transition: color var(--td-dur) var(--td-ease);
}
.td-plugin-menu :deep(li > a:hover > i:first-child) {
  color: var(--td-sidebar-text-2);
}
.td-plugin-menu :deep(li > a > span) {
  flex: 1;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.td-plugin-menu :deep(li > a.active) {
  background: var(--td-sidebar-active-bg);
  color: var(--td-sidebar-active-text);
  font-weight: 500;
}
.td-plugin-menu :deep(li > a.active > i:first-child) {
  color: var(--td-sidebar-active-text);
}
.td-plugin-menu :deep(li > a.active::before) {
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
.td-plugin-menu :deep(.td-side-submenu > .td-side-subnav) {
  list-style: none;
  margin: 0;
  padding: 2px 0 4px 0;
  max-height: 0;
  overflow: hidden;
  opacity: 0;
  transition: max-height var(--td-dur-lg) var(--td-ease-out),
              opacity var(--td-dur) var(--td-ease);
}
.td-plugin-menu :deep(.td-side-submenu.open > .td-side-subnav) {
  max-height: 600px;
  opacity: 1;
}
.td-plugin-menu :deep(.td-side-submenu > .td-side-subnav > li > a) {
  padding-left: 42px;
  font-size: 12.5px;
  padding-top: 7px;
  padding-bottom: 7px;
}
.td-plugin-menu :deep(.td-side-submenu > .td-side-subnav .td-side-submenu > .td-side-subnav > li > a) {
  padding-left: 62px;
}
.td-plugin-menu :deep(.td-side-submenu > a .td-arrow) {
  font-size: 14px;
  opacity: 0.6;
  transform: rotate(-90deg);
  transition: transform var(--td-dur) var(--td-ease),
              opacity var(--td-dur) var(--td-ease);
}
.td-plugin-menu :deep(.td-side-submenu.open > a .td-arrow) {
  transform: rotate(0deg);
  opacity: 0.9;
}

/* 用户卡 */
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

/* 折叠态 */
.td-layout.collapsed .td-side-menu a > span,
.td-layout.collapsed .td-side-menu a > .td-arrow,
.td-layout.collapsed .td-plugin-menu :deep(li > a > span),
.td-layout.collapsed .td-plugin-menu :deep(li > a > .td-arrow),
.td-layout.collapsed .brand-text,
.td-layout.collapsed .td-side-subnav,
.td-layout.collapsed .td-plugin-menu :deep(.td-side-submenu > .td-side-subnav),
.td-layout.collapsed .td-side-group,
.td-layout.collapsed .td-side-user {
  display: none;
}
.td-layout.collapsed .td-side-menu a,
.td-layout.collapsed .td-plugin-menu :deep(li > a) {
  justify-content: center;
  padding: 10px 0;
}
.td-layout.collapsed .td-side-menu a.active::before,
.td-layout.collapsed .td-plugin-menu :deep(li > a.active::before) {
  left: 0;
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
  .td-layout.collapsed .td-plugin-menu :deep(li > a > span),
  .td-layout.collapsed .brand-text,
  .td-layout.collapsed .td-side-group,
  .td-layout.collapsed .td-side-user {
    display: revert;
  }
  .td-layout.collapsed .td-side-menu a,
  .td-layout.collapsed .td-plugin-menu :deep(li > a) {
    justify-content: revert;
    padding: 9px 12px;
  }
}
</style>
