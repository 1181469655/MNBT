<template>
  <div class="td-page">
    <div class="td-page-head">
      <div>
        <h3 class="td-page-title"><i class="mdi mdi-docker"></i> 我的 Docker</h3>
        <p class="td-page-subtitle">查看已开通的 Docker 账号，可重置密码、刷新容器状态</p>
      </div>
      <t-button variant="outline" @click="router.push('/docker-shop')">
        <i class="mdi mdi-cart"></i> 前往商城
      </t-button>
    </div>

    <div v-if="assets.length" class="asset-grid">
      <div class="asset-card td-card" v-for="asset in assets" :key="asset.id">
        <div class="asset-head">
          <div class="asset-icon"><i class="mdi mdi-docker"></i></div>
          <div class="asset-info">
            <strong>{{ asset.plan_name || 'Docker 容器' }}</strong>
            <span class="asset-node">节点：{{ asset.node_name || '—' }}</span>
          </div>
          <t-tag :theme="statusTheme(asset.status)" variant="light">
            {{ statusText(asset.status) }}
          </t-tag>
        </div>

        <div class="asset-meta">
          <div class="meta-item">
            <span>容器状态</span>
            <b>
              <i class="mdi mdi-circle" :class="'dot-' + containerStatus(asset.container_status)"></i>
              {{ containerText(asset.container_status) }}
            </b>
          </div>
          <div class="meta-item">
            <span>磁盘用量</span>
            <b>{{ diskText(asset.disk_usage) }}</b>
          </div>
          <div class="meta-item">
            <span>到期时间</span>
            <b :class="{ expired: isExpired(asset) }">{{ asset.expire_at || '—' }}</b>
          </div>
          <div class="meta-item">
            <span>开通时间</span>
            <b>{{ asset.created_at || '—' }}</b>
          </div>
        </div>

        <div class="asset-foot">
          <span>Docker 账号：<b class="mono">{{ asset.docker_username || '—' }}</b></span>
          <span class="pass-row">
            Docker 密码：
            <b class="mono pass" :class="{ revealed: asset._reveal }">{{ asset._reveal ? asset.docker_password : '••••••••' }}</b>
            <button class="icon-btn" title="显示/隐藏密码" @click="toggleReveal(asset)">
              <i class="mdi" :class="asset._reveal ? 'mdi-eye-off-outline' : 'mdi-eye-outline'"></i>
            </button>
          </span>
        </div>

        <div class="asset-actions">
          <t-button
            theme="primary"
            variant="outline"
            size="small"
            :href="dockerUrl"
            target="_blank"
            rel="noopener"
          >
            <i class="mdi mdi-console"></i> 进入控制台
          </t-button>
          <t-button variant="outline" size="small" :loading="asset._syncing" @click="onSync(asset)">
            <i class="mdi mdi-refresh"></i> 刷新状态
          </t-button>
          <t-button
            variant="outline"
            size="small"
            theme="warning"
            :loading="asset._resetting"
            @click="onReset(asset)"
          >
            <i class="mdi mdi-key-change"></i> 重置密码
          </t-button>
          <t-button v-if="canRenew(asset)" size="small" @click="openRenew(asset)">
            <i class="mdi mdi-autorenew"></i> 续费
          </t-button>
        </div>

        <div v-if="asset.docker_user_id > 0 && !canRenew(asset) && asset.status !== 'cancelled'" class="renew-hint">
          <i class="mdi mdi-information-outline"></i> 套餐已下架，续费请联系管理员
        </div>
      </div>
    </div>

    <t-empty v-else description="暂无 Docker 资产，前往商城选购" style="padding: 60px 0">
      <t-button theme="primary" @click="router.push('/docker-shop')">去选购套餐</t-button>
    </t-empty>

    <t-dialog
      v-model:visible="renewVisible"
      header="续费 Docker"
      width="520px"
      :confirm-btn="{ content: '去支付', theme: 'primary', loading: renewing, disabled: !renewReady }"
      @confirm="submitRenew"
    >
      <div v-if="renewTarget" class="renew-body">
        <div class="renew-row"><span>套餐</span><b>{{ renewTarget.plan_name || 'Docker 容器' }}</b></div>
        <div class="renew-row"><span>Docker 账号</span><b class="mono">{{ renewTarget.docker_username || '—' }}</b></div>
        <div class="renew-row"><span>当前到期</span><b>{{ renewTarget.expire_at || '—' }}</b></div>
        <div class="renew-row"><span>续费后到期</span><b class="renew-new">{{ renewPreview }}</b></div>

        <t-alert v-if="dockerQk === 'pruned'" theme="warning" class="renew-alert">
          原容器已被到期清理，续费成功后请进入控制台重新创建容器。
        </t-alert>
        <t-alert v-else-if="dockerQk === 'paused'" theme="warning" class="renew-alert">
          账户处于暂停状态，续费仅延长到期时间，恢复使用请联系管理员。
        </t-alert>

        <div class="renew-section">续费周期</div>
        <t-radio-group v-model="renewPeriod" class="renew-radios">
          <t-radio-button v-for="opt in renewPeriodOptions" :key="opt.value" :value="opt.value">
            {{ opt.label }} ¥{{ centsToYuan(opt.price) }}
          </t-radio-button>
        </t-radio-group>

        <template v-if="renewPrice > 0">
          <div class="renew-section">支付方式</div>
          <t-radio-group v-model="renewType" class="renew-radios">
            <t-radio v-for="m in renewMethods" :key="m.plugin + '__' + m.method" :value="m.plugin + '__' + m.method">
              {{ m.display_name || (m.plugin + ' / ' + m.method) }}
            </t-radio>
          </t-radio-group>
        </template>
      </div>
    </t-dialog>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { MessagePlugin, DialogPlugin } from 'tdesign-vue-next'
