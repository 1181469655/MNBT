<template>
  <div class="dk-appstore">
    <!-- 已有容器提示 -->
    <t-alert
      v-if="hasContainer"
      theme="warning"
      message="您已创建容器，单容器模型下无法再次创建。如需更换应用，请联系管理员先删除现有容器。"
      style="margin-bottom: 16px"
    />

    <t-card>
      <template #header>
        <div class="dk-card-hd">
          <h3>应用商店</h3>
          <t-input
            v-model="keyword"
            placeholder="搜索应用名称…"
            style="width: 260px"
            clearable
          >
            <template #prefix-icon><i class="mdi mdi-magnify"></i></template>
          </t-input>
        </div>
      </template>

      <t-loading :loading="loading" text="正在加载应用列表…" size="large">
        <div v-if="filteredApps.length" class="dk-grid">
          <div v-for="app in pagedApps" :key="app.appname" class="dk-app-card">
            <div class="dk-app-head">
              <div class="dk-app-icon">{{ appIconChar(app.appname) }}</div>
              <div>
                <h4>{{ app.apptitle || app.appname }}</h4>
                <div class="dk-app-type">{{ typeLabel(app.apptype) }} · v{{ versionSummary(app.appversion) }}</div>
              </div>
            </div>
            <div class="dk-app-desc">{{ app.desc || app.description || ('应用标识：' + app.appname) }}</div>
            <div class="dk-app-foot">
              <span class="dk-app-name dk-mono">{{ app.appname }}</span>
              <t-button
                theme="primary"
                size="small"
                :disabled="hasContainer"
                @click="openInstall(app)"
              >
                <i class="mdi mdi-download"></i> 安装
              </t-button>
            </div>
          </div>
        </div>
        <div v-if="filteredApps.length" class="dk-pager">
          <t-pagination
            v-model="page"
            :total="filteredApps.length"
            :page-size="pageSize"
            :show-jumper="true"
            :show-page-size="false"
          />
        </div>
        <div v-else class="dk-empty">
          <i class="mdi mdi-magnify-close dk-empty-ico"></i>
          <p>{{ keyword ? '没有匹配的应用' : '暂无可用应用' }}</p>
        </div>
      </t-loading>
    </t-card>

    <!-- 安装弹窗 -->
    <t-dialog
      v-model:visible="installVisible"
      :header="`安装应用：${currentApp?.apptitle || currentApp?.appname || ''}`"
      :on-confirm="doInstall"
      width="640px"
      :confirm-btn="{ content: '确认安装', loading: installing }"
      :close-on-overlay-click="false"
    >
      <div v-if="currentApp">
        <!-- 应用头部 -->
        <div class="dk-install-head">
          <div class="dk-app-icon">{{ appIconChar(currentApp.appname) }}</div>
          <div>
            <h4>{{ currentApp.apptitle || currentApp.appname }}</h4>
            <span class="dk-app-desc">{{ currentApp.desc || '' }}</span>
          </div>
        </div>

        <!-- 依赖提示 -->
        <t-alert
          v-if="currentApp.depend && currentApp.depend.length"
          theme="warning"
          :message="`此应用依赖：${dependText(currentApp.depend)}，请确保依赖应用已安装。`"
          style="margin-bottom: 16px"
        />

        <t-form class="dk-install-form" :data="installForm" label-align="top">
          <!-- 版本选择 -->
          <t-form-item label="版本选择">
            <t-select v-model="installForm.dk_version">
              <t-option
                v-for="opt in versionOptions"
                :key="opt.value"
                :value="opt.value"
                :label="opt.label"
              />
            </t-select>
          </t-form-item>

          <!-- CPU / 内存 -->
          <div class="dk-form-grid">
            <t-form-item :label="`CPU 核数（0=不限制，上限 ${cpuMax}）`">
              <t-input-number v-model="installForm.cpus" :min="0" :max="cpuMax" :step="1" />
            </t-form-item>
            <t-form-item :label="`内存 MB（0=不限制，上限 ${memMax}）`">
              <t-input-number v-model="installForm.memory_limit" :min="0" :max="memMax" :step="32" />
            </t-form-item>
          </div>

          <!-- 允许外网访问 -->
          <t-form-item>
            <t-checkbox v-model="installForm.allow_access">允许外网访问（通过主机 IP + 端口访问，设了域名可不勾）</t-checkbox>
          </t-form-item>

          <!-- 动态字段 -->
          <t-form-item
            v-for="field in dynamicFields"
            :key="field.key"
            :label="field.label"
          >
            <t-input
              v-if="field.type === 'password'"
              v-model="installForm[field.key]"
              type="password"
            >
              <template #suffix>
                <t-link theme="primary" @click="installForm[field.key] = randomStr(12)">
                  <i class="mdi mdi-shuffle-variant"></i>
                </t-link>
              </template>
            </t-input>
            <t-input-number
              v-else-if="field.type === 'port'"
              v-model="installForm[field.key]"
              :min="1"
              :max="65535"
            >
              <template #suffix>
                <t-link theme="primary" @click="installForm[field.key] = randomPort()">
                  <i class="mdi mdi-shuffle-variant"></i>
                </t-link>
              </template>
            </t-input-number>
            <t-textarea
              v-else-if="field.type === 'textarea'"
              v-model="installForm[field.key]"
              :autosize="{ minRows: 2, maxRows: 4 }"
            />
            <t-checkbox v-else-if="field.type === 'checkbox'" v-model="installForm[field.key]">启用</t-checkbox>
            <t-input v-else v-model="installForm[field.key]" />
          </t-form-item>
        </t-form>
      </div>
    </t-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, computed, watch, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { MessagePlugin } from 'tdesign-vue-next'
