<template>
  <div class="td-page realname-page">
    <div v-if="loading" class="td-card loading-box">
      <t-loading text="正在读取认证状态" />
    </div>

    <template v-else>
      <div class="status-band" :class="`status-${status}`">
        <div class="status-icon"><i class="mdi" :class="statusMeta.icon"></i></div>
        <div class="status-main">
          <div class="status-title">{{ statusMeta.title }}</div>
          <div class="status-desc">{{ statusMeta.desc }}</div>
        </div>
        <t-tag :theme="statusMeta.theme" variant="light">{{ statusMeta.tag }}</t-tag>
      </div>

      <div v-if="auth" class="td-card info-card">
        <div class="td-card-head">认证信息</div>
        <div class="info-grid">
          <div><span>姓名</span><strong>{{ auth.real_name || '—' }}</strong></div>
          <div><span>手机号</span><strong>{{ auth.phone || '—' }}</strong></div>
          <div><span>身份证号</span><strong>{{ auth.id_card || '—' }}</strong></div>
          <div><span>提交时间</span><strong>{{ auth.created_at || '—' }}</strong></div>
        </div>
        <div v-if="auth.audit_note" class="audit-note">
          <i class="mdi mdi-information"></i>
          <span>{{ auth.audit_note }}</span>
        </div>
      </div>

      <div v-if="canSubmit" class="td-card form-card">
        <div class="td-card-head">{{ status === 'rejected' ? '重新提交认证' : '提交实名认证' }}</div>
        <div class="td-card-body">
          <t-form ref="formRef" :data="form" :rules="rules" label-width="96px" @submit="onSubmit">
            <div class="section-title">身份信息</div>
            <t-form-item label="真实姓名" name="real_name">
              <t-input v-model="form.real_name" placeholder="请输入身份证上的姓名" maxlength="20" clearable />
            </t-form-item>
            <t-form-item label="手机号" name="phone">
              <t-input v-model="form.phone" placeholder="请输入 11 位手机号" maxlength="11" clearable />
            </t-form-item>
            <t-form-item label="身份证号" name="id_card">
              <t-input v-model="form.id_card" placeholder="请输入 18 位身份证号" maxlength="18" clearable @change="normalizeIdCard" />
            </t-form-item>

            <div class="section-title photo-title">证件照片</div>
            <div class="upload-grid">
              <label v-for="item in uploadItems" :key="item.key" class="upload-box" :class="{ filled: previews[item.key] }">
                <input type="file" accept="image/jpeg,image/png" @change="onFileChange($event, item.key)" />
                <img v-if="previews[item.key]" :src="previews[item.key]" :alt="item.title" />
                <div v-else class="upload-placeholder">
                  <i class="mdi" :class="item.icon"></i>
                  <strong>{{ item.title }}</strong>
                  <span>{{ item.tip }}</span>
                </div>
                <div v-if="previews[item.key]" class="upload-replace"><i class="mdi mdi-camera"></i> 更换照片</div>
              </label>
            </div>

            <div v-if="ocrRunning" class="ocr-state">
              <t-loading size="small" />
              <span>正在使用本地 OCR 识别身份证正面，请勿关闭页面</span>
            </div>
            <div v-else-if="ocrTried" class="ocr-result" :class="{ incomplete: !ocrIdCard || !ocrName }">
              <i class="mdi" :class="ocrIdCard && ocrName ? 'mdi-check-circle' : 'mdi-alert-circle'"></i>
              <div>
                <strong>{{ ocrIdCard && ocrName ? '本地 OCR 识别完成' : 'OCR 信息不完整' }}</strong>
                <span>姓名：{{ ocrName || '未识别' }}　身份证号：{{ ocrIdCard || '未识别' }}</span>
              </div>
            </div>

            <div class="privacy-tip">
              <i class="mdi mdi-shield-lock"></i>
              <span>OCR 在当前浏览器本地运行，不调用外部 API；手持身份证照片仅用于人工审核。</span>
            </div>

            <t-form-item class="submit-row">
              <t-button theme="primary" type="submit" size="large" :loading="submitting" :disabled="ocrRunning">
                提交认证
              </t-button>
            </t-form-item>
          </t-form>
        </div>
      </div>
    </template>
  </div>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, reactive, ref } from 'vue'
