<template>
  <div class="td-page">
    <div class="td-page-head">
      <div>
        <h3 class="td-page-title"><i class="mdi mdi-view-dashboard-outline"></i>控制面板设置</h3>
        <p class="td-page-subtitle">面板名称、FTP 模块、版权与开关</p>
      </div>
    </div>

    <div class="td-set-card">
      <div class="td-set-card-hd">
        <div class="td-set-icon"><i class="mdi mdi-view-dashboard-outline"></i></div>
        <div>
          <h4>控制面板</h4>
          <p>名称、FTP 面板、Logo 与开关</p>
        </div>
      </div>
      <div class="td-set-card-bd">
        <t-loading :loading="loading" text="保存中…" size="small">
          <div class="td-form">
            <div class="td-form-row">
              <label>控制面板名称 <span class="td-text-danger">*</span></label>
              <t-input v-model="form.name" placeholder="请在这填写控制面板的名称" clearable>
                <template #prefix-icon><i class="mdi mdi-rename-box"></i></template>
              </t-input>
            </div>

            <div class="td-form-row">
              <label>FTP 操作面板</label>
              <t-select v-model="form.hxw">
                <t-option value="amftp" label="AMFTP 操作面板" />
                <t-option value="mnftp" label="MN 操作面板(推荐)" />
              </t-select>
              <div class="td-form-hint">AMFTP 仅支持本机宝塔;MN 面板支持本地与远程</div>
            </div>

            <div class="td-form-row">
              <label>显示版权</label>
              <t-input v-model="form.hxp" placeholder="可以使用HTML标签">
                <template #prefix-icon><i class="mdi mdi-copyright"></i></template>
              </t-input>
              <div class="td-form-hint">例如:Copyright © 梦奈云 2026</div>
            </div>

            <div class="td-form-switch">
              <div class="td-form-switch-txt">
                <strong>用户登录验证码</strong>
                <span>控制面板登录是否需要验证码</span>
              </div>
              <t-switch v-model="form.yzme" />
            </div>

            <div class="td-form-switch">
              <div class="td-form-switch-txt">
                <strong>控制面板开关</strong>
                <span>关闭后用户无法进入控制面板</span>
              </div>
              <t-switch v-model="form.kzmbqk" />
            </div>

            <div class="td-form-note">
              <b>Logo 上传:</b> 登录页 / 侧栏 / 用户头像 Logo 上传请使用 <code>default</code> 主题的
              <a href="./set.php?gn=kzmb">控制面板设置</a> 页面。本页仅保存核心字段。
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
import { setPanel } from '@/admin/api/settings'

const boot = window.__TD_BOOT__ || {}
const conf = boot.conf || {}
const loading = ref(false)

const form = reactive({
  name: conf.name || '',
  hxw: conf.hxw || 'amftp',
  hxp: conf.hxp || '',
  yzme: conf.yzme === 'true',
  kzmbqk: conf.kzmbqk === 'true',
})

async function save() {
  if (!form.name) {
    MessagePlugin.warning('请填写控制面板名称')
    return
  }
  loading.value = true
  const r = await setPanel({
    name: form.name,
    ftp: form.hxw,
    yzm: form.yzme ? 'true' : 'false',
    kg: form.kzmbqk ? 'true' : 'false',
    bq: form.hxp,
  })
  loading.value = false
  if (r.ok) MessagePlugin.success('保存成功')
}
</script>

<style scoped>
code {
  background: #f3f3f3;
  padding: 1px 6px;
  border-radius: 4px;
  font-family: Consolas, Monaco, monospace;
  font-size: 12px;
  color: #d63384;
}
</style>
