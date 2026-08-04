<template>
  <div class="td-page">
    <div class="td-page-head">
      <div>
        <h3 class="td-page-title"><i class="mdi mdi-webpack"></i>程序列表</h3>
        <p class="td-page-subtitle">管理一键部署程序包,支持新增 / 导入 / 导出</p>
      </div>
    </div>

    <div class="td-table-wrap">
      <div class="td-toolbar">
        <t-button theme="primary" @click="goAdd">
          <i class="mdi mdi-plus"></i> 新增程序
        </t-button>
        <t-button theme="default" variant="outline" @click="goImport">
          <i class="mdi mdi-upload"></i> 导入程序
        </t-button>
        <t-button theme="default" variant="outline" :disabled="!selectedIds.length" @click="exportSelected">
          <i class="mdi mdi-download"></i> 导出选中
        </t-button>
        <t-button theme="danger" variant="outline" :disabled="!selectedIds.length" @click="batchDelete">
          <i class="mdi mdi-delete"></i> 删除选中
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
        :pagination="pagination"
        stripe
        bordered
        @page-change="onPageChange"
        @select-change="onSelectChange"
      >
        <template #jc="{ row }">
          <span class="cell-clip" :title="row.jc">{{ slice(row.jc, 80) }}</span>
        </template>

        <template #sxpz="{ row }">
          <span>{{ parseSxpz(row.sxpz)[0] }}MB</span>
        </template>

        <template #sqlkj="{ row }">
          <span>{{ parseSxpz(row.sxpz)[1] }}MB</span>
        </template>

        <template #tj="{ row }">
          <span>{{ deployCount(row.tj) }}次</span>
        </template>

        <template #src="{ row }">
          <div class="thumb-row" v-if="parseSrc(row.src).length">
            <img
              v-for="(u, i) in parseSrc(row.src).slice(0, 3)"
              :key="i"
              :src="u"
              class="thumb"
              alt="预览"
              @error="onImgError"
            />
          </div>
          <span v-else class="td-text-mute td-text-xs">无</span>
        </template>

        <template #qk="{ row }">
          <span class="td-chip td-chip-success" v-if="row.qk === true || row.qk === 'true'">
            <i class="mdi mdi-check-circle"></i> 上架
          </span>
          <span class="td-chip td-chip-danger" v-else>
            <i class="mdi mdi-close-circle"></i> 下架
          </span>
        </template>

        <template #op="{ row }">
          <div class="td-row-actions">
            <t-button theme="primary" variant="text" size="small" @click="openEdit(row)">
              <i class="mdi mdi-pencil"></i> 编辑
            </t-button>
            <t-button theme="default" variant="text" size="small" @click="exportRow(row)">
              <i class="mdi mdi-download"></i> 导出
            </t-button>
            <t-button theme="default" variant="text" size="small" @click="downloadSource(row)">
              <i class="mdi mdi-package-down"></i> 源码
            </t-button>
            <t-button theme="danger" variant="text" size="small" @click="deleteRow(row)">
              <i class="mdi mdi-delete"></i> 删除
            </t-button>
          </div>
        </template>
      </t-table>
    </div>

    <!-- 编辑弹窗 -->
    <t-dialog
      v-model:visible="editVisible"
      header="编辑程序"
      :on-confirm="submitEdit"
      :confirm-btn="{ content: '保存', loading: editLoading }"
      width="560px"
    >
      <div class="td-form">
        <div class="td-form-row">
          <label>程序名称</label>
          <t-input v-model="editForm.cxname" placeholder="请输入程序名称" />
        </div>
        <div class="td-form-row">
          <label>程序介绍</label>
          <t-textarea v-model="editForm.cxjc" :autosize="{ minRows: 3, maxRows: 6 }" placeholder="请输入程序介绍" />
        </div>
        <div class="td-form-row">
          <label>网页空间 (MB)</label>
          <t-input-number v-model="editForm.webkj" :min="0" theme="normal" />
        </div>
        <div class="td-form-row">
          <label>数据库空间 (MB)</label>
          <t-input-number v-model="editForm.sqlkj" :min="0" theme="normal" />
        </div>
        <div class="td-form-row">
          <label>价格 (元)</label>
          <t-input-number v-model="editForm.cxrmb" :min="0" theme="normal" />
        </div>
        <div class="td-form-row">
          <label>部署完成提示</label>
          <t-textarea v-model="editForm.alerts" :autosize="{ minRows: 2, maxRows: 4 }" placeholder="部署完成后给用户的提示" />
        </div>
        <div class="td-form-switch">
          <div class="td-form-switch-txt">
            <strong>是否上架</strong>
            <span>关闭后用户端将不可见</span>
          </div>
          <t-switch v-model="editForm.cxkg" />
        </div>
      </div>
    </t-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { MessagePlugin, DialogPlugin } from 'tdesign-vue-next'
import {
  listProgram,
  updateProgram,
  deleteProgram,
  deleteProgramBatch,
  exportProgram,
} from '@/admin/api/program'

const router = useRouter()

const loading = ref(false)
const rows = ref([])
const selectedIds = ref([])

const pagination = reactive({
  current: 1,
  pageSize: 10,
  total: 0,
  showJumper: true,
})

