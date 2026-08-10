<template>
  <div class="hd-page">
    <div class="hd-container" style="max-width:720px;">
      <div class="hd-page-head">
        <div>
          <h1 class="hd-page-title">我的余额</h1>
          <p class="hd-page-subtitle">查看余额和流水记录</p>
        </div>
        <t-button theme="primary" @click="$router.push('/balance/recharge')">
          <i class="mdi mdi-plus"></i> 充值
        </t-button>
      </div>

      <t-loading :loading="loading" size="large">
        <div class="hd-balance-card" style="margin-bottom:20px;">
          <div>
            <div class="label">账户余额</div>
            <div class="value">¥{{ balanceYuan }}</div>
          </div>
          <div class="hd-balance-actions">
            <t-button theme="default" variant="outline" @click="$router.push('/balance/recharge')">
              <i class="mdi mdi-plus"></i> 充值
            </t-button>
          </div>
        </div>

        <div class="hd-card">
          <div class="hd-card-body" style="padding:0;">
            <t-table
              :data="logs"
              :columns="columns"
              row-key="id"
              hover
              size="medium"
            />
          </div>
        </div>

        <div v-if="logs.length === 0 && !loading" class="hd-empty" style="margin-top:16px;">
          <i class="mdi mdi-history"></i>
          <p>暂无流水记录</p>
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
      </t-loading>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, h } from 'vue'
import { getBalanceInfo } from '@/home/api/balance'
import { centsToYuan, logTypeMap } from '@/home/utils/format'

const loading = ref(true)
const balanceYuan = ref('0.00')
const logs = ref([])
const total = ref(0)
const page = ref(1)
const perPage = ref(15)

const columns = [
  { colKey: 'type', title: '类型', width: 80, cell: (_h, { row }) => {
    const s = logTypeMap[row.type] || { label: row.type, theme: 'default' }
    return h('t-tag', { theme: s.theme, size: 'small' }, () => s.label)
  } },
  { colKey: 'amount', title: '金额', width: 110, cell: (_h, { row }) => {
    const sign = (row.amount || 0) > 0 ? '+' : ''
    return sign + '¥' + centsToYuan(row.amount || 0)
  } },
  { colKey: 'balance_after', title: '余额', width: 110, cell: (_h, { row }) => '¥' + centsToYuan(row.balance_after || 0) },
  { colKey: 'remark', title: '备注', ellipsis: true, cell: (_h, { row }) => row.remark || '-' },
  { colKey: 'created_at', title: '时间', width: 160 },
]

async function fetchData() {
  loading.value = true
  const res = await getBalanceInfo(page.value, perPage.value)
  if (res.ok && res.data) {
    balanceYuan.value = res.data.balance_yuan || '0.00'
    if (res.data.logs) {
      logs.value = res.data.logs.list || []
      total.value = res.data.logs.total || 0
    }
  }
  loading.value = false
}

function onPageChange(p) {
  page.value = p
  fetchData()
}

onMounted(fetchData)
</script>
