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

    <el-row :gutter="16" class="mt">
      <el-col :xs="24" :lg="16">
        <div class="ql-card">
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
      </el-col>
      <el-col :xs="24" :lg="8">
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
          </div>
        </div>
      </el-col>
    </el-row>

    <div v-if="history.length" class="ql-card mt">
      <div class="chart-head">
        <h3 class="ql-section-title" style="margin:0">月度流量</h3>
        <span class="ql-muted">{{ trendText }}</span>
      </div>
      <div class="bar-chart">
        <div v-for="(h, i) in historyBars" :key="i" class="bar-col">
          <div class="bar" :style="{ height: h.h + '%' }" :title="h.tip" />
          <span>{{ h.label }}</span>
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

function formatTraffic(bytes, maxGb) {
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
  const llsMax = num(lls.max) // GB
  const llsDq = num(lls.dq) // bytes often
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

const history = computed(() => {
  const h = conf.value?.lls?.history
  if (!h) return []
  if (Array.isArray(h)) return h
  return Object.values(h)
})

const historyBars = computed(() => {
  const arr = history.value.map((x) => num(x.value ?? x.dq ?? x))
  const max = Math.max(...arr, 1)
  return history.value.map((x, i) => ({
    label: String(x.month ?? x.name ?? i + 1).slice(-2),
    h: Math.max(6, (num(x.value ?? x.dq ?? x) / max) * 100),
    tip: String(x.value ?? x.dq ?? x),
  }))
})

const trendText = computed(() => {
  const arr = history.value
  if (arr.length < 2) return ''
  const a = num(arr[arr.length - 2].value ?? arr[arr.length - 2].dq ?? arr[arr.length - 2])
  const b = num(arr[arr.length - 1].value ?? arr[arr.length - 1].dq ?? arr[arr.length - 1])
  if (a <= 0) return ''
  const d = (((b - a) / a) * 100).toFixed(1)
  return Number(d) >= 0 ? `环比 +${d}%` : `环比 ${d}%`
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
    { label: '文件管理', legacy: 'ftp.php', title: '在线文件管理', icon: FolderOpened },
    { label: '监控', path: '/monitor', icon: Monitor },
    { label: '改密', path: '/settings/xgpass', icon: Key },
    { label: '统计', path: '/stats', icon: DataAnalysis },
  ]
})

function go(s) {
  if (s.legacy) {
    router.push({ path: '/legacy', query: { u: s.legacy, t: s.title || s.label } })
    return
  }
  if (s.path) router.push(s.path)
}

async function load() {
  loading.value = true
  const res = await fetchIndexConf()
  loading.value = false
  if (res.ok) {
    conf.value = res.data
    if (res.data?.gg) {
      // 公告仅提示一次
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
.mt {
  margin-top: 4px;
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
.info-list {
  display: flex;
  flex-direction: column;
  gap: 14px;
  min-height: 120px;
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
.chart-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 16px;
}
.bar-chart {
  display: flex;
  align-items: flex-end;
  gap: 10px;
  height: 160px;
  padding-top: 8px;
}
.bar-col {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  height: 100%;
  justify-content: flex-end;
  gap: 6px;
}
.bar {
  width: 100%;
  max-width: 36px;
  border-radius: 8px 8px 4px 4px;
  background: linear-gradient(180deg, #38d9a9, #12b886);
  min-height: 4px;
  transition: height 0.4s ease;
}
.bar-col span {
  font-size: 11px;
  color: var(--ql-text-secondary);
}
.info-card {
  margin-bottom: 16px;
}
</style>
