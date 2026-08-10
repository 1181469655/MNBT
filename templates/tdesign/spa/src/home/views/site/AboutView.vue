<template>
  <div class="site-about">
    <section class="site-page-header" :style="{ backgroundImage: `url(${headerImage})` }">
      <div class="hd-container">
        <h1>关于我们</h1>
        <p>了解 MNBT 的发展历程与使命愿景</p>
      </div>
    </section>

    <section class="site-sec">
      <div class="hd-container">
        <div class="site-about-grid">
          <div class="site-about-text">
            <h2>平台简介</h2>
            <p>
              MNBT 是一家面向企业和开发者的云计算服务商，专注于虚拟主机、云服务器、域名与安全防护等基础设施服务。依托高性能节点与全自动化部署体系，帮助用户以极低的成本快速上线业务。
            </p>
            <p>我们坚持"稳定、安全、简单"的产品理念，通过持续的技术迭代和完善的售后服务，已为大量个人站长与中小企业提供可靠的托管服务。</p>
          </div>
          <div class="site-about-image">
            <div class="site-ph">数据中心 · 高性能节点</div>
          </div>
        </div>
      </div>
    </section>

    <section class="site-sec site-sec--alt">
      <div class="hd-container">
        <div class="site-sec-head">
          <div class="site-sec-tag">Mission &amp; Vision</div>
          <h2>使命与愿景</h2>
        </div>
        <div class="site-mv-grid">
          <div class="site-mv-card">
            <div class="site-mv-icon"><i class="mdi mdi-crosshairs-gps"></i></div>
            <h3>我们的使命</h3>
            <p>通过自动化的云基础设施与贴心的技术服务，让每一位用户都能低成本、高效率地构建和运营自己的网站与业务。</p>
            <ul class="site-mv-list">
              <li>提供开箱即用的一键部署能力</li>
              <li>持续优化节点性能与稳定性</li>
              <li>以用户价值为先，做长期可靠的伙伴</li>
            </ul>
          </div>
          <div class="site-mv-card">
            <div class="site-mv-icon"><i class="mdi mdi-rocket-launch-outline"></i></div>
            <h3>我们的愿景</h3>
            <p>成为国内领先的轻量云服务品牌，让建站与托管像水电一样简单可靠，助力数字世界的每个创意落地。</p>
            <ul class="site-mv-list">
              <li>打造普惠的云计算服务平台</li>
              <li>建立覆盖全国的优质节点网络</li>
              <li>构建开放、共赢的服务生态</li>
            </ul>
          </div>
        </div>
      </div>
    </section>

    <section class="site-sec">
      <div class="hd-container">
        <div class="site-sec-head">
          <div class="site-sec-tag">Teams</div>
          <h2>核心团队</h2>
          <p>由基础设施专家、安全工程师与服务团队组成的多元化团队，全天候保障平台稳定运行。</p>
        </div>
        <div class="site-team-grid">
          <div v-for="t in teams" :key="t.title" class="site-team-card">
            <div class="site-team-avatar"><i :class="'mdi ' + t.icon"></i></div>
            <h3>{{ t.title }}</h3>
            <p class="site-team-role">{{ t.role }}</p>
            <p class="site-team-bio">{{ t.bio }}</p>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import bg2 from '@/shared/assets/bg2.jpg'

const headerImage = bg2

const teams = [
  { icon: 'mdi-server-network', title: '技术研发', role: '产品与架构', bio: '负责平台架构、自动化开通与功能迭代，持续打磨易用性。' },
  { icon: 'mdi-shield-lock', title: '安全合规', role: '安全运维', bio: '保障数据中心与业务系统的安全，通过等保三级等权威认证。' },
  { icon: 'mdi-headset', title: '客户服务', role: '7×24 支持', bio: '提供全天候技术支持与工单响应，快速解决用户问题。' },
  { icon: 'mdi-account-group', title: '运营服务', role: '用户成功', bio: '关注用户体验与反馈，推动服务与产品持续改进。' },
]
</script>

