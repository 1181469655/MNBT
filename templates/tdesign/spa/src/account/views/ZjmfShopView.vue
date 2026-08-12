<template>
  <div class="td-page">
    <div class="td-page-head">
      <div>
        <h3 class="td-page-title"><i class="mdi mdi-cloud-outline"></i> 云服务器选购</h3>
        <p class="td-page-subtitle">魔方财务代理分销，即买即用，支付完成自动开通</p>
      </div>
      <t-button variant="outline" @click="router.push('/zjmf-assets')">
        <i class="mdi mdi-server"></i> 我的主机
      </t-button>
    </div>

    <div v-if="products.length" class="zjmf-grid">
      <div class="zjmf-card td-card" v-for="p in products" :key="p.id">
        <div class="zjmf-head">
          <div class="zjmf-name">{{ p.name }}</div>
        </div>
        <div class="zjmf-desc" v-if="p.description" v-html="p.description"></div>
        <p v-else class="zjmf-desc zjmf-desc-empty">暂无介绍</p>

        <div class="zjmf-foot">
          <div class="zjmf-price">
            <template v-if="p.cycles.length">
              <span class="price-num">¥{{ centsToYuan(minPrice(p)) }}</span>
              <span class="price-unit">起</span>
            </template>
            <span v-else class="price-empty">未设置价格</span>
          </div>
          <t-button theme="primary" size="small" :disabled="!p.cycles.length" @click="openOrder(p)">
            {{ p.cycles.length ? '立即购买' : '暂不可购' }}
          </t-button>
        </div>
      </div>
    </div>

    <t-empty v-else description="暂无可购买的云服务器" style="padding: 60px 0" />

    <!-- 下单对话框 -->
    <t-dialog
      v-model:visible="dialogVisible"
      :header="'购买：' + (currentProduct?.name || '')"
      :confirm-btn="{ content: '确认购买', theme: 'primary', loading: submitting }"
      :cancel-btn="'取消'"
      :on-confirm="onConfirmOrder"
    >
      <div class="order-body">
        <div class="order-label">购买周期</div>
        <div class="period-grid">
          <label
            v-for="c in currentCycles"
            :key="c.key"
            class="period-item"
            :class="{ active: cycle === c.key }"
          >
            <input type="radio" name="cycle" :value="c.key" v-model="cycle" />
            <span class="period-name">{{ c.name }}</span>
            <span class="period-price">¥{{ centsToYuan(c.price_cents) }}</span>
          </label>
        </div>

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
      </div>
    </t-dialog>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { MessagePlugin } from 'tdesign-vue-next'
import {
  getZjmfProducts, getZjmfMethods, createZjmfOrder, centsToYuan, goPay,
} from '@/account/api/plugins'

const router = useRouter()

const products = ref([])
const methods = ref([])

// 下单状态
const dialogVisible = ref(false)
const currentProduct = ref(null)
const cycle = ref('')
const payType = ref('')
const submitting = ref(false)

const currentCycles = computed(() => currentProduct.value?.cycles || [])

function minPrice(p) {
  const prices = (p.cycles || []).map((c) => c.price_cents || 0).filter((v) => v > 0)
  return prices.length ? Math.min(...prices) : 0
}

function openOrder(p) {
  currentProduct.value = p
  cycle.value = p.cycles?.[0]?.key || ''
  payType.value = methods.value[0] ? methods.value[0].plugin + '__' + methods.value[0].method : ''
  dialogVisible.value = true
}

async function onConfirmOrder() {
  if (!currentProduct.value) return
  if (!payType.value) {
    MessagePlugin.warning('请选择支付方式')
    return false
  }
  submitting.value = true
  const res = await createZjmfOrder(currentProduct.value.id, cycle.value, payType.value)
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
  const [productsRes, methodsRes] = await Promise.all([getZjmfProducts(), getZjmfMethods()])
  if (productsRes.ok) products.value = productsRes.data.list || []
  if (methodsRes.ok) methods.value = methodsRes.data.methods || []
}

onMounted(load)
</script>

<style scoped>
.zjmf-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 16px;
}
.zjmf-card {
  padding: 20px;
  display: flex;
  flex-direction: column;
  transition: transform var(--td-dur) var(--td-ease),
              box-shadow var(--td-dur) var(--td-ease);
}
.zjmf-card:hover {
  transform: translateY(-3px);
  box-shadow: var(--td-shadow-md);
}
.zjmf-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 8px;
}
.zjmf-name {
  font-size: 16px;
  font-weight: 700;
  color: var(--td-text);
}
.zjmf-desc {
  margin: 8px 0 16px;
  font-size: 13px;
  color: var(--td-text-secondary);
  line-height: 1.6;
  flex: 1;
}
/* 简介 li 列表：每项独占一行、带圆点 */
.zjmf-desc ul {
  margin: 0;
  padding-left: 16px;
}
.zjmf-desc li {
  display: list-item;
  list-style: disc;
  margin: 2px 0;
}
.zjmf-desc p {
  margin: 2px 0;
}
.zjmf-desc-empty {
  min-height: auto;
}
.zjmf-foot {
  display: flex;
  align-items: center;
  justify-content: space-between;
  border-top: 1px solid var(--td-border);
  padding-top: 14px;
}
.zjmf-price {
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
.no-methods {
  margin: 12px 0 0;
  font-size: 13px;
  color: var(--td-text-placeholder);
}

@media (max-width: 1080px) {
  .zjmf-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}
@media (max-width: 680px) {
  .zjmf-grid {
    grid-template-columns: 1fr;
  }
}
</style>
