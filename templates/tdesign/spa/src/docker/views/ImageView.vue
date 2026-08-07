<template>
  <div class="dk-image">
    <t-card>
      <template #header>
        <div class="dk-card-hd">
          <h3>本地镜像</h3>
          <t-button theme="default" variant="outline" size="small" @click="load">
            <i class="mdi mdi-refresh"></i> 刷新
          </t-button>
        </div>
      </template>

      <t-table
        row-key="id"
        :data="rows"
        :columns="columns"
        :loading="loading"
        table-layout="auto"
        stripe
        bordered
      >
        <template #repo="{ row }">
          <span class="dk-mono">{{ repoOf(row) }}</span>
        </template>
        <template #tag="{ row }">
          <span class="dk-mono">{{ tagOf(row) }}</span>
        </template>
        <template #size="{ row }">
          {{ fmtSize(row.Size ?? row.size) }}
        </template>
        <template #created="{ row }">
          <span class="dk-mono">{{ fmtCreated(createdOf(row)) }}</span>
        </template>
        <template #used="{ row }">
          {{ usedOf(row) }}
        </template>
        <template #id="{ row }">
          <span class="dk-mono dk-id">{{ String(row.Id ?? row.id ?? row.digest ?? '').slice(0, 19) }}</span>
        </template>
        <template #empty>
          <div class="dk-empty">
            <i class="mdi mdi-package-variant"></i>
            <p>暂无本地镜像</p>
          </div>
        </template>
      </t-table>
    </t-card>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { listImage } from '@/docker/api/docker'

const loading = ref(false)
const rows = ref([])

const columns = [
  { colKey: 'repo', title: '镜像', minWidth: 200 },
  { colKey: 'tag', title: '标签', width: 110 },
  { colKey: 'size', title: '大小', width: 110 },
  { colKey: 'created', title: '创建时间', minWidth: 180 },
  { colKey: 'used', title: '使用容器', width: 100 },
  { colKey: 'id', title: 'ID', minWidth: 160 },
]

function tagsOf(row) {
  if (!row) return []
  const raw = row.RepoTags ?? row.repo_tags ?? row.tags ?? row.name
  if (Array.isArray(raw)) return raw.map(String).filter(Boolean)
  if (typeof raw === 'string' && raw.trim()) return [raw.trim()]
  return []
}

function repoOf(row) {
  const tags = tagsOf(row)
  if (tags.length) {
    const first = tags[0]
    const idx = first.lastIndexOf(':')
    return idx > 1 ? first.slice(0, idx) : first
  }
  return String(row.repository || row.repo || row.Repository || '-')
}

function tagOf(row) {
  const tags = tagsOf(row)
  if (tags.length) {
    const first = tags[0]
    const idx = first.lastIndexOf(':')
    return idx > 1 ? first.slice(idx + 1) : (first || '-')
  }
  return String(row.tag || row.Tag || '-')
}

function createdOf(row) {
  return row.Created ?? row.created ?? row.created_at ?? row.create_time ?? row.time ?? ''
}

function usedOf(row) {
  const c = row.containers
  if (Array.isArray(c)) return c.length
  const n = parseInt(row.used, 10)
  return isNaN(n) ? 0 : n
}

function fmtSize(v) {
  const n = parseInt(v, 10) || 0
  if (n < 1048576) return (n / 1024).toFixed(1) + ' KB'
  if (n < 1073741824) return (n / 1048576).toFixed(1) + ' MB'
  return (n / 1073741824).toFixed(2) + ' GB'
}

function fmtCreated(v) {
  if (!v) return '-'
  const n = Number(v)
  if (!isNaN(n) && v !== '') {
    const ms = n < 1e12 ? n * 1000 : n
    const d = new Date(ms)
    if (!isNaN(d.getTime())) return d.toLocaleString('zh-CN', { hour12: false })
  }
  return String(v)
}

async function load() {
  loading.value = true
  const r = await listImage()
  loading.value = false
  if (r.ok && r.data) {
    const btResp = r.data.data || r.data
    const list = btResp.data || btResp || []
    rows.value = Array.isArray(list) ? list : []
  }
}

onMounted(load)
</script>

<style scoped>
.dk-card-hd {
  display: flex;
  align-items: center;
  justify-content: space-between;
  width: 100%;
}
.dk-card-hd h3 {
  margin: 0;
  font-size: 16px;
  font-weight: 600;
}
.dk-mono {
  font-family: Consolas, Monaco, monospace;
  font-size: 12.5px;
}
.dk-id {
  color: var(--td-text-color-secondary, #6b7280);
}
.dk-empty {
  text-align: center;
  padding: 40px 20px;
  color: var(--td-text-color-secondary, #6b7280);
}
.dk-empty i {
  font-size: 36px;
  opacity: 0.5;
  display: block;
  margin-bottom: 8px;
}
.dk-empty p {
  margin: 0;
  font-size: 13px;
}
</style>
