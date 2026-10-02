<template>
  <div class="td-page">
    <div class="td-page-head">
      <div>
        <h3 class="td-page-title"><i class="mdi mdi-cloud-download"></i>系统更新</h3>
        <p class="td-page-subtitle">检查新版本并执行在线更新（异步后台执行，进度实时反馈）</p>
      </div>
      <t-button theme="default" variant="outline" @click="recheck">
        <i class="mdi mdi-refresh"></i> 重新检查
      </t-button>
    </div>

    <div class="upd-wrap">
      <!-- 错误态 -->
      <div v-if="!info" class="td-set-card">
        <div class="td-set-card-hd">
          <div class="td-set-icon" style="background: #fdecee; color: #d54941">
            <i class="mdi mdi-alert-circle-outline"></i>
          </div>
          <div>
            <h4>检查更新失败</h4>
            <p>无法连接到更新服务器,请稍后重试</p>
          </div>
        </div>
      </div>

      <!-- 正常态 -->
      <template v-else>
        <!-- 版本卡片 -->
        <div class="stat-row">
          <div class="stat-card">
            <div class="stat-icon" style="background: #0052d9">
              <i class="mdi mdi-tag"></i>
            </div>
            <div>
              <div class="stat-num">{{ currentVersion }}</div>
              <div class="stat-label">当前版本</div>
            </div>
          </div>
          <div class="stat-card">
            <div class="stat-icon" :style="{ background: hasUpdate ? '#e37318' : '#2ba471' }">
              <i class="mdi" :class="hasUpdate ? 'mdi-arrow-up-bold-circle' : 'mdi-check-circle'"></i>
            </div>
            <div>
              <div class="stat-num">{{ latestVersion }}</div>
              <div class="stat-label">最新版本<template v-if="info.publishedAt">（{{ String(info.publishedAt).slice(0, 10) }}）</template></div>
            </div>
          </div>
        </div>

        <!-- 更新提示 -->
        <div v-if="hasUpdate" class="td-set-card">
          <div class="td-set-card-hd">
            <div class="td-set-icon" style="background: #fff3e0; color: #e37318">
              <i class="mdi mdi-alert-circle"></i>
            </div>
            <div>
              <h4>发现新版本</h4>
              <p>{{ info.msg || '建议尽快更新以获取最新功能与安全修复' }}</p>
            </div>
            <t-button theme="warning" :loading="updating" :disabled="upd.terminal === false" @click="askUpdate" class="upd-btn">
              <i class="mdi mdi-update"></i> 立刻更新
            </t-button>
          </div>
          <div class="td-set-card-bd">
            <h5 class="upd-section">下载来源</h5>
            <table class="upd-kv">
              <tr><td>命中来源</td><td>{{ info.channelLabel || '—' }}（仓库 {{ info.repo }}）</td></tr>
              <tr><td>包来源</td><td>{{ info.sourceLabel }}<template v-if="info.assetSize">（{{ mb(info.assetSize) }}）</template></td></tr>
              <tr>
                <td>下载策略</td>
                <td>{{ POLICY_LABELS[info.sourcePolicy] || info.sourcePolicy }}<template v-if="info.sourcePolicy === 'gitee_first' || info.sourcePolicy === 'github_first'"> · 失败自动切换到另一种来源</template></td>
              </tr>
              <tr v-if="info.assetUrl"><td>附件地址</td><td>{{ info.assetUrl }}</td></tr>
              <tr v-if="info.fallback"><td>检查方式</td><td>latest 接口不可用，已改用发布列表取最高版本</td></tr>
            </table>
          </div>
        </div>

        <!-- 已是最新 -->
        <div v-else-if="isLatest" class="td-set-card">
          <div class="td-set-card-hd">
            <div class="td-set-icon" style="background: #e8f8f0; color: #2ba471">
              <i class="mdi mdi-check-circle"></i>
            </div>
            <div>
              <h4>已是最新版本</h4>
              <p>{{ info.msg || '当前版本已是最新,无需更新' }}</p>
            </div>
          </div>
          <div class="td-set-card-bd">
            <h5 class="upd-section">下载来源</h5>
            <table class="upd-kv">
              <tr><td>命中来源</td><td>{{ info.channelLabel || '—' }}（仓库 {{ info.repo }}）</td></tr>
              <tr><td>下载策略</td><td>{{ POLICY_LABELS[info.sourcePolicy] || info.sourcePolicy }}</td></tr>
            </table>
          </div>
        </div>

        <!-- 检查失败 -->
        <div v-else class="td-set-card">
          <div class="td-set-card-hd">
            <div class="td-set-icon" style="background: #f5f6f8; color: #8c8c8c">
              <i class="mdi mdi-cloud-alert"></i>
            </div>
            <div>
              <h4>{{ info.msg || '暂时无法检查更新' }}</h4>
              <p>更新从 GitHub / Gitee Release 拉取，请检查服务器能否访问 gitee.com 或 api.github.com，以及更新设置里的两个仓库地址</p>
            </div>
          </div>
        </div>

        <!-- 更新日志 -->
        <div v-if="info.uplog" class="td-set-card">
          <div class="td-set-card-hd">
            <div class="td-set-icon" style="background: #eff6ff; color: #2563eb">
              <i class="mdi mdi-file-document-outline"></i>
            </div>
            <div>
              <h4>更新日志</h4>
              <p>该版本发布的更新说明</p>
            </div>
          </div>
          <div class="td-set-card-bd">
            <pre class="td-code-block upd-log">{{ info.uplog }}</pre>
          </div>
        </div>

        <!-- 更新进度面板（对齐 classic：主请求 + 1.5s 轮询 progress.json） -->
        <div v-if="upd.visible" class="td-set-card upd-prog-card">
          <div class="td-set-card-bd">
            <div class="upd-prog-hd">
              <span class="upd-prog-title">
                <template v-if="!upd.terminal">{{ upd.stepLabel || '进行中' }}<template v-if="upd.steps.length">（{{ Math.max(0, upd.stepIndex) + 1 }}/{{ upd.steps.length }}）</template></template>
                <template v-else>{{ upd.ok ? '更新完成' : '更新失败' }}</template>
              </span>
              <span class="upd-prog-elapsed">已用 {{ elapsedText }}</span>
            </div>
            <div class="upd-prog-bar" :class="{ 'is-indet': upd.pct === null }">
              <i :style="{ width: barWidth }"></i>
            </div>
            <div class="upd-prog-detail">
              {{ upd.detail }}<template v-if="typeof upd.pct === 'number' && !upd.terminal">　{{ clampPct }}%</template><template v-if="upd.httpNote && !upd.terminal">　·　{{ upd.httpNote }}</template>
            </div>
            <ol v-if="upd.steps.length" class="upd-prog-steps">
              <li
                v-for="(st, i) in upd.steps"
                :key="i"
                :class="{
                  'is-done': i < upd.stepIndex || (i === upd.stepIndex && upd.terminal && upd.ok),
                  'is-active': i === upd.stepIndex && !upd.terminal,
                  'is-failed': i === upd.stepIndex && upd.terminal && !upd.ok,
                }"
              >
                <span class="upd-step-icon">
                  <i v-if="i < upd.stepIndex || (i === upd.stepIndex && upd.terminal && upd.ok)" class="mdi mdi-check-circle"></i>
                  <i v-else-if="i === upd.stepIndex && upd.terminal" class="mdi mdi-close-circle"></i>
                  <i v-else-if="i === upd.stepIndex" class="mdi mdi-loading upd-step-spin"></i>
                  <i v-else class="mdi mdi-circle-outline"></i>
                </span>
                <span class="upd-step-label">
                  {{ st.label }}<template v-if="i === upd.stepIndex && !upd.terminal && upd.detail"> <span class="upd-step-note">· {{ upd.detail }}</span></template>
                </span>
              </li>
            </ol>
            <div v-if="upd.terminal && upd.message" class="upd-prog-notice" :class="{ 'is-ok': upd.ok }">
              {{ upd.message }}{{ upd.ok ? '（页面已刷新过请忽略，否则请手动刷新后台）' : '' }}
            </div>
          </div>
        </div>
      </template>

      <!-- 更新设置 -->
      <div class="td-set-card">
        <div class="td-set-card-hd">
          <div class="td-set-icon" style="background: #eff6ff; color: #2563eb">
            <i class="mdi mdi-settings"></i>
          </div>
          <div>
            <h4>更新设置</h4>
            <p>GitHub / Gitee 仓库、下载策略与可选 Token</p>
          </div>
        </div>
        <div class="td-set-card-bd">
          <div class="upd-field">
            <label>GitHub 仓库</label>
            <t-input v-model="form.repo" placeholder="owner/repo" :maxlength="140" />
            <p class="upd-hint">格式 <code>owner/repo</code>；是否作为下载源、以及与 Gitee 的先后顺序由下方"下载策略"决定。</p>
          </div>
          <div class="upd-field">
            <label>Gitee 仓库</label>
            <t-input v-model="form.giteeRepo" placeholder="owner/repo（留空表示只走 GitHub）" :maxlength="140" />
            <p class="upd-hint">格式 <code>owner/repo</code>，也可直接粘贴 <code>https://gitee.com/owner/repo</code> 地址。Gitee 不会自动同步 GitHub 的 Release，需自建对应版本的 Release。</p>
          </div>
          <div class="upd-field">
            <label>下载策略</label>
            <t-radio-group v-model="form.sourcePolicy" class="upd-policy">
              <t-radio v-for="(pv, pk) in POLICY_LABELS" :key="pk" :value="pk" :label="pv" />
            </t-radio-group>
            <p class="upd-hint">国内服务器直连 GitHub 大概率超时，建议保持"Gitee 优先"；"仅用 Gitee"能彻底避免 GitHub 超时拖时间；"仅用 GitHub"适合海外或有代理的服务器。</p>
          </div>
          <div class="upd-field">
            <label>GitHub Token（可选）</label>
            <t-input v-model="form.token" type="password" :placeholder="form.hasToken ? '已设置，留空表示不修改' : '仅用于提高 API 速率限制，可留空'" :maxlength="128" />
            <label v-if="form.hasToken" class="upd-clear">
              <input type="checkbox" v-model="form.clearToken" /> 清除已保存的 Token
            </label>
            <p class="upd-hint">Token 只用于调用 GitHub API，不会跟着下载请求发给 Gitee，页面也不回显明文。</p>
          </div>
          <div class="upd-actions">
            <t-button theme="primary" :loading="saving" @click="saveSetting">
              <i class="mdi mdi-content-save-outline"></i> 保存更新设置
            </t-button>
          </div>
          <p class="upd-note">
            <b>更新说明：</b>更新会直接用 GitHub / Gitee Release 的包覆盖站点文件，覆盖前会备份并还原
            <code>config.php</code>、<code>cf_up.php</code>、<code>install/install.lock</code>、<code>runtime/bt_cookie/</code>；
            包里 <code>update/update_v*_*.sql</code> 的版本化迁移会按版本号依次执行（仅跑游标 <code>MN_dbver</code> 之后的增量），旧式单文件 <code>update/update.sql</code> 仍兼容。请在维护时段操作，更新过程中不要关闭页面。
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onBeforeUnmount } from 'vue'
import { MessagePlugin, DialogPlugin } from 'tdesign-vue-next'
import { postGn } from '@/shared/api/http'
import { updaterCheck, saveUpdaterConfig } from '@/admin/api/dashboard'