import { getDockerShopAssets, getDockerShopMethods, renewDockerAsset, resetDockerPassword, syncDockerStatus, dockerConsoleUrl, goPay, centsToYuan } from '@/account/api/plugins'

const router = useRouter()
const boot = window.__TD_BOOT__ || {}
const dockerUrl = dockerConsoleUrl() || boot.dockerUrl || ''

const assets = ref([])

const PERIOD_LABELS = { month: '月付', quarter: '季付', half_year: '半年付', year: '年付', two_year: '两年付', three_year: '三年付' }
const PERIOD_MONTHS = { month: 1, quarter: 3, half_year: 6, year: 12, two_year: 24, three_year: 36 }
const PRICE_FIELDS = {
  month: 'price_month_cents',
  quarter: 'price_quarter_cents',
  half_year: 'price_half_year_cents',
  year: 'price_year_cents',
  two_year: 'price_two_year_cents',
  three_year: 'price_three_year_cents',
}

function statusText(status) {
  return { active: '使用中', expired: '已到期', cancelled: '已停用' }[status] || status
}

function statusTheme(status) {
  if (status === 'active') return 'success'
  if (status === 'expired') return 'warning'
  return 'default'
}

function containerStatus(s) {
  return { running: 'running', stopped: 'stopped', creating: 'creating', none: 'none' }[s] || 'none'
}

function containerText(s) {
  return { running: '运行中', stopped: '已停止', creating: '创建中', none: '未创建' }[s] || '未知'
}

function diskText(usage) {
  const n = Number(usage || 0)
  if (!n) return '—'
  if (n >= 1024 * 1024) return (n / 1024 / 1024).toFixed(1) + ' GB'
  if (n >= 1024) return (n / 1024).toFixed(1) + ' MB'
  return n + ' KB'
}

function isExpired(asset) {
  return !!asset.expire_at && asset.expire_at < new Date().toISOString().slice(0, 10)
}

function toggleReveal(asset) {
  asset._reveal = !asset._reveal
}

async function onSync(asset) {
  asset._syncing = true
  const res = await syncDockerStatus(asset.id)
  asset._syncing = false
  if (!res.ok) {
    MessagePlugin.error(res.message || '刷新失败')
    return
  }
  asset.container_status = res.data.container_status ?? asset.container_status
  asset.container_id = res.data.container_id ?? asset.container_id
  asset.disk_usage = res.data.disk_usage ?? asset.disk_usage
  MessagePlugin.success('状态已刷新')
}

function onReset(asset) {
  const dialog = DialogPlugin.confirm({
    header: '重置 Docker 密码',
    body: `确定要重置「${asset.docker_username || asset.plan_name}」的密码吗？新密码将立即生效。`,
    confirmBtn: { content: '确认重置', theme: 'danger' },
    onConfirm: async () => {
      dialog.destroy()
      asset._resetting = true
      const res = await resetDockerPassword(asset.id)
      asset._resetting = false
      if (!res.ok) {
        MessagePlugin.error(res.message || '重置失败')
        return
      }
      asset.docker_password = res.data.password || asset.docker_password
      asset._reveal = true
      MessagePlugin.success('密码已重置，请及时保存')
    },
    onClose: () => dialog.destroy(),
  })
}

async function load() {
  const res = await getDockerShopAssets()
  if (res.ok) assets.value = (res.data.assets || []).map((a) => ({ ...a, _reveal: false }))
}

/* ===== 续费 ===== */
const renewVisible = ref(false)
const renewing = ref(false)
const renewTarget = ref(null)
const renewPeriod = ref('')
const renewType = ref('')
const renewPeriodOptions = ref([])
const renewMethods = ref([])

function periodOptions(asset) {
  const all = Object.keys(PERIOD_LABELS)
  const raw = (asset.enabled_periods || '').toString().trim()
  const enabled = raw
    ? raw.split(',').map((s) => s.trim()).filter((p) => all.includes(p))
    : all.filter((p) => Number(asset[PRICE_FIELDS[p]] ?? 0) > 0)
  return enabled.map((p) => ({ value: p, label: PERIOD_LABELS[p], price: Number(asset[PRICE_FIELDS[p]] ?? 0) }))
}

function canRenew(asset) {
  return asset.docker_user_id > 0 && asset.status !== 'cancelled' && periodOptions(asset).length > 0
}

