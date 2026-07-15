<template>
  <div class="ql-page">
    <div class="ql-card head">
      <div>
        <h3 class="ql-section-title" style="margin:0">监控任务</h3>
        <p class="ql-muted">URL / 资源阈值监控</p>
      </div>
      <el-button type="primary" round @click="reload">刷新</el-button>
    </div>
    <div class="ql-card frame-card">
      <iframe ref="frame" class="frame" :src="src" title="监控任务" @load="onLoad" />
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const src = ref('./monitor.php?_ql=1')
const frame = ref(null)

function reload() {
  src.value = `./monitor.php?_ql=1&t=${Date.now()}`
}

function onLoad() {
  // 可后续注入样式
}
</script>

<style scoped>
.head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 16px;
  gap: 12px;
}
.head p {
  margin: 6px 0 0;
}
.frame-card {
  padding: 0;
  overflow: hidden;
  min-height: 70vh;
}
.frame {
  width: 100%;
  height: 72vh;
  border: 0;
  display: block;
}
</style>
