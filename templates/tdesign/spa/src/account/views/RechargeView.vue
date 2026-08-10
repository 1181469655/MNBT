<template>
  <div class="td-page td-form-narrow">
    <div class="td-card">
      <div class="td-card-head">余额充值</div>
      <div class="td-card-body">
        <p class="recharge-tip">选择支付方式并输入充值金额，金额到账后可用于购买主机等服务。</p>

        <div v-if="methods.length">
          <div class="form-label">支付方式</div>
          <div class="pay-grid">
            <label
              v-for="m in methods"
              :key="m.plugin + '__' + m.method"
              class="pay-item"
              :class="{ active: type === m.plugin + '__' + m.method }"
            >
              <input
                type="radio"
                name="paytype"
                :value="m.plugin + '__' + m.method"
                v-model="type"
              />
              <i class="mdi mdi-credit-card-outline"></i>
              <span>{{ m.display_name || m.plugin + ' / ' + m.method }}</span>
              <i class="mdi mdi-check pay-check" v-if="type === m.plugin + '__' + m.method"></i>
            </label>
          </div>

          <div class="form-label">充值金额</div>
          <t-input-number
            v-model="amount"
            :min="1"
            :max="50000"
            :step="10"
            :precision="2"
            theme="column"
            size="large"
            placeholder="最低 1 元"
            style="width: 100%"
          >
            <template #prefixIcon><i class="mdi mdi-currency-cny"></i></template>
          </t-input-number>

          <div class="quick-btns">
            <t-button v-for="v in quickAmounts" :key="v" variant="outline" size="small" @click="amount = v">
              {{ v }} 元
            </t-button>
          </div>

          <t-button theme="primary" size="large" block style="margin-top: 18px" :loading="loading" @click="onSubmit">
            立即充值
          </t-button>
        </div>

        <t-empty v-else description="暂无可用的支付方式，请联系管理员启用支付插件" style="padding: 40px 0" />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { MessagePlugin } from 'tdesign-vue-next'
import { getBalanceMethods, createRecharge, goPay } from '@/account/api/plugins'

const methods = ref([])
const type = ref('')
const amount = ref(50)
const loading = ref(false)
const quickAmounts = [10, 50, 100, 500]

async function loadMethods() {
  const res = await getBalanceMethods()
  if (!res.ok) return
  methods.value = res.data.methods || []
  if (methods.value.length) {
    type.value = methods.value[0].plugin + '__' + methods.value[0].method
  }
}

async function onSubmit() {
  if (!type.value) {
    MessagePlugin.warning('请选择支付方式')
    return
  }
  if (!amount.value || amount.value < 1) {
    MessagePlugin.warning('充值金额至少 1 元')
    return
  }
  loading.value = true
  const res = await createRecharge(amount.value, type.value)
  loading.value = false
  if (!res.ok) {
    MessagePlugin.error(res.message || '创建订单失败')
    return
  }
  if (!goPay(res)) {
    MessagePlugin.success('充值订单已创建')
  }
}

onMounted(loadMethods)
</script>

<style scoped>
.recharge-tip {
  margin: 0 0 18px;
  font-size: 13px;
  color: var(--td-text-secondary);
}
.form-label {
  font-size: 13px;
  font-weight: 600;
  color: var(--td-text);
  margin: 0 0 10px;
}
.pay-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 10px;
  margin-bottom: 18px;
}
.pay-item {
  position: relative;
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 12px 14px;
  border: 1px solid var(--td-border);
  border-radius: 10px;
  cursor: pointer;
  font-size: 13px;
  color: var(--td-text);
  transition: border-color var(--td-dur) var(--td-ease),
              background var(--td-dur) var(--td-ease),
              box-shadow var(--td-dur) var(--td-ease);
}
.pay-item input {
  position: absolute;
  opacity: 0;
  pointer-events: none;
}
.pay-item i:first-child {
  font-size: 20px;
  color: var(--td-text-placeholder);
}
.pay-item:hover {
  border-color: var(--td-brand);
}
.pay-item.active {
  border-color: var(--td-brand);
  background: var(--td-brand-light);
  color: var(--td-brand);
  box-shadow: 0 0 0 1px var(--td-brand) inset;
}
.pay-item.active i:first-child {
  color: var(--td-brand);
}
.pay-check {
  margin-left: auto;
  font-size: 16px;
}
.quick-btns {
  display: flex;
  gap: 8px;
  margin-top: 10px;
  flex-wrap: wrap;
}

@media (max-width: 480px) {
  .pay-grid {
    grid-template-columns: 1fr;
  }
}
</style>
