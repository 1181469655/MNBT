<template>
  <div class="site-products">
    <section class="site-page-header" :style="{ backgroundImage: `url(${headerImage})` }">
      <div class="hd-container">
        <h1>产品中心</h1>
        <p>从建站托管到安全防护，一站式云基础设施服务</p>
      </div>
    </section>

    <section class="site-sec">
      <div class="hd-container">
        <div class="site-cat-filter">
          <button
            v-for="cat in categories"
            :key="cat.id"
            class="site-cat-btn"
            :class="{ active: activeCategory === cat.id }"
            @click="switchCategory(cat.id)"
          >
            {{ cat.name }}
          </button>
        </div>

        <t-loading :loading="loading" size="large">
          <div v-if="products.length === 0 && !loading" class="site-empty">
            <i class="mdi mdi-package-variant"></i>
            <p>暂无相关产品</p>
          </div>
          <div v-else class="site-products-grid">
            <div
              v-for="p in products"
              :key="p.id"
              class="site-product-card"
              @click="$router.push('/site/products/' + p.id)"
            >
              <div class="site-product-img">
                <img v-if="p.image" :src="p.image" :alt="p.name" />
                <div v-else class="site-ph"><i class="mdi mdi-package-variant"></i></div>
              </div>
              <div class="site-product-info">
                <h3 class="site-product-title">{{ p.name }}</h3>
                <p class="site-product-desc">{{ p.description }}</p>
                <div class="site-product-foot">
                  <span class="site-product-cat">{{ p.category_name }}</span>
                  <span class="site-product-more">了解更多 <i class="mdi mdi-arrow-right"></i></span>
                </div>
              </div>
            </div>
          </div>
        </t-loading>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import bg2 from '@/shared/assets/bg2.jpg'
import { getSiteProducts } from '@/home/api/site'

const headerImage = bg2
const router = useRouter()

const categories = ref([{ id: 'all', name: '全部产品' }])
const activeCategory = ref('all')
const products = ref([])
const loading = ref(true)

async function fetchProducts(category) {
  loading.value = true
  const res = await getSiteProducts(category === 'all' ? '' : category)
  if (res.ok && res.data?.products) {
    products.value = res.data.products
    if (res.data.categories && categories.value.length <= 1) {
      categories.value = [{ id: 'all', name: '全部产品' }, ...res.data.categories]
    }
  } else {
    products.value = []
  }
  loading.value = false
}

function switchCategory(id) {
  activeCategory.value = id
  fetchProducts(id)
}

onMounted(() => fetchProducts('all'))
</script>

<style scoped>
.site-products {
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
  padding: 48px 0;
}

/* 分类筛选 */
.site-cat-filter {
  display: flex;
  justify-content: center;
  flex-wrap: wrap;
  gap: 10px;
  margin-bottom: 32px;
}

.site-cat-btn {
  padding: 8px 20px;
  background: var(--hd-surface);
  border: 1px solid var(--hd-border);
  border-radius: 999px;
  cursor: pointer;
  transition: all var(--hd-dur) var(--hd-ease);
  font-size: 14px;
  color: var(--hd-text-2);
}

.site-cat-btn:hover,
.site-cat-btn.active {
  background: var(--hd-brand);
  color: #fff;
  border-color: var(--hd-brand);
}

/* 产品卡片 */
.site-products-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 20px;
}

.site-product-card {
  background: var(--hd-surface);
  border: 1px solid var(--hd-border);
  border-radius: var(--hd-radius-xl);
  overflow: hidden;
  cursor: pointer;
  transition: box-shadow var(--hd-dur) var(--hd-ease), transform var(--hd-dur) var(--hd-ease);
}

.site-product-card:hover {
  box-shadow: var(--hd-shadow-lg);
  transform: translateY(-3px);
}

.site-product-img {
  height: 160px;
  background: linear-gradient(135deg, var(--hd-brand-light), #f4fbf7);
  display: flex;
  align-items: center;
  justify-content: center;
}

.site-product-img img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.site-product-img .site-ph {
  color: var(--hd-brand);
  font-size: 40px;
}

.site-product-info {
  padding: 18px 20px 20px;
}

.site-product-title {
  position: relative;
  margin: 0 0 10px;
  font-size: 1.15rem;
  font-weight: 700;
  color: var(--hd-text);
  padding-bottom: 10px;
}

.site-product-title::after {
  content: '';
  position: absolute;
  bottom: 0;
  left: 0;
  width: 30px;
  height: 3px;
  border-radius: 3px;
  background: var(--hd-brand);
}

.site-product-desc {
  margin: 0 0 16px;
  color: var(--hd-text-2);
  font-size: 14px;
  line-height: 1.7;
  min-height: 48px;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.site-product-foot {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 8px;
}

.site-product-cat {
  display: inline-block;
  padding: 3px 12px;
  border-radius: 999px;
  background: var(--hd-brand-light);
  color: var(--hd-brand);
  font-size: 12px;
  font-weight: 600;
}

.site-product-more {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  color: var(--hd-brand);
  font-size: 13px;
  font-weight: 600;
  transition: gap var(--hd-dur) var(--hd-ease);
}

.site-product-card:hover .site-product-more {
  gap: 8px;
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

@media (max-width: 860px) {
  .site-products-grid { grid-template-columns: repeat(2, 1fr); }
}

@media (max-width: 560px) {
  .site-products-grid { grid-template-columns: 1fr; }
}
</style>
