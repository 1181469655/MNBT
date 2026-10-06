<template>
  <div class="td-page">
    <div class="td-page-head">
      <div>
        <h3 class="td-page-title"><i class="mdi mdi-bell-outline"></i>通知日志</h3>
        <p class="td-page-subtitle">查看系统发送的通知记录</p>
      </div>
    </div>

    <div class="td-table-wrap">
      <div class="td-toolbar">
        <t-select v-model="filter.type" style="width: 140px" placeholder="全部类型" clearable @change="onFilterChange">
          <t-option value="monitor" label="监控告警" />
          <t-option value="expire" label="主机到期" />
          <t-option value="traffic" label="流量提醒" />
        </t-select>
        <div class="td-toolbar-spacer"></div>
        <t-button theme="default" variant="text" @click="load">
          <i class="mdi mdi-refresh"></i> 刷新
        </t-button>
      </div>

      <t-table
        row-key="id"
        :data="rows"
        :columns="columns"
        :loading="loading"
        :pagination="pagination"
        table-layout="auto"
        stripe
        bordered
        @page-change="onPageChange"
      >
        <template #empty>
          <div class="td-empty">
            <i class="mdi mdi-bell-off-outline"></i>
            暂无通知日志
          </div>
        </template>
        <template #time="{ row }">{{ fmtTime(row.created_at) }}</template>
        <template #type="{ row }">
          <span :class="typeClass(row.type)">
            {{ typeText(row.type) }}
          </span>
        </template>
        <template #content="{ row }">
          <span class="cell-clip" :title="row.content">{{ row.content || '-' }}</span>
        </template>
        <template #status="{ row }">
          <span :class="statusClass(row.is_read)">
            {{ statusText(row.is_read) }}
          </span>
        </template>
      </t-table>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { MessagePlugin } from 'tdesign-vue-next'
import { listNoticeLog } from '@/user/api/monitor'

const loading = ref(false)
const rows = ref([])

const pagination = reactive({
  current: 1,
  pageSize: 15,
  total: 0,
  showJumper: true,
})

const filter = reactive({
  type: '',
})

const columns = [
  { colKey: 'id', title: 'ID', width: 70 },
  { colKey: 'time', title: '时间', width: 160 },
  { colKey: 'type', title: '类型', width: 100 },
  { colKey: 'content', title: '内容', minWidth: 280, ellipsis: true },
  { colKey: 'status', title: '状态', width: 100 },
]

function fmtTime(v) {
  if (!v) return '-'
  const d = new Date(String(v).replace(/-/g, '/'))
  if (isNaN(d.getTime())) return String(v)
  return d.toLocaleString('zh-CN', { hour12: false })
}

// 后端 type 值域:monitor / expire / traffic
function typeClass(v) {
  if (v === 'monitor') return 'td-chip td-chip-warning'
  if (v === 'expire') return 'td-chip td-chip-danger'
  if (v === 'traffic') return 'td-chip td-chip-info'
  return 'td-chip td-chip-default'
}

function typeText(v) {
  if (v === 'monitor') return '监控告警'
  if (v === 'expire') return '主机到期'
  if (v === 'traffic') return '流量提醒'
  return v || '-'
}

// 状态列展示已读/未读(MN_notice_log.is_read)
function statusClass(v) {
  if (v === true || v === 'true' || v === 1 || v === '1') return 'td-chip td-chip-default'
  return 'td-chip td-chip-warning'
}

function statusText(v) {
  if (v === true || v === 'true' || v === 1 || v === '1') return '已读'
  return '未读'
}

function onFilterChange() {
  pagination.current = 1
  load()
}

async function load() {
  loading.value = true
  const r = await listNoticeLog(pagination.current, pagination.pageSize)
  loading.value = false
  if (r.ok && r.data) {
    const d = r.data
    let list = Array.isArray(d) ? d : (d.logs || d.rows || d.list || [])
    if (filter.type) {
      list = list.filter((row) => row.type === filter.type)
    }
    rows.value = list
    pagination.total = d.total || list.length
  } else {
    rows.value = []
    pagination.total = 0
    if (!r.ok) MessagePlugin.error(r.message || '加载失败')
  }
}

function onPageChange(p) {
  pagination.current = p.current
  pagination.pageSize = p.pageSize
  load()
}

onMounted(load)
</script>

<style scoped>
.td-empty {
  text-align: center;
  padding: 36px 16px;
  color: var(--td-text-placeholder);
  font-size: 13px;
}
.td-empty i {
  font-size: 36px;
  display: block;
  margin-bottom: 8px;
  color: #cbd5e1;
}
.cell-clip {
  display: inline-block;
  max-width: 100%;
  color: var(--td-text);
  font-size: 12px;
  line-height: 1.5;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
</style>
