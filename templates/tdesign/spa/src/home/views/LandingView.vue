<template>
  <div class="home">
    <!-- Hero 轮播（guanwang1 风格：全屏背景图 + 居中文案 + 双向箭头 + 指示器） -->
    <section class="hero">
      <div class="carousel">
        <div class="carousel-inner">
          <div
            v-for="(item, index) in carouselItems"
            :key="index"
            class="carousel-item"
            :class="{ active: index === currentSlide }"
          >
            <div class="carousel-bg" :style="{ 'background-image': 'url(' + item.image + ')' }"></div>
            <div class="carousel-caption">
              <h2 class="carousel-title">{{ item.title }}</h2>
              <p class="carousel-subtitle">{{ item.subtitle }}</p>
              <p class="carousel-description">{{ item.description }}</p>
              <div class="hero-buttons">
                <router-link :to="productsUrl" class="btn btn-primary">了解产品</router-link>
                <router-link :to="aboutUrl" class="btn btn-secondary">关于我们</router-link>
              </div>
            </div>
          </div>
        </div>
        <div class="carousel-controls">
          <button class="carousel-btn prev" @click="prevSlide">‹</button>
          <button class="carousel-btn next" @click="nextSlide">›</button>
        </div>
        <div class="carousel-indicators">
          <span
            v-for="(item, index) in carouselItems"
            :key="index"
            :class="{ active: index === currentSlide }"
            @click="goToSlide(index)"
          ></span>
        </div>
      </div>
    </section>

    <!-- 公告 -->
    <section v-if="boot.showNotice && boot.notice" class="notice-strip">
      <div class="container">
        <div class="hd-notice">
          <i class="mdi mdi-bullhorn"></i>
          <div class="txt">{{ boot.notice }}</div>
        </div>
      </div>
    </section>

    <!-- 套餐区（hosting_shop 插件） -->
    <section v-if="boot.showPlans !== false && boot.hasShop" class="plans-section section">
      <div class="container">
        <div class="section-header">
          <h2 class="section-title">选择适合的套餐</h2>
          <p class="section-subtitle">按需选择，随时升级。价格透明，开通简单。</p>
        </div>
        <div v-if="plans.length === 0" class="hd-empty">
          <i class="mdi mdi-package-variant"></i>
          <p>暂无可购买套餐，请联系管理员</p>
        </div>
        <div v-else class="hd-plans">
          <div
            v-for="(plan, i) in plans.slice(0, 3)"
            :key="plan.id"
            class="hd-plan-card"
            :class="{ pop: i === 1 }"
          >
            <div class="hd-plan-top">
              <span v-if="i === 1" class="hd-plan-chip">推荐</span>
              <h3>{{ plan.name }}</h3>
              <div class="hd-plan-desc">{{ plan.desc || '适合中小站点快速上线' }}</div>
            </div>
            <div class="hd-plan-body">
              <div class="hd-plan-price">
                <div class="num">{{ plan.price }}</div>
                <div class="sub">含基础资源与自动开通</div>
              </div>
              <ul class="hd-plan-feats">
                <li v-for="feat in plan.feats" :key="feat"><span class="ok">✓</span>{{ feat }}</li>
                <li v-if="!plan.feats || plan.feats.length === 0"><span class="ok">✓</span>高性能节点资源</li>
                <li v-if="!plan.feats || plan.feats.length === 0"><span class="ok">✓</span>一键开通部署</li>
              </ul>
              <t-button block theme="primary" size="large" @click="$router.push('/shop/order/' + plan.id)">立即购买</t-button>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- 核心优势 -->
    <section class="features section">
      <div class="container">
        <div class="section-header">
          <h2 class="section-title">核心优势</h2>
          <p class="section-subtitle">我们专注于为客户提供稳定可靠的虚拟主机与云计算服务</p>
        </div>
        <div class="features-grid">
          <div v-for="f in features" :key="f.title" class="feature-card">
            <div class="feature-icon"><i :class="'mdi ' + f.icon"></i></div>
            <h3>{{ f.title }}</h3>
            <p>{{ f.desc }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- 新闻预览（official_site 插件） -->
    <section v-if="boot.hasSite" class="news-preview section">
      <div class="container">
        <div class="section-header">
          <h2 class="section-title">新闻资讯</h2>
          <p class="section-subtitle">了解平台最新动态与发展历程</p>
          <router-link to="/site/news" class="more-news">查看更多新闻 ›</router-link>
        </div>
        <div v-if="newsList.length === 0" class="hd-empty">
          <i class="mdi mdi-newspaper-variant-outline"></i>
          <p>暂无资讯</p>
        </div>
        <div v-else class="news-grid">
          <div v-for="n in newsList" :key="n.id" class="news-card" @click="$router.push('/site/news/' + n.id)">
            <div class="news-date">{{ formatDate(n.created_at) }}</div>
            <h3 class="news-title">{{ n.title }}</h3>
            <p class="news-excerpt">{{ excerpt(n.content) }}</p>
            <span class="read-more">阅读更多</span>
          </div>
        </div>
      </div>
    </section>

    <!-- 客户评价 -->
    <section class="testimonials section">
      <div class="container">
        <div class="section-header">
          <h2 class="section-title">客户评价</h2>
          <p class="section-subtitle">听听我们的客户怎么说</p>
        </div>
        <div class="testimonials-grid">
          <div v-for="t in testimonials" :key="t.name" class="testimonial-card">
            <div class="testimonial-header">
              <div class="client-avatar">
                <div class="avatar-placeholder">{{ t.name.slice(0, 1) }}</div>
              </div>
              <div class="client-details">
                <div class="client-name">{{ t.name }}</div>
                <div class="client-title">{{ t.role }}</div>
              </div>
            </div>
            <p class="testimonial-text">"{{ t.text }}"</p>
            <i class="mdi mdi-format-quote-close quote-icon"></i>
          </div>
        </div>
      </div>
    </section>

    <!-- 底部 CTA -->
    <section v-if="!authState.loggedIn && boot.hasUser" class="section">
      <div class="container">
        <div class="hd-cta">
          <h3>准备好开始了吗？</h3>
          <p>注册账户，立即体验高性能虚拟主机。</p>
          <router-link to="/register" class="btn btn-light">免费注册</router-link>
        </div>
      </div>
    </section>

    <!-- 扩展区块 -->
    <section v-for="block in boot.blocks" :key="block.id" class="section">
      <div class="container">
        <div v-if="block.title" class="section-header">
          <h2 class="section-title">{{ block.title }}</h2>
        </div>
        <div v-html="block.html"></div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import authState from '@/home/store/auth'
import { getSiteNews } from '@/home/api/site'
import { formatDate } from '@/home/utils/format'
import bg1 from '@/shared/assets/bg1.jpg'
import bg2 from '@/shared/assets/bg2.jpg'
import bg3 from '@/shared/assets/bg3.jpg'

const boot = window.__TD_BOOT__ || {}

const plans = computed(() => boot.plans || [])

// hero 按钮路由：优先官网产品页，无官网插件则回退套餐/登录
const productsUrl = computed(() =>
  boot.hasSite ? '/site/products' : (boot.hasShop ? '/shop' : '/')
)
const aboutUrl = computed(() =>
  boot.hasSite ? '/about'
    : (boot.hasUser ? (authState.loggedIn ? '/profile' : '/login') : '/')
)

const carouselItems = [
  {
    title: '高性能虚拟主机',
    subtitle: '即买即用 · 自动开通 · 秒级部署',
    description: '全 SSD 存储与 BGP 多线接入，支付完成后自动开通，分钟级上线，为企业和开发者打造稳定高效的主机平台。',
    image: bg1,
  },
  {
    title: '专业团队支持',
    subtitle: '7×24 小时全天候技术支持',
    description: '经验丰富的运维与开发团队随时待命，从建站到运维全程护航，让您专注于业务本身。',
    image: bg2,
  },
  {
    title: '企业级安全防护',
    subtitle: 'DDoS 清洗 · WAF 规则 · 每日备份',
    description: '内置安全防护体系与自动备份能力，SSL 一键签发，全面保障您的数据与业务安全。',
    image: bg3,
  },
]

const currentSlide = ref(0)
let timer = null

function nextSlide() {
  currentSlide.value = (currentSlide.value + 1) % carouselItems.length
}
function prevSlide() {
  currentSlide.value = (currentSlide.value - 1 + carouselItems.length) % carouselItems.length
}
function goToSlide(index) {
  currentSlide.value = index
}
function startAutoPlay() {
  stopAutoPlay()
  timer = setInterval(nextSlide, 5000)
}
function stopAutoPlay() {
  if (timer) {
    clearInterval(timer)
    timer = null
  }
}

const features = [
  { icon: 'mdi-shield-check', title: '稳定可靠', desc: '企业级硬件架构与 BGP 多线接入，SLA 99.9% 保障，网站始终在线。' },
  { icon: 'mdi-rocket-launch-outline', title: '极速开通', desc: '支付成功自动开通主机，分钟级上线，无需等待人工处理。' },
  { icon: 'mdi-headset', title: '专业服务', desc: '7×24 小时技术支持，从建站到运维全程护航，问题快速响应。' },
]

const newsList = ref([])

function excerpt(html, len = 72) {
  if (!html) return ''
  const text = String(html).replace(/<[^>]+>/g, ' ').replace(/\s+/g, ' ').trim()
  return text.length > len ? text.slice(0, len) + '…' : text
}

const testimonials = [
  { name: '张先生', role: '个人站长', text: '从下单到开通不到一分钟，面板操作也很顺手，续费两年了一直很稳定。' },
  { name: '李女士', role: '电商创业者', text: '网站迁移过来后访问速度明显提升，客服响应很快，问题当天就解决了。' },
  { name: '陈先生', role: '企业运维', text: '自动备份和 SSL 功能非常省心，售后工单处理专业，值得信赖。' },
]

onMounted(async () => {
  startAutoPlay()
  if (boot.hasSite) {
    const res = await getSiteNews({ page: 1, perPage: 3 })
    if (res.ok && res.data?.news) {
      newsList.value = res.data.news
    }
  }
})
onUnmounted(stopAutoPlay)
</script>
