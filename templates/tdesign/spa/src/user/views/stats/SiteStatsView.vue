<template>
  <div class="td-page">
    <div class="td-page-head">
      <div>
        <h3 class="td-page-title"><i class="mdi mdi-chart-line"></i>站点统计</h3>
        <p class="td-page-subtitle">查看访问量、流量等站点统计信息</p>
      </div>
      <div class="td-head-actions">
        <t-select v-model="range" style="width: 130px" @change="onRangeChange">
          <t-option value="today" label="今日" />
          <t-option value="yesterday" label="昨日" />
          <t-option value="7days" label="近7天" />
          <t-option value="30days" label="近30天" />
        </t-select>
        <t-button theme="default" variant="outline" @click="loadAll">
          <i class="mdi mdi-refresh"></i> 刷新
        </t-button>
      </div>
    </div>

    <!-- 数字卡片汇总 -->
    <section class="stat-row" v-if="overview">
      <div class="stat-card" v-for="card in summaryCards" :key="card.label">
        <div class="stat-card-body">
          <div class="stat-label">{{ card.label }}</div>
          <div class="stat-num" :style="{ color: card.color }">{{ card.value }}</div>
          <div class="stat-foot">
            <i class="mdi" :class="card.footIcon"></i>
            {{ card.foot }}
          </div>
        </div>
        <div class="stat-card-icon" :style="{ background: card.bg, color: card.color }">
          <i class="mdi" :class="card.icon"></i>
        </div>
      </div>
    </section>

    <t-loading v-if="overviewLoading && !overview" text="加载概览中…" size="large" />

    <!-- 明细列表 -->
    <div class="td-table-wrap">
      <div class="td-toolbar">
        <t-radio-group v-model="detailTab" variant="default-filled" size="small" @change="onTabChange">
          <t-radio-button value="uri_rank">访问路径</t-radio-button>
          <t-radio-button value="ip_rank">IP 排行</t-radio-button>
          <t-radio-button value="errors">错误日志</t-radio-button>
        </t-radio-group>
        <div class="td-toolbar-spacer"></div>
        <t-button theme="default" variant="text" @click="loadDetail">
          <i class="mdi mdi-refresh"></i>
        </t-button>
      </div>

      <t-table
        row-key="id"
        :data="rows"
        :columns="columns"
        :loading="detailLoading"
        :pagination="pagination"
        table-layout="auto"
        stripe
        bordered
        @page-change="onPageChange"
      >
        <template #empty>
          <div class="td-empty">
            <i class="mdi mdi-database-search-outline"></i>
            暂无统计数据
          </div>
        </template>
      </t-table>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { MessagePlugin } from 'tdesign-vue-next'
import { getSiteStats } from '@/user/api/stats'

const overview = ref(null)
const overviewLoading = ref(false)
const detailLoading = ref(false)
const rows = ref([])
const range = ref('today')
const detailTab = ref('uri_rank')

const pagination = reactive({
  current: 1,
  pageSize: 15,
  total: 0,
  showJumper: true,
})

const columns = computed(() => {
  if (detailTab.value === 'ip_rank') {
    return [
      { colKey: 'ip', title: 'IP 地址', minWidth: 140 },
      { colKey: 'count', title: '请求数', width: 120 },
      { colKey: 'traffic', title: '流量', width: 120 },
    ]
  }
  if (detailTab.value === 'errors') {
    return [
      { colKey: 'time', title: '时间', width: 160 },
      { colKey: 'status', title: '状态码', width: 90 },
      { colKey: 'uri', title: '路径', minWidth: 200, ellipsis: true },
      { colKey: 'ip', title: 'IP', width: 130 },
    ]
  }
  // uri_rank 默认
  return [
    { colKey: 'uri', title: '访问路径', minWidth: 200, ellipsis: true },
    { colKey: 'count', title: '请求数', width: 120 },
    { colKey: 'traffic', title: '流量', width: 120 },
  ]
})

