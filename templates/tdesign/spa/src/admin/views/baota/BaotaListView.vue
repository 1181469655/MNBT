<template>
  <div class="td-page">
    <div class="td-page-head">
      <div>
        <h3 class="td-page-title"><i class="mdi mdi-server"></i>宝塔列表</h3>
        <p class="td-page-subtitle">管理对接的宝塔面板</p>
      </div>
    </div>

    <div class="td-table-wrap">
      <div class="td-toolbar">
        <t-button theme="primary" @click="$router.push('/baota/add')">
          <i class="mdi mdi-plus"></i> 新增宝塔
        </t-button>
        <div class="td-toolbar-spacer"></div>
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
        <template #status="{ row }">
          <t-button
            v-if="connectStatus[row.id] === undefined"
            theme="default"
            variant="outline"
            size="small"
            :loading="connectLoading[row.id]"
            @click="checkConnect(row)"
          >点我检测</t-button>
          <span v-else-if="connectStatus[row.id]" class="td-chip td-chip-success">通信正常</span>
          <span v-else class="td-chip td-chip-danger">通信失败</span>
        </template>
        <template #ktmy="{ row }">
          <div class="td-flex-center td-gap-8">
            <span class="td-mono">{{ showKtmy[row.id] ? (row.ktmy || '-') : '********' }}</span>
            <t-link theme="primary" @click="toggleKtmy(row.id)">
              <i class="mdi" :class="showKtmy[row.id] ? 'mdi-eye-off' : 'mdi-eye'"></i>
            </t-link>
          </div>
        </template>
        <template #btmy="{ row }">
          <div class="td-flex-center td-gap-8">
            <span class="td-mono">{{ showBtmy[row.id] ? (row.btmy || '-') : '********' }}</span>
            <t-link theme="primary" @click="toggleBtmy(row.id)">
              <i class="mdi" :class="showBtmy[row.id] ? 'mdi-eye-off' : 'mdi-eye'"></i>
            </t-link>
          </div>
        </template>
        <template #qk="{ row }">
          <span :class="isOn(row.qk) ? 'td-chip td-chip-success' : 'td-chip td-chip-danger'">
            {{ isOn(row.qk) ? '开启' : '关闭' }}
          </span>
        </template>
        <template #date="{ row }">{{ fmtTime(row.date) }}</template>
        <template #operate="{ row }">
          <div class="td-row-actions">
            <t-button theme="default" variant="outline" size="small" @click="edit(row)" title="编辑">
              <i class="mdi mdi-pencil"></i>
            </t-button>
            <t-button theme="default" variant="outline" size="small" @click="openTutorial(row)" title="魔方对接文档">
              <i class="mdi mdi-book-open-variant"></i>
            </t-button>
            <t-button theme="default" variant="outline" size="small" @click="openPhpList(row)" title="PHP 版本">
              <i class="mdi mdi-language-php"></i>
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
      header="编辑宝塔"
      :on-confirm="onSave"
      width="600px"
      :confirm-btn="{ loading: saving }"
    >
      <div class="td-form">
        <div class="td-form-row">
          <label>宝塔 IP</label>
          <t-input v-model="editForm.ip" />
        </div>
        <div class="td-form-row">
          <label>宝塔端口</label>
          <t-input v-model="editForm.dk" />
        </div>
        <div class="td-form-row">
          <label>宝塔密钥</label>
          <t-textarea v-model="editForm.key" :autosize="{ minRows: 2, maxRows: 4 }" />
        </div>
        <div class="td-form-row">
          <label>操作系统</label>
          <t-select v-model="editForm.btos">
            <t-option :value="1" label="Linux" />
            <t-option :value="2" label="Windows" />
          </t-select>
        </div>
        <div class="td-form-row">
          <label>域名解析说明</label>
          <t-textarea v-model="editForm.urlla" :autosize="{ minRows: 2, maxRows: 4 }" />
        </div>
        <div class="td-form-row">
          <label>FTP 地址</label>
          <t-input v-model="editForm.ftpdz" />
        </div>
        <div class="td-form-switch">
          <div class="td-form-switch-txt">
            <strong>安全访问 HTTPS</strong>
            <span>开启后通过 HTTPS 访问宝塔</span>
          </div>
          <t-switch v-model="editForm.xieyi" />
        </div>
        <div class="td-form-switch">
          <div class="td-form-switch-txt">
            <strong>宝塔接口开关</strong>
            <span>关闭后无法调用宝塔</span>
          </div>
          <t-switch v-model="editForm.kg" />
        </div>
      </div>
    </t-dialog>

    <t-dialog v-model:visible="phpVisible" header="PHP 版本" width="520px">
      <t-loading :loading="phpLoading" text="加载中…">
        <div v-if="phpCurrent" class="td-php-current">
          当前默认：<span class="td-chip td-chip-info">{{ phpCurrent }}</span>
        </div>
        <div v-if="phpList.length">
          <t-radio-group v-model="phpSelected" class="td-php-list">
            <t-radio v-for="v in phpList" :key="v.version" :value="v.version">
              {{ v.name }} <span class="td-text-mute">({{ v.version }})</span>
            </t-radio>
          </t-radio-group>
        </div>
        <div v-else class="td-empty">
          <i class="mdi mdi-information"></i>暂无 PHP 版本
        </div>
      </t-loading>
      <template #footer>
        <t-button theme="primary" :loading="autoLoading" @click="setPhp">
          设为默认
        </t-button>
        <t-button theme="default" variant="outline" :loading="autoLoading" @click="autoSetPhp">
          自动设置最新 PHP
        </t-button>
        <t-button theme="default" variant="outline" @click="phpVisible = false">关闭</t-button>
      </template>
    </t-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { MessagePlugin, DialogPlugin } from 'tdesign-vue-next'
