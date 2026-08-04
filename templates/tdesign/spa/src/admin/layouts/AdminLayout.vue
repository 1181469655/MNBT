<template>
  <div class="td-layout" :class="{ collapsed: sidebarCollapsed, 'mobile-open': mobileOpen }">
    <!-- 侧栏 -->
    <aside class="td-sidebar">
      <!-- 品牌区(无 logo,简洁文字) -->
      <div class="td-side-brand">
        <div v-show="!sidebarCollapsed || isMobile" class="brand-text">
          <strong>{{ siteName }}</strong>
          <span>管理后台</span>
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
                <i class="mdi mdi-view-dashboard-outline"></i><span>后台首页</span>
              </a>
            </router-link>
          </li>
        </ul>

        <!-- 业务管理 -->
        <div class="td-side-group" v-show="!sidebarCollapsed || isMobile">
          <div class="td-side-group-label">
            <i class="mdi mdi-briefcase-outline"></i>
            <span>业务管理</span>
          </div>
        </div>
        <ul class="td-side-menu">
          <li class="td-side-submenu" :class="{ open: openGroups.host }">
            <a href="javascript:;" @click="toggleGroup('host')">
              <i class="mdi mdi-locker-multiple"></i><span>主机管理</span>
              <i class="td-arrow mdi" :class="openGroups.host ? 'mdi-chevron-down' : 'mdi-chevron-right'"></i>
            </a>
            <ul class="td-side-subnav">
              <li><router-link to="/host" custom v-slot="{ navigate, isActive }"><a href="javascript:;" :class="{ active: isActive }" @click="navigate">主机列表</a></router-link></li>
              <li><router-link to="/host/add" custom v-slot="{ navigate, isActive }"><a href="javascript:;" :class="{ active: isActive }" @click="navigate">添加主机</a></router-link></li>
            </ul>
          </li>

          <li class="td-side-submenu" :class="{ open: openGroups.node }">
            <a href="javascript:;" @click="toggleGroup('node')">
              <i class="mdi mdi-server-network"></i><span>节点与宝塔</span>
              <i class="td-arrow mdi" :class="openGroups.node ? 'mdi-chevron-down' : 'mdi-chevron-right'"></i>
            </a>
            <ul class="td-side-subnav">
              <li><router-link to="/baota" custom v-slot="{ navigate, isActive }"><a href="javascript:;" :class="{ active: isActive }" @click="navigate">宝塔列表</a></router-link></li>
              <li><router-link to="/baota/add" custom v-slot="{ navigate, isActive }"><a href="javascript:;" :class="{ active: isActive }" @click="navigate">添加宝塔</a></router-link></li>
              <li><router-link to="/node" custom v-slot="{ navigate, isActive }"><a href="javascript:;" :class="{ active: isActive }" @click="navigate">节点列表</a></router-link></li>
              <li><router-link to="/node/scan" custom v-slot="{ navigate, isActive }"><a href="javascript:;" :class="{ active: isActive }" @click="navigate">违禁词扫描</a></router-link></li>
            </ul>
          </li>

          <li class="td-side-submenu" :class="{ open: openGroups.deploy }">
            <a href="javascript:;" @click="toggleGroup('deploy')">
              <i class="mdi mdi-webpack"></i><span>一键部署</span>
              <i class="td-arrow mdi" :class="openGroups.deploy ? 'mdi-chevron-down' : 'mdi-chevron-right'"></i>
            </a>
            <ul class="td-side-subnav">
              <li><router-link to="/order" custom v-slot="{ navigate, isActive }"><a href="javascript:;" :class="{ active: isActive }" @click="navigate">订单列表</a></router-link></li>
              <li><router-link to="/program" custom v-slot="{ navigate, isActive }"><a href="javascript:;" :class="{ active: isActive }" @click="navigate">程序列表</a></router-link></li>
              <li><router-link to="/program/add" custom v-slot="{ navigate, isActive }"><a href="javascript:;" :class="{ active: isActive }" @click="navigate">添加程序</a></router-link></li>
              <li><router-link to="/program/import" custom v-slot="{ navigate, isActive }"><a href="javascript:;" :class="{ active: isActive }" @click="navigate">导入程序</a></router-link></li>
            </ul>
          </li>
        </ul>

        <!-- 系统设置 -->
        <div class="td-side-group" v-show="!sidebarCollapsed || isMobile">
          <div class="td-side-group-label">
            <i class="mdi mdi-cog-outline"></i>
            <span>系统设置</span>
          </div>
        </div>
        <ul class="td-side-menu">
          <li class="td-side-submenu" :class="{ open: openGroups.system }">
            <a href="javascript:;" @click="toggleGroup('system')">
              <i class="mdi mdi-tune-vertical"></i><span>系统管理</span>
              <i class="td-arrow mdi" :class="openGroups.system ? 'mdi-chevron-down' : 'mdi-chevron-right'"></i>
            </a>
            <ul class="td-side-subnav">
              <li><router-link to="/settings/website" custom v-slot="{ navigate, isActive }"><a href="javascript:;" :class="{ active: isActive }" @click="navigate">网站设置</a></router-link></li>
              <li><router-link to="/settings/admin" custom v-slot="{ navigate, isActive }"><a href="javascript:;" :class="{ active: isActive }" @click="navigate">管理设置</a></router-link></li>
              <li><router-link to="/settings/api" custom v-slot="{ navigate, isActive }"><a href="javascript:;" :class="{ active: isActive }" @click="navigate">API 设置</a></router-link></li>
              <li><router-link to="/settings/mail" custom v-slot="{ navigate, isActive }"><a href="javascript:;" :class="{ active: isActive }" @click="navigate">邮箱设置</a></router-link></li>
              <li><router-link to="/settings/panel" custom v-slot="{ navigate, isActive }"><a href="javascript:;" :class="{ active: isActive }" @click="navigate">控制面板</a></router-link></li>
              <li><router-link to="/update" custom v-slot="{ navigate, isActive }"><a href="javascript:;" :class="{ active: isActive }" @click="navigate">系统更新</a></router-link></li>
              <li><router-link to="/log" custom v-slot="{ navigate, isActive }"><a href="javascript:;" :class="{ active: isActive }" @click="navigate">操作日志</a></router-link></li>
            </ul>
          </li>

          <li class="td-side-item">
            <router-link to="/pay" custom v-slot="{ navigate, isActive }">
              <a href="javascript:;" :class="{ active: isActive }" @click="navigate">
                <i class="mdi mdi-credit-card-outline"></i><span>支付设置</span>
              </a>
            </router-link>
          </li>

          <li class="td-side-item">
            <router-link to="/settings/theme" custom v-slot="{ navigate, isActive }">
              <a href="javascript:;" :class="{ active: isActive }" @click="navigate">
                <i class="mdi mdi-palette-outline"></i><span>前端模板</span>
              </a>
            </router-link>
          </li>
        </ul>

        <!-- 扩展与工具 -->
        <div class="td-side-group" v-show="!sidebarCollapsed || isMobile">
          <div class="td-side-group-label">
            <i class="mdi mdi-tools"></i>
            <span>扩展与工具</span>
          </div>
        </div>
        <!-- 插件菜单区完全由 theme.php 渲染器生成(结构:插件列表 + 独立 groups + 插件管理分组) -->
        <ul class="td-side-menu td-plugin-menu" ref="pluginMenuRef" @click="onPluginMenuClick" v-html="pluginMenuHtml"></ul>

        <ul class="td-side-menu">
          <li class="td-side-submenu" :class="{ open: openGroups.tutorial }">
            <a href="javascript:;" @click="toggleGroup('tutorial')">
              <i class="mdi mdi-monitor-dashboard"></i><span>监控与教程</span>
              <i class="td-arrow mdi" :class="openGroups.tutorial ? 'mdi-chevron-down' : 'mdi-chevron-right'"></i>
            </a>
            <ul class="td-side-subnav">
              <li><router-link to="/settings/monitor" custom v-slot="{ navigate, isActive }"><a href="javascript:;" :class="{ active: isActive }" @click="navigate">监控主机删除设置</a></router-link></li>
              <li><router-link to="/tutorial" custom v-slot="{ navigate, isActive }"><a href="javascript:;" :class="{ active: isActive }" @click="navigate">教程及监控</a></router-link></li>
            </ul>
          </li>

          <li class="td-side-item">
            <router-link to="/repair" custom v-slot="{ navigate, isActive }">
              <a href="javascript:;" :class="{ active: isActive }" @click="navigate">
                <i class="mdi mdi-backup-restore"></i><span>系统修复</span>
              </a>
            </router-link>
          </li>
        </ul>
      </div>

      <!-- 底部用户卡 -->
      <div v-show="!sidebarCollapsed || isMobile" class="td-side-user">
        <div class="td-side-user-avatar">
          <i class="mdi mdi-account-circle"></i>
        </div>
        <div class="td-side-user-info">
          <strong>{{ adminUser }}</strong>
          <span>v{{ boot.version || '0.1.0' }}</span>
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
            {{ adminUser }}
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
import { logout } from '@/admin/api/auth'

