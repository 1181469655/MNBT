<template>
  <div class="td-page">
    <div class="td-page-head">
      <div>
        <h3 class="td-page-title"><i class="mdi mdi-clock-outline"></i>计划任务</h3>
        <p class="td-page-subtitle">在主机上定时备份网站/数据库、切割日志、访问指定 URL</p>
      </div>
    </div>

    <div class="td-table-wrap">
      <div class="td-toolbar">
        <t-button theme="primary" @click="openAdd">
          <i class="mdi mdi-plus"></i> 新增任务
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
        table-layout="auto"
        stripe
        bordered
      >
        <template #empty>
          <div class="td-empty">
            <i class="mdi mdi-clock-outline"></i>
            暂无计划任务
          </div>
        </template>
        <template #stype_text="{ row }">
          <t-tag size="small" variant="light" :theme="stypeTheme(row.stype)">{{ row.stype_text }}</t-tag>
        </template>
        <template #target="{ row }">
          <code class="cron-target" :title="row.sbody">{{ row.stype === 'url' ? row.sbody : row.sname }}</code>
        </template>
        <template #bt_status="{ row }">
          <span :class="statusClass(row.bt_status)">{{ statusText(row.bt_status) }}</span>
        </template>
        <template #operate="{ row }">
          <div class="td-row-actions">
            <t-button theme="default" variant="outline" size="small" @click="showLogs(row)" title="执行日志">
              <i class="mdi mdi-text-box-outline"></i>
            </t-button>
            <t-button theme="default" variant="outline" size="small" @click="exec(row)" title="立即执行">
              <i class="mdi mdi-play"></i>
            </t-button>
            <t-button
              theme="default" variant="outline" size="small"
              :title="row.bt_status == 1 || row.bt_status === '1' || row.bt_status === true ? '暂停' : '启用'"
              @click="toggle(row)"
            >
              <i :class="isOn(row.bt_status) ? 'mdi mdi-pause' : 'mdi mdi-play-circle-outline'"></i>
            </t-button>
            <t-button theme="default" variant="outline" size="small" @click="openEdit(row)" title="编辑">
              <i class="mdi mdi-pencil"></i>
            </t-button>
            <t-button theme="danger" variant="outline" size="small" @click="del(row)" title="删除">
              <i class="mdi mdi-delete"></i>
            </t-button>
          </div>
        </template>
      </t-table>
    </div>

    <!-- 新增任务 -->
    <t-dialog
      v-model:visible="addVisible"
      header="新增任务"
      :on-confirm="onAdd"
      width="560px"
      :confirm-btn="{ loading: saving }"
    >
      <div class="td-form">
        <div class="td-form-row">
          <label>任务类型</label>
          <t-select v-model="form.stype">
            <t-option value="site" label="备份网站" />
            <t-option value="database" label="备份数据库" />
            <t-option value="logs" label="日志切割" />
            <t-option value="url" label="访问 URL" />
          </t-select>
          <div class="td-form-hint">{{ stypeHint(form.stype) }}</div>
        </div>
        <div class="td-form-row">
          <label>任务名称</label>
          <t-input v-model="form.name" placeholder="如：每日备份网站" clearable :maxlength="50" />
        </div>
        <div v-if="form.stype === 'url'" class="td-form-row">
          <label>访问 URL</label>
          <t-input v-model="form.sbody" placeholder="https://你的域名/cron.php" clearable />
          <div class="td-form-hint">仅允许本站点已绑定的域名（http/https）</div>
        </div>
        <div class="td-form-row">
          <label>执行周期</label>
          <t-select v-model="form.cycle_type">
            <t-option value="minute-n" label="每 N 分钟" />
            <t-option value="hour" label="每小时" />
            <t-option value="day" label="每天" />
            <t-option value="day-n" label="每 N 天" />
          </t-select>
        </div>
        <div class="td-form-grid">
          <div v-if="form.cycle_type === 'minute-n' || form.cycle_type === 'day-n'" class="td-form-row">
            <label>间隔天数 / 分钟</label>
            <t-input-number v-model="form.cycle_value" :min="cycleRange[0]" :max="cycleRange[1]" theme="normal" />
          </div>
          <div v-if="needTime" class="td-form-row">
            <label>执行时间</label>
            <div class="cron-time-row">
              <t-input-number v-model="form.cycle_hour" :min="0" :max="23" theme="normal" />
              <span class="cron-time-colon">:</span>
              <t-input-number v-model="form.cycle_minute" :min="0" :max="59" theme="normal" />
            </div>
          </div>
          <div v-if="form.cycle_type === 'hour'" class="td-form-row">
            <label>每小时的第几分钟</label>
            <t-input-number v-model="form.cycle_minute" :min="0" :max="59" theme="normal" />
          </div>
        </div>
        <div v-if="form.stype === 'site' || form.stype === 'database'" class="td-form-row">
          <label>备份保留份数</label>
          <t-input-number v-model="form.save_count" :min="1" :max="30" theme="normal" />
          <div class="td-form-hint">超出份数后自动删除最旧备份</div>
        </div>
      </div>
    </t-dialog>

    <!-- 编辑任务（类型与目标锁定） -->
    <t-dialog
      v-model:visible="editVisible"
      header="编辑任务"
      :on-confirm="onEdit"
      width="560px"
      :confirm-btn="{ loading: saving }"
    >
      <div class="td-form">
        <div class="td-form-row">
          <label>任务类型（不可修改）</label>
          <t-input :value="editing.stype_text" disabled />
          <div class="td-form-hint">目标：{{ editing.stype === 'url' ? editing.sbody : editing.sname }}</div>
        </div>
        <div class="td-form-row">
          <label>任务名称</label>
          <t-input v-model="editForm.name" clearable :maxlength="50" />
        </div>
        <div class="td-form-row">
          <label>执行周期</label>
          <t-select v-model="editForm.cycle_type">
            <t-option value="minute-n" label="每 N 分钟" />
            <t-option value="hour" label="每小时" />
            <t-option value="day" label="每天" />
            <t-option value="day-n" label="每 N 天" />
          </t-select>
        </div>
        <div class="td-form-grid">
          <div v-if="editForm.cycle_type === 'minute-n' || editForm.cycle_type === 'day-n'" class="td-form-row">
            <label>间隔天数 / 分钟</label>
            <t-input-number v-model="editForm.cycle_value" :min="editCycleRange[0]" :max="editCycleRange[1]" theme="normal" />
          </div>
          <div v-if="editNeedTime" class="td-form-row">
            <label>执行时间</label>
            <div class="cron-time-row">
              <t-input-number v-model="editForm.cycle_hour" :min="0" :max="23" theme="normal" />
              <span class="cron-time-colon">:</span>
              <t-input-number v-model="editForm.cycle_minute" :min="0" :max="59" theme="normal" />
            </div>
          </div>
          <div v-if="editForm.cycle_type === 'hour'" class="td-form-row">
            <label>每小时的第几分钟</label>
            <t-input-number v-model="editForm.cycle_minute" :min="0" :max="59" theme="normal" />
          </div>
        </div>
      </div>
    </t-dialog>

    <!-- 执行日志 -->
    <t-dialog v-model:visible="logsVisible" :header="'执行日志' + (logsName ? ' · ' + logsName : '')" :footer="false" width="720px">
      <div class="cron-log-wrap">
        <t-loading :loading="logsLoading" text="加载中…" size="small">
          <pre class="cron-log-pre">{{ logsContent || '暂无日志' }}</pre>
        </t-loading>
      </div>
    </t-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { MessagePlugin, DialogPlugin } from 'tdesign-vue-next'