const dockerQk = computed(() => (renewTarget.value ? (renewTarget.value.docker_qk || 'active') : 'active'))
const renewPrice = computed(() => (renewTarget.value ? Number(renewTarget.value[PRICE_FIELDS[renewPeriod.value]] ?? 0) : 0))
const renewReady = computed(() => !!renewPeriod.value && (renewPrice.value === 0 || !!renewType.value))

const renewPreview = computed(() => {
  const t = renewTarget.value
  if (!t) return '—'
  const cur = t.expire_at ? Date.parse(t.expire_at) : NaN
  const base = (!isNaN(cur) && cur > Date.now()) ? new Date(cur) : new Date()
  base.setMonth(base.getMonth() + (PERIOD_MONTHS[renewPeriod.value] || 1))
  return base.toISOString().slice(0, 10)
})

async function openRenew(asset) {
  renewTarget.value = asset
  const opts = periodOptions(asset)
  renewPeriodOptions.value = opts
  renewPeriod.value = opts.length ? opts[0].value : ''
  renewType.value = ''
  renewVisible.value = true
  if (!renewMethods.value.length) {
    const res = await getDockerShopMethods()
    if (res.ok) renewMethods.value = res.data.methods || []
  }
}

async function submitRenew() {
  if (!renewTarget.value || !renewReady.value || renewing.value) return
  renewing.value = true
  const res = await renewDockerAsset(renewTarget.value.id, renewPeriod.value, renewType.value || '')
  renewing.value = false
  if (!res.ok) {
    MessagePlugin.error(res.message || '创建续费订单失败')
    return
  }
  if (goPay(res)) return
  // 0 元续费或未触发跳转：直接刷新
  renewVisible.value = false
  MessagePlugin.success('续费成功')
  load()
}

onMounted(load)
</script>

<style scoped>
.asset-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 16px;
}
.asset-card {
  padding: 18px;
}
.asset-head {
  display: flex;
  align-items: center;
  gap: 12px;
}
.asset-icon {
  width: 42px;
  height: 42px;
  border-radius: 10px;
  background: var(--td-brand-light);
  color: var(--td-brand);
  display: grid;
  place-items: center;
  font-size: 22px;
  flex-shrink: 0;
}
.asset-info {
  flex: 1;
  min-width: 0;
  display: flex;
  flex-direction: column;
}
.asset-info strong {
  font-size: 15px;
  color: var(--td-text);
  font-weight: 600;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.asset-node {
  font-size: 12px;
  color: var(--td-text-secondary);
  margin-top: 2px;
}
.asset-meta {
  margin-top: 14px;
  border-top: 1px dashed var(--td-border);
  padding-top: 12px;
}
.meta-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  font-size: 13px;
  padding: 4px 0;
}
.meta-item span {
  color: var(--td-text-placeholder);
}
.meta-item b {
  color: var(--td-text);
  font-weight: 500;
  display: inline-flex;
  align-items: center;
  gap: 6px;
}
.meta-item b.expired {
  color: var(--td-error);
}
.dot-running { color: #2ba471; font-size: 10px; }
.dot-stopped { color: #e37318; font-size: 10px; }
.dot-creating { color: #0052d9; font-size: 10px; }
.dot-none { color: var(--td-text-placeholder); font-size: 10px; }
.asset-foot {
  margin-top: 12px;
  border-top: 1px dashed var(--td-border);
  padding-top: 10px;
  display: flex;
  flex-direction: column;
  gap: 6px;
  font-size: 12px;
  color: var(--td-text-secondary);
}
.asset-foot b {
  color: var(--td-text);
  font-weight: 500;
}
.asset-foot .mono {
  font-family: Consolas, Monaco, monospace;
}
.pass-row {
  display: flex;
  align-items: center;
  gap: 6px;
}
.asset-actions {
  display: flex;
  gap: 8px;
  margin-top: 14px;
  flex-wrap: wrap;
}
.renew-hint {
  margin-top: 8px;
  font-size: 12px;
  color: var(--td-text-placeholder);
  display: flex;
  align-items: center;
  gap: 4px;
}

/* 续费弹窗 */
.renew-body {
  padding: 4px 2px;
}
.renew-row {
  display: flex;
  justify-content: space-between;
  font-size: 13px;
  padding: 5px 0;
}
.renew-row span {
  color: var(--td-text-placeholder);
}
.renew-row b {
  color: var(--td-text);
  font-weight: 500;
}
.renew-row .mono {
  font-family: Consolas, Monaco, monospace;
}
.renew-row .renew-new {
  color: var(--td-brand);
}
.renew-alert {
  margin-top: 10px;
}
.renew-section {
  margin-top: 16px;
  margin-bottom: 8px;
  font-size: 13px;
  font-weight: 500;
  color: var(--td-text);
}
.renew-radios {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}
.icon-btn {
  border: none;
  background: none;
  cursor: pointer;
  color: var(--td-text-placeholder);
  font-size: 15px;
  line-height: 1;
  padding: 2px;
  transition: color var(--td-dur) var(--td-ease);
}
.icon-btn:hover {
  color: var(--td-brand);
}

@media (max-width: 860px) {
  .asset-grid {
    grid-template-columns: 1fr;
  }
}
</style>
