<template>
  <div class="ql-page">
    <div class="ql-card head">
      <h3 class="ql-section-title" style="margin:0">{{ title }}</h3>
      <p class="ql-muted">{{ desc }}</p>
    </div>

    <div class="ql-card" v-loading="loading">
      <!-- PHP -->
      <template v-if="gn === 'php'">
        <el-form label-position="top" style="max-width:420px">
          <el-form-item label="PHP 版本">
            <el-select v-model="php.version" style="width:100%" placeholder="选择版本">
              <el-option
                v-for="p in php.list"
                :key="p.version || p.name"
                :label="p.name || p.version"
                :value="p.version || p.name"
              />
            </el-select>
          </el-form-item>
          <el-button type="primary" round :loading="saving" @click="savePhp">保存</el-button>
        </el-form>
      </template>

      <!-- 域名 -->
      <template v-else-if="gn === 'url' || gn === 'CDN_url'">
        <div class="sec-toolbar">
          <span class="ql-muted">{{ domainHint }}</span>
          <el-button type="primary" round size="small" @click="domainDialog = true">添加域名</el-button>
        </div>
        <el-table :data="domains" stripe empty-text="暂无域名" style="margin-top:12px">
          <el-table-column prop="name" label="域名" min-width="160" />
          <el-table-column prop="port" label="端口" width="80" />
          <el-table-column prop="path" label="路径" width="100" />
          <el-table-column label="操作" width="100">
            <template #default="{ row }">
              <el-button link type="danger" @click="delDomain(row)">删除</el-button>
            </template>
          </el-table-column>
        </el-table>
        <el-dialog v-model="domainDialog" title="添加域名" width="420px">
          <el-form label-position="top">
            <el-form-item label="域名">
              <el-input v-model="domainForm.url" placeholder="www.example.com" />
            </el-form-item>
            <el-form-item v-if="gn === 'CDN_url'" label="源站 IP">
              <el-input v-model="domainForm.yz_ip" />
            </el-form-item>
            <el-form-item v-else label="绑定目录">
              <el-select v-model="domainForm.dirs" style="width:100%">
                <el-option v-for="d in domainDirs" :key="d" :label="d" :value="d" />
              </el-select>
            </el-form-item>
          </el-form>
          <template #footer>
            <el-button @click="domainDialog = false">取消</el-button>
            <el-button type="primary" :loading="saving" @click="addDomain">添加</el-button>
          </template>
        </el-dialog>
      </template>

      <!-- 密码访问 -->
      <template v-else-if="gn === 'pass'">
        <div class="sec-toolbar">
          <span class="ql-muted">目录访问认证</span>
          <el-button type="primary" round size="small" @click="passDialog = true">添加</el-button>
        </div>
        <el-table :data="passListData" stripe empty-text="暂无" style="margin-top:12px">
          <el-table-column prop="name" label="名称" />
          <el-table-column prop="site_dir" label="目录" />
          <el-table-column prop="username" label="用户" />
          <el-table-column label="操作" width="90">
            <template #default="{ row }">
              <el-button link type="danger" @click="delPass(row)">删除</el-button>
            </template>
          </el-table-column>
        </el-table>
        <el-dialog v-model="passDialog" title="添加密码访问" width="420px">
          <el-form label-position="top">
            <el-form-item label="名称"><el-input v-model="passForm.name" /></el-form-item>
            <el-form-item label="目录"><el-input v-model="passForm.mbml" placeholder="/" /></el-form-item>
            <el-form-item label="用户名"><el-input v-model="passForm.user" /></el-form-item>
            <el-form-item label="密码"><el-input v-model="passForm.pass" type="password" show-password /></el-form-item>
          </el-form>
          <template #footer>
            <el-button @click="passDialog = false">取消</el-button>
            <el-button type="primary" :loading="saving" @click="addPass">创建</el-button>
          </template>
        </el-dialog>
      </template>

      <!-- 默认文档 -->
      <template v-else-if="gn === 'mrwd'">
        <el-form label-position="top" style="max-width:560px">
          <el-form-item label="默认文档（逗号分隔）">
            <el-input v-model="mrwd" type="textarea" :rows="3" />
          </el-form-item>
          <el-button type="primary" round :loading="saving" @click="saveMrwd">保存</el-button>
        </el-form>
      </template>

      <!-- 运行目录 -->
      <template v-else-if="gn === 'yxml'">
        <el-form label-position="top" style="max-width:420px">
          <el-form-item label="运行目录">
            <el-select v-model="runPath" filterable allow-create style="width:100%">
              <el-option v-for="d in runDirs" :key="d" :label="d" :value="d" />
            </el-select>
          </el-form-item>
          <el-button type="primary" round :loading="saving" @click="saveRunPath">保存</el-button>
        </el-form>
      </template>

      <!-- 伪静态 -->
      <template v-else-if="gn === 'wjt'">
        <el-form label-position="top">
          <el-form-item label="模板">
            <el-select v-model="rewriteTpl" style="width:240px" @change="loadTpl" clearable>
              <el-option v-for="t in rewriteTpls" :key="t" :label="t" :value="t" />
            </el-select>
          </el-form-item>
          <el-form-item label="规则">
            <el-input v-model="rewriteBody" type="textarea" :rows="12" class="mono" />
          </el-form-item>
          <el-button type="primary" round :loading="saving" @click="saveWjt">保存</el-button>
        </el-form>
      </template>

      <!-- SSL -->
      <template v-else-if="gn === 'ssl'">
        <el-tabs v-model="sslTab">
          <el-tab-pane label="证书配置" name="pem">
            <el-form label-position="top">
              <el-form-item label="私钥 KEY">
                <el-input v-model="ssl.key" type="textarea" :rows="6" class="mono" />
              </el-form-item>
              <el-form-item label="证书 PEM">
                <el-input v-model="ssl.pem" type="textarea" :rows="8" class="mono" />
              </el-form-item>
              <div class="btn-row">
                <el-button type="primary" round :loading="saving" @click="saveSsl">保存证书</el-button>
                <el-button round :loading="saving" @click="onCloseSsl">关闭 SSL</el-button>
                <el-switch
                  v-model="ssl.https"
                  active-text="强制 HTTPS"
                  @change="onForceHttps"
                  style="margin-left:12px"
                />
              </div>
              <p v-if="ssl.info" class="ql-muted cert-info">{{ ssl.info }}</p>
            </el-form>
          </el-tab-pane>
          <el-tab-pane label="申请证书" name="le">
            <el-checkbox-group v-model="ssl.domains">
              <el-checkbox v-for="d in ssl.domainList" :key="d" :value="d" :label="d" />
            </el-checkbox-group>
            <div class="btn-row" style="margin-top:16px">
              <el-button type="primary" round :loading="saving" @click="onApplySsl">申请 Let's Encrypt</el-button>
            </div>
          </el-tab-pane>
        </el-tabs>
      </template>

      <!-- 防盗链 -->
      <template v-else-if="gn === 'fdl'">
        <el-form label-position="top" style="max-width:560px">
          <el-form-item label="URL 后缀（逗号分隔）">
            <el-input v-model="fdl.fix" placeholder="png,jpg,gif" />
          </el-form-item>
          <el-form-item label="许可域名（每行一个）">
            <el-input v-model="fdl.domains" type="textarea" :rows="4" />
          </el-form-item>
          <el-form-item label="响应资源">
            <el-input v-model="fdl.return_rule" placeholder="404 或 /security.png" />
          </el-form-item>
          <el-form-item label="允许空 Referer">
            <el-switch v-model="fdl.http_status" active-value="true" inactive-value="false" />
          </el-form-item>
          <el-form-item label="启用防盗链">
            <el-switch v-model="fdl.status" active-value="true" inactive-value="false" />
          </el-form-item>
          <el-button type="primary" round :loading="saving" @click="saveFdl">保存</el-button>
        </el-form>
      </template>

      <!-- Gzip -->
      <template v-else-if="gn === 'gzip'">
        <el-form label-position="top" style="max-width:520px">
          <el-form-item label="启用 Gzip">
            <el-switch v-model="gzip.on" />
          </el-form-item>
          <template v-if="gzip.on">
            <el-form-item label="压缩级别 (1-9)">
              <el-slider v-model="gzip.level" :min="1" :max="9" show-stops />
            </el-form-item>
            <el-form-item label="最小长度">
              <el-input v-model="gzip.min_len" placeholder="1k" />
            </el-form-item>
            <el-form-item label="MIME 类型">
              <el-input v-model="gzip.types" type="textarea" :rows="3" />
            </el-form-item>
          </template>
          <el-button type="primary" round :loading="saving" @click="saveGzip">保存</el-button>
        </el-form>
      </template>

      <!-- 缓存 -->
      <template v-else-if="gn === 'cache'">
        <div class="sec-toolbar">
          <span class="ql-muted">静态缓存规则</span>
          <el-button type="primary" round size="small" @click="cacheDialog = true">添加</el-button>
        </div>
        <el-table :data="cacheList" stripe empty-text="暂无规则" style="margin-top:12px">
          <el-table-column prop="suffix" label="后缀" />
          <el-table-column prop="time_out" label="过期" />
          <el-table-column label="操作" width="90">
            <template #default="{ row }">
              <el-button link type="danger" @click="delCache(row)">删除</el-button>
            </template>
          </el-table-column>
        </el-table>
        <el-dialog v-model="cacheDialog" title="添加缓存" width="400px">
          <el-form label-position="top">
            <el-form-item label="后缀"><el-input v-model="cacheForm.suffix" placeholder="js" /></el-form-item>
            <el-form-item label="过期时间"><el-input v-model="cacheForm.time_out" placeholder="30d" /></el-form-item>
          </el-form>
          <template #footer>
            <el-button @click="cacheDialog = false">取消</el-button>
            <el-button type="primary" :loading="saving" @click="addCache">添加</el-button>
          </template>
        </el-dialog>
      </template>

      <!-- 改密 -->
      <template v-else-if="gn === 'xgpass'">
        <el-form label-position="top" style="max-width:420px">
          <el-alert type="info" :closable="false" show-icon style="margin-bottom:16px"
            title="FTP 密码即控制面板登录密码，留空表示不修改该项" />
          <el-form-item label="FTP / 面板密码">
            <el-input v-model="passwords.ftp" type="password" show-password placeholder="至少 6 位" />
          </el-form-item>
          <el-form-item label="数据库密码">
            <el-input v-model="passwords.sql" type="password" show-password placeholder="至少 6 位" />
          </el-form-item>
          <el-button type="primary" round :loading="saving" @click="savePasswords">保存</el-button>
        </el-form>
      </template>

      <!-- SQL 权限 -->
      <template v-else-if="gn === 'mysqlcz'">
        <el-form label-position="top" style="max-width:420px">
          <el-form-item label="访问权限">
            <el-select v-model="dbAccess" style="width:100%" allow-create filterable>
              <el-option label="本机 127.0.0.1" value="127.0.0.1" />
              <el-option label="所有人 %" value="%" />
            </el-select>
          </el-form-item>
          <el-button type="primary" round :loading="saving" @click="saveDbAccess">保存</el-button>
        </el-form>
      </template>

      <el-empty v-else description="未知设置项" />
    </div>
  </div>
