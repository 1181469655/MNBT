<template>
  <div class="td-page">
    <div class="td-page-head">
      <div>
        <h3 class="td-page-title"><i class="mdi mdi-server"></i> 我的主机</h3>
        <p class="td-page-subtitle">查看已开通的虚拟主机资产</p>
      </div>
      <t-button variant="outline" @click="router.push('/shop')">
        <i class="mdi mdi-cart"></i> 前往商城
      </t-button>
    </div>

    <div v-if="assets.length" class="asset-grid">
      <div class="asset-card td-card" v-for="asset in assets" :key="asset.id">
        <div class="asset-head">
          <div class="asset-icon"><i class="mdi mdi-server"></i></div>
          <div class="asset-info">
            <strong>{{ asset.plan_name || '虚拟主机' }}</strong>
            <span class="asset-node">节点：{{ asset.ssbt || '—' }}</span>
          </div>
          <t-tag :theme="statusTheme(asset.status)" variant="light">
            {{ statusText(asset.status) }}
          </t-tag>
        </div>
        <div class="asset-meta">
          <div class="meta-item">
            <span>开通时间</span>
            <b>{{ asset.created_at || '—' }}</b>
          </div>
          <div class="meta-item">
            <span>到期时间</span>
            <b :class="{ expired: isExpired(asset) }">{{ asset.expire_at || '—' }}</b>
          </div>
          <div class="meta-item" v-if="asset.sqldz">
            <span>数据库地址</span>
            <b>{{ asset.sqldz }}</b>
          </div>
        </div>
        <div class="asset-foot" v-if="asset.host_user">
          <span>主机账号：<b>{{ asset.host_user }}</b></span>
          <span>主机密码：<b class="pass">{{ asset.host_pass }}</b></span>
        </div>
      </div>
    </div>

    <t-empty v-else description="暂无主机资产，前往商城选购" style="padding: 60px 0">
      <t-button theme="primary" @click="router.push('/shop')">去选购套餐</t-button>
    </t-empty>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { getShopAssets } from '@/account/api/plugins'

const router = useRouter()
const assets = ref([])

function statusText(status) {
  return { active: '运行中', expired: '已到期', cancelled: '已停用' }[status] || status
}

function statusTheme(status) {
  if (status === 'active') return 'success'
  if (status === 'expired') return 'warning'
  return 'default'
}

function isExpired(asset) {
  return !!asset.expire_at && asset.expire_at < new Date().toISOString().slice(0, 10)
}

async function load() {
  const res = await getShopAssets()
  if (res.ok) assets.value = res.data.assets || []
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
}
.meta-item b.expired {
  color: var(--td-error);
}
.asset-foot {
  margin-top: 12px;
  display: flex;
  justify-content: space-between;
  gap: 8px;
  font-size: 12px;
  color: var(--td-text-secondary);
  flex-wrap: wrap;
}
.asset-foot b {
  color: var(--td-text);
  font-weight: 500;
}
.asset-foot .pass {
  font-family: Consolas, monospace;
}

@media (max-width: 860px) {
  .asset-grid {
    grid-template-columns: 1fr;
  }
}
</style>
