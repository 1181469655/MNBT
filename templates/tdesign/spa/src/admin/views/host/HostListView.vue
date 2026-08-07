<template>
  <div class="td-page">
    <div class="td-page-head">
      <div>
        <h3 class="td-page-title"><i class="mdi mdi-server"></i>主机列表</h3>
        <p class="td-page-subtitle">管理虚拟主机与 FTP/SQL 账号</p>
      </div>
    </div>

    <div class="td-table-wrap">
      <div class="td-toolbar">
        <t-select v-model="search.field" style="width: 130px">
          <t-option value="ssbt" label="所属宝塔" />
          <t-option value="sqldz" label="网站名" />
          <t-option value="user" label="账号" />
        </t-select>
        <t-select v-model="search.type" style="width: 100px">
          <t-option value="like" label="模糊" />
          <t-option value="eq" label="精确" />
        </t-select>
        <t-input
          v-model="search.value"
          placeholder="输入关键字"
          clearable
          style="width: 200px"
          @enter="onSearch"
        />
        <t-button theme="primary" @click="onSearch"><i class="mdi mdi-magnify"></i> 搜索</t-button>
        <t-button theme="default" variant="outline" @click="onReset">重置</t-button>
        <div class="td-toolbar-spacer"></div>
        <t-button theme="primary" @click="$router.push('/host/add')">
          <i class="mdi mdi-plus"></i> 新增主机
        </t-button>
        <t-button
          theme="danger"
          variant="outline"
          :disabled="!selectedIds.length"
          @click="batchDelete"
        >
          <i class="mdi mdi-delete"></i> 删除选中
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
        @select-change="onSelectChange"
      >
        <template #userpass="{ row }">
          <div class="td-cell-line">{{ row.user ?? '-' }}</div>
          <div class="td-cell-line td-text-mute">
            <span>{{ showPass[row.id] ? (row.pass ?? '-') : '********' }}</span>
            <t-link theme="primary" @click="togglePass(row.id)" style="margin-left: 4px">
              <i class="mdi" :class="showPass[row.id] ? 'mdi-eye-off' : 'mdi-eye'"></i>
            </t-link>
          </div>
        </template>
        <template #sqluserpass="{ row }">
          <div class="td-cell-line">{{ row.sqluser ?? '-' }}</div>
          <div class="td-cell-line td-text-mute">
            <span>{{ showSqlPass[row.id] ? (row.sqlpass ?? '-') : '********' }}</span>
            <t-link theme="primary" @click="toggleSqlPass(row.id)" style="margin-left: 4px">
              <i class="mdi" :class="showSqlPass[row.id] ? 'mdi-eye-off' : 'mdi-eye'"></i>
            </t-link>
          </div>
        </template>
        <template #hxa="{ row }">
          <span :class="spaceOver(row.hxa) ? 'td-text-danger' : ''">{{ formatSpace(row.hxa) }}</span>
        </template>
        <template #hxb="{ row }">
          <span :class="spaceOver(row.hxb) ? 'td-text-danger' : ''">{{ formatSpace(row.hxb) }}</span>
        </template>
        <template #llmax="{ row }">
          <span :class="spaceOver(row.llmax) ? 'td-text-danger' : ''">{{ formatFlow(row.llmax) }}</span>
        </template>
        <template #data="{ row }">{{ fmtTime(row.data) }}</template>
        <template #datae="{ row }">
          <span :class="isExpired(row.datae) ? 'td-text-danger' : ''">{{ row.datae || '永久' }}</span>
        </template>
        <template #qk="{ row }">
          <div class="td-status-cell">
            <span :class="isOff(row.qk) ? 'td-chip td-chip-danger' : 'td-chip td-chip-success'">
              {{ isOff(row.qk) ? '被关闭' : '正常' }}
            </span>
            <div class="td-status-tags">
              <span v-if="spaceOver(row.hxa)" class="td-chip td-chip-danger">网页超</span>
              <span v-if="spaceOver(row.hxb)" class="td-chip td-chip-danger">数据库超</span>
              <span v-if="spaceOver(row.llmax)" class="td-chip td-chip-danger">流量超</span>
              <span v-if="isExpired(row.datae)" class="td-chip td-chip-danger">已到期</span>
            </div>
          </div>
        </template>
        <template #operate="{ row }">
          <div class="td-row-actions">
            <t-button theme="default" variant="outline" size="small" @click="edit(row)" title="编辑">
              <i class="mdi mdi-pencil"></i>
            </t-button>
            <t-button theme="default" variant="outline" size="small" @click="loginPanel(row)" title="登录控制面板">
              <i class="mdi mdi-login"></i>
            </t-button>
            <t-button theme="danger" variant="outline" size="small" @click="del(row)" title="删除">
              <i class="mdi mdi-delete"></i>
            </t-button>
          </div>
        </template>
      </t-table>
    </div>

    <t-dialog
      v-model:visible="editVisible"
      header="编辑主机"
      :on-confirm="onSave"
      width="600px"
      :confirm-btn="{ loading: saving }"
    >
      <div class="td-form">
        <div class="td-form-row">
          <label>账号 (user)</label>
          <t-input v-model="editForm.user" readonly />
        </div>
        <div class="td-form-row">
          <label>密码 (pass)</label>
          <t-input v-model="editForm.pass" />
        </div>
        <div class="td-form-row">
          <label>SQL 账号</label>
          <t-input v-model="editForm.sqluser" readonly />
        </div>
        <div class="td-form-row">
          <label>SQL 密码</label>
          <t-input v-model="editForm.sqlpass" />
        </div>
        <div class="td-form-row">
          <label>网页空间 (MB)</label>
          <t-input-number v-model="editForm.webkj" theme="normal" :min="0" />
        </div>
        <div class="td-form-row">
          <label>数据库空间 (MB)</label>
          <t-input-number v-model="editForm.sqlkj" theme="normal" :min="0" />
        </div>
        <div class="td-form-row">
          <label>最大流量 (G/月)</label>
          <t-input-number v-model="editForm.llmax" theme="normal" :min="0" />
        </div>
        <div class="td-form-row">
          <label>域名最大绑定数</label>
          <t-input-number v-model="editForm.ymbds" theme="normal" :min="0" />
          <div class="td-form-hint">0 = 无限制</div>
        </div>
        <div class="td-form-row">
          <label>到期时间</label>
          <t-date-picker
            v-model="editForm.datar"
            mode="date"
            format="YYYY-MM-DD"
            value-format="YYYY-MM-DD"
            clearable
          />
          <div class="td-form-hint">留空为永久</div>
        </div>
        <div class="td-form-switch">
          <div class="td-form-switch-txt">
            <strong>主机开关</strong>
            <span>关闭后用户无法访问</span>
          </div>
          <t-switch v-model="editForm.zjkg" />
        </div>
      </div>
    </t-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { MessagePlugin, DialogPlugin } from 'tdesign-vue-next'
