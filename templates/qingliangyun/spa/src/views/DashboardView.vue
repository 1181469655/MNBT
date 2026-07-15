<template>
  <div class="ql-page">
    <div class="hero">
      <div>
        <h2>你好，{{ userName }}</h2>
        <p class="ql-muted">主机资源与运行状态一览</p>
      </div>
      <el-button type="primary" round :loading="refreshing" @click="onRefresh">
        <el-icon class="el-icon--left"><Refresh /></el-icon>
        刷新用量
      </el-button>
    </div>

    <el-row :gutter="16" class="quota-row">
      <el-col :xs="24" :sm="8" v-for="item in quotaCards" :key="item.key">
        <div class="ql-card quota-card">
          <CircleProgress
            :percent="item.percent"
            :label="item.title"
            :color="item.color"
            :size="132"
          />
          <div class="quota-meta">
            <div class="used">{{ item.usedText }}</div>
            <div class="ql-muted">上限 {{ item.maxText }}</div>
          </div>
        </div>
      </el-col>
    </el-row>

    <div class="main-grid">
      <div class="main-left">
        <div class="ql-card chart-card">
          <div class="chart-head">
            <h3 class="ql-section-title" style="margin:0">月度流量趋势</h3>
            <span class="ql-muted" v-html="trendHtml" />
          </div>
          <div class="legend">
            <span class="leg"><i class="dot line" />趋势</span>
            <span class="leg"><i class="dot bar" />流量用量 (GB)</span>
          </div>
          <div class="chart-area" ref="chartRef">
            <svg
              v-if="chartPoints.length"
              class="traffic-svg"
              :viewBox="`0 0 ${svgW} ${svgH}`"
              preserveAspectRatio="none"
            >
              <!-- grid -->
              <line
                v-for="g in gridYs"
                :key="'g' + g"
                :x1="padL"
                :y1="g"
                :x2="svgW - padR"
                :y2="g"
                class="grid"
              />
              <!-- bars -->
              <rect
                v-for="(p, i) in chartPoints"
                :key="'b' + i"
                :x="p.bx"
                :y="p.by"
                :width="p.bw"
                :height="p.bh"
                rx="6"
                class="bar-fill"
              />
              <!-- area under line -->
              <path v-if="areaPath" :d="areaPath" class="area-fill" />
              <!-- trend line -->
              <polyline
                v-if="linePoints"
                :points="linePoints"
                class="trend-line"
                fill="none"
              />
              <circle
                v-for="(p, i) in chartPoints"
                :key="'c' + i"
                :cx="p.cx"
                :cy="p.cy"
                r="4"
                class="trend-dot"
              />
            </svg>
            <div v-else class="chart-empty ql-muted">暂无流量历史，刷新用量后可查看</div>
            <div v-if="chartPoints.length" class="x-labels">
              <span v-for="(p, i) in chartPoints" :key="'l' + i">{{ p.label }}</span>
            </div>
          </div>
        </div>

        <div class="ql-card shortcut-card">
          <h3 class="ql-section-title">快捷功能</h3>
          <div class="shortcut-grid">
            <button
              v-for="s in shortcuts"
              :key="s.label"
              class="shortcut"
              type="button"
              @click="go(s)"
            >
              <el-icon :size="20"><component :is="s.icon" /></el-icon>
              <span>{{ s.label }}</span>
            </button>
          </div>
        </div>
      </div>

      <div class="main-right">
        <div class="ql-card info-card">
          <h3 class="ql-section-title">主机信息</h3>
          <div class="info-list" v-loading="loading">
            <div class="info-item">
              <span class="ql-muted">运行状态</span>
              <el-tag :type="statusTag" effect="light" round size="small">{{ statusText }}</el-tag>
            </div>
            <div class="info-item">
              <span class="ql-muted">域名额度</span>
              <strong>{{ domainLimit }}</strong>
            </div>
            <div class="info-item">
              <span class="ql-muted">PHP 版本</span>
              <div class="php-row">
                <strong>{{ phpNow || '—' }}</strong>
                <el-dropdown v-if="phpList.length" trigger="click" @command="onPhpChange">
                  <el-button link type="primary" size="small">切换</el-button>
                  <template #dropdown>
                    <el-dropdown-menu>
                      <el-dropdown-item
                        v-for="p in phpList"
                        :key="p.version || p.name"
                        :command="p.version || p.name"
                      >
                        {{ p.name || p.version }}
                      </el-dropdown-item>
                    </el-dropdown-menu>
                  </template>
                </el-dropdown>
              </div>
            </div>
            <div v-if="!isCdn" class="info-item">
              <span class="ql-muted">FTP</span>
              <strong class="mono">{{ ftpHost || '—' }}</strong>
            </div>
            <div v-if="!isCdn" class="info-item">
              <span class="ql-muted">FTP 账号</span>
              <strong class="mono">{{ ftpUser || '—' }}</strong>
            </div>
            <div v-if="!isCdn" class="info-item">
              <span class="ql-muted">数据库账号</span>
              <strong class="mono">{{ sqlUser || '—' }}</strong>
            </div>
            <div class="info-spacer" />
            <div class="info-foot ql-muted">
              用量超限时系统可能暂停站点，可点右上角刷新重新计算。
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import {
  Refresh,
  Link,
  Key,
  FolderOpened,
  Monitor,
  Lock,
  DataAnalysis,
} from '@element-plus/icons-vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import CircleProgress from '../components/CircleProgress.vue'
import { fetchIndexConf, refreshUsage, changePhp } from '../api/user'

