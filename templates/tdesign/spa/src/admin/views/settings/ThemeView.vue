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

            <div v-if="dockerThemes.length" class="td-form-row">
              <label>Docker 控制台主题</label>
              <t-select v-model="form.dockertheme" placeholder="请选择 Docker 控制台主题">
                <t-option
                  v-for="t in dockerThemes"
                  :key="t.name"
                  :value="t.name"
                  :label="`${t.title}${t.version ? ' v' + t.version : ''} (${t.name})`"
                />
              </t-select>
              <div class="td-form-hint">
                当前: <code>{{ curDockerTheme || '-' }}</code> · 缺页回退 default
              </div>
            </div>

            <div v-if="homeThemes.length" class="td-form-row">
              <label>主页主题</label>
              <t-select v-model="form.hometheme" placeholder="请选择主页主题">
                <t-option
                  v-for="t in homeThemes"
                  :key="t.name"
                  :value="t.name"
                  :label="`${t.title}${t.version ? ' v' + t.version : ''} (${t.name})`"
                />
              </t-select>
              <div class="td-form-hint">
                当前: <code>{{ curHomeTheme || '-' }}</code> · 站点根路径 / 的落地页皮肤 · templates/主题/home/
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
          <template #has_docker="{ row }">
            <t-tag v-if="row.has_docker" size="small" theme="success" variant="light">支持</t-tag>
            <span v-else class="td-text-mute">—</span>
          </template>
          <template #has_home="{ row }">
            <t-tag v-if="row.has_home" size="small" theme="success" variant="light">支持</t-tag>
            <span v-else class="td-text-mute">—</span>
          </template>
          <template #active="{ row }">
            <div class="active-tags">
              <t-tag v-if="row.name === curUserTheme" size="small" theme="warning" variant="light">用户端</t-tag>
              <t-tag v-if="row.name === curAdminTheme" size="small" theme="warning" variant="light">管理端</t-tag>
              <t-tag v-if="row.name === curDockerTheme" size="small" theme="warning" variant="light">Docker</t-tag>
              <t-tag v-if="row.name === curHomeTheme" size="small" theme="warning" variant="light">主页</t-tag>
              <span v-if="row.name !== curUserTheme && row.name !== curAdminTheme && row.name !== curDockerTheme && row.name !== curHomeTheme" class="td-text-mute">—</span>
            </div>
          </template>
        </t-table>
      </div>
    </div>

    <div class="td-set-card">
      <div class="td-set-card-hd">
        <div class="td-set-icon"><i class="mdi mdi-home-city-outline"></i></div>
        <div>
          <h4>主页内容</h4>
          <p>内置主页落地页配置，模板跟随上方所选主页主题（templates/主题/home/）</p>
        </div>
      </div>
      <div class="td-set-card-bd">
        <t-loading :loading="homeLoading" text="保存中…" size="small">
          <div class="td-form">
            <div class="td-form-switch">
              <div class="td-form-switch-txt">
                <strong>启用内置主页</strong>
                <span>关闭后恢复旧行为：插件接管或跳转用户面板</span>
              </div>
              <t-switch v-model="homeForm.home_enable" />
            </div>

            <div class="td-form-row">
              <label>站点标题</label>
              <t-input v-model="homeForm.home_title" placeholder="留空使用系统名称" clearable />
            </div>

            <div class="td-form-row">
              <label>Hero 标语</label>
              <t-input v-model="homeForm.home_hero" placeholder="高性能虚拟主机，即买即用" clearable />
              <div class="td-form-hint">主页首屏大标题</div>
            </div>

            <div class="td-form-row">
              <label>主色调</label>
              <div class="home-color-row">
                <input type="color" class="home-color-picker" v-model="homeForm.home_primary" />
                <t-input v-model="homeForm.home_primary" style="max-width: 140px" />
              </div>
              <div class="td-form-hint">十六进制色值（#rrggbb），应用到主页按钮与强调元素</div>
            </div>

            <div class="td-form-row">
              <label>站点 Logo</label>
              <div class="home-upload-row">
                <img v-if="logoPreview" :src="logoPreview" alt="logo" class="home-logo-preview" />
                <span v-else class="home-logo-empty">未设置</span>
                <input
                  ref="logoInput"
                  type="file"
                  accept=".png,.jpg,.jpeg,.gif,.ico"
                  style="display: none"
                  @change="onUpload('logo')"
                />
                <t-button variant="outline" @click="logoInput.click()"><i class="mdi mdi-upload"></i> 上传</t-button>
                <t-button variant="outline" theme="danger" @click="homeForm.home_logo = ''">清除</t-button>
              </div>
              <t-input v-model="homeForm.home_logo" placeholder="上传后自动填入，或手动填写 URL" clearable />
              <div class="td-form-hint">留空使用系统控制面板 Logo（imsetes/upload_logo/logo.index.png）</div>
            </div>

            <div class="td-form-row">
              <label>Favicon</label>
              <div class="home-upload-row">
                <input
                  ref="faviconInput"
                  type="file"
                  accept=".png,.jpg,.jpeg,.gif,.ico"
                  style="display: none"
                  @change="onUpload('favicon')"
                />
                <t-button variant="outline" @click="faviconInput.click()"><i class="mdi mdi-upload"></i> 上传</t-button>
                <t-button variant="outline" theme="danger" @click="homeForm.home_favicon = ''">清除</t-button>
              </div>
              <t-input v-model="homeForm.home_favicon" placeholder="上传后自动填入，或手动填写 URL" clearable />
              <div class="td-form-hint">留空使用系统默认图标</div>
            </div>

            <div class="td-form-row">
              <label>底部版权</label>
              <t-input v-model="homeForm.home_footer" placeholder="留空使用系统版权（hxp）" clearable />
            </div>

            <div class="td-form-switch">
              <div class="td-form-switch-txt">
                <strong>显示公告区</strong>
                <span>展示系统网站公告（MN_config.gg）</span>
              </div>
              <t-switch v-model="homeForm.home_show_notice" />
            </div>

            <div class="td-form-switch">
              <div class="td-form-switch-txt">
                <strong>显示套餐区</strong>
                <span>hosting_shop 启用且存在有效套餐时展示</span>
              </div>
              <t-switch v-model="homeForm.home_show_plans" />
            </div>

            <div v-if="themeFields.length" class="td-form-note" style="padding-top:8px;border-top:1px solid #e5e7eb;margin-bottom:8px;">
              <b>主题自定义设置</b>（当前主页主题注册的扩展项）
            </div>
            <template v-for="f in themeFields" :key="f.key">
              <!-- text / number -->
              <div v-if="f.type === 'text' || f.type === 'number'" class="td-form-row">
                <label>{{ f.label }}</label>
                <t-input v-model="themeValues[f.key]" :type="f.type" :placeholder="f.placeholder || ''" clearable />
                <div v-if="f.hint" class="td-form-hint">{{ f.hint }}</div>
              </div>
              <!-- color -->
              <div v-else-if="f.type === 'color'" class="td-form-row">
                <label>{{ f.label }}</label>
                <div class="home-color-row">
                  <input type="color" class="home-color-picker" v-model="themeValues[f.key]" />
                  <t-input v-model="themeValues[f.key]" style="max-width: 140px" :placeholder="f.placeholder || ''" />
                </div>
                <div v-if="f.hint" class="td-form-hint">{{ f.hint }}</div>
              </div>
              <!-- switch -->
              <div v-else-if="f.type === 'switch'" class="td-form-switch">
                <div class="td-form-switch-txt">
                  <strong>{{ f.label }}</strong>
                  <span v-if="f.hint">{{ f.hint }}</span>
                </div>
                <t-switch v-model="themeValues[f.key]" />
              </div>
              <!-- select -->
              <div v-else-if="f.type === 'select'" class="td-form-row">
                <label>{{ f.label }}</label>
                <t-select v-model="themeValues[f.key]" :placeholder="f.placeholder || ''" clearable>
                  <t-option v-for="opt in (f.options || [])" :key="opt.value" :value="opt.value" :label="opt.label" />
                </t-select>
                <div v-if="f.hint" class="td-form-hint">{{ f.hint }}</div>
              </div>
              <!-- textarea -->
              <div v-else-if="f.type === 'textarea'" class="td-form-row">
                <label>{{ f.label }}</label>
                <t-textarea v-model="themeValues[f.key]" :autosize="{ minRows: 3, maxRows: 8 }" :placeholder="f.placeholder || ''" />
                <div v-if="f.hint" class="td-form-hint">{{ f.hint }}</div>
              </div>
              <!-- image -->
              <div v-else-if="f.type === 'image'" class="td-form-row">
                <label>{{ f.label }}</label>
                <div class="home-upload-row">
                  <img v-if="themeImagePreview(f.key)" :src="themeImagePreview(f.key)" alt="" class="home-image-preview" />
                  <span v-else class="home-logo-empty">未设置</span>
                  <input
                    :ref="(el) => setImageInput(f.key, el)"
                    type="file"
                    accept=".png,.jpg,.jpeg,.gif,.webp,.ico"
                    style="display: none"
                    @change="onUploadThemeImage(f.key)"
                  />
                  <t-button variant="outline" @click="triggerImageUpload(f.key)"><i class="mdi mdi-upload"></i> 上传</t-button>
                  <t-button variant="outline" theme="danger" @click="themeValues[f.key] = ''">清除</t-button>
                </div>
                <t-input v-model="themeValues[f.key]" placeholder="上传后自动填入，或手动填写 URL" clearable />
                <div v-if="f.hint" class="td-form-hint">{{ f.hint }}</div>
              </div>
            </template>

            <div class="td-form-actions">
              <t-button theme="primary" :loading="homeLoading" @click="saveHome">
                <i class="mdi mdi-content-save-outline"></i> 保存主页内容
              </t-button>
            </div>

            <div class="td-form-note">
              <b>提示:</b> 主页模板位于 <code>templates/当前主页主题/home/index.php</code>，缺页回退
              <code>templates/default/home/index.php</code>。插件可通过 <code>home.blocks</code> 过滤器注入扩展区块；
              启用 shop_frontend 等插件时，插件主页优先接管。
            </div>
          </div>
        </t-loading>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref, computed } from 'vue'
