<template>
  <div class="td-page td-dashboard">
    <!-- 顶部横幅广告位 -->
    <div v-if="bannerVisible" class="ad-banner">
      <a href="https://www.qimingidc.cn/" target="_blank" rel="noopener" class="ad-link">
        <img :src="adImg" alt="广告" class="ad-img" />
      </a>
      <button class="ad-close" title="关闭广告" @click="closeBanner">
        <i class="mdi mdi-close"></i>
      </button>
    </div>

    <!-- 欢迎区 -->
    <section class="hero">
      <div class="hero-left">
        <h2 class="hero-title">{{ greeting }},{{ adminUser }}</h2>
        <p class="hero-sub">
          欢迎回到 {{ siteName }} 管理后台 ·
          <span class="hero-time">{{ nowTime }}</span>
        </p>
      </div>
      <div class="hero-right">
        <t-tag :theme="updateTagTheme" variant="light" shape="round" size="medium">
          <i class="mdi" :class="updateIcon"></i>
          <span style="margin-left: 4px">{{ updateText }}</span>
        </t-tag>
        <t-button theme="primary" @click="$router.push('/settings/website')">
          <i class="mdi mdi-cog-outline"></i>
          站点设置
        </t-button>
      </div>
    </section>

    <!-- 统计卡片 -->
    <section class="stat-row">
      <div class="stat-card" v-for="card in statCards" :key="card.label">
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

    <!-- 资源监控 gauge -->
    <section class="section">
      <div class="section-title">
        <i class="mdi mdi-chart-arc"></i>
        <span>资源使用</span>
      </div>
      <div class="gauge-grid">
        <div class="gauge-card">
          <div class="gauge-head">
            <div class="gauge-title">
              <i class="mdi mdi-harddisk"></i>
              <span>磁盘</span>
            </div>
            <t-tag theme="default" size="small" shape="round" variant="light">
              {{ fmtBytes(info.diskUsed) }} / {{ fmtBytes(info.diskTotal) }}
            </t-tag>
          </div>
          <div ref="diskGaugeRef" class="gauge-body"></div>
          <div class="gauge-foot">可用 {{ fmtBytes(info.diskFree) }}</div>
        </div>

        <div class="gauge-card">
          <div class="gauge-head">
            <div class="gauge-title">
              <i class="mdi mdi-cpu-64-bit"></i>
              <span>CPU 负载</span>
            </div>
            <t-tag theme="default" size="small" shape="round" variant="light">
              {{ fmtLoad(0) }} / {{ fmtLoad(1) }} / {{ fmtLoad(2) }}
            </t-tag>
          </div>
          <div ref="cpuGaugeRef" class="gauge-body"></div>
          <div class="gauge-foot">1 / 5 / 15 分钟</div>
        </div>

        <div class="gauge-card">
          <div class="gauge-head">
            <div class="gauge-title">
              <i class="mdi mdi-memory"></i>
              <span>PHP 内存</span>
            </div>
            <t-tag theme="default" size="small" shape="round" variant="light">
              {{ fmtBytes(info.memCurrent) }} / 峰值 {{ fmtBytes(info.memPeak) }}
            </t-tag>
          </div>
          <div ref="memGaugeRef" class="gauge-body"></div>
          <div class="gauge-foot">内存限制 {{ info.memoryLimit }}</div>
        </div>
      </div>
    </section>

    <!-- 系统信息 -->
    <section class="section">
      <div class="section-title">
        <i class="mdi mdi-information-outline"></i>
        <span>系统信息</span>
      </div>
      <div class="info-grid">
        <div class="info-card">
          <div class="info-card-head">
            <div class="info-card-icon info-card-icon-blue">
              <i class="mdi mdi-server"></i>
            </div>
            <div class="info-card-name">
              <strong>服务器</strong>
              <span>Server Environment</span>
            </div>
            <t-tag theme="success" size="small" shape="round" variant="light">
              <i class="mdi mdi-circle-medium"></i>运行中
            </t-tag>
          </div>
          <table class="info-tbl">
            <tbody>
              <tr><td>操作系统</td><td>{{ info.os || '-' }}</td></tr>
              <tr><td>主机名</td><td>{{ info.hostname || '-' }}</td></tr>
              <tr><td>Web 服务</td><td>{{ info.serverSoft || '-' }}</td></tr>
              <tr><td>IP : 端口</td><td>{{ info.serverIp }} : {{ info.serverPort }}</td></tr>
              <tr><td>服务器时间</td><td>{{ info.serverTime }}</td></tr>
              <tr><td>时区</td><td>{{ info.timezone }}</td></tr>
            </tbody>
          </table>
        </div>

        <div class="info-card">
          <div class="info-card-head">
            <div class="info-card-icon info-card-icon-purple">
              <i class="mdi mdi-language-php"></i>
            </div>
            <div class="info-card-name">
              <strong>PHP 信息</strong>
              <span>PHP Runtime</span>
            </div>
            <t-tag theme="primary" size="small" shape="round" variant="light">{{ info.phpVersion }}</t-tag>
          </div>
          <table class="info-tbl">
            <tbody>
              <tr><td>PHP 版本</td><td>{{ info.phpVersion }}</td></tr>
              <tr><td>运行模式</td><td>{{ info.phpSapi }}</td></tr>
              <tr><td>内存限制</td><td>{{ info.memoryLimit }}</td></tr>
              <tr><td>最大执行时间</td><td>{{ info.maxExecTime }}s</td></tr>
              <tr><td>上传限制</td><td>{{ info.uploadMax }}</td></tr>
              <tr><td>POST 限制</td><td>{{ info.postMax }}</td></tr>
              <tr><td>已加载扩展</td><td>{{ info.extCount }} 个</td></tr>
            </tbody>
          </table>
        </div>

        <div class="info-card">
          <div class="info-card-head">
            <div class="info-card-icon info-card-icon-green">
              <i class="mdi mdi-database"></i>
            </div>
            <div class="info-card-name">
              <strong>数据库</strong>
              <span>Database & Versions</span>
            </div>
            <t-tag theme="success" size="small" shape="round" variant="light">
              <i class="mdi mdi-circle-medium"></i>已连接
            </t-tag>
          </div>
          <table class="info-tbl">
            <tbody>
              <tr><td>数据库版本</td><td>{{ info.dbVersion }}</td></tr>
              <tr><td>Web 版本</td><td>{{ info.webVersion }}</td></tr>
              <tr><td>SQL 版本</td><td>{{ info.sqlVersion }}</td></tr>
              <tr><td>主机数量</td><td>{{ hosts }}</td></tr>
              <tr><td>宝塔面板</td><td>{{ btPanels }}</td></tr>
              <tr><td>节点数量</td><td>{{ nodes }}</td></tr>
              <tr><td>订单总数</td><td>{{ orders }}</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { computed, onMounted, onBeforeUnmount, ref, nextTick } from 'vue'
