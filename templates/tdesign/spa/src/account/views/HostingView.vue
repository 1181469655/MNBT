<template>
  <div class="td-page">
    <div class="td-page-head">
      <div>
        <h3 class="td-page-title"><i class="mdi mdi-server"></i> 我的主机</h3>
        <p class="td-page-subtitle">查看已开通的虚拟主机资产</p>
      </div>
      <t-button variant="outline" @click="router.push('/shop')">
        <i class="mdi mdi-cart"></i> 前往商城
      </t-button>
    </div>

    <div v-if="assets.length" class="asset-grid">
      <div class="asset-card td-card" v-for="asset in assets" :key="asset.id">
        <div class="asset-head">
          <div class="asset-icon"><i class="mdi mdi-server"></i></div>
          <div class="asset-info">
            <strong>{{ asset.plan_name || '虚拟主机' }}</strong>
            <span class="asset-node">节点：{{ asset.ssbt || '—' }}</span>
          </div>
          <t-tag :theme="statusTheme(asset.status)" variant="light">
            {{ statusText(asset.status) }}
          </t-tag>
        </div>
        <div class="asset-meta">
          <div class="meta-item">
            <span>开通时间</span>
            <b>{{ asset.created_at || '—' }}</b>
          </div>
          <div class="meta-item">
            <span>到期时间</span>
            <b :class="{ expired: isExpired(asset) }">{{ asset.expire_at || '—' }}</b>
          </div>
          <div class="meta-item" v-if="asset.sqldz">
            <span>数据库地址</span>
            <b>{{ asset.sqldz }}</b>
          </div>
        </div>
        <div class="asset-foot" v-if="asset.host_user">
          <span>主机账号：<b>{{ asset.host_user }}</b></span>
          <span>主机密码：<b class="pass">{{ asset.host_pass }}</b></span>
        </div>

        <div class="asset-actions" v-if="asset.host_id > 0">
          <t-button v-if="canRenew(asset)" theme="primary" variant="outline" size="small" @click="openRenew(asset)">
            <i class="mdi mdi-autorenew"></i> 续费
          </t-button>
          <span v-else-if="asset.status !== 'cancelled'" class="renew-hint">
            <i class="mdi mdi-information-outline"></i> 套餐已下架，续费请联系管理员
          </span>
        </div>
      </div>
    </div>

    <t-empty v-else description="暂无主机资产，前往商城选购" style="padding: 60px 0">
      <t-button theme="primary" @click="router.push('/shop')">去选购套餐</t-button>
    </t-empty>

    <t-dialog
      v-model:visible="renewVisible"
      header="续费主机"
      width="520px"
      :confirm-btn="{ content: '去支付', theme: 'primary', loading: renewing, disabled: !renewReady }"
      @confirm="submitRenew"
    >
      <div v-if="renewTarget" class="renew-body">
        <div class="renew-row"><span>套餐</span><b>{{ renewTarget.plan_name || '虚拟主机' }}</b></div>
        <div class="renew-row"><span>当前到期</span><b>{{ renewTarget.expire_at || '—' }}</b></div>
        <div class="renew-row"><span>续费后到期</span><b class="renew-new">{{ renewPreview }}</b></div>

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
import { MessagePlugin } from 'tdesign-vue-next'
import { getShopAssets, getShopMethods, renewShopAsset, goPay, centsToYuan } from '@/account/api/plugins'

const router = useRouter()
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
  return { active: '运行中', expired: '已到期', cancelled: '已停用' }[status] || status
}

function statusTheme(status) {
  if (status === 'active') return 'success'
  if (status === 'expired') return 'warning'
  return 'default'
}

function isExpired(asset) {
  return !!asset.expire_at && asset.expire_at < new Date().toISOString().slice(0, 10)
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
  return asset.host_id > 0 && asset.status !== 'cancelled' && periodOptions(asset).length > 0
}

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
    const res = await getShopMethods()
    if (res.ok) renewMethods.value = res.data.methods || []
  }
}

async function submitRenew() {
  if (!renewTarget.value || !renewReady.value || renewing.value) return
  renewing.value = true
  const res = await renewShopAsset(renewTarget.value.id, renewPeriod.value, renewType.value || '')
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

async function load() {
  const res = await getShopAssets()
  if (res.ok) assets.value = res.data.assets || []
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
}
.meta-item b.expired {
  color: var(--td-error);
}
.asset-foot {
  margin-top: 12px;
  display: flex;
  justify-content: space-between;
  gap: 8px;
  font-size: 12px;
  color: var(--td-text-secondary);
  flex-wrap: wrap;
}
.asset-foot b {
  color: var(--td-text);
  font-weight: 500;
}
.asset-foot .pass {
  font-family: Consolas, monospace;
}
.asset-actions {
  margin-top: 12px;
  display: flex;
  align-items: center;
  gap: 8px;
}
.renew-hint {
  font-size: 12px;
  color: var(--td-text-placeholder);
  display: inline-flex;
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
.renew-row .renew-new {
  color: var(--td-brand);
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

@media (max-width: 860px) {
  .asset-grid {
    grid-template-columns: 1fr;
  }
}
</style>
