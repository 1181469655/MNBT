<template>
  <v-container style="max-width: 500px" class="py-8">
    <v-card>
      <v-card-item><v-card-title>个人信息</v-card-title></v-card-item>
      <v-card-text>
        <v-list density="compact">
          <v-list-item title="用户名" :subtitle="user.user?.username || ''" />
          <v-list-item title="注册时间" :subtitle="fmtDate(user.user?.created_at)" />
          <v-list-item title="邮箱" :subtitle="user.user?.email || '未设置'" />
          <v-list-item title="QQ" :subtitle="user.user?.qq || '未设置'" />
        </v-list>
      </v-card-text>
    </v-card>
  </v-container>
</template>

<script setup>
import { useUserStore } from '../stores/user';
function fmtDate(d) { if (!d) return '-'; try { return new Date(d).toLocaleString('zh-CN'); } catch (e) { return d; } }
const user = useUserStore();
</script>
