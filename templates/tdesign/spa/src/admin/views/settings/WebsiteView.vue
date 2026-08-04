<template>
  <div class="td-page">
    <div class="td-page-head">
      <div>
        <h3 class="td-page-title"><i class="mdi mdi-earth"></i>网站设置</h3>
        <p class="td-page-subtitle">公告、联系方式与登录安全</p>
      </div>
    </div>

    <div class="td-set-card">
      <div class="td-set-card-hd">
        <div class="td-set-icon"><i class="mdi mdi-earth"></i></div>
        <div>
          <h4>网站配置</h4>
          <p>公告、联系方式与登录安全</p>
        </div>
      </div>
      <div class="td-set-card-bd">
        <t-loading :loading="loading" text="保存中…" size="small">
          <div class="td-form">
            <div class="td-form-row">
              <label>网站公告</label>
              <t-textarea
                v-model="form.gg"
                :autosize="{ minRows: 4, maxRows: 10 }"
                placeholder="请在这填写网站公告"
              />
              <div class="td-form-hint">显示在用户端首页公告区域,支持纯文本</div>
            </div>

            <div class="td-form-row">
              <label>站长 QQ</label>
              <t-input v-model="form.qqh" placeholder="请在这填写您的QQ号" clearable>
                <template #prefix-icon><i class="mdi mdi-account"></i></template>
              </t-input>
            </div>

            <div class="td-form-switch">
              <div class="td-form-switch-txt">
                <strong>后台登录验证码</strong>
                <span>开启后管理员登录需填写验证码</span>
              </div>
              <t-switch v-model="form.yzm" />
            </div>

            <div class="td-form-switch">
              <div class="td-form-switch-txt">
                <strong>主机邮箱绑定</strong>
                <span>要求用户绑定邮箱后方可使用部分功能</span>
              </div>
              <t-switch v-model="form.zjyxbd" />
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
import { setWebsite } from '@/admin/api/settings'

const boot = window.__TD_BOOT__ || {}
const conf = boot.conf || {}
const loading = ref(false)

const form = reactive({
  gg: conf.gg || '',
  qqh: conf.qqh || '',
  yzm: conf.yzm === 'true',
  zjyxbd: conf.zjyxbd === 'true',
})

async function save() {
  loading.value = true
  const r = await setWebsite({
    gg: form.gg,
    qq: form.qqh,
    yzm: form.yzm ? 'true' : 'false',
    zjyx: form.zjyxbd ? 'true' : 'false',
  })
  loading.value = false
  if (r.ok) MessagePlugin.success('保存成功')
}
</script>
