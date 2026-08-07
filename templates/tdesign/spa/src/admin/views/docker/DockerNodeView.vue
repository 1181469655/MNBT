<template>
  <div class="td-page">
    <div class="td-page-head">
      <div>
        <h3 class="td-page-title"><i class="mdi mdi-docker"></i>Docker 节点</h3>
        <p class="td-page-subtitle">管理独立宝塔 Docker 面板节点实例</p>
      </div>
      <div class="td-page-actions">
        <t-button theme="default" variant="outline" @click="load">
          <i class="mdi mdi-refresh"></i> 刷新
        </t-button>
        <t-button theme="primary" @click="$router.push('/docker/node/add')">
          <i class="mdi mdi-plus"></i> 添加节点
        </t-button>
      </div>
    </div>

    <div class="td-table-wrap">
      <t-table
        row-key="id"
        :data="rows"
        :columns="columns"
        :loading="loading"
        table-layout="auto"
        stripe
        bordered
        :max-height="560"
      >
        <template #panel="{ row }">
          <span class="dk-mono">{{ row.btip }}:{{ row.btdk }} {{ row.ptl === 'true' ? '(HTTPS)' : '(HTTP)' }}</span>
        </template>
        <template #btmy="{ row }">
          <t-tag v-if="row.btmy" size="small" theme="success" variant="light">已设置</t-tag>
          <t-tag v-else size="small" theme="danger" variant="light">未设置</t-tag>
        </template>
        <template #ktmy="{ row }">
          {{ row.ktmy ? '已设置' : '-' }}
        </template>
        <template #qk="{ row }">
          <t-tag :theme="row.qk === 'true' ? 'success' : 'default'" variant="light" size="small">
            {{ row.qk === 'true' ? '启用' : '禁用' }}
          </t-tag>
        </template>
        <template #operate="{ row }">
          <div class="td-row-actions">
            <t-button theme="default" variant="outline" size="small" @click="openEdit(row)">编辑</t-button>
            <t-button theme="danger" variant="outline" size="small" @click="del(row)">删除</t-button>
          </div>
        </template>
      </t-table>
    </div>

    <!-- 节点容器查询 -->
    <div class="td-set-card" style="margin-top: 16px">
      <div class="td-set-card-hd">
        <div class="td-set-icon"><i class="mdi mdi-docker"></i></div>
        <div>
          <h4>节点容器查询</h4>
          <p>选择节点查看 Docker 安装状态与该节点全部容器</p>
        </div>
      </div>
      <div class="td-set-card-bd">
        <div class="td-toolbar" style="margin-bottom: 12px">
          <t-select
            v-model="queryNodeId"
            placeholder="请选择节点"
            style="width: 260px"
            :loading="loadingOptions"
          >
            <t-option
              v-for="n in optionNodes"
              :key="n.id"
              :value="n.id"
              :label="`${n.name} (${n.btip})`"
            />
          </t-select>
          <t-button theme="primary" :loading="querying" @click="queryNode">
            <i class="mdi mdi-magnify"></i> 查询
          </t-button>
        </div>

        <t-alert
          v-if="nodeConfigText"
          :theme="dockerInstalled ? 'success' : 'warning'"
          :message="nodeConfigText"
          style="margin-bottom: 12px"
        />

        <t-table
          row-key="name"
          :data="containerRows"
          :columns="containerColumns"
          :loading="querying"
          table-layout="auto"
          stripe
          bordered
          size="small"
        >
          <template #empty>
            <div class="td-empty">
              <i class="mdi mdi-information-outline"></i>请选择节点后查询
            </div>
          </template>
        </t-table>
      </div>
    </div>

    <!-- 编辑弹窗 -->
    <t-dialog
      v-model:visible="editVisible"
      header="编辑节点"
      :on-confirm="onEdit"
      width="560px"
      :confirm-btn="{ loading: saving }"
    >
      <div class="td-form" v-if="editForm">
        <div class="td-form-row">
          <label>节点名称 <span class="td-text-danger">*</span></label>
          <t-input v-model="editForm.name" />
        </div>
        <div class="td-form-row">
          <label>宝塔面板地址 <span class="td-text-danger">*</span></label>
          <t-input v-model="editForm.btip" placeholder="IP 或域名" />
        </div>
        <div class="td-form-grid">
          <div class="td-form-row">
            <label>端口</label>
            <t-input-number v-model="editForm.btdk" :min="1" :max="65535" />
          </div>
          <div class="td-form-row">
            <label>HTTPS</label>
            <t-select v-model="editForm.ptl">
              <t-option value="false" label="否" />
              <t-option value="true" label="是" />
            </t-select>
          </div>
        </div>
        <div class="td-form-row">
          <label>宝塔接口密钥 <span class="td-text-danger">*</span></label>
          <t-textarea v-model="editForm.btmy" :autosize="{ minRows: 2, maxRows: 3 }" />
        </div>
        <div class="td-form-row">
          <label>调用密钥（外部 API 鉴权）</label>
          <t-input v-model="editForm.ktmy" placeholder="留空则不校验调用密钥" />
        </div>
        <div class="td-form-row">
          <label>二级验证密钥</label>
          <t-input v-model="editForm.qmk" placeholder="与调用密钥组合 md5 校验" />
        </div>
        <div class="td-form-row">
          <label>启用</label>
          <t-select v-model="editForm.qk">
            <t-option value="true" label="启用" />
            <t-option value="false" label="禁用" />
          </t-select>
        </div>
      </div>
    </t-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { MessagePlugin, DialogPlugin } from 'tdesign-vue-next'
import {
  listDockerNode, editDockerNode, delDockerNode,
  dockerNodeConfig, dockerNodeContainers, dockerOptions,
} from '@/admin/api/docker'