const columns = [
  { colKey: 'row-select', type: 'multiple', width: 50 },
  { colKey: 'id', title: 'ID', width: 70, sorter: true },
  { colKey: 'name', title: '程序名称', minWidth: 140, ellipsis: true },
  { colKey: 'jc', title: '程序介绍', minWidth: 180, col: 'jc' },
  { colKey: 'date', title: '添加时间', width: 160, sorter: true },
  { colKey: 'cxdx', title: '大小', width: 90, sorter: true, cell: (h, { row }) => `${row.cxdx || 0}MB` },
  { colKey: 'sxpz', title: '网页空间', width: 100 },
  { colKey: 'sqlkj', title: '数据库空间', width: 110 },
  { colKey: 'tj', title: '部署次数', width: 90, sorter: true },
  { colKey: 'jg', title: '价格', width: 90, sorter: true, cell: (h, { row }) => `${row.jg || 0}元` },
  { colKey: 'src', title: '展示图', width: 130 },
  { colKey: 'qk', title: '状态', width: 90 },
  { colKey: 'op', title: '操作', width: 220, fixed: 'right' },
]

function slice(str, n) {
  if (!str) return ''
  return str.length > n ? str.slice(0, n) + '…' : str
}

function parseSxpz(sxpz) {
  try {
    const arr = JSON.parse(sxpz)
    if (Array.isArray(arr)) return arr
  } catch (_) {
    /* ignore */
  }
  return [0, 0]
}

function parseSrc(src) {
  try {
    const arr = JSON.parse(src)
    if (Array.isArray(arr)) return arr
  } catch (_) {
    /* ignore */
  }
  return []
}

function deployCount(tj) {
  try {
    const obj = JSON.parse(tj)
    if (obj && typeof obj === 'object') return Object.keys(obj).length
  } catch (_) {
    /* ignore */
  }
  return 0
}

function onImgError(e) {
  e.target.style.display = 'none'
}

async function load() {
  loading.value = true
  const r = await listProgram({ page: pagination.current, limit: pagination.pageSize })
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

function onSelectChange(selectedRowKeys) {
  selectedIds.value = selectedRowKeys || []
}

function goAdd() {
  router.push('/program/add')
}

function goImport() {
  router.push('/program/import')
}

async function exportSelected() {
  if (!selectedIds.value.length) return
  const r = await exportProgram(selectedIds.value.join(','))
  if (r.ok) MessagePlugin.success('导出成功')
}

async function exportRow(row) {
  const r = await exportProgram(row.id)
  if (r.ok) MessagePlugin.success('导出成功')
}

function downloadSource(row) {
  window.open('./wjxz.php?idh=' + encodeURIComponent(row.id), '_blank')
}

function batchDelete() {
  if (!selectedIds.value.length) return
  const dialog = DialogPlugin.confirm({
    header: '批量删除',
    body: `确定删除选中的 ${selectedIds.value.length} 项程序吗?此操作不可恢复。`,
    confirmBtn: { content: '删除', theme: 'danger' },
    onConfirm: async () => {
      const r = await deleteProgramBatch(selectedIds.value)
      dialog.destroy()
      if (r.ok) {
        MessagePlugin.success('删除成功')
        selectedIds.value = []
        load()
      }
    },
    onClose: () => dialog.destroy(),
  })
}

function deleteRow(row) {
  const dialog = DialogPlugin.confirm({
    header: '删除程序',
    body: `确定删除程序「${row.name}」吗?此操作不可恢复。`,
    confirmBtn: { content: '删除', theme: 'danger' },
    onConfirm: async () => {
      const r = await deleteProgram(row.id)
      dialog.destroy()
      if (r.ok) {
        MessagePlugin.success('删除成功')
        load()
      }
    },
    onClose: () => dialog.destroy(),
  })
}

const editVisible = ref(false)
const editLoading = ref(false)
const editForm = reactive({
  id: '',
  cxname: '',
  cxjc: '',
  webkj: 0,
  sqlkj: 0,
  cxrmb: 0,
  alerts: '',
  cxkg: true,
})

function openEdit(row) {
  const sxpz = parseSxpz(row.sxpz)
  editForm.id = row.id
  editForm.cxname = row.name || ''
  editForm.cxjc = row.jc || ''
  editForm.webkj = Number(sxpz[0]) || 0
  editForm.sqlkj = Number(sxpz[1]) || 0
  editForm.cxrmb = Number(row.jg) || 0
  editForm.alerts = row.alet || ''
  editForm.cxkg = row.qk === true || row.qk === 'true'
  editVisible.value = true
}

async function submitEdit() {
  if (!editForm.cxname) {
    MessagePlugin.warning('请填写程序名称')
    return
  }
  editLoading.value = true
  const r = await updateProgram({
    id: editForm.id,
    cxname: editForm.cxname,
    cxjc: editForm.cxjc,
    webkj: String(editForm.webkj),
    sqlkj: String(editForm.sqlkj),
    cxrmb: String(editForm.cxrmb),
    alerts: editForm.alerts,
    cxkg: editForm.cxkg ? 'true' : 'false',
  })
  editLoading.value = false
  if (r.ok) {
    MessagePlugin.success('保存成功')
    editVisible.value = false
    load()
  }
}

onMounted(load)
</script>

<style scoped>
.cell-clip {
  display: inline-block;
  max-width: 100%;
  color: var(--td-text-secondary);
  font-size: 12px;
  line-height: 1.5;
}
.thumb-row {
  display: inline-flex;
  gap: 4px;
  align-items: center;
}
.thumb {
  width: 36px;
  height: 36px;
  object-fit: cover;
  border-radius: 4px;
  border: 1px solid var(--td-border);
  background: var(--td-bg);
}
</style>