import {
  listCronTasks,
  addCronTask,
  editCronTask,
  deleteCronTask,
  toggleCronTask,
  execCronTask,
  cronTaskLogs,
} from '@/user/api/crontab'

const loading = ref(false)
const saving = ref(false)
const rows = ref([])

const columns = [
  { colKey: 'id', title: 'ID', width: 64 },
  { colKey: 'name', title: '任务名称', minWidth: 150, ellipsis: true },
  { colKey: 'stype_text', title: '类型', width: 100 },
  { colKey: 'target', title: '目标', minWidth: 180, ellipsis: true },
  { colKey: 'cycle_text', title: '执行周期', minWidth: 130 },
  { colKey: 'bt_status', title: '状态', width: 84 },
  { colKey: 'operate', title: '操作', width: 220, fixed: 'right' },
]

// ---------- 新增 ----------
const addVisible = ref(false)
const form = reactive({
  stype: 'site',
  name: '',
  sbody: '',
  cycle_type: 'day',
  cycle_value: 1,
  cycle_hour: 2,
  cycle_minute: 30,
  save_count: 3,
})

const needTime = computed(() => form.cycle_type === 'day' || form.cycle_type === 'day-n')
const cycleRange = computed(() => (form.cycle_type === 'minute-n' ? [1, 720] : [1, 31]))
const editNeedTime = computed(() => editForm.cycle_type === 'day' || editForm.cycle_type === 'day-n')
const editCycleRange = computed(() => (editForm.cycle_type === 'minute-n' ? [1, 720] : [1, 31]))

