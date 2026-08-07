<template>
  <div class="td-console">
    <t-loading :loading="loading && !hasData" text="加载中…" size="large">

      <!-- 空状态 -->
      <div v-if="view === 'empty'" class="td-empty-page">
        <i class="mdi mdi-package-variant-closed td-empty-big"></i>
        <h3>您还没有容器</h3>
        <p>每个账户可创建一个容器，前往应用商店选择镜像或应用即可开通。</p>
        <t-button theme="primary" size="large" @click="$router.push('/appstore')">
          <i class="mdi mdi-store"></i> 前往应用商店
        </t-button>
      </div>

      <!-- 创建中 -->
      <div v-else-if="view === 'creating'" class="td-create-page">
        <div class="td-hero">
          <div class="td-hero-left">
            <h2>容器创建中</h2>
            <p>应用 <b>{{ me.app_name || '' }}</b> 正在初始化，通常需要 1-5 分钟</p>
          </div>
          <t-tag theme="warning" variant="light" size="large">
            <i class="mdi mdi-clock-outline"></i> 创建中
          </t-tag>
        </div>
        <div class="td-steps-card">
          <div class="td-steps">
            <div class="td-step done"><span class="td-step-num">1</span><span>提交创建请求</span></div>
            <div class="td-step active"><span class="td-step-num">2</span><span>拉取镜像 / 启动容器</span></div>
            <div class="td-step"><span class="td-step-num">3</span><span>应用初始化完成</span></div>
          </div>
          <p class="td-poll-hint"><i class="mdi mdi-autorenew"></i> 每 8 秒自动检查容器状态…</p>
        </div>
      </div>

      <!-- 容器详情 -->
      <template v-else-if="view === 'detail' && container">
        <!-- 欢迎区 -->
        <div class="td-hero">
          <div class="td-hero-left">
            <div class="td-hero-avatar">{{ appIconChar(container.appname || me.app_name) }}</div>
            <div>
              <h2>{{ container.apptitle || container.appname || me.app_name || '容器详情' }}</h2>
              <p>{{ container.appdesc || '' }}</p>
            </div>
          </div>
          <div class="td-hero-right">
            <t-tag :theme="statusTheme" variant="light" size="large">
              <i class="mdi mdi-circle-medium"></i>{{ statusText }}
            </t-tag>
          </div>
        </div>

        <!-- 磁盘超限警告 -->
        <t-alert
          v-if="diskOverQuota"
          theme="error"
          :message="`磁盘用量已超出配额（${diskMax >= 1024 ? (diskMax/1024).toFixed(1) + ' GB' : diskMax + ' MB'}），容器已被自动停机。请清理数据后重新启动。`"
          style="margin-bottom: 18px"
        />

        <!-- 操作 -->
        <section class="td-section">
          <div class="td-section-title">
            <i class="mdi mdi-play-circle-outline"></i>
            <span>容器操作</span>
          </div>
          <div class="td-actions">
            <t-button v-if="me.container_status === 'running'" theme="warning" :loading="opLoading === 'container_stop'" @click="onOp('container_stop')">
              <i class="mdi mdi-stop"></i> 停止容器
            </t-button>
            <t-button v-else-if="me.container_status === 'stopped'" theme="success" :loading="opLoading === 'container_start'" @click="onOp('container_start')">
              <i class="mdi mdi-play"></i> 启动容器
            </t-button>
            <t-button theme="default" variant="outline" :loading="opLoading === 'container_restart'" :disabled="me.container_status === 'creating'" @click="onOp('container_restart')">
              <i class="mdi mdi-restart"></i> 重启容器
            </t-button>
            <t-button theme="default" variant="outline" @click="load">
              <i class="mdi mdi-refresh"></i> 刷新状态
            </t-button>
            <t-button theme="danger" variant="outline" @click="onRemove">
              <i class="mdi mdi-delete"></i> 删除容器
            </t-button>
          </div>
        </section>

        <!-- 资源使用 -->
        <section class="td-section">
          <div class="td-section-title">
            <i class="mdi mdi-chart-arc"></i>
            <span>资源使用</span>
          </div>
          <div class="td-gauge-grid">
            <div class="td-gauge-card">
              <div class="td-gauge-head">
                <i class="mdi mdi-cpu-64-bit"></i><span>CPU</span>
              </div>
              <div class="td-gauge-body">
                <div class="td-gauge-val">{{ specCpu }}</div>
                <div class="td-gauge-unit">核</div>
              </div>
              <div class="td-gauge-foot">配额上限 {{ plan?.cpu_max || 1 }} 核</div>
            </div>
            <div class="td-gauge-card">
              <div class="td-gauge-head">
                <i class="mdi mdi-memory"></i><span>内存</span>
              </div>
              <div class="td-gauge-body">
                <div class="td-gauge-val">{{ specMem }}</div>
                <div class="td-gauge-unit">MB</div>
              </div>
              <div class="td-gauge-foot">配额上限 {{ plan?.mem_max || 512 }} MB</div>
            </div>
            <div class="td-gauge-card" :class="{ 'td-gauge-danger': diskPct >= 90 }">
              <div class="td-gauge-head">
                <i class="mdi mdi-harddisk"></i><span>磁盘</span>
              </div>
              <div class="td-gauge-body">
                <div class="td-gauge-val">{{ diskUsage ? formatBytes(diskUsage) : '--' }}</div>
                <div class="td-gauge-unit">{{ diskMax ? (diskPct >= 100 ? '已超限' : diskPct.toFixed(0) + '%') : '不限' }}</div>
              </div>
              <div class="td-gauge-foot">配额 {{ diskMax ? (diskMax >= 1024 ? (diskMax/1024).toFixed(1) + ' GB' : diskMax + ' MB') : '不限制' }}</div>
            </div>
          </div>
        </section>

        <!-- 信息卡片 -->
        <section class="td-section">
          <div class="td-section-title">
            <i class="mdi mdi-information-outline"></i>
            <span>容器信息</span>
          </div>
          <div class="td-info-grid">
            <!-- 基本信息 -->
            <div class="td-info-card">
              <div class="td-info-head">
                <div class="td-info-icon td-info-icon-blue"><i class="mdi mdi-server"></i></div>
                <div class="td-info-name"><strong>基本信息</strong><span>Container Info</span></div>
              </div>
              <table class="td-info-tbl">
                <tbody>
                  <tr><td>服务名</td><td class="td-mono">{{ container.service_name || me.service_name || '-' }}</td></tr>
                  <tr><td>节点 IP</td><td class="td-mono">{{ nodeIp || '-' }}</td></tr>
                  <tr><td>版本</td><td>{{ container.version || versionLabel || '-' }}</td></tr>
                  <tr><td>状态</td><td>{{ container.status || me.container_status }}</td></tr>
                  <tr v-if="container.home"><td>应用主页</td><td><a :href="container.home.trim()" target="_blank" rel="noopener" class="td-link">{{ container.home.trim() }}</a></td></tr>
                </tbody>
              </table>
            </div>

            <!-- 端口映射 -->
            <div class="td-info-card">
              <div class="td-info-head">
                <div class="td-info-icon td-info-icon-green"><i class="mdi mdi-lan-connect"></i></div>
                <div class="td-info-name"><strong>端口映射</strong><span>Port Mapping</span></div>
              </div>
              <table class="td-info-tbl" v-if="portList.length">
                <tbody>
                  <tr v-for="p in portList" :key="p.port">
                    <td>{{ p.port }}</td>
                    <td>
                      <a :href="p.url" target="_blank" rel="noopener" class="td-link td-mono">{{ nodeIp }}:{{ p.port }}</a>
                      <span v-if="p.title" class="td-port-meta">（{{ p.title }}）</span>
                    </td>
                  </tr>
                </tbody>
              </table>
              <div v-else class="td-info-empty">无端口映射</div>
            </div>

            <!-- 配额信息 -->
            <div class="td-info-card">
              <div class="td-info-head">
                <div class="td-info-icon td-info-icon-purple"><i class="mdi mdi-scale"></i></div>
                <div class="td-info-name"><strong>配额信息</strong><span>Resource Quota</span></div>
              </div>
              <table class="td-info-tbl">
                <tbody>
                  <tr><td>CPU</td><td>{{ plan?.cpu_max || '-' }} 核</td></tr>
                  <tr><td>内存</td><td>{{ plan?.mem_max || '-' }} MB</td></tr>
                  <tr><td>磁盘</td><td :class="{ 'td-text-danger': diskPct >= 100 }">{{ diskLabel }}</td></tr>
                  <tr><td>代理数</td><td>{{ plan?.proxy_max && plan.proxy_max !== '0' ? plan.proxy_max + ' 个' : '不限制' }}</td></tr>
                  <tr><td>到期时间</td><td :class="{ 'td-text-danger': isExpired(me.datae) }">{{ me.datae === '0000-00-00' ? '永久' : (me.datae || '-') }}</td></tr>
                </tbody>
              </table>
            </div>
          </div>
        </section>

        </template>
      </t-loading>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { MessagePlugin, DialogPlugin } from 'tdesign-vue-next'