import { MessagePlugin } from 'tdesign-vue-next'
import { getRealnameInfo, submitRealname } from '@/account/api/plugins'

const boot = window.__TD_BOOT__ || {}
const formRef = ref()
const loading = ref(true)
const submitting = ref(false)
const auth = ref(null)
const form = reactive({ real_name: '', phone: '', id_card: '' })
const files = reactive({ front: null, back: null, hand: null })
const previews = reactive({ front: '', back: '', hand: '' })
const ocrRunning = ref(false)
const ocrTried = ref(false)
const ocrName = ref('')
const ocrIdCard = ref('')

const uploadItems = [
  { key: 'front', title: '身份证正面', tip: '选择后自动识别', icon: 'mdi-card-account-details' },
  { key: 'back', title: '身份证反面', tip: '国徽面，清晰完整', icon: 'mdi-card-bulleted' },
  { key: 'hand', title: '手持身份证', tip: '人物与证件均清晰', icon: 'mdi-account-box' },
]

const status = computed(() => auth.value?.status || 'none')
const canSubmit = computed(() => status.value === 'none' || status.value === 'rejected')
const statusMeta = computed(() => ({
  none: { title: '尚未实名认证', desc: '购买或充值前需要完成实名认证', tag: '未认证', theme: 'default', icon: 'mdi-account-alert' },
  pending: { title: '认证资料审核中', desc: '资料已提交，请等待管理员完成复核', tag: '审核中', theme: 'warning', icon: 'mdi-clock' },
  approved: { title: '实名认证已通过', desc: '当前账号已满足产品购买与充值要求', tag: '已认证', theme: 'success', icon: 'mdi-account-check' },
  rejected: { title: '实名认证未通过', desc: '请根据审核说明修正资料后重新提交', tag: '未通过', theme: 'danger', icon: 'mdi-account-remove' },
}[status.value]))

const rules = {
  real_name: [{ validator: (v) => /^[\u4e00-\u9fa5·•.]{2,20}$/.test((v || '').trim()), message: '请输入正确的姓名', trigger: 'blur' }],
  phone: [{ validator: (v) => /^1[3-9]\d{9}$/.test((v || '').trim()), message: '请输入正确的 11 位手机号', trigger: 'blur' }],
  id_card: [{ validator: (v) => /^\d{17}[\dX]$/.test((v || '').trim().toUpperCase()), message: '请输入正确的 18 位身份证号', trigger: 'blur' }],
}

function normalizeIdCard() {
  form.id_card = form.id_card.trim().toUpperCase()
}

async function load() {
  loading.value = true
  const res = await getRealnameInfo()
  loading.value = false
  if (res.ok) auth.value = res.data.auth || null
}

function compressImage(file) {
  return new Promise((resolve, reject) => {
    const image = new Image()
    const url = URL.createObjectURL(file)
    image.onload = () => {
      let width = image.width
      let height = image.height
      const max = 1280
      if (width > max || height > max) {
        const scale = max / Math.max(width, height)
        width = Math.round(width * scale)
        height = Math.round(height * scale)
      }
      const canvas = document.createElement('canvas')
      canvas.width = width
      canvas.height = height
      canvas.getContext('2d').drawImage(image, 0, 0, width, height)
      canvas.toBlob((blob) => {
        URL.revokeObjectURL(url)
        blob ? resolve(blob) : reject(new Error('图片压缩失败'))
      }, 'image/jpeg', 0.85)
    }
    image.onerror = () => {
      URL.revokeObjectURL(url)
      reject(new Error('图片读取失败'))
    }
    image.src = url
  })
}

async function onFileChange(event, key) {
  const input = event.target
  const file = input.files?.[0]
  if (!file) return
  if (file.size > 8 * 1024 * 1024) {
    MessagePlugin.warning('图片不能超过 8MB')
    input.value = ''
    return
  }
  try {
    const blob = await compressImage(file)
    files[key] = blob
    if (previews[key]) URL.revokeObjectURL(previews[key])
    previews[key] = URL.createObjectURL(blob)
    if (key === 'front') await runOcr(blob)
  } catch (e) {
    MessagePlugin.error(e.message || '图片处理失败')
  }
}