import {
  listBaota, deleteBaota, updateBaota, checkBaotaConnect,
  listNodePhp, autoDetectNodePhp, setNodePhp,
} from '@/admin/api/baota'

const router = useRouter()
const loading = ref(false)
const saving = ref(false)
const rows = ref([])
const selectedIds = ref([])
const showKtmy = reactive({})
const showBtmy = reactive({})
const connectStatus = reactive({})
const connectLoading = reactive({})
const pagination = reactive({ current: 1, pageSize: 10, total: 0, showJumper: true })

const editVisible = ref(false)
const editForm = reactive({
  id: null, ip: '', dk: '', key: '', btos: 1,
  urlla: '', ftpdz: '', xieyi: false, kg: true,
})

const phpVisible = ref(false)
const phpList = ref([])
const phpLoading = ref(false)
const autoLoading = ref(false)
const currentBtdh = ref('')
const phpCurrent = ref('')
const phpSelected = ref('')

const columns = [
  { colKey: 'row-select', type: 'multiple', width: 50 },
  { colKey: 'id', title: 'ID', width: 70, sorter: true },
  { colKey: 'status', title: '通信状态', width: 110 },
  { colKey: 'btdh', title: '宝塔编号', width: 140, ellipsis: true },
  { colKey: 'btip', title: '宝塔IP', width: 140 },
  { colKey: 'ktmy', title: '调用密钥', width: 180 },
  { colKey: 'date', title: '添加时间', width: 150 },
  { colKey: 'btdk', title: '端口', width: 80 },
  { colKey: 'btmy', title: '宝塔密钥', width: 180 },
  { colKey: 'qk', title: '状态', width: 90 },
  { colKey: 'operate', title: '操作', width: 180, fixed: 'right' },
]

function isOn(qk) {
  return qk === true || qk === 'true'
}

function fmtTime(v) {
  if (!v) return '-'
  const d = new Date(v)
  if (isNaN(d.getTime())) return String(v)
  return d.toLocaleString('zh-CN', { hour12: false })
}

function toggleKtmy(id) {
  showKtmy[id] = !showKtmy[id]
}
function toggleBtmy(id) {
  showBtmy[id] = !showBtmy[id]
}

