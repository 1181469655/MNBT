<template>
  <div class="site-news">
    <section class="site-page-header" :style="{ backgroundImage: `url(${headerImage})` }">
      <div class="hd-container">
        <h1>新闻资讯</h1>
        <p>了解平台最新动态、优惠活动与行业资讯</p>
      </div>
    </section>

    <section class="site-sec">
      <div class="hd-container">
        <div class="site-news-layout">
          <main class="site-news-main">
            <!-- 分类快捷筛选 -->
            <div class="site-news-tabs">
              <button class="site-news-tab" :class="{ active: category === '' }" @click="switchCategory('')">全部</button>
              <button
                v-for="cat in categories"
                :key="cat.category"
                class="site-news-tab"
                :class="{ active: category === cat.category }"
                @click="switchCategory(cat.category)"
              >
                {{ cat.category }}
              </button>
            </div>

            <t-loading :loading="loading" size="large">
              <div v-if="newsList.length === 0 && !loading" class="site-empty">
                <i class="mdi mdi-newspaper-variant-outline"></i>
                <p>暂无新闻</p>
              </div>
              <div v-else class="site-news-cards">
                <div v-for="n in newsList" :key="n.id" class="site-news-card" @click="$router.push('/site/news/' + n.id)">
                  <div class="site-news-body">
                    <h2 class="site-news-title">{{ n.title }}</h2>
                    <p class="site-news-excerpt">{{ excerpt(n.content) }}</p>
                    <div class="site-news-meta">
                      <span class="site-news-chip"><i class="mdi mdi-calendar-month-outline"></i>{{ formatDate(n.created_at) }}</span>
                      <span v-if="n.category" class="site-news-chip"><i class="mdi mdi-tag-outline"></i>{{ n.category }}</span>
                      <span class="site-news-chip"><i class="mdi mdi-eye-outline"></i>{{ n.views }} 浏览</span>
                    </div>
                    <div class="site-news-more">阅读全文 <i class="mdi mdi-arrow-right"></i></div>
                  </div>
                </div>
              </div>
            </t-loading>

            <!-- 分页 -->
            <div v-if="totalPages > 1" class="site-pagination">
              <button class="site-page-btn" :disabled="page <= 1" @click="goToPage(page - 1)">
                <i class="mdi mdi-chevron-left"></i>上一页
              </button>
              <div class="site-page-nums">
                <button
                  v-for="p in visiblePages"
                  :key="p"
                  class="site-page-num"
                  :class="{ active: p === page }"
                  @click="goToPage(p)"
                >
                  {{ p }}
                </button>
              </div>
              <button class="site-page-btn" :disabled="page >= totalPages" @click="goToPage(page + 1)">
                下一页<i class="mdi mdi-chevron-right"></i>
              </button>
            </div>
          </main>

          <aside class="site-news-sidebar">
            <div class="site-widget">
              <h3><i class="mdi mdi-trending-up"></i>热门文章</h3>
              <div v-for="(a, i) in popular" :key="a.id" class="site-popular" @click="$router.push('/site/news/' + a.id)">
                <span class="site-popular-rank" :class="{ top: i < 3 }">{{ i + 1 }}</span>
                <div class="site-popular-info">
                  <h4 class="site-popular-title">{{ a.title }}</h4>
                  <div class="site-popular-meta">
                    <span>{{ formatDate(a.created_at) }}</span>
                    <span>{{ a.views }} 浏览</span>
                  </div>
                </div>
              </div>
            </div>

            <div class="site-widget">
              <h3><i class="mdi mdi-information-outline"></i>关于我们</h3>
              <div class="site-widget-about">
                <p>MNBT 专注虚拟主机、云服务器与安全防护服务，支付即开、分钟级上线。</p>
                <router-link to="/about" class="site-widget-link">了解更多 <i class="mdi mdi-arrow-right"></i></router-link>
              </div>
            </div>
          </aside>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import bg3 from '@/shared/assets/bg3.jpg'
