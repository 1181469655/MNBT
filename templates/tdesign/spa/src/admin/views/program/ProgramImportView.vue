<template>
  <div class="td-page">
    <div class="td-page-head">
      <div>
        <h3 class="td-page-title"><i class="mdi mdi-upload"></i>导入程序</h3>
        <p class="td-page-subtitle">上传打包导出后的程序包(zip 文件)快速导入</p>
      </div>
      <t-button theme="default" variant="outline" @click="goBack">
        <i class="mdi mdi-arrow-left"></i> 返回列表
      </t-button>
    </div>

    <div class="td-set-card">
      <div class="td-set-card-hd">
        <div class="td-set-icon"><i class="mdi mdi-package-variant-closed"></i></div>
        <div>
          <h4>程序包导入</h4>
          <p>选择打包导出后的 zip 程序包上传</p>
        </div>
      </div>
      <div class="td-set-card-bd">
        <div class="td-form">
          <div class="td-form-row">
            <label>程序包文件 <span class="req">*</span></label>
            <div class="file-row">
              <label class="file-pick">
                <i class="mdi mdi-file-upload-outline"></i>
                <span>{{ form.filecx ? form.filecx.name : '选择 .zip 文件…' }}</span>
                <input type="file" accept=".zip" @change="onPickFile" />
              </label>
              <span class="td-text-mute td-text-xs" v-if="form.filecx">
                {{ fmtSize(form.filecx.size) }}
              </span>
            </div>
            <div class="td-form-hint">仅支持打包导出后的 zip 程序包</div>
          </div>

          <div class="td-form-switch">
            <div class="td-form-switch-txt">
              <strong>导入后自动上架</strong>
              <span>关闭后保存为下架状态,可在程序列表中再开启</span>
            </div>
            <t-switch v-model="form.kg" />
          </div>

          <div v-if="uploading" class="td-form-row">
            <label>上传进度</label>
            <t-progress :percentage="progress" :label="true" />
            <div class="td-form-hint">正在上传,请勿关闭页面</div>
          </div>

          <div class="td-form-note">
            <b>提示:</b> 选择打包导出后的程序包(zip 文件)。导入后将自动写入数据库,可在程序列表中查看与编辑。
          </div>

          <div class="td-form-actions">
            <t-button theme="primary" :loading="uploading" :disabled="!form.filecx" @click="submit">
              <i class="mdi mdi-upload"></i> 开始上传
            </t-button>
            <t-button theme="default" variant="outline" @click="goBack">取消</t-button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import { MessagePlugin } from 'tdesign-vue-next'
import http, { parseResult } from '@/shared/api/http'

const router = useRouter()

const boot = window.__TD_BOOT__ || {}
const ajaxUrl = boot.ajaxBase || './ajax.php'

const uploading = ref(false)
const progress = ref(0)

const form = reactive({
  filecx: null,
  kg: true,
})

function onPickFile(e) {
  const f = e.target.files && e.target.files[0]
  if (f) form.filecx = f
}

function goBack() {
  router.push('/program')
}

function fmtSize(bytes) {
  if (!bytes) return '0 B'
  const units = ['B', 'KB', 'MB', 'GB']
  const pow = Math.min(Math.floor(Math.log(bytes) / Math.log(1024)), units.length - 1)
  return (bytes / Math.pow(1024, pow)).toFixed(2) + ' ' + units[pow]
}

async function submit() {
  if (!form.filecx) return MessagePlugin.warning('请选择程序包文件')

  const fd = new FormData()
  fd.append('gn', 'cxfiledru')
  fd.append('file', form.filecx)
  fd.append('sxj', form.kg ? 'true' : 'false')

  uploading.value = true
  progress.value = 0
  try {
    const res = await http.post(ajaxUrl, fd, {
      onUploadProgress: (e) => {
        if (e.total) {
          progress.value = Math.min(99, Math.round((e.loaded / e.total) * 100))
        }
      },
    })
    progress.value = 100
    const r = parseResult(res.data)
    if (r.ok) {
      MessagePlugin.success('导入成功')
      router.push('/program')
    }
  } finally {
    uploading.value = false
  }
}
</script>

<style scoped>
.req {
  color: var(--td-error);
  margin-left: 2px;
}
.file-row {
  display: flex;
  align-items: center;
  gap: 12px;
  flex-wrap: wrap;
}
.file-pick {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 7px 14px;
  background: var(--td-surface);
  border: 1px dashed var(--td-border);
  border-radius: var(--td-radius);
  cursor: pointer;
  font-size: 13px;
  color: var(--td-text-secondary);
  transition: border-color 0.15s ease, color 0.15s ease;
}
.file-pick:hover {
  border-color: var(--td-brand);
  color: var(--td-brand);
}
.file-pick input[type='file'] {
  position: absolute;
  width: 0;
  height: 0;
  opacity: 0;
  overflow: hidden;
}
</style>
