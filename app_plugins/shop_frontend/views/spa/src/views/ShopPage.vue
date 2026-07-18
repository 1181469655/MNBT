<template>
  <v-container class="py-8">
    <h1 class="text-h4 font-weight-bold mb-6">主机套餐</h1>
    <v-row v-if="plans.length">
      <v-col v-for="p in plans" :key="p.id" cols="12" md="6" lg="4">
        <PlanCard :plan="p" />
      </v-col>
    </v-row>
    <v-card v-else class="pa-8 text-center"><p class="text-body-1 text-medium-emphasis">暂无可购买的套餐</p></v-card>
  </v-container>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import api from '../api';
import PlanCard from '../components/PlanCard.vue';

const plans = ref([]);
onMounted(async () => {
  try { const r = await api.get('plans'); plans.value = r.data.plans || []; } catch (e) {}
});
</script>
