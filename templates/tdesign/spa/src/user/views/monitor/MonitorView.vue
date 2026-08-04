<template>
  <div class="td-page">
    <div class="td-page-head">
      <div>
        <h3 class="td-page-title"><i class="mdi mdi-radar"></i>监控任务</h3>
        <p class="td-page-subtitle">管理监控任务,查看可用性指标</p>
      </div>
    </div>

    <div class="td-table-wrap">
      <div class="td-toolbar">
        <t-button theme="primary" @click="openAdd">
          <i class="mdi mdi-plus"></i> 新增任务
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
            <i class="mdi mdi-radar"></i>
            暂无监控任务
          </div>
        </template>
        <template #status="{ row }">
          <span :class="statusClass(row.status || row.qk)">
            {{ statusText(row.status || row.qk) }}
          </span>
        </template>
        <template #operate="{ row }">
          <div class="td-row-actions">
            <t-button theme="default" variant="outline" size="small" @click="openEdit(row)" title="编辑">
              <i class="mdi mdi-pencil"></i>
            </t-button>
            <t-button theme="danger" variant="outline" size="small" @click="del(row)" title="删除">
              <i class="mdi mdi-delete"></i>
            </t-button>
          </div>
        </template>
      </t-table>
    </div>

    <!-- 添加 / 编辑弹窗 -->
    <t-dialog
      v-model:visible="dialogVisible"
      :header="editForm.id ? '编辑任务' : '新增任务'"
      :on-confirm="onSave"
      width="520px"
      :confirm-btn="{ loading: saving }"
    >
      <div class="td-form">
        <div class="td-form-row">
          <label>任务名称</label>
          <t-input v-model="editForm.name" placeholder="请输入任务名称" clearable />
        </div>
        <div class="td-form-row">
          <label>监控 URL</label>
          <t-input v-model="editForm.url" placeholder="https://example.com/health" clearable />
          <div class="td-form-hint">监控目标地址,需带 http:// 或 https://</div>
        </div>
        <div class="td-form-row">
          <label>监控频率(秒)</label>
          <t-input-number v-model="editForm.interval" :min="10" :step="10" theme="normal" />
        </div>
        <div class="td-form-switch">
          <div class="td-form-switch-txt">
            <strong>启用监控</strong>
            <span>关闭后将不再检查该任务</span>
          </div>
          <t-switch v-model="editForm.kg" />
        </div>
      </div>
    </t-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { MessagePlugin, DialogPlugin } from 'tdesign-vue-next'
import {
  listMonitorTasks,
  saveMonitorTask,
  deleteMonitorTask,
} from '@/user/api/monitor'

const loading = ref(false)
const saving = ref(false)
const rows = ref([])

const dialogVisible = ref(false)

const editForm = reactive({
  id: null,
  name: '',
  url: '',
  interval: 60,
  kg: true,
})

const columns = [
  { colKey: 'id', title: 'ID', width: 70 },
  { colKey: 'name', title: '任务名称', minWidth: 160, ellipsis: true },
  { colKey: 'url', title: 'URL', minWidth: 240, ellipsis: true },
  { colKey: 'status', title: '状态', width: 100 },
  { colKey: 'operate', title: '操作', width: 130, fixed: 'right' },
]

function statusClass(v) {
  if (v === true || v === 'true' || v === 1 || v === '1' || v === 'on') return 'td-chip td-chip-success'
  if (v === false || v === 'false' || v === 0 || v === '0' || v === 'off') return 'td-chip td-chip-danger'
  return 'td-chip td-chip-default'
}

function statusText(v) {
  if (v === true || v === 'true' || v === 1 || v === '1' || v === 'on') return '运行中'
  if (v === false || v === 'false' || v === 0 || v === '0' || v === 'off') return '已停止'
  return v || '-'
}

async function load() {
  loading.value = true
  const r = await listMonitorTasks()
  loading.value = false
  if (r.ok && r.data) {
    const d = r.data
    rows.value = Array.isArray(d) ? d : (d.rows || d.list || d.data || [])
  } else {
    rows.value = []
  }
}

function openAdd() {
  editForm.id = null
  editForm.name = ''
  editForm.url = ''
  editForm.interval = 60
  editForm.kg = true
  dialogVisible.value = true
}

function openEdit(row) {
  editForm.id = row.id
  editForm.name = row.name || ''
  editForm.url = row.url || ''
  editForm.interval = Number(row.interval) || 60
  editForm.kg = row.status === true || row.status === 'true' || row.status === 1 ||
    row.status === '1' || row.qk === true || row.qk === 'true' || row.qk === 1 || row.qk === '1' || row.kg === true
  dialogVisible.value = true
}

async function onSave() {
  if (!editForm.name) {
    MessagePlugin.warning('请填写任务名称')
    return
  }
  if (!editForm.url) {
    MessagePlugin.warning('请填写监控 URL')
    return
  }
  saving.value = true
  const payload = {
    name: editForm.name,
    url: editForm.url,
    interval: String(editForm.interval),
    kg: editForm.kg ? 'true' : 'false',
  }
  const r = editForm.id
    ? await saveMonitorTask({ id: editForm.id, ...payload })
    : await saveMonitorTask(payload)
  saving.value = false
  if (r.ok) {
    MessagePlugin.success(editForm.id ? '保存成功' : '添加成功')
    dialogVisible.value = false
    load()
  }
}

function del(row) {
  const dlg = DialogPlugin.confirm({
    header: '删除任务',
    body: `确定删除任务「${row.name || row.id}」吗?`,
    theme: 'warning',
    confirmBtn: { content: '删除', theme: 'danger' },
    onConfirm: async () => {
      const r = await deleteMonitorTask(row.id)
      dlg.destroy()
      if (r.ok) {
        MessagePlugin.success('删除成功')
        load()
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
