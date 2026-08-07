<template>
  <div class="dk-proxy">
    <t-alert
      v-if="!hasContainer"
      theme="warning"
      message="您还没有容器，请先在应用商店创建容器后再配置反向代理。"
      style="margin-bottom: 16px"
    />

    <t-card>
      <template #header>
        <div class="dk-card-hd">
          <h3>反向代理</h3>
          <t-button theme="primary" @click="openAdd">
            <i class="mdi mdi-plus"></i> 添加规则
          </t-button>
        </div>
      </template>

      <t-loading :loading="loading" text="加载中…" size="large">
        <div v-if="list.length" class="dk-proxy-table">
          <t-table
            :data="list"
            :columns="columns"
            row-key="id"
            size="medium"
            hover
            stripe
          >
            <template #status="{ row }">
              <t-tag :theme="row.status === '1' ? 'success' : 'danger'" variant="light" size="small">
                {{ row.status === '1' ? '运行中' : '已停止' }}
              </t-tag>
            </template>
            <template #op="{ row }">
              <t-button theme="danger" variant="text" size="small" @click="onDelete(row)">
                <i class="mdi mdi-delete"></i> 删除
              </t-button>
            </template>
          </t-table>
        </div>
        <div v-else class="dk-empty">
          <i class="mdi mdi-swap-horizontal dk-empty-ico"></i>
          <h4>暂无反向代理规则</h4>
          <p>点击上方按钮添加，将域名指向容器端口。</p>
        </div>
      </t-loading>
    </t-card>

    <!-- 添加弹窗 -->
    <t-dialog
      v-model:visible="addVisible"
      header="添加反向代理"
      width="520px"
      :close-on-overlay-click="false"
      :footer="false"
    >
      <div class="dk-add-form">
        <div class="dk-add-field">
          <label>域名 <span class="dk-required">*</span></label>
          <t-textarea v-model="addForm.domains" placeholder="多个域名用换行分隔，如 example.com" :autosize="{ minRows: 1, maxRows: 3 }" />
        </div>
        <div class="dk-add-field">
          <label>容器端口 <span class="dk-required">*</span></label>
          <div class="dk-add-port-row">
            <t-select v-model="addForm.proto" style="width:100px;flex-shrink:0">
              <t-option value="http" label="http://" />
              <t-option value="https" label="https://" />
            </t-select>
            <t-select v-model="addForm.ip" style="width:130px;flex-shrink:0" disabled>
              <t-option value="127.0.0.1" label="127.0.0.1" />
            </t-select>
            <t-select v-model="addForm.port" style="flex:1;min-width:0">
              <t-option v-for="p in ports" :key="p" :value="String(p)" :label="String(p)" />
            </t-select>
          </div>
          <div class="dk-add-hint">代理目标：&lt;协议&gt;://&lt;IP&gt;:&lt;端口&gt;（容器与本机同机部署）</div>
        </div>
        <div class="dk-add-field">
          <label>代理路径</label>
          <t-input v-model="addForm.proxy_path" placeholder="/" />
        </div>
        <div class="dk-add-field">
          <label>备注</label>
          <t-input v-model="addForm.remark" placeholder="可选" />
        </div>
      </div>
      <div class="dk-add-foot">
        <t-button theme="default" variant="outline" @click="addVisible = false">取消</t-button>
        <t-button theme="primary" :loading="adding" @click="doAdd">确认添加</t-button>
      </div>
    </t-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { MessagePlugin, DialogPlugin } from 'tdesign-vue-next'
import { listProxy, createProxy, deleteProxy, getContainerPorts, getMyContainer } from '@/docker/api/docker'

const boot = window.__TD_BOOT__ || {}
const dockerUser = boot.dockerUser || {}
const proxyMax = Number(dockerUser.proxy_max || 0)

const loading = ref(false)
const list = ref([])
const ports = ref([])
const hasContainer = ref(false)
const addVisible = ref(false)
const adding = ref(false)
const addForm = reactive({ proto: 'http', ip: '127.0.0.1', port: '', proxy_path: '/', remark: '', domains: '' })