function idcardCheck(id) {
  if (!/^\d{17}[\dX]$/.test(id)) return false
  const weights = [7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2]
  const mapping = ['1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2']
  const sum = weights.reduce((total, weight, index) => total + Number(id[index]) * weight, 0)
  return mapping[sum % 11] === id[17]
}

function extractIdCard(text) {
  const matches = (text || '').replace(/\s+/g, '').match(/\d{17}[\dXx]/g) || []
  return matches.map((v) => v.toUpperCase()).find(idcardCheck) || ''
}

function extractName(text) {
  for (const line of (text || '').split('\n')) {
    const value = line.replace(/\s+/g, '')
    const match = value.match(/姓名[:：]?([\u4e00-\u9fa5·]{1,10})/)
    if (match?.[1]) return match[1]
  }
  return ''
}

function loadOcrLib() {
  if (window.Tesseract) return Promise.resolve(window.Tesseract)
  return new Promise((resolve, reject) => {
    const script = document.createElement('script')
    script.src = `${boot.realnameOcrBase}tesseract.min.js`
    script.onload = () => window.Tesseract ? resolve(window.Tesseract) : reject(new Error('OCR 加载失败'))
    script.onerror = () => reject(new Error('OCR 库加载失败'))
    document.head.appendChild(script)
  })
}

async function runOcr(blob) {
  ocrRunning.value = true
  ocrTried.value = false
  ocrName.value = ''
  ocrIdCard.value = ''
  try {
    const Tesseract = await loadOcrLib()
    const base = boot.realnameOcrBase
    const result = await Tesseract.recognize(blob, 'chi_sim', {
      workerPath: `${base}worker.min.js`,
      corePath: `${base}tesseract-core.wasm.js`,
      langPath: `${base}lang/`,
      logger: () => {},
    })
    const text = result?.data?.text || ''
    ocrName.value = extractName(text)
    ocrIdCard.value = extractIdCard(text)
    if (ocrName.value) form.real_name = ocrName.value
    if (ocrIdCard.value) form.id_card = ocrIdCard.value
    if (!ocrName.value || !ocrIdCard.value) MessagePlugin.warning('OCR 信息不完整，可手动填写后提交人工复核')
  } catch (e) {
    console.error('[realname] OCR failed:', e)
    MessagePlugin.warning('本地 OCR 加载失败，可手动填写后提交人工复核')
  } finally {
    ocrRunning.value = false
    ocrTried.value = true
  }
}

async function onSubmit({ validateResult }) {
  if (validateResult !== true) return
  if (!files.front || !files.back || !files.hand) {
    MessagePlugin.warning('请上传身份证正面、反面和手持身份证照片')
    return
  }
  submitting.value = true
  const data = new FormData()
  data.append('real_name', form.real_name.trim())
  data.append('phone', form.phone.trim())
  data.append('id_card', form.id_card.trim().toUpperCase())
  data.append('ocr_name', ocrName.value)
  data.append('ocr_id_card', ocrIdCard.value)
  data.append('front_img', files.front, 'front.jpg')
  data.append('back_img', files.back, 'back.jpg')
  data.append('hand_img', files.hand, 'hand.jpg')
  const res = await submitRealname(data)
  submitting.value = false
  if (!res.ok) {
    MessagePlugin.error(res.message || '提交失败')
    return
  }
  MessagePlugin.success(res.message || '提交成功')
  await load()
}

onMounted(load)
onBeforeUnmount(() => Object.values(previews).forEach((url) => url && URL.revokeObjectURL(url)))
</script>

