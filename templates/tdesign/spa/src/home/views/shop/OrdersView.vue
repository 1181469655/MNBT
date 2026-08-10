<template>
  <div class="hd-page">
    <div class="hd-container">
      <div class="hd-page-head">
        <div>
          <h1 class="hd-page-title">我的订单</h1>
          <p class="hd-page-subtitle">查看购买记录和状态</p>
        </div>
      </div>

      <t-loading :loading="loading" size="large">
        <div v-if="orders.length === 0 && !loading" class="hd-empty">
          <i class="mdi mdi-receipt"></i>
          <p>暂无订单</p>
        </div>

        <template v-else>
          <div class="hd-card">
            <t-table
              :data="orders"
              :columns="columns"
              row-key="id"
              hover
              size="medium"
            />
          </div>
          <div style="display:flex;justify-content:center;margin-top:16px;">
            <t-pagination
              v-if="total > 0"
              :current="page"
              :total="total"
              :page-size="perPage"
              @change="onPageChange"
            />
          </div>
        </template>
      </t-loading>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, h } from 'vue'
import { useRouter } from 'vue-router'
import { getOrders } from '@/home/api/shop'
import { centsToYuan, orderStatusMap } from '@/home/utils/format'

const router = useRouter()
const loading = ref(true)
const orders = ref([])
const total = ref(0)
const page = ref(1)
const perPage = ref(15)

const columns = [
  { colKey: 'order_no', title: '订单号', width: 200, ellipsis: true },
  { colKey: 'plan_name', title: '套餐', ellipsis: true },
  { colKey: 'period', title: '周期', width: 100, cell: (_h, { row }) => {
    const map = { month: '月付', quarter: '季付', half_year: '半年付', year: '年付', two_year: '两年付', three_year: '三年付' }
    return map[row.period] || row.period || '-'
  } },
  { colKey: 'amount_cents', title: '金额', width: 100, cell: (_h, { row }) => '¥' + centsToYuan(row.amount_cents || 0) },
  { colKey: 'status', title: '状态', width: 90, cell: (_h, { row }) => {
    const s = orderStatusMap[row.status] || { label: row.status, theme: 'default' }
    return h('t-tag', { theme: s.theme, size: 'small' }, () => s.label)
  } },
  { colKey: 'created_at', title: '时间', width: 160 },
]

async function fetchOrders() {
  loading.value = true
  const res = await getOrders(page.value, perPage.value)
  if (res.ok && res.data?.orders) {
    orders.value = res.data.orders.list || []
    total.value = res.data.orders.total || 0
  }
  loading.value = false
}

function onPageChange(p) {
  page.value = p
  fetchOrders()
}

onMounted(fetchOrders)
</script>
