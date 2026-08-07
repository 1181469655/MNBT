<template>
  <div class="td-page">
    <div class="td-page-head">
      <div>
        <h3 class="td-page-title"><i class="mdi mdi-tune-vertical"></i>站点设置</h3>
        <p class="td-page-subtitle">{{ currentTabTitle }}</p>
      </div>
      <div class="td-head-actions">
        <t-select :value="tab" style="width: 160px" @change="onTabChange">
          <t-option v-for="t in tabOptions" :key="t.value" :value="t.value" :label="t.label" />
        </t-select>
      </div>
    </div>

    <!-- PHP 版本切换 -->
    <div v-if="tab === 'php'" class="td-set-card">
      <div class="td-set-card-hd">
        <div class="td-set-icon"><i class="mdi mdi-language-php"></i></div>
        <div>
          <h4>PHP 版本切换</h4>
          <p>切换站点的 PHP 运行版本</p>
        </div>
      </div>
      <div class="td-set-card-bd">
        <t-loading :loading="loadings.php" text="加载中…" size="small">
          <div class="td-form">
            <div class="td-form-row">
              <label>选择 PHP 版本</label>
              <t-select v-model="forms.php.ver" placeholder="请选择">
                <t-option v-for="v in lists.php" :key="v" :value="v" :label="v" />
              </t-select>
              <div class="td-form-hint">当前版本会立即生效</div>
            </div>
            <div class="td-form-actions">
              <t-button theme="primary" :loading="savings.php" @click="savePhp">
                <i class="mdi mdi-content-save-outline"></i> 保存
              </t-button>
            </div>
          </div>
        </t-loading>
      </div>
    </div>

    <!-- 密码访问 -->
    <div v-else-if="tab === 'pass'" class="td-set-card">
      <div class="td-set-card-hd">
        <div class="td-set-icon"><i class="mdi mdi-lock"></i></div>
        <div>
          <h4>密码访问</h4>
          <p>为站点目录开启 HTTP 基本认证访问</p>
        </div>
      </div>
      <div class="td-set-card-bd">
        <t-loading :loading="loadings.pass" text="加载中…" size="small">
          <div class="td-form">
            <div v-if="lists.pass.length" class="td-form-row">
              <label>已设置的密码访问目录</label>
              <div v-for="(item, i) in lists.pass" :key="i" class="td-pass-item">
                <span>{{ item }}</span>
                <t-button theme="danger" variant="text" size="small" :loading="savings.pass" @click="delPass(item)">
                  <i class="mdi mdi-delete"></i> 删除
                </t-button>
              </div>
            </div>
            <div v-else class="td-form-hint">暂无密码访问目录</div>
            <div class="td-form-row">
              <label>目录名称</label>
              <t-input v-model="forms.pass.name" placeholder="如 secret" clearable />
            </div>
            <div class="td-form-row">
              <label>目录路径</label>
              <t-input v-model="forms.pass.mbml" placeholder="如 /secret" clearable />
            </div>
            <div class="td-form-row">
              <label>用户名</label>
              <t-input v-model="forms.pass.user" placeholder="访问用户名" clearable />
            </div>
            <div class="td-form-row">
              <label>访问密码</label>
              <t-input v-model="forms.pass.pass" placeholder="访问密码" clearable />
              <div class="td-form-hint">建议使用强密码</div>
            </div>
            <div class="td-form-actions">
              <t-button theme="primary" :loading="savings.pass" @click="savePass">
                <i class="mdi mdi-plus"></i> 添加
              </t-button>
            </div>
          </div>
        </t-loading>
      </div>
    </div>

    <!-- 默认文档 -->
    <div v-else-if="tab === 'default-doc'" class="td-set-card">
      <div class="td-set-card-hd">
        <div class="td-set-icon"><i class="mdi mdi-file-document-outline"></i></div>
        <div>
          <h4>默认文档</h4>
          <p>设置目录默认访问的文档名,用逗号分隔</p>
        </div>
      </div>
      <div class="td-set-card-bd">
        <t-loading :loading="loadings['default-doc']" text="加载中…" size="small">
          <div class="td-form">
            <div class="td-form-row">
              <label>默认文档列表</label>
              <t-textarea
                v-model="forms.defaultDoc.doc"
                :autosize="{ minRows: 3, maxRows: 8 }"
                placeholder="index.html,index.php,index.htm"
              />
              <div class="td-form-hint">每行一个,或用英文逗号分隔</div>
            </div>
            <div class="td-form-actions">
              <t-button theme="primary" :loading="savings['default-doc']" @click="saveDefaultDoc">
                <i class="mdi mdi-content-save-outline"></i> 保存
              </t-button>
            </div>
          </div>
        </t-loading>
      </div>
    </div>

    <!-- 运行目录 -->
    <div v-else-if="tab === 'run-dir'" class="td-set-card">
      <div class="td-set-card-hd">
        <div class="td-set-icon"><i class="mdi mdi-folder-outline"></i></div>
        <div>
          <h4>运行目录</h4>
          <p>设置站点的运行目录(相对站点根目录)</p>
        </div>
      </div>
      <div class="td-set-card-bd">
        <t-loading :loading="loadings['run-dir']" text="加载中…" size="small">
          <div class="td-form">
            <div class="td-form-row">
              <label>运行目录</label>
              <t-input v-model="forms.runDir.dir" placeholder="如 /public" clearable />
              <div class="td-form-hint">留空表示站点根目录</div>
            </div>
            <div class="td-form-actions">
              <t-button theme="primary" :loading="savings['run-dir']" @click="saveRunDir">
                <i class="mdi mdi-content-save-outline"></i> 保存
              </t-button>
            </div>
          </div>
        </t-loading>
      </div>
    </div>

    <!-- 伪静态 -->
    <div v-else-if="tab === 'rewrite'" class="td-set-card">
      <div class="td-set-card-hd">
        <div class="td-set-icon"><i class="mdi mdi-file-replace-outline"></i></div>
        <div>
          <h4>伪静态</h4>
          <p>设置 URL 重写规则</p>
        </div>
      </div>
      <div class="td-set-card-bd">
        <t-loading :loading="loadings.rewrite" text="加载中…" size="small">
          <div class="td-form">
            <div class="td-form-row">
              <label>规则模板</label>
              <t-select v-model="forms.rewrite.type" placeholder="请选择" @change="onRewriteTemplateChange">
                <t-option value="0.当前" label="当前规则" />
                <t-option v-for="t in lists.rewrite" :key="t" :value="t" :label="t" />
              </t-select>
              <div class="td-form-hint">选择模板会加载对应规则到下方编辑区,选择「当前规则」可查看已保存的配置</div>
            </div>
            <div class="td-form-row">
              <label>规则内容</label>
              <t-textarea
                v-model="forms.rewrite.content"
                :autosize="{ minRows: 6, maxRows: 16 }"
                placeholder="请填写伪静态规则"
              />
            </div>
            <div class="td-form-actions">
              <t-button theme="primary" :loading="savings.rewrite" @click="saveRewrite">
                <i class="mdi mdi-content-save-outline"></i> 保存
              </t-button>
            </div>
          </div>
        </t-loading>
      </div>
    </div>

    <!-- SSL 配置 -->
    <div v-else-if="tab === 'ssl'" class="td-set-card">
      <div class="td-set-card-hd">
        <div class="td-set-icon"><i class="mdi mdi-certificate"></i></div>
        <div>
          <h4>SSL 配置</h4>
          <p>配置站点 HTTPS 证书</p>
        </div>
      </div>
      <div class="td-set-card-bd">
        <t-loading :loading="loadings.ssl" text="加载中…" size="small">
          <div class="td-form">
            <div class="td-form-row">
              <label>SSL 状态</label>
              <div class="td-ssl-status">
                <t-tag :theme="forms.ssl.status ? 'success' : 'default'" variant="light">
                  {{ forms.ssl.status ? '已开启' : '未开启' }}
                </t-tag>
              </div>
            </div>
            <div v-if="forms.ssl.certInfo && forms.ssl.certInfo.subject" class="td-form-row">
              <label>证书信息</label>
              <div class="td-ssl-cert-info">
                <span><b>认证域名：</b>{{ forms.ssl.certInfo.subject || '未知' }}</span>
                <span><b>证书品牌：</b>{{ forms.ssl.certInfo.issuer || '未知' }}</span>
                <span><b>到期时间：</b>{{ forms.ssl.certInfo.notAfter || '未知' }}</span>
              </div>
            </div>
            <div class="td-form-switch">
              <div class="td-form-switch-txt">
                <strong>强制 HTTPS</strong>
                <span>开启后所有 HTTP 请求将跳转为 HTTPS</span>
              </div>
              <t-switch v-model="forms.ssl.httpTohttps" @change="onForceHttpsChange" />
            </div>

            <t-tabs v-model="sslSubTab" class="td-ssl-tabs">
              <t-tab-panel value="manual" label="当前证书">
                <div class="td-form">
                  <div class="td-form-row">
                    <label>密钥 (KEY)</label>
                    <t-textarea
                      v-model="forms.ssl.key"
                      :autosize="{ minRows: 6, maxRows: 16 }"
                      placeholder="粘贴 SSL 密钥 (*.key) 内容"
                    />
                  </div>
                  <div class="td-form-row">
                    <label>证书 (PEM 格式)</label>
                    <t-textarea
                      v-model="forms.ssl.pem"
                      :autosize="{ minRows: 6, maxRows: 16 }"
                      placeholder="粘贴 PEM 格式证书内容"
                    />
                    <div class="td-form-hint">PEM = 域名证书.crt + 根证书(root_bundle).crt</div>
                  </div>
                  <div class="td-form-actions">
                    <t-button theme="primary" :loading="savings.ssl" @click="saveSsl">
                      <i class="mdi mdi-shield-plus"></i> 保存并启用证书
                    </t-button>
                    <t-button v-if="forms.ssl.status" theme="danger" variant="outline" :loading="savings.ssl" @click="closeSslHandler">
                      <i class="mdi mdi-shield-remove"></i> 关闭 SSL
                    </t-button>
                  </div>
                </div>
              </t-tab-panel>

              <t-tab-panel value="letsencrypt" label="Let's Encrypt 免费证书">
                <div class="td-form">
                  <div class="td-form-row">
                    <label>选择申请域名</label>
                    <div class="td-ssl-domains">
                      <t-checkbox v-model="sslAllDomains">全选</t-checkbox>
                      <t-checkbox-group v-model="forms.ssl.selectedDomains">
                        <t-checkbox v-for="d in lists.sslDomains" :key="d" :value="d" :label="d" />
                      </t-checkbox-group>
                    </div>
                    <div class="td-form-hint">申请前请确保域名已解析,有效期 3 个月,支持自动续签</div>
                  </div>
                  <div class="td-form-actions">
                    <t-button
                      v-if="!forms.ssl.status || forms.ssl.letsEncryptType !== 1"
                      theme="primary"
                      :loading="savings.ssl"
                      @click="applyLetsEncrypt"
                    >
                      <i class="mdi mdi-shield-plus-outline"></i> 申请证书
                    </t-button>
                    <t-button
                      v-if="forms.ssl.status && forms.ssl.letsEncryptType === 1"
                      theme="primary"
                      variant="outline"
                      :loading="savings.ssl"
                      @click="renewLetsEncrypt"
                    >
                      <i class="mdi mdi-circle-edit-outline"></i> 续签证书
                    </t-button>
                  </div>
                  <div class="td-form-hint td-ssl-le-tip">
                    Let's Encrypt 因更换根证书,部分老旧设备访问时可能提示不可信。
                    若站点使用 CDN 或 301 重定向会导致续签失败。
                  </div>
                </div>
              </t-tab-panel>
            </t-tabs>
          </div>
        </t-loading>
      </div>
    </div>

    <!-- 防盗链 -->
    <div v-else-if="tab === 'hotlink'" class="td-set-card">
      <div class="td-set-card-hd">
        <div class="td-set-icon"><i class="mdi mdi-shield-link-variant-outline"></i></div>
        <div>
          <h4>防盗链</h4>
          <p>限制资源被外部站点引用</p>
        </div>
      </div>
      <div class="td-set-card-bd">
        <t-loading :loading="loadings.hotlink" text="加载中…" size="small">
          <div class="td-form">
            <div class="td-form-switch">
              <div class="td-form-switch-txt">
                <strong>启用防盗链</strong>
                <span>开启后限制外部站点引用资源</span>
              </div>
              <t-switch v-model="forms.hotlink.kg" />
            </div>
            <div class="td-form-row">
              <label>保护的扩展名</label>
              <t-input v-model="forms.hotlink.exts" placeholder="jpg,png,gif,css,js" clearable />
              <div class="td-form-hint">用英文逗号分隔</div>
            </div>
            <div class="td-form-row">
              <label>允许的域名</label>
              <t-textarea
                v-model="forms.hotlink.domains"
                :autosize="{ minRows: 3, maxRows: 8 }"
                placeholder="每行一个域名"
              />
              <div class="td-form-hint">每行一个允许的来路域名</div>
            </div>
            <div class="td-form-actions">
              <t-button theme="primary" :loading="savings.hotlink" @click="saveHotlink">
                <i class="mdi mdi-content-save-outline"></i> 保存
              </t-button>
            </div>
          </div>
        </t-loading>
      </div>
    </div>

    <!-- Gzip -->
    <div v-else-if="tab === 'gzip'" class="td-set-card">
      <div class="td-set-card-hd">
        <div class="td-set-icon"><i class="mdi mdi-folder-zip-outline"></i></div>
        <div>
          <h4>Gzip 配置</h4>
          <p>启用页面压缩,减少传输量</p>
        </div>
      </div>
      <div class="td-set-card-bd">
        <div class="td-form">
          <div class="td-form-hint">点击下方按钮启用 Gzip 压缩</div>
          <div class="td-form-actions">
            <t-button theme="primary" :loading="savings.gzip" @click="saveGzip">
              <i class="mdi mdi-check"></i> 启用 Gzip
            </t-button>
          </div>
        </div>
      </div>
    </div>

    <!-- 缓存 -->
    <div v-else-if="tab === 'cache'" class="td-set-card">
      <div class="td-set-card-hd">
        <div class="td-set-icon"><i class="mdi mdi-comment-flash-outline"></i></div>
        <div>
          <h4>缓存配置</h4>
          <p>设置站点页面缓存</p>
        </div>
      </div>
      <div class="td-set-card-bd">
        <div class="td-form">
          <div class="td-form-row">
            <label>缓存后缀</label>
            <t-input v-model="forms.cache.suffix" placeholder="如 html,htm" clearable />
            <div class="td-form-hint">用英文逗号分隔多个后缀</div>
          </div>
          <div class="td-form-row">
            <label>缓存时间(秒)</label>
            <t-input v-model="forms.cache.time_out" placeholder="如 3600" clearable />
            <div class="td-form-hint">单位:秒</div>
          </div>
          <div class="td-form-actions">
            <t-button theme="primary" :loading="savings.cache" @click="saveCache">
              <i class="mdi mdi-content-save-outline"></i> 保存
            </t-button>
          </div>
        </div>
      </div>
    </div>

    <!-- 修改密码 -->
    <div v-else-if="tab === 'password'" class="td-set-card">
      <div class="td-set-card-hd">
        <div class="td-set-icon"><i class="mdi mdi-key-variant"></i></div>
        <div>
          <h4>修改密码</h4>
          <p>修改 FTP 密码与 SQL 密码</p>
        </div>
      </div>
      <div class="td-set-card-bd">
        <div class="td-form">
          <div class="td-form-row">
            <label>FTP 密码</label>
            <t-input v-model="forms.password.ftp" type="password" placeholder="留空表示不修改 FTP 密码" clearable />
          </div>
          <div class="td-form-row">
            <label>SQL 密码</label>
            <t-input v-model="forms.password.sql" type="password" placeholder="留空表示不修改 SQL 密码" clearable />
            <div class="td-form-hint">至少填写一项</div>
          </div>
          <div class="td-form-actions">
            <t-button theme="primary" :loading="savings.password" @click="savePassword">
              <i class="mdi mdi-content-save-outline"></i> 修改密码
            </t-button>
          </div>
        </div>
      </div>
    </div>

    <!-- SQL 权限 -->
    <div v-else-if="tab === 'sql-auth'" class="td-set-card">
      <div class="td-set-card-hd">
        <div class="td-set-icon"><i class="mdi mdi-database-alert-outline"></i></div>
        <div>
          <h4>SQL 权限设置</h4>
          <p>应用数据库权限设置</p>
        </div>
      </div>
      <div class="td-set-card-bd">
        <div class="td-form">
          <div class="td-form-hint">点击下方按钮应用 SQL 权限设置</div>
          <div class="td-form-actions">
            <t-button theme="primary" :loading="savings['sql-auth']" @click="saveSqlAuth">
              <i class="mdi mdi-check"></i> 应用设置
            </t-button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref, computed, watch, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { MessagePlugin } from 'tdesign-vue-next'
