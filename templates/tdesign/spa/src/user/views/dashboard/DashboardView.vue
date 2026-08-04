<template>
  <div class="td-page td-user-dashboard">
    <!-- 欢迎区 -->
    <section class="hero">
      <div class="hero-left">
        <h2 class="hero-title">{{ greeting }},{{ user }}</h2>
        <p class="hero-sub">
          欢迎回到 {{ siteName }} 控制面板 ·
          <span class="hero-time">{{ nowTime }}</span>
        </p>
      </div>
      <div class="hero-right">
        <t-button theme="primary" @click="$router.push('/settings/password')">
          <i class="mdi mdi-key-variant"></i>
          修改密码
        </t-button>
      </div>
    </section>

    <!-- 资源使用 gauge 仪表盘 -->
    <section class="section">
      <div class="section-title">
        <i class="mdi mdi-chart-arc"></i>
        <span>资源使用</span>
      </div>
      <div class="gauge-grid">
        <!-- 网页空间 -->
        <div class="gauge-card">
          <div class="gauge-head">
            <div class="gauge-title">
              <i class="mdi mdi-web"></i>
              <span>网页空间</span>
            </div>
            <t-tag theme="default" size="small" shape="round" variant="light">
              {{ formatSpaceText(hxa) }}
            </t-tag>
          </div>
          <div ref="hxaGaugeRef" class="gauge-body"></div>
          <div class="gauge-foot">
            已用 {{ hxa.dq || 0 }} MB · {{ hxa.max === 0 ? '不限上限' : `上限 ${hxa.max} MB` }}
          </div>
        </div>

        <!-- 数据库空间 -->
        <div class="gauge-card">
          <div class="gauge-head">
            <div class="gauge-title">
              <i class="mdi mdi-database"></i>
              <span>数据库空间</span>
            </div>
            <t-tag theme="default" size="small" shape="round" variant="light">
              {{ formatSpaceText(hxb) }}
            </t-tag>
          </div>
          <div ref="hxbGaugeRef" class="gauge-body"></div>
          <div class="gauge-foot">
            已用 {{ hxb.dq || 0 }} MB · {{ hxb.max === 0 ? '不限上限' : `上限 ${hxb.max} MB` }}
          </div>
        </div>

        <!-- 流量使用 -->
        <div class="gauge-card">
          <div class="gauge-head">
            <div class="gauge-title">
              <i class="mdi mdi-chart-line-variant"></i>
              <span>流量使用</span>
            </div>
            <t-tag theme="default" size="small" shape="round" variant="light">
              {{ formatFlowText(llmax) }}
            </t-tag>
          </div>
          <div ref="llmaxGaugeRef" class="gauge-body"></div>
          <div class="gauge-foot">
            已用 {{ llmax.dq || 0 }} G · {{ llmax.max === 0 ? '不限上限' : `上限 ${llmax.max} G` }}
          </div>
        </div>
      </div>
    </section>

    <!-- 站点信息：顶部平铺快捷操作 + 下方信息卡片 -->
    <section class="section">
      <div class="section-title">
        <i class="mdi mdi-information-outline"></i>
        <span>站点信息</span>
      </div>

      <!-- 顶部平铺快捷操作区（宽度与下方信息区相同）-->
      <div class="quick-actions-bar">
        <div class="quick-actions-head">
          <i class="mdi mdi-lightning-bolt"></i>
          <span>快捷操作</span>
        </div>
        <div class="quick-actions-grid">
          <router-link
            v-for="item in quickNavItems"
            :key="item.path"
            :to="item.path"
            custom
            v-slot="{ navigate, isActive }"
          >
            <a
              href="javascript:;"
              class="quick-action-btn"
              :class="{ active: isActive }"
              @click="navigate"
            >
              <i class="mdi quick-action-icon" :class="item.icon"></i>
              <span class="quick-action-label">{{ item.label }}</span>
            </a>
          </router-link>
        </div>
      </div>

      <!-- 信息卡片网格 -->
      <div class="info-grid">
          <!-- 基本信息 -->
          <div class="info-card">
            <div class="info-card-head">
              <div class="info-card-icon info-card-icon-blue">
                <i class="mdi mdi-earth"></i>
              </div>
              <div class="info-card-name">
                <strong>基本信息</strong>
                <span>Basic Information</span>
              </div>
              <t-tag
                :theme="siteStatusTag.theme"
                size="small"
                shape="round"
                variant="light"
              >
                <i class="mdi mdi-circle-medium"></i>{{ siteStatusTag.text }}
              </t-tag>
            </div>
            <table class="info-tbl">
              <tbody>
                <tr><td>主域名</td><td>{{ yhc.url || yhc.domain || '-' }}</td></tr>
                <tr><td>所属宝塔</td><td>{{ yhc.ssbt || yhc.bt || '-' }}</td></tr>
                <tr><td>开通时间</td><td>{{ yhc.data || '-' }}</td></tr>
                <tr>
                  <td>到期时间</td>
                  <td :class="{ 'td-text-danger': isExpired(yhc.datae) }">
                    {{ yhc.datae || '永久' }}
                    <t-tag v-if="isExpired(yhc.datae)" theme="danger" size="small" variant="light" shape="round">已到期</t-tag>
                  </td>
                </tr>
                <tr><td>主机状态</td><td>{{ siteStatusTag.text }}</td></tr>
              </tbody>
            </table>
          </div>

          <!-- 账号信息 -->
          <div class="info-card">
            <div class="info-card-head">
              <div class="info-card-icon info-card-icon-purple">
                <i class="mdi mdi-account-key"></i>
              </div>
              <div class="info-card-name">
                <strong>账号信息</strong>
                <span>Account Credentials</span>
              </div>
            </div>
            <table class="info-tbl">
              <tbody>
                <tr><td>FTP 账号</td><td>{{ yhc.user || '-' }}</td></tr>
                <tr><td>FTP 密码</td><td>••••••••（已隐藏）</td></tr>
                <tr><td>SQL 账号</td><td>{{ yhc.sqluser || '-' }}</td></tr>
                <tr><td>SQL 密码</td><td>••••••••（已隐藏）</td></tr>
                <tr><td>SQL 数据库</td><td>{{ yhc.sqldz || '-' }}</td></tr>
                <tr><td>SQL ID</td><td>{{ yhc.hxd || '-' }}</td></tr>
              </tbody>
            </table>
          </div>

          <!-- 配额信息 -->
          <div class="info-card">
            <div class="info-card-head">
              <div class="info-card-icon info-card-icon-green">
                <i class="mdi mdi-scale"></i>
              </div>
              <div class="info-card-name">
                <strong>配额信息</strong>
                <span>Resource Quota</span>
              </div>
            </div>
            <table class="info-tbl">
              <tbody>
                <tr><td>网页空间</td><td>{{ formatSpaceText(hxa) }}</td></tr>
                <tr><td>数据库空间</td><td>{{ formatSpaceText(hxb) }}</td></tr>
                <tr><td>流量上限</td><td>{{ formatFlowText(llmax) }}</td></tr>
                <tr><td>域名绑定数</td><td>{{ yhc.ymbds || '不限' }} 个</td></tr>
                <tr><td>宝塔站点 ID</td><td>{{ yhc.btid || '-' }}</td></tr>
                <tr><td>FTP ID</td><td>{{ yhc.ftpid || '-' }}</td></tr>
              </tbody>
            </table>
          </div>

          <!-- 联系信息 -->
          <div class="info-card">
            <div class="info-card-head">
              <div class="info-card-icon info-card-icon-orange">
                <i class="mdi mdi-email-outline"></i>
              </div>
              <div class="info-card-name">
                <strong>联系信息</strong>
                <span>Contact Information</span>
              </div>
            </div>
            <table class="info-tbl">
              <tbody>
                <tr>
                  <td>绑定邮箱</td>
                  <td>
                    <span v-if="yhc.mailuser">{{ yhc.mailuser }}</span>
                    <span v-else class="td-text-warning">未绑定</span>
                  </td>
                </tr>
                <tr><td>主机 ID</td><td>#{{ yhc.id || '-' }}</td></tr>
                <tr><td>产品类型</td><td>{{ productTypeText }}</td></tr>
                <tr><td>服务器时间</td><td>{{ nowTime }}</td></tr>
              </tbody>
            </table>
            <div class="info-card-actions">
              <t-button
                v-if="!yhc.mailuser"
                theme="primary"
                size="small"
                variant="outline"
                @click="showMailBind = true"
              >
                <i class="mdi mdi-email-plus-outline"></i> 绑定邮箱
              </t-button>
              <t-button
                theme="default"
                size="small"
                variant="outline"
                @click="$router.push('/settings/password')"
              >
                <i class="mdi mdi-key-change"></i> 修改密码
              </t-button>
            </div>
          </div>
        </div>
    </section>

    <!-- 月度流量趋势 echarts 图表 -->
    <section class="section">
      <div class="section-title">
        <i class="mdi mdi-chart-bar"></i>
        <span>月度流量趋势</span>
        <span v-if="trendText" class="trend-indicator" v-html="trendText"></span>
      </div>
      <div class="chart-card">
        <div ref="trafficChartRef" class="traffic-chart"></div>
        <div v-if="!hasChartData" class="chart-empty">
          <i class="mdi mdi-chart-bar-stacked"></i>
          <span>暂无流量数据</span>
        </div>
      </div>
    </section>

    <!-- 邮箱绑定弹窗 -->
    <MailBindDialog
      v-model:visible="showMailBind"
      :required="mailBindRequired"
      @success="onMailBound"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount, nextTick, watch } from 'vue'
