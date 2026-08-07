<template>
  <div class="td-page">
    <div class="td-page-head">
      <div>
        <h3 class="td-page-title"><i class="mdi mdi-cloud-download"></i>系统更新</h3>
        <p class="td-page-subtitle">检查新版本并执行在线更新</p>
      </div>
      <t-button theme="default" variant="outline" @click="recheck">
        <i class="mdi mdi-refresh"></i> 重新检查
      </t-button>
    </div>

    <div class="upd-wrap">
      <!-- 错误态 -->
      <div v-if="!info" class="td-set-card">
        <div class="td-set-card-hd">
          <div class="td-set-icon" style="background: #fdecee; color: #d54941">
            <i class="mdi mdi-alert-circle-outline"></i>
          </div>
          <div>
            <h4>检查更新失败</h4>
            <p>无法连接到更新服务器,请稍后重试</p>
          </div>
        </div>
      </div>

      <!-- 正常态 -->
      <template v-else>
        <!-- 版本卡片 -->
        <div class="stat-row">
          <div class="stat-card">
            <div class="stat-icon" style="background: #0052d9">
              <i class="mdi mdi-tag"></i>
            </div>
            <div>
              <div class="stat-num">{{ currentVersion }}</div>
              <div class="stat-label">当前版本</div>
            </div>
          </div>
          <div class="stat-card">
            <div class="stat-icon" :style="{ background: hasUpdate ? '#e37318' : '#2ba471' }">
              <i class="mdi" :class="hasUpdate ? 'mdi-arrow-up-bold-circle' : 'mdi-check-circle'"></i>
            </div>
            <div>
              <div class="stat-num">{{ latestVersion }}</div>
              <div class="stat-label">最新版本</div>
            </div>
          </div>
        </div>

        <!-- 更新提示 -->
        <div v-if="hasUpdate" class="td-set-card">
          <div class="td-set-card-hd">
            <div class="td-set-icon" style="background: #fff3e0; color: #e37318">
              <i class="mdi mdi-alert-circle"></i>
            </div>
            <div>
              <h4>发现新版本</h4>
              <p>{{ info.msg || '建议尽快更新以获取最新功能与安全修复' }}</p>
            </div>
            <t-button theme="warning" :loading="updating" @click="doUpdate" class="upd-btn">
              <i class="mdi mdi-update"></i> 立刻更新
            </t-button>
          </div>
          <div class="td-set-card-bd" v-if="info.uplog">
            <h5 class="upd-section">更新日志</h5>
            <pre class="td-code-block upd-log">{{ info.uplog }}</pre>
          </div>
        </div>

        <!-- 已是最新 -->
        <div v-else-if="isLatest" class="td-set-card">
          <div class="td-set-card-hd">
            <div class="td-set-icon" style="background: #e8f8f0; color: #2ba471">
              <i class="mdi mdi-check-circle"></i>
            </div>
            <div>
              <h4>已是最新版本</h4>
              <p>{{ info.msg || '当前版本已是最新,无需更新' }}</p>
            </div>
          </div>
          <div class="td-set-card-bd" v-if="info.uplog">
            <h5 class="upd-section">版本日志</h5>
            <pre class="td-code-block upd-log">{{ info.uplog }}</pre>
          </div>
        </div>

        <!-- 离线模式 -->
        <div v-else class="td-set-card">
          <div class="td-set-card-hd">
            <div class="td-set-icon" style="background: #f5f6f8; color: #8c8c8c">
              <i class="mdi mdi-account-off"></i>
            </div>
            <div>
              <h4>{{ info.msg || '离线模式' }}</h4>
              <p>离线模式不提供更新服务</p>
            </div>
          </div>
        </div>
      </template>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { MessagePlugin } from 'tdesign-vue-next'
import { systemUpdate } from '@/admin/api/dashboard'

const boot = window.__TD_BOOT__ || {}

// 服务器端已检查更新,直接读取 boot
const info = ref(boot.updateInfo || null)
const currentVersion = ref(boot.currentVersion || 'V0.00')

const hasUpdate = computed(() => info.value && String(info.value.code) === '1')
const isLatest = computed(() => info.value && String(info.value.code) === '0')

const latestVersion = computed(() => {
  if (!info.value || !info.value.ver) return '-'
  return info.value.ver
})

const updating = ref(false)

async function doUpdate() {
  updating.value = true
  const r = await systemUpdate()
  updating.value = false
  if (r.ok) {
    MessagePlugin.success('更新成功,请手动刷新页面')
    setTimeout(() => {
      window.location.reload()
    }, 1200)
  } else {
    MessagePlugin.error(r.message || '更新失败')
  }
}

function recheck() {
  // 重新检查需要重新请求 PHP(服务器端 send_post)
  window.location.reload()
}
</script>

<style scoped>
.upd-wrap {
  display: flex;
  flex-direction: column;
  gap: 14px;
}
.stat-row {
  display: flex;
  flex-wrap: wrap;
  gap: 12px;
}
.stat-card {
  flex: 1;
  min-width: 200px;
  background: var(--td-surface);
  border: 1px solid var(--td-border);
  border-radius: var(--td-radius-lg);
  padding: 16px;
  display: flex;
  align-items: center;
  gap: 12px;
  box-shadow: var(--td-shadow);
}
.stat-icon {
  width: 44px;
  height: 44px;
  border-radius: 8px;
  display: grid;
  place-items: center;
  color: #fff;
  font-size: 22px;
  flex-shrink: 0;
}
.stat-num {
  font-size: 20px;
  font-weight: 700;
  color: var(--td-text);
  line-height: 1.2;
}
.stat-label {
  font-size: 12px;
  color: var(--td-text-secondary);
  margin-top: 2px;
}
.upd-btn {
  margin-left: auto;
}
.upd-section {
  margin: 0 0 8px;
  font-size: 13px;
  font-weight: 600;
  color: var(--td-text);
}
.upd-log {
  white-space: pre-wrap;
  word-break: break-word;
  max-height: 320px;
  overflow-y: auto;
  margin: 0;
}
</style>