import { listApp, createApp, getMyContainer } from '@/docker/api/docker'

const router = useRouter()
const boot = window.__TD_BOOT__ || {}
const dockerUser = boot.dockerUser || {}

const loading = ref(false)
const appList = ref([])
const keyword = ref('')
const hasContainer = ref(false)

// 安装弹窗
const installVisible = ref(false)
const installing = ref(false)
const currentApp = ref(null)
const installForm = reactive({})
const dynamicFields = ref([])

const cpuMax = Number(dockerUser.cpu_max || 1)
const memMax = Number(dockerUser.mem_max || 512)

const filteredApps = computed(() => {
  const q = keyword.value.toLowerCase().trim()
  if (!q) return appList.value
  return appList.value.filter((a) => {
    const t = `${a.appname || ''} ${a.apptitle || ''} ${a.apptype || ''}`.toLowerCase()
    return t.includes(q)
  })
})

// 分页
const page = ref(1)
const pageSize = 12
const pagedApps = computed(() => {
  const start = (page.value - 1) * pageSize
  return filteredApps.value.slice(start, start + pageSize)
})

watch(keyword, () => {
  page.value = 1
})

const versionOptions = computed(() => {
  if (!currentApp.value) return []
  const opts = []
  ;(currentApp.value.appversion || []).forEach((v) => {
    const subs = v.s_version || []
    if (Array.isArray(subs) && subs.length) {
      subs.forEach((sv) => {
        opts.push({ value: `${v.m_version}|${sv}`, label: `${v.m_version}.${sv}` })
      })
    } else {
      opts.push({ value: `${v.m_version}|`, label: v.m_version })
    }
  })
  return opts
})

