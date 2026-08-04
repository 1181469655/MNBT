<template>
  <div class="td-login">
    <!-- 左侧背景图区 -->
    <div class="login-banner">
      <img :src="bgImg" alt="" class="banner-img" />
      <div class="banner-overlay"></div>
      <div class="banner-content">
        <h1 class="banner-title">{{ siteName }}</h1>
        <p class="banner-subtitle">Docker 容器控制台 · 弹性便捷的容器管理平台</p>
        <ul class="banner-features">
          <li><i class="mdi mdi-docker"></i> 容器应用一键部署</li>
          <li><i class="mdi mdi-apps"></i> 精选应用商店</li>
          <li><i class="mdi mdi-gauge"></i> 资源配额灵活管控</li>
        </ul>
      </div>
    </div>

    <!-- 右侧登录卡片区 -->
    <div class="login-side">
      <div class="login-card">
        <div class="brand">
          <img :src="logoImg" alt="Docker" class="brand-logo" />
          <h2>Docker 容器控制台</h2>
          <p class="sub">{{ siteName }} · 用户登录</p>
        </div>

        <!-- 内容输入区 -->
        <div class="login-form">
          <t-input
            v-model="form.username"
            placeholder="请输入账号"
            size="large"
            clearable
            autofocus
            @enter="onSubmit"
          >
            <template #prefix-icon><i class="mdi mdi-account-outline"></i></template>
          </t-input>

          <t-input
            v-model="form.password"
            :type="showPwd ? 'text' : 'password'"
            placeholder="请输入密码"
            size="large"
            @enter="onSubmit"
          >
            <template #prefix-icon><i class="mdi mdi-lock-outline"></i></template>
            <template #suffix-icon>
              <i
                class="mdi pwd-toggle"
                :class="showPwd ? 'mdi-eye-off-outline' : 'mdi-eye-outline'"
                @click.stop="showPwd = !showPwd"
              ></i>
            </template>
          </t-input>

          <t-button
            theme="primary"
            block
            size="large"
            :loading="loading"
            @click="onSubmit"
          >
            登 录
          </t-button>
        </div>

        <p v-if="footer" class="footer-note" v-html="footer"></p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue'
import { MessagePlugin } from 'tdesign-vue-next'
import { dockerLogin } from '@/docker/api/docker'
import logoImg from '@/shared/assets/docker.svg'
import bgImg from '@/shared/assets/login-bg.webp'

const boot = window.__TD_BOOT__ || {}
const siteName = boot.siteName || 'MNBT'
const footer = boot.footer || ''

const loading = ref(false)
const showPwd = ref(false)
const form = reactive({ username: '', password: '' })

async function onSubmit() {
  if (!form.username || !form.password) {
    MessagePlugin.warning('请输入账号和密码')
    return
  }
  loading.value = true
  const r = await dockerLogin(form.username.trim(), form.password)
  loading.value = false
  if (r.ok) {
    MessagePlugin.success(r.message || '登录成功')
    setTimeout(() => {
      window.location.href = './console.php'
    }, 600)
  }
}
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
    rgba(0, 30, 80, 0.6) 0%,
    rgba(0, 50, 130, 0.4) 50%,
    rgba(20, 80, 180, 0.3) 100%
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
  text-align: center;
}
.brand-logo {
  width: 96px;
  height: auto;
  margin-bottom: 10px;
}
.brand h2 {
  margin: 0;
  font-size: 22px;
  font-weight: 700;
  letter-spacing: -0.02em;
  color: var(--td-text, #1f2937);
}
.sub {
  margin: 8px 0 0;
  font-size: 13px;
  color: var(--td-text-secondary, #6b7280);
}
/* 内容输入区 */
.login-form {
  display: flex;
  flex-direction: column;
  gap: 16px;
}
.pwd-toggle {
  cursor: pointer;
  font-size: 17px;
  color: var(--td-text-color-secondary, #6b7280);
  transition: color 0.15s;
}
.pwd-toggle:hover {
  color: var(--td-text-color-primary, #1f2937);
}
.footer-note {
  margin: 24px 0 0;
  text-align: center;
  font-size: 12px;
  color: var(--td-text-secondary, #6b7280);
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
