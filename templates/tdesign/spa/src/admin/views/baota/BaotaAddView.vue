<template>
  <div class="td-page">
    <div class="td-page-head">
      <div>
        <h3 class="td-page-title"><i class="mdi mdi-server-plus"></i>添加宝塔</h3>
        <p class="td-page-subtitle">对接宝塔面板以管理主机</p>
      </div>
      <t-button theme="default" variant="outline" @click="$router.push('/baota')">
        <i class="mdi mdi-arrow-left"></i> 返回列表
      </t-button>
    </div>

    <div class="td-set-card">
      <div class="td-set-card-hd">
        <div class="td-set-icon"><i class="mdi mdi-server"></i></div>
        <div>
          <h4>宝塔信息</h4>
          <p>填写宝塔面板的连接信息</p>
        </div>
      </div>
      <div class="td-set-card-bd">
        <t-loading :loading="loading" text="提交中…" size="small">
          <div class="td-form">
            <div class="td-form-row">
              <label>宝塔 IP <span class="td-text-danger">*</span></label>
              <t-input v-model="form.ip" placeholder="如 192.168.1.1" @input="onIpInput" />
            </div>

            <div class="td-form-row">
              <label>宝塔端口 <span class="td-text-danger">*</span></label>
              <t-input v-model="form.dk" placeholder="默认 8888" />
            </div>

            <div class="td-form-row">
              <label>宝塔密钥 <span class="td-text-danger">*</span></label>
              <t-textarea
                v-model="form.key"
                :autosize="{ minRows: 2, maxRows: 4 }"
                placeholder="宝塔面板 API 密钥"
              />
            </div>

            <div class="td-form-row">
              <label>FTP 地址</label>
              <t-input v-model="form.ftpdz" placeholder="FTP 连接地址" />
            </div>

            <div class="td-form-row">
              <label>域名解析说明</label>
              <t-textarea v-model="form.urlla" :autosize="{ minRows: 2, maxRows: 4 }" />
            </div>

            <div class="td-form-row">
              <label>宝塔编号</label>
              <t-input v-model="form.bh" placeholder="可中文,可随机">
                <template #suffix>
                  <t-link theme="primary" @click="genBh"><i class="mdi mdi-shuffle-variant"></i></t-link>
                </template>
              </t-input>
            </div>

            <div class="td-form-row">
              <label>操作系统</label>
              <t-select v-model="form.btos">
                <t-option :value="1" label="Linux" />
                <t-option :value="2" label="Windows" />
              </t-select>
            </div>

            <div class="td-form-switch">
              <div class="td-form-switch-txt">
                <strong>安全访问 HTTPS</strong>
                <span>开启后通过 HTTPS 访问宝塔</span>
              </div>
              <t-switch v-model="form.xieyi" />
            </div>

            <div class="td-form-switch">
              <div class="td-form-switch-txt">
                <strong>宝塔接口开关</strong>
                <span>关闭后无法调用宝塔</span>
              </div>
              <t-switch v-model="form.kg" />
            </div>

            <div class="td-form-note">
              <b>提示:</b> 必须安装至少一个 PHP;推荐宝塔 7.9.0+。
            </div>

            <div class="td-form-actions">
              <t-button theme="primary" :loading="loading" @click="submit">
                <i class="mdi mdi-content-save"></i> 提交
              </t-button>
              <t-button theme="default" variant="outline" @click="$router.push('/baota')">取消</t-button>
            </div>
          </div>
        </t-loading>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { MessagePlugin } from 'tdesign-vue-next'
import { addBaota } from '@/admin/api/baota'

const router = useRouter()
const loading = ref(false)

const form = reactive({
  ip: '',
  dk: '8888',
  key: '',
  ftpdz: '',
  urlla: '',
  bh: '',
  btos: 1,
  xieyi: false,
  kg: true,
})

function rand(len) {
  let s = ''
  const chars = 'abcdefghijklmnopqrstuvwxyz0123456789'
  for (let i = 0; i < len; i++) s += chars[Math.floor(Math.random() * chars.length)]
  return s
}
function genBh() {
  form.bh = 'mn' + rand(6) + 'f'
}

function onIpInput() {
  if (form.ip) {
    if (!form.urlla || form.urlla.startsWith('请将域名A记录到 ')) {
      form.urlla = `请将域名A记录到 ${form.ip}`
    }
    if (!form.ftpdz) {
      form.ftpdz = form.ip
    }
  }
}

async function submit() {
  if (!form.ip) { MessagePlugin.warning('请输入宝塔IP'); return }
  if (!form.dk) { MessagePlugin.warning('请输入宝塔端口'); return }
  if (!form.key) { MessagePlugin.warning('请输入宝塔密钥'); return }
  loading.value = true
  const r = await addBaota({
    ip: form.ip,
    dk: form.dk,
    key: form.key,
    bh: form.bh,
    btos: form.btos,
    urlla: form.urlla,
    ftpdz: form.ftpdz,
    xieyi: form.xieyi ? 'true' : 'false',
    kg: form.kg ? 'true' : 'false',
  })
  loading.value = false
  if (r.ok) {
    MessagePlugin.success('添加成功')
    router.push('/baota')
  } else {
    MessagePlugin.error(r.message || '添加失败')
  }
}
</script>