function stypeHint(t) {
  if (t === 'site') return '定时备份当前站点，目标不可修改'
  if (t === 'database') return '定时备份当前主机绑定的数据库，目标不可修改'
  if (t === 'logs') return '定时切割站点日志，防止日志占满磁盘'
  if (t === 'url') return '定时访问一个 URL（仅限本站点已绑定域名）'
  return ''
}
function stypeTheme(t) {
  return t === 'site' || t === 'database' ? 'primary' : (t === 'logs' ? 'warning' : 'success')
}

function isOn(v) {
  return v === true || v === 'true' || v === 1 || v === '1'
}
function statusClass(v) {
  return isOn(v) ? 'td-chip td-chip-success' : 'td-chip td-chip-default'
}
function statusText(v) {
  return isOn(v) ? '运行中' : '已暂停'
}

async function load() {
  loading.value = true
  const r = await listCronTasks()
  loading.value = false
  rows.value = r.ok && r.data && Array.isArray(r.data.rows) ? r.data.rows : []
}

function openAdd() {
  form.stype = 'site'
  form.name = ''
  form.sbody = ''
  form.cycle_type = 'day'
  form.cycle_value = 1
  form.cycle_hour = 2
  form.cycle_minute = 30
  form.save_count = 3
  addVisible.value = true
}

function buildCyclePayload(f) {
  return {
    cycle_type: f.cycle_type,
    cycle_value: String(f.cycle_value ?? ''),
    cycle_hour: String(f.cycle_hour ?? 0),
    cycle_minute: String(f.cycle_minute ?? 0),
  }
}

async function onAdd() {
  if (!form.name || !form.name.trim()) {
    MessagePlugin.warning('请填写任务名称')
    return
  }
  if (form.stype === 'url' && !form.sbody.trim()) {
    MessagePlugin.warning('请填写要访问的 URL')
    return
  }
  if ((form.cycle_type === 'day' || form.cycle_type === 'day-n') && (form.cycle_hour < 0 || form.cycle_hour > 23)) {
    MessagePlugin.warning('执行小时需在 0~23 之间')
    return
  }
  saving.value = true
  const r = await addCronTask({ name: form.name.trim(), sbody: form.sbody.trim(), save_count: String(form.save_count), ...buildCyclePayload(form) })
  saving.value = false
  if (r.ok) {
    MessagePlugin.success('添加成功')
    addVisible.value = false
    load()
  }
}

