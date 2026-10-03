<template>
  <div class="site-product-detail">
    <section class="site-page-header" :style="{ backgroundImage: `url(${headerImage})` }">
      <div class="hd-container">
        <h1>{{ product.name || '产品详情' }}</h1>
        <p>了解产品详细信息与核心能力</p>
      </div>
    </section>

    <section class="site-sec">
      <div class="hd-container">
        <div v-if="product.id" class="site-detail-layout">
          <div class="site-detail-img">
            <img v-if="product.image" :src="product.image" :alt="product.name" />
            <div v-else class="site-ph"><i class="mdi mdi-package-variant"></i></div>
          </div>

          <div class="site-detail-info">
            <h2>{{ product.name }}</h2>
            <div class="site-detail-meta">
              <span class="site-detail-cat">{{ product.category_name }}</span>
              <span class="site-detail-date">更新于 {{ formatDate(product.created_at) }}</span>
            </div>
            <p class="site-detail-desc">{{ product.description }}</p>

            <div class="site-detail-feats">
              <h3>产品特性</h3>
              <ul class="site-feat-list">
                <li v-for="(feat, i) in product.features" :key="i">
                  <span class="site-feat-icon"><i class="mdi mdi-check"></i></span>
                  {{ feat }}
                </li>
              </ul>
            </div>

            <div class="site-detail-actions">
              <t-button v-if="product.link" theme="primary" size="large" @click="openProductLink">
                前往了解 <template #suffix><i class="mdi mdi-open-in-new"></i></template>
              </t-button>
              <t-button v-else theme="primary" size="large" @click="$router.push('/site/contact')">
                联系我们 <template #suffix><i class="mdi mdi-send"></i></template>
              </t-button>
              <t-button variant="outline" size="large" @click="$router.push('/site/products')">返回产品中心</t-button>
            </div>
          </div>
        </div>

        <div v-else-if="loading" class="site-empty">
          <i class="mdi mdi-loading mdi-spin"></i>
          <p>正在加载产品信息...</p>
        </div>

        <div v-else-if="error" class="site-empty">
          <i class="mdi mdi-alert-circle-outline"></i>
          <p>{{ error }}</p>
          <t-button theme="primary" style="margin-top:12px" @click="$router.push('/site/products')">返回产品中心</t-button>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import bg2 from '@/shared/assets/bg2.jpg'
import { getSiteProduct } from '@/home/api/site'
import { formatDate } from '@/home/utils/format'

const headerImage = bg2
const route = useRoute()
const router = useRouter()

const product = ref({})
const loading = ref(true)
const error = ref('')

// 跳转产品配置的链接（http(s) 外链新窗口，站内路径路由跳转）
function openProductLink() {
  const link = (product.value.link || '').trim()
  if (!link) return
  if (/^https?:\/\//i.test(link)) {
    window.open(link, '_blank', 'noopener')
  } else {
    router.push(link)
  }
}

onMounted(async () => {
  const res = await getSiteProduct(route.params.id)
  if (res.ok && res.data?.product) {
    product.value = res.data.product
  } else {
    error.value = res.message === '产品不存在' ? '产品不存在或已下架' : '获取产品信息失败'
  }
  loading.value = false
})
</script>

<style scoped>
.site-product-detail {
  background: var(--hd-bg);
}

.site-page-header {
  background-size: cover;
  background-position: center;
  padding: 72px 0;
  text-align: center;
}

.site-page-header h1 {
  margin: 0 0 10px;
  font-size: 2.2rem;
  font-weight: 800;
  letter-spacing: -0.02em;
  color: #181818;
  text-shadow: 0 0 10px rgba(255, 255, 255, 0.75);
}

.site-page-header p {
  margin: 0;
  font-size: 1.05rem;
  color: #444;
  text-shadow: 0 0 10px rgba(255, 255, 255, 0.75);
}

.site-sec {
  padding: 56px 0;
}

.site-detail-layout {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 48px;
}

.site-detail-img {
  border-radius: var(--hd-radius-xl);
  overflow: hidden;
  background: linear-gradient(135deg, var(--hd-brand-light), #f4fbf7);
  min-height: 320px;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 1px solid var(--hd-border);
}

.site-detail-img img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.site-detail-img .site-ph {
  color: var(--hd-brand);
  font-size: 64px;
}

.site-detail-info h2 {
  position: relative;
  margin: 0 0 16px;
  font-size: 1.7rem;
  font-weight: 800;
  color: var(--hd-text);
  padding-bottom: 12px;
}

.site-detail-info h2::after {
  content: '';
  position: absolute;
  bottom: 0;
  left: 0;
  width: 40px;
  height: 3px;
  border-radius: 3px;
  background: var(--hd-brand);
}

.site-detail-meta {
  display: flex;
  gap: 10px;
  margin-bottom: 18px;
  flex-wrap: wrap;
}

.site-detail-cat,
.site-detail-date {
  display: inline-flex;
  align-items: center;
  padding: 4px 12px;
  border-radius: 999px;
  background: var(--hd-surface);
  border: 1px solid var(--hd-border);
  color: var(--hd-text-2);
  font-size: 13px;
}

.site-detail-cat {
  background: var(--hd-brand-light);
  border-color: transparent;
  color: var(--hd-brand);
  font-weight: 600;
}

.site-detail-desc {
  margin: 0 0 24px;
  padding: 16px 18px;
  background: var(--hd-surface);
  border: 1px solid var(--hd-border);
  border-left: 4px solid var(--hd-brand);
  border-radius: var(--hd-radius-lg);
  color: var(--hd-text-2);
  line-height: 1.8;
  font-size: 15px;
}

.site-detail-feats h3 {
  position: relative;
  margin: 0 0 16px;
  font-size: 1.1rem;
  font-weight: 700;
  color: var(--hd-text);
  padding-bottom: 10px;
}

.site-detail-feats h3::after {
  content: '';
  position: absolute;
  bottom: 0;
  left: 0;
  width: 30px;
  height: 3px;
  border-radius: 3px;
  background: var(--hd-brand);
}

.site-feat-list {
  list-style: none;
  margin: 0 0 28px;
  padding: 0;
}

.site-feat-list li {
  position: relative;
  display: flex;
  align-items: flex-start;
  gap: 10px;
  padding: 10px 0;
  margin-bottom: 6px;
  color: var(--hd-text-2);
  font-size: 14px;
  line-height: 1.6;
  border-bottom: 1px dashed var(--hd-border);
}

.site-feat-icon {
  width: 20px;
  height: 20px;
  margin-top: 2px;
  flex-shrink: 0;
  display: grid;
  place-items: center;
  border-radius: 50%;
  background: var(--hd-brand);
  color: #fff;
  font-size: 13px;
}

.site-detail-actions {
  display: flex;
  gap: 12px;
  flex-wrap: wrap;
}

.site-empty {
  text-align: center;
  padding: 60px 16px;
  color: var(--hd-text-3);
}

.site-empty i {
  font-size: 40px;
  display: block;
  margin-bottom: 10px;
  color: #cbd5e1;
}

.site-empty p {
  font-size: 15px;
}

@media (max-width: 860px) {
  .site-detail-layout { grid-template-columns: 1fr; gap: 28px; }
}
</style>