import { getSiteNews, getSiteNewsPopular } from '@/home/api/site'
import { formatDate } from '@/home/utils/format'

const headerImage = bg3
const route = useRoute()
const router = useRouter()

const perPage = 6
const page = ref(1)
const category = ref('')
const total = ref(0)
const newsList = ref([])
const categories = ref([])
const popular = ref([])
const loading = ref(true)

const totalPages = computed(() => Math.max(1, Math.ceil(total.value / perPage)))

const visiblePages = computed(() => {
  const maxVisible = 5
  const half = Math.floor(maxVisible / 2)
  let start = Math.max(1, page.value - half)
  let end = Math.min(totalPages.value, start + maxVisible - 1)
  if (end - start + 1 < maxVisible) {
    start = Math.max(1, end - maxVisible + 1)
  }
  const pages = []
  for (let i = start; i <= end; i++) pages.push(i)
  return pages
})

function excerpt(html, len = 96) {
  if (!html) return ''
  const text = String(html).replace(/<[^>]+>/g, ' ').replace(/\s+/g, ' ').trim()
  return text.length > len ? text.slice(0, len) + '…' : text
}

async function fetchNews() {
  loading.value = true
  const res = await getSiteNews({ page: page.value, perPage, category: category.value })
  if (res.ok && res.data) {
    newsList.value = res.data.news || []
    total.value = res.data.total || 0
    if (res.data.categories && Array.isArray(res.data.categories)) {
      categories.value = res.data.categories
    }
  } else {
    newsList.value = []
    total.value = 0
  }
  loading.value = false
}

function switchCategory(cat) {
  category.value = cat
  page.value = 1
  fetchNews()
}