import {
  getPhpList, setPhpVersion,
  getPassList, addPassDir, delPassDir,
  getDefaultDoc, setDefaultDoc,
  getRunDir, setRunDir,
  getRewriteTemplates, getRewrite, setRewrite,
  getSsl, setSsl, closeSsl, forceHttps, applySsl,
  getDomainList,
  getHotlink, setHotlink,
  setGzip,
  setCache,
  changePassword,
  setSqlAuth,
} from '@/user/api/site'

const route = useRoute()
const router = useRouter()

const tabOptions = [
  { value: 'php', label: 'PHP 版本' },
  { value: 'pass', label: '密码访问' },
  { value: 'default-doc', label: '默认文档' },
  { value: 'run-dir', label: '运行目录' },
  { value: 'rewrite', label: '伪静态' },
  { value: 'ssl', label: 'SSL' },
  { value: 'hotlink', label: '防盗链' },
  { value: 'gzip', label: 'Gzip' },
  { value: 'cache', label: '缓存' },
  { value: 'password', label: '修改密码' },
  { value: 'sql-auth', label: 'SQL 权限' },
]

const tab = ref(route.params.tab || 'php')
const sslSubTab = ref('manual')

const currentTabTitle = computed(() => {
  const o = tabOptions.find((x) => x.value === tab.value)
  return o ? o.label + ' 设置' : '站点设置'
})

