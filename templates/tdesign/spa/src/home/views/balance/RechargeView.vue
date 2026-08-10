<template>
  <div class="hd-page">
    <div class="hd-container" style="max-width:560px;">
      <t-button variant="text" style="margin-bottom:12px;" @click="$router.push('/balance')">
        <i class="mdi mdi-arrow-left"></i> 返回余额页
      </t-button>

      <h1 class="hd-page-title" style="margin-bottom:4px;">余额充值</h1>
      <p class="hd-page-subtitle" style="margin-bottom:18px;">选择支付方式并输入充值金额</p>

      <t-loading :loading="loading" size="large">
        <div v-if="methods.length === 0 && !loading" class="hd-empty">
          <i class="mdi mdi-credit-card-outline"></i>
          <p>暂无可用的支付方式，请联系管理员启用支付插件</p>
        </div>

        <div v-else class="hd-card">
          <div class="hd-card-body" style="padding:24px 28px;">

            <t-alert v-if="errorMsg" theme="error" :message="errorMsg" style="margin-bottom:16px;" close @close="errorMsg=''" />

            <t-form ref="formRef" :data="formData" :rules="rules" label-align="top" @submit="onSubmit">
              <t-form-item label="支付方式" name="type">
                <t-radio-group v-model="formData.type" style="display:flex;flex-wrap:wrap;gap:8px;">
                  <t-radio-button v-for="m in methods" :key="m.plugin + '__' + m.method" :value="m.plugin + '__' + m.method">
                    <i v-if="m.icon" :class="'mdi ' + m.icon" style="margin-right:4px;"></i>
                    {{ m.display_name || (m.plugin + '/' + m.method) }}
                  </t-radio-button>
                </t-radio-group>
              </t-form-item>

              <t-form-item label="充值金额（元）" name="amount">
                <t-input-number
                  v-model="formData.amount"
                  size="large"
                  :min="1"
                  :max="50000"
                  :decimal-places="2"
                  placeholder="最低 1 元"
                  style="width:100%;"
                />
              </t-form-item>

              <div style="display:flex;gap:8px;margin-bottom:20px;flex-wrap:wrap;">
                <t-button v-for="v in quickAmounts" :key="v" size="small" variant="outline" @click="formData.amount = v">{{ v }} 元</t-button>
              </div>

              <t-form-item>
                <t-button size="large" block theme="primary" type="submit" :loading="submitting">立即充值</t-button>
              </t-form-item>
            </t-form>

          </div>
        </div>
      </t-loading>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { MessagePlugin } from 'tdesign-vue-next'
import { getBalanceMethods, createRecharge } from '@/home/api/balance'

const loading = ref(true)
const submitting = ref(false)
const errorMsg = ref('')
const methods = ref([])
const formRef = ref(null)
const quickAmounts = [10, 50, 100, 500]

const formData = reactive({
  type: '',
  amount: 10,
})

const rules = {
  type: [{ required: true, message: '请选择支付方式' }],
  amount: [
    { required: true, message: '请输入充值金额' },
    { validator: (val) => val >= 1, message: '充值金额至少 1 元' },
    { validator: (val) => val <= 50000, message: '单次充值金额不能超过 50000 元' },
  ],
}

onMounted(async () => {
  const res = await getBalanceMethods()
  if (res.ok && res.data?.methods) {
    methods.value = res.data.methods
    if (methods.value.length > 0) {
      formData.type = methods.value[0].plugin + '__' + methods.value[0].method
    }
  }
  loading.value = false
})

async function onSubmit({ validateResult }) {
  if (validateResult !== true) return
  submitting.value = true
  errorMsg.value = ''
  try {
    const res = await createRecharge(formData.amount, formData.type)
    if (res.ok) {
      if (res.data?.html) {
        document.open()
        document.write(res.data.html)
        document.close()
      } else {
        MessagePlugin.success('充值订单已创建')
      }
    } else {
      errorMsg.value = res.message || '创建充值订单失败'
    }
  } finally {
    submitting.value = false
  }
}
</script>