import { getMyContainer, containerStart, containerStop, containerRestart, containerRemove } from '@/docker/api/docker'

const loading = ref(false)
const hasData = ref(false)
const me = ref({})
const container = ref(null)
const node = ref({})
const plan = ref(null)
const opLoading = ref('')
let pollTimer = null

const view = computed(() => {
  if (!me.value.service_name && !me.value.container_id) return 'empty'
  if (me.value.container_status === 'creating' || (!container.value && me.value.service_name)) return 'creating'
  if (container.value) return 'detail'
  return 'empty'
})

const nodeIp = computed(() => node.value.btip || container.value?.server_ip || container.value?.host_ip || '')
const statusTheme = computed(() => {
  const map = { running: 'success', stopped: 'danger', creating: 'warning', none: 'default', failed: 'danger' }
  return map[me.value.container_status] || 'default'
})
const statusText = computed(() => {
  const map = { none: '未创建', creating: '创建中', running: '运行中', stopped: '已停止', failed: '失败' }
  return map[me.value.container_status] || '未知'
})
const ports = computed(() => container.value?.port || [])
const portList = computed(() => {
  const ip = nodeIp.value
  const appinfo = container.value?.appinfo || []
  const portTitleMap = {}
  appinfo.forEach((info) => {
    const key = info.fieldKey || ''
    const val = String(info.fieldValue || '')
    if (/_port$/i.test(key) && /^\d+$/.test(val)) portTitleMap[val] = info.fieldTitle || key
  })
  return ports.value.map((p) => {
    const port = String(p)
    let url = ''
    if (ip) {
      if (port === '80') url = `http://${ip}`
      else if (port === '443') url = `https://${ip}`
      else url = `http://${ip}:${port}`
    }
    return { port, url, title: portTitleMap[port] || '' }
  })
})
const versionLabel = computed(() => {
  if (!container.value) return ''
  const c = container.value
  return c.m_version ? c.m_version + (c.s_version ? '.' + c.s_version : '') : ''
})
const specCpu = computed(() => {
  try { return JSON.parse(me.value.container_spec || '{}').cpus || '-' } catch { return '-' }
})
const specMem = computed(() => {
  try { return JSON.parse(me.value.container_spec || '{}').memory_limit || '-' } catch { return '-' }
})
const diskUsage = computed(() => parseInt(me.value.disk_usage, 10) || 0)
const diskMax = computed(() => plan.value?.disk_max ? parseInt(plan.value.disk_max, 10) : 0)
const diskPct = computed(() => {
  if (!diskMax.value || !diskUsage.value) return 0
  return diskUsage.value / (diskMax.value * 1048576) * 100
})
const diskOverQuota = computed(() => diskPct.value >= 100 && me.value.container_status === 'stopped')
const diskLabel = computed(() => {
  if (!diskUsage.value) return '暂无数据'
  let s = formatBytes(diskUsage.value)
  if (diskMax.value) {
    s += ' / ' + (diskMax.value >= 1024 ? (diskMax.value / 1024).toFixed(1) + ' GB' : diskMax.value + ' MB')
    if (diskPct.value >= 100) s += '（已超限）'
    else if (diskPct.value > 90) s += '（' + diskPct.value.toFixed(0) + '% 即将超限）'
    else s += '（' + diskPct.value.toFixed(0) + '%）'
  }
  return s
})

