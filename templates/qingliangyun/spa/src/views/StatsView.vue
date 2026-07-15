<template>
  <div class="ql-page">
    <div class="ql-card">
      <h3 class="ql-section-title">站点统计</h3>
      <p class="ql-muted intro">详细图表与历史数据沿用系统接口，以下为轻量入口。</p>
      <el-button type="primary" round @click="openLegacy">打开完整统计页</el-button>
    </div>
    <div class="legacy-box ql-card mt">
      <iframe class="frame" :src="frameSrc" title="站点统计" />
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const frameSrc = computed(() => './site_stats.php?_ql=1')

function openLegacy() {
  window.open('./site_stats.php', '_blank')
}
</script>

<style scoped>
.intro {
  margin: 0 0 16px;
}
.mt {
  margin-top: 16px;
  padding: 0;
  overflow: hidden;
}
.legacy-box {
  min-height: 70vh;
}
.frame {
  width: 100%;
  height: 70vh;
  border: 0;
  display: block;
  background: #fff;
}
</style>