// 各 tab 的表单数据
const forms = reactive({
  php: { ver: '' },
  pass: { name: '', mbml: '', user: '', pass: '' },
  defaultDoc: { doc: '' },
  runDir: { dir: '' },
  rewrite: { type: '0.当前', content: '' },
  ssl: { status: false, key: '', pem: '', httpTohttps: false, certInfo: null, selectedDomains: [] },
  hotlink: { kg: false, exts: '', domains: '' },
  cache: { suffix: '', time_out: '' },
  password: { ftp: '', sql: '' },
})

const lists = reactive({
  php: [],
  pass: [],
  rewrite: [],
  sslDomains: [],
})

const loadings = reactive({})
const savings = reactive({})

function setLoading(name, v) { loadings[name] = v }
function setSaving(name, v) { savings[name] = v }

function isOn(v) {
  return v === true || v === 'true' || v === 1 || v === '1' || v === 'on'
}

function onTabChange(v) {
  router.replace(`/settings/${v}`)
}

watch(
  () => route.params.tab,
  (v) => {
    if (v && v !== tab.value) {
      tab.value = v
      loadTab(v)
    }
  },
)

async function loadTab(name) {
  // 修改密码、Gzip、缓存、SQL权限没有获取接口,不需要远程加载
  if (name === 'password' || name === 'gzip' || name === 'cache' || name === 'sql-auth') return

  setLoading(name, true)
  try {
    if (name === 'php') {
      const r = await getPhpList()
      if (r.ok && r.data) {
        const d = r.data
        const arr = Array.isArray(d) ? d : (d.versions || d.list || d.data || [])
        lists.php = arr.map((v) => (typeof v === 'object' ? v.ver || v.version || v.name : String(v)))
        forms.php.ver = d.current || d.cur || d.ver || (lists.php[0] || '')
      }
    } else if (name === 'pass') {
      const r = await getPassList()
      if (r.ok && r.data) {
        const d = r.data
        const arr = Array.isArray(d) ? d : (d.list || d.data || [])
        lists.pass = arr.map((v) => (typeof v === 'object' ? v.mbml || v.name || v.dir || JSON.stringify(v) : String(v)))
      }
    } else if (name === 'default-doc') {
      const r = await getDefaultDoc()
      if (r.ok && r.data) {
        const d = r.data
        forms.defaultDoc.doc = d.doc || d.docs || d.default || d.mrwd || (typeof d === 'string' ? d : '') || ''
      }
    } else if (name === 'run-dir') {
      const r = await getRunDir()
      if (r.ok && r.data) {
        const d = r.data
        forms.runDir.dir = d.dir || d.path || d.run_dir || d.yxml || (typeof d === 'string' ? d : '') || ''
      }
    } else if (name === 'rewrite') {
      // 并行加载模板列表与当前规则内容
      const [tr, cr] = await Promise.all([
        getRewriteTemplates(),
        getRewrite('0.当前'),
      ])
      if (tr.ok && tr.data) {
        const d = tr.data
        const raw = Array.isArray(d) ? d : (d.templates || d.list || d.msg?.templates || [])
        // 过滤空值与 '0.当前'（已由硬编码选项提供友好标签）
        lists.rewrite = raw.filter((t) => t && t !== '0.当前')
      }
      if (cr.ok && cr.data) {
        forms.rewrite.content = typeof cr.data === 'string' ? cr.data : (cr.data.content || cr.data.rules || cr.data.rule || cr.data.wb || '')
      }
      forms.rewrite.type = '0.当前'
    } else if (name === 'ssl') {
      const [r, dr] = await Promise.all([
        getSsl(),
        getDomainList(),
      ])
      if (r.ok && r.data) {
        const d = r.data
        forms.ssl.status = isOn(d.status)
        forms.ssl.key = d.key === false ? '' : (d.key || '')
        forms.ssl.pem = d.csr === false ? '' : (d.csr || '')
        forms.ssl.httpTohttps = isOn(d.httpTohttps)
        forms.ssl.certInfo = d.cert_data || null
        forms.ssl.letsEncryptType = d.type ?? 0
      }
      if (dr.ok && dr.data) {
        const dd = dr.data
        const arr = Array.isArray(dd) ? dd : (dd.domains || dd.list || [])
        lists.sslDomains = arr.map((v) => (typeof v === 'object' ? v.name || v.domain || String(v) : String(v)))
      }
    } else if (name === 'hotlink') {
      const r = await getHotlink()
      if (r.ok && r.data) {
        const d = r.data
        forms.hotlink.kg = isOn(d.kg || d.status || d.qk)
        forms.hotlink.exts = d.exts || d.ext || d.extensions || d.fix || ''
        forms.hotlink.domains = d.domains || d.domain || d.list || ''
      }
    }
  } finally {
    setLoading(name, false)
  }
}