function appIconChar(name) {
  if (!name) return '?'
  return String(name).charAt(0).toUpperCase()
}
function typeLabel(t) {
  const map = { BuildWebsite: '建站', Database: '数据库', Storage: '存储', DevTool: '开发工具', Media: '媒体', Other: '其他' }
  return map[t] || t || '应用'
}
function versionSummary(versions) {
  if (!Array.isArray(versions) || !versions.length) return '-'
  return versions.map((v) => v.m_version).join(' / ')
}
function dependText(deps) {
  return deps.map((d) => (d.appname || []).join(',')).join('、')
}
function randomStr(len) {
  const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789'
  let s = ''
  for (let i = 0; i < (len || 12); i++) s += chars.charAt(Math.floor(Math.random() * chars.length))
  return s
}
function randomUser(len) {
  const chars = 'abcdefghijkmnpqrstuvwxyz23456789'
  let s = ''
  for (let i = 0; i < (len || 10); i++) s += chars.charAt(Math.floor(Math.random() * chars.length))
  return s
}
function randomPort() {
  return Math.floor(Math.random() * 50000) + 10000
}

function buildDynamicFields(app) {
  const fields = []
  const seen = { cpus: 1, memory_limit: 1, allow_access: 1, dk_version: 1, version: 1, app_path: 1, host_ip: 1 }
  ;(app.env || []).forEach((e) => {
    const k = e.key
    if (!k || seen[k]) return
    seen[k] = 1
    fields.push(buildField(k, e.default || '', e.desc, e.type))
  })
  ;(app.field || []).forEach((f) => {
    const k = f.attr
    if (!k || seen[k]) return
    seen[k] = 1
    fields.push(buildField(k, f.default || '', f.name, f.type))
  })
  return fields
}

function buildField(key, def, desc, type) {
  const label = desc || key.replace(/_/g, ' ')
  const t = type || 'string'
  const isPwd = t === 'password' || t === 'secret' || /password|secret|passwd|pwd/i.test(key)
  const isUser = /user|username|admin/i.test(key) && !/host|ip|email|domain/i.test(key)
  const isPort = t === 'port' || /port/i.test(key)

  let fieldType = 'string'
  let defaultVal = def

  if (def === 'random') {
    defaultVal = randomStr(12)
    fieldType = 'password'
  } else if (isPwd && (def === '' || def == null)) {
    defaultVal = randomStr(12)
    fieldType = 'password'
  } else if (isUser && (def === '' || def == null)) {
    defaultVal = randomUser(10)
  } else if (isPort && (def === '' || def == null || def === '0')) {
    defaultVal = randomPort()
    fieldType = 'port'
  } else if (t === 'checkbox') {
    fieldType = 'checkbox'
    defaultVal = def === true || def === 'true' || def === '1'
  } else if (t === 'textarea') {
    fieldType = 'textarea'
  } else if (t === 'port' || t === 'number') {
    fieldType = 'port'
  } else if (isPwd) {
    fieldType = 'password'
  }

  return { key, label, type: fieldType, default: defaultVal }
}

function openInstall(app) {
  if (hasContainer.value) {
    MessagePlugin.warning('您已创建容器，无法再次创建')
    return
  }
  currentApp.value = app
  dynamicFields.value = buildDynamicFields(app)

  // 重置表单
  Object.keys(installForm).forEach((k) => delete installForm[k])
  installForm.cpus = 0
  installForm.memory_limit = 0
  installForm.allow_access = true

  // 设置版本默认值
  const opts = versionOptions.value
  if (opts.length) installForm.dk_version = opts[0].value

  // 设置动态字段默认值
  dynamicFields.value.forEach((f) => {
    installForm[f.key] = f.default
  })

  installVisible.value = true
}