</template>

<script setup>
import { computed, reactive, ref, watch } from 'vue'
import { useRoute } from 'vue-router'
import { ElMessage, ElMessageBox } from 'element-plus'
import {
  setInit,
  passList,
  domainList,
  domainAdd,
  domainDel,
  phpChange,
  setDefaultDoc,
  setRunPath,
  loadRewriteTpl,
  saveRewrite,
  getSsl,
  setSsl,
  closeSsl,
  forceHttps,
  applySsl,
  listUrl,
  getFdl,
  setFdl,
  setGzip,
  cacheAdd,
  cacheDel,
  changePass,
  setDbAccess,
  addPassDir,
  delPassDir,
} from '../api/panel'

const route = useRoute()
const gn = computed(() => route.params.gn || 'php')
const loading = ref(false)
const saving = ref(false)

const titles = {
  php: 'PHP 版本',
  url: '域名管理',
  CDN_url: '域名管理',
  pass: '密码访问',
  mrwd: '默认文档',
  yxml: '运行目录',
  wjt: '伪静态',
  ssl: 'SSL 配置',
  fdl: '防盗链',
  gzip: 'Gzip 压缩',
  cache: '缓存配置',
  xgpass: '修改密码',
  mysqlcz: 'SQL 权限',
}
const descs = {
  php: '切换站点 PHP 运行版本',
  url: '绑定与管理站点域名',
  CDN_url: 'CDN 产品域名管理',
  pass: '为指定目录设置访问认证',
  mrwd: '设置首页默认文档列表',
  yxml: '设置网站运行目录',
  wjt: '配置伪静态规则',
  ssl: '证书部署与 HTTPS',
  fdl: '防盗链与 Referer 策略',
  gzip: '开启 Gzip 传输压缩',
  cache: '静态资源浏览器缓存',
  xgpass: '修改 FTP / 数据库密码',
  mysqlcz: '数据库远程访问 ACL',
}
const title = computed(() => titles[gn.value] || '站点设置')
const desc = computed(() => descs[gn.value] || '')

