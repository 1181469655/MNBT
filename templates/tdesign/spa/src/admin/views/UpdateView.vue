<template>
  <div class="td-page">
    <div class="td-page-head">
      <div>
        <h3 class="td-page-title"><i class="mdi mdi-cloud-download"></i>系统更新</h3>
        <p class="td-page-subtitle">检查新版本并执行在线更新</p>
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
              <div class="stat-label">最新版本</div>
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
            <t-button theme="warning" :loading="updating" @click="doUpdate" class="upd-btn">
              <i class="mdi mdi-update"></i> 立刻更新
            </t-button>
          </div>
          <div class="td-set-card-bd">
            <h5 class="upd-section">下载来源</h5>
            <table class="upd-kv">
              <tr><td>更新仓库</td><td>{{ info.repo }}</td></tr>
              <tr><td>包来源</td><td>{{ info.sourceLabel }}<template v-if="info.assetSize">（{{ mb(info.assetSize) }}）</template></td></tr>
              <tr><td>下载通道</td><td>github.com 直连<template v-if="info.mirrors && info.mirrors.length">，失败后依次尝试：{{ info.mirrors.join('、') }}</template></td></tr>
              <tr v-if="info.publishedAt"><td>发布时间</td><td>{{ String(info.publishedAt).slice(0, 10) }}</td></tr>
              <tr v-if="info.fallback"><td>检查方式</td><td>latest 接口不可用，已改用发布列表取最高版本</td></tr>
            </table>
          </div>
          <div class="td-set-card-bd" v-if="info.uplog">
            <h5 class="upd-section">更新日志</h5>
            <pre class="td-code-block upd-log">{{ info.uplog }}</pre>
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
              <tr><td>更新仓库</td><td>{{ info.repo }}</td></tr>
              <tr><td>包来源</td><td>{{ info.sourceLabel }}</td></tr>
              <tr><td>下载通道</td><td>github.com 直连<template v-if="info.mirrors && info.mirrors.length">，失败后依次尝试：{{ info.mirrors.join('、') }}</template></td></tr>
            </table>
          </div>
          <div class="td-set-card-bd" v-if="info.uplog">
            <h5 class="upd-section">版本日志</h5>
            <pre class="td-code-block upd-log">{{ info.uplog }}</pre>
          </div>
        </div>

        <!-- 检查失败 -->
        <div v-else class="td-set-card">
          <div class="td-set-card-hd">
            <div class="td-set-icon" style="background: #f5f6f8; color: #8c8c8c">
              <i class="mdi mdi-cloud-off"></i>
            </div>
            <div>
              <h4>{{ info.msg || '暂时无法检查更新' }}</h4>
              <p>更新只走 GitHub Release，请检查服务器能否访问 api.github.com 或更新设置里的仓库与镜像</p>
            </div>
          </div>
        </div>
      </template>

      <!-- 更新设置 -->
      <div class="td-set-card">
        <div class="td-set-card-hd">
          <div class="td-set-icon" style="background: #eff6ff; color: #2563eb">
            <i class="mdi mdi-cog-outline"></i>
          </div>
          <div>
            <h4>更新设置</h4>
            <p>GitHub 仓库、下载镜像与可选 Token</p>
          </div>
        </div>
        <div class="td-set-card-bd">
          <div class="upd-field">
            <label>GitHub 仓库</label>
            <t-input v-model="form.repo" placeholder="owner/repo" :maxlength="140" />
            <p class="upd-hint">更新包只能从这个仓库的 Release 里取。</p>
          </div>
          <div class="upd-field">
            <label>下载镜像</label>
            <div v-for="(m, i) in form.mirrors" :key="i" class="upd-mirror-row">
              <t-input v-model="form.mirrors[i]" placeholder="https://gh-proxy.com/" :maxlength="160" />
              <t-button theme="default" variant="outline" @click="delMirror(i)">
                <i class="mdi mdi-close"></i>
              </t-button>
            </div>
            <t-button theme="default" variant="outline" @click="addMirror">
              <i class="mdi mdi-plus"></i> 添加镜像
            </t-button>
            <p class="upd-hint">先 github.com 直连，再按上面的顺序依次尝试镜像；镜像地址会在原始 GitHub 地址前面加前缀。</p>
          </div>
          <div class="upd-field">
            <label>GitHub Token（可选）</label>
            <t-input v-model="form.token" type="password" :placeholder="form.hasToken ? '已设置，留空表示不修改' : '仅用于提高 API 速率限制，可留空'" :maxlength="128" />
            <label v-if="form.hasToken" class="upd-clear">
              <input type="checkbox" v-model="form.clearToken" /> 清除已保存的 Token
            </label>
            <p class="upd-hint">Token 只用于调用 GitHub API，不会跟着下载请求发给镜像，页面也不回显明文。</p>
          </div>
          <div class="upd-actions">
            <t-button theme="primary" :loading="saving" @click="saveSetting">
              <i class="mdi mdi-content-save-outline"></i> 保存更新设置
            </t-button>
          </div>
          <p class="upd-note">
            更新会直接用 GitHub Release 的包覆盖站点文件，覆盖前会备份并还原
            <code>config.php</code>、<code>cf_up.php</code>、<code>install/install.lock</code>、<code>api/cookie/</code>；
            包里 <code>update/update_v*_*.sql</code> 的版本化迁移会按版本号依次执行（仅跑游标 <code>MN_dbver</code> 之后的增量），旧式单文件 <code>update/update.sql</code> 仍兼容。请在维护时段操作。
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed } from 'vue'
import { MessagePlugin } from 'tdesign-vue-next'
import { systemUpdate, updaterCheck, saveUpdaterConfig } from '@/admin/api/dashboard'

