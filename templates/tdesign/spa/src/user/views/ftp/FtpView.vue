<template>
  <div class="td-page td-ftp">
    <div class="td-page-head">
      <div>
        <h3 class="td-page-title"><i class="mdi mdi-folder-multiple-outline"></i>在线文件管理</h3>
        <p class="td-page-subtitle">基于宝塔节点 API，支持断点续传与在线编辑</p>
      </div>
    </div>

    <div class="td-ftp-pathbar">
      <t-button theme="default" variant="outline" size="small" :disabled="path === '/'" title="返回上级" @click="go(parentPath)">
        <i class="mdi mdi-arrow-up"></i>
      </t-button>
      <t-button theme="default" variant="outline" size="small" @click="go('/')">
        <i class="mdi mdi-home-outline"></i> 根目录
      </t-button>
      <t-button theme="default" variant="outline" size="small" @click="reload">
        <i class="mdi mdi-refresh"></i> 刷新
      </t-button>
      <t-breadcrumb max-item-width="160" class="td-ftp-crumb">
        <t-breadcrumb-item @click="go('/')">根目录</t-breadcrumb-item>
        <t-breadcrumb-item
          v-for="seg in crumbs"
          :key="seg.path"
          @click="go(seg.path)"
        >{{ seg.name }}</t-breadcrumb-item>
      </t-breadcrumb>
    </div>

    <div class="td-table-wrap">
      <div class="td-toolbar td-ftp-toolbar">
        <t-button theme="primary" size="small" @click="openCreate('file')">
          <i class="mdi mdi-file-plus-outline"></i> 新建文件
        </t-button>
        <t-button theme="primary" size="small" @click="openCreate('dir')">
          <i class="mdi mdi-folder-plus-outline"></i> 新建文件夹
        </t-button>
        <t-button theme="primary" size="small" @click="openUpload">
          <i class="mdi mdi-cloud-upload-outline"></i> 上传
        </t-button>
        <span class="td-ftp-hint td-ftp-dragtip"><i class="mdi mdi-file-move-outline"></i> 可将文件直接拖入列表上传</span>
        <span class="td-toolbar-spacer"></span>
        <t-button v-show="clipboard" theme="success" size="small" @click="paste">
          <i class="mdi mdi-content-paste"></i> 粘贴（{{ clipboard ? clipboard.names.length : 0 }} 项）
        </t-button>
        <t-button v-show="selection.length > 0" theme="info" size="small" @click="openCompress">
          <i class="mdi mdi-zip-box-outline"></i> 压缩选中
        </t-button>
        <t-button v-show="selection.length > 0" theme="danger" size="small" @click="removeEntries(selection)">
          <i class="mdi mdi-window-close"></i> 删除选中
        </t-button>
      </div>

      <div v-if="dq.active" class="td-ftp-dragbar">
        <span class="td-ftp-dragbar-title">拖拽上传 {{ dq.idx + 1 }}/{{ dq.total }} · {{ dq.name }}</span>
        <t-progress :percentage="dq.percent" size="small" class="td-ftp-dragbar-bar" />
        <span class="td-ftp-hint">{{ dq.text }} · {{ dq.speed }} · 剩余 {{ dq.rest }}</span>
        <t-button theme="danger" variant="outline" size="small" @click="cancelDragUpload">取消</t-button>
      </div>

      <div
        class="td-ftp-dropzone"
        @dragenter.prevent="onDragEnter"
        @dragover.prevent="onDragOver"
        @dragleave="onDragLeave"
        @drop.prevent="onDrop"
      >
      <t-table
        row-key="name"
        :data="rows"
        :columns="columns"
        :loading="loading"
        :pagination="pagination"
        :selected-row-keys="selectedKeys"
        table-layout="auto"
        hover
        @page-change="onPageChange"
        @sort-change="onSortChange"
        @select-change="onSelectChange"
      >
        <template #empty>
          <div class="td-empty">
            <i class="mdi mdi-folder-open-outline"></i>
            当前目录为空
          </div>
        </template>
        <template #name="{ row }">
          <a v-if="row.type === 'dir'" class="td-ftp-enter" href="javascript:;" @click="enter(row)">
            <i class="mdi mdi-folder-open mdi-18px"></i>
            <span>{{ row.name }}</span>
          </a>
          <span v-else class="td-ftp-file">
            <i class="mdi" :class="[iconOf(row.name), 'mdi-18px']"></i>
            <span>{{ row.name }}</span>
          </span>
        </template>
        <template #size="{ row }">
          <a
            v-if="row.type === 'dir' && row.sizeShown !== true"
            href="javascript:;"
            class="td-ftp-calc"
            @click="calcSize(row)"
          >计算</a>
          <span v-else>{{ fmtSize(row.size) }}</span>
        </template>
        <template #mtime="{ row }">{{ fmtTime(row.mtime) }}</template>
        <template #operate="{ row }">
          <div class="td-row-actions">
            <t-button
              v-if="row.type === 'file' && isEditable(row.name)"
              theme="default" variant="outline" size="small" title="编辑"
              @click="openEditor(row)"
            ><i class="mdi mdi-pencil"></i></t-button>
            <t-button
              v-if="row.type === 'file'"
              theme="default" variant="outline" size="small" title="下载"
              @click="download(row)"
            ><i class="mdi mdi-cloud-download-outline"></i></t-button>
            <t-dropdown
              trigger="click"
              :options="moreOptions(row)"
              @click="onMoreClick($event, row)"
            >
              <t-button theme="default" variant="outline" size="small" title="更多操作">
                <i class="mdi mdi-dots-horizontal"></i>
              </t-button>
            </t-dropdown>
            <t-button theme="danger" variant="outline" size="small" title="删除" @click="removeEntries([row])">
              <i class="mdi mdi-window-close"></i>
            </t-button>
          </div>
        </template>
      </t-table>

        <div v-if="dropActive" class="td-ftp-drop-mask">
          <i class="mdi mdi-cloud-upload-outline mdi-36px"></i>
          <p>松开上传 {{ dropCount }} 个文件到 {{ path }}</p>
        </div>
      </div>
    </div>

    <!-- 上传 -->
    <t-dialog
      v-model:visible="uploadVisible"
      header="上传文件"
      :close-on-overlay-click="!uploading"
      :close-on-esc-keydown="!uploading"
      @closed="resetUpload"
    >
      <p class="td-ftp-hint">支持断点续传；同名文件将提示覆盖或重命名。</p>
      <div v-show="!uploading">
        <input ref="fileInputRef" type="file" class="td-ftp-file-input" @change="onFileChange" />
        <div v-if="uploadFile" class="td-ftp-filechip">
          <i class="mdi mdi-file-document-outline"></i>
          {{ uploadFile.name }}（{{ fmtSize(uploadFile.size) }}）
        </div>
      </div>
      <div v-show="uploading">
        <t-progress :percentage="uploadPercent" theme="line" :status="uploadPercent >= 100 ? 'success' : 'active'" />
        <p class="td-ftp-hint">已上传 {{ uploadText }} · 速度 {{ uploadSpeed }} · 剩余 {{ uploadRest }}</p>
        <t-button theme="danger" variant="outline" size="small" @click="cancelUpload">取消上传</t-button>
      </div>
      <template #footer>
        <t-button theme="default" :disabled="uploading" @click="uploadVisible = false">关闭</t-button>
        <t-button theme="primary" :disabled="!uploadFile || uploading" @click="startUpload">确认上传</t-button>
      </template>
    </t-dialog>

    <!-- 在线编辑 -->
    <t-dialog
      v-model:visible="editorVisible"
      :header="'编辑文件 [ ' + editorName + ' ]'"
      width="920px"
      top="6vh"
      @closed="closeEditor"
    >
      <div class="td-ftp-editor-head">
        <t-select v-model="editorTheme" size="small" class="td-ftp-theme-select" @change="onEditorTheme">
          <t-option v-for="t in EDITOR_THEMES" :key="t" :value="t" :label="t" />
        </t-select>
        <span class="td-ftp-hint">Ctrl+S 保存</span>
      </div>
      <div v-show="editorLoading" class="td-ftp-editor-loading">
        <t-loading text="正在加载编辑器…" size="small" />
      </div>
      <textarea ref="editorTextareaRef" class="td-ftp-editor-area"></textarea>
      <template #footer>
        <t-button theme="default" @click="editorVisible = false">关闭</t-button>
        <t-button theme="primary" :loading="saving" @click="saveEditor(false)">保存</t-button>
      </template>
    </t-dialog>

    <!-- 解压 -->
    <t-dialog v-model:visible="unzipVisible" header="解压文件" width="520px">
      <div class="td-form">
        <div class="td-form-row">
          <label>需解压的文件</label>
          <t-input :value="unzipPath" readonly />
        </div>
        <div class="td-form-row">
          <label>解压到</label>
          <t-input v-model="unzipDest" placeholder="站点相对路径，如 /" />
        </div>
        <div class="td-form-row">
          <label>解压密码</label>
          <t-input v-model="unzipPass" placeholder="没有密码请留空" />
        </div>
        <div class="td-form-row">
          <label>压缩包编码</label>
          <t-select v-model="unzipCoding">
            <t-option value="UTF-8" label="UTF-8" />
            <t-option value="GBK" label="GBK" />
          </t-select>
        </div>
      </div>
      <template #footer>
        <t-button theme="default" @click="unzipVisible = false">关闭</t-button>
        <t-button theme="primary" :loading="unzipLoading" @click="doUnzip">确认解压</t-button>
      </template>
    </t-dialog>

    <!-- 修改权限 -->
    <t-dialog v-model:visible="permVisible" header="修改权限" width="480px">
      <div class="td-form">
        <div class="td-form-row">
          <label>文件</label>
          <t-input :value="permPath" readonly />
        </div>
        <div class="td-form-row">
          <label>权限值（八进制）</label>
          <div class="td-ftp-perm-row">
            <t-input v-model="permVal" placeholder="如 644 / 755" />
            <t-button theme="default" variant="outline" @click="permVal = '644'">644</t-button>
            <t-button theme="default" variant="outline" @click="permVal = '755'">755</t-button>
          </div>
        </div>
      </div>
      <template #footer>
        <t-button theme="default" @click="permVisible = false">关闭</t-button>
        <t-button theme="primary" :loading="permLoading" @click="doPerm">确认修改</t-button>
      </template>
    </t-dialog>

    <!-- 图片预览 -->
    <t-dialog v-model:visible="imageVisible" :header="'图片预览 [ ' + imageName + ' ]'" width="720px" :footer="false">
      <div class="td-ftp-image">
        <img :src="imageUrl" :alt="imageName" />
      </div>
    </t-dialog>

    <!-- 压缩 -->
    <t-dialog v-model:visible="compressVisible" header="压缩选中文件" width="520px">
      <div class="td-form">
        <div class="td-form-row">
          <label>压缩类型</label>
          <t-select v-model="compressType" @change="onCompressType">
            <t-option value="zip" label="zip（通用格式）" />
            <t-option value="tar.gz" label="tar.gz（推荐）" />
            <t-option value="rar" label="rar（WinRAR 对中文兼容较好）" />
            <t-option value="7z" label="7z（压缩率高）" />
          </t-select>
        </div>
        <div class="td-form-row">
          <label>压缩包存放路径</label>
          <t-input v-model="compressDest" placeholder="如 /yuanma.zip" />
        </div>
      </div>
      <template #footer>
        <t-button theme="default" @click="compressVisible = false">关闭</t-button>
        <t-button theme="primary" :loading="compressLoading" @click="doCompress">确认压缩</t-button>
      </template>
    </t-dialog>

    <!-- 拖拽上传：同名文件逐个确认 -->
    <t-dialog
      :visible="dup.visible"
      header="存在同名文件"
      width="440px"
      :close-on-overlay-click="false"
      :close-on-esc-keydown="false"
      :cancel-btn="null"
      :confirm-btn="{ content: '确定', theme: 'primary' }"
      @confirm="resolveDup"
      @close="resolveDup"
    >
      <p class="td-ftp-hint">
        正在上传 <b>{{ dup.name }}</b>（{{ fmtSize(dup.size) }}），当前目录已存在同名文件{{ dup.existing ? '（' + fmtSize(dup.existing.size) + '）' : '' }}。
      </p>
      <t-radio-group v-model="dup.choice">
        <t-radio value="overwrite">覆盖（删除服务器同名文件后重传）</t-radio>
        <t-radio value="skip">跳过该文件</t-radio>
        <t-radio value="rename">改名后上传</t-radio>
      </t-radio-group>
      <t-input v-if="dup.choice === 'rename'" v-model="dup.newName" placeholder="新文件名" class="td-ftp-dup-rename" />
    </t-dialog>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, h } from 'vue'
