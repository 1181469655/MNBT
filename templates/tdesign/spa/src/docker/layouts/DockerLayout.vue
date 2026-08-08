<template>
  <div class="dk-app" :class="{ 'dk-menu-open': menuOpen }">
    <!-- 移动端遮罩 -->
    <div v-if="menuOpen" class="dk-overlay" @click="menuOpen = false"></div>

    <!-- 侧边栏 -->
    <aside class="dk-sidebar" :class="{ open: menuOpen }">
      <!-- 品牌区 -->
      <div class="dk-sidebar-brand">
        <img :src="logoImg" alt="Docker" class="dk-logo" />
        <div class="dk-brand-text">
          <strong>{{ siteName }}</strong>
          <span>Docker 控制台</span>
        </div>
        <button class="dk-mclose" @click="menuOpen = false">
          <i class="mdi mdi-close"></i>
        </button>
      </div>

      <!-- 导航 -->
      <nav class="dk-nav">
        <div class="dk-nav-section"><i class="mdi mdi-docker"></i>容器管理</div>
        <router-link
          v-for="item in menus"
          :key="item.path"
          :to="item.path"
          custom
          v-slot="{ navigate, isActive }"
        >
          <a href="javascript:;" :class="{ active: isActive }" @click="onNav(navigate)">
            <i class="mdi" :class="item.icon"></i>
            <span>{{ item.title }}</span>
          </a>
        </router-link>
      </nav>

      <!-- 底部用户卡 -->
      <div class="dk-sidebar-user">
        <div class="dk-u-avatar">
          <i class="mdi mdi-account-circle"></i>
        </div>
        <div class="dk-u-info">
          <strong>{{ user.username || '用户' }}</strong>
          <span>{{ user.plan_name ? '套餐：' + user.plan_name : 'Docker 用户' }}</span>
          <span v-if="user.datae && user.datae !== '0000-00-00'">到期：{{ user.datae }}</span>
        </div>
        <button class="dk-u-logout" title="退出登录" @click="onLogout">
          <i class="mdi mdi-logout-variant"></i>
        </button>
      </div>
    </aside>

    <!-- 主区 -->
    <main class="dk-main">
      <header class="dk-topbar">
        <button class="dk-hamburger" @click="menuOpen = !menuOpen">
          <i class="mdi" :class="menuOpen ? 'mdi-close' : 'mdi-menu'"></i>
        </button>
        <h2>{{ currentTitle }}</h2>
        <div class="dk-topbar-badges" v-if="planLabel">
          <t-tag variant="light" theme="default">{{ planLabel }}</t-tag>
        </div>
      </header>
      <div class="dk-content">
        <router-view />
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRoute } from 'vue-router'
import { MessagePlugin } from 'tdesign-vue-next'
import { dockerLogout } from '@/docker/api/docker'
import logoImg from '@/shared/assets/docker.svg'

const route = useRoute()
const boot = window.__TD_BOOT__ || {}
const siteName = boot.siteName || 'MNBT'
const user = boot.dockerUser || {}
const menuOpen = ref(false)

const menus = [
  { path: '/console', title: '我的容器', icon: 'mdi-view-dashboard-outline' },
  { path: '/appstore', title: '应用商店', icon: 'mdi-store' },
  { path: '/proxy', title: '反向代理', icon: 'mdi-swap-horizontal' },
  { path: '/image', title: '镜像管理', icon: 'mdi-package-variant' },
  { path: '/volume', title: '数据卷', icon: 'mdi-database' },
  { path: '/compose', title: 'Compose', icon: 'mdi-file-tree' },
]

const currentTitle = computed(() => route.meta?.title || 'Docker 控制台')

const planLabel = computed(() => {
  const parts = []
  if (user.plan_name) parts.push(user.plan_name)
  if (user.cpu_max) parts.push(`${user.cpu_max}核`)
  if (user.mem_max) parts.push(`${user.mem_max}MB`)
  if (user.disk_max && user.disk_max !== '0') parts.push(user.disk_max >= 1024 ? `${(user.disk_max/1024).toFixed(1)}GB磁盘` : `${user.disk_max}MB磁盘`)
  if (user.proxy_max && user.proxy_max !== '0') parts.push(`${user.proxy_max}代理`)
  return parts.length > 1 ? parts.join(' · ') : ''
})

function onNav(navigate) {
  navigate()
  menuOpen.value = false
}

async function onLogout() {
  await dockerLogout()
  MessagePlugin.success('已退出登录')
  setTimeout(() => {
    window.location.href = './login.php'
  }, 600)
}
</script>