const router = useRouter()
const boot = window.__QL_BOOT__ || {}
const userName = boot.user || '用户'
const loading = ref(true)
const refreshing = ref(false)
const conf = ref(null)

const svgW = 640
const svgH = 220
const padL = 36
const padR = 16
const padT = 16
const padB = 12

const isCdn = computed(() => String(conf.value?.type || boot.productType || '') === '1')

function num(v, d = 0) {
  const n = Number(v)
  return Number.isFinite(n) ? n : d
}

function pct(dq, max) {
  const m = num(max)
  if (m <= 0) return 0
  return Math.min(100, (num(dq) / m) * 100)
}

function formatMb(v) {
  return `${num(v).toFixed(1)} MB`
}

function formatTraffic(bytes) {
  const b = num(bytes)
  if (b >= 1024 * 1024 * 1024) return `${(b / 1024 / 1024 / 1024).toFixed(2)} GB`
  if (b >= 1024 * 1024) return `${(b / 1024 / 1024).toFixed(1)} MB`
  if (b >= 1024) return `${(b / 1024).toFixed(0)} KB`
  return `${b} B`
}

const quotaCards = computed(() => {
  const c = conf.value || {}
  const web = c.web || {}
  const sql = c.sql || {}
  const lls = c.lls || {}
  const llsMax = num(lls.max)
  const llsDq = num(lls.dq)
  const llsPct = llsMax > 0 ? Math.min(100, (llsDq / (llsMax * 1024 * 1024 * 1024)) * 100) : 0
  return [
    {
      key: 'web',
      title: '网页空间',
      percent: pct(web.dq, web.max),
      usedText: formatMb(web.dq),
      maxText: formatMb(web.max),
      color: '#12b886',
    },
    {
      key: 'sql',
      title: '数据库',
      percent: pct(sql.dq, sql.max),
      usedText: formatMb(sql.dq),
      maxText: formatMb(sql.max),
      color: '#20c997',
    },
    {
      key: 'lls',
      title: '本月流量',
      percent: llsPct,
      usedText: formatTraffic(lls.dq),
      maxText: `${num(lls.max).toFixed(0)} GB`,
      color: '#0ca678',
    },
  ]
})

const statusRaw = computed(() => conf.value?.qk)
const statusText = computed(() => {
  const s = statusRaw.value
  if (s === true || s === 1 || s === '1' || s === 'true') return '运行中'
  if (s === false || s === 0 || s === '0' || s === 'false') return '已暂停'
  return '未知'
})
const statusTag = computed(() => {
  if (statusText.value === '运行中') return 'success'
  if (statusText.value === '已暂停') return 'danger'
  return 'info'
})

const domainLimit = computed(() => {
  const u = conf.value?.config?.url
  if (u === 0 || u === '0') return '无限制'
  return u == null || u === '' ? '—' : String(u)
})

const phpNow = computed(() => conf.value?.php?.dq || '')
const phpList = computed(() => {
  const list = conf.value?.php?.list
  if (!list) return []
  if (Array.isArray(list)) return list
  return Object.values(list)
})
const ftpHost = computed(() => conf.value?.config?.ftp?.host || '')
const ftpUser = computed(() => conf.value?.config?.ftp?.user || '')
const sqlUser = computed(() => conf.value?.config?.sql?.user || '')

