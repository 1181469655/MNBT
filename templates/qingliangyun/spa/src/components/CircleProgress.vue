<template>
  <div class="circle-wrap">
    <svg :width="size" :height="size" class="circle-svg">
      <circle
        class="track"
        :cx="half"
        :cy="half"
        :r="radius"
        :stroke-width="stroke"
        fill="none"
      />
      <circle
        class="progress"
        :cx="half"
        :cy="half"
        :r="radius"
        :stroke-width="stroke"
        fill="none"
        :stroke="color"
        :stroke-dasharray="circumference"
        :stroke-dashoffset="offset"
        stroke-linecap="round"
      />
    </svg>
    <div class="circle-center">
      <div class="pct">{{ displayPct }}%</div>
      <div class="label">{{ label }}</div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  percent: { type: Number, default: 0 },
  label: { type: String, default: '' },
  size: { type: Number, default: 140 },
  stroke: { type: Number, default: 10 },
  color: { type: String, default: 'var(--ql-primary)' },
})

const half = computed(() => props.size / 2)
const radius = computed(() => (props.size - props.stroke) / 2 - 2)
const circumference = computed(() => 2 * Math.PI * radius.value)
const clamped = computed(() => Math.min(100, Math.max(0, Number(props.percent) || 0)))
const offset = computed(() => circumference.value * (1 - clamped.value / 100))
const displayPct = computed(() => Math.round(clamped.value))
</script>

<style scoped>
.circle-wrap {
  position: relative;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}
.circle-svg {
  transform: rotate(-90deg);
}
.track {
  stroke: #e6fcf5;
}
.progress {
  transition: stroke-dashoffset 0.6s cubic-bezier(0.16, 1, 0.3, 1);
}
.circle-center {
  position: absolute;
  inset: 0;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  text-align: center;
}
.pct {
  font-size: 22px;
  font-weight: 700;
  color: var(--ql-text);
  line-height: 1.1;
}
.label {
  margin-top: 4px;
  font-size: 12px;
  color: var(--ql-text-secondary);
}
</style>
