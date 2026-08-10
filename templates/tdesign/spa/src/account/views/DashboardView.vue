<template>
  <div class="td-page">
    <!-- 欢迎横幅 -->
    <div class="welcome-card td-card">
      <div class="welcome-left">
        <div class="welcome-avatar">
          <i class="mdi mdi-account-circle"></i>
        </div>
        <div>
          <h2 class="welcome-title">你好,{{ user.username }}</h2>
          <p class="welcome-sub">欢迎回到 {{ siteName }} 用户中心,在这里管理你的个人信息、余额与主机资产。</p>
        </div>
      </div>
      <div class="welcome-right">
        <t-button theme="primary" variant="outline" @click="router.push('/profile')">
          <i class="mdi mdi-account-edit-outline"></i> 编辑资料
        </t-button>
      </div>
    </div>

    <!-- 业务概览（余额 / 主机 / 订单） -->
    <div v-if="hasBalance || hasShop" class="overview-grid">
      <div v-if="hasBalance" class="overview-card td-card" @click="router.push('/balance')">
        <div class="overview-icon overview-blue"><i class="mdi mdi-wallet"></i></div>
        <div class="overview-meta">
          <span class="overview-label">账户余额</span>
          <strong class="overview-value">¥{{ balanceText }}</strong>
        </div>
        <i class="mdi mdi-chevron-right overview-arrow"></i>
      </div>
      <div v-if="hasShop" class="overview-card td-card" @click="router.push('/hosting')">
        <div class="overview-icon overview-green"><i class="mdi mdi-server"></i></div>
        <div class="overview-meta">
          <span class="overview-label">我的主机</span>
          <strong class="overview-value">{{ assetCount }} 台</strong>
        </div>
        <i class="mdi mdi-chevron-right overview-arrow"></i>
      </div>
      <div v-if="hasShop" class="overview-card td-card" @click="router.push('/orders')">
        <div class="overview-icon overview-orange"><i class="mdi mdi-receipt"></i></div>
        <div class="overview-meta">
          <span class="overview-label">我的订单</span>
          <strong class="overview-value">{{ orderCount }} 笔</strong>
        </div>
        <i class="mdi mdi-chevron-right overview-arrow"></i>
      </div>
    </div>

    <!-- 账户信息卡片 -->
    <div class="info-grid">
      <div class="info-card td-card">
        <div class="info-icon info-icon-blue"><i class="mdi mdi-account-details"></i></div>
        <div class="info-meta">
          <span class="info-label">用户名</span>
          <strong class="info-value">{{ user.username }}</strong>
        </div>
      </div>
      <div class="info-card td-card">
        <div class="info-icon info-icon-green"><i class="mdi mdi-email-outline"></i></div>
        <div class="info-meta">
          <span class="info-label">邮箱</span>
          <strong class="info-value" :class="{ muted: !user.email }">{{ user.email || '未绑定' }}</strong>
        </div>
      </div>
      <div class="info-card td-card">
        <div class="info-icon info-icon-orange"><i class="mdi mdi-qqchat"></i></div>
        <div class="info-meta">
          <span class="info-label">QQ</span>
          <strong class="info-value" :class="{ muted: !user.qq }">{{ user.qq || '未绑定' }}</strong>
        </div>
      </div>
      <div class="info-card td-card">
        <div class="info-icon info-icon-purple"><i class="mdi mdi-calendar-account-outline"></i></div>
        <div class="info-meta">
          <span class="info-label">注册时间</span>
          <strong class="info-value">{{ user.created_at || '—' }}</strong>
        </div>
      </div>
    </div>

    <!-- 快速入口 -->
    <div class="quick-wrap td-card">
      <div class="td-card-head">
        <span>快速入口</span>
      </div>
      <div class="quick-body">
        <div class="quick-item" @click="router.push('/profile')">
          <div class="quick-icon quick-icon-blue"><i class="mdi mdi-account-details"></i></div>
          <div>
            <strong>个人信息</strong>
            <span>查看并修改邮箱、QQ 等联系方式</span>
          </div>
          <i class="mdi mdi-chevron-right quick-arrow"></i>
        </div>
        <div class="quick-item" @click="router.push('/password')">
          <div class="quick-icon quick-icon-orange"><i class="mdi mdi-shield-account-outline"></i></div>
          <div>
            <strong>修改密码</strong>
            <span>定期更换密码,保障账号安全</span>
          </div>
          <i class="mdi mdi-chevron-right quick-arrow"></i>
        </div>
        <div v-if="hasBalance" class="quick-item" @click="router.push('/balance')">
          <div class="quick-icon quick-icon-green"><i class="mdi mdi-wallet"></i></div>
          <div>
            <strong>余额中心</strong>
            <span>查看余额流水、在线充值</span>
          </div>
          <i class="mdi mdi-chevron-right quick-arrow"></i>
        </div>
        <div v-if="hasShop" class="quick-item" @click="router.push('/shop')">
          <div class="quick-icon quick-icon-purple"><i class="mdi mdi-cart"></i></div>
          <div>
            <strong>主机商城</strong>
            <span>浏览并购买虚拟主机套餐</span>
          </div>
          <i class="mdi mdi-chevron-right quick-arrow"></i>
        </div>
        <div v-if="hasShop" class="quick-item" @click="router.push('/hosting')">
          <div class="quick-icon quick-icon-blue"><i class="mdi mdi-server"></i></div>
          <div>
            <strong>我的主机</strong>
            <span>管理已开通的主机资产</span>
          </div>
          <i class="mdi mdi-chevron-right quick-arrow"></i>
        </div>
        <div v-if="hasShop" class="quick-item" @click="router.push('/orders')">
          <div class="quick-icon quick-icon-orange"><i class="mdi mdi-receipt"></i></div>
          <div>
            <strong>我的订单</strong>
            <span>查看订单与支付状态</span>
          </div>
          <i class="mdi mdi-chevron-right quick-arrow"></i>
        </div>
        <a v-if="panelUrl" class="quick-item" :href="panelUrl">
          <div class="quick-icon quick-icon-green"><i class="mdi mdi-server"></i></div>
          <div>
            <strong>主机管理面板</strong>
            <span>进入主机业务管理界面</span>
          </div>
          <i class="mdi mdi-chevron-right quick-arrow"></i>
        </a>
        <a v-if="homeUrl" class="quick-item" :href="homeUrl">
          <div class="quick-icon quick-icon-purple"><i class="mdi mdi-home-outline"></i></div>
          <div>
            <strong>返回官网</strong>
            <span>浏览站点首页与产品服务</span>
          </div>
          <i class="mdi mdi-chevron-right quick-arrow"></i>
        </a>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { pluginEnabled, getBalanceInfo, getShopAssets, getShopOrders, centsToYuan } from '@/account/api/plugins'

