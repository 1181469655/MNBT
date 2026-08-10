<template>
  <div class="hd-page">
    <div class="hd-container">
      <div class="hd-auth-wrap">
        <h1 class="hd-auth-title">登录</h1>
        <p class="hd-auth-sub">使用账号密码登录您的账户</p>

        <div class="hd-card">
          <div class="hd-card-body" style="padding:24px 28px;">

            <t-alert v-if="errorMsg" theme="error" :message="errorMsg" style="margin-bottom:16px;" close @close="errorMsg=''" />

            <t-form ref="formRef" :data="formData" :rules="rules" label-align="top" @submit="onSubmit">
              <t-form-item label="用户名" name="username">
                <t-input v-model="formData.username" size="large" placeholder="请输入用户名" clearable />
              </t-form-item>
              <t-form-item label="密码" name="password">
                <t-input v-model="formData.password" size="large" type="password" placeholder="请输入密码" clearable />
              </t-form-item>
              <t-form-item>
                <t-button block size="large" theme="primary" type="submit" :loading="loading">登录</t-button>
              </t-form-item>
            </t-form>

          </div>
        </div>

        <div class="hd-auth-foot">
          还没有账户？
          <router-link to="/register">立即注册</router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { MessagePlugin } from 'tdesign-vue-next'
import { login } from '@/home/api/account'
import { initAuth } from '@/home/store/auth'

const router = useRouter()
const formRef = ref(null)
const loading = ref(false)
const errorMsg = ref('')

const formData = reactive({
  username: '',
  password: '',
})

const rules = {
  username: [{ required: true, message: '请输入用户名' }],
  password: [{ required: true, message: '请输入密码' }],
}

async function onSubmit({ validateResult }) {
  if (validateResult !== true) return
  loading.value = true
  errorMsg.value = ''
  try {
    const res = await login(formData.username, formData.password)
    if (res.ok) {
      MessagePlugin.success('登录成功')
      await initAuth()
      router.push('/')
    } else {
      errorMsg.value = res.message || '登录失败'
    }
  } finally {
    loading.value = false
  }
}
</script>