import { refreshSpace, getIndexConf } from '@/user/api/common'
import MailBindDialog from '@/user/components/MailBindDialog.vue'
import echarts from '@/shared/utils/echarts'

const boot = window.__TD_BOOT__ || {}
const siteName = boot.siteName || 'MNBT'
const user = boot.user || 'user'
const yhc = boot.yhc || {}

const nowTime = ref('')
let timer = null

// ============== 弹窗控制 ==============
const showMailBind = ref(false)
// 邮箱未绑定时,自动弹出且不允许关闭
const mailBindRequired = ref(!yhc.mailuser)

// ============== 空间数据 ==============
function parseSpace(v) {
  if (v == null) return { dq: 0, max: 0 }
  if (typeof v === 'object') return { dq: Number(v.dq) || 0, max: Number(v.max) || 0 }
  try {
    const o = JSON.parse(v)
    return { dq: Number(o.dq) || 0, max: Number(o.max) || 0 }
  } catch {
    return { dq: 0, max: 0 }
  }
}

const hxa = ref(parseSpace(yhc.hxa))
const hxb = ref(parseSpace(yhc.hxb))
const llmax = ref(parseSpace(yhc.llmax))

// 保存原始 lls 数据,用于绘制趋势图
const rawLls = ref(null)

function calcPercent(o) {
  if (!o.max || o.max === 0) return 0
  return Math.min(100, Math.round((o.dq / o.max) * 100))
}

