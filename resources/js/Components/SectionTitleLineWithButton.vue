<script setup>
import { mdiCog } from '@mdi/js'
import { useSlots, computed } from 'vue'
import BaseIcon from '@/Components/BaseIcon.vue'
import BaseButton from '@/Components/BaseButton.vue'
import IconRounded from '@/Components/IconRounded.vue'

defineProps({
  icon: {
    type: String,
    default: null
  },
  title: {
    type: String,
    required: true
  },
  main: Boolean
})

const hasSlot = computed(() => useSlots().default)
</script>

<template>
  <section :class="{ 'pt-6': !main }" class="mb-6 flex items-center">
    <div class="flex items-center justify-start flex-grow">
      <IconRounded v-if="icon && main" :icon="icon" color="light" class="mr-3" bg />
      <BaseIcon v-else-if="icon" :path="icon" class="mr-2 text-gray-600 dark:text-dark-text" size="20" />
      <h1 :class="[
        main ? 'text-3xl' : 'text-2xl',
        'leading-tight text-gray-900 dark:text-dark-text font-semibold'
      ]">
        {{ title }}
      </h1>
    </div>
    <div v-if="hasSlot" class="flex space-x-2">
      <slot />
    </div>
    <BaseButton v-else-if="$slots.button" :icon="mdiCog" color="whiteDark" />
  </section>
</template>
