<template>
  <div class="td-page">
    <div class="td-page-head">
      <div>
        <h3 class="td-page-title"><i class="mdi mdi-plus-circle"></i>添加主机</h3>
        <p class="td-page-subtitle">在宝塔节点上创建虚拟主机</p>
      </div>
      <t-button theme="default" variant="outline" @click="$router.push('/host')">
        <i class="mdi mdi-arrow-left"></i> 返回列表
      </t-button>
    </div>

    <div class="td-set-card">
      <div class="td-set-card-hd">
        <div class="td-set-icon"><i class="mdi mdi-server-plus"></i></div>
        <div>
          <h4>主机信息</h4>
          <p>选择宝塔节点并填写账号密码</p>
        </div>
      </div>
      <div class="td-set-card-bd">
        <t-loading :loading="loading" text="提交中…" size="small">
          <div class="td-form">
            <div class="td-form-row">
              <label>宝塔节点 <span class="td-text-danger">*</span></label>
              <t-select v-model="form.btdh" placeholder="请选择宝塔" :loading="btLoading">
                <t-option
                  v-for="b in baotaList"
                  :key="b.id"
                  :value="b.btdh"
                  :label="`${b.btdh} (${b.btip})`"
                />
              </t-select>
            </div>

            <div class="td-form-row">
              <label>账号 <span class="td-text-danger">*</span></label>
              <t-input v-model="form.user" placeholder="FTP/SQL 账号">
                <template #suffix>
                  <t-link theme="primary" @click="genUser"><i class="mdi mdi-shuffle-variant"></i></t-link>
                </template>
              </t-input>
            </div>

            <div class="td-form-row">
              <label>密码 <span class="td-text-danger">*</span></label>
              <t-input v-model="form.pass" placeholder="FTP/SQL 密码">
                <template #suffix>
                  <t-link theme="primary" @click="genPass"><i class="mdi mdi-shuffle-variant"></i></t-link>
                </template>
              </t-input>
            </div>

            <div class="td-form-row">
              <label>网页空间 (MB)</label>
              <t-input-number v-model="form.webkj" theme="normal" :min="0" />
              <div class="td-form-hint">可选,留 0 表示不限制</div>
            </div>

            <div class="td-form-row">
              <label>数据库空间 (MB)</label>
              <t-input-number v-model="form.sqlkj" theme="normal" :min="0" />
              <div class="td-form-hint">可选,留 0 表示不限制</div>
            </div>

            <div class="td-form-row">
              <label>最大流量 (G/月) <span class="td-text-danger">*</span></label>
              <t-input-number v-model="form.ll" theme="normal" :min="0" />
            </div>

            <div class="td-form-row">
              <label>域名最大绑定数</label>
              <t-input-number v-model="form.ymbds" theme="normal" :min="0" />
              <div class="td-form-hint">0 = 不限制</div>
            </div>

            <div class="td-form-row">
              <label>到期时间</label>
              <t-date-picker
                v-model="form.datae"
                mode="date"
                format="YYYY-MM-DD"
                value-format="YYYY-MM-DD"
                clearable
              />
              <div class="td-form-hint">留空为永久</div>
            </div>

            <div class="td-form-switch">
              <div class="td-form-switch-txt">
                <strong>主机开关</strong>
                <span>关闭后用户无法访问</span>
              </div>
              <t-switch v-model="form.kg" />
            </div>

            <div class="td-form-note">
              <b>提示:</b> 系统会自动检测最新 PHP;到期时间留空为永久;流量每月 1 日重置。
            </div>

            <div class="td-form-actions">
              <t-button theme="primary" :loading="loading" @click="submit">
                <i class="mdi mdi-content-save"></i> 提交
              </t-button>
              <t-button theme="default" variant="outline" @click="$router.push('/host')">取消</t-button>
            </div>
          </div>
        </t-loading>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { MessagePlugin } from 'tdesign-vue-next'
import { addHost } from '@/admin/api/host'
import { listBaota } from '@/admin/api/baota'

const router = useRouter()
const loading = ref(false)
const btLoading = ref(false)
const baotaList = ref([])

const form = reactive({
  btdh: '',
  user: '',
  pass: '',
  webkj: 1024,
  sqlkj: 256,
  ll: 10,
  ymbds: 0,
  datae: '',
  kg: true,
})

function rand(len) {
  let s = ''
  const chars = 'abcdefghijklmnopqrstuvwxyz0123456789'
  for (let i = 0; i < len; i++) s += chars[Math.floor(Math.random() * chars.length)]
  return s
}
function genUser() {
  form.user = 'f' + rand(4) + rand(4) + 'w'
}
function genPass() {
  form.pass = rand(12)
}

async function loadBaota() {
  btLoading.value = true
  const r = await listBaota({ page: 1, limit: 200 })
  btLoading.value = false
  if (r.ok && r.data) {
    baotaList.value = r.data.rows || []
  } else if (!r.ok) {
    MessagePlugin.error(r.message || '宝塔列表加载失败')
  }
}

async function submit() {
  if (!form.btdh) { MessagePlugin.warning('请选择宝塔'); return }
  if (!form.user) { MessagePlugin.warning('请输入账号'); return }
  if (!form.pass) { MessagePlugin.warning('请输入密码'); return }
  if (form.ll == null) { MessagePlugin.warning('请输入流量'); return }
  loading.value = true
  const r = await addHost({
    btdh: form.btdh,
    user: form.user,
    pass: form.pass,
    webkj: form.webkj,
    sqlkj: form.sqlkj,
    ll: form.ll,
    ymbds: form.ymbds,
    datae: form.datae || '',
    kg: form.kg ? 'true' : 'false',
  })
  loading.value = false
  if (r.ok) {
    MessagePlugin.success('添加成功')
    router.push('/host')
  } else {
    MessagePlugin.error(r.message || '添加失败')
  }
}

onMounted(loadBaota)
</script>
