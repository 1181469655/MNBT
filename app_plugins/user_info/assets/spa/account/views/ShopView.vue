<template>
  <div class="td-page">
    <div class="td-page-head">
      <div>
        <h3 class="td-page-title"><i class="mdi mdi-cart"></i> 主机商城</h3>
        <p class="td-page-subtitle">选择适合你的虚拟主机套餐，支付后自动开通</p>
      </div>
      <t-button variant="outline" @click="router.push('/hosting')">
        <i class="mdi mdi-server"></i> 我的主机
      </t-button>
    </div>

    <div v-if="plans.length" class="plan-grid">
      <div class="plan-card td-card" v-for="plan in plans" :key="plan.id">
        <div class="plan-head">
          <div class="plan-name">{{ plan.name }}</div>
          <span v-if="plan.category" class="plan-category">{{ plan.category }}</span>
        </div>
        <p class="plan-desc">{{ plan.description || '暂无介绍' }}</p>

        <ul class="plan-specs">
          <li><i class="mdi mdi-cloud-outline"></i><span>网页空间</span><b>{{ plan.spec_web }} MB</b></li>
          <li><i class="mdi mdi-database"></i><span>数据库</span><b>{{ plan.spec_sql }} MB</b></li>
          <li><i class="mdi mdi-transfer"></i><span>月流量</span><b>{{ plan.spec_flow > 0 ? plan.spec_flow + ' GB' : '不限' }}</b></li>
          <li><i class="mdi mdi-web"></i><span>绑定域名</span><b>{{ plan.spec_domain }} 个</b></li>
        </ul>

        <div class="plan-foot">
          <div class="plan-price">
            <template v-if="plan.periods.length">
              <span class="price-num">¥{{ centsToYuan(minPrice(plan)) }}</span>
              <span class="price-unit">起</span>
            </template>
            <span v-else class="price-empty">未设置价格</span>
          </div>
          <t-button theme="primary" size="small" :disabled="!plan.periods.length" @click="openOrder(plan)">
            {{ plan.periods.length ? '立即购买' : '暂不可购' }}
          </t-button>
        </div>
      </div>
    </div>

    <t-empty v-else description="暂无可购买的套餐" style="padding: 60px 0" />

    <!-- 下单对话框 -->
    <t-dialog
      v-model:visible="dialogVisible"
      :header="'购买：' + (currentPlan?.name || '')"
      :confirm-btn="{ content: '确认购买', theme: 'primary', loading: submitting }"
      :cancel-btn="'取消'"
      :on-confirm="onConfirmOrder"
    >
      <div class="order-body">
        <div class="order-label">购买周期</div>
        <div class="period-grid">
          <label
            v-for="p in currentPeriods"
            :key="p"
            class="period-item"
            :class="{ active: period === p }"
          >
            <input type="radio" name="period" :value="p" v-model="period" />
            <span class="period-name">{{ periodLabel(p) }}</span>
            <span class="period-price">¥{{ centsToYuan(currentPlan.prices[p]) }}</span>
          </label>
        </div>

        <template v-if="isFreePlan">
          <div class="free-tip"><i class="mdi mdi-check-circle-outline"></i> 免费套餐，下单后立即开通</div>
        </template>
        <template v-else>
          <div class="order-label" style="margin-top: 16px">支付方式</div>
          <div class="period-grid">
            <label
              v-for="m in methods"
              :key="m.plugin + '__' + m.method"
              class="period-item"
              :class="{ active: payType === m.plugin + '__' + m.method }"
            >
              <input type="radio" name="paytype" :value="m.plugin + '__' + m.method" v-model="payType" />
              <span class="period-name">{{ m.display_name || m.plugin + ' / ' + m.method }}</span>
            </label>
          </div>
          <p v-if="!methods.length" class="no-methods">暂无可用的支付方式</p>
        </template>
      </div>
    </t-dialog>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { MessagePlugin } from 'tdesign-vue-next'
import { getShopPlans, getShopMethods, createShopOrder, centsToYuan, goPay } from '@/account/api/plugins'

const router = useRouter()

const PERIODS = {
  month: '月付',
  quarter: '季付',
  half_year: '半年付',
  year: '年付',
  two_year: '两年付',
  three_year: '三年付',
}

const plans = ref([])
const methods = ref([])

// 下单状态
const dialogVisible = ref(false)
const currentPlan = ref(null)
const period = ref('')
const payType = ref('')
const submitting = ref(false)

const currentPeriods = computed(() => currentPlan.value?.periods || [])
const isFreePlan = computed(() => {
  const plan = currentPlan.value
  if (!plan) return false
  return currentPeriods.value.every((p) => !plan.prices[p])
})

