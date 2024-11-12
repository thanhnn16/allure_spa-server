<script setup>
import { containerMaxW } from '@/config.js'

defineProps({
  isAsideLgActive: {
    type: Boolean,
    required: true
  }
})
</script>

<template>
  <section 
    class="p-6 transition-all duration-300 ease-in-out" 
    :class="[
      containerMaxW,
      { 'lg:ml-64': isAsideLgActive },
      { 'lg:ml-0': !isAsideLgActive }
    ]"
  >
    <slot />
  </section>
</template>
