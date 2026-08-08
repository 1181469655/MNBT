<template>
  <div class="dk-compose">
    <t-card>
      <template #header>
        <div class="dk-card-hd">
          <h3>Compose 模板</h3>
          <t-button theme="default" variant="outline" size="small" @click="load">
            <i class="mdi mdi-refresh"></i> 刷新
          </t-button>
        </div>
      </template>

      <t-table
        row-key="name"
        :data="templates"
        :columns="tplColumns"
        :loading="loading"
        table-layout="auto"
        stripe
        bordered
      >
        <template #empty>
          <div class="dk-empty">
            <i class="mdi mdi-file-document"></i>
            <p>暂无 Compose 模板</p>
          </div>
        </template>
      </t-table>
    </t-card>

    <t-card style="margin-top: 16px">
      <template #header>
        <div class="dk-card-hd">
          <h3>Docker 项目</h3>
        </div>
      </template>

      <t-table
        row-key="name"
        :data="projects"
        :columns="projColumns"
        :loading="loading"
        table-layout="auto"
        stripe
        bordered
      >
        <template #empty>
          <div class="dk-empty">
            <i class="mdi mdi-docker"></i>
            <p>暂无 Docker 项目</p>
          </div>
        </template>
      </t-table>
    </t-card>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { listCompose } from '@/docker/api/docker'

const loading = ref(false)
const templates = ref([])
const projects = ref([])

const tplColumns = [
  { colKey: 'name', title: '模板名称', minWidth: 160 },
  { colKey: 'desc', title: '描述', minWidth: 260, ellipsis: true },
  { colKey: 'category', title: '分类', width: 120 },
]

const projColumns = [
  { colKey: 'name', title: '项目名', minWidth: 180 },
  { colKey: 'status', title: '状态', width: 110 },
  { colKey: 'count', title: '容器数', width: 90, align: 'center' },
  { colKey: 'updated', title: '更新时间', minWidth: 180 },
]

function mapRows(list) {
  if (!Array.isArray(list)) return []
  return list.map((t) => ({
    name: t.name || t.title || '-',
    desc: t.desc || t.description || '-',
    category: t.category || t.type || '-',
  }))
}

async function load() {
  loading.value = true
  const r = await listCompose()
  loading.value = false
  if (r.ok && r.data) {
    const btTpl = r.data.templates
    const tplList = btTpl && btTpl.data ? btTpl.data : btTpl || []
    templates.value = mapRows(tplList)

    const btProj = r.data.projects
    const projList = btProj && btProj.data ? btProj.data : btProj || []
    projects.value = Array.isArray(projList) ? projList.map((p) => ({
      name: p.name || '-',
      status: p.status || '-',
      count: p.container_count ?? p.count ?? '-',
      updated: p.updated_at || p.time || '-',
    })) : []
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
  .dk-compose :deep(.t-table) { font-size: 12px; }
}
</style>
