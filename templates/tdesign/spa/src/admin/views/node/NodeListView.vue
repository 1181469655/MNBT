<template>
  <div class="td-page">
    <div class="td-page-head">
      <div>
        <h3 class="td-page-title"><i class="mdi mdi-router-wireless"></i>节点列表</h3>
        <p class="td-page-subtitle">管理分布式节点与违禁词扫描</p>
      </div>
    </div>

    <div class="td-table-wrap">
      <div class="td-toolbar">
        <t-input
          v-model="keyword"
          placeholder="搜索节点名称"
          clearable
          style="width: 200px"
          @change="applyFilter"
        >
          <template #prefix-icon><i class="mdi mdi-magnify"></i></template>
        </t-input>
        <t-select v-model="statusFilter" style="width: 130px" @change="applyFilter">
          <t-option value="" label="全部" />
          <t-option value="enabled" label="已启用" />
          <t-option value="disabled" label="已停用" />
        </t-select>
        <t-button theme="default" variant="outline" @click="load">
          <i class="mdi mdi-refresh"></i> 刷新
        </t-button>
        <div class="td-toolbar-spacer"></div>
        <t-button theme="primary" @click="openAdd">
          <i class="mdi mdi-plus"></i> 新增节点
        </t-button>
      </div>

      <t-table
        row-key="id"
        :data="filteredRows"
        :columns="columns"
        :loading="loading"
        :pagination="pagination"
        table-layout="auto"
        stripe
        bordered
        @page-change="onPageChange"
      >
        <template #status="{ row }">
          <span :class="row.status === 'enabled' ? 'td-chip td-chip-success' : 'td-chip td-chip-danger'">
            {{ row.status === 'enabled' ? '已启用' : '已停用' }}
          </span>
        </template>
        <template #created_at="{ row }">{{ fmtTime(row.created_at) }}</template>
        <template #last_ping="{ row }">{{ fmtTime(row.last_ping) }}</template>
        <template #operate="{ row }">
          <div class="td-row-actions">
            <t-button
              v-if="row.status === 'enabled'"
              theme="default"
              variant="outline"
              size="small"
              @click="toggleStatus(row, false)"
              title="停用"
            >
              <i class="mdi mdi-pause"></i>
            </t-button>
            <t-button
              v-else
              theme="default"
              variant="outline"
              size="small"
              @click="toggleStatus(row, true)"
              title="启用"
            >
              <i class="mdi mdi-play"></i>
            </t-button>
            <t-button
              theme="default"
              variant="outline"
              size="small"
              :loading="pingLoading[row.id]"
              @click="ping(row)"
              title="Ping 测试"
            >
              <i class="mdi mdi-pulse"></i>
            </t-button>
            <t-button
              theme="default"
              variant="outline"
              size="small"
              @click="viewConfig(row)"
              title="查看配置"
            >
              <i class="mdi mdi-file-document-outline"></i>
            </t-button>
            <t-button
              theme="danger"
              variant="outline"
              size="small"
              @click="del(row)"
              title="删除"
            >
              <i class="mdi mdi-delete"></i>
            </t-button>
          </div>
        </template>
      </t-table>
    </div>

    <t-dialog
      v-model:visible="addVisible"
      header="新增节点"
      :on-confirm="onAdd"
      width="500px"
      :confirm-btn="{ loading: saving }"
    >
      <div class="td-form">
        <div class="td-form-row">
          <label>节点名称 <span class="td-text-danger">*</span></label>
          <t-input v-model="addForm.name" />
        </div>
        <div class="td-form-row">
          <label>所属宝塔</label>
          <t-select v-model="addForm.bt_id" :loading="btLoading" clearable placeholder="可选">
            <t-option
              v-for="b in baotaList"
              :key="b.id"
              :value="b.id"
              :label="`${b.btdh} (${b.btip})`"
            />
          </t-select>
        </div>
        <div class="td-form-row">
          <label>Base URL</label>
          <t-input v-model="addForm.base_url" placeholder="如 https://node.example.com" />
        </div>
        <div class="td-form-row">
          <label>API Key</label>
          <t-input v-model="addForm.api_key" />
        </div>
      </div>
    </t-dialog>

    <t-dialog v-model:visible="cfgVisible" header="节点配置" width="600px">
      <t-loading :loading="cfgLoading" text="加载中…">
        <pre v-if="cfgText" class="td-cfg-pre">{{ cfgText }}</pre>
        <div v-else class="td-empty">
          <i class="mdi mdi-information"></i>暂无配置
        </div>
      </t-loading>
      <template #footer>
        <t-button theme="default" @click="cfgVisible = false">关闭</t-button>
      </template>
    </t-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { MessagePlugin, DialogPlugin } from 'tdesign-vue-next'
import { listNode, addNode, deleteNode, setNodeStatus, nodeConfig, nodePing } from '@/admin/api/node'
import { listBaota } from '@/admin/api/baota'

const loading = ref(false)
const saving = ref(false)
const rows = ref([])
const keyword = ref('')
const statusFilter = ref('')
const pingLoading = reactive({})
const pagination = reactive({ current: 1, pageSize: 10, total: 0, showJumper: true })

const addVisible = ref(false)
const addForm = reactive({ name: '', bt_id: '', base_url: '', api_key: '' })
const btLoading = ref(false)
const baotaList = ref([])