import { MessagePlugin, DialogPlugin } from 'tdesign-vue-next'
import {
  listDir, readFile, saveFile, createEntry, deleteOne, deleteBatch, rename,
  copyPaste, compress, unzip, dirSize, fileDownload, getAccess, setAccess, importSql,
} from '@/user/api/ftp'
import { ChunkUploader } from '@/user/utils/uploader'
import { loadCodeMirror, cmThemeCss } from '@/user/utils/codemirror'

// ------------------------------------------------------------------
// 常量与工具
// ------------------------------------------------------------------
const EDITOR_THEMES = ['3024-night', 'default', '3024-day', 'abbott', 'abcdef', 'ambiance', 'ayu-dark',
  'ayu-mirage', 'base16-dark', 'base16-light', 'bespin', 'blackboard', 'cobalt', 'colorforth',
  'darcula', 'dracula', 'duotone-dark', 'duotone-light', 'eclipse', 'erlang-dark', 'gruvbox-dark',
  'hopscotch', 'icecoder', 'idea', 'isotope', 'material', 'material-darker', 'material-ocean',
  'mbo', 'midnight', 'monokai', 'neat', 'neo', 'night', 'nord', 'oceanic-next', 'panda-syntax',
  'paraiso-dark', 'paraiso-light', 'pastel-on-dark', 'railscasts', 'rubyblue', 'seti', 'solarized',
  'the-matrix', 'tomorrow-night-bright', 'tomorrow-night-eighties', 'twilight', 'vibrant-ink',
  'xq-dark', 'xq-light', 'yeti', 'zenburn']

