<script setup>
import { containerMaxW } from '@/config.js'
import { computed, onMounted, getCurrentInstance } from 'vue'
import BaseIcon from '@/Components/BaseIcon.vue'
import { mdiArrowLeft } from '@mdi/js'
import { router } from '@inertiajs/vue3'

const props = defineProps({
  showBackButton: {
    type: Boolean,
    default: true
  }
})

const canGoBack = computed(() => {
  return props.showBackButton && !router.page.url.endsWith('/dashboard')
})

const handleGoBack = () => {
  window.history.back()
}

onMounted(() => {
  console.log('SectionMain mounted', {
    url: router.page.url,
    props: props,
    parent: getCurrentInstance()?.parent?.type?.name
  })
})
</script>

<template>
  <section class="p-6" :class="[containerMaxW]">
    <div v-if="canGoBack" class="mb-6">
      <button @click="handleGoBack" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium
               text-gray-700 dark:text-gray-200 bg-white dark:bg-slate-800
               border border-gray-200 dark:border-slate-700 rounded-lg
               hover:bg-gray-50 dark:hover:bg-slate-700 
               transition-colors duration-200">
        <BaseIcon :path="mdiArrowLeft" class="w-5 h-5" />
        <span>Quay lại</span>
      </button>
    </div>
    <slot />
  </section>
</template>