const boot = window.__TD_BOOT__ || {}

// 服务器端已检查更新,直接读取 boot
const info = ref(boot.updateInfo || null)
const currentVersion = ref(boot.currentVersion || 'V0.00')

const hasUpdate = computed(() => info.value && String(info.value.code) === '1')
const isLatest = computed(() => info.value && String(info.value.code) === '0')

const latestVersion = computed(() => {
  if (!info.value || !info.value.ver) return '-'
  return info.value.ver
})

const updating = ref(false)
const saving = ref(false)

const cfg = boot.updaterConfig || {}
const form = reactive({
  repo: cfg.repo || '',
  mirrors: Array.isArray(cfg.mirrors) ? cfg.mirrors.slice() : [],
  token: '',
  hasToken: Number(cfg.hasToken) === 1,
  clearToken: false,
})

function mb(bytes) {
  return (Number(bytes) / 1048576).toFixed(1) + ' MB'
}

function addMirror() {
  form.mirrors.push('')
}

function delMirror(i) {
  if (form.mirrors.length <= 1) {
    form.mirrors[0] = ''
    return
  }
  form.mirrors.splice(i, 1)
}

async function doUpdate() {
  updating.value = true
  const r = await systemUpdate()
  updating.value = false
  if (r.ok) {
    MessagePlugin.success('更新成功,请手动刷新页面')
    setTimeout(() => {
      window.location.reload()
    }, 1200)
  } else {
    MessagePlugin.error(r.message || '更新失败')
  }
}

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
  const mirrors = form.mirrors.map((m) => String(m || '').trim()).filter((m, i, arr) => m !== '' && arr.indexOf(m) === i)
  const payload = { repo: form.repo.trim(), mirrors }
  if (form.token && form.token.trim() !== '') payload.github_token = form.token.trim()
  if (form.clearToken) payload.clear_token = '1'
  saving.value = true
  const r = await saveUpdaterConfig(payload)
  saving.value = false
  if (r.ok) {
    MessagePlugin.success('保存成功')
    form.token = ''
    form.clearToken = false
    form.mirrors = mirrors.length ? mirrors.slice() : ['']
    setTimeout(() => window.location.reload(), 1200)
  } else {
    MessagePlugin.error(r.message || '保存失败')
  }
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
    mirrors: d.mirrors || [],
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
.upd-mirror-row {
  display: flex;
  gap: 8px;
  margin-bottom: 8px;
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