function formatBytes(n) {
  n = parseInt(n, 10) || 0
  if (n < 1024) return n + ' B'
  if (n < 1048576) return (n / 1024).toFixed(1) + ' KB'
  if (n < 1073741824) return (n / 1048576).toFixed(1) + ' MB'
  return (n / 1073741824).toFixed(2) + ' GB'
}
function appIconChar(name) {
  if (!name) return '◆'
  name = String(name).trim()
  if (/^[\u4e00-\u9fa5]/.test(name)) return name.charAt(0)
  return name.charAt(0).toUpperCase()
}
function isExpired(datae) {
  if (!datae || datae === '0000-00-00') return false
  const d = new Date(datae)
  if (isNaN(d.getTime())) return false
  return d.getTime() < Date.now()
}

async function load() {
  loading.value = true
  const r = await getMyContainer()
  loading.value = false
  if (r.ok && r.data) {
    me.value = r.data.me || {}
    container.value = r.data.container || null
    node.value = r.data.node || {}
    plan.value = r.data.plan || null
    hasData.value = true
    if (pollTimer) { clearInterval(pollTimer); pollTimer = null }
    if (view.value === 'creating') pollTimer = setInterval(load, 8000)
  }
}

async function onOp(gn) {
  opLoading.value = gn
  const fn = gn === 'container_start' ? containerStart : gn === 'container_stop' ? containerStop : containerRestart
  const r = await fn()
  opLoading.value = ''
  if (r.ok) { MessagePlugin.success(r.message || '操作已提交'); setTimeout(load, 1500) }
}