const summaryCards = computed(() => {
  const d = overview.value
  if (!d) return []
  const totalReq = Number(d.total_requests || d.requests || d.count || 0)
  const totalIp = Number(d.unique_ips || d.ips || 0)
  const totalTraffic = Number(d.total_traffic || d.traffic || 0)
  const totalErr = Number(d.error_count || d.errors || 0)
  return [
    {
      label: '总请求数',
      value: totalReq.toLocaleString(),
      icon: 'mdi-file-multiple-outline',
      color: '#0052d9',
      bg: '#e8f3ff',
      footIcon: 'mdi-chart-bell-curve',
      foot: rangeText(),
    },
    {
      label: '独立 IP',
      value: totalIp.toLocaleString(),
      icon: 'mdi-account-multiple-outline',
      color: '#2ba471',
      bg: '#e8f8f0',
      footIcon: 'mdi-eye-outline',
      foot: 'IP 访问',
    },
    {
      label: '总流量',
      value: fmtFlow(totalTraffic),
      icon: 'mdi-cloud-download-outline',
      color: '#e37318',
      bg: '#fff3e0',
      footIcon: 'mdi-database',
      foot: '字节累计',
    },
    {
      label: '错误请求',
      value: totalErr.toLocaleString(),
      icon: 'mdi-alert-circle-outline',
      color: '#d54941',
      bg: '#fdecee',
      footIcon: 'mdi-alert',
      foot: '错误数',
    },
  ]
})

function rangeText() {
  const m = { today: '今日', yesterday: '昨日', '7days': '近7天', '30days': '近30天' }
  return m[range.value] || range.value
}

function fmtFlow(v) {
  const n = Number(v) || 0
  if (n === 0) return '0 B'
  const units = ['B', 'KB', 'MB', 'GB', 'TB']
  const pow = Math.min(Math.floor(Math.log(n) / Math.log(1024)), units.length - 1)
  return (n / Math.pow(1024, pow)).toFixed(2) + ' ' + units[pow]
}

function onRangeChange() {
  pagination.current = 1
  loadAll()
}

function onTabChange() {
  pagination.current = 1
  loadDetail()
}

async function loadOverview() {
  overviewLoading.value = true
  const r = await getSiteStats('overview', range.value)
  overviewLoading.value = false
  if (r.ok && r.data) {
    // 后端透传宝塔插件响应,数据可能在 data.msg 或 data 本身
    const d = r.data
    overview.value = d.msg || d.data || d
  } else {
    overview.value = null
  }
}

async function loadDetail() {
  detailLoading.value = true
  const r = await getSiteStats(detailTab.value, range.value)
  detailLoading.value = false
  if (r.ok && r.data) {
    const d = r.data
    const msg = d.msg || d.data || d
    const list = Array.isArray(msg) ? msg : (msg.list || msg.rows || msg.items || [])
    rows.value = list
    pagination.total = msg.total || d.total || list.length
  } else {
    rows.value = []
    pagination.total = 0
    if (!r.ok && r.message && r.message !== 'ok') {
      MessagePlugin.warning(r.message)
    }
  }
}

async function loadAll() {
  await Promise.all([loadOverview(), loadDetail()])
}

function onPageChange(p) {
  pagination.current = p.current
  pagination.pageSize = p.pageSize
  loadDetail()
}

onMounted(loadAll)
</script>

<style scoped>
.td-head-actions {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
}

/* ============== 统计卡片 ============== */
.stat-row {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 14px;
  margin-bottom: 18px;
}
.stat-card {
  background: var(--td-surface);
  border: 1px solid var(--td-border);
  border-radius: var(--td-radius-lg);
  padding: 18px 20px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 14px;
  box-shadow: var(--td-shadow-sm);
  transition: transform var(--td-dur) var(--td-ease),
              box-shadow var(--td-dur) var(--td-ease);
  animation: td-fade-in var(--td-dur-lg) var(--td-ease-out);
}
.stat-card:hover {
  transform: translateY(-2px);
  box-shadow: var(--td-shadow-md);
}
.stat-card-body {
  flex: 1;
  min-width: 0;
}
.stat-label {
  font-size: 12px;
  color: var(--td-text-secondary);
  margin-bottom: 4px;
}
.stat-num {
  font-size: 24px;
  font-weight: 700;
  line-height: 1.1;
  font-variant-numeric: tabular-nums;
  letter-spacing: -0.3px;
  word-break: break-all;
}
.stat-foot {
  font-size: 11px;
  color: var(--td-text-placeholder);
  margin-top: 6px;
  display: flex;
  align-items: center;
  gap: 4px;
}
.stat-card-icon {
  width: 44px;
  height: 44px;
  border-radius: 12px;
  display: grid;
  place-items: center;
  font-size: 22px;
  flex-shrink: 0;
}

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