<style scoped>
.dk-app {
  display: flex;
  min-height: 100vh;
  background: var(--td-bg-color-page, #f2f3f5);
}

/* ============== 侧边栏 ============== */
.dk-sidebar {
  width: 232px;
  flex-shrink: 0;
  background: #fff;
  border-right: 1px solid var(--td-border-level-1-color, #e7e7e7);
  display: flex;
  flex-direction: column;
  position: sticky;
  top: 0;
  height: 100vh;
}
.dk-sidebar-brand {
  padding: 18px 16px;
  border-bottom: 1px solid var(--td-border-level-1-color, #e7e7e7);
  display: flex;
  align-items: center;
  gap: 12px;
}
.dk-logo {
  width: 46px;
  height: auto;
  flex-shrink: 0;
}
.dk-brand-text {
  display: flex;
  flex-direction: column;
  min-width: 0;
}
.dk-brand-text strong {
  font-size: 15px;
  font-weight: 600;
  color: var(--td-text-color-primary, #1f2937);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.dk-brand-text span {
  font-size: 12px;
  color: var(--td-text-color-secondary, #6b7280);
  margin-top: 1px;
}

.dk-nav {
  flex: 1;
  padding: 14px 12px;
  overflow-y: auto;
}
.dk-nav-section {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 11px;
  color: var(--td-text-color-placeholder, #999);
  padding: 10px 12px 8px;
  letter-spacing: 0.5px;
  text-transform: uppercase;
  font-weight: 600;
}
.dk-nav-section .mdi {
  font-size: 13px;
}
.dk-nav a {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 11px 13px;
  margin: 3px 0;
  border-radius: 8px;
  color: var(--td-text-color-secondary, #4b5563);
  text-decoration: none;
  font-size: 14px;
  font-weight: 500;
  transition: background 0.18s, color 0.18s;
}
.dk-nav a:hover {
  background: var(--td-bg-color-container-hover, #f3f3f3);
  color: var(--td-text-color-primary, #1f2937);
}
.dk-nav a.active {
  background: var(--td-brand-color-light, rgba(0, 82, 217, 0.08));
  color: var(--td-brand-color, #0052d9);
  font-weight: 600;
}
.dk-nav a .mdi {
  width: 18px;
  text-align: center;
  font-size: 17px;
  opacity: 0.9;
}
.dk-nav a.active .mdi {
  opacity: 1;
}

/* 底部用户卡 */
.dk-sidebar-user {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 12px 16px;
  border-top: 1px solid var(--td-border-level-1-color, #e7e7e7);
}
.dk-u-avatar .mdi {
  font-size: 34px;
  color: var(--td-brand-color, #0052d9);
}
.dk-u-info {
  flex: 1;
  min-width: 0;
  display: flex;
  flex-direction: column;
}
.dk-u-info strong {
  font-size: 13.5px;
  font-weight: 600;
  color: var(--td-text-color-primary, #1f2937);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.dk-u-info span {
  font-size: 11.5px;
  color: var(--td-text-color-secondary, #6b7280);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.dk-u-logout {
  border: none;
  background: transparent;
  cursor: pointer;
  color: var(--td-text-color-secondary, #6b7280);
  font-size: 17px;
  padding: 6px;
  border-radius: 8px;
  transition: color 0.18s, background 0.18s;
}
.dk-u-logout:hover {
  color: var(--td-error-color, #d54941);
  background: var(--td-error-color-light, rgba(213, 73, 65, 0.08));
}

/* ============== 主区 ============== */
.dk-main {
  flex: 1;
  min-width: 0;
  display: flex;
  flex-direction: column;
}
.dk-topbar {
  background: rgba(255, 255, 255, 0.85);
  backdrop-filter: blur(8px);
  border-bottom: 1px solid var(--td-border-level-1-color, #e7e7e7);
  padding: 16px 28px;
  position: sticky;
  top: 0;
  z-index: 10;
}
.dk-topbar h2 {
  margin: 0;
  font-size: 18px;
  font-weight: 600;
  color: var(--td-text-color-primary, #1f2937);
}
.dk-topbar-badges {
  margin-left: auto;
}
.dk-content {
  padding: 24px 28px 48px;
  flex: 1;
}

/* ========== 移动端适配 ========== */
.dk-hamburger {
  display: none;
  border: none;
  background: none;
  padding: 6px;
  border-radius: 8px;
  color: #555;
  font-size: 22px;
  cursor: pointer;
  transition: background 0.15s;
  flex-shrink: 0;
}
.dk-hamburger:hover { background: #f3f4f6; }
.dk-mclose {
  display: none;
  border: none;
  background: none;
  padding: 4px;
  border-radius: 6px;
  color: #999;
  font-size: 20px;
  cursor: pointer;
  margin-left: auto;
  flex-shrink: 0;
}
.dk-mclose:hover { color: #333; background: #f3f4f6; }
.dk-overlay {
  display: none;
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.4);
  z-index: 99;
}

@media (max-width: 768px) {
  .dk-hamburger { display: block; }
  .dk-mclose { display: block; }
  .dk-overlay { display: block; }

  .dk-sidebar {
    position: fixed;
    left: 0;
    top: 0;
    bottom: 0;
    z-index: 100;
    transform: translateX(-100%);
    transition: transform 0.25s ease;
    box-shadow: 4px 0 20px rgba(0,0,0,0.12);
  }
  .dk-sidebar.open {
    transform: translateX(0);
  }

  .dk-topbar {
    padding: 12px 16px;
    gap: 10px;
    display: flex;
    align-items: center;
  }
  .dk-topbar h2 { font-size: 16px; flex: 1; }
  .dk-topbar-badges { margin-left: 0; }

  .dk-content { padding: 16px 12px 32px; }
}
</style>
