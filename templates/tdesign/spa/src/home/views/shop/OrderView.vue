<template>
  <div class="hd-page">
    <div class="hd-container" style="max-width:720px;">
      <t-button variant="text" style="margin-bottom:12px;" @click="$router.push('/shop')">
        <i class="mdi mdi-arrow-left"></i> 返回套餐列表
      </t-button>

      <t-loading :loading="loading" size="large">
        <div v-if="plan">
          <h1 class="hd-page-title" style="margin-bottom:4px;">购买：{{ plan.name }}</h1>
          <p class="hd-page-subtitle" style="margin-bottom:18px;">选择周期和支付方式</p>

          <div class="hd-card" style="margin-bottom:16px;">
            <div class="hd-card-body" style="padding:20px 24px;">
              <h4 style="margin:0 0 12px;font-size:14px;color:var(--hd-text-2);">套餐规格</h4>
              <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;">
                <div style="text-align:center;padding:10px;background:var(--hd-bg);border-radius:8px;">
                  <div style="font-size:12px;color:var(--hd-text-3);">网页空间</div>
                  <div style="font-weight:700;font-size:15px;">{{ plan.spec_web }} MB</div>
                </div>
                <div style="text-align:center;padding:10px;background:var(--hd-bg);border-radius:8px;">
                  <div style="font-size:12px;color:var(--hd-text-3);">数据库</div>
                  <div style="font-weight:700;font-size:15px;">{{ plan.spec_sql }} MB</div>
                </div>
                <div style="text-align:center;padding:10px;background:var(--hd-bg);border-radius:8px;">
                  <div style="font-size:12px;color:var(--hd-text-3);">流量</div>
                  <div style="font-weight:700;font-size:15px;">{{ plan.spec_flow > 0 ? plan.spec_flow + ' GB' : '不限' }}</div>
                </div>
                <div style="text-align:center;padding:10px;background:var(--hd-bg);border-radius:8px;">
                  <div style="font-size:12px;color:var(--hd-text-3);">域名</div>
                  <div style="font-weight:700;font-size:15px;">{{ plan.spec_domain }} 个</div>
                </div>
              </div>
            </div>
          </div>

          <div class="hd-card">
            <div class="hd-card-body" style="padding:24px;">

              <t-alert v-if="errorMsg" theme="error" :message="errorMsg" style="margin-bottom:16px;" close @close="errorMsg=''" />

              <t-form ref="formRef" :data="formData" :rules="rules" label-align="top" @submit="onSubmit">
                <t-form-item label="购买周期" name="period">
                  <t-radio-group v-model="formData.period" style="display:flex;flex-wrap:wrap;gap:8px;">
                    <t-radio-button v-for="p in plan.periods" :key="p" :value="p">
                      {{ periodLabels[p] || p }}
                      <span style="margin-left:4px;font-weight:600;">
                        ¥{{ centsToYuan(plan.prices[p] || 0) }}
                      </span>
                    </t-radio-button>
                  </t-radio-group>
                </t-form-item>

                <t-form-item v-if="!isFreeSelected" label="支付方式" name="type">
                  <t-radio-group v-if="methods.length" v-model="formData.type" style="display:flex;flex-wrap:wrap;gap:8px;">
                    <t-radio-button v-for="m in methods" :key="m.plugin + '__' + m.method" :value="m.plugin + '__' + m.method">
                      <i v-if="m.icon" :class="'mdi ' + m.icon" style="margin-right:4px;"></i>
                      {{ m.display_name || (m.plugin + '/' + m.method) }}
                    </t-radio-button>
                  </t-radio-group>
                  <div v-else style="color:var(--hd-text-3);font-size:13px;">暂无可用的支付方式</div>
                </t-form-item>

                <t-form-item v-if="isFreeSelected">
                  <t-tag theme="success" size="medium">免费套餐，无需支付</t-tag>
                </t-form-item>

                <t-form-item>
                  <t-button size="large" theme="primary" type="submit" :loading="submitting" :disabled="!canSubmit">
                    {{ isFreeSelected ? '立即开通' : '确认购买' }}
                  </t-button>
                </t-form-item>
              </t-form>

            </div>
          </div>
        </div>
      </t-loading>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { MessagePlugin } from 'tdesign-vue-next'
import { getPlan, createOrder } from '@/home/api/shop'
import { centsToYuan, periodLabels } from '@/home/utils/format'

const route = useRoute()
const router = useRouter()
const planId = computed(() => parseInt(route.params.planId) || 0)
const loading = ref(true)
const submitting = ref(false)
const errorMsg = ref('')
const plan = ref(null)
const methods = ref([])
const formRef = ref(null)

const formData = reactive({
  period: '',
  type: '',
})

const rules = {
  period: [{ required: true, message: '请选择购买周期' }],
  type: [
    {
      validator: () => {
        if (isFreeSelected.value) return true
        return formData.type !== ''
      },
      message: '请选择支付方式',
    },
  ],
}

const isFreeSelected = computed(() => {
  if (!plan.value || !formData.period) return false
  const price = (plan.value.prices || {})[formData.period] || 0
  return price === 0
})

const canSubmit = computed(() => {
  if (!formData.period) return false
  if (isFreeSelected.value) return true
  return formData.type !== '' && methods.value.length > 0
})

onMounted(async () => {
  if (!planId.value) return
  const res = await getPlan(planId.value)
  if (res.ok && res.data?.plan) {
    plan.value = res.data.plan
    methods.value = res.data.methods || []
    if (plan.value.periods && plan.value.periods.length > 0) {
      formData.period = plan.value.periods[0]
    }
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
    const res = await createOrder(planId.value, formData.period, formData.type)
    if (res.ok) {
      // 支付跳转：有 html 则填页面跳转，有 redirect 则跳页面
      if (res.data?.html) {
        document.open()
        document.write(res.data.html)
        document.close()
      } else if (res.data?.redirect) {
        router.push(res.data.redirect)
      } else {
        MessagePlugin.success('操作成功')
        router.push('/shop/assets')
      }
    } else {
      errorMsg.value = res.message || '操作失败'
    }
  } finally {
    submitting.value = false
  }
}
</script>