const boot = window.__TD_BOOT__ || {}

// 服务器端已检查更新,直接读取 boot
const info = ref(boot.updateInfo || null)
const currentVersion = ref(boot.currentVersion || 'V0.00')

const hasUpdate = computed(() => info.value && String(info.value.code) === '1')
const isLatest = computed(() => info.value && String(info.value.code) === '0')
const latestVersion = computed(() => (info.value && info.value.ver) || '-')

const updating = ref(false)
const saving = ref(false)

const POLICY_LABELS = {
  gitee_first: 'Gitee 优先（推荐国内服务器）',
  github_first: 'GitHub 优先',
  gitee_only: '仅用 Gitee',
  github_only: '仅用 GitHub',
}

const cfg = boot.updaterConfig || {}
const form = reactive({
  repo: cfg.repo || '',
  giteeRepo: cfg.giteeRepo || '',
  token: '',
  hasToken: Number(cfg.hasToken) === 1,
  clearToken: false,
  sourcePolicy: cfg.sourcePolicy || 'gitee_first',
})

// ------------------------------------------------------------------
// 更新进度状态机（对齐 classic update.php：async=1 → 轮询 progress.json）
// ------------------------------------------------------------------
const upd = reactive({
  visible: false,
  terminal: true,
  ok: null,
  message: '',
  steps: [],
  stepIndex: -1,
  stepLabel: '准备',
  detail: '',
  pct: null,
  startedAt: 0,
  lastUpdatedAt: 0,
  httpNote: '',
})