const ICONS = {
  zip: 'mdi-zip-box', rar: 'mdi-zip-box', '7z': 'mdi-zip-box', gz: 'mdi-zip-box', tgz: 'mdi-zip-box',
  js: 'mdi-language-javascript', php: 'mdi-language-php', sql: 'mdi-database',
  png: 'mdi-image', jpg: 'mdi-image', jpeg: 'mdi-image', svg: 'mdi-image', ico: 'mdi-image',
  gif: 'mdi-image', webp: 'mdi-image', bmp: 'mdi-image',
  mp4: 'mdi-file-video', avi: 'mdi-file-video', wmv: 'mdi-file-video', mpg: 'mdi-file-video',
  mpeg: 'mdi-file-video', mov: 'mdi-file-video', mkv: 'mdi-file-video',
  mp3: 'mdi-file-music', wma: 'mdi-file-music', aac: 'mdi-file-music', flac: 'mdi-file-music',
  css: 'mdi-language-css3', htm: 'mdi-web', html: 'mdi-web', xml: 'mdi-xml', json: 'mdi-code-json',
  py: 'mdi-language-python', go: 'mdi-language-go', java: 'mdi-language-java',
  docx: 'mdi-file-word', doc: 'mdi-file-word', xls: 'mdi-file-excel', xlsx: 'mdi-file-excel',
  pdf: 'mdi-file-pdf', md: 'mdi-language-markdown', txt: 'mdi-file-document-outline',
  log: 'mdi-file-document-outline',
}
const ARCHIVES = ['zip', 'rar', 'gz', 'tgz']
const IMAGES = ['png', 'jpg', 'jpeg', 'gif', 'ico', 'svg', 'webp', 'bmp']
const MEDIA = ['mp4', 'avi', 'wmv', 'mpg', 'mpeg', 'mov', 'mkv', 'mp3', 'wma', 'aac', 'flac']
const CODE_MODES = {
  js: 'javascript', json: 'javascript', php: 'application/x-httpd-php',
  sql: 'text/x-mysql', css: 'text/css', htm: 'text/html', html: 'text/html',
  xml: 'application/xml', md: 'text/html',
}

