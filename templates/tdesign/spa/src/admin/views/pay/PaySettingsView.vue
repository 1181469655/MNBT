<template>
  <div class="td-page">
    <div class="td-page-head">
      <div>
        <h3 class="td-page-title"><i class="mdi mdi-credit-card-outline"></i>支付设置</h3>
        <p class="td-page-subtitle">配置可用的支付方式与客户端展示</p>
      </div>
      <t-button theme="primary" :loading="saving" :disabled="!paymentPlugins.length" @click="save">
        <i class="mdi mdi-content-save-outline"></i> 保存支付方式配置
      </t-button>
    </div>

    <div class="td-form-note pay-note">
      <b>使用流程:</b>
      ① 在插件管理中安装所需的支付插件 → ② 在本页勾选启用具体付款方式,填写客户端显示名 / 图标 / 排序 → ③ 点击「保存支付方式配置」。
      启用方式越少,结算页越简洁。
    </div>

    <div v-if="!paymentPlugins.length" class="td-empty td-set-card">
      <i class="mdi mdi-credit-card-off-outline"></i>
      暂无可用支付插件,请先在插件管理中安装
    </div>

    <div v-else>
      <div v-for="plugin in paymentPlugins" :key="plugin.plugin_id" class="td-set-card">
        <div class="td-set-card-hd">
          <div class="td-set-icon"><i class="mdi" :class="plugin.icon || 'mdi-credit-card'"></i></div>
          <div class="plugin-head-info">
            <h4>{{ plugin.name }}</h4>
            <p>
              <code class="td-code">{{ plugin.plugin_id }}</code>
              <span v-if="plugin.description"> · {{ plugin.description }}</span>
            </p>
          </div>
          <t-button
            v-if="pluginEntryUrl(plugin.plugin_id)"
            theme="default"
            variant="outline"
            size="small"
            @click="goPluginEntry(plugin.plugin_id)"
          >
            <i class="mdi mdi-cog"></i> 插件设置
          </t-button>
        </div>
        <div class="td-set-card-bd">
          <t-table
            row-key="method_id"
            :data="methodRows(plugin)"
            :columns="methodColumns"
            stripe
            bordered
            :pagination="false"
          >
            <template #enabled="{ row }">
              <t-switch
                :value="!!enabledMap[pluginMethodKey(plugin.plugin_id, row.method_id)]"
                @change="(v) => onToggle(plugin.plugin_id, row.method_id, v)"
              />
            </template>

            <template #display_name="{ row }">
              <t-input
                :value="getDisplayName(plugin.plugin_id, row.method_id)"
                @input="(v) => setDisplayName(plugin.plugin_id, row.method_id, v)"
                placeholder="留空则使用默认名称"
                size="small"
              />
            </template>

            <template #icon="{ row }">
              <t-input
                :value="getIcon(plugin.plugin_id, row.method_id)"
                @input="(v) => setIcon(plugin.plugin_id, row.method_id, v)"
                :placeholder="row.icon || 'mdi-credit-card'"
                size="small"
              />
            </template>

            <template #sort="{ row }">
              <t-input-number
                :value="getSort(plugin.plugin_id, row.method_id)"
                @change="(v) => setSort(plugin.plugin_id, row.method_id, v)"
                :min="0"
                theme="normal"
                size="small"
              />
            </template>
          </t-table>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { MessagePlugin } from 'tdesign-vue-next'
import { savePaymentMethods } from '@/admin/api/pay'

const router = useRouter()

const boot = window.__TD_BOOT__ || {}

const paymentPlugins = computed(() => {
  const raw = boot.paymentPlugins
  const arr = Array.isArray(raw) ? raw : raw ? Object.values(raw) : []
  // 兼容旧数据:若元素是对象但没有 plugin_id,尝试用对象的 key 补充
  return arr.map((item) => {
    if (!item || typeof item !== 'object') return item
    if (!item.plugin_id && item.slug) item.plugin_id = item.slug
    return item
  })
})

const settingsTabs = computed(() => {
  const arr = boot.pluginSettingsTabs
  return Array.isArray(arr) ? arr : []
})