// 卡死判定：progress.json 的 updated_at 5 分钟不动 = 后端死了
// 总时长兜底：30 分钟仍未终态 = 前端不再等（只提示不判失败，后端可能仍在跑）
const STALL_MS = 300 * 1000
const MAX_WAIT_MS = 30 * 60 * 1000
let pollTimer = null
let elapsedTimer = null
let pollBusy = false
const nowTick = ref(Math.floor(Date.now() / 1000))

const elapsedText = computed(() => {
  const secs = Math.max(0, nowTick.value - (upd.startedAt || nowTick.value))
  const m = Math.floor(secs / 60)
  return (m > 0 ? m + '分' : '') + (secs % 60) + '秒'
})

const clampPct = computed(() => Math.max(0, Math.min(100, Math.round(upd.pct || 0))))

const barWidth = computed(() => {
  if (upd.pct === null) return '30%'
  let p = clampPct.value
  if (upd.terminal && upd.ok) p = 100
  return p + '%'
})

function stopTimers() {
  if (pollTimer) { clearInterval(pollTimer); pollTimer = null }
  if (elapsedTimer) { clearInterval(elapsedTimer); elapsedTimer = null }
}

function finalize(ok, message) {
  if (upd.terminal) return
  upd.terminal = true
  upd.ok = ok ? 1 : 0
  upd.message = message
  stopTimers()
  if (ok) MessagePlugin.success(message, 6000)
  else MessagePlugin.error(message, 8000)
}