async function savePhp() {
  if (!forms.php.ver) {
    MessagePlugin.warning('请选择 PHP 版本')
    return
  }
  setSaving('php', true)
  const r = await setPhpVersion(forms.php.ver)
  setSaving('php', false)
  if (r.ok) MessagePlugin.success('PHP 版本已切换')
}

async function savePass() {
  if (!forms.pass.name || !forms.pass.mbml || !forms.pass.user || !forms.pass.pass) {
    MessagePlugin.warning('请完整填写目录名称、目录路径、用户名与访问密码')
    return
  }
  setSaving('pass', true)
  const r = await addPassDir(
    forms.pass.name.trim(),
    forms.pass.mbml.trim(),
    forms.pass.user.trim(),
    forms.pass.pass,
  )
  setSaving('pass', false)
  if (r.ok) {
    MessagePlugin.success('密码访问目录已添加')
    forms.pass.name = ''
    forms.pass.mbml = ''
    forms.pass.user = ''
    forms.pass.pass = ''
    loadTab('pass')
  }
}

async function delPass(mb) {
  setSaving('pass', true)
  const r = await delPassDir(mb)
  setSaving('pass', false)
  if (r.ok) {
    MessagePlugin.success('密码访问目录已删除')
    loadTab('pass')
  }
}

