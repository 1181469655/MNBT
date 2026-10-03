<template>
  <div class="td-page">
    <div class="td-page-head">
      <div>
        <h3 class="td-page-title"><i class="mdi mdi-receipt"></i> Docker 订单</h3>
        <p class="td-page-subtitle">查看 Docker 购买订单与支付状态</p>
      </div>
      <t-button variant="outline" @click="router.push('/docker-shop')">
        <i class="mdi mdi-cart"></i> 继续购买
      </t-button>
    </div>

    <div class="td-card" v-if="orders.length">
      <div class="order-item" v-for="order in orders" :key="order.id">
        <div class="order-icon" :class="'order-icon-' + orderStatus(order.status)">
          <i class="mdi mdi-docker"></i>
        </div>
        <div class="order-main">
          <div class="order-title">
            <strong>{{ order.plan_name }}</strong>
            <span class="order-period">{{ periodLabel(order.period) }}</span>
            <t-tag :theme="statusTheme(order.status)" variant="light" size="small">
              {{ statusText(order.status) }}
            </t-tag>
          </div>
          <div class="order-meta">
            <span>单号 {{ order.order_no || '—' }}</span>
            <span>下单 {{ order.created_at || '—' }}</span>
            <span v-if="order.remark" class="order-remark">备注：{{ order.remark }}</span>
          </div>
        </div>
        <div class="order-amount">¥{{ centsToYuan(order.amount_cents) }}</div>
      </div>

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

    <t-empty v-else description="暂无 Docker 订单" style="padding: 60px 0">
      <t-button theme="primary" @click="router.push('/docker-shop')">去选购套餐</t-button>
    </t-empty>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { getDockerShopOrders, centsToYuan } from '@/account/api/plugins'

const router = useRouter()

const PERIODS = {
  month: '月付',
  quarter: '季付',
  half_year: '半年付',
  year: '年付',
  two_year: '两年付',
  three_year: '三年付',
}

const orders = ref([])
const page = ref(1)
const perPage = 15
const total = ref(0)

function periodLabel(p) {
  return PERIODS[p] || p
}

function orderStatus(status) {
  return { pending: 'pending', paid: 'paid', opened: 'opened', failed: 'failed', cancelled: 'cancelled' }[status] || 'pending'
}

function statusText(status) {
  return { pending: '待支付', paid: '已支付', opened: '已开通', failed: '开通失败', cancelled: '已取消' }[status] || status
}

function statusTheme(status) {
  if (status === 'opened') return 'success'
  if (status === 'pending') return 'warning'
  if (status === 'failed' || status === 'cancelled') return 'default'
  return 'primary'
}

async function load() {
  const res = await getDockerShopOrders(page.value, perPage)
  if (!res.ok) return
  const data = res.data.orders || {}
  orders.value = data.list || []
  total.value = data.total || 0
}

onMounted(load)
</script>

<style scoped>
.order-item {
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 16px 18px;
  border-bottom: 1px solid var(--td-border);
}
.order-item:last-child {
  border-bottom: none;
}
.order-icon {
  width: 40px;
  height: 40px;
  border-radius: 10px;
  display: grid;
  place-items: center;
  font-size: 20px;
  flex-shrink: 0;
}
.order-icon-pending {
  background: #fff3e0;
  color: #e37318;
}
.order-icon-opened {
  background: #e8f8f0;
  color: #2ba471;
}
.order-icon-paid {
  background: #e8f3ff;
  color: #0052d9;
}
.order-icon-failed,
.order-icon-cancelled {
  background: #f0f0f0;
  color: #8c8c8c;
}
.order-main {
  flex: 1;
  min-width: 0;
}
.order-title {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
}
.order-title strong {
  font-size: 14px;
  color: var(--td-text);
  font-weight: 600;
}
.order-period {
  font-size: 12px;
  color: var(--td-text-secondary);
  background: var(--td-bg);
  padding: 1px 8px;
  border-radius: 10px;
}
.order-meta {
  display: flex;
  align-items: center;
  gap: 14px;
  font-size: 12px;
  color: var(--td-text-placeholder);
  margin-top: 4px;
  flex-wrap: wrap;
}
.order-remark {
  color: var(--td-warning);
}
.order-amount {
  font-size: 16px;
  font-weight: 700;
  color: var(--td-text);
  white-space: nowrap;
}
.pager {
  display: flex;
  justify-content: flex-end;
  padding: 16px 18px;
  border-top: 1px solid var(--td-border);
}

@media (max-width: 560px) {
  .order-meta {
    gap: 8px;
  }
}
</style>
