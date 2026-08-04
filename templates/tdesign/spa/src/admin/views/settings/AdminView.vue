<template>
  <div class="td-page">
    <div class="td-page-head">
      <div>
        <h3 class="td-page-title"><i class="mdi mdi-account-key"></i>管理设置</h3>
        <p class="td-page-subtitle">修改后台登录账号与密码</p>
      </div>
    </div>

    <div class="td-set-card">
      <div class="td-set-card-hd">
        <div class="td-set-icon"><i class="mdi mdi-account-key"></i></div>
        <div>
          <h4>管理账号</h4>
          <p>修改后台登录账号与密码</p>
        </div>
      </div>
      <div class="td-set-card-bd">
        <t-loading :loading="loading" text="保存中…" size="small">
          <div class="td-form">
            <div class="td-form-row">
              <label>原账号 <span class="td-text-danger">*</span></label>
              <t-input v-model="form.ysuser" placeholder="原来的账号" clearable>
                <template #prefix-icon><i class="mdi mdi-account"></i></template>
              </t-input>
            </div>

            <div class="td-form-row">
              <label>原密码 <span class="td-text-danger">*</span></label>
              <t-input
                v-model="form.yspass"
                type="password"
                placeholder="原来的密码"
                clearable
              >
                <template #prefix-icon><i class="mdi mdi-lock"></i></template>
              </t-input>
            </div>

            <div class="td-form-row">
              <label>新账号</label>
              <t-input v-model="form.huser" placeholder="不修改请留空" clearable>
                <template #prefix-icon><i class="mdi mdi-account-plus"></i></template>
              </t-input>
              <div class="td-form-hint">需大于或等于 4 位</div>
            </div>

            <div class="td-form-row">
              <label>新密码</label>
              <t-input
                v-model="form.hpass"
                type="password"
                placeholder="不修改请留空"
                clearable
              >
                <template #prefix-icon><i class="mdi mdi-lock-reset"></i></template>
              </t-input>
              <div class="td-form-hint">需大于或等于 6 位</div>
            </div>

            <div class="td-form-note">
              <b>注意:</b> 原账号和原密码必填;新账号、新密码留空表示不修改。修改成功后下次登录请使用新凭证。
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
import { setAdmin } from '@/admin/api/settings'

const loading = ref(false)

const form = reactive({
  ysuser: '',
  yspass: '',
  huser: '',
  hpass: '',
})

async function save() {
  if (!form.ysuser || !form.yspass) {
    MessagePlugin.warning('原账号和原密码不能为空')
    return
  }
  if (!form.huser && !form.hpass) {
    MessagePlugin.warning('新的账号或密码不能都为空')
    return
  }
  if (form.huser && form.huser.length < 4) {
    MessagePlugin.warning('新账号必须大于或等于 4 位')
    return
  }
  if (form.hpass && form.hpass.length < 6) {
    MessagePlugin.warning('新密码必须大于或等于 6 位')
    return
  }

  loading.value = true
  const r = await setAdmin({
    yuser: form.ysuser,
    ypass: form.yspass,
    xuser: form.huser,
    xpass: form.hpass,
  })
  loading.value = false
  if (r.ok) {
    MessagePlugin.success('保存成功')
    form.ysuser = ''
    form.yspass = ''
    form.huser = ''
    form.hpass = ''
  }
}
</script>