async function saveDefaultDoc() {
  setSaving('default-doc', true)
  const r = await setDefaultDoc(forms.defaultDoc.doc)
  setSaving('default-doc', false)
  if (r.ok) MessagePlugin.success('默认文档已保存')
}

async function saveRunDir() {
  setSaving('run-dir', true)
  const r = await setRunDir(forms.runDir.dir)
  setSaving('run-dir', false)
  if (r.ok) MessagePlugin.success('运行目录已保存')
}

async function onRewriteTemplateChange(v) {
  if (!v || v === '0.当前') return
  setLoading('rewrite', true)
  try {
    const r = await getRewrite(v)
    if (r.ok && r.data) {
      forms.rewrite.content = typeof r.data === 'string' ? r.data : (r.data.content || r.data.rules || r.data.rule || r.data.wb || '')
    }
  } finally {
    setLoading('rewrite', false)
  }
}

async function saveRewrite() {
  setSaving('rewrite', true)
  const r = await setRewrite(forms.rewrite.content)
  setSaving('rewrite', false)
  if (r.ok) MessagePlugin.success('伪静态规则已保存')
}

async function saveSsl() {
  if (!forms.ssl.key || !forms.ssl.pem) {
    MessagePlugin.warning('密钥(KEY)和证书(PEM)均不能留空')
    return
  }
  setSaving('ssl', true)
  const r = await setSsl(forms.ssl.key, forms.ssl.pem)
  setSaving('ssl', false)
  if (r.ok) {
    MessagePlugin.success('SSL 证书已保存')
    loadTab('ssl')
  }
}

