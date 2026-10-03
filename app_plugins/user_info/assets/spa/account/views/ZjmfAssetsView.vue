<template>
  <div class="td-page">
    <div class="td-page-head">
      <div>
        <h3 class="td-page-title"><i class="mdi mdi-server"></i> 我的云服务器</h3>
        <p class="td-page-subtitle">查看已开通的主机，可执行开机/关机/重启/重置密码，管理详情跳转独立页面</p>
      </div>
      <t-button variant="outline" @click="router.push('/zjmf-shop')">
        <i class="mdi mdi-cart"></i> 前往选购
      </t-button>
    </div>

    <div v-if="hosts.length" class="zjmf-asset-grid">
      <div class="zjmf-asset td-card" v-for="h in hosts" :key="h.id">
        <div class="zjmf-asset-head">
          <div class="zjmf-asset-icon"><i class="mdi mdi-cloud-outline"></i></div>
          <div class="zjmf-asset-info">
            <strong>{{ h.name }}</strong>
            <span class="zjmf-asset-node">{{ cycleText(h.cycle) }}</span>
          </div>
          <t-tag :theme="statusTheme(h.status)" variant="light">
            {{ statusText(h.status) }}
          </t-tag>
        </div>

        <div class="zjmf-asset-meta">
          <div class="meta-item">
            <span>供应商</span>
            <b>{{ h.supplier_name || '—' }}</b>
          </div>
          <div class="meta-item">
            <span>主机账号</span>
            <b class="mono">{{ h.username || '—' }}</b>
          </div>
          <div class="meta-item">
            <span>上游主机 ID</span>
            <b>{{ h.up_host_id > 0 ? h.up_host_id : '—' }}</b>
          </div>
          <div class="meta-item">
            <span>到期时间</span>
            <b :class="{ expired: isExpired(h) }">{{ h.renew_date || '—' }}</b>
          </div>
          <div class="meta-item">
            <span>开通时间</span>
            <b>{{ h.created_at || '—' }}</b>
          </div>
        </div>

        <div class="zjmf-asset-actions">
          <t-button
            theme="primary"
            variant="outline"
            size="small"
            :href="reserveUrl('hosts/' + h.id)"
            target="_blank"
            rel="noopener"
          >
            <i class="mdi mdi-cog-outline"></i> 管理详情
          </t-button>
          <t-button
            variant="outline"
            size="small"
            theme="success"
            :disabled="!canAction(h, 'on')"
            :loading="h._acting === 'on'"
            @click="onAction(h, 'on')"
          >
            <i class="mdi mdi-play"></i> 开机
          </t-button>
          <t-button
            variant="outline"
            size="small"
            theme="warning"
            :disabled="!canAction(h, 'off')"
            :loading="h._acting === 'off'"
            @click="onAction(h, 'off')"
          >
            <i class="mdi mdi-stop"></i> 关机
          </t-button>
          <t-button
            variant="outline"
            size="small"
            :disabled="!canAction(h, 'reboot')"
            :loading="h._acting === 'reboot'"
            @click="onAction(h, 'reboot')"
          >
            <i class="mdi mdi-restart"></i> 重启
          </t-button>
          <t-button
            variant="outline"
            size="small"
            :loading="h._acting === 'reset_password'"
            @click="onResetPassword(h)"
          >
            <i class="mdi mdi-key-change"></i> 重置密码
          </t-button>
        </div>
      </div>
    </div>

    <t-empty v-else description="暂无云服务器，前往商城选购" style="padding: 60px 0">
      <t-button theme="primary" @click="router.push('/zjmf-shop')">去选购商品</t-button>
    </t-empty>

    <!-- 重置密码对话框 -->
    <t-dialog
      v-model:visible="pwdVisible"
      :header="'重置密码：' + (currentHost?.name || '')"
      :confirm-btn="{ content: '确认重置', theme: 'danger', loading: pwdSubmitting }"
      :cancel-btn="'取消'"
      :on-confirm="onConfirmPwd"
    >
      <p style="font-size: 13px; color: var(--td-text-secondary); margin: 0 0 12px">
        新密码将立即生效，请妥善保存。
      </p>
      <t-input v-model="newPassword" type="password" placeholder="输入新密码" clearable />
    </t-dialog>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { MessagePlugin, DialogPlugin } from 'tdesign-vue-next'
import { getZjmfHosts, zjmfHostAction } from '@/account/api/plugins'

const router = useRouter()
const boot = window.__TD_BOOT__ || {}

const hosts = ref([])