async function load() {
  loading.value = true
  const r = await listBaota({ page: pagination.current, limit: pagination.pageSize })
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

function onPageChange(p) {
  pagination.current = p.current
  pagination.pageSize = p.pageSize
  load()
}
function onSelectChange(keys) {
  selectedIds.value = keys
}

async function checkConnect(row) {
  connectLoading[row.id] = true
  const r = await checkBaotaConnect(row.id)
  connectLoading[row.id] = false
  connectStatus[row.id] = !!r.ok
  if (!r.ok) MessagePlugin.warning(r.message || '通信失败')
}

function edit(row) {
  editForm.id = row.id
  editForm.ip = row.btip || ''
  editForm.dk = row.btdk || ''
  editForm.key = row.btmy || ''
  editForm.btos = Number(row.btos) || 1
  editForm.urlla = row.als || ''
  editForm.ftpdz = row.ftpdz || ''
  editForm.xieyi = row.ptl === true || row.ptl === 'true'
  editForm.kg = isOn(row.qk)
  editVisible.value = true
}

async function onSave() {
  saving.value = true
  const r = await updateBaota({
    id: editForm.id,
    ip: editForm.ip,
    dk: editForm.dk,
    key: editForm.key,
    btos: editForm.btos,
    urlla: editForm.urlla,
    ftpdz: editForm.ftpdz,
    xieyi: editForm.xieyi ? 'true' : 'false',
    kg: editForm.kg ? 'true' : 'false',
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
    header: '删除宝塔',
    body: `确定删除宝塔 #${row.id}(${row.btdh || ''})吗?`,
    theme: 'warning',
    onConfirm: async () => {
      const r = await deleteBaota(row.id)
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
    body: `确定删除选中的 ${selectedIds.value.length} 条宝塔吗?`,
    theme: 'warning',
    onConfirm: async () => {
      let ok = 0, fail = 0
      for (const id of selectedIds.value) {
        const r = await deleteBaota(id)
        if (r.ok) ok++
        else fail++
      }
      if (fail === 0) MessagePlugin.success(`成功删除 ${ok} 条`)
      else MessagePlugin.warning(`成功 ${ok} 条, 失败 ${fail} 条`)
      selectedIds.value = []
      load()
      dlg.destroy()
    },
  })
}

function openTutorial(row) {
  router.push({ path: '/tutorial', query: { gn: 'mr', sz: String(row.id) } })
}

async function openPhpList(row) {
  currentBtdh.value = row.btdh
  phpVisible.value = true
  phpLoading.value = true
  phpList.value = []
  phpCurrent.value = ''
  phpSelected.value = ''
  const r = await listNodePhp(row.btdh)
  phpLoading.value = false
  if (r.ok && r.data) {
    if (Array.isArray(r.data.versions)) {
      phpList.value = r.data.versions
      phpCurrent.value = r.data.current_default || ''
      phpSelected.value = phpCurrent.value || (r.data.latest || '')
    } else if (Array.isArray(r.data)) {
      phpList.value = r.data
    } else if (Array.isArray(r.data.list)) {
      phpList.value = r.data.list
    } else {
      phpList.value = []
    }
  } else {
    MessagePlugin.warning(r.message || '获取失败')
  }
}

async function setPhp() {
  if (!phpSelected.value) {
    MessagePlugin.warning('请选择 PHP 版本')
    return
  }
  autoLoading.value = true
  const r = await setNodePhp(currentBtdh.value, phpSelected.value)
  autoLoading.value = false
  if (r.ok) {
    MessagePlugin.success(r.message || '设置成功')
    phpVisible.value = false
  } else {
    MessagePlugin.error(r.message || '设置失败')
  }
}

async function autoSetPhp() {
  autoLoading.value = true
  const r = await autoDetectNodePhp(currentBtdh.value)
  autoLoading.value = false
  if (r.ok) {
    MessagePlugin.success('已自动设置')
    phpVisible.value = false
  } else {
    MessagePlugin.error(r.message || '设置失败')
  }
}

onMounted(load)
</script>

<style scoped>
.td-mono {
  font-family: Consolas, Monaco, monospace;
  font-size: 12px;
  word-break: break-all;
}
.td-php-current {
  margin-bottom: 14px;
  font-size: 13px;
}
.td-php-list {
  display: flex;
  flex-direction: column;
  gap: 10px;
}
.td-php-list :deep(.t-radio) {
  font-size: 13px;
}
</style>