function extOf(name) {
  const i = String(name).lastIndexOf('.')
  return i > -1 ? String(name).slice(i + 1).toLowerCase() : ''
}
function iconOf(name) {
  return ICONS[extOf(name)] || 'mdi-file-document'
}
function isArchive(name) {
  return ARCHIVES.indexOf(extOf(name)) !== -1
}
function isImage(name) {
  return IMAGES.indexOf(extOf(name)) !== -1
}
function isEditable(name) {
  const e = extOf(name)
  return !isArchive(name) && !isImage(name) && MEDIA.indexOf(e) === -1
}
function fmtSize(v) {
  const n = Number(v) || 0
  const units = ['B', 'KB', 'MB', 'GB', 'TB']
  let i = 0
  let x = n
  while (x >= 1024 && i < units.length - 1) {
    x /= 1024
    i += 1
  }
  return x.toFixed(2) + ' ' + units[i]
}
function fmtTime(v) {
  const n = Number(v)
  if (!n) return '-'
  const d = new Date(n * 1000)
  if (isNaN(d.getTime())) return String(v)
  const p = (x) => (x < 10 ? '0' + x : x)
  return `${d.getFullYear()}-${p(d.getMonth() + 1)}-${p(d.getDate())} ${p(d.getHours())}:${p(d.getMinutes())}:${p(d.getSeconds())}`
}
function joinPath(name) {
  return path.value === '/' ? '/' + name : path.value + '/' + name
}

// ------------------------------------------------------------------
// 目录列表
// ------------------------------------------------------------------
const path = ref('/')
const rows = ref([])
const total = ref(0)
const page = ref(1)
const limit = ref(100)
const sortBy = ref('name')
const descending = ref(false)
const loading = ref(false)
const selection = ref([])
const selectedKeys = ref([])

const pagination = computed(() => ({
  current: page.value,
  pageSize: limit.value,
  total: total.value,
  showJumper: true,
  pageSizeOptions: [20, 50, 100, 200, 500, 1000],
}))

const crumbs = computed(() => {
  const out = []
  let acc = ''
  path.value.split('/').forEach((seg) => {
    if (!seg) return
    acc += '/' + seg
    out.push({ name: seg, path: acc })
  })
  return out
})

const columns = [
  { colKey: 'row-select', type: 'multiple', width: 46 },
  { colKey: 'name', title: '文件名称', sorter: true, minWidth: 260, ellipsis: true },
  { colKey: 'size', title: '大小', sorter: true, width: 110 },
  { colKey: 'mtime', title: '修改时间', sorter: true, width: 170 },
  { colKey: 'operate', title: '操作', width: 150, fixed: 'right' },
]

const parentPath = computed(() => {
  const p = path.value
  const i = p.lastIndexOf('/')
  return i <= 0 ? '/' : p.slice(0, i)
})

function moreOptions(row) {
  const out = [
    { content: '重命名', value: 'rename' },
    { content: '复制', value: 'copy' },
    { content: '剪切', value: 'cut' },
  ]
  if (row.type === 'file') {
    if (isArchive(row.name)) out.push({ content: '解压', value: 'unzip' })
    if (extOf(row.name) === 'sql') out.push({ content: '导入到数据库', value: 'sql' })
    if (isImage(row.name)) out.push({ content: '预览图片', value: 'preview' })
  }
  out.push({ content: '修改权限', value: 'perm' })
  return out
}

function onMoreClick(item, row) {
  const v = item && item.value
  if (v === 'rename') openRename(row)
  else if (v === 'copy') clip([row], 'copy')
  else if (v === 'cut') clip([row], 'cut')
  else if (v === 'unzip') openUnzip(row)
  else if (v === 'sql') confirmImportSql(row)
  else if (v === 'preview') previewImage(row)
  else if (v === 'perm') openPerm(row)
}

async function load() {
  loading.value = true
  const r = await listDir(path.value, page.value, limit.value, sortBy.value, descending.value ? 'desc' : 'asc')
  loading.value = false
  if (!r.ok) {
    MessagePlugin.error(r.code || '目录加载失败')
    rows.value = []
    total.value = 0
    return
  }
  // 服务端强制回落（站点目录异常时回到根目录），同步本地状态
  if (r.raw.path && r.raw.path !== path.value) {
    path.value = r.raw.path
  }
  rows.value = (r.raw.rows || []).map((it) => ({ ...it }))
  total.value = Number(r.raw.total) || 0
  selection.value = []
  selectedKeys.value = []
}

function go(p) {
  if (!p || p === path.value) return
  path.value = p
  page.value = 1
  load()
}

function enter(row) {
  go(joinPath(row.name))
}

function reload() {
  load()
}

function onPageChange(info) {
  page.value = info.current
  limit.value = info.pageSize
  load()
}

function onSortChange(ctx) {
  if (!ctx || !ctx.sortBy) return
  sortBy.value = ctx.sortBy
  descending.value = !!ctx.descending
  page.value = 1
  load()
}

function onSelectChange(keys, context) {
  selectedKeys.value = keys || []
  selection.value = (context && context.selectedRowData) || []
}

async function calcSize(row) {
  const r = await dirSize(joinPath(row.name))
  if (r.ok) {
    row.size = Number(r.raw.size) || 0
    row.sizeShown = true
  }
}

// ------------------------------------------------------------------
// 新建 / 重命名 / 删除
// ------------------------------------------------------------------
function openCreate(type) {
  const label = type === 'dir' ? '文件夹' : '文件'
  const input = { value: '' }
  const dialog = DialogPlugin.confirm({
    header: '新建' + label,
    body: () => h('div', [
      h('p', { style: 'margin:0 0 8px;color:#4b5b5b;font-size:13px' }, `存放于 ${path.value}，请输入${label}名称：`),
      h('input', {
        style: 'width:100%;box-sizing:border-box;padding:8px;border:1px solid #dcdcdc;border-radius:6px',
        autofocus: true,
        onInput: (e) => { input.value = e.target.value },
        onFocus: (e) => e.target.select(),
      }),
    ]),
    confirmBtn: '创建',
    onConfirm: async () => {
      const name = (input.value || '').trim()
      if (!name) {
        MessagePlugin.warning('名称不能为空')
        return
      }
      const r = await createEntry(path.value, name, type)
      if (r.ok) {
        MessagePlugin.success('创建成功')
        dialog.destroy()
        load()
      }
    },
  })
}

