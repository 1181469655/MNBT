<template>
  <div class="dk-console">
    <t-loading :loading="loading && !hasData" text="加载中…" size="large" class="dk-page-loading">
      <!-- 空状态 -->
      <div v-if="view === 'empty'" class="dk-empty-card">
        <t-card>
          <div class="dk-empty">
            <i class="mdi mdi-package-variant-closed dk-empty-ico"></i>
            <h4>您还没有容器</h4>
            <p>每个账户可创建一个容器，前往应用商店选择镜像或应用即可开通。</p>
            <t-button theme="primary" @click="$router.push('/appstore')">
              <i class="mdi mdi-store"></i> 前往应用商店
            </t-button>
          </div>
        </t-card>
      </div>

      <!-- 创建中 -->
      <div v-else-if="view === 'creating'" class="dk-create-card">
        <t-card>
          <template #header>
            <div class="dk-card-hd">
              <h3>容器创建中</h3>
              <t-tag theme="warning" variant="light">
                <i class="mdi mdi-clock-outline"></i> 创建中
              </t-tag>
            </div>
          </template>
          <t-alert theme="info" :message="`应用 ${me.app_name || ''} 正在初始化，通常需要 1-5 分钟，请耐心等待，页面会自动刷新。`" />
          <div class="dk-steps">
            <div class="dk-step" data-s="1">
              <span class="dk-step-ico">1</span>
              <span class="dk-step-txt">提交创建请求</span>
            </div>
            <div class="dk-step" data-s="2">
              <span class="dk-step-ico">2</span>
              <span class="dk-step-txt">拉取镜像 / 启动容器</span>
            </div>
            <div class="dk-step" data-s="3">
              <span class="dk-step-ico">3</span>
              <span class="dk-step-txt">应用初始化完成</span>
            </div>
          </div>
          <p class="dk-poll-hint"><i class="mdi mdi-autorenew"></i> 每 8 秒自动检查容器状态…</p>
        </t-card>
      </div>

      <!-- 容器详情 -->
      <div v-else-if="view === 'detail' && container" class="dk-detail-card">
        <t-card>
          <template #header>
            <div class="dk-card-hd">
              <div class="dk-app-title">
                <div class="dk-app-icon">{{ appIconChar(container.appname || me.app_name) }}</div>
                <div>
                  <h3>{{ container.apptitle || container.appname || me.app_name || '容器详情' }}</h3>
                  <span v-if="container.appdesc" class="dk-app-desc">{{ container.appdesc }}</span>
                </div>
              </div>
              <t-tag :theme="statusTheme" variant="light">{{ statusText }}</t-tag>
            </div>
          </template>

          <!-- 指标 -->
          <div class="dk-metrics">
            <div class="dk-metric">
              <div class="dk-m-label">服务名</div>
              <div class="dk-m-value">{{ container.service_name || me.service_name || '-' }}</div>
            </div>
            <div class="dk-metric">
              <div class="dk-m-label">节点 IP</div>
              <div class="dk-m-value">{{ nodeIp || '-' }}</div>
            </div>
            <div class="dk-metric">
              <div class="dk-m-label">配额</div>
              <div class="dk-m-value">{{ specCpu }} 核 / {{ specMem }} MB</div>
            </div>
            <div class="dk-metric">
              <div class="dk-m-label">版本</div>
              <div class="dk-m-value">{{ container.version || versionLabel || '-' }}</div>
            </div>
          </div>

          <!-- 详情表 -->
          <div class="dk-detail-table">
            <div class="dk-detail-row">
              <span class="dk-detail-label">状态</span>
              <span class="dk-detail-value">{{ container.status || me.container_status }}</span>
            </div>
            <div class="dk-detail-row">
              <span class="dk-detail-label">节点 IP</span>
              <span class="dk-detail-value dk-mono">{{ nodeIp || '-' }}</span>
            </div>
            <div class="dk-detail-row">
              <span class="dk-detail-label">端口映射</span>
              <span class="dk-detail-value">
                <span v-if="!ports.length" class="dk-muted">无端口映射</span>
                <div v-else class="dk-ports">
                  <a
                    v-for="p in portList"
                    :key="p.port"
                    :href="p.url"
                    target="_blank"
                    rel="noopener"
                    class="dk-port-link"
                  >
                    {{ nodeIp }}:{{ p.port }}
                    <span v-if="p.title" class="dk-port-meta">（{{ p.title }}）</span>
                  </a>
                </div>
              </span>
            </div>
            <div v-if="container.home" class="dk-detail-row">
              <span class="dk-detail-label">应用主页</span>
              <span class="dk-detail-value">
                <a :href="container.home.trim()" target="_blank" rel="noopener" class="dk-port-link">
                  {{ container.home.trim() }}
                </a>
              </span>
            </div>
            <div class="dk-detail-row">
              <span class="dk-detail-label">服务名</span>
              <span class="dk-detail-value dk-mono">{{ container.service_name || me.service_name || '-' }}</span>
            </div>
          </div>

          <!-- 操作 -->
          <div class="dk-actions">
            <t-button
              v-if="me.container_status === 'running'"
              theme="warning"
              variant="outline"
              :loading="opLoading === 'container_stop'"
              @click="onOp('container_stop')"
            >
              <i class="mdi mdi-stop"></i> 停止
            </t-button>
            <t-button
              v-else-if="me.container_status === 'stopped'"
              theme="success"
              variant="outline"
              :loading="opLoading === 'container_start'"
              @click="onOp('container_start')"
            >
              <i class="mdi mdi-play"></i> 启动
            </t-button>
            <t-button
              theme="default"
              variant="outline"
              :loading="opLoading === 'container_restart'"
              :disabled="me.container_status === 'creating'"
              @click="onOp('container_restart')"
            >
              <i class="mdi mdi-restart"></i> 重启
            </t-button>
            <t-button theme="default" variant="outline" @click="load">
              <i class="mdi mdi-refresh"></i> 刷新状态
            </t-button>
          </div>
        </t-card>
      </div>
    </t-loading>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { MessagePlugin } from 'tdesign-vue-next'
