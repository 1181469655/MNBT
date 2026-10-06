<template>
  <div class="hv-wrap">
    <t-button theme="default" variant="outline" block size="large" @click="open">
      <template v-if="verified">
        <i class="mdi mdi-check-circle" style="color:#2ba471"></i> 验证通过
      </template>
      <template v-else>
        <i class="mdi mdi-shield-check-outline"></i> 点击进行人机验证
      </template>
    </t-button>

    <!-- 内置验证码弹窗（官方 verifition 组件，服务端二次校验） -->
    <Verify
      v-if="mode === 'builtin'"
      ref="builtinRef"
      captcha-type="blockPuzzle"
      mode="pop"
      @success="onBuiltinSuccess"
      @error="$emit('error')"
    />

    <!-- 插件适配器挂载弹窗 -->
    <Teleport to="body">
      <div v-if="mode === 'plugin' && pluginShow" class="hv-mask" @click.self="closePlugin">
        <div class="hv-dialog">
          <div class="hv-dialog__head">
            <h3>安全验证</h3>
            <button class="hv-dialog__close" title="关闭" @click="closePlugin">
              <i class="mdi mdi-close"></i>
            </button>
          </div>
          <div class="hv-dialog__body">
            <div ref="mountEl" class="hv-mount"></div>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
/**
 * HumanVerify —— 三端登录共用的人机验证组件
 *
 * 按 boot.captcha 分派两种模式：
 * - builtin（默认）：官方 verifition 滑块/点选弹窗，登录请求提交 captchaVerification
 * - 插件接管（验证码插件通过 filter('spa.boot') 注入 boot.captcha =
 *     { provider:'xxx', adapter:'<适配器JS完整URL>' }）：动态加载适配器 JS 并挂载，
 *     契约：window.MNBT_CAPTCHA_ADAPTER = { mount(el, { onSuccess, onFail }), reset() }，
 *     onSuccess 收到的字符串作为 captchaToken 随登录请求提交。
 *
 * 用法：<HumanVerify ref="captchaRef" @verified="payload => ..." @error="..." />
 *       提交时把 verified 载荷对象展开进登录请求；失败后调用 captchaRef.value.reset()
 */
import { ref, nextTick } from 'vue'
import Verify from './Verify.vue'

const boot = window.__TD_BOOT__ || {}
const captchaBoot = boot.captcha || {}
const mode = captchaBoot.provider && captchaBoot.provider !== 'builtin' ? 'plugin' : 'builtin'

const emit = defineEmits(['verified', 'reset', 'error'])

const builtinRef = ref(null)
const mountEl = ref(null)
const verified = ref(false)
const pluginShow = ref(false)
let adapterPromise = null

function open() {
  if (verified.value) return
  if (mode === 'builtin') {
    builtinRef.value?.show()
  } else {
    showPlugin()
  }
}

function loadAdapter() {
  if (window.MNBT_CAPTCHA_ADAPTER) return Promise.resolve(window.MNBT_CAPTCHA_ADAPTER)
  if (!adapterPromise) {
    adapterPromise = new Promise((resolve, reject) => {
      const s = document.createElement('script')
      s.src = captchaBoot.adapter
      s.onload = () => resolve(window.MNBT_CAPTCHA_ADAPTER || null)
      s.onerror = () => reject(new Error('验证码适配器加载失败'))
      document.head.appendChild(s)
    })
  }
  return adapterPromise
}

async function showPlugin() {
  pluginShow.value = true
  try {
    const adapter = await loadAdapter()
    if (!adapter || typeof adapter.mount !== 'function') {
      throw new Error('验证码适配器契约无效')
    }
    // 等弹窗 v-if DOM 渲染完成后再挂载适配器
    await nextTick()
    adapter.mount(mountEl.value, {
      onSuccess: (payload) => {
        const token = typeof payload === 'string' ? payload : String(payload ?? '')
        verified.value = true
        emit('verified', { captchaToken: token })
        closePlugin()
      },
      onFail: () => emit('error'),
    })
  } catch (e) {
    pluginShow.value = false
    emit('error')
  }
}

function closePlugin() {
  pluginShow.value = false
  try {
    window.MNBT_CAPTCHA_ADAPTER?.reset?.()
  } catch { /* 适配器可选实现 reset */ }
}

function onBuiltinSuccess(payload) {
  verified.value = true
  emit('verified', { captchaVerification: payload.captchaVerification })
}

/** 登录失败后重置验证状态（验证载荷一次性，必须重取） */
function reset() {
  verified.value = false
  if (mode === 'builtin') {
    builtinRef.value?.refresh()
  } else {
    closePlugin()
  }
  emit('reset')
}

defineExpose({ reset, verified })
</script>

<style scoped>
.hv-wrap {
  width: 100%;
}
</style>

<style>
/* 插件适配器挂载弹窗（全局样式，弹窗经 Teleport 挂到 body） */
.hv-mask {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.4);
  display: grid;
  place-items: center;
  z-index: 9999;
  padding: 24px;
}
.hv-dialog {
  width: min(420px, 100%);
  background: #fff;
  border-radius: 12px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.18);
  overflow: hidden;
}
.hv-dialog__head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 14px 18px 0;
}
.hv-dialog__head h3 {
  margin: 0;
  font-size: 16px;
  font-weight: 600;
  color: #1f2937;
}
.hv-dialog__close {
  border: none;
  background: none;
  padding: 4px;
  border-radius: 6px;
  cursor: pointer;
  color: #999;
  font-size: 18px;
}
.hv-dialog__close:hover {
  color: #333;
  background: #f3f4f6;
}
.hv-dialog__body {
  padding: 14px 18px 18px;
}
.hv-mount:empty::after {
  content: '正在加载验证组件…';
  display: block;
  text-align: center;
  color: #999;
  font-size: 13px;
  padding: 24px 0;
}
</style>