function openRename(row) {
  const label = row.type === 'dir' ? '文件夹' : '文件'
  const input = { value: row.name }
  const dialog = DialogPlugin.confirm({
    header: `重命名${label} - ${row.name}`,
    body: () => h('input', {
      style: 'width:100%;box-sizing:border-box;padding:8px;border:1px solid #dcdcdc;border-radius:6px',
      value: row.name,
      autofocus: true,
      onInput: (e) => { input.value = e.target.value },
      onFocus: (e) => e.target.select(),
    }),
    confirmBtn: '确定',
    onConfirm: async () => {
      const name = (input.value || '').trim()
      if (!name) {
        MessagePlugin.warning('名称不能为空')
        return
      }
      if (name === row.name) {
        MessagePlugin.warning('新名称与原名称相同')
        return
      }
      const r = await rename(path.value, row.name, name)
      if (r.ok) {
        MessagePlugin.success('重命名成功')
        dialog.destroy()
        load()
      }
    },
  })
}

function removeEntries(list) {
  if (!list || !list.length) {
    MessagePlugin.warning('请至少选择一项')
    return
  }
  const names = list.map((r) => r.name)
  const confirm = DialogPlugin.confirm({
    header: '删除确认',
    body: `确定要删除选中的 ${names.length} 项吗？文件将进入节点回收站，如需恢复请联系站点管理员。`,
    confirmBtn: { content: '确定删除', theme: 'danger' },
    onConfirm: async () => {
      let r
      if (names.length === 1) {
        r = await deleteOne(path.value, names[0], list[0].type === 'dir' ? 'dir' : 'file')
      } else {
        r = await deleteBatch(path.value, names)
      }
      if (r.ok) {
        MessagePlugin.success('删除成功')
        confirm.destroy()
        load()
      }
    },
  })
}

// ------------------------------------------------------------------
// 复制 / 剪切 / 粘贴
// ------------------------------------------------------------------
const clipboard = ref(null)

function clip(list, type) {
  if (!list || !list.length) return
  clipboard.value = { path: path.value, names: list.map((r) => r.name), type }
  MessagePlugin.info((type === 'cut' ? '剪切' : '复制') + '完成！请到目标目录点击"粘贴"。')
}

function paste() {
  const cb = clipboard.value
  if (!cb) return
  if (cb.path === path.value) {
    MessagePlugin.warning('原目录与粘贴目录不能相同')
    return
  }
  const selfLoop = cb.names.some(
    (n) => path.value.slice(0, (cb.path + n + '/').length) === cb.path + n + '/',
  )
  if (selfLoop) {
    MessagePlugin.error('粘贴目录与源目录存在包含关系，禁止粘贴')
    return
  }
  const conflicts = rows.value.filter((r) => cb.names.indexOf(r.name) !== -1)
  const doPaste = async () => {
    const r = await copyPaste(cb.path, path.value, cb.names, cb.type)
    if (r.ok) {
      MessagePlugin.success(r.code || '粘贴成功')
    } else {
      MessagePlugin.error(r.code || '粘贴失败')
    }
    clipboard.value = null
    load()
  }
  if (conflicts.length) {
    const names = conflicts.map((c) => c.name).join('、')
    const confirm = DialogPlugin.confirm({
      header: '即将覆盖以下文件',
      body: `该目录存在同名文件：${names}。是否确认覆盖？`,
      confirmBtn: { content: '确认覆盖', theme: 'primary' },
      onConfirm: async () => {
        confirm.destroy()
        await doPaste()
      },
    })
  } else {
    doPaste()
  }
}

// ------------------------------------------------------------------
// 压缩 / 解压
// ------------------------------------------------------------------
const compressVisible = ref(false)
const compressType = ref('zip')
const compressDest = ref('')
const compressNames = ref([])
const compressLoading = ref(false)

function openCompress() {
  if (!selection.value.length) return
  compressNames.value = selection.value.map((r) => r.name)
  compressType.value = 'zip'
  compressDest.value = 'yuanma' + Math.floor(Math.random() * 2000) + '.zip'
  compressVisible.value = true
}

function onCompressType(v) {
  if (compressDest.value && !compressDest.value.endsWith('.' + compressType.value)) {
    compressDest.value = compressDest.value.replace(/\.[a-z0-9.]+$/i, '') + '.' + v
  }
}

async function doCompress() {
  const dest = (compressDest.value || '').trim()
  if (!dest) {
    MessagePlugin.warning('压缩包存放路径不能为空')
    return
  }
  compressLoading.value = true
  const r = await compress(path.value, compressNames.value, compressType.value, dest)
  compressLoading.value = false
  if (r.ok) {
    MessagePlugin.success('压缩成功')
    compressVisible.value = false
    load()
  }
}

const unzipVisible = ref(false)
const unzipName = ref('')
const unzipPath = ref('')
const unzipDest = ref('/')
const unzipPass = ref('')
const unzipCoding = ref('UTF-8')
const unzipLoading = ref(false)

function openUnzip(row) {
  unzipName.value = row.name
  unzipPath.value = joinPath(row.name)
  unzipDest.value = path.value
  unzipPass.value = ''
  unzipCoding.value = 'UTF-8'
  unzipVisible.value = true
}

async function doUnzip() {
  const dest = (unzipDest.value || '').trim()
  if (!dest) {
    MessagePlugin.warning('解压到的目录不能为空')
    return
  }
  unzipLoading.value = true
  const r = await unzip(path.value, unzipName.value, dest, unzipPass.value, unzipCoding.value)
  unzipLoading.value = false
  if (r.ok) {
    MessagePlugin.success('解压请求已提交')
    unzipVisible.value = false
    setTimeout(load, 1500)
  }
}

