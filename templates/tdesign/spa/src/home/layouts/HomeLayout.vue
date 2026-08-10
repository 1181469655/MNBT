<template>
  <div class="hd-shell">
    <!-- 顶部导航（guanwang1 风格：毛玻璃 sticky + 绿色 logo + 下划线动画导航） -->
    <header class="hd-nav">
      <div class="container hd-nav-inner">
        <router-link to="/" class="hd-brand">
          <img v-if="boot.siteLogo" :src="boot.siteLogo" alt="logo" class="hd-brand-logo" />
          <h1>{{ boot.siteTitle || 'MNBT' }}</h1>
        </router-link>
        <nav class="hd-nav-right">
          <ul class="hd-nav-list" :class="{ active: menuOpen }">
            <li><router-link to="/" exact-active-class="active">首页</router-link></li>
            <li v-if="boot.hasSite"><router-link to="/about" active-class="active">关于我们</router-link></li>
            <li v-if="boot.hasSite"><router-link to="/site/products" active-class="active">产品中心</router-link></li>
            <li v-if="boot.hasSite"><router-link to="/site/news" active-class="active">新闻资讯</router-link></li>
            <li v-if="boot.hasSite"><router-link to="/site/contact" active-class="active">联系我们</router-link></li>
            <li v-if="!boot.hasSite && boot.hasShop"><router-link to="/shop" active-class="active">主机套餐</router-link></li>
            <li v-if="authState.loggedIn && boot.hasShop"><router-link to="/shop/assets" active-class="active">我的主机</router-link></li>
            <li v-if="authState.loggedIn && boot.hasBalance"><router-link to="/balance" active-class="active">我的余额</router-link></li>
          </ul>
          <div class="hd-nav-actions">
            <template v-if="authState.loggedIn">
              <a v-if="boot.hasUser" :href="accountUrl" class="hd-account-link" title="进入用户中心">
                <i class="mdi mdi-account-circle-outline"></i><span>{{ authState.user?.username }}</span>
              </a>
              <span v-else class="hd-user-name">{{ authState.user?.username }}</span>
              <t-button theme="default" variant="outline" @click="onLogout">退出</t-button>
            </template>
            <template v-else-if="boot.hasUser">
              <router-link to="/login">
                <t-button theme="default" variant="outline">登录</t-button>
              </router-link>
              <router-link to="/register">
                <t-button theme="primary">免费注册</t-button>
              </router-link>
            </template>
          </div>
          <button class="hd-menu-toggle" :class="{ active: menuOpen }" @click="menuOpen = !menuOpen" :aria-label="menuOpen ? '关闭菜单' : '打开菜单'">
            <span></span><span></span><span></span>
          </button>
        </nav>
      </div>
    </header>

    <main class="hd-main">
      <router-view />
    </main>

    <!-- 页脚（guanwang1 风格：三列信息 + 备案 + 返回顶部） -->
    <footer class="hd-footer">
      <div class="container">
        <div class="hd-footer-content">
          <div class="hd-footer-section hd-company-info">
            <h3>{{ boot.siteTitle || 'MNBT' }}</h3>
            <p>{{ boot.footerAbout || '致力于为客户提供稳定、安全、高性能的虚拟主机与云计算服务。' }}</p>
            <div class="hd-social-links">
              <a href="#" class="hd-social-link" aria-label="QQ 群" @click.prevent><i class="mdi mdi-qqchat"></i></a>
              <a href="#" class="hd-social-link" aria-label="邮箱" @click.prevent><i class="mdi mdi-email-outline"></i></a>
              <a href="#" class="hd-social-link" aria-label="GitHub" @click.prevent><i class="mdi mdi-github-circle"></i></a>
            </div>
          </div>
          <div class="hd-footer-section hd-quick-links">
            <h4>快速链接</h4>
            <ul>
              <li><router-link to="/">首页</router-link></li>
              <li v-if="boot.hasSite"><router-link to="/about">关于我们</router-link></li>
              <li v-if="boot.hasShop"><router-link to="/shop">主机套餐</router-link></li>
              <li v-if="boot.hasSite"><router-link to="/site/news">新闻资讯</router-link></li>
              <li v-if="boot.hasSite"><router-link to="/site/contact">联系我们</router-link></li>
            </ul>
          </div>
          <div class="hd-footer-section hd-contact-info">
            <h4>联系方式</h4>
            <div class="hd-contact-item">
              <span class="icon"><i class="mdi mdi-qqchat"></i></span>
              <span class="info">官方 QQ 群：{{ boot.contactQq || '994752422' }}</span>
            </div>
            <div class="hd-contact-item">
              <span class="icon"><i class="mdi mdi-email-outline"></i></span>
              <span class="info">{{ boot.contactEmail || 'support@mnbt.example' }}</span>
            </div>
            <div class="hd-contact-item">
              <span class="icon"><i class="mdi mdi-map-marker-outline"></i></span>
              <span class="info">{{ boot.contactAddress || 'MNBT 虚拟主机平台' }}</span>
            </div>
          </div>
        </div>
        <div class="hd-footer-bottom">
          <p v-if="boot.siteFooter">{{ boot.siteFooter }}</p>
          <p v-else>© {{ new Date().getFullYear() }} {{ boot.siteTitle || 'MNBT' }}. 保留所有权利.</p>
          <div class="hd-footer-beians">
            <a v-if="boot.beianInfo" href="https://beian.miit.gov.cn/" target="_blank" rel="noopener">{{ boot.beianInfo }}</a>
            <a v-if="boot.policeBeian" href="https://www.beian.gov.cn/" target="_blank" rel="noopener">{{ boot.policeBeian }}</a>
          </div>
          <div class="hd-back-to-top" @click="scrollToTop">
            <i class="mdi mdi-arrow-up"></i>
            <span>返回顶部</span>
          </div>
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import { useRoute } from 'vue-router'
import { MessagePlugin } from 'tdesign-vue-next'
import authState from '@/home/store/auth'

const boot = window.__TD_BOOT__ || {}
const menuOpen = ref(false)
const route = useRoute()

// 路由切换后自动收起移动端菜单
watch(() => route.fullPath, () => {
  menuOpen.value = false
})

function onLogout() {
  // user_info 插件的退出路由（GET /account/logout）
  window.location.href = (boot.routeBase || '/index.php?_r=') + 'account/logout'
  MessagePlugin.success('已退出登录')
}

// 用户中心（user_info 插件独立后台）
const accountUrl = (boot.routeBase || '/index.php?_r=') + 'account'

function scrollToTop() {
  window.scrollTo({ top: 0, behavior: 'smooth' })
}
</script>
