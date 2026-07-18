<template>
  <v-container style="max-width: 400px" class="py-8">
    <v-card>
      <v-card-item><v-card-title class="text-center">注册</v-card-title></v-card-item>
      <v-card-text>
        <v-text-field v-model="form.username" label="用户名" variant="outlined" density="compact" />
        <v-text-field v-model="form.password" label="密码" type="password" variant="outlined" density="compact" />
        <v-text-field v-model="form.password2" label="确认密码" type="password" variant="outlined" density="compact" />
        <v-text-field v-model="form.email" label="邮箱（选填）" variant="outlined" density="compact" />
        <v-text-field v-model="form.qq" label="QQ（选填）" variant="outlined" density="compact" />
      </v-card-text>
      <v-card-actions class="pa-4"><v-btn color="primary" block @click="doReg" :loading="loading">注册</v-btn></v-card-actions>
      <div class="text-center pb-4"><a href="/login" class="text-caption">已有账号？登录</a></div>
    </v-card>
    <v-snackbar v-model="snack" color="error">{{ snackMsg }}</v-snackbar>
  </v-container>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { useRouter } from 'vue-router';
import { useUserStore } from '../stores/user';

const user = useUserStore();
const router = useRouter();
const form = reactive({ username: '', password: '', password2: '', email: '', qq: '' });
const loading = ref(false); const snack = ref(false); const snackMsg = ref('');

async function doReg() {
  if (!form.username || !form.password) { snackMsg.value = '请填写用户名和密码'; snack.value = true; return; }
  if (form.password !== form.password2) { snackMsg.value = '两次密码不一致'; snack.value = true; return; }
  loading.value = true;
  try { await user.register(form); router.push('/dashboard'); }
  catch (e) { snackMsg.value = e.message; snack.value = true; }
  loading.value = false;
}
</script>