// ---------- 编辑 ----------
const editVisible = ref(false)
const editing = reactive({ id: null, stype_text: '', stype: '', sname: '', sbody: '' })
const editForm = reactive({
  name: '',
  cycle_type: 'day',
  cycle_value: 1,
  cycle_hour: 2,
  cycle_minute: 30,
})

function openEdit(row) {
  editing.id = row.id
  editing.stype = row.stype
  editing.stype_text = row.stype_text || row.stype
  editing.sname = row.sname || ''
  editing.sbody = row.sbody || ''
  editForm.name = row.name || ''
  editForm.cycle_type = row.cycle_type || 'day'
  editForm.cycle_value = Number(row.cycle_value_raw) || (row.cycle_type === 'minute-n' ? 10 : 1)
  editForm.cycle_hour = Number(row.cycle_hour) >= 0 ? Number(row.cycle_hour) : 2
  editForm.cycle_minute = Number(row.cycle_minute) >= 0 ? Number(row.cycle_minute) : 30
  editVisible.value = true
}

async function onEdit() {
  if (!editForm.name.trim()) {
    MessagePlugin.warning('请填写任务名称')
    return
  }
  saving.value = true
  const r = await editCronTask({ id: editing.id, name: editForm.name.trim(), ...buildCyclePayload(editForm) })
  saving.value = false
  if (r.ok) {
    MessagePlugin.success('保存成功')
    editVisible.value = false
    load()
  }
}

// ---------- 操作 ----------
function del(row) {
  const dlg = DialogPlugin.confirm({
    header: '删除任务',
    body: `确定删除任务「${row.name || row.id}」吗?`,
    theme: 'warning',
    confirmBtn: { content: '删除', theme: 'danger' },
    onConfirm: async () => {
      const r = await deleteCronTask(row.id)
      dlg.destroy()
      if (r.ok) {
        MessagePlugin.success('删除成功')
        load()
      }
    },
    onClose: () => dlg.destroy(),
  })
}

async function toggle(row) {
  const next = isOn(row.bt_status) ? '0' : '1'
  const r = await toggleCronTask(row.id, next)
  if (r.ok) {
    MessagePlugin.success(next === '1' ? '已启用' : '已暂停')
    load()
  }
}

async function exec(row) {
  const r = await execCronTask(row.id)
  if (r.ok) {
    MessagePlugin.success('已提交执行，稍后可查看执行日志')
  }
}

// ---------- 日志 ----------
const logsVisible = ref(false)
const logsLoading = ref(false)
const logsContent = ref('')
const logsName = ref('')

async function showLogs(row) {
  logsVisible.value = true
  logsName.value = row.name || ''
  logsLoading.value = true
  logsContent.value = ''
  const r = await cronTaskLogs(row.id)
  logsLoading.value = false
  if (r.ok && r.data) {
    logsContent.value = r.data.log || ''
  } else {
    logsContent.value = ''
  }
}

onMounted(load)
</script>

<style scoped>
.td-empty {
  text-align: center;
  padding: 36px 16px;
  color: var(--td-text-placeholder);
  font-size: 13px;
}
.td-empty i {
  font-size: 36px;
  display: block;
  margin-bottom: 8px;
  color: #cbd5e1;
}
.cron-target {
  background: #f3f3f3;
  padding: 1px 6px;
  border-radius: 4px;
  font-size: 12px;
  word-break: break-all;
}
.cron-time-row {
  display: flex;
  align-items: center;
  gap: 6px;
}
.cron-time-colon {
  color: var(--td-text-secondary);
}
.cron-log-wrap {
  min-height: 200px;
}
.cron-log-pre {
  max-height: 420px;
  overflow: auto;
  white-space: pre-wrap;
  word-break: break-all;
  background: #f6f7f9;
  border: 1px solid #e7e7e7;
  border-radius: 6px;
  padding: 12px;
  font-size: 12px;
  line-height: 1.7;
  margin: 0;
}
</style>
