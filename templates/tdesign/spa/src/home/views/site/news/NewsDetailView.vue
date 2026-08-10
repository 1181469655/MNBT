<template>
  <div class="site-news-detail">
    <section class="site-page-header" :style="{ backgroundImage: `url(${headerImage})` }">
      <div class="hd-container">
        <h1>新闻资讯</h1>
        <p>了解平台最新动态与行业资讯</p>
      </div>
    </section>

    <section class="site-sec">
      <div class="hd-container">
        <article v-if="news.id" class="site-article">
          <header class="site-article-head">
            <h1>{{ news.title }}</h1>
            <div class="site-article-meta">
              <span class="site-meta-chip"><i class="mdi mdi-calendar-month-outline"></i>{{ formatDate(news.created_at) }}</span>
              <span v-if="news.category" class="site-meta-chip"><i class="mdi mdi-tag-outline"></i>{{ news.category }}</span>
              <span class="site-meta-chip"><i class="mdi mdi-eye-outline"></i>{{ news.views }} 浏览</span>
            </div>
          </header>

          <div class="site-article-content" v-html="renderedContent"></div>

          <div class="site-article-foot">
            <t-button theme="primary" @click="$router.push('/site/news')">
              <template #prefix><i class="mdi mdi-arrow-left"></i></template>
              返回新闻列表
            </t-button>
          </div>
        </article>

        <div v-else-if="loading" class="site-empty">
          <i class="mdi mdi-loading mdi-spin"></i>
          <p>正在加载新闻内容...</p>
        </div>

        <div v-else-if="error" class="site-empty">
          <i class="mdi mdi-alert-circle-outline"></i>
          <p>{{ error }}</p>
          <t-button theme="primary" style="margin-top:12px" @click="$router.push('/site/news')">返回新闻列表</t-button>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import bg2 from '@/shared/assets/bg2.jpg'
import { getSiteNewsDetail } from '@/home/api/site'
import { formatDate } from '@/home/utils/format'

const headerImage = bg2
const route = useRoute()

const news = ref({})
const loading = ref(true)
const error = ref('')

// 将内容按空行分段渲染；单换行转 <br>，保留管理员录入的简单 HTML
const renderedContent = computed(() => {
  const raw = String(news.value.content || '')
  const paragraphs = raw.split(/\n{2,}/).map((p) => p.replace(/\n/g, '<br>').trim()).filter((p) => p !== '')
  return paragraphs.map((p) => `<p>${p}</p>`).join('')
})

onMounted(async () => {
  const res = await getSiteNewsDetail(route.params.id)
  if (res.ok && res.data?.news) {
    news.value = res.data.news
  } else {
    error.value = res.message === '新闻不存在' ? '新闻不存在或已下架' : '获取新闻内容失败'
  }
  loading.value = false
})
</script>

<style scoped>
.site-news-detail {
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

.site-article {
  max-width: 820px;
  margin: 0 auto;
  background: var(--hd-surface);
  border: 1px solid var(--hd-border);
  border-radius: var(--hd-radius-xl);
  padding: 36px 40px;
  box-shadow: var(--hd-shadow);
}

.site-article-head h1 {
  position: relative;
  margin: 0 0 18px;
  font-size: 1.7rem;
  font-weight: 800;
  color: var(--hd-text);
  line-height: 1.35;
  padding-bottom: 16px;
}

.site-article-head h1::after {
  content: '';
  position: absolute;
  bottom: 0;
  left: 0;
  width: 44px;
  height: 3px;
  border-radius: 3px;
  background: var(--hd-brand);
}

.site-article-meta {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  margin-bottom: 28px;
  padding-bottom: 20px;
  border-bottom: 1px solid var(--hd-border);
}

.site-meta-chip {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  padding: 4px 12px;
  border-radius: 999px;
  background: var(--hd-bg);
  border: 1px solid var(--hd-border);
  color: var(--hd-text-2);
  font-size: 13px;
}

.site-article-content {
  font-size: 15px;
  line-height: 2;
  color: var(--hd-text-2);
}

.site-article-content p {
  margin: 0 0 18px;
}

.site-article-foot {
  margin-top: 32px;
  padding-top: 20px;
  border-top: 1px solid var(--hd-border);
  text-align: center;
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

@media (max-width: 640px) {
  .site-article { padding: 24px 20px; }
  .site-article-head h1 { font-size: 1.4rem; }
}
</style>
