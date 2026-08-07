<template>
  <div class="td-page">
    <div class="td-page-head">
      <div>
        <h3 class="td-page-title"><i class="mdi mdi-timer-sand"></i>监控设置</h3>
        <p class="td-page-subtitle">域名 / 文件监控到期后的处理策略</p>
      </div>
    </div>

    <div class="td-set-card">
      <div class="td-set-card-hd">
        <div class="td-set-icon"><i class="mdi mdi-timer-sand"></i></div>
        <div>
          <h4>自动处理主机</h4>
          <p>域名 / 文件监控到期后的删除或暂停策略</p>
        </div>
      </div>
      <div class="td-set-card-bd">
        <t-loading :loading="loading" text="保存中…" size="small">
          <div class="td-form">
            <h5 class="td-form-section-title">
              <i class="mdi mdi-domain"></i> 域名监控
            </h5>

            <div class="td-form-switch">
              <div class="td-form-switch-txt">
                <strong>删除/处理开关</strong>
                <span>达到阈值后按下方策略处理主机</span>
              </div>
              <t-switch v-model="form.ymjkkg" />
            </div>

            <div class="td-form-switch">
              <div class="td-form-switch-txt">
                <strong>邮件通知</strong>
                <span>处理前发送邮件提醒</span>
              </div>
              <t-switch v-model="form.mtyxfskg" />
            </div>

            <div class="td-form-row">
              <label>域名删除天数阈值</label>
              <t-input-number
                v-model="form.ymjktsyz"
                :min="1"
                :step="1"
                theme="normal"
                placeholder="请输入天数"
              />
              <div class="td-form-hint">超过该阈值未处理的域名将按策略执行</div>
            </div>

            <h5 class="td-form-section-title td-mt-16">
              <i class="mdi mdi-file-document-outline"></i> 文件监控
            </h5>

            <div class="td-form-switch">
              <div class="td-form-switch-txt">
                <strong>删除/处理开关</strong>
                <span>达到阈值后按下方策略处理主机</span>
              </div>
              <t-switch v-model="form.wjjkkg" />
            </div>

            <div class="td-form-switch">
              <div class="td-form-switch-txt">
                <strong>邮件通知</strong>
                <span>处理前发送邮件提醒</span>
              </div>
              <t-switch v-model="form.mtwjfskg" />
            </div>

            <div class="td-form-row">
              <label>文件删除天数阈值</label>
              <t-input-number
                v-model="form.wjjktsyz"
                :min="1"
                :step="1"
                theme="normal"
                placeholder="请输入天数"
              />
              <div class="td-form-hint">超过该阈值未处理的文件将按策略执行</div>
            </div>

            <div class="td-form-row">
              <label>处理方式</label>
              <t-select v-model="form.optionzc">
                <t-option value="del" label="删除主机" />
                <t-option value="stop" label="暂停主机" />
              </t-select>
              <div class="td-form-hint">删除主机不可恢复,建议优先使用暂停</div>
            </div>

            <div class="td-form-note">
              <b>注意:</b> 开启处理开关后按天数阈值执行;仅通知可只开邮件、关闭处理开关。
              天数请勿填 0 或负数。执行前一天会发送邮件提醒。
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
import { setMonitor } from '@/admin/api/settings'

const boot = window.__TD_BOOT__ || {}
const conf = boot.conf || {}
const loading = ref(false)

function num(v, def = 1) {
  const n = parseInt(v, 10)
  return Number.isFinite(n) && n > 0 ? n : def
}

const form = reactive({
  ymjkkg: conf.ymjkkg === 'true',
  mtyxfskg: conf.mtyxfskg === 'true',
  ymjktsyz: num(conf.ymjktsyz, 1),
  wjjkkg: conf.wjjkkg === 'true',
  mtwjfskg: conf.mtwjfskg === 'true',
  wjjktsyz: num(conf.wjjktsyz, 1),
  optionzc: conf.optionzc === 'del' || conf.optionzc === 'stop' ? conf.optionzc : 'stop',
})

async function save() {
  if (!form.ymjktsyz || form.ymjktsyz <= 0 || !form.wjjktsyz || form.wjjktsyz <= 0) {
    MessagePlugin.warning('天数阈值必须大于 0')
    return
  }
  loading.value = true
  const r = await setMonitor({
    ymkg: form.ymjkkg ? 'true' : 'false',
    ymyjkg: form.mtyxfskg ? 'true' : 'false',
    ymtsyz: String(form.ymjktsyz),
    wjkg: form.wjjkkg ? 'true' : 'false',
    wjyjkg: form.mtwjfskg ? 'true' : 'false',
    wjtsyz: String(form.wjjktsyz),
    option: form.optionzc,
  })
  loading.value = false
  if (r.ok) MessagePlugin.success('保存成功')
}
</script>

<style scoped>
.td-form-section-title {
  margin: 0 0 12px;
  padding: 6px 10px;
  font-size: 13px;
  font-weight: 600;
  color: var(--td-text);
  background: var(--td-brand-light);
  border-radius: var(--td-radius);
  display: flex;
  align-items: center;
  gap: 6px;
}
.td-form-section-title i {
  color: var(--td-brand);
  font-size: 16px;
}
.td-form-section-title.td-mt-16 {
  margin-top: 16px;
}
</style>