import { checkUpdate, systemInfo } from '@/admin/api/dashboard'
import echarts from '@/shared/utils/echarts'
import adImg from '@/shared/assets/ad3.webp'

const boot = window.__TD_BOOT__ || {}
const adminUser = boot.adminUser || 'admin'
const siteName = boot.siteName || 'MNBT'

// 顶部横幅广告（关闭状态记入 localStorage，下次进入不再显示）
const bannerVisible = ref(true)
function closeBanner() {
  bannerVisible.value = false
  try {
    localStorage.setItem('td_admin_ad_closed', '1')
  } catch { /* ignore */ }
}
try {
  if (localStorage.getItem('td_admin_ad_closed') === '1') bannerVisible.value = false
} catch { /* ignore */ }

// 系统信息:优先从 boot.sy 读取(如果由 sy.php 控制器加载),否则通过 AJAX 获取
const bootSy = boot.sy || {}

const info = ref({
  os: bootSy.os || '-',
  hostname: bootSy.hostname || '-',
  phpVersion: bootSy.php_version || '-',
  phpSapi: bootSy.php_sapi || '-',
  serverSoft: bootSy.server_soft || '-',
  serverIp: bootSy.server_ip || '-',
  serverPort: bootSy.server_port || '-',
  serverTime: bootSy.server_time || '-',
  timezone: bootSy.timezone || '-',
  memoryLimit: bootSy.memory_limit || '-',
  maxExecTime: bootSy.max_exec_time || '-',
  uploadMax: bootSy.upload_max || '-',
  postMax: bootSy.post_max || '-',
  extCount: bootSy.ext_count || 0,
  diskTotal: bootSy.disk_total || 0,
  diskFree: bootSy.disk_free || 0,
  diskUsed: bootSy.disk_used || 0,
  diskPct: bootSy.disk_pct || 0,
  memCurrent: bootSy.mem_current || 0,
  memPeak: bootSy.mem_peak || 0,
  loadAvg: bootSy.load_avg || null,
  dbVersion: bootSy.db_version || '-',
  webVersion: bootSy.web_version || '-',
  sqlVersion: bootSy.sql_version || '-',
})

const hosts = ref(bootSy.hosts || 0)
const btPanels = ref(bootSy.bt_panels || 0)
const nodes = ref(bootSy.nodes || 0)
const orders = ref(bootSy.orders || 0)

const updateIcon = ref('mdi-bookmark-check')
const updateText = ref('最新版本')
const updateTagTheme = ref('success')

const nowTime = ref('')
let timer = null

const greeting = computed(() => {
  const h = new Date().getHours()
  if (h < 6) return '凌晨好'
  if (h < 9) return '早上好'
  if (h < 12) return '上午好'
  if (h < 14) return '中午好'
  if (h < 18) return '下午好'
  return '晚上好'
})