function askUpdate() {
  const confirm = DialogPlugin.confirm({
    header: '确认开始更新',
    body: '将用 GitHub / Gitee Release 的包覆盖当前站点文件，过程可能需要几分钟，期间请勿关闭页面。确认开始更新？',
    confirmBtn: { content: '开始更新', theme: 'warning' },
    onConfirm: () => {
      confirm.destroy()
      startUpdate()
    },
  })
}

async function startUpdate() {
  upd.visible = true
  upd.terminal = false
  upd.ok = null
  upd.message = ''
  upd.steps = []
  upd.stepIndex = -1
  upd.stepLabel = '准备'
  upd.detail = '发起更新请求…'
  upd.pct = null
  upd.startedAt = Math.floor(Date.now() / 1000)
  upd.lastUpdatedAt = upd.startedAt
  upd.httpNote = ''
  updating.value = true
  nowTick.value = upd.startedAt
  elapsedTimer = setInterval(() => { nowTick.value = Math.floor(Date.now() / 1000) }, 1000)
  pollTimer = setInterval(poll, 1500)
  setTimeout(poll, 1000)

  let raw
  try {
    raw = await postGn('update', {})
  } catch (e) {
    // HTTP 中断不代表后端死了：FPM 下后端仍在跑，继续按进度轮询直到终态/卡死判定
    upd.httpNote = 'HTTP ' + ((e && e.response && e.response.status) || '?') + '（连接已断，若后端仍在跑会自动继续，进度以页面为准）'
    updating.value = false
    return
  }
  updating.value = false
  // 异步模式：HTTP 只回 202/started，终态完全靠 progress.json
  if (raw && Number(raw.async) === 1) {
    upd.detail = '后端已启动，等待进度反馈…'
    return
  }
  // 同步模式（无 fastcgi_finish_request）：HTTP 响应即终态
  const ok = !!(raw && (raw.qk === 1 || raw.qk === '1' || raw.code === '更新成功～请手动刷新页面'))
  let msg = ok ? '更新成功～请手动刷新页面' : ((raw && raw.code) || '更新失败')
  if (ok && raw && raw.source) msg += '｜' + raw.source + (raw.via ? '（' + raw.via + '）' : '')
  finalize(ok, msg)
}