function goToPage(p) {
  if (p < 1 || p > totalPages.value) return
  page.value = p
  fetchNews()
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

onMounted(async () => {
  const p = parseInt(route.query.page, 10)
  if (p > 0) page.value = p
  const popularRes = await getSiteNewsPopular()
  if (popularRes.ok && popularRes.data?.popular) {
    popular.value = popularRes.data.popular
  }
  fetchNews()
})
</script>

<style scoped>
.site-news {
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

.site-news-layout {
  display: grid;
  grid-template-columns: 2fr 1fr;
  gap: 32px;
  align-items: start;
}

/* 分类 tabs */
.site-news-tabs {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  margin-bottom: 20px;
}

.site-news-tab {
  padding: 6px 16px;
  background: var(--hd-surface);
  border: 1px solid var(--hd-border);
  border-radius: 999px;
  font-size: 13px;
  color: var(--hd-text-2);
  cursor: pointer;
  transition: all var(--hd-dur) var(--hd-ease);
}

.site-news-tab:hover,
.site-news-tab.active {
  background: var(--hd-brand);
  border-color: var(--hd-brand);
  color: #fff;
}

/* 新闻卡片 */
.site-news-cards {
  display: grid;
  gap: 16px;
}

.site-news-card {
  background: var(--hd-surface);
  border: 1px solid var(--hd-border);
  border-radius: var(--hd-radius-xl);
  padding: 20px 22px;
  cursor: pointer;
  transition: box-shadow var(--hd-dur) var(--hd-ease), transform var(--hd-dur) var(--hd-ease);
}

.site-news-card:hover {
  box-shadow: var(--hd-shadow-lg);
  transform: translateY(-2px);
}

.site-news-title {
  position: relative;
  margin: 0 0 10px;
  font-size: 1.15rem;
  font-weight: 700;
  color: var(--hd-text);
  padding-bottom: 10px;
  line-height: 1.4;
}

.site-news-title::after {
  content: '';
  position: absolute;
  bottom: 0;
  left: 0;
  width: 34px;
  height: 3px;
  border-radius: 3px;
  background: var(--hd-brand);
}

.site-news-excerpt {
  margin: 0 0 14px;
  color: var(--hd-text-2);
  font-size: 14px;
  line-height: 1.7;
}

.site-news-meta {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  margin-bottom: 12px;
}

.site-news-chip {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  padding: 3px 12px;
  border-radius: 999px;
  background: var(--hd-bg);
  border: 1px solid var(--hd-border);
  color: var(--hd-text-2);
  font-size: 12px;
}

.site-news-more {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  color: var(--hd-brand);
  font-size: 13px;
  font-weight: 600;
  transition: gap var(--hd-dur) var(--hd-ease);
}

.site-news-card:hover .site-news-more {
  gap: 8px;
}

/* 分页 */
.site-pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 10px;
  margin-top: 28px;
  flex-wrap: wrap;
}

.site-page-btn {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 8px 14px;
  background: var(--hd-surface);
  border: 1px solid var(--hd-border);
  border-radius: var(--hd-radius-lg);
  font-size: 13px;
  color: var(--hd-text-2);
  cursor: pointer;
  transition: all var(--hd-dur) var(--hd-ease);
}

.site-page-btn:hover:not(:disabled) {
  color: var(--hd-brand);
  border-color: var(--hd-brand);
}

.site-page-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.site-page-nums {
  display: flex;
  gap: 6px;
}

.site-page-num {
  width: 34px;
  height: 34px;
  display: grid;
  place-items: center;
  background: var(--hd-surface);
  border: 1px solid var(--hd-border);
  border-radius: var(--hd-radius-lg);
  font-size: 13px;
  color: var(--hd-text-2);
  cursor: pointer;
  transition: all var(--hd-dur) var(--hd-ease);
}

.site-page-num:hover,
.site-page-num.active {
  background: var(--hd-brand);
  border-color: var(--hd-brand);
  color: #fff;
}

/* 侧边栏 */
.site-news-sidebar {
  position: sticky;
  top: 76px;
  display: grid;
  gap: 16px;
}

.site-widget {
  background: var(--hd-surface);
  border: 1px solid var(--hd-border);
  border-radius: var(--hd-radius-xl);
  padding: 20px;
  box-shadow: var(--hd-shadow);
}

.site-widget h3 {
  position: relative;
  display: flex;
  align-items: center;
  gap: 8px;
  margin: 0 0 16px;
  font-size: 15px;
  font-weight: 700;
  color: var(--hd-text);
  padding-bottom: 12px;
  border-bottom: 1px solid var(--hd-border);
}

.site-widget h3::after {
  content: '';
  position: absolute;
  bottom: -1px;
  left: 0;
  width: 30px;
  height: 3px;
  border-radius: 3px;
  background: var(--hd-brand);
}

.site-popular {
  display: flex;
  gap: 12px;
  padding: 10px 6px;
  border-radius: var(--hd-radius-lg);
  cursor: pointer;
  transition: background var(--hd-dur) var(--hd-ease);
}

.site-popular:hover {
  background: var(--hd-brand-light);
}

.site-popular-rank {
  width: 24px;
  height: 24px;
  flex-shrink: 0;
  display: grid;
  place-items: center;
  border-radius: 50%;
  background: var(--hd-bg);
  color: var(--hd-text-3);
  font-size: 12px;
  font-weight: 700;
  margin-top: 2px;
}

.site-popular-rank.top {
  background: var(--hd-brand);
  color: #fff;
}

.site-popular-title {
  margin: 0 0 6px;
  font-size: 14px;
  font-weight: 600;
  color: var(--hd-text);
  line-height: 1.5;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.site-popular:hover .site-popular-title {
  color: var(--hd-brand);
}

.site-popular-meta {
  display: flex;
  gap: 12px;
  font-size: 12px;
  color: var(--hd-text-3);
}

.site-widget-about p {
  margin: 0 0 12px;
  color: var(--hd-text-2);
  font-size: 13px;
  line-height: 1.8;
}

.site-widget-link {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  color: var(--hd-brand);
  font-size: 13px;
  font-weight: 600;
  text-decoration: none;
}

.site-widget-link:hover {
  text-decoration: none;
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
  .site-news-layout { grid-template-columns: 1fr; }
  .site-news-sidebar { position: static; }
}
</style>
