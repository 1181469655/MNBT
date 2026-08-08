<template>
  <div class="dk-volume">
    <t-card>
      <template #header>
        <div class="dk-card-hd">
          <h3>存储卷</h3>
          <t-button theme="default" variant="outline" size="small" @click="load">
            <i class="mdi mdi-refresh"></i> 刷新
          </t-button>
        </div>
      </template>

      <t-table
        row-key="name"
        :data="rows"
        :columns="columns"
        :loading="loading"
        table-layout="auto"
        stripe
        bordered
      >
        <template #name="{ row }">
          <span class="dk-mono">{{ row.Name || row.name || '-' }}</span>
        </template>
        <template #driver="{ row }">
          {{ row.Driver || row.driver || '-' }}
        </template>
        <template #mountpoint="{ row }">
          <span class="dk-mono dk-path">{{ row.Mountpoint || row.mountpoint || '-' }}</span>
        </template>
        <template #created="{ row }">
          <span class="dk-mono">{{ row.CreatedAt || row.created_at || '-' }}</span>
        </template>
        <template #empty>
          <div class="dk-empty">
            <i class="mdi mdi-database"></i>
            <p>暂无存储卷</p>
          </div>
        </template>
      </t-table>
    </t-card>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { listVolume } from '@/docker/api/docker'

const loading = ref(false)
const rows = ref([])

const columns = [
  { colKey: 'name', title: '名称', minWidth: 200 },
  { colKey: 'driver', title: '驱动', width: 120 },
  { colKey: 'mountpoint', title: '挂载点', minWidth: 280 },
  { colKey: 'created', title: '创建时间', minWidth: 180 },
]

async function load() {
  loading.value = true
  const r = await listVolume()
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
.dk-path {
  font-size: 11.5px;
  word-break: break-all;
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

/* ========== 移动端 ========== */
@media (max-width: 768px) {
  .dk-card-hd { flex-direction: column; gap: 10px; align-items: flex-start; }
  .dk-volume :deep(.t-table) { font-size: 12px; }
}
</style>