const statCards = computed(() => [
  {
    label: '主机数量',
    value: hosts.value,
    icon: 'mdi-laptop',
    color: '#0052d9',
    bg: '#e8f3ff',
    footIcon: 'mdi-server',
    foot: '已对接主机',
  },
  {
    label: '宝塔面板',
    value: btPanels.value,
    icon: 'mdi-server',
    color: '#2ba471',
    bg: '#e8f8f0',
    footIcon: 'mdi-shield-check',
    foot: '通信正常',
  },
  {
    label: '节点数量',
    value: nodes.value,
    icon: 'mdi-router-wireless',
    color: '#e37318',
    bg: '#fff3e0',
    footIcon: 'mdi-account-multiple',
    foot: '在线节点',
  },
  {
    label: '订单总数',
    value: orders.value,
    icon: 'mdi-cart',
    color: '#d54941',
    bg: '#fdecee',
    footIcon: 'mdi-clock-outline',
    foot: '历史累计',
  },
])

const memPct = computed(() => {
  if (!info.value.memPeak) return 0
  return Math.min(100, Math.round((info.value.memCurrent / info.value.memPeak) * 100))
})

/* echarts gauge 实例 */
const diskGaugeRef = ref(null)
const cpuGaugeRef = ref(null)
const memGaugeRef = ref(null)
let diskChart = null
let cpuChart = null
let memChart = null

function buildGaugeOption(value, opts = {}) {
  const {
    color = '#0052d9',
    max = 100,
    unit = '%',
    formatter = null,
  } = opts
  let theme = '#2ba471'
  if (value > 80) theme = '#d54941'
  else if (value > 60) theme = '#e37318'
  return {
    series: [
      {
        type: 'gauge',
        startAngle: 210,
        endAngle: -30,
        min: 0,
        max,
        progress: {
          show: true,
          width: 12,
          roundCap: true,
          itemStyle: {
            color: theme,
          },
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
          formatter: formatter || `{value}${unit}`,
        },
        data: [{ value }],
        color,
      },
    ],
  }
}

function renderGauges() {
  if (diskGaugeRef.value) {
    if (!diskChart) diskChart = echarts.init(diskGaugeRef.value)
    diskChart.setOption(buildGaugeOption(info.value.diskPct || 0, { color: '#0052d9' }))
  }
  if (cpuGaugeRef.value) {
    if (!cpuChart) cpuChart = echarts.init(cpuGaugeRef.value)
    const cpuPct = info.value.loadAvg ? Math.min(100, Math.round((info.value.loadAvg[0] || 0) * 100)) : 0
    cpuChart.setOption(buildGaugeOption(cpuPct, { color: '#e37318' }))
  }
  if (memGaugeRef.value) {
    if (!memChart) memChart = echarts.init(memGaugeRef.value)
    memChart.setOption(buildGaugeOption(memPct.value, { color: '#2ba471' }))
  }
}

function resizeGauges() {
  diskChart?.resize()
  cpuChart?.resize()
  memChart?.resize()
}

function fmtBytes(bytes) {
  if (!bytes || bytes <= 0) return '0 B'
  const units = ['B', 'KB', 'MB', 'GB', 'TB']
  const pow = Math.min(Math.floor(Math.log(bytes) / Math.log(1024)), units.length - 1)
  return (bytes / Math.pow(1024, pow)).toFixed(2) + ' ' + units[pow]
}

function fmtLoad(i) {
  if (!info.value.loadAvg) return '0.00'
  return Number(info.value.loadAvg[i] || 0).toFixed(2)
}

function tickTime() {
  nowTime.value = new Date().toLocaleString('zh-CN', { hour12: false })
}