const router = useRouter()
const boot = window.__TD_BOOT__ || {}
const siteName = boot.siteName || 'MNBT'
const panelUrl = boot.panelUrl || ''
const homeUrl = boot.homeUrl || ''
const user = boot.accountUser || { username: boot.user || 'user', email: '', qq: '', created_at: '' }

const hasBalance = pluginEnabled('balance')
const hasShop = pluginEnabled('hosting_shop')

const balanceCents = ref(0)
const assetCount = ref(0)
const orderCount = ref(0)

const balanceText = computed(() => centsToYuan(balanceCents.value))

onMounted(async () => {
  const tasks = []
  if (hasBalance) tasks.push(getBalanceInfo(1, 1))
  if (hasShop) {
    tasks.push(getShopAssets())
    tasks.push(getShopOrders(1, 1))
  }
  const results = await Promise.all(tasks)
  let idx = 0
  if (hasBalance) {
    const res = results[idx++]
    if (res.ok) balanceCents.value = res.data.balance_cents ?? 0
  }
  if (hasShop) {
    const assetRes = results[idx++]
    if (assetRes.ok) assetCount.value = (assetRes.data.assets || []).length
    const orderRes = results[idx++]
    if (orderRes.ok) orderCount.value = (orderRes.data.orders || {}).total || 0
  }
})
</script>

<style scoped>
.td-page {
  max-width: 1080px;
}

/* ============== 欢迎横幅 ============== */
.welcome-card {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  padding: 24px;
  margin-bottom: 16px;
  flex-wrap: wrap;
}
.welcome-left {
  display: flex;
  align-items: center;
  gap: 16px;
  min-width: 0;
}
.welcome-avatar {
  width: 56px;
  height: 56px;
  border-radius: 50%;
  background: linear-gradient(135deg, var(--td-brand) 0%, var(--td-brand-dark) 100%);
  display: grid;
  place-items: center;
  color: #fff;
  font-size: 32px;
  flex-shrink: 0;
}
.welcome-title {
  margin: 0;
  font-size: 20px;
  font-weight: 700;
  color: var(--td-text);
}
.welcome-sub {
  margin: 6px 0 0;
  font-size: 13px;
  color: var(--td-text-secondary);
}

