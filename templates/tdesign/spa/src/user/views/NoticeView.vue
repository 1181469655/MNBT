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
          <t-option value="email" label="邮件" />
          <t-option value="sms" label="短信" />
          <t-option value="system" label="系统" />
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
        <template #time="{ row }">{{ fmtTime(row.time || row.data || row.created_at) }}</template>
        <template #type="{ row }">
          <span :class="typeClass(row.type || row.leixing)">
            {{ typeText(row.type || row.leixing) }}
          </span>
        </template>
        <template #content="{ row }">
          <span class="cell-clip" :title="row.content || row.nr || row.text">{{ row.content || row.nr || row.text || '-' }}</span>
        </template>
        <template #status="{ row }">
          <span :class="statusClass(row.status || row.qk)">
            {{ statusText(row.status || row.qk) }}
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
  const d = new Date(v)
  if (isNaN(d.getTime())) return String(v)
  return d.toLocaleString('zh-CN', { hour12: false })
}

function typeClass(v) {
  if (v === 'email' || v === '邮件' || v === 'mail') return 'td-chip td-chip-info'
  if (v === 'sms' || v === '短信' || v === 'message') return 'td-chip td-chip-warning'
  if (v === 'system' || v === '系统' || v === 'sys') return 'td-chip td-chip-default'
  return 'td-chip td-chip-default'
}

function typeText(v) {
  if (v === 'email' || v === '邮件' || v === 'mail') return '邮件'
  if (v === 'sms' || v === '短信' || v === 'message') return '短信'
  if (v === 'system' || v === '系统' || v === 'sys') return '系统'
  return v || '-'
}

function statusClass(v) {
  if (v === true || v === 'true' || v === 1 || v === '1' || v === 'success' || v === 'ok') return 'td-chip td-chip-success'
  if (v === false || v === 'false' || v === 0 || v === '0' || v === 'fail' || v === 'error') return 'td-chip td-chip-danger'
  if (v === 'pending' || v === 'wait' || v === 'waiting') return 'td-chip td-chip-warning'
  return 'td-chip td-chip-default'
}

function statusText(v) {
  if (v === true || v === 'true' || v === 1 || v === '1' || v === 'success' || v === 'ok') return '已发送'
  if (v === false || v === 'false' || v === 0 || v === '0' || v === 'fail' || v === 'error') return '失败'
  if (v === 'pending' || v === 'wait' || v === 'waiting') return '待发送'
  return v || '-'
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
    let list = Array.isArray(d) ? d : (d.rows || d.list || d.data || [])
    if (filter.type) {
      list = list.filter((row) => typeMatch(row.type || row.leixing, filter.type))
    }
    rows.value = list
    pagination.total = d.total || list.length
  } else {
    rows.value = []
    pagination.total = 0
    if (!r.ok) MessagePlugin.error(r.message || '加载失败')
  }
}

function typeMatch(v, target) {
  if (target === 'email') return v === 'email' || v === '邮件' || v === 'mail'
  if (target === 'sms') return v === 'sms' || v === '短信' || v === 'message'
  if (target === 'system') return v === 'system' || v === '系统' || v === 'sys'
  return false
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
