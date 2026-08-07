<template>
  <div class="td-page">
    <div class="td-page-head">
      <div>
        <h3 class="td-page-title"><i class="mdi mdi-backup-restore"></i>系统修复</h3>
        <p class="td-page-subtitle">修复配置、数据表、缓存与文件权限</p>
      </div>
    </div>

    <div class="td-form-note rep-note">
      <b>警告:</b> 修复操作可能影响系统运行,请谨慎执行;建议先备份系统与数据库后再操作。
    </div>

    <div class="rep-grid">
      <div v-for="item in repairItems" :key="item.id" class="td-set-card">
        <div class="td-set-card-hd">
          <div class="td-set-icon" :style="{ background: item.bg, color: item.color }">
            <i class="mdi" :class="item.icon"></i>
          </div>
          <div class="rep-card-head">
            <h4>{{ item.title }}</h4>
            <p>{{ item.desc }}</p>
          </div>
        </div>
        <div class="td-set-card-bd">
          <div class="rep-detail">{{ item.detail }}</div>
          <t-button
            theme="primary"
            variant="outline"
            :loading="busyId === item.id"
            @click="runRepair(item)"
          >
            <template #icon><i class="mdi mdi-wrench"></i></template>
            执行修复
          </t-button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { MessagePlugin, DialogPlugin } from 'tdesign-vue-next'
import { repair } from '@/admin/api/repair'

const busyId = ref(0)

const repairItems = [
  {
    id: 1,
    title: '修复配置文件',
    desc: '重新生成系统配置文件',
    detail: '当 config.php 损坏或字段缺失时使用,会按当前环境重写配置。',
    icon: 'mdi-tools',
    color: '#0052d9',
    bg: '#e8f3ff',
  },
  {
    id: 2,
    title: '重建数据表',
    desc: '检查并修复数据库表结构',
    detail: '比对系统所需表结构,自动新增缺失字段或修复损坏的表。',
    icon: 'mdi-database-refresh',
    color: '#2ba471',
    bg: '#e8f8f0',
  },
  {
    id: 3,
    title: '清理缓存',
    desc: '清理系统临时缓存文件',
    detail: '删除缓存目录与编译模板,释放空间并解决缓存导致的问题。',
    icon: 'mdi-broom',
    color: '#e37318',
    bg: '#fff3e0',
  },
  {
    id: 4,
    title: '修复权限',
    desc: '修复文件和目录权限',
    detail: '按推荐权限规则重置关键目录与文件的读写执行权限。',
    icon: 'mdi-shield-key',
    color: '#d54941',
    bg: '#fdecee',
  },
]

function runRepair(item) {
  const dialog = DialogPlugin.confirm({
    header: '确认执行修复',
    body: `将执行「${item.title}」操作,是否继续?`,
    confirmBtn: { content: '执行', theme: 'primary' },
    onConfirm: async () => {
      dialog.destroy()
      busyId.value = item.id
      const r = await repair(item.id, 'repair')
      busyId.value = 0
      if (r.ok) {
        MessagePlugin.success(r.message || '修复成功')
      }
    },
    onClose: () => dialog.destroy(),
  })
}
</script>

<style scoped>
.rep-note {
  margin-top: 0;
  margin-bottom: 14px;
}
.rep-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 14px;
}
.rep-card-head {
  flex: 1;
  min-width: 0;
}
.rep-detail {
  font-size: 12px;
  color: var(--td-text-secondary);
  line-height: 1.7;
  margin-bottom: 12px;
}
</style>
