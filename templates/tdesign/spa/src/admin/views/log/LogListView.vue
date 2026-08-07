<template>
  <div class="td-page">
    <div class="td-page-head">
      <div>
        <h3 class="td-page-title"><i class="mdi mdi-file-document-outline"></i>操作日志</h3>
        <p class="td-page-subtitle">查看、检索与清理系统操作日志</p>
      </div>
    </div>

    <div class="td-table-wrap">
      <div class="td-toolbar">
        <t-select v-model="search.field" class="td-search-field">
          <t-option value="czuser" label="操作用户" />
          <t-option value="lx" label="操作类型" />
          <t-option value="lr" label="操作内容" />
          <t-option value="qk" label="操作情况" />
        </t-select>
        <t-select v-model="search.mode" class="td-search-mode">
          <t-option value="like" label="模糊匹配" />
          <t-option value="eq" label="精确匹配" />
        </t-select>
        <t-input
          v-model="search.kw"
          placeholder="输入关键字搜索"
          clearable
          @enter="doSearch"
          class="td-search-kw"
        />
        <t-button theme="primary" @click="doSearch">
          <i class="mdi mdi-magnify"></i> 搜索
        </t-button>
        <t-button theme="default" variant="outline" @click="resetSearch">
          <i class="mdi mdi-refresh"></i> 重置
        </t-button>
        <div class="td-toolbar-spacer"></div>
        <t-button theme="danger" variant="outline" @click="clearAll">
          <i class="mdi mdi-broom"></i> 清空日志
        </t-button>
      </div>

      <t-table
        row-key="id"
        :data="rows"
        :columns="columns"
        :loading="loading"
        :pagination="pagination"
        stripe
        bordered
        @page-change="onPageChange"
      >
        <template #lr="{ row }">
          <span class="cell-lr" :title="row.lr">{{ row.lr }}</span>
        </template>

        <template #qk="{ row }">
          <span class="td-chip td-chip-success" v-if="isSuccess(row.qk)">
            <i class="mdi mdi-check-circle"></i> {{ row.qk }}
          </span>
          <span class="td-chip td-chip-danger" v-else-if="isFail(row.qk)">
            <i class="mdi mdi-alert-circle"></i> {{ row.qk }}
          </span>
          <span class="td-chip td-chip-default" v-else>{{ row.qk || '-' }}</span>
        </template>

        <template #op="{ row }">
          <div class="td-row-actions">
            <t-button theme="danger" variant="text" size="small" @click="deleteRow(row)">
              <i class="mdi mdi-delete"></i> 删除
            </t-button>
          </div>
        </template>
      </t-table>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { MessagePlugin, DialogPlugin } from 'tdesign-vue-next'
import { listLog, deleteLog, clearLog } from '@/admin/api/log'

const loading = ref(false)
const rows = ref([])

const pagination = reactive({
  current: 1,
  pageSize: 10,
  total: 0,
  showJumper: true,
})

const search = reactive({
  field: 'czuser',
  mode: 'like',
  kw: '',
})

const columns = [
  { colKey: 'id', title: 'ID', width: 70, sorter: true },
  { colKey: 'czuser', title: '操作用户', width: 130, sorter: true },
  { colKey: 'lx', title: '操作类型', width: 130, sorter: true },
  { colKey: 'lr', title: '操作内容', minWidth: 260, col: 'lr' },
  { colKey: 'qk', title: '操作情况', width: 130, sorter: true },
  { colKey: 'ip', title: 'IP地址', width: 140 },
  { colKey: 'date', title: '操作时间', width: 170, sorter: true },
  { colKey: 'op', title: '操作', width: 90, fixed: 'right' },
]

function isSuccess(qk) {
  if (qk == null) return false
  const s = String(qk)
  return s === '成功' || s === '1' || s.includes('成功')
}

function isFail(qk) {
  if (qk == null) return false
  const s = String(qk)
  return s === '失败' || s === '0' || s.includes('失败') || s.includes('错误')
}

function buildWhere() {
  if (!search.kw) return null
  const w = {}
  if (search.mode === 'eq') {
    w[search.field] = search.kw
  } else {
    w[search.field] = { like: search.kw }
  }
  return w
}

async function load() {
  loading.value = true
  const params = {
    page: pagination.current,
    limit: pagination.pageSize,
  }
  const where = buildWhere()
  if (where) params.where = where
  const r = await listLog(params)
  loading.value = false
  if (r.ok && r.data) {
    rows.value = r.data.rows || []
    pagination.total = r.data.total || 0
  }
}

function onPageChange(p) {
  pagination.current = p.current
  pagination.pageSize = p.pageSize
  load()
}

function doSearch() {
  pagination.current = 1
  load()
}

function resetSearch() {
  search.kw = ''
  search.field = 'czuser'
  search.mode = 'like'
  pagination.current = 1
  load()
}

function deleteRow(row) {
  const dialog = DialogPlugin.confirm({
    header: '删除日志',
    body: `确定删除日志 #${row.id} 吗?`,
    confirmBtn: { content: '删除', theme: 'danger' },
    onConfirm: async () => {
      const r = await deleteLog(row.id)
      dialog.destroy()
      if (r.ok) {
        MessagePlugin.success('删除成功')
        load()
      }
    },
    onClose: () => dialog.destroy(),
  })
}

function clearAll() {
  const dialog = DialogPlugin.confirm({
    header: '清空日志',
    body: '将删除全部操作日志,此操作不可恢复。确定继续吗?',
    confirmBtn: { content: '清空全部', theme: 'danger' },
    onConfirm: async () => {
      const r = await clearLog()
      dialog.destroy()
      if (r.ok) {
        MessagePlugin.success('已清空')
        pagination.current = 1
        load()
      }
    },
    onClose: () => dialog.destroy(),
  })
}

onMounted(load)
</script>

<style scoped>
.td-search-field {
  min-width: 130px !important;
  width: 130px !important;
}
.td-search-mode {
  min-width: 120px !important;
  width: 120px !important;
}
.td-search-kw {
  min-width: 220px !important;
  width: 220px !important;
}
.cell-lr {
  display: inline-block;
  max-width: 100%;
  word-break: break-all;
  font-size: 12px;
  line-height: 1.5;
  color: var(--td-text);
}
</style>