const route = useRoute()
const router = useRouter()
const boot = window.__TD_BOOT__ || {}

const siteName = boot.siteName || 'MNBT'
const footer = boot.footer || ''
const adminUser = boot.adminUser || 'admin'
const pluginMenuHtml = boot.pluginMenuHtml || ''

const sidebarCollapsed = ref(false)
const mobileOpen = ref(false)
const isMobile = ref(false)
const pluginMenuRef = ref(null)

const openGroups = reactive({
  host: false,
  node: false,
  deploy: false,
  system: false,
  tutorial: false,
})

const cachedViews = ref([])

const pageTitle = computed(() => route.meta.title || '管理后台')

const userMenuOptions = [
  { content: '网站设置', value: 'website' },
  { content: '修改密码', value: 'admin' },
  { content: '系统修复', value: 'repair' },
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
      await logout()
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
  if (v === 'website') router.push('/settings/website')
  else if (v === 'admin') router.push('/settings/admin')
  else if (v === 'repair') router.push('/repair')
}

/**
 * 根据当前路由更新插件菜单区 active 高亮
 */
function updatePluginMenuActive() {
  if (!pluginMenuRef.value) return
  pluginMenuRef.value.querySelectorAll('a.active').forEach((a) => a.classList.remove('active'))

  const path = route.path
  const query = route.query

  // 插件列表页
  if (path === '/plugin') {
    const listLink = pluginMenuRef.value.querySelector('a[data-td-route="/plugin"]')
    if (listLink) listLink.classList.add('active')
    return
  }

  // 插件 iframe 页面:匹配 p / page 参数
  if (path === '/plugin/page') {
    const p = query.p || ''
    const page = query.page || ''
    pluginMenuRef.value.querySelectorAll('a[href*="plugin.php"]').forEach((a) => {
      const href = a.getAttribute('href') || ''
      const matchP = href.match(/[?&]p=([^&]+)/)
      const matchPage = href.match(/[?&]page=([^&]+)/)
      const hrefP = matchP ? decodeURIComponent(matchP[1]) : ''
      const hrefPage = matchPage ? decodeURIComponent(matchPage[1]) : ''
      if (hrefP === p && hrefPage === page) {
        a.classList.add('active')
        // 自动展开父级 submenu
        let parent = a.closest('.td-side-submenu')
        while (parent && parent !== pluginMenuRef.value) {
          parent.classList.add('open')
          parent = parent.parentElement.closest('.td-side-submenu')
        }
      }
    })
  }
}

