<script setup>
import { useLayoutStore } from '@/Stores/layoutStore'

const layoutStore = useLayoutStore()

const toggleDarkMode = () => {
  layoutStore.toggleDarkMode()
}
</script>

<template>
  <button @click="toggleDarkMode">
    <BaseIcon :path="layoutStore.isDark ? mdiWeatherNight : mdiWeatherSunny" />
  </button>
</template> 