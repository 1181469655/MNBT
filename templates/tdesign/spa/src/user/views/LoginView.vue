<template>
  <div class="td-login">
    <!-- 左侧背景图区 -->
    <div class="login-banner">
      <img :src="bgImg" alt="" class="banner-img" />
      <div class="banner-overlay"></div>
      <div class="banner-content">
        <h1 class="banner-title">{{ siteName }}</h1>
        <p class="banner-subtitle">控制面板 · 安全稳定的站点管理平台</p>
        <ul class="banner-features">
          <li><i class="mdi mdi-cloud-upload-outline"></i> 一键文件管理</li>
          <li><i class="mdi mdi-backup-restore"></i> 自动数据备份</li>
          <li><i class="mdi mdi-chart-bar"></i> 实时站点统计</li>
        </ul>
      </div>
    </div>

    <!-- 右侧登录卡片区 -->
    <div class="login-side">
      <div class="login-card">
        <div class="brand">
          <h2>{{ siteName }}</h2>
          <p class="sub">控制面板 · 用户登录</p>
        </div>

        <t-form ref="formRef" :data="form" :rules="rules" :label-width="0" @submit="onSubmit">
          <t-form-item name="user">
            <t-input v-model="form.user" placeholder="用户名" clearable size="large" @enter="onSubmit">
              <template #prefix-icon><i class="mdi mdi-account"></i></template>
            </t-input>
          </t-form-item>
          <t-form-item name="pass">
            <t-input
              v-model="form.pass"
              type="password"
              placeholder="密码"
              size="large"
              @enter="onSubmit"
            >
              <template #prefix-icon><i class="mdi mdi-lock"></i></template>
            </t-input>
          </t-form-item>
          <t-form-item v-if="needCaptcha" name="code">
            <div class="captcha-row">
              <t-input
                v-model="form.code"
                placeholder="验证码"
                maxlength="8"
                size="large"
                @enter="onSubmit"
              >
                <template #prefix-icon><i class="mdi mdi-check-all"></i></template>
              </t-input>
              <img
                class="captcha-img"
                :src="captchaUrl"
                alt="验证码"
                title="点击刷新"
                @click="refreshCaptcha"
              />
            </div>
          </t-form-item>
          <t-form-item>
            <t-button theme="primary" type="submit" block size="large" :loading="loading">
              登 录
            </t-button>
          </t-form-item>
        </t-form>

        <p v-if="footer" class="footer-note" v-html="footer"></p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { MessagePlugin } from 'tdesign-vue-next'
import { userLogin } from '@/user/api/auth'
import bgImg from '@/shared/assets/login-bg.webp'

const router = useRouter()
const boot = window.__TD_BOOT__ || {}
const siteName = boot.siteName || 'MNBT'
const footer = boot.footer || ''
const needCaptcha = !!boot.needCaptcha

const formRef = ref()
const loading = ref(false)
const captchaSeed = ref(Date.now())

const form = reactive({
  user: '',
  pass: '',
  code: '',
})

const rules = {
  user: [{ required: true, message: '请输入用户名', trigger: 'blur' }],
  pass: [{ required: true, message: '请输入密码', trigger: 'blur' }],
  code: needCaptcha
    ? [{ required: true, message: '请输入验证码', trigger: 'blur' }]
    : [],
}

const captchaUrl = computed(() => `${boot.codeUrl || './code.php'}?r=${captchaSeed.value}`)

function refreshCaptcha() {
  captchaSeed.value = Date.now()
}

async function onSubmit({ validateResult }) {
  if (validateResult !== true) return
  loading.value = true
  const code = needCaptcha ? form.code : '0000'
  const res = await userLogin(form.user.trim(), form.pass, code)
  loading.value = false
  if (res.ok) {
    MessagePlugin.success('登录成功,正在跳转…')
    window.__TD_BOOT__ = { ...boot, loggedIn: true, user: form.user.trim() }
    window.location.href = './index.php'
    return
  }
  if (needCaptcha) refreshCaptcha()
}

onMounted(() => {
  if (boot.loggedIn) {
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
.captcha-row {
  display: flex;
  gap: 10px;
  width: 100%;
}
.captcha-row :deep(.t-input) {
  flex: 1;
  min-width: 0;
}
.captcha-img {
  height: 40px;
  border-radius: 6px;
  cursor: pointer;
  border: 1px solid var(--td-border);
  flex-shrink: 0;
}
.footer-note {
  margin: 24px 0 0;
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
