<template>
  <div class="td-page td-form-page">
    <div class="td-page-head">
      <div>
        <h3 class="td-page-title"><i class="mdi mdi-view-dashboard-outline"></i>控制面板设置</h3>
        <p class="td-page-subtitle">面板名称、Logo 与开关</p>
      </div>
    </div>

    <div class="td-set-card">
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
              <label>显示版权</label>
              <t-input v-model="form.hxp" placeholder="可以使用HTML标签">
                <template #prefix-icon><i class="mdi mdi-copyright"></i></template>
              </t-input>
              <div class="td-form-hint">例如:Copyright © 梦奈云 2026</div>
            </div>

            <div class="td-form-row">
              <label>站点 Logo</label>
              <div class="logo-grid">
                <div v-for="item in logoFields" :key="item.key" class="logo-cell">
                  <div class="logo-cell-label">{{ item.label }}</div>
                  <div class="logo-preview">
                    <img :src="previews[item.key] || logos[item.key]" :alt="item.label" />
                  </div>
                  <label class="logo-pick">
                    <i class="mdi mdi-file-upload-outline"></i>
                    <span class="logo-pick-name">{{ picks[item.key] ? picks[item.key].name : '选择文件…' }}</span>
                    <input type="file" accept="image/png,image/jpeg" @change="onPick(item, $event)" />
                  </label>
                </div>
              </div>
              <div class="td-form-hint">
                支持 PNG / JPG,单张不超过 {{ maxMb }}MB;未选择文件的那一项沿用原图。上传后请强制刷新(Ctrl+F5)查看新图。
              </div>
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
              <b>说明:</b> 三张图统一存到 <code>imsetes/upload_logo/</code>,由用户端与官网首页按位置读取——浏览器图标用于用户端页头的标签页图标,侧栏图在官网未设置 Logo 时充当首页 Logo。管理端品牌图另有目录,不受这里影响。在线文件管理由系统内置(基于宝塔节点 API)。
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

const logos = boot.panelLogos || {}
const maxMb = boot.panelLogoMaxMb || 2
const logoFields = [
  { key: 'loa', label: '登录页 Logo' },
  { key: 'lob', label: '侧栏 Logo' },
  { key: 'loc', label: '浏览器图标' },
]

const form = reactive({
  name: conf.name || '',
  hxp: conf.hxp || '',
  yzme: conf.yzme === 'true',
  kzmbqk: conf.kzmbqk === 'true',
})

const picks = reactive({ loa: null, lob: null, loc: null })
const previews = reactive({ loa: '', lob: '', loc: '' })

function onPick(item, e) {
  const input = e.target
  const file = input.files && input.files[0]
  if (!file) return
  if (['image/png', 'image/jpeg'].indexOf(file.type) === -1) {
    MessagePlugin.warning(`${item.label}:仅支持 PNG 或 JPG`)
    input.value = ''
    return
  }
  if (file.size > maxMb * 1024 * 1024) {
    MessagePlugin.warning(`${item.label}:不能超过 ${maxMb}MB`)
    input.value = ''
    return
  }
  if (previews[item.key]) URL.revokeObjectURL(previews[item.key])
  picks[item.key] = file
  previews[item.key] = URL.createObjectURL(file)
}

async function save() {
  if (!form.name) {
    MessagePlugin.warning('请填写控制面板名称')
    return
  }
  const fd = new FormData()
  fd.append('name', form.name)
  fd.append('yzm', form.yzme ? 'true' : 'false')
  fd.append('kg', form.kzmbqk ? 'true' : 'false')
  fd.append('bq', form.hxp)
  logoFields.forEach((it) => {
    if (picks[it.key]) fd.append(it.key, picks[it.key])
  })

  loading.value = true
  const r = await setPanel(fd)
  loading.value = false
  if (r.ok) {
    MessagePlugin.success('保存成功')
    // 同步内存中的 boot.conf,否则 SPA 内切页回来仍回显页面加载时的旧值
    const c = window.__TD_BOOT__?.conf || (window.__TD_BOOT__.conf = {})
    c.name = form.name
    c.hxp = form.hxp
    c.yzme = form.yzme ? 'true' : 'false'
    c.kzmbqk = form.kzmbqk ? 'true' : 'false'
  }
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

.logo-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 12px;
}

.logo-cell {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.logo-cell-label {
  font-size: 12px;
  color: var(--td-text-secondary);
}

.logo-preview {
  display: flex;
  align-items: center;
  justify-content: center;
  height: 56px;
  padding: 6px;
  background: var(--td-surface);
  border: 1px solid var(--td-border);
}

.logo-preview img {
  max-width: 100%;
  max-height: 100%;
  object-fit: contain;
}

.logo-pick {
  position: relative;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 7px 14px;
  background: var(--td-surface);
  border: 1px dashed var(--td-border);
  border-radius: var(--td-radius);
  cursor: pointer;
  font-size: 13px;
  color: var(--td-text-secondary);
  transition: border-color 0.15s ease, color 0.15s ease;
}

.logo-pick:hover {
  border-color: var(--td-brand);
  color: var(--td-brand);
}

.logo-pick input[type='file'] {
  position: absolute;
  width: 0;
  height: 0;
  opacity: 0;
  overflow: hidden;
}

.logo-pick-name {
  overflow: hidden;
  white-space: nowrap;
  text-overflow: ellipsis;
}

@media (max-width: 768px) {
  .logo-grid {
    grid-template-columns: 1fr;
  }
}
</style>
