<template>
  <div class="td-page">
    <div class="td-page-head">
      <div>
        <h3 class="td-page-title"><i class="mdi mdi-database-sync-outline"></i>SQL 数据备份</h3>
        <p class="td-page-subtitle">备份 / 恢复 / 下载 / 删除数据库</p>
      </div>
    </div>

    <div class="td-table-wrap">
      <div class="td-toolbar">
        <t-button theme="primary" :loading="creating" @click="create">
          <i class="mdi mdi-database-plus-outline"></i> 立即备份
        </t-button>
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
        table-layout="auto"
        stripe
        bordered
      >
        <template #empty>
          <div class="td-empty">
            <i class="mdi mdi-database-search-outline"></i>
            暂无备份记录
          </div>
        </template>
        <template #size="{ row }">{{ fmtSize(row.size || row.dx) }}</template>
        <template #time="{ row }">{{ fmtTime(row.addtime || row.time) }}</template>
        <template #operate="{ row }">
          <div class="td-row-actions">
            <t-button theme="default" variant="outline" size="small" @click="download(row)" title="下载">
              <i class="mdi mdi-download"></i>
            </t-button>
            <t-button theme="warning" variant="outline" size="small" @click="restore(row)" title="恢复">
              <i class="mdi mdi-database-refresh"></i>
            </t-button>
            <t-button theme="danger" variant="outline" size="small" @click="del(row)" title="删除">
              <i class="mdi mdi-delete"></i>
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
import {
  listSqlBackup,
  createBackup,
  downloadBackup,
  restoreBackup,
  deleteBackup,
} from '@/user/api/database'

const loading = ref(false)
const creating = ref(false)
const rows = ref([])
// 数据库 ID 与用户名(由 backup_list 返回,备份/恢复时随请求提交)
const dbId = ref('')
const dbUser = ref('')

const columns = [
  { colKey: 'id', title: 'ID', width: 70 },
  { colKey: 'name', title: '文件名', minWidth: 240, ellipsis: true },
  { colKey: 'size', title: '大小', width: 110 },
  { colKey: 'time', title: '备份时间', width: 160 },
  { colKey: 'operate', title: '操作', width: 170, fixed: 'right' },
]

function fmtTime(v) {
  if (!v) return '-'
  const d = new Date(String(v).replace(/-/g, '/'))
  if (isNaN(d.getTime())) return String(v)
  return d.toLocaleString('zh-CN', { hour12: false })
}

function fmtSize(v) {
  const n = Number(v) || 0
  if (n === 0) return '-'
  const units = ['B', 'KB', 'MB', 'GB', 'TB']
  const pow = Math.min(Math.floor(Math.log(n) / Math.log(1024)), units.length - 1)
  return (n / Math.pow(1024, pow)).toFixed(2) + ' ' + units[pow]
}

function backupFile(row) {
  return row.filename || row.name || ''
}

async function load() {
  loading.value = true
  const r = await listSqlBackup()
  loading.value = false
  if (r.ok && r.data) {
    const d = r.data
    dbId.value = d.db_id || ''
    dbUser.value = d.user || ''
    rows.value = Array.isArray(d) ? d : (d.list || d.rows || d.data || [])
  } else {
    rows.value = []
  }
}

async function create() {
  if (!dbId.value) {
    MessagePlugin.warning('未获取到数据库信息,请刷新页面后重试')
    return
  }
  creating.value = true
  const r = await createBackup(dbId.value)
  creating.value = false
  // 后端 json_exit 失败时 success 仍为 true,需按消息关键字判定真实结果
  if (r.ok && /成功/.test(r.message || '')) {
    MessagePlugin.success('备份成功')
    load()
  } else if (r.ok) {
    MessagePlugin.error(r.message || '备份失败')
  }
}

async function download(row) {
  const file = backupFile(row)
  if (!file) {
    MessagePlugin.warning('该备份缺少文件名,无法下载')
    return
  }
  const r = await downloadBackup(file)
  if (r.ok) {
    const url = r.raw?.url || (r.data && typeof r.data === 'object' ? r.data.url : '')
    if (url) {
      window.open(url, '_blank', 'noopener')
    } else {
      MessagePlugin.warning('未获取到下载链接')
    }
  }
}

function restore(row) {
  const dlg = DialogPlugin.confirm({
    header: '恢复备份',
    body: `确定使用备份「${row.name || row.id}」恢复数据库吗?当前数据将被覆盖且不可恢复。`,
    theme: 'warning',
    confirmBtn: { content: '恢复', theme: 'danger' },
    onConfirm: async () => {
      const r = await restoreBackup(dbUser.value, backupFile(row))
      dlg.destroy()
      if (r.ok && /成功/.test(r.message || '')) {
        MessagePlugin.success('恢复成功')
        load()
      } else if (r.ok) {
        MessagePlugin.error(r.message || '恢复失败')
      }
    },
    onClose: () => dlg.destroy(),
  })
}

function del(row) {
  const dlg = DialogPlugin.confirm({
    header: '删除备份',
    body: `确定删除备份「${row.name || row.id}」吗?此操作不可恢复。`,
    confirmBtn: { content: '删除', theme: 'danger' },
    onConfirm: async () => {
      const r = await deleteBackup(row.id)
      dlg.destroy()
      if (r.ok && /成功/.test(r.message || '')) {
        MessagePlugin.success('删除成功')
        load()
      } else if (r.ok) {
        MessagePlugin.error(r.message || '删除失败')
      }
    },
    onClose: () => dlg.destroy(),
  })
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