const cfgVisible = ref(false)
const cfgLoading = ref(false)
const cfgText = ref('')

const columns = [
  { colKey: 'id', title: 'ID', width: 70, sorter: true },
  { colKey: 'name', title: '节点名称', width: 160, ellipsis: true },
  { colKey: 'btdh', title: '所属宝塔', width: 140, ellipsis: true },
  { colKey: 'btip', title: '节点IP', width: 140 },
  { colKey: 'status', title: '状态', width: 100 },
  { colKey: 'created_at', title: '创建时间', width: 160 },
  { colKey: 'last_ping', title: '最后 ping', width: 160 },
  { colKey: 'version', title: '版本', width: 100 },
  { colKey: 'operate', title: '操作', width: 200, fixed: 'right' },
]

const filteredRows = computed(() => {
  let arr = rows.value
  if (keyword.value) {
    const k = keyword.value.toLowerCase()
    arr = arr.filter((r) => (r.name || '').toLowerCase().includes(k))
  }
  if (statusFilter.value) {
    arr = arr.filter((r) => r.status === statusFilter.value)
  }
  return arr
})

function fmtTime(v) {
  if (!v) return '-'
  if (typeof v === 'number') {
    if (v < 1e12) v *= 1000
    return new Date(v).toLocaleString('zh-CN', { hour12: false })
  }
  const d = new Date(v)
  if (isNaN(d.getTime())) return String(v)
  return d.toLocaleString('zh-CN', { hour12: false })
}

async function load() {
  loading.value = true
  try {
    const r = await listNode({ page: 1, limit: 500 })
    if (r.ok && r.data) {
      if (Array.isArray(r.data)) {
        rows.value = r.data
        pagination.total = r.data.length
      } else if (Array.isArray(r.data.rows)) {
        rows.value = r.data.rows
        pagination.total = r.data.total || r.data.rows.length
      } else {
        rows.value = []
        pagination.total = 0
      }
    } else {
      rows.value = []
      pagination.total = 0
      if (!r.ok) MessagePlugin.error(r.message || '加载失败')
    }
  } catch (e) {
    rows.value = []
    pagination.total = 0
    MessagePlugin.error('加载节点失败')
  } finally {
    loading.value = false
  }
}

function applyFilter() {
  pagination.current = 1
}
function onPageChange(p) {
  pagination.current = p.current
  pagination.pageSize = p.pageSize
}

async function loadBaota() {
  btLoading.value = true
  const r = await listBaota({ page: 1, limit: 200 })
  btLoading.value = false
  if (r.ok && r.data) {
    baotaList.value = r.data.rows || []
  }
}

function openAdd() {
  addForm.name = ''
  addForm.bt_id = ''
  addForm.base_url = ''
  addForm.api_key = ''
  addVisible.value = true
  if (!baotaList.value.length) loadBaota()
}

async function onAdd() {
  if (!addForm.name) { MessagePlugin.warning('请输入节点名称'); return }
  saving.value = true
  const r = await addNode({
    name: addForm.name,
    bt_id: addForm.bt_id,
    base_url: addForm.base_url,
    api_key: addForm.api_key,
  })
  saving.value = false
  if (r.ok) {
    MessagePlugin.success('添加成功')
    addVisible.value = false
    load()
  } else {
    MessagePlugin.error(r.message || '添加失败')
  }
}

async function toggleStatus(row, enabled) {
  const r = await setNodeStatus(row.id, enabled)
  if (r.ok) {
    MessagePlugin.success(enabled ? '已启用' : '已停用')
    load()
  } else {
    MessagePlugin.error(r.message || '操作失败')
  }
}

async function ping(row) {
  pingLoading[row.id] = true
  MessagePlugin.info(`正在 ping ${row.name || row.id}…`)
  const r = await nodePing(row.id)
  pingLoading[row.id] = false
  if (r.ok) {
    MessagePlugin.success('Ping 任务已下发')
    setTimeout(load, 1500)
  } else {
    MessagePlugin.error(r.message || 'Ping 失败')
  }
}

async function viewConfig(row) {
  cfgVisible.value = true
  cfgLoading.value = true
  cfgText.value = ''
  const r = await nodeConfig(row.id)
  cfgLoading.value = false
  if (r.ok && r.data != null) {
    if (typeof r.data === 'string') cfgText.value = r.data
    else cfgText.value = JSON.stringify(r.data, null, 2)
  } else {
    cfgText.value = r.message || '加载失败'
  }
}

function del(row) {
  const dlg = DialogPlugin.confirm({
    header: '删除节点',
    body: `确定删除节点 #${row.id}(${row.name || ''})吗?`,
    theme: 'warning',
    onConfirm: async () => {
      const r = await deleteNode(row.id)
      if (r.ok) {
        MessagePlugin.success('删除成功')
        load()
      } else {
        MessagePlugin.error(r.message || '删除失败')
      }
      dlg.destroy()
    },
  })
}

onMounted(load)
</script>

<style scoped>
.td-cfg-pre {
  background: #f7f8fa;
  border: 1px solid var(--td-border);
  border-radius: var(--td-radius);
  padding: 12px;
  font-family: Consolas, Monaco, monospace;
  font-size: 12px;
  line-height: 1.6;
  color: var(--td-text);
  white-space: pre-wrap;
  word-break: break-all;
  max-height: 400px;
  overflow: auto;
  margin: 0;
}
</style>