async function onRemove() {
  const confirmed = await new Promise((resolve) => {
    const dlg = DialogPlugin.confirm({
      header: '确认删除', body: '确定要删除容器吗？容器数据将被永久清除，此操作不可恢复。',
      confirmBtn: '确认删除', cancelBtn: '取消', theme: 'danger',
      onConfirm: () => { dlg.destroy(); resolve(true) },
      onCancel: () => { dlg.destroy(); resolve(false) },
      onClose: () => { dlg.destroy(); resolve(false) },
    })
  })
  if (!confirmed) return
  const r = await containerRemove()
  if (r.ok) { MessagePlugin.success(r.message || '容器已删除'); setTimeout(load, 2000) }
}

onMounted(load)
onUnmounted(() => { if (pollTimer) clearInterval(pollTimer) })
</script>

<style scoped>
.td-console { animation: td-fade-in .3s ease; }
@keyframes td-fade-in { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: translateY(0); } }

/* ========== 空状态 ========== */
.td-empty-page { text-align: center; padding: 80px 20px; }
.td-empty-big { font-size: 64px; color: #cbd5e1; margin-bottom: 16px; display: block; }
.td-empty-page h3 { margin: 0 0 8px; font-size: 18px; color: var(--td-text-color-primary); }
.td-empty-page p { margin: 0 0 20px; font-size: 14px; color: var(--td-text-color-secondary); }

/* ========== 创建中 ========== */
.td-create-page { max-width: 640px; }
.td-steps-card { background: #fff; border: 1px solid var(--td-border-level-1-color, #e7e7e7); border-radius: 12px; padding: 20px 24px; box-shadow: 0 1px 3px rgba(0,0,0,.04); }
.td-steps { display: flex; flex-direction: column; gap: 14px; }
.td-step { display: flex; align-items: center; gap: 12px; opacity: .45; font-size: 14px; }
.td-step.done { opacity: 1; }
.td-step.active { opacity: .85; }
.td-step-num { width: 30px; height: 30px; border-radius: 50%; background: #e5e7eb; color: #6b7280; display: flex; align-items: center; justify-content: center; font-size: 13px; font-weight: 600; flex-shrink: 0; }
.td-step.done .td-step-num, .td-step.active .td-step-num { background: var(--td-brand-color, #0052d9); color: #fff; }
.td-step.active .td-step-num { animation: stepPulse 1.4s infinite; }
@keyframes stepPulse { 0%,100% { box-shadow: 0 0 0 0 rgba(0,82,217,.35); } 50% { box-shadow: 0 0 0 6px rgba(0,82,217,0); } }
.td-poll-hint { margin: 16px 0 0; font-size: 13px; color: var(--td-text-color-secondary); }

/* ========== Hero ========== */
.td-hero { display: flex; align-items: center; justify-content: space-between; gap: 16px; padding: 22px 24px; margin-bottom: 18px; background: linear-gradient(135deg, #fff 0%, #f5f8ff 100%); border: 1px solid var(--td-border-level-1-color, #e7e7e7); border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,.04); flex-wrap: wrap; }
.td-hero-left { display: flex; align-items: center; gap: 14px; min-width: 0; }
.td-hero-avatar { width: 48px; height: 48px; border-radius: 12px; background: rgba(0,82,217,.08); color: var(--td-brand-color, #0052d9); display: flex; align-items: center; justify-content: center; font-size: 20px; font-weight: 700; flex-shrink: 0; }
.td-hero-left h2 { margin: 0; font-size: 20px; font-weight: 700; color: var(--td-text-color-primary); }
.td-hero-left p { margin: 4px 0 0; font-size: 13px; color: var(--td-text-color-secondary); }
.td-hero-right { display: flex; gap: 10px; align-items: center; }

/* ========== Section ========== */
.td-section { margin-top: 22px; }
.td-section-title { display: flex; align-items: center; gap: 8px; font-size: 15px; font-weight: 600; margin-bottom: 14px; color: var(--td-text-color-primary); }
.td-section-title i { font-size: 18px; color: var(--td-brand-color, #0052d9); }

/* ========== Gauge 仪表盘 ========== */
.td-gauge-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 14px; }
.td-gauge-card { background: #fff; border: 1px solid var(--td-border-level-1-color, #e7e7e7); border-radius: 12px; padding: 18px; box-shadow: 0 1px 3px rgba(0,0,0,.04); transition: box-shadow .2s; }
.td-gauge-card:hover { box-shadow: 0 4px 12px rgba(0,0,0,.06); }
.td-gauge-head { display: flex; align-items: center; gap: 6px; font-size: 13px; font-weight: 600; color: var(--td-text-color-secondary); margin-bottom: 12px; }
.td-gauge-head i { font-size: 16px; color: var(--td-brand-color, #0052d9); }
.td-gauge-body { display: flex; align-items: baseline; gap: 4px; margin-bottom: 8px; }
.td-gauge-val { font-size: 28px; font-weight: 700; color: var(--td-text-color-primary); }
.td-gauge-unit { font-size: 13px; color: var(--td-text-color-secondary); }
.td-gauge-foot { font-size: 12px; color: var(--td-text-color-placeholder); }
.td-gauge-danger .td-gauge-val { color: var(--td-error-color, #d54941); }

/* ========== 信息卡片 ========== */
.td-info-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 14px; }
.td-info-card { background: #fff; border: 1px solid var(--td-border-level-1-color, #e7e7e7); border-radius: 12px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,.04); transition: box-shadow .2s; display: flex; flex-direction: column; }
.td-info-card:hover { box-shadow: 0 4px 12px rgba(0,0,0,.06); }
.td-info-head { display: flex; align-items: center; gap: 10px; padding: 14px 16px; border-bottom: 1px solid var(--td-border-level-1-color, #e7e7e7); }
.td-info-icon { width: 36px; height: 36px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0; }
.td-info-icon-blue { background: #e8f3ff; color: #0052d9; }
.td-info-icon-green { background: #e8f8f0; color: #2ba471; }
.td-info-icon-purple { background: #f3e8ff; color: #7c3aed; }
.td-info-name { flex: 1; display: flex; flex-direction: column; }
.td-info-name strong { font-size: 14px; font-weight: 600; color: var(--td-text-color-primary); }
.td-info-name span { font-size: 11px; color: var(--td-text-color-placeholder); }
.td-info-tbl { width: 100%; border-collapse: collapse; flex: 1; }
.td-info-tbl td { padding: 9px 16px; font-size: 13px; border-bottom: 1px solid #f5f6f8; }
.td-info-tbl tr:last-child td { border-bottom: none; }
.td-info-tbl td:first-child { color: var(--td-text-color-secondary); white-space: nowrap; width: 80px; }
.td-info-tbl td:last-child { color: var(--td-text-color-primary); word-break: break-all; }
.td-info-empty { padding: 24px; text-align: center; color: var(--td-text-color-placeholder); font-size: 13px; }

/* ========== 操作 ========== */
.td-actions { display: flex; gap: 10px; flex-wrap: wrap; }

/* ========== 通用 ========== */
.td-mono { font-family: Consolas, Monaco, monospace; }
.td-link { color: var(--td-brand-color, #0052d9); text-decoration: none; }
.td-link:hover { text-decoration: underline; }
.td-port-meta { color: var(--td-text-color-secondary); font-size: 12px; }
.td-text-danger { color: var(--td-error-color, #d54941); }
</style>