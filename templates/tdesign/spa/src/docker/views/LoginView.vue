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
            theme="default"
            variant="outline"
            block
            size="large"
            :disabled="captchaOk"
            @click="showCaptcha = true"
          >
            <template v-if="captchaOk">
              <i class="mdi mdi-check-circle" style="color:#2ba471"></i> 验证通过
            </template>
            <template v-else>
              <i class="mdi mdi-shield-check-outline"></i> 点击进行人机验证
            </template>
          </t-button>

          <t-button
            theme="primary"
            block
            size="large"
            :loading="loading"
            :disabled="!captchaOk"
            @click="onSubmit"
          >
            登 录
          </t-button>
        </div>

        <!-- 滑块验证码弹窗 -->
        <Teleport to="body">
          <Transition name="captcha-modal">
            <div v-if="showCaptcha" class="captcha-overlay" @click.self="onCloseCaptcha">
              <div class="captcha-dialog">
                <div class="captcha-dialog__head">
                  <h3>安全验证</h3>
                  <button class="captcha-dialog__close" @click="onCloseCaptcha" title="关闭">
                    <i class="mdi mdi-close"></i>
                  </button>
                </div>
                <div class="captcha-dialog__body">
                  <SliderCaptcha
                    ref="captchaRef"
                    @success="onCaptchaSuccess"
                    @reset="onCaptchaReset"
                  />
                </div>
              </div>
            </div>
          </Transition>
        </Teleport>

        <p v-if="footer" class="footer-note" v-html="footer"></p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue'
import { MessagePlugin } from 'tdesign-vue-next'
import { dockerLogin } from '@/docker/api/docker'
import SliderCaptcha from '@/docker/components/SliderCaptcha.vue'
import logoImg from '@/shared/assets/docker.svg'
import bgImg from '@/shared/assets/login-bg.webp'

const boot = window.__TD_BOOT__ || {}
const siteName = boot.siteName || 'MNBT'
const footer = boot.footer || ''

const loading = ref(false)
const showPwd = ref(false)
const showCaptcha = ref(false)
const captchaOk = ref(false)
const captchaRef = ref(null)
const form = reactive({ username: '', password: '' })

function onCaptchaSuccess() {
  captchaOk.value = true
  // 短暂展示成功状态后关闭弹窗
  setTimeout(() => {
    showCaptcha.value = false
  }, 800)
}

function onCaptchaReset() {
  captchaOk.value = false
}

function onCloseCaptcha() {
  showCaptcha.value = false
}

async function onSubmit() {
  if (!form.username || !form.password) {
    MessagePlugin.warning('请输入账号和密码')
    return
  }
  if (!captchaOk.value) {
    MessagePlugin.warning('请完成滑块验证')
    return
  }
  loading.value = true
  const r = await dockerLogin(form.username.trim(), form.password)
  loading.value = false
  if (!r.ok) {
    // 登录失败后刷新验证码状态
    captchaOk.value = false
    showCaptcha.value = false
  } else {
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

/* ---- 验证码弹窗 ---- */
.captcha-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.45);
  backdrop-filter: blur(4px);
  display: grid;
  place-items: center;
  z-index: 9999;
  padding: 24px;
}
.captcha-dialog {
  width: min(420px, 100%);
  background: #fff;
  border-radius: 16px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
  overflow: hidden;
  animation: captcha-dialog-in 0.25s ease-out;
}
@keyframes captcha-dialog-in {
  from { opacity: 0; transform: scale(0.92) translateY(12px); }
  to   { opacity: 1; transform: scale(1) translateY(0); }
}
.captcha-dialog__head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 16px 20px 0;
}
.captcha-dialog__head h3 {
  margin: 0;
  font-size: 16px;
  font-weight: 600;
  color: #1f2937;
}
.captcha-dialog__close {
  border: none;
  background: none;
  padding: 4px;
  border-radius: 6px;
  cursor: pointer;
  color: #999;
  font-size: 18px;
  transition: color 0.15s, background 0.15s;
}
.captcha-dialog__close:hover {
  color: #333;
  background: #f3f4f6;
}
.captcha-dialog__body {
  padding: 16px 20px 20px;
}

/* 弹窗过渡动画 */
.captcha-modal-enter-active {
  transition: opacity 0.2s ease;
}
.captcha-modal-leave-active {
  transition: opacity 0.15s ease;
}
.captcha-modal-enter-from,
.captcha-modal-leave-to {
  opacity: 0;
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

/* 验证码弹窗移动端 */
@media (max-width: 600px) {
  .captcha-dialog {
    width: calc(100vw - 32px);
    margin: 0 16px;
  }
  .captcha-dialog__body {
    padding: 12px 14px 16px;
  }
  .captcha-dialog__head { padding: 14px 16px 0; }
}
</style>