const hxaPercent = computed(() => calcPercent(hxa.value))
const hxbPercent = computed(() => calcPercent(hxb.value))
const llmaxPercent = computed(() => calcPercent(llmax.value))

function formatSpaceText(o) {
  if (o.max === 0) return `${o.dq || 0} / ∞ MB`
  return `${o.dq || 0} / ${o.max} MB`
}
function formatFlowText(o) {
  if (o.max === 0) return `${o.dq || 0} / ∞ G`
  return `${o.dq || 0} / ${o.max} G`
}

function isExpired(datae) {
  if (!datae) return false
  const d = new Date(datae)
  if (isNaN(d.getTime())) return false
  return d.getTime() < Date.now()
}

const siteStatusTag = computed(() => {
  const off = yhc.qk === false || yhc.qk === 'false' || yhc.qk === 0 || yhc.qk === '0'
  if (off) return { theme: 'danger', text: '已关闭' }
  if (isExpired(yhc.datae)) return { theme: 'warning', text: '已到期' }
  return { theme: 'success', text: '正常运行' }
})

const productTypeText = computed(() => {
  const t = yhc.hxc
  if (t === '2' || t === 2) return '虚拟主机'
  if (t === '1' || t === 1) return '云服务器'
  return t ? `类型 ${t}` : '虚拟主机'
})

