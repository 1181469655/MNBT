<template>
  <div class="td-page">
    <div class="td-page-head">
      <div>
        <h3 class="td-page-title"><i class="mdi mdi-shield-search"></i>违禁词扫描</h3>
        <p class="td-page-subtitle">扫描节点文件并查看命中记录</p>
      </div>
    </div>

    <div class="td-table-wrap">
      <div class="td-toolbar">
        <t-select
          v-model="filter.node_id"
          placeholder="节点筛选"
          clearable
          style="width: 180px"
          :loading="nodesLoading"
          @change="reload"
        >
          <t-option v-for="n in nodes" :key="n.id" :value="n.id" :label="n.name || `#${n.id}`" />
        </t-select>
        <t-select v-model="filter.days" placeholder="时间" style="width: 130px" @change="reload">
          <t-option :value="0" label="全部" />
          <t-option :value="1" label="今天" />
          <t-option :value="2" label="昨天" />
          <t-option :value="7" label="最近7天" />
          <t-option :value="30" label="最近30天" />
        </t-select>
        <t-select v-model="filter.status" placeholder="状态" style="width: 130px" @change="reload">
          <t-option value="" label="全部" />
          <t-option value="success" label="成功" />
          <t-option value="failed" label="失败" />
          <t-option value="matched" label="有命中" />
        </t-select>
        <t-button theme="primary" variant="outline" @click="openBatchScan">
          <i class="mdi mdi-radar"></i> 批量扫描
        </t-button>
        <t-button theme="default" variant="outline" @click="openClear">
          <i class="mdi mdi-broom"></i> 清理旧记录
        </t-button>
        <div class="td-toolbar-spacer"></div>
        <t-button theme="default" variant="outline" @click="reload">
          <i class="mdi mdi-refresh"></i> 刷新
        </t-button>
      </div>

      <t-tabs v-model="tab" class="td-scan-tabs">
        <t-tab-panel value="scan" label="扫描记录">
          <t-table
            row-key="id"
            :data="scanRows"
            :columns="scanColumns"
            :loading="scanLoading"
            :pagination="scanPagination"
            table-layout="auto"
            stripe
            bordered
            @page-change="onScanPage"
          >
            <template #status="{ row }">
              <span :class="statusClass(row)">{{ statusText(row) }}</span>
            </template>
            <template #started_at="{ row }">{{ fmtTime(row.started_at) }}</template>
            <template #finished_at="{ row }">{{ fmtTime(row.finished_at) }}</template>
          </t-table>
        </t-tab-panel>

        <t-tab-panel value="match" label="命中记录">
          <t-table
            row-key="id"
            :data="matchRows"
            :columns="matchColumns"
            :loading="matchLoading"
            :pagination="matchPagination"
            table-layout="auto"
            stripe
            bordered
            @page-change="onMatchPage"
          >
            <template #created_at="{ row }">{{ fmtTime(row.created_at) }}</template>
            <template #operate="{ row }">
              <t-button theme="default" variant="outline" size="small" @click="viewMatch(row)">
                <i class="mdi mdi-eye"></i> 详情
              </t-button>
            </template>
          </t-table>
        </t-tab-panel>
      </t-tabs>
    </div>

    <t-dialog
      v-model:visible="batchVisible"
      header="批量扫描"
      :on-confirm="doBatchScan"
      width="500px"
      :confirm-btn="{ loading: scanBusy }"
    >
      <div class="td-form">
        <div class="td-form-row">
          <label>选择节点 <span class="td-text-danger">*</span></label>
          <t-select v-model="batchForm.node_id" placeholder="选择节点" :loading="nodesLoading">
            <t-option v-for="n in nodes" :key="n.id" :value="n.id" :label="n.name || `#${n.id}`" />
          </t-select>
          <div class="td-form-hint">将向该节点下发违禁词扫描任务</div>
        </div>
      </div>
    </t-dialog>

    <t-dialog
      v-model:visible="clearVisible"
      header="清理旧扫描记录"
      :on-confirm="doClear"
      width="450px"
      :confirm-btn="{ loading: clearBusy }"
    >
      <div class="td-form">
        <div class="td-form-row">
          <label>清理多少天前的记录</label>
          <t-input-number v-model="clearDays" :min="1" theme="normal" />
          <div class="td-form-hint">将删除该天数之前的所有扫描与命中记录</div>
        </div>
      </div>
    </t-dialog>

    <t-dialog v-model:visible="matchVisible" header="命中详情" width="600px">
      <div v-if="currentMatch" class="td-form">
        <div class="td-form-row">
          <label>文件路径</label>
          <div class="td-code-block">{{ currentMatch.file_path || '-' }}</div>
        </div>
        <div class="td-form-row">
          <label>命中关键词</label>
          <div class="td-code-block">{{ currentMatch.matched_keyword || '-' }}</div>
        </div>
        <div class="td-form-row">
          <label>命中行内容</label>
          <pre class="td-match-pre">{{ currentMatch.matched_line || '-' }}</pre>
        </div>
      </div>
      <template #footer>
        <t-button theme="default" @click="matchVisible = false">关闭</t-button>
      </template>
    </t-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, watch } from 'vue'