const php = reactive({ version: '', list: [] })
const domains = ref([])
const domainDirs = ref(['/'])
const domainDialog = ref(false)
const domainForm = reactive({ url: '', dirs: '/', yz_ip: '' })
const domainHint = ref('')
const passListData = ref([])
const passDialog = ref(false)
const passForm = reactive({ name: '', mbml: '/', user: '', pass: '' })
const mrwd = ref('')
const runPath = ref('')
const runDirs = ref([])
const rewriteTpl = ref('')
const rewriteTpls = ref([])
const rewriteBody = ref('')
const sslTab = ref('pem')
const ssl = reactive({
  key: '',
  pem: '',
  https: false,
  info: '',
  domains: [],
  domainList: [],
})
const fdl = reactive({
  fix: '',
  domains: '',
  return_rule: '',
  http_status: 'true',
  status: 'false',
})
const gzip = reactive({
  on: false,
  level: 6,
  min_len: '1k',
  types:
    'text/plain application/javascript application/x-javascript text/javascript text/css application/xml application/json',
})
const cacheList = ref([])
const cacheDialog = ref(false)
const cacheForm = reactive({ suffix: '', time_out: '30d' })
const passwords = reactive({ ftp: '', sql: '' })
const dbAccess = ref('127.0.0.1')