// ============== 快捷跳转栏 ==============
const quickNavItems = [
  { path: '/settings/php', label: 'PHP 版本', icon: 'mdi-language-php' },
  { path: '/settings/ssl', label: 'SSL 配置', icon: 'mdi-shield-lock-outline' },
  { path: '/settings/rewrite', label: '伪静态', icon: 'mdi-file-replace-outline' },
  { path: '/settings/run-dir', label: '运行目录', icon: 'mdi-folder-outline' },
  { path: '/ftp', label: '文件管理', icon: 'mdi-folder-multiple-outline' },
  { path: '/monitor', label: '监控任务', icon: 'mdi-radar' },
  { path: '/stats', label: '站点统计', icon: 'mdi-chart-bar' },
  { path: '/deploy', label: '一键部署', icon: 'mdi-webpack' },
]

const greeting = computed(() => {
  const h = new Date().getHours()
  if (h < 6) return '凌晨好'
  if (h < 9) return '早上好'
  if (h < 12) return '上午好'
  if (h < 14) return '中午好'
  if (h < 18) return '下午好'
  return '晚上好'
})

function tickTime() {
  nowTime.value = new Date().toLocaleString('zh-CN', { hour12: false })
}

async function loadSpace() {
  const r = await refreshSpace()
  if (r.ok && r.data) {
    const d = r.data
    if (d.hxa) hxa.value = parseSpace(d.hxa)
    if (d.hxb) hxb.value = parseSpace(d.hxb)
    if (d.llmax) llmax.value = parseSpace(d.llmax)
    nextTick(renderGauges)
  }
}

/** 从 indexconf 合并空间/流量数据 */
function applyIndexConf(conf) {
  if (!conf) return
  if (conf.web) {
    hxa.value = { dq: Number(conf.web.dq) || 0, max: Number(conf.web.max) || 0 }
  }
  if (conf.sql) {
    hxb.value = { dq: Number(conf.sql.dq) || 0, max: Number(conf.sql.max) || 0 }
  }
  if (conf.lls) {
    rawLls.value = conf.lls
    const dqBytes = Number(conf.lls.dq) || 0
    const maxG = Number(conf.lls.max) || 0
    llmax.value = { dq: +(dqBytes / (1024 * 1024 * 1024)).toFixed(2), max: maxG }
    nextTick(() => {
      renderGauges()
      renderChart()
    })
  }
}

async function loadIndexConf() {
  const r = await getIndexConf()
  if (r.ok && r.data) applyIndexConf(r.data)
}

function onMailBound(mail) {
  // 同步更新 boot.yhc,避免再次自动弹出
  window.__TD_BOOT__ = {
    ...(window.__TD_BOOT__ || {}),
    yhc: { ...(window.__TD_BOOT__?.yhc || {}), mailuser: mail },
  }
  mailBindRequired.value = false
}

// ============== echarts gauge 仪表盘 ==============
const hxaGaugeRef = ref(null)
const hxbGaugeRef = ref(null)
const llmaxGaugeRef = ref(null)
let hxaChart = null
let hxbChart = null
let llmaxChart = null