// ------------------------------------------------------------------
// 上传
// ------------------------------------------------------------------
const uploadVisible = ref(false)
const uploadFile = ref(null)
const uploading = ref(false)
const uploadPercent = ref(0)
const uploadText = ref('')
const uploadSpeed = ref('')
const uploadRest = ref('')
const uploader = ref(null)
const fileInputRef = ref(null)

function openUpload() {
  uploadFile.value = null
  uploadVisible.value = true
}

function onFileChange(e) {
  uploadFile.value = (e.target.files && e.target.files[0]) || null
}

function resetUpload() {
  if (uploader.value) uploader.value.stop()
  uploading.value = false
  uploadPercent.value = 0
  uploadText.value = ''
  uploadSpeed.value = ''
  uploadRest.value = ''
  if (fileInputRef.value) fileInputRef.value.value = ''
  uploadFile.value = null
}

function cancelUpload() {
  if (uploader.value) uploader.value.stop()
  uploading.value = false
  MessagePlugin.info('已取消上传')
}

async function startUpload() {
  const file = uploadFile.value
  if (!file || uploading.value) return

  const beginWith = (name) => {
    const real = new File([file], name, { type: file.type })
    uploading.value = true
    uploadPercent.value = 0
    uploader.value = new ChunkUploader(real, path.value, {
      onProgress: (p, speed, text, rest) => {
        uploadPercent.value = p
        uploadSpeed.value = speed
        uploadText.value = text
        uploadRest.value = rest
      },
      onDone: () => {
        uploading.value = false
        MessagePlugin.success('上传成功')
        uploadVisible.value = false
        load()
      },
      onError: (msg) => {
        uploading.value = false
        MessagePlugin.error(msg || '上传失败')
      },
    })
    uploader.value.start()
  }

  const conflict = rows.value.find((r) => r.name === file.name && r.type === 'file')
  if (conflict) {
    const input = { value: file.name.replace(/(\.[^.]+)?$/, '-副本$1') }
    const mode = { value: 'overwrite' }
    const confirm = DialogPlugin.confirm({
      header: '文件名冲突',
      body: () => h('div', [
        h('p', { style: 'margin:0 0 8px;font-size:13px;color:#4b5b5b' },
          `目录中已存在同名文件 [${file.name}]（${fmtSize(conflict.size)}），请选择操作：`),
        h('label', { style: 'display:block;margin-bottom:4px' }, [
          h('input', {
            type: 'radio', name: 'td-up-mode', value: 'overwrite', checked: true,
            onChange: () => { mode.value = 'overwrite' },
          }),
          ' 覆盖文件',
        ]),
        h('label', { style: 'display:block;margin-bottom:8px' }, [
          h('input', {
            type: 'radio', name: 'td-up-mode', value: 'rename',
            onChange: () => { mode.value = 'rename' },
          }),
          ' 重命名上传',
        ]),
        h('input', {
          style: 'width:100%;box-sizing:border-box;padding:8px;border:1px solid #dcdcdc;border-radius:6px',
          value: input.value,
          onInput: (e) => { input.value = e.target.value },
        }),
      ]),
      confirmBtn: '开始上传',
      onConfirm: () => {
        if (mode.value === 'rename') {
          const nn = (input.value || '').trim()
          if (!nn || nn.indexOf('/') !== -1) {
            MessagePlugin.warning('文件名不合法')
            return
          }
          confirm.destroy()
          beginWith(nn)
        } else {
          confirm.destroy()
          beginWith(file.name)
        }
      },
    })
  } else {
    beginWith(file.name)
  }
}

// ------------------------------------------------------------------
// 下载 / 图片预览 / SQL 导入
// ------------------------------------------------------------------
async function getDownloadUrl(row) {
  const r = await fileDownload(path.value, row.name)
  if (r.ok && r.raw.url) return r.raw.url
  MessagePlugin.error(r.code || '获取下载链接失败')
  return ''
}

async function download(row) {
  const url = await getDownloadUrl(row)
  if (url) window.open(url, '_blank')
}

const imageVisible = ref(false)
const imageName = ref('')
const imageUrl = ref('')

async function previewImage(row) {
  const url = await getDownloadUrl(row)
  if (!url) return
  imageName.value = row.name
  imageUrl.value = url
  imageVisible.value = true
}

function confirmImportSql(row) {
  const confirm = DialogPlugin.confirm({
    header: '导入确认',
    body: `确定要将 [${row.name}] 导入数据库吗？如有相同数据将在导入后覆盖，导入后不可恢复！`,
    confirmBtn: { content: '确认导入', theme: 'danger' },
    onConfirm: async () => {
      const r = await importSql(path.value, row.name)
      if (r.ok) {
        MessagePlugin.success(r.code || '导入成功')
        confirm.destroy()
      }
    },
  })
}

// ------------------------------------------------------------------
// 权限
// ------------------------------------------------------------------
const permVisible = ref(false)
const permPath = ref('')
const permVal = ref('')
const permLoading = ref(false)

async function openPerm(row) {
  permPath.value = joinPath(row.name)
  permVal.value = ''
  permVisible.value = true
  const r = await getAccess(permPath.value)
  if (r.ok) permVal.value = r.raw.access || ''
}

async function doPerm() {
  const val = (permVal.value || '').trim()
  if (!/^[0-7]{3,4}$/.test(val)) {
    MessagePlugin.warning('请输入如 644 / 755 的八进制权限值')
    return
  }
  permLoading.value = true
  const r = await setAccess(permPath.value, val)
  permLoading.value = false
  if (r.ok) {
    MessagePlugin.success('修改成功')
    permVisible.value = false
  }
}

