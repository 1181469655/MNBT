<template>
  <div class="ql-page">
    <div class="ql-card head">
      <h3 class="ql-section-title" style="margin:0">{{ title }}</h3>
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

const title = computed(() => String(route.query.t || '功能页面'))
const src = computed(() => {
  const u = String(route.query.u || 'sy.php')
  // 仅允许相对 user 路径，防止开放重定向
  if (u.includes('://') || u.startsWith('//') || u.includes('..')) {
    return './sy.php'
  }
  const sep = u.includes('?') ? '&' : '?'
  return `./${u}${sep}_ql=1`
})
</script>

<style scoped>
.head {
  margin-bottom: 16px;
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
