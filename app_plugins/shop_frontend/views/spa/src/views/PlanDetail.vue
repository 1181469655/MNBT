<template>
  <v-container v-if="plan" style="max-width: 600px" class="py-8">
    <v-btn variant="text" prepend-icon="mdi-arrow-left" to="/shop" class="mb-4">返回套餐列表</v-btn>
    <v-card>
      <v-card-item><v-card-title class="text-h5">{{ plan.name }}</v-card-title><v-card-subtitle>{{ plan.description }}</v-card-subtitle></v-card-item>
      <v-card-text>
        <v-list density="compact">
          <v-list-item prepend-icon="mdi-harddisk" title="网页空间" :subtitle="plan.spec_web + ' MB'" />
          <v-list-item prepend-icon="mdi-database" title="数据库" :subtitle="plan.spec_sql + ' MB'" />
          <v-list-item prepend-icon="mdi-swap-horizontal" title="流量" :subtitle="plan.spec_flow > 0 ? plan.spec_flow + ' GB' : '不限'" />
          <v-list-item prepend-icon="mdi-web" title="域名绑定" :subtitle="plan.spec_domain + ' 个'" />
        </v-list>
        <v-divider class="my-3" />
        <v-radio-group v-model="period" inline>
          <v-radio v-if="plan.price_month_cents > 0" label="月付 ¥" + (plan.price_month_cents / 100).toFixed(2) value="month" />
          <v-radio v-if="plan.price_year_cents > 0" label="年付 ¥" + (plan.price_year_cents / 100).toFixed(2) value="year" />
        </v-radio-group>
        <v-select v-if="nodes.length" v-model="node" :items="nodes" item-title="btdh" item-value="btdh" label="开通节点" variant="outlined" density="compact" />
        <v-alert v-if="!user.isLogin" type="warning" variant="tonal" class="mt-2">请先<a href="/login" style="color:inherit">登录</a>后再购买</v-alert>
      </v-card-text>
      <v-card-actions class="pa-4">
        <v-btn color="primary" block size="large" @click="buy" :loading="loading" :disabled="!user.isLogin">确认购买</v-btn>
      </v-card-actions>
    </v-card>
    <v-snackbar v-model="snack" color="error">{{ snackMsg }}</v-snackbar>
  </v-container>
  <v-container v-else class="py-8 text-center"><p>套餐不存在</p></v-container>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { useUserStore } from '../stores/user';
import api from '../api';

const route = useRoute(); const user = useUserStore();
const plan = ref(null); const nodes = ref([]); const period = ref('month');
const node = ref(''); const loading = ref(false); const snack = ref(false); const snackMsg = ref('');

onMounted(async () => {
  try { const r = await api.get('plans'); plan.value = (r.data.plans || []).find(p => p.id == route.params.id) || null; } catch (e) {}
  try { const r = await api.get('nodes'); nodes.value = r.data.nodes || []; if (nodes.value.length) node.value = nodes.value[0].btdh; } catch (e) {}
});

async function buy() {
  loading.value = true;
  try { const r = await api.post('create_order', { plan_id: route.params.id, period: period.value, node: node.value }); if (r.data.html) { document.open(); document.write(r.data.html); document.close(); } }
  catch (e) { snackMsg.value = '创建订单失败'; snack.value = true; }
  loading.value = false;
}
</script>
