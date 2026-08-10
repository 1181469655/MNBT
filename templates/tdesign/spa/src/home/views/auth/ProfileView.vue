<template>
  <div class="hd-page">
    <div class="hd-container" style="max-width:680px;">
      <div class="hd-page-head">
        <div>
          <h1 class="hd-page-title">个人信息</h1>
          <p class="hd-page-subtitle">管理您的账户资料</p>
        </div>
      </div>

      <t-alert v-if="errorMsg" theme="error" :message="errorMsg" style="margin-bottom:16px;" close @close="errorMsg=''" />

      <div class="hd-card">
        <div class="hd-card-body" style="padding:24px 28px;">
          <t-form ref="formRef" :data="formData" :rules="rules" label-align="top" @submit="onSubmit">
            <t-form-item label="用户名">
              <t-input :value="user.username" size="large" disabled />
              <template #help>用户名不可修改</template>
            </t-form-item>
            <t-form-item label="邮箱" name="email">
              <t-input v-model="formData.email" size="large" placeholder="your@email.com" clearable />
            </t-form-item>
            <t-form-item label="QQ" name="qq">
              <t-input v-model="formData.qq" size="large" placeholder="123456789" clearable />
            </t-form-item>
            <t-form-item label="注册时间">
              <t-input :value="user.created_at || '-'" size="large" disabled />
            </t-form-item>
            <t-form-item>
              <t-button theme="primary" type="submit" :loading="loading">保存</t-button>
              <t-button variant="outline" theme="default" style="margin-left:10px;" @click="$router.push('/password')">修改密码</t-button>
            </t-form-item>
          </t-form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed } from 'vue'
import { MessagePlugin } from 'tdesign-vue-next'
import { updateProfile } from '@/home/api/account'
import { initAuth } from '@/home/store/auth'
import authState from '@/home/store/auth'

const formRef = ref(null)
const loading = ref(false)
const errorMsg = ref('')

const user = computed(() => authState.user || {})

const formData = reactive({
  email: user.value.email || '',
  qq: user.value.qq || '',
})

const rules = {
  email: [
    { validator: (val) => !val || /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val), message: '邮箱格式不正确' },
  ],
  qq: [
    { validator: (val) => !val || /^[0-9]{5,12}$/.test(val), message: 'QQ 号格式不正确' },
  ],
}

async function onSubmit({ validateResult }) {
  if (validateResult !== true) return
  loading.value = true
  errorMsg.value = ''
  try {
    const res = await updateProfile(formData.email, formData.qq)
    if (res.ok) {
      MessagePlugin.success('保存成功')
      await initAuth()
    } else {
      errorMsg.value = res.message || '保存失败'
    }
  } finally {
    loading.value = false
  }
}
</script>
