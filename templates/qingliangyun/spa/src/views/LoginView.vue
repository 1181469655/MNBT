<template>
  <div class="login-page">
    <div class="login-bg" />
    <div class="login-card">
      <div class="brand">
        <div class="logo-mark">
          <el-icon :size="28"><Cloudy /></el-icon>
        </div>
        <h1>{{ siteName }}</h1>
        <p class="ql-muted">清凉云控制面板 · 轻量 · 专业 · 高效</p>
      </div>

      <el-form ref="formRef" :model="form" :rules="rules" size="large" @submit.prevent>
        <el-form-item prop="user">
          <el-input
            v-model="form.user"
            placeholder="用户名 / 账号"
            :prefix-icon="User"
            clearable
            @keyup.enter="onSubmit"
          />
        </el-form-item>
        <el-form-item prop="pass">
          <el-input
            v-model="form.pass"
            type="password"
            placeholder="密码"
            :prefix-icon="Lock"
            show-password
            @keyup.enter="onSubmit"
          />
        </el-form-item>
        <el-form-item v-if="needCaptcha" prop="code">
          <div class="captcha-row">
            <el-input
              v-model="form.code"
              placeholder="验证码"
              maxlength="8"
              @keyup.enter="onSubmit"
            />
            <img
              class="captcha-img"
              :src="captchaUrl"
              alt="验证码"
              title="点击刷新"
              @click="refreshCaptcha"
            />
          </div>
        </el-form-item>
        <el-button
          type="primary"
          class="submit-btn"
          :loading="loading"
          round
          @click="onSubmit"
        >
          登 录
        </el-button>
      </el-form>

      <p v-if="footer" class="footer-note">{{ footer }}</p>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { User, Lock, Cloudy } from '@element-plus/icons-vue'
import { ElMessage } from 'element-plus'
import { login } from '../api/user'

const router = useRouter()
const boot = window.__QL_BOOT__ || {}
const siteName = boot.siteName || '清凉云'
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

const captchaUrl = computed(
  () => `./code.php?r=${captchaSeed.value}`
)

function refreshCaptcha() {
  captchaSeed.value = Date.now()
}

async function onSubmit() {
  if (!formRef.value) return
  await formRef.value.validate(async (valid) => {
    if (!valid) return
    loading.value = true
    const code = needCaptcha ? form.code : '0000'
    const res = await login(form.user.trim(), form.pass, code)
    loading.value = false
    if (res.ok) {
      ElMessage.success('登录成功')
      window.__QL_BOOT__ = { ...boot, loggedIn: true, user: form.user.trim() }
      // 整页跳转以刷新 PHP session 上下文
      window.location.href = './index.php'
      return
    }
    if (needCaptcha) refreshCaptcha()
  })
}

onMounted(() => {
  if (boot.loggedIn) {
    router.replace('/dashboard')
  }
})
</script>

<style scoped>
.login-page {
  min-height: 100dvh;
  display: grid;
  place-items: center;
  padding: 24px;
  position: relative;
  overflow: hidden;
}
.login-bg {
  position: absolute;
  inset: 0;
  background:
    radial-gradient(ellipse 80% 60% at 20% 20%, rgba(56, 217, 169, 0.35), transparent 55%),
    radial-gradient(ellipse 70% 50% at 85% 70%, rgba(18, 184, 134, 0.22), transparent 50%),
    linear-gradient(160deg, #f0fff8 0%, #e6fcf5 40%, #f8fffc 100%);
  z-index: 0;
}
.login-card {
  position: relative;
  z-index: 1;
  width: min(420px, 100%);
  background: rgba(255, 255, 255, 0.88);
  backdrop-filter: blur(16px);
  border: 1px solid rgba(216, 235, 227, 0.9);
  border-radius: 24px;
  padding: 36px 32px 28px;
  box-shadow: 0 16px 48px rgba(18, 184, 134, 0.12);
}
.brand {
  text-align: center;
  margin-bottom: 28px;
}
.logo-mark {
  width: 56px;
  height: 56px;
  margin: 0 auto 12px;
  border-radius: 16px;
  display: grid;
  place-items: center;
  color: #fff;
  background: linear-gradient(135deg, #38d9a9, #12b886);
  box-shadow: 0 8px 20px rgba(18, 184, 134, 0.35);
}
.brand h1 {
  margin: 0;
  font-size: 22px;
  font-weight: 700;
  letter-spacing: -0.02em;
  color: var(--ql-text);
}
.brand p {
  margin: 8px 0 0;
}
.submit-btn {
  width: 100%;
  height: 44px;
  font-weight: 600;
  letter-spacing: 0.08em;
  margin-top: 4px;
}
.captcha-row {
  display: flex;
  gap: 10px;
  width: 100%;
}
.captcha-img {
  height: 40px;
  border-radius: 10px;
  cursor: pointer;
  border: 1px solid var(--ql-border);
  flex-shrink: 0;
}
.footer-note {
  margin: 20px 0 0;
  text-align: center;
  font-size: 12px;
  color: var(--ql-text-secondary);
}
</style>