<style scoped>
.realname-page { max-width: 980px; }
.loading-box { min-height: 220px; display: grid; place-items: center; }
.status-band { display: flex; align-items: center; gap: 16px; padding: 20px 22px; margin-bottom: 16px; border: 1px solid var(--td-border); border-left: 4px solid #8b95a5; background: var(--td-surface); }
.status-icon { width: 44px; height: 44px; display: grid; place-items: center; border-radius: 8px; background: #f2f3f5; color: #667085; font-size: 23px; flex-shrink: 0; }
.status-main { flex: 1; min-width: 0; }
.status-title { font-size: 16px; font-weight: 600; color: var(--td-text); }
.status-desc { margin-top: 4px; font-size: 13px; color: var(--td-text-secondary); }
.status-approved { border-left-color: #2ba471; }
.status-approved .status-icon { background: #e8f8f0; color: #2ba471; }
.status-pending { border-left-color: #d48806; }
.status-pending .status-icon { background: #fff7e6; color: #d48806; }
.status-rejected { border-left-color: #d54941; }
.status-rejected .status-icon { background: #fdeeee; color: #d54941; }
.info-card { margin-bottom: 16px; }
.info-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); }
.info-grid > div { display: flex; flex-direction: column; gap: 6px; padding: 16px 20px; border-bottom: 1px solid var(--td-border); }
.info-grid > div:nth-child(odd) { border-right: 1px solid var(--td-border); }
.info-grid span { font-size: 12px; color: var(--td-text-secondary); }
.info-grid strong { font-size: 14px; color: var(--td-text); font-weight: 500; }
.audit-note { display: flex; align-items: flex-start; gap: 8px; margin: 16px 20px; padding: 12px 14px; background: var(--td-bg); color: var(--td-text-secondary); font-size: 13px; line-height: 1.6; }
.audit-note i { color: var(--td-brand); font-size: 17px; }
.form-card { margin-bottom: 20px; }
.section-title { margin: 0 0 18px; padding-bottom: 10px; border-bottom: 1px solid var(--td-border); color: var(--td-text); font-size: 14px; font-weight: 600; }
.photo-title { margin-top: 28px; }
.upload-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; }
.upload-box { position: relative; aspect-ratio: 4 / 3; border: 1px dashed var(--td-border); background: var(--td-bg); cursor: pointer; overflow: hidden; transition: border-color .2s, background .2s; }
.upload-box:hover { border-color: var(--td-brand); background: var(--td-brand-light); }
.upload-box input { position: absolute; width: 1px; height: 1px; opacity: 0; }
.upload-box img { width: 100%; height: 100%; object-fit: cover; display: block; }
.upload-placeholder { height: 100%; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 6px; padding: 14px; text-align: center; box-sizing: border-box; }
.upload-placeholder i { font-size: 30px; color: var(--td-brand); }
.upload-placeholder strong { font-size: 13px; color: var(--td-text); }
.upload-placeholder span { font-size: 12px; color: var(--td-text-placeholder); }
.upload-replace { position: absolute; left: 0; right: 0; bottom: 0; padding: 7px; background: rgba(0, 0, 0, .62); color: #fff; font-size: 12px; text-align: center; }
.ocr-state, .ocr-result, .privacy-tip { display: flex; align-items: center; gap: 10px; margin-top: 14px; padding: 12px 14px; font-size: 13px; }
.ocr-state { background: #edf5ff; color: #0052d9; }
.ocr-result { background: #e8f8f0; color: #237a50; }
.ocr-result.incomplete { background: #fff7e6; color: #9a6700; }
.ocr-result > i { font-size: 20px; }
.ocr-result > div { display: flex; flex-direction: column; gap: 3px; min-width: 0; }
.ocr-result span { overflow-wrap: anywhere; }
.privacy-tip { background: var(--td-bg); color: var(--td-text-secondary); line-height: 1.6; }
.privacy-tip i { color: #2ba471; font-size: 19px; flex-shrink: 0; }
.submit-row { margin-top: 22px; margin-bottom: 0; }
@media (max-width: 720px) {
  .status-band { align-items: flex-start; flex-wrap: wrap; }
  .status-band :deep(.t-tag) { margin-left: 60px; }
  .info-grid { grid-template-columns: 1fr; }
  .info-grid > div:nth-child(odd) { border-right: none; }
  .upload-grid { grid-template-columns: 1fr; }
  .upload-box { aspect-ratio: 16 / 9; }
}
</style>
