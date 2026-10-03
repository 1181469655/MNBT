<template>
  <div class="td-page td-form-narrow">
    <div class="td-card">
      <div class="td-card-head">个人信息</div>
      <div class="td-card-body">
        <p class="form-intro">管理你的账号资料,带 <span class="req">*</span> 为必填项。</p>

        <t-form ref="formRef" :data="form" :rules="rules" label-width="90px" @submit="onSubmit">
          <t-form-item label="用户名">
            <t-input :value="user.username" disabled />
          </t-form-item>
          <t-form-item label="邮箱" name="email">
            <t-input v-model="form.email" placeholder="请输入邮箱(选填)" clearable />
          </t-form-item>
          <t-form-item label="QQ" name="qq">
            <t-input v-model="form.qq" placeholder="请输入QQ号(选填)" clearable maxlength="12" />
          </t-form-item>
          <t-form-item label="注册时间">
            <t-input :value="user.created_at || '—'" disabled />
          </t-form-item>
          <t-form-item>
            <t-button theme="primary" type="submit" :loading="loading">保存修改</t-button>
          </t-form-item>
        </t-form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue'
import { MessagePlugin } from 'tdesign-vue-next'
import { updateProfile } from '@/account/api/auth'

const boot = window.__TD_BOOT__ || {}
const user = boot.accountUser || { username: '', email: '', qq: '', created_at: '' }

const formRef = ref()
const loading = ref(false)

const form = reactive({
  email: user.email || '',
  qq: user.qq || '',
})

const rules = {
  email: [
    {
      validator: (val) => val === '' || /^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(val),
      message: '邮箱格式不正确',
      trigger: 'blur',
    },
  ],
  qq: [
    {
      validator: (val) => val === '' || /^[0-9]{5,12}$/.test(val),
      message: 'QQ号格式不正确',
      trigger: 'blur',
    },
  ],
}

async function onSubmit({ validateResult }) {
  if (validateResult !== true) return
  loading.value = true
  const res = await updateProfile({ email: form.email.trim(), qq: form.qq.trim() })
  loading.value = false
  if (res.ok) {
    MessagePlugin.success('保存成功')
    boot.accountUser.email = form.email.trim()
    boot.accountUser.qq = form.qq.trim()
    return
  }
}
</script>

<style scoped>
.form-intro {
  margin: 0 0 16px;
  font-size: 13px;
  color: var(--td-text-secondary);
}
.req {
  color: var(--td-error);
}
</style>
