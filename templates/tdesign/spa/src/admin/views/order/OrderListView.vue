<template>
  <div class="td-page">
    <div class="td-page-head">
      <div>
        <h3 class="td-page-title"><i class="mdi mdi-format-list-bulleted"></i>订单列表</h3>
        <p class="td-page-subtitle">查看与删除支付订单</p>
      </div>
    </div>

    <div class="td-table-wrap">
      <div class="td-toolbar">
        <t-button theme="danger" variant="outline" :disabled="!selectedIds.length" @click="batchDelete">
          <i class="mdi mdi-delete"></i> 删除选中
        </t-button>
        <div class="td-toolbar-spacer"></div>
        <t-button theme="default" variant="text" @click="load">
          <i class="mdi mdi-refresh"></i> 刷新
        </t-button>
      </div>

      <t-table
        row-key="id"
        :data="rows"
        :columns="columns"
        :loading="loading"
        :pagination="pagination"
        stripe
        bordered
        @page-change="onPageChange"
        @select-change="onSelectChange"
      >
        <template #user="{ row }">
          <span>{{ parseUser(row.cs) || row.url || '-' }}</span>
        </template>

        <template #je="{ row }">
          <span class="td-text-success td-text-sm">¥ {{ row.je || 0 }}</span>
        </template>

        <template #qk="{ row }">
          <span class="td-chip td-chip-success" v-if="row.qk === true || row.qk === 'true'">
            <i class="mdi mdi-check-circle"></i> 支付成功
          </span>
          <span class="td-chip td-chip-danger" v-else>
            <i class="mdi mdi-close-circle"></i> 未支付
          </span>
        </template>

        <template #op="{ row }">
          <div class="td-row-actions">
            <t-button theme="danger" variant="text" size="small" @click="deleteRow(row)">
              <i class="mdi mdi-delete"></i> 删除
            </t-button>
          </div>
        </template>
      </t-table>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { MessagePlugin, DialogPlugin } from 'tdesign-vue-next'
import { listOrder, deleteOrder, deleteOrderBatch } from '@/admin/api/order'

const loading = ref(false)
const rows = ref([])
const selectedIds = ref([])

const pagination = reactive({
  current: 1,
  pageSize: 10,
  total: 0,
  showJumper: true,
})

const columns = [
  { colKey: 'row-select', type: 'multiple', width: 50 },
  { colKey: 'id', title: 'ID', width: 70, sorter: true },
  { colKey: 'user', title: '发起者账号', minWidth: 130, col: 'user' },
  { colKey: 'spname', title: '支付商品', minWidth: 140, ellipsis: true, sorter: true },
  { colKey: 'ddh', title: '订单号', minWidth: 180, ellipsis: true, sorter: true },
  { colKey: 'je', title: '支付金额', width: 110, sorter: true },
  { colKey: 'zffs', title: '支付方式', width: 110, sorter: true },
  { colKey: 'date', title: '发起时间', width: 160, sorter: true },
  { colKey: 'ip', title: '发起者IP', width: 130 },
  { colKey: 'qk', title: '支付情况', width: 110 },
  { colKey: 'op', title: '操作', width: 90, fixed: 'right' },
]

function parseUser(cs) {
  if (!cs) return ''
  try {
    const obj = typeof cs === 'string' ? JSON.parse(cs) : cs
    if (obj && typeof obj === 'object') return obj.user || ''
  } catch (_) {
    /* ignore */
  }
  return ''
}

async function load() {
  loading.value = true
  const r = await listOrder({ page: pagination.current, limit: pagination.pageSize })
  loading.value = false
  if (r.ok && r.data) {
    rows.value = r.data.rows || []
    pagination.total = r.data.total || 0
  }
}

function onPageChange(p) {
  pagination.current = p.current
  pagination.pageSize = p.pageSize
  load()
}

function onSelectChange(selectedRowKeys) {
  selectedIds.value = selectedRowKeys || []
}

function batchDelete() {
  if (!selectedIds.value.length) return
  const dialog = DialogPlugin.confirm({
    header: '批量删除',
    body: `确定删除选中的 ${selectedIds.value.length} 笔订单吗?此操作不可恢复。`,
    confirmBtn: { content: '删除', theme: 'danger' },
    onConfirm: async () => {
      const r = await deleteOrderBatch(selectedIds.value)
      dialog.destroy()
      if (r.ok) {
        MessagePlugin.success('删除成功')
        selectedIds.value = []
        load()
      }
    },
    onClose: () => dialog.destroy(),
  })
}

function deleteRow(row) {
  const dialog = DialogPlugin.confirm({
    header: '删除订单',
    body: `确定删除订单「${row.ddh || row.id}」吗?此操作不可恢复。`,
    confirmBtn: { content: '删除', theme: 'danger' },
    onConfirm: async () => {
      const r = await deleteOrder(row.id)
      dialog.destroy()
      if (r.ok) {
        MessagePlugin.success('删除成功')
        load()
      }
    },
    onClose: () => dialog.destroy(),
  })
}

onMounted(load)
</script>
