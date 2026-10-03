<template>
  <div class="hd-page">
    <div class="hd-container">
      <div class="hd-page-head">
        <div>
          <h1 class="hd-page-title">Docker 套餐</h1>
          <p class="hd-page-subtitle">选择适合您的 Docker 容器套餐，支付后自动开通</p>
        </div>
      </div>

      <t-loading :loading="loading" size="large">
        <div v-if="plans.length === 0 && !loading" class="hd-empty">
          <i class="mdi mdi-docker"></i>
          <p>暂无可购买的 Docker 套餐</p>
        </div>
        <div v-else class="hd-plans" style="grid-template-columns: repeat(3, 1fr);">
          <div v-for="plan in plans" :key="plan.id" class="hd-plan-card">
            <div class="hd-plan-top">
              <h3>{{ plan.name }}</h3>
              <div class="hd-plan-desc">{{ plan.description || 'Docker 容器服务，即买即用' }}</div>
            </div>
            <div class="hd-plan-body">
              <div class="hd-plan-price">
                <div class="num">{{ formatPrice(plan) }}</div>
                <div class="sub">{{ plan.node?.name ? '开通节点：' + plan.node.name : '自动分配节点' }}</div>
              </div>
              <ul class="hd-plan-feats">
                <li><span class="ok">✓</span>CPU {{ plan.base_plan ? cpuText(plan.base_plan) : '—' }}</li>
                <li><span class="ok">✓</span>内存 {{ plan.base_plan ? memText(plan.base_plan) : '—' }}</li>
                <li><span class="ok">✓</span>磁盘 {{ plan.base_plan ? diskText(plan.base_plan) : '—' }}</li>
                <li><span class="ok">✓</span>代理 {{ plan.base_plan ? proxyText(plan.base_plan) : '—' }}</li>
              </ul>
              <t-button block theme="primary" @click="goBuy">立即购买</t-button>
            </div>
          </div>
        </div>
      </t-loading>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { getDockerPlans } from '@/home/api/docker'
import { accountUrl } from '@/home/utils/account'

const loading = ref(true)
const plans = ref([])

function formatPrice(plan) {
  const minPrice = getMinPriceCents(plan)
  return minPrice > 0 ? '¥' + (minPrice / 100).toFixed(2) + ' 起' : '免费'
}

function getMinPriceCents(plan) {
  if (!plan.prices) return 0
  let min = 0
  for (const cents of Object.values(plan.prices)) {
    if (cents > 0 && (min === 0 || cents < min)) min = cents
  }
  return min
}

function cpuText(b) {
  const v = Number(b.cpu_max)
  return v > 0 ? v + ' 核' : '—'
}

function memText(b) {
  const mb = Number(b.mem_max)
  if (!mb) return '—'
  return mb >= 1024 ? mb / 1024 + ' GB' : mb + ' MB'
}

function diskText(b) {
  const mb = Number(b.disk_max)
  if (!mb) return '—'
  return mb >= 1024 ? mb / 1024 + ' GB' : mb + ' MB'
}

function proxyText(b) {
  const v = Number(b.proxy_max)
  return v > 0 ? v + ' 个' : '不限'
}

onMounted(async () => {
  const res = await getDockerPlans()
  if (res.ok && res.data?.plans) {
    plans.value = res.data.plans
  }
  loading.value = false
})

// 购买统一跳转 account SPA 的 Docker 商城
function goBuy() {
  window.location.href = accountUrl('docker-shop')
}
</script>
