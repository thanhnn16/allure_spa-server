<script setup>
import { containerMaxW } from '@/config.js'
import { computed, onMounted } from 'vue'
import BaseIcon from '@/Components/BaseIcon.vue'
import { mdiArrowLeft } from '@mdi/js'
import { router } from '@inertiajs/vue3'

const props = defineProps({
  showBackButton: {
    type: Boolean,
    default: true
  }
})

// Get route history from localStorage or initialize empty array
const getRouteHistory = () => {
  const history = localStorage.getItem('routeHistory')
  return history ? JSON.parse(history) : []
}

// Save route history to localStorage
const saveRouteHistory = (history) => {
  localStorage.setItem('routeHistory', JSON.stringify(history))
}

const canGoBack = computed(() => {
  const history = getRouteHistory()
  return props.showBackButton && router.page.url !== '/dashboard' && history.length > 1
})

const handleGoBack = () => {
  const history = getRouteHistory()
  if (history.length > 1) {
    // Remove current route
    history.pop()
    // Save updated history
    saveRouteHistory(history)
    // Get previous route
    const previousRoute = history[history.length - 1]
    router.visit(previousRoute)
  }
}

// Listen to route changes
router.on('finish', (event) => {
  const history = getRouteHistory()
  const currentUrl = router.page.url
  // Only add to history if it's a different route
  if (!history.length || history[history.length - 1] !== currentUrl) {
    history.push(currentUrl)
    saveRouteHistory(history)
  }
})

// Initialize with current route
onMounted(() => {
  const history = getRouteHistory()
  if (!history.length) {
    history.push(router.page.url)
    saveRouteHistory(history)
  }
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
