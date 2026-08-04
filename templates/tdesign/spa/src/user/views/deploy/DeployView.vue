<template>
  <div class="td-page">
    <div class="td-page-head">
      <div>
        <h3 class="td-page-title"><i class="mdi mdi-webpack"></i>一键部署</h3>
        <p class="td-page-subtitle">选择常用程序,一键部署到您的站点</p>
      </div>
      <div class="td-head-actions">
        <t-button theme="default" variant="outline" @click="load">
          <i class="mdi mdi-refresh"></i> 刷新
        </t-button>
      </div>
    </div>

    <t-loading :loading="loading" text="加载中…" size="small">
      <div v-if="rows.length === 0 && !loading" class="td-empty">
        <i class="mdi mdi-webpack"></i>
        暂无可部署程序
      </div>

      <div v-else class="deploy-grid">
        <div v-for="row in rows" :key="row.id" class="deploy-card">
          <div class="deploy-card-head">
            <div class="deploy-thumb" v-if="parseThumb(row.src).length">
              <img
                :src="parseThumb(row.src)[0]"
                :alt="row.name"
                @error="onImgError"
              />
            </div>
            <div class="deploy-thumb deploy-thumb-empty" v-else>
              <i class="mdi mdi-package-variant-closed"></i>
            </div>
            <div class="deploy-name">
              <strong>{{ row.name || '-' }}</strong>
              <span>{{ fmtSize(row.cxdx) }}</span>
            </div>
          </div>
          <div class="deploy-card-body">
            <p class="deploy-desc">{{ row.jc || row.desc || '暂无介绍' }}</p>
            <div class="deploy-meta">
              <span class="td-chip td-chip-info" v-if="row.webkj">
                <i class="mdi mdi-web"></i> {{ row.webkj }}MB
              </span>
              <span class="td-chip td-chip-default" v-if="row.sqlkj">
                <i class="mdi mdi-database"></i> {{ row.sqlkj }}MB
              </span>
              <span class="td-chip td-chip-warning" v-if="row.jg">
                <i class="mdi mdi-currency-cny"></i> {{ row.jg }}
              </span>
            </div>
          </div>
          <div class="deploy-card-foot">
            <t-button
              theme="primary"
              :loading="deploying === row.id"
              :disabled="!!deploying"
              @click="deploy(row)"
            >
              <i class="mdi mdi-rocket-launch"></i> 一键部署
            </t-button>
          </div>
        </div>
      </div>
    </t-loading>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { MessagePlugin, DialogPlugin } from 'tdesign-vue-next'
import { listDeployPrograms, deployProgram } from '@/user/api/deploy'

const loading = ref(false)
const rows = ref([])
const deploying = ref(null)

function parseThumb(src) {
  if (!src) return []
  if (Array.isArray(src)) return src
  try {
    const arr = JSON.parse(src)
    if (Array.isArray(arr)) return arr
    return [src]
  } catch {
    return [src]
  }
}

function fmtSize(v) {
  const n = Number(v) || 0
  if (n === 0) return ''
  if (n < 1024) return n + ' MB'
  return (n / 1024).toFixed(2) + ' GB'
}

function onImgError(e) {
  e.target.style.display = 'none'
}

async function load() {
  loading.value = true
  const r = await listDeployPrograms()
  loading.value = false
  if (r.ok && r.data) {
    const d = r.data
    rows.value = Array.isArray(d) ? d : (d.rows || d.list || d.data || [])
  } else {
    rows.value = []
  }
}

function deploy(row) {
  const dlg = DialogPlugin.confirm({
    header: '一键部署',
    body: `确定将「${row.name}」部署到当前站点吗?部署会覆盖同名文件。`,
    confirmBtn: { content: '开始部署', theme: 'primary' },
    onConfirm: async () => {
      dlg.destroy()
      deploying.value = row.id
      const r = await deployProgram(row.id)
      deploying.value = null
      if (r.ok) {
        const msg = r.message || '部署成功'
        const alert = row.alerts || row.alert || ''
        if (alert) {
          MessagePlugin.success(alert, 5000)
        } else {
          MessagePlugin.success(msg)
        }
      }
    },
    onClose: () => dlg.destroy(),
  })
}

onMounted(load)
</script>

<style scoped>
.td-head-actions {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
}
.deploy-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 16px;
}
.deploy-card {
  background: var(--td-surface);
  border: 1px solid var(--td-border);
  border-radius: var(--td-radius-lg);
  box-shadow: var(--td-shadow-sm);
  display: flex;
  flex-direction: column;
  overflow: hidden;
  transition: transform var(--td-dur) var(--td-ease),
              box-shadow var(--td-dur) var(--td-ease);
  animation: td-fade-in var(--td-dur-lg) var(--td-ease-out);
}
.deploy-card:hover {
  transform: translateY(-3px);
  box-shadow: var(--td-shadow-md);
}
.deploy-card-head {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 14px 16px;
  border-bottom: 1px solid var(--td-border);
}
.deploy-thumb {
  width: 56px;
  height: 56px;
  border-radius: 8px;
  overflow: hidden;
  flex-shrink: 0;
  background: var(--td-bg);
  display: grid;
  place-items: center;
}
.deploy-thumb img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}
.deploy-thumb-empty i {
  font-size: 28px;
  color: var(--td-text-placeholder);
}
.deploy-name {
  flex: 1;
  min-width: 0;
  display: flex;
  flex-direction: column;
}
.deploy-name strong {
  font-size: 14px;
  font-weight: 600;
  color: var(--td-text);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.deploy-name span {
  font-size: 11px;
  color: var(--td-text-placeholder);
  margin-top: 2px;
}
.deploy-card-body {
  padding: 14px 16px;
  flex: 1;
  min-height: 0;
  display: flex;
  flex-direction: column;
  gap: 10px;
}
.deploy-desc {
  margin: 0;
  font-size: 12px;
  color: var(--td-text-secondary);
  line-height: 1.6;
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
.deploy-meta {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
}
.deploy-card-foot {
  padding: 12px 16px;
  border-top: 1px solid var(--td-border);
  background: var(--td-bg);
}
.deploy-card-foot .t-button {
  width: 100%;
}

.td-empty {
  text-align: center;
  padding: 48px 16px;
  color: var(--td-text-placeholder);
  font-size: 13px;
}
.td-empty i {
  font-size: 36px;
  display: block;
  margin-bottom: 8px;
  color: #cbd5e1;
}
</style>