/* ============== 业务概览 ============== */
.overview-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 16px;
  margin-bottom: 16px;
}
.overview-card {
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 18px;
  cursor: pointer;
  transition: box-shadow var(--td-dur) var(--td-ease),
              transform var(--td-dur) var(--td-ease);
}
.overview-card:hover {
  box-shadow: var(--td-shadow-md);
  transform: translateY(-2px);
}
.overview-icon {
  width: 46px;
  height: 46px;
  border-radius: 12px;
  display: grid;
  place-items: center;
  font-size: 24px;
  flex-shrink: 0;
}
.overview-blue { background: #e8f3ff; color: #0052d9; }
.overview-green { background: #e8f8f0; color: #2ba471; }
.overview-orange { background: #fff3e0; color: #e37318; }
.overview-meta {
  flex: 1;
  min-width: 0;
  display: flex;
  flex-direction: column;
}
.overview-label {
  font-size: 12px;
  color: var(--td-text-placeholder);
  margin-bottom: 4px;
}
.overview-value {
  font-size: 18px;
  color: var(--td-text);
  font-weight: 700;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.overview-arrow {
  color: var(--td-text-placeholder);
  font-size: 18px;
}

/* ============== 账户信息卡片 ============== */
.info-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 16px;
  margin-bottom: 16px;
}
.info-card {
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 18px;
}
.info-icon {
  width: 44px;
  height: 44px;
  border-radius: 10px;
  display: grid;
  place-items: center;
  font-size: 22px;
  flex-shrink: 0;
}
.info-icon-blue { background: #e8f3ff; color: #0052d9; }
.info-icon-green { background: #e8f8f0; color: #2ba471; }
.info-icon-orange { background: #fff3e0; color: #e37318; }
.info-icon-purple { background: #f3e8ff; color: #7a4dd0; }
.info-meta {
  display: flex;
  flex-direction: column;
  min-width: 0;
}
.info-label {
  font-size: 12px;
  color: var(--td-text-placeholder);
  margin-bottom: 4px;
}
.info-value {
  font-size: 15px;
  color: var(--td-text);
  font-weight: 600;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.info-value.muted {
  color: var(--td-text-placeholder);
  font-weight: 400;
}

/* ============== 快速入口 ============== */
.quick-wrap {
  overflow: hidden;
}
.quick-body {
  padding: 6px 0;
}
.quick-item {
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 14px 18px;
  cursor: pointer;
  text-decoration: none;
  transition: background var(--td-dur) var(--td-ease);
  border-radius: 8px;
}
.quick-item:hover {
  background: var(--td-bg);
  text-decoration: none;
}
.quick-icon {
  width: 40px;
  height: 40px;
  border-radius: 10px;
  display: grid;
  place-items: center;
  font-size: 20px;
  flex-shrink: 0;
}
.quick-icon-blue { background: #e8f3ff; color: #0052d9; }
.quick-icon-green { background: #e8f8f0; color: #2ba471; }
.quick-icon-orange { background: #fff3e0; color: #e37318; }
.quick-icon-purple { background: #f3e8ff; color: #7a4dd0; }
.quick-item > div:nth-child(2) {
  flex: 1;
  min-width: 0;
  display: flex;
  flex-direction: column;
}
.quick-item strong {
  font-size: 14px;
  color: var(--td-text);
  font-weight: 600;
}
.quick-item span {
  font-size: 12px;
  color: var(--td-text-secondary);
  margin-top: 3px;
}
.quick-arrow {
  color: var(--td-text-placeholder);
  font-size: 18px;
}

/* ============== 响应式 ============== */
@media (max-width: 992px) {
  .overview-grid {
    grid-template-columns: repeat(2, 1fr);
  }
  .info-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 560px) {
  .overview-grid {
    grid-template-columns: 1fr;
  }
  .info-grid {
    grid-template-columns: 1fr;
  }
  .welcome-card {
    flex-direction: column;
    align-items: flex-start;
  }
}
</style>
