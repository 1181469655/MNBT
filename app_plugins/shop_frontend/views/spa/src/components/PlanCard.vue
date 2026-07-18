<template>
  <v-card hover @click="$router.push('/shop/' + plan.id)" class="cursor-pointer h-100">
    <v-card-item>
      <v-card-title>{{ plan.name }}
        <v-chip size="small" color="primary" variant="tonal" class="ml-2">{{ plan.spec_type == 1 ? 'CDN' : '虚拟主机' }}</v-chip>
      </v-card-title>
      <v-card-text class="text-body-2 text-medium-emphasis">{{ plan.description }}</v-card-text>
      <v-divider class="my-2" />
      <v-row dense class="text-caption px-4">
        <v-col cols="6"><v-icon size="14">mdi-harddisk</v-icon> {{ plan.spec_web }} MB</v-col>
        <v-col cols="6"><v-icon size="14">mdi-database</v-icon> {{ plan.spec_sql }} MB</v-col>
        <v-col cols="6"><v-icon size="14">mdi-swap-horizontal</v-icon> {{ plan.spec_flow > 0 ? plan.spec_flow + 'G' : '不限' }}</v-col>
        <v-col cols="6"><v-icon size="14">mdi-web</v-icon> {{ plan.spec_domain }} 域名</v-col>
      </v-row>
    </v-card-item>
    <v-card-actions class="px-4 pb-3">
      <span class="text-h6 font-weight-bold text-primary">¥{{ (plan.price_month_cents / 100).toFixed(2) }}</span>
      <span class="text-caption text-medium-emphasis">/月</span>
      <v-spacer />
      <v-btn color="primary" size="small">购买</v-btn>
    </v-card-actions>
  </v-card>
</template>

<script setup>
defineProps({ plan: Object });
</script>