const columns = [
  { colKey: 'name', title: '站点名', ellipsis: true },
  { colKey: 'proxy_pass', title: '代理目标', ellipsis: true },
  { colKey: 'path', title: '路径', width: 80 },
  { colKey: 'status', title: '状态', width: 90 },
  { colKey: 'ps', title: '备注', ellipsis: true },
  { colKey: 'op', title: '操作', width: 80 },
]

async function loadList() {
  loading.value = true
  const r = await listProxy()
  loading.value = false
  if (r.ok) {
    list.value = Array.isArray(r.data?.data) ? r.data.data : (Array.isArray(r.data) ? r.data : [])
  }
}

async function loadPorts() {
  const r = await getContainerPorts()
  if (r.ok && r.data?.ports) {
    ports.value = r.data.ports || []
  }
}

async function checkContainer() {
  const r = await getMyContainer()
  if (r.ok && r.data?.me) {
    hasContainer.value = !!(r.data.me.container_id || r.data.me.service_name)
  }
}

async function openAdd() {
  if (!hasContainer.value) {
    MessagePlugin.warning('请先创建容器')
    return
  }
  await loadPorts()
  if (!ports.value.length) {
    MessagePlugin.warning('未检测到容器端口，请确认容器已创建并运行')
    return
  }
  if (proxyMax > 0) {
    await loadList()
    if (list.value.length >= proxyMax) {
      MessagePlugin.warning(`反向代理数量已达上限（${proxyMax}个）`)
      return
    }
  }
  addForm.domains = ''
  addForm.proto = 'http'
  addForm.port = String(ports.value[0] || '')
  addForm.proxy_path = '/'
  addForm.remark = ''
  addVisible.value = true
}

async function doAdd() {
  if (!addForm.domains.trim() || !addForm.port) {
    MessagePlugin.warning('域名和端口不能为空')
    return
  }
  adding.value = true
  const r = await createProxy({
    domains: addForm.domains,
    proto: addForm.proto,
    ip: addForm.ip,
    port: addForm.port,
    proxy_path: addForm.proxy_path || '/',
    remark: addForm.remark,
  })
  adding.value = false
  if (r.ok) {
    addVisible.value = false
    MessagePlugin.success('反向代理创建成功')
    loadList()
  }
}

async function onDelete(row) {
  const confirmed = await new Promise((resolve) => {
    const dlg = DialogPlugin.confirm({
      header: '确认删除',
      body: `确定删除反向代理 ${row.name} 吗？`,
      confirmBtn: '确认删除',
      cancelBtn: '取消',
      theme: 'danger',
      onConfirm: () => { dlg.destroy(); resolve(true) },
      onCancel: () => { dlg.destroy(); resolve(false) },
      onClose: () => { dlg.destroy(); resolve(false) },
    })
  })
  if (!confirmed) return
  const r = await deleteProxy(row.id, row.name)
  if (r.ok) {
    MessagePlugin.success('已删除')
    loadList()
  }
}

onMounted(() => {
  checkContainer()
  loadList()
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
.dk-empty h4 { margin: 0 0 8px; font-size: 15px; }
.dk-empty p { margin: 0; font-size: 13px; }

/* 添加表单 */
.dk-add-form { display: flex; flex-direction: column; gap: 16px; }
.dk-add-field { display: flex; flex-direction: column; gap: 6px; }
.dk-add-field label { font-size: 13px; color: var(--td-text-color-secondary, #6b7280); }
.dk-required { color: var(--td-error-color, #d54941); }
.dk-add-port-row { display: flex; gap: 8px; align-items: flex-start; }
.dk-add-hint { font-size: 12px; color: var(--td-text-color-placeholder, #999); }
.dk-add-foot { display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px; padding-top: 16px; border-top: 1px solid var(--td-border-level-1-color, #e7e7e7); }
</style>