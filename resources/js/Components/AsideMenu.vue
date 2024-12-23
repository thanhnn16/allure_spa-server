<script setup>
import { ref, watch, onMounted, onUnmounted } from 'vue'
import AsideMenuLayer from '@/Components/AsideMenuLayer.vue'
import OverlayLayer from '@/Components/OverlayLayer.vue'

const props = defineProps({
  menu: {
    type: Array,
    required: true
  },
  isAsideMobileExpanded: Boolean
})

const emit = defineEmits(['menu-click', 'aside-lg-close-click'])

// Add ref for screen width
const screenWidth = ref(0)

// Handle menu click
const menuClick = (event, item) => {
  emit('menu-click', event, item)
}

// Handle close click
const asideLgCloseClick = (event) => {
  emit('aside-lg-close-click', event)
}

// Add debounce function
const debounce = (fn, delay) => {
  let timeoutId
  return (...args) => {
    clearTimeout(timeoutId)
    timeoutId = setTimeout(() => fn(...args), delay)
  }
}

// Update handleResize with debounce
const handleResize = debounce(() => {
  screenWidth.value = window.innerWidth
  if (screenWidth.value >= 1024) {
    // Trên desktop, đóng overlay nếu đang mở
    if (props.isAsideMobileExpanded) {
      emit('aside-lg-close-click')
    }
  }
}, 250)

// Add resize listener
onMounted(() => {
  // Set initial screen width
  screenWidth.value = window.innerWidth
  window.addEventListener('resize', handleResize)
  handleResize()
})

onUnmounted(() => {
  window.removeEventListener('resize', handleResize)
})
</script>

<template>
  <div>
    <AsideMenuLayer :menu="menu" :class="[
      'transition-all duration-300 ease-in-out',
      'lg:fixed lg:top-14 lg:left-0',
      isAsideMobileExpanded ? 'left-0' : '-left-64'
    ]" @menu-click="menuClick" @aside-lg-close-click="asideLgCloseClick" />

    <OverlayLayer v-show="isAsideMobileExpanded && screenWidth < 1024" z-index="z-30"
      @overlay-click="asideLgCloseClick" />
  </div>
</template>