async function poll() {
  if (pollBusy || !upd.visible || upd.terminal) return
  pollBusy = true
  try {
    const p = await postGn('upprogress', {})
    if (!p || Number(p.has) !== 1) return
    // 后端 progress.json 是权威状态：running=0 才算终态
    if (Array.isArray(p.steps)) upd.steps = p.steps
    if (typeof p.step_index === 'number') upd.stepIndex = p.step_index
    if (p.step_label) upd.stepLabel = p.step_label
    if (p.detail) upd.detail = p.detail
    if (typeof p.pct === 'number' || p.pct === null) upd.pct = p.pct
    if (p.started_at) upd.startedAt = Number(p.started_at)
    const beUpdated = Number(p.updated_at) || 0
    if (beUpdated > upd.lastUpdatedAt) upd.lastUpdatedAt = beUpdated
    if (Number(p.running) === 0) {
      const ok = Number(p.ok) === 1
      finalize(ok, p.message || (ok ? '更新完成' : '更新失败'))
      return
    }
    // 卡死判定：后端 progress.json 里 updated_at 5 分钟没动
    const nowSec = Math.floor(Date.now() / 1000)
    if (upd.lastUpdatedAt && (nowSec - upd.lastUpdatedAt) * 1000 > STALL_MS) {
      finalize(false, '进度已 5 分钟未更新，后端可能崩溃或超时；本地配置与后台目录在关闭时会自动还原，请刷新后台确认站点状态。')
      return
    }
    // 总等待兜底：30 分钟仍未终态，前端放弃等待；后端可能仍在跑，靠 progress.json 自锁防并发
    if (upd.startedAt && (nowSec - upd.startedAt) * 1000 > MAX_WAIT_MS) {
      finalize(false, '已等满 30 分钟仍无终态，前端停止轮询。请稍后刷新后台查看版本号是否已升；如仍未升级，可去服务器看 runtime/temp/update_tmp/progress.json 确认后端状态。')
    }
  } catch (e) {
    // 轮询单次失败忽略，下个周期重试
  } finally {
    pollBusy = false
  }
}

onBeforeUnmount(stopTimers)

// ------------------------------------------------------------------
// 检查 / 设置
// ------------------------------------------------------------------
async function recheck() {
  const r = await updaterCheck()
  if (r.ok && r.data && typeof r.data === 'object') {
    info.value = normalizeInfo(r.data)
    if (r.data.current) currentVersion.value = r.data.current
    MessagePlugin.success('已重新检查')
  } else {
    // 接口不可用时退回整页重查
    window.location.href = 'update.php?recheck=1'
  }
}