// ------------------------------------------------------------------
// 拖拽上传（队列串行 + 同名确认，复用 ChunkUploader）
// ------------------------------------------------------------------
const dropActive = ref(false)
const dropCount = ref(0)
let dragDepth = 0
let dragDir = '/'
let dragQueue = []
let dragUploader = null
let dupResolve = null
const dq = ref({ active: false, idx: 0, total: 0, name: '', percent: 0, text: '', speed: '', rest: '' })
const dup = ref({ visible: false, name: '', size: 0, existing: null, choice: 'skip', newName: '' })

function onDragEnter(e) {
  if (!e.dataTransfer || !Array.from(e.dataTransfer.types || []).includes('Files')) return
  dragDepth += 1
  dropActive.value = true
}
function onDragOver(e) {
  if (!e.dataTransfer || !Array.from(e.dataTransfer.types || []).includes('Files')) return
  dropActive.value = true
  dropCount.value = (e.dataTransfer.items && e.dataTransfer.items.length) || 0
}
function onDragLeave() {
  dragDepth = Math.max(0, dragDepth - 1)
  if (dragDepth === 0) dropActive.value = false
}

function onDrop(e) {
  dropActive.value = false
  dragDepth = 0
  const list = Array.from((e.dataTransfer && e.dataTransfer.files) || [])
  const files = list.filter((f) => f.size > 0)
  if (list.length > files.length) {
    MessagePlugin.warning(`暂不支持拖入文件夹，已跳过 ${list.length - files.length} 项`)
  }
  if (!files.length) return
  if (dq.value.active) {
    MessagePlugin.warning('已有拖拽上传任务进行中，请等待完成')
    return
  }
  startDragUpload(files)
}

function dupOfName(name) {
  return rows.value.find((r) => r.name === name) || null
}

function suggestName(name) {
  const dot = name.lastIndexOf('.')
  const base = dot > 0 ? name.slice(0, dot) : name
  const ext = dot > 0 ? name.slice(dot) : ''
  let i = 1
  let n = base + '(' + i + ')' + ext
  while (dupOfName(n)) { i += 1; n = base + '(' + i + ')' + ext }
  return n
}

function askDup(file, existing) {
  dup.value = {
    visible: true,
    name: file.name,
    size: file.size,
    existing,
    choice: 'skip',
    newName: suggestName(file.name),
  }
  return new Promise((resolve) => { dupResolve = resolve })
}

function resolveDup() {
  const d = dup.value
  if (!d.visible || !dupResolve) return
  if (d.choice === 'rename') {
    const nn = String(d.newName || '').trim()
    if (!nn || /[\\/]/.test(nn)) {
      MessagePlugin.error('新文件名无效')
      return
    }
    finishDup({ choice: 'rename', name: dupOfName(nn) ? suggestName(nn) : nn })
    return
  }
  finishDup({ choice: d.choice })
}

function finishDup(result) {
  dup.value.visible = false
  const r = dupResolve
  dupResolve = null
  if (r) r(result)
}

async function startDragUpload(files) {
  dragDir = path.value
  dragQueue = []
  for (const f of files) {
    const existing = dupOfName(f.name)
    const decision = existing ? await askDup(f, existing) : { choice: 'upload' }
    if (decision.choice === 'skip') continue
    if (decision.choice === 'overwrite') dragQueue.push({ file: f, name: f.name, overwrite: true })
    else if (decision.choice === 'rename') dragQueue.push({ file: f, name: decision.name, overwrite: false })
    else dragQueue.push({ file: f, name: f.name, overwrite: false })
  }
  if (!dragQueue.length) {
    MessagePlugin.info('没有需要上传的文件')
    return
  }
  dq.value = { active: true, idx: 0, total: dragQueue.length, name: '', percent: 0, text: '', speed: '', rest: '' }
  runNextDrag()
}

function nextDrag() {
  dq.value.idx += 1
  runNextDrag()
}

function finishDragUpload(msg) {
  dq.value = { active: false, idx: 0, total: 0, name: '', percent: 0, text: '', speed: '', rest: '' }
  dragQueue = []
  dragUploader = null
  if (msg) MessagePlugin.success(msg)
  load()
}

function runNextDrag() {
  const item = dragQueue[dq.value.idx]
  if (!item) {
    finishDragUpload('拖拽上传完成')
    return
  }
  dq.value.name = item.name
  dq.value.percent = 0
  const up = new ChunkUploader(item.file, dragDir, {
    name: item.name,
    onProgress: (p, speed, text, rest) => {
      dq.value.percent = p
      dq.value.speed = speed
      dq.value.text = text
      dq.value.rest = rest || '—'
    },
    // 服务端已存在同名文件（如在其他分页、列表页检测不到）一律中止，绝不续写污染
    onPrepare: () => false,
    onDone: nextDrag,
    onError: (msg) => {
      MessagePlugin.error(`${item.name}：${msg}`)
      nextDrag()
    },
  })
  dragUploader = up
  if (item.overwrite) {
    deleteOne(dragDir, item.name, 'file').then((r) => {
      if (r.ok) {
        up.start()
      } else {
        MessagePlugin.error(`${item.name}：删除旧文件失败，已跳过（${r.code || '未知错误'}）`)
        nextDrag()
      }
    })
  } else {
    up.start()
  }
}

function cancelDragUpload() {
  if (dragUploader) dragUploader.stop()
  finishDragUpload('已取消拖拽上传')
}

// ------------------------------------------------------------------
// 在线编辑器
// ------------------------------------------------------------------
const editorVisible = ref(false)
const editorName = ref('')
const editorPath = ref('')
const editorLoading = ref(false)
const saving = ref(false)
const editorTheme = ref('3024-night')
const editorTextareaRef = ref(null)
let cm = null

