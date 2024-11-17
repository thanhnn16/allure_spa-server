<script setup>
import { containerMaxW } from '@/config.js'
import { computed } from 'vue'
import { router } from '@inertiajs/vue3'
import Breadcrumb from '@/Components/Breadcrumb.vue'

const props = defineProps({
  breadcrumbs: {
    type: Array,
    default: () => []
  }
})

const showBreadcrumbs = computed(() => {
  return props.breadcrumbs.length > 0
})
</script>

<template>
  <section class="p-6" :class="[containerMaxW]">
    <Breadcrumb v-if="showBreadcrumbs" :items="breadcrumbs" />
    <slot />
  </section>
</template>
