<template>
  <div class="ql-layout" :class="{ collapsed: sidebarCollapsed, 'mobile-open': mobileOpen }">
    <aside class="ql-sidebar">
      <div class="side-brand">
        <div class="mark">
          <el-icon :size="20"><Cloudy /></el-icon>
        </div>
        <div v-show="!sidebarCollapsed || isMobile" class="brand-text">
          <strong>{{ siteName }}</strong>
          <span>清凉云</span>
        </div>
      </div>

      <el-scrollbar class="side-scroll">
        <el-menu
          :default-active="activeMenu"
          :collapse="sidebarCollapsed && !isMobile"
          :collapse-transition="false"
          router
          @select="onMenuSelect"
        >
          <el-menu-item index="/dashboard">
            <el-icon><Odometer /></el-icon>
            <span>控制面板</span>
          </el-menu-item>
          <el-menu-item index="/stats">
            <el-icon><DataLine /></el-icon>
            <span>站点统计</span>
          </el-menu-item>

          <template v-if="isCdn">
            <el-menu-item index="/settings/CDN_url">
              <el-icon><Link /></el-icon>
              <span>域名修改</span>
            </el-menu-item>
          </template>
          <template v-else>
            <el-sub-menu index="cfg">
              <template #title>
                <el-icon><Setting /></el-icon>
                <span>基本配置</span>
              </template>
              <el-menu-item
                v-for="item in basicMenus"
                :key="item.gn"
                :index="`/settings/${item.gn}`"
              >
                {{ item.label }}
              </el-menu-item>
            </el-sub-menu>

            <el-sub-menu index="data">
              <template #title>
                <el-icon><FolderOpened /></el-icon>
                <span>数据管理</span>
              </template>
              <el-menu-item index="/legacy?u=ftp.php&t=在线文件管理">在线文件</el-menu-item>
              <el-menu-item index="mysql-panel" @click.prevent="openExternal('./mysql.php')">
                SQL 面板
              </el-menu-item>
              <el-menu-item index="/legacy?u=sqlgl.php&t=SQL数据备份">数据备份</el-menu-item>
              <el-menu-item index="/settings/mysqlcz">SQL 权限</el-menu-item>
            </el-sub-menu>

            <el-sub-menu index="site">
              <template #title>
                <el-icon><Monitor /></el-icon>
                <span>网站管理</span>
              </template>
              <el-menu-item index="/legacy?u=webgl.php%3Fgn%3Dyjbs&t=一键部署">一键部署</el-menu-item>
              <el-menu-item index="/monitor">监控任务</el-menu-item>
              <el-menu-item index="/notice">通知日志</el-menu-item>
            </el-sub-menu>
          </template>
        </el-menu>
      </el-scrollbar>

      <div v-if="footer" class="side-footer">{{ footer }}</div>
    </aside>

    <div class="ql-main">
      <header class="ql-header">
        <div class="left">
          <el-button text circle @click="toggleSidebar">
            <el-icon :size="20"><Fold v-if="!sidebarCollapsed" /><Expand v-else /></el-icon>
          </el-button>
          <div class="page-title">{{ pageTitle }}</div>
        </div>
        <div class="right">
          <el-tag type="success" effect="light" round size="small" class="user-tag">
            {{ userName }}
          </el-tag>
          <el-dropdown trigger="click">
            <el-button circle>
              <el-icon><UserFilled /></el-icon>
            </el-button>
            <template #dropdown>
              <el-dropdown-menu>
                <el-dropdown-item @click="goSettings('xgpass')">修改密码</el-dropdown-item>
                <el-dropdown-item divided @click="onLogout">退出登录</el-dropdown-item>
              </el-dropdown-menu>
            </template>
          </el-dropdown>
        </div>
      </header>

      <main class="ql-content">
        <router-view />
      </main>
    </div>

    <div v-if="mobileOpen" class="mask" @click="mobileOpen = false" />
  </div>
</template>

