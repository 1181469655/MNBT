<template>
  <div class="td-page td-form-narrow">
    <div class="td-card">
      <div class="td-card-head">修改密码</div>
      <div class="td-card-body">
        <div class="td-form-note">
          <b>安全提示:</b> 修改成功后旧密码立即失效,下次登录请使用新密码。
        </div>

        <t-form ref="formRef" :data="form" :rules="rules" label-width="90px" @submit="onSubmit">
          <t-form-item label="原密码" name="old_password">
            <t-input v-model="form.old_password" type="password" placeholder="请输入原密码" clearable />
          </t-form-item>
          <t-form-item label="新密码" name="new_password">
            <t-input v-model="form.new_password" type="password" placeholder="至少6个字符" clearable />
          </t-form-item>
          <t-form-item label="确认新密码" name="new_password2">
            <t-input v-model="form.new_password2" type="password" placeholder="请再次输入新密码" clearable />
          </t-form-item>
          <t-form-item>
            <t-button theme="primary" type="submit" :loading="loading">确认修改</t-button>
          </t-form-item>
        </t-form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue'
import { MessagePlugin } from 'tdesign-vue-next'
import { changePassword } from '@/account/api/auth'

const formRef = ref()
const loading = ref(false)

const form = reactive({
  old_password: '',
  new_password: '',
  new_password2: '',
})

const rules = {
  old_password: [{ required: true, message: '请输入原密码', trigger: 'blur' }],
  new_password: [
    { required: true, message: '请输入新密码', trigger: 'blur' },
    { min: 6, message: '新密码至少6个字符', trigger: 'blur' },
    {
      validator: (val) => val !== form.old_password,
      message: '新密码不能与原密码相同',
      trigger: 'blur',
    },
  ],
  new_password2: [
    { required: true, message: '请再次输入新密码', trigger: 'blur' },
    {
      validator: (val) => val === form.new_password,
      message: '两次输入的新密码不一致',
      trigger: 'blur',
    },
  ],
}

async function onSubmit({ validateResult }) {
  if (validateResult !== true) return
  loading.value = true
  const res = await changePassword(form)
  loading.value = false
  if (res.ok) {
    MessagePlugin.success('密码修改成功')
    form.old_password = ''
    form.new_password = ''
    form.new_password2 = ''
    formRef.value?.clearValidate()
    return
  }
}
</script>

<style scoped>
.td-form-note {
  margin-bottom: 20px;
}
</style>
