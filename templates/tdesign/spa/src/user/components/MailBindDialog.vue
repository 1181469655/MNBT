<template>
  <t-dialog
    v-model:visible="innerVisible"
    header="邮箱绑定"
    :close-on-click-overlay="false"
    :close-on-esc-keydown="!required"
    :close-btn="!required"
    :confirm-btn="{ content: '提交绑定', loading: saving }"
    :cancel-btn="required ? null : '取消'"
    width="460px"
    @confirm="onSubmit"
  >
    <div class="td-mail-bind">
      <div class="td-mail-bind-tip">
        <i class="mdi mdi-email-alert-outline"></i>
        <div>
          <strong>{{ tipTitle }}</strong>
          <p>{{ tipDesc }}</p>
        </div>
      </div>
      <t-form ref="formRef" :data="form" :rules="rules" label-width="0" @submit="onSubmit">
        <t-form-item name="mail">
          <t-input
            v-model="form.mail"
            placeholder="请输入您的邮箱"
            clearable
            size="large"
            @enter="onSubmit"
          >
            <template #prefix-icon>
              <i class="mdi mdi-email-outline"></i>
            </template>
          </t-input>
        </t-form-item>
      </t-form>
    </div>
  </t-dialog>
</template>

<script setup>
import { ref, reactive, computed, watch } from 'vue'
import { MessagePlugin } from 'tdesign-vue-next'
import { bindEmail } from '@/user/api/site'

const props = defineProps({
  visible: { type: Boolean, default: false },
  /** 是否为强制绑定（邮箱为空时自动弹出且不允许关闭） */
  required: { type: Boolean, default: false },
  /** 标题副文案自定义 */
  tipTitle: { type: String, default: '绑定您的邮箱' },
  tipDesc: {
    type: String,
    default: '绑定邮箱后可接收监控通知与找回密码',
  },
})

const emit = defineEmits(['update:visible', 'success'])

const innerVisible = computed({
  get: () => props.visible,
  set: v => emit('update:visible', v),
})

const formRef = ref(null)
const saving = ref(false)
const form = reactive({ mail: '' })

const rules = {
  mail: [
    { required: true, message: '请输入邮箱', trigger: 'blur' },
    {
      pattern: /^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/,
      message: '邮箱格式不正确',
      trigger: 'blur',
    },
  ],
}

watch(
  () => props.visible,
  v => {
    if (v) form.mail = ''
  },
)

async function onSubmit(ctx) {
  if (ctx && ctx.e) ctx.e.preventDefault()
  const valid = await formRef.value?.validate()
  if (valid !== true) return
  saving.value = true
  const r = await bindEmail(form.mail.trim())
  saving.value = false
  if (r.ok) {
    MessagePlugin.success('邮箱绑定成功')
    emit('success', form.mail.trim())
    innerVisible.value = false
  }
}
</script>

<style scoped>
.td-mail-bind-tip {
  display: flex;
  gap: 10px;
  padding: 12px 14px;
  margin-bottom: 14px;
  background: #fff9e6;
  border: 1px solid #ffe69b;
  border-radius: 8px;
  color: #8a6d3b;
}
.td-mail-bind-tip > i {
  font-size: 22px;
  color: #e37318;
  flex-shrink: 0;
  margin-top: 2px;
}
.td-mail-bind-tip strong {
  display: block;
  font-size: 13px;
  color: #665028;
  margin-bottom: 2px;
}
.td-mail-bind-tip p {
  margin: 0;
  font-size: 12px;
  color: #8a6d3b;
  line-height: 1.5;
}
</style>