/** 与 default 主题一致：history 为 { '2024-01': bytes, ... }，再追加本月 */
const series = computed(() => {
  const lls = conf.value?.lls || {}
  const history = lls.history || {}
  const labels = []
  const values = []
  if (history && typeof history === 'object' && !Array.isArray(history)) {
    Object.keys(history)
      .sort()
      .forEach((m) => {
        const mon = m.includes('-') ? parseInt(m.split('-')[1], 10) : m
        labels.push(`${mon}月`)
        values.push(+(num(history[m]) / (1024 * 1024 * 1024)).toFixed(2))
      })
  } else if (Array.isArray(history)) {
    history.forEach((x, i) => {
      labels.push(String(x.month ?? x.name ?? i + 1))
      const raw = x.value ?? x.dq ?? x
      const gb = num(raw) > 100 ? num(raw) / (1024 * 1024 * 1024) : num(raw)
      values.push(+gb.toFixed(2))
    })
  }
  labels.push('本月')
  values.push(+(num(lls.dq) / (1024 * 1024 * 1024)).toFixed(2))
  return { labels, values }
})

const chartPoints = computed(() => {
  const { labels, values } = series.value
  if (!labels.length) return []
  const maxV = Math.max(...values, 0.1)
  const n = values.length
  const plotW = svgW - padL - padR
  const plotH = svgH - padT - padB
  const slot = plotW / n
  const barW = Math.min(36, slot * 0.45)

  return values.map((v, i) => {
    const cx = padL + slot * i + slot / 2
    const ratio = v / maxV
    const bh = Math.max(v > 0 ? 4 : 0, ratio * plotH)
    const by = padT + plotH - bh
    const cy = v > 0 ? by : padT + plotH
    return {
      label: labels[i],
      value: v,
      cx,
      cy,
      bx: cx - barW / 2,
      by,
      bw: barW,
      bh,
    }
  })
})

const linePoints = computed(() =>
  chartPoints.value.map((p) => `${p.cx},${p.cy}`).join(' ')
)

const areaPath = computed(() => {
  const pts = chartPoints.value
  if (!pts.length) return ''
  const baseY = svgH - padB
  let d = `M ${pts[0].cx} ${baseY}`
  pts.forEach((p) => {
    d += ` L ${p.cx} ${p.cy}`
  })
  d += ` L ${pts[pts.length - 1].cx} ${baseY} Z`
  return d
})

const gridYs = computed(() => {
  const ys = []
  const plotH = svgH - padT - padB
  for (let i = 0; i <= 4; i++) {
    ys.push(padT + (plotH * i) / 4)
  }
  return ys
})

const trendHtml = computed(() => {
  const values = series.value.values
  if (values.length < 2) {
    const curr = values[0] ?? 0
    return `本月用量 <b style="color:#12b886">${curr.toFixed(2)} GB</b>`
  }
  const prev = values[values.length - 2]
  const curr = values[values.length - 1]
  if (prev <= 0) {
    return `本月用量 <b style="color:#12b886">${curr.toFixed(2)} GB</b>`
  }
  const pctChg = (((curr - prev) / prev) * 100).toFixed(1)
  const up = curr >= prev
  const color = up ? '#e03131' : '#12b886'
  const arrow = up ? '↑' : '↓'
  return `较上月 <b style="color:${color}">${arrow} ${Math.abs(curr - prev).toFixed(2)}GB (${pctChg >= 0 ? '+' : ''}${pctChg}%)</b>`
})

const shortcuts = computed(() => {
  if (isCdn.value) {
    return [
      { label: '域名修改', path: '/settings/CDN_url', icon: Link },
      { label: '站点统计', path: '/stats', icon: DataAnalysis },
    ]
  }
  return [
    { label: '域名', path: '/settings/url', icon: Link },
    { label: 'SSL', path: '/settings/ssl', icon: Lock },
    { label: '文件管理', path: '/ftp', icon: FolderOpened },
    { label: '监控', path: '/monitor', icon: Monitor },
    { label: '改密', path: '/settings/xgpass', icon: Key },
    { label: '统计', path: '/stats', icon: DataAnalysis },
  ]
})

function go(s) {
  if (s.path) router.push(s.path)
}

async function load() {
  loading.value = true
  const res = await fetchIndexConf()
  loading.value = false
  if (res.ok) {
    conf.value = res.data
    if (res.data?.gg) {
      const key = 'ql_gg_' + String(res.data.gg).slice(0, 32)
      if (!sessionStorage.getItem(key)) {
        sessionStorage.setItem(key, '1')
        ElMessage({ message: String(res.data.gg), type: 'info', duration: 5000, showClose: true })
      }
    }
  }
}

async function onRefresh() {
  refreshing.value = true
  const res = await refreshUsage()
  refreshing.value = false
  if (res.ok) {
    ElMessage.success(res.message || '已刷新')
    await load()
  }
}