<style scoped>
.site-about {
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

.site-sec--alt {
  background: #f8faf9;
}

.site-sec-head {
  text-align: center;
  margin-bottom: 36px;
}

.site-sec-tag {
  display: inline-block;
  margin-bottom: 10px;
  padding: 4px 12px;
  border-radius: 999px;
  background: var(--hd-brand-light);
  color: var(--hd-brand);
  font-size: 12px;
  font-weight: 700;
  letter-spacing: 0.06em;
  text-transform: uppercase;
}

.site-sec-head h2 {
  margin: 0 0 8px;
  font-size: 1.6rem;
  font-weight: 800;
  letter-spacing: -0.02em;
  color: var(--hd-text);
}

.site-sec-head p {
  margin: 0 auto;
  max-width: 560px;
  color: var(--hd-text-3);
  font-size: 15px;
  line-height: 1.7;
}

/* 平台简介 */
.site-about-grid {
  display: grid;
  grid-template-columns: 1.6fr 1fr;
  gap: 48px;
  align-items: center;
}

.site-about-text h2 {
  position: relative;
  margin: 0 0 18px;
  font-size: 1.5rem;
  font-weight: 800;
  color: var(--hd-text);
  padding-bottom: 12px;
}

.site-about-text h2::after {
  content: '';
  position: absolute;
  bottom: 0;
  left: 0;
  width: 40px;
  height: 3px;
  border-radius: 3px;
  background: var(--hd-brand);
}

.site-about-text p {
  margin: 0 0 14px;
  color: var(--hd-text-2);
  line-height: 1.9;
  font-size: 15px;
}

.site-about-image .site-ph {
  height: 260px;
  display: grid;
  place-items: center;
  border-radius: var(--hd-radius-xl);
  background: linear-gradient(135deg, var(--hd-brand-light), #f0faf5);
  color: var(--hd-brand);
  font-size: 15px;
  font-weight: 600;
  border: 1px solid var(--hd-border);
}

/* 使命愿景 */
.site-mv-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 20px;
}

.site-mv-card {
  padding: 30px 26px;
  background: var(--hd-surface);
  border: 1px solid var(--hd-border);
  border-radius: var(--hd-radius-xl);
  box-shadow: var(--hd-shadow);
  transition: box-shadow var(--hd-dur) var(--hd-ease), transform var(--hd-dur) var(--hd-ease);
}

.site-mv-card:hover {
  box-shadow: var(--hd-shadow-lg);
  transform: translateY(-2px);
}

.site-mv-icon {
  width: 52px;
  height: 52px;
  display: grid;
  place-items: center;
  border-radius: 14px;
  background: var(--hd-brand-light);
  color: var(--hd-brand);
  font-size: 26px;
  margin-bottom: 16px;
}

.site-mv-card h3 {
  position: relative;
  margin: 0 0 14px;
  font-size: 1.15rem;
  font-weight: 700;
  color: var(--hd-text);
  padding-bottom: 10px;
}

.site-mv-card h3::after {
  content: '';
  position: absolute;
  bottom: 0;
  left: 0;
  width: 34px;
  height: 3px;
  border-radius: 3px;
  background: var(--hd-brand);
}

.site-mv-card p {
  margin: 0 0 16px;
  color: var(--hd-text-2);
  line-height: 1.8;
  font-size: 14px;
}

.site-mv-list {
  list-style: none;
  margin: 0;
  padding: 0;
}

.site-mv-list li {
  position: relative;
  padding-left: 22px;
  margin-bottom: 10px;
  color: var(--hd-text-2);
  font-size: 14px;
  line-height: 1.6;
}

.site-mv-list li::before {
  content: '';
  position: absolute;
  left: 0;
  top: 7px;
  width: 10px;
  height: 10px;
  border-radius: 50%;
  background: var(--hd-brand);
}

/* 核心团队 */
.site-team-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 18px;
}

.site-team-card {
  text-align: center;
  padding: 28px 18px;
  background: var(--hd-surface);
  border: 1px solid var(--hd-border);
  border-radius: var(--hd-radius-xl);
  box-shadow: var(--hd-shadow);
  transition: box-shadow var(--hd-dur) var(--hd-ease), transform var(--hd-dur) var(--hd-ease);
}

.site-team-card:hover {
  box-shadow: var(--hd-shadow-lg);
  transform: translateY(-3px);
}

.site-team-avatar {
  width: 68px;
  height: 68px;
  margin: 0 auto 14px;
  display: grid;
  place-items: center;
  border-radius: 50%;
  border: 3px solid var(--hd-brand);
  background: var(--hd-brand-light);
  color: var(--hd-brand);
  font-size: 28px;
}

.site-team-card h3 {
  margin: 0 0 4px;
  font-size: 15px;
  font-weight: 700;
  color: var(--hd-text);
}

.site-team-role {
  margin: 0 0 10px;
  color: var(--hd-brand);
  font-weight: 600;
  font-size: 13px;
}

.site-team-bio {
  margin: 0;
  color: var(--hd-text-3);
  font-size: 13px;
  line-height: 1.7;
}

@media (max-width: 860px) {
  .site-about-grid { grid-template-columns: 1fr; gap: 28px; }
  .site-mv-grid { grid-template-columns: 1fr; }
  .site-team-grid { grid-template-columns: repeat(2, 1fr); }
}

@media (max-width: 560px) {
  .site-team-grid { grid-template-columns: 1fr; }
}
</style>