/**
 * 事件委托:处理插件菜单区点击
 * 1. data-td-route 链接 → Vue router 导航(如插件列表)
 * 2. plugin.php 链接 → 改为 SPA iframe 路由,在 layout 内展示
 * submenu toggle 由原生 onclick 处理,不进入此处
 */
function onPluginMenuClick(e) {
  const link = e.target.closest('a[href],a[data-td-route]')
  if (!link) return

  // 1) 内部路由(如插件列表)
  const routePath = link.getAttribute('data-td-route')
  if (routePath) {
    e.preventDefault()
    e.stopPropagation()
    router.push(routePath)
    updatePluginMenuActive()
    return
  }

  const href = link.getAttribute('href') || ''
  // submenu 折叠开关是 javascript:; 且没有 data-td-route,直接放行由原生 onclick 处理
  if (!href.includes('plugin.php')) return

  e.preventDefault()
  e.stopPropagation()

  // 解析 p 和 page 参数
  const match = href.match(/[?&](p|page)=([^&]+)/g)
  if (!match) return
  const params = {}
  match.forEach((m) => {
    const [, k, v] = m.match(/[?&](p|page)=([^&]+)/)
    params[k] = decodeURIComponent(v)
  })

  // 标记当前激活的叶子链接(清除其他 active,给当前加 active)
  if (pluginMenuRef.value) {
    pluginMenuRef.value.querySelectorAll('a.active').forEach((a) => a.classList.remove('active'))
    link.classList.add('active')
  }

  if (params.p && params.page) {
    router.push({ path: '/plugin/page', query: { p: params.p, page: params.page } })
  } else if (params.p) {
    router.push({ path: '/plugin/page', query: { p: params.p } })
  }
}

watch(
  () => [route.path, route.query],
  () => {
    if (isMobile.value) mobileOpen.value = false
    updatePluginMenuActive()
  },
  { immediate: true },
)

onMounted(() => {
  checkMobile()
  window.addEventListener('resize', checkMobile)
  updatePluginMenuActive()
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

/* 品牌区(无 logo) */
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
  font-size: 10px;
  font-weight: 600;
  color: var(--td-sidebar-group-label);
  text-transform: uppercase;
  letter-spacing: 1.2px;
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

/* ============== 插件菜单区(由 theme.php 渲染器注入,无 data-v) ============== */
.td-plugin-menu {
  list-style: none;
  margin: 0;
  padding: 0;
}
.td-plugin-menu :deep(li) {
  list-style: none;
  margin: 0;
}
/* 链接基础样式:与 Vue 渲染的 .td-side-menu a 保持一致 */
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
/* 子菜单 subnav:默认折叠,.open 时展开 */
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
/* 二级子菜单项缩进 */
.td-plugin-menu :deep(.td-side-submenu > .td-side-subnav > li > a) {
  padding-left: 42px;
  font-size: 12.5px;
  padding-top: 7px;
  padding-bottom: 7px;
}
/* 三级子菜单项进一步缩进 */
.td-plugin-menu :deep(.td-side-submenu > .td-side-subnav .td-side-submenu > .td-side-subnav > li > a) {
  padding-left: 62px;
}
/* 箭头旋转:注入的 HTML 中箭头始终是 mdi-chevron-down,折叠时旋转 -90deg */
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