try {
  const saved = localStorage.getItem('mnbt_editor_theme')
  if (saved && EDITOR_THEMES.indexOf(saved) !== -1) editorTheme.value = saved
} catch (e) { /* localStorage 不可用时忽略 */ }

async function openEditor(row) {
  editorPath.value = joinPath(row.name)
  editorName.value = row.name
  editorVisible.value = true
  editorLoading.value = true
  try {
    const [CodeMirror, r] = await Promise.all([loadCodeMirror(), readFile(editorPath.value)])
    if (!r.ok) {
      MessagePlugin.error(r.code || '文件内容获取失败')
      editorVisible.value = false
      return
    }
    await new Promise((resolve) => setTimeout(resolve, 60))
    if (cm) {
      cm.toTextArea()
      cm = null
    }
    cm = CodeMirror.fromTextArea(editorTextareaRef.value, {
      lineNumbers: true,
      tabSize: 4,
      indentUnit: 4,
      styleActiveLine: true,
      matchBrackets: true,
      mode: CODE_MODES[extOf(row.name)] || 'application/x-httpd-php',
      lineWrapping: true,
      theme: editorTheme.value,
      autoRefresh: true,
      autoCloseBrackets: true,
      extraKeys: {
        'Ctrl-S': () => saveEditor(false),
        'Cmd-S': () => saveEditor(false),
        'Ctrl-H': 'replace',
      },
    })
    cm.setSize('100%', '560px')
    cm.setValue(r.raw.content || '')
    setTimeout(() => cm && cm.refresh(), 120)
  } catch (e) {
    MessagePlugin.error((e && e.message) || '编辑器加载失败')
    editorVisible.value = false
  } finally {
    editorLoading.value = false
  }
}

function closeEditor() {
  if (cm) {
    cm.toTextArea()
    cm = null
  }
  editorPath.value = ''
  editorName.value = ''
}

async function saveEditor(closeAfter) {
  if (!cm || !editorPath.value) return
  saving.value = true
  const r = await saveFile(editorPath.value, cm.getValue())
  saving.value = false
  if (r.ok) {
    MessagePlugin.success('保存成功')
    if (closeAfter) editorVisible.value = false
  }
}

function onEditorTheme(theme) {
  if (!theme) return
  const linkId = 'td-ftp-editor-theme-css'
  let link = document.getElementById(linkId)
  if (!link) {
    link = document.createElement('link')
    link.id = linkId
    link.rel = 'stylesheet'
    document.head.appendChild(link)
  }
  link.href = cmThemeCss(theme)
  if (cm) cm.setOption('theme', theme)
  try {
    localStorage.setItem('mnbt_editor_theme', theme)
  } catch (e) { /* ignore */ }
}

// ------------------------------------------------------------------
onMounted(load)
</script>

<style scoped>
.td-ftp-pathbar {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px 12px;
  margin-bottom: 12px;
  background: var(--td-surface);
  border: 1px solid var(--td-border);
  border-radius: var(--td-radius-lg);
  box-shadow: var(--td-shadow);
}
.td-ftp-crumb {
  flex: 1;
  min-width: 0;
  overflow-x: auto;
  white-space: nowrap;
  scrollbar-width: thin;
}
.td-ftp-crumb::-webkit-scrollbar {
  height: 4px;
}
.td-ftp-crumb::-webkit-scrollbar-thumb {
  background: #d6dce5;
}
.td-ftp-dragtip {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  margin-left: 4px;
}
.td-ftp-enter {
  color: #0052d9;
  font-weight: 500;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  gap: 6px;
}
.td-ftp-enter:hover {
  text-decoration: underline;
}
.td-ftp-file {
  display: inline-flex;
  align-items: center;
  gap: 6px;
}
.td-ftp-calc {
  color: #0052d9;
}
.td-ftp-hint {
  color: #8a8f8d;
  font-size: 12px;
  margin: 4px 0;
}
.td-ftp-file-input {
  width: 100%;
  padding: 8px;
  border: 1px dashed #bbb;
  border-radius: 6px;
  box-sizing: border-box;
}
.td-ftp-filechip {
  margin-top: 8px;
  padding: 8px 10px;
  background: #f3f6fb;
  border-radius: 6px;
  font-size: 13px;
  display: flex;
  align-items: center;
  gap: 6px;
}
.td-ftp-editor-head {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 8px;
}
.td-ftp-theme-select {
  width: 200px;
}
.td-ftp-editor-loading {
  padding: 16px 0;
}
.td-ftp-editor-area {
  width: 100%;
  height: 480px;
}
.td-ftp-perm-row {
  display: flex;
  gap: 8px;
}
.td-ftp-perm-row .t-input__wrap {
  flex: 1;
}
.td-ftp-image {
  text-align: center;
}
.td-ftp-image img {
  max-width: 100%;
  max-height: 60vh;
}
.td-ftp-dropzone {
  position: relative;
}
:deep(.t-table--hoverable tbody tr:hover > td) {
  background: #f5f8ff;
}
.td-ftp-drop-mask {
  position: absolute;
  inset: 0;
  z-index: 30;
  pointer-events: none;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 8px;
  background: rgba(0, 82, 217, 0.06);
  border: 2px dashed #0052d9;
  color: #0052d9;
  font-size: 15px;
}
.td-ftp-dragbar {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 8px 12px;
  margin-bottom: 8px;
  background: #f2f6ff;
  border: 1px solid #d9e4ff;
  font-size: 13px;
}
.td-ftp-dragbar-title {
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  max-width: 260px;
}
.td-ftp-dragbar-bar {
  width: 240px;
}
.td-ftp-dup-rename {
  margin-top: 8px;
}
</style>
