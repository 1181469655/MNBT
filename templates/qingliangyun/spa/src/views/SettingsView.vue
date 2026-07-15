<template>
  <div class="ql-page">
    <div class="ql-card head">
      <div>
        <h3 class="ql-section-title" style="margin:0">{{ title }}</h3>
        <p class="ql-muted">配置项使用系统原生能力，界面由清凉云外壳承载</p>
      </div>
    </div>
    <div class="ql-card frame-card">
      <iframe class="frame" :src="src" :title="title" />
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'

const route = useRoute()

const labels = {
  php: 'PHP 版本切换',
  url: '域名修改',
  CDN_url: '域名修改',
  pass: '密码访问',
  mrwd: '默认文档',
  yxml: '运行目录',
  wjt: '伪静态',
  ssl: 'SSL 配置',
  fdl: '防盗链',
  gzip: 'Gzip 配置',
  cache: '缓存配置',
  xgpass: '修改密码',
  mysqlcz: 'SQL 权限设置',
}

const gn = computed(() => route.params.gn || 'php')
const title = computed(() => labels[gn.value] || '站点设置')
const src = computed(() => `./set.php?gn=${encodeURIComponent(gn.value)}&_ql=1`)
</script>

<style scoped>
.head {
  margin-bottom: 16px;
}
.head p {
  margin: 6px 0 0;
}
.frame-card {
  padding: 0;
  overflow: hidden;
}
.frame {
  width: 100%;
  height: 75vh;
  border: 0;
  display: block;
  background: #fff;
}
</style>
