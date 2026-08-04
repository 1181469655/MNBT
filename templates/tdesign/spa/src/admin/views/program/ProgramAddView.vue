<template>
  <div class="td-page">
    <div class="td-page-head">
      <div>
        <h3 class="td-page-title"><i class="mdi mdi-plus-box-outline"></i>添加程序</h3>
        <p class="td-page-subtitle">上传程序源码包并填写基础信息</p>
      </div>
      <t-button theme="default" variant="outline" @click="goBack">
        <i class="mdi mdi-arrow-left"></i> 返回列表
      </t-button>
    </div>

    <div class="td-set-card">
      <div class="td-set-card-hd">
        <div class="td-set-icon"><i class="mdi mdi-webpack"></i></div>
        <div>
          <h4>程序基础信息</h4>
          <p>填写名称、空间、价格与程序源码包</p>
        </div>
      </div>
      <div class="td-set-card-bd">
        <t-loading :loading="loading" text="提交中…" size="small">
          <div class="td-form">
            <div class="td-form-row">
              <label>程序名称 <span class="req">*</span></label>
              <t-input v-model="form.cxname" placeholder="请输入程序名称" clearable />
            </div>

            <div class="td-form-row">
              <label>程序介绍 <span class="req">*</span></label>
              <t-textarea
                v-model="form.cxjs"
                :autosize="{ minRows: 3, maxRows: 8 }"
                placeholder="请输入程序介绍"
              />
            </div>

            <div class="td-form-row-grid">
              <div class="td-form-row">
                <label>价格 (元) <span class="req">*</span></label>
                <t-input-number v-model="form.cxrmb" :min="0" theme="normal" placeholder="0 表示免费" />
                <div class="td-form-hint">0 表示免费</div>
              </div>

              <div class="td-form-row">
                <label>网页空间 (MB) <span class="req">*</span></label>
                <t-input-number v-model="form.cxwebkj" :min="0" theme="normal" />
              </div>

              <div class="td-form-row">
                <label>数据库空间 (MB) <span class="req">*</span></label>
                <t-input-number v-model="form.cxsqlkj" :min="0" theme="normal" />
              </div>
            </div>

            <div class="td-form-row">
              <label>部署完成提示</label>
              <t-textarea
                v-model="form.alerts"
                :autosize="{ minRows: 2, maxRows: 4 }"
                placeholder="部署完成后给用户的提示语,可留空"
              />
            </div>

            <div class="td-form-row">
              <label>程序源码包 <span class="req">*</span></label>
              <div class="file-row">
                <label class="file-pick">
                  <i class="mdi mdi-file-upload-outline"></i>
                  <span>{{ form.filecx ? form.filecx.name : '选择文件…' }}</span>
                  <input type="file" accept=".zip,.tar,.gz,.tgz" @change="onPickFile" />
                </label>
                <span class="td-text-mute td-text-xs">支持 zip / tar.gz 压缩包</span>
              </div>
            </div>

            <div class="td-form-switch">
              <div class="td-form-switch-txt">
                <strong>是否上架</strong>
                <span>关闭后保存为下架状态,可在列表中再开启</span>
              </div>
              <t-switch v-model="form.kg" />
            </div>

            <div class="td-form-note">
              <b>说明:</b> 本表单仅录入基础字段。如需配置安装时操作 (pz)、用户填写表单 (inp)、展示图 (src) 等高级字段,
              请使用 default 主题或后续在程序列表中编辑。
            </div>

            <div class="td-form-actions">
              <t-button theme="primary" :loading="loading" @click="submit">
                <i class="mdi mdi-content-save-outline"></i> 提交添加
              </t-button>
              <t-button theme="default" variant="outline" @click="goBack">取消</t-button>
            </div>
          </div>
        </t-loading>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import { MessagePlugin } from 'tdesign-vue-next'
import { addProgram } from '@/admin/api/program'

const router = useRouter()

const loading = ref(false)

const form = reactive({
  cxname: '',
  cxjs: '',
  cxrmb: 0,
  cxwebkj: 0,
  cxsqlkj: 0,
  alerts: '',
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

async function submit() {
  if (!form.cxname) return MessagePlugin.warning('请填写程序名称')
  if (!form.cxjs) return MessagePlugin.warning('请填写程序介绍')
  if (form.cxrmb == null || form.cxrmb === '') return MessagePlugin.warning('请填写价格')
  if (form.cxwebkj == null || form.cxwebkj === '') return MessagePlugin.warning('请填写网页空间')
  if (form.cxsqlkj == null || form.cxsqlkj === '') return MessagePlugin.warning('请填写数据库空间')
  if (!form.filecx) return MessagePlugin.warning('请选择程序源码包')

  const fd = new FormData()
  fd.append('gn', 'cxtj')
  fd.append('cxname', form.cxname)
  fd.append('cxjs', form.cxjs)
  fd.append('cxrmb', String(form.cxrmb))
  fd.append('cxwebkj', String(form.cxwebkj))
  fd.append('cxsqlkj', String(form.cxsqlkj))
  fd.append('alerts', form.alerts || '')
  fd.append('filecx', form.filecx)
  fd.append('kg', form.kg ? 'true' : 'false')

  loading.value = true
  const r = await addProgram(fd)
  loading.value = false
  if (r.ok) {
    MessagePlugin.success('添加成功')
    router.push('/program')
  }
}
</script>

<style scoped>
.req {
  color: var(--td-error);
  margin-left: 2px;
}
.td-form-row-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
  gap: 16px;
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
