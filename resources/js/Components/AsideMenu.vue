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
  if (window.innerWidth < 1024 && props.isAsideLgActive) {
    emit('aside-lg-close-click')
  }
}

// Add resize listener
onMounted(() => {
  window.addEventListener('resize', handleResize)
})

onUnmounted(() => {
  window.removeEventListener('resize', handleResize)
})
</script>

<template>
  <div> <!-- Added a single root node -->
    <AsideMenuLayer
      :menu="menu"
      :class="[
        isAsideMobileExpanded ? 'left-0' : '-left-78',
        { 'lg:left-0': !isAsideLgActive },
        'transition-all duration-300 ease-in-out'
      ]"
      @menu-click="menuClick"
      @aside-lg-close-click="asideLgCloseClick"
    />
    
    <!-- Overlay when menu is open -->
    <OverlayLayer 
      v-show="isAsideMobileExpanded || isAsideLgActive" 
      z-index="z-30" 
      @overlay-click="asideLgCloseClick" 
    />
  </div>
</template>
