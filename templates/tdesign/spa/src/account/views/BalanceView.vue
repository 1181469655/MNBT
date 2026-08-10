<template>
  <div class="td-page">
    <!-- 余额总览 -->
    <div class="balance-hero td-card">
      <div class="balance-hero-left">
        <div class="balance-hero-icon"><i class="mdi mdi-wallet"></i></div>
        <div>
          <div class="balance-hero-label">当前余额</div>
          <div class="balance-hero-amount">¥{{ balanceText }}</div>
        </div>
      </div>
      <div class="balance-hero-right">
        <t-button theme="primary" @click="router.push('/balance/recharge')">
          <i class="mdi mdi-plus"></i> 立即充值
        </t-button>
      </div>
    </div>

    <!-- 流水列表 -->
    <div class="td-card">
      <div class="td-card-head">余额流水</div>
      <div class="log-list" v-if="logs.length">
        <div class="log-item" v-for="log in logs" :key="log.id">
          <div class="log-type" :class="'log-type-' + (log.amount >= 0 ? 'in' : 'out')">
            <i class="mdi" :class="typeIcon(log.type)"></i>
          </div>
          <div class="log-main">
            <div class="log-title">
              <strong>{{ typeText(log.type) }}</strong>
              <span class="log-no" v-if="log.order_no">单号 {{ log.order_no }}</span>
            </div>
            <div class="log-meta">
              <span v-if="log.remark">{{ log.remark }}</span>
              <span class="log-time">{{ log.created_at }}</span>
            </div>
          </div>
          <div class="log-amount" :class="log.amount >= 0 ? 'amount-in' : 'amount-out'">
            {{ log.amount >= 0 ? '+' : '' }}{{ centsToYuan(log.amount) }}
          </div>
          <div class="log-balance">余额 ¥{{ centsToYuan(log.balance_after) }}</div>
        </div>
      </div>
      <t-empty v-else description="暂无流水记录" style="padding: 40px 0" />

      <!-- 分页 -->
      <div class="pager" v-if="total > perPage">
        <t-pagination
          v-model:current="page"
          :page-size="perPage"
          :total="total"
          :show-jumper="true"
          @change="load"
        />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { getBalanceInfo, centsToYuan } from '@/account/api/plugins'

const router = useRouter()
const balanceCents = ref(0)
const logs = ref([])
const page = ref(1)
const perPage = 15
const total = ref(0)

const balanceText = computed(() => centsToYuan(balanceCents.value))

function typeText(type) {
  return { recharge: '充值', consume: '消费', refund: '退款', adjust: '调整' }[type] || type
}

function typeIcon(type) {
  return {
    recharge: 'mdi-cash',
    consume: 'mdi-cart-outline',
    refund: 'mdi-currency-cny',
    adjust: 'mdi-tune',
  }[type] || 'mdi-history'
}

async function load() {
  const res = await getBalanceInfo(page.value, perPage)
  if (!res.ok) return
  balanceCents.value = res.data.balance_cents ?? 0
  const logsData = res.data.logs || {}
  logs.value = logsData.list || []
  total.value = logsData.total || 0
}

onMounted(load)
</script>

<style scoped>
.balance-hero {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  padding: 24px;
  margin-bottom: 16px;
  background: linear-gradient(135deg, var(--td-brand) 0%, var(--td-brand-dark) 100%);
  border: none;
  flex-wrap: wrap;
}
.balance-hero-left {
  display: flex;
  align-items: center;
  gap: 16px;
  min-width: 0;
}
.balance-hero-icon {
  width: 52px;
  height: 52px;
  border-radius: 12px;
  background: rgba(255, 255, 255, 0.18);
  display: grid;
  place-items: center;
  color: #fff;
  font-size: 28px;
  flex-shrink: 0;
}
.balance-hero-label {
  font-size: 13px;
  color: rgba(255, 255, 255, 0.85);
}
.balance-hero-amount {
  font-size: 28px;
  font-weight: 700;
  color: #fff;
  letter-spacing: -0.02em;
  margin-top: 2px;
}
.balance-hero-right :deep(.t-button) {
  --td-brand-color: #fff;
  --td-brand-color-hover: rgba(255, 255, 255, 0.92);
  --td-brand-color-active: rgba(255, 255, 255, 0.85);
  --td-brand-color-focus: rgba(255, 255, 255, 0.2);
  color: var(--td-brand);
  border: none;
}

/* 流水列表 */
.log-list {
  padding: 4px 0;
}
.log-item {
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 14px 18px;
  border-bottom: 1px solid var(--td-border);
}
.log-item:last-child {
  border-bottom: none;
}
.log-type {
  width: 38px;
  height: 38px;
  border-radius: 10px;
  display: grid;
  place-items: center;
  font-size: 19px;
  flex-shrink: 0;
}
.log-type-in {
  background: #e8f8f0;
  color: #2ba471;
}
.log-type-out {
  background: #fdeeee;
  color: #d54941;
}
.log-main {
  flex: 1;
  min-width: 0;
}
.log-title {
  display: flex;
  align-items: center;
  gap: 10px;
  flex-wrap: wrap;
}
.log-title strong {
  font-size: 14px;
  color: var(--td-text);
  font-weight: 600;
}
.log-no {
  font-size: 12px;
  color: var(--td-text-placeholder);
}
.log-meta {
  display: flex;
  align-items: center;
  gap: 12px;
  font-size: 12px;
  color: var(--td-text-secondary);
  margin-top: 3px;
  flex-wrap: wrap;
}
.log-time {
  color: var(--td-text-placeholder);
}
.log-amount {
  font-size: 15px;
  font-weight: 600;
  white-space: nowrap;
}
.amount-in {
  color: #2ba471;
}
.amount-out {
  color: #d54941;
}
.log-balance {
  font-size: 12px;
  color: var(--td-text-secondary);
  white-space: nowrap;
  text-align: right;
}

.pager {
  display: flex;
  justify-content: flex-end;
  padding: 16px 18px;
  border-top: 1px solid var(--td-border);
}

@media (max-width: 640px) {
  .log-balance {
    display: none;
  }
}
</style>
