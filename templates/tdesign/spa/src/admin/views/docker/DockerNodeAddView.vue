<template>
  <div class="td-page">
    <div class="td-page-head">
      <div>
        <h3 class="td-page-title"><i class="mdi mdi-server-plus"></i>添加 Docker 节点</h3>
        <p class="td-page-subtitle">接入独立宝塔 Docker 面板实例</p>
      </div>
      <t-button theme="default" variant="outline" @click="$router.push('/docker/node')">
        <i class="mdi mdi-arrow-left"></i> 返回列表
      </t-button>
    </div>

    <div class="td-set-card">
      <div class="td-set-card-hd">
        <div class="td-set-icon"><i class="mdi mdi-docker"></i></div>
        <div>
          <h4>节点信息</h4>
          <p>填写宝塔面板地址与接口密钥</p>
        </div>
      </div>
      <div class="td-set-card-bd">
        <t-loading :loading="saving" text="提交中…" size="small">
          <div class="td-form">
            <div class="td-form-row">
              <label>节点名称 <span class="td-text-danger">*</span></label>
              <t-input v-model="form.name" placeholder="如：北京节点A" />
            </div>

            <div class="td-form-row">
              <label>宝塔面板地址 <span class="td-text-danger">*</span></label>
              <t-input v-model="form.btip" placeholder="IP 或域名" />
            </div>

            <div class="td-form-grid">
              <div class="td-form-row">
                <label>端口</label>
                <t-input-number v-model="form.btdk" :min="1" :max="65535" />
              </div>
              <div class="td-form-row">
                <label>HTTPS</label>
                <t-select v-model="form.ptl">
                  <t-option value="false" label="否" />
                  <t-option value="true" label="是" />
                </t-select>
              </div>
            </div>

            <div class="td-form-row">
              <label>宝塔接口密钥 <span class="td-text-danger">*</span></label>
              <t-textarea v-model="form.btmy" :autosize="{ minRows: 2, maxRows: 3 }" placeholder="宝塔面板 API 密钥" />
            </div>

            <div class="td-form-row">
              <label>调用密钥（外部 API 鉴权）</label>
              <t-input v-model="form.ktmy" placeholder="留空则不校验调用密钥" />
            </div>

            <div class="td-form-row">
              <label>二级验证密钥</label>
              <t-input v-model="form.qmk" placeholder="与调用密钥组合 md5 校验" />
            </div>

            <div class="td-form-row">
              <label>启用</label>
              <t-select v-model="form.qk">
                <t-option value="true" label="启用" />
                <t-option value="false" label="禁用" />
              </t-select>
            </div>

            <div class="td-form-note">
              <b>提示:</b> 接口密钥从宝塔面板「安全 → API 接口」获取；调用密钥/二级验证密钥用于外部 API 鉴权，可留空。
            </div>

            <div class="td-form-actions">
              <t-button theme="primary" :loading="saving" @click="submit">
                <i class="mdi mdi-content-save"></i> 提交
              </t-button>
              <t-button theme="default" variant="outline" @click="$router.push('/docker/node')">取消</t-button>
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
import { addDockerNode } from '@/admin/api/docker'

const router = useRouter()
const saving = ref(false)

const form = reactive({
  name: '',
  btip: '',
  btdk: 8888,
  ptl: 'false',
  btmy: '',
  ktmy: '',
  qmk: '',
  qk: 'true',
})

async function submit() {
  if (!form.name) { MessagePlugin.warning('请输入节点名称'); return }
  if (!form.btip) { MessagePlugin.warning('请输入面板地址'); return }
  if (!form.btmy) { MessagePlugin.warning('请输入宝塔接口密钥'); return }
  saving.value = true
  const r = await addDockerNode({ ...form })
  saving.value = false
  if (r.ok) {
    MessagePlugin.success(r.message || '添加成功')
    router.push('/docker/node')
  } else {
    MessagePlugin.error(r.message || '添加失败')
  }
}
</script>

<style scoped>
.td-form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0 16px;
}
</style>
