<template>
  <div class="td-page">
    <div class="td-page-head">
      <div>
        <h3 class="td-page-title"><i class="mdi mdi-key-variant"></i>API 设置</h3>
        <p class="td-page-subtitle">接口密钥、建站目录与外部调用开关</p>
      </div>
    </div>

    <div class="td-set-card">
      <div class="td-set-card-hd">
        <div class="td-set-icon"><i class="mdi mdi-key-variant"></i></div>
        <div>
          <h4>API 设置</h4>
          <p>接口密钥、建站目录与外部调用开关</p>
        </div>
      </div>
      <div class="td-set-card-bd">
        <t-loading :loading="loading" text="保存中…" size="small">
          <div class="td-form">
            <div class="td-form-row">
              <label>API 密钥</label>
              <div class="api-key-row">
                <t-input v-model="form.api" placeholder="API密钥(推荐随机生成)" clearable>
                  <template #prefix-icon><i class="mdi mdi-key"></i></template>
                </t-input>
                <t-button theme="default" variant="outline" @click="genApiKey">
                  <i class="mdi mdi-shuffle-variant"></i> 随机生成
                </t-button>
              </div>
              <div class="td-form-hint">用于监控 URL 与外部系统对接,请妥善保管</div>
            </div>

            <div class="td-form-row">
              <label>Linux 建站目录</label>
              <t-input v-model="form.hxi" placeholder="Linux宝塔面板的建站目录">
                <template #prefix-icon><i class="mdi mdi-linux"></i></template>
              </t-input>
              <div class="td-form-hint">默认 /www/wwwroot</div>
            </div>

            <div class="td-form-row">
              <label>Windows 建站目录</label>
              <t-input v-model="form.hxo" placeholder="Windows宝塔面板的建站目录">
                <template #prefix-icon><i class="mdi mdi-microsoft-windows"></i></template>
              </t-input>
              <div class="td-form-hint">默认 D:/wwwroot</div>
            </div>

            <div class="td-form-switch">
              <div class="td-form-switch-txt">
                <strong>API 接口开关</strong>
                <span>关闭后外部系统将无法调用接口</span>
              </div>
              <t-switch v-model="form.apiqk" />
            </div>

            <div class="td-form-note">
              <b>注意:</b> 建站目录请勿随意修改,已开通主机可能受影响。API 密钥修改后,监控 URL 与外部对接均需同步更新。
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
import { setApi } from '@/admin/api/settings'

const boot = window.__TD_BOOT__ || {}
const conf = boot.conf || {}
const loading = ref(false)

const form = reactive({
  api: conf.api || '',
  hxi: conf.hxi || '/www/wwwroot',
  hxo: conf.hxo || 'D:/wwwroot',
  apiqk: conf.apiqk === 'true',
})

function genApiKey() {
  form.api = Math.random().toString(36).slice(2) + Date.now().toString(36)
  MessagePlugin.success('已生成新密钥,请记得保存')
}

async function save() {
  if (!form.api || !form.hxi || !form.hxo) {
    MessagePlugin.warning('请将表单填写完整')
    return
  }
  loading.value = true
  const r = await setApi({
    apikey: form.api,
    linux: form.hxi,
    windows: form.hxo,
    apiqk: form.apiqk ? 'true' : 'false',
  })
  loading.value = false
  if (r.ok) MessagePlugin.success('保存成功')
}
</script>

<style scoped>
.api-key-row {
  display: flex;
  gap: 8px;
  align-items: center;
}
.api-key-row :deep(.t-input) {
  flex: 1;
  min-width: 0;
}
</style>
