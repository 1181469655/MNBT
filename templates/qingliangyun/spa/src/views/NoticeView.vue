<template>
  <div class="ql-page">
    <div class="ql-card head">
      <div>
        <h3 class="ql-section-title" style="margin:0">通知日志</h3>
        <p class="ql-muted">系统与监控通知记录</p>
      </div>
      <el-button type="primary" round @click="reload">刷新</el-button>
    </div>
    <div class="ql-card frame-card">
      <iframe class="frame" :src="src" title="通知日志" />
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const src = ref('./notice.php?_ql=1')

function reload() {
  src.value = `./notice.php?_ql=1&t=${Date.now()}`
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
}
.frame {
  width: 100%;
  height: 72vh;
  border: 0;
  display: block;
}
</style>
