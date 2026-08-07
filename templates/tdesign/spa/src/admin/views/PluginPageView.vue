<template>
  <div class="td-plugin-page">
    <div class="td-plugin-head">
      <div class="td-plugin-title">
        <i class="mdi mdi-puzzle"></i>
        <span>{{ pageTitle }}</span>
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
        <t-loading text="加载中…" size="large" />
      </div>
      <iframe
        v-show="!loading"
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
import { ref, computed, watch, onMounted, onBeforeUnmount, nextTick } from 'vue'
import { useRoute } from 'vue-router'

const route = useRoute()
const iframeRef = ref(null)
const loading = ref(true)
const pageTitle = ref('插件页面')

const iframeSrc = computed(() => {
  const p = route.query.p || ''
  const page = route.query.page || ''
  if (!p) return ''
  let url = './plugin.php?p=' + encodeURIComponent(p)
  if (page) url += '&page=' + encodeURIComponent(page)
  return url
})

function onLoad() {
  loading.value = false
  adjustHeight()
  extractTitle()
}

function adjustHeight() {
  const iframe = iframeRef.value
  if (!iframe) return
  try {
    const doc = iframe.contentDocument || iframe.contentWindow.document
    if (!doc) return
    // 重置 height 为 auto 以获取真实高度
    iframe.style.height = 'auto'
    const height = Math.max(
      doc.body.scrollHeight,
      doc.documentElement.scrollHeight,
      doc.body.offsetHeight,
      doc.documentElement.offsetHeight,
    )
    iframe.style.height = (height + 20) + 'px'
  } catch (e) {
    // 跨域无法读取,设置一个默认高度
    iframe.style.height = '80vh'
  }
}

function extractTitle() {
  const iframe = iframeRef.value
  if (!iframe) return
  try {
    const doc = iframe.contentDocument || iframe.contentWindow.document
    if (!doc) return
    const title = doc.title || doc.querySelector('h1, h2, h3')?.textContent
    if (title) pageTitle.value = title
  } catch (e) {
    // 跨域忽略
  }
}

function reload() {
  loading.value = true
  if (iframeRef.value) {
    iframeRef.value.src = iframeSrc.value
  }
}

function openInNewTab() {
  if (iframeSrc.value) {
    window.open(iframeSrc.value, '_blank')
  }
}

// 监听窗口大小变化,重新调整高度
function onResize() {
  adjustHeight()
}

// 监听路由参数变化,重新加载
watch(
  () => route.fullPath,
  () => {
    if (iframeSrc.value) {
      loading.value = true
      nextTick(() => {
        if (iframeRef.value) {
          iframeRef.value.src = iframeSrc.value
        }
      })
    }
  },
)

onMounted(() => {
  window.addEventListener('resize', onResize)
  // 从路由参数初始化标题
  const p = route.query.p || ''
  const page = route.query.page || ''
  if (p && page) {
    pageTitle.value = p + ' / ' + page
  } else if (p) {
    pageTitle.value = p
  }
})

onBeforeUnmount(() => {
  window.removeEventListener('resize', onResize)
})
</script>

<style scoped>
.td-plugin-page {
  display: flex;
  flex-direction: column;
  height: 100%;
  background: var(--td-bg);
}
.td-plugin-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 14px 20px;
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
}
.td-plugin-title i {
  font-size: 18px;
  color: var(--td-brand);
}
.td-plugin-actions {
  display: flex;
  gap: 8px;
}
.td-plugin-frame-wrap {
  flex: 1;
  position: relative;
  overflow: hidden;
}
.td-plugin-loading {
  position: absolute;
  inset: 0;
  display: grid;
  place-items: center;
  background: var(--td-bg);
}
.td-plugin-iframe {
  width: 100%;
  height: 100%;
  border: none;
  display: block;
  min-height: calc(100vh - var(--td-header-height) - 56px);
}
</style>
