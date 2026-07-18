<template>
  <div>
    <v-parallax :src="bg" height="300">
      <v-container class="fill-height">
        <v-row align="center" justify="center">
          <v-col cols="12" md="8" class="text-center text-white">
            <h1 class="text-h3 font-weight-bold mb-4">高性能虚拟主机，即买即用</h1>
            <p class="text-h6 mb-6">稳定、快速、安全的虚拟主机服务</p>
            <v-btn color="primary" size="large" to="/shop" class="mr-3">查看套餐</v-btn>
            <v-btn variant="outlined" size="large" to="/register">免费注册</v-btn>
          </v-col>
        </v-row>
      </v-container>
    </v-parallax>

    <v-container class="py-10">
      <h2 class="text-center text-h4 font-weight-bold mb-8">为什么选择我们</h2>
      <v-row>
        <v-col v-for="f in features" :key="f.icon" cols="12" sm="4">
          <v-card variant="flat" class="text-center pa-4">
            <v-icon size="48" color="primary">{{ f.icon }}</v-icon>
            <h3 class="text-h6 mt-3">{{ f.title }}</h3>
            <p class="text-body-2 text-medium-emphasis">{{ f.desc }}</p>
          </v-card>
        </v-col>
      </v-row>
    </v-container>

    <v-container v-if="plans.length" class="pb-10">
      <h2 class="text-center text-h4 font-weight-bold mb-8">热门套餐</h2>
      <v-row>
        <v-col v-for="p in plans.slice(0, 3)" :key="p.id" cols="12" md="4">
          <PlanCard :plan="p" />
        </v-col>
      </v-row>
      <div class="text-center mt-6">
        <v-btn variant="outlined" to="/shop" size="large">查看全部套餐</v-btn>
      </div>
    </v-container>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import api from '../api';
import PlanCard from '../components/PlanCard.vue';

const plans = ref([]);
const bg = 'data:image/svg+xml,' + encodeURIComponent('<svg xmlns="http://www.w3.org/2000/svg" width="1440" height="300"><rect width="1440" height="300" fill="#1867C0"/></svg>');

onMounted(async () => {
  try { const r = await api.get('plans'); plans.value = r.data.plans || []; } catch (e) {}
});

const features = [
  { icon: 'mdi-shield-check', title: '99.9% 在线率', desc: '企业级硬件，稳定可靠' },
  { icon: 'mdi-flash', title: '快速部署', desc: '支付完成后自动开通主机' },
  { icon: 'mdi-headset', title: '技术支持', desc: '专业技术团队在线支持' },
];
</script>
