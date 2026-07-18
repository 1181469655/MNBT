<template>
  <v-container class="py-8">
    <h1 class="text-h4 font-weight-bold mb-4">我的主机</h1>
    <v-card v-if="!assets.length" class="pa-8 text-center">
      <p class="mb-4">暂无开通的主机</p>
      <v-btn color="primary" to="/shop">去购买</v-btn>
    </v-card>
    <v-table v-else>
      <thead><tr><th>套餐</th><th>主机账号</th><th>节点</th><th>开通时间</th><th>到期时间</th><th>状态</th></tr></thead>
      <tbody>
        <tr v-for="a in assets" :key="a.id">
          <td>{{ a.plan_name }}</td><td>{{ a.host_user || '-' }}</td><td>{{ a.ssbt || '-' }}</td>
          <td>{{ fmtDate(a.created_at) }}</td><td>{{ fmtDate(a.expire_at) }}</td>
          <td><v-chip size="small" :color="a.status==='active'?'success':'error'">{{ a.status==='active'?'正常':'已到期' }}</v-chip></td>
        </tr>
      </tbody>
    </v-table>
  </v-container>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import api from '../api';
const assets = ref([]);
function fmtDate(d) { if (!d) return '-'; try { return new Date(d).toLocaleString('zh-CN'); } catch (e) { return d; } }
onMounted(async () => { try { const r = await api.get('assets'); assets.value = r.data.assets || []; } catch (e) {} });
</script>