import { MessagePlugin } from 'tdesign-vue-next'
import { setTheme, setHome, uploadHomeIcon, uploadHomeImage } from '@/admin/api/settings'

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
const curDockerTheme = boot.curDockerTheme || ''
const curHomeTheme = boot.curHomeTheme || ''
const loading = ref(false)

const userThemes = computed(() => themeList.filter((t) => t.has_user))
const adminThemes = computed(() => themeList.filter((t) => t.has_admin))
const dockerThemes = computed(() => themeList.filter((t) => t.has_docker))
const homeThemes = computed(() => themeList.filter((t) => t.has_home))

const form = reactive({
  usertheme: curUserTheme || (userThemes.value[0] && userThemes.value[0].name) || '',
  admintheme: curAdminTheme || (adminThemes.value[0] && adminThemes.value[0].name) || '',
  dockertheme: curDockerTheme || (dockerThemes.value[0] && dockerThemes.value[0].name) || '',
  hometheme: curHomeTheme || (homeThemes.value[0] && homeThemes.value[0].name) || '',
})

const columns = [
  { colKey: 'name', title: '目录', width: 160, ellipsis: true },
  { colKey: 'title', title: '名称', minWidth: 140, ellipsis: true },
  { colKey: 'version', title: '版本', width: 100 },
  { colKey: 'has_user', title: '用户端', width: 90, align: 'center' },
  { colKey: 'has_admin', title: '管理端', width: 90, align: 'center' },
  { colKey: 'has_docker', title: 'Docker', width: 85, align: 'center' },
  { colKey: 'has_home', title: '主页', width: 75, align: 'center' },
  { colKey: 'active', title: '当前启用', width: 200, align: 'center' },
  { colKey: 'description', title: '说明', minWidth: 200, ellipsis: true },
]