async function onPhpChange(ver) {
  try {
    await ElMessageBox.confirm(`确认切换 PHP 版本为 ${ver}？`, 'PHP 版本', { type: 'warning' })
  } catch {
    return
  }
  const res = await changePhp(ver)
  if (res.ok) {
    ElMessage.success(res.message || '切换成功')
    await load()
  }
}

onMounted(load)
</script>

<style scoped>
.hero {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  margin-bottom: 20px;
  flex-wrap: wrap;
}
.hero h2 {
  margin: 0;
  font-size: 22px;
  font-weight: 700;
  letter-spacing: -0.02em;
}
.hero p {
  margin: 6px 0 0;
}
.quota-row {
  margin-bottom: 4px;
}
.quota-card {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 12px;
  margin-bottom: 16px;
  min-height: 220px;
  justify-content: center;
}
.quota-meta {
  text-align: center;
}
.used {
  font-weight: 700;
  font-size: 15px;
}

/* 左：趋势+快捷；右：主机信息等高拉伸 */
.main-grid {
  display: grid;
  grid-template-columns: 1fr 320px;
  gap: 16px;
  align-items: stretch;
}
@media (max-width: 991px) {
  .main-grid {
    grid-template-columns: 1fr;
  }
}
.main-left {
  display: flex;
  flex-direction: column;
  gap: 16px;
  min-width: 0;
}
.main-right {
  min-width: 0;
  display: flex;
}
.info-card {
  flex: 1;
  display: flex;
  flex-direction: column;
  width: 100%;
  margin: 0;
}
.info-list {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 16px;
  min-height: 0;
}
.info-spacer {
  flex: 1;
  min-height: 12px;
}
.info-foot {
  font-size: 12px;
  line-height: 1.5;
  padding-top: 8px;
  border-top: 1px dashed var(--ql-border);
}

.chart-card {
  margin: 0;
}
.chart-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  flex-wrap: wrap;
  margin-bottom: 8px;
}
.legend {
  display: flex;
  gap: 16px;
  justify-content: center;
  margin-bottom: 8px;
  font-size: 12px;
  color: var(--ql-text-secondary);
}
.leg {
  display: inline-flex;
  align-items: center;
  gap: 6px;
}
.dot {
  display: inline-block;
  width: 14px;
  height: 10px;
  border-radius: 3px;
}
.dot.bar {
  background: rgba(18, 184, 134, 0.55);
  border: 1px solid #12b886;
}
.dot.line {
  background: linear-gradient(90deg, #38d9a9, #12b886);
  border-radius: 2px;
  height: 3px;
  width: 16px;
}
.chart-area {
  position: relative;
  height: 240px;
}
.traffic-svg {
  width: 100%;
  height: 200px;
  display: block;
}
.grid {
  stroke: #e8f5ef;
  stroke-width: 1;
}
.bar-fill {
  fill: rgba(18, 184, 134, 0.45);
  stroke: #12b886;
  stroke-width: 1;
}
.area-fill {
  fill: rgba(18, 184, 134, 0.12);
}
.trend-line {
  stroke: #0ca678;
  stroke-width: 2.5;
  stroke-linejoin: round;
  stroke-linecap: round;
}
.trend-dot {
  fill: #12b886;
  stroke: #fff;
  stroke-width: 2;
}
.x-labels {
  display: flex;
  justify-content: space-around;
  padding: 0 16px 0 28px;
  font-size: 12px;
  color: var(--ql-text-secondary);
}
.chart-empty {
  height: 200px;
  display: grid;
  place-items: center;
  font-size: 13px;
}

.shortcut-card {
  margin: 0;
}
.shortcut-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 12px;
}
@media (max-width: 600px) {
  .shortcut-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}
.shortcut {
  border: 1px solid var(--ql-border);
  background: var(--ql-primary-soft);
  border-radius: 14px;
  padding: 16px 12px;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
  cursor: pointer;
  color: var(--ql-primary-dark);
  font-size: 13px;
  font-weight: 600;
  transition: transform 0.15s ease, box-shadow 0.15s ease;
}
.shortcut:hover {
  transform: translateY(-2px);
  box-shadow: var(--ql-shadow);
}
.shortcut:active {
  transform: scale(0.98);
}
.info-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  font-size: 13px;
}
.info-item strong {
  font-weight: 600;
}
.mono {
  font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
  font-size: 12px;
}
.php-row {
  display: flex;
  align-items: center;
  gap: 8px;
}
</style>
