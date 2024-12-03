<script setup>
import { mdiCog } from '@mdi/js'
import CardBox from '@/Components/CardBox.vue'
import NumberDynamic from '@/Components/NumberDynamic.vue'
import BaseIcon from '@/Components/BaseIcon.vue'
import BaseLevel from '@/Components/BaseLevel.vue'
import PillTagTrend from '@/Components/PillTagTrend.vue'
import BaseButton from '@/Components/BaseButton.vue'

defineProps({
  number: {
    type: Number,
    default: 0
  },
  icon: {
    type: String,
    default: null
  },
  prefix: {
    type: String,
    default: null
  },
  suffix: {
    type: String,
    default: null
  },
  label: {
    type: String,
    default: null
  },
  color: {
    type: String,
    default: null
  },
  trend: {
    type: String,
    default: null
  },
  trendType: {
    type: String,
    default: null
  }
})
</script>

<template>
  <CardBox class="transition-colors duration-150 hover:bg-gray-50 dark:hover:bg-dark-hover">
    <BaseLevel v-if="trend" class="mb-3" mobile>
      <PillTagTrend :trend="trend" :trend-type="trendType" small />
      <BaseButton :icon="mdiCog" icon-w="w-4" icon-h="h-4" color="lightDark"
        class="hover:bg-gray-100 dark:hover:bg-dark-active transition-colors duration-150" small />
    </BaseLevel>
    <BaseLevel mobile>
      <div class="space-y-2">
        <h3 class="text-lg leading-tight text-gray-500 dark:text-dark-muted font-medium">
          {{ label }}
        </h3>
        <h1 class="text-3xl leading-tight font-semibold text-gray-900 dark:text-dark-text">
          <NumberDynamic :value="number" :prefix="prefix" :suffix="suffix" class="tracking-tight" />
        </h1>
      </div>
      <BaseIcon v-if="icon" :path="icon" size="48" w="" h="h-16" :class="[
        color,
        'transition-transform duration-200 transform group-hover:scale-110'
      ]" />
    </BaseLevel>
  </CardBox>
</template>

<style scoped>
.dark .dark\:hover\:bg-dark-hover:hover {
  background-color: rgba(255, 255, 255, 0.05);
}

.dark .dark\:hover\:bg-dark-active:hover {
  background-color: rgba(255, 255, 255, 0.1);
}
</style>
