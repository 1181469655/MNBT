<template>
  <div class="hd-page">
    <div class="hd-container">
      <div class="hd-auth-wrap">
        <h1 class="hd-auth-title">注册</h1>
        <p class="hd-auth-sub">创建您的账户，开启主机之旅</p>

        <div class="hd-card">
          <div class="hd-card-body" style="padding:24px 28px;">

            <t-alert v-if="errorMsg" theme="error" :message="errorMsg" style="margin-bottom:16px;" close @close="errorMsg=''" />

            <t-form ref="formRef" :data="formData" :rules="rules" label-align="top" @submit="onSubmit">
              <t-form-item label="用户名" name="username">
                <t-input v-model="formData.username" size="large" placeholder="3~32 位字母、数字或下划线" clearable />
              </t-form-item>
              <t-form-item label="密码" name="password">
                <t-input v-model="formData.password" size="large" type="password" placeholder="至少 6 个字符" clearable />
              </t-form-item>
              <t-form-item label="确认密码" name="password2">
                <t-input v-model="formData.password2" size="large" type="password" placeholder="请再次输入密码" clearable />
              </t-form-item>
              <t-form-item label="邮箱（选填）" name="email">
                <t-input v-model="formData.email" size="large" placeholder="your@email.com" clearable />
              </t-form-item>
              <t-form-item label="QQ（选填）" name="qq">
                <t-input v-model="formData.qq" size="large" placeholder="123456789" clearable />
              </t-form-item>
              <t-form-item>
                <t-button block size="large" theme="primary" type="submit" :loading="loading">注册</t-button>
              </t-form-item>
            </t-form>

          </div>
        </div>

        <div class="hd-auth-foot">
          已有账户？
          <router-link to="/login">立即登录</router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { MessagePlugin } from 'tdesign-vue-next'
import { register } from '@/home/api/account'
import { initAuth } from '@/home/store/auth'
import { accountUrl } from '@/home/utils/account'

const formRef = ref(null)
const loading = ref(false)
const errorMsg = ref('')

const formData = reactive({
  username: '',
  password: '',
  password2: '',
  email: '',
  qq: '',
})

const rules = {
  username: [
    { required: true, message: '请输入用户名' },
    { pattern: /^[a-zA-Z0-9_]{3,32}$/, message: '3~32 位字母、数字或下划线' },
  ],
  password: [
    { required: true, message: '请输入密码' },
    { min: 6, message: '密码至少 6 个字符' },
  ],
  password2: [
    { required: true, message: '请确认密码' },
    {
      validator: (val) => val === formData.password,
      message: '两次输入的密码不一致',
    },
  ],
}

async function onSubmit({ validateResult }) {
  if (validateResult !== true) return
  loading.value = true
  errorMsg.value = ''
  try {
    const res = await register({
      username: formData.username,
      password: formData.password,
      password2: formData.password2,
      email: formData.email,
      qq: formData.qq,
    })
    if (res.ok) {
      MessagePlugin.success('注册成功')
      // 注册成功后自动登录，强制刷新登录态避免并发早退；跳转到用户中心
      await initAuth(true)
      window.location.href = accountUrl('profile')
    } else {
      errorMsg.value = res.message || '注册失败'
    }
  } finally {
    loading.value = false
  }
}
</script>
