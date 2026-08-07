<template>
  <div class="td-plugin-page">
    <div class="td-plugin-head">
      <div class="td-plugin-title">
        <i class="mdi mdi-puzzle"></i>
        <span>{{ title || '插件页面' }}</span>
      </div>
      <div class="td-plugin-actions">
        <t-button theme="default" variant="outline" size="small" @click="reload" :loading="loading">
          <i class="mdi mdi-refresh"></i> 刷新
        </t-button>
        <t-button theme="default" variant="outline" size="small" @click="openInNewTab">
          <i class="mdi mdi-open-in-new"></i> 新窗口
        </t-button>
      </div>
    </div>
    <div class="td-plugin-frame-wrap">
      <div v-if="loading" class="td-plugin-loading">
        <t-loading text="插件页面加载中…" size="large" />
      </div>
      <div v-if="!iframeSrc" class="td-plugin-empty">
        <i class="mdi mdi-alert-circle-outline"></i>
        <span>未指定插件页面</span>
      </div>
      <iframe
        v-show="!loading && iframeSrc"
        ref="iframeRef"
        :src="iframeSrc"
        @load="onLoad"
        frameborder="0"
        class="td-plugin-iframe"
      ></iframe>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { useRoute } from 'vue-router'

const route = useRoute()
const iframeRef = ref(null)
const loading = ref(true)

const p = computed(() => route.query.p || '')
const page = computed(() => route.query.page || 'index')
const title = computed(() => route.query.title || route.meta.title || '')
const iframeSrc = computed(() => {
  if (!p.value) return ''
  const params = new URLSearchParams({
    p: String(p.value),
    page: String(page.value),
  })
  return './plugin.php?' + params.toString()
})

function onLoad() {
  loading.value = false
}

function reload() {
  if (!iframeRef.value) return
  loading.value = true
  const src = iframeRef.value.src
  iframeRef.value.src = 'about:blank'
  setTimeout(() => {
    if (iframeRef.value) iframeRef.value.src = src
  }, 50)
}

function openInNewTab() {
  if (iframeSrc.value) window.open(iframeSrc.value, '_blank')
}

watch(
  () => route.fullPath,
  () => {
    if (iframeSrc.value) loading.value = true
  },
)
</script>

<style scoped>
.td-plugin-page {
  display: flex;
  flex-direction: column;
  height: 100%;
  min-height: calc(100vh - var(--td-header-height));
  background: var(--td-bg);
}
.td-plugin-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 12px 20px;
  background: var(--td-surface);
  border-bottom: 1px solid var(--td-border);
  gap: 12px;
}
.td-plugin-title {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 15px;
  font-weight: 600;
  color: var(--td-text);
  min-width: 0;
}
.td-plugin-title i {
  font-size: 18px;
  color: var(--td-brand);
  flex-shrink: 0;
}
.td-plugin-title span {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
.td-plugin-actions {
  display: flex;
  gap: 8px;
  flex-shrink: 0;
}
.td-plugin-frame-wrap {
  flex: 1;
  position: relative;
  overflow: hidden;
  background: var(--td-bg);
}
.td-plugin-loading {
  position: absolute;
  inset: 0;
  display: grid;
  place-items: center;
  background: var(--td-bg);
  z-index: 2;
}
.td-plugin-empty {
  position: absolute;
  inset: 0;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 8px;
  color: var(--td-text-placeholder);
  font-size: 13px;
}
.td-plugin-empty i {
  font-size: 38px;
  color: #cbd5e1;
}
.td-plugin-iframe {
  width: 100%;
  height: 100%;
  border: none;
  display: block;
  min-height: calc(100vh - var(--td-header-height) - 52px);
  background: #fff;
}
</style>