async function saveSetting() {
  const payload = {
    repo: form.repo.trim(),
    gitee_repo: form.giteeRepo.trim(),
    source_policy: form.sourcePolicy,
  }
  if (form.token && form.token.trim() !== '') payload.github_token = form.token.trim()
  if (form.clearToken) payload.clear_token = '1'
  saving.value = true
  const r = await saveUpdaterConfig(payload)
  saving.value = false
  if (r.ok) {
    MessagePlugin.success('保存成功')
    form.token = ''
    form.clearToken = false
    setTimeout(() => window.location.reload(), 1200)
  } else {
    MessagePlugin.error(r.message || '保存失败')
  }
}

function mb(bytes) {
  return (Number(bytes) / 1048576).toFixed(1) + ' MB'
}

// 后端 upcheck 返回的是原始检查结果，转成页面用的 updateInfo 结构
function normalizeInfo(d) {
  const ok = Number(d.ok) === 1
  const can = Number(d.can_update) === 1
  return {
    code: ok ? (can ? '1' : '0') : '-1',
    ver: ok ? d.latest : '-',
    msg: !ok
      ? '暂时无法检查更新：' + (d.error || '')
      : can
        ? '发现新版本，可执行在线覆盖更新'
        : '当前版本已是最新，无需更新',
    uplog: d.body || '',
    repo: d.repo,
    tag: d.tag,
    name: d.name,
    source: d.source,
    sourceLabel: d.source_label,
    assetName: d.asset_name,
    assetUrl: d.asset_url,
    assetSize: d.asset_size,
    publishedAt: d.published_at,
    fallback: d.fallback,
    sourcePolicy: d.source_policy,
    channelLabel: d.channel_label,
    currentVer: d.current,
  }
}
</script>