<script setup>
import { computed, ref, onMounted, onUnmounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import {
  Cloudy,
  Odometer,
  DataLine,
  Setting,
  FolderOpened,
  Monitor,
  Link,
  Fold,
  Expand,
  UserFilled,
} from '@element-plus/icons-vue'
import { ElMessageBox } from 'element-plus'
import { logout } from '../api/user'

const route = useRoute()
const router = useRouter()
const boot = window.__QL_BOOT__ || {}

const siteName = boot.siteName || '清凉云'
const userName = boot.user || '用户'
const footer = boot.footer || ''
const isCdn = String(boot.productType || '') === '1'

const sidebarCollapsed = ref(false)
const mobileOpen = ref(false)
const isMobile = ref(false)

const basicMenus = [
  { gn: 'php', label: 'PHP 版本' },
  { gn: 'url', label: '域名修改' },
  { gn: 'pass', label: '密码访问' },
  { gn: 'mrwd', label: '默认文档' },
  { gn: 'yxml', label: '运行目录' },
  { gn: 'wjt', label: '伪静态' },
  { gn: 'ssl', label: 'SSL 配置' },
  { gn: 'fdl', label: '防盗链' },
  { gn: 'gzip', label: 'Gzip' },
  { gn: 'cache', label: '缓存配置' },
  { gn: 'xgpass', label: '修改密码' },
]

const activeMenu = computed(() => {
  if (route.path.startsWith('/settings')) {
    return `/settings/${route.params.gn || 'php'}`
  }
  if (route.path === '/legacy') {
    const u = route.query.u || ''
    if (String(u).includes('ftp')) return '/legacy?u=ftp.php&t=在线文件管理'
    if (String(u).includes('sqlgl')) return '/legacy?u=sqlgl.php&t=SQL数据备份'
    if (String(u).includes('webgl')) return '/legacy?u=webgl.php%3Fgn%3Dyjbs&t=一键部署'
  }
  return route.path
})

const pageTitle = computed(() => route.meta.title || '控制面板')

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

function onMenuSelect() {
  if (isMobile.value) mobileOpen.value = false
}

function openExternal(url) {
  window.open(url, '_blank')
}

function goSettings(gn) {
  router.push(`/settings/${gn}`)
}

async function onLogout() {
  try {
    await ElMessageBox.confirm('确定退出登录？', '提示', {
      type: 'warning',
      confirmButtonText: '退出',
      cancelButtonText: '取消',
    })
  } catch {
    return
  }
  await logout()
  window.location.href = './login.php'
}

onMounted(() => {
  checkMobile()
  window.addEventListener('resize', checkMobile)
})
onUnmounted(() => {
  window.removeEventListener('resize', checkMobile)
})
</script>

<style scoped>
.ql-layout {
  display: flex;
  min-height: 100dvh;
  background: var(--ql-bg);
}
.ql-sidebar {
  width: var(--ql-sidebar-w);
  flex-shrink: 0;
  background: var(--ql-surface);
  border-right: 1px solid var(--ql-border);
  display: flex;
  flex-direction: column;
  transition: width 0.2s ease;
  z-index: 30;
}
.ql-layout.collapsed .ql-sidebar {
  width: 72px;
}
.side-brand {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 18px 16px;
  border-bottom: 1px solid var(--ql-border);
}
.mark {
  width: 40px;
  height: 40px;
  border-radius: 12px;
  display: grid;
  place-items: center;
  color: #fff;
  background: linear-gradient(135deg, #38d9a9, #12b886);
  flex-shrink: 0;
}
.brand-text {
  display: flex;
  flex-direction: column;
  min-width: 0;
}
.brand-text strong {
  font-size: 14px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.brand-text span {
  font-size: 11px;
  color: var(--ql-text-secondary);
}
.side-scroll {
  flex: 1;
  padding: 12px 10px;
}
.side-footer {
  padding: 12px;
  font-size: 11px;
  color: var(--ql-text-secondary);
  border-top: 1px solid var(--ql-border);
  text-align: center;
  line-height: 1.4;
}
.ql-main {
  flex: 1;
  min-width: 0;
  display: flex;
  flex-direction: column;
}
.ql-header {
  height: 60px;
  background: rgba(255, 255, 255, 0.9);
  backdrop-filter: blur(10px);
  border-bottom: 1px solid var(--ql-border);
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 16px;
  position: sticky;
  top: 0;
  z-index: 20;
}
.left,
.right {
  display: flex;
  align-items: center;
  gap: 10px;
}
.page-title {
  font-size: 16px;
  font-weight: 600;
}
.user-tag {
  max-width: 140px;
  overflow: hidden;
  text-overflow: ellipsis;
}
.ql-content {
  flex: 1;
  min-height: 0;
}
.mask {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.35);
  z-index: 25;
}

@media (max-width: 991px) {
  .ql-sidebar {
    position: fixed;
    left: 0;
    top: 0;
    bottom: 0;
    transform: translateX(-100%);
    width: min(280px, 86vw) !important;
    transition: transform 0.25s ease;
  }
  .ql-layout.mobile-open .ql-sidebar {
    transform: translateX(0);
  }
  .ql-layout.collapsed .ql-sidebar {
    width: min(280px, 86vw) !important;
  }
}
</style>