import { getMyContainer, containerStart, containerStop, containerRestart } from '@/docker/api/docker'

const loading = ref(false)
const hasData = ref(false)
const me = ref({})
const container = ref(null)
const node = ref({})
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
    if (/_port$/i.test(key) && /^\d+$/.test(val)) {
      portTitleMap[val] = info.fieldTitle || key
    }
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

function appIconChar(name) {
  if (!name) return '◆'
  name = String(name).trim()
  if (/^[\u4e00-\u9fa5]/.test(name)) return name.charAt(0)
  return name.charAt(0).toUpperCase()
}

async function load() {
  loading.value = true
  const r = await getMyContainer()
  loading.value = false
  if (r.ok && r.data) {
    me.value = r.data.me || {}
    container.value = r.data.container || null
    node.value = r.data.node || {}
    hasData.value = true
    if (pollTimer) { clearInterval(pollTimer); pollTimer = null }
    if (view.value === 'creating') {
      pollTimer = setInterval(load, 8000)
    }
  }
}

async function onOp(gn) {
  opLoading.value = gn
  const fn = gn === 'container_start' ? containerStart : gn === 'container_stop' ? containerStop : containerRestart
  const r = await fn()
  opLoading.value = ''
  if (r.ok) {
    MessagePlugin.success(r.message || '操作已提交')
    setTimeout(load, 1500)
  }
}

onMounted(load)
onUnmounted(() => { if (pollTimer) clearInterval(pollTimer) })
</script>