function periodLabel(p) {
  return PERIODS[p] || p
}

function minPrice(plan) {
  const prices = (plan.periods || []).map((p) => plan.prices[p] || 0).filter((v) => v > 0)
  return prices.length ? Math.min(...prices) : 0
}

function openOrder(plan) {
  currentPlan.value = plan
  period.value = plan.periods?.[0] || ''
  payType.value = methods.value[0] ? methods.value[0].plugin + '__' + methods.value[0].method : ''
  dialogVisible.value = true
}

async function onConfirmOrder() {
  if (!currentPlan.value) return
  if (!isFreePlan.value && !payType.value) {
    MessagePlugin.warning('请选择支付方式')
    return false
  }
  submitting.value = true
  const res = await createShopOrder(currentPlan.value.id, period.value, payType.value)
  submitting.value = false
  if (!res.ok) {
    MessagePlugin.error(res.message || '创建订单失败')
    return false
  }
  if (!goPay(res)) {
    MessagePlugin.success('订单创建成功')
    dialogVisible.value = false
  }
  return true
}

async function load() {
  const [plansRes, methodsRes] = await Promise.all([getShopPlans(), getShopMethods()])
  if (plansRes.ok) plans.value = plansRes.data.plans || []
  if (methodsRes.ok) methods.value = methodsRes.data.methods || []
}

onMounted(load)
</script>

<style scoped>
.plan-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 16px;
}
.plan-card {
  padding: 20px;
  display: flex;
  flex-direction: column;
  transition: transform var(--td-dur) var(--td-ease),
              box-shadow var(--td-dur) var(--td-ease);
}
.plan-card:hover {
  transform: translateY(-3px);
  box-shadow: var(--td-shadow-md);
}
.plan-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 8px;
}
.plan-name {
  font-size: 16px;
  font-weight: 700;
  color: var(--td-text);
}
.plan-category {
  font-size: 12px;
  color: var(--td-brand);
  background: var(--td-brand-light);
  padding: 2px 8px;
  border-radius: 10px;
  white-space: nowrap;
}
.plan-desc {
  margin: 8px 0 14px;
  font-size: 13px;
  color: var(--td-text-secondary);
  line-height: 1.6;
  min-height: 40px;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
.plan-specs {
  list-style: none;
  margin: 0 0 16px;
  padding: 0;
  flex: 1;
}
.plan-specs li {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 13px;
  color: var(--td-text-secondary);
  padding: 5px 0;
}
.plan-specs li i {
  font-size: 17px;
  color: var(--td-brand);
  width: 20px;
  text-align: center;
}
.plan-specs li span {
  flex: 1;
}
.plan-specs li b {
  color: var(--td-text);
  font-weight: 600;
}
.plan-foot {
  display: flex;
  align-items: center;
  justify-content: space-between;
  border-top: 1px solid var(--td-border);
  padding-top: 14px;
}
.plan-price {
  display: flex;
  align-items: baseline;
  gap: 2px;
}
.price-num {
  font-size: 22px;
  font-weight: 700;
  color: var(--td-error);
}
.price-unit {
  font-size: 12px;
  color: var(--td-text-placeholder);
}
.price-empty {
  font-size: 13px;
  color: var(--td-text-placeholder);
}

/* 下单对话框 */
.order-body {
  padding: 4px 0 0;
}
.order-label {
  font-size: 13px;
  font-weight: 600;
  color: var(--td-text);
  margin-bottom: 10px;
}
.period-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 8px;
}
.period-item {
  display: flex;
  flex-direction: column;
  gap: 2px;
  padding: 10px 12px;
  border: 1px solid var(--td-border);
  border-radius: 10px;
  cursor: pointer;
  font-size: 13px;
  color: var(--td-text);
  transition: border-color var(--td-dur) var(--td-ease),
              background var(--td-dur) var(--td-ease);
}
.period-item input {
  position: absolute;
  opacity: 0;
  pointer-events: none;
}
.period-item.active {
  border-color: var(--td-brand);
  background: var(--td-brand-light);
}
.period-name {
  font-weight: 600;
}
.period-price {
  font-size: 12px;
  color: var(--td-brand);
}
.free-tip {
  margin-top: 14px;
  font-size: 13px;
  color: var(--td-success);
  display: flex;
  align-items: center;
  gap: 6px;
}
.no-methods {
  margin: 12px 0 0;
  font-size: 13px;
  color: var(--td-text-placeholder);
}

@media (max-width: 1080px) {
  .plan-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}
@media (max-width: 680px) {
  .plan-grid {
    grid-template-columns: 1fr;
  }
}
</style>