async function closeSslHandler() {
  setSaving('ssl', true)
  const r = await closeSsl()
  setSaving('ssl', false)
  if (r.ok) {
    MessagePlugin.success('SSL 已关闭')
    loadTab('ssl')
  }
}

async function onForceHttpsChange(v) {
  if (!forms.ssl.status) {
    MessagePlugin.warning('请先开启 SSL 再设置强制 HTTPS')
    forms.ssl.httpTohttps = false
    return
  }
  setSaving('ssl', true)
  const r = await forceHttps(v)
  setSaving('ssl', false)
  if (r.ok) {
    MessagePlugin.success(v ? '强制 HTTPS 已开启' : '强制 HTTPS 已关闭')
  } else {
    forms.ssl.httpTohttps = !v
  }
}

const sslAllDomains = computed({
  get: () => lists.sslDomains.length > 0 && lists.sslDomains.every((d) => forms.ssl.selectedDomains.includes(d)),
  set: (v) => {
    forms.ssl.selectedDomains = v ? [...lists.sslDomains] : []
  },
})

async function applyLetsEncrypt() {
  if (forms.ssl.status) {
    MessagePlugin.warning('当前 SSL 已开启,继续申请将覆盖现有证书,请先关闭 SSL')
    return
  }
  if (forms.ssl.selectedDomains.length === 0) {
    MessagePlugin.warning('请选择需要申请 SSL 的域名')
    return
  }
  setSaving('ssl', true)
  const r = await applySsl(forms.ssl.selectedDomains, false)
  setSaving('ssl', false)
  if (r.ok) {
    MessagePlugin.success('证书申请成功')
    loadTab('ssl')
  }
}

