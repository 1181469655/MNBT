<template>
  <div class="site-contact">
    <section class="site-page-header" :style="{ backgroundImage: `url(${headerImage})` }">
      <div class="hd-container">
        <h1>联系我们</h1>
        <p>我们随时准备为您提供帮助与支持</p>
      </div>
    </section>

    <section class="site-sec">
      <div class="hd-container">
        <div class="site-contact-layout">
          <div class="site-contact-info">
            <h2>联系信息</h2>
            <div class="site-contact-details">
              <div class="site-contact-item">
                <div class="site-contact-icon"><i class="mdi mdi-map-marker-outline"></i></div>
                <div class="site-contact-text">
                  <h3>公司地址</h3>
                  <p>北京市朝阳区 · 数据中心园区</p>
                </div>
              </div>
              <div class="site-contact-item">
                <div class="site-contact-icon"><i class="mdi mdi-email-outline"></i></div>
                <div class="site-contact-text">
                  <h3>服务邮箱</h3>
                  <p>support@mnbt.example</p>
                </div>
              </div>
              <div class="site-contact-item">
                <div class="site-contact-icon"><i class="mdi mdi-headset"></i></div>
                <div class="site-contact-text">
                  <h3>客服支持</h3>
                  <p>工作日 9:00 - 21:00 · 7×24 工单系统</p>
                </div>
              </div>
            </div>
            <div class="site-contact-map">
              <div class="site-ph"><i class="mdi mdi-map-outline"></i> 数据中心分布：华东 / 华北 / 华南</div>
            </div>
          </div>

          <div class="site-contact-form">
            <h2>发送消息</h2>
            <form @submit.prevent="submitForm">
              <div class="site-form-group">
                <label>姓名</label>
                <t-input v-model="form.name" placeholder="请输入您的姓名" :status="errors.name ? 'error' : undefined" />
                <span v-if="errors.name" class="site-form-error">{{ errors.name }}</span>
              </div>
              <div class="site-form-group">
                <label>邮箱</label>
                <t-input v-model="form.email" placeholder="请输入联系邮箱" :status="errors.email ? 'error' : undefined" />
                <span v-if="errors.email" class="site-form-error">{{ errors.email }}</span>
              </div>
              <div class="site-form-group">
                <label>电话（选填）</label>
                <t-input v-model="form.phone" placeholder="请输入联系电话" :status="errors.phone ? 'error' : undefined" />
                <span v-if="errors.phone" class="site-form-error">{{ errors.phone }}</span>
              </div>
              <div class="site-form-group">
                <label>留言内容</label>
                <t-textarea v-model="form.message" placeholder="请描述您的需求或问题" :rows="5" :status="errors.message ? 'error' : undefined" />
                <span v-if="errors.message" class="site-form-error">{{ errors.message }}</span>
              </div>
              <t-button block theme="primary" size="large" type="submit" :loading="submitting" style="height:44px">
                {{ submitting ? '发送中...' : '发送消息' }}
              </t-button>
              <div v-if="submitStatus" class="site-submit-status" :class="submitStatus.type">
                <i :class="submitStatus.type === 'success' ? 'mdi mdi-check-circle-outline' : 'mdi mdi-alert-circle-outline'"></i>
                {{ submitStatus.message }}
              </div>
            </form>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { MessagePlugin } from 'tdesign-vue-next'
import bg3 from '@/shared/assets/bg3.jpg'
import { submitSiteContact } from '@/home/api/site'

const headerImage = bg3

const form = reactive({ name: '', email: '', phone: '', message: '' })
const errors = reactive({})
const submitting = ref(false)
const submitStatus = ref(null)

function validate() {
  errors.name = ''
  errors.email = ''
  errors.phone = ''
  errors.message = ''
  if (!form.name.trim()) errors.name = '请输入姓名'
  if (!form.email.trim()) {
    errors.email = '请输入邮箱'
  } else if (!/\S+@\S+\.\S+/.test(form.email)) {
    errors.email = '邮箱格式不正确'
  }
  if (!form.message.trim()) errors.message = '请输入留言内容'
  if (form.phone && !/^(\+?86)?1[3-9]\d{9}$/.test(form.phone)) {
    errors.phone = '请输入有效的电话号码'
  }
  return !errors.name && !errors.email && !errors.phone && !errors.message
}

