<script setup>
import { ref, watch, onMounted, onUnmounted } from 'vue'
import AsideMenuLayer from '@/Components/AsideMenuLayer.vue'
import OverlayLayer from '@/Components/OverlayLayer.vue'

const props = defineProps({
  menu: {
    type: Array,
    required: true
  },
  isAsideMobileExpanded: Boolean,
  isAsideLgActive: Boolean
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

// Watch screen size changes
const handleResize = () => {
  screenWidth.value = window.innerWidth
  if (screenWidth.value < 1024) {
    if (props.isAsideLgActive) {
      emit('aside-lg-close-click')
    }
  }
}

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
    <AsideMenuLayer
      :menu="menu"
      :class="[
        'fixed top-0 z-40 h-screen w-64',
        isAsideMobileExpanded ? 'left-0' : '-left-64',
        { 'lg:left-0': isAsideLgActive },
        { 'lg:-left-64': !isAsideLgActive },
        'transition-all duration-300 ease-in-out'
      ]"
      @menu-click="menuClick"
      @aside-lg-close-click="asideLgCloseClick"
    />

    <OverlayLayer
      v-show="isAsideMobileExpanded || (isAsideLgActive && screenWidth < 1024)"
      z-index="z-30"
      @overlay-click="asideLgCloseClick"
    />
  </div>
</template>