const saving = ref(false)

/**
 * enabledMap: { 'plugin__method': { display_name, icon, sort } }
 */
const enabledMap = reactive({})
const initialEnabled = reactive({})

function pluginMethodKey(plugin, method) {
  return plugin + '__' + method
}

function methodRows(plugin) {
  if (!plugin.methods) return []
  return Object.keys(plugin.methods).map((mid) => ({
    method_id: mid,
    name: plugin.methods[mid].name,
    icon: plugin.methods[mid].icon || '',
  }))
}

const methodColumns = [
  { colKey: 'enabled', title: '启用', width: 80 },
  { colKey: 'name', title: '子付款方式', minWidth: 160, cell: (h, { row }) => row.name },
  { colKey: 'method_id', title: '代码', width: 160, cell: (h, { row }) => row.method_id },
  { colKey: 'display_name', title: '客户端显示名', width: 200 },
  { colKey: 'icon', title: '图标 class', width: 200 },
  { colKey: 'sort', title: '排序', width: 120 },
]

function onToggle(plugin, method, v) {
  const key = pluginMethodKey(plugin, method)
  if (v) {
    if (!enabledMap[key]) {
      enabledMap[key] = { display_name: '', icon: '', sort: 0 }
    }
  } else {
    delete enabledMap[key]
  }
}

function getDisplayName(plugin, method) {
  const key = pluginMethodKey(plugin, method)
  return enabledMap[key]?.display_name || ''
}

function setDisplayName(plugin, method, v) {
  const key = pluginMethodKey(plugin, method)
  if (!enabledMap[key]) enabledMap[key] = { display_name: '', icon: '', sort: 0 }
  enabledMap[key].display_name = v
}

function getIcon(plugin, method) {
  const key = pluginMethodKey(plugin, method)
  return enabledMap[key]?.icon || ''
}

function setIcon(plugin, method, v) {
  const key = pluginMethodKey(plugin, method)
  if (!enabledMap[key]) enabledMap[key] = { display_name: '', icon: '', sort: 0 }
  enabledMap[key].icon = v
}

function getSort(plugin, method) {
  const key = pluginMethodKey(plugin, method)
  return enabledMap[key]?.sort ?? 0
}

function setSort(plugin, method, v) {
  const key = pluginMethodKey(plugin, method)
  if (!enabledMap[key]) enabledMap[key] = { display_name: '', icon: '', sort: 0 }
  enabledMap[key].sort = v || 0
}

function pluginEntryUrl(pluginId) {
  const tab = settingsTabs.value.find((t) => t.plugin === pluginId)
  return tab ? tab.url : ''
}

function goPluginEntry(pluginId) {
  const url = pluginEntryUrl(pluginId)
  if (url) router.push(url)
}

function initEnabled() {
  const arr = boot.enabledPayments
  if (!Array.isArray(arr)) return
  arr.forEach((item) => {
    if (!item || !item.plugin || !item.method) return
    const key = pluginMethodKey(item.plugin, item.method)
    enabledMap[key] = {
      display_name: item.display_name || '',
      icon: item.icon || '',
      sort: Number(item.sort) || 0,
    }
    initialEnabled[key] = { ...enabledMap[key] }
  })
}

async function save() {
  const rows = []
  Object.keys(enabledMap).forEach((key) => {
    const [plugin, method] = key.split('__')
    if (!plugin || !method) return
    const conf = enabledMap[key]
    rows.push({
      plugin,
      method,
      display_name: conf.display_name || '',
      icon: conf.icon || '',
      sort: conf.sort || 0,
    })
  })

  saving.value = true
  const r = await savePaymentMethods(JSON.stringify(rows))
  saving.value = false
  if (r.ok) MessagePlugin.success('保存成功')
}

onMounted(initEnabled)
</script>

<style scoped>
.pay-note {
  margin-top: 0;
  margin-bottom: 14px;
}
.plugin-head-info {
  flex: 1;
  min-width: 0;
}
.plugin-head-info p {
  display: flex;
  align-items: center;
  gap: 4px;
  flex-wrap: wrap;
}
</style>
