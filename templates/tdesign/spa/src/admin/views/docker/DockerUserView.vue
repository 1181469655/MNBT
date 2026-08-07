<template>
  <div class="td-page">
    <div class="td-page-head">
      <div>
        <h3 class="td-page-title"><i class="mdi mdi-account-group"></i>Docker 用户</h3>
        <p class="td-page-subtitle">管理 Docker 控制台账号、节点归属与套餐</p>
      </div>
      <div class="td-page-actions">
        <t-button theme="default" variant="outline" @click="load">
          <i class="mdi mdi-refresh"></i> 刷新
        </t-button>
        <t-button theme="primary" @click="openEdit(null)">
          <i class="mdi mdi-plus"></i> 添加用户
        </t-button>
      </div>
    </div>

    <div class="td-table-wrap">
      <t-table
        row-key="id"
        :data="rows"
        :columns="columns"
        :loading="loading"
        table-layout="auto"
        stripe
        bordered
        :max-height="600"
      >
        <template #container_status="{ row }">
          <t-tag :theme="csTheme(row.container_status)" variant="light" size="small">
            {{ csText(row.container_status) }}
          </t-tag>
        </template>
        <template #datae="{ row }">
          {{ row.datae === '0000-00-00' ? '永久' : row.datae }}
        </template>
        <template #qk="{ row }">
          <t-tag :theme="qkTheme(row.qk)" variant="light" size="small">{{ qkText(row.qk) }}</t-tag>
        </template>
        <template #operate="{ row }">
          <div class="td-row-actions">
            <t-button theme="default" variant="outline" size="small" @click="openEdit(row)">编辑</t-button>
            <t-button theme="default" variant="outline" size="small" @click="openReset(row)">改密</t-button>
            <t-button
              v-if="row.qk === 'active'"
              theme="warning"
              variant="outline"
              size="small"
              @click="toggleStatus(row, true)"
            >暂停</t-button>
            <t-button
              v-else
              theme="success"
              variant="outline"
              size="small"
              @click="toggleStatus(row, false)"
            >恢复</t-button>
            <t-button theme="danger" variant="outline" size="small" @click="del(row)">删除</t-button>
          </div>
        </template>
      </t-table>
    </div>

    <!-- 添加/编辑用户弹窗 -->
    <t-dialog
      v-model:visible="editVisible"
      :header="editForm && editForm.id ? '编辑用户' : '添加用户'"
      :on-confirm="onEdit"
      width="560px"
      :confirm-btn="{ loading: saving }"
    >
      <div class="td-form" v-if="editForm">
        <template v-if="!editForm.id">
          <div class="td-form-row">
            <label>账号 <span class="td-text-danger">*</span></label>
            <t-input v-model="editForm.username" />
          </div>
          <div class="td-form-row">
            <label>密码 <span class="td-text-danger">*</span></label>
            <t-input v-model="editForm.password" type="password">
              <template #suffix>
                <t-link theme="primary" @click="editForm.password = randomStr(12)"><i class="mdi mdi-shuffle-variant"></i></t-link>
              </template>
            </t-input>
          </div>
        </template>

        <div class="td-form-row">
          <label>邮箱</label>
          <t-input v-model="editForm.email" />
        </div>

        <div class="td-form-row">
          <label>Docker 节点 <span class="td-text-danger">*</span></label>
          <t-select v-model="editForm.ssbt" :loading="optionsLoading">
            <t-option
              v-for="n in nodeOptions"
              :key="n.id"
              :value="n.id"
              :label="`${n.name} (${n.btip})`"
            />
          </t-select>
        </div>

        <div class="td-form-row">
          <label>套餐</label>
          <t-select v-model="editForm.plan_id" :loading="optionsLoading" clearable>
            <t-option value="0" label="无套餐" />
            <t-option
              v-for="p in planOptions"
              :key="p.id"
              :value="p.id"
              :label="planLabel(p)"
            />
          </t-select>
        </div>

        <div class="td-form-row">
          <label>到期时间</label>
          <t-date-picker
            v-model="editForm.datae"
            mode="date"
            format="YYYY-MM-DD"
            value-format="YYYY-MM-DD"
            clearable
          />
          <div class="td-form-hint">0000-00-00 = 永久</div>
        </div>

        <div v-if="editForm.id" class="td-form-row">
          <label>状态</label>
          <t-select v-model="editForm.qk">
            <t-option value="active" label="正常" />
            <t-option value="paused" label="暂停" />
            <t-option value="expired" label="到期" />
          </t-select>
        </div>
      </div>
    </t-dialog>

    <!-- 重置密码弹窗 -->
    <t-dialog
      v-model:visible="resetVisible"
      header="重置密码"
      :on-confirm="onReset"
      width="420px"
      :confirm-btn="{ loading: resetting }"
    >
      <div class="td-form">
        <div class="td-form-row">
          <label>新密码 <span class="td-text-danger">*</span></label>
          <t-input v-model="resetPassword" type="password">
            <template #suffix>
              <t-link theme="primary" @click="resetPassword = randomStr(12)"><i class="mdi mdi-shuffle-variant"></i></t-link>
            </template>
          </t-input>
        </div>
      </div>
    </t-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { MessagePlugin, DialogPlugin } from 'tdesign-vue-next'
