<template>
  <v-app-bar app elevation="1" color="white">
    <v-container class="d-flex align-center">
      <v-app-bar-title>
        <a href="/" class="text-decoration-none" style="color: inherit; display: flex; align-items: center; gap: 8px;">
          <v-avatar v-if="opts.logo" size="32"><v-img :src="opts.logo" cover /></v-avatar>
          <span class="text-h6 font-weight-bold">{{ opts.title }}</span>
        </a>
      </v-app-bar-title>
      <v-spacer />
      <v-btn variant="text" to="/">首页</v-btn>
      <v-btn variant="text" to="/shop">套餐</v-btn>
      <template v-if="user.isLogin">
        <v-btn variant="text" prepend-icon="mdi-wallet" @click="$router.push('/balance')">余额</v-btn>
        <v-menu>
          <template #activator="{ props }"><v-btn icon v-bind="props"><v-icon>mdi-account-circle</v-icon></v-btn></template>
          <v-list density="compact">
            <v-list-item prepend-icon="mdi-view-dashboard" to="/dashboard" title="我的主机" />
            <v-list-item prepend-icon="mdi-receipt" to="/orders" title="订单" />
            <v-list-item prepend-icon="mdi-account-edit" to="/profile" title="个人信息" />
            <v-divider />
            <v-list-item prepend-icon="mdi-logout" title="退出" @click="doLogout" />
          </v-list>
        </v-menu>
      </template>
      <template v-else>
        <v-btn variant="outlined" to="/login" class="mr-2">登录</v-btn>
        <v-btn color="primary" to="/register">注册</v-btn>
      </template>
    </v-container>
  </v-app-bar>
</template>

<script setup>
import { useUserStore } from '../stores/user';
import { useRouter } from 'vue-router';

const user = useUserStore();
const router = useRouter();
const opts = { title: 'MNBT 主机售卖', logo: '' };

async function doLogout() { await user.logout(); router.push('/'); }
</script>
