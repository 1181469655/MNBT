<template>
  <div class="td-ftp-page">
    <div class="td-ftp-head">
      <div class="td-ftp-title">
        <i class="mdi mdi-folder-multiple-outline"></i>
        <span>在线文件管理</span>
      </div>
      <div class="td-ftp-actions">
        <t-button theme="default" variant="outline" size="small" @click="reload" :loading="loading">
          <i class="mdi mdi-refresh"></i> 刷新
        </t-button>
        <t-button theme="default" variant="outline" size="small" @click="openInNewTab">
          <i class="mdi mdi-open-in-new"></i> 新窗口
        </t-button>
      </div>
    </div>
    <div class="td-ftp-frame-wrap">
      <div v-if="loading" class="td-ftp-loading">
        <t-loading text="文件管理器加载中…" size="large" />
      </div>
      <iframe
        v-show="!loading"
        ref="iframeRef"
        :src="iframeSrc"
        @load="onLoad"
        frameborder="0"
        class="td-ftp-iframe"
      ></iframe>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue'

const iframeRef = ref(null)
const loading = ref(true)
const iframeSrc = './ftp.php'

function onLoad() {
  loading.value = false
}

function reload() {
  loading.value = true
  if (iframeRef.value) {
    // 重新加载 iframe
    const src = iframeRef.value.src
    iframeRef.value.src = 'about:blank'
    setTimeout(() => {
      if (iframeRef.value) iframeRef.value.src = src
    }, 50)
  }
}

function openInNewTab() {
  window.open(iframeSrc, '_blank')
}

function onResize() {
  // iframe 高度自适应由 CSS 处理,无需手动调整
}

onMounted(() => {
  window.addEventListener('resize', onResize)
})

onBeforeUnmount(() => {
  window.removeEventListener('resize', onResize)
})
</script>

<style scoped>
.td-ftp-page {
  display: flex;
  flex-direction: column;
  height: 100%;
  min-height: calc(100vh - var(--td-header-height));
  background: var(--td-bg);
}
.td-ftp-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 14px 20px;
  background: var(--td-surface);
  border-bottom: 1px solid var(--td-border);
  gap: 12px;
}
.td-ftp-title {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 15px;
  font-weight: 600;
  color: var(--td-text);
}
.td-ftp-title i {
  font-size: 18px;
  color: var(--td-brand);
}
.td-ftp-actions {
  display: flex;
  gap: 8px;
}
.td-ftp-frame-wrap {
  flex: 1;
  position: relative;
  overflow: hidden;
  background: var(--td-bg);
}
.td-ftp-loading {
  position: absolute;
  inset: 0;
  display: grid;
  place-items: center;
  background: var(--td-bg);
  z-index: 2;
}
.td-ftp-iframe {
  width: 100%;
  height: 100%;
  border: none;
  display: block;
  min-height: calc(100vh - var(--td-header-height) - 56px);
}
</style>