function buildGaugeOption(value, opts = {}) {
  const { color = '#0052d9', unlimited = false } = opts
  let theme = '#2ba471'
  if (value > 80) theme = '#d54941'
  else if (value > 60) theme = '#e37318'

  // 不限时显示"不限"而非 0%
  const detailFormatter = unlimited ? '不限' : `{value}%`

  return {
    series: [
      {
        type: 'gauge',
        startAngle: 210,
        endAngle: -30,
        min: 0,
        max: 100,
        progress: {
          show: !unlimited,
          width: 12,
          roundCap: true,
          itemStyle: { color: theme },
        },
        axisLine: {
          lineStyle: {
            width: 12,
            color: [[1, '#ebedf0']],
          },
        },
        pointer: { show: false },
        axisTick: { show: false },
        splitLine: { show: false },
        axisLabel: { show: false },
        anchor: { show: false },
        title: { show: false },
        detail: {
          valueAnimation: true,
          fontSize: 24,
          fontWeight: 700,
          color: theme,
          offsetCenter: [0, '0%'],
          formatter: detailFormatter,
        },
        data: [{ value: unlimited ? 0 : value }],
        color,
      },
    ],
  }
}

function renderGauges() {
  if (hxaGaugeRef.value) {
    if (!hxaChart) hxaChart = echarts.init(hxaGaugeRef.value)
    hxaChart.setOption(buildGaugeOption(hxaPercent.value, {
      color: '#0052d9',
      unlimited: hxa.value.max === 0,
    }))
  }
  if (hxbGaugeRef.value) {
    if (!hxbChart) hxbChart = echarts.init(hxbGaugeRef.value)
    hxbChart.setOption(buildGaugeOption(hxbPercent.value, {
      color: '#7c3aed',
      unlimited: hxb.value.max === 0,
    }))
  }
  if (llmaxGaugeRef.value) {
    if (!llmaxChart) llmaxChart = echarts.init(llmaxGaugeRef.value)
    llmaxChart.setOption(buildGaugeOption(llmaxPercent.value, {
      color: '#e37318',
      unlimited: llmax.value.max === 0,
    }))
  }
}

function resizeGauges() {
  hxaChart?.resize()
  hxbChart?.resize()
  llmaxChart?.resize()
}

// ============== echarts 月度流量趋势图 ==============
const trafficChartRef = ref(null)
let chartInstance = null
const trendText = ref('')
const hasChartData = ref(false)

function buildChartData() {
  const lls = rawLls.value
  if (!lls) return { labels: [], values: [] }
  const history = lls.history || {}
  const months = Object.keys(history).sort()
  const labels = []
  const values = []
  months.forEach(m => {
    const mm = parseInt(String(m).split('-')[1], 10)
    labels.push(isNaN(mm) ? m : mm + '月')
    values.push(+(Number(history[m]) / (1024 * 1024 * 1024)).toFixed(2))
  })
  // 追加本月
  const curDq = Number(lls.dq) || 0
  if (curDq > 0 || labels.length > 0) {
    labels.push('本月')
    values.push(+(curDq / (1024 * 1024 * 1024)).toFixed(2))
  }
  return { labels, values }
}

function calcTrend(values) {
  if (!values || values.length < 2) {
    if (values && values.length === 1) {
      trendText.value = `本月用量 <span style="color:#7367f0;font-weight:bold">${values[0].toFixed(2)} GB</span>`
    } else {
      trendText.value = ''
    }
    return
  }
  const prev = values[values.length - 2]
  const curr = values[values.length - 1]
  if (prev > 0) {
    const pct = ((curr - prev) / prev * 100).toFixed(1)
    const arrow = curr >= prev ? '↑' : '↓'
    const color = curr >= prev ? '#ea5455' : '#28c76f'
    trendText.value =
      `较上月 <span style="color:${color};font-weight:bold">${arrow} ${Math.abs(curr - prev).toFixed(2)} GB` +
      ` (${pct >= 0 ? '+' : ''}${pct}%)</span>`
  } else {
    trendText.value = `本月用量 <span style="color:#7367f0;font-weight:bold">${curr.toFixed(2)} GB</span>`
  }
}

