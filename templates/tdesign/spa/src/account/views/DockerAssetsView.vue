<template>
  <div class="td-page">
    <div class="td-page-head">
      <div>
        <h3 class="td-page-title"><i class="mdi mdi-docker"></i> 我的 Docker</h3>
        <p class="td-page-subtitle">查看已开通的 Docker 账号，可重置密码、刷新容器状态</p>
      </div>
      <t-button variant="outline" @click="router.push('/docker-shop')">
        <i class="mdi mdi-cart"></i> 前往商城
      </t-button>
    </div>

    <div v-if="assets.length" class="asset-grid">
      <div class="asset-card td-card" v-for="asset in assets" :key="asset.id">
        <div class="asset-head">
          <div class="asset-icon"><i class="mdi mdi-docker"></i></div>
          <div class="asset-info">
            <strong>{{ asset.plan_name || 'Docker 容器' }}</strong>
            <span class="asset-node">节点：{{ asset.node_name || '—' }}</span>
          </div>
          <t-tag :theme="statusTheme(asset.status)" variant="light">
            {{ statusText(asset.status) }}
          </t-tag>
        </div>

        <div class="asset-meta">
          <div class="meta-item">
            <span>容器状态</span>
            <b>
              <i class="mdi mdi-circle" :class="'dot-' + containerStatus(asset.container_status)"></i>
              {{ containerText(asset.container_status) }}
            </b>
          </div>
          <div class="meta-item">
            <span>磁盘用量</span>
            <b>{{ diskText(asset.disk_usage) }}</b>
          </div>
          <div class="meta-item">
            <span>到期时间</span>
            <b :class="{ expired: isExpired(asset) }">{{ asset.expire_at || '—' }}</b>
          </div>
          <div class="meta-item">
            <span>开通时间</span>
            <b>{{ asset.created_at || '—' }}</b>
          </div>
        </div>

        <div class="asset-foot">
          <span>Docker 账号：<b class="mono">{{ asset.docker_username || '—' }}</b></span>
          <span class="pass-row">
            Docker 密码：
            <b class="mono pass" :class="{ revealed: asset._reveal }">{{ asset._reveal ? asset.docker_password : '••••••••' }}</b>
            <button class="icon-btn" title="显示/隐藏密码" @click="toggleReveal(asset)">
              <i class="mdi" :class="asset._reveal ? 'mdi-eye-off-outline' : 'mdi-eye-outline'"></i>
            </button>
          </span>
        </div>

        <div class="asset-actions">
          <t-button
            theme="primary"
            variant="outline"
            size="small"
            :href="dockerUrl"
            target="_blank"
            rel="noopener"
          >
            <i class="mdi mdi-console"></i> 进入控制台
          </t-button>
          <t-button variant="outline" size="small" :loading="asset._syncing" @click="onSync(asset)">
            <i class="mdi mdi-refresh"></i> 刷新状态
          </t-button>
          <t-button
            variant="outline"
            size="small"
            theme="warning"
            :loading="asset._resetting"
            @click="onReset(asset)"
          >
            <i class="mdi mdi-key-change"></i> 重置密码
          </t-button>
        </div>
      </div>
    </div>

    <t-empty v-else description="暂无 Docker 资产，前往商城选购" style="padding: 60px 0">
      <t-button theme="primary" @click="router.push('/docker-shop')">去选购套餐</t-button>
    </t-empty>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { MessagePlugin, DialogPlugin } from 'tdesign-vue-next'
import { getDockerShopAssets, resetDockerPassword, syncDockerStatus, dockerConsoleUrl } from '@/account/api/plugins'

const router = useRouter()
const boot = window.__TD_BOOT__ || {}
const dockerUrl = dockerConsoleUrl() || boot.dockerUrl || ''

const assets = ref([])

function statusText(status) {
  return { active: '使用中', expired: '已到期', cancelled: '已停用' }[status] || status
}

function statusTheme(status) {
  if (status === 'active') return 'success'
  if (status === 'expired') return 'warning'
  return 'default'
}

function containerStatus(s) {
  return { running: 'running', stopped: 'stopped', creating: 'creating', none: 'none' }[s] || 'none'
}