async function save() {
  if (!form.usertheme || !form.admintheme) {
    MessagePlugin.warning('请选择用户端和管理端主题')
    return
  }
  loading.value = true
  const r = await setTheme(form.usertheme, form.admintheme, form.dockertheme || '', form.hometheme || '')
  loading.value = false
  if (r.ok) {
    MessagePlugin.success('保存成功,建议刷新页面查看效果')
  }
}

/* ===== 主页内容（V1.84 独立主页系统） ===== */
const conf = boot.conf || {}
const homeLoading = ref(false)

// 主题注册字段定义
const themeFields = (boot.homeThemeSettingsFields && Array.isArray(boot.homeThemeSettingsFields))
  ? boot.homeThemeSettingsFields
  : []

// 响应式值映射
const themeValues = reactive(
  themeFields.reduce((acc, f) => {
    acc[f.key] = f.value !== undefined ? f.value : f.default
    return acc
  }, {})
)

const logoInput = ref(null)
const faviconInput = ref(null)

/** 相对资源转可预览 URL（admin 目录下 ../imsetes/...） */
function resolveAsset(v) {
  if (!v) return ''
  if (/^https?:\/\//i.test(v) || v.startsWith('/')) return v
  if (v.startsWith('imsetes/')) return '../' + v
  return v
}

const homeForm = reactive({
  home_enable: conf.home_enable !== 'false',
  home_title: conf.home_title || '',
  home_hero: conf.home_hero || '',
  home_primary: /^#[0-9a-fA-F]{6}$/.test(conf.home_primary || '')
    ? conf.home_primary
    : '#4f46e5',
  home_logo: conf.home_logo || '',
  home_favicon: conf.home_favicon || '',
  home_footer: conf.home_footer || '',
  home_show_notice: conf.home_show_notice !== 'false',
  home_show_plans: conf.home_show_plans !== 'false',
})

const logoPreview = computed(() => resolveAsset(homeForm.home_logo))

/* ===== 主题 image 类型字段上传 ===== */
const imageInputs = {}
function setImageInput(key, el) {
  if (el) imageInputs[key] = el
}
function triggerImageUpload(key) {
  imageInputs[key]?.click()
}
function themeImagePreview(key) {
  return resolveAsset(themeValues[key] || '')
}
async function onUploadThemeImage(key) {
  const input = imageInputs[key]
  if (!input?.files?.length) return
  const file = input.files[0]
  homeLoading.value = true
  const r = await uploadHomeImage(key, file)
  input.value = ''
  if (r.ok) {
    themeValues[key] = 'imsetes/upload_logo/home_' + key + '.png'
    MessagePlugin.success('上传成功，请保存设置')
  }
  homeLoading.value = false
}

async function onUpload(target) {
  const input = target === 'logo' ? logoInput.value : faviconInput.value
  if (!input?.files?.length) return
  const file = input.files[0]
  homeLoading.value = true
  const r = await uploadHomeIcon(target, file)
  input.value = ''
  if (r.ok) {
    const rel = target === 'logo' ? 'imsetes/upload_logo/home_logo.png' : 'imsetes/upload_logo/home_favicon.ico'
    if (target === 'logo') homeForm.home_logo = rel
    else homeForm.home_favicon = rel
    MessagePlugin.success('上传成功，请保存设置')
  }
  homeLoading.value = false
}

async function saveHome() {
  if (!/^#[0-9a-fA-F]{6}$/.test(homeForm.home_primary)) {
    MessagePlugin.warning('主色调必须是 #rrggbb 格式')
    return
  }
  homeLoading.value = true
  const data = {
    home_enable: homeForm.home_enable ? 'true' : 'false',
    home_title: homeForm.home_title,
    home_hero: homeForm.home_hero,
    home_primary: homeForm.home_primary,
    home_logo: homeForm.home_logo,
    home_favicon: homeForm.home_favicon,
    home_footer: homeForm.home_footer,
    home_show_notice: homeForm.home_show_notice ? 'true' : 'false',
    home_show_plans: homeForm.home_show_plans ? 'true' : 'false',
  }
  // 主题自定义字段
  themeFields.forEach(f => {
    data['home_ts_' + f.key] = f.type === 'switch' ? (themeValues[f.key] ? 'true' : 'false') : String(themeValues[f.key] || '')
  })
  const r = await setHome(data)
  homeLoading.value = false
  if (r.ok) MessagePlugin.success('保存成功，刷新前台页面查看效果')
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
.home-color-row {
  display: flex;
  align-items: center;
  gap: 10px;
}
.home-color-picker {
  width: 46px;
  height: 34px;
  padding: 2px;
  border: 1px solid #d9d9d9;
  border-radius: 6px;
  background: #fff;
  cursor: pointer;
}
.home-upload-row {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 8px;
}
.home-logo-preview {
  width: 36px;
  height: 36px;
  object-fit: contain;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  background: #fff;
}
.home-logo-empty {
  color: #9ca3af;
  font-size: 13px;
  border: 1px dashed #e5e7eb;
  border-radius: 8px;
  padding: 6px 12px;
}
.home-image-preview {
  width: 140px;
  height: 88px;
  object-fit: cover;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  background: #f8f9fa;
}
</style>
