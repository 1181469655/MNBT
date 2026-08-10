<template>
  <div class="hd-page">
    <div class="hd-container" style="max-width:540px;">
      <div class="hd-page-head">
        <div>
          <h1 class="hd-page-title">修改密码</h1>
          <p class="hd-page-subtitle">修改后需要重新登录</p>
        </div>
      </div>

      <t-alert v-if="errorMsg" theme="error" :message="errorMsg" style="margin-bottom:16px;" close @close="errorMsg=''" />

      <div class="hd-card">
        <div class="hd-card-body" style="padding:24px 28px;">
          <t-form ref="formRef" :data="formData" :rules="rules" label-align="top" @submit="onSubmit">
            <t-form-item label="原密码" name="old_password">
              <t-input v-model="formData.old_password" size="large" type="password" placeholder="请输入原密码" clearable />
            </t-form-item>
            <t-form-item label="新密码" name="new_password">
              <t-input v-model="formData.new_password" size="large" type="password" placeholder="至少 6 个字符" clearable />
            </t-form-item>
            <t-form-item label="确认新密码" name="new_password2">
              <t-input v-model="formData.new_password2" size="large" type="password" placeholder="请再次输入新密码" clearable />
            </t-form-item>
            <t-form-item>
              <t-button theme="primary" type="submit" :loading="loading">修改密码</t-button>
            </t-form-item>
          </t-form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { MessagePlugin } from 'tdesign-vue-next'
import { changePassword } from '@/home/api/account'
import { resetAuth } from '@/home/store/auth'

const router = useRouter()
const formRef = ref(null)
const loading = ref(false)
const errorMsg = ref('')

const formData = reactive({
  old_password: '',
  new_password: '',
  new_password2: '',
})

const rules = {
  old_password: [{ required: true, message: '请输入原密码' }],
  new_password: [
    { required: true, message: '请输入新密码' },
    { min: 6, message: '新密码至少 6 个字符' },
  ],
  new_password2: [
    { required: true, message: '请确认新密码' },
    {
      validator: (val) => val === formData.new_password,
      message: '两次输入的新密码不一致',
    },
  ],
}

async function onSubmit({ validateResult }) {
  if (validateResult !== true) return
  loading.value = true
  errorMsg.value = ''
  try {
    const res = await changePassword({
      old_password: formData.old_password,
      new_password: formData.new_password,
      new_password2: formData.new_password2,
    })
    if (res.ok) {
      MessagePlugin.success('密码修改成功，请重新登录')
      resetAuth()
      router.push('/login')
    } else {
      errorMsg.value = res.message || '修改失败'
    }
  } finally {
    loading.value = false
  }
}
</script>
