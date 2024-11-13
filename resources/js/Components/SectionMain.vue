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
    class="p-6 transition-all duration-300 ease-in-out will-change-transform" 
    :class="[
      containerMaxW,
      { 'lg:ml-64 transform': isAsideLgActive },
      { 'lg:ml-0 transform': !isAsideLgActive }
    ]"
  >
    <slot />
  </section>
</template>