const loading = ref(false)
const saving = ref(false)
const rows = ref([])

const columns = [
  { colKey: 'id', title: 'ID', width: 70 },
  { colKey: 'name', title: '节点名称', minWidth: 140, ellipsis: true },
  { colKey: 'panel', title: '面板地址', minWidth: 200 },
  { colKey: 'btmy', title: '接口密钥', width: 100, align: 'center' },
  { colKey: 'ktmy', title: '调用密钥', width: 100, align: 'center' },
  { colKey: 'qk', title: '状态', width: 90, align: 'center' },
  { colKey: 'date', title: '添加时间', width: 160 },
  { colKey: 'operate', title: '操作', width: 140, fixed: 'right' },
]

// 容器查询
const loadingOptions = ref(false)
const optionNodes = ref([])
const queryNodeId = ref('')
const querying = ref(false)
const nodeConfigText = ref('')
const dockerInstalled = ref(false)
const containerRows = ref([])
const containerColumns = [
  { colKey: 'name', title: '容器名', minWidth: 160 },
  { colKey: 'image', title: '镜像', minWidth: 180, ellipsis: true },
  { colKey: 'status', title: '状态', width: 100 },
  { colKey: 'ports', title: '端口', minWidth: 140, ellipsis: true },
  { colKey: 'time', title: '创建时间', minWidth: 170 },
]

// 编辑弹窗
const editVisible = ref(false)
const editForm = ref(null)

async function load() {
  loading.value = true
  const r = await listDockerNode()
  loading.value = false
  if (r.ok && r.data) {
    rows.value = r.data.data || []
    optionNodes.value = rows.value
  } else if (!r.ok) {
    MessagePlugin.error(r.message || '加载失败')
  }
}

async function loadOptions() {
  loadingOptions.value = true
  const r = await dockerOptions()
  loadingOptions.value = false
  if (r.ok && r.data) {
    optionNodes.value = r.data.nodes || []
  }
}

function openEdit(row) {
  editForm.value = reactive({
    id: row.id,
    name: row.name || '',
    btip: row.btip || '',
    btdk: Number(row.btdk || 8888),
    ptl: row.ptl === 'true' ? 'true' : 'false',
    btmy: row.btmy || '',
    ktmy: row.ktmy || '',
    qmk: row.qmk || '',
    qk: row.qk === 'true' ? 'true' : 'false',
  })
  editVisible.value = true
}

async function onEdit() {
  if (!editForm.value.name || !editForm.value.btip || !editForm.value.btmy) {
    MessagePlugin.warning('节点名、面板地址、接口密钥必填')
    return
  }
  saving.value = true
  const r = await editDockerNode({ ...editForm.value })
  saving.value = false
  if (r.ok) {
    MessagePlugin.success(r.message || '编辑成功')
    editVisible.value = false
    load()
  } else {
    MessagePlugin.error(r.message || '编辑失败')
  }
}

function del(row) {
  const dlg = DialogPlugin.confirm({
    header: '删除节点',
    body: `确定删除 Docker 节点「${row.name || row.id}」吗？节点下有用户时无法删除。`,
    theme: 'warning',
    onConfirm: async () => {
      const r = await delDockerNode(row.id)
      if (r.ok) {
        MessagePlugin.success('删除成功')
        load()
      } else {
        MessagePlugin.error(r.message || '删除失败')
      }
      dlg.destroy()
    },
  })
}

function pickContainerName(x) {
  const name = x.name || (x.Names && x.Names[0]) || ''
  return String(name).replace(/^\//, '')
}

async function queryNode() {
  if (!queryNodeId.value) {
    MessagePlugin.warning('请先选择节点')
    return
  }
  querying.value = true
  nodeConfigText.value = ''
  containerRows.value = []

  const [cfg, containers] = await Promise.all([
    dockerNodeConfig(queryNodeId.value),
    dockerNodeContainers(queryNodeId.value),
  ])

  if (cfg.ok && cfg.data) {
    const d = cfg.data.data && cfg.data.data.data ? cfg.data.data.data : (cfg.data.data || cfg.data)
    const installed = d && (d.docker_installed || (d.service_status && d.service_status.docker))
    dockerInstalled.value = !!installed
    nodeConfigText.value = 'Docker ' + (installed ? '已安装' : '未安装或异常')
      + (d && d.service_status ? ' · Compose: ' + (d.service_status.docker_compose ? '已安装' : '未安装') : '')
  } else {
    dockerInstalled.value = false
    nodeConfigText.value = cfg.message || '配置检测失败'
  }

  if (containers.ok && containers.data) {
    const bt = containers.data.data || containers.data
    const list = bt.data || bt || []
    containerRows.value = (Array.isArray(list) ? list : []).map((x) => ({
      name: pickContainerName(x),
      image: x.image || x.Image || '-',
      status: x.status || x.State || '-',
      ports: x.ports || x.Ports || '-',
      time: x.time || x.Created || '-',
    }))
  } else if (!containers.ok) {
    MessagePlugin.error(containers.message || '容器查询失败')
  }

  querying.value = false
}

onMounted(() => {
  load()
  loadOptions()
})
</script>

<style scoped>
.td-page-actions {
  display: flex;
  gap: 8px;
}
.td-form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0 16px;
}
.dk-mono {
  font-family: Consolas, Monaco, monospace;
  font-size: 12.5px;
}
.td-empty {
  text-align: center;
  padding: 32px 16px;
  color: var(--td-text-color-secondary, #6b7280);
}
.td-empty i {
  font-size: 32px;
  display: block;
  margin-bottom: 8px;
  opacity: 0.5;
}
</style>