import { listHost, deleteHost, deleteHostBatch, updateHost } from '@/admin/api/host'

const loading = ref(false)
const saving = ref(false)
const rows = ref([])
const selectedIds = ref([])
const showPass = reactive({})
const showSqlPass = reactive({})
const pagination = reactive({ current: 1, pageSize: 10, total: 0, showJumper: true })
const search = reactive({ field: 'ssbt', type: 'like', value: '' })

const editVisible = ref(false)
const editForm = reactive({
  id: null,
  user: '',
  pass: '',
  sqluser: '',
  sqlpass: '',
  webkj: 0,
  sqlkj: 0,
  llmax: 0,
  ymbds: 0,
  datar: '',
  zjkg: true,
})

const columns = [
  { colKey: 'row-select', type: 'multiple', width: 50 },
  { colKey: 'id', title: 'ID', width: 70, sorter: true },
  { colKey: 'ssbt', title: '所属宝塔', width: 120, sorter: true, ellipsis: true },
  { colKey: 'sqldz', title: '网站名', width: 140, ellipsis: true },
  { colKey: 'userpass', title: '账号/密码', width: 150 },
  { colKey: 'sqluserpass', title: 'SQL账号/密码', width: 150 },
  { colKey: 'hxa', title: '网页空间', width: 110, ellipsis: true },
  { colKey: 'hxb', title: '数据库空间', width: 110, ellipsis: true },
  { colKey: 'llmax', title: '流量', width: 100, ellipsis: true },
  { colKey: 'data', title: '创建时间', width: 140 },
  { colKey: 'datae', title: '到期时间', width: 100 },
  { colKey: 'qk', title: '状态', width: 120 },
  { colKey: 'operate', title: '操作', width: 130, fixed: 'right' },
]

function isOff(qk) {
  return qk === false || qk === 'false' || qk === 0 || qk === '0'
}

function parseJson(v) {
  if (v == null) return null
  if (typeof v === 'object') return v
  try {
    return JSON.parse(v)
  } catch {
    return null
  }
}

function formatSpace(v) {
  const o = parseJson(v)
  if (!o) return '-'
  const dq = Number(o.dq) || 0
  const max = Number(o.max) || 0
  if (max === 0) return `${dq} / ∞ MB`
  return `${dq} / ${max} MB`
}

function formatFlow(v) {
  const o = parseJson(v)
  if (!o) return '-'
  const dq = Number(o.dq) || 0
  const max = Number(o.max) || 0
  if (max === 0) return `${dq} / ∞ G`
  return `${dq} / ${max} G`
}