function containerText(s) {
  return { running: '运行中', stopped: '已停止', creating: '创建中', none: '未创建' }[s] || '未知'
}

function diskText(usage) {
  const n = Number(usage || 0)
  if (!n) return '—'
  if (n >= 1024 * 1024) return (n / 1024 / 1024).toFixed(1) + ' GB'
  if (n >= 1024) return (n / 1024).toFixed(1) + ' MB'
  return n + ' KB'
}

function isExpired(asset) {
  return !!asset.expire_at && asset.expire_at < new Date().toISOString().slice(0, 10)
}

function toggleReveal(asset) {
  asset._reveal = !asset._reveal
}

async function onSync(asset) {
  asset._syncing = true
  const res = await syncDockerStatus(asset.id)
  asset._syncing = false
  if (!res.ok) {
    MessagePlugin.error(res.message || '刷新失败')
    return
  }
  asset.container_status = res.data.container_status ?? asset.container_status
  asset.container_id = res.data.container_id ?? asset.container_id
  asset.disk_usage = res.data.disk_usage ?? asset.disk_usage
  MessagePlugin.success('状态已刷新')
}

function onReset(asset) {
  const dialog = DialogPlugin.confirm({
    header: '重置 Docker 密码',
    body: `确定要重置「${asset.docker_username || asset.plan_name}」的密码吗？新密码将立即生效。`,
    confirmBtn: { content: '确认重置', theme: 'danger' },
    onConfirm: async () => {
      dialog.destroy()
      asset._resetting = true
      const res = await resetDockerPassword(asset.id)
      asset._resetting = false
      if (!res.ok) {
        MessagePlugin.error(res.message || '重置失败')
        return
      }
      asset.docker_password = res.data.password || asset.docker_password
      asset._reveal = true
      MessagePlugin.success('密码已重置，请及时保存')
    },
    onClose: () => dialog.destroy(),
  })
}

async function load() {
  const res = await getDockerShopAssets()
  if (res.ok) assets.value = (res.data.assets || []).map((a) => ({ ...a, _reveal: false }))
}

onMounted(load)
</script>

<style scoped>
.asset-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 16px;
}
.asset-card {
  padding: 18px;
}
.asset-head {
  display: flex;
  align-items: center;
  gap: 12px;
}
.asset-icon {
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
.asset-info {
  flex: 1;
  min-width: 0;
  display: flex;
  flex-direction: column;
}
.asset-info strong {
  font-size: 15px;
  color: var(--td-text);
  font-weight: 600;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.asset-node {
  font-size: 12px;
  color: var(--td-text-secondary);
  margin-top: 2px;
}
.asset-meta {
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
  display: inline-flex;
  align-items: center;
  gap: 6px;
}
.meta-item b.expired {
  color: var(--td-error);
}
.dot-running { color: #2ba471; font-size: 10px; }
.dot-stopped { color: #e37318; font-size: 10px; }
.dot-creating { color: #0052d9; font-size: 10px; }
.dot-none { color: var(--td-text-placeholder); font-size: 10px; }
.asset-foot {
  margin-top: 12px;
  border-top: 1px dashed var(--td-border);
  padding-top: 10px;
  display: flex;
  flex-direction: column;
  gap: 6px;
  font-size: 12px;
  color: var(--td-text-secondary);
}
.asset-foot b {
  color: var(--td-text);
  font-weight: 500;
}
.asset-foot .mono {
  font-family: Consolas, Monaco, monospace;
}
.pass-row {
  display: flex;
  align-items: center;
  gap: 6px;
}
.asset-actions {
  display: flex;
  gap: 8px;
  margin-top: 14px;
  flex-wrap: wrap;
}
.icon-btn {
  border: none;
  background: none;
  cursor: pointer;
  color: var(--td-text-placeholder);
  font-size: 15px;
  line-height: 1;
  padding: 2px;
  transition: color var(--td-dur) var(--td-ease);
}
.icon-btn:hover {
  color: var(--td-brand);
}

@media (max-width: 860px) {
  .asset-grid {
    grid-template-columns: 1fr;
  }
}
</style>
