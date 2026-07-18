<template>
  <v-container class="py-8">
    <h1 class="text-h4 font-weight-bold mb-4">我的订单</h1>
    <v-table v-if="orders.length">
      <thead><tr><th>订单号</th><th>套餐</th><th>周期</th><th>金额</th><th>状态</th><th>时间</th></tr></thead>
      <tbody>
        <tr v-for="o in orders" :key="o.id">
          <td class="text-caption">{{ o.order_no }}</td><td>{{ o.plan_name }}</td>
          <td>{{ o.period === 'year' ? '年付' : '月付' }}</td><td>¥{{ (o.amount_cents / 100).toFixed(2) }}</td>
          <td><v-chip size="small" :color="sc[o.status]||'grey'">{{ sl[o.status] || o.status }}</v-chip></td>
          <td>{{ fmtDate(o.created_at) }}</td>
        </tr>
      </tbody>
    </v-table>
    <v-card v-else class="pa-8 text-center"><p>暂无订单</p></v-card>
  </v-container>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import api from '../api';
const orders = ref([]);
const sl = { pending: '待支付', paid: '已支付', opened: '已开通', failed: '失败', cancelled: '已取消' };
const sc = { pending: 'warning', paid: 'info', opened: 'success', failed: 'error', cancelled: 'error' };
function fmtDate(d) { if (!d) return '-'; try { return new Date(d).toLocaleString('zh-CN'); } catch (e) { return d; } }
onMounted(async () => { try { const r = await api.get('orders'); orders.value = r.data.orders || []; } catch (e) {} });
</script>