function buildOption(labels, values) {
  return {
    grid: { top: 36, right: 18, bottom: 30, left: 42 },
    tooltip: {
      trigger: 'axis',
      axisPointer: { type: 'shadow' },
      valueFormatter: v => (v == null ? '-' : Number(v).toFixed(2) + ' GB'),
    },
    legend: {
      top: 4,
      right: 8,
      data: ['流量用量 (GB)', '趋势'],
      icon: 'roundRect',
      itemWidth: 14,
      itemHeight: 8,
      textStyle: { fontSize: 12 },
    },
    xAxis: {
      type: 'category',
      data: labels,
      axisLine: { lineStyle: { color: '#e3e8ef' } },
      axisLabel: { color: '#666', fontSize: 12 },
      axisTick: { show: false },
    },
    yAxis: {
      type: 'value',
      name: 'GB',
      nameTextStyle: { color: '#999', fontSize: 11 },
      axisLine: { show: false },
      axisLabel: { color: '#666', fontSize: 12 },
      splitLine: { lineStyle: { color: '#f0f2f5', type: 'dashed' } },
    },
    series: [
      {
        name: '流量用量 (GB)',
        type: 'bar',
        data: values,
        barWidth: '46%',
        itemStyle: {
          color: {
            type: 'linear',
            x: 0, y: 0, x2: 0, y2: 1,
            colorStops: [
              { offset: 0, color: 'rgba(23, 162, 184, 0.85)' },
              { offset: 1, color: 'rgba(23, 162, 184, 0.45)' },
            ],
          },
          borderRadius: [4, 4, 0, 0],
        },
      },
      {
        name: '趋势',
        type: 'line',
        data: values,
        smooth: true,
        symbol: 'circle',
        symbolSize: 7,
        lineStyle: { color: '#7367f0', width: 2 },
        itemStyle: { color: '#7367f0', borderColor: '#fff', borderWidth: 2 },
        areaStyle: {
          color: {
            type: 'linear',
            x: 0, y: 0, x2: 0, y2: 1,
            colorStops: [
              { offset: 0, color: 'rgba(115, 103, 240, 0.18)' },
              { offset: 1, color: 'rgba(115, 103, 240, 0)' },
            ],
          },
        },
      },
    ],
  }
}

function renderChart() {
  if (!trafficChartRef.value) return
  const { labels, values } = buildChartData()
  hasChartData.value = values.length > 0
  calcTrend(values)
  if (!hasChartData.value) return

  if (!chartInstance) {
    chartInstance = echarts.init(trafficChartRef.value)
  }
  chartInstance.setOption(buildOption(labels, values), true)

  // 自适应容器尺寸
  chartInstance.resize()
}

function handleResize() {
  resizeGauges()
  if (chartInstance) chartInstance.resize()
}

// 数据更新后重绘
watch([rawLls, hxa, hxb, llmax], () => {
  nextTick(() => {
    renderGauges()
    renderChart()
  })
})

onMounted(() => {
  tickTime()
  timer = setInterval(tickTime, 1000)
  loadSpace()
  loadIndexConf()
  // 强制邮箱绑定时自动弹出
  if (mailBindRequired.value) {
    nextTick(() => { showMailBind.value = true })
  }
  window.addEventListener('resize', handleResize)
  // 首次渲染 gauge（基于 boot 数据）
  nextTick(renderGauges)
})

onBeforeUnmount(() => {
  if (timer) clearInterval(timer)
  window.removeEventListener('resize', handleResize)
  if (chartInstance) {
    chartInstance.dispose()
    chartInstance = null
  }
  hxaChart?.dispose()
  hxbChart?.dispose()
  llmaxChart?.dispose()
})
</script>

