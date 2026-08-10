<template>
  <div class="td-login">
    <!-- 左侧背景图区 -->
    <div class="login-banner">
      <img :src="bgImg" alt="" class="banner-img" />
      <div class="banner-overlay"></div>
      <div class="banner-content">
        <h1 class="banner-title">{{ siteName }}</h1>
        <p class="banner-subtitle">用户中心 · 我的账户、个人信息与安全</p>
        <ul class="banner-features">
          <li><i class="mdi mdi-account-details"></i> 个人信息管理</li>
          <li><i class="mdi mdi-shield-account-outline"></i> 账号安全保障</li>
          <li><i class="mdi mdi-sync"></i> 独立用户体系</li>
        </ul>
      </div>
    </div>

    <!-- 右侧登录卡片区 -->
    <div class="login-side">
      <div class="login-card">
        <div class="brand">
          <h2>{{ siteName }}</h2>
          <p class="sub">用户中心 · 欢迎回来</p>
        </div>

        <t-form ref="formRef" :data="form" :rules="rules" :label-width="0" @submit="onSubmit">
          <t-form-item name="username">
            <t-input v-model="form.username" placeholder="用户名" clearable size="large" @enter="onSubmit">
              <template #prefix-icon><i class="mdi mdi-account"></i></template>
            </t-input>
          </t-form-item>
          <t-form-item name="password">
            <t-input
              v-model="form.password"
              type="password"
              placeholder="密码"
              size="large"
              @enter="onSubmit"
            >
              <template #prefix-icon><i class="mdi mdi-lock"></i></template>
            </t-input>
          </t-form-item>
          <t-form-item>
            <t-button theme="primary" type="submit" block size="large" :loading="loading">
              登 录
            </t-button>
          </t-form-item>
        </t-form>

        <p class="switch-note">
          还没有账号?
          <router-link to="/register" class="switch-link">立即注册</router-link>
        </p>

        <p v-if="footer" class="footer-note" v-html="footer"></p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { MessagePlugin } from 'tdesign-vue-next'
import { accountLogin, getMe } from '@/account/api/auth'
import bgImg from '@/shared/assets/login-bg.webp'

const route = useRoute()
const router = useRouter()
const boot = window.__TD_BOOT__ || {}
const siteName = boot.siteName || 'MNBT'
const footer = boot.footer || ''

const formRef = ref()
const loading = ref(false)

const form = reactive({
  username: '',
  password: '',
})

const rules = {
  username: [{ required: true, message: '请输入用户名', trigger: 'blur' }],
  password: [{ required: true, message: '请输入密码', trigger: 'blur' }],
}

async function onSubmit({ validateResult }) {
  if (validateResult !== true) return
  loading.value = true
  const res = await accountLogin(form.username.trim(), form.password)
  loading.value = false
  if (res.ok) {
    MessagePlugin.success('登录成功,正在跳转…')
    const me = await getMe()
    window.__TD_BOOT__ = { ...boot, loggedIn: true, accountUser: me.ok ? me.data.user : null }
    const redirect = typeof route.query.redirect === 'string' ? route.query.redirect : '/dashboard'
    router.replace(redirect)
    return
  }
}

onMounted(() => {
  if (boot.loggedIn && boot.accountUser) {
    router.replace('/dashboard')
  }
})
</script>

<style scoped>
.td-login {
  min-height: 100vh;
  display: grid;
  grid-template-columns: 1fr 480px;
  background: #f5f7fa;
}

/* ============== 左侧背景图区 ============== */
.login-banner {
  position: relative;
  overflow: hidden;
  min-height: 100vh;
}
.banner-img {
  position: absolute;
  inset: 0;
  width: 100%;
  height: 100%;
  object-fit: cover;
  z-index: 0;
}
.banner-overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(
    135deg,
    rgba(0, 30, 80, 0.65) 0%,
    rgba(0, 50, 130, 0.45) 50%,
    rgba(20, 80, 180, 0.35) 100%
  );
  z-index: 1;
}
.banner-content {
  position: absolute;
  left: 0;
  right: 0;
  bottom: 0;
  padding: 60px;
  z-index: 2;
  color: #fff;
  animation: banner-fade-in 0.8s ease-out;
}
.banner-title {
  margin: 0;
  font-size: 38px;
  font-weight: 700;
  letter-spacing: -0.02em;
  text-shadow: 0 2px 16px rgba(0, 0, 0, 0.25);
}
.banner-subtitle {
  margin: 12px 0 28px;
  font-size: 15px;
  opacity: 0.92;
  text-shadow: 0 1px 8px rgba(0, 0, 0, 0.2);
}
.banner-features {
  list-style: none;
  margin: 0;
  padding: 0;
  display: flex;
  flex-wrap: wrap;
  gap: 18px;
}
.banner-features li {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 13px;
  opacity: 0.88;
}
.banner-features i {
  font-size: 18px;
  color: #4fc3f7;
}

@keyframes banner-fade-in {
  from { opacity: 0; transform: translateY(20px); }
  to   { opacity: 1; transform: translateY(0); }
}

/* ============== 右侧登录卡片区 ============== */
.login-side {
  display: grid;
  place-items: center;
  padding: 32px;
  min-height: 100vh;
  background: #fff;
}
.login-card {
  width: min(380px, 100%);
  animation: card-fade-in 0.6s ease-out;
}
@keyframes card-fade-in {
  from { opacity: 0; transform: translateX(20px); }
  to   { opacity: 1; transform: translateX(0); }
}
.brand {
  margin-bottom: 32px;
}
.brand h2 {
  margin: 0;
  font-size: 24px;
  font-weight: 700;
  letter-spacing: -0.02em;
  color: var(--td-text);
}
.sub {
  margin: 8px 0 0;
  font-size: 13px;
  color: var(--td-text-secondary);
}
.switch-note {
  margin: 20px 0 0;
  text-align: center;
  font-size: 13px;
  color: var(--td-text-secondary);
}
.switch-link {
  color: var(--td-brand);
  font-weight: 500;
}
.footer-note {
  margin: 16px 0 0;
  text-align: center;
  font-size: 12px;
  color: var(--td-text-secondary);
}

/* ============== 响应式 ============== */
@media (max-width: 960px) {
  .td-login {
    grid-template-columns: 1fr;
  }
  .login-banner {
    min-height: 280px;
    max-height: 36vh;
  }
  .banner-content {
    padding: 32px;
  }
  .banner-title {
    font-size: 26px;
  }
  .banner-subtitle {
    margin: 8px 0 16px;
    font-size: 13px;
  }
  .banner-features {
    gap: 12px;
  }
  .banner-features li {
    font-size: 12px;
  }
  .login-side {
    padding: 24px;
    min-height: auto;
  }
}

@media (max-width: 600px) {
  .login-banner {
    min-height: 200px;
    max-height: 30vh;
  }
  .banner-content {
    padding: 20px;
  }
  .banner-title {
    font-size: 20px;
  }
  .banner-features {
    display: none;
  }
  .login-side {
    padding: 20px 16px;
  }
}
</style>