import {
  listDockerUser, addDockerUser, editDockerUser, delDockerUser,
  resetDockerUser, setDockerUserStatus, dockerOptions,
} from '@/admin/api/docker'

const loading = ref(false)
const saving = ref(false)
const resetting = ref(false)
const rows = ref([])

const columns = [
  { colKey: 'id', title: 'ID', width: 70 },
  { colKey: 'username', title: '账号', minWidth: 120, ellipsis: true },
  { colKey: 'email', title: '邮箱', minWidth: 140, ellipsis: true },
  { colKey: 'node_name', title: '节点', minWidth: 130, ellipsis: true },
  { colKey: 'plan_name', title: '套餐', minWidth: 110, ellipsis: true },
  { colKey: 'app_name', title: '应用', minWidth: 110, ellipsis: true },
  { colKey: 'container_status', title: '容器状态', width: 110, align: 'center' },
  { colKey: 'disk_usage', title: '磁盘用量', width: 110, align: 'center', cell: (h, { row }) => formatDiskUsage(row.disk_usage) },
  { colKey: 'data', title: '开通', width: 160 },
  { colKey: 'datae', title: '到期', width: 110 },
  { colKey: 'qk', title: '状态', width: 90, align: 'center' },
  { colKey: 'operate', title: '操作', width: 260, fixed: 'right' },
]

// 选项
const optionsLoading = ref(false)
const nodeOptions = ref([])
const planOptions = ref([])

// 编辑弹窗
const editVisible = ref(false)
const editForm = ref(null)

// 重置密码
const resetVisible = ref(false)
const resetUserId = ref(null)
const resetPassword = ref('')

const csMap = {
  none: ['default', '未创建'],
  creating: ['warning', '创建中'],
  running: ['success', '运行中'],
  stopped: ['default', '已停止'],
  failed: ['danger', '失败'],
}
const qkMap = {
  active: ['success', '正常'],
  paused: ['warning', '已暂停'],
  expired: ['danger', '已到期'],
  pruned: ['default', '已清理'],
}
function planLabel(p) {
  let s = `${p.name} (${p.cpu_max}核/${p.mem_max}MB`
  if (p.disk_max && p.disk_max !== '0') s += `/${p.disk_max}MB磁盘`
  if (p.proxy_max && p.proxy_max !== '0') s += `/${p.proxy_max}代理`
  s += `/¥${p.jg})`
  return s
}