async function loadDashboard() {
  tickTime()
  timer = setInterval(tickTime, 1000)

  // 系统信息:如果 boot.sy 没有数据,则通过 AJAX 获取
  if (!boot.sy) {
    const syRes = await systemInfo()
    if (syRes.ok && syRes.data) {
      const d = syRes.data
      info.value = {
        os: d.os || '-',
        hostname: d.hostname || '-',
        phpVersion: d.php_version || '-',
        phpSapi: d.php_sapi || '-',
        serverSoft: d.server_soft || '-',
        serverIp: d.server_ip || '-',
        serverPort: d.server_port || '-',
        serverTime: d.server_time || '-',
        timezone: d.timezone || '-',
        memoryLimit: d.memory_limit || '-',
        maxExecTime: d.max_exec_time || '-',
        uploadMax: d.upload_max || '-',
        postMax: d.post_max || '-',
        extCount: d.ext_count || 0,
        diskTotal: d.disk_total || 0,
        diskFree: d.disk_free || 0,
        diskUsed: d.disk_used || 0,
        diskPct: d.disk_pct || 0,
        memCurrent: d.mem_current || 0,
        memPeak: d.mem_peak || 0,
        loadAvg: d.load_avg || null,
        dbVersion: d.db_version || '-',
        webVersion: d.web_version || '-',
        sqlVersion: d.sql_version || '-',
      }
      hosts.value = d.hosts || 0
      btPanels.value = d.bt_panels || 0
      nodes.value = d.nodes || 0
      orders.value = d.orders || 0
    }
  }

  // 版本与更新提示 (gn=mnbt 返回 cl/gx/vs)
  const res = await checkUpdate()
  if (res.ok && res.data) {
    const d = res.data
    if (d.cl) updateIcon.value = d.cl
    if (d.vs) updateText.value = d.vs
    if (d.gx) {
      if (d.gx.includes('最新')) {
        updateText.value = '已是最新'
        updateTagTheme.value = 'success'
      } else if (d.gx.includes('新版本')) {
        updateText.value = '有新版本'
        updateTagTheme.value = 'warning'
      } else if (d.gx.includes('离线')) {
        updateText.value = '离线模式'
        updateTagTheme.value = 'default'
      }
    }
  }

  // 渲染 gauge
  await nextTick()
  renderGauges()
}

onMounted(() => {
  loadDashboard()
  window.addEventListener('resize', resizeGauges)
})

onBeforeUnmount(() => {
  if (timer) clearInterval(timer)
  window.removeEventListener('resize', resizeGauges)
  diskChart?.dispose()
  cpuChart?.dispose()
  memChart?.dispose()
})
</script>

<style scoped>
.td-dashboard {
  padding: 20px;
}

/* ============== 顶部横幅广告 ============== */
.ad-banner {
  position: relative;
  border-radius: var(--td-radius-xl);
  overflow: hidden;
  margin-bottom: 18px;
  border: 1px solid var(--td-border);
  box-shadow: var(--td-shadow-sm);
  animation: td-fade-in var(--td-dur-lg) var(--td-ease-out);
}
.ad-link {
  display: block;
}
.ad-img {
  display: block;
  width: 100%;
  height: auto;
}
.ad-close {
  position: absolute;
  top: 10px;
  right: 10px;
  width: 28px;
  height: 28px;
  border-radius: 50%;
  border: none;
  background: rgba(0, 0, 0, 0.35);
  color: #fff;
  cursor: pointer;
  display: grid;
  place-items: center;
  font-size: 15px;
  transition: background 0.2s;
}
.ad-close:hover {
  background: rgba(0, 0, 0, 0.55);
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

/* ============== 统计卡片 ============== */
.stat-row {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 14px;
  margin-bottom: 22px;
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
              box-shadow var(--td-dur) var(--td-ease),
              border-color var(--td-dur) var(--td-ease);
  animation: td-fade-in var(--td-dur-lg) var(--td-ease-out);
}
.stat-card:hover {
  transform: translateY(-2px);
  box-shadow: var(--td-shadow-md);
  border-color: transparent;
}
.stat-card-body {
  flex: 1;
  min-width: 0;
}
.stat-label {
  font-size: 12px;
  color: var(--td-text-secondary);
  margin-bottom: 4px;
  letter-spacing: 0.3px;
}
.stat-num {
  font-size: 28px;
  font-weight: 700;
  line-height: 1.1;
  font-variant-numeric: tabular-nums;
  letter-spacing: -0.5px;
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
  width: 48px;
  height: 48px;
  border-radius: 12px;
  display: grid;
  place-items: center;
  font-size: 22px;
  flex-shrink: 0;
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

/* ============== Gauge ============== */
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

/* ============== 系统信息卡片 ============== */
.info-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 14px;
}
.info-grid-2 {
  grid-template-columns: repeat(auto-fit, minmax(340px, 1fr));
}
.info-card {
  background: var(--td-surface);
  border: 1px solid var(--td-border);
  border-radius: var(--td-radius-lg);
  box-shadow: var(--td-shadow-sm);
  overflow: hidden;
  transition: box-shadow var(--td-dur) var(--td-ease);
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
.info-card-icon-cyan   { background: #e0f7fa; color: #0891b2; }
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
.info-card-body {
  padding: 14px 16px;
  font-size: 13px;
  line-height: 1.7;
  color: var(--td-text);
}
.info-tbl {
  width: 100%;
  border-collapse: collapse;
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
  width: 110px;
}
.info-tbl td:last-child {
  color: var(--td-text);
  word-break: break-all;
}

@media (max-width: 600px) {
  .td-dashboard { padding: 14px; }
  .hero { padding: 16px; }
  .hero-title { font-size: 18px; }
  .stat-card { padding: 14px; }
  .stat-num { font-size: 22px; }
  .stat-card-icon { width: 40px; height: 40px; font-size: 18px; }
}
</style>