async function submitForm() {
  if (!validate()) return
  submitting.value = true
  submitStatus.value = null
  const res = await submitSiteContact({
    name: form.name,
    email: form.email,
    phone: form.phone,
    message: form.message,
  })
  submitting.value = false
  if (res.ok) {
    submitStatus.value = { type: 'success', message: '消息已发送！我们会尽快与您联系。' }
    form.name = ''
    form.email = ''
    form.phone = ''
    form.message = ''
  } else {
    submitStatus.value = { type: 'error', message: res.message || '发送失败，请稍后重试' }
  }
}
</script>

<style scoped>
.site-contact {
  background: var(--hd-bg);
}

.site-page-header {
  background-size: cover;
  background-position: center;
  padding: 72px 0;
  text-align: center;
}

.site-page-header h1 {
  margin: 0 0 10px;
  font-size: 2.2rem;
  font-weight: 800;
  letter-spacing: -0.02em;
  color: #181818;
  text-shadow: 0 0 10px rgba(255, 255, 255, 0.75);
}

.site-page-header p {
  margin: 0;
  font-size: 1.05rem;
  color: #444;
  text-shadow: 0 0 10px rgba(255, 255, 255, 0.75);
}

.site-sec {
  padding: 56px 0;
}

.site-contact-layout {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 40px;
  align-items: start;
}

.site-contact-info h2,
.site-contact-form h2 {
  position: relative;
  margin: 0 0 24px;
  font-size: 1.4rem;
  font-weight: 800;
  color: var(--hd-text);
  padding-bottom: 12px;
}

.site-contact-info h2::after,
.site-contact-form h2::after {
  content: '';
  position: absolute;
  bottom: 0;
  left: 0;
  width: 40px;
  height: 3px;
  border-radius: 3px;
  background: var(--hd-brand);
}

.site-contact-details {
  display: grid;
  gap: 18px;
  margin-bottom: 24px;
}

.site-contact-item {
  display: flex;
  gap: 14px;
  align-items: flex-start;
}

.site-contact-icon {
  width: 46px;
  height: 46px;
  flex-shrink: 0;
  display: grid;
  place-items: center;
  border-radius: 14px;
  background: var(--hd-brand-light);
  color: var(--hd-brand);
  font-size: 22px;
}

.site-contact-text h3 {
  margin: 0 0 4px;
  font-size: 14px;
  font-weight: 600;
  color: var(--hd-text);
}

.site-contact-text p {
  margin: 0;
  color: var(--hd-text-3);
  font-size: 13px;
  line-height: 1.6;
}

.site-contact-map .site-ph {
  height: 160px;
  display: grid;
  place-items: center;
  gap: 8px;
  border-radius: var(--hd-radius-xl);
  border: 1px solid var(--hd-border);
  background: linear-gradient(135deg, var(--hd-brand-light), #f4fbf7);
  color: var(--hd-brand);
  font-size: 14px;
  font-weight: 600;
  text-align: center;
  padding: 16px;
}

.site-contact-map .site-ph i {
  font-size: 34px;
  display: block;
  margin-bottom: 6px;
}

.site-contact-form {
  background: var(--hd-surface);
  border: 1px solid var(--hd-border);
  border-radius: var(--hd-radius-xl);
  padding: 26px 28px;
  box-shadow: var(--hd-shadow);
}

.site-form-group {
  margin-bottom: 16px;
}

.site-form-group label {
  display: block;
  margin-bottom: 6px;
  font-size: 13px;
  font-weight: 600;
  color: var(--hd-text);
}

.site-form-error {
  display: block;
  margin-top: 4px;
  font-size: 12px;
  color: var(--hd-error, #d54941);
}

.site-submit-status {
  display: flex;
  align-items: center;
  gap: 6px;
  margin-top: 14px;
  padding: 10px 14px;
  border-radius: var(--hd-radius-lg);
  font-size: 13px;
}

.site-submit-status.success {
  background: #e8f8f0;
  color: #2ba471;
  border: 1px solid #c5e8d4;
}

.site-submit-status.error {
  background: #fdecee;
  color: #d54941;
  border: 1px solid #f7c5c8;
}

@media (max-width: 860px) {
  .site-contact-layout { grid-template-columns: 1fr; gap: 28px; }
}
</style>