function spaceOver(v) {
  const o = parseJson(v)
  if (!o) return false
  const dq = Number(o.dq) || 0
  const max = Number(o.max) || 0
  return max > 0 && dq > max
}

function isExpired(datae) {
  if (!datae) return false
  const d = new Date(datae)
  if (isNaN(d.getTime())) return false
  return d.getTime() < Date.now()
}

function fmtTime(v) {
  if (!v) return '-'
  const d = new Date(v)
  if (isNaN(d.getTime())) return String(v)
  return d.toLocaleString('zh-CN', { hour12: false })
}

function togglePass(id) {
  showPass[id] = !showPass[id]
}
function toggleSqlPass(id) {
  showSqlPass[id] = !showSqlPass[id]
}

async function load() {
  loading.value = true
  const where = search.value
    ? { name: search.field, type: search.type, value: search.value }
    : null
  const r = await listHost({ page: pagination.current, limit: pagination.pageSize, where })
  loading.value = false
  if (r.ok && r.data) {
    rows.value = r.data.rows || []
    pagination.total = r.data.total || 0
  } else {
    if (!r.ok) MessagePlugin.error(r.message || '加载失败')
    rows.value = []
    pagination.total = 0
  }
}

function onSearch() {
  pagination.current = 1
  load()
}
function onReset() {
  search.field = 'ssbt'
  search.type = 'like'
  search.value = ''
  pagination.current = 1
  load()
}
function onPageChange(p) {
  pagination.current = p.current
  pagination.pageSize = p.pageSize
  load()
}
function onSelectChange(keys) {
  selectedIds.value = keys
}

function edit(row) {
  editForm.id = row.id
  editForm.user = row.user || ''
  editForm.pass = row.pass || ''
  editForm.sqluser = row.sqluser || ''
  editForm.sqlpass = row.sqlpass || ''
  const hxa = parseJson(row.hxa) || {}
  const hxb = parseJson(row.hxb) || {}
  const llmax = parseJson(row.llmax) || {}
  editForm.webkj = Number(hxa.max) || 0
  editForm.sqlkj = Number(hxb.max) || 0
  editForm.llmax = Number(llmax.max) || 0
  editForm.ymbds = Number(row.ymbds) || 0
  editForm.datar = row.datae || ''
  editForm.zjkg = !isOff(row.qk)
  editVisible.value = true
}

async function onSave() {
  saving.value = true
  const r = await updateHost({
    id: editForm.id,
    user: editForm.user,
    pass: editForm.pass,
    sqluser: editForm.sqluser,
    sqlpass: editForm.sqlpass,
    datar: editForm.datar || '',
    ymbds: editForm.ymbds,
    webkj: editForm.webkj,
    sqlkj: editForm.sqlkj,
    llmax: editForm.llmax,
    kg: editForm.zjkg ? 'true' : 'false',
  })
  saving.value = false
  if (r.ok) {
    MessagePlugin.success('保存成功')
    editVisible.value = false
    load()
  } else {
    MessagePlugin.error(r.message || '保存失败')
  }
}

function del(row) {
  const dlg = DialogPlugin.confirm({
    header: '删除主机',
    body: `确定删除主机 #${row.id}(${row.sqldz || row.user || ''})吗?`,
    theme: 'warning',
    onConfirm: async () => {
      const r = await deleteHost(row.id)
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

function batchDelete() {
  if (!selectedIds.value.length) return
  const dlg = DialogPlugin.confirm({
    header: '批量删除',
    body: `确定删除选中的 ${selectedIds.value.length} 条主机吗?`,
    theme: 'warning',
    onConfirm: async () => {
      const r = await deleteHostBatch(selectedIds.value)
      if (r.ok) {
        MessagePlugin.success('删除成功')
        selectedIds.value = []
        load()
      } else {
        MessagePlugin.error(r.message || '删除失败')
      }
      dlg.destroy()
    },
  })
}

async function loginPanel(row) {
  try {
    await fetch('../user/idcdl.php?gn=logine', {
      method: 'POST',
      credentials: 'include',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `username=${encodeURIComponent(row.user ?? '')}&password=${encodeURIComponent(row.pass ?? '')}`,
    })
  } catch (e) {
    /* ignore */
  }
  window.open('../user/')
}

onMounted(load)
</script>

<style scoped>
.td-cell-line {
  line-height: 1.4;
  font-size: 12px;
}
.td-status-cell {
  display: flex;
  flex-direction: column;
  gap: 2px;
  align-items: flex-start;
}
.td-status-tags {
  display: flex;
  flex-wrap: wrap;
  gap: 2px;
}
</style>
