<template>
  <v-container style="max-width: 400px" class="py-8">
    <v-card>
      <v-card-item><v-card-title class="text-center">登录</v-card-title></v-card-item>
      <v-card-text>
        <v-text-field v-model="username" label="用户名" variant="outlined" density="compact" />
        <v-text-field v-model="password" label="密码" type="password" variant="outlined" density="compact" @keyup.enter="doLogin" />
      </v-card-text>
      <v-card-actions class="pa-4"><v-btn color="primary" block @click="doLogin" :loading="loading">登录</v-btn></v-card-actions>
      <div class="text-center pb-4"><a href="/register" class="text-caption">注册账号</a></div>
    </v-card>
    <v-snackbar v-model="snack" color="error">{{ snackMsg }}</v-snackbar>
  </v-container>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useUserStore } from '../stores/user';

const user = useUserStore();
const router = useRouter();
const username = ref(''); const password = ref('');
const loading = ref(false); const snack = ref(false); const snackMsg = ref('');

async function doLogin() {
  loading.value = true;
  try { await user.login(username.value, password.value); router.push('/dashboard'); }
  catch (e) { snackMsg.value = e.message; snack.value = true; }
  loading.value = false;
}
</script>