async function renewLetsEncrypt() {
  if (forms.ssl.httpTohttps) {
    MessagePlugin.warning('续签前请先关闭强制 HTTPS')
    return
  }
  const dns = forms.ssl.certInfo?.dns || []
  if (dns.length === 0) {
    MessagePlugin.warning('未找到可续签的域名信息')
    return
  }
  setSaving('ssl', true)
  const r = await applySsl(dns, true)
  setSaving('ssl', false)
  if (r.ok) {
    MessagePlugin.success('证书续签成功')
    loadTab('ssl')
  }
}

async function saveHotlink() {
  setSaving('hotlink', true)
  const r = await setHotlink(
    forms.hotlink.exts,
    forms.hotlink.domains,
    forms.hotlink.kg ? 'true' : 'false',
  )
  setSaving('hotlink', false)
  if (r.ok) MessagePlugin.success('防盗链配置已保存')
}

async function saveGzip() {
  setSaving('gzip', true)
  const r = await setGzip()
  setSaving('gzip', false)
  if (r.ok) MessagePlugin.success('Gzip 配置已保存')
}

async function saveCache() {
  if (!forms.cache.suffix || !forms.cache.time_out) {
    MessagePlugin.warning('请填写缓存后缀与缓存时间')
    return
  }
  setSaving('cache', true)
  const r = await setCache(forms.cache.suffix, forms.cache.time_out)
  setSaving('cache', false)
  if (r.ok) MessagePlugin.success('缓存配置已保存')
}