function pickPayload(res) {
  if (!res) return {}
  // panel 包装: data 为 msg；纯数据: data 即对象
  if (res.data != null && typeof res.data === 'object') return res.data
  if (res.raw != null && typeof res.raw === 'object') return res.raw
  return {}
}

function normalizeBool(v) {
  if (v === true || v === 1 || v === '1' || v === 'true' || v === 'True') return true
  return false
}

async function loadSection() {
  loading.value = true
  const section = gn.value
  try {
    if (section === 'url' || section === 'CDN_url') {
      const [init, list] = await Promise.all([setInit(section), domainList()])
      const m = pickPayload(init)
      if (m.btip || m.als !== undefined) {
        domainHint.value =
          m.als && m.als !== 'false' ? String(m.als) : `请将域名 A 记录到 ${m.btip || '服务器 IP'}`
      }
      const d = pickPayload(list)
      let urls = d.url || d.domains || []
      if (!Array.isArray(urls)) urls = []
      domains.value = urls.map((x) => ({
        name: x.name || x.domain || '',
        port: x.port ?? 80,
        path: x.path || '/',
        addtime: x.addtime || '',
      }))
      domainDirs.value = Array.isArray(d.dir) ? d.dir : Array.isArray(d.dirs) ? d.dirs : ['/']
      if (!domainDirs.value.length) domainDirs.value = ['/']
    } else if (section === 'pass') {
      const res = await passList()
      const m = pickPayload(res)
      let list = m.list
      // 宝塔返回可能是 { sitename: [...] }
      if (list && !Array.isArray(list) && typeof list === 'object') {
        const first = Object.values(list).find((v) => Array.isArray(v))
        list = first || Object.values(list)
      }
      if (!Array.isArray(list)) list = []
      passListData.value = list.map((x) => ({
        name: x.name || '',
        site_dir: x.site_dir || x.dir || '',
        username: x.username || x.user || '',
      }))
    } else if (section === 'ssl') {
      const [gs, lu] = await Promise.all([getSsl(), listUrl()])
      const d = pickPayload(gs)
      ssl.key = d.key && d.key !== false ? String(d.key) : ''
      ssl.pem = d.csr && d.csr !== false ? String(d.csr) : d.pem && d.pem !== false ? String(d.pem) : ''
      ssl.https = normalizeBool(d.httpTohttps)
      const cd = d.cert_data && typeof d.cert_data === 'object' ? d.cert_data : {}
      ssl.info = cd.subject
        ? `认证域名: ${cd.subject} · 品牌: ${cd.issuer || '-'} · 到期: ${cd.notAfter || '-'}`
        : ''
      const ud = pickPayload(lu)
      const arr = ud.domains || ud.url || []
      ssl.domainList = (Array.isArray(arr) ? arr : [])
        .map((x) => (typeof x === 'string' ? x : x.name || x.domain || ''))
        .filter(Boolean)
    } else if (section === 'fdl') {
      const res = await getFdl()
      const d = pickPayload(res)
      // 宝塔防盗链字段兼容
      fdl.fix = d.fix || d.fixs || d.suffix || ''
      let doms = d.domains || d.domain || ''
      if (Array.isArray(doms)) doms = doms.join('\n')
      fdl.domains = String(doms).replace(/,/g, '\n')
      fdl.return_rule = d.return_rule || d.return_url || d.return || '404'
      fdl.http_status = normalizeBool(d.http_status) ? 'true' : 'false'
      fdl.status = normalizeBool(d.status) ? 'true' : 'false'
    } else if (section === 'wjt') {
      const res = await setInit('wjt')
      const m = pickPayload(res)
      let t = m.templates
      // GetRewriteList 常见: { rewrite: ['wordpress', ...] } 或数组
      if (t && !Array.isArray(t) && typeof t === 'object') {
        t = t.rewrite || t.list || Object.keys(t)
      }
      if (!Array.isArray(t)) t = []
      rewriteTpls.value = t.map((x) => (typeof x === 'string' ? x : x.name || String(x)))
      // 加载当前站点规则
      const cur = await loadRewriteTpl('0.当前')
      if (cur.ok && typeof cur.data === 'string') {
        rewriteBody.value = cur.data
      } else if (cur.ok && cur.data != null) {
        rewriteBody.value = String(cur.data)
      }
      rewriteTpl.value = ''
    } else {
      const res = await setInit(section)
      const m = pickPayload(res)
      if (section === 'php') {
        php.version = m.php || ''
        let pl = m.list
        if (!Array.isArray(pl)) pl = Object.values(pl || {})
        php.list = pl.filter(Boolean)
      } else if (section === 'mrwd') {
        let idx = m.index
        if (Array.isArray(idx)) idx = idx.join(',')
        idx = idx == null ? '' : String(idx)
        // 接口误调用时可能把错误文案塞进内容，忽略
        if (!idx || /不能为空|失败|错误/.test(idx)) {
          idx = 'index.php,index.html,index.htm,default.php,default.htm,default.html'
        }
        mrwd.value = idx
      } else if (section === 'yxml') {
        // runPath 可能是嵌套 { runPath: { runPath: '/', dirs: [] } }
        let dirs = m.dirs || []
        let current = m.current || ''
        if (m.runPath && typeof m.runPath === 'object') {
          current = m.runPath.runPath || m.runPath.path || current
          dirs = m.runPath.dirs || dirs
        }
        if (!Array.isArray(dirs)) dirs = []
        runDirs.value = dirs.length ? dirs : ['/']
        runPath.value = current || runDirs.value[0] || '/'
      } else if (section === 'gzip') {
        const g = m.gzip || m
        gzip.on = normalizeBool(g.status ?? g.gzip ?? g.open ?? g.enabled)
        gzip.level = Number(g.level || g.comp_level || g.compLevel || 6) || 6
        gzip.min_len = g.minLength || g.min_length || g.min_len || '1k'
        gzip.types = g.types || g.gzip_types || gzip.types
      } else if (section === 'cache') {
        let list = m.list
        if (!list && Array.isArray(m)) list = m
        if (!Array.isArray(list) && list && typeof list === 'object') {
          list = Object.entries(list).map(([k, v]) =>
            typeof v === 'object' ? { suffix: v.suffix || k, time_out: v.time_out || v.expire || v.time } : { suffix: k, time_out: v }
          )
        }
        if (!Array.isArray(list)) list = []
        cacheList.value = list.map((x) => ({
          suffix: x.suffix || x.ext || x.name || '',
          time_out: x.time_out || x.expire || x.time || '',
        }))
      } else if (section === 'mysqlcz') {
        dbAccess.value = m.access || '127.0.0.1'
      }
    }
  } catch (e) {
    console.error('loadSection', section, e)
  }
  loading.value = false
}