<style scoped>
.td-user-dashboard {
  padding: 20px;
}

/* ============== 欢迎区 ============== */
.hero {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  padding: 22px 24px;
  margin-bottom: 18px;
  background: linear-gradient(135deg, #ffffff 0%, #f5f8ff 100%);
  border: 1px solid var(--td-border);
  border-radius: var(--td-radius-xl);
  box-shadow: var(--td-shadow-sm);
  flex-wrap: wrap;
  animation: td-fade-in var(--td-dur-lg) var(--td-ease-out);
}
.hero-left {
  min-width: 0;
  flex: 1;
}
.hero-title {
  margin: 0;
  font-size: 22px;
  font-weight: 700;
  color: var(--td-text);
  letter-spacing: 0.3px;
  line-height: 1.3;
}
.hero-sub {
  margin: 6px 0 0;
  font-size: 13px;
  color: var(--td-text-secondary);
}
.hero-time {
  color: var(--td-text-placeholder);
  font-variant-numeric: tabular-nums;
}
.hero-right {
  display: flex;
  align-items: center;
  gap: 10px;
  flex-wrap: wrap;
}

/* ============== Section ============== */
.section {
  margin-top: 22px;
}
.section-title {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 15px;
  font-weight: 600;
  margin: 0 0 14px 0;
  color: var(--td-text);
  letter-spacing: 0.2px;
}
.section-title i {
  font-size: 18px;
  color: var(--td-brand);
}
.trend-indicator {
  margin-left: auto;
  font-size: 12px;
  font-weight: 400;
  color: var(--td-text-secondary);
}

/* ============== Gauge 仪表盘 ============== */
.gauge-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
  gap: 14px;
}
.gauge-card {
  background: var(--td-surface);
  border: 1px solid var(--td-border);
  border-radius: var(--td-radius-lg);
  box-shadow: var(--td-shadow-sm);
  overflow: hidden;
  transition: box-shadow var(--td-dur) var(--td-ease);
}
.gauge-card:hover {
  box-shadow: var(--td-shadow-md);
}
.gauge-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 8px;
  padding: 14px 16px 0;
}
.gauge-title {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 13px;
  font-weight: 600;
  color: var(--td-text);
}
.gauge-title i {
  font-size: 16px;
  color: var(--td-brand);
}
.gauge-body {
  width: 100%;
  height: 180px;
}
.gauge-foot {
  padding: 0 16px 14px;
  font-size: 11px;
  color: var(--td-text-placeholder);
  text-align: center;
}

/* ============== 图表卡片 ============== */
.chart-card {
  position: relative;
  background: var(--td-surface);
  border: 1px solid var(--td-border);
  border-radius: var(--td-radius-lg);
  box-shadow: var(--td-shadow-sm);
  padding: 14px 16px;
}
.traffic-chart {
  width: 100%;
  height: 280px;
}
.chart-empty {
  position: absolute;
  inset: 0;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 8px;
  color: var(--td-text-placeholder);
  font-size: 13px;
  pointer-events: none;
}
.chart-empty i {
  font-size: 38px;
  color: #cbd5e1;
}

