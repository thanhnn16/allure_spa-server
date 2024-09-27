<script setup>
import { computed } from 'vue'

const props = defineProps({
  type: {
    type: String,
    default: 'justify-start'
  },
  noWrap: Boolean
})

const componentClass = computed(() => [
  props.type,
  props.noWrap ? 'flex-nowrap' : 'flex-wrap'
])
</script>

<template>
  <div class="flex items-center" :class="componentClass">
    <slot />
  </div>
</template>
