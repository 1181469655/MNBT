<template>
  <div class="td-page td-theme-page">
    <div class="td-page-head">
      <div>
        <h3 class="td-page-title"><i class="mdi mdi-palette-outline"></i>前端模板设置</h3>
        <p class="td-page-subtitle">切换用户端 / 管理端主题皮肤</p>
      </div>
    </div>

    <div class="td-set-card">
      <div class="td-set-card-hd">
        <div class="td-set-icon"><i class="mdi mdi-palette-outline"></i></div>
        <div>
          <h4>前端模板</h4>
          <p>切换用户端 / 管理端主题皮肤</p>
        </div>
      </div>
      <div class="td-set-card-bd">
        <t-loading :loading="loading" text="保存中…" size="small">
          <div class="td-form">
            <div class="td-form-row">
              <label>用户端主题</label>
              <t-select v-model="form.usertheme" placeholder="请选择用户端主题">
                <t-option
                  v-for="t in userThemes"
                  :key="t.name"
                  :value="t.name"
                  :label="`${t.title}${t.version ? ' v' + t.version : ''} (${t.name})`"
                />
              </t-select>
              <div class="td-form-hint">
                当前: <code>{{ curUserTheme || '-' }}</code> · 目录 templates/
              </div>
            </div>

            <div class="td-form-row">
              <label>管理端主题</label>
              <t-select v-model="form.admintheme" placeholder="请选择管理端主题">
                <t-option
                  v-for="t in adminThemes"
                  :key="t.name"
                  :value="t.name"
                  :label="`${t.title}${t.version ? ' v' + t.version : ''} (${t.name})`"
                />
              </t-select>
              <div class="td-form-hint">
                当前: <code>{{ curAdminTheme || '-' }}</code> · 缺页回退 default
              </div>
            </div>

            <div class="td-form-actions">
              <t-button theme="primary" :loading="loading" @click="save">
                <i class="mdi mdi-content-save-outline"></i> 保存主题设置
              </t-button>
            </div>

            <div class="td-form-note">
              <b>提示:</b> 保存后用户端立即生效;管理端建议整页刷新。主题包放在
              <code>templates/主题名/</code> 下即可被扫描。
            </div>
          </div>
        </t-loading>
      </div>
    </div>

    <div class="td-set-card">
      <div class="td-set-card-hd">
        <div class="td-set-icon"><i class="mdi mdi-format-list-bulleted"></i></div>
        <div>
          <h4>已安装主题</h4>
          <p>共 {{ themeList.length }} 个</p>
        </div>
      </div>
      <div class="td-set-card-bd td-set-card-bd-table">
        <t-table
          row-key="name"
          :data="themeList"
          :columns="columns"
          :bordered="false"
          :hover="true"
          :stripe="false"
          size="small"
          :max-height="600"
          cell-empty-content="-"
        >
          <template #name="{ row }">
            <code class="theme-code">{{ row.name }}</code>
          </template>
          <template #version="{ row }">
            <t-tag v-if="row.version" size="small" theme="primary" variant="light">
              v{{ row.version }}
            </t-tag>
            <span v-else class="td-text-mute">-</span>
          </template>
          <template #has_user="{ row }">
            <t-tag v-if="row.has_user" size="small" theme="success" variant="light">支持</t-tag>
            <span v-else class="td-text-mute">—</span>
          </template>
          <template #has_admin="{ row }">
            <t-tag v-if="row.has_admin" size="small" theme="success" variant="light">支持</t-tag>
            <span v-else class="td-text-mute">—</span>
          </template>
          <template #active="{ row }">
            <div class="active-tags">
              <t-tag v-if="row.name === curUserTheme" size="small" theme="warning" variant="light">用户端</t-tag>
              <t-tag v-if="row.name === curAdminTheme" size="small" theme="warning" variant="light">管理端</t-tag>
              <span v-if="row.name !== curUserTheme && row.name !== curAdminTheme" class="td-text-mute">—</span>
            </div>
          </template>
        </t-table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref, computed } from 'vue'
import { MessagePlugin } from 'tdesign-vue-next'
import { setTheme } from '@/admin/api/settings'

const boot = window.__TD_BOOT__ || {}
// 防御:boot.themeList 可能是对象(关联数组 json_encode 后)或数组,统一转成数组
const rawThemeList = Array.isArray(boot.themeList)
  ? boot.themeList
  : boot.themeList
    ? Object.values(boot.themeList)
    : []
const themeList = rawThemeList
const curUserTheme = boot.curUserTheme || ''
const curAdminTheme = boot.curAdminTheme || ''
const loading = ref(false)

const userThemes = computed(() => themeList.filter((t) => t.has_user))
const adminThemes = computed(() => themeList.filter((t) => t.has_admin))

const form = reactive({
  usertheme: curUserTheme || (userThemes.value[0] && userThemes.value[0].name) || '',
  admintheme: curAdminTheme || (adminThemes.value[0] && adminThemes.value[0].name) || '',
})

const columns = [
  { colKey: 'name', title: '目录', width: 160, ellipsis: true },
  { colKey: 'title', title: '名称', minWidth: 140, ellipsis: true },
  { colKey: 'version', title: '版本', width: 100 },
  { colKey: 'has_user', title: '用户端', width: 90, align: 'center' },
  { colKey: 'has_admin', title: '管理端', width: 90, align: 'center' },
  { colKey: 'active', title: '当前启用', width: 160, align: 'center' },
  { colKey: 'description', title: '说明', minWidth: 200, ellipsis: true },
]

async function save() {
  if (!form.usertheme || !form.admintheme) {
    MessagePlugin.warning('请选择用户端和管理端主题')
    return
  }
  loading.value = true
  const r = await setTheme(form.usertheme, form.admintheme)
  loading.value = false
  if (r.ok) {
    MessagePlugin.success('保存成功,建议刷新页面查看效果')
  }
}
</script>

<style scoped>
.td-theme-page code,
.theme-code {
  background: #f3f3f3;
  padding: 1px 6px;
  border-radius: 4px;
  font-family: Consolas, Monaco, monospace;
  font-size: 12px;
  color: #d63384;
  word-break: break-all;
}
.td-set-card-bd-table {
  padding: 0;
}
.active-tags {
  display: inline-flex;
  gap: 4px;
  justify-content: center;
  flex-wrap: wrap;
}
</style>