async function savePassword() {
  if (!forms.password.ftp && !forms.password.sql) {
    MessagePlugin.warning('请至少填写一项密码')
    return
  }
  setSaving('password', true)
  const r = await changePassword(forms.password.ftp, forms.password.sql)
  setSaving('password', false)
  if (r.ok) {
    MessagePlugin.success('密码修改成功')
    forms.password.ftp = ''
    forms.password.sql = ''
  }
}

async function saveSqlAuth() {
  setSaving('sql-auth', true)
  const r = await setSqlAuth()
  setSaving('sql-auth', false)
  if (r.ok) MessagePlugin.success('SQL 权限设置已保存')
}

onMounted(() => {
  loadTab(tab.value)
})
</script>

<style scoped>
.td-head-actions {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
}
.td-pass-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 8px;
  padding: 6px 12px;
  border-bottom: 1px solid var(--td-component-border, #e7e7e7);
}
.td-pass-item:last-child {
  border-bottom: none;
}
.td-ssl-cert-info {
  display: flex;
  flex-direction: column;
  gap: 6px;
  padding: 10px 12px;
  background: var(--td-bg-color-container, #f5f5f5);
  border-radius: 6px;
  font-size: 13px;
  line-height: 1.6;
}
.td-ssl-tabs {
  margin-top: 8px;
}
.td-ssl-domains {
  display: flex;
  flex-wrap: wrap;
  gap: 8px 16px;
  padding: 10px 12px;
  background: var(--td-bg-color-container, #f5f5f5);
  border-radius: 6px;
}
.td-ssl-le-tip {
  margin-top: 8px;
  line-height: 1.6;
}
.td-form-actions {
  gap: 8px;
}
</style>