import { MessagePlugin } from 'tdesign-vue-next'
import {
  listNode, listForbiddenScan, listForbiddenMatch,
  nodeForbiddenScan, clearOldScans,
} from '@/admin/api/node'

const tab = ref('scan')
const nodes = ref([])
const nodesLoading = ref(false)

const filter = reactive({ node_id: '', days: 0, status: '' })

const scanRows = ref([])
const scanLoading = ref(false)
const scanPagination = reactive({ current: 1, pageSize: 10, total: 0, showJumper: true })
const matchRows = ref([])
const matchLoading = ref(false)
const matchPagination = reactive({ current: 1, pageSize: 10, total: 0, showJumper: true })

const batchVisible = ref(false)
const batchForm = reactive({ node_id: '' })
const scanBusy = ref(false)

const clearVisible = ref(false)
const clearDays = ref(30)
const clearBusy = ref(false)

const matchVisible = ref(false)
const currentMatch = ref(null)

const scanColumns = [
  { colKey: 'id', title: 'ID', width: 70 },
  { colKey: 'node_id', title: '节点ID', width: 90 },
  { colKey: 'node_name', title: '节点名称', width: 140, ellipsis: true },
  { colKey: 'status', title: '状态', width: 100 },
  { colKey: 'started_at', title: '开始时间', width: 160 },
  { colKey: 'finished_at', title: '完成时间', width: 160 },
  { colKey: 'scanned_count', title: '扫描文件', width: 100 },
  { colKey: 'matched_count', title: '命中数', width: 100 },
]
const matchColumns = [
  { colKey: 'id', title: 'ID', width: 70 },
  { colKey: 'scan_id', title: '扫描ID', width: 90 },
  { colKey: 'node_id', title: '节点ID', width: 90 },
  { colKey: 'file_path', title: '文件路径', width: 260, ellipsis: true },
  { colKey: 'matched_keyword', title: '关键词', width: 140, ellipsis: true },
  { colKey: 'created_at', title: '命中时间', width: 160 },
  { colKey: 'operate', title: '操作', width: 110, fixed: 'right' },
]

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

function statusClass(row) {
  if (row.status === 'success' && Number(row.matched_count) > 0) return 'td-chip td-chip-warning'
  if (row.status === 'success') return 'td-chip td-chip-success'
  if (row.status === 'failed') return 'td-chip td-chip-danger'
  return 'td-chip td-chip-default'
}
function statusText(row) {
  if (row.status === 'success' && Number(row.matched_count) > 0) return '有命中'
  if (row.status === 'success') return '成功'
  if (row.status === 'failed') return '失败'
  return row.status || '-'
}

async function loadNodes() {
  nodesLoading.value = true
  try {
    const r = await listNode({ page: 1, limit: 200 })
    if (r.ok && r.data) {
      nodes.value = Array.isArray(r.data) ? r.data : (r.data.rows || [])
    } else {
      nodes.value = []
    }
  } catch (e) {
    nodes.value = []
  } finally {
    nodesLoading.value = false
  }
}