async function savePhp() {
  saving.value = true
  const res = await phpChange(php.version)
  saving.value = false
  if (res.ok) ElMessage.success(res.message || '修改成功')
}

async function addDomain() {
  saving.value = true
  const payload =
    gn.value === 'CDN_url'
      ? { url: domainForm.url, yz_ip: domainForm.yz_ip }
      : { url: domainForm.url, dirs: domainForm.dirs }
  const res = await domainAdd(payload)
  saving.value = false
  if (res.ok) {
    ElMessage.success(res.message)
    domainDialog.value = false
    await loadSection()
  }
}

async function delDomain(row) {
  try {
    await ElMessageBox.confirm(`删除域名 ${row.name}？`, '删除', { type: 'warning' })
  } catch {
    return
  }
  const res = await domainDel({ url: row.name, port: row.port, dir: row.path || '/' })
  if (res.ok) {
    ElMessage.success(res.message)
    await loadSection()
  }
}

async function addPass() {
  saving.value = true
  const res = await addPassDir({ ...passForm })
  saving.value = false
  if (res.ok) {
    ElMessage.success(res.message)
    passDialog.value = false
    await loadSection()
  }
}

async function delPass(row) {
  const res = await delPassDir(row.name || row.siteName)
  if (res.ok) {
    ElMessage.success(res.message)
    await loadSection()
  }
}