// 重置密码
const pwdVisible = ref(false)
const currentHost = ref(null)
const newPassword = ref('')
const pwdSubmitting = ref(false)

function statusText(status) {
  return { active: '运行中', suspend: '已暂停', pending: '待开通', terminated: '已终止', unknown: '未知' }[status] || status
}

function cycleText(cycle) {
  const map = {
    Monthly: '月付',
    Quarterly: '季付',
    SemiAnnually: '半年付',
    Annually: '年付',
    Biennially: '两年付',
    Triennially: '三年付',
  }
  return map[cycle] || cycle || ''
}

function statusTheme(status) {
  if (status === 'active') return 'success'
  if (status === 'suspend') return 'warning'
  if (status === 'pending') return 'warning'
  if (status === 'terminated') return 'danger'
  return 'default'
}

function isExpired(h) {
  return !!h.renew_date && h.renew_date < new Date().toISOString().slice(0, 10)
}

/** 操作可用性：运行中才能关机/重启，已暂停/未知才能开机 */
function canAction(h, action) {
  if (h.up_host_id <= 0) return false
  const active = h.status === 'active'
  if (action === 'on') return !active
  if (action === 'off') return active
  if (action === 'reboot') return active
  return true
}

function reserveUrl(path) {
  const base = boot.routeBase || '/index.php?_r='
  return base + 'reserve/' + String(path).replace(/^\/+/, '')
}

function onAction(h, action) {
  const labels = { on: '开机', off: '关机', reboot: '重启' }
  const dialog = DialogPlugin.confirm({
    header: labels[action],
    body: `确定要执行「${labels[action]}」操作吗？`,
    confirmBtn: { content: '确认', theme: action === 'off' ? 'danger' : 'primary' },
    onConfirm: async () => {
      dialog.destroy()
      h._acting = action
      const res = await zjmfHostAction(h.id, action)
      h._acting = ''
      if (!res.ok) {
        MessagePlugin.error(res.message || '操作失败')
        return
      }
      MessagePlugin.success('操作成功')
    },
    onClose: () => dialog.destroy(),
  })
}

function onResetPassword(h) {
  currentHost.value = h
  newPassword.value = ''
  pwdVisible.value = true
}

async function onConfirmPwd() {
  const pwd = newPassword.value.trim()
  if (!pwd) {
    MessagePlugin.warning('请输入新密码')
    return false
  }
  pwdSubmitting.value = true
  const res = await zjmfHostAction(currentHost.value.id, 'reset_password', pwd)
  pwdSubmitting.value = false
  if (!res.ok) {
    MessagePlugin.error(res.message || '重置失败')
    return false
  }
  pwdVisible.value = false
  MessagePlugin.success('密码已重置，请及时保存')
  return true
}

async function load() {
  const res = await getZjmfHosts()
  if (res.ok) hosts.value = res.data.hosts || []
}

onMounted(load)
</script>

<style scoped>
.zjmf-asset-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 16px;
}
.zjmf-asset {
  padding: 18px;
}
.zjmf-asset-head {
  display: flex;
  align-items: center;
  gap: 12px;
}
.zjmf-asset-icon {
  width: 42px;
  height: 42px;
  border-radius: 10px;
  background: var(--td-brand-light);
  color: var(--td-brand);
  display: grid;
  place-items: center;
  font-size: 22px;
  flex-shrink: 0;
}
.zjmf-asset-info {
  flex: 1;
  min-width: 0;
  display: flex;
  flex-direction: column;
}
.zjmf-asset-info strong {
  font-size: 15px;
  color: var(--td-text);
  font-weight: 600;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.zjmf-asset-node {
  font-size: 12px;
  color: var(--td-text-secondary);
  margin-top: 2px;
}
.zjmf-asset-meta {
  margin-top: 14px;
  border-top: 1px dashed var(--td-border);
  padding-top: 12px;
}
.meta-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  font-size: 13px;
  padding: 4px 0;
}
.meta-item span {
  color: var(--td-text-placeholder);
}
.meta-item b {
  color: var(--td-text);
  font-weight: 500;
}
.meta-item b.expired {
  color: var(--td-error);
}
.meta-item .mono {
  font-family: Consolas, Monaco, monospace;
}
.zjmf-asset-actions {
  display: flex;
  gap: 8px;
  margin-top: 14px;
  flex-wrap: wrap;
}

@media (max-width: 860px) {
  .zjmf-asset-grid {
    grid-template-columns: 1fr;
  }
}
</style>
