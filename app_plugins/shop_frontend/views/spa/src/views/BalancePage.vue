<template>
  <v-container class="py-8">
    <v-card>
      <v-card-item><v-card-title>我的余额</v-card-title></v-card-item>
      <v-card-text>
        <div class="text-center my-4"><span class="text-h2 font-weight-bold text-primary">¥{{ (balance / 100).toFixed(2) }}</span></div>
        <v-btn color="primary" block size="large" @click="showRecharge = !showRecharge">充值</v-btn>
        <v-expand-transition>
          <v-card v-if="showRecharge" variant="outlined" class="mt-4 pa-4">
            <v-text-field v-model.number="amount" label="充值金额" type="number" min="1" max="50000" variant="outlined" density="compact" />
            <div class="d-flex gap-2 mb-3">
              <v-btn v-for="a in [10,50,100,500]" :key="a" size="small" variant="outlined" @click="amount = a">{{ a }}元</v-btn>
            </div>
            <v-btn color="primary" block @click="recharge" :loading="loading">立即充值</v-btn>
          </v-card>
        </v-expand-transition>
      </v-card-text>
    </v-card>

    <v-card class="mt-6">
      <v-card-item><v-card-title>交易记录</v-card-title></v-card-item>
      <v-table>
        <thead><tr><th>时间</th><th>类型</th><th>金额</th><th>备注</th></tr></thead>
        <tbody>
          <tr v-for="l in logs" :key="l.id">
            <td>{{ fmtDate(l.created_at) }}</td><td>{{ tl[l.type] || l.type }}</td>
            <td :class="l.amount >= 0 ? 'text-success' : 'text-error'">{{ l.amount >= 0 ? '+' : '' }}{{ Math.abs(l.amount / 100).toFixed(2) }}</td>
            <td>{{ l.remark || '-' }}</td>
          </tr>
        </tbody>
      </v-table>
    </v-card>
  </v-container>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import api from '../api';
const balance = ref(0); const logs = ref([]); const amount = ref(10);
const showRecharge = ref(false); const loading = ref(false);
const tl = { recharge: '充值', consume: '消费', refund: '退款', adjust: '调整' };
function fmtDate(d) { if (!d) return '-'; try { return new Date(d).toLocaleString('zh-CN'); } catch (e) { return d; } }

async function fetch() {
  try { const r = await api.get('balance'); balance.value = r.data.balance_cents || 0; logs.value = r.data.logs || []; } catch (e) {}
}
onMounted(fetch);

async function recharge() {
  loading.value = true;
  try { const r = await api.post('recharge', { amount: amount.value }); if (r.data.html) { document.open(); document.write(r.data.html); document.close(); } }
  catch (e) {}
  loading.value = false;
}
</script>
