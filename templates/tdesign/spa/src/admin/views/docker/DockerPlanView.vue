<template>
  <div class="td-page">
    <div class="td-page-head">
      <div>
        <h3 class="td-page-title"><i class="mdi mdi-ticket-confirmation-outline"></i>Docker 套餐</h3>
        <p class="td-page-subtitle">配置 Docker 容器配额与价格</p>
      </div>
      <div class="td-page-actions">
        <t-button theme="default" variant="outline" @click="load">
          <i class="mdi mdi-refresh"></i> 刷新
        </t-button>
        <t-button theme="primary" @click="openEdit(null)">
          <i class="mdi mdi-plus"></i> 添加套餐
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
      >
        <template #jc="{ row }">{{ row.jc || '-' }}</template>
        <template #qk="{ row }">
          <t-tag :theme="row.qk === 'true' ? 'success' : 'default'" variant="light" size="small">
            {{ row.qk === 'true' ? '上架' : '下架' }}
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

    <!-- 添加/编辑套餐弹窗 -->
    <t-dialog
      v-model:visible="editVisible"
      :header="editForm && editForm.id ? '编辑套餐' : '添加套餐'"
      :on-confirm="onEdit"
      width="520px"
      :confirm-btn="{ loading: saving }"
    >
      <div class="td-form" v-if="editForm">
        <div class="td-form-row">
          <label>套餐名 <span class="td-text-danger">*</span></label>
          <t-input v-model="editForm.name" />
        </div>
        <div class="td-form-row">
          <label>介绍</label>
          <t-textarea v-model="editForm.jc" :autosize="{ minRows: 2, maxRows: 3 }" />
        </div>
        <div class="td-form-grid">
          <div class="td-form-row">
            <label>CPU 核上限</label>
            <t-input-number v-model="editForm.cpu_max" :min="0.1" :step="0.1" />
          </div>
          <div class="td-form-row">
            <label>内存 MB 上限</label>
            <t-input-number v-model="editForm.mem_max" :min="32" :step="32" />
          </div>
        </div>
        <div class="td-form-grid">
          <div class="td-form-row">
            <label>磁盘配额 MB（0=不限制）</label>
            <t-input-number v-model="editForm.disk_max" :min="0" :step="128" />
          </div>
          <div class="td-form-row">
            <label>代理数量上限（0=不限制）</label>
            <t-input-number v-model="editForm.proxy_max" :min="0" :step="1" />
          </div>
        </div>
        <div class="td-form-row">
          <label>价格</label>
          <t-input-number v-model="editForm.jg" :min="0" :step="0.01" />
        </div>
        <div class="td-form-row">
          <label>上架</label>
          <t-select v-model="editForm.qk">
            <t-option value="true" label="上架" />
            <t-option value="false" label="下架" />
          </t-select>
        </div>
      </div>
    </t-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { MessagePlugin, DialogPlugin } from 'tdesign-vue-next'
import { listDockerPlan, addDockerPlan, editDockerPlan, delDockerPlan } from '@/admin/api/docker'

const loading = ref(false)
const saving = ref(false)
const rows = ref([])

const columns = [
  { colKey: 'id', title: 'ID', width: 70 },
  { colKey: 'name', title: '套餐名', minWidth: 140 },
  { colKey: 'jc', title: '介绍', minWidth: 200, ellipsis: true },
  { colKey: 'cpu_max', title: 'CPU 核', width: 100, align: 'center' },
  { colKey: 'mem_max', title: '内存 MB', width: 110, align: 'center' },
  { colKey: 'disk_max', title: '磁盘 MB', width: 110, align: 'center', cell: (h, { row }) => row.disk_max && row.disk_max !== '0' ? row.disk_max + ' MB' : '不限制' },
  { colKey: 'proxy_max', title: '代理数', width: 90, align: 'center', cell: (h, { row }) => row.proxy_max && row.proxy_max !== '0' ? row.proxy_max + '个' : '不限制' },
  { colKey: 'jg', title: '价格', width: 100, align: 'center' },
  { colKey: 'qk', title: '上架', width: 90, align: 'center' },
  { colKey: 'operate', title: '操作', width: 140, fixed: 'right' },
]

const editVisible = ref(false)
const editForm = ref(null)

async function load() {
  loading.value = true
  const r = await listDockerPlan()
  loading.value = false
  if (r.ok && r.data) {
    rows.value = r.data.data || []
  } else if (!r.ok) {
    MessagePlugin.error(r.message || '加载失败')
  }
}

function openEdit(row) {
  editForm.value = reactive({
    id: row ? row.id : 0,
    name: row ? row.name || '' : '',
    jc: row ? row.jc || '' : '',
    cpu_max: row ? Number(row.cpu_max || 1) : 1,
    mem_max: row ? Number(row.mem_max || 512) : 512,
    disk_max: row ? Number(row.disk_max || 0) : 0,
    proxy_max: row ? Number(row.proxy_max || 0) : 0,
    jg: row ? Number(row.jg || 0) : 0,
    qk: row ? (row.qk === 'true' ? 'true' : 'false') : 'true',
  })
  editVisible.value = true
}

async function onEdit() {
  const f = editForm.value
  if (!f.name) {
    MessagePlugin.warning('套餐名必填')
    return
  }
  saving.value = true
  const data = {
    id: f.id || undefined,
    name: f.name,
    jc: f.jc,
    cpu_max: String(f.cpu_max),
    mem_max: String(f.mem_max),
    disk_max: String(f.disk_max),
    proxy_max: String(f.proxy_max),
    jg: String(f.jg),
    qk: f.qk,
  }
  const r = f.id ? await editDockerPlan(data) : await addDockerPlan(data)
  saving.value = false
  if (r.ok) {
    MessagePlugin.success(r.message || '保存成功')
    editVisible.value = false
    load()
  } else {
    MessagePlugin.error(r.message || '保存失败')
  }
}

function del(row) {
  const dlg = DialogPlugin.confirm({
    header: '删除套餐',
    body: `确定删除套餐「${row.name || row.id}」吗？`,
    theme: 'warning',
    onConfirm: async () => {
      const r = await delDockerPlan(row.id)
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

onMounted(load)
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
</style>
