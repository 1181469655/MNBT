<template>
  <div class="td-page">
    <div class="td-page-head">
      <div>
        <h3 class="td-page-title"><i class="mdi mdi-history"></i>监控日志</h3>
        <p class="td-page-subtitle">查看监控任务的执行记录</p>
      </div>
    </div>

    <div class="td-table-wrap">
      <div class="td-toolbar">
        <t-select v-model="filter.status" style="width: 120px" placeholder="全部状态" clearable @change="onFilterChange">
          <t-option value="success" label="成功" />
          <t-option value="fail" label="失败" />
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
            <i class="mdi mdi-history"></i>
            暂无监控日志
          </div>
        </template>
        <template #time="{ row }">{{ fmtTime(row.time || row.data || row.created_at) }}</template>
        <template #task_name="{ row }">{{ row.task_name || row.name || '-' }}</template>
        <template #status_code="{ row }">
          <span :class="codeClass(row.status_code || row.code)">
            {{ row.status_code || row.code || '-' }}
          </span>
        </template>
        <template #response_time="{ row }">{{ fmtMs(row.response_time || row.rt || row.time_ms) }}</template>
        <template #result="{ row }">
          <span :class="resultClass(row.result || row.qk)">
            {{ resultText(row.result || row.qk) }}
          </span>
        </template>
      </t-table>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { MessagePlugin } from 'tdesign-vue-next'
import { listMonitorLog } from '@/user/api/monitor'

const loading = ref(false)
const rows = ref([])

const pagination = reactive({
  current: 1,
  pageSize: 15,
  total: 0,
  showJumper: true,
})

const filter = reactive({
  status: '',
})

const columns = [
  { colKey: 'id', title: 'ID', width: 70 },
  { colKey: 'time', title: '时间', width: 160 },
  { colKey: 'task_name', title: '任务名', minWidth: 160, ellipsis: true },
  { colKey: 'status_code', title: '状态码', width: 100 },
  { colKey: 'response_time', title: '响应时间', width: 120 },
  { colKey: 'result', title: '结果', width: 100 },
]

function fmtTime(v) {
  if (!v) return '-'
  const d = new Date(v)
  if (isNaN(d.getTime())) return String(v)
  return d.toLocaleString('zh-CN', { hour12: false })
}

function fmtMs(v) {
  const n = Number(v) || 0
  if (!n) return '-'
  if (n < 1000) return n + ' ms'
  return (n / 1000).toFixed(2) + ' s'
}

function codeClass(code) {
  const n = Number(code)
  if (Number.isFinite(n)) {
    if (n >= 200 && n < 400) return 'td-chip td-chip-success'
    if (n >= 400 && n < 500) return 'td-chip td-chip-warning'
    if (n >= 500) return 'td-chip td-chip-danger'
  }
  return 'td-chip td-chip-default'
}

function resultClass(v) {
  if (v === true || v === 'true' || v === 1 || v === '1' || v === 'success') return 'td-chip td-chip-success'
  if (v === false || v === 'false' || v === 0 || v === '0' || v === 'fail' || v === 'error') return 'td-chip td-chip-danger'
  return 'td-chip td-chip-default'
}

function resultText(v) {
  if (v === true || v === 'true' || v === 1 || v === '1' || v === 'success') return '成功'
  if (v === false || v === 'false' || v === 0 || v === '0' || v === 'fail' || v === 'error') return '失败'
  return v || '-'
}

function onFilterChange() {
  pagination.current = 1
  load()
}

async function load() {
  loading.value = true
  const r = await listMonitorLog(pagination.current, pagination.pageSize)
  loading.value = false
  if (r.ok && r.data) {
    const d = r.data
    let list = Array.isArray(d) ? d : (d.rows || d.list || d.data || [])
    if (filter.status === 'success') {
      list = list.filter((row) => isResultSuccess(row.result || row.qk))
    } else if (filter.status === 'fail') {
      list = list.filter((row) => !isResultSuccess(row.result || row.qk))
    }
    rows.value = list
    pagination.total = d.total || list.length
  } else {
    rows.value = []
    pagination.total = 0
    if (!r.ok) MessagePlugin.error(r.message || '加载失败')
  }
}

function isResultSuccess(v) {
  return v === true || v === 'true' || v === 1 || v === '1' || v === 'success'
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
</style>
