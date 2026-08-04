<template>
  <div class="td-page">
    <div class="td-page-head">
      <div>
        <h3 class="td-page-title"><i class="mdi mdi-puzzle"></i>插件管理</h3>
        <p class="td-page-subtitle">查看、安装、启用与卸载系统插件</p>
      </div>
      <t-button theme="default" variant="outline" @click="load">
        <i class="mdi mdi-refresh"></i> 刷新
      </t-button>
    </div>

    <!-- 已启用插件设置入口 -->
    <div v-if="settingsTabs.length" class="td-set-card">
      <div class="td-set-card-hd">
        <div>
          <h4>已启用插件设置入口</h4>
          <p>点击进入对应插件的设置页面</p>
        </div>
      </div>
      <div class="td-set-card-bd">
        <div class="entry-row">
          <t-link
            v-for="tab in settingsTabs"
            :key="tab.plugin"
            theme="primary"
            hover="color"
            class="entry-item"
            @click="goEntry(tab)"
          >
            {{ tab.title }}
          </t-link>
        </div>
      </div>
    </div>

    <div class="td-table-wrap">
      <t-table
        row-key="slug"
        :data="rows"
        :columns="columns"
        :loading="loading"
        stripe
        bordered
      >
        <template #slug="{ row }">
          <code class="td-code">{{ row.slug }}</code>
        </template>

        <template #status="{ row }">
          <span class="td-chip td-chip-success" v-if="row.enabled">
            <i class="mdi mdi-check-circle"></i> 已启用
          </span>
          <span class="td-chip td-chip-default" v-else-if="row.installed">
            <i class="mdi mdi-pause-circle"></i> 已安装
          </span>
          <span class="td-chip td-chip-warning" v-else>
            <i class="mdi mdi-download"></i> 未安装
          </span>
        </template>

        <template #description="{ row }">
          <span class="td-text-mute td-text-sm">{{ row.description || '-' }}</span>
        </template>

        <template #op="{ row }">
          <div class="td-row-actions">
            <t-button
              v-if="!row.installed"
              theme="primary"
              variant="text"
              size="small"
              :loading="busySlug === row.slug"
              @click="install(row)"
            >
              <i class="mdi mdi-download"></i> 安装
            </t-button>
            <t-button
              v-else-if="!row.enabled"
              theme="primary"
              variant="text"
              size="small"
              :loading="busySlug === row.slug"
              @click="enable(row)"
            >
              <i class="mdi mdi-play"></i> 启用
            </t-button>
            <t-button
              v-else
              theme="warning"
              variant="text"
              size="small"
              :loading="busySlug === row.slug"
              @click="disable(row)"
            >
              <i class="mdi mdi-pause"></i> 禁用
            </t-button>
            <t-button
              v-if="row.installed"
              theme="danger"
              variant="text"
              size="small"
              :loading="busySlug === row.slug"
              @click="uninstall(row)"
            >
              <i class="mdi mdi-delete"></i> 卸载
            </t-button>
          </div>
        </template>
      </t-table>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { MessagePlugin, DialogPlugin } from 'tdesign-vue-next'
import {
  listPlugin,
  setPluginEnabled,
  installPlugin,
  uninstallPlugin,
} from '@/admin/api/plugin'

const router = useRouter()

const loading = ref(false)
const rows = ref([])
const busySlug = ref('')

const boot = window.__TD_BOOT__ || {}
const settingsTabs = computed(() => {
  const tabs = boot.pluginSettingsTabs
  return Array.isArray(tabs) ? tabs : []
})

const columns = [
  { colKey: 'slug', title: '标识', width: 180, col: 'slug' },
  { colKey: 'name', title: '名称', minWidth: 140 },
  { colKey: 'version', title: '版本', width: 100 },
  { colKey: 'author', title: '作者', width: 140 },
  { colKey: 'status', title: '状态', width: 110, col: 'status' },
  { colKey: 'description', title: '说明', minWidth: 200, col: 'description' },
  { colKey: 'op', title: '操作', width: 200, fixed: 'right' },
]

function goEntry(tab) {
  if (tab.url) {
    router.push(tab.url)
  }
}

async function load() {
  loading.value = true
  const r = await listPlugin()
  loading.value = false
  if (r.ok && r.data) {
    rows.value = r.data.rows || []
  }
}

async function install(row) {
  busySlug.value = row.slug
  const r = await installPlugin(row.slug)
  busySlug.value = ''
  if (r.ok) {
    MessagePlugin.success('安装成功')
    load()
  }
}

async function enable(row) {
  busySlug.value = row.slug
  const r = await setPluginEnabled(row.slug, true)
  busySlug.value = ''
  if (r.ok) {
    MessagePlugin.success('已启用')
    load()
  }
}

async function disable(row) {
  busySlug.value = row.slug
  const r = await setPluginEnabled(row.slug, false)
  busySlug.value = ''
  if (r.ok) {
    MessagePlugin.success('已禁用')
    load()
  }
}

function uninstall(row) {
  const dialog = DialogPlugin.confirm({
    header: '卸载插件',
    body: `确定卸载插件「${row.name || row.slug}」吗?卸载后该插件的功能将不可用。`,
    confirmBtn: { content: '卸载', theme: 'danger' },
    onConfirm: async () => {
      busySlug.value = row.slug
      const r = await uninstallPlugin(row.slug)
      busySlug.value = ''
      dialog.destroy()
      if (r.ok) {
        MessagePlugin.success('已卸载')
        load()
      }
    },
    onClose: () => dialog.destroy(),
  })
}

onMounted(load)
</script>

<style scoped>
.entry-row {
  display: flex;
  flex-wrap: wrap;
  gap: 12px 18px;
}
.entry-item {
  font-size: 13px;
}
</style>