function csText(v) { return (csMap[v] || ['default', v || '未知'])[1] }
function csTheme(v) { return (csMap[v] || ['default'])[0] }
function formatDiskUsage(n) {
  n = parseInt(n, 10) || 0
  if (n <= 0) return '-'
  if (n < 1073741824) return (n / 1048576).toFixed(1) + ' MB'
  return (n / 1073741824).toFixed(2) + ' GB'
}

function qkText(v) { return (qkMap[v] || ['default', v || '未知'])[1] }
function qkTheme(v) { return (qkMap[v] || ['default'])[0] }

function randomStr(len) {
  const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789'
  let s = ''
  for (let i = 0; i < (len || 12); i++) s += chars.charAt(Math.floor(Math.random() * chars.length))
  return s
}

async function load() {
  loading.value = true
  const r = await listDockerUser()
  loading.value = false
  if (r.ok && r.data) {
    rows.value = r.data.data || []
  } else if (!r.ok) {
    MessagePlugin.error(r.message || '加载失败')
  }
}

async function loadOptions() {
  optionsLoading.value = true
  const r = await dockerOptions()
  optionsLoading.value = false
  if (r.ok && r.data) {
    nodeOptions.value = r.data.nodes || []
    planOptions.value = r.data.plans || []
  }
}

function openEdit(row) {
  editForm.value = reactive({
    id: row ? row.id : 0,
    username: row ? row.username || '' : '',
    password: '',
    email: row ? row.email || '' : '',
    ssbt: row ? Number(row.ssbt || 0) : '',
    plan_id: row ? Number(row.plan_id || 0) : 0,
    datae: row ? (row.datae && row.datae !== '0000-00-00' ? row.datae : '') : '',
    qk: row ? row.qk || 'active' : 'active',
  })
  editVisible.value = true
  if (!nodeOptions.value.length) loadOptions()
}

async function onEdit() {
  const f = editForm.value
  if (!f.id && (!f.username || !f.password)) {
    MessagePlugin.warning('账号密码必填')
    return
  }
  if (!f.ssbt) {
    MessagePlugin.warning('请选择 Docker 节点')
    return
  }
  saving.value = true
  const data = {
    id: f.id || undefined,
    username: f.username,
    password: f.password,
    email: f.email,
    ssbt: f.ssbt,
    plan_id: Number(f.plan_id || 0),
    datae: f.datae || '0000-00-00',
  }
  if (f.id) data.qk = f.qk
  const r = f.id ? await editDockerUser(data) : await addDockerUser(data)
  saving.value = false
  if (r.ok) {
    MessagePlugin.success(r.message || '保存成功')
    editVisible.value = false
    load()
  } else {
    MessagePlugin.error(r.message || '保存失败')
  }
}

function openReset(row) {
  resetUserId.value = row.id
  resetPassword.value = randomStr(12)
  resetVisible.value = true
}

async function onReset() {
  if (!resetPassword.value) {
    MessagePlugin.warning('密码不能为空')
    return
  }
  resetting.value = true
  const r = await resetDockerUser(resetUserId.value, resetPassword.value)
  resetting.value = false
  if (r.ok) {
    MessagePlugin.success('密码已重置')
    resetVisible.value = false
  } else {
    MessagePlugin.error(r.message || '重置失败')
  }
}

async function toggleStatus(row, paused) {
  const r = await setDockerUserStatus(row.id, paused)
  if (r.ok) {
    MessagePlugin.success(paused ? '已暂停' : '已恢复')
    load()
  } else {
    MessagePlugin.error(r.message || '操作失败')
  }
}

function del(row) {
  const dlg = DialogPlugin.confirm({
    header: '删除用户',
    body: `确定删除 Docker 用户「${row.username || row.id}」吗？删除用户不会自动删除其容器。`,
    theme: 'warning',
    onConfirm: async () => {
      const r = await delDockerUser(row.id)
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

onMounted(() => {
  load()
  loadOptions()
})
</script>

<style scoped>
.td-page-actions {
  display: flex;
  gap: 8px;
}
</style>
