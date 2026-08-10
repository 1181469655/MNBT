<template>
  <div class="hd-page">
    <div class="hd-container">
      <div class="hd-page-head">
        <div>
          <h1 class="hd-page-title">主机套餐</h1>
          <p class="hd-page-subtitle">选择适合您的主机套餐，支付后自动开通</p>
        </div>
      </div>

      <t-loading :loading="loading" size="large">
        <div v-if="plans.length === 0 && !loading" class="hd-empty">
          <i class="mdi mdi-package-variant"></i>
          <p>暂无可购买套餐</p>
        </div>
        <div v-else class="hd-plans" style="grid-template-columns: repeat(3, 1fr);">
          <div v-for="plan in plans" :key="plan.id" class="hd-plan-card">
            <div class="hd-plan-top">
              <h3>{{ plan.name }}</h3>
              <div class="hd-plan-desc">{{ plan.description || '适合各类站点快速上线' }}</div>
            </div>
            <div class="hd-plan-body">
              <div class="hd-plan-price">
                <div class="num">{{ formatPrice(plan) }}</div>
                <div class="sub">含基础资源与自动开通</div>
              </div>
              <ul class="hd-plan-feats">
                <li v-if="plan.spec_web"><span class="ok">✓</span>网页空间 {{ plan.spec_web }} MB</li>
                <li v-if="plan.spec_sql"><span class="ok">✓</span>数据库 {{ plan.spec_sql }} MB</li>
                <li v-if="plan.spec_flow"><span class="ok">✓</span>月流量 {{ plan.spec_flow }} GB</li>
                <li v-if="plan.spec_domain"><span class="ok">✓</span>可绑定 {{ plan.spec_domain }} 个域名</li>
              </ul>
              <t-button block theme="primary" @click="$router.push('/shop/order/' + plan.id)">立即购买</t-button>
            </div>
          </div>
        </div>
      </t-loading>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { getPlans } from '@/home/api/shop'
import { periodLabels } from '@/home/utils/format'

const loading = ref(true)
const plans = ref([])

function formatPrice(plan) {
  const minPrice = getMinPriceCents(plan)
  return minPrice > 0 ? '¥' + (minPrice / 100).toFixed(2) + ' 起/月' : '免费'
}

function getMinPriceCents(plan) {
  if (!plan.prices) return 0
  let min = 0
  for (const cents of Object.values(plan.prices)) {
    if (cents > 0 && (min === 0 || cents < min)) min = cents
  }
  return min
}

onMounted(async () => {
  const res = await getPlans()
  if (res.ok && res.data?.plans) {
    plans.value = res.data.plans
  }
  loading.value = false
})
</script>