<style scoped>
.dk-page-loading { min-height: 320px; }
.dk-empty-card, .dk-create-card, .dk-detail-card { max-width: 820px; }
.dk-empty {
  text-align: center;
  padding: 40px 20px;
}
.dk-empty-ico {
  font-size: 48px;
  color: var(--td-text-color-placeholder, #bbb);
  margin-bottom: 12px;
}
.dk-empty h4 {
  margin: 0 0 8px;
  font-size: 16px;
  color: var(--td-text-color-primary, #1f2937);
}
.dk-empty p {
  margin: 0 0 18px;
  font-size: 13.5px;
  color: var(--td-text-color-secondary, #6b7280);
}
.dk-card-hd {
  display: flex;
  align-items: center;
  justify-content: space-between;
  width: 100%;
}
.dk-card-hd h3 {
  margin: 0;
  font-size: 16px;
  font-weight: 600;
}
.dk-app-title {
  display: flex;
  align-items: center;
  gap: 12px;
}
.dk-app-icon {
  width: 44px;
  height: 44px;
  border-radius: 10px;
  background: rgba(0, 82, 217, 0.08);
  color: var(--td-brand-color, #0052d9);
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 18px;
  font-weight: 600;
}
.dk-app-desc {
  font-size: 12.5px;
  color: var(--td-text-color-secondary, #6b7280);
  margin-top: 2px;
  display: block;
}
.dk-steps {
  display: flex;
  flex-direction: column;
  gap: 14px;
  margin: 20px 0 12px;
}
.dk-step {
  display: flex;
  align-items: center;
  gap: 12px;
  opacity: 0.55;
}
.dk-step-ico {
  width: 30px;
  height: 30px;
  border-radius: 50%;
  flex-shrink: 0;
  background: #e5e7eb;
  color: #6b7280;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 13px;
  font-weight: 600;
}
.dk-step-txt {
  font-size: 14px;
  color: var(--td-text-color-primary, #1f2937);
}
.dk-step[data-s="1"] { opacity: 1; }
.dk-step[data-s="1"] .dk-step-ico { background: var(--td-brand-color, #0052d9); color: #fff; }
.dk-step[data-s="2"] .dk-step-ico { background: var(--td-brand-color, #0052d9); color: #fff; animation: dkStepPulse 1.4s infinite; }
.dk-step[data-s="2"] { opacity: 0.85; }
@keyframes dkStepPulse {
  0%, 100% { box-shadow: 0 0 0 0 rgba(0, 82, 217, 0.35); }
  50% { box-shadow: 0 0 0 6px rgba(0, 82, 217, 0); }
}
.dk-poll-hint {
  margin: 14px 0 0;
  font-size: 13px;
  color: var(--td-text-color-secondary, #6b7280);
}
.dk-metrics {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 16px;
  margin-bottom: 22px;
}
.dk-metric {
  background: var(--td-bg-color-container, #f7f8fa);
  border-radius: 8px;
  padding: 12px 14px;
}
.dk-m-label {
  font-size: 12px;
  color: var(--td-text-color-secondary, #6b7280);
  margin-bottom: 4px;
}
.dk-m-value {
  font-size: 14px;
  font-weight: 500;
  color: var(--td-text-color-primary, #1f2937);
  word-break: break-all;
}
.dk-mono {
  font-family: Consolas, Monaco, monospace;
}
.dk-detail-table {
  border: 1px solid var(--td-border-level-1-color, #e7e7e7);
  border-radius: 8px;
  overflow: hidden;
}
.dk-detail-row {
  display: flex;
  border-bottom: 1px solid var(--td-border-level-1-color, #e7e7e7);
}
.dk-detail-row:last-child { border-bottom: none; }
.dk-detail-label {
  width: 140px;
  flex-shrink: 0;
  padding: 13px 18px;
  background: var(--td-bg-color-container, #f9fafb);
  font-size: 13px;
  color: var(--td-text-color-secondary, #6b7280);
  font-weight: 500;
}
.dk-detail-value {
  flex: 1;
  padding: 13px 18px;
  font-size: 13.5px;
  color: var(--td-text-color-primary, #1f2937);
  word-break: break-all;
}
.dk-muted { color: var(--td-text-color-secondary, #6b7280); }
.dk-ports {
  display: flex;
  flex-direction: column;
  gap: 6px;
}
.dk-port-link {
  color: var(--td-brand-color, #0052d9);
  text-decoration: none;
  font-size: 13.5px;
}
.dk-port-link:hover { text-decoration: underline; }
.dk-port-meta {
  color: var(--td-text-color-secondary, #6b7280);
  font-size: 12px;
}
.dk-actions {
  display: flex;
  gap: 10px;
  margin-top: 22px;
  flex-wrap: wrap;
}
</style>