<style scoped>
.upd-wrap {
  display: flex;
  flex-direction: column;
  gap: 14px;
}
.stat-row {
  display: flex;
  flex-wrap: wrap;
  gap: 12px;
}
.stat-card {
  flex: 1;
  min-width: 200px;
  background: var(--td-surface);
  border: 1px solid var(--td-border);
  border-radius: var(--td-radius-lg);
  padding: 16px;
  display: flex;
  align-items: center;
  gap: 12px;
  box-shadow: var(--td-shadow);
}
.stat-icon {
  width: 44px;
  height: 44px;
  border-radius: 8px;
  display: grid;
  place-items: center;
  color: #fff;
  font-size: 22px;
  flex-shrink: 0;
}
.stat-num {
  font-size: 20px;
  font-weight: 700;
  color: var(--td-text);
  line-height: 1.2;
}
.stat-label {
  font-size: 12px;
  color: var(--td-text-secondary);
  margin-top: 2px;
}
.upd-btn {
  margin-left: auto;
}
.upd-section {
  margin: 0 0 8px;
  font-size: 13px;
  font-weight: 600;
  color: var(--td-text);
}
.upd-log {
  white-space: pre-wrap;
  word-break: break-word;
  max-height: 320px;
  overflow-y: auto;
  margin: 0;
}
.upd-kv {
  width: 100%;
  border-collapse: collapse;
}
.upd-kv td {
  font-size: 13px;
  padding: 6px 0;
  border-bottom: 1px solid #f1f5f9;
  color: var(--td-text);
  word-break: break-all;
}
.upd-kv td:first-child {
  color: var(--td-text-secondary);
  width: 96px;
  white-space: nowrap;
}
/* 进度面板（对齐 classic：细条 + 不定长动画 + 步骤列表） */
.upd-prog-card {
  border-color: #d9e4f5;
}
.upd-prog-hd {
  display: flex;
  align-items: baseline;
  justify-content: space-between;
  margin-bottom: 10px;
}
.upd-prog-title {
  font-size: 14px;
  font-weight: 600;
  color: var(--td-text);
}
.upd-prog-elapsed {
  font-size: 12px;
  color: var(--td-text-secondary);
  font-variant-numeric: tabular-nums;
}
.upd-prog-bar {
  height: 3px;
  background: #f1f5f9;
  overflow: hidden;
  margin-bottom: 8px;
  border-radius: 2px;
}
.upd-prog-bar > i {
  display: block;
  height: 100%;
  width: 0;
  background: #0052d9;
  transition: width 0.3s ease;
}
.upd-prog-bar.is-indet > i {
  width: 30% !important;
  animation: upd-indet 1.4s linear infinite;
}
@keyframes upd-indet {
  0% { transform: translateX(-100%); }
  100% { transform: translateX(340%); }
}
.upd-prog-detail {
  font-size: 12px;
  color: var(--td-text-secondary);
  min-height: 20px;
  line-height: 1.6;
  word-break: break-all;
}
.upd-prog-steps {
  list-style: none;
  padding: 0;
  margin: 12px 0 0;
  font-size: 13px;
  color: var(--td-text-secondary);
}
.upd-prog-steps li {
  display: flex;
  align-items: flex-start;
  gap: 8px;
  padding: 4px 0;
}
.upd-step-icon {
  width: 16px;
  flex-shrink: 0;
  text-align: center;
  color: #cbd5e1;
  font-size: 14px;
  line-height: 20px;
}
.upd-step-label {
  flex: 1;
}
.upd-step-note {
  font-size: 12px;
  color: var(--td-text-secondary);
}
.upd-prog-steps li.is-done .upd-step-icon {
  color: #2ba471;
}
.upd-prog-steps li.is-done .upd-step-label {
  color: var(--td-text-secondary);
}
.upd-prog-steps li.is-active {
  color: var(--td-text);
}
.upd-prog-steps li.is-active .upd-step-icon {
  color: #0052d9;
}
.upd-prog-steps li.is-active .upd-step-label {
  font-weight: 600;
}
.upd-prog-steps li.is-failed .upd-step-icon {
  color: #d54941;
}
.upd-prog-steps li.is-failed .upd-step-label {
  color: #b42318;
}
.upd-step-spin {
  display: inline-block;
  animation: upd-spin 1s linear infinite;
}
@keyframes upd-spin {
  from { transform: rotate(0); }
  to { transform: rotate(360deg); }
}
.upd-prog-notice {
  margin-top: 10px;
  padding: 8px 10px;
  background: #fff7ed;
  border: 1px solid #fed7aa;
  color: #9a3412;
  font-size: 12px;
  line-height: 1.6;
  border-radius: 6px;
}
.upd-prog-notice.is-ok {
  background: #ecfdf5;
  border-color: #a7f3d0;
  color: #065f46;
}
.upd-field {
  margin-bottom: 16px;
}
.upd-field > label {
  display: block;
  font-size: 13px;
  font-weight: 500;
  color: var(--td-text);
  margin-bottom: 6px;
}
.upd-policy {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}
.upd-clear {
  display: block;
  margin-top: 8px;
  font-size: 12px;
  color: var(--td-text-secondary);
}
.upd-hint {
  margin: 6px 0 0;
  font-size: 12px;
  color: var(--td-text-secondary);
  line-height: 1.7;
}
.upd-hint code {
  background: #f3f3f3;
  padding: 1px 4px;
  border-radius: 4px;
}
.upd-actions {
  margin-top: 6px;
}
.upd-note {
  margin: 16px 0 0;
  padding: 12px 14px;
  background: #f8fafc;
  border: 1px solid #eef1f5;
  font-size: 12px;
  line-height: 1.8;
  color: var(--td-text-secondary);
}
.upd-note code {
  background: #fff;
  border: 1px solid #e8ecf1;
  padding: 1px 4px;
}
</style>