async function saveMrwd() {
  saving.value = true
  const res = await setDefaultDoc(mrwd.value)
  saving.value = false
  if (res.ok) ElMessage.success(res.message || '设置成功')
}

async function saveRunPath() {
  saving.value = true
  const res = await setRunPath(runPath.value)
  saving.value = false
  if (res.ok) ElMessage.success(res.message || '设置成功')
}

async function loadTpl(name) {
  if (!name) return
  const res = await loadRewriteTpl(name)
  if (res.ok) {
    if (typeof res.data === 'string') rewriteBody.value = res.data
    else if (res.data != null) rewriteBody.value = String(res.data)
    else if (typeof res.raw === 'string') rewriteBody.value = res.raw
  } else {
    ElMessage.error(res.message || '加载模板失败')
  }
}

async function saveWjt() {
  saving.value = true
  const res = await saveRewrite(rewriteBody.value)
  saving.value = false
  if (res.ok) ElMessage.success(res.message || '已保存')
}

async function saveSsl() {
  saving.value = true
  const res = await setSsl(ssl.key, ssl.pem)
  saving.value = false
  if (res.ok || res.raw?.qk == 1) ElMessage.success(res.message || res.raw?.code || '保存成功')
}

async function onCloseSsl() {
  saving.value = true
  const res = await closeSsl()
  saving.value = false
  if (res.ok || res.raw?.qk == 1) {
    ElMessage.success(res.message || '已关闭')
    await loadSection()
  }
}