async function doInstall() {
  if (!currentApp.value) return
  if (!installForm.dk_version) {
    MessagePlugin.warning('请选择版本')
    return
  }
  installing.value = true

  const [mVersion, sVersion] = installForm.dk_version.split('|')
  const params = {
    app_name: currentApp.value.appname,
    m_version: mVersion || '',
    s_version: sVersion || '',
    cpus: String(Math.min(parseInt(installForm.cpus || 0, 10), cpuMax)),
    memory_limit: String(Math.min(parseFloat(installForm.memory_limit || 0), memMax)),
    allow_access: installForm.allow_access ? '1' : '0',
  }
  dynamicFields.value.forEach((f) => {
    const v = installForm[f.key]
    if (v !== undefined && v !== null) {
      params[f.key] = f.type === 'checkbox' ? (v ? '1' : '0') : String(v)
    }
  })

  const r = await createApp(params)
  installing.value = false
  if (r.ok) {
    installVisible.value = false
    MessagePlugin.success('创建请求已提交，正在初始化…')
    setTimeout(() => {
      window.location.href = './console.php'
    }, 800)
  }
}

async function loadApps() {
  loading.value = true
  const r = await listApp()
  loading.value = false
  if (r.ok && r.data) {
    const btResp = r.data.data || r.data
    const apps = btResp.data || btResp || []
    appList.value = Array.isArray(apps) ? apps : []
  }
}

async function checkContainer() {
  const r = await getMyContainer()
  if (r.ok && r.data?.me) {
    hasContainer.value = !!(r.data.me.container_id || r.data.me.service_name)
  }
}

onMounted(() => {
  checkContainer()
  loadApps()
})
</script>

<style scoped>
.dk-card-hd {
  display: flex;
  align-items: center;
  justify-content: space-between;
  width: 100%;
}
.dk-card-hd h3 {
  margin: 0;
  font-size: 16px;
  font-weight: 600;
}
.dk-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
  gap: 16px;
}
.dk-app-card {
  background: var(--td-bg-color-container, #fff);
  border: 1px solid var(--td-border-level-1-color, #e7e7e7);
  border-radius: 12px;
  padding: 20px;
  transition: box-shadow 0.2s, transform 0.12s, border-color 0.2s;
  display: flex;
  flex-direction: column;
}
.dk-app-card:hover {
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
  transform: translateY(-2px);
  border-color: var(--td-border-level-2-color, #d1d5db);
}
.dk-app-head {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 10px;
}
.dk-app-icon {
  width: 44px;
  height: 44px;
  border-radius: 10px;
  background: rgba(0, 82, 217, 0.08);
  color: var(--td-brand-color, #0052d9);
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 18px;
  font-weight: 600;
  flex-shrink: 0;
}
.dk-app-head h4 {
  margin: 0;
  font-size: 14.5px;
}
.dk-app-type {
  font-size: 11.5px;
  color: var(--td-text-color-secondary, #6b7280);
  margin-top: 2px;
}
.dk-app-desc {
  font-size: 12.5px;
  color: var(--td-text-color-secondary, #6b7280);
  margin: 6px 0 14px;
  min-height: 34px;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
.dk-app-foot {
  margin-top: auto;
  display: flex;
  align-items: center;
  justify-content: space-between;
}
.dk-app-name {
  font-size: 12px;
  color: var(--td-text-color-secondary, #6b7280);
}
.dk-mono {
  font-family: Consolas, Monaco, monospace;
}
.dk-empty {
  text-align: center;
  padding: 48px 20px;
  color: var(--td-text-color-secondary, #6b7280);
}
.dk-empty-ico {
  font-size: 40px;
  margin-bottom: 12px;
  opacity: 0.5;
}
.dk-empty p {
  margin: 0;
  font-size: 13px;
}
.dk-pager {
  display: flex;
  justify-content: center;
  margin-top: 20px;
}
.dk-install-head {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 16px;
  padding-bottom: 14px;
  border-bottom: 1px solid var(--td-border-level-1-color, #e7e7e7);
}
.dk-install-head .dk-app-desc {
  display: block;
  margin: 4px 0 0;
  min-height: 0;
  -webkit-line-clamp: unset;
  overflow: visible;
}
.dk-install-head h4 {
  margin: 0;
  font-size: 15px;
}
/* 安装表单控件撑满宽度 */
.dk-install-form :deep(.t-input-number) {
  width: 100%;
}
.dk-form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 14px;
}
</style>