function buildParams(pag) {
  const params = { page: pag.current, limit: pag.pageSize }
  if (filter.node_id !== '' && filter.node_id != null) params.node_id = filter.node_id
  if (filter.status) params.status = filter.status
  if (filter.days > 0) params.days = filter.days
  return params
}

async function loadScan() {
  scanLoading.value = true
  try {
    const r = await listForbiddenScan(buildParams(scanPagination))
    if (r.ok && r.data) {
      scanRows.value = Array.isArray(r.data) ? r.data : (r.data.rows || [])
      scanPagination.total = (r.data && r.data.total != null) ? r.data.total : scanRows.value.length
    } else {
      scanRows.value = []
      scanPagination.total = 0
      if (!r.ok) MessagePlugin.error(r.message || '加载失败')
    }
  } catch (e) {
    scanRows.value = []
    scanPagination.total = 0
    MessagePlugin.error('加载扫描记录失败')
  } finally {
    scanLoading.value = false
  }
}

async function loadMatch() {
  matchLoading.value = true
  try {
    const r = await listForbiddenMatch(buildParams(matchPagination))
    if (r.ok && r.data) {
      matchRows.value = Array.isArray(r.data) ? r.data : (r.data.rows || [])
      matchPagination.total = (r.data && r.data.total != null) ? r.data.total : matchRows.value.length
    } else {
      matchRows.value = []
      matchPagination.total = 0
    }
  } catch (e) {
    matchRows.value = []
    matchPagination.total = 0
    MessagePlugin.error('加载命中记录失败')
  } finally {
    matchLoading.value = false
  }
}

function reload() {
  scanPagination.current = 1
  matchPagination.current = 1
  loadScan()
  loadMatch()
}

function onScanPage(p) {
  scanPagination.current = p.current
  scanPagination.pageSize = p.pageSize
  loadScan()
}
function onMatchPage(p) {
  matchPagination.current = p.current
  matchPagination.pageSize = p.pageSize
  loadMatch()
}

watch(tab, (v) => {
  if (v === 'scan' && !scanRows.value.length) loadScan()
  if (v === 'match' && !matchRows.value.length) loadMatch()
})

function openBatchScan() {
  batchForm.node_id = ''
  batchVisible.value = true
  if (!nodes.value.length) loadNodes()
}

async function doBatchScan() {
  if (!batchForm.node_id) { MessagePlugin.warning('请选择节点'); return }
  scanBusy.value = true
  const r = await nodeForbiddenScan({ node_id: batchForm.node_id })
  scanBusy.value = false
  if (r.ok) {
    MessagePlugin.success('扫描任务已下发')
    batchVisible.value = false
    setTimeout(loadScan, 1500)
  } else {
    MessagePlugin.error(r.message || '下发失败')
  }
}

function openClear() {
  clearDays.value = 30
  clearVisible.value = true
}

async function doClear() {
  if (!clearDays.value || clearDays.value < 1) { MessagePlugin.warning('请输入有效天数'); return }
  clearBusy.value = true
  const r = await clearOldScans(clearDays.value)
  clearBusy.value = false
  if (r.ok) {
    MessagePlugin.success('清理完成')
    clearVisible.value = false
    reload()
  } else {
    MessagePlugin.error(r.message || '清理失败')
  }
}

function viewMatch(row) {
  currentMatch.value = row
  matchVisible.value = true
}

onMounted(() => {
  loadNodes()
  loadScan()
})
</script>

<style scoped>
.td-scan-tabs {
  padding: 0 12px;
}
.td-match-pre {
  background: #f7f8fa;
  border: 1px solid var(--td-border);
  border-radius: var(--td-radius);
  padding: 10px 12px;
  font-family: Consolas, Monaco, monospace;
  font-size: 12px;
  line-height: 1.6;
  color: var(--td-text);
  white-space: pre-wrap;
  word-break: break-all;
  max-height: 240px;
  overflow: auto;
  margin: 0;
}
</style>
