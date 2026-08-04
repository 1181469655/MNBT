<template>
  <div class="td-page">
    <div class="td-page-head">
      <div>
        <h3 class="td-page-title"><i class="mdi mdi-email-outline"></i>邮箱设置</h3>
        <p class="td-page-subtitle">SMTP 发信参数,用于监控通知与系统邮件</p>
      </div>
    </div>

    <div class="td-set-card">
      <div class="td-set-card-hd">
        <div class="td-set-icon"><i class="mdi mdi-email-outline"></i></div>
        <div>
          <h4>邮箱配置</h4>
          <p>SMTP 发信参数</p>
        </div>
      </div>
      <div class="td-set-card-bd">
        <t-loading :loading="loading" text="保存中…" size="small">
          <div class="td-form">
            <div class="td-form-row">
              <label>SMTP 服务器</label>
              <t-input v-model="form.mailhost" placeholder="请输入邮箱服务器地址" clearable>
                <template #prefix-icon><i class="mdi mdi-server-network"></i></template>
              </t-input>
              <div class="td-form-hint">例如 smtp.qq.com / smtp.163.com / smtp.gmail.com</div>
            </div>

            <div class="td-form-row">
              <label>邮箱账号</label>
              <t-input v-model="form.mailuser" placeholder="请输入邮箱账号" clearable>
                <template #prefix-icon><i class="mdi mdi-email"></i></template>
              </t-input>
            </div>

            <div class="td-form-row">
              <label>邮箱密码 / 授权码</label>
              <t-input
                v-model="form.mailpassword"
                placeholder="请输入邮箱密码或授权码"
                clearable
              >
                <template #prefix-icon><i class="mdi mdi-lock"></i></template>
              </t-input>
              <div class="td-form-hint">部分邮箱服务商需使用授权码而非登录密码</div>
            </div>

            <div class="td-form-row">
              <label>端口</label>
              <t-input v-model="form.mailport" placeholder="请输入邮箱端口">
                <template #prefix-icon><i class="mdi mdi-ethernet-cable"></i></template>
              </t-input>
              <div class="td-form-hint">常见: SSL 465 / TLS 587 / 明文 25</div>
            </div>

            <div class="td-form-note">
              <b>提示:</b> 邮箱配置保存后将用于域名/文件监控通知、密码找回等场景。请确认邮箱已开启 SMTP 服务。
            </div>

            <div class="td-form-actions">
              <t-button theme="primary" :loading="loading" @click="save">
                <i class="mdi mdi-content-save-outline"></i> 保存修改
              </t-button>
            </div>
          </div>
        </t-loading>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue'
import { MessagePlugin } from 'tdesign-vue-next'
import { setMail } from '@/admin/api/settings'

const boot = window.__TD_BOOT__ || {}
const conf = boot.conf || {}
const loading = ref(false)

const form = reactive({
  mailhost: conf.mailhost || '',
  mailuser: conf.mailuser || '',
  mailpassword: conf.mailpassword || '',
  mailport: conf.mailport || '',
})

async function save() {
  if (!form.mailhost || !form.mailuser || !form.mailpassword || !form.mailport) {
    MessagePlugin.warning('请填写完整的邮箱配置')
    return
  }
  loading.value = true
  const r = await setMail({
    host: form.mailhost,
    user: form.mailuser,
    password: form.mailpassword,
    port: form.mailport,
  })
  loading.value = false
  if (r.ok) MessagePlugin.success('保存成功')
}
</script>
