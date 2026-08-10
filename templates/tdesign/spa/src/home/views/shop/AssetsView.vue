<template>
  <div class="hd-page">
    <div class="hd-container">
      <div class="hd-page-head">
        <div>
          <h1 class="hd-page-title">我的主机</h1>
          <p class="hd-page-subtitle">查看已开通的主机资产</p>
        </div>
      </div>

      <t-loading :loading="loading" size="large">
        <div v-if="assets.length === 0 && !loading" class="hd-empty">
          <i class="mdi mdi-server"></i>
          <p>暂无主机资产</p>
          <t-button style="margin-top:12px;" theme="primary" variant="outline" @click="$router.push('/shop')">去购买</t-button>
        </div>

        <div v-else>
          <div v-for="a in assets" :key="a.id" class="hd-card" style="margin-bottom:14px;">
            <div class="hd-card-body" style="padding:18px 22px;">
              <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;">
                <div>
                  <h4 style="margin:0 0 4px;font-size:15px;">{{ a.plan_name || '—' }}</h4>
                  <div style="font-size:13px;color:var(--hd-text-2);">
                    账号：{{ a.host_user || '—' }}
                  </div>
                </div>
                <div style="display:flex;align-items:center;gap:10px;">
                  <t-tag :theme="(assetStatusMap[a.status] || {}).theme || 'default'" size="small">
                    {{ (assetStatusMap[a.status] || {}).label || a.status || '—' }}
                  </t-tag>
                </div>
              </div>
              <div style="display:flex;gap:20px;margin-top:12px;flex-wrap:wrap;font-size:13px;color:var(--hd-text-3);">
                <span>到期：{{ a.expire_at || '—' }}</span>
                <span v-if="a.host_qk">状态：{{ a.host_qk }}</span>
              </div>
            </div>
          </div>
        </div>
      </t-loading>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { getAssets } from '@/home/api/shop'
import { assetStatusMap } from '@/home/utils/format'

const loading = ref(true)
const assets = ref([])

onMounted(async () => {
  const res = await getAssets()
  if (res.ok && res.data?.assets) {
    assets.value = res.data.assets
  }
  loading.value = false
})
</script>