async function onForceHttps(val) {
  const res = await forceHttps(val)
  if (res.ok) ElMessage.success(res.message || '已更新')
  else {
    // 回滚开关显示
    ssl.https = !val
  }
}

async function onApplySsl() {
  if (!ssl.domains.length) {
    ElMessage.warning('请选择域名')
    return
  }
  saving.value = true
  const res = await applySsl(ssl.domains, false)
  saving.value = false
  if (res.ok) {
    ElMessage.success(res.message || '申请已提交')
    await loadSection()
  }
}

async function saveFdl() {
  saving.value = true
  const res = await setFdl({
    fix: fdl.fix,
    domains: fdl.domains.split(/[\n,]+/).map((s) => s.trim()).filter(Boolean).join(','),
    return_rule: fdl.return_rule,
    http_status: fdl.http_status,
    status: fdl.status,
  })
  saving.value = false
  if (res.ok) ElMessage.success(res.message || '设置成功')
}

async function saveGzip() {
  saving.value = true
  const res = await setGzip(
    gzip.on
      ? { action: 'on', level: gzip.level, min_len: gzip.min_len, types: gzip.types }
      : { action: 'off' }
  )
  saving.value = false
  if (res.ok) ElMessage.success(res.message || '修改成功')
}

async function addCache() {
  saving.value = true
  const res = await cacheAdd({
    suffix: cacheForm.suffix,
    ext: cacheForm.suffix,
    time_out: cacheForm.time_out,
    time: cacheForm.time_out,
  })
  saving.value = false
  if (res.ok) {
    ElMessage.success(res.message)
    cacheDialog.value = false
    await loadSection()
  }
}

async function delCache(row) {
  const res = await cacheDel(row.suffix)
  if (res.ok) {
    ElMessage.success(res.message)
    await loadSection()
  }
}

async function savePasswords() {
  if (!passwords.ftp && !passwords.sql) {
    ElMessage.warning('请至少填写一项')
    return
  }
  saving.value = true
  const res = await changePass(passwords.ftp, passwords.sql)
  saving.value = false
  if (res.ok) ElMessage.success(res.message || '修改成功')
}

async function saveDbAccess() {
  saving.value = true
  const res = await setDbAccess(dbAccess.value)
  saving.value = false
  if (res.ok) ElMessage.success(res.message || '设置成功')
}

watch(gn, loadSection, { immediate: true })
</script>

<style scoped>
.head {
  margin-bottom: 16px;
}
.head p {
  margin: 6px 0 0;
}
.sec-toolbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 12px;
  flex-wrap: wrap;
}
.btn-row {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 8px;
}
.cert-info {
  margin-top: 12px;
}
.mono :deep(textarea) {
  font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
  font-size: 12px;
}
</style>