/* ============== 快捷操作平铺区 ============== */
.quick-actions-bar {
  background: var(--td-surface);
  border: 1px solid var(--td-border);
  border-radius: var(--td-radius-lg);
  box-shadow: var(--td-shadow-sm);
  overflow: hidden;
  margin-bottom: 14px;
}
.quick-actions-head {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 12px 16px;
  font-size: 13px;
  font-weight: 600;
  color: var(--td-text);
  border-bottom: 1px solid var(--td-border);
  background: linear-gradient(135deg, #fffbe6 0%, #ffffff 100%);
}
.quick-actions-head i {
  font-size: 16px;
  color: #e37318;
}
.quick-actions-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(110px, 1fr));
  gap: 6px;
  padding: 10px;
}
.quick-action-btn {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 6px;
  padding: 14px 8px;
  border-radius: 10px;
  color: var(--td-text-secondary);
  font-size: 12px;
  text-decoration: none;
  cursor: pointer;
  text-align: center;
  transition: background var(--td-dur) var(--td-ease),
              color var(--td-dur) var(--td-ease),
              transform var(--td-dur) var(--td-ease),
              box-shadow var(--td-dur) var(--td-ease);
}
.quick-action-icon {
  font-size: 22px;
  color: var(--td-text-placeholder);
  transition: color var(--td-dur) var(--td-ease),
              transform var(--td-dur) var(--td-ease);
}
.quick-action-label {
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  max-width: 100%;
}
.quick-action-btn:hover {
  background: var(--td-brand-light, #e8f3ff);
  color: var(--td-brand);
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 82, 217, 0.08);
}
.quick-action-btn:hover .quick-action-icon {
  color: var(--td-brand);
  transform: scale(1.1);
}
.quick-action-btn.active {
  background: var(--td-brand-light, #e8f3ff);
  color: var(--td-brand);
  font-weight: 500;
}
.quick-action-btn.active .quick-action-icon {
  color: var(--td-brand);
}

/* ============== 信息卡片 ============== */
.info-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 14px;
}
.info-card {
  background: var(--td-surface);
  border: 1px solid var(--td-border);
  border-radius: var(--td-radius-lg);
  box-shadow: var(--td-shadow-sm);
  overflow: hidden;
  transition: box-shadow var(--td-dur) var(--td-ease);
  display: flex;
  flex-direction: column;
}
.info-card:hover {
  box-shadow: var(--td-shadow-md);
}
.info-card-head {
  padding: 14px 16px;
  border-bottom: 1px solid var(--td-border);
  display: flex;
  align-items: center;
  gap: 10px;
}
.info-card-icon {
  width: 36px;
  height: 36px;
  border-radius: 8px;
  display: grid;
  place-items: center;
  font-size: 18px;
  flex-shrink: 0;
}
.info-card-icon-blue   { background: #e8f3ff; color: #0052d9; }
.info-card-icon-purple { background: #f3e8ff; color: #7c3aed; }
.info-card-icon-green  { background: #e8f8f0; color: #2ba471; }
.info-card-icon-orange { background: #fff3e0; color: #e37318; }
.info-card-name {
  flex: 1;
  min-width: 0;
  display: flex;
  flex-direction: column;
}
.info-card-name strong {
  font-size: 14px;
  font-weight: 600;
  color: var(--td-text);
}
.info-card-name span {
  font-size: 11px;
  color: var(--td-text-placeholder);
  margin-top: 1px;
}
.info-tbl {
  width: 100%;
  border-collapse: collapse;
  flex: 1;
}
.info-tbl td {
  padding: 8px 16px;
  font-size: 13px;
  border-bottom: 1px solid #f5f6f8;
}
.info-tbl tr:last-child td {
  border-bottom: none;
}
.info-tbl td:first-child {
  color: var(--td-text-secondary);
  white-space: nowrap;
  width: 100px;
}
.info-tbl td:last-child {
  color: var(--td-text);
  word-break: break-all;
}
.info-card-actions {
  display: flex;
  gap: 8px;
  padding: 12px 16px;
  border-top: 1px solid #f5f6f8;
  flex-wrap: wrap;
}

.td-text-danger {
  color: var(--td-error, #d54941);
}
.td-text-warning {
  color: #e37318;
}

@media (max-width: 991px) {
  .quick-actions-grid {
    grid-template-columns: repeat(auto-fit, minmax(90px, 1fr));
  }
  .quick-action-btn {
    padding: 10px 6px;
  }
}

@media (max-width: 600px) {
  .td-user-dashboard { padding: 14px; }
  .hero { padding: 16px; }
  .hero-title { font-size: 18px; }
  .traffic-chart { height: 240px; }
  .gauge-body { height: 150px; }
}
</style>
